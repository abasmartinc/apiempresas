<?php
$isBonusUser = (($walletBalance ?? 0) > 0);
if ($isBonusUser && !($isPaid ?? false)) {
    $isPaid = true; // Tratar como usuario de pago para quitar bloqueos
    $planNameRaw = 'Bono API Prepago';
    $currentPlanSlug = 'custom_bonus';
    $requestsUsed = 0; // Quitar limitación del buscador
}
?>
<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/dashboard.css?v=' . (file_exists(FCPATH . 'public/css/dashboard.css') ? filemtime(FCPATH . 'public/css/dashboard.css') : time())) ?>" />
    <style>
        .trigger-box { border-left: 6px solid #2152ff; background: #f8faff; padding: 24px; border-radius: 16px; margin-bottom: 32px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #eef2ff; border-left-width: 6px; }
        .trigger-box h4 { margin: 0 0 8px; color: #1e3a8a; font-weight: 800; font-size: 1.15rem; }
        .trigger-box p { margin: 0 0 16px; color: #475569; font-weight: 600; font-size: 0.95rem; }
        .trigger-cta { display: inline-block; background: #2152ff; color: white !important; text-decoration: none !important; padding: 12px 24px; border-radius: 10px; font-weight: 800; font-size: 0.9rem; transition: all 0.2s; }
        .trigger-cta:hover { transform: translateY(-2px); box-shadow: 0 8px 15px -3px rgba(33, 82, 255, 0.3); }
        .trigger-box--warning { border-left-color: #f59e0b; background: #fffbeb; }
        .trigger-box--warning h4 { color: #92400e; }
        .trigger-box--warning .trigger-cta { background: #f59e0b; }
        
        .activation-main-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 32px; margin-bottom: 32px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02); }
        .activation-header h2 { font-size: 1.75rem; font-weight: 900; color: #0f172a; margin-bottom: 8px; }
        .activation-header p { color: #64748b; font-weight: 600; margin-bottom: 24px; }
        
        .search-form-dash { display: flex; gap: 12px; }
        .search-input-dash { flex: 1; height: 60px; padding: 0 24px; border-radius: 14px; border: 1px solid #e2e8f0; font-size: 1rem; font-weight: 600; outline: none; transition: all 0.2s; }
        .search-input-dash:focus { border-color: #2152ff; box-shadow: 0 0 0 4px rgba(33, 82, 255, 0.1); }
        .btn-validate-dash { height: 60px; padding: 0 32px; border-radius: 14px; background: #2152ff; color: white; font-weight: 800; border: none; cursor: pointer; transition: all 0.2s; }
        .btn-validate-dash:hover { transform: translateY(-2px); box-shadow: 0 10px 15px -3px rgba(33, 82, 255, 0.3); }
        .btn-validate-dash:disabled { background: #94a3b8; cursor: not-allowed; transform: none !important; box-shadow: none !important; }

        .aha-moment-card { display: none; background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 20px; padding: 24px; margin-top: 24px; animation: slideDown 0.4s ease-out; }
        @keyframes slideDown { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }
        .aha-header { display: flex; align-items: center; gap: 10px; color: #0369a1; font-weight: 800; margin-bottom: 16px; }
        .aha-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
        .aha-item { background: white; padding: 12px 16px; border-radius: 12px; border: 1px solid rgba(186, 230, 253, 0.5); }
        .aha-label { font-size: 0.7rem; color: #64748b; text-transform: uppercase; font-weight: 800; margin-bottom: 4px; }
        .aha-val { font-size: 0.95rem; font-weight: 700; color: #0f172a; }

        .step-guide { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin: 32px 0; }
        .step-card { background: white; padding: 24px; border-radius: 20px; border: 1px solid #e2e8f0; }
        .step-num { width: 32px; height: 32px; background: #2152ff; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 900; margin-bottom: 16px; font-size: 0.9rem; }
        .step-card h4 { font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 8px; }
        .step-card p { font-size: 0.85rem; color: #64748b; font-weight: 600; line-height: 1.5; }

        .usage-alert-strip { background: #fff7ed; border: 1px solid #ffedd5; padding: 12px 20px; border-radius: 12px; margin-bottom: 32px; display: flex; align-items: center; gap: 12px; color: #9a3412; font-weight: 700; font-size: 0.9rem; }
        
        .progress-container { margin-top: 16px; margin-bottom: 8px; }
        .progress-bar-bg { width: 100%; height: 10px; background: #e2e8f0; border-radius: 999px; overflow: hidden; position: relative; }
        .progress-bar-fill { 
            height: 100%; 
            background: linear-gradient(90deg, #2152ff, #12b48a); 
            border-radius: 999px; 
            box-shadow: 0 2px 4px rgba(33, 82, 255, 0.2);
            animation: growProgress 1.5s cubic-bezier(0.4, 0, 0.2, 1) forwards;
            width: 0; /* Start at 0 for animation */
        }
        @keyframes growProgress {
            from { width: 0; }
            to { width: var(--target-width); }
        }
        .progress-text { font-size: 0.85rem; font-weight: 700; color: #64748b; margin-top: 8px; display: block; }
        
        .test-api-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 24px; margin-top: 24px; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .test-api-title { font-size: 1.1rem; font-weight: 900; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 10px; }
        .test-api-desc { font-size: 0.9rem; color: #64748b; margin-bottom: 20px; line-height: 1.5; font-weight: 600; }
        .test-api-result { display: none; margin-top: 20px; background: #f8fafc; border-radius: 12px; padding: 16px; border: 1px solid #e2e8f0; }
        .test-api-json { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; background: #1e293b; color: #f8fafc; padding: 16px; border-radius: 8px; overflow-x: auto; max-height: 300px; white-space: pre-wrap; margin: 12px 0; }
        .test-api-success-tag { color: #10b981; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; margin-bottom: 8px; }
        .api-loading { display: flex; align-items: center; gap: 8px; }
        .api-loading::after { content: ""; width: 14px; height: 14px; border: 2px solid #ffffff; border-top-color: transparent; border-radius: 50%; animation: api-spin 0.8s linear infinite; }
        @keyframes api-spin { to { transform: rotate(360deg); } }
        
        /* KPI Cards Pro */
        .kpi-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px; }
        @media (max-width: 1024px) { .kpi-grid-4 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .kpi-grid-4 { grid-template-columns: 1fr; } }
        
        .waiting-pulse {
        animation: pulse-text 2s infinite ease-in-out;
        display: inline-block;
    }

    @keyframes pulse-text {
        0% { opacity: 0.5; }
        50% { opacity: 1; }
        100% { opacity: 0.5; }
    }

    .kpi-card-pro { 
            background: #ffffff; 
            border: 1px solid #e2e8f0; 
            padding: 24px; 
            border-radius: 20px; 
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02), 0 4px 6px -4px rgba(0,0,0,0.02); 
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); 
            display: flex;
            align-items: flex-start;
            gap: 16px;
            position: relative;
            overflow: hidden;
        }
        .kpi-card-pro:hover { 
            transform: translateY(-4px); 
            border-color: #2152ff; 
            box-shadow: 0 20px 25px -5px rgba(33, 82, 255, 0.1), 0 8px 10px -6px rgba(33, 82, 255, 0.05); 
        }
        .kpi-icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            background: #f0f4ff;
            color: #2152ff;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.3s;
        }
        .kpi-card-pro:hover .kpi-icon-box {
            background: #2152ff;
            color: white;
            transform: scale(1.1) rotate(-5deg);
        }
        .kpi-content { flex: 1; }
        .kpi-card-pro .label { 
            font-size: 0.7rem; 
            font-weight: 800; 
            color: #64748b; 
            text-transform: uppercase; 
            letter-spacing: 0.05em;
            margin-bottom: 4px; 
            display: block; 
        }
        .kpi-card-pro .value { 
            font-size: 1.75rem; 
            font-weight: 950; 
            color: #0f172a; 
            line-height: 1.1; 
            display: flex;
            align-items: baseline;
            gap: 4px;
        }
        .kpi-card-pro .value-unit { font-size: 0.85rem; color: #94a3b8; font-weight: 700; }
        .kpi-card-pro .meta { 
            font-size: 0.75rem; 
            color: #94a3b8; 
            margin-top: 6px; 
            font-weight: 600; 
            display: flex;
            align-items: center;
            gap: 4px;
        }
        
        .kpi-status-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; position: relative; }
        .kpi-status-dot.active { background: #10b981; animation: pulse-green 2s infinite; }
        @keyframes pulse-green { 
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); } 
            70% { box-shadow: 0 0 0 8px rgba(16, 185, 129, 0); } 
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); } 
        }

        /* Dynamic States */
        .kpi-card--warning { border-color: #f59e0b !important; background: #fffbeb !important; }
        .kpi-card--warning .kpi-icon-box { background: #fef3c7; color: #d97706; }
        
        .kpi-card--cta { background: #10b981 !important; border-color: #059669 !important; color: white !important; cursor: pointer; transform: scale(1.02); box-shadow: 0 20px 25px -5px rgba(16, 185, 129, 0.2); }
        .kpi-card--cta .label, .kpi-card--cta .value, .kpi-card--cta .meta, .kpi-card--cta .value-unit { color: white !important; }
        .kpi-card--cta .kpi-icon-box { background: rgba(255,255,255,0.2); color: white; }
        .kpi-card--cta:hover { transform: scale(1.05); }

    </style>
</head>

<body>

<div class="auth-wrapper">
    <?=view('partials/header_inner') ?>

    <main class="dash-main">
        <div class="container">
            <?= view('partials/usage_trigger_banner') ?>
            <?= view('components/onboarding_modal') ?>

            <?php 
                $userName = 'Cliente';
                if (is_object($user)) $userName = $user->name ?? 'Cliente';
                elseif (is_array($user)) $userName = $user['name'] ?? 'Cliente';

                $get = function($src, $key, $default = null) {
                    if (is_array($src)) return $src[$key] ?? $default;
                    if (is_object($src)) return $src->$key ?? $default;
                    return $default;
                };
                $planNameRaw = $get($plan, 'plan_name', 'Free');
                $currentPlanSlug = strtolower(trim($planNameRaw));
                $isPaid = ($currentPlanSlug !== 'free' && !empty($currentPlanSlug));

                $planClass = '';
                if (strpos($currentPlanSlug, 'business') !== false) $planClass = 'plan-card--business';
                elseif (strpos($currentPlanSlug, 'pro') !== false) $planClass = 'plan-card--pro';

                $reqCount = 0;
                $hasRadar = false;
                if (isset($user)) {
                    if (is_array($user)) {
                        $reqCount = $user['requests_count'] ?? 0;
                        $hasRadar = $user['has_radar'] ?? false;
                    } else if (is_object($user)) {
                        $reqCount = $user->requests_count ?? 0;
                        $hasRadar = (method_exists($user, 'hasRadar')) ? $user->hasRadar() : ($user->has_radar ?? false);
                    }
                }

                $usagePercent = 0;
                if (isset($plan) && $get($plan, 'monthly_quota', 0) > 0) {
                    $usagePercent = round(($reqCount / $get($plan, 'monthly_quota', 0)) * 100);
                }
            ?>

            <div class="dash-header">
                <h1>Bienvenido, <?= htmlspecialchars($userName) ?></h1>
                
                <?php if (!empty($usageMessage)): ?>
                    <div class="usage-alert-card" style="background: #f0f9ff; border: 1px solid #bae6fd; border-radius: 16px; padding: 20px; margin-bottom: 24px; display: flex; align-items: flex-start; gap: 16px;">
                        <div style="background: #2152ff; color: white; width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        </div>
                        <div>
                            <h3 style="margin: 0 0 4px; font-size: 1.1rem; font-weight: 800; color: #0c4a6e;"><?= esc($usageMessage['title']) ?></h3>
                            <p style="margin: 0; font-size: 0.95rem; color: #0369a1; font-weight: 600;"><?= esc($usageMessage['text']) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($answeredTickets)): ?>
                    <?php foreach($answeredTickets as $t): ?>
                          <div class="usage-alert-card" style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 16px; margin: 0 0 24px 0; display: flex; align-items: flex-start; gap: 16px;">
                              <div style="background: #10b981; color: white; width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                  <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                              </div>
                              <div style="flex: 1;">
                                <h3 style="margin: 0 0 4px; font-size: 1rem; font-weight: 800; color: #065f46;">Respuesta de Soporte al Ticket #<?= $t['id'] ?></h3>
                                <p style="margin: 0 0 8px; font-size: 0.9rem; color: #047857; font-weight: 600;">El equipo de soporte ha respondido a tu ticket: <strong><?= esc($t['subject']) ?></strong></p>
                                <a href="<?= site_url('tickets/'.$t['id']) ?>" style="display: inline-block; background: #10b981; color: white; padding: 6px 12px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; text-decoration: none; transition: all 0.2s;">Ver respuesta</a>
                              </div>
                          </div>
                      <?php endforeach; ?>
                  <?php endif; ?>
            </div>
            <?= view('components/dash_kpis', get_defined_vars()) ?>

            <div class="dash-grid">
                <div>
                    <?= view('components/dash_search_card', get_defined_vars()) ?>
                    <?= view('components/dash_api_key', get_defined_vars()) ?>
                    <?= view('components/dash_integration', get_defined_vars()) ?>
                </div>

                <aside>
                    <?= view('components/dash_sidebar', get_defined_vars()) ?>
                </aside>
            </div>


            <!-- HELP SECTION -->
            <?= view('components/help_section') ?>
        </div>
    </main>

    <?=view('partials/footer') ?>
</div>

<script>
    (function(){
        const box = document.getElementById('apiKeyBox');
        if(!box) return;
        const realKey = box.getAttribute('data-api-key') || '';
        const masked = '•'.repeat(Math.max(realKey.length - 8, 12));
        const valueEl = document.getElementById('apiKeyMasked');
        const btnToggle = document.getElementById('btnToggleKey');
        const btnCopy = document.getElementById('btnCopyKey');
        let visible = false;
        valueEl.textContent = masked;
        btnToggle.addEventListener('click', () => {
            visible = !visible;
            valueEl.textContent = visible ? realKey : masked;
            btnToggle.textContent = visible ? 'Ocultar' : 'Mostrar';
        });
        btnCopy.addEventListener('click', async () => {
            try{
                await navigator.clipboard.writeText(realKey);
                btnCopy.textContent = 'Copiado ✓';
                
                // Tracking: API Key Copied
                if (window.trackEvent) {
                    window.trackEvent('api_key_copied', {
                        source: 'dashboard',
                        page_type: 'dashboard'
                    });
                }
                
                setTimeout(() => btnCopy.textContent = 'Copiar', 1800);
            }catch(e){ alert('Error al copiar.'); }
        });
    })();

    // === NEW: Dashboard Validation Logic ===
    (function(){
        const btnValidate = document.getElementById('btnDashValidate');
        const inputQ = document.getElementById('dash_q');
        const ahaCard = document.getElementById('aha-moment-card');
        const realKey = document.getElementById('apiKeyBox')?.getAttribute('data-api-key') || '';

        if(!btnValidate) return;

        async function validateCompany() {
            const q = inputQ.value.trim();
            if(!q) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Campo vacío',
                    text: 'Por favor, introduce un CIF para buscar.',
                    confirmButtonColor: '#2152ff',
                    confirmButtonText: 'Entendido'
                });
                return;
            }

            if (window.trackEvent) trackEvent('dashboard_search_executed', { query: q });

            btnValidate.disabled = true;
            btnValidate.textContent = 'Validando...';

            try {
                const res = await fetch(`<?= site_url('api/v1/companies') ?>?cif=${encodeURIComponent(q)}`, {
                    headers: { 'X-API-KEY': realKey, 'X-Requested-With': 'XMLHttpRequest' }
                });
                const data = await res.json();

                if(data.success && data.data) {
                    const comp = data.data;
                    document.getElementById('aha-name').textContent = comp.name || '-';
                    document.getElementById('aha-cif').textContent = comp.cif || '-';
                    document.getElementById('aha-status').textContent = comp.status || 'Activa';
                    
                    <?php if (!$isPaid && $walletBalance <= 0): ?>
                    document.getElementById('aha-address').innerHTML = '<span style="filter: blur(4px); user-select: none;">Calle Falsa 123, 28080 Madrid</span> <span style="font-size:0.8rem; color:#e11d48; margin-left:8px; font-weight:800;">🔒 Pro</span>';
                    document.getElementById('aha-activity').innerHTML = '<span style="filter: blur(4px); user-select: none;">La prestación de servicios de consultoría...</span> <span style="font-size:0.8rem; color:#e11d48; margin-left:8px; font-weight:800;">🔒 Pro</span>';
                    <?php else: ?>
                    document.getElementById('aha-address').textContent = comp.address || '-';
                    document.getElementById('aha-activity').textContent = comp.corporate_purpose || '-';
                    <?php endif; ?>
                    
                    ahaCard.style.display = 'block';
                    
                    // Remove internal AI fields from JSON view
                    if (data.data) {
                        delete data.data.ai_seo_text;
                        delete data.data.ai_faqs;
                    }
                    
                    // Update Inline JSON
                    const jsonContainer = document.getElementById('inlineJsonContainer');
                    const jsonPre = document.getElementById('inlineJsonPre');
                    if (jsonContainer && jsonPre) {
                        jsonPre.textContent = JSON.stringify(data, null, 4);
                        jsonContainer.style.display = 'block';
                    }
                    
                    ahaCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Store for JSON view
                    window.lastAhaResult = data;

                    // If it was the first request, visually activate the API Key section instead of reloading
                    <?php if ($requestsUsedThisMonth == 0): ?>
                    const sectionApiKey = document.getElementById('section-api-key');
                    if (sectionApiKey) {
                        sectionApiKey.style.opacity = '1';
                        sectionApiKey.style.borderColor = '#2152ff';
                        sectionApiKey.style.boxShadow = '0 10px 15px -3px rgba(33, 82, 255, 0.1)';
                        const paso2Badge = sectionApiKey.querySelector('div');
                        if (paso2Badge) paso2Badge.style.background = '#2152ff';
                        
                        const pDesc = sectionApiKey.querySelector('p');
                        if (pDesc) pDesc.textContent = 'Usa tu clave para conectar tu sistema.';
                        
                        const btnCopyKey = document.getElementById('btnCopyKey');
                        if (btnCopyKey) {
                            btnCopyKey.style.background = '';
                            btnCopyKey.style.borderColor = '';
                        }
                    }
                    
                    const sectionPaso3 = document.getElementById('section-paso3');
                    if (sectionPaso3) {
                        sectionPaso3.style.opacity = '1';
                        sectionPaso3.style.pointerEvents = 'auto';
                        const paso3Badge = sectionPaso3.querySelector('div');
                        if (paso3Badge) paso3Badge.style.background = '#2152ff';
                    }
                    <?php endif; ?>
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: data.message || 'No se encontró la empresa.',
                        confirmButtonColor: '#2152ff',
                        confirmButtonText: 'Aceptar'
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de conexión',
                    text: 'Error en la validación. Revisa tu conexión.',
                    confirmButtonColor: '#2152ff',
                    confirmButtonText: 'Aceptar'
                });
            } finally {
                btnValidate.disabled = false;
                btnValidate.textContent = 'Validar empresa ahora';
            }
        }

        btnValidate.addEventListener('click', validateCompany);
        inputQ.addEventListener('keypress', (e) => { if(e.key === 'Enter') validateCompany(); });

        document.getElementById('btnShowJson')?.addEventListener('click', () => {
            if(window.lastAhaResult) {
                const jsonStr = JSON.stringify(window.lastAhaResult, null, 4);
                Swal.fire({
                    title: 'Respuesta JSON',
                    html: `
                        <div style="text-align: left;">
                            <pre id="jsonPre" style="background: #0f172a; color: #f8fafc; padding: 16px; border-radius: 8px; font-size: 12px; max-height: 400px; overflow: auto; margin-bottom: 16px;">${jsonStr}</pre>
                            <button id="swalCopyJson" class="btn primary" style="width: 100%; border: none;">Copiar JSON</button>
                        </div>
                    `,
                    showConfirmButton: false,
                    showCloseButton: true,
                    width: '600px',
                    didOpen: () => {
                        document.getElementById('swalCopyJson').addEventListener('click', () => {
                            navigator.clipboard.writeText(jsonStr);
                            const btn = document.getElementById('swalCopyJson');
                            btn.textContent = '¡Copiado!';
                            btn.style.background = '#10b981';
                            setTimeout(() => {
                                btn.textContent = 'Copiar JSON';
                                btn.style.background = '#2152ff';
                            }, 2000);
                        });
                    }
                });
            }
        });

        document.getElementById('btnCopyInlineJson')?.addEventListener('click', () => {
            const pre = document.getElementById('inlineJsonPre');
            if(pre && pre.textContent) {
                navigator.clipboard.writeText(pre.textContent);
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success',
                    title: 'JSON Copiado', showConfirmButton: false, timer: 1500
                });
            }
        });

        document.getElementById('btnCopyEndpoint')?.addEventListener('click', () => {
            const endpoint = `<?= site_url('api/v1/companies') ?>?cif=${encodeURIComponent(inputQ.value || 'B12345678')}`;
            navigator.clipboard.writeText(endpoint);
            
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: 'Endpoint copiado',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
        });

        document.getElementById('btnRotateKey')?.addEventListener('click', async () => {
            const { value: confirmed } = await Swal.fire({
                title: '¿Regenerar API Key?',
                text: 'La clave actual dejará de funcionar inmediatamente en todas tus integraciones.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Sí, regenerar',
                cancelButtonText: 'Cancelar'
            });

            if(confirmed) {
                try {
                    const res = await fetch('<?= site_url('billing/rotate-key') ?>', {
                        method: 'POST',
                        headers: { 
                            'X-Requested-With': 'XMLHttpRequest',
                            '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                        }
                    });
                    const data = await res.json();
                    if(data.status === 'success' || data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡API Key regenerada!',
                            text: 'Actualiza tus sistemas con la nueva clave.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        setTimeout(() => window.location.reload(), 1500);
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo regenerar.', 'error');
                    }
                } catch (e) {
                    Swal.fire('Error', 'Error de conexión al servidor.', 'error');
                }
            }
        });
    })();

    // === DEBUG: Reset Onboarding via URL ===
    if(window.location.search.includes('reset_onboarding=1')) {
        localStorage.removeItem('onboarding_completed');
        localStorage.removeItem('user_sector');
        localStorage.removeItem('user_province');
        window.location.href = window.location.pathname;
    }
</script>

<script>
function showPluginComingSoon() {
    Swal.fire({
        title: '¡Muy pronto!',
        text: 'Estamos terminando los últimos detalles del plugin oficial de WordPress. Te avisaremos en cuanto esté disponible para descarga.',
        icon: 'info',
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#2152ff',
        background: '#ffffff',
        customClass: {
            popup: 'premium-swal-popup'
        }
    });
}

function showUpgradeModal() {
    // Tracking: Upgrade Limit Reached View
    if (window.trackEvent) {
        window.trackEvent('upgrade_limit_reached_view', {
            source: 'dashboard_search',
            limit_type: 'free_quota',
            page_type: 'dashboard'
        });
    }

    Swal.fire({
        title: '¡Límite alcanzado!',
        text: 'Has consumido tus <?= $freeLimit ?> consultas gratuitas. Activa el Plan Pro para tener acceso ilimitado y automatizar tus procesos.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Activar Plan Pro',
        cancelButtonText: 'Ahora no',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        background: '#ffffff',
        customClass: {
            popup: 'premium-swal-popup'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= site_url('billing') ?>';
        }
    });
}


</script>

</body>
</html>
