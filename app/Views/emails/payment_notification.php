<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($subject) ?></title>
    <style>
        body { font-family: 'Inter', system-ui, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f1f5f9; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1); }
        .header { background: #0041ce; padding: 30px; text-align: center; color: #ffffff; }
        .header h1 { margin: 0; font-size: 20px; text-transform: uppercase; letter-spacing: 1px; }
        .content { padding: 40px; }
        .data-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #f1f5f9; }
        .data-label { color: #64748b; font-weight: bold; font-size: 14px; }
        .data-value { color: #1e293b; font-weight: 500; }
        .amount-highlight { font-size: 24px; font-weight: 800; color: #0041ce; text-align: center; margin: 30px 0; padding: 20px; background: #f8fafc; border-radius: 8px; }
        .footer { background: #f8fafc; padding: 20px; text-align: center; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <p style="margin: 0; font-size: 12px; opacity: 0.8;">NOTIFICACIÓN DE SISTEMA</p>
            <h1>Nuevo Cobro Procesado</h1>
        </div>
        <div class="content">
            <p>Se ha registrado un nuevo ingreso a través de Stripe:</p>
            
            <div class="amount-highlight">
                <?= number_format($amount, 2, ',', '.') ?> <?= $currency ?>
            </div>

            <div class="data-row">
                <span class="data-label">Cliente:</span>
                <span class="data-value"><?= esc($customer) ?></span>
            </div>
            <div class="data-row">
                <span class="data-label">Email:</span>
                <span class="data-value"><?= esc($email) ?></span>
            </div>
            <div class="data-row">
                <span class="data-label">Plan:</span>
                <span class="data-value"><?= esc($plan) ?></span>
            </div>
            <div class="data-row" style="border-bottom: none;">
                <span class="data-label">Nº Factura:</span>
                <span class="data-value"><?= esc($invoice->invoice_number) ?></span>
            </div>

            <p style="margin-top: 30px; font-size: 13px; color: #64748b; text-align: center;">
                La suscripción del usuario ha sido actualizada automáticamente.
            </p>
        </div>
        <div class="footer">
            &copy; <?= date('Y') ?> APIEmpresas.es Dashboard Administrativo
        </div>
    </div>
</body>
</html>
