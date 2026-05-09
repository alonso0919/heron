<?php

require_once __DIR__ . '/../config/init.php';
$serviciosDestacados = getServicios($conn, true);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Heron Estética &amp; Barbería - Inicio</title>
    <meta name="description" content="Barbería y estética en Durango. Reserva y paga en línea tus servicios.">
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/owl.carousel.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="main-layout">
    <div class="loader_bg">
        <div class="loader"><img src="images/loading.gif" alt="" /></div>
    </div>

    <div class="wrapper">
        <?php include 'nav.php'; ?>

        <div id="content">
            <!-- slider -->
            <div class="slider_section banner_bg">
                <img src="images/banner.jpg" alt="Heron Barbería">
                <div class="container">
                    <div class="text_box">
                        <span>Estilo &amp; Clase</span>
                        <h1>Reserva tu<br> corte en línea</h1>
                        <a href="pricing.php">Ver precios y reservar</a>
                    </div>
                </div>
            </div>
            <!-- end slider -->

            <!-- about -->
            <div id="about" class="about">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <div class="about_box">
                                <span>Bienvenido a nuestra barbería</span>
                                <h2>Acerca de<strong class="white"> Heron</strong></h2>
                                <p>En Heron combinamos la tradición de la barbería clásica con un toque moderno. Nuestros barberos están capacitados para darte el estilo que buscas: desde un corte clásico hasta degradados, arreglo de barba, afeitado tradicional con navaja y tratamientos faciales. Reserva y paga tu servicio en línea de forma segura, recibe tu ticket y, si lo necesitas, tu factura CFDI por correo.</p>
                                <a href="about.php">Conoce más</a>
                            </div>
                        </div>
                        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                            <div class="about_img">
                                <figure><img src="images/about_img.png" alt="Heron" /></figure>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end about -->

            <!-- service -->
            <div id="service" class="service">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="ourheading">
                                <h2>Nuestros<strong class="white_ll"> servicios</strong></h2>
                                <span>Explora lo que nuestros barberos pueden hacer por ti.</span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <?php foreach ($serviciosDestacados as $s): ?>
                            <div class="col-xl-4 col-lg-4 col-md-4 col-sm-12" style="margin-bottom:30px;">
                                <div class="service_box">
                                    <figure><img src="<?= htmlspecialchars($s['imagen'] ?: 'images/ser.png') ?>" alt="<?= htmlspecialchars($s['nombre']) ?>"></figure>
                                    <h3><?= htmlspecialchars($s['nombre']) ?></h3>
                                    <p><?= htmlspecialchars($s['descripcion']) ?></p>
                                    <p style="font-weight:bold; color:#fff; font-size:18px;">
                                        $<?= number_format($s['precio'], 2) ?> MXN
                                        <span style="font-size:12px; font-weight:normal; opacity:.8;">&middot; <?= (int)$s['duracion_min'] ?> min</span>
                                    </p>
                                    <button type="button"
                                            onclick="HeronCart.add({product_id: <?= (int)$s['id_servicio'] ?>, name: '<?= htmlspecialchars(addslashes($s['nombre'])) ?>', price: <?= (float)$s['precio'] ?>, image: '<?= htmlspecialchars($s['imagen']) ?>'})"
                                            style="margin-top:8px; background:#ffb400; color:#000; border:none; padding:10px 22px; border-radius:6px; font-weight:700; cursor:pointer;">
                                        Agregar al carrito
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="text-center" style="margin-top:20px;">
                        <a href="pricing.php" style="display:inline-block; background:#ffb400; color:#000; padding:12px 30px; border-radius:6px; font-weight:700; text-decoration:none;">Ver todos los servicios &amp; reservar</a>
                    </div>
                </div>
            </div>
            <!-- end service -->

            <!-- horarios -->
            <div class="opening" style="padding: 40px 0;">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="ourheading">
                                <h2>Horario de<strong class="white"> atención</strong></h2>
                            </div>
                        </div>
                    </div>
                    <div class="opening_bg">
                        <div class="row">
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                <div class="times">
                                    <ul>
                                        <li><span>Lunes</span><span class="float-right">9:00am <strong class="bbbb">9:00pm</strong></span></li>
                                        <li><span>Martes</span><span class="float-right">9:00am <strong class="bbbb">9:00pm</strong></span></li>
                                        <li><span>Miércoles</span><span class="float-right">9:00am <strong class="bbbb">9:00pm</strong></span></li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-xl-6 col-lg-6 col-md-6 col-sm-12">
                                <div class="times">
                                    <ul>
                                        <li><span>Jueves</span><span class="float-right">9:00am <strong class="bbbb">9:00pm</strong></span></li>
                                        <li><span>Viernes</span><span class="float-right">9:00am <strong class="bbbb">9:00pm</strong></span></li>
                                        <li><span>Sábado</span><span class="float-right">9:00am <strong class="bbbb">6:00pm</strong></span></li>
                                        <li><span>Domingo</span><span class="float-right">Cerrado</span></li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include 'footer.php'; ?>
        </div>

        <div class="overlay"></div>

        <!-- JS -->
        <script src="js/jquery.min.js"></script>
        <script src="js/popper.min.js"></script>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/owl.carousel.min.js"></script>
        <script src="js/custom.js"></script>
        <script src="js/jquery.mCustomScrollbar.concat.min.js"></script>
        <script src="js/cart_sync.js"></script>
    </div>
</body>
</html>
