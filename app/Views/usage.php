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
// ===== Helpers defensivos (array u objeto) =====
$get = function ($src, string $key, $default = null) {
    if (is_array($src)) return $src[$key] ?? $default;
    if (is_object($src)) return $src->$key ?? $default;
    return $default;
};

$fmt = function ($n) {
    return number_format((int)$n, 0, ',', '.');
};

$formatDateTime = function ($dt) {
    if (!$dt) return null;
    $ts = strtotime((string)$dt);
    if (!$ts) return null;
    return date('d/m/Y H:i', $ts);
};

// ===== Datos principales =====
$userName = esc($get($user, 'name', ''));
$apiKey   = $get($api_key, 'api_key', null);

// En tu BD:
// api_plans: name, monthly_quota
// user_subscriptions: status, current_period_start, current_period_end
$planName      = $get($plan, 'name', '—');
$monthlyQuota  = $get($plan, 'monthly_quota', null);

$subStatus     = $get($plan, 'status', null);
$periodStart   = $get($plan, 'current_period_start', null);
$periodEnd     = $get($plan, 'current_period_end', null);

$periodStartFmt = $formatDateTime($periodStart);
$periodEndFmt   = $formatDateTime($periodEnd);

// ===== Consumos =====
$usedThisMonth = (int)($api_request_total_month ?? 0);
$usedToday     = isset($api_request_total_today) ? (int)$api_request_total_today : null;

// ===== Normalización cuota (por seguridad) =====
$monthlyQuotaRaw = $monthlyQuota;

if (is_string($monthlyQuotaRaw)) {
    $monthlyQuotaRaw = trim($monthlyQuotaRaw);
    $monthlyQuotaRaw = str_replace(['.', ',', ' '], '', $monthlyQuotaRaw);
}

$monthlyQuotaInt = (is_numeric($monthlyQuotaRaw) && (int)$monthlyQuotaRaw > 0)
    ? (int)$monthlyQuotaRaw
    : null;

// ===== Cálculos cuota =====
$percent = null;
$remaining = null;

if ($monthlyQuotaInt !== null) {
    $percent = (int) round(($usedThisMonth / $monthlyQuotaInt) * 100);
    $percent = max(0, min(999, $percent));
    $remaining = max(0, $monthlyQuotaInt - $usedThisMonth);
}

$barWidth = $percent !== null ? max(0, min(100, $percent)) : 0;
?>

<main class="usage-main">
    <div class="container">

        <!-- CABECERA -->
        <div class="usage-header">
            <div>
                <h1>Uso de la API</h1>
                <p>Revisa tus consultas y controla cuánto estás consumiendo en cada periodo.</p>
            </div>

            <form class="usage-filters" method="get" action="<?= current_url() ?>">
                <div>
                    <label for="range">Rango</label><br>
                    <select id="range" name="range">
                        <option value="30" <?= (($_GET['range'] ?? '') === '30') ? 'selected' : '' ?>>Últimos 30 días</option>
                        <option value="7" <?= (($_GET['range'] ?? '') === '7') ? 'selected' : '' ?>>Últimos 7 días</option>
                        <option value="today" <?= (($_GET['range'] ?? '') === 'today') ? 'selected' : '' ?>>Hoy</option>
                        <option value="custom" <?= (($_GET['range'] ?? '') === 'custom') ? 'selected' : '' ?>>Personalizado…</option>
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

        <!-- FRANJA DE KPIs -->
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
                <div class="ve-stat__label">Porcentaje de tu límite</div>
                <div class="ve-stat__value">
                    <?php if ($monthlyQuotaInt !== null): ?>
                        <?= (int)$percent ?>% (<?= $fmt($usedThisMonth) ?> / <?= $fmt($monthlyQuotaInt) ?>)
                    <?php else: ?>
                        —
                    <?php endif; ?>
                </div>
            </div>


        </div>

        <!-- LAYOUT PRINCIPAL -->
        <div class="usage-layout">
            <!-- IZQUIERDA -->
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
                            <th>% del plan</th>
                            <th>Hoy</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td><span class="usage-pill">/api/v1/companies</span></td>
                            <td><?= $fmt($usedThisMonth) ?></td>
                            <td><?= $percent !== null ? ($percent . '%') : '—' ?></td>
                            <td><?= $usedToday !== null ? $fmt($usedToday) : '—' ?></td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- DERECHA -->
            <section class="usage-card">
                <h2>Límites de tu plan</h2>
                <p>Monitoriza cómo vas respecto al límite de consultas incluido en tu suscripción.</p>

                <div class="limit-block">
                    <h3>Uso del plan actual</h3>

                    <p style="margin-bottom:6px;">
                        Plan <strong><?= esc($planName) ?></strong>
                        <?php if ($subStatus): ?>
                            — <span style="opacity:.8;"><?= esc($subStatus) ?></span>
                        <?php endif; ?>
                        <br>

                        <?php if ($periodStartFmt && $periodEndFmt): ?>
                            <span style="opacity:.85;">Periodo: <?= esc($periodStartFmt) ?> → <?= esc($periodEndFmt) ?></span><br>
                        <?php endif; ?>

                        <?php if ($monthlyQuotaInt !== null): ?>
                            <span><?= $fmt($monthlyQuotaInt) ?> consultas/mes incluidas.</span>
                        <?php else: ?>
                            <span>límite no disponible.</span>
                        <?php endif; ?>
                    </p>

                    <div class="limit-bar">
                        <div class="limit-bar-inner" style="width: <?= (int)$barWidth ?>%;"></div>
                    </div>

                    <div class="limit-meta">
                        <span><?= $fmt($usedThisMonth) ?> usadas este mes</span>
                        <span>
                            <?php if ($remaining !== null): ?>
                                Quedan <?= $fmt($remaining) ?> consultas
                            <?php else: ?>
                                Quedan —
                            <?php endif; ?>
                        </span>
                    </div>
                </div>

                <div class="limit-block" style="margin-top:14px;">
                    <h3>Alertas</h3>
                    <ul class="alert-list">
                        <?php if ($percent !== null && $percent >= 80): ?>
                            <li>
                                <span class="badge-dot"></span>
                                <div>
                                    <strong>Vas por el <?= $percent ?>% de tu cuota mensual.</strong><br>
                                    Si sigues a este ritmo, podrías llegar al límite antes de fin de mes.
                                </div>
                            </li>
                        <?php elseif ($percent !== null && $percent >= 60): ?>
                            <li>
                                <span class="badge-dot"></span>
                                <div>
                                    <strong>Has superado el 60% de tu límite mensual.</strong><br>
                                    Te avisaremos al pasar el 80% para que puedas ampliar antes de llegar al tope.
                                </div>
                            </li>
                        <?php else: ?>
                            <li class="info">
                                <span class="badge-dot"></span>
                                <div>
                                    <strong>No tienes alertas críticas.</strong><br>
                                    Si llegas al 100% de tu cuota, las nuevas consultas se gestionarán según tu plan.
                                </div>
                            </li>
                        <?php endif; ?>
                    </ul>
                </div>


                <div class="limit-block" style="margin-top:14px;">
                    <h3>Subir de plan</h3>
                    <p>
                        Si sueles estar por encima del 70–80% de tu cuota, suele salir más rentable subir al siguiente
                        plan en lugar de pagar exceso por consulta.
                    </p>
                    <a href="<?= site_url('billing') ?>" class="btn secondary" style="margin-top:6px;">
                        Cambiar de plan
                    </a>
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

        // Labels más legibles: "03/01"
        const prettyLabels = labels.map(d => {
            const parts = d.split('-'); // YYYY-MM-DD
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
</script>

</body>
</html>
