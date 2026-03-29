<?php if (empty($favorites)): ?>
    <div class="ae-favorites-empty">
        <div class="ae-favorites-empty__icon">🔍</div>
        <h3 class="ae-favorites-empty__title">No se encontraron leads</h3>
        <p class="ae-favorites-empty__text">Prueba a ajustar tus filtros o buscador.</p>
    </div>
<?php else: ?>
    <div class="ae-favorites-grid">
        <?php foreach ($favorites as $f): ?>
            <div class="ae-fav-card" id="fav-card-<?= $f['company_id'] ?>" data-status="<?= esc($f['status'] ?? 'nuevo') ?>">
                <div class="ae-fav-card__badge-row">
                    <span class="ae-fav-card__status ae-fav-card__status--<?= esc($f['status'] ?? 'nuevo') ?>">
                        <?= ucfirst(esc($f['status'] ?? 'Nuevo')) ?>
                    </span>
                    <span class="ae-radar-page__score ae-radar-page__score--<?= strtolower(str_replace('+', 'plus', $f['lead_score'])) ?>" title="Score de calidad: <?= $f['lead_score'] ?>">
                        <?= $f['lead_score'] ?>
                    </span>
                </div>

                <div class="ae-fav-card__info">
                    <a href="<?= company_url(['cif' => $f['cif'], 'name' => $f['company_name']]) ?>" class="ae-fav-card__company-name">
                        <?= esc($f['company_name']) ?>
                    </a>

                    <?php if (!empty($f['objeto_social'])): ?>
                        <p class="ae-fav-card__activity" title="<?= esc($f['objeto_social']) ?>">
                            <?= esc($f['objeto_social']) ?>
                        </p>
                    <?php endif; ?>

                    <div class="ae-fav-card__meta-grid">
                        <div class="ae-fav-card__meta-item">
                            <span class="ae-fav-card__meta-label">CIF</span>
                            <span class="ae-fav-card__meta-value"><?= esc($f['cif']) ?></span>
                        </div>
                        <div class="ae-fav-card__meta-item">
                            <span class="ae-fav-card__meta-label">Ubicación</span>
                            <span class="ae-fav-card__meta-value"><?= esc($f['municipality'] ?? 'N/D') ?></span>
                        </div>
                    </div>
                </div>

                <div class="ae-fav-card__notes" style="margin-top: auto; padding: 12px; background: #f8fafc; border-radius: 12px; border: 1px solid #f1f5f9;">
                    <div class="ae-fav-card__notes-header">
                        <label class="ae-fav-card__notes-label">Notas rápidas</label>
                        <div id="save-indicator-<?= $f['company_id'] ?>" class="ae-fav-card__save-indicator" style="font-size: 10px; color: #10b981; font-weight: 800; display: none;">GUARDADO ✓</div>
                    </div>
                    <textarea class="ae-fav-card__notes-area" placeholder="Añade seguimiento comercial..." onchange="saveNote(<?= $f['company_id'] ?>, this.value)" style="background: transparent; border: none; padding: 0; min-height: 60px; font-size: 13px;"><?= esc($f['notes'] ?? '') ?></textarea>
                </div>

                <div class="ae-fav-card__footer" style="display: flex; justify-content: space-between; align-items: center; padding-top: 16px; border-top: 1px dashed #e2e8f0; margin-top: 4px;">
                    <div class="ae-fav-card__status-selector">
                        <select onchange="updateStatus(<?= $f['company_id'] ?>, this.value, this)" class="ae-status-select" style="padding: 6px 10px; font-size: 12px;">
                            <option value="nuevo" <?= ($f['status'] == 'nuevo') ? 'selected' : '' ?>>Mover a: Nuevo</option>
                            <option value="contactado" <?= ($f['status'] == 'contactado') ? 'selected' : '' ?>>Contactado</option>
                            <option value="negociacion" <?= ($f['status'] == 'negociacion') ? 'selected' : '' ?>>Negociación</option>
                            <option value="ganado" <?= ($f['status'] == 'ganado') ? 'selected' : '' ?>>Ganado</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="ae-fav-card__remove" onclick="removeFavorite(<?= $f['company_id'] ?>)" title="Eliminar de favoritos">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: 16px; height: 16px;"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path><line x1="10" y1="11" x2="10" y2="17"></line><line x1="14" y1="11" x2="14" y2="17"></line></svg>
                        </button>
                        <button type="button" class="ae-fav-btn ae-fav-btn--ai" onclick="analyzeAI(<?= $f['company_id'] ?>, this, '<?= esc($f['company_name']) ?>')" style="padding: 8px 12px; font-size: 12px;">
                            ✨ IA
                        </button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Paginación -->
    <?php if (($totalPages ?? 1) > 1): ?>
        <div class="ae-favorites-pagination">
            <?php if ($currentPage > 1): ?>
                <button class="ae-pagination-btn" onclick="goToPage(<?= $currentPage - 1 ?>)">Anterior</button>
            <?php endif; ?>

            <div class="ae-pagination-numbers">
                <?php 
                $start = max(1, $currentPage - 2);
                $end = min($totalPages, $currentPage + 2);
                for ($i = $start; $i <= $end; $i++): ?>
                    <button class="ae-pagination-number <?= ($i == $currentPage) ? 'is-active' : '' ?>" onclick="goToPage(<?= $i ?>)"><?= $i ?></button>
                <?php endfor; ?>
            </div>

            <?php if ($currentPage < $totalPages): ?>
                <button class="ae-pagination-btn" onclick="goToPage(<?= $currentPage + 1 ?>)">Siguiente</button>
            <?php endif; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
