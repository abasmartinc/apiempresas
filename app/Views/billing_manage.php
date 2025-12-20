<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/billing.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/css/billing_manage.css') ?>" />
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
                    <a class="btn btn_header btn_header--ghost" href="<?=site_url() ?>enter"><span>Iniciar sesión</span></a>
                <?php } else { ?>
                    <a class="btn btn_header btn_header--ghost logout" href="<?=site_url() ?>logout"><span>Salir</span></a>
                <?php } ?>
            </div>
        </div>
    </header>

    <main class="billing-main">
        <div class="container">

            <!-- HERO -->
            <div class="manage-hero">
                <div class="manage-hero__left">
                    <div class="kicker">Gestión de suscripción</div>
                    <h1>Tu plan y facturación, en un solo sitio</h1>
                    <p class="sub">
                        Consulta tu plan, próxima renovación y facturas. Puedes cambiar de plan o cancelar cuando quieras.
                        <span style="white-space:nowrap;">IVA según país en cada recibo.</span>
                    </p>

                    <div class="hero-meta">
                        <span class="pill">Plan: <strong><?= htmlspecialchars($current_plan ?? 'Pro') ?></strong></span>
                        <span class="dot-sep">•</span>
                        <span class="pill">Estado: <strong><?= htmlspecialchars($subscription_status ?? 'Activa') ?></strong></span>
                        <span class="dot-sep">•</span>
                        <span class="pill">Renovación: <strong><?= htmlspecialchars($renewal_date ?? '—') ?></strong></span>
                    </div>

                    <p class="muted" style="margin:12px 0 0;">
                        Tip: si tienes picos de tráfico, subir a Business evita bloqueos por volumen y mejora SLA.
                    </p>
                </div>

                <div class="manage-hero__right">
                    <a class="btn btn_light" href="<?=site_url()?>dashboard">Volver al dashboard</a>
                </div>
            </div>

            <!-- GRID -->
            <div class="manage-grid">

                <!-- LEFT: Overview + Actions -->
                <section class="panel manage-panel">

                    <div class="panel-head manage-head">
                        <div>
                            <div class="kicker">Resumen</div>
                            <h2>Estado de tu suscripción</h2>
                            <p class="muted">
                                Aquí gestionas cambios de plan, método de pago y facturas. No se guardan datos de tarjeta en nuestros servidores.
                            </p>
                        </div>

                        <div class="status-chip status-chip--ok" aria-label="Estado de la suscripción">
                            <span class="status-dot" aria-hidden="true"></span>
                            <span><?= htmlspecialchars($subscription_status_badge ?? 'Activa') ?></span>
                        </div>
                    </div>

                    <div class="cards-2">
                        <!-- Card: Plan actual -->
                        <div class="mini-card">
                            <div class="mini-card__top">
                                <div>
                                    <div class="mini-kicker">Plan actual</div>
                                    <div class="mini-title"><?= htmlspecialchars($current_plan ?? 'Pro') ?></div>
                                </div>

                                <div class="mini-price">
                                    <div class="mini-amount"><?= htmlspecialchars($price_base ?? '19') ?>€</div>
                                    <div class="mini-unit">/ mes <span class="muted" style="font-size:12px;">+ IVA</span></div>
                                </div>
                            </div>

                            <ul class="mini-list">
                                <li><span class="tick" aria-hidden="true"></span> Más límite para producción</li>
                                <li><span class="tick" aria-hidden="true"></span> Métricas de latencia/errores</li>
                                <li><span class="tick" aria-hidden="true"></span> Soporte prioritario</li>
                            </ul>

                            <div class="mini-actions">
                                <a class="btn btn_primary" href="<?=site_url()?>billing">Cambiar plan</a>
                                <a class="btn btn_light" href="<?=site_url()?>prices">Ver comparativa</a>
                            </div>

                            <p class="muted mini-note">
                                El cambio se aplica al finalizar el periodo o de forma inmediata según el plan y configuración del checkout.
                            </p>
                        </div>

                        <!-- Card: Método de pago -->
                        <div class="mini-card">
                            <div class="mini-card__top">
                                <div>
                                    <div class="mini-kicker">Método de pago</div>
                                    <div class="mini-title"><?= htmlspecialchars($payment_method ?? 'Tarjeta (Stripe) / PayPal') ?></div>
                                </div>

                                <div class="method-badge">
                                    <span class="m-ic" aria-hidden="true"></span>
                                    <span>Seguro</span>
                                </div>
                            </div>

                            <div class="muted" style="margin-top:10px; line-height:1.55;">
                                Gestiona tu método de pago desde la pasarela (Stripe/PayPal). Nosotros solo guardamos el estado de tu suscripción y tus facturas.
                            </div>

                            <div class="mini-actions" style="margin-top:14px;">
                                <!-- Placeholder a futuro: Stripe Customer Portal -->
                                <a class="btn btn_light btn_full" href="#"
                                   aria-disabled="true" title="Placeholder: se conectará a Stripe/PayPal">
                                    Actualizar método de pago (próximamente)
                                </a>
                            </div>

                            <p class="muted mini-note">
                                Si un pago falla, te avisaremos por email y tendrás margen para actualizar el método antes de pausar la suscripción.
                            </p>
                        </div>
                    </div>

                    <div class="divider"></div>

                    <!-- Risk-free / Cancel -->
                    <div class="danger-zone">
                        <div class="danger-zone__left">
                            <div class="kicker">Control</div>
                            <h2>Cancelar o pausar</h2>
                            <p class="muted">
                                Puedes cancelar cuando quieras. Mantendrás el acceso hasta el final de tu periodo facturado.
                                No hay permanencia.
                            </p>
                        </div>

                        <div class="danger-zone__right">
                            <a class="btn btn_light" href="#"
                               aria-disabled="true" title="Placeholder: se implementará cuando conectes pagos">
                                Pausar suscripción (opcional)
                            </a>
                            <a class="btn btn_danger" href="#"
                               aria-disabled="true" title="Placeholder: se implementará cuando conectes pagos">
                                Cancelar suscripción
                            </a>
                        </div>
                    </div>

                    <div class="muted" style="font-size:12px; margin-top:10px;">
                        Consejo: si solo estás en staging, puedes bajar a Free y reactivar Pro cuando pases a producción.
                    </div>

                </section>

                <!-- RIGHT: Invoices / Billing -->
                <aside class="side">
                    <section class="summary manage-summary">
                        <div class="summary-head">
                            <h3>Facturas y recibos</h3>
                            <span class="mini">Historial</span>
                        </div>

                        <div class="invoice-toolbar">
                            <div class="muted" style="font-size:12px;">
                                Referencia: <strong><?= htmlspecialchars($billing_ref ?? '—') ?></strong>
                            </div>
                            <a class="btn btn_light" href="#" aria-disabled="true" title="Placeholder">
                                Descargar último recibo
                            </a>
                        </div>

                        <div class="invoice-table" role="table" aria-label="Historial de facturas">
                            <div class="invoice-row invoice-row--head" role="row">
                                <div role="columnheader">Fecha</div>
                                <div role="columnheader">Importe</div>
                                <div role="columnheader">Estado</div>
                                <div role="columnheader" class="ar">Acción</div>
                            </div>

                            <!-- Placeholders -->
                            <div class="invoice-row" role="row">
                                <div role="cell">—</div>
                                <div role="cell">—</div>
                                <div role="cell"><span class="pill pill--soft">Sin datos</span></div>
                                <div role="cell" class="ar"><a class="link disabled" href="#" aria-disabled="true">Ver</a></div>
                            </div>
                            <div class="invoice-row" role="row">
                                <div role="cell">—</div>
                                <div role="cell">—</div>
                                <div role="cell"><span class="pill pill--soft">Sin datos</span></div>
                                <div role="cell" class="ar"><a class="link disabled" href="#" aria-disabled="true">Ver</a></div>
                            </div>
                        </div>

                        <div class="summary-box" style="margin-top:12px;">
                            <div class="srow">
                                <span class="dot ok" aria-hidden="true"></span>
                                <div>
                                    <strong>IVA por país</strong>
                                    <span>Se calcula automáticamente en checkout y aparece en la factura.</span>
                                </div>
                            </div>
                            <div class="srow">
                                <span class="dot ok" aria-hidden="true"></span>
                                <div>
                                    <strong>Comprobante descargable</strong>
                                    <span>PDF disponible desde esta sección o desde la pasarela.</span>
                                </div>
                            </div>
                        </div>

                        <div class="divider" style="margin:14px 0;"></div>

                        <a class="btn btn_full btn_primary" href="<?=site_url()?>billing">
                            Ver planes y facturación
                        </a>

                        <p class="muted" style="margin:10px 0 0; font-size:12px;">
                            Si necesitas ayuda con la facturación, escríbenos a
                            <a class="link" href="mailto:soporte@apiempresas.es">soporte@apiempresas.es</a>.
                        </p>
                    </section>

                    <section class="help">
                        <h4>¿Necesitas factura a nombre de empresa?</h4>
                        <p class="muted">
                            Añade tu NIF/CIF y razón social en el checkout o desde tu configuración de facturación.
                        </p>
                        <a class="btn btn_light btn_full" href="<?=site_url()?>billing">Ir a facturación</a>
                    </section>
                </aside>

            </div>
        </div>
    </main>

    <?=view('partials/footer') ?>
</div>

</body>
</html>

