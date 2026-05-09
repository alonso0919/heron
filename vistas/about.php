<?php
// vistas/about.php - Página "Nosotros"
require_once __DIR__ . '/../config/init.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nosotros - Heron Barbería</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="main-layout">
    <div class="wrapper">
        <?php include 'nav.php'; ?>

        <div id="content">
            <div class="yellow_bg">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="title"><h2>Nosotros</h2></div>
                        </div>
                    </div>
                </div>
            </div>

            <div id="about" class="about">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <div class="about_box">
                                <span>Nuestra historia</span>
                                <h2>Heron<strong class="white"> Barbería</strong></h2>
                                <p>Somos una estética y barbería de Durango que combina técnica clásica con tendencias modernas. Nuestro equipo se capacita constantemente para ofrecer cortes, arreglo de barba, afeitado con navaja, coloración y tratamientos faciales al mejor nivel.</p>
                                <p>Creemos que un buen corte es una experiencia: desde el momento en que entras, el café, la música y el trato personalizado son tan importantes como el resultado en el espejo.</p>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <div class="about_img">
                                <figure><img src="images/about_img.png" alt="Heron"/></figure>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="margin-top:40px;">
                        <div class="col-md-4 text-center" style="padding:20px;">
                            <i class="fa fa-scissors" style="font-size:48px;color:#ffb400;"></i>
                            <h3 style="margin-top:12px;">Barberos expertos</h3>
                            <p>Equipo certificado con años de experiencia en cortes clásicos y modernos.</p>
                        </div>
                        <div class="col-md-4 text-center" style="padding:20px;">
                            <i class="fa fa-clock-o" style="font-size:48px;color:#ffb400;"></i>
                            <h3 style="margin-top:12px;">Puntualidad</h3>
                            <p>Respetamos tu tiempo. Reserva con horario y te atendemos sin esperas.</p>
                        </div>
                        <div class="col-md-4 text-center" style="padding:20px;">
                            <i class="fa fa-credit-card" style="font-size:48px;color:#ffb400;"></i>
                            <h3 style="margin-top:12px;">Pago en línea</h3>
                            <p>Paga con tarjeta, efectivo o transferencia y recibe tu factura CFDI al instante.</p>
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
