<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head', [
        'title' => 'Plugin WordPress Buscador de Empresas | Integración API Registros | APIEmpresas.es',
        'excerptText' => 'Integra el buscador de empresas oficial de APIEmpresas.es en tu WordPress. Plugin no-code, buscador por CIF o Nombre y resultados en tiempo real.',
        'canonical' => site_url('plugin-wordpress-buscador-empresas'),
        'robots' => 'index,follow',
    ]) ?>
    <link rel="stylesheet"
        href="<?= base_url('public/css/precios_radar.css?v=' . (file_exists(FCPATH . 'public/css/precios_radar.css') ? filemtime(FCPATH . 'public/css/precios_radar.css') : time())) ?>" />
    <style>
        /* Estilos específicos para la landing del Plugin WP */
        .wp-hero__badge {
            background: #dcfce7 !important;
            border-color: #bbf7d0 !important;
            color: #166534 !important;
        }

        .wp-hero__badge-dot {
            background: #22c55e !important;
            box-shadow: 0 0 10px rgba(34, 197, 94, 0.35) !important;
        }

        .wp-preview-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            text-align: left;
        }

        .wp-preview-card__header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 16px 20px;
            border-bottom: 1px solid #f1f5f9;
            background: #1d2327;
            /* WordPress Admin Gray */
            color: #fff;
        }

        .wp-preview-card__logo {
            font-size: 20px;
            color: #72aee6;
        }

        .wp-preview-card__body {
            padding: 24px;
            background: #f0f0f1;
        }

        .wp-search-box {
            background: #fff;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #ccd0d4;
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .wp-search-input {
            flex: 1;
            border: 1px solid #8c8f94;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13px;
        }

        .wp-search-btn {
            background: #2271b1;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 4px;
            font-weight: 600;
            font-size: 13px;
        }

        .wp-result-card {
            background: #fff;
            border: 1px solid #ccd0d4;
            border-left: 4px solid #2271b1;
            padding: 15px;
            border-radius: 0 4px 4px 0;
        }

        /* Re-implementing missing API styles for WordPress Landing */
        .api-feature-card {
            background: #fff;
            padding: 32px;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
            text-align: left;
        }

        .api-feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.08);
            border-color: #21759b;
            /* WordPress Blue */
        }

        .api-feature-icon {
            width: 48px;
            height: 48px;
            background: #f0f7ff;
            color: #2271b1;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .api-pricing-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 32px;
            margin-top: 60px;
            align-items: stretch;
        }

        .api-pricing-card {
            background: #ffffff;
            border-radius: 32px;
            border: 1px solid #f1f5f9;
            padding: 40px;
            display: flex;
            flex-direction: column;
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            text-align: left;
        }

        .api-pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.08);
        }

        .api-pricing-card.featured {
            transform: scale(1.05);
            z-index: 10;
        }

        .api-pricing-card.free-plan {
            background: linear-gradient(180deg, #5b6278 0%, #555c73 100%);
        }

        .api-pricing-card.featured {
            background: linear-gradient(180deg, #4f46e5 0%, #4c44dc 100%);
        }

        .api-pricing-card.business-plan {
            background: linear-gradient(180deg, #5ea083 0%, #57997c 100%);
        }

        .api-pricing-card.free-plan h3,
        .api-pricing-card.featured h3,
        .api-pricing-card.business-plan h3,
        .api-pricing-card.free-plan .api-price-value,
        .api-pricing-card.featured .api-price-value,
        .api-pricing-card.business-plan .api-price-value,
        .api-pricing-card.free-plan .api-pricing-card__desc,
        .api-pricing-card.featured .api-pricing-card__desc,
        .api-pricing-card.business-plan .api-pricing-card__desc,
        .api-pricing-card.free-plan .api-price-list li,
        .api-pricing-card.featured .api-price-list li,
        .api-pricing-card.business-plan .api-price-list li {
            color: #ffffff !important;
        }

        .api-pricing-card__header h3 {
            font-size: 1.25rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .api-price-value {
            font-size: 3.5rem;
            font-weight: 950;
            margin: 20px 0;
            letter-spacing: -0.05em;
            display: flex;
            align-items: baseline;
        }

        .api-price-value span {
            font-size: 1.125rem;
            margin-left: 4px;
            opacity: 0.7;
        }

        .api-pricing-card__desc {
            font-size: 0.95rem;
            line-height: 1.6;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .api-price-list {
            list-style: none;
            padding: 24px 0 0;
            margin: 0 0 32px;
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            flex-grow: 1;
        }

        .api-price-list li {
            padding: 10px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
            font-weight: 600;
        }

        .api-pricing-btn {
            width: 100%;
            padding: 16px;
            border-radius: 14px;
            font-weight: 800;
            text-align: center;
            text-decoration: none;
            background: #fff;
            color: #1f2937;
            transition: all 0.3s;
        }

        .api-pricing-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }

        .api-faq {
            margin-top: 40px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
            text-align: left;
        }

        .api-faq-item {
            background: #fff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            margin-bottom: 12px;
            overflow: hidden;
        }

        .api-faq-question {
            padding: 20px 24px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: none;
            border: none;
            font-size: 1.05rem;
            font-weight: 800;
            cursor: pointer;
            text-align: left;
        }

        .api-faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s ease;
            background: #f8fafc;
        }

        .api-faq-answer-inner {
            padding: 0 24px 24px;
            font-size: 0.95rem;
            color: #475569;
            line-height: 1.6;
        }

        .api-faq-item.active .api-faq-answer {
            max-height: 300px;
        }

        .api-faq-icon {
            font-size: 1.4rem;
            color: #94a3b8;
            transition: transform 0.2s;
        }

        .api-faq-item.active .api-faq-icon {
            transform: rotate(45deg);
            color: #2271b1;
        }

        @media (max-width: 1024px) {
            .api-pricing-grid {
                grid-template-columns: 1fr;
                gap: 24px;
            }

            .api-pricing-card.featured {
                transform: none;
            }
        }
    </style>
</head>

<body>
    <?= view('partials/header') ?>

    <main class="radar-page">

        <!-- HERO SECTION -->
        <section class="radar-hero">
            <div class="container">
                <div class="radar-hero__shell">
                    <div class="radar-hero__badge wp-hero__badge">
                        <span class="radar-hero__badge-dot wp-hero__badge-dot"></span>
                        NUEVO PLUGIN WP DISPONIBLE
                    </div>

                    <h1 class="radar-hero__title"
                        style="font-size: clamp(2rem, 5vw, 3.5rem); line-height: 1.1; margin-bottom: 1.5rem;">
                        Convierte tu WordPress en una herramienta de<br>
                        <span style="color: #2563eb;">datos empresariales en 1 minuto</span>
                    </h1>

                    <p style="font-size: 1.25rem; font-weight: 700; color: #1e293b; margin-bottom: 1rem;">
                        Convierte tu web en una herramienta que aporta valor real a tus usuarios y aumenta el engagement
                        o las oportunidades de negocio sin desarrollar nada.
                    </p>

                    <p class="radar-hero__subtitle"
                        style="font-size: 1.1rem; color: #475569; max-width: 800px; margin-left: auto; margin-right: auto; line-height: 1.6;">
                        Empieza hoy y convierte tu web en una herramienta que genera valor real para tus usuarios desde
                        el primer minuto.
                    </p>

                    <div class="radar-hero__proof"
                        style="margin-top: 2.5rem; display: flex; justify-content: center; gap: 40px;">
                        <div class="radar-hero__proof-item">
                            <strong style="color: #0f172a;">No-Code</strong>
                            <span style="color: #64748b;">Sin programar nada</span>
                        </div>
                        <div class="radar-hero__proof-item">
                            <strong style="color: #0f172a;">Nativo WP</strong>
                            <span style="color: #64748b;">Gutenberg Ready</span>
                        </div>
                        <div class="radar-hero__proof-item">
                            <strong style="color: #0f172a;">Sync Total</strong>
                            <span style="color: #64748b;">Escala tu plan</span>
                        </div>
                    </div>

                    <div class="radar-hero__actions"
                        style="margin-top: 2.5rem; display: flex; flex-direction: column; align-items: center; gap: 16px;">
                        <a href="<?= site_url('obtener-plugin-wordpress') ?>"
                            class="radar-btn radar-btn--primary js-track-wp-cta"
                            style="padding: 18px 40px; font-weight: 800;">
                            Instalar plugin y empezar ahora
                        </a>
                        <div style="display: flex; flex-direction: column; gap: 4px;">
                            <p style="font-size: 0.85rem; color: #2563eb; font-weight: 800; margin: 0;">Activa datos
                                reales en tu web en menos de 60 segundos</p>
                            <p style="font-size: 0.8rem; color: #94a3b8; font-weight: 500; margin: 0;">Usado por
                                agencias, SaaS y directorios para generar valor en sus webs</p>
                        </div>
                    </div>

                    <!-- BLOQUE SIN FRICCIÓN -->
                    <div style="margin-top: 40px; text-align: center;">
                        <div
                            style="display: inline-flex; gap: 32px; padding: 16px 32px; background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                            <span
                                style="font-size: 0.95rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;"><span
                                    style="color: #2563eb;">✔</span> Sin backend</span>
                            <span
                                style="font-size: 0.95rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;"><span
                                    style="color: #2563eb;">✔</span> Sin configuración compleja</span>
                            <span
                                style="font-size: 0.95rem; font-weight: 800; color: #0f172a; display: flex; align-items: center; gap: 8px;"><span
                                    style="color: #2563eb;">✔</span> Sin mantenimiento</span>
                        </div>
                    </div>

                    <!-- BLOQUE VALOR INMEDIATO -->
                    <div style="margin-top: 40px; text-align: center;">
                        <div
                            style="display: inline-flex; gap: 24px; padding: 12px 24px; background: #f0f7ff; border-radius: 99px; border: 1px solid #dbeafe;">
                            <span style="font-size: 0.9rem; font-weight: 700; color: #1e40af;">Qué consigues al
                                instalarlo:</span>
                            <span style="font-size: 0.9rem; color: #1e40af;">• Más valor percibido</span>
                            <span style="font-size: 0.9rem; color: #1e40af;">• Más interacción</span>
                            <span style="font-size: 0.9rem; color: #1e40af;">• Monetización de datos</span>
                        </div>
                    </div>

                    <!-- BLOQUE CASOS DE USO -->
                    <div
                        style="margin-top: 60px; padding: 40px; background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; text-align: left; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.05);">
                        <p
                            style="color: #2563eb; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 30px; text-align: center;">
                            Cómo lo usan nuestros clientes</p>
                        <div
                            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 30px;">
                            <div style="display: flex; align-items: flex-start; gap: 16px;">
                                <div style="font-size: 2rem;">📁</div>
                                <div>
                                    <div style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">
                                        Directorios</div>
                                    <div style="font-size: 0.85rem; color: #64748b; line-height: 1.4;">Aumentan el
                                        tiempo en página y la retención automáticamente.</div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 16px;">
                                <div style="font-size: 2rem;">🎯</div>
                                <div>
                                    <div style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">
                                        Agencias</div>
                                    <div style="font-size: 0.85rem; color: #64748b; line-height: 1.4;">Generan leads
                                        cualificados de forma totalmente pasiva.</div>
                                </div>
                            </div>
                            <div style="display: flex; align-items: flex-start; gap: 16px;">
                                <div style="font-size: 2rem;">⚡</div>
                                <div>
                                    <div style="font-size: 1rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">
                                        SaaS</div>
                                    <div style="font-size: 0.85rem; color: #64748b; line-height: 1.4;">Integran datos
                                        sin backend y escalan su producto rápido.</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="radar-hero__feature-panel" style="max-width: 1040px; margin-top: 60px;">
                        <div class="radar-hero__feature-copy">
                            <h2 style="font-size: 2rem; font-weight: 850; letter-spacing: -0.02em;">El buscador oficial
                                ahora en tu CMS favorito</h2>
                            <ul style="display: grid; gap: 16px;">
                                <li><strong>Integración por API Key:</strong> Solo tienes que descargar el plugin,
                                    activar tu clave y el buscador estará listo para usarse.</li>
                                <li><strong>Búsqueda por CIF y Nombre:</strong> Permite a tus usuarios localizar
                                    cualquier empresa española por su identificador fiscal o razón social.</li>
                                <li><strong>Resultados Inteligentes:</strong> El plugin muestra automáticamente los
                                    datos permitidos por tu suscripción (Free, Pro o Business).</li>
                            </ul>
                        </div>

                        <div class="wp-preview-card">
                            <div class="wp-preview-card__header">
                                <span class="wp-preview-card__logo">WP</span>
                                <div style="font-size: 13px; font-weight: 700;">Vista previa del Plugin</div>
                            </div>

                            <div class="wp-preview-card__body">
                                <div class="wp-search-box">
                                    <div class="wp-search-input">B12345678</div>
                                    <div class="wp-search-btn">Buscar</div>
                                </div>

                                <div class="wp-result-card">
                                    <div style="font-size: 12px; font-weight: 700; color: #1d2327; margin-bottom: 5px;">
                                        TECH FLOW SOLUTIONS SL</div>
                                    <div style="font-size: 11px; color: #64748b;">MADRID • CNAE: 6201</div>
                                    <div style="margin-top: 10px; display: flex; gap: 5px;">
                                        <span
                                            style="background: #dcfce7; color: #166534; font-size: 9px; padding: 2px 6px; border-radius: 4px; font-weight: 700;">ACTIVA</span>
                                        <span
                                            style="background: #e0f2fe; color: #0369a1; font-size: 9px; padding: 2px 6px; border-radius: 4px; font-weight: 700;">IO:
                                            94/100</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- VALUE HOOKS -->
        <section id="funcionalidades" class="radar-section">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">Tu web con superpoderes</div>
                    <h2 class="radar-title">Diseñado para profesionales de WordPress</h2>
                    <p class="radar-subtitle">
                        No importa si eres una agencia, un directorio o un blog especializado. Nuestro plugin es la
                        herramienta definitiva de datos mercantiles.
                    </p>
                </div>

                <div class="radar-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 24px;">
                    <div class="api-feature-card" style="padding: 24px;">
                        <div class="api-feature-icon" style="margin-bottom: 15px;">✔</div>
                        <h3 style="font-size: 1.1rem; margin-bottom: 10px;">Añade valor real a tu web</h3>
                        <p class="radar-text" style="font-size: 0.9rem;">Ofrece información valiosa del Registro
                            Mercantil que tus usuarios realmente necesitan consultar.</p>
                    </div>
                    <div class="api-feature-card" style="padding: 24px;">
                        <div class="api-feature-icon" style="margin-bottom: 15px;">✔</div>
                        <h3 style="font-size: 1.1rem; margin-bottom: 10px;">Aumenta engagement</h3>
                        <p class="radar-text" style="font-size: 0.9rem;">Mantén a tus visitantes más tiempo en tu sitio
                            proporcionándoles las herramientas que buscan.</p>
                    </div>
                    <div class="api-feature-card" style="padding: 24px;">
                        <div class="api-feature-icon" style="margin-bottom: 15px;">✔</div>
                        <h3 style="font-size: 1.1rem; margin-bottom: 10px;">Integra datos oficiales</h3>
                        <p class="radar-text" style="font-size: 0.9rem;">Olvídate de APIs complejas. Instalación no-code
                            lista para funcionar en menos de 60s.</p>
                    </div>
                    <div class="api-feature-card" style="padding: 24px;">
                        <div class="api-feature-icon" style="margin-bottom: 15px;">✔</div>
                        <h3 style="font-size: 1.1rem; margin-bottom: 10px;">Escala con tu plan</h3>
                        <p class="radar-text" style="font-size: 0.9rem;">A medida que tu negocio crezca, el plugin se
                            adapta a tus nuevas necesidades sin cambios.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- PRICING SECTION -->
        <section id="planes" class="radar-section radar-section--soft">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">Plugin Gratuito</div>
                    <h2 class="radar-title">Empieza gratis y escala cuando tu web empiece a generar valor real</h2>
                    <p class="radar-subtitle">
                        El plugin es gratuito. Solo pagas por el uso de datos según tu plan activo.
                    </p>
                </div>

                <div class="api-pricing-grid">

                    <!-- FREE -->
                    <div class="api-pricing-card free-plan">
                        <div class="api-pricing-card__header">
                            <h3>Free</h3>
                        </div>
                        <div class="api-price-value">0€<span>/ mes</span></div>
                        <p class="api-pricing-card__desc">Para blogs y webs con poco tráfico que necesitan validación
                            básica de empresas.</p>

                        <ul class="api-price-list">
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Buscador básico</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Datos públicos oficiales</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> 15 búsquedas / mes</li>
                        </ul>

                        <a href="<?= site_url('obtener-plugin-wordpress') ?>"
                            class="api-pricing-btn js-track-wp-cta">Empezar con Free</a>
                    </div>

                    <!-- PRO -->
                    <div class="api-pricing-card featured">
                        <div class="api-pricing-card__header">
                            <h3>Pro</h3>
                        </div>
                        <div class="api-price-value">19€<span>/ mes</span></div>
                        <p class="api-pricing-card__desc">Para webs que quieren crecer y monetizar su tráfico. Más
                            consultas → más valor.</p>

                        <ul class="api-price-list">
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Buscador Avanzado</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Scoring IA Incluido</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> 3.000 búsquedas / mes</li>
                        </ul>

                        <a href="<?= site_url('obtener-plugin-wordpress') ?>"
                            class="api-pricing-btn primary js-track-wp-cta">Activar Plan Pro</a>
                    </div>

                    <!-- BUSINESS -->
                    <div class="api-pricing-card business-plan">
                        <div class="api-pricing-card__header">
                            <h3>Business</h3>
                        </div>
                        <div class="api-price-value">49€<span>/ mes</span></div>
                        <p class="api-pricing-card__desc">Infraestructura masiva para intranets y sistemas corporativos
                            CRM basados en WordPress.</p>

                        <ul class="api-price-list">
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Datos Ilimitados (SLA)</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Soporte Prioritario WP</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> 10.000 búsquedas / mes</li>
                        </ul>

                        <a href="<?= site_url('obtener-plugin-wordpress') ?>"
                            class="api-pricing-btn js-track-wp-cta">Activar Business</a>
                    </div>
                </div>

                <!-- BLOQUE DE ESCALADO PRO -->
                <div style="margin-top: 80px; position: relative;">
                    <div
                        style="background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%); padding: 48px; border-radius: 32px; border: 1px solid #e2e8f0; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02); text-align: center;">
                        <div
                            style="display: inline-block; background: #f0f7ff; color: #2563eb; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.1em; padding: 6px 16px; border-radius: 99px; margin-bottom: 24px;">
                            Ecosistema de Crecimiento
                        </div>
                        <h3
                            style="font-size: 1.5rem; font-weight: 850; color: #0f172a; margin-bottom: 16px; letter-spacing: -0.02em;">
                            A medida que crece tu web, desbloquea:</h3>

                        <div
                            style="display: flex; justify-content: center; gap: 24px; flex-wrap: wrap; margin-top: 32px;">
                            <!-- Benefit 1 -->
                            <div
                                style="background: #fff; border: 1px solid #f1f5f9; padding: 12px 24px; border-radius: 16px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
                                <div
                                    style="width: 32px; height: 32px; background: #eff6ff; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path
                                            d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4a2 2 0 0 0 1-1.73z">
                                        </path>
                                    </svg>
                                </div>
                                <span style="font-weight: 700; color: #475569; font-size: 1rem;">Más consultas</span>
                            </div>

                            <!-- Benefit 2 -->
                            <div
                                style="background: #fff; border: 1px solid #f1f5f9; padding: 12px 24px; border-radius: 16px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
                                <div
                                    style="width: 32px; height: 32px; background: #fef2f2; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ef4444"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline>
                                        <polyline points="16 7 22 7 22 13"></polyline>
                                    </svg>
                                </div>
                                <span style="font-weight: 700; color: #475569; font-size: 1rem;">Datos avanzados</span>
                            </div>

                            <!-- Benefit 3 -->
                            <div
                                style="background: #fff; border: 1px solid #f1f5f9; padding: 12px 24px; border-radius: 16px; display: flex; align-items: center; gap: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.03);">
                                <div
                                    style="width: 32px; height: 32px; background: #ecfdf5; border-radius: 8px; display: flex; align-items: center; justify-content: center;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981"
                                        stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="12" y1="8" x2="12" y2="12"></line>
                                        <line x1="12" y1="16" x2="12.01" y2="16"></line>
                                    </svg>
                                </div>
                                <span style="font-weight: 700; color: #475569; font-size: 1rem;">Scoring de
                                    empresas</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- BLOQUE RADAR (CROSS-SELL) -->
        <section class="radar-section">
            <div class="container">
                <div
                    style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-radius: 32px; padding: 60px; color: #fff; text-align: center; position: relative; overflow: hidden;">
                    <div
                        style="position: absolute; top: -20%; right: -10%; width: 40%; height: 60%; background: radial-gradient(circle, rgba(59, 130, 246, 0.2) 0%, transparent 70%); filter: blur(40px);">
                    </div>
                    <div style="position: relative; z-index: 2;">
                        <h2 style="font-size: 2.2rem; font-weight: 850; margin-bottom: 16px;">Estas empresas están
                            buscando proveedor ahora mismo</h2>
                        <p
                            style="font-size: 1.2rem; color: #94a3b8; max-width: 650px; margin: 0 auto 12px; line-height: 1.6;">
                            Cada día nuevas empresas aparecen — si no contactas tú, otro proveedor lo hará primero.
                        </p>
                        <p style="font-size: 0.85rem; color: #60a5fa; font-weight: 700; margin-bottom: 32px;">
                            Actualizado en tiempo real con datos del BORME</p>
                        <a href="<?= site_url('radar/preview') ?>" class="radar-btn radar-btn--primary"
                            style="padding: 16px 40px; font-size: 1.1rem;">Ver empresas activas ahora</a>
                    </div>
                </div>
            </div>
        </section>
        ...

        <!-- MONETIZATION HOOK -->
        <section class="radar-section radar-section--soft">
            <div class="container">
                <div
                    style="background: #fff; border: 1px solid #e2e8f0; border-radius: 32px; padding: 40px; display: flex; align-items: center; justify-content: space-between; gap: 40px; flex-wrap: wrap;">
                    <div style="flex: 1; min-width: 300px;">
                        <h2 style="font-size: 1.8rem; font-weight: 850; color: #0f172a; margin-bottom: 12px;">¿Necesitas
                            más?</h2>
                        <p style="font-size: 1.1rem; color: #64748b; margin: 0;">Activa un plan y desbloquea más
                            consultas, datos avanzados y funcionalidades adicionales.</p>
                    </div>
                    <a href="<?= site_url('api-empresas') ?>" class="radar-btn radar-btn--primary"
                        style="padding: 16px 32px;">Ver planes y precios</a>
                </div>
            </div>
        </section>

        <!-- FAQ SECTION -->
        <section class="radar-section">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">Preguntas de WordPress</div>
                    <h2 class="radar-title">Dudas frecuentes sobre el Plugin</h2>
                </div>

                <div class="api-faq">
                    <div class="api-faq-item active">
                        <button class="api-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>¿Es compatible con Gutenberg y Elementor?</span>
                            <span class="api-faq-icon">+</span>
                        </button>
                        <div class="api-faq-answer">
                            <div class="api-faq-answer-inner">
                                Sí, el plugin incluye un Block nativo para Gutenberg y un Shortcode universal que puedes
                                pegar en cualquier maquetador visual como Elementor, Divi o WPBakery.
                            </div>
                        </div>
                    </div>

                    <div class="api-faq-item">
                        <button class="api-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>¿El plugin tiene coste por descarga?</span>
                            <span class="api-faq-icon">+</span>
                        </button>
                        <div class="api-faq-answer">
                            <div class="api-faq-answer-inner">
                                No, el plugin es gratuito. El uso se rige por el plan de suscripción que tengas en
                                APIEmpresas (incluido el plan gratuito de <?= $freeLimit ?> consultas).
                            </div>
                        </div>
                    </div>

                    <div class="api-faq-item">
                        <button class="api-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>¿Puedo personalizar el diseño del buscador?</span>
                            <span class="api-faq-icon">+</span>
                        </button>
                        <div class="api-faq-answer">
                            <div class="api-faq-answer-inner">
                                El plugin está diseñado para ser mínimamente intrusivo y heredar el CSS de tu tema, pero
                                también ofrece opciones en el panel de administración para ajustar los colores
                                principales y el radio de los bordes.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FINAL CTA -->
        <section style="padding: 100px 0 120px;">
            <div class="container">
                <div
                    style="background: #133A82; border-radius: 40px; padding: 100px 60px; text-align: center; position: relative; overflow: hidden; box-shadow: 0 40px 80px -20px rgba(19, 58, 130, 0.45);">

                    <!-- Vibrant Blue Gradient Overlay -->
                    <div
                        style="position: absolute; inset: 0; background: linear-gradient(135deg, #133A82 0%, #1d4ed8 40%, #0369a1 100%); z-index: 1;">
                    </div>

                    <!-- Technical Grid Pattern -->
                    <div
                        style="position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px); background-size: 40px 40px; opacity: 0.3; z-index: 2;">
                    </div>

                    <!-- Subtle Mesh Lights -->
                    <div
                        style="position: absolute; top: -20%; right: -10%; width: 60%; height: 60%; background: radial-gradient(circle, rgba(99,179,237,0.4) 0%, transparent 70%); filter: blur(60px); z-index: 3;">
                    </div>
                    <div
                        style="position: absolute; bottom: -20%; left: -10%; width: 50%; height: 50%; background: radial-gradient(circle, rgba(16,185,129,0.3) 0%, transparent 70%); filter: blur(60px); z-index: 3;">
                    </div>

                    <div style="position: relative; z-index: 10;">
                        <h2
                            style="color: #ffffff; font-size: clamp(1.8rem, 5vw, 2.5rem); font-weight: 950; margin: 0 0 20px; letter-spacing: -0.04em; line-height: 1.1;">
                            Activa datos empresariales en tu web en menos de 1 minuto
                        </h2>
                        <p
                            style="color: rgba(255,255,255,0.75); font-size: 1.15rem; font-weight: 500; margin: 0 0 52px; max-width: 520px; margin-left: auto; margin-right: auto; line-height: 1.7;">
                            Descarga el buscador oficial y empieza a mostrar datos del Registro Mercantil con la máxima
                            confianza en tu sitio web.
                        </p>

                        <div style="display: flex; flex-direction: column; align-items: center; gap: 24px;">
                            <a href="<?= site_url('obtener-plugin-wordpress') ?>" class="js-track-wp-cta"
                                style="display: inline-block; background: #ffffff; color: #133A82; font-weight: 900; font-size: 1.2rem; padding: 22px 64px; border-radius: 18px; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                                Instalar plugin y empezar ahora
                            </a>

                            <div style="display: flex; gap: 24px; flex-wrap: wrap; justify-content: center;">
                                <span
                                    style="color: #fff; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                                    <span style="color: #4ade80;">✔</span> Sin configuración técnica
                                </span>
                                <span
                                    style="color: #fff; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                                    <span style="color: #4ade80;">✔</span> Compatible con Elementor y Gutenberg
                                </span>
                                <span
                                    style="color: #fff; font-size: 0.95rem; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                                    <span style="color: #4ade80;">✔</span> Instalación en 60 segundos
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <!-- PRO MODAL: COMING SOON -->
    <div id="wp-coming-soon-modal"
        style="display: none; position: fixed; inset: 0; z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
        <!-- Backdrop -->
        <div style="position: absolute; inset: 0; background: rgba(15, 23, 42, 0.8); backdrop-filter: blur(8px);"></div>

        <!-- Modal Card -->
        <div
            style="position: relative; background: #fff; width: 100%; max-width: 500px; border-radius: 32px; padding: 48px 40px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); text-align: center; overflow: hidden;">
            <!-- Decoration -->
            <div
                style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px; background: radial-gradient(circle, rgba(37, 99, 235, 0.1) 0%, transparent 70%);">
            </div>

            <div
                style="width: 80px; height: 80px; background: #f0f7ff; border-radius: 24px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px;">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"
                    stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
                </svg>
            </div>

            <h3
                style="font-size: 1.75rem; font-weight: 850; color: #0f172a; margin-bottom: 16px; letter-spacing: -0.02em;">
                ¡Estamos casi listos!</h3>

            <p style="font-size: 1.1rem; color: #475569; line-height: 1.6; margin-bottom: 32px;">
                Estamos ultimando los detalles para que tu experiencia con el plugin de <strong>APIEmpresas</strong> sea
                perfecta. Estará disponible para descarga en los próximos días.
            </p>

            <button onclick="document.getElementById('wp-coming-soon-modal').style.display = 'none'"
                style="width: 100%; background: #2563eb; color: #fff; font-weight: 800; padding: 18px; border-radius: 16px; border: none; cursor: pointer; transition: all 0.2s ease; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.25);"
                onmouseover="this.style.background='#1d4ed8'; this.style.transform='translateY(-2px)'"
                onmouseout="this.style.background='#2563eb'; this.style.transform='translateY(0)'">
                Entendido, avisadme pronto
            </button>

            <p style="font-size: 0.9rem; color: #94a3b8; margin-top: 24px; font-weight: 500;">
                Gracias por tu interés en profesionalizar tu WordPress.
            </p>
        </div>
    </div>

    <?= view('partials/footer') ?>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctaButtons = document.querySelectorAll('.js-track-wp-cta');
            const modal = document.getElementById('wp-coming-soon-modal');

            ctaButtons.forEach(btn => {
                btn.addEventListener('click', function (e) {
                    e.preventDefault(); // Prevent navigation as it's not ready

                    const label = this.innerText.trim();

                    // Track event
                    fetch('<?= site_url('tracking/radar-demo-event') ?>', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            event_type: 'click_cta_coming_soon',
                            source: 'marketing_wp_plugin',
                            page: 'plugin_wp_marketing',
                            cta_label: label,
                            url: this.getAttribute('href')
                        })
                    })
                        .then(res => res.json())
                        .then(data => {
                            console.log('Marketing event tracked (Coming Soon):', label);
                            // Show modal
                            modal.style.display = 'flex';
                        })
                        .catch(err => {
                            console.error('Error tracking marketing event:', err);
                            // Show modal anyway so user gets feedback
                            modal.style.display = 'flex';
                        });
                });
            });
        });
    </script>
</body>

</html>