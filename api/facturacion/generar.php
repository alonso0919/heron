<?php

require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../config/facturama.php';

header('Content-Type: application/json; charset=utf-8');

function out(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') out(['ok' => false, 'error' => 'Método no permitido'], 405);
if (FACTURAMA_ENABLE_SANDBOX_ONLY && FACTURAMA_MODE !== 'sandbox') out(['ok' => false, 'error' => 'Este módulo está preparado solo para pruebas'], 403);
if (!facturama_is_configured()) out(['ok' => false, 'error' => 'Configura primero config/facturama.php con tus credenciales sandbox y RFC emisor'], 500);

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) out(['ok' => false, 'error' => 'JSON inválido'], 400);

$idVenta = (int)($payload['id_venta'] ?? 0);
if ($idVenta <= 0) out(['ok' => false, 'error' => 'id_venta inválido'], 422);

// Buscar la venta y validar que esté completada
$stmt = $conn->prepare("SELECT v.*, u.id_user AS user_exists
    FROM ventas v
    LEFT JOIN usuarios u ON u.id_user = v.id_usuario
    WHERE v.id_venta = ? LIMIT 1");
$stmt->bind_param('i', $idVenta);
$stmt->execute();
$venta = $stmt->get_result()->fetch_assoc();
if (!$venta) out(['ok' => false, 'error' => 'Venta no encontrada'], 404);
if (($venta['estado'] ?? '') !== 'completado') out(['ok' => false, 'error' => 'Solo se pueden facturar ventas completadas'], 409);

// Solo el dueño de la venta puede facturarla
$idUsuarioActual = current_user_id();
if ($idUsuarioActual && (int)($venta['id_usuario'] ?? 0) !== $idUsuarioActual) {
    out(['ok' => false, 'error' => 'No puedes facturar una venta que no te pertenece'], 403);
}

// Si ya hay factura emitida, regresarla tal cual
$already = $conn->prepare("SELECT * FROM facturas_emitidas WHERE id_venta = ? LIMIT 1");
$already->bind_param('i', $idVenta);
$already->execute();
$exists = $already->get_result()->fetch_assoc();
if ($exists && !empty($exists['facturama_cfdi_id'])) {
    out(['ok' => true, 'already_exists' => true, 'invoice' => $exists]);
}

// Obtener datos fiscales (o los del id_fiscal que manden, o los más recientes del usuario)
$fiscal = null;
$fiscalId = (int)($payload['id_fiscal'] ?? 0);
if ($fiscalId > 0) {
    $q = $conn->prepare("SELECT * FROM facturacion_clientes WHERE id_fiscal = ? LIMIT 1");
    $q->bind_param('i', $fiscalId);
    $q->execute();
    $fiscal = $q->get_result()->fetch_assoc();
}
if (!$fiscal && $idUsuarioActual) {
    $q = $conn->prepare("SELECT * FROM facturacion_clientes WHERE id_usuario = ? ORDER BY updated_at DESC, id_fiscal DESC LIMIT 1");
    $q->bind_param('i', $idUsuarioActual);
    $q->execute();
    $fiscal = $q->get_result()->fetch_assoc();
}
if (!$fiscal) out(['ok' => false, 'error' => 'Primero registra tus datos fiscales'], 422);

// Construir items a partir del detalle_venta
$det = $conn->prepare("SELECT dv.*, s.categoria, s.sat_product_code, s.sat_unit_code, s.sat_unit
    FROM detalle_venta dv
    LEFT JOIN servicios s ON s.id_servicio = dv.id_producto
    WHERE dv.id_venta = ? ORDER BY dv.id_detalle ASC");
$det->bind_param('i', $idVenta);
$det->execute();
$itemsRes = $det->get_result();
$items = [];
while ($row = $itemsRes->fetch_assoc()) {
    $items[] = facturama_item_from_sale_row($row);
}
if (!$items) out(['ok' => false, 'error' => 'La venta no tiene conceptos facturables'], 422);

// Payload para Facturama (CFDI 4.0)
$request = [
    'Receiver' => facturama_receiver_payload($fiscal),
    'CfdiType' => 'I', // Ingreso
    'NameId' => FACTURAMA_DEFAULT_CFDI_NAME_ID,
    'ExpeditionPlace' => FACTURAMA_EXPEDITION_PLACE,
    'Serie' => FACTURAMA_DEFAULT_SERIE,
    'Folio' => 'VENTA-' . $idVenta,
    'PaymentForm' => facturama_payment_form_from_sale($venta),
    'PaymentMethod' => FACTURAMA_DEFAULT_PAYMENT_METHOD,
    'Currency' => 'MXN',
    'Exportation' => FACTURAMA_DEFAULT_EXPORTATION,
    'Items' => $items,
];

// Llamar a Facturama para crear el CFDI
$response = facturama_request('POST', '3/cfdis', $request);
if (!($response['ok'] ?? false)) {
    out(['ok' => false, 'error' => 'No se pudo generar el CFDI', 'facturama' => $response, 'request_preview' => $request], 400);
}
$data = $response['data'] ?? [];
$cfdiId = (string)($data['Id'] ?? $data['id'] ?? '');
$folio = (string)($data['Folio'] ?? $data['folio'] ?? ('VENTA-' . $idVenta));
$uuid = (string)($data['Complement']['TaxStamp']['Uuid'] ?? $data['Complement']['TaxStamp']['UUID'] ?? $data['Uuid'] ?? $data['uuid'] ?? '');

// Guardar factura en BD
$conn->begin_transaction();
try {
    $idUsuarioVenta = !empty($venta['id_usuario']) ? (int)$venta['id_usuario'] : 0;
    $idFiscalVenta  = !empty($fiscal['id_fiscal']) ? (int)$fiscal['id_fiscal'] : 0;

    $cfdiIdSafe = trim((string)$cfdiId);
    $uuidSafe   = trim((string)$uuid);
    $serieSafe  = trim((string)FACTURAMA_DEFAULT_SERIE);
    $folioSafe  = trim((string)$folio);

    $reqJson = json_encode($request, JSON_UNESCAPED_UNICODE);
    $resJson = json_encode($data, JSON_UNESCAPED_UNICODE);

    $sql = "INSERT INTO facturas_emitidas (
                id_venta, id_usuario, id_fiscal, facturama_cfdi_id, facturama_uuid, serie, folio, status, request_payload, response_payload, created_at, updated_at
            ) VALUES (
                ?, NULLIF(?,0), NULLIF(?,0), NULLIF(?,''), NULLIF(?,''), ?, ?, 'issued', ?, ?, NOW(), NOW()
            )
            ON DUPLICATE KEY UPDATE
                id_usuario = VALUES(id_usuario),
                id_fiscal = VALUES(id_fiscal),
                facturama_cfdi_id = VALUES(facturama_cfdi_id),
                facturama_uuid = VALUES(facturama_uuid),
                serie = VALUES(serie),
                folio = VALUES(folio),
                status = 'issued',
                request_payload = VALUES(request_payload),
                response_payload = VALUES(response_payload),
                updated_at = NOW()";

    $stmt = $conn->prepare($sql);
    if (!$stmt) throw new Exception('Prepare failed: ' . $conn->error);

    $stmt->bind_param(
        'iiissssss',
        $idVenta,
        $idUsuarioVenta,
        $idFiscalVenta,
        $cfdiIdSafe,
        $uuidSafe,
        $serieSafe,
        $folioSafe,
        $reqJson,
        $resJson
    );

    if (!$stmt->execute()) throw new Exception('Execute failed: ' . $stmt->error);

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    out([
        'ok' => false,
        'error' => 'Se generó el CFDI pero no se pudo guardar localmente',
        'detail' => $e->getMessage(),
        'sql_error' => $conn->error,
        'facturama' => $data
    ], 500);
}

out([
    'ok' => true,
    'message' => 'CFDI generado en sandbox',
    'invoice' => [
        'id_venta' => $idVenta,
        'facturama_cfdi_id' => $cfdiId,
        'uuid' => $uuid,
        'serie' => FACTURAMA_DEFAULT_SERIE,
        'folio' => $folio,
    ],
    'facturama_response' => $data,
]);
