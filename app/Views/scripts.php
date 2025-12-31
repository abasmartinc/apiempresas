<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btnBuscar');
        const q   = document.getElementById('q');
        const out = document.getElementById('resultado');

        if (!btn || !q || !out) return;

        function sectionRegistro(company, apiJson) {
            const statusRaw = (company.status || '').toString();
            const isActive  = statusRaw.toUpperCase() === 'ACTIVA';

            const statusClass = isActive
                ? 'company-status company-status--active'
                : 'company-status company-status--inactive';

            const cnaeFull = company.cnae && company.cnae_label
                ? `${company.cnae} · ${company.cnae_label}`
                : (company.cnae_label || company.cnae || '-');

            const jsonForCode = (apiJson && typeof apiJson === 'object')
                ? apiJson
                : { success: true, data: company };

            const jsonPretty = JSON.stringify(jsonForCode, null, 2)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            return `
<article class="company-card">
  <header class="company-card__header">
    <div>
      <div class="company-card__eyebrow">Ficha registral</div>
      <h3 class="company-card__name">${company.name || '-'}</h3>
      <div class="company-card__meta">
        ${(company.cif || company.nif || '-') } · ${(company.province || company.provincia || '-')}
      </div>
    </div>
    <div class="${statusClass}">
      <span class="company-status__dot"></span>
      <span>${statusRaw || '-'}</span>
    </div>
  </header>

  <section class="company-card__body">
    <dl class="company-card__grid">
      <div><dt>CIF</dt><dd>${company.cif || company.nif || '-'}</dd></div>
      <div><dt>CNAE</dt><dd>${cnaeFull}</dd></div>
      <div><dt>Provincia</dt><dd>${company.province || company.provincia || '-'}</dd></div>
      <div><dt>Fecha de constitución</dt><dd>${company.incorporation_date || company.founded || company.fecha_constitucion || '-'}</dd></div>
      <div class="company-card__purpose"><dt>Objeto social</dt><dd>${company.corporate_purpose || company.objeto_social || '-'}</dd></div>
    </dl>
  </section>

  <div class="company-card__footer">
    <button type="button" class="btn-json-api">Ver JSON de la API</button>
  </div>

  <pre class="company-card__json is-hidden"><code>${jsonPretty}</code></pre>
</article>`;
        }

        function bindJsonButtons(container) {
            const buttons = container.querySelectorAll('.btn-json-api');
            buttons.forEach((b) => {
                if (b.dataset.bound === '1') return;
                b.dataset.bound = '1';

                b.addEventListener('click', (e) => {
                    e.preventDefault();
                    e.stopPropagation();

                    const card = b.closest('.company-card');
                    const pre  = card?.querySelector('.company-card__json');
                    if (!pre) return;

                    const nowHidden = pre.classList.toggle('is-hidden');
                    b.textContent = nowHidden ? 'Ver JSON de la API' : 'Ocultar JSON de la API';
                });
            });
        }

        // Endpoint absoluto correcto (respeta subcarpeta /apiempresas)
        const API_URL = '<?= site_url('search') ?>';

        async function doSearch() {
            const v = (q.value || '').trim();

            if (!v) {
                out.innerHTML = '<div class="muted">Escribe un CIF (ej. B12345678).</div>';
                return;
            }

            out.innerHTML = '<div class="muted">Buscando empresa en la base de datos...</div>';
            btn.disabled = true;

            const endpoint = API_URL + '?cif=' + encodeURIComponent(v);

            try {
                const res = await fetch(endpoint, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                });

                let json = null;
                try { json = await res.json(); } catch(_) {}

                if (!res.ok) {
                    const msg = (json && json.message)
                        ? json.message
                        : (res.status === 404 ? 'No se encontró ninguna empresa con ese CIF.' : 'Error al consultar la API.');
                    out.innerHTML = `<div class="muted">${msg}</div>`;
                    return;
                }

                if (!json || json.success === false) {
                    out.innerHTML = `<div class="muted">${(json && json.message) ? json.message : 'Se ha producido un error al consultar la empresa.'}</div>`;
                    return;
                }

                const company = json.data || {};
                out.innerHTML = sectionRegistro(company, json);
                bindJsonButtons(out);

                if (typeof track === 'function') {
                    track('search_by_cif', { cif: v });
                }

            } catch (err) {
                console.error(err);
                out.innerHTML = '<div class="muted">Error de conexión con la API.</div>';
            } finally {
                btn.disabled = false;
            }
        }

        // Click
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            doSearch();
        });

        // Enter en input (sin submit, sin doble)
        q.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                doSearch();
            }
        });
    });
</script>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const logoutLink = document.querySelector('.logout');
        if (!logoutLink) return;

        logoutLink.addEventListener('click', function (e) {
            e.preventDefault();

            const targetUrl = this.getAttribute('href') || '<?= site_url('logout') ?>';

            Swal.fire({
                title: '¿Cerrar sesión?',
                html: 'Se cerrará tu sesión en <strong>APIEmpresas.es</strong> y volverás a la pantalla de acceso.',
                icon: null,
                iconHtml: '<span class="ve-swal-icon-inner">✓</span>',
                showCancelButton: true,
                confirmButtonText: 'Sí, cerrar sesión',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                focusCancel: true,
                customClass: {
                    popup: 've-swal',
                    title: 've-swal-title',
                    htmlContainer: 've-swal-text',
                    confirmButton: 'btn ve-swal-confirm',
                    cancelButton: 'btn btn_header--ghost ve-swal-cancel',
                    icon: 've-swal-icon'
                },
                buttonsStyling: false
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = targetUrl;
                }
            });
        });
    });
</script>
<script>
    (function(){
        const body = document.body;

        function openModal(id){
            const overlay = document.getElementById(id);
            if(!overlay) return;

            overlay.classList.add('active');
            overlay.setAttribute('aria-hidden', 'false');
            body.style.overflow = 'hidden';

            const dialog = overlay.querySelector('.modal');
            if(dialog) dialog.focus({ preventScroll: true });
        }

        function closeModal(overlay){
            if(!overlay) return;

            overlay.classList.remove('active');
            overlay.setAttribute('aria-hidden', 'true');
            body.style.overflow = '';
        }

        // Abrir desde links/buttons con data-open-modal="id"
        document.addEventListener('click', (e) => {
            const opener = e.target.closest('[data-open-modal]');
            if(opener){
                e.preventDefault();
                openModal(opener.getAttribute('data-open-modal'));
                return;
            }

            // Cerrar desde botones con data-close-modal
            const closer = e.target.closest('[data-close-modal]');
            if(closer){
                e.preventDefault();
                const overlay = closer.closest('.modal-overlay');
                closeModal(overlay);
                return;
            }

            // Click fuera del modal (overlay)
            const overlay = e.target.classList && e.target.classList.contains('modal-overlay') ? e.target : null;
            if(overlay){
                closeModal(overlay);
            }
        });

        // ESC para cerrar el modal activo
        document.addEventListener('keydown', (e) => {
            if(e.key !== 'Escape') return;
            const active = document.querySelector('.modal-overlay.active');
            if(active) closeModal(active);
        });
    })();
</script>
