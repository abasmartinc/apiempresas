<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head', [
        'title' => 'API de Empresas España | Datos BORME, Scoring e IA en Tiempo Real',
        'excerptText' => 'La API definitiva para automatizar tu validación de clientes y captación comercial. Datos oficiales del BORME, Scoring IA y Webhooks en una sola integración.',
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
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            color: #fff;
            padding: 6px 16px;
            border-radius: 99px;
            font-size: 11px;
            font-weight: 800;
            letter-spacing: 0.05em;
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
        }

        .api-pricing-card__header h3 {
            font-size: 1.25rem;
            font-weight: 800;
            color: #64748b;
            margin-bottom: 8px;
            letter-spacing: 0.02em;
        }

        .api-pricing-card.featured .api-pricing-card__header h3 {
            color: #2563eb;
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
            color: #10b981;
            flex-shrink: 0;
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
            .api-pricing-grid { grid-template-columns: 1fr; }
            .api-integration-grid { grid-template-columns: 1fr !important; }
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
            box-shadow: 0 40px 80px -20px rgba(15,23,42,0.4);
            border: 1px solid rgba(255,255,255,0.06);
        }
        .api-terminal__bar {
            background: #1e293b;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }
        .api-terminal__dot {
            width: 12px; height: 12px;
            border-radius: 50%;
        }
        .api-terminal__dot--red   { background: #ff5f57; }
        .api-terminal__dot--amber { background: #febc2e; }
        .api-terminal__dot--green { background: #28c840; }
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
        .api-integration-stat:last-child { border-bottom: none; }
        .api-integration-stat__icon {
            width: 40px; height: 40px;
            background: #eff6ff;
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
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
            box-shadow: 0 30px 60px -15px rgba(0,0,0,0.12);
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
            width: 42px; height: 42px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            border-radius: 12px;
            color: #fff;
            font-weight: 900;
            font-size: 14px;
            display: flex; align-items: center; justify-content: center;
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
        .api-preview-card__body { padding: 24px; }
        .api-score-sector { margin-bottom: 24px; }
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
        .api-score-value span { font-size: 1rem; color: #94a3b8; font-weight: 600; }
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
        .api-signals-tags { display: flex; flex-wrap: wrap; gap: 6px; }
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
            0% { transform: translate(0, 0) scale(1); opacity: 0.4; }
            33% { transform: translate(30px, -50px) scale(1.2); opacity: 0.7; }
            66% { transform: translate(-20px, 20px) scale(0.9); opacity: 0.3; }
            100% { transform: translate(0, 0) scale(1); opacity: 0.4; }
        }
        @keyframes mesh-glow-2 {
            0% { transform: translate(0, 0) scale(1); opacity: 0.35; }
            33% { transform: translate(-40px, 30px) scale(1.1); opacity: 0.6; }
            66% { transform: translate(25px, -20px) scale(1.3); opacity: 0.45; }
            100% { transform: translate(0, 0) scale(1); opacity: 0.35; }
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
                <div class="radar-hero__shell">
                    <div class="radar-hero__badge api-hero__badge">
                        <span class="radar-hero__badge-dot api-hero__badge-dot"></span>
                        API COMERCIAL DE ALTO RENDIMIENTO
                    </div>

                    <h1 class="radar-hero__title">
                        Convierte datos en ingresos.
                        <span>Automatización B2B real.</span>
                    </h1>

                    <p class="radar-hero__subtitle">
                        Integra datos oficiales del BORME, <strong>Algoritmos de Scoring IA</strong> y señales de
                        mercado en tiempo real. La API definitiva para validar clientes y detectar oportunidades antes
                        que nadie.
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
                            Empezar gratis hoy
                        </a>
                        <a href="<?= site_url('documentation') ?>" class="radar-btn radar-btn--ghost">
                            Explorar documentación
                        </a>
                    </div>

                    <!-- API UI PREVIEW BLOCK -->
                    <div class="api-preview-card" style="max-width: 500px; margin: 40px auto 0;">
                        <div class="api-preview-card__header">
                             <div class="api-preview-card__logo">TS</div>
                             <div class="api-preview-card__info">
                                 <h4>Tech Flow Solutions SL</h4>
                                 <span>B12345678 • Madrid</span>
                             </div>
                             <div class="api-preview-card__status">ACTIVA</div>
                        </div>

                        <div class="api-preview-card__body">
                            <div class="api-score-sector">
                                <div class="api-score-main">
                                    <div class="api-score-label">COMERCIAL SCORE</div>
                                    <div class="api-score-value">94<span>/100</span></div>
                                </div>
                                <div class="api-score-bar">
                                    <div class="api-score-progress" style="width: 94%;"></div>
                                </div>
                            </div>

                            <div class="api-insights-grid">
                                <div class="api-insight-item">
                                    <label>IA INSIGHTS</label>
                                    <p>Alta probabilidad de inversión en digitalización. Empresa en expansión detectada en BORME.</p>
                                </div>
                                <div class="api-insight-item">
                                    <label>SEÑALES RECIENTES</label>
                                    <div class="api-signals-tags">
                                        <span>Constitución</span>
                                        <span>Ampliación</span>
                                    </div>
                                </div>
                            </div>
                        </div>
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
                        <h3>Validación Impecable</h3>
                        <p class="radar-text">Elimina errores en facturación y registro. Nuestra API valida CIFs y
                            extrae datos oficiales al instante.</p>
                    </div>
                    <div class="api-feature-card">
                        <div class="api-feature-icon">🎯</div>
                        <h3>Scoring IA (0-100)</h3>
                        <p class="radar-text">Prioriza tu equipo comercial. Identifica qué nuevas empresas tienen mayor
                            capital y probabilidad de compra.</p>
                    </div>
                    <div class="api-feature-card">
                        <div class="api-feature-icon">⚡</div>
                        <h3>Sincronización Push</h3>
                        <p class="radar-text">Recibe Webhooks automáticos cuando detectemos un cambio relevante en el
                            BORME o una nueva constitución.</p>
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
                                <td style="padding: 18px 20px; font-family: monospace; font-weight: 700; color: #1e40af;">GET /companies</td>
                                <td style="padding: 18px 20px; color: #475569; font-weight: 600;">Enriquecimiento total y validación oficial de cualquier sociedad.</td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="color: #10b981; font-weight: 800; font-size: 11px; letter-spacing: 0.05em;">ESTABLE</span></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px; font-family: monospace; font-weight: 700; color: #1e40af;">GET /companies/search</td>
                                <td style="padding: 18px 20px; color: #475569; font-weight: 600;">Búsqueda predictiva por nombre de empresa o razón social.</td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="color: #10b981; font-weight: 800; font-size: 11px; letter-spacing: 0.05em;">ESTABLE</span></td>
                            </tr>

                            <!-- ENRICHMENT & IA -->
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px; font-family: monospace; font-weight: 700; color: #1e40af;">GET /companies/score</td>
                                <td style="padding: 18px 20px; color: #475569; font-weight: 600;">Obtiene el score comercial y prioridad de captación.</td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="color: #3b82f6; font-weight: 800; font-size: 11px; letter-spacing: 0.05em;">PRO / BUS</span></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px; font-family: monospace; font-weight: 700; color: #1e40af;">GET /companies/signals</td>
                                <td style="padding: 18px 20px; color: #475569; font-weight: 600;">Lista de señales detectadas (ej: nuevos actos BORME).</td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="color: #3b82f6; font-weight: 800; font-size: 11px; letter-spacing: 0.05em;">PRO / BUS</span></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px; font-family: monospace; font-weight: 700; color: #1e40af;">GET /companies/insights</td>
                                <td style="padding: 18px 20px; color: #475569; font-weight: 600;">Análisis IA: Resumen de nicho, necesidades y probabilidad.</td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="color: #8b5cf6; font-weight: 800; font-size: 11px; letter-spacing: 0.05em;">IA PREMIUM</span></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px; font-family: monospace; font-weight: 700; color: #1e40af;">GET /companies/contact-prep</td>
                                <td style="padding: 18px 20px; color: #475569; font-weight: 600;">Pitch de ventas sugerido y manejo de objeciones por IA.</td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="color: #8b5cf6; font-weight: 800; font-size: 11px; letter-spacing: 0.05em;">IA PREMIUM</span></td>
                            </tr>

                            <!-- RADAR -->
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px; font-family: monospace; font-weight: 700; color: #1e40af;">GET /companies/radar</td>
                                <td style="padding: 18px 20px; color: #475569; font-weight: 600;">Radar de nuevas empresas con filtros avanzados de provincia.</td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="color: #3b82f6; font-weight: 800; font-size: 11px; letter-spacing: 0.05em;">PRO / BUS</span></td>
                            </tr>

                            <!-- WEBHOOKS -->
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px; font-family: monospace; font-weight: 700; color: #1e40af;">GET /webhooks</td>
                                <td style="padding: 18px 20px; color: #475569; font-weight: 600;">Lista tus webhooks registrados y su estado actual.</td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="color: #d946ef; font-weight: 800; font-size: 11px; letter-spacing: 0.05em;">BUSINESS</span></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px; font-family: monospace; font-weight: 700; color: #1e40af;">POST /webhooks</td>
                                <td style="padding: 18px 20px; color: #475569; font-weight: 600;">Registra una nueva URL para recibir eventos push.</td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="color: #d946ef; font-weight: 800; font-size: 11px; letter-spacing: 0.05em;">BUSINESS</span></td>
                            </tr>
                            <tr class="api-endpoint-row">
                                <td style="padding: 18px 20px; font-family: monospace; font-weight: 700; color: #1e40af;">DELETE /webhooks/{id}</td>
                                <td style="padding: 18px 20px; color: #475569; font-weight: 600;">Elimina una suscripción de webhook de forma programática.</td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="color: #d946ef; font-weight: 800; font-size: 11px; letter-spacing: 0.05em;">BUSINESS</span></td>
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
                    <div class="api-pricing-card">
                        <div class="api-pricing-card__header">
                            <h3>Free</h3>
                        </div>
                        <div class="api-price-value">0€<span>/mes</span></div>
                        <p class="api-pricing-card__desc">Ideal para desarrollos iniciales, pruebas de concepto y
                            validación técnica.</p>

                        <ul class="api-price-list">
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> 100 consultas / mes</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Datos básicos oficiales</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Acceso a /companies</li>
                        </ul>

                        <a href="<?= site_url('register') ?>" class="api-pricing-btn">Empezar Gratis</a>
                    </div>

                    <!-- PRO -->
                    <div class="api-pricing-card featured">
                        <div class="api-pricing-card__header">
                            <h3>Pro</h3>
                        </div>
                        <div class="api-price-value">19€<span>/mes</span></div>
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
                    <div class="api-pricing-card">
                        <div class="api-pricing-card__header">
                            <h3>Business</h3>
                        </div>
                        <div class="api-price-value">49€<span>/mes</span></div>
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
                    <h2 class="radar-title">Integra en minutos, escala sin límites</h2>
                    <p class="radar-subtitle">Una llamada REST. Sin SDKs complicados. Soporte oficial para los lenguajes más populares.</p>
                </div>

                <!-- Language tabs -->
                <div style="display: flex; justify-content: center; gap: 8px; margin-bottom: 24px; margin-top: 40px;">
                    <button onclick="switchTab('python')" id="tab-python" style="padding: 10px 22px; border-radius: 99px; font-size: 12px; font-weight: 800; letter-spacing: 0.05em; cursor: pointer; border: 2px solid #2563eb; color: #fff; background: #2563eb; transition: all 0.2s;">PYTHON</button>
                    <button onclick="switchTab('php')" id="tab-php" style="padding: 10px 22px; border-radius: 99px; font-size: 12px; font-weight: 800; letter-spacing: 0.05em; cursor: pointer; border: 2px solid #e2e8f0; color: #94a3b8; background: #fff; transition: all 0.2s;">PHP / LARAVEL</button>
                    <button onclick="switchTab('node')" id="tab-node" style="padding: 10px 22px; border-radius: 99px; font-size: 12px; font-weight: 800; letter-spacing: 0.05em; cursor: pointer; border: 2px solid #e2e8f0; color: #94a3b8; background: #fff; transition: all 0.2s;">NODE.JS</button>
                </div>

                <!-- Code window -->
                <div style="background: #0f172a; border-radius: 20px; overflow: hidden; max-width: 780px; margin: 0 auto; box-shadow: 0 30px 60px -15px rgba(15,23,42,0.2); border: 1px solid #1e293b;">
                    <div style="background: #1e293b; padding: 14px 20px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <span style="width:12px;height:12px;border-radius:50%;background:#ff5f57;display:inline-block;"></span>
                        <span style="width:12px;height:12px;border-radius:50%;background:#febc2e;display:inline-block;"></span>
                        <span style="width:12px;height:12px;border-radius:50%;background:#28c840;display:inline-block;"></span>
                        <span id="tab-filename" style="flex:1;text-align:center;font-size:12px;color:#64748b;font-weight:700;font-family:monospace;">api_empresas.py</span>
                    </div>
<pre id="code-python" style="margin:0;padding:32px 36px;font-family:'Fira Code','Courier New',monospace;font-size:13.5px;line-height:2;color:#e2e8f0;overflow-x:auto;"><span class="api-code-keyword">import</span> requests

url     = <span class="api-code-string">"https://apiempresas.es/api/v1/companies"</span>
params  = {<span class="api-code-attr">"cif"</span>: <span class="api-code-string">"B12345678"</span>}
headers = {<span class="api-code-attr">"X-API-KEY"</span>: <span class="api-code-string">"tu_clave_aqui"</span>}

res  = requests.get(url, params=params, headers=headers)
data = res.json()

<span class="api-code-keyword">print</span>(data[<span class="api-code-string">"company_name"</span>])  <span style="color:#475569"># → Tech Flow Solutions SL</span>
<span class="api-code-keyword">print</span>(data[<span class="api-code-string">"scoring"</span>])       <span style="color:#475569"># → 94</span>
<span class="api-code-keyword">print</span>(data[<span class="api-code-string">"ia_insights"</span>])   <span style="color:#475569"># → Alta prob. digitalización</span></pre>
<pre id="code-php" style="display:none;margin:0;padding:32px 36px;font-family:'Fira Code','Courier New',monospace;font-size:13.5px;line-height:2;color:#e2e8f0;overflow-x:auto;"><span class="api-code-keyword">$ch</span> = curl_init();

curl_setopt_array(<span class="api-code-keyword">$ch</span>, [
    CURLOPT_URL            => <span class="api-code-string">"https://apiempresas.es/api/v1/companies?cif=B12345678"</span>,
    CURLOPT_HTTPHEADER     => [<span class="api-code-string">"X-API-KEY: tu_clave_aqui"</span>],
    CURLOPT_RETURNTRANSFER => <span class="api-code-keyword">true</span>,
]);

<span class="api-code-keyword">$data</span> = json_decode(curl_exec(<span class="api-code-keyword">$ch</span>), <span class="api-code-keyword">true</span>);

<span class="api-code-keyword">echo</span> <span class="api-code-keyword">$data</span>[<span class="api-code-string">'company_name'</span>];  <span style="color:#475569">// → Tech Flow Solutions SL</span>
<span class="api-code-keyword">echo</span> <span class="api-code-keyword">$data</span>[<span class="api-code-string">'scoring'</span>];       <span style="color:#475569">// → 94</span></pre>
<pre id="code-node" style="display:none;margin:0;padding:32px 36px;font-family:'Fira Code','Courier New',monospace;font-size:13.5px;line-height:2;color:#e2e8f0;overflow-x:auto;"><span class="api-code-keyword">const</span> axios = require(<span class="api-code-string">'axios'</span>);

<span class="api-code-keyword">const</span> res = <span class="api-code-keyword">await</span> axios.get(<span class="api-code-string">'https://apiempresas.es/api/v1/companies'</span>, {
  params:  { cif: <span class="api-code-string">'B12345678'</span> },
  headers: { <span class="api-code-string">'X-API-KEY'</span>: <span class="api-code-string">'tu_clave_aqui'</span> }
});

<span class="api-code-keyword">const</span> { company_name, scoring, ia_insights } = res.data;
console.log(company_name);  <span style="color:#475569">// → Tech Flow Solutions SL</span>
console.log(scoring);       <span style="color:#475569">// → 94</span></pre>
                </div>

                <!-- Feature pills below code -->
                <div style="display: flex; justify-content: center; gap: 32px; margin-top: 40px; flex-wrap: wrap;">
                    <div style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:0.88rem;font-weight:700;">
                        <span style="background:#eff6ff;color:#2563eb;padding:6px 8px;border-radius:8px;font-size:14px;">⚡</span> Respuesta &lt; 200ms
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:0.88rem;font-weight:700;">
                        <span style="background:#eff6ff;color:#2563eb;padding:6px 8px;border-radius:8px;font-size:14px;">🔐</span> Auth por API Key
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:0.88rem;font-weight:700;">
                        <span style="background:#eff6ff;color:#2563eb;padding:6px 8px;border-radius:8px;font-size:14px;">📄</span> Docs completas
                    </div>
                    <div style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:0.88rem;font-weight:700;">
                        <span style="background:#eff6ff;color:#2563eb;padding:6px 8px;border-radius:8px;font-size:14px;">🤖</span> IA en cada respuesta
                    </div>
                </div>
            </div>
        </section>

        <script>
        function switchTab(lang) {
            const names = { python: 'api_empresas.py', php: 'api_empresas.php', node: 'api_empresas.js' };
            ['python','php','node'].forEach(l => {
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


        <!-- FINAL CTA -->
        <section style="padding: 80px 0 100px;">
            <div class="container">
                <div style="background: #0f172a; border-radius: 32px; padding: 80px 60px; text-align: center; position: relative; overflow: hidden; box-shadow: 0 40px 80px -20px rgba(15, 23, 42, 0.5);">
                    
                    <!-- Solid Background Gradient -->
                    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 40%, #0369a1 100%);"></div>

                    <!-- Technical Grid Pattern -->
                    <div class="technical-grid" style="position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px); background-size: 30px 30px; opacity: 0.4;"></div>
                    
                    <!-- Animated Background Blobs -->
                    <div style="position: absolute; top: -10%; right: -10%; width: 50%; height: 50%; background: radial-gradient(circle, rgba(99,179,237,0.5) 0%, transparent 70%); border-radius: 50%; filter: blur(40px); animation: mesh-glow-1 15s infinite ease-in-out; pointer-events: none;"></div>
                    <div style="position: absolute; bottom: -10%; left: -10%; width: 60%; height: 60%; background: radial-gradient(circle, rgba(16,185,129,0.4) 0%, transparent 70%); border-radius: 50%; filter: blur(50px); animation: mesh-glow-2 18s infinite ease-in-out; pointer-events: none; animation-delay: -2s;"></div>

                    <!-- Content (ensure relative and above background) -->
                    <div style="position: relative; z-index: 2;">
                        <!-- Badge -->
                        <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); border-radius: 99px; padding: 6px 16px; margin-bottom: 28px;">
                            <span style="width: 8px; height: 8px; background: #34d399; border-radius: 50%; box-shadow: 0 0 8px #34d399;"></span>
                            <span style="color: rgba(255,255,255,0.9); font-size: 11px; font-weight: 800; letter-spacing: 0.08em;">SISTEMA LISTO</span>
                        </div>

                        <!-- Headline -->
                        <h2 style="color: #ffffff; font-size: 2.75rem; font-weight: 950; margin: 0 0 16px; letter-spacing: -0.04em; line-height: 1.1;">Empieza a construir el<br>futuro de tu negocio</h2>
                        <p style="color: rgba(255,255,255,0.65); font-size: 1.1rem; font-weight: 500; margin: 0 0 48px; max-width: 480px; margin-left: auto; margin-right: auto; line-height: 1.6;">Únete a las empresas que ya automatizan su validación mercantil y captan clientes antes que nadie.</p>

                        <!-- Stats row -->
                        <div style="display: flex; justify-content: center; gap: 48px; margin-bottom: 52px; flex-wrap: wrap;">
                            <div>
                                <div style="color: #fff; font-size: 1.75rem; font-weight: 950; letter-spacing: -0.04em;">+800</div>
                                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; font-weight: 700; letter-spacing: 0.05em; margin-top: 4px;">EMPRESAS INTEGRADAS</div>
                            </div>
                            <div style="width: 1px; background: rgba(255,255,255,0.1);"></div>
                            <div>
                                <div style="color: #fff; font-size: 1.75rem; font-weight: 950; letter-spacing: -0.04em;">&lt; 200ms</div>
                                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; font-weight: 700; letter-spacing: 0.05em; margin-top: 4px;">LATENCIA MEDIA</div>
                            </div>
                            <div style="width: 1px; background: rgba(255,255,255,0.1);"></div>
                            <div>
                                <div style="color: #fff; font-size: 1.75rem; font-weight: 950; letter-spacing: -0.04em;">99.9%</div>
                                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; font-weight: 700; letter-spacing: 0.05em; margin-top: 4px;">UPTIME SLA</div>
                            </div>
                        </div>

                        <!-- CTA Button + disclaimer stacked -->
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 14px;">
                            <a href="<?= site_url('register') ?>" style="display: inline-block; background: linear-gradient(135deg, #facc15 0%, #f97316 100%); color: #0b1020; font-weight: 900; font-size: 1.05rem; padding: 18px 48px; border-radius: 16px; text-decoration: none; box-shadow: 0 10px 30px -5px rgba(15,23,42,0.45); transition: all 0.3s ease; letter-spacing: 0.01em;">
                                Crear cuenta gratuita
                            </a>
                            <span style="color: rgba(255,255,255,0.45); font-size: 0.82rem; font-weight: 600;">No requiere tarjeta de crédito para empezar</span>
                        </div>
                    </div>

                </div>
            </div>
        </section>

    </main>

    <?= view('partials/footer') ?>
</body>

</html>