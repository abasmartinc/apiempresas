<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/docs.css?v=' . time()) ?>" />

</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header') ?>


<main class="docs-main">
    <div class="container">
        <div class="docs-layout">
            <!-- SIDEBAR -->
            <aside class="docs-sidebar">
                <div class="docs-sidebar-section">
                    <h3>General</h3>
                    <ul class="docs-nav">
                        <li>
                            <a href="#intro" class="active">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path><polyline points="9 22 9 12 15 12 15 22"></polyline></svg>
                                Introducción
                            </a>
                        </li>
                        <li>
                            <a href="#auth">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                Autenticación
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
                                Consulta por CIF
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-search">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                Búsqueda
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-expanded">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                Comercial <span class="sidebar-badge pro">Pro</span>
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-radar">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                Radar <span class="sidebar-badge pro">Pro</span>
                            </a>
                        </li>
                        <li>
                            <a href="#endpoint-webhooks">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 11a9 9 0 0 1 9 9"></path><path d="M4 4a16 16 0 0 1 16 16"></path><circle cx="5" cy="19" r="1"></circle></svg>
                                Webhooks <span class="sidebar-badge biz">Biz</span>
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="docs-sidebar-section">
                    <h3>Recursos</h3>
                    <ul class="docs-nav">
                        <li>
                            <a href="#examples">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                Ejemplos
                            </a>
                        </li>
                        <li>
                            <a href="#postman">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                                Postman
                            </a>
                        </li>
                    </ul>
                </div>
            </aside>

            <!-- CONTENIDO -->
            <div class="docs-content">
                <h1>Documentación de la API</h1>
                <p>Bienvenido a la documentación oficial de <strong>APIEmpresas.es</strong>. Nuestra API te permite consultar datos mercantiles actualizados de empresas españolas de forma rápida y sencilla.</p>

                <?php if (session('logged_in') && isset($user) && ($user->requests_count ?? 0) > 3 && !($user->hasRadar())): ?>
                    <div class="conditional-banner" style="background: #fef2f2; border-color: #fecaca; border-left: 5px solid #ef4444;">
                        <div class="conditional-banner-text">
                            <h4 style="color: #991b1b;">⚠️ Ya has hecho <?= $user->requests_count ?> búsquedas manuales</h4>
                            <p style="color: #b91c1c; font-weight: 700;">Podrías haber recibido estas empresas automáticamente.</p>
                        </div>
                        <a href="<?= site_url('radar') ?>" class="btn-radar-strong" style="padding: 12px 24px; font-size: 1rem;">Ver Radar</a>
                    </div>
                <?php endif; ?>

                <!-- USE CASES -->
                <div class="use-case-box">
                    <h3>💡 ¿Qué puedes hacer con esta API?</h3>
                    <div class="use-case-grid">
                        <div class="use-case-card">
                            <h4>🔍 Validar empresas</h4>
                            <p>Consulta datos por CIF en tiempo real de forma automatizada.</p>
                        </div>
                        <div class="use-case-card">
                            <h4>💎 Enriquecer datos</h4>
                            <p>Obtén información completa de empresas para tus sistemas de gestión.</p>
                        </div>
                        <div class="use-case-card">
                            <h4>📈 Generar leads</h4>
                            <p>Detecta nuevas empresas y oportunidades de negocio automáticamente.</p>
                            <a href="<?= site_url('radar') ?>" class="btn-link">Ver Radar &rarr;</a>
                        </div>
                    </div>
                </div>

                <!-- INTRO -->
                <section class="docs-section" id="intro">
                    <h2>1. Introducción</h2>
                    <p>
                        La API está diseñada siguiendo principios REST. Todas las respuestas se devuelven en formato JSON y requieren una conexión segura vía HTTPS.
                    </p>
                    <div class="api-info-card">
                        <strong>Base URL:</strong> <code>https://apiempresas.es/api/v1</code>
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
    "cnae_label": "Actividades de programación informática"
  }
}</code></pre>

                    <!-- MONEY BLOCK -->
                    <?= view('components/money_block') ?>

                    <?= view('components/radar_strong_cta', ['user' => $user ?? null]) ?>
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

                    <?= view('components/binary_decision') ?>
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
                    </div>

                    <!-- SIGNALS -->
                    <div style="margin-top: 32px;">
                        <div class="endpoint-header">
                            <span class="http-badge get">GET</span>
                            <code>/companies/signals</code>
                            <span class="plan-badge pro">Pro</span>
                        </div>
                        <p>Eventos y actos societarios detectados recientemente.</p>
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
    "conversion_probability": "Alta"
  }
}</code></pre>
                    </div>

                    <!-- CONTACT PREP -->
                    <div style="margin-top: 32px;">
                        <div class="endpoint-header">
                            <span class="http-badge get">GET</span>
                            <code>/companies/contact-prep</code>
                            <span class="plan-badge business">Business</span>
                        </div>
                        <p>Pitch de venta sugerido y manejo de objeciones generado por IA.</p>
                    </div>
                </section>

                <!-- RADAR -->
                <section class="docs-section" id="endpoint-radar">
                    <h2>6. Radar de Empresas</h2>
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
                </section>

                <!-- WEBHOOKS -->
                <section class="docs-section" id="endpoint-webhooks">
                    <h2>7. Webhooks (Solo Business)</h2>
                    <p>Recibe notificaciones automáticas en tiempo real en tu sistema cuando detectemos nuevas empresas o señales.</p>
                    
                    <div class="endpoint-header" style="margin-bottom: 5px;">
                        <span class="http-badge get">GET</span>
                        <code>/webhooks</code>
                        <span class="plan-badge business">Business</span>
                    </div>
                    <div class="endpoint-header">
                        <span class="http-badge post">POST</span>
                        <code>/webhooks</code>
                        <span class="plan-badge business">Business</span>
                    </div>
                </section>

                <!-- EXAMPLES -->
                <section class="docs-section" id="examples">
                    <h2>8. Ejemplos de Código</h2>
                    <p>Implementa la conexión en minutos con estos ejemplos listos para usar.</p>

                    <div class="code-tabs">
                        <h3>PHP (cURL)</h3>
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

                        <h3>Laravel (HTTP Client)</h3>
                        <pre><code class="language-php">use Illuminate\Support\Facades\Http;

$response = Http::withHeaders([
    'X-API-KEY' => 'TU_API_KEY'
])->get('https://apiempresas.es/api/v1/companies', [
    'cif' => 'B12345678'
]);

if ($response->successful()) {
    $data = $response->json();
}</code></pre>

                        <h3>CodeIgniter 4</h3>
                        <pre><code class="language-php">$client = \Config\Services::curlrequest();

$response = $client->request('GET', 'https://apiempresas.es/api/v1/companies', [
    'headers' => [
        'X-API-KEY' => 'TU_API_KEY',
        'Accept'    => 'application/json'
    ],
    'query' => ['cif' => 'B12345678']
]);

$data = json_decode($response->getBody(), true);</code></pre>

                        <h3>Node.js (Fetch)</h3>
                        <pre><code class="language-js">const fetch = require('node-fetch');

const getCompany = async (cif) => {
  const response = await fetch('https://apiempresas.es/api/v1/companies?cif=' + cif, {
    headers: { 'X-API-KEY': 'TU_API_KEY' }
  });
  const data = await response.json();
  console.log(data);
};</code></pre>

                        <h3>Python (Requests)</h3>
                        <pre><code class="language-python">import requests

url = "https://apiempresas.es/api/v1/companies"
params = {"cif": "B12345678"}
headers = {"X-API-KEY": "TU_API_KEY"}

response = requests.get(url, params=params, headers=headers)
print(response.json())</code></pre>

                        <h3>JavaScript (Fetch Browser)</h3>
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

                <!-- POSTMAN -->
                <section class="docs-section" id="postman">
                    <h2>9. Postman Collection</h2>
                    <p>Si prefieres probar la API directamente en Postman, puedes descargarte nuestra colección oficial e importarla con un clic.</p>
                    
                    <div style="background: #f8fafc; padding: 20px; border-radius: 12px; border: 1px dashed #cbd5e1; text-align: center; margin-top: 20px;">
                        <img src="https://www.postman.com/assets/postman-logo-stacked.svg" alt="Postman" style="height: 40px; margin-bottom: 15px;">
                        <p style="margin-bottom: 20px; color: #475569;">Incluye todos los endpoints configurados, ejemplos de respuestas y variables de entorno.</p>
                        <a href="<?= base_url('public/docs/apiempresas_postman.json') ?>" download class="btn primary" style="display: inline-flex; align-items: center; gap: 8px;">
                            <span>📥 Descargar Colección Postman</span>
                        </a>
                    </div>

                    <div style="margin-top: 80px; text-align: center; background: linear-gradient(135deg, #0F172A 0%, #1E3A8A 100%); color: white; padding: 60px 40px; border-radius: 32px; box-shadow: 0 25px 50px -12px rgba(30, 58, 138, 0.25);">
                        <h2 style="color: white; font-size: 2.3rem; font-weight: 900; margin-bottom: 16px; letter-spacing: -0.02em;">💎 Cada día se crean nuevas empresas</h2>
                        <p style="font-size: 1.25rem; color: rgba(255,255,255,0.7); margin-bottom: 32px; font-weight: 500;">La diferencia es quién llega primero.</p>
                        <a href="<?= site_url('radar') ?>" class="btn-radar-strong" style="max-width: 400px; margin: 0 auto; padding: 20px 40px;">Ver oportunidades ahora</a>
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
    .use-case-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px; }
    .use-case-card { padding: 20px; border-radius: 12px; background: #f8fafc; border: 1px solid #f1f5f9; transition: transform 0.2s; }
    .use-case-card:hover { transform: translateY(-2px); border-color: #cbd5e1; }
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
        .use-case-grid { grid-template-columns: 1fr; }
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

</body>
</html>
