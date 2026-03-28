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

$filters = $filters ?? [];
$allCompanies = $companies ?? [];
$visibleCompanies = $isFree ? array_slice($allCompanies, 0, 10) : $allCompanies;
?>

<div id="radar-list-view">
    <div class="ae-radar-page__lead-top" style="display:flex; justify-content:space-between; align-items:flex-end; padding:24px 26px;">
        <!-- Left Side: Title & Info -->
        <div class="ae-radar-page__lead-headings">
            <h2 class="ae-radar-page__lead-title" style="margin-bottom:4px;">Oportunidades detectadas</h2>
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
                            Excel
                        </a>
                        <a href="<?= site_url('radar/exportar?' . http_build_query(array_merge($filters, ['format' => 'csv']))) ?>" class="ae-radar-page__export-btn" style="background:#475569; padding: 10px 14px; border-radius:10px; color:#fff; text-decoration:none; font-size:12px; font-weight:800; display:flex; align-items:center;" title="Exportar CSV (Datos brutos)">
                            .CSV
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" class="ae-radar-page__export-btn" style="background:#0f172a; padding: 11px 18px; border-radius:12px; color:#fff; text-decoration:none; font-size:13px; font-weight:800;">Activar PRO (79€)</a>
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
                    <th style="text-align:right;">Acceso</th>
                </tr>
            </thead>

            <tbody>
                <?php if (empty($visibleCompanies)): ?>
                    <tr>
                        <td colspan="5" style="text-align:center; padding: 40px; color: #6b7280;">
                            No se han encontrado empresas con los filtros seleccionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($visibleCompanies as $co): ?>
                        <tr class="ae-radar-page__row-visible">
                            <td class="ae-radar-page__td-company">
                                <div class="ae-radar-page__company">
                                    <div class="ae-radar-page__company-header">
                                        <span class="ae-radar-page__score ae-radar-page__score--<?= strtolower(str_replace('+', 'plus', $co['lead_score'])) ?>" title="Score de calidad: <?= $co['lead_score'] ?>">
                                            <?= $co['lead_score'] ?>
                                        </span>
                                        <a href="<?= $isFree ? site_url('leads-empresas-nuevas') : company_url(['cif' => $co['cif'], 'name' => $co['company_name']]) ?>" class="ae-radar-page__company-link">
                                            <span class="ae-radar-page__company-name"><?= esc($co['company_name']) ?></span>
                                        </a>
                                    </div>
                                    <span class="ae-radar-page__company-cif"><?= esc($co['cif']) ?></span>
                                </div>
                                <div class="ae-radar-page__company-actions">
                                    <button type="button" class="ae-radar-page__btn-qv" onclick="openQuickView('<?= $co['id'] ?>')" title="Vista rápida">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
                                    </button>
                                    <button type="button" class="ae-radar-page__btn-ai" 
                                            onclick="analyzeAI('<?= $co['id'] ?>', this)" 
                                            title="Analizar con Inteligencia Artificial">
                                        ✨ IA
                                    </button>
                                    <button type="button" 
                                            class="ae-radar-page__btn-fav <?= ($co['is_favorite'] ?? false) ? 'is-active' : '' ?>" 
                                            onclick="toggleFavorite(this, '<?= $co['id'] ?>')"
                                            title="<?= ($co['is_favorite'] ?? false) ? 'Quitar de favoritos' : 'Guardar en favoritos' ?>">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="<?= ($co['is_favorite'] ?? false) ? 'currentColor' : 'none' ?>" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                    </button>
                                </div>
                                <div class="ae-radar-page__company-purpose" title="<?= esc($co['objeto_social'] ?? '') ?>">
                                    <?= esc(mb_strimwidth($co['objeto_social'] ?? 'Sin objeto social definido.', 0, 100, '...')) ?>
                                </div>
                            </td>
                            <td class="ae-radar-page__td-date">
                                <span class="ae-radar-page__date"><?= $formatEsDate($co['fecha_constitucion']) ?></span>
                            </td>
                            <td class="ae-radar-page__td-location">
                                <div class="ae-radar-page__location">
                                    <span class="ae-radar-page__province"><?= esc($co['registro_mercantil'] ?? 'N/D') ?></span>
                                    <span class="ae-radar-page__municipality"><?= esc($co['municipality'] ?? '') ?></span>
                                </div>
                            </td>
                            <td class="ae-radar-page__td-activity">
                                <div class="ae-radar-page__activity">
                                    <span class="ae-radar-page__badge ae-radar-page__badge--sector">
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
                                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l2.21-2.21a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                            <span class="ae-radar-page__phone-number"><?= esc($co['phone']) ?></span>
                                        </div>
                                    <?php else: ?>
                                        <span class="ae-radar-page__no-phone">-</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                            <td class="ae-radar-page__td-action" style="text-align:right;">
                                <a href="<?= $isFree ? site_url('leads-empresas-nuevas') : company_url(['cif' => $co['cif'], 'name' => $co['company_name']]) ?>" class="ae-radar-page__btn-action <?= $isFree ? 'ae-radar-page__btn-action--free' : '' ?>">
                                    <?= $isFree ? 'Activar Radar PRO ahora' : 'Ver ficha' ?>
                                </a>
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
