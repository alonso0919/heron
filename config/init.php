<?php
// Cargar variables de entorno desde .env antes que cualquier otra cosa
require_once __DIR__ . '/env.php';
load_env(__DIR__ . '/../.env');

// Seguridad básica de cookies de sesión
if (session_status() === PHP_SESSION_NONE) {
    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

require_once __DIR__ . '/conexion.php';

// Determinar la base de la URL de la aplicación (APP_BASE)
if (!defined('APP_BASE')) {
    $envBase = getenv('APP_BASE_URL');
    if ($envBase !== false && trim((string)$envBase) !== '') {
        define('APP_BASE', rtrim(trim((string)$envBase), '/'));
    } else {
        $scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $scriptDir = preg_replace('#/config$#', '', $scriptDir);
        $scriptDir = preg_replace('#/api(/[^/]+)?$#', '', $scriptDir);
        $scriptDir = preg_replace('#/vistas$#', '', $scriptDir);
        if ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '\\' || $scriptDir === '') {
            $scriptDir = '';
        }
        define('APP_BASE', rtrim($scriptDir, '/'));
    }
}

// Funciones de autenticación y autorización
function is_logged_in(): bool {
    return !empty($_SESSION['user']);
}

function current_user(): ?array {
    return $_SESSION['user'] ?? null;
}

function current_user_id(): ?int {
    return isset($_SESSION['user']['id_user']) ? (int)$_SESSION['user']['id_user'] : null;
}

function require_login(): void {
    if (is_logged_in()) {
        return;
    }

    // Evita bucles: no redirigir si ya estás en login/registro/logout
    $path = parse_url($_SERVER['REQUEST_URI'] ?? '', PHP_URL_PATH) ?? '';
    $basename = basename($path);
    if (in_array($basename, ['login.php', 'register.php', 'logout.php'], true)) {
        return;
    }

    // Guardar la URL destino en sesión (para regresar después del login)
    $next = $_SERVER['REQUEST_URI'] ?? (APP_BASE . '/vistas/index.php');
    $parts = parse_url($next);
    $cleanPath = $parts['path'] ?? (APP_BASE . '/vistas/index.php');
    $cleanQuery = '';
    if (!empty($parts['query'])) {
        parse_str($parts['query'], $q);
        unset($q['next']);
        if (!empty($q)) {
            $cleanQuery = '?' . http_build_query($q);
        }
    }
    $cleanNext = $cleanPath . $cleanQuery;

    if (strlen($cleanNext) > 1500) {
        $cleanNext = APP_BASE . '/vistas/index.php';
    }

    $_SESSION['login_next'] = $cleanNext;

    header('Location: ' . APP_BASE . '/vistas/login.php');
    exit;
}

// ID de sesión para carritos de invitado (persistencia en servidor)
if (empty($_SESSION['guest_session_id'])) {
    $_SESSION['guest_session_id'] = bin2hex(random_bytes(16));
}
?>
