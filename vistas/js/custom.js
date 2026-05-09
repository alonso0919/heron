/*---------------------------------------------------------------------
    custom.js (versión limpia para Heron)
    Solo mantenemos lo que realmente se usa en el proyecto:
      - Preloader (pantalla de carga inicial)
      - Toggle del sidebar (botón MENU)
      - Cierre del sidebar (botón X dentro del sidebar)
      - Carrusel Owl (si está presente en la página)

    Se quitaron: Swiper, slick, fancybox, countdown, niceSelect, niceScroll,
    meanmenu, jQuery Validate, y el tracker externo de leostop.com que
    traía el template por default.
---------------------------------------------------------------------*/

$(function () {
    "use strict";

    /* Preloader: oculta la pantalla de carga después de 1.5s */
    setTimeout(function () {
        $('.loader_bg').fadeToggle();
    }, 1500);

    /* Toggle del sidebar (botón "MENU" arriba a la derecha) */
    $('#sidebarCollapse').on('click', function (e) {
        e.preventDefault();
        $('#sidebar').toggleClass('active');
        $(this).toggleClass('active');
        $('.overlay').fadeToggle();
    });

    /* Cerrar sidebar con el botón X (flecha) dentro del propio sidebar */
    $('#dismiss, .overlay').on('click', function () {
        $('#sidebar').removeClass('active');
        $('.overlay').fadeOut();
    });

    /* Carrusel Owl (solo se inicializa si existe en la página) */
    if ($('.owl-carousel').length > 0 && typeof $.fn.owlCarousel === 'function') {
        $('.owl-carousel').owlCarousel({
            items: 3,
            loop: true,
            margin: 20,
            autoplay: true,
            autoplayTimeout: 4000,
            responsive: {
                0:    { items: 1 },
                600:  { items: 2 },
                1000: { items: 3 }
            }
        });
    }
});
