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
</style>

<?=view('partials/footer') ?>

</body>
</html>
