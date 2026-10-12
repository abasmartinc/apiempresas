<!doctype html>
<html lang="en">

<head>
    <?= view('partials/head', [
        'title' => 'KYB API for Spain: Verify Spanish Companies and Directors | SpainCompanyAPI',
        'excerptText' => 'Automate KYB for Spanish customers and suppliers: verify the tax ID (CIF/NIF), registry status, current directors and BORME filings with a REST API. ' . (int) $freeLimit . ' free requests.',
        'canonical' => site_url('spanish-company-data-api'),
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

        .api-code-keyword {
            color: #c678dd;
        }

        .api-code-string {
            color: #98c379;
        }

        .api-code-attr {
            color: #d19a66;
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
    </style>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "WebAPI",
      "name": "SpainCompanyAPI KYB API",
      "description": "REST API to verify Spanish companies in KYB processes: tax ID (CIF/NIF), registry status, directors and BORME filings.",
      "documentation": "<?= site_url('docs') ?>",
      "provider": {
        "@type": "Organization",
        "name": "SpainCompanyAPI"
      }
    }
    </script>
</head>

<body>
    <?= view('partials/header_en') ?>

    <main class="radar-page">

        <!-- HERO: API KYB (26-09-2026). La home se queda con "API de empresas / validar CIF";
             esta página apunta a KYB, verificación de empresas y NIF. Versión inglesa traducida de seo/api_prices.php (12-10-2026). -->
        <?php
        $ps = $publicStats ?? [];
        // English number format (PublicStats::corto() is Spanish: "+4,7 M", "+12 mil")
        $fmtCorto = static function (int $n): string {
            if ($n >= 1000000) { $m = floor($n / 100000) / 10; return '+' . number_format($m, $m == floor($m) ? 0 : 1, '.', ',') . 'M'; }
            if ($n >= 1000) { return '+' . number_format(floor($n / 1000), 0, '.', ',') . 'K'; }
            return (string) $n;
        };
        ?>
        <header class="api-unified-hero">
            <div class="container" style="max-width:1100px; margin:0 auto; padding:0 2rem;">

                <!-- Language switcher -->
                <div class="lang-switcher">
                    <a href="https://apiempresas.es/api-empresas" class="inactive" title="Versión en español" hreflang="es" lang="es">🇪🇸 ES</a>
                    <a href="<?= site_url('spanish-company-data-api') ?>" class="active" title="English version">🇬🇧 EN</a>
                </div>

                <div class="api-hero-badge">
                    <span class="api-hero-badge-dot"></span>
                    KYB API · Company verification
                </div>

                <h1 class="api-hero-title">
                    KYB API to verify Spanish companies<br>
                    <span>Tax ID, registry status and directors</span>
                </h1>

                <p class="api-hero-sub">
                    Automate the onboarding of Spanish B2B customers and suppliers: check that the tax ID (CIF/NIF)
                    belongs to a company that exists and is active, who its directors are and what it has filed in the BORME.
                    One REST call, a JSON response.
                </p>

                <div class="api-hero-stars">
                    <span style="color:#94a3b8; font-size:0.92rem;"><strong><?= (int) $freeLimit ?> free requests</strong> to try it · No card required</span>
                </div>

                <div class="api-hero-badges">
                    <span class="api-hero-chip api-hero-chip--green">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                        BORME data
                    </span>
                    <span class="api-hero-chip api-hero-chip--blue">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
                        REST · JSON
                    </span>
                    <span class="api-hero-chip api-hero-chip--purple">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        For KYB and KYC processes
                    </span>
                </div>

                <div class="api-hero-actions">
                    <a href="<?= site_url('register?intent=api&plan=free&source=en_api_kyb_hero') ?>" class="api-hero-btn-primary" data-track-event="hero_cta_click" data-track-metadata='{"cta_text": "Try the API for free", "source_block": "hero", "page_type": "api_kyb_en"}'>
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M13 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V9z"/><polyline points="13 2 13 9 20 9"/></svg>
                        Try the API for free
                    </a>
                    <a href="<?= site_url('docs') ?>" class="api-hero-btn-ghost">
                        <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                        Read the docs
                    </a>
                </div>

                <!-- Cifras reales (App\Libraries\PublicStats, caché de 12 h) -->
                <div class="kyb-proof">
                    <?php if (!empty($ps['companies'])): ?>
                    <div><strong><?= esc($fmtCorto((int) $ps['companies'])) ?></strong><span>Companies</span></div>
                    <?php endif; ?>
                    <?php if (!empty($ps['acts'])): ?>
                    <div><strong><?= esc($fmtCorto((int) $ps['acts'])) ?></strong><span>BORME filings</span></div>
                    <?php endif; ?>
                    <div><strong>Daily</strong><span>BORME updates</span></div>
                </div>

            </div>
        </header>

        <style>
            .kyb-proof { display:flex; gap:32px; justify-content:center; flex-wrap:wrap; padding-top:1rem; border-top:1px solid rgba(255,255,255,0.07); }
            .kyb-proof div { text-align:center; }
            .kyb-proof a { text-decoration:none; }
            .kyb-proof strong { display:block; font-size:1.6rem; font-weight:900; color:#fff; letter-spacing:-0.04em; }
            .kyb-proof span { display:block; font-size:0.78rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.05em; margin-top:2px; }

            .kyb-wrap { max-width: 1100px; margin: 0 auto; }
            .kyb-lead { font-size: 1.1rem; line-height: 1.7; color: #475569; max-width: 780px; }
            .kyb-table-box { background:#fff; border:1px solid #e2e8f0; border-radius:20px; overflow:hidden; margin-top:36px; box-shadow: 0 10px 30px -18px rgba(15,23,42,.18); }
            .kyb-table { width:100%; border-collapse:collapse; }
            .kyb-table th { background:#f8fafc; text-align:left; font-size:12px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:.05em; padding:16px 20px; border-bottom:2px solid #e2e8f0; }
            .kyb-table td { padding:16px 20px; border-bottom:1px solid #f1f5f9; vertical-align:top; font-size:0.95rem; color:#334155; line-height:1.5; }
            .kyb-table tr:last-child td { border-bottom:none; }
            .kyb-table td:first-child { font-weight:800; color:#0f172a; width:28%; }
            .kyb-table tr.kyb-no td { background:#fafafa; color:#64748b; }
            .kyb-plan { display:inline-block; font-size:11px; font-weight:800; padding:4px 10px; border-radius:99px; white-space:nowrap; }
            .kyb-plan--free { background:#f1f5f9; color:#475569; }
            .kyb-plan--pro { background:#eef2ff; color:#4338ca; }
            .kyb-plan--biz { background:#ecfdf5; color:#047857; }
            .kyb-plan--no { background:#fef2f2; color:#b91c1c; }

            .kyb-flow { display:grid; grid-template-columns: 1.05fr .95fr; gap:48px; align-items:start; margin-top:40px; }
            .kyb-steps { list-style:none; padding:0; margin:0; display:grid; gap:22px; }
            .kyb-steps > li { display:flex; gap:16px; }
            .kyb-steps .n { flex-shrink:0; width:34px; height:34px; border-radius:10px; background:#2563eb; color:#fff; font-weight:900; display:flex; align-items:center; justify-content:center; }
            .kyb-steps h3 { margin:2px 0 4px; font-size:1.05rem; font-weight:800; color:#0f172a; }
            .kyb-steps p { margin:0; color:#64748b; font-size:0.95rem; line-height:1.55; }
            .kyb-steps ul { margin:8px 0 0; padding-left:18px; color:#475569; font-size:0.92rem; line-height:1.6; list-style:disc; }
            .kyb-steps ul li { display:list-item; margin-bottom:4px; }
            .kyb-code { background:#0f172a; border-radius:20px; overflow:hidden; border:1px solid #1e293b; box-shadow: 0 30px 60px -20px rgba(15,23,42,.35); }
            .kyb-code-bar { background:#1e293b; padding:12px 18px; font-family:monospace; font-size:12px; color:#94a3b8; }
            .kyb-code pre { margin:0; padding:22px 24px; font-family:'Fira Code','JetBrains Mono',Consolas,monospace; font-size:13px; line-height:1.7; color:#e2e8f0; overflow-x:auto; }
            .kyb-code .k { color:#f07178; } .kyb-code .s { color:#c3e88d; } .kyb-code .c { color:#64748b; }
            .kyb-errors { margin-top:18px; background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:18px 20px; }
            .kyb-errors h3 { margin:0 0 10px; font-size:0.95rem; font-weight:800; color:#0f172a; }
            .kyb-errors dl { margin:0; display:grid; grid-template-columns:auto 1fr; gap:8px 14px; font-size:0.88rem; color:#475569; }
            .kyb-errors dt { font-family:monospace; font-weight:700; color:#1e40af; }
            .kyb-errors dd { margin:0; }

            .kyb-cards { display:grid; grid-template-columns: repeat(3, 1fr); gap:24px; margin-top:36px; }
            .kyb-card { background:#fff; border:1px solid #e2e8f0; border-radius:20px; padding:26px; }
            .kyb-card h3 { margin:0 0 10px; font-size:1.1rem; font-weight:850; color:#0f172a; }
            .kyb-card p { margin:0; color:#475569; font-size:0.95rem; line-height:1.6; }
            .kyb-sectors { display:grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap:20px; margin-top:36px; }
            .kyb-sector { background:#fff; border:1px solid #e2e8f0; border-radius:18px; padding:22px; }
            .kyb-sector h3 { margin:0 0 8px; font-size:1rem; font-weight:800; color:#0f172a; }
            .kyb-sector p { margin:0; color:#64748b; font-size:0.9rem; line-height:1.55; }
            .kyb-more { margin-top:22px; display:flex; gap:24px; flex-wrap:wrap; font-weight:800; }
            .kyb-more a { color:#2563eb; text-decoration:none; }

            .kyb-flow > * { min-width: 0; }
            @media (max-width: 900px) {
                .kyb-flow, .kyb-cards { grid-template-columns: 1fr; }
            }
            @media (max-width: 640px) {
                .kyb-table thead { display:none; }
                .kyb-table, .kyb-table tbody, .kyb-table tr, .kyb-table td { display:block; width:auto; }
                .kyb-table tr { padding:14px 18px; border-bottom:1px solid #f1f5f9; }
                .kyb-table td { padding:0; border:none; }
                .kyb-table td:first-child { margin-bottom:4px; width:auto; }
                .kyb-table tr.kyb-no { background:#fafafa; }
                .kyb-table tr.kyb-no td { background:transparent; }
                .kyb-table td:last-child { margin-top:8px; }
                .kyb-errors dl { grid-template-columns: 1fr; gap:2px; }
                .kyb-errors dd { margin-bottom:8px; }
                .kyb-code pre { font-size:12px; padding:18px; }
            }
        </style>

        <!-- QUÉ CUBRE UN KYB -->
        <section class="radar-section" style="background:#fbfcfe;">
            <div class="container kyb-wrap">
                <div class="radar-kicker">Know Your Business</div>
                <h2 class="radar-title" style="margin-top:12px;">What a company KYB checks and what the API covers</h2>
                <p class="kyb-lead">
                    KYB is the verification of a company before you onboard it as a customer, supplier or partner.
                    This is what you can automate with the API for Spanish companies, and what you will need to cover elsewhere.
                </p>

                <div class="kyb-table-box">
                    <table class="kyb-table">
                        <thead>
                            <tr><th>Check</th><th>What the API returns</th><th>Plan</th></tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>The company exists and the tax ID is its own</td>
                                <td>The legal name of the company behind the tax ID (CIF/NIF) your customer gives you.</td>
                                <td><span class="kyb-plan kyb-plan--free">From Free</span></td>
                            </tr>
                            <tr>
                                <td>It is still active</td>
                                <td>Registry status based on BORME filings: active, dissolved, in insolvency, closed…</td>
                                <td><span class="kyb-plan kyb-plan--free">From Free</span></td>
                            </tr>
                            <tr>
                                <td>Who can sign on its behalf</td>
                                <td>Current directors and officers, with their appointment date, using the <code>admin=true</code> parameter.</td>
                                <td><span class="kyb-plan kyb-plan--pro">Pro</span></td>
                            </tr>
                            <tr>
                                <td>Where it is and what it does</td>
                                <td>Full registered address, CNAE activity code and corporate purpose. On Free, CNAE and province.</td>
                                <td><span class="kyb-plan kyb-plan--pro">Pro</span></td>
                            </tr>
                            <tr>
                                <td>Recent changes worth a review</td>
                                <td>BORME filing history: changes of directors, address or capital.</td>
                                <td><span class="kyb-plan kyb-plan--pro">Pro</span></td>
                            </tr>
                            <tr>
                                <td>Risk signals</td>
                                <td>Dissolutions, insolvencies and other corporate signals (Pro). Risk and solvency profile (Business).</td>
                                <td><span class="kyb-plan kyb-plan--pro">Pro</span> <span class="kyb-plan kyb-plan--biz">Business</span></td>
                            </tr>
                            <tr class="kyb-no">
                                <td>Ultimate beneficial owner (UBO)</td>
                                <td>Not included. Ask the customer for a UBO declaration, or check Spain's beneficial ownership register if you have access as an obliged entity.</td>
                                <td><span class="kyb-plan kyb-plan--no">Not included</span></td>
                            </tr>
                            <tr class="kyb-no">
                                <td>Sanctions and PEP lists</td>
                                <td>Not included. You will need a dedicated sanctions and PEP screening provider.</td>
                                <td><span class="kyb-plan kyb-plan--no">Not included</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- CÓMO AUTOMATIZAR EL ONBOARDING -->
        <section class="radar-section radar-section--soft">
            <div class="container kyb-wrap">
                <div class="radar-kicker">Onboarding B2B</div>
                <h2 class="radar-title" style="margin-top:12px;">How to automate KYB onboarding with the API</h2>
                <p class="kyb-lead">A typical company onboarding flow, end to end.</p>

                <div class="kyb-flow">
                    <ol class="kyb-steps">
                        <li><span class="n">1</span><div>
                            <h3>Ask for the tax ID in your sign-up form</h3>
                            <p>The NIF (formerly CIF) is enough to identify a Spanish company. If the customer does not have it at hand, the name search finds it for you.</p>
                        </div></li>
                        <li><span class="n">2</span><div>
                            <h3>Look up the company</h3>
                            <p>One call to <code>/companies?cif=…&amp;admin=true</code> returns legal name, status, address, CNAE code and directors.</p>
                        </div></li>
                        <li><span class="n">3</span><div>
                            <h3>Apply your rules</h3>
                            <ul>
                                <li>If the status is not "activa" (compare case-insensitively), reject the sign-up or send it to manual review.</li>
                                <li>If the signer is not in <code>administrators</code>, ask for a power of attorney or review manually.</li>
                                <li>If the legal name does not match what the customer typed, ask them to correct it.</li>
                            </ul>
                        </div></li>
                        <li><span class="n">4</span><div>
                            <h3>Keep the evidence</h3>
                            <p>Store the response and the lookup date as proof of the check you carried out.</p>
                        </div></li>
                        <li><span class="n">5</span><div>
                            <h3>Re-check your portfolio regularly</h3>
                            <p>With <code>/companies/batch</code> (100 tax IDs per request) and <code>/companies/signals</code> you spot customers that have been dissolved or entered insolvency.</p>
                        </div></li>
                    </ol>

                    <div>
                        <div class="kyb-code">
                            <div class="kyb-code-bar">GET /api/v1/companies?cif=B12345678&amp;admin=true</div>
<pre>{
  <span class="k">"success"</span>: true,
  <span class="k">"data"</span>: {
    <span class="k">"cif"</span>: <span class="s">"B12345678"</span>,
    <span class="k">"name"</span>: <span class="s">"EMPRESA DE EJEMPLO SL"</span>,
    <span class="k">"status"</span>: <span class="s">"ACTIVA"</span>,
    <span class="k">"province"</span>: <span class="s">"MADRID"</span>,
    <span class="k">"cnae"</span>: <span class="s">"6201"</span>,
    <span class="k">"cnae_label"</span>: <span class="s">"Actividades de programación informática"</span>,
    <span class="k">"administrators"</span>: [
      { <span class="k">"name"</span>: <span class="s">"JUAN PÉREZ GARCÍA"</span>, <span class="k">"position"</span>: <span class="s">"Administrador Único"</span> }
    ]
  }
}</pre>
                        </div>
                        <div class="kyb-errors">
                            <h3>Error responses that also help you decide</h3>
                            <dl>
                                <dt>COMPANY_NOT_FOUND</dt><dd>No company has that tax ID.</dd>
                                <dt>INVALID_CIF_FORMAT</dt><dd>The tax ID does not have a company format (letter, 7 digits and a check character).</dd>
                                <dt>AUTONOMO_NOT_SUPPORTED</dt><dd>It is an individual's tax ID. The API only covers companies, for data protection reasons.</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- KYB, KYC Y NIF -->
        <section class="radar-section">
            <div class="container kyb-wrap">
                <div class="radar-kicker">Key concepts</div>
                <h2 class="radar-title" style="margin-top:12px;">KYB, KYC and the Spanish tax ID: what to know</h2>
                <div class="kyb-cards">
                    <div class="kyb-card">
                        <h3>KYB is not KYC</h3>
                        <p>KYC verifies individuals: who they are and whether their identity is real. KYB verifies companies: that they exist, that they are still active, who runs them and, if your activity requires it, who their ultimate beneficial owner is.</p>
                    </div>
                    <div class="kyb-card">
                        <h3>NIF or CIF?</h3>
                        <p>Since 2008 Spanish companies have a NIF; "CIF" is still widely used for it. The API accepts any company's NIF, with or without hyphens. Individuals' NIFs (sole traders, autónomos) are not supported, for data protection reasons.</p>
                    </div>
                    <div class="kyb-card">
                        <h3>Validating the format is not enough</h3>
                        <p>The check digit only tells you the number is well formed. It does not tell you whether the company exists, whether it is active or whether the person signing up has anything to do with it. For that you need to look it up.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- CASOS POR SECTOR -->
        <section class="radar-section radar-section--soft">
            <div class="container kyb-wrap">
                <div class="radar-kicker">Who uses it</div>
                <h2 class="radar-title" style="margin-top:12px;">Company verification by industry</h2>
                <div class="kyb-sectors">
                    <div class="kyb-sector">
                        <h3>Fintech and lending</h3>
                        <p>Onboarding Spanish businesses as customers and checking their situation before granting credit or payment terms.</p>
                    </div>
                    <div class="kyb-sector">
                        <h3>B2B marketplaces</h3>
                        <p>Onboarding sellers and suppliers after checking that the company exists and is active.</p>
                    </div>
                    <div class="kyb-sector">
                        <h3>SaaS and invoicing</h3>
                        <p>Business customers set up with the correct legal name and tax address from day one.</p>
                    </div>
                    <div class="kyb-sector">
                        <h3>Accounting and law firms</h3>
                        <p>Onboarding new clients and reviewing the portfolio regularly to catch dissolutions or insolvencies.</p>
                    </div>
                    <div class="kyb-sector">
                        <h3>Procurement and suppliers</h3>
                        <p>Supplier due diligence: status, directors and, on Business, public contracts and risk profile.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- ENDPOINTS PARA KYB -->
        <section class="radar-section">
            <div class="container kyb-wrap">
                <div class="radar-heading">
                    <div class="radar-kicker">Quick reference</div>
                    <h2 class="radar-title">API endpoints for KYB</h2>
                    <p class="radar-subtitle" style="text-align: left; max-width: 800px; margin-left: 0;">
                        The ones most used in a company verification. The rest (commercial scoring, newly
                        incorporated companies, corporate graphs…) are in the docs. If you are looking for the full
                        <a href="<?= site_url() ?>">Spanish company data API</a> (search, enrichment, BORME
                        and risk), start there.
                    </p>
                </div>

                <div style="background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; overflow-x: auto; box-shadow: var(--shadow-soft);">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 2px solid #e2e8f0;">
                                <th style="padding: 20px; text-align: left; font-weight: 800; font-size: 13px; color: #64748b; text-transform: uppercase;">Endpoint</th>
                                <th style="padding: 20px; text-align: left; font-weight: 800; font-size: 13px; color: #64748b; text-transform: uppercase;">What it does in a KYB</th>
                                <th style="padding: 20px; text-align: center; font-weight: 800; font-size: 13px; color: #64748b; text-transform: uppercase;">Plan</th>
                                <th style="padding: 20px; text-align: center; font-weight: 800; font-size: 13px; color: #64748b; text-transform: uppercase;">Example</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        GET /companies</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parameter: ?cif=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Company data by tax ID</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Legal name, registry status, CNAE code and province. With <code style="color: #2563eb;">admin=true</code>, directors and officers (Pro). On Free, address and corporate purpose are hidden.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">FREE</span>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('get_companies')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button>
                                </td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        GET /companies/search</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parameter: ?q=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Search by name</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Find a company's tax ID when the customer only gives you its name.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #f1f5f9; color: #475569; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">FREE</span>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('get_search')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button>
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
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Batch lookup</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Check up to 100 tax IDs in a single request: useful for reviewing your customer or supplier portfolio. Only companies found are charged.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">PRO
                                        / BUS</span>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('post_batch')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button>
                                </td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        GET /companies/borme</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parameter: ?cif=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">BORME
                                        filing history</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        The full chronological history of a company's Commercial Registry filings, useful for KYB audits and due diligence.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">PRO
                                        / BUS</span></td>
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('get_borme')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button>
                                </td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        GET /companies/signals</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parameter: ?cif=...</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Corporate
                                        signals (BORME)</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Dissolutions, insolvencies, capital and director changes published in the BORME.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #eff6ff; color: #2563eb; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">PRO
                                        / BUS</span></td>
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('get_signals')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button>
                                </td>
                            </tr>
                            <tr class="api-endpoint-row" style="border-bottom: 1px solid #f1f5f9;">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        GET /companies/risk-profile</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Parameter: cif</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Risk and solvency profile</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Algorithmic score, annual accounts filing compliance, governance volatility and corporate distress events.
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #fdf2f8; color: #db2777; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">BUSINESS</span></td>
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('get_risk_profile')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button>
                                </td>
                            </tr>
                            <tr class="api-endpoint-row">
                                <td style="padding: 18px 20px;">
                                    <div
                                        style="font-family: monospace; font-weight: 700; color: #1e40af; margin-bottom: 4px;">
                                        POST /webhooks</div>
                                    <div style="font-size: 0.75rem; color: #94a3b8;">Body: {url, event}</div>
                                </td>
                                <td style="padding: 18px 20px;">
                                    <strong
                                        style="display: block; color: #0f172a; font-size: 0.9rem; margin-bottom: 4px;">Push notifications (webhooks)</strong>
                                    <p style="margin: 0; font-size: 0.82rem; color: #64748b; line-height: 1.4;">
                                        Register a callback URL to receive HTTP notifications when something changes
                                        in the companies you monitor (BORME filings, status or risk).
                                    </p>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;"><span
                                        style="background: #fdf2f8; color: #db2777; padding: 4px 10px; border-radius: 6px; font-weight: 800; font-size: 10px; white-space: nowrap;">BUSINESS</span>
                                </td>
                                <td style="padding: 18px 20px; text-align: center;">
                                    <button type="button" onclick="event.preventDefault(); showJsonPreview('post_webhook')" style="background: none; border: 1px solid #e2e8f0; color: #3b82f6; font-size: 11px; font-weight: 800; padding: 6px 12px; border-radius: 8px; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#3b82f6'; this.style.background='#eff6ff';" onmouseout="this.style.borderColor='#e2e8f0'; this.style.background='none';">VIEW JSON</button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="kyb-more">
                    <a href="<?= site_url('docs') ?>">See every endpoint in the docs →</a>
                    <a href="<?= site_url() ?>#precios">Full plan comparison →</a>
                </div>
            </div>
        </section>

        <!-- PRICING SECTION (CORRECTED DATA) -->
        <section id="planes" class="radar-section">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">Pricing</div>
                    <h2 class="radar-title">KYB API pricing</h2>
                    <p class="radar-subtitle">
                        Start for free to test the flow with real data. To verify directors and review your portfolio you will need Pro. Prices in euros, VAT not included.
                    </p>
                </div>

                <!-- TOGGLE ANUAL / MENSUAL -->
                <div style="display: flex; justify-content: center; align-items: center; margin-bottom: 64px; margin-top: 16px; gap: 12px;">
                    <span style="font-size: 0.95rem; font-weight: 800; color: #2563eb; transition: all 0.3s;" id="labelMonthly">Monthly</span>
                    <button type="button" id="billingToggle" style="width: 56px; height: 32px; background: #0f172a; border-radius: 99px; position: relative; cursor: pointer; border: none; padding: 4px; transition: background 0.3s;" onclick="togglePricing()">
                        <div id="toggleKnob" style="width: 24px; height: 24px; background: white; border-radius: 50%; box-shadow: 0 2px 4px rgba(0,0,0,0.1); transition: transform 0.3s cubic-bezier(0.4, 0.0, 0.2, 1); transform: translateX(0px);"></div>
                    </button>
                    <span style="font-size: 0.95rem; font-weight: 600; color: #94a3b8; display: flex; align-items: center; gap: 8px; transition: all 0.3s;" id="labelAnnual">Annual <span style="background: #dcfce7; color: #166534; font-size: 10px; padding: 4px 8px; border-radius: 99px; letter-spacing: 0.05em; font-weight: 800;">SAVE 20%</span></span>
                </div>

                <div class="api-pricing-grid">

                    <!-- FREE -->
                    <div class="api-pricing-card free-plan">
                        <div class="api-pricing-card__header">
                            <h3>Free</h3>
                        </div>
                        <div class="api-price-value">€0<span>/ one-time</span></div>
                        <p class="api-pricing-card__desc">To test your onboarding flow with real data: <?= (int) $freeLimit ?> requests that never expire.</p>

                        <ul class="api-price-list">
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> <?= (int) $freeLimit ?> free requests (one-time, not renewed)</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Legal name, status, CNAE code and province</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Lookup by tax ID and search by name</li>
                        </ul>

                        <div class="api-price-cta" style="margin-top: 32px;">
                            <form id="api_quick_unlock_form" style="display: flex; flex-direction: column; gap: 12px;">
                                <input type="email" name="email" placeholder="Your work email" required 
                                       style="padding: 14px 20px; border-radius: 12px; border: 2px solid #e2e8f0; font-size: 1rem; width: 100%; outline: none; transition: border-color 0.2s;"
                                       onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                                <button type="submit" class="api-pricing-btn" style="width: 100%; justify-content: center; background: #0f172a; color: white; border: none; cursor: pointer;">
                                    Get a free API key
                                </button>
                            </form>
                            <p style="font-size: 0.75rem; color: rgba(255, 255, 255, 0.7); margin-top: 12px; text-align: center;">
                                Instant access. No credit card required.
                            </p>
                        </div>
                    </div>

                    <!-- PRO -->
                    <div class="api-pricing-card featured">
                        <div class="api-pricing-card__header">
                            <h3>Pro</h3>
                        </div>
                        <div class="api-price-value"><b id="pricePro" data-monthly="19" data-annual="15.17" style="font-weight: inherit;">19</b>€<span>/ month + VAT</span></div>
                        <div class="api-annual-note" style="display: none; margin: -6px 0 10px; font-size: 0.8rem; font-weight: 700; color: #ecfdf5; opacity: .92;">Billed yearly: €182 + VAT (you save €46)</div>
                        <p class="api-pricing-card__desc">For a full KYB: directors, address, BORME history and portfolio monitoring.</p>

                        <ul class="api-price-list">
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> 3,000 requests / month</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Current directors, with appointment date</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> One-call KYB verification, incl. VAT number in VIES (/verify)</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Company name to tax ID in batch (/reconcile)</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Full address and corporate purpose</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Revenue band and latest filed accounts year</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Monitoring of 100 companies (BORME, status and risk)</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> BORME history and signals</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Batch lookups: 100 tax IDs per request</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Priority email support: reply in under 2 h</li>
                        </ul>

                        <a href="<?= site_url('register?intent=api&plan=pro&period=monthly&source=en_api_kyb_pricing_pro') ?>" class="api-pricing-btn primary">Start with Pro</a>
                    </div>

                    <!-- BUSINESS -->
                    <div class="api-pricing-card business-plan">
                        <div class="api-pricing-card__header">
                            <h3>Business</h3>
                        </div>
                        <div class="api-price-value"><b id="priceBusiness" data-monthly="49" data-annual="39.17" style="font-weight: inherit;">49</b>€<span>/ month + VAT</span></div>
                        <div class="api-annual-note" style="display: none; margin: -6px 0 10px; font-size: 0.8rem; font-weight: 700; color: #ecfdf5; opacity: .92;">Billed yearly: €470 + VAT (you save €118)</div>
                        <p class="api-pricing-card__desc">For more volume and risk analysis: solvency profile, public contracts and webhooks.</p>

                        <ul class="api-price-list">
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> 10,000 requests / month</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Risk and solvency profile</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Public contracts and awards</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Monitoring of 1,000 companies (BORME, status and risk)</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Push webhooks for monitored companies</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Segments: download companies by CNAE, area and size</li>
                            <li><svg width="18" height="18" viewBox="0 0 20 20" fill="currentColor">
                                    <path
                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" />
                                </svg> Priority email support: reply in under 2 h</li>
                        </ul>

                        <a href="<?= site_url('register?intent=api&plan=business&period=monthly&source=en_api_kyb_pricing_business') ?>" class="api-pricing-btn">Start with Business</a>
                    </div>
                </div>
                <p style="text-align:center; margin-top:28px; font-weight:800;"><a href="<?= site_url() ?>#precios" style="color:#2563eb; text-decoration:none;">See the full plan and feature comparison →</a></p>

            </div>

            <div style="margin-top: 60px; background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 50%, #f0fdf4 100%); padding: 48px 32px; border-radius: 24px; text-align: center; position: relative; overflow: hidden; border: 1px solid rgba(59, 130, 246, 0.15); box-shadow: 0 20px 40px -15px rgba(37, 99, 235, 0.1); max-width: 900px; margin-left: auto; margin-right: auto;">
                
                <!-- Patrón de puntos decorativo de fondo -->
                <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.4; background-image: radial-gradient(#cbd5e1 1px, transparent 1px); background-size: 20px 20px; pointer-events: none;"></div>
                
                <!-- Efectos de luz suaves -->
                <div style="position: absolute; top: -30%; left: -10%; width: 50%; height: 160%; background: radial-gradient(circle, rgba(59,130,246,0.1) 0%, transparent 60%); pointer-events: none;"></div>
                <div style="position: absolute; bottom: -30%; right: -10%; width: 50%; height: 160%; background: radial-gradient(circle, rgba(16,185,129,0.08) 0%, transparent 60%); pointer-events: none;"></div>

                <div style="position: relative; z-index: 1;">
                    <div style="display: inline-block; background: #ffffff; color: #2563eb; font-size: 0.8rem; font-weight: 800; padding: 6px 16px; border-radius: 99px; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 16px; box-shadow: 0 4px 6px -1px rgba(37,99,235,0.1); border: 1px solid rgba(59,130,246,0.1);">No subscription</div>
                    <h3 style="color: #0f172a; font-size: 2.1rem; font-weight: 900; margin: 0 0 12px; letter-spacing: -0.03em;">Prefer to pay only for what you use?</h3>
                    <p style="color: #475569; font-size: 1.15rem; max-width: 600px; margin: 0 auto 32px; line-height: 1.6;">Buy <strong style="color: #0f172a;">prepaid credits</strong> with no subscription and no expiry: 1,000 credits for €9, or a custom pack from 10,000 credits with volume discounts.</p>
                    
                    <a href="<?= site_url('buy-api-credits') ?>" style="display: inline-flex; align-items: center; gap: 10px; background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: #fff; padding: 18px 40px; border-radius: 16px; font-weight: 800; font-size: 1.1rem; text-decoration: none; box-shadow: 0 10px 25px rgba(37,99,235,0.4); transition: all 0.3s ease; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">
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
                        See credit packs
                    </a>
                </div>
            </div>

            <div style="margin-top: 24px; text-align: center; padding: 16px;">
                <p style="color: #64748b; font-weight: 700; margin: 0; font-size: 0.95rem;">Need a higher volume or custom invoicing? <a href="mailto:soporte@apiempresas.es" style="color: #3b82f6; text-decoration: none;">Email us about your project →</a></p>
            </div>
            </div>
        </section>

        <!-- CODE INTEGRATION -->
        <section class="radar-section radar-section--soft">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">For developers</div>
                    <h2 class="radar-title">KYB verification example in Python, PHP and Node.js</h2>
                    <p class="radar-subtitle">Look up the company with its directors and decide whether the onboarding goes ahead. API key authentication and SDKs for PHP, Node.js and Python.</p>
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
                            style="flex:1;text-align:center;font-size:12px;color:#64748b;font-weight:700;font-family:monospace;">verify_company.py</span>
                    </div>
                    <pre id="code-python"
                        style="margin:0;padding:32px 36px;font-family:'Fira Code','Courier New',monospace;font-size:13.5px;line-height:2;color:#e2e8f0;overflow-x:auto;"><span class="api-code-keyword">import</span> requests

res = requests.get(
    <span class="api-code-string">"https://apiempresas.es/api/v1/companies"</span>,
    params={<span class="api-code-attr">"cif"</span>: <span class="api-code-string">"B12345678"</span>, <span class="api-code-attr">"admin"</span>: <span class="api-code-string">"true"</span>},
    headers={<span class="api-code-attr">"X-API-KEY"</span>: <span class="api-code-string">"your_api_key"</span>},
).json()

<span class="api-code-keyword">if not</span> res[<span class="api-code-string">"success"</span>]:
    <span class="api-code-keyword">print</span>(<span class="api-code-string">"Manual review:"</span>, res[<span class="api-code-string">"error"</span>])   <span style="color:#475569"># e.g. COMPANY_NOT_FOUND</span>
<span class="api-code-keyword">else</span>:
    company = res[<span class="api-code-string">"data"</span>]
    admins  = [a[<span class="api-code-string">"name"</span>] <span class="api-code-keyword">for</span> a <span class="api-code-keyword">in</span> company.get(<span class="api-code-string">"administrators"</span>, [])]
    active  = (company[<span class="api-code-string">"status"</span>] <span class="api-code-keyword">or</span> <span class="api-code-string">""</span>).upper() == <span class="api-code-string">"ACTIVA"</span>
    signer_ok = <span class="api-code-string">"JUAN PÉREZ GARCÍA"</span> <span class="api-code-keyword">in</span> admins
    <span class="api-code-keyword">print</span>(<span class="api-code-string">"Approved"</span> <span class="api-code-keyword">if</span> active <span class="api-code-keyword">and</span> signer_ok <span class="api-code-keyword">else</span> <span class="api-code-string">"Manual review"</span>)
</pre>
                    <pre id="code-php"
                        style="display:none;margin:0;padding:32px 36px;font-family:'Fira Code','Courier New',monospace;font-size:13.5px;line-height:2;color:#e2e8f0;overflow-x:auto;"><span class="api-code-keyword">$ch</span> = curl_init(<span class="api-code-string">"https://apiempresas.es/api/v1/companies?cif=B12345678&amp;admin=true"</span>);
curl_setopt_array(<span class="api-code-keyword">$ch</span>, [
    CURLOPT_HTTPHEADER     => [<span class="api-code-string">"X-API-KEY: your_api_key"</span>],
    CURLOPT_RETURNTRANSFER => <span class="api-code-keyword">true</span>,
]);
<span class="api-code-keyword">$res</span> = json_decode(curl_exec(<span class="api-code-keyword">$ch</span>), <span class="api-code-keyword">true</span>);

<span class="api-code-keyword">if</span> (!<span class="api-code-keyword">$res</span>[<span class="api-code-string">'success'</span>]) {
    <span class="api-code-keyword">echo</span> <span class="api-code-string">'Manual review: '</span> . <span class="api-code-keyword">$res</span>[<span class="api-code-string">'error'</span>];
} <span class="api-code-keyword">else</span> {
    <span class="api-code-keyword">$e</span>      = <span class="api-code-keyword">$res</span>[<span class="api-code-string">'data'</span>];
    <span class="api-code-keyword">$admins</span> = array_column(<span class="api-code-keyword">$e</span>[<span class="api-code-string">'administrators'</span>] ?? [], <span class="api-code-string">'name'</span>);
    <span class="api-code-keyword">$ok</span>     = strtoupper(<span class="api-code-keyword">$e</span>[<span class="api-code-string">'status'</span>] ?? <span class="api-code-string">''</span>) === <span class="api-code-string">'ACTIVA'</span> &amp;&amp; in_array(<span class="api-code-string">'JUAN PÉREZ GARCÍA'</span>, <span class="api-code-keyword">$admins</span>);
    <span class="api-code-keyword">echo</span> <span class="api-code-keyword">$ok</span> ? <span class="api-code-string">'Approved'</span> : <span class="api-code-string">'Manual review'</span>;
}
</pre>
                    <pre id="code-node"
                        style="display:none;margin:0;padding:32px 36px;font-family:'Fira Code','Courier New',monospace;font-size:13.5px;line-height:2;color:#e2e8f0;overflow-x:auto;"><span class="api-code-keyword">const</span> res = <span class="api-code-keyword">await</span> fetch(
  <span class="api-code-string">'https://apiempresas.es/api/v1/companies?cif=B12345678&amp;admin=true'</span>,
  { headers: { <span class="api-code-string">'X-API-KEY'</span>: <span class="api-code-string">'your_api_key'</span> } }
).then(r => r.json());

<span class="api-code-keyword">if</span> (!res.success) {
  console.log(<span class="api-code-string">'Manual review:'</span>, res.error);
} <span class="api-code-keyword">else</span> {
  <span class="api-code-keyword">const</span> { status, administrators = [] } = res.data;
  <span class="api-code-keyword">const</span> signerOk = administrators.some(a => a.name === <span class="api-code-string">'JUAN PÉREZ GARCÍA'</span>);
  console.log((status || <span class="api-code-string">''</span>).toUpperCase() === <span class="api-code-string">'ACTIVA'</span> &amp;&amp; signerOk ? <span class="api-code-string">'Approved'</span> : <span class="api-code-string">'Manual review'</span>);
}
</pre>
                </div>

                <!-- Feature pills below code -->
                <div style="display: flex; justify-content: center; gap: 32px; margin-top: 40px; flex-wrap: wrap;">
                    <div
                        style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:0.88rem;font-weight:700;">
                        <span
                            style="background:#eff6ff;color:#2563eb;padding:6px 8px;border-radius:8px;font-size:14px;">⚡</span>
                        JSON responses
                    </div>
                    <div
                        style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:0.88rem;font-weight:700;">
                        <span
                            style="background:#eff6ff;color:#2563eb;padding:6px 8px;border-radius:8px;font-size:14px;">🔐</span>
                        API key auth
                    </div>
                    <div
                        style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:0.88rem;font-weight:700;">
                        <span
                            style="background:#eff6ff;color:#2563eb;padding:6px 8px;border-radius:8px;font-size:14px;">📄</span>
                        Full docs
                    </div>
                    <div
                        style="display:flex;align-items:center;gap:8px;color:#64748b;font-size:0.88rem;font-weight:700;">
                        <span
                            style="background:#eff6ff;color:#2563eb;padding:6px 8px;border-radius:8px;font-size:14px;">📦</span>
                        SDKs: npm, pip and composer
                    </div>
                </div>
            </div>
        </section>

        <script>
            function switchTab(lang) {
                const names = { python: 'verify_company.py', php: 'verify_company.php', node: 'verify_company.js' };
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


        <!-- FAQ KYB: se pinta desde $kybFaqs, que genera también el JSON-LD FAQPage de abajo -->
        <?php
        $kybFaqs = [
            ['What is KYB and how is it different from KYC?',
             'KYB (Know Your Business) is the verification of a company before you work with it: that it exists, that it is still active, who runs it and, if your activity requires it, who its ultimate beneficial owner is. KYC (Know Your Customer) verifies individuals. The API covers the company side for Spain, with data from the BORME.'],
            ['Does the API help with anti-money laundering (AML) compliance?',
             'It helps you identify the legal entity and check its status and directors with BORME data, and leaves you the response as evidence. It does not replace your due diligence policy: it does not include beneficial ownership or sanctions and PEP lists, which you need to cover elsewhere.'],
            ['Can I verify a Spanish company tax ID (CIF/NIF) by API?',
             'Yes. Send the company NIF (formerly CIF, with or without hyphens) to /companies and you get its legal name and registry status. If no company has that tax ID, the API answers COMPANY_NOT_FOUND. Individuals\' tax IDs (sole traders) are not supported, for data protection reasons.'],
            ['How do I check that the signer is a director of the company?',
             'Add admin=true to the request (Pro plan or higher) and compare the signer\'s name with the list of current directors and officers. If they are not there, the usual step is to ask for a power of attorney or review the onboarding manually.'],
            ['Can I check the EU VAT number in VIES?',
             'Yes. The /companies/verify endpoint (Pro) checks in one call that the company exists and is active, that the name matches, that the signer is a current director and that the VAT number is registered in VIES, and returns pass, review or fail.'],
            ['How often is the data updated?',
             'We process the BORME every day: incorporations, director appointments and resignations, changes of address or capital, dissolutions, insolvencies and closures are added with each published issue.'],
            ['Can I re-check my whole customer portfolio at once?',
             'Yes. With /companies/batch you look up to 100 tax IDs in a single request (Pro plan or higher), and /companies/signals flags dissolutions, insolvencies and other changes. The Free plan allows 2 requests per second; Pro and Business, 20.'],
        ];
        ?>
        <section class="radar-section">
            <div class="container">
                <div class="radar-heading radar-heading--center">
                    <div class="radar-kicker">FAQ</div>
                    <h2 class="radar-title">KYB API: frequently asked questions</h2>
                    <p class="radar-subtitle">What teams usually ask before connecting company verification to their onboarding.</p>
                </div>

                <div class="api-faq">
                    <?php foreach ($kybFaqs as $i => [$q, $a]): ?>
                    <div class="api-faq-item<?= $i === 0 ? ' active' : '' ?>">
                        <button class="api-faq-question" onclick="this.parentElement.classList.toggle('active')">
                            <span><?= esc($q) ?></span>
                            <span class="api-faq-icon">+</span>
                        </button>
                        <div class="api-faq-answer">
                            <div class="api-faq-answer-inner"><?= esc($a) ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <script type="application/ld+json">
        <?= json_encode([
            '@context'   => 'https://schema.org',
            '@type'      => 'FAQPage',
            'mainEntity' => array_map(static fn($f) => [
                '@type'          => 'Question',
                'name'           => $f[0],
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $f[1]],
            ], $kybFaqs),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
        </script>

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
                                style="color: rgba(255,255,255,0.9); font-size: 11px; font-weight: 800; letter-spacing: 0.08em;">READY
                                TO START</span>
                        </div>

                        <!-- Headline -->
                        <h2
                            style="color: #ffffff; font-size: 2.75rem; font-weight: 950; margin: 0 0 16px; letter-spacing: -0.04em; line-height: 1.1;">
                            Automate your KYB checks for Spanish companies</h2>
                        <p
                            style="color: rgba(255,255,255,0.65); font-size: 1.1rem; font-weight: 500; margin: 0 0 48px; max-width: 480px; margin-left: auto; margin-right: auto; line-height: 1.6;">
                            Connect company verification to your customer and supplier onboarding. Start with <?= (int) $freeLimit ?> free requests.</p>

                        <!-- Stats row -->
                        <div
                            style="display: flex; justify-content: center; gap: 48px; margin-bottom: 52px; flex-wrap: wrap;">
                            <div>
                                <div
                                    style="color: #fff; font-size: 1.75rem; font-weight: 950; letter-spacing: -0.04em;">
                                    <?= !empty($ps['companies']) ? esc($fmtCorto((int) $ps['companies'])) : '+4 M' ?></div>
                                <div
                                    style="color: rgba(255,255,255,0.5); font-size: 0.8rem; font-weight: 700; letter-spacing: 0.05em; margin-top: 4px;">
                                    COMPANIES</div>
                            </div>
                            <div style="width: 1px; background: rgba(255,255,255,0.1);"></div>
                            <div>
                                <div
                                    style="color: #fff; font-size: 1.75rem; font-weight: 950; letter-spacing: -0.04em;">
                                    <?= (int) $freeLimit ?></div>
                                <div
                                    style="color: rgba(255,255,255,0.5); font-size: 0.8rem; font-weight: 700; letter-spacing: 0.05em; margin-top: 4px;">
                                    FREE REQUESTS</div>
                            </div>
                        </div>

                        <!-- CTA Button + disclaimer stacked -->
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 14px;">
                            <a href="<?= site_url('register?intent=api&plan=free&source=en_api_kyb_final') ?>"
                                style="display: inline-block; background: linear-gradient(135deg, #facc15 0%, #f97316 100%); color: #0b1020; font-weight: 900; font-size: 1.05rem; padding: 18px 48px; border-radius: 16px; text-decoration: none; box-shadow: 0 10px 30px -5px rgba(15,23,42,0.45); transition: all 0.3s ease; letter-spacing: 0.01em;">
                                Get your free API key
                            </a>
                            <span style="color: rgba(255,255,255,0.45); font-size: 0.82rem; font-weight: 600;">No
                                credit card required to get started</span>
                        </div>
                    </div>

                </div>
            </div>
        </section>


    </main>

    <?= view('partials/footer_en') ?>
    <script>
        // Sin jQuery (26-09-2026): la cabecera ya carga jQuery y SweetAlert2 una vez;
        // antes esta página los volvía a descargar de forma síncrona solo para esto.
        (function () {
            function post(url, data) {
                return fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new URLSearchParams(data).toString(),
                    credentials: 'same-origin'
                });
            }
            function trackEvent(type, metadata) {
                post('<?= site_url("api/tracking/event") ?>', {
                    event_type: type,
                    source: 'api_landing',
                    metadata: JSON.stringify(metadata || {})
                }).catch(function () {});
            }
            function aviso(opts, fallbackText) {
                if (window.Swal) { return Swal.fire(opts); }
                alert(fallbackText);
                return Promise.resolve();
            }

            document.addEventListener('DOMContentLoaded', function () {
                trackEvent('api_prices_view', { lang: 'en' });

                var form = document.getElementById('api_quick_unlock_form');
                if (!form) return;
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    var btn = form.querySelector('button');
                    var email = form.querySelector('input').value;
                    var reset = function () { btn.disabled = false; btn.textContent = 'Get a free API key'; };
                    btn.disabled = true;
                    btn.textContent = 'Generating...';

                    post('<?= site_url("api/quick-unlock") ?>', { email: email })
                        .then(function (r) { return r.json(); })
                        .then(function (res) {
                            if (res.status === 'success') {
                                trackEvent('api_quick_unlock_success', {});
                                aviso({
                                    title: 'API key created!',
                                    text: 'Your API key is: ' + res.api_key + '. We will take you to your dashboard, where your first request is already waiting.',
                                    icon: 'success',
                                    confirmButtonText: 'Go to my dashboard'
                                }, 'Your API key is: ' + res.api_key).then(function () { window.location.href = res.redirect; });
                            } else if (res.status === 'exists') {
                                window.location.href = res.redirect;
                            } else {
                                aviso({ title: 'Error', text: res.message || 'Could not create the key', icon: 'error' }, res.message || 'Could not create the key');
                                reset();
                            }
                        })
                        .catch(function () {
                            aviso({ title: 'Error', text: 'Connection error', icon: 'error' }, 'Connection error');
                            reset();
                        });
                });
            });
        })();
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

        <script>
            const jsonExamples = {
                get_companies: {
                    success: true,
                    data: {
                        cif: "B12345678",
                        name: "EMPRESA DE EJEMPLO SL",
                        status: "ACTIVA",
                        founded: "2019-03-12",
                        province: "MADRID",
                        municipality: "MADRID",
                        address: "CALLE DE EJEMPLO 42, MADRID",
                        cnae: "6201",
                        cnae_label: "Actividades de programación informática",
                        administrators: [
                            { name: "JUAN PÉREZ GARCÍA", position: "Administrador Único" }
                        ]
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
                        { name: "EMPRESA DE EJEMPLO SL", cif: "B12345678" },
                        { name: "OTRA EMPRESA DE EJEMPLO SA", cif: "A87654321" }
                    ],
                    meta: { requested: 2, found: 2, cost: 2, truncated: false }
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
                get_risk_profile: {
                    success: true,
                    data: {
                        cif: "B12345678",
                        company_name: "EMPRESA DE EJEMPLO SL",
                        risk_score: 62,
                        risk_level: "ALTO",
                        confidence_score: 49,
                        data_quality_score: 70,
                        summary_message: "Atención: Constan indicadores de elevado riesgo financiero o corporativo.",
                        legal_state: "REGISTRY_CLOSURE_GENERICO",
                        data_sources: {
                            borme_status: "CHECKED_WITH_RECORDS",
                            accounts_status: "KNOWN_DELAYED",
                            official_status: "KNOWN"
                        },
                        dimensions: {
                            legal_distress: 60,
                            filing_compliance: 0,
                            governance_volatility: 30,
                            capital_instability: 0,
                            structural_volatility: 0,
                            stabilizing_credit: 0
                        },
                        canonical_events: [
                            {
                                code: "LEGAL_STATE_REGISTRY_CLOSURE_GENERICO",
                                dimension: "legal_distress",
                                severity: "high",
                                description: "Consta publicación registral de cierre sin especificación de causa.",
                                event_date: "2026-08-24",
                                classification_confidence: "LOW"
                            }
                        ],
                        model_version: "2.0.0",
                        calculated_at: "2026-08-24T00:00:00Z"
                    }
                },
                post_webhook: {
                    success: true,
                    message: "Webhook creado correctamente",
                    id: 789
                },
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
                    get_contracts: 'GET /companies/contracts',
                    get_risk_profile: 'GET /companies/risk-profile',
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
            let isAnnual = false; // arranca en mensual: es lo que se compra y evita la sorpresa del cobro anual
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
                    document.querySelectorAll('.api-annual-note').forEach(function (n) { n.style.display = ''; });
                } else {
                    knob.style.transform = 'translateX(0px)';
                    labelMonthly.style.color = '#2563eb';
                    labelMonthly.style.fontWeight = '800';
                    labelAnnual.style.color = '#94a3b8';
                    labelAnnual.style.fontWeight = '600';
                    
                    pricePro.textContent = pricePro.dataset.monthly;
                    priceBusiness.textContent = priceBusiness.dataset.monthly;
                    document.querySelectorAll('.api-annual-note').forEach(function (n) { n.style.display = 'none'; });
                }

                // Los botones de pago llevan el periodo que el usuario está viendo
                document.querySelectorAll('a[href*="register?intent=api&plan=pro"], a[href*="register?intent=api&plan=business"]').forEach(function (a) {
                    var u = new URL(a.href, window.location.origin);
                    u.searchParams.set('period', isAnnual ? 'annual' : 'monthly');
                    a.href = u.toString();
                });
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