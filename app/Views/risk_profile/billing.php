<?= $this->extend(($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app') ?>

<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/css/billing.css') ?>?v=<?= time() ?>" />
<style>
    /* Aislamiento y estilo premium para Solvencia Pro */
    .risk-billing-container {
        max-width: 1140px;
        margin: 0 auto;
        padding: 40px 20px 80px;
    }
    .risk-billing-hero {
        text-align: center;
        max-width: 780px;
        margin: 0 auto 36px;
    }
    .risk-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        padding: 6px 14px;
        border-radius: 999px;
        font-size: 0.78rem;
        font-weight: 800;
        letter-spacing: 0.05em;
        text-transform: uppercase;
        margin-bottom: 16px;
    }
    .risk-billing-title {
        font-size: 2.6rem;
        font-weight: 900;
        color: #0f172a;
        margin: 0 0 12px;
        letter-spacing: -0.03em;
        line-height: 1.18;
    }
    .risk-billing-subtitle {
        font-size: 1.15rem;
        color: #64748b;
        margin: 0 0 28px;
        line-height: 1.55;
    }
    .risk-plan-card-featured {
        background: #ffffff;
        border: 2px solid #2563eb;
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 20px 30px -10px rgba(37, 99, 235, 0.08), 0 8px 10px -6px rgba(37, 99, 235, 0.04);
        position: relative;
    }
    .risk-plan-ribbon {
        position: absolute;
        top: -14px;
        left: 40px;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff;
        padding: 5px 16px;
        border-radius: 999px;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        box-shadow: 0 4px 8px rgba(37, 99, 235, 0.3);
    }
    .risk-price-wrapper {
        display: flex;
        align-items: baseline;
        gap: 8px;
        margin: 16px 0 6px;
    }
    .risk-price-amount {
        font-size: 3.6rem;
        font-weight: 900;
        color: #0f172a;
        line-height: 1;
        letter-spacing: -0.04em;
    }
    .risk-price-period {
        font-size: 1.1rem;
        font-weight: 700;
        color: #64748b;
    }
    .risk-price-vat {
        font-size: 0.85rem;
        color: #94a3b8;
        font-weight: 600;
        margin-left: 4px;
    }
    .risk-price-annual-note {
        font-size: 0.88rem;
        color: #10b981;
        font-weight: 700;
        margin-bottom: 24px;
        min-height: 20px;
    }
    .risk-feature-list {
        list-style: none;
        padding: 0;
        margin: 28px 0;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .risk-feature-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        font-size: 0.95rem;
        color: #1e293b;
        line-height: 1.45;
    }
    .risk-feature-icon {
        width: 24px;
        height: 24px;
        border-radius: 50%;
        background: #ecfdf5;
        color: #10b981;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        margin-top: 1px;
    }
    .risk-feature-icon svg {
        width: 14px;
        height: 14px;
    }
    .risk-btn-submit {
        width: 100%;
        height: 56px;
        background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
        color: #ffffff;
        border: none;
        border-radius: 14px;
        font-size: 1.1rem;
        font-weight: 900;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        transition: transform 0.15s ease, box-shadow 0.15s ease;
        box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.35);
    }
    .risk-btn-submit:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 20px -3px rgba(37, 99, 235, 0.45);
    }
    .risk-active-banner {
        background: #f0fdf4;
        border: 2px solid #86efac;
        border-radius: 20px;
        padding: 28px 32px;
        margin-bottom: 40px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 20px;
    }
    .risk-faq-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
helper('company');

/*
 * Los importes de esta página salen de `api_plans`, que es la MISMA fila que
 * lee Billing::checkout() para construir la sesión de Stripe. Estaban escritos
 * a mano (29 y 290, también dentro del objeto `prices` del JavaScript), así que
 * si un día se cambia la tarifa en la base de datos esta pantalla anuncia un
 * precio y la pasarela cobra otro. Es el fallo que ya ocurrió con el PDF —3,90 €
 * anunciados, 5,90 € cobrados—, aquí con un importe diez veces mayor y en la
 * pantalla donde el cliente da su tarjeta.
 *
 * Los respaldos son exactamente los mismos que usa el controlador (29 / 290),
 * para que no haya forma de que difieran.
 */
$planRiesgo    = $risk_plan ?? null;
$precioMes     = isset($planRiesgo->price_monthly) ? (float) $planRiesgo->price_monthly : 29.00;
$precioAnio    = isset($planRiesgo->price_annual)  ? (float) $planRiesgo->price_annual  : 290.00;
$precioPack5   = ((int) solvencia('centimos.pack5', 990)) / 100;

$ahorroAnual   = max(0, ($precioMes * 12) - $precioAnio);
$anualPorMes   = $precioAnio / 12;

/** 29 → "29", 29,5 → "29,50": el titular no lleva decimales si no los necesita. */
$eurosCorto = static function (float $n): string {
    return floor($n) == $n
        ? number_format($n, 0, ',', '.')
        : number_format($n, 2, ',', '.');
};
$eurosLargo = static fn (float $n): string => number_format($n, 2, ',', '.');
?>
<div class="risk-billing-container">

    <?php if (!empty($is_risk_subscribed)): ?>
        <!-- BANNER DE USUARIO YA SUSCRITO A SOLVENCIA PRO -->
        <div class="risk-active-banner">
            <div style="display: flex; align-items: center; gap: 18px;">
                <div style="width: 52px; height: 52px; border-radius: 16px; background: #10b981; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 8px 16px rgba(16, 185, 129, 0.25);">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                        <polyline points="22 4 12 14.01 9 11.01"></polyline>
                    </svg>
                </div>
                <div>
                    <h2 style="font-size: 1.35rem; font-weight: 900; color: #065f46; margin: 0 0 4px;">Tu suscripción a Solvencia Pro está activa</h2>
                    <p style="font-size: 0.95rem; color: #047857; margin: 0;">Vigilamos hasta <?= (int) solvencia('vigilanciasPro', 25) ?> empresas de tu cartera en el BORME y te avisamos de cada movimiento.</p>
                </div>
            </div>
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="<?= site_url('dashboard') ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #10b981; color: white; padding: 12px 22px; border-radius: 12px; font-weight: 800; text-decoration: none; font-size: 0.95rem; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);">
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Ir al Dashboard de Solvencia
                </a>
            </div>
        </div>
    <?php endif; ?>

    <!-- HERO DE SOLVENCIA -->
    <div class="risk-billing-hero">
        <div class="risk-badge">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
            Análisis de Solvencia & Riesgo Mercantil
        </div>
        <h1 class="risk-billing-title">Acceso Completo a Solvencia Pro</h1>
        <?php
        /*
         * Decía "dictámenes ejecutivos oficiales en PDF sin límites", y es la página
         * donde se paga. Dos problemas en una frase: "oficiales" sobre una conclusión
         * propia, y "sin límites" cuando el plan tiene tope de 300 consultas y 25
         * vigilancias — que es justo lo que se quitó del panel del suscriptor.
         * Prometer ilimitado en la pantalla de cobro es la que obliga a devolver.
         */
        ?>
        <p class="risk-billing-subtitle">Comprueba qué consta de tus clientes y proveedores en el Registro Mercantil, y entérate el mismo día en que se publique algo nuevo.</p>

        <!-- SELECTOR MENSUAL / ANUAL -->
        <div class="period-toggle-container" style="margin-bottom: 0;">
            <div class="period-toggle" role="group" aria-label="Periodicidad de facturación" style="background: #e2e8f0; padding: 6px; border-radius: 999px;">
                <button type="button" class="period-btn active" data-period="monthly" id="btnMonthly">
                    Facturación Mensual
                </button>
                <button type="button" class="period-btn" data-period="annual" id="btnAnnual" style="position: relative;">
                    Facturación Anual
                    <span class="badge-save" style="top: -12px; right: -10px; background: #10b981; border: 2px solid #f8fafc; color: #fff; padding: 2px 8px; font-size: 0.7rem; font-weight: 800; border-radius: 999px; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.25);">
                        Ahorra 2 meses (-17%)
                    </span>
                </button>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (session('error')): ?>
        <div style="margin-bottom: 28px; padding: 16px 20px; background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; border-radius: 14px; font-weight: 600; display: flex; align-items: center; gap: 12px;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
            <?= esc(session('error')) ?>
        </div>
    <?php endif; ?>

    <?php if (session('message')): ?>
        <div style="margin-bottom: 28px; padding: 16px 20px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 14px; font-weight: 600; display: flex; align-items: center; gap: 12px;">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
            <?= esc(session('message')) ?>
        </div>
    <?php endif; ?>

    <!-- GRID PRINCIPAL DE SOLVENCIA PRO -->
    <div class="billing-layout">
        
        <!-- COLUMNA IZQUIERDA: TARJETA DEL PLAN SOLVENCIA PRO & FORMULARIO -->
        <div class="billing-left">
            <div class="risk-plan-card-featured">
                <div class="risk-plan-ribbon">Plan Recomendado · Todo Incluido</div>

                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px;">
                    <div>
                        <h2 style="font-size: 1.85rem; font-weight: 900; color: #0f172a; margin: 0 0 6px;">Solvencia Pro</h2>
                        <p style="font-size: 0.95rem; color: #64748b; margin: 0;">Para directores financieros, gerentes y autónomos que necesitan asegurar sus ventas a crédito.</p>
                    </div>
                </div>

                <div class="risk-price-wrapper">
                    <span class="risk-price-amount" id="displayPrice"><?= $eurosCorto($precioMes) ?></span>
                    <span class="risk-price-period">€ / <span id="displayPeriodText">mes</span></span>
                    <span class="risk-price-vat">+ IVA</span>
                </div>
                <div class="risk-price-annual-note" id="displayAnnualNote">
                    &nbsp;
                </div>

                <!-- CARACTERÍSTICAS EXCLUSIVAS DE SOLVENCIA -->
                <ul class="risk-feature-list">
                    <li class="risk-feature-item">
                        <div class="risk-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div>
                            <strong style="color: #0f172a;">Vigilancia continua del Registro Mercantil:</strong>
                            <span style="color: #475569;"> te avisamos por correo el mismo día que aparece un acto nuevo en el BORME a nombre de cualquiera de las empresas que has consultado. Dejas de tener que volver a mirarlas.</span>
                        </div>
                    </li>
                    <li class="risk-feature-item">
                        <div class="risk-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div>
                            <strong style="color: #0f172a;">Cartera vigilada de hasta <?= (int) solvencia('vigilanciasPro', 25) ?> empresas:</strong>
                            <span style="color: #475569;"> te avisamos por correo el día que cualquiera de ellas se mueva en el BORME, sin que tengas que volver a mirarlas.</span>
                        </div>
                    </li>
                    <li class="risk-feature-item">
                        <div class="risk-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div>
                            <?php
                            /*
                             * Decía "scoring predictivo de impago" e "índice de probabilidad de
                             * morosidad". La validación temporal del 16-09 (50.000 empresas,
                             * corte 2023, horizonte 24 meses) dio AUC 0,468 sobre la cartera
                             * viva: el score NO predice hechos futuros. Lo que sí hace, y muy
                             * bien, es medir la gravedad de lo que ya consta publicado.
                             * Prometer una probabilidad que no se ha medido nunca es lo mismo
                             * que el "dictamen oficial" que ya quitamos, en la pantalla del cobro.
                             */
                            ?>
                            <strong style="color: #0f172a;">Índice de solvencia IES (0 - 100):</strong>
                            <span style="color: #475569;"> resume en una cifra la gravedad de lo que consta en el Registro Mercantil: concursos, cierres de hoja, disoluciones y retrasos en el depósito de cuentas.</span>
                        </div>
                    </li>
                    <li class="risk-feature-item">
                        <div class="risk-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div>
                            <strong style="color: #0f172a;">Informes ejecutivos en PDF:</strong>
                            <?php // "sello oficial de solvencia": no hay sello, y lo oficial es la fuente. ?>
                            <span style="color: #475569;"> con la puntuación, el histórico del BORME y la fecha de emisión, listos para adjuntar a una operación o a un comité.</span>
                        </div>
                    </li>
                    <li class="risk-feature-item">
                        <div class="risk-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div>
                            <?php
                            /*
                             * Aquí se prometía "balance, facturación, endeudamiento, fondo de
                             * maniobra y evolución de resultados". La aplicación NO TIENE ni un
                             * dato financiero: ni balances, ni ventas, ni EBITDA. Ninguna cifra
                             * de esas se calcula, se guarda ni se muestra en ninguna pantalla.
                             * Era la promesa más gruesa de la página del cobro.
                             */
                            ?>
                            <strong style="color: #0f172a;">Vigilancia diaria del BORME:</strong>
                            <span style="color: #475569;"> hasta 25 empresas en seguimiento, y un correo el mismo día en que a una de ellas le publican algo.</span>
                        </div>
                    </li>
                    <li class="risk-feature-item">
                        <div class="risk-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div>
                            <strong style="color: #0f172a;">Vigilancia y alertas BORME:</strong>
                            <span style="color: #475569;"> detección inmediata de concursos de acreedores, embargos, disoluciones y cambios de administradores.</span>
                        </div>
                    </li>
                    <li class="risk-feature-item">
                        <div class="risk-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div>
                            <?php
                            /*
                             * Aquí se anunciaba un "límite de crédito comercial sugerido"
                             * —"recomendación calculada"— que NO EXISTE: no hay una sola línea
                             * en el motor ni en la aplicación que lo calcule. Se prometía en la
                             * pantalla del cobro, en el correo de bienvenida de Pro y en los
                             * datos estructurados que lee Google. Sustituido por una función
                             * que sí está construida.
                             */
                            ?>
                            <strong style="color: #0f172a;">Tu cartera de un vistazo:</strong>
                            <span style="color: #475569;"> sube tus clientes en CSV y ve de golpe cuáles tienen incidencias, sin buscarlos uno a uno.</span>
                        </div>
                    </li>
                    <li class="risk-feature-item">
                        <div class="risk-feature-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div>
                            <strong style="color: #0f172a;">Sin permanencia:</strong>
                            <span style="color: #475569;"> cancela tu suscripción en 1 solo clic desde tu panel cuando lo decidas.</span>
                        </div>
                    </li>
                </ul>

                <!-- FORMULARIO DE CHECKOUT DIRECTO STRIPE -->
                <div style="border-top: 1px solid #f1f5f9; padding-top: 28px; margin-top: 28px;">
                    <form id="riskCheckoutForm" method="post" action="<?= site_url('billing/checkout') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="plan" value="risk_pro" />
                        <input type="hidden" name="period" id="periodInput" value="monthly" />
                        <input type="hidden" name="payment_method" value="stripe" />
                        <input type="hidden" name="country" value="ES" />
                        <input type="hidden" name="email" value="<?= esc($user->email ?? '') ?>" />
                        <input type="hidden" name="name" value="<?= esc($user->company ?: $user->name ?: '') ?>" />

                        <button type="submit" class="risk-btn-submit js-loading-btn" id="btnSubmitCheckout">
                            <span style="color: #fbbf24; font-size: 1.25rem;">⚡</span>
                            <span id="btnSubmitText">Activar Solvencia Pro — <?= $eurosCorto($precioMes) ?> € / mes</span>
                        </button>
                    </form>

                    <?php if (solvencia('garantiaActiva', true)): ?>
                        <div style="margin-top: 16px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 13px 16px; color: #15803d; font-size: 0.88rem; line-height: 1.5; text-align: center;">
                            🛡️ <strong><?= (int) solvencia('garantiaDias', 30) ?> días de garantía.</strong>
                            Si no te ha servido, te devolvemos el importe íntegro del periodo. Sin preguntas.
                            <a href="<?= site_url('garantia') ?>" style="color: #15803d; font-weight: 700;">Cómo funciona</a>
                        </div>
                    <?php endif; ?>

                    <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 16px; color: #64748b; font-size: 0.88rem; font-weight: 600;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        Pago seguro cifrado SSL vía Stripe · Activación inmediata
                    </div>
                </div>
            </div>

            <!-- ALTERNATIVA SIN SUSCRIPCIÓN: PACK 5 AUDITORÍAS (TRIPWIRE) -->
            <div style="margin-top: 20px; background: #eef2ff; border: 1.5px solid #c7d2fe; border-radius: 18px; padding: 22px 26px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
                <div style="max-width: 400px;">
                    <div style="display: inline-flex; align-items: center; gap: 6px; background: #4f46e5; color: #fff; padding: 2px 8px; border-radius: 999px; font-size: 0.68rem; font-weight: 800; text-transform: uppercase; margin-bottom: 6px;">
                        ¿Sin suscripción recurrente?
                    </div>
                    <div style="font-size: 1.08rem; font-weight: 800; color: #0f172a; margin-bottom: 2px;">
                        Pack 5 Auditorías con informe en PDF
                    </div>
                    <div style="font-size: 0.84rem; color: #4338ca; line-height: 1.4;">
                        Pago único de <strong><?= $eurosLargo($precioPack5) ?> € + IVA</strong> (<?= $eurosLargo($precioPack5 / 5) ?> €/auditoría). Saldo permanente sin fecha de caducidad.
                    </div>
                </div>
                <form method="post" action="<?= site_url('billing/checkout') ?>" style="margin: 0;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="plan" value="risk_pack_5" />
                    <input type="hidden" name="period" value="single" />
                    <button type="submit" style="background: #4f46e5; color: #ffffff; border: none; padding: 11px 18px; border-radius: 10px; font-weight: 800; font-size: 0.88rem; cursor: pointer; transition: background 0.2s; white-space: nowrap; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);" onmouseover="this.style.background='#4338ca';" onmouseout="this.style.background='#4f46e5';">
                        Comprar Pack 5 (<?= $eurosLargo($precioPack5) ?> €) ⚡
                    </button>
                </form>
            </div>
        </div>

        <!-- COLUMNA DERECHA: RESUMEN STICKY Y GARANTÍAS -->
        <div class="checkout-sidebar">
            
            <!-- TARJETA 1: RESUMEN DEL PEDIDO -->
            <div class="summary-card" style="margin-bottom: 24px;">
                <h3><?= lang('Billing.summary_title') ?></h3>
                <div style="padding: 32px;">
                    <div class="summary-row" style="margin-bottom: 12px; font-weight: 600; color: #475569;">
                        <span>Plan seleccionado</span>
                        <span class="value" style="color: #0f172a; font-weight: 800;">Solvencia Pro</span>
                    </div>

                    <div class="summary-row" style="margin-bottom: 12px; font-weight: 600; color: #475569;">
                        <span>Periodicidad</span>
                        <span class="value" id="sumPeriod" style="color: #0f172a; font-weight: 800;">Mensual</span>
                    </div>

                    <div class="summary-row" style="margin-bottom: 12px; font-weight: 600; color: #475569;">
                        <span>Subtotal (Base)</span>
                        <span class="value" style="color: #0f172a; font-weight: 800;"><span id="sumSubtotal"><?= $eurosLargo($precioMes) ?></span> €</span>
                    </div>

                    <div class="summary-row" style="margin-bottom: 24px; font-weight: 600; color: #475569;">
                        <span>IVA (21%)</span>
                        <span class="value" style="color: #0f172a; font-weight: 800;"><span id="sumIva"><?= $eurosLargo($precioMes * 0.21) ?></span> €</span>
                    </div>

                    <div class="summary-row total" style="margin-top: 0; padding-top: 20px; border-top: 2px dashed #cbd5e1; align-items: center;">
                        <span style="font-size: 1.1rem; color: #0f172a; font-weight: 900;"><?= lang('Billing.total') ?></span>
                        <span class="value" style="color: #2152ff; font-size: 1.5rem; font-weight: 900;"><span id="sumPrice"><?= $eurosLargo($precioMes * 1.21) ?></span> €</span>
                    </div>

                    <div style="margin-top: 24px; display: flex; align-items: flex-start; gap: 12px;">
                        <div style="width: 28px; height: 28px; background: #ecfdf5; border: 1px solid #d1fae5; color: #10b981; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;">
                            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        </div>
                        <div>
                            <strong style="display: block; font-size: 0.9rem; font-weight: 800; color: #0f172a; margin-bottom: 2px;">Activación inmediata</strong>
                            <span style="font-size: 0.8rem; color: #64748b; line-height: 1.4; display: block;">Acceso completo en tu panel en cuanto se procese el pago.</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TARJETA 2: CONFIANZA, MÉTODOS Y TESTIMONIO -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 32px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02);">
                <h3 style="font-size: 1.15rem; font-weight: 900; margin: 0 0 20px; text-align: center; color: #0f172a;">Garantías de Contratación</h3>
                
                <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">
                    <div style="display: flex; align-items: flex-start; gap: 14px;">
                        <div style="width: 36px; height: 36px; background: #ecfdf5; color: #10b981; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                        </div>
                        <div>
                            <strong style="display: block; font-size: 0.9rem; font-weight: 800; color: #0f172a; margin-bottom: 2px;">Factura deducible con IVA</strong>
                            <span style="font-size: 0.82rem; color: #475569; display: block; line-height: 1.4;">Generada automáticamente con tus datos fiscales para tu contabilidad.</span>
                        </div>
                    </div>

                    <div style="display: flex; align-items: flex-start; gap: 14px;">
                        <div style="width: 36px; height: 36px; background: #eff6ff; color: #2563eb; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </div>
                        <div>
                            <strong style="display: block; font-size: 0.9rem; font-weight: 800; color: #0f172a; margin-bottom: 2px;">Cifrado bancario seguro</strong>
                            <span style="font-size: 0.82rem; color: #475569; display: block; line-height: 1.4;">Procesado directamente por Stripe sin almacenar los datos de tu tarjeta.</span>
                        </div>
                    </div>

                    <div style="display: flex; align-items: flex-start; gap: 14px;">
                        <div style="width: 36px; height: 36px; background: #f3e8ff; color: #9333ea; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        </div>
                        <div>
                            <strong style="display: block; font-size: 0.9rem; font-weight: 800; color: #0f172a; margin-bottom: 2px;">Sin compromiso de permanencia</strong>
                            <span style="font-size: 0.82rem; color: #475569; display: block; line-height: 1.4;">Baja en cualquier momento con un clic. Conservas el acceso hasta fin de periodo.</span>
                        </div>
                    </div>
                </div>

                <!-- LOGOS DE PAGO -->
                <div style="display: flex; gap: 8px; justify-content: center; margin-bottom: 24px;">
                    <div style="border: 1px solid #e2e8f0; border-radius: 6px; width: 48px; height: 30px; display: flex; align-items: center; justify-content: center; background: #fff;">
                        <span style="font-family: Arial, sans-serif; font-style: italic; font-weight: 900; color: #1434cb; font-size: 13px;">VISA</span>
                    </div>
                    <div style="border: 1px solid #e2e8f0; border-radius: 6px; width: 48px; height: 30px; display: flex; align-items: center; justify-content: center; background: #fff;">
                        <div style="display: flex; align-items: center; justify-content: center;">
                           <div style="width: 14px; height: 14px; border-radius: 50%; background: #eb001b; z-index: 2;"></div>
                           <div style="width: 14px; height: 14px; border-radius: 50%; background: #f79e1b; margin-left: -5px; z-index: 1;"></div>
                        </div>
                    </div>
                    <div style="border: 1px solid #e2e8f0; border-radius: 6px; width: 48px; height: 30px; display: flex; align-items: center; justify-content: center; background: #fff;">
                        <span style="font-family: Arial, sans-serif; font-weight: 800; color: #016fd0; font-size: 10px;">AMEX</span>
                    </div>
                    <div style="border: 1px solid #e2e8f0; border-radius: 6px; width: 48px; height: 30px; display: flex; align-items: center; justify-content: center; background: #fff;">
                        <span style="font-family: Arial, sans-serif; font-style: italic; font-weight: 800; color: #003087; font-size: 11px;">PayPal</span>
                    </div>
                </div>

                <!-- TESTIMONIO SOLVENCIA -->
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px;">
                    <div style="display:flex; gap: 3px; color: #fbbf24; margin-bottom: 10px;">
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        <svg width="16" height="16" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    </div>
                    <p style="font-size: 0.88rem; color: #1e293b; margin: 0 0 10px; line-height: 1.5; font-style: italic;">
                        "Detectamos a tiempo que un nuevo cliente que nos pedía 15.000€ a 60 días tenía incidencias en el BORME. Exigimos pago al contado y a los 2 meses entraron en concurso. Esta herramienta nos ha salvado la cuenta de resultados."
                    </p>
                    <div style="font-size: 0.8rem; color: #64748b; font-weight: 700;">
                        Marcos G. · Director Financiero (Distribución B2B)
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- SECCIÓN INFERIOR: GESTIÓN DE SUSCRIPCIÓN (SI EXISTE) Y FAQS -->
    <div style="margin-top: 60px;">
        <?php if (!empty($plan) && !empty($is_risk_subscribed)): ?>
            <!-- GESTIÓN DE SUSCRIPCIÓN ACTIVA -->
            <?php include APPPATH . 'Views/components/manage_subscription.php'; ?>
        <?php endif; ?>

        <!-- PREGUNTAS FRECUENTES -->
        <div style="max-width: 800px; margin: 60px auto 0;">
            <h2 style="font-size: 1.6rem; font-weight: 900; margin-bottom: 24px; text-align: center; color: #0f172a;">Preguntas Frecuentes sobre Solvencia Pro</h2>
            <div style="display: flex; flex-direction: column; gap: 16px;">
                <div class="risk-faq-card">
                    <strong style="display: block; font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">¿Qué empresas puedo consultar con mi suscripción?</strong>
                    <p style="margin: 0; font-size: 0.95rem; color: #475569; line-height: 1.5;">Puedes auditar cualquier sociedad limitada, anónima, autónomo o entidad registrada en el Registro Mercantil de España simplemente introduciendo su CIF o nombre comercial.</p>
                </div>
                <div class="risk-faq-card">
                    <strong style="display: block; font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">¿Las descargas de informes PDF tienen algún límite o coste extra?</strong>
                    <p style="margin: 0; font-size: 0.95rem; color: #475569; line-height: 1.5;">No hay coste por informe: las descargas de dictámenes en PDF están incluidas en el plan Solvencia Pro. Lo que sí tiene un tope es la cartera bajo vigilancia, <?= (int) solvencia('vigilanciasPro', 25) ?> empresas a la vez.</p>
                </div>
                <div class="risk-faq-card">
                    <strong style="display: block; font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">¿Existe compromiso de permanencia?</strong>
                    <p style="margin: 0; font-size: 0.95rem; color: #475569; line-height: 1.5;">En absoluto. Puedes cancelar tu suscripción con 1 solo clic desde tu panel de facturación en cualquier momento. Mantendrás el acceso activo hasta el final del periodo ya abonado.</p>
                </div>
                <div class="risk-faq-card">
                    <strong style="display: block; font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">¿Cómo recibo la factura de mi suscripción?</strong>
                    <p style="margin: 0; font-size: 0.95rem; color: #475569; line-height: 1.5;">Inmediatamente tras el pago se genera una factura oficial con tu CIF/NIF y el 21% de IVA desglosado, lista para descargar desde tu panel y deducir en tus liquidaciones trimestrales.</p>
                </div>
            </div>
        </div>
    </div>

</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
(function() {
    const btnMonthly = document.getElementById('btnMonthly');
    const btnAnnual = document.getElementById('btnAnnual');
    const periodInput = document.getElementById('periodInput');
    const displayPrice = document.getElementById('displayPrice');
    const displayPeriodText = document.getElementById('displayPeriodText');
    const displayAnnualNote = document.getElementById('displayAnnualNote');
    const sumPeriod = document.getElementById('sumPeriod');
    const sumSubtotal = document.getElementById('sumSubtotal');
    const sumIva = document.getElementById('sumIva');
    const sumPrice = document.getElementById('sumPrice');
    const btnSubmitText = document.getElementById('btnSubmitText');

    const prices = {
        // Mismos números que pinta PHP arriba, y los dos salen de api_plans.
        monthly: { base: <?= json_encode(round($precioMes, 2)) ?>, periodText: 'mes', annualNote: '&nbsp;' },
        annual:  { base: <?= json_encode(round($precioAnio, 2)) ?>, periodText: 'año', annualNote: <?= json_encode('Abono anual único de ' . $eurosCorto($precioAnio) . ' € (equivale a solo ' . $eurosLargo($anualPorMes) . ' € / mes' . ($ahorroAnual > 0 ? ' — te ahorras ' . $eurosCorto($ahorroAnual) . ' €' : '') . ')') ?> }
    };

    function setPeriod(period) {
        const isAnnual = (period === 'annual');
        
        btnMonthly.classList.toggle('active', !isAnnual);
        btnAnnual.classList.toggle('active', isAnnual);
        
        if (periodInput) periodInput.value = period;

        const data = isAnnual ? prices.annual : prices.monthly;
        const base = data.base;
        const iva = base * 0.21;
        const total = base + iva;

        if (displayPrice) displayPrice.textContent = base.toFixed(0);
        if (displayPeriodText) displayPeriodText.textContent = data.periodText;
        if (displayAnnualNote) displayAnnualNote.innerHTML = data.annualNote;

        if (sumPeriod) sumPeriod.textContent = isAnnual ? 'Anual' : 'Mensual';
        if (sumSubtotal) sumSubtotal.textContent = base.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (sumIva) sumIva.textContent = iva.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        if (sumPrice) sumPrice.textContent = total.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (btnSubmitText) {
            btnSubmitText.textContent = isAnnual
                ? <?= json_encode('Activar Solvencia Pro — ' . $eurosCorto($precioAnio) . ' € / año') ?>
                : <?= json_encode('Activar Solvencia Pro — ' . $eurosCorto($precioMes) . ' € / mes') ?>;
        }

        if (window.trackEvent) {
            trackEvent('risk_billing_period_changed', { period: period });
        }
    }

    if (btnMonthly) btnMonthly.addEventListener('click', () => setPeriod('monthly'));
    if (btnAnnual) btnAnnual.addEventListener('click', () => setPeriod('annual'));

    const form = document.getElementById('riskCheckoutForm');
    if (form) {
        form.addEventListener('submit', () => {
            if (window.trackEvent) {
                trackEvent('checkout_started', {
                    plan: 'risk_pro',
                    period: periodInput ? periodInput.value : 'monthly',
                    source: 'risk_billing'
                });
            }
        });
    }

    if (window.trackEvent) {
        trackEvent('risk_billing_view', { plan: 'risk_pro' });
    }
})();
</script>
<?= $this->endSection() ?>
