<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Radar de Nuevas Empresas en España | Detecta Leads B2B Cada Día',
        'excerptText' => 'Detecta empresas recién creadas en España y accede a oportunidades comerciales antes que tu competencia. Datos del BORME actualizados diariamente.',
        'canonical'   => site_url('precios-radar'),
        'robots'      => 'index,follow',
    ]) ?>
    <link rel="stylesheet" href="<?= base_url('public/css/precios_radar.css?v=' . (file_exists(FCPATH . 'public/css/precios_radar.css') ? filemtime(FCPATH . 'public/css/precios_radar.css') : time())) ?>" />
</head>
<body>
<?= view('partials/header') ?>

<main class="radar-page">

    <section class="radar-hero">
        <div class="container">
            <div class="radar-hero__shell">
                <div class="radar-hero__badge">
                    <span class="radar-hero__badge-dot"></span>
                    Radar de nuevas empresas
                </div>

                <h1 class="radar-hero__title">
                    Detecta nuevas empresas
                    <span>antes que tu competencia</span>
                </h1>

                <p class="radar-hero__subtitle">
                    Accede cada día a nuevas sociedades detectadas desde el BORME, filtra por sector o provincia y exporta leads listos para prospección comercial.
                </p>

                <div class="radar-hero__proof">
                    <div class="radar-hero__proof-item">
                        <strong>+200</strong>
                        <span>nuevas empresas detectadas al día</span>
                    </div>
                    <div class="radar-hero__proof-item">
                        <strong>+4,5M</strong>
                        <span>empresas analizadas en España</span>
                    </div>
                    <div class="radar-hero__proof-item">
                        <strong>100%</strong>
                        <span>datos oficiales del BORME</span>
                    </div>
                </div>

                <div class="radar-hero__actions">
                    <a href="<?= site_url('billing/checkout?plan=radar&period=monthly') ?>" class="radar-btn radar-btn--primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        Activar Radar
                    </a>

                    <a href="#radar-pricing" class="radar-btn radar-btn--ghost">
                        Ver plan Radar
                    </a>
                </div>

                <p class="radar-hero__note">
                    Sin permanencia · Cancela cuando quieras · Exportación Excel / CSV incluida
                </p>

                <div class="radar-hero__feature-panel">
                    <div class="radar-hero__feature-copy">
                        <h2>Todo lo que necesitas para encontrar leads B2B antes que el mercado</h2>
                        <ul>
                            <li>Nuevas empresas detectadas diariamente desde el BORME</li>
                            <li>Filtros por sector CNAE, provincia y fecha</li>
                            <li>Exportación directa para prospección comercial</li>
                        </ul>
                    </div>

                    <div class="radar-hero__mini-dashboard">
                        <div class="radar-hero__mini-card">
                            <span class="radar-hero__mini-label">Hoy</span>
                            <strong>218</strong>
                            <small>nuevas empresas</small>
                        </div>
                        <div class="radar-hero__mini-card">
                            <span class="radar-hero__mini-label">Esta semana</span>
                            <strong>1.482</strong>
                            <small>oportunidades detectadas</small>
                        </div>
                        <div class="radar-hero__mini-card">
                            <span class="radar-hero__mini-label">Este mes</span>
                            <strong>6.068</strong>
                            <small>leads disponibles</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="radar-section">
        <div class="container">
            <div class="radar-split">
                <div class="radar-split__content">
                    <div class="radar-kicker">Oportunidad comercial</div>
                    <h2 class="radar-title">Las empresas nuevas son las mejores oportunidades comerciales</h2>
                    <p class="radar-text">
                        Durante sus primeros meses de actividad, las empresas recién constituidas suelen contratar proveedores, asesoría, software, seguros, marketing y servicios especializados.
                    </p>
                    <p class="radar-text">
                        Si llegas antes que otros proveedores, tu probabilidad de cerrar una primera venta aumenta significativamente. Radar te permite detectar esas empresas justo en el momento adecuado.
                    </p>
                    <a href="<?= site_url('billing/checkout?plan=radar&period=monthly') ?>" class="radar-btn radar-btn--primary radar-btn--inline">
                        Activar Radar
                    </a>
                </div>

                <div class="radar-timeline">
                    <div class="radar-timeline__item">
                        <div class="radar-timeline__num">1</div>
                        <div>
                            <h3>Nace una nueva empresa</h3>
                            <p>Radar detecta automáticamente la constitución desde fuentes oficiales mercantiles.</p>
                        </div>
                    </div>
                    <div class="radar-timeline__item">
                        <div class="radar-timeline__num">2</div>
                        <div>
                            <h3>Tú la ves antes que otros</h3>
                            <p>Filtras tu nicho por provincia, actividad o fecha y encuentras oportunidades activas.</p>
                        </div>
                    </div>
                    <div class="radar-timeline__item">
                        <div class="radar-timeline__num">3</div>
                        <div>
                            <h3>Exportas y contactas</h3>
                            <p>Descargas los leads en Excel o CSV y empiezas tu prospección comercial ese mismo día.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="radar-section radar-section--soft">
        <div class="container">
            <div class="radar-heading radar-heading--center">
                <div class="radar-kicker">Vista previa real</div>
                <h2 class="radar-title">Ejemplo real del Radar</h2>
                <p class="radar-subtitle">
                    Esto es el tipo de información que tendrías disponible para detectar y trabajar nuevas oportunidades comerciales.
                </p>
            </div>

            <div class="radar-preview">
                <div class="radar-preview__toolbar">
                    <div class="radar-preview__dots">
                        <span></span><span></span><span></span>
                    </div>
                    <div class="radar-preview__filters">
                        <span>Sector: Programación</span>
                        <span>Provincia: Barcelona</span>
                        <span>Periodo: Últimos 7 días</span>
                    </div>
                </div>

                <table class="radar-table">
                    <thead>
                        <tr>
                            <th>Empresa</th>
                            <th>Sector</th>
                            <th>Provincia</th>
                            <th>Fecha constitución</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td data-label="Empresa"><span class="radar-table__company">ACME TECH SL</span></td>
                            <td data-label="Sector"><span class="radar-table__tag">Programación informática</span></td>
                            <td data-label="Provincia"><span class="radar-table__meta">Barcelona</span></td>
                            <td data-label="Fecha"><span class="radar-table__date">05/03/2026</span></td>
                        </tr>
                        <tr>
                            <td data-label="Empresa"><span class="radar-table__company">DATA CONSULTING SL</span></td>
                            <td data-label="Sector"><span class="radar-table__tag">Consultoría</span></td>
                            <td data-label="Provincia"><span class="radar-table__meta">Madrid</span></td>
                            <td data-label="Fecha"><span class="radar-table__date">04/03/2026</span></td>
                        </tr>
                        <tr>
                            <td data-label="Empresa"><span class="radar-table__company">FOOD GROUP SL</span></td>
                            <td data-label="Sector"><span class="radar-table__tag">Hostelería</span></td>
                            <td data-label="Provincia"><span class="radar-table__meta">Valencia</span></td>
                            <td data-label="Fecha"><span class="radar-table__date">04/03/2026</span></td>
                        </tr>
                        <tr>
                            <td data-label="Empresa"><span class="radar-table__company">DIGITAL GROWTH SL</span></td>
                            <td data-label="Sector"><span class="radar-table__tag">Marketing digital</span></td>
                            <td data-label="Provincia"><span class="radar-table__meta">Málaga</span></td>
                            <td data-label="Fecha"><span class="radar-table__date">03/03/2026</span></td>
                        </tr>
                        <tr>
                            <td data-label="Empresa"><span class="radar-table__company">CONSTRUCCIONES NOVA SL</span></td>
                            <td data-label="Sector"><span class="radar-table__tag">Construcción</span></td>
                            <td data-label="Provincia"><span class="radar-table__meta">Sevilla</span></td>
                            <td data-label="Fecha"><span class="radar-table__date">03/03/2026</span></td>
                        </tr>
                    </tbody>
                </table>

                <div class="radar-preview__cta">
                    <a href="<?= site_url('radar') ?>" class="radar-btn radar-btn--ghost">
                        Ver todos los leads en Radar
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="radar-section">
        <div class="container">
            <div class="radar-heading radar-heading--center">
                <div class="radar-kicker">Proceso</div>
                <h2 class="radar-title">Cómo funciona Radar</h2>
                <p class="radar-subtitle">
                    Un flujo simple para detectar oportunidades reales, filtrarlas y convertirlas en prospección accionable.
                </p>
            </div>

            <div class="radar-steps">
                <article class="radar-step">
                    <div class="radar-step__num">1</div>
                    <h3>Detección automática</h3>
                    <p>Radar monitoriza nuevas constituciones mercantiles y las incorpora a tu entorno de trabajo.</p>
                </article>

                <article class="radar-step">
                    <div class="radar-step__num">2</div>
                    <h3>Segmentación inteligente</h3>
                    <p>Filtra por sector CNAE, provincia o fecha para centrarte solo en el tipo de empresa que te interesa.</p>
                </article>

                <article class="radar-step">
                    <div class="radar-step__num">3</div>
                    <h3>Exportación y prospección</h3>
                    <p>Descarga los leads en Excel o CSV y empieza a trabajar campañas comerciales inmediatamente.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="radar-section radar-section--soft">
        <div class="container">
            <div class="radar-heading radar-heading--center">
                <div class="radar-kicker">Incluye</div>
                <h2 class="radar-title">Qué incluye el Radar</h2>
                <p class="radar-subtitle">
                    Todo lo que necesitas para detectar, filtrar y trabajar nuevas empresas como canal de adquisición B2B.
                </p>
            </div>

            <div class="radar-includes">
                <div class="radar-include">
                    <div class="radar-include__icon">✓</div>
                    <span>Empresas nuevas detectadas diariamente</span>
                </div>
                <div class="radar-include">
                    <div class="radar-include__icon">✓</div>
                    <span>Sector CNAE de cada empresa</span>
                </div>
                <div class="radar-include">
                    <div class="radar-include__icon">✓</div>
                    <span>Provincia y municipio</span>
                </div>
                <div class="radar-include">
                    <div class="radar-include__icon">✓</div>
                    <span>Fecha de constitución</span>
                </div>
                <div class="radar-include">
                    <div class="radar-include__icon">✓</div>
                    <span>Filtros por actividad y provincia</span>
                </div>
                <div class="radar-include">
                    <div class="radar-include__icon">✓</div>
                    <span>Exportación a Excel / CSV</span>
                </div>
            </div>
        </div>
    </section>

    <section class="radar-band">
        <div class="container">
            <div class="radar-band__header">
                <div class="radar-kicker radar-kicker--dark">Datos del mercado</div>
                <h2 class="radar-band__title">La base de datos más completa de nuevas empresas en España</h2>
            </div>

            <div class="radar-metrics">
                <div class="radar-metric">
                    <strong>+4,5M</strong>
                    <span>empresas analizadas</span>
                </div>
                <div class="radar-metric">
                    <strong>+200</strong>
                    <span>nuevas empresas detectadas cada día</span>
                </div>
                <div class="radar-metric">
                    <strong>100%</strong>
                    <span>datos oficiales del BORME</span>
                </div>
            </div>
        </div>
    </section>

    <section id="radar-pricing" class="radar-section">
        <div class="container">
            <div class="radar-heading radar-heading--center">
                <div class="radar-kicker">Precio</div>
                <h2 class="radar-title">Plan Radar</h2>
                <p class="radar-subtitle">
                    Acceso mensual completo al radar de nuevas empresas en España, con filtros avanzados y exportación ilimitada.
                </p>
            </div>

            <div class="radar-pricing-wrap">
                <div class="radar-pricing-card">
                    <div class="radar-pricing-card__topbar"></div>

                    <div class="radar-pricing-card__header">
                        <div class="radar-pricing-card__label">Plan Radar</div>
                        <div class="radar-pricing-card__price">79€<span>/mes</span></div>
                        <p>Acceso completo al radar de empresas nuevas en España.</p>
                    </div>

                    <div class="radar-pricing-card__body">
                        <ul class="radar-pricing-list">
                            <li>Acceso completo al Radar</li>
                            <li>Filtros por sector y provincia</li>
                            <li>Exportación ilimitada Excel / CSV</li>
                            <li>Actualización diaria de nuevas empresas</li>
                            <li>Sin permanencia</li>
                        </ul>

                        <a href="<?= site_url('billing/checkout?plan=radar&period=monthly') ?>" class="radar-btn radar-btn--primary radar-btn--full">
                            Activar Radar
                        </a>

                        <div class="radar-pricing-card__footnote">
                            Cancela cuando quieras
                        </div>
                    </div>
                </div>

                <div class="radar-pricing-side">
                    <div class="radar-pricing-side__card">
                        <h3>Ideal para</h3>
                        <ul>
                            <li>Despachos y asesorías</li>
                            <li>Software B2B</li>
                            <li>Marketing y captación comercial</li>
                            <li>Seguros y servicios profesionales</li>
                            <li>Equipos de prospección y ventas</li>
                        </ul>
                    </div>

                    <div class="radar-pricing-side__card radar-pricing-side__card--soft">
                        <h3>Qué consigues</h3>
                        <p>
                            Un canal continuo de nuevas oportunidades comerciales con empresas que acaban de constituirse y todavía no tienen proveedores cerrados.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="radar-section radar-section--soft">
        <div class="container">
            <div class="radar-heading radar-heading--center">
                <div class="radar-kicker">Comparativa</div>
                <h2 class="radar-title">Radar vs Excel puntual</h2>
                <p class="radar-subtitle">
                    Si necesitas captar oportunidades de forma continua, Radar es claramente la opción más rentable.
                </p>
            </div>

            <div class="radar-comparison-wrap">
                <table class="radar-comparison">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="radar-comparison__col-main">Radar mensual</th>
                            <th>Excel puntual</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Actualización</td>
                            <td class="radar-comparison__col-main">Diaria automática</td>
                            <td>Descarga única</td>
                        </tr>
                        <tr>
                            <td>Filtros</td>
                            <td class="radar-comparison__col-main">Avanzados por sector, provincia y fecha</td>
                            <td>Sin filtros dinámicos</td>
                        </tr>
                        <tr>
                            <td>Leads nuevos</td>
                            <td class="radar-comparison__col-main">Cada día</td>
                            <td>Datos estáticos</td>
                        </tr>
                        <tr>
                            <td>Exportación</td>
                            <td class="radar-comparison__col-main">Ilimitada</td>
                            <td>Puntual</td>
                        </tr>
                        <tr>
                            <td>Precio</td>
                            <td class="radar-comparison__col-main"><strong>79€/mes</strong></td>
                            <td>Desde 2€ por listado</td>
                        </tr>
                    </tbody>
                </table>

                <div class="radar-comparison__cta">
                    <a href="<?= site_url('billing/checkout?plan=radar&period=monthly') ?>" class="radar-btn radar-btn--primary">
                        Activar Radar
                    </a>
                    <p>
                        O <a href="<?= site_url('billing/single_checkout?period=30days') ?>">descarga un listado puntual</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="radar-section">
        <div class="container">
            <div class="radar-heading radar-heading--center">
                <div class="radar-kicker">FAQ</div>
                <h2 class="radar-title">Preguntas frecuentes</h2>
            </div>

            <div class="radar-faq">
                <div class="radar-faq__item">
                    <h3>¿De dónde salen los datos?</h3>
                    <p>Los datos se obtienen diariamente del BORME y de registros mercantiles oficiales en España.</p>
                </div>
                <div class="radar-faq__item">
                    <h3>¿Cada cuánto se actualiza el radar?</h3>
                    <p>Radar se actualiza diariamente con todas las nuevas constituciones detectadas.</p>
                </div>
                <div class="radar-faq__item">
                    <h3>¿Puedo cancelar la suscripción?</h3>
                    <p>Sí. Es una suscripción mensual sin permanencia. Puedes cancelarla cuando quieras.</p>
                </div>
                <div class="radar-faq__item">
                    <h3>¿Puedo exportar los leads?</h3>
                    <p>Sí. Puedes exportar los leads filtrados en formato Excel o CSV directamente desde Radar.</p>
                </div>
                <div class="radar-faq__item">
                    <h3>¿Hay permanencia?</h3>
                    <p>No. No existe permanencia ni compromiso de permanencia.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="radar-final">
        <div class="container">
            <div class="radar-final__shell">
                <div class="radar-kicker radar-kicker--dark">Empieza hoy</div>
                <h2>Empieza a detectar nuevas empresas hoy</h2>
                <p>
                    Accede al Radar y convierte nuevas constituciones mercantiles en oportunidades comerciales reales cada día.
                </p>
                <a href="<?= site_url('billing/checkout?plan=radar&period=monthly') ?>" class="radar-btn radar-btn--white">
                    Activar Radar
                </a>
                <small>Sin permanencia · Cancela cuando quieras</small>
            </div>
        </div>
    </section>

</main>

<?= view('partials/footer') ?>
</body>
</html>