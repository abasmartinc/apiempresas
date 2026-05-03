<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => $title ?? 'Mis Facturas - Radar PRO',
        'excerptText' => 'Historial de facturación y descargas en Radar PRO.',
    ]) ?>
    <link rel="stylesheet" href="<?= base_url('public/css/radar.css?v=' . (file_exists(FCPATH . 'public/css/radar.css') ? filemtime(FCPATH . 'public/css/radar.css') : time())) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .ae-radar-btn--outline:hover {
            background: #f1f5f9 !important;
            border-color: #cbd5e1 !important;
            color: #1e293b !important;
        }
    </style>
</head>
<body>

<div class="ae-radar-page">
    <div class="ae-radar-page__shell">
        
        <?= view('radar/partials/sidebar') ?>


        <main class="ae-radar-page__main">
            <header class="ae-radar-page__topbar">
                <div class="ae-radar-page__breadcrumb">
                    <span>Radar PRO</span>
                    <span>/</span>
                    <strong>Mis Facturas</strong>
                </div>
            </header>

            <div class="ae-radar-page__content">
                <div class="ae-radar-page__container">
                    
                    <div class="ae-favorites-page__header" style="margin-bottom: 40px;">
                        <h1 class="ae-favorites-page__title">Tu Historial de Facturación</h1>
                        <p class="ae-favorites-page__subtitle">Consulta y descarga todas tus facturas de suscripción y listados puntuales de Radar PRO.</p>
                    </div>

                    <div style="background: white; border-radius: 24px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05);">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                    <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Factura</th>
                                    <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Fecha</th>
                                    <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Concepto</th>
                                    <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Importe</th>
                                    <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em;">Estado</th>
                                    <th style="padding: 20px 24px; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($invoices)): ?>
                                    <tr>
                                        <td colspan="6" style="padding: 100px 24px; text-align: center;">
                                            <div style="font-size: 48px; margin-bottom: 16px; opacity: 0.2;">🧾</div>
                                            <div style="font-size: 16px; font-weight: 700; color: #1e293b;">No hay facturas disponibles</div>
                                            <p style="color: #64748b; margin-top: 8px;">Tus facturas aparecerán aquí una vez realices tu primera compra.</p>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($invoices as $inv): ?>
                                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                            <td style="padding: 20px 24px;">
                                                <span style="font-weight: 700; color: #1e293b;"><?= esc($inv->invoice_number) ?></span>
                                            </td>
                                            <td style="padding: 20px 24px; color: #64748b; font-size: 14px; font-weight: 500;">
                                                <?= date('d/m/Y', strtotime($inv->created_at)) ?>
                                            </td>
                                            <td style="padding: 20px 24px;">
                                                <div style="font-size: 14px; font-weight: 700; color: #1e293b;"><?= esc($inv->plan_name ?: 'Servicio Radar') ?></div>
                                                <div style="font-size: 11px; color: #94a3b8; font-weight: 600; text-transform: uppercase;">Radar PRO</div>
                                            </td>
                                            <td style="padding: 20px 24px;">
                                                <span style="font-size: 15px; font-weight: 800; color: #0f172a;"><?= number_format($inv->total_amount, 2, ',', '.') ?>€</span>
                                            </td>
                                            <td style="padding: 20px 24px;">
                                                <?php if ($inv->status === 'paid'): ?>
                                                    <span style="background: #ecfdf5; color: #059669; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase;">Pagada</span>
                                                <?php else: ?>
                                                    <span style="background: #fffbeb; color: #d97706; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase;"><?= esc($inv->status) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td style="padding: 20px 24px; text-align: right;">
                                                <a href="<?= site_url('billing/invoices/download/' . $inv->id) ?>" class="ae-radar-btn ae-radar-btn--outline" style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 14px; font-size: 12px; text-decoration: none; border: 1px solid #e2e8f0; border-radius: 8px; color: #475569; font-weight: 700; transition: all 0.2s;">
                                                    <i class="fas fa-download"></i> Descargar PDF
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                        
                        <?php if ($pager): ?>
                            <div class="ae-favorites-pagination" style="padding: 20px 24px; background: #f8fafc; border-top: 1px solid #e2e8f0;">
                                <?= $pager->links() ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div style="margin-top: 32px; padding: 24px; background: #eff6ff; border: 1px solid #dbeafe; border-radius: 20px; display: flex; align-items: flex-start; gap: 16px;">
                        <div style="font-size: 24px;">ℹ️</div>
                        <div>
                            <h4 style="margin: 0 0 4px 0; font-size: 14px; font-weight: 800; color: #1e40af;">¿Necesitas cambiar tus datos de facturación?</h4>
                            <p style="margin: 0; font-size: 13px; color: #1e40af; opacity: 0.8; line-height: 1.5;">Puedes actualizar tu dirección fiscal, NIF o razón social desde el <a href="<?= site_url('billing/portal') ?>" style="font-weight: 700; color: #1e40af; text-decoration: underline;">Portal de Cliente</a> de Stripe.</p>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</div>

</body>
</html>
