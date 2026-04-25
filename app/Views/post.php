<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/post.css?v=' . (file_exists(FCPATH . 'public/css/post.css') ? filemtime(FCPATH . 'public/css/post.css') : time())) ?>" />
    <style>
        .blog-post__content h2{ margin-top: 40px !important; }

        /* ==================================
           PREMIUM DYNAMIC CTAs (WOW EFFECT)
           ================================== */
        .ae-blog-cta {
            margin: 48px 0;
            padding: 40px 32px;
            border-radius: 28px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border: 1px solid rgba(255, 255, 255, 0.4);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            z-index: 1;
        }

        .ae-blog-cta:hover {
            transform: translateY(-5px);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.1);
        }

        /* Decorative Blobs */
        .ae-blog-cta::before, .ae-blog-cta::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            filter: blur(60px);
            z-index: -1;
            opacity: 0.5;
            transition: all 0.6s ease;
        }

        .ae-blog-cta:hover::before { transform: translate(-20px, -20px) scale(1.1); }
        .ae-blog-cta:hover::after { transform: translate(20px, 20px) scale(1.1); }

        .ae-blog-cta__text {
            font-size: 22px;
            font-weight: 800;
            margin-bottom: 24px;
            line-height: 1.2;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .ae-blog-cta__btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 14px 40px;
            border-radius: 99px;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            letter-spacing: 0.02em;
        }

        .ae-blog-cta__btn::before {
            content: '→';
            position: absolute;
            right: 20px;
            opacity: 0;
            transform: translateX(-10px);
            transition: all 0.3s ease;
        }

        .ae-blog-cta__btn:hover {
            padding-right: 50px;
            padding-left: 30px;
            transform: translateY(-2px);
        }

        .ae-blog-cta__btn:hover::before {
            opacity: 1;
            transform: translateX(0);
        }

        .ae-blog-cta__microcopy {
            margin-top: 20px;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            font-weight: 700;
            display: flex;
            justify-content: center;
            gap: 12px;
            flex-wrap: wrap;
            opacity: 0.8;
        }

        .ae-blog-cta__microcopy span {
            display: flex;
            align-items: center;
            gap: 4px;
        }

        /* VARIANTS */

        /* RADAR (PURPLE/INDIGO) */
        .ae-blog-cta--radar { background: #fdfcff; }
        .ae-blog-cta--radar::before { background: #e0d7ff; top: -150px; left: -100px; }
        .ae-blog-cta--radar::after { background: #f3e8ff; bottom: -150px; right: -100px; }
        .ae-blog-cta--radar .ae-blog-cta__text { color: #4338ca; }
        .ae-blog-cta--radar .ae-blog-cta__btn { 
            background: linear-gradient(135deg, #7c3aed 0%, #4f46e5 100%);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }

        /* API (BLUE/CYAN) */
        .ae-blog-cta--api { background: #f8fafc; }
        .ae-blog-cta--api::before { background: #e0f2fe; top: -150px; left: -100px; }
        .ae-blog-cta--api::after { background: #dcfce7; bottom: -150px; right: -100px; }
        .ae-blog-cta--api .ae-blog-cta__text { color: #1e40af; }
        .ae-blog-cta--api .ae-blog-cta__btn { 
            background: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }

        /* MIXED (GOLD/AMBER) */
        .ae-blog-cta--mixed { background: #fffcf0; }
        .ae-blog-cta--mixed::before { background: #fef3c7; top: -150px; left: -100px; }
        .ae-blog-cta--mixed::after { background: #ffedd5; bottom: -150px; right: -100px; }
        .ae-blog-cta--mixed .ae-blog-cta__text { color: #b45309; }
        .ae-blog-cta--mixed .ae-blog-cta__btn { 
            background: linear-gradient(135deg, #d97706 0%, #f59e0b 100%);
            box-shadow: 0 10px 20px rgba(217, 119, 6, 0.3);
        }
        .ae-blog-cta--mixed .ae-blog-cta__btn.secondary { background: #ffffff; color: #b45309; border: 1px solid #fde68a; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }

        @media (max-width: 640px) {
            .ae-blog-cta { padding: 32px 20px; margin: 32px 0; }
            .ae-blog-cta__text { font-size: 19px; }
            .ae-blog-cta__btn { width: 100%; }
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

                <!-- CTA TOP (DYN) -->
                <?= $ctaTop ?? '' ?>

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

                <!-- CTA FINAL (DYN) -->
                <?= $ctaBottom ?? '' ?>

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
