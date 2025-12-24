<?php
/**
 * app/Views/partials/head.php
 * HEAD SEO (APIEmpresas.es) — optimizado para Google + social + JSON-LD consistente
 *
 * Variables opcionales que puedes pasar desde cada vista:
 * - $title (string)
 * - $excerptText (string)  // description
 * - $canonical (string)
 * - $robots (string)       // "index,follow" / "noindex,follow" / etc.
 * - $ogImage (string)      // 1200x630 recomendado
 * - $lang (string)         // "es-ES"
 * - $locale (string)       // "es_ES"
 * - $twitterSite (string)  // "@usuario" opcional
 */

$siteName = 'APIEmpresas.es';

$defaultTitle = 'Validar CIF y verificar empresas en España | APIEmpresas.es';
$defaultDesc  = 'Valida CIF y razón social con datos oficiales (BOE/BORME, AEAT, INE y VIES). Buscador web y API REST para KYB/KYC, facturación y scoring.';

$seoTitle = $title ?? $defaultTitle;
$seoDesc  = $excerptText ?? $defaultDesc;

$lang   = $lang ?? 'es-ES';
$locale = $locale ?? 'es_ES';

$robots = $robots ?? 'index,follow';

// Base URL (respeta subcarpeta en local si la hay)
$siteUrl = rtrim(site_url('/'), '/');     // ej: https://apiempresas.es  o  http://localhost/apiempresas
$homeUrl = $siteUrl . '/';

// Canonical: si no viene, usa URL actual sin querystring
$canonicalUrl = $canonical ?? (function () use ($siteUrl) {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $uriNoQuery = explode('?', $uri, 2)[0];
    return $siteUrl . $uriNoQuery;
})();

// Assets
$logoUrl = $logoUrl ?? ($siteUrl . '/logo.png');
$ogImage = $ogImage ?? ($siteUrl . '/public/img/og/apiempresas-og.png'); // ideal 1200x630 real

// URLs reales del sitio
$urlSearch        = $siteUrl . '/search_company';
$urlDocs          = $siteUrl . '/documentation';
$urlBlog          = $siteUrl . '/blog';
$urlPricingAnchor = $siteUrl . '/#precios';
$urlFaqsAnchor    = $siteUrl . '/#faqs';

// SearchAction: URL shareable (GET)
$searchTarget = $urlSearch . '?q={search_term_string}';

// Opcional (si lo tienes)
$twitterSite = $twitterSite ?? null;

// “Googlebot” enriquecido: permite previews grandes si indexas
$googlebot = $googlebot ?? ($robots . ',max-snippet:-1,max-image-preview:large,max-video-preview:-1');

// Title length: no lo recorto aquí (Google lo reescribe), pero evita absurdos
?>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />

<title><?= esc($seoTitle) ?></title>

<meta name="description" content="<?= esc($seoDesc) ?>" />
<meta name="robots" content="<?= esc($robots) ?>" />
<meta name="googlebot" content="<?= esc($googlebot) ?>" />

<link rel="canonical" href="<?= esc($canonicalUrl) ?>" />

<!-- Hreflang (si solo tienes ES, esto es suficiente) -->
<link rel="alternate" href="<?= esc($canonicalUrl) ?>" hreflang="es-ES" />
<link rel="alternate" href="<?= esc($canonicalUrl) ?>" hreflang="x-default" />

<!-- Open Graph -->
<meta property="og:site_name" content="<?= esc($siteName) ?>" />
<meta property="og:title" content="<?= esc($seoTitle) ?>" />
<meta property="og:description" content="<?= esc($seoDesc) ?>" />
<meta property="og:type" content="website" />
<meta property="og:url" content="<?= esc($canonicalUrl) ?>" />
<meta property="og:locale" content="<?= esc($locale) ?>" />
<meta property="og:image" content="<?= esc($ogImage) ?>" />
<meta property="og:image:alt" content="APIEmpresas.es — Validar CIF y verificar empresas en España" />
<meta property="og:image:width" content="1200" />
<meta property="og:image:height" content="630" />

<!-- Twitter -->
<meta name="twitter:card" content="summary_large_image" />
<?php if (!empty($twitterSite)): ?>
    <meta name="twitter:site" content="<?= esc($twitterSite) ?>" />
<?php endif; ?>
<meta name="twitter:title" content="<?= esc($seoTitle) ?>" />
<meta name="twitter:description" content="<?= esc($seoDesc) ?>" />
<meta name="twitter:image" content="<?= esc($ogImage) ?>" />

<!-- Theme color -->
<meta name="theme-color" content="#0b1f56" />

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

<!-- Structured Data (JSON-LD) — @graph enlazado y consistente -->
<script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@graph": [
        {
          "@type": "Organization",
          "@id": "<?= esc($homeUrl) ?>#org",
      "name": "APIEmpresas.es",
      "url": "<?= esc($homeUrl) ?>",
      "logo": {
        "@type": "ImageObject",
        "@id": "<?= esc($homeUrl) ?>#logo",
        "url": "<?= esc($logoUrl) ?>"
      }
    },
    {
      "@type": "WebSite",
      "@id": "<?= esc($homeUrl) ?>#website",
      "url": "<?= esc($homeUrl) ?>",
      "name": "APIEmpresas.es",
      "publisher": { "@id": "<?= esc($homeUrl) ?>#org" },
      "inLanguage": "<?= esc($lang) ?>",
      "potentialAction": {
        "@type": "SearchAction",
        "target": "<?= esc($searchTarget) ?>",
        "query-input": "required name=search_term_string"
      }
    },
    {
      "@type": "WebPage",
      "@id": "<?= esc($canonicalUrl) ?>#webpage",
      "url": "<?= esc($canonicalUrl) ?>",
      "name": "<?= esc($seoTitle) ?>",
      "description": "<?= esc($seoDesc) ?>",
      "isPartOf": { "@id": "<?= esc($homeUrl) ?>#website" },
      "inLanguage": "<?= esc($lang) ?>",
      "primaryImageOfPage": {
        "@type": "ImageObject",
        "@id": "<?= esc($canonicalUrl) ?>#primaryimage",
        "url": "<?= esc($ogImage) ?>",
        "width": 1200,
        "height": 630
      }
    },
    {
      "@type": ["SoftwareApplication", "WebApplication"],
      "@id": "<?= esc($homeUrl) ?>#app",
      "name": "APIEmpresas.es",
      "url": "<?= esc($homeUrl) ?>",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web",
      "description": "Valida CIF y verifica empresas en España con datos oficiales (BOE/BORME, AEAT, INE y VIES). Incluye buscador web y API REST para KYB/KYC, onboarding, facturación y scoring.",
      "publisher": { "@id": "<?= esc($homeUrl) ?>#org" },
      "image": "<?= esc($ogImage) ?>",
      "offers": [
        { "@type": "Offer", "name": "Free (Sandbox)", "price": "0",  "priceCurrency": "EUR", "category": "Free",         "url": "<?= esc($urlPricingAnchor) ?>" },
        { "@type": "Offer", "name": "Pro",           "price": "19", "priceCurrency": "EUR", "category": "Subscription", "url": "<?= esc($urlPricingAnchor) ?>" },
        { "@type": "Offer", "name": "Business",      "price": "49", "priceCurrency": "EUR", "category": "Subscription", "url": "<?= esc($urlPricingAnchor) ?>" }
      ],
      "hasPart": [
        { "@type": "WebPage", "@id": "<?= esc($urlSearch) ?>#webpage", "url": "<?= esc($urlSearch) ?>", "name": "Buscador | APIEmpresas.es" },
        { "@type": "WebPage", "@id": "<?= esc($urlDocs) ?>#webpage",   "url": "<?= esc($urlDocs) ?>",   "name": "Documentación API | APIEmpresas.es" },
        { "@type": "Blog",    "@id": "<?= esc($urlBlog) ?>#blog",      "url": "<?= esc($urlBlog) ?>",   "name": "Blog | APIEmpresas.es" }
      ]
    }
  ]
}
</script>

<?php
/**
 * FAQPage: solo inclúyelo en la HOME (o donde realmente el contenido FAQ sea visible).
 * Así evitas marcar FAQ en páginas donde no está el bloque de FAQs en el DOM.
 *
 * Condición simple: canonical es home o URL termina en "/" (ajústalo si usas otra lógica).
 */
$isHome = rtrim($canonicalUrl, '/') === rtrim($homeUrl, '/');

if ($isHome):
    ?>
    <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "FAQPage",
          "@id": "<?= esc($urlFaqsAnchor) ?>",
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
<?php endif; ?>
