<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/billing-success.css') ?>" />
    <style>
        .radar-accent { color: #f59e0b !important; }
        .step-orb--radar { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; }
    </style>
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <header>
        <div class="container nav">
            <div class="brand">
                <a href="<?=site_url('radar') ?>">
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
                        </defs>
                        <g filter="url(#ve-cardShadow)">
                            <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)"/>
                        </g>
                        <path d="M18 33 L28 43 L46 22" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none" filter="url(#ve-checkShadow)"/>
                    </svg>
                </a>
                <div class="brand-text">
                    <span class="brand-name">Radar<span class="grad">PRO</span></span>
                    <span class="brand-tag">Captación B2B inteligente</span>
                </div>
            </div>

            <nav class="desktop-only" aria-label="Principal">
                <a class="minor" href="<?=site_url('radar') ?>">Ir al Radar</a>
                <span class="nav-dot">•</span>
                <a class="minor" href="<?=site_url('billing/invoices') ?>">Mis Facturas</a>
            </nav>

            <div class="desktop-only">
                <a class="btn btn_header btn_header--ghost logout" href="<?=site_url() ?>logout"><span>Salir</span></a>
            </div>
        </div>
    </header>

    <main class="success-main">
        <div class="container">

            <!-- HERO -->
            <div class="success-hero">
                <div class="success-hero__left">
                    <div class="kicker">Suscripción Activada</div>

                    <div class="title-row">
                        <h1>¡Bienvenido a Radar PRO! Ya puedes acceder a todas las oportunidades.</h1>
                        <span class="status-badge status-badge--ok">
                            <span class="status-ic" aria-hidden="true"></span>
                            PRO Activo
                        </span>
                    </div>

                    <p class="sub">
                        Has desbloqueado el acceso completo. Ahora puedes ver datos de contacto, administradores y filtrar todas las empresas recién constituidas en España.
                    </p>

                    <div class="hero-actions">
                        <a class="btn btn_primary" href="<?=site_url('radar')?>" style="background: linear-gradient(135deg, #2152ff 0%, #123c85 100%);">Acceder al Radar ahora</a>
                        <a class="btn btn_light" href="<?=site_url('billing/invoices')?>">Descargar factura</a>
                    </div>
                </div>

                <!-- Resumen compra -->
                <aside class="purchase-card" aria-label="Resumen de la compra">
                    <div class="purchase-head">
                        <div>
                            <div class="purchase-title">Resumen de suscripción</div>
                            <div class="purchase-sub">Referencia: <strong>#<?= htmlspecialchars($order_ref) ?></strong></div>
                        </div>
                        <span class="pill-badge pill-badge--ok">
                            <span class="pill-dot" aria-hidden="true"></span>
                            Activa
                        </span>
                    </div>

                    <div class="purchase-lines">
                        <div class="line"><span>Plan</span><strong>Radar PRO (Ilimitado)</strong></div>
                        <div class="line"><span>Periodicidad</span><strong>Mensual</strong></div>
                        <div class="line"><span>Precio</span><strong>79.00 € <span class="muted">+ IVA</span></strong></div>
                    </div>

                    <div class="purchase-foot">
                        Método de pago: <strong>Tarjeta (Stripe Checkout)</strong>
                    </div>
                </aside>
            </div>

            <!-- NEXT STEPS -->
            <section class="next-steps">
                <div class="section-head">
                    <h2>Consejos para tu Prospección (Paso a paso)</h2>
                    <p class="muted">
                        Saca el máximo partido a tu suscripción PRO con estos tres pasos clave.
                    </p>
                </div>

                <div class="step-list">
                    <article class="step-card">
                        <div class="step-rail" aria-hidden="true">
                            <div class="step-orb step-orb--radar"><span class="orb-ic">1</span></div>
                            <div class="step-line"></div>
                        </div>
                        <div class="step-body">
                            <h3>Filtra por tu zona y sector</h3>
                            <p class="muted">
                                Ve al Radar y utiliza los filtros de provincia y CNAE para encontrar empresas que encajen exactamente con tu cliente ideal.
                            </p>
                            <div class="step-actions">
                                <a class="btn btn_light" href="<?=site_url('radar')?>">Ir a los filtros</a>
                            </div>
                        </div>
                    </article>

                    <article class="step-card">
                        <div class="step-rail" aria-hidden="true">
                            <div class="step-orb step-orb--radar"><span class="orb-ic">2</span></div>
                            <div class="step-line"></div>
                        </div>
                        <div class="step-body">
                            <h3>Identifica al Administrador</h3>
                            <p class="muted">
                                Usa la "Vista Rápida" para ver quién figura como administrador y usa el botón de LinkedIn para contactar con ellos directamente.
                            </p>
                            <div class="step-actions">
                                <span class="mini-hint">Disponible visualmente en cada fila</span>
                            </div>
                        </div>
                    </article>

                    <article class="step-card">
                        <div class="step-rail" aria-hidden="true">
                            <div class="step-orb step-orb--radar"><span class="orb-ic">3</span></div>
                            <div class="step-line step-line--fade"></div>
                        </div>
                        <div class="step-body">
                            <h3>Exporta a Excel</h3>
                            <p class="muted">
                                Si tienes un equipo de ventas o un CRM, descarga el listado completo para que tus comerciales empiecen a llamar hoy mismo.
                            </p>
                            <div class="step-actions">
                                <span class="mini-hint">Botones de exportación en la tabla</span>
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
