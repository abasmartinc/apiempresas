<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Informe de Riesgo - <?= esc($company['name']) ?></title>
    <style>
        <?php
            $brandColor = $brandColor ?? '#0f172a';
            $brandName = $brandName ?? 'APIEmpresas';
            $brandFooterText = $brandFooterText ?? 'Documento generado por ' . esc($brandName);
            
            // Check default logo if none provided
            if (empty($brandLogoBase64)) {
                $baseDir = defined('FCPATH') ? FCPATH : (dirname(__DIR__, 3) . '/public/');
                $defaultLogoFile = $baseDir . 'images/logo.png';
                if (file_exists($defaultLogoFile)) {
                    $brandLogoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($defaultLogoFile));
                }
            }
        ?>
        @page {
            margin: 0;
            size: A4 portrait;
        }
        * {
            box-sizing: border-box;
            font-family: 'Helvetica', 'Arial', sans-serif !important;
        }
        body { 
            font-family: 'Helvetica', 'Arial', sans-serif !important; 
            color: #334155; 
            line-height: 1.4; 
            font-size: 8.5pt;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        
        .header-bar {
            height: 6px;
            background-color: #0f172a;
            width: 100%;
        }
        
        .container {
            padding: 24px 34px;
        }
        
        /* HEADER */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 12px;
        }
        .header-table td { vertical-align: middle; }
        .logo-section { width: 40%; }
        .info-section { width: 60%; text-align: right; }
        
        .report-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .report-subtitle {
            font-size: 8.5pt;
            color: #2563eb;
            margin-top: 2px;
            font-weight: bold;
        }
        .report-date {
            font-size: 7.5pt;
            color: #94a3b8;
            margin-top: 2px;
        }

        /* COMPANY IDENTITY BOX */
        .company-id-box {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #2563eb;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 18px;
        }
        .company-name-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        .meta-table td {
            padding: 2px 0;
            vertical-align: top;
        }
        .meta-label {
            color: #64748b;
            font-weight: bold;
            width: 18%;
        }
        .meta-value {
            color: #1e293b;
            width: 32%;
        }

        /* IES RISK CONTAINER (Exact match to Premium PDF & Web) */
        .ies-container {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }
        .ies-score-col {
            width: 33%;
            vertical-align: top;
            padding-right: 18px;
        }
        .ies-factors-col {
            width: 67%;
            vertical-align: top;
        }

        .ies-score-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px 12px;
            text-align: center;
        }
        .ies-score-badge {
            font-size: 34pt;
            font-weight: bold;
            color: #ffffff;
            padding: 10px 18px;
            border-radius: 12px;
            display: inline-block;
            margin-bottom: 12px;
            min-width: 65px;
            line-height: 1;
        }
        .ies-factor-card {
            border: 1px solid #e2e8f0;
            background-color: #ffffff;
            border-radius: 8px;
            padding: 8px 10px;
            margin-bottom: 8px;
            width: 100%;
            border-collapse: collapse;
        }
        .ies-factor-icon {
            width: 28px;
            text-align: center;
            vertical-align: middle;
        }
        .ies-factor-icon-circle {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            line-height: 22px;
            text-align: center;
            font-weight: bold;
            font-size: 10pt;
            display: inline-block;
        }
        .ies-factor-text {
            vertical-align: middle;
            padding-left: 8px;
        }
        .ies-factor-badge-col {
            width: 65px;
            vertical-align: middle;
            text-align: right;
        }
        .ies-badge {
            font-size: 6.5pt;
            font-weight: bold;
            padding: 3px 6px;
            border-radius: 6px;
            text-transform: uppercase;
            display: inline-block;
        }

        /* METHODOLOGY & LEGAL */
        .methodology-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            margin-top: 14px;
            font-size: 7.5pt;
            color: #475569;
            line-height: 1.35;
        }
        
        .footer-table {
            position: absolute;
            bottom: 20px;
            left: 34px;
            right: 34px;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            font-size: 7pt;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="header-bar"></div>
    
    <div class="container">
        
        <!-- HEADER -->
        <table class="header-table">
            <tr>
                <td class="logo-section">
                    <?php if (!empty($brandLogoBase64)): ?>
                        <img src="<?= $brandLogoBase64 ?>" style="max-height: 38px; max-width: 190px; display: block;">
                    <?php else: ?>
                        <div style="font-size: 18pt; font-weight: bold; color: #0f172a; letter-spacing: -0.5px; line-height: 1;">
                            API<span style="color: #2563eb;">Empresas</span>
                        </div>
                    <?php endif; ?>
                </td>
                <td class="info-section">
                    <div class="report-title">Dictamen de Riesgo y Solvencia</div>
                    <div class="report-subtitle">Índice de Estabilidad Societaria y Alertas BORME</div>
                    <div class="report-date">Fecha de emisión: <?= date('d/m/Y H:i') ?> | Ref: <?= strtoupper(substr(md5($company['id'] . '-' . date('Ymd')), 0, 10)) ?></div>
                </td>
            </tr>
        </table>

        <!-- COMPANY IDENTITY -->
        <div class="company-id-box">
            <div class="company-name-title"><?= esc($company['name']) ?></div>
            <table class="meta-table">
                <tr>
                    <td class="meta-label">CIF / NIF:</td>
                    <td class="meta-value"><strong><?= esc($company['cif'] ?? $company['nif'] ?? 'No disponible') ?></strong></td>
                    <td class="meta-label">Estado Registral:</td>
                    <td class="meta-value">
                        <?php 
                        $statusStr = strtoupper($company['estado'] ?? 'ACTIVA');
                        $isDefunct = (strpos($statusStr, 'EXTIN') !== false || strpos($statusStr, 'DISUEL') !== false || strpos($statusStr, 'BAJA') !== false);
                        ?>
                        <strong style="color: <?= $isDefunct ? '#ef4444' : '#166534' ?>;"><?= esc($statusStr) ?></strong>
                    </td>
                </tr>
                <tr>
                    <td class="meta-label">Domicilio:</td>
                    <td class="meta-value"><?= esc($company['address'] ?? ($company['municipality'] . ', ' . $company['province'])) ?></td>
                    <td class="meta-label">Actividad (CNAE):</td>
                    <td class="meta-value"><?= esc($company['cnae_code'] ?? '') ?> - <?= esc($company['cnae_name'] ?? 'No especificado') ?></td>
                </tr>
            </table>
        </div>

        <?php 
        $score = (int)($riskProfile['risk_score'] ?? 50);
        if ($score < 30) {
            $color = '#22c55e'; // Verde
            $label = 'BAJO';
        } elseif ($score < 70) {
            $color = '#f59e0b'; // Naranja
            $label = 'MEDIO';
        } else {
            $color = '#ef4444'; // Rojo
            $label = 'ALTO';
        }
        $riskLevelText = $riskProfile['data']['risk_level'] ?? $label;
        ?>

        <!-- IES RISK SECTION (Identical to Web & Premium PDF) -->
        <table class="ies-container">
            <tr>
                <!-- LEFT: SCORE BOX -->
                <td class="ies-score-col">
                    <div class="ies-score-box">
                        <div style="font-size: 8.5pt; color: #64748b; font-weight: bold; text-transform: uppercase; margin-bottom: 12px;">Nivel de Riesgo</div>
                        <div class="ies-score-badge" style="background-color: <?= $color ?>;">
                            <?= $score ?>
                        </div>
                        <div style="font-size: 13pt; font-weight: bold; color: <?= $color ?>; text-transform: uppercase; margin-bottom: 4px;"><?= esc($riskLevelText) ?></div>
                        <div style="font-size: 8pt; color: #64748b;">Puntuación de 0 a 100</div>

                        <?php if (!empty($riskProfile['data']['summary_message'])): ?>
                            <div style="margin-top: 14px; text-align: left; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 6px; padding: 8px 10px; font-size: 7.5pt; color: #475569; line-height: 1.35;">
                                <strong style="color: #0f172a;">Dictamen:</strong> <?= esc($riskProfile['data']['summary_message']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </td>

                <!-- RIGHT: FACTORS ANALYZED -->
                <td class="ies-factors-col">
                    <div style="font-size: 10pt; font-weight: bold; color: #0f172a; margin-bottom: 4px; text-transform: uppercase;">Factores Analizados</div>
                    <div style="font-size: 8pt; color: #64748b; margin-bottom: 12px;">Evaluación automática de los principales indicadores de estabilidad corporativa.</div>
                    
                    <?php 
                    $flags = $riskProfile['data']['canonical_events'] ?? $riskProfile['data']['flags'] ?? [];
                    ?>
                    <?php if (!empty($flags)): ?>
                        <?php foreach (array_slice($flags, 0, 5) as $flag): ?>
                            <?php 
                            $sev = $flag['severity'] ?? 'low';
                            if ($sev === 'high') {
                                $iconColor = '#ef4444'; $iconBg = '#fee2e2';
                                $badgeColor = '#b91c1c'; $badgeBg = '#fef2f2'; $badgeBorder = '#fecaca';
                                $sevLabel = 'ALERTA'; $iconSymbol = '!';
                            } elseif ($sev === 'medium') {
                                $iconColor = '#f59e0b'; $iconBg = '#fef3c7';
                                $badgeColor = '#b45309'; $badgeBg = '#fffbeb'; $badgeBorder = '#fde68a';
                                $sevLabel = 'ATENCIÓN'; $iconSymbol = '?';
                            } else {
                                $iconColor = '#22c55e'; $iconBg = '#dcfce7';
                                $badgeColor = '#15803d'; $badgeBg = '#f0fdf4'; $badgeBorder = '#bbf7d0';
                                $sevLabel = 'POSITIVO'; $iconSymbol = '✓';
                            }
                            ?>
                            <table class="ies-factor-card">
                                <tr>
                                    <td class="ies-factor-icon">
                                        <div class="ies-factor-icon-circle" style="background-color: <?= $iconBg ?>; color: <?= $iconColor ?>;">
                                            <?= $iconSymbol ?>
                                        </div>
                                    </td>
                                    <td class="ies-factor-text">
                                        <div style="font-size: 8.5pt; font-weight: bold; color: #0f172a; margin-bottom: 2px;">
                                            <?= esc(ucwords(strtolower(str_replace('_', ' ', $flag['code'] ?? 'EVENTO REPORTADO')))) ?>
                                        </div>
                                        <div style="font-size: 7.5pt; color: #64748b; line-height: 1.3;">
                                            <?= esc($flag['description'] ?? 'Registro oficial verificado') ?>
                                        </div>
                                    </td>
                                    <td class="ies-factor-badge-col">
                                        <span class="ies-badge" style="background-color: <?= $badgeBg ?>; color: <?= $badgeColor ?>; border: 1px solid <?= $badgeBorder ?>;">
                                            <?= $sevLabel ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <table class="ies-factor-card">
                            <tr>
                                <td class="ies-factor-icon">
                                    <div class="ies-factor-icon-circle" style="background-color: #dcfce7; color: #22c55e;">✓</div>
                                </td>
                                <td class="ies-factor-text">
                                    <div style="font-size: 8.5pt; font-weight: bold; color: #0f172a; margin-bottom: 2px;">Sin eventos de riesgo detectados</div>
                                    <div style="font-size: 7.5pt; color: #64748b;">No constan procesos concursales, disoluciones o cierres registrales publicados en el BORME.</div>
                                </td>
                                <td class="ies-factor-badge-col">
                                    <span class="ies-badge" style="background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0;">FAVORABLE</span>
                                </td>
                            </tr>
                        </table>
                    <?php endif; ?>

                    <!-- INDICADORES DE CONTRATACIÓN Y SUBVENCIONES -->
                    <?php if (!empty($contracts) || !empty($subsidies)): ?>
                        <div style="margin-top: 8px; background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 6px 10px;">
                            <div style="font-size: 7.5pt; font-weight: bold; color: #1e3a8a; margin-bottom: 2px;">
                                Indicadores de Solvencia Institucional:
                            </div>
                            <div style="font-size: 7pt; color: #2563eb;">
                                <?php if (!empty($contracts)): ?>
                                    &bull; Adjudicatario en <strong><?= count($contracts) ?></strong> contrato(s) del sector público.<br>
                                <?php endif; ?>
                                <?php if (!empty($subsidies)): ?>
                                    &bull; Beneficiario de <strong><?= count($subsidies) ?></strong> subvención(es) o ayuda(s) oficiales.
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <!-- METHODOLOGY & DIMENSIONS -->
        <div class="methodology-box">
            <strong style="color: #0f172a; display: block; margin-bottom: 3px;">Metodología del Índice de Estabilidad Societaria (IES):</strong>
            El algoritmo evalúa en tiempo real 6 dimensiones objetivas: (1) Estado legal y mercantil, (2) Regularidad en actos del BORME, (3) Estabilidad del órgano de administración, (4) Capital social suscrito, (5) Volatilidad en cambios societarios y (6) Factores compensatorios (contratación pública y ayudas). La escala asigna de 0 (máxima estabilidad) a 100 (máximo riesgo operativo).
        </div>

        <div style="margin-top: 10px; font-size: 7pt; color: #64748b; line-height: 1.35; padding: 6px 8px; border-left: 3px solid #cbd5e1; background: #fafafa;">
            <strong>Aviso de Responsabilidad Legal:</strong> Este informe es una estimación estadística y algorítmica generada automáticamente a partir de registros mercantiles, publicaciones del Boletín Oficial del Registro Mercantil (BORME) y bases de datos públicas. No constituye asesoramiento legal, financiero ni una calificación crediticia regulada. <?= esc($brandFooterText) ?>
        </div>

        <!-- FOOTER -->
        <table class="footer-table">
            <tr>
                <td style="width: 70%;">
                    <?= esc($brandFooterText) ?> &bull; Documento generado electrónicamente.
                </td>
                <td style="width: 30%; text-align: right;">
                    Página 1 de 1
                </td>
            </tr>
        </table>

    </div>
</body>
</html>
