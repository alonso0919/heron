<?php

require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../config/facturama.php';

header('Content-Type: application/json; charset=utf-8');

function out(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    out(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) {
    out(['ok' => false, 'error' => 'JSON inválido'], 400);
}

$rfc = strtoupper(trim((string)($payload['rfc'] ?? '')));
$razon = trim((string)($payload['razon_social'] ?? ''));
$cp = preg_replace('/\D+/', '', (string)($payload['tax_zip_code'] ?? ''));
$regimen = trim((string)($payload['fiscal_regime'] ?? ''));
$uso = trim((string)($payload['cfdi_use'] ?? ''));
$email = trim((string)($payload['email'] ?? (current_user()['email'] ?? '')));
$idUsuario = current_user_id();

if ($rfc === '' || $razon === '' || $cp === '' || $regimen === '' || $uso === '') {
    out(['ok' => false, 'error' => 'Faltan datos fiscales obligatorios'], 422);
}

if (!preg_match('/^[A-Z&Ñ]{3,4}[0-9]{6}[A-Z0-9]{3}$/u', $rfc)) {
    out(['ok' => false, 'error' => 'RFC inválido'], 422);
}

// Validación remota en Facturama (si falla, seguimos guardando local)
$validation = facturama_request('POST', 'api/customers/validate', [
    'Rfc' => $rfc,
    'Name' => facturama_normalize_name($razon),
    'FiscalRegime' => $regimen,
    'CfdiUse' => $uso,
    'TaxZipCode' => $cp,
]);

$conn->begin_transaction();
try {
    $stmt = $conn->prepare("INSERT INTO facturacion_clientes
        (id_usuario, email, rfc, razon_social, tax_zip_code, fiscal_regime, cfdi_use, validated_at, validation_payload, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW(), ?, NOW(), NOW())");
    $validationPayload = json_encode($validation, JSON_UNESCAPED_UNICODE);
    $stmt->bind_param('isssssss', $idUsuario, $email, $rfc, $razon, $cp, $regimen, $uso, $validationPayload);
    $stmt->execute();
    $idFiscal = (int)$conn->insert_id;

    // Si el usuario cambió su email al facturar, actualizamos el de la cuenta
    if ($idUsuario && $email !== '') {
        $up = $conn->prepare("UPDATE usuarios SET email = COALESCE(NULLIF(?, ''), email) WHERE id_user = ?");
        $up->bind_param('si', $email, $idUsuario);
        $up->execute();
    }
    $conn->commit();
} catch (Throwable $e) {
    $conn->rollback();
    out(['ok' => false, 'error' => 'No se pudieron guardar los datos fiscales', 'detail' => $e->getMessage()], 500);
}

out([
    'ok' => true,
    'id_fiscal' => $idFiscal,
    'message' => 'Datos fiscales guardados',
    'facturama_validation_ok' => (bool)($validation['ok'] ?? false),
    'facturama_validation' => $validation,
]);
