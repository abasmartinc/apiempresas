<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<?=view('partials/header') ?>

<main>
    <!-- HERO -->
    <section class="hero container" id="inicio">
        <div class="grid">
            <div>
                <span class="pill top">Datos oficiales · Valida CIF/NIF · API REST + Buscador</span>

                <h1 class="title">
                    Valida un CIF y verifica una empresa española en segundos
                    <span class="grad">con datos oficiales y trazables</span>.
                </h1>

                <p class="subtitle">
                    <strong>Comprueba CIF, razón social y estado</strong> con información de
                    <strong>BOE/BORME, AEAT, INE y VIES</strong>.
                    Integra la verificación en tus flujos de <strong>KYB/KYC</strong>, onboarding,
                    facturación y scoring con una <strong>API REST</strong> lista para producción.
                </p>

                <div class="cta-row">
                    <a class="btn btn_start" href="<?=site_url() ?>register">Crear cuenta y obtener API Key (gratis)</a>
                    <a class="btn ghost" href="<?=site_url() ?>documentation">Ver documentación (OpenAPI/Swagger)</a>
                </div>

                <p class="muted" style="margin-top:10px;font-size:13px;">
                    Para SaaS, ERPs, CRMs y fintech que necesitan validar CIF y evitar altas con datos erróneos antes de facturar.
                </p>
            </div>

            <div class="code-card" aria-label="Ejemplo de respuesta">
                <div class="code-top">
                    <span>GET /company?cif=B12345678</span>
                    <span class="muted">200 OK • 142 ms</span>
                </div>
                <pre><code>{
  <span class="key">"success"</span>: <span class="str">true</span>,
  <span class="key">"data"</span>: {
    <span class="key">"name"</span>: <span class="str">"Producciones Martinez SL"</span>,
    <span class="key">"cif"</span>: <span class="str">"B85438414"</span>,
    <span class="key">"cnae"</span>: <span class="str">1200</span>,
    <span class="key">"cnae_label"</span>: <span class="str">"Actividades varias"</span>,
    <span class="key">"corporate_purpose"</span>: <span class="str">"Fabricación y distribución"</span>,
    <span class="key">"founded"</span>: <span class="str">"2008-07-02"</span>,
    <span class="key">"province"</span>: <span class="str">"Madrid"</span>,
    <span class="key">"status"</span>: <span class="str">"ACTIVA"</span>
  }
}</code></pre>
            </div>
        </div>
    </section>

    <!-- SEARCH -->
    <section id="buscar" class="search-section container">
        <svg class="hint-arrow-svg hint-arrow-svg--right" viewBox="0 0 220 110" aria-hidden="true">
            <defs>
                <linearGradient id="ve-g" x1="0" y1="0" x2="1" y2="1">
                    <stop stop-color="#2152FF" />
                    <stop offset=".65" stop-color="#5C7CFF" />
                    <stop offset="1" stop-color="#12B48A" />
                </linearGradient>
            </defs>
            <path class="path"
                  d="M5,75 C60,10 150,10 205,45"
                  fill="none" stroke="url(#ve-g)"
                  stroke-width="5" stroke-linecap="round" />
            <path class="arrow"
                  d="M200,38 l15,6 -13,10"
                  fill="none" stroke="url(#ve-g)"
                  stroke-width="5" stroke-linecap="round" stroke-linejoin="round" />
        </svg>

        <div class="search-card">
            <div>
                <h2>Validar CIF online: prueba el buscador (mismo motor que la API)</h2>
                <p class="muted">
                    Introduce un <strong>CIF/NIF</strong> o el <strong>nombre de la empresa</strong>.
                    Verás el resultado en una ficha clara y puedes consultar el <strong>JSON exacto</strong> que devuelve la API.
                </p>
            </div>

            <div class="search-row">
                <input class="input" id="q" placeholder="Ej. B12345678 o “Gestiones López”" aria-label="Buscar empresa por nombre o CIF" />
                <button type="button" class="btn" id="btnBuscar" aria-label="Buscar">Validar CIF / Buscar empresa</button>
            </div>

            <div id="resultado" class="result"></div>

            <p class="muted">
                ¿Vas a automatizarlo en tu producto?
                <a href="<?=site_url() ?>register">Crea tu cuenta y copia tu API Key</a> para integrar la validación en minutos.
            </p>
        </div>
    </section>

    <!-- FEATURES -->
    <section id="caracteristicas" class="container">
        <div class="band">
            <span class="eyebrow">Fuentes oficiales y trazabilidad</span>
            <h2>Datos listos para producción: valida CIF, razón social y estado de empresa</h2>

            <div class="features-stripe">
                <!-- LEFT -->
                <div class="feature-left">
                    <div class="feature-hero">
                        <h3 style="margin:0 0 6px; font-weight:800;">Fiables y rápidos para verificación de empresas</h3>
                        <p class="muted" style="margin:0; color:#cdd6ea">
                            Origen BOE/BORME, AEAT, INE y VIES. Respuestas rápidas con caché inteligente y referencia a la fuente para trazabilidad y auditoría.
                        </p>
                    </div>

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

                <!-- RIGHT -->
                <div class="feature-rows">
                    <div class="feature-row">
                        <div class="feat-badge feat-badge--bolt">⚡</div>
                        <div>
                            <h3>Pensada para developers</h3>
                            <p>
                                JSON limpio y normalizado (CIF, razón social, CNAE, estado, domicilio) con ejemplos listos en
                                PHP/Laravel, Node, Python y cURL.
                            </p>
                        </div>
                    </div>

                    <div class="feature-row">
                        <div class="feat-badge feat-badge--check">✔︎</div>
                        <div>
                            <h3>Validación y control de calidad</h3>
                            <p>
                                Reduce errores en altas y facturación: valida CIF y verifica datos clave para evitar registros duplicados o con información incorrecta.
                            </p>
                        </div>
                    </div>

                    <div class="feature-row">
                        <div class="feat-badge feat-badge--api">API</div>
                        <div>
                            <h3>Listo para producción</h3>
                            <p>
                                Endpoint REST unificado, rate-limits, logs de actividad y versiones estables para mantener integraciones robustas.
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
            <h2>Automatiza la verificación de empresas por CIF en tu producto</h2>
            <br />

            <div class="audience-lines">
                <div class="aud-line dev">
                    <h3>Developers & SaaS</h3>
                    <p>
                        Evita registros con datos erróneos: valida CIF en formularios de alta, billing y paneles internos.
                        Integra en minutos y automatiza el onboarding.
                    </p>
                </div>

                <div class="aud-line gest">
                    <h3>ERPs, CRMs y automatización</h3>
                    <p>
                        Enriquecimiento de fichas, deduplicación y validación previa a facturar:
                        consulta empresa por CIF y normaliza datos para conciliación.
                    </p>
                </div>

                <div class="aud-line fin">
                    <h3>Fintech, marketplaces y plataformas B2B</h3>
                    <p>
                        Automatiza KYB/KYC, reduce fraude en altas y guarda evidencia trazable con referencia a fuentes oficiales.
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
                <p>Regístrate y obtén al instante tu <strong>API Key gratuita</strong> para entorno de pruebas.</p>
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

        <br />
        <br />
    </section>

    <!-- PRICING -->
    <section id="precios" class="pricing container">
        <h2>Planes transparentes para cualquier volumen</h2>
        <p class="muted">
            Empieza validando CIF y razón social en Sandbox. Cuando lo lleves a producción, escala a Pro/Business con control de consumo y trazabilidad.
            Sin permanencias, sin costes ocultos.
        </p>

        <div class="tiers">
            <!-- PLAN FREE -->
            <div class="tier">
                <span class="badge">Sandbox</span>
                <h3>Free</h3>
                <p class="muted">Prueba la API con datos reales y valida resultados en un entorno de pruebas.</p>
                <div class="price">0 €</div>

                <ul class="tier__list">
                    <li>Acceso al mismo motor de producción</li>
                    <li>Datos oficiales para validar resultados</li>
                    <li>Perfecto para pruebas técnicas y POCs</li>
                    <li>Sin tarjeta de crédito</li>
                </ul>

                <p class="tier__note muted">
                    Pensado para desarrollo y validación. No recomendado para uso en producción.
                </p>

                <a class="btn secondary" href="<?=site_url() ?>register?plan=free">Empezar gratis</a>
            </div>

            <!-- PLAN PRO -->
            <div class="tier">
                <span class="badge">Recomendado</span>
                <h3>Pro</h3>
                <p class="muted">El plan estándar para SaaS, ERPs y productos en producción.</p>
                <div class="price">19 €/mes</div>

                <ul class="tier__list">
                    <li>Verificación completa y actualizada</li>
                    <li>Tiempo real, lista para automatización</li>
                    <li>Ideal para facturación y scoring</li>
                    <li>Logs y control de consumo</li>
                </ul>

                <p class="tier__note muted">
                    Para automatizar altas y facturación con trazabilidad.
                </p>

                <a class="btn secondary" href="<?=site_url() ?>register?plan=pro">Empezar con Pro</a>
            </div>

            <!-- PLAN BUSINESS -->
            <div class="tier">
                <h3>Business</h3>
                <p class="muted">Para plataformas con alto volumen y procesos críticos.</p>
                <div class="price">49 €/mes</div>

                <ul class="tier__list">
                    <li>Infraestructura para cargas elevadas</li>
                    <li>Para alto volumen y procesos críticos</li>
                    <li>SLA y alta disponibilidad</li>
                    <li>Soporte prioritario y alertas de uso</li>
                </ul>

                <p class="tier__note muted">
                    Pensado para fintech, marketplaces y plataformas críticas.
                </p>

                <a class="btn secondary" href="<?=site_url() ?>contact?interest=business">Empezar con Business</a>
            </div>
        </div>

        <div class="pricing__foot">
            <p class="pricing__hint">
                Cada consulta corresponde a una verificación completa de empresa en una sola respuesta. Sin costes ocultos ni llamadas parciales.
            </p>

            <div class="enterprise muted">
                <span>¿Más de <strong>20.000 consultas/mes</strong> o requisitos especiales de SLA/compliance?</span>
                <span><strong>Planes Enterprise a partir de 299 €/mes</strong>.</span>
                <a class="enterprise__link" href="<?=site_url() ?>contact?interest=enterprise">Hablemos de tu volumen y requisitos</a>
            </div>
        </div>
    </section>

    <!-- DOCS -->
    <section id="docs" class="dev container">
        <h2>API para validar CIF en España: documentación y ejemplos listos</h2>

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

    <!-- BLOG HOME -->
    <section class="home-blog container">
        <div class="band home-blog__band">
            <div class="home-blog__head">
                <span class="eyebrow">Recursos &amp; guías</span>
                <h2>Aprende a sacarle más partido a la API</h2>
                <p class="muted">
                    Guías técnicas y casos de uso reales para developers, producto y equipos de riesgo
                    que trabajan con datos de empresas españolas.
                </p>
            </div>

            <div class="home-blog__grid" id="home-blog-grid">
                <article class="home-blog__card home-blog__card--skeleton">
                    <div class="home-blog__skeleton-eyebrow skeleton-block"></div>
                    <div class="home-blog__skeleton-title skeleton-block"></div>
                    <div class="home-blog__skeleton-line skeleton-block"></div>
                    <div class="home-blog__skeleton-line skeleton-block"></div>
                    <div class="home-blog__skeleton-meta skeleton-block"></div>
                </article>

                <article class="home-blog__card home-blog__card--skeleton">
                    <div class="home-blog__skeleton-eyebrow skeleton-block"></div>
                    <div class="home-blog__skeleton-title skeleton-block"></div>
                    <div class="home-blog__skeleton-line skeleton-block"></div>
                    <div class="home-blog__skeleton-line skeleton-block"></div>
                    <div class="home-blog__skeleton-meta skeleton-block"></div>
                </article>

                <article class="home-blog__card home-blog__card--skeleton">
                    <div class="home-blog__skeleton-eyebrow skeleton-block"></div>
                    <div class="home-blog__skeleton-title skeleton-block"></div>
                    <div class="home-blog__skeleton-line skeleton-block"></div>
                    <div class="home-blog__skeleton-line skeleton-block"></div>
                    <div class="home-blog__skeleton-meta skeleton-block"></div>
                </article>

                <article class="home-blog__card home-blog__card--skeleton">
                    <div class="home-blog__skeleton-eyebrow skeleton-block"></div>
                    <div class="home-blog__skeleton-title skeleton-block"></div>
                    <div class="home-blog__skeleton-line skeleton-block"></div>
                    <div class="home-blog__skeleton-line skeleton-block"></div>
                    <div class="home-blog__skeleton-meta skeleton-block"></div>
                </article>
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

            <div class="faq-grid" style="margin-top: 18px; display: grid; gap: 14px;">
                <details class="faq-item" style="background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); border-radius: 16px; padding: 14px 16px;">
                    <summary style="cursor: pointer; font-weight: 700;">
                        ¿Cómo validar un CIF en España?
                    </summary>
                    <div class="muted" style="margin-top: 10px;">
                        Validar un CIF puede incluir dos cosas: comprobar el <strong>formato</strong> y, además, verificar datos de empresa
                        como <strong>razón social</strong> y <strong>estado</strong>. En APIEmpresas.es puedes hacerlo desde el buscador web
                        o automatizarlo por <strong>API REST</strong> con tu API Key.
                    </div>
                </details>

                <details class="faq-item" style="background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); border-radius: 16px; padding: 14px 16px;">
                    <summary style="cursor: pointer; font-weight: 700;">
                        ¿Qué diferencia hay entre validar CIF y verificar una empresa?
                    </summary>
                    <div class="muted" style="margin-top: 10px;">
                        “Validar CIF” suele referirse a la comprobación de <strong>estructura</strong> y consistencia.
                        “Verificar empresa” implica contrastar información relevante (p. ej., <strong>razón social</strong>,
                        <strong>estado</strong>, domicilio básico) para reducir errores en <strong>altas</strong> y <strong>facturación</strong>.
                    </div>
                </details>

                <details class="faq-item" style="background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); border-radius: 16px; padding: 14px 16px;">
                    <summary style="cursor: pointer; font-weight: 700;">
                        ¿Puedo comprobar un NIF-IVA intracomunitario (VIES)?
                    </summary>
                    <div class="muted" style="margin-top: 10px;">
                        Sí. Puedes validar el NIF-IVA intracomunitario contra <strong>VIES</strong> y usar el resultado en procesos
                        de onboarding, cumplimiento y validación fiscal en operaciones B2B.
                    </div>
                </details>

                <details class="faq-item" style="background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); border-radius: 16px; padding: 14px 16px;">
                    <summary style="cursor: pointer; font-weight: 700;">
                        ¿Para qué sirve en KYB/KYC y prevención de fraude?
                    </summary>
                    <div class="muted" style="margin-top: 10px;">
                        Para automatizar verificaciones, reducir altas con datos falsos o inconsistentes y mantener un rastro consultable
                        del dato verificado. Es especialmente útil si vendes a empresas (B2B), trabajas con pagos, crédito o acceso a producto.
                    </div>
                </details>

                <details class="faq-item" style="background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); border-radius: 16px; padding: 14px 16px;">
                    <summary style="cursor: pointer; font-weight: 700;">
                        ¿La API es adecuada para producción?
                    </summary>
                    <div class="muted" style="margin-top: 10px;">
                        Sí. Tienes <strong>API estable</strong>, control de consumo y planes preparados para producción.
                        Puedes empezar en Sandbox, validar tu integración y escalar a Pro/Business cuando lo necesites.
                    </div>
                </details>

                <details class="faq-item" style="background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); border-radius: 16px; padding: 14px 16px;">
                    <summary style="cursor: pointer; font-weight: 700;">
                        ¿Qué datos devuelvo y qué obtengo en una consulta?
                    </summary>
                    <div class="muted" style="margin-top: 10px;">
                        Una consulta devuelve una ficha normalizada en JSON con los campos esenciales para automatización (CIF, razón social,
                        estado, CNAE y datos básicos). El objetivo es integrarlo rápido en formularios de alta, ERPs/CRMs y flujos de riesgo.
                    </div>
                </details>

                <details class="faq-item" style="background: rgba(255,255,255,.06); border: 1px solid rgba(255,255,255,.08); border-radius: 16px; padding: 14px 16px;">
                    <summary style="cursor: pointer; font-weight: 700;">
                        ¿Cómo integro la API para validar CIF?
                    </summary>
                    <div class="muted" style="margin-top: 10px;">
                        Creas una cuenta, obtienes tu <strong>API Key</strong> y llamas al endpoint REST. En la documentación tienes ejemplos
                        listos en cURL, PHP/Laravel, Node y Python para copiar y pegar.
                        <div style="margin-top: 10px;">
                            <a class="btn secondary" href="<?=site_url() ?>documentation">Ver documentación</a>
                            <a class="btn btn_start" style="margin-left: 8px;" href="<?=site_url() ?>register">Crear cuenta gratis</a>
                        </div>
                    </div>
                </details>
            </div>
        </div>
    </section>


    <!-- CTA FINAL -->
    <section id="beta" class="cta-final container">
        <div class="cta-box">
            <div class="cta-layout">
                <div class="cta-copy">
                    <h2>Empieza hoy a validar CIF con tu API Key (gratis)</h2>

                    <p class="muted">
                        Regístrate en minutos y prueba la API con el mismo motor que usamos en producción.
                        Ideal para validar CIF, razón social y estado de empresas españolas sin fricción.
                    </p>

                    <ul class="cta-benefits">
                        <li><strong>Sin tarjeta</strong> ni permanencias: cancela cuando quieras.</li>
                        <li><strong>200 consultas/mes</strong> incluidas en el plan Sandbox.</li>
                        <li><strong>Acceso al buscador web</strong> y a la documentación completa.</li>
                    </ul>
                </div>

                <div class="cta-actions">
                    <span class="cta-pill">Sandbox · Entorno de pruebas</span>

                    <a class="btn btn_start cta-main-btn" href="<?=site_url() ?>register">
                        Obtén tu API Key
                    </a>

                    <p class="muted cta-note">
                        Sin tarjeta, sin compromiso. Solo necesitas tu email.
                    </p>

                    <p class="muted cta-login">
                        ¿Ya tienes cuenta?
                        <a href="<?=site_url() ?>login">Inicia sesión aquí</a>.
                    </p>
                </div>
            </div>
        </div>
    </section>
</main>


<?=view('partials/footer') ?>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const grid = document.getElementById('home-blog-grid');
        if (!grid) return;

        fetch('<?=site_url() ?>blog/get_posts', {
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
    });
</script>
</body>
</html>
