<?php
$compBtnId = (int)($company['id'] ?? 0);
$compNameStr = $company['name'] ?? 'Empresa';
$compCifStr = $company['cif'] ?? '';
?>

<!-- PAYWALL CUOTA ALCANZADA (3/3) -->
<div style="padding: 20px; position: relative; display: flex; align-items: center; justify-content: center; min-height: 480px; overflow: hidden; background: #fafafa; margin: -24px; margin-bottom: 0; border-radius: 0 0 16px 16px;">
    
    <!-- BLURRED BACKGROUND (Scorecard & Factors Behind) -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; filter: blur(6px); opacity: 0.45; pointer-events: none; display: flex; flex-wrap: nowrap; gap: 80px; align-items: center; justify-content: center; padding: 24px;">
        <!-- Fake Score Card -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px 20px; text-align: center; width: 190px;">
            <div style="font-size: 0.8rem; font-weight: bold; color: #64748b; margin-bottom: 12px;">NIVEL DE RIESGO</div>
            <div style="width: 80px; height: 75px; background: #f59e0b; border-radius: 12px; margin: 0 auto 12px auto; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 2rem; font-weight: 800;">55</div>
            <div style="font-size: 1.1rem; font-weight: bold; color: #f59e0b;">MEDIO</div>
        </div>

        <!-- Fake Factors List -->
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

    <!-- FOREGROUND CONVERSION MODAL / CARD -->
    <div style="position: relative; z-index: 10; display: flex; flex-direction: column; align-items: center; text-align: center; width: 100%; max-width: 580px; background: #ffffff; padding: 36px 32px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.12); border: 1px solid #e2e8f0;">
        
        <!-- Icon & Badge -->
        <div style="display: inline-flex; align-items: center; gap: 6px; background: #fffbeb; border: 1px solid #fde68a; color: #b45309; padding: 4px 12px; border-radius: 999px; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; margin-bottom: 14px;">
            ⚠️ Límite Mensual Alcanzado (3/3)
        </div>

        <h3 style="font-size: 1.5rem; font-weight: 900; color: #0f172a; margin: 0 0 10px 0; letter-spacing: -0.5px;">
            Has alcanzado tus 3 consultas gratuitas
        </h3>
        
        <p style="color: #475569; margin: 0 0 24px 0; font-size: 0.92rem; line-height: 1.5; max-width: 480px;">
            Has analizado el límite mensual de 3 empresas gratuitas. Para consultar el dictamen de <strong style="color: #0f172a;"><?= esc($compNameStr) ?></strong> o desbloquear todas las empresas:
        </p>

        <!-- COMPARATIVA VS COMPETENCIA TRADICIONAL (PRICE ANCHORING) -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px 16px; width: 100%; margin-bottom: 20px; box-sizing: border-box; text-align: left;">
            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                <span style="font-size: 0.72rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                    Comparativa frente a informes tradicionales
                </span>
                <span style="background: #dcfce7; color: #15803d; font-size: 0.7rem; font-weight: 800; padding: 2px 8px; border-radius: 999px;">
                    Ahorras hasta un 85%
                </span>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px; font-size: 0.78rem;">
                <!-- Informa / Axesor -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 12px; color: #64748b;">
                    <div style="font-weight: 700; color: #94a3b8; font-size: 0.75rem; margin-bottom: 2px;">Informa D&B / Axesor</div>
                    <div style="color: #ef4444; font-weight: 800; font-size: 0.95rem;">~25 € <span style="font-size: 0.7rem; font-weight: normal; color: #94a3b8;">/ informe</span></div>
                    <div style="font-size: 0.68rem; color: #94a3b8; margin-top: 2px;">Exige permanencia o saldo prepago</div>
                </div>
                <!-- APIEmpresas -->
                <div style="background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 10px; padding: 8px 12px; color: #1e40af;">
                    <div style="font-weight: 800; color: #1d4ed8; font-size: 0.75rem; margin-bottom: 2px;">APIEmpresas Solvencia</div>
                    <div style="color: #16a34a; font-weight: 900; font-size: 0.95rem;">29 € <span style="font-size: 0.7rem; font-weight: normal; color: #1e40af;">/ mes</span></div>
                    <div style="font-size: 0.68rem; color: #1e40af; font-weight: 700; margin-top: 2px;">✅ Ilimitado &bull; Cancela cuando quieras</div>
                </div>
            </div>
        </div>

        <!-- Monetization Options Grid -->
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; width: 100%; margin-bottom: 20px; text-align: left;">
            
            <!-- Option 1: PDF Download (Transactional) -->
            <div style="border: 2px solid #10b981; background: #f0fdf4; border-radius: 14px; padding: 18px 16px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="font-size: 0.72rem; font-weight: 800; color: #15803d; text-transform: uppercase; margin-bottom: 4px;">PAGO PUNTUAL</div>
                    <div style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Informe en PDF</div>
                    <div style="font-size: 0.78rem; color: #475569; line-height: 1.35; margin-bottom: 12px;">Descarga oficial con score IES y alertas BORME de esta empresa.</div>
                </div>
                <div>
                    <div style="font-size: 1.25rem; font-weight: 900; color: #059669; margin-bottom: 8px;">3,90 € <span style="font-size: 0.75rem; font-weight: 600; color: #64748b;">+ IVA</span></div>
                    <button type="button" onclick="openRiskPdfModal(<?= $compBtnId ?>, '<?= esc($compCifStr) ?>');" style="width: 100%; background: #10b981; color: #fff; border: none; padding: 10px 12px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#059669';" onmouseout="this.style.background='#10b981';">
                        Descargar PDF 💳
                    </button>
                </div>
            </div>

            <!-- Option 2: Monthly Pro Subscription (MRR) -->
            <div style="border: 2px solid #2563eb; background: #eff6ff; border-radius: 14px; padding: 18px 16px; display: flex; flex-direction: column; justify-content: space-between; position: relative;">
                <div style="position: absolute; top: -10px; right: 12px; background: #2563eb; color: #fff; font-size: 0.65rem; font-weight: 800; padding: 2px 8px; border-radius: 999px; text-transform: uppercase;">
                    RECOMENDADO
                </div>
                <div>
                    <div style="font-size: 0.72rem; font-weight: 800; color: #1d4ed8; text-transform: uppercase; margin-bottom: 4px;">PASE MENSUAL</div>
                    <div style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 6px;">Solvencia Pro</div>
                    <div style="font-size: 0.78rem; color: #475569; line-height: 1.35; margin-bottom: 12px;">Consultas ilimitadas de riesgo y solvencia mercantil sin restricciones.</div>
                </div>
                <div>
                    <div style="font-size: 1.25rem; font-weight: 900; color: #1d4ed8; margin-bottom: 8px;">29 € <span style="font-size: 0.75rem; font-weight: 600; color: #64748b;">/ mes</span></div>
                    <form method="post" action="<?= site_url('billing/checkout') ?>" style="margin: 0;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="plan" value="risk_pro">
                        <input type="hidden" name="period" value="monthly">
                        <button type="submit" style="width: 100%; border: none; cursor: pointer; text-align: center; background: #2563eb; color: #fff; padding: 10px 12px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; transition: background 0.2s;" onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
                            Activar Solvencia Pro ⭐
                        </button>
                    </form>
                </div>
            </div>

        </div>

        <div style="display: flex; align-items: center; gap: 8px; color: #64748b; font-size: 0.78rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            Sin permanencia &bull; Factura con IVA deducible &bull; Pasarela segura Stripe
        </div>

    </div>
</div>
