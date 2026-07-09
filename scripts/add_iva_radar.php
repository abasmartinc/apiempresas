<?php
$radarViews = [
    'radar_companies_province.php',
    'radar_new_companies.php',
    'radar_new_companies_period.php',
    'radar_new_companies_province.php',
    'radar_new_companies_sector.php',
];

$ivaLabel = ' <span style="font-size:0.85em; opacity:0.85; font-weight:600;">+ IVA</span>';

foreach ($radarViews as $f) {
    $path = 'app/Views/seo/' . $f;
    if (!file_exists($path)) continue;

    $c = file_get_contents($path);
    $orig = $c;

    $p1 = "<?= number_format(\$dynamic_price['base_price'] ?? 9, 0) ?>€";
    $p2 = "<?= number_format(\$dynamic_price['base_price'] ?? 15, 0) ?>€";

    $c = str_replace($p1, $p1 . $ivaLabel, $c);
    $c = str_replace($p2, $p2 . $ivaLabel, $c);

    if ($c !== $orig) {
        file_put_contents($path, $c);
        echo 'Updated: ' . $f . "\n";
    }
}
echo "Done.\n";
