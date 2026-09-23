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

$siteName = lang('Seo.siteName') !== 'Seo.siteName' ? lang('Seo.siteName') : 'APIEmpresas.es';

$defaultTitle = lang('Seo.defaultTitle') !== 'Seo.defaultTitle' ? lang('Seo.defaultTitle') : 'API para validar empresas y verificar CIF en España | APIEmpresas';
$defaultDesc  = lang('Seo.defaultDesc') !== 'Seo.defaultDesc' ? lang('Seo.defaultDesc') : 'Valida CIF y razón social con datos oficiales. API para validar empresas y Buscador web para KYB/KYC, facturación y prospección B2B.';

$seoTitle = $title ?? $defaultTitle;
$seoDesc  = $excerptText ?? $defaultDesc;

$currentLocale = service('request')->getLocale();
$lang   = $lang ?? ($currentLocale === 'en' ? 'en-US' : 'es-ES');
$locale = $locale ?? ($currentLocale === 'en' ? 'en_US' : 'es_ES');

$robots = $robots ?? 'index,follow';

// Base URL (respeta subcarpeta en local si la hay)
$siteUrl = rtrim(site_url('/'), '/');     // ej: https://apiempresas.es  o  http://localhost/apiempresas
$homeUrl = $siteUrl . '/';

// Canonical: si no viene, usa URL actual sin querystring
$canonicalUrl = $canonical ?? current_url();

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
<?php if (!empty($prevUrl)): ?>
<link rel="prev" href="<?= esc($prevUrl) ?>" />
<?php endif; ?>
<?php if (!empty($nextUrl)): ?>
<link rel="next" href="<?= esc($nextUrl) ?>" />
<?php endif; ?>

<!-- Hreflang cruzado internacional (ES / EN) -->
<?php
// Construimos las URLs para ambos dominios basándonos en la canónica actual
$esUrl = str_replace(['spaincompanyapi.com', 'spaincompanyapi.local'], ['apiempresas.es', 'apiempresas.local'], $canonicalUrl);
$enUrl = str_replace(['apiempresas.es', 'apiempresas.local'], ['spaincompanyapi.com', 'spaincompanyapi.local'], $canonicalUrl);
?>
<link rel="alternate" href="<?= esc($esUrl) ?>" hreflang="es" />
<link rel="alternate" href="<?= esc($esUrl) ?>" hreflang="es-ES" />
<link rel="alternate" href="<?= esc($enUrl) ?>" hreflang="en" />
<link rel="alternate" href="<?= esc($esUrl) ?>" hreflang="x-default" />

<!-- Open Graph -->
<meta property="og:site_name" content="<?= esc($siteName) ?>" />
<meta property="og:title" content="<?= esc($seoTitle) ?>" />
<meta property="og:description" content="<?= esc($seoDesc) ?>" />
<meta property="og:type" content="website" />
<meta property="og:url" content="<?= esc($canonicalUrl) ?>" />
<meta property="og:locale" content="<?= esc($locale) ?>" />
<meta property="og:image" content="<?= esc($ogImage) ?>" />
<meta property="og:image:alt" content="<?= esc($siteName) ?> — <?= esc($seoDesc) ?>" />
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
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;600&display=swap"
        rel="stylesheet"
/>

<!-- Styles -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" />
<link rel="stylesheet" href="<?= base_url('public/css/styles.css?v=' . (file_exists(FCPATH . 'public/css/styles.css') ? filemtime(FCPATH . 'public/css/styles.css') : time())) ?>" /><!-- Sentry Error Tracking -->
<script src="<?= base_url('public/js/sentry-8.54.0.min.js') ?>" crossorigin="anonymous"></script>
<script>
<?php if (env('SENTRY_DSN')): ?>
  Sentry.init({
    dsn: "<?= env('SENTRY_DSN') ?>",
    integrations: [
      Sentry.browserTracingIntegration(),
      Sentry.replayIntegration(),
    ],
    tracesSampleRate: 1.0, 
    replaysSessionSampleRate: 0.1, 
    replaysOnErrorSampleRate: 1.0, 
    environment: "<?= ENVIRONMENT ?>",
  });
<?php endif; ?>
</script>


<!-- jQuery (Required for many interactive elements) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js" defer></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11" defer></script>


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
      "name": "<?= esc($siteName) ?>",
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
      "name": "<?= esc($siteName) ?>",
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
      "name": "<?= esc($siteName) ?>",
      "url": "<?= esc($homeUrl) ?>",
      "applicationCategory": "BusinessApplication",
      "operatingSystem": "Web",
      "description": "<?= esc($defaultDesc) ?>",
      "publisher": { "@id": "<?= esc($homeUrl) ?>#org" },
      "image": "<?= esc($ogImage) ?>",
      "offers": [
        { "@type": "Offer", "name": "Free (Sandbox)", "price": "0",  "priceCurrency": "EUR", "category": "Free",         "url": "<?= esc($urlPricingAnchor) ?>" },
        { "@type": "Offer", "name": "Pro",           "price": "19", "priceCurrency": "EUR", "category": "Subscription", "url": "<?= esc($urlPricingAnchor) ?>" },
        { "@type": "Offer", "name": "Business",      "price": "49", "priceCurrency": "EUR", "category": "Subscription", "url": "<?= esc($urlPricingAnchor) ?>" }
      ],
      "hasPart": [
        { "@type": "WebPage", "@id": "<?= esc($urlSearch) ?>#webpage", "url": "<?= esc($urlSearch) ?>", "name": "Buscador | <?= esc($siteName) ?>" },
        { "@type": "WebPage", "@id": "<?= esc($urlDocs) ?>#webpage",   "url": "<?= esc($urlDocs) ?>",   "name": "Documentación API | <?= esc($siteName) ?>" },
        { "@type": "Blog",    "@id": "<?= esc($urlBlog) ?>#blog",      "url": "<?= esc($urlBlog) ?>",   "name": "Blog | <?= esc($siteName) ?>" }
      ]
    },
    {
      "@type": "Service",
      "name": "API de Verificación y Validación de Empresas en España",
      "serviceType": "B2B Data Verification",
      "provider": { "@id": "<?= esc($homeUrl) ?>#org" },
      "areaServed": {
        "@type": "Country",
        "name": "España"
      },
      "description": "<?= esc($defaultDesc) ?>"
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
      "@type": "BreadcrumbList",
      "itemListElement": [{
        "@type": "ListItem",
        "position": 1,
        "name": "Inicio",
        "item": "<?= site_url() ?>"
      }]
    }
    </script>
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

<!-- Global SweetAlert2 Confirmations -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    document.body.addEventListener('click', function(e) {
        let target = e.target.closest('[data-confirm]');
        if (!target) return;
        
        e.preventDefault();
        const message = target.getAttribute('data-confirm');
        
        Swal.fire({
            title: '¿Estás seguro?',
            text: message,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#10b981',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, continuar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                if (target.tagName === 'A') {
                    window.location.href = target.href;
                } else if (target.tagName === 'FORM') {
                    target.removeAttribute('data-confirm');
                    target.submit();
                } else if (target.tagName === 'BUTTON' || target.tagName === 'INPUT') {
                    let form = target.closest('form');
                    if (form) {
                        form.removeAttribute('data-confirm'); // prevent reshowing
                        form.submit();
                    }
                }
            }
        });
    });

    document.querySelectorAll('form[data-confirm]').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            if (this.hasAttribute('data-confirm')) {
                e.preventDefault();
                const message = this.getAttribute('data-confirm');
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.removeAttribute('data-confirm');
                        form.submit();
                    }
                });
            }
        });
    });
});
</script>

<!-- ============================================================================
     CLIENTE DE TRACKING
     El backend ya existía (tabla tracking_events + TrackingController::logEvent),
     pero window.trackEvent no estaba definido en ninguna parte: todas las llamadas
     repartidas por las vistas iban dentro de un `if (window.trackEvent)` que nunca
     se cumplía, así que no se registraba nada.

     No guarda nada en el navegador: no hay cookie ni localStorage propios. El
     servidor deriva el anonymous_id de IP + user-agent, así que esto no añade
     obligaciones de consentimiento.
============================================================================ -->
<script>
(function () {
    if (window.trackEvent) return;

    var ENDPOINT = '<?= site_url('api/tracking/event') ?>';
    var seen     = {};

    function send(payload) {
        try {
            var body = JSON.stringify(payload);
            if (navigator.sendBeacon) {
                // sendBeacon sobrevive a la navegación: imprescindible para los
                // clicks en CTAs que se llevan al usuario a otra página.
                navigator.sendBeacon(ENDPOINT, new Blob([body], { type: 'application/json' }));
                return;
            }
            fetch(ENDPOINT, {
                method: 'POST',
                credentials: 'same-origin',
                keepalive: true,
                headers: { 'Content-Type': 'application/json' },
                body: body
            }).catch(function () {});
        } catch (err) { /* el tracking nunca debe romper la página */ }
    }

    window.trackEvent = function (name, metadata, element) {
        if (!name) return;
        send({
            event_name: String(name).slice(0, 100),
            page: window.location.pathname + window.location.search,
            element: element ? String(element).slice(0, 255) : '',
            metadata: metadata || {}
        });
    };

    // Para impresiones: una sola vez por clave y carga de página
    window.trackEventOnce = function (key, name, metadata, element) {
        if (seen[key]) return;
        seen[key] = true;
        window.trackEvent(name, metadata, element);
    };

    function readMeta(el) {
        try { return JSON.parse(el.getAttribute('data-track-meta') || '{}'); }
        catch (err) { return {}; }
    }

    // --- CLICKS: delegación con captura, para que el evento salga aunque el
    //     handler de la página haga preventDefault o navegue ---
    document.addEventListener('click', function (e) {
        var el = e.target.closest && e.target.closest('[data-track-click]');
        if (!el) return;
        window.trackEvent(
            el.getAttribute('data-track-click'),
            readMeta(el),
            el.getAttribute('data-track-element') || ''
        );
    }, true);

    // --- IMPRESIONES: se registran cuando el bloque entra de verdad en pantalla ---
    function observeViews(root) {
        var nodes = (root || document).querySelectorAll('[data-track-view]:not([data-track-seen])');
        if (!nodes.length) return;

        if (!('IntersectionObserver' in window)) {
            nodes.forEach(function (el) {
                el.setAttribute('data-track-seen', '1');
                window.trackEvent(el.getAttribute('data-track-view'), readMeta(el));
            });
            return;
        }

        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                var el = entry.target;
                io.unobserve(el);
                if (el.getAttribute('data-track-seen')) return;
                el.setAttribute('data-track-seen', '1');
                window.trackEvent(el.getAttribute('data-track-view'), readMeta(el));
            });
        }, { threshold: 0.3 });

        nodes.forEach(function (el) { io.observe(el); });
    }

    // Expuesto para los bloques que llegan por AJAX (perfil de riesgo, desbloqueo)
    window.trackObserveViews = observeViews;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { observeViews(); });
    } else {
        observeViews();
    }
})();
</script>

<!-- ============================================================================
     ESTADO DE CARGA EN BOTONES DE ENVÍO
     Opt-in: basta con poner data-loading="Texto mientras carga" en el botón.
     Se activa solo en formularios que navegan de verdad; si algún handler hiciera
     preventDefault el botón quedaría bloqueado, por eso NO se aplica a todos.
============================================================================ -->
<style>
    @keyframes ae-spin { to { transform: rotate(360deg); } }
    .ae-spinner {
        display: inline-block;
        width: 1em;
        height: 1em;
        margin-right: 0.55em;
        vertical-align: -0.125em;
        border: 2px solid currentColor;
        border-right-color: transparent;
        border-radius: 50%;
        animation: ae-spin 0.6s linear infinite;
    }
    @media (prefers-reduced-motion: reduce) {
        .ae-spinner { animation-duration: 2s; }
    }
</style>
<script>
(function () {
    if (window.__aeSubmitLoading) return;
    window.__aeSubmitLoading = true;

    function start(btn) {
        if (!btn || btn.getAttribute('data-loading-active') === '1') return;

        var label = btn.getAttribute('data-loading') || 'Un momento…';
        btn.setAttribute('data-loading-active', '1');
        btn.setAttribute('data-loading-html', btn.innerHTML);
        btn.setAttribute('aria-busy', 'true');
        btn.innerHTML = '<span class="ae-spinner"></span><span>' + label + '</span>';
        btn.style.cursor = 'progress';
        btn.style.opacity = '0.9';

        // El submit ya está en marcha cuando llega este evento: deshabilitar aquí
        // no cancela el envío, solo evita el segundo click.
        btn.disabled = true;
    }

    function restore(btn) {
        if (!btn || btn.getAttribute('data-loading-active') !== '1') return;
        btn.innerHTML = btn.getAttribute('data-loading-html') || btn.innerHTML;
        btn.removeAttribute('data-loading-active');
        btn.removeAttribute('data-loading-html');
        btn.removeAttribute('aria-busy');
        btn.disabled = false;
        btn.style.cursor = '';
        btn.style.opacity = '';
    }

    // El evento submit no se dispara si la validación HTML5 falla, así que un
    // formulario incompleto nunca deja el botón girando.
    document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM') return;
        start(form.querySelector('[data-loading]'));
    }, true);

    // Volver atrás restaura la página desde la bfcache con el botón aún bloqueado
    window.addEventListener('pageshow', function (e) {
        if (!e.persisted) return;
        document.querySelectorAll('[data-loading-active="1"]').forEach(restore);
    });
})();
</script>

<!-- ============================================================================
     DESBLOQUEO DEL PERFIL DE RIESGO + APERTURA AUTOMÁTICA TRAS EL REGISTRO

     Este handler vivía dentro de company_risk_locked.php, pero ese bloque llega
     a la ficha por innerHTML y los <script> insertados así NO se ejecutan: con la
     página cacheada en Cloudflare el botón "Ver dictamen" no hacía nada. Aquí
     forma parte del documento y siempre está activo.
============================================================================ -->
<script>
(function () {
    if (window.__riskUnlockBound) return;
    window.__riskUnlockBound = true;

    var ENDPOINT = '<?= site_url('api/empresa/desbloquear-riesgo') ?>';

    function container(el) {
        return (el && el.closest && (el.closest('#risk-profile-container') || el.closest('[data-risk-container]')))
            || document.getElementById('risk-profile-container');
    }

    // --- ¿Venimos de registrarnos para ver ESTA empresa? ---
    function wantsAuto() {
        try { return new URLSearchParams(window.location.search).get('ver-riesgo') === '1'; }
        catch (err) { return false; }
    }

    function clearFlag() {
        try {
            if (!wantsAuto() || !window.history || !history.replaceState) return;
            var u = new URL(window.location.href);
            u.searchParams.delete('ver-riesgo');
            history.replaceState(null, '', u.pathname + (u.search || '') + u.hash);
        } catch (err) {}
    }

    function scrollToBlock() {
        var el = document.getElementById('risk-profile-container');
        if (!el) return;

        function go(smooth) {
            var r = el.getBoundingClientRect();
            var centrado = Math.max(0, (window.innerHeight - r.height) / 2);
            window.scrollTo({
                top: Math.max(0, r.top + window.pageYOffset - centrado),
                behavior: smooth ? 'smooth' : 'auto'
            });
        }

        go(true);

        // La ficha sigue cargando (mapa, gráficas del BORME) y el layout se mueve
        // bajo los pies, así que el destino cambia. Se corrige al terminar la carga,
        // y solo si el bloque acabó fuera de sitio: si ya está bien, no se toca.
        function corregir() {
            setTimeout(function () {
                var r = el.getBoundingClientRect();
                if (r.top < -80 || r.top > window.innerHeight * 0.6) go(false);
            }, 400);
        }

        if (document.readyState === 'complete') corregir();
        else window.addEventListener('load', corregir, { once: true });
    }

    window.riskWantsAutoOpen  = wantsAuto;
    window.riskScrollToBlock  = scrollToBlock;
    window.riskClearAutoFlag  = clearFlag;

    // Lleva al usuario al bloque desde el primer momento, sin esperar al AJAX.
    function focoInicial() {
        if (wantsAuto() && document.getElementById('risk-profile-container')) scrollToBlock();
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', focoInicial);
    } else {
        focoInicial();
    }

    // --- Botón de vigilancia ---
    // Vive aquí por lo mismo que el de desbloqueo: el bloque llega por innerHTML
    // y un <script> dentro de ese HTML no se ejecutaría nunca.
    var ENDPOINT_WATCH = '<?= site_url('api/empresa/vigilar') ?>';

    var URL_PERFIL   = '<?= site_url('profile') ?>';
    var URL_ACTIVAR  = '<?= site_url('api/usuario/activar-avisos') ?>';
    // Solvencia Pro, no el plan Pro de la API (planes/pro, 19 €): el aviso de lista
    // de vigilancia llena mandaba al producto equivocado.
    var URL_PRO      = '<?= site_url('billing?view=risk&plan=risk_pro') ?>';
    <?php helper('company'); ?>
    var CUOTA_GRATIS = <?= (int) solvencia('consultasGratis', 3) ?>;

    /**
     * Píldora de cuota de la cabecera del bloque de riesgo.
     *
     * Vive aquí, y no en company.php, porque hay DOS momentos que la cambian: la
     * hidratación al cargar y el desbloqueo de una empresa. Solo pintaba el
     * primero, así que tras consultar una empresa la píldora seguía diciendo el
     * número de antes mientras el upsell, que sí venía del HTML nuevo, decía el
     * de después: "0 de 3 consultas este mes" arriba y "te quedan 2" abajo, en
     * la misma pantalla.
     */
    function pintarCuota(q) {
        var pill = document.getElementById('risk-quota-pill');
        if (!pill || !q) return;

        if (q.is_subscriber) {
            pill.style.background = '#ecfdf5';
            pill.style.border = '1px solid #a7f3d0';
            pill.style.color = '#047857';
            pill.textContent = '⭐ Solvencia Pro';
            pill.title = 'Suscripción activa';
            pill.style.display = 'inline-flex';
            return;
        }

        if (typeof q.views_used === 'undefined') return;

        var limite = (typeof q.views_limit === 'number' && q.views_limit > 0) ? q.views_limit : CUOTA_GRATIS;
        var usadas = Math.min(limite, Math.max(0, q.views_used));
        var quedan = Math.max(0, limite - usadas);

        // El color sigue a lo que queda: antes iba siempre en azul "todo en
        // orden", incluso con 3 de 3 gastadas, que es justo cuando no lo está.
        if (quedan === 0) {
            pill.style.background = '#fef2f2';
            pill.style.border = '1px solid #fecaca';
            pill.style.color = '#b91c1c';
            pill.textContent = '🔴 Sin consultas gratis este mes';
            pill.title = 'Has usado tus ' + limite + ' consultas gratuitas del mes';
        } else if (quedan === 1) {
            pill.style.background = '#fffbeb';
            pill.style.border = '1px solid #fde68a';
            pill.style.color = '#b45309';
            pill.textContent = '🟠 Te queda 1 consulta gratis';
            pill.title = 'Límite mensual gratuito de ' + limite + ' empresas';
        } else {
            pill.style.background = '#eff6ff';
            pill.style.border = '1px solid #bfdbfe';
            pill.style.color = '#1d4ed8';
            // "Te quedan N" y no "N de 3 usadas": la píldora y el upsell hablaban
            // del mismo dato en dos unidades distintas, y eso ya parece un error
            // aunque los números cuadren.
            pill.textContent = '🟢 Te quedan ' + quedan + ' consultas gratis';
            pill.title = 'Límite mensual gratuito de ' + limite + ' empresas';
        }

        pill.style.display = 'inline-flex';
    }
    window.riskPintarCuota = pintarCuota;

    /**
     * Menú "Descargar" de la ficha: qué opción se enseña para el informe de riesgo.
     *
     * La ficha va cacheada en Cloudflare, así que el menú nace ofreciendo la compra
     * a todo el mundo. Quien ya tiene derecho al informe —suscriptor, empresa ya
     * consultada o ya comprada— tiene que ver la descarga, no el precio: ofrecerle
     * por 3,90 € lo que puede bajarse gratis en la barra de abajo es la clase de
     * detalle que le hace dudar del resto de precios de la página.
     *
     * Las dos variantes están en el HTML desde el principio y ninguna lleva dato de
     * sesión, así que la página sigue siendo cacheable: aquí solo se decide cuál se
     * ve. Hay dos momentos que lo cambian —la hidratación y el desbloqueo de la
     * empresa—, de ahí que viva aquí y no en company.php.
     */
    function pintarMenuDescargas(gratis) {
        var opcionGratis = document.querySelector('[data-descarga-informe="incluido"]');
        var opcionPago   = document.querySelector('[data-descarga-informe="pago"]');

        if (!opcionGratis || !opcionPago) return;

        // 'block', que es lo que el CSS del menú le da a sus <a> y <button>. Con
        // 'flex' los dos <span> de dentro —título y subtítulo— se ponen en fila en
        // vez de uno debajo del otro, y la opción sale rota en una sola línea.
        opcionGratis.style.display = gratis ? 'block' : 'none';
        opcionPago.style.display   = gratis ? 'none' : 'block';
    }
    window.riskMenuDescargas = pintarMenuDescargas;

    /**
     * Nota de "has llegado al tope de empresas vigiladas".
     *
     * Se pinta en el mismo sitio que el resto de notas del chip en vez de en un
     * alert(): el alert dice "error" y esto no lo es —el usuario ha hecho algo
     * perfectamente razonable y la respuesta correcta es enseñarle la salida.
     * Si el bloque Pro está en la página se le lleva ahí, que ya trae el precio
     * y la garantía delante; si no, a la página del plan.
     */
    function pintarLimite(mensaje) {
        var nota = document.getElementById('risk-watch-note');
        if (!nota) { window.location.href = URL_PRO; return; }

        var hayBloque = !!document.getElementById('risk-pro-upsell');

        nota.style.color = '#b45309';
        nota.innerHTML = '⚠️ ' + (mensaje || 'Has llegado al máximo de empresas vigiladas.') + ' '
            + '<button type="button" data-risk-ver-pro '
            + 'style="background:none;border:none;padding:0;font:inherit;color:#b45309;'
            + 'font-weight:800;text-decoration:underline;cursor:pointer;">'
            + (hayBloque ? 'Ver Pro' : 'Ver Solvencia Pro') + '</button>';
        nota.style.display = 'block';
    }

    document.addEventListener('click', function (e) {
        var enlace = e.target.closest && e.target.closest('[data-risk-ver-pro]');
        if (!enlace) return;

        e.preventDefault();
        var bloque = document.getElementById('risk-pro-upsell');
        if (bloque && bloque.scrollIntoView) {
            bloque.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            window.location.href = URL_PRO;
        }
        if (window.trackEvent) window.trackEvent('risk_watch_limit_cta', {}, 'ficha');
    });

    // La nota que acompaña al botón. Apagado vende, encendido tranquiliza, y si el
    // usuario tiene los avisos desactivados lo dice en vez de prometer un correo
    // que nunca va a salir.
    /**
     * La nota que acompaña al chip.
     *
     * `confirmar` distingue dos situaciones que NO deben decir lo mismo:
     *   - true  → el usuario acaba de pulsar. Toca acusar recibo ("Listo…").
     *   - false → la página se acaba de cargar. Nadie ha hecho nada, así que
     *             describe el estado; un "Listo" al recargar suena a que la
     *             página ha hecho algo por su cuenta.
     */
    function pintarNota(btn, on, confirmar) {
        var nota = document.getElementById('risk-watch-note');
        if (!nota) return;

        var avisos = btn.dataset.alertsOn !== '0';
        var correo = btn.dataset.email || '';

        if (!on) {
            nota.style.color = confirmar ? '#475569' : '#64748b';
            nota.textContent = confirmar
                ? 'Has dejado de vigilar esta empresa.'
                : 'Te avisamos por correo si aparece un acto nuevo en el BORME.';
        } else if (!avisos) {
            // Botón, no enlace al perfil: decirle a alguien que algo está mal y
            // mandarlo a buscar una casilla en otra página pierde por el camino a
            // la mayoría. Se arregla aquí mismo, en un clic.
            nota.style.color = '#b45309';
            nota.innerHTML = '⚠️ Vigilando, pero tienes los avisos desactivados. '
                + '<button type="button" data-risk-alerts-on '
                + 'style="background:none;border:none;padding:0;font:inherit;color:#b45309;'
                + 'font-weight:800;text-decoration:underline;cursor:pointer;">Activarlos ahora</button>';
        } else if (confirmar) {
            nota.style.color = '#047857';
            nota.textContent = correo
                ? 'Listo. Te escribimos a ' + correo + ' en cuanto se mueva.'
                : 'Listo. Te escribimos en cuanto se mueva.';
        } else {
            nota.style.color = '#047857';
            nota.textContent = correo
                ? 'Te escribimos a ' + correo + ' si aparece un acto nuevo.'
                : 'Te escribimos por correo si aparece un acto nuevo.';
        }

        nota.style.display = 'block';
    }

    // Las cajas de "¿quieres vigilarla?" solo tienen sentido mientras NO se vigila.
    // En cuanto se vigila desaparecen: al que ya dijo que sí no se le vuelve a pedir.
    //
    // La verdad la tiene el chip de la cabecera, que es a quien la hidratación le
    // pasa el estado real; estas cajas vienen dentro del HTML del dictamen y no
    // pueden traerlo (la ficha va cacheada).
    function sincronizarPeticion() {
        var chip  = document.getElementById('risk-watch-header');
        var cajas = document.querySelectorAll('[data-risk-prompt]');
        if (!cajas.length) return;

        var vigilando = chip ? chip.dataset.watching === '1' : false;
        var lleno     = chip ? chip.dataset.watchFull === '1' : false;
        var tope      = chip ? (chip.dataset.watchTope || '') : '';

        for (var i = 0; i < cajas.length; i++) {
            var btn = cajas[i].querySelector('[data-risk-watch]');
            if (btn && chip) {
                btn.dataset.watching = vigilando ? '1' : '0';
                btn.dataset.alertsOn = chip.dataset.alertsOn || '1';
                btn.dataset.email    = chip.dataset.email || '';
                btn.dataset.watchFull = lleno ? '1' : '0';
            }

            // Con la lista llena, pedirle que vigile una más es ofrecerle un botón
            // que no va a funcionar. Se cambia la letra pequeña por el motivo y la
            // salida, y el botón sigue ahí: el clic explica, no falla en silencio.
            var pie = cajas[i].querySelector('[data-risk-prompt-nota]');
            if (pie) {
                // El texto original se guarda la primera vez: si luego libera un
                // hueco, la caja tiene que volver a vender, no quedarse con el
                // aviso de "completa" puesto para siempre.
                if (typeof pie.dataset.original === 'undefined') {
                    pie.dataset.original = pie.innerHTML;
                }

                if (lleno) {
                    pie.innerHTML = 'Tu lista de vigilancia está completa'
                        + (tope ? ' (' + tope + ' empresas)' : '') + '. '
                        + '<button type="button" data-risk-ver-pro '
                        + 'style="background:none;border:none;padding:0;font:inherit;color:#b45309;'
                        + 'font-weight:800;text-decoration:underline;cursor:pointer;">Ampliar con Pro</button>';
                    pie.style.color = '#b45309';
                } else {
                    pie.innerHTML = pie.dataset.original;
                    pie.style.color = '#64748b';
                }
            }

            cajas[i].style.display = vigilando ? 'none' : 'flex';
        }
    }
    window.riskSincronizarPeticion = sincronizarPeticion;

    function pintarVigilancia(btn, on, confirmar) {
        btn.dataset.watching = on ? '1' : '0';

        // El chip de la cabecera es el indicador de estado y cambia de color;
        // el botón del final es una llamada a la acción y mantiene su aspecto.
        if (btn.id === 'risk-watch-header') {
            btn.style.background = on ? '#ecfdf5' : '#ffffff';
            btn.style.borderColor = on ? '#a7f3d0' : '#cbd5e1';
            btn.style.color = on ? '#047857' : '#475569';
        }

        var icono = btn.querySelector('[data-watch-icon]');
        var texto = btn.querySelector('[data-watch-label]');
        if (icono) icono.textContent = on ? '🔔' : '🔕';
        if (texto) texto.textContent = on ? 'Vigilando' : 'Vigilar empresa';

        if (btn.id === 'risk-watch-header') {
            pintarNota(btn, on, confirmar);
            ultimaVigilancia = !!on;
            pintarUpsell(ultimaVigilancia);
        }
    }

    // Último estado conocido de vigilancia de la empresa de la ficha. Hace falta
    // guardarlo porque el chip se pinta ANTES de que exista el upsell en el DOM: la
    // hidratación inyecta el HTML del dictamen después, y para entonces ya nadie
    // vuelve a llamar aquí. Es el mismo desfase que ya obligaba a sincronizar a mano
    // la caja de "¿quieres vigilarla?".
    var ultimaVigilancia = null;

    // El upsell de Pro no puede venderle "te avisamos cuando se mueva" a quien ya
    // tiene esa empresa en vigilancia: eso ya lo tiene, y gratis. La vista emite las
    // dos frases y aquí se enseña la que corresponde al estado real del usuario.
    function pintarUpsell(on) {
        var caja = document.getElementById('risk-pro-upsell');
        if (!caja) return;

        // querySelectorAll, no querySelector: hay varios pares (titular, párrafo y la
        // primera ventaja de la lista). Con el singular se cambiaba solo el párrafo y
        // el titular se quedaba pidiendo "no perder de vista" una empresa que el texto
        // de debajo reconocía como ya vigilada.
        var pares = caja.querySelectorAll('[data-upsell-copy]');
        for (var i = 0; i < pares.length; i++) {
            var esDeVigilando = pares[i].getAttribute('data-upsell-copy') === 'on';
            pares[i].hidden = esDeVigilando ? !on : !!on;
        }
    }

    // Activar las alertas sin salir de la ficha.
    document.addEventListener('click', function (e) {
        var enlace = e.target.closest && e.target.closest('[data-risk-alerts-on]');
        if (!enlace) return;

        e.preventDefault();
        if (enlace.dataset.loading === '1') return;
        enlace.dataset.loading = '1';
        enlace.textContent = 'Activando…';

        fetch(URL_ACTIVAR, {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: '{}'
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data || !data.ok) throw new Error('alerts_failed');
            var btn = document.getElementById('risk-watch-header');
            if (btn) {
                btn.dataset.alertsOn = '1';
                pintarTodos(btn, btn.dataset.watching === '1', true);
            }
            if (window.trackEvent) window.trackEvent('risk_alerts_enabled', {}, 'ficha');
        })
        .catch(function () {
            // Si falla, al menos que pueda llegar al sitio donde se arregla.
            enlace.dataset.loading = '0';
            enlace.textContent = 'Activarlos en tu perfil';
            enlace.onclick = function () { window.location.href = URL_PERFIL; };
        });
    });

    // Hay dos botones para la misma empresa: si uno cambia y el otro no, el usuario
    // ve dos verdades distintas en la misma página. Se pintan siempre los dos.
    function pintarTodos(origen, on, confirmar) {
        var cif = origen.dataset.cif || '';
        var todos = document.querySelectorAll('[data-risk-watch]');
        for (var i = 0; i < todos.length; i++) {
            if ((todos[i].dataset.cif || '') !== cif) continue;
            todos[i].dataset.alertsOn = origen.dataset.alertsOn || todos[i].dataset.alertsOn || '1';
            todos[i].dataset.email    = origen.dataset.email || todos[i].dataset.email || '';
            pintarVigilancia(todos[i], on, confirmar);
        }
        sincronizarPeticion();
    }

    // La hidratación de la ficha necesita pintar el botón de la cabecera con el
    // estado real del usuario: el HTML cacheado lo sirve siempre en neutro.
    window.riskPintarVigilancia = pintarVigilancia;

    // La llama la hidratación DESPUÉS de inyectar el dictamen, que es el momento en
    // que el upsell existe por fin. Sin argumentos: usa el estado que el chip ya dejó
    // guardado, para que no haya dos fuentes de verdad sobre si vigila o no.
    window.riskPintarUpsell = function () {
        if (ultimaVigilancia !== null) pintarUpsell(ultimaVigilancia);
    };

    document.addEventListener('click', function (e) {
        var btn = e.target.closest && e.target.closest('[data-risk-watch]');
        if (!btn) return;

        e.preventDefault();
        if (btn.dataset.loading === '1') return;
        btn.dataset.loading = '1';
        btn.style.opacity = '0.6';

        var estabaActivo = btn.dataset.watching === '1';

        fetch(ENDPOINT_WATCH, {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            // `origen` lo pone la vuelta de "Avísame si cambia"; un clic normal es 'manual'.
            body: JSON.stringify({ cif: btn.dataset.cif || '', source: btn.dataset.origen || 'manual' })
        })
        .then(function (r) { delete btn.dataset.origen; return r.json(); })
        .then(function (data) {
            // Tope de vigilancias: no es un fallo, es una respuesta del producto.
            if (data && data.limite) {
                // El chip es de donde sincronizarPeticion() lee la verdad, así que
                // el "lleno" se apunta ahí antes de repintar; si no, la caja de
                // petición seguiría invitando a un clic que ya sabemos que rebota.
                var chip = document.getElementById('risk-watch-header');
                if (chip) {
                    chip.dataset.watchFull = '1';
                    if (data.cupo && data.cupo.tope) chip.dataset.watchTope = data.cupo.tope;
                }
                pintarTodos(btn, estabaActivo);      // el botón se queda como estaba
                pintarLimite(data.message);
                if (window.trackEvent) {
                    window.trackEvent('risk_watch_limit_hit', { cif: btn.dataset.cif || '' }, 'ficha');
                }
                return;
            }
            if (!data || !data.ok) throw new Error('watch_failed');
            if (typeof data.alerts_on !== 'undefined') {
                btn.dataset.alertsOn = data.alerts_on ? '1' : '0';
            }
            // Dejar de vigilar libera hueco: el "lleno" tiene que caducar aquí o
            // la caja seguiría diciendo que no cabe nada cuando ya cabe.
            var chipOk = document.getElementById('risk-watch-header');
            if (chipOk && data.cupo) {
                chipOk.dataset.watchFull = (!data.cupo.ilimitado && data.cupo.lleno) ? '1' : '0';
                if (data.cupo.tope) chipOk.dataset.watchTope = data.cupo.tope;
            }
            pintarTodos(btn, !!data.watching, true);   // viene de un clic
            if (window.trackEvent) {
                window.trackEvent('risk_watch_toggle', { cif: btn.dataset.cif || '' }, data.watching ? 'on' : 'off');
            }
        })
        .catch(function () {
            pintarTodos(btn, estabaActivo);        // se deja como estaba
            alert('No hemos podido cambiar la vigilancia. Vuelve a intentarlo en unos segundos.');
        })
        .then(function () {
            btn.dataset.loading = '0';
            btn.style.opacity = '1';
        });
    });

    // --- Click de desbloqueo (único punto donde se consume cuota) ---
    document.addEventListener('click', function (e) {
        var btn = e.target.closest && e.target.closest('[data-risk-unlock]');
        if (!btn) return;

        e.preventDefault();
        if (btn.dataset.loading === '1') return;
        btn.dataset.loading = '1';

        var original = btn.innerHTML;
        btn.innerHTML = '<span class="ae-spinner"></span><span>Consultando registro oficial…</span>';
        btn.style.opacity = '0.85';
        btn.style.cursor = 'progress';

        var cont = container(btn);
        var auto = wantsAuto();

        fetch(ENDPOINT, {
            method: 'POST',
            credentials: 'same-origin',
            cache: 'no-store',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ cif: btn.dataset.cif || '', company_id: btn.dataset.companyId || 0 })
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            if (!data || !data.html || !cont) {
                throw new Error(data && data.message ? data.message : 'unlock_failed');
            }

            cont.innerHTML = data.html;

            // Acabamos de gastar una consulta: la píldora de la cabecera tiene que
            // enterarse. Si no, se queda contradiciendo al bloque que hay debajo.
            if (window.riskPintarCuota) window.riskPintarCuota(data.risk_quota);

            // Y desbloquear la empresa da derecho a su informe: el menú de descargas
            // tiene que dejar de ofrecer la compra en el mismo instante, o le estará
            // pidiendo 3,90 € por lo que acaba de conseguir.
            if (window.riskMenuDescargas) window.riskMenuDescargas(true);

            // Y desbloquear da de alta la vigilancia, así que el chip también se
            // ha quedado atrás: lo pintó la hidratación, antes de todo esto.
            var chipTrasDesbloqueo = document.getElementById('risk-watch-header');
            if (chipTrasDesbloqueo && typeof data.is_watching !== 'undefined') {
                chipTrasDesbloqueo.style.display = 'inline-flex';
                if (typeof data.watch_alerts_on !== 'undefined') {
                    chipTrasDesbloqueo.dataset.alertsOn = data.watch_alerts_on ? '1' : '0';
                }
                if (data.watch_email) chipTrasDesbloqueo.dataset.email = data.watch_email;
                chipTrasDesbloqueo.dataset.watchFull = data.watch_full ? '1' : '0';
                if (data.watch_quota && data.watch_quota.tope) {
                    chipTrasDesbloqueo.dataset.watchTope = data.watch_quota.tope;
                }
                // `false` en confirmar: el usuario ha pedido ver el dictamen, no ha
                // pulsado "vigilar". Un "Listo, te escribimos..." aquí suena a que
                // la página ha hecho algo por su cuenta — y es que lo ha hecho.
                pintarTodos(chipTrasDesbloqueo, !!data.is_watching, false);
            }

            if (window.trackEvent) {
                window.trackEvent('risk_unlock_done', { cif: btn.dataset.cif || '', state: data.state || '' });
            }
            if (window.trackObserveViews) window.trackObserveViews(cont);

            // El dictamen es mucho más alto que la tarjeta bloqueada: hay que
            // recolocar la vista después de sustituir el contenido.
            if (auto) { scrollToBlock(); clearFlag(); }
        })
        .catch(function () {
            btn.innerHTML = original;
            btn.style.opacity = '1';
            btn.style.cursor = 'pointer';
            btn.dataset.loading = '0';
            alert('No hemos podido abrir el dictamen. Vuelve a intentarlo en unos segundos.');
        });
    });
})();
</script>
