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
        
        .test-api-card { background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 24px; margin-top: 24px; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .test-api-title { font-size: 1.1rem; font-weight: 900; color: #0f172a; margin-bottom: 12px; display: flex; align-items: center; gap: 10px; }
        .test-api-desc { font-size: 0.9rem; color: #64748b; margin-bottom: 20px; line-height: 1.5; font-weight: 600; }
        .test-api-result { display: none; margin-top: 20px; background: #f8fafc; border-radius: 12px; padding: 16px; border: 1px solid #e2e8f0; }
        .test-api-json { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; background: #1e293b; color: #f8fafc; padding: 16px; border-radius: 8px; overflow-x: auto; max-height: 300px; white-space: pre-wrap; margin: 12px 0; }
        .test-api-success-tag { color: #10b981; font-weight: 800; font-size: 0.85rem; display: flex; align-items: center; gap: 6px; margin-bottom: 8px; }
        .api-loading { display: flex; align-items: center; gap: 8px; }
        .api-loading::after { content: ""; width: 14px; height: 14px; border: 2px solid #ffffff; border-top-color: transparent; border-radius: 50%; animation: api-spin 0.8s linear infinite; }
        @keyframes api-spin { to { transform: rotate(360deg); } }
    </style>
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

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
                
                <div class="upgrade-triggers">
                    <?php if ($reqCount > 5 && !$hasRadar): ?>
                        <div class="trigger-box">
                            <h4>🚀 Estás haciendo búsquedas manuales</h4>
                            <p>Podrías recibir estas empresas automáticamente cada día en tu email antes que tu competencia.</p>
                            <a href="<?= site_url('empresas-nuevas-hoy') ?>" class="trigger-cta">Automatizar con Radar</a>
                        </div>
                    <?php elseif ($usagePercent >= 20): ?>
                        <div class="trigger-box trigger-box--warning">
                            <h4>⚠️ Estás usando el <?= $usagePercent ?>% del plan</h4>
                            <p>Evita que tu integración se detenga al alcanzar el límite. Asegura tu producción ahora.</p>
                            <a href="<?= site_url('billing') ?>" class="trigger-cta">Activar Pro ahora</a>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!$hasRadar): ?>
                    <?= view('components/radar_strong_cta', ['user' => $user ?? null]) ?>
                <?php endif; ?>
            </div>

            <div class="dash-grid">
                <div>
                    <section class="dash-card" id="section-api-key">
                        <div class="kicker">Seguridad</div>
                        <h2>Tu API Key</h2>
                        <div class="apikey-row">
                            <div class="apikey-box" id="apiKeyBox" data-api-key="<?=htmlspecialchars($api_key->api_key ?? '') ?>">
                                <div>
                                    <div class="apikey-label">API KEY</div>
                                    <div class="apikey-value" id="apiKeyMasked"><?=htmlspecialchars($api_key->api_key ?? '') ?></div>
                                </div>
                            </div>
                            <div class="apikey-actions">
                                <button type="button" class="btn-small" id="btnToggleKey">Mostrar</button>
                                <button type="button" class="btn-small primary" id="btnCopyKey">Copiar</button>
                            </div>
                        </div>
                    </section>

                    <!-- RESTORED: Interactive API Test -->
                    <section class="test-api-card" id="test-api-container">
                        <div class="test-api-title">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2152ff" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            Probar API ahora
                        </div>
                        <p class="test-api-desc">
                            Ejecuta una consulta real a la API con un clic. Verás la respuesta JSON y cómo se estructuran los datos de una empresa real.
                        </p>
                        
                        <div id="test-api-initial">
                            <button type="button" class="btn primary" id="btnRunTest" style="background: #0f172a; color: white !important; padding: 16px 32px; width: 100%; font-weight: 800; border: none; border-radius: 14px; transition: transform 0.2s;">
                                👉 Probar API ahora
                            </button>
                            <div style="margin-top:12px; font-size: 0.8rem; color: #64748b; text-align: center; font-weight: 600;">
                                Se usará un CIF de ejemplo real. La consulta se descontará de tu cuota mensual.
                            </div>
                        </div>

                        <div class="test-api-result" id="test-api-result">
                            <div class="test-api-success-tag">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                Has validado una empresa correctamente
                            </div>
                            <div class="test-api-json" id="test-api-json-content"></div>
                            
                            <button type="button" class="btn primary" id="btnTestAnother" style="width:100%; padding:14px; font-weight:800; background: #2152ff; border: none; border-radius: 10px; margin-top: 12px;">
                                Probar otra empresa
                            </button>
                        </div>
                    </section>
                </div>

                <aside>
                    <section class="dash-card <?= $planClass ?>" style="<?= !$isPaid ? 'border-top: 4px solid #94a3b8;' : 'border-top: 4px solid #2152ff;' ?>">
                        <div class="kicker" style="<?= !$isPaid ? 'background: #f1f5f9; color: #475569;' : '' ?>">
                            <?= $isPaid ? 'PLAN ACTUAL' : '⚠️ PLAN LIMITADO' ?>
                        </div>
                        <?php if (!$isPaid): ?>
                            <h2 style="margin-top: 12px !important;">Plan Free</h2>
                            <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 20px; font-weight: 600;">Ideal para pruebas técnicas.</p>
                            <a href="<?= site_url('billing') ?>" class="btn primary" style="width: 100%; display: block; text-align: center; text-decoration: none; padding: 14px; font-weight: 800;">
                                Activar Pro
                            </a>
                        <?php else: ?>
                            <h2 style="margin-top: 12px !important;"><?= esc($planNameRaw) ?></h2>
                            <a href="<?= site_url('billing') ?>" style="display: block; text-align: center; color: #2152ff; font-weight: 800; font-size: 0.9rem; text-decoration: none;">Gestionar plan &rarr;</a>
                        <?php endif; ?>
                    </section>

                    <?php if (!$isPaid || $currentPlanSlug === 'pro'): ?>
                        <?= view('components/recommended_plan') ?>
                    <?php endif; ?>

                    <?= view('components/money_block') ?>

                    <section class="dash-card" style="background: #f8fafc; border: 1px solid #e2e8f0;">
                        <h3 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">Soporte</h3>
                        <a href="mailto:soporte@apiempresas.es" style="color: #2152ff; font-weight: 800; font-size: 0.85rem; text-decoration: none;">soporte@apiempresas.es</a>
                    </section>
                </aside>
            </div>
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
                setTimeout(() => btnCopy.textContent = 'Copiar', 1800);
            }catch(e){ alert('Error al copiar.'); }
        });
    })();

    // === RESTORED: Interactive API Test Logic ===
    (function(){
        const container = document.getElementById('test-api-container');
        if(!container) return;

        const btnRun = document.getElementById('btnRunTest');
        const btnAnother = document.getElementById('btnTestAnother');
        const initialView = document.getElementById('test-api-initial');
        const resultView = document.getElementById('test-api-result');
        const jsonContent = document.getElementById('test-api-json-content');
        const realKey = document.getElementById('apiKeyBox')?.getAttribute('data-api-key') || '';

        async function runTest() {
            if(!realKey) {
                alert('No se ha detectado tu API Key.');
                return;
            }

            const originalBtnText = btnRun.innerHTML;
            btnRun.disabled = true;
            btnRun.innerHTML = '<span class="api-loading">Consultando API...</span>';

            try {
                const sampleRes = await fetch('<?= site_url('dashboard/test-sample') ?>');
                const sampleData = await sampleRes.json();
                if(!sampleData.success) throw new Error('No se pudo obtener una muestra.');
                
                const cif = sampleData.data.cif;
                const apiRes = await fetch(`<?= site_url('api/v1/companies') ?>?cif=${cif}`, {
                    headers: { 'X-API-KEY': realKey, 'X-Requested-With': 'XMLHttpRequest' }
                });
                const apiData = await apiRes.json();

                jsonContent.textContent = JSON.stringify(apiData, null, 4);
                initialView.style.display = 'none';
                resultView.style.display = 'block';
                
                btnRun.innerHTML = originalBtnText;
                btnRun.disabled = false;
            } catch (error) {
                console.error('Test API error:', error);
                alert('Error al probar la API. Inténtalo de nuevo.');
                btnRun.innerHTML = originalBtnText;
                btnRun.disabled = false;
            }
        }

        btnRun.addEventListener('click', runTest);
        btnAnother.addEventListener('click', () => {
            resultView.style.display = 'none';
            initialView.style.display = 'block';
            runTest();
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

</body>
</html>
