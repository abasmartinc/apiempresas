<?php
    $brandColor = $brandColor ?? '#0f172a';
    $brandName = $brandName ?? 'APIEmpresas';
    $brandFooterText = $brandFooterText ?? 'Datos que impulsan decisiones';
    
    // Check default logo if none provided
    if (empty($brandLogoBase64)) {
        $baseDir = defined('FCPATH') ? FCPATH : (dirname(__DIR__, 3) . '/public/');
        $defaultLogoFile = $baseDir . 'images/logo.png';
        if (file_exists($defaultLogoFile)) {
            $brandLogoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($defaultLogoFile));
        }
    }

    // Score & Severity config
    $score = (int)($riskProfile['risk_score'] ?? 50);
    if ($score < 30) {
        $color = '#16a34a'; // Verde
        $bgScoreLight = '#f0fdf4';
        $borderScore = '#bbf7d0';
        $label = 'BAJO';
    } elseif ($score < 70) {
        $color = '#d97706'; // Naranja
        $bgScoreLight = '#fffbeb';
        $borderScore = '#fde68a';
        $label = 'MEDIO';
    } else {
        $color = '#dc2626'; // Rojo
        $bgScoreLight = '#fef2f2';
        $borderScore = '#fecaca';
        $label = 'ALTO';
    }
    $riskLevelText = $riskProfile['data']['risk_level'] ?? $label;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dictamen de Riesgo y Solvencia - <?= esc($company['name'] ?? 'Empresa') ?></title>
    <style>
        @page {
            margin: 28px 36px 28px 36px;
            size: A4 portrait;
        }
        * {
            box-sizing: border-box;
            font-family: 'Helvetica', 'Arial', sans-serif !important;
        }
        body { 
            color: #334155; 
            line-height: 1.35; 
            font-size: 8.5pt;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        
        /* HEADER */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .header-table td { vertical-align: middle; }
        .logo-section { width: 44%; }
        .info-section { width: 56%; text-align: right; }
        
        .report-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0b1c40;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.2;
        }
        .report-subtitle {
            font-size: 9pt;
            color: #1e293b;
            margin-top: 3px;
            font-weight: bold;
        }
        .report-date {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 4px;
        }

        .header-rule {
            width: 100%;
            height: 2px;
            background-color: #2563eb;
            margin-bottom: 16px;
        }

        /* COMPANY IDENTITY CARD */
        .company-card {
            width: 100%;
            background-color: #f0f7ff;
            border: 1px solid #dbeafe;
            border-radius: 10px;
            margin-bottom: 14px;
            border-collapse: collapse;
        }
        .company-card td {
            padding: 12px 14px;
            vertical-align: middle;
        }
        .company-name-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0b1c40;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8pt;
        }
        .meta-table td {
            padding: 2px 4px;
            vertical-align: top;
        }
        .meta-label {
            color: #64748b;
            font-weight: bold;
            width: 16%;
        }
        .meta-value {
            color: #0f172a;
            width: 34%;
        }

        .status-pill {
            display: inline-block;
            padding: 3px 10px;
            border-radius: 20px;
            font-size: 7.5pt;
            font-weight: bold;
            letter-spacing: 0.3px;
        }
        .status-pill-active {
            background-color: #dcfce7;
            color: #15803d;
            border: 1px solid #bbf7d0;
        }
        .status-pill-inactive {
            background-color: #fee2e2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* MAIN 2 COLUMNS: SCORE & FACTORS */
        .columns-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }
        .score-col {
            width: 34%;
            vertical-align: top;
            padding-right: 12px;
        }
        .factors-col {
            width: 66%;
            vertical-align: top;
            padding-left: 6px;
        }

        .panel-box {
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            background-color: #ffffff;
            padding: 16px 14px;
        }

        /* SCORE HERO BADGE (Diseño moderno tipo Dashboard) */
        .score-hero-card {
            background-color: <?= $bgScoreLight ?>;
            border: 1px solid <?= $borderScore ?>;
            border-radius: 12px;
            padding: 16px 12px 14px 12px;
            margin: 12px auto 14px auto;
            text-align: center;
        }
        .score-big-number {
            font-size: 40pt;
            font-weight: bold;
            color: <?= $color ?>;
            line-height: 1;
            margin: 0;
            letter-spacing: -1px;
        }
        .score-scale-denom {
            font-size: 9pt;
            color: #94a3b8;
            font-weight: bold;
            margin-top: 2px;
            margin-bottom: 10px;
        }
        .score-pill-tag {
            display: inline-block;
            background-color: <?= $color ?>;
            color: #ffffff;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 4px 14px;
            border-radius: 20px;
        }

        /* BARRA DE PROGRESSO DEL SCORE (0-100) */
        .score-track-bar {
            width: 100%;
            height: 6px;
            background-color: #e2e8f0;
            border-radius: 3px;
            margin: 10px 0 6px 0;
        }
        .score-track-fill {
            height: 6px;
            background-color: <?= $color ?>;
            border-radius: 3px;
            width: <?= max(5, min(100, $score)) ?>%;
        }

        /* DICTAMEN CARD */
        .dictamen-box {
            background-color: <?= $bgScoreLight ?>;
            border: 1px solid <?= $borderScore ?>;
            border-radius: 8px;
            padding: 8px 10px;
            text-align: left;
        }
        .dictamen-title {
            font-size: 8pt;
            font-weight: bold;
            color: #0b1c40;
            margin-bottom: 2px;
        }
        .dictamen-desc {
            font-size: 7.4pt;
            color: #475569;
            line-height: 1.35;
        }

        /* FACTOR CARDS */
        .factor-card {
            width: 100%;
            border-collapse: collapse;
            background-color: #fffafa;
            border: 1px solid #fecaca;
            border-left: 4px solid #ef4444;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .factor-card td {
            padding: 8px 10px;
            vertical-align: middle;
        }
        .factor-card-caution {
            background-color: #fffdf5;
            border-color: #fde68a;
            border-left-color: #f59e0b;
        }
        .factor-card-ok {
            background-color: #f0fdf4;
            border-color: #bbf7d0;
            border-left-color: #22c55e;
        }

        .factor-icon-badge {
            width: 24px;
            height: 24px;
            border-radius: 12px;
            background-color: #fee2e2;
            color: #dc2626;
            text-align: center;
            line-height: 24px;
            font-weight: bold;
            font-size: 10pt;
            display: inline-block;
        }
        .factor-icon-badge-caution {
            background-color: #fef3c7;
            color: #d97706;
        }
        .factor-icon-badge-ok {
            background-color: #dcfce7;
            color: #16a34a;
        }

        .factor-pill-badge {
            font-size: 6.8pt;
            font-weight: bold;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            display: inline-block;
            background-color: #fee2e2;
            color: #991b1b;
        }
        .factor-pill-badge-caution {
            background-color: #fef3c7;
            color: #92400e;
        }
        .factor-pill-badge-ok {
            background-color: #dcfce7;
            color: #166534;
        }

        /* BOTTOM BOXES */
        .bottom-card {
            width: 100%;
            border-collapse: collapse;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            margin-bottom: 8px;
        }
        .bottom-card td {
            padding: 8px 12px;
            vertical-align: top;
        }

        /* FOOTER */
        .footer-rule {
            width: 100%;
            height: 1px;
            background-color: #e2e8f0;
            margin-top: 14px;
            margin-bottom: 8px;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7.5pt;
            color: #64748b;
        }
        .footer-table td {
            vertical-align: middle;
        }
    </style>
</head>
<body>

    <!-- HEADER -->
    <table class="header-table">
        <tr>
            <td class="logo-section">
                <?php if (!empty($brandLogoBase64)): ?>
                    <img src="<?= $brandLogoBase64 ?>" style="max-height: 36px; max-width: 180px; display: block;">
                    <div style="font-size: 6pt; color: #64748b; letter-spacing: 1.5px; margin-top: 3px; font-weight: bold; text-transform: uppercase;">
                        INFORMACIÓN EMPRESARIAL DE CONFIANZA
                    </div>
                <?php else: ?>
                    <div style="font-size: 18pt; font-weight: bold; color: #0b1c40; line-height: 1;">
                        API<span style="color: #2563eb;">Empresas</span>
                    </div>
                    <div style="font-size: 6pt; color: #64748b; letter-spacing: 1.5px; margin-top: 3px; font-weight: bold; text-transform: uppercase;">
                        INFORMACIÓN EMPRESARIAL DE CONFIANZA
                    </div>
                <?php endif; ?>
            </td>
            <td class="info-section">
                <div class="report-title">DICTAMEN DE RIESGO Y SOLVENCIA</div>
                <div class="report-subtitle">Índice de Estabilidad Societaria y Alertas BORME</div>
                <div class="report-date">
                    Fecha de emisión: <?= date('d/m/Y H:i') ?> &nbsp;|&nbsp; Ref: <?= strtoupper(substr(md5(($company['id'] ?? '1') . '-' . date('Ymd')), 0, 10)) ?>
                </div>
            </td>
        </tr>
    </table>

    <div class="header-rule"></div>

    <!-- COMPANY IDENTITY CARD -->
    <table class="company-card">
        <tr>
            <td>
                <div class="company-name-title"><?= esc($company['name'] ?? 'Empresa') ?></div>
                <table class="meta-table">
                    <tr>
                        <td style="width: 14%;" class="meta-label">CIF / NIF</td>
                        <td style="width: 32%;" class="meta-value"><strong><?= esc($company['cif'] ?? $company['nif'] ?? 'No disponible') ?></strong></td>
                        <td style="width: 20%;" class="meta-label">Estado Registral</td>
                        <td style="width: 34%;" class="meta-value">
                            <?php 
                            $statusStr = strtoupper($company['estado'] ?? $company['status'] ?? 'ACTIVA');
                            $isDefunct = (strpos($statusStr, 'EXTIN') !== false || strpos($statusStr, 'DISUEL') !== false || strpos($statusStr, 'BAJA') !== false || strpos($statusStr, 'CIERRE') !== false);
                            ?>
                            <span class="status-pill <?= $isDefunct ? 'status-pill-inactive' : 'status-pill-active' ?>">
                                &bull; <?= esc($statusStr) ?>
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td style="width: 14%;" class="meta-label">Domicilio</td>
                        <td style="width: 32%;" class="meta-value">
                            <?php
                            $addr = $company['address'] ?? '';
                            if (empty($addr) && (!empty($company['municipality']) || !empty($company['province']))) {
                                $addr = trim(($company['municipality'] ?? '') . ' (' . ($company['province'] ?? '') . ')', ' ()');
                            }
                            echo esc($addr ?: 'No disponible');
                            ?>
                        </td>
                        <td style="width: 20%;" class="meta-label">Actividad (CNAE)</td>
                        <td style="width: 34%;" class="meta-value">
                            <?php
                            $cnaeCode = $company['cnae_code'] ?? $company['cnae'] ?? '';
                            $cnaeDesc = $company['cnae_name'] ?? $company['cnae_label'] ?? 'No especificado';
                            echo esc($cnaeCode ? "{$cnaeCode} - {$cnaeDesc}" : $cnaeDesc);
                            ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- MAIN 2 COLUMNS -->
    <table class="columns-table">
        <tr>
            <!-- LEFT: SCORE PANEL -->
            <td class="score-col">
                <div class="panel-box" style="text-align: center;">
                    <div style="font-size: 8.5pt; color: #475569; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px;">
                        NIVEL DE RIESGO
                    </div>

                    <!-- MODERN SCORE HERO CARD -->
                    <div class="score-hero-card">
                        <div class="score-big-number"><?= $score ?></div>
                        <div class="score-scale-denom">de 100 puntos</div>
                        
                        <div class="score-track-bar">
                            <div class="score-track-fill"></div>
                        </div>

                        <div style="margin-top: 10px;">
                            <span class="score-pill-tag"><?= esc($riskLevelText) ?></span>
                        </div>
                    </div>

                    <!-- DICTAMEN -->
                    <div class="dictamen-box">
                        <div class="dictamen-title">Dictamen</div>
                        <div class="dictamen-desc">
                            <?= esc($riskProfile['data']['summary_message'] ?? 'Constan publicaciones de estabilidad conforme a fuentes oficiales vigentes.') ?>
                        </div>
                    </div>
                </div>
            </td>

            <!-- RIGHT: FACTORS ANALYZED -->
            <td class="factors-col">
                <div class="panel-box">
                    <div style="font-size: 10.5pt; font-weight: bold; color: #0b1c40; text-transform: uppercase; margin-bottom: 2px;">
                        FACTORES ANALIZADOS
                    </div>
                    <div style="font-size: 7.6pt; color: #64748b; margin-bottom: 12px;">
                        Evaluación automática de los principales indicadores de estabilidad corporativa.
                    </div>

                    <?php 
                    $flags = $riskProfile['data']['canonical_events'] ?? $riskProfile['data']['flags'] ?? [];
                    ?>
                    <?php if (!empty($flags)): ?>
                        <?php foreach (array_slice($flags, 0, 4) as $flag): ?>
                            <?php 
                            $sev = $flag['severity'] ?? 'low';
                            if ($sev === 'high') {
                                $cClass = '';
                                $iClass = '';
                                $pClass = '';
                                $sevLabel = 'ALERTA';
                                $symbol = '!';
                            } elseif ($sev === 'medium') {
                                $cClass = 'factor-card-caution';
                                $iClass = 'factor-icon-badge-caution';
                                $pClass = 'factor-pill-badge-caution';
                                $sevLabel = 'ATENCIÓN';
                                $symbol = '!';
                            } else {
                                $cClass = 'factor-card-ok';
                                $iClass = 'factor-icon-badge-ok';
                                $pClass = 'factor-pill-badge-ok';
                                $sevLabel = 'POSITIVO';
                                $symbol = '✓';
                            }

                            $flagTitle = $flag['code'] ?? 'Evento Registral';
                            $flagTitle = ucwords(strtolower(str_replace(['_', '-'], ' ', $flagTitle)));
                            ?>
                            <table class="factor-card <?= $cClass ?>">
                                <tr>
                                    <td style="width: 30px; text-align: center;">
                                        <div class="factor-icon-badge <?= $iClass ?>"><?= $symbol ?></div>
                                    </td>
                                    <td>
                                        <div style="font-size: 8.2pt; font-weight: bold; color: #0b1c40; margin-bottom: 1px;">
                                            <?= esc($flagTitle) ?>
                                        </div>
                                        <div style="font-size: 7.4pt; color: #475569; line-height: 1.3;">
                                            <?= esc($flag['description'] ?? 'Registro mercantil verificado.') ?>
                                        </div>
                                    </td>
                                    <td style="width: 58px; text-align: right;">
                                        <span class="factor-pill-badge <?= $pClass ?>">
                                            <?= $sevLabel ?>
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <table class="factor-card factor-card-ok">
                            <tr>
                                <td style="width: 30px; text-align: center;">
                                    <div class="factor-icon-badge factor-icon-badge-ok">✓</div>
                                </td>
                                <td>
                                    <div style="font-size: 8.2pt; font-weight: bold; color: #0b1c40; margin-bottom: 1px;">Sin incidencias concursales detectadas</div>
                                    <div style="font-size: 7.4pt; color: #475569;">No constan quiebras, disoluciones ni revocaciones publicadas en el BORME.</div>
                                </td>
                                <td style="width: 65px; text-align: right;">
                                    <span class="factor-pill-badge factor-pill-badge-ok">FAVORABLE</span>
                                </td>
                            </tr>
                        </table>
                    <?php endif; ?>

                    <!-- Institutional solvency -->
                    <?php if (!empty($contracts) || !empty($subsidies)): ?>
                        <div style="margin-top: 6px; background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px; padding: 6px 10px;">
                            <div style="font-size: 7.5pt; font-weight: bold; color: #1e3a8a; margin-bottom: 1px;">
                                Indicadores de Solvencia Institucional:
                            </div>
                            <div style="font-size: 7.2pt; color: #2563eb; line-height: 1.3;">
                                <?php if (!empty($contracts)): ?>
                                    &bull; Adjudicatario en <strong><?= count($contracts) ?></strong> contrato(s) del sector público.<br>
                                <?php endif; ?>
                                <?php if (!empty($subsidies)): ?>
                                    &bull; Beneficiario de <strong><?= count($subsidies) ?></strong> subvención(es) o ayuda(s) del Estado.
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>

    <!-- METHODOLOGY -->
    <table class="bottom-card">
        <tr>
            <td>
                <div style="font-size: 8pt; font-weight: bold; color: #0b1c40; margin-bottom: 2px;">
                    Metodología del Índice de Estabilidad Societaria (IES)
                </div>
                <div style="font-size: 7.3pt; color: #64748b; line-height: 1.35;">
                    El algoritmo evalúa en tiempo real 6 dimensiones objetivas: (1) Estado legal y mercantil, (2) Regularidad en actos del BORME, (3) Estabilidad del órgano de administración, (4) Capital social suscrito, (5) Volatilidad en cambios societarios y (6) Factores compensatorios (contratación pública y ayudas). La escala asigna de 0 (máxima estabilidad) a 100 (máximo riesgo operativo).
                </div>
            </td>
        </tr>
    </table>

    <!-- LEGAL NOTICE -->
    <table class="bottom-card">
        <tr>
            <td>
                <div style="font-size: 8pt; font-weight: bold; color: #0b1c40; margin-bottom: 2px;">
                    Aviso de Responsabilidad Legal
                </div>
                <div style="font-size: 7.3pt; color: #64748b; line-height: 1.35;">
                    Este informe es una estimación estadística y algorítmica generada automáticamente a partir de registros mercantiles, publicaciones del Boletín Oficial del Registro Mercantil (BORME) y bases de datos públicas. No constituye asesoramiento legal, financiero ni una calificación crediticia regulada. Documento confidencial generado por <?= esc($brandName) ?>.
                </div>
            </td>
        </tr>
    </table>

    <!-- FOOTER -->
    <div class="footer-rule"></div>
    <table class="footer-table">
        <tr>
            <td style="width: 50%; text-align: left;">
                <strong style="color: #0b1c40; font-size: 8pt;">API<span style="color: #2563eb;">Empresas</span></strong>
                &nbsp;|&nbsp;
                <span><?= esc($brandFooterText) ?></span>
            </td>
            <td style="width: 50%; text-align: right; color: #2563eb; font-weight: bold;">
                www.apiempresas.es
            </td>
        </tr>
    </table>

</body>
</html>
