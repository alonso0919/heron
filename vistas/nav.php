<?php

require_once __DIR__ . '/../config/init.php';
$user = current_user();
$currentPage = basename($_SERVER['PHP_SELF']);

function nav_active($page, $currentPage) {
    return $page === $currentPage ? 'active' : '';
}
?>
<div class="sidebar">
    <!-- Sidebar -->
    <nav id="sidebar">
        <div id="dismiss">
            <i class="fa fa-arrow-left"></i>
        </div>
        <ul class="list-unstyled components">
            <li class="<?= nav_active('index.php', $currentPage) ?>">
                <a href="index.php">Inicio</a>
            </li>
            <li class="<?= nav_active('about.php', $currentPage) ?>">
                <a href="about.php">Nosotros</a>
            </li>
            <li class="<?= nav_active('service.php', $currentPage) ?>">
                <a href="service.php">Servicios</a>
            </li>
            <li class="<?= nav_active('pricing.php', $currentPage) ?>">
                <a href="pricing.php">Precios / Reservar</a>
            </li>
            <li class="<?= nav_active('barbers.php', $currentPage) ?>">
                <a href="barbers.php">Barberos</a>
            </li>
            <li class="<?= nav_active('contact.php', $currentPage) ?>">
                <a href="contact.php">Contacto</a>
            </li>
            <li class="<?= nav_active('cart.php', $currentPage) ?>">
                <a href="cart.php">
                    Mi Carrito
                    <span class="cart-count-badge" id="sidebarCartCount" style="background:#ffb400;color:#000;border-radius:50%;padding:2px 8px;margin-left:6px;font-size:12px;font-weight:bold;">0</span>
                </a>
            </li>
            <?php if ($user): ?>
                <li class="<?= nav_active('factura_tu_ticket.php', $currentPage) ?>">
                    <a href="factura_tu_ticket.php">Mis compras / Facturar</a>
                </li>
                <li style="border-top:1px solid rgba(255,255,255,.1); margin-top:10px; padding-top:10px;">
                    <a href="#" style="pointer-events:none;opacity:.8;">
                        <i class="fa fa-user"></i>&nbsp; <?= htmlspecialchars($user['nombre']) ?>
                    </a>
                </li>
                <li>
                    <a href="logout.php">
                        <i class="fa fa-sign-out"></i>&nbsp; Cerrar sesión
                    </a>
                </li>
            <?php else: ?>
                <li style="border-top:1px solid rgba(255,255,255,.1); margin-top:10px; padding-top:10px;"
                    class="<?= nav_active('login.php', $currentPage) ?>">
                    <a href="login.php">Iniciar sesión</a>
                </li>
                <li class="<?= nav_active('register.php', $currentPage) ?>">
                    <a href="register.php">Registrarme</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>

<header>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-3">
                <div class="full">
                    <a class="logo" href="index.php"><img src="images/logo.png" alt="Heron" /></a>
                </div>
            </div>
            <div class="col-md-9">
                <div class="full">
                    <div class="right_header_info">
                        <ul>
                            <li class="dinone">
                                <img style="margin-right:15px;margin-left:15px;" src="images/phone_icon.png" alt="#">
                                <a href="contact.php">618-123-4567</a>
                            </li>
                            <li class="dinone">
                                <img style="margin-right:15px;" src="images/mail_icon.png" alt="#">
                                <a href="contact.php">contacto@heron.mx</a>
                            </li>
                            <li class="dinone">
                                <a href="cart.php" style="position:relative;display:inline-block;padding:0 15px;color:#fff;">
                                    <i class="fa fa-shopping-cart" style="font-size:20px;"></i>
                                    <span id="headerCartCount"
                                          style="position:absolute;top:-6px;right:6px;background:#ffb400;color:#000;border-radius:50%;padding:0 6px;font-size:11px;font-weight:bold;">0</span>
                                </a>
                            </li>
                            <li class="button_user">
                                <a class="button" href="pricing.php">Reservar</a>
                            </li>
                            <?php if ($user): ?>
                                <li class="dinone">
                                    <span style="color:#fff; padding: 0 10px;">
                                        <i class="fa fa-user"></i> <?= htmlspecialchars(strtok($user['nombre'],' ')) ?>
                                    </span>
                                </li>
                            <?php else: ?>
                                <li class="dinone">
                                    <a href="login.php" style="color:#fff; padding: 0 10px;">
                                        <i class="fa fa-sign-in"></i> Entrar
                                    </a>
                                </li>
                            <?php endif; ?>
                            <li>
                                <button type="button" id="sidebarCollapse">
                                    <a href="#">MENU</a>
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
