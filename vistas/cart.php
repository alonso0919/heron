<?php

require_once __DIR__ . '/../config/guard.php'; // require_login()
$user = current_user();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mi carrito - Heron</title>
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <style>
        .cart-wrap { padding: 40px 15px; }
        .cart-card { background:#fff; padding: 24px; border-radius: 12px; box-shadow: 0 8px 24px rgba(0,0,0,.08); margin-bottom: 20px; }
        .cart-item { display:flex; align-items:center; gap:16px; padding:16px 0; border-bottom:1px solid #eee; }
        .cart-item:last-child { border-bottom: none; }
        .cart-item img { width:70px; height:70px; object-fit:cover; border-radius:8px; background:#eee; }
        .cart-item .info { flex:1; }
        .cart-item .info h5 { margin:0 0 4px 0; color:#222; font-weight: 600; }
        .cart-item .info small { color: #888; }
        .cart-item .qty-controls { display:flex; align-items:center; gap:6px; }
        .cart-item .qty-controls button { width:30px; height:30px; border:1px solid #ddd; background:#fafafa; border-radius:6px; cursor:pointer; font-weight: 700; }
        .cart-item .qty-controls input { width:45px; text-align:center; border:1px solid #ddd; border-radius:6px; padding: 6px; }
        .cart-item .remove-btn { color:#c0392b; background:none; border:none; cursor:pointer; font-size:18px; }
        .cart-summary { background:#f8f8f8; padding:20px; border-radius:8px; }
        .cart-summary .total-row { display:flex; justify-content:space-between; margin-bottom:10px; }
        .cart-summary .total-row.total { font-size:22px; font-weight:700; padding-top:14px; border-top:2px solid #ffb400; color:#222; }
        .empty-cart { text-align:center; padding: 60px 20px; color:#888; }
        .empty-cart i { font-size: 64px; color:#ddd; margin-bottom: 14px; }
        .btn-checkout { display:block; width:100%; background:#ffb400; color:#000; border:none; padding:16px; border-radius:8px; font-weight:700; font-size:17px; cursor:pointer; margin-top:14px; }
        .btn-checkout:hover { background:#e09e00; }
        .btn-checkout:disabled { background:#ccc; cursor:not-allowed; }
        .user-info { background:#fffbe6; border:1px solid #ffb400; padding: 10px 14px; border-radius: 8px; font-size:14px; margin-bottom: 16px; }
    </style>
</head>
<body class="main-layout">
    <div class="wrapper">
        <?php include 'nav.php'; ?>

        <div id="content">
            <div class="yellow_bg">
                <div class="container">
                    <div class="row">
                        <div class="col-md-12"><div class="title"><h2>Mi carrito</h2></div></div>
                    </div>
                </div>
            </div>

            <div class="container cart-wrap">
                <div class="user-info">
                    <i class="fa fa-user"></i>&nbsp;
                    Comprando como <strong><?= htmlspecialchars($user['nombre']) ?></strong>
                    (<?= htmlspecialchars($user['email']) ?>)
                </div>

                <div class="row">
                    <div class="col-md-8">
                        <div class="cart-card" id="cartItemsCard">
                            <h3 style="margin-bottom:20px; color:#222;">Servicios</h3>
                            <div id="cartItems"></div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="cart-card">
                            <h3 style="margin-bottom:18px; color:#222;">Resumen</h3>
                            <div class="cart-summary">
                                <div class="total-row">
                                    <span>Productos (<span id="sumCount">0</span>)</span>
                                    <span id="sumSubtotal">$0.00</span>
                                </div>
                                <div class="total-row">
                                    <span>Envío</span>
                                    <span>N/A (servicios)</span>
                                </div>
                                <div class="total-row total">
                                    <span>TOTAL</span>
                                    <span id="sumTotal">$0.00 MXN</span>
                                </div>
                            </div>

                            <button id="btnCheckout" class="btn-checkout" onclick="irACheckout()">
                                <i class="fa fa-lock"></i>&nbsp; Pagar con Conekta
                            </button>

                            <button type="button" onclick="vaciarCarrito()"
                                    style="margin-top:10px; width:100%; background:none; border:1px solid #c0392b; color:#c0392b; padding:10px; border-radius:6px; cursor:pointer;">
                                Vaciar carrito
                            </button>

                            <p style="font-size:12px; color:#888; margin-top:14px; text-align:center;">
                                Pago seguro procesado por Conekta.<br>
                                Aceptamos tarjeta, OXXO y transferencia.
                            </p>
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

    <script>
        // Datos del cliente logueado (se envían al backend para crear la orden en Conekta)
        const CLIENTE = {
            nombre: <?= json_encode($user['nombre']) ?>,
            email: <?= json_encode($user['email']) ?>
        };

        function money(v) { return '$' + Number(v).toFixed(2); }

        function renderCart() {
            const cart = HeronCart.read();
            const itemsDiv = document.getElementById('cartItems');
            const sumCount = document.getElementById('sumCount');
            const sumSubtotal = document.getElementById('sumSubtotal');
            const sumTotal = document.getElementById('sumTotal');
            const btn = document.getElementById('btnCheckout');

            if (cart.length === 0) {
                itemsDiv.innerHTML = `
                    <div class="empty-cart">
                        <i class="fa fa-shopping-cart"></i>
                        <h4>Tu carrito está vacío</h4>
                        <p>Agrega algún servicio para continuar.</p>
                        <a href="pricing.php" style="display:inline-block; margin-top:10px; background:#ffb400; color:#000; padding:10px 24px; border-radius:6px; font-weight:700; text-decoration:none;">Ver precios</a>
                    </div>`;
                sumCount.textContent = 0;
                sumSubtotal.textContent = '$0.00';
                sumTotal.textContent = '$0.00 MXN';
                btn.disabled = true;
                return;
            }

            let total = 0;
            let totalItems = 0;
            let html = '';
            cart.forEach(function (it, idx) {
                const subtotal = it.price * it.quantity;
                total += subtotal;
                totalItems += it.quantity;
                html += `
                <div class="cart-item">
                    <img src="${it.image || 'images/ser.png'}" alt="">
                    <div class="info">
                        <h5>${it.name}</h5>
                        <small>${money(it.price)} c/u &middot; Subtotal: <strong>${money(subtotal)}</strong></small>
                    </div>
                    <div class="qty-controls">
                        <button onclick="updateQty(${idx}, -1)">-</button>
                        <input type="number" min="1" value="${it.quantity}" onchange="setQty(${idx}, this.value)">
                        <button onclick="updateQty(${idx}, 1)">+</button>
                    </div>
                    <button class="remove-btn" onclick="removerItem(${idx})" title="Quitar">
                        <i class="fa fa-trash"></i>
                    </button>
                </div>`;
            });
            itemsDiv.innerHTML = html;
            sumCount.textContent = totalItems;
            sumSubtotal.textContent = money(total);
            sumTotal.textContent = money(total) + ' MXN';
            btn.disabled = false;
        }

        function updateQty(index, delta) {
            const cart = HeronCart.read();
            cart[index].quantity = Math.max(1, (parseInt(cart[index].quantity) || 1) + delta);
            HeronCart.write(cart);
            HeronCart.push();
            HeronCart.refresh();
            renderCart();
        }

        function setQty(index, val) {
            const cart = HeronCart.read();
            const q = Math.max(1, parseInt(val) || 1);
            cart[index].quantity = q;
            HeronCart.write(cart);
            HeronCart.push();
            HeronCart.refresh();
            renderCart();
        }

        function removerItem(index) {
            HeronCart.remove(index);
            renderCart();
        }

        function vaciarCarrito() {
            if (!confirm('¿Vaciar todo el carrito?')) return;
            HeronCart.clear();
            renderCart();
        }

        async function irACheckout() {
            const cart = HeronCart.read();
            if (cart.length === 0) {
                alert('Tu carrito está vacío');
                return;
            }
            const btn = document.getElementById('btnCheckout');
            btn.disabled = true;
            btn.innerHTML = '<i class="fa fa-spinner fa-spin"></i>&nbsp; Redirigiendo a Conekta...';

            try {
                // Detectar URL del API automáticamente
                const path = window.location.pathname;
                const base = path.substring(0, path.indexOf('/vistas/')) + '/api';

                const res = await fetch(base + '/conekta_checkout.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ carrito: cart, cliente: CLIENTE })
                });
                const data = await res.json();
                if (!data.ok) {
                    console.error(data);
                    alert('No se pudo iniciar el pago:\n' + (data.error || 'Error') + '\n\n' + JSON.stringify(data.detail || {}, null, 2));
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fa fa-lock"></i>&nbsp; Pagar con Conekta';
                    return;
                }
                // Redirige al Hosted Payment de Conekta
                window.location.href = data.checkout_url;
            } catch (e) {
                console.error(e);
                alert('Error al iniciar pago. Revisa la consola del navegador.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fa fa-lock"></i>&nbsp; Pagar con Conekta';
            }
        }

        document.addEventListener('DOMContentLoaded', renderCart);
    </script>
</body>
</html>
