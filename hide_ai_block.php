<?php
$inputFile = __DIR__ . '/app/Views/company_en.php';
$content = file_get_contents($inputFile);

// The block to comment out starts with: <?php if (!empty($company['ai_seo_text'])): ? >
// And ends with: <?php endif; ? > just before <div class="company-share-row">

$startToken = "<?php if (!empty(\$company['ai_seo_text'])): ?>";
$endToken = "<?php endif; ?>\n\n                            <div class=\"company-share-row\">";

$posStart = strpos($content, $startToken);
$posEnd = strpos($content, $endToken);

if ($posStart !== false && $posEnd !== false) {
    // We want to effectively remove or hide it. 
    // Let's replace the whole block with nothing to make it clean.
    $replacement = "<!-- AI SEO Text Block Hidden for English Version -->\n                            <div class=\"company-share-row\">";
    $content = substr_replace($content, $replacement, $posStart, ($posEnd + strlen($endToken)) - $posStart);
    file_put_contents($inputFile, $content);
    echo "AI SEO block hidden.\n";
} else {
    echo "Could not find block bounds.\n";
}
