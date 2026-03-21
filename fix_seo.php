<?php
$dir = __DIR__ . '/app/Views/seo/';
$files = [
    'radar_new_companies.php',
    'radar_new_companies_province.php',
    'radar_new_companies_sector.php',
    'radar_new_companies_period.php',
    'radar_companies_province.php'
];

foreach ($files as $filename) {
    $path = $dir . $filename;
    if (!file_exists($path)) {
        echo "File not found: $filename\n";
        continue;
    }
    
    $content = file_get_contents($path);
    
    // 1. Accessibility SVGs
    // Add aria-hidden="true" to SVGs that don't have it
    $content = preg_replace('/<svg (?!.*aria-hidden="true")(.*?)/i', '<svg aria-hidden="true" $1', $content);
    // Cleanup double aria-hidden just in case
    $content = preg_replace('/<svg aria-hidden="true" (.*?) aria-hidden="true"/i', '<svg aria-hidden="true" $1', $content);
    
    // 2. Add JSON-LD right before </main>
    if (strpos($content, 'application/ld+json') === false) {
        $jsonld = <<<LD
<?php
\$baseJsonUrl = rtrim(site_url('/'), '/');
?>
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@graph": [
    {
      "@type": "BreadcrumbList",
      "itemListElement": [
        {
          "@type": "ListItem",
          "position": 1,
          "name": "Inicio",
          "item": "<?= \$baseJsonUrl ?>"
        },
        {
          "@type": "ListItem",
          "position": 2,
          "name": "Nuevas Empresas",
          "item": "<?= \$baseJsonUrl . '/empresas-nuevas' ?>"
        },
        {
          "@type": "ListItem",
          "position": 3,
          "name": "<?= esc(\$title ?? 'Radar Comercial') ?>"
        }
      ]
    },
    {
      "@type": "Product",
      "name": "Listado Excel B2B - <?= esc(\$title ?? 'Directorio') ?>",
      "description": "Descarga directa del listado B2B formateado en Excel para prospección comercial.",
      "offers": {
        "@type": "Offer",
        "price": "<?= number_format(\$dynamic_price['base_price'] ?? 9, 2, '.', '') ?>",
        "priceCurrency": "EUR",
        "availability": "https://schema.org/InStock",
        "url": "<?= esc(\$canonical ?? '') ?>"
      }
    }
  ]
}
</script>
LD;
        $content = str_replace('</main>', $jsonld . "\n    </main>", $content);
    }
    
    // 3. Anchor Titles (heuristic for generic buttons)
    // "Ver empresa" -> title="Ver detalles de la empresa"
    $content = preg_replace('/<a([^>]*)>(\s*)Ver empresa(\s*)<\/a>/i', '<a$1 title="Ver detalles de la empresa">$2Ver empresa$3</a>', $content);
    // Remove duplicate title if it existed
    $content = preg_replace('/title="Ver detalles de la empresa"([^>]*) title="Ver detalles/i', 'title="Ver detalles', $content);

    $content = preg_replace('/<a([^>]*)>(\s*)Abrir Radar(\s*)<\/a>/i', '<a$1 title="Abrir y configurar el Radar Comercial">$2Abrir Radar$3</a>', $content);
    
    file_put_contents($path, $content);
    echo "Processed: $filename\n";
}
echo "Done.\n";
