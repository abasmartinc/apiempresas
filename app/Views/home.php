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
        <section class="hero container">
            <h1 class="reveal">
                API de empresas para validar CIF y 
                <span class="gradient-text">verificar datos oficiales en España</span>
            </h1>
            <p class="reveal delay-1">Consulta datos oficiales de empresas españolas y accede a nuevas oportunidades de negocio con información basada en Registro Mercantil y BORME. También puedes detectar empresas nuevas en España listas para prospección con Radar B2B.</p>
            
            <div class="hero-btns reveal delay-3">
                <a href="#buscar" class="btn-ae btn-ae-primary">Validar CIF gratis</a>
                <a href="<?= getRadarRedirect('home_hero') ?>" class="btn-ae btn-ae-outline" data-cta="radar_home" data-source="home_hero">Ver Radar en acción</a>
            </div>
            
            <span class="trust-tag reveal delay-3">Datos empresariales oficiales · Registro Mercantil · BORME</span>
        </section>

        <!-- 2. BLOQUE DE BÚSQUEDA -->
        <section id="buscar" class="search-section container">
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
                        <button id="btnBuscar" class="btn-ae btn-ae-primary" style="height: 72px; padding: 0 48px; border-radius: 14px; font-size: 1.15rem;">Validar ahora</button>
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

                <p style="text-align: center; font-size: 0.9rem; color: #64748b; margin-top: 8px; font-weight: 500;">¿Prefieres ver oportunidades directamente? → <a href="<?= getRadarRedirect('home_search') ?>" style="color: var(--ae-blue); text-decoration: none; font-weight: 800;" data-cta="radar_home" data-source="home_search">Ver Radar B2B</a></p>

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
                        <span>Datos para prospección</span>
                    </div>
                </div>
                <p style="text-align: center; font-size: 1rem; color: var(--ae-slate); margin-top: 16px; font-weight: 600;">¿Buscas oportunidades para prospectar? <a href="<?= getRadarRedirect('home_search') ?>" style="color: var(--ae-blue); font-weight: 800; text-decoration: none; border-bottom: 2px solid rgba(37, 99, 235, 0.2); transition: all 0.2s;" onmouseover="this.style.borderColor='var(--ae-blue)'" onmouseout="this.style.borderColor='rgba(37, 99, 235, 0.2)'" data-cta="radar_home" data-source="home_search">Ver Radar B2B.</a></p>
            </div>
        </section>

        <!-- 3. BLOQUE DE AUTORIDAD -->
        <section class="band">
            <div class="container">
                <div class="band-header" style="margin-left: auto; margin-right: auto; text-align: center;">
                    <div class="pro-badge pro-badge-blue reveal" style="margin-left: auto; margin-right: auto;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                        Soluciones integrales
                    </div>
                    <h2 class="reveal delay-1">Datos empresariales para <span class="gradient-text">validar, integrar y vender mejor</span></h2>
                    <p class="reveal delay-2">Acceda a información veraz para mejorar sus procesos de validación de clientes o para potenciar sus equipos comerciales con datos frescos.</p>
                </div>

                <div class="grid-3">
                    <div class="feature-card card-blue reveal delay-1">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                            <div class="icon-box" style="margin-bottom: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            </div>
                            <h3 style="margin-bottom: 0; font-size: 1.2rem; font-weight: 850;">Validación y consulta</h3>
                        </div>
                        <p>Compruebe la existencia de sociedades, verifique CIFs y acceda a los datos de registro básicos con total rapidez.</p>
                    </div>
                    <div class="feature-card card-teal reveal delay-2">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                            <div class="icon-box" style="margin-bottom: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="22" y1="12" x2="18" y2="12"></line><line x1="6" y1="12" x2="2" y2="12"></line><line x1="12" y1="6" x2="12" y2="2"></line><line x1="12" y1="22" x2="12" y2="18"></line></svg>
                            </div>
                            <h3 style="margin-bottom: 0; font-size: 1.2rem; font-weight: 850;">Prospección comercial</h3>
                        </div>
                        <p>Identifique empresas recién creadas antes que su competencia y priorice sus esfuerzos comerciales con precisión.</p>
                    </div>
                    <div class="feature-card card-indigo reveal delay-3">
                        <div style="display: flex; align-items: center; gap: 20px; margin-bottom: 20px;">
                            <div class="icon-box" style="margin-bottom: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                            </div>
                            <h3 style="margin-bottom: 0; font-size: 1.2rem; font-weight: 850;">Integración vía API</h3>
                        </div>
                        <p>Automatice sus flujos internos conectando su CRM o ERP directamente a nuestra base de datos oficial.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- 4. DOS FORMAS DE CRECER -->
        <section class="dual-path">
            <div class="container">
                <div class="band-header" style="text-align: center; margin-left: auto; margin-right: auto;">
                    <h2 class="reveal">Una plataforma, <span style="color: var(--ae-teal);">dos formas de crecer</span></h2>
                    <p class="reveal delay-1">Elige el camino que mejor se adapte a tu operativa diaria. Radar está pensado para equipos comerciales. La API está diseñada para integración y automatización.</p>
                </div>

                <div class="path-grid">
                    <div class="path-card reveal delay-1">
                        <h3 style="display: flex; align-items: center; gap: 12px;">
                            <div style="background: rgba(37,99,235,0.1); padding: 10px; border-radius: 10px; color: #3B82F6; display: flex; align-items: center; justify-content: center;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            </div>
                            Equipos comerciales
                        </h3>
                        <p>Utilice Radar B2B para detectar oportunidades de negocio de forma visual y sin necesidad de programación.</p>
                        <ul class="path-list">
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Detección de empresas recién creadas
                            </li>
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Filtrado por actividad y provincia
                            </li>
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Exportación de leads comerciales
                            </li>
                        </ul>
                        <a href="<?= getRadarRedirect('home_dual_block') ?>" class="btn-ae" style="background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #0F172A; font-weight: 800; border: none; width: 100%; box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3);" data-cta="radar_home" data-source="home_dual_block">Ver Radar</a>
                    </div>
                    <div class="path-card reveal delay-2">
                        <h3 style="display: flex; align-items: center; gap: 12px;">
                            <div style="background: rgba(16,185,129,0.1); padding: 10px; border-radius: 10px; color: #10B981; display: flex; align-items: center; justify-content: center;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                            </div>
                            Equipos técnicos
                        </h3>
                        <p>Integre nuestra API para automatizar la validación de empresas y el enriquecimiento de datos en sus sistemas.</p>
                        <ul class="path-list">
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Endpoints REST de alta disponibilidad
                            </li>
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Documentación técnica detallada
                            </li>
                            <li>
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Escalabilidad para grandes volúmenes
                            </li>
                        </ul>
                        <a href="<?= site_url('documentation') ?>" class="btn-ae" style="background: linear-gradient(135deg, #fbbf24, #f59e0b); color: #0F172A; font-weight: 800; border: none; width: 100%; box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3);">Documentación API</a>
                    </div>
                </div>
                <p style="text-align: center; margin-top: 40px; color: var(--ae-slate); font-weight: 600;">Si tu objetivo es vender o prospectar directamente, utiliza Radar B2B. Si necesitas integrar datos en tu sistema, utiliza la API.</p>
            </div>
        </section>

        <!-- 5. BLOQUE RADAR -->
        <section class="band">
            <div class="container product-flex">
                <div class="product-info">
                    <div class="pro-badge pro-badge-blue reveal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg>
                        Inteligencia de Ventas
                    </div>
                    <h2 class="reveal delay-1">Radar B2B para detectar nuevas empresas <span class="gradient-text">con potencial comercial</span></h2>
                    <p class="reveal delay-1">Accede a sociedades recién creadas, filtra por actividad y ubicación, y trabaja oportunidades antes de que el mercado se sature. La mayoría de empresas nuevas solo son una oportunidad real durante sus primeros días de actividad.</p>
                    
                    <div class="feature-grid-simple reveal delay-2">
                        <div class="feature-tag">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Alertas diarias
                        </div>
                        <div class="feature-tag">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Filtros avanzados
                        </div>
                        <div class="feature-tag">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Exportación CSV
                        </div>
                        <div class="feature-tag">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Datos oficiales
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; gap: 20px;" class="reveal delay-3">
                        <a href="<?= getRadarRedirect('home_product') ?>" class="btn-ae btn-ae-primary" data-cta="radar_home" data-source="home_product">Ver oportunidades</a>
                        <a href="<?= site_url('leads-empresas-nuevas') ?>" style="color: var(--ae-slate); font-weight: 700; font-size: 0.95rem; text-decoration: none; border-bottom: 2px solid transparent; transition: all 0.3s ease;" onmouseover="this.style.color='var(--ae-blue)'; this.style.borderColor='var(--ae-blue)';" onmouseout="this.style.color='var(--ae-slate)'; this.style.borderColor='transparent';" data-cta="radar_home" data-location="radar_block">Cómo funciona</a>
                    </div>
                </div>

                <div class="product-visual reveal delay-2" role="img" aria-label="Vista previa del Dashboard de Inteligencia de Ventas Radar B2B">
                    <div class="visual-decoration"></div>
                    <div class="mockup-container">
                        <!-- Floating Glassmorphism Alert -->
                        <div class="floating-alert">
                            <div class="alert-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                            </div>
                            <div class="alert-text">
                                <h6>Match de ICP Alto</h6>
                                <p>TechVision S.L. ha sido añadida</p>
                            </div>
                        </div>

                        <div class="mockup-browser">
                            <div class="mockup-header">
                                <div class="dot"></div><div class="dot"></div><div class="dot"></div>
                            </div>
                            
                            <!-- Premium Dark Dashboard UI -->
                            <div class="abstract-dashboard">
                                <div class="dashboard-bg-glow"></div>
                                
                                <div class="dashboard-sidebar">
                                    <div class="db-icon active" style="background: #2563EB; display: flex; align-items: center; justify-content: center;">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                    </div>
                                    <div class="db-icon"></div>
                                    <div class="db-icon"></div>
                                    <div class="db-icon" style="margin-top: auto; opacity: 0.5;"></div>
                                </div>
                                
                                <div class="dashboard-main">
                                    <div class="db-top">
                                        <div class="db-search">
                                            <span style="color: #64748B; font-size: 0.8rem;">Buscar empresas, CIF...</span>
                                        </div>
                                        <div class="db-user"></div>
                                    </div>
                                    
                                    <div class="db-stats">
                                        <div class="db-stat-card">
                                            <div class="db-stat-label">Nuevas Hoy</div>
                                            <div class="db-stat-val">412 <span style="background: rgba(16, 185, 129, 0.1); color: #10B981;">+12%</span></div>
                                        </div>
                                        <div class="db-stat-card">
                                            <div class="db-stat-label">Oportunidades</div>
                                            <div class="db-stat-val">89 <span style="background: rgba(37, 99, 235, 0.1); color: var(--ae-blue);">Activas</span></div>
                                        </div>
                                        <div class="db-stat-card">
                                            <div class="db-stat-label">Ahorro Tiempo</div>
                                            <div class="db-stat-val">85% <span style="background: #F1F5F9; color: #64748B;">IA</span></div>
                                        </div>
                                    </div>
                                    
                                    <div class="db-table" style="background: transparent; border: none; box-shadow: none; padding: 0;">
                                        <div class="db-table-header" style="margin-bottom: 12px; padding: 0 4px;">
                                            <div class="db-table-title" style="font-size: 1.1rem;">
                                                Radar de Prospectos <div class="db-live-badge" style="margin-left: 8px;"></div>
                                            </div>
                                        </div>
                                        
                                        <div class="db-row" style="margin-bottom: 8px;">
                                            <div class="db-col-main">
                                                <div class="db-company" style="display: flex; align-items: center; gap: 8px;">
                                                    TechVision Global S.L.
                                                    <span class="db-badge" style="background: #22c55e; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.6rem;">NUEVO</span>
                                                </div>
                                                <div class="db-meta">Madrid • Hace 2h</div>
                                            </div>
                                            <div class="db-btn-action" style="padding: 8px 14px; background: #2563EB; font-size: 0.7rem;">Contactar ahora</div>
                                        </div>
                                        
                                        <div class="db-row" style="margin-bottom: 8px;">
                                            <div class="db-col-main">
                                                <div class="db-company" style="display: flex; align-items: center; gap: 8px;">
                                                    Quantum Finance
                                                    <span class="db-badge" style="background: #3b82f6; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.6rem;">INTERÉS</span>
                                                </div>
                                                <div class="db-meta">Barcelona • Hace 5h</div>
                                            </div>
                                            <div class="db-btn-action" style="padding: 8px 14px; background: #2563EB; font-size: 0.7rem;">Contactar ahora</div>
                                        </div>
                                        
                                        <div class="db-row">
                                            <div class="db-col-main">
                                                <div class="db-company" style="display: flex; align-items: center; gap: 8px;">
                                                    EcoLogistics Sur
                                                    <span class="db-badge" style="background: #f59e0b; color: white; padding: 2px 8px; border-radius: 4px; font-size: 0.6rem;">PREMIUM</span>
                                                </div>
                                                <div class="db-meta">Sevilla • Hace 1d</div>
                                            </div>
                                            <div class="db-btn-action" style="padding: 8px 14px; background: #2563EB; font-size: 0.7rem;">Contactar ahora</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- End Premium Dashboard UI -->

                    </div>
                </div>
            </div>
        </section>

        <!-- 6. BLOQUE API -->
        <section class="band band-light">
            <div class="container product-flex" style="flex-direction: row-reverse;">
                <div class="product-info">
                    <div class="pro-badge pro-badge-green reveal">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                        Para Desarrolladores
                    </div>
                    <h2 class="reveal delay-1">API de empresas para validar, <span class="gradient-text">consultar e integrar datos</span></h2>
                    <p class="reveal delay-1">Incorpore información empresarial oficial directamente en sus procesos de registro, formularios o aplicaciones internas.</p>
                    <ul class="path-list reveal delay-2" style="margin-bottom: 48px;">
                        <li style="color: var(--ae-dark); border-bottom: none; padding: 6px 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Validación automática de CIF en tiempo real
                        </li>
                        <li style="color: var(--ae-dark); border-bottom: none; padding: 6px 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Enriquecimiento de datos de prospectos
                        </li>
                        <li style="color: var(--ae-dark); border-bottom: none; padding: 6px 0;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#10B981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            Fácil integración vía JSON / REST
                        </li>
                    </ul>
                    <a href="<?= site_url('api-empresas') ?>" class="btn-ae btn-ae-primary reveal delay-3" style="background: #10B981; border-color: #10B981; box-shadow: 0 10px 20px rgba(16, 185, 129, 0.2);">Explorar API</a>
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

        <!-- 7. COMPARATIVA -->
        <section class="band">
            <div class="container">
                <div class="band-header" style="text-align: center; margin-left: auto; margin-right: auto; max-width: 600px;">
                    <div class="pro-badge pro-badge-blue reveal" style="background: rgba(37,99,235,0.05); border: none; margin-left: auto; margin-right: auto;">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        Diferencias clave
                    </div>
                    <h2 class="reveal delay-1">¿Radar o API? Elige según cómo trabajas</h2>
                    <p class="reveal delay-2">Dos formas de acceder a nuestra base de datos. Selecciona la herramienta que mejor se adapte a las capacidades de tu equipo. Radar está orientado a acción comercial. La API está orientada a integración y automatización.</p>
                </div>
                <div class="comp-grid-premium">
                    <div class="comp-card-premium card-radar reveal delay-1">
                        <svg class="comp-bg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><circle cx="12" cy="12" r="10"></circle><polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon></svg>
                        <div class="tag-premium">Para Equipos Comerciales</div>
                        <h3>Radar B2B</h3>
                        <p>Interfaz web lista para usar. Filtra, descubre y exporta nuevas oportunidades comerciales diariamente sin necesidad de programación.</p>
                        <a href="<?= getRadarRedirect('home_product') ?>" class="btn-ae btn-ae-primary" style="width: 100%; padding: 20px; font-size: 1.1rem; box-shadow: 0 10px 20px rgba(37,99,235,0.2);" data-cta="radar_home" data-source="home_product">Ver oportunidades</a>
                    </div>
                    <div class="comp-card-premium card-api reveal delay-2">
                        <svg class="comp-bg-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                        <div class="tag-premium">Para Desarrolladores</div>
                        <h3>API REST</h3>
                        <p>Endpoints JSON para automatizar la validación de CIFs e integrar datos de empresas directamente en tu propio software o CRM.</p>
                        <a href="<?= site_url('documentation') ?>" class="btn-ae" style="width: 100%; padding: 20px; font-size: 1.1rem; background: rgba(255,255,255,0.1); color: #fff; border: 1px solid rgba(255,255,255,0.1); transition: all 0.3s ease;">Ver Documentación API</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- 8. PRICING -->
        <section id="precios" class="band band-light">
            <div class="container">
                <div class="band-header" style="text-align: left; max-width: 800px;">
                    <h2 class="reveal delay-1">Planes transparentes para cualquier volumen</h2>
                    <div style="width: 60px; height: 4px; background: linear-gradient(90deg, #4f46e5, #4b9a69); margin-top: 16px; margin-bottom: 24px;"></div>
                    <p class="reveal delay-2" style="font-size: 1.1rem; color: var(--ae-slate);">Empieza validando CIF y razón social en Sandbox. Cuando lo lleves a producción, escala a Pro/Business con control de consumo y trazabilidad. Sin permanencias, sin costes ocultos.<br><br><strong style="color: var(--ae-dark);">Los planes siguientes corresponden al acceso a la API. Radar B2B dispone de una suscripción independiente orientada a equipos comerciales.</strong></p>
                </div>
                
                <div class="tier-grid" style="margin-top: 48px;">
                    <!-- FREE -->
                    <div class="tier tier-free reveal delay-1">
                        <div class="tier-tag">TESTING</div>
                        <h3>Free</h3>
                        <div class="tier-subtitle">Para probar la API</div>
                        <div class="tier-desc">Prueba la API con datos reales y valida resultados antes de pasar a producción.</div>
                        <div class="price">0€<span>/mes</span></div>
                        <ul class="tier-features">
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> 100 consultas al mes</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Acceso al mismo motor de validación</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Datos oficiales para comprobar resultados</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Sin tarjeta de crédito</li>
                        </ul>
                        <a href="<?= site_url('register?plan=free') ?>" class="btn-tier">Empezar gratis</a>
                    </div>
                    
                    <!-- PRO -->
                    <div class="tier tier-pro reveal delay-2">
                        <div class="tier-tag">MÁS ELEGIDO</div>
                        <h3>Pro</h3>
                        <div class="tier-subtitle">Para automatizar validaciones</div>
                        <div class="tier-desc">La opción ideal para SaaS, ERPs y productos que ya necesitan validación en producción.</div>
                        <div class="price">19€<span>/mes</span></div>
                        <ul class="tier-features">
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> 3.000 consultas al mes</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Verificación completa y actualizada</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Tiempo real para automatización</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Ideal para facturación y scoring</li>
                        </ul>
                        <a href="<?= site_url('register?plan=pro') ?>" class="btn-tier">Empezar con Pro</a>
                    </div>
                    
                    <!-- BUSINESS -->
                    <div class="tier tier-biz reveal delay-3">
                        <div class="tier-tag">ESCALA</div>
                        <h3>Business</h3>
                        <div class="tier-subtitle">Para equipos y alto volumen</div>
                        <div class="tier-desc">Pensado para plataformas con más carga, procesos críticos y necesidades de mayor disponibilidad.</div>
                        <div class="price">49€<span>/mes</span></div>
                        <ul class="tier-features">
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> 10.000 consultas al mes</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Infraestructura preparada para alta carga</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> SLA y alta disponibilidad</li>
                            <li><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> Soporte prioritario</li>
                        </ul>
                        <a href="<?= site_url('register?plan=business') ?>" class="btn-tier">Empezar con Business</a>
                    </div>
                </div>
                <div style="text-align: center; margin-top: 48px;">
                    <a href="<?= getRadarRedirect('home_pricing') ?>" class="btn-ae" style="background: #12b48a; color: white; padding: 18px 36px; font-size: 1.1rem; border-radius: 14px; box-shadow: 0 10px 20px rgba(18, 180, 138, 0.2); font-weight: 800;" data-cta="radar_home" data-source="home_pricing">Ver Radar B2B (79€/mes)</a>
                </div>
            </div>
        </section>

        <!-- 9. FAQ -->
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
                        <p class="reveal delay-2" style="color: var(--ae-slate); font-size: 1.15rem; line-height: 1.6; margin-bottom: 32px; font-weight: 500;">Si no encuentras la respuesta que buscas, nuestro equipo de expertos está disponible para ayudarte a integrar la API o configurar tu Radar B2B al máximo nivel.</p>
                        
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
                                    Sí, al registrarte obtienes un plan Free con 100 consultas gratuitas al mes para que puedas hacer pruebas en nuestro entorno Sandbox o en producción sin ningún tipo de compromiso.
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
                                <h3>¿Qué diferencia hay entre API y Radar?</h3>
                                <div class="faq-icon"></div>
                            </div>
                            <div class="faq-content">
                                <div class="faq-body">
                                    Radar B2B es una plataforma visual lista para usar, ideal para equipos comerciales que buscan prospectos. La API es un servicio técnico (endpoints JSON) pensado para que los desarrolladores integren los datos directamente en su propio software (CRM, ERP, procesos de alta).
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
                                <h3>¿Sirve para prospección B2B?</h3>
                                <div class="faq-icon"></div>
                            </div>
                            <div class="faq-content">
                                <div class="faq-body">
                                    Sí, especialmente a través de nuestro producto Radar B2B. Podrás detectar diariamente qué nuevas empresas se han creado en España y filtrarlas por sector o provincia para llegar a ellas antes que tu competencia.
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>

        <!-- 10. CTA FINAL -->
        <section class="band" style="background: #ffffff;">
            <div class="container">
                <div style="background: linear-gradient(135deg, #1E3A8A 0%, #2563EB 100%); border-radius: 32px; padding: 56px 32px; text-align: center; position: relative; overflow: hidden; box-shadow: 0 40px 100px -20px rgba(37, 99, 235, 0.4);">
                    <!-- Decorative Glows -->
                    <div style="position: absolute; top: -50%; left: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(96, 165, 250, 0.4) 0%, transparent 70%); filter: blur(60px); pointer-events: none; z-index: 0;"></div>
                    <div style="position: absolute; bottom: -50%; right: -10%; width: 500px; height: 500px; background: radial-gradient(circle, rgba(16, 185, 129, 0.2) 0%, transparent 70%); filter: blur(60px); pointer-events: none; z-index: 0;"></div>
                    
                    <!-- Content -->
                    <div style="position: relative; z-index: 1;">
                        <h2 style="font-size: 2.8rem; font-weight: 950; margin-bottom: 24px; color: #ffffff; letter-spacing: -0.02em; line-height: 1.1;">Empieza hoy a validar empresas o encontrar nuevos clientes</h2>
                        <p style="font-size: 1.25rem; margin-bottom: 48px; color: #E2E8F0; max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.6;">Consulta datos empresariales, intégralos en tu sistema o trabaja oportunidades con Radar B2B.</p>
                        
                        <div style="display: flex; justify-content: center; gap: 16px; flex-wrap: wrap;">
                            <a href="#buscar" class="btn-ae" style="background: #ffffff; color: #0F172A; padding: 18px 32px; font-size: 1.1rem; border: none; box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1); font-weight: 800;">Validar CIF gratis</a>
                            <a href="<?= getRadarRedirect('home_final') ?>" class="btn-ae" style="background: #12b48a; color: #ffffff; padding: 18px 32px; font-size: 1.1rem; border: none; box-shadow: 0 10px 20px rgba(18, 180, 138, 0.3); font-weight: 800;" data-cta="radar_home" data-source="home_final">Ver oportunidades</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <?= view('partials/footer') ?>

    <script>
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
            if (typeof trackRadarEvent === 'function') {
                trackRadarEvent({ event_type: 'cta_click', source: source });
            } else {
                $.post('<?= site_url("api/tracking/event") ?>', {
                    event_type: 'cta_click',
                    source: source
                });
            }
        });
    </script>
</body>

</html>
