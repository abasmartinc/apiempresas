<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/dashboard.css') ?>" />
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
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
                <a class="minor" href="<?=site_url() ?>billing">Planes y facturación</a>
                <span style="margin:0 12px; color:#cdd6ea">•</span>
                <a class="minor" href="<?=site_url() ?>usage">Consumo</a>
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


    <!-- MAIN -->
    <main class="dash-main">
        <div class="container">
            <div class="dash-header">
                <h1>Hola, <?=htmlspecialchars($user->name ?? 'Cliente') ?> </h1>
                <p>Este es tu panel. Desde aquí puedes ver tu API key, consumo y acceder rápido a la documentación.</p>
            </div>

            <div class="dash-grid">
                <!-- COLUMNA IZQUIERDA -->
                <div>
                    <!-- API KEY -->
                    <section class="dash-card">
                        <h2>Tu API Key principal</h2>
                        <p>Usa esta clave en tus servidores backend para autenticar las peticiones.</p>

                        <div class="apikey-row">
                            <div class="apikey-box" id="apiKeyBox" data-api-key="<?=htmlspecialchars($user->api_key ?? '') ?>">
                                <div>
                                    <div class="apikey-label">API KEY</div>
                                    <div class="apikey-value" id="apiKeyMasked"><?=htmlspecialchars($user->api_key ?? '') ?></div>
                                </div>
                            </div>
                            <div class="apikey-actions">
                                <button type="button" class="btn-small" id="btnToggleKey">Mostrar</button>
                                <button type="button" class="btn-small primary" id="btnCopyKey">Copiar</button>
                            </div>
                        </div>

                        <p class="usage-footnote" style="margin-top:12px;">
                            ¿Necesitas rotar la clave? Podrás generar una nueva key y revocar la anterior desde aquí.
                        </p>
                    </section>

                    <!-- CONSUMO -->
                    <section class="dash-card">
                        <h2>Consumo del mes</h2>
                        <p>Monitoriza tus consultas para evitar sorpresas a final de mes.</p>

                        <div class="usage-wrap">
                            <div class="usage-top">
                                <span><span class="usage-strong">1.750</span> de 5.000 consultas usadas</span>
                                <span>Renueva el <span class="usage-strong">01/12</span></span>
                            </div>
                            <div class="usage-bar">
                                <!-- Ajusta el width del fill desde backend según porcentaje real -->
                                <div class="usage-fill" style="width:35%;"></div>
                            </div>
                            <div class="usage-footnote">
                                Te avisaremos por email al 80 % y 100 % del consumo de tu plan.
                            </div>
                        </div>
                    </section>

                    <!-- QUICKSTART / DOCS -->
                    <section class="dash-card">
                        <h2>Empieza en 5 minutos</h2>
                        <p>Endpoints principales y recursos para integrar APIEmpresas en tus flujos.</p>

                        <div class="quick-grid">
                            <div class="quick-item">
                                <strong>Endpoint básico</strong>
                                <code style="font-size:12px;">GET /company?cif=B12345678</code><br />
                                <a href="<?=site_url() ?>documentation#company">Ver ejemplo completo →</a>
                            </div>
                            <div class="quick-item">
                                <strong>SDK PHP / Laravel</strong>
                                <p style="margin:4px 0 0;">Instala el cliente oficial y valida CIF en tus middlewares.</p>
                                <a href="<?=site_url() ?>documentation#sdk-php">Guía rápida PHP →</a>
                            </div>
                            <div class="quick-item">
                                <strong>SDK Node / JS</strong>
                                <p style="margin:4px 0 0;">Ideal para backend en Node o funciones serverless.</p>
                                <a href="<?=site_url() ?>documentation#sdk-node">Guía rápida Node →</a>
                            </div>
                            <div class="quick-item">
                                <strong>Buscador web</strong>
                                <p style="margin:4px 0 0;">Consulta manual de empresas desde el navegador.</p>
                                <a href="<?=site_url() ?>search_company">Abrir buscador →</a>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- COLUMNA DERECHA (SIDEBAR) -->
                <aside>
                    <!-- PLAN -->
                    <section class="plan-card">
                        <div class="plan-pill">
                            <span>PLAN ACTUAL</span>
                            <span>Free</span>
                        </div>

                        <h2><!-- <?= $planName ?? 'Free' ?> -->Free</h2>
                        <div class="plan-price">0 €/mes</div>
                        <p>100 consultas al mes para pruebas, desarrollo y entornos de staging.</p>

                        <div class="plan-meta">
                            <div>• Sin compromiso, puedes cancelar cuando quieras.</div>
                            <div>• Ideal para validar integraciones y PoCs.</div>
                        </div>

                        <button class="btn" type="button" onclick="window.location.href='<?=site_url() ?>prices'">
                            Actualizar a Pro o Business
                        </button>
                    </section>

                    <!-- SOPORTE -->
                    <section class="support-card">
                        <h3>¿Necesitas ayuda?</h3>
                        <p>Si tienes una duda sobre la integración o uso del API, escríbenos.</p>
                        <p>
                            <a href="mailto:soporte@apiempresas.es">soporte@apiempresas.es</a><br />
                            <span style="color:#6b7280;">Tiempo medio de respuesta &lt; 24h laborables.</span>
                        </p>
                    </section>
                </aside>
            </div>
        </div>
    </main>

    <?=view('partials/footer') ?>

</div>

<script>
    // === Lógica sencilla para mostrar/ocultar y copiar API key ===
    (function(){
        const box = document.getElementById('apiKeyBox');
        if(!box) return;

        const realKey = box.getAttribute('data-api-key') || '';
        const masked = '•'.repeat(Math.max(realKey.length - 8, 12));
        const valueEl = document.getElementById('apiKeyMasked');
        const btnToggle = document.getElementById('btnToggleKey');
        const btnCopy = document.getElementById('btnCopyKey');

        let visible = false;
        valueEl.textContent = masked;

        btnToggle.addEventListener('click', () => {
            visible = !visible;
            valueEl.textContent = visible ? realKey : masked;
            btnToggle.textContent = visible ? 'Ocultar' : 'Mostrar';
        });

        btnCopy.addEventListener('click', async () => {
            try{
                await navigator.clipboard.writeText(realKey);
                btnCopy.textContent = 'Copiado ✓';
                setTimeout(() => btnCopy.textContent = 'Copiar', 1800);
            }catch(e){
                alert('No se pudo copiar la clave. Copia el texto manualmente.');
            }
        });
    })();
</script>

</body>
</html>

