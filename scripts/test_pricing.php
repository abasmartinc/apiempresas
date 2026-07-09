<?php
// Quick price test
define('APPPATH', __DIR__ . '/../app/');
require_once __DIR__ . '/../app/Helpers/pricing_helper.php';

$tests = [
    1000 => 'small (1k)',
    10000 => 'medium (10k)',
    50000 => 'large (50k)',
    200000 => 'very large (200k)',
    500000 => 'very large (500k)',
    600000 => 'Barcelona (600k)',
];

echo "=== DIRECTORY PRICING ===\n";
foreach ($tests as $count => $label) {
    $p = calculate_directory_price($count);
    echo sprintf("%-25s: base=%s€, original=%s€, capped=%s\n",
        $label,
        number_format($p['base_price'], 2),
        number_format($p['original_price'], 2),
        $p['is_discounted'] ? 'YES' : 'no'
    );
}

echo "\n=== RADAR PRICING (premium x1.5) ===\n";
foreach ($tests as $count => $label) {
    $p = calculate_radar_price($count);
    echo sprintf("%-25s: base=%s€, original=%s€, capped=%s\n",
        $label,
        number_format($p['base_price'], 2),
        number_format($p['original_price'], 2),
        $p['is_discounted'] ? 'YES' : 'no'
    );
}
