<?php
/**
 * Direct binary-safe string replacement script for SEO views
 * Replaces all calculatePublicFundsPrice calls and Descargar CSV Completo buttons
 */
$files = glob(__DIR__ . '/app/Views/seo/*.php');
$updated = 0;

foreach ($files as $file) {
    $content = file_get_contents($file);
    $original = $content;

    // Fix: $total variable
    $content = str_replace(
        '$dynamic_price = $billingService->calculatePublicFundsPrice($total);',
        "\$pricing = \$billingService->getPublicFundsPricingDetails(\$total);\n                    \$dynamic_price = \$pricing['base_price'];",
        $content
    );

    // Fix: $total_subs variable
    $content = str_replace(
        '$dynamic_price = $billingService->calculatePublicFundsPrice($total_subs);',
        "\$pricing = \$billingService->getPublicFundsPricingDetails(\$total_subs);\n                    \$dynamic_price = \$pricing['base_price'];",
        $content
    );

    // Fix: $total_contracts variable
    $content = str_replace(
        '$dynamic_price = $billingService->calculatePublicFundsPrice($total_contracts);',
        "\$pricing = \$billingService->getPublicFundsPricingDetails(\$total_contracts);\n                    \$dynamic_price = \$pricing['base_price'];",
        $content
    );

    // Now fix the download buttons - find all occurrences of price in CSV download links
    // Pattern: Descargar CSV Completo — <?= number_format($dynamic_price, 2, ',', '') ?>€ ...
    $discount_check = "<?php if(isset(\$pricing) && \$pricing['is_discounted']): ?><s style=\"opacity:0.7; font-size:0.9em; margin-right:6px;\"><?= number_format(\$pricing['original_price'], 2, ',', '') ?>€</s><?php endif; ?>";
    
    // Replace button text — pattern with + IVA
    $content = str_replace(
        "Descargar CSV Completo — <?= number_format(\$dynamic_price, 2, ',', '') ?>€ <span style=\"font-size: 0.85em; opacity: 0.9; font-weight: 600;\">+ IVA</span>",
        "Descargar CSV Completo — {$discount_check}<?= number_format(\$dynamic_price, 2, ',', '') ?>€ <span style=\"font-size: 0.85em; opacity: 0.9; font-weight: 600;\">+ IVA</span>",
        $content
    );

    // Replace button text with year — pattern with + IVA  
    $content = str_replace(
        "Descargar CSV Completo (<?= \$year ?>) — <?= number_format(\$dynamic_price, 2, ',', '') ?>€ <span style=\"font-size: 0.85em; opacity: 0.9; font-weight: 600;\">+ IVA</span>",
        "Descargar CSV Completo (<?= \$year ?>) — {$discount_check}<?= number_format(\$dynamic_price, 2, ',', '') ?>€ <span style=\"font-size: 0.85em; opacity: 0.9; font-weight: 600;\">+ IVA</span>",
        $content
    );

    // Also fix Descargar BBDD labels (ranking pages)
    $content = str_replace(
        "Descargar BBDD Completa de Subvenciones — <?= number_format(\$dynamic_price, 2, ',', '') ?>€ <span style=\"font-size: 0.85em; opacity: 0.9; font-weight: 600;\">+ IVA</span>",
        "Descargar BBDD Completa de Subvenciones — {$discount_check}<?= number_format(\$dynamic_price, 2, ',', '') ?>€ <span style=\"font-size: 0.85em; opacity: 0.9; font-weight: 600;\">+ IVA</span>",
        $content
    );
    $content = str_replace(
        "Descargar BBDD Completa de Contratos — <?= number_format(\$dynamic_price, 2, ',', '') ?>€ <span style=\"font-size: 0.85em; opacity: 0.9; font-weight: 600;\">+ IVA</span>",
        "Descargar BBDD Completa de Contratos — {$discount_check}<?= number_format(\$dynamic_price, 2, ',', '') ?>€ <span style=\"font-size: 0.85em; opacity: 0.9; font-weight: 600;\">+ IVA</span>",
        $content
    );

    if ($original !== $content) {
        file_put_contents($file, $content);
        echo "Updated: " . basename($file) . "\n";
        $updated++;
    }
}
echo "Total files updated: $updated\n";
