<!-- app/Views/search.php -->
<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => !empty($q) ? ('Buscar empresa: ' . $q . ' | APIEmpresas.es') : 'Buscar empresa | APIEmpresas.es',
        'excerptText' => 'Busca empresas por CIF o nombre comercial. Resultados trazables con fuentes oficiales y salida por API.',
        'canonical'   => site_url('search_company') . (!empty($q) ? ('?q=' . rawurlencode($q)) : ''),
        'robots'      => 'noindex,follow',
    ]) ?>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<header>
    <div class="container nav">
        <div class="brand">
            <a href="<?= site_url() ?>">
                <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#2152FF"/>
                            <stop offset=".65" stop-color="#5C7CFF"/>
                            <stop offset="1" stop-color="#12B48A"/>
                        </linearGradient>
                        <filter id="ve-cardShadow" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20"/>
                        </filter>
                        <filter id="ve-checkShadow" x="-30%" y="-30%" width="160%" height="160%">
                            <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="#0B1A36" flood-opacity=".22"/>
                        </filter>
                        <radialGradient id="ve-gloss" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(20 16) rotate(45) scale(28)">
                            <stop stop-color="#FFFFFF" stop-opacity=".32"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity="0"/>
                        </radialGradient>
                        <linearGradient id="ve-rim" x1="12" y1="52" x2="52" y2="12">
                            <stop stop-color="#FFFFFF" stop-opacity=".6"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity=".35"/>
                        </linearGradient>
                    </defs>

                    <g filter="url(#ve-cardShadow)">
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)"/>
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss)"/>
                        <rect x="6.5" y="6.5" width="51" height="51" rx="13.5" fill="none" stroke="url(#ve-rim)"/>
                    </g>

                    <path d="M18 33 L28 43 L46 22"
                          stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"
                          fill="none" filter="url(#ve-checkShadow)"/>
                </svg>
            </a>

            <div class="brand-text">
                <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                <span class="brand-tag">Verificación empresarial en segundos</span>
            </div>
        </div>

        <nav class="desktop-only" aria-label="Principal">
            <a class="minor" href="<?= site_url() ?>dashboard">Dashboard</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
           <!-- <a class="minor" href="<?php /*= site_url() */?>billing">Planes y facturación</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="<?php /*= site_url() */?>usage">Consumo</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>-->
            <a class="minor" href="<?= site_url() ?>documentation">Documentación</a>
        </nav>

        <div class="desktop-only">
            <?php if (!session('logged_in')): ?>
                <a class="btn btn_header btn_header--ghost" href="<?= site_url() ?>enter">
                    <span>Iniciar sesión</span>
                </a>
            <?php else: ?>
                <a class="btn btn_header btn_header--ghost logout" href="<?= site_url() ?>logout">
                    <span>Salir</span>
                </a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main style="padding:40px 0 70px;">
    <section class="container search-section">
        <div class="search-card">
            <div>
                <h2>Prueba el buscador — mismo motor que la API</h2>
                <p class="muted">
                    Introduce un <strong>CIF</strong> o nombre comercial. Verás el resultado en tarjetas limpias
                    y puedes consultar el JSON que devuelve la API.
                </p>
            </div>

            <form class="search-row" method="POST" action="<?= site_url('search_company') ?>" id="searchForm">
                <?= csrf_field() ?>
                <input
                        class="input"
                        id="q"
                        name="q"
                        value="<?= esc($q ?? '') ?>"
                        placeholder="Ej. Gestiones López o B12345678"
                        aria-label="Buscar empresa por nombre o CIF"
                        autocomplete="off"
                />
                <button class="btn" id="btnBuscar" type="submit" aria-label="Buscar">Buscar empresa</button>
            </form>

            <div id="resultado" class="result">
                <?php if (!empty($errorMsg)): ?>
                    <div class="muted"><?= esc($errorMsg) ?></div>
                <?php endif; ?>

                <?php if (!empty($company) && is_array($company)): ?>
                    <?php
                    $statusRaw = (string)($company['status'] ?? '');
                    $isActive  = strtoupper($statusRaw) === 'ACTIVA';
                    $statusClass = $isActive ? 'company-status company-status--active' : 'company-status company-status--inactive';

                    $cnaeFull = (!empty($company['cnae']) && !empty($company['cnae_label']))
                        ? ($company['cnae'] . ' · ' . $company['cnae_label'])
                        : ($company['cnae_label'] ?? ($company['cnae'] ?? '-'));

                    $jsonForCode = ['success' => true, 'data' => $company];
                    $jsonPretty  = json_encode($jsonForCode, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    ?>
                    <article class="company-card">
                        <header class="company-card__header">
                            <div>
                                <div class="company-card__eyebrow">Ficha registral</div>
                                <h3 class="company-card__name"><?= esc($company['name'] ?? '-') ?></h3>
                                <div class="company-card__meta">
                                    <?= esc(($company['cif'] ?? $company['nif'] ?? '-') . ' · ' . ($company['province'] ?? $company['provincia'] ?? '-')) ?>
                                </div>
                            </div>
                            <div class="<?= esc($statusClass) ?>">
                                <span class="company-status__dot"></span>
                                <span><?= esc($statusRaw ?: '-') ?></span>
                            </div>
                        </header>

                        <section class="company-card__body">
                            <dl class="company-card__grid">
                                <div><dt>CIF</dt><dd><?= esc($company['cif'] ?? $company['nif'] ?? '-') ?></dd></div>
                                <div><dt>CNAE</dt><dd><?= esc($cnaeFull ?: '-') ?></dd></div>
                                <div><dt>Provincia</dt><dd><?= esc($company['province'] ?? $company['provincia'] ?? '-') ?></dd></div>
                                <div><dt>Fecha de constitución</dt><dd><?= esc($company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? '-') ?></dd></div>
                                <div class="company-card__purpose"><dt>Objeto social</dt><dd><?= esc($company['corporate_purpose'] ?? $company['objeto_social'] ?? '-') ?></dd></div>
                            </dl>
                        </section>

                        <div class="company-card__footer">
                            <button type="button" class="btn-json-api">Ver JSON de la API</button>
                        </div>

                        <pre class="company-card__json is-hidden"><code><?= esc($jsonPretty) ?></code></pre>
                    </article>
                <?php endif; ?>
            </div>
        </div>
    </section>
</main>

<?= view('partials/footer') ?>


<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    /**
     * Buscador SEO-friendly + robusto:
     * - Evita doble inicialización aunque haya otro JS en view('scripts')
     * - Usa endpoint correcto con site_url('search') (respeta /apiempresas en local)
     * - Evita carreras con AbortController
     * - Usa solo submit (sin keydown enter extra)
     */
    (function () {
        if (window.__APIE_SEARCH_INIT__) return; // ✅ evita duplicados
        window.__APIE_SEARCH_INIT__ = true;

        // Tracking mínimo hacia CodeIgniter (igual que tu versión)
        window.track = window.track || (async function(name, props={}){
            try{
                await fetch('<?= site_url('events/track') ?>', {
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
            } catch(e) { /* silencioso */ }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.getElementById('searchForm');
            const btn  = document.getElementById('btnBuscar');
            const qEl  = document.getElementById('q');
            const out  = document.getElementById('resultado');

            if (!form || !btn || !qEl || !out) return;

            // Endpoint JSON real (IMPORTANTE: site_url para respetar subcarpeta /apiempresas)
            const JSON_ENDPOINT_BASE = '<?= site_url('search') ?>'; // -> http://localhost/apiempresas/search

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
                        const card = b.closest('.company-card');
                        const pre  = card?.querySelector('.company-card__json');
                        if (!pre) return;
                        const nowHidden = pre.classList.toggle('is-hidden');
                        b.textContent = nowHidden ? 'Ver JSON de la API' : 'Ocultar JSON de la API';
                    });
                });
            }

            // Si SSR pintó una card, activamos el botón
            bindJsonButtons(out);

            let currentController = null;
            let lastRequestId = 0;

            async function doSearch(rawValue) {
                const v = (rawValue || '').trim();

                if (!v) {
                    out.innerHTML = '<div class="muted">Escribe un CIF (ej. B12345678).</div>';
                    return;
                }

                // Cancela request anterior (evita “primero error luego ok” por carreras)
                if (currentController) currentController.abort();
                currentController = new AbortController();

                const requestId = ++lastRequestId;

                // UI: limpia y muestra loading (en el MISMO tick)
                out.innerHTML = '<div class="muted">Buscando empresa en la base de datos...</div>';
                btn.disabled = true;

                const endpoint = JSON_ENDPOINT_BASE + '?cif=' + encodeURIComponent(v);

                try {
                    const res = await fetch(endpoint, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Search-Origin': 'web-search',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        signal: currentController.signal
                    });

                    // Si entra una búsqueda nueva, ignoramos esta respuesta
                    if (requestId !== lastRequestId) return;

                    let json = null;
                    try { json = await res.json(); } catch(_) { json = null; }

                    if (!res.ok) {
                        const msg = (json && json.message) ? json.message : 'No se encontró ninguna empresa con ese CIF.';
                        out.innerHTML = `<div class="muted">${msg}</div>`;
                        return;
                    }

                    if (!json || json.success === false) {
                        const msg = (json && json.message) ? json.message : 'Se ha producido un error al consultar la empresa.';
                        out.innerHTML = `<div class="muted">${msg}</div>`;
                        return;
                    }

                    const company = json.data || {};
                    out.innerHTML = sectionRegistro(company, json);
                    bindJsonButtons(out);

                    // URL shareable sin recargar
                    const url = new URL(window.location.href);
                    url.searchParams.set('q', v);
                    window.history.replaceState({}, '', url.toString());

                    if (typeof track === 'function') {
                        track('search_by_cif', { cif: v });
                    }

                } catch (e) {
                    if (e && e.name === 'AbortError') return; // búsqueda cancelada
                    console.error(e);
                    out.innerHTML = '<div class="muted">Error de conexión con la API.</div>';
                } finally {
                    if (requestId === lastRequestId) {
                        btn.disabled = false;
                    }
                }
            }

            // Solo SUBMIT (evita doble disparo con Enter)
            form.addEventListener('submit', (e) => {
                e.preventDefault();
                doSearch(qEl.value);
            });

            // Si vienes con ?q=..., rellena input (no fuerza AJAX; SSR ya pudo pintar)
            const params = new URLSearchParams(window.location.search);
            const qUrl = (params.get('q') || '').trim();
            if (qUrl) qEl.value = qUrl;
        });
    })();
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
</body>
</html>
