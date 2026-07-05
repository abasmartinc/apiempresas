<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/docs.css?v=' . time()) ?>" />

</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<?php if(session('logged_in')): ?>
    <?= view('partials/header_inner') ?>
<?php else: ?>
    <?= view('partials/header') ?>
<?php endif; ?>


<main class="docs-main">
    <div class="container">
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
                            <a href="<?= site_url('api/docs') ?>" target="_blank">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                Swagger UI (Interactivo)
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
                            <a href="#endpoint-webhooks">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11a9 9 0 0 1 9 9"></path><path d="M4 4a16 16 0 0 1 16 16"></path><circle cx="5" cy="19" r="1"></circle></svg>
                                <span class="nav-num">8.</span> Webhooks <span class="sidebar-badge biz">Biz</span>
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-usage">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="18" y="3" width="4" height="18"></rect><rect x="10" y="8" width="4" height="13"></rect><rect x="2" y="13" width="4" height="8"></rect></svg>
                                <span class="nav-num">9.</span> Consumo
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
                                <span class="nav-num">10.</span> Gestión de Errores
                            </a>
                        </li>
                        <li>
                            <a href="#throttling">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                <span class="nav-num">11.</span> Rate Limiting
                            </a>
                        </li>
                        <li>
                            <a href="#paginacion">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                <span class="nav-num">12.</span> Paginación
                            </a>
                        </li>
                        <li>
                            <a href="#sdks">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11a9 9 0 0 1 9 9"></path><path d="M4 4a16 16 0 0 1 16 16"></path><circle cx="5" cy="19" r="1"></circle></svg>
                                <span class="nav-num">13.</span> SDKs Oficiales
                            </a>
                        </li>
                        <li>
                            <a href="#examples">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                <span class="nav-num">14.</span> Ejemplos
                            </a>
                        </li>
                        <li>
                            <a href="#postman">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                <span class="nav-num">15.</span> Postman
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
                <div class="api-playground" style="margin: 40px 0; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); background: #ffffff;">
                    <div style="padding: 20px 24px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h3 style="margin: 0; font-size: 1.1rem; color: #0f172a; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                Probar API Interactivamente
                            </h3>
                            <p style="margin: 4px 0 0 0; font-size: 0.9rem; color: #64748b;">Lanza peticiones en tiempo real al entorno Sandbox. No requiere API Key.</p>
                        </div>
                        <a href="<?= site_url('api/docs') ?>" style="font-size: 0.85rem; color: #3b82f6; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                            Ir a Swagger UI completo <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
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
                                </select>
                            </div>

                            <div style="margin-bottom: 24px;">
                                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">2. Parámetros</label>
                                <div style="display: flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #cbd5e1; padding: 4px 4px 4px 12px; border-radius: 8px; transition: border-color 0.2s;" id="pg-param-container">
                                    <span id="pg-param-label" style="color: #64748b; font-weight: 600; font-family: monospace;">?cif=</span>
                                    <input type="text" id="pg-cif" value="A15075062" placeholder="Ej: B12345678" style="flex: 1; border: none; padding: 8px 0; outline: none; font-size: 0.95rem; color: #0f172a; font-family: monospace;">
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
                            inputCif.placeholder = 'Ej: B12345678';
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
                                    <span style="color: #334155; font-size: 0.95rem; line-height: 1.5; padding-top: 4px;">Simula una consulta exitosa (Devuelve datos reales de Inditex).</span>
                                </li>
                                <li style="display: flex; gap: 16px; align-items: flex-start;">
                                    <code style="background: #fef2f2; color: #dc2626; padding: 6px 10px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #fecaca; font-weight: 700; min-width: 90px; text-align: center;">B00000000</code>
                                    <span style="color: #334155; font-size: 0.95rem; line-height: 1.5; padding-top: 4px;">Simula un error HTTP 404 estandarizado de "empresa no encontrada".</span>
                                </li>
                                <li style="display: flex; gap: 16px; align-items: flex-start;">
                                    <code style="background: #fffbeb; color: #d97706; padding: 6px 10px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #fde68a; font-weight: 700; min-width: 90px; text-align: center;">C11111111</code>
                                    <span style="color: #334155; font-size: 0.95rem; line-height: 1.5; padding-top: 4px;">Simula un error HTTP 404 indicando que la empresa está encolada asíncronamente para extracción profunda.</span>
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
                </section>

                <!-- AUTH -->
                <section class="docs-section" id="auth">
                    <h2>2. Autenticación</h2>
                    <p>
                        Para acceder a los endpoints debes incluir tu <strong>X-API-KEY</strong> en la cabecera de la petición. Puedes generar y copiar tu clave desde tu <a href="<?= site_url('dashboard') ?>">panel de control</a>.
                    </p>
                    <pre><code class="language-http">GET /api/v1/companies?cif=B12345678 HTTP/1.1
Host: apiempresas.es
X-API-KEY: tu_api_key_aqui
Accept: application/json</code></pre>
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
                                <td><strong>Requerido.</strong> El CIF/NIF de la empresa (ej: B12345678).</td>
                            </tr>
                            <tr>
                                <td><code>admin</code></td>
                                <td>boolean</td>
                                <td><strong>Opcional.</strong> Si es <code>true</code>, incluye los administradores y cargos directivos actuales. <span class="plan-badge pro" style="margin-left: 5px; display: inline-block;">Pro</span></td>
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
    "province": "MADRID",
    "cnae": "6201",
    "cnae_label": "Actividades de programación informática",
    "administrators": [
      {
        "name": "JUAN PÉREZ GARCÍA",
        "position": "Administrador Único"
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
                                <td style="color: #475569;">Estado mercantil actual. Puede ser: <code style="font-size: 0.8rem;">ACTIVA</code>, <code style="font-size: 0.8rem;">CESADA</code>, <code style="font-size: 0.8rem;">LIQUIDACION</code>.</td>
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
                                <td style="color: #475569;">Lista de administradores actuales. Cada objeto contiene <code style="font-size: 0.8rem;">name</code> y su cargo oficial (<code style="font-size: 0.8rem;">position</code>).</td>
                            </tr>
                        </tbody>
                    </table>


                </section>

                <!-- SEARCH -->
                <section class="docs-section" id="endpoint-search">
                    <h2>4. Búsqueda por Nombre</h2>
                    <p>Busca empresas similares a un nombre o razón social.</p>
                    
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
                        </tbody>
                    </table>

                    </table>

                    <h4>Respuesta de éxito (200 OK)</h4>
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
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">data[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td><td style="color: #475569;">Lista de empresas coincidentes. Contiene campos básicos como <code style="font-size: 0.8rem;">cif</code>, <code style="font-size: 0.8rem;">name</code>, <code style="font-size: 0.8rem;">status</code>, <code style="font-size: 0.8rem;">province</code>.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.page</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Página actual de los resultados de búsqueda.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.limit</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Número máximo de resultados por página.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.has_more</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">boolean</span></td><td style="color: #475569;">Indica si existen más resultados para la búsqueda actual.</td></tr>
                        </tbody>
                    </table>
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
                            <span class="plan-badge pro">Pro</span>
                        </div>
                        <p>Obtén el score de interés comercial y el nivel de prioridad de una empresa.</p>
                        <pre><code class="language-json">{
  "success": true,
  "data": {
    "cif": "B12345678",
    "score": 85,
    "priority": "Alta",
    "reasons": "Constitución reciente, Sector en crecimiento",
    "last_signal": { "type": "CONSTITUCION", "date": "2023-10-01" }
  }
}</code></pre>
                        <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <thead style="background: #f8fafc;">
                                <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">data.score</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Puntuación de 0 a 100 indicando el nivel de actividad comercial.</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">data.priority</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">enum</span></td><td style="color: #475569;">Nivel de prioridad de prospección: Alta, Media, Baja.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- SIGNALS -->
                    <div style="margin-top: 32px;">
                        <div class="endpoint-header">
                            <span class="http-badge get">GET</span>
                            <code>/companies/signals</code>
                            <span class="plan-badge pro">Pro</span>
                        </div>
                        <p>Eventos y actos societarios detectados recientemente.</p>
                        <pre><code class="language-json">{
  "success": true,
  "data": {
    "cif": "B12345678",
    "signals": [
      {
        "type": "borme_event",
        "label": "CONSTITUCION",
        "date": "2023-10-01",
        "probability": "Alta"
      }
    ]
  }
}</code></pre>
                        <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <thead style="background: #f8fafc;">
                                <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">signals[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td><td style="color: #475569;">Lista de eventos recientes. Cada objeto tiene un `type` y `label`.</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">signals[].probability</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Fiabilidad del evento detectado basado en nuestro modelo.</td></tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- INSIGHTS -->
                    <div style="margin-top: 32px;">
                        <div class="endpoint-header">
                            <span class="http-badge get">GET</span>
                            <code>/companies/insights</code>
                            <span class="plan-badge business">Business</span>
                        </div>
                        <p>Análisis IA del perfil comercial y necesidades probables de la empresa.</p>
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
                            <span class="plan-badge business">Business</span>
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
                            <span class="plan-badge pro">Pro</span>
                        </div>
                        <p>Obtiene la red de vinculación entre empresas a través de sus administradores. Requiere el parámetro <code>cif</code>.</p>
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
                            <span class="plan-badge business">Business</span>
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
                    <div style="margin-top: 32px;">
                        <div class="endpoint-header">
                            <span class="http-badge post">POST</span>
                            <code>/companies/batch</code>
                            <span class="plan-badge pro">Pro</span>
                            <span class="plan-badge business">Business</span>
                        </div>
                        <p>Consulta hasta 100 empresas de una sola vez enviando un array de CIFs. El coste es variable: 1 consulta de tu cuota mensual o monedero por cada empresa <strong>encontrada</strong> (código 200). Si no tienes suficientes créditos para cubrir el lote entero, la respuesta se recortará automáticamente hasta el número de empresas que puedas pagar.</p>
                        
                        <h5>Cuerpo de la Petición (JSON)</h5>
                        <pre><code class="language-json">{
  "cifs": ["B12345678", "A87654321", "B00000000"],
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
                    <p>Obtén el historial cronológico completo de publicaciones en el Registro Mercantil (BORME) para una empresa.</p>
                    
                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/companies/borme</code>
                        <span class="plan-badge pro">Pro</span>
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
                                <td><strong>Requerido.</strong> El CIF/NIF de la empresa (ej: B12345678).</td>
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
                    <p>Consulta programáticamente el listado de nuevas empresas detectadas por nuestro radar.</p>
                    
                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/companies/radar</code>
                        <span class="plan-badge pro">Pro</span>
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
                                <td>Ventana temporal. Acepta el valor <code>hoy</code> o un número entero indicando los <strong>días atrás</strong> (ej: <code>7</code>, <code>30</code>).</td>
                            </tr>
                            <tr>
                                <td><code>priority</code></td>
                                <td>Nivel de relevancia comercial: <code>alta</code>, <code>media</code>, <code>baja</code>.</td>
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
      "cif": "B12345678",
      "company_name": "NUEVA EMPRESA SL",
      "registro_mercantil": "MADRID",
      "fecha_constitucion": "2023-11-01"
    }
  ]
}</code></pre>
                    <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">data[].fecha_constitucion</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Fecha en que se ha procesado y publicado en la API.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.total_disponibles</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Número total de empresas en el Radar. Si tu plan está limitado a `limit`, verás el total real aquí.</td></tr>
                        </tbody>
                    </table>
                </section>

                <!-- WEBHOOKS -->
                <section class="docs-section" id="endpoint-webhooks">
                    <h2>8. Webhooks (Solo Business)</h2>
                    <p>Recibe notificaciones automáticas en tiempo real en tu sistema cuando detectemos nuevas empresas o señales.</p>
                    
                    <div class="endpoint-header" style="margin-bottom: 5px;">
                        <span class="http-badge get">GET</span>
                        <code>/webhooks</code>
                        <span class="plan-badge business">Business</span>
                    </div>
                    <div class="endpoint-header" style="margin-bottom: 5px;">
                        <span class="http-badge post">POST</span>
                        <code>/webhooks</code>
                        <span class="plan-badge business">Business</span>
                    </div>
                    <div class="endpoint-header">
                        <span class="http-badge" style="background: #f93e3e;">DELETE</span>
                        <code>/webhooks/{id}</code>
                        <span class="plan-badge business">Business</span>
                    </div>

                    <h4>Ejemplo de respuesta (GET)</h4>
                    <pre><code class="language-json">{
  "success": true,
  "data": [
    {
      "id": 1,
      "url": "https://midominio.com/webhook",
      "event": "company.updated",
      "created_at": "2023-11-01 10:00:00"
    }
  ]
}</code></pre>

                    <h4>Ejemplo de respuesta (POST / DELETE)</h4>
                    <pre><code class="language-json">{
  "success": true,
  "message": "Webhook creado correctamente",
  "id": 1
}</code></pre>
                    <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">event</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Tipo de evento suscrito (ej: `company.updated`, `radar.new`).</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">url</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">URL de tu servidor donde enviaremos el payload (POST).</td></tr>
                        </tbody>
                    </table>
                </section>

                <!-- USAGE -->
                <section class="docs-section" id="endpoint-usage">
                    <h2>9. Estadísticas de Consumo</h2>
                    <p>Obtén el recuento de peticiones del mes actual y el historial de empresas consultadas asociado a tu API Key.</p>
                    
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
      "total_queries": 1250
    },
    "history": [
      {
        "cif": "B12345678",
        "name": "EMPRESA DE EJEMPLO SL"
      }
    ]
  }
}</code></pre>
                    <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr><th style="width: 25%;">Campo</th><th style="width: 15%;">Tipo</th><th>Descripción</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">stats.monthly_queries</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Número de llamadas realizadas en el ciclo de facturación actual.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">history[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td><td style="color: #475569;">Histórico de consultas individuales con su fecha y CIF.</td></tr>
                        </tbody>
                    </table>
                </section>

                <!-- SDKS -->
                <section class="docs-section" id="sdks">
                    <h2>13. SDKs Oficiales (Publicados)</h2>
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
                            <pre style="background: #0f172a; padding: 10px 15px; border-radius: 8px; margin: 0;"><code class="language-bash" style="color: #e2e8f0;">composer require apiempresas/php</code></pre>
                            <p style="color:#475569; font-size:0.95rem; margin-top:15px; margin-bottom: 10px;">Ejemplo de uso:</p>
                            <pre style="background: #0f172a; padding: 15px; border-radius: 8px; margin: 0;"><code class="language-php" style="color: #e2e8f0;">require_once 'vendor/autoload.php';

use ApiEmpresas\ApiEmpresas;

$api = new ApiEmpresas('tu_api_key');
$empresa = $api->companies()->getByCif('B12345678');
echo $empresa->name;</code></pre>
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

const api = new ApiEmpresas('tu_api_key');
const empresa = await api.companies.getByCif('B12345678');
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
empresa = api.companies.get_by_cif('B12345678')
print(empresa.name)</code></pre>
                        </div>
                    </div>
                </section>

                <!-- EXAMPLES -->
                <section class="docs-section" id="examples">
                    <h2>14. Ejemplos de Código Manual</h2>
                    <p>Implementa la conexión en minutos con estos ejemplos listos para usar.</p>

                    <div class="code-tabs">
                        <h3 style="display:flex; align-items:center; gap:10px;">
                            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/php/php-original.svg" width="24" height="24" alt="PHP" />
                            PHP (cURL)
                        </h3>
                        <pre><code class="language-php">&lt;?php
$apiKey = 'TU_API_KEY';
$cif = 'B12345678';
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
    'cif' => 'B12345678'
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
    'query' => ['cif' => 'B12345678']
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
params = {"cif": "B12345678"}
headers = {"X-API-KEY": "TU_API_KEY"}

response = requests.get(url, params=params, headers=headers)
print(response.json())</code></pre>

                        <h3 style="display:flex; align-items:center; gap:10px;">
                            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/javascript/javascript-original.svg" width="24" height="24" alt="JavaScript" />
                            JavaScript (Fetch Browser)
                        </h3>
                        <pre><code class="language-js">fetch('https://apiempresas.es/api/v1/companies?cif=B12345678', {
  headers: {
    'X-API-KEY': 'TU_API_KEY',
    'Accept': 'application/json'
  }
})
.then(res => res.json())
.then(data => console.log(data));</code></pre>
                    </div>
                </section>

                <!-- ERRORES -->
                <section class="docs-section" id="errores">
                    <h2>10. Gestión de Errores (RFC 7807)</h2>
                    <p>Las respuestas de error de la API incluyen los campos legacy (<code>success</code>, <code>error</code>, <code>message</code>) y además implementan el estándar moderno <strong>RFC 7807 (Problem Details)</strong>. Si hay un error, el código de estado HTTP será diferente a 200 y recibirás un JSON detallado.</p>
                    <pre><code class="language-json">{
  "success": false,
  "error": "COMPANY_NOT_FOUND",
  "message": "Empresa no encontrada.",
  "type": "https://apiempresas.com/docs/errors/company_not_found",
  "title": "COMPANY_NOT_FOUND",
  "status": 404,
  "detail": "Empresa no encontrada.",
  "instance": "req_8f73b1a2c9"
}</code></pre>
                    <p>El campo <code>instance</code> es único para cada petición y resulta muy útil si necesitas reportar un problema a soporte.</p>
                </section>

                <!-- THROTTLING -->
                <section class="docs-section" id="throttling">
                    <h2>11. Límites de Peticiones (Rate Limiting)</h2>
                    <p>Para proteger la estabilidad de la API, aplicamos límites de peticiones por segundo (Throttling) utilizando un algoritmo de ventana deslizante en memoria caché.</p>
                    <ul>
                        <li><strong>Plan Free:</strong> Límite de 2 peticiones por segundo.</li>
                        <li><strong>Planes de Pago (Pro, Business, Enterprise):</strong> Límite de 20 peticiones por segundo.</li>
                    </ul>
                    <p>Si superas este límite, recibirás un error <code>429 Too Many Requests</code>. Si necesitas procesar muchas empresas de golpe, te recomendamos utilizar el endpoint <a href="#batch">Batch</a>, que permite enviar hasta 100 empresas en una sola petición.</p>
                </section>

                <!-- PAGINACION -->
                <section class="docs-section" id="paginacion">
                    <h2>12. Paginación por Cursores</h2>
                    <p>En el endpoint de búsqueda múltiple (<code>/api/v1/companies/search?multiple=true</code>), puedes utilizar el parámetro <code>page</code> tradicional o el parámetro <code>cursor</code> para paginar los resultados de forma más segura.</p>
                    <p>Si la respuesta contiene más páginas, el objeto <code>meta</code> incluirá un <code>next_cursor</code>. Solo tienes que enviar ese valor exacto en tu siguiente petición para obtener la siguiente página de resultados de manera automática:</p>
                    <pre><code class="language-json">"meta": {
  "page": 1,
  "limit": 20,
  "has_more": true,
  "next_cursor": "eyJwIjoyfQ=="
}</code></pre>
                </section>

                <!-- POSTMAN -->
                <section class="docs-section" id="postman">
                    <h2>15. Postman Collection</h2>
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
</main>

<style>
    .http-badge { padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px; margin-right: 8px; color: white; }
    .http-badge.get { background: #61affe; }
    .http-badge.post { background: #49cc90; }
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

    <?=view('partials/footer') ?>
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
</body>
</html>
