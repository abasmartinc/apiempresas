<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <!-- Reutiliza tus estilos base (home) + dashboard si aplica -->
    <link rel="stylesheet" href="<?= base_url('public/css/dashboard.css') ?>" />
    <!-- Estilos específicos de la pantalla "construction" -->
    <link rel="stylesheet" href="<?= base_url('public/css/dashboard_construction.css') ?>" />
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
                <!--<a class="minor" href="<?php /*=site_url() */?>billing">Planes y facturación</a>
                <span style="margin:0 12px; color:#cdd6ea">•</span>-->
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
                <p style="margin: 0px">
                    Gracias por registrarte. Estamos afinando los últimos detalles para publicar la API en producción con garantías.
                </p>
            </div>

            <div class="dash-grid construction-grid">
                <!-- COLUMNA IZQUIERDA -->
                <div>
                    <!-- HERO / STATUS -->
                    <section class="dash-card construction-hero">
                        <div class="ch-top">
                            <div class="status-pill">
                                <span class="dot" aria-hidden="true"></span>
                                <span>Acceso anticipado</span>
                                <span class="sep">•</span>
                                <span>API en preparación</span>
                            </div>

                            <h2 class="ch-title">
                                Estamos terminando la API para que entre en tu stack sin fricción.
                            </h2>

                            <p class="ch-subtitle">
                                Nuestro objetivo es entregarte un servicio estable, rápido y consistente con datos oficiales
                                (BORME/BOE, AEAT, INE y VIES). En cuanto activemos producción, te avisaremos automáticamente.
                            </p>

                            <div class="ch-cta">
                                <a class="btn btn-ghost" href="<?=site_url() ?>documentation">
                                    Ver documentación (preview)
                                </a>
                            </div>

                        </div>

                        <div class="ch-side">
                            <div class="timeline">
                                <div class="tl-item done">
                                    <div class="tl-dot"></div>
                                    <div class="tl-content">
                                        <strong>Registro y panel</strong>
                                        <span>Cuenta creada y acceso al área cliente.</span>
                                    </div>
                                </div>

                                <div class="tl-item active">
                                    <div class="tl-dot"></div>
                                    <div class="tl-content">
                                        <strong>Hardening de producción</strong>
                                        <span>Rate limiting, logging, reintentos y monitorización.</span>
                                    </div>
                                </div>

                                <div class="tl-item">
                                    <div class="tl-dot"></div>
                                    <div class="tl-content">
                                        <strong>Activación de endpoints</strong>
                                        <span>Publicación por fases y estabilidad garantizada.</span>
                                    </div>
                                </div>

                                <div class="tl-item">
                                    <div class="tl-dot"></div>
                                    <div class="tl-content">
                                        <strong>Go-live</strong>
                                        <span>Acceso completo y planes de pago disponibles.</span>
                                    </div>
                                </div>
                            </div>

                            <div class="ch-card">
                                <div class="ch-card-title">Mientras tanto, puedes:</div>
                                <ul class="ch-list">
                                    <li>Explorar la documentación y ejemplos de integración.</li>
                                    <li>Probar el buscador web (mismo motor que la API).</li>
                                    <li>Dejarnos tu caso de uso para priorizar endpoints.</li>
                                </ul>

                                <div class="ch-links">
                                    <a href="<?=site_url() ?>search_company">Abrir buscador →</a>
                                    <a href="mailto:soporte@apiempresas.es">Contactar soporte →</a>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- FAQ / INFO -->
                    <section class="dash-card">
                        <h2>¿Qué significa “en preparación”?</h2>
                        <p style="margin-top:6px;">
                            Ya puedes usar el panel y la documentación de preview. Estamos cerrando el ciclo de calidad para producción:
                            estabilidad, trazabilidad de fuentes, control de consumo y acuerdos de servicio (SLA).
                        </p>

                        <div class="construction-faq">
                            <div class="faq-item">
                                <strong>¿Cuándo estará lista?</strong>
                                <span>Te avisaremos por email en cuanto abramos producción para tu cuenta.</span>
                            </div>
                            <div class="faq-item">
                                <strong>¿Se mantiene mi cuenta?</strong>
                                <span>Sí. Tu cuenta y preferencias quedan guardadas para el lanzamiento.</span>
                            </div>
                            <div class="faq-item">
                                <strong>¿Puedo pedir un endpoint prioritario?</strong>
                                <span>Sí. Escríbenos y lo añadimos a la hoja de ruta.</span>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- COLUMNA DERECHA -->
                <aside>
                    <section class="plan-card plan-card--construction">
                        <div class="plan-pill">
                            <span>ESTADO</span>
                            <span>Preview</span>
                        </div>

                        <h2>Tu acceso está activo</h2>
                        <div class="plan-price">Producción: Próximamente</div>
                        <p>
                            Abriremos el API por oleadas para asegurar rendimiento y disponibilidad desde el primer día.
                        </p>

                        <div class="plan-meta">
                            <div>• Notificación automática al activar producción.</div>
                            <div>• Documentación disponible en preview.</div>
                            <div>• Soporte para integración &lt; 24h laborables.</div>
                        </div>

                        <button class="btn" type="button" onclick="window.location.href='<?=site_url() ?>documentation'">
                            Ver documentación
                        </button>
                    </section>


                </aside>
            </div>
        </div>
    </main>

    <?=view('partials/footer') ?>
</div>


</body>
</html>
