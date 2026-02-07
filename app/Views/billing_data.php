<!doctype html>
<html lang="es">
<head>
    <head>
        <?=view('partials/head') ?>
        <link rel="stylesheet" href="<?= base_url('public/css/billing_data.css') ?>" />
    </head>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<?=view('partials/header_inner') ?>

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

