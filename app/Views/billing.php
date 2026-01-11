<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/billing.css') ?>" />
    <style>
        /* Spinner simple (si no lo tienes ya en billing.css) */
        .btn-spinner{
            display:inline-block;
            width:14px;
            height:14px;
            margin-right:8px;
            border-radius:999px;
            border:2px solid rgba(255,255,255,.45);
            border-top-color: rgba(255,255,255,1);
            animation: spin .8s linear infinite;
            vertical-align:-2px;
        }
        @keyframes spin { to { transform: rotate(360deg);} }
    </style>
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <?=view('partials/header_inner') ?>

    <?php
    // Helpers defensivos
    $get = function ($src, string $key, $default = null) {
        if (is_array($src)) return $src[$key] ?? $default;
        if (is_object($src)) return $src->$key ?? $default;
        return $default;
    };

    $planName = $get($plan, 'name', 'Free');
    $periodEnd = $get($plan, 'current_period_end', null);

    $fmt = function ($n) { return number_format((int)$n, 0, ',', '.'); };
    ?>

    <main class="billing-main">
        <div class="container">
            <div class="billing-hero">
                <div class="billing-hero__left">
                    <div class="kicker">Planes y facturación</div>
                    <h1>Activa tu plan y elimina límites para producción</h1>
                    <p class="sub">
                        Pasa a Pro para trabajar sin sustos: más consultas, métricas de calidad
                        (latencia/errores) y SLA. Cambia de plan o cancela cuando quieras.
                    </p>

                    <div class="hero-meta">
                        <span class="pill">Plan actual: <strong><?= esc($planName) ?></strong></span>
                        <span class="dot-sep">•</span>
                        <span class="pill">Consultas este mes: <strong><?= esc($fmt($api_request_total_month ?? 0)) ?></strong></span>
                        <span class="dot-sep">•</span>
                        <span class="pill">
                            Renovación:
                            <strong>
                                <?= $periodEnd ? esc(date('d-m-Y', strtotime((string)$periodEnd))) : '—' ?>
                            </strong>
                        </span>
                    </div>

                    <p class="muted" style="margin:12px 0 0;">
                        Recomendación: si vas a integrar en producción (alta de clientes, KYC, scoring), Pro suele ser el punto óptimo.
                    </p>

                    <?php if (session('error')): ?>
                        <div class="auth-error" style="margin-top:14px;">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="billing-hero__right">
                    <a class="btn btn_light" href="<?=site_url()?>dashboard">Volver al dashboard</a>
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
                                Empieza con Pro y sube a Business si necesitas más límite, SLA avanzado o gestión de equipos.
                                <span style="white-space:nowrap;">Precios + IVA.</span>
                            </p>
                        </div>

                        <div class="period-toggle" role="group" aria-label="Periodicidad">
                            <button type="button" class="period-btn active" data-period="monthly">Mensual</button>
                            <button type="button" class="period-btn" data-period="annual">Anual <span class="save">Ahorra</span></button>
                        </div>
                    </div>

                    <div class="plan-grid" role="radiogroup" aria-label="Planes">
                        <label class="plan-card is-selected" for="plan_pro">
                            <input id="plan_pro" name="plan_ui" type="radio" value="pro" checked />

                            <div class="plan-top">
                                <div>
                                    <div class="plan-title">
                                        <span class="badge badge-ok">Más elegido</span>
                                        <span class="name">Pro</span>
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

                        <label class="plan-card" for="plan_business">
                            <input id="plan_business" name="plan_ui" type="radio" value="business" />

                            <div class="plan-top">
                                <div>
                                    <div class="plan-title">
                                        <span class="badge badge-neutral">Equipos</span>
                                        <span class="name">Business</span>
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

                    <div class="divider"></div>

                    <div class="kicker">Pago</div>
                    <h2>Completa la activación</h2>
                    <p class="muted">
                        Continuarás a una pasarela segura para pagar con <strong>Tarjeta (Stripe)</strong> o <strong>PayPal</strong>.
                        <span style="white-space:nowrap;">El IVA se añade en el checkout según país.</span>
                    </p>

                    <div style="margin:10px 0 0;">
                        <div class="muted" style="font-size:12px; margin-bottom:8px;">Método de pago</div>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <label class="pill" style="cursor:pointer;">
                                <input type="radio" name="payment_method_ui" id="pm_card" value="stripe" checked style="margin-right:8px;">
                                Tarjeta (Stripe)
                            </label>
                            <label class="pill" style="cursor:pointer;">
                                <input type="radio" name="payment_method_ui" id="pm_paypal" value="paypal" style="margin-right:8px;">
                                PayPal
                            </label>
                        </div>
                        <div class="muted" style="font-size:12px; margin-top:8px;">
                            Podrás confirmarlo en el paso final de pago.
                        </div>
                    </div>

                    <form class="billing-form" method="post" action="<?=site_url()?>billing/checkout">
                        <?= csrf_field() ?>

                        <div class="form-grid">
                            <div class="field">
                                <label for="bill_email">Email de facturación</label>
                                <input id="bill_email" name="email" type="email" placeholder="tu@email.com" autocomplete="email" />
                                <div class="muted" style="font-size:12px; margin-top:6px;">
                                    Factura, recibos y avisos de renovación.
                                </div>
                            </div>

                            <div class="field">
                                <label for="bill_name">Nombre / Empresa</label>
                                <input id="bill_name" name="name" type="text" placeholder="Nombre o razón social" />
                                <div class="muted" style="font-size:12px; margin-top:6px;">
                                    Recomendado si necesitas factura a nombre de empresa.
                                </div>
                            </div>

                            <div class="field">
                                <label for="bill_country">País</label>
                                <select id="bill_country" name="country" autocomplete="country">
                                    <option value="ES" selected>España</option>
                                </select>
                            </div>

                            <div class="field">
                                <label for="bill_vat">NIF/CIF (opcional)</label>
                                <input id="bill_vat" name="vat" type="text" placeholder="ESB12345678" />
                                <div class="muted" style="font-size:12px; margin-top:6px;">
                                    Si lo indicas, lo incluiremos en la factura.
                                </div>
                            </div>

                            <div class="field field-full">
                                <label for="bill_card">Datos de pago</label>
                                <input id="bill_card" name="card" type="text" placeholder="Se completa en Stripe/PayPal en el siguiente paso" />
                                <div class="muted" style="font-size:12px; margin-top:16px;">
                                    No guardamos datos de tarjeta. El pago se completa en Stripe/PayPal.
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <div class="secure-note" style="margin-bottom: 20px;">
                                <span class="secure-ic" aria-hidden="true"></span>
                                Pago seguro con Stripe/PayPal. <br/>Cancelas cuando quieras. <strong>Precio + IVA</strong>.
                            </div>

                            <div class="action-buttons">
                                <a class="btn btn_light" href="<?=site_url()?>dashboard">Cancelar</a>
                                <button class="btn btn_primary" type="submit" id="btnCheckout">Continuar al pago</button>
                            </div>
                        </div>

                        <!-- IMPORTANTÍSIMO: estos son los campos reales que viajarán al backend -->
                        <input type="hidden" name="period" id="periodInput" value="monthly" />
                        <input type="hidden" name="plan" id="planInput" value="pro" />
                        <input type="hidden" name="payment_method" id="paymentMethodInput" value="stripe" />
                    </form>
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
                            <div class="line total"><span>Total estimado</span><strong><span id="sumPrice">19</span> € <span class="muted" style="font-size:12px;">+ IVA</span></strong></div>
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

    <?=view('partials/footer') ?>
</div>

<script>
    (function(){
        const periodBtns  = document.querySelectorAll('.period-btn');
        const periodInput = document.getElementById('periodInput');

        const planInputs  = document.querySelectorAll('.plan-card input[type="radio"][name="plan_ui"]');
        const planCards   = document.querySelectorAll('.plan-card');

        const pmRadios    = document.querySelectorAll('input[name="payment_method_ui"]');
        const pmInput     = document.getElementById('paymentMethodInput');

        const planInput   = document.getElementById('planInput');

        const sumPlan     = document.getElementById('sumPlan');
        const sumPeriod   = document.getElementById('sumPeriod');
        const sumPrice    = document.getElementById('sumPrice');

        const btnCheckout = document.getElementById('btnCheckout');
        const form        = document.querySelector('.billing-form');

        function getPeriod(){
            return document.querySelector('.period-btn.active')?.dataset.period || 'monthly';
        }

        function setPeriod(period){
            periodBtns.forEach(b => b.classList.toggle('active', b.dataset.period === period));
            if (periodInput) periodInput.value = period;

            document.querySelectorAll('.per').forEach(el => el.textContent = (period === 'annual') ? 'año' : 'mes');
            update();
        }

        function setSelectedPlan(value){
            planCards.forEach(c => c.classList.remove('is-selected'));
            const input = document.querySelector('.plan-card input[value="'+value+'"][name="plan_ui"]');
            if(input){
                input.checked = true;
                input.closest('.plan-card').classList.add('is-selected');
            }
            update();
        }

        function update(){
            const period = getPeriod();
            const checked = document.querySelector('.plan-card input[name="plan_ui"]:checked');
            const plan = checked ? checked.value : 'pro';
            const card = checked ? checked.closest('.plan-card') : document.querySelector('.plan-card');

            if (planInput) planInput.value = plan;

            sumPlan.textContent = (plan === 'business') ? 'Business' : 'Pro';
            sumPeriod.textContent = (period === 'annual') ? 'Anual' : 'Mensual';

            if(card){
                const amountEl = card.querySelector('.amount');
                const value = (period === 'annual') ? amountEl.getAttribute('data-annual') : amountEl.getAttribute('data-monthly');
                amountEl.textContent = value;
                sumPrice.textContent = value;
            }

            const pm = document.querySelector('input[name="payment_method_ui"]:checked')?.value || 'stripe';
            if (pmInput) pmInput.value = pm;

            btnCheckout.textContent = (pm === 'paypal')
                ? ((plan === 'business') ? 'Continuar a PayPal (Business)' : 'Continuar a PayPal (Pro)')
                : ((plan === 'business') ? 'Continuar al pago (Business)' : 'Continuar al pago (Pro)');
        }

        periodBtns.forEach(btn => btn.addEventListener('click', () => setPeriod(btn.dataset.period)));
        planInputs.forEach(r => r.addEventListener('change', () => setSelectedPlan(r.value)));
        pmRadios.forEach(r => r.addEventListener('change', update));

        if (form) {
            form.addEventListener('submit', () => {
                btnCheckout.disabled = true;
                btnCheckout.innerHTML = '<span class="btn-spinner" aria-hidden="true"></span> Redirigiendo...';
            });
        }

        setSelectedPlan('pro');
        setPeriod('monthly');
        update();
    })();
</script>



</body>
</html>
