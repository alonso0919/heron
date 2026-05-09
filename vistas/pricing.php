<?php

require_once __DIR__ . '/../config/init.php';
$gruposServicios = getServiciosPorCategoria($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Precios y reservas - Heron</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .pricing-group { margin-bottom: 40px; }
        .pricing-group h3 { color:#ffb400; border-bottom: 2px solid #ffb400; padding-bottom: 10px; margin-bottom: 20px; font-size: 26px; text-transform: uppercase; letter-spacing: 1px; }
        .pricing-row { display:flex; align-items:center; justify-content:space-between; padding: 16px 14px; border-bottom: 1px solid #2a2a2a; background:#1a1a1a; color:#fff; margin-bottom: 6px; border-radius:6px; flex-wrap: wrap; gap: 10px; }
        .pricing-row:hover { background:#242424; }
        .pricing-row .info { flex: 1; min-width: 240px; }
        .pricing-row .info h4 { margin: 0 0 4px 0; color:#fff; font-size: 17px; font-weight: 600; }
        .pricing-row .info p { margin: 0; color: #999; font-size: 13px; }
        .pricing-row .price-col { color:#ffb400; font-weight: 700; font-size: 22px; white-space: nowrap; }
        .pricing-row .duration { color:#888; font-size:12px; display:block; font-weight: normal; }
        .pricing-row .add-btn { background:#ffb400; color:#000; border:none; padding:10px 20px; border-radius:6px; font-weight:700; cursor:pointer; white-space:nowrap; }
        .pricing-row .add-btn:hover { background:#e09e00; }
        .cta-cart { background: #ffb400; color:#000; padding: 20px; border-radius: 10px; text-align:center; margin-top: 30px; }
        .cta-cart a { color:#000; font-weight:700; text-decoration:underline; }
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
                            <div class="title"><h2>Precios y reservas</h2></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="container" style="padding: 50px 15px;">
                <p style="text-align:center; font-size:16px; color:#666; margin-bottom: 40px;">
                    Elige tus servicios, agrégalos al carrito y paga en línea con tarjeta, efectivo o transferencia.
                </p>

                <?php foreach ($gruposServicios as $categoria => $serviciosCat): ?>
                    <div class="pricing-group">
                        <h3><?= htmlspecialchars($categoria) ?></h3>
                        <?php foreach ($serviciosCat as $s): ?>
                            <div class="pricing-row">
                                <div class="info">
                                    <h4><?= htmlspecialchars($s['nombre']) ?></h4>
                                    <p><?= htmlspecialchars($s['descripcion']) ?></p>
                                </div>
                                <div class="price-col">
                                    $<?= number_format($s['precio'], 2) ?>
                                    <span class="duration"><?= (int)$s['duracion_min'] ?> min</span>
                                </div>
                                <button type="button" class="add-btn"
                                        onclick="HeronCart.add({product_id: <?= (int)$s['id_servicio'] ?>, name: '<?= htmlspecialchars(addslashes($s['nombre']), ENT_QUOTES) ?>', price: <?= (float)$s['precio'] ?>, image: '<?= htmlspecialchars($s['imagen']) ?>'})">
                                    <i class="fa fa-plus"></i> Agregar
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>

                <div class="cta-cart">
                    <p style="margin:0; font-size:17px; font-weight:600;">
                        <i class="fa fa-shopping-cart"></i>
                        ¿Ya agregaste todo? Ve a <a href="cart.php">tu carrito</a> para pagar y reservar.
                    </p>
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
