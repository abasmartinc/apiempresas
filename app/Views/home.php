<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head') ?>
    <!-- Fonts handled by partials/head -->
    <?php $cssV = static fn($f) => is_file(FCPATH . $f) ? filemtime(FCPATH . $f) : '1'; ?>
    <link rel="stylesheet" href="<?= base_url('public/css/home.css') ?>?v=<?= $cssV('public/css/home.css') ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/home-mobile.css') ?>?v=<?= $cssV('public/css/home-mobile.css') ?>" media="screen and (max-width: 768px)">
</head>

<body>

    <?= view('partials/header') ?>

    <main>

        <!-- 1. HERO PROFESIONAL -->
        <section class="hero container" data-track-section="hero">
            <h1 class="reveal">
                API para validar CIF y empresas españolas
                <span class="gradient-text">con datos del Registro Mercantil y BORME</span>
            </h1>
            <p class="reveal delay-1">Integra en tu software los datos de cualquier empresa española: valida el CIF y la razón social, comprueba si está activa y obtén CNAE, domicilio, administradores y actos del BORME con una sola llamada REST.</p>
            
            <div class="hero-btns reveal delay-3">
                <a href="<?= site_url('register?intent=api&plan=free&source=home_hero') ?>" class="btn-ae btn-ae-primary" data-track-event="hero_cta_click" data-track-metadata='{"cta_text": "Obtener API key gratis", "plan": "free", "source_block": "hero", "page_type": "home"}'>Obtener API key gratis</a>
                <a href="#buscar" class="btn-ae btn-ae-outline" data-track-event="hero_cta_click" data-track-metadata='{"cta_text": "Probar en vivo", "source_block": "hero", "page_type": "home"}'>Probar en vivo</a>
            </div>
            
            <span class="trust-tag reveal delay-3"><?= (int) $freeLimit ?> consultas gratis · Sin tarjeta · Datos del Registro Mercantil y BORME</span>

            <?php
            // Cifras reales (Home::getHomeStats, caché de 12 h). Se redondean hacia abajo
            // para que "más de" sea siempre cierto.
            $fmtMas = static function (int $n, string $que): string {
                if ($n >= 1000000) {
                    $m = floor($n / 100000) / 10;
                    return '<strong>Más de ' . number_format($m, $m == floor($m) ? 0 : 1, ',', '.') . ' millones</strong> de ' . esc($que);
                }
                if ($n >= 1000) {
                    return '<strong>Más de ' . number_format(floor($n / 1000) * 1000, 0, ',', '.') . '</strong> ' . esc($que);
                }
                return '<strong>' . $n . '</strong> ' . esc($que);
            };
            $mesesCortos = [1 => 'ene', 'feb', 'mar', 'abr', 'may', 'jun', 'jul', 'ago', 'sep', 'oct', 'nov', 'dic'];
            $hs = $homeStats ?? [];
            ?>
            <?php if (!empty($hs['companies']) && !empty($hs['acts'])): ?>
            <ul class="hero-stats reveal delay-3" aria-label="Datos de la base de datos">
                <li><?= $fmtMas((int) $hs['companies'], 'empresas españolas') ?></li>
                <li><?= $fmtMas((int) $hs['acts'], 'actos del BORME') ?></li>
                <?php if (!empty($hs['last_borme'])): $t = strtotime($hs['last_borme']); ?>
                <li>Último BORME procesado: <strong><?= (int) date('j', $t) . ' ' . $mesesCortos[(int) date('n', $t)] . ' ' . date('Y', $t) ?></strong></li>
                <?php endif; ?>
            </ul>
            <style>
                .hero-stats { list-style: none; padding: 0; margin: 22px auto 0; display: flex; flex-wrap: wrap; justify-content: center; gap: 10px 28px; font-size: 0.92rem; color: var(--ae-slate); }
                .hero-stats li { position: relative; }
                .hero-stats li + li::before { content: "·"; position: absolute; left: -17px; color: #94a3b8; }
                .hero-stats strong { color: var(--ae-dark); font-weight: 800; }
                @media (max-width: 640px) { .hero-stats { flex-direction: column; align-items: center; gap: 6px; } .hero-stats li + li::before { display: none; } }
            </style>
            <?php endif; ?>

            <!-- Primera llamada: ejemplo copiable (misma cabecera y URL que la documentación) -->
            <div class="hero-curl reveal delay-3" data-track-section="hero_curl">
                <div class="code-editor-window">
                    <div class="editor-header">
                        <div class="dots">
                            <div class="dot red"></div><div class="dot yellow"></div><div class="dot green"></div>
                        </div>
                        <div class="tab">Tu primera llamada</div>
                        <button type="button" class="hero-curl-copy" id="heroCurlCopy" aria-label="Copiar la petición curl">Copiar</button>
                    </div>
                    <div class="editor-body">
<pre><code id="heroCurlCode"><span class="hc-cmd">curl</span> <span class="hc-str">"https://apiempresas.es/api/v1/companies?cif=B12345678"</span> \
  <span class="hc-flag">-H</span> <span class="hc-str">"X-API-KEY: TU_API_KEY"</span></code></pre>
<pre class="hc-res"><code><span class="hc-muted"># Respuesta (resumida)</span>
{ <span class="hc-key">"success"</span>: <span class="hc-bool">true</span>, <span class="hc-key">"data"</span>: { <span class="hc-key">"cif"</span>: <span class="hc-val">"B12345678"</span>, <span class="hc-key">"name"</span>: <span class="hc-val">"EMPRESA DE EJEMPLO SL"</span>, <span class="hc-key">"status"</span>: <span class="hc-val">"ACTIVA"</span>, … } }</code></pre>
                    </div>
                </div>
                <div class="hero-sdks" aria-label="Instalar un SDK">
                    <span class="hero-sdks-label">O instala un SDK:</span>
                    <code>npm install apiempresas</code>
                    <code>pip install apiempresas</code>
                    <code>composer require apiempresas/apiempresas-php</code>
                </div>
                <p class="hero-curl-note">Crea tu cuenta gratis y cambia <code>B12345678</code> por el CIF que quieras y <code>TU_API_KEY</code> por tu clave. <a href="<?= site_url('documentation#sdks') ?>">Ver ejemplos en PHP, Node.js y Python →</a></p>
            </div>
            <style>
                .hero-curl { max-width: 760px; margin: 40px auto 0; text-align: left; }
                .hero-curl .code-editor-window:hover { transform: none; }
                .hero-curl .editor-body { padding: 18px 22px; font-size: 0.9rem; }
                .hero-curl pre { margin: 0; white-space: pre; }
                .hero-curl .hc-res { margin-top: 14px; padding-top: 14px; border-top: 1px dashed rgba(255,255,255,0.12); white-space: pre-wrap; word-break: break-word; }
                .hero-curl .hc-cmd { color: #82AAFF; font-weight: 700; }
                .hero-curl .hc-flag { color: #C792EA; }
                .hero-curl .hc-str, .hero-curl .hc-val { color: #C3E88D; }
                .hero-curl .hc-key { color: #F07178; }
                .hero-curl .hc-bool { color: #FFCB6B; }
                .hero-curl .hc-muted { color: #64748B; }
                .hero-curl code { color: #E2E8F0; font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace; }
                .hero-curl-copy { margin-left: auto; background: rgba(255,255,255,0.08); color: #E2E8F0; border: 1px solid rgba(255,255,255,0.15); border-radius: 8px; padding: 4px 12px; font-size: 0.8rem; font-weight: 700; cursor: pointer; }
                .hero-curl-copy:hover { background: rgba(255,255,255,0.16); }
                .hero-sdks { display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 8px; margin-top: 14px; }
                .hero-sdks-label { font-size: 0.85rem; font-weight: 700; color: var(--ae-slate); }
                .hero-sdks code { background: #0f172a; color: #C3E88D; padding: 4px 10px; border-radius: 8px; font-size: 0.8rem; font-family: 'JetBrains Mono', 'Fira Code', Consolas, monospace; user-select: all; }
                .hero-curl-note { font-size: 0.9rem !important; margin: 14px 0 0 !important; max-width: none !important; text-align: center; color: var(--ae-slate); }
                .hero-curl-note code { background: #eef2ff; color: #1e3a8a; padding: 1px 6px; border-radius: 6px; font-size: 0.85em; }
                .hero-curl-note a { color: var(--ae-blue); font-weight: 700; text-decoration: none; }
                @media (max-width: 768px) {
                    .hero-curl .editor-header .tab { display: none; }
                    .hero-curl .editor-body { font-size: 0.78rem; padding: 14px; }
                }
            </style>
        </section>

        <!-- 2. BLOQUE DE BÚSQUEDA -->
        <section id="buscar" class="search-section container" data-track-section="search_block">
            <div class="search-panel reveal delay-3">
                <div style="text-align: center;">
                    <div class="badge-intro">
                        <div class="dot-live"></div>
                        Datos de fuentes oficiales
                    </div>
                </div>
                <h2>Prueba la API: <span class="highlight">valida un CIF en vivo</span></h2>
                <p class="subtitle">Busca por CIF o nombre y mira los mismos datos que devuelve la API, con información del Registro Mercantil y el BORME.</p>
                
                <div class="search-form-wrapper">
                    <div class="search-form">
                        <input type="text" id="q" class="search-input" placeholder="Ej: B12345678 o Nombre de Empresa" aria-label="Buscador de empresas">
                        <button id="btnBuscar" class="btn-ae btn-ae-primary" style="height: 72px; padding: 0 48px; border-radius: 14px; font-size: 1.15rem;">Validar empresa ahora</button>
                    </div>
                </div>
                
                <?php if (!empty($socialProofText)): ?>
                    <div class="social-proof-wrapper">
                        <div class="social-proof-counter">
                            <span class="social-proof-dot"></span>
                            <span><?= esc($socialProofText) ?></span>
                        </div>
                    </div>
                <?php endif; ?>


                <div id="resultado_container" style="display:none; margin-top: 24px;">
                    <div id="resultado"></div>
                </div>

            </div>
        </section>

        <!-- INTEGRATIONS STRIP -->
        <section class="integrations-strip">
            <div class="container">
                <h3 class="integrations-title">Úsala desde tus herramientas</h3>
                <div class="integrations-grid">
                    <!-- Zapier -->
                    <div class="integration-item" title="Zapier: próximamente" style="opacity: .55;">
                        <svg viewBox="0 0 24 24" fill="#FF4F00"><path d="M19.141 12l3.418-3.418c.319-.319.319-.838 0-1.157L19.141 4c-.319-.319-.838-.319-1.157 0L14.566 7.418l1.157 1.157L18 6.314l2.121 2.121-2.121 2.121 2.121 2.121 2.121-2.121-2.121-2.121 1.157-1.157c-.159 0-3.417 3.418-3.417 3.418zM4.859 12L1.441 8.582c-.319-.319-.319-.838 0-1.157L4.859 4c.319-.319.838-.319 1.157 0l3.418 3.418-1.157 1.157L6 6.314 3.879 8.435 6 10.556 3.879 12.677 1.758 10.556l2.121-2.121L4.859 12zm14.282 3.418l-3.418 3.418-1.157-1.157L18 17.686l2.121-2.121-2.121-2.121-2.121 2.121 2.121 2.121-2.121-2.121-1.157 1.157c.159 0 3.417-3.418 3.417-3.418zM4.859 15.418L1.441 18.836c-.319.319-.319.838 0 1.157L4.859 23.412c.319.319.838.319 1.157 0l3.418-3.418-1.157-1.157L6 21.123 3.879 19 6 16.877l-2.121-2.121 1.157-1.157c.159 0 3.417 3.418 3.417 3.418z"/></svg>
                        <span>Zapier <small>(próximamente)</small></span>
                    </div>
                    <!-- Make -->
                    <div class="integration-item" title="Make: próximamente" style="opacity: .55;">
                        <svg viewBox="0 0 24 24" fill="#6435c9"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8zm-3-9h6v2H9z"/></svg>
                        <span>Make <small>(próximamente)</small></span>
                    </div>
                    <!-- Google Sheets -->
                    <a href="<?= site_url('integraciones/google-sheets') ?>" class="integration-item" title="Extensión de Google Sheets" style="text-decoration: none; color: inherit;">
                        <svg viewBox="0 0 24 24" fill="#0F9D58"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                        <span>Google Sheets</span>
                    </a>
                    <!-- WordPress -->
                    <a href="<?= site_url('plugin-wordpress-buscador-empresas') ?>" class="integration-item" title="Plugin de WordPress" style="text-decoration: none; color: inherit;">
                        <svg viewBox="0 0 24 24" fill="#21759b"><path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2zm0 1.5c-4.694 0-8.5 3.806-8.5 8.5 0 .543.053 1.074.154 1.59l4.58-12.564c-.015-.008-.03-.017-.045-.026a8.411 8.411 0 0 0-4.689 2.5zm6.541 12.39c.094-.43.14-.858.14-1.283 0-1.13-.207-2.144-.622-3.042l-2.482 7.027c1.32-.716 2.427-1.631 3.32-2.702l-.356-.1zM12 12.75l-2.484 7.042c.8.21 1.637.333 2.484.333 1.103 0 2.158-.205 3.13-.578l-3.13-6.797zm-5.116-.252l2.64 7.37c-1.393-.572-2.584-1.464-3.5-2.613l.86-4.757z"/></svg>
                        <span>WordPress</span>
                    </a>
                </div>
            </div>
        </section>

        <!-- 3. BLOQUE DE AUTORIDAD -->
        <section class="band">
            <div class="container">
                <div class="band-header" style="margin-left: auto; margin-right: auto; text-align: center;">
                    <div class="pro-badge pro-badge-blue reveal" style="margin-left: auto; margin-right: auto;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                        Casos de uso
                    </div>
                    <h2 class="reveal delay-1">La API de empresas para <span class="gradient-text">validar, automatizar e integrar</span></h2>
                    <p class="reveal delay-2">Tres procesos en los que los datos del Registro Mercantil y el BORME ahorran trabajo manual y errores desde la primera llamada.</p>
                </div>

                <div class="grid-3">
                    <div class="feature-card use-case card-blue reveal delay-1">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 16px;">
                            <div class="icon-box" style="margin-bottom: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><polyline points="16 11 18 13 22 9"></polyline></svg>
                            </div>
                            <h3 style="margin-bottom: 0; font-size: 1.2rem; font-weight: 850;">Alta de clientes y KYB</h3>
                        </div>
                        <p>Cuando un cliente B2B se registra, valida con su CIF que la empresa existe y está activa antes de darle de alta.</p>
                        <ul class="use-case-list">
                            <li>CIF, razón social y estado registral</li>
                            <li>Administradores y cargos para verificar quién firma</li>
                            <li>Domicilio social y CNAE para completar la ficha</li>
                        </ul>
                        <div class="use-case-foot">
                            <span class="use-case-plan">Desde el plan Free</span>
                            <a href="<?= site_url('api-empresas') ?>" data-track-event="use_case_click" data-track-metadata='{"use_case": "Alta de clientes y KYB", "page_type": "home"}'>Ver la API KYB →</a>
                        </div>
                    </div>
                    <div class="feature-card use-case card-teal reveal delay-2">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 16px;">
                            <div class="icon-box" style="margin-bottom: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="8" y1="13" x2="16" y2="13"></line><line x1="8" y1="17" x2="13" y2="17"></line></svg>
                            </div>
                            <h3 style="margin-bottom: 0; font-size: 1.2rem; font-weight: 850;">Facturación y ERP</h3>
                        </div>
                        <p>Evita facturas con datos fiscales erróneos: autocompleta la razón social y el domicilio desde el CIF o el nombre.</p>
                        <ul class="use-case-list">
                            <li>Autocompletado por CIF o por nombre de empresa</li>
                            <li>Limpieza de tu base de clientes por lotes (100 CIFs por petición)</li>
                            <li>SDKs para PHP, Node.js y Python</li>
                        </ul>
                        <div class="use-case-foot">
                            <span class="use-case-plan">Lotes desde el plan Pro</span>
                            <a href="<?= site_url('documentation#endpoint-search') ?>" data-track-event="use_case_click" data-track-metadata='{"use_case": "Facturación y ERP", "page_type": "home"}'>Ver el buscador por nombre →</a>
                        </div>
                    </div>
                    <div class="feature-card use-case card-indigo reveal delay-3">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 16px;">
                            <div class="icon-box" style="margin-bottom: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            </div>
                            <h3 style="margin-bottom: 0; font-size: 1.2rem; font-weight: 850;">Riesgo de clientes</h3>
                        </div>
                        <p>Antes de dar crédito o firmar con un proveedor, comprueba su situación legal y su historial en el BORME.</p>
                        <ul class="use-case-list">
                            <li>Disoluciones, concursos y extinciones publicados</li>
                            <li>Historial BORME y cambios de administradores</li>
                            <li>Perfil de riesgo y solvencia (0-100)</li>
                        </ul>
                        <div class="use-case-foot">
                            <span class="use-case-plan">Perfil de riesgo en Business</span>
                            <a href="<?= site_url('documentation#endpoint-risk-profile') ?>" data-track-event="use_case_click" data-track-metadata='{"use_case": "Riesgo de clientes", "page_type": "home"}'>Ver el perfil de riesgo →</a>
                        </div>
                    </div>
                </div>
                <style>
                    .use-case { display: flex; flex-direction: column; }
                    .use-case > p { margin-bottom: 14px; }
                    .use-case-list { list-style: none; padding: 0; margin: 4px 0 20px; display: grid; gap: 8px; }
                    .use-case-list li { position: relative; padding-left: 22px; font-size: 0.95rem; color: var(--ae-slate); line-height: 1.45; }
                    .use-case-list li::before { content: ""; position: absolute; left: 4px; top: 0.55em; width: 8px; height: 8px; border-radius: 50%; background: var(--ae-blue); opacity: .7; }
                    .use-case-foot { margin-top: auto; display: flex; justify-content: space-between; align-items: center; gap: 12px; flex-wrap: wrap; padding-top: 16px; border-top: 1px solid rgba(15,23,42,0.08); }
                    .use-case-plan { font-size: 0.78rem; font-weight: 800; text-transform: uppercase; letter-spacing: .04em; color: #64748B; }
                    .use-case-foot a { font-weight: 800; font-size: 0.92rem; color: var(--ae-blue); text-decoration: none; }
                    .use-case-foot a:hover { text-decoration: underline; }
                </style>
            </div>
        </section>

        <!-- 4. BLOQUE API -->
        <section class="band band-light">
            <div class="container product-flex" style="flex-direction: row-reverse;">
                <div class="product-info">
                    <div class="pro-badge pro-badge-green reveal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                        Para Desarrolladores y Empresas
                    </div>
                    <h2 class="reveal delay-1">API REST con datos del <span class="gradient-text">Registro Mercantil y el BORME</span></h2>
                    <p class="reveal delay-1">Lleva los datos de cualquier empresa española a tus procesos de alta, formularios o aplicaciones internas con una sola llamada.</p>
                    <ul class="path-list reveal delay-2" style="margin-bottom: 48px;">
                        <li style="color: var(--ae-dark); border-bottom: none; padding: 6px 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Validación instantánea de CIFs y sociedades en tiempo real
                        </li>
                        <li style="color: var(--ae-dark); border-bottom: none; padding: 6px 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Historial del BORME, administradores y contratos públicos
                        </li>
                        <li style="color: var(--ae-dark); border-bottom: none; padding: 6px 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            API REST con respuestas JSON y SDKs para PHP, Node.js y Python
                        </li>
                    </ul>
                    <a href="<?= site_url('documentation') ?>" class="btn-ae btn-ae-primary reveal delay-3" style="background: #10B981; border-color: #10B981; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);">Ver Documentación API</a>
                </div>
                <div class="product-visual reveal delay-2" role="img" aria-label="Ejemplo de integración de API REST con respuesta en formato JSON">
                    
                    <div class="api-mockup-wrapper">
                        <div class="api-glow"></div>
                        
                        <div class="floating-badge-api">
                            <span class="pulse-dot"></span>
                            200 OK
                        </div>

                        <div class="code-editor-window">
                            <div class="editor-header">
                                <div class="dots">
                                    <div class="dot red"></div><div class="dot yellow"></div><div class="dot green"></div>
                                </div>
                                <div class="tab" style="white-space: nowrap;">GET /companies?cif=B12345678&amp;admin=true</div>
                            </div>
                            <div class="editor-body">
<pre style="margin: 0;"><code><span class="token punctuation">{</span>
  <span class="token property">"success"</span><span class="token punctuation">:</span> <span class="token boolean">true</span><span class="token punctuation">,</span>
  <span class="token property">"data"</span><span class="token punctuation">:</span> <span class="token punctuation">{</span>
    <span class="token property">"cif"</span><span class="token punctuation">:</span> <span class="token string">"B12345678"</span><span class="token punctuation">,</span>
    <span class="token property">"name"</span><span class="token punctuation">:</span> <span class="token string">"EMPRESA DE EJEMPLO SL"</span><span class="token punctuation">,</span>
    <span class="token property">"status"</span><span class="token punctuation">:</span> <span class="token string">"ACTIVA"</span><span class="token punctuation">,</span>
    <span class="token property">"province"</span><span class="token punctuation">:</span> <span class="token string">"MADRID"</span><span class="token punctuation">,</span>
    <span class="token property">"cnae"</span><span class="token punctuation">:</span> <span class="token string">"6201"</span><span class="token punctuation">,</span>
    <span class="token property">"cnae_label"</span><span class="token punctuation">:</span> <span class="token string">"Actividades de programación informática"</span><span class="token punctuation">,</span>
    <span class="token property">"administrators"</span><span class="token punctuation">:</span> <span class="token punctuation">[</span>
      <span class="token punctuation">{</span> <span class="token property">"name"</span><span class="token punctuation">:</span> <span class="token string">"JUAN PÉREZ GARCÍA"</span><span class="token punctuation">,</span> <span class="token property">"position"</span><span class="token punctuation">:</span> <span class="token string">"Administrador Único"</span> <span class="token punctuation">}</span>
    <span class="token punctuation">]</span>
  <span class="token punctuation">}</span>
<span class="token punctuation">}</span></code></pre>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- 4b. CÓMO FUNCIONA Y FUENTES -->
        <section class="band band-light how-it-works" data-track-section="how_it_works">
            <div class="container">
                <div class="band-header" style="margin-left: auto; margin-right: auto; text-align: center;">
                    <h2 class="reveal">Cómo empezar a usar la <span class="gradient-text">API de empresas</span></h2>
                    <p class="reveal delay-1">Tres pasos desde que creas la cuenta hasta tu primera respuesta.</p>
                </div>
                <ol class="hiw-steps">
                    <li class="hiw-step reveal delay-1">
                        <span class="hiw-num">1</span>
                        <h3>Crea tu cuenta gratis</h3>
                        <p>Sin tarjeta. Tienes <?= (int) $freeLimit ?> consultas para probar con datos reales.</p>
                        <a href="<?= site_url('register?intent=api&plan=free&source=home_how') ?>" data-track-event="how_cta_click" data-track-metadata='{"step": 1, "page_type": "home"}'>Crear cuenta →</a>
                    </li>
                    <li class="hiw-step reveal delay-2">
                        <span class="hiw-num">2</span>
                        <h3>Copia tu API key</h3>
                        <p>La tienes en tu panel nada más registrarte. Se envía en la cabecera <code>X-API-KEY</code> de cada petición.</p>
                    </li>
                    <li class="hiw-step reveal delay-3">
                        <span class="hiw-num">3</span>
                        <h3>Haz tu primera llamada</h3>
                        <p>Con <code>curl</code> o con los SDK para PHP, Node.js y Python. La respuesta llega en JSON.</p>
                        <a href="<?= site_url('documentation') ?>" data-track-event="how_cta_click" data-track-metadata='{"step": 3, "page_type": "home"}'>Ver la documentación →</a>
                    </li>
                </ol>

                <div class="hiw-sources reveal">
                    <h3>De dónde salen los datos</h3>
                    <div class="hiw-sources-grid">
                        <div>
                            <strong>BORME (Boletín Oficial del Registro Mercantil)</strong>
                            <p>Constituciones, nombramientos y ceses, cambios de domicilio, objeto o capital, disoluciones, concursos y extinciones. Lo revisamos a diario, con cada boletín publicado.</p>
                        </div>
                        <div>
                            <strong>Plataforma de Contratación del Sector Público</strong>
                            <p>Contratos y adjudicaciones públicas de cada empresa: órgano de contratación, importes y fechas.</p>
                        </div>
                        <div>
                            <strong>Datos calculados por APIEmpresas</strong>
                            <p>El estado registral, el scoring y el perfil de riesgo se calculan a partir de esas publicaciones. No son un dato oficial del Registro Mercantil.</p>
                        </div>
                    </div>
                </div>
            </div>
            <style>
                .how-it-works .hiw-steps { list-style: none; padding: 0; margin: 40px 0 0; display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
                .how-it-works .hiw-step { background: #fff; border: 1px solid var(--ae-border); border-radius: 20px; padding: 28px; position: relative; }
                .how-it-works .hiw-num { display: inline-flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 50%; background: var(--ae-blue); color: #fff; font-weight: 900; margin-bottom: 14px; }
                .how-it-works .hiw-step h3 { font-size: 1.15rem; font-weight: 850; margin: 0 0 8px; }
                .how-it-works .hiw-step p { color: var(--ae-slate); font-size: 0.95rem; margin: 0 0 12px; }
                .how-it-works .hiw-step a { color: var(--ae-blue); font-weight: 800; text-decoration: none; font-size: 0.92rem; }
                .how-it-works code { background: #eef2ff; color: #1e3a8a; padding: 1px 6px; border-radius: 6px; font-size: 0.85em; }
                .how-it-works .hiw-sources { margin-top: 48px; background: #fff; border: 1px solid var(--ae-border); border-radius: 20px; padding: 28px 32px; }
                .how-it-works .hiw-sources h3 { font-size: 1.2rem; font-weight: 850; margin: 0 0 18px; }
                .how-it-works .hiw-sources-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
                .how-it-works .hiw-sources-grid strong { display: block; color: var(--ae-dark); margin-bottom: 6px; font-size: 0.98rem; }
                .how-it-works .hiw-sources-grid p { color: var(--ae-slate); font-size: 0.92rem; margin: 0; line-height: 1.5; }
                @media (max-width: 900px) {
                    .how-it-works .hiw-steps, .how-it-works .hiw-sources-grid { grid-template-columns: 1fr; }
                    .how-it-works .hiw-sources { padding: 22px; }
                }
            </style>
        </section>

        <!-- 5. PRICING -->
        <section id="precios" class="band" data-track-section="pricing_cta">
            <div class="container">
                <div class="band-header" style="text-align: left; max-width: 800px;">
                    <h2 class="reveal delay-1">Precios de la API de empresas</h2>
                    <div style="width: 60px; height: 4px; background: linear-gradient(90deg, #4f46e5, #4b9a69); margin-top: 16px; margin-bottom: 24px;"></div>
                    <p class="reveal delay-2" style="font-size: 1.1rem; color: var(--ae-slate);">Empieza gratis con <?= (int) $freeLimit ?> consultas reales para validar CIF y razón social. Cuando lo lleves a producción, pasa a Pro o Business con control de consumo y trazabilidad. Cada plan incluye todo lo del anterior. Sin permanencia ni costes ocultos.</p>
                </div>
                
                <!-- TOGGLE ANUAL / MENSUAL -->
                <div style="display: flex; justify-content: flex-start; align-items: center; margin-bottom: 40px; margin-top: 24px; gap: 12px;">
                    <span style="font-size: 0.95rem; font-weight: 600; color: #94a3b8; transition: all 0.3s;" id="labelMonthlyHome">Mensual</span>
                    <button type="button" id="billingToggleHome" style="width: 56px; height: 32px; background: #0f172a; border-radius: 99px; position: relative; cursor: pointer; border: none; padding: 4px; transition: background 0.3s;" onclick="togglePricingHome()">
                        <div id="toggleKnobHome" style="width: 24px; height: 24px; background: white; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1); transform: translateX(24px);"></div>
                    </button>
                    <span style="font-size: 0.95rem; font-weight: 800; color: #2563eb; display: flex; align-items: center; gap: 8px; transition: all 0.3s;" id="labelAnnualHome">Anual <span style="background: #dcfce7; color: #166534; font-size: 10px; padding: 4px 8px; border-radius: 99px; letter-spacing: 0.05em; font-weight: 800;">AHORRA 20%</span></span>
                </div>

<?php
                // Qué incluye cada plan. Fuente: PlanAccessService y los controladores de /api/v1 (25-09-2026).
                // Cada plan incluye todo lo del anterior; aquí solo se listan las novedades.
                $tierFeatures = [
                    'free' => [
                        (int) $freeLimit . ' consultas gratis (no se renuevan)',
                        'Validación por CIF y buscador por nombre',
                        'Datos básicos: razón social, estado, CNAE, provincia y fecha de constitución',
                        'Scoring comercial básico (sin desglose)',
                        'Empresas recién constituidas (hoy, 7 o 30 días): 10 por consulta',
                        'Sin tarjeta de crédito',
                    ],
                    'pro' => [
                        '3.000 consultas al mes',
                        'Datos completos, con dirección, administradores y cargos',
                        'Consultas por lotes: hasta 100 CIFs por petición',
                        'Historial BORME, señales y grafos societarios',
                        'Scoring comercial IA completo (0-100)',
                        'Empresas recién constituidas (hoy, 7 o 30 días): hasta 100 por consulta',
                        'Soporte prioritario por email',
                    ],
                    'business' => [
                        '10.000 consultas al mes',
                        'Perfil de riesgo y solvencia',
                        'Contratos y adjudicaciones públicas',
                        'Webhooks Push (Notificaciones BORME)',
                        'IA Business Insights completo',
                        'IA Contact Prep y Calculadora Match B2B',
                        'Empresas recién constituidas (hoy, 7 o 30 días): hasta 1.000 por consulta',
                        'Soporte prioritario por email',
                    ],
                ];
                $tierTick = '<svg style="flex-shrink: 0;" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                ?>
                <style>
                    .annual-note-home { margin: -6px 0 12px; font-size: 0.82rem; font-weight: 700; color: #ecfdf5; opacity: .92; }
                </style>
                <div class="tier-grid" style="margin-top: 16px;">
                    <!-- FREE -->
                    <div class="tier tier-free reveal delay-1">
                        <div class="tier-tag">TESTING</div>
                        <h3>Free</h3>
                        <div class="tier-subtitle">Para probar la API</div>
                        <div class="tier-desc">Prueba la API con datos reales y valida resultados antes de pasar a producción.</div>
                        <div class="price">0€<span> · pago único</span></div>
                        <ul class="tier-features">
                            <?php foreach ($tierFeatures['free'] as $f): ?>
                            <li><?= $tierTick ?> <?= esc($f) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="<?= site_url('register?intent=api&plan=free') ?>" class="btn-tier" data-track-event="pricing_cta_click" data-track-metadata='{"cta_text": "Empezar gratis", "plan": "free", "source_block": "pricing_cta", "page_type": "home"}'>Empezar gratis</a>
                        <div style="text-align: center; margin-top: 16px;">
                            <a href="<?= site_url('planes/free') ?>" style="color: #e2e8f0; font-size: 0.9rem; text-decoration: none; font-weight: 500; border-bottom: 1px dashed #cbd5e1; padding-bottom: 2px; transition: color 0.2s;">Ver casos de uso y ejemplos &rarr;</a>
                        </div>
                    </div>
                    
                    <!-- PRO -->
                    <div class="tier tier-pro reveal delay-2">
                        <div class="tier-tag">MÁS ELEGIDO</div>
                        <h3>Pro</h3>
                        <div class="tier-subtitle">Para automatizar validaciones</div>
                        <div class="tier-desc">La opción ideal para SaaS, ERPs y productos que ya necesitan validación en producción.</div>
                        <div class="price"><b id="priceProHome" data-monthly="19" data-annual="15,17" style="font-weight: inherit;">15,17</b>€<span>/mes</span></div>
                        <div class="annual-note-home">Facturado 182 € al año (ahorras 46 €)</div>
                        <ul class="tier-features">
                            <li style="font-weight: 800;"><?= $tierTick ?> Todo lo del plan Free, más:</li>
                            <?php foreach ($tierFeatures['pro'] as $f): ?>
                            <li><?= $tierTick ?> <?= esc($f) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="<?= site_url('register?intent=api&plan=pro&period=annual') ?>" class="btn-tier" data-track-event="pricing_cta_click" data-track-metadata='{"cta_text": "Empezar con Pro", "plan": "pro", "source_block": "pricing_cta", "page_type": "home"}'>Empezar con Pro</a>
                        <div style="text-align: center; margin-top: 16px;">
                            <a href="<?= site_url('planes/pro') ?>" style="color: #c7d2fe; font-size: 0.9rem; text-decoration: none; font-weight: 500; border-bottom: 1px dashed #818cf8; padding-bottom: 2px; transition: color 0.2s;">Ver casos de uso y ejemplos &rarr;</a>
                        </div>
                    </div>
                    
                    <!-- BUSINESS -->
                    <div class="tier tier-biz reveal delay-3">
                        <div class="tier-tag">ESCALA</div>
                        <h3>Business</h3>
                        <div class="tier-subtitle">Para equipos y alto volumen</div>
                        <div class="tier-desc">Pensado para plataformas con más carga, procesos críticos y necesidades de mayor disponibilidad.</div>
                        <div class="price"><b id="priceBizHome" data-monthly="49" data-annual="39,17" style="font-weight: inherit;">39,17</b>€<span>/mes</span></div>
                        <div class="annual-note-home">Facturado 470 € al año (ahorras 118 €)</div>
                        <ul class="tier-features">
                            <li style="font-weight: 800;"><?= $tierTick ?> Todo lo del plan Pro, más:</li>
                            <?php foreach ($tierFeatures['business'] as $f): ?>
                            <li><?= $tierTick ?> <?= esc($f) ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <a href="<?= site_url('register?intent=api&plan=business&period=annual') ?>" class="btn-tier" data-track-event="pricing_cta_click" data-track-metadata='{"cta_text": "Empezar con Business", "plan": "business", "source_block": "pricing_cta", "page_type": "home"}'>Empezar con Business</a>
                        <div style="text-align: center; margin-top: 16px;">
                            <a href="<?= site_url('planes/business') ?>" style="color: #a7f3d0; font-size: 0.9rem; text-decoration: none; font-weight: 500; border-bottom: 1px dashed #34d399; padding-bottom: 2px; transition: color 0.2s;">Ver casos de uso y ejemplos &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- Custom Bonus Banner -->
                <div style="margin-top: 60px; background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 50%, #f0fdf4 100%); padding: 48px 32px; border-radius: 24px; text-align: center; position: relative; overflow: hidden; border: 1px solid rgba(59, 130, 246, 0.15); box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.1); max-width: 900px; margin-left: auto; margin-right: auto;">
                    
                    <!-- Patrón de puntos decorativo de fondo -->
                    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.4; background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px; pointer-events: none;"></div>
                    
                    <!-- Efectos de luz suaves -->
                    <div style="position: absolute; top: -30%; left: -10%; width: 50%; height: 160%; background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 60%); pointer-events: none;"></div>
                    <div style="position: absolute; bottom: -30%; right: -10%; width: 50%; height: 160%; background: radial-gradient(circle, rgba(16,185,129,0.08) 0%, transparent 60%); pointer-events: none;"></div>

                    <div style="position: relative; z-index: 1;">
                        <div style="display: inline-block; background: #ffffff; color: #2563eb; font-size: 0.8rem; font-weight: 800; padding: 6px 16px; border-radius: 99px; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 16px; box-shadow: 0 4px 6px -1px rgba(37,99,235,0.1); border: 1px solid rgba(59,130,246,0.1);">Nuevo Plan a Medida</div>
                        <h3 style="color: #0f172a; font-size: 2.1rem; font-weight: 900; margin: 0 0 12px; letter-spacing: -0.03em;">¿Prefieres pagar solo por lo que usas?</h3>
                        <p style="color: #475569; font-size: 1.15rem; max-width: 600px; margin: 0 auto 32px; line-height: 1.6;">Diseña tu propio <strong style="color: #0f172a;">Bono de Créditos Prepago</strong>. Paga una sola vez, consúmelo a tu ritmo y consigue descuentos automáticos por volumen.</p>
                        
                        <a href="<?= site_url('crear-bono-api') ?>" style="display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: #fff; padding: 18px 40px; border-radius: 16px; font-weight: 800; font-size: 1.1rem; text-decoration: none; box-shadow: 0 10px 25px rgba(37,99,235,0.4); transition: all 0.3s ease; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <line x1="21" y1="4" x2="14" y2="4"></line>
                                <line x1="10" y1="4" x2="3" y2="4"></line>
                                <line x1="21" y1="12" x2="12" y2="12"></line>
                                <line x1="8" y1="12" x2="3" y2="12"></line>
                                <line x1="21" y1="20" x2="16" y2="20"></line>
                                <line x1="12" y1="20" x2="3" y2="20"></line>
                                <line x1="14" y1="1" x2="14" y2="7"></line>
                                <line x1="8" y1="9" x2="8" y2="15"></line>
                                <line x1="16" y1="17" x2="16" y2="23"></line>
                            </svg>
                            Crear mi Bono Personalizado
                        </a>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 80px; margin-bottom: 40px;">
                    <h3 class="reveal" style="font-size: 1.8rem; font-weight: 850; color: var(--ae-dark);">Comparativa detallada de funciones</h3>
                    <p style="color: var(--ae-slate); font-size: 0.95rem; margin-top: 8px;">Cada plan incluye todo lo del anterior.</p>
                </div>
                
                <div class="table-responsive reveal delay-1" style="overflow-x: auto; background: #fff; border-radius: 24px; border: 1px solid var(--ae-border); box-shadow: var(--ae-shadow-sm);">
                    <style>
                        .capabilities-table .cap-group-head td { background: #f8fafc; padding: 14px 20px !important; border-top: 1px solid var(--ae-border); text-align: left; }
                        .capabilities-table .cap-group-title { font-weight: 850; color: var(--ae-dark); font-size: 0.95rem; text-transform: uppercase; letter-spacing: .04em; }
                        .capabilities-table .cap-group-sub { color: var(--ae-slate); font-size: 0.85rem; font-weight: 500; margin-left: 8px; }
                        .capabilities-table .cap-fold-btn { all: unset; cursor: pointer; display: flex; align-items: baseline; flex-wrap: wrap; gap: 4px; width: 100%; }
                        .capabilities-table .cap-fold-btn:focus-visible { outline: 2px solid #2563eb; outline-offset: 4px; border-radius: 6px; }
                        .capabilities-table .cap-fold-cta { margin-left: auto; display: inline-flex; align-items: center; gap: 6px; padding: 6px 14px; border-radius: 999px; background: #eff6ff; border: 1px solid #bfdbfe; color: var(--ae-blue); font-size: 0.85rem; font-weight: 800; white-space: nowrap; }
                        .capabilities-table .cap-fold-btn:hover .cap-fold-cta { background: #dbeafe; }
                        .capabilities-table .cap-fold-close { display: none; }
                        .capabilities-table .cap-fold-btn[aria-expanded="true"] .cap-fold-open { display: none; }
                        .capabilities-table .cap-fold-btn[aria-expanded="true"] .cap-fold-close { display: inline; }
                        .capabilities-table .cap-fold-btn { align-items: center !important; }
                        .capabilities-table .cap-fold-icon { color: var(--ae-blue); font-weight: 900; transition: transform .2s; }
                        @media (max-width: 760px) {
                            .capabilities-table .cap-group-head .cap-group-sub { display: none; }
                            .capabilities-table .cap-fold-cta { margin-left: 0; }
                            .capabilities-table .cap-fold-btn { width: auto; max-width: calc(100vw - 72px); gap: 10px; }
                        }
                        .capabilities-table .cap-fold-btn[aria-expanded="true"] .cap-fold-icon { transform: rotate(180deg); }
                    </style>
                    <table class="capabilities-table">
                        <thead>
                            <tr>
                                <th>Función / Capacidad</th>
                                <th>Free</th>
                                <th class="cap-featured-col">Pro</th>
                                <th>Business</th>
                            </tr>
                        </thead>
                        <tbody class="cap-group-head">
                            <tr><td colspan="4"><span class="cap-group-title">Validar</span> <span class="cap-group-sub">Comprueba que la empresa existe y obtén sus datos</span></td></tr>
                        </tbody>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Datos de empresa por CIF</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies</div>
                                        <div class="cap-feature-desc">Comprueba que la sociedad existe y obtén su razón social, estado, CNAE, domicilio y capital.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_companies')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); font-size: 0.85rem;" title="Dirección y objeto social completo ocultos">Básico</td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Administradores y cargos</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies?cif=…&amp;admin=true</div>
                                        <div class="cap-feature-desc">Añade a la respuesta los administradores y cargos actuales de la empresa.</div>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Buscador Inteligente</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/search</div>
                                        <div class="cap-feature-desc">Encuentra empresas por nombre o razón social con autocompletado y normalización.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_search')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Consulta Múltiple (Batch)</div>
                                        <div class="cap-feature-endpoint">POST /api/v1/companies/batch</div>
                                        <div class="cap-feature-desc">Consulta hasta 100 CIFs en una única petición ahorrando tiempos de red.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('post_batch')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                        </tbody>
                        <tbody class="cap-group-head">
                            <tr><td colspan="4"><span class="cap-group-title">Enriquecer y seguir cambios</span> <span class="cap-group-sub">Historial del Registro Mercantil, vínculos y contratos públicos</span></td></tr>
                        </tbody>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Historial Actos BORME</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/borme</div>
                                        <div class="cap-feature-desc">Historial cronológico completo de publicaciones en el Registro Mercantil.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_borme')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Empresas recién constituidas</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/radar</div>
                                        <div class="cap-feature-desc">Listado de empresas constituidas en España hoy o en los últimos 7 o 30 días, filtrable por provincia.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_radar')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); font-size: 0.85rem;">10 resultados</td>
                                <td class="cap-featured-col" style="text-align: center; font-size: 0.85rem; font-weight: 700;">100 resultados</td>
                                <td style="text-align: center; font-size: 0.85rem; font-weight: 700;">1.000 resultados</td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Grafos de Poder Societario</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/network</div>
                                        <div class="cap-feature-desc">Obtiene la red de vinculación entre empresas a través de sus administradores.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_network')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Contratos y Adjudicaciones Públicas</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/contracts</div>
                                        <div class="cap-feature-desc">Consulta el historial de adjudicaciones y contratos públicos de la empresa, órgano de contratación e importes.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_contracts')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Webhooks PUSH</div>
                                        <div class="cap-feature-endpoint">POST /api/v1/webhooks</div>
                                        <div class="cap-feature-desc">Sincroniza eventos en tiempo real con tu CRM sin necesidad de consultar la API.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('post_webhook')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                        </tbody>
                        <tbody class="cap-group-head">
                            <tr><td colspan="4"><span class="cap-group-title">Riesgo</span> <span class="cap-group-sub">Situación legal, alertas societarias y solvencia</span></td></tr>
                        </tbody>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Señales Societarias BORME</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/signals</div>
                                        <div class="cap-feature-desc">Monitoriza eventos reales: ampliaciones de capital, cambios de administrador y más.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_signals')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Perfil de Riesgo y Solvencia</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/risk-profile</div>
                                        <div class="cap-feature-desc">Scoring algorítmico de riesgo, cumplimiento de depósito de cuentas y alertas societarias.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_risk_profile')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                        </tbody>
                        <tbody class="cap-group-head">
                            <tr><td colspan="4">
                                <button type="button" class="cap-fold-btn" aria-expanded="false" aria-controls="cap-group-prospeccion-comercial-con-ia" onclick="var t=document.getElementById('cap-group-prospeccion-comercial-con-ia'),o=this.getAttribute('aria-expanded')==='true';t.hidden=o;this.setAttribute('aria-expanded',o?'false':'true');">
                                    <span class="cap-group-title">Prospección comercial con IA</span> <span class="cap-group-sub">Scoring, insights, contact prep y match B2B, para equipos de ventas</span> <span class="cap-fold-cta"><span class="cap-fold-open">Ver las 4 funciones</span><span class="cap-fold-close">Ocultar</span> <span class="cap-fold-icon" aria-hidden="true">▾</span></span>
                                </button>
                            </td></tr>
                        </tbody>
                        <tbody id="cap-group-prospeccion-comercial-con-ia" hidden>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Scoring Comercial IA</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/score</div>
                                        <div class="cap-feature-desc">Clasifica empresas por potencial de compra y salud financiera mediante nuestro algoritmo.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_score')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); font-size: 0.85rem;">Básico</td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">IA Business Insights</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/insights</div>
                                        <div class="cap-feature-desc">Análisis avanzado de necesidades de negocio y probabilidad de conversión.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_insights')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center; color: var(--ae-slate); opacity: 0.5;">Preview</td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">IA Contact Prep</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/contact-prep</div>
                                        <div class="cap-feature-desc">Genera argumentos de venta personalizados para cada empresa con nuestra IA.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_contact_prep')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Calculadora Match B2B</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/match</div>
                                        <div class="cap-feature-desc">Evalúa el encaje comercial y genera un argumentario de venta personalizado.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_match')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td class="cap-featured-col" style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <p style="text-align: center; margin-top: 24px; color: var(--ae-slate); font-size: 0.9rem; font-weight: 500;">
                    ¿Necesitas un plan personalizado con más volumen? <a href="<?= site_url('contact') ?>" style="color: var(--ae-blue); font-weight: 700; text-decoration: none;">Contacta con nosotros</a>
                </p>
            </div>
        </section>



        <!-- 6. FAQ -->
        <section class="band" style="background: #F8FAFC; border-top: 1px solid var(--ae-border); border-bottom: 1px solid var(--ae-border); position: relative; overflow: hidden;">
            <!-- Abstract background elements for WOW effect -->
            <div style="position: absolute; top: -20%; left: -10%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(37, 99, 235, 0.04) 0%, transparent 70%); filter: blur(60px); pointer-events: none;"></div>
            <div style="position: absolute; bottom: -20%; right: -10%; width: 600px; height: 600px; background: radial-gradient(circle, rgba(16, 185, 129, 0.04) 0%, transparent 70%); filter: blur(60px); pointer-events: none;"></div>
            <div class="bg-grid"></div>
            
            <div class="container" style="position: relative; z-index: 2;">
                <div class="faq-grid">
                    
                    <!-- Left Column: Intro -->
                    <div style="position: sticky; top: 120px;">
                        <span class="tag reveal" style="background: rgba(37,99,235,0.1); color: var(--ae-blue); border: none; font-weight: 800; padding: 6px 16px; border-radius: 100px; display: inline-block; margin-bottom: 8px;">Soporte Técnico</span>
                        <h2 class="reveal delay-1" style="font-size: 3rem; font-weight: 950; margin-top: 16px; margin-bottom: 24px; text-align: left; line-height: 1.1; letter-spacing: -0.03em;">Preguntas frecuentes sobre la <span class="gradient-text" style="display: inline-block; padding-bottom: 4px;">API de empresas</span></h2>
                        <p class="reveal delay-2" style="color: var(--ae-slate); font-size: 1.15rem; line-height: 1.6; margin-bottom: 32px; font-weight: 500;">Si no encuentras la respuesta que buscas, escríbeme. Te responde directamente quien desarrolla la API, y te ayudo a integrarla en tu sistema.</p>
                        
                        <!-- Soporte y estado del servicio (sin avatares: el soporte lo da quien desarrolla la API) -->
                        <div class="reveal delay-2" style="display: grid; gap: 12px; margin-bottom: 32px; max-width: 420px;">
                            <div style="display: flex; align-items: center; gap: 14px; padding: 12px 18px; background: #ffffff; border-radius: 16px; border: 1px solid var(--ae-border); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);">
                                <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(37,99,235,0.1); color: var(--ae-blue); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                </div>
                                <div>
                                    <div style="font-weight: 800; color: var(--ae-dark); font-size: 0.95rem;">Soporte directo del desarrollador</div>
                                    <div style="color: var(--ae-slate); font-size: 0.8rem; font-weight: 500;">Desde España · Tiempo de respuesta &lt; 2h</div>
                                </div>
                            </div>
                            <a href="https://status.apiempresas.es" target="_blank" rel="noopener" style="display: flex; align-items: center; gap: 14px; padding: 12px 18px; background: #ffffff; border-radius: 16px; border: 1px solid var(--ae-border); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); text-decoration: none;">
                                <div style="width: 40px; height: 40px; border-radius: 12px; background: rgba(16,185,129,0.12); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <span style="width: 10px; height: 10px; border-radius: 50%; background: #10b981; box-shadow: 0 0 0 4px rgba(16,185,129,0.2);"></span>
                                </div>
                                <div>
                                    <div style="font-weight: 800; color: var(--ae-dark); font-size: 0.95rem;">Estado del servicio en tiempo real</div>
                                    <div style="color: var(--ae-slate); font-size: 0.8rem; font-weight: 500;">Disponibilidad pública de la API &rarr;</div>
                                </div>
                            </a>
                        </div>

                        <a href="mailto:soporte@apiempresas.es" class="btn-ae reveal delay-3" style="background: linear-gradient(135deg, var(--ae-blue), var(--ae-teal)); color: #ffffff; border-radius: 14px; box-shadow: 0 10px 20px -5px rgba(37,99,235,0.4); padding: 16px 32px; font-size: 1.05rem; display: inline-flex; align-items: center; gap: 12px; transition: all 0.4s ease; border: none; font-weight: 700;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 15px 30px -5px rgba(37, 99, 235, 0.5)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 20px -5px rgba(37,99,235,0.4)';">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            Escribir a soporte
                        </a>
                    </div>

                    <!-- Right Column: Accordion -->
                    <div class="faq-accordion" style="width: 100%; margin: 0;">
                        <?php
                        // Única fuente de las FAQ: se pintan aquí y en el JSON-LD de más abajo.
                        $homeFaqs = [
                            ['¿Qué datos devuelve la API?', 'Devuelve la razón social oficial, el estado de actividad (activa, extinguida, etc.), la fecha de constitución, la provincia, y la actividad principal (CNAE) obtenida directamente del Registro Mercantil.'],
                            ['¿Puedo probarla gratis?', 'Sí. Al registrarte obtienes el plan Free con ' . (int) $freeLimit . ' consultas gratuitas (no se renuevan) contra la API real, sin tarjeta y sin compromiso.'],
                            ['¿La información es oficial?', 'Los datos proceden de fuentes públicas oficiales, como el BORME (Registro Mercantil) y la Plataforma de Contratación del Sector Público, y se actualizan de forma continua.'],
                            ['¿Qué tipo de autenticación utiliza la API?', 'La API utiliza autenticación estándar mediante Bearer Token (API Key) en los encabezados HTTP. Al crear tu cuenta obtienes al instante tu API Key, lista para usar.'],
                            ['¿Cuánto se tarda en integrar?', 'Nuestra API REST está diseñada con estándares modernos y es extremadamente sencilla. Un desarrollador promedio puede completar la integración y validar su primera empresa en menos de una hora. Dispones de documentación detallada para guiarte.'],
                            ['¿Se puede integrar en cualquier lenguaje o plataforma?', 'Sí. Al ser una API REST estándar que devuelve respuestas en JSON, puedes consumirla desde cualquier lenguaje (PHP, Node.js, Python, Java, C#, Go) o integrarla en ERPs/CRMs como Salesforce, HubSpot o SAP, y en cualquier herramienta no-code que haga peticiones HTTP.'],
                        ];
                        foreach ($homeFaqs as $i => [$q, $a]): ?>
                        <div class="faq-item reveal delay-<?= min(3, intdiv($i, 2) + 1) ?>">
                            <div class="faq-header">
                                <h3><?= esc($q) ?></h3>
                                <div class="faq-icon"></div>
                            </div>
                            <div class="faq-content">
                                <div class="faq-body">
                                    <?= esc($a) ?>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>

                    </div>
                </div>
            </div>
        </section>

        <!-- 7. CTA FINAL -->
        <section class="band" style="background: #ffffff;" data-track-section="final_cta">
            <div class="container">
                <div style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%); border-radius: 32px; padding: 56px 32px; text-align: center; position: relative; overflow: hidden; box-shadow: 0 40px 100px -20px rgba(37, 99, 235, 0.4);">
                    <!-- Decorative Glows -->
                    <div style="position: absolute; top: -50%; left: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(96, 165, 250, 0.4) 0%, transparent 70%); filter: blur(60px); pointer-events: none; z-index: 0;"></div>
                    <div style="position: absolute; bottom: -50%; right: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, transparent 70%); filter: blur(60px); pointer-events: none; z-index: 0;"></div>
                    
                    <!-- Content -->
                    <div style="position: relative; z-index: 1;">
                        <h2 style="font-size: 2.8rem; font-weight: 950; margin-bottom: 24px; color: #ffffff; letter-spacing: -0.02em; line-height: 1.1;">Empieza hoy a validar empresas en tus sistemas</h2>
                        <p style="font-size: 1.25rem; margin-bottom: 48px; color: #E2E8F0; max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.6;">Consulta datos de empresas del Registro Mercantil y el BORME, automatiza validaciones de CIF y empieza con <?= (int) $freeLimit ?> consultas gratis, sin tarjeta.</p>
                        
                        <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
                            <a href="<?= site_url('register?intent=api&plan=free') ?>" class="btn-ae" style="background: #ffffff; color: #0F172A; padding: 18px 32px; font-size: 1.1rem; border: none; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); font-weight: 800;" data-track-event="pricing_cta_click" data-track-metadata='{"cta_text": "Empezar gratis", "plan": "free", "source_block": "final_cta", "page_type": "home"}'>Empezar gratis</a>
                            <a href="<?= site_url('documentation') ?>" class="btn-ae" style="background: #10b981; color: #ffffff; padding: 18px 32px; font-size: 1.1rem; border: none; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.3); font-weight: 800;">
                                Ver Documentación API
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <?= view('partials/footer') ?>
    <?= view('partials/review_modal') ?>

    <!-- JSON PREVIEW MODAL -->
    <div id="json-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.4); z-index:9999; backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:20px;">
        <div style="background:#ffffff; width:100%; max-width:640px; border-radius:20px; border:1px solid #e2e8f0; box-shadow:0 30px 60px -12px rgba(15,23,42,0.15); overflow:hidden; position:relative;">
            <div style="background:#f8fafc; padding:18px 24px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #f1f5f9;">
                <div style="display:flex; align-items:center; gap:12px;">
                    <span style="background:rgba(37,99,235,0.08); color:#2563eb; font-size:10px; font-weight:800; padding:4px 10px; border-radius:6px; letter-spacing:0.05em; text-transform:uppercase;">Response Data</span>
                    <span id="modal-endpoint-name" style="color:#475569; font-family:'Fira Code', monospace; font-size:13px; font-weight:700;">GET /v1/companies</span>
                </div>
                <button onclick="closeJsonModal()" style="background:none; border:none; color:#94a3b8; cursor:pointer; font-size:24px; line-height:1; transition:color 0.2s;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#94a3b8'">&times;</button>
            </div>
            <div style="padding:32px; max-height:70vh; overflow-y:auto; background:#ffffff;">
                <pre id="modal-json-content" style="margin:0; font-family:'Fira Code', 'Courier New', monospace; font-size:14px; line-height:1.6; color:#1e293b;"></pre>
            </div>
            <div style="background:#f8fafc; padding:16px 24px; text-align:right; border-top:1px solid #f1f5f9;">
                <button onclick="closeJsonModal()" style="background:#ffffff; color:#475569; border:1px solid #e2e8f0; padding:10px 24px; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#ffffff'">Cerrar ventana</button>
            </div>
        </div>
    </div>

    <script>
        const jsonExamples = {
            get_companies: {
                success: true,
                data: {
                    cif: "B12345678",
                    name: "EMPRESA DE EJEMPLO SL",
                    status: "ACTIVA",
                    province: "MADRID",
                    address: "CALLE DE EJEMPLO 42, MADRID",
                    cnae: "6201",
                    cnae_label: "Actividades de programación informática"
                }
            },
            get_search: {
                success: true,
                data: {
                    name: "TECH FLOW SOLUTIONS SL",
                    cif: "B12345678",
                    score: 94,
                    province: "MADRID",
                    status: "ACTIVA"
                }
            },
            post_batch: {
                success: true,
                data: [
                    { name: "EMPRESA DE EJEMPLO SL", cif: "B12345678" },
                    { name: "OTRA EMPRESA DE EJEMPLO SA", cif: "A87654321" }
                ],
                meta: {
                    requested: 2,
                    found: 2,
                    cost: 2,
                    truncated: false
                }
            },
            get_score: {
                success: true,
                data: {
                    cif: "B12345678",
                    score: 94,
                    priority: "MUY_ALTA",
                    reasons: ["Crecimiento de capital reciente", "Alta actividad en BORME"],
                    last_signal: {
                        type: "AMPLIACION_CAPITAL",
                        date: "2024-05-01"
                    }
                }
            },
            get_signals: {
                success: true,
                data: {
                    cif: "B12345678",
                    signals: [
                        {
                            type: "borme_event",
                            label: "AMPLIACION_CAPITAL",
                            date: "2024-05-01",
                            probability: "MUY_ALTA"
                        }
                    ]
                }
            },
            get_borme: {
                success: true,
                data: {
                    cif: "B12345678",
                    company_name: "EMPRESA DE EJEMPLO SL",
                    events: [
                        {
                            date: "2023-11-01",
                            act_types: "Nombramientos, Ceses",
                            description: "Ceses/Dimisiones. Administrador único: JUAN PEREZ...",
                            url_pdf: "https://www.boe.es/borme/dias/2023/11/01/pdfs/BORME-A-2023-100-28.pdf"
                        }
                    ]
                }
            },
            get_insights: {
                success: true,
                data: {
                    profile: "SaaS / Fintech / Cloud",
                    summary: "Empresa con alta tracción y necesidad inminente de escalado tecnológico.",
                    needs: ["Infraestructura Cloud", "Ciberseguridad", "Contratación Devs"],
                    conversion_probability: "HIGH",
                    estimated_ticket: "10k-50k€"
                }
            },
            get_radar: {
                success: true,
                meta: {
                    plan: "pro",
                    count: 142,
                    limit: 100
                },
                data: [
                    { name: "NEW CORP SL", cif: "B99887766", founded: "2024-05-05", province: "BARCELONA", score: 88 }
                ]
            },
            get_network: {
                success: true,
                data: {
                    cif: "B12345678",
                    administrators: [
                        {
                            name: "GARCIA LOPEZ JUAN",
                            position: "Administrador Único",
                            linked_companies: [
                                { name: "OTRA EMPRESA SL", cif: "B87654321", status: "ACTIVA" }
                            ]
                        }
                    ]
                }
            },
            get_match: {
                success: true,
                data: {
                    cif: "B12345678",
                    seller_sector: "software",
                    match_score: 85,
                    analysis: {
                        match_level: "Alto",
                        synergy: "Alta sinergia",
                        buyer_needs: ["Digitalización", "CRM"]
                    },
                    sales_pitch: "He visto que están creciendo. Nuestro software puede ayudarles a..."
                }
            },
            get_contact_prep: {
                success: true,
                data: {
                    cif: "B12345678",
                    pitch_angle: "Escalabilidad tecnológica",
                    suggested_message: "Hola, he visto que Tech Flow Solutions SL está creciendo...",
                    key_metrics: ["+20% empleados este año", "Última ronda: Series A"]
                }
            },
            get_contracts: {
                success: true,
                data: {
                    cif: "A87654321",
                    company_name: "OTRA EMPRESA DE EJEMPLO SA",
                    summary: {
                        total_contracts: 32,
                        total_amount: "4817450.20",
                        currency: "EUR"
                    },
                    contracts: [
                        {
                            tender_id: "https://contrataciondelestado.es/sindicacion/licitacionesPerfilContratante/00000000",
                            title: "Servicio de mantenimiento de equipos informáticos...",
                            contracting_authority: "Ayuntamiento de Ejemplo",
                            award_date: "2026-08-25",
                            amount: "185320.00",
                            currency: "EUR",
                            tender_url: "https://contrataciondelestado.es/..."
                        }
                    ],
                    pagination: {
                        total: 32,
                        page: 1,
                        limit: 20,
                        total_pages: 2,
                        has_more: true
                    }
                }
            },
            get_risk_profile: {
                success: true,
                data: {
                    cif: "B12345678",
                    company_name: "EMPRESA DE EJEMPLO SL",
                    risk_score: 62,
                    risk_level: "ALTO",
                    confidence_score: 49,
                    data_quality_score: 70,
                    summary_message: "Atención: Constan indicadores de elevado riesgo financiero o corporativo.",
                    legal_state: "REGISTRY_CLOSURE_GENERICO",
                    data_sources: {
                        borme_status: "CHECKED_WITH_RECORDS",
                        accounts_status: "KNOWN_DELAYED",
                        official_status: "KNOWN"
                    },
                    dimensions: {
                        legal_distress: 60,
                        filing_compliance: 0,
                        governance_volatility: 30,
                        capital_instability: 0,
                        structural_volatility: 0,
                        stabilizing_credit: 0
                    },
                    canonical_events: [
                        {
                            code: "LEGAL_STATE_REGISTRY_CLOSURE_GENERICO",
                            dimension: "legal_distress",
                            severity: "high",
                            description: "Consta publicación registral de cierre sin especificación de causa.",
                            event_date: "2026-08-24",
                            classification_confidence: "LOW"
                        }
                    ],
                    model_version: "2.0.0",
                    calculated_at: "2026-08-24T00:00:00Z"
                }
            },
            post_webhook: {
                success: true,
                message: "Webhook creado correctamente",
                id: 789
            }
        };

        function showJsonPreview(key) {
            const modal = document.getElementById('json-modal');
            const content = document.getElementById('modal-json-content');
            const endpoint = document.getElementById('modal-endpoint-name');
            
            const names = {
                get_companies: 'GET /companies',
                get_search: 'GET /companies/search',
                post_batch: 'POST /companies/batch',
                get_score: 'GET /companies/score',
                get_signals: 'GET /companies/signals',
                get_borme: 'GET /companies/borme',
                get_insights: 'GET /companies/insights',
                get_radar: 'GET /companies/radar',
                get_network: 'GET /companies/network',
                get_match: 'GET /companies/match',
                get_contact_prep: 'GET /companies/contact-prep',
                get_contracts: 'GET /companies/contracts',
                get_risk_profile: 'GET /companies/risk-profile',
                post_webhook: 'POST /webhooks'
            };

            endpoint.textContent = names[key];
            content.innerHTML = syntaxHighlight(jsonExamples[key]);
            modal.style.display = 'flex';

            // Tracking Event
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': 'view_json_preview',
                'api_endpoint': names[key]
            });
            // Eliminamos overflow hidden para evitar saltos de scroll
            // document.body.style.overflow = 'hidden';
        }

        function closeJsonModal() {
            document.getElementById('json-modal').style.display = 'none';
            // document.body.style.overflow = 'auto';
        }

        function syntaxHighlight(json) {
            if (typeof json != 'string') {
                json = JSON.stringify(json, undefined, 2);
            }
            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g, function (match) {
                var cls = 'color:#d97706;'; // number (Orange)
                if (/^"/.test(match)) {
                    if (/:$/.test(match)) {
                        cls = 'color:#2563eb;'; // key (Blue)
                    } else {
                        cls = 'color:#16a34a;'; // string (Green)
                    }
                } else if (/true|false/.test(match)) {
                    cls = 'color:#9333ea;'; // boolean (Purple)
                } else if (/null/.test(match)) {
                    cls = 'color:#64748b;'; // null (Gray)
                }
                return '<span style="' + cls + ' font-weight: 500;">' + match + '</span>';
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            // FAQ Accordion Logic
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const header = item.querySelector('.faq-header');
                const content = item.querySelector('.faq-content');
                
                header.addEventListener('click', () => {
                    const isActive = item.classList.contains('active');
                    
                    // Close all others
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.classList.remove('active');
                            otherItem.querySelector('.faq-content').style.maxHeight = null;
                        }
                    });
                    
                    // Toggle current
                    if (isActive) {
                        item.classList.remove('active');
                        content.style.maxHeight = null;
                    } else {
                        item.classList.add('active');
                        content.style.maxHeight = content.scrollHeight + "px";
                    }
                });
            });
        });

        // Copiar el curl del hero
        (function () {
            var btn = document.getElementById('heroCurlCopy');
            if (!btn) return;
            btn.addEventListener('click', function () {
                var text = 'curl "https://apiempresas.es/api/v1/companies?cif=B12345678" \\\n  -H "X-API-KEY: TU_API_KEY"';
                var done = function () {
                    btn.textContent = 'Copiado';
                    setTimeout(function () { btn.textContent = 'Copiar'; }, 2000);
                };
                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(text).then(done).catch(function () {});
                } else {
                    var ta = document.createElement('textarea');
                    ta.value = text; document.body.appendChild(ta); ta.select();
                    try { document.execCommand('copy'); done(); } catch (e) {}
                    document.body.removeChild(ta);
                }
                if (window.trackEvent) window.trackEvent('hero_curl_copy', { source_block: 'hero', page_type: 'home' }, 'heroCurlCopy');
            });
        })();

        // Pricing Toggle Home
        let isAnnualHome = true;
        function togglePricingHome() {
            isAnnualHome = !isAnnualHome;
            const knob = document.getElementById('toggleKnobHome');
            const labelMonthly = document.getElementById('labelMonthlyHome');
            const labelAnnual = document.getElementById('labelAnnualHome');
            const pricePro = document.getElementById('priceProHome');
            const priceBiz = document.getElementById('priceBizHome');

            if(isAnnualHome) {
                knob.style.transform = 'translateX(24px)';
                labelMonthly.style.color = '#94a3b8';
                labelMonthly.style.fontWeight = '600';
                labelAnnual.style.color = '#2563eb';
                labelAnnual.style.fontWeight = '800';
                
                pricePro.textContent = pricePro.dataset.annual;
                priceBiz.textContent = priceBiz.dataset.annual;
                document.querySelectorAll('.annual-note-home').forEach(function (n) { n.style.visibility = ''; });
            } else {
                knob.style.transform = 'translateX(0px)';
                labelMonthly.style.color = '#2563eb';
                labelMonthly.style.fontWeight = '800';
                labelAnnual.style.color = '#94a3b8';
                labelAnnual.style.fontWeight = '600';
                
                pricePro.textContent = pricePro.dataset.monthly;
                priceBiz.textContent = priceBiz.dataset.monthly;
                document.querySelectorAll('.annual-note-home').forEach(function (n) { n.style.visibility = 'hidden'; });
            }

            // Los botones de pago llevan el periodo que el usuario está viendo
            document.querySelectorAll('a[href*="register?intent=api&plan=pro"], a[href*="register?intent=api&plan=business"]').forEach(function (a) {
                var u = new URL(a.href, window.location.origin);
                u.searchParams.set('period', isAnnualHome ? 'annual' : 'monthly');
                a.href = u.toString();
            });
        }
    </script>
    <script type="application/ld+json">
    <?= json_encode([
        '@context'   => 'https://schema.org',
        '@type'      => 'FAQPage',
        'mainEntity' => array_map(static fn($f) => [
            '@type'          => 'Question',
            'name'           => $f[0],
            'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
        ], $homeFaqs),
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) ?>
    </script>
</body>
</html>

