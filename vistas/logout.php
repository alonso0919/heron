<?php

require_once __DIR__ . '/../config/init.php';

// 1. Vaciar todas las variables de sesión
$_SESSION = [];

// 2. Borrar la cookie de sesión del navegador (para que el browser no siga
//    mandando el mismo SID y PHP no "resucite" la sesión vieja).
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// 3. Destruir la sesión en el servidor
session_destroy();

// 4. Redirigir al inicio (ya sin sesión)
header('Location: ' . APP_BASE . '/vistas/index.php');
exit;
