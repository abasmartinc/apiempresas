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
                <span class="blog-hero__eyebrow">Blog de APIEmpresas</span>

                <h1 class="blog-hero__title">
                    Información y análisis sobre el
                    <span class="blog-hero__title-grad">tejido empresarial español</span>
                </h1>

                <p class="blog-hero__subtitle">
                    Artículos sobre actualidad empresarial, análisis de sectores, finanzas y soluciones 
                    para optimizar la gestión de datos en tu negocio.
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
                        <li>Análisis detallados de sectores y tendencias de mercado.</li>
                        <li>Guías sobre finanzas corporativas y gestión de riesgos.</li>
                        <li>Novedades sobre normativas y cumplimiento legal (KYC/KYB).</li>
                        <li>Cómo potenciar tu empresa con datos mercantiles precisos.</li>
                    </ul>

                    <div class="blog-summary__footer">
                        <p class="blog-summary__hint-title">¿Buscas algo específico?</p>
                        <p class="blog-summary__hint-text">
                            Explora nuestras categorías para encontrar análisis sobre <strong>autónomos</strong>, 
                            <strong>PYMES</strong> o grandes <strong>corporaciones</strong> españolas.
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
