<?php 
/**
 * partials/company_risk_teaser.php
 * Teaser de conversión para usuarios públicos / no autenticados en el Perfil de Riesgo
 * 
 * Variables:
 * - $riskProfile (array)
 * - $company (array)
 * - $companyName (string, opcional)
 * - $redirectPath (string, opcional)
 */
$teaserEvents = $riskProfile['data']['canonical_events'] ?? [];
$teaserTotalAlerts = count($teaserEvents);
$teaserHighAlerts = 0;
$teaserMediumAlerts = 0;
foreach ($teaserEvents as $ev) {
    $sev = strtolower($ev['severity'] ?? '');
    if ($sev === 'high') $teaserHighAlerts++;
    elseif ($sev === 'medium') $teaserMediumAlerts++;
}
$teaserScore = (int)($riskProfile['risk_score'] ?? 50);
$teaserColor = '#22c55e';
if ($teaserScore >= 70) $teaserColor = '#ef4444';
elseif ($teaserScore >= 30) $teaserColor = '#f59e0b';

$companyName = $companyName ?? ($company['name'] ?? 'Empresa');
$compCif = $company['cif'] ?? '';
$compId = (int)($company['id'] ?? 0);

if (empty($redirectPath)) {
    $slugVal = $company['slug'] ?? url_title($company['name'] ?? '', '-', true);
    $redirectPath = 'empresa/' . $compId . '-' . $slugVal; 
}
?>
<div style="padding: 20px; position: relative; display: flex; align-items: center; justify-content: center; min-height: 480px; overflow: hidden; background: #fafafa; margin: -24px; margin-bottom: 0;">
    <!-- BLURRED BACKGROUND (Reflejo dinámico del score y factores reales) -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; filter: blur(7px); opacity: 0.45; pointer-events: none; display: flex; flex-wrap: nowrap; gap: 80px; align-items: center; justify-content: center; padding: 24px;">
        <!-- Score Card -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px 20px; text-align: center; width: 190px;">
            <div style="font-size: 0.8rem; font-weight: bold; color: #64748b; margin-bottom: 12px;">NIVEL DE RIESGO</div>
            <div style="width: 80px; height: 75px; background: <?= $teaserColor ?>; border-radius: 12px; margin: 0 auto 12px auto; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 2rem; font-weight: 800;"><?= $teaserScore ?></div>
            <div style="font-size: 1.1rem; font-weight: bold; color: <?= $teaserColor ?>;">ANÁLISIS</div>
        </div>

        <!-- Factors List -->
        <div style="flex: 1; max-width: 480px; display: flex; flex-direction: column; gap: 12px;">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; display: flex; gap: 12px; align-items: center;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-weight: bold;">!</div>
                <div style="flex: 1;">
                    <div style="height: 14px; width: 60%; background: #0f172a; border-radius: 4px; margin-bottom: 6px;"></div>
                    <div style="height: 10px; width: 85%; background: #cbd5e1; border-radius: 3px;"></div>
                </div>
            </div>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; display: flex; gap: 12px; align-items: center;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-weight: bold;">✓</div>
                <div style="flex: 1;">
                    <div style="height: 14px; width: 45%; background: #0f172a; border-radius: 4px; margin-bottom: 6px;"></div>
                    <div style="height: 10px; width: 70%; background: #cbd5e1; border-radius: 3px;"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- FOREGROUND CONVERSION CARD -->
    <div style="position: relative; z-index: 10; display: flex; flex-direction: column; align-items: center; text-align: center; width: 100%; max-width: 560px; background: #ffffff; padding: 36px 32px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;">
        
        <!-- DYNAMIC BORME HOOK BANNER -->
        <?php if ($teaserHighAlerts > 0): ?>
            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 14px; padding: 14px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; text-align: left; width: 100%; box-sizing: border-box;">
                <div style="background: #fee2e2; color: #ef4444; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; font-weight: 800;">
                    ⚠️
                </div>
                <div>
                    <div style="font-weight: 800; color: #991b1b; font-size: 0.92rem; line-height: 1.3;">
                        <?= $teaserTotalAlerts ?> <?= $teaserTotalAlerts === 1 ? 'incidencia societaria' : 'incidencias societarias' ?> (<?= $teaserHighAlerts ?> de gravedad alta en BORME)
                    </div>
                    <div style="color: #7f1d1d; font-size: 0.8rem; margin-top: 2px; line-height: 1.35;">
                        Constan actos registrales relevantes que afectan a la estabilidad de <strong><?= esc(rtrim($companyName, '.')) ?></strong>.
                    </div>
                </div>
            </div>
        <?php elseif ($teaserTotalAlerts > 0): ?>
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 14px; padding: 14px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; text-align: left; width: 100%; box-sizing: border-box;">
                <div style="background: #fef3c7; color: #d97706; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; font-weight: 800;">
                    🔍
                </div>
                <div>
                    <div style="font-weight: 800; color: #92400e; font-size: 0.92rem; line-height: 1.3;">
                        <?= $teaserTotalAlerts ?> <?= $teaserTotalAlerts === 1 ? 'observación de estabilidad societaria' : 'observaciones de estabilidad societaria' ?>
                    </div>
                    <div style="color: #78350f; font-size: 0.8rem; margin-top: 2px; line-height: 1.35;">
                        Existen indicadores clave en el registro oficial para <strong><?= esc(rtrim($companyName, '.')) ?></strong>. Regístrate gratis para ver el desglose.
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px; padding: 14px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; text-align: left; width: 100%; box-sizing: border-box;">
                <div style="background: #dcfce7; color: #16a34a; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; font-weight: 800;">
                    🛡️
                </div>
                <div>
                    <div style="font-weight: 800; color: #166534; font-size: 0.92rem; line-height: 1.3;">
                        Dictamen de Solvencia y Scoring BORME disponible
                    </div>
                    <div style="color: #14532d; font-size: 0.8rem; margin-top: 2px; line-height: 1.35;">
                        Evaluación algorítmica procesada en tiempo real. Crea tu cuenta gratis para ver el resultado.
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <h3 style="font-size: 1.45rem; font-weight: 900; color: #0f172a; margin-bottom: 8px; margin-top: 0; letter-spacing: -0.5px;">
            Consulta el Nivel de Riesgo Oficial
        </h3>
        <p style="color: #475569; margin-bottom: 20px; margin-top: 0; font-size: 0.92rem; line-height: 1.5; max-width: 440px;">
            Accede al semáforo de solvencia, alertas BORME e historial de contratación pública creando tu cuenta gratis:
        </p>
        
        <!-- Registration CTAs -->
        <div style="display: flex; flex-direction: column; gap: 10px; width: 100%; margin-bottom: 16px;">
            <!-- 1-Click Google -->
            <a href="<?= site_url('auth/google') ?>?intent=view_risk_profile&redirect=<?= urlencode($redirectPath) ?>" style="background: #ffffff; color: #1e293b; border: 1.5px solid #cbd5e1; padding: 13px 20px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; box-sizing: border-box; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.03);" onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#94a3b8';" onmouseout="this.style.background='#ffffff'; this.style.borderColor='#cbd5e1';">
                <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
                <span>Continuar con Google (1 Clic)</span>
            </a>

            <!-- Email Signup -->
            <a href="<?= site_url('register') ?>?intent=view_risk_profile&redirect=<?= urlencode($redirectPath) ?>" style="background: #2563eb; color: #fff; padding: 13px 20px; border-radius: 12px; font-weight: 800; text-decoration: none; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; box-sizing: border-box; transition: all 0.2s; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);" onmouseover="this.style.background='#1d4ed8'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='#2563eb'; this.style.transform='translateY(0)';">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                <span>Registrarse con Email (3 Consultas Gratis/mes)</span>
            </a>
        </div>
        
        <div style="margin-top: 4px; text-align: center;">
            <button type="button" onclick="openRiskPdfModal(<?= $compId ?>, '<?= esc($compCif) ?>');" style="background: none; border: none; color: #2563eb; font-size: 0.85rem; font-weight: 700; cursor: pointer; text-decoration: underline; padding: 4px;" onmouseover="this.style.color='#1d4ed8';" onmouseout="this.style.color='#2563eb';">
                o Descargar Dictamen Oficial en PDF (3,90 € + IVA)
            </button>
        </div>
        
        <div style="display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 14px; color: #64748b; font-size: 0.78rem; font-weight: 500;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            Sin tarjeta de crédito &bull; 3 consultas gratuitas al mes &bull; Acceso instantáneo
        </div>
    </div>
</div>
