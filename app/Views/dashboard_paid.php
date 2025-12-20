<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/dashboard_paid.css') ?>" />
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
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

    <main class="dash-main">
        <div class="container">
            <div class="dash-header">
                <h1>Hola, <?=htmlspecialchars($user->name ?? 'Cliente') ?></h1>
                <p class="dash-sub">
                    Panel de producción: consumo, calidad del servicio y acciones rápidas de operación (rotación de clave, logs y facturación).
                </p>
            </div>

            <!-- KPI STRIP -->
            <section class="kpi-strip kpi-theme-a">
                <div class="kpi-strip__header">
                    <div class="kpi-strip__title">
                        <p class="kicker">VISIÓN GENERAL</p>
                        <div class="kpi-strip__subtitle">Métricas clave de uso y calidad del servicio</div>
                    </div>
                    <div class="kpi-strip__range">Últimas 24h / Mes actual</div>
                </div>

                <div class="kpi-grid">

                    <!-- Consultas -->
                    <article class="kpi">
                        <div class="kpi__left">
                            <p class="kpi__label">Consultas mes</p>
                            <div class="kpi__meta">de 20.000 incluidas</div>
                        </div>
                        <div class="kpi__right">
                            <p class="kpi__number">8.420</p>
                        </div>
                        <p class="kpi__sub">
                            <span>Consumo del plan</span>
                            <span>—</span>
                        </p>
                    </article>

                    <!-- Latencia -->
                    <article class="kpi">
                        <div class="kpi__left">
                            <p class="kpi__label">Latencia P95</p>
                            <div class="kpi__meta">últimas 24h</div>
                        </div>
                        <div class="kpi__right">
                            <p class="kpi__number">240<span class="kpi__unit">ms</span></p>
                        </div>
                        <p class="kpi__sub">
                            <span>Tiempo de respuesta</span>
                            <span>—</span>
                        </p>
                    </article>

                    <!-- Errores -->
                    <article class="kpi">
                        <div class="kpi__left">
                            <p class="kpi__label">Errores</p>
                            <div class="kpi__meta">últimos 7 días</div>
                        </div>
                        <div class="kpi__right">
                            <p class="kpi__number">0,18<span class="kpi__unit">%</span></p>
                        </div>
                        <p class="kpi__sub">
                            <span>Ratio de error</span>
                            <span>—</span>
                        </p>
                    </article>

                    <!-- Estado -->
                    <article class="kpi">
                        <div class="kpi__left">
                            <p class="kpi__label">Estado</p>
                            <div class="kpi__meta">SLA activo</div>
                        </div>
                        <div class="kpi__right">
          <span class="status-chip">
            <span class="status-dot" aria-hidden="true"></span>
            Operativo
            <span class="sla-mini">SLA</span>
          </span>
                        </div>
                        <p class="kpi__sub">
                            <span>Disponibilidad garantizada</span>
                            <span>—</span>
                        </p>
                    </article>

                </div>
            </section>



            <div class="dash-grid">
                <!-- LEFT -->
                <div>
                    <!-- API KEY -->
                    <section class="dash-card">
                        <div class="kicker">Credenciales</div>
                        <h2>Tu API Key principal</h2>
                        <p>Uso recomendado: backend + variables de entorno. Si sospechas exposición, rota la clave.</p>

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
                                <!-- CTA de rotación (enlaza a tu ruta real si la tienes) -->
                                <button type="button" class="btn-small danger" onclick="window.location.href='<?=site_url() ?>billing#api-keys'">
                                    Rotar clave
                                </button>
                            </div>
                        </div>

                        <p class="usage-footnote" style="margin-top:12px;">
                            Sugerencia: rota la key cada X meses (o tras cambios de equipo/repositorio) y revoca la anterior.
                        </p>
                    </section>

                    <!-- USAGE -->
                    <section class="dash-card">
                        <div class="kicker">Consumo</div>
                        <h2>Consumo del mes</h2>
                        <p>Monitoriza consultas para mantenerte dentro de plan y detectar picos anómalos.</p>

                        <div class="usage-wrap">
                            <div class="usage-top">
                                <span><span class="usage-strong">8.420</span> de 20.000 consultas usadas</span>
                                <span>Renueva el <span class="usage-strong">01/<?=date('m')?></span></span>
                            </div>
                            <div class="usage-bar">
                                <div class="usage-fill" style="width:58%;"></div>
                            </div>
                            <div class="usage-footnote">
                                Alertas activas: email al 80% y 100%. Recomendado: caché por CIF y retries con backoff.
                            </div>
                        </div>
                    </section>

                    <!-- OPERATIONS / QUICK ACTIONS -->
                    <section class="dash-card">
                        <div class="kicker">Acciones rápidas</div>
                        <h2>Operación y diagnóstico</h2>
                        <p>Accede rápido a lo que importa cuando estás en producción.</p>

                        <div class="quick-grid">
                            <div class="quick-item">
                                <strong>Logs y métricas</strong>
                                <p style="margin:0; color:#64748b;">Revisa requests, rate limits, errores por endpoint y latencias.</p>
                                <a href="<?=site_url() ?>usage">Abrir panel de consumo →</a>
                            </div>
                            <div class="quick-item">
                                <strong>Documentación (prod)</strong>
                                <p style="margin:0; color:#64748b;">Ejemplos, códigos de error, buenas prácticas y paginación.</p>
                                <a href="<?=site_url() ?>documentation">Ir a documentación →</a>
                            </div>
                            <div class="quick-item">
                                <strong>Buscador</strong>
                                <p style="margin:0; color:#64748b;">Validación manual para soporte o casos puntuales.</p>
                                <a href="<?=site_url() ?>search_company">Abrir buscador →</a>
                            </div>
                            <div class="quick-item">
                                <strong>Webhooks (opcional)</strong>
                                <p style="margin:0; color:#64748b;">Automatiza flujos cuando cambie el estado o se refresquen datos.</p>
                                <a href="<?=site_url() ?>documentation#webhooks">Ver webhooks →</a>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- RIGHT -->
                <aside>
                    <!-- PLAN -->
                    <section class="plan-card">
                        <div class="plan-pill">
                            <span>PLAN ACTUAL</span>
                            <span>Pro</span>
                        </div>

                        <h2>Pro</h2>
                        <div class="plan-price">49 €/mes</div>
                        <p style="margin:0 0 12px; color:rgba(239,246,255,.92); font-size:13px;">
                            Plan de producción para integraciones activas con visibilidad y estabilidad.
                        </p>

                        <div class="plan-meta">
                            <div>• Límite ampliado + métricas avanzadas.</div>
                            <div>• Alertas de consumo y soporte priorizado.</div>
                            <div>• Recomendado para KYC/validación en alta.</div>
                        </div>

                        <button class="btn" type="button" onclick="window.location.href='<?=site_url() ?>billing'">
                            Gestionar plan y facturación
                        </button>
                    </section>

                    <!-- BILLING / INVOICES -->
                    <section class="mini-card">
                        <h3>Facturación</h3>
                        <p>
                            Próxima renovación: <strong>01/<?=date('m')?></strong><br>
                            Método de pago: <strong>Tarjeta</strong><br>
                            Estado: <strong>Activo</strong>
                        </p>
                        <a href="<?=site_url() ?>billing">Ver facturas y datos fiscales →</a>
                        <div class="mini-note">
                            Si necesitas factura con datos fiscales específicos, actualízalo en “Facturación”.
                        </div>
                    </section>

                    <!-- SUPPORT -->
                    <section class="mini-card">
                        <h3>Soporte Pro</h3>
                        <p>
                            Canal prioritario para incidencias de producción y dudas de integración.
                        </p>
                        <p style="margin:0;">
                            <a href="mailto:soporte@apiempresas.es">soporte@apiempresas.es</a><br />
                            <span class="mini-note">SLA soporte: respuesta prioritaria en horario laboral.</span>
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

