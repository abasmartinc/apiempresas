<!-- Modal AI Analysis -->
<div id="ae-ai-modal" class="ae-ai-modal" style="display:none;">
    <div class="ae-ai-modal__backdrop" onclick="closeAIModal()"></div>
    <div class="ae-ai-modal__container">
        <header class="ae-ai-modal__header" style="flex-direction: column; align-items: flex-start; gap: 4px; padding: 16px 24px;">
            <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                <div class="ae-ai-modal__header-left">
                    <span class="ae-ai-modal__badge">IA B2B</span>
                    <h2 class="ae-ai-modal__title" style="margin: 0;">Análisis Estratégico AI</h2>
                </div>
                <button type="button" class="ae-ai-modal__close" onclick="closeAIModal()" style="position: static; font-size: 28px;">×</button>
            </div>
            <div id="ae-ai-modal-company" style="font-size: 14px; font-weight: 700; color: #2563eb; background: #eff6ff; padding: 4px 12px; border-radius: 8px; margin-top: 4px; display: none;"></div>
        </header>
        <div id="ae-ai-content" class="ae-ai-modal__body">
            <!-- Se cargará por AJAX -->
        </div>
    </div>
</div>

<script>
    /**
     * Análisis IA bajo demanda
     */
    function analyzeAI(id, btn, companyName = '') {
        if (!id) return;

        const $modal = document.getElementById('ae-ai-modal');
        const $content = document.getElementById('ae-ai-content');
        const $companyName = document.getElementById('ae-ai-modal-company');

        if (companyName) {
            $companyName.innerText = companyName;
            $companyName.style.display = 'inline-block';
        } else {
            $companyName.style.display = 'none';
        }
        
        // Bloquear botón y mostrar loading en modal
        const originalBtnHtml = btn.innerHTML;
        btn.innerHTML = '⏳...';
        btn.disabled = true;

        $content.innerHTML = `
            <div style="text-align:center; padding: 40px 0;">
                <div class="ae-spinner"></div>
                <p style="margin-top:20px; font-weight:700; color:#1e293b; font-size:18px;">
                    Consultando con la Inteligencia Artificial...
                </p>
                <p style="color:#64748b; font-size:14px;">
                    Analizando objeto social y extrayendo nichos estratégicos.
                </p>
            </div>
        `;
        $modal.style.display = 'flex';
        document.body.style.overflow = 'hidden';

        fetch('<?= site_url('radar/ai-analyze/') ?>' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            btn.innerHTML = originalBtnHtml;
            btn.disabled = false;

            if (data.status === 'success') {
                $content.innerHTML = `
                    <div class="ae-ai-result">
                        <!-- 1. Resumen Comercial -->
                        <div class="ae-ai-card ae-ai-card--summary" style="margin-bottom: 20px; padding: 20px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                            <span class="ae-ai-result__label" style="display: block; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Análisis Comercial</span>
                            <div style="font-size: 18px; font-weight: 800; color: #1e293b; margin-bottom: 8px;">${data.commercial_profile}</div>
                            <p style="font-size: 15px; line-height: 1.6; color: #475569; margin: 0;">${data.summary}</p>
                        </div>

                        <!-- NUEVOS BLOQUES: KPIs Comerciales -->
                        <div class="ai-kpis-grid">
                            <div class="ai-kpi-card ai-kpi-card--prob-${data.conversion_probability.label.toLowerCase()}">
                                <span class="ai-kpi-label">Probabilidad</span>
                                <span class="ai-kpi-value">${data.conversion_probability.label}</span>
                                <span class="ai-kpi-desc">${data.conversion_probability.description}</span>
                            </div>
                            <div class="ai-kpi-card">
                                <span class="ai-kpi-label">Ventana óptima</span>
                                <span class="ai-kpi-value">${data.contact_window.label}</span>
                                <span class="ai-kpi-desc">${data.contact_window.description}</span>
                            </div>
                            <div class="ai-kpi-card">
                                <span class="ai-kpi-label">Ticket estimado</span>
                                <span class="ai-kpi-value">${data.estimated_ticket.label}</span>
                                <span class="ai-kpi-desc">${data.estimated_ticket.description}</span>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                            <!-- 2. Necesidades Probables -->
                            <div class="ae-ai-card" style="padding: 16px; border: 1px solid #e2e8f0; border-radius: 12px;">
                                <span class="ae-ai-result__label" style="display: block; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 12px;">Necesidades probables</span>
                                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                                    ${data.needs.map(n => `<li style="font-size: 13px; color: #334155; display: flex; align-items: center; gap: 8px;"><span style="color:#2563eb">✔</span> ${n}</li>`).join('')}
                                </ul>
                            </div>

                            <!-- 3. Qué venderle primero -->
                            <div class="ae-ai-card" style="padding: 16px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f0f7ff;">
                                <span class="ae-ai-result__label" style="display: block; font-size: 11px; font-weight: 800; color: #1e40af; text-transform: uppercase; margin-bottom: 12px;">Qué venderle primero</span>
                                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                                    ${data.first_offers.map(o => `<li style="font-size: 13px; color: #1e3a8a; font-weight: 700; display: flex; align-items: center; gap: 8px;"><span>🚀</span> ${o}</li>`).join('')}
                                </ul>
                            </div>
                        </div>

                        <!-- NUEVOS BLOQUES: Objeción y Ángulo -->
                        <div class="ai-objection-box">
                            <div class="ai-box-icon">⚠️</div>
                            <div class="ai-box-content">
                                <span class="ai-box-label">Objeción más probable</span>
                                <p class="ai-box-text">"${data.likely_objection}"</p>
                            </div>
                        </div>

                        <div class="ai-angle-box">
                            <div class="ai-box-icon">🎯</div>
                            <div class="ai-box-content">
                                <span class="ai-box-label">Ángulo de ataque recomendado</span>
                                <p class="ai-box-text">${data.attack_angle}</p>
                            </div>
                        </div>

                        <!-- 4. Enfoque de venta y 5. Mensaje sugerido -->
                        <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 24px;">
                            <div style="padding: 16px; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 12px;">
                                <span class="ae-ai-result__label" style="display: block; font-size: 11px; font-weight: 800; color: #92400e; text-transform: uppercase; margin-bottom: 8px;">Enfoque de venta recomendado</span>
                                <p style="font-size: 14px; color: #854d0e; margin: 0; line-height: 1.5;">${data.sales_approach}</p>
                            </div>

                            <div style="padding: 20px; background: #f1f5f9; border-radius: 12px; border: 1px dashed #cbd5e1; position: relative;">
                                <span class="ae-ai-result__label" style="display: block; font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; margin-bottom: 12px;">Mensaje inicial sugerido</span>
                                <p id="ae-ai-message" style="font-size: 14px; line-height: 1.6; color: #1e293b; margin: 0; font-style: italic;">"${data.first_message}"</p>
                            </div>
                        </div>

                        <!-- 6. Acciones Finales -->
                        <div class="ai-action-buttons">
                            <button type="button" class="ai-action-btn ai-action-btn--copy" onclick="copyToClipboard('ae-ai-message', this)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                Copiar mensaje
                            </button>
                            <button type="button" class="ai-action-btn ai-action-btn--list" onclick="addToListFromAI(${id}, this)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"></path></svg>
                                Añadir a lista
                            </button>
                            <button type="button" class="ai-action-btn ai-action-btn--contact" onclick="markAsContactedFromAI(${id}, this)">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Marcar contactado
                            </button>
                        </div>

                        <!-- Señales detectadas -->
                        <div style="padding-top: 20px; border-top: 1px solid #f1f5f9; margin-top: 20px;">
                            <span class="ae-ai-result__label" style="display: block; font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 10px;">Señales comerciales utilizadas</span>
                            <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                                ${data.signals.map(s => `<span style="font-size: 10px; background: #f8fafc; color: #64748b; padding: 4px 10px; border-radius: 20px; border: 1px solid #e2e8f0;">${s}</span>`).join('')}
                            </div>
                        </div>
                    </div>
                `;
            } else {
                $content.innerHTML = `
                    <div style="text-align:center; padding: 40px;">
                        <span style="font-size: 40px;">❌</span>
                        <p style="margin-top:20px; color:#b91c1c; font-weight:bold;">${data.message}</p>
                        <button onclick="closeAIModal()" style="margin-top:20px; padding: 10px 20px; background: #f1f5f9; border:none; border-radius:8px; cursor:pointer;">Cerrar</button>
                    </div>
                `;
            }
        })
        .catch(err => {
            btn.innerHTML = originalBtnHtml;
            btn.disabled = false;
            $content.innerHTML = `
                <div style="text-align:center; padding: 40px 0; color:#ef4444;">
                    <p style="font-size:32px;">⚠️</p>
                    <p style="font-weight:700; margin-top:16px;">Error en el análisis</p>
                    <p style="color:#64748b;">${err.message}</p>
                    <button type="button" class="ae-radar-page__hero-btn ae-radar-page__hero-btn--secondary" 
                            onclick="closeAIModal()" style="margin-top:24px; min-height:40px;">
                        Cerrar
                    </button>
                </div>
            `;
        });
    }

    function closeAIModal() {
        document.getElementById('ae-ai-modal').style.display = 'none';
        document.getElementById('ae-ai-modal-company').innerText = '';
        document.getElementById('ae-ai-modal-company').style.display = 'none';
        document.body.style.overflow = '';
    }

    function copyToClipboard(elementId, btn) {
        const text = document.getElementById(elementId).innerText.replace(/^"|"$/g, '');
        navigator.clipboard.writeText(text).then(() => {
            const original = btn.innerHTML;
            btn.innerHTML = '✅ Copiado';
            btn.classList.add('is-copied');
            setTimeout(() => {
                btn.innerHTML = original;
                btn.classList.remove('is-copied');
            }, 2000);
        });
    }

    // Acciones comerciales con persistencia
    function addToListFromAI(companyId, btn) {
        if (typeof toggleFavorite === 'function') {
            toggleFavorite(btn, companyId);
            btn.innerHTML = '✅ En la lista';
            btn.style.borderColor = '#10b981';
            btn.style.color = '#10b981';
            btn.disabled = true;
        }
    }

    function markAsContactedFromAI(companyId, btn) {
        btn.innerHTML = '⏳...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('company_id', companyId);
        formData.append('status', 'contactado');

        fetch('<?= site_url('radar/update-favorite-status') ?>', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                btn.innerHTML = '✅ Contactado';
                btn.style.background = '#10b981';
                btn.style.borderColor = '#10b981';
                
                // Actualizar estrella si existe en la página
                const rowBtn = document.querySelector(`.ae-radar-page__btn-fav[onclick*="${companyId}"], .ae-fav-btn[onclick*="${companyId}"]`);
                if (rowBtn && !rowBtn.classList.contains('is-active')) {
                    rowBtn.classList.add('is-active');
                    const svg = rowBtn.querySelector('svg');
                    if (svg) svg.setAttribute('fill', 'currentColor');
                }
            } else {
                btn.innerHTML = '❌ Error';
                btn.disabled = false;
            }
        })
        .catch(err => {
            btn.innerHTML = '❌ Error';
            btn.disabled = false;
        });
    }
</script>
