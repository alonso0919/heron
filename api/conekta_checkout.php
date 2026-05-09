<?php

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../config/conekta.php';

header('Content-Type: application/json; charset=utf-8');

function json_out($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

// Normaliza un número de teléfono mexicano a formato +52XXXXXXXXXX o devuelve null si no es válido
function normalize_mx_phone($raw): ?string {
    $raw = (string)$raw;
    if ($raw === '') return null;

    $digits = preg_replace('/\D+/', '', $raw);
    if ($digits === '') return null;

    if (strlen($digits) === 10) {
        return '+52' . $digits;
    }
    if (strlen($digits) === 13 && str_starts_with($digits, '521')) {
        return '+52' . substr($digits, -10);
    }
    if (strlen($digits) >= 12 && str_starts_with($digits, '52')) {
        return '+52' . substr($digits, -10);
    }
    if (strlen($digits) > 10) {
        return '+52' . substr($digits, -10);
    }
    return null;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_out(['ok' => false, 'error' => 'Método no permitido'], 405);
}

$payload = json_decode(file_get_contents('php://input'), true);
if (!is_array($payload)) $payload = [];

$carrito = $payload['carrito'] ?? [];
$cliente = $payload['cliente'] ?? [];

if (!is_array($carrito) || count($carrito) === 0) {
    json_out(['ok' => false, 'error' => 'Carrito vacío'], 400);
}

// Calcula total y arma line_items (Conekta pide precios en CENTAVOS)
$line_items = [];
$total = 0.0;
foreach ($carrito as $it) {
    $name = (string)($it['name'] ?? 'Servicio');
    $qty = (int)($it['quantity'] ?? 1);
    $price = (float)($it['price'] ?? 0);
    if ($qty <= 0 || $price <= 0) continue;
    $total += ($price * $qty);
    $line_items[] = [
        'name' => $name,
        'unit_price' => (int) round($price * 100),
        'quantity' => $qty,
    ];
}
if (count($line_items) === 0) {
    json_out(['ok' => false, 'error' => 'Carrito inválido'], 400);
}

// Datos cliente mínimos para Conekta
$customer_info = [
    'name'  => (string)($cliente['nombre'] ?? (current_user()['nombre'] ?? 'Cliente')),
    'email' => (string)($cliente['email'] ?? (current_user()['email'] ?? 'cliente@example.com')),
];

$phone_raw = $cliente['telefono'] ?? ($cliente['phone'] ?? ($cliente['phone_number'] ?? ''));
$phone_norm = normalize_mx_phone($phone_raw);
if ($phone_norm !== null) {
    $customer_info['phone'] = $phone_norm;
}

// URLs de redirección después del pago (Hosted Payment)
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
// /api => /vistas
$baseVistas = preg_replace('#/api$#', '/vistas', $basePath);
$success_url = "$scheme://$host$baseVistas/pago_exitoso.php";
$failure_url = "$scheme://$host$baseVistas/pago_fallido.php";

$orderRequest = [
    'currency' => 'MXN',
    'customer_info' => $customer_info,
    'line_items' => $line_items,
    'shipping_lines' => [[ 'amount' => 0 ]], // Sin envío, son servicios
    'checkout' => [
        'type' => 'HostedPayment',
        'success_url' => $success_url,
        'failure_url' => $failure_url,
        'allowed_payment_methods' => ['card', 'cash', 'bank_transfer'],
        'monthly_installments_enabled' => true,
        'monthly_installments_options' => [3, 6, 9, 12],
        'redirection_time' => 4,
    ],
];

// 1) Crear orden en Conekta
$ch = curl_init('https://api.conekta.io/orders');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => [
        'Accept: ' . CONEKTA_ACCEPT,
        'Content-Type: application/json',
        'Authorization: Bearer ' . CONEKTA_PRIVATE_KEY,
        'Accept-Language: es',
    ],
    CURLOPT_POSTFIELDS => json_encode($orderRequest),
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_CAINFO => __DIR__ . '/../config/cacert.pem',
]);

$resp = curl_exec($ch);
$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err = curl_error($ch);
curl_close($ch);

if ($resp === false) {
    json_out(['ok' => false, 'error' => 'Error cURL: ' . $err], 500);
}

$data = json_decode($resp, true);
if ($http < 200 || $http >= 300) {
    json_out(['ok' => false, 'error' => 'Conekta error', 'http' => $http, 'detail' => $data ?: $resp], 400);
}

$conekta_order_id = $data['id'] ?? null;
$checkout_url = $data['checkout']['url'] ?? null;

if (!$conekta_order_id || !$checkout_url) {
    json_out(['ok' => false, 'error' => 'Respuesta inválida de Conekta', 'detail' => $data], 500);
}

// 2) Guardar venta PENDIENTE en BD (se confirma vía webhook cuando se paga)
$id_usuario = current_user_id();
$nombre = $customer_info['name'] ?: 'Cliente';
$email = $customer_info['email'] ?: 'cliente@example.com';
$telefono = $customer_info['phone'] ?? '';

$stmt = $conn->prepare("INSERT INTO ventas (id_usuario, nombre_cliente, email_cliente, telefono_cliente, total, fecha_venta, estado, conekta_order_id, payment_status) VALUES (?, ?, ?, ?, ?, NOW(), 'pendiente', ?, 'created')");
if ($id_usuario) {
    $stmt->bind_param('isssds', $id_usuario, $nombre, $email, $telefono, $total, $conekta_order_id);
} else {
    $null = null;
    $stmt->bind_param('isssds', $null, $nombre, $email, $telefono, $total, $conekta_order_id);
}
if (!$stmt->execute()) {
    json_out(['ok' => false, 'error' => 'No se pudo guardar la venta en BD', 'detail' => $stmt->error], 500);
}
$id_venta = (int)$conn->insert_id;

// 3) Insertar detalle_venta (uno por cada servicio)
$stmtDet = $conn->prepare("INSERT INTO detalle_venta (id_venta, id_producto, nombre_producto, precio_unitario, cantidad, subtotal) VALUES (?, ?, ?, ?, ?, ?)");
foreach ($carrito as $item) {
    $nombre_prod = (string)($item['name'] ?? '');
    $precio = (float)($item['price'] ?? 0);
    $qty = (int)($item['quantity'] ?? 1);
    if ($nombre_prod === '' || $precio <= 0 || $qty <= 0) continue;

    // Buscar id del servicio por nombre (si existe en catálogo)
    $id_producto = null;
    if (!empty($item['product_id']) && (int)$item['product_id'] > 0) {
        $id_producto = (int)$item['product_id'];
    } else {
        $stmtProd = $conn->prepare("SELECT id_servicio FROM servicios WHERE nombre LIKE ? LIMIT 1");
        $like = "%$nombre_prod%";
        $stmtProd->bind_param('s', $like);
        $stmtProd->execute();
        $row = $stmtProd->get_result()->fetch_assoc();
        if ($row) $id_producto = (int)$row['id_servicio'];
    }

    $subtotal = $precio * $qty;
    if ($id_producto) {
        $stmtDet->bind_param('iisdid', $id_venta, $id_producto, $nombre_prod, $precio, $qty, $subtotal);
    } else {
        $null = null;
        $stmtDet->bind_param('iisdid', $id_venta, $null, $nombre_prod, $precio, $qty, $subtotal);
    }
    $stmtDet->execute();
}

json_out([
    'ok' => true,
    'checkout_url' => $checkout_url,
    'conekta_order_id' => $conekta_order_id,
    'id_venta' => $id_venta,
]);
