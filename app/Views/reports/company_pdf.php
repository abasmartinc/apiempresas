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
            line-height: 1.4; 
            font-size: 9pt;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        .header-bar {
            height: 8px;
            background: linear-gradient(90deg, #2152FF, #12B48A);
            width: 100%;
        }
        .container {
            padding: 30px 45px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .header-table td { vertical-align: middle; }
        .logo-section { width: 45%; }
        .info-section { width: 55%; text-align: right; }
        
        .logo-text { 
            font-size: 18pt; 
            font-weight: bold; 
            color: #1e293b; 
            margin: 0;
        }
        .logo-text span { color: #2152FF; }

        .report-title {
            font-size: 14pt;
            font-weight: bold;
            color: #1e293b;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-date {
            font-size: 8pt;
            color: #64748b;
            margin-top: 2px;
        }

        .section-title {
            font-size: 10pt;
            font-weight: bold;
            color: #0f172a;
            background-color: #f8fafc;
            border-left: 3px solid #2152FF;
            padding: 6px 10px;
            margin: 20px 0 10px 0;
            text-transform: uppercase;
        }

        .data-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .data-grid td {
            padding: 6px 0;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #64748b;
            width: 28%;
        }
        .value {
            color: #1e293b;
            width: 72%;
        }

        .status-badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 8pt;
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

        /* Lists for Admins and Borme */
        .list-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .list-table th {
            text-align: left;
            font-size: 8pt;
            color: #64748b;
            padding: 4px 8px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
        }
        .list-table td {
            padding: 6px 8px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 8.5pt;
        }

        .borme-date {
            font-weight: bold;
            color: #2152FF;
            white-space: nowrap;
        }
        .borme-type {
            font-weight: bold;
            color: #1e293b;
        }
        .borme-desc {
            color: #475569;
            font-size: 8pt;
            line-height: 1.3;
        }

        .footer {
            margin-top: 30px;
            border-top: 1px solid #f1f5f9;
            padding-top: 10px;
            text-align: center;
        }
        .footer-text {
            font-size: 7.5pt;
            color: #94a3b8;
        }

        .page-break {
            page-break-before: always;
        }

        .no-break {
            page-break-inside: avoid;
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
                            <td style="vertical-align: middle; padding-right: 8px;">
                                <?php
                                    $logoPath = ROOTPATH . 'public/images/logo.png';
                                    if (file_exists($logoPath)) {
                                        $type = pathinfo($logoPath, PATHINFO_EXTENSION);
                                        $data = file_get_contents($logoPath);
                                        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
                                        echo '<img src="' . $base64 . '" style="max-height: 30px;" alt="Logo">';
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
                    <div class="report-title">Certificado de Verificación Mercantil</div>
                    <div class="report-date">Emisión: <?= date('d/m/Y H:i') ?> · Ref: <?= strtoupper(substr(md5($company['id'] . time()), 0, 8)) ?></div>
                </td>
            </tr>
        </table>

        <div class="no-break">
            <div class="section-title">Identificación y Estado</div>
            <table class="data-grid">
                <tr>
                    <td class="label">Razón Social</td>
                    <td class="value"><strong><?= esc($company['name'] ?? '-') ?></strong></td>
                </tr>
                <tr>
                    <td class="label">NIF / CIF</td>
                    <td class="value"><strong><?= esc($company['cif'] ?? $company['nif'] ?? '-') ?></strong></td>
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
                    <td class="label">Fecha Constitución</td>
                    <td class="value"><?= esc($company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">Registro Mercantil</td>
                    <td class="value"><?= esc($company['province'] ?? $company['provincia'] ?? '-') ?></td>
                </tr>
            </table>
        </div>

        <div class="no-break">
            <div class="section-title">Localización y Contacto</div>
            <table class="data-grid">
                <tr>
                    <td class="label">Domicilio Social</td>
                    <td class="value"><?= esc($company['address'] ?? '-') ?>, <?= esc($company['municipality'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">Provincia</td>
                    <td class="value"><?= esc($company['province'] ?? $company['provincia'] ?? '-') ?></td>
                </tr>
                <?php if (!empty($company['phone']) || !empty($company['phone_enriched'])): ?>
                <tr>
                    <td class="label">Teléfono</td>
                    <td class="value"><?= esc($company['phone_enriched'] ?? $company['phone'] ?? '-') ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($company['email'])): ?>
                <tr>
                    <td class="label">Email</td>
                    <td class="value"><?= esc($company['email']) ?></td>
                </tr>
                <?php endif; ?>
                <?php if (!empty($company['website_official'])): ?>
                <tr>
                    <td class="label">Sitio Web</td>
                    <td class="value"><?= esc($company['website_official']) ?></td>
                </tr>
                <?php endif; ?>
            </table>
        </div>

        <div class="no-break">
            <div class="section-title">Actividad y Clasificación</div>
            <table class="data-grid">
                <tr>
                    <td class="label">CNAE 2009</td>
                    <td class="value"><?= esc($company['cnae'] ?? $company['cnae_code'] ?? '-') ?> · <?= esc($company['cnae_label'] ?? '-') ?></td>
                </tr>
                <?php if (!empty($company['cnae_2025'])): ?>
                <tr>
                    <td class="label">CNAE 2025 (Nuevo)</td>
                    <td class="value"><?= esc($company['cnae_2025']) ?> · <?= esc($company['cnae_2025_label'] ?? '-') ?></td>
                </tr>
                <?php endif; ?>
                <tr>
                    <td class="label">Objeto Social</td>
                    <td class="value" style="font-size: 8pt; text-align: justify;"><?= esc($company['corporate_purpose'] ?? $company['objeto_social'] ?? '-') ?></td>
                </tr>
            </table>
        </div>

        <?php if (!empty($administrators)): ?>
        <div class="no-break">
            <div class="section-title">Administradores y Cargos Actuales</div>
            <table class="list-table">
                <thead>
                    <tr>
                        <th style="width: 60%;">Nombre / Razón Social</th>
                        <th style="width: 40%;">Cargo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($administrators as $admin): ?>
                    <tr>
                        <td><strong><?= esc($admin['name']) ?></strong></td>
                        <td><?= esc($admin['position']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if (!empty($bormePosts)): ?>
        <div class="page-break"></div>
        <div class="header-bar"></div>
        <div class="container" style="padding-top: 15px;">
            <div class="section-title">Últimos Actos en el Registro Mercantil (BORME)</div>
            <table class="list-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Fecha</th>
                        <th style="width: 85%;">Detalle del Acto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($bormePosts, 0, 15) as $post): ?>
                    <tr>
                        <td class="borme-date"><?= date('d/m/Y', strtotime($post['borme_date'])) ?></td>
                        <td>
                            <div class="borme-type"><?= esc($post['act_types'] ?: 'Acto Registral') ?></div>
                            <div class="borme-desc"><?= esc(character_limiter($post['description'], 400)) ?></div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if (count($bormePosts) > 15): ?>
            <p style="font-size: 8pt; color: #64748b; margin-top: 10px; font-style: italic;">
                * Se muestran los últimos 15 actos. Consulte la ficha completa en apiempresas.es para ver el historial total.
            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <div class="footer">
            <div style="font-weight: bold; color: #64748b; margin-bottom: 2px;">Documento generado por APIEmpresas.es</div>
            <div class="footer-text">
                Este informe tiene carácter informativo y ha sido generado automáticamente a partir de datos oficiales (BORME, Registro Mercantil, AEAT).<br>
                Verifique la integridad de este documento mediante el código de referencia en la cabecera.
            </div>
        </div>
    </div>
</body>
</html>
