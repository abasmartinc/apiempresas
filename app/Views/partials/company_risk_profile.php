<?php 
$score = $riskProfile['risk_score'] ?? 50;
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
<div style="display: flex; flex-wrap: wrap; gap: 32px; align-items: stretch;">
    
    <!-- LEFT COLUMN (Score) -->
    <div style="width: 280px; background: #f8fafc; border-radius: 16px; border: 1px solid #f1f5f9; padding: 22px 20px; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; flex-shrink: 0; box-sizing: border-box;">
        <div style="width: 100%; display: flex; flex-direction: column; align-items: center; gap: 6px; margin-bottom: 20px;">
            <h4 style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin: 0; font-weight: 800; text-align: center;">Nivel de Riesgo</h4>
            <?php if (!empty($riskQuota['is_subscriber'])): ?>
                <span style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; padding: 3px 10px; border-radius: 999px; font-size: 0.7rem; font-weight: 800; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;">
                    ⭐ Solvencia Pro &bull; Ilimitado
                </span>
            <?php else: ?>
                <span style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; padding: 3px 10px; border-radius: 999px; font-size: 0.7rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap;" title="Límite mensual gratuito de 3 empresas">
                    🟢 <?= (int)($riskQuota['views_used'] ?? 1) ?> de 3 consultas este mes
                </span>
            <?php endif; ?>
        </div>
        
        <!-- Circle -->
        <div style="width: 160px; height: 160px; border-radius: 50%; border: 12px solid <?= $color ?>; display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 24px; background: #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.05);">
            <span style="font-size: 4rem; font-weight: 900; color: #0f172a; line-height: 1; letter-spacing: -1px;"><?= $score ?></span>
            <span style="font-size: 0.9rem; font-weight: 600; color: #94a3b8; margin-top: 2px;">de 100</span>
        </div>
        
        <div style="text-align: center; margin-bottom: 24px;">
            <div style="font-size: 1.5rem; font-weight: 800; color: <?= $color ?>; text-transform: uppercase; letter-spacing: 1px; line-height: 1.2;"><?= $riskLevelText ?></div>
            <div style="font-size: 0.85rem; color: #64748b; margin-top: 4px;">Nivel de riesgo</div>
        </div>

        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 16px; display: flex; gap: 12px; align-items: center; width: 100%; box-sizing: border-box;">
            <div style="background: #ecfdf5; color: #10b981; padding: 8px; border-radius: 8px; flex-shrink: 0;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 7 13.5 15.5 8.5 10.5 2 17"></polyline><polyline points="16 7 22 7 22 13"></polyline></svg>
            </div>
            <p style="margin: 0; font-size: 0.75rem; color: #475569; line-height: 1.4; font-weight: 500;">
                <?= esc($riskProfile['data']['summary_message'] ?? 'Puntuación procesada correctamente.') ?>
            </p>
        </div>
    </div>

    <!-- RIGHT COLUMN (Factors) -->
    <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column;">
        
        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
            <div style="background: #eff6ff; color: #3b82f6; padding: 6px; border-radius: 50%;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
            </div>
            <h4 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0; text-transform: uppercase;">Factores Analizados</h4>
        </div>
        <p style="margin: 0 0 24px 0; font-size: 0.9rem; color: #64748b;">Evaluación automática de los principales indicadores de estabilidad corporativa.</p>
        
        <div style="display: flex; flex-direction: column; gap: 16px; margin-bottom: 24px;">
            <?php if (!empty($riskProfile['data']['canonical_events'])): ?>
                <?php foreach ($riskProfile['data']['canonical_events'] as $flag): ?>
                    <?php 
                    $sev = $flag['severity'] ?? 'low';
                    if ($sev === 'high') {
                        $iconColor = '#ef4444';
                        $iconBg = '#fee2e2';
                        $badgeColor = '#b91c1c';
                        $badgeBg = '#fef2f2';
                        $badgeBorder = '#fecaca';
                        $sevLabel = 'ALERTA';
                    } elseif ($sev === 'medium') {
                        $iconColor = '#f59e0b';
                        $iconBg = '#fef3c7';
                        $badgeColor = '#b45309';
                        $badgeBg = '#fffbeb';
                        $badgeBorder = '#fde68a';
                        $sevLabel = 'ATENCIÓN';
                    } else {
                        $iconColor = '#22c55e';
                        $iconBg = '#dcfce7';
                        $badgeColor = '#15803d';
                        $badgeBg = '#f0fdf4';
                        $badgeBorder = '#bbf7d0';
                        $sevLabel = 'POSITIVO';
                    }
                    ?>
                    <div style="border: 1px solid #f1f5f9; border-radius: 12px; padding: 20px; display: flex; gap: 16px; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: <?= $iconBg ?>; color: <?= $iconColor ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <?php if ($sev === 'high'): ?>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            <?php elseif ($sev === 'medium'): ?>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
                            <?php else: ?>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <?php endif; ?>
                        </div>
                        <div style="flex: 1;">
                            <p style="margin: 0 0 6px 0; font-size: 1rem; font-weight: 700; color: #0f172a;"><?= esc(ucwords(strtolower(str_replace('_', ' ', $flag['code'] ?? 'EVENTO REPORTADO')))) ?></p>
                            <p style="margin: 0; font-size: 0.9rem; color: #64748b; line-height: 1.4;"><?= esc($flag['description'] ?? 'Basado en histórico público') ?></p>
                        </div>
                        <span style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; border: 1px solid <?= $badgeBorder ?>; padding: 6px 14px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; flex-shrink: 0;">
                            <?= $sevLabel ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="border: 1px solid #f1f5f9; border-radius: 12px; padding: 20px; display: flex; gap: 16px; align-items: center;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: #dcfce7; color: #22c55e; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    </div>
                    <div style="flex: 1;">
                        <p style="margin: 0 0 6px 0; font-size: 1rem; font-weight: 700; color: #0f172a;">Análisis Favorable</p>
                        <p style="margin: 0; font-size: 0.9rem; color: #64748b; line-height: 1.4;"><?= esc($riskProfile['data']['summary_message'] ?? 'No se han detectado eventos societarios que indiquen riesgo.') ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Info Box: Cómo se calcula -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; display: flex; gap: 20px; align-items: center; position: relative; overflow: hidden;">
            <div style="color: #3b82f6; flex-shrink: 0;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
            </div>
            <div style="flex: 1; z-index: 2;">
                <p style="margin: 0 0 6px 0; font-size: 1rem; font-weight: 700; color: #1e293b;">¿Cómo se calcula?</p>
                <p style="margin: 0; font-size: 0.9rem; color: #475569; line-height: 1.5;">El motor de riesgo evalúa en tiempo real 6 dimensiones clave: estado legal, cumplimiento registral (BORME y Cuentas), gobernanza, capital, volatilidad estructural y factores estabilizadores. Estos datos se consolidan en una puntuación de 0 a 100, donde 100 representa el mayor nivel de riesgo.</p>
            </div>
        </div>

    </div>
</div>

<!-- CTA BANNER: DESCARGAR INFORME DE RIESGO EN PDF -->
<?php 
$compBtnId = !empty($company['id']) ? (int)$company['id'] : 0; 
$compNameStr = !empty($company['name']) ? $company['name'] : 'esta empresa';
?>
<div style="margin-top: 24px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 16px; padding: 24px 28px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 20px; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.12); border: 1px solid #334155;">
    <div style="flex: 1; min-width: 260px;">
        <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.4); color: #93c5fd; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            Dictamen Oficial Descargable
        </div>
        <h4 style="margin: 0 0 6px 0; color: #ffffff; font-size: 1.25rem; font-weight: 800; letter-spacing: -0.3px;">
            Descargar Informe Oficial de Riesgo en PDF
        </h4>
        <p style="margin: 0; color: #94a3b8; font-size: 0.9rem; line-height: 1.4;">
            Obtén el dictamen ejecutivo con certificación de solvencia, semáforo de riesgo y detalle de eventos BORME listo para adjuntar a tu expediente de cliente.
        </p>
        <div style="display: flex; flex-wrap: wrap; gap: 16px; margin-top: 12px; color: #cbd5e1; font-size: 0.8rem; font-weight: 500;">
            <span style="display: flex; align-items: center; gap: 5px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Descarga instantánea en 1 clic
            </span>
            <span style="display: flex; align-items: center; gap: 5px;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Factura deducible con IVA español
            </span>
        </div>
    </div>

    <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
        <?php if (!empty($riskQuota['is_subscriber'])): ?>
            <a href="<?= site_url('empresa/export-risk/' . $compBtnId) ?>" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; padding: 14px 24px; border: none; border-radius: 12px; font-weight: 800; font-size: 1.05rem; display: flex; align-items: center; gap: 10px; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4); text-decoration: none;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(16, 185, 129, 0.5)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(16, 185, 129, 0.4)';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Descargar Informe en PDF 📥
            </a>
            <span style="color: #6ee7b7; font-size: 0.75rem; font-weight: 600;">⭐ Incluido en tu plan Solvencia Pro</span>
        <?php else: ?>
            <button type="button" onclick="openRiskPdfModal(<?= $compBtnId ?>, '<?= esc($company['cif'] ?? '') ?>');" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; padding: 14px 24px; border: none; border-radius: 12px; font-weight: 800; font-size: 1.05rem; display: flex; align-items: center; gap: 10px; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4); text-decoration: none;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(16, 185, 129, 0.5)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 129 rgba(16, 185, 129, 0.4)';">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                Descargar Informe (3,90 € + IVA)
            </button>
            <span style="color: #64748b; font-size: 0.75rem;">Pago seguro vía Stripe &bull; Sin permanencia</span>
        <?php endif; ?>
    </div>
</div>


