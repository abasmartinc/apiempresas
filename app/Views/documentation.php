<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/docs.css') ?>" />

</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<?=view('partials/header') ?>

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
                <p>Integra VerificaEmpresas.es en tus sistemas para validar CIF, estado mercantil y datos básicos de empresas españolas.</p>

                <!-- INTRO -->
                <section class="docs-section" id="intro">
                    <h2>1. Introducción</h2>
                    <p>
                        El API de VerificaEmpresas.es está pensado para usarse desde tu backend
                        (PHP, Node, Python, etc.) y devolver información mercantil en tiempo real.
                    </p>
                    <p>
                        Todas las peticiones se realizan sobre HTTPS y devuelven respuestas en formato
                        <code class="inline">application/json</code>.
                    </p>
                    <p>
                        Base URL recomendada:
                    </p>
                    <pre><code>https://api.verificaempresas.es/v1</code></pre>
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
Host: api.verificaempresas.es
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
                    <pre><code class="language-bash">composer require verificaempresas/sdk-php</code></pre>

                    <h3>4.2 Ejemplo rápido (Laravel)</h3>
                    <pre><code class="language-php">&lt;?php

use VerificaEmpresas\Client;

$client = new Client(env('VERIFICAEMPRESAS_API_KEY'));

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
                    <pre><code class="language-bash">npm install @verificaempresas/sdk</code></pre>

                    <h3>5.2 Ejemplo rápido (Node)</h3>
                    <pre><code class="language-js">import { VerificaEmpresas } from "@verificaempresas/sdk";

const client = new VerificaEmpresas(process.env.VE_API_KEY);

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
