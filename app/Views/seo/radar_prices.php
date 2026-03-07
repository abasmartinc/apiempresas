<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Precios Radar B2B - APIEmpresas',
        'excerptText' => 'Elige entre una descarga puntual o el acceso total al Radar B2B.',
    ]) ?>
    <style>
        :root {
            --primary: #2152ff;
            --slate-900: #0f172a;
            --slate-600: #475569;
            --slate-400: #94a3b8;
            --border: #e2e8f0;
        }
        .radar-hero {
            padding: 80px 20px 60px;
            text-align: center;
        }
        .radar-title {
            font-size: 3.5rem;
            font-weight: 950;
            color: var(--slate-900);
            margin-bottom: 20px;
            letter-spacing: -0.03em;
        }
        .radar-subtitle {
            font-size: 1.25rem;
            color: var(--slate-600);
            max-width: 650px;
            margin: 0 auto 60px;
            line-height: 1.6;
        }
        
        .pricing-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 32px;
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .pricing-card {
            background: white;
            border-radius: 24px;
            border: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            position: relative;
            transition: all 0.3s ease;
        }
        .pricing-card.featured {
            border: 2px solid var(--primary);
            box-shadow: 0 20px 50px -12px rgba(33, 82, 255, 0.15);
            transform: scale(1.02);
            z-index: 10;
        }
        .badge-recommended {
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary);
            color: white;
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 11px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .card-header {
            padding: 40px 40px 30px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
        }
        .card-name {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--slate-600);
            margin-bottom: 12px;
            display: block;
        }
        .card-price {
            font-size: 3.5rem;
            font-weight: 900;
            color: var(--slate-900);
            letter-spacing: -0.02em;
        }
        .card-price span {
            font-size: 1.1rem;
            font-weight: 600;
            color: var(--slate-400);
        }

        .card-body {
            padding: 40px;
            flex-grow: 1;
        }
        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0 0 40px;
        }
        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            margin-bottom: 18px;
            font-size: 14px;
            font-weight: 600;
            color: var(--slate-900);
        }
        .feature-item.disabled { color: var(--slate-400); font-weight: 500; }
        .feature-item svg { flex-shrink: 0; margin-top: 2px; }

        .btn-buy {
            display: block;
            width: 100%;
            padding: 18px;
            border-radius: 14px;
            font-size: 1rem;
            font-weight: 800;
            text-align: center;
            text-decoration: none;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn-buy-secondary {
            background: #f8fafc;
            color: var(--slate-900);
            border: 1px solid var(--border);
        }
        .btn-buy-secondary:hover { background: #f1f5f9; }
        
        .btn-buy-primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 10px 20px -5px rgba(33, 82, 255, 0.3);
        }
        .btn-buy-primary:hover { transform: translateY(-2px); box-shadow: 0 14px 24px -5px rgba(33, 82, 255, 0.4); }

        .pricing-footer {
            margin-top: 80px;
            padding-bottom: 100px;
            text-align: center;
            color: var(--slate-400);
            font-size: 0.95rem;
        }
        
        @media (max-width: 768px) {
            .radar-title { font-size: 2.5rem; }
            .pricing-grid { grid-template-columns: 1fr; }
            .pricing-card.featured { transform: none; margin-top: 20px; }
        }
    </style>
</head>
<body>
    <?= view('partials/header') ?>
    <main>
        <section class="radar-hero container">
            <span style="font-size: 0.85rem; font-weight: 800; background: #eef2ff; color: var(--primary); padding: 8px 16px; border-radius: 100px; margin-bottom: 24px; display: inline-block; border: 1px solid #dbeafe;">
                PLANES Y PRECIOS
            </span>
            <h1 class="radar-title">Acelera tu Prospección B2B</h1>
            <p class="radar-subtitle">
                Accede a los datos de las nuevas empresas que se constituyen cada día en España antes que tu competencia.
            </p>

            <div class="pricing-grid">
                <!-- SINGLE DOWNLOAD -->
                <div class="pricing-card">
                    <div class="card-header">
                        <span class="card-name">LISTADO PUNTUAL</span>
                        <div class="card-price">9€ <span>/descarga</span></div>
                        <p style="margin: 12px 0 0; font-size: 0.9rem; color: var(--slate-600);">Para necesidades específicas.</p>
                    </div>
                    <div class="card-body">
                        <ul class="feature-list">
                            <li class="feature-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#12b48a" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Descarga 1 listado segmentado
                            </li>
                            <li class="feature-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#12b48a" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Exportación Excel / CSV
                            </li>
                            <li class="feature-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#12b48a" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Acceso al Radar por 24 horas
                            </li>
                            <li class="feature-item disabled">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                Alertas diarias automáticas
                            </li>
                            <li class="feature-item disabled">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                Histórico completo ilimitado
                            </li>
                        </ul>
                        <form action="<?= site_url('billing/checkout') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="plan" value="radar">
                            <input type="hidden" name="period" value="single">
                            <button type="submit" class="btn-buy btn-buy-secondary">
                                Comprar una descarga
                            </button>
                        </form>
                    </div>
                </div>

                <!-- SUBSCRIPTION (FEATURED) -->
                <div class="pricing-card featured">
                    <span class="badge-recommended">RECOMENDADO</span>
                    <div class="card-header">
                        <span class="card-name">RADAR PRO</span>
                        <div class="card-price">99€ <span>/mes</span></div>
                        <p style="margin: 12px 0 0; font-size: 0.9rem; color: var(--slate-600);">Tu motor de ventas en piloto automático.</p>
                    </div>
                    <div class="card-body">
                        <ul class="feature-list">
                            <li class="feature-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#12b48a" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                <strong>Acceso Ilimitado</strong> 24/7/365
                            </li>
                            <li class="feature-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#12b48a" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Alertas en tiempo real por email
                            </li>
                            <li class="feature-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#12b48a" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Exportaciones ilimitadas
                            </li>
                            <li class="feature-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#12b48a" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Datos de administradores incluidos
                            </li>
                            <li class="feature-item">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#12b48a" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Sin permanencia. Cancela online.
                            </li>
                        </ul>
                        <form action="<?= site_url('billing/checkout') ?>" method="POST">
                            <?= csrf_field() ?>
                            <input type="hidden" name="plan" value="radar">
                            <input type="hidden" name="period" value="monthly">
                            <button type="submit" class="btn-buy btn-buy-primary">
                                Activar Radar Pro
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="pricing-footer container">
                <p>Todos los precios incluyen acceso al soporte premium por tickets.</p>
                <div style="margin-top: 24px; display: flex; justify-content: center; gap: 40px;">
                    <span style="display: flex; align-items: center; gap: 8px;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg> Pago 100% Seguro</span>
                    <span style="display: flex; align-items: center; gap: 8px;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg> Visa, Mastercard & PayPal</span>
                </div>
            </div>
        </section>
    </main>
    <?= view('partials/footer') ?>
</body>
</html>
