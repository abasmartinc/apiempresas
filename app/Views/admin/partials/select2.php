<?php
/**
 * Select2 sobre el jQuery de la página (14-10-2026).
 *
 * partials/head.php carga jQuery 3.6.0 con defer. Estas vistas cargaban al final otro jQuery
 * (3.7.1) y select2 sin defer: los scripts con defer se ejecutan DESPUÉS, así que el 3.6.0
 * sustituía al jQuery que tenía select2 y daba "$(...).select2 is not a function".
 *
 * - aeAlCargar(fn): ejecuta fn cuando el DOM está listo, o ya si lo está (con hx-boost la página
 *   se cambia sin recargar el documento y DOMContentLoaded no vuelve a saltar).
 * - aeSelect2(fn, src): espera a jQuery, carga select2 una sola vez sobre ese jQuery y llama a fn($).
 */
?>
<script>
    (function () {
        if (window.aeSelect2) return;
        window.aeAlCargar = function (fn) {
            if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', fn); else fn();
        };
        var cola = [], cargando = false, esperas = 0;
        var listo = function () { return window.jQuery && window.jQuery.fn && window.jQuery.fn.select2; };
        var vaciar = function () {
            var fn;
            while ((fn = cola.shift())) {
                try { fn(window.jQuery); } catch (e) { setTimeout(function () { throw e; }); }
            }
        };
        var arrancar = function (src) {
            if (listo()) return vaciar();
            if (!window.jQuery) {
                if (++esperas < 400) setTimeout(function () { arrancar(src); }, 25); // hasta 10 s
                return;
            }
            if (cargando) return;
            cargando = true;
            var s = document.createElement('script');
            s.src = src;
            s.onload = vaciar;
            s.onerror = function () { cargando = false; };
            document.head.appendChild(s);
        };
        window.aeSelect2 = function (fn, src) {
            cola.push(fn);
            window.aeAlCargar(function () {
                arrancar(src || 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js');
            });
        };
    })();
</script>
