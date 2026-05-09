<?php
require_once __DIR__ . '/../config/init.php';

$barberos = [
    ['nombre' => 'Carlos Ramírez',  'puesto' => 'Barbero principal',     'img' => 'images/1.png'],
    ['nombre' => 'Luis Hernández',  'puesto' => 'Especialista en fade',  'img' => 'images/2.png'],
    ['nombre' => 'Miguel Ángel',    'puesto' => 'Barba y navaja',        'img' => 'images/3.png'],
];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nuestros barberos - Heron</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .barber-card { background:#222; border-radius:12px; overflow:hidden; margin-bottom:30px; text-align:center; color:#fff; box-shadow:0 8px 20px rgba(0,0,0,.15); }
        .barber-card img { width:100%; height:340px; object-fit:cover; }
        .barber-card .body { padding: 20px 15px; }
        .barber-card h3 { color:#ffb400; margin-bottom:4px; }
        .barber-card p { color:#bbb; margin: 0; }
    </style>
</head>
<body class="main-layout">
    <div class="wrapper">
        <?php include 'nav.php'; ?>

        <div id="content">
            <div class="yellow_bg">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12"><div class="title"><h2>Nuestros barberos</h2></div></div>
                    </div>
                </div>
            </div>

            <div class="container" style="padding: 50px 15px;">
                <div class="row">
                    <?php foreach ($barberos as $b): ?>
                        <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                            <div class="barber-card">
                                <img src="<?= htmlspecialchars($b['img']) ?>" alt="<?= htmlspecialchars($b['nombre']) ?>">
                                <div class="body">
                                    <h3><?= htmlspecialchars($b['nombre']) ?></h3>
                                    <p><?= htmlspecialchars($b['puesto']) ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div style="text-align:center; margin-top: 20px;">
                    <a href="pricing.php" style="display:inline-block; background:#ffb400; color:#000; padding:14px 40px; border-radius:6px; font-weight:700; text-decoration:none; font-size:16px;">
                        Reservar un servicio
                    </a>
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
