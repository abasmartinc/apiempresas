<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Factura <?= $invoice->invoice_number ?></title>
    <style>
        /* PDF Design System - Corporate Premium */
        @page {
            margin: 0;
            size: A4;
        }
        
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            color: #334155; 
            line-height: 1.5; 
            font-size: 10pt;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }

        /* Accent Elements */
        .color-accent { color: #2152FF; }
        .bg-accent { background-color: #2152FF; }
        
        .header-bar {
            height: 12px;
            background: linear-gradient(90deg, #2152FF, #12B48A);
            width: 100%;
        }

        .container {
            padding: 40px 50px;
        }

        /* Top Layout */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 40px;
        }
        
        .header-table td { vertical-align: top; }
        
        .brand-section { width: 60%; }
        .invoice-section { width: 40%; text-align: right; }

        .logo-text { 
            font-size: 24pt; 
            font-weight: bold; 
            color: #1e293b; 
            letter-spacing: -1px;
            margin: 0;
            line-height: 1;
        }
        .logo-text span { color: #2152FF; }
        
        .tagline {
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 5px;
            font-weight: 500;
        }

        .invoice-title {
            font-size: 22pt;
            font-weight: bold;
            color: #1e293b;
            margin: 0;
            letter-spacing: 1px;
        }

        .status-badge {
            display: inline-block;
            margin-top: 8px;
            padding: 4px 12px;
            background-color: #dcfce7;
            color: #166534;
            font-weight: bold;
            font-size: 9pt;
            border-radius: 4px;
            text-transform: uppercase;
        }

        /* Information Grid */
        .details-table {
            width: 100%;
            margin-bottom: 50px;
            border-collapse: collapse;
        }
        
        .details-table td { 
            width: 50%; 
            vertical-align: top;
            padding: 0 10px 0 0;
        }

        .section-label {
            font-size: 8pt;
            font-weight: bold;
            color: #94a3b8;
            text-transform: uppercase;
            margin-bottom: 8px;
            border-bottom: 1px solid #f1f5f9;
            padding-bottom: 4px;
            display: block;
        }

        .info-content {
            font-size: 9.5pt;
            color: #1e293b;
        }

        .info-content strong { color: #0f172a; }

        /* Items List */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }

        .items-table th {
            background-color: #1e293b;
            color: #ffffff;
            padding: 12px 15px;
            text-align: left;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .items-table td {
            padding: 15px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9.5pt;
        }

        .item-description strong {
            display: block;
            color: #1e293b;
            margin-bottom: 3px;
        }
        
        .item-subtext {
            font-size: 8pt;
            color: #64748b;
        }

        /* Totals Area */
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .summary-table td { vertical-align: top; }
        
        .notes-area {
            width: 55%;
            font-size: 8.5pt;
            padding-right: 50px;
            color: #64748b;
        }

        .totals-area { width: 45%; }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }

        .totals-table td {
            padding: 8px 15px;
            font-size: 10pt;
        }

        .total-label { color: #64748b; text-align: left; }
        .total-value { font-weight: 500; text-align: right; color: #1e293b; }

        .row-grand-total td {
            padding-top: 15px;
            border-top: 2px solid #1e293b;
        }

        .grand-total-label {
            font-size: 11pt;
            font-weight: bold;
            color: #1e293b;
        }

        .grand-total-value {
            font-size: 16pt;
            font-weight: bold;
            color: #2152FF;
        }

        /* Paid Stamp (Watermark style) */
        .paid-stamp {
            position: absolute;
            top: 450px;
            right: 100px;
            opacity: 0.08;
            transform: rotate(-30deg);
            border: 8px solid #166534;
            color: #166534;
            font-size: 60pt;
            font-weight: bold;
            padding: 10px 30px;
            border-radius: 20px;
            pointer-events: none;
        }

        /* Footer Section */
        .footer {
            position: absolute;
            bottom: 40px;
            left: 50px;
            right: 50px;
            border-top: 1px solid #f1f5f9;
            padding-top: 15px;
            text-align: center;
        }

        .footer-text {
            font-size: 8pt;
            color: #94a3b8;
            margin: 0;
            line-height: 1.6;
        }

        .footer-brand {
            font-weight: bold;
            color: #64748b;
            margin-bottom: 5px;
        }
    </style>
</head>
<body>
    <div class="header-bar"></div>
    
    <!-- Paid Stamp Background -->
    <div class="paid-stamp">PAGADA</div>

    <div class="container">
        <!-- Header Section -->
        <table class="header-table">
            <tr>
                <td class="brand-section">
                    <h1 class="logo-text">API<span>Empresas</span></h1>
                    <div class="tagline">Infraestructura de datos para el ecosistema empresarial</div>
                        <strong>Ariel Martinez Hernandez (APIEmpresas)</strong><br>
                        C/ Paseo República dominicana 40, Bajo E, 28983, Parla, Madrid<br>
                        NIF: 54994158P • soporte@apiempresas.es
                    </div>
                </td>
                <td class="invoice-section">
                    <div class="invoice-title">FACTURA</div>
                    <div class="status-badge">DOCUMENTO PAGADO</div>
                    <div style="margin-top: 15px; color: #475569; font-size: 10pt;">
                        FACTURA Nº: <strong><?= $invoice->invoice_number ?></strong><br>
                        FECHA EMISIÓN: <?= date('d/m/Y', strtotime($invoice->created_at)) ?><br>
                        MODO DE PAGO: Tarjeta (Stripe)
                    </div>
                </td>
            </tr>
        </table>

        <!-- Details Grid -->
        <table class="details-table">
            <tr>
                <td>
                    <span class="section-label">Información Fiscal del Cliente</span>
                    <div class="info-content">
                        <strong><?= esc($invoice->billing_name) ?></strong><br>
                        <?php if ($invoice->billing_vat): ?>
                        NIF/CIF: <?= esc($invoice->billing_vat) ?><br>
                        <?php endif; ?>
                        <?= esc($invoice->billing_email) ?><br>
                        <?= nl2br(esc($invoice->billing_address)) ?>
                    </div>
                </td>
                <td style="padding-left: 20px;">
                    <span class="section-label">Detalles del Servicio</span>
                    <div class="info-content">
                        <strong>Periodo de Facturación:</strong><br>
                        <?= date('d/m/Y', strtotime($invoice->created_at)) ?> — 
                        <?= date('d/m/Y', strtotime('+1 month', strtotime($invoice->created_at))) ?><br>
                        <strong>Referencia:</strong> INV-<?= substr(md5($invoice->id), 0, 8) ?>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 65%;">Descripción del concepto</th>
                    <th style="text-align: center; width: 10%;">Cant.</th>
                    <th style="text-align: right; width: 25%;">Importe</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="item-description">
                        <strong>Suscripción mensual API de verificación mercantil – Plan <?= esc($plan_name) ?></strong>
                        <div class="item-subtext">
                            Acceso full API, soporte prioritario 24/7 y actualizaciones en tiempo real.
                        </div>
                    </td>
                    <td style="text-align: center;">1</td>
                    <td style="text-align: right; font-weight: bold; color: #1e293b;">
                        <?= number_format($invoice->amount, 2, ',', '.') ?> €
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Summary and Totals -->
        <table class="summary-table">
            <tr>
                <td class="notes-area">
                    <span class="section-label">Notas Adicionales</span>
                    <p style="margin-top: 0; line-height: 1.4;">
                        Gracias por confiar en APIEmpresas. Los cargos aparecerán en su extracto bancario bajo el nombre de "APIEMPRESAS". 
                        Este documento sirve como comprobante legal de su suscripción activa.
                    </p>
                    <div style="margin-top: 15px;">
                        <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAFAAAABQCAYAAACOEfKtAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH5QgKDAAAB8A/zQAAAB1pVFh0Q29tbWVudAAAAAAAQ3JlYXRlZCB3aXRoIEdJTVBkLm3EAAAF8klEQVR42u2cf0BUVRTHv8+VZWVF/XGXVYIUK1S0LCX8kSBYloZlSloZloZlSloZloZlbGVaGZaGZUlSGRiZkVEZZWRUhmVkRkZmZGSMRpJRmZEJGZFZkeGdmX7H4zV/u/uWPXv37n2/M+/N3XvPue+++865773z7rn37rnn3nvvPW973nvvPe973nvve9/77nvve9/77nvve9/77nvve9/77nvve9/77nvve9/77nvve9/77nvve9/77nvve9/77nvve9/77nvve9/77nvve9/77nvve9/77nvve9/77nvvee973/uW9773vfe9733ve9/73vee9773vfe9733ve9/73vee9773vfe9733ve9/77nvve9/73nvve9/73vee9773vfe9733ve9/73vee9773vfe9733ve+9773vfe9/73vee9773vfe9733ve+9773vfe9733ve+9773vfe9733ve+9773vfe9733ve+9773vfe9733ve+9773vfe9733ve9733ve9733ve+9773vfe9733ve9733vve9773vfe9733ve+9773vfe9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733vve9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733ve+9733veXm9P8AEl08U+AAAAAElFTkSuQmCC" width="60" style="opacity: 0.5;">
                    </div>
                </td>
                <td class="totals-area">
                    <table class="totals-table">
                        <tr>
                            <td class="total-label">Base Imponible</td>
                            <td class="total-value"><?= number_format($invoice->amount, 2, ',', '.') ?> €</td>
                        </tr>
                        <tr>
                            <td class="total-label">IVA (21%)</td>
                            <td class="total-value"><?= number_format($invoice->tax_amount, 2, ',', '.') ?> €</td>
                        </tr>
                        <tr class="row-grand-total">
                            <td class="grand-total-label">TOTAL</td>
                            <td class="grand-total-value"><?= number_format($invoice->total_amount, 2, ',', '.') ?> €</td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>

        <!-- Footer -->
        <div class="footer">
            <p class="footer-brand">APIEmpresas.es — Verificación Empresarial Inteligente</p>
            <p class="footer-text">
                Ariel Martinez Hernandez (APIEmpresas) - NIF: 54994158P<br>
                Este documento es una factura electrónica válida. No requiere firma manuscrita según el RD 1619/2012.
            </p>
        </div>
    </div>
</body>
</html>
