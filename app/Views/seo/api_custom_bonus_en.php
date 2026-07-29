<!doctype html>
<html lang="en">

<head>
    <?= view('partials/head', [
        'title' => 'Create your Custom API Credits Bonus | APIEmpresas.es',
        'excerptText' => 'Buy a credit package with no monthly subscription and use them at your own pace with no expiration date. Automatic volume discounts.',
        'canonical' => site_url('buy-api-credits'),
        'robots' => 'index,follow',
    ]) ?>
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
        .api-hero-title {
            font-size: clamp(2.2rem, 4vw, 3.4rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #fff;
            line-height: 1.1;
            margin-bottom: 1.25rem;
        }
        .api-hero-title span {
            background: linear-gradient(135deg, #34d399 0%, #10b981 100%);
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
        
        .wizard-container {
            max-width: 800px;
            margin: -40px auto 100px;
            background: #ffffff;
            border-radius: 32px;
            box-shadow: 0 40px 100px -20px rgba(15, 23, 42, 0.15);
            border: 1px solid #e2e8f0;
            position: relative;
            z-index: 10;
            padding: 48px;
            text-align: left;
        }
        
        @media (max-width: 768px) {
            .wizard-container {
                margin-top: -20px;
                padding: 32px 24px;
                border-radius: 24px;
            }
        }

        .slider-wrapper {
            margin: 48px 0;
        }

        .slider-container {
            position: relative;
            height: 32px;
            display: flex;
            align-items: center;
            margin-bottom: 12px;
        }

        /* Custom Range Slider Styling */
        input[type=range] {
            -webkit-appearance: none;
            width: 100%;
            background: transparent;
            margin: 0;
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            z-index: 3;
        }
        input[type=range]:focus {
            outline: none;
        }
        input[type=range]::-webkit-slider-runnable-track {
            width: 100%;
            height: 12px;
            cursor: pointer;
            background: #e2e8f0;
            border-radius: 99px;
            border: none;
        }
        .slider-progress {
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            height: 12px;
            background: linear-gradient(90deg, #3b82f6, #10b981);
            border-radius: 99px;
            z-index: 2;
            pointer-events: none;
        }
        input[type=range]::-webkit-slider-thumb {
            height: 32px;
            width: 32px;
            border-radius: 50%;
            background: #ffffff;
            cursor: pointer;
            -webkit-appearance: none;
            margin-top: -10px; /* Centra el thumb de 32px en el track de 12px */
            box-shadow: 0 4px 10px rgba(0,0,0,0.15), 0 0 0 4px rgba(59,130,246,0.1);
            border: 2px solid #3b82f6;
            transition: transform 0.1s;
        }
        input[type=range]::-webkit-slider-thumb:active {
            transform: scale(1.1);
            box-shadow: 0 4px 10px rgba(0,0,0,0.2), 0 0 0 6px rgba(59,130,246,0.2);
        }

        .credits-display {
            text-align: center;
            margin-bottom: 24px;
        }
        .credits-number {
            font-size: 4rem;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.04em;
            line-height: 1;
            font-variant-numeric: tabular-nums;
        }
        .credits-label {
            font-size: 1.1rem;
            color: #64748b;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .pricing-summary {
            background: #f8fafc;
            border-radius: 20px;
            padding: 32px;
            border: 1px dashed #cbd5e1;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 24px;
        }

        .price-final {
            font-size: 3.5rem;
            font-weight: 900;
            color: #10b981;
            letter-spacing: -0.04em;
            line-height: 1;
        }
        .price-final span {
            font-size: 1.2rem;
            color: #94a3b8;
            font-weight: 700;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
            margin-top: 32px;
        }
        .stat-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            padding: 20px;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);
        }
        .stat-card-title {
            font-size: 0.85rem;
            color: #94a3b8;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }
        .stat-card-value {
            font-size: 1.5rem;
            font-weight: 800;
            color: #0f172a;
            font-variant-numeric: tabular-nums;
        }
        .stat-card-desc {
            font-size: 0.85rem;
            color: #64748b;
            margin-top: 4px;
        }

        .buy-btn {
            display: block;
            width: 100%;
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: #ffffff;
            text-align: center;
            padding: 20px;
            border-radius: 16px;
            font-size: 1.25rem;
            font-weight: 800;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.4);
            transition: all 0.3s ease;
            margin-top: 32px;
        }
        .buy-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px -5px rgba(16, 185, 129, 0.5);
        }

        /* Scale marks */
        .scale-marks {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
            padding: 0 16px;
            color: #94a3b8;
            font-size: 0.8rem;
            font-weight: 700;
        }

        /* ── CRO Sections ── */
        .cro-sections {
            max-width: 900px;
            margin: 0 auto 80px;
            padding: 0 20px;
        }
        .cro-section {
            margin-bottom: 64px;
        }
        .cro-title {
            text-align: center;
            font-size: 1.8rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 32px;
            letter-spacing: -0.03em;
        }
        
        /* Grid */
        .cro-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 24px;
        }
        .cro-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 32px 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);
            text-align: center;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .cro-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px -15px rgba(0,0,0,0.1);
        }
        .cro-icon {
            font-size: 2.5rem;
            margin-bottom: 16px;
        }
        .cro-card-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: #0f172a;
            margin-bottom: 12px;
        }
        .cro-card-desc {
            font-size: 0.9rem;
            color: #64748b;
            line-height: 1.5;
            margin: 0;
        }

        /* Costs */
        .cro-table-wrapper {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 24px;
        }
        .cro-cost-card {
            background: #ffffff;
            border-radius: 24px;
            padding: 32px;
            border: 1px solid #e2e8f0;
        }
        .cro-cost-card.advanced {
            background: linear-gradient(135deg, #f0fdf4, #ffffff);
            border-color: #bbf7d0;
        }
        .cro-cost-header {
            font-size: 2rem;
            font-weight: 900;
            color: #3b82f6;
            margin-bottom: 4px;
        }
        .cro-cost-card.advanced .cro-cost-header {
            color: #10b981;
        }
        .cro-cost-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 24px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .cro-cost-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .cro-cost-list li {
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            color: #475569;
            font-size: 0.95rem;
            display: flex;
            align-items: center;
        }
        .cro-cost-list li:last-child {
            border-bottom: none;
        }
        .cro-cost-list li::before {
            content: '✓';
            color: #3b82f6;
            font-weight: bold;
            margin-right: 12px;
        }
        .cro-cost-card.advanced .cro-cost-list li::before {
            color: #10b981;
        }

        /* FAQs */
        .cro-faqs {
            max-width: 700px;
            margin: 0 auto;
        }
        .cro-faq {
            background: #ffffff;
            border-radius: 16px;
            margin-bottom: 16px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            transition: all 0.3s;
        }
        .cro-faq-q {
            padding: 20px 24px;
            font-weight: 700;
            color: #0f172a;
            cursor: pointer;
            list-style: none;
            position: relative;
        }
        .cro-faq-q::-webkit-details-marker {
            display: none;
        }
        .cro-faq-q::after {
            content: '+';
            position: absolute;
            right: 24px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 1.5rem;
            color: #94a3b8;
            font-weight: 300;
        }
        .cro-faq[open] .cro-faq-q::after {
            content: '−';
        }
        .cro-faq-a {
            padding: 0 24px 24px;
            color: #64748b;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        .cro-endpoint-card {
            background: #ffffff;
            border-radius: 16px;
            padding: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            margin-bottom: 16px;
            transition: all 0.2s;
        }
        .cro-endpoint-card:hover {
            border-color: #cbd5e1;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.05);
        }
        .cro-endpoint-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 12px;
            flex-wrap: wrap;
            gap: 12px;
        }
        .cro-endpoint-method {
            background: #dbeafe;
            color: #1d4ed8;
            font-weight: 800;
            font-size: 0.8rem;
            padding: 4px 8px;
            border-radius: 6px;
            font-family: 'Fira Code', monospace;
            margin-right: 8px;
        }
        .cro-endpoint-path {
            font-family: 'Fira Code', monospace;
            font-size: 0.95rem;
            color: #0f172a;
            font-weight: 700;
        }
        .cro-endpoint-cost {
            font-size: 0.85rem;
            font-weight: 700;
            color: #10b981;
            background: #dcfce7;
            padding: 6px 12px;
            border-radius: 99px;
            display: flex;
            align-items: center;
            gap: 6px;
            white-space: nowrap;
        }
        .cro-endpoint-desc {
            color: #64748b;
            font-size: 0.9rem;
            line-height: 1.6;
            margin: 0;
        }
    </style>
</head>

<body style="background: #f1f5f9;">
    <?= view('partials/header_en') ?>

    <!-- HERO SECTION -->
    <header class="api-unified-hero">
        <div class="container" style="max-width:900px; margin:0 auto; padding:0 2rem;">
            <div class="api-hero-badge">One-time Payment · No expiration</div>
            <h1 class="api-hero-title">
                Create your own Bonus<br>
                <span>Custom Prepaid Credits</span>
            </h1>
            <p class="api-hero-sub">
                Pay only for what you consume. Ideal for one-off projects, data migrations, or integrations without a monthly commitment. Credits never expire.
            </p>
        </div>
    </header>

    <!-- WIZARD -->
    <div class="container">
        <div class="wizard-container">
            
            <div class="credits-display">
                <div class="credits-number" id="creditsOutput">50.000</div>
                <div class="credits-label">API Credits</div>
            </div>

            <div class="slider-wrapper">
                <div class="slider-container">
                    <div class="slider-progress" id="sliderProgress"></div>
                    <input type="range" id="creditsSlider" min="10000" max="1000000" step="10000" value="50000">
                </div>
                <div class="scale-marks">
                    <span>10k</span>
                    <span>250k</span>
                    <span>500k</span>
                    <span>750k</span>
                    <span>1M</span>
                </div>
            </div>

            <div class="pricing-summary">
                <div>
                    <h3 style="margin:0 0 4px; color:#0f172a; font-size:1.2rem; font-weight:800;">One-time Payment</h3>
                    <p style="margin:0; color:#64748b; font-size:0.95rem;">Final price with applied volume discount.</p>
                </div>
                <div style="text-align: right;">
                    <div class="price-final" id="priceOutput">199<span>€</span></div>
                    <div style="font-size: 0.85rem; color: #10b981; font-weight: 800; background: #dcfce7; display: inline-block; padding: 4px 10px; border-radius: 99px; margin-top: 6px;" id="pricePerCallOutput">0,0039€ / credit</div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-title">Basic Searches</div>
                    <div class="stat-card-value" id="basicCallsOutput">50.000</div>
                    <div class="stat-card-desc">Generic endpoints (1 credit)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-title">Advanced Calls (IA/Pro)</div>
                    <div class="stat-card-value" id="advancedCallsOutput">16.666</div>
                    <div class="stat-card-desc">Complex endpoints (3 credits)</div>
                </div>
            </div>

            <form action="<?= site_url('billing/checkout_bonus') ?>" method="POST" id="bonusForm">
                <?= csrf_field() ?>
                <input type="hidden" name="credits" id="inputCredits" value="50000">
                <button type="submit" class="buy-btn">
                    Buy Bonus Now
                </button>
                <p style="text-align:center; font-size:0.85rem; color:#94a3b8; margin-top:16px;">100% secure payment encrypted by Stripe. You will receive your invoice instantly.</p>
            </form>

        </div> <!-- End of wizard-container -->

        <!-- CRO Sections -->
        <div class="cro-sections">
            <!-- 1. Use Cases Grid -->
            <div class="cro-section">
                <h2 class="cro-title">What can you do with your credits?</h2>
                <div class="cro-grid">
                    <div class="cro-card">
                        <div class="cro-icon">⚡</div>
                        <h3 class="cro-card-title">B2B Enrichment</h3>
                        <p class="cro-card-desc">Autocomplete company data in your CRM or ERP by simply entering the CIF or name.</p>
                    </div>
                    <div class="cro-card">
                        <div class="cro-icon">🛡️</div>
                        <h3 class="cro-card-title">Automatic Onboarding</h3>
                        <p class="cro-card-desc">Validate corporate identity and verify corporate roles (KYB) in milliseconds.</p>
                    </div>
                    <div class="cro-card">
                        <div class="cro-icon">🧠</div>
                        <h3 class="cro-card-title">Scoring and Commercial AI</h3>
                        <p class="cro-card-desc">Calculate risk or detect growing companies (gazelles) before closing a sale.</p>
                    </div>
                </div>
                
                <!-- Trust Badges -->
                <div style="text-align: center; margin-top: 40px; opacity: 0.6;">
                    <span style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 16px;">Compatible with any tech stack</span>
                    <div style="display: flex; justify-content: center; gap: 32px; flex-wrap: wrap; font-weight: 800; font-size: 1.1rem; color: #94a3b8; filter: grayscale(100%);">
                        <span>PHP</span>
                        <span>Node.js</span>
                        <span>Python</span>
                        <span>Make</span>
                        <span>Zapier</span>
                        <span>Salesforce</span>
                    </div>
                </div>
            </div>

            <!-- 2. Guarantees -->
            <div class="cro-section" style="background: #ffffff; padding: 40px; border-radius: 24px; border: 1px solid #e2e8f0; margin-bottom: 48px; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);">
                <h2 style="font-size: 1.5rem; font-weight: 850; margin-top: 0; margin-bottom: 32px; text-align: center; color: #0f172a;">Quality and Performance Guarantee</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 32px;">
                    <div>
                        <div style="color: #2563eb; font-weight: 800; font-size: 1.1rem; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            Official Sources
                        </div>
                        <p style="color: #64748b; font-size: 0.95rem; margin: 0; line-height: 1.6;">All data comes from the Central Mercantile Register and the BORME. Updated daily first thing in the morning for maximum reliability.</p>
                    </div>
                    <div>
                        <div style="color: #2563eb; font-weight: 800; font-size: 1.1rem; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            99.9% Uptime
                        </div>
                        <p style="color: #64748b; font-size: 0.95rem; margin: 0; line-height: 1.6;">Our scalable cloud infrastructure ensures latencies below 50ms for direct CIF/NIF validations.</p>
                    </div>
                    <div>
                        <div style="color: #2563eb; font-weight: 800; font-size: 1.1rem; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            Privacy and Security
                        </div>
                        <p style="color: #64748b; font-size: 0.95rem; margin: 0; line-height: 1.6;">TLS 1.3 encrypted connections. We do not store or track private or customer data sent in your validation requests.</p>
                    </div>
                </div>
            </div>

            <!-- 3. Endpoints y Costes -->
            <div class="cro-section">
                <h2 class="cro-title">Endpoints and Costs Catalog</h2>
                <p style="text-align:center; color:#64748b; margin-top:-16px; margin-bottom:32px; font-size:1.05rem; max-width: 650px; margin-left: auto; margin-right: auto;">
                    With a single API Key you get access to <strong>our entire REST API</strong>. The cost is automatically deducted from your wallet only for each successful request (HTTP 200 OK). Failed or test calls in our Sandbox <strong>do not consume credits</strong>.
                </p>

                <div class="cro-endpoint-card">
                    <div class="cro-endpoint-header">
                        <div>
                            <span class="cro-endpoint-method">GET</span>
                            <span class="cro-endpoint-path">/api/v1/companies</span>
                        </div>
                        <div class="cro-endpoint-cost"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg> 1 Credit</div>
                    </div>
                    <p class="cro-endpoint-desc"><strong>CIF Validation and Enrichment:</strong> Returns complete Mercantile Register data (Company Name, Province, Municipality, Status, CNAE Sector, Social Capital, Incorporation Date). Ideal for database cleansing.</p>
                </div>

                <div class="cro-endpoint-card">
                    <div class="cro-endpoint-header">
                        <div>
                            <span class="cro-endpoint-method">GET</span>
                            <span class="cro-endpoint-path">/api/v1/companies/search</span>
                        </div>
                        <div class="cro-endpoint-cost"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg> 1 Credit</div>
                    </div>
                    <p class="cro-endpoint-desc"><strong>Smart Search:</strong> Allows searching companies by partial text (name or company name) using fuzzy search. Designed to implement real-time autocompletes in your registration forms.</p>
                </div>

                <div class="cro-endpoint-card">
                    <div class="cro-endpoint-header">
                        <div>
                            <span class="cro-endpoint-method">GET</span>
                            <span class="cro-endpoint-path">/api/v1/companies/score</span>
                        </div>
                        <div class="cro-endpoint-cost" style="background: #fef3c7; color: #d97706;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg> 3 Credits</div>
                    </div>
                    <p class="cro-endpoint-desc"><strong>Predictive Scoring with Artificial Intelligence:</strong> Analyzes and evaluates the commercial risk profile and purchasing potential of the company on a scale from 0 to 100 to prioritize sales efforts.</p>
                </div>

                <div class="cro-endpoint-card">
                    <div class="cro-endpoint-header">
                        <div>
                            <span class="cro-endpoint-method">GET</span>
                            <span class="cro-endpoint-path">/api/v1/companies/radar</span>
                        </div>
                        <div class="cro-endpoint-cost" style="background: #fef3c7; color: #d97706;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg> 3 Credits</div>
                    </div>
                    <p class="cro-endpoint-desc"><strong>Mass Extraction of New Companies:</strong> Retrieves massive batches of companies incorporated "today", "yesterday" or in the last week, with options to filter by province or sector. Designed for mass prospecting (Cold Email/Calls).</p>
                </div>

                <div class="cro-endpoint-card">
                    <div class="cro-endpoint-header">
                        <div>
                            <span class="cro-endpoint-method">GET</span>
                            <span class="cro-endpoint-path">/api/v1/companies/signals</span>
                        </div>
                        <div class="cro-endpoint-cost" style="background: #fef3c7; color: #d97706;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg> 3 Credits</div>
                    </div>
                    <p class="cro-endpoint-desc"><strong>Corporate Signals and BORME:</strong> Get a structured list of the latest official registry acts (capital increases, bankruptcies, appointments, or dismissals of officers) for a specific company.</p>
                </div>
            </div>

            <!-- 4. FAQs -->
            <div class="cro-section">
                <h2 class="cro-title">Frequently Asked Questions</h2>
                <div class="cro-faqs">
                    <details class="cro-faq">
                        <summary class="cro-faq-q">Do credits ever expire?</summary>
                        <div class="cro-faq-a">No, they never expire. You can buy a bonus today and consume the credits at your own pace over months or years without any rush. This balance remains permanently linked to your account.</div>
                    </details>
                    <details class="cro-faq">
                        <summary class="cro-faq-q">Are credits consumed if the API returns an error (e.g. 404)?</summary>
                        <div class="cro-faq-a"><strong>No.</strong> We only deduct credits from your wallet when the request is 100% successful and returns a <code>200 OK</code> code. If you search for a company that does not exist, make a format error, or use our Sandbox API, you will not consume any credits.</div>
                    </details>
                    <details class="cro-faq">
                        <summary class="cro-faq-q">Is it easy to integrate with my tech stack?</summary>
                        <div class="cro-faq-a">Absolutely. We use standard REST architecture with lightning-fast JSON responses. You will have full documentation (Swagger / OpenAPI), ready-to-copy examples in PHP, Python, cURL, and NodeJS, and direct technical support to answer any questions in less than 24 hours.</div>
                    </details>
                    <details class="cro-faq">
                        <summary class="cro-faq-q">Can I switch to a monthly plan later?</summary>
                        <div class="cro-faq-a">Yes, absolutely. If you find your usage becomes recurring or very predictable month by month, you can subscribe to the Pro or Business plans from your dashboard. The balance you had in your bonus is not lost, it is added as a backup to the monthly balance.</div>
                    </details>
                    <details class="cro-faq">
                        <summary class="cro-faq-q">How do I get my invoice with broken-down VAT?</summary>
                        <div class="cro-faq-a">Instantly after payment. We use Stripe as a B2B payment gateway. You will receive in your email the complete official invoice with the tax data (Company name, CIF, Address) that you can enter during the payment process on the next screen.</div>
                    </details>
                </div>
            </div>

            <!-- 4. Final CTA -->
            <div style="text-align: center; margin-top: 48px; margin-bottom: 32px;">
                <button type="button" onclick="window.scrollTo({top: 0, behavior: 'smooth'});" class="buy-btn" style="width: auto; padding: 18px 64px; border-radius: 99px; display: inline-block; margin-top: 0;">
                    Configure my Bonus Now
                </button>
            </div>
        </div>
    </div>

    <?= view('partials/footer_en') ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const slider = document.getElementById('creditsSlider');
            const progress = document.getElementById('sliderProgress');
            const creditsOutput = document.getElementById('creditsOutput');
            const priceOutput = document.getElementById('priceOutput');
            const basicOutput = document.getElementById('basicCallsOutput');
            const advancedOutput = document.getElementById('advancedCallsOutput');
            const inputCredits = document.getElementById('inputCredits');
            const pricePerCallOutput = document.getElementById('pricePerCallOutput');

            // Pricing logic matching the backend algorithm
            function calculatePrice(credits) {
                const tiers = [
                    { qty: 10000, price: 49 },
                    { qty: 50000, price: 199 },
                    { qty: 100000, price: 349 },
                    { qty: 500000, price: 999 },
                    { qty: 1000000, price: 1499 }
                ];

                if (credits <= tiers[0].qty) return tiers[0].price;
                if (credits >= tiers[4].qty) return tiers[4].price;

                for (let i = 0; i < tiers.length - 1; i++) {
                    if (credits >= tiers[i].qty && credits <= tiers[i+1].qty) {
                        const range = tiers[i+1].qty - tiers[i].qty;
                        const priceRange = tiers[i+1].price - tiers[i].price;
                        const progress = (credits - tiers[i].qty) / range;
                        return Math.round(tiers[i].price + (progress * priceRange));
                    }
                }
                return 49;
            }

            function updateUI() {
                const val = parseInt(slider.value);
                const min = parseInt(slider.min);
                const max = parseInt(slider.max);
                
                // Update progress bar
                const percent = ((val - min) / (max - min)) * 100;
                progress.style.width = percent + '%';

                // Update text
                creditsOutput.textContent = val.toLocaleString('es-ES');
                
                const price = calculatePrice(val);
                priceOutput.innerHTML = price.toLocaleString('es-ES') + '<span>€</span><span style="font-size: 1.2rem; margin-left: 8px; font-weight: 700; color: #94a3b8;">+ IVA</span>';

                // Calculate price per credit
                const pricePerCredit = price / val;
                pricePerCallOutput.textContent = pricePerCredit.toLocaleString('es-ES', { minimumFractionDigits: 4, maximumFractionDigits: 4 }) + '€ / credit';

                basicOutput.textContent = val.toLocaleString('es-ES');
                advancedOutput.textContent = Math.floor(val / 3).toLocaleString('es-ES');

                // Update form input
                inputCredits.value = val;
            }

            slider.addEventListener('input', updateUI);
            updateUI(); // Init
        });
    </script>
</body>
</html>
