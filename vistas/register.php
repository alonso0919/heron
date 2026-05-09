<?php

require_once __DIR__ . '/../config/init.php';

if (is_logged_in()) {
    header('Location: ' . APP_BASE . '/vistas/index.php');
    exit;
}

$error = '';
$ok = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = (string)($_POST['password'] ?? '');
    $password2 = (string)($_POST['password2'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $edad = (int)($_POST['edad'] ?? 0);

    if ($nombre === '' || $email === '' || $password === '') {
        $error = 'Nombre, email y contraseña son obligatorios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Email inválido.';
    } elseif (strlen($password) < 6) {
        $error = 'La contraseña debe tener al menos 6 caracteres.';
    } elseif ($password !== $password2) {
        $error = 'Las contraseñas no coinciden.';
    } else {
        $stmt = $conn->prepare('SELECT id_user FROM usuarios WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $exists = $stmt->get_result()->fetch_assoc();

        if ($exists) {
            $error = 'Ese email ya está registrado.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare('INSERT INTO usuarios (nombre, email, telefono, direccion, edad, password_hash) VALUES (?, ?, ?, ?, ?, ?)');
            $stmt->bind_param('ssssis', $nombre, $email, $telefono, $direccion, $edad, $hash);
            if ($stmt->execute()) {
                $ok = 'Cuenta creada. Ya puedes iniciar sesión.';
            } else {
                $error = 'No se pudo crear la cuenta. Intenta de nuevo.';
            }
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
    <title>Crear cuenta - Heron</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .auth-wrap { max-width: 620px; margin: 50px auto; padding: 0 20px; }
        .auth-card { background: #fff; border-radius: 14px; padding: 34px 28px; box-shadow: 0 12px 30px rgba(0,0,0,.12); }
        .auth-card h2 { margin-bottom: 6px; color:#222; }
        .auth-card p.sub { color:#888; margin-bottom: 24px; }
        .auth-card label { font-weight: 600; color:#333; margin-top: 10px; display:block; }
        .auth-card input, .auth-card textarea { width: 100%; padding: 11px 14px; border: 1px solid #ddd; border-radius: 8px; margin-top: 4px; font-family: inherit; }
        .auth-card input:focus, .auth-card textarea:focus { border-color:#ffb400; outline:none; }
        .auth-card .btn-submit { width:100%; margin-top:20px; padding:12px; background:#ffb400; color:#000; border:none; border-radius:8px; font-weight:700; cursor:pointer; font-size:16px; }
        .auth-card .btn-submit:hover { background:#e09e00; }
        .auth-card .error { background:#fff0f0; color:#c0392b; padding:10px 14px; border-radius:8px; margin-bottom:16px; border-left:4px solid #c0392b; }
        .auth-card .ok { background:#f0fff4; color:#1b7a3e; padding:10px 14px; border-radius:8px; margin-bottom:16px; border-left:4px solid #1b7a3e; }
        .auth-card .link { text-align:center; margin-top:18px; color:#555; }
        .auth-card .link a { color:#ffb400; font-weight:600; }
        .row-2 { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        @media (max-width: 600px) { .row-2 { grid-template-columns: 1fr; } }
        .hint { font-size:12px; color:#888; margin-top:4px; }
    </style>
</head>
<body class="main-layout">
<div class="wrapper">
    <?php include 'nav.php'; ?>

    <div id="content">
        <div class="auth-wrap">
            <div class="auth-card">
                <h2>Crear cuenta</h2>
                <p class="sub">Solo necesitas email y contraseña para empezar.</p>

                <?php if ($error): ?>
                    <div class="error"><?= htmlspecialchars($error) ?></div>
                <?php endif; ?>
                <?php if ($ok): ?>
                    <div class="ok">
                        <?= htmlspecialchars($ok) ?>
                        <br><a href="login.php">Ir a iniciar sesión &rarr;</a>
                    </div>
                <?php endif; ?>

                <?php if (!$ok): ?>
                <form method="POST">
                    <label>Nombre completo</label>
                    <input name="nombre" required value="<?= htmlspecialchars($_POST['nombre'] ?? '') ?>">

                    <label>Email</label>
                    <input type="email" name="email" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">

                    <div class="row-2">
                        <div>
                            <label>Contraseña</label>
                            <input type="password" name="password" required>
                            <div class="hint">Mínimo 6 caracteres.</div>
                        </div>
                        <div>
                            <label>Confirmar contraseña</label>
                            <input type="password" name="password2" required>
                        </div>
                    </div>

                    <div class="row-2">
                        <div>
                            <label>Teléfono</label>
                            <input name="telefono" value="<?= htmlspecialchars($_POST['telefono'] ?? '') ?>" placeholder="618-123-4567">
                        </div>
                        <div>
                            <label>Edad</label>
                            <input type="number" min="0" name="edad" value="<?= htmlspecialchars($_POST['edad'] ?? '') ?>">
                        </div>
                    </div>

                    <label>Dirección (opcional)</label>
                    <textarea name="direccion" rows="2"><?= htmlspecialchars($_POST['direccion'] ?? '') ?></textarea>

                    <button class="btn-submit" type="submit">Registrarme</button>
                </form>
                <?php endif; ?>

                <p class="link">¿Ya tienes cuenta? <a href="login.php">Inicia sesión</a></p>
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
