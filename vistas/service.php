<?php

require_once __DIR__ . '/../config/init.php';
$servicios = getServicios($conn, false);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Servicios - Heron Barbería</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .service-card { background:#222; color:#fff; border-radius:12px; overflow:hidden; margin-bottom:28px; box-shadow:0 8px 20px rgba(0,0,0,.15); transition: transform .2s; height: 100%; display:flex; flex-direction:column; }
        .service-card:hover { transform: translateY(-4px); }
        .service-card img { width:100%; height:180px; object-fit:cover; background:#111; }
        .service-card .body { padding: 20px; flex: 1; display:flex; flex-direction:column; }
        .service-card h3 { color:#ffb400; margin-bottom:8px; font-size:20px; }
        .service-card .cat { display:inline-block; background:rgba(255,180,0,.15); color:#ffb400; padding:3px 10px; border-radius:20px; font-size:11px; font-weight:600; margin-bottom:10px; }
        .service-card p { font-size:14px; color:#ccc; margin-bottom:12px; flex:1; }
        .service-card .price { font-size:22px; font-weight:700; color:#fff; }
        .service-card .duration { font-size:12px; color:#888; margin-left:8px; }
        .service-card button { margin-top:14px; background:#ffb400; color:#000; border:none; padding:11px 0; width:100%; border-radius:6px; font-weight:700; cursor:pointer; font-size:14px; }
        .service-card button:hover { background:#e09e00; }
    </style>
</head>
<body class="main-layout">
    <div class="wrapper">
        <?php include 'nav.php'; ?>

        <div id="content">
            <div class="yellow_bg">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="title"><h2>Nuestros servicios</h2></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container" style="padding: 50px 15px;">
                <div class="row">
                    <?php if (empty($servicios)): ?>
                        <div class="col-md-12 text-center">
                            <p>No hay servicios disponibles por el momento.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($servicios as $s): ?>
                            <div class="col-xl-4 col-lg-4 col-md-6 col-sm-12">
                                <div class="service-card">
                                    <img src="<?= htmlspecialchars($s['imagen'] ?: 'images/ser.png') ?>" alt="<?= htmlspecialchars($s['nombre']) ?>">
                                    <div class="body">
                                        <span class="cat"><?= htmlspecialchars($s['categoria']) ?></span>
                                        <h3><?= htmlspecialchars($s['nombre']) ?></h3>
                                        <p><?= htmlspecialchars($s['descripcion']) ?></p>
                                        <div>
                                            <span class="price">$<?= number_format($s['precio'], 2) ?></span>
                                            <span class="duration">&middot; <?= (int)$s['duracion_min'] ?> min</span>
                                        </div>
                                        <button type="button"
                                                onclick="HeronCart.add({product_id: <?= (int)$s['id_servicio'] ?>, name: '<?= htmlspecialchars(addslashes($s['nombre']), ENT_QUOTES) ?>', price: <?= (float)$s['precio'] ?>, image: '<?= htmlspecialchars($s['imagen']) ?>'})">
                                            <i class="fa fa-cart-plus"></i>&nbsp; Agregar al carrito
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
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
