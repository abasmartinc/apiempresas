<!-- Modal AI Analysis -->
<div id="ae-ai-modal" class="ae-ai-modal" style="display:none;">
    <div class="ae-ai-modal__backdrop" onclick="closeAIModal()"></div>
    <div class="ae-ai-modal__container">
        <header class="ae-ai-modal__header" style="flex-direction: column; align-items: flex-start; gap: 4px; padding: 16px 24px;">
            <div style="display: flex; justify-content: space-between; width: 100%; align-items: center;">
                <div class="ae-ai-modal__header-left">
                    <span class="ae-ai-modal__badge">IA B2B</span>
                    <h2 class="ae-ai-modal__title" style="margin: 0;">🎯 Oportunidad detectada</h2>
                    <p style="margin: 4px 0 0 0; font-size: 13px; color: #64748b; font-weight: 600;">Alta probabilidad de cierre si contactas en el momento adecuado</p>
                </div>
                <button type="button" class="ae-ai-modal__close" onclick="closeAIModal()" style="position: static; font-size: 28px;">×</button>
            </div>
            <div id="ae-ai-modal-company" style="font-size: 14px; font-weight: 700; color: #2563eb; background: #eff6ff; padding: 4px 12px; border-radius: 8px; margin-top: 8px; display: none;"></div>
            <div class="ae-ai-modal__urgency-warning" style="background: #fef2f2; color: #dc2626; padding: 6px 12px; border-radius: 6px; font-size: 12px; font-weight: 800; margin-top: 8px; border: 1px solid #fee2e2; display: flex; align-items: center; gap: 6px;">
                <span>⚠️</span> Otros proveedores pueden contactar antes
            </div>
        </header>
        <div id="ae-ai-content" class="ae-ai-modal__body">
            <!-- Se cargará por AJAX -->
        </div>
    </div>
</div>

<script>
    /**
     * Análisis IA bajo demanda
     * @param {string} intent - 'analyze' o 'action'
     */
    function analyzeAI(id, btn, companyName = '', intent = 'analyze') {
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

        fetch('<?= site_url('radar/ai-analyze/') ?>' + id, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            btn.innerHTML = originalBtnHtml;
            btn.disabled = false;

            if (data.status === 'success') {
                const contact = data.contact_status;
                const isContactMissing = !contact.has_any_contact;

                $content.innerHTML = `
                    <div class="ae-ai-result">
                        
                        <!-- 0. Estado de Contacto -->
                        <div class="ai-contact-status ${isContactMissing ? 'ai-contact-status--missing' : 'ai-contact-status--ok'}">
                            <span class="ai-contact-status__dot"></span>
                            <div>
                                <strong style="display: block;">${contact.status_title}</strong>
                                <span style="font-size: 11px; opacity: 0.9;">${contact.status_message}</span>
                            </div>
                        </div>

                        <!-- 0.1 Oportunidad Temprana (Condicional) -->
                        ${data.early_opportunity_message ? `
                            <div class="ai-early-opportunity-box">
                                <span class="ai-early-opportunity-box__icon">💎</span>
                                <p class="ai-early-opportunity-box__text">${data.early_opportunity_message}</p>
                            </div>
                        ` : ''}

                        <!-- 1. Resumen Comercial -->
                        <div class="ae-ai-card ae-ai-card--summary" style="margin-bottom: 20px; padding: 20px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                            <span class="ae-ai-result__label" style="display: block; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 8px;">Análisis Comercial</span>
                            <div style="font-size: 18px; font-weight: 800; color: #1e293b; margin-bottom: 8px;">${data.commercial_profile}</div>
                            <p style="font-size: 15px; line-height: 1.6; color: #475569; margin: 0;">${data.summary}</p>
                        </div>

                        <!-- 2. KPIs Comerciales -->
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
                            <!-- 3. Necesidades Probables -->
                            <div class="ae-ai-card" style="padding: 16px; border: 1px solid #e2e8f0; border-radius: 12px;">
                                <span class="ae-ai-result__label" style="display: block; font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 12px;">Necesidades probables</span>
                                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                                    ${data.needs.map(n => `<li style="font-size: 13px; color: #334155; display: flex; align-items: center; gap: 8px;"><span style="color:#2563eb">✔</span> ${n}</li>`).join('')}
                                </ul>
                            </div>

                            <!-- 4. Qué venderle primero -->
                            <div class="ae-ai-card" style="padding: 16px; border: 1px solid #e2e8f0; border-radius: 12px; background: #f0f7ff;">
                                <span class="ae-ai-result__label" style="display: block; font-size: 11px; font-weight: 800; color: #1e40af; text-transform: uppercase; margin-bottom: 12px;">Qué venderle primero</span>
                                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 8px;">
                                    ${data.first_offers.map(o => `<li style="font-size: 13px; color: #1e3a8a; font-weight: 700; display: flex; align-items: center; gap: 8px;"><span>🚀</span> ${o}</li>`).join('')}
                                </ul>
                            </div>
                        </div>

                        <!-- 5. Objeción y Ángulo -->
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

                        <!-- 6. Recomendación Operativa -->
                        <div class="ai-operational-box">
                            <span class="ai-operational-box__label">Estrategia sugerida</span>
                            <p class="ai-operational-box__text">${data.operational_recommendation}</p>
                        </div>

                        <!-- 6.1 Plan de Acción (NUEVO) -->
                        <div id="ai-action-plan-container">
                            ${(data.followup && data.followup.exists) ? renderActionPlan(data.followup) : ''}
                        </div>

                        <!-- 7. Enfoque de venta y Mensaje sugerido -->
                        <div style="display: flex; flex-direction: column; gap: 20px; margin-bottom: 24px;">
                            <div style="padding: 16px; background: #fffbeb; border: 1px solid #fef3c7; border-radius: 12px;">
                                <span class="ae-ai-result__label" style="display: block; font-size: 11px; font-weight: 800; color: #92400e; text-transform: uppercase; margin-bottom: 8px;">Enfoque de venta recomendado</span>
                                <p style="font-size: 14px; color: #854d0e; margin: 0; line-height: 1.5;">${data.sales_approach}</p>
                            </div>

                            <div id="ae-ai-message-section" style="padding: 20px; background: #f1f5f9; border-radius: 12px; border: 2px dashed #2563eb; position: relative; transition: all 0.5s;">
                                <span class="ae-ai-result__label" style="display: block; font-size: 11px; font-weight: 800; color: #2563eb; text-transform: uppercase; margin-bottom: 12px;">Mensaje inicial sugerido</span>
                                <p id="ae-ai-message" style="font-size: 15px; line-height: 1.6; color: #1e293b; margin: 0 0 16px 0; font-style: italic; font-weight: 500;">"${data.first_message}"</p>
                                
                                <!-- Botones de acción inmediata sobre el mensaje -->
                                    <button type="button" class="ai-action-btn ai-action-btn--copy" style="background: #2563eb; color: white; border: none; height: 42px; display: flex; align-items: center; justify-content: center; font-weight: 700; width: 100%; font-size: 15px;" onclick="copyToClipboard('ae-ai-message', this)">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="width:16px; margin-right: 8px;"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                        📋 Copiar mensaje sugerido
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- 8. CTA Principal Dinámico -->
                        <div class="ai-primary-action-wrap">
                            <button type="button" id="ai-main-cta" 
                                    class="ai-primary-action ${isContactMissing ? 'ai-primary-action--prepare' : ''} ${(data.followup && data.followup.exists) ? 'ai-primary-action--active' : ''}" 
                                    onclick="${(data.followup && data.followup.exists) ? '' : (isContactMissing ? 'prepareLeadForContact(' + id + ', this)' : 'handleDirectContact(' + id + ')')}"
                                    ${(data.followup && data.followup.exists) ? 'disabled' : ''}>
                                ${ (data.followup && data.followup.exists) 
                                    ? '⏳ En seguimiento' 
                                    : (isContactMissing ? '⚡ Preparar contacto' : '📞 ' + data.primary_action.label) 
                                }
                            </button>
                        </div>

                        <!-- 9. Acciones Finales Dinámicas -->
                        <div class="ai-action-buttons">
                            ${isContactMissing ? `
                                <button type="button" class="ai-action-btn ai-action-btn--alert" onclick="alertWhenContactAvailable(${id}, this)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path><path d="M13.73 21a2 2 0 0 1-3.46 0"></path></svg>
                                    Avisarme contacto
                                </button>
                            ` : `
                                <button type="button" class="ai-action-btn ai-action-btn--contact" onclick="markAsContactedFromAI(${id}, this)">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Marcar contactado
                                </button>
                            `}
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

                // Si la intención es 'action', hacemos scroll automático al mensaje
                if (intent === 'action') {
                    setTimeout(() => {
                        const $msgSection = document.getElementById('ae-ai-message-section');
                        if ($msgSection) {
                            $msgSection.scrollIntoView({ behavior: 'smooth', block: 'center' });
                            $msgSection.style.background = '#eff6ff';
                            $msgSection.style.boxShadow = '0 0 20px rgba(37, 99, 235, 0.2)';
                        }
                    }, 200);
                }
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


    function markAsContactedFromAI(companyId, btn) {
        btn.innerHTML = '⏳...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('company_id', companyId);
        formData.append('status', 'contactado');
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

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
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo actualizar el estado.', confirmButtonColor: '#2563eb' });
                btn.innerHTML = '❌ Reintentar';
                btn.disabled = false;
            }
        })
        .catch(err => {
            btn.innerHTML = '❌ Error';
            btn.disabled = false;
        });
    }

    /**
     * Gestión de contacto directo
     * TODO: Integrar con sistema de telefonía o apertura de mailto
     */
    function handleDirectContact(companyId) {
        // Por ahora, como fallback, marcamos como contactado y abrimos una alerta de éxito
        Swal.fire({
            title: 'Contacto Directo',
            text: 'Esta funcionalidad se integrará próximamente con tu CRM y sistema de telefonía IP.',
            icon: 'info',
            confirmButtonColor: '#2563eb'
        });
        // Opcionalmente podrías activar el marcado automático:
        // markAsContactedFromAI(companyId, document.querySelector('.ai-primary-action'));
    }

    /**
     * Alerta de disponibilidad de contacto
     * TODO: Crear endpoint en backend para suscribirse a notificaciones de este lead
     */
    function alertWhenContactAvailable(companyId, btn) {
        const original = btn.innerHTML;
        btn.innerHTML = '⏳...';
        btn.disabled = true;

        // Simulamos una suscripción exitosa
        setTimeout(() => {
            btn.innerHTML = '✅ Te avisaremos';
            btn.style.background = '#059669';
            btn.style.color = '#fff';
            btn.style.borderColor = '#059669';
            
            // Nota: Aquí iría la llamada fetch al backend para registrar la alerta
            console.log("Suscripción de contacto para ID: " + companyId);
        }, 800);
    }

    /**
     * Flujo Real: Preparar Lead para Contacto
     */
    function prepareLeadForContact(companyId, btn) {
        const message = document.getElementById('ae-ai-message')?.innerText?.replace(/^"|"$/g, '') || '';
        const originalHtml = btn.innerHTML;
        
        btn.innerHTML = '<span class="ae-spinner ae-spinner--small"></span> Guardando...';
        btn.disabled = true;

        const formData = new FormData();
        formData.append('message', message);
        formData.append('notes', 'Lead preparado desde Modal IA - Plan de acción activado.');
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch('<?= site_url('radar/prepare-contact/') ?>' + companyId, {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                btn.innerHTML = '⏳ En seguimiento';
                btn.classList.add('ai-primary-action--active');
                
                // Renderizar plan de acción suavemente
                const planContainer = document.getElementById('ai-action-plan-container');
                planContainer.innerHTML = renderActionPlan({
                    exists: true,
                    status: 'seguimiento',
                    notify_when_contact: true,
                    message_saved: true
                });
                planContainer.style.opacity = 0;
                setTimeout(() => { planContainer.style.opacity = 1; planContainer.style.transition = 'opacity 0.5s'; }, 10);

                // Actualizar UI de la tabla/lista si es posible
                if (typeof toggleFavorite === 'function') {
                    const favBtn = document.querySelector(`.ae-radar_fav_btn[data-id="${companyId}"]`);
                    if (favBtn) favBtn.classList.add('is-active');
                }
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al preparar contacto',
                    text: data.message || 'La acción no se pudo completar. Por favor, inténtalo de nuevo.',
                    confirmButtonColor: '#2563eb'
                });
                btn.innerHTML = originalHtml;
                btn.disabled = false;
            }
        })
        .catch(err => {
            console.error(err);
            btn.innerHTML = originalHtml;
            btn.disabled = false;
        });
    }

    function renderActionPlan(followup) {
        return `
            <div class="ai-action-plan">
                <div class="ai-action-plan__header">
                    <span class="ai-action-plan__icon">📋</span>
                    <strong class="ai-action-plan__title">Plan de acción activado</strong>
                </div>
                <div class="ai-action-plan__content">
                    <div class="ai-action-plan__item">
                        <span class="ai-action-plan__check">✔</span>
                        <span>Lead guardado en <strong>seguimiento</strong></span>
                    </div>
                    <div class="ai-action-plan__item">
                        <span class="ai-action-plan__check">✔</span>
                        <span>Mensaje preparado y guardado</span>
                    </div>
                    <div class="ai-action-plan__item">
                        <span class="ai-action-plan__check">✔</span>
                        <span>Aviso automático activado (cuanto haya contacto)</span>
                    </div>
                    <div class="ai-action-plan__footer">
                        <strong>Próximo paso:</strong> Esperar detección automática de teléfono/web para iniciar contacto directo.
                    </div>
                </div>
            </div>
        `;
    }
</script>
