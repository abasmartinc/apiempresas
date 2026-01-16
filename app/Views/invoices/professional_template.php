<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura <?= $invoice->invoice_number ?></title>
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; color: #333; line-height: 1.5; font-size: 12px; }
        .invoice-box { max-width: 800px; margin: auto; padding: 30px; }
        .header { display: flex; justify-content: space-between; margin-bottom: 40px; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px; }
        .logo { font-size: 24px; font-weight: 800; color: #2152ff; }
        .logo span { color: #12b48a; }
        .company-info { text-align: right; }
        .invoice-details { margin-bottom: 40px; }
        .invoice-details table { width: 100%; }
        .invoice-details td { vertical-align: top; width: 50%; }
        .section-title { font-weight: 800; text-transform: uppercase; font-size: 10px; color: #64748b; margin-bottom: 8px; }
        .table { width: 100%; border-collapse: collapse; margin-bottom: 40px; }
        .table th { background: #f8fafc; text-align: left; padding: 12px; border-bottom: 2px solid #e2e8f0; font-weight: 700; color: #475569; }
        .table td { padding: 12px; border-bottom: 1px solid #f1f5f9; }
        .totals { float: right; width: 250px; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 8px 0; }
        .totals .grand-total { border-top: 2px solid #2152ff; font-weight: 800; font-size: 16px; color: #1e293b; }
        .footer { margin-top: 100px; text-align: center; color: #94a3b8; font-size: 10px; border-top: 1px solid #f1f5f9; padding-top: 20px; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table style="width: 100%; margin-bottom: 40px;">
            <tr>
                <td>
                    <div class="logo">API<span>Empresas</span>.es</div>
                    <div style="margin-top: 10px; color: #64748b;">
                        Factura: <strong><?= $invoice->invoice_number ?></strong><br>
                        Fecha: <?= date('d/m/Y', strtotime($invoice->created_at)) ?>
                    </div>
                </td>
                <td style="text-align: right; color: #64748b;">
                    <strong>APIEmpresas S.L.</strong><br>
                    CIF: B12345678<br>
                    Calle Falsa 123, 28001<br>
                    Madrid, España
                </td>
            </tr>
        </table>

        <table style="width: 100%; margin-bottom: 40px;">
            <tr>
                <td>
                    <div class="section-title">Facturar a:</div>
                    <strong><?= esc($invoice->billing_name) ?></strong><br>
                    <?= esc($invoice->billing_email) ?><br>
                    <?php if ($invoice->billing_vat): ?>
                        NIF/CIF: <?= esc($invoice->billing_vat) ?><br>
                    <?php endif; ?>
                    <?= nl2br(esc($invoice->billing_address)) ?>
                </td>
                <td style="text-align: right;">
                    <div class="section-title">Estado del Pago:</div>
                    <span style="background: #dcfce7; color: #166534; padding: 4px 12px; border-radius: 99px; font-weight: 700; text-transform: uppercase; font-size: 10px;">
                        <?= $invoice->status === 'paid' ? 'Pagada' : esc($invoice->status) ?>
                    </span>
                </td>
            </tr>
        </table>

        <table class="table">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th style="text-align: right;">Precio</th>
                    <th style="text-align: right;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong>Suscripción Plan <?= esc($plan_name) ?></strong><br>
                        <span style="color: #64748b; font-size: 10px;">Periodo: <?= date('d/m/Y', strtotime($invoice->created_at)) ?> - <?= date('d/m/Y', strtotime('+1 month', strtotime($invoice->created_at))) ?></span>
                    </td>
                    <td style="text-align: right;"><?= number_format($invoice->amount, 2, ',', '.') ?> €</td>
                    <td style="text-align: right;"><?= number_format($invoice->amount, 2, ',', '.') ?> €</td>
                </tr>
            </tbody>
        </table>

        <div class="totals">
            <table>
                <tr>
                    <td>Base Imponible:</td>
                    <td style="text-align: right;"><?= number_format($invoice->amount, 2, ',', '.') ?> €</td>
                </tr>
                <tr>
                    <td>IVA (21%):</td>
                    <td style="text-align: right;"><?= number_format($invoice->tax_amount, 2, ',', '.') ?> €</td>
                </tr>
                <tr class="grand-total">
                    <td>Total:</td>
                    <td style="text-align: right;"><?= number_format($invoice->total_amount, 2, ',', '.') ?> €</td>
                </tr>
            </table>
        </div>

        <div style="clear: both;"></div>

        <div class="footer">
            Gracias por confiar en APIEmpresas.es<br>
            Esta factura ha sido generada automáticamente y es válida sin firma.
        </div>
    </div>
</body>
</html>
