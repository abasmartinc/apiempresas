<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head') ?>
    <link rel="stylesheet"
        href="<?= base_url('public/css/home.css?v=' . (file_exists(FCPATH . 'public/css/home.css') ? filemtime(FCPATH . 'public/css/home.css') : time())) ?>" />
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div><?= view('partials/header', [], ['debug' => false]) ?>

    <main>

        <!-- HERO DUAL -->
        <section class="hero-dual container" id="inicio">
            <span class="eyebrow">Datos oficiales de empresas en tiempo real</span>
            <h1 class="title">
                Valida CIF, verifica empresas y <span class="grad">convierte datos en oportunidades</span>
            </h1>
            <p class="subheadline">
                Accede a datos oficiales, scoring y señales de negocio directamente desde fuentes oficiales. La
                infraestructura definitiva para productos que escalan.
            </p>

            <div class="dual-grid">
                <!-- API CARD -->
                <div class="dual-card api-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="16 18 22 12 16 6"></polyline>
                                <polyline points="8 6 2 12 8 18"></polyline>
                            </svg>
                        </div>
                        <div class="card-tags">
                            <span class="card-tag">REST API</span>
                            <span class="card-tag">JSON</span>
                            <span class="card-tag">Ready to scale</span>
                        </div>
                    </div>
                    <h3>API Empresas</h3>
                    <p>Integra validación de CIF, datos oficiales y scoring en minutos con una infraestructura latente y
                        segura.</p>
                    <a class="btn" href="<?= site_url('api-empresas') ?>">Ver las capacidades de la API →</a>
                </div>

                <!-- RADAR CARD -->
                <div class="dual-card radar-card">
                    <div class="card-header">
                        <div class="card-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10"></circle>
                                <path d="M12 2v4"></path>
                                <path d="M12 18v4"></path>
                                <path d="M4.93 4.93l2.83 2.83"></path>
                                <path d="M16.24 16.24l2.83 2.83"></path>
                                <path d="M2 12h4"></path>
                                <path d="M18 12h4"></path>
                                <path d="M4.93 19.07l2.83-2.83"></path>
                                <path d="M16.24 7.76l2.83-2.83"></path>
                            </svg>
                        </div>
                        <div class="card-tags">
                            <span class="card-tag">INTELIGENCIA B2B</span>
                            <span class="card-tag">IA</span>
                            <span class="card-tag">Lead scoring</span>
                        </div>
                    </div>
                    <h3>Radar de Empresas</h3>
                    <p>Detecta nuevas empresas, prioriza oportunidades y acelera tu prospección B2B antes
                        que tu competencia.</p>
                    <a class="btn secondary" href="<?= site_url('leads-empresas-nuevas') ?>">Explorar las ventajas del
                        Radar →</a>
                </div>
            </div>

            <div class="cta-row">
                <a class="btn btn_start" href="<?= site_url('register') ?>">Crear cuenta gratis</a>
                <a class="btn ghost" href="#buscar">Ver ejemplo en vivo</a>
            </div>
        </section>
        <!-- HERO DUAL -->


        <!-- BUSCADOR -->
        <section id="buscar" class="search-section container">
            <div class="search-card">
                <div>
                    <h2>Prueba la API con un CIF real</h2>
                    <p class="muted">
                        Consulta al instante datos oficiales y comprueba cómo respondería la API en un caso real.
                    </p>
                </div>

                <div class="search-row">
                    <input class="input" id="q" placeholder="Introduce un CIF o razón social"
                        aria-label="Buscar empresa por nombre o CIF" />
                    <button type="button" class="btn" id="btnBuscar" aria-label="Buscar">Validar ahora</button>
                </div>

                <p class="muted" style="font-size:13px; margin-top: 12px;">
                    Datos obtenidos en tiempo real desde fuentes oficiales
                </p>

                <div id="resultado_container" style="display:none; margin-top: 32px;">
                    <div class="demo-tabs">
                        <button class="tab-btn active" data-tab="visual">Ficha de empresa</button>
                        <button class="tab-btn" data-tab="json">JSON de la API</button>
                    </div>

                    <div id="tab-visual" class="tab-content active">
                        <div id="resultado"></div>
                    </div>

                    <div id="tab-json" class="tab-content">
                        <div class="code-card">
                            <pre><code id="json-output"></code></pre>
                        </div>
                    </div>
                </div>
            </div>
        </section>
        <!-- BUSCADOR -->

        <!-- DOS FORMAS DE CRECER -->
        <section class="container growth-section">
            <div class="band">
                <h3 class="section-heading">Una única plataforma, <span
                        style="background: linear-gradient(90deg, #2152ff, #12b48a); -webkit-background-clip: text; background-clip: text; color: transparent;">dos
                        formas de crecer</span></h3>
                <p class="section-subheading">
                    Integra datos oficiales en tu producto o utilízalos para acelerar tu prospección comercial con una
                    misma infraestructura.
                </p>

                <div class="growth-grid">
                    <!-- API COLUMN -->
                    <div class="path-column growth-api">
                        <div class="growth-card-top">
                            <div class="growth-card-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="16 18 22 12 16 6"></polyline>
                                    <polyline points="8 6 2 12 8 18"></polyline>
                                </svg>
                            </div>
                            <h3 class="growth-card-title">Para tu producto</h3>
                        </div>

                        <p class="growth-card-lead">
                            Automatiza validaciones, enriquece registros y lleva datos oficiales directamente a tu CRM,
                            ERP o SaaS.
                        </p>

                        <div class="growth-feature-list">
                            <div class="feature-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2152ff"
                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span><strong>Validación automática</strong> de CIF en formularios y onboarding</span>
                            </div>

                            <div class="feature-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2152ff"
                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span><strong>Enriquecimiento en tiempo real</strong> con datos oficiales de
                                    empresa</span>
                            </div>

                            <div class="feature-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#2152ff"
                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span><strong>Scoring e insights</strong> para riesgos, validación y
                                    automatización</span>
                            </div>
                        </div>

                        <a href="<?= site_url() ?>documentation" class="btn">Explorar API REST →</a>
                    </div>

                    <!-- RADAR COLUMN -->
                    <div class="path-column growth-radar">
                        <div class="growth-card-top">
                            <div class="growth-card-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M12 2v4"></path>
                                    <path d="M12 18v4"></path>
                                </svg>
                            </div>
                            <h3 class="growth-card-title">Para tu equipo comercial</h3>
                        </div>

                        <p class="growth-card-lead">
                            Detecta nuevas empresas cada día, prioriza oportunidades por scoring y acelera tu
                            prospección B2B.
                        </p>

                        <div class="growth-feature-list">
                            <div class="feature-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#12b48a"
                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span><strong>Detección diaria</strong> de nuevas constituciones</span>
                            </div>

                            <div class="feature-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#12b48a"
                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span><strong>Filtrado avanzado</strong> por sector, provincia y oportunidad</span>
                            </div>

                            <div class="feature-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#12b48a"
                                    stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="20 6 9 17 4 12"></polyline>
                                </svg>
                                <span><strong>Exportación directa</strong> a CSV/Excel para tu flujo comercial</span>
                            </div>
                        </div>

                        <a href="<?= site_url('leads-empresas-nuevas') ?>" class="btn secondary">Explorar Radar B2B
                            →</a>
                    </div>
                </div>
            </div>
        </section>
        <!-- DOS FORMAS DE CRECER -->


        <!-- DECISIONES REALES -->
        <section class="container decisions-section">
            <div>
                <h3 style="margin-top:0;" class="section-heading">Convierte datos en <span
                        style="background: linear-gradient(90deg, #2152ff, #12b48a); -webkit-background-clip: text; background-clip: text; color: transparent;">decisiones
                        reales</span></h3>
                <p class="section-subheading">
                    No se trata solo de consultar información. Se trata de usarla para automatizar procesos, reducir
                    fricción y mejorar resultados.
                </p>
            </div>

            <div class="benefits-grid">
                <div class="benefit-card">
                    <div class="benefit-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                        </svg>
                    </div>
                    <div class="benefit-copy">
                        <h4>Valida empresas automáticamente</h4>
                        <p>Evita errores en onboarding, facturación o alta de proveedores verificando datos oficiales en
                            segundos.</p>
                    </div>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <polygon
                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2">
                            </polygon>
                        </svg>
                    </div>
                    <div class="benefit-copy">
                        <h4>Enriquece tus datos sin esfuerzo</h4>
                        <p>Completa registros con información oficial y mantén tu CRM o base de datos más limpia y útil.
                        </p>
                    </div>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="20" x2="12" y2="10"></line>
                            <line x1="18" y1="20" x2="18" y2="4"></line>
                            <line x1="6" y1="20" x2="6" y2="16"></line>
                        </svg>
                    </div>
                    <div class="benefit-copy">
                        <h4>Prioriza mejor tus leads</h4>
                        <p>Apóyate en scoring y señales para centrarte antes en las empresas con mayor potencial
                            comercial.</p>
                    </div>
                </div>

                <div class="benefit-card">
                    <div class="benefit-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                            stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="8"></circle>
                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            <line x1="11" y1="8" x2="11" y2="14"></line>
                            <line x1="8" y1="11" x2="14" y2="11"></line>
                        </svg>
                    </div>
                    <div class="benefit-copy">
                        <h4>Detecta oportunidades antes</h4>
                        <p>Accede a nuevas empresas en cuanto aparecen y gana velocidad comercial frente a tu
                            competencia.</p>
                    </div>
                </div>
            </div>
        </section>
        <!-- DECISIONES REALES -->


        <!-- USE CASES -->
        <section class="container usecases-section" id="casos-uso">
            <div>
                <h3 class="section-heading">Qué puedes hacer con nuestra plataforma</h3>
                <p class="section-subheading">
                    Casos de uso reales para automatizar procesos, mejorar datos y acelerar tu crecimiento comercial.
                </p>

                <div class="usecases-grid">
                    <!-- API -->
                    <div class="usecase-card usecase-api">
                        <div class="usecase-header">
                            <div class="usecase-icon">API</div>
                            <h3 class="usecase-title">Con la API de Empresas</h3>
                        </div>

                        <p class="usecase-lead">
                            Lleva validación, enriquecimiento y verificación oficial directamente a tus flujos internos.
                        </p>

                        <div class="usecase-list">
                            <div class="usecase-item">
                                <div class="usecase-dot"></div>
                                <span>Validar CIF en formularios y procesos de onboarding</span>
                            </div>

                            <div class="usecase-item">
                                <div class="usecase-dot"></div>
                                <span>Enriquecer leads automáticamente dentro de tu CRM</span>
                            </div>

                            <div class="usecase-item">
                                <div class="usecase-dot"></div>
                                <span>Normalizar bases de datos de clientes y proveedores</span>
                            </div>

                            <div class="usecase-item">
                                <div class="usecase-dot"></div>
                                <span>Verificar empresas antes de operar o facturar</span>
                            </div>
                        </div>
                    </div>

                    <!-- RADAR -->
                    <div class="usecase-card usecase-radar">
                        <div class="usecase-header">
                            <div class="usecase-icon">RDE</div>
                            <h3 class="usecase-title">Con el Radar de Empresas</h3>
                        </div>

                        <p class="usecase-lead">
                            Descubre oportunidades nuevas, filtra mejor y llega antes a las empresas que te interesan.
                        </p>

                        <div class="usecase-list">
                            <div class="usecase-item">
                                <div class="usecase-dot"></div>
                                <span>Detectar nuevas empresas antes que la competencia</span>
                            </div>

                            <div class="usecase-item">
                                <div class="usecase-dot"></div>
                                <span>Priorizar leads por scoring y señales de negocio</span>
                            </div>

                            <div class="usecase-item">
                                <div class="usecase-dot"></div>
                                <span>Segmentar oportunidades por provincia, sector o actividad</span>
                            </div>

                            <div class="usecase-item">
                                <div class="usecase-dot"></div>
                                <span>Preparar mejor la prospección y el primer contacto comercial</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- CUSTOM INTEGRATIONS -->
        <section id="custom-integrations" class="container" style="margin-top: 0px; margin-bottom: 20px;">
            <div class="band"
                style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 24px; padding: 60px 40px; color: #0f172a; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05); border: 1px solid #e2e8f0;">

                <!-- Decorative background elements -->
                <div
                    style="position: absolute; top: 0; right: 0; width: 400px; height: 400px; background: radial-gradient(circle, rgba(33, 82, 255, 0.08) 0%, transparent 70%); pointer-events: none;">
                </div>
                <div
                    style="position: absolute; bottom: 0; left: 0; width: 300px; height: 300px; background: radial-gradient(circle, rgba(18, 180, 138, 0.05) 0%, transparent 70%); pointer-events: none;">
                </div>

                <div class="grid custom-integrations-grid"
                    style="grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; position: relative; z-index: 1;">

                    <!-- Content -->
                    <div>
                        <span class="eyebrow"
                            style="color: #2152ff; border-color: rgba(33, 82, 255, 0.2); background: rgba(33, 82, 255, 0.03);">Enterprise
                            & Medida</span>
                        <h2 style="color: #0f172a; margin-top: 10px; margin-bottom: 20px; font-size: 2.5rem;">
                            Integraciones a medida y Soluciones Enterprise</h2>
                        <p style="color: #475569; font-size: 1.1rem; line-height: 1.6; margin-bottom: 30px;">
                            No nos limitamos a la API estándar. Adaptamos nuestra tecnología para que se fusione
                            perfectamente con tu infraestructura y lógica de negocio.
                        </p>

                        <ul style="list-style: none; padding: 0; margin-bottom: 40px; display: grid; gap: 20px;">
                            <li style="display: flex; gap: 15px; align-items: flex-start;">
                                <div
                                    style="background: rgba(33, 82, 255, 0.1); color: #2152ff; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 4px rgba(33, 82, 255, 0.1);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="16 18 22 12 16 6"></polyline>
                                        <polyline points="8 6 2 12 8 18"></polyline>
                                    </svg>
                                </div>
                                <div>
                                    <h4 style="color: #0f172a; margin: 0 0 5px 0; font-size: 1.1rem; font-weight: 700;">
                                        Endpoints Personalizados</h4>
                                    <p style="color: #64748b; margin: 0; font-size: 0.95rem;">¿Necesitas datos
                                        específicos cruzados o lógica de negocio en la respuesta? Construimos endpoints
                                        exclusivos para tu caso de uso.</p>
                                </div>
                            </li>
                            <li style="display: flex; gap: 15px; align-items: flex-start;">
                                <div
                                    style="background: rgba(18, 180, 138, 0.1); color: #12b48a; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 4px rgba(18, 180, 138, 0.1);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                </div>
                                <div>
                                    <h4 style="color: #0f172a; margin: 0 0 5px 0; font-size: 1.1rem; font-weight: 700;">
                                        Validación Batch / Masiva</h4>
                                    <p style="color: #64748b; margin: 0; font-size: 0.95rem;">Procesa miles de registros
                                        vía CSV, Excel o API asíncrona de alto rendimiento. Ideal para limpieza de CRMs.
                                    </p>
                                </div>
                            </li>
                            <li style="display: flex; gap: 15px; align-items: flex-start;">
                                <div
                                    style="background: rgba(245, 158, 11, 0.1); color: #d97706; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.1);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <path
                                            d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 style="color: #0f172a; margin: 0 0 5px 0; font-size: 1.1rem; font-weight: 700;">
                                        Conectores ERP/CRM</h4>
                                    <p style="color: #64748b; margin: 0; font-size: 0.95rem;">Integración nativa con
                                        SAP, Microsoft Dynamics, Salesforce, HubSpot y plataformas no-code.</p>
                                </div>
                            </li>
                        </ul>

                        <a class="btn" href="<?= site_url() ?>contact?interest=custom_integration"
                            style="background: linear-gradient(90deg, #2152ff, #12b48a); color: white; border: none; font-weight: 700; padding: 15px 30px; border-radius: 12px; box-shadow: 0 8px 16px rgba(33, 82, 255, 0.15);">
                            Hablemos de tu proyecto
                        </a>
                    </div>

                    <!-- Visual/Image -->
                    <div style="position: relative; display: flex; justify-content: center; align-items: center;">
                        <div
                            style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 30px; width: 100%; max-width: 450px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.1);">
                            <div
                                style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
                                <div style="display: flex; gap: 8px;">
                                    <div
                                        style="width: 10px; height: 10px; border-radius: 50%; background: #ff5f56; box-shadow: 0 0 0 1px #e0443e;">
                                    </div>
                                    <div
                                        style="width: 10px; height: 10px; border-radius: 50%; background: #ffbd2e; box-shadow: 0 0 0 1px #dea123;">
                                    </div>
                                    <div
                                        style="width: 10px; height: 10px; border-radius: 50%; background: #27c93f; box-shadow: 0 0 0 1px #1aab29;">
                                    </div>
                                </div>
                                <span
                                    style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #94a3b8; letter-spacing: 0.5px;">POST
                                    /api/v1/custom/verify-batch</span>
                            </div>
                            <pre
                                style="font-family: 'JetBrains Mono', monospace; font-size: 13px; line-height: 1.6; color: #334155; margin: 0; background: transparent; padding: 0;">
<span style="color: #7c3aed;">{</span>
  <span style="color: #2563eb;">"job_id"</span>: <span style="color: #0891b2;">"job_8923_batch_kyb"</span>,
  <span style="color: #2563eb;">"status"</span>: <span style="color: #059669;">"completed"</span>,
  <span style="color: #2563eb;">"processed"</span>: <span style="color: #dc2626;">15420</span>,
  <span style="color: #2563eb;">"fields"</span>: <span style="color: #7c3aed;">[</span>
    <span style="color: #0891b2;">"risk_score_v2"</span>,
    <span style="color: #0891b2;">"erp_match"</span>
  <span style="color: #7c3aed;">]</span>
<span style="color: #7c3aed;">}</span></pre>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- INTEGRATION -->
        <section id="integracion" class="container" style="margin-top: 20px;">
            <div class="band">
                <span class="eyebrow">Developers First</span>
                <h2>Integra en minutos, escala sin límites</h2>
                <p class="muted" style="font-size: 1.1rem; margin-bottom: 40px;">Una API REST simple, rápida y preparada
                    para producción desde el primer día.</p>

                <div class="grid" style="grid-template-columns: repeat(4, 1fr); gap: 24px; text-align: center;">
                    <div class="card">
                        <div style="font-size: 2rem; margin-bottom: 12px;">{ }</div>
                        <h4>JSON REST</h4>
                        <p class="muted" style="font-size: 0.9rem;">Formato estándar y predecible.</p>
                    </div>
                    <div class="card">
                        <div style="font-size: 2rem; margin-bottom: 12px;">⚡</div>
                        <h4>Milisegundos</h4>
                        <p class="muted" style="font-size: 0.9rem;">Respuestas ultra rápidas.</p>
                    </div>
                    <div class="card">
                        <div style="font-size: 2rem; margin-bottom: 12px;">📑</div>
                        <h4>Docs Claras</h4>
                        <p class="muted" style="font-size: 0.9rem;">OpenAPI / Swagger.</p>
                    </div>
                    <div class="card">
                        <div style="font-size: 2rem; margin-bottom: 12px;">🚀</div>
                        <h4>Escalable</h4>
                        <p class="muted" style="font-size: 0.9rem;">Preparado para alto volumen.</p>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 48px;">
                    <a href="<?= site_url() ?>documentation" class="btn">Ver documentación</a>
                </div>
            </div>
        </section>

        <!-- PRICING -->
        <section id="precios" class="pricing container">
            <h2>Planes transparentes para cualquier volumen</h2>
            <p class="muted">
                Empieza validando CIF y razón social en Sandbox. Cuando lo lleves a producción, escala a Pro/Business
                con control de consumo y trazabilidad.
                Sin permanencias, sin costes ocultos.
            </p>

            <div class="tiers">
                <div class="tier free-plan">
                    <span class="badge">Testing</span>
                    <h3>Free</h3>
                    <p class="tier-subtitle">Para probar la API</p>
                    <p class="tier-desc">
                        Prueba la API con datos reales y valida resultados antes de pasar a producción.
                    </p>
                    <div class="price">0 € <small>/mes</small></div>

                    <ul class="tier__list">
                        <li><strong>100</strong> consultas al mes</li>
                        <li>Acceso al mismo motor de validación</li>
                        <li>Datos oficiales para comprobar resultados</li>
                        <li>Sin tarjeta de crédito</li>
                    </ul>

                    <a class="btn secondary" href="<?= site_url('register?plan=free') ?>">Empezar gratis</a>
                </div>

                <div class="tier featured">
                    <span class="badge">Más elegido</span>
                    <h3>Pro</h3>
                    <p class="tier-subtitle">Para automatizar validaciones</p>
                    <p class="tier-desc">
                        La opción ideal para SaaS, ERPs y productos que ya necesitan validación en producción.
                    </p>
                    <div class="price">19 € <small>/mes</small></div>

                    <ul class="tier__list">
                        <li><strong>3.000</strong> consultas al mes</li>
                        <li>Verificación completa y actualizada</li>
                        <li>Tiempo real para automatización</li>
                        <li>Ideal para facturación y scoring</li>
                    </ul>

                    <a class="btn" href="<?= site_url('register') ?>">Empezar con Pro</a>
                </div>

                <div class="tier business-plan">
                    <span class="badge">Escala</span>
                    <h3>Business</h3>
                    <p class="tier-subtitle">Para equipos y alto volumen</p>
                    <p class="tier-desc">
                        Pensado para plataformas con más carga, procesos críticos y necesidades de mayor disponibilidad.
                    </p>
                    <div class="price">49 € <small>/mes</small></div>

                    <ul class="tier__list">
                        <li><strong>10.000</strong> consultas al mes</li>
                        <li>Infraestructura preparada para alta carga</li>
                        <li>SLA y alta disponibilidad</li>
                        <li>Soporte prioritario</li>
                    </ul>

                    <a class="btn secondary" href="<?= site_url('register') ?>">Empezar con Business</a>
                </div>
            </div>

            <div class="pricing__foot">
                <p class="pricing__hint">
                    Cada consulta corresponde a una verificación completa de empresa en una sola respuesta. Sin costes
                    ocultos ni llamadas parciales.
                </p>

                <div class="enterprise muted">
                    <span>¿Más de <strong>20.000 consultas/mes</strong> o requisitos especiales de
                        SLA/compliance?</span>
                    <span><strong>Planes Enterprise a partir de 299 €/mes</strong>.</span>
                    <a class="enterprise__link" href="<?= site_url() ?>contact?interest=enterprise">Hablemos de tu
                        volumen y requisitos</a>
                </div>
            </div>

            <!-- PLAN COMPARISON TABLE NESTED -->
            <div class="capabilities-wrap">
                <h3 class="capabilities-title">Detalle de capacidades por Plan</h3>

                <div class="capabilities-card">
                    <div class="capabilities-scroll">
                        <table class="capabilities-table">
                            <thead>
                                <tr>
                                    <th>Funcionalidad</th>
                                    <th>Free</th>
                                    <th class="cap-featured-col">Pro</th>
                                    <th>Business</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <div class="cap-col-feature">
                                            <div class="cap-feature-name">
                                                <code>/companies</code>
                                            </div>
                                            <span class="ve-help-btn" onclick="openInfo(event, 'companies')"
                                                title="Ver más info">?</span>
                                        </div>
                                    </td>
                                    <td class="cap-center"><span class="cap-check">✓</span></td>
                                    <td class="cap-center cap-featured-col"><span class="cap-check">✓</span></td>
                                    <td class="cap-center"><span class="cap-check">✓</span></td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="cap-col-feature">
                                            <div class="cap-feature-name">
                                                <code>/score</code>
                                            </div>
                                            <span class="ve-help-btn" onclick="openInfo(event, 'score')"
                                                title="Ver más info">?</span>
                                        </div>
                                    </td>
                                    <td class="cap-center"><span class="cap-muted">Solo Score</span></td>
                                    <td class="cap-center cap-featured-col"><span class="cap-pill basic">Básico</span>
                                    </td>
                                    <td class="cap-center"><span class="cap-pill advanced">Avanzado</span></td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="cap-col-feature">
                                            <div class="cap-feature-name">
                                                <code>/signals</code>
                                            </div>
                                            <span class="ve-help-btn" onclick="openInfo(event, 'signals')"
                                                title="Ver más info">?</span>
                                        </div>
                                    </td>
                                    <td class="cap-center"><span class="cap-cross">✕</span></td>
                                    <td class="cap-center cap-featured-col"><span class="cap-pill basic">Básico</span>
                                    </td>
                                    <td class="cap-center"><span class="cap-pill advanced">Avanzado</span></td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="cap-col-feature">
                                            <div class="cap-feature-name">
                                                <code>/radar</code>
                                            </div>
                                            <span class="ve-help-btn" onclick="openInfo(event, 'radar')"
                                                title="Ver más info">?</span>
                                        </div>
                                    </td>
                                    <td class="cap-center"><span class="cap-muted">Limitado (10)</span></td>
                                    <td class="cap-center cap-featured-col"><span class="cap-note">Limitado (100)</span>
                                    </td>
                                    <td class="cap-center"><span class="cap-business-strong">COMPLETO</span></td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="cap-col-feature">
                                            <div class="cap-feature-name">
                                                <code>/insights</code>
                                            </div>
                                            <span class="ve-help-btn" onclick="openInfo(event, 'insights')"
                                                title="Ver más info">?</span>
                                        </div>
                                    </td>
                                    <td class="cap-center"><span class="cap-cross">✕</span></td>
                                    <td class="cap-center cap-featured-col"><span class="cap-muted">Solo Perfil</span>
                                    </td>
                                    <td class="cap-center"><span class="cap-check">✓</span></td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="cap-col-feature">
                                            <div class="cap-feature-name">
                                                <code>/contact-prep</code>
                                            </div>
                                            <span class="ve-help-btn" onclick="openInfo(event, 'contact-prep')"
                                                title="Ver más info">?</span>
                                        </div>
                                    </td>
                                    <td class="cap-center"><span class="cap-cross">✕</span></td>
                                    <td class="cap-center cap-featured-col"><span class="cap-cross">✕</span></td>
                                    <td class="cap-center"><span class="cap-check">✓</span></td>
                                </tr>

                                <tr>
                                    <td>
                                        <div class="cap-col-feature">
                                            <div class="cap-feature-name">
                                                <code>/webhooks</code>
                                            </div>
                                            <span class="ve-help-btn" onclick="openInfo(event, 'webhooks')"
                                                title="Ver más info">?</span>
                                        </div>
                                    </td>
                                    <td class="cap-center"><span class="cap-cross">✕</span></td>
                                    <td class="cap-center cap-featured-col"><span class="cap-cross">✕</span></td>
                                    <td class="cap-center"><span class="cap-check">✓</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="cap-link-row">
                        <a href="<?= site_url('api-empresas') ?>" class="ve-api-view-all">
                            Explorar todas las capacidades de la API →
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- RADAR SHOWCASE -->
        <section id="radar-leads" class="container radar-conversion">
            <div class="band">

                <div class="grid" style="grid-template-columns: 1.1fr 0.9fr; gap: 80px; align-items: center;">

                    <!-- TEXTO -->
                    <div>
                        <span class="pill" style="background:#edf2ff;color:#2152ff;font-weight:700;">
                            INTELIGENCIA B2B REAL
                        </span>

                        <h2 style="margin-top:20px;margin-bottom:20px;">
                            ¿Vendes a empresas?<br>
                            <span style="color:#2152ff;">Consigue clientes antes que nadie</span>
                        </h2>

                        <p style="margin-bottom:32px;">
                            Detecta nuevas empresas cada día, prioriza por scoring y accede a oportunidades comerciales
                            antes de que aparezcan en el mercado.
                        </p>

                        <!-- BENEFICIOS -->
                        <div class="radar-benefits">

                            <div class="radar-benefit">
                                <div class="radar-dot"></div>
                                <div>
                                    <strong>Nuevas empresas cada día</strong>
                                    <p>Accede a leads recién creados en España en tiempo real.</p>
                                </div>
                            </div>

                            <div class="radar-benefit">
                                <div class="radar-dot"></div>
                                <div>
                                    <strong>Prioriza con scoring</strong>
                                    <p>Enfócate solo en empresas con mayor probabilidad de compra.</p>
                                </div>
                            </div>

                            <div class="radar-benefit">
                                <div class="radar-dot"></div>
                                <div>
                                    <strong>Filtra como quieras</strong>
                                    <p>Sector, provincia, actividad o tipo de empresa.</p>
                                </div>
                            </div>

                            <div class="radar-benefit">
                                <div class="radar-dot"></div>
                                <div>
                                    <strong>Exporta y actúa</strong>
                                    <p>Descarga leads listos para tu CRM en segundos.</p>
                                </div>
                            </div>

                        </div>

                        <!-- CTA -->
                        <div class="radar-cta">
                            <a href="<?= site_url('leads-empresas-nuevas') ?>" class="btn">
                                Ver oportunidades ahora →
                            </a>

                            <span class="radar-secondary">
                                Sin scraping • Datos oficiales • Actualizado diariamente
                            </span>
                        </div>

                        <!-- METRICS -->
                        <div class="radar-metrics">
                            <div class="radar-metric">
                                +1200
                                <span>empresas nuevas / día</span>
                            </div>
                            <div class="radar-metric">
                                +4.5M
                                <span>empresas en base</span>
                            </div>
                            <div class="radar-metric">
                                100%
                                <span>datos oficiales</span>
                            </div>
                        </div>

                    </div>

                    <!-- VISUAL (dejas tu imagen igual) -->
                    <div>
                        <img src="<?= base_url('public/img/radar_showcase.png?v=' . time()) ?>" alt="Radar B2B"
                            style="width:100%;border-radius:20px;">
                    </div>

                </div>
            </div>
        </section>

        <!-- FAQS -->
        <section id="faqs" class="container" style="padding-top: 10px;">
            <div class="band">
                <span class="eyebrow">FAQs</span>
                <h2>Preguntas frecuentes sobre validar CIF y verificación de empresas</h2>
                <p class="muted" style="max-width: 920px;">
                    Resolvemos las dudas típicas antes de integrar la API: validación de CIF, verificación de empresa,
                    VIES, KYB/KYC y uso en producción.
                </p>

                <!-- Two-column layout: FAQs + Sidebar -->
                <div class="faq-grid-container">

                    <!-- Left: FAQs -->
                    <div class="faq-grid" style="display: grid; gap: 14px;">
                        <details class="faq-item">
                            <summary>¿Qué datos devuelve la API?</summary>
                            <div class="muted" style="margin-top: 10px;">
                                La API devuelve datos oficiales como razón social, CIF, estado de la empresa
                                (Activa/Inactiva), domicilio social, código CNAE y su descripción. En los planes
                                Pro/Business también incluye scoring comercial, señales societarias e insights
                                avanzados.
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>¿Puedo probarla gratis?</summary>
                            <div class="muted" style="margin-top: 10px;">
                                Sí, disponemos de un plan **Free (Sandbox)** con 100 consultas mensuales gratuitas para
                                que puedas realizar pruebas técnicas e integrar el sistema antes de pasar a producción.
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>¿La información es oficial?</summary>
                            <div class="muted" style="margin-top: 10px;">
                                Totalmente. Todos nuestros datos provienen de fuentes oficiales como el BOE, BORME, la
                                Agencia Tributaria (AEAT), el INE y el registro VIES de la Unión Europea.
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>¿Qué diferencia hay entre API y Radar?</summary>
                            <div class="muted" style="margin-top: 10px;">
                                La **API** está diseñada para automatizar validaciones dentro de tu propio software
                                (onboarding, facturación, riesgos). El **Radar** es una herramienta comercial para
                                descubrir nuevas empresas cada día y priorizar oportunidades de venta B2B de forma
                                sencilla.
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>¿Cuánto se tarda en integrar?</summary>
                            <div class="muted" style="margin-top: 10px;">
                                Minutos. Al ser una API REST estándar, puedes conectar el servicio con un simple comando
                                cURL o usando nuestras librerías en PHP, Node o Python. La documentación es clara y
                                directa.
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>¿Sirve para prospección B2B?</summary>
                            <div class="muted" style="margin-top: 10px;">
                                Sí, especialmente la herramienta Radar y los endpoints de Scoring. Permiten identificar
                                empresas de reciente creación, filtrar por provincia/sector y preparar el primer
                                contacto comercial con datos verificados.
                            </div>
                        </details>
                    </div>

                    <!-- Right: Sidebar -->
                    <aside style="position: sticky; top: 100px; display: grid; gap: 20px;">

                        <!-- Stats Card - High Impact Design -->
                        <div
                            style="background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2), inset 0 0 20px rgba(255,255,255,0.02); position: relative; overflow: hidden;">

                            <!-- Background Accent Icon -->
                            <div
                                style="position: absolute; right: -20px; bottom: -20px; opacity: 0.03; transform: rotate(-15deg); pointer-events: none;">
                                <svg width="180" height="180" viewBox="0 0 24 24" fill="none" stroke="white"
                                    stroke-width="1">
                                    <path
                                        d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z">
                                    </path>
                                </svg>
                            </div>

                            <div
                                style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; position: relative; z-index: 1;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <div
                                            style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 8px #10b981; animation: pulse-green 2s infinite;">
                                        </div>
                                        <h3
                                            style="font-size: 0.85rem; margin: 0; color: rgba(255,255,255,0.7); font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">
                                            PLATAFORMA</h3>
                                    </div>
                                </div>
                                <style>
                                    @keyframes pulse-green {
                                        0% {
                                            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
                                        }

                                        70% {
                                            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
                                        }

                                        100% {
                                            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
                                        }
                                    }
                                </style>
                            </div>

                            <div style="position: relative; z-index: 1;">
                                <div style="margin-bottom: 24px;">
                                    <div
                                        style="font-size: 3rem; font-weight: 900; background: linear-gradient(135deg, #fff 30%, #60A5FA 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent; line-height: 1; letter-spacing: -1px;">
                                        +3M</div>
                                    <div style="font-size: 0.9rem; color: #94a3b8; font-weight: 500; margin-top: 4px;">
                                        Empresas verificadas</div>
                                </div>

                                <div
                                    style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.08);">
                                    <div>
                                        <div
                                            style="font-size: 1.2rem; font-weight: 800; color: #fff; line-height: 1; margin-bottom: 4px;">
                                            99.9<span style="color: #60A5FA;">%</span></div>
                                        <div
                                            style="font-size: 0.7rem; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                                            Uptime</div>
                                    </div>
                                    <div>
                                        <div
                                            style="font-size: 1.2rem; font-weight: 800; color: #fff; line-height: 1; margin-bottom: 4px;">
                                            &lt;200<span style="color: #60A5FA;">ms</span></div>
                                        <div
                                            style="font-size: 0.7rem; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                                            Respuesta</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CTA Card - Premium Design -->
                        <div
                            style="background: linear-gradient(135deg, #2152ff 0%, #1a42cc 100%); border-radius: 20px; padding: 28px; position: relative; overflow: hidden; box-shadow: 0 8px 32px rgba(33, 82, 255, 0.3);">
                            <!-- Decorative gradient overlay -->
                            <div
                                style="position: absolute; top: -50%; right: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); pointer-events: none;">
                            </div>

                            <div style="position: relative; z-index: 1; text-align: center;">
                                <h3 style="font-size: 1.2rem; margin: 0 0 10px 0; color: #fff; font-weight: 700;">¿Listo
                                    para empezar?</h3>
                                <p
                                    style="font-size: 0.9rem; color: rgba(255,255,255,0.85); margin: 0 0 24px 0; line-height: 1.6;">
                                    Obtén tu API Key gratis y comienza a validar CIF en minutos.</p>

                                <a href="<?= site_url('register') ?>"
                                    style="display: block; background: #fff; color: #2152ff; text-align: center; padding: 14px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 0.95rem; transition: all 0.2s; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"
                                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.2)'"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'">
                                    Crear cuenta gratis →
                                </a>
                            </div>
                        </div>

                    </aside>

                </div>
            </div>
        </section>

        <!-- FAQ Schema for Rich Snippets -->
        <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "¿Cómo validar un CIF en España?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Validar un CIF puede incluir dos cosas: comprobar el formato y, además, verificar datos de empresa como razón social y estado. En APIEmpresas.es puedes hacerlo desde el buscador web o automatizarlo por API REST con tu API Key."
          }
        },
        {
          "@type": "Question",
          "name": "¿Qué diferencia hay entre validar CIF y verificar una empresa?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Validar CIF suele referirse a la comprobación de estructura y consistencia. Verificar empresa implica contrastar información relevante (p. ej., razón social, estado, domicilio básico) para reducir errores en altas y facturación."
          }
        },
        {
          "@type": "Question",
          "name": "¿Puedo comprobar un NIF-IVA intracomunitario (VIES)?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Sí. Puedes validar el NIF-IVA intracomunitario contra VIES y usar el resultado en procesos de onboarding, cumplimiento y validación fiscal en operaciones B2B."
          }
        },
        {
          "@type": "Question",
          "name": "¿Para qué sirve en KYB/KYC y prevención de fraude?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Para automatizar verificaciones, reducir altas con datos falsos o inconsistentes y mantener un rastro consultable del dato verificado. Es especialmente útil si vendes a empresas (B2B), trabajas con pagos, crédito o acceso a producto."
          }
        },
        {
          "@type": "Question",
          "name": "¿La API es adecuada para producción?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Sí. Tienes API estable, control de consumo y planes preparados para producción. Puedes empezar en Sandbox, validar tu integración y escalar a Pro/Business cuando lo necesites."
          }
        },
        {
          "@type": "Question",
          "name": "¿Qué datos devuelvo y qué obtengo en una consulta?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Una consulta devuelve una ficha normalizada en JSON con los campos esenciales para automatización (CIF, razón social, estado, CNAE y datos básicos). El objetivo es integrarlo rápido en formularios de alta, ERPs/CRMs y flujos de riesgo."
          }
        },
        {
          "@type": "Question",
          "name": "¿Cómo integro la API para validar CIF?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Creas una cuenta, obtienes tu API Key y llamas al endpoint REST. En la documentación tienes ejemplos listos en cURL, PHP/Laravel, Node y Python para copiar y pegar."
          }
        }
      ]
    }
    </script>

    </main>





    <!-- =========================
         MODAL · INTEGRACIÓN A MEDIDA (Marketing/WOW)
         ========================= -->
    <div class="modal-overlay" id="modalCustomIntegration" aria-hidden="true" data-prevent-overlay-close="true">
        <div class="modal modal-wow" role="dialog" aria-modal="true" aria-labelledby="customTitle" tabindex="-1"
            style="max-width: 860px; padding: 0; border: 1px solid rgba(0,0,0,0.08); background: #ffffff; border-radius: 28px; box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.15); overflow: hidden;">

            <!-- Full Width Header -->
            <div
                style="padding: 48px 56px 32px; text-align: center; border-bottom: 1px solid rgba(0,0,0,0.03); position: relative;">
                <div>
                    <div class="modal-kicker"
                        style="color: #12b48a; font-weight: 800; font-size: 13px; letter-spacing: 1.2px; text-transform: uppercase; margin-bottom: 10px;">
                        Servicio Exclusivo</div>
                    <h2 class="modal-title" id="customTitle"
                        style="font-size: 38px; line-height: 1.1; margin: 0; color: #0f172a; font-weight: 900; letter-spacing: -0.03em;">
                        Integración <span
                            style="background: linear-gradient(90deg, #2152ff, #12b48a); -webkit-background-clip: text; background-clip: text; color: transparent;">personalizada</span>
                    </h2>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1.2fr; min-height: 400px;">
                <!-- Left Column: Visual -->
                <div
                    style="background: linear-gradient(135deg, #f8faff 0%, #eef2ff 100%); position: relative; display: flex; align-items: center; justify-content: center; overflow: hidden; border-right: 1px solid rgba(0,0,0,0.03);">
                    <div
                        style="position: absolute; inset: 0; opacity: 0.4; background-image: radial-gradient(#2152ff 0.5px, transparent 0.5px); background-size: 20px 20px;">
                    </div>
                    <div
                        style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 320px; height: 320px; background: radial-gradient(circle, rgba(33, 82, 255, 0.08) 0%, transparent 70%);">
                    </div>
                    <img src="<?= base_url('public/images/custom-integration.png') ?>"
                        alt="Icono de integración empresarial"
                        style="position: relative; z-index: 2; transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);"
                        onmouseover="this.style.transform='scale(1.08) rotate(3deg)'"
                        onmouseout="this.style.transform='scale(1) rotate(0deg)'">
                </div>

                <!-- Right Column: Content -->
                <div
                    style="padding: 48px 56px; display: flex; flex-direction: column; justify-content: center; background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(20px); position: relative;">
                    <div class="modal-body" style="padding: 0; margin-bottom: 40px;">
                        <p
                            style="font-size: 17px; color: #475569; line-height: 1.7; margin-bottom: 24px; font-weight: 400;">
                            ¿Tu flujo de trabajo requiere algo único? Adaptamos nuestra tecnología para que se fusione
                            perfectamente con tu infraestructura actual.
                        </p>
                        <ul style="list-style: none; padding: 0; margin: 0; color: #334155; font-size: 15px;">
                            <li style="margin-bottom: 16px; display: flex; align-items: center; gap: 14px;">
                                <div
                                    style="flex-shrink: 0; width: 22px; height: 22px; background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #0369a1;">
                                    <span style="font-size: 12px; font-weight: bold;">✓</span>
                                </div>
                                <span style="font-weight: 500;">Validaciones por lotes y Webhooks reales</span>
                            </li>
                            <li style="margin-bottom: 16px; display: flex; align-items: center; gap: 14px;">
                                <div
                                    style="flex-shrink: 0; width: 22px; height: 22px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #15803d;">
                                    <span style="font-size: 12px; font-weight: bold;">✓</span>
                                </div>
                                <span style="font-weight: 500;">Conectores nativos para ERP/CRM (SAP, Dynamics)</span>
                            </li>
                            <li style="margin-bottom: 0; display: flex; align-items: center; gap: 14px;">
                                <div
                                    style="flex-shrink: 0; width: 22px; height: 22px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #c2410c;">
                                    <span style="font-size: 12px; font-weight: bold;">✓</span>
                                </div>
                                <span style="font-weight: 500;">Enriquecimiento de datos a medida (KYB avanzado)</span>
                            </li>
                        </ul>
                    </div>

                    <div class="modal-footer"
                        style="padding: 0; border: none; justify-content: flex-start; gap: 16px; display: flex; align-items: center; flex-wrap: wrap;">
                        <a href="<?= site_url() ?>contact?interest=custom_integration" class="btn btn_start"
                            style="text-decoration:none; padding: 18px 32px; font-size: 16px; background: var(--primary); border: none; box-shadow: 0 15px 30px rgba(19, 58, 130, 0.2); border-radius: 16px; font-weight: 700; color: #fff; transition: all 0.3s; display: inline-block; white-space: nowrap;">
                            Consultar con un experto
                        </a>
                        <button class="modal-btn" type="button" data-close-modal
                            style="background: transparent; border: 1.5px solid #e2e8f0; color: #64748b; padding: 16px 26px; border-radius: 16px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                            Ahora no, gracias
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?php if (isset($showReviewModal) && $showReviewModal): ?>
        <!-- =========================
         MODAL · REVIEW USUARIO
         ========================= -->
        <div class="modal-overlay active" id="modalUserReview" aria-hidden="false" data-prevent-overlay-close="true"
            style="z-index: 10000; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px);">
            <div class="modal" role="dialog" aria-modal="true" aria-labelledby="reviewTitle" tabindex="-1"
                style="max-width: 500px; padding: 0; background: #ffffff; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25); overflow: hidden; position: relative;">

                <button class="modal-close" type="button" aria-label="Cerrar modal" onclick="closeReviewModal()"
                    style="position: absolute; top: 16px; right: 16px; background: transparent; border: none; font-size: 28px; color: #94a3b8; cursor: pointer; transition: color 0.2s; line-height: 1;">
                    &times;
                </button>

                <div style="padding: 40px 32px 32px; text-align: center;">
                    <div
                        style="width: 56px; height: 56px; background: #f0fdf4; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #16a34a; margin: 0 auto 20px;">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                            stroke-linecap="round" stroke-linejoin="round">
                            <path
                                d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z">
                            </path>
                        </svg>
                    </div>
                    <h2 id="reviewTitle"
                        style="font-size: 24px; color: #0f172a; font-weight: 800; margin-bottom: 12px; letter-spacing: -0.02em;">
                        ¿Qué te parece APIEmpresas?</h2>
                    <p style="color: #64748b; font-size: 15px; line-height: 1.6; margin-bottom: 24px;">Hemos notado que
                        estás usando bastante nuestro buscador. Nos ayudaría mucho saber tu opinión para seguir mejorando.
                    </p>

                    <form id="reviewForm" onsubmit="submitReview(event)">
                        <!-- Stars -->
                        <div style="display: flex; justify-content: center; gap: 8px; margin-bottom: 24px;"
                            id="starRatingContainer">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <button type="button" class="star-btn" data-value="<?= $i ?>" onclick="setRating(<?= $i ?>)"
                                    onmouseover="hoverRating(<?= $i ?>)" onmouseout="resetRating()"
                                    style="background: transparent; border: none; cursor: pointer; padding: 0; color: #cbd5e1; transition: color 0.2s, transform 0.2s;">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor"
                                        stroke-width="1.5" stroke-linejoin="round">
                                        <path
                                            d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z">
                                        </path>
                                    </svg>
                                </button>
                            <?php endfor; ?>
                        </div>
                        <input type="hidden" name="rating" id="reviewRating" value="0" required>

                        <textarea name="comment" id="reviewComment"
                            placeholder="Comentarios, mejoras o funciones que eches en falta (opcional)..." rows="3"
                            style="width: 100%; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px; font-size: 14px; color: #1e293b; background: #f8fafc; resize: none; margin-bottom: 24px; font-family: inherit; transition: border-color 0.2s; box-sizing: border-box;"
                            onfocus="this.style.borderColor='#2152ff'" onblur="this.style.borderColor='#e2e8f0'"></textarea>

                        <button type="submit" id="reviewSubmitBtn" disabled
                            style="width: 100%; background: linear-gradient(135deg, #2152ff 0%, #1d4ed8 100%); color: white; border: none; font-weight: 700; padding: 16px; border-radius: 12px; font-size: 1.05rem; cursor: not-allowed; opacity: 0.6; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 14px rgba(33, 82, 255, 0.4);">
                            Enviar valoración
                        </button>
                    </form>

                    <div id="reviewSuccess" style="display: none; padding: 20px 0;">
                        <div style="color: #16a34a; font-size: 48px; margin-bottom: 16px;">✓</div>
                        <h3 style="font-size: 20px; color: #0f172a; margin-bottom: 8px;">¡Gracias por tu reseña!</h3>
                        <p style="color: #64748b; font-size: 15px;">Tu feedback es muy valioso para nosotros.</p>
                    </div>
                </div>
            </div>
        </div>

        <script>
            let currentRating = 0;

            function setRating(rating) {
                currentRating = rating;
                document.getElementById('reviewRating').value = rating;
                updateStars(rating);

                const submitBtn = document.getElementById('reviewSubmitBtn');
                submitBtn.disabled = false;
                submitBtn.style.cursor = 'pointer';
                submitBtn.style.opacity = '1';
                submitBtn.style.transform = 'translateY(-2px)';
                submitBtn.style.boxShadow = '0 10px 20px rgba(33, 82, 255, 0.3)';
            }

            function hoverRating(rating) {
                updateStars(rating);
            }

            function resetRating() {
                updateStars(currentRating);
            }

            function updateStars(activeCount) {
                const stars = document.querySelectorAll('#starRatingContainer .star-btn');
                stars.forEach((star, index) => {
                    if (index < activeCount) {
                        star.style.color = '#fbbf24'; // yellow
                        star.style.transform = 'scale(1.1)';
                    } else {
                        star.style.color = '#cbd5e1'; // gray
                        star.style.transform = 'scale(1)';
                    }
                });
            }

            function closeReviewModal() {
                const modal = document.getElementById('modalUserReview');
                modal.classList.remove('active');
                setTimeout(() => modal.style.display = 'none', 300);
                document.body.style.overflow = '';
            }

            function submitReview(e) {
                e.preventDefault();
                if (currentRating < 1) return;

                const btn = document.getElementById('reviewSubmitBtn');
                const form = document.getElementById('reviewForm');
                const success = document.getElementById('reviewSuccess');

                btn.innerHTML = 'Enviando...';
                btn.disabled = true;

                const formData = new FormData();
                formData.append('rating', currentRating);
                formData.append('comment', document.getElementById('reviewComment').value);

                fetch('<?= site_url('submit-review') ?>', {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            form.style.display = 'none';
                            success.style.display = 'block';
                            setTimeout(() => {
                                closeReviewModal();
                            }, 3000);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        btn.innerHTML = 'Error al enviar. Reintentar';
                        btn.disabled = false;
                    });
            }
            document.body.style.overflow = 'hidden';
        </script>
    <?php endif; ?>

    <?= view('partials/footer') ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const grid = document.getElementById('home-blog-grid');
            if (!grid) return;

            // Only fetch if no posts were rendered server-side (empty or skeleton)
            if (grid.querySelectorAll('.home-blog__card:not(.home-blog__card--skeleton)').length > 0) {
                return;
            }

            fetch('<?= site_url() ?>blog/get_posts', {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
                .then(function (response) {
                    if (!response.ok) throw new Error('HTTP ' + response.status);
                    return response.json();
                })
                .then(function (data) {
                    if (data && data.ok && data.html) {
                        grid.innerHTML = data.html;
                    } else {
                        // Si algo falla, dejamos el skeleton o podríamos ocultar la sección
                        console.warn('No se pudieron cargar los posts del blog.');
                    }
                })
                .catch(function (err) {
                    console.error('Error al cargar posts:', err);
                });
            // Custom Integration Modal Trigger
            (function () {
                const MODAL_ID = 'modalCustomIntegration';
                const STORAGE_KEY = 've_custom_integration_modal_shown';
                const DELAY_MS = 8000; // 8 segundos
                const FREQUENCY_DAYS = 7;

                function shouldShow() {
                    const lastShown = localStorage.getItem(STORAGE_KEY);
                    if (!lastShown) return true;

                    const now = new Date().getTime();
                    const diffDays = (now - parseInt(lastShown)) / (1000 * 60 * 60 * 24);
                    return diffDays > FREQUENCY_DAYS;
                }

                const overlay = document.getElementById(MODAL_ID);
                if (!overlay || !shouldShow()) return;

                setTimeout(() => {
                    // Reutilizamos la función global openModal si está disponible, 
                    // o disparamos un click en un elemento dummy si fuera necesario.
                    // Sin embargo, como scripts.php define openModal dentro de un IIFE,
                    // la forma más limpia es replicar la lógica o exponerla.
                    // Dado el diseño de scripts.php, usaremos un trigger programático:

                    overlay.classList.add('active');
                    overlay.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';

                    const dialog = overlay.querySelector('.modal');
                    if (dialog) dialog.focus({ preventScroll: true });

                    // Guardar persistencia
                    localStorage.setItem(STORAGE_KEY, new Date().getTime().toString());
                }, DELAY_MS);
            })();
        });
    </script>
    <!-- INFO MODAL -->
    <div id="veModalHelp" class="ve-modal-overlay" onclick="if(event.target === this) closeInfo()">
        <div class="ve-modal-content">
            <button class="ve-modal-close" onclick="closeInfo()">×</button>
            <div id="veModalBody" class="ve-modal-body">
                <!-- Dynamic Content -->
            </div>
            <div style="margin-top: 24px; text-align: right;">
                <button class="btn primary" onclick="closeInfo()">Entendido</button>
            </div>
        </div>
    </div>

    <script>
        const endpointData = {
            'companies': {
                icon: '🏢',
                title: 'Validación de Empresas (Datos base)',
                text: 'Obtén la información oficial de cualquier empresa española en tiempo real. Incluye razón social, CIF, domicilio social registrado, código CNAE y estado de actividad (Activa, Disuelta, etc.).<br><br><strong>Origen de datos:</strong> Conexión directa con AEAT y Registro Mercantil para máxima fiabilidad en tus procesos de facturación o alta de clientes.'
            },
            'score': {
                icon: '📊',
                title: 'Inteligencia de Prioridad Comercial',
                text: 'Nuestro algoritmo propietario analiza múltiples señales para predecir el interés comercial de una empresa. Asigna una puntuación del 0 al 100 y una prioridad de contacto.<br><br><strong>Valor añadido:</strong> Optimiza el tiempo de tu equipo de ventas enfocándolos en las empresas que tienen mayor probabilidad de contratar tus servicios ahora mismo.'
            },
            'signals': {
                icon: '🔔',
                title: 'Alertas y Señales de Negocio',
                text: 'Monitorizamos diariamente el BORME para informarte sobre cambios estructurales en empresas: ampliaciones de capital, cambios de administradores, nombramientos o traslados de domicilio.<br><br><strong>Utilidad:</strong> Detecta oportunidades de "venta cruzada" o riesgos de negocio antes que tu competencia.'
            },
            'radar': {
                icon: '📡',
                title: 'Radar de Nuevas Empresas',
                text: 'Accede programáticamente al listado de todas las empresas constituidas en las últimas 24 horas en España.<br><br><strong>Detalle:</strong> Puedes filtrar por provincia y sector. Es la herramienta definitiva para ser el primero en ofrecer tus servicios a nuevos proyectos recién creados.'
            },
            'insights': {
                icon: '🧠',
                title: 'Análisis Estratégico por IA',
                text: 'Utilizamos modelos de IA avanzados para "leer" el perfil de la empresa y generar un resumen ejecutivo de su actividad y necesidades probables.<br><br><strong>Resultado:</strong> Te entregamos una visión clara de por qué esa empresa es un buen cliente para ti y qué problemas podrías resolverles.'
            },
            'contact-prep': {
                icon: '💬',
                title: 'Preparación Comercial Inteligente',
                text: 'La IA genera automáticamente un pitch de ventas personalizado para el lead seleccionado.<br><br><strong>Contenido:</strong> Incluye un guion de apertura persuasivo y una lista de preguntas para manejar las objeciones más comunes en su sector específico.'
            },
            'webhooks': {
                icon: '⚡',
                title: 'Sincronización vía Webhooks',
                text: 'Recibe notificaciones automáticas (PUSH) en tu servidor cada vez que detectemos una nueva empresa que cumpla tus criterios o una señal relevante.<br><br><strong>Automatización:</strong> Elimina la necesidad de consultar la API manualmente. Tu sistema se mantiene actualizado en tiempo real.'
            }
        };

        function openInfo(e, slug) {
            if (e) {
                e.preventDefault();
                e.stopPropagation();
            }

            const data = endpointData[slug];
            if (!data) return;

            const body = document.getElementById('veModalBody');
            body.innerHTML = `
                <h4><span class="ve-modal-icon">${data.icon}</span> ${data.title}</h4>
                <p>${data.text}</p>
            `;

            const modal = document.getElementById('veModalHelp');
            modal.classList.add('active');
        }

        function closeInfo() {
            const modal = document.getElementById('veModalHelp');
            modal.classList.remove('active');
        }

        // Tabs functionality for search results
        document.addEventListener('click', function (e) {
            if (e.target.classList.contains('tab-btn')) {
                const tabId = e.target.getAttribute('data-tab');
                const container = e.target.closest('#resultado_container') || e.target.closest('.search-card');
                if (!container) return;

                container.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
                container.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

                e.target.classList.add('active');
                const targetTab = container.querySelector('#tab-' + tabId);
                if (targetTab) targetTab.classList.add('active');
            }
        });
    </script>
</body>

</html>