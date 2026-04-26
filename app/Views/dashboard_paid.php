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
                $planNameRaw = $get($plan, 'plan_name', 'Pro');
                $currentPlanSlug = strtolower(trim($planNameRaw));
                $isPaid = true; 

                $planClass = '';
                if (strpos($currentPlanSlug, 'business') !== false) $planClass = 'plan-card--business';
                elseif (strpos($currentPlanSlug, 'pro') !== false) $planClass = 'plan-card--pro';

                $requestsUsed = (int)$requestsUsedThisMonth;
                $maxLimitVal = (int)$maxLimit;
                
                $reachedLimit = ($requestsUsed >= $maxLimitVal);
                $warningLimit = ($maxLimitVal * 0.8); 

                $usagePercent = round(($requestsUsed / $maxLimitVal) * 100);

                // Branding dinámico
                $isBusiness = (strpos($currentPlanSlug, 'business') !== false);
                $brandColor = $isBusiness ? '#10b981' : '#2152ff';
                $cardBg = $isBusiness 
                    ? 'linear-gradient(135deg, #065f46 0%, #10b981 100%)' 
                    : 'linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%)';
                $cardAccent = $isBusiness ? '#34d399' : '#60a5fa';
                $btnTextColor = $isBusiness ? '#064e3b' : '#2152ff';
            ?>

            <div class="dash-header">
                <p style="text-transform: uppercase; letter-spacing: 0.1em; color: <?= $brandColor ?>; font-weight: 800; font-size: 0.75rem; margin-bottom: 4px;">PANEL DE CONTROL <?= strtoupper(esc($planNameRaw)) ?></p>
                <h1>Hola, <?= htmlspecialchars($userName) ?></h1>
            </div>

            <div class="kpi-grid-4">
                <?php 
                    $kpiClass = '';
                    if ($reachedLimit) $kpiClass = 'kpi-card--cta';
                    elseif ($requestsUsed >= $warningLimit) $kpiClass = 'kpi-card--warning';
                ?>
                <div class="kpi-card-pro <?= $kpiClass ?>" <?= ($reachedLimit) ? 'onclick="showUpgradeBusinessModal()"' : '' ?>>
                    <div class="kpi-icon-box">
                        <?php if ($reachedLimit): ?>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                        <?php else: ?>
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20M2 12h20M12 2l4.5 4.5M12 22l-4.5-4.5M2 12l4.5 4.5M22 12l-4.5-4.5"/></svg>
                        <?php endif; ?>
                    </div>
                    <div class="kpi-content">
                        <span class="label"><?= ($reachedLimit) ? 'Límite alcanzado' : 'Consultas Mes' ?></span>
                        <div class="value">
                            <span id="kpi-requests"><?= $requestsUsed ?></span>
                        </div>
                        <div class="meta"><?= ($reachedLimit) ? '<strong>Activar Business &rarr;</strong>' : 'Límite: ' . number_format($maxLimitVal, 0, ',', '.') ?></div>
                    </div>
                </div>
                
                <div class="kpi-card-pro">
                    <div class="kpi-icon-box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2L3 14h9l-1 8 10-12h-9l1-8z"/></svg>
                    </div>
                    <div class="kpi-content">
                        <span class="label">Latencia</span>
                        <div class="value">
                            <span id="kpi-latency">...</span>
                            <span class="value-unit">ms</span>
                        </div>
                        <div class="meta">Velocidad real</div>
                    </div>
                </div>

                <div class="kpi-card-pro">
                    <div class="kpi-icon-box" style="background: #fff1f2; color: #e11d48;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    </div>
                    <div class="kpi-content">
                        <span class="label">Ratio Error</span>
                        <div class="value" id="kpi-error">...</div>
                        <div class="meta">Tasa de fallo</div>
                    </div>
                </div>

                <div class="kpi-card-pro">
                    <div class="kpi-icon-box" style="background: #f0fdf4; color: #16a34a;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"/><path d="m9 12 2 2 4-4"/></svg>
                    </div>
                    <div class="kpi-content">
                        <span class="label">Estado</span>
                        <div class="value" style="color: #10b981;">Operativo</div>
                        <div class="meta">Disponibilidad 99.9%</div>
                    </div>
                </div>
            </div>

            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    fetch('<?= site_url('dashboard/kpis') ?>', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => res.json())
                    .then(data => {
                        if(data.error) return;
                        const numFmt = new Intl.NumberFormat('es-ES');
                        const reqVal = document.getElementById('kpi-requests');
                        if (reqVal) reqVal.innerText = numFmt.format(data.api_request_total_month || 0);
                        document.getElementById('kpi-latency').innerText = numFmt.format(data.avg_latency || 0);
                        document.getElementById('kpi-error').innerText = numFmt.format(data.error_rate || 0);
                    })
                    .catch(e => console.error('Error fetching KPIs', e));
                });
            </script>

            <div class="dash-grid">
                <div>
                    <!-- 1. BLOQUE PRINCIPAL ACTIVACIÓN -->
                    <section class="activation-main-card" style="<?= ($reachedLimit) ? 'border: 2px solid #e11d48; background: #fff1f2;' : '' ?>">
                        <div class="activation-header">
                            <h2><?= ($reachedLimit) ? 'Límite mensual Pro alcanzado' : 'Valida empresas en segundos' ?></h2>
                            <p><?= ($reachedLimit) ? 'Has agotado tus '.number_format($maxLimitVal, 0, ',', '.').' consultas Pro. Activa el Plan Business para seguir validando.' : 'Introduce un CIF o nombre y valida una empresa real.' ?></p>
                        </div>
                        
                        <div class="search-form-dash" style="position: relative;">
                            <input type="text" id="dash_q" class="search-input-dash" placeholder="Ej: B12345678 o Nombre de Empresa" <?= ($reachedLimit) ? 'readonly style="background: #f8fafc; cursor: not-allowed;"' : '' ?>>
                            <button id="btnDashValidate" class="btn-validate-dash" <?= ($reachedLimit) ? 'style="background: #94a3b8;"' : '' ?>>Validar ahora</button>
                            
                            <?php if ($reachedLimit): ?>
                            <div onclick="showUpgradeBusinessModal()" style="position: absolute; inset: 0; cursor: pointer; z-index: 5;"></div>
                            <?php endif; ?>
                        </div>

                        <div class="progress-container" style="margin-top: 12px;">
                            <div class="progress-bar-bg" style="height: 12px; background: #e2e8f0; border-radius: 6px;">
                                <?php 
                                    $prog = ($requestsUsed / $maxLimitVal) * 100;
                                    $displayPercent = ($prog > 0 && $prog < 5) ? 5 : ceil($prog); 
                                ?>
                                <div class="progress-bar-fill" style="--target-width: <?= min(100, $displayPercent) ?>%; <?= ($reachedLimit) ? 'background: #e11d48;' : (($requestsUsed >= $warningLimit) ? 'background: #f59e0b;' : '') ?>"></div>
                            </div>
                            <span class="progress-text" style="font-size: 0.8rem; font-weight: 700; color: #64748b; margin-top: 8px; display: block;">
                                <?= number_format($requestsUsed, 0, ',', '.') ?> de <?= number_format($maxLimitVal, 0, ',', '.') ?> consultas utilizadas
                            </span>
                            <span style="font-size: 0.75rem; color: #64748b; font-weight: 700; display: block; margin-top: 4px;">
                                <?= ($reachedLimit) ? '<span style="color: #e11d48;">Has alcanzado el límite de tu plan.</span>' : 'Uso actual de tu suscripción Pro' ?>
                            </span>
                        </div>

                        <div style="margin-top: 16px; font-size: 0.85rem; color: #64748b; font-weight: 600; display: flex; align-items: center; gap: 8px;">
                            <div style="background: #eff6ff; color: #2152ff; width: 24px; height: 24px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
                            </div>
                            <span><strong>Caso real:</strong> Valida automáticamente los CIF de tus clientes y evita errores en tu CRM.</span>
                        </div>

                        <!-- AHA MOMENT CARD (Initially hidden) -->
                        <div class="aha-moment-card" id="aha-moment-card">
                            <div class="aha-header">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                Empresa encontrada
                            </div>
                            <div class="aha-grid">
                                <div class="aha-item" style="grid-column: span 2;">
                                    <div class="aha-label">Nombre / Razón Social</div>
                                    <div class="aha-val" id="aha-name" style="font-size: 1.1rem; font-weight: 800;">-</div>
                                </div>
                                <div class="aha-item">
                                    <div class="aha-label">CIF</div>
                                    <div class="aha-val" id="aha-cif">-</div>
                                </div>
                                <div class="aha-item">
                                    <div class="aha-label">Estado</div>
                                    <div class="aha-val" id="aha-status">-</div>
                                </div>
                                <div class="aha-item" style="grid-column: span 2;">
                                    <div class="aha-label">Dirección / Sede Social</div>
                                    <div class="aha-val" id="aha-address">-</div>
                                </div>
                            </div>
                            <div style="margin-top: 20px; border-top: 1px solid #bae6fd; padding-top: 16px;">
                                <p style="font-size: 0.85rem; color: #0369a1; font-weight: 700; margin-bottom: 16px;">Estos datos puedes integrarlos automáticamente en tu CRM o sistema.</p>
                                <div style="display: flex; flex-direction: column; gap: 10px;">
                                    <div style="display: flex; gap: 10px;">
                                        <button class="btn-small" id="btnCopyEndpoint">Copiar endpoint</button>
                                        <button class="btn-small" id="btnShowJson">Ver JSON</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- API KEY SECTION -->
                    <section class="dash-card" id="section-api-key" style="margin-top: 32px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                            <div class="kicker" style="background: #eef2ff; color: #4f46e5; border: 1px solid #e0e7ff;">
                                CREDENCIALES DE PRODUCCIÓN
                            </div>
                            <button type="button" class="btn-small" id="btnRotateKey" style="color: #64748b; border-color: #e2e8f0; font-size: 0.75rem; padding: 6px 12px;">Regenerar clave</button>
                        </div>
                        
                        <h2 style="margin-top: 0 !important;">Tu API Key</h2>
                        <p style="font-size: 0.85rem; color: #64748b; font-weight: 600; margin-bottom: 16px;">
                            Copia tu API Key y úsala para realizar consultas desde tu servidor o CRM.
                        </p>
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

                    <!-- NEXT STEP SECTION -->
                    <div style="margin-top: 24px; padding: 20px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px;">
                        <h4 style="margin: 0 0 12px; font-size: 1rem; font-weight: 800; color: #0f172a;">Siguiente paso:</h4>
                        <p style="margin: 0 0 16px; font-size: 0.9rem; color: #64748b; font-weight: 600;">Integra esta validación en tu sistema y automatiza todo el proceso.</p>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <a href="<?= site_url('documentation') ?>" style="display: inline-flex; align-items: center; gap: 8px; color: #2152ff; font-weight: 800; font-size: 0.85rem; text-decoration: none;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                                Documentación API
                            </a>
                            <a href="javascript:void(0)" onclick="showPluginComingSoon()" style="display: inline-flex; align-items: center; gap: 8px; color: #2152ff; font-weight: 800; font-size: 0.85rem; text-decoration: none;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"></path></svg>
                                Plugin WordPress
                            </a>
                        </div>
                    </div>

                    <!-- 3. GUÍA 3 PASOS -->
                    <h3 style="font-size: 1.25rem; font-weight: 900; color: #0f172a; margin-bottom: 16px; margin-top: 32px;">Empieza en 3 pasos</h3>
                    <div class="step-guide">
                        <div class="step-card">
                            <div class="step-num">1</div>
                            <h4>Prueba una empresa</h4>
                            <p>Valida un CIF o nombre desde el panel.</p>
                        </div>
                        <div class="step-card">
                            <div class="step-num">2</div>
                            <h4>Copia tu API Key</h4>
                            <p>Usa tu clave para conectar tu sistema.</p>
                        </div>
                        <div class="step-card">
                            <div class="step-num">3</div>
                            <h4>Integra en minutos</h4>
                            <p>Consulta la documentación o usa el plugin oficial.</p>
                        </div>
                    </div>

                    <!-- 4. RADAR SECUNDARIO -->
                    <div class="radar-secondary-card" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px dashed #cbd5e1; border-radius: 20px; padding: 24px; margin-top: 32px; display: flex; align-items: center; justify-content: space-between; gap: 24px;">
                        <div class="radar-sec-info">
                            <h3 style="font-size: 1.1rem; font-weight: 900; color: #0f172a; margin-bottom: 8px;">¿Buscas clientes en vez de validar datos?</h3>
                            <p style="font-size: 0.9rem; color: #64748b; font-weight: 600;">Descubre nuevas empresas y encuentra oportunidades antes que tu competencia.</p>
                        </div>
                        <a href="<?= site_url('radar') ?>" class="trigger-cta" style="white-space: nowrap; display: inline-block; background: #2152ff; color: white !important; text-decoration: none !important; padding: 12px 24px; border-radius: 10px; font-weight: 800; font-size: 0.9rem; transition: all 0.2s;">Ver Radar B2B</a>
                    </div>
                </div>

                <aside>
                    <!-- PLAN ACTUAL -->
                    <section class="dash-card <?= $planClass ?>" style="border-top: 4px solid #ffffff; background: <?= $cardBg ?>; color: white !important;">
                        <div class="kicker" style="background: rgba(255,255,255,0.2); color: white; border: 1px solid rgba(255,255,255,0.3);">
                            PLAN ACTUAL <?= strtoupper(esc($planNameRaw)) ?><?= ($get($plan, 'status') === 'canceled') ? ' (CANCELADO)' : '' ?>
                        </div>
                        <h2 style="margin-top: 12px !important; color: white !important; font-size: 1.5rem; font-weight: 900;"><?= esc($planNameRaw) ?><?= ($get($plan, 'status') === 'canceled') ? ' (Cancelado)' : '' ?></h2>
                        <p style="color: white !important; opacity: 0.95; font-size: 1rem; margin-bottom: 16px; font-weight: 800; border-left: 3px solid <?= $cardAccent ?>; padding-left: 12px;">
                            Límite: <?= number_format($maxLimitVal, 0, ',', '.') ?> consultas/mes
                        </p>
                        <p style="color: white !important; opacity: 0.8; font-size: 0.85rem; font-weight: 600; margin-bottom: 24px; line-height: 1.5;">
                            Disfrutas de todas las ventajas del Plan <?= esc($planNameRaw) ?>, incluyendo métricas avanzadas, soporte prioritario y SLA garantizado.
                        </p>
                        <a href="<?= site_url('billing') ?>" class="btn primary" style="width: 100%; display: block; text-align: center; text-decoration: none; padding: 14px; font-weight: 900; background: white; color: <?= $btnTextColor ?> !important; border: none; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.2); border-radius: 12px;">
                            Gestionar suscripción
                        </a>
                    </section>

                    <!-- RECOMMENDED (Business) -->
                    <?php if ($currentPlanSlug === 'pro'): ?>
                        <?= view('components/recommended_plan', ['currentPlanSlug' => $currentPlanSlug, 'isPaid' => $isPaid]) ?>
                    <?php endif; ?>

                    <section class="dash-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px;">
                        <div style="display: flex; align-items: flex-start; gap: 16px;">
                            <div style="background: #eff6ff; color: #2152ff; padding: 10px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                            </div>
                            <div>
                                <h3 style="font-size: 1rem; font-weight: 900; color: #0f172a; margin: 0 0 4px !important;">¿Necesitas ayuda?</h3>
                                <p style="font-size: 0.8rem; color: #64748b; font-weight: 600; margin: 0 0 12px !important; line-height: 1.4;">Nuestro equipo técnico te ayudará con cualquier duda o integración.</p>
                                <a href="mailto:soporte@apiempresas.es" style="display: inline-block; color: #2152ff; font-weight: 800; font-size: 0.85rem; text-decoration: none; border-bottom: 2px solid rgba(33, 82, 255, 0.1); transition: all 0.2s;" onmouseover="this.style.borderColor='#2152ff'" onmouseout="this.style.borderColor='rgba(33, 82, 255, 0.1)'">
                                    soporte@apiempresas.es
                                </a>
                            </div>
                        </div>
                    </section>
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
                setTimeout(() => btnCopy.textContent = 'Copiar', 1800);
            }catch(e){ alert('Error al copiar.'); }
        });
    })();

    // === Dashboard Validation Logic ===
    (function(){
        const btnValidate = document.getElementById('btnDashValidate');
        const inputQ = document.getElementById('dash_q');
        const ahaCard = document.getElementById('aha-moment-card');
        const realKey = document.getElementById('apiKeyBox')?.getAttribute('data-api-key') || '';

        if(!btnValidate) return;

        async function validateCompany() {
            const q = inputQ.value.trim();
            if(!q) {
                alert('Introduce un CIF o nombre.');
                return;
            }

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
                    document.getElementById('aha-address').textContent = comp.address || '-';
                    
                    ahaCard.style.display = 'block';
                    ahaCard.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Store for JSON view
                    window.lastAhaResult = data;
                } else {
                    alert(data.message || 'No se encontró la empresa.');
                }
            } catch (e) {
                alert('Error en la validación. Revisa tu conexión.');
            } finally {
                btnValidate.disabled = false;
                btnValidate.textContent = 'Validar ahora';
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
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
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
        customClass: { popup: 'premium-swal-popup' }
    });
}

function showUpgradeBusinessModal() {
    Swal.fire({
        title: '¡Límite Pro alcanzado!',
        text: 'Has completado tus <?= number_format($maxLimitVal, 0, ',', '.') ?> consultas del Plan Pro. Activa el Plan Business para tener acceso ilimitado y gestión de equipos.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Ver Plan Business',
        cancelButtonText: 'Ahora no',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#64748b',
        background: '#ffffff',
        customClass: { popup: 'premium-swal-popup' }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= site_url('billing') ?>';
        }
    });
}
</script>

</body>
</html>
