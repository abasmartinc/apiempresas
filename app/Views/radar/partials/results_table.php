<?php
$formatEsDate = function($dateStr, $format = 'd M Y') {
    if (empty($dateStr) || $dateStr === '0000-00-00') return 'Reciente';
    
    try {
        $date = new \DateTime($dateStr);
        $timestamp = $date->getTimestamp();
        $year = (int)$date->format('Y');
        
        // Validación de rango: mínimo 1900, máximo fecha de hoy (sin fechas futuras)
        if ($year < 1900 || $timestamp > time()) {
            return 'Reciente';
        }
    } catch (\Exception $e) {
        return 'Reciente';
    }
    
    if (!$timestamp) return 'Reciente';
    
    $mesesEn = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $mesesEs = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    
    return str_replace($mesesEn, $mesesEs, date($format, $timestamp));
};

$getPriorityBadge = function($level) {
    $map = [
        'muy_alta' => ['label' => 'Muy alta', 'icon' => '🔥', 'class' => 'muy-alta'],
        'alta' => ['label' => 'Alta', 'icon' => '⚡', 'class' => 'alta'],
        'media' => ['label' => 'Media', 'icon' => '🟡', 'class' => 'media'],
        'baja' => ['label' => 'Baja', 'icon' => '⚪', 'class' => 'baja'],
        'muy_baja' => ['label' => 'Muy baja', 'icon' => '⛔', 'class' => 'muy-baja'],
    ];
    return $map[$level] ?? ['label' => 'Sin clasificar', 'icon' => '', 'class' => 'none'];
};

$getScoreClass = function($score) {
    if (!$score) return 'none';
    if ($score >= 90) return 'premium';
    if ($score >= 75) return 'strong';
    if ($score >= 55) return 'medium';
    if ($score >= 35) return 'low';
    return 'minimal';
};

$formatCapital = function($val) {
    if (empty($val)) return null;
    $clean = preg_replace('/[^0-9]/', '', $val);
    if (!$clean) return 0;
    return (float)$clean;
};

$getOpportunityText = function($score) {
    if ($score >= 85) return ['label' => 'Lead prioritario', 'class' => 'hot'];
    if ($score >= 70) return ['label' => 'Oportunidad alta', 'class' => 'high'];
    if ($score >= 50) return ['label' => 'Oportunidad media', 'class' => 'now'];
    if ($score >= 30) return ['label' => 'Potencial bajo', 'class' => 'medium'];
    if ($score > 0) return ['label' => 'Baja prioridad', 'class' => 'low-priority'];
    return ['label' => 'No contactar', 'class' => 'no-contact'];
};


$getEstimatedTicket = function($capital) {
    if (!$capital || $capital <= 0) return '500€ - 1.500€';
    if ($capital > 100000) return '5.000€ - 12.000€';
    if ($capital > 50000) return '3.000€ - 7.000€';
    if ($capital > 10000) return '1.500€ - 4.000€';
    return '1.000€ - 2.500€';
};

$filters = $filters ?? [];
$allCompanies = $companies ?? [];
$limitFree = 3; // Limitado a 3 empresas visibles
$visibleCompanies = $isFree ? array_slice($allCompanies, 0, $limitFree) : $allCompanies;
$lockedCompanies = $isFree ? array_slice($allCompanies, $limitFree) : [];
?>

<!-- Dynamic Results Title (Sincronizado con AJAX) -->
<div class="ae-radar-page__results-info" style="margin-top: 16px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding: 0 20px 16px 20px;">
    <h3 style="font-size: 20px; font-weight: 800; color: #1e293b; margin: 0; font-family: 'Outfit', sans-serif;">
        <?php 
            $totalItems = $pagination['total'] ?? 0;
            $filterText = "posibles clientes detectados";
            if (isset($filters['priority_level']) && !empty($filters['priority_level'])) {
                $pMap = [
                    'muy_alta' => '<span style="color:#e11d48">🔥 Prioridad Muy Alta</span>', 
                    'alta' => '<span style="color:#ef4444">⚡ Prioridad Alta</span>', 
                    'media' => '<span style="color:#d97706">🟡 Prioridad Media</span>'
                ];
                $filterText = "posibles clientes con " . ($pMap[$filters['priority_level']] ?? "prioridad detectada");
            } elseif (isset($filters['main_act_type']) && !empty($filters['main_act_type'])) {
                $filterText = "posibles clientes en fase de <span style='color:#2563eb;'>" . esc($filters['main_act_type']) . "</span>";
            }
            echo "<strong>" . number_format($totalItems, 0, ',', '.') . "</strong> " . $filterText;
        ?>
    </h3>
    <div style="font-size: 13px; font-weight: 700; color: #64748b; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px;">
        <span style="font-size: 14px;">⚖️</span>
        Ordenado por: <span style="color: #2563eb;">Inteligencia Radar (Relevancia) ↓</span>
    </div>
</div>

<style>
    /* Estilos dinámicos del Radar B2B (Optimización UI v2.5) */
    .ae-radar-row {
        opacity: 1; /* Visibilidad base garantizada */
        transition: background-color 0.2s, box-shadow 0.2s, border-color 0.2s;
    }

    .ae-row-entrance {
        /* Usamos 'backwards' para que mantenga la opacidad 0 solo durante el delay */
        animation: ae-fade-in-up 0.5s cubic-bezier(0.16, 1, 0.3, 1) backwards;
    }

    @keyframes ae-fade-in-up {
        0% { opacity: 0; transform: translateY(10px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    .ae-radar-row:hover {
        background-color: rgba(248, 250, 252, 0.8) !important;
        box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05);
        z-index: 10;
        position: relative;
    }

    /* Flash de fondo sin tocar la opacidad */
    .ae-row-flash {
        animation: ae-row-flash 1s ease-out;
    }

    @keyframes ae-row-flash {
        0% { background-color: rgba(254, 240, 138, 0.4); }
        100% { background-color: transparent; }
    }

    /* Asegurar alineación de botones de acción */
    .ae-radar-page__company-actions > *,
    .ae-radar-page__company-actions div > * {
        margin: 0 !important;
        box-sizing: border-box !important;
        vertical-align: middle;
    }

    .ae-btn-hover:hover {
        filter: brightness(1.05);
        box-shadow: 0 6px 15px rgba(37, 99, 235, 0.3);
    }

    .ae-recommended-row:hover {
        border-left: 4px solid #059669 !important; /* Mismo grosor para evitar movimiento */
        box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.15);
    }

    .ae-score-badge {
        cursor: help;
        transition: opacity 0.2s;
    }

    .ae-score-badge:hover {
        opacity: 0.85;
    }

    /* Nuevos estilos Pro-Comercial */
    .ae-opp-badge {
        padding: 4px 10px;
        border-radius: 6px;
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .ae-opp-badge.hot { background: #fee2e2; color: #ef4444; border: 1px solid #fecaca; }
    .ae-opp-badge.high { background: #fef3c7; color: #d97706; border: 1px solid #fde68a; }
    .ae-opp-badge.now { background: #dcfce7; color: #10b981; border: 1px solid #bbf7d0; }
    .ae-opp-badge.medium { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }
    .ae-opp-badge.low-priority { background: #fffbeb; color: #d97706; border: 1px solid #fef3c7; }
    .ae-opp-badge.no-contact { background: #f8fafc; color: #94a3b8; border: 1px solid #e2e8f0; }


    .ae-ticket-val {
        font-size: 15px;
        font-weight: 800;
        color: #1e293b;
        letter-spacing: -0.01em;
    }

    .ae-reason-text {
        font-size: 12px;
        line-height: 1.4;
        color: #64748b;
        font-weight: 500;
    }

    /* Blur para leads bloqueados */
    .ae-radar-row-blurred {
        filter: blur(5px);
        pointer-events: none;
        user-select: none;
        opacity: 0.6;
    }

    .ae-locked-overlay {
        position: relative;
    }

    .ae-locked-badge {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background: #0f172a;
        color: white;
        padding: 8px 16px;
        border-radius: 999px;
        font-size: 11px;
        font-weight: 800;
        z-index: 20;
        white-space: nowrap;
        pointer-events: auto;
        cursor: pointer;
    }

    .ae-radar-page__table thead th {
        text-transform: uppercase;
        font-size: 11px;
        letter-spacing: 0.05em;
        color: #94a3b8;
        padding-bottom: 16px;
    }

    .ae-radar-page__td-identity { width: 42%; }
    .ae-radar-page__td-opportunity { width: 38%; }
    .ae-radar-page__td-actions { width: 20%; }

    .ae-btn-strategy {
        background: #fff;
        color: #475569;
        border: 1px solid #e2e8f0;
        transition: all 0.2s;
    }
    .ae-btn-strategy:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #1e293b;
    }

    .ae-btn-fav-v3 {
        background: #fff;
        border: 1px solid #f1f5f9;
        color: #94a3b8;
        transition: all 0.2s;
    }
    .ae-btn-fav-v3:hover {
        border-color: #e2e8f0;
        background: #f8fafc;
    }
    .ae-btn-fav-v3.is-active {
        color: #ffb800;
        background: #fffbeb;
        border-color: #fef3c7;
    }

    .ae-status-pill-v3 {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 6px 10px;
        border-radius: 8px;
        background: #fff;
        border: 1px solid #e2e8f0;
        cursor: pointer;
        transition: all 0.2s;
        width: 100%;
        justify-content: space-between;
        position: relative;
    }
    .ae-status-pill-v3:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }
    .ae-status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        display: inline-block;
    }
    .ae-status-text {
        font-size: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }

    .ae-meta-sub {
        display: flex;
        align-items: center;
        gap: 12px;
        color: #64748b;
        font-size: 11px;
        font-weight: 500;
        margin-top: 6px;
    }
    .ae-meta-item {
        display: flex;
        align-items: center;
        gap: 4px;
    }
    .ae-meta-icon {
        opacity: 0.7;
        font-size: 12px;
    }

    .ae-value-box {
        background: rgba(248, 250, 252, 0.5);
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 12px 16px;
        transition: all 0.2s;
    }
    .ae-radar-row:hover .ae-value-box {
        border-color: #cbd5e1;
        background: #fff;
        box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    }
</style>

<div class="ae-radar-page__lead-top" style="display:flex; justify-content:space-between; align-items:flex-end; padding:24px 26px;">
        <!-- Left Side: Title & Info -->
        <div class="ae-radar-page__lead-headings">
            <h2 class="ae-radar-page__lead-title" style="margin-bottom:4px;">Oportunidades con potencial de compra</h2>
            <div class="ae-radar-page__lead-desc">
                <?php if ($isFree) { ?>
                    Muestra limitada de radar. Desbloquea PRO para ver todas las empresas.
                <?php } else { ?>
                    Mostrando del <strong><?= $pagination['start'] ?> al <?= $pagination['end'] ?></strong> de <strong><?= number_format($pagination['total']) ?></strong> oportunidades con potencial de compra.
                <?php } ?>
            </div>
        </div>

        <!-- Right Side: Grouped Controls -->
        <div class="ae-radar-page__lead-controls" style="display:flex; align-items:center; gap:16px;">
            <!-- View Toggle -->
            <div class="ae-radar-page__view-toggle" style="display:flex; gap:4px; background:#f1f5f9; padding:4px; border-radius:12px;">
                <button type="button" class="ae-view-btn is-active" data-view="list" onclick="switchView('list')" style="display:flex; align-items:center; gap:6px; border:none; background:white; padding:6px 14px; border-radius:10px; font-weight:700; color:#2563eb; cursor:pointer; box-shadow:0 2px 4px rgba(0,0,0,0.05); font-size:12px;">
                    Listado
                </button>
                <button type="button" class="ae-view-btn" data-view="map" onclick="switchView('map')" style="display:flex; align-items:center; gap:6px; border:none; background:transparent; padding:6px 14px; border-radius:10px; font-weight:700; color:#64748b; cursor:pointer; font-size:12px;">
                    Mapa
                </button>
            </div>

            <?php if (!$isFree) { ?>
                <!-- Vertical Separator -->
                <div style="width:1px; height:24px; background:#e2e8f0;"></div>

                <!-- Controls Group -->
                <div style="display:flex; align-items:center; gap:10px;">
                    <!-- Per Page -->
                    <div class="ae-radar-page__per-page-wrap" style="display:flex; align-items:center; gap:8px; font-size:12px; font-weight:700; color:#64748b; background:#f8fafc; padding:4px 12px; border-radius:10px; border:1px solid #e2e8f0;">
                        <span>Ver:</span>
                        <select onchange="updateResultsWithPerPage(this.value)" style="border:none; background:transparent; font-weight:800; color:#1e293b; cursor:pointer; outline:none;">
                            <option value="20" <?= (($filters['per_page'] ?? 20) == 20) ? 'selected' : '' ?>>20</option>
                            <option value="50" <?= (($filters['per_page'] ?? 20) == 50) ? 'selected' : '' ?>>50</option>
                            <option value="100" <?= (($filters['per_page'] ?? 20) == 100) ? 'selected' : '' ?>>100</option>
                        </select>
                    </div>

                    <!-- Export Options -->
                    <div style="display:flex; gap:6px;">
                        <a href="<?= site_url('radar/exportar?' . http_build_query(array_merge($filters, ['format' => 'excel']))) ?>" class="ae-radar-page__export-btn" style="background:#2563eb; padding: 10px 14px; border-radius:10px; color:#fff; text-decoration:none; font-size:12px; font-weight:800; display:flex; align-items:center; gap:6px;">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v4a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                            Desbloquear oportunidades
                        </a>
                        <a href="<?= site_url('radar/exportar?' . http_build_query(array_merge($filters, ['format' => 'csv']))) ?>" class="ae-radar-page__export-btn" style="background:#475569; padding: 10px 14px; border-radius:10px; color:#fff; text-decoration:none; font-size:12px; font-weight:800; display:flex; align-items:center;" title="Exportar CSV (Datos brutos)">
                            .CSV
                        </a>
                    </div>
                </div>
            <?php } else { ?>
                <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" class="ae-radar-page__export-btn" style="background:#0f172a; padding: 11px 18px; border-radius:12px; color:#fff; text-decoration:none; font-size:13px; font-weight:800;">Desbloquear todas las oportunidades ahora</a>
            <?php } ?>
        </div>
    </div>

<div id="radar-list-view">
    <div class="ae-radar-page__table-scroll">
        <table class="ae-radar-page__table">
            <thead>
                <tr>
                    <th class="ae-radar-page__td-identity">Empresa y Contexto</th>
                    <th class="ae-radar-page__td-opportunity">Potencial Comercial</th>
                    <th class="ae-radar-page__td-actions" style="text-align:right;">Gestión y Acción</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($visibleCompanies)) { ?>
                    <tr>
                        <td colspan="3" style="text-align:center; padding: 40px; color: #6b7280;">
                            No se han encontrado empresas con los filtros seleccionados.
                        </td>
                    </tr>
                <?php } else { ?>
                    <?php 
                        $statusStyleMap = [
                            'nuevo' => ['bg' => '#f3f4f6', 'color' => '#4b5563', 'border' => '#e5e7eb'],
                            'contactado' => ['bg' => '#eff6ff', 'color' => '#2563eb', 'border' => '#dbeafe'],
                            'seguimiento' => ['bg' => '#fff7ed', 'color' => '#ea580c', 'border' => '#ffedd5'],
                            'negociacion' => ['bg' => '#f5f3ff', 'color' => '#7c3aed', 'border' => '#ddd6fe'],
                            'ganado' => ['bg' => '#f0fdf4', 'color' => '#16a34a', 'border' => '#dcfce7']
                        ];
                    ?>
                    <?php foreach ($visibleCompanies as $index => $co): ?>
                        <?php 
                            $isFirst = ($index === 0 && ($pagination['start'] ?? 1) == 1);
                            $isNew = ($co['status'] ?? 'nuevo') === 'nuevo';
                            
                            // 1. Score y Colores (Heurística unificada)
                            $scoreData = $co['lead_score_data'] ?? ['numeric' => (int)($co['score_total'] ?? 0), 'base' => (int)($co['score_total'] ?? 0)];
                            $scoreTotal = (int)round($scoreData['numeric']);
                            $scoreColor = ($scoreTotal >= 70) ? '#059669' : ($scoreTotal >= 40 ? '#d97706' : '#64748b');
                            $scoreIcon = ($scoreTotal >= 70) ? '🟢' : ($scoreTotal >= 40 ? '🟡' : '⚪');

                            // 2. Datos Comerciales
                            $opp = $getOpportunityText($scoreTotal);
                            $capitalNum = $formatCapital($co['capital_social_raw'] ?? '');
                            $ticket = $getEstimatedTicket($capitalNum);
                            
                            // 3. Timing y Urgencia (Temporal FOMO)
                            $fechaConst = $co['fecha_constitucion'] ?? 'today';
                            $daysSince = floor((time() - strtotime($fechaConst)) / 86400);
                            $urgencyClass = ($daysSince <= 3) ? 'color: #e11d48; background: #fff1f2;' : 'color: #2563eb; background: #eff6ff;';
                            
                            if ($daysSince <= 0) $timingLabel = 'Reciente (Hoy)';
                            elseif ($daysSince == 1) $timingLabel = 'Detectada ayer';
                            elseif ($daysSince <= 7) $timingLabel = "Detectada hace $daysSince días";
                            else $timingLabel = 'Oportunidad activa';

                            // 4. Motivo Inteligente (Fallback mejorado)
                            $sectorSimple = esc(mb_strimwidth($co['cnae_label'] ?? 'su sector', 0, 30, '...'));
                            if (empty($needText) || $needText == 'Necesidad detectada por Radar') {
                                $reason = $scoreData['details']['explanation'] ?? "Empresa de $sectorSimple con señales de interés detectadas.";
                            } else {
                                $reason = $needText;
                            }


                            // 5. Estilo de Fila y Estado
                            $rowStyle = 'border-left: 5px solid transparent; transition: all 0.2s; vertical-align: middle;';
                            if ($isNew || $scoreTotal >= 70) {
                                $rowStyle = "border-left: 5px solid {$scoreColor}; background: linear-gradient(to right, #f8fbff, #ffffff);";
                            }

                            $curStatus = $co['status'] ?? 'nuevo';
                            $st = $statusStyleMap[$curStatus] ?? $statusStyleMap['nuevo'];
                        ?>
                        <tr class="ae-radar-row ae-row-entrance <?= $isFirst ? 'ae-recommended-row' : '' ?>" style="<?= $rowStyle ?> animation-delay: <?= $index * 0.03 ?>s;">
                            <!-- ZONA 1: IDENTIDAD Y CONTEXTO (42%) -->
                            <td class="ae-radar-page__td-identity" style="padding: 24px 20px;">
                                <div style="display: flex; flex-direction: column;">
                                    <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 6px;">
                                        <a href="javascript:void(0)" onclick="<?= $isFree ? "showConversionNudge('Oportunidad real bloqueada', 'Activa Radar PRO para ver los detalles de esta empresa y del resto de oportunidades detectadas hoy.', {id: '".$co['id']."', action: 'view'})" : "openQuickView('".$co['id']."')" ?>" style="text-decoration: none;">
                                            <span style="font-size: 17px; font-weight: 800; color: #0f172a; line-height: 1.2; letter-spacing: -0.01em;"><?= esc($co['company_name']) ?></span>
                                        </a>
                                        <div class="ae-score-badge" title="<?= esc($scoreData['details']['explanation'] ?? 'Puntuación inteligente de Radar') ?>" style="background: white; border: 1.5px solid <?= $co['lead_score_data']['color'] ?? $scoreColor ?>; padding: 2px 8px; border-radius: 999px; display: inline-flex; align-items: center; gap: 4px; flex-shrink: 0;">
                                            <span style="font-weight: 900; font-size: 10px; color: <?= $co['lead_score_data']['color'] ?? $scoreColor ?>;">
                                                <?= $co['lead_score_data']['icon'] ?? $scoreIcon ?> <?= $scoreTotal ?>/100
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Desglose de Score amigable para el usuario -->
                                    <?php if (isset($scoreData['details'])): ?>
                                        <div style="font-size: 10px; color: #64748b; display: flex; gap: 14px; margin-bottom: 8px; font-weight: 700; align-items: center;">
                                            <span title="Fuerza de la señal comercial (BORME)" style="display: flex; align-items: center; gap: 4px; cursor: help;">
                                                <span style="opacity: 0.6; font-size: 12px;">🎯</span> 
                                                <span>Oportunidad: <span style="color: #0f172a;"><?= $scoreData['details']['borme'] ?>%</span></span>
                                            </span>
                                            <span title="Calidad y solidez del perfil de empresa" style="display: flex; align-items: center; gap: 4px; cursor: help;">
                                                <span style="opacity: 0.6; font-size: 12px;">💎</span> 
                                                <span>Perfil: <span style="color: #0f172a;"><?= $scoreData['details']['quality'] ?>%</span></span>
                                            </span>
                                            <span title="Nivel de datos de contacto disponibles" style="display: flex; align-items: center; gap: 4px; cursor: help;">
                                                <span style="opacity: 0.6; font-size: 12px;">📞</span> 
                                                <span>Contacto: <span style="color: #0f172a;"><?= $scoreData['details']['contact'] ?>%</span></span>
                                            </span>
                                        </div>
                                    <?php endif; ?>

                                    
                                    <div class="ae-meta-sub">
                                        <div class="ae-meta-item" title="Actividad">
                                            <span class="ae-meta-icon">🏢</span>
                                            <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 180px;"><?= esc($co['cnae_label'] ?? 'Sector no especificado') ?></span>
                                        </div>
                                        <div style="width: 1px; height: 10px; background: #e2e8f0;"></div>
                                        <div class="ae-meta-item" title="Ubicación">
                                            <span class="ae-meta-icon">📍</span>
                                            <span><?= esc($co['registro_mercantil'] ?? 'N/D') ?></span>
                                        </div>
                                    </div>

                                    <div style="margin-top: 10px; display: flex; align-items: center; gap: 8px;">
                                        <div style="font-size: 10px; font-weight: 800; padding: 4px 10px; border-radius: 8px; <?= $urgencyClass ?> text-transform: uppercase; border: 1px solid currentColor;">
                                            ⏱️ <?= $timingLabel ?>
                                        </div>
                                        <span style="font-size: 10px; color: #94a3b8; font-weight: 600;">(<?= $formatEsDate($co['last_borme_date'] ?? $co['fecha_constitucion']) ?>)</span>
                                        <?php if ($daysSince <= 3) { ?>
                                            <span style="font-size: 10px; font-weight: 900; color: #e11d48; text-transform: uppercase;">🔥 Alta Relevancia</span>
                                        <?php } ?>
                                    </div>
                                </div>
                            </td>

                            <!-- ZONA 2: POTENCIAL COMERCIAL (38%) -->
                            <td class="ae-radar-page__td-opportunity" style="padding: 24px 10px;">
                                <div class="ae-value-box">
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                                        <span class="ae-opp-badge <?= $opp['class'] ?>"><?= $opp['label'] ?></span>
                                        <div style="text-align: right;">
                                            <div style="font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 1px;">Ticket Est.</div>
                                            <div class="ae-ticket-val"><?= $ticket ?></div>
                                        </div>
                                    </div>
                                    <div class="ae-reason-text" style="border-top: 1px solid #f1f5f9; padding-top: 8px; font-style: italic;">
                                        <span style="color: #475569; font-weight: 800; font-style: normal;">Motivo:</span> 
                                        "<?= esc($reason) ?>"
                                    </div>
                                </div>
                            </td>

                            <!-- ZONA 3: GESTIÓN Y ACCIÓN (20%) -->
                            <td class="ae-radar-page__td-actions" style="padding: 24px 20px; text-align: right; vertical-align: top;">
                                <div style="display: flex; flex-direction: column; gap: 10px; align-items: stretch; max-width: 180px; margin-left: auto;">
                                    <?php if ($curStatus === 'nuevo') { ?>
                                        <!-- Acción Primaria -->
                                        <?php if ($isFree) { ?>
                                            <button type="button" 
                                                    onclick="showConversionNudge('Acceso bloqueado', 'Esta empresa está activa ahora mismo. Otros proveedores ya están contactando esta oportunidad. Desbloquea el acceso completo para ver los detalles.', {id: '<?= $co['id'] ?>', name: '<?= esc($co['company_name']) ?>', action: 'contact'})"
                                                    title="Contactar ahora"
                                                    style="background: #2563eb; color: white; width: 100%; height: 42px; border-radius: 10px; font-weight: 800; font-size: 13px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; transition: all 0.2s; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width: 14px; height: 14px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                                Contactar ahora
                                            </button>
                                            <div style="text-align: center; font-size: 9px; color: #64748b; font-weight: 700; margin-top: 4px;">Requiere acceso completo</div>
                                        <?php } else { ?>
                                            <button type="button" class="ae-btn-hover" onclick="handleContactClick(this, '<?= $co['id'] ?>', '<?= esc($co['company_name']) ?>')" 
                                                    style="background: #2563eb; color: white; width: 100%; height: 42px; border-radius: 10px; font-weight: 800; font-size: 13px; border: none; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);">
                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width: 14px; height: 14px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                                Contactar ahora
                                            </button>
                                        <?php } ?>
                                    <?php } ?>
                                    
                                    <!-- Acciones Secundarias -->
                                    <div style="display: flex; gap: 6px;">
                                        <button type="button" class="ae-btn-strategy ae-btn-hover" onclick="analyzeAI('<?= $co['id'] ?>', this, '<?= esc($co['company_name']) ?>', 'analyze')" 
                                                style="flex: 1; height: 36px; border-radius: 8px; font-weight: 700; font-size: 11px; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 6px;">
                                            🎯 Cómo venderle
                                        </button>

                                        <button type="button" class="ae-btn-fav-v3 <?= ($co['is_favorite'] ?? false) ? 'is-active' : '' ?>" onclick="toggleFavorite(this, '<?= $co['id'] ?>')"
                                                style="width: 36px; height: 36px; border-radius: 8px; cursor: pointer; display: flex; align-items: center; justify-content: center;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="<?= ($co['is_favorite'] ?? false) ? '#ffb800' : 'none' ?>" stroke="currentColor" stroke-width="2.5"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                        </button>
                                    </div>
                                    
                                    <!-- Píldora de Estado (V3) -->
                                    <div style="margin-top: 4px;">
                                        <div class="ae-status-pill-v3" onclick="this.querySelector('select').focus()">
                                            <div style="display: flex; align-items: center; gap: 6px;">
                                                <span class="ae-status-dot" style="background: <?= $st['color'] ?>;"></span>
                                                <span class="ae-status-text" style="color: <?= $st['color'] ?>;"><?= $curStatus ?></span>
                                            </div>
                                            <select onchange="updateLeadStatusAndNotify(this, '<?= $co['id'] ?>')" 
                                                    style="position: absolute; opacity: 0; width: 100%; height: 100%; top:0; left:0; cursor: pointer;">
                                                <option value="nuevo" <?= ($curStatus === 'nuevo') ? 'selected' : '' ?>>NUEVO</option>
                                                <option value="contactado" <?= ($curStatus === 'contactado') ? 'selected' : '' ?>>CONTACTADO</option>
                                                <option value="seguimiento" <?= ($curStatus === 'seguimiento') ? 'selected' : '' ?>>SEGUIMIENTO</option>
                                                <option value="negociacion" <?= ($curStatus === 'negociacion') ? 'selected' : '' ?>>NEGOCIACIÓN</option>
                                                <option value="ganado" <?= ($curStatus === 'ganado') ? 'selected' : '' ?>>GANADO</option>
                                            </select>
                                            <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>

                    <?php if ($isFree && !empty($lockedCompanies)) { ?>
                        <tr class="ae-radar-inline-paywall">
                            <td colspan="3" style="padding: 60px 40px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 24px; text-align: left; color: white; position: relative; overflow: hidden; margin: 20px 0;">
                                <!-- Glow Effect -->
                                <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(37,99,235,0.15) 0%, transparent 60%); pointer-events: none;"></div>
                                
                                <div class="paywall-grid" style="max-width: 1000px; margin: 0 auto; position: relative; z-index: 10; display: grid; grid-template-columns: 1.4fr 1fr; gap: 48px; align-items: center;">
                                    <div class="paywall-content">
                                        <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(37,99,235,0.1); color: #60a5fa; padding: 6px 12px; border-radius: 100px; font-size: 11px; font-weight: 800; margin-bottom: 20px; border: 1px solid rgba(37,99,235,0.2); backdrop-filter: blur(4px);">
                                            <span style="display: inline-block; width: 6px; height: 6px; background: #60a5fa; border-radius: 50%; animation: pulse 2s infinite;"></span>
                                            Oportunidades en tiempo real
                                        </div>

                                        <h2 style="font-size: 34px; font-weight: 900; margin-bottom: 16px; letter-spacing: -1px; line-height: 1.1; color: white !important;">Estas empresas están siendo contactadas <span style="background: linear-gradient(to right, #60a5fa, #34d399); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">ahora mismo</span></h2>
                                        <p style="font-size: 16px; color: rgba(255,255,255,0.7); margin-bottom: 32px; line-height: 1.6; max-width: 480px;">No permitas que tu competencia llegue antes. Desbloquea el acceso completo para ver los detalles de contacto antes que desaparezcan.</p>
                                        
                                        <div style="display: flex; flex-direction: column; gap: 16px; align-items: flex-start;">
                                            <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" style="display: inline-flex; align-items: center; gap: 10px; background: #2563eb; color: white; padding: 18px 36px; border-radius: 16px; font-size: 16px; font-weight: 900; text-decoration: none; box-shadow: 0 10px 30px rgba(37,99,235,0.4); transition: transform 0.2s; transform-origin: center;" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform='translateY(0)'">
                                                <span>Acceder ahora antes que tu competencia</span>
                                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                                            </a>
                                            <div style="display: flex; align-items: center; gap: 8px; font-size: 10px; color: #fbbf24; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; background: rgba(251,191,36,0.1); padding: 6px 14px; border-radius: 8px; border: 1px solid rgba(251,191,36,0.2);">
                                                <span style="font-size: 14px;">💰</span> ROI: Recupera la inversión con 1 cliente
                                            </div>
                                        </div>
                                    </div>

                                    <div class="paywall-features" style="background: rgba(255,255,255,0.03); padding: 32px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.06); backdrop-filter: blur(8px);">
                                        <div style="display: flex; flex-direction: column; gap: 20px;">
                                            <div style="display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 700; color: rgba(255,255,255,0.9);">
                                                <span style="color: #10b981; font-size: 18px;">✔</span> Acceso completo hoy
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 700; color: rgba(255,255,255,0.9);">
                                                <span style="color: #10b981; font-size: 18px;">✔</span> Filtros avanzados
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 700; color: rgba(255,255,255,0.9);">
                                                <span style="color: #10b981; font-size: 18px;">✔</span> Detección temprana
                                            </div>
                                            <div style="display: flex; align-items: center; gap: 12px; font-size: 14px; font-weight: 700; color: rgba(255,255,255,0.9);">
                                                <span style="color: #10b981; font-size: 18px;">✔</span> Ventaja competitiva
                                            </div>
                                        </div>
                                        
                                        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid rgba(255,255,255,0.06); font-size: 11px; color: rgba(255,255,255,0.4); font-weight: 600; text-align: center;">
                                            Sin permanencia · Activación inmediata
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <?php foreach ($lockedCompanies as $co): ?>
                            <tr class="ae-radar-row ae-locked-overlay" onclick="showConversionNudge('Oportunidad real bloqueada', 'Activa Radar PRO para ver los detalles de esta empresa y del resto de oportunidades detectadas hoy.', {id: '<?= $co['id'] ?>', action: 'view'})">
                                <td class="ae-radar-page__td-identity" style="padding: 24px 20px;">
                                    <div class="ae-radar-row-blurred" style="filter: blur(12px); pointer-events: none; opacity: 0.4;">
                                        <span style="font-size: 17px; font-weight: 800; color: #0f172a;"><?= esc($co['company_name']) ?></span>
                                        <div class="ae-meta-sub">
                                            <span>B********</span> · <span>Sector Reservado</span>
                                        </div>
                                    </div>
                                    <div style="position: absolute; left: 50%; top: 50%; transform: translate(-50%, -50%); background: #1e293b; color: white; padding: 8px 16px; border-radius: 10px; font-size: 12px; font-weight: 900; box-shadow: 0 4px 12px rgba(0,0,0,0.2); white-space: nowrap; border: 1px solid rgba(255,255,255,0.1);">
                                        🔒 Bloqueado: Radar PRO
                                    </div>
                                </td>
                                <td class="ae-radar-page__td-opportunity" style="padding: 24px 10px;">
                                    <div class="ae-radar-row-blurred" style="filter: blur(12px); pointer-events: none; opacity: 0.4;">
                                        <div class="ae-value-box">
                                            <div style="font-size: 15px; font-weight: 800;">Potencial Reservado</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="ae-radar-page__td-actions" style="padding: 24px 20px; text-align: right;">
                                    <div style="opacity: 0.3;">
                                        <button disabled style="background: #94a3b8; color: white; width: 100%; height: 42px; border-radius: 10px; font-weight: 800; border: none; cursor: not-allowed; text-transform: uppercase; font-size: 11px;">Disponible en PRO</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php } ?>
                <?php } ?>
            </tbody>
        </table>
    </div>

    <?php if (!$isFree && isset($pagination) && isset($pager)) { ?>
        <!-- Dynamic Results Title (Sincronizado con AJAX) -->
        <div class="ae-radar-page__results-info" style="margin-top: 16px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px; padding-left: 16px; padding-right: 16px;">
            <h3 style="font-size: 20px; font-weight: 800; color: #1e293b; margin: 0; font-family: 'Outfit', sans-serif;">
                <?php 
                    $totalItems = $pagination['total'] ?? 0;
                    $filterText = "posibles clientes detectados";
                    if (isset($filters['priority_level']) && !empty($filters['priority_level'])) {
                        $pMap = [
                            'muy_alta' => '<span style="color:#e11d48">🔥 Prioridad Muy Alta</span>', 
                            'alta' => '<span style="color:#ef4444">⚡ Prioridad Alta</span>', 
                            'media' => '<span style="color:#d97706">🟡 Prioridad Media</span>'
                        ];
                        $filterText = "posibles clientes con " . ($pMap[$filters['priority_level']] ?? "prioridad detectada");
                    } elseif (isset($filters['main_act_type']) && !empty($filters['main_act_type'])) {
                        $filterText = "posibles clientes en fase de <span style='color:#2563eb;'>" . esc($filters['main_act_type']) . "</span>";
                    }
                    echo "<strong>" . number_format($totalItems, 0, ',', '.') . "</strong> " . $filterText;
                ?>
            </h3>
            <div style="font-size: 13px; font-weight: 700; color: #64748b; background: #f8fafc; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px;">
                <span style="font-size: 14px;">⚖️</span>
                Ordenado por: <span style="color: #2563eb;">Inteligencia Radar (Relevancia) ↓</span>
            </div>
        </div>

        <div class="ae-radar-page__table-footer" style="display:flex; justify-content:space-between; align-items:center; margin-top:28px; padding:0 26px 30px;">
            <div class="ae-radar-page__pagination-info" style="font-size:13px; font-weight:700; color:#64748b; background:#f8fafc; padding:8px 16px; border-radius:12px; border:1px solid #e2e8f0;">
                Mostrando <span style="color:#1e293b;"><?= $pagination['start'] ?> a <?= $pagination['end'] ?></span> de <span style="color:#1e293b;"><?= number_format($pagination['total']) ?></span> empresas
            </div>
            
            <div class="ae-radar-page__pagination">
                <?= $pager->links('default', 'radar_es') ?>
            </div>
        </div>
    <?php } ?>
</div>

<script>
    /**
     * Feedback visual para el botón de contacto
     */
    function handleContactClick(btn, id, name) {
        // [TRACKING] Registro de clic en Contactar
        fetch('<?= site_url('radar/log-event') ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: `lead_id=${id}&action=click_contact&<?= csrf_token() ?>=<?= csrf_hash() ?>`
        }).catch(err => console.warn('Tracking error:', err));
        
        // Llamada a la lógica original (analyzeAI debe estar definida en el padre)
        if (typeof analyzeAI === 'function') {
            analyzeAI(id, btn, name, 'action');
        } else {
            console.error('analyzeAI no está definida');
        }
    }

    /**
     * Feedback visual para cambio de estado CRM (Flash)
     */
    function updateLeadStatusAndNotify(select, id) {
        const row = select.closest('.ae-radar-row');
        
        // Disparar flash visual
        if (row) {
            row.classList.remove('ae-row-flash');
            void row.offsetWidth; // Force reflow
            row.classList.add('ae-row-flash');
        }
        
        // Llamada a la lógica original
        if (typeof updateLeadStatus === 'function') {
            updateLeadStatus(select, id);
        } else {
            console.error('updateLeadStatus no está definida');
        }
    }
</script>
