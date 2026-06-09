<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => '¡Pago completado! - APIEmpresas',
        'excerptText' => 'Tu descarga está lista.',
    ]) ?>
    <style>
        body { overflow: hidden; background: #f8fafc; }
        .success-main {
            height: calc(100vh - 64px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            box-sizing: border-box;
        }
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 40px 48px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            text-align: center;
            max-width: 600px;
            width: 100%;
        }
        @media (max-width: 600px) {
            body { overflow: auto; }
            .success-main { height: auto; padding: 40px 16px; }
            .success-card { padding: 32px 24px; }
        }
        .check-icon {
            width: 64px; height: 64px;
            background: #dcfce7; color: #16a34a;
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            margin: 0 auto 20px;
        }
        .btn-download {
            display: inline-flex; align-items: center; justify-content: center; gap: 10px;
            padding: 16px 32px;
            background: #10b981; color: white;
            text-decoration: none; border-radius: 16px;
            font-weight: 900; font-size: 1.1rem;
            box-shadow: 0 10px 25px rgba(16, 185, 129, 0.35);
            transition: all 0.2s;
            margin-top: 24px;
            width: 100%;
        }
        .btn-download:hover { transform: translateY(-2px); box-shadow: 0 14px 30px rgba(16, 185, 129, 0.45); }
        .aba-confetti-burst {
            position: absolute; top: 50%; left: 50%;
            width: 1px; height: 1px;
            pointer-events: none; z-index: 10;
        }
        .aba-confetti-burst .confetti {
            position: absolute; top: 0; left: 0;
            opacity: 0; animation: burst 1.8s ease-out forwards;
        }
        @keyframes burst {
            0%   { transform: translate(0,0) scale(0) rotate(0deg); opacity: 0; }
            10%  { opacity: 1; }
            60%  { opacity: 1; }
            100% { transform: translate(var(--x),var(--y)) scale(1) rotate(calc(var(--spinBase,0deg) + 520deg)); opacity: 0; }
        }
    </style>
</head>
<body>
    <?= view('partials/header') ?>

    <main class="success-main">
        <div style="position: relative; width: 100%; max-width: 600px;">
            <div class="aba-confetti-burst" aria-hidden="true"></div>
            
            <div class="success-card" style="position: relative; z-index: 2;">
                <div class="check-icon">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><polyline points="20 6 9 17 4 12"></polyline></svg>
                </div>
                
                <h1 style="font-size: 2rem; font-weight: 900; color: #0f172a; margin: 0 0 12px; letter-spacing: -0.02em;">¡Listado listo!</h1>
                
                <?php 
                    $displayArea = 'España';
                    if (!empty($export_params['sector']) && $export_params['sector'] !== 'General') {
                        $displayArea = $export_params['sector'];
                    } elseif (!empty($export_params['provincia']) && $export_params['provincia'] !== 'España') {
                        $displayArea = $export_params['provincia'];
                    }
                ?>
                <p style="color: #475569; font-size: 1.05rem; line-height: 1.6; margin: 0 0 24px;">
                    Tu archivo CSV con el histórico de <strong><?= number_format($total_count ?? 0, 0, ',', '.') ?> empresas</strong> en <strong><?= esc($displayArea) ?></strong> ya está disponible para descargar.
                </p>

                <div style="background: #f1f5f9; border-radius: 12px; padding: 16px; margin-bottom: 24px; text-align: left; border: 1px solid #e2e8f0;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Referencia</span>
                        <span style="font-size: 0.9rem; color: #0f172a; font-weight: 800;">#<?= esc($order_ref) ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Empresas incluidas</span>
                        <span style="font-size: 0.9rem; color: #0f172a; font-weight: 800;"><?= number_format($total_count ?? 0, 0, ',', '.') ?></span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 0.85rem; color: #64748b; font-weight: 600; text-transform: uppercase;">Formato</span>
                        <span style="font-size: 0.9rem; color: #0f172a; font-weight: 800;">CSV (Delimitado por comas)</span>
                    </div>
                </div>

                <a href="<?= esc($download_url) ?>" class="btn-download" id="excel_main_download_btn">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Descargar CSV Ahora
                </a>
                
                <p style="margin-top: 16px; font-size: 0.85rem; color: #94a3b8;">
                    La descarga comenzará automáticamente.<br>
                    Si tienes algún problema, <a href="<?= site_url('contacto') ?>" style="color: #3b82f6; text-decoration: none;">contáctanos</a>.
                </p>
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
            const COLORS = ['#10b981', '#34d399', '#facc15', '#f97316'];
            const SHAPES = ['pill', 'diamond', 'line', 'dot'];
            for (let i = 0; i < 80; i++) {
                const el = document.createElement('span');
                el.className = 'confetti ' + SHAPES[Math.floor(Math.random() * SHAPES.length)];
                el.style.background = COLORS[Math.floor(Math.random() * COLORS.length)];
                el.style.position = 'absolute';
                const angle = (-Math.PI / 2) + (Math.random() - 0.5) * (Math.PI / 1.3);
                const distance = 200 + Math.random() * 250;
                el.style.setProperty('--x', (Math.cos(angle) * distance).toFixed(1) + 'px');
                el.style.setProperty('--y', (Math.sin(angle) * distance).toFixed(1) + 'px');
                el.style.setProperty('--spinBase', Math.floor(Math.random() * 180) + 'deg');
                el.style.animationDelay = (Math.random() * 0.22).toFixed(3) + 's';
                container.appendChild(el);
            }
        })();

        $(document).ready(function() {
            trackEvent('directory_excel_purchase', { provincia: '<?= esc($export_params["provincia"] ?? "") ?>', total: <?= $total_count ?? 0 ?> });
            
            // CSS for spinner and premium modals
            if (!$('#spinner-style').length) {
                $('<style id="spinner-style">@keyframes spin_pulse { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } } .swal-premium { border-radius: 24px !important; padding: 32px 24px !important; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15) !important; border: 1px solid #e2e8f0; } .swal-premium .swal2-title { font-weight: 800 !important; color: #0f172a !important; font-size: 1.75rem !important; margin-bottom: 8px !important; letter-spacing: -0.02em; } .swal-premium .swal2-html-container { margin: 0 !important; } .swal-close-btn-upsell { border-radius: 12px !important; padding: 12px 24px !important; font-weight: 700 !important; transition: all 0.2s !important; }</style>').appendTo('head');
            }

            $('#excel_main_download_btn').on('click', function(e) { 
                e.preventDefault();
                trackEvent('directory_excel_download'); 
                
                var $btn = $(this);
                if ($btn.hasClass('is-loading')) return;
                $btn.addClass('is-loading');
                
                // Unique token to track download completion
                var token = Math.random().toString(36).substring(2, 15);
                var downloadUrl = $btn.attr('href');
                var sep = downloadUrl.indexOf('?') !== -1 ? '&' : '?';
                var finalUrl = downloadUrl + sep + 'dl_token=' + token;
                
                Swal.fire({
                    title: '¡Preparando tu archivo CSV!',
                    html: `
                        <div style="margin-top: 5px; margin-bottom: 30px; color: #64748b; font-size: 1.1rem; line-height: 1.6;">
                            Recopilando y formateando miles de registros.<br>
                            <strong>Por favor, no cierres esta ventana.</strong>
                        </div>
                        <div style="position: relative; width: 80px; height: 80px; margin: 0 auto; color: #10b981; overflow: hidden; border-radius: 50%; box-shadow: 0 0 0 8px rgba(16, 185, 129, 0.1);">
                            <svg style="width: 100%; height: 100%; display: block; animation: spin_pulse 2s linear infinite;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-opacity="0.2" stroke-width="3"></circle>
                                <path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" stroke="currentColor" stroke-width="3" stroke-linecap="round"></path>
                            </svg>
                        </div>
                    `,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    background: '#ffffff',
                    customClass: { popup: 'swal-premium' }
                });
                
                // Start the download
                window.location.href = finalUrl;
                
                // Check for the cookie
                var checkInterval = setInterval(function() {
                    if (document.cookie.indexOf('dl_token=' + token) !== -1) {
                        clearInterval(checkInterval);
                        $btn.removeClass('is-loading');
                        
                        // Clear cookie
                        document.cookie = "dl_token=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;";
                        
                        Swal.fire({
                            title: '¡Descarga Completada!',
                            html: `
                                <div style="width: 72px; height: 72px; background: #dcfce7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; color: #16a34a; box-shadow: 0 0 0 10px rgba(22, 163, 74, 0.1);">
                                    <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                </div>
                                <div style="margin-bottom: 20px; font-size: 1.05rem; color: #475569;">Tu base de datos histórica ha sido generada y descargada con éxito.</div>
                                
                                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: left; margin-top: 20px;">
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 10px;">
                                        <div style="background: #e0e7ff; color: #4f46e5; padding: 8px; border-radius: 8px;">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                        </div>
                                        <h4 style="margin: 0; font-weight: 800; color: #1e293b; font-size: 1.1rem;">Ya tienes el pasado. ¿Quieres el futuro?</h4>
                                    </div>
                                    <p style="margin: 0 0 15px 0; color: #64748b; font-size: 0.95rem; line-height: 1.5;">
                                        Tu archivo CSV tiene las empresas creadas hasta hoy. Mantente actualizado automáticamente con nuestro <strong>Radar B2B</strong> y recibe las nuevas empresas de España cada mañana.
                                    </p>
                                    <a href="${'<?= site_url('radar/preview') ?>'}" style="display: block; text-align: center; background: #4f46e5; color: white; text-decoration: none; padding: 12px; border-radius: 8px; font-weight: 700; transition: background 0.2s;">
                                        Descubrir Radar B2B
                                    </a>
                                </div>
                            `,
                            showConfirmButton: true,
                            confirmButtonText: 'Cerrar ventana',
                            confirmButtonColor: '#94a3b8',
                            allowOutsideClick: false,
                            allowEscapeKey: false,
                            customClass: {
                                popup: 'swal-premium',
                                confirmButton: 'swal-close-btn-upsell'
                            }
                        });
                    }
                }, 1000);
            });
        });

        function trackEvent(type, metadata = {}) {
            $.post('<?= site_url("api/tracking/event") ?>', {
                event_type: type, source: 'directory_success', metadata: JSON.stringify(metadata)
            });
        }
    </script>
</body>
</html>
