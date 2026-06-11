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
            margin-top: -10px; /* (12px / 2) - (32px / 2) = -10 */
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
                <p style="text-align:center; font-size:0.85rem; color:#94a3b8; margin-top:16px;">Pago seguro gestionado por Stripe. IVA no incluido.</p>
            </form>

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
                priceOutput.innerHTML = price.toLocaleString('es-ES') + '<span>€</span>';

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
