<?php
/**
 * UI KPI Component
 * @param string $label The title/label of the KPI
 * @param string $value HTML or string for the value
 * @param string $meta HTML or string for the subtitle/meta text
 * @param string $icon SVG icon
 * @param string $theme default|success|warning|error
 * @param string $extraAttributes Optional extra attributes for the card (e.g. onclick, id)
 */
$label = $label ?? '';
$value = $value ?? '';
$meta = $meta ?? '';
$icon = $icon ?? '';
$theme = $theme ?? 'default';
$extraAttributes = $extraAttributes ?? '';

$themes = [
    'default' => ['bg' => '#f8faff', 'color' => '#2152ff', 'card_class' => ''],
    'success' => ['bg' => '#ecfdf5', 'color' => '#10b981', 'card_class' => ''],
    'warning' => ['bg' => '#fffbeb', 'color' => '#f59e0b', 'card_class' => 'kpi-card--warning'],
    'error'   => ['bg' => '#fff1f2', 'color' => '#e11d48', 'card_class' => ''],
    'cta'     => ['bg' => '#fef2f2', 'color' => '#ef4444', 'card_class' => 'kpi-card--cta'],
];

$t = $themes[$theme] ?? $themes['default'];
?>
<div class="kpi-card-pro <?= $t['card_class'] ?>" <?= $extraAttributes ?>>
    <div class="kpi-icon-box" style="background: <?= $t['bg'] ?>; color: <?= $t['color'] ?>;">
        <?= $icon ?>
    </div>
    <div class="kpi-content">
        <span class="label"><?= esc($label) ?></span>
        <div class="value">
            <?= $value ?>
        </div>
        <div class="meta"><?= $meta ?></div>
    </div>
</div>
