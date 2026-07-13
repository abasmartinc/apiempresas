<?php
// Helpers defensivos
$get = function ($src, string $key, $default = null) {
    if (is_array($src)) return $src[$key] ?? $default;
    if (is_object($src)) return $src->$key ?? $default;
    return $default;
};

$planNameRaw = $get($plan, 'plan_name', 'Free');
$planName = 'Free';
if (strcasecmp($planNameRaw, 'pro') === 0) $planName = 'Pro';
if (strcasecmp($planNameRaw, 'business') === 0) $planName = 'Business';
if (strcasecmp($planNameRaw, 'radar b2b') === 0) $planName = 'Radar B2B';
$periodEnd = $get($plan, 'current_period_end', null);

$fmt = function ($n) {
    return number_format((int) $n, 0, ',', '.'); 
};
?>
<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app' ) ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/css/billing.css') ?>?v=<?= time() ?>" />
<style>
    /* Aislamiento del checkout */
    header .nav nav.desktop-only,
    header .nav .auth-buttons,
    header .nav .mobile-menu-toggle {
        display: none !important;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container billing-main">
            


            <!-- HERO Y TOGGLE REDISEÑADOS -->
            <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 24px; margin-bottom: 32px;">
                
                <div class="billing-hero-clean" style="text-align: left; margin: 0; max-width: 600px;">
                    <h1 style="font-size: 2.75rem; margin-bottom: 12px;">Planes y Precios</h1>
                    <p style="font-size: 1.1rem; line-height: 1.5;">Escala tu integración con límites adaptados a tu volumen de operaciones.<br>Sin sorpresas. Cancela cuando quieras.</p>
                </div>

                <div class="period-toggle-container" style="margin: 0;">
                    <div class="period-toggle" role="group" aria-label="Periodicidad" style="background: #e2e8f0; padding: 6px; border-radius: 12px; box-shadow: none;">
                        <button type="button" class="period-btn active" data-period="monthly" style="border-radius: 8px;">Mensual</button>
                        <button type="button" class="period-btn" data-period="annual" style="position: relative; border-radius: 8px;">
                            Anual
                            <span class="badge-save" style="top: -14px; right: -12px; background: #10b981; border: 2px solid #f8fafc; color: #fff; padding: 2px 8px; font-size: 0.7rem; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);">-20%</span>
                        </button>
                    </div>
                </div>
                
            </div>

            <div class="billing-layout">
                
                <!-- COLUMNA IZQUIERDA: TARJETAS Y FORMULARIO -->
                <div class="billing-left">
                    
                    <div class="plan-grid" role="radiogroup" aria-label="Planes">
                        
                        <!-- TARJETA PRO -->
                        <label class="plan-card <?= ($planName === 'Pro') ? 'is-current' : '' ?> <?= (!$plan || $planName === 'Free') ? 'is-selected' : '' ?>" for="plan_pro" data-plan="pro">
                            <div class="plan-selection-indicator">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                            <input id="plan_pro" name="plan_ui" type="radio" value="pro" <?= (!$plan || $planName === 'Free') ? 'checked' : '' ?> />
                            
                            <div class="plan-title">
                                <span class="name">Pro</span>
                                <?php if ($planName === 'Pro'): ?>
                                    <span class="badge-pro">Tu plan actual</span>
                                <?php else: ?>
                                    <span class="badge-pro">Más elegido</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="plan-price">
                                <div class="amount" data-monthly="19" data-annual="182">19</div>
                                <div class="currency">€ / <span class="per">mes</span> + IVA</div>
                            </div>
                            
                            <div class="plan-desc">La opción ideal para SaaS, ERPs y productos que ya necesitan validación en producción.</div>
                            
                            <ul class="plan-features">
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> 3.000 consultas al mes</li>
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> Datos completos BORME y Actividad</li>
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> Scoring Comercial IA (0-100)</li>
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> Listado de Constituciones</li>
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> Grafos de Poder Societario</li>
                            </ul>
                        </label>

                        <!-- TARJETA BUSINESS -->
                        <label class="plan-card <?= ($planName === 'Business') ? 'is-current' : '' ?> <?= ($planName === 'Pro') ? 'is-selected' : '' ?>" for="plan_business" data-plan="business">
                            <div class="plan-selection-indicator">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                            <input id="plan_business" name="plan_ui" type="radio" value="business" <?= ($planName === 'Pro' || $planName === 'Business') ? 'checked' : '' ?> />
                            
                            <div class="plan-title">
                                <span class="name">Business</span>
                                <?php if ($planName === 'Business'): ?>
                                    <span class="badge-pro">Tu plan actual</span>
                                <?php else: ?>
                                    <span class="badge-green">Equipos</span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="plan-price">
                                <div class="amount" data-monthly="49" data-annual="470">49</div>
                                <div class="currency">€ / <span class="per">mes</span> + IVA</div>
                            </div>
                            
                            <div class="plan-desc">Pensado para plataformas con más carga, procesos críticos y necesidades de mayor disponibilidad.</div>
                            
                            <ul class="plan-features">
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> 10.000 consultas al mes</li>
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> Webhooks Push</li>
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> IA Predictiva de Oportunidades</li>
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> Calculadora de Match B2B</li>
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> Soporte Prioritario Slack / Email</li>
                            </ul>
                        </label>
                    </div>

                    <?php if (session('error')): ?>
                        <!-- Error Modal -->
                        <div id="error-modal" style="position: fixed; inset: 0; z-index: 9999; display: flex; align-items: center; justify-content: center; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px);">
                            <div style="background: #ffffff; border-radius: 20px; width: 100%; max-width: 440px; padding: 32px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1), 0 10px 10px -5px rgba(0,0,0,0.04); text-align: center; position: relative; animation: modalIn 0.3s ease-out;">
                                <div style="width: 56px; height: 56px; border-radius: 50%; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                                    <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                                </div>
                                <h3 style="margin: 0 0 12px; font-size: 1.25rem; font-weight: 900; color: #0f172a;">Acción no procesada</h3>
                                <p style="margin: 0 0 20px; font-size: 0.95rem; color: #dc2626; line-height: 1.5; font-weight: 600; background: #fef2f2; padding: 12px; border-radius: 8px;">
                                    <?= esc(session('error')) ?>
                                </p>
                                <p style="margin: 0 0 24px; font-size: 0.85rem; color: #64748b; font-weight: 500; line-height: 1.4;">
                                    Tu cuenta no ha sufrido ningún cargo. Por favor, inténtalo de nuevo en unos minutos o contacta con soporte si el problema persiste.
                                </p>
                                <div style="display: flex; gap: 12px; justify-content: center;">
                                    <button type="button" onclick="document.getElementById('error-modal').remove()" style="padding: 12px 20px; background: #ffffff; border: 1px solid #cbd5e1; color: #475569; font-weight: 800; border-radius: 12px; cursor: pointer; transition: all 0.2s;">Cerrar</button>
                                    <a href="<?= site_url('contacto') ?>" style="padding: 12px 20px; background: #2152ff; border: 1px solid #2152ff; color: #ffffff; font-weight: 800; border-radius: 12px; text-decoration: none; display: inline-block; transition: background 0.2s; box-shadow: 0 4px 6px -1px rgba(33, 82, 255, 0.2);">Contactar Soporte</a>
                                </div>
                            </div>
                        </div>
                        <style>
                            @keyframes modalIn {
                                from { opacity: 0; transform: scale(0.95) translateY(10px); }
                                to { opacity: 1; transform: scale(1) translateY(0); }
                            }
                        </style>
                    <?php endif; ?>

                    <?php if (session('message')): ?>
                        <div style="margin-top:24px; padding: 16px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; border-radius: 12px; font-weight: 600;">
                            <?= esc(session('message')) ?>
                        </div>
                    <?php endif; ?>

                    <!-- BLOQUE DE PAGO (Stepped Layout) -->
                    <div id="checkout-section" style="margin-top: 40px; display: none;">
                        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 40px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02), 0 4px 6px -4px rgba(0,0,0,0.02);">
                            
                            <form class="billing-form" method="post" action="<?= site_url() ?>billing/checkout">
                                <?= csrf_field() ?>
                                
                                <div style="position: relative; padding-left: 36px;">
                                    <!-- Línea vertical conectora -->
                                    <div style="position: absolute; top: 16px; bottom: 32px; left: 14px; width: 2px; background: #e2e8f0; z-index: 0;"></div>

                                    <!-- PASO 1 -->
                                    <div style="position: relative; margin-bottom: 40px; z-index: 1;">
                                        <div style="position: absolute; left: -36px; top: -2px; width: 28px; height: 28px; border-radius: 50%; background: #2152ff; color: white; font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 14px; transform: translateX(-50%); box-shadow: 0 0 0 6px #ffffff;">1</div>
                                        <h3 style="margin: 0 0 4px; font-size: 1.25rem; font-weight: 900; color: #0f172a;">Datos de facturación</h3>
                                        <p style="margin: 0 0 16px; font-size: 0.95rem; color: #64748b;">Usaremos estos datos para emitir tu factura.</p>
                                        
                                        <div class="form-group" style="position: relative;">
                                            <label for="bill_email" style="font-weight: 800; color: #0f172a; display: block; margin-bottom: 8px;">Email de facturación</label>
                                            <div style="position: relative;">
                                                <div style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #64748b; display: flex;">
                                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                                </div>
                                                <input class="form-control" id="bill_email" name="email" type="email" placeholder="tu@email.com" value="<?= esc($get($user, 'email') ?? '') ?>" autocomplete="email" required style="padding-left: 48px; border-radius: 12px; height: 50px; background: #f8fafc; border: 1px solid #cbd5e1;" />
                                            </div>
                                        </div>

                                        <div id="billing_extra_fields" style="display:block; margin-top: 16px;">
                                            <div class="form-group">
                                                <label for="bill_name">Nombre o Razón Social</label>
                                                <input class="form-control" id="bill_name" name="name" type="text" placeholder="Empresa S.L." value="<?= esc($get($user, 'company') ?: $get($user, 'name') ?: '') ?>" style="border-radius: 12px; background: #f8fafc;" />
                                            </div>
                                            <div class="form-group">
                                                <label for="bill_vat">NIF/CIF</label>
                                                <input class="form-control" id="bill_vat" name="vat" type="text" style="border-radius: 12px; background: #f8fafc;" />
                                            </div>
                                        </div>
                                    </div>

                                    <!-- PASO 2 -->
                                    <div style="position: relative; z-index: 1;">
                                        <div style="position: absolute; left: -36px; top: -2px; width: 28px; height: 28px; border-radius: 50%; background: #2152ff; color: white; font-weight: 800; display: flex; align-items: center; justify-content: center; font-size: 14px; transform: translateX(-50%); box-shadow: 0 0 0 6px #ffffff;">2</div>
                                        <h3 style="margin: 0 0 16px; font-size: 1.25rem; font-weight: 900; color: #0f172a;">Activar plan y usar en producción</h3>
                                        
                                        <div style="border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; display: flex; gap: 32px; align-items: center; background: #ffffff; margin-bottom: 24px;">
                                            <div style="width: 140px; height: 140px; border-radius: 16px; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: inset 0 2px 4px rgba(255,255,255,0.8), 0 4px 6px -1px rgba(37, 99, 235, 0.1); border: 1px solid #bfdbfe;">
                                                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" style="filter: drop-shadow(0 2px 4px rgba(37, 99, 235, 0.2));">
                                                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                                    <path d="M9 12l2 2 4-4"></path>
                                                </svg>
                                            </div>
                                            <div style="display: flex; flex-direction: column; gap: 16px;">
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div style="width: 24px; height: 24px; border-radius: 50%; background: #10b981; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                                                    <span style="font-weight: 600; color: #0f172a;">Activación inmediata del plan</span>
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div style="width: 24px; height: 24px; border-radius: 50%; background: #10b981; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                                                    <span style="font-weight: 600; color: #0f172a;">Acceso completo a todas las funciones</span>
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div style="width: 24px; height: 24px; border-radius: 50%; background: #10b981; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                                                    <span style="font-weight: 600; color: #0f172a;">Sin permanencia. Cancela cuando quieras</span>
                                                </div>
                                            </div>
                                        </div>

                                        <button class="btn-primary js-loading-btn" type="submit" id="btnCheckout" style="width: 100%; height: 56px; font-size: 1.1rem; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                            <span style="color: #fbbf24; font-size: 1.2rem;">⚡</span> Activar Business y usar en producción
                                        </button>
                                        
                                        <div class="secure-badge" style="justify-content: center; margin-top: 16px; color: #64748b; font-size: 0.9rem;">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                            Pago 100% seguro y cifrado con Stripe
                                        </div>
                                    </div>
                                </div>

                                <input type="hidden" name="country" value="ES">
                                <input type="hidden" name="period" id="periodInput" value="monthly" />
                                <input type="hidden" name="plan" id="planInput" value="pro" />
                                <input type="hidden" name="payment_method" id="paymentMethodInput" value="stripe" />
                            </form>
                        </div>
                    </div>

                </div>

                <!-- COLUMNA DERECHA: RESUMEN STICKY -->
                <div class="checkout-sidebar">
                    
                    <!-- TARJETA 1: RESUMEN -->
                    <div class="summary-card" style="margin-bottom: 24px;">
                        <h3>Resumen del plan</h3>
                        <div style="padding: 32px;">
                        
                        <div class="summary-row" style="margin-bottom: 12px; font-weight: 600; color: #475569;">
                            <span>Plan elegido</span>
                            <span class="value" id="sumPlan" style="color: #0f172a; font-weight: 800;">Business</span>
                        </div>
                        
                        <div class="summary-row" style="margin-bottom: 12px; font-weight: 600; color: #475569;">
                            <span>Periodicidad</span>
                            <span class="value" id="sumPeriod" style="color: #0f172a; font-weight: 800;">Mensual</span>
                        </div>
                        
                        <div class="summary-row" style="margin-bottom: 12px; font-weight: 600; color: #475569;">
                            <span>Subtotal</span>
                            <span class="value" style="color: #0f172a; font-weight: 800;"><span id="sumSubtotal">49,00</span> €</span>
                        </div>
                        
                        <div class="summary-row" style="margin-bottom: 24px; font-weight: 600; color: #475569;">
                            <span>IVA (21%)</span>
                            <span class="value" style="color: #0f172a; font-weight: 800;"><span id="sumIva">10,29</span> €</span>
                        </div>
                        
                        <div class="summary-row total" style="margin-top: 0; padding-top: 24px; border-top: 2px dashed #cbd5e1; align-items: center;">
                            <span style="font-size: 1.1rem; color: #0f172a; font-weight: 900;">Total</span>
                            <span class="value" style="color: #2152ff; font-size: 1.4rem; font-weight: 900;"><span id="sumPrice">59,29</span> €</span>
                        </div>

                        <div style="margin-top: 24px; display: flex; align-items: flex-start; gap: 12px;">
                            <div style="width: 28px; height: 28px; background: #ecfdf5; border: 1px solid #d1fae5; color: #10b981; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            </div>
                            <div>
                                <strong style="display: block; font-size: 0.9rem; font-weight: 800; color: #0f172a; margin-bottom: 2px;">Uso en producción inmediato</strong>
                                <span style="font-size: 0.8rem; color: #64748b; line-height: 1.4; display: block;">Accede a todas las funcionalidades desde el momento de activación.</span>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- TARJETA 2: CONFIANZA Y TESTIMONIO -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 32px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02), 0 4px 6px -4px rgba(0,0,0,0.02);">
                        
                        <h3 style="font-size: 1.25rem; font-weight: 900; margin: 0 0 24px; text-align: center; color: #0f172a;">Confianza y seguridad</h3>
                        
                        <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 32px;">
                            <div style="display: flex; align-items: flex-start; gap: 16px;">
                                <div style="width: 40px; height: 40px; background: #ecfdf5; color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                </div>
                                <div>
                                    <strong style="display: block; font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 2px;">Pago 100% seguro</strong>
                                    <span style="font-size: 0.85rem; color: #475569; display: block;">Procesado con Stripe.</span>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: flex-start; gap: 16px;">
                                <div style="width: 40px; height: 40px; background: #eff6ff; color: #2563eb; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                </div>
                                <div>
                                    <strong style="display: block; font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 2px;">Tus datos protegidos</strong>
                                    <span style="font-size: 0.85rem; color: #475569; display: block; line-height: 1.4;">Ciframos tu información con SSL de 256 bits.</span>
                                </div>
                            </div>

                            <div style="display: flex; align-items: flex-start; gap: 16px;">
                                <div style="width: 40px; height: 40px; background: #f3e8ff; color: #9333ea; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                </div>
                                <div>
                                    <strong style="display: block; font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 2px;">Sin permanencia</strong>
                                    <span style="font-size: 0.85rem; color: #475569; display: block;">Cancela tu plan cuando quieras.</span>
                                </div>
                            </div>
                        </div>

                        <!-- Logos de pago -->
                        <div style="display: flex; gap: 10px; justify-content: center; margin-bottom: 12px;">
                            <div style="border: 1px solid #e2e8f0; border-radius: 6px; width: 50px; height: 32px; display: flex; align-items: center; justify-content: center; background: #fff;">
                                <span style="font-family: Arial, sans-serif; font-style: italic; font-weight: 900; color: #1434cb; font-size: 14px; letter-spacing: -0.5px;">VISA</span>
                            </div>
                            <div style="border: 1px solid #e2e8f0; border-radius: 6px; width: 50px; height: 32px; display: flex; align-items: center; justify-content: center; background: #fff;">
                                <div style="display: flex; align-items: center; justify-content: center;">
                                   <div style="width: 16px; height: 16px; border-radius: 50%; background: #eb001b; z-index: 2;"></div>
                                   <div style="width: 16px; height: 16px; border-radius: 50%; background: #f79e1b; margin-left: -6px; z-index: 1;"></div>
                                </div>
                            </div>
                            <div style="border: 1px solid #e2e8f0; border-radius: 6px; width: 50px; height: 32px; display: flex; align-items: center; justify-content: center; background: #fff;">
                                <span style="font-family: Arial, sans-serif; font-weight: 800; color: #016fd0; font-size: 11px;">AMEX</span>
                            </div>
                            <div style="border: 1px solid #e2e8f0; border-radius: 6px; width: 50px; height: 32px; display: flex; align-items: center; justify-content: center; background: #fff;">
                                <span style="font-family: Arial, sans-serif; font-style: italic; font-weight: 800; color: #003087; font-size: 12px;">PayPal</span>
                            </div>
                        </div>
                        <div style="text-align: center; font-size: 0.75rem; color: #64748b; font-weight: 600; margin-bottom: 32px;">
                            y más métodos seguros
                        </div>

                        <!-- Testimonio Integrado (Carrusel) -->
                        <div style="background: #edf4ff; border-radius: 16px; padding: 24px; position: relative; min-height: 190px; display: flex; flex-direction: column; justify-content: center;">
                            <div style="position: absolute; top: 16px; left: 16px; color: #60a5fa; opacity: 0.6;">
                                <svg width="32" height="32" viewBox="0 0 24 24" fill="currentColor"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"></path></svg>
                            </div>
                            <div style="display:flex; gap: 4px; color: #fbbf24; justify-content: center; margin-bottom: 16px;">
                                <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                <svg width="18" height="18" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            </div>
                            
                            <div id="review-slider" style="transition: opacity 0.4s ease-in-out; opacity: 1;">
                                <div style="display: flex; justify-content: center; margin-bottom: 12px;">
                                    <div id="review-avatar-fallback" style="display: none; width: 48px; height: 48px; border-radius: 50%; align-items: center; justify-content: center; background: linear-gradient(135deg, #1d4ed8 0%, #14b8a6 100%); color: #ffffff; font-weight: 900; font-size: 0.85rem; border: 3px solid #ffffff; box-shadow: 0 10px 24px rgba(37, 99, 235, 0.18);">AS</div>
                                    <img id="review-avatar" src="https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=96&h=96&q=80" alt="Foto de Alex S." loading="lazy" style="width: 48px; height: 48px; border-radius: 50%; object-fit: cover; border: 3px solid #ffffff; box-shadow: 0 10px 24px rgba(37, 99, 235, 0.18);">
                                </div>
                                <p id="review-text" style="font-size: 0.95rem; color: #0f172a; text-align: center; line-height: 1.6; margin: 0 0 16px; font-weight: 500;">La integración con APIEmpresas es brutal. Puedes ver parámetros clave y mucho más. Todo muy rápido y fiable.</p>
                                <div style="font-size: 0.85rem; text-align: center; color: #475569;">
                                    <span id="review-author" style="font-weight: 900; color: #0f172a;">Alex S.</span> · <span id="review-role">CTO en SaaS B2B</span>
                                </div>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', () => {
                                    const reviews = [
                                        { text: "La integración con APIEmpresas es brutal. Puedes ver parámetros clave y mucho más. Todo muy rápido y fiable.", author: "Alex S.", role: "CTO en SaaS B2B" },
                                        { text: "Pasamos de revisar NIFs manualmente a automatizar el 100% del onboarding de clientes. Nos ahorra decenas de horas al mes.", author: "Laura M.", role: "Dir. Operaciones" },
                                        { text: "La API es ultra rápida y la documentación impecable. Lo conectamos con nuestro ERP interno en menos de una mañana.", author: "David R.", role: "Lead Developer" }
                                    ];
                                    const reviewAvatars = {
                                        "Alex S.": { initials: "AS", avatar: "https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?auto=format&fit=crop&w=96&h=96&q=80" },
                                        "Laura M.": { initials: "LM", avatar: "https://images.unsplash.com/photo-1544005313-94ddf0286df2?auto=format&fit=crop&w=96&h=96&q=80" },
                                        "David R.": { initials: "DR", avatar: "https://images.unsplash.com/photo-1560250097-0b93528c311a?auto=format&fit=crop&w=96&h=96&q=80" }
                                    };
                                    const avatar = document.getElementById('review-avatar');
                                    const avatarFallback = document.getElementById('review-avatar-fallback');
                                    const updateAvatar = (review) => {
                                        if (!avatar || !avatarFallback) return;
                                        const avatarData = reviewAvatars[review.author] || { initials: review.author.slice(0, 2).toUpperCase(), avatar: '' };
                                        avatarFallback.textContent = avatarData.initials;
                                        avatarFallback.style.display = 'none';
                                        avatar.style.display = 'block';
                                        avatar.alt = 'Foto de ' + review.author;
                                        avatar.src = avatarData.avatar;
                                    };
                                    if (avatar) {
                                        avatar.addEventListener('error', () => {
                                            avatar.style.display = 'none';
                                            if (avatarFallback) avatarFallback.style.display = 'flex';
                                        });
                                    }
                                    let currentReview = 0;
                                    setInterval(() => {
                                        const slider = document.getElementById('review-slider');
                                        if(!slider) return;
                                        slider.style.opacity = 0;
                                        setTimeout(() => {
                                            currentReview = (currentReview + 1) % reviews.length;
                                            document.getElementById('review-text').textContent = reviews[currentReview].text;
                                            document.getElementById('review-author').textContent = reviews[currentReview].author;
                                            document.getElementById('review-role').textContent = reviews[currentReview].role;
                                            updateAvatar(reviews[currentReview]);
                                            slider.style.opacity = 1;
                                        }, 400); 
                                    }, 6000); 
                                });
                            </script>
                        </div>
                    </div>

                </div>

            </div>

            <!-- SECCIÓN BOTTOM: GESTIÓN Y FAQS -->
            <div class="bottom-section" style="padding-bottom: 80px;">
                
                <!-- GESTIÓN DE SUSCRIPCIÓN -->
                <?php include APPPATH . 'Views/components/manage_subscription.php'; ?>

                <div style="text-align: center; margin-bottom: 40px; margin-top: 40px;">
                    <h2 style="font-size: 1.75rem; font-weight: 900; color: var(--text-main); margin: 0;">Dudas frecuentes</h2>
                </div>

                <div class="faq-grid">
                    <div class="faq-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                        <div style="width: 40px; height: 40px; background: #e0e7ff; color: #4338ca; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        </div>
                        <h4 style="margin-top: 0;">¿Recibiré factura oficial?</h4>
                        <p>Sí, tras cada pago recibirás automáticamente una factura legal con el desglose del IVA correspondiente a tu NIF/CIF.</p>
                    </div>
                    <div class="faq-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                        <div style="width: 40px; height: 40px; background: #dcfce7; color: #15803d; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                        </div>
                        <h4 style="margin-top: 0;">¿Puedo cancelar cuando quiera?</h4>
                        <p>Sin compromiso. Puedes cancelar tu suscripción con un solo clic desde tu panel y seguirás teniendo acceso hasta el final del periodo pagado.</p>
                    </div>
                    <div class="faq-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                        <div style="width: 40px; height: 40px; background: #fef3c7; color: #b45309; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline></svg>
                        </div>
                        <h4 style="margin-top: 0;">¿Cómo funciona el cambio de plan?</h4>
                        <p>Si pasas de Pro a Business, Stripe prorrateará el importe y solo pagarás la diferencia por el tiempo restante de tu mensualidad.</p>
                    </div>
                    <div class="faq-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 32px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                        <div style="width: 40px; height: 40px; background: #f1f5f9; color: #334155; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px;">
                            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        </div>
                        <h4 style="margin-top: 0;">¿El pago es seguro?</h4>
                        <p>Utilizamos Stripe como pasarela de pago. Tus datos bancarios nunca tocan nuestros servidores y están protegidos por cifrado bancario de nivel militar.</p>
                    </div>
                </div>

            </div>

        </div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function setSelectedPlan(value) {
        const planCards = document.querySelectorAll('.plan-card');
        planCards.forEach(c => c.classList.remove('is-selected'));
        const input = document.querySelector('.plan-card input[value="' + value + '"][name="plan_ui"]');
        if (input) {
            input.checked = true;
            input.closest('.plan-card').classList.add('is-selected');
            
            if (window.trackEvent) {
                trackEvent('plan_selection_clicked', { 
                    plan: value,
                    period: document.querySelector('.period-btn.active')?.dataset.period || 'monthly'
                });
            }
        }
        if (typeof window.updateBillingUI === 'function') window.updateBillingUI();
    }

    (function () {
        window.updateBillingUI = update;
        const periodBtns = document.querySelectorAll('.period-btn');
        const periodInput = document.getElementById('periodInput');
        const planInputs = document.querySelectorAll('.plan-card input[type="radio"][name="plan_ui"]');
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

            if (btnCheckout) {
                btnCheckout.innerHTML = `<span style="color: #fbbf24; font-size: 1.2rem;">⚡</span> Activar ${plan === 'business' ? 'Business' : 'Pro'} y usar en producción`;
            }

            const stepPlanName = document.getElementById('stepPlanName');
            const stepPlanPrice = document.getElementById('stepPlanPrice');
            if (stepPlanName && card) {
                stepPlanName.textContent = plan === 'business' ? 'Business' : 'Pro';
                const amountEl = card.querySelector('.amount');
                const value = parseFloat((period === 'annual') ? amountEl.getAttribute('data-annual') : amountEl.getAttribute('data-monthly'));
                stepPlanPrice.textContent = value + ' € / ' + (period === 'annual' ? 'año' : 'mes');
            }

            if (checkoutSection) {
                if (plan === currentPlan) {
                    checkoutSection.style.display = 'none';
                } else {
                    checkoutSection.style.display = 'block';
                    if (currentPlan !== 'free' && currentPlan !== 'none') {
                        if (checkoutTitle) checkoutTitle.textContent = 'Confirma tu cambio de plan';
                        if (checkoutSub) checkoutSub.innerHTML = 'Estás a punto de mejorar tu cuenta. Se aplicará el nuevo cargo y se prorrateará tu periodo actual.';
                    } else {
                        if (checkoutTitle) checkoutTitle.textContent = 'Completa la activación';
                        if (checkoutSub) checkoutSub.textContent = 'Pago cifrado y seguro vía Stripe o PayPal.';
                    }
                }
            }
        }

        periodBtns.forEach(btn => btn.addEventListener('click', () => setPeriod(btn.dataset.period)));
        planInputs.forEach(r => r.addEventListener('change', () => setSelectedPlan(r.value)));

        if (form) {
            form.addEventListener('submit', () => {
                if (window.trackEvent) {
                    trackEvent('checkout_started', {
                        plan: document.getElementById('planInput').value,
                        period: document.getElementById('periodInput').value,
                        email: document.getElementById('bill_email').value
                    });
                }
            });
        }

        if (window.trackEvent) {
            trackEvent('checkout_view', { current_plan: currentPlan });
        }

        if (currentPlan === 'pro') {
            setSelectedPlan('business');
        } else if (currentPlan === 'business') {
            setSelectedPlan('business');
        } else {
            setSelectedPlan('pro');
        }
    })();
</script>
<?= $this->endSection() ?>
