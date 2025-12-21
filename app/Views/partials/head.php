<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />

<?php
// ---------------------------------------------------------------------------
// SEO defaults (Home)
// - $title, $excerptText y $canonical pueden venir desde el controlador/vista.
// - Si no vienen, usamos defaults optimizados para "validar CIF" + conversión.
// ---------------------------------------------------------------------------
$siteName   = 'APIEmpresas.es';
$defaultTitle = 'Validar CIF y verificar empresas en España | APIEmpresas.es';
$defaultDesc  = 'Valida CIF y razón social con datos oficiales (BOE/BORME, AEAT, INE y VIES). Buscador web y API REST para KYB/KYC, facturación y scoring.';

$seoTitle = $title ?? $defaultTitle;
$seoDesc  = $excerptText ?? $defaultDesc;

// Canonical: puedes pasar $canonical desde el controlador.
// Fallback razonable usando la URL actual.
$canonicalUrl = $canonical ?? (function () {
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int)$_SERVER['SERVER_PORT'] === 443);
    $scheme  = $isHttps ? 'https' : 'http';
    $host    = $_SERVER['HTTP_HOST'] ?? 'apiempresas.es';
    $uri     = $_SERVER['REQUEST_URI'] ?? '/';
    // Quitamos querystring del canonical
    $uriNoQuery = explode('?', $uri, 2)[0];
    return $scheme . '://' . $host . $uriNoQuery;
})();

// OG Image: define un asset estable. Ajusta ruta si lo tienes en otro sitio.
$ogImage = $ogImage ?? (rtrim($canonicalUrl, '/') . '/public/img/og/apiempresas-og.png');

// Robots: por defecto index,follow. Puedes forzar noindex con $robots = 'noindex,nofollow'
$robots = $robots ?? 'index,follow';
?>

<title><?= esc($seoTitle) ?></title>
<meta name="description" content="<?= esc($seoDesc) ?>" />
<meta name="robots" content="<?= esc($robots) ?>" />
<link rel="canonical" href="<?= esc($canonicalUrl) ?>" />

<!-- Open Graph -->
<meta property="og:site_name" content="<?= esc($siteName) ?>" />
<meta property="og:title" content="<?= esc($seoTitle) ?>" />
<meta property="og:description" content="<?= esc($seoDesc) ?>" />
<meta property="og:type" content="website" />
<meta property="og:url" content="<?= esc($canonicalUrl) ?>" />
<meta property="og:image" content="<?= esc($ogImage) ?>" />
<meta property="og:image:alt" content="APIEmpresas.es — Validar CIF y verificar empresas en España" />

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image" />
<meta name="twitter:title" content="<?= esc($seoTitle) ?>" />
<meta name="twitter:description" content="<?= esc($seoDesc) ?>" />
<meta name="twitter:image" content="<?= esc($ogImage) ?>" />

<!-- Performance: fonts -->
<link rel="preconnect" href="https://fonts.googleapis.com" />
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
<link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;600&display=swap"
        rel="stylesheet"
/>

<!-- Styles -->
<link rel="stylesheet" href="<?= base_url('public/css/styles.css') ?>" />

<!-- Favicons -->
<link rel="icon" href="/favicon.ico?v=3" type="image/x-icon" />
<link rel="shortcut icon" href="/favicon.ico?v=3" type="image/x-icon" />
<link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png?v=3" />
<link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png?v=3" />
<link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png?v=3" />

<!-- Structured data (JSON-LD) -->
<script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "SoftwareApplication",
      "name": "APIEmpresas.es",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web",
      "description": "Valida CIF y verifica empresas en España con datos oficiales (BOE/BORME, AEAT, INE y VIES). Incluye buscador web y API REST para KYB/KYC, onboarding, facturación y scoring.",
      "offers": [
        {
          "@type": "Offer",
          "name": "Free (Sandbox)",
          "price": "0",
          "priceCurrency": "EUR",
          "category": "Free"
        },
        {
          "@type": "Offer",
          "name": "Pro",
          "price": "19",
          "priceCurrency": "EUR",
          "category": "Subscription"
        },
        {
          "@type": "Offer",
          "name": "Business",
          "price": "49",
          "priceCurrency": "EUR",
          "category": "Subscription"
        }
      ],
      "url": "<?= esc($canonicalUrl) ?>",
  "publisher": {
    "@type": "Organization",
    "name": "APIEmpresas.es",
    "url": "<?= esc($canonicalUrl) ?>"
  }
}
</script>

<script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "¿Cómo validar un CIF en España?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Puedes validar el formato del CIF y, además, verificar datos de empresa (razón social/estado) contrastándolos con fuentes oficiales. En APIEmpresas.es puedes hacerlo desde el buscador o integrarlo por API REST."
                }
            },
            {
                "@type": "Question",
                "name": "¿Qué diferencia hay entre validar CIF y verificar una empresa?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Validar CIF suele referirse a comprobar el formato y consistencia. Verificar empresa implica contrastar información clave (razón social, estado, etc.) con datos fiables para reducir errores en altas y facturación."
                }
            },
            {
                "@type": "Question",
                "name": "¿Puedo comprobar un NIF-IVA intracomunitario (VIES)?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Sí. Puedes validar el NIF-IVA intracomunitario contra VIES y utilizar ese resultado en procesos de onboarding y cumplimiento."
                }
            },
            {
                "@type": "Question",
                "name": "¿Para qué sirve en KYB/KYC?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Para automatizar verificaciones, reducir riesgo y fraude en altas, y dejar evidencia de la verificación en tus flujos de negocio."
                }
            },
            {
                "@type": "Question",
                "name": "¿Cómo integro la API para validar CIF?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Creas una cuenta, obtienes tu API Key y haces llamadas REST. Tienes documentación y ejemplos en cURL, PHP/Laravel, Node y Python listos para copiar."
                }
            }
        ]
    }
</script>
