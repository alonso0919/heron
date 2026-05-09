<?php
// NOTA: en producción se debe validar la firma del webhook (HMAC) en sandbox
// lo dejamos abierto para que sea fácil probar desde el dashboard de Conekta.

require_once __DIR__ . '/../config/init.php';

header('Content-Type: application/json; charset=utf-8');

$raw = file_get_contents('php://input');
$event = json_decode($raw, true);

if (!is_array($event)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'JSON inválido']);
    exit;
}

$event_id = (string)($event['id'] ?? '');
$type = $event['type'] ?? '';
$obj = $event['data']['object'] ?? [];
$order_id = $obj['id'] ?? ($obj['order_id'] ?? null);

// Log del evento (INSERT IGNORE por event_id UNIQUE => evita duplicados)
if ($event_id !== '') {
    $log = $conn->prepare("INSERT IGNORE INTO webhook_eventos (provider, event_id, event_type, order_id, payload, received_at) VALUES ('conekta', ?, ?, ?, ?, NOW())");
    $log->bind_param('ssss', $event_id, $type, $order_id, $raw);
    $log->execute();
}

if (!$order_id) {
    http_response_code(200);
    echo json_encode(['ok' => true, 'ignored' => true]);
    exit;
}

// Buscar venta por conekta_order_id
$stmt = $conn->prepare("SELECT id_venta, estado FROM ventas WHERE conekta_order_id = ? LIMIT 1");
$stmt->bind_param('s', $order_id);
$stmt->execute();
$venta = $stmt->get_result()->fetch_assoc();
if (!$venta) {
    http_response_code(200);
    echo json_encode(['ok' => true, 'ignored' => true, 'reason' => 'venta no encontrada']);
    exit;
}

$id_venta = (int)$venta['id_venta'];

 // Genera el ticket de compra para una venta. Evita duplicados por si Conekta reintenta el webhook.
function generar_ticket(mysqli $conn, int $id_venta): void {
    $check = $conn->prepare("SELECT id_ticket FROM tickets WHERE id_venta = ? LIMIT 1");
    $check->bind_param('i', $id_venta);
    $check->execute();
    $exists = $check->get_result()->fetch_assoc();
    if ($exists) return;

    $stmt = $conn->prepare("SELECT nombre_cliente, total, fecha_venta FROM ventas WHERE id_venta = ?");
    $stmt->bind_param('i', $id_venta);
    $stmt->execute();
    $v = $stmt->get_result()->fetch_assoc();
    if (!$v) return;

    $numero_ticket = 'HRN-' . date('Ymd') . '-' . str_pad((string)$id_venta, 5, '0', STR_PAD_LEFT);

    $lines = [];
    $lines[] = '====== TICKET DE COMPRA ======';
    $lines[] = '   HERON ESTÉTICA & BARBERÍA';
    $lines[] = '==============================';
    $lines[] = 'Venta #' . $id_venta;
    $lines[] = 'Ticket: ' . $numero_ticket;
    $lines[] = 'Cliente: ' . ($v['nombre_cliente'] ?? '');
    $lines[] = 'Fecha: ' . ($v['fecha_venta'] ?? '');
    $lines[] = '------------------------------';

    $stmt2 = $conn->prepare("SELECT nombre_producto, precio_unitario, cantidad, subtotal FROM detalle_venta WHERE id_venta = ?");
    $stmt2->bind_param('i', $id_venta);
    $stmt2->execute();
    $res = $stmt2->get_result();
    while ($d = $res->fetch_assoc()) {
        $lines[] = $d['nombre_producto']
                 . "\n   " . $d['cantidad'] . ' x $' . number_format($d['precio_unitario'], 2)
                 . ' = $' . number_format($d['subtotal'], 2);
    }
    $lines[] = '------------------------------';
    $lines[] = 'TOTAL: $' . number_format((float)$v['total'], 2) . ' MXN';
    $lines[] = '==============================';
    $lines[] = 'Gracias por tu visita.';
    $lines[] = 'Puedes facturar este ticket en tu perfil.';
    $ticket = implode("\n", $lines);

    $ins = $conn->prepare("INSERT INTO tickets (id_venta, numero_ticket, contenido, fecha_generacion) VALUES (?, ?, ?, NOW())");
    $ins->bind_param('iss', $id_venta, $numero_ticket, $ticket);
    $ins->execute();
}

// Procesar eventos relevantes

if ($type === 'order.paid') {
    // Evitar doble procesamiento si Conekta reenvía el webhook
    if (($venta['estado'] ?? '') !== 'completado') {
        $conn->begin_transaction();
        try {
            $up = $conn->prepare("UPDATE ventas SET estado='completado', payment_status='paid' WHERE id_venta = ?");
            $up->bind_param('i', $id_venta);
            $up->execute();
            // Los servicios NO tienen stock, así que NO se descuenta nada.
            generar_ticket($conn, $id_venta);
            $conn->commit();
        } catch (Throwable $e) {
            $conn->rollback();
            http_response_code(500);
            echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
            exit;
        }
    }
    echo json_encode(['ok' => true]);
    exit;
}

if (in_array($type, ['order.declined', 'order.expired', 'order.canceled', 'order.voided'], true)) {
    $status = str_replace('order.', '', $type);
    $up = $conn->prepare("UPDATE ventas SET estado='cancelado', payment_status=? WHERE id_venta = ?");
    $up->bind_param('si', $status, $id_venta);
    $up->execute();
    echo json_encode(['ok' => true]);
    exit;
}

// Cualquier otro tipo de evento: solo loguearlo
echo json_encode(['ok' => true, 'ignored' => true, 'type' => $type]);
