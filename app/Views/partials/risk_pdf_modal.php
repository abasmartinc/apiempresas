<?php
$compBtnId = !empty($company['id']) ? (int)$company['id'] : 0; 
$compNameStr = !empty($company['name']) ? $company['name'] : 'esta empresa';
$compCifStr = !empty($company['cif']) ? $company['cif'] : '';
?>
<!-- Risk PDF Modal Component -->
<div id="risk-pdf-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.75); backdrop-filter: blur(8px); z-index: 9999999; align-items: center; justify-content: center; padding: 20px;">
    <div style="background: #ffffff; border-radius: 24px; width: 100%; max-width: 940px; padding: 0; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); position: relative; overflow-y: auto; max-height: 90vh; display: flex; flex-wrap: wrap;">
        
        <button type="button" onclick="closeRiskPdfModal();" style="position: absolute; top: 15px; right: 15px; background: #f1f5f9; border: none; color: #64748b; cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.2s; z-index: 10;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a'">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>
        
        <!-- Left Side: Value proposition (Dynamic) -->
        <div style="flex: 1 1 380px; background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 36px 30px; border-radius: 24px 0 0 24px; border-right: 1px solid #334155; color: #ffffff;">
            
            <!-- Risk Report Details -->
            <div id="risk-left-view-risk">
                <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.4); color: #6ee7b7; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 16px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"></polyline></svg>
                    Dictamen Oficial (1-2 págs)
                </div>
                <h3 style="margin: 0 0 12px 0; font-size: 1.35rem; color: #ffffff; font-weight: 800; line-height: 1.25;">
                    Informe de Riesgo y Solvencia
                </h3>
                <p style="color: #94a3b8; margin-bottom: 20px; line-height: 1.45; font-size: 0.88rem;">
                    Dictamen ejecutivo de estabilidad mercantil de <strong style="color: #ffffff;"><?= esc($compNameStr) ?></strong> listo para imprimir o adjuntar:
                </p>

                <ul style="list-style: none; padding: 0; margin: 0 0 24px 0; display: flex; flex-direction: column; gap: 14px;">
                    <li style="display: flex; gap: 10px; align-items: flex-start;">
                        <span style="color: #10b981; flex-shrink: 0; margin-top: 2px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                        <div style="font-size: 0.85rem; color: #e2e8f0; line-height: 1.35;"><strong>Índice IES (0-100)</strong> y semáforo de riesgo oficial.</div>
                    </li>
                    <li style="display: flex; gap: 10px; align-items: flex-start;">
                        <span style="color: #10b981; flex-shrink: 0; margin-top: 2px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                        <div style="font-size: 0.85rem; color: #e2e8f0; line-height: 1.35;"><strong>Alertas de Concursos y Cierres</strong> publicados en el BORME.</div>
                    </li>
                    <li style="display: flex; gap: 10px; align-items: flex-start;">
                        <span style="color: #10b981; flex-shrink: 0; margin-top: 2px;"><svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                        <div style="font-size: 0.85rem; color: #e2e8f0; line-height: 1.35;"><strong>Contratos Públicos y Ayudas</strong> del Estado verificadas.</div>
                    </li>
                </ul>

                <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 12px 14px; font-size: 0.78rem; color: #94a3b8; line-height: 1.4;">
                    🔒 Descarga inmediata tras el pago con factura de IVA deducible.
                </div>
            </div>

            <!-- Dossier 360º Details -->
            <div id="risk-left-view-dossier" style="display: none;">
                <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(59, 130, 246, 0.2); border: 1px solid rgba(59, 130, 246, 0.4); color: #93c5fd; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; margin-bottom: 16px;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                    Dossier Completo 360º (4 págs)
                </div>
                <h3 style="margin: 0 0 12px 0; font-size: 1.35rem; color: #ffffff; font-weight: 800; line-height: 1.25;">
                    Dossier Integral 360º
                </h3>
                <p style="color: #94a3b8; margin-bottom: 18px; line-height: 1.4; font-size: 0.85rem;">
                    Dossier ejecutivo completo de <strong style="color: #ffffff;"><?= esc($compNameStr) ?></strong> con toda la inteligencia comercial y legal:
                </p>

                <ul style="list-style: none; padding: 0; margin: 0 0 20px 0; display: flex; flex-direction: column; gap: 10px;">
                    <li style="display: flex; gap: 8px; align-items: flex-start;">
                        <span style="color: #60a5fa; flex-shrink: 0; margin-top: 2px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                        <div style="font-size: 0.82rem; color: #e2e8f0; line-height: 1.3;"><strong>Datos Generales y Contacto:</strong> Dirección, CNAE, provincia y registro.</div>
                    </li>
                    <li style="display: flex; gap: 8px; align-items: flex-start;">
                        <span style="color: #34d399; flex-shrink: 0; margin-top: 2px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                        <div style="font-size: 0.82rem; color: #e2e8f0; line-height: 1.3;"><strong>Índice de Estabilidad:</strong> Scoring algorítmico y alertas BORME.</div>
                    </li>
                    <li style="display: flex; gap: 8px; align-items: flex-start;">
                        <span style="color: #a78bfa; flex-shrink: 0; margin-top: 2px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                        <div style="font-size: 0.82rem; color: #e2e8f0; line-height: 1.3;"><strong>Estructura y BORME:</strong> Cronología de actos mercantiles y estado.</div>
                    </li>
                    <li style="display: flex; gap: 8px; align-items: flex-start;">
                        <span style="color: #f472b6; flex-shrink: 0; margin-top: 2px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                        <div style="font-size: 0.82rem; color: #e2e8f0; line-height: 1.3;"><strong>Administradores y Cargos:</strong> Órganos de gobierno y directivos.</div>
                    </li>
                    <li style="display: flex; gap: 8px; align-items: flex-start;">
                        <span style="color: #fbbf24; flex-shrink: 0; margin-top: 2px;"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg></span>
                        <div style="font-size: 0.82rem; color: #e2e8f0; line-height: 1.3;"><strong>Contratos y Subvenciones:</strong> Adjudicaciones y ayudas oficiales.</div>
                    </li>
                </ul>

                <div style="background: rgba(255, 255, 255, 0.05); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 12px; padding: 10px 12px; font-size: 0.78rem; color: #94a3b8; line-height: 1.35;">
                    ⭐ Incluye personalización de marca, logotipo y pie de página.
                </div>
            </div>

        </div>

        <!-- Right Side: Fast Checkout Form -->
        <div style="flex: 1 1 450px; padding: 36px 32px; background: #ffffff;">
            <h3 style="margin: 0 0 6px 0; font-size: 1.35rem; color: #0f172a; font-weight: 800;">Opciones de Descarga</h3>
            <p style="color: #64748b; margin-bottom: 20px; font-size: 0.88rem;">Selecciona el informe que deseas descargar:</p>

            <div id="risk-checkout-status"></div>

            <form id="risk-pdf-form" onsubmit="handleRiskPdfSubmit(event, this);">
                <input type="hidden" name="company_id" id="risk-modal-company-id" value="<?= $compBtnId ?>">
                <input type="hidden" name="cif" id="risk-modal-cif" value="<?= esc($compCifStr) ?>">
                <input type="hidden" name="report_type" id="risk-modal-report-type" value="risk">
                <?= csrf_field() ?>
                
                <!-- Report selection cards -->
                <div style="display: flex; flex-direction: column; gap: 10px; margin-bottom: 20px;">
                    <label id="label-opt-risk" style="border: 2px solid #2563eb; background: #eff6ff; border-radius: 12px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.2s;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="radio" name="opt_report" value="risk" checked onchange="updateRiskModalPrice(this.value);" style="accent-color: #2563eb; transform: scale(1.2);">
                            <div>
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">Informe de Riesgo y Solvencia</div>
                                <div style="font-size: 0.78rem; color: #64748b;">Semáforo, alertas BORME y contratos públicos</div>
                            </div>
                        </div>
                        <span style="font-weight: 900; color: #2563eb; font-size: 1.1rem; white-space: nowrap;">3,90 € <span style="font-size: 0.75rem; font-weight: 600;">+ IVA</span></span>
                    </label>

                    <label id="label-opt-dossier" style="border: 1px solid #cbd5e1; background: #ffffff; border-radius: 12px; padding: 14px 16px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: all 0.2s;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <input type="radio" name="opt_report" value="dossier" onchange="updateRiskModalPrice(this.value);" style="accent-color: #2563eb; transform: scale(1.2);">
                            <div>
                                <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">Dossier Integral 360º <span style="background: #ecfdf5; color: #059669; font-size: 0.7rem; font-weight: 700; padding: 2px 6px; border-radius: 4px; margin-left: 4px;">Todo incluido</span></div>
                                <div style="font-size: 0.78rem; color: #64748b;">Riesgo + Administradores + BORME + 4 págs</div>
                            </div>
                        </div>
                        <span style="font-weight: 900; color: #0f172a; font-size: 1.1rem; white-space: nowrap;">5,90 € <span style="font-size: 0.75rem; font-weight: 600;">+ IVA</span></span>
                    </label>
                </div>

                <!-- Solvencia Pro Subscription Upsell Link -->
                <div style="background: #f8fafc; border: 1px dashed #cbd5e1; border-radius: 10px; padding: 10px 14px; margin-bottom: 18px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="font-size: 0.78rem; color: #475569;">
                        ¿Consultas varias empresas al mes?
                    </div>
                    <a href="<?= site_url('billing') ?>" style="font-size: 0.78rem; font-weight: 800; color: #2563eb; text-decoration: none;" onmouseover="this.style.textDecoration='underline';" onmouseout="this.style.textDecoration='none';">
                        Solvencia Pro Ilimitado (29€/mes) &rarr;
                    </a>
                </div>

                <div style="margin-bottom: 16px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; color: #1e293b; font-size: 0.88rem;">Tu Correo Electrónico <span style="color:red">*</span></label>
                    <input type="email" name="email" required placeholder="tu@empresa.com" value="<?= esc(session('email') ?? '') ?>" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
                </div>

                <div style="margin-bottom: 22px;">
                    <label style="display: block; margin-bottom: 6px; font-weight: 700; color: #1e293b; font-size: 0.88rem;">Nombre de tu Empresa / Consultoría <span style="font-weight: 400; color: #64748b;">(Opcional)</span></label>
                    <input type="text" name="agency_name" placeholder="APIEmpresas" value="APIEmpresas" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.95rem; box-sizing: border-box;">
                </div>

                <button type="submit" id="btn-risk-modal-submit" style="width: 100%; padding: 14px 16px; background: #10b981; color: white; border: none; border-radius: 10px; font-weight: 800; font-size: 1rem; cursor: pointer; transition: background 0.2s; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35); white-space: nowrap; display: flex; align-items: center; justify-content: center; gap: 8px;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                    <span>Pagar 3,90 € + IVA y Descargar PDF 💳</span>
                </button>
                
                <div style="display: flex; align-items: center; justify-content: center; gap: 6px; margin-top: 14px; color: #64748b; font-size: 0.78rem;">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    Pasarela bancaria Stripe con cifrado SSL 256-bit
                </div>
            </form>
        </div>
    </div>
</div>

<script>
window.openRiskPdfModal = function(companyId, cif) {
    if(companyId) {
        const input = document.getElementById('risk-modal-company-id');
        if(input) input.value = companyId;
    }
    if(cif) {
        const cifInput = document.getElementById('risk-modal-cif');
        if(cifInput) cifInput.value = cif;
    }
    const modal = document.getElementById('risk-pdf-modal');
    if(modal) {
        modal.style.display = 'flex';
    }
};

window.closeRiskPdfModal = function() {
    const modal = document.getElementById('risk-pdf-modal');
    if(modal) {
        modal.style.display = 'none';
    }
};

window.updateRiskModalPrice = function(type) {
    const typeInput = document.getElementById('risk-modal-report-type');
    const submitBtn = document.getElementById('btn-risk-modal-submit');
    const cardRisk = document.getElementById('label-opt-risk');
    const cardDossier = document.getElementById('label-opt-dossier');
    const viewRisk = document.getElementById('risk-left-view-risk');
    const viewDossier = document.getElementById('risk-left-view-dossier');

    if(typeInput) typeInput.value = type;

    if (type === 'dossier') {
        if(submitBtn) submitBtn.innerHTML = '<span>Pagar 5,90 € + IVA y Descargar Dossier 💳</span>';
        if(cardRisk) {
            cardRisk.style.borderColor = '#cbd5e1';
            cardRisk.style.background = '#ffffff';
        }
        if(cardDossier) {
            cardDossier.style.borderColor = '#2563eb';
            cardDossier.style.background = '#eff6ff';
        }
        if(viewRisk) viewRisk.style.display = 'none';
        if(viewDossier) viewDossier.style.display = 'block';
    } else {
        if(submitBtn) submitBtn.innerHTML = '<span>Pagar 3,90 € + IVA y Descargar PDF 💳</span>';
        if(cardRisk) {
            cardRisk.style.borderColor = '#2563eb';
            cardRisk.style.background = '#eff6ff';
        }
        if(cardDossier) {
            cardDossier.style.borderColor = '#cbd5e1';
            cardDossier.style.background = '#ffffff';
        }
        if(viewRisk) viewRisk.style.display = 'block';
        if(viewDossier) viewDossier.style.display = 'none';
    }
};

let isRiskSubmitting = false;
window.handleRiskPdfSubmit = function(e, formEl) {
    e.preventDefault();
    if(isRiskSubmitting) return;
    isRiskSubmitting = true;

    const btnSubmit = document.getElementById('btn-risk-modal-submit');
    const statusArea = document.getElementById('risk-checkout-status');
    
    btnSubmit.disabled = true;
    btnSubmit.innerHTML = 'Conectando con Stripe... ⏳';
    
    const formData = new FormData(formEl);
    
    fetch('<?= site_url("empresa/checkout-premium-pdf") ?>', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if(data.status === 'success') {
            statusArea.innerHTML = `
                <div style="background: #eff6ff; color: #1e3a8a; padding: 15px; border-radius: 8px; margin-bottom: 15px; font-weight: bold; text-align: center;">
                    Redirigiendo a pasarela segura... 💳
                </div>
            `;
            window.location.href = data.checkout_url;
        } else {
            isRiskSubmitting = false;
            statusArea.innerHTML = `
                <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 14px; border-radius: 8px; margin-bottom: 15px; font-weight: bold; font-size: 0.88rem; text-align: center;">
                    ⚠️ ${data.message || 'Error al procesar la solicitud'}
                </div>
            `;
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = '<span>Pagar y Descargar PDF 💳</span>';
        }
    })
    .catch(error => {
        console.error(error);
        isRiskSubmitting = false;
        statusArea.innerHTML = `
            <div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 14px; border-radius: 8px; margin-bottom: 15px; font-weight: bold; font-size: 0.88rem; text-align: center;">
                ⚠️ Error de comunicación con la pasarela
            </div>
        `;
        btnSubmit.disabled = false;
        btnSubmit.innerHTML = '<span>Pagar y Descargar PDF 💳</span>';
    });
};
</script>
