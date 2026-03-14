<?php
$formatEsDate = function($dateStr, $format = 'd M Y') {
    if (empty($dateStr) || $dateStr === '0000-00-00') return 'Reciente';
    
    try {
        $date = new \DateTime($dateStr);
        $timestamp = $date->getTimestamp();
        $year = (int)$date->format('Y');
        
        // Validación de rango de años (ej: 1900 hasta el año actual + 2)
        if ($year < 1900 || $year > (date('Y') + 2)) {
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
?>
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
            <?php if (empty($companies)): ?>
                <tr>
                    <td colspan="5" style="text-align:center; padding: 40px; color: #6b7280;">
                        No se han encontrado empresas con los filtros seleccionados.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($companies as $co): ?>
                    <tr class="ae-radar-page__row-visible">
                        <td class="ae-radar-page__td-company">
                            <div class="ae-radar-page__company">
                                <div class="ae-radar-page__company-header">
                                    <span class="ae-radar-page__score ae-radar-page__score--<?= strtolower(str_replace('+', 'plus', $co['lead_score'])) ?>" title="Score de calidad: <?= $co['lead_score'] ?>">
                                        <?= $co['lead_score'] ?>
                                    </span>
                                    <a href="<?= $isFree ? site_url('precios-radar') : company_url(['cif' => $co['cif'], 'name' => $co['company_name']]) ?>" class="ae-radar-page__company-link">
                                        <span class="ae-radar-page__company-name"><?= esc($co['company_name']) ?></span>
                                    </a>
                                </div>
                                <span class="ae-radar-page__company-cif"><?= esc($co['cif']) ?></span>
                            </div>
                            <div class="ae-radar-page__company-actions">
                                <button type="button" class="ae-radar-page__btn-qv" onclick="openQuickView('<?= $co['id'] ?>')">
                                    Vista rápida
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
                            <a href="<?= $isFree ? site_url('precios-radar') : company_url(['cif' => $co['cif'], 'name' => $co['company_name']]) ?>" class="ae-radar-page__btn-action <?= $isFree ? 'ae-radar-page__btn-action--free' : '' ?>">
                                <?= $isFree ? 'Ver planes' : 'Ver ficha' ?>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
