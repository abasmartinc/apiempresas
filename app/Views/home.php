<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head') ?>
</head>

<body><div class="bg-halo" aria-hidden="true"></div><?= view('partials/header', [], ['debug' => false]) ?>

    <main>
        <!-- HERO -->
        <section class="hero container" id="inicio">
            <div class="grid">
                <div>
                    <span class="pill top">Datos oficiales · Valida CIF/NIF · API REST + Buscador</span>

                    <h1 class="title">
                        <a href="<?= site_url() ?>autocompletado-cif-empresas" class="pill"
                            style="margin-bottom:15px; background:rgba(33,82,255,0.1); border-color:rgba(33,82,255,0.2); text-decoration:none;">
                            <span class="dot"></span> Nuevo: Autocompletado Predictivo Inteligente
                        </a><br>
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
                        <a class="btn btn_start" href="<?= site_url('register/quick') ?>">Crear cuenta y obtener API Key
                            (gratis)</a>
                        <a class="btn ghost" href="<?= site_url() ?>documentation">Ver documentación
                            (OpenAPI/Swagger)</a>
                    </div>

                    <p class="muted" style="margin-top:10px;font-size:13px;">
                        Para SaaS, ERPs, CRMs y fintech que necesitan validar CIF y evitar altas con datos erróneos
                        antes de facturar.
                    </p>
                </div>

                <div class="code-card" aria-label="Ejemplo de respuesta">
                    <div class="code-top">
                        <span>GET /api/v1/companies?cif=B12345678</span>
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
                <path class="path" d="M5,75 C60,10 150,10 205,45" fill="none" stroke="url(#ve-g)" stroke-width="5"
                    stroke-linecap="round" />
                <path class="arrow" d="M200,38 l15,6 -13,10" fill="none" stroke="url(#ve-g)" stroke-width="5"
                    stroke-linecap="round" stroke-linejoin="round" />
            </svg>

            <div class="search-card">
                <div>
                    <h2>Validar CIF online: prueba el buscador (mismo motor que la API)</h2>
                    <p class="muted">
                        Introduce un <strong>CIF/NIF</strong> o el <strong>nombre de la empresa</strong>.
                        Verás el resultado en una ficha clara y puedes consultar el <strong>JSON exacto</strong> que
                        devuelve la API.
                    </p>
                </div>

                <div class="search-row">
                    <input class="input" id="q" placeholder="Ej. B12345678 o “Gestiones López”"
                        aria-label="Buscar empresa por nombre o CIF" />
                    <button type="button" class="btn" id="btnBuscar" aria-label="Buscar">Validar CIF / Buscar
                        empresa</button>
                </div>

                <div style="margin-top:12px; display:flex; align-items:center; gap:8px;">
                    <span class="pill mini-pill" style="font-size:10px; padding:2px 8px;">TIP</span>
                    <p class="muted" style="font-size:13px; margin:0;">
                        ¿Buscas más velocidad? Prueba el <a href="<?= site_url() ?>autocompletado-cif-empresas"
                            style="color:var(--primary); font-weight:700; text-decoration:underline;">Autocompletado
                            Predictivo Pro</a>.
                    </p>
                </div>

                <div id="resultado" class="result"></div>

                <div id="resultado" class="result"></div>

                <p class="muted">
                    ¿Vas a automatizarlo en tu producto?
                    <a href="<?= site_url('register/quick') ?>">Crea tu cuenta y copia tu API Key</a> para integrar la validación
                    en minutos.
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
                            <h3 style="margin:0 0 6px; font-weight:800;">Fiables y rápidos para verificación de empresas
                            </h3>
                            <p class="muted" style="margin:0; color:#cdd6ea">
                                Origen BOE/BORME, AEAT, INE y VIES. Respuestas rápidas con caché inteligente y
                                referencia a la fuente para trazabilidad y auditoría.
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
                                    JSON limpio y normalizado (CIF, razón social, CNAE, estado, domicilio) con ejemplos
                                    listos en
                                    PHP/Laravel, Node, Python y cURL.
                                </p>
                            </div>
                        </div>

                        <div class="feature-row">
                            <div class="feat-badge feat-badge--check">✔︎</div>
                            <div>
                                <h3>Validación y control de calidad</h3>
                                <p>
                                    Reduce errores en altas y facturación: valida CIF y verifica datos clave para evitar
                                    registros duplicados o con información incorrecta.
                                </p>
                            </div>
                        </div>

                        <div class="feature-row">
                            <div class="feat-badge feat-badge--api">API</div>
                            <div>
                                <h3>Listo para producción</h3>
                                <p>
                                    Endpoint REST unificado, rate-limits, logs de actividad y versiones estables para
                                    mantener integraciones robustas.
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
                            Evita registros con datos erróneos: valida CIF en formularios de alta, billing y paneles
                            internos.
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
                            Automatiza KYB/KYC, reduce fraude en altas y guarda evidencia trazable con referencia a
                            fuentes oficiales.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CUSTOM INTEGRATIONS -->
        <section id="custom-integrations" class="container" style="margin-top: 40px; margin-bottom: 40px;">
            <div class="band"
                style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border-radius: 24px; padding: 60px 40px; color: #0f172a; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05); border: 1px solid #e2e8f0;">

                <!-- Decorative background elements -->
                <div
                    style="position: absolute; top: 0; right: 0; width: 400px; height: 400px; background: radial-gradient(circle, rgba(33, 82, 255, 0.08) 0%, transparent 70%); pointer-events: none;">
                </div>
                <div
                    style="position: absolute; bottom: 0; left: 0; width: 300px; height: 300px; background: radial-gradient(circle, rgba(18, 180, 138, 0.05) 0%, transparent 70%); pointer-events: none;">
                </div>

                <div class="grid"
                    style="grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; position: relative; z-index: 1;">

                    <!-- Content -->
                    <div>
                        <span class="eyebrow"
                            style="color: #2152ff; border-color: rgba(33, 82, 255, 0.2); background: rgba(33, 82, 255, 0.03);">Enterprise
                            & Medida</span>
                        <h2 style="color: #0f172a; margin-top: 10px; margin-bottom: 20px; font-size: 2.5rem;">
                            Integraciones a medida y Soluciones Enterprise</h2>
                        <p style="color: #475569; font-size: 1.1rem; line-height: 1.6; margin-bottom: 30px;">
                            No nos limitamos a la API estándar. Adaptamos nuestra tecnología para que se fusione
                            perfectamente con tu infraestructura y lógica de negocio.
                        </p>

                        <ul style="list-style: none; padding: 0; margin-bottom: 40px; display: grid; gap: 20px;">
                            <li style="display: flex; gap: 15px; align-items: flex-start;">
                                <div
                                    style="background: rgba(33, 82, 255, 0.1); color: #2152ff; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 4px rgba(33, 82, 255, 0.1);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="16 18 22 12 16 6"></polyline>
                                        <polyline points="8 6 2 12 8 18"></polyline>
                                    </svg>
                                </div>
                                <div>
                                    <h4 style="color: #0f172a; margin: 0 0 5px 0; font-size: 1.1rem; font-weight: 700;">
                                        Endpoints Personalizados</h4>
                                    <p style="color: #64748b; margin: 0; font-size: 0.95rem;">¿Necesitas datos
                                        específicos cruzados o lógica de negocio en la respuesta? Construimos endpoints
                                        exclusivos para tu caso de uso.</p>
                                </div>
                            </li>
                            <li style="display: flex; gap: 15px; align-items: flex-start;">
                                <div
                                    style="background: rgba(18, 180, 138, 0.1); color: #12b48a; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 4px rgba(18, 180, 138, 0.1);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                </div>
                                <div>
                                    <h4 style="color: #0f172a; margin: 0 0 5px 0; font-size: 1.1rem; font-weight: 700;">
                                        Validación Batch / Masiva</h4>
                                    <p style="color: #64748b; margin: 0; font-size: 0.95rem;">Procesa miles de registros
                                        vía CSV, Excel o API asíncrona de alto rendimiento. Ideal para limpieza de CRMs.
                                    </p>
                                </div>
                            </li>
                            <li style="display: flex; gap: 15px; align-items: flex-start;">
                                <div
                                    style="background: rgba(245, 158, 11, 0.1); color: #d97706; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 4px rgba(245, 158, 11, 0.1);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="3"></circle>
                                        <path
                                            d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1 0 2.83 2 2 0 0 1-2.83 0l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-2 2 2 2 0 0 1-2-2v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83 0 2 2 0 0 1 0-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1-2-2 2 2 0 0 1 2-2h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 0-2.83 2 2 0 0 1 2.83 0l.06.06a1.65 1.65 0 0 0 1.82.33H9a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 2-2 2 2 0 0 1 2 2v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 0 2 2 0 0 1 0 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82V9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 2 2 2 2 0 0 1-2 2h-.09a1.65 1.65 0 0 0-1.51 1z">
                                        </path>
                                    </svg>
                                </div>
                                <div>
                                    <h4 style="color: #0f172a; margin: 0 0 5px 0; font-size: 1.1rem; font-weight: 700;">
                                        Conectores ERP/CRM</h4>
                                    <p style="color: #64748b; margin: 0; font-size: 0.95rem;">Integración nativa con
                                        SAP, Microsoft Dynamics, Salesforce, HubSpot y plataformas no-code.</p>
                                </div>
                            </li>
                        </ul>

                        <a class="btn" href="<?= site_url() ?>contact?interest=custom_integration"
                            style="background: linear-gradient(90deg, #2152ff, #12b48a); color: white; border: none; font-weight: 700; padding: 15px 30px; border-radius: 12px; box-shadow: 0 8px 16px rgba(33, 82, 255, 0.15);">
                            Hablemos de tu proyecto
                        </a>
                    </div>

                    <!-- Visual/Image -->
                    <div style="position: relative; display: flex; justify-content: center; align-items: center;">
                        <div
                            style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 30px; width: 100%; max-width: 450px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.1);">
                            <div
                                style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; border-bottom: 1px solid #f1f5f9; padding-bottom: 15px;">
                                <div style="display: flex; gap: 8px;">
                                    <div
                                        style="width: 10px; height: 10px; border-radius: 50%; background: #ff5f56; box-shadow: 0 0 0 1px #e0443e;">
                                    </div>
                                    <div
                                        style="width: 10px; height: 10px; border-radius: 50%; background: #ffbd2e; box-shadow: 0 0 0 1px #dea123;">
                                    </div>
                                    <div
                                        style="width: 10px; height: 10px; border-radius: 50%; background: #27c93f; box-shadow: 0 0 0 1px #1aab29;">
                                    </div>
                                </div>
                                <span
                                    style="font-family: 'JetBrains Mono', monospace; font-size: 11px; color: #94a3b8; letter-spacing: 0.5px;">POST
                                    /api/v1/custom/verify-batch</span>
                            </div>
                            <pre
                                style="font-family: 'JetBrains Mono', monospace; font-size: 13px; line-height: 1.6; color: #334155; margin: 0; background: transparent; padding: 0;">
<span style="color: #7c3aed;">{</span>
  <span style="color: #2563eb;">"job_id"</span>: <span style="color: #0891b2;">"job_8923_batch_kyb"</span>,
  <span style="color: #2563eb;">"status"</span>: <span style="color: #059669;">"completed"</span>,
  <span style="color: #2563eb;">"processed"</span>: <span style="color: #dc2626;">15420</span>,
  <span style="color: #2563eb;">"fields"</span>: <span style="color: #7c3aed;">[</span>
    <span style="color: #0891b2;">"risk_score_v2"</span>,
    <span style="color: #0891b2;">"erp_match"</span>
  <span style="color: #7c3aed;">]</span>
<span style="color: #7c3aed;">}</span></pre>
                        </div>
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
                Empieza validando CIF y razón social en Sandbox. Cuando lo lleves a producción, escala a Pro/Business
                con control de consumo y trazabilidad.
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

                    <a class="btn secondary" href="<?= site_url('register/quick?plan=free') ?>">Empezar gratis</a>
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

                    <a class="btn secondary" href="<?= site_url('checkout/radar-export?type=subscription&plan=pro&period=monthly') ?>">Empezar con Pro</a>
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

                    <a class="btn secondary" href="<?= site_url('checkout/radar-export?type=subscription&plan=business&period=monthly') ?>">Empezar con Business</a>
                </div>
            </div>

            <div class="pricing__foot">
                <p class="pricing__hint">
                    Cada consulta corresponde a una verificación completa de empresa en una sola respuesta. Sin costes
                    ocultos ni llamadas parciales.
                </p>

                <div class="enterprise muted">
                    <span>¿Más de <strong>20.000 consultas/mes</strong> o requisitos especiales de
                        SLA/compliance?</span>
                    <span><strong>Planes Enterprise a partir de 299 €/mes</strong>.</span>
                    <a class="enterprise__link" href="<?= site_url() ?>contact?interest=enterprise">Hablemos de tu
                        volumen y requisitos</a>
                </div>
            </div>
        </section>

        <!-- DOCS -->
        <section id="docs" class="dev container">
            <h2>API para validar CIF en España: documentación y ejemplos listos</h2>

            <p>
                Documentación en español, <strong>OpenAPI/Swagger</strong>, SDKs oficiales y entorno de pruebas
                interactivo.
                Incluye ejemplos completos de integración para alta de clientes, flujos KYB y validación masiva.
            </p>

            <div class="code-card">
                <div class="code-top"><span>curl</span><span>Ejemplo rápido</span></div>
                <pre><code>curl -H "Authorization: Bearer &lt;API_KEY&gt;" \
"https://apiempresas.es/api/v1/companies?cif=B12345678"</code></pre>
            </div>
        </section>
        <!-- RADAR LEAD CAPTURE -->
        <section id="radar-leads" class="container" style="margin-top: 40px; margin-bottom: 40px;">
            <div class="band"
                style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 24px; padding: 60px 40px; color: #0f172a; position: relative; overflow: hidden; box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05); border: 1px solid #bfdbfe;">

                <!-- Decorative background elements -->
                <div
                    style="position: absolute; top: -50px; right: -50px; width: 300px; height: 300px; background: radial-gradient(circle, rgba(33, 82, 255, 0.1) 0%, transparent 70%); pointer-events: none;">
                </div>
                <div
                    style="position: absolute; bottom: -50px; left: -50px; width: 200px; height: 200px; background: radial-gradient(circle, rgba(29, 78, 216, 0.05) 0%, transparent 70%); pointer-events: none;">
                </div>

                <div class="grid"
                    style="grid-template-columns: 1fr 1fr; gap: 60px; align-items: center; position: relative; z-index: 1;">

                    <!-- Content -->
                    <div>
                        <span class="eyebrow"
                            style="color: #2152ff; border-color: rgba(33, 82, 255, 0.2); background: rgba(33, 82, 255, 0.1);">APIEmpresas
                            Radar</span>
                        <h2
                            style="color: #0f172a; margin-top: 10px; margin-bottom: 20px; font-size: 2.5rem; letter-spacing: -0.02em; font-weight: 800; line-height: 1.1;">
                            ¿Trabajas B2B? Descubre a tus clientes antes que nadie</h2>
                        <p style="color: #475569; font-size: 1.1rem; line-height: 1.6; margin-bottom: 30px;">
                            Cada semana se constituyen cientos de nuevas empresas en España. Sé el primero en ofrecerles
                            tus servicios (asesoría, marketing, software, suministros).
                        </p>

                        <ul style="list-style: none; padding: 0; margin-bottom: 40px; display: grid; gap: 16px;">
                            <li style="display: flex; gap: 12px; align-items: center;">
                                <div
                                    style="background: rgba(33, 82, 255, 0.15); color: #2152ff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </div>
                                <span style="color: #334155; font-size: 1rem; font-weight: 500;">Alerta semanal gratuita
                                    en tu correo.</span>
                            </li>
                            <li style="display: flex; gap: 12px; align-items: center;">
                                <div
                                    style="background: rgba(33, 82, 255, 0.15); color: #2152ff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </div>
                                <span style="color: #334155; font-size: 1rem; font-weight: 500;">Filtrado directo por tu
                                    provincia.</span>
                            </li>
                            <li style="display: flex; gap: 12px; align-items: center;">
                                <div
                                    style="background: rgba(33, 82, 255, 0.15); color: #2152ff; width: 28px; height: 28px; border-radius: 50%; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="20 6 9 17 4 12"></polyline>
                                    </svg>
                                </div>
                                <span style="color: #334155; font-size: 1rem; font-weight: 500;">Datos oficiales recién
                                    salidos del BORME.</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Form Card -->
                    <div style="position: relative; display: flex; justify-content: center; align-items: center;">
                        <style>
                            .radar-form-card {
                                background: #ffffff; 
                                border: 1px solid rgba(255,255,255,0.5); 
                                border-radius: 24px; 
                                padding: 40px 32px; 
                                width: 100%; 
                                max-width: 450px; 
                                box-shadow: 0 20px 40px -10px rgba(33, 82, 255, 0.15), 0 0 0 1px rgba(33, 82, 255, 0.05);
                                position: relative;
                                backdrop-filter: blur(10px);
                            }
                            
                            .radar-input-group {
                                position: relative;
                                margin-bottom: 24px;
                                width: 100%;
                            }
                            .radar-input-group label {
                                display: block; 
                                font-size: 0.85rem; 
                                font-weight: 600; 
                                color: #475569; 
                                margin-bottom: 8px;
                                transition: color 0.2s;
                            }
                            .radar-input-group input.radar-input {
                                width: 100%; 
                                box-sizing: border-box;
                                display: block;
                                border: 1.5px solid #e2e8f0; 
                                border-radius: 12px; 
                                padding: 14px 16px 14px 44px;
                                font-size: 1rem; 
                                background: #f8fafc; 
                                color: #1e293b;
                                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                line-height: 1.5;
                                margin: 0;
                            }
                            .radar-input-group input.radar-input::placeholder {
                                color: #94a3b8;
                            }
                            .radar-input-group input.radar-input:focus {
                                outline: none;
                                border-color: #2152ff;
                                background: #ffffff;
                                box-shadow: 0 0 0 4px rgba(33, 82, 255, 0.1);
                            }
                            .radar-input-group:focus-within label {
                                color: #2152ff;
                            }
                            .radar-input-icon {
                                position: absolute;
                                left: 16px;
                                top: 48px;
                                color: #94a3b8;
                                transition: color 0.3s;
                                pointer-events: none;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                            }
                            .radar-input-group:focus-within .radar-input-icon {
                                color: #2152ff;
                            }
                            .radar-submit-btn {
                                width: 100%; 
                                background: linear-gradient(135deg, #2152ff 0%, #0f2c9c 100%); 
                                color: white; 
                                border: none; 
                                font-weight: 700; 
                                padding: 16px; 
                                border-radius: 12px; 
                                font-size: 1.05rem; 
                                box-shadow: 0 10px 20px rgba(33, 82, 255, 0.3); 
                                cursor: pointer; 
                                transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                                position: relative;
                                overflow: hidden;
                                display: flex;
                                justify-content: center;
                                align-items: center;
                                gap: 8px;
                            }
                            .radar-submit-btn::before {
                                content: '';
                                position: absolute;
                                top: 0;
                                left: -100%;
                                width: 100%;
                                height: 100%;
                                background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
                                transition: left 0.7s ease;
                            }
                            .radar-submit-btn:hover {
                                transform: translateY(-2px);
                                box-shadow: 0 14px 28px rgba(33, 82, 255, 0.4);
                            }
                            .radar-submit-btn:hover::before {
                                left: 100%;
                            }
                            .radar-submit-btn:active {
                                transform: translateY(1px);
                                box-shadow: 0 4px 10px rgba(33, 82, 255, 0.3);
                            }
                        </style>
                        <div class="radar-form-card">
                            <h3 style="font-size: 1.5rem; color: #0f172a; margin-top: 0; margin-bottom: 12px; text-align: center; font-weight: 800; letter-spacing: -0.02em;">
                                Recibe nuevas empresas locales</h3>
                            <p style="text-align: center; color: #64748b; margin-bottom: 30px; font-size: 0.95rem; line-height: 1.5;">
                                Déjanos tu email y provincia. Te enviaremos el radar semanal directo a tu bandeja.
                            </p>

                            <!-- Using the same lead-form class and handleLeadSubmit logic -->
                            <form class="lead-form"
                                onsubmit="window.handleLeadSubmit && window.handleLeadSubmit(event, this)">
                                
                                <div class="radar-input-group">
                                    <label>Correo electrónico <span style="color:#ef4444">*</span></label>
                                    <div class="radar-input-icon">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                                            <polyline points="22,6 12,13 2,6"></polyline>
                                        </svg>
                                    </div>
                                    <input type="email" name="email" class="radar-input" placeholder="tu@empresa.com" required>
                                </div>

                                <div class="radar-input-group">
                                    <label>Provincia de interés <span style="color:#ef4444">*</span></label>
                                    <div class="radar-input-icon">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                    </div>
                                    <input type="text" name="province" class="radar-input" placeholder="Ej. Madrid, Barcelona..." required>
                                </div>

                                <input type="hidden" name="source" value="home_marketing_section">
                                <button type="submit" class="btn radar-submit-btn">
                                    <span>Apuntarme Gratis</span>
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                        <polyline points="12 5 19 12 12 19"></polyline>
                                    </svg>
                                </button>
                            </form>
                            
                            <div style="text-align: center; margin-top: 24px;">
                                <a href="<?= site_url() ?>empresas-nuevas"
                                    style="color: #64748b; font-size: 0.85rem; font-weight: 500; text-decoration: none; transition: color 0.2s; display: inline-flex; align-items: center; gap: 4px;"
                                    onmouseover="this.style.color='#2152ff'" onmouseout="this.style.color='#64748b'">
                                    Conoce más sobre APIEmpresas Radar
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="16" x2="12" y2="12"></line>
                                        <line x1="12" y1="8" x2="12.01" y2="8"></line>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>

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

                <!-- Two-column layout: FAQs + Sidebar -->
                <div class="faq-grid-container">

                    <!-- Left: FAQs -->
                    <div class="faq-grid" style="display: grid; gap: 14px;">
                        <details class="faq-item">
                            <summary>
                                ¿Cómo validar un CIF en España?
                            </summary>
                            <div class="muted" style="margin-top: 10px;">
                                Validar un CIF puede incluir dos cosas: comprobar el <strong>formato</strong> y, además,
                                verificar datos de empresa
                                como <strong>razón social</strong> y <strong>estado</strong>. En APIEmpresas.es puedes
                                hacerlo desde el buscador web
                                o automatizarlo por <strong>API REST</strong> con tu API Key.
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>
                                ¿Qué diferencia hay entre validar CIF y verificar una empresa?
                            </summary>
                            <div class="muted" style="margin-top: 10px;">
                                "Validar CIF" suele referirse a la comprobación de <strong>estructura</strong> y
                                consistencia.
                                "Verificar empresa" implica contrastar información relevante (p. ej., <strong>razón
                                    social</strong>,
                                <strong>estado</strong>, domicilio básico) para reducir errores en
                                <strong>altas</strong> y <strong>facturación</strong>.
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>
                                ¿Puedo comprobar un NIF-IVA intracomunitario (VIES)?
                            </summary>
                            <div class="muted" style="margin-top: 10px;">
                                Sí. Puedes validar el NIF-IVA intracomunitario contra <strong>VIES</strong> y usar el
                                resultado en procesos
                                de onboarding, cumplimiento y validación fiscal en operaciones B2B.
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>
                                ¿Para qué sirve en KYB/KYC y prevención de fraude?
                            </summary>
                            <div class="muted" style="margin-top: 10px;">
                                Para automatizar verificaciones, reducir altas con datos falsos o inconsistentes y
                                mantener un rastro consultable
                                del dato verificado. Es especialmente útil si vendes a empresas (B2B), trabajas con
                                pagos, crédito o acceso a producto.
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>
                                ¿La API es adecuada para producción?
                            </summary>
                            <div class="muted" style="margin-top: 10px;">
                                Sí. Tienes <strong>API estable</strong>, control de consumo y planes preparados para
                                producción.
                                Puedes empezar en Sandbox, validar tu integración y escalar a Pro/Business cuando lo
                                necesites.
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>
                                ¿Qué datos devuelvo y qué obtengo en una consulta?
                            </summary>
                            <div class="muted" style="margin-top: 10px;">
                                Una consulta devuelve una ficha normalizada en JSON con los campos esenciales para
                                automatización (CIF, razón social,
                                estado, CNAE y datos básicos). El objetivo es integrarlo rápido en formularios de alta,
                                ERPs/CRMs y flujos de riesgo.
                            </div>
                        </details>

                        <details class="faq-item">
                            <summary>
                                ¿Cómo integro la API para validar CIF?
                            </summary>
                            <div class="muted" style="margin-top: 10px;">
                                Creas una cuenta, obtienes tu <strong>API Key</strong> y llamas al endpoint REST. En la
                                documentación tienes ejemplos
                                listos en cURL, PHP/Laravel, Node y Python para copiar y pegar.
                                <div style="margin-top: 10px;">
                                    <a class="btn secondary" href="<?= site_url() ?>documentation">Ver documentación</a>
                                    <a class="btn btn_start" style="margin-left: 8px;"
                                        href="<?= site_url() ?>register">Crear cuenta gratis</a>
                                </div>
                            </div>
                        </details>
                    </div>

                    <!-- Right: Sidebar -->
                    <aside style="position: sticky; top: 100px; display: grid; gap: 20px;">

                        <!-- Stats Card - High Impact Design -->
                        <div
                            style="background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(16px); -webkit-backdrop-filter: blur(16px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 20px; padding: 28px; box-shadow: 0 20px 40px rgba(0,0,0,0.2), inset 0 0 20px rgba(255,255,255,0.02); position: relative; overflow: hidden;">

                            <!-- Background Accent Icon -->
                            <div
                                style="position: absolute; right: -20px; bottom: -20px; opacity: 0.03; transform: rotate(-15deg); pointer-events: none;">
                                <svg width="180" height="180" viewBox="0 0 24 24" fill="none" stroke="white"
                                    stroke-width="1">
                                    <path
                                        d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z">
                                    </path>
                                </svg>
                            </div>

                            <div
                                style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; position: relative; z-index: 1;">
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <div
                                            style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 8px #10b981; animation: pulse-green 2s infinite;">
                                        </div>
                                        <h3
                                            style="font-size: 0.85rem; margin: 0; color: rgba(255,255,255,0.7); font-weight: 700; letter-spacing: 1px; text-transform: uppercase;">
                                            PLATAFORMA</h3>
                                    </div>
                                </div>
                                <style>
                                    @keyframes pulse-green {
                                        0% {
                                            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
                                        }

                                        70% {
                                            box-shadow: 0 0 0 6px rgba(16, 185, 129, 0);
                                        }

                                        100% {
                                            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0);
                                        }
                                    }
                                </style>
                            </div>

                            <div style="position: relative; z-index: 1;">
                                <div style="margin-bottom: 24px;">
                                    <div
                                        style="font-size: 3rem; font-weight: 900; background: linear-gradient(135deg, #fff 30%, #60A5FA 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; color: transparent; line-height: 1; letter-spacing: -1px;">
                                        +3M</div>
                                    <div style="font-size: 0.9rem; color: #94a3b8; font-weight: 500; margin-top: 4px;">
                                        Empresas verificadas</div>
                                </div>

                                <div
                                    style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.08);">
                                    <div>
                                        <div
                                            style="font-size: 1.2rem; font-weight: 800; color: #fff; line-height: 1; margin-bottom: 4px;">
                                            99.9<span style="color: #60A5FA;">%</span></div>
                                        <div
                                            style="font-size: 0.7rem; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                                            Uptime</div>
                                    </div>
                                    <div>
                                        <div
                                            style="font-size: 1.2rem; font-weight: 800; color: #fff; line-height: 1; margin-bottom: 4px;">
                                            &lt;200<span style="color: #60A5FA;">ms</span></div>
                                        <div
                                            style="font-size: 0.7rem; color: rgba(255,255,255,0.4); text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">
                                            Respuesta</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- CTA Card - Premium Design -->
                        <div
                            style="background: linear-gradient(135deg, #2152ff 0%, #1a42cc 100%); border-radius: 20px; padding: 28px; position: relative; overflow: hidden; box-shadow: 0 8px 32px rgba(33, 82, 255, 0.3);">
                            <!-- Decorative gradient overlay -->
                            <div
                                style="position: absolute; top: -50%; right: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%); pointer-events: none;">
                            </div>

                            <div style="position: relative; z-index: 1; text-align: center;">
                                <h3 style="font-size: 1.2rem; margin: 0 0 10px 0; color: #fff; font-weight: 700;">¿Listo
                                    para empezar?</h3>
                                <p
                                    style="font-size: 0.9rem; color: rgba(255,255,255,0.85); margin: 0 0 24px 0; line-height: 1.6;">
                                    Obtén tu API Key gratis y comienza a validar CIF en minutos.</p>

                                <a href="<?= site_url('register') ?>"
                                    style="display: block; background: #fff; color: #2152ff; text-align: center; padding: 14px 24px; border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 0.95rem; transition: all 0.2s; box-shadow: 0 4px 12px rgba(0,0,0,0.15);"
                                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 20px rgba(0,0,0,0.2)'"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(0,0,0,0.15)'">
                                    Crear cuenta gratis →
                                </a>
                            </div>
                        </div>

                    </aside>

                </div>
            </div>
        </section>

        <!-- FAQ Schema for Rich Snippets -->
        <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "¿Cómo validar un CIF en España?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Validar un CIF puede incluir dos cosas: comprobar el formato y, además, verificar datos de empresa como razón social y estado. En APIEmpresas.es puedes hacerlo desde el buscador web o automatizarlo por API REST con tu API Key."
          }
        },
        {
          "@type": "Question",
          "name": "¿Qué diferencia hay entre validar CIF y verificar una empresa?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Validar CIF suele referirse a la comprobación de estructura y consistencia. Verificar empresa implica contrastar información relevante (p. ej., razón social, estado, domicilio básico) para reducir errores en altas y facturación."
          }
        },
        {
          "@type": "Question",
          "name": "¿Puedo comprobar un NIF-IVA intracomunitario (VIES)?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Sí. Puedes validar el NIF-IVA intracomunitario contra VIES y usar el resultado en procesos de onboarding, cumplimiento y validación fiscal en operaciones B2B."
          }
        },
        {
          "@type": "Question",
          "name": "¿Para qué sirve en KYB/KYC y prevención de fraude?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Para automatizar verificaciones, reducir altas con datos falsos o inconsistentes y mantener un rastro consultable del dato verificado. Es especialmente útil si vendes a empresas (B2B), trabajas con pagos, crédito o acceso a producto."
          }
        },
        {
          "@type": "Question",
          "name": "¿La API es adecuada para producción?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Sí. Tienes API estable, control de consumo y planes preparados para producción. Puedes empezar en Sandbox, validar tu integración y escalar a Pro/Business cuando lo necesites."
          }
        },
        {
          "@type": "Question",
          "name": "¿Qué datos devuelvo y qué obtengo en una consulta?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Una consulta devuelve una ficha normalizada en JSON con los campos esenciales para automatización (CIF, razón social, estado, CNAE y datos básicos). El objetivo es integrarlo rápido en formularios de alta, ERPs/CRMs y flujos de riesgo."
          }
        },
        {
          "@type": "Question",
          "name": "¿Cómo integro la API para validar CIF?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Creas una cuenta, obtienes tu API Key y llamas al endpoint REST. En la documentación tienes ejemplos listos en cURL, PHP/Laravel, Node y Python para copiar y pegar."
          }
        }
      ]
    }
    </script>


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

                        <a class="btn btn_start cta-main-btn" href="<?= site_url() ?>register">
                            Obtén tu API Key
                        </a>

                        <p class="muted cta-note">
                            Sin tarjeta, sin compromiso. Solo necesitas tu email.
                        </p>

                        <p class="muted cta-login">
                            ¿Ya tienes cuenta?
                            <a href="<?= site_url() ?>login">Inicia sesión aquí</a>.
                        </p>
                    </div>
                </div>
            </div>
        </section>
    </main>



    <!-- DIRECTORIO SEO (Very subtle for crawlers only) -->
    <section id="directory-links" class="container"
        style="padding: 30px 0 20px; border-top: 1px solid rgba(255,255,255,0.02);">
        <div class="band">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px;">
                <h3 style="font-size: 0.95rem; margin: 0; color: #475569; font-weight: 500;">Directorio por provincia
                </h3>
                <a href="<?= site_url('directorio') ?>"
                    style="color: #64748b; font-weight: 500; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; font-size: 0.8rem;">
                    Ver todo
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                        <polyline points="12 5 19 12 12 19"></polyline>
                    </svg>
                </a>
            </div>

            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(130px, 1fr)); gap: 6px;">
                <?php if (!empty($provinces)): ?>
                    <?php foreach ($provinces as $prov): ?>
                        <a href="<?= site_url('directorio/provincia/' . urlencode($prov['name'])) ?>"
                            style="color: #64748b; text-decoration: none; font-size: 12px; padding: 5px 8px; background: rgba(255,255,255,0.01); border-radius: 4px; border: 1px solid rgba(255,255,255,0.02); transition: all 0.2s;"
                            onmouseover="this.style.borderColor='rgba(33, 82, 255, 0.2)'; this.style.color='#94a3b8'; this.style.background='rgba(255,255,255,0.03)'"
                            onmouseout="this.style.borderColor='rgba(255,255,255,0.02)'; this.style.color='#64748b'; this.style.background='rgba(255,255,255,0.01)'">
                            <?= esc($prov['name']) ?>
                        </a>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- =========================
         MODAL · INTEGRACIÓN A MEDIDA (Marketing/WOW)
         ========================= -->
    <div class="modal-overlay" id="modalCustomIntegration" aria-hidden="true" data-prevent-overlay-close="true">
        <div class="modal modal-wow" role="dialog" aria-modal="true" aria-labelledby="customTitle" tabindex="-1"
            style="max-width: 860px; padding: 0; border: 1px solid rgba(0,0,0,0.08); background: #ffffff; border-radius: 28px; box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.15); overflow: hidden;">

            <!-- Full Width Header -->
            <div
                style="padding: 48px 56px 32px; text-align: center; border-bottom: 1px solid rgba(0,0,0,0.03); position: relative;">
                <div>
                    <div class="modal-kicker"
                        style="color: #12b48a; font-weight: 800; font-size: 13px; letter-spacing: 1.2px; text-transform: uppercase; margin-bottom: 10px;">
                        Servicio Exclusivo</div>
                    <h2 class="modal-title" id="customTitle"
                        style="font-size: 38px; line-height: 1.1; margin: 0; color: #0f172a; font-weight: 900; letter-spacing: -0.03em;">
                        Integración <span
                            style="background: linear-gradient(90deg, #2152ff, #12b48a); -webkit-background-clip: text; background-clip: text; color: transparent;">personalizada</span>
                    </h2>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1.2fr; min-height: 400px;">
                <!-- Left Column: Visual -->
                <div
                    style="background: linear-gradient(135deg, #f8faff 0%, #eef2ff 100%); position: relative; display: flex; align-items: center; justify-content: center; overflow: hidden; border-right: 1px solid rgba(0,0,0,0.03);">
                    <div
                        style="position: absolute; inset: 0; opacity: 0.4; background-image: radial-gradient(#2152ff 0.5px, transparent 0.5px); background-size: 20px 20px;">
                    </div>
                    <div
                        style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); width: 320px; height: 320px; background: radial-gradient(circle, rgba(33, 82, 255, 0.08) 0%, transparent 70%);">
                    </div>
                    <img src="<?= base_url('public/images/custom-integration.png') ?>"
                        alt="Icono de integración empresarial"
                        style="position: relative; z-index: 2; transition: transform 0.6s cubic-bezier(0.34, 1.56, 0.64, 1);"
                        onmouseover="this.style.transform='scale(1.08) rotate(3deg)'"
                        onmouseout="this.style.transform='scale(1) rotate(0deg)'">
                </div>

                <!-- Right Column: Content -->
                <div
                    style="padding: 48px 56px; display: flex; flex-direction: column; justify-content: center; background: rgba(255, 255, 255, 0.6); backdrop-filter: blur(20px); position: relative;">
                    <div class="modal-body" style="padding: 0; margin-bottom: 40px;">
                        <p
                            style="font-size: 17px; color: #475569; line-height: 1.7; margin-bottom: 24px; font-weight: 400;">
                            ¿Tu flujo de trabajo requiere algo único? Adaptamos nuestra tecnología para que se fusione
                            perfectamente con tu infraestructura actual.
                        </p>
                        <ul style="list-style: none; padding: 0; margin: 0; color: #334155; font-size: 15px;">
                            <li style="margin-bottom: 16px; display: flex; align-items: center; gap: 14px;">
                                <div
                                    style="flex-shrink: 0; width: 22px; height: 22px; background: #e0f2fe; border: 1px solid #bae6fd; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #0369a1;">
                                    <span style="font-size: 12px; font-weight: bold;">✓</span>
                                </div>
                                <span style="font-weight: 500;">Validaciones por lotes y Webhooks reales</span>
                            </li>
                            <li style="margin-bottom: 16px; display: flex; align-items: center; gap: 14px;">
                                <div
                                    style="flex-shrink: 0; width: 22px; height: 22px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #15803d;">
                                    <span style="font-size: 12px; font-weight: bold;">✓</span>
                                </div>
                                <span style="font-weight: 500;">Conectores nativos para ERP/CRM (SAP, Dynamics)</span>
                            </li>
                            <li style="margin-bottom: 0; display: flex; align-items: center; gap: 14px;">
                                <div
                                    style="flex-shrink: 0; width: 22px; height: 22px; background: #fff7ed; border: 1px solid #fed7aa; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #c2410c;">
                                    <span style="font-size: 12px; font-weight: bold;">✓</span>
                                </div>
                                <span style="font-weight: 500;">Enriquecimiento de datos a medida (KYB avanzado)</span>
                            </li>
                        </ul>
                    </div>

                    <div class="modal-footer"
                        style="padding: 0; border: none; justify-content: flex-start; gap: 16px; display: flex; align-items: center; flex-wrap: wrap;">
                        <a href="<?= site_url() ?>contact?interest=custom_integration" class="btn btn_start"
                            style="text-decoration:none; padding: 18px 32px; font-size: 16px; background: var(--primary); border: none; box-shadow: 0 15px 30px rgba(19, 58, 130, 0.2); border-radius: 16px; font-weight: 700; color: #fff; transition: all 0.3s; display: inline-block; white-space: nowrap;">
                            Consultar con un experto
                        </a>
                        <button class="modal-btn" type="button" data-close-modal
                            style="background: transparent; border: 1.5px solid #e2e8f0; color: #64748b; padding: 16px 26px; border-radius: 16px; font-weight: 600; cursor: pointer; transition: all 0.2s;">
                            Ahora no, gracias
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php if (isset($showReviewModal) && $showReviewModal): ?>
    <!-- =========================
         MODAL · REVIEW USUARIO
         ========================= -->
    <div class="modal-overlay active" id="modalUserReview" aria-hidden="false" data-prevent-overlay-close="true" style="z-index: 10000; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(8px);">
        <div class="modal" role="dialog" aria-modal="true" aria-labelledby="reviewTitle" tabindex="-1"
            style="max-width: 500px; padding: 0; background: #ffffff; border-radius: 24px; box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.25); overflow: hidden; position: relative;">
            
            <button class="modal-close" type="button" aria-label="Cerrar modal" onclick="closeReviewModal()"
                style="position: absolute; top: 16px; right: 16px; background: transparent; border: none; font-size: 28px; color: #94a3b8; cursor: pointer; transition: color 0.2s; line-height: 1;">
                &times;
            </button>

            <div style="padding: 40px 32px 32px; text-align: center;">
                <div style="width: 56px; height: 56px; background: #f0fdf4; border-radius: 50%; display: flex; align-items: center; justify-content: center; color: #16a34a; margin: 0 auto 20px;">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                    </svg>
                </div>
                <h2 id="reviewTitle" style="font-size: 24px; color: #0f172a; font-weight: 800; margin-bottom: 12px; letter-spacing: -0.02em;">¿Qué te parece APIEmpresas?</h2>
                <p style="color: #64748b; font-size: 15px; line-height: 1.6; margin-bottom: 24px;">Hemos notado que estás usando bastante nuestro buscador. Nos ayudaría mucho saber tu opinión para seguir mejorando.</p>
                
                <form id="reviewForm" onsubmit="submitReview(event)">
                    <!-- Stars -->
                    <div style="display: flex; justify-content: center; gap: 8px; margin-bottom: 24px;" id="starRatingContainer">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <button type="button" class="star-btn" data-value="<?= $i ?>" onclick="setRating(<?= $i ?>)" onmouseover="hoverRating(<?= $i ?>)" onmouseout="resetRating()"
                                style="background: transparent; border: none; cursor: pointer; padding: 0; color: #cbd5e1; transition: color 0.2s, transform 0.2s;">
                                <svg width="36" height="36" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="1.5" stroke-linejoin="round">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"></path>
                                </svg>
                            </button>
                        <?php endfor; ?>
                    </div>
                    <input type="hidden" name="rating" id="reviewRating" value="0" required>
                    
                    <textarea name="comment" id="reviewComment" placeholder="Comentarios, mejoras o funciones que eches en falta (opcional)..." rows="3"
                        style="width: 100%; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 14px; font-size: 14px; color: #1e293b; background: #f8fafc; resize: none; margin-bottom: 24px; font-family: inherit; transition: border-color 0.2s; box-sizing: border-box;"
                        onfocus="this.style.borderColor='#2152ff'" onblur="this.style.borderColor='#e2e8f0'"></textarea>
                        
                    <button type="submit" id="reviewSubmitBtn" disabled
                        style="width: 100%; background: linear-gradient(135deg, #2152ff 0%, #1d4ed8 100%); color: white; border: none; font-weight: 700; padding: 16px; border-radius: 12px; font-size: 1.05rem; cursor: not-allowed; opacity: 0.6; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 4px 14px rgba(33, 82, 255, 0.4);">
                        Enviar valoración
                    </button>
                </form>
                
                <div id="reviewSuccess" style="display: none; padding: 20px 0;">
                    <div style="color: #16a34a; font-size: 48px; margin-bottom: 16px;">✓</div>
                    <h3 style="font-size: 20px; color: #0f172a; margin-bottom: 8px;">¡Gracias por tu reseña!</h3>
                    <p style="color: #64748b; font-size: 15px;">Tu feedback es muy valioso para nosotros.</p>
                </div>
            </div>
        </div>
    </div>

    <script>
    let currentRating = 0;
    
    function setRating(rating) {
        currentRating = rating;
        document.getElementById('reviewRating').value = rating;
        updateStars(rating);
        
        const submitBtn = document.getElementById('reviewSubmitBtn');
        submitBtn.disabled = false;
        submitBtn.style.cursor = 'pointer';
        submitBtn.style.opacity = '1';
        submitBtn.style.transform = 'translateY(-2px)';
        submitBtn.style.boxShadow = '0 10px 20px rgba(33, 82, 255, 0.3)';
    }
    
    function hoverRating(rating) {
        updateStars(rating);
    }
    
    function resetRating() {
        updateStars(currentRating);
    }
    
    function updateStars(activeCount) {
        const stars = document.querySelectorAll('#starRatingContainer .star-btn');
        stars.forEach((star, index) => {
            if (index < activeCount) {
                star.style.color = '#fbbf24'; // yellow
                star.style.transform = 'scale(1.1)';
            } else {
                star.style.color = '#cbd5e1'; // gray
                star.style.transform = 'scale(1)';
            }
        });
    }
    
    function closeReviewModal() {
        const modal = document.getElementById('modalUserReview');
        modal.classList.remove('active');
        setTimeout(() => modal.style.display = 'none', 300);
        document.body.style.overflow = '';
    }
    
    function submitReview(e) {
        e.preventDefault();
        if(currentRating < 1) return;
        
        const btn = document.getElementById('reviewSubmitBtn');
        const form = document.getElementById('reviewForm');
        const success = document.getElementById('reviewSuccess');
        
        btn.innerHTML = 'Enviando...';
        btn.disabled = true;
        
        const formData = new FormData();
        formData.append('rating', currentRating);
        formData.append('comment', document.getElementById('reviewComment').value);
        
        fetch('<?= site_url('submit-review') ?>', {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.success) {
                form.style.display = 'none';
                success.style.display = 'block';
                setTimeout(() => {
                    closeReviewModal();
                }, 3000);
            }
        })
        .catch(err => {
            console.error(err);
            btn.innerHTML = 'Error al enviar. Reintentar';
            btn.disabled = false;
        });
    }
    document.body.style.overflow = 'hidden';
    </script>
    <?php endif; ?>

    <?= view('partials/footer') ?>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const grid = document.getElementById('home-blog-grid');
            if (!grid) return;

            // Only fetch if no posts were rendered server-side (empty or skeleton)
            if (grid.querySelectorAll('.home-blog__card:not(.home-blog__card--skeleton)').length > 0) {
                return;
            }

            fetch('<?= site_url() ?>blog/get_posts', {
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
            // Custom Integration Modal Trigger
            (function () {
                const MODAL_ID = 'modalCustomIntegration';
                const STORAGE_KEY = 've_custom_integration_modal_shown';
                const DELAY_MS = 8000; // 8 segundos
                const FREQUENCY_DAYS = 7;

                function shouldShow() {
                    const lastShown = localStorage.getItem(STORAGE_KEY);
                    if (!lastShown) return true;

                    const now = new Date().getTime();
                    const diffDays = (now - parseInt(lastShown)) / (1000 * 60 * 60 * 24);
                    return diffDays > FREQUENCY_DAYS;
                }

                const overlay = document.getElementById(MODAL_ID);
                if (!overlay || !shouldShow()) return;

                setTimeout(() => {
                    // Reutilizamos la función global openModal si está disponible, 
                    // o disparamos un click en un elemento dummy si fuera necesario.
                    // Sin embargo, como scripts.php define openModal dentro de un IIFE,
                    // la forma más limpia es replicar la lógica o exponerla.
                    // Dado el diseño de scripts.php, usaremos un trigger programático:

                    overlay.classList.add('active');
                    overlay.setAttribute('aria-hidden', 'false');
                    document.body.style.overflow = 'hidden';

                    const dialog = overlay.querySelector('.modal');
                    if (dialog) dialog.focus({ preventScroll: true });

                    // Guardar persistencia
                    localStorage.setItem(STORAGE_KEY, new Date().getTime().toString());
                }, DELAY_MS);
            })();
        });
    </script>
</body>

</html>