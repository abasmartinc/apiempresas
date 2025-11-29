<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/blog.css') ?>" />
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header') ?>

<main>
    <!-- HERO BLOG -->
    <section class="container blog-hero">
        <div class="blog-hero__eyebrow">Recursos &amp; guías</div>

        <h1 class="title">
            Centro de recursos para
            <span class="grad">desarrolladores y equipos de riesgo.</span>
        </h1>

        <p class="subtitle">
            Guías técnicas, casos de uso y buenas prácticas para integrar la verificación
            de empresas españolas en tus flujos de onboarding, scoring y facturación.
        </p>

        <div class="blog-hero__meta">
            <button type="button" class="blog-hero__primary-chip">
                <span>🧩</span>
                <span>Artículos curados sobre datos mercantiles y APIs</span>
            </button>

            <div class="blog-hero__order">
                <span>Ordenado por</span>
                <select class="blog-hero__order-select" aria-label="Ordenar artículos">
                    <option value="recent">Más recientes</option>
                    <option value="popular">Más leídos</option>
                </select>
            </div>
        </div>

        <div class="blog-filters">
            <button class="blog-chip blog-chip--active" type="button">Todo</button>
            <button class="blog-chip" type="button">Integraciones</button>
            <button class="blog-chip" type="button">Casos de uso</button>
            <button class="blog-chip" type="button">Producto</button>
            <button class="blog-chip" type="button">Compliance</button>
        </div>
    </section>

    <!-- LISTADO BLOG -->
    <section class="container blog-list">
        <div class="blog-list-shell">
            <!-- PRIMERA FILA -->
            <div class="blog-list__grid">

                <!-- DESTACADO IZQUIERDA -->
                <article class="blog-card blog-card--featured">
                    <a href="<?= site_url('blog/automatizar-onboarding-kyc') ?>" class="blog-card__link">
                        <div class="blog-card__header">
                            <div class="blog-card__header-label">Caso de uso · Onboarding</div>
                            <div class="blog-card__header-title">
                                Reduce fraude en altas y errores de CIF usando la API en tiempo real.
                            </div>
                        </div>
                        <div class="blog-card__body">
                            <div class="blog-card__eyebrow">Integraciones</div>
                            <h2 class="blog-card__title">
                                Cómo automatizar el onboarding de clientes B2B validando CIF y razón social en segundos
                            </h2>
                            <p class="blog-card__excerpt">
                                Te mostramos un flujo completo para validar empresas desde tu SaaS:
                                formulario, llamada a la API, gestión de errores y trazabilidad con enlace al BORME.
                            </p>
                            <div class="blog-card__meta">
                                <span>📅 12 nov 2025</span>
                                <span class="blog-card__meta-dot"></span>
                                <span>⏱ 8 min</span>
                                <span class="blog-card__meta-dot"></span>
                                <span class="blog-card__tag">Onboarding KYB/KYC</span>
                            </div>
                        </div>
                    </a>
                </article>

                <!-- COLUMNA DERECHA -->
                <div class="blog-list__stack">

                    <article class="blog-card blog-card--compact">
                        <a href="<?= site_url('blog/validar-cif-desde-laravel') ?>" class="blog-card__link">
                            <div class="blog-card--compact-inner">
                                <div class="blog-card--compact-bar"></div>
                                <div class="blog-card--compact-content">
                                    <div class="blog-card__eyebrow">Guía técnica</div>
                                    <h2 class="blog-card__title">
                                        Validar CIF y razón social desde Laravel paso a paso
                                    </h2>
                                    <p class="blog-card__excerpt">
                                        Endpoint, middleware, manejo de errores y logs de auditoría listos para producción.
                                    </p>
                                    <div class="blog-card__meta">
                                        <span>📅 3 nov 2025</span>
                                        <span class="blog-card__meta-dot"></span>
                                        <span>⏱ 6 min</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </article>

                    <article class="blog-card blog-card--compact">
                        <a href="<?= site_url('blog/score-riesgo-empresas-datos-publicos') ?>" class="blog-card__link">
                            <div class="blog-card--compact-inner">
                                <div class="blog-card--compact-bar"></div>
                                <div class="blog-card--compact-content">
                                    <div class="blog-card__eyebrow">Producto &amp; datos</div>
                                    <h2 class="blog-card__title">
                                        Diseñando un score de riesgo con BORME, AEAT e INE
                                    </h2>
                                    <p class="blog-card__excerpt">
                                        Qué campos usar, cómo ponderarlos y cómo mantener el modelo
                                        explicable para equipos de riesgo y compliance.
                                    </p>
                                    <div class="blog-card__meta">
                                        <span>📅 20 oct 2025</span>
                                        <span class="blog-card__meta-dot"></span>
                                        <span>⏱ 7 min</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </article>

                    <article class="blog-card blog-card--compact">
                        <a href="<?= site_url('blog/logs-trazabilidad-kyc') ?>" class="blog-card__link">
                            <div class="blog-card--compact-inner">
                                <div class="blog-card--compact-bar"></div>
                                <div class="blog-card--compact-content">
                                    <div class="blog-card__eyebrow">Compliance</div>
                                    <h2 class="blog-card__title">
                                        Trazabilidad y evidencias cuando usas la API de empresas
                                    </h2>
                                    <p class="blog-card__excerpt">
                                        Campos mínimos, tiempos de retención y cómo vincular cada consulta
                                        al expediente o cliente en tu base de datos.
                                    </p>
                                    <div class="blog-card__meta">
                                        <span>📅 8 oct 2025</span>
                                        <span class="blog-card__meta-dot"></span>
                                        <span>⏱ 5 min</span>
                                    </div>
                                </div>
                            </div>
                        </a>
                    </article>

                </div>
            </div>

            <!-- SEGUNDA FILA -->
            <div class="blog-list__grid-secondary">

                <article class="blog-card blog-card--wide">
                    <a href="<?= site_url('blog/webhooks-empresas') ?>" class="blog-card__link">
                        <div class="blog-card__body">
                            <div class="blog-card__eyebrow">Integraciones</div>
                            <h2 class="blog-card__title">
                                Cómo usar webhooks para refrescar datos de empresas de forma automática
                            </h2>
                            <p class="blog-card__excerpt">
                                Diseña un flujo donde los cambios en el registro mercantil disparan
                                actualizaciones en tu CRM, billing o plataforma interna.
                            </p>
                            <div class="blog-card__meta">
                                <span>📅 27 sep 2025</span>
                                <span class="blog-card__meta-dot"></span>
                                <span>⏱ 9 min</span>
                            </div>
                        </div>
                    </a>
                </article>

                <article class="blog-card blog-card--wide">
                    <a href="<?= site_url('blog/ine-y-vies-para-expansiones') ?>" class="blog-card__link">
                        <div class="blog-card__body">
                            <div class="blog-card__eyebrow">Datos</div>
                            <h2 class="blog-card__title">
                                Usar INE y VIES para priorizar expansión comercial en España y Europa
                            </h2>
                            <p class="blog-card__excerpt">
                                Segmenta tus cuentas objetivo cruzando CNAE, localización y estado fiscal
                                para decidir dónde abrir mercado primero.
                            </p>
                            <div class="blog-card__meta">
                                <span>📅 15 sep 2025</span>
                                <span class="blog-card__meta-dot"></span>
                                <span>⏱ 6 min</span>
                            </div>
                        </div>
                    </a>
                </article>

            </div>
        </div>
    </section>
</main>

<?= view('partials/footer') ?>
<?= view('scripts') ?>

</body>
</html>
