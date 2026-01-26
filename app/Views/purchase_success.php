<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/billing-success.css') ?>" />
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <header>
        <div class="container nav">
            <div class="brand">
                <a href="<?=site_url() ?>">
                    <!-- tu SVG de logo tal cual -->
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
                <span class="nav-dot">•</span>
                <a class="minor" href="<?=site_url() ?>usage">Consumo</a>
                <span class="nav-dot">•</span>
                <a class="minor" href="<?=site_url() ?>documentation">Documentación</a>
                <span class="nav-dot">•</span>
                <a class="minor" href="<?=site_url() ?>search_company">Buscador</a>
            </nav>

            <div class="desktop-only">
                <?php if(!session('logged_in')){ ?>
                    <a class="btn btn_header btn_header--ghost" href="<?=site_url() ?>enter"><span>Iniciar sesión</span></a>
                <?php } else { ?>
                    <a class="btn btn_header btn_header--ghost logout" href="<?=site_url() ?>logout"><span>Salir</span></a>
                <?php } ?>
            </div>
        </div>
    </header>

    <main class="success-main">
        <div class="container">

            <!-- HERO -->
            <div class="success-hero">
                <div class="success-hero__left">
                    <div class="kicker">Compra completada</div>

                    <div class="title-row">
                        <h1>Plan activado. Ya puedes usar APIEmpresas en producción.</h1>

                        <!-- Badge “Confirmado” llamativo -->
                        <span class="status-badge status-badge--ok">
              <span class="status-ic" aria-hidden="true"></span>
              Confirmado
            </span>
                    </div>

                    <p class="sub">
                        Hemos confirmado tu pago y tu plan ya está activo. En 2 minutos puedes dejar tu integración lista.
                        <span class="nowrap">El IVA se calcula según país y aparece en el comprobante.</span>
                    </p>

                    <div class="hero-actions">
                        <a class="btn btn_primary" href="<?=site_url()?>dashboard">Ir al dashboard</a>
                        <a class="btn btn_light" href="<?=site_url()?>usage">Ver consumo</a>
                    </div>

                    <div class="hero-note">
                        <span class="note-ic" aria-hidden="true"></span>
                        Te enviaremos la factura y el recibo al email de facturación. Puedes cambiar o cancelar el plan cuando quieras.
                    </div>
                </div>

                <!-- Resumen compra -->
                <aside class="purchase-card" aria-label="Resumen de la compra">
                    <div class="purchase-head">
                        <div>
                            <div class="purchase-title">Resumen de la compra</div>
                            <div class="purchase-sub">Referencia: <strong>#<?= htmlspecialchars($order_ref) ?></strong></div>
                        </div>

                        <!-- Badge compacto pero potente -->
                        <span class="pill-badge pill-badge--ok">
              <span class="pill-dot" aria-hidden="true"></span>
              Pago OK
            </span>
                    </div>

                    <div class="purchase-lines">
                        <div class="line"><span>Plan</span><strong><?= htmlspecialchars($plan_name) ?></strong></div>
                        <div class="line"><span>Periodicidad</span><strong><?= htmlspecialchars($period_name) ?></strong></div>
                        <div class="line"><span>Precio base</span><strong><?= htmlspecialchars($base_price) ?> €</strong></div>
                        <div class="line"><span>IVA</span><strong>Se calcula según país</strong></div>
                        <div class="line total"><span>Total</span><strong><?= htmlspecialchars($base_price) ?> € <span class="muted">+ IVA</span></strong></div>
                    </div>

                    <div class="purchase-actions">
                        <a class="btn btn_light btn_full" href="<?=site_url()?>billing">Ver planes y facturas</a>
                    </div>

                    <div class="purchase-foot">
                        Método de pago: <strong><?= htmlspecialchars($payment_method) ?></strong>
                    </div>
                </aside>
            </div>

            <!-- NEXT STEPS -->
            <section class="next-steps">
                <div class="section-head">
                    <h2>Siguientes pasos (2 minutos)</h2>
                    <p class="muted">
                        Si quieres resultados rápidos: copia la API key, prueba un endpoint y activa métricas desde el primer día.
                    </p>
                </div>

                <div class="step-list">

                    <article class="step-card">
                        <div class="step-rail" aria-hidden="true">
                            <div class="step-orb step-orb--blue">
                                <span class="orb-ic">1</span>
                            </div>
                            <div class="step-line"></div>
                        </div>

                        <div class="step-body">
                            <h3>Copia tu API key</h3>
                            <p class="muted">
                                Ve a tu dashboard y copia la clave para hacer llamadas desde tu backend.
                                Recomendación: guárdala en variables de entorno, no en frontend.
                            </p>
                            <div class="step-actions">
                                <a class="btn btn_light" href="<?=site_url()?>dashboard">Abrir dashboard</a>
                                <span class="mini-hint">Tarda 10 segundos</span>
                            </div>
                        </div>
                    </article>

                    <article class="step-card">
                        <div class="step-rail" aria-hidden="true">
                            <div class="step-orb step-orb--green">
                                <span class="orb-ic">2</span>
                            </div>
                            <div class="step-line"></div>
                        </div>

                        <div class="step-body">
                            <h3>Prueba un endpoint en 30 segundos</h3>
                            <p class="muted">
                                Valida el flujo con un CIF real y revisa el JSON de respuesta. Si estás en staging,
                                activa logs para ver latencia y ratio de errores desde el primer día.
                            </p>
                            <div class="step-actions">
                                <a class="btn btn_light" href="<?=site_url()?>documentation">Ver documentación</a>
                                <span class="mini-hint">Incluye ejemplos y códigos</span>
                            </div>
                        </div>
                    </article>

                    <article class="step-card">
                        <div class="step-rail" aria-hidden="true">
                            <div class="step-orb step-orb--violet">
                                <span class="orb-ic">3</span>
                            </div>
                            <div class="step-line step-line--fade"></div>
                        </div>

                        <div class="step-body">
                            <h3>Monitoriza consumo y calidad</h3>
                            <p class="muted">
                                En Pro tienes visibilidad de consumo, latencia y errores para evitar sorpresas y detectar incidencias
                                antes de que afecten a producción.
                            </p>
                            <div class="step-actions">
                                <a class="btn btn_light" href="<?=site_url()?>usage">Ir a consumo</a>
                                <span class="mini-hint">Alertas y métricas</span>
                            </div>
                        </div>
                    </article>

                </div>
            </section>

         
        </div>
    </main>

    <?=view('partials/footer') ?>
</div>
</body>
</html>
