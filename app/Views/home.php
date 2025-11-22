<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>VerificaEmpresas.es — API de verificación de empresas españolas</title>
    <meta name="description" content="API REST y buscador para verificar empresas españolas en segundos. Datos del BORME, AEAT, INE y VIES listos para tus flujos KYC/KYB, onboarding y facturación." />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="<?= base_url('public/css/styles.css') ?>" />
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<header>
    <div class="container nav">
        <div class="brand">
            <!-- ICONO VERIFICAEMPRESAS (check limpio, sin triángulo) -->
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


            <div class="brand-text">
                <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                <span class="brand-tag">Verificación empresarial en segundos</span>
            </div>
        </div>

        <nav class="desktop-only" aria-label="Principal">
            <a class="minor" href="#buscar">Buscar</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="#caracteristicas">Características</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="#precios">Precios</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="#docs">Docs</a>
        </nav>
        <div class="desktop-only">
            <a class="btn btn_header" href="<?=site_url() ?>register">Crear cuenta gratis</a>
        </div>
    </div>
</header>

<main>
    <section class="hero container">
        <div class="grid">
            <div>
                <span class="pill top">Datos verificados · API REST + Buscador</span>
                <h1 class="title">
                    La API para verificar en segundos la información real de
                    <span class="grad">cualquier empresa española</span>.
                </h1>
                <p class="subtitle">
                    <strong>Integra verificación mercantil en tus flujos en minutos.</strong>
                    API REST y buscador con datos oficiales de
                    <strong>BOE/BORME, AEAT, INE y VIES</strong>, pensados para onboarding, KYC/KYB,
                    facturación y scoring de clientes.
                </p>
                <div class="cta-row">
                    <a class="btn" href="#beta">🚀 Empezar gratis (sin tarjeta)</a>
                    <a class="btn ghost" href="#docs">Ver documentación</a>
                </div>
                <p class="muted" style="margin-top:10px;font-size:13px;">
                    Ideal para SaaS, fintech, ERPs y productos B2B que necesitan validar CIF y razón social en tiempo real.
                </p>
            </div>

            <div class="code-card" aria-label="Ejemplo de respuesta">
                <div class="code-top">
                    <span>GET /company?cif=B12345678</span>
                    <span class="muted">200 OK • 142 ms</span>
                </div>
                <pre>
                <code>
{
  <span class="key">"success"</span>: <span class="str">true</span>,
  <span class="key">"data"</span>: {
    <span class="key">"cif"</span>: <span class="str">"B85438414"</span>,
    <span class="key">"name"</span>: <span class="str">"Producciones Martinez SL"</span>,
    <span class="key">"status"</span>: <span class="str">"ACTIVA"</span>,
    <span class="key">"province"</span>: <span class="str">"Madrid"</span>,
    <span class="key">"cnae"</span>: <span class="str">"Actividades varias"</span>,
    <span class="key">"founded"</span>: <span class="str">"2008-07-02"</span>,
    <span class="key">"registry_url"</span>: <span class="str">""</span>
  }
} </code>
                </pre>
            </div>
        </div>
    </section>


    <!-- SEARCH -->
    <section id="buscar" class="search-section container">
        <svg class="hint-arrow-svg hint-arrow-svg--right" viewBox="0 0 220 110" aria-hidden="true">
            <defs>
                <linearGradient id="ve-g" x1="0" y1="0" x2="1" y2="1">
                    <stop stop-color="#2152FF"/>
                    <stop offset=".65" stop-color="#5C7CFF"/>
                    <stop offset="1"  stop-color="#12B48A"/>
                </linearGradient>
            </defs>
            <path class="path"
                  d="M5,75 C60,10 150,10 205,45"
                  fill="none" stroke="url(#ve-g)"
                  stroke-width="5" stroke-linecap="round"/>
            <path class="arrow"
                  d="M200,38 l15,6 -13,10"
                  fill="none" stroke="url(#ve-g)"
                  stroke-width="5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>

        <div class="search-card ">
            <div>
                <h2>Prueba el buscador — mismo motor que la API</h2>
                <p class="muted">
                    Introduce un <strong>CIF</strong> o nombre comercial. Verás el resultado en tarjetas limpias
                    y puedes consultar el JSON que devuelve la API.
                </p>
            </div>

            <div class="search-row">
                <input class="input" id="q" placeholder="Ej. Gestiones López o B12345678" aria-label="Buscar empresa por nombre o CIF" />
                <button class="btn" id="btnBuscar" aria-label="Buscar">Buscar empresa</button>
            </div>

            <!-- 💡 Este div es imprescindible para que el JS pinte resultados -->
            <div id="resultado" class="result"></div>

            <p class="muted">
                ¿Quieres integrarlo en tu producto?
                <a href="<?=site_url() ?>register">Crear cuenta gratis">Crea tu cuenta gratuita y consigue tu API key</a>.
            </p>

        </div>
    </section>


    <!-- FEATURES -->
    <section id="caracteristicas" class="container">
        <div class="band">
            <span class="eyebrow">Datos oficiales</span>
            <h2>Listos para producción e integrables en minutos</h2>

            <div class="features-stripe">
                <!-- IZQUIERDA -->
                <div class="feature-left">
                    <div class="feature-hero">
                        <h3 style="margin:0 0 6px; font-weight:800;">Fiables y rápidos a nivel empresa</h3>
                        <p class="muted" style="margin:0; color:#cdd6ea">
                            Origen BOE/BORME, AEAT, INE y VIES. Respuestas &lt;200 ms con caché inteligente y enlace a la fuente oficial para trazabilidad.
                        </p>
                    </div>

                    <!-- Franja de métricas -->
                    <div class="ve-stat-strip" role="group" aria-label="Métricas del servicio">
                        <div class="ve-stat">
                            <span class="ve-stat__label">Latencia media</span>
                            <span class="ve-stat__value">~142 ms</span>
                        </div>

                        <span class="ve-stat__divider" aria-hidden="true"></span>

                        <div class="ve-stat">
                            <span class="ve-stat__label">Uptime objetivo</span>
                            <span class="ve-stat__value">99,9 %</span>
                        </div>

                        <span class="ve-stat__divider" aria-hidden="true"></span>

                        <div class="ve-stat">
                            <span class="ve-stat__label">Fuentes oficiales</span>
                            <span class="ve-stat__value ve-wrap" style="font-size: 14px;line-height: 20px;">
      BOE/BORME · AEAT · INE · VIES
    </span>
                        </div>
                    </div>

                    <div class="ve-stat ve-stat--sources">
                        <span class="ve-stat__label">Fuentes oficiales</span>
                        <div class="ve-sources-inline">
                            <span class="chip">BOE/BORME</span>
                            <span class="chip">AEAT</span>
                            <span class="chip">INE</span>
                            <span class="chip">VIES (UE)</span>
                        </div>
                    </div>
                </div>

                <!-- DERECHA -->
                <div class="feature-rows">
                    <div class="feature-row">
                        <div class="feat-badge feat-badge--bolt">⚡</div>
                        <div>
                            <h3>Pensada para developers</h3>
                            <p>
                                JSON limpio, campos normalizados (CIF, razón social, CNAE, estado, domicilio) y
                                ejemplos listos para copiar en PHP, Node y Python.
                            </p>
                        </div>
                    </div>
                    <div class="feature-row">
                        <div class="feat-badge feat-badge--check">✔︎</div>
                        <div>
                            <h3>Confiable</h3>
                            <p>Datos verificados con referencia a BOE/BORME, AEAT, INE y VIES. Ideal para flujos KYC/KYB y auditoría.</p>
                        </div>
                    </div>
                    <div class="feature-row">
                        <div class="feat-badge feat-badge--api">API</div>
                        <div>
                            <h3>Listo para producción</h3>
                            <p>
                                Endpoint REST unificado, control de rate-limits, logs de actividad y
                                versiones de API estables para no romper tus integraciones.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>



    <!-- AUDIENCES -->
    <section class="audiences container">
        <div class="band">
            <span class="eyebrow">Casos de uso</span>
            <h2>Conecta en tu stack y automatiza la verificación de empresas</h2>
            <br/>
            <div class="audience-lines">
                <div class="aud-line dev">
                    <h3>Developers & SaaS</h3>
                    <p>
                        Valida CIF y razón social en formularios de alta, billing y paneles internos.
                        Integra la API en minutos con SDKs y ejemplos listos.
                    </p>
                </div>
                <div class="aud-line gest">
                    <h3>ERPs, CRMs y herramientas de automatización</h3>
                    <p>
                        Enriquecimiento de fichas de cliente, detección de duplicados,
                        validación previa a la facturación y conciliación de datos.
                    </p>
                </div>
                <div class="aud-line fin">
                    <h3>Fintech, marketplaces y plataformas B2B</h3>
                    <p>
                        Automatiza procesos KYB/KYC, reduce fraude en altas y genera evidencias con
                        enlace directo a documentación oficial.
                    </p>
                </div>
            </div>
        </div>
    </section>



    <!-- HOW -->
    <section class="how container">
        <h2>Empieza en menos de 5 minutos</h2>
        <div class="steps">
            <div class="card">
                <span class="pill mini-pill">1</span>
                <h3>Crea tu cuenta</h3>
                <p>Regístrate y obtén al instante tu <strong>API key gratuita</strong> para entorno de pruebas.</p>
            </div>
            <div class="card">
                <span class="pill mini-pill">2</span>
                <h3>Conecta la API</h3>
                <p>
                    Llama al endpoint REST desde tu backend o frontend.
                    Ejemplos en PHP/Laravel, Node, Python y cURL listos para copiar.
                </p>
            </div>
            <div class="card">
                <span class="pill mini-pill">3</span>
                <h3>Escala cuando lo necesites</h3>
                <p>
                    Pasa del plan gratuito a Pro o Business cuando tu volumen de verificaciones crezca.
                    Sin permanencias ni costes ocultos.
                </p>
            </div>
        </div>
        <br/>
        <br/>
    </section>

    <section id="precios" class="pricing container">
        <h2>Planes transparentes para cualquier volumen</h2>
        <p class="muted" >
            Empieza gratis en minutos y escala solo cuando tu producto lo necesite. Sin permanencias, sin costes ocultos.
        </p>

        <div class="tiers">
            <!-- PLAN FREE -->
            <div class="tier">
                <span class="badge">Sandbox</span>
                <h3>Free</h3>
                <p class="muted">Para probar la API y desarrollo</p>
                <div class="price">0 €</div>
                <ul class="list">
                    <li>2.000 consultas/mes</li>
                    <li>Acceso al buscador web</li>
                    <li>Logs básicos (24–48 h)</li>
                    <li>Soporte por email estándar</li>
                </ul>
                <a class="btn secondary" href="#beta">Crear cuenta gratis</a>
            </div>

            <!-- PLAN PRO -->
            <div class="tier">
                <h3>Pro</h3>
                <p class="muted">Para SaaS y startups en producción</p>
                <div class="price">39 €/mes</div>
                <ul class="list">
                    <li>50.000 consultas/mes</li>
                    <li>Logs y analítica 90 días</li>
                    <li>2 API keys (sandbox + producción)</li>
                    <li>SLA objetivo 99,9 %</li>
                </ul>
                <a class="btn secondary" href="#beta">Empezar con Pro</a>
            </div>

            <!-- PLAN BUSINESS -->
            <div class="tier">
                <h3>Business</h3>
                <p class="muted">Para plataformas con alto volumen</p>
                <div class="price">99 €/mes</div>
                <ul class="list">
                    <li>250.000 consultas/mes</li>
                    <li>Webhooks y exportaciones</li>
                    <li>IP allowlist y mayor QPS</li>
                    <li>Soporte prioritario (&lt; 24 h)</li>
                </ul>
                <a class="btn secondary" href="#beta">Hablar con ventas</a>
            </div>
        </div>

        <!-- ENTERPRISE -->
        <div style="text-align:center;margin-top:32px;font-size:14px;" class="muted">
            ¿Más de <strong>250.000 consultas/mes</strong> o requisitos especiales de SLA/compliance?
            <br/>
            <strong>Planes Enterprise a partir de 299 €/mes</strong>.
            <a href="#beta">Cuéntanos tu caso y te preparamos una propuesta a medida</a>.
        </div>
    </section>


    <section id="docs" class="dev container">
        <h2>Diseñada para developers y automatización empresarial</h2>
        <p>
            Documentación en español, <strong>OpenAPI/Swagger</strong>, SDKs oficiales y entorno de pruebas interactivo.
            Incluye ejemplos completos de integración para alta de clientes, flujos KYB y validación masiva.
        </p>
        <div class="code-card">
            <div class="code-top"><span>curl</span><span>Ejemplo rápido</span></div>
            <pre><code>curl -H "Authorization: Bearer &lt;API_KEY&gt;" \
"https://apiempresas.es/company?cif=B12345678"</code></pre>
        </div>
    </section>

    <!--<section id="beta" class="cta-final container">
        <div class="cta-box">
            <h2>Crea tu cuenta gratuita y recibe tu API key</h2>
            <p class="muted">
                Regístrate y te enviaremos al instante tu <strong>API key gratuita</strong> y acceso al buscador avanzado.
                Sin tarjeta, sin compromiso. Perfecto para probar la integración en tu entorno.
            </p>
            <form onsubmit="event.preventDefault(); document.getElementById('beta-ok').style.display='block'" class="search-row">
                <input class="input" type="email" placeholder="tu@correo.com" required />
                <button class="btn" type="submit">🚀 Crear cuenta gratis</button>
            </form>
            <p id="beta-ok" class="muted" style="display:none; margin-top:8px">
                ✅ ¡Listo! Te enviaremos tu API key y los siguientes pasos a tu correo.
            </p>
        </div>
    </section>-->
</main>

<footer >
    <div class="container">
        <div class="foot-grid">
            <div>
                <div class="brand">
                    <!-- ICONO VERIFICAEMPRESAS (check limpio, sin triángulo) -->
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


                    <div class="brand-text">
                        <span class="brand-name">Verifica<span class="grad">Empresas</span>.es</span>
                        <span class="brand-tag">API de verificación mercantil en segundos</span>
                    </div>
                </div>
                <p>
                    Datos procedentes de fuentes públicas: BOE/BORME, AEAT, INE, VIES.
                    No somos servicio oficial del BOE. Cumplimiento RGPD (Art. 14) y
                    reutilización de información del sector público.
                </p>
            </div>
            <div>
                <h4 style="margin:0 0 8px">Producto</h4>
                <a class="minor" href="#buscar">Buscador</a><br />
                <a class="minor" href="#docs">Documentación</a><br />
                <a class="minor" href="#precios">Precios</a>
            </div>
            <div>
                <h4 style="margin:0 0 8px">Legal</h4>
                <a class="minor" href="#">Privacidad</a><br />
                <a class="minor" href="#">Términos</a><br />
                <a class="minor" href="#">Contacto</a>
            </div>
        </div>
    </div>
</footer>
<?= view('scripts') ?>

</body>
</html>

