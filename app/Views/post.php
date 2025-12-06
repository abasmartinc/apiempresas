<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/post.css') ?>" />
    <style>
        .blog-post__content h2{
            margin-top: 40px !important;
        }
    </style>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header') ?>
<main class="blog-main">
    <!-- HERO BLOG -->
    <section class="container blog-hero">
        <div class="blog-post-shell">
            <article class="blog-post-card">

                <!-- BREADCRUMB -->
                <div class="blog-post__breadcrumb">
                    <a href="<?= site_url() ?>">Inicio</a>
                    <span>·</span>
                    <a href="<?= site_url('blog') ?>">Blog Integraciones</a>
                    <span>·</span>
                    <span><?= esc($title ?? 'Artículo') ?></span>
                </div>

                <!-- CABECERA -->
                <div class="blog-post__hero">
                    <h1 class="blog-post__title">
                        <?= esc($title ?? '') ?>
                    </h1>

                    <?php if (!empty($excerptText)): ?>
                        <p class="blog-post__subtitle">
                            <?= esc($excerptText) ?>
                        </p>
                    <?php endif; ?>

                    <div class="blog-post__meta">
                        <?php if (!empty($dateStr)): ?>
                            <span>📅 Publicado el <?= esc($dateStr) ?></span>
                            <span class="blog-post__meta-dot"></span>
                        <?php endif; ?>

                        <span>✍️ Por Equipo APIEmpresas</span>

                        <?php if (!empty($readingTime)): ?>
                            <span class="blog-post__meta-dot"></span>
                            <span>⏱ <?= esc($readingTime) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <hr class="blog-post__divider"/>

                <!-- CONTENIDO -->
                <div class="blog-post__content">
                    <!-- Contenido HTML renderizado por WordPress -->
                    <?= $content ?? '' ?>
                </div>

                <!-- BLOQUES FINALES -->
                <div class="blog-post__footer-blocks">
                    <div class="blog-post__footer-box">
                        <h3>Resumen rápido</h3>
                        <p>
                            Verifica CIF, razón social y estado de empresas españolas en segundos usando nuestra API REST,
                            con datos oficiales pensados para onboarding, facturación y scoring de riesgo.
                        </p>
                    </div>

                    <!-- ARTÍCULOS RELACIONADOS (DINÁMICOS POR AJAX) -->
                    <div class="blog-post__footer-box blog-post__footer-box--related">
                        <h3>Artículos relacionados</h3>

                        <!-- Contenedor donde se inyectarán los posts -->
                        <div id="related-posts" class="related-posts-grid">
                            <!-- Skeletons mientras carga -->
                            <article class="related-post-skeleton">
                                <div class="skeleton-line skeleton-eyebrow"></div>
                                <div class="skeleton-line skeleton-title"></div>
                                <div class="skeleton-line skeleton-text"></div>
                                <div class="skeleton-line skeleton-text short"></div>
                            </article>
                            <article class="related-post-skeleton">
                                <div class="skeleton-line skeleton-eyebrow"></div>
                                <div class="skeleton-line skeleton-title"></div>
                                <div class="skeleton-line skeleton-text"></div>
                                <div class="skeleton-line skeleton-text short"></div>
                            </article>
                            <article class="related-post-skeleton">
                                <div class="skeleton-line skeleton-eyebrow"></div>
                                <div class="skeleton-line skeleton-title"></div>
                                <div class="skeleton-line skeleton-text"></div>
                                <div class="skeleton-line skeleton-text short"></div>
                            </article>
                        </div>

                        <!-- Mensaje de error opcional -->
                        <p id="related-posts-error" class="muted" style="display:none;margin-top:8px;">
                            No ha sido posible cargar los artículos relacionados en este momento.
                        </p>
                    </div>
                </div>

                <!-- CTA FINAL -->
                <div class="blog-post__cta-final">
                    <p>
                        Crea una cuenta gratuita, obtén tu API key y prueba el endpoint desde el buscador web
                        sin escribir una sola línea de código.
                    </p>
                    <a href="<?= site_url('register') ?>" class="blog-post__btn-main">
                        Empezar gratis
                    </a>
                </div>

            </article>
        </div>
    </section>

</main>
<?= view('partials/footer') ?>
<?= view('scripts') ?>

<script>
    (function () {
        const container = document.getElementById('related-posts');
        const errorMsg  = document.getElementById('related-posts-error');
        if (!container) return;

        fetch('<?= site_url('blog/get_posts') ?>', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (!data || !data.ok || !data.html) {
                    throw new Error(data && data.err ? data.err : 'Error al cargar posts');
                }

                container.innerHTML = data.html;
            })
            .catch(function () {
                // Muestra error y deja de enseñar los skeletons
                if (errorMsg) {
                    errorMsg.style.display = 'block';
                }
            });
    })();
</script>

</body>
</html>
