<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app' ) ?>
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
                        <button id="theme-toggle" class="theme-toggle-btn" title="Toggle dark mode">
                            <svg class="moon-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path></svg>
                            <svg class="sun-icon" style="display:none;" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"></circle><line x1="12" y1="1" x2="12" y2="3"></line><line x1="12" y1="21" x2="12" y2="23"></line><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"></line><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"></line><line x1="1" y1="12" x2="3" y2="12"></line><line x1="21" y1="12" x2="23" y2="12"></line><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"></line><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"></line></svg>
                        </button>
                    </div>

                    <!-- Language switcher / Selector de idioma -->
                    <div style="display: flex; gap: 8px; margin-bottom: 16px;">
                        <a href="<?= site_url('documentation') ?>" style="flex:1; text-align:center; padding: 6px 10px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.8rem; font-weight: 600; color: #64748b; text-decoration: none; transition: all 0.2s;" title="Versión en español">🇪🇸 ES</a>
                        <a href="<?= site_url('documentation/en') ?>" style="flex:1; text-align:center; padding: 6px 10px; border-radius: 8px; border: 2px solid #2563eb; background: #eff6ff; font-size: 0.8rem; font-weight: 800; color: #2563eb; text-decoration: none;" title="English version">🇬🇧 EN</a>
                    </div>

                    <ul class="docs-nav">
                        <li>
                            <a href="#intro" class="active">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                <span class="nav-num">1.</span> Introduction
                            </a>
                        </li>
                        <li>
                            <a href="#auth">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <span class="nav-num">2.</span> Authentication
                            </a>
                        </li>
                        <li>
                            <a href="<?= site_url('api/docs') ?>" target="_blank">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                Swagger UI (Interactive)
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
                                <span class="nav-num">3.</span> Lookup by CIF
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-search">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                <span class="nav-num">4.</span> Search
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-expanded">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                <span class="nav-num">5.</span> Commercial <span class="sidebar-badge pro">Pro</span>
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-borme">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                <span class="nav-num">6.</span> BORME History <span class="sidebar-badge pro">Pro</span>
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
                            <a href="#endpoint-contracts">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                <span class="nav-num">9.</span> Public Contracts <span class="sidebar-badge biz">Biz</span>
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-risk-profile">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                <span class="nav-num">10.</span> Risk Profile <span class="sidebar-badge biz">Biz</span>
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-usage">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="18" y="3" width="4" height="18"></rect><rect x="10" y="8" width="4" height="13"></rect><rect x="2" y="13" width="4" height="8"></rect></svg>
                                <span class="nav-num">11.</span> Usage
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="docs-sidebar-section">
                    <h3>Resources</h3>
                    <ul class="docs-nav">
                        <li>
                            <a href="#errores">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                <span class="nav-num">12.</span> Error Handling
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
                                <span class="nav-num">14.</span> Pagination
                            </a>
                        </li>
                        <li>
                            <a href="#sdks">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11a9 9 0 0 1 9 9"></path><path d="M4 4a16 16 0 0 1 16 16"></path><circle cx="5" cy="19" r="1"></circle></svg>
                                <span class="nav-num">15.</span> Official SDKs
                            </a>
                        </li>
                        <li>
                            <a href="#examples">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                <span class="nav-num">16.</span> Examples
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

            <!-- CONTENT -->
            <div class="docs-content">
                <h1>API Documentation</h1>
                <p class="docs-intro-lead" style="font-size: 1.1rem; color: #334155; line-height: 1.7; margin-bottom: 24px;">
                    Welcome to the official documentation for <strong>SpainCompanyAPI.com</strong>. Our RESTful API is designed for development teams that need to integrate Spanish commercial intelligence (data, BORME records, contacts, scoring and corporate links) into their own systems quickly and without friction.
                </p>
                <div class="docs-intro-highlights" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 16px; margin: 0 0 40px 0;">
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.01);">
                        <div style="font-weight: 800; color: #0f172a; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                            RESTful Architecture
                        </div>
                        <div style="font-size: 0.9rem; color: #475569; line-height: 1.5;">Responses always in standard JSON format with predictable, resource-oriented URLs.</div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.01);">
                        <div style="font-weight: 800; color: #0f172a; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            High Availability
                        </div>
                        <div style="font-size: 0.9rem; color: #475569; line-height: 1.5;">Native protection with Intelligent Rate Limiting and ultra-fast cursor-based pagination.</div>
                    </div>
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.01);">
                        <div style="font-weight: 800; color: #0f172a; margin-bottom: 6px; display: flex; align-items: center; gap: 6px;">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                            RFC 7807 Errors
                        </div>
                        <div style="font-size: 0.9rem; color: #475569; line-height: 1.5;">We implement the Problem Details standard for strict and predictable error typing.</div>
                    </div>
                </div>

                <!-- API PLAYGROUND -->
                <div class="api-playground" style="margin: 40px 0; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; display: flex; flex-direction: column; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05); background: #ffffff;">
                    <div style="padding: 20px 24px; border-bottom: 1px solid #e2e8f0; background: #f8fafc; display: flex; align-items: center; justify-content: space-between;">
                        <div>
                            <h3 style="margin: 0; font-size: 1.1rem; color: #0f172a; font-weight: 800; display: flex; align-items: center; gap: 8px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                Try the API Interactively
                            </h3>
                            <p style="margin: 4px 0 0 0; font-size: 0.9rem; color: #64748b;">Fire real-time requests to the Sandbox environment. No API Key required.</p>
                        </div>
                        <a href="<?= site_url('api/docs') ?>" style="font-size: 0.85rem; color: #3b82f6; text-decoration: none; font-weight: 600; display: flex; align-items: center; gap: 4px;">
                            Open full Swagger UI <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </a>
                    </div>

                    <div style="display: flex; flex-wrap: wrap; background: #fff;">
                        <!-- Left Panel: Form -->
                        <div style="flex: 1; min-width: 300px; padding: 24px; border-right: 1px solid #e2e8f0;">
                            <div style="margin-bottom: 20px;">
                                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">1. Select Endpoint</label>
                                <select id="pg-endpoint" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; color: #0f172a; background: #f8fafc; cursor: pointer; outline: none; transition: border-color 0.2s;">
                                    <option value="/api/sandbox/v1/companies">GET /companies (Lookup by CIF)</option>
                                    <option value="/api/sandbox/v1/companies/search">GET /companies/search (Search)</option>
                                    <option value="/api/sandbox/v1/companies/score">GET /companies/score (Commercial Score)</option>
                                    <option value="/api/sandbox/v1/companies/signals">GET /companies/signals (Change Signals)</option>
                                    <option value="/api/sandbox/v1/companies/insights">GET /companies/insights (AI Analysis)</option>
                                    <option value="/api/sandbox/v1/companies/contact-prep">GET /companies/contact-prep (Contact Prep)</option>
                                    <option value="/api/sandbox/v1/companies/match">GET /companies/match (B2B Match)</option>
                                    <option value="/api/sandbox/v1/companies/network">GET /companies/network (Corporate Network)</option>
                                    <option value="/api/sandbox/v1/companies/borme">GET /companies/borme (BORME History)</option>
                                    <option value="/api/sandbox/v1/companies/radar">GET /companies/radar (Radar Pro)</option>
                                    <option value="/api/sandbox/v1/companies/risk-profile">GET /companies/risk-profile (Risk Profile)</option>
                                </select>
                            </div>

                            <div style="margin-bottom: 24px;">
                                <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.05em;">2. Parameters</label>
                                <div style="display: flex; align-items: center; gap: 8px; background: #fff; border: 1px solid #cbd5e1; padding: 4px 4px 4px 12px; border-radius: 8px; transition: border-color 0.2s;" id="pg-param-container">
                                    <span id="pg-param-label" style="color: #64748b; font-weight: 600; font-family: monospace;">?cif=</span>
                                    <input type="text" id="pg-cif" value="A15075062" placeholder="e.g. B12345678" style="flex: 1; border: none; padding: 8px 0; outline: none; font-size: 0.95rem; color: #0f172a; font-family: monospace;">
                                </div>
                                <p style="font-size: 0.8rem; color: #94a3b8; margin-top: 8px;">Use <code style="background: #f1f5f9; padding: 2px 4px; border-radius: 4px; color: #e11d48; font-family: monospace;">A15075062</code> to test the Sandbox.</p>
                            </div>

                            <button id="pg-run" style="width: 100%; background: #2563eb; color: #fff; border: none; padding: 14px; border-radius: 8px; font-weight: 800; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: background 0.2s; box-shadow: 0 4px 12px rgba(37,99,235,0.2);">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                                Run Request
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
                            <pre style="margin: 0; padding: 24px; flex: 1; overflow-y: auto; max-height: 400px; font-family: 'Fira Code', monospace; font-size: 0.85rem; color: #e2e8f0; background: transparent;"><code id="pg-response" class="language-json">// Press "Run Request" to see the live JSON response.
// Sandbox defaults to the magic CIF: A15075062.</code></pre>
                            
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
                            inputCif.placeholder = 'e.g. Inditex';
                            if(inputCif.value === 'A15075062') inputCif.value = 'Inditex';
                        } else {
                            paramLabel.textContent = '?cif=';
                            inputCif.placeholder = 'e.g. B12345678';
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
                                headers: { 'Accept': 'application/json' }
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
                            
                            if (window.Prism) { Prism.highlightElement(responseBlock); }
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



                <!-- INTRODUCTION -->
                <section class="docs-section" id="intro">
                    <h2>1. Introduction</h2>
                    <p>
                        The API is designed following REST principles. All responses are returned in JSON format and require a secure connection via HTTPS.
                    </p>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 16px; margin: 24px 0;">
                        <div style="background: #1e293b; border: 1px solid #334155; padding: 20px; border-radius: 12px; position: relative;">
                            <div style="font-size: 0.75rem; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: #10b981;"></div> Production Environment
                            </div>
                            <code style="color: #e2e8f0; font-family: 'Fira Code', monospace; font-size: 0.95rem; background: transparent; padding: 0;">https://spaincompanyapi.com/api/v1</code>
                        </div>
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 12px; position: relative;">
                            <div style="font-size: 0.75rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; margin-bottom: 8px; display: flex; align-items: center; gap: 6px;">
                                <div style="width: 8px; height: 8px; border-radius: 50%; background: #f59e0b;"></div> Sandbox Environment (Free)
                            </div>
                            <code style="color: #0f172a; font-family: 'Fira Code', monospace; font-size: 0.95rem; background: transparent; padding: 0;">https://spaincompanyapi.com/api/sandbox/v1</code>
                        </div>
                    </div>

                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; margin-top: 32px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);">
                        <div style="background: #f8fafc; padding: 16px 20px; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#f59e0b" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            <h4 style="margin: 0; color: #0f172a; font-weight: 800; font-size: 1.05rem;">Sandbox Guide (Test Environment)</h4>
                        </div>
                        <div style="padding: 24px;">
                            <p style="margin: 0 0 24px 0; font-size: 0.95rem; color: #475569; line-height: 1.6;">
                                To run tests <strong>without consuming credit or quota</strong>, send your requests to the Sandbox URL using your regular API Key. We have enabled several "magic CIFs" that let you simulate different flows in your application:
                            </p>
                            
                            <h5 style="margin: 0 0 16px 0; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">🎯 Allowed Magic CIFs</h5>
                            <ul style="margin: 0 0 32px 0; padding: 0; list-style: none; display: flex; flex-direction: column; gap: 12px;">
                                <li style="display: flex; gap: 16px; align-items: flex-start;">
                                    <code style="background: #f1f5f9; color: #0f172a; padding: 6px 10px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #cbd5e1; font-weight: 700; min-width: 90px; text-align: center;">A15075062</code>
                                    <span style="color: #334155; font-size: 0.95rem; line-height: 1.5; padding-top: 4px;">Simulates a successful lookup (returns real Inditex data).</span>
                                </li>
                                <li style="display: flex; gap: 16px; align-items: flex-start;">
                                    <code style="background: #fef2f2; color: #dc2626; padding: 6px 10px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #fecaca; font-weight: 700; min-width: 90px; text-align: center;">B00000000</code>
                                    <span style="color: #334155; font-size: 0.95rem; line-height: 1.5; padding-top: 4px;">Simulates a standardised HTTP 404 "company not found" error.</span>
                                </li>
                                <li style="display: flex; gap: 16px; align-items: flex-start;">
                                    <code style="background: #fffbeb; color: #d97706; padding: 6px 10px; border-radius: 6px; font-size: 0.85rem; border: 1px solid #fde68a; font-weight: 700; min-width: 90px; text-align: center;">C11111111</code>
                                    <span style="color: #334155; font-size: 0.95rem; line-height: 1.5; padding-top: 4px;">Simulates an HTTP 404 indicating the company is asynchronously queued for deep extraction.</span>
                                </li>
                            </ul>

                            <h5 style="margin: 0 0 16px 0; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b;">⚡ Simulating Complex Endpoints</h5>
                            <ul style="margin: 0; padding-left: 20px; color: #334155; font-size: 0.95rem; line-height: 1.7; display: flex; flex-direction: column; gap: 10px;">
                                <li><strong>Search (/search):</strong> Send any text in <code>q=</code>. Always returns Inditex data so you can validate list parsing in your frontend.</li>
                                <li><strong>Batch (/batch):</strong> Send a JSON with magic CIFs (e.g. <code>{"cifs": ["A15075062", "B00000000"]}</code>) to test how your code handles partial responses.</li>
                                <li><strong>Premium Endpoints (/score, /insights):</strong> Call any of them using <code>A15075062</code> to receive its simulated data structure and validate the integration.</li>
                            </ul>
                        </div>
                    </div>
                </section>

                <!-- AUTHENTICATION -->
                <section class="docs-section" id="auth">
                    <h2>2. Authentication</h2>
                    <p>
                        To access the endpoints you must include your <strong>X-API-KEY</strong> in the request header. You can generate and copy your key from your <a href="<?= site_url('dashboard') ?>">control panel</a>.
                    </p>
                    <pre><code class="language-http">GET /api/v1/companies?cif=B12345678 HTTP/1.1
Host: spaincompanyapi.com
X-API-KEY: your_api_key_here
Accept: application/json</code></pre>
                </section>

                <!-- LOOKUP BY CIF -->
                <section class="docs-section" id="endpoint-by-cif">
                    <h2>3. Lookup by CIF</h2>
                    <p>Retrieve the complete profile of a company by providing its CIF or NIF.</p>
                    
                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/companies</code>
                    </div>

                    <h4>Parameters</h4>
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>cif</code></td>
                                <td>string</td>
                                <td><strong>Required.</strong> The company CIF/NIF (e.g. B12345678).</td>
                            </tr>
                            <tr>
                                <td><code>admin</code></td>
                                <td>boolean</td>
                                <td><strong>Optional.</strong> If <code>true</code>, includes current directors and officers. <span class="plan-badge pro" style="margin-left: 5px; display: inline-block;">Pro</span></td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Success response (200 OK)</h4>
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
                    <p style="font-size: 13px; color: #64748b; margin-top: 5px; margin-bottom: 24px;">* The <code>administrators</code> array is only included if you send <code>admin=true</code> and your plan supports it.</p>

                    <h4 style="margin-bottom: 12px; font-size: 1.1rem; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                        Data Dictionary (Schema)
                    </h4>
                    <table class="docs-table" style="margin-bottom: 40px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th style="width: 25%;">Field</th>
                                <th style="width: 15%;">Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">cif</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td>
                                <td style="color: #475569;">The normalised Tax Identification Code (Spanish CIF/NIF).</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">name</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td>
                                <td style="color: #475569;">The official company name as registered in the BORME.</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">status</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">enum</span></td>
                                <td style="color: #475569;">Current commercial status. Possible values: <code style="font-size: 0.8rem;">ACTIVA</code>, <code style="font-size: 0.8rem;">CESADA</code>, <code style="font-size: 0.8rem;">LIQUIDACION</code>.</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">province</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td>
                                <td style="color: #475569;">Spanish province where the registered office is located.</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">cnae</code> / <code style="background: transparent; color: #2563eb; font-weight: 600;">cnae_label</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td>
                                <td style="color: #475569;">CNAE industry code for the primary economic activity and its full descriptive label.</td>
                            </tr>
                            <tr>
                                <td><code style="background: transparent; color: #2563eb; font-weight: 600;">administrators[]</code></td>
                                <td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td>
                                <td style="color: #475569;">List of current directors. Each object contains <code style="font-size: 0.8rem;">name</code> and their official title (<code style="font-size: 0.8rem;">position</code>).</td>
                            </tr>
                        </tbody>
                    </table>
                </section>

                <!-- SEARCH -->
                <section class="docs-section" id="endpoint-search">
                    <h2>4. Search by Name</h2>
                    <p>Search for companies matching a name or business name.</p>
                    
                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/companies/search</code>
                    </div>

                    <h4>Parameters</h4>
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>name</code></td>
                                <td>string</td>
                                <td><strong>Required.</strong> The name or partial name to search for. (Alias: <code>q</code>)</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Success response (200 OK)</h4>
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
                        Data Dictionary (Search)
                    </h4>
                    <table class="docs-table" style="margin-bottom: 40px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr><th style="width: 25%;">Field</th><th style="width: 15%;">Type</th><th>Description</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">data[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td><td style="color: #475569;">List of matching companies. Contains basic fields: <code style="font-size: 0.8rem;">cif</code>, <code style="font-size: 0.8rem;">name</code>, <code style="font-size: 0.8rem;">status</code>, <code style="font-size: 0.8rem;">province</code>.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.page</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Current page of search results.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.limit</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Maximum number of results per page.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.has_more</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">boolean</span></td><td style="color: #475569;">Indicates whether more results exist for the current search.</td></tr>
                        </tbody>
                    </table>
                </section>

                <!-- COMMERCIAL CAPABILITIES -->
                <section class="docs-section" id="endpoint-expanded">
                    <h2>5. Commercial Capabilities (Pro &amp; Business)</h2>
                    <p>Power your prospecting with enriched data and real-time business signals.</p>
                    
                    <!-- SCORE -->
                    <div style="margin-top: 32px;">
                        <div class="endpoint-header">
                            <span class="http-badge get">GET</span>
                            <code>/companies/score</code>
                            <span class="plan-badge pro">Pro</span>
                        </div>
                        <p>Get the commercial interest score and priority level for a company.</p>
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
                                <tr><th style="width: 25%;">Field</th><th style="width: 15%;">Type</th><th>Description</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">data.score</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Score from 0 to 100 indicating the level of commercial activity.</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">data.priority</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">enum</span></td><td style="color: #475569;">Prospecting priority level: Alta (High), Media (Medium), Baja (Low).</td></tr>
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
                        <p>Recently detected corporate events and acts.</p>
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
                                <tr><th style="width: 25%;">Field</th><th style="width: 15%;">Type</th><th>Description</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">signals[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td><td style="color: #475569;">List of recent events. Each object has a <code>type</code> and <code>label</code>.</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">signals[].probability</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Reliability of the detected event based on our model.</td></tr>
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
                        <p>AI-powered analysis of the commercial profile and probable needs of a company.</p>
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
                                <tr><th style="width: 25%;">Field</th><th style="width: 15%;">Type</th><th>Description</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">needs[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[string]</span></td><td style="color: #475569;">List of software needs detected through AI inference.</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">conversion_probability</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Estimated B2B cross-selling probability.</td></tr>
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
                        <p>AI-generated sales pitch and objection handling for a target company.</p>
                        <pre><code class="language-json">{
  "success": true,
  "data": {
    "sales_approach": "Consultative and direct approach...",
    "suggested_message": "Hi, I noticed the recent growth of...",
    "likely_objection": "Temporary budget constraints",
    "attack_angle": "Demonstrate immediate ROI..."
  }
}</code></pre>
                        <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <thead style="background: #f8fafc;">
                                <tr><th style="width: 25%;">Field</th><th style="width: 15%;">Type</th><th>Description</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">sales_approach</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Suggested commercial outreach strategy.</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">likely_objection</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Most likely objection from the prospect.</td></tr>
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
                        <p>Gets the corporate network linking companies through their directors. Requires the <code>cif</code> parameter.</p>
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
                                <tr><th style="width: 25%;">Field</th><th style="width: 15%;">Type</th><th>Description</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">nodes[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td><td style="color: #475569;">Graph nodes (companies or directors).</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">edges[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td><td style="color: #475569;">Connections (links) between nodes.</td></tr>
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
                        <p>B2B Match calculator. Requires <code>cif</code> and <code>seller_sector</code> parameters. Returns the commercial fit level and a personalised sales argument.</p>
                        <pre><code class="language-json">{
  "success": true,
  "data": {
    "match_score": 85,
    "fit_level": "Alto",
    "pain_points_addressed": ["Ineficiencia operativa", "Falta de digitalización"],
    "sales_argument": "Our software eliminates manual work...",
    "recommendation": "Contact immediately."
  }
}</code></pre>
                        <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                            <thead style="background: #f8fafc;">
                                <tr><th style="width: 25%;">Field</th><th style="width: 15%;">Type</th><th>Description</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">match_score</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">B2B fit level (0–100).</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">sales_argument</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Sales argument tailored to the match.</td></tr>
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
                        <p>Query up to 100 companies in a single request by sending an array of CIFs. The cost is variable: 1 credit from your monthly quota or wallet per company <strong>found</strong> (HTTP 200). If you don't have enough credits to cover the entire batch, the response will be automatically trimmed to the number of companies you can afford.</p>
                        
                        <h5>Request Body (JSON)</h5>
                        <pre><code class="language-json">{
  "cifs": ["B12345678", "A87654321", "B00000000"],
  "admin": true
}</code></pre>

                        <h5>Success response</h5>
                        <pre><code class="language-json">{
  "success": true,
  "data": [
    { "cif": "B12345678", "name": "EMPRESA DE EJEMPLO SL", "status": "ACTIVA" },
    { "cif": "A87654321", "name": "OTRA EMPRESA SA", "status": "ACTIVA" }
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
                                <tr><th style="width: 25%;">Field</th><th style="width: 15%;">Type</th><th>Description</th></tr>
                            </thead>
                            <tbody>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.requested</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Number of CIFs sent in the request.</td></tr>
                                <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.cost</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Credits deducted from your plan for companies found.</td></tr>
                            </tbody>
                        </table>
                    </div>
                </section>

                <!-- BORME HISTORY -->
                <section class="docs-section" id="endpoint-borme">
                    <h2>6. BORME Act History</h2>
                    <p>Get the complete chronological history of publications in the Spanish Commercial Registry (BORME) for a company.</p>
                    
                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/companies/borme</code>
                        <span class="plan-badge pro">Pro</span>
                    </div>

                    <h4>Parameters</h4>
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Type</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>cif</code></td>
                                <td>string</td>
                                <td><strong>Required.</strong> The company CIF/NIF (e.g. B12345678).</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Success response (200 OK)</h4>
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
                            <tr><th style="width: 25%;">Field</th><th style="width: 15%;">Type</th><th>Description</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">events[].date</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Official BORME publication date (YYYY-MM-DD format).</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">events[].act_types</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Categorised corporate act type (e.g. Appointments, Capital Increase).</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">events[].url_pdf</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Direct URL to the original PDF scan of the Official Gazette.</td></tr>
                        </tbody>
                    </table>
                </section>

                <!-- RADAR -->
                <section class="docs-section" id="endpoint-radar">
                    <h2>7. Company Radar</h2>
                    <p>Programmatically query the list of newly incorporated companies detected by our radar.</p>
                    
                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/companies/radar</code>
                        <span class="plan-badge pro">Pro</span>
                    </div>

                    <h4>Optional parameters</h4>
                    <table class="docs-table">
                        <thead>
                            <tr>
                                <th>Field</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>province</code></td>
                                <td>Filter by Spanish province (e.g. MADRID).</td>
                            </tr>
                            <tr>
                                <td><code>range</code></td>
                                <td>Time window. Accepts <code>hoy</code> (today) or an integer for <strong>days back</strong> (e.g. <code>7</code>, <code>30</code>).</td>
                            </tr>
                            <tr>
                                <td><code>priority</code></td>
                                <td>Commercial relevance level: <code>alta</code> (high), <code>media</code> (medium), <code>baja</code> (low).</td>
                            </tr>
                        </tbody>
                    </table>

                    <h4>Success response (200 OK)</h4>
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
                            <tr><th style="width: 25%;">Field</th><th style="width: 15%;">Type</th><th>Description</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">data[].fecha_constitucion</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Date the company was processed and published in the API.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">meta.total_disponibles</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Total companies in the Radar. If your plan is capped at <code>limit</code>, you'll see the real total here.</td></tr>
                        </tbody>
                    </table>
                </section>

                <!-- WEBHOOKS -->
                <section class="docs-section" id="endpoint-webhooks">
                    <h2>8. Webhooks (Business only)</h2>
                    <p>Receive automatic real-time notifications in your system when we detect new companies or signals.</p>
                    
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

                    <h4>Example response (GET)</h4>
                    <pre><code class="language-json">{
  "success": true,
  "data": [
    {
      "id": 1,
      "url": "https://yourdomain.com/webhook",
      "event": "company.updated",
      "created_at": "2023-11-01 10:00:00"
    }
  ]
}</code></pre>

                    <h4>Example response (POST / DELETE)</h4>
                    <pre><code class="language-json">{
  "success": true,
  "message": "Webhook created successfully",
  "id": 1
}</code></pre>
                    <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr><th style="width: 25%;">Field</th><th style="width: 15%;">Type</th><th>Description</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">event</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Subscribed event type (e.g. <code>company.updated</code>, <code>radar.new</code>).</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">url</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Your server URL where we will POST the payload.</td></tr>
                        </tbody>
                    </table>
                </section>

                <!-- PUBLIC CONTRACTS -->
                <section class="docs-section" id="endpoint-contracts">
                    <h2>9. Public Contracts (Business Plan Only)</h2>
                    <p>Query public procurement contracts and awards associated with Spanish companies by CIF. Retrieve contracting authority details, award amounts, award dates, and official tender links.</p>

                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/companies/contracts</code>
                        <span class="plan-badge business">Business</span>
                    </div>

                    <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr><th style="width: 20%;">Parameter</th><th style="width: 15%;">Type</th><th style="width: 15%;">Required</th><th>Description</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">cif</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td><span style="background: #fef2f2; color: #ef4444; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">Required</span></td><td style="color: #475569;">Company CIF/NIF to query (e.g. <code>A01001411</code>).</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">page</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td><span style="color: #94a3b8; font-size: 0.8rem;">Optional</span></td><td style="color: #475569;">Page number to retrieve. Default: <code>1</code>.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">limit</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td><span style="color: #94a3b8; font-size: 0.8rem;">Optional</span></td><td style="color: #475569;">Number of contracts per page. Default: <code>20</code> (maximum: <code>100</code>).</td></tr>
                        </tbody>
                    </table>

                    <h4>Success response (200 OK - Business Plan)</h4>
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

                    <h4>Plan restricted response (403 Forbidden - Free & Pro Plans)</h4>
                    <pre><code class="language-json">{
  "success": false,
  "error": "PLAN_RESTRICTION",
  "message": "El acceso a contratos públicos requiere el plan Business."
}</code></pre>
                </section>

                <!-- CORPORATE RISK PROFILE -->
                <section class="docs-section" id="endpoint-risk-profile">
                    <h2>10. Corporate Risk Profile (Business Plan Only)</h2>
                    <p>Returns the corporate risk profile, registry compliance (annual accounts filing), governance stability, capital volatility, and official distress alerts associated with a company by CIF. Designed to automate credit scoring, solvency checks, and compliance in B2B onboarding workflows.</p>

                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/companies/risk-profile</code>
                        <span class="plan-badge business">Business</span>
                    </div>

                    <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr><th style="width: 20%;">Parameter</th><th style="width: 15%;">Type</th><th style="width: 15%;">Required</th><th>Description</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">cif</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td><span style="background: #fef2f2; color: #ef4444; padding: 2px 6px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">Required</span></td><td style="color: #475569;">Company CIF/NIF to audit (e.g. <code>A01001411</code> or <code>A15075062</code> in Sandbox).</td></tr>
                        </tbody>
                    </table>

                    <h4>Success response (200 OK - Business Plan)</h4>
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

                    <h4>Plan restricted response (403 Forbidden - Free & Pro Plans)</h4>
                    <pre><code class="language-json">{
  "success": false,
  "error": "PLAN_RESTRICTION",
  "message": "El acceso al perfil de riesgo corporativo requiere el plan Business."
}</code></pre>

                    <table class="docs-table" style="margin-top: 16px; margin-bottom: 24px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <thead style="background: #f8fafc;">
                            <tr><th style="width: 25%;">Field</th><th style="width: 15%;">Type</th><th>Description</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">risk_score</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer (0-100)</span></td><td style="color: #475569;">Consolidated algorithmic risk rating (higher values denote higher default or distress risk).</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">risk_level</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">string</span></td><td style="color: #475569;">Qualitative risk grade: <code>BAJO</code>, <code>MEDIO</code>, <code>ALTO</code>, <code>CRITICO</code>.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">dimensions</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">object</span></td><td style="color: #475569;">Detailed breakdown of risk factors: legal distress, filing compliance, governance changes, capital instability and credit mitigation.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">canonical_events[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td><td style="color: #475569;">Chronological list of official corporate and mercantile registry events with severity assessment.</td></tr>
                        </tbody>
                    </table>
                </section>

                <!-- USAGE -->
                <section class="docs-section" id="endpoint-usage">
                    <h2>11. Usage Statistics</h2>
                    <p>Get the request count for the current month and the history of companies queried associated with your API Key.</p>
                    
                    <div class="endpoint-header">
                        <span class="http-badge get">GET</span>
                        <code>/usage</code>
                    </div>

                    <h4>Success response (200 OK)</h4>
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
                            <tr><th style="width: 25%;">Field</th><th style="width: 15%;">Type</th><th>Description</th></tr>
                        </thead>
                        <tbody>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">stats.monthly_queries</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">integer</span></td><td style="color: #475569;">Number of calls made in the current billing cycle.</td></tr>
                            <tr><td><code style="background: transparent; color: #2563eb; font-weight: 600;">history[]</code></td><td><span style="color: #10b981; font-family: monospace; font-size: 0.85rem;">array[object]</span></td><td style="color: #475569;">History of individual queries with their date and CIF.</td></tr>
                        </tbody>
                    </table>
                </section>

                <!-- ERROR HANDLING -->
                <section class="docs-section" id="errores">
                    <h2>12. Error Handling (RFC 7807)</h2>
                    <p>API error responses include legacy fields (<code>success</code>, <code>error</code>, <code>message</code>) and also implement the modern <strong>RFC 7807 (Problem Details)</strong> standard. If there is an error, the HTTP status code will be different from 200 and you will receive a detailed JSON.</p>
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
                    <p>The <code>instance</code> field is unique per request and is very useful if you need to report a problem to support.</p>
                </section>

                <!-- RATE LIMITING -->
                <section class="docs-section" id="throttling">
                    <h2>13. Rate Limiting</h2>
                    <p>To protect the API's stability and help you monitor your consumption, we return <strong>standard HTTP headers</strong> in every response that inform you in real time of your monthly quota and per-second speed limit.</p>
                    
                    <h4 style="margin-top: 24px; margin-bottom: 12px; font-size: 1.1rem; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                        HTTP Response Headers
                    </h4>
                    <table class="docs-table" style="margin-bottom: 24px;">
                        <thead>
                            <tr>
                                <th>HTTP Header</th>
                                <th>Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><code>X-RateLimit-Limit</code></td>
                                <td>Maximum requests allowed per second (2 on Free, 20 on paid plans).</td>
                            </tr>
                            <tr>
                                <td><code>X-RateLimit-Remaining</code></td>
                                <td>Requests per second remaining in the current time window.</td>
                            </tr>
                            <tr>
                                <td><code>X-RateLimit-Reset</code></td>
                                <td>Unix timestamp indicating when the speed limit resets.</td>
                            </tr>
                            <tr>
                                <td><code>X-Quota-Limit</code></td>
                                <td>Total monthly query limit for your current plan.</td>
                            </tr>
                            <tr>
                                <td><code>X-Quota-Remaining</code></td>
                                <td>Monthly queries remaining before you start using wallet balance.</td>
                            </tr>
                            <tr>
                                <td><code>X-Request-Id</code></td>
                                <td>Unique traceability identifier for the request. Useful for fast technical support.</td>
                            </tr>
                        </tbody>
                    </table>

                    <p>If you exceed the per-second limit, you will receive a <code>429 Too Many Requests</code> error along with the <code>Retry-After: 1</code> header indicating to wait 1 second. If you need to process many companies at once, we recommend using the <a href="#batch">Batch</a> endpoint.</p>
                </section>

                <!-- PAGINATION -->
                <section class="docs-section" id="paginacion">
                    <h2>14. Cursor-based Pagination</h2>
                    <p>In the multiple search endpoint (<code>/api/v1/companies/search?multiple=true</code>), you can use the traditional <code>page</code> parameter or the <code>cursor</code> parameter to paginate results more safely.</p>
                    <p>If the response contains more pages, the <code>meta</code> object will include a <code>next_cursor</code>. Simply send that exact value in your next request to automatically get the next page of results:</p>
                    <pre><code class="language-json">"meta": {
  "page": 1,
  "limit": 20,
  "has_more": true,
  "next_cursor": "eyJwIjoyfQ=="
}</code></pre>
                </section>

                <!-- OFFICIAL SDKs -->
                <section class="docs-section" id="sdks">
                    <h2>15. Official SDKs (Published)</h2>
                    <p>Speed up integration in your applications using our official libraries with static typing and native error handling. These SDKs are production-ready.</p>
                    
                    <div style="display: flex; flex-direction: column; gap: 20px; margin-top: 24px;">
                        
                        <!-- PHP SDK -->
                        <div style="background: #ffffff; padding: 25px; border-radius: 12px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                                <h3 style="display:flex; align-items:center; gap:10px; font-size:1.2rem; color:#0f172a; margin:0;">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#4f5b93" stroke-width="2"><path d="M12 2L2 7l10 5 10-5-10-5z"></path><path d="M2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                                    PHP SDK
                                </h3>
                                <span style="background: #e0e7ff; color: #4338ca; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">Official</span>
                            </div>
                            <p style="color:#475569; font-size:0.95rem; margin-bottom: 10px;">Install via Composer:</p>
                            <pre style="background: #0f172a; padding: 10px 15px; border-radius: 8px; margin: 0;"><code class="language-bash" style="color: #e2e8f0;">composer require apiempresas/php</code></pre>
                            <p style="color:#475569; font-size:0.95rem; margin-top:15px; margin-bottom: 10px;">Usage example:</p>
                            <pre style="background: #0f172a; padding: 15px; border-radius: 8px; margin: 0;"><code class="language-php" style="color: #e2e8f0;">require_once 'vendor/autoload.php';

use ApiEmpresas\ApiEmpresas;

$api = new ApiEmpresas('your_api_key');
$company = $api->companies()->getByCif('B12345678');
echo $company->name;</code></pre>
                        </div>

                        <!-- Node.js SDK -->
                        <div style="background: #ffffff; padding: 25px; border-radius: 12px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                                <h3 style="display:flex; align-items:center; gap:10px; font-size:1.2rem; color:#0f172a; margin:0;">
                                    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/nodejs/nodejs-original.svg" width="28" height="28" alt="Node.js" />
                                    Node.js / TS
                                </h3>
                                <span style="background: #e0e7ff; color: #4338ca; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">Official</span>
                            </div>
                            <p style="color:#475569; font-size:0.95rem; margin-bottom: 10px;">Install via NPM:</p>
                            <pre style="background: #0f172a; padding: 10px 15px; border-radius: 8px; margin: 0;"><code class="language-bash" style="color: #e2e8f0;">npm install apiempresas</code></pre>
                            <p style="color:#475569; font-size:0.95rem; margin-top:15px; margin-bottom: 10px;">TypeScript usage example:</p>
                            <pre style="background: #0f172a; padding: 15px; border-radius: 8px; margin: 0;"><code class="language-typescript" style="color: #e2e8f0;">import { ApiEmpresas } from 'apiempresas';

const api = new ApiEmpresas('your_api_key');
const company = await api.companies.getByCif('B12345678');
console.log(company.name);</code></pre>
                        </div>

                        <!-- Python SDK -->
                        <div style="background: #ffffff; padding: 25px; border-radius: 12px; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 15px;">
                                <h3 style="display:flex; align-items:center; gap:10px; font-size:1.2rem; color:#0f172a; margin:0;">
                                    <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/python/python-original.svg" width="28" height="28" alt="Python" />
                                    Python SDK
                                </h3>
                                <span style="background: #e0e7ff; color: #4338ca; padding: 4px 8px; border-radius: 4px; font-size: 0.75rem; font-weight: 700;">Official</span>
                            </div>
                            <p style="color:#475569; font-size:0.95rem; margin-bottom: 10px;">Install via PIP:</p>
                            <pre style="background: #0f172a; padding: 10px 15px; border-radius: 8px; margin: 0;"><code class="language-bash" style="color: #e2e8f0;">pip install apiempresas</code></pre>
                            <p style="color:#475569; font-size:0.95rem; margin-top:15px; margin-bottom: 10px;">Usage example:</p>
                            <pre style="background: #0f172a; padding: 15px; border-radius: 8px; margin: 0;"><code class="language-python" style="color: #e2e8f0;">from apiempresas import ApiEmpresas

api = ApiEmpresas('your_api_key')
company = api.companies.get_by_cif('B12345678')
print(company.name)</code></pre>
                        </div>
                    </div>
                </section>

                <!-- CODE EXAMPLES -->
                <section class="docs-section" id="examples">
                    <h2>16. Manual Code Examples</h2>
                    <p>Implement the connection in minutes with these ready-to-use examples.</p>

                    <div class="code-tabs">
                        <h3 style="display:flex; align-items:center; gap:10px;">
                            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/php/php-original.svg" width="24" height="24" alt="PHP" />
                            PHP (cURL)
                        </h3>
                        <pre><code class="language-php">&lt;?php
$apiKey = 'YOUR_API_KEY';
$cif = 'B12345678';
$url = 'https://spaincompanyapi.com/api/v1/companies?cif=' . $cif;

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
    'X-API-KEY' => 'YOUR_API_KEY'
])->get('https://spaincompanyapi.com/api/v1/companies', [
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

$response = $client->request('GET', 'https://spaincompanyapi.com/api/v1/companies', [
    'headers' => [
        'X-API-KEY' => 'YOUR_API_KEY',
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
  const response = await fetch('https://spaincompanyapi.com/api/v1/companies?cif=' + cif, {
    headers: { 'X-API-KEY': 'YOUR_API_KEY' }
  });
  const data = await response.json();
  console.log(data);
};</code></pre>

                        <h3 style="display:flex; align-items:center; gap:10px;">
                            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/python/python-original.svg" width="24" height="24" alt="Python" />
                            Python (Requests)
                        </h3>
                        <pre><code class="language-python">import requests

url = "https://spaincompanyapi.com/api/v1/companies"
params = {"cif": "B12345678"}
headers = {"X-API-KEY": "YOUR_API_KEY"}

response = requests.get(url, params=params, headers=headers)
print(response.json())</code></pre>

                        <h3 style="display:flex; align-items:center; gap:10px;">
                            <img src="https://cdn.jsdelivr.net/gh/devicons/devicon@latest/icons/javascript/javascript-original.svg" width="24" height="24" alt="JavaScript" />
                            JavaScript (Browser Fetch)
                        </h3>
                        <pre><code class="language-js">fetch('https://spaincompanyapi.com/api/v1/companies?cif=B12345678', {
  headers: {
    'X-API-KEY': 'YOUR_API_KEY',
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
                    <p>If you prefer to test the API directly in Postman, you can download our official collection and import it with one click.</p>
                    
                    <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px dashed #cbd5e1; text-align: center; margin-top: 20px;">
                        <img src="https://www.postman.com/assets/postman-logo-stacked.svg" alt="Postman" style="height: 40px; margin-bottom: 15px;">
                        <p style="margin-bottom: 20px; color: #475569;">Includes all endpoints configured, response examples and environment variables.</p>
                        <a href="<?= base_url('public/docs/apiempresas_postman.json') ?>" download class="btn primary" style="display: inline-flex; align-items: center; gap: 8px;">
                            <span>📥 Download Postman Collection</span>
                        </a>
                    </div>
                </section>

                <!-- CTA -->
                <div style="margin-top: 80px; text-align: center; background: linear-gradient(135deg, #0F172A 0%, #1E3A8A 100%); color: white; padding: 60px 40px; border-radius: 32px; box-shadow: 0 25px 50px -12px rgba(30, 58, 138, 0.25);">
                    <h2 style="color: white; font-size: 2.3rem; font-weight: 900; margin-bottom: 16px; letter-spacing: -0.02em;">🚀 Ready to get started?</h2>
                    <p style="font-size: 1.25rem; color: rgba(255,255,255,0.7); margin-bottom: 32px; font-weight: 500;">Get your API Key in seconds and start integrating real data into your applications.</p>
                    <a href="<?= site_url('dashboard') ?>" class="btn-radar-strong" style="max-width: 400px; margin: 0 auto; padding: 20px 40px;">Go to Control Panel</a>
                </div>
            </div>
        </div>
    </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
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

    @media (max-width: 768px) {
        .radar-upsell { flex-direction: column; text-align: center; padding: 32px 24px; }
        .conditional-banner { flex-direction: column; text-align: center; }
    }
</style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Scrollspy for sidebar menu
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

            sections.forEach(section => { observer.observe(section); });

            // --- Copy to Clipboard Buttons for <pre> blocks ---
            const preTags = document.querySelectorAll('pre');
            preTags.forEach(pre => {
                if(pre.innerText.trim() === '') return;

                const copyBtn = document.createElement('button');
                copyBtn.className = 'copy-code-btn';
                copyBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg> Copy';
                
                const computedStyle = window.getComputedStyle(pre);
                if (computedStyle.position === 'static') { pre.style.position = 'relative'; }

                copyBtn.addEventListener('click', () => {
                    const code = pre.querySelector('code');
                    const textToCopy = code ? code.innerText : pre.innerText;
                    
                    navigator.clipboard.writeText(textToCopy).then(() => {
                        const originalHtml = copyBtn.innerHTML;
                        copyBtn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Copied!';
                        copyBtn.classList.add('copied');
                        setTimeout(() => {
                            copyBtn.innerHTML = originalHtml;
                            copyBtn.classList.remove('copied');
                        }, 2000);
                    }).catch(err => { console.error('Failed to copy text: ', err); });
                });
                
                pre.appendChild(copyBtn);
            });

            // --- Theme Toggle Logic ---
            const themeToggleBtn = document.getElementById('theme-toggle');
            const moonIcon = document.querySelector('.moon-icon');
            const sunIcon = document.querySelector('.sun-icon');
            
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
