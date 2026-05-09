<?php

require_once __DIR__ . '/../config/init.php';
require_login();

$idUsuario = current_user_id();

$stmt = $conn->prepare("SELECT v.id_venta, v.total, v.fecha_venta, v.estado,
    t.numero_ticket, f.facturama_uuid, f.facturama_cfdi_id
    FROM ventas v
    LEFT JOIN tickets t ON t.id_venta = v.id_venta
    LEFT JOIN facturas_emitidas f ON f.id_venta = v.id_venta
    WHERE v.id_usuario = ?
    ORDER BY v.fecha_venta DESC LIMIT 50");
$stmt->bind_param('i', $idUsuario);
$stmt->execute();
$ventas = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mis compras y facturas - Heron</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .card-box { background:#fff; border-radius:14px; box-shadow:0 10px 30px rgba(0,0,0,.08); padding:24px; }
        .card-box h2 { color:#222; margin-bottom:6px; }
        .card-box p.sub { color:#888; margin-bottom:18px; }
        table.invoices-tbl { width:100%; border-collapse:collapse; background:#fff; }
        table.invoices-tbl th { background:#222; color:#ffb400; text-align:left; padding:12px; font-size:13px; text-transform:uppercase; }
        table.invoices-tbl td { padding:12px; border-bottom:1px solid #eee; font-size:14px; }
        .estado-badge { display:inline-block; padding:3px 10px; border-radius:12px; font-size:11px; font-weight:600; text-transform:uppercase; }
        .estado-completado { background:#d4f4dd; color:#1b7a3e; }
        .estado-pendiente { background:#fff4d4; color:#8a6700; }
        .estado-cancelado { background:#f8d7d7; color:#9c2020; }
        .btn-mini { background:#ffb400; color:#000; border:none; padding:7px 14px; border-radius:6px; font-weight:700; cursor:pointer; font-size:13px; text-decoration:none; display:inline-block; margin-right:4px; margin-bottom:4px; }
        .btn-mini:hover { background:#e09e00; color:#000; text-decoration:none; }
        .btn-mini-outline { background:#fff; color:#222; border:1px solid #222; padding:6px 13px; border-radius:6px; font-weight:600; cursor:pointer; font-size:13px; text-decoration:none; display:inline-block; margin-right:4px; margin-bottom:4px; }
        .btn-mini-info { background:#17a2b8; color:#fff; border:none; padding:7px 14px; border-radius:6px; font-weight:700; text-decoration:none; display:inline-block; margin-right:4px; margin-bottom:4px; font-size:13px; }
        .btn-mini-ok { background:#28a745; color:#fff; padding:7px 14px; border-radius:6px; font-size:13px; font-weight:600; }
        pre#salida { margin-top:16px; white-space:pre-wrap; background:#1a1a1a; color:#6de36d; padding:14px; border-radius:8px; font-size:12px; max-height:300px; overflow:auto; }
        .empty { text-align:center; padding: 50px 20px; color:#888; }
        .empty i { font-size:64px; color:#ddd; display:block; margin-bottom:14px; }
    </style>
</head>
<body class="main-layout">
<div class="wrapper">
    <?php include 'nav.php'; ?>

    <div id="content">
        <div class="yellow_bg">
            <div class="container">
                <div class="row">
                    <div class="col-md-12"><div class="title"><h2>Mis compras y facturas</h2></div></div>
                </div>
            </div>
        </div>

        <div class="container" style="max-width:1100px; padding: 40px 15px;">
            <div class="card-box">
                <h2>Historial de compras</h2>
                <p class="sub">
                    Aquí puedes ver tus compras y generar facturas CFDI de las que están completadas.
                    Para generar una factura necesitas primero registrar tus
                    <a href="facturacion.php" style="color:#ffb400; font-weight:600;">datos fiscales</a>.
                </p>

                <?php if (empty($ventas)): ?>
                    <div class="empty">
                        <i class="fa fa-shopping-bag"></i>
                        <h4>Aún no tienes compras</h4>
                        <p>Cuando hagas tu primera reserva, aparecerá aquí.</p>
                        <a href="pricing.php" class="btn-mini">Ver precios</a>
                    </div>
                <?php else: ?>
                    <div style="overflow-x:auto;">
                        <table class="invoices-tbl">
                            <thead>
                                <tr>
                                    <th>Venta</th>
                                    <th>Ticket</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Factura</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($ventas as $v): ?>
                                    <?php $tieneFactura = !empty($v['facturama_cfdi_id']); ?>
                                    <tr>
                                        <td><strong>#<?= (int)$v['id_venta'] ?></strong></td>
                                        <td><?= htmlspecialchars($v['numero_ticket'] ?? '-') ?></td>
                                        <td><?= htmlspecialchars($v['fecha_venta']) ?></td>
                                        <td><strong>$<?= number_format((float)$v['total'], 2) ?></strong></td>
                                        <td>
                                            <span class="estado-badge estado-<?= htmlspecialchars($v['estado']) ?>">
                                                <?= htmlspecialchars($v['estado']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if (!empty($v['facturama_uuid'])): ?>
                                                <small style="color:#28a745;">UUID:</small><br>
                                                <code style="font-size:11px;"><?= htmlspecialchars($v['facturama_uuid']) ?></code>
                                            <?php else: ?>
                                                <small style="color:#888;">Sin generar</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($v['estado'] === 'completado'): ?>
                                                <?php if (!$tieneFactura): ?>
                                                    <button class="btn-mini" onclick="generarFactura(<?= (int)$v['id_venta'] ?>)">
                                                        <i class="fa fa-file-text-o"></i> Generar CFDI
                                                    </button>
                                                <?php else: ?>
                                                    <span class="btn-mini-ok"><i class="fa fa-check"></i> Generado</span><br>
                                                    <a class="btn-mini-info" href="../api/facturacion/descargar_pdf.php?id_venta=<?= (int)$v['id_venta'] ?>" target="_blank" rel="noopener">
                                                        <i class="fa fa-download"></i> PDF
                                                    </a>
                                                <?php endif; ?>
                                                <button class="btn-mini-outline" onclick="verFactura(<?= (int)$v['id_venta'] ?>)">
                                                    <i class="fa fa-eye"></i> Consultar
                                                </button>
                                            <?php else: ?>
                                                <small style="color:#888;">Solo las compras completadas se pueden facturar.</small>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <pre id="salida"></pre>
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
<script>
function apiBase() {
    const path = window.location.pathname;
    return path.substring(0, path.indexOf('/vistas/')) + '/api';
}

async function generarFactura(idVenta) {
    document.getElementById('salida').textContent = 'Generando CFDI en Facturama sandbox...';
    try {
        const res = await fetch(apiBase() + '/facturacion/generar.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_venta: idVenta })
        });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); }
        catch (e) {
            document.getElementById('salida').textContent = 'Respuesta no JSON:\n\n' + text;
            return;
        }
        document.getElementById('salida').textContent = JSON.stringify(data, null, 2);
        if (data.ok) {
            alert('CFDI generado correctamente');
            window.location.reload();
        }
    } catch (err) {
        document.getElementById('salida').textContent = 'Error: ' + err.message;
    }
}

async function verFactura(idVenta) {
    document.getElementById('salida').textContent = 'Consultando...';
    try {
        const res = await fetch(apiBase() + '/facturacion/obtener.php?id_venta=' + encodeURIComponent(idVenta));
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); }
        catch (e) {
            document.getElementById('salida').textContent = 'Respuesta no JSON:\n\n' + text;
            return;
        }
        document.getElementById('salida').textContent = JSON.stringify(data, null, 2);
    } catch (err) {
        document.getElementById('salida').textContent = 'Error: ' + err.message;
    }
}
</script>
</body>
</html>
