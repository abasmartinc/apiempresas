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
                    const v=Math.random().toString(36).slice(2)+Date.now().toString(36);
                    localStorage.setItem('ve_session_id', v); return v;
                })()),
                page_path: window.location.pathname + window.location.search,
                referer: document.referrer || null,
                props
            })
        });
    }catch(e){ /* silencioso */ }
});

(function(){
    // --- referencias ---
    const btn = document.getElementById('btnBuscar');
    const q   = document.getElementById('q');
    const out = document.getElementById('resultado');

    // --- Pinta la ficha registral con datos REALES de la API ---
    function sectionRegistro(company, apiJson) {
        // normalizamos status solo para el color
        const statusRaw = (company.status || '').toString();
        const isActive  = statusRaw.toUpperCase() === 'ACTIVA';

        const statusClass = isActive
            ? 'company-status company-status--active'
            : 'company-status company-status--inactive';

        // cnae + etiqueta
        const cnaeFull = company.cnae && company.cnae_label
            ? `${company.cnae} · ${company.cnae_label}`
            : (company.cnae_label || company.cnae || '-');

        // JSON REAL que devuelve la API (si no viene, montamos algo razonable)
        const jsonForCode = apiJson && typeof apiJson === 'object'
            ? apiJson
            : { success: true, data: company };

        const jsonPretty = JSON
            .stringify(jsonForCode, null, 2)
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
      <div>
        <dt>CIF</dt>
        <dd>${company.cif || company.nif || '-'}</dd>
      </div>
      <div>
        <dt>CNAE</dt>
        <dd>${cnaeFull}</dd>
      </div>

      <div>
        <dt>Provincia</dt>
        <dd>${company.province || company.provincia || '-'}</dd>
      </div>
      <div>
        <dt>Fecha de constitución</dt>
        <dd>${company.incorporation_date || company.founded || company.fecha_constitucion || '-'}</dd>
      </div>

      <div class="company-card__purpose">
        <dt>Objeto social</dt>
        <dd>${company.corporate_purpose || company.objeto_social || '-'}</dd>
      </div>
    </dl>
  </section>

  <div class="company-card__footer">
    <button type="button" class="btn-json-api">Ver JSON de la API</button>
  </div>

  <pre class="company-card__json is-hidden"><code>${jsonPretty}</code></pre>
</article>`;
    }

    // --- Delegación para botón "Ver JSON de la API" ---
    out.addEventListener('click', (e) => {
        const jsonBtn = e.target.closest('.btn-json-api');
        if (!jsonBtn) return;

        e.preventDefault();
        const card = jsonBtn.closest('.company-card');
        if (!card) return;
        const pre = card.querySelector('.company-card__json');
        if (!pre) return;

        const hidden = pre.classList.toggle('is-hidden');
        jsonBtn.textContent = hidden
            ? 'Ver JSON de la API'
            : 'Ocultar JSON de la API';
    });

    // --- Buscar: SOLO llama a la API y pinta lo que venga ---
    btn?.addEventListener('click', async () => {
        const v = (q?.value || '').trim();
        if (!v) {
            out.innerHTML = '<div class="muted">Escribe un CIF (ej. B12345678).</div>';
            return;
        }

        out.innerHTML = '<div class="muted">Buscando empresa en la base de datos...</div>';

        // URL al endpoint de búsqueda
        const endpoint = (window.API_SEARCH_URL || 'search') +
            '?cif=' + encodeURIComponent(v);

        try {
            const res = await fetch(endpoint, {
                method: 'GET',
                headers: { 'Accept': 'application/json' }
            });

            let json = null;
            try {
                json = await res.json();
            } catch(_){}

            // errores HTTP
            if (!res.ok) {
                if (res.status === 404) {
                    const msg404 = json && json.message
                        ? json.message
                        : 'No se encontró ninguna empresa con ese CIF.';
                    out.innerHTML = `<div class="muted">${msg404}</div>`;
                    return;
                }
                if (res.status === 400) {
                    const msg400 = json && json.message
                        ? json.message
                        : 'El CIF no es válido o falta el parámetro.';
                    out.innerHTML = `<div class="muted">${msg400}</div>`;
                    return;
                }
                out.innerHTML = '<div class="muted">Error al consultar la API. Inténtalo de nuevo en unos segundos.</div>';
                return;
            }

            if (!json || json.success === false) {
                const msg = json && json.message
                    ? json.message
                    : 'Se ha producido un error al consultar la empresa.';
                out.innerHTML = `<div class="muted">${msg}</div>`;
                return;
            }

            const company = json.data || {};
            out.innerHTML = sectionRegistro(company, json);

            if (typeof track === 'function') {
                track('search_by_cif', { cif: v });
            }

        } catch (e) {
            console.error(e);
            out.innerHTML = '<div class="muted">Error de conexión con la API. Revisa que el backend esté levantado.</div>';
        }
    });
})();


