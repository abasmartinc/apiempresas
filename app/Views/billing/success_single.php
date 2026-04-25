<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => '¡Pago completado! - APIEmpresas',
        'excerptText' => 'Tu descarga está lista.',
    ]) ?>
    <style>
        .success-wrapper {
            max-width: 1100px;
            margin: 40px auto;
            display: grid;
            grid-template-columns: 1fr 450px;
            gap: 40px;
            padding: 0 20px;
        }
        @media (max-width: 950px) {
            .success-wrapper { grid-template-columns: 1fr; }
        }
        .success-card {
            background: white;
            border-radius: 24px;
            padding: 48px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            text-align: center;
        }
        .check-icon {
            width: 64px;
            height: 64px;
            background: #dcfce7;
            color: #16a34a;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }
        .btn-download {
            display: inline-flex;
            align-items: center;
            gap: 12px;
            padding: 20px 40px;
            background: #0f172a;
            color: white;
            text-decoration: none;
            border-radius: 16px;
            font-weight: 900;
            font-size: 1.25rem;
            margin-top: 24px;
            box-shadow: 0 10px 15px -3px rgba(15, 23, 42, 0.3);
            transition: transform 0.2s;
        }
        .btn-download:hover { transform: translateY(-2px); }
        
        .btn-email {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 16px 32px;
            background: #f1f5f9;
            color: #475569;
            text-decoration: none;
            border-radius: 14px;
            font-weight: 700;
            font-size: 1rem;
            margin-top: 12px;
            border: 1px solid #e2e8f0;
            transition: all 0.2s;
            cursor: pointer;
            width: auto;
            min-width: 250px;
        }
        .btn-email:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: translateY(-1px);
        }
        .btn-email:disabled { opacity: 0.6; cursor: not-allowed; }
        
        .upsell-card {
            background: linear-gradient(135deg, #334155, #1e293b);
            border-radius: 24px;
            padding: 40px;
            color: white;
            text-align: left;
            position: relative;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .aba-confetti-burst {
            position: absolute;
            top: 50%; left: 50%;
            width: 1px; height: 1px;
            pointer-events: none;
            z-index: 10;
        }
        .aba-confetti-burst .confetti {
            position: absolute;
            top: 0; left: 0;
            opacity: 0;
            animation: burst 1.8s ease-out forwards;
        }
        @keyframes burst {
            0% { transform: translate(0, 0) scale(0) rotate(0deg); opacity: 0; }
            10% { opacity: 1; }
            60% { opacity: 1; }
            100% { transform: translate(var(--x), var(--y)) scale(1) rotate(calc(var(--spinBase, 0deg) + 520deg)); opacity: 0; }
        }
    </style>
</head>
<body>
    <?= view('partials/header') ?>

    <main class="container">
        <div class="success-wrapper">
            <!-- IZQUIERDA: PAGO Y DESCARGA -->
            <div style="position: relative;">
                <div class="aba-confetti-burst" aria-hidden="true"></div>
                <div class="success-card" style="position: relative; z-index: 2;">
                    <div class="check-icon">
                        <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <h1 style="font-size: 2.5rem; font-weight: 900; color: #1e293b; margin-bottom: 12px; letter-spacing: -0.02em;">¡Listado desbloqueado!</h1>
                    <p style="color: #64748b; font-size: 1.1rem; line-height: 1.5; margin-bottom: 32px; max-width: 500px; margin-left: auto; margin-right: auto;">
                        Este listado incluye <strong><?= number_format($total_count ?? 0, 0, ',', '.') ?> empresas</strong> detectadas hoy.
                        <br>
                        <span style="color: #ef4444; font-weight: 700;">Pero mañana habrá nuevas oportunidades disponibles en el Radar.</span>
                    </p>

                    <div style="background: #f8fafc; border-radius: 16px; padding: 24px; margin-bottom: 32px; border: 1px solid #e2e8f0; text-align: left;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <div>
                                <h4 style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin-bottom: 12px;">Tu Excel</h4>
                                <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem; color: #475569;">
                                    <li style="margin-bottom: 8px;">✅ <?= number_format($total_count ?? 0, 0, ',', '.') ?> empresas</li>
                                    <li>❌ Datos estáticos (hoy)</li>
                                </ul>
                            </div>
                            <div style="border-left: 1px solid #e2e8f0; padding-left: 20px;">
                                <h4 style="font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; color: #3b82f6; margin-bottom: 12px;">Radar PRO</h4>
                                <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.9rem; color: #475569;">
                                    <li style="margin-bottom: 8px;">🚀 Oportunidades ilimitadas</li>
                                    <li>⚡ Actualización cada hora</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 12px; max-width: 400px; margin: 0 auto;">
                        <a href="<?= esc($download_url) ?>" class="btn-download" id="excel_main_download_btn" style="width: 100%; justify-content: center;">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            Descargar Listado (.xlsx)
                        </a>
                        
                        <a href="<?= site_url('radar?source=excel') ?>" id="excel_to_radar_cta" style="display: inline-flex; justify-content: center; align-items: center; gap: 10px; background: radial-gradient(circle at 0% 0%, #fefce8 0, #facc15 35%, #f97316 100%); color: #0f172a; font-weight: 800; text-decoration: none; font-size: 1.15rem; padding: 14px 24px; border-radius: 12px; box-shadow: 0 12px 30px rgba(249, 115, 22, 0.4); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 16px 40px rgba(249, 115, 22, 0.5)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 12px 30px rgba(249, 115, 22, 0.4)';">
                            Ver todas las oportunidades ahora <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </a>
                    </div>
                    <div style="display: flex; justify-content: center;">
                        <button type="button" class="btn-email ae-email-export-btn" 
                                data-url="<?= site_url('checkout/radar-email?' . http_build_query($export_params ?? [])) ?>"
                                data-total="<?= number_format($total_count ?? 0, 0, ',', '.') ?>">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                            Enviar por correo
                        </button>
                    </div>

                    <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #f1f5f9; color: #94a3b8; font-size: 0.85rem; font-weight: 600;">
                        REF: #<?= esc($order_ref) ?> | Formato: Microsoft Excel / CSV
                    </div>
                </div>
            </div>

            <!-- DERECHA: UPSELL RADAR -->
            <div class="upsell-card">
                <div style="display: inline-block; background: #ef4444; color: white; padding: 6px 14px; border-radius: 8px; font-size: 0.75rem; font-weight: 900; text-transform: uppercase; margin-bottom: 20px; letter-spacing: 0.05em; align-self: flex-start;">
                    ⚠️ No te quedes atrás
                </div>
                
                <h3 style="font-size: 1.75rem; font-weight: 900; margin-bottom: 16px; line-height: 1.2; color: white;">Evita que la competencia contacte antes</h3>
                
                <p style="color: #cbd5e1; line-height: 1.6; margin-bottom: 28px; font-size: 1rem;">
                    Mientras tú descargas este listado, otros proveedores están recibiendo alertas en tiempo real de nuevas empresas que se crean hoy mismo.
                </p>
                
                <ul style="list-style: none; padding: 0; margin: 0 0 32px 0;">
                    <?php foreach (['Descargas ilimitadas diarias', 'Alertas por email al instante', 'Acceso a datos de contacto PRO'] as $feat): ?>
                    <li style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; color: #f8fafc; font-weight: 600; font-size: 0.95rem;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg> <?= $feat ?>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <a href="<?= site_url('radar') ?>" id="excel_to_radar_cta" style="display: inline-flex; justify-content: center; align-items: center; gap: 10px; background: radial-gradient(circle at 0% 0%, #fefce8 0, #facc15 35%, #f97316 100%); color: #0f172a; font-weight: 800; text-decoration: none; font-size: 1.15rem; padding: 14px 24px; border-radius: 12px; box-shadow: 0 12px 30px rgba(249, 115, 22, 0.4); transition: transform 0.2s, box-shadow 0.2s; width: 100%;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 16px 40px rgba(249, 115, 22, 0.5)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 12px 30px rgba(249, 115, 22, 0.4)';">
                    Ver nuevas oportunidades ahora <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
            </div>
        </div>
    </main>

    <?= view('partials/footer') ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        (function () {
            const container = document.querySelector('.aba-confetti-burst');
            if (!container) return;

            const COLORS = ['#2152FF', '#5C7CFF', '#12B48A', '#facc15', '#f97316'];
            const SHAPES = ['pill', 'diamond', 'line', 'dot'];
            const COUNT  = 60;

            for (let i = 0; i < COUNT; i++) {
                const el = document.createElement('span');
                const shape = SHAPES[Math.floor(Math.random() * SHAPES.length)];
                el.className = `confetti ${shape}`;
                el.style.background = COLORS[Math.floor(Math.random() * COLORS.length)];
                el.style.position = 'absolute';

                const angle = (-Math.PI / 2) + (Math.random() - 0.5) * (Math.PI / 1.3);
                const distance = 220 + Math.random() * 240;
                const x = Math.cos(angle) * distance;
                const y = Math.sin(angle) * distance;

                el.style.setProperty('--x', `${x.toFixed(1)}px`);
                el.style.setProperty('--y', `${y.toFixed(1)}px`);
                el.style.setProperty('--spinBase', `${Math.floor(Math.random() * 180)}deg`);
                el.style.animationDelay = `${(Math.random() * 0.22).toFixed(3)}s`;

                container.appendChild(el);
            }
        })();

        $(document).ready(function() {
            // Eventos Tracking
            trackEvent('excel_purchase', {
                provincia: '<?= esc($export_params["provincia"] ?? "") ?>',
                total: <?= $total_count ?? 0 ?>
            });
            trackEvent('excel_post_download_view');
            trackEvent('excel_to_radar_view');
            trackEvent('excel_success_view');

            $('#excel_to_radar_cta').on('click', function() {
                trackEvent('excel_to_radar_click');
            });
            
            $('#excel_main_download_btn').on('click', function() {
                trackEvent('excel_download_start');
            });
        });

        function trackEvent(type, metadata = {}) {
            $.post('<?= site_url("api/tracking/event") ?>', {
                event_type: type,
                source: 'excel_success',
                metadata: JSON.stringify(metadata)
            });
        }
    </script>
</body>
</html>
