<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app' ) ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/css/billing-success.css?v=' . time()) ?>" />
<style>
    .risk-success-hero::before {
        background: linear-gradient(90deg, #6366f1, #4f46e5) !important;
    }
    .badge-risk {
        background: #eef2ff;
        border-color: #c7d2fe;
        color: #3730a3;
    }
    .badge-risk .status-ic {
        background: #4f46e5;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container" style="padding-top: 80px; padding-bottom: 60px;">

    <!-- HERO -->
    <div class="success-hero">
        <div class="success-hero__left risk-success-hero" style="border-color: rgba(99, 102, 241, 0.3);">
            <div class="kicker" style="color: #4f46e5;">Pack de Auditorías Activado</div>

            <div class="title-row">
                <h1>¡Pack 5 Auditorías disponible!</h1>
                <span class="status-badge badge-risk">
                    <span class="status-ic" aria-hidden="true"></span>
                    Pago Aprobado
                </span>
            </div>

            <p class="sub">
                Se han añadido <strong>5 auditorías completas de solvencia y riesgo mercantil</strong> a tu cuenta. Puedes utilizarlas en cualquier momento; <strong>no caducan</strong> e incluyen el dictamen de cada empresa en PDF.
            </p>

            <div class="hero-actions">
                <?php if (!empty($target_cif)): ?>
                    <a class="btn btn_primary" href="<?= esc($target_url ?? site_url('dashboard?view=risk&cif=' . rawurlencode($target_cif)), 'attr') ?>" style="background: #4f46e5; box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.4);">
                        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Auditar <?= htmlspecialchars($target_cif) ?>
                    </a>
                <?php endif; ?>
                <a class="btn btn_primary" href="<?= site_url('dashboard?view=risk') ?>" style="background: #0f172a; box-shadow: 0 10px 20px -5px rgba(15, 23, 42, 0.3);">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right: 6px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    Panel de Solvencia
                </a>
                <a class="btn btn_light" href="<?= site_url('buscar') ?>">
                    Buscar empresas
                </a>
            </div>

            <div class="hero-note" style="background: #eef2ff; border-color: #c7d2fe; color: #3730a3;">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                <span>Cada vez que audites una nueva empresa, quedará desbloqueada permanentemente en tu cuenta sin volver a consumir saldo.</span>
            </div>
        </div>

        <!-- TICKET DE COMPRA -->
        <aside class="purchase-card" aria-label="Resumen de la compra">
            <div class="purchase-head">
                <div class="purchase-title">Resumen de Compra</div>
                <div class="purchase-sub">Ref: <strong>#<?= htmlspecialchars($order_ref ?? 'RISK-0001') ?></strong></div>
            </div>

            <div class="purchase-lines">
                <div class="line">
                    <span>Producto</span>
                    <strong>Pack 5 Auditorías</strong>
                </div>
                <div class="line">
                    <span>Créditos añadidos</span>
                    <strong style="color: #4f46e5;">+<?= number_format($credits_bought ?? 5, 0, ',', '.') ?> auditorías</strong>
                </div>
                <div class="line">
                    <span>Saldo total disponible</span>
                    <strong style="color: #059669;"><?= number_format($total_credits ?? 5, 0, ',', '.') ?> auditorías</strong>
                </div>
                <div class="line">
                    <span>Caducidad</span>
                    <strong>Sin caducidad</strong>
                </div>
                <div class="line">
                    <span>Informes PDF</span>
                    <strong>Incluidos</strong>
                </div>
                
                <div class="ticket-divider"></div>

                <?php 
                    $base = (float)($price ?? 9.90);
                    $iva = $base * 0.21;
                    $total = $base + $iva;
                ?>
                <div class="line"><span>Base imponible</span><strong><?= number_format($base, 2, ',', '.') ?> €</strong></div>
                <div class="line"><span>IVA (21%)</span><strong><?= number_format($iva, 2, ',', '.') ?> €</strong></div>
                <div class="line total"><span>Total pagado</span><strong style="color: #4f46e5;"><?= number_format($total, 2, ',', '.') ?> €</strong></div>
            </div>
        </aside>
    </div>

    <!-- Siguientes pasos -->
    <section class="next-steps" style="margin-top: 50px;">
        <div class="section-head">
            <h2>Cómo aprovechar tus auditorías de solvencia</h2>
            <p>Revisa lo que consta en el Registro Mercantil de tus clientes y proveedores antes de venderles a crédito.</p>
        </div>

        <div class="step-list">
            <!-- Tarjeta 1 -->
            <article class="step-card">
                <div class="step-icon-box" style="background: #eef2ff; color: #4f46e5; border-color: #c7d2fe;">
                    1
                </div>
                <div class="step-body">
                    <h3>Busca y audita al instante</h3>
                    <p>
                        Introduce el CIF o nombre comercial desde tu panel de solvencia o desde el buscador general.
                    </p>
                </div>
                <div class="step-actions">
                    <a class="btn btn_light btn_full" href="<?= site_url('dashboard?view=risk') ?>">Ir al Panel</a>
                </div>
            </article>

            <!-- Tarjeta 2 -->
            <article class="step-card">
                <div class="step-icon-box" style="background: #f0fdf4; color: #059669; border-color: #a7f3d0;">
                    2
                </div>
                <div class="step-body">
                    <h3>Descarga el dictamen en PDF</h3>
                    <p>
                        Descarga el dictamen de cada empresa auditada: puntuación, cada acto del BORME con su fecha, contratos públicos y ayudas.
                    </p>
                </div>
                <div class="step-actions">
                    <a class="btn btn_light btn_full" href="<?= site_url('dashboard?view=risk') ?>">Ver Historial</a>
                </div>
            </article>

            <!-- Tarjeta 3 -->
            <article class="step-card">
                <div class="step-icon-box" style="background: #fdf4ff; color: #9333ea; border-color: #f0abfc;">
                    3
                </div>
                <div class="step-body">
                    <h3>Sin cuotas ni ataduras</h3>
                    <p>
                        Tus créditos nunca caducan. Si revisas clientes a menudo, Solvencia Pro te da <?= (int) solvencia('consultasPro', 300) ?> consultas al mes y vigila hasta <?= (int) solvencia('vigilanciasPro', 25) ?> empresas por ti.
                    </p>
                </div>
                <div class="step-actions">
                    <a class="btn btn_light btn_full" href="<?= site_url('billing?view=risk&plan=risk_pro') ?>">Ver Solvencia Pro</a>
                </div>
            </article>
        </div>
    </section>

</div>
<?= $this->endSection() ?>
