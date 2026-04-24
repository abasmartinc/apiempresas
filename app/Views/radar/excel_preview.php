<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Desbloquea tu listado Excel — APIEmpresas',
        'excerptText' => 'Introduce tu email para acceder al listado completo de empresas detectadas hoy.',
        'robots'      => 'noindex,follow',
    ]) ?>
    <style>
        body { background: #f8fafc; font-family: 'Inter', sans-serif; }
        .preview-container { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        
        .hero-card {
            background: white;
            border-radius: 24px;
            padding: 48px;
            text-align: center;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 30px rgba(0,0,0,0.03);
            margin-bottom: 40px;
        }
        
        .hero-card__badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 99px;
            background: #f0fdf4;
            color: #16a34a;
            font-size: 0.8rem;
            font-weight: 800;
            margin-bottom: 24px;
        }

        .hero-card__title {
            font-size: 2.25rem;
            font-weight: 900;
            color: #0f172a;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }

        .hero-card__subtitle {
            font-size: 1.1rem;
            color: #64748b;
            margin-bottom: 32px;
        }

        .capture-box {
            max-width: 500px;
            margin: 0 auto;
            background: #f8fafc;
            padding: 32px;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
        }

        .capture-box__title {
            font-size: 1rem;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 20px;
        }

        .email-input {
            width: 100%;
            height: 60px;
            padding: 0 20px;
            border-radius: 12px;
            border: 2px solid #e2e8f0;
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 16px;
            outline: none;
            transition: border-color 0.2s;
        }
        .email-input:focus { border-color: #2563eb; }

        .submit-btn {
            width: 100%;
            height: 60px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            transition: background 0.2s;
        }
        .submit-btn:hover { background: #1d4ed8; }

        .features-grid {
            display: flex;
            justify-content: center;
            gap: 24px;
            margin-top: 24px;
            font-size: 0.85rem;
            color: #64748b;
            font-weight: 600;
        }
        .feature-item { display: flex; align-items: center; gap: 6px; }
        .feature-item svg { color: #10b981; }

        /* Blurred Table */
        .table-wrap {
            background: white;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            overflow: hidden;
            position: relative;
        }
        .preview-table { width: 100%; border-collapse: collapse; }
        .preview-table th { background: #f8fafc; padding: 16px 24px; text-align: left; font-size: 0.75rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; }
        .preview-table td { padding: 20px 24px; border-bottom: 1px solid #f1f5f9; }
        
        .blurred-row { filter: blur(8px); opacity: 0.5; pointer-events: none; }
        
        .table-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, transparent 10%, rgba(255,255,255,0.9) 30%, white 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            padding-top: 100px;
        }

        .stats-summary {
            display: flex;
            gap: 32px;
            justify-content: center;
            margin-top: 40px;
        }
        .stat-item { text-align: center; }
        .stat-item__value { font-size: 1.5rem; font-weight: 900; color: #0f172a; }
        .stat-item__label { font-size: 0.8rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; }
    </style>
</head>
<body>
    <?= view('partials/header') ?>

    <main class="preview-container">
        <section class="hero-card">
            <div class="hero-card__badge">
                <span class="pulse-dot" style="width:8px;height:8px;background:#10b981;border-radius:50%;display:inline-block;margin-right:8px;"></span>
                Listado actualizado hace 5 min
            </div>
            
            <h1 class="hero-card__title">Desbloquea las <?= number_format($total_context_count, 0, ',', '.') ?> empresas de <?= esc($province) ?></h1>
            <p class="hero-card__subtitle">Estas son las mismas oportunidades que acabas de ver. Desbloquea el acceso completo para descargar el listado y contactar antes que otros proveedores.</p>

            <div class="capture-box" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; padding: 32px;">
                <div style="text-align: center; margin-bottom: 24px;">
                    <p style="font-size: 1.1rem; color: #1e40af; font-weight: 800; margin-bottom: 8px;">
                        💰 Valor estimado de estas oportunidades:
                    </p>
                    <div style="font-size: 1.8rem; font-weight: 950; color: #1e3a8a; letter-spacing: -0.02em;">
                        <?= number_format($total_context_count * 500, 0, ',', '.') ?>€ – <?= number_format($total_context_count * 2000, 0, ',', '.') ?>€
                    </div>
                    <p style="font-size: 0.85rem; color: #60a5fa; font-weight: 600; margin-top: 4px;">
                        Estimación basada en el ticket medio B2B para <?= number_format($total_context_count, 0, ',', '.') ?> empresas
                    </p>
                </div>

                <a href="<?= site_url('checkout/radar-export?' . http_build_query($_GET)) ?>" class="submit-btn" style="text-decoration: none; text-align: center; display: block;" id="excel_checkout_btn">
                    Desbloquear Listado Completo
                </a>
                <p style="font-size: 0.8rem; color: #64748b; text-align: center; margin-top: 16px; font-weight: 500;">
                    Acceso inmediato tras el pago · Sin registro previo necesario
                </p>

                <div class="features-grid" style="margin-top: 24px; border-top: 1px solid #bfdbfe; padding-top: 20px;">
                    <div class="feature-item" style="color: #1e40af;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Descarga directa (.xlsx)
                    </div>
                    <div class="feature-item" style="color: #1e40af;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Garantía oficial BORME
                    </div>
                </div>
            </div>

            <div class="stats-summary">
                <div class="stat-item">
                    <div class="stat-item__value"><?= number_format($total_context_count, 0, ',', '.') ?></div>
                    <div class="stat-item__label">Empresas</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item__value">~15%</div>
                    <div class="stat-item__label">Alta Prioridad</div>
                </div>
                <div class="stat-item">
                    <div class="stat-item__value"><?= esc($period === 'hoy' ? 'Hoy' : ($period === 'semana' ? '7 días' : '30 días')) ?></div>
                    <div class="stat-item__label">Periodo</div>
                </div>
            </div>
        </section>

        <div class="table-wrap">
            <table class="preview-table">
                <thead>
                    <tr>
                        <th>Empresa</th>
                        <th>Actividad</th>
                        <th>Provincia</th>
                        <th>Prioridad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($companies, 0, 3) as $co): ?>
                    <tr>
                        <td style="font-weight: 700;"><?= esc($co['name'] ?? 'Empresa Confidencial') ?></td>
                        <td style="font-size: 0.8rem; color: #64748b;"><?= esc($co['cnae_label'] ?? 'Sector B2B') ?></td>
                        <td><?= esc($co['municipality'] ?? $province) ?></td>
                        <td><span style="background: #fef2f2; color: #ef4444; padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 800;">ALTA</span></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php for($i=0; $i<10; $i++): ?>
                    <tr class="blurred-row">
                        <td>Empresa Confidencial SL</td>
                        <td>Servicios de Consultoría</td>
                        <td>Madrid</td>
                        <td>MEDIA</td>
                    </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
            <div class="table-overlay">
                <div style="background: white; padding: 24px; border-radius: 16px; border: 1px solid #e2e8f0; text-align: center; box-shadow: 0 4px 20px rgba(0,0,0,0.05);">
                    <p style="font-weight: 800; color: #0f172a; margin-bottom: 4px;">+<?= number_format($total_context_count - 3, 0, ',', '.') ?> empresas ocultas</p>
                    <p style="font-size: 0.85rem; color: #64748b;">Desbloquea el acceso para ver el listado completo</p>
                </div>
            </div>
        </div>
    </main>

    <?= view('partials/footer') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Evento: excel_preview_view
            trackEvent('excel_preview_view', { 
                provincia: '<?= esc($province) ?>',
                period: '<?= esc($period) ?>',
                total: <?= $total_context_count ?>
            });

            $('#excel_checkout_btn').on('click', function() {
                trackEvent('excel_checkout_start', {
                    provincia: '<?= esc($province) ?>',
                    period: '<?= esc($period) ?>'
                });
            });
        });

        function trackEvent(type, metadata = {}) {
            $.post('<?= site_url("api/tracking/event") ?>', {
                event_type: type,
                source: 'excel_preview',
                metadata: JSON.stringify(metadata)
            });
        }
    </script>
</body>
</html>
