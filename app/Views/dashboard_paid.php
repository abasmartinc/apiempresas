<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/dashboard_paid.css?v=' . (file_exists(FCPATH . 'public/css/dashboard_paid.css') ? filemtime(FCPATH . 'public/css/dashboard_paid.css') : time())) ?>" />
    <style>
        /* Modern Layout Refinement */
        .dash-layout { display: grid; grid-template-columns: 1fr 340px; gap: 32px; align-items: start; }
        @media (max-width: 1024px) { .dash-layout { grid-template-columns: 1fr; } }
        
        /* KPI Cards Refinement */
        .kpi-grid-4 { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 32px; }
        @media (max-width: 768px) { .kpi-grid-4 { grid-template-columns: repeat(2, 1fr); } }
        
        .kpi-card-v2 { background: #ffffff; border: 1px solid #e2e8f0; padding: 20px; border-radius: 16px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02); transition: transform 0.2s; }
        .kpi-card-v2:hover { transform: translateY(-2px); border-color: #2152ff; }
        .kpi-card-v2 .label { font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px; display: block; }
        .kpi-card-v2 .value { font-size: 1.5rem; font-weight: 950; color: #0f172a; line-height: 1; }
        .kpi-card-v2 .meta { font-size: 0.8rem; color: #94a3b8; margin-top: 4px; font-weight: 600; }

        /* Sidebar Spacing */
        .sidebar-stack { display: grid; gap: 24px; }
        
        /* Trigger Boxes */
        .trigger-box { border-left: 6px solid #2152ff; background: #f8faff; padding: 20px; border-radius: 16px; margin-bottom: 24px; border: 1px solid #eef2ff; border-left-width: 6px; }
        .trigger-box h4 { margin: 0 0 4px; color: #1e3a8a; font-weight: 800; font-size: 1rem; }
        .trigger-box p { margin: 0 0 12px; color: #475569; font-weight: 600; font-size: 0.85rem; }
        .trigger-cta { display: inline-block; background: #2152ff; color: white !important; text-decoration: none !important; padding: 8px 16px; border-radius: 8px; font-weight: 800; font-size: 0.8rem; }
        
        /* Test API Card Styling */
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

            <!-- HEADER SECTION -->
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; gap: 20px; flex-wrap: wrap;">
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
                <div>
                    <p style="text-transform: uppercase; letter-spacing: 0.1em; color: #2152ff; font-weight: 800; font-size: 0.75rem; margin-bottom: 4px;">Panel de control</p>
                    <h1 style="margin: 0; font-size: 2.2rem; font-weight: 950; letter-spacing: -0.03em;">Hola, <?= htmlspecialchars($userName) ?></h1>
                </div>
                
                <div style="display: flex; gap: 12px;">
                    <a href="<?= site_url('documentation') ?>" class="btn-small" style="background: white; border: 1px solid #e2e8f0; font-weight: 700; color: #475569; padding: 10px 20px; border-radius: 12px; text-decoration: none;">Documentación</a>
                    <a href="<?= site_url('billing') ?>" class="btn-small primary" style="background: #2152ff; color: white !important; font-weight: 700; padding: 10px 20px; border-radius: 12px; text-decoration: none;">Mi suscripción</a>
                </div>
            </div>

            <!-- KPI GRID (4 COLUMNS) -->
            <div class="kpi-grid-4">
                <div class="kpi-card-v2">
                    <span class="label">Consultas Mes</span>
                    <div class="value" id="kpi-requests">...</div>
                    <div class="meta">de <?= number_format($plan->monthly_quota ?? 0, 0, ',', '.') ?></div>
                </div>
                <div class="kpi-card-v2">
                    <span class="label">Latencia</span>
                    <div class="value"><span id="kpi-latency">...</span><span style="font-size: 0.8rem; margin-left: 2px;">ms</span></div>
                    <div class="meta">media actual</div>
                </div>
                <div class="kpi-card-v2">
                    <span class="label">Ratio Error</span>
                    <div class="value" id="kpi-error">...</div>
                    <div class="meta">histórico (%)</div>
                </div>
                <div class="kpi-card-v2">
                    <span class="label">Estado</span>
                    <div class="value" style="color: #10b981; display: flex; align-items: center; gap: 8px;">
                        <span style="width: 10px; height: 10px; background: #10b981; border-radius: 50%; display: block; animation: pulse 2s infinite;"></span>
                        Operativo
                    </div>
                    <div class="meta">SLA 99.9%</div>
                </div>
            </div>
            
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    fetch('<?= site_url('dashboard/kpis') ?>', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => res.json())
                    .then(data => {
                        if(data.error) return;
                        const numFmt = new Intl.NumberFormat('es-ES');
                        document.getElementById('kpi-requests').innerText = numFmt.format(data.api_request_total_month || 0);
                        document.getElementById('kpi-latency').innerText = numFmt.format(data.avg_latency || 0);
                        document.getElementById('kpi-error').innerText = numFmt.format(data.error_rate || 0);
                    })
                    .catch(e => console.error('Error fetching KPIs', e));
                });
            </script>

            <!-- MAIN LAYOUT -->
            <div class="dash-layout">
                
                <!-- MAIN COLUMN -->
                <div class="main-stack">
                    
                    <!-- TRIGGERS -->
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

                    <!-- RADAR CTA (PRIMARY) -->
                    <?php if(!$hasRadar): ?>
                    <?= view('components/radar_strong_cta', ['user' => $user ?? null]) ?>
                    <?php endif; ?>

                    <!-- ONBOARDING (IF NEW) -->
                    <?php if ($reqCount == 0): ?>
                    <section class="dash-card" style="margin-bottom: 32px; border-left: 4px solid #2152ff;">
                        <div class="kicker" style="background: #eff6ff; color: #2152ff;">GUÍA DE INICIO</div>
                        <h2 style="font-size: 1.2rem; margin-bottom: 16px !important;">Cómo realizar tu primera consulta</h2>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px;">
                            <div style="font-size: 0.85rem;">
                                <strong style="display: block; margin-bottom: 4px; color: #2152ff;">1. API Key</strong>
                                <p style="margin: 0; color: #64748b;">Copia la clave de abajo.</p>
                            </div>
                            <div style="font-size: 0.85rem;">
                                <strong style="display: block; margin-bottom: 4px; color: #2152ff;">2. Petición</strong>
                                <p style="margin: 0; color: #64748b;">CIF en cabecera Auth.</p>
                            </div>
                            <div style="font-size: 0.85rem;">
                                <strong style="display: block; margin-bottom: 4px; color: #2152ff;">3. Escala</strong>
                                <p style="margin: 0; color: #64748b;">Plan Pro para producción.</p>
                            </div>
                        </div>
                    </section>
                    <?php endif; ?>

                    <!-- RESTORED: INTERACTIVE API TEST -->
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

                    <!-- API KEY & QUICK ACTIONS (SIDE BY SIDE) -->
                    <div style="display: grid; grid-template-columns: 1.2fr 1fr; gap: 24px; margin-bottom: 32px; margin-top: 32px;">
                        <!-- API KEY -->
                        <section class="dash-card" style="margin: 0;">
                            <div class="kicker">Credenciales</div>
                            <h2 style="font-size: 1.1rem; margin-bottom: 12px !important;">Tu API Key</h2>
                            <div class="apikey-row" style="margin-bottom: 0;">
                                <div class="apikey-box" id="apiKeyBox" data-api-key="<?= esc($api_key->api_key ?? 'No generada') ?>">
                                    <div class="apikey-value" id="apiKeyMasked" style="font-size: 0.8rem;">...</div>
                                </div>
                                <div class="apikey-actions">
                                    <button type="button" class="btn-small primary" id="btnCopyKey" style="font-size: 0.75rem; padding: 6px 12px;">Copiar</button>
                                </div>
                            </div>
                        </section>

                        <!-- QUICK ACTIONS -->
                        <section class="dash-card" style="margin: 0;">
                            <div class="kicker">Diagnóstico</div>
                            <div style="display: grid; gap: 8px; margin-top: 12px;">
                                <a href="<?=site_url() ?>consumption" style="font-size: 0.9rem; font-weight: 700; color: #2152ff; text-decoration: none; display: flex; align-items: center; justify-content: space-between;">
                                    Logs de consumo <span>&rarr;</span>
                                </a>
                                <a href="<?=site_url() ?>documentation" style="font-size: 0.9rem; font-weight: 700; color: #2152ff; text-decoration: none; display: flex; align-items: center; justify-content: space-between;">
                                    Documentación <span>&rarr;</span>
                                </a>
                            </div>
                        </section>
                    </div>

                    <!-- WORDPRESS PLUGIN -->
                    <?php if (is_file(APPPATH . 'Views/partials/dashboard/wordpress_plugin.php')): ?>
                        <?= view('partials/dashboard/wordpress_plugin') ?>
                    <?php endif; ?>

                </div>

                <!-- SIDEBAR -->
                <aside class="sidebar-stack">
                    
                    <!-- PLAN ACTUAL -->
                    <section class="dash-card <?= $planClass ?>" style="<?= !$isPaid ? 'border-top: 4px solid #94a3b8;' : 'border-top: 4px solid #2152ff;' ?>">
                        <div class="kicker" style="<?= !$isPaid ? 'background: #f1f5f9; color: #475569;' : '' ?>">
                            <?= $isPaid ? 'Suscripción activa' : '⚠️ Plan Gratuito' ?>
                        </div>
                        <?php if (!$isPaid): ?>
                            <h2 style="margin: 12px 0 8px !important; font-size: 1.25rem;">Estás en plan Free</h2>
                            <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 16px; font-weight: 600;">Ideal para pruebas técnicas.</p>
                            <a href="<?= site_url('billing') ?>" class="btn-radar-cta" style="font-size: 0.9rem; padding: 12px;">
                                Activar acceso completo
                            </a>
                        <?php else: ?>
                            <h2 style="margin: 12px 0 4px !important; font-size: 1.25rem;"><?= esc($planNameRaw) ?></h2>
                            <div style="font-size: 1.2rem; font-weight: 900; color: #0f172a; margin-bottom: 12px;">
                                <?= esc($get($plan, 'price_monthly', '0')) ?> €/mes
                            </div>
                            <a href="<?= site_url('billing') ?>" style="display: block; text-align: center; color: #2152ff; font-weight: 800; font-size: 0.85rem; text-decoration: none; padding: 10px; border: 1px solid #dbeafe; border-radius: 8px;">Gestionar plan &rarr;</a>
                        <?php endif; ?>
                    </section>

                    <!-- RECOMMENDED -->
                    <?php if (!$isPaid || $currentPlanSlug === 'pro'): ?>
                        <?= view('components/recommended_plan') ?>
                    <?php endif; ?>

                    <!-- MONEY BLOCK -->
                    <?= view('components/money_block') ?>

                    <!-- SUPPORT -->
                    <section class="dash-card" style="background: #f8fafc; border: 1px solid #e2e8f0; text-align: center; padding: 24px;">
                        <h3 style="font-size: 0.95rem; font-weight: 800; color: #0f172a; margin-bottom: 8px;">¿Tienes dudas?</h3>
                        <p style="font-size: 0.8rem; color: #64748b; font-weight: 600; margin-bottom: 12px;">Contacta con nuestro equipo.</p>
                        <a href="mailto:soporte@apiempresas.es" style="color: #2152ff; font-weight: 800; font-size: 0.9rem; text-decoration: none;">soporte@apiempresas.es</a>
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
        const btnCopy = document.getElementById('btnCopyKey');
        valueEl.textContent = masked;
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
