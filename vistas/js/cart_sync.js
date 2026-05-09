// vistas/js/cart_sync.js
// Sincroniza el carrito entre localStorage (rápido, offline) y la BD (persistente).
// También actualiza los contadores de la UI (badge en header y sidebar).

(function () {
    'use strict';

    // Detecta la URL base del API automáticamente (funciona esté el sitio en / o en /heron)
    function apiBase() {
        var path = window.location.pathname;
        // estamos dentro de /vistas/xxx.php -> subimos un nivel y vamos a /api
        var idx = path.indexOf('/vistas/');
        if (idx !== -1) return path.substring(0, idx) + '/api';
        return '../api';
    }

    function readLocalCart() {
        try {
            var data = JSON.parse(localStorage.getItem('cart') || '[]');
            return Array.isArray(data) ? data : [];
        } catch (e) { return []; }
    }

    function writeLocalCart(items) {
        localStorage.setItem('cart', JSON.stringify(items || []));
    }

    /** Actualiza los badges del carrito en el header y el sidebar */
    function refreshCartBadges() {
        var cart = readLocalCart();
        var totalItems = cart.reduce(function (s, it) { return s + (parseInt(it.quantity) || 0); }, 0);
        ['headerCartCount', 'sidebarCartCount', 'cartCount'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.textContent = totalItems;
        });
    }

    /** Envía el carrito local al servidor */
    function pushCart() {
        var cart = readLocalCart();
        return fetch(apiBase() + '/cart.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'set', items: cart })
        }).catch(function (e) { console.warn('pushCart error:', e); });
    }

    /** Trae el carrito desde el servidor (útil al hacer login para traer el carrito de invitado) */
    function pullCart() {
        return fetch(apiBase() + '/cart.php')
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data && data.ok && Array.isArray(data.items)) {
                    // Solo sobrescribimos local si el local está vacío,
                    // así no perdemos cosas recién agregadas.
                    var localCart = readLocalCart();
                    if (localCart.length === 0 && data.items.length > 0) {
                        writeLocalCart(data.items);
                    }
                }
                refreshCartBadges();
            })
            .catch(function (e) { console.warn('pullCart error:', e); });
    }

    /** Agrega un servicio al carrito local + sincroniza con servidor */
    function addToCart(item) {
        if (!item || !item.name || !item.price) {
            console.warn('addToCart: item inválido', item);
            return;
        }
        var cart = readLocalCart();
        var found = false;
        for (var i = 0; i < cart.length; i++) {
            if (cart[i].name === item.name) {
                cart[i].quantity = (parseInt(cart[i].quantity) || 0) + (parseInt(item.quantity) || 1);
                found = true;
                break;
            }
        }
        if (!found) {
            cart.push({
                product_id: item.product_id || 0,
                name: item.name,
                price: parseFloat(item.price),
                quantity: parseInt(item.quantity) || 1,
                image: item.image || ''
            });
        }
        writeLocalCart(cart);
        refreshCartBadges();
        pushCart();

        // Aviso visual simple
        try {
            var note = document.createElement('div');
            note.textContent = '✓ ' + item.name + ' agregado al carrito';
            note.style.cssText = 'position:fixed;bottom:24px;right:24px;background:#ffb400;color:#000;' +
                'padding:14px 22px;border-radius:8px;font-weight:600;z-index:99999;box-shadow:0 6px 18px rgba(0,0,0,.25);';
            document.body.appendChild(note);
            setTimeout(function () { note.remove(); }, 2200);
        } catch (e) {}
    }

    function removeFromCart(index) {
        var cart = readLocalCart();
        cart.splice(index, 1);
        writeLocalCart(cart);
        refreshCartBadges();
        pushCart();
    }

    function clearCart() {
        writeLocalCart([]);
        refreshCartBadges();
        pushCart();
    }

    // Exponer funciones al scope global para usarlas desde los onclick de las vistas
    window.HeronCart = {
        read: readLocalCart,
        write: writeLocalCart,
        add: addToCart,
        remove: removeFromCart,
        clear: clearCart,
        push: pushCart,
        pull: pullCart,
        refresh: refreshCartBadges
    };

    // Inicializar al cargar
    document.addEventListener('DOMContentLoaded', function () {
        refreshCartBadges();
        pullCart();
    });
})();
