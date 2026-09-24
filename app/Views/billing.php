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
                    <h1 style="font-size: 2.75rem; margin-bottom: 12px;"><?= lang('Billing.title') ?></h1>
                    <p style="font-size: 1.1rem; line-height: 1.5;"><?= lang('Billing.subtitle') ?></p>
                </div>

                <div class="period-toggle-container" style="margin: 0;">
                    <div class="period-toggle" role="group" aria-label="Periodicidad" style="background: #e2e8f0; padding: 6px; border-radius: 12px; box-shadow: none;">
                        <button type="button" class="period-btn active" data-period="monthly" style="border-radius: 8px;"><?= lang('Billing.monthly') ?></button>
                        <button type="button" class="period-btn" data-period="annual" style="position: relative; border-radius: 8px;">
                            <?= lang('Billing.annual') ?>
                            <span class="badge-save" style="top: -14px; right: -12px; background: #10b981; border: 2px solid #f8fafc; color: #fff; padding: 2px 8px; font-size: 0.7rem; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.2);"><?= lang('Billing.save_20') ?></span>
                        </button>
                    </div>
                </div>
                
            </div>

            <div class="billing-layout">
                
                <!-- COLUMNA IZQUIERDA: TARJETAS Y FORMULARIO -->
                <div class="billing-left">
                    
                    <div class="plan-grid" role="radiogroup" aria-label="Planes">
                        <!-- TARJETA FREE -->
                        <label class="plan-card <?= ($planName === 'Free') ? 'is-current' : '' ?>" for="plan_free" data-plan="free" style="opacity: 0.95; cursor: default; border: 1px solid #e2e8f0; background: #ffffff;">
                            <input id="plan_free" name="plan_ui" type="radio" value="free" <?= ($planName === 'Free') ? 'checked' : '' ?> disabled style="display:none;" />
                            
                            <div class="plan-title">
                                <span class="name">Free</span>
                                <?php if ($planName === 'Free'): ?>
                                    <span class="badge-gray"><?= lang('Billing.current_plan') ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="plan-price">
                                <div class="amount" data-monthly="0" data-annual="0">0</div>
                                <div class="currency">€ / <span class="per"><?= lang('Billing.per_month') ?></span> <span style="opacity:0"><?= lang('Billing.plus_vat') ?></span></div>
                            </div>
                            
                            <div class="plan-desc">El plan básico gratuito.</div>
                            
                            <ul class="plan-features">
                                <li style="color: #64748b;"><div class="feature-icon" style="background: #f1f5f9; color: #64748b;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> 100 consultas en total (no se renuevan)</li>
                                <li style="color: #64748b;"><div class="feature-icon" style="background: #f1f5f9; color: #64748b;"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> Datos limitados</li>
                            </ul>
                        </label>
                        
                        <!-- TARJETA PRO -->
                        <label class="plan-card <?= ($planName === 'Pro') ? 'is-current' : '' ?> <?= (!$plan || $planName === 'Free') ? 'is-selected' : '' ?>" for="plan_pro" data-plan="pro">
                            <div class="plan-selection-indicator">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            </div>
                            <input id="plan_pro" name="plan_ui" type="radio" value="pro" <?= (!$plan || $planName === 'Free') ? 'checked' : '' ?> />
                            
                            <div class="plan-title">
                                <span class="name">Pro</span>
                                <?php if ($planName === 'Pro'): ?>
                                    <span class="badge-pro"><?= lang('Billing.current_plan') ?></span>
                                <?php else: ?>
                                    <span class="badge-pro"><?= lang('Billing.most_chosen') ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="plan-price">
                                <div class="amount" data-monthly="19" data-annual="182">19</div>
                                <div class="currency">€ / <span class="per"><?= lang('Billing.per_month') ?></span> <?= lang('Billing.plus_vat') ?></div>
                            </div>
                            
                            <div class="plan-desc"><?= lang('Billing.pro_desc') ?></div>
                            
                            <ul class="plan-features">
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> <?= lang('Billing.pro_f1') ?></li>
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> <?= lang('Billing.pro_f2') ?></li>
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> <?= lang('Billing.pro_f3') ?></li>
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> <?= lang('Billing.pro_f4') ?></li>
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> <?= lang('Billing.pro_f5') ?></li>
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
                                    <span class="badge-pro"><?= lang('Billing.current_plan') ?></span>
                                <?php else: ?>
                                    <span class="badge-green"><?= lang('Billing.teams') ?></span>
                                <?php endif; ?>
                            </div>
                            
                            <div class="plan-price">
                                <div class="amount" data-monthly="49" data-annual="470">49</div>
                                <div class="currency">€ / <span class="per"><?= lang('Billing.per_month') ?></span> <?= lang('Billing.plus_vat') ?></div>
                            </div>
                            
                            <div class="plan-desc"><?= lang('Billing.business_desc') ?></div>
                            
                            <ul class="plan-features">
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> <?= lang('Billing.bus_f1') ?></li>
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> <?= lang('Billing.bus_f2') ?></li>
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> <?= lang('Billing.bus_f3') ?></li>
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> <?= lang('Billing.bus_f4') ?></li>
                                <li><div class="feature-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div> <?= lang('Billing.bus_f5') ?></li>
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
                                <h3 style="margin: 0 0 12px; font-size: 1.25rem; font-weight: 900; color: #0f172a;"><?= lang('Billing.error_title') ?></h3>
                                <p style="margin: 0 0 20px; font-size: 0.95rem; color: #dc2626; line-height: 1.5; font-weight: 600; background: #fef2f2; padding: 12px; border-radius: 8px;">
                                    <?= esc(session('error')) ?>
                                </p>
                                <p style="margin: 0 0 24px; font-size: 0.85rem; color: #64748b; font-weight: 500; line-height: 1.4;">
                                    <?= lang('Billing.error_desc') ?>
                                </p>
                                <div style="display: flex; gap: 12px; justify-content: center;">
                                    <button type="button" onclick="document.getElementById('error-modal').remove()" style="padding: 12px 20px; background: #ffffff; border: 1px solid #cbd5e1; color: #475569; font-weight: 800; border-radius: 12px; cursor: pointer; transition: all 0.2s;"><?= lang('Billing.close') ?></button>
                                    <a href="<?= site_url('contacto') ?>" style="padding: 12px 20px; background: #2152ff; border: 1px solid #2152ff; color: #ffffff; font-weight: 800; border-radius: 12px; text-decoration: none; display: inline-block; transition: background 0.2s; box-shadow: 0 4px 6px -1px rgba(33, 82, 255, 0.2);"><?= lang('Billing.contact_support') ?></a>
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

                                    <!-- PASO OCULTO (Datos enviados al controlador si existen) -->
                                    <div style="display: none;">
                                        <input id="bill_email" name="email" type="hidden" value="<?= esc($get($user, 'email') ?? '') ?>" autocomplete="email" />
                                        <input id="bill_name" name="name" type="hidden" value="<?= esc($get($user, 'company') ?: $get($user, 'name') ?: '') ?>" />
                                        <input id="bill_vat" name="vat" type="hidden" value="" />
                                    </div>

                                    <!-- BOTON Y CONFIRMACION -->
                                    <div style="position: relative; z-index: 1;">
                                        <h3 style="margin: 0 0 16px; font-size: 1.25rem; font-weight: 900; color: #0f172a;"><?= lang('Billing.step2_title') ?></h3>
                                        
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
                                                    <span style="font-weight: 600; color: #0f172a;"><?= lang('Billing.immediate_activation') ?></span>
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div style="width: 24px; height: 24px; border-radius: 50%; background: #10b981; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                                                    <span style="font-weight: 600; color: #0f172a;"><?= lang('Billing.full_access') ?></span>
                                                </div>
                                                <div style="display: flex; align-items: center; gap: 12px;">
                                                    <div style="width: 24px; height: 24px; border-radius: 50%; background: #10b981; color: white; display: flex; align-items: center; justify-content: center; flex-shrink: 0;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg></div>
                                                    <span style="font-weight: 600; color: #0f172a;"><?= lang('Billing.no_commitment') ?></span>
                                                </div>
                                            </div>
                                        </div>

                                        <button class="btn-primary js-loading-btn" type="submit" id="btnCheckout" style="width: 100%; height: 56px; font-size: 1.1rem; border-radius: 12px; display: flex; align-items: center; justify-content: center; gap: 8px;">
                                            <span style="color: #fbbf24; font-size: 1.2rem;">⚡</span> <?= lang('Billing.activate_btn', ['Business']) ?>
                                        </button>
                                        
                                        <div class="secure-badge" style="justify-content: center; margin-top: 16px; color: #64748b; font-size: 0.9rem;">
                                            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                                            <?= lang('Billing.secure_payment') ?>
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
                        <h3><?= lang('Billing.summary_title') ?></h3>
                        <div style="padding: 32px;">
                        
                        <div class="summary-row" style="margin-bottom: 12px; font-weight: 600; color: #475569;">
                            <span><?= lang('Billing.chosen_plan') ?></span>
                            <span class="value" id="sumPlan" style="color: #0f172a; font-weight: 800;">Business</span>
                        </div>
                        
                        <div class="summary-row" style="margin-bottom: 12px; font-weight: 600; color: #475569;">
                            <span><?= lang('Billing.periodicity') ?></span>
                            <span class="value" id="sumPeriod" style="color: #0f172a; font-weight: 800;">Mensual</span>
                        </div>
                        
                        <div class="summary-row" style="margin-bottom: 12px; font-weight: 600; color: #475569;">
                            <span><?= lang('Billing.subtotal') ?></span>
                            <span class="value" style="color: #0f172a; font-weight: 800;"><span id="sumSubtotal">49,00</span> €</span>
                        </div>
                        
                        <div class="summary-row" style="margin-bottom: 24px; font-weight: 600; color: #475569;">
                            <span><?= lang('Billing.vat') ?></span>
                            <span class="value" style="color: #0f172a; font-weight: 800;"><span id="sumIva">10,29</span> €</span>
                        </div>
                        
                        <div class="summary-row total" style="margin-top: 0; padding-top: 24px; border-top: 2px dashed #cbd5e1; align-items: center;">
                            <span style="font-size: 1.1rem; color: #0f172a; font-weight: 900;"><?= lang('Billing.total') ?></span>
                            <span class="value" style="color: #2152ff; font-size: 1.4rem; font-weight: 900;"><span id="sumPrice">59,29</span> €</span>
                        </div>

                        <div style="margin-top: 24px; display: flex; align-items: flex-start; gap: 12px;">
                            <div style="width: 28px; height: 28px; background: #ecfdf5; border: 1px solid #d1fae5; color: #10b981; border-radius: 8px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; margin-top: 2px;">
                                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            </div>
                            <div>
                                <strong style="display: block; font-size: 0.9rem; font-weight: 800; color: #0f172a; margin-bottom: 2px;"><?= lang('Billing.immediate_use') ?></strong>
                                <span style="font-size: 0.8rem; color: #64748b; line-height: 1.4; display: block;"><?= lang('Billing.immediate_use_desc') ?></span>
                            </div>
                        </div>
                        </div>
                    </div>

                    <!-- TARJETA 2: CONFIANZA Y TESTIMONIO -->
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 32px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02), 0 4px 6px -4px rgba(0,0,0,0.02);">
                        
                        <h3 style="font-size: 1.25rem; font-weight: 900; margin: 0 0 24px; text-align: center; color: #0f172a;"><?= lang('Billing.trust_title') ?></h3>
                        
                        <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 32px;">
                            <div style="display: flex; align-items: flex-start; gap: 16px;">
                                <div style="width: 40px; height: 40px; background: #ecfdf5; color: #10b981; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                </div>
                                <div>
                                    <strong style="display: block; font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 2px;"><?= lang('Billing.secure_pay_title') ?></strong>
                                    <span style="font-size: 0.85rem; color: #475569; display: block;"><?= lang('Billing.secure_pay_desc') ?></span>
                                </div>
                            </div>
                            
                            <div style="display: flex; align-items: flex-start; gap: 16px;">
                                <div style="width: 40px; height: 40px; background: #eff6ff; color: #2563eb; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                </div>
                                <div>
                                    <strong style="display: block; font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 2px;"><?= lang('Billing.data_protected_title') ?></strong>
                                    <span style="font-size: 0.85rem; color: #475569; display: block; line-height: 1.4;"><?= lang('Billing.data_protected_desc') ?></span>
                                </div>
                            </div>

                            <div style="display: flex; align-items: flex-start; gap: 16px;">
                                <div style="width: 40px; height: 40px; background: #f3e8ff; color: #9333ea; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                                </div>
                                <div>
                                    <strong style="display: block; font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 2px;"><?= lang('Billing.no_commit_title') ?></strong>
                                    <span style="font-size: 0.85rem; color: #475569; display: block;"><?= lang('Billing.no_commit_desc') ?></span>
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
                        </div>
                        <div style="text-align: center; font-size: 0.75rem; color: #64748b; font-weight: 600; margin-bottom: 32px;">
                            <?= lang('Billing.more_methods') ?>
                        </div>

                        <!-- Datos comprobables en lugar de testimonio -->
                        <div style="background: #edf4ff; border-radius: 16px; padding: 20px 24px; text-align: center; font-size: 0.9rem; color: #0f172a; line-height: 1.6;">
                            ¿Quieres comprobarlo antes de pagar? Mira la <a href="https://status.apiempresas.es" target="_blank" rel="noopener" style="font-weight: 800; color: #1d4ed8;">disponibilidad en tiempo real</a> de cada endpoint o prueba la API con las <strong>100 consultas gratis</strong> del plan Free.
                        </div>
                    </div>

                </div>

            </div>

            <!-- SECCIÓN BOTTOM: GESTIÓN Y FAQS -->
            <div class="bottom-section" style="padding-bottom: 80px;">
                
                <!-- GESTIÓN DE SUSCRIPCIÓN -->
                <?php include APPPATH . 'Views/components/manage_subscription.php'; ?>

                <div style="max-width: 800px; margin: 64px auto 0;">
                <h3 style="font-size: 1.5rem; font-weight: 900; margin-bottom: 24px; text-align: center; color: #0f172a;"><?= lang('Billing.faq_title') ?></h3>
                <div style="display: flex; flex-direction: column; gap: 16px;">
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px;">
                        <strong style="display: block; font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;"><?= lang('Billing.faq_1_q') ?></strong>
                        <p style="margin: 0; font-size: 0.95rem; color: #475569; line-height: 1.5;"><?= lang('Billing.faq_1_a') ?></p>
                    </div>
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px;">
                        <strong style="display: block; font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;"><?= lang('Billing.faq_2_q') ?></strong>
                        <p style="margin: 0; font-size: 0.95rem; color: #475569; line-height: 1.5;"><?= lang('Billing.faq_2_a') ?></p>
                    </div>
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px;">
                        <strong style="display: block; font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;"><?= lang('Billing.faq_3_q') ?></strong>
                        <p style="margin: 0; font-size: 0.95rem; color: #475569; line-height: 1.5;"><?= lang('Billing.faq_3_a') ?></p>
                    </div>
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px;">
                        <strong style="display: block; font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;"><?= lang('Billing.faq_4_q') ?></strong>
                        <p style="margin: 0; font-size: 0.95rem; color: #475569; line-height: 1.5;"><?= lang('Billing.faq_4_a') ?></p>
                    </div>
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
            
            // Actualizar el texto del precio en todas las tarjetas
            document.querySelectorAll('.plan-card').forEach(c => {
                const amountEl = c.querySelector('.amount');
                if (amountEl && amountEl.hasAttribute('data-monthly')) {
                    amountEl.textContent = (period === 'annual') ? amountEl.getAttribute('data-annual') : amountEl.getAttribute('data-monthly');
                }
            });

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
                btnCheckout.innerHTML = `<span style="color: #fbbf24; font-size: 1.2rem;">⚡</span> <?= lang('Billing.activate_btn', ['${plan === "business" ? "Business" : "Pro"}']) ?>`;
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
                        if (checkoutTitle) checkoutTitle.textContent = '<?= lang('Billing.confirm_change') ?>';
                        if (checkoutSub) checkoutSub.innerHTML = '<?= lang('Billing.confirm_change_desc') ?>';
                    } else {
                        if (checkoutTitle) checkoutTitle.textContent = '<?= lang('Billing.complete_activation') ?>';
                        if (checkoutSub) checkoutSub.textContent = '<?= lang('Billing.complete_activation_desc') ?>';
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

        // Preselección desde la URL: los correos enlazan con ?plan=pro&period=annual.
        // Sin parámetros, el comportamiento de siempre.
        const qs = new URLSearchParams(window.location.search);
        const planUrl = qs.get('plan');
        if ((planUrl === 'pro' || planUrl === 'business') && planUrl !== currentPlan) {
            setSelectedPlan(planUrl);
        } else if (currentPlan === 'pro') {
            setSelectedPlan('business');
        } else if (currentPlan === 'business') {
            setSelectedPlan('business');
        } else {
            setSelectedPlan('pro');
        }
        if (qs.get('period') === 'annual') {
            setPeriod('annual');
        }
    })();
</script>
<?= $this->endSection() ?>
