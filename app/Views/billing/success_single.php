<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => '¡Pago completado! - APIEmpresas',
        'excerptText' => 'Tu descarga está lista.',
    ]) ?>
    <style>
        body { overflow: hidden; }
        .success-main {
            height: calc(100vh - 64px);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 16px;
            box-sizing: border-box;
        }
        .success-wrapper {
            max-width: 980px;
            width: 100%;
            display: grid;
            grid-template-columns: 1fr 380px;
            gap: 20px;
        }
        @media (max-width: 860px) {
            body { overflow: auto; }
            .success-main { height: auto; padding: 20px 16px; }
            .success-wrapper { grid-template-columns: 1fr; }
        }
        .success-card {
            background: white;
            border-radius: 20px;
            padding: 24px 28px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            text-align: center;
        }
        .check-icon {
            width: 46px; height: 46px;
            background: #dcfce7; color: #16a34a;
            border-radius: 50%;
            display: inline-flex; align-items: center; justify-content: center;
            margin-bottom: 10px;
        }
        .btn-download {
            display: inline-flex; align-items: center; gap: 10px;
            padding: 13px 24px;
            background: #0f172a; color: white;
            text-decoration: none; border-radius: 14px;
            font-weight: 900; font-size: 1rem;
            box-shadow: 0 8px 12px -3px rgba(15,23,42,0.3);
            transition: transform 0.2s;
        }
        .btn-download:hover { transform: translateY(-2px); }
        .btn-email {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 10px 22px;
            background: #f1f5f9; color: #475569;
            text-decoration: none; border-radius: 12px;
            font-weight: 700; font-size: 0.88rem;
            border: 1px solid #e2e8f0;
            transition: all 0.2s; cursor: pointer;
        }
        .btn-email:hover { background: #e2e8f0; color: #1e293b; }
        .btn-email:disabled { opacity: 0.6; cursor: not-allowed; }
        .upsell-card {
            background: linear-gradient(135deg, #334155, #1e293b);
            border-radius: 20px; padding: 24px 28px;
            color: white; display: flex; flex-direction: column; justify-content: center;
            position: relative; overflow: hidden;
        }
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

<?php
    $type = $export_type ?? 'excel';
    $itemLabel = 'empresas';
    $itemSingular = 'Empresas';
    $upsellTitle = 'Radar PRO';
    $upsellFeat1 = 'Oportunidades ilimitadas';
    $upsellFeat2 = 'Actualización cada hora';
    $ctaUrl = site_url('radar?source=excel');
    $ctaText = 'Ver todas las oportunidades ahora';
    
    if ($type === 'subsidies_excel') {
        $itemLabel = 'subvenciones';
        $itemSingular = 'Subvenciones';
        $upsellTitle = 'API de Subvenciones';
        $upsellFeat1 = 'Integración en tiempo real';
        $upsellFeat2 = 'Automatiza tu CRM';
        $ctaUrl = site_url('api-empresas');
        $ctaText = 'Ver Planes API';
    } elseif ($type === 'contracts_excel') {
        $itemLabel = 'licitaciones';
        $itemSingular = 'Licitaciones';
        $upsellTitle = 'API de Contratos';
        $upsellFeat1 = 'Alertas de adjudicaciones';
        $upsellFeat2 = 'Búsqueda por CIF';
        $ctaUrl = site_url('api-empresas');
        $ctaText = 'Ver Planes API';
    }
?>
    <main class="success-main">
        <div class="success-wrapper">
            <!-- IZQUIERDA: PAGO Y DESCARGA -->
            <div style="position: relative;">
                <div class="aba-confetti-burst" aria-hidden="true"></div>
                <div class="success-card" style="position: relative; z-index: 2;">
                    <div class="check-icon">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="4"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <h1 style="font-size: 1.75rem; font-weight: 900; color: #1e293b; margin: 0 0 6px; letter-spacing: -0.02em;">¡Listado desbloqueado!</h1>
                    <p style="color: #64748b; font-size: 0.9rem; line-height: 1.5; margin: 0 0 14px;">
                        Este listado incluye <strong><?= number_format($total_count ?? 0, 0, ',', '.') ?> <?= $itemLabel ?></strong>.
                        <?php if($type === 'excel'): ?>
                        <br><span style="color: #ef4444; font-weight: 700;">Pero mañana habrá nuevas oportunidades disponibles en el Radar.</span>
                        <?php endif; ?>
                    </p>

                    <div style="background: #f8fafc; border-radius: 12px; padding: 12px 16px; margin-bottom: 14px; border: 1px solid #e2e8f0; text-align: left;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                            <div>
                                <h4 style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; margin: 0 0 6px;">Tu archivo CSV</h4>
                                <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.82rem; color: #475569;">
                                    <li style="margin-bottom: 3px;">✅ <?= number_format($total_count ?? 0, 0, ',', '.') ?> <?= $itemLabel ?></li>
                                    <li>❌ Datos estáticos (hoy)</li>
                                </ul>
                            </div>
                            <div style="border-left: 1px solid #e2e8f0; padding-left: 12px;">
                                <h4 style="font-size: 0.65rem; text-transform: uppercase; letter-spacing: 0.05em; color: #3b82f6; margin: 0 0 6px;"><?= $upsellTitle ?></h4>
                                <ul style="list-style: none; padding: 0; margin: 0; font-size: 0.82rem; color: #475569;">
                                    <li style="margin-bottom: 3px;">🚀 <?= $upsellFeat1 ?></li>
                                    <li>⚡ <?= $upsellFeat2 ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <div style="display: flex; flex-direction: column; gap: 8px; max-width: 380px; margin: 0 auto;">
                        <?php if (($total_count ?? 0) > 100000): ?>
                            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 16px; margin-bottom: 12px;">
                                <div style="display: flex; align-items: center; justify-content: center; margin-bottom: 8px;">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-clock"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                                </div>
                                <h3 style="color: #92400e; font-size: 1rem; font-weight: 800; margin: 0 0 6px;">Generando tu archivo...</h3>
                                <p style="color: #b45309; font-size: 0.85rem; line-height: 1.4; margin: 0;">Debido al gran volumen de datos (<?= number_format($total_count ?? 0, 0, ',', '.') ?> registros), estamos empaquetando el listado. Recibirás un enlace de descarga segura en tu correo electrónico en los próximos minutos.</p>
                            </div>
                        <?php else: ?>
                            <a href="<?= esc($download_url) ?>" class="btn-download" id="excel_main_download_btn" style="width: 100%; justify-content: center;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                Descargar Listado (.csv)
                            </a>
                        <?php endif; ?>

                        <?php if ($type === 'excel'): ?>
                        <a href="<?= $ctaUrl ?>" id="excel_to_radar_cta" style="display:inline-flex; justify-content:center; align-items:center; gap:8px; background:radial-gradient(circle at 0% 0%,#fefce8 0,#facc15 35%,#f97316 100%); color:#0f172a; font-weight:800; text-decoration:none; font-size:0.95rem; padding:12px 18px; border-radius:12px; box-shadow:0 10px 24px rgba(249,115,22,0.4); transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                            <?= $ctaText ?>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                        </a>
                        <?php endif; ?>

                    </div>

                    <div style="margin-top: 12px; padding-top: 10px; border-top: 1px solid #f1f5f9; color: #94a3b8; font-size: 0.75rem; font-weight: 600;">
                        REF: #<?= esc($order_ref) ?> | Formato: CSV
                    </div>
                </div>
            </div>

<?php
                $rightTitle = 'Evita que la competencia contacte antes';
                $rightText = 'Mientras tú descargas este listado, otros proveedores están recibiendo alertas en tiempo real de nuevas empresas que se crean hoy mismo.';
                $rightBtn = 'Ver nuevas oportunidades ahora';
                if ($type === 'subsidies_excel') {
                    $rightTitle = 'Conecta tu sistema en tiempo real';
                    $rightText = 'Descargar un archivo estático está bien, pero nuestra API de Subvenciones insertará automáticamente las nuevas ayudas en tu CRM en cuanto se publiquen en el BOE.';
                    $rightBtn = 'Descubrir Planes API';
                } elseif ($type === 'contracts_excel') {
                    $rightTitle = 'Recibe alertas de adjudicaciones';
                    $rightText = 'Descargar el historial es el primer paso. Con nuestra API de Contratos, podrás monitorizar licitaciones y recibir notificaciones instantáneas para actuar rápido.';
                    $rightBtn = 'Descubrir Planes API';
                }
            ?>
            <div class="upsell-card">
                <div style="display:inline-block; background:#ef4444; color:white; padding:4px 10px; border-radius:8px; font-size:0.68rem; font-weight:900; text-transform:uppercase; margin-bottom:12px; letter-spacing:0.05em; align-self:flex-start;">
                    ⚠️ No te quedes atrás
                </div>

                <h3 style="font-size:1.35rem; font-weight:900; margin:0 0 10px; line-height:1.2; color:white;"><?= $rightTitle ?></h3>

                <p style="color:#cbd5e1; line-height:1.5; margin:0 0 16px; font-size:0.875rem;">
                    <?= $rightText ?>
                </p>

                <ul style="list-style:none; padding:0; margin:0 0 18px;">
                    <?php foreach (['Descargas ilimitadas diarias', 'Alertas por email al instante', 'Acceso a datos de contacto PRO'] as $feat): ?>
                    <li style="display:flex; align-items:center; gap:8px; margin-bottom:7px; color:#f8fafc; font-weight:600; font-size:0.875rem;">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        <?= $feat ?>
                    </li>
                    <?php endforeach; ?>
                </ul>

                <a href="<?= $ctaUrl ?>" style="display:inline-flex; justify-content:center; align-items:center; gap:8px; background:radial-gradient(circle at 0% 0%,#fefce8 0,#facc15 35%,#f97316 100%); color:#0f172a; font-weight:800; text-decoration:none; font-size:0.95rem; padding:13px 18px; border-radius:12px; box-shadow:0 10px 24px rgba(249,115,22,0.4); transition:transform 0.2s; width:100%;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                    <?= $rightBtn ?>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
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
            for (let i = 0; i < 60; i++) {
                const el = document.createElement('span');
                el.className = 'confetti ' + SHAPES[Math.floor(Math.random() * SHAPES.length)];
                el.style.background = COLORS[Math.floor(Math.random() * COLORS.length)];
                el.style.position = 'absolute';
                const angle = (-Math.PI / 2) + (Math.random() - 0.5) * (Math.PI / 1.3);
                const distance = 180 + Math.random() * 200;
                el.style.setProperty('--x', (Math.cos(angle) * distance).toFixed(1) + 'px');
                el.style.setProperty('--y', (Math.sin(angle) * distance).toFixed(1) + 'px');
                el.style.setProperty('--spinBase', Math.floor(Math.random() * 180) + 'deg');
                el.style.animationDelay = (Math.random() * 0.22).toFixed(3) + 's';
                container.appendChild(el);
            }
        })();

        $(document).ready(function() {
            trackEvent('excel_purchase', { provincia: '<?= esc($export_params["provincia"] ?? "") ?>', total: <?= $total_count ?? 0 ?> });
            trackEvent('excel_post_download_view');
            trackEvent('excel_success_view');
            $('#excel_to_radar_cta').on('click', function() { trackEvent('excel_to_radar_click'); });
            $('#excel_main_download_btn').on('click', function() { trackEvent('excel_download_start'); });
        });

        function trackEvent(type, metadata = {}) {
            $.post('<?= site_url("api/tracking/event") ?>', {
                event_type: type, source: 'excel_success', metadata: JSON.stringify(metadata)
            });
        }
    </script>
</body>
</html>
