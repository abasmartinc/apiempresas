<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/billing.css') ?>?v=<?= filemtime(FCPATH . 'public/css/billing.css') ?>" />
    <style>
        /* Spinner simple (si no lo tienes ya en billing.css) */
        .btn-spinner {
            display: inline-block;
            width: 14px;
            height: 14px;
            margin-right: 8px;
            border-radius: 999px;
            border: 2px solid rgba(255, 255, 255, .45);
            border-top-color: rgba(255, 255, 255, 1);
            animation: spin .8s linear infinite;
            vertical-align: -2px;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div>

    <div class="auth-wrapper">
        <?= view('partials/header_inner') ?>

        <?php
        // Helpers defensivos
        $get = function ($src, string $key, $default = null) {
            if (is_array($src))
                return $src[$key] ?? $default;
            if (is_object($src))
                return $src->$key ?? $default;
            return $default;
        };

        $planNameRaw = $get($plan, 'plan_name', 'Free');
        $planName = 'Free';
        if (strcasecmp($planNameRaw, 'pro') === 0)
            $planName = 'Pro';
        if (strcasecmp($planNameRaw, 'business') === 0)
            $planName = 'Business';
        if (strcasecmp($planNameRaw, 'radar b2b') === 0)
            $planName = 'Radar B2B';
        $periodEnd = $get($plan, 'current_period_end', null);

        $fmt = function ($n) {
            return number_format((int) $n, 0, ',', '.'); };
        ?>


        <main class="billing-main">
            <div class="container">
                <div class="billing-hero">
                    <div class="billing-hero__left">
                        <div class="kicker">Planes y facturación</div>
                        <?php if ($planName === 'Pro'): ?>
                            <h1>🚀 Mejora a tu plan Business</h1>
                            <p class="sub">
                                Escala tu integración con gestión de equipos, mayor volumen de consultas y soporte avanzado.
                            </p>
                        <?php elseif ($planName === 'Business'): ?>
                            <h1>Gestiona tu suscripción Business</h1>
                            <p class="sub">
                                Aquí puedes ver tus facturas, cambiar el método de pago o gestionar tu plan actual.
                            </p>
                        <?php else: ?>
                            <h1>👉 Activa tu plan Pro</h1>
                            <p class="sub">
                                Estás a un paso de escalar tu integración sin limitaciones.
                            </p>
                        <?php endif; ?>

                        <div class="hero-meta">
                            <span class="pill <?= $plan ? 'pill--active' : '' ?>">Plan actual:
                                <strong><?= esc($planName) ?><?= ($plan && $get($plan, 'status') === 'canceled') ? ' (Cancelado)' : '' ?></strong></span>
                            <span class="dot-sep">•</span>
                            <span class="pill">Consultas este mes:
                                <strong><?= esc($fmt($api_request_total_month ?? 0)) ?></strong></span>
                            <?php if ($periodEnd): ?>
                                <span class="dot-sep">•</span>
                                <span class="pill">
                                    <?= ($get($plan, 'status') === 'canceled') ? 'Vence:' : 'Renovación:' ?>
                                    <strong><?= esc(date('d/m/Y', strtotime((string) $periodEnd))) ?></strong>
                                </span>
                            <?php endif; ?>
                        </div>


                       

                        <?php if (session('error')): ?>
                            <div class="auth-error" style="margin-top:14px;">
                                <?= esc(session('error')) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session('message')): ?>
                            <div class="auth-error" style="margin-top:14px; background: #ecfdf5; color: #065f46; border-color: #34d399;">
                                <?= esc(session('message')) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="billing-hero__right">
                        <a class="btn btn_light" href="<?= site_url() ?>dashboard">Volver al dashboard</a>
                    </div>
                </div>

                <div class="billing-grid">

                    <!-- LEFT -->
                    <section class="panel">
                        <div class="panel-head">
                            <div>
                                <div class="kicker">Selecciona plan</div>
                                <h2>Elige el plan que encaja con tu volumen</h2>
                              
                            </div>

                            <div class="period-toggle" role="group" aria-label="Periodicidad">
                                <button type="button" class="period-btn active" data-period="monthly">Mensual</button>
                                <button type="button" class="period-btn" data-period="annual">Anual </button>
                            </div>
                        </div>
                        <div class="plan-grid" role="radiogroup" aria-label="Planes">
                            <label class="plan-card <?= ($planName === 'Pro') ? 'is-current' : '' ?> <?= (!$plan || $planName === 'Free') ? 'is-selected' : '' ?>" for="plan_pro" data-plan="pro">
                                <input id="plan_pro" name="plan_ui" type="radio" value="pro" <?= (!$plan || $planName === 'Free') ? 'checked' : '' ?> />

                                <div class="plan-top">
                                    <div>
                                        <div class="plan-title">
                                            <span class="name">Pro</span>
                                            <?php if ($planName === 'Pro'): ?>
                                                <span class="badge badge-current">Tu plan actual</span>
                                            <?php else: ?>
                                                <span class="badge badge-ok">Más elegido</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="desc">Ideal para producción: más límite + métricas.</div>
                                    </div>

                                    <div class="price">
                                        <div class="amount" data-monthly="19" data-annual="170">19</div>
                                        <div class="unit">€ / <span class="per">mes</span> <span class="muted" style="font-size:12px;">+ IVA</span></div>
                                    </div>
                                </div>

                                <ul class="features">
                                    <li>✓ Límite mensual superior (sin bloqueos inesperados)</li>
                                    <li>✓ Métricas de latencia/errores para monitorizar calidad</li>
                                    <li>✓ Soporte prioritario</li>
                                </ul>
                            </label>

                            <label class="plan-card <?= ($planName === 'Business') ? 'is-current' : '' ?> <?= ($planName === 'Pro') ? 'is-selected' : '' ?>" for="plan_business" data-plan="business">
                                <input id="plan_business" name="plan_ui" type="radio" value="business" <?= ($planName === 'Pro' || $planName === 'Business') ? 'checked' : '' ?> />

                                <div class="plan-top">
                                    <div>
                                        <div class="plan-title">
                                            <span class="name">Business</span>
                                            <?php if ($planName === 'Business'): ?>
                                                <span class="badge badge-current">Tu plan actual</span>
                                            <?php else: ?>
                                                <span class="badge badge-neutral">Equipos</span>
                                            <?php endif; ?>
                                        </div>
                                        <div class="desc">Para volumen alto, roles y SLA avanzado.</div>
                                    </div>

                                    <div class="price">
                                        <div class="amount" data-monthly="49" data-annual="470">49</div>
                                        <div class="unit">€ / <span class="per">mes</span> <span class="muted" style="font-size:12px;">+ IVA</span></div>
                                    </div>
                                </div>

                                <ul class="features">
                                    <li>✓ Límite alto para uso intensivo</li>
                                    <li>✓ SLA avanzado</li>
                                    <li>✓ Gestión de equipos</li>
                                </ul>
                            </label>
                        </div>

                        <div style="margin: 20px 0; padding: 12px; background: #f8fafc; border-radius: 12px; font-size: 14px; color: #475569; font-weight: 700; display: flex; align-items: center; gap: 8px; border: 1px solid #e2e8f0;">
                            <span>⚡ La mayoría de usuarios activan Pro antes de pasar a producción.</span>
                        </div>

                        <div id="checkout-section">
                            <div class="divider"></div>

                            <div class="kicker">Pago</div>
                            <h2 id="checkout-title">Completa la activación</h2>
                            <p class="muted" id="checkout-sub">
                                Continuarás a una pasarela segura para pagar con <strong>Tarjeta (Stripe)</strong> o <strong>PayPal</strong>.

                            </p>

                            <form class="billing-form" method="post" action="<?= site_url() ?>billing/checkout">
                                <?= csrf_field() ?>

                                <div class="form-grid">
                                    <div class="field">
                                        <label for="bill_email">Email de facturación</label>
                                        <input id="bill_email" name="email" type="email" placeholder="tu@email.com" value="<?= esc($get($user, 'email') ?? '') ?>" autocomplete="email" />
                                        <div class="muted" style="font-size:12px; margin-top:6px;">
                                            Factura, recibos y avisos de renovación.
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label for="bill_name">Nombre / Empresa</label>
                                        <input id="bill_name" name="name" type="text" placeholder="Nombre o razón social" value="<?= esc($get($user, 'company') ?: $get($user, 'name') ?: '') ?>" />
                                        <div class="muted" style="font-size:12px; margin-top:6px;">
                                            Recomendado si necesitas factura a nombre de empresa.
                                        </div>
                                    </div>

                                    <div class="field">
                                        <label for="bill_vat">NIF/CIF (opcional)</label>
                                        <input id="bill_vat" name="vat" type="text" placeholder="ESB12345678" />
                                        <div class="muted" style="font-size:12px; margin-top:6px;">
                                            Si lo indicas, lo incluiremos en la factura.
                                        </div>
                                    </div>

                                    <div class="field field-full">
                                        <div class="muted" style="font-size:12px; margin-bottom:8px;">Metodo de pago</div>
                                        <div class="pill" style="display:inline-flex; align-items:center; cursor:default;">
                                            Tarjeta de crédito / débito (Stripe)
                                        </div>
                                        <div style="margin-top: 8px; font-size: 13px; color: #16a34a; font-weight: 600; display: flex; align-items: center; gap: 6px;">
                                            <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                            Pago seguro con Stripe. Tus datos están protegidos.
                                        </div>
                                    </div>
                                    
                                    <input type="hidden" name="country" value="ES">
                                </div>

                                <div class="closing-block" style="margin-top: 32px; padding: 24px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 16px; margin-bottom: 24px;">
                                    <h3 style="margin: 0 0 8px; font-size: 1.25rem; color: #1e40af; font-weight: 800;">Estás a un paso de activar Pro para producción</h3>
                                    <p style="margin: 0; color: #1e40af; opacity: 0.9; font-size: 14px; line-height: 1.5;">Evita limitaciones antes de que tu aplicación empiece a recibir tráfico real.</p>
                                </div>

                                <div class="form-actions">
                                    <div class="secure-note" style="margin-bottom: 20px;">
                                        <span class="secure-ic" aria-hidden="true"></span>
                                        <span>Pago seguro con Stripe/PayPal. <br />Cancelas cuando quieras. <strong>Precio + IVA</strong>.</span>
                                    </div>

                                    <div class="action-buttons" style="flex-direction: column; gap: 12px; align-items: stretch;">
                                        <div style="margin-bottom: 8px; font-size: 14px; color: #475569; font-weight: 700; text-align: center;">
                                            ⚡ La mayoría de usuarios activan Pro antes de lanzar su integración.
                                        </div>
                                        <button class="btn btn_primary js-loading-btn" type="submit" id="btnCheckout" style="width: 100%; padding: 18px; font-size: 1.1rem; font-weight: 800;">👉 Activar Pro y usar en producción</button>
                                        <div style="text-align: center;">
                                            <div style="font-size: 13px; color: #16a34a; font-weight: 700; margin-bottom: 4px;">Acceso inmediato. Sin permanencia. Cancela cuando quieras.</div>
                                        </div>
                                        <a class="btn btn_light" href="<?= site_url() ?>dashboard" style="margin-top: 8px;">Cancelar</a>
                                    </div>
                                </div>

                                <!-- IMPORTANTÍSIMO: estos son los campos reales que viajarán al backend -->
                                <input type="hidden" name="period" id="periodInput" value="monthly" />
                                <input type="hidden" name="plan" id="planInput" value="pro" />
                                <input type="hidden" name="payment_method" id="paymentMethodInput" value="stripe" />
                            </form>
                        </div>

                        <?php if ($plan && $planName !== 'Free'): ?>
                            <!-- GESTIÓN DE SUSCRIPCIÓN ACTIVA -->
                            <div class="divider"></div>

                            <div class="manage-box">
                                <div class="manage-head">
                                    <div>
                                        <div class="kicker">Gestión</div>
                                        <h2>Estado de tu suscripción</h2>
                                    </div>
                                    <?php 
                                        $status = $get($plan, 'status', 'active');
                                        $isCanceled = ($status === 'canceled');
                                    ?>
                                    <div class="status-badge <?= $isCanceled ? 'status-badge--warning' : 'status-badge--active' ?>">
                                        <?= $isCanceled ? 'Cancelada' : 'Activa' ?>
                                    </div>
                                </div>

                                <div class="manage-info">
                                    <div class="min-card">
                                        <div class="min-label">Plan contratado</div>
                                        <div class="min-value"><?= esc($planName) ?></div>
                                    </div>
                                    <div class="min-card">
                                        <div class="min-label">Próximo cobro</div>
                                        <div class="min-value"><?= $periodEnd ? esc(date('d/m/Y', strtotime((string) $periodEnd))) : '—' ?></div>
                                    </div>
                                    <div class="min-card">
                                        <div class="min-label">Método de pago</div>
                                        <div class="min-value">Tarjeta (Stripe)</div>
                                    </div>
                                </div>

                                <div class="manage-box" style="margin-top: 32px; padding: 20px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                                    <div class="manage-head" style="margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">
                                        <div class="kicker" style="color: #64748b; font-size: 0.7rem; font-weight: 800; text-transform: uppercase;">Estado Detallado</div>
                                        <h3 style="font-size: 1.1rem; font-weight: 800; color: #1e293b;">Tus Planes</h3>
                                    </div>
                                    <div class="table-responsive">
                                        <table style="width: 100%; border-collapse: collapse; font-size: 0.85rem;">
                                            <thead>
                                                <tr style="text-align: left; color: #64748b; border-bottom: 1px solid #e2e8f0;">
                                                    <th style="padding: 10px 5px;">Plan</th>
                                                    <th style="padding: 10px 5px;">Estado</th>
                                                    <th style="padding: 10px 5px;">Fin Periodo</th>
                                                    <th style="padding: 10px 5px;">Acción</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($all_subscriptions ?? [] as $sub): ?>
                                                    <tr style="border-bottom: 1px solid #f1f5f9;">
                                                        <td style="padding: 10px 5px; font-weight: 700; color: #334155;"><?= esc($sub->plan_name) ?></td>
                                                        <td style="padding: 10px 5px;">
                                                            <span style="display: inline-block; padding: 2px 8px; border-radius: 99px; font-size: 0.7rem; font-weight: 700; background: <?= ($sub->status === 'canceled') ? '#fef3c7; color: #92400e;' : '#dcfce7; color: #166534;' ?>">
                                                                <?= ($sub->status === 'canceled') ? 'Cancelada' : 'Activa' ?>
                                                            </span>
                                                        </td>
                                                        <td style="padding: 10px 5px; color: #64748b;"><?= date('d/m/Y', strtotime((string)$sub->current_period_end)) ?></td>
                                                        <td style="padding: 10px 5px;">
                                                            <?php if ($sub->status === 'active'): ?>
                                                                <form class="form-cancel-sub-item" action="<?= site_url('billing/cancel-subscription') ?>" method="POST">
                                                                    <input type="hidden" name="sub_id" value="<?= $sub->id ?>">
                                                                    <input type="hidden" name="plan_name" value="<?= esc($sub->plan_name) ?>">
                                                                    <button type="submit" class="btn danger" style="padding: 4px 8px; font-size: 0.7rem; background: #ef4444; color: white; border: none; border-radius: 4px; cursor: pointer;">Cancelar</button>
                                                                </form>
                                                            <?php else: ?>
                                                                <span style="color: #94a3b8;">-</span>
                                                            <?php endif; ?>
                                                        </td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="manage-actions">
                                    <?php if (!empty($stripe_customer_id)): ?>
                                        <a href="<?= site_url('billing/portal') ?>" class="btn btn_primary">
                                            <span style="display:flex; align-items:center; gap:8px;">
                                                <svg style="width:16px; height:16px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                                Gestionar Facturación y Pagos
                                            </span>
                                        </a>
                                    <?php endif; ?>

                                    <a href="<?= site_url('billing/invoices') ?>" class="btn btn_light">Ver mis facturas</a>

                                    <?php if (!$isCanceled): ?>
                                        <form id="formCancelSubscription" action="<?= site_url('billing/cancel-subscription') ?>" method="POST" style="display:inline;">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn_outline_danger">Cancelar suscripción</button>
                                        </form>
                                    <?php else: ?>
                                        <div style="margin-left: 12px; font-size: 0.85rem; color: #94a3b8; font-weight: 700;">
                                            Tu suscripción finalizará el <?= esc(date('d/m/Y', strtotime((string) $periodEnd))) ?>. No se realizarán más cobros.
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <script>
                                    (function() {
                                        function initCancelForm() {
                                            const cancelForms = document.querySelectorAll('#formCancelSubscription, .form-cancel-sub-item');
                                            
                                            cancelForms.forEach(form => {
                                                form.addEventListener('submit', function (e) {
                                                    e.preventDefault();

                                                    const planNameAttr = form.querySelector('input[name="plan_name"]')?.value || '<?= esc($planName) ?>';

                                                    if (typeof Swal === 'undefined') {
                                                        if (confirm('¿Estás seguro de que deseas cancelar tu suscripción a ' + planNameAttr + '?')) {
                                                            form.submit();
                                                        }
                                                        return;
                                                    }

                                                    Swal.fire({
                                                        title: '¿Cancelar suscripción?',
                                                        html: 'Lamentamos que te vayas. Seguirás teniendo acceso a las funciones de <strong>' + planNameAttr + '</strong> hasta el final de tu periodo de facturación actual.',
                                                        icon: 'warning',
                                                        showCancelButton: true,
                                                        confirmButtonText: 'Sí, cancelar suscripción',
                                                        cancelButtonText: 'Mantener plan',
                                                        reverseButtons: true,
                                                        focusCancel: true,
                                                        customClass: {
                                                            popup: 've-swal',
                                                            title: 've-swal-title',
                                                            htmlContainer: 've-swal-text',
                                                            confirmButton: 'btn danger ve-swal-confirm',
                                                            cancelButton: 'btn btn_header--ghost ve-swal-cancel',
                                                        },
                                                        buttonsStyling: false
                                                    }).then((result) => {
                                                        if (result.isConfirmed) {
                                                            form.submit();
                                                        }
                                                    });
                                                });
                                            });
                                        }

                                        if (document.readyState === 'loading') {
                                            document.addEventListener('DOMContentLoaded', initCancelForm);
                                        } else {
                                            initCancelForm();
                                        }
                                    })();
                                </script>

                                <?php if ($planName === 'Pro'): ?>
                                    <div class="upgrade-promo" id="upgrade-promo">
                                        <div class="promo-icon">🚀</div>
                                        <div class="promo-text">
                                            <strong>¿Necesitas más potencia?</strong>
                                            Pasa al plan Business para obtener SLA avanzado, gestión de equipos y límites superiores.
                                        </div>
                                        <button class="btn btn_primary btn_small" onclick="setSelectedPlan('business')">Mejorar a Business</button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </section>

                <!-- RIGHT -->
                <aside class="side">
                    <section class="summary">
                        <div class="summary-head">
                            <h3>Resumen</h3>
                            <span class="mini">Se confirma en checkout</span>
                        </div>

                        <div class="summary-lines">
                            <div class="line"><span>Plan</span><strong id="sumPlan">Pro</strong></div>
                            <div class="line"><span>Periodicidad</span><strong id="sumPeriod">Mensual</strong></div>
                            <div class="line"><span>Subtotal</span><strong><span id="sumSubtotal">19,00</span> €</strong></div>
                            <div class="line"><span>IVA (21%)</span><strong><span id="sumIva">3,99</span> €</strong></div>
                            <div class="line total"><span>Total estimado</span><strong><span id="sumPrice">22,99</span> €</strong></div>
                        </div>

                        <div class="summary-security">
                            <div class="security-header">
                                <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                <span>Pago seguro garantizado</span>
                            </div>
                            <div class="security-logos">
                                <img src="https://upload.wikimedia.org/wikipedia/commons/b/ba/Stripe_Logo%2C_revised_2016.svg" alt="Stripe" class="pm-logo-stripe">
                                <div class="logos-divider"></div>
                                <div class="card-brands">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/5/5e/Visa_Inc._logo.svg" alt="Visa">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal">
                                </div>
                            </div>
                        </div>

                        <ul class="summary-benefits">
                            <li>
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <div>
                                    <strong>Uso en producción sin límites inesperados</strong>
                                </div>
                            </li>
                            <li>
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <div>
                                    <strong>Mayor estabilidad y rendimiento</strong>
                                </div>
                            </li>
                            <li>
                                <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                <div>
                                    <strong>Soporte prioritario</strong>
                                </div>
                            </li>
                        </ul>

                        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e2e8f0; text-align: center;">
                            <div style="font-size: 13px; color: #475569; font-weight: 700;">Sin permanencia. Cancela cuando quieras.</div>
                            <div style="font-size: 13px; color: #64748b; margin-top: 4px;">Listo para usar en producción desde el primer momento.</div>
                        </div>

                        <div class="summary-tip">
                            <strong>Tip para desarrolladores:</strong>
                            <p>Si estás en staging, puedes seguir en Free y activar Pro cuando pases a producción definitiva.</p>
                        </div>
                    </section>
                </aside>

                <section class="faq-section">
                    <div class="kicker">Dudas frecuentes</div>
                    <h2>Preguntas sobre facturación</h2>
                    
                    <div class="faq-grid">
                        <div class="faq-item">
                            <h4>¿Recibiré factura oficial?</h4>
                            <p>Sí, tras cada pago recibirás automáticamente una factura legal con el desglose del IVA correspondiente a tu NIF/CIF.</p>
                        </div>
                        <div class="faq-item">
                            <h4>¿Puedo cancelar cuando quiera?</h4>
                            <p>Sin compromiso. Puedes cancelar tu suscripción con un solo clic desde tu panel y seguirás teniendo acceso hasta el final del periodo pagado.</p>
                        </div>
                        <div class="faq-item">
                            <h4>¿Cómo funciona el cambio de plan?</h4>
                            <p>Si pasas de Pro a Business, Stripe prorrateará el importe y solo pagarás la diferencia por el tiempo restante.</p>
                        </div>
                        <div class="faq-item">
                            <h4>¿El pago es seguro?</h4>
                            <p>Utilizamos Stripe como pasarela de pago. Tus datos bancarios nunca tocan nuestros servidores y están protegidos por cifrado bancario.</p>
                        </div>
                    </div>
                </section>
            </div>
    </div>
    </main>

    <?= view('partials/footer') ?>
    </div>

    <script>
        function setSelectedPlan(value) {
            const planCards = document.querySelectorAll('.plan-card');
            planCards.forEach(c => c.classList.remove('is-selected'));
            const input = document.querySelector('.plan-card input[value="' + value + '"][name="plan_ui"]');
            if (input) {
                input.checked = true;
                input.closest('.plan-card').classList.add('is-selected');
            }
            if (typeof window.updateBillingUI === 'function') window.updateBillingUI();
        }

        (function () {
            window.updateBillingUI = update;
            const periodBtns = document.querySelectorAll('.period-btn');
            const periodInput = document.getElementById('periodInput');

            const planInputs = document.querySelectorAll('.plan-card input[type="radio"][name="plan_ui"]');
            const planCards = document.querySelectorAll('.plan-card');

            const pmRadios = document.querySelectorAll('input[name="payment_method_ui"]');
            const pmInput = document.getElementById('paymentMethodInput');

            const planInput = document.getElementById('planInput');

            const sumPlan = document.getElementById('sumPlan');
            const sumPeriod = document.getElementById('sumPeriod');
            const sumSubtotal = document.getElementById('sumSubtotal');
            const sumIva = document.getElementById('sumIva');
            const sumPrice = document.getElementById('sumPrice');

            const btnCheckout = document.getElementById('btnCheckout');
            const form = document.querySelector('.billing-form');
            const checkoutSection = document.getElementById('checkout-section');
            const checkoutTitle = document.getElementById('checkout-title');
            const checkoutSub = document.getElementById('checkout-sub');
            const upgradePromo = document.getElementById('upgrade-promo');

            const currentPlan = '<?= esc($planName) ?>'.toLowerCase();

            function getPeriod() {
                return document.querySelector('.period-btn.active')?.dataset.period || 'monthly';
            }

            function setPeriod(period) {
                periodBtns.forEach(b => b.classList.toggle('active', b.dataset.period === period));
                if (periodInput) periodInput.value = period;

                document.querySelectorAll('.per').forEach(el => el.textContent = (period === 'annual') ? 'año' : 'mes');
                update();
            }


            function update() {
                const period = getPeriod();
                const checked = document.querySelector('.plan-card input[name="plan_ui"]:checked');
                const plan = checked ? checked.value : 'pro';
                const card = checked ? checked.closest('.plan-card') : document.querySelector('.plan-card');

                if (planInput) planInput.value = plan;

                sumPlan.textContent = (plan === 'business') ? 'Business' : 'Pro';
                sumPeriod.textContent = (period === 'annual') ? 'Anual' : 'Mensual';

                if (card) {
                    const amountEl = card.querySelector('.amount');
                    const value = parseFloat((period === 'annual') ? amountEl.getAttribute('data-annual') : amountEl.getAttribute('data-monthly'));
                    
                    const iva = value * 0.21;
                    const total = value + iva;

                    if (amountEl) amountEl.textContent = value.toFixed(0);
                    if (sumSubtotal) sumSubtotal.textContent = value.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    if (sumIva) sumIva.textContent = iva.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    if (sumPrice) sumPrice.textContent = total.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }

                const pm = document.querySelector('input[name="payment_method_ui"]:checked')?.value || 'stripe';
                if (pmInput) pmInput.value = pm;

                if (btnCheckout) {
                    btnCheckout.textContent = (pm === 'paypal')
                        ? ((plan === 'business') ? '👉 Activar Business y usar en producción' : '👉 Activar Pro y usar en producción')
                        : ((plan === 'business') ? '👉 Activar Business y usar en producción' : '👉 Activar Pro y usar en producción');
                }

                // Upgrade logic
                if (checkoutSection) {
                    if (plan === currentPlan) {
                        checkoutSection.style.display = 'none';
                        if (upgradePromo) upgradePromo.style.display = 'flex';
                    } else {
                        checkoutSection.style.display = 'block';
                        if (upgradePromo) upgradePromo.style.display = 'none';

                        if (currentPlan !== 'free' && currentPlan !== 'none') {
                            if (checkoutTitle) checkoutTitle.textContent = 'Confirma tu cambio de plan';
                            if (checkoutSub) checkoutSub.innerHTML = 'Estás a punto de mejorar tu cuenta a <strong>' + (plan === 'business' ? 'Business' : 'Pro') + '</strong>. Se aplicará el nuevo cargo y se prorrateará tu periodo actual si es necesario.';
                        } else {
                            if (checkoutTitle) checkoutTitle.textContent = 'Completa la activación';
                        }
                    }
                }
            }

            periodBtns.forEach(btn => btn.addEventListener('click', () => setPeriod(btn.dataset.period)));
            planInputs.forEach(r => r.addEventListener('change', () => setSelectedPlan(r.value)));
            pmRadios.forEach(r => r.addEventListener('change', update));

            if (form) {
                form.addEventListener('submit', () => {
                    // Handled globally via .js-loading-btn
                });
            }

        // Initial state logic for auto-selection
        if (currentPlan === 'pro') {
            // Si el usuario ya es Pro, le pre-seleccionamos el Business para facilitar el upgrade
            setSelectedPlan('business');
        } else if (currentPlan === 'business') {
            setSelectedPlan('business');
        } else {
            // Para usuarios Free o sin plan, pre-seleccionamos el Pro (nuestro plan más popular)
            setSelectedPlan('pro');
        }
    })();
</script>



</body>

</html>