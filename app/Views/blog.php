<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/blog.css') ?>" />
</head>
<body class="blog-page">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header') ?>

<main class="blog-main">
    <!-- HERO BLOG -->
    <section class="container blog-hero">
        <div class="blog-hero__grid">
            <div class="blog-hero__content">
                <span class="blog-hero__eyebrow">Centro de recursos</span>

                <h1 class="blog-hero__title">
                    Guías técnicas para
                    <span class="blog-hero__title-grad">desarrolladores y equipos de riesgo</span>
                </h1>

                <p class="blog-hero__subtitle">
                    Artículos sobre integraciones, scoring, compliance y uso avanzado de datos mercantiles
                    para llevar al siguiente nivel tus flujos de onboarding, riesgos y facturación.
                </p>

                <div class="blog-hero__meta-row">
                    <div class="blog-hero__meta-pill">
                        <span class="blog-hero__meta-icon">★</span>
                        <span>Contenido curado por el equipo técnico de APIEmpresas.</span>
                    </div>

                </div>
            </div>

            <aside class="blog-hero__side" aria-label="Resumen del contenido del blog">
                <div class="blog-summary">
                    <div class="blog-summary__badge">Qué encontrarás aquí</div>

                    <ul class="blog-summary__list">
                        <li>Playbooks para integrar datos mercantiles en tus sistemas.</li>
                        <li>Casos reales de scoring, fraude y KYC/KYB.</li>
                        <li>Buenas prácticas de compliance y documentación.</li>
                        <li>Novedades de producto y roadmap de la API.</li>
                    </ul>

                    <div class="blog-summary__footer">
                        <p class="blog-summary__hint-title">No sabes por dónde empezar</p>
                        <p class="blog-summary__hint-text">
                            Empieza por los artículos marcados como <strong>“Caso de uso”</strong> para ver
                            implementaciones end-to-end en entornos reales.
                        </p>
                    </div>
                </div>
            </aside>
        </div>
    </section>

    <!-- LISTADO BLOG -->
    <section class="container blog-hero">
        <div class="blog-list" >
            <div class="blog-list__header">
                <div>
                    <p class="blog-list__eyebrow">Artículos</p>
                    <h2 class="blog-list__title">Últimas publicaciones</h2>
                </div>
                <div class="blog-list__actions">
                    <div class="blog-search">
                        <span class="blog-search__icon">🔍</span>
                        <input
                                type="search"
                                class="blog-search__input"
                                placeholder="Busca por tema, API, riesgo… (visual)"
                                disabled
                        >
                    </div>
                </div>
            </div>

            <div id="blog-list-error" class="blog-list__error" style="display:none;">
                No se han podido cargar los artículos en este momento. Inténtalo de nuevo en unos segundos.
            </div>

            <div id="blog-list-shell" class="blog-list__shell">
                <!-- Aquí se inyecta el grid vía AJAX -->
            </div>
        </div>
    </section>
</main>

<?= view('partials/footer') ?>
<?= view('scripts') ?>

<script>
    (function () {
        const shell    = document.getElementById('blog-list-shell');
        const errorElt = document.getElementById('blog-list-error');

        if (!shell) return;

        fetch('<?= site_url('get-posts-grid') ?>', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data || !data.ok || !data.html) {
                    throw new Error(data && data.err ? data.err : 'Error al cargar posts');
                }
                shell.innerHTML = data.html;
            })
            .catch(function () {
                if (errorElt) {
                    errorElt.style.display = 'block';
                }
            });
    })();
</script>

</body>
</html>
