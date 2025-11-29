<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/post.css') ?>" />
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header') ?>

<main class="container blog-post-page">
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
                <div class="blog-post__eyebrow">
                    <?= esc($eyebrow ?? 'Guía técnica') ?>
                </div>

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

                    <span>✍️ Por <?= esc($authorName ?? 'Equipo APIEmpresas') ?></span>

                    <?php if (!empty($readingTime)): ?>
                        <span class="blog-post__meta-dot"></span>
                        <span>⏱ <?= esc($readingTime) ?></span>
                    <?php endif; ?>
                </div>

                <div class="blog-post__hero-cta">
                    <a href="<?= site_url('documentation') ?>" class="blog-post__btn-docs">
                        Ver documentación de la API
                    </a>
                </div>
            </div>

            <hr class="blog-post__divider"/>

            <!-- CONTENIDO -->
            <div class="blog-post__content">
                <!-- Contenido HTML renderizado por WordPress -->
                <?= $content ?? '' ?>
            </div>

            <!-- BLOQUES FINALES (de momento estáticos, si luego quieres los hacemos dinámicos) -->
            <div class="blog-post__footer-blocks">
                <div class="blog-post__footer-box">
                    <h3>Resumen rápido</h3>
                    <p>
                        Verifica CIF, razón social y estado de empresas españolas en segundos usando nuestra API REST,
                        con datos oficiales pensados para onboarding, facturación y scoring de riesgo.
                    </p>
                </div>

                <div class="blog-post__footer-box">
                    <h3>Artículos relacionados</h3>
                    <ul>
                        <li>
                            <a href="<?= site_url('blog') ?>">
                                Ver más guías de integración y casos de uso
                            </a>
                        </li>
                    </ul>
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
</main>

<?= view('partials/footer') ?>
<?= view('scripts') ?>
</body>
</html>
