<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe - <?= esc($company['name']) ?></title>
    <style>
        @page {
            margin: 0;
            size: A4;
        }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif; 
            color: #334155; 
            line-height: 1.6; 
            font-size: 10pt;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .header-bar {
            height: 10px;
            background: linear-gradient(90deg, #2152FF, #12B48A);
            width: 100%;
        }
        .container {
            padding: 40px 50px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .header-table td { vertical-align: middle; }
        .logo-section { width: 50%; }
        .info-section { width: 50%; text-align: right; }
        
        .logo-text { 
            font-size: 20pt; 
            font-weight: bold; 
            color: #1e293b; 
            margin: 0;
        }
        .logo-text span { color: #2152FF; }

        .report-title {
            font-size: 18pt;
            font-weight: bold;
            color: #1e293b;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .report-date {
            font-size: 9pt;
            color: #64748b;
            margin-top: 5px;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 5px;
            margin: 30px 0 15px 0;
            text-transform: uppercase;
        }

        .data-grid {
            width: 100%;
            border-collapse: collapse;
        }
        .data-grid td {
            padding: 8px 0;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #64748b;
            width: 30%;
        }
        .value {
            color: #1e293b;
            width: 70%;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 9pt;
            text-transform: uppercase;
        }
        .status-active {
            background-color: #dcfce7;
            color: #166534;
        }
        .status-inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .footer {
            position: absolute;
            bottom: 40px;
            left: 50px;
            right: 50px;
            text-align: center;
            border-top: 1px solid #f1f5f9;
            padding-top: 15px;
        }
        .footer-text {
            font-size: 8pt;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="header-bar"></div>
    <div class="container">
        <table class="header-table">
            <tr>
                <td class="logo-section">
                    <table style="width: auto;">
                        <tr>
                            <td style="vertical-align: middle; padding-right: 10px;">
                                <?php
                                    $logoPath = ROOTPATH . 'public/images/logo.png';
                                    if (file_exists($logoPath)) {
                                        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                                        $data = file_get_contents($logoPath);
                                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                        echo '<img src="' . $base64 . '" style="max-height: 35px;" alt="Logo">';
                                    }
                                ?>
                            </td>
                            <td style="vertical-align: middle;">
                                <h1 class="logo-text">API<span>Empresas</span></h1>
                            </td>
                        </tr>
                    </table>
                </td>
                <td class="info-section">
                    <div class="report-title">Informe de Verificación</div>
                    <div class="report-date">Generado el: <?= date('d/m/Y H:i') ?></div>
                </td>
            </tr>
        </table>

        <div class="section-title">Datos Principales</div>
        <table class="data-grid">
            <tr>
                <td class="label">Razón Social</td>
                <td class="value"><strong><?= esc($company['name'] ?? '-') ?></strong></td>
            </tr>
            <tr>
                <td class="label">Identificación Fiscal (NIF/CIF)</td>
                <td class="value"><?= esc($company['cif'] ?? $company['nif'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Estado Mercantil</td>
                <td class="value">
                    <?php
                        $statusRaw = (string)($company['status'] ?? '');
                        $isActive  = strtoupper($statusRaw) === 'ACTIVA';
                    ?>
                    <span class="status-badge <?= $isActive ? 'status-active' : 'status-inactive' ?>">
                        <?= esc($statusRaw ?: 'DESCONOCIDO') ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="label">Fecha de Constitución</td>
                <td class="value"><?= esc($company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Provincia</td>
                <td class="value"><?= esc($company['province'] ?? $company['provincia'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Municipio</td>
                <td class="value"><?= esc($company['municipality'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Domicilio Social</td>
                <td class="value"><?= esc($company['address'] ?? '-') ?></td>
            </tr>
        </table>

        <div class="section-title">Actividad Económica</div>
        <table class="data-grid">
            <tr>
                <td class="label">Código CNAE</td>
                <td class="value"><?= esc($company['cnae'] ?? $company['cnae_code'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Actividad Principal</td>
                <td class="value"><?= esc($company['cnae_label'] ?? '-') ?></td>
            </tr>
            <tr>
                <td class="label">Objeto Social</td>
                <td class="value"><?= esc($company['corporate_purpose'] ?? $company['objeto_social'] ?? '-') ?></td>
            </tr>
        </table>

        <div class="footer">
            <div style="font-weight: bold; color: #64748b; margin-bottom: 4px;">Informe emitido por APIEmpresas.es</div>
            <div class="footer-text">
                Este informe contiene datos procedentes de fuentes públicas oficiales.<br>
                La información mostrada está sujeta a la última actualización registral disponible.
            </div>
        </div>
    </div>
</body>
</html>
