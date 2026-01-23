<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/billing.css') ?>" />
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
        $periodEnd = $get($plan, 'current_period_end', null);

        $fmt = function ($n) {
            return number_format((int) $n, 0, ',', '.'); };
        ?>


        <main class="billing-main">
            <div class="container">
                <div class="billing-hero">
                    <div class="billing-hero__left">
                        <div class="kicker">Planes y facturación</div>
                        <?php if ($plan): ?>
                            <h1>Gestiona tu suscripción <?= esc($planName) ?></h1>
                            <p class="sub">
                                Gracias por confiar en nosotros. Aquí puedes ver los detalles de tu plan,
                                descargar facturas o mejorar tu límite si tu volumen de negocio crece.
                            </p>
                        <?php else: ?>
                            <h1>Activa tu plan y elimina límites para producción</h1>
                            <p class="sub">
                                Pasa a Pro para trabajar sin sustos: más consultas, métricas de calidad
                                (latencia/errores) y SLA. Cambia de plan o cancela cuando quieras.
                            </p>
                        <?php endif; ?>

                        <div class="hero-meta">
                            <span class="pill <?= $plan ? 'pill--active' : '' ?>">Plan actual:
                                <strong><?= esc($planName) ?></strong></span>
                            <span class="dot-sep">•</span>
                            <span class="pill">Consultas este mes:
                                <strong><?= esc($fmt($api_request_total_month ?? 0)) ?></strong></span>
                            <?php if ($periodEnd): ?>
                                <span class="dot-sep">•</span>
                                <span class="pill">
                                    Renovación:
                                    <strong><?= esc(date('d/m/Y', strtotime((string) $periodEnd))) ?></strong>
                                </span>
                            <?php endif; ?>
                        </div>


                        <p class="muted" style="margin:12px 0 0;">
                            Recomendación: si vas a integrar en producción (alta de clientes, KYC, scoring), Pro suele
                            ser el punto óptimo.
                        </p>

                        <?php if (session('error')): ?>
                            <div class="auth-error" style="margin-top:14px;">
                                <?= esc(session('error')) ?>
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
                                <p class="muted">
                                    Empieza con Pro y sube a Business si necesitas más límite, SLA avanzado o gestión de
                                    equipos.
                                    <span style="white-space:nowrap;">Precios + IVA.</span>
                                </p>
                            </div>

                            <div class="period-toggle" role="group" aria-label="Periodicidad">
                                <button type="button" class="period-btn active" data-period="monthly">Mensual</button>
                                <button type="button" class="period-btn" data-period="annual">Anual <span
                                        class="save">Ahorra</span></button>
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

                            <label class="plan-card <?= ($planName === 'Business') ? 'is-current' : '' ?>" for="plan_business" data-plan="business">
                                <input id="plan_business" name="plan_ui" type="radio" value="business" <?= ($planName === 'Business') ? 'checked' : '' ?> />

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
                                    </div>
                                    
                                    <input type="hidden" name="country" value="ES">
                                </div>

                                <div class="form-actions">
                                    <div class="secure-note" style="margin-bottom: 20px;">
                                        <span class="secure-ic" aria-hidden="true"></span>
                                        <span>Pago seguro con Stripe/PayPal. <br />Cancelas cuando quieras. <strong>Precio + IVA</strong>.</span>
                                    </div>

                                    <div class="action-buttons">
                                        <a class="btn btn_light" href="<?= site_url() ?>dashboard">Cancelar</a>
                                        <button class="btn btn_primary" type="submit" id="btnCheckout">Continuar al pago</button>
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
                                    <div class="status-badge status-badge--active">Activa</div>
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

                                    <form id="formCancelSubscription" action="<?= site_url('billing/cancel-subscription') ?>" method="POST" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn_outline_danger">Cancelar suscripción</button>
                                    </form>
                                </div>

                                <script>
                                    document.addEventListener('DOMContentLoaded', function () {
                                        const formCancel = document.getElementById('formCancelSubscription');
                                        if (!formCancel) return;

                                        formCancel.addEventListener('submit', function (e) {
                                            e.preventDefault();

                                            Swal.fire({
                                                title: '¿Cancelar suscripción?',
                                                html: 'Lamentamos que te vayas. Seguirás teniendo acceso a las funciones de <strong>' + '<?= esc($planName) ?>' + '</strong> hasta el final de tu periodo de facturación actual.',
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
                                                    formCancel.submit();
                                                }
                                            });
                                        });
                                    });
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
                            <div class="line total"><span>Total estimado</span><strong><span id="sumPrice">19</span> €
                                    <span class="muted" style="font-size:12px;">+ IVA</span></strong></div>
                        </div>

                        <div class="summary-box">
                            <div class="srow">
                                <span class="dot ok" aria-hidden="true"></span>
                                <div>
                                    <strong>Más límite para producción</strong>
                                    <span>Evita bloqueos por volumen</span>
                                </div>
                            </div>
                            <div class="srow">
                                <span class="dot ok" aria-hidden="true"></span>
                                <div>
                                    <strong>Métricas de calidad</strong>
                                    <span>Latencia, errores y consumo</span>
                                </div>
                            </div>
                            <div class="srow">
                                <span class="dot ok" aria-hidden="true"></span>
                                <div>
                                    <strong>Soporte prioritario</strong>
                                    <span>Respuesta mejorada</span>
                                </div>
                            </div>
                        </div>

                        <p class="muted" style="margin:10px 0 0; font-size:12px;">
                            Tip: si estás en staging, puedes seguir en Free y activar Pro cuando pases a producción.
                        </p>
                    </section>
                </aside>

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
                    const value = (period === 'annual') ? amountEl.getAttribute('data-annual') : amountEl.getAttribute('data-monthly');
                    if (amountEl) amountEl.textContent = value;
                    if (sumPrice) sumPrice.textContent = value;
                }

                const pm = document.querySelector('input[name="payment_method_ui"]:checked')?.value || 'stripe';
                if (pmInput) pmInput.value = pm;

                if (btnCheckout) {
                    btnCheckout.textContent = (pm === 'paypal')
                        ? ((plan === 'business') ? 'Continuar a PayPal (Business)' : 'Continuar a PayPal (Pro)')
                        : ((plan === 'business') ? 'Continuar al pago (Business)' : 'Continuar al pago (Pro)');
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
                    if (btnCheckout) {
                        btnCheckout.disabled = true;
                        btnCheckout.innerHTML = '<span class="btn-spinner" aria-hidden="true"></span> Redirigiendo...';
                    }
                });
            }

            // Initial state
        if (currentPlan === 'pro') {
            // Si es pro, seleccionamos pro para que el formulario se oculte
            setSelectedPlan('pro');
        } else if (currentPlan === 'business') {
            setSelectedPlan('business');
        } else {
            // Si es free/ninguno, seleccionamos pro por defecto y mostramos
            setSelectedPlan('pro');
            setPeriod('monthly');
        }
    })();
</script>



</body>

</html>