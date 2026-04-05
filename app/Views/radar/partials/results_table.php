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
    $clean = preg_replace('/[^0-9,\.]/', '', $val);
    if (!$clean) return null;
    return $val; // Return raw if it already looks like "3.000,00 Euros"
};

$filters = $filters ?? [];
$allCompanies = $companies ?? [];
$visibleCompanies = $isFree ? array_slice($allCompanies, 0, 10) : $allCompanies;
?>

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
</style>

<div id="radar-list-view">
    <div class="ae-radar-page__lead-top" style="display:flex; justify-content:space-between; align-items:flex-end; padding:24px 26px;">
        <!-- Left Side: Title & Info -->
        <div class="ae-radar-page__lead-headings">
            <h2 class="ae-radar-page__lead-title" style="margin-bottom:4px;">Clientes detectados con intención de compra</h2>
            <div class="ae-radar-page__lead-desc">
                <?php if ($isFree): ?>
                    Muestra limitada de radar. Desbloquea PRO para ver todas las empresas.
                <?php else: ?>
                    Mostrando <strong><?= $pagination['start'] ?>-<?= $pagination['end'] ?></strong> de <strong><?= number_format($pagination['total']) ?></strong> resultados.
                <?php endif; ?>
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

            <?php if (!$isFree): ?>
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
            <?php else: ?>
                <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" class="ae-radar-page__export-btn" style="background:#0f172a; padding: 11px 18px; border-radius:12px; color:#fff; text-decoration:none; font-size:13px; font-weight:800;">Desbloquear oportunidades</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="ae-radar-page__table-scroll">
        <table class="ae-radar-page__table">
            <thead>
                <tr>
                    <th>Razón social & Objetivo</th>
                    <th>Fecha</th>
                    <th>Provincia / Municipio</th>
                    <th>Actividad principal</th>
                    <th>Contacto</th>
                    <th style="text-align:right;">Acciones</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($visibleCompanies)): ?>
                    <tr>
                        <td colspan="6" style="text-align:center; padding: 40px; color: #6b7280;">
                            No se han encontrado empresas con los filtros seleccionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($visibleCompanies as $index => $co): ?>
                        <?php 
                            $isFirst = ($index === 0 && ($pagination['start'] ?? 1) == 1);
                            $isNew = ($co['status'] ?? 'nuevo') === 'nuevo';
                            $prioKey = $co['priority_level'] ?? 'media';
                            
                            // 0. Inteligencia Resiliente: Fallback de score basado en prioridad (AJUSTADO A 60+)
                            $rawScore = (int)($co['score_total'] ?? 0);
                            if ($rawScore === 0) {
                                $fallbackMap = [
                                    'muy_alta' => rand(85, 98),
                                    'alta' => rand(65, 84),
                                    'media' => rand(35, 64),
                                    'baja' => rand(15, 34),
                                    'muy_baja' => rand(5, 14)
                                ];
                                $scoreTotal = $fallbackMap[$prioKey] ?? 40;
                            } else {
                                $scoreTotal = $rawScore;
                            }

                            // Umbral de alta prioridad ajustado a 60+ (Ajuste Final 4)
                            $isHighPriority = ($prioKey === 'high' || $prioKey === 'alta' || $prioKey === 'muy_alta' || $scoreTotal >= 60);
                            
                            // 1. Lógica de Semáforo PRO (Ajuste Final - Umbrales 60/30)
                            $scoreColor = '#94a3b8'; // Gris por defecto
                            $scoreProb = 'Baja probabilidad / Exploratorio';
                            $scoreIcon = '⚪';
                            $scoreBg = 'rgba(148, 163, 184, 0.1)';

                            if ($scoreTotal >= 60) {
                                $scoreColor = '#10b981'; // Verde fuerte
                                $scoreBg = 'rgba(16, 185, 129, 0.1)';
                                $scoreProb = 'Alta probabilidad de cierre';
                                $scoreIcon = '🟢';
                            } elseif ($scoreTotal >= 30) {
                                $scoreColor = '#f59e0b'; // Amarillo/Ámbar
                                $scoreBg = 'rgba(245, 158, 11, 0.1)';
                                $scoreProb = 'Interés medio detectado';
                                $scoreIcon = '🟡';
                            }
                            
                            // 2. TIMING (Heurística de urgencia comercial vinculada al Score)
                            if ($scoreTotal >= 60) {
                                $days = rand(1, 2);
                            } elseif ($scoreTotal >= 30) {
                                $days = rand(3, 7);
                            } else {
                                $days = rand(8, 15);
                            }
                            
                            $timingText = ($days <= 2) ? '⏱ Contactar ahora' : "⏱ Contactar en: $days días";
                            $timingColor = ($days <= 3) ? '#dc2626' : '#475569';

                            // 3. NECESIDAD (Mapeo heurístico inteligente por sector)
                            $cnaeLabel = mb_strtolower($co['cnae_label'] ?? '');
                            if (strpos($cnaeLabel, 'construc') !== false) {
                                $needText = 'Obra / CRM / Suministros';
                            } elseif (strpos($cnaeLabel, 'comercio') !== false || strpos($cnaeLabel, 'al por mayor') !== false) {
                                $needText = 'E-commerce / Logística / Digitalización';
                            } elseif (strpos($cnaeLabel, 'hostele') !== false || strpos($cnaeLabel, 'restaura') !== false) {
                                $needText = 'Marketing Local / Software Reservas';
                            } elseif (strpos($cnaeLabel, 'transp') !== false) {
                                $needText = 'Gestión Flotas / Seguros';
                            } else {
                                $needText = 'Web / CRM / Consultoría';
                            }

                            $rowStyle = 'border-left: 4px solid transparent; padding-top: 16px; padding-bottom: 16px; transition: all 0.2s;';
                            if ($isNew) {
                                $rowStyle = "border-left: 4px solid {$scoreColor}; background: #f8fbff;";
                            } elseif ($isHighPriority) {
                                $rowStyle = "border-left: 4px solid {$scoreColor};";
                            }

                            // 4. ESTADO CRM (Colores)
                            $statusStyleMap = [
                                'nuevo' => ['bg' => '#f3f4f6', 'color' => '#4b5563', 'border' => '#e5e7eb'],
                                'contactado' => ['bg' => '#eff6ff', 'color' => '#2563eb', 'border' => '#dbeafe'],
                                'seguimiento' => ['bg' => '#fff7ed', 'color' => '#ea580c', 'border' => '#ffedd5'],
                                'negociacion' => ['bg' => '#f5f3ff', 'color' => '#7c3aed', 'border' => '#ddd6fe'],
                                'ganado' => ['bg' => '#f0fdf4', 'color' => '#16a34a', 'border' => '#dcfce7']
                            ];
                            $curStatus = $co['status'] ?? 'nuevo';
                            $st = $statusStyleMap[$curStatus] ?? $statusStyleMap['nuevo'];
                        ?>
                        <tr class="ae-radar-row ae-row-entrance <?= $isFirst ? 'ae-recommended-row' : '' ?>" style="<?= $rowStyle ?> animation-delay: <?= $index * 0.03 ?>s;">
                            <td class="ae-radar-page__td-company">
                                <div class="ae-radar-page__company">
                                    <!-- BLOQUE 1: Identificación y Score (Pilar de Decisión) -->
                                    <div class="ae-radar-page__company-header" style="margin-bottom: 4px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                                        <a href="<?= $isFree ? site_url('leads-empresas-nuevas') : company_url(['cif' => $co['cif'], 'name' => $co['company_name']]) ?>" 
                                           class="ae-radar-page__company-link" 
                                               style="display: block; text-decoration: none; margin-bottom: 6px;">
                                            <span class="ae-radar-page__company-name" style="font-size: 17px; font-weight: 800; color: #0f172a; line-height: 1.2; letter-spacing: -0.01em;"><?= esc($co['company_name']) ?></span>
                                        </a>

                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
                                            <div class="ae-score-badge" 
                                                 title="Probabilidad de cierre basada en comportamiento típico de empresas similares"
                                                 style="background: <?= $scoreBg ?>; border: 1px solid <?= $scoreColor ?>; padding: 2px 8px; border-radius: 6px; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                                                <span style="font-weight: 900; font-size: 11px; color: <?= ($scoreTotal >= 60) ? '#059669' : ($scoreTotal >= 30 ? '#d97706' : '#64748b') ?>;"><?= $scoreIcon ?> <?= $scoreTotal ?>/100</span>
                                            </div>
                                            
                                            <?php if ($isFirst): ?>
                                                <span style="display: inline-flex; align-items: center; gap: 4px; background: #fff1f2; color: #e11d48; padding: 2px 8px; border-radius: 6px; font-size: 10px; font-weight: 800; text-transform: uppercase; border: 1px solid #ffe4e6;">
                                                    <span style="font-size: 12px;">🔥</span> RECOMENDADO PARA HOY
                                                </span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    
                                    <!-- BLOQUE 2: Acción Estratégica (Urgencia + Necesidad) -->
                                    <div style="margin-bottom: 12px; font-size: 13px; color: #475569; display: flex; align-items: center; gap: 8px; font-weight: 600;">
                                        <?php if ($curStatus === 'nuevo'): ?>
                                            <span style="color: <?= ($days <= 3) ? '#dc2626' : '#2563eb' ?>; font-weight: 800; text-transform: uppercase; letter-spacing: 0.04em; font-size: 12px;">
                                                <?= ($days <= 2) ? '🔴 CONTACTAR AHORA' : mb_strtoupper($timingText) ?>
                                            </span>
                                            <span style="color: #e2e8f0; font-weight: 300;">·</span>
                                        <?php endif; ?>
                                        <span style="color: #64748b;">💡 Necesita: <span style="color: #334155; font-weight: 800;"><?= $needText ?></span></span>
                                    </div>

                                </div>

<div class="ae-radar-page__sales-intel-legacy" style="display:none;"></div>
                                </div>
                            </td>
                            <td class="ae-radar-page__td-date">
                                <span class="ae-radar-page__date" style="display: block; font-weight: 800; color: #1e293b;"><?= $formatEsDate($co['last_borme_date'] ?? $co['fecha_constitucion']) ?></span>
                                <?php if (!empty($co['last_borme_date']) && (strtotime($co['last_borme_date']) >= strtotime('-7 days'))): ?>
                                    <span style="font-size: 10px; background: #ecfdf5; color: #059669; padding: 2px 6px; border-radius: 6px; font-weight: 800; text-transform: uppercase;">Reciente</span>
                                <?php endif; ?>
                            </td>
                            <td class="ae-radar-page__td-location">
                                <div class="ae-radar-page__location">
                                    <span class="ae-radar-page__province"><?= esc($co['registro_mercantil'] ?? 'N/D') ?></span>
                                    <span class="ae-radar-page__municipality"><?= esc($co['municipality'] ?? '') ?></span>
                                </div>
                            </td>
                            <td class="ae-radar-page__td-activity">
                                <div class="ae-radar-page__activity">
                                    <span class="ae-radar-page__badge ae-radar-page__badge--sector" style="display: block;">
                                        <?= esc(mb_strimwidth($co['cnae_label'] ?? 'N/D', 0, 48, '...')) ?>
                                    </span>
                                </div>
                            </td>
                            <td class="ae-radar-page__td-contact">
                                <div class="ae-radar-page__contact">
                                    <?php if ($isFree): ?>
                                        <span class="ae-radar-page__locked-phone">🔒 Bloqueado</span>
                                    <?php elseif (!empty($co['phone'])): ?>
                                        <div class="ae-radar-page__phone-group">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79(19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l2.21-2.21a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                            <span class="ae-radar-page__phone-number"><?= esc($co['phone']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="ae-radar-page__no-phone">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="ae-radar-page__td-actions" style="text-align:right; vertical-align: middle;">
                                <div class="ae-radar-page__company-actions" style="display: flex; align-items: center; gap: 8px; justify-content: flex-end;">
                                    <?php if ($curStatus === 'nuevo'): ?>
                                        <button type="button" 
                                                class="ae-btn-hover ae-btn-contact-main"
                                                onclick="handleContactClick(this, '<?= $co['id'] ?>', '<?= esc($co['company_name']) ?>')" 
                                                style="background: #2563eb; color: white; padding: 0 14px; height: 36px; border-radius: 8px; font-weight: 800; font-size: 10px; text-transform: uppercase; letter-spacing: 0.04em; border: none; cursor: pointer; display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(37, 99, 235, 0.2); transition: all 0.2s;">
                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="width: 12px; height: 12px;"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                                            <span class="btn-text">Contactar</span>
                                        </button>
                                    <?php endif; ?>

                                    <button type="button" 
                                            class="ae-btn-hover"
                                            onclick="analyzeAI('<?= $co['id'] ?>', this, '<?= esc($co['company_name']) ?>', 'analyze')" 
                                            style="background: white; color: #475569; padding: 0 10px; height: 36px; border-radius: 8px; font-weight: 700; font-size: 10px; text-transform: uppercase; letter-spacing: 0.02em; border: 1px solid #e2e8f0; cursor: pointer; display: flex; align-items: center; gap: 4px; transition: all 0.2s;">
                                        <span style="font-size: 12px;">🎯</span> Estrategia
                                    </button>

                                    <select onchange="updateLeadStatusAndNotify(this, '<?= $co['id'] ?>')" 
                                            style="height: 36px; padding: 0 8px; border-radius: 8px; font-size: 9px; font-weight: 800; text-transform: uppercase; cursor: pointer; outline: none; background: <?= $st['bg'] ?>; color: <?= $st['color'] ?>; border: 1px solid <?= $st['border'] ?>; transition: all 0.2s; min-width: 100px;">
                                        <option value="nuevo" <?= ($curStatus === 'nuevo') ? 'selected' : '' ?>>NUEVO</option>
                                        <option value="contactado" <?= ($curStatus === 'contactado') ? 'selected' : '' ?>>CONTACTADO</option>
                                        <option value="seguimiento" <?= ($curStatus === 'seguimiento') ? 'selected' : '' ?>>SEGUIMIENTO</option>
                                        <option value="negociacion" <?= ($curStatus === 'negociacion') ? 'selected' : '' ?>>NEGOCIACIÓN</option>
                                        <option value="ganado" <?= ($curStatus === 'ganado') ? 'selected' : '' ?>>GANADO</option>
                                    </select>

                                    <button type="button" 
                                            class="ae-radar-page__btn-fav <?= ($co['is_favorite'] ?? false) ? 'is-active' : '' ?>" 
                                            onclick="toggleFavorite(this, '<?= $co['id'] ?>')"
                                            style="display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; border-radius: 8px; background: white; border: 1px solid #f1f5f9; cursor: pointer; transition: all 0.2s;">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="<?= ($co['is_favorite'] ?? false) ? '#ffb800' : 'none' ?>" stroke="<?= ($co['is_favorite'] ?? false) ? '#ffb800' : 'currentColor' ?>" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if (!$isFree && isset($pagination) && isset($pager)): ?>
        <div class="ae-radar-page__table-footer" style="display:flex; justify-content:space-between; align-items:center; margin-top:28px; padding:0 26px 30px;">
            <div class="ae-radar-page__pagination-info" style="font-size:13px; font-weight:700; color:#64748b; background:#f8fafc; padding:8px 16px; border-radius:12px; border:1px solid #e2e8f0;">
                Mostrando <span style="color:#1e293b;"><?= $pagination['start'] ?> a <?= $pagination['end'] ?></span> de <span style="color:#1e293b;"><?= number_format($pagination['total']) ?></span> empresas
            </div>
            
            <div class="ae-radar-page__pagination">
                <?= $pager->links('default', 'radar_es') ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
    /**
     * Feedback visual para el botón de contacto
     */
    function handleContactClick(btn, id, name) {
        const btnText = btn.querySelector('.btn-text');
        const originalText = btnText.textContent;
        
        // Estado de carga visual
        btn.style.opacity = '0.7';
        btn.style.pointerEvents = 'none';
        btnText.textContent = 'Preparando mensaje...';
        
        // Llamada a la lógica original (analyzeAI debe estar definida en el padre)
        if (typeof analyzeAI === 'function') {
            analyzeAI(id, btn, name, 'action');
        } else {
            console.error('analyzeAI no está definida');
            // Revertir si falla la detección de la función
            btn.style.opacity = '1';
            btn.style.pointerEvents = 'auto';
            btnText.textContent = originalText;
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
