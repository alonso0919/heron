<?php
require_once __DIR__ . '/../config/init.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pago no completado - Heron</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="main-layout">
    <div class="wrapper">
        <?php include 'nav.php'; ?>

        <div id="content">
            <div class="container" style="padding:70px 15px; max-width:760px;">
                <div style="background:#fff; padding:40px 30px; border-radius:12px; box-shadow:0 10px 30px rgba(0,0,0,.1); text-align:center;">
                    <div style="font-size:72px; color:#c0392b; margin-bottom:15px;">
                        <i class="fa fa-times-circle"></i>
                    </div>
                    <h2 style="color:#c0392b;">El pago no se completó</h2>
                    <p style="color:#555; font-size:16px;">
                        Parece que tu pago no se pudo procesar o fue cancelado. No se te ha cobrado nada.
                    </p>
                    <p style="color:#888; font-size:14px;">
                        Tus servicios siguen en el carrito. Puedes intentarlo de nuevo cuando quieras.
                    </p>
                    <div style="display:flex; gap:10px; flex-wrap:wrap; justify-content:center; margin-top:24px;">
                        <a class="button" href="cart.php" style="background:#ffb400; color:#000; padding:12px 26px; border-radius:6px; text-decoration:none; font-weight:700;">
                            <i class="fa fa-shopping-cart"></i>&nbsp; Volver al carrito
                        </a>
                        <a class="button" href="index.php" style="background:#222; color:#fff; padding:12px 26px; border-radius:6px; text-decoration:none; font-weight:700;">Ir al inicio</a>
                    </div>
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
