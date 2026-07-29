<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app' ) ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/css/billing-success.css?v=' . time()) ?>" />
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container" style="padding-top: 80px;">

            <!-- HERO -->
            <div class="success-hero">
                <div class="success-hero__left">
                    <div class="kicker"><?= lang('Billing.success_sub_kicker') ?></div>

                    <div class="title-row">
                        <h1><?= lang('Billing.success_sub_title') ?></h1>
                        <span class="status-badge">
                            <span class="status-ic" aria-hidden="true"></span>
                            <?= lang('Billing.success_sub_confirmed') ?>
                        </span>
                    </div>

                    <p class="sub">
                        Hemos confirmado tu pago y tu plan <strong><?= htmlspecialchars($plan_name ?? 'Pro') ?></strong> ya está activo. En 2 minutos puedes dejar tu integración lista.
                        El IVA se calcula según tu país y aparece desglosado en el comprobante.
                    </p>

                    <div class="hero-actions">
                        <a class="btn btn_primary" href="<?=site_url()?>dashboard"><?= lang('Billing.success_sub_btn_dash') ?></a>
                        <a class="btn btn_light" href="<?=site_url()?>consumption"><?= lang('Billing.success_sub_btn_metrics') ?></a>
                    </div>

                    <div class="hero-note">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        <?= lang('Billing.success_sub_note') ?>
                    </div>
                </div>

                <!-- TICKET DE COMPRA -->
                <aside class="purchase-card" aria-label="Resumen de la compra">
                    <div class="purchase-head">
                        <div class="purchase-title"><?= lang('Billing.success_sub_summary_title') ?></div>
                        <div class="purchase-sub"><?= lang('Billing.success_sub_ref') ?><strong>#<?= htmlspecialchars($order_ref ?? 'SUB-0001') ?></strong></div>
                    </div>

                    <div class="purchase-lines">
                        <div class="line"><span><?= lang('Billing.success_sub_plan') ?></span><strong><?= htmlspecialchars($plan_name ?? 'Pro') ?></strong></div>
                        <div class="line"><span><?= lang('Billing.success_sub_period') ?></span><strong><?= htmlspecialchars($period_name ?? 'Mensual') ?></strong></div>
                        <div class="line"><span><?= lang('Billing.success_sub_method') ?></span><strong><?= htmlspecialchars($payment_method ?? 'Tarjeta') ?></strong></div>
                        
                        <div class="ticket-divider"></div>

                        <?php 
                            $base = (float)($base_price ?? 0);
                            $iva = $base * 0.21;
                            $total = $base + $iva;
                        ?>
                        <div class="line"><span><?= lang('Billing.success_sub_base') ?></span><strong><?= number_format($base, 2, ',', '.') ?> €</strong></div>
                        <div class="line"><span><?= lang('Billing.success_sub_vat') ?></span><strong><?= number_format($iva, 2, ',', '.') ?> €</strong></div>
                        <div class="line total"><span><?= lang('Billing.success_sub_total') ?></span><strong><?= number_format($total, 2, ',', '.') ?> €</strong></div>
                    </div>
                </aside>
            </div>

            <!-- Siguientes pasos (Premium Grid) -->
            <section class="next-steps">
                <div class="section-head">
                    <h2><?= lang('Billing.success_sub_next_title') ?></h2>
                    <p><?= lang('Billing.success_sub_next_desc') ?></p>
                </div>

                <div class="step-list">
                    <!-- Tarjeta 1 -->
                    <article class="step-card">
                        <div class="step-icon-box blue">
                            1
                        </div>
                        <div class="step-body">
                            <h3><?= lang('Billing.success_sub_step1_title') ?></h3>
                            <p>
                                <?= lang('Billing.success_sub_step1_desc') ?>
                            </p>
                        </div>
                        <div class="step-actions">
                            <a class="btn btn_light btn_full" href="<?=site_url()?>dashboard"><?= lang('Billing.success_sub_step1_btn') ?></a>
                        </div>
                    </article>

                    <!-- Tarjeta 2 -->
                    <article class="step-card">
                        <div class="step-icon-box green">
                            2
                        </div>
                        <div class="step-body">
                            <h3><?= lang('Billing.success_sub_step2_title') ?></h3>
                            <p>
                                <?= lang('Billing.success_sub_step2_desc') ?>
                            </p>
                        </div>
                        <div class="step-actions">
                            <a class="btn btn_light btn_full" href="<?=site_url()?>documentation"><?= lang('Billing.success_sub_step2_btn') ?></a>
                        </div>
                    </article>

                    <!-- Tarjeta 3 -->
                    <article class="step-card">
                        <div class="step-icon-box violet">
                            3
                        </div>
                        <div class="step-body">
                            <h3><?= lang('Billing.success_sub_step3_title') ?></h3>
                            <p>
                                <?= lang('Billing.success_sub_step3_desc') ?>
                            </p>
                        </div>
                        <div class="step-actions">
                            <a class="btn btn_light btn_full" href="<?=site_url()?>consumption"><?= lang('Billing.success_sub_step3_btn') ?></a>
                        </div>
                    </article>
                </div>
            </section>
         
        </div>
<?= $this->endSection() ?>
