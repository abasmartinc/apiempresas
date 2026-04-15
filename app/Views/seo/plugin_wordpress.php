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
            background: #1d2327; /* WordPress Admin Gray */
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
            border-color: #21759b; /* WordPress Blue */
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

        .api-pricing-card.free-plan { background: linear-gradient(180deg, #5b6278 0%, #555c73 100%); }
        .api-pricing-card.featured { background: linear-gradient(180deg, #4f46e5 0%, #4c44dc 100%); }
        .api-pricing-card.business-plan { background: linear-gradient(180deg, #5ea083 0%, #57997c 100%); }

        .api-pricing-card.free-plan h3, .api-pricing-card.featured h3, .api-pricing-card.business-plan h3,
        .api-pricing-card.free-plan .api-price-value, .api-pricing-card.featured .api-price-value, .api-pricing-card.business-plan .api-price-value,
        .api-pricing-card.free-plan .api-pricing-card__desc, .api-pricing-card.featured .api-pricing-card__desc, .api-pricing-card.business-plan .api-pricing-card__desc,
        .api-pricing-card.free-plan .api-price-list li, .api-pricing-card.featured .api-price-list li, .api-pricing-card.business-plan .api-price-list li {
            color: #ffffff !important;
        }

        .api-pricing-card__header h3 { font-size: 1.25rem; font-weight: 800; margin-bottom: 8px; }

        .api-price-value {
            font-size: 3.5rem;
            font-weight: 950;
            margin: 20px 0;
            letter-spacing: -0.05em;
            display: flex;
            align-items: baseline;
        }

        .api-price-value span { font-size: 1.125rem; margin-left: 4px; opacity: 0.7; }

        .api-pricing-card__desc { font-size: 0.95rem; line-height: 1.6; margin-bottom: 30px; opacity: 0.9; }

        .api-price-list {
            list-style: none;
            padding: 24px 0 0;
            margin: 0 0 32px;
            border-top: 1px solid rgba(255,255,255,0.1);
            flex-grow: 1;
        }

        .api-price-list li { padding: 10px 0; display: flex; align-items: center; gap: 12px; font-size: 0.95rem; font-weight: 600; }

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

        .api-pricing-btn:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(0,0,0,0.1); }

        .api-faq { margin-top: 40px; max-width: 800px; margin-left: auto; margin-right: auto; text-align: left; }
        .api-faq-item { background: #fff; border: 1px solid #e2e8f0; border-radius: 20px; margin-bottom: 12px; overflow: hidden; }
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
        .api-faq-answer { max-height: 0; overflow: hidden; transition: max-height 0.3s ease; background: #f8fafc; }
        .api-faq-answer-inner { padding: 0 24px 24px; font-size: 0.95rem; color: #475569; line-height: 1.6; }
        .api-faq-item.active .api-faq-answer { max-height: 300px; }
        .api-faq-icon { font-size: 1.4rem; color: #94a3b8; transition: transform 0.2s; }
        .api-faq-item.active .api-faq-icon { transform: rotate(45deg); color: #2271b1; }

        @media (max-width: 1024px) {
            .api-pricing-grid { grid-template-columns: 1fr; gap: 24px; }
            .api-pricing-card.featured { transform: none; }
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

                    <h1 class="radar-hero__title">
                        Buscador de Empresas para<br>
                        <span>tu WordPress en 1 minuto.</span>
                    </h1>

                    <p class="radar-hero__subtitle">
                        Integra toda la potencia de APIEmpresas en tu web sin programar una sola línea de código. Ofrece a tus visitantes consultas del Registro Mercantil con un buscador nativo y profesional.
                    </p>

                    <div class="radar-hero__proof">
                        <div class="radar-hero__proof-item">
                            <strong>No-Code</strong>
                            <span>Instalar y usar</span>
                        </div>
                        <div class="radar-hero__proof-item">
                            <strong>Nativo WP</strong>
                            <span>Gutenberg Ready</span>
                        </div>
                        <div class="radar-hero__proof-item">
                            <strong>Sync Total</strong>
                            <span>Según tu plan</span>
                        </div>
                    </div>

                        <div class="radar-hero__actions">
                            <a href="<?= site_url('obtener-plugin-wordpress') ?>" class="radar-btn radar-btn--primary js-track-wp-cta">
                                Descargar Plugin Gratis
                            </a>
                            <a href="#funcionalidades" class="radar-btn radar-btn--ghost">
                                Ver funcionalidades
                            </a>
                        </div>

                <div class="radar-hero__feature-panel" style="max-width: 1040px; margin-top: 40px;">
                        <div class="radar-hero__feature-copy">
                            <h2>El buscador oficial ahora en tu CMS favorito</h2>
                            <ul style="display: grid; gap: 16px;">
                                <li><strong>Integración por API Key:</strong> Solo tienes que descargar el plugin, activar tu clave y el buscador estará listo para usarse.</li>
                                <li><strong>Búsqueda por CIF y Nombre:</strong> Permite a tus usuarios localizar cualquier empresa española por su identificador fiscal o razón social.</li>
                                <li><strong>Resultados Inteligentes:</strong> El plugin muestra automáticamente los datos permitidos por tu suscripción (Free, Pro o Business).</li>
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
                                    <div style="font-size: 12px; font-weight: 700; color: #1d2327; margin-bottom: 5px;">TECH FLOW SOLUTIONS SL</div>
                                    <div style="font-size: 11px; color: #64748b;">MADRID • CNAE: 6201</div>
                                    <div style="margin-top: 10px; display: flex; gap: 5px;">
                                        <span style="background: #dcfce7; color: #166534; font-size: 9px; padding: 2px 6px; border-radius: 4px; font-weight: 700;">ACTIVA</span>
                                        <span style="background: #e0f2fe; color: #0369a1; font-size: 9px; padding: 2px 6px; border-radius: 4px; font-weight: 700;">IO: 94/100</span>
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
                        No importa si eres una agencia, un directorio o un blog especializado. Nuestro plugin es la herramienta definitiva de datos mercantiles.
                    </p>
                </div>

                <div class="radar-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px;">
                    <div class="api-feature-card">
                        <div class="api-feature-icon">🔌</div>
                        <h3>Zero Configuration</h3>
                        <p class="radar-text">Olvídate de configurar bases de datos o escribir código Curl. Instala, pega tu token y publica el bloque del buscador.</p>
                    </div>
                    <div class="api-feature-card">
                        <div class="api-feature-icon">🎨</div>
                        <h3>Estilos Flexibles</h3>
                        <p class="radar-text">El buscador hereda la tipografía y colores de tu tema de WordPress para una integración visual perfecta y fluida.</p>
                    </div>
                    <div class="api-feature-card">
                        <div class="api-feature-icon">⚖️</div>
                        <h3>Escalado de Datos</h3>
                        <p class="radar-text">Si pasas de un plan Free a Pro, el plugin desbloquea automáticamente el scoring IA y los datos extendidos en tu web.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- PRICING SECTION -->
        <section id="planes" class="radar-section radar-section--soft">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">Plugin Gratuito</div>
                    <h2 class="radar-title">Usa el Plugin con cualquier plan</h2>
                    <p class="radar-subtitle">
                        El plugin no tiene coste adicional. Se rige por las capacidades del plan que tengas activo en APIEmpresas.
                    </p>
                </div>

                <div class="api-pricing-grid">

                    <!-- FREE -->
                    <div class="api-pricing-card free-plan">
                        <div class="api-pricing-card__header">
                            <h3>Free</h3>
                        </div>
                        <div class="api-price-value">0€<span>/ mes</span></div>
                        <p class="api-pricing-card__desc">Para blogs y webs con poco tráfico que necesitan validación básica de empresas.</p>

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
                                </svg> 100 búsquedas / mes</li>
                        </ul>

                        <a href="<?= site_url('obtener-plugin-wordpress') ?>" class="api-pricing-btn js-track-wp-cta">Empezar con Free</a>
                    </div>

                    <!-- PRO -->
                    <div class="api-pricing-card featured">
                        <div class="api-pricing-card__header">
                            <h3>Pro</h3>
                        </div>
                        <div class="api-price-value">19€<span>/ mes</span></div>
                        <p class="api-pricing-card__desc">Perfecto para portales de noticias o directorios que quieren mostrar scoring de empresas.</p>

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

                        <a href="<?= site_url('obtener-plugin-wordpress') ?>" class="api-pricing-btn primary js-track-wp-cta">Activar Plan Pro</a>
                    </div>

                    <!-- BUSINESS -->
                    <div class="api-pricing-card business-plan">
                        <div class="api-pricing-card__header">
                            <h3>Business</h3>
                        </div>
                        <div class="api-price-value">49€<span>/ mes</span></div>
                        <p class="api-pricing-card__desc">Infraestructura masiva para intranets y sistemas corporativos CRM basados en WordPress.</p>

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

                        <a href="<?= site_url('obtener-plugin-wordpress') ?>" class="api-pricing-btn js-track-wp-cta">Activar Business</a>
                    </div>
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
                                Sí, el plugin incluye un Block nativo para Gutenberg y un Shortcode universal que puedes pegar en cualquier maquetador visual como Elementor, Divi o WPBakery.
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
                                No, el plugin es gratuito. El uso se rige por el plan de suscripción que tengas en APIEmpresas (incluido el plan gratuito de 100 consultas).
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
                                El plugin está diseñado para ser mínimamente intrusivo y heredar el CSS de tu tema, pero también ofrece opciones en el panel de administración para ajustar los colores principales y el radio de los bordes.
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
                    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #133A82 0%, #1d4ed8 40%, #0369a1 100%); z-index: 1;"></div>
                    
                    <!-- Technical Grid Pattern -->
                    <div style="position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px); background-size: 40px 40px; opacity: 0.3; z-index: 2;"></div>

                    <!-- Subtle Mesh Lights -->
                    <div style="position: absolute; top: -20%; right: -10%; width: 60%; height: 60%; background: radial-gradient(circle, rgba(99,179,237,0.4) 0%, transparent 70%); filter: blur(60px); z-index: 3;"></div>
                    <div style="position: absolute; bottom: -20%; left: -10%; width: 50%; height: 50%; background: radial-gradient(circle, rgba(16,185,129,0.3) 0%, transparent 70%); filter: blur(60px); z-index: 3;"></div>

                    <div style="position: relative; z-index: 10;">
                        <h2 style="color: #ffffff; font-size: clamp(2rem, 5vw, 3rem); font-weight: 950; margin: 0 0 20px; letter-spacing: -0.04em; line-height: 1.1;">
                            Transforma tu WordPress hoy
                        </h2>
                        <p style="color: rgba(255,255,255,0.75); font-size: 1.15rem; font-weight: 500; margin: 0 0 52px; max-width: 520px; margin-left: auto; margin-right: auto; line-height: 1.7;">
                            Descarga el buscador oficial y empieza a mostrar datos del Registro Mercantil con la máxima confianza en tu sitio web.
                        </p>

                        <div style="display: flex; flex-direction: column; align-items: center; gap: 16px;">
                            <a href="<?= site_url('obtener-plugin-wordpress') ?>"
                                class="js-track-wp-cta"
                                style="display: inline-block; background: #ffffff; color: #133A82; font-weight: 900; font-size: 1.1rem; padding: 20px 52px; border-radius: 18px; text-decoration: none; transition: all 0.3s ease; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
                                Registrarse y Descargar Plugin
                            </a>
                            <span style="color: rgba(255,255,255,0.5); font-size: 0.85rem; font-weight: 700;">Instalación rápida en solo 1 minuto</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

    </main>

    <?= view('partials/footer') ?>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctaButtons = document.querySelectorAll('.js-track-wp-cta');
        ctaButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                // We don't preventDefault so the link still works
                const label = this.innerText.trim();
                
                fetch('<?= site_url('tracking/radar-demo-event') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        event_type: 'click_cta',
                        source: 'marketing_wp_plugin',
                        page: 'plugin_wp_marketing',
                        cta_label: label,
                        url: this.getAttribute('href')
                    })
                })
                .then(res => res.json())
                .then(data => console.log('Marketing event tracked:', label))
                .catch(err => console.error('Error tracking marketing event:', err));
            });
        });
    });
    </script>
</body>

</html>
