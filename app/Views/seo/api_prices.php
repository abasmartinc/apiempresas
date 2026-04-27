<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head', [
        'title' => 'API Registro Mercantil y Webhooks BORME en tiempo real | APIEmpresas.es',
        'excerptText' => 'Infraestructura técnica para integración masiva de datos mercantiles. API REST, Webhooks para el BORME y enriquecimiento de leads con IA para desarrolladores.',
        'canonical' => site_url('api-empresas'),
        'robots' => 'index,follow',
    ]) ?>
    <link rel="stylesheet"
        href="<?= base_url('public/css/precios_radar.css?v=' . (file_exists(FCPATH . 'public/css/precios_radar.css') ? filemtime(FCPATH . 'public/css/precios_radar.css') : time())) ?>" />
    <style>
        /* Overrides y estilos específicos para la landing de API */
        .api-hero__badge {
            background: #eff6ff !important;
            border-color: #dbeafe !important;
            color: #1e40af !important;
        }

        .api-hero__badge-dot {
            background: #3b82f6 !important;
            box-shadow: 0 0 10px rgba(59, 130, 246, 0.35) !important;
        }

        .radar-hero {
            border-bottom: none !important;
        }

        @keyframes mesh-glow-3 {

            0%,
            100% {
                opacity: 0.3;
                transform: scale(1) translate(0, 0);
            }

            50% {
                opacity: 0.6;
                transform: scale(1.1) translate(20px, -20px);
            }
        }

        .api-code-block {
            background: #0f172a;
            border-radius: 16px;
            padding: 24px;
            font-family: 'Fira Code', monospace;
            font-size: 13px;
            color: #e2e8f0;
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin: 40px auto 0;
            text-align: left;
            box-shadow: 0 20px 50px -12px rgba(15, 23, 42, 0.35);
        }

        .api-code-keyword {
            color: #c678dd;
        }

        .api-code-string {
            color: #98c379;
        }

        .api-code-attr {
            color: #d19a66;
        }

        .api-feature-card {
            background: #fff;
            padding: 32px;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            transition: all 0.3s ease;
        }

        .api-feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.08);
            border-color: #3b82f6;
        }

        .api-feature-icon {
            width: 48px;
            height: 48px;
            background: #eff6ff;
            color: #3b82f6;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .api-endpoint-row:hover {
            background: #f8fafc;
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
            padding: 48px;
            display: flex;
            flex-direction: column;
            position: relative;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02), 0 2px 4px -1px rgba(0, 0, 0, 0.01);
        }

        .api-pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 40px 80px -20px rgba(0, 0, 0, 0.08);
            border-color: #e2e8f0;
        }

        .api-pricing-card.featured {
            border: 2px solid #3b82f6;
            background: linear-gradient(180deg, #ffffff 0%, #f0f7ff 100%);
            transform: scale(1.05);
            z-index: 10;
        }

        .api-pricing-card.featured:hover {
            transform: scale(1.05) translateY(-10px);
        }

        .api-pricing-card.featured::before {
            content: "OPCIÓN MÁS POPULAR";
            position: absolute;
            top: -16px;
            left: 50%;
            transform: translateX(-50%);
            background: linear-gradient(135deg, #f4b622 0%, #d89a12 100%);
            color: #0f172a;
            padding: 8px 20px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 900;
            letter-spacing: 0.1em;
            box-shadow: 0 10px 20px -5px rgba(216, 154, 18, 0.4);
            white-space: nowrap;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .api-pricing-card__header h3 {
            font-size: 1.25rem;
            font-weight: 800;
            color: #64748b;
            margin-bottom: 8px;
            letter-spacing: 0.02em;
        }

        .api-pricing-card.featured .api-pricing-card__header h3 {
            color: #ffffff;
        }

        /* Color Themes from Home */
        .api-pricing-card.free-plan {
            background: linear-gradient(180deg, #5b6278 0%, #555c73 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .api-pricing-card.free-plan h3,
        .api-pricing-card.free-plan .api-price-value,
        .api-pricing-card.free-plan .api-price-value span,
        .api-pricing-card.free-plan .api-pricing-card__desc,
        .api-pricing-card.free-plan .api-price-list li {
            color: #ffffff !important;
        }

        .api-pricing-card.free-plan .api-price-list {
            border-top-color: rgba(255, 255, 255, 0.1);
        }

        .api-pricing-card.featured {
            background: linear-gradient(180deg, #4f46e5 0%, #4c44dc 100%) !important;
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .api-pricing-card.featured h3,
        .api-pricing-card.featured .api-price-value,
        .api-pricing-card.featured .api-price-value span,
        .api-pricing-card.featured .api-pricing-card__desc,
        .api-pricing-card.featured .api-price-list li {
            color: #ffffff !important;
        }

        .api-pricing-card.featured .api-pricing-btn.primary {
            background: #ffffff !important;
            color: #4338ca !important;
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .api-pricing-card.featured .api-price-list {
            border-top-color: rgba(255, 255, 255, 0.1);
        }

        .api-pricing-card.business-plan {
            background: linear-gradient(180deg, #5ea083 0%, #57997c 100%);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .api-pricing-card.business-plan h3,
        .api-pricing-card.business-plan .api-price-value,
        .api-pricing-card.business-plan .api-price-value span,
        .api-pricing-card.business-plan .api-pricing-card__desc,
        .api-pricing-card.business-plan .api-price-list li {
            color: #ffffff !important;
        }

        .api-pricing-card.business-plan .api-price-list {
            border-top-color: rgba(255, 255, 255, 0.1);
        }

        .api-pricing-card.free-plan .api-pricing-btn {
            background: #2563eb !important;
            color: #ffffff !important;
            border: none !important;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.4) !important;
        }

        .api-pricing-card.free-plan .api-pricing-btn:hover {
            background: #1d4ed8 !important;
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(37, 99, 235, 0.5) !important;
        }

        .api-pricing-card.business-plan .api-pricing-btn {
            background: #ffffff !important;
            color: #1f2937 !important;
            border: none !important;
        }

        .api-price-value span {
            color: rgba(255, 255, 255, 0.7) !important;
        }

        .api-price-value {
            font-size: 4rem;
            font-weight: 950;
            color: #0f172a;
            margin: 24px 0;
            letter-spacing: -0.05em;
            line-height: 1;
            display: flex;
            align-items: baseline;
        }

        .api-price-value span {
            font-size: 1.125rem;
            color: #94a3b8;
            font-weight: 600;
            margin-left: 4px;
        }

        .api-pricing-card__desc {
            font-size: 0.95rem;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 32px;
            min-height: 3em;
        }

        .api-price-list {
            list-style: none;
            padding: 0;
            margin: 0 0 40px;
            flex-grow: 1;
            border-top: 1px solid #f1f5f9;
            padding-top: 32px;
        }

        .api-price-list li {
            padding: 12px 0;
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
            color: #334155;
            font-weight: 600;
        }

        .api-price-list li svg {
            color: #4ade80;
            flex-shrink: 0;
            filter: drop-shadow(0 0 5px rgba(74, 222, 128, 0.35));
        }

        .api-pricing-btn {
            width: 100%;
            padding: 18px 24px;
            border-radius: 16px;
            font-weight: 800;
            font-size: 1rem;
            text-align: center;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid #e2e8f0;
            color: #475569;
            background: transparent;
        }

        .api-pricing-btn:hover {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .api-pricing-btn.primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            color: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(37, 99, 235, 0.3);
        }

        .api-pricing-btn.primary:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 20px 35px -10px rgba(37, 99, 235, 0.4);
            transform: translateY(-2px);
        }

        @media (max-width: 900px) {
            .api-pricing-grid {
                grid-template-columns: 1fr;
            }

            .api-integration-grid {
                grid-template-columns: 1fr !important;
            }
        }

        /* --- Integration Section --- */
        .api-integration-grid {
            display: grid;
            grid-template-columns: 1fr 1.4fr;
            gap: 64px;
            align-items: center;
            margin-top: 0;
        }

        .api-integration-tabs {
            display: flex;
            gap: 8px;
            margin-bottom: 32px;
            flex-wrap: wrap;
        }

        .api-tab {
            padding: 8px 18px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.05em;
            cursor: pointer;
            border: 2px solid #e2e8f0;
            color: #94a3b8;
            background: transparent;
            transition: all 0.2s ease;
        }

        .api-tab.active {
            background: #0f172a;
            border-color: #0f172a;
            color: #fff;
        }

        .api-terminal {
            background: #0f172a;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 40px 80px -20px rgba(15, 23, 42, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.06);
        }

        .api-terminal__bar {
            background: #1e293b;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
        }

        .api-terminal__dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
        }

        .api-terminal__dot--red {
            background: #ff5f57;
        }

        .api-terminal__dot--amber {
            background: #febc2e;
        }

        .api-terminal__dot--green {
            background: #28c840;
        }

        .api-terminal__title {
            flex: 1;
            text-align: center;
            font-size: 12px;
            color: #475569;
            font-weight: 700;
        }

        .api-terminal__body {
            padding: 28px;
            font-family: 'Fira Code', 'Courier New', monospace;
            font-size: 13.5px;
            line-height: 1.8;
            color: #e2e8f0;
        }

        .api-integration-stat {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 0;
            border-bottom: 1px solid #f1f5f9;
        }

        .api-integration-stat:last-child {
            border-bottom: none;
        }

        .api-integration-stat__icon {
            width: 40px;
            height: 40px;
            background: #eff6ff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            flex-shrink: 0;
        }

        .api-integration-stat__text strong {
            display: block;
            font-size: 0.9rem;
            font-weight: 800;
            color: #0f172a;
        }

        .api-integration-stat__text span {
            font-size: 0.82rem;
            color: #94a3b8;
            font-weight: 500;
        }

        /* --- IA Preview Card --- */
        .api-preview-card {
            background: #fff;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 30px 60px -15px rgba(0, 0, 0, 0.12);
            overflow: hidden;
            text-align: left;
        }

        .api-preview-card__header {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
        }

        .api-preview-card__logo {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-radius: 12px;
            color: #fff;
            font-weight: 900;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .api-preview-card__info h4 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 800;
            color: #0f172a;
        }

        .api-preview-card__info span {
            font-size: 0.8rem;
            color: #94a3b8;
            font-weight: 600;
        }

        .api-preview-card__status {
            margin-left: auto;
            background: #dcfce7;
            color: #16a34a;
            font-size: 11px;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 99px;
            letter-spacing: 0.05em;
        }

        .api-preview-card__body {
            padding: 24px;
        }

        .api-score-sector {
            margin-bottom: 24px;
        }

        .api-score-main {
            display: flex;
            align-items: baseline;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .api-score-label {
            font-size: 11px;
            font-weight: 800;
            color: #94a3b8;
            letter-spacing: 0.08em;
        }

        .api-score-value {
            font-size: 2.2rem;
            font-weight: 950;
            color: #0f172a;
            letter-spacing: -0.04em;
        }

        .api-score-value span {
            font-size: 1rem;
            color: #94a3b8;
            font-weight: 600;
        }

        .api-score-bar {
            height: 8px;
            background: #f1f5f9;
            border-radius: 99px;
            overflow: hidden;
        }

        .api-score-progress {
            height: 100%;
            background: linear-gradient(90deg, #2563eb, #06b6d4);
            border-radius: 99px;
        }

        .api-insights-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        .api-insight-item label {
            display: block;
            font-size: 10px;
            font-weight: 800;
            color: #94a3b8;
            letter-spacing: 0.08em;
            margin-bottom: 8px;
        }

        .api-insight-item p {
            margin: 0;
            font-size: 0.82rem;
            color: #475569;
            line-height: 1.5;
            font-weight: 500;
        }

        .api-signals-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
        }

        .api-signals-tags span {
            background: #eff6ff;
            color: #2563eb;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 10px;
            border-radius: 99px;
            border: 1px solid #dbeafe;
        }

        /* PREMIUM CTA ANIMATIONS */
        @keyframes mesh-glow-1 {
            0% {
                transform: translate(0, 0) scale(1);
                opacity: 0.4;
            }

            33% {
                transform: translate(30px, -50px) scale(1.2);
                opacity: 0.7;
            }

            66% {
                transform: translate(-20px, 20px) scale(0.9);
                opacity: 0.3;
            }

            100% {
                transform: translate(0, 0) scale(1);
                opacity: 0.4;
            }
        }

        @keyframes mesh-glow-2 {
            0% {
                transform: translate(0, 0) scale(1);
                opacity: 0.35;
            }

            33% {
                transform: translate(-40px, 30px) scale(1.1);
                opacity: 0.6;
            }

            66% {
                transform: translate(25px, -20px) scale(1.3);
                opacity: 0.45;
            }

            100% {
                transform: translate(0, 0) scale(1);
                opacity: 0.35;
            }
        }

        .api-faq {
            margin-top: 60px;
            max-width: 800px;
            margin-left: auto;
            margin-right: auto;
        }

        .api-faq-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            margin-bottom: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .api-faq-item:hover {
            border-color: #3b82f6;
            box-shadow: 0 10px 25px -10px rgba(59, 130, 246, 0.12);
        }

        .api-faq-question {
            padding: 24px 28px;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: none;
            border: none;
            text-align: left;
            font-size: 1.05rem;
            font-weight: 800;
            color: #0f172a;
            cursor: pointer;
        }

        .api-faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            background: #f8fafc;
        }

        .api-faq-answer-inner {
            padding: 0 28px 24px;
            font-size: 0.95rem;
            color: #475569;
            line-height: 1.6;
            font-weight: 500;
        }

        .api-faq-item.active .api-faq-answer {
            max-height: 400px;
        }

        .api-faq-icon {
            font-size: 1.4rem;
            transition: transform 0.2s ease;
            color: #94a3b8;
            font-weight: 400;
        }

        .api-faq-item.active .api-faq-icon {
            transform: rotate(45deg);
            color: #3b82f6;
        }

        .technical-grid {
            mask-image: linear-gradient(to bottom, transparent, black 15%, black 85%, transparent);
            -webkit-mask-image: linear-gradient(to bottom, transparent, black 15%, black 85%, transparent);
        }
    </style>
</head>

<body>
    <?= view('partials/header') ?>

    <main class="radar-page">

        <!-- HERO SECTION -->
        <section class="radar-hero">
            <div class="container">
                <div class="radar-hero__shell" style="margin-bottom: 60px;">
                    <div class="radar-hero__badge api-hero__badge">
                        <span class="radar-hero__badge-dot api-hero__badge-dot"></span>
                        INFRAESTRUCTURA DE DATOS A ESCALA
                    </div>

                    <h1 class="radar-hero__title">
                        API de empresas en España para <span>validar CIF, consultar datos y automatizar
                            integraciones</span>
                    </h1>

                    <p class="radar-hero__subtitle">
                        Accede a datos del Registro Mercantil y BORME en tiempo real vía API REST. Diseñada para
                        desarrolladores y sistemas de alta disponibilidad.
                    </p>
                    <p style="margin-top: -10px; font-weight: 700; color: #1e40af; opacity: 0.8; font-size: 1rem;">
                        API REST con respuesta en milisegundos, endpoints listos para producción y datos oficiales del
                        Registro Mercantil.
                    </p>

                    <div class="radar-hero__proof">
                        <div class="radar-hero__proof-item">
                            <strong>JSON REST</strong>
                            <span>Integración simple</span>
                        </div>
                        <div class="radar-hero__proof-item">
                            <strong>Scoring IA</strong>
                            <span>Modelo Inteligente</span>
                        </div>
                        <div class="radar-hero__proof-item">
                            <strong>Datos Oficiales</strong>
                            <span>Seguridad mercantil</span>
                        </div>
                    </div>

                    <div class="radar-hero__actions">
                        <a href="<?= site_url('register') ?>" class="radar-btn radar-btn--primary">
                            Probar API gratis
                        </a>
                        <a href="<?= site_url('documentation') ?>" class="radar-btn radar-btn--ghost">
                            Ver documentación
                        </a>
                    </div>

                    <!-- API HERO TWO-COLUMN PANEL -->
                    <div class="radar-hero__feature-panel" style="max-width: 1040px; margin-top: 40px;">
                        <div class="radar-hero__feature-copy">
                            <h2>Impulsa tu prospección con datos en tiempo real</h2>
                            <ul style="display: grid; gap: 16px;">
                                <li><strong>Infraestructura API Nativa:</strong> Integración simple con JSON de alto
                                    rendimiento para validar cada oportunidad técnica.</li>
                                <li><strong>Webhooks del Registro Mercantil:</strong> Notificaciones HTTP directas del
                                    BORME para automatizar tu CRM o ERP.</li>
                                <li><strong>Enriquecimiento de Leads:</strong> Ingesta masiva de datos societarios,
                                    cargos y capital social para scoring avanzado.</li>
                            </ul>
                        </div>

                        <div class="api-preview-card">
                            <div class="api-preview-card__header">
                                <div class="api-preview-card__logo">TS</div>
                                <div class="api-preview-card__info">
                                    <h4>Tech Flow Solutions SL</h4>
                                    <span>B12345678 • Madrid</span>
                                </div>
                                <div class="api-preview-card__status">ACTIVA</div>
                            </div>

                            <div class="api-preview-card__body">
                                <div class="api-score-sector" style="margin-bottom: 20px;">
                                    <div class="api-score-main">
                                        <div class="api-score-label">COMERCIAL SCORE</div>
                                        <div class="api-score-value" style="font-size: 1.8rem;">94<span>/100</span>
                                        </div>
                                    </div>
                                    <div class="api-score-bar">
                                        <div class="api-score-progress" style="width: 94%;"></div>
                                    </div>
                                </div>

                                <div class="api-insights-grid" style="grid-template-columns: 1fr;">
                                    <div class="api-insight-item">
                                        <label>IA INSIGHTS</label>
                                        <p style="font-size: 0.78rem;">Alta probabilidad de inversión en digitalización.
                                            Empresa en expansión detectada.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SEO BLOCK: CÓMO FUNCIONA -->
        <section class="radar-section" style="padding: 120px 0; background: #fbfcfe;">
            <div class="container">
                <div style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 80px; align-items: center;">
                    <div>
                        <div class="radar-kicker">Infraestructura de Datos</div>
                        <h2 class="radar-title"
                            style="margin-top: 12px; margin-bottom: 32px; font-size: 2.75rem; letter-spacing: -0.03em;">
                            API de empresas en España: cómo funciona</h2>

                        <p class="radar-text"
                            style="font-size: 1.2rem; line-height: 1.7; color: #334155; margin-bottom: 40px; max-width: 600px;">
                            Nuestra infraestructura conecta directamente con los nodos del <strong>Registro
                                Mercantil</strong> para ofrecerte una interfaz REST de baja latencia diseñada para
                            automatización masiva.
                        </p>

                        <div style="display: grid; gap: 32px; margin-bottom: 48px;">
                            <div style="display: flex; gap: 20px; align-items: flex-start;">
                                <div
                                    style="background: #2563eb; color: #fff; width: 36px; height: 36px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; flex-shrink: 0; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.25);">
                                    1</div>
                                <div>
                                    <h4 style="margin: 0 0 4px; font-weight: 800; color: #0f172a;">Consulta Masiva</h4>
                                    <p style="margin: 0; font-size: 1rem; color: #64748b; line-height: 1.5;">Peticiones
                                        por <strong>CIF o NIF</strong> con payloads JSON estructurados.</p>
                                </div>
                            </div>
                            <div style="display: flex; gap: 20px; align-items: flex-start;">
                                <div
                                    style="background: #2563eb; color: #fff; width: 36px; height: 36px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; flex-shrink: 0; box-shadow: 0 10px 15px -3px rgba(37, 99, 235, 0.25);">
                                    2</div>
                                <div>
                                    <h4 style="margin: 0 0 4px; font-weight: 800; color: #0f172a;">Respuesta en Tiempo
                                        Real</h4>
                                    <p style="margin: 0; font-size: 1rem; color: #64748b; line-height: 1.5;">Datos
                                        oficiales, CNAE y estado mercantil en menos de 200ms.</p>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; flex-wrap: wrap; gap: 24px; align-items: center;">
                            <a href="<?= site_url() ?>" class="radar-btn radar-btn--ghost"
                                style="border: 2px solid #e2e8f0; padding: 14px 32px; border-radius: 16px; font-size: 1rem;">
                                Herramienta Validar CIF
                            </a>
                            <a href="<?= site_url('documentation') ?>"
                                style="font-weight: 800; color: #2563eb; text-decoration: none; display: flex; align-items: center; gap: 8px; font-size: 1.05rem;">
                                Explorar documentación técnica →
                            </a>
                        </div>
                    </div>

                    <div style="position: relative;">
                        <!-- Subtle background glow for the terminal -->
                        <div
                            style="position: absolute; inset: -40px; background: radial-gradient(circle, rgba(37,99,235,0.08) 0%, transparent 70%); pointer-events: none;">
                        </div>

                        <div
                            style="background: #0f172a; border-radius: 24px; padding: 32px; box-shadow: 0 50px 100px -20px rgba(15, 23, 42, 0.4); border: 1px solid rgba(255,255,255,0.05); position: relative; z-index: 2;">
                            <div style="display: flex; gap: 8px; margin-bottom: 24px;">
                                <span
                                    style="width: 10px; height: 10px; background: #ff5f57; border-radius: 50%;"></span>
                                <span
                                    style="width: 10px; height: 10px; background: #febc2e; border-radius: 50%;"></span>
                                <span
                                    style="width: 10px; height: 10px; background: #28c840; border-radius: 50%;"></span>
                            </div>
                            <pre
                                style="margin: 0; font-family: 'Fira Code', monospace; font-size: 14px; line-height: 1.8;">
<span style="color: #c678dd;">GET</span> <span style="color: #98c379;">/v1/companies/B12345678</span>
<span style="color: #abb2bf;">{</span>
  <span style="color: #d19a66;">"name"</span>: <span style="color: #98c379;">"TECH FLOW SL"</span>,
  <span style="color: #d19a66;">"cif"</span>: <span style="color: #98c379;">"B12345678"</span>,
  <span style="color: #d19a66;">"status"</span>: <span style="color: #98c379;">"ACTIVA"</span>,
  <span style="color: #d19a66;">"score"</span>: <span style="color: #d19a66;">92</span>
<span style="color: #abb2bf;">}</span></pre>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- SEO BLOCK: QUÉ PUEDES HACER -->
        <section class="radar-section">
            <div class="container">
                <div class="radar-split">
                    <div class="radar-split__content">
                        <div class="radar-kicker">Capacidades técnicas</div>
                        <h2 class="radar-title">¿Qué puedes hacer con la API de empresas?</h2>
                        <p class="radar-text">Nuestra infraestructura te permite abstraer la complejidad de los
                            registros oficiales en una interfaz programática simple y potente.</p>
                        <ul style="display: grid; gap: 12px; margin-top: 24px;">
                            <li
                                style="display: flex; align-items: center; gap: 12px; font-weight: 700; color: #475569;">
                                <span style="color: #3b82f6;">✓</span> Validar CIF automáticamente
                            </li>
                            <li
                                style="display: flex; align-items: center; gap: 12px; font-weight: 700; color: #475569;">
                                <span style="color: #3b82f6;">✓</span> Consultar empresas por nombre o NIF
                            </li>
                            <li
                                style="display: flex; align-items: center; gap: 12px; font-weight: 700; color: #475569;">
                                <span style="color: #3b82f6;">✓</span> Acceder a datos del Registro Mercantil
                            </li>
                            <li
                                style="display: flex; align-items: center; gap: 12px; font-weight: 700; color: #475569;">
                                <span style="color: #3b82f6;">✓</span> Integrar datos empresariales en CRM o ERP
                            </li>
                            <li
                                style="display: flex; align-items: center; gap: 12px; font-weight: 700; color: #475569;">
                                <span style="color: #3b82f6;">✓</span> Automatizar procesos KYC y validación
                            </li>
                        </ul>
                    </div>
                    <div class="api-terminal" style="max-width: 500px;">
                        <div class="api-terminal__bar">
                            <span class="api-terminal__dot api-terminal__dot--red"></span>
                            <span class="api-terminal__dot api-terminal__dot--amber"></span>
                            <span class="api-terminal__dot api-terminal__dot--green"></span>
                            <span class="api-terminal__title">bash — curl</span>
                        </div>
                        <div class="api-terminal__body">
                            <span style="color: #98c379;">$</span> curl -X GET <span
                                class="api-code-string">"https://apiempresas.es/api/v1/companies?cif=B12345678"</span>
                            \<br>
                            &nbsp;&nbsp;-H <span class="api-code-string">"X-API-KEY: tu_clave"</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CASOS DE USO -->
        <section class="radar-section radar-section--soft">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">Soluciones B2B</div>
                    <h2 class="radar-title">Casos de uso de la API de empresas</h2>
                </div>
                <div class="radar-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-top: 40px;">
                    <div
                        style="background: white; padding: 32px; border-radius: 20px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 16px;">🛡️</div>
                        <h4 style="margin: 0; font-weight: 800;">Validación de clientes (KYC)</h4>
                    </div>
                    <div
                        style="background: white; padding: 32px; border-radius: 20px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 16px;">📈</div>
                        <h4 style="margin: 0; font-weight: 800;">Enriquecimiento de leads</h4>
                    </div>
                    <div
                        style="background: white; padding: 32px; border-radius: 20px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 16px;">🔄</div>
                        <h4 style="margin: 0; font-weight: 800;">Integración en CRM</h4>
                    </div>
                    <div
                        style="background: white; padding: 32px; border-radius: 20px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 16px;">💳</div>
                        <h4 style="margin: 0; font-weight: 800;">Fintech y scoring</h4>
                    </div>
                    <div
                        style="background: white; padding: 32px; border-radius: 20px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 16px;">🚀</div>
                        <h4 style="margin: 0; font-weight: 800;">SaaS B2B</h4>
                    </div>
                </div>
            </div>
        </section>

        <!-- VALUE HOOKS -->
        <section class="radar-section">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">Por qué elegirnos</div>
                    <h2 class="radar-title">Diferencia tu producto con datos inteligentes</h2>
                    <p class="radar-subtitle">
                        No entregamos datos estáticos. Entregamos inteligencia accionable lista para ser integrada en
                        tus procesos.
                    </p>
                </div>

                <div class="radar-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px;">
                    <div class="api-feature-card">
                        <div class="api-feature-icon">🛡️</div>
                        <h3>Ingestión Masiva</h3>
                        <p class="radar-text">Elimina cuellos de botella técnicos. Nuestra API está diseñada para
                            soportar
                            miles de peticiones simultáneas con latencia mínima.</p>
                    </div>
                    <div class="api-feature-card">
                        <div class="api-feature-icon">🎯</div>
                        <h3>Scoring IA (0-100)</h3>
                        <p class="radar-text">Prioriza tu equipo comercial. Identifica qué nuevas empresas tienen mayor
                            capital y probabilidad de compra.</p>
                    </div>
                    <div class="api-feature-card">
                        <div class="api-feature-icon">⚡</div>
                        <h3>Notificaciones Push</h3>
                        <p class="radar-text">Recibe payloads de Webhooks en tiempo real. Mantén tu base de datos
                            sincronizada con el BORME sin procesos de polling.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ENDPOINTS TABLE -->
        <section class="radar-section radar-section--soft">
            <div class="container">
                <div class="radar-heading">
                    <div class="radar-kicker">Documentación rápida</div>
                    <h2 class="radar-title">Capacidades de la API</h2>
                    <p class="radar-subtitle" style="text-align: left; max-width: 800px; margin-left: 0;">
                        Consulta empresas en España mediante endpoints API REST. Accede a datos fiscales, actividad,
                        estado y otra información oficial del Registro Mercantil en tiempo real.
                    </p>
                </div>

                <div
                    style="background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: var(--shadow-soft);">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                <th
                                    style="padding: 20px; text-align: left; font-weight: 800; font-size: 13px; color: #64748b; text-transform: uppercase;">
                                    Endpoint</th>
                                <th
                                    style="padding: 20px; text-align: left; font-weight: 800; font-size: 13px; color: #64748b; text-transform: uppercase;">
                                    Beneficio de Negocio</th>
                                <th
                                    style="padding: 20px; text-align: center; font-weight: 800; font-size: 13px; color: #64748b; text-transform: uppercase;">
                                    Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- CORE ENDPOINTS -->
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        GET /companies</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parámetro: ?cif=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Datos
                                        Oficiales y Validación</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Obtén el perfil legal completo: <code style="color: #2563eb;">name</code>, <code
                                            style="color: #2563eb;">founded</code>, <code
                                            style="color: #2563eb;">address</code>, <code
                                            style="color: #2563eb;">cnae</code>, <code
                                            style="color: #2563eb;">status</code> y enlaces a registros.
                                        <span style="color: #64748b; font-style: italic;">(Datos de contacto protegidos
                                            en Free)</span>.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">GÉNERICO</span>
                                </td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        GET /companies/search</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parámetro: ?q=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Búsqueda
                                        Inteligente</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Localiza empresas por nombre comercial o razón social. Incluye sugerencias
                                        fonéticas y coincidencias parciales con alta precisión.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">GÉNERICO</span>
                                </td>
                            </tr>

                            <!-- ENRICHMENT & IA -->
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        GET /companies/score</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parámetro: ?cif=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Comercial
                                        Scoring IA</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Algoritmo de propensión de compra. Devuelve <code
                                            style="color: #2563eb;">score</code> (0-100), <code
                                            style="color: #2563eb;">priority</code> (Muy Alta - Baja) y un mensaje
                                        descriptivo del potencial comercial.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">PRO
                                        / BUS</span></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        GET /companies/signals</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parámetro: ?cif=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Señales
                                        Societarias (BORME)</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Monitoriza cambios de capital, actos de insolvencia, nombramientos directivos y
                                        renovaciones de cargos societarios en tiempo real.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">PRO
                                        / BUS</span></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        GET /companies/insights</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parámetro: ?cif=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Insights
                                        Predictivos por IA</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Análisis avanzado de necesidades. Devuelve <code
                                            style="color: #2563eb;">profile</code> (resumen IA del nicho) y <code
                                            style="color: #2563eb;">prob</code> (índice de probabilidad de éxito en
                                        captación).
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #f5f3ff; color: #8b5cf6; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">IA
                                        PREMIUM</span></td>
                            </tr>

                            <!-- RADAR -->
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        GET /companies/radar</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Filtros: province, sector...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Radar
                                        de Nuevas Sociedades</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Extracción masiva de empresas recién constituidas. Filtra por geolocalización o
                                        actividad económica (CNAE) para alimentar tu flujo de ventas frío.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">PRO
                                        / BUS</span></td>
                            </tr>

                            <!-- WEBHOOKS -->
                            <tr class="api-endpoint-row">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        POST /webhooks</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Body: {url, event}</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Suscripción
                                        a Webhooks Push</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Registra tu URL de callback para recibir notificaciones HTTP en tiempo real
                                        cuando ocurra un evento de interés (ej: nueva empresa en tu zona).
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #fdf2f8; color: #db2777; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">BUSINESS</span>
                                </td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        GET /webhooks</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Headers: X-API-KEY</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Gestión
                                        de Webhooks</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Lista y gestiona tus suscripciones activas para recibir notificaciones HTTP
                                        instantáneas.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #fdf2f8; color: #db2777; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">BUSINESS</span>
                                </td>
                            </tr>
                            <tr class="api-endpoint-row">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        DELETE /webhooks/{id}</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Path param: webhook_id</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Baja
                                        de Webhooks</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Elimina la suscripción a eventos de forma programática.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #fdf2f8; color: #db2777; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">BUSINESS</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- PRICING SECTION (CORRECTED DATA) -->
        <section id="planes" class="radar-section">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">Precios transparentes</div>
                    <h2 class="radar-title">Planes diseñados para crecer</h2>
                    <p class="radar-subtitle">
                        Sin costes de integración. Empieza gratis e integra la potencia de datos oficiales en tu stack
                        en minutos.
                    </p>
                </div>

                <div class="api-pricing-grid">

                    <!-- FREE -->
                    <div class="api-pricing-card free-plan">
                        <div class="api-pricing-card__header">
                            <h3>Free</h3>
                        </div>
                        <div class="api-price-value">0€<span>/ mes</span></div>
                        <p class="api-pricing-card__desc">Ideal para desarrollos iniciales, pruebas de concepto y
                            validación técnica.</p>

                        <ul class="api-price-list">
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> <?= $freeLimit ?> consultas / mes</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Datos básicos oficiales</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Acceso a /companies</li>
                        </ul>

                        <div class="api-price-cta" style="margin-top: 32px;">
                            <form id="api_quick_unlock_form" style="display: flex; flex-direction: column; gap: 12px;">
                                <input type="email" name="email" placeholder="Tu email corporativo" required 
                                       style="padding: 14px 20px; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 1rem; width: 100%; outline: none; transition: border-color 0.2s;"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                                <button type="submit" class="api-pricing-btn" style="width: 100%; justify-content: center; background: #0f172a; color: white; border: none; cursor: pointer;">
                                    Obtener API Key Gratis
                                </button>
                            </form>
                            <p style="font-size: 0.75rem; color: rgba(255, 255, 255, 0.7); margin-top: 12px; text-align: center;">
                                Acceso instantáneo. Sin tarjeta de crédito.
                            </p>
                        </div>
                    </div>

                    <!-- PRO -->
                    <div class="api-pricing-card featured">
                        <div class="api-pricing-card__header">
                            <h3>Pro</h3>
                        </div>
                        <div class="api-price-value">19€<span>/ mes</span></div>
                        <p class="api-pricing-card__desc">El estándar para SaaS, ERPs y automatización comercial con
                            scoring inteligente.</p>

                        <ul class="api-price-list">
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> 3.000 consultas / mes</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Datos completos BORME</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Scoring IA Incluido</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Acceso a Radar API</li>
                        </ul>

                        <a href="<?= site_url('register') ?>" class="api-pricing-btn primary">Activar Plan Pro</a>
                    </div>

                    <!-- BUSINESS -->
                    <div class="api-pricing-card business-plan">
                        <div class="api-pricing-card__header">
                            <h3>Business</h3>
                        </div>
                        <div class="api-price-value">49€<span>/ mes</span></div>
                        <p class="api-pricing-card__desc">Infraestructura dedicada para alto volumen, Webhooks y
                            procesos críticos.</p>

                        <ul class="api-price-list">
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> 10.000 consultas / mes</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Webhooks Push PUSH</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> IA Insights & Predictor</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Soporte Prioritario Slack</li>
                        </ul>

                        <a href="<?= site_url('register') ?>" class="api-pricing-btn">Activar Business</a>
                    </div>
                </div>

            </div>

            <div
                style="margin-top: 40px; text-align: center; background: #f8fafc; padding: 24px; border-radius: 20px; border: 1px dashed #cbd5e1;">
                <p style="color: #64748b; font-weight: 700; margin: 0;">¿Necesitas más de 20.000 consultas o soporte
                    Enterprise? <a href="<?= site_url('contact') ?>"
                        style="color: #3b82f6; text-decoration: none;">Hablemos de tu proyecto →</a></p>
            </div>
            </div>
        </section>

        <!-- CODE INTEGRATION -->
        <section class="radar-section radar-section--soft">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">Developer First</div>
                    <h2 class="radar-title">Cómo integrar la API de empresas</h2>
                    <p class="radar-subtitle">Autenticación simple, Endpoints REST, ejemplos JSON y tiempos de respuesta
                        optimizados para producción.</p>
                </div>

                <!-- Language tabs -->
                <div style="display: flex; justify-content: center; gap: 8px; margin-bottom: 24px; margin-top: 40px;">
                    <button onclick="switchTab('python')" id="tab-python"
                        style="padding: 10px 22px; border-radius: 99px; font-size: 12px; font-weight: 800; letter-spacing: 0.05em; cursor: pointer; border: 2px solid #2563eb; color: #fff; background: #2563eb; transition: all 0.2s;">PYTHON</button>
                    <button onclick="switchTab('php')" id="tab-php"
                        style="padding: 10px 22px; border-radius: 99px; font-size: 12px; font-weight: 800; letter-spacing: 0.05em; cursor: pointer; border: 2px solid #e2e8f0; color: #94a3b8; background: #fff; transition: all 0.2s;">PHP
                        / LARAVEL</button>
                    <button onclick="switchTab('node')" id="tab-node"
                        style="padding: 10px 22px; border-radius: 99px; font-size: 12px; font-weight: 800; letter-spacing: 0.05em; cursor: pointer; border: 2px solid #e2e8f0; color: #94a3b8; background: #fff; transition: all 0.2s;">NODE.JS</button>
                </div>

                <!-- Code window -->
                <div
                    style="background: #0f172a; border-radius: 20px; overflow: hidden; max-width: 780px; margin: 0 auto; box-shadow: 0 30px 60px -15px rgba(15,23,42,0.2); border: 1px solid #1e293b;">
                    <div
                        style="background: #1e293b; padding: 14px 20px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <span
                            style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></span>
                        <span
                            style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></span>
                        <span
                            style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></span>
                        <span id="tab-filename"
                            style="flex:1;text-align:center;font-size:12px;color:#64748b;font-weight:700;font-family:monospace;">api_empresas.py</span>
                    </div>
                    <pre id="code-python"
                        style="margin:0;padding:32px 36px;font-family:'Fira Code','Courier New',monospace;font-size:13.5px;line-height:2;color:#e2e8f0;overflow-x:auto;"><span class="api-code-keyword">import</span> requests

url     = <span class="api-code-string">"https://apiempresas.es/api/v1/companies"</span>
params  = {<span class="api-code-attr">"cif"</span>: <span class="api-code-string">"B12345678"</span>}
headers = {<span class="api-code-attr">"X-API-KEY"</span>: <span class="api-code-string">"tu_clave_aqui"</span>}

res  = requests.get(url, params=params, headers=headers)
data = res.json()

<span class="api-code-keyword">if</span> data[<span class="api-code-string">"success"</span>]:
    company = data[<span class="api-code-string">"data"</span>]
    <span class="api-code-keyword">print</span>(company[<span class="api-code-string">"name"</span>])     <span style="color:#475569"># → Tech Flow Solutions SL</span>
    <span class="api-code-keyword">print</span>(company[<span class="api-code-string">"province"</span>]) <span style="color:#475569"># → MADRID</span>
    <span class="api-code-keyword">print</span>(company[<span class="api-code-string">"founded"</span>])  <span style="color:#475569"># → 2024-03-12</span>
</pre>
                    <pre id="code-php"
                        style="display:none;margin:0;padding:32px 36px;font-family:'Fira Code','Courier New',monospace;font-size:13.5px;line-height:2;color:#e2e8f0;overflow-x:auto;"><span class="api-code-keyword">$ch</span> = curl_init();

curl_setopt_array(<span class="api-code-keyword">$ch</span>, [
    CURLOPT_URL            => <span class="api-code-string">"https://apiempresas.es/api/v1/companies?cif=B12345678"</span>,
    CURLOPT_HTTPHEADER     => [<span class="api-code-string">"X-API-KEY: tu_clave_aqui"</span>],
    CURLOPT_RETURNTRANSFER => <span class="api-code-keyword">true</span>,
]);

<span class="api-code-keyword">$data</span> = json_decode(curl_exec(<span class="api-code-keyword">$ch</span>), <span class="api-code-keyword">true</span>);

<span class="api-code-keyword">if</span> (<span class="api-code-keyword">$data</span>[<span class="api-code-string">'success'</span>]) {
    <span class="api-code-keyword">$c</span> = <span class="api-code-keyword">$data</span>[<span class="api-code-string">'data'</span>];
    <span class="api-code-keyword">echo</span> <span class="api-code-keyword">$c</span>[<span class="api-code-string">'name'</span>];      <span style="color:#475569">// → Tech Flow Solutions SL</span>
    <span class="api-code-keyword">echo</span> <span class="api-code-keyword">$c</span>[<span class="api-code-string">'province'</span>];  <span style="color:#475569">// → MADRID</span>
}
</pre>
                    <pre id="code-node"
                        style="display:none;margin:0;padding:32px 36px;font-family:'Fira Code','Courier New',monospace;font-size:13.5px;line-height:2;color:#e2e8f0;overflow-x:auto;"><span class="api-code-keyword">const</span> axios = require(<span class="api-code-string">'axios'</span>);

<span class="api-code-keyword">const</span> res = <span class="api-code-keyword">await</span> axios.get(<span class="api-code-string">'https://apiempresas.es/api/v1/companies'</span>, {
  params:  { cif: <span class="api-code-string">'B12345678'</span> },
  headers: { <span class="api-code-string">'X-API-KEY'</span>: <span class="api-code-string">'tu_clave_aqui'</span> }
});

<span class="api-code-keyword">const</span> { success, data } = res.data;

<span class="api-code-keyword">if</span> (success) {
  <span class="api-code-keyword">const</span> { name, cnae_label, founded } = data;
  console.log(name);       <span style="color:#475569">// → Tech Flow Solutions SL</span>
  console.log(cnae_label); <span style="color:#475569">// → Programación informática</span>
}
</pre>
                </div>

                <!-- Feature pills below code -->
                <div style="display: flex; justify-content: center; gap: 32px; margin-top: 40px; flex-wrap: wrap;">
                    <div
                        style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:0.88rem;font-weight:700;">
                        <span
                            style="background:#eff6ff;color:#2563eb;padding:6px 8px;border-radius:8px;font-size:14px;">⚡</span>
                        Respuesta &lt; 200ms
                    </div>
                    <div
                        style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:0.88rem;font-weight:700;">
                        <span
                            style="background:#eff6ff;color:#2563eb;padding:6px 8px;border-radius:8px;font-size:14px;">🔐</span>
                        Auth por API Key
                    </div>
                    <div
                        style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:0.88rem;font-weight:700;">
                        <span
                            style="background:#eff6ff;color:#2563eb;padding:6px 8px;border-radius:8px;font-size:14px;">📄</span>
                        Docs completas
                    </div>
                    <div
                        style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:0.88rem;font-weight:700;">
                        <span
                            style="background:#eff6ff;color:#2563eb;padding:6px 8px;border-radius:8px;font-size:14px;">🤖</span>
                        IA en cada respuesta
                    </div>
                </div>
            </div>
        </section>

        <script>
            function switchTab(lang) {
                const names = { python: 'api_empresas.py', php: 'api_empresas.php', node: 'api_empresas.js' };
                ['python', 'php', 'node'].forEach(l => {
                    document.getElementById('code-' + l).style.display = (l === lang) ? 'block' : 'none';
                    const btn = document.getElementById('tab-' + l);
                    if (l === lang) {
                        btn.style.background = '#2563eb';
                        btn.style.borderColor = '#2563eb';
                        btn.style.color = '#fff';
                    } else {
                        btn.style.background = '#fff';
                        btn.style.borderColor = '#e2e8f0';
                        btn.style.color = '#94a3b8';
                    }
                });
                document.getElementById('tab-filename').textContent = names[lang];
            }
        </script>


        <!-- FAQ SECTION -->
        <section class="radar-section">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">Respuesta a dudas técnicas</div>
                    <h2 class="radar-title">Preguntas Frecuentes sobre la API</h2>
                    <p class="radar-subtitle">Todo lo que necesitas saber para integrar nuestra infraestructura en tu
                        producto.</p>
                </div>

                <div class="api-faq">
                    <div class="api-faq-item active">
                        <button class="api-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>¿Cómo se integra la API de empresas?</span>
                            <span class="api-faq-icon">+</span>
                        </button>
                        <div class="api-faq-answer">
                            <div class="api-faq-answer-inner">
                                La integración es sencilla mediante una API REST estándar. Solo necesitas tu API Key
                                para realizar peticiones GET a nuestros endpoints. Disponemos de ejemplos listos para
                                usar en Python, PHP, Node.js y cURL en nuestra documentación oficial.
                            </div>
                        </div>
                    </div>

                    <div class="api-faq-item">
                        <button class="api-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>¿Con qué frecuencia se actualizan los datos del Registro Mercantil?</span>
                            <span class="api-faq-icon">+</span>
                        </button>
                        <div class="api-faq-answer">
                            <div class="api-faq-answer-inner">
                                Nuestro motor monitoriza el BORME (Boletín Oficial del Registro Mercantil) diariamente.
                                Las nuevas constituciones, ceses, nombramientos y ampliaciones de capital suelen estar
                                disponibles en la API pocas horas después de su publicación oficial.
                            </div>
                        </div>
                    </div>

                    <div class="api-faq-item">
                        <button class="api-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>¿Soportan Webhooks para recibir alertas del BORME?</span>
                            <span class="api-faq-icon">+</span>
                        </button>
                        <div class="api-faq-answer">
                            <div class="api-faq-answer-inner">
                                Sí, el plan Business permite configurar Webhooks. Puedes registrar una URL de callback
                                para recibir notificaciones PUSH cada vez que detectemos una nueva empresa que cumpla
                                tus filtros de sector o provincia, evitando el polling constante.
                            </div>
                        </div>
                    </div>

                    <div class="api-faq-item">
                        <button class="api-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>¿Qué tipo de datos devuelve la API?</span>
                            <span class="api-faq-icon">+</span>
                        </button>
                        <div class="api-faq-answer">
                            <div class="api-faq-answer-inner">
                                Devolvemos un JSON estructurado con: Datos legales (CIF, Razón Social), CNAE, Capital
                                Social, Localización (Dirección, Provincia), Estado (Activa/Extinguida), Cargos
                                Directivos y Scoring de propensión comercial basado en IA.
                            </div>
                        </div>
                    </div>

                    <div class="api-faq-item">
                        <button class="api-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>¿Cuál es el límite de peticiones por segundo?</span>
                            <span class="api-faq-icon">+</span>
                        </button>
                        <div class="api-faq-answer">
                            <div class="api-faq-answer-inner">
                                Nuestra infraestructura está diseñada para ser escalable. Por defecto, permitimos
                                ráfagas de hasta 10 peticiones por segundo en planes estándar, pero podemos habilitar
                                cuotas personalizadas para ingestas masivas de datos en planes Enterprise.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FINAL CTA -->
        <section style="padding: 80px 0 100px;">
            <div class="container">
                <div
                    style="background: #0f172a; border-radius: 32px; padding: 80px 60px; text-align: center; position: relative; overflow: hidden; box-shadow: 0 40px 80px -20px rgba(15, 23, 42, 0.5);">

                    <!-- Solid Background Gradient -->
                    <div
                        style="position: absolute; inset: 0; background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 40%, #0369a1 100%); z-index: 0;">
                    </div>

                    <!-- Technical Grid Pattern -->
                    <div class="technical-grid"
                        style="position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px); background-size: 30px 30px; opacity: 0.4; z-index: 1;">
                    </div>

                    <!-- Animated Background Blobs -->
                    <div
                        style="position: absolute; top: -15%; right: -15%; width: 70%; height: 70%; background: radial-gradient(circle, rgba(99,179,237,0.7) 0%, transparent 70%); border-radius: 50%; filter: blur(60px); animation: mesh-glow-1 15s infinite ease-in-out; pointer-events: none; z-index: 2; will-change: transform, opacity;">
                    </div>
                    <div
                        style="position: absolute; bottom: -15%; left: -15%; width: 80%; height: 80%; background: radial-gradient(circle, rgba(16,185,129,0.5) 0%, transparent 70%); border-radius: 50%; filter: blur(70px); animation: mesh-glow-2 18s infinite ease-in-out; pointer-events: none; animation-delay: -2s; z-index: 2; will-change: transform, opacity;">
                    </div>

                    <!-- Content (ensure relative and above background) -->
                    <div style="position: relative; z-index: 10;">
                        <!-- Badge -->
                        <div
                            style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); border-radius: 99px; padding: 6px 16px; margin-bottom: 28px;">
                            <span
                                style="width: 8px; height: 8px; background: #34d399; border-radius: 50%; box-shadow: 0 0 8px #34d399;"></span>
                            <span
                                style="color: rgba(255,255,255,0.9); font-size: 11px; font-weight: 800; letter-spacing: 0.08em;">SISTEMA
                                LISTO</span>
                        </div>

                        <!-- Headline -->
                        <h2
                            style="color: #ffffff; font-size: 2.75rem; font-weight: 950; margin: 0 0 16px; letter-spacing: -0.04em; line-height: 1.1;">
                            Empieza a usar la API de empresas ahora</h2>
                        <p
                            style="color: rgba(255,255,255,0.65); font-size: 1.1rem; font-weight: 500; margin: 0 0 48px; max-width: 480px; margin-left: auto; margin-right: auto; line-height: 1.6;">
                            Únete a las empresas que ya automatizan su validación mercantil y captan clientes antes que
                            nadie.</p>

                        <!-- Stats row -->
                        <div
                            style="display: flex; justify-content: center; gap: 48px; margin-bottom: 52px; flex-wrap: wrap;">
                            <div>
                                <div
                                    style="color: #fff; font-size: 1.75rem; font-weight: 950; letter-spacing: -0.04em;">
                                    +800</div>
                                <div
                                    style="color: rgba(255,255,255,0.5); font-size: 0.8rem; font-weight: 700; letter-spacing: 0.05em; margin-top: 4px;">
                                    EMPRESAS INTEGRADAS</div>
                            </div>
                            <div style="width: 1px; background: rgba(255,255,255,0.1);"></div>
                            <div>
                                <div
                                    style="color: #fff; font-size: 1.75rem; font-weight: 950; letter-spacing: -0.04em;">
                                    &lt; 200ms</div>
                                <div
                                    style="color: rgba(255,255,255,0.5); font-size: 0.8rem; font-weight: 700; letter-spacing: 0.05em; margin-top: 4px;">
                                    LATENCIA MEDIA</div>
                            </div>
                            <div style="width: 1px; background: rgba(255,255,255,0.1);"></div>
                            <div>
                                <div
                                    style="color: #fff; font-size: 1.75rem; font-weight: 950; letter-spacing: -0.04em;">
                                    99.9%</div>
                                <div
                                    style="color: rgba(255,255,255,0.5); font-size: 0.8rem; font-weight: 700; letter-spacing: 0.05em; margin-top: 4px;">
                                    UPTIME SLA</div>
                            </div>
                        </div>

                        <!-- CTA Button + disclaimer stacked -->
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 14px;">
                            <a href="<?= site_url('register') ?>"
                                style="display: inline-block; background: linear-gradient(135deg, #facc15 0%, #f97316 100%); color: #0b1020; font-weight: 900; font-size: 1.05rem; padding: 18px 48px; border-radius: 16px; text-decoration: none; box-shadow: 0 10px 30px -5px rgba(15,23,42,0.45); transition: all 0.3s ease; letter-spacing: 0.01em;">
                                Obtener API Key gratis
                            </a>
                            <span style="color: rgba(255,255,255,0.45); font-size: 0.82rem; font-weight: 600;">No
                                requiere tarjeta de crédito para empezar</span>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- FAQ SCHEMA -->
        <script type="application/ld+json">
        {
          "@context": "https://schema.org",
          "@type": "FAQPage",
          "mainEntity": [
            {
              "@type": "Question",
              "name": "¿Cómo se integra la API de empresas?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "La integración se realiza mediante una API REST estándar usando una API Key. Tienes ejemplos disponibles en Python, PHP y Node.js."
              }
            },
            {
              "@type": "Question",
              "name": "¿Qué frecuencia de actualización tienen los datos del BORME?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Monitorizamos el BORME diariamente, procesando altas y cambios societarios pocas horas después de su publicación oficial."
              }
            },
            {
              "@type": "Question",
              "name": "¿Ofrecen soporte para Webhooks?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Sí, el plan Business permite configurar Webhooks para recibir notificaciones push en tiempo real sobre eventos del Registro Mercantil."
              }
            }
          ]
        }
        </script>

    </main>

    <?= view('partials/footer') ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Tracking Inicial
            trackEvent('api_prices_view');

            $('#api_quick_unlock_form').on('submit', function(e) {
                e.preventDefault();
                const $btn = $(this).find('button');
                const $input = $(this).find('input');
                const email = $input.val();
                
                $btn.prop('disabled', true).text('Generando...');

                $.post('<?= site_url("api/quick-unlock") ?>', { email: email }, function(res) {
                    if (res.status === 'success') {
                        trackEvent('api_quick_unlock_success', { email: email });
                        Swal.fire({
                            title: '¡API Key Generada!',
                            text: 'Tu llave es: ' + res.api_key + '. Te redirigimos a la documentación.',
                            icon: 'success',
                            confirmButtonText: 'Ir a Documentación'
                        }).then(() => {
                            window.location.href = res.redirect;
                        });
                    } else if (res.status === 'exists') {
                        window.location.href = res.redirect;
                    } else {
                        Swal.fire('Error', res.message || 'Error al generar la llave', 'error');
                        $btn.prop('disabled', false).text('Obtener API Key Gratis');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Error de conexión', 'error');
                    $btn.prop('disabled', false).text('Obtener API Key Gratis');
                });
            });

            function trackEvent(type, metadata = {}) {
                $.post('<?= site_url("api/tracking/event") ?>', {
                    event_type: type,
                    source: 'api_landing',
                    metadata: JSON.stringify(metadata)
                });
            }
        });
    </script>
</body>

</html>