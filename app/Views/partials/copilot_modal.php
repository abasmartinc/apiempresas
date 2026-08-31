<?php
$db = \Config\Database::connect();
$userId = session('user_id');
$hasCopilot = false;

if ($userId) {
    $activePlan = $db->table('user_subscriptions')
        ->select('api_plans.slug as plan_name, api_plans.id as plan_id')
        ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id')
        ->where('user_subscriptions.user_id', $userId)
        ->where('user_subscriptions.status', 'active')
        ->orderBy('user_subscriptions.created_at', 'DESC')
        ->get()->getRow();
        
    $planSlug = $activePlan ? strtolower(trim($activePlan->plan_name)) : 'free';
    
    if ($activePlan && ($activePlan->plan_id == 13 || strpos($planSlug, 'copiloto') !== false || strpos($planSlug, 'business') !== false)) {
        $hasCopilot = true;
    }
    
    // Also check trials if free? We will just show the sticky bar for logged in users, 
    // and let the backend handle the trials/upsell when they click.
}
?>

<style>
/* AI Copilot Global Styles */
.ai-copilot-modal {
    display: none;
    position: fixed;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(15, 23, 42, 0.7);
    backdrop-filter: blur(4px);
    z-index: 9999999;
    align-items: flex-start;
    justify-content: center;
    padding: 3vh 20px;
    overflow-y: auto;
}
.ai-copilot-content {
    background: #fff;
    border-radius: 16px;
    width: 100%;
    max-width: 950px;
    padding: 20px 24px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    position: relative;
    margin: 0 auto;
}
.ai-copilot-header {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-bottom: 24px;
}
.ai-copilot-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}
.ai-copilot-input {
    width: 100%;
    padding: 12px 16px;
    border: 1px solid #cbd5e1;
    border-radius: 8px;
    font-size: 0.95rem;
    margin-bottom: 20px;
    box-sizing: border-box;
    font-family: inherit;
}
.ai-copilot-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}
.ai-copilot-btn-premium {
    width: 100%;
    padding: 14px;
    background: linear-gradient(135deg, #2563eb, #4f46e5);
    color: #fff;
    border: none;
    border-radius: 12px;
    font-weight: 700;
    font-size: 1.05rem;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(37,99,235,0.3);
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 8px;
}
.ai-copilot-btn-premium:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37,99,235,0.4);
    background: linear-gradient(135deg, #1d4ed8, #4338ca);
}
.ai-copilot-result {
    display: none;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 16px;
    font-size: 0.95rem;
    line-height: 1.5;
    color: #334155;
}
@keyframes spin { 100% { transform: rotate(360deg); } }

/* Sticky Bar Styles */
.ai-copilot-sticky-bar {
    position: fixed;
    bottom: 20px;
    left: 50%;
    transform: translateX(-50%);
    background: #ffffff;
    border-radius: 100px;
    padding: 8px 12px;
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.2), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    display: flex;
    align-items: center;
    gap: 12px;
    z-index: 999990;
    border: 1px solid #e2e8f0;
}
.ai-copilot-sticky-input {
    border: none;
    background: #f8fafc;
    padding: 10px 16px;
    border-radius: 100px;
    font-size: 0.95rem;
    outline: none;
    width: 220px;
    transition: width 0.3s ease;
}
.ai-copilot-sticky-input:focus {
    width: 280px;
    background: #f1f5f9;
}
.ai-copilot-sticky-btn {
    background: linear-gradient(135deg, #9333ea, #d946ef);
    color: white;
    border: none;
    border-radius: 100px;
    padding: 10px 24px;
    font-weight: 700;
    font-size: 0.95rem;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(147, 51, 234, 0.3);
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: 6px;
    transition: all 0.3s ease;
}
.ai-copilot-sticky-btn:hover {
    background: linear-gradient(135deg, #7e22ce, #c026d3);
    transform: translateY(-1px);
    box-shadow: 0 6px 16px rgba(147, 51, 234, 0.4);
}
</style>

<!-- Sticky Bar -->
<div id="global-sticky-bar" class="ai-copilot-sticky-bar" style="display: <?= $hasCopilot ? 'flex' : 'none' ?>;">
    
    <!-- Onboarding Tooltip -->
    <div id="copilot-sticky-tooltip" style="display: none; position: absolute; bottom: calc(100% + 18px); left: 50%; transform: translateX(-50%); background: #f3e8ff; color: #581c87; padding: 12px 18px; border-radius: 12px; font-size: 0.9rem; width: 340px; text-align: center; box-shadow: 0 10px 25px rgba(147, 51, 234, 0.15); border: 1px solid #d8b4fe; line-height: 1.5; pointer-events: none;">
        <div style="position: absolute; bottom: -6px; left: 50%; transform: translateX(-50%) rotate(45deg); width: 12px; height: 12px; background: #f3e8ff; border-right: 1px solid #d8b4fe; border-bottom: 1px solid #d8b4fe;"></div>
        <strong style="color: #9333ea; font-weight: 900;">NUEVO:</strong> Introduce el CIF de un prospecto y la IA redactará el argumento de ventas perfecto basado en su situación financiera actual.
    </div>

    <button onclick="document.getElementById('global-sticky-bar').style.display='none';" style="position: absolute; right: -10px; top: -10px; background: #e2e8f0; border: none; width: 24px; height: 24px; border-radius: 50%; font-size: 14px; font-weight: bold; color: #475569; cursor: pointer; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 5px rgba(0,0,0,0.1);">&times;</button>
    <div style="background: #f3e8ff; color: #9333ea; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; margin-left: 4px;">✨</div>
    <span style="font-weight: 700; color: #0f172a; font-size: 0.95rem; display: none; @media(min-width: 768px){display: block;}">Copiloto de Ventas</span>
    <input type="text" id="global-copilot-cif" class="ai-copilot-sticky-input" placeholder="Introduce un CIF válido..." autocomplete="off">
    <button class="ai-copilot-sticky-btn" onclick="openGlobalCopilot()">
        Generar Guion
    </button>
</div>

<!-- AI Copilot Modal -->
<div id="ai-copilot-modal" class="ai-copilot-modal" onclick="if(event.target === this) this.style.display='none';">
    <div class="ai-copilot-content">
        <button onclick="document.getElementById('ai-copilot-modal').style.display='none';" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #94a3b8;">&times;</button>
        <div class="ai-copilot-header">
            <div style="background: #f3e8ff; color: #9333ea; width: 48px; height: 48px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">✨</div>
            <div>
                <h3 class="ai-copilot-title">Copiloto de Ventas <span id="ai-copilot-cif-display" style="color: #3b82f6; font-size: 1rem; margin-left: 8px;"></span></h3>
                <p style="margin: 4px 0 0 0; color: #64748b; font-size: 0.9rem;">Guion ultra-personalizado con IA.</p>
            </div>
        </div>
        
        <div id="ai-copilot-form">
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px 16px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; color: #166534; line-height: 1.4;">
                <strong>Cómo funciona:</strong> Nuestra IA analiza en tiempo real el BORME, las subvenciones, licitaciones y datos del registro mercantil de esta empresa para generarte un guion de llamada y una secuencia de correos con la máxima probabilidad de conversión.
            </div>
            <label style="display: block; font-weight: 600; color: #334155; margin-bottom: 8px; font-size: 0.95rem;">¿Qué producto o servicio ofreces?</label>
            <input type="text" id="ai-copilot-product" class="ai-copilot-input" placeholder="Ej: Software ERP, Servicios SEO, Consultoría Legal..." autocomplete="off">
            
            <label style="display: block; font-weight: 600; color: #334155; margin-top: 16px; margin-bottom: 8px; font-size: 0.95rem;">¿A quién va dirigida la venta? (Rol objetivo)</label>
            <select id="ai-copilot-role" class="ai-copilot-input" style="appearance: auto; background-color: #fff;">
                <option value="CEO">CEO / Dirección General</option>
                <option value="CTO">CTO / Dirección IT</option>
                <option value="CFO">CFO / Dirección Financiera</option>
                <option value="CMO">CMO / Dirección de Marketing</option>
                <option value="COO">COO / Dirección de Operaciones</option>
                <option value="RRHH">RRHH / Recursos Humanos</option>
                <option value="Genérico">Genérico / Otro</option>
            </select>

            <button type="button" id="ai-copilot-btn" class="ai-copilot-btn-premium" onclick="generateAiScript()" style="margin-top: 20px;">
                ✨ Generar Guion de Ventas
            </button>
        </div>

        <div id="ai-copilot-loading" class="ai-loading-spinner" style="display: none; text-align: center; padding: 40px 0;">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="animation: spin 1s linear infinite; margin: 0 auto 12px auto; display: block;">
                <circle cx="12" cy="12" r="10" stroke-dasharray="30"></circle>
            </svg>
            Analizando Contratos, Subvenciones y BORME...
        </div>

        <div id="ai-copilot-result-container" class="ai-copilot-result"></div>
        
        <div id="ai-copilot-auth-wall" style="display: none; position: relative; margin-top: 0;">
            <!-- Fake blurred document -->
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 4px 20px rgba(0,0,0,0.04); position: relative; overflow: hidden; height: 370px; text-align: left;">
                
                <div style="display: flex; align-items: center; gap: 16px; border-bottom: 1px solid #f1f5f9; padding-bottom: 16px; margin-bottom: 16px;">
                    <div style="text-align: center; min-width: 60px; padding-right: 16px; border-right: 1px solid #e2e8f0;">
                        <div style="font-size: 2.2rem; font-weight: 800; color: #10b981; line-height: 1;">89</div>
                        <div style="font-size: 0.65rem; color: #64748b; font-weight: 800; text-transform: uppercase; margin-top: 4px; letter-spacing: 0.5px;">Score</div>
                    </div>
                    <div>
                        <h4 style="margin: 0 0 4px 0; color: #0f172a; font-size: 0.95rem;">💡 Análisis del CRO completado</h4>
                        <div style="height: 8px; width: 140px; background: #e2e8f0; border-radius: 4px;"></div>
                    </div>
                </div>
                
                <div style="height: 12px; width: 100%; background: #f1f5f9; border-radius: 4px; margin-bottom: 10px;"></div>
                <div style="height: 12px; width: 85%; background: #f1f5f9; border-radius: 4px; margin-bottom: 24px;"></div>
                
                <strong style="color: #0f172a; font-size: 0.95rem; display: block; margin-bottom: 12px;">📞 Guion de ventas hiper-personalizado:</strong>
                <p style="color: #64748b; font-size: 0.9rem; line-height: 1.6; margin: 0;">
                    "Hola [Nombre], te llamo porque he analizado vuestras últimas cuentas depositadas en el Registro Mercantil y he detectado que vuestra partida de...
                </p>
                
                <div style="height: 12px; width: 90%; background: #f1f5f9; border-radius: 4px; margin-top: 12px; margin-bottom: 10px;"></div>
                <div style="height: 12px; width: 75%; background: #f1f5f9; border-radius: 4px; margin-bottom: 10px;"></div>
                
                <!-- Gradient Blur Overlay -->
                <div style="position: absolute; bottom: 0; left: 0; right: 0; height: 280px; background: linear-gradient(to bottom, rgba(255,255,255,0), rgba(255,255,255,0.95) 45%, rgba(255,255,255,1)); backdrop-filter: blur(4px); z-index: 1;"></div>
            </div>
            
            <!-- Premium CTA Box (floating over the blur) -->
            <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -40%); width: 90%; max-width: 440px; text-align: center; z-index: 10; padding-bottom: 12px;">
                <div style="background: linear-gradient(135deg, #a855f7, #9333ea); border-radius: 50%; width: 56px; height: 56px; display: inline-flex; align-items: center; justify-content: center; box-shadow: 0 8px 16px rgba(147, 51, 234, 0.3); margin-bottom: 16px; border: 4px solid #fff;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <h4 style="margin: 0 0 8px 0; color: #0f172a; font-size: 1.25rem; font-weight: 800;">Guion bloqueado (Modo Invitado)</h4>
                <p style="font-size: 0.95rem; color: #475569; margin-bottom: 20px; font-weight: 500;">
                    Crea tu cuenta gratis en 5 segundos para leer este guion y desbloquear tus próximos análisis sin coste.
                </p>
                <a id="ai-copilot-register-btn" href="<?= site_url('register?redirect=') ?><?= urlencode(uri_string()) ?>" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; background: linear-gradient(135deg, #2563eb, #4f46e5); color: white; text-decoration: none; padding: 14px; border-radius: 12px; font-weight: 700; font-size: 1.05rem; box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.3); transition: transform 0.2s;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/><polyline points="10 17 15 12 10 7"/><line x1="15" y1="12" x2="3" y2="12"/></svg>
                    Crear Cuenta Gratis
                </a>
                <div style="margin-top: 24px; font-size: 0.85rem; color: #64748b; display: flex; align-items: center; justify-content: center; gap: 6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    No se requiere tarjeta de crédito
                </div>
            </div>
        </div>

        <div id="ai-copilot-guest-upsell" style="display: none; text-align: center; margin-top: 24px; padding: 24px; background: linear-gradient(to right, #f8fafc, #f1f5f9); border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <p style="font-size: 1.15rem; color: #0f172a; font-weight: 800; margin-bottom: 8px;">¿Cuánto tiempo te habría llevado investigar todo esto a mano?</p>
            <p style="font-size: 0.95rem; color: #475569; margin-bottom: 20px;">Automatiza tu <strong>Account Intelligence</strong>. Regístrate ahora y te regalamos 5 dossiers hiper-personalizados para tus próximas reuniones.</p>
            <a href="<?= site_url('copilot-pro') ?>" style="display: inline-block; background: #2563eb; color: white; text-decoration: none; padding: 12px 28px; border-radius: 8px; font-weight: 700; font-size: 1rem; box-shadow: 0 4px 12px rgba(37,99,235,0.3);">⚡ Desbloquear mis 5 Informes Gratis</a>
        </div>

        <div id="ai-copilot-upsell" style="display: none; text-align: center; margin-top: 24px; padding: 24px; background: linear-gradient(135deg, #f8fafc, #eff6ff); border: 1px solid #bfdbfe; border-radius: 12px; box-shadow: 0 4px 15px rgba(37,99,235,0.05);">
            <div style="font-size: 1.5rem; margin-bottom: 8px;">💎</div>
            <p style="font-size: 1.15rem; color: #1e3a8a; font-weight: 800; margin-bottom: 8px;">¿Quieres multiplicar tus cierres de ventas?</p>
            <p style="font-size: 0.95rem; color: #3b82f6; margin-bottom: 20px; font-weight: 500;">Sube al Plan Business o Copiloto de Ventas y genera Account Intelligence ilimitada para todas tus cuentas.</p>
            <a href="<?= site_url('copilot-pro') ?>" style="display: inline-block; background: linear-gradient(135deg, #2563eb, #4f46e5); color: white; text-decoration: none; padding: 12px 32px; border-radius: 8px; font-weight: 700; font-size: 1.05rem; box-shadow: 0 4px 12px rgba(37,99,235,0.3);">🚀 Ver Planes Pro</a>
        </div>
    </div>
</div>

<script>
window.currentCopilotCif = '';
const isUserLoggedIn = <?= session('logged_in') ? 'true' : 'false' ?>;

function openGlobalCopilot() {
    const inputCif = document.getElementById('global-copilot-cif');
    let cif = '';
    if(inputCif && inputCif.value.trim() !== '') {
        // Remove spaces, hyphens, etc and uppercase it
        cif = inputCif.value.trim().replace(/[^A-Z0-9]/ig, '').toUpperCase();
    }
    
    // Basic Spanish CIF/NIF/NIE validation (9 alphanumeric chars)
    const isValidCif = /^[A-Z0-9]{9}$/i.test(cif);
    
    if(!cif || !isValidCif) {
        if(inputCif) {
            const originalBorder = inputCif.style.border;
            const originalBg = inputCif.style.backgroundColor;
            
            inputCif.style.border = '2px solid #ef4444';
            inputCif.style.backgroundColor = '#fef2f2';
            inputCif.value = ''; // clear invalid input
            inputCif.placeholder = '⚠️ Introduce un CIF/NIF válido (9 caracteres)...';
            inputCif.focus();
            
            inputCif.addEventListener('input', function resetError() {
                this.style.border = originalBorder;
                this.style.backgroundColor = originalBg;
                this.placeholder = 'Introduce un CIF válido...';
                this.removeEventListener('input', resetError);
            });
        }
        return;
    }
    openCopilotModal(cif);
}

function openCopilotModal(cif) {
    window.currentCopilotCif = cif;
    document.getElementById('ai-copilot-cif-display').textContent = '[' + cif + ']';
    
    // Reset state
    document.getElementById('ai-copilot-form').style.display = 'block';
    document.getElementById('ai-copilot-loading').style.display = 'none';
    document.getElementById('ai-copilot-result-container').style.display = 'none';
    document.getElementById('ai-copilot-upsell').style.display = 'none';
    document.getElementById('ai-copilot-guest-upsell').style.display = 'none';
    document.getElementById('ai-copilot-auth-wall').style.display = 'none';
    
    document.getElementById('ai-copilot-modal').style.display = 'flex';
}

function generateAiScript(modifier = null) {
    const product = document.getElementById('ai-copilot-product').value.trim();
    if(!product) {
        alert('Por favor, indica qué producto ofreces.');
        return;
    }

    if(!window.currentCopilotCif) {
        alert('No hay un CIF seleccionado.');
        return;
    }

    if (modifier) {
        // Show partial loading state
        let modifierLoading = document.getElementById('ai-modifier-loading');
        if (modifierLoading) modifierLoading.style.display = 'flex';
        
        let resultContainer = document.getElementById('ai-copilot-result-container');
        if (resultContainer) resultContainer.style.opacity = '0.5';
    } else {
        document.getElementById('ai-copilot-form').style.display = 'none';
        document.getElementById('ai-copilot-loading').style.display = 'block';
        document.getElementById('ai-copilot-result-container').style.display = 'none';
        document.getElementById('ai-copilot-upsell').style.display = 'none';
        document.getElementById('ai-copilot-guest-upsell').style.display = 'none';
        document.getElementById('ai-copilot-auth-wall').style.display = 'none';
    }

    let payload = 'cif=' + encodeURIComponent(window.currentCopilotCif) + '&product=' + encodeURIComponent(product);
    
    const targetRole = document.getElementById('ai-copilot-role') ? document.getElementById('ai-copilot-role').value : 'CEO';
    payload += '&target_role=' + encodeURIComponent(targetRole);
    
    if (modifier) {
        payload += '&modifier=' + encodeURIComponent(modifier);
    }

    fetch('<?= site_url('api/ai/copilot/generate') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: payload
    })
    .then(res => res.json())
    .then(data => {
        document.getElementById('ai-copilot-loading').style.display = 'none';
        
        let resultContainer = document.getElementById('ai-copilot-result-container');
        resultContainer.style.opacity = '1';
        
        if(data.status === 'success') {
            resultContainer.style.display = 'block';
            let d = data.dossier;
            let html = `
                <div style="margin-bottom: 16px; display: flex; gap: 8px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">
                    <button onclick="document.getElementById('ai-tab-1').style.display='block'; document.getElementById('ai-tab-2').style.display='none'; this.style.color='#2563eb'; this.style.borderBottom='2px solid #2563eb'; this.nextElementSibling.style.color='#64748b'; this.nextElementSibling.style.borderBottom='none';" style="background: none; border: none; font-size: 0.95rem; font-weight: 700; color: #2563eb; cursor: pointer; padding: 4px 12px; border-bottom: 2px solid #2563eb;">🎯 Inteligencia & Llamada</button>
                    <button onclick="document.getElementById('ai-tab-2').style.display='block'; document.getElementById('ai-tab-1').style.display='none'; this.style.color='#2563eb'; this.style.borderBottom='2px solid #2563eb'; this.previousElementSibling.style.color='#64748b'; this.previousElementSibling.style.borderBottom='none';" style="background: none; border: none; font-size: 0.95rem; font-weight: 700; color: #64748b; cursor: pointer; padding: 4px 12px; border-bottom: none;">✉️ Secuencia Email</button>
                </div>

                <!-- TAB 1: Inteligencia -->
                <div id="ai-tab-1">
                    <div style="background: #fff; border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px; margin-bottom: 16px; display: flex; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                        <div style="text-align: center; min-width: 80px; border-right: 1px solid #e2e8f0; padding-right: 16px;">
                            <div style="font-size: 2.5rem; font-weight: 800; color: ${(d.score !== null && d.score !== undefined && d.score >= 80) ? '#10b981' : ((d.score !== null && d.score !== undefined && d.score >= 50) ? '#f59e0b' : '#ef4444')}; line-height: 1; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">${(d.score !== null && d.score !== undefined && d.score !== '') ? d.score : '-'}</div>
                            <div style="font-size: 0.7rem; color: #64748b; font-weight: 800; text-transform: uppercase; margin-top: 6px; letter-spacing: 0.5px;">Score</div>
                        </div>
                        <div style="flex-grow: 1;">
                            <h4 style="margin: 0 0 6px 0; color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; gap: 6px;">💡 Veredicto Comercial</h4>
                            <div style="color: #475569; font-size: 0.9rem; line-height: 1.5; font-style: italic;">
                                "${d.score_insight || 'Evaluando potencial de la cuenta...'}"
                            </div>
                        </div>
                    </div>

                    ${d.v2_evidence ? `
                    <div style="background: #f8fafc; border: 1px solid #cbd5e1; padding: 14px 16px; border-radius: 10px; margin-bottom: 16px;">
                        <div style="font-size: 0.75rem; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px; display: flex; justify-content: space-between; align-items: center;">
                            <span>🔍 Desglose de Inteligencia V2</span>
                            <span style="background: #e2e8f0; color: #334155; padding: 2px 8px; border-radius: 12px; font-size: 0.7rem; font-weight: bold;">Evidencia Verificada</span>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 12px;">
                            <div style="background: #fff; border: 1px solid #e2e8f0; padding: 10px 12px; border-radius: 8px;">
                                <div style="font-size: 0.7rem; color: #64748b; font-weight: bold; text-transform: uppercase;">Fiabilidad Evidencia</div>
                                <div style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-top: 2px;">
                                    ${(d.v2_evidence.confidence_score * 100).toFixed(1)}%
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">Certidumbre técnica</div>
                            </div>
                            <div style="background: #fff; border: 1px solid #e2e8f0; padding: 10px 12px; border-radius: 8px;">
                                <div style="font-size: 0.7rem; color: #64748b; font-weight: bold; text-transform: uppercase;">Encaje Sectorial (CNAE)</div>
                                <div style="font-size: 0.85rem; font-weight: 700; color: #2563eb; margin-top: 2px;">
                                    ${d.v2_evidence.tax_match_level === 'class' ? '🎯 Clase CNAE (Exacto 100%)' :
                                      (d.v2_evidence.tax_match_level === 'group' ? '📈 Grupo CNAE (Alto 90%)' :
                                      (d.v2_evidence.tax_match_level === 'division' ? '🏢 División CNAE (80%)' :
                                      (d.v2_evidence.tax_match_level === 'section' ? '🌐 Sección CNAE (60%)' :
                                      (d.v2_evidence.tax_match_level === 'label_match' ? '🏷️ Coincidencia Comercial' : '🚫 Sin Coincidencia Directa'))))}
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px; text-overflow: ellipsis; overflow: hidden; white-space: nowrap;">
                                    CNAE ${d.v2_evidence.cnae_code || ''} ${d.v2_evidence.cnae_label || ''}
                                </div>
                            </div>
                            <div style="background: #fff; border: 1px solid #e2e8f0; padding: 10px 12px; border-radius: 8px;">
                                <div style="font-size: 0.7rem; color: #64748b; font-weight: bold; text-transform: uppercase;">Impulso / Timing</div>
                                <div style="font-size: 1.1rem; font-weight: 800; color: ${d.v2_evidence.trigger_score > 0 ? '#10b981' : '#64748b'}; margin-top: 2px;">
                                    ${d.v2_evidence.trigger_score} / 100
                                </div>
                                <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                    ${d.v2_evidence.trigger_score > 0 ? 'Señales recientes' : 'Sin señal reciente'}
                                </div>
                            </div>
                        </div>
                    </div>
                    ` : ''}

                    ${data.admins ? `
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 8px; margin-bottom: 16px; font-size: 0.85rem; color: #334155; display: flex; align-items: center; gap: 8px;">
                        <span style="font-size: 1.1rem;">👥</span> <strong>Directivos identificados:</strong> ${data.admins}
                    </div>
                    ` : ''}
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div>
                            <h4 style="margin: 0 0 8px 0; color: #0f172a; font-size: 0.95rem; display: flex; justify-content: space-between; align-items: center;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <span style="background: #ef4444; color: white; padding: 2px 6px; border-radius: 12px; font-size: 0.65rem; font-weight: bold;">TRIGGER</span>
                                    Por qué llamar hoy
                                </div>
                                ${data.phone ? `<a href="tel:${data.phone.replace(/\s+/g, '')}" style="background: #2563eb; color: white; padding: 2px 10px; border-radius: 12px; font-size: 0.75rem; text-decoration: none; font-weight: bold; box-shadow: 0 2px 4px rgba(37,99,235,0.2);">Llamar: ${data.phone}</a>` : ''}
                            </h4>
                            <div style="background: #fef2f2; border: 1px solid #fca5a5; padding: 12px; border-radius: 8px; color: #991b1b; font-size: 0.85rem; margin-bottom: 12px; line-height: 1.4;">
                                ${d.trigger_event}
                            </div>
                            <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; border-radius: 8px; color: #334155; font-size: 0.85rem; line-height: 1.4;">
                                <strong>Pain Points:</strong> ${d.pain_points}
                            </div>
                        </div>
                        
                        <div>
                            <h4 style="margin: 0 0 8px 0; color: #0f172a; font-size: 0.95rem; display: flex; justify-content: space-between; align-items: center;">
                                <span>📞 Guion Cold Call</span>
                                ${data.phone ? `<a href="tel:${data.phone.replace(/\s+/g, '')}" style="background: #2563eb; color: white; padding: 2px 10px; border-radius: 12px; font-size: 0.75rem; text-decoration: none; font-weight: bold; box-shadow: 0 2px 4px rgba(37,99,235,0.2);">Llamar: ${data.phone}</a>` : ''}
                            </h4>
                            <div style="background: #f1f5f9; padding: 12px; border-radius: 8px; font-style: italic; color: #1e293b; font-size: 0.85rem; line-height: 1.4; margin-bottom: 12px;">
                                "${d.cold_call}"
                            </div>
                            
                            <h4 style="margin: 0 0 8px 0; color: #0f172a; font-size: 0.95rem;">💼 LinkedIn Icebreaker</h4>
                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 12px; border-radius: 8px; color: #166534; font-size: 0.85rem; line-height: 1.4;">
                                ${d.linkedin}
                            </div>
                        </div>
                    </div>

                    ${d.objections && Array.isArray(d.objections) ? `
                    <div style="margin-top: 16px; border-top: 1px dashed #cbd5e1; padding-top: 16px;">
                        <h4 style="margin: 0 0 12px 0; color: #0f172a; font-size: 0.95rem;">🛡️ Anticipación de Objeciones</h4>
                        <div style="display: flex; flex-direction: column; gap: 8px;">
                            ${d.objections.map(obj => `
                                <div style="display: flex; flex-direction: column; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                                    <div style="background: #fef2f2; padding: 8px 12px; font-size: 0.85rem; color: #991b1b; border-bottom: 1px solid #fee2e2;"><strong>Cliente:</strong> "${obj.excuse}"</div>
                                    <div style="background: #f0fdf4; padding: 8px 12px; font-size: 0.85rem; color: #166534;"><strong>Respuesta:</strong> ${obj.rebuttal}</div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    ` : ''}
                </div>

                <!-- TAB 2: Emails -->
                <div id="ai-tab-2" style="display: none;">
                    <h4 style="margin: 0 0 12px 0; color: #0f172a; font-size: 0.95rem;">✉️ Secuencia Email de Prospección</h4>
                    
                    ${d.email_subjects && Array.isArray(d.email_subjects) ? `
                    <div style="background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px 16px; border-radius: 8px; margin-bottom: 16px;">
                        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 8px; text-transform: uppercase; font-weight: bold;">🎯 Asuntos Sugeridos (A/B Testing)</div>
                        <ul style="margin: 0; padding-left: 20px; color: #334155; font-size: 0.85rem; line-height: 1.5;">
                            ${d.email_subjects.map(sub => `<li><strong>${sub}</strong></li>`).join('')}
                        </ul>
                    </div>
                    ` : ''}

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div style="background: #fff; border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px;">
                            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 6px; text-transform: uppercase; font-weight: bold;">Email 1</div>
                            <div style="color: #334155; white-space: pre-wrap; font-size: 0.85rem; line-height: 1.4;">${d.email_1}</div>
                        </div>
                        <div style="background: #fff; border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px;">
                            <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 6px; text-transform: uppercase; font-weight: bold;">Email 2 (Follow-up)</div>
                            <div style="color: #334155; white-space: pre-wrap; font-size: 0.85rem; line-height: 1.4;">${d.email_2}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Quick Modifiers (Mando a distancia) -->
                <div style="margin-top: 16px; margin-bottom: 8px; background: #f8fafc; padding: 12px; border-radius: 8px; border: 1px dashed #cbd5e1; position: relative;">
                    <div id="ai-modifier-loading" style="display: none; position: absolute; inset: 0; background: rgba(255,255,255,0.8); backdrop-filter: blur(2px); border-radius: 8px; z-index: 10; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: bold; color: #2563eb;">
                        <span class="spinner" style="margin-right: 8px;"></span> Modificando guion...
                    </div>
                    <div style="font-size: 0.75rem; font-weight: bold; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Ajustar tono y enfoque:</div>
                    <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                        <button onclick="generateAiScript('agresivo')" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 100px; padding: 6px 12px; font-size: 0.8rem; cursor: pointer; color: #334155; font-weight: 500; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">⚡ Hacer agresivo</button>
                        <button onclick="generateAiScript('corto')" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 100px; padding: 6px 12px; font-size: 0.8rem; cursor: pointer; color: #334155; font-weight: 500; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">✂️ Hacer más corto</button>
                        <button onclick="generateAiScript('precio')" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 100px; padding: 6px 12px; font-size: 0.8rem; cursor: pointer; color: #334155; font-weight: 500; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">💰 Enfocar en Precio</button>
                        <button onclick="generateAiScript('suave')" style="background: #fff; border: 1px solid #e2e8f0; border-radius: 100px; padding: 6px 12px; font-size: 0.8rem; cursor: pointer; color: #334155; font-weight: 500; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#fff'">🤝 Tono suave/consultivo</button>
                    </div>
                </div>

                
                ${isUserLoggedIn ? `
                <div style="margin-top: 24px; padding-top: 20px; border-top: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
                    <div id="ai-copilot-feedback-container-${data.log_id}" style="display: flex; align-items: center; gap: 12px; font-size: 0.9rem; color: #475569;">
                        <span>¿Te ha resultado útil?</span>
                        <button onclick="submitCopilotFeedback(${data.log_id}, 1)" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 6px 12px; border-radius: 6px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#f0fdf4'">👍 Sí</button>
                        <button onclick="submitCopilotFeedback(${data.log_id}, -1)" style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 6px 12px; border-radius: 6px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#fee2e2'" onmouseout="this.style.background='#fef2f2'">👎 No</button>
                    </div>

                    <button id="ai-copilot-email-btn" onclick="sendCopilotEmail()" style="background: #ffffff; border: 1px solid #cbd5e1; color: #334155; padding: 10px 18px; border-radius: 8px; font-weight: bold; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-size: 0.9rem; transition: all 0.2s; box-shadow: 0 1px 2px rgba(0,0,0,0.05);">
                        <span id="ai-copilot-email-icon">✉️</span> 
                        <span id="ai-copilot-email-text">Enviarme resumen por email</span>
                    </button>
                </div>
                ` : ''}
            `;
            document.getElementById('ai-copilot-result-container').innerHTML = html;
            
            if (!isUserLoggedIn) {
                document.getElementById('ai-copilot-guest-upsell').style.display = 'block';
            } else if(data.trials_left !== 'ilimitado' && data.trials_left <= 0) {
                document.getElementById('ai-copilot-upsell').style.display = 'block';
            } else {
                document.getElementById('ai-copilot-form').style.display = 'block';
            }
        } else {
            if(data.message === 'guest_limit_reached') {
                document.getElementById('ai-copilot-auth-wall').style.display = 'block';
                let registerBtn = document.getElementById('ai-copilot-register-btn');
                if(registerBtn) {
                    registerBtn.href = "<?= site_url('register?redirect=') ?><?= urlencode(uri_string()) ?>&intent=copiloto";
                }
            } else if(data.message === 'premium_required') {
                document.getElementById('ai-copilot-form').style.display = 'none';
                document.getElementById('ai-copilot-result-container').innerHTML = `
                    <div style="text-align: center; padding: 60px 20px;">
                        <div style="font-size: 3rem; margin-bottom: 16px;">🎯</div>
                        <h3 style="color: #0f172a; margin-bottom: 12px; font-size: 1.5rem;">Límite de prospección alcanzado</h3>
                        <p style="color: #475569; margin-bottom: 40px; font-size: 1rem; max-width: 480px; margin-left: auto; margin-right: auto; line-height: 1.6;">
                            Multiplica tu tasa de respuesta y escala tu <strong>Account-Based Selling</strong> con la suscripción Business o Copiloto de Ventas. Cierra una sola reunión gracias a este Copiloto y habrás recuperado la inversión de todo el año.
                        </p>
                        <a href="<?= site_url('copilot-pro') ?>" style="display: inline-block; background: #2563eb; color: white; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: bold; font-size: 1rem; box-shadow: 0 4px 6px rgba(37,99,235,0.2);">🚀 Activar Copiloto</a>
                    </div>
                `;
                document.getElementById('ai-copilot-result-container').style.display = 'block';
            } else {
                document.getElementById('ai-copilot-form').style.display = 'block';
                alert('Error: ' + data.message);
            }
        }
    })
    .catch(err => {
        document.getElementById('ai-copilot-loading').style.display = 'none';
        document.getElementById('ai-copilot-form').style.display = 'block';
        alert('Ocurrió un error al contactar con la IA.');
    });
}

function sendCopilotEmail() {
    if(!window.currentCopilotCif) return;
    
    const btn = document.getElementById('ai-copilot-email-btn');
    const textSpan = document.getElementById('ai-copilot-email-text');
    const iconSpan = document.getElementById('ai-copilot-email-icon');
    
    // UI Loading state
    btn.disabled = true;
    btn.style.opacity = '0.7';
    textSpan.innerText = 'Enviando...';
    iconSpan.innerText = '⏳';

    fetch('<?= site_url('api/ai/copilot/email') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'cif=' + encodeURIComponent(window.currentCopilotCif)
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            textSpan.innerText = 'Email enviado';
            iconSpan.innerText = '✅';
            btn.style.borderColor = '#10b981';
            btn.style.color = '#10b981';
        } else {
            alert('Error: ' + data.message);
            // Reset UI
            btn.disabled = false;
            btn.style.opacity = '1';
            textSpan.innerText = 'Enviarme resumen por email';
            iconSpan.innerText = '✉️';
        }
    })
    .catch(err => {
        alert('Error al enviar el email.');
        // Reset UI
        btn.disabled = false;
        btn.style.opacity = '1';
        textSpan.innerText = 'Enviarme resumen por email';
        iconSpan.innerText = '✉️';
    });
}

function submitCopilotFeedback(logId, score) {
    const container = document.getElementById('ai-copilot-feedback-container-' + logId);
    if (!container) return;
    
    container.innerHTML = '<span style="color: #64748b;">Enviando...</span>';

    fetch('<?= site_url('api/ai/copilot/feedback') ?>', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: 'log_id=' + logId + '&feedback_score=' + score
    })
    .then(res => res.json())
    .then(data => {
        if(data.status === 'success') {
            container.innerHTML = '<span style="color: #10b981; font-weight: bold;">¡Gracias por tu feedback! 🤝</span>';
        } else {
            container.innerHTML = '<span style="color: #ef4444;">Error al guardar.</span>';
        }
    })
    .catch(err => {
        container.innerHTML = '<span style="color: #ef4444;">Error de conexión.</span>';
    });
}

function toggleGlobalCopilotSticky() {
    const sticky = document.getElementById('global-sticky-bar');
    if (sticky) {
        if (sticky.style.display === 'none' || sticky.style.display === '') {
            sticky.style.display = 'flex';
            
            // Track Copilot header click
            fetch('<?= site_url('api/user/log-event') ?>', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                body: 'event_type=copilot_header_click'
            }).catch(e => console.error(e));

            const tooltip = document.getElementById('copilot-sticky-tooltip');
            if (tooltip && localStorage.getItem('copilot_tooltip_seen') !== 'true') {
                tooltip.style.display = 'block'; // Show tooltip if not seen before
            }
            const cifInput = document.getElementById('global-copilot-cif');
            if (cifInput) {
                setTimeout(() => cifInput.focus(), 50);
            }
        } else {
            sticky.style.display = 'none';
        }
    }
}

// Ocultar tooltip al escribir
document.addEventListener('DOMContentLoaded', () => {
    const cifInput = document.getElementById('global-copilot-cif');
    if (cifInput) {
        cifInput.addEventListener('input', () => {
            const tooltip = document.getElementById('copilot-sticky-tooltip');
            if (tooltip && tooltip.style.display !== 'none') {
                tooltip.style.display = 'none';
                localStorage.setItem('copilot_tooltip_seen', 'true'); // Save that user has interacted
            }
        });
    }
});
</script>
