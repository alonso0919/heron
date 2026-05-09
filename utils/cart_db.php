<?php

// Duración de "apartado" (minutos)
const CART_TTL_MINUTES = 30;

// Borra los carritos expirados (y sus items) de la base de datos.
function cart_cleanup_expired(mysqli $conn): void {
    $sql = "DELETE c, ci FROM carts c
            LEFT JOIN cart_items ci ON ci.cart_id = c.id_cart
            WHERE c.expires_at < NOW()";
    $conn->query($sql);
}

// Regresa el ID del carrito activo del usuario o invitado, o crea uno nuevo si no existe.
function cart_get_or_create(mysqli $conn, ?int $user_id, string $guest_session_id): int {
    cart_cleanup_expired($conn);

    if ($user_id) {
        $stmt = $conn->prepare("SELECT id_cart FROM carts WHERE user_id = ? AND status = 'active' ORDER BY updated_at DESC LIMIT 1");
        $stmt->bind_param('i', $user_id);
    } else {
        $stmt = $conn->prepare("SELECT id_cart FROM carts WHERE guest_session_id = ? AND status = 'active' ORDER BY updated_at DESC LIMIT 1");
        $stmt->bind_param('s', $guest_session_id);
    }
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if ($row) return (int)$row['id_cart'];

    $expires = (new DateTime('now'))
        ->add(new DateInterval('PT' . CART_TTL_MINUTES . 'M'))
        ->format('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO carts (user_id, guest_session_id, status, expires_at) VALUES (?, ?, 'active', ?)");
    if ($user_id) {
        $stmt->bind_param('iss', $user_id, $guest_session_id, $expires);
    } else {
        $null = null;
        $stmt->bind_param('iss', $null, $guest_session_id, $expires);
    }
    $stmt->execute();
    return (int)$conn->insert_id;
}

// Actualiza el timestamp de "updated_at" y "expires_at" del carrito para mantenerlo activo.
function cart_touch(mysqli $conn, int $cart_id): void {
    $expires = (new DateTime('now'))
        ->add(new DateInterval('PT' . CART_TTL_MINUTES . 'M'))
        ->format('Y-m-d H:i:s');
    $stmt = $conn->prepare("UPDATE carts SET updated_at = NOW(), expires_at = ? WHERE id_cart = ?");
    $stmt->bind_param('si', $expires, $cart_id);
    $stmt->execute();
}

// Reemplaza los items del carrito por el arreglo dado
function cart_set_items(mysqli $conn, int $cart_id, array $items): void {
    $conn->begin_transaction();
    try {
        $stmt = $conn->prepare("DELETE FROM cart_items WHERE cart_id = ?");
        $stmt->bind_param('i', $cart_id);
        $stmt->execute();

        $stmt = $conn->prepare("INSERT INTO cart_items (cart_id, product_id, name, unit_price, quantity, image) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($items as $it) {
            $pid = (int)($it['product_id'] ?? 0);
            $name = (string)($it['name'] ?? '');
            $price = (float)($it['price'] ?? 0);
            $qty = (int)($it['quantity'] ?? 1);
            $img = (string)($it['image'] ?? '');
            if ($name === '' || $qty <= 0) continue;
            $stmt->bind_param('iisdis', $cart_id, $pid, $name, $price, $qty, $img);
            $stmt->execute();
        }

        cart_touch($conn, $cart_id);
        $conn->commit();
    } catch (Throwable $e) {
        $conn->rollback();
        throw $e;
    }
}

// Regresa un arreglo con los items del carrito.
function cart_get_items(mysqli $conn, int $cart_id): array {
    cart_cleanup_expired($conn);
    $stmt = $conn->prepare("SELECT product_id, name, unit_price AS price, quantity, image FROM cart_items WHERE cart_id = ? ORDER BY id_item ASC");
    $stmt->bind_param('i', $cart_id);
    $stmt->execute();
    $res = $stmt->get_result();
    $out = [];
    while ($row = $res->fetch_assoc()) {
        $row['product_id'] = (int)$row['product_id'];
        $row['price'] = (float)$row['price'];
        $row['quantity'] = (int)$row['quantity'];
        $out[] = $row;
    }
    return $out;
}

// Si el usuario se loguea después de haber agregado items al carrito como invitado, esta función asocia el carrito activo del invitado a su cuenta de usuario.
function cart_attach_guest_to_user(mysqli $conn, string $guest_session_id, int $user_id): void {
    cart_cleanup_expired($conn);
    $stmt = $conn->prepare("SELECT id_cart FROM carts WHERE guest_session_id = ? AND status='active' ORDER BY updated_at DESC LIMIT 1");
    $stmt->bind_param('s', $guest_session_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    if (!$row) return;
    $cart_id = (int)$row['id_cart'];

    $stmt = $conn->prepare("UPDATE carts SET user_id = ?, guest_session_id = NULL, updated_at = NOW() WHERE id_cart = ?");
    $stmt->bind_param('ii', $user_id, $cart_id);
    $stmt->execute();
}
