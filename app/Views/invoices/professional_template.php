<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura <?= $invoice->invoice_number ?></title>
    <style>
        /* Design System */
        :root {
            --primary: #0041ce;
            --secondary: #64748b;
            --dark: #0f172a;
            --light: #f8fafc;
            --border: #e2e8f0;
            --success: #10b981;
        }
        
        @page { margin: 0; }
        
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            color: #334155; 
            line-height: 1.6; 
            font-size: 11pt;
            margin: 0;
            background-color: #fff;
        }

        /* Header Accent Bar */
        .color-bar {
            height: 8px;
            background: linear-gradient(90deg, #0041ce, #12b48a);
            width: 100%;
        }

        .invoice-container {
            padding: 40px 50px;
        }

        /* Top Header */
        .header {
            width: 100%;
            margin-bottom: 50px;
        }
        .header td { vertical-align: top; }
        
        .logo { font-size: 26pt; font-weight: bold; color: #0041ce; letter-spacing: -1px; }
        .logo span { color: #12b48a; }
        
        .invoice-badge {
            display: inline-block;
            background-color: #f1f5f9;
            color: #475569;
            padding: 5px 15px;
            border-radius: 4px;
            font-size: 10pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .status-paid { background-color: #dcfce7; color: #166534; }

        /* Info Grid */
        .info-grid {
            width: 100%;
            margin-bottom: 40px;
        }
        .info-grid td { width: 50%; vertical-align: top; }
        
        .label {
            font-size: 9pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
            margin-bottom: 5px;
            display: block;
        }

        .value { color: #1e293b; font-weight: 500; }

        /* Table Styling */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .items-table th {
            background-color: #f8fafc;
            border-bottom: 2px solid #e2e8f0;
            padding: 12px 10px;
            text-align: left;
            font-size: 9pt;
            text-transform: uppercase;
            color: #64748b;
        }
        .items-table td {
            padding: 15px 10px;
            border-bottom: 1px solid #f1f5f9;
        }
        
        /* Summary / Totals */
        .totals-container {
            width: 100%;
        }
        .totals-container td { width: 60%; vertical-align: top; }
        .total-box {
            width: 100%;
            background-color: #f8fafc;
            padding: 20px;
            border-radius: 8px;
        }
        .total-row { display: table; width: 100%; margin-bottom: 8px; }
        .total-label { display: table-cell; color: #64748b; }
        .total-value { display: table-cell; text-align: right; font-weight: bold; color: #1e293b; }
        
        .grand-total {
            border-top: 1px solid #e2e8f0;
            margin-top: 10px;
            padding-top: 10px;
            font-size: 14pt;
        }
        .grand-total .total-value { color: #0041ce; }

        /* Footer */
        .footer {
            margin-top: 80px;
            padding-top: 20px;
            border-top: 1px solid #f1f5f9;
            text-align: center;
            font-size: 9pt;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="color-bar"></div>
    
    <div class="invoice-container">
        <!-- Header Section -->
        <table class="header">
            <tr>
                <td>
                    <div class="logo">API<span>Empresas</span>.es</div>
                    <div style="margin-top: 5px; color: #64748b; font-size: 10pt;">
                        Infraestructura de datos empresariales en tiempo real
                    </div>
                </td>
                <td style="text-align: right;">
                    <h1 style="margin: 0; color: #0f172a; font-size: 18pt;">FACTURA</h1>
                    <div class="invoice-badge status-paid">PAGADA</div>
                    <div style="margin-top: 10px; color: #475569; font-size: 10pt;">
                        Nº <strong><?= $invoice->invoice_number ?></strong><br>
                        Fecha: <?= date('d/m/Y', strtotime($invoice->created_at)) ?>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Details Grid -->
        <table class="info-grid">
            <tr>
                <td>
                    <div class="label">Emisor</div>
                    <div class="value">
                        <strong>APIEmpresas S.L.</strong><br>
                        CIF: B12345678<br>
                        Calle de la Innovación 42, 2ª Planta<br>
                        28001 Madrid, España<br>
                        <span style="color: #0041ce;">soporte@apiempresas.es</span>
                    </div>
                </td>
                <td>
                    <div class="label">Cliente</div>
                    <div class="value">
                        <strong><?= esc($invoice->billing_name) ?></strong><br>
                        <?php if ($invoice->billing_vat): ?>
                            NIF/CIF: <?= esc($invoice->billing_vat) ?><br>
                        <?php endif; ?>
                        <?= esc($invoice->billing_email) ?><br>
                        <?= nl2br(esc($invoice->billing_address)) ?>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 70%;">Descripción del concepto</th>
                    <th style="text-align: center; width: 10%;">Cant.</th>
                    <th style="text-align: right; width: 20%;">Total</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="font-weight: bold; color: #0f172a;">Suscripción APIEmpresas - Plan <?= esc($plan_name) ?></div>
                        <div style="font-size: 9pt; color: #64748b; margin-top: 2px;">
                            Acceso ilimitado a consultas y soporte prioritario. <br>
                            Periodo: <?= date('d/m/Y', strtotime($invoice->created_at)) ?> al <?= date('d/m/Y', strtotime('+1 month', strtotime($invoice->created_at))) ?>
                        </div>
                    </td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right; font-weight: bold; color: #0f172a;">
                        <?= number_format($invoice->amount, 2, ',', '.') ?> €
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Summary -->
        <table class="totals-container">
            <tr>
                <td>
                    <div style="padding-right: 40px; font-size: 9pt; color: #64748b; line-height: 1.4;">
                        <strong>Método de Pago:</strong> Tarjeta de Crédito (Vía Stripe)<br>
                        <strong>Notas:</strong> Gracias por ayudarnos a crecer. Los cargos en tu extracto aparecerán como "APIEMPRESAS".
                    </div>
                </td>
                <td>
                    <div class="total-box">
                        <div class="total-row">
                            <span class="total-label">Subtotal</span>
                            <span class="total-value"><?= number_format($invoice->amount, 2, ',', '.') ?> €</span>
                        </div>
                        <div class="total-row">
                            <span class="total-label">IVA (21%)</span>
                            <span class="total-value"><?= number_format($invoice->tax_amount, 2, ',', '.') ?> €</span>
                        </div>
                        <div class="total-row grand-total">
                            <span class="total-label" style="font-weight: bold; color: #0f172a;">TOTAL</span>
                            <span class="total-value"><?= number_format($invoice->total_amount, 2, ',', '.') ?> €</span>
                        </div>
                    </div>
                </td>
            </tr>
        </table>

        <div class="footer">
            APIEmpresas.es • Creado por y para desarrolladores • Documento digitalmente verificado
        </div>
    </div>
</body>
</html>
