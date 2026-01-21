<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/docs.css') ?>" />

</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<header>
    <div class="container nav">
        <div class="brand">
            <a href="<?=site_url() ?>">
                <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#2152FF"/>
                            <stop offset=".65" stop-color="#5C7CFF"/>
                            <stop offset="1" stop-color="#12B48A"/>
                        </linearGradient>
                        <filter id="ve-cardShadow" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20"/>
                        </filter>
                        <filter id="ve-checkShadow" x="-30%" y="-30%" width="160%" height="160%">
                            <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="#0B1A36" flood-opacity=".22"/>
                        </filter>
                        <radialGradient id="ve-gloss" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(20 16) rotate(45) scale(28)">
                            <stop stop-color="#FFFFFF" stop-opacity=".32"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity="0"/>
                        </radialGradient>
                        <linearGradient id="ve-rim" x1="12" y1="52" x2="52" y2="12">
                            <stop stop-color="#FFFFFF" stop-opacity=".6"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity=".35"/>
                        </linearGradient>
                    </defs>

                    <g filter="url(#ve-cardShadow)">
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)"/>
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss)"/>
                        <rect x="6.5" y="6.5" width="51" height="51" rx="13.5" fill="none" stroke="url(#ve-rim)"/>
                    </g>

                    <path d="M18 33 L28 43 L46 22"
                          stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"
                          fill="none" filter="url(#ve-checkShadow)"/>
                </svg>
            </a>

            <div class="brand-text">
                <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                <span class="brand-tag">Verificación empresarial en segundos</span>
            </div>
        </div>

        <nav class="desktop-only" aria-label="Principal">
            <a class="minor" href="<?=site_url() ?>dashboard">Dashboard</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="<?=site_url() ?>documentation">Documentación</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="<?=site_url() ?>search_company">Buscador</a>
        </nav>

        <div class="desktop-only">
            <?php if(!session('logged_in')){ ?>
                <a class="btn btn_header btn_header--ghost" href="<?=site_url() ?>enter">
                    <span>Iniciar sesión</span>
                </a>
            <?php } else { ?>
                <a class="btn btn_header btn_header--ghost logout" href="<?=site_url() ?>logout">
                    <span>Salir</span>
                </a>
            <?php } ?>
        </div>
    </div>
</header>


<main class="docs-main">
    <div class="container">
        <div class="docs-layout">
            <!-- SIDEBAR -->
            <aside class="docs-sidebar">
                <h2>Índice</h2>
                <ul class="docs-nav">
                    <li><a href="#intro">Introducción</a></li>
                    <li><a href="#auth">Autenticación</a></li>
                    <li><a href="#endpoint-by-cif">Consulta por CIF</a></li>
                    <li><a href="#endpoint-search">Búsqueda por Nombre</a></li>
                    <li><a href="#examples">Ejemplos de Código</a></li>
                    <li><a href="#postman">Postman Collection</a></li>
                </ul>
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

                <!-- EXAMPLES -->
                <section class="docs-section" id="examples">
                    <h2>5. Ejemplos de Código</h2>
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
                    <h2>6. Postman Collection</h2>
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
    .endpoint-header { display: flex; align-items: center; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px solid #e2e8f0; margin-bottom: 20px; }
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
