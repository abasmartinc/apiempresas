<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tu factura de APIEmpresas.es</title>
    <style>
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #334155; margin: 0; padding: 0; background-color: #f8fafc; }
        .wrapper { padding: 40px 20px; }
        .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; }
        .header { background: linear-gradient(135deg, #2152FF 0%, #12B48A 100%); padding: 40px 30px; text-align: center; color: white; }
        .header h1 { margin: 0; font-size: 24px; font-weight: 700; }
        .content { padding: 40px 30px; }
        .content h2 { color: #0f172a; font-size: 20px; margin-top: 0; margin-bottom: 20px; }
        .content p { margin-bottom: 20px; color: #475569; }
        .payment-summary { background: #f1f5f9; padding: 25px; border-radius: 8px; margin: 30px 0; }
        .summary-row { display: flex; justify-content: space-between; margin-bottom: 10px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px; }
        .summary-row:last-child { border-bottom: none; margin-bottom: 0; padding-bottom: 0; }
        .summary-label { font-weight: 600; color: #64748b; font-size: 14px; }
        .summary-value { font-weight: 600; color: #0f172a; }
        .total-row { margin-top: 15px; padding-top: 15px; border-top: 2px solid #cbd5e1; display: flex; justify-content: space-between; align-items: center; }
        .total-label { font-size: 16px; font-weight: 700; color: #0f172a; }
        .total-value { font-size: 24px; font-weight: 800; color: #2152FF; }
        .cta-container { text-align: center; margin-top: 40px; }
        .btn { display: inline-block; background-color: #2152FF; color: white !important; padding: 14px 28px; border-radius: 8px; text-decoration: none; font-weight: 600; font-size: 16px; }
        .footer { padding: 30px; text-align: center; font-size: 13px; color: #94a3b8; border-top: 1px solid #f1f5f9; }
        .footer a { color: #64748b; text-decoration: underline; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="container">
            <div class="header">
                <h1>APIEmpresas.es</h1>
            </div>
            <div class="content">
                <h2>¡Gracias por tu pago!</h2>
                <p>Hola <strong><?= esc($name) ?></strong>,</p>
                <p>Te confirmamos que hemos recibido correctamente el pago de tu suscripción. Adjunto a este correo encontrarás la factura correspondiente en formato PDF.</p>

                <table width="100%" cellpadding="0" cellspacing="0" style="background: #f1f5f9; border-radius: 8px; margin: 30px 0;">
                    <tr>
                        <td style="padding: 25px;">
                            <table width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td style="padding-bottom: 15px; border-bottom: 1px solid #e2e8f0;">
                                        <span style="font-size: 14px; font-weight: 600; color: #64748b;">Concepto</span>
                                    </td>
                                    <td align="right" style="padding-bottom: 15px; border-bottom: 1px solid #e2e8f0;">
                                        <span style="font-weight: 600; color: #0f172a;"><?= esc($plan_name) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                        <span style="font-size: 14px; font-weight: 600; color: #64748b;">Nº Factura</span>
                                    </td>
                                    <td align="right" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                        <span style="font-weight: 600; color: #0f172a;"><?= esc($invoice_number) ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                        <span style="font-size: 14px; font-weight: 600; color: #64748b;">Fecha</span>
                                    </td>
                                    <td align="right" style="padding: 15px 0; border-bottom: 1px solid #e2e8f0;">
                                        <span style="font-weight: 600; color: #0f172a;"><?= date('d/m/Y') ?></span>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding-top: 20px;">
                                        <span style="font-size: 16px; font-weight: 700; color: #0f172a;">Total Pagado</span>
                                    </td>
                                    <td align="right" style="padding-top: 20px;">
                                        <span style="font-size: 24px; font-weight: 800; color: #2152FF;"><?= number_format($amount, 2, ',', '.') ?> <?= $currency ?></span>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <p>Ya puedes seguir disfrutando de todas las ventajas de tu plan. No tienes que realizar ninguna acción adicional.</p>

                <div class="cta-container">
                    <a href="<?= site_url('dashboard') ?>" class="btn">Ir a mi Panel</a>
                </div>
            </div>
            <div class="footer">
                <p>¿Tienes alguna duda con tu factura? Contáctanos en <a href="mailto:soporte@apiempresas.es">soporte@apiempresas.es</a></p>
                <p>&copy; <?= date('Y') ?> APIEmpresas.es - Todos los derechos reservados.</p>
            </div>
        </div>
    </div>
</body>
</html>
