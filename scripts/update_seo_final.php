<?php
$files = [
    'hub_subvenciones.php',
    'listado_ano_contratos.php',
    'listado_ano_subvenciones.php',
    'listado_convocatoria.php',
    'listado_organo.php',
    'ranking_contratistas.php'
];

foreach ($files as $f) {
    $path = 'app/Views/seo/' . $f;
    $c = file_get_contents($path);
    $orig = $c;

    $vars = ['$total', '$total_subs', '$total_contracts'];
    foreach ($vars as $var) {
        $needle = '$dynamic_price = $billingService->calculatePublicFundsPrice(' . $var . ');';
        $repl = '$pricing = $billingService->getPublicFundsPricingDetails(' . $var . ');' . "\n" .
                '                    $dynamic_price = $pricing[\'base_price\'];';
        $c = str_replace($needle, $repl, $c);
    }

    // Fix download buttons
    $discountSnippet = '<?php if(isset($pricing) && $pricing[\'is_discounted\']): ?>' .
                       '<s style="opacity:0.7; font-size:0.9em; margin-right:6px;">' .
                       '<?= number_format($pricing[\'original_price\'], 2, \',\', \'\') ?>€</s>' .
                       '<?php endif; ?>';

    $patterns = [
        'Descargar CSV Completo — ',
        'Descargar CSV Completo (<?= $year ?>) — ',
        'Descargar BBDD Completa de Subvenciones — ',
        'Descargar BBDD Completa de Contratos — ',
    ];
    $tail = '<?= number_format($dynamic_price, 2, \',\', \'\') ?>€ <span style="font-size: 0.85em; opacity: 0.9; font-weight: 600;">+ IVA</span>';

    foreach ($patterns as $p) {
        $needle = $p . $tail;
        $repl = $p . $discountSnippet . $tail;
        $c = str_replace($needle, $repl, $c);
    }

    if ($c !== $orig) {
        file_put_contents($path, $c);
        echo 'Updated: ' . $f . "\n";
    }
}
echo "Done.\n";
