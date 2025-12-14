<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    // Tracking mínimo hacia CodeIgniter. Ajusta la ruta si hiciera falta.
    window.track = window.track || (async function(name, props={}){
        try{
            await fetch('/events/track', {
                method:'POST',
                headers:{'Content-Type':'application/json'},
                credentials: 'same-origin',
                body: JSON.stringify({
                    name,
                    session_id: (localStorage.getItem('ve_session_id') || (function(){
                        const v = Math.random().toString(36).slice(2) + Date.now().toString(36);
                        localStorage.setItem('ve_session_id', v);
                        return v;
                    })()),
                    page_path: window.location.pathname + window.location.search,
                    referer: document.referrer || null,
                    props
                })
            });
        }catch(e){ /* silencioso */ }
    });

    document.addEventListener('DOMContentLoaded', () => {
        // --- referencias ---
        const btn = document.getElementById('btnBuscar');
        const q   = document.getElementById('q');
        const out = document.getElementById('resultado');

        if (!btn || !q || !out) {
            console.warn('Faltan elementos del buscador:', { btn: !!btn, q: !!q, out: !!out });
            return;
        }

        // --- Render de la tarjeta ---
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

        // --- Engancha botones JSON SOLO dentro del contenedor (después de pintar) ---
        function bindJsonButtons(container) {
            const buttons = container.querySelectorAll('.btn-json-api');

            buttons.forEach((b) => {
                if (b.dataset.bound === '1') return; // evita doble binding
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

        // (Opcional) buscar con Enter
        q.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') btn.click();
        });

        // --- Buscar: llama a API y pinta lo que venga ---
        btn.addEventListener('click', async () => {
            const v = (q.value || '').trim();

            if (!v) {
                out.innerHTML = '<div class="muted">Escribe un CIF (ej. B12345678).</div>';
                return;
            }

            out.innerHTML = '<div class="muted">Buscando empresa en la base de datos...</div>';

            const endpoint = (window.API_SEARCH_URL || 'search') + '?cif=' + encodeURIComponent(v);

            try {
                const res = await fetch(endpoint, {
                    method: 'GET',
                    headers: { 'Accept': 'application/json' }
                });

                let json = null;
                try { json = await res.json(); } catch(_) {}

                if (!res.ok) {
                    if (res.status === 404) {
                        out.innerHTML = `<div class="muted">${(json && json.message) ? json.message : 'No se encontró ninguna empresa con ese CIF.'}</div>`;
                        return;
                    }
                    if (res.status === 400) {
                        out.innerHTML = `<div class="muted">${(json && json.message) ? json.message : 'El CIF no es válido o falta el parámetro.'}</div>`;
                        return;
                    }

                    out.innerHTML = `<div class="muted">${(json && json.message) ? json.message : 'Error al consultar la API. Inténtalo de nuevo.'}</div>`;
                    return;
                }

                if (!json || json.success === false) {
                    out.innerHTML = `<div class="muted">${(json && json.message) ? json.message : 'Se ha producido un error al consultar la empresa.'}</div>`;
                    return;
                }

                const company = json.data || {};
                out.innerHTML = sectionRegistro(company, json);

                // Enganchar evento después de pintar
                bindJsonButtons(out);

                if (typeof track === 'function') {
                    track('search_by_cif', { cif: v });
                }

            } catch (e) {
                console.error(e);
                out.innerHTML = '<div class="muted">Error de conexión con la API. Revisa que el backend esté levantado.</div>';
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
