<?php
$files = glob(__DIR__ . '/app/Views/seo/*.php');
$updated = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;

    // Fix price variable assignments (3 variations of count variable)
    $content = str_replace(
        '$dynamic_price = $billingService->calculatePublicFundsPrice($total);',
        '$pricing = $billingService->getPublicFundsPricingDetails($total);' . "\n" . '                    $dynamic_price = $pricing[\'base_price\'];',
        $content
    );
    $content = str_replace(
        '$dynamic_price = $billingService->calculatePublicFundsPrice($total_subs);',
        '$pricing = $billingService->getPublicFundsPricingDetails($total_subs);' . "\n" . '                    $dynamic_price = $pricing[\'base_price\'];',
        $content
    );
    $content = str_replace(
        '$dynamic_price = $billingService->calculatePublicFundsPrice($total_contracts);',
        '$pricing = $billingService->getPublicFundsPricingDetails($total_contracts);' . "\n" . '                    $dynamic_price = $pricing[\'base_price\'];',
        $content
    );

    // Build the discount prefix snippet (literal PHP code to insert)
    $prefix = '<?php if(isset($pricing) && $pricing[\'is_discounted\']): ?>' .
              '<s style="opacity:0.7; font-size:0.9em; margin-right:6px;">' .
              '<?= number_format($pricing[\'original_price\'], 2, \',\', \'\') ?>€</s>' .
              '<?php endif; ?>';

    $suffixes = [
        '€ <span style="font-size: 0.85em; opacity: 0.9; font-weight: 600;">+ IVA</span>'
    ];

    $prefixes_text = [
        'Descargar CSV Completo — ',
        'Descargar CSV Completo (<?= $year ?>) — ',
        'Descargar BBDD Completa de Subvenciones — ',
        'Descargar BBDD Completa de Contratos — ',
    ];

    $price_str = '<?= number_format($dynamic_price, 2, \',\', \'\') ?>';

    foreach ($prefixes_text as $pt) {
        foreach ($suffixes as $sf) {
            $needle = $pt . $price_str . $sf;
            $replacement = $pt . $prefix . $price_str . $sf;
            $content = str_replace($needle, $replacement, $content);
        }
    }

    if ($original !== $content) {
        file_put_contents($file, $content);
        echo 'Updated: ' . basename($file) . "\n";
        $updated++;
    }
}
echo 'Total files updated: ' . $updated . "\n";
