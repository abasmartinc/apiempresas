<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app' ) ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/css/billing-success.css?v=' . time()) ?>" />
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container" style="padding-top: 80px;">

    <!-- HERO -->
    <div class="success-hero">
        <div class="success-hero__left">
            <div class="kicker">SUSCRIPCIÓN CONFIRMADA</div>

            <div class="title-row">
                <h1>Plan Solvencia Pro activado.<br>Consultas y dictámenes ilimitados.</h1>
                <span class="status-badge">
                    <span class="status-ic" aria-hidden="true"></span>
                    Confirmado
                </span>
            </div>

            <p class="sub">
                Hemos confirmado tu pago y tu plan <strong>Solvencia Pro</strong> ya está activo. Ya puedes consultar el scoring de estabilidad societaria, semáforo de riesgo y alertas BORME de cualquier empresa en España sin límites ni bloqueos.
            </p>

            <div class="hero-actions">
                <a class="btn btn_primary" href="<?= site_url('/') ?>" style="background: #2563eb; border-color: #2563eb; padding: 14px 28px; font-weight: 800; font-size: 1rem;">Ir a la Home</a>
            </div>

            <div class="hero-note">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                Te enviaremos la factura oficial al email de tu cuenta. Recuerda que puedes cambiar o cancelar tu suscripción en cualquier momento sin permanencia.
            </div>
        </div>

        <!-- TICKET DE COMPRA -->
        <aside class="purchase-card" aria-label="Resumen de la compra">
            <div class="purchase-head">
                <div class="purchase-title">Resumen de tu suscripción</div>
                <div class="purchase-sub">Referencia: <strong>#<?= htmlspecialchars($order_ref ?? 'SUB-0001') ?></strong></div>
            </div>

            <div class="purchase-lines">
                <div class="line"><span>Plan contratado</span><strong>Solvencia Pro</strong></div>
                <div class="line"><span>Periodicidad</span><strong><?= htmlspecialchars($period_name ?? 'Mensual') ?></strong></div>
                <div class="line"><span>Método de pago</span><strong><?= htmlspecialchars($payment_method ?? 'Tarjeta (Stripe)') ?></strong></div>
                
                <div class="ticket-divider"></div>

                <?php 
                    $base = (float)($base_price ?? 29.00);
                    $iva = $base * 0.21;
                    $total = $base + $iva;
                ?>
                <div class="line"><span>Precio base</span><strong><?= number_format($base, 2, ',', '.') ?> €</strong></div>
                <div class="line"><span>IVA (21%)</span><strong><?= number_format($iva, 2, ',', '.') ?> €</strong></div>
                <div class="line total"><span>Total facturado</span><strong><?= number_format($total, 2, ',', '.') ?> €</strong></div>
            </div>
        </aside>
    </div>

</div>
<?= $this->endSection() ?>
