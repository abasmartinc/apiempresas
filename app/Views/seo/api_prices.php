<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head', [
        'title' => 'Infraestructura API para Datos Mercantiles y KYB en España | APIEmpresas.es',
        'excerptText' => 'Integra datos oficiales del Registro Mercantil y BORME en tu CRM o ERP. API REST diseñada para enriquecimiento de datos B2B, onboarding KYB automatizado y scoring IA.',
        'canonical' => site_url('api-empresas'),
        'robots' => 'index,follow',
    ]) ?>
    <link rel="stylesheet"
        href="<?= base_url('public/css/precios_radar.css?v=' . (file_exists(FCPATH . 'public/css/precios_radar.css') ? filemtime(FCPATH . 'public/css/precios_radar.css') : time())) ?>" />
    <style>
        /* ── API HERO UNIFICADO ── */
        .api-unified-hero {
            padding: 44px 0 72px;
            background: linear-gradient(160deg, #060a14 0%, #0c1428 50%, #0f172a 100%);
            color: #fff;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .api-unified-hero::before {
            content: '';
            position: absolute;
            top: -20%; right: -8%;
            width: 42%; height: 85%;
            background: radial-gradient(circle, rgba(59,130,246,0.14) 0%, transparent 70%);
            pointer-events: none;
        }
        .api-unified-hero::after {
            content: '';
            position: absolute;
            bottom: -10%; left: -5%;
            width: 35%; height: 60%;
            background: radial-gradient(circle, rgba(99,102,241,0.09) 0%, transparent 70%);
            pointer-events: none;
        }
        .api-hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(59,130,246,0.15);
            color: #60A5FA;
            padding: 6px 16px;
            border-radius: 99px;
            font-size: 0.82rem;
            font-weight: 700;
            margin-bottom: 1.75rem;
            border: 1px solid rgba(59,130,246,0.25);
            letter-spacing: 0.03em;
            text-transform: uppercase;
        }
        .api-hero-badge-dot {
            display: inline-block;
            width: 7px; height: 7px;
            background: #34D399;
            border-radius: 99px;
            box-shadow: 0 0 8px #34D399;
            animation: heroPulse 2s ease-in-out infinite;
        }
        @keyframes heroPulse {
            0%,100% { opacity:1; transform:scale(1); }
            50% { opacity:0.7; transform:scale(1.3); }
        }
        .api-hero-title {
            font-size: clamp(2.2rem, 4vw, 3.4rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #fff;
            line-height: 1.1;
            margin-bottom: 1.25rem;
        }
        .api-hero-title span {
            background: linear-gradient(135deg, #60A5FA 0%, #818cf8 60%, #34D399 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .api-hero-sub {
            font-size: 1.15rem;
            color: #cbd5e1;
            max-width: 680px;
            margin: 0 auto 1.5rem;
            line-height: 1.65;
        }
        .api-hero-stars {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin-bottom: 1.25rem;
        }
        .api-hero-badges {
            display: flex;
            gap: 14px;
            flex-wrap: wrap;
            justify-content: center;
            margin-bottom: 2.25rem;
        }
        .api-hero-chip {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            border-radius: 99px;
            font-size: 0.82rem;
            font-weight: 700;
            border: 1px solid;
        }
        .api-hero-chip--green  { background: rgba(16,185,129,0.15); color: #34D399; border-color: rgba(16,185,129,0.25); }
        .api-hero-chip--blue   { background: rgba(96,165,250,0.15); color: #60A5FA; border-color: rgba(96,165,250,0.25); }
        .api-hero-chip--purple { background: rgba(129,140,248,0.15); color: #a5b4fc; border-color: rgba(129,140,248,0.25); }
        .api-hero-actions {
            display: flex;
            gap: 14px;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 3rem;
        }
        .api-hero-btn-primary {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            color: #fff;
            padding: 14px 32px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 1rem;
            text-decoration: none;
            box-shadow: 0 10px 30px rgba(37,99,235,0.35);
            transition: all 0.25s;
        }
        .api-hero-btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 16px 40px rgba(37,99,235,0.45);
            color: #fff;
        }
        .api-hero-btn-ghost {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(255,255,255,0.07);
            color: #e2e8f0;
            padding: 14px 28px;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1rem;
            text-decoration: none;
            border: 1px solid rgba(255,255,255,0.15);
            backdrop-filter: blur(8px);
            transition: all 0.25s;
        }
        .api-hero-btn-ghost:hover {
            background: rgba(255,255,255,0.12);
            border-color: rgba(255,255,255,0.3);
            color: #fff;
        }
        /* Keep radar-hero for other pages */
        .radar-hero { border-bottom: none !important; }

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
            content: "RECOMENDADO PARA SAAS/ERP";
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

        <!-- HERO SECTION UNIFICADO -->
        <header class="api-unified-hero">
            <div class="container" style="max-width:1100px; margin:0 auto; padding:0 2rem;">

                <!-- Badge -->
                <div class="api-hero-badge">
                    <span class="api-hero-badge-dot"></span>
                    API REST · Infraestructura de Datos a Escala
                </div>

                <!-- H1 -->
                <h1 class="api-hero-title">
                    API REST de Datos Mercantiles<br>
                    <span>Registro Mercantil y BORME</span>
                </h1>

                <!-- Subtitle -->
                <p class="api-hero-sub">
                    Endpoints JSON para consulta por CIF/NIF, enriquecimiento B2B masivo,
                    webhooks de alertas BORME y onboarding KYB automatizado.
                    Latencia &lt;200ms &middot; 99.9% SLA.
                </p>

                <!-- Stars -->
                <div class="api-hero-stars">
                    <div style="display:flex; color:#fbbf24;">
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                        <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                    </div>
                    <span style="color:#94a3b8; font-size:0.92rem;">Usado por <strong>+1.200</strong> desarrolladores y equipos SaaS</span>
                </div>

                <!-- Badges -->
                <div class="api-hero-badges">
                    <span class="api-hero-chip api-hero-chip--green">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Datos Oficiales BORME
                    </span>
                    <span class="api-hero-chip api-hero-chip--blue">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        REST API · &lt;200ms
                    </span>
                    <span class="api-hero-chip api-hero-chip--purple">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        KYB &amp; Compliance Listo
                    </span>
                </div>

                <!-- CTAs -->
                <div class="api-hero-actions">
                    <a href="<?= site_url('register') ?>" class="api-hero-btn-primary">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                        Probar API gratis
                    </a>
                    <a href="<?= site_url('documentation') ?>" class="api-hero-btn-ghost">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Ver documentación
                    </a>
                </div>

                <!-- Proof items -->
                <div style="display:flex; gap:32px; justify-content:center; flex-wrap:wrap; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.07);">
                    <div style="text-align:center;">
                        <div style="font-size:1.6rem; font-weight:900; color:#fff; letter-spacing:-0.04em;">+3.5M</div>
                        <div style="font-size:0.78rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px;">Empresas indexadas</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:1.6rem; font-weight:900; color:#fff; letter-spacing:-0.04em;">&lt;200ms</div>
                        <div style="font-size:0.78rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px;">Latencia media</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:1.6rem; font-weight:900; color:#fff; letter-spacing:-0.04em;">99.9%</div>
                        <div style="font-size:0.78rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px;">Uptime garantizado</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:1.6rem; font-weight:900; color:#fff; letter-spacing:-0.04em;">Diario</div>
                        <div style="font-size:0.78rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px;">Sincronización BORME</div>
                    </div>
                </div>

            </div>
        </header>


        <!-- SEO BLOCK: CÓMO FUNCIONA -->
        <section class="radar-section" style="padding: 120px 0; background: #fbfcfe;">
            <div class="container">
                <div style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 80px; align-items: center;">
                    <div>
                        <div class="radar-kicker">Integración sin fricción</div>
                        <h2 class="radar-title"
                            style="margin-top: 12px; margin-bottom: 32px; font-size: 2.75rem; letter-spacing: -0.03em;">
                            Arquitectura orientada a la automatización masiva</h2>

                        <p class="radar-text"
                            style="font-size: 1.2rem; line-height: 1.7; color: #334155; margin-bottom: 40px; max-width: 600px;">
                            Nuestra infraestructura abstrae la complejidad del Registro Mercantil ofreciendo una interfaz REST de baja latencia para sistemas de misión crítica.
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
                                Consultar CIF en Web
                            </a>
                            <a href="<?= site_url('documentation') ?>"
                                style="font-weight: 800; color: #2563eb; text-decoration: none; display: flex; align-items: center; gap: 8px; font-size: 1.05rem;">
                                Guía de integración técnica →
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
                                style="margin: 0; font-family: 'Fira Code', monospace; font-size: 14px; line-height: 1.6;">
<span style="color: #c678dd;">GET</span> <span style="color: #98c379;">/v1/companies?cif=B12345678</span>
<span style="color: #abb2bf;">{</span>
  <span style="color: #d19a66;">"success"</span>: <span style="color: #d19a66;">true</span>,
  <span style="color: #d19a66;">"data"</span>: <span style="color: #abb2bf;">{</span>
    <span style="color: #d19a66;">"name"</span>: <span style="color: #98c379;">"TECH FLOW SOLUTIONS SL"</span>,
    <span style="color: #d19a66;">"cif"</span>: <span style="color: #98c379;">"B12345678"</span>,
    <span style="color: #d19a66;">"status"</span>: <span style="color: #98c379;">"ACTIVA"</span>,
    <span style="color: #d19a66;">"founded"</span>: <span style="color: #98c379;">"2024-03-12"</span>,
    <span style="color: #d19a66;">"cnae"</span>: <span style="color: #98c379;">"6201"</span>,
    <span style="color: #d19a66;">"cnae_label"</span>: <span style="color: #98c379;">"Programación informática"</span>,
    <span style="color: #d19a66;">"municipality"</span>: <span style="color: #98c379;">"MADRID"</span>,
    <span style="color: #d19a66;">"score"</span>: <span style="color: #d19a66;">94</span>
  <span style="color: #abb2bf;">}</span>
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
                        <div class="radar-kicker">Potencia para tu stack</div>
                        <h2 class="radar-title">Capacidades de Enriquecimiento B2B</h2>
                        <p class="radar-text">Integra inteligencia mercantil en tu flujo de trabajo y elimina los silos de información desactualizada en tu base de datos.</p>
                        <ul style="display: grid; gap: 12px; margin-top: 24px;">
                            <li
                                style="display: flex; align-items: center; gap: 12px; font-weight: 700; color: #475569;">
                                <span style="color: #3b82f6;">✓</span> Onboarding KYB en segundos
                            </li>
                            <li
                                style="display: flex; align-items: center; gap: 12px; font-weight: 700; color: #475569;">
                                <span style="color: #3b82f6;">✓</span> Sincronización masiva de bases de datos
                            </li>
                            <li
                                style="display: flex; align-items: center; gap: 12px; font-weight: 700; color: #475569;">
                                <span style="color: #3b82f6;">✓</span> Enriquecimiento de CRM (Salesforce, HubSpot, etc.)
                            </li>
                            <li
                                style="display: flex; align-items: center; gap: 12px; font-weight: 700; color: #475569;">
                                <span style="color: #3b82f6;">✓</span> Monitorización de insolvencias y cambios BORME
                            </li>
                            <li
                                style="display: flex; align-items: center; gap: 12px; font-weight: 700; color: #475569;">
                                <span style="color: #3b82f6;">✓</span> Scoring de salud mercantil vía IA
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
                        <h4 style="margin: 0; font-weight: 800;">Compliance & KYB Automatizado</h4>
                    </div>
                    <div
                        style="background: white; padding: 32px; border-radius: 20px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 16px;">📈</div>
                        <h4 style="margin: 0; font-weight: 800;">Enriquecimiento de Leads B2B</h4>
                    </div>
                    <div
                        style="background: white; padding: 32px; border-radius: 20px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 16px;">🔄</div>
                        <h4 style="margin: 0; font-weight: 800;">Sincronización de CRM/ERP</h4>
                    </div>
                    <div
                        style="background: white; padding: 32px; border-radius: 20px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 16px;">💳</div>
                        <h4 style="margin: 0; font-weight: 800;">Risk Analysis & Lending</h4>
                    </div>
                    <div
                        style="background: white; padding: 32px; border-radius: 20px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 16px;">🚀</div>
                        <h4 style="margin: 0; font-weight: 800;">SaaS Product Integration</h4>
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
                        <h3>Ingestión Masiva de Datos</h3>
                        <p class="radar-text">Diseñada para flujos de alta demanda. Nuestra arquitectura soporta miles de peticiones simultáneas sin degradación de latencia.</p>
                    </div>
                    <div class="api-feature-card">
                        <div class="api-feature-icon">🎯</div>
                        <h3>Scoring IA Predictivo</h3>
                        <p class="radar-text">Transforma datos brutos en inteligencia accionable. Prioriza tus esfuerzos comerciales basándote en la solvencia y potencial de cada empresa.</p>
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
                                    Utilidad de Negocio e Integración</th>
                                <th
                                    style="padding: 20px; text-align: center; font-weight: 800; font-size: 13px; color: #64748b; text-transform: uppercase;">
                                    Estado</th>
                                <th
                                    style="padding: 20px; text-align: center; font-weight: 800; font-size: 13px; color: #64748b; text-transform: uppercase;">
                                    Payload</th>
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
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Data Enrichment Legal</strong>
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
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('get_companies')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VER JSON</button>
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
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Normalización y Búsqueda</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Localiza empresas por nombre comercial o razón social. Incluye sugerencias
                                        fonéticas y coincidencias parciales con alta precisión.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">GÉNERICO</span>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('get_search')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VER JSON</button>
                                </td>
                            </tr>

                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        POST /companies/batch</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">JSON Array: cifs[]</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Consulta Múltiple (Batch)</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Consulta de golpe hasta 100 CIFs en una única petición ahorrando latencia de red. El consumo se calcula dinámicamente por cada CIF encontrado.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">PRO
                                        / BUS</span>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('post_batch')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VER JSON</button>
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
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('get_score')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VER JSON</button>
                                </td>
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
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('get_signals')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VER JSON</button>
                                </td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        GET /companies/borme</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parámetro: ?cif=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Historial
                                        de Actos del BORME</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Obtén el historial cronológico completo de publicaciones en el Registro Mercantil para una empresa, útil para auditoría KYC y Due Diligence.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">PRO
                                        / BUS</span></td>
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('get_borme')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VER JSON</button>
                                </td>
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
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Predictive Business Insights</strong>
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
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('get_insights')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VER JSON</button>
                                </td>
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
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('get_radar')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VER JSON</button>
                                </td>
                            </tr>

                            <!-- NETWORK -->
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        GET /companies/network</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parámetro: ?cif=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Grafos de Poder Societario</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Obtiene la red de vinculación entre empresas a través de sus administradores.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">PRO
                                        / BUS</span></td>
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('get_network')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VER JSON</button>
                                </td>
                            </tr>

                            <!-- MATCH -->
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        GET /companies/match</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Filtros: cif, seller_sector</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Calculadora de Match B2B</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Evalúa el encaje comercial entre una empresa prospecto y un sector de ventas, devolviendo un score y argumentario.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #fdf2f8; color: #db2777; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">BUSINESS</span></td>
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('get_match')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VER JSON</button>
                                </td>
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
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Sincronización PUSH (BORME)</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Registra tu URL de callback para recibir notificaciones HTTP en tiempo real
                                        cuando ocurra un evento de interés (ej: nueva empresa en tu zona).
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #fdf2f8; color: #db2777; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">BUSINESS</span>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('post_webhook')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VER JSON</button>
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
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('get_webhooks')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VER JSON</button>
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
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('delete_webhook')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VER JSON</button>
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

                <!-- TOGGLE ANUAL / MENSUAL -->
                <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 64px; margin-top: 16px; gap: 12px;">
                    <span style="font-size: 0.95rem; font-weight: 600; color: #94a3b8; transition: all 0.3s;" id="labelMonthly">Mensual</span>
                    <button type="button" id="billingToggle" style="width: 56px; height: 32px; background: #0f172a; border-radius: 99px; position: relative; cursor: pointer; border: none; padding: 4px; transition: background 0.3s;" onclick="togglePricing()">
                        <div id="toggleKnob" style="width: 24px; height: 24px; background: white; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1); transform: translateX(24px);"></div>
                    </button>
                    <span style="font-size: 0.95rem; font-weight: 800; color: #2563eb; display: flex; align-items: center; gap: 8px; transition: all 0.3s;" id="labelAnnual">Anual <span style="background: #dcfce7; color: #166534; font-size: 10px; padding: 4px 8px; border-radius: 99px; letter-spacing: 0.05em; font-weight: 800;">AHORRA 20%</span></span>
                </div>

                <div class="api-pricing-grid">

                    <!-- FREE -->
                    <div class="api-pricing-card free-plan">
                        <div class="api-pricing-card__header">
                            <h3>Free</h3>
                        </div>
                        <div class="api-price-value">0€<span>/ único</span></div>
                        <p class="api-pricing-card__desc">Para entornos de desarrollo, sandboxing técnico y validación de esquemas JSON.</p>

                        <ul class="api-price-list">
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> <?= $freeLimit ?> consultas garantizadas</li>
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
                        <div class="api-price-value"><b id="pricePro" data-monthly="19" data-annual="15" style="font-weight: inherit;">15</b>€<span>/ mes</span></div>
                        <p class="api-pricing-card__desc">Integración completa para procesos de onboarding B2B, enriquecimiento de leads y scoring.</p>

                        <ul class="api-price-list">
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> 3.000 consultas / mes</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Historial Actos BORME</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Scoring IA Incluido</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Acceso a Radar API</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Grafos de Poder Societario</li>
                        </ul>

                        <a href="<?= site_url('register') ?>" class="api-pricing-btn primary">Activar Plan Pro</a>
                    </div>

                    <!-- BUSINESS -->
                    <div class="api-pricing-card business-plan">
                        <div class="api-pricing-card__header">
                            <h3>Business</h3>
                        </div>
                        <div class="api-price-value"><b id="priceBusiness" data-monthly="49" data-annual="39" style="font-weight: inherit;">39</b>€<span>/ mes</span></div>
                        <p class="api-pricing-card__desc">Sincronización en tiempo real vía Webhooks y volumen masivo para plataformas de misión crítica.</p>

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
                                </svg> IA Insights & Predictiva de Negocio</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Calculadora de Match B2B</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Soporte Prioritario Slack</li>
                        </ul>

                        <a href="<?= site_url('register') ?>" class="api-pricing-btn">Activar Business</a>
                    </div>
                </div>

            </div>

            <div style="margin-top: 60px; background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 50%, #f0fdf4 100%); padding: 48px 32px; border-radius: 24px; text-align: center; position: relative; overflow: hidden; border: 1px solid rgba(59, 130, 246, 0.15); box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.1); max-width: 900px; margin-left: auto; margin-right: auto;">
                
                <!-- Patrón de puntos decorativo de fondo -->
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.4; background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px; pointer-events: none;"></div>
                
                <!-- Efectos de luz suaves -->
                <div style="position: absolute; top: -30%; left: -10%; width: 50%; height: 160%; background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 60%); pointer-events: none;"></div>
                <div style="position: absolute; bottom: -30%; right: -10%; width: 50%; height: 160%; background: radial-gradient(circle, rgba(16,185,129,0.08) 0%, transparent 60%); pointer-events: none;"></div>

                <div style="position: relative; z-index: 1;">
                    <div style="display: inline-block; background: #ffffff; color: #2563eb; font-size: 0.8rem; font-weight: 800; padding: 6px 16px; border-radius: 99px; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 16px; box-shadow: 0 4px 6px -1px rgba(37,99,235,0.1); border: 1px solid rgba(59,130,246,0.1);">Nuevo Plan a Medida</div>
                    <h3 style="color: #0f172a; font-size: 2.1rem; font-weight: 900; margin: 0 0 12px; letter-spacing: -0.03em;">¿No quieres ataduras mensuales?</h3>
                    <p style="color: #475569; font-size: 1.15rem; max-width: 600px; margin: 0 auto 32px; line-height: 1.6;">Diseña tu propio <strong style="color: #0f172a;">Bono de Créditos Prepago</strong>. Paga una sola vez, consúmelo a tu ritmo y consigue descuentos automáticos por volumen.</p>
                    
                    <a href="<?= site_url('crear-bono-api') ?>" style="display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: #fff; padding: 18px 40px; border-radius: 16px; font-weight: 800; font-size: 1.1rem; text-decoration: none; box-shadow: 0 10px 25px rgba(37,99,235,0.4); transition: all 0.3s ease; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="21" y1="4" x2="14" y2="4"></line>
                            <line x1="10" y1="4" x2="3" y2="4"></line>
                            <line x1="21" y1="12" x2="12" y2="12"></line>
                            <line x1="8" y1="12" x2="3" y2="12"></line>
                            <line x1="21" y1="20" x2="16" y2="20"></line>
                            <line x1="12" y1="20" x2="3" y2="20"></line>
                            <line x1="14" y1="1" x2="14" y2="7"></line>
                            <line x1="8" y1="9" x2="8" y2="15"></line>
                            <line x1="16" y1="17" x2="16" y2="23"></line>
                        </svg>
                        Crear mi Bono Personalizado
                    </a>
                </div>
            </div>

            <div style="margin-top: 24px; text-align: center; padding: 16px;">
                <p style="color: #64748b; font-weight: 700; margin: 0; font-size: 0.95rem;">¿Necesitas soporte Enterprise o facturación anual a medida? <a href="<?= site_url('contact') ?>" style="color: #3b82f6; text-decoration: none;">Hablemos de tu proyecto →</a></p>
            </div>
            </div>
        </section>

        <!-- CODE INTEGRATION -->
        <section class="radar-section radar-section--soft">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">Developer First</div>
                    <h2 class="radar-title">Implementación orientada a Desarrolladores</h2>
                    <p class="radar-subtitle">Documentación OpenAPI, autenticación mediante API Key y SDKs para una activación inmediata en producción.</p>
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
                    <h2 class="radar-title">Soporte Técnico e Integración</h2>
                    <p class="radar-subtitle">Resolvemos tus dudas sobre el flujo de datos mercantiles y las capacidades de nuestra infraestructura.</p>
                </div>

                <div class="api-faq">
                    <div class="api-faq-item active">
                        <button class="api-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>¿Cómo realizar la integración técnica de la API?</span>
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
                            <span>¿Cuál es la frecuencia de actualización de los datos societarios?</span>
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
                            <span>¿Es posible recibir alertas automáticas del BORME vía Webhooks?</span>
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
                            <span>¿Qué esquema de datos JSON devuelve el endpoint /companies?</span>
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
        <!-- JSON PREVIEW MODAL -->
        <div id="json-modal" style="display:none; position:fixed; inset:0; background:rgba(15,23,42,0.4); z-index:9999; backdrop-filter:blur(4px); align-items:center; justify-content:center; padding:20px;">
            <div style="background:#ffffff; width:100%; max-width:640px; border-radius:20px; border:1px solid #e2e8f0; box-shadow:0 30px 60px -12px rgba(15,23,42,0.15); overflow:hidden; position:relative;">
                <div style="background:#f8fafc; padding:18px 24px; display:flex; align-items:center; justify-content:space-between; border-bottom:1px solid #f1f5f9;">
                    <div style="display:flex; align-items:center; gap:12px;">
                        <span style="background:rgba(37,99,235,0.08); color:#2563eb; font-size:10px; font-weight:800; padding:4px 10px; border-radius:6px; letter-spacing:0.05em; text-transform:uppercase;">Response Data</span>
                        <span id="modal-endpoint-name" style="color:#475569; font-family:'Fira Code', monospace; font-size:13px; font-weight:700;">GET /v1/companies</span>
                    </div>
                    <button onclick="closeJsonModal()" style="background:none; border:none; color:#94a3b8; cursor:pointer; font-size:24px; line-height:1; transition:color 0.2s;" onmouseover="this.style.color='#0f172a'" onmouseout="this.style.color='#94a3b8'">&times;</button>
                </div>
                <div style="padding:32px; max-height:70vh; overflow-y:auto; background:#ffffff;">
                    <pre id="modal-json-content" style="margin:0; font-family:'Fira Code', 'Courier New', monospace; font-size:14px; line-height:1.6; color:#1e293b;"></pre>
                </div>
                <div style="background:#f8fafc; padding:16px 24px; text-align:right; border-top:1px solid #f1f5f9;">
                    <button onclick="closeJsonModal()" style="background:#ffffff; color:#475569; border:1px solid #e2e8f0; padding:10px 24px; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#ffffff'">Cerrar ventana</button>
                </div>
            </div>
        </div>

        <script>
            const jsonExamples = {
                get_companies: {
                    success: true,
                    data: {
                        id: 12345,
                        name: "TECH FLOW SOLUTIONS SL",
                        cif: "B12345678",
                        cnae: "6201",
                        cnae_label: "Programación informática",
                        founded: "2024-03-12",
                        province: "MADRID",
                        municipality: "MADRID",
                        address: "CALLE DE LA TECNOLOGIA 42",
                        status: "ACTIVA",
                        score: 94
                    }
                },
                get_search: {
                    success: true,
                    data: {
                        name: "TECH FLOW SOLUTIONS SL",
                        cif: "B12345678",
                        score: 94,
                        province: "MADRID",
                        status: "ACTIVA"
                    }
                },
                post_batch: {
                    success: true,
                    data: [
                        { name: "INDUSTRIA DE DISENO TEXTIL SA", cif: "A15075062" },
                        { name: "INDITEX LOGISTICA SA", cif: "B00000001" }
                    ],
                    meta: {
                        requested: 2,
                        found: 2,
                        cost: 2,
                        truncated: false
                    }
                },
                get_score: {
                    success: true,
                    data: {
                        cif: "B12345678",
                        score: 94,
                        priority: "MUY_ALTA",
                        reasons: ["Crecimiento de capital reciente", "Alta actividad en BORME"],
                        last_signal: {
                            type: "AMPLIACION_CAPITAL",
                            date: "2024-05-01"
                        }
                    }
                },
                get_signals: {
                    success: true,
                    data: {
                        cif: "B12345678",
                        signals: [
                            {
                                type: "borme_event",
                                label: "AMPLIACION_CAPITAL",
                                date: "2024-05-01",
                                probability: "MUY_ALTA"
                            }
                        ]
                    }
                },
                get_borme: {
                    success: true,
                    data: {
                        cif: "B12345678",
                        company_name: "EMPRESA DE EJEMPLO SL",
                        events: [
                            {
                                date: "2023-11-01",
                                act_types: "Nombramientos, Ceses",
                                description: "Ceses/Dimisiones. Administrador único: JUAN PEREZ...",
                                url_pdf: "https://www.boe.es/borme/dias/2023/11/01/pdfs/BORME-A-2023-100-28.pdf"
                            }
                        ]
                    }
                },
                get_insights: {
                    success: true,
                    data: {
                        profile: "SaaS / Fintech / Cloud",
                        summary: "Empresa con alta tracción y necesidad inminente de escalado tecnológico.",
                        needs: ["Infraestructura Cloud", "Ciberseguridad", "Contratación Devs"],
                        conversion_probability: "HIGH",
                        estimated_ticket: "10k-50k€"
                    }
                },
                get_radar: {
                    success: true,
                    meta: {
                        plan: "business",
                        count: 142,
                        limit: 500
                    },
                    data: [
                        { name: "NEW CORP SL", cif: "B99887766", founded: "2024-05-05", province: "BARCELONA", score: 88 }
                    ]
                },
                get_network: {
                    success: true,
                    data: {
                        cif: "B12345678",
                        administrators: [
                            {
                                name: "GARCIA LOPEZ JUAN",
                                position: "Administrador Único",
                                linked_companies: [
                                    { name: "OTRA EMPRESA SL", cif: "B87654321", status: "ACTIVA" }
                                ]
                            }
                        ]
                    }
                },
                get_match: {
                    success: true,
                    data: {
                        cif: "B12345678",
                        seller_sector: "software",
                        match_score: 85,
                        analysis: {
                            match_level: "Alto",
                            synergy: "Alta sinergia",
                            buyer_needs: ["Digitalización", "CRM"]
                        },
                        sales_pitch: "He visto que están creciendo. Nuestro software puede ayudarles a..."
                    }
                },
                post_webhook: {
                    success: true,
                    message: "Webhook creado correctamente",
                    id: 789
                },
                get_webhooks: {
                    success: true,
                    data: [
                        { id: "789", url: "https://tucrm.com/api/callback", event: "company.created" }
                    ]
                },
                delete_webhook: {
                    success: true,
                    message: "Webhook eliminado"
                }
            };

            function showJsonPreview(key) {
                const modal = document.getElementById('json-modal');
                const content = document.getElementById('modal-json-content');
                const endpoint = document.getElementById('modal-endpoint-name');
                
                const names = {
                    get_companies: 'GET /companies',
                    get_search: 'GET /companies/search',
                    post_batch: 'POST /companies/batch',
                    get_score: 'GET /companies/score',
                    get_signals: 'GET /companies/signals',
                    get_borme: 'GET /companies/borme',
                    get_insights: 'GET /companies/insights',
                    get_radar: 'GET /companies/radar',
                    get_network: 'GET /companies/network',
                    get_match: 'GET /companies/match',
                    post_webhook: 'POST /webhooks',
                    get_webhooks: 'GET /webhooks',
                    delete_webhook: 'DELETE /webhooks/{id}'
                };

                endpoint.textContent = names[key];
                content.innerHTML = syntaxHighlight(jsonExamples[key]);
                modal.style.display = 'flex';

                // Tracking Event
                window.dataLayer = window.dataLayer || [];
                window.dataLayer.push({
                    'event': 'view_json_preview',
                    'api_endpoint': names[key]
                });
                // Evitamos el salto quitando el hidden del body si causa conflicto
                // document.body.style.overflow = 'hidden'; 
            }

            function closeJsonModal() {
                document.getElementById('json-modal').style.display = 'none';
                // document.body.style.overflow = 'auto';
            }
            
            // Pricing Toggle
            let isAnnual = true;
            function togglePricing() {
                isAnnual = !isAnnual;
                const knob = document.getElementById('toggleKnob');
                const labelMonthly = document.getElementById('labelMonthly');
                const labelAnnual = document.getElementById('labelAnnual');
                const pricePro = document.getElementById('pricePro');
                const priceBusiness = document.getElementById('priceBusiness');

                if(isAnnual) {
                    knob.style.transform = 'translateX(24px)';
                    labelMonthly.style.color = '#94a3b8';
                    labelMonthly.style.fontWeight = '600';
                    labelAnnual.style.color = '#2563eb';
                    labelAnnual.style.fontWeight = '800';
                    
                    pricePro.textContent = pricePro.dataset.annual;
                    priceBusiness.textContent = priceBusiness.dataset.annual;
                } else {
                    knob.style.transform = 'translateX(0px)';
                    labelMonthly.style.color = '#2563eb';
                    labelMonthly.style.fontWeight = '800';
                    labelAnnual.style.color = '#94a3b8';
                    labelAnnual.style.fontWeight = '600';
                    
                    pricePro.textContent = pricePro.dataset.monthly;
                    priceBusiness.textContent = priceBusiness.dataset.monthly;
                }
            }

            function syntaxHighlight(json) {
                if (typeof json != 'string') {
                    json = JSON.stringify(json, undefined, 2);
                }
                json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g, function (match) {
                    var cls = 'color:#d97706;'; // number (Orange)
                    if (/^"/.test(match)) {
                        if (/:$/.test(match)) {
                            cls = 'color:#2563eb;'; // key (Blue)
                        } else {
                            cls = 'color:#16a34a;'; // string (Green)
                        }
                    } else if (/true|false/.test(match)) {
                        cls = 'color:#9333ea;'; // boolean (Purple)
                    } else if (/null/.test(match)) {
                        cls = 'color:#64748b;'; // null (Gray)
                    }
                    return '<span style="' + cls + ' font-weight: 500;">' + match + '</span>';
                });
            }
        </script>
</body>

</html>