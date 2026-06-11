<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head', [
        'title' => 'Crea tu Bono Personalizado de Créditos API | APIEmpresas.es',
        'excerptText' => 'Compra un paquete de créditos sin suscripción mensual y úsalos a tu ritmo sin fecha de caducidad. Descuentos automáticos por volumen.',
        'canonical' => site_url('crear-bono-api'),
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
    <?= view('partials/header') ?>

    <!-- HERO SECTION -->
    <header class="api-unified-hero">
        <div class="container" style="max-width:900px; margin:0 auto; padding:0 2rem;">
            <div class="api-hero-badge">Pago Único · Sin caducidad</div>
            <h1 class="api-hero-title">
                Crea tu propio Bono<br>
                <span>Créditos Prepago a Medida</span>
            </h1>
            <p class="api-hero-sub">
                Paga solo por lo que consumes. Ideal para proyectos puntuales, migraciones de datos o integración sin compromiso mensual. Los créditos nunca caducan.
            </p>
        </div>
    </header>

    <!-- WIZARD -->
    <div class="container">
        <div class="wizard-container">
            
            <div class="credits-display">
                <div class="credits-number" id="creditsOutput">50.000</div>
                <div class="credits-label">Créditos de la API</div>
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
                    <h3 style="margin:0 0 4px; color:#0f172a; font-size:1.2rem; font-weight:800;">Pago Único</h3>
                    <p style="margin:0; color:#64748b; font-size:0.95rem;">Precio final con descuento por volumen aplicado.</p>
                </div>
                <div style="text-align: right;">
                    <div class="price-final" id="priceOutput">199<span>€</span></div>
                    <div style="font-size: 0.85rem; color: #10b981; font-weight: 800; background: #dcfce7; display: inline-block; padding: 4px 10px; border-radius: 99px; margin-top: 6px;" id="pricePerCallOutput">0,0039€ / crédito</div>
                </div>
            </div>

            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-card-title">Búsquedas Básicas</div>
                    <div class="stat-card-value" id="basicCallsOutput">50.000</div>
                    <div class="stat-card-desc">Endpoints genéricos (1 crédito)</div>
                </div>
                <div class="stat-card">
                    <div class="stat-card-title">Llamadas Avanzadas (IA/Pro)</div>
                    <div class="stat-card-value" id="advancedCallsOutput">16.666</div>
                    <div class="stat-card-desc">Endpoints complejos (3 créditos)</div>
                </div>
            </div>

            <form action="<?= site_url('billing/checkout_bonus') ?>" method="POST" id="bonusForm">
                <?= csrf_field() ?>
                <input type="hidden" name="credits" id="inputCredits" value="50000">
                <button type="submit" class="buy-btn">
                    Comprar Bono Ahora
                </button>
                <p style="text-align:center; font-size:0.85rem; color:#94a3b8; margin-top:16px;">Pago 100% seguro cifrado por Stripe. Recibirás tu factura al instante.</p>
            </form>

        </div> <!-- End of wizard-container -->

        <!-- CRO Sections -->
        <div class="cro-sections">
            <!-- 1. Use Cases Grid -->
            <div class="cro-section">
                <h2 class="cro-title">¿Qué puedes hacer con tus créditos?</h2>
                <div class="cro-grid">
                    <div class="cro-card">
                        <div class="cro-icon">⚡</div>
                        <h3 class="cro-card-title">Enriquecimiento B2B</h3>
                        <p class="cro-card-desc">Autocompleta datos de empresas en tu CRM o ERP introduciendo solo el CIF o el nombre.</p>
                    </div>
                    <div class="cro-card">
                        <div class="cro-icon">🛡️</div>
                        <h3 class="cro-card-title">Onboarding Automático</h3>
                        <p class="cro-card-desc">Valida identidad corporativa y verifica cargos societarios (KYB) en milisegundos.</p>
                    </div>
                    <div class="cro-card">
                        <div class="cro-icon">🧠</div>
                        <h3 class="cro-card-title">Scoring y IA Comercial</h3>
                        <p class="cro-card-desc">Calcula el riesgo o detecta empresas en crecimiento (gacelas) antes de cerrar una venta.</p>
                    </div>
                </div>
                
                <!-- Trust Badges -->
                <div style="text-align: center; margin-top: 40px; opacity: 0.6;">
                    <span style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; display: block; margin-bottom: 16px;">Compatible con cualquier stack tecnológico</span>
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
                <h2 style="font-size: 1.5rem; font-weight: 850; margin-top: 0; margin-bottom: 32px; text-align: center; color: #0f172a;">Garantía de Calidad y Rendimiento</h2>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 32px;">
                    <div>
                        <div style="color: #2563eb; font-weight: 800; font-size: 1.1rem; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                            Fuentes Oficiales
                        </div>
                        <p style="color: #64748b; font-size: 0.95rem; margin: 0; line-height: 1.6;">Todos los datos provienen del Registro Mercantil Central y el BORME. Se actualizan diariamente a primera hora de la mañana para máxima fiabilidad.</p>
                    </div>
                    <div>
                        <div style="color: #2563eb; font-weight: 800; font-size: 1.1rem; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            Disponibilidad 99.9%
                        </div>
                        <p style="color: #64748b; font-size: 0.95rem; margin: 0; line-height: 1.6;">Nuestra infraestructura escalable en la nube garantiza latencias por debajo de 50ms para las validaciones directas por CIF/NIF.</p>
                    </div>
                    <div>
                        <div style="color: #2563eb; font-weight: 800; font-size: 1.1rem; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            Privacidad y Seguridad
                        </div>
                        <p style="color: #64748b; font-size: 0.95rem; margin: 0; line-height: 1.6;">Conexiones cifradas TLS 1.3. No almacenamos ni trazamos los datos privados o de clientes que envías en tus peticiones de validación.</p>
                    </div>
                </div>
            </div>

            <!-- 3. Endpoints y Costes -->
            <div class="cro-section">
                <h2 class="cro-title">Catálogo de Endpoints y Costes</h2>
                <p style="text-align:center; color:#64748b; margin-top:-16px; margin-bottom:32px; font-size:1.05rem; max-width: 650px; margin-left: auto; margin-right: auto;">
                    Con una sola API Key obtienes acceso a <strong>toda nuestra API REST</strong>. El coste se descuenta de tu monedero de forma automática únicamente con cada petición exitosa (HTTP 200 OK). Las llamadas erróneas o de prueba en nuestro Sandbox <strong>no consumen créditos</strong>.
                </p>

                <div class="cro-endpoint-card">
                    <div class="cro-endpoint-header">
                        <div>
                            <span class="cro-endpoint-method">GET</span>
                            <span class="cro-endpoint-path">/api/v1/companies</span>
                        </div>
                        <div class="cro-endpoint-cost"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg> 1 Crédito</div>
                    </div>
                    <p class="cro-endpoint-desc"><strong>Validación y Enriquecimiento CIF:</strong> Devuelve datos completos del Registro Mercantil (Razón Social, Provincia, Municipio, Estado, Sector CNAE, Capital Social, Fecha de constitución). Ideal para limpieza de BBDD.</p>
                </div>

                <div class="cro-endpoint-card">
                    <div class="cro-endpoint-header">
                        <div>
                            <span class="cro-endpoint-method">GET</span>
                            <span class="cro-endpoint-path">/api/v1/companies/search</span>
                        </div>
                        <div class="cro-endpoint-cost"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg> 1 Crédito</div>
                    </div>
                    <p class="cro-endpoint-desc"><strong>Buscador Inteligente:</strong> Permite buscar sociedades por texto parcial (nombre o razón social) usando búsqueda difusa. Pensado para implementar autocompletados en tiempo real en tus formularios de registro.</p>
                </div>

                <div class="cro-endpoint-card">
                    <div class="cro-endpoint-header">
                        <div>
                            <span class="cro-endpoint-method">GET</span>
                            <span class="cro-endpoint-path">/api/v1/companies/score</span>
                        </div>
                        <div class="cro-endpoint-cost" style="background: #fef3c7; color: #d97706;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg> 3 Créditos</div>
                    </div>
                    <p class="cro-endpoint-desc"><strong>Scoring Predictivo con Inteligencia Artificial:</strong> Analiza y evalúa el perfil de riesgo comercial y el potencial de compra de la empresa en una escala del 0 al 100 para priorizar el esfuerzo comercial.</p>
                </div>

                <div class="cro-endpoint-card">
                    <div class="cro-endpoint-header">
                        <div>
                            <span class="cro-endpoint-method">GET</span>
                            <span class="cro-endpoint-path">/api/v1/companies/radar</span>
                        </div>
                        <div class="cro-endpoint-cost" style="background: #fef3c7; color: #d97706;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg> 3 Créditos</div>
                    </div>
                    <p class="cro-endpoint-desc"><strong>Extracción Masiva de Nuevas Empresas:</strong> Recupera lotes masivos de sociedades constituidas "hoy", "ayer" o en la última semana, con opción de filtro por provincia o sector. Diseñado para prospección masiva (Cold Email/Calls).</p>
                </div>

                <div class="cro-endpoint-card">
                    <div class="cro-endpoint-header">
                        <div>
                            <span class="cro-endpoint-method">GET</span>
                            <span class="cro-endpoint-path">/api/v1/companies/signals</span>
                        </div>
                        <div class="cro-endpoint-cost" style="background: #fef3c7; color: #d97706;"><svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 2 7 12 12 22 7 12 2"></polygon><polyline points="2 17 12 22 22 17"></polyline><polyline points="2 12 12 17 22 12"></polyline></svg> 3 Créditos</div>
                    </div>
                    <p class="cro-endpoint-desc"><strong>Señales Societarias y BORME:</strong> Obtén de forma estructurada el listado de los últimos actos registrales oficiales (ampliaciones de capital, concursos de acreedores, nombramientos o ceses de cargos) de una sociedad concreta.</p>
                </div>
            </div>

            <!-- 4. FAQs -->
            <div class="cro-section">
                <h2 class="cro-title">Preguntas Frecuentes</h2>
                <div class="cro-faqs">
                    <details class="cro-faq">
                        <summary class="cro-faq-q">¿Los créditos caducan en algún momento?</summary>
                        <div class="cro-faq-a">No, nunca caducan. Puedes comprar un bono hoy y consumir los créditos a tu ritmo durante meses o años sin ningún tipo de prisa. Este saldo queda permanentemente ligado a tu cuenta.</div>
                    </details>
                    <details class="cro-faq">
                        <summary class="cro-faq-q">¿Se consumen créditos si la API devuelve un error (ej. 404)?</summary>
                        <div class="cro-faq-a"><strong>No.</strong> Solo deducimos créditos de tu monedero cuando la petición es 100% exitosa y devuelve un código <code>200 OK</code>. Si buscas una empresa que no existe, te equivocas en el formato o usas nuestra API de Sandbox (pruebas), no consumirás ningún crédito.</div>
                    </details>
                    <details class="cro-faq">
                        <summary class="cro-faq-q">¿Es fácil integrarlo con mi stack tecnológico?</summary>
                        <div class="cro-faq-a">Completamente. Usamos una arquitectura REST estándar con respuestas JSON ultra rápidas. Dispondrás de documentación completa (Swagger / OpenAPI), ejemplos copiables en PHP, Python, cURL y NodeJS, y soporte técnico directo para resolver cualquier duda en menos de 24 horas.</div>
                    </details>
                    <details class="cro-faq">
                        <summary class="cro-faq-q">¿Puedo pasarme luego a un plan mensual?</summary>
                        <div class="cro-faq-a">Sí, totalmente. Si ves que tu consumo se vuelve recurrente o muy predecible mes a mes, puedes suscribirte a los planes Pro o Business desde tu panel de control. El saldo que tuvieras en tu bono no se pierde, se suma como respaldo al saldo mensual.</div>
                    </details>
                    <details class="cro-faq">
                        <summary class="cro-faq-q">¿Cómo obtengo mi factura con el IVA desglosado?</summary>
                        <div class="cro-faq-a">Al instante tras realizar el pago. Utilizamos Stripe como pasarela de pago B2B. Recibirás en tu correo electrónico la factura oficial completa con los datos fiscales (Razón social, CIF, Dirección) que podrás introducir durante el propio proceso de pago en la siguiente pantalla.</div>
                    </details>
                </div>
            </div>

            <!-- 4. Final CTA -->
            <div style="text-align: center; margin-top: 48px; margin-bottom: 32px;">
                <button type="button" onclick="window.scrollTo({top: 0, behavior: 'smooth'});" class="buy-btn" style="width: auto; padding: 18px 64px; border-radius: 99px; display: inline-block; margin-top: 0;">
                    Configurar mi Bono Ahora
                </button>
            </div>
        </div>
    </div>

    <?= view('partials/footer') ?>

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
                pricePerCallOutput.textContent = pricePerCredit.toLocaleString('es-ES', { minimumFractionDigits: 4, maximumFractionDigits: 4 }) + '€ / crédito';

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
