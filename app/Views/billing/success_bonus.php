<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app' ) ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/css/billing-success.css?v=' . time()) ?>" />
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container">

            <!-- HERO -->
            <div class="success-hero">
                <div class="success-hero__left" style="border-color: rgba(16, 185, 129, 0.3);">
                    <style>
                        .success-hero__left::before {
                            background: linear-gradient(90deg, #10b981, #059669);
                        }
                    </style>
                    <div class="kicker" style="color: #059669;">RECARGA COMPLETADA</div>

                    <div class="title-row">
                        <h1>Créditos añadidos.<br>Tu monedero está recargado.</h1>
                        <span class="status-badge" style="background: #ecfdf5; border-color: #a7f3d0; color: #065f46;">
                            <span class="status-ic" style="background: #10b981;" aria-hidden="true"></span>
                            Aprobado
                        </span>
                    </div>

                    <p class="sub">
                        Hemos confirmado tu pago y tus créditos ya están disponibles en tu monedero prepago. Recuerda que no tienen caducidad.
                    </p>

                    <div class="hero-actions">
                        <a class="btn btn_primary" href="<?=site_url()?>dashboard" style="background: #10b981; box-shadow: 0 10px 20px -5px rgba(16, 185, 129, 0.4);">Ir a mi Monedero</a>
                        <a class="btn btn_light" href="<?=site_url()?>documentation">Ver documentación</a>
                    </div>

                    <div class="hero-note" style="background: #f0fdf4; border-color: #d1fae5; color: #064e3b;">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Los créditos de este bono se consumirán únicamente cuando agotes tu cuota mensual gratuita o tu plan principal. Nunca caducan.
                    </div>
                </div>

                <!-- TICKET DE COMPRA -->
                <aside class="purchase-card" aria-label="Resumen de la recarga">
                    <div class="purchase-head">
                        <div class="purchase-title">Resumen de recarga</div>
                        <div class="purchase-sub">Ref: <strong>#<?= htmlspecialchars($order_ref ?? 'BONUS-0001') ?></strong></div>
                    </div>

                    <div class="purchase-lines">
                        <div class="line"><span>Concepto</span><strong>Bono API a Medida</strong></div>
                        <div class="line"><span>Créditos añadidos</span><strong style="color: #10b981;">+<?= number_format($credits ?? 0, 0, ',', '.') ?></strong></div>
                        <div class="line"><span>Caducidad</span><strong>Nunca</strong></div>
                        
                        <div class="ticket-divider"></div>

                        <?php 
                            $base = (float)($price ?? 0);
                            $iva = $base * 0.21;
                            $total = $base + $iva;
                        ?>
                        <div class="line"><span>Base imponible</span><strong><?= number_format($base, 2, ',', '.') ?> €</strong></div>
                        <div class="line"><span>IVA (21%)</span><strong><?= number_format($iva, 2, ',', '.') ?> €</strong></div>
                        <div class="line total"><span>Total abonado</span><strong style="color: #10b981;"><?= number_format($total, 2, ',', '.') ?> €</strong></div>
                    </div>
                </aside>
            </div>

            <!-- Siguientes pasos (Premium Grid) -->
            <section class="next-steps">
                <div class="section-head">
                    <h2>Siguientes pasos</h2>
                    <p>Ya puedes empezar a gastar tus créditos realizando peticiones a nuestra API.</p>
                </div>

                <div class="step-list">
                    <!-- Tarjeta 1 -->
                    <article class="step-card">
                        <div class="step-icon-box green">
                            1
                        </div>
                        <div class="step-body">
                            <h3>Copia tu API Key</h3>
                            <p>
                                Accede a tu dashboard principal para copiar la llave maestra que te permitirá autenticarte en todos nuestros endpoints.
                            </p>
                        </div>
                        <div class="step-actions">
                            <a class="btn btn_light btn_full" href="<?=site_url()?>dashboard">Obtener API Key</a>
                        </div>
                    </article>

                    <!-- Tarjeta 2 -->
                    <article class="step-card">
                        <div class="step-icon-box blue">
                            2
                        </div>
                        <div class="step-body">
                            <h3>Prueba el Enriquecimiento</h3>
                            <p>
                                Haz una llamada al endpoint básico (cuesta solo 1 crédito) y verifica cómo el saldo de tu monedero disminuye instantáneamente.
                            </p>
                        </div>
                        <div class="step-actions">
                            <a class="btn btn_light btn_full" href="<?=site_url()?>documentation#enriquecimiento">Ver endpoint</a>
                        </div>
                    </article>

                    <!-- Tarjeta 3 -->
                    <article class="step-card">
                        <div class="step-icon-box violet">
                            3
                        </div>
                        <div class="step-body">
                            <h3>Aprovecha el Radar</h3>
                            <p>
                                Al tener créditos de pago, se te desbloquean los endpoints premium como Radar o Scoring (3 créditos por llamada).
                            </p>
                        </div>
                        <div class="step-actions">
                            <a class="btn btn_light btn_full" href="<?=site_url()?>documentation#radar">Explorar premium</a>
                        </div>
                    </article>
                </div>
            </section>
         
        </div>
<?= $this->endSection() ?>
