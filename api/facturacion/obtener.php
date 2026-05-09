<?php

require_once __DIR__ . '/../../config/init.php';
require_once __DIR__ . '/../../config/facturama.php';

header('Content-Type: application/json; charset=utf-8');

function out(array $data, int $code = 200): void {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$idVenta = (int)($_GET['id_venta'] ?? 0);
if ($idVenta <= 0) out(['ok' => false, 'error' => 'id_venta inválido'], 422);

$stmt = $conn->prepare("SELECT * FROM facturas_emitidas WHERE id_venta = ? LIMIT 1");
$stmt->bind_param('i', $idVenta);
$stmt->execute();
$invoice = $stmt->get_result()->fetch_assoc();
if (!$invoice) out(['ok' => false, 'error' => 'Factura no encontrada'], 404);

$out = ['ok' => true, 'invoice' => $invoice];
if (!empty($invoice['facturama_cfdi_id'])) {
    $detail = facturama_request('GET', '3/cfdis/' . rawurlencode($invoice['facturama_cfdi_id']));
    $out['facturama_detail'] = $detail;
}
out($out);
