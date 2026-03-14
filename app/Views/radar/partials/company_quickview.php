<div class="ae-qv">
    <div class="ae-qv__header">
        <div class="ae-qv__brand">
            <span class="ae-qv__badge">Perfil de empresa</span>
            <h2 class="ae-qv__name"><?= esc($co['company_name']) ?></h2>
            <div class="ae-qv__meta">
                <span class="ae-qv__cif">CIF: <?= esc($co['cif'] ?? 'N/D') ?></span>
                <?php 
                    $isValidDate = false;
                    $displayDate = '';
                    if (!empty($co['fecha_constitucion']) && $co['fecha_constitucion'] !== '0000-00-00') {
                        try {
                            $dt = new \DateTime($co['fecha_constitucion']);
                            $year = (int)$dt->format('Y');
                            if ($year >= 1900 && $year <= (date('Y') + 2)) {
                                $isValidDate = true;
                                $displayDate = $dt->format('d/m/Y');
                            }
                        } catch (\Exception $e) {}
                    }
                ?>
                <?php if ($isValidDate): ?>
                    <span class="ae-qv__sep">•</span>
                    <span class="ae-qv__date">Const. <?= esc($displayDate) ?></span>
                <?php endif; ?>
            </div>
        </div>
        <button type="button" class="ae-qv__close" onclick="closeQuickView()">&times;</button>
    </div>

    <div class="ae-qv__body">
        <div class="ae-qv__section">
            <h3 class="ae-qv__section-title">Actividad e Identidad</h3>
            <div class="ae-qv__grid">
                <div class="ae-qv__item">
                    <label>Objeto Social</label>
                    <p><?= esc($co['objeto_social'] ?? 'Sin información detallada.') ?></p>
                </div>
                <div class="ae-qv__item">
                    <label>Sector (CNAE)</label>
                    <div class="ae-radar-page__badge ae-radar-page__badge--sector" style="display:inline-block; margin-top:4px;">
                        <?= esc($co['cnae_label'] ?? 'N/D') ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="ae-qv__section">
            <h3 class="ae-qv__section-title">Administración y Cargos</h3>
            <div class="ae-qv__admins">
                <?php if ($isFree): ?>
                    <div class="ae-radar-page__locked-phone" style="padding: 12px 0;">🔒 Administradores bloqueados en Plan Free</div>
                <?php elseif (!empty($admins)): ?>
                    <ul class="ae-qv__admin-list">
                        <?php foreach ($admins as $admin): ?>
                            <li class="ae-qv__admin-item">
                                <div class="ae-qv__admin-info">
                                    <span class="ae-qv__admin-name"><?= esc($admin['name']) ?></span>
                                    <span class="ae-qv__admin-pos"><?= esc($admin['position'] ?? 'Administrador') ?></span>
                                </div>
                                <a href="https://www.linkedin.com/search/results/all/?keywords=<?= urlencode($admin['name'] . ' ' . $co['company_name']) ?>" 
                                   target="_blank" class="ae-qv__admin-lk">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                                    LinkedIn
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p class="ae-qv__admin-none">No se han encontrado administradores registrados.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="ae-qv__section">
            <h3 class="ae-qv__section-title">Ubicación y Contacto</h3>
            <div class="ae-qv__grid ae-qv__grid--2">
                <div class="ae-qv__item">
                    <label>Provincia / Registro</label>
                    <p><?= esc($co['registro_mercantil'] ?? 'N/D') ?></p>
                </div>
                <div class="ae-qv__item">
                    <label>Municipio</label>
                    <p><?= esc($co['municipality'] ?? 'N/D') ?></p>
                </div>
                <div class="ae-qv__item">
                    <label>Teléfono</label>
                    <p class="ae-qv__contact-value">
                        <?php if ($isFree): ?>
                            <span class="ae-radar-page__locked-phone">🔒 Bloqueado</span>
                        <?php elseif (!empty($co['phone'])): ?>
                            <strong><?= esc($co['phone']) ?></strong>
                        <?php else: ?>
                            -
                        <?php endif; ?>
                    </p>
                </div>
                <div class="ae-qv__item">
                    <label>Acceso Web</label>
                    <p>No disponible en QuickView</p>
                </div>
            </div>
        </div>

        <div class="ae-qv__actions">
            <a href="<?= $isFree ? site_url('precios-radar') : company_url(['cif' => $co['cif'], 'name' => $co['company_name']]) ?>" class="ae-qv__btn ae-qv__btn--primary">
                Ver ficha completa <?= $isFree ? '🔒' : '→' ?>
            </a>
            <button type="button" class="ae-qv__btn ae-qv__btn--text" onclick="closeQuickView()">Cerrar</button>
        </div>
    </div>
</div>
