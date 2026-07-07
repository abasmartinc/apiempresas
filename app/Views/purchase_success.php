<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app' ) ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/css/billing-success.css?v=' . time()) ?>" />
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container">

            <!-- HERO -->
            <div class="success-hero">
                <div class="success-hero__left">
                    <div class="kicker">COMPRA COMPLETADA</div>

                    <div class="title-row">
                        <h1>Plan activado.<br>Ya puedes usar la API en producción.</h1>
                        <span class="status-badge">
                            <span class="status-ic" aria-hidden="true"></span>
                            Confirmado
                        </span>
                    </div>

                    <p class="sub">
                        Hemos confirmado tu pago y tu plan <strong><?= htmlspecialchars($plan_name ?? 'Pro') ?></strong> ya está activo. En 2 minutos puedes dejar tu integración lista.
                        El IVA se calcula según tu país y aparece desglosado en el comprobante.
                    </p>

                    <div class="hero-actions">
                        <a class="btn btn_primary" href="<?=site_url()?>dashboard">Ir al Dashboard</a>
                        <a class="btn btn_light" href="<?=site_url()?>consumption">Ver métricas de consumo</a>
                    </div>

                    <div class="hero-note">
                        <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        Te enviaremos la factura al email de facturación de tu cuenta. Recuerda que puedes cambiar o cancelar el plan en cualquier momento.
                    </div>
                </div>

                <!-- TICKET DE COMPRA -->
                <aside class="purchase-card" aria-label="Resumen de la compra">
                    <div class="purchase-head">
                        <div class="purchase-title">Resumen de tu suscripción</div>
                        <div class="purchase-sub">Referencia: <strong>#<?= htmlspecialchars($order_ref ?? 'SUB-0001') ?></strong></div>
                    </div>

                    <div class="purchase-lines">
                        <div class="line"><span>Plan contratado</span><strong><?= htmlspecialchars($plan_name ?? 'Pro') ?></strong></div>
                        <div class="line"><span>Periodicidad</span><strong><?= htmlspecialchars($period_name ?? 'Mensual') ?></strong></div>
                        <div class="line"><span>Método de pago</span><strong><?= htmlspecialchars($payment_method ?? 'Tarjeta') ?></strong></div>
                        
                        <div class="ticket-divider"></div>

                        <?php 
                            $base = (float)($base_price ?? 0);
                            $iva = $base * 0.21;
                            $total = $base + $iva;
                        ?>
                        <div class="line"><span>Precio base</span><strong><?= number_format($base, 2, ',', '.') ?> €</strong></div>
                        <div class="line"><span>IVA (21%)</span><strong><?= number_format($iva, 2, ',', '.') ?> €</strong></div>
                        <div class="line total"><span>Total facturado</span><strong><?= number_format($total, 2, ',', '.') ?> €</strong></div>
                    </div>
                </aside>
            </div>

            <!-- Siguientes pasos (Premium Grid) -->
            <section class="next-steps">
                <div class="section-head">
                    <h2>Siguientes pasos recomendados</h2>
                    <p>Saca el máximo partido a tu nuevo plan desde el primer minuto.</p>
                </div>

                <div class="step-list">
                    <!-- Tarjeta 1 -->
                    <article class="step-card">
                        <div class="step-icon-box blue">
                            1
                        </div>
                        <div class="step-body">
                            <h3>Obtén tu Clave Maestra</h3>
                            <p>
                                Ve a tu dashboard y copia la API Key para hacer llamadas desde tu backend. Recomendamos guardarla siempre como variable de entorno.
                            </p>
                        </div>
                        <div class="step-actions">
                            <a class="btn btn_light btn_full" href="<?=site_url()?>dashboard">Abrir mi Dashboard</a>
                        </div>
                    </article>

                    <!-- Tarjeta 2 -->
                    <article class="step-card">
                        <div class="step-icon-box green">
                            2
                        </div>
                        <div class="step-body">
                            <h3>Lanza tu primera petición</h3>
                            <p>
                                Revisa nuestra documentación y valida un flujo real con un CIF de prueba. Nuestro endpoint responde en escasos milisegundos.
                            </p>
                        </div>
                        <div class="step-actions">
                            <a class="btn btn_light btn_full" href="<?=site_url()?>documentation">Ver Documentación</a>
                        </div>
                    </article>

                    <!-- Tarjeta 3 -->
                    <article class="step-card">
                        <div class="step-icon-box violet">
                            3
                        </div>
                        <div class="step-body">
                            <h3>Monitoriza la latencia</h3>
                            <p>
                                Al ser usuario de pago, dispones de métricas avanzadas. Controla la latencia y la tasa de error para asegurar la calidad de tu servicio.
                            </p>
                        </div>
                        <div class="step-actions">
                            <a class="btn btn_light btn_full" href="<?=site_url()?>consumption">Ir a consumo</a>
                        </div>
                    </article>
                </div>
            </section>
         
        </div>
<?= $this->endSection() ?>
