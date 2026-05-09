<?php

require_once __DIR__ . '/../config/init.php';
require_once __DIR__ . '/../config/sat_catalogos.php';
require_login();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Datos fiscales - Heron</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .card-box { background:#fff; border-radius: 14px; box-shadow: 0 10px 30px rgba(0,0,0,.08); padding: 28px; }
        .card-box h2 { color:#222; margin-bottom:6px; }
        .card-box p.sub { color:#888; margin-bottom: 20px; }
        label { font-weight:600; margin-top:12px; display:block; color:#333; }
        input, select { width:100%; padding:11px 14px; border:1px solid #ddd; border-radius:8px; margin-top:4px; font-family: inherit; }
        input:focus, select:focus { outline:none; border-color:#ffb400; }
        .grid { display:grid; grid-template-columns:1fr 1fr; gap:14px; }
        @media(max-width:768px) { .grid{ grid-template-columns:1fr; } }
        .btn-primary-custom { background:#ffb400; color:#000; padding:12px 24px; border:none; border-radius:8px; font-weight:700; cursor:pointer; }
        .btn-primary-custom:hover { background:#e09e00; }
        .btn-outline-custom { background:none; color:#ffb400; border: 2px solid #ffb400; padding:10px 22px; border-radius:8px; font-weight:700; text-decoration:none; display:inline-block; }
        pre#result { margin-top:16px; white-space:pre-wrap; background:#1a1a1a; color:#6de36d; padding:14px; border-radius:8px; font-size:12px; max-height:300px; overflow:auto; }
    </style>
</head>
<body class="main-layout">
<div class="wrapper">
    <?php include 'nav.php'; ?>

    <div id="content">
        <div class="yellow_bg">
            <div class="container">
                <div class="row">
                    <div class="col-md-12"><div class="title"><h2>Datos fiscales</h2></div></div>
                </div>
            </div>
        </div>

        <div class="container" style="max-width: 900px; padding: 40px 15px;">
            <div class="card-box">
                <h2>Registrar datos fiscales</h2>
                <p class="sub">Guarda aquí tus datos para poder generar facturas CFDI 4.0 de tus compras (modo de pruebas / sandbox).</p>

                <div class="grid">
                    <div>
                        <label>RFC</label>
                        <input id="rfc" maxlength="13" placeholder="XAXX010101000" style="text-transform:uppercase;">
                    </div>
                    <div>
                        <label>Código postal fiscal</label>
                        <input id="tax_zip_code" maxlength="5" placeholder="34000">
                    </div>
                    <div style="grid-column:1/-1">
                        <label>Nombre o razón social</label>
                        <input id="razon_social" placeholder="Tal como aparece en tu constancia de situación fiscal">
                    </div>
                    <div>
                        <label>Régimen fiscal</label>
                        <select id="fiscal_regime">
                            <option value="">Selecciona...</option>
                            <?php foreach ($SAT_REGIMENES as $k => $v): ?>
                                <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k . ' - ' . $v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label>Uso del CFDI</label>
                        <select id="cfdi_use">
                            <option value="">Selecciona...</option>
                            <?php foreach ($SAT_CFDI_USES as $k => $v): ?>
                                <option value="<?= htmlspecialchars($k) ?>"><?= htmlspecialchars($k . ' - ' . $v) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div style="grid-column:1/-1">
                        <label>Email para facturas</label>
                        <input id="email" type="email" value="<?= htmlspecialchars(current_user()['email'] ?? '') ?>">
                    </div>
                </div>

                <div style="margin-top:20px; display:flex; gap:10px; flex-wrap:wrap;">
                    <button class="btn-primary-custom" onclick="guardarDatosFiscales()">
                        <i class="fa fa-save"></i>&nbsp; Guardar datos fiscales
                    </button>
                    <a class="btn-outline-custom" href="factura_tu_ticket.php">
                        Ir a facturar una compra
                    </a>
                </div>

                <pre id="result"></pre>
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
async function guardarDatosFiscales(){
    const payload = {
        rfc: document.getElementById('rfc').value.trim().toUpperCase(),
        razon_social: document.getElementById('razon_social').value.trim(),
        tax_zip_code: document.getElementById('tax_zip_code').value.trim(),
        fiscal_regime: document.getElementById('fiscal_regime').value,
        cfdi_use: document.getElementById('cfdi_use').value,
        email: document.getElementById('email').value.trim()
    };

    if (!payload.rfc || !payload.razon_social || !payload.tax_zip_code || !payload.fiscal_regime || !payload.cfdi_use) {
        document.getElementById('result').textContent = 'Por favor completa todos los campos.';
        return;
    }

    // Detectar URL del API
    const path = window.location.pathname;
    const base = path.substring(0, path.indexOf('/vistas/')) + '/api';

    try {
        const res = await fetch(base + '/facturacion/registrar_cliente.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        document.getElementById('result').textContent = JSON.stringify(data, null, 2);
        if (data.ok) {
            alert('Datos fiscales guardados. Ya puedes facturar tus compras.');
        }
    } catch (e) {
        document.getElementById('result').textContent = 'Error: ' + e.message;
    }
}
</script>
</body>
</html>
