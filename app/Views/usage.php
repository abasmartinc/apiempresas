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
                        <h2>Registro de Actividad Reciente</h2>
                        <p>Detalle de tus últimas llamadas a la API y el plan de origen.</p>
                        <div class="usage-table-wrapper" style="max-height: 520px; overflow-y: auto;">
                            <table class="usage-table">
                                <thead style="position: sticky; top: 0; background: #f8fafc; box-shadow: 0 1px 2px rgba(0,0,0,0.05); z-index: 10;">
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Endpoint</th>
                                        <th>Búsqueda</th>
                                        <th>Resp</th>
                                        <th>Origen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (!empty($recent_requests)): ?>
                                        <?php foreach ($recent_requests as $req): ?>
                                            <tr>
                                                <td style="font-size: 0.85rem; color: #64748b; font-weight: 600; white-space: nowrap;"><?= date('d/m/Y H:i', strtotime($req['created_at'])) ?></td>
                                                <td><span class="usage-pill"><?= esc(str_replace('/apiempresas/api/v1', '', $req['endpoint'])) ?></span></td>
                                                <td style="font-weight: 800; color: #1e293b;"><?= esc($req['search_term'] ?? '--') ?></td>
                                                <td style="color: <?= $req['status_code'] == 200 ? '#10b981' : '#e11d48' ?>; font-weight: 900;"><?= esc($req['status_code']) ?> <br><span style="font-size:0.7rem; color:#94a3b8; font-weight:600;">(<?= esc($req['duration_ms']) ?>ms)</span></td>
                                                <td>
                                                    <span style="background: <?= stripos($req['plan_name'], 'free') !== false ? '#f1f5f9' : '#eff6ff' ?>; color: <?= stripos($req['plan_name'], 'free') !== false ? '#475569' : '#2563eb' ?>; padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.05em; text-transform: uppercase; border: 1px solid <?= stripos($req['plan_name'], 'free') !== false ? '#e2e8f0' : '#bfdbfe' ?>; white-space: nowrap;">
                                                        <?= esc($req['plan_name']) ?>
                                                    </span>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="5" style="text-align: center; opacity: 0.6; padding: 24px; font-weight: 700;">
                                                No hay actividad reciente registrada.
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
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
                </aside>
            </div>
        </div>
    </main>>

        </div>
    </main>

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

        $(document).ready(function () {
            const trackingUrl = '<?= site_url("api/tracking/event") ?>';

            $.post(trackingUrl, {
                event_type: 'consumption_view',
                metadata: JSON.stringify({
                    used: <?= (int) $usedThisMonth ?>,
                    limit: <?= (int) ($monthlyQuotaInt ?? 0) ?>,
                    usage_ratio: <?= (float) $usageRatio ?>
                })
            });

            $('.btn-upgrade-track').on('click', function () {
                $.post(trackingUrl, {
                    event_type: 'consumption_upgrade_click',
                    metadata: JSON.stringify({
                        used: <?= (int) $usedThisMonth ?>,
                        limit: <?= (int) ($monthlyQuotaInt ?? 0) ?>,
                        usage_ratio: <?= (float) $usageRatio ?>,
                        cta_text: "Activar Pro y evitar quedarte sin acceso"
                    })
                });
            });
        });
    </script>

</body>

</html>