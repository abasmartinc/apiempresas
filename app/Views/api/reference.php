<?php
$lang = ($lang ?? 'es') === 'en' ? 'en' : 'es';
$en   = $lang === 'en';
// Textos de la página en los dos idiomas (13-10-2026)
$t = $en ? [
    'title'   => 'API Reference | Spain Company API',
    'desc'    => 'Spanish company data API reference: endpoints, parameters, response examples and Sandbox testing without using requests.',
    'tag'     => 'API reference',
    'brand'   => 'SpainCompanyAPI.com',
    'pricing' => site_url('spanish-company-data-api'),
    'pricingL'=> 'Pricing',
    'guide'   => site_url('docs'),
    'guideL'  => 'Guide',
    'ref'     => site_url('api/reference'),
    'refL'    => 'Reference',
    'cta'     => site_url('register?intent=api&source=api_reference_en'),
    'ctaL'    => 'Create free account',
    'spec'    => site_url('api/reference/openapi.json'),
    'paste'   => 'Paste your API key here',
    'close'   => 'Close',
] : [
    'title'   => 'Referencia de la API | APIEmpresas',
    'desc'    => 'Referencia de la API de empresas españolas: rutas, parámetros, ejemplos de respuesta y pruebas en el Sandbox sin gastar consultas.',
    'tag'     => 'Referencia de la API',
    'brand'   => 'APIEmpresas.es',
    'pricing' => site_url() . '#precios',
    'pricingL'=> 'Precios',
    'guide'   => site_url('documentation'),
    'guideL'  => 'Guía',
    'ref'     => site_url('api/referencia'),
    'refL'    => 'Referencia',
    'cta'     => site_url('register?intent=api&source=api_reference'),
    'ctaL'    => 'Crear cuenta gratis',
    'spec'    => site_url('api/referencia/openapi.json'),
    'paste'   => 'Pega aquí tu API Key',
    'close'   => 'Cerrar',
];
?>
<!doctype html>
<html lang="<?= $lang ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($t['title']) ?></title>
    <meta name="description" content="<?= esc($t['desc']) ?>">
    <?php /* Convive con /documentation hasta decidir cuál queda: no se indexa todavía */ ?>
    <meta name="robots" content="noindex, follow">
    <link rel="icon" href="<?= base_url('favicon-32x32.png') ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300..900&family=JetBrains+Mono:wght@400;600&display=swap">
    <style>
        /* Mismo aspecto que la cabecera de la web (styles.css: header.main-site-header, .nav .btn_header) */
        :root { --ar-header-h: 64px; }
        body { margin: 0; background: #fff; font-family: Inter, system-ui, -apple-system, "Segoe UI", sans-serif; }
        .ar-header { position: sticky; top: 0; z-index: 1000; height: var(--ar-header-h); box-sizing: border-box;
            display: flex; align-items: center; justify-content: space-between; gap: 16px; padding: 0 24px;
            background: linear-gradient(180deg, #133A82 0%, #0B2254 100%); box-shadow: 0 8px 30px rgba(0, 0, 0, .25);
            border-bottom: 1px solid rgba(255, 255, 255, .04); }
        .ar-brand { display: flex; align-items: center; gap: 12px; text-decoration: none; min-width: 0; }
        .ar-brand-text { display: flex; flex-direction: column; line-height: 1.1; }
        .ar-brand-name { color: #fff; font-weight: 800; font-size: 1.05rem; letter-spacing: .1px; white-space: nowrap; }
        .ar-brand-tag { color: rgba(255, 255, 255, .7); font-size: .78rem; font-weight: 500; margin-top: 3px; white-space: nowrap; }
        .ar-nav { display: flex; align-items: center; gap: 22px; }
        .ar-nav a.ar-link { color: #fff; font-weight: 700; font-size: .93rem; text-decoration: none; opacity: .9; }
        .ar-nav a.ar-link:hover, .ar-nav a.ar-link.is-active { opacity: 1; text-decoration: underline; text-underline-offset: 6px; text-decoration-color: #FACC15; }
        .ar-cta { display: inline-flex; align-items: center; padding: .5rem 1.4rem; border-radius: 999px; font-weight: 600; font-size: .9rem;
            text-decoration: none; color: #0B1020; white-space: nowrap;
            background: radial-gradient(circle at 0 0, #FEFCE8 0, #FACC15 35%, #F97316 100%);
            box-shadow: 0 0 0 1px rgba(248, 250, 252, .18), 0 12px 30px rgba(37, 99, 235, .55); transition: transform .12s; }
        .ar-cta:hover { transform: translateY(-1px); }
        @media (max-width: 760px) { .ar-brand-tag, .ar-hide-sm { display: none; } .ar-header { padding: 0 14px; } .ar-nav { gap: 12px; } .ar-cta { padding: .45rem 1rem; } }

        /* Scalar con los colores de la web (modo claro) */
        .light-mode {
            --scalar-custom-header-height: var(--ar-header-h);
            --scalar-font: Inter, system-ui, -apple-system, "Segoe UI", sans-serif;
            --scalar-font-code: "JetBrains Mono", Consolas, monospace;
            --scalar-color-1: #0F172A;
            --scalar-color-2: #475569;
            --scalar-color-3: #64748B;
            --scalar-color-accent: #2563EB;
            --scalar-background-1: #FFFFFF;
            --scalar-background-2: #F8FAFC;
            --scalar-background-3: #EEF2F7;
            --scalar-background-accent: #EFF6FF;
            --scalar-border-color: #E2E8F0;
            --scalar-link-color: #2563EB;
            --scalar-link-color-hover: #1D4ED8;
            --scalar-button-1: #2563EB;
            --scalar-button-1-color: #FFFFFF;
            --scalar-button-1-hover: #1D4ED8;
            --scalar-radius: 8px;
            --scalar-radius-lg: 12px;
            --scalar-radius-xl: 16px;
            --scalar-color-green: #059669;
            --scalar-color-blue: #2563EB;
            --scalar-color-orange: #EA580C;
            --scalar-color-red: #DC2626;
            --scalar-sidebar-background-1: #F8FAFC;
            --scalar-sidebar-color-1: #0F172A;
            --scalar-sidebar-color-2: #475569;
            --scalar-sidebar-border-color: #E2E8F0;
            --scalar-sidebar-item-hover-background: #EEF2F7;
            --scalar-sidebar-item-hover-color: #0F172A;
            --scalar-sidebar-item-active-background: #DBEAFE;
            --scalar-sidebar-color-active: #1D4ED8;
            --scalar-sidebar-search-background: #FFFFFF;
            --scalar-sidebar-search-border-color: #E2E8F0;
            --scalar-sidebar-search-color: #64748B;
        }
        /* Títulos con el peso de la web */
        .light-mode .section-header, .light-mode h1, .light-mode h2 { letter-spacing: -0.02em; }
        .light-mode .section-header-label, .light-mode h1 { font-weight: 800 !important; }
        /* Tablas del texto: no partir palabras en las dos primeras columnas */
        .markdown table { table-layout: auto !important; }
        .markdown td:nth-child(-n+2), .markdown th:nth-child(-n+2) { word-break: normal !important; overflow-wrap: normal !important; hyphens: none !important; }
        .markdown td code { white-space: nowrap; }
        /* Botón de cerrar del probador: Scalar lo saca fijo a la esquina de la pantalla (fuera de la
           ventana y encima de esta cabecera). Se deja dentro de la barra superior del probador,
           en el hueco de la derecha, como un botón "Cerrar" visible. */
        .app-exit-button { position: static !important; display: inline-flex !important; align-items: center; gap: 6px;
            padding: 6px 14px !important; border-radius: 8px !important; background: #0F172A !important;
            color: #FFFFFF !important; opacity: 1 !important; font: 600 13px/1 Inter, system-ui, sans-serif; cursor: pointer; }
        .app-exit-button::after { content: "<?= $t['close'] ?>"; }
        .app-exit-button svg { width: 16px !important; height: 16px !important; }
        .app-exit-button:hover { background: #2563EB !important; }
    </style>
</head>
<body>
    <header class="ar-header">
        <a class="ar-brand" href="<?= site_url() ?>">
            <svg class="ar-logo" width="30" height="30" viewBox="0 0 64 64" aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#2152FF" />
                            <stop offset=".65" stop-color="#5C7CFF" />
                            <stop offset="1" stop-color="#12B48A" />
                        </linearGradient>
                        <filter id="ve-cardShadow" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20" />
                        </filter>
                        <filter id="ve-checkShadow" x="-30%" y="-30%" width="160%" height="160%">
                            <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="#0B1A36" flood-opacity=".22" />
                        </filter>
                        <radialGradient id="ve-gloss" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                            gradientTransform="translate(20 16) rotate(45) scale(28)">
                            <stop stop-color="#FFFFFF" stop-opacity=".32" />
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity="0" />
                        </radialGradient>
                        <linearGradient id="ve-rim" x1="12" y1="52" x2="52" y2="12">
                            <stop stop-color="#FFFFFF" stop-opacity=".6" />
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity=".35" />
                        </linearGradient>
                    </defs>
                    <g filter="url(#ve-cardShadow)">
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)" />
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss)" />
                        <rect x="6.5" y="6.5" width="51" height="51" rx="13.5" fill="none" stroke="url(#ve-rim)" />
                    </g>
                    <path d="M18 33 L28 43 L46 22" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round"
                        stroke-linejoin="round" fill="none" filter="url(#ve-checkShadow)" />
                </svg>
            <span class="ar-brand-text">
                <span class="ar-brand-name"><?= esc($t['brand']) ?></span>
                <span class="ar-brand-tag"><?= esc($t['tag']) ?></span>
            </span>
        </a>
        <nav class="ar-nav" aria-label="Principal">
            <a class="ar-link ar-hide-sm" href="<?= $t['pricing'] ?>"><?= esc($t['pricingL']) ?></a>
            <a class="ar-link ar-hide-sm" href="<?= $t['guide'] ?>"><?= esc($t['guideL']) ?></a>
            <a class="ar-link ar-hide-sm is-active" href="<?= $t['ref'] ?>"><?= esc($t['refL']) ?></a>
            <a class="ar-cta" href="<?= $t['cta'] ?>"><?= esc($t['ctaL']) ?></a>
        </nav>
    </header>
    <div id="app"></div>

    <script src="https://cdn.jsdelivr.net/npm/@scalar/api-reference@1.72.0/dist/browser/standalone.js"></script>
    <script>
        var ORDEN = <?= json_encode($orden ?? []) ?>;
        function posicion(op) { var i = ORDEN.indexOf(String(op.method).toLowerCase() + ' ' + op.path); return i < 0 ? 999 : i; }
        Scalar.createApiReference('#app', {
            operationsSorter: function (a, b) { return posicion(a) - posicion(b); },
            url: <?= json_encode($t['spec']) ?>,
            theme: 'none',
            withDefaultFonts: false,
            forceDarkModeState: 'light',
            hideDarkModeToggle: true,
            layout: 'modern',
            hideClientButton: true, // el botón "Abrir cliente API" lleva a client.scalar.com
            defaultHttpClient: { targetKey: 'shell', clientKey: 'curl' },
            hiddenClients: {
                c: true, clojure: true, csharp: true, dart: true, fsharp: true, go: true, http: true,
                java: true, kotlin: true, objc: true, ocaml: true, powershell: true, r: true,
                ruby: true, rust: true, swift: true
            },
            authentication: { preferredSecurityScheme: 'ApiKeyAuth' },
            localization: { locale: '<?= $lang ?>' },
            showDeveloperTools: 'never',
            agent: { disabled: true },
            mcp: { disabled: true },
            metaData: { title: <?= json_encode($t['title']) ?> }
        });

        // El campo de la API Key trae de fábrica un texto de ejemplo en base64 que parece una clave.
        // Scalar no deja cambiarlo por configuración: se sustituye al pintarse (13-10-2026).
        (function () {
            var EJEMPLO = 'QUxMIFlPVVIgQkFTRSBBUkUgQkVMT05HIFRPIFVT';
            function cambiar(raiz) {
                (raiz.querySelectorAll ? raiz : document).querySelectorAll('[placeholder="' + EJEMPLO + '"]').forEach(function (el) {
                    el.setAttribute('placeholder', <?= json_encode($t['paste']) ?>);
                });
            }
            new MutationObserver(function () { cambiar(document); })
                .observe(document.getElementById('app'), { childList: true, subtree: true, attributes: true, attributeFilter: ['placeholder'] });
            cambiar(document);
        })();
    </script>
</body>
</html>
