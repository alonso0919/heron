<?php
require_once __DIR__ . '/../config/init.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Contacto - Heron</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .contact-card { background: #1a1a1a; color:#fff; padding: 28px; border-radius: 10px; margin-bottom: 20px; }
        .contact-card .icon { background:#ffb400; color:#000; width:50px; height:50px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:22px; margin-bottom: 14px; }
        .contact-card h4 { color:#ffb400; margin-bottom: 8px; }
        .contact-card p { color: #ccc; margin: 0; line-height: 1.6; }
    </style>
</head>
<body class="main-layout">
    <div class="wrapper">
        <?php include 'nav.php'; ?>

        <div id="content">
            <div class="yellow_bg">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12"><div class="title"><h2>Contacto</h2></div></div>
                    </div>
                </div>
            </div>

            <div class="container" style="padding: 50px 15px;">
                <div class="row">
                    <div class="col-md-4">
                        <div class="contact-card">
                            <div class="icon"><i class="fa fa-map-marker"></i></div>
                            <h4>Dirección</h4>
                            <p>Av. 20 de Noviembre 100<br>Centro, 34000<br>Durango, Dgo.</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="contact-card">
                            <div class="icon"><i class="fa fa-phone"></i></div>
                            <h4>Teléfono</h4>
                            <p>(618) 123-4567<br>WhatsApp disponible</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="contact-card">
                            <div class="icon"><i class="fa fa-envelope"></i></div>
                            <h4>Email</h4>
                            <p>contacto@heron.mx<br>facturacion@heron.mx</p>
                        </div>
                    </div>
                </div>

                <div class="contact-card" style="margin-top:30px;">
                    <h4><i class="fa fa-clock-o"></i> Horario</h4>
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong style="color:#fff;">Lunes - Viernes</strong><br>9:00 a.m. - 9:00 p.m.</p>
                        </div>
                        <div class="col-md-6">
                            <p><strong style="color:#fff;">Sábado</strong>  9:00 a.m. - 6:00 p.m.<br><strong style="color:#fff;">Domingo</strong>  Cerrado</p>
                        </div>
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
