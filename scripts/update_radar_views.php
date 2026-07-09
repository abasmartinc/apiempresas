<?php
$radarViews = [
    'radar_companies_province.php',
    'radar_new_companies.php',
    'radar_new_companies_period.php',
    'radar_new_companies_province.php',
    'radar_new_companies_sector.php',
];

$discountSnippet = "<?php if(isset(\$dynamic_price['is_discounted']) && \$dynamic_price['is_discounted']): ?>"
                 . '<s style="opacity:0.65; font-size:0.85em; margin-right:4px;">'
                 . "<?= number_format(\$dynamic_price['original_price'], 0) ?>€</s>"
                 . "<?php endif; ?>";

foreach ($radarViews as $f) {
    $path = 'app/Views/seo/' . $f;
    if (!file_exists($path)) continue;
    
    $c = file_get_contents($path);
    $orig = $c;

    $patterns = [
        "<?= number_format(\$dynamic_price['base_price'] ?? 9, 0) ?>€",
        "<?= number_format(\$dynamic_price['base_price'] ?? 15, 0) ?>€",
    ];

    foreach ($patterns as $needle) {
        $repl = $discountSnippet . $needle;
        $c = str_replace($needle, $repl, $c);
    }

    if ($c !== $orig) {
        file_put_contents($path, $c);
        echo 'Updated: ' . $f . "\n";
    }
}
echo "Done.\n";
