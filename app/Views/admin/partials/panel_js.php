<?php
/**
 * Tooltips de los graficos (.gr-hit con data-tip) y ayudas "?" (.gr-help con data-help) de los paneles del admin.
 * Va dentro de .gr-root, al final del contenido.
 */
?>
<div class="gr-tip" id="grTip"></div>
<div class="gr-pop" id="grPop" role="tooltip"></div>
<script>
    (function () {
        const tip = document.getElementById('grTip');
        const pop = document.getElementById('grPop');
        if (!tip || !pop) return;
        document.querySelectorAll('.gr-root .gr-hit').forEach(function (el) {
            el.addEventListener('mousemove', function (e) {
                tip.textContent = el.getAttribute('data-tip');
                tip.style.display = 'block';
                const w = tip.offsetWidth;
                const left = e.clientX + 14 + w > window.innerWidth ? e.clientX - w - 14 : e.clientX + 14;
                tip.style.left = left + 'px';
                tip.style.top = Math.max(8, e.clientY - tip.offsetHeight - 10) + 'px';
            });
            el.addEventListener('mouseleave', function () { tip.style.display = 'none'; });
        });

        // Ayudas "?": al pasar el raton se ve; al hacer clic se queda fija (en el movil, tocar). Esc o clic fuera la cierra.
        let fija = null;
        const colocar = function (btn) {
            pop.innerHTML = btn.getAttribute('data-help');
            pop.style.display = 'block';
            const r = btn.getBoundingClientRect();
            const w = pop.offsetWidth, h = pop.offsetHeight;
            let left = Math.min(Math.max(12, r.left + r.width / 2 - w / 2), window.innerWidth - w - 12);
            let top = r.bottom + 8;
            if (top + h > window.innerHeight - 12) top = Math.max(12, r.top - h - 8);
            pop.style.left = left + 'px';
            pop.style.top = top + 'px';
        };
        const cerrar = function () {
            pop.style.display = 'none';
            if (fija) fija.classList.remove('on');
            fija = null;
        };
        document.querySelectorAll('.gr-root .gr-help').forEach(function (btn) {
            btn.addEventListener('mouseenter', function () { if (!fija) colocar(btn); });
            btn.addEventListener('mouseleave', function () { if (!fija) pop.style.display = 'none'; });
            btn.addEventListener('focus', function () { if (!fija) colocar(btn); });
            btn.addEventListener('blur', function () { if (!fija) pop.style.display = 'none'; });
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                if (fija === btn) { cerrar(); return; }
                if (fija) fija.classList.remove('on');
                fija = btn;
                btn.classList.add('on');
                colocar(btn);
            });
        });
        // Los escuchadores globales se registran una sola vez (con hx-boost la pagina se carga sin recargar el documento)
        // y llaman a la version de cerrar/colocar de la pagina que este pintada ahora.
        window.__grPanel = { cerrar: cerrar, scroll: function () { if (fija && document.body.contains(fija)) colocar(fija); else pop.style.display = 'none'; },
            fuera: function (e) { if (fija && !pop.contains(e.target)) cerrar(); } };
        if (!window.__grPanelGlobal) {
            window.__grPanelGlobal = true;
            document.addEventListener('click', function (e) { if (window.__grPanel) window.__grPanel.fuera(e); });
            document.addEventListener('keydown', function (e) { if (e.key === 'Escape' && window.__grPanel) window.__grPanel.cerrar(); });
            window.addEventListener('scroll', function () { if (window.__grPanel) window.__grPanel.scroll(); }, { passive: true });
        }
    })();
</script>
