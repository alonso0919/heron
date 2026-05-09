<?php

require_once __DIR__ . '/../config/init.php';

// Si viene ?next=... por GET, lo guardamos en sesión (con límite de seguridad)
if (!empty($_GET['next'])) {
    $n = (string)$_GET['next'];
    if (strlen($n) <= 1500 && str_starts_with($n, APP_BASE)) {
        $_SESSION['login_next'] = $n;
    }
}

if (is_logged_in()) {
    $dest = $_SESSION['login_next'] ?? (APP_BASE . '/vistas/index.php');
    unset($_SESSION['login_next']);
    header('Location: ' . $dest);
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $error = 'Ingresa email y contraseña.';
    } else {
        $sql = "SELECT id_user, nombre, email, password_hash FROM usuarios WHERE email = ? LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $res = $stmt->get_result();
        $user = $res->fetch_assoc();

        if (!$user || empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
            $error = 'Credenciales inválidas.';
        } else {
            // Guardar user en sesión
            $_SESSION['user'] = [
                'id_user' => (int)$user['id_user'],
                'nombre' => $user['nombre'],
                'email' => $user['email'],
            ];

            // Enganchar el carrito de invitado al usuario recién logueado
            require_once __DIR__ . '/../utils/cart_db.php';
            cart_attach_guest_to_user($conn, $_SESSION['guest_session_id'], (int)$user['id_user']);

            $dest = $_SESSION['login_next'] ?? (APP_BASE . '/vistas/index.php');
            unset($_SESSION['login_next']);
            header('Location: ' . $dest);
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión - Heron</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .auth-wrap { max-width: 460px; margin: 60px auto; padding: 0 20px; }
        .auth-card { background: #fff; border-radius: 14px; padding: 34px 28px; box-shadow: 0 12px 30px rgba(0,0,0,.12); }
        .auth-card h2 { margin-bottom: 6px; color:#222; }
        .auth-card p.sub { color:#888; margin-bottom: 24px; }
        .auth-card label { font-weight: 600; color:#333; margin-top: 10px; display:block; }
        .auth-card input { width: 100%; padding: 11px 14px; border: 1px solid #ddd; border-radius: 8px; margin-top: 4px; }
        .auth-card input:focus { border-color:#ffb400; outline:none; }
        .auth-card .btn-submit { width:100%; margin-top:20px; padding:12px; background:#ffb400; color:#000; border:none; border-radius:8px; font-weight:700; cursor:pointer; font-size:16px; }
        .auth-card .btn-submit:hover { background:#e09e00; }
        .auth-card .error { background:#fff0f0; color:#c0392b; padding:10px 14px; border-radius:8px; margin-bottom:16px; border-left:4px solid #c0392b; }
        .auth-card .link { text-align:center; margin-top:18px; color:#555; }
        .auth-card .link a { color:#ffb400; font-weight:600; }
        .auth-card .demo-hint { background:#fffbe6; border:1px dashed #ffb400; padding:10px 14px; border-radius:8px; font-size:13px; color:#555; margin-top:14px; }
    </style>
</head>
<body class="main-layout">
<div class="wrapper">
    <?php include 'nav.php'; ?>

    <div id="content">
        <div class="auth-wrap">
            <div class="auth-card">
                <h2>Iniciar sesión</h2>
                <p class="sub">Accede a tu cuenta para reservar y facturar.</p>

                <?php if ($error): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>

                <form method="POST">
                    <label for="email">Email</label>
                    <input id="email" type="email" name="email" required
                           value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

                    <label for="password">Contraseña</label>
                    <input id="password" type="password" name="password" required>

                    <button class="btn-submit" type="submit">Entrar</button>
                </form>

                <div class="demo-hint">
                    <strong>Cuenta demo:</strong> cliente@demo.com / demo1234
                </div>

                <p class="link">¿No tienes cuenta? <a href="register.php">Regístrate</a></p>
            </div>
        </div>

        <?php include 'footer.php'; ?>
    </div>

    <div class="overlay"></div>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/popper.min.js"></script>
<script src="js/bootstrap.bundle.min.js"></script>
<script src="js/custom.js"></script>
<script src="js/cart_sync.js"></script>
</body>
</html>
