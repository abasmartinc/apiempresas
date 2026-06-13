<?php
// Unified limits logic
$limitToUse = $isPaid ? ($maxLimitVal ?? 3000) : ($freeLimit ?? 20);
$isLimitReached = ($requestsUsed >= $limitToUse);
$warningThreshold = $isPaid ? ($limitToUse * 0.8) : ($limitToUse * 0.7);
$isWarning = ($requestsUsed >= $warningThreshold);
?>
<section class="activation-main-card" data-track-section="activation_search" style="position: relative; <?= $isLimitReached ? 'border: 2px solid #e11d48; background: #fff1f2;' : '' ?>">
    <?php if (!$isPaid): ?>
        <div style="position: absolute; top: -14px; left: 32px; background: #2152ff; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 900; text-transform: uppercase; letter-spacing: 0.05em; box-shadow: 0 4px 6px rgba(33, 82, 255, 0.3);">Paso 1</div>
    <?php endif; ?>
    
    <div class="activation-header" style="margin-top: 10px;">
        <h2><?= $isLimitReached ? ($isPaid ? 'Límite mensual alcanzado' : 'Límite alcanzado') : ($isPaid ? 'Valida empresas en segundos' : 'Prueba la API en segundos') ?></h2>
        <p><?= $isLimitReached ? 'Has agotado tus ' . number_format($limitToUse, 0, ',', '.') . ' consultas. Activa un plan superior para seguir validando.' : 'Introduce un CIF o nombre y valida una empresa real.' ?></p>
    </div>
    
    <div class="search-form-dash" style="position: relative;">
        <input type="text" id="dash_q" class="search-input-dash" placeholder="Ej: B12345678 o Nombre de Empresa" <?= $isLimitReached ? 'readonly style="background: #f8fafc; cursor: not-allowed;"' : '' ?>>
        <button id="btnDashValidate" class="btn-validate-dash" <?= $isLimitReached ? 'style="background: #94a3b8;"' : '' ?>>Validar ahora</button>
        
        <?php if ($isLimitReached): ?>
            <?php if ($isPaid): ?>
                <div onclick="showUpgradeBusinessModal()" style="position: absolute; inset: 0; cursor: pointer; z-index: 5;"></div>
            <?php else: ?>
                <div onclick="showUpgradeModal()" style="position: absolute; inset: 0; cursor: pointer; z-index: 5;"></div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <?php if (!$isPaid && $requestsUsedThisMonth < 5): ?>
    <!-- CIF Examples (Free only) -->
    <div style="margin-top: 12px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 700;">Prueba con:</span>
        <button class="example-cif-btn" onclick="fillAndSearch('A15075062')">Inditex</button>
        <button class="example-cif-btn" onclick="fillAndSearch('A46103834')">Mercadona</button>
    </div>
    <style>
        .example-cif-btn { background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 6px; padding: 4px 10px; font-size: 0.75rem; color: #475569; font-weight: 700; cursor: pointer; transition: all 0.2s; }
        .example-cif-btn:hover { background: #e2e8f0; color: #2152ff; border-color: #2152ff; }
    </style>
    <script>
        function fillAndSearch(cif) {
            if (window.trackEvent) trackEvent('example_cif_clicked', { cif: cif });
            document.getElementById('dash_q').value = cif;
            document.getElementById('btnDashValidate').click();
        }
    </script>
    <?php endif; ?>

    <?php if (!isset($isBonusUser) || !$isBonusUser): ?>
    <div class="progress-container" style="margin-top: 12px;">
        <div class="progress-bar-bg" style="height: 12px; background: #e2e8f0; border-radius: 6px;">
            <?php 
                $prog = ($limitToUse > 0) ? ($requestsUsed / $limitToUse) * 100 : 0;
                $displayPercent = ($prog > 0 && $prog < 5) ? 5 : ceil($prog); 
            ?>
            <div class="progress-bar-fill" style="--target-width: <?= min(100, $displayPercent) ?>%; <?= $isLimitReached ? 'background: #e11d48;' : ($isWarning ? 'background: #f59e0b;' : '') ?>"></div>
        </div>
        <span class="progress-text" style="font-size: 0.8rem; font-weight: 700; color: #64748b; margin-top: 8px; display: block;">
            <?= number_format($requestsUsed, 0, ',', '.') ?> de <?= number_format($limitToUse, 0, ',', '.') ?> <?= $isPaid ? 'consultas utilizadas' : 'empresas probadas' ?>
        </span>
        <span style="font-size: 0.75rem; color: #64748b; font-weight: 700; display: block; margin-top: 4px;">
            <?php if ($isLimitReached): ?>
                <span style="color: #e11d48;">Has alcanzado el límite <?= $isPaid ? 'de tu plan' : '' ?>.</span>
            <?php else: ?>
                <?= $isPaid ? 'Uso actual de tu suscripción' : 'Completa varias validaciones para ver el valor real de la API' ?>
            <?php endif; ?>
        </span>
    </div>
    <?php endif; ?>

    <div style="margin-top: 16px; font-size: 0.85rem; color: #64748b; font-weight: 600; display: flex; align-items: center; gap: 8px;">
        <div style="background: #eff6ff; color: #2152ff; width: 24px; height: 24px; border-radius: 6px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/></svg>
        </div>
        <span><strong>Caso real:</strong> Valida automáticamente los CIF de tus clientes y evita errores en tu CRM.</span>
    </div>

    <!-- AHA MOMENT CARD (Initially hidden) -->
    <div class="aha-moment-card" id="aha-moment-card">
        <div class="aha-header">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
            Empresa encontrada
        </div>
        <div class="aha-grid">
            <div class="aha-item" style="grid-column: span 2;">
                <div class="aha-label">Nombre / Razón Social</div>
                <div class="aha-val" id="aha-name" style="font-size: 1.1rem; font-weight: 800;">-</div>
            </div>
            <div class="aha-item">
                <div class="aha-label">CIF</div>
                <div class="aha-val" id="aha-cif">-</div>
            </div>
            <div class="aha-item">
                <div class="aha-label">Estado</div>
                <div class="aha-val" id="aha-status">-</div>
            </div>
            <div class="aha-item" style="grid-column: span 2;">
                <div class="aha-label" style="display:flex; justify-content:space-between;">Dirección / Sede Social <?php if (!$isPaid && ($walletBalance ?? 0) <= 0): ?><a href="<?=site_url('billing')?>" style="color:#2152ff;text-decoration:none;">Desbloquear Pro 🔒</a><?php endif; ?></div>
                <div class="aha-val" id="aha-address">-</div>
            </div>
            <div class="aha-item" style="grid-column: span 2;">
                <div class="aha-label" style="display:flex; justify-content:space-between;">Actividad / Objeto Social <?php if (!$isPaid && ($walletBalance ?? 0) <= 0): ?><a href="<?=site_url('billing')?>" style="color:#2152ff;text-decoration:none;">Desbloquear Pro 🔒</a><?php endif; ?></div>
                <div class="aha-val" id="aha-activity">-</div>
            </div>
        </div>
        
        <?php if (!$isPaid && ($walletBalance ?? 0) <= 0): ?>
        <div style="margin-top: 20px; border-top: 1px solid #bae6fd; padding-top: 16px;">
            <p style="font-size: 0.85rem; color: #0369a1; font-weight: 700; margin-bottom: 16px;">Estos datos puedes integrarlos automáticamente en tu CRM o sistema.</p>
            <div style="display: flex; flex-direction: column; gap: 10px;">
                <div style="display: flex; gap: 10px;">
                    <button class="btn-small" id="btnCopyEndpoint">Copiar endpoint</button>
                    <button class="btn-small" id="btnShowJson">Ver en Popup</button>
                </div>
                <a href="<?= site_url('billing') ?>" class="btn-small primary" style="background: #10b981; color: white !important; border: none; text-align: center; justify-content: center; padding: 14px; font-weight: 900; box-shadow: 0 10px 15px -3px rgba(16, 185, 129, 0.3); border-radius: 12px; font-size: 1rem;">Activar Pro y automatizar esto</a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- JSON RAW VIEW INLINE -->
    <div id="inlineJsonContainer" style="display: none; margin-top: 16px; animation: slideDown 0.4s ease-out;">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
            <span style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase;">Respuesta JSON (Datos Reales)</span>
            <button id="btnCopyInlineJson" style="background: none; border: none; color: #2152ff; font-weight: 800; font-size: 0.7rem; cursor: pointer;">Copiar JSON</button>
        </div>
        <pre id="inlineJsonPre" style="background: #1e293b; color: #f8fafc; padding: 16px; border-radius: 10px; font-size: 0.75rem; max-height: 250px; overflow: auto; margin: 0; font-family: 'JetBrains Mono', monospace; border: 1px solid #334155;"></pre>
    </div>
</section>
