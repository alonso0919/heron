<?php

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../utils/cart_db.php';

header('Content-Type: application/json; charset=utf-8');

function json_out($data, int $code = 200) {
    http_response_code($code);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];

$user_id = current_user_id();
$guest_id = $_SESSION['guest_session_id'] ?? '';
if ($guest_id === '') {
    json_out(['ok' => false, 'error' => 'No hay sesión'], 400);
}

try {
    if ($method === 'GET') {
        $cart_id = cart_get_or_create($conn, $user_id, $guest_id);
        $items = cart_get_items($conn, $cart_id);
        json_out(['ok' => true, 'items' => $items, 'ttl_minutes' => CART_TTL_MINUTES]);
    }

    if ($method === 'POST') {
        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        if (!is_array($payload)) $payload = [];

        $action = $payload['action'] ?? 'set';
        $cart_id = cart_get_or_create($conn, $user_id, $guest_id);

        if ($action === 'set') {
            $items = $payload['items'] ?? [];
            if (!is_array($items)) $items = [];
            cart_set_items($conn, $cart_id, $items);
            json_out(['ok' => true]);
        }

        if ($action === 'clear') {
            cart_set_items($conn, $cart_id, []);
            json_out(['ok' => true]);
        }

        json_out(['ok' => false, 'error' => 'Acción inválida'], 400);
    }

    json_out(['ok' => false, 'error' => 'Método no permitido'], 405);
} catch (Throwable $e) {
    json_out(['ok' => false, 'error' => $e->getMessage()], 500);
}
