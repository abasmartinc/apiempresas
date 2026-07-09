<?php
$files = glob(__DIR__ . '/app/Views/seo/*.php');
foreach($files as $file) {
    $content = file_get_contents($file);
    $original = $content;

    // Fix price variable definition
    $patterns = [
        '$dynamic_price = $billingService->calculatePublicFundsPrice($total);',
        '$dynamic_price = $billingService->calculatePublicFundsPrice($total_subs);',
        '$dynamic_price = $billingService->calculatePublicFundsPrice($total_contracts);'
    ];
    $replacements = [
        '$pricing = $billingService->getPublicFundsPricingDetails($total); $dynamic_price = $pricing[\'base_price\'];',
        '$pricing = $billingService->getPublicFundsPricingDetails($total_subs); $dynamic_price = $pricing[\'base_price\'];',
        '$pricing = $billingService->getPublicFundsPricingDetails($total_contracts); $dynamic_price = $pricing[\'base_price\'];'
    ];
    $content = str_replace($patterns, $replacements, $content);

    // Fix download button (with year)
    $content = str_replace(
        'Descargar CSV Completo (<?= $year ?>) — <?= number_format($dynamic_price, 2, \',\', \'\') ?>€ <span style="font-size: 0.85em; opacity: 0.9; font-weight: 600;">+ IVA</span>',
        'Descargar CSV Completo (<?= $year ?>) — <?php if(isset($pricing) && $pricing[\'is_discounted\']): ?><s style="opacity:0.7; font-size:0.9em; margin-right:6px;"><?= number_format($pricing[\'original_price\'], 2, \',\', \'\') ?>€</s><?php endif; ?><?= number_format($dynamic_price, 2, \',\', \'\') ?>€ <span style="font-size: 0.85em; opacity: 0.9; font-weight: 600;">+ IVA</span>',
        $content
    );

    // Fix download button (without year)
    $content = str_replace(
        'Descargar CSV Completo — <?= number_format($dynamic_price, 2, \',\', \'\') ?>€ <span style="font-size: 0.85em; opacity: 0.9; font-weight: 600;">+ IVA</span>',
        'Descargar CSV Completo — <?php if(isset($pricing) && $pricing[\'is_discounted\']): ?><s style="opacity:0.7; font-size:0.9em; margin-right:6px;"><?= number_format($pricing[\'original_price\'], 2, \',\', \'\') ?>€</s><?php endif; ?><?= number_format($dynamic_price, 2, \',\', \'\') ?>€ <span style="font-size: 0.85em; opacity: 0.9; font-weight: 600;">+ IVA</span>',
        $content
    );

    if ($original !== $content) {
        file_put_contents($file, $content);
        echo "Updated $file\n";
    }
}
