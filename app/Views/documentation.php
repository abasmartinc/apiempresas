<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/public' ) ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/css/docs.css?v=' . time()) ?>" />
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container docs-main">
        <div class="docs-layout">
            <!-- SIDEBAR -->
            <aside class="docs-sidebar">
                <div class="docs-sidebar-section">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                        <h3 style="margin: 0;">General</h3>
                        <button id="theme-toggle" class="theme-toggle-btn" title="Alternar modo oscuro">
                            <svg class="moon-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                            <svg class="sun-icon" style="display:none;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                        </button>
                    </div>

                    <!-- Selector de idioma / Language switcher -->
                    <div style="display: flex; gap: 8px; margin-bottom: 16px;">
                        <a href="<?= site_url('documentation') ?>" style="flex:1; text-align:center; padding: 6px 10px; border-radius: 8px; border: 2px solid #2563eb; background: #eff6ff; font-size: 0.8rem; font-weight: 800; color: #2563eb; text-decoration: none;" title="Versión en español">🇪🇸 ES</a>
                        <a href="https://spaincompanyapi.com/docs" style="flex:1; text-align:center; padding: 6px 10px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.8rem; font-weight: 600; color: #64748b; text-decoration: none; transition: all 0.2s;" title="English version">🇬🇧 EN</a>
                    </div>

                    <ul class="docs-nav">
                        <li>
                            <a href="#intro" class="active">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                <span class="nav-num">1.</span> Introducción
                            </a>
                        </li>
                        <li>
                            <a href="#auth">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <span class="nav-num">2.</span> Autenticación
                            </a>
                        </li>
                        <li>
                            <a href="#consumo">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                <span class="nav-num">2.1</span> Planes y consumo
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('api/referencia') ?>" target="_blank">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                Referencia interactiva
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="docs-sidebar-section">
                    <h3>Endpoints v1</h3>
                    <ul class="docs-nav">
                        <li>
                            <a href="#endpoint-by-cif">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"></path></svg>
                                <span class="nav-num">3.</span> Consulta por CIF
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-verify">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline></svg>
                                <span class="nav-num">3.1</span> Verificación KYB <span class="sidebar-badge pro">Pro</span>
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-reconcile">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="17 1 21 5 17 9"></polyline><path d="M3 11V9a4 4 0 0 1 4-4h14"></path><polyline points="7 23 3 19 7 15"></polyline><path d="M21 13v2a4 4 0 0 1-4 4H3"></path></svg>
                                <span class="nav-num">3.2</span> Nombre a CIF <span class="sidebar-badge pro">Pro</span>
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-search">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                <span class="nav-num">4.</span> Búsqueda
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-expanded">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                <span class="nav-num">5.</span> Comercial <span class="sidebar-badge pro">Pro</span>
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-borme">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                <span class="nav-num">6.</span> Historial BORME <span class="sidebar-badge pro">Pro</span>
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-radar">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                <span class="nav-num">7.</span> Radar <span class="sidebar-badge pro">Pro</span>
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-watchlist">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                <span class="nav-num">7.1</span> Vigilancia <span class="sidebar-badge pro">Pro</span>
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-filter">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                                <span class="nav-num">7.2</span> Segmentos <span class="sidebar-badge biz">Biz</span>
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-webhooks">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11a9 9 0 0 1 9 9"></path><path d="M4 4a16 16 0 0 1 16 16"></path><circle cx="5" cy="19" r="1"></circle></svg>
                                <span class="nav-num">8.</span> Webhooks <span class="sidebar-badge biz">Biz</span>
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-contracts">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                <span class="nav-num">9.</span> Contratos Públicos <span class="sidebar-badge biz">Biz</span>
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-risk-profile">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                <span class="nav-num">10.</span> Perfil de Riesgo <span class="sidebar-badge biz">Biz</span>
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-usage">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="18" y="3" width="4" height="18"></rect><rect x="10" y="8" width="4" height="13"></rect><rect x="2" y="13" width="4" height="8"></rect></svg>
                                <span class="nav-num">11.</span> Consumo
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-catalog">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                                <span class="nav-num">11.1</span> Catálogo de valores
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="docs-sidebar-section">
                    <h3>Recursos</h3>
                    <ul class="docs-nav">
                        <li>
                            <a href="#errores">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                <span class="nav-num">12.</span> Gestión de Errores
                            </a>
                        </li>
                        <li>
                            <a href="#throttling">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                <span class="nav-num">13.</span> Rate Limiting
                            </a>
                        </li>
                        <li>
                            <a href="#paginacion">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                <span class="nav-num">14.</span> Paginación
                            </a>
                        </li>
                        <li>
                            <a href="#sdks">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11a9 9 0 0 1 9 9"></path><path d="M4 4a16 16 0 0 1 16 16"></path><circle cx="5" cy="19" r="1"></circle></svg>
                                <span class="nav-num">15.</span> SDKs Oficiales
                            </a>
                        </li>
                        <li>
                            <a href="#examples">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                <span class="nav-num">16.</span> Ejemplos
                            </a>
                        </li>
                        <li>
                            <a href="#postman">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                <span class="nav-num">17.</span> Postman
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>

            <!-- CONTENIDO -->
            <div class="docs-content">
                <h1>Documentación de la API</h1>
                <p class="docs-intro-lead" style="font-size: 1.1rem; color: #334155; line-height: 1.7; margin-bottom: 24px;">
                    Bienvenido a la documentación oficial de <strong>APIEmpresas.es</strong>. Nuestra API RESTful está diseñada para equipos de desarrollo que necesitan integrar inteligencia mercantil española (datos, BORME, contactos, scoring y vínculos) en sus propios sistemas de forma rápida y sin fricción.
                </p>
                <div class="docs-intro-highlights" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin: 0 0 40px 0;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.01);">
                        <div style="font-weight: 800; color: #0f172a; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                            Arquitectura RESTful
                        </div>
                        <div style="font-size: 0.9rem; color: #475569; line-height: 1.5;">Respuestas siempre en formato JSON estándar con URLs predecibles orientadas a recursos.</div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.01);">
                        <div style="font-weight: 800; color: #0f172a; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            Alta Disponibilidad
                        </div>
                        <div style="font-size: 0.9rem; color: #475569; line-height: 1.5;">Protección nativa con Rate Limiting Inteligente y paginación ultra rápida por cursores.</div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.01);">
                        <div style="font-weight: 800; color: #0f172a; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            Errores RFC 7807
                        </div>
                        <div style="font-size: 0.9rem; color: #475569; line-height: 1.5;">Implementamos el estándar Problem Details para un tipado de errores estricto y predecible.</div>
                    </div>
                </div>

                <!-- API PLAYGROUND -->
                <style>
                    /* Enlace a la referencia: botón visible (13-10-2026) */
                    .api-playground > div:first-child { flex-wrap: wrap; gap: 14px; }
                    .ae-ref-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 18px; border-radius: 10px;
                        background: #2563eb; color: #fff !important; font-size: 0.9rem; font-weight: 700; text-decoration: none !important;
                        box-shadow: 0 6px 16px -4px rgba(37, 99, 235, .45); transition: background .15s, transform .15s; white-space: nowrap; }
                    .ae-ref-btn:hover { background: #1d4ed8; transform: translateY(-1px); }
                    .ae-ref-btn svg { width: 16px; height: 16px; }
                </style>
                <div class="api-playground" style="margin: 40px 0; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); background: #ffffff;">
                    <div style="padding: 20px 24px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h3 style="margin: 0; font-size: 1.1rem; color: #0f172a; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                Probar API Interactivamente
                            </h3>
                            <p style="margin: 4px 0 0 0; font-size: 0.9rem; color: #64748b;">Lanza peticiones en tiempo real al entorno Sandbox. No requiere API Key.</p>
                        </div>
                        <a href="<?= site_url('api/referencia') ?>" class="ae-ref-btn">
                            Abrir la referencia completa <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </a>
                    </div>

                    <div style="display: flex; flex-wrap: wrap; background: #fff;">
                        <!-- Left Panel: Form -->
                        <div style="flex: 1; min-width: 300px; padding: 24px; border-right: 1px solid #e2e8f0;">
                            <div style="margin-bottom: 20px;">
                                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">1. Selecciona el Endpoint</label>
                                <select id="pg-endpoint" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: #0f172a; background: #f8fafc; cursor: pointer; outline: none; transition: border-color 0.2s;">
                                    <option value="/api/sandbox/v1/companies">GET /companies (Consulta CIF)</option>
                                    <option value="/api/sandbox/v1/companies/search">GET /companies/search (Buscador)</option>
                                    <option value="/api/sandbox/v1/companies/score">GET /companies/score (Score Comercial)</option>
                                    <option value="/api/sandbox/v1/companies/signals">GET /companies/signals (Señales de Cambio)</option>
                                    <option value="/api/sandbox/v1/companies/insights">GET /companies/insights (Análisis IA)</option>
                                    <option value="/api/sandbox/v1/companies/contact-prep">GET /companies/contact-prep (Prep. Contacto)</option>
                                    <option value="/api/sandbox/v1/companies/match">GET /companies/match (Match B2B)</option>
                                    <option value="/api/sandbox/v1/companies/network">GET /companies/network (Red Vínculos)</option>
                                    <option value="/api/sandbox/v1/companies/borme">GET /companies/borme (Historial BORME)</option>
                                    <option value="/api/sandbox/v1/companies/radar">GET /companies/radar (Radar Pro)</option>
                                    <option value="/api/sandbox/v1/companies/risk-profile">GET /companies/risk-profile (Perfil de Riesgo)</option>
                                </select>
                            </div>

                            <div style="margin-bottom: 24px;">
                                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">2. Parámetros</label>
                                <div style="display: flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #cbd5e1; padding: 4px 4px 4px 12px; border-radius: 8px; transition: border-color 0.2s;" id="pg-param-container">
                                    <span id="pg-param-label" style="color: #64748b; font-weight: 600; font-family: monospace;">?cif=</span>
                                    <input type="text" id="pg-cif" value="A15075062" placeholder="Ej: A15075062" style="flex: 1; border: none; padding: 8px 0; outline: none; font-size: 0.95rem; color: #0f172a; font-family: monospace;">
                                </div>
                                <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 8px;">Usa <code style="background: #f1f5f9; padding: 2px 4px; border-radius: 4px; color: #e11d48; font-family: monospace;">A15075062</code> para probar el Sandbox.</p>
                            </div>

                            <button id="pg-run" style="width: 100%; background: #2563eb; color: #fff; border: none; padding: 14px; border-radius: 8px; font-weight: 800; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s; box-shadow: 0 4px 12px rgba(37,99,235,0.2);">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                                Lanzar Petición
                            </button>
                        </div>

                        <!-- Right Panel: Code Editor -->
                        <div style="flex: 1.5; min-width: 350px; background: #0f172a; position: relative; display: flex; flex-direction: column;">
                            <div style="background: #1e293b; padding: 8px 16px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #334155;">
                                <div style="display: flex; gap: 6px;">
                                    <div style="width: 10px; height: 10px; border-radius: 50%; background: #ef4444;"></div>
                                    <div style="width: 10px; height: 10px; border-radius: 50%; background: #eab308;"></div>
                                    <div style="width: 10px; height: 10px; border-radius: 50%; background: #22c55e;"></div>
                                </div>
                                <span id="pg-status" style="font-family: monospace; font-size: 0.8rem; color: #10b981; font-weight: bold; background: rgba(16,185,129,0.1); padding: 2px 8px; border-radius: 4px; display: none;">200 OK</span>
                            </div>
                            <pre style="margin: 0; padding: 24px; flex: 1; overflow-y: auto; max-height: 400px; font-family: 'Fira Code', monospace; font-size: 0.85rem; color: #e2e8f0; background: transparent;"><code id="pg-response" class="language-json">// Pulsa "Lanzar Petición" para ver la respuesta JSON en vivo.
// Sandbox configurado por defecto para el CIF mágico: A15075062.</code></pre>
                            
                            <div id="pg-loader" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15,23,42,0.8); display: none; align-items: center; justify-content: center; backdrop-filter: blur(2px);">
                                <svg style="animation: spin 1s linear infinite; color: #3b82f6;" width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="2" x2="12" y2="6"></line><line x1="12" y1="18" x2="12" y2="22"></line><line x1="4.93" y1="4.93" x2="7.76" y2="7.76"></line><line x1="16.24" y1="16.24" x2="19.07" y2="19.07"></line><line x1="2" y1="12" x2="6" y2="12"></line><line x1="18" y1="12" x2="22" y2="12"></line><line x1="4.93" y1="19.07" x2="7.76" y2="16.24"></line><line x1="16.24" y1="7.76" x2="19.07" y2="4.93"></line></svg>
                            </div>
                        </div>
                    </div>
                </div>

                <style>
                @keyframes spin { 100% { transform: rotate(360deg); } }
                .api-playground #pg-run:hover { background: #1d4ed8; }
                </style>

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const btnRun = document.getElementById('pg-run');
                    const endpointSelect = document.getElementById('pg-endpoint');
                    const inputCif = document.getElementById('pg-cif');
                    const responseBlock = document.getElementById('pg-response');
                    const statusBadge = document.getElementById('pg-status');
                    const loader = document.getElementById('pg-loader');
                    const paramLabel = document.getElementById('pg-param-label');

                    endpointSelect.addEventListener('change', function() {
                        if (this.value.includes('search')) {
                            paramLabel.textContent = '?q=';
                            inputCif.placeholder = 'Ej: Inditex';
                            if(inputCif.value === 'A15075062') inputCif.value = 'Inditex';
                        } else {
                            paramLabel.textContent = '?cif=';
                            inputCif.placeholder = 'Ej: A15075062';
                            if(inputCif.value === 'Inditex') inputCif.value = 'A15075062';
                        }
                    });

                    btnRun.addEventListener('click', async function() {
                        const baseUrl = '<?= rtrim(site_url(), "/") ?>';
                        let endpoint = endpointSelect.value;
                        let val = inputCif.value;
                        let paramName = endpoint.includes('search') ? 'q' : 'cif';
                        
                        const url = `${baseUrl}${endpoint}?${paramName}=${encodeURIComponent(val)}`;
                        
                        loader.style.display = 'flex';
                        statusBadge.style.display = 'none';
                        
                        try {
                            const start = performance.now();
                            const res = await fetch(url, {
                                headers: {
                                    'Accept': 'application/json'
                                }
                            });
                            const end = performance.now();
                            const ms = Math.round(end - start);
                            
                            const data = await res.json();
                            
                            statusBadge.textContent = `${res.status} ${res.statusText} - ${ms}ms`;
                            statusBadge.style.display = 'block';
                            
                            if (res.ok) {
                                statusBadge.style.color = '#10b981';
                                statusBadge.style.background = 'rgba(16,185,129,0.1)';
                            } else {
                                statusBadge.style.color = '#ef4444';
                                statusBadge.style.background = 'rgba(239,68,68,0.1)';
                            }
                            
                            responseBlock.textContent = JSON.stringify(data, null, 2);
                            
                            if (window.Prism) {
                                Prism.highlightElement(responseBlock);
                            }
                        } catch (e) {
                            responseBlock.textContent = `Error: ${e.message}`;
                            statusBadge.textContent = 'Network Error';
                            statusBadge.style.display = 'block';
                            statusBadge.style.color = '#ef4444';
                            statusBadge.style.background = 'rgba(239,68,68,0.1)';
                        } finally {
                            loader.style.display = 'none';
                        }
                    });
                });
                </script>



                <!-- INTRO -->
                <section class="docs-section" id="intro">
                    <h2>1. Introducción</h2>
                    <p>
                        La API está diseñada siguiendo principios REST. Todas las respuestas se devuelven en formato JSON y requieren una conexión segura vía HTTPS.
                    </p>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; margin: 24px 0;">
                        <div style="background: #1e293b; border: 1px solid #334155; padding: 20px; border-radius: 12px; position: relative;">
                            <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></div> Entorno de Producción
                            </div>
                            <code style="color: #e2e8f0; font-family: 'Fira Code', monospace; font-size: 0.95rem; background: transparent; padding: 0;">https://apiempresas.es/api/v1</code>
                        </div>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; position: relative;">
                            <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b;"></div> Entorno Sandbox (Gratis)
                            </div>
                            <code style="color: #0f172a; font-family: 'Fira Code', monospace; font-size: 0.95rem; background: transparent; padding: 0;">https://apiempresas.es/api/sandbox/v1</code>
                        </div>
                    </div>

                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-top: 32px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);">
                        <div style="background: #f8fafc; padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            <h4 style="margin: 0; color: #0f172a; font-weight: 800; font-size: 1.05rem;">Guía del Sandbox (Entorno de Pruebas)</h4>
                        </div>
                        <div style="padding: 24px;">
                            <p style="margin: 0 0 24px 0; font-size: 0.95rem; color: #475569; line-height: 1.6;">
                                Para hacer pruebas <strong>sin consumir saldo ni cuota</strong>, envía tus peticiones a la URL de Sandbox usando tu API Key habitual. Hemos habilitado varios "CIFs mágicos" que te permitirán simular diferentes flujos en tu aplicación:
                            </p>
                            
                            <h5 style="margin: 0 0 16px 0; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">🎯 CIFs Mágicos Permitidos</h5>
                            <ul style="margin: 0 0 32px 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 12px;">
                                <li style="display: flex; gap: 16px; align-items: flex-start;">
                                    <code style="background: #f1f5f9; color: #0f172a; padding: 6px 10px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1; font-weight: 700; min-width: 90px; text-align: center;">A15075062</code>
                                    <span style="color: #334155; font-size: 0.95rem; line-height: 1.5; padding-top: 4px;">Simula una consulta exitosa: devuelve una ficha de ejemplo de Inditex (datos fijos).</span>
                                </li>
                                <li style="display: flex; gap: 16px; align-items: flex-start;">
                                    <code style="background: #fef2f2; color: #dc2626; padding: 6px 10px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #fecaca; font-weight: 700; min-width: 90px; text-align: center;">B00000000</code>
                                    <span style="color: #334155; font-size: 0.95rem; line-height: 1.5; padding-top: 4px;">Simula un error HTTP 404 estandarizado de "empresa no encontrada".</span>
                                </li>
                            </ul>

                            <h5 style="margin: 0 0 16px 0; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">⚡ Simulación de Endpoints Complejos</h5>
                            <ul style="margin: 0; padding-left: 20px; color: #334155; font-size: 0.95rem; line-height: 1.7; display: flex; flex-direction: column; gap: 10px;">
                                <li><strong>Búsqueda (/search):</strong> Envía cualquier texto en <code>q=</code>. Siempre devuelve datos de Inditex para validar el parseo de listas en tu frontend.</li>
                                <li><strong>Por Lotes (/batch):</strong> Envía un JSON con CIFs mágicos (ej. <code>{"cifs": ["A15075062", "B00000000"]}</code>) para probar cómo tu código maneja respuestas parciales.</li>
                                <li><strong>Endpoints Premium (/score, /insights):</strong> Llama a cualquiera usando <code>A15075062</code> para recibir su estructura de datos simulada y validar la integración.</li>
                            </ul>
                        </div>
                    </div>

                    <div id="cambios-datos" style="margin: 24px 0 0; padding: 16px 20px; border-radius: 12px; background: #fffbeb; border: 1px solid #fde68a; color: #78350f; line-height: 1.6;">
                        <strong>Cambios en los datos (1 de octubre de 2026).</strong> No cambia ningún campo, solo el valor que llevan en estos casos:
                        <ul style="margin: 8px 0 0; padding-left: 20px;">
                            <li><code>status_code</code>: una ficha sin ningún dato del Registro (por ejemplo, una UTE) devuelve <code>UNKNOWN</code> en lugar de <code>PRESUMED_ACTIVE</code>. Que el BORME no tenga hechos de una entidad que no está inscrita no dice nada de su estado.</li>
                            <li><code>upsell_opportunities.datos_pro.administradores</code> (plan Free) cuenta las personas con un cargo de administración vigente: administradores, consejeros, presidente y vicepresidente (no apoderados, auditores ni secretarios no consejeros). Antes contaba a todos los que habían pasado por la empresa, aunque hubieran cesado.</li>
                            <li><code>/companies/search</code> (sin <code>multiple</code>): la mejor coincidencia da prioridad a las sociedades inscritas en el Registro frente a UTE y fichas sin datos.</li>
                        </ul>
                    </div>
                </section>

                <!-- AUTH -->
                <section class="docs-section" id="auth">
                    <h2>2. Autenticación</h2>
                    <p>
                        Para acceder a los endpoints debes incluir tu <strong>X-API-KEY</strong> en la cabecera de la petición. Puedes generar y copiar tu clave desde tu <a href="<?= site_url('dashboard') ?>">panel de control</a>.
                    </p>
                    <pre><code class="language-http">GET /api/v1/companies?cif=A15075062 HTTP/1.1
Host: apiempresas.es
X-API-KEY: tu_api_key_aqui
Accept: application/json</code></pre>
                    <p>También se acepta la cabecera <code>Authorization: Bearer tu_api_key_aqui</code>.</p>

                    <h4>Entorno de pruebas (Sandbox)</h4>
                    <p>Para probar tu integración sin gastar consultas, usa la misma API Key con la base <code>https://apiempresas.es/api/sandbox/v1</code> (mismas rutas que producción: <code>/companies</code>, <code>/companies/search</code>, <code>/companies/score</code>…). El Sandbox no gasta consultas y solo admite estos CIF (todas las respuestas son de ejemplo, con datos fijos y la misma estructura que producción):</p>
                    <ul>
                        <li><code>A15075062</code>: empresa encontrada (Inditex).</li>
                        <li><code>B00000000</code>: empresa no encontrada (404).</li>
                    </ul>
                    <p>Cualquier otro CIF devuelve un error. El Sandbox responde siempre con los datos completos, sin los recortes del plan Free ni los límites de cupo: sirve para comprobar el formato de las respuestas, no lo que verás con tu plan.</p>
                    <p>Cada respuesta correcta del Sandbox añade un bloque <code>sandbox</code>: <code>plan_required</code> (plan mínimo del endpoint), <code>production_url</code> (la misma petición en producción, lista para copiar), <code>cost</code> (siempre 0) y, en <code>/companies</code>, <code>/companies/search</code> y <code>/companies/batch</code>, <code>fields_by_plan</code> con los campos que solo llegan completos con Pro.</p>
                    <p>Están en el Sandbox todas las rutas de <code>/companies</code>, también <code>/companies/verify</code>, <code>/companies/contracts</code>, <code>/companies/filter</code> y <code>POST /companies/reconcile</code> (<code>"Inditex"</code> da <code>match</code>, un nombre con <code>"Ejemplo"</code> da <code>ambiguous</code> y el resto <code>no_match</code>), y la vigilancia (<code>/watchlist</code> y <code>/watchlist/events</code>, sin guardar nada: responde como si ya vigilaras A15075062). No están <code>/webhooks</code> ni <code>/usage</code>.</p>
                </section>

                <!-- CONSUMO (13-10-2026) -->
                <section class="docs-section" id="consumo">
                    <h2>2.1 Planes, consumo y créditos</h2>
                    <table class="docs-table">
                        <thead><tr><th>Plan</th><th>Consultas</th><th>Velocidad</th></tr></thead>
                        <tbody>
                            <tr><td>Free</td><td>100 en total (no se renuevan)</td><td>2 por segundo</td></tr>
                            <tr><td>Pro</td><td>3.000 al mes</td><td>20 por segundo</td></tr>
                            <tr><td>Business</td><td>10.000 al mes</td><td>20 por segundo</td></tr>
                        </tbody>
                    </table>
                    <p>Cada ruta indica el plan que necesita. Con un plan inferior responde 403 con el plan necesario en <code>required_plan</code> y el enlace para cambiar en <code>upsell_opportunities.checkout_url</code>.</p>

                    <h4>Qué gasta una consulta</h4>
                    <ul>
                        <li>Solo se cobran las respuestas 200. Los errores no gastan nada.</li>
                        <li>Mientras te queden consultas en tu plan, cada respuesta 200 gasta 1, sea cual sea la ruta. Excepciones: <code>/companies/verify</code> gasta 2; <code>/companies/batch</code> y <code>POST /companies/reconcile</code>, 1 por cada empresa encontrada; las filas de <code>/companies/filter</code>, 5 por fila.</li>
                        <li>No gastan nada: el Sandbox, <code>/usage</code>, <code>/catalog</code>, <code>/watchlist</code>, <code>/webhooks</code>, el recuento de <code>/companies/filter</code> (<code>count_only=true</code>) y las vistas previas (<code>/companies/score</code> en Free e <code>/companies/insights</code> en Pro).</li>
                    </ul>

                    <h4>Créditos del bono</h4>
                    <p>Cuando se acaban las consultas de tu plan, o si usas un <a href="<?= site_url('crear-bono-api') ?>">bono sin suscripción</a>, cada respuesta 200 se paga con créditos del monedero. No todas las rutas cuestan lo mismo:</p>
                    <table class="docs-table">
                        <thead><tr><th>Créditos</th><th>Rutas</th></tr></thead>
                        <tbody>
                            <tr><td>1</td><td><code>/companies</code> y <code>/companies/search</code>; <code>/companies/batch</code> y <code>POST /companies/reconcile</code>, 1 por cada empresa encontrada</td></tr>
                            <tr><td>2</td><td><code>/companies/verify</code></td></tr>
                            <tr><td>3</td><td><code>/companies/score</code>, <code>/signals</code>, <code>/insights</code>, <code>/contact-prep</code>, <code>/network</code>, <code>/match</code>, <code>/radar</code>, <code>/borme</code>, <code>/contracts</code> y <code>/risk-profile</code></td></tr>
                            <tr><td>5 por fila</td><td>Filas de <code>/companies/filter</code></td></tr>
                        </tbody>
                    </table>

                    <h4>Plan Free con bono</h4>
                    <ul>
                        <li>Accedes como Pro: datos completos en <code>/companies</code> y <code>/companies/search</code>, y también <code>/companies/verify</code>, <code>POST /companies/reconcile</code>, <code>/companies/borme</code>, <code>/companies/score</code> completo, <code>/companies/signals</code>, <code>/companies/radar</code> (hasta 100 resultados), <code>/companies/insights</code> (vista previa) y <code>/companies/network</code>.</li>
                        <li>No incluye <code>/companies/batch</code>, la vigilancia (<code>/watchlist</code>) ni las rutas de Business.</li>
                        <li>Mientras tengas saldo, cada consulta se paga con créditos, aunque te queden consultas gratuitas. La velocidad sigue siendo de 2 por segundo.</li>
                    </ul>

                    <h4>Plan Free</h4>
                    <ul>
                        <li>Las 100 consultas cuentan todo lo consumido desde el 28/05/2026, también lo gastado con un plan de pago anterior.</li>
                        <li>La vista previa de <code>/companies/score</code> no gasta consultas, pero necesita que te quede alguna: sin consultas ni saldo responde 429 <code>QUOTA_EXCEEDED</code>.</li>
                    </ul>

                    <h4>Vistas previas</h4>
                    <p>Lo que se enseña sin cobrar tiene un tope conjunto de 25 al día por cuenta: la vista previa de <code>/companies/score</code> (Free) y de <code>/companies/insights</code> (Pro), <code>business_preview</code> (Pro) y los recuentos que acompañan a algunos 403 (por ejemplo <code>eventos_ocultos</code> en <code>/companies/signals</code>). Pasado el tope, score e insights responden 429 <code>PREVIEW_LIMIT_EXCEEDED</code>, con <code>Retry-After</code> hasta el día siguiente, y los 403 llegan sin recuentos.</p>
                </section>

                <!-- BY CIF -->
                <section class="docs-section" id="endpoint-by-cif">
                    <h2>3. Consulta por CIF</h2>
                    <p>Obtén la ficha completa de una empresa proporcionando su CIF o NIF.</p>
                    
                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/companies</code>
                    </div>

                    <h4>Parámetros</h4>
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Campo</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>cif</code></td>
                                <td>string</td>
                                <td><strong>Requerido.</strong> El CIF/NIF de la empresa (ej: A15075062).</td>
                            </tr>
                            <tr>
                                <td><code>admin</code></td>
                                <td>boolean</td>
                                <td><strong>Opcional.</strong> Si es <code>true</code>, incluye los administradores y cargos vigentes (se descuentan los ceses, dimisiones y revocaciones publicados en el BORME). <a href="<?= site_url('register?intent=api&plan=pro&period=monthly&source=docs_by_cif') ?>" class="plan-badge pro" style="margin-left: 5px; display: inline-block;; text-decoration: none;" title="Ver el plan Pro">Pro</a></td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Respuesta de éxito (200 OK)</h4>
                    <pre><code class="language-json">{
  "success": true,
  "data": {
    "cif": "B12345678",
    "name": "EMPRESA DE EJEMPLO SL",
    "status": "ACTIVA",
    "status_code": "ACTIVE",
    "status_source": "registry",
    "status_date": "2011-02-03",
    "province": "MADRID",
    "cnae": "6201",
    "cnae_label": "Actividades de programación informática",
    "financials": {
      "size_band": "GT_1M",
      "size_band_label": "Más de 1 M€",
      "last_accounts_year": 2023
    },
    "identifiers": {
      "lei": "5493000EJEMPLO000000",
      "lei_status": "ISSUED",
      "lei_next_renewal_date": "2027-03-15",
      "lei_source": "gleif",
      "lei_as_of": "2026-10-15"
    },
    "legal_form": {
      "code": "SL",
      "label": "Sociedad limitada",
      "iso20275": "DP3Q",
      "source": "name"
    },
    "group": {
      "direct_parent": { "lei": "5493000MATRIZ0000000", "name": "GRUPO EJEMPLO SA", "country": "ES", "cif": "A12345674" },
      "ultimate_parent": { "lei": "5493000MATRIZ0000000", "name": "GRUPO EJEMPLO SA", "country": "ES", "cif": "A12345674" },
      "source": "gleif",
      "as_of": "2026-10-15"
    },
    "ownership": {
      "unipersonal": true,
      "sole_shareholder": {
        "name": "GRUPO EJEMPLO SA",
        "type": "company",
        "since": "2021-06-14",
        "url_pdf": "https://www.boe.es/borme/dias/2021/06/14/pdfs/BORME-A-2021-111-28.pdf"
      },
      "source": "borme",
      "as_of": "2021-06-14"
    },
    "administrators": [
      {
        "name": "JUAN PÉREZ GARCÍA",
        "position": "Adm. Unico",
        "since": "2019-03-01",
        "url_pdf": "https://www.boe.es/borme/dias/2019/03/01/pdfs/BORME-A-2019-42-28.pdf"
      }
    ]
  }
}</code></pre>
                    <p style="font-size: 13px; color: #64748b; margin-top: 5px; margin-bottom: 24px;">* El array <code>administrators</code> solo se incluye si envías el parámetro <code>admin=true</code> y tu plan lo permite.</p>

                    <h4 style="margin-bottom: 12px; font-size: 1.1rem; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                        Diccionario de Datos (Schema)
                    </h4>
                    <table class="docs-table" style="margin-bottom: 40px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th style="width: 25%;">Campo</th>
                                <th style="width: 15%;">Tipo</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">cif</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td>
                                <td style="color: #475569;">El Código de Identificación Fiscal normalizado.</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">name</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td>
                                <td style="color: #475569;">La razón social oficial de la empresa registrada en el BORME.</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">status</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">enum</span></td>
                                <td style="color: #475569;">Estado tal como figura en el Registro, sin normalizar (por ejemplo <code style="font-size: 0.8rem;">ACTIVA</code>, <code style="font-size: 0.8rem;">Activa</code>, <code style="font-size: 0.8rem;">Extinción</code>). Puede venir vacío. Para programar, usa <code style="font-size: 0.8rem;">status_code</code>.</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">status_code</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">enum</span></td>
                                <td style="color: #475569;">Estado normalizado. <code style="font-size: 0.8rem;">ACTIVE</code> (activa en el Registro), <code style="font-size: 0.8rem;">PRESUMED_ACTIVE</code> (sin estado en el Registro, con otros datos registrales y sin ningún hecho en el BORME que la cierre), <code style="font-size: 0.8rem;">INSOLVENCY</code> (concurso en curso), <code style="font-size: 0.8rem;">IN_LIQUIDATION</code>, <code style="font-size: 0.8rem;">DISSOLVED</code>, <code style="font-size: 0.8rem;">REGISTRY_CLOSED</code> (hoja registral cerrada), <code style="font-size: 0.8rem;">MERGED</code> (absorbida), <code style="font-size: 0.8rem;">INACTIVE</code>, <code style="font-size: 0.8rem;">EXTINCT</code> o <code style="font-size: 0.8rem;">UNKNOWN</code> (sin datos para decidir; p. ej. UTE o fichas sin datos del Registro). Todos los planes.</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">status_source</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string | null</span></td>
                                <td style="color: #475569;">De dónde sale <code style="font-size: 0.8rem;">status_code</code>: <code style="font-size: 0.8rem;">registry</code> (estado del Registro) o <code style="font-size: 0.8rem;">borme_analysis</code> (lectura de los actos del BORME, que manda cuando hay un hecho más grave que el estado registrado).</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">status_date</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">date | null</span></td>
                                <td style="color: #475569;">Fecha del estado, cuando se conoce.</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">financials</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">object</span></td>
                                <td style="color: #475569;"><a href="<?= site_url('register?intent=api&plan=pro&period=monthly&source=docs_by_cif') ?>" class="plan-badge pro" style="text-decoration: none;" title="Ver el plan Pro">Pro</a> <code style="font-size: 0.8rem;">size_band</code>: tramo orientativo de facturación (<code style="font-size: 0.8rem;">LT_500K</code>, <code style="font-size: 0.8rem;">500K_1M</code>, <code style="font-size: 0.8rem;">GT_1M</code> o <code style="font-size: 0.8rem;">NO_REVENUE</code>; null si no consta) y su texto en <code style="font-size: 0.8rem;">size_band_label</code>. <code style="font-size: 0.8rem;">last_accounts_year</code>: último ejercicio con cuentas depositadas que consta en nuestra base (puede existir uno posterior aún no incorporado).</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">identifiers</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">object</span></td>
                                <td style="color: #475569;">Código LEI de la empresa en GLEIF (todos los planes). <code style="font-size: 0.8rem;">lei</code> (null si no tiene), <code style="font-size: 0.8rem;">lei_status</code> tal como lo da GLEIF (<code style="font-size: 0.8rem;">ISSUED</code> vigente, <code style="font-size: 0.8rem;">LAPSED</code> caducado por no renovarlo, <code style="font-size: 0.8rem;">RETIRED</code> retirado…), <code style="font-size: 0.8rem;">lei_next_renewal_date</code>, <code style="font-size: 0.8rem;">lei_source</code> y <code style="font-size: 0.8rem;">lei_as_of</code> (fecha de la copia de GLEIF). Lo tienen sobre todo empresas medianas y grandes, y las que operan en mercados financieros.</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">legal_form</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">object</span></td>
                                <td style="color: #475569;">Forma jurídica (todos los planes). <code style="font-size: 0.8rem;">code</code> (<code style="font-size: 0.8rem;">SL</code>, <code style="font-size: 0.8rem;">SA</code>, <code style="font-size: 0.8rem;">SLP</code>, <code style="font-size: 0.8rem;">SLL</code>, <code style="font-size: 0.8rem;">SCOOP</code>, <code style="font-size: 0.8rem;">AIE</code>, <code style="font-size: 0.8rem;">UTE</code>, <code style="font-size: 0.8rem;">FUNDACION</code>…), <code style="font-size: 0.8rem;">label</code>, <code style="font-size: 0.8rem;">iso20275</code> (código ELF de la norma ISO 20275, el que usan GLEIF y los sistemas KYB; null si la forma no tiene código propio, como la SLNE o la UTE) y <code style="font-size: 0.8rem;">source</code>: <code style="font-size: 0.8rem;">name</code> (leída de la razón social) o <code style="font-size: 0.8rem;">cif</code> (deducida de la letra del CIF cuando el nombre no la dice).</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">group</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">object | null</span></td>
                                <td style="color: #475569;"><a href="<?= site_url('register?intent=api&plan=pro&period=monthly&source=docs_by_cif') ?>" class="plan-badge pro" style="text-decoration: none;" title="Ver el plan Pro">Pro</a> Matriz directa (<code style="font-size: 0.8rem;">direct_parent</code>) y cabecera del grupo (<code style="font-size: 0.8rem;">ultimate_parent</code>) que la empresa declara a GLEIF, cada una con <code style="font-size: 0.8rem;">lei</code>, <code style="font-size: 0.8rem;">name</code>, <code style="font-size: 0.8rem;">country</code> y <code style="font-size: 0.8rem;">cif</code> (si es una sociedad española con ficha). null si la empresa no tiene LEI; las dos matrices a null si tiene LEI pero no declara matriz.</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">ownership</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">object</span></td>
                                <td style="color: #475569;"><a href="<?= site_url('register?intent=api&plan=pro&period=monthly&source=docs_by_cif') ?>" class="plan-badge pro" style="text-decoration: none;" title="Ver el plan Pro">Pro</a> Socio único según el BORME. <code style="font-size: 0.8rem;">unipersonal</code>: true, false o null (no consta). <code style="font-size: 0.8rem;">sole_shareholder</code>: <code style="font-size: 0.8rem;">name</code>, <code style="font-size: 0.8rem;">type</code> (<code style="font-size: 0.8rem;">company</code> o <code style="font-size: 0.8rem;">person</code>), <code style="font-size: 0.8rem;">since</code> y <code style="font-size: 0.8rem;">url_pdf</code> (PDF oficial del BORME), o null. <code style="font-size: 0.8rem;">source</code>: <code style="font-size: 0.8rem;">borme</code> (último acto de unipersonalidad publicado) o <code style="font-size: 0.8rem;">name</code> (solo el nombre dice SLU o SAU; el socio no consta). En España solo es público el socio de las sociedades unipersonales: el accionariado de una sociedad con varios socios y el titular real no se publican.</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">province</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td>
                                <td style="color: #475569;">Provincia de registro de la sede social principal.</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">cnae</code> / <code style="background: transparent; color: #2563eb; font-weight: 600;">cnae_label</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td>
                                <td style="color: #475569;">Código CNAE de su actividad económica principal y su descripción descriptiva completa.</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">administrators[]</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td>
                                <td style="color: #475569;">Administradores y cargos vigentes: nombramientos del BORME menos los ceses, dimisiones y revocaciones posteriores. Cada objeto contiene <code style="font-size: 0.8rem;">name</code>, su cargo (<code style="font-size: 0.8rem;">position</code>; si tiene varios, separados por comas), <code style="font-size: 0.8rem;">since</code>, la fecha del nombramiento vigente (null si es anterior a nuestro histórico del BORME), y <code style="font-size: 0.8rem;">url_pdf</code>, el PDF oficial del BORME de ese nombramiento (null si no consta). Si la persona tiene varios cargos, <code style="font-size: 0.8rem;">since</code> y <code style="font-size: 0.8rem;">url_pdf</code> son los de su nombramiento vigente más antiguo.</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Datos en el plan Free</h4>
                    <p>En el plan Free (sin saldo de bono) la respuesta tiene la misma estructura, con estos recortes:</p>
                    <ul>
                        <li><code>address</code> llega con el texto <code>*** [ACTUALIZA A PRO PARA VER LA DIRECCION ]</code>.</li>
                        <li><code>corporate_purpose</code> se corta a 100 caracteres cuando es más largo.</li>
                        <li><code>lat</code> y <code>lng</code> no se incluyen.</li>
                        <li>Se añade el objeto <code>upsell_opportunities</code>. Si tu código necesita saber si la respuesta está recortada, comprueba si existe ese campo.</li>
                        <li><code>financials</code> no se incluye. Dentro de <code>upsell_opportunities</code>, <code>datos_pro</code> indica si hay tramo de facturación y año de cuentas, y cuántas personas tienen un cargo de administración vigente (sin apoderados; <code>admin=true</code> en Pro devuelve además los demás cargos).</li>
                        <li><code>group</code> y <code>ownership</code> no se incluyen. <code>datos_pro</code> indica si consta un socio único (<code>socio_unico</code>) y si la empresa declara una matriz a GLEIF (<code>grupo_empresarial</code>).</li>
                        <li>También dentro de <code>upsell_opportunities</code>: <code>consultas_restantes</code> (ya descontada esta petición), <code>consultas_totales</code> y <code>se_renuevan</code> (<code>false</code>: las del Free no se renuevan).</li>
                        <li><code>status_code</code>, <code>status_source</code>, <code>status_date</code>, <code>identifiers</code> (LEI) y <code>legal_form</code> llegan igual que en los planes de pago.</li>
                    </ul>
                    <p>Con un plan Pro o Business, o con saldo de bono, recibes los datos completos sin cambiar nada en tu código.</p>
                    <p>En el plan Pro, cuando la empresa tiene contratos públicos o perfil de riesgo (datos de Business), la respuesta puede añadir <code>business_preview</code>: <code>contratos_publicos</code>, <code>importe_contratos</code>, <code>perfil_riesgo_disponible</code>, <code>mensaje</code> y <code>checkout_url</code>. Solo son recuentos, y aparece como mucho en 25 respuestas al día. También en <code>/companies/verify</code>.</p>
                </section>

                <section class="docs-section" id="endpoint-verify">
                    <h2>3.1 Verificación KYB</h2>
                    <p>En una sola llamada: si la empresa existe y está operativa, si el nombre que te han dado coincide con la razón social, si quien firma es administrador vigente y si el NIF-IVA está en VIES. Devuelve las alertas y una recomendación (<code>decision_hint</code>) que puedes usar tal cual o ignorar y decidir con los datos. <strong>Coste: 2 consultas.</strong></p>

                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/companies/verify</code>
                        <a href="<?= site_url('register?intent=api&plan=pro&period=monthly&source=docs_verify') ?>" class="plan-badge pro" style="text-decoration: none;" title="Ver el plan Pro">Pro</a>
                    </div>
                    <table class="docs-table">
                        <thead><tr><th>Parámetro</th><th>Descripción</th></tr></thead>
                        <tbody>
                            <tr><td><code>cif</code></td><td><strong>Requerido.</strong> CIF de la empresa.</td></tr>
                            <tr><td><code>name</code></td><td>Opcional. Razón social que te han dado. Se compara sin tildes, signos ni forma jurídica (0-100; coincide desde 80).</td></tr>
                            <tr><td><code>person</code></td><td>Opcional. Nombre y apellidos de quien firma. Se busca entre los administradores vigentes, en cualquier orden.</td></tr>
                            <tr><td><code>vat</code></td><td>Opcional. Con <code>true</code>, comprueba el NIF-IVA intracomunitario en VIES (Comisión Europea).</td></tr>
                        </tbody>
                    </table>
                    <pre><code class="language-json">{
  "success": true,
  "data": {
    "cif": "B12345678",
    "exists": true,
    "name": "EMPRESA DE EJEMPLO SL",
    "status": "ACTIVA",
    "status_code": "ACTIVE",
    "status_source": "registry",
    "status_date": null,
    "identifiers": { "lei": null, "lei_status": null, "lei_next_renewal_date": null, "lei_source": null, "lei_as_of": null },
    "legal_form": { "code": "SL", "label": "Sociedad limitada", "iso20275": "DP3Q", "source": "name" },
    "group": null,
    "ownership": {
      "unipersonal": true,
      "sole_shareholder": { "name": "PEREZ GARCIA JUAN", "type": "person", "since": "2019-03-01", "url_pdf": "https://www.boe.es/borme/dias/2019/03/01/pdfs/BORME-A-2019-42-28.pdf" },
      "source": "borme",
      "as_of": "2019-03-01"
    },
    "checks": {
      "name": { "provided": "Empresa de Ejemplo, S.L.", "score": 100, "match": true },
      "person": { "provided": "Juan Pérez García", "is_current_admin": true, "matched_name": "PEREZ GARCIA JUAN", "position": "Adm. Unico", "since": "2019-03-01", "is_sole_shareholder": true },
      "vat": { "vat_number": "ESB12345678", "checked": true, "valid": true, "source": "VIES", "error": null },
      "accounts": { "last_accounts_year": 2023 }
    },
    "flags": [],
    "decision_hint": "pass",
    "checked_at": "2026-09-28T10:30:00+02:00"
  }
}</code></pre>
                    <table class="docs-table" style="margin-top: 16px;">
                        <thead><tr><th style="width: 18%;">decision_hint</th><th>Cuándo</th></tr></thead>
                        <tbody>
                            <tr><td><code>fail</code></td><td>La sociedad no opera: extinguida, disuelta, en liquidación, hoja registral cerrada, absorbida o inactiva.</td></tr>
                            <tr><td><code>review</code></td><td>Concurso en curso, estado desconocido, el nombre no coincide, quien firma no es administrador vigente, el NIF no está en VIES (si lo pides) o, en Business, riesgo alto.</td></tr>
                            <tr><td><code>pass</code></td><td>Ninguna de las anteriores.</td></tr>
                        </tbody>
                    </table>
                    <ul>
                        <li>Cada alerta de <code>flags</code> lleva <code>code</code>, <code>severity</code> (critical, high, medium, low) y <code>message</code>.</li>
                        <li>Si VIES no responde, <code>checks.vat.checked</code> es <code>false</code> y no afecta a la decisión.</li>
                        <li><code>identifiers</code>, <code>legal_form</code>, <code>group</code> y <code>ownership</code> son los mismos bloques que en <a href="#endpoint-by-cif">/companies</a>. Con <code>person</code>, <code>checks.person.is_sole_shareholder</code> dice además si quien firma es el socio único (persona física). Es informativo: no cambia <code>decision_hint</code>.</li>
                        <li>El año de últimas cuentas es informativo: puede haber depósitos posteriores que aún no constan.</li>
                        <li><strong>Business:</strong> la respuesta añade <code>risk</code> (<code>risk_level</code>, <code>risk_score</code>).</li>
                        <li>Empresa no encontrada: 404 <code>COMPANY_NOT_FOUND</code> con <code>decision_hint: "fail"</code> (no se cobra). Plan Free sin saldo: 403 <code>PLAN_RESTRICTION</code>.</li>
                    </ul>
                </section>

                <section class="docs-section" id="endpoint-reconcile">
                    <h2>3.2 Nombre a CIF (reconciliación)</h2>
                    <p>Envía una lista de nombres de empresa y recibe el CIF de cada una con una puntuación de confianza. Sirve para completar o limpiar bases de clientes y proveedores que tienen el nombre pero no el CIF. <strong>Coste: 1 consulta por cada nombre con coincidencia (<code>match</code>)</strong>; los ambiguos y los que no se encuentran no se cobran.</p>

                    <div class="endpoint-header">
                        <span class="http-badge post">POST</span>
                        <code>/companies/reconcile</code>
                        <a href="<?= site_url('register?intent=api&plan=pro&period=monthly&source=docs_reconcile') ?>" class="plan-badge pro" style="text-decoration: none;" title="Ver el plan Pro">Pro</a>
                    </div>
                    <pre><code class="language-json">// Petición
{
  "items": [
    { "name": "Mercadona, S.A.", "province": "Valencia" },
    { "name": "Talleres Pérez" },
    { "name": "Panadería Inventada del Norte" }
  ]
}

// Respuesta
{
  "success": true,
  "data": [
    {
      "input": { "name": "Mercadona, S.A.", "province": "Valencia" },
      "status": "match",
      "score": 100,
      "company": { "cif": "A46103834", "name": "MERCADONA SA", "province": "VALENCIA", "status": "ACTIVA", "status_code": "ACTIVE", "score": 100 }
    },
    {
      "input": { "name": "Talleres Pérez", "province": null },
      "status": "ambiguous",
      "candidates": [
        { "cif": "B12345678", "name": "TALLERES PEREZ SL", "province": "MADRID", "status": "ACTIVA", "status_code": "ACTIVE", "score": 100 },
        { "cif": "A12345674", "name": "TALLERES PEREZ SA", "province": "SEVILLA", "status": "ACTIVA", "status_code": "ACTIVE", "score": 100 }
      ]
    },
    { "input": { "name": "Panadería Inventada del Norte", "province": null }, "status": "no_match" }
  ],
  "meta": { "requested": 3, "matched": 1, "ambiguous": 1, "no_match": 1, "invalid": 0, "skipped_quota": 0, "cost": 1, "thresholds": { "match": 85, "ambiguous": 70 } }
}</code></pre>
                    <ul>
                        <li>Hasta 100 nombres por petición. También vale <code>{"names": ["...", "..."]}</code>.</li>
                        <li>El nombre se compara sin tildes, signos ni forma jurídica. <code>match</code>: puntuación de 85 o más y claramente por delante del siguiente candidato. <code>ambiguous</code>: hay candidatos plausibles (70 o más), se devuelven hasta 3. <code>no_match</code>: ninguno llega a 70.</li>
                        <li>La provincia (<code>province</code>) es opcional y sirve para desempatar entre empresas con el mismo nombre.</li>
                        <li>Si no te llega el cupo, los nombres que quedan salen como <code>skipped_quota</code> y no se cobran.</li>
                        <li>Pro y Business (y Free con saldo). Plan Free sin saldo: 403 <code>PLAN_RESTRICTION</code>.</li>
                    </ul>
                </section>

                <!-- SEARCH -->
                <section class="docs-section" id="endpoint-search">
                    <h2>4. Búsqueda por Nombre</h2>
                    <p>Busca empresas similares a un nombre o razón social. Sin <code>multiple</code> devuelve la mejor coincidencia, con prioridad para las sociedades inscritas en el Registro frente a UTE y fichas sin datos.</p>
                    
                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/companies/search</code>
                    </div>

                    <h4>Parámetros</h4>
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Campo</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>name</code></td>
                                <td>string</td>
                                <td><strong>Requerido.</strong> El nombre o parte del nombre a buscar. (Alias: <code>q</code>)</td>
                            </tr>
                            <tr>
                                <td><code>multiple</code></td>
                                <td>boolean</td>
                                <td>Opcional. Sin él (o con <code>false</code>) devuelve en <code>data</code> <strong>un solo objeto</strong>: la empresa que mejor coincide. Con <code>true</code> devuelve una <strong>lista</strong> en <code>data</code> y la paginación en <code>meta</code> (el ejemplo de abajo).</td>
                            </tr>
                            <tr>
                                <td><code>limit</code></td>
                                <td>integer</td>
                                <td>Opcional, solo con <code>multiple=true</code>. Resultados por página (por defecto 20, máximo 100).</td>
                            </tr>
                            <tr>
                                <td><code>page</code> / <code>cursor</code></td>
                                <td>integer / string</td>
                                <td>Opcional, solo con <code>multiple=true</code>. Página a pedir, o el <code>meta.next_cursor</code> de la respuesta anterior (ver <a href="#paginacion">Paginación</a>). Con <code>cursor</code>, envía también el mismo <code>limit</code> que en la primera petición: si no, se usa 20.</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Respuesta de éxito con <code>multiple=true</code> (200 OK)</h4>
                    <pre><code class="language-json">{
  "success": true,
  "data": [
    {
      "cif": "B12345678",
      "name": "EMPRESA DE EJEMPLO SL",
      "status": "ACTIVA",
      "province": "MADRID"
    }
  ],
  "meta": {
    "page": 1,
    "limit": 20,
    "has_more": false
  }
}</code></pre>
                    <h4 style="margin-top: 24px; margin-bottom: 12px; font-size: 1.1rem; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                        Diccionario de Datos (Search)
                    </h4>
                    <table class="docs-table" style="margin-bottom: 40px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">data[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td><td style="color: #475569;">Lista de empresas coincidentes, con los mismos campos que <code style="font-size: 0.8rem;">/companies</code> (sin administradores): <code style="font-size: 0.8rem;">cif</code>, <code style="font-size: 0.8rem;">name</code>, <code style="font-size: 0.8rem;">status</code>, <code style="font-size: 0.8rem;">status_code</code>, <code style="font-size: 0.8rem;">province</code>…</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.page</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Página actual de los resultados de búsqueda.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.limit</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Número máximo de resultados por página.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.has_more</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">boolean</span></td><td style="color: #475569;">Indica si existen más resultados para la búsqueda actual.</td></tr>
                        </tbody>
                    </table>

                    <h4>Datos en el plan Free</h4>
                    <p>En el plan Free (sin saldo de bono), los mismos recortes que en <a href="#endpoint-by-cif">/companies</a>: <code>address</code> oculta, <code>corporate_purpose</code> cortado a 100 caracteres, sin <code>lat</code>, <code>lng</code> ni <code>financials</code>, y con <code>upsell_opportunities</code> (<code>campos_ocultos</code>, <code>mensaje</code> y <code>upgrade_url</code>) en cada empresa. Con Pro, Business o saldo de bono, datos completos.</p>
                </section>

                <!-- EXPANDED -->
                <section class="docs-section" id="endpoint-expanded">
                    <h2>5. Capacidades Comerciales (Pro & Business)</h2>
                    <p>Potencia tu prospección con datos enriquecidos y señales de negocio en tiempo real.</p>
                    
                    <!-- SCORE -->
                    <div style="margin-top: 32px;">
                        <div class="endpoint-header">
                            <span class="http-badge get">GET</span>
                            <code>/companies/score</code>
                            <a href="<?= site_url('register?intent=api&plan=pro&period=monthly&source=docs_expanded') ?>" class="plan-badge pro" style="text-decoration: none;" title="Ver el plan Pro">Pro</a>
                        </div>
                        <p>Puntuación comercial de 0 a 100 de una empresa: su actividad reciente en el BORME, lo completa que está su ficha y si tiene teléfono, email o web. No mide solvencia (para eso está <code>/companies/risk-profile</code>). En Free devuelve solo la cifra, sin gastar consultas (dentro del tope de 25 vistas previas al día); <code>priority</code>, <code>reasons</code> y <code>last_signal</code> llegan bloqueados.</p>
                        <pre><code class="language-json">{
  "success": true,
  "data": {
    "cif": "B12345678",
    "score": 82,
    "priority": "muy_alta",
    "reasons": ["OPP:95|QUAL:80|CONT:60 · Ampliación de capital"],
    "last_signal": { "type": "Ampliación de capital", "date": "2026-09-15", "url_pdf": "https://www.boe.es/borme/dias/2026/09/15/pdfs/BORME-A-2026-176-15.pdf" },
    "source": "radar"
  }
}</code></pre>
                        <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <thead style="background: #f8fafc;">
                                <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">data.score</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Puntuación comercial de 0 a 100.</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">data.priority</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">enum</span></td><td style="color: #475569;">Prioridad de prospección según la puntuación: <code>muy_alta</code> (75 o más), <code>alta</code> (60), <code>media</code> (40), <code>baja</code> (20) o <code>muy_baja</code>.</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">data.reasons</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array</span></td><td style="color: #475569;">Lista de motivos de la puntuación.</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">data.last_signal</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">object</span></td><td style="color: #475569;">Último acto del BORME que cuenta para la puntuación: <code>type</code>, <code>date</code> y <code>url_pdf</code> (PDF oficial del BORME de ese día; <code>null</code> si no consta).</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- SIGNALS -->
                    <div style="margin-top: 32px;">
                        <div class="endpoint-header">
                            <span class="http-badge get">GET</span>
                            <code>/companies/signals</code>
                            <a href="<?= site_url('register?intent=api&plan=pro&period=monthly&source=docs_expanded') ?>" class="plan-badge pro" style="text-decoration: none;" title="Ver el plan Pro">Pro</a>
                        </div>
                        <p>El último acto del BORME que cuenta para la <a href="#endpoint-expanded">puntuación comercial</a> de la empresa. La lista trae como mucho un elemento. Plan Free: 403 <code>PLAN_RESTRICTION</code> con el número de eventos en <code>upsell_opportunities.eventos_ocultos</code>.</p>
                        <pre><code class="language-json">{
  "success": true,
  "data": {
    "cif": "A46103834",
    "signals": [
      {
        "type": "borme_event",
        "label": "Ampliación de capital",
        "date": "2026-09-15",
        "probability": "muy_alta",
        "url_pdf": "https://www.boe.es/borme/dias/2026/09/15/pdfs/BORME-A-2026-176-15.pdf"
      }
    ]
  }
}</code></pre>
                        <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <thead style="background: #f8fafc;">
                                <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">signals[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td><td style="color: #475569;">Como mucho un elemento: <code>type</code> (siempre <code>borme_event</code>), <code>label</code> (tipo de acto) y <code>date</code>.</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">signals[].probability</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Prioridad comercial de la empresa, la misma que <code>priority</code> en <code>/companies/score</code>: <code>muy_alta</code>, <code>alta</code>, <code>media</code>, <code>baja</code> o <code>muy_baja</code>. No es la fiabilidad del evento.</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">signals[].url_pdf</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string | null</span></td><td style="color: #475569;">PDF oficial del BORME del día de la señal (<code>null</code> si no consta).</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- INSIGHTS -->
                    <div style="margin-top: 32px;">
                        <div class="endpoint-header">
                            <span class="http-badge get">GET</span>
                            <code>/companies/insights</code>
                            <a href="<?= site_url('register?intent=api&plan=business&period=monthly&source=docs_expanded') ?>" class="plan-badge business" style="text-decoration: none;" title="Ver el plan Business">Business</a>
                        </div>
                        <p>Análisis IA del perfil comercial y necesidades probables de la empresa. Con Pro (o Free con saldo de bono) hay una vista previa que no gasta consultas, dentro del tope de 25 al día: solo <code>profile</code> y <code>conversion_probability</code>; <code>pain_points</code>, <code>buyer_persona</code> y <code>sales_arguments</code> llegan bloqueados, con <code>message</code> y <code>checkout_url</code>. Plan Free: 403 <code>PLAN_RESTRICTION</code>.</p>
                        <pre><code class="language-json">{
  "success": true,
  "data": {
    "profile": "Servicios de Tecnología",
    "summary": "Empresa enfocada en desarrollo software...",
    "needs": ["Presencia Web", "Gestión Cloud"],
    "conversion_probability": "Alta",
    "estimated_ticket": "Medio-Alto"
  }
}</code></pre>
                        <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <thead style="background: #f8fafc;">
                                <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">needs[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[string]</span></td><td style="color: #475569;">Lista de necesidades de software detectadas mediante inferencia IA.</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">conversion_probability</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Estimación de probabilidad de venta cruzada B2B.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- CONTACT PREP -->
                    <div style="margin-top: 32px;">
                        <div class="endpoint-header">
                            <span class="http-badge get">GET</span>
                            <code>/companies/contact-prep</code>
                            <a href="<?= site_url('register?intent=api&plan=business&period=monthly&source=docs_expanded') ?>" class="plan-badge business" style="text-decoration: none;" title="Ver el plan Business">Business</a>
                        </div>
                        <p>Pitch de venta sugerido y manejo de objeciones generado por IA.</p>
                        <pre><code class="language-json">{
  "success": true,
  "data": {
    "sales_approach": "Enfoque consultivo y directo...",
    "suggested_message": "Hola, he notado el reciente crecimiento de...",
    "likely_objection": "Falta de presupuesto temporal",
    "attack_angle": "Demostrar el ROI inmediato..."
  }
}</code></pre>
                        <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <thead style="background: #f8fafc;">
                                <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">sales_approach</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Estrategia de acercamiento comercial sugerida.</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">likely_objection</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Objeción principal esperada por parte del cliente.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- NETWORK -->
                    <div style="margin-top: 32px;">
                        <div class="endpoint-header">
                            <span class="http-badge get">GET</span>
                            <code>/companies/network</code>
                            <a href="<?= site_url('register?intent=api&plan=pro&period=monthly&source=docs_expanded') ?>" class="plan-badge pro" style="text-decoration: none;" title="Ver el plan Pro">Pro</a>
                        </div>
                        <p>Obtiene la red de vinculación entre empresas a través de sus administradores. Requiere el parámetro <code>cif</code>. Pro, Business y Free con saldo de bono; en Free sin saldo, 403 <code>PLAN_RESTRICTION</code> con los recuentos en <code>data.stats</code>. Si la empresa no tiene administradores conocidos, responde 200 solo con el nodo de la empresa, sin <code>stats</code> y con un <code>message</code> (se cobra como una consulta normal).</p>
                        <pre><code class="language-json">{
  "success": true,
  "data": {
    "nodes": [
      { "id": "C_123", "type": "company", "label": "EMPRESA DE EJEMPLO SL", "cif": "B12345678", "root": true },
      { "id": "A_abc", "type": "administrator", "label": "JUAN PEREZ GARCIA" }
    ],
    "edges": [
      { "source": "A_abc", "target": "C_123", "label": "Administrador" }
    ],
    "stats": {
      "total_administrators": 1,
      "total_linked_companies": 1
    }
  }
}</code></pre>
                        <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <thead style="background: #f8fafc;">
                                <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">nodes[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td><td style="color: #475569;">Nodos del grafo (empresas o administradores).</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">edges[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td><td style="color: #475569;">Conexiones (vínculos) entre los nodos.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- MATCH -->
                    <div style="margin-top: 32px;">
                        <div class="endpoint-header">
                            <span class="http-badge get">GET</span>
                            <code>/companies/match</code>
                            <a href="<?= site_url('register?intent=api&plan=business&period=monthly&source=docs_expanded') ?>" class="plan-badge business" style="text-decoration: none;" title="Ver el plan Business">Business</a>
                        </div>
                        <p>Calculadora de Match B2B. Requiere los parámetros <code>cif</code> y <code>seller_sector</code>. Devuelve el nivel de encaje comercial y un argumentario personalizado.</p>
                        <pre><code class="language-json">{
  "success": true,
  "data": {
    "match_score": 85,
    "fit_level": "Alto",
    "pain_points_addressed": ["Ineficiencia operativa", "Falta de digitalización"],
    "sales_argument": "Nuestro software elimina el trabajo manual...",
    "recommendation": "Contactar de inmediato."
  }
}</code></pre>
                        <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <thead style="background: #f8fafc;">
                                <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">match_score</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Nivel de encaje B2B (0-100).</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">sales_argument</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Argumentario de venta adaptado al match.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- BATCH -->
                    <div id="batch" style="margin-top: 32px;">
                        <div class="endpoint-header">
                            <span class="http-badge post">POST</span>
                            <code>/companies/batch</code>
                            <a href="<?= site_url('register?intent=api&plan=pro&period=monthly&source=docs_expanded') ?>" class="plan-badge pro" style="text-decoration: none;" title="Ver el plan Pro">Pro</a>
                            <a href="<?= site_url('register?intent=api&plan=business&period=monthly&source=docs_expanded') ?>" class="plan-badge business" style="text-decoration: none;" title="Ver el plan Business">Business</a>
                        </div>
                        <p>Consulta hasta 100 empresas de una sola vez enviando un array de CIFs. El coste es variable: 1 consulta de tu cuota mensual o monedero por cada empresa <strong>encontrada</strong> (código 200). Si no tienes suficientes créditos para cubrir el lote entero, la respuesta se recortará automáticamente hasta el número de empresas que puedas pagar. Solo con plan Pro o Business: un Free con saldo de bono recibe 403.</p>
                        
                        <h5>Cuerpo de la Petición (JSON)</h5>
                        <pre><code class="language-json">{
  "cifs": ["A15075062", "A46103834"],
  "admin": true
}</code></pre>

                        <h5>Respuesta de éxito</h5>
                        <pre><code class="language-json">{
  "success": true,
  "data": [
    {
      "cif": "B12345678",
      "name": "EMPRESA DE EJEMPLO SL",
      "status": "ACTIVA"
    },
    {
      "cif": "A87654321",
      "name": "OTRA EMPRESA SA",
      "status": "ACTIVA"
    }
  ],
  "meta": {
    "requested": 3,
    "found": 2,
    "cost": 2,
    "truncated": false
  }
}</code></pre>
                        <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <thead style="background: #f8fafc;">
                                <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.requested</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Número de CIFs enviados en la petición.</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.cost</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Créditos deducidos de tu plan por las empresas encontradas.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- BORME -->
                <section class="docs-section" id="endpoint-borme">
                    <h2>6. Historial de Actos del BORME</h2>
                    <p>Obtén el historial cronológico completo de publicaciones en el Registro Mercantil (BORME) para una empresa. Pro, Business y Free con saldo de bono. Plan Free sin saldo: 403 <code>UPGRADE_REQUIRED</code>.</p>
                    
                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/companies/borme</code>
                        <a href="<?= site_url('register?intent=api&plan=pro&period=monthly&source=docs_borme') ?>" class="plan-badge pro" style="text-decoration: none;" title="Ver el plan Pro">Pro</a>
                    </div>

                    <h4>Parámetros</h4>
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Campo</th>
                                <th>Tipo</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>cif</code></td>
                                <td>string</td>
                                <td><strong>Requerido.</strong> El CIF/NIF de la empresa (ej: A15075062).</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Respuesta de éxito (200 OK)</h4>
                    <pre><code class="language-json">{
  "success": true,
  "data": {
    "cif": "B12345678",
    "company_name": "EMPRESA DE EJEMPLO SL",
    "events": [
      {
        "date": "2023-11-01",
        "act_types": "Nombramientos, Ceses",
        "description": "Ceses/Dimisiones. Administrador único: JUAN PEREZ...",
        "url_pdf": "https://www.boe.es/borme/dias/2023/11/01/pdfs/BORME-A-2023-100-28.pdf"
      }
    ]
  }
}</code></pre>
                    <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">events[].date</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Fecha de publicación oficial en el BORME (Formato YYYY-MM-DD).</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">events[].act_types</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Tipo de acto mercantil categorizado (ej: Nombramientos, Ampliación).</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">events[].url_pdf</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">URL directa al escaneo PDF original del Boletín Oficial.</td></tr>
                        </tbody>
                    </table>
                </section>

                <!-- RADAR -->
                <section class="docs-section" id="endpoint-radar">
                    <h2>7. Radar de Empresas</h2>
                    <p>Empresas constituidas en el periodo que pidas (por defecto, hoy), ordenadas de mayor a menor puntuación comercial.</p>
                    <ul>
                        <li>Free: 10 resultados con el nombre y el CIF ocultos. Gasta 1 consulta.</li>
                        <li>Pro y Free con saldo de bono: hasta 100 resultados. Business: hasta 1.000.</li>
                        <li>Si hay más empresas de las que caben, <code>meta.oportunidades_ocultas</code> dice cuántas faltan.</li>
                        <li>Todos los filtros funcionan en todos los planes.</li>
                    </ul>
                    
                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/companies/radar</code>
                        <a href="<?= site_url('register?intent=api&plan=pro&period=monthly&source=docs_radar') ?>" class="plan-badge pro" style="text-decoration: none;" title="Ver el plan Pro">Pro</a>
                    </div>

                    <h4>Parámetros opcionales</h4>
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Campo</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>province</code></td>
                                <td>Filtrar por provincia (ej: MADRID).</td>
                            </tr>
                            <tr>
                                <td><code>range</code></td>
                                <td>Ventana temporal. Acepta el valor <code>hoy</code> (por defecto) o un número entero indicando los <strong>días atrás</strong> (ej: <code>7</code>, <code>30</code>).</td>
                            </tr>
                            <tr>
                                <td><code>priority</code></td>
                                <td>Prioridad comercial: <code>muy_alta</code>, <code>alta</code>, <code>media</code>, <code>baja</code> o <code>muy_baja</code>.</td>
                            </tr>
                            <tr>
                                <td><code>cnae</code></td>
                                <td>Uno o varios prefijos CNAE separados por comas (ej: <code>62</code> o <code>4711,4719</code>).</td>
                            </tr>
                            <tr>
                                <td><code>min_score</code></td>
                                <td>Puntuación mínima de oportunidad, de 0 a 100.</td>
                            </tr>
                            <tr>
                                <td><code>main_act_type</code></td>
                                <td>Tipo del último acto relevante del BORME (ej: <code>Constitución</code>, <code>Ampliación de capital</code>).</td>
                            </tr>
                            <tr>
                                <td><code>has_phone</code></td>
                                <td>Con <code>true</code>, solo empresas con teléfono.</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Respuesta de éxito (200 OK)</h4>
                    <pre><code class="language-json">{
  "success": true,
  "meta": {
    "plan": "business",
    "count": 50,
    "limit": 1000,
    "total_disponibles": 50
  },
  "data": [
    {
      "id": 4812345,
      "company_name": "NUEVA EMPRESA SL",
      "cif": "B12345678",
      "fecha_constitucion": "2026-10-13",
      "cnae": "6201",
      "cnae_label": "Actividades de programación informática",
      "registro_mercantil": "MADRID",
      "municipality": "MADRID",
      "score_total": 78,
      "priority_level": "alta",
      "main_act_type": "Constitución"
    }
  ]
}</code></pre>
                    <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">data[].fecha_constitucion</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Fecha de constitución de la empresa.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">data[].score_total</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer | null</span></td><td style="color: #475569;">Puntuación comercial (0-100). Con <code>priority_level</code> y <code>main_act_type</code>, último acto relevante del BORME.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.plan</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Tu plan contratado.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.total_disponibles</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Número total de empresas en el Radar. Si tu plan está limitado a `limit`, verás el total real aquí.</td></tr>
                        </tbody>
                    </table>
                </section>

                <!-- WEBHOOKS -->
                <section class="docs-section" id="endpoint-watchlist">
                    <h2>7.1 Vigilancia de Empresas</h2>
                    <p>Da de alta las empresas que te interesan y consulta sus cambios desde una fecha: actos nuevos en el BORME, cambios de estado en el Registro y cambios de nivel de riesgo. <strong>No gasta consultas.</strong> Pro: hasta 100 empresas. Business: hasta 1.000.</p>

                    <div class="endpoint-header">
                        <span class="http-badge post">POST</span>
                        <code>/watchlist</code>
                        <a href="<?= site_url('register?intent=api&plan=pro&period=monthly&source=docs_watchlist') ?>" class="plan-badge pro" style="text-decoration: none;" title="Ver el plan Pro">Pro</a>
                    </div>
                    <p>Añade hasta 500 CIF por petición. La respuesta dice qué pasó con cada uno.</p>
                    <pre><code class="language-json">// Petición
{ "cifs": ["A46103834", "A15075062"] }

// Respuesta
{
  "success": true,
  "data": {
    "added": ["A46103834"],
    "already_watching": ["A15075062"],
    "not_found": [],
    "invalid": [],
    "rejected_over_limit": []
  },
  "meta": { "total": 2, "watch_limit": 100 }
}</code></pre>

                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/watchlist</code>
                    </div>
                    <p>Lista tus empresas vigiladas (<code>page</code>, <code>limit</code> hasta 500).</p>
                    <pre><code class="language-json">{
  "success": true,
  "data": [
    { "cif": "A46103834", "name": "MERCADONA SA", "added_at": "2026-09-27 10:12:04" },
    { "cif": "A15075062", "name": "INDUSTRIA DE DISENO TEXTIL SA", "added_at": "2026-09-28 08:30:51" }
  ],
  "meta": { "total": 2, "watch_limit": 100, "page": 1, "limit": 100, "has_more": false }
}</code></pre>

                    <div class="endpoint-header">
                        <span class="http-badge delete">DELETE</span>
                        <code>/watchlist/{cif}</code>
                    </div>
                    <p>Quita una empresa. Si no estaba en tu vigilancia, responde 404 <code>NOT_WATCHING</code>.</p>
                    <pre><code class="language-json">{
  "success": true,
  "data": { "cif": "A46103834", "removed": true },
  "meta": { "total": 1, "watch_limit": 100 }
}</code></pre>

                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/watchlist/events</code>
                    </div>
                    <table class="docs-table">
                        <thead><tr><th>Parámetro</th><th>Descripción</th></tr></thead>
                        <tbody>
                            <tr><td><code>since</code></td><td>Fecha <code>YYYY-MM-DD</code>, incluida. Por defecto, hace 7 días; como mucho, 90 días atrás.</td></tr>
                            <tr><td><code>types</code></td><td>Opcional. <code>borme_act</code>, <code>status_change</code>, <code>risk_level_change</code>, separados por comas.</td></tr>
                            <tr><td><code>cif</code></td><td>Opcional. Solo los eventos de esa empresa.</td></tr>
                            <tr><td><code>page</code> / <code>limit</code></td><td>Paginación (hasta 500 por página).</td></tr>
                        </tbody>
                    </table>
                    <pre><code class="language-json">{
  "success": true,
  "data": [
    {
      "id": "borme_act:8812345",
      "type": "borme_act",
      "date": "2026-09-22",
      "cif": "B12345678",
      "company_name": "EMPRESA DE EJEMPLO SL",
      "data": {
        "act_types": "Nombramientos, Ceses/Dimisiones",
        "description": "Ceses/Dimisiones. Adm. Unico: ...",
        "url_pdf": "https://www.boe.es/borme/dias/2026/09/22/pdfs/BORME-A-2026-181-28.pdf"
      }
    },
    {
      "id": "risk_level_change:B12345678:2026-09-25",
      "type": "risk_level_change",
      "date": "2026-09-25",
      "cif": "B12345678",
      "company_name": "EMPRESA DE EJEMPLO SL",
      "data": { "from": "BAJO", "to": "MEDIO", "model_change": null }
    }
  ],
  "meta": { "since": "2026-09-20", "types": ["borme_act", "status_change", "risk_level_change"], "total": 2, "page": 1, "limit": 100, "has_more": false }
}</code></pre>
                    <ul>
                        <li>Los eventos salen de más antiguo a más reciente. El <code>id</code> es estable: úsalo para no procesar dos veces el mismo evento.</li>
                        <li><code>model_change: true</code> indica que el nivel cambió por un recálculo de nuestro modelo, no por algo nuevo de la empresa, y <code>false</code> que no. <code>null</code> si no consta la versión del modelo con la que se calculó (perfiles antiguos).</li>
                        <li>Uso recomendado: una consulta al día con <code>since</code> = la fecha de tu última consulta.</li>
                        <li>Plan Free (también con saldo de bono): 403 <code>PLAN_RESTRICTION</code>. En Business puedes recibir estos mismos eventos en tu servidor con <a href="#endpoint-webhooks">webhooks</a>.</li>
                    </ul>
                </section>

                <section class="docs-section" id="endpoint-filter">
                    <h2>7.2 Segmentos de Empresas</h2>
                    <p>Filtra empresas por actividad, zona, tamaño y otros criterios. <strong>Contar es gratis en todos los planes</strong> (<code>count_only=true</code>): sabrás cuántas empresas encajan antes de pagar nada. Descargar las filas es del plan Business y cuesta <strong>5 consultas por fila devuelta</strong>.</p>

                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/companies/filter</code>
                        <a href="<?= site_url('register?intent=api&plan=business&period=monthly&source=docs_filter') ?>" class="plan-badge business" style="text-decoration: none;" title="Ver el plan Business">Business</a>
                    </div>
                    <table class="docs-table">
                        <thead><tr><th>Parámetro</th><th>Descripción</th></tr></thead>
                        <tbody>
                            <tr><td><code>cnae</code></td><td>Prefijos CNAE separados por comas (ej: <code>62</code> o <code>4711,4719</code>).</td></tr>
                            <tr><td><code>province</code></td><td>Provincia del Registro Mercantil (ej: <code>MADRID</code>).</td></tr>
                            <tr><td><code>municipality</code></td><td>Municipio (ej: <code>GETAFE</code>).</td></tr>
                            <tr><td><code>status</code></td><td><code>active</code> (por defecto: activas en el Registro), <code>active_or_unknown</code> (también las que no tienen estado registrado) o <code>any</code>.</td></tr>
                            <tr><td><code>founded_from / founded_to</code></td><td>Fecha de constitución, <code>YYYY-MM-DD</code>.</td></tr>
                            <tr><td><code>has_phone</code></td><td>Con <code>true</code>, solo empresas con teléfono. El número no se devuelve.</td></tr>
                            <tr><td><code>size_band</code></td><td>Tramos de facturación separados por comas: <code>NO_REVENUE</code>, <code>LT_500K</code>, <code>500K_1M</code>, <code>GT_1M</code>.</td></tr>
                            <tr><td><code>min_accounts_year</code></td><td>Último año de cuentas depositadas igual o posterior (ej: <code>2023</code>).</td></tr>
                            <tr><td><code>count_only</code></td><td>Con <code>true</code>, solo el recuento. Gratis.</td></tr>
                            <tr><td><code>limit / cursor</code></td><td>Filas por página (1-1000, por defecto 100) y <code>meta.next_cursor</code> de la página anterior.</td></tr>
                        </tbody>
                    </table>
                    <p>Hace falta al menos uno de <code>cnae</code>, <code>province</code> o <code>municipality</code>.</p>

                    <h4>Solo el recuento (todos los planes, gratis)</h4>
                    <pre><code class="language-json">// GET /companies/filter?cnae=62&province=MADRID&has_phone=true&count_only=true
{
  "success": true,
  "data": { "total": 3412 },
  "meta": {
    "filters": { "cnae": ["62"], "province": "MADRID", "status": "active", "has_phone": true },
    "cost": 0,
    "counted_at": "2026-09-29T09:30:00+02:00"
  }
}</code></pre>

                    <h4>Filas (Business)</h4>
                    <pre><code class="language-json">// GET /companies/filter?cnae=62&province=MADRID&has_phone=true&limit=100
{
  "success": true,
  "data": [
    {
      "cif": "B12345678",
      "name": "EMPRESA DE EJEMPLO SL",
      "cnae": "6201",
      "cnae_label": "Actividades de programación informática",
      "province": "MADRID",
      "municipality": "MADRID",
      "founded": "2019-03-01",
      "status": "ACTIVA",
      "status_code": "ACTIVE",
      "status_source": "registry",
      "financials": { "size_band": "GT_1M", "size_band_label": "Más de 1 M€", "last_accounts_year": 2023 },
      "has_phone": true
    }
  ],
  "meta": {
    "total": 3412,
    "returned": 100,
    "limit": 100,
    "has_more": true,
    "next_cursor": "eyJpZCI6ODgxMjM0NX0",
    "cost": 500,
    "cost_per_row": 5,
    "truncated": false,
    "filters": { "cnae": ["62"], "province": "MADRID", "status": "active", "has_phone": true }
  }
}</code></pre>
                    <ul>
                        <li>Se cobran solo las filas devueltas: <code>meta.cost</code> = filas × 5. Si no te llega el cupo, la página se recorta (<code>truncated: true</code>) a las filas que puedes pagar.</li>
                        <li>Para la página siguiente, pasa <code>meta.next_cursor</code> en <code>cursor</code> con los mismos filtros.</li>
                        <li>Los recuentos se guardan 6 horas: repetir la misma búsqueda no vuelve a calcularse.</li>
                        <li>Sin plan Business, pedir filas responde 403 <code>PLAN_RESTRICTION</code> con el recuento en <code>upsell_opportunities.total</code>.</li>
                        <li>No se devuelven teléfonos ni emails: solo <code>has_phone</code>.</li>
                    </ul>
                </section>

                <section class="docs-section" id="endpoint-webhooks">
                    <h2>8. Webhooks (Solo Business)</h2>
                    <p>Recibe en tu servidor, sin tener que consultar la API, los cambios de las empresas de tu <a href="#endpoint-watchlist">vigilancia</a>: actos nuevos en el BORME, cambios de estado y cambios de nivel de riesgo. Los eventos se generan cada 15 minutos.</p>

                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/webhooks</code>
                        <a href="<?= site_url('register?intent=api&plan=business&period=monthly&source=docs_webhooks') ?>" class="plan-badge business" style="text-decoration: none;" title="Ver el plan Business">Business</a>
                    </div>
                    <p>Lista tus webhooks con su estado: <code>is_active</code>, <code>failure_count</code> (intentos fallidos seguidos), <code>last_status_code</code> y fechas del último envío. Incluye el <code>secret</code> de cada uno.</p>
                    <pre><code class="language-json">{
  "success": true,
  "data": [
    {
      "id": 12,
      "user_id": 229,
      "url": "https://tu-dominio.com/webhooks/apiempresas",
      "event": "watchlist.*",
      "secret": "3f9c1b2a7d...",
      "filters": "[]",
      "is_active": 1,
      "failure_count": 0,
      "last_delivery_at": "2026-10-13 09:15:02",
      "last_success_at": "2026-10-13 09:15:02",
      "last_status_code": 200,
      "disabled_at": null,
      "created_at": "2026-09-28 10:00:00",
      "updated_at": "2026-10-13 09:15:02"
    }
  ]
}</code></pre>

                    <div class="endpoint-header">
                        <span class="http-badge post">POST</span>
                        <code>/webhooks</code>
                        <a href="<?= site_url('register?intent=api&plan=business&period=monthly&source=docs_webhooks') ?>" class="plan-badge business" style="text-decoration: none;" title="Ver el plan Business">Business</a>
                    </div>
                    <h4>Crear un webhook</h4>
                    <pre><code class="language-json">// Petición
{ "url": "https://tu-dominio.com/webhooks/apiempresas", "event": "watchlist.*" }

// Respuesta (201)
{
  "success": true,
  "message": "Webhook creado correctamente",
  "id": 12,
  "event": "watchlist.*",
  "secret": "3f9c1b2a7d..."
}</code></pre>
                    <p>Guarda el <code>secret</code>: sirve para comprobar que cada envío es nuestro. Si lo mandas en la petición, se usa el tuyo.</p>

                    <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr><th style="width: 30%;">Evento</th><th>Cuándo llega</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">company.borme_act</code></td><td style="color: #475569;">Se publica un acto en el BORME de una empresa vigilada.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">company.status_changed</code></td><td style="color: #475569;">El Registro cambia el estado de una empresa vigilada.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">company.risk_level_changed</code></td><td style="color: #475569;">Cambia su nivel de riesgo (<code>model_change: true</code> si es por un recálculo de nuestro modelo).</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">watchlist.*</code></td><td style="color: #475569;">Los tres anteriores.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">test.ping</code></td><td style="color: #475569;">Solo al llamar a <code>/webhooks/{id}/test</code>.</td></tr>
                        </tbody>
                    </table>

                    <h4>Qué recibe tu servidor</h4>
                    <p>Un <code>POST</code> con JSON y estas cabeceras: <code>X-ApiEmpresas-Event</code>, <code>X-ApiEmpresas-Delivery</code> (id único del envío) y <code>X-ApiEmpresas-Signature</code>.</p>
                    <pre><code class="language-json">{
  "id": "5d41402a-bc4b-2a76-b971-9d911017c592",
  "event": "company.borme_act",
  "created_at": "2026-09-28T10:15:00+02:00",
  "data": {
    "id": "borme_act:8812345",
    "type": "borme_act",
    "date": "2026-09-28",
    "cif": "B12345678",
    "company_name": "EMPRESA DE EJEMPLO SL",
    "data": { "act_types": "Nombramientos", "description": "Nombramientos. Adm. Unico: ...", "url_pdf": "https://www.boe.es/borme/..." }
  }
}</code></pre>

                    <h4>Comprobar la firma</h4>
                    <p><code>X-ApiEmpresas-Signature</code> tiene la forma <code>t=1790000000,v1=&lt;firma&gt;</code>. La firma es el HMAC-SHA256, en hexadecimal, de <code>t + "." + cuerpo</code> con tu <code>secret</code>. Rechaza el envío si no coincide o si <code>t</code> tiene más de 5 minutos.</p>
                    <pre><code class="language-php">[$t, $v1] = array_map(fn($p) => explode('=', $p, 2)[1], explode(',', $_SERVER['HTTP_X_APIEMPRESAS_SIGNATURE']));
$body = file_get_contents('php://input');
$ok = hash_equals(hash_hmac('sha256', $t . '.' . $body, $secret), $v1) && abs(time() - (int) $t) < 300;</code></pre>

                    <h4>Reintentos</h4>
                    <ul>
                        <li>Responde con un código 2xx en menos de 10 segundos. Si no, reintentamos con espera creciente: 1, 5 y 30 minutos, 2 y 6 horas. Los envíos salen cada 15 minutos, así que cada reintento llega en la primera pasada después de esa espera. Tras 6 intentos, el envío se descarta.</li>
                        <li>Usa <code>X-ApiEmpresas-Delivery</code> para no procesar dos veces el mismo envío.</li>
                        <li>Un webhook se desactiva tras 50 intentos fallidos seguidos, sumando todos sus envíos; un envío correcto pone la cuenta a cero. Vuelve a crearlo cuando tu servidor responda.</li>
                        <li>La URL debe ser HTTPS (puerto 443) y pública. No seguimos redirecciones.</li>
                    </ul>

                    <div class="endpoint-header">
                        <span class="http-badge post">POST</span>
                        <code>/webhooks/{id}/test</code>
                        <a href="<?= site_url('register?intent=api&plan=business&period=monthly&source=docs_webhooks') ?>" class="plan-badge business" style="text-decoration: none;" title="Ver el plan Business">Business</a>
                    </div>
                    <p>Envía al webhook un evento <code>test.ping</code> para comprobar que tu servidor lo recibe y valida la firma. Si tu servidor no responde con un 2xx, la respuesta es 502 <code>WEBHOOK_DELIVERY_FAILED</code> con el mismo <code>data</code> (código HTTP que devolvió tu servidor y el error): es un fallo de tu servidor, no de la API.</p>
                    <pre><code class="language-json">{
  "success": true,
  "data": {
    "delivered": true,
    "http_status": 200,
    "duration_ms": 143,
    "error": null,
    "delivery_id": "5d41402a-bc4b-2a76-b971-9d911017c592"
  }
}</code></pre>

                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/webhooks/{id}/deliveries</code>
                        <a href="<?= site_url('register?intent=api&plan=business&period=monthly&source=docs_webhooks') ?>" class="plan-badge business" style="text-decoration: none;" title="Ver el plan Business">Business</a>
                    </div>
                    <p>Últimos envíos del webhook, del más reciente al más antiguo: estado (<code>pending</code>, <code>processing</code>, <code>retry</code>, <code>delivered</code> o <code>dead</code>), intentos, código HTTP que devolvió tu servidor, duración y error. <code>next_attempt_at</code> solo aparece en los que están pendientes o en reintento. Parámetros opcionales: <code>status</code>, <code>page</code> y <code>limit</code> (1-100, por defecto 50). No gasta consultas. 404 <code>WEBHOOK_NOT_FOUND</code> si el webhook no es tuyo.</p>
                    <pre><code class="language-json">{
  "success": true,
  "data": [
    {
      "delivery_id": "5d41402a-bc4b-2a76-b971-9d911017c592",
      "event": "company.borme_act",
      "event_id": "borme_act:8812345",
      "status": "delivered",
      "attempts": 1,
      "http_status": 200,
      "duration_ms": 143,
      "error": null,
      "created_at": "2026-10-13 09:15:00",
      "last_attempt_at": "2026-10-13 09:15:02",
      "next_attempt_at": null,
      "completed_at": "2026-10-13 09:15:02"
    }
  ],
  "meta": { "webhook_id": 12, "total": 1, "page": 1, "limit": 50, "has_more": false }
}</code></pre>

                    <div class="endpoint-header">
                        <span class="http-badge delete">DELETE</span>
                        <code>/webhooks/{id}</code>
                        <a href="<?= site_url('register?intent=api&plan=business&period=monthly&source=docs_webhooks') ?>" class="plan-badge business" style="text-decoration: none;" title="Ver el plan Business">Business</a>
                    </div>
                    <p>Borra un webhook. Deja de recibir envíos desde ese momento. Si no existe, 404 <code>WEBHOOK_NOT_FOUND</code>.</p>
                    <pre><code class="language-json">{ "success": true, "message": "Webhook eliminado" }</code></pre>
                </section>

                <!-- CONTRATOS PUBLICOS -->
                <section class="docs-section" id="endpoint-contracts">
                    <h2>9. Contratos Públicos (Solo Business)</h2>
                    <p>Consulta adjudicaciones y contratos públicos asociados a empresas mediante CIF en nuestra base de datos. Permite conocer el historial de adjudicaciones, el órgano de contratación, el importe de adjudicación y el enlace oficial a la licitación.</p>

                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/companies/contracts</code>
                        <a href="<?= site_url('register?intent=api&plan=business&period=monthly&source=docs_contracts') ?>" class="plan-badge business" style="text-decoration: none;" title="Ver el plan Business">Business</a>
                    </div>

                    <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr><th style="width: 20%;">Parámetro</th><th style="width: 15%;">Tipo</th><th style="width: 15%;">Requerido</th><th>Descripción</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">cif</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td><span style="background: #fef2f2; color: #ef4444; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">Requerido</span></td><td style="color: #475569;">CIF de la empresa a consultar (ej: <code>A01001411</code>).</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">page</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td><span style="color: #94a3b8; font-size: 0.8rem;">Opcional</span></td><td style="color: #475569;">Número de página a consultar. Valor por defecto: <code>1</code>.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">limit</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td><span style="color: #94a3b8; font-size: 0.8rem;">Opcional</span></td><td style="color: #475569;">Cantidad de contratos por página. Por defecto: <code>20</code> (máximo permitido: <code>100</code>).</td></tr>
                        </tbody>
                    </table>

                    <h4>Respuesta de éxito (200 OK - Plan Business)</h4>
                    <pre><code class="language-json">{
  "success": true,
  "data": {
    "cif": "A01001411",
    "company_name": "RHEINMETALL EXPAL MUNITIONS SA",
    "summary": {
      "total_contracts": 32,
      "total_amount": "617746086.47",
      "currency": "EUR"
    },
    "contracts": [
      {
        "tender_id": "https://contrataciondelestado.es/sindicacion/licitacionesPerfilContratante/20344437",
        "title": "Suministro de 27.000 granadas de mortero de 81 mm...",
        "contracting_authority": "Jefatura de Asuntos Económicos del Mando de Apoyo Logístico",
        "award_date": "2026-08-25",
        "amount": "4395320.00",
        "currency": "EUR",
        "tender_url": "https://contrataciondelestado.es/wps/poc?uri=deeplink:detalle_licitacion&idEvl=B4EkDEGFvaA%2Bk2oCbDosIw%3D%3D"
      }
    ],
    "pagination": {
      "total": 32,
      "page": 1,
      "limit": 20,
      "total_pages": 2,
      "has_more": true
    }
  }
}</code></pre>

                    <h4>Respuesta restringida (403 Forbidden - Planes Free y Pro)</h4>
                    <pre><code class="language-json">{
  "success": false,
  "error": "PLAN_RESTRICTION",
  "message": "El acceso a contratos públicos requiere el plan Business.",
  "upsell_opportunities": {
    "mensaje": "Esta empresa tiene 14 contratos públicos adjudicados por 2.318.400 €. Con el plan Business ves cada contrato: órgano, título, fecha, importe y enlace a la licitación.",
    "upgrade_url": "https://apiempresas.es/billing?plan=business&source=api_403_contracts",
    "contratos_detectados": 14,
    "importe_total": 2318400.0,
    "currency": "EUR",
    "ultima_adjudicacion": "2026-06-12"
  }
}</code></pre>
                    <p style="font-size: 13px; color: #64748b;">El resumen de <code>upsell_opportunities</code> sale en un máximo de 25 respuestas 403 al día por cuenta; a partir de ahí solo llevan <code>mensaje</code> y <code>upgrade_url</code>.</p>
                </section>

                <!-- PERFIL DE RIESGO -->
                <section class="docs-section" id="endpoint-risk-profile">
                    <h2>10. Perfil de Riesgo Corporativo (Solo Business)</h2>
                    <p>Devuelve el perfil de riesgo corporativo, cumplimiento registral (depósito de cuentas anuales), estabilidad de gobernanza, volatilidad de capital y alertas mercantiles asociadas a una empresa mediante su CIF. Diseñado para automatizar la evaluación de solvencia y riesgo de impago en procesos de onboarding y compliance B2B.</p>

                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/companies/risk-profile</code>
                        <a href="<?= site_url('register?intent=api&plan=business&period=monthly&source=docs_risk_profile') ?>" class="plan-badge business" style="text-decoration: none;" title="Ver el plan Business">Business</a>
                    </div>

                    <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr><th style="width: 20%;">Parámetro</th><th style="width: 15%;">Tipo</th><th style="width: 15%;">Requerido</th><th>Descripción</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">cif</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td><span style="background: #fef2f2; color: #ef4444; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">Requerido</span></td><td style="color: #475569;">CIF de la empresa a auditar (ej: <code>A01001411</code> o <code>A15075062</code> en Sandbox).</td></tr>
                        </tbody>
                    </table>

                    <h4>Respuesta de éxito (200 OK - Plan Business)</h4>
                    <pre><code class="language-json">{
  "success": true,
  "data": {
    "cif": "A01001411",
    "company_name": "RHEINMETALL EXPAL MUNITIONS SA",
    "risk_score": 62,
    "risk_level": "ALTO",
    "confidence_score": 49,
    "data_quality_score": 70,
    "summary_message": "Atención: Constan indicadores de elevado riesgo financiero o corporativo.",
    "legal_state": "REGISTRY_CLOSURE_GENERICO",
    "data_sources": {
      "borme_status": "CHECKED_WITH_RECORDS",
      "accounts_status": "KNOWN_DELAYED",
      "official_status": "KNOWN"
    },
    "dimensions": {
      "legal_distress": 60,
      "filing_compliance": 0,
      "governance_volatility": 30,
      "capital_instability": 0,
      "structural_volatility": 0,
      "stabilizing_credit": 0
    },
    "canonical_events": [
      {
        "code": "LEGAL_STATE_REGISTRY_CLOSURE_GENERICO",
        "dimension": "legal_distress",
        "severity": "high",
        "description": "Consta publicación registral de cierre sin especificación de causa.",
        "event_date": "2026-08-24",
        "classification_confidence": "LOW"
      }
    ],
    "model_version": "2.0.0",
    "calculated_at": "2026-08-24T00:00:00Z"
  }
}</code></pre>

                    <h4>Respuesta restringida (403 Forbidden - Planes Free y Pro)</h4>
                    <pre><code class="language-json">{
  "success": false,
  "error": "PLAN_RESTRICTION",
  "message": "El acceso al perfil de riesgo corporativo requiere el plan Business.",
  "upsell_opportunities": {
    "mensaje": "Nivel de riesgo de esta empresa: MEDIO. Con el plan Business ves la puntuación, las seis dimensiones, el estado legal y cada evento del BORME que lo explica.",
    "upgrade_url": "https://apiempresas.es/billing?plan=business&source=api_403_risk",
    "perfil_disponible": true,
    "nivel_riesgo": "MEDIO"
  }
}</code></pre>
                    <p style="font-size: 13px; color: #64748b;"><code>nivel_riesgo</code> solo aparece en planes de pago; en el Free se indica únicamente si hay perfil calculado (<code>perfil_disponible</code>). Máximo 25 respuestas con este resumen al día por cuenta.</p>

                    <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">risk_score</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer (0-100)</span></td><td style="color: #475569;">Puntuación algorítmica de riesgo consolidada (valores más altos indican mayor riesgo de impago/concursal).</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">risk_level</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Semáforo cualitativo de riesgo: <code>BAJO</code>, <code>MEDIO</code>, <code>ALTO</code>, <code>CRITICO</code>.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">dimensions</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">object</span></td><td style="color: #475569;">Desglose por áreas de análisis: legal distress, depósito de cuentas, volatilidad de gobernanza, estabilidad de capital y crédito mitigador.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">canonical_events[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td><td style="color: #475569;">Lista cronológica de eventos mercantiles clasificados con su severidad e impacto en el cálculo.</td></tr>
                        </tbody>
                    </table>
                </section>

                <!-- USAGE -->
                <section class="docs-section" id="endpoint-usage">
                    <h2>11. Estadísticas de Consumo</h2>
                    <p>Obtén el recuento de peticiones del mes actual y el historial de empresas consultadas asociado a tu API Key. No gasta consultas.</p>
                    
                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/usage</code>
                    </div>

                    <h4>Respuesta de éxito (200 OK)</h4>
                    <pre><code class="language-json">{
  "success": true,
  "data": {
    "stats": {
      "monthly_queries": 150,
      "total_queries": 1250,
      "monthly_quota": 3000,
      "remaining_calls": 2850,
      "wallet_balance": 0,
      "plan_name": "Pro",
      "plan_slug": "pro",
      "quota_period": "monthly"
    },
    "history": [
      {
        "cif": "A46103834",
        "name": "MERCADONA SA",
        "province": "VALENCIA",
        "query_count": 3,
        "last_query": "2026-10-12 17:40:02",
        "found": true
      }
    ]
  }
}</code></pre>
                    <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">stats.monthly_queries</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Consultas cobradas en el periodo del cupo: el mes en curso en Pro y Business; desde el 28/05/2026 en Free (o desde el alta, si es posterior), incluido lo gastado con un plan de pago anterior.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">stats.monthly_quota</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Consultas incluidas en tu plan (al mes, o 100 en total en Free).</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">stats.remaining_calls</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Consultas del plan que te quedan antes de usar saldo del monedero.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">stats.quota_period</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;"><code>monthly</code> (se renueva cada mes) o <code>lifetime</code> (Free: no se renueva).</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">stats.quota_resets_at</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string|null</span></td><td style="color: #475569;">Cuándo se renueva el cupo (ISO 8601). <code>null</code> en Free.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">stats.checkout_url</code> / <code style="background: transparent; color: #2563eb; font-weight: 600;">stats.recharge_url</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string|null</span></td><td style="color: #475569;">Enlace para pasar al plan siguiente (<code>null</code> si no hay) y para comprar un bono.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">stats.total_queries</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Consultas del plan cobradas desde el alta (no incluye los créditos del bono).</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">stats.wallet_balance</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Créditos que te quedan en el bono.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">history[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td><td style="color: #475569;">Las 20 últimas empresas, por CIF, que has consultado en producción con respuesta 200: la ficha de cada una (recortada en Free), <code>query_count</code> (veces), <code>last_query</code> (última vez) y <code>found</code>.</td></tr>
                        </tbody>
                    </table>
                </section>

                <!-- CATALOGO (13-10-2026) -->
                <section class="docs-section" id="endpoint-catalog">
                    <h2>11.1 Catálogo de valores</h2>
                    <p>Valores válidos de los filtros: <code>province</code> (radar y segmentos), <code>size_band</code> y <code>status</code> (segmentos), <code>range</code>, <code>priority</code> y <code>main_act_type</code> (radar), la prioridad de la puntuación, los eventos de webhook y los tipos de evento de la vigilancia. Todos los planes. <strong>No gasta consultas.</strong> También en el Sandbox.</p>

                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/catalog</code>
                    </div>
                    <p>No hay lista de CNAE: usa prefijos de la CNAE-2009 del INE (1 a 4 dígitos) y comprueba cuántas empresas hay con <code>/companies/filter?cnae=62&amp;count_only=true</code>, que es gratis.</p>
                    <pre><code class="language-json">{
  "success": true,
  "data": {
    "province": ["A CORUÑA", "ÁLAVA", "ALBACETE", "…", "ZARAGOZA"],
    "cnae": {
      "format": "Prefijos de la CNAE-2009 (INE) de 1 a 4 dígitos, separados por comas: 62 o 4711,4719.",
      "count": "/companies/filter?cnae=62&count_only=true (gratis) dice cuántas empresas hay."
    },
    "size_band": [{ "value": "NO_REVENUE", "label": "Sin ventas" }, { "value": "LT_500K", "label": "Menos de 0,5 M€" }, { "value": "500K_1M", "label": "Entre 0,5 y 1 M€" }, { "value": "GT_1M", "label": "Más de 1 M€" }],
    "status": ["active", "active_or_unknown", "any"],
    "radar": {
      "range": ["hoy", "7", "30"],
      "range_note": "'hoy' o cualquier número de días atrás",
      "priority": ["muy_alta", "alta", "media", "baja", "muy_baja"],
      "main_act_type": ["Constitución", "Nombramientos", "Ampliación de Capital", "…"]
    },
    "score_priority": ["muy_alta", "alta", "media", "baja", "muy_baja"],
    "webhook_events": ["test.ping", "company.borme_act", "company.status_changed", "company.risk_level_changed", "watchlist.*"],
    "watchlist_event_types": ["borme_act", "status_change", "risk_level_change"]
  },
  "meta": { "updated_at": "2026-10-13", "cost": 0 }
}</code></pre>
                </section>


                <!-- ERRORES -->
                <section class="docs-section" id="errores">
                    <h2>12. Gestión de Errores (RFC 7807)</h2>
                    <p>Las respuestas de error de la API incluyen los campos legacy (<code>success</code>, <code>error</code>, <code>message</code>) y además implementan el estándar moderno <strong>RFC 7807 (Problem Details)</strong>. Si hay un error, el código de estado HTTP será diferente a 200 y recibirás un JSON detallado.</p>
                    <pre><code class="language-json">{
  "success": false,
  "error": "COMPANY_NOT_FOUND",
  "message": "Empresa no encontrada.",
  "type": "https://apiempresas.es/docs/errors/company_not_found",
  "title": "COMPANY_NOT_FOUND",
  "status": 404,
  "detail": "Empresa no encontrada.",
  "instance": "req_8f73b1a2c9"
}</code></pre>
                    <p>El campo <code>instance</code> es único para cada petición (coincide con la cabecera <code>X-Request-Id</code>) y resulta muy útil si necesitas reportar un problema a soporte.</p>
                    <p>Para programar contra los errores usa el campo <code>code</code>: es un identificador estable. Estos son los que puede devolver la API:</p>
                    <table class="docs-table" style="margin-bottom: 24px;">
                        <thead>
                            <tr>
                                <th>HTTP</th>
                                <th><code>code</code></th>
                                <th>Qué significa</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr><td>401</td><td><code>API_KEY_MISSING</code></td><td>No se ha enviado la cabecera <code>X-API-KEY</code>.</td></tr>
                            <tr><td>401</td><td><code>API_KEY_INVALID</code></td><td>La API Key no existe.</td></tr>
                            <tr><td>403</td><td><code>API_KEY_INACTIVE</code></td><td>La API Key o la cuenta están desactivadas.</td></tr>
                            <tr><td>403</td><td><code>COUNTRY_NOT_ALLOWED</code></td><td>La petición sale de un país no permitido para tu API Key.</td></tr>
                            <tr><td>429</td><td><code>TOO_MANY_REQUESTS</code></td><td>Demasiadas peticiones por segundo. Espera lo que indique <code>Retry-After</code> y reintenta.</td></tr>
                            <tr><td>429</td><td><code>QUOTA_EXCEEDED</code></td><td>Has agotado las consultas de tu plan y no tienes saldo. Reintentar no sirve: recarga saldo o cambia de plan. En planes de pago, <code>quota_resets_at</code> y la cabecera <code>X-Quota-Reset</code> indican cuándo se renueva el cupo; en Free las 100 consultas no se renuevan (<code>quota_resets_at</code> es <code>null</code>).</td></tr>
                            <tr><td>429</td><td><code>IP_LIMIT_EXCEEDED</code></td><td>Límite de seguridad por IP del plan Free.</td></tr>
                            <tr><td>429</td><td><code>PREVIEW_LIMIT_EXCEEDED</code></td><td>Has pasado el tope de 25 vistas previas gratuitas al día (ver <a href="#consumo">Planes y consumo</a>). <code>Retry-After</code> indica cuándo vuelve a haber.</td></tr>
                            <tr><td>400</td><td><code>VALIDATION_ERROR</code></td><td>Falta un parámetro obligatorio o no es válido.</td></tr>
                            <tr><td>400</td><td><code>INVALID_CIF_FORMAT</code></td><td>El CIF no tiene un formato válido.</td></tr>
                            <tr><td>400</td><td><code>FAKE_CIF_NOT_ALLOWED</code></td><td>CIF de prueba (por ejemplo <code>B12345678</code> o <code>B00000000</code>) en producción. Para probar, usa el Sandbox.</td></tr>
                            <tr><td>400</td><td><code>AUTONOMO_NOT_SUPPORTED</code></td><td>DNI o NIE: solo damos datos de sociedades.</td></tr>
                            <tr><td>403</td><td><code>PLAN_RESTRICTION</code></td><td>Tu plan no incluye esta ruta. <code>required_plan</code> dice cuál hace falta y <code>upsell_opportunities.checkout_url</code> lleva a cambiar.</td></tr>
                            <tr><td>403</td><td><code>UPGRADE_REQUIRED</code></td><td>Lo mismo en <code>/companies/borme</code>.</td></tr>
                            <tr><td>403</td><td><code>TEST_MODE_RESTRICTION</code></td><td>En el Sandbox, un CIF que no es de prueba.</td></tr>
                            <tr><td>404</td><td><code>COMPANY_NOT_FOUND</code></td><td>No hay ninguna empresa con ese CIF o nombre.</td></tr>
                            <tr><td>404</td><td><code>RISK_PROFILE_NOT_AVAILABLE</code></td><td>La empresa no tiene perfil de riesgo calculado.</td></tr>
                            <tr><td>404</td><td><code>NOT_WATCHING</code></td><td>Quitas de la vigilancia una empresa que no estaba.</td></tr>
                            <tr><td>404</td><td><code>WEBHOOK_NOT_FOUND</code></td><td>No tienes ningún webhook con ese id.</td></tr>
                            <tr><td>502</td><td><code>WEBHOOK_DELIVERY_FAILED</code></td><td>En <code>/webhooks/{id}/test</code>: tu servidor no aceptó el envío de prueba.</td></tr>
                            <tr><td>400</td><td><code>INVALID_EVENT</code></td><td>Al crear un webhook, un evento que no existe.</td></tr>
                            <tr><td>500 / 503</td><td><code>SERVER_ERROR</code> / <code>SERVICE_UNAVAILABLE</code></td><td>Error nuestro. Reintenta más tarde y, si sigue, escríbenos con el <code>instance</code>.</td></tr>
                        </tbody>
                    </table>
                </section>

                <!-- THROTTLING -->
                <section class="docs-section" id="throttling">
                    <h2>13. Límites de Peticiones (Rate Limiting)</h2>
                    <p>Para proteger la estabilidad de la API y ayudarte a monitorizar tu consumo, devolvemos <strong>cabeceras HTTP estándar</strong> en cada respuesta que te informan en tiempo real de tu cuota mensual y tu límite de velocidad por segundo.</p>
                    
                    <h4 style="margin-top: 24px; margin-bottom: 12px; font-size: 1.1rem; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        Cabeceras HTTP de Respuesta
                    </h4>
                    <table class="docs-table" style="margin-bottom: 24px;">
                        <thead>
                            <tr>
                                <th>Cabecera HTTP</th>
                                <th>Descripción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>X-RateLimit-Limit</code></td>
                                <td>Peticiones máximas permitidas por segundo (2 en Free, 20 en Pago).</td>
                            </tr>
                            <tr>
                                <td><code>X-RateLimit-Remaining</code></td>
                                <td>Peticiones por segundo restantes en la ventana de tiempo actual.</td>
                            </tr>
                            <tr>
                                <td><code>X-RateLimit-Reset</code></td>
                                <td>Timestamp Unix indicando cuándo se reinicia el límite de velocidad.</td>
                            </tr>
                            <tr>
                                <td><code>X-Quota-Limit</code></td>
                                <td>Consultas incluidas en tu plan: al mes en Pro y Business; en Free, 100 en total (no se renuevan).</td>
                            </tr>
                            <tr>
                                <td><code>X-Quota-Remaining</code></td>
                                <td>Consultas de tu plan que te quedan, ya descontada la petición actual, antes de empezar a usar saldo del monedero.</td>
                            </tr>
                            <tr>
                                <td><code>X-Quota-Reset</code></td>
                                <td>Solo en el error de cupo agotado de los planes de pago: timestamp Unix de cuándo se renueva el cupo mensual.</td>
                            </tr>
                            <tr>
                                <td><code>X-Request-Id</code></td>
                                <td>Identificador único de trazabilidad de la petición. Útil para darte soporte técnico rápido.</td>
                            </tr>
                        </tbody>
                    </table>

                    <p><strong>Aviso de cupo en la respuesta.</strong> En cualquier respuesta correcta que gasta consultas de tu plan, cuando te queda el 20 % del cupo o menos, la respuesta añade un objeto <code>notice</code> junto a <code>data</code>: <code>type</code> (<code>quota_warning</code>), <code>message</code>, <code>quota_remaining</code>, <code>quota_limit</code>, <code>quota_resets_at</code> (null en Free), <code>checkout_url</code> y <code>recharge_url</code>. Por debajo de ese umbral no aparece.</p>

                    <p>Si superas el límite por segundo, recibirás un error <code>429 Too Many Requests</code> junto con la cabecera <code>Retry-After: 1</code> indicando que esperes 1 segundo (<code>code</code>: <code>TOO_MANY_REQUESTS</code>). El cupo agotado también responde 429, pero con <code>code</code> <code>QUOTA_EXCEEDED</code> y sin <code>Retry-After</code>: no reintentes en ese caso. Si necesitas procesar muchas empresas de golpe, te recomendamos utilizar el endpoint <a href="#batch">Batch</a>.</p>
                </section>

                <!-- PAGINACION -->
                <section class="docs-section" id="paginacion">
                    <h2>14. Paginación por Cursores</h2>
                    <p>En el endpoint de búsqueda múltiple (<code>/api/v1/companies/search?multiple=true</code>), puedes utilizar el parámetro <code>page</code> tradicional o el parámetro <code>cursor</code> para paginar los resultados de forma más segura.</p>
                    <p>Si la respuesta contiene más páginas, el objeto <code>meta</code> incluirá un <code>next_cursor</code>. Solo tienes que enviar ese valor exacto en tu siguiente petición para obtener la siguiente página de resultados de manera automática:</p>
                    <pre><code class="language-json">"meta": {
  "page": 1,
  "limit": 20,
  "has_more": true,
  "next_cursor": "eyJwIjoyfQ=="
}</code></pre>
                    <p><code>/companies/filter</code> también pagina con <code>meta.next_cursor</code>. <code>/watchlist</code> y <code>/watchlist/events</code> paginan con <code>page</code> y <code>limit</code>: pide la página siguiente mientras <code>meta.has_more</code> sea <code>true</code>.</p>
                </section>

                <!-- SDKS -->
                <section class="docs-section" id="sdks">
                    <h2>15. SDKs Oficiales (Publicados)</h2>
                    <p>Agiliza la integración en tus aplicaciones utilizando nuestras librerías oficiales con tipado estático y manejo de errores nativo. Estos SDKs están listos para usarse en entornos de producción.</p>
                    
                    <div style="display: flex; flex-direction: column; gap: 20px; margin-top: 24px;">
                        
                        <!-- PHP SDK -->
                        <div style="background: #ffffff; padding: 25px; border-radius: 12px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                                <h3 style="display:flex; align-items:center; gap:10px; font-size:1.2rem; color:#0f172a; margin:0;">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#4f5b93" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                                    PHP SDK
                                </h3>
                                <span style="background: #e0e7ff; color: #4338ca; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">Oficial</span>
                            </div>
                            <p style="color:#475569; font-size:0.95rem; margin-bottom: 10px;">Instalación vía Composer:</p>
                            <pre style="background: #0f172a; padding: 10px 15px; border-radius: 8px; margin: 0;"><code class="language-bash" style="color: #e2e8f0;">composer require apiempresas/apiempresas-php</code></pre>
                            <p style="color:#475569; font-size:0.95rem; margin-top:15px; margin-bottom: 10px;">Ejemplo de uso:</p>
                            <pre style="background: #0f172a; padding: 15px; border-radius: 8px; margin: 0;"><code class="language-php" style="color: #e2e8f0;">require_once 'vendor/autoload.php';

use ApiEmpresas\ApiEmpresas;

$api = new ApiEmpresas('tu_api_key');
$empresa = $api->companies->get('A15075062');
echo $empresa['name'];</code></pre>
                        </div>

                        <!-- Node.js SDK -->
                        <div style="background: #ffffff; padding: 25px; border-radius: 12px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                                <h3 style="display:flex; align-items:center; gap:10px; font-size:1.2rem; color:#0f172a; margin:0;">
                                    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/nodejs/nodejs-original.svg" width="28" height="28" alt="Node.js" />
                                    Node.js / TS
                                </h3>
                                <span style="background: #e0e7ff; color: #4338ca; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">Oficial</span>
                            </div>
                            <p style="color:#475569; font-size:0.95rem; margin-bottom: 10px;">Instalación vía NPM:</p>
                            <pre style="background: #0f172a; padding: 10px 15px; border-radius: 8px; margin: 0;"><code class="language-bash" style="color: #e2e8f0;">npm install apiempresas</code></pre>
                            <p style="color:#475569; font-size:0.95rem; margin-top:15px; margin-bottom: 10px;">Ejemplo de uso con TypeScript:</p>
                            <pre style="background: #0f172a; padding: 15px; border-radius: 8px; margin: 0;"><code class="language-typescript" style="color: #e2e8f0;">import { ApiEmpresas } from 'apiempresas';

const api = new ApiEmpresas({ apiKey: 'tu_api_key' });
const empresa = await api.companies.get('A15075062');
console.log(empresa.name);</code></pre>
                        </div>

                        <!-- Python SDK -->
                        <div style="background: #ffffff; padding: 25px; border-radius: 12px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                                <h3 style="display:flex; align-items:center; gap:10px; font-size:1.2rem; color:#0f172a; margin:0;">
                                    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/python/python-original.svg" width="28" height="28" alt="Python" />
                                    Python SDK
                                </h3>
                                <span style="background: #e0e7ff; color: #4338ca; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">Oficial</span>
                            </div>
                            <p style="color:#475569; font-size:0.95rem; margin-bottom: 10px;">Instalación vía PIP:</p>
                            <pre style="background: #0f172a; padding: 10px 15px; border-radius: 8px; margin: 0;"><code class="language-bash" style="color: #e2e8f0;">pip install apiempresas</code></pre>
                            <p style="color:#475569; font-size:0.95rem; margin-top:15px; margin-bottom: 10px;">Ejemplo de uso:</p>
                            <pre style="background: #0f172a; padding: 15px; border-radius: 8px; margin: 0;"><code class="language-python" style="color: #e2e8f0;">from apiempresas import ApiEmpresas

api = ApiEmpresas('tu_api_key')
empresa = api.companies.get('A15075062')
print(empresa['name'])</code></pre>
                        </div>
                    </div>

                    <h3 style="margin-top: 28px;">Novedades de la versión 1.2.0</h3>
                    <p>Los tres SDK incluyen la <a href="#endpoint-verify">verificación KYB</a> (<code>companies.verify</code>), la <a href="#endpoint-watchlist">vigilancia</a> (<code>watchlist.add / list / remove / events</code>), los <a href="#endpoint-webhooks">webhooks</a> (<code>webhooks.create / list / remove / test</code>), los filtros nuevos del Radar y una función para comprobar la firma de los webhooks. Actualiza con <code>npm install apiempresas@latest</code>, <code>pip install -U apiempresas</code> o <code>composer update apiempresas/apiempresas-php</code>.</p>
                    <pre><code class="language-typescript">import { ApiEmpresas, constructWebhookEvent } from 'apiempresas';

const api = new ApiEmpresas({ apiKey: 'tu_api_key' });

const v = await api.companies.verify('A46103834', { name: 'Mercadona SA', vat: true });
console.log(v.decision_hint); // pass | review | fail

await api.watchlist.add(['A46103834']);
const { data: cambios } = await api.watchlist.events({ since: '2026-09-01' });

// En tu servidor (cuerpo en bruto):
const evento = constructWebhookEvent(rawBody, req.headers['x-apiempresas-signature'], secret);</code></pre>
                    <p>En PHP: <code>ApiEmpresas\Resources\Webhooks::constructEvent($raw, $firma, $secret)</code>. En Python: <code>from apiempresas import construct_webhook_event</code>.</p>
                </section>

                <!-- EXAMPLES -->
                <section class="docs-section" id="examples">
                    <h2>16. Ejemplos de Código Manual</h2>
                    <p>Implementa la conexión en minutos con estos ejemplos listos para usar.</p>

                    <div class="code-tabs">
                        <h3 style="display:flex; align-items:center; gap:10px;">
                            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/php/php-original.svg" width="24" height="24" alt="PHP" />
                            PHP (cURL)
                        </h3>
                        <pre><code class="language-php">&lt;?php
$apiKey = 'TU_API_KEY';
$cif = 'A15075062';
$url = 'https://apiempresas.es/api/v1/companies?cif=' . $cif;

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'X-API-KEY: ' . $apiKey,
    'Accept: application/json'
]);

$response = curl_exec($ch);
$data = json_decode($response, true);
print_r($data);
?&gt;</code></pre>

                        <h3 style="display:flex; align-items:center; gap:10px;">
                            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/laravel/laravel-original.svg" width="24" height="24" alt="Laravel" />
                            Laravel (HTTP Client)
                        </h3>
                        <pre><code class="language-php">use Illuminate\Support\Facades\Http;

$response = Http::withHeaders([
    'X-API-KEY' => 'TU_API_KEY'
])->get('https://apiempresas.es/api/v1/companies', [
    'cif' => 'A15075062'
]);

if ($response->successful()) {
    $data = $response->json();
}</code></pre>

                        <h3 style="display:flex; align-items:center; gap:10px;">
                            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/codeigniter/codeigniter-plain.svg" width="24" height="24" alt="CodeIgniter" />
                            CodeIgniter 4
                        </h3>
                        <pre><code class="language-php">$client = \Config\Services::curlrequest();

$response = $client->request('GET', 'https://apiempresas.es/api/v1/companies', [
    'headers' => [
        'X-API-KEY' => 'TU_API_KEY',
        'Accept'    => 'application/json'
    ],
    'query' => ['cif' => 'A15075062']
]);

$data = json_decode($response->getBody(), true);</code></pre>

                        <h3 style="display:flex; align-items:center; gap:10px;">
                            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/nodejs/nodejs-original.svg" width="24" height="24" alt="Node.js" />
                            Node.js (Fetch)
                        </h3>
                        <pre><code class="language-js">const fetch = require('node-fetch');

const getCompany = async (cif) => {
  const response = await fetch('https://apiempresas.es/api/v1/companies?cif=' + cif, {
    headers: { 'X-API-KEY': 'TU_API_KEY' }
  });
  const data = await response.json();
  console.log(data);
};</code></pre>

                        <h3 style="display:flex; align-items:center; gap:10px;">
                            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/python/python-original.svg" width="24" height="24" alt="Python" />
                            Python (Requests)
                        </h3>
                        <pre><code class="language-python">import requests

url = "https://apiempresas.es/api/v1/companies"
params = {"cif": "A15075062"}
headers = {"X-API-KEY": "TU_API_KEY"}

response = requests.get(url, params=params, headers=headers)
print(response.json())</code></pre>

                        <h3 style="display:flex; align-items:center; gap:10px;">
                            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/javascript/javascript-original.svg" width="24" height="24" alt="JavaScript" />
                            JavaScript (Fetch Browser)
                        </h3>
                        <pre><code class="language-js">fetch('https://apiempresas.es/api/v1/companies?cif=A15075062', {
  headers: {
    'X-API-KEY': 'TU_API_KEY',
    'Accept': 'application/json'
  }
})
.then(res => res.json())
.then(data => console.log(data));</code></pre>
                    </div>
                </section>

                <!-- POSTMAN -->
                <section class="docs-section" id="postman">
                    <h2>17. Postman Collection</h2>
                    <p>Si prefieres probar la API directamente en Postman, puedes descargarte nuestra colección oficial e importarla con un clic.</p>
                    
                    <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px dashed #cbd5e1; text-align: center; margin-top: 20px;">
                        <img src="https://www.postman.com/assets/postman-logo-stacked.svg" alt="Postman" style="height: 40px; margin-bottom: 15px;">
                        <p style="margin-bottom: 20px; color: #475569;">Incluye todos los endpoints configurados, ejemplos de respuestas y variables de entorno.</p>
                        <a href="<?= base_url('public/docs/apiempresas_postman.json') ?>" download class="btn primary" style="display: inline-flex; align-items: center; gap: 8px;">
                            <span>📥 Descargar Colección Postman</span>
                        </a>
                    </div>
                </section>



                    <div style="margin-top: 80px; text-align: center; background: linear-gradient(135deg, #0F172A 0%, #1E3A8A 100%); color: white; padding: 60px 40px; border-radius: 32px; box-shadow: 0 25px 50px -12px rgba(30, 58, 138, 0.25);">
                        <h2 style="color: white; font-size: 2.3rem; font-weight: 900; margin-bottom: 16px; letter-spacing: -0.02em;">🚀 ¿Listo para empezar?</h2>
                        <p style="font-size: 1.25rem; color: rgba(255,255,255,0.7); margin-bottom: 32px; font-weight: 500;">Obtén tu API Key en segundos y empieza a integrar datos reales en tus aplicaciones.</p>
                        <a href="<?= site_url('dashboard') ?>" class="btn-radar-strong" style="max-width: 400px; margin: 0 auto; padding: 20px 40px;">Ir al Panel de Control</a>
                    </div>
                </section>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<style>
    .http-badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; margin-right: 8px; color: white; }
    .http-badge.get { background: #61affe; }
    .http-badge.post { background: #49cc90; }
    .http-badge.delete { background: #f93e3e; }
    .endpoint-header { display: flex; align-items: center; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px; gap: 10px; }
    .plan-badge { font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 2px 8px; border-radius: 99px; margin-left: auto; }
    .plan-badge.pro { background: #fef3c7; color: #92400e; border: 1px solid #fcd34d; }
    .plan-badge.business { background: #dcfce7; color: #166534; border: 1px solid #86efac; }
    .docs-table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 14px; }
    .docs-table th, .docs-table td { text-align: left; padding: 12px; border-bottom: 1px solid #e2e8f0; }
    .docs-table th { background: #f8fafc; color: #64748b; }
    .api-info-card { background: #eff6ff; border-left: 4px solid #3b82f6; padding: 15px; margin: 15px 0; border-radius: 0 8px 8px 0; }
    .code-tabs h3 { font-size: 16px; margin-top: 25px; color: #1e293b; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px; }
    code.inline { background: #f1f5f9; padding: 2px 5px; border-radius: 4px; color: #e11d48; font-family: monospace; }

    /* --- UX Conversion Styles --- */
    .use-case-box {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 40px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }
    .use-case-box h3 { margin-bottom: 24px; color: #1e293b; font-size: 1.25rem; font-weight: 800; }
    .use-case-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
    .use-case-card { padding: 24px; border-radius: 18px; background: white; border: 1px solid #f1f5f9; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); }
    .use-case-card:hover { transform: translateY(-4px); border-color: #cbd5e1; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1); }
    .use-case-card h4 { margin-bottom: 12px; color: #334155; display: flex; align-items: center; gap: 8px; font-weight: 700; }
    .use-case-card p { font-size: 0.9rem; color: #64748b; margin-bottom: 16px; line-height: 1.5; }
    .use-case-card .btn-link { color: #2563eb; font-weight: 700; text-decoration: none; font-size: 0.9rem; }

    .radar-upsell {
        background: linear-gradient(135deg, #133a82 0%, #1e40af 100%);
        color: white;
        padding: 24px;
        border-radius: 16px;
        margin: 32px 0;
        display: flex;
        align-items: center;
        gap: 24px;
        position: relative;
        overflow: hidden;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .radar-upsell-icon { font-size: 32px; flex-shrink: 0; }
    .radar-upsell-body { flex: 1; }
    .radar-upsell-body h4 { color: white; margin-bottom: 8px; font-size: 1.1rem; font-weight: 700; }
    .radar-upsell-body p { color: rgba(255,255,255,0.9); font-size: 0.95rem; margin: 0; line-height: 1.5; }
    .btn-radar {
        background: #10b981;
        color: white !important;
        padding: 12px 20px;
        border-radius: 10px;
        text-decoration: none !important;
        font-weight: 800;
        font-size: 0.9rem;
        white-space: nowrap;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    .btn-radar:hover { background: #059669; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4); }

    .money-block {
        background: #fffbeb;
        border: 1px solid #fef3c7;
        border-left: 5px solid #f59e0b;
        padding: 24px;
        border-radius: 12px;
        margin: 32px 0;
    }
    .money-block h4 { color: #92400e; margin-bottom: 8px; font-weight: 800; font-size: 1.1rem; }
    .money-block p { color: #b45309; margin-bottom: 0; font-size: 0.95rem; }
    .money-result {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #fef3c7;
        color: #92400e;
        padding: 10px 20px;
        border-radius: 99px;
        font-weight: 800;
        margin-top: 16px;
        border: 1px solid #fcd34d;
        font-size: 1rem;
    }

    .conditional-banner {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        padding: 24px;
        border-radius: 16px;
        margin-bottom: 40px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 24px;
        box-shadow: 0 4px 12px rgba(22, 163, 74, 0.05);
    }
    .conditional-banner-text h4 { color: #166534; margin-bottom: 6px; font-weight: 800; font-size: 1.15rem; }
    .conditional-banner-text p { color: #15803d; margin: 0; font-size: 1rem; opacity: 0.9; }

    @media (max-width: 768px) {
        .radar-upsell { flex-direction: column; text-align: center; padding: 32px 24px; }
        .conditional-banner { flex-direction: column; text-align: center; }
        .use-case-grid { grid-template-columns: 1fr !important; }
    }

    /* --- CRO Optimization Styles --- */
    .radar-cta-strong {
        background: #fff;
        border: 2px solid #2563EB;
        border-radius: 20px;
        padding: 32px;
        margin: 40px 0;
        position: relative;
        box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.1), 0 10px 10px -5px rgba(37, 99, 235, 0.04);
        text-align: left;
    }
    .radar-cta-header { display: flex; align-items: center; gap: 12px; margin-bottom: 16px; }
    .radar-cta-header h4 { margin: 0; color: #0f172a; font-size: 1.4rem; font-weight: 900; text-transform: uppercase; line-height: 1.2; }
    .pulse-icon { font-size: 24px; animation: pulse 2s infinite; }
    @keyframes pulse { 0% { transform: scale(1); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }
    .radar-features { list-style: none; padding: 0; margin: 24px 0; display: flex; flex-wrap: wrap; gap: 12px; }
    .radar-features li { background: #f8fafc; padding: 8px 16px; border-radius: 99px; font-weight: 800; color: #334155; font-size: 0.85rem; border: 1px solid #e2e8f0; }
    .btn-radar-strong {
        display: block;
        background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
        color: white !important;
        padding: 18px 32px;
        border-radius: 14px;
        text-decoration: none !important;
        font-weight: 900;
        font-size: 1.2rem;
        text-align: center;
        transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.4);
    }
    .btn-radar-strong:hover { background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%); transform: translateY(-3px) scale(1.02); box-shadow: 0 20px 25px -5px rgba(37, 99, 235, 0.5); }

    .money-block-v2 {
        background: #f8fafc;
        border: 2px solid #e2e8f0;
        border-radius: 20px;
        padding: 32px;
        margin: 40px 0;
        text-align: left;
    }
    .money-block-v2 h4 { color: #0f172a; font-weight: 900; font-size: 1.6rem; margin-bottom: 12px; }
    .money-block-v2 p { color: #475569; font-size: 1.1rem; line-height: 1.5; }
    .money-results-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin: 24px 0; }
    .money-result-item { background: #ffffff; padding: 20px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
    .money-result-item .label { display: block; color: #64748b; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; margin-bottom: 6px; letter-spacing: 0.05em; }
    .money-result-item .value { display: block; color: #16a34a; font-size: 1.3rem; font-weight: 900; }
    .btn-money {
        display: inline-block;
        background: #0f172a;
        color: white !important;
        padding: 14px 28px;
        border-radius: 12px;
        text-decoration: none !important;
        font-weight: 800;
        transition: all 0.2s;
    }
    .btn-money:hover { background: #1e293b; transform: translateX(4px); }

    .binary-choice {
        background: #ffffff;
        border: 2px solid #f1f5f9;
        border-radius: 32px;
        padding: 48px;
        margin: 64px 0;
        text-align: center;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
    }
    .choice-title { font-size: 1.6rem; font-weight: 900; color: #0f172a; margin-bottom: 40px; }
    .choice-container { display: flex; align-items: stretch; gap: 24px; }
    .choice-item { flex: 1; padding: 32px; border-radius: 24px; transition: all 0.3s; display: flex; flex-direction: column; justify-content: center; align-items: center; }
    .choice-item.manual { border: 2px solid #f1f5f9; }
    .choice-item.manual:hover { border-color: #e2e8f0; background: #fafafa; }
    .choice-item.auto { background: #f0fdf4; border: 2px solid #bbf7d0; }
    .choice-item.auto:hover { border-color: #86efac; transform: translateY(-4px); }
    .choice-icon { font-size: 48px; margin-bottom: 20px; }
    .choice-item p { font-weight: 700; color: #334155; margin-bottom: 24px; font-size: 1.1rem; }
    .choice-divider { font-weight: 900; color: #cbd5e1; font-style: italic; font-size: 1.2rem; display: flex; align-items: center; }
    .btn-secondary { color: #64748b; text-decoration: underline !important; font-weight: 800; font-size: 0.95rem; }

    @media (max-width: 992px) {
        .choice-container { flex-direction: column; }
        .choice-divider { padding: 12px 0; justify-content: center; }
    }

    /* --- FOMO & Loss Aversion --- */
    .radar-fomo-badge {
        background: #fee2e2;
        color: #991b1b;
        padding: 10px 16px;
        border-radius: 8px;
        font-size: 0.9rem;
        font-weight: 800;
        margin-bottom: 12px;
        display: inline-block;
        border: 1px solid #fecaca;
    }
    .radar-loss-message {
        color: #b91c1c;
        font-weight: 900;
        font-size: 1rem;
        margin-bottom: 20px;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
</style>

    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#api_to_radar_cta').on('click', function() {
                $.post('<?= site_url("api/tracking/event") ?>', {
                    event_type: 'api_to_radar_click',
                    source: 'api_docs'
                });
            });

            // Scrollspy para el menú lateral
            const sections = document.querySelectorAll('.docs-section');
            const navLinks = document.querySelectorAll('.docs-nav a[href^="#"]');

            const observerOptions = {
                root: null,
                rootMargin: '-20% 0px -60% 0px',
                threshold: 0
            };

            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const id = entry.target.getAttribute('id');
                        navLinks.forEach(link => link.classList.remove('active'));
                        const activeLink = document.querySelector(`.docs-nav a[href="#${id}"]`);
                        if (activeLink) activeLink.classList.add('active');
                    }
                });
            }, observerOptions);

            sections.forEach(section => {
                observer.observe(section);
            });

            // --- Copy to Clipboard Buttons for <pre> blocks ---
            const preTags = document.querySelectorAll('pre');
            preTags.forEach(pre => {
                // Ignore pre tags that are just single-line inline styles if any
                if(pre.innerText.trim() === '') return;

                const copyBtn = document.createElement('button');
                copyBtn.className = 'copy-code-btn';
                copyBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg> Copiar';
                
                // Keep default styling if pre doesn't have position relative/absolute
                const computedStyle = window.getComputedStyle(pre);
                if (computedStyle.position === 'static') {
                    pre.style.position = 'relative';
                }

                copyBtn.addEventListener('click', () => {
                    const code = pre.querySelector('code');
                    const textToCopy = code ? code.innerText : pre.innerText;
                    
                    navigator.clipboard.writeText(textToCopy).then(() => {
                        const originalHtml = copyBtn.innerHTML;
                        copyBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Copiado!';
                        copyBtn.classList.add('copied');
                        setTimeout(() => {
                            copyBtn.innerHTML = originalHtml;
                            copyBtn.classList.remove('copied');
                        }, 2000);
                    }).catch(err => {
                        console.error('Failed to copy text: ', err);
                    });
                });
                
                pre.appendChild(copyBtn);
            });

            // --- Theme Toggle Logic ---
            const themeToggleBtn = document.getElementById('theme-toggle');
            const moonIcon = document.querySelector('.moon-icon');
            const sunIcon = document.querySelector('.sun-icon');
            
            // Check saved preference or system preference
            const savedTheme = localStorage.getItem('api_docs_theme');
            if (savedTheme === 'dark' || (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                document.body.classList.add('dark-mode');
                moonIcon.style.display = 'none';
                sunIcon.style.display = 'block';
            }

            themeToggleBtn.addEventListener('click', () => {
                document.body.classList.toggle('dark-mode');
                const isDark = document.body.classList.contains('dark-mode');
                
                if (isDark) {
                    moonIcon.style.display = 'none';
                    sunIcon.style.display = 'block';
                    localStorage.setItem('api_docs_theme', 'dark');
                } else {
                    moonIcon.style.display = 'block';
                    sunIcon.style.display = 'none';
                    localStorage.setItem('api_docs_theme', 'light');
                }
            });
        });
    </script>
<?= $this->endSection() ?>
