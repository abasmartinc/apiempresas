<!doctype html>
<html lang="es">
<head>
    <head>
        <?=view('partials/head') ?>
        <link rel="stylesheet" href="<?= base_url('public/css/billing.css') ?>" />
    </head>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

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
            <a class="minor" href="<?=site_url() ?>dashboard">Dashboard</a>
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

<main class="billing-main">
    <div class="container">
        <!-- CABECERA -->
        <div class="billing-header">
            <div>
                <h1>Plan y facturación</h1>
                <p>Revisa tu plan actual, consumo de API y facturas emitidas.</p>
            </div>
            <div>
                <small style="display:block;font-size:12px;color:#6b7280;margin-bottom:4px;">
                    ID de cuenta
                </small>
                <strong style="font-size:13px;letter-spacing:.02em;color:#0f172a;">
                    ACC-001234
                </strong>
            </div>
        </div>

        <!-- GRID PRINCIPAL -->
        <div class="billing-layout">
            <!-- RESUMEN DE PLAN -->
            <section class="billing-card">
                <h2>Resumen de plan</h2>
                <p>Gestiona tu suscripción y controla el uso mensual de consultas.</p>

                <div class="plan-row">
                    <div class="plan-main">
                        <span class="plan-name">Plan Pro</span>
                        <span class="plan-meta">
                            5.000 consultas / mes incluidas · 39 € / mes
                        </span>
                    </div>
                    <span class="badge-plan">En curso</span>
                </div>

                <div>
                    <div class="usage-label">
                        <span>Consumo este mes</span>
                        <span>2.320 / 5.000</span>
                    </div>
                    <div class="usage-bar">
                        <!-- cambia el width con tu % real -->
                        <div class="usage-bar-fill" style="width:46%;"></div>
                    </div>
                    <p style="font-size:12px;color:#6b7280;margin-top:6px;">
                        Se reseteará el <strong>01/12/2025</strong>. Te avisaremos si llegas al 80% y al 100% del límite.
                    </p>
                </div>

                <div class="plan-actions">
                    <button class="btn secondary" type="button" onclick="window.location.href='/precios'">
                        Cambiar de plan
                    </button>
                    <button class="btn ghost" type="button">
                        Ver detalle de consumo
                    </button>
                </div>
            </section>

            <!-- MÉTODO DE PAGO -->
            <section class="billing-card">
                <h2>Método de pago</h2>
                <p>Gestiona la tarjeta usada para el cobro mensual de tu suscripción.</p>

                <!-- Si no hay tarjeta, cambia este bloque por un CTA de añadir tarjeta -->
                <div class="payment-method">
                    <div class="card-icon">VISA</div>
                    <div class="payment-text">
                        <span>•••• •••• •••• 4242</span>
                        <span>Caduca 08/28 · Titular: APIEMPRESAS DEMO</span>
                    </div>
                </div>

                <p class="payment-footer">
                    Cobro recurrente el día <strong>1 de cada mes</strong>. Puedes actualizar el método de pago en cualquier momento.
                </p>

                <div class="plan-actions">
                    <button class="btn" type="button">
                        Actualizar tarjeta
                    </button>
                    <button class="btn ghost" type="button" style="margin-left:auto;">
                        Descargar datos de facturación
                    </button>
                </div>
            </section>
        </div>

        <!-- HISTORIAL DE FACTURAS -->
        <section class="billing-card billing-table-card">
            <h2>Historial de facturas</h2>
            <p>Descarga las facturas emitidas o revisa su estado de pago.</p>

            <div class="billing-table-wrapper">
                <table class="billing-table">
                    <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Periodo</th>
                        <th>Plan</th>
                        <th>Importe</th>
                        <th>Estado</th>
                        <th>Factura</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- Ejemplos de filas; rellena con tus datos -->
                    <tr>
                        <td>01/11/2025</td>
                        <td>Nov 2025</td>
                        <td>Pro</td>
                        <td>39,00 €</td>
                        <td><span class="pill-invoice">Pagada</span></td>
                        <td><a href="#" class="link-inline">Descargar PDF</a></td>
                    </tr>
                    <tr>
                        <td>01/10/2025</td>
                        <td>Oct 2025</td>
                        <td>Pro</td>
                        <td>39,00 €</td>
                        <td><span class="pill-invoice">Pagada</span></td>
                        <td><a href="#" class="link-inline">Descargar PDF</a></td>
                    </tr>
                    <tr>
                        <td>01/09/2025</td>
                        <td>Sep 2025</td>
                        <td>Pro</td>
                        <td>39,00 €</td>
                        <td><span class="pill-invoice pending">Pendiente</span></td>
                        <td><a href="#" class="link-inline">Reintentar pago</a></td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </section>

    </div>
</main>

<?=view('partials/footer') ?>

</body>
</html>

