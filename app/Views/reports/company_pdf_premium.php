<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe Premium - <?= esc($company['name']) ?></title>
    <style>
        <?php
            // Variables de Marca Blanca (White Label) por defecto
            $brandColor = $brandColor ?? '#1e293b'; // Azul oscuro si no hay color
            $brandName = $brandName ?? 'APIEmpresas';
            $brandFooterText = $brandFooterText ?? 'Documento generado por ' . esc($brandName);
        ?>
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
        
        /* PORTADA EDITORIAL PREMIUM */
        .cover-page {
            width: 100%;
            height: 100%;
            position: relative;
            background-color: #ffffff;
            box-sizing: border-box;
        }
        .cover-border-top { position: absolute; top: 0; left: 0; width: 100%; height: 12px; background-color: <?= $brandColor ?>; }
        .cover-border-bottom { position: absolute; bottom: 0; left: 0; width: 100%; height: 12px; background-color: <?= $brandColor ?>; }
        .cover-border-left { position: absolute; top: 0; left: 0; width: 12px; height: 100%; background-color: <?= $brandColor ?>; }
        .cover-border-right { position: absolute; top: 0; right: 0; width: 12px; height: 100%; background-color: <?= $brandColor ?>; }

        .cover-content {
            padding: 120px 80px;
        }
        
        .cover-logo-container {
            height: 120px;
            margin-bottom: 100px;
        }
        .cover-logo {
            max-width: 250px;
            max-height: 100px;
        }
        .cover-brand-name {
            font-size: 28pt;
            font-weight: bold;
            color: <?= $brandColor ?>;
        }

        .cover-title-main {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 42pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.1;
            margin-bottom: 40px;
            letter-spacing: -1px;
            text-transform: uppercase;
        }
        .cover-title-highlight {
            color: <?= $brandColor ?>;
        }
        .cover-divider {
            width: 120px;
            height: 8px;
            background-color: #0f172a;
            margin-bottom: 50px;
        }

        .cover-company-name {
            font-size: 24pt;
            font-weight: bold;
            color: #334155;
            margin-bottom: 10px;
        }
        .cover-company-cif {
            font-size: 16pt;
            color: #64748b;
        }

        .cover-footer {
            position: absolute;
            bottom: 80px;
            left: 80px;
            color: #64748b;
            font-size: 11pt;
            border-left: 4px solid <?= $brandColor ?>;
            padding-left: 20px;
            line-height: 1.6;
        }
        .cover-qr {
            position: absolute;
            bottom: 80px;
            right: 80px;
            text-align: right;
            color: #64748b;
            font-size: 8pt;
        }
        .cover-qr img {
            width: 80px;
            height: 80px;
            margin-bottom: 5px;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
        }

        /* PAGINAS INTERIORES */
        .header-bar {
            height: 8px;
            background-color: <?= $brandColor ?>;
            width: 100%;
        }
        .container {
            padding: 30px 45px;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 30px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 15px;
        }
        .header-table td { vertical-align: middle; }
        .logo-section { width: 50%; }
        .info-section { width: 50%; text-align: right; }
        
        .logo-small { max-height: 35px; }
        .logo-text-small { font-size: 14pt; font-weight: bold; color: <?= $brandColor ?>; margin: 0; }

        .report-title-small {
            font-size: 11pt;
            font-weight: bold;
            color: #1e293b;
            margin: 0;
            text-transform: uppercase;
        }
        .report-date-small {
            font-size: 8pt;
            color: #64748b;
            margin-top: 2px;
        }

        .section-title {
            font-size: 11pt;
            font-weight: bold;
            color: #ffffff;
            background-color: <?= $brandColor ?>;
            padding: 8px 12px;
            margin: 25px 0 15px 0;
            text-transform: uppercase;
            border-radius: 4px;
        }

        /* TABLAS DE DATOS */
        .data-grid {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-grid td {
            padding: 8px 5px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: top;
        }
        .label {
            font-weight: bold;
            color: #475569;
            width: 30%;
        }
        .value {
            color: #0f172a;
            width: 70%;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 4px;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
        }
        .status-active { background-color: #dcfce7; color: #166534; }
        .status-inactive { background-color: #fee2e2; color: #991b1b; }

        /* RADAR SCORE */
        .score-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin-bottom: 20px;
        }
        .score-title {
            font-size: 10pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .score-value {
            font-size: 32pt;
            font-weight: bold;
            color: <?= $brandColor ?>;
        }
        .score-level {
            font-size: 12pt;
            font-weight: bold;
            margin-top: 5px;
        }
        .level-low { color: #16a34a; }
        .level-medium { color: #ca8a04; }
        .level-high { color: #dc2626; }

        /* BORME Y CARGOS */
        .list-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .list-table th {
            text-align: left;
            font-size: 8.5pt;
            color: #ffffff;
            padding: 6px 8px;
            background: <?= $brandColor ?>;
        }
        .list-table td {
            padding: 8px 8px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 8.5pt;
        }
        .borme-date {
            font-weight: bold;
            color: <?= $brandColor ?>;
            white-space: nowrap;
        }
        .borme-type {
            font-weight: bold;
            color: #1e293b;
        }
        .borme-desc {
            color: #475569;
            font-size: 8pt;
            line-height: 1.4;
        }

        footer {
            position: fixed;
            bottom: 20px;
            left: 45px;
            right: 45px;
            text-align: center;
            border-top: 1px solid #f1f5f9;
            padding-top: 10px;
        }
        .footer-text {
            font-size: 8pt;
            color: #94a3b8;
            font-weight: bold;
        }

        /* ADMINISTRATORS CARDS */
        .admin-cards { margin-top: 20px; }
        .admin-card {
            border-left: 4px solid <?= $brandColor ?>;
            background-color: #f8fafc;
            padding: 15px;
            margin-bottom: 10px;
            page-break-inside: avoid;
        }
        .admin-name { font-weight: bold; font-size: 11pt; color: #0f172a; margin-bottom: 3px; }
        .admin-position { font-size: 9pt; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; }

        /* BORME TIMELINE */
        .timeline { margin-top: 20px; padding-left: 10px; }
        .timeline-item {
            border-left: 2px solid #cbd5e1;
            padding-left: 20px;
            padding-bottom: 20px;
            position: relative;
            margin-bottom: 0;
        }
        .timeline-item::before {
            content: "";
            position: absolute;
            left: -6px;
            top: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background-color: <?= $brandColor ?>;
        }
        .timeline-date { font-weight: bold; font-size: 9pt; color: <?= $brandColor ?>; margin-bottom: 4px; }
        .timeline-title { font-weight: bold; font-size: 10pt; color: #0f172a; margin-bottom: 4px; }
        .timeline-desc { font-size: 8.5pt; color: #475569; }

        /* KPI & TABLES */
        .kpi-container {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .kpi-container td {
            width: 50%;
            padding: 15px;
            background-color: #f1f5f9;
            border: 2px solid #ffffff;
            text-align: center;
        }
        .kpi-title { font-size: 9pt; color: #64748b; text-transform: uppercase; font-weight: bold; margin-bottom: 5px; }
        .kpi-value { font-size: 18pt; font-weight: bold; color: <?= $brandColor ?>; }
        
        .clean-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .clean-table th {
            text-align: left;
            font-size: 8.5pt;
            color: #64748b;
            text-transform: uppercase;
            padding: 8px 5px;
            border-bottom: 2px solid <?= $brandColor ?>;
        }
        .clean-table td {
            padding: 10px 5px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 9pt;
            color: #334155;
            vertical-align: top;
        }

        .page-break { page-break-before: always; }
        .no-break { page-break-inside: avoid; }
    </style>
</head>
<body>
    <!-- PORTADA EDITORIAL PREMIUM -->
    <div class="cover-page">
        <!-- Marcos perimetrales -->
        <div class="cover-border-top"></div>
        <div class="cover-border-bottom"></div>
        <div class="cover-border-left"></div>
        <div class="cover-border-right"></div>
        
        <div class="cover-content">
            <div class="cover-logo-container">
                <?php if (!empty($brandLogoBase64)): ?>
                    <img src="<?= $brandLogoBase64 ?>" class="cover-logo" alt="Logo">
                <?php else: ?>
                    <div class="cover-brand-name"><?= esc($brandName) ?></div>
                <?php endif; ?>
            </div>

            <div class="cover-title-main">
                INFORME DE<br>
                <span class="cover-title-highlight">SALUD FINANCIERA</span><br>
                Y MERCANTIL
            </div>
            
            <div class="cover-divider"></div>

            <div class="cover-company-name"><?= esc($company['name'] ?? 'Empresa Desconocida') ?></div>
            
            <?php if(!empty($company['cif'])): ?>
                <div class="cover-company-cif">NIF/CIF: <?= esc($company['cif']) ?></div>
            <?php endif; ?>
        </div>
        
        <div class="cover-footer">
            <strong>Fecha de emisión:</strong> <?= date('d/m/Y') ?><br>
            <?= esc($brandFooterText) ?>
        </div>
        
        <div class="cover-qr">
            <?php if (!empty($qrBase64)): ?>
                <img src="<?= $qrBase64 ?>" alt="QR Code"><br>
            <?php endif; ?>
            <strong>Dossier Verificado</strong><br>
            Escanea para acceder a la<br>
            ficha digital en tiempo real
        </div>
    </div>

    <div class="page-break"></div>
    
    <!-- FOOTER FIJO PARA TODAS LAS PÁGINAS -->
    <footer>
        <div class="footer-text"><?= esc($brandFooterText) ?></div>
    </footer>

    <!-- PÁGINA 2: DATOS GENERALES Y SCORE -->
    <div class="header-bar"></div>
    <div class="container">
        <!-- HEADER INTERIOR -->
        <table class="header-table">
            <tr>
                <td class="logo-section">
                    <?php if (!empty($brandLogoBase64)): ?>
                        <img src="<?= $brandLogoBase64 ?>" class="logo-small" alt="Logo">
                    <?php else: ?>
                        <div class="logo-text-small"><?= esc($brandName) ?></div>
                    <?php endif; ?>
                </td>
                <td class="info-section">
                    <div class="report-title-small">Informe Mercantil</div>
                    <div class="report-date-small"><?= esc($company['name']) ?> · Ref: <?= strtoupper(substr(md5($company['id'] . time()), 0, 8)) ?></div>
                </td>
            </tr>
        </table>

        <!-- SCORE (SI EXISTE) -->
        <!-- SCORE (SI EXISTE) -->
        <?php if (!empty($radarScore)): ?>
        <div class="no-break">
            <div class="section-title">Índice de Dinamismo Comercial (Radar Score)</div>
            <table style="width: 100%;">
                <tr>
                    <td style="width: 40%; vertical-align: top; padding-right: 20px;">
                        <div class="score-box" style="background-color: <?= $radarScore['visuals']['bg'] ?? '#f1f5f9' ?>; border-color: <?= $radarScore['visuals']['color'] ?? '#94a3b8' ?>;">
                            <div class="score-title" style="color: <?= $radarScore['visuals']['color'] ?? '#94a3b8' ?>;">Score de Oportunidad</div>
                            <div class="score-value" style="color: <?= $radarScore['visuals']['color'] ?? '#94a3b8' ?>; font-size: 38pt;">
                                <?= (int)$radarScore['final_score'] ?><span style="font-size: 16pt; color: #64748b;">/100</span>
                            </div>
                            <div class="score-level" style="color: <?= $radarScore['visuals']['color'] ?? '#94a3b8' ?>;">
                                <?= $radarScore['visuals']['icon'] ?? '' ?> <?= esc($radarScore['visuals']['label'] ?? 'General') ?>
                            </div>
                        </div>
                    </td>
                    <td style="width: 60%; vertical-align: middle;">
                        <div style="font-size: 9.5pt; color: #334155; text-align: left;">
                            <strong style="font-size: 10.5pt;">Análisis de Dinamismo:</strong><br><br>
                            <?= esc($radarScore['explanation'] ?? 'Sin detalles adicionales.') ?><br><br>
                            <div style="width: 100%; background: #e2e8f0; height: 12px; border-radius: 6px; overflow: hidden; margin-top: 10px;">
                                <div style="width: <?= (int)($radarScore['final_score'] ?? 0) ?>%; background-color: <?= $radarScore['visuals']['color'] ?? '#94a3b8' ?>; height: 100%;"></div>
                            </div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>
        <?php endif; ?>

        <!-- IDENTIFICACIÓN -->
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
                    <td class="value">
                        <?php 
                            $dateStr = $company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? '-';
                            echo esc($dateStr);
                            
                            if ($dateStr !== '-' && !empty($dateStr)) {
                                $years = date('Y') - date('Y', strtotime($dateStr));
                                if ($years > 10) {
                                    echo ' &nbsp; <span style="background: #fef3c7; color: #d97706; padding: 2px 6px; border-radius: 4px; font-size: 7.5pt; font-weight: bold; vertical-align: middle;">Empresa Consolidada (+10 años)</span>';
                                } elseif ($years >= 3) {
                                    echo ' &nbsp; <span style="background: #e0f2fe; color: #0369a1; padding: 2px 6px; border-radius: 4px; font-size: 7.5pt; font-weight: bold; vertical-align: middle;">Empresa Estable</span>';
                                } elseif ($years > 0) {
                                    echo ' &nbsp; <span style="background: #dcfce7; color: #15803d; padding: 2px 6px; border-radius: 4px; font-size: 7.5pt; font-weight: bold; vertical-align: middle;">Startup / Reciente</span>';
                                }
                            }
                        ?>
                    </td>
                </tr>
            </table>
        </div>

        <!-- LOCALIZACIÓN -->
        <div class="no-break">
            <div class="section-title">Localización y Contacto</div>
            <table class="data-grid">
                <tr>
                    <td class="label">Domicilio Social</td>
                    <td class="value"><?= esc($company['address'] ?? '-') ?>, <?= esc($company['municipality'] ?? '-') ?> (<?= esc($company['province'] ?? $company['provincia'] ?? '-') ?>)</td>
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

        <!-- ACTIVIDAD -->
        <div class="no-break">
            <div class="section-title">Actividad y Objeto Social</div>
            <table class="data-grid">
                <tr>
                    <td class="label">CNAE 2009</td>
                    <td class="value"><?= esc($company['cnae'] ?? $company['cnae_code'] ?? '-') ?> · <?= esc($company['cnae_label'] ?? '-') ?></td>
                </tr>
                <tr>
                    <td class="label">Objeto Social</td>
                    <td class="value" style="font-size: 8pt; text-align: justify;"><?= esc($company['corporate_purpose'] ?? $company['objeto_social'] ?? '-') ?></td>
                </tr>
            </table>
        </div>
    </div>

    <!-- PÁGINA 3: ADMINISTRADORES Y BORME -->
    <?php if (!empty($administrators) || !empty($bormePosts)): ?>
    <div class="page-break"></div>
    <div class="header-bar"></div>
    <div class="container">
        
        <?php if (!empty($administrators)): ?>
        <div class="no-break">
            <div class="section-title">Estructura Corporativa y de Poder</div>
            
            <?php 
                $organos = [];
                $apoderados = [];
                foreach (array_slice($administrators, 0, 20) as $admin) {
                    $pos = strtolower($admin['position']);
                    if (strpos($pos, 'apoderado') !== false) {
                        $apoderados[] = $admin;
                    } else {
                        $organos[] = $admin;
                    }
                }
            ?>
            
            <?php if(!empty($organos)): ?>
            <div style="font-weight: bold; color: <?= $brandColor ?>; margin-bottom: 10px; font-size: 10pt; text-transform: uppercase;">Órganos de Gobierno</div>
            <div class="admin-cards" style="display: block; width: 100%;">
                <?php foreach ($organos as $admin): ?>
                <div class="admin-card" style="display: inline-block; width: 45%; margin-right: 2%; vertical-align: top;">
                    <div class="admin-name"><?= esc($admin['name']) ?></div>
                    <div class="admin-position"><?= esc($admin['position']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <?php if(!empty($apoderados)): ?>
            <div style="font-weight: bold; color: <?= $brandColor ?>; margin-bottom: 10px; margin-top: 20px; font-size: 10pt; text-transform: uppercase;">Apoderados</div>
            <div class="admin-cards" style="display: block; width: 100%;">
                <?php foreach ($apoderados as $admin): ?>
                <div class="admin-card" style="display: inline-block; width: 45%; margin-right: 2%; vertical-align: top; border-left-color: #94a3b8;">
                    <div class="admin-name"><?= esc($admin['name']) ?></div>
                    <div class="admin-position"><?= esc($admin['position']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($bormePosts)): ?>
        <div style="margin-top: 30px;">
            <div class="section-title">Actividad Registral (BORME)</div>
            
            <?php 
            $actCounts = [];
            $bormeTimeline = [];
            $totalActs = 0;
            foreach ($bormePosts as $post) {
                $monthYear = date('Y-m', strtotime($post['borme_date']));
                if (!isset($bormeTimeline[$monthYear])) $bormeTimeline[$monthYear] = ['count' => 0, 'types' => []];
                $bormeTimeline[$monthYear]['count']++;
                $types = array_map('trim', explode(',', strtolower($post['act_types'] ?? '')));
                foreach ($types as $t) {
                    if (empty($t)) continue;
                    if (strpos($t, 'nombramiento') !== false) $t = 'Nombramientos';
                    elseif (strpos($t, 'cese') !== false || strpos($t, 'dimision') !== false || strpos($t, 'revocacion') !== false) $t = 'Ceses/Dimisiones';
                    elseif (strpos($t, 'capital') !== false) $t = 'Modific. de Capital';
                    elseif (strpos($t, 'domicilio') !== false) $t = 'Cambio de Domicilio';
                    elseif (strpos($t, 'estatutos') !== false || strpos($t, 'objeto social') !== false) $t = 'Modific. Estatutos';
                    elseif (strpos($t, 'constitucion') !== false) $t = 'Constitución';
                    elseif (strpos($t, 'unipersonalidad') !== false) $t = 'Unipersonalidad';
                    elseif (strpos($t, 'cuentas') !== false) $t = 'Cuentas Anuales';
                    elseif (strpos($t, 'socio unico') !== false) $t = 'Socio Único';
                    else $t = 'Otros Actos';
                    
                    if (!isset($actCounts[$t])) $actCounts[$t] = 0;
                    $actCounts[$t]++;
                    $totalActs++;
                }
            }
            arsort($actCounts);
            ksort($bormeTimeline);
            $topActs = array_slice($actCounts, 0, 4, true);
            $maxActsTimeline = 1;
            foreach ($bormeTimeline as $data) {
                if ($data['count'] > $maxActsTimeline) $maxActsTimeline = $data['count'];
            }
            $monthsEs = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
            ?>
            <table style="width: 100%; border-collapse: separate; border-spacing: 10px; margin-left: -10px; margin-right: -10px; margin-bottom: 20px; page-break-inside: avoid;">
                <tr>
                    <!-- COL 1: Resumen IA -->
                    <td style="width: 33.3%; vertical-align: top; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px; background: #f8fafc;">
                        <div style="font-size: 10pt; font-weight: bold; color: <?= $brandColor ?>; margin-bottom: 10px;">
                            Resumen del BORME con IA
                        </div>
                        <div style="font-size: 8pt; color: #475569; line-height: 1.5;">
                            <?= !empty($company['ai_borme_summary']) ? nl2br(esc($company['ai_borme_summary'])) : 'No hay resumen disponible.' ?>
                        </div>
                    </td>

                    <!-- COL 2: Evolucion (Bar Chart) -->
                    <td style="width: 33.3%; vertical-align: top; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px;">
                        <div style="font-size: 9pt; font-weight: bold; color: #64748b; margin-bottom: 15px; text-transform: uppercase;">
                            Evolución de Actividad
                        </div>
                        <table style="width: 100%; border-collapse: collapse; height: 100px;">
                            <tr style="height: 70px;">
                                <?php foreach ($bormeTimeline as $my => $data): 
                                    $count = $data['count'];
                                    // Calculate height in pixels (max 60px) instead of % for DOMPDF compatibility
                                    $pixelHeight = max(($count / $maxActsTimeline) * 60, 5); 
                                ?>
                                <td style="vertical-align: bottom; text-align: center; padding: 0 2px; height: 70px;">
                                    <div style="font-size: 7pt; color: #64748b; margin-bottom: 2px;"><?= $count ?></div>
                                    <div style="background-color: <?= $brandColor ?>; width: 12px; margin: 0 auto; border-radius: 2px 2px 0 0; height: <?= $pixelHeight ?>px;"></div>
                                </td>
                                <?php endforeach; ?>
                            </tr>
                            <tr>
                                <?php foreach ($bormeTimeline as $my => $data): 
                                    list($y, $m) = explode('-', $my);
                                ?>
                                <td style="text-align: center; font-size: 6pt; color: #94a3b8; padding-top: 5px; line-height: 1.1;">
                                    <?= $monthsEs[$m] ?><br><?= substr($y, 2) ?>'
                                </td>
                                <?php endforeach; ?>
                            </tr>
                        </table>
                    </td>

                    <!-- COL 3: Distribucion -->
                    <td style="width: 33.3%; vertical-align: top; border: 1px solid #e2e8f0; border-radius: 8px; padding: 15px;">
                        <div style="font-size: 9pt; font-weight: bold; color: #64748b; margin-bottom: 15px; text-transform: uppercase;">
                            Distribución de Actos
                        </div>
                        <table style="width: 100%; border-collapse: collapse;">
                            <?php foreach ($topActs as $type => $count): 
                                $pct = $totalActs > 0 ? round(($count / $totalActs) * 100) : 0;
                            ?>
                            <tr>
                                <td style="font-size: 7pt; color: #475569; padding-bottom: 2px;"><?= esc($type) ?></td>
                                <td style="font-size: 7pt; color: #94a3b8; text-align: right; padding-bottom: 2px;"><?= $count ?> (<?= $pct ?>%)</td>
                            </tr>
                            <tr>
                                <td colspan="2" style="padding-bottom: 8px;">
                                    <div style="width: 100%; background: #f1f5f9; height: 5px; border-radius: 2px;">
                                        <div style="width: <?= $pct ?>%; background-color: <?= $brandColor ?>; height: 5px; border-radius: 2px;"></div>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </table>
                    </td>
                </tr>
            </table>
            
            <div class="timeline">
                <?php foreach (array_slice($bormePosts, 0, 15) as $post): ?>
                <div class="timeline-item">
                    <div class="timeline-date"><?= date('d/m/Y', strtotime($post['borme_date'])) ?></div>
                    <div class="timeline-title"><?= esc($post['act_types'] ?: 'Acto Registral') ?></div>
                    <div class="timeline-desc"><?= esc($post['description']) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- PÁGINA 4: CONTRATOS Y SUBVENCIONES -->
    <?php if (!empty($contracts) || !empty($subsidies)): ?>
    <div class="page-break"></div>
    <div class="header-bar"></div>
    <div class="container">
        
        <?php if (!empty($contracts)): ?>
        <?php 
            $totalContracts = count($contracts);
            $maxContractAmount = 0;
            foreach($contracts as $c) {
                if($c['importe_adjudicacion'] > $maxContractAmount) $maxContractAmount = $c['importe_adjudicacion'];
            }
        ?>
        <div>
            <div class="section-title">Licitaciones y Contratos Públicos</div>
            <table class="kpi-container">
                <tr>
                    <td>
                        <div class="kpi-title">Total Adjudicaciones</div>
                        <div class="kpi-value"><?= $totalContracts ?></div>
                    </td>
                    <td>
                        <div class="kpi-title">Mayor Importe Adjudicado</div>
                        <div class="kpi-value"><?= number_format($maxContractAmount, 0, ',', '.') ?> €</div>
                    </td>
                </tr>
            </table>

            <table class="clean-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Fecha</th>
                        <th style="width: 30%;">Órgano de Contratación</th>
                        <th style="width: 40%;">Título del Contrato</th>
                        <th style="width: 15%; text-align: right;">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($contracts, 0, 15) as $contract): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($contract['fecha_adjudicacion'])) ?></td>
                        <td><?= esc($contract['organo_contratacion']) ?></td>
                        <td><?= esc($contract['titulo_contrato']) ?></td>
                        <td style="text-align: right; font-weight: bold;"><?= number_format($contract['importe_adjudicacion'], 2, ',', '.') ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if (!empty($subsidies)): ?>
        <?php 
            $totalSubsidies = count($subsidies);
            $totalSubsidiesAmount = 0;
            foreach($subsidies as $s) {
                $totalSubsidiesAmount += $s['importe'];
            }
        ?>
        <div style="margin-top: 40px;" class="no-break">
            <div class="section-title">Subvenciones y Ayudas Concedidas</div>
            <table class="kpi-container">
                <tr>
                    <td>
                        <div class="kpi-title">Total Subvenciones</div>
                        <div class="kpi-value"><?= $totalSubsidies ?></div>
                    </td>
                    <td>
                        <div class="kpi-title">Importe Total Acumulado</div>
                        <div class="kpi-value"><?= number_format($totalSubsidiesAmount, 0, ',', '.') ?> €</div>
                    </td>
                </tr>
            </table>

            <table class="clean-table">
                <thead>
                    <tr>
                        <th style="width: 15%;">Fecha</th>
                        <th style="width: 35%;">Convocatoria / Órgano</th>
                        <th style="width: 35%;">Instrumento</th>
                        <th style="width: 15%; text-align: right;">Importe</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($subsidies, 0, 15) as $subsidy): ?>
                    <tr>
                        <td><?= date('d/m/Y', strtotime($subsidy['fecha_concesion'])) ?></td>
                        <td><?= esc($subsidy['convocatoria']) ?></td>
                        <td><?= esc($subsidy['instrumento']) ?></td>
                        <td style="text-align: right; font-weight: bold; color: <?= $brandColor ?>;"><?= number_format($subsidy['importe'], 2, ',', '.') ?> €</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

</body>
</html>
