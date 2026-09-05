<!doctype html>
<html lang="en">

<head>
    <?= view('partials/head', [
        'title'       => 'Spanish Company Data API | CIF Lookup, BORME & KYB | APIEmpresas.es',
        'excerptText' => 'REST API for Spanish company data. Look up companies by CIF/NIF, bulk B2B enrichment, BORME webhooks and automated KYB onboarding. <200ms · 99.9% SLA.',
        'canonical'   => site_url('spanish-company-data-api'),
        'robots'      => 'index,follow',
        'hreflang_es' => site_url('api-empresas'),
        'hreflang_en' => site_url('spanish-company-data-api'),
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
        .radar-hero { border-bottom: none !important; }

        @keyframes mesh-glow-3 {
            0%, 100% { opacity: 0.3; transform: scale(1) translate(0, 0); }
            50% { opacity: 0.6; transform: scale(1.1) translate(20px, -20px); }
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
        .api-code-keyword { color: #c678dd; }
        .api-code-string  { color: #98c379; }
        .api-code-attr    { color: #d19a66; }

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
            width: 48px; height: 48px;
            background: #eff6ff;
            color: #3b82f6;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .api-endpoint-row:hover { background: #f8fafc; }

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
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02), 0 2px 4px -1px rgba(0,0,0,0.01);
        }
        .api-pricing-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 40px 80px -20px rgba(0,0,0,0.08);
            border-color: #e2e8f0;
        }
        .api-pricing-card.featured {
            border: 2px solid #3b82f6;
            background: linear-gradient(180deg, #ffffff 0%, #f0f7ff 100%);
            transform: scale(1.05);
            z-index: 10;
        }
        .api-pricing-card.featured:hover { transform: scale(1.05) translateY(-10px); }
        .api-pricing-card.featured::before {
            content: "RECOMMENDED FOR SAAS/ERP";
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
            box-shadow: 0 10px 20px -5px rgba(216,154,18,0.4);
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
        .api-pricing-card.featured .api-pricing-card__header h3 { color: #ffffff; }
        .api-pricing-card.free-plan {
            background: linear-gradient(180deg, #5b6278 0%, #555c73 100%);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .api-pricing-card.free-plan h3,
        .api-pricing-card.free-plan .api-price-value,
        .api-pricing-card.free-plan .api-price-value span,
        .api-pricing-card.free-plan .api-pricing-card__desc,
        .api-pricing-card.free-plan .api-price-list li { color: #ffffff !important; }
        .api-pricing-card.free-plan .api-price-list { border-top-color: rgba(255,255,255,0.1); }
        .api-pricing-card.featured {
            background: linear-gradient(180deg, #4f46e5 0%, #4c44dc 100%) !important;
            border: 2px solid rgba(255,255,255,0.2);
        }
        .api-pricing-card.featured h3,
        .api-pricing-card.featured .api-price-value,
        .api-pricing-card.featured .api-price-value span,
        .api-pricing-card.featured .api-pricing-card__desc,
        .api-pricing-card.featured .api-price-list li { color: #ffffff !important; }
        .api-pricing-card.featured .api-pricing-btn.primary {
            background: #ffffff !important;
            color: #4338ca !important;
            box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important;
        }
        .api-pricing-card.featured .api-price-list { border-top-color: rgba(255,255,255,0.1); }
        .api-pricing-card.business-plan {
            background: linear-gradient(180deg, #5ea083 0%, #57997c 100%);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .api-pricing-card.business-plan h3,
        .api-pricing-card.business-plan .api-price-value,
        .api-pricing-card.business-plan .api-price-value span,
        .api-pricing-card.business-plan .api-pricing-card__desc,
        .api-pricing-card.business-plan .api-price-list li { color: #ffffff !important; }
        .api-pricing-card.business-plan .api-price-list { border-top-color: rgba(255,255,255,0.1); }
        .api-pricing-card.free-plan .api-pricing-btn {
            background: #2563eb !important;
            color: #ffffff !important;
            border: none !important;
            box-shadow: 0 10px 25px -5px rgba(37,99,235,0.4) !important;
        }
        .api-pricing-card.free-plan .api-pricing-btn:hover {
            background: #1d4ed8 !important;
            transform: translateY(-2px);
            box-shadow: 0 15px 30px -5px rgba(37,99,235,0.5) !important;
        }
        .api-pricing-card.business-plan .api-pricing-btn {
            background: #ffffff !important;
            color: #1f2937 !important;
            border: none !important;
        }
        .api-price-value span { color: rgba(255,255,255,0.7) !important; }
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
        .api-price-list li svg { color: #4ade80; flex-shrink: 0; filter: drop-shadow(0 0 5px rgba(74,222,128,0.35)); }
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
        .api-pricing-btn:hover { background: #f8fafc; border-color: #cbd5e1; color: #0f172a; }
        .api-pricing-btn.primary {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            border: none;
            color: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(37,99,235,0.3);
        }
        .api-pricing-btn.primary:hover {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            box-shadow: 0 20px 35px -10px rgba(37,99,235,0.4);
            transform: translateY(-2px);
        }

        @media (max-width: 900px) {
            .api-pricing-grid { grid-template-columns: 1fr; }
            .api-integration-grid { grid-template-columns: 1fr !important; }
        }

        .api-integration-grid {
            display: grid;
            grid-template-columns: 1fr 1.4fr;
            gap: 64px;
            align-items: center;
            margin-top: 0;
        }
        .api-integration-tabs { display: flex; gap: 8px; margin-bottom: 32px; flex-wrap: wrap; }
        .api-tab {
            padding: 8px 18px;
            border-radius: 99px;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.05em;
            cursor: pointer;
            border: 2px solid #e2e8f0;
            color: #94a3b8;
            background: #fff;
            transition: all 0.2s;
        }
        .api-tab.active { border-color: #2563eb; color: #fff; background: #2563eb; }

        .api-faq-item { border-bottom: 1px solid #f1f5f9; }
        .api-faq-question {
            width: 100%;
            background: none;
            border: none;
            padding: 24px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 1.05rem;
            font-weight: 700;
            color: #0f172a;
            cursor: pointer;
            text-align: left;
            gap: 16px;
        }
        .api-faq-icon { font-size: 1.5rem; color: #94a3b8; transition: all 0.3s; flex-shrink: 0; }
        .api-faq-answer { max-height: 0; overflow: hidden; transition: max-height 0.4s ease; }
        .api-faq-answer-inner { padding: 0 0 24px; color: #475569; line-height: 1.7; font-size: 1rem; }
        .api-faq-item.active .api-faq-answer { max-height: 300px; }
        .api-faq-item.active .api-faq-icon { transform: rotate(45deg); color: #3b82f6; }

        .technical-grid {
            mask-image: linear-gradient(to bottom, transparent, black 15%, black 85%, transparent);
            -webkit-mask-image: linear-gradient(to bottom, transparent, black 15%, black 85%, transparent);
        }

        /* Language switcher in hero */
        .lang-switcher {
            display: inline-flex;
            gap: 8px;
            margin-bottom: 1.5rem;
        }
        .lang-switcher a {
            padding: 5px 14px;
            border-radius: 99px;
            font-size: 0.78rem;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.2s;
        }
        .lang-switcher a.active {
            background: rgba(255,255,255,0.18);
            color: #fff;
            border: 1px solid rgba(255,255,255,0.35);
        }
        .lang-switcher a.inactive {
            background: rgba(255,255,255,0.06);
            color: rgba(255,255,255,0.55);
            border: 1px solid rgba(255,255,255,0.1);
        }
        .lang-switcher a.inactive:hover {
            background: rgba(255,255,255,0.12);
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
    </style>
</head>

<body>
    <?= view('partials/header_en') ?>

    <main class="radar-page">

        <!-- HERO SECTION -->
        <header class="api-unified-hero">
            <div class="container" style="max-width:1100px; margin:0 auto; padding:0 2rem;">



                <!-- Badge -->
                <div class="api-hero-badge">
                    <span class="api-hero-badge-dot"></span>
                    REST API · Data Infrastructure at Scale
                </div>

                <!-- H1 -->
                <h1 class="api-hero-title">
                    Spanish Company Data API<br>
                    <span>Mercantile Registry &amp; BORME</span>
                </h1>

                <!-- Subtitle -->
                <p class="api-hero-sub">
                    JSON endpoints for CIF/NIF lookup, bulk B2B enrichment,
                    BORME alert webhooks and automated KYB onboarding.
                    Latency &lt;200ms &middot; 99.9% SLA.
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
                    <span style="color:#94a3b8; font-size:0.92rem;">Used by <strong>+1,200</strong> developers and SaaS teams</span>
                </div>

                <!-- Badges -->
                <div class="api-hero-badges">
                    <span class="api-hero-chip api-hero-chip--green">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        Official BORME Data
                    </span>
                    <span class="api-hero-chip api-hero-chip--blue">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        REST API · &lt;200ms
                    </span>
                    <span class="api-hero-chip api-hero-chip--purple">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        KYB &amp; Compliance Ready
                    </span>
                </div>

                <!-- CTAs -->
                <div class="api-hero-actions">
                    <a href="<?= site_url('register') ?>" class="api-hero-btn-primary">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                        Try the API for free
                    </a>
                    <a href="<?= site_url('documentation/en') ?>" class="api-hero-btn-ghost">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        View documentation
                    </a>
                </div>

                <!-- Proof stats -->
                <div style="display:flex; gap:32px; justify-content:center; flex-wrap:wrap; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.07);">
                    <div style="text-align:center;">
                        <div style="font-size:1.6rem; font-weight:900; color:#fff; letter-spacing:-0.04em;">+3.5M</div>
                        <div style="font-size:0.78rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px;">Companies indexed</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:1.6rem; font-weight:900; color:#fff; letter-spacing:-0.04em;">&lt;200ms</div>
                        <div style="font-size:0.78rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px;">Average latency</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:1.6rem; font-weight:900; color:#fff; letter-spacing:-0.04em;">99.9%</div>
                        <div style="font-size:0.78rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px;">Guaranteed uptime</div>
                    </div>
                    <div style="text-align:center;">
                        <div style="font-size:1.6rem; font-weight:900; color:#fff; letter-spacing:-0.04em;">Daily</div>
                        <div style="font-size:0.78rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px;">BORME sync</div>
                    </div>
                </div>

            </div>
        </header>


        <!-- HOW IT WORKS -->
        <section class="radar-section" style="padding: 120px 0; background: #fbfcfe;">
            <div class="container">
                <div style="display: grid; grid-template-columns: 1.1fr 0.9fr; gap: 80px; align-items: center;">
                    <div>
                        <div class="radar-kicker">Frictionless integration</div>
                        <h2 class="radar-title"
                            style="margin-top: 12px; margin-bottom: 32px; font-size: 2.75rem; letter-spacing: -0.03em;">
                            Architecture built for mass automation</h2>

                        <p class="radar-text"
                            style="font-size: 1.2rem; line-height: 1.7; color: #334155; margin-bottom: 40px; max-width: 600px;">
                            Our infrastructure abstracts the complexity of the Spanish Mercantile Registry, offering a low-latency REST interface designed for mission-critical systems.
                        </p>

                        <div style="display: grid; gap: 32px; margin-bottom: 48px;">
                            <div style="display: flex; gap: 20px; align-items: flex-start;">
                                <div style="background: #2563eb; color: #fff; width: 36px; height: 36px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; flex-shrink: 0; box-shadow: 0 10px 15px -3px rgba(37,99,235,0.25);">1</div>
                                <div>
                                    <h4 style="margin: 0 0 4px; font-weight: 800; color: #0f172a;">Bulk Lookup</h4>
                                    <p style="margin: 0; font-size: 1rem; color: #64748b; line-height: 1.5;">Requests by <strong>CIF or NIF</strong> with structured JSON payloads.</p>
                                </div>
                            </div>
                            <div style="display: flex; gap: 20px; align-items: flex-start;">
                                <div style="background: #2563eb; color: #fff; width: 36px; height: 36px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 16px; flex-shrink: 0; box-shadow: 0 10px 15px -3px rgba(37,99,235,0.25);">2</div>
                                <div>
                                    <h4 style="margin: 0 0 4px; font-weight: 800; color: #0f172a;">Real-Time Response</h4>
                                    <p style="margin: 0; font-size: 1rem; color: #64748b; line-height: 1.5;">Official data, CNAE code and commercial status in under 200ms.</p>
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; flex-wrap: wrap; gap: 24px; align-items: center;">
                            <a href="<?= site_url() ?>" class="radar-btn radar-btn--ghost"
                                style="border: 2px solid #e2e8f0; padding: 14px 32px; border-radius: 16px; font-size: 1rem;">
                                Look up a CIF online
                            </a>
                            <a href="<?= site_url('documentation/en') ?>"
                                style="font-weight: 800; color: #2563eb; text-decoration: none; display: flex; align-items: center; gap: 8px; font-size: 1.05rem;">
                                Technical integration guide →
                            </a>
                        </div>
                    </div>

                    <div style="position: relative;">
                        <div style="position: absolute; inset: -40px; background: radial-gradient(circle, rgba(37,99,235,0.08) 0%, transparent 70%); pointer-events: none;"></div>
                        <div style="background: #0f172a; border-radius: 24px; padding: 32px; box-shadow: 0 50px 100px -20px rgba(15,23,42,0.4); border: 1px solid rgba(255,255,255,0.05); position: relative; z-index: 2;">
                            <div style="display: flex; gap: 8px; margin-bottom: 24px;">
                                <span style="width: 10px; height: 10px; background: #ff5f57; border-radius: 50%;"></span>
                                <span style="width: 10px; height: 10px; background: #febc2e; border-radius: 50%;"></span>
                                <span style="width: 10px; height: 10px; background: #28c840; border-radius: 50%;"></span>
                            </div>
                            <pre style="margin: 0; font-family: 'Fira Code', monospace; font-size: 14px; line-height: 1.6;">
<span style="color: #c678dd;">GET</span> <span style="color: #98c379;">/v1/companies?cif=B12345678</span>
<span style="color: #abb2bf;">{</span>
  <span style="color: #d19a66;">"success"</span>: <span style="color: #d19a66;">true</span>,
  <span style="color: #d19a66;">"data"</span>: <span style="color: #abb2bf;">{</span>
    <span style="color: #d19a66;">"name"</span>: <span style="color: #98c379;">"TECH FLOW SOLUTIONS SL"</span>,
    <span style="color: #d19a66;">"cif"</span>: <span style="color: #98c379;">"B12345678"</span>,
    <span style="color: #d19a66;">"status"</span>: <span style="color: #98c379;">"ACTIVA"</span>,
    <span style="color: #d19a66;">"founded"</span>: <span style="color: #98c379;">"2024-03-12"</span>,
    <span style="color: #d19a66;">"cnae"</span>: <span style="color: #98c379;">"6201"</span>,
    <span style="color: #d19a66;">"municipality"</span>: <span style="color: #98c379;">"MADRID"</span>,
    <span style="color: #d19a66;">"score"</span>: <span style="color: #d19a66;">94</span>
  <span style="color: #abb2bf;">}</span>
<span style="color: #abb2bf;">}</span></pre>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- B2B ENRICHMENT -->
        <section class="radar-section">
            <div class="container">
                <div class="radar-split">
                    <div class="radar-split__content">
                        <div class="radar-kicker">Power for your stack</div>
                        <h2 class="radar-title">B2B Enrichment Capabilities</h2>
                        <p class="radar-text">Integrate commercial intelligence into your workflow and eliminate silos of outdated information in your database.</p>
                        <ul style="display: grid; gap: 12px; margin-top: 24px;">
                            <li style="display: flex; align-items: center; gap: 12px; font-weight: 700; color: #475569;">
                                <span style="color: #3b82f6;">✓</span> KYB onboarding in seconds
                            </li>
                            <li style="display: flex; align-items: center; gap: 12px; font-weight: 700; color: #475569;">
                                <span style="color: #3b82f6;">✓</span> Bulk database synchronisation
                            </li>
                            <li style="display: flex; align-items: center; gap: 12px; font-weight: 700; color: #475569;">
                                <span style="color: #3b82f6;">✓</span> CRM enrichment (Salesforce, HubSpot, etc.)
                            </li>
                            <li style="display: flex; align-items: center; gap: 12px; font-weight: 700; color: #475569;">
                                <span style="color: #3b82f6;">✓</span> Insolvency monitoring and BORME change alerts
                            </li>
                            <li style="display: flex; align-items: center; gap: 12px; font-weight: 700; color: #475569;">
                                <span style="color: #3b82f6;">✓</span> AI-powered commercial health scoring
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
                            <span style="color: #98c379;">$</span> curl -X GET <span class="api-code-string">"https://spaincompanyapi.com/api/v1/companies?cif=B12345678"</span>
                            \<br>
                            &nbsp;&nbsp;-H <span class="api-code-string">"X-API-KEY: your_key"</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- USE CASES -->
        <section class="radar-section radar-section--soft">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">B2B Solutions</div>
                    <h2 class="radar-title">Use cases for the Spanish Company API</h2>
                </div>
                <div class="radar-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-top: 40px;">
                    <div style="background: white; padding: 32px; border-radius: 20px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 16px;">🛡️</div>
                        <h4 style="margin: 0; font-weight: 800;">Compliance &amp; Automated KYB</h4>
                    </div>
                    <div style="background: white; padding: 32px; border-radius: 20px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 16px;">📈</div>
                        <h4 style="margin: 0; font-weight: 800;">B2B Lead Enrichment</h4>
                    </div>
                    <div style="background: white; padding: 32px; border-radius: 20px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 16px;">🔄</div>
                        <h4 style="margin: 0; font-weight: 800;">CRM / ERP Synchronisation</h4>
                    </div>
                    <div style="background: white; padding: 32px; border-radius: 20px; border: 1px solid #e2e8f0; text-align: center;">
                        <div style="font-size: 2rem; margin-bottom: 16px;">💳</div>
                        <h4 style="margin: 0; font-weight: 800;">Risk Analysis &amp; Lending</h4>
                    </div>
                    <div style="background: white; padding: 32px; border-radius: 20px; border: 1px solid #e2e8f0; text-align: center;">
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
                    <div class="radar-kicker">Why choose us</div>
                    <h2 class="radar-title">Differentiate your product with intelligent data</h2>
                    <p class="radar-subtitle">
                        We don't deliver static data. We deliver actionable intelligence ready to integrate into your processes.
                    </p>
                </div>
                <div class="radar-grid"
                    style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px;">
                    <div class="api-feature-card">
                        <div class="api-feature-icon">🛡️</div>
                        <h3>Bulk Data Ingestion</h3>
                        <p class="radar-text">Built for high-throughput pipelines. Our architecture handles thousands of concurrent requests without latency degradation.</p>
                    </div>
                    <div class="api-feature-card">
                        <div class="api-feature-icon">🎯</div>
                        <h3>Predictive AI Scoring</h3>
                        <p class="radar-text">Transform raw data into actionable intelligence. Prioritise your commercial efforts based on each company's solvency and potential.</p>
                    </div>
                    <div class="api-feature-card">
                        <div class="api-feature-icon">⚡</div>
                        <h3>Push Notifications</h3>
                        <p class="radar-text">Receive real-time webhook payloads. Keep your database in sync with the BORME without polling processes.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ENDPOINTS TABLE -->
        <section class="radar-section radar-section--soft">
            <div class="container">
                <div class="radar-heading">
                    <div class="radar-kicker">Quick reference</div>
                    <h2 class="radar-title">API Capabilities</h2>
                    <p class="radar-subtitle" style="text-align: left; max-width: 800px; margin-left: 0;">
                        Query Spanish companies via REST API endpoints. Access tax data, activity, status and other official Mercantile Registry information in real time.
                    </p>
                </div>

                <div style="background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: var(--shadow-soft);">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 20px; text-align: left; font-weight: 800; font-size: 13px; color: #64748b; text-transform: uppercase;">Endpoint</th>
                                <th style="padding: 20px; text-align: left; font-weight: 800; font-size: 13px; color: #64748b; text-transform: uppercase;">Business Use &amp; Integration</th>
                                <th style="padding: 20px; text-align: center; font-weight: 800; font-size: 13px; color: #64748b; text-transform: uppercase;">Plan</th>
                                <th style="padding: 20px; text-align: center; font-weight: 800; font-size: 13px; color: #64748b; text-transform: uppercase;">Payload</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">GET /companies</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parameter: ?cif=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Legal Data Enrichment</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">Get the full legal profile: <code style="color: #2563eb;">name</code>, <code style="color: #2563eb;">founded</code>, <code style="color: #2563eb;">address</code>, <code style="color: #2563eb;">cnae</code>, <code style="color: #2563eb;">status</code> and registry links.</p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">FREE</span></td>
                                <td style="padding: 18px 20px; text-align: center;"><button type="button" onclick="event.preventDefault(); showJsonPreview('get_companies')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">GET /companies/search</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parameter: ?q=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Normalisation &amp; Search</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">Locate companies by trade name or legal name. Includes phonetic suggestions and high-precision partial matches.</p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">FREE</span></td>
                                <td style="padding: 18px 20px; text-align: center;"><button type="button" onclick="event.preventDefault(); showJsonPreview('get_search')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">POST /companies/batch</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">JSON Array: cifs[]</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Batch Lookup</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">Query up to 100 CIFs in a single request, saving network round-trips. Cost is dynamically calculated per company found.</p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">PRO / BUS</span></td>
                                <td style="padding: 18px 20px; text-align: center;"><button type="button" onclick="event.preventDefault(); showJsonPreview('post_batch')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">GET /companies/score</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parameter: ?cif=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">AI Commercial Scoring</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">Purchase propensity algorithm. Returns <code style="color: #2563eb;">score</code> (0–100), <code style="color: #2563eb;">priority</code> and a descriptive commercial potential message.</p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">PRO / BUS</span></td>
                                <td style="padding: 18px 20px; text-align: center;"><button type="button" onclick="event.preventDefault(); showJsonPreview('get_score')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">GET /companies/signals</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parameter: ?cif=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Corporate Signals (BORME)</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">Monitor capital changes, insolvency acts, board appointments and corporate renewals in real time.</p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">PRO / BUS</span></td>
                                <td style="padding: 18px 20px; text-align: center;"><button type="button" onclick="event.preventDefault(); showJsonPreview('get_signals')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">GET /companies/borme</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parameter: ?cif=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">BORME Act History</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">Get the full chronological history of Mercantile Registry publications, useful for KYC auditing and Due Diligence.</p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">PRO / BUS</span></td>
                                <td style="padding: 18px 20px; text-align: center;"><button type="button" onclick="event.preventDefault(); showJsonPreview('get_borme')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">GET /companies/insights</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parameter: ?cif=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Predictive Business Insights</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">Advanced needs analysis. Returns <code style="color: #2563eb;">profile</code> (AI niche summary) and <code style="color: #2563eb;">prob</code> (acquisition success probability index).</p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="background: #f5f3ff; color: #8b5cf6; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">AI PREMIUM</span></td>
                                <td style="padding: 18px 20px; text-align: center;"><button type="button" onclick="event.preventDefault(); showJsonPreview('get_insights')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">GET /companies/radar</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Filters: province, sector...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">New Incorporations Endpoint</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">Bulk extraction of newly incorporated companies. Filter by geolocation or economic activity (CNAE) to feed your cold sales pipeline.</p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">PRO / BUS</span></td>
                                <td style="padding: 18px 20px; text-align: center;"><button type="button" onclick="event.preventDefault(); showJsonPreview('get_radar')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">GET /companies/network</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parameter: ?cif=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Corporate Power Graphs</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">Get the network of corporate links between companies through their directors.</p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">PRO / BUS</span></td>
                                <td style="padding: 18px 20px; text-align: center;"><button type="button" onclick="event.preventDefault(); showJsonPreview('get_network')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">GET /companies/match</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Filters: cif, seller_sector</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">B2B Match Calculator</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">Evaluates the commercial fit between a prospect company and a sales sector, returning a score and a sales argument.</p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="background: #fdf2f8; color: #db2777; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">BUSINESS</span></td>
                                <td style="padding: 18px 20px; text-align: center;"><button type="button" onclick="event.preventDefault(); showJsonPreview('get_match')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">GET /companies/contracts</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Filters: cif, page, limit</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Public Contracts & Awards</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">Historical government tenders, contracting authorities, and award amounts associated with a company.</p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="background: #fdf2f8; color: #db2777; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">BUSINESS</span></td>
                                <td style="padding: 18px 20px; text-align: center;"><button type="button" onclick="event.preventDefault(); showJsonPreview('get_contracts')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button></td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">GET /companies/risk-profile</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Filters: cif</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Corporate Risk & Solvency Profile</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">Algorithmic risk score, accounts filing compliance, governance volatility, and official distress alerts.</p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="background: #fdf2f8; color: #db2777; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">BUSINESS</span></td>
                                <td style="padding: 18px 20px; text-align: center;"><button type="button" onclick="event.preventDefault(); showJsonPreview('get_risk_profile')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button></td>
                            </tr>
                            <tr class="api-endpoint-row">
                                <td style="padding: 18px 20px;">
                                    <div style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">POST /webhooks</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Body: {url, event}</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">PUSH Sync (BORME)</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">Register your callback URL to receive real-time HTTP notifications when an event of interest occurs (e.g. new company in your area).</p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span style="background: #fdf2f8; color: #db2777; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">BUSINESS</span></td>
                                <td style="padding: 18px 20px; text-align: center;"><button type="button" onclick="event.preventDefault(); showJsonPreview('post_webhook')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- PRICING -->
        <section id="plans" class="radar-section">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">Transparent pricing</div>
                    <h2 class="radar-title">Plans designed to grow with you</h2>
                    <p class="radar-subtitle">
                        No integration costs. Start free and add the power of official data to your stack in minutes.
                    </p>
                </div>

                <!-- Annual / Monthly toggle -->
                <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 64px; margin-top: 16px; gap: 12px;">
                    <span style="font-size: 0.95rem; font-weight: 600; color: #94a3b8; transition: all 0.3s;" id="labelMonthly">Monthly</span>
                    <button type="button" id="billingToggle" style="width: 56px; height: 32px; background: #0f172a; border-radius: 99px; position: relative; cursor: pointer; border: none; padding: 4px; transition: background 0.3s;" onclick="togglePricing()">
                        <div id="toggleKnob" style="width: 24px; height: 24px; background: white; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1); transform: translateX(24px);"></div>
                    </button>
                    <span style="font-size: 0.95rem; font-weight: 800; color: #2563eb; display: flex; align-items: center; gap: 8px; transition: all 0.3s;" id="labelAnnual">Annual <span style="background: #dcfce7; color: #166534; font-size: 10px; padding: 4px 8px; border-radius: 99px; letter-spacing: 0.05em; font-weight: 800;">SAVE 20%</span></span>
                </div>

                <div class="api-pricing-grid">
                    <!-- FREE -->
                    <div class="api-pricing-card free-plan">
                        <div class="api-pricing-card__header"><h3>Free</h3></div>
                        <div class="api-price-value">€0<span>/ one-time</span></div>
                        <p class="api-pricing-card__desc">For development environments, technical sandboxing and JSON schema validation.</p>
                        <ul class="api-price-list">
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" /></svg> <?= $freeLimit ?> guaranteed queries</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" /></svg> Official basic data</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" /></svg> Access to /companies</li>
                        </ul>
                        <div class="api-price-cta" style="margin-top: 32px;">
                            <form id="api_quick_unlock_form" style="display: flex; flex-direction: column; gap: 12px;">
                                <input type="email" name="email" placeholder="Your corporate email" required
                                       style="padding: 14px 20px; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 1rem; width: 100%; outline: none; transition: border-color 0.2s;"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                                <button type="submit" class="api-pricing-btn" style="width: 100%; justify-content: center; background: #0f172a; color: white; border: none; cursor: pointer;">
                                    Get Free API Key
                                </button>
                            </form>
                            <p style="font-size: 0.75rem; color: rgba(255,255,255,0.7); margin-top: 12px; text-align: center;">
                                Instant access. No credit card required.
                            </p>
                        </div>
                    </div>

                    <!-- PRO -->
                    <div class="api-pricing-card featured">
                        <div class="api-pricing-card__header"><h3>Pro</h3></div>
                        <div class="api-price-value"><b id="pricePro" data-monthly="19" data-annual="15" style="font-weight: inherit;">15</b>€<span>/ month</span></div>
                        <p class="api-pricing-card__desc">Full integration for B2B onboarding, lead enrichment and scoring workflows.</p>
                        <ul class="api-price-list">
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" /></svg> 3,000 queries / month</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" /></svg> Full BORME Act History</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" /></svg> AI Scoring Included</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" /></svg> New Incorporations Feed</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" /></svg> Corporate Network Graphs</li>
                        </ul>
                        <a href="<?= site_url('register') ?>" class="api-pricing-btn primary">Activate Pro Plan</a>
                    </div>

                    <!-- BUSINESS -->
                    <div class="api-pricing-card business-plan">
                        <div class="api-pricing-card__header"><h3>Business</h3></div>
                        <div class="api-price-value"><b id="priceBusiness" data-monthly="49" data-annual="39" style="font-weight: inherit;">39</b>€<span>/ month</span></div>
                        <p class="api-pricing-card__desc">Real-time synchronisation via Webhooks and bulk volume for mission-critical platforms.</p>
                        <ul class="api-price-list">
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" /></svg> 10,000 queries / month</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" /></svg> PUSH Webhooks</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" /></svg> AI Insights &amp; Predictive Business</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" /></svg> B2B Match Calculator</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" /></svg> Priority Slack Support</li>
                        </ul>
                        <a href="<?= site_url('register') ?>" class="api-pricing-btn">Activate Business</a>
                    </div>
                </div>

            </div>

            <div style="margin-top: 60px; background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 50%, #f0fdf4 100%); padding: 48px 32px; border-radius: 24px; text-align: center; position: relative; overflow: hidden; border: 1px solid rgba(59,130,246,0.15); box-shadow: 0 20px 40px -15px rgba(37,99,235,0.1); max-width: 900px; margin-left: auto; margin-right: auto;">
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.4; background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px; pointer-events: none;"></div>
                <div style="position: absolute; top: -30%; left: -10%; width: 50%; height: 160%; background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 60%); pointer-events: none;"></div>
                <div style="position: absolute; bottom: -30%; right: -10%; width: 50%; height: 160%; background: radial-gradient(circle, rgba(16,185,129,0.08) 0%, transparent 60%); pointer-events: none;"></div>
                <div style="position: relative; z-index: 1;">
                    <div style="display: inline-block; background: #ffffff; color: #2563eb; font-size: 0.8rem; font-weight: 800; padding: 6px 16px; border-radius: 99px; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 16px; box-shadow: 0 4px 6px -1px rgba(37,99,235,0.1); border: 1px solid rgba(59,130,246,0.1);">Pay-as-you-go</div>
                    <h3 style="color: #0f172a; font-size: 2.1rem; font-weight: 900; margin: 0 0 12px; letter-spacing: -0.03em;">Prefer to pay only for what you use?</h3>
                    <p style="color: #475569; font-size: 1.15rem; max-width: 600px; margin: 0 auto 32px; line-height: 1.6;">Design your own <strong style="color: #0f172a;">Prepaid Credit Bundle</strong>. Pay once, consume at your pace and get automatic volume discounts.</p>
                    <a href="<?= site_url('buy-api-credits') ?>" style="display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: #fff; padding: 18px 40px; border-radius: 16px; font-weight: 800; font-size: 1.1rem; text-decoration: none; box-shadow: 0 10px 25px rgba(37,99,235,0.4); transition: all 0.3s ease;">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="21" y1="4" x2="14" y2="4"></line><line x1="10" y1="4" x2="3" y2="4"></line><line x1="21" y1="12" x2="12" y2="12"></line><line x1="8" y1="12" x2="3" y2="12"></line><line x1="21" y1="20" x2="16" y2="20"></line><line x1="12" y1="20" x2="3" y2="20"></line><line x1="14" y1="1" x2="14" y2="7"></line><line x1="8" y1="9" x2="8" y2="15"></line><line x1="16" y1="17" x2="16" y2="23"></line></svg>
                        Build my Custom Bundle
                    </a>
                </div>
            </div>

            <div style="margin-top: 24px; text-align: center; padding: 16px;">
                <p style="color: #64748b; font-weight: 700; margin: 0; font-size: 0.95rem;">Need Enterprise support or custom annual billing? <a href="<?= site_url('contact') ?>" style="color: #3b82f6; text-decoration: none;">Let's talk about your project →</a></p>
            </div>
        </section>

        <!-- CODE INTEGRATION -->
        <section class="radar-section radar-section--soft">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">Developer First</div>
                    <h2 class="radar-title">Developer-oriented implementation</h2>
                    <p class="radar-subtitle">OpenAPI documentation, API Key authentication and SDKs for immediate production activation.</p>
                </div>

                <div style="display: flex; justify-content: center; gap: 8px; margin-bottom: 24px; margin-top: 40px;">
                    <button onclick="switchTab('python')" id="tab-python" style="padding: 10px 22px; border-radius: 99px; font-size: 12px; font-weight: 800; letter-spacing: 0.05em; cursor: pointer; border: 2px solid #2563eb; color: #fff; background: #2563eb; transition: all 0.2s;">PYTHON</button>
                    <button onclick="switchTab('php')" id="tab-php" style="padding: 10px 22px; border-radius: 99px; font-size: 12px; font-weight: 800; letter-spacing: 0.05em; cursor: pointer; border: 2px solid #e2e8f0; color: #94a3b8; background: #fff; transition: all 0.2s;">PHP / LARAVEL</button>
                    <button onclick="switchTab('node')" id="tab-node" style="padding: 10px 22px; border-radius: 99px; font-size: 12px; font-weight: 800; letter-spacing: 0.05em; cursor: pointer; border: 2px solid #e2e8f0; color: #94a3b8; background: #fff; transition: all 0.2s;">NODE.JS</button>
                </div>

                <div style="background: #0f172a; border-radius: 20px; overflow: hidden; box-shadow: 0 40px 80px -20px rgba(15,23,42,0.3); max-width: 760px; margin: 0 auto;">
                    <div style="background: #1e293b; padding: 14px 20px; display: flex; align-items: center; gap: 8px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                        <span style="width: 10px; height: 10px; background: #ff5f57; border-radius: 50%;"></span>
                        <span style="width: 10px; height: 10px; background: #febc2e; border-radius: 50%;"></span>
                        <span style="width: 10px; height: 10px; background: #28c840; border-radius: 50%;"></span>
                        <span id="tab-filename" style="margin-left: 8px; font-family: monospace; font-size: 12px; color: #64748b;">example.py</span>
                    </div>
                    <pre id="code-python" style="margin: 0; padding: 28px; font-family: 'Fira Code', monospace; font-size: 13px; line-height: 1.7; color: #e2e8f0; overflow-x: auto;"><code><span style="color:#c678dd;">import</span> <span style="color:#e2e8f0;">requests</span>

<span style="color:#d19a66;">API_KEY</span> <span style="color:#e2e8f0;">=</span> <span style="color:#98c379;">"your_api_key"</span>
<span style="color:#d19a66;">CIF</span>     <span style="color:#e2e8f0;">=</span> <span style="color:#98c379;">"B12345678"</span>

<span style="color:#d19a66;">response</span> <span style="color:#e2e8f0;">= requests.get(</span>
    <span style="color:#98c379;">"https://spaincompanyapi.com/api/v1/companies"</span><span style="color:#e2e8f0;">,</span>
    <span style="color:#d19a66;">params</span><span style="color:#e2e8f0;">={"cif": CIF},</span>
    <span style="color:#d19a66;">headers</span><span style="color:#e2e8f0;">={"X-API-KEY": API_KEY}</span>
<span style="color:#e2e8f0;">)</span>

<span style="color:#d19a66;">company</span> <span style="color:#e2e8f0;">= response.json()[</span><span style="color:#98c379;">"data"</span><span style="color:#e2e8f0;">]</span>
<span style="color:#c678dd;">print</span><span style="color:#e2e8f0;">(company[</span><span style="color:#98c379;">"name"</span><span style="color:#e2e8f0;">], company[</span><span style="color:#98c379;">"status"</span><span style="color:#e2e8f0;">])</span></code></pre>
                    <pre id="code-php" style="display:none; margin: 0; padding: 28px; font-family: 'Fira Code', monospace; font-size: 13px; line-height: 1.7; color: #e2e8f0; overflow-x: auto;"><code><span style="color:#c678dd;">use</span> Illuminate\Support\Facades\Http;

<span style="color:#d19a66;">$response</span> = Http::withHeaders([
    <span style="color:#98c379;">'X-API-KEY'</span> => <span style="color:#98c379;">'your_api_key'</span>
])->get(<span style="color:#98c379;">'https://spaincompanyapi.com/api/v1/companies'</span>, [
    <span style="color:#98c379;">'cif'</span> => <span style="color:#98c379;">'B12345678'</span>
]);

<span style="color:#d19a66;">$company</span> = <span style="color:#d19a66;">$response</span>->json(<span style="color:#98c379;">'data'</span>);
<span style="color:#c678dd;">echo</span> <span style="color:#d19a66;">$company</span>[<span style="color:#98c379;">'name'</span>];</code></pre>
                    <pre id="code-node" style="display:none; margin: 0; padding: 28px; font-family: 'Fira Code', monospace; font-size: 13px; line-height: 1.7; color: #e2e8f0; overflow-x: auto;"><code><span style="color:#c678dd;">const</span> <span style="color:#d19a66;">getCompany</span> = <span style="color:#c678dd;">async</span> (<span style="color:#e2e8f0;">cif</span>) => {
  <span style="color:#c678dd;">const</span> <span style="color:#d19a66;">res</span> = <span style="color:#c678dd;">await</span> fetch(
    <span style="color:#98c379;">`https://spaincompanyapi.com/api/v1/companies?cif=${cif}`</span>,
    { headers: { <span style="color:#98c379;">'X-API-KEY'</span>: <span style="color:#98c379;">'your_api_key'</span> } }
  );
  <span style="color:#c678dd;">const</span> { data } = <span style="color:#c678dd;">await</span> res.json();
  console.log(data.name, data.status);
};

getCompany(<span style="color:#98c379;">'B12345678'</span>);</code></pre>
                </div>

                <div style="display: flex; justify-content: center; gap: 32px; margin-top: 48px; flex-wrap: wrap;">
                    <a href="<?= site_url('documentation/en') ?>" style="display: inline-flex; align-items: center; gap: 8px; color: #2563eb; font-weight: 800; text-decoration: none; font-size: 1rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
                        Full API documentation →
                    </a>
                    <a href="<?= site_url('api/docs') ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; color: #64748b; font-weight: 700; text-decoration: none; font-size: 1rem;">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                        Swagger UI (Interactive) →
                    </a>
                </div>
            </div>
        </section>

        <!-- FAQ -->
        <section class="radar-section">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">Support</div>
                    <h2 class="radar-title">Technical Support &amp; Integration</h2>
                </div>
                <div style="max-width: 800px; margin: 48px auto 0;">
                    <div class="api-faq-item">
                        <button class="api-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>How do I integrate the Spanish company API?</span>
                            <span class="api-faq-icon">+</span>
                        </button>
                        <div class="api-faq-answer">
                            <div class="api-faq-answer-inner">Integration is done via a standard REST API using an API Key. Examples are available in Python, PHP and Node.js. You'll be making your first query in under 5 minutes.</div>
                        </div>
                    </div>
                    <div class="api-faq-item">
                        <button class="api-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>How often is BORME data updated?</span>
                            <span class="api-faq-icon">+</span>
                        </button>
                        <div class="api-faq-answer">
                            <div class="api-faq-answer-inner">We monitor the BORME daily, processing new registrations and corporate changes within a few hours of their official publication. Data is always fresh and up-to-date.</div>
                        </div>
                    </div>
                    <div class="api-faq-item">
                        <button class="api-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>Can I receive automatic BORME alerts via Webhooks?</span>
                            <span class="api-faq-icon">+</span>
                        </button>
                        <div class="api-faq-answer">
                            <div class="api-faq-answer-inner">Yes, the Business plan allows you to configure Webhooks. You can register a callback URL to receive PUSH notifications whenever we detect a new company matching your sector or province filters, eliminating constant polling.</div>
                        </div>
                    </div>
                    <div class="api-faq-item">
                        <button class="api-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>What JSON schema does the /companies endpoint return?</span>
                            <span class="api-faq-icon">+</span>
                        </button>
                        <div class="api-faq-answer">
                            <div class="api-faq-answer-inner">We return a structured JSON with: Legal data (CIF, Company Name), CNAE industry code, Registered Capital, Location (Address, Province), Status (Active/Dissolved), Board of Directors and AI-based commercial propensity scoring.</div>
                        </div>
                    </div>
                    <div class="api-faq-item">
                        <button class="api-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span>What is the rate limit per second?</span>
                            <span class="api-faq-icon">+</span>
                        </button>
                        <div class="api-faq-answer">
                            <div class="api-faq-answer-inner">Our infrastructure is built to scale. By default we allow bursts of up to 10 requests per second on standard plans, but we can enable custom quotas for bulk data ingestion on Enterprise plans.</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- FINAL CTA -->
        <section style="padding: 80px 0 100px;">
            <div class="container">
                <div style="background: #0f172a; border-radius: 32px; padding: 80px 60px; text-align: center; position: relative; overflow: hidden; box-shadow: 0 40px 80px -20px rgba(15,23,42,0.5);">
                    <div style="position: absolute; inset: 0; background: linear-gradient(135deg, #1e3a8a 0%, #1d4ed8 40%, #0369a1 100%); z-index: 0;"></div>
                    <div class="technical-grid" style="position: absolute; inset: 0; background-image: linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px); background-size: 30px 30px; opacity: 0.4; z-index: 1;"></div>
                    <div style="position: absolute; top:-15%; right:-15%; width:70%; height:70%; background: radial-gradient(circle, rgba(99,179,237,0.7) 0%, transparent 70%); border-radius:50%; filter:blur(60px); pointer-events:none; z-index:2;"></div>
                    <div style="position: absolute; bottom:-15%; left:-15%; width:80%; height:80%; background: radial-gradient(circle, rgba(16,185,129,0.5) 0%, transparent 70%); border-radius:50%; filter:blur(70px); pointer-events:none; z-index:2;"></div>
                    <div style="position: relative; z-index: 10;">
                        <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(255,255,255,0.12); border: 1px solid rgba(255,255,255,0.2); border-radius: 99px; padding: 6px 16px; margin-bottom: 28px;">
                            <span style="width: 8px; height: 8px; background: #34d399; border-radius: 50%; box-shadow: 0 0 8px #34d399;"></span>
                            <span style="color: rgba(255,255,255,0.9); font-size: 11px; font-weight: 800; letter-spacing: 0.08em;">SYSTEM READY</span>
                        </div>
                        <h2 style="color: #ffffff; font-size: 2.75rem; font-weight: 950; margin: 0 0 16px; letter-spacing: -0.04em; line-height: 1.1;">
                            Start using the Spanish Company API today</h2>
                        <p style="color: rgba(255,255,255,0.65); font-size: 1.1rem; font-weight: 500; margin: 0 0 48px; max-width: 480px; margin-left: auto; margin-right: auto; line-height: 1.6;">
                            Join the companies already automating their KYB validation and reaching new clients before anyone else.</p>
                        <div style="display: flex; justify-content: center; gap: 48px; margin-bottom: 52px; flex-wrap: wrap;">
                            <div>
                                <div style="color: #fff; font-size: 1.75rem; font-weight: 950; letter-spacing: -0.04em;">+800</div>
                                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; font-weight: 700; letter-spacing: 0.05em; margin-top: 4px;">COMPANIES INTEGRATED</div>
                            </div>
                            <div style="width: 1px; background: rgba(255,255,255,0.1);"></div>
                            <div>
                                <div style="color: #fff; font-size: 1.75rem; font-weight: 950; letter-spacing: -0.04em;">&lt; 200ms</div>
                                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; font-weight: 700; letter-spacing: 0.05em; margin-top: 4px;">AVERAGE LATENCY</div>
                            </div>
                            <div style="width: 1px; background: rgba(255,255,255,0.1);"></div>
                            <div>
                                <div style="color: #fff; font-size: 1.75rem; font-weight: 950; letter-spacing: -0.04em;">99.9%</div>
                                <div style="color: rgba(255,255,255,0.5); font-size: 0.8rem; font-weight: 700; letter-spacing: 0.05em; margin-top: 4px;">UPTIME SLA</div>
                            </div>
                        </div>
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 14px;">
                            <a href="<?= site_url('register') ?>"
                                style="display: inline-block; background: linear-gradient(135deg, #facc15 0%, #f97316 100%); color: #0b1020; font-weight: 900; font-size: 1.05rem; padding: 18px 48px; border-radius: 16px; text-decoration: none; box-shadow: 0 10px 30px -5px rgba(15,23,42,0.45); transition: all 0.3s ease;">
                                Get your free API Key
                            </a>
                            <span style="color: rgba(255,255,255,0.45); font-size: 0.82rem; font-weight: 600;">No credit card required to get started</span>
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
              "name": "How do I integrate the Spanish company API?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Integration is done via a standard REST API using an API Key. Examples are available in Python, PHP and Node.js."
              }
            },
            {
              "@type": "Question",
              "name": "How often is BORME data updated?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "We monitor the BORME daily, processing new registrations and corporate changes within a few hours of their official publication."
              }
            },
            {
              "@type": "Question",
              "name": "Do you support Webhooks?",
              "acceptedAnswer": {
                "@type": "Answer",
                "text": "Yes, the Business plan allows you to configure Webhooks to receive real-time push notifications about Spanish Mercantile Registry events."
              }
            }
          ]
        }
        </script>

    </main>
    <?= view('partials/footer_en') ?>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            $('#api_quick_unlock_form').on('submit', function(e) {
                e.preventDefault();
                const $btn   = $(this).find('button');
                const $input = $(this).find('input');
                const email  = $input.val();
                $btn.prop('disabled', true).text('Generating...');

                $.post('<?= site_url("api/quick-unlock") ?>', { email: email }, function(res) {
                    if (res.status === 'success') {
                        Swal.fire({
                            title: 'API Key Generated!',
                            text: 'Your key is: ' + res.api_key + '. Redirecting to documentation.',
                            icon: 'success',
                            confirmButtonText: 'Go to Documentation'
                        }).then(() => { window.location.href = res.redirect; });
                    } else if (res.status === 'exists') {
                        window.location.href = res.redirect;
                    } else {
                        Swal.fire('Error', res.message || 'Error generating the key', 'error');
                        $btn.prop('disabled', false).text('Get Free API Key');
                    }
                }).fail(function() {
                    Swal.fire('Error', 'Connection error', 'error');
                    $btn.prop('disabled', false).text('Get Free API Key');
                });
            });
        });

        // Code tabs
        function switchTab(lang) {
            ['python','php','node'].forEach(l => {
                document.getElementById('code-' + l).style.display = 'none';
                const btn = document.getElementById('tab-' + l);
                btn.style.background = '#fff';
                btn.style.color = '#94a3b8';
                btn.style.borderColor = '#e2e8f0';
            });
            document.getElementById('code-' + lang).style.display = 'block';
            const active = document.getElementById('tab-' + lang);
            active.style.background = '#2563eb';
            active.style.color = '#fff';
            active.style.borderColor = '#2563eb';
            const names = { python: 'example.py', php: 'example.php', node: 'example.js' };
            document.getElementById('tab-filename').textContent = names[lang];
        }

        // Pricing toggle
        let isAnnual = true;
        function togglePricing() {
            isAnnual = !isAnnual;
            const knob          = document.getElementById('toggleKnob');
            const labelMonthly  = document.getElementById('labelMonthly');
            const labelAnnual   = document.getElementById('labelAnnual');
            const pricePro      = document.getElementById('pricePro');
            const priceBusiness = document.getElementById('priceBusiness');

            if (isAnnual) {
                knob.style.transform = 'translateX(24px)';
                labelMonthly.style.color = '#94a3b8'; labelMonthly.style.fontWeight = '600';
                labelAnnual.style.color  = '#2563eb'; labelAnnual.style.fontWeight  = '800';
                pricePro.textContent      = pricePro.dataset.annual;
                priceBusiness.textContent = priceBusiness.dataset.annual;
            } else {
                knob.style.transform = 'translateX(0px)';
                labelMonthly.style.color = '#2563eb'; labelMonthly.style.fontWeight = '800';
                labelAnnual.style.color  = '#94a3b8'; labelAnnual.style.fontWeight  = '600';
                pricePro.textContent      = pricePro.dataset.monthly;
                priceBusiness.textContent = priceBusiness.dataset.monthly;
            }
        }

        // JSON preview modal
        const jsonExamples = {
            get_companies: { success: true, data: { id: 12345, name: "TECH FLOW SOLUTIONS SL", cif: "B12345678", cnae: "6201", cnae_label: "Programación informática", founded: "2024-03-12", province: "MADRID", municipality: "MADRID", address: "CALLE DE LA TECNOLOGIA 42", status: "ACTIVA", score: 94 } },
            get_search: { success: true, data: { name: "TECH FLOW SOLUTIONS SL", cif: "B12345678", score: 94, province: "MADRID", status: "ACTIVA" } },
            post_batch: { success: true, data: [{ name: "INDUSTRIA DE DISENO TEXTIL SA", cif: "A15075062" }, { name: "INDITEX LOGISTICA SA", cif: "B00000001" }], meta: { requested: 2, found: 2, cost: 2, truncated: false } },
            get_score: { success: true, data: { cif: "B12345678", score: 94, priority: "VERY_HIGH", reasons: ["Recent capital increase", "High BORME activity"], last_signal: { type: "CAPITAL_INCREASE", date: "2024-05-01" } } },
            get_signals: { success: true, data: { cif: "B12345678", signals: [{ type: "borme_event", label: "AMPLIACION_CAPITAL", date: "2024-05-01", probability: "VERY_HIGH" }] } },
            get_borme: { success: true, data: { cif: "B12345678", company_name: "EMPRESA DE EJEMPLO SL", events: [{ date: "2023-11-01", act_types: "Appointments, Resignations", description: "Resignations. Sole Administrator: JUAN PEREZ...", url_pdf: "https://www.boe.es/borme/dias/2023/11/01/pdfs/BORME-A-2023-100-28.pdf" }] } },
            get_insights: { success: true, data: { profile: "SaaS / Fintech / Cloud", summary: "High-traction company with imminent need for tech scaling.", needs: ["Cloud Infrastructure", "Cybersecurity", "Dev Hiring"], conversion_probability: "HIGH", estimated_ticket: "€10k–50k" } },
            get_radar: { success: true, meta: { plan: "business", count: 142, limit: 500 }, data: [{ name: "NEW CORP SL", cif: "B99887766", founded: "2024-05-05", province: "BARCELONA", score: 88 }] },
            get_network: { success: true, data: { cif: "B12345678", administrators: [{ name: "GARCIA LOPEZ JUAN", position: "Sole Administrator", linked_companies: [{ name: "OTRA EMPRESA SL", cif: "B87654321", status: "ACTIVA" }] }] } },
            get_match: { success: true, data: { cif: "B12345678", seller_sector: "software", match_score: 85, analysis: { match_level: "High", synergy: "High synergy", buyer_needs: ["Digitalisation", "CRM"] }, sales_pitch: "I noticed you're growing fast. Our software can help you..." } },
            get_contracts: { success: true, count: 1, cif: "B12345678", contracts: [{ organ: "Ministerio de Transformación Digital", amount: 154200.50, award_date: "2024-02-15", status: "Adjudicado", description: "Desarrollo plataforma cloud" }] },
            get_risk_profile: { success: true, data: { cif: "B12345678", risk_score: 22, risk_level: "BAJO", confidence_score: 0.95, data_quality_score: 0.90, summary_message: "Empresa con bajo riesgo legal y solvencia acreditada.", legal_state: "ACTIVA", data_sources: { borme_active: true, judicial_bulletins: false }, dimensions: { legal_distress: { level: "BAJO", score: 10 }, filing_compliance: { level: "OPTIMO", score: 95 } }, canonical_events: [], model_version: "2.1.0", calculated_at: "2024-05-15T10:00:00Z" } },
            post_webhook: { success: true, message: "Webhook created successfully", id: 789 },
            get_webhooks: { success: true, data: [{ id: "789", url: "https://yourcrm.com/api/callback", event: "company.created" }] },
            delete_webhook: { success: true, message: "Webhook deleted" }
        };

        function showJsonPreview(key) {
            const modal    = document.getElementById('json-modal');
            const content  = document.getElementById('modal-json-content');
            const endpoint = document.getElementById('modal-endpoint-name');
            const names = { get_companies: 'GET /companies', get_search: 'GET /companies/search', post_batch: 'POST /companies/batch', get_score: 'GET /companies/score', get_signals: 'GET /companies/signals', get_borme: 'GET /companies/borme', get_insights: 'GET /companies/insights', get_radar: 'GET /companies/radar', get_network: 'GET /companies/network', get_match: 'GET /companies/match', get_contracts: 'GET /companies/contracts', get_risk_profile: 'GET /companies/risk-profile', post_webhook: 'POST /webhooks', get_webhooks: 'GET /webhooks', delete_webhook: 'DELETE /webhooks/{id}' };
            endpoint.textContent = names[key];
            content.innerHTML = syntaxHighlight(jsonExamples[key]);
            modal.style.display = 'flex';
        }
        function closeJsonModal() { document.getElementById('json-modal').style.display = 'none'; }
        function syntaxHighlight(json) {
            if (typeof json != 'string') { json = JSON.stringify(json, undefined, 2); }
            json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            return json.replace(/(\"(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*\"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g, function (match) {
                var cls = 'color:#d97706;';
                if (/^\"/.test(match)) { cls = /:$/.test(match) ? 'color:#2563eb;' : 'color:#16a34a;'; }
                else if (/true|false/.test(match)) { cls = 'color:#9333ea;'; }
                else if (/null/.test(match)) { cls = 'color:#64748b;'; }
                return '<span style="' + cls + ' font-weight: 500;">' + match + '</span>';
            });
        }
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
                <button onclick="closeJsonModal()" style="background:#ffffff; color:#475569; border:1px solid #e2e8f0; padding:10px 24px; border-radius:10px; font-size:13px; font-weight:700; cursor:pointer; transition:all 0.2s;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='#ffffff'">Close</button>
            </div>
        </div>
    </div>

</body>

</html>
