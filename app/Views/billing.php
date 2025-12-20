<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/billing.css') ?>" />
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <header>
        <div class="container nav">
            <div class="brand">
                <a href="<?=site_url() ?>">
                    <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#2152FF"/>
                                <stop offset=".65" stop-color="#5C7CFF"/>
                                <stop offset="1" stop-color="#12B48A"/>
                            </linearGradient>
                            <filter id="ve-cardShadow" x="-20%" y="-20%" width="140%" height="140%">
                                <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20"/>
                            </filter>
                            <filter id="ve-checkShadow" x="-30%" y="-30%" width="160%" height="160%">
                                <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="#0B1A36" flood-opacity=".22"/>
                            </filter>
                            <radialGradient id="ve-gloss" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                            gradientTransform="translate(20 16) rotate(45) scale(28)">
                                <stop stop-color="#FFFFFF" stop-opacity=".32"/>
                                <stop offset="1" stop-color="#FFFFFF" stop-opacity="0"/>
                            </radialGradient>
                            <linearGradient id="ve-rim" x1="12" y1="52" x2="52" y2="12">
                                <stop stop-color="#FFFFFF" stop-opacity=".6"/>
                                <stop offset="1" stop-color="#FFFFFF" stop-opacity=".35"/>
                            </linearGradient>
                        </defs>

                        <g filter="url(#ve-cardShadow)">
                            <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)"/>
                            <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss)"/>
                            <rect x="6.5" y="6.5" width="51" height="51" rx="13.5" fill="none" stroke="url(#ve-rim)"/>
                        </g>

                        <path d="M18 33 L28 43 L46 22"
                              stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"
                              fill="none" filter="url(#ve-checkShadow)"/>
                    </svg>
                </a>

                <div class="brand-text">
                    <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                    <span class="brand-tag">Verificación empresarial en segundos</span>
                </div>
            </div>

            <nav class="desktop-only" aria-label="Principal">
                <a class="minor" href="<?=site_url() ?>billing">Planes y facturación</a>
                <span style="margin:0 12px; color:#cdd6ea">•</span>
                <a class="minor" href="<?=site_url() ?>usage">Consumo</a>
                <span style="margin:0 12px; color:#cdd6ea">•</span>
                <a class="minor" href="<?=site_url() ?>documentation">Documentación</a>
                <span style="margin:0 12px; color:#cdd6ea">•</span>
                <a class="minor" href="<?=site_url() ?>search_company">Buscador</a>
            </nav>

            <div class="desktop-only">
                <?php if(!session('logged_in')){ ?>
                    <a class="btn btn_header btn_header--ghost" href="<?=site_url() ?>enter"><span>Iniciar sesión</span></a>
                <?php } else { ?>
                    <a class="btn btn_header btn_header--ghost logout" href="<?=site_url() ?>logout"><span>Salir</span></a>
                <?php } ?>
            </div>
        </div>
    </header>

    <main class="billing-main">
        <div class="container">
            <div class="billing-hero">
                <div class="billing-hero__left">
                    <div class="kicker">Planes y facturación</div>
                    <h1>Activa tu plan y elimina límites para producción</h1>
                    <p class="sub">
                        Pasa a Pro por <strong>19 € / mes + IVA</strong> para trabajar sin sustos: más consultas, métricas de calidad
                        (latencia/errores) y SLA. Cambia de plan o cancela cuando quieras.
                    </p>

                    <div class="hero-meta">
                        <span class="pill">Plan actual: <strong><?= htmlspecialchars($current_plan ?? 'Free') ?></strong></span>
                        <span class="dot-sep">•</span>
                        <span class="pill">Consultas este mes: <strong><?= htmlspecialchars($usage_month ?? '0') ?></strong></span>
                        <span class="dot-sep">•</span>
                        <span class="pill">Renovación: <strong><?= htmlspecialchars($renewal_date ?? '—') ?></strong></span>
                    </div>

                    <p class="muted" style="margin:12px 0 0;">
                        Recomendación: si vas a integrar en producción (alta de clientes, KYC, scoring), Pro suele ser el punto óptimo.
                        <span style="white-space:nowrap;">El IVA se calcula en el checkout según país.</span>
                    </p>
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
                            <input id="plan_pro" name="plan" type="radio" value="pro" checked />

                            <div class="plan-top">
                                <div>
                                    <div class="plan-title">
                                        <span class="badge badge-ok">Más elegido</span>
                                        <span class="name">Pro</span>
                                    </div>
                                    <div class="desc">Ideal para producción: más límite + métricas.</div>
                                </div>

                                <div class="price">
                                    <div class="amount" data-monthly="19" data-annual="190">19</div>
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
                            <input id="plan_business" name="plan" type="radio" value="business" />

                            <div class="plan-top">
                                <div>
                                    <div class="plan-title">
                                        <span class="badge badge-neutral">Equipos</span>
                                        <span class="name">Business</span>
                                    </div>
                                    <div class="desc">Para volumen alto, roles y SLA avanzado.</div>
                                </div>

                                <div class="price">
                                    <div class="amount" data-monthly="99" data-annual="990">99</div>
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

                    <!-- SOLO UI/COPY: selector de método (sin funcionalidad real) -->
                    <div style="margin:10px 0 0;">
                        <div class="muted" style="font-size:12px; margin-bottom:8px;">Método de pago</div>
                        <div style="display:flex; gap:10px; flex-wrap:wrap;">
                            <label class="pill" style="cursor:pointer;">
                                <input type="radio" name="payment_method_ui" value="card" checked style="margin-right:8px;">
                                Tarjeta (Stripe)
                            </label>
                            <label class="pill" style="cursor:pointer;">
                                <input type="radio" name="payment_method_ui" value="paypal" style="margin-right:8px;">
                                PayPal
                            </label>
                        </div>
                        <div class="muted" style="font-size:12px; margin-top:8px;">
                            Podrás confirmarlo en el paso final de pago.
                        </div>
                    </div>

                    <form class="billing-form" method="post" action="<?=site_url()?>billing/checkout">
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
                                    <option value="PT">Portugal</option>
                                    <option value="FR">Francia</option>
                                    <option value="IT">Italia</option>
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
                                <label for="bill_card">Datos de pago (placeholder)</label>
                                <input id="bill_card" name="card" type="text" placeholder="En el siguiente paso podrás pagar con Tarjeta (Stripe) o PayPal" />
                                <div class="muted" style="font-size:12px; margin-top:6px;">
                                    No guardamos datos de tarjeta. El pago se completa en Stripe/PayPal.
                                </div>
                            </div>
                        </div>

                        <div class="form-actions">
                            <div class="secure-note">
                                <span class="secure-ic" aria-hidden="true"></span>
                                Pago seguro con Stripe/PayPal. Cancelas cuando quieras. <strong>Precio + IVA</strong> (se calcula en el checkout según país).
                            </div>

                            <div class="action-buttons">
                                <a class="btn btn_light" href="<?=site_url()?>dashboard">Cancelar</a>
                                <button class="btn btn_primary" type="submit" id="btnCheckout">Continuar al pago</button>
                            </div>
                        </div>

                        <input type="hidden" name="period" id="periodInput" value="monthly" />
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

        const planInputs  = document.querySelectorAll('.plan-card input[type="radio"]');
        const planCards   = document.querySelectorAll('.plan-card');

        const sumPlan     = document.getElementById('sumPlan');
        const sumPeriod   = document.getElementById('sumPeriod');
        const sumPrice    = document.getElementById('sumPrice');
        const btnCheckout = document.getElementById('btnCheckout');

        function getPeriod(){
            return document.querySelector('.period-btn.active')?.dataset.period || 'monthly';
        }

        function setPeriod(period){
            periodBtns.forEach(b => b.classList.toggle('active', b.dataset.period === period));
            periodInput.value = period;

            document.querySelectorAll('.per').forEach(el => el.textContent = (period === 'annual') ? 'año' : 'mes');
            update();
        }

        function setSelectedPlan(value){
            planCards.forEach(c => c.classList.remove('is-selected'));
            const input = document.querySelector('.plan-card input[value="'+value+'"]');
            if(input){
                input.checked = true;
                input.closest('.plan-card').classList.add('is-selected');
            }
            update();
        }

        function update(){
            const period = getPeriod();
            const checked = document.querySelector('.plan-card input:checked');
            const plan = checked ? checked.value : 'pro';
            const card = checked ? checked.closest('.plan-card') : document.querySelector('.plan-card');

            sumPlan.textContent = (plan === 'business') ? 'Business' : 'Pro';
            sumPeriod.textContent = (period === 'annual') ? 'Anual' : 'Mensual';

            if(card){
                const amountEl = card.querySelector('.amount');
                const value = (period === 'annual') ? amountEl.getAttribute('data-annual') : amountEl.getAttribute('data-monthly');
                amountEl.textContent = value;
                sumPrice.textContent = value;
            }

            btnCheckout.textContent = (plan === 'business') ? 'Continuar al pago (Business)' : 'Continuar al pago (Pro)';
        }

        periodBtns.forEach(btn => btn.addEventListener('click', () => setPeriod(btn.dataset.period)));
        planInputs.forEach(r => r.addEventListener('change', () => setSelectedPlan(r.value)));

        setSelectedPlan('pro');
        setPeriod('monthly');
    })();
</script>

</body>
</html>
