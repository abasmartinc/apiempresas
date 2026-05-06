<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/usage.css') ?>" />
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

    <main class="usage-main">
        <div class="container">

            <div class="usage-header">
                <div>
                    <h1>Uso de la API</h1>
                    <p style="font-weight: 700; color: <?= $isVeryLowUsage ? '#64748b' : esc($stateColor) ?>; margin-top: 8px;">
                        <?php if ($isVeryLowUsage): ?>
                            ✨ Explorando la API: Fase de pruebas activa<br>
                            <span style="font-weight: 400; opacity: 0.85;">
                                Disfruta de tus consultas gratuitas para validar tu integración
                            </span>
                        <?php else: ?>
                            ⚠️ Estás usando la API en condiciones reales<br>
                            <span style="font-weight: 400; opacity: 0.85;">
                                Asegura continuidad antes de integrarla en producción
                            </span>
                        <?php endif; ?>
                    </p>
                </div>

                <form class="usage-filters" method="get" action="<?= current_url() ?>">
                    <div>
                        <label for="range">Rango</label><br>
                        <select id="range" name="range">
                            <option value="30" <?= (($_GET['range'] ?? '') === '30') ? 'selected' : '' ?>>Últimos 30 días
                            </option>
                            <option value="7" <?= (($_GET['range'] ?? '') === '7') ? 'selected' : '' ?>>Últimos 7 días
                            </option>
                            <option value="today" <?= (($_GET['range'] ?? '') === 'today') ? 'selected' : '' ?>>Hoy
                            </option>
                            <option value="custom" <?= (($_GET['range'] ?? '') === 'custom') ? 'selected' : '' ?>>
                                Personalizado…</option>
                        </select>
                    </div>

                    <div>
                        <label for="from">Desde</label><br>
                        <input type="date" id="from" name="from" value="<?= esc($_GET['from'] ?? '') ?>">
                    </div>

                    <div>
                        <label for="to">Hasta</label><br>
                        <input type="date" id="to" name="to" value="<?= esc($_GET['to'] ?? '') ?>">
                    </div>

                    <div>
                        <label>&nbsp;</label><br>
                        <button type="submit" class="btn secondary">Actualizar</button>
                    </div>
                </form>
            </div>

            <div class="ve-stat-strip">
                <div class="ve-stat">
                    <div class="ve-stat__label">Consultas en este mes</div>
                    <div class="ve-stat__value"><?= $fmt($usedThisMonth) ?></div>
                </div>

                <div class="ve-stat__divider"></div>

                <div class="ve-stat">
                    <div class="ve-stat__label">Consultas hoy</div>
                    <div class="ve-stat__value"><?= $usedToday !== null ? $fmt($usedToday) : '—' ?></div>
                </div>

                <div class="ve-stat__divider"></div>

                <div class="ve-stat ve-stat--sources">
                    <div class="ve-stat__label">Estado de uso</div>
                    <div class="ve-stat__value" style="color: <?= esc($stateColor) ?>;">
                        <?php if ($monthlyQuotaInt !== null): ?>
                            Has usado <?= $fmt($usedThisMonth) ?> de <?= $fmt($monthlyQuotaInt) ?> consultas gratuitas
                        <?php else: ?>
                            Uso no disponible
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="usage-layout">

                <section class="usage-card">
                    <h2>Consultas por día</h2>
                    <p>Distribución del número de consultas realizadas en el periodo seleccionado.</p>

                    <div class="chart-wrapper">
                        <canvas id="usageChart" height="110"></canvas>
                    </div>

                    <div class="usage-table-wrapper">
                        <table class="usage-table">
                            <thead>
                                <tr>
                                    <th>Endpoint</th>
                                    <th>Este mes</th>
                                    <th>% de uso</th>
                                    <th>Hoy</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($endpoint_breakdown)): ?>
                                    <?php foreach ($endpoint_breakdown as $ep): ?>
                                        <tr>
                                            <td><span class="usage-pill"><?= esc($ep['endpoint']) ?></span></td>
                                            <td><?= $fmt($ep['total']) ?></td>
                                            <td><?= $monthlyQuotaInt ? round(($ep['total'] / $monthlyQuotaInt) * 100) . '%' : '—' ?>
                                            </td>
                                            <td><?= $fmt($ep['today'] ?? 0) ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="4" style="text-align: center; opacity: 0.6; padding: 20px;">
                                            No hay datos de endpoints para este periodo
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="usage-card">
                    <h2>Uso de tu plan</h2>
                    <p>
                        <?php if ($isVeryLowUsage): ?>
                            Fase inicial de descubrimiento activa<br>
                            Descubre el potencial de los datos oficiales
                        <?php else: ?>
                            Estás empezando a usar la API en condiciones reales<br>
                            Evita quedarte sin acceso cuando más la necesites
                        <?php endif; ?>
                    </p>

                    <div class="limit-block">
                        <h3>Uso de tu plan</h3>

                        <p style="margin-bottom:6px;">
                            Plan <strong><?= esc($planName) ?></strong>

                            <?php if ($subStatus): ?>
                                — <span
                                    class="status-badge status-badge--<?= $subStatus === 'active' ? 'active' : 'inactive' ?>">
                                    <?= esc(ucfirst($subStatus)) ?>
                                </span>
                            <?php endif; ?>

                            <br>

                            <?php if ($periodStartFmt && $periodEndFmt): ?>
                                <span style="opacity:.85;">Periodo: <?= esc($periodStartFmt) ?> →
                                    <?= esc($periodEndFmt) ?></span><br>
                            <?php endif; ?>

                            <?php if ($monthlyQuotaInt !== null): ?>
                                <span><?= $fmt($monthlyQuotaInt) ?> consultas gratuitas incluidas.</span>
                            <?php else: ?>
                                <span>Límite no disponible.</span>
                            <?php endif; ?>

                            <br>
                            <span style="font-size: 11px; opacity: 0.75;">
                                ⚡ Uso típico antes de pasar a Pro: 5–10 consultas
                            </span>
                        </p>

                        <div class="limit-bar">
                            <div class="limit-bar-inner"
                                style="width: <?= (int) $barWidth ?>%; background: <?= esc($stateColor) ?>;"></div>
                        </div>

                        <div class="limit-meta">
                            <?php if ($monthlyQuotaInt !== null): ?>
                                <span style="font-weight: 700; color: <?= esc($stateColor) ?>;">
                                    Has usado <?= $fmt($usedThisMonth) ?> de <?= $fmt($monthlyQuotaInt) ?> consultas
                                    gratuitas
                                </span>
                                <span>
                                    Te quedan <?= $fmt($remaining) ?> antes de que el acceso se limite
                                </span>
                            <?php else: ?>
                                <span style="font-weight: 700; color: <?= esc($stateColor) ?>;">
                                    Uso del plan no disponible
                                </span>
                                <span>
                                    Revisa tu suscripción o contacta con soporte.
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <?php if (!$isVeryLowUsage && $usageRatio > 0.2): ?>
                    <div class="limit-block" style="margin-top:14px;">
                        <h3 style="color: #ef4444;">Presión de uso</h3>
                        <p style="font-weight: 600;">
                            Si sigues usando la API así, te quedarás sin acceso antes de lo esperado
                        </p>
                        <p style="font-size: 12px; margin-bottom: 0;">
                            Evita que tu sistema deje de validar empresas en producción
                        </p>
                    </div>
                    <?php endif; ?>

                    <div class="limit-block" style="margin-top:14px;">
                        <h3>Estado del sistema</h3>
                        <ul class="alert-list">
                            <li class="info" style="border-left: 4px solid <?= esc($stateColor) ?>;">
                                <span class="badge-dot" style="background: <?= esc($stateColor) ?>;"></span>
                                <div>
                                    <?php if ($isVeryLowUsage): ?>
                                        <strong>Sistema listo para producción</strong><br>
                                        Tu integración está respondiendo correctamente.
                                    <?php else: ?>
                                        <strong>Tu uso ya está entrando en fase real</strong><br>
                                        Es el punto en el que la mayoría de usuarios activan Pro.
                                    <?php endif; ?>
                                </div>
                            </li>
                        </ul>
                    </div>

                    <div class="limit-block"
                        style="margin-top:14px; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-color: #bfdbfe;">
                        <h3>Evita interrupciones</h3>

                        <p>
                            Activa Pro antes de llegar al límite y mantén tus validaciones funcionando sin cortes.
                        </p>

                        <a href="<?= site_url('billing') ?>" class="btn-upgrade-track btn secondary"
                            style="margin-top:6px; width: 100%; text-align: center; background: <?= esc($stateColor) ?>; color: #fff; border: none; font-weight: 800;">
                            Activar Pro y evitar quedarte sin acceso
                        </a>

                        <div
                            style="margin-top: 8px; text-align: center; font-size: 11px; color: #64748b; font-weight: 600;">
                            Empieza a usar la API sin límites antes de integrarla en tu sistema
                        </div>

                        <div style="margin-top: 15px; font-size: 13px; line-height: 1.6;">
                            <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 4px;">
                                <span style="color: #10B981;">✔</span> Sigue validando empresas sin restricciones
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center; margin-bottom: 4px;">
                                <span style="color: #10B981;">✔</span> Automatiza procesos en producción
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                <span style="color: #10B981;">✔</span> Evita interrupciones en tu sistema
                            </div>
                        </div>
                    </div>

                </section>
            </div>

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