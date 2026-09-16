<?php
    // Mismos helpers que la ficha: si no, el PDF —que es el producto de pago—
    // sale con los códigos del motor en inglés y la razón social en MAYÚSCULAS.
    helper(['risk_labels', 'company']);

    $companyNamePdf = company_display_name($company['name'] ?? '', 'Empresa');

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

    // Score & Severity config.
    // El corte de ALTO era 70 aquí y 60 en el motor: el PDF de pago podía decir
    // "ALTO" en el texto y pintarlo de naranja de "MEDIO".
    $score = (int)($riskProfile['risk_score'] ?? 50);
    $umbralMedioPdf = (int) solvencia('umbralMedio', 30);
    $umbralAltoPdf  = (int) solvencia('umbralAlto', 60);
    if ($score < $umbralMedioPdf) {
        $color = '#16a34a'; // Verde
        $bgScoreLight = '#f0fdf4';
        $borderScore = '#bbf7d0';
        $label = 'BAJO';
    } elseif ($score < $umbralAltoPdf) {
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

    // Color de acento del documento (filete de cabecera, numeración de secciones).
    // No es $brandColor: ese vale por defecto '#0f172a' porque el pedido no recoge
    // color, y un acento azul marino sobre texto azul marino no se ve.
    $accentPdf = (!empty($brandColor) && strtolower($brandColor) !== '#0f172a')
        ? $brandColor
        : '#2563eb';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dictamen de Riesgo y Solvencia - <?= esc($companyNamePdf) ?></title>
    <style>
        @page {
            /* Margen inferior ampliado para dejar sitio a la banda fija de paginación:
               con el histórico registral dentro, este informe ya no cabe en una hoja. */
            margin: 28px 36px 50px 36px;
            size: A4 portrait;
        }

        /* Banda fija: se repite en TODAS las páginas. Lleva la empresa además del
           número de página porque una hoja suelta de un expediente tiene que poder
           identificarse sin la primera. Dompdf resuelve counter(page)/counter(pages). */
        .page-strip {
            position: fixed;
            bottom: -30px;
            left: 0;
            right: 0;
            font-size: 6.6pt;
            color: #94a3b8;
        }
        .page-strip-num:after {
            content: counter(page) " de " counter(pages);
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
            height: 3px;
            background-color: <?= esc($accentPdf) ?>;
            margin-bottom: 16px;
        }

        /* COMPANY IDENTITY CARD */
        /* Ficha de identidad.
           Antes: fondo azul plano, etiquetas grises EN NEGRITA del mismo tamaño que
           los valores, y cuatro columnas que dejaban el domicilio tan estrecho que
           siempre partía en dos líneas. Todo pesaba igual, así que no se leía nada
           primero. Ahora: nombre y estado arriba como identidad, un filete, y debajo
           los atributos con etiqueta pequeña encima del valor. */
        .company-card {
            width: 100%;
            background-color: #ffffff;
            border: 1px solid #dbe3ee;
            border-radius: 4px;
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
            letter-spacing: -0.2px;
        }

        /* ---------------------------------------------------------------
           TRATAMIENTO "INFORME INSTITUCIONAL"
           Tres decisiones, no retoques sueltos:
           a) Cada sección se abre con una BARRA OSCURA y su número. Un título
              en negro sobre panel claro se lee como una web; una banda con el
              texto en blanco se lee como un informe.
           b) Radios de 10-12px bajados a 4. Las esquinas muy redondeadas son
              lenguaje de aplicación, no de documento.
           c) El color de marca manda en la estructura y el semáforo se reserva
              para el dato. Antes el verde estaba en el fondo, el número, la
              pastilla, la barra y el tick a la vez, y no destacaba ninguno.
           --------------------------------------------------------------- */
        .sec-bar {
            width: 100%;
            border-collapse: collapse;
            background-color: #0b1c40;
            margin-bottom: 0;
        }
        .sec-bar td {
            padding: 5px 10px;
            vertical-align: middle;
        }
        .sec-num {
            font-size: 7pt;
            font-weight: bold;
            color: <?= esc($accentPdf) ?>;
            letter-spacing: 1px;
        }
        .sec-name {
            font-size: 8.4pt;
            font-weight: bold;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.7px;
        }
        .sec-body {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #dbe3ee;
            border-top: none;
            background-color: #ffffff;
        }
        .sec-body td { padding: 11px 12px; }

        .identity-rule {
            height: 1px;
            background-color: #eef2f7;
            margin: 10px 0 9px 0;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
        }
        .meta-table td {
            padding: 0 12px 0 0;
            vertical-align: top;
        }
        /* Etiqueta: pequeña, en versalitas y SIN negrita. La negrita la lleva el dato,
           que es lo que se busca al escanear el documento. */
        .meta-label {
            font-size: 6.4pt;
            color: #8a94a6;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding-bottom: 1px;
        }
        .meta-value {
            font-size: 8.2pt;
            font-weight: bold;
            color: #0f172a;
            line-height: 1.25;
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
            border: 1px solid #dbe3ee;
            border-radius: 4px;
            background-color: #ffffff;
            padding: 16px 14px;
        }

        /* SCORE HERO BADGE (Diseño moderno tipo Dashboard) */
        /* El número en BLANCO sobre el color del riesgo, no de color sobre un fondo
           pálido. Es el dato central del informe y tiene que poder leerse desde el
           otro lado de una mesa; además así el semáforo se ve aunque se imprima el
           documento en una fotocopiadora mala. */
        .score-hero-card {
            background-color: <?= $color ?>;
            border-radius: 4px;
            padding: 14px 12px 12px 12px;
            margin: 10px auto 12px auto;
            text-align: center;
        }
        .score-big-number {
            font-size: 42pt;
            font-weight: bold;
            color: #ffffff;
            line-height: 1;
            margin: 0;
            letter-spacing: -1.5px;
        }
        .score-scale-denom {
            font-size: 7.4pt;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 3px;
            margin-bottom: 9px;
        }
        .score-pill-tag {
            display: inline-block;
            background-color: #ffffff;
            color: <?= $color ?>;
            font-size: 8.5pt;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 3px 14px;
            border-radius: 3px;
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

        /* El círculo es una TABLA de una celda, no un div con line-height: es la única
           forma de centrar de verdad en Dompdf cuando el glifo cambia de fuente. */
        .factor-icon-badge {
            width: 24px;
            height: 24px;
            border-radius: 12px;
            background-color: #fee2e2;
            color: #dc2626;
            font-weight: bold;
            font-size: 10pt;
            border-collapse: collapse;
            margin: 0 auto;
        }
        .factor-icon-badge td {
            width: 24px;
            height: 24px;
            text-align: center;
            vertical-align: middle;
            padding: 0;
        }
        .factor-icon-badge-caution {
            background-color: #fef3c7;
            color: #d97706;
        }
        /* El "✓" (U+2713) NO existe en Helvetica: es una fuente core del PDF, solo
           Latin-1, y Dompdf lo sustituye por "?". Resultado: el factor favorable salía
           con un interrogante en verde, que se lee como "no lo sabemos" justo donde
           queremos decir "esto está bien".
           DejaVu Sans viene con Dompdf y sí lo trae. Va con !important porque la regla
           `*` de arriba también lo lleva y si no, gana ella. */
        /* Círculo VERDE SÓLIDO con el tick en blanco. En #dcfce7 sobre una tarjeta
           #f0fdf4 el círculo era invisible —dos verdes casi idénticos— y quedaba un
           tick suelto flotando, mucho más flojo que el "!" de las alertas. El tick de
           DejaVu además dibuja más pequeño que la "!" de Helvetica al mismo tamaño,
           así que sube un punto para igualar el peso óptico. */
        .factor-icon-badge-ok {
            background-color: #16a34a;
            color: #ffffff;
        }
        /* Solo el tamaño: la fuente se declara en línea en el <td> del badge, porque
           desde aquí Dompdf la pierde contra el `!important` del selector `*`. */
        .factor-icon-badge-ok td {
            font-size: 11pt;
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

        /* LEVE. Los factores de esta lista son incidencias ENCONTRADAS, así que la
           de gravedad baja no puede ir en verde con un tick y la palabra POSITIVO:
           eso convierte un hecho en contra en un punto a favor. Neutro: es un dato
           menor, ni alarma ni mérito. El verde queda para la tarjeta de "sin
           incidencias", que es el único caso en que de verdad no hay nada. */
        .factor-card-leve {
            background-color: #f8fafc;
            border-color: #e2e8f0;
            border-left-color: #94a3b8;
        }
        .factor-icon-badge-leve {
            background-color: #e2e8f0;
            color: #475569;
        }
        .factor-pill-badge-leve {
            background-color: #f1f5f9;
            color: #475569;
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

<?php $pdfRef = strtoupper(substr(md5(($company['id'] ?? '1') . '-' . date('Ymd')), 0, 10)); ?>
<table class="page-strip" cellpadding="0" cellspacing="0" style="width: 100%;">
    <tr>
        <td style="text-align: left;">
            <?= esc($companyNamePdf) ?> &nbsp;&bull;&nbsp; <?= esc($company['cif'] ?? $company['nif'] ?? '') ?>
            &nbsp;&bull;&nbsp; Ref: <?= esc($pdfRef) ?>
        </td>
        <td style="text-align: right;">Página <span class="page-strip-num"></span></td>
    </tr>
</table>


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
                <?php
                /*
                 * Fecha de emisión ≠ fecha de los datos. Un informe emitido hoy con datos
                 * de hace ocho meses vale lo que vale, y es lo primero que mira quien lo
                 * recibe. Se toma del último asiento publicado de esta empresa: es el
                 * corte real de lo que el dictamen ha podido ver.
                 */
                $pdfPosts = $bormePosts ?? [];
                $pdfFechas = array_filter(array_map(
                    static fn ($p) => trim((string) ($p['borme_date'] ?? '')),
                    $pdfPosts
                ));
                $pdfCorte = $pdfFechas ? date('d/m/Y', strtotime(max($pdfFechas))) : null;
                ?>
                <div class="report-date">
                    Fecha de emisión: <?= date('d/m/Y H:i') ?> &nbsp;|&nbsp; Ref: <?= esc($pdfRef) ?>
                </div>
                <div class="report-date">
                    <?php if ($pdfCorte !== null): ?>
                        Datos del BORME hasta <?= esc($pdfCorte) ?>
                    <?php else: ?>
                        Sin asientos del BORME publicados para esta sociedad
                    <?php endif; ?>
                </div>
            </td>
        </tr>
    </table>

    <div class="header-rule"></div>

    <!-- COMPANY IDENTITY CARD -->
    <?php
    // Datos de la ficha, resueltos antes del marcado para que la tabla quede limpia.
    $statusStr = strtoupper($company['estado'] ?? $company['status'] ?? 'ACTIVA');
    $isDefunct = (strpos($statusStr, 'EXTIN') !== false || strpos($statusStr, 'DISUEL') !== false
               || strpos($statusStr, 'BAJA') !== false || strpos($statusStr, 'CIERRE') !== false);

    $addr = $company['address'] ?? '';
    if (empty($addr) && (!empty($company['municipality']) || !empty($company['province']))) {
        $addr = trim(($company['municipality'] ?? '') . ' (' . ($company['province'] ?? '') . ')', ' ()');
    }

    $cnaeCode = $company['cnae_code'] ?? $company['cnae'] ?? '';
    $cnaeDesc = $company['cnae_name'] ?? $company['cnae_label'] ?? 'No especificado';
    $cnaeTxt  = $cnaeCode ? "{$cnaeCode} - {$cnaeDesc}" : $cnaeDesc;

    /*
     * Constitución y antigüedad. El desglose afirma "antigüedad de diez años o más"
     * entre los factores que restan riesgo, y el documento no decía en ningún sitio
     * de cuándo es la empresa: una afirmación del informe que no se podía comprobar
     * dentro del propio informe.
     */
    $pdfConst = trim((string) ($company['fecha_constitucion'] ?? ''));
    $pdfTs    = ($pdfConst !== '' && strpos($pdfConst, '0000') !== 0) ? strtotime($pdfConst) : false;
    $constTxt = 'No consta';
    if ($pdfTs) {
        $pdfAnios = (int) floor((time() - $pdfTs) / 31557600);
        $constTxt = date('d/m/Y', $pdfTs) . ' · ' . $pdfAnios . ' ' . ($pdfAnios === 1 ? 'año' : 'años');
    }
    ?>
    <table class="company-card">
        <tr>
            <td>
                <!-- Identidad: el nombre y el estado se leen juntos, así que van en la
                     misma línea. El estado a la derecha, donde el ojo cae al terminar
                     de leer el nombre. -->
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="vertical-align: middle;">
                            <span class="company-name-title"><?= esc($companyNamePdf) ?></span>
                        </td>
                        <td style="text-align: right; vertical-align: middle; width: 120px;">
                            <span class="status-pill <?= $isDefunct ? 'status-pill-inactive' : 'status-pill-active' ?>">
                                &bull; <?= esc($statusStr) ?>
                            </span>
                        </td>
                    </tr>
                </table>

                <div class="identity-rule"></div>

                <!-- Atributos: etiqueta encima del valor. En cuatro columnas enfrentadas
                     el domicilio no cabía y partía siempre; aquí ocupa dos. -->
                <table class="meta-table">
                    <tr>
                        <td style="width: 20%;" class="meta-label">CIF / NIF</td>
                        <td style="width: 26%;" class="meta-label">Constitución</td>
                        <td style="width: 18%;" class="meta-label">Asientos BORME</td>
                        <td style="width: 36%;" class="meta-label">Actividad (CNAE)</td>
                    </tr>
                    <tr>
                        <td class="meta-value" style="padding-bottom: 8px;"><?= esc($company['cif'] ?? $company['nif'] ?? 'No disponible') ?></td>
                        <td class="meta-value" style="padding-bottom: 8px;"><?= esc($constTxt) ?></td>
                        <td class="meta-value" style="padding-bottom: 8px;"><?= count($pdfPosts) ?></td>
                        <td class="meta-value" style="padding-bottom: 8px;"><?= esc($cnaeTxt) ?></td>
                    </tr>
                    <tr>
                        <td class="meta-label" colspan="4">Domicilio social</td>
                    </tr>
                    <tr>
                        <td class="meta-value" colspan="4"><?= esc($addr ?: 'No disponible') ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table class="sec-bar" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 26px;"><span class="sec-num">01</span></td>
            <td><span class="sec-name">Valoración del riesgo</span></td>
        </tr>
    </table>
    <table class="columns-table" style="border: 1px solid #dbe3ee; border-top: none; background: #ffffff;">
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
                            // Mismo fallo que arrastraban las vistas: `critical` no entraba
                            // en ninguna rama y caía al bloque verde de "sin problema". En un
                            // PDF que el cliente adjunta a un expediente, una sociedad
                            // extinguida pintada en verde no es un detalle estético.
                            $sevN = risk_event_severidad($flag);
                            $sev  = $sevN >= 3 ? 'high' : ($sevN === 2 ? 'medium' : 'low');
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
                                // Ver la nota de .factor-card-leve en el CSS: una
                                // incidencia leve sigue siendo una incidencia.
                                $cClass = 'factor-card-leve';
                                $iClass = 'factor-icon-badge-leve';
                                $pClass = 'factor-pill-badge-leve';
                                $sevLabel = 'LEVE';
                                // "i" existe en Helvetica; el tick era el problema.
                                $symbol = 'i';
                            }

                            // Antes: ucwords(strtolower(str_replace('_', ' ', $code))), que
                            // imprimía "Legal State Registry Closure Tax Index Provisional"
                            // dentro de un "dictamen oficial" en español.
                            $flagTitle = risk_event_label($flag);
                            ?>
                            <table class="factor-card <?= $cClass ?>">
                                <tr>
                                    <td style="width: 30px; text-align: center;">
                                        <?php /* Tabla y no div: en Dompdf el `line-height` no
                                                 centra de verdad, y con DejaVu —métricas
                                                 distintas a las de Helvetica— el tick se iba
                                                 abajo a la izquierda. `vertical-align: middle`
                                                 de una celda sí lo centra, y da igual la
                                                 fuente y el glifo que lleve dentro. */ ?>
                                        <?php /* El tick va como IMAGEN, no como carácter.
                                                 Helvetica —fuente core del PDF, solo Latin-1—
                                                 no tiene el glifo y lo pinta como "?", y en
                                                 este montaje Dompdf tampoco resuelve DejaVu
                                                 (por eso en Company.php ya se quitaban los
                                                 emojis a mano antes de generar). Un PNG
                                                 embebido no depende de fuentes, ni de la
                                                 cascada, ni de la versión de Dompdf.
                                                 La "!" de alerta y atención sí existe en
                                                 Helvetica, así que esas siguen como texto. */ ?>
                                        <table class="factor-icon-badge <?= $iClass ?>" cellpadding="0" cellspacing="0">
                                            <tr><td>
                                                <?= $symbol ?>
                                            </td></tr>
                                        </table>
                                    </td>
                                    <td>
                                        <div style="font-size: 8.2pt; font-weight: bold; color: #0b1c40; margin-bottom: 1px;">
                                            <?= esc($flagTitle) ?>
                                        </div>
                                        <div style="font-size: 7.4pt; color: #475569; line-height: 1.3;">
                                            <?= esc(company_sentence_case($flag['description'] ?? 'Registro mercantil verificado.')) ?>
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
                                    <?php /* Este bloque es el caso "sin eventos" y llevaba su
                                             PROPIO badge escrito a mano, fuera del bucle de
                                             arriba: por eso seguía saliendo "?" cuando el del
                                             bucle ya estaba corregido. Mismo tick en imagen y
                                             misma estructura de tabla que allí. */ ?>
                                    <table class="factor-icon-badge factor-icon-badge-ok" cellpadding="0" cellspacing="0">
                                        <tr><td>
                                            <img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEgAAABICAYAAABV7bNHAAAE9klEQVR42u2bTYgcVRDH/693VtGYoMZAQozJISriQQkYggZPInpaxEWCoIJCQIyaSw5BiKKXHPRgQDwEJAdBQlBkEEEvQVQW9RC/UBc1QaIbBQ0i7qpxpn9e6mGlmdmdmX6TnZ7tgmZmmf5479fV9a+q1yvVVltttdVWW21jamHcJwgEm2ecK5IIIbCi7zwQgMYivzcM3srzICALIeTu7+slXS0pkzQnaTaE0Oq070rwnMw+J4EngE+BFv9bDnwDPAWstn0nVhqczcAM51vbNm9fATf7Y8cajsWddcB3BuCcQck7wDpn338FrhtrSAZmwgLvcQdnKYv7fGzHji2ghn0esQn/S+8W95325xonOJP2eaAPzykCyoHm2AVs5zkPFCbbj8XAfcrBDuME53bzmtYAcHDH/AZcUQSUVRTORAihBWyV9IakyUI5MYj9Y9t5llUQTiYpBy6X1JS0VlK7xFxyq89OhhAWLLOmkoAKhecxSTdIakkqE1ix8zWr/FQV487hAeS8W4DOLf5cZflUqCqcqDD7B5TzTsE5Ar6v0hLvPGdXCTnvlkUfqHSCGO8qcCvwd5faigGSQ4AjVYcTq/MtwC+FxK4snONWf00kiTtWLTcKWzZMOBY01wCf26RaJeHE42eBK+38WZKBLvF7lhhOrM4D8HZixTprHcaegnKjh4y1bd9vknSbpC0xsZL0YQjhy+K+CSxmyi9LuttynTJxArdNhxBmgUZsu5YNjrcA73QJjG3gLdeVayTwnijn+xLJufe+h5OM08F5yA0w5g1+i7bgconJBHI+PQQ5f67s+Ipw7uqQUC12dwDuH3QQ7rrbgb8Sy/mr8QaUUqyYagOrgR/7UA7fEH+kXzd2cr4JmEsk53Hc79sKR3k5dy7+6ADKkbtJ7ekVUpRaYBVwIrGcfw+sS9aQd3fyXZtwa4D6Jh6zbym39qufQDOxnP8O3JisxoqTMHc8VcLNPaSnF4Pk4BxKBCd3ncU7k5YRDtAq4EyhHVmmUj4Y76KH5ODsTQTHn2N38hrLAZqwVDxl3fNioXyIcKYSynnxhkwqtTmpPdaDvPc78MMFz9kGzCeW86NJ5LwHQFMJ3d4nazEfWQ+cTiznM8DFS9WO/VroomRB0geSdiSog6LF8xyVtFHSTmu2l1GY3HrIP0jaEUL4OfXrLN0AIWmrpBm3apCiHZm7pjgll2kihAVJO0MInyUumDt38I1+CCF8K+kOST8ZnHai67VtcmXgxMo8k7TL4DRSw+k1Hl0LnEwck1IF/seWtWXqFGcz8PWIQIoB/4WR6Cc7T9pgr7MtJ6R43deHKuclIK0FPlomSFHOPwEuSS3nKSGtAd67wJBirnQa2JisOh/iMsyl1oZN1RLtpY0yD2wb+RVQB+ki4M0he5Ivd6Yqs8jnis4MeG2IkOI59w6tAB0ypOhNrwzhcYtwDlV2edivTAIvDaGv0xwpOS+zCmrfn0/Q34lyfsKadxlVf3+5AOnZBG+czgGbRlbOS0CKpcl+5w15H3Datia2feTlPAGkJx2kdg9w4qN1b2WD8gBF7u5C4I3t1dwlgD6oPz72cDpAusetknSzs8CDKwZOh/ptPfCMKdMf9jj9CXwBHASuGdWYEy4EJN/pAzZIukzSvKQz8aXtYbRLKxm8Oz2KoyzlYTlguevW/55dW2211VZbbbXVVls17T8oyMSE0tREOwAAAABJRU5ErkJggg==" width="18" height="18" alt="Favorable">
                                        </td></tr>
                                    </table>
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

    <!-- DESGLOSE DEL SCORING
         El texto de metodología prometía seis dimensiones sin enseñar ni una.
         En un informe que se cobra, la cifra tiene que poder auditarse. -->
    <?php
    $dimsPdf = risk_dimensions($riskProfile['data'] ?? []);
    $dominantePdf = null;
    $maxPdf = 0.0;
    foreach ($dimsPdf as $dPdf) {
        if ($dPdf['suma'] && $dPdf['valor'] > $maxPdf) {
            $maxPdf = $dPdf['valor'];
            $dominantePdf = $dPdf['clave'];
        }
    }
    ?>
    <?php if (!empty($dimsPdf)): ?>
    <table class="sec-bar" cellpadding="0" cellspacing="0">
        <tr>
            <td style="width: 26px;"><span class="sec-num">02</span></td>
            <td><span class="sec-name">Desglose del Índice de Estabilidad Societaria</span></td>
        </tr>
    </table>
    <table class="sec-body" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div style="font-size: 8pt; font-weight: bold; color: #0b1c40; margin-bottom: 4px;">
                    De dónde sale el <?= $score ?>
                </div>
                <div style="font-size: 7.1pt; color: #64748b; line-height: 1.35; margin-bottom: 6px;">
                    La puntuación no es la suma de las dimensiones: manda la más grave y las demás
                    aportan una parte del margen restante. Escala de 0 (máxima estabilidad) a 100
                    (máximo riesgo operativo).
                </div>

                <table style="width: 100%; border-collapse: collapse;">
                    <?php foreach ($dimsPdf as $dPdf): ?>
                        <?php
                        $activaPdf = abs($dPdf['valor']) > 0.01;
                        $colorPdf  = !$dPdf['suma'] ? '#16a34a' : (($dPdf['clave'] === $dominantePdf) ? '#b91c1c' : ($activaPdf ? '#b45309' : '#94a3b8'));
                        ?>
                        <tr>
                            <td style="padding: 2px 0; font-size: 7.4pt; color: #0b1c40; width: 44%;">
                                <?= esc($dPdf['titulo']) ?><?php if ($dPdf['clave'] === $dominantePdf): ?> <span style="color: #b91c1c; font-weight: bold;">(factor dominante)</span><?php endif; ?>
                            </td>
                            <td style="padding: 2px 0; width: 40%;">
                                <table style="width: 100%; border-collapse: collapse; background: #eef2f7;">
                                    <tr>
                                        <td style="height: 5px; width: <?= $activaPdf ? max(2, (int) $dPdf['pct']) : 0 ?>%; background: <?= $colorPdf ?>; font-size: 0;">&nbsp;</td>
                                        <td style="font-size: 0;">&nbsp;</td>
                                    </tr>
                                </table>
                            </td>
                            <td style="padding: 2px 0; font-size: 7.4pt; font-weight: bold; color: <?= $colorPdf ?>; text-align: right; width: 16%;">
                                <?= number_format($dPdf['valor'], 0, ',', '.') ?><?php if ($dPdf['suma']): ?><span style="color: #94a3b8; font-weight: normal;"> / <?= (int) $dPdf['tope'] ?></span><?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <?php
                /*
                 * Qué factores forman el crédito estabilizador.
                 *
                 * El PDF enseñaba el "-15" a secas mientras la ficha web —gratuita—
                 * sí explicaba de dónde salía. El documento por el que se paga no
                 * puede explicar MENOS que la página que se ve sin pagar.
                 *
                 * Misma función que la web, así que las dos dicen lo mismo y con la
                 * misma prudencia cuando el número admite más de una lectura.
                 */
                $credPdf = (float) ($riskProfile['data']['dimensions']['stabilizing_credit'] ?? 0);
                if (abs($credPdf) > 0.01):
                ?>
                    <div style="margin-top: 5px; font-size: 7.1pt; color: #15803d; line-height: 1.35;">
                        <?= esc(risk_stabilizers_text($credPdf, (array) ($riskProfile['data']['dimensions'] ?? []))) ?>
                    </div>
                <?php endif; ?>

                <?php
                /*
                 * Evolución. La ficha web ya la enseña y el documento por el que se paga
                 * no la llevaba. Una foto la da cualquier informe tradicional; lo que
                 * distingue a este producto es poder decir de dónde viene el número.
                 */
                $pdfTrend = $riskTrend ?? null;
                if (!empty($pdfTrend) && isset($pdfTrend['antes'], $pdfTrend['delta'])):
                    $pdfDelta = (int) $pdfTrend['delta'];
                    $pdfColorT = $pdfDelta > 0 ? '#b91c1c' : ($pdfDelta < 0 ? '#15803d' : '#64748b');
                    // En esta escala SUBIR es empeorar. Se dice con palabras y no con una
                    // flecha, que es justo lo que se leía al revés en la ficha.
                    $pdfFraseT = $pdfDelta > 0
                        ? 'ha empeorado ' . $pdfDelta . ' ' . (abs($pdfDelta) === 1 ? 'punto' : 'puntos')
                        : ($pdfDelta < 0
                            ? 'ha mejorado ' . abs($pdfDelta) . ' ' . (abs($pdfDelta) === 1 ? 'punto' : 'puntos')
                            : 'no ha variado');
                ?>
                    <div style="margin-top: 5px; font-size: 7.1pt; color: #475569; line-height: 1.35;">
                        <strong>Evolución:</strong> hace <?= esc((string) $pdfTrend['periodo']) ?> la puntuación era
                        <strong><?= (int) $pdfTrend['antes'] ?></strong>;
                        <span style="color: <?= $pdfColorT ?>; font-weight: bold;"><?= esc($pdfFraseT) ?></span>.
                    </div>
                <?php endif; ?>

                <?php if (!empty($riskProfile['data']['legal_evidence_conflict'])): ?>
                    <div style="margin-top: 5px; font-size: 7.1pt; color: #92400e; line-height: 1.35;">
                        <strong>Evidencia contradictoria:</strong> el estado oficial y los actos del BORME no
                        concuerdan. La puntuación se ha calculado con el criterio más prudente.
                    </div>
                <?php endif; ?>

                <?php if (isset($riskProfile['data']['confidence_score'])): ?>
                    <div style="margin-top: 4px; font-size: 7.1pt; color: #64748b;">
                        Cobertura del dato: <?= (int) ($riskProfile['data']['data_quality_score'] ?? 0) ?>% &nbsp;|&nbsp;
                        Confianza: <?= (int) $riskProfile['data']['confidence_score'] ?>%
                        <?php if (!empty($riskProfile['data']['model_version'])): ?>
                            &nbsp;|&nbsp; Modelo <?= esc($riskProfile['data']['model_version']) ?>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <?php else: ?>
    <table class="bottom-card">
        <tr>
            <td>
                <div style="font-size: 8pt; font-weight: bold; color: #0b1c40; margin-bottom: 2px;">
                    Metodología del Índice de Estabilidad Societaria (IES)
                </div>
                <div style="font-size: 7.3pt; color: #64748b; line-height: 1.35;">
                    El algoritmo evalúa 6 dimensiones objetivas: estado legal y mercantil, depósito de
                    cuentas anuales, estabilidad del órgano de administración, capital social,
                    volatilidad estructural y factores estabilizadores. La escala asigna de 0 (máxima
                    estabilidad) a 100 (máximo riesgo operativo).
                </div>
            </td>
        </tr>
    </table>
    <?php endif; ?>

    <?php
    /*
     * HISTÓRICO REGISTRAL.
     *
     * El texto de venta promete "el histórico registral de X con su puntuación" y el
     * informe entregaba solo los factores analizados, que son CONCLUSIONES del motor,
     * no los asientos. Quien lo compra para adjuntarlo a un expediente necesita la
     * relación de actos con sus fechas: es la parte verificable contra el BORME, y la
     * que convierte el documento en prueba en vez de en opinión.
     *
     * Más recientes primero, que es como se lee un histórico registral, y con tope:
     * una sociedad con cuarenta años de boletín haría un PDF inmanejable.
     */
    $pdfHist = $pdfPosts;
    usort($pdfHist, static function ($a, $b) {
        return strcmp((string) ($b['borme_date'] ?? ''), (string) ($a['borme_date'] ?? ''));
    });
    $pdfMaxHist  = 25;
    $pdfRestante = max(0, count($pdfHist) - $pdfMaxHist);
    ?>
    <?php if (!empty($pdfHist)): ?>
    <?php /* El histórico abre PÁGINA NUEVA. Antes el título quedaba al final de la
             primera y la tabla arrancaba en la segunda, que es el peor de los casos:
             un encabezado huérfano. Al ser una relación larga y de consulta, tener su
             propia hoja además la hace utilizable por separado. */ ?>
    <table class="sec-bar" cellpadding="0" cellspacing="0" style="page-break-before: always;">
        <tr>
            <td style="width: 26px;"><span class="sec-num">03</span></td>
            <td><span class="sec-name">Histórico registral (BORME)</span></td>
        </tr>
    </table>
    <table class="sec-body" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div style="font-size: 7.1pt; color: #64748b; margin-bottom: 6px;">
                    Asientos publicados a nombre de esta sociedad, del más reciente al más antiguo.
                    <?= count($pdfHist) ?> en total<?= $pdfRestante > 0 ? ', se detallan los ' . $pdfMaxHist . ' últimos' : '' ?>.
                </div>

                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="width: 14%; font-size: 6.8pt; font-weight: bold; color: #64748b; border-bottom: 1px solid #e2e8f0; padding: 0 0 3px;">FECHA</td>
                        <td style="width: 30%; font-size: 6.8pt; font-weight: bold; color: #64748b; border-bottom: 1px solid #e2e8f0; padding: 0 0 3px;">TIPO DE ACTO</td>
                        <td style="font-size: 6.8pt; font-weight: bold; color: #64748b; border-bottom: 1px solid #e2e8f0; padding: 0 0 3px;">DETALLE</td>
                    </tr>
                    <?php foreach (array_slice($pdfHist, 0, $pdfMaxHist) as $pdfAct): ?>
                        <?php
                        $pdfFa = trim((string) ($pdfAct['borme_date'] ?? ''));
                        $pdfTipo = trim((string) ($pdfAct['act_types'] ?? ''));
                        $pdfDesc = trim((string) ($pdfAct['description'] ?? ''));
                        // Un acto grave se marca también aquí: en una lista larga, lo
                        // importante no puede tener el mismo aspecto que un cambio de
                        // domicilio solo porque comparten tabla.
                        $pdfGrave = \App\Services\EmailService::actoDestacado($pdfTipo . ' ' . $pdfDesc);
                        $pdfRojo  = $pdfGrave !== null && $pdfGrave['grave'];
                        ?>
                        <tr style="page-break-inside: avoid;">
                            <td style="font-size: 7.1pt; color: #475569; padding: 2px 0; vertical-align: top; border-bottom: 1px solid #f1f5f9;">
                                <?= $pdfFa !== '' ? esc(date('d/m/Y', strtotime($pdfFa))) : '—' ?>
                            </td>
                            <td style="font-size: 7.1pt; padding: 2px 6px 2px 0; vertical-align: top; border-bottom: 1px solid #f1f5f9; color: <?= $pdfRojo ? '#b91c1c' : '#0b1c40' ?>; <?= $pdfRojo ? 'font-weight: bold;' : '' ?>">
                                <?= esc(company_sentence_case(mb_substr($pdfTipo !== '' ? $pdfTipo : 'Acto registral', 0, 60))) ?>
                            </td>
                            <td style="font-size: 7.1pt; color: #475569; padding: 2px 0; vertical-align: top; border-bottom: 1px solid #f1f5f9;">
                                <?= esc(company_sentence_case(mb_substr($pdfDesc, 0, 150))) ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </table>

                <?php if ($pdfRestante > 0): ?>
                    <div style="margin-top: 5px; font-size: 7.1pt; color: #64748b;">
                        Y <?= $pdfRestante ?> asiento(s) anterior(es) no detallados en este informe.
                    </div>
                <?php endif; ?>
            </td>
        </tr>
    </table>
    <?php endif; ?>

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
