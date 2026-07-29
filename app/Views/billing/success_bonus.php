<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app' ) ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/css/billing-success.css?v=' . time()) ?>" />
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container" style="padding-top: 80px;">

            <!-- HERO -->
            <div class="success-hero">
                <div class="success-hero__left" style="border-color: rgba(16, 185, 129, 0.3);">
                    <style>
                        .success-hero__left::before {
                            background: linear-gradient(90deg, #10b981, #059669);
                        }
                    </style>
                    <div class="kicker" style="color: #059669;"><?= lang('Billing.success_bonus_kicker') ?></div>

                    <div class="title-row">
                        <h1><?= lang('Billing.success_bonus_title') ?></h1>
                        <span class="status-badge" style="background: #ecfdf5; border-color: #a7f3d0; color: #065f46;">
                            <span class="status-ic" style="background: #10b981;" aria-hidden="true"></span>
                            <?= lang('Billing.success_bonus_approved') ?>
                        </span>
                    </div>

                    <p class="sub">
                        <?= lang('Billing.success_bonus_desc') ?>
                    </p>

                    <div class="hero-actions">
                        <a class="btn btn_primary" href="<?=site_url()?>dashboard" style="background: #10b981; box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);"><?= lang('Billing.success_bonus_btn_wallet') ?></a>
                        <a class="btn btn_light" href="<?=site_url()?>documentation"><?= lang('Billing.success_bonus_btn_docs') ?></a>
                    </div>

                    <div class="hero-note" style="background: #f0fdf4; border-color: #d1fae5; color: #064e3b;">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <?= lang('Billing.success_bonus_note') ?>
                    </div>
                </div>

                <!-- TICKET DE COMPRA -->
                <aside class="purchase-card" aria-label="Resumen de la recarga">
                    <div class="purchase-head">
                        <div class="purchase-title"><?= lang('Billing.success_bonus_summary_title') ?></div>
                        <div class="purchase-sub">Ref: <strong>#<?= htmlspecialchars($order_ref ?? 'BONUS-0001') ?></strong></div>
                    </div>

                    <div class="purchase-lines">
                        <div class="line"><span><?= lang('Billing.success_bonus_concept') ?></span><strong><?= lang('Billing.success_bonus_concept_val') ?></strong></div>
                        <div class="line"><span><?= lang('Billing.success_bonus_credits_added') ?></span><strong style="color: #10b981;">+<?= number_format($credits ?? 0, 0, ',', '.') ?></strong></div>
                        <div class="line"><span><?= lang('Billing.success_bonus_expiration') ?></span><strong><?= lang('Billing.success_bonus_never') ?></strong></div>
                        
                        <div class="ticket-divider"></div>

                        <?php 
                            $base = (float)($price ?? 0);
                            $iva = $base * 0.21;
                            $total = $base + $iva;
                        ?>
                        <div class="line"><span><?= lang('Billing.success_bonus_base') ?></span><strong><?= number_format($base, 2, ',', '.') ?> €</strong></div>
                        <div class="line"><span><?= lang('Billing.success_bonus_vat') ?></span><strong><?= number_format($iva, 2, ',', '.') ?> €</strong></div>
                        <div class="line total"><span><?= lang('Billing.success_bonus_total') ?></span><strong style="color: #10b981;"><?= number_format($total, 2, ',', '.') ?> €</strong></div>
                    </div>
                </aside>
            </div>

            <!-- Siguientes pasos (Premium Grid) -->
            <section class="next-steps">
                <div class="section-head">
                    <h2><?= lang('Billing.success_bonus_next_title') ?></h2>
                    <p><?= lang('Billing.success_bonus_next_desc') ?></p>
                </div>

                <div class="step-list">
                    <!-- Tarjeta 1 -->
                    <article class="step-card">
                        <div class="step-icon-box green">
                            1
                        </div>
                        <div class="step-body">
                            <h3><?= lang('Billing.success_bonus_step1_title') ?></h3>
                            <p>
                                <?= lang('Billing.success_bonus_step1_desc') ?>
                            </p>
                        </div>
                        <div class="step-actions">
                            <a class="btn btn_light btn_full" href="<?=site_url()?>dashboard"><?= lang('Billing.success_bonus_step1_btn') ?></a>
                        </div>
                    </article>

                    <!-- Tarjeta 2 -->
                    <article class="step-card">
                        <div class="step-icon-box blue">
                            2
                        </div>
                        <div class="step-body">
                            <h3><?= lang('Billing.success_bonus_step2_title') ?></h3>
                            <p>
                                <?= lang('Billing.success_bonus_step2_desc') ?>
                            </p>
                        </div>
                        <div class="step-actions">
                            <a class="btn btn_light btn_full" href="<?=site_url()?>documentation#enriquecimiento"><?= lang('Billing.success_bonus_step2_btn') ?></a>
                        </div>
                    </article>

                    <!-- Tarjeta 3 -->
                    <article class="step-card">
                        <div class="step-icon-box violet">
                            3
                        </div>
                        <div class="step-body">
                            <h3><?= lang('Billing.success_bonus_step3_title') ?></h3>
                            <p>
                                <?= lang('Billing.success_bonus_step3_desc') ?>
                            </p>
                        </div>
                        <div class="step-actions">
                            <a class="btn btn_light btn_full" href="<?=site_url()?>documentation#radar"><?= lang('Billing.success_bonus_step3_btn') ?></a>
                        </div>
                    </article>
                </div>
            </section>
         
        </div>
<?= $this->endSection() ?>
