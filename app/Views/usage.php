<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/usage.css') ?>" />
    <link rel="stylesheet" href="<?= base_url('public/css/dashboard.css') ?>" />
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div>

    <?= view('partials/header_inner') ?>

    <?php
    $get = function ($src, string $key, $default = null) {
        if (is_array($src))
            return $src[$key] ?? $default;
        if (is_object($src))
            return $src->$key ?? $default;
        return $default;
    };

    $fmt = function ($n) {
        return number_format((int) $n, 0, ',', '.');
    };

    $formatDateTime = function ($dt) {
        if (!$dt)
            return null;
        $ts = strtotime((string) $dt);
        if (!$ts)
            return null;
        return date('d/m/Y H:i', $ts);
    };

    $userName = esc($get($user, 'name', ''));
    $apiKey = $get($api_key, 'api_key', null);

    $planName = $get($plan, 'plan_name', '—');
    $monthlyQuota = $get($plan, 'monthly_quota', null);

    $isBonus = isset($plan->is_bonus) && $plan->is_bonus;
    $walletBalance = $get($plan, 'wallet_balance', 0);
    $walletSpent = $get($plan, 'wallet_spent', 0);
    $walletTotal = $walletBalance + $walletSpent;

    $subStatus = $get($plan, 'status', null);
    $periodStart = $get($plan, 'current_period_start', null);
    $periodEnd = $get($plan, 'current_period_end', null);

    $periodStartFmt = $formatDateTime($periodStart);
    $periodEndFmt = $formatDateTime($periodEnd);

    $usedThisMonth = (int) ($api_request_total_month ?? 0);
    $usedToday = isset($api_request_total_today) ? (int) $api_request_total_today : null;

    $monthlyQuotaRaw = $monthlyQuota;

    if (is_string($monthlyQuotaRaw)) {
        $monthlyQuotaRaw = trim($monthlyQuotaRaw);
        $monthlyQuotaRaw = str_replace(['.', ',', ' '], '', $monthlyQuotaRaw);
    }

    $monthlyQuotaInt = (is_numeric($monthlyQuotaRaw) && (int) $monthlyQuotaRaw > 0)
        ? (int) $monthlyQuotaRaw
        : null;

    $percent = null;
    $remaining = null;
    $usageRatio = 0;

    if ($monthlyQuotaInt !== null) {
        $usageRatio = $usedThisMonth / $monthlyQuotaInt;
        $percent = (int) round($usageRatio * 100);
        $percent = max(0, min(999, $percent));
        $remaining = max(0, $monthlyQuotaInt - $usedThisMonth);
    }

    $stateColor = '#2563eb';

    if ($usageRatio >= 1) {
        $stateColor = '#ef4444';
    } elseif ($usageRatio >= 0.9) {
        $stateColor = '#dc2626';
    } elseif ($usageRatio >= 0.7) {
        $stateColor = '#f97316';
    } elseif ($usageRatio >= 0.3) {
        $stateColor = '#2563eb';
    }

    $barWidth = $percent !== null ? max(0, min(100, $percent)) : 0;

    // Logic for adaptive messaging
    $isVeryLowUsage = $usedThisMonth <= 5;
    $isHighUsage = $usageRatio >= 0.7;
    $isCriticalUsage = $usageRatio >= 0.9;
    ?>
    <style>
        /* Exact Dashboard KPI Styles */
        .kpi-grid-3 { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 24px; }
        @media (max-width: 1024px) { .kpi-grid-3 { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 640px) { .kpi-grid-3 { grid-template-columns: 1fr; } }
        
        .kpi-card-pro { 
            background: #ffffff; 
            border: 1px solid #e2e8f0; 
            padding: 24px; 
            border-radius: 20px; 
            box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02), 0 4px 6px -4px rgba(0,0,0,0.02); 
            display: flex;
            align-items: center;
            gap: 20px;
        }
        .kpi-icon-box { 
            width: 48px; 
            height: 48px; 
            border-radius: 14px; 
            background: #eff6ff; 
            color: #2563eb; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            flex-shrink: 0;
        }
        .kpi-content { flex: 1; min-width: 0; }
        .kpi-content .label { font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 4px; }
        .kpi-content .value { font-size: 1.5rem; font-weight: 900; color: #0f172a; letter-spacing: -0.02em; display: flex; align-items: baseline; gap: 4px; }
        .kpi-content .meta { font-size: 0.75rem; color: #94a3b8; margin-top: 6px; font-weight: 600; display: flex; align-items: center; gap: 4px; }
        
        /* Log Slideover */
        .log-row:hover { background: #f8fafc; }
        .log-slideover { position: fixed; inset: 0; z-index: 9999; display: none; justify-content: flex-end; }
        .log-slideover.active { display: flex; }
        .log-slideover-overlay { position: absolute; inset: 0; background: rgba(15,23,42,0.4); backdrop-filter: blur(2px); animation: fadeIn 0.2s ease-out; }
        .log-slideover-panel { position: relative; width: 450px; max-width: 100%; background: #fff; height: 100%; box-shadow: -10px 0 25px rgba(0,0,0,0.1); animation: slideIn 0.3s cubic-bezier(0.16, 1, 0.3, 1); display: flex; flex-direction: column; }
        .log-slideover-header { padding: 20px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; background: #f8fafc; }
        .log-slideover-header h3 { margin: 0; font-size: 1.1rem; color: #0f172a; font-weight: 800; }
        .log-slideover-header button { background: none; border: none; font-size: 1.5rem; color: #64748b; cursor: pointer; padding: 0; line-height: 1; }
        .log-slideover-body { padding: 24px; overflow-y: auto; flex: 1; display: flex; flex-direction: column; gap: 20px; }
        
        .log-meta-group label, .log-meta label { display: block; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; font-weight: 800; margin-bottom: 6px; }
        .copy-box { display: flex; background: #f1f5f9; border: 1px solid #cbd5e1; border-radius: 8px; overflow: hidden; }
        .copy-box code { flex: 1; padding: 10px 12px; font-size: 0.85rem; color: #0f172a; border: none; background: transparent; }
        .copy-box button { background: #e2e8f0; border: none; border-left: 1px solid #cbd5e1; padding: 0 12px; cursor: pointer; color: #475569; transition: background 0.2s; }
        .copy-box button:hover { background: #cbd5e1; color: #0f172a; }
        
        .log-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; background: #f8fafc; border: 1px solid #e2e8f0; padding: 16px; border-radius: 12px; }
        .log-meta span { font-weight: 700; color: #0f172a; font-size: 0.95rem; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 0.8rem; font-weight: 800; position: relative !important; top: auto !important; right: auto !important; }
        .badge.success { background: #d1fae5; color: #065f46; border: 1px solid #34d399; }
        .badge.error { background: #fee2e2; color: #991b1b; border: 1px solid #f87171; }
        .badge-method { background: #eff6ff; color: #1e40af; border: 1px solid #93c5fd; }
        
        .block-code { display: block; padding: 10px 12px; background: #1e293b; color: #e2e8f0; border-radius: 8px; font-size: 0.85rem; font-family: monospace; word-break: break-all; }
        .log-notice { display: flex; gap: 10px; background: #fffbeb; border: 1px solid #fde68a; padding: 16px; border-radius: 8px; color: #b45309; font-size: 0.85rem; line-height: 1.5; }
        .log-notice svg { flex-shrink: 0; color: #d97706; }
        
        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideIn { from { transform: translateX(100%); } to { transform: translateX(0); } }
    </style>

    <main class="dash-main">
        <div class="container">

            <div class="dash-header" style="margin: 10px 0 24px;">
                <h1>Uso de la API</h1>
                <p class="dash-sub">Distribución del consumo y registro de llamadas de tu cuenta.</p>
            </div>

            <!-- KPIs (Dashboard Exact Match) -->
            <div class="kpi-grid-3">
                <div class="kpi-card-pro">
                    <div class="kpi-icon-box">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div class="kpi-content">
                        <span class="label">Consultas este mes</span>
                        <div class="value">
                            <span><?= $fmt($usedThisMonth) ?></span>
                        </div>
                        <div class="meta">Acumulado actual</div>
                    </div>
                </div>

                <div class="kpi-card-pro">
                    <div class="kpi-icon-box" style="background: #f0fdf4; color: #10b981;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div class="kpi-content">
                        <span class="label">Consultas hoy</span>
                        <div class="value">
                            <span><?= $usedToday !== null ? $fmt($usedToday) : '—' ?></span>
                        </div>
                        <div class="meta">Actividad diaria</div>
                    </div>
                </div>

                <div class="kpi-card-pro" style="border-color: #ecfdf5; background: #ffffff;">
                    <div class="kpi-icon-box" style="background: #ecfdf5; color: #10b981;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                    </div>
                    <div class="kpi-content">
                        <span class="label">Estado de la API</span>
                        <div class="value" style="color: #10b981;">
                            <span>Operativo</span>
                        </div>
                        <div class="meta">Disponibilidad 99.9%</div>
                    </div>
                </div>
            </div>

            <!-- Filtros a todo lo ancho -->
            <form class="dash-card" method="get" action="<?= current_url() ?>" style="display: flex; gap: 24px; align-items: flex-end; margin-bottom: 24px; padding: 20px 24px; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 180px;">
                    <label for="range" style="font-size: 11px; font-weight: 800; color: #64748b; margin-bottom: 6px; display: block; text-transform: uppercase; letter-spacing: 0.08em;">Rango</label>
                    <select id="range" name="range" style="width: 100%; border-radius: 10px; border: 1px solid #cbd5e1; padding: 10px 14px; font-size: 13px; background: #f8fafc; color: #0f172a; font-weight: 700; outline: none; transition: border-color 0.2s;">
                        <option value="30" <?= (($_GET['range'] ?? '') === '30') ? 'selected' : '' ?>>Últimos 30 días</option>
                        <option value="7" <?= (($_GET['range'] ?? '') === '7') ? 'selected' : '' ?>>Últimos 7 días</option>
                        <option value="today" <?= (($_GET['range'] ?? '') === 'today') ? 'selected' : '' ?>>Hoy</option>
                        <option value="custom" <?= (($_GET['range'] ?? '') === 'custom') ? 'selected' : '' ?>>Personalizado…</option>
                    </select>
                </div>

                <div style="flex: 1; min-width: 180px;">
                    <label for="from" style="font-size: 11px; font-weight: 800; color: #64748b; margin-bottom: 6px; display: block; text-transform: uppercase; letter-spacing: 0.08em;">Desde</label>
                    <input type="date" id="from" name="from" value="<?= esc($_GET['from'] ?? '') ?>" style="width: 100%; border-radius: 10px; border: 1px solid #cbd5e1; padding: 10px 14px; font-size: 13px; background: #f8fafc; color: #0f172a; font-weight: 700; outline: none; transition: border-color 0.2s;">
                </div>

                <div style="flex: 1; min-width: 180px;">
                    <label for="to" style="font-size: 11px; font-weight: 800; color: #64748b; margin-bottom: 6px; display: block; text-transform: uppercase; letter-spacing: 0.08em;">Hasta</label>
                    <input type="date" id="to" name="to" value="<?= esc($_GET['to'] ?? '') ?>" style="width: 100%; border-radius: 10px; border: 1px solid #cbd5e1; padding: 10px 14px; font-size: 13px; background: #f8fafc; color: #0f172a; font-weight: 700; outline: none; transition: border-color 0.2s;">
                </div>

                <div>
                    <button type="submit" style="padding: 11px 24px; border-radius: 10px; font-size: 13px; font-weight: 800; background: linear-gradient(135deg, #2152ff 0%, #0284c7 100%); color: white; border: none; cursor: pointer; box-shadow: 0 8px 20px rgba(33, 82, 255, 0.35); transition: transform 0.1s;">Actualizar</button>
                </div>
            </form>

            <!-- Main Layout (Grid) -->
            <div class="dash-grid">
                <!-- Columna Izquierda (Gráfico y Tablas) -->
                <div class="dash-content">
                    <section class="dash-card">
                        <h2>Consultas por día</h2>
                        <p>Distribución del número de consultas realizadas en el periodo seleccionado.</p>
                        <div class="chart-wrapper">
                            <canvas id="usageChart" height="110"></canvas>
                        </div>
                    </section>

                    <section class="dash-card" style="margin-top: 24px;">
                        <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; margin-bottom: 20px; gap: 16px;">
                            <div>
                                <h2>Registro de Actividad Reciente</h2>
                                <p style="margin:0;">Haz clic en cualquier petición para inspeccionar sus metadatos.</p>
                            </div>
                            <div style="display: flex; gap: 12px; align-items: center; flex-wrap: wrap;">
                                <input type="text" id="logFilterSearch" placeholder="Buscar CIF o ID..." style="padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px; outline: none; width: 180px;">
                                <select id="logFilterEndpoint" style="padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px; outline: none; background: #fff;">
                                    <option value="">Todos los endpoints</option>
                                    <option value="/companies/search">/companies/search</option>
                                    <option value="/companies/score">/companies/score</option>
                                    <option value="/companies/signals">/companies/signals</option>
                                    <option value="/companies/insights">/companies/insights</option>
                                    <option value="/companies/radar">/companies/radar</option>
                                    <option value="/companies">/companies (CIF)</option>
                                </select>
                                <select id="logFilterStatus" style="padding: 8px 12px; border-radius: 8px; border: 1px solid #cbd5e1; font-size: 13px; outline: none; background: #fff;">
                                    <option value="">Todos los estados</option>
                                    <option value="success">Solo Éxito (200)</option>
                                    <option value="error">Solo Errores (!200)</option>
                                </select>
                            </div>
                        </div>

                        <!-- Top Pagination Controls -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; padding-bottom: 12px;">
                            <div style="font-size: 13px; color: #64748b; font-weight: 600;" id="logPaginationInfoTop">
                                Mostrando página 1 de 1
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <button id="logBtnPrevTop" onclick="changeLogPage(-1)" style="padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-size: 13px; font-weight: 600; color: #475569; transition: all 0.2s;">Anterior</button>
                                <button id="logBtnNextTop" onclick="changeLogPage(1)" style="padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-size: 13px; font-weight: 600; color: #475569; transition: all 0.2s;">Siguiente</button>
                            </div>
                        </div>

                        <div class="usage-table-wrapper" id="logsTableContainer">
                            <table class="usage-table">
                                <thead style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Endpoint</th>
                                        <th>Búsqueda</th>
                                        <th>Resp</th>
                                        <th>Origen</th>
                                    </tr>
                                </thead>
                                <tbody id="logsTableBody">
                                    <tr>
                                        <td colspan="5" style="text-align: center; padding: 40px; color: #64748b; font-weight: 600;">Cargando registros...</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination Controls -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 16px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
                            <div style="font-size: 13px; color: #64748b; font-weight: 600;" id="logPaginationInfo">
                                Mostrando página 1 de 1
                            </div>
                            <div style="display: flex; gap: 8px;">
                                <button id="logBtnPrev" onclick="changeLogPage(-1)" style="padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-size: 13px; font-weight: 600; color: #475569;">Anterior</button>
                                <button id="logBtnNext" onclick="changeLogPage(1)" style="padding: 6px 12px; border-radius: 6px; border: 1px solid #cbd5e1; background: #fff; cursor: pointer; font-size: 13px; font-weight: 600; color: #475569;">Siguiente</button>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Columna Derecha (Sidebar) -->
                <aside class="dash-sidebar">
                    <?php if ($isBonus): ?>
                    <!-- Tarjeta Monedero Prepago -->
                    <div class="dash-card" style="margin-bottom: 24px; background: #f0fdf4; border: 1px solid #10b981; border-top: 4px solid #10b981; box-shadow: 0 4px 6px -1px rgba(16, 185, 129, 0.1); padding: 24px;">
                        <div class="plan-pill" style="background: #d1fae5; color: #065f46; font-weight: 800; border-radius: 6px; display: inline-block; padding: 4px 10px; font-size: 0.7rem; letter-spacing: 0.05em; margin-bottom: 8px;">💎 MONEDERO PREPAGO</div>
                        <h2 style="color: #064e3b; margin-top: 4px !important; margin-bottom: 4px !important; font-size: 2rem; font-weight: 900; display: flex; align-items: baseline; gap: 6px;">
                            <?= $fmt($walletBalance) ?> <span style="font-size: 0.9rem; font-weight: 800; color: #059669;">créditos</span>
                        </h2>
                        
                        <div style="margin-top: 20px;">
                            <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:800; margin-bottom:8px; color:#064e3b;">
                                <span>Saldo consumido</span>
                                <span><?= $fmt($walletSpent) ?> / <?= $fmt($walletTotal) ?></span>
                            </div>
                            <?php $walletPct = ($walletTotal > 0) ? ($walletSpent / $walletTotal * 100) : 0; ?>
                            <div style="width:100%; height:10px; background:#d1fae5; border-radius:999px; overflow:hidden;">
                                <div style="width: <?= $walletPct ?>%; height:100%; background:#10b981; border-radius:999px;"></div>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <!-- Tarjeta de Plan (Estilo Dashboard) -->
                    <div class="plan-card <?= stripos($planName, 'pro') !== false ? 'plan-card--pro' : (stripos($planName, 'business') !== false ? 'plan-card--business' : '') ?>" style="margin-bottom: 24px;">
                        <div class="plan-pill"><?= esc($planName) ?></div>
                        <h2>Límites de tu plan</h2>
                        
                        <div style="margin-top: 16px;">
                            <div style="display:flex; justify-content:space-between; font-size:12px; font-weight:700; margin-bottom:6px; color:#fff;">
                                <span>Consumo Actual</span>
                                <span><?= $fmt($usedThisMonth) ?> / <?= $monthlyQuotaInt !== null ? $fmt($monthlyQuotaInt) : '∞' ?></span>
                            </div>
                            <div style="width:100%; height:8px; background:rgba(255,255,255,0.2); border-radius:999px; overflow:hidden;">
                                <div style="width: <?= $barWidth ?>%; height:100%; background:#fff; border-radius:999px;"></div>
                            </div>
                            <?php if ($monthlyQuotaInt !== null): ?>
                                <div style="text-align: right; font-size: 11px; color: rgba(255,255,255,0.8); margin-top: 6px; font-weight: 600;">
                                    Te quedan <?= $fmt($remaining) ?> consultas
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Desglose por Endpoint Mini -->
                    <div class="dash-card">
                        <h2>Uso por Endpoint</h2>
                        <div class="usage-table-wrapper" style="margin-top: 12px; box-shadow: none; border: none; overflow: visible;">
                            <table class="usage-table" style="font-size: 12px;">
                                <thead>
                                    <tr>
                                        <th style="padding: 8px 0;">Ruta</th>
                                        <th style="padding: 8px 0; text-align: right;">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($endpoint_breakdown)): ?>
                                        <?php foreach ($endpoint_breakdown as $ep): ?>
                                            <tr>
                                                <td style="padding: 8px 0;"><span class="usage-pill" style="font-size: 10px; padding: 2px 6px;"><?= esc($ep['endpoint']) ?></span></td>
                                                <td style="padding: 8px 0; text-align: right; font-weight: 800;"><?= $fmt($ep['total']) ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="2" style="text-align: center; opacity: 0.6; padding: 12px;">Sin datos</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <?php if (strpos(strtolower($planName), 'business') === false && !$isBonus): ?>
                    <div style="margin-top: 24px;">
                        <?= view('components/recommended_plan', [
                            'currentPlanSlug' => strtolower($planName), 
                            'isPaid' => (strpos(strtolower($planName), 'free') === false)
                        ]) ?>
                    </div>
                    <?php endif; ?>

                    <section class="dash-card" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 24px; margin-top: 24px;">
                        <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 16px;">
                            <div style="background: #eff6ff; color: #2152ff; padding: 12px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="4"></circle><line x1="4.93" y1="4.93" x2="9.17" y2="9.17"></line><line x1="14.83" y1="14.83" x2="19.07" y2="19.07"></line><line x1="14.83" y1="9.17" x2="19.07" y2="4.93"></line><line x1="4.93" y1="19.07" x2="9.17" y2="14.83"></line></svg>
                            </div>
                            <div>
                                <h3 style="font-size: 1.1rem; font-weight: 900; color: #0f172a; margin: 0 0 4px !important;">Soporte Técnico</h3>
                                <p style="font-size: 0.85rem; color: #64748b; font-weight: 600; margin: 0 !important; line-height: 1.4;">¿Necesitas ayuda con la API o tu cuenta?</p>
                            </div>
                        </div>
                        <a href="<?= site_url('tickets') ?>" style="display: block; width: 100%; text-align: center; background: #f8fafc; color: #0f172a; padding: 12px; border-radius: 10px; border: 1px solid #e2e8f0; font-weight: 800; font-size: 0.95rem; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'; this.style.borderColor='#cbd5e1';" onmouseout="this.style.background='#f8fafc'; this.style.borderColor='#e2e8f0';">
                            Abrir un Ticket
                        </a>
                    </section>
                </aside>
            </div>
        </div>
    </main>>

        </div>
    </main>

    <!-- Log Details Slide-over -->
    <div id="logSlideover" class="log-slideover">
        <div class="log-slideover-overlay" onclick="closeLogDetails()"></div>
        <div class="log-slideover-panel">
            <div class="log-slideover-header">
                <h3>Detalles de la Petición</h3>
                <button onclick="closeLogDetails()">&times;</button>
            </div>
            <div class="log-slideover-body">
                <div class="log-meta-group">
                    <label>Request ID</label>
                    <div class="copy-box">
                        <code id="logModalReqId"></code>
                        <button onclick="copyReqId()" title="Copiar"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg></button>
                    </div>
                </div>
                
                <div class="log-grid">
                    <div class="log-meta">
                        <label>Método</label>
                        <span class="badge badge-method log-modal-method-text">---</span>
                    </div>
                    <div class="log-meta">
                        <label>Estado HTTP</label>
                        <span class="badge log-modal-status-text">---</span>
                    </div>
                    <div class="log-meta">
                        <label>Latencia</label>
                        <span id="logModalLatency"></span>
                    </div>
                    <div class="log-meta">
                        <label>Fecha y Hora</label>
                        <span id="logModalDate"></span>
                    </div>
                </div>

                <div class="log-meta-group">
                    <label>Endpoint URL</label>
                    <code id="logModalEndpoint" class="block-code"></code>
                </div>

                <div class="log-meta-group">
                    <label>Término de Búsqueda</label>
                    <code id="logModalSearch" class="block-code"></code>
                </div>

                <div class="log-meta-group">
                    <label>Dirección IP</label>
                    <code id="logModalIp" class="block-code"></code>
                </div>

                <div class="log-meta-group">
                    <label>User Agent</label>
                    <code id="logModalUa" class="block-code"></code>
                </div>
                
                <div class="log-notice">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <span>El payload JSON no se almacena en base de datos para garantizar la privacidad y optimizar el rendimiento.</span>
                </div>
            </div>
        </div>
    </div>

    <?= view('partials/footer') ?>

    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

    <script>
        (() => {
            const el = document.getElementById('usageChart');
            if (!el) return;

            const labels = <?= json_encode($chart_labels ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;
            const values = <?= json_encode($chart_values ?? [], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?>;

            const prettyLabels = labels.map(d => {
                const parts = d.split('-');
                return parts.length === 3 ? `${parts[2]}/${parts[1]}` : d;
            });

            new Chart(el, {
                type: 'line',
                data: {
                    labels: prettyLabels,
                    datasets: [{
                        label: 'Consultas',
                        data: values,
                        tension: 0.35,
                        pointRadius: 2,
                        pointHoverRadius: 5,
                        borderWidth: 2,
                        fill: false
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: true }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: { maxTicksLimit: 10 }
                        },
                        y: {
                            beginAtZero: true,
                            ticks: { precision: 0 }
                        }
                    }
                }
            });
        })();

        document.addEventListener('DOMContentLoaded', function () {
            const trackingUrl = '<?= site_url("api/tracking/event") ?>';

            fetch(trackingUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: new URLSearchParams({
                    event_type: 'consumption_view',
                    metadata: JSON.stringify({
                        used: <?= (int) $usedThisMonth ?>,
                        limit: <?= (int) ($monthlyQuotaInt ?? 0) ?>,
                        usage_ratio: <?= (float) $usageRatio ?>
                    })
                })
            }).catch(e => console.error("Tracking error:", e));

            const btnUpgrade = document.querySelector('.btn-upgrade-track');
            if (btnUpgrade) {
                btnUpgrade.addEventListener('click', function () {
                    fetch(trackingUrl, {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: new URLSearchParams({
                            event_type: 'consumption_upgrade_click',
                            metadata: JSON.stringify({
                                used: <?= (int) $usedThisMonth ?>,
                                limit: <?= (int) ($monthlyQuotaInt ?? 0) ?>,
                                usage_ratio: <?= (float) $usageRatio ?>,
                                cta_text: "Activar Pro y evitar quedarte sin acceso"
                            })
                        })
                    }).catch(e => console.error("Tracking error:", e));
                });
            }

            // Initialize AJAX logs
            loadLogs(1);

            // Bind filters
            let searchTimeout;
            const searchInput = document.getElementById('logFilterSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    searchTimeout = setTimeout(() => loadLogs(1), 400);
                });
            }
            
            const endpointFilter = document.getElementById('logFilterEndpoint');
            if (endpointFilter) endpointFilter.addEventListener('change', () => loadLogs(1));
            
            const statusFilter = document.getElementById('logFilterStatus');
            if (statusFilter) statusFilter.addEventListener('change', () => loadLogs(1));
        });

        // Developer Logs UI Scripts
        let currentLogPage = 1;
        let totalLogPages = 1;

        async function loadLogs(page) {
            currentLogPage = page;
            const search = document.getElementById('logFilterSearch').value;
            const endpoint = document.getElementById('logFilterEndpoint').value;
            const status = document.getElementById('logFilterStatus').value;
            
            const tbody = document.getElementById('logsTableBody');
            
            // Prevent layout shift: if we already have rows, just fade them out instead of emptying the table
            if (tbody.children.length > 0 && tbody.children[0].classList.contains('log-row')) {
                tbody.style.opacity = '0.5';
                tbody.style.pointerEvents = 'none';
            } else {
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 40px; color: #64748b; font-weight: 600;">Cargando registros...</td></tr>';
            }
            
            document.getElementById('logBtnPrev').disabled = true;
            document.getElementById('logBtnNext').disabled = true;
            if (document.getElementById('logBtnPrevTop')) document.getElementById('logBtnPrevTop').disabled = true;
            if (document.getElementById('logBtnNextTop')) document.getElementById('logBtnNextTop').disabled = true;
            
            try {
                const url = new URL('<?= site_url("consumption/logs_ajax") ?>', window.location.origin);
                url.searchParams.append('page', page);
                url.searchParams.append('limit', 15);
                if (search) url.searchParams.append('search', search);
                if (endpoint) url.searchParams.append('endpoint', endpoint);
                if (status) url.searchParams.append('status', status);

                const response = await fetch(url);
                const result = await response.json();
                
                // Restore opacity in case we faded the table
                tbody.style.opacity = '1';
                tbody.style.pointerEvents = 'auto';

                if (result.success) {
                    totalLogPages = result.pagination.total_pages;
                    const infoText = `Mostrando página ${currentLogPage} de ${totalLogPages} (${result.pagination.total_records} registros)`;
                    document.getElementById('logPaginationInfo').innerText = infoText;
                    if (document.getElementById('logPaginationInfoTop')) document.getElementById('logPaginationInfoTop').innerText = infoText;
                    
                    const isPrevDisabled = currentLogPage <= 1;
                    const isNextDisabled = currentLogPage >= totalLogPages;

                    document.getElementById('logBtnPrev').disabled = isPrevDisabled;
                    document.getElementById('logBtnNext').disabled = isNextDisabled;
                    if (document.getElementById('logBtnPrevTop')) document.getElementById('logBtnPrevTop').disabled = isPrevDisabled;
                    if (document.getElementById('logBtnNextTop')) document.getElementById('logBtnNextTop').disabled = isNextDisabled;

                    tbody.innerHTML = '';
                    if (result.data.length === 0) {
                        tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; opacity: 0.6; padding: 24px; font-weight: 700;">No hay resultados con estos filtros.</td></tr>';
                        return;
                    }

                    result.data.forEach(req => {
                        const statusColor = req.status_code == 200 ? '#10b981' : '#e11d48';
                        const isFree = req.plan_name && req.plan_name.toLowerCase().includes('free');
                        const planBg = isFree ? '#f1f5f9' : '#eff6ff';
                        const planColor = isFree ? '#475569' : '#2563eb';
                        const planBorder = isFree ? '#e2e8f0' : '#bfdbfe';
                        
                        const tr = document.createElement('tr');
                        tr.className = 'log-row';
                        tr.style.cursor = 'pointer';
                        tr.style.transition = 'background 0.2s';
                        
                        // dataset values for slideover
                        tr.dataset.reqid = req.request_id || '--';
                        tr.dataset.ip = req.ip_address || '--';
                        tr.dataset.ua = req.user_agent || '--';
                        tr.dataset.method = req.http_method || 'GET';
                        tr.dataset.endpoint = req.endpoint || '--';
                        tr.dataset.status = req.status_code || '';
                        tr.dataset.latency = req.duration_ms || '0';
                        tr.dataset.date = req.date_display || '--';
                        tr.dataset.search = req.search_term || '--';
                        tr.onclick = function() { openLogDetails(this); };

                        tr.innerHTML = `
                            <td style="font-size: 0.85rem; color: #64748b; font-weight: 600; white-space: nowrap;">${req.date_display}</td>
                            <td><span class="usage-pill">${req.short_endpoint}</span></td>
                            <td style="font-weight: 800; color: #1e293b;">${req.search_term || '--'}</td>
                            <td style="color: ${statusColor}; font-weight: 900;">${req.status_code} <br><span style="font-size:0.7rem; color:#94a3b8; font-weight:600;">(${req.duration_ms}ms)</span></td>
                            <td>
                                <span style="background: ${planBg}; color: ${planColor}; padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; border: 1px solid ${planBorder}; white-space: nowrap;">
                                    ${req.plan_name || '--'}
                                </span>
                            </td>
                        `;
                        tbody.appendChild(tr);
                    });
                } else {
                    console.error("Backend returned:", result);
                    tbody.style.opacity = '1';
                    tbody.style.pointerEvents = 'auto';
                    const errMsg = result.message || result.error || 'Error desconocido en el servidor';
                    tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 24px; color: #ef4444; font-weight: 700;">Error del servidor: ' + errMsg + '</td></tr>';
                }
            } catch (err) {
                console.error("Fetch error:", err);
                tbody.style.opacity = '1';
                tbody.style.pointerEvents = 'auto';
                tbody.innerHTML = '<tr><td colspan="5" style="text-align: center; padding: 24px; color: #ef4444; font-weight: 700;">Error de red al cargar registros.</td></tr>';
            }
        }

        function changeLogPage(dir) {
            let newPage = currentLogPage + dir;
            if (newPage < 1) newPage = 1;
            if (newPage > totalLogPages) newPage = totalLogPages;
            if (newPage !== currentLogPage) {
                loadLogs(newPage).then(() => {
                    const tableContainer = document.getElementById('logsTableContainer');
                    if (tableContainer) {
                        const y = tableContainer.getBoundingClientRect().top + window.scrollY - 100;
                        window.scrollTo({ top: y, behavior: 'smooth' });
                    }
                });
            }
        }
        async function openLogDetails(row) {
            const reqId = row.dataset.reqid;
            
            // Abrir el panel inmediatamente con estado de carga
            document.getElementById('logSlideover').classList.add('active');
            document.getElementById('logModalReqId').innerText = reqId;
            
            const searchEl = document.getElementById('logModalSearch');
            if (searchEl) searchEl.innerText = 'Cargando...';

            try {
                const response = await fetch('<?= site_url("consumption/request") ?>/' + reqId);
                const result = await response.json();
                
                if (result.success) {
                    const data = result.data;
                    document.getElementById('logModalIp').innerText = data.ip_address || '--';
                    document.getElementById('logModalUa').innerText = data.user_agent || '--';
                    document.getElementById('logModalEndpoint').innerText = data.endpoint;
                    document.getElementById('logModalLatency').innerText = data.duration_ms + ' ms';
                    
                    const dt = new Date(data.created_at);
                    document.getElementById('logModalDate').innerText = dt.toLocaleString('es-ES');
                    
                    if (searchEl) searchEl.innerText = data.search_term || '--';

                    const method = data.http_method || 'GET';
                    const status = parseInt(data.status_code);

                    // Update existing nodes (if user moved them to header or elsewhere)
                    const existingMethod = document.querySelectorAll('[id="logModalMethod"], .log-modal-method-text');
                    existingMethod.forEach(n => { n.innerText = method; n.style.display = 'inline-block'; });

                    const existingStatus = document.querySelectorAll('[id="logModalStatus"], .log-modal-status-text');
                    existingStatus.forEach(n => {
                        n.innerText = status;
                        n.className = 'badge ' + (status === 200 ? 'success' : 'error') + ' log-modal-status-text';
                    });

                    // Bulletproof: Inject into the grid directly by finding the labels
                    const metaLabels = document.querySelectorAll('.log-meta label');
                    metaLabels.forEach(label => {
                        const text = label.innerText.toUpperCase();
                        if (text.includes('MÉTODO') || text.includes('METODO')) {
                            let container = label.parentElement;
                            let span = container.querySelector('span');
                            if (!span) {
                                span = document.createElement('span');
                                span.className = 'badge badge-method log-modal-method-text';
                                container.appendChild(span);
                            }
                            span.innerText = method;
                            span.style.display = 'inline-block';
                        }
                        if (text.includes('ESTADO HTTP')) {
                            let container = label.parentElement;
                            let span = container.querySelector('span');
                            if (!span) {
                                span = document.createElement('span');
                                container.appendChild(span);
                            }
                            span.innerText = status;
                            span.className = 'badge ' + (status === 200 ? 'success' : 'error') + ' log-modal-status-text';
                            span.style.display = 'inline-flex';
                        }
                    });

                    const noticeEl = document.querySelector('.log-notice');
                    if (status === 200) {
                         noticeEl.style.display = 'none';
                    } else {
                         noticeEl.style.display = 'flex';
                         noticeEl.style.background = '#fee2e2';
                         noticeEl.style.borderColor = '#ef4444';
                         let icon = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2" style="margin-top:2px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`;
                         let msg = '';
                         if (status == 400) {
                             msg = `<span style="color:#991b1b;"><b>Error 400 (Bad Request):</b> Has enviado parámetros incorrectos o el formato del CIF es inválido. Comprueba que estás enviando los datos exactamente como pide la documentación.</span>`;
                         } else if (status == 401 || status == 403) {
                             msg = `<span style="color:#991b1b;"><b>Error ` + status + ` (Unauthorized):</b> La API Key proporcionada no es válida o tu plan actual no permite el acceso a este endpoint.</span>`;
                         } else if (status == 404) {
                             noticeEl.style.background = '#fef3c7';
                             noticeEl.style.borderColor = '#f59e0b';
                             icon = `<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" style="margin-top:2px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>`;
                             msg = `<span style="color:#b45309;"><b>Error 404 (Not Found):</b> La llamada es correcta, pero no hemos encontrado ninguna empresa en la base de datos con ese CIF o término de búsqueda.</span>`;
                         } else if (status == 429) {
                             msg = `<span style="color:#991b1b;"><b>Error 429 (Too Many Requests):</b> Has superado tu límite de peticiones por segundo o te has quedado sin cuota mensual/saldo en el monedero.</span>`;
                         } else if (status >= 500) {
                             msg = `<span style="color:#991b1b;"><b>Error ` + status + ` (Server Error):</b> Ha ocurrido un problema interno en nuestros servidores. Si persiste, contacta con soporte indicando el Request ID.</span>`;
                         } else {
                             msg = `<span style="color:#991b1b;"><b>Error HTTP ` + status + `:</b> La petición ha fallado. Revisa los parámetros enviados.</span>`;
                         }
                         
                         msg += `<br><a href="<?= site_url('tickets') ?>" style="display: inline-block; margin-top: 6px; font-weight: 800; text-decoration: underline; color: inherit; opacity: 0.8; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.8'">¿Crees que es un fallo nuestro? Abre un ticket indicando este Request ID</a>`;
                         
                         noticeEl.innerHTML = icon + '<div style="display: flex; flex-direction: column;">' + msg + '</div>';
                    }
                } else {
                    console.error("No se encontraron los datos para la petición.");
                }
            } catch (error) {
                console.error("Error cargando los detalles:", error);
            }
        }

        function closeLogDetails() {
            document.getElementById('logSlideover').classList.remove('active');
        }

        function copyReqId() {
            const reqId = document.getElementById('logModalReqId').innerText;
            navigator.clipboard.writeText(reqId).then(() => {
                const btn = document.querySelector('.copy-box button');
                const origHTML = btn.innerHTML;
                btn.innerHTML = '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>';
                setTimeout(() => { btn.innerHTML = origHTML; }, 2000);
            });
        }

        function toggleErrorsOnly(showErrors) {
            const rows = document.querySelectorAll('.log-row');
            rows.forEach(row => {
                if (showErrors) {
                    if (row.dataset.status == 200) row.style.display = 'none';
                    else row.style.display = '';
                } else {
                    row.style.display = '';
                }
            });
        }
    </script>

</body>

</html>