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
            <!-- ICONO APIEMPRESAS (check limpio, sin triángulo) -->
            <a href="<?=site_url() ?>">
                <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <!-- Degradado de marca -->
                        <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#2152FF"/>
                            <stop offset=".65" stop-color="#5C7CFF"/>
                            <stop offset="1" stop-color="#12B48A"/>
                        </linearGradient>
                        <!-- Halo del bloque -->
                        <filter id="ve-cardShadow" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20"/>
                        </filter>
                        <!-- Sombra suave del check (no genera triángulos) -->
                        <filter id="ve-checkShadow" x="-30%" y="-30%" width="160%" height="160%">
                            <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="#0B1A36" flood-opacity=".22"/>
                        </filter>
                        <!-- Brillo muy leve arriba-izquierda -->
                        <radialGradient id="ve-gloss" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(20 16) rotate(45) scale(28)">
                            <stop stop-color="#FFFFFF" stop-opacity=".32"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity="0"/>
                        </radialGradient>
                        <!-- Aro exterior para definir borde en fondos muy claros -->
                        <linearGradient id="ve-rim" x1="12" y1="52" x2="52" y2="12">
                            <stop stop-color="#FFFFFF" stop-opacity=".6"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity=".35"/>
                        </linearGradient>
                    </defs>

                    <!-- Tarjeta con halo + brillo sutil -->
                    <g filter="url(#ve-cardShadow)">
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)"/>
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss)"/>
                        <rect x="6.5" y="6.5" width="51" height="51" rx="13.5" fill="none" stroke="url(#ve-rim)"/>
                    </g>

                    <!-- Check principal sin trazo oscuro debajo, con sombra de filtro -->
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
            <a class="minor" href="<?=site_url() ?>billing">Planes y facturación</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="<?=site_url() ?>usage">Consumo</a>
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
                    <li><a href="#company">Endpoint /company</a></li>
                    <li><a href="#sdk-php">SDK PHP / Laravel</a></li>
                    <li><a href="#sdk-node">SDK Node / JS</a></li>
                </ul>
            </aside>

            <!-- CONTENIDO -->
            <div class="docs-content">
                <h1>Documentación del API</h1>
                <p>Integra APIEmpresas.es en tus sistemas para validar CIF, estado mercantil y datos básicos de empresas españolas.</p>

                <!-- INTRO -->
                <section class="docs-section" id="intro">
                    <h2>1. Introducción</h2>
                    <p>
                        El API de APIEmpresas.es está pensado para usarse desde tu backend
                        (PHP, Node, Python, etc.) y devolver información mercantil en tiempo real.
                    </p>
                    <p>
                        Todas las peticiones se realizan sobre HTTPS y devuelven respuestas en formato
                        <code class="inline">application/json</code>.
                    </p>
                    <p>
                        Base URL recomendada:
                    </p>
                    <pre><code>https://api.apiempresas.es/v1</code></pre>
                </section>

                <!-- AUTH -->
                <section class="docs-section" id="auth">
                    <h2>2. Autenticación</h2>
                    <p>
                        La autenticación se realiza mediante una cabecera
                        <code class="inline">Authorization: Bearer &lt;API_KEY&gt;</code>.
                        Puedes ver y rotar tu clave desde el panel.
                    </p>
                    <pre><code class="language-http">GET /v1/company?cif=B12345678 HTTP/1.1
Host: api.apiempresas.es
Authorization: Bearer sk_live_xxxxxxxx
Accept: application/json
</code></pre>
                </section>

                <!-- COMPANY -->
                <section class="docs-section" id="company">
                    <h2>3. Endpoint <code class="inline">GET /company</code></h2>

                    <h3>3.1 Descripción</h3>
                    <p>
                        Devuelve la ficha básica de una empresa a partir de su CIF/NIF. Ideal para
                        onboarding de clientes, scoring de riesgo o comprobaciones de compliance.
                    </p>

                    <p>
                        <span class="http-badge">GET</span>
                        <code class="inline">/company?cif={CIF}</code>
                    </p>

                    <h3>3.2 Parámetros</h3>
                    <ul style="font-size:14px; color:#4b5563; padding-left:18px;">
                        <li><strong>cif</strong> (obligatorio): CIF/NIF de la empresa, sin espacios.</li>
                        <li><strong>live</strong> (opcional, bool): si se fuerza actualización en tiempo real.</li>
                    </ul>

                    <h3>3.3 Ejemplo de respuesta</h3>
                    <pre><code class="language-json">{
  "cif": "B12345678",
  "name": "EMPRESA DEMO SL",
  "status": "activa",
  "province": "Madrid",
  "municipality": "Madrid",
  "registry": {
    "tomo": "12345",
    "libro": "0",
    "folio": "12",
    "hoja": "M-123456"
  },
  "sources": ["BORME", "AEAT", "VIES"]
}</code></pre>

                    <h3>3.4 Códigos de error</h3>
                    <ul style="font-size:14px; color:#4b5563; padding-left:18px;">
                        <li><strong>400</strong>: parámetro <code class="inline">cif</code> ausente o inválido.</li>
                        <li><strong>401</strong>: API key incorrecta o ausente.</li>
                        <li><strong>404</strong>: no se ha encontrado ninguna empresa para ese CIF.</li>
                        <li><strong>429</strong>: límite de peticiones excedido.</li>
                    </ul>
                </section>

                <!-- SDK PHP -->
                <section class="docs-section" id="sdk-php">
                    <h2>4. SDK PHP / Laravel</h2>
                    <p>Ejemplo mínimo para validar un CIF desde PHP o Laravel.</p>

                    <h3>4.1 Instalación</h3>
                    <pre><code class="language-bash">composer require apiempresas/sdk-php</code></pre>

                    <h3>4.2 Ejemplo rápido (Laravel)</h3>
                    <pre><code class="language-php">&lt;?php

use APIEmpresas\Client;

$client = new Client(env('APIEMPRESAS_API_KEY'));

$response = $client-&gt;company([
    'cif' =&gt; 'B12345678',
]);

if ($response-&gt;ok()) {
    $data = $response-&gt;json();
    // $data['status'], $data['name'], etc.
}</code></pre>
                    <p>
                        Puedes ver más ejemplos (middlewares, validación de formularios, jobs en cola) en
                        <a href="/docs/sdk-php">/docs/sdk-php</a>.
                    </p>
                </section>

                <!-- SDK NODE -->
                <section class="docs-section" id="sdk-node">
                    <h2>5. SDK Node / JavaScript</h2>
                    <p>Uso típico en un backend Node (Express, Nest, serverless, etc.).</p>

                    <h3>5.1 Instalación</h3>
                    <pre><code class="language-bash">npm install @apiempresas/sdk</code></pre>

                    <h3>5.2 Ejemplo rápido (Node)</h3>
                    <pre><code class="language-js">import { APIEmpresas } from "@apiempresas/sdk";

const client = new ApiEmpresas(process.env.VE_API_KEY);

const company = await client.company({ cif: "B12345678" });

if (company.status === "activa") {
  console.log("Empresa activa:", company.name);
}</code></pre>
                </section>
            </div>
        </div>
    </div>
</main>

<?=view('partials/footer') ?>

</body>
</html>
