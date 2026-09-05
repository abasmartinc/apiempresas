<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head') ?>
    <!-- Fonts handled by partials/head -->
    <link rel="stylesheet" href="<?= base_url('public/css/home.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/home-mobile.css') ?>?v=<?= time() ?>" media="screen and (max-width: 768px)">
</head>

<body>

    <?= view('partials/header') ?>

    <main>

        <!-- 1. HERO PROFESIONAL -->
        <section class="hero container" data-track-section="hero">
            <h1 class="reveal">
                API para validar empresas y verificar CIF 
                <span class="gradient-text">con datos oficiales en España</span>
            </h1>
            <p class="reveal delay-1">Consulta datos oficiales de empresas españolas en tiempo real. Valida CIFs, verifica la razón social y conecta la información directa del Registro Mercantil y BORME en tus aplicaciones y flujos de trabajo.</p>
            
            <div class="hero-btns reveal delay-3">
                <a href="#buscar" class="btn-ae btn-ae-primary">Validar CIF gratis</a>
                <a href="<?= site_url('documentation') ?>" class="btn-ae btn-ae-outline">Ver Documentación API</a>
            </div>
            
            <span class="trust-tag reveal delay-3">Datos empresariales oficiales · Registro Mercantil · BORME</span>
        </section>

        <!-- 2. BLOQUE DE BÚSQUEDA -->
        <section id="buscar" class="search-section container" data-track-section="search_block">
            <div class="search-panel reveal delay-3">
                <div style="text-align: center;">
                    <div class="badge-intro">
                        <div class="dot-live"></div>
                        Acceso a Base de Datos Oficial
                    </div>
                </div>
                <h2>Validador de CIF y <span class="highlight">Buscador Oficial</span></h2>
                <p class="subtitle">Valide datos en segundos con conexión directa al Registro Mercantil y BORME.</p>
                
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

                <div class="search-benefits">
                    <div class="benefit-tag">
                        <div class="benefit-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <span>Validación rápida de CIF</span>
                    </div>
                    <div class="benefit-tag">
                        <div class="benefit-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <span>Consulta en tiempo real</span>
                    </div>
                    <div class="benefit-tag">
                        <div class="benefit-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <span>Enriquecimiento de datos</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- INTEGRATIONS STRIP -->
        <section class="integrations-strip">
            <div class="container">
                <h3 class="integrations-title">Conecta con tus herramientas favoritas</h3>
                <div class="integrations-grid">
                    <!-- Zapier -->
                    <div class="integration-item" title="Conecta con Zapier">
                        <svg viewBox="0 0 24 24" fill="#FF4F00"><path d="M19.141 12l3.418-3.418c.319-.319.319-.838 0-1.157L19.141 4c-.319-.319-.838-.319-1.157 0L14.566 7.418l1.157 1.157L18 6.314l2.121 2.121-2.121 2.121 2.121 2.121 2.121-2.121-2.121-2.121 1.157-1.157c-.159 0-3.417 3.418-3.417 3.418zM4.859 12L1.441 8.582c-.319-.319-.319-.838 0-1.157L4.859 4c.319-.319.838-.319 1.157 0l3.418 3.418-1.157 1.157L6 6.314 3.879 8.435 6 10.556 3.879 12.677 1.758 10.556l2.121-2.121L4.859 12zm14.282 3.418l-3.418 3.418-1.157-1.157L18 17.686l2.121-2.121-2.121-2.121-2.121 2.121 2.121 2.121-2.121-2.121-1.157 1.157c.159 0 3.417-3.418 3.417-3.418zM4.859 15.418L1.441 18.836c-.319.319-.319.838 0 1.157L4.859 23.412c.319.319.838.319 1.157 0l3.418-3.418-1.157-1.157L6 21.123 3.879 19 6 16.877l-2.121-2.121 1.157-1.157c.159 0 3.417 3.418 3.417 3.418z"/></svg>
                        <span>Zapier</span>
                    </div>
                    <!-- Make -->
                    <div class="integration-item" title="Conecta con Make">
                        <svg viewBox="0 0 24 24" fill="#6435c9"><path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm0 18c-4.411 0-8-3.589-8-8s3.589-8 8-8 8 3.589 8 8-3.589 8-8 8zm-3-9h6v2H9z"/></svg>
                        <span>Make</span>
                    </div>
                    <!-- Google Sheets -->
                    <div class="integration-item" title="Conecta con Google Sheets">
                        <svg viewBox="0 0 24 24" fill="#0F9D58"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
                        <span>Google Sheets</span>
                    </div>
                    <!-- Excel -->
                    <div class="integration-item" title="Exporta a Excel">
                        <svg viewBox="0 0 24 24" fill="#1D6F42"><path d="M15.8 2H13v3h3V2zm-4 0H9v3h2.8V2zM5.2 2H2v3h3.2V2zm16.8 4h-2.8v3H22V6zm-4 0H13v3h3.8V6zm-4 0H9v3h2.8V6zM5.2 6H2v3h3.2V6zM22 10h-2.8v3H22v-3zm-4 0H13v3h3.8v-3zm-4 0H9v3h2.8v-3zM5.2 10H2v3h3.2v-3zm16.8 4h-2.8v3H22v-3zm-4 0H13v3h3.8v-3zm-4 0H9v3h2.8v-3zM5.2 14H2v3h3.2v-3zm16.8 4h-2.8v3H22v-3zm-4 0H13v3h3.8v-3zm-4 0H9v3h2.8v-3zM5.2 18H2v3h3.2v-3z"/></svg>
                        <span>Microsoft Excel</span>
                    </div>
                    <!-- WordPress -->
                    <div class="integration-item" title="Plugin de WordPress">
                        <svg viewBox="0 0 24 24" fill="#21759b"><path d="M12 2c5.523 0 10 4.477 10 10s-4.477 10-10 10S2 17.523 2 12 6.477 2 12 2zm0 1.5c-4.694 0-8.5 3.806-8.5 8.5 0 .543.053 1.074.154 1.59l4.58-12.564c-.015-.008-.03-.017-.045-.026a8.411 8.411 0 0 0-4.689 2.5zm6.541 12.39c.094-.43.14-.858.14-1.283 0-1.13-.207-2.144-.622-3.042l-2.482 7.027c1.32-.716 2.427-1.631 3.32-2.702l-.356-.1zM12 12.75l-2.484 7.042c.8.21 1.637.333 2.484.333 1.103 0 2.158-.205 3.13-.578l-3.13-6.797zm-5.116-.252l2.64 7.37c-1.393-.572-2.584-1.464-3.5-2.613l.86-4.757z"/></svg>
                        <span>WordPress</span>
                    </div>
                </div>
            </div>
        </section>

        <!-- 3. BLOQUE DE AUTORIDAD -->
        <section class="band">
            <div class="container">
                <div class="band-header" style="margin-left: auto; margin-right: auto; text-align: center;">
                    <div class="pro-badge pro-badge-blue reveal" style="margin-left: auto; margin-right: auto;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                        Infraestructura Robusta
                    </div>
                    <h2 class="reveal delay-1">Datos empresariales para <span class="gradient-text">validar, automatizar e integrar</span></h2>
                    <p class="reveal delay-2">Accede a información mercantil oficial para acelerar el onboarding de clientes, evitar errores en facturación y enriquecer tus sistemas en tiempo real.</p>
                </div>

                <div class="grid-3">
                    <div class="feature-card card-blue reveal delay-1">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                            <div class="icon-box" style="margin-bottom: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            </div>
                            <h3 style="margin-bottom: 0; font-size: 1.2rem; font-weight: 850;">Validación y consulta CIF</h3>
                        </div>
                        <p>Comprueba al instante la existencia de sociedades, estado de actividad, razón social y domicilio oficial.</p>
                    </div>
                    <div class="feature-card card-teal reveal delay-2">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                            <div class="icon-box" style="margin-bottom: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="22" y1="12" x2="18" y2="12"></line><line x1="6" y1="12" x2="2" y2="12"></line><line x1="12" y1="6" x2="12" y2="2"></line><line x1="12" y1="22" x2="12" y2="18"></line></svg>
                            </div>
                            <h3 style="margin-bottom: 0; font-size: 1.2rem; font-weight: 850;">Enriquecimiento y Scoring</h3>
                        </div>
                        <p>Obtén códigos CNAE, administradores, actos BORME, contratos públicos y evaluación automática de solvencia.</p>
                    </div>
                    <div class="feature-card card-indigo reveal delay-3">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                            <div class="icon-box" style="margin-bottom: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                            </div>
                            <h3 style="margin-bottom: 0; font-size: 1.2rem; font-weight: 850;">Integración vía API REST</h3>
                        </div>
                        <p>Automatiza tus flujos conectando tu CRM, ERP o pasarela de pago directamente mediante JSON estándar.</p>
                    </div>
                </div>
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
                    <h2 class="reveal delay-1">API para validar empresas, <span class="gradient-text">consultar e integrar datos</span></h2>
                    <p class="reveal delay-1">Incorpore información empresarial oficial directamente en sus procesos de registro, formularios o aplicaciones internas con latencia mínima.</p>
                    <ul class="path-list reveal delay-2" style="margin-bottom: 48px;">
                        <li style="color: var(--ae-dark); border-bottom: none; padding: 6px 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Validación instantánea de CIFs y sociedades en tiempo real
                        </li>
                        <li style="color: var(--ae-dark); border-bottom: none; padding: 6px 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Historial oficial BORME, administradores y contratos públicos
                        </li>
                        <li style="color: var(--ae-dark); border-bottom: none; padding: 6px 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Fácil integración en menos de 10 minutos vía JSON / REST
                        </li>
                    </ul>
                    <a href="<?= site_url('documentation') ?>" class="btn-ae btn-ae-primary reveal delay-3" style="background: #10B981; border-color: #10B981; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);">Ver Documentación API</a>
                </div>
                <div class="product-visual reveal delay-2" role="img" aria-label="Ejemplo de integración de API REST con respuesta en formato JSON">
                    
                    <div class="api-mockup-wrapper">
                        <div class="api-glow"></div>
                        
                        <div class="floating-badge-api">
                            <span class="pulse-dot"></span>
                            200 OK — 45ms
                        </div>

                        <div class="code-editor-window">
                            <div class="editor-header">
                                <div class="dots">
                                    <div class="dot red"></div><div class="dot yellow"></div><div class="dot green"></div>
                                </div>
                                <div class="tab">GET /api/v1/companies?cif=B12345678</div>
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
    <span class="token property">"cnae_label"</span><span class="token punctuation">:</span> <span class="token string">"Actividades de programación informática"</span>
  <span class="token punctuation">}</span>
<span class="token punctuation">}</span></code></pre>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- 5. PRICING -->
        <section id="precios" class="band" data-track-section="pricing_cta">
            <div class="container">
                <div class="band-header" style="text-align: left; max-width: 800px;">
                    <h2 class="reveal delay-1">Planes transparentes para cualquier volumen</h2>
                    <div style="width: 60px; height: 4px; background: linear-gradient(90deg, #4f46e5, #4b9a69); margin-top: 16px; margin-bottom: 24px;"></div>
                    <p class="reveal delay-2" style="font-size: 1.1rem; color: var(--ae-slate);">Empieza validando CIF y razón social en Sandbox. Cuando lo lleves a producción, escala a Pro o Business con control de consumo y trazabilidad. Sin permanencias ni costes ocultos.</p>
                </div>
                
                <!-- TOGGLE ANUAL / MENSUAL -->
                <div style="display: flex; justify-content: flex-start; align-items: center; margin-bottom: 40px; margin-top: 24px; gap: 12px;">
                    <span style="font-size: 0.95rem; font-weight: 600; color: #94a3b8; transition: all 0.3s;" id="labelMonthlyHome">Mensual</span>
                    <button type="button" id="billingToggleHome" style="width: 56px; height: 32px; background: #0f172a; border-radius: 99px; position: relative; cursor: pointer; border: none; padding: 4px; transition: background 0.3s;" onclick="togglePricingHome()">
                        <div id="toggleKnobHome" style="width: 24px; height: 24px; background: white; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1); transform: translateX(24px);"></div>
                    </button>
                    <span style="font-size: 0.95rem; font-weight: 800; color: #2563eb; display: flex; align-items: center; gap: 8px; transition: all 0.3s;" id="labelAnnualHome">Anual <span style="background: #dcfce7; color: #166534; font-size: 10px; padding: 4px 8px; border-radius: 99px; letter-spacing: 0.05em; font-weight: 800;">AHORRA 20%</span></span>
                </div>

                <div class="tier-grid" style="margin-top: 16px;">
                    <!-- FREE -->
                    <div class="tier tier-free reveal delay-1">
                        <div class="tier-tag">TESTING</div>
                        <h3>Free</h3>
                        <div class="tier-subtitle">Para probar la API</div>
                        <div class="tier-desc">Prueba la API con datos reales y valida resultados antes de pasar a producción.</div>
                        <div class="price">0€<span>/único</span></div>
                        <ul class="tier-features">
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> <?= $freeLimit ?> consultas garantizadas</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Acceso a endpoint /companies</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Datos básicos oficiales (CIF, Razón Social, CNAE)</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Sin tarjeta de crédito</li>
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
                        <div class="price"><b id="priceProHome" data-monthly="19" data-annual="15" style="font-weight: inherit;">15</b>€<span>/mes</span></div>
                        <ul class="tier-features">
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> 3.000 consultas al mes</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Datos completos BORME y Actividad</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Scoring Comercial IA (0-100)</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Listado de Constituciones</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Grafos de Poder Societario</li>
                        </ul>
                        <a href="<?= site_url('register?intent=api&plan=pro') ?>" class="btn-tier" data-track-event="pricing_cta_click" data-track-metadata='{"cta_text": "Empezar con Pro", "plan": "pro", "source_block": "pricing_cta", "page_type": "home"}'>Empezar con Pro</a>
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
                        <div class="price"><b id="priceBizHome" data-monthly="49" data-annual="39" style="font-weight: inherit;">39</b>€<span>/mes</span></div>
                        <ul class="tier-features">
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> 10.000 consultas al mes</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Contratos y Adjudicaciones Públicas</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Webhooks Push (Notificaciones BORME)</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> IA Predictiva y Calculadora Match B2B</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Soporte Prioritario Slack / Email</li>
                        </ul>
                        <a href="<?= site_url('register?intent=api&plan=business') ?>" class="btn-tier" data-track-event="pricing_cta_click" data-track-metadata='{"cta_text": "Empezar con Business", "plan": "business", "source_block": "pricing_cta", "page_type": "home"}'>Empezar con Business</a>
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
                </div>
                
                <div class="table-responsive reveal delay-1" style="overflow-x: auto; background: #fff; border-radius: 24px; border: 1px solid var(--ae-border); box-shadow: var(--ae-shadow-sm);">
                    <table class="capabilities-table">
                        <thead>
                            <tr>
                                <th>Función / Capacidad</th>
                                <th>Free</th>
                                <th class="cap-featured-col">Pro</th>
                                <th>Business</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Validación y Enriquecimiento</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies</div>
                                        <div class="cap-feature-desc">Valida la existencia de una sociedad y obtén sus datos oficiales (CNAE, Domicilio, Capital).</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_companies')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
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
                            <tr>
                                <td>
                                    <div class="cap-col-feature">
                                        <div class="cap-feature-name">Scoring Comercial IA</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/score</div>
                                        <div class="cap-feature-desc">Clasifica empresas por potencial de compra y salud financiera mediante nuestro algoritmo.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_score')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">Basic</td>
                                <td class="cap-featured-col" style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                                <td style="text-align: center;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></td>
                            </tr>
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
                                        <div class="cap-feature-name">Nuevas Sociedades (Constituciones)</div>
                                        <div class="cap-feature-endpoint">GET /api/v1/companies/radar</div>
                                        <div class="cap-feature-desc">Consulta las nuevas sociedades constituidas en España filtradas por provincia o actividad económica.</div>
                                        <button type="button" onclick="event.preventDefault(); showJsonPreview('get_radar')" class="btn-json-preview">Ver Respuesta JSON</button>
                                    </div>
                                </td>
                                <td style="text-align: center; color: var(--ae-slate); opacity: 0.5;">—</td>
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
                        <h2 class="reveal delay-1" style="font-size: 3rem; font-weight: 950; margin-top: 16px; margin-bottom: 24px; text-align: left; line-height: 1.1; letter-spacing: -0.03em;">Resolvemos <span class="gradient-text" style="display: inline-block; padding-bottom: 4px;">tus dudas</span></h2>
                        <p class="reveal delay-2" style="color: var(--ae-slate); font-size: 1.15rem; line-height: 1.6; margin-bottom: 32px; font-weight: 500;">Si no encuentras la respuesta que buscas, nuestro equipo técnico está disponible para ayudarte a integrar la API en tu infraestructura.</p>
                        
                        <!-- Avatar group & trust -->
                        <div class="reveal delay-2" style="display: flex; align-items: center; gap: 16px; margin-bottom: 32px; padding: 12px 20px; background: #ffffff; border-radius: 16px; border: 1px solid var(--ae-border); box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); max-width: max-content;">
                            <div style="display: flex;">
                                <div style="width: 40px; height: 40px; border-radius: 50%; border: 3px solid #ffffff; background: linear-gradient(135deg, #fca5a5, #ef4444); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.85rem; z-index: 3; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">ES</div>
                                <div style="width: 40px; height: 40px; border-radius: 50%; border: 3px solid #ffffff; background: linear-gradient(135deg, #93c5fd, #3b82f6); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.85rem; margin-left: -12px; z-index: 2; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">AG</div>
                                <div style="width: 40px; height: 40px; border-radius: 50%; border: 3px solid #ffffff; background: linear-gradient(135deg, #6ee7b7, #10b981); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 0.85rem; margin-left: -12px; z-index: 1; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">MJ</div>
                            </div>
                            <div>
                                <div style="font-weight: 800; color: var(--ae-dark); font-size: 0.95rem;">Soporte en España</div>
                                <div style="color: var(--ae-slate); font-size: 0.8rem; font-weight: 500;">Tiempo de respuesta &lt; 2h</div>
                            </div>
                        </div>

                        <a href="mailto:soporte@apiempresas.es" class="btn-ae reveal delay-3" style="background: linear-gradient(135deg, var(--ae-blue), var(--ae-teal)); color: #ffffff; border-radius: 14px; box-shadow: 0 10px 20px -5px rgba(37,99,235,0.4); padding: 16px 32px; font-size: 1.05rem; display: inline-flex; align-items: center; gap: 12px; transition: all 0.4s ease; border: none; font-weight: 700;" onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='0 15px 30px -5px rgba(37, 99, 235, 0.5)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 20px -5px rgba(37,99,235,0.4)';">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            Contactar equipo
                        </a>
                    </div>

                    <!-- Right Column: Accordion -->
                    <div class="faq-accordion" style="width: 100%; margin: 0;">
                        <!-- Q1 -->
                        <div class="faq-item reveal delay-1">
                            <div class="faq-header">
                                <h3>¿Qué datos devuelve la API?</h3>
                                <div class="faq-icon"></div>
                            </div>
                            <div class="faq-content">
                                <div class="faq-body">
                                    Devuelve la razón social oficial, el estado de actividad (activa, extinguida, etc.), la fecha de constitución, la provincia, y la actividad principal (CNAE) obtenida directamente del Registro Mercantil.
                                </div>
                            </div>
                        </div>
                        
                        <!-- Q2 -->
                        <div class="faq-item reveal delay-1">
                            <div class="faq-header">
                                <h3>¿Puedo probarla gratis?</h3>
                                <div class="faq-icon"></div>
                            </div>
                            <div class="faq-content">
                                <div class="faq-body">
                                    Sí, al registrarte obtienes un plan Free con <?= $freeLimit ?> consultas gratuitas sin caducidad para que puedas hacer pruebas en nuestro entorno Sandbox o en producción sin ningún tipo de compromiso.
                                </div>
                            </div>
                        </div>

                        <!-- Q3 -->
                        <div class="faq-item reveal delay-2">
                            <div class="faq-header">
                                <h3>¿La información es oficial?</h3>
                                <div class="faq-icon"></div>
                            </div>
                            <div class="faq-content">
                                <div class="faq-body">
                                    Absolutamente. Todos nuestros datos provienen de fuentes oficiales del Estado, como el Registro Mercantil Central y el BORME, garantizando su validez y actualización constante.
                                </div>
                            </div>
                        </div>

                        <!-- Q4 -->
                        <div class="faq-item reveal delay-2">
                            <div class="faq-header">
                                <h3>¿Qué tipo de autenticación utiliza la API?</h3>
                                <div class="faq-icon"></div>
                            </div>
                            <div class="faq-content">
                                <div class="faq-body">
                                    La API utiliza autenticación estándar mediante Bearer Token (API Key) en los encabezados HTTP. Al crear tu cuenta obtendrás al instante tus credenciales para los entornos de Sandbox y Producción.
                                </div>
                            </div>
                        </div>

                        <!-- Q5 -->
                        <div class="faq-item reveal delay-3">
                            <div class="faq-header">
                                <h3>¿Cuánto se tarda en integrar?</h3>
                                <div class="faq-icon"></div>
                            </div>
                            <div class="faq-content">
                                <div class="faq-body">
                                    Nuestra API REST está diseñada con estándares modernos y es extremadamente sencilla. Un desarrollador promedio puede completar la integración y validar su primera empresa en menos de una hora. Dispones de documentación detallada para guiarte.
                                </div>
                            </div>
                        </div>

                        <!-- Q6 -->
                        <div class="faq-item reveal delay-3">
                            <div class="faq-header">
                                <h3>¿Se puede integrar en cualquier lenguaje o plataforma?</h3>
                                <div class="faq-icon"></div>
                            </div>
                            <div class="faq-content">
                                <div class="faq-body">
                                    Sí. Al ser una API REST estándar que devuelve respuestas en JSON, puedes consumirla desde cualquier lenguaje (PHP, Node.js, Python, Java, C#, Go) o integrarla en ERPs/CRMs como Salesforce, HubSpot, SAP o herramientas no-code como Zapier y Make.
                                </div>
                            </div>
                        </div>

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
                        <p style="font-size: 1.25rem; margin-bottom: 48px; color: #E2E8F0; max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.6;">Consulta datos empresariales oficiales en tiempo real, automatiza validaciones de CIF y conecta nuestra API REST en minutos.</p>
                        
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

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        const jsonExamples = {
            get_companies: {
                success: true,
                data: {
                    id: 12345,
                    name: "TECH FLOW SOLUTIONS SL",
                    cif: "B12345678",
                    cnae: "6201",
                    cnae_label: "Programación informática",
                    founded: "2024-03-12",
                    province: "MADRID",
                    municipality: "MADRID",
                    address: "CALLE DE LA TECNOLOGIA 42",
                    status: "ACTIVA",
                    score: 94
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
                    { name: "INDUSTRIA DE DISENO TEXTIL SA", cif: "A15075062" },
                    { name: "INDITEX LOGISTICA SA", cif: "B00000001" }
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
                    cif: "A01001411",
                    company_name: "RHEINMETALL EXPAL MUNITIONS SA",
                    summary: {
                        total_contracts: 32,
                        total_amount: "617746086.47",
                        currency: "EUR"
                    },
                    contracts: [
                        {
                            tender_id: "https://contrataciondelestado.es/sindicacion/licitacionesPerfilContratante/20344437",
                            title: "Suministro de 27.000 granadas de mortero de 81 mm...",
                            contracting_authority: "Jefatura de Asuntos Económicos del Mando de Apoyo Logístico",
                            award_date: "2026-08-25",
                            amount: "4395320.00",
                            currency: "EUR",
                            tender_url: "https://contrataciondelestado.es/wps/poc?uri=deeplink:detalle_licitacion&idEvl=B4EkDEGFvaA%2Bk2oCbDosIw%3D%3D"
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
                    cif: "A01001411",
                    company_name: "RHEINMETALL EXPAL MUNITIONS SA",
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

        // CTA Click Tracking
        $(document).on('click', '[data-cta="radar_home"]', function(e) {
            const source = $(this).data('source') || 'home_generic';
            $.post('<?= site_url("api/tracking/event") ?>', {
                event_type: 'cta_click',
                source: source
            });
        });

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
            } else {
                knob.style.transform = 'translateX(0px)';
                labelMonthly.style.color = '#2563eb';
                labelMonthly.style.fontWeight = '800';
                labelAnnual.style.color = '#94a3b8';
                labelAnnual.style.fontWeight = '600';
                
                pricePro.textContent = pricePro.dataset.monthly;
                priceBiz.textContent = priceBiz.dataset.monthly;
            }
        }
    </script>
</body>
</html>

