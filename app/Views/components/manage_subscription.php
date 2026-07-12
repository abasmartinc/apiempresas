<?php if ($plan && $planName !== 'Free'): ?>
    <!-- GESTIÓN DE SUSCRIPCIÓN ACTIVA -->
    <div style="margin-top: 0px; margin-bottom: 40px; position: relative; z-index: 10;">

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 24px; padding: 40px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02), 0 4px 6px -4px rgba(0,0,0,0.02);">
            
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 32px; border-bottom: 1px solid #f1f5f9; padding-bottom: 24px;">
                <div>
                    <div style="font-size: 0.8rem; font-weight: 800; text-transform: uppercase; color: #64748b; letter-spacing: 0.05em; margin-bottom: 6px;">Gestión de Facturación</div>
                    <h2 style="font-size: 1.5rem; font-weight: 900; color: #0f172a; margin: 0;">Estado de tu suscripción</h2>
                </div>
                <?php 
                    $status = $get($plan, 'status', 'active');
                    $isCanceled = ($status === 'canceled');
                ?>
                <div style="padding: 6px 16px; border-radius: 999px; font-size: 0.85rem; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; <?= $isCanceled ? 'background: #fef2f2; color: #b91c1c; border: 1px solid #fecaca;' : 'background: #ecfdf5; color: #059669; border: 1px solid #a7f3d0;' ?>">
                    <span style="width: 8px; height: 8px; border-radius: 50%; <?= $isCanceled ? 'background: #ef4444;' : 'background: #10b981;' ?>"></span>
                    <?= $isCanceled ? 'Cancelada' : 'Activa' ?>
                </div>
            </div>

            <!-- Datos principales -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px; margin-bottom: 32px;">
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 4px;">Plan contratado</div>
                    <div style="font-size: 1.25rem; font-weight: 900; color: #0f172a;"><?= esc($planName) ?></div>
                </div>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 4px;">Próximo cobro</div>
                    <div style="font-size: 1.25rem; font-weight: 900; color: #0f172a;"><?= $periodEnd ? esc(date('d/m/Y', strtotime((string) $periodEnd))) : '—' ?></div>
                </div>
                <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 20px;">
                    <div style="font-size: 0.85rem; font-weight: 700; color: #64748b; margin-bottom: 4px;">Método de pago</div>
                    <div style="font-size: 1.25rem; font-weight: 900; color: #0f172a; display: flex; align-items: center; gap: 8px;">
                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2152ff" stroke-width="2"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                        Stripe
                    </div>
                </div>
            </div>

            <!-- Tabla de planes limpia -->
            <div style="margin-bottom: 32px;">
                <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 16px;">Histórico de Planes</h3>
                <div style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden;">
                    <table style="width: 100%; border-collapse: collapse; font-size: 0.95rem;">
                        <thead style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <tr style="text-align: left; color: #64748b; font-weight: 700;">
                                <th style="padding: 16px 20px;">Plan</th>
                                <th style="padding: 16px 20px;">Estado</th>
                                <th style="padding: 16px 20px;">Fin Periodo</th>
                                <th style="padding: 16px 20px;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($all_subscriptions ?? [] as $sub): ?>
                                <tr style="border-bottom: 1px solid #f1f5f9; background: #ffffff;">
                                    <td style="padding: 16px 20px; font-weight: 800; color: #0f172a;"><?= esc($sub->plan_name) ?></td>
                                    <td style="padding: 16px 20px;">
                                        <span style="display: inline-block; padding: 4px 10px; border-radius: 99px; font-size: 0.8rem; font-weight: 800; background: <?= ($sub->status === 'canceled') ? '#fef2f2; color: #b91c1c;' : '#ecfdf5; color: #059669;' ?>">
                                            <?= ($sub->status === 'canceled') ? 'Cancelada' : 'Activa' ?>
                                        </span>
                                    </td>
                                    <td style="padding: 16px 20px; color: #64748b; font-weight: 500;"><?= date('d/m/Y', strtotime((string)$sub->current_period_end)) ?></td>
                                    <td style="padding: 16px 20px;">
                                        <?php if ($sub->status === 'active'): ?>
                                            <form class="form-cancel-sub-item" action="<?= site_url('billing/cancel-subscription') ?>" method="POST" style="margin: 0;">
                                                <?= csrf_field() ?>
                                                <input type="hidden" name="sub_id" value="<?= $sub->id ?>">
                                                <input type="hidden" name="plan_name" value="<?= esc($sub->plan_name) ?>">
                                                <button type="submit" style="background: #fef2f2; color: #dc2626; border: 1px solid #fecaca; padding: 6px 12px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; cursor: pointer; transition: all 0.2s;">Cancelar plan</button>
                                            </form>
                                        <?php else: ?>
                                            <span style="color: #cbd5e1;">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Botonera -->
            <div style="display: flex; flex-wrap: wrap; gap: 16px; align-items: center; border-top: 1px solid #e2e8f0; padding-top: 32px;">
                <?php if (!empty($stripe_customer_id)): ?>
                    <a href="<?= site_url('billing/portal') ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #0f172a; color: white; padding: 14px 24px; border-radius: 12px; font-weight: 800; text-decoration: none; font-size: 0.95rem; transition: background 0.2s;">
                        <svg style="width:18px; height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        Gestionar Facturación
                    </a>
                <?php endif; ?>

                <a href="<?= site_url('billing/invoices') ?>" style="display: inline-flex; align-items: center; background: #ffffff; border: 1px solid #cbd5e1; color: #334155; padding: 14px 24px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 0.95rem; transition: all 0.2s;">Ver mis facturas</a>

                <?php if (!$isCanceled): ?>
                    <form id="formCancelSubscription" action="<?= site_url('billing/cancel-subscription') ?>" method="POST" style="margin: 0; margin-left: auto;">
                        <?= csrf_field() ?>
                        <button type="submit" style="background: transparent; color: #dc2626; border: none; font-weight: 700; cursor: pointer; font-size: 0.95rem; text-decoration: underline;">Cancelar suscripción</button>
                    </form>
                <?php else: ?>
                    <div style="margin-left: auto; font-size: 0.9rem; color: #64748b; font-weight: 600;">
                        Finalizará el <?= esc(date('d/m/Y', strtotime((string) $periodEnd))) ?>.
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($planName === 'Pro'): ?>
                <div style="margin-top: 32px; background: linear-gradient(135deg, #2152ff 0%, #1e40af 100%); border-radius: 16px; padding: 24px; color: white; display: flex; align-items: center; justify-content: space-between; gap: 24px; box-shadow: 0 10px 15px -3px rgba(33, 82, 255, 0.3);">
                    <div>
                        <div style="font-size: 1.2rem; font-weight: 900; margin-bottom: 4px;">¿Necesitas más potencia? 🚀</div>
                        <div style="font-size: 0.95rem; color: #bfdbfe; font-weight: 500;">Pasa al plan Business para obtener SLA avanzado, gestión de equipos y límites superiores.</div>
                    </div>
                    <button style="background: white; color: #1e40af; border: none; padding: 12px 24px; border-radius: 99px; font-weight: 800; font-size: 0.95rem; cursor: pointer; flex-shrink: 0;" onclick="document.getElementById('plan_business').click(); window.scrollTo({top:0, behavior:'smooth'});">Mejorar a Business</button>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <script>
        (function() {
            function initCancelForm() {
                const cancelForms = document.querySelectorAll('#formCancelSubscription, .form-cancel-sub-item');
                const cancellationReasonOptions = [
                    {
                        value: 'too_expensive',
                        label: 'Precio',
                        detail: 'No encaja con mi uso actual',
                        prompt: 'Que precio, volumen o condiciones harian que tuviera sentido para ti?'
                    },
                    {
                        value: 'missing_features',
                        label: 'Funcionalidades',
                        detail: 'Echo en falta algo importante',
                        prompt: 'Que funcionalidad concreta echaste en falta o esperabas encontrar?'
                    },
                    {
                        value: 'low_usage',
                        label: 'Poco uso',
                        detail: 'No lo estoy aprovechando',
                        prompt: 'Que te impidio integrarlo en tu trabajo habitual?'
                    },
                    {
                        value: 'technical_issues',
                        label: 'Problemas tecnicos',
                        detail: 'Datos, rendimiento o integracion',
                        prompt: 'Que problema encontraste y donde ocurrio?'
                    },
                    {
                        value: 'switched_solution',
                        label: 'Otra solucion',
                        detail: 'He elegido otra herramienta',
                        prompt: 'Que alternativa estas usando y que te convencio de ella?'
                    },
                    {
                        value: 'temporary_pause',
                        label: 'Pausa temporal',
                        detail: 'Volvere mas adelante',
                        prompt: 'Cuando tendria sentido que volvieramos a contactarte?'
                    },
                    {
                        value: 'other',
                        label: 'Otro motivo',
                        detail: 'Quiero explicarlo con mis palabras',
                        prompt: 'Cuentanos brevemente que ha motivado la cancelacion.'
                    },
                    {
                        value: 'prefer_not_to_say',
                        label: 'Prefiero no responder',
                        detail: 'Continuar sin dar motivo',
                        prompt: ''
                    }
                ];

                function escapeHtml(value) {
                    const div = document.createElement('div');
                    div.textContent = value || '';
                    return div.innerHTML;
                }

                function setCancellationFeedback(form, reason, feedback) {
                    let reasonInput = form.querySelector('input[name="cancellation_reason"]');
                    let feedbackInput = form.querySelector('input[name="cancellation_feedback"]');

                    if (!reasonInput) {
                        reasonInput = document.createElement('input');
                        reasonInput.type = 'hidden';
                        reasonInput.name = 'cancellation_reason';
                        form.appendChild(reasonInput);
                    }

                    if (!feedbackInput) {
                        feedbackInput = document.createElement('input');
                        feedbackInput.type = 'hidden';
                        feedbackInput.name = 'cancellation_feedback';
                        form.appendChild(feedbackInput);
                    }

                    reasonInput.value = reason || 'prefer_not_to_say';
                    feedbackInput.value = feedback || '';
                }

                cancelForms.forEach(form => {
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();

                        const planNameAttr = form.querySelector('input[name="plan_name"]')?.value || <?= json_encode((string) $planName) ?>;

                        if (typeof Swal === 'undefined') {
                            if (confirm('¿Estás seguro de que deseas cancelar tu suscripción a ' + planNameAttr + '?')) {
                                const fallbackFeedback = prompt('Nos ayudas a mejorar: cual es el motivo principal de la cancelacion?') || '';
                                setCancellationFeedback(form, 'other', fallbackFeedback);
                                form.submit();
                            }
                            return;
                        }

                        Swal.fire({
                            width: 720,
                            padding: 0,
                            buttonsStyling: false,
                            title: '¿Cancelar suscripción?',
                            html: `
                                <style>
                                    .cancel-sub-modal { font-family: inherit; color: #0f172a; text-align: left; }
                                    .cancel-sub-modal__hero { position: relative; overflow: hidden; padding: 28px 32px 24px; background: linear-gradient(135deg, #0f172a 0%, #1d4ed8 56%, #14b8a6 100%); color: #ffffff; }
                                    .cancel-sub-modal__hero::after { content: ""; position: absolute; inset: auto -70px -120px auto; width: 260px; height: 260px; border-radius: 999px; background: rgba(255,255,255,0.16); }
                                    .cancel-sub-modal__badge { position: relative; z-index: 1; display: inline-flex; align-items: center; gap: 8px; padding: 7px 11px; border: 1px solid rgba(255,255,255,0.28); border-radius: 999px; background: rgba(255,255,255,0.12); font-size: 0.78rem; font-weight: 800; }
                                    .cancel-sub-modal__badge-dot { width: 7px; height: 7px; border-radius: 999px; background: #67e8f9; box-shadow: 0 0 0 5px rgba(103,232,249,0.18); }
                                    .cancel-sub-modal__title { position: relative; z-index: 1; margin: 18px 0 8px; font-size: 1.75rem; line-height: 1.15; font-weight: 900; letter-spacing: 0; }
                                    .cancel-sub-modal__copy { position: relative; z-index: 1; margin: 0; max-width: 580px; color: rgba(255,255,255,0.84); font-size: 0.96rem; line-height: 1.55; }
                                    .cancel-sub-modal__body { padding: 24px 32px 8px; background: #ffffff; }
                                    .cancel-sub-modal__section-label { display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 12px; color: #0f172a; font-size: 0.9rem; font-weight: 900; }
                                    .cancel-sub-modal__hint { color: #64748b; font-size: 0.8rem; font-weight: 700; }
                                    .cancel-sub-modal__reasons { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
                                    .cancel-sub-modal__reason { position: relative; display: flex; gap: 12px; min-height: 74px; padding: 13px; border: 1px solid #e2e8f0; border-radius: 12px; background: #ffffff; cursor: pointer; transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease, background .18s ease; text-align: left; }
                                    .cancel-sub-modal__reason:hover { border-color: #93c5fd; background: #f8fbff; transform: translateY(-1px); box-shadow: 0 12px 24px rgba(15,23,42,0.08); }
                                    .cancel-sub-modal__reason.is-selected { border-color: #2563eb; background: #eff6ff; box-shadow: 0 14px 28px rgba(37,99,235,0.16); }
                                    .cancel-sub-modal__radio { width: 18px; height: 18px; margin-top: 2px; border: 2px solid #cbd5e1; border-radius: 999px; flex: 0 0 auto; background: #fff; }
                                    .cancel-sub-modal__reason.is-selected .cancel-sub-modal__radio { border: 5px solid #2563eb; }
                                    .cancel-sub-modal__reason-title { display: block; margin: 0 0 3px; color: #0f172a; font-size: 0.92rem; font-weight: 900; }
                                    .cancel-sub-modal__reason-detail { display: block; margin: 0; color: #64748b; font-size: 0.8rem; line-height: 1.35; }
                                    .cancel-sub-modal__feedback { margin-top: 16px; padding: 16px; border: 1px solid #e2e8f0; border-radius: 14px; background: #f8fafc; }
                                    .cancel-sub-modal__feedback-label { display: block; margin-bottom: 8px; color: #0f172a; font-size: 0.86rem; font-weight: 900; }
                                    .cancel-sub-modal__feedback textarea { display: block; width: 100%; min-height: 96px; margin: 0; padding: 12px 13px; box-sizing: border-box; border: 1px solid #cbd5e1; border-radius: 10px; background: #ffffff; color: #0f172a; font: inherit; font-size: 0.9rem; resize: vertical; outline: none; }
                                    .cancel-sub-modal__feedback textarea:focus { border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,0.12); }
                                    .cancel-sub-modal__feedback-note { margin: 8px 0 0; color: #64748b; font-size: 0.78rem; line-height: 1.35; }
                                    .cancel-sub-modal__feedback.is-muted { opacity: .72; }
                                    .cancel-sub-modal__footer-note { padding: 12px 32px 22px; background: #ffffff; color: #64748b; font-size: 0.78rem; text-align: left; }
                                    .cancel-sub-popup { border-radius: 16px; overflow: hidden; }
                                    .cancel-sub-popup .swal2-html-container { margin: 0; padding: 0; overflow: visible; }
                                    .cancel-sub-popup .swal2-title, .cancel-sub-popup .swal2-icon { display: none !important; }
                                    .cancel-sub-popup .swal2-actions { width: 100%; justify-content: flex-end; gap: 10px; margin: 0; padding: 18px 32px 28px; background: #ffffff; border-top: 1px solid #edf2f7; box-sizing: border-box; }
                                    .cancel-sub-popup .swal2-confirm, .cancel-sub-popup .swal2-cancel { border: 0; border-radius: 10px; padding: 12px 18px; font-weight: 900; cursor: pointer; transition: transform .18s ease, box-shadow .18s ease, background .18s ease; }
                                    .cancel-sub-popup .swal2-confirm { background: #dc2626; color: #ffffff; box-shadow: 0 12px 24px rgba(220,38,38,0.22); }
                                    .cancel-sub-popup .swal2-confirm:hover { background: #b91c1c; transform: translateY(-1px); box-shadow: 0 16px 30px rgba(220,38,38,0.28); }
                                    .cancel-sub-popup .swal2-cancel { background: #0f172a; color: #ffffff; box-shadow: 0 12px 24px rgba(15,23,42,0.16); }
                                    .cancel-sub-popup .swal2-cancel:hover { background: #1e293b; transform: translateY(-1px); }
                                    .cancel-sub-popup .swal2-validation-message { margin: 0 32px 14px; border-radius: 10px; font-weight: 800; }
                                    @media (max-width: 640px) {
                                        .cancel-sub-modal__hero, .cancel-sub-modal__body, .cancel-sub-modal__footer-note { padding-left: 20px; padding-right: 20px; }
                                        .cancel-sub-modal__reasons { grid-template-columns: 1fr; }
                                        .cancel-sub-popup .swal2-actions { padding-left: 20px; padding-right: 20px; flex-direction: column-reverse; }
                                        .cancel-sub-popup .swal2-confirm, .cancel-sub-popup .swal2-cancel { width: 100%; }
                                    }
                                </style>
                                <div class="cancel-sub-modal">
                                    <div class="cancel-sub-modal__hero">
                                        <span class="cancel-sub-modal__badge"><span class="cancel-sub-modal__badge-dot"></span> Gestion de suscripcion</span>
                                        <h2 class="cancel-sub-modal__title">Antes de cancelar, ayudanos a mejorar</h2>
                                        <p class="cancel-sub-modal__copy">Tu acceso a <strong>${escapeHtml(planNameAttr)}</strong> seguira activo hasta el final del periodo facturado. Solo necesitamos entender que ha fallado para mejorar el producto.</p>
                                    </div>
                                    <div class="cancel-sub-modal__body">
                                        <div class="cancel-sub-modal__section-label">
                                            <span>Motivo principal</span>
                                            <span class="cancel-sub-modal__hint">1 seleccion</span>
                                        </div>
                                        <div class="cancel-sub-modal__reasons" id="cancelReasonCards">
                                            ${cancellationReasonOptions.map(option => `
                                                <button type="button" class="cancel-sub-modal__reason" data-reason="${option.value}" data-prompt="${escapeHtml(option.prompt)}">
                                                    <span class="cancel-sub-modal__radio"></span>
                                                    <span>
                                                        <span class="cancel-sub-modal__reason-title">${option.label}</span>
                                                        <span class="cancel-sub-modal__reason-detail">${option.detail}</span>
                                                    </span>
                                                </button>
                                            `).join('')}
                                        </div>
                                        <input type="hidden" id="cancelReason" value="">
                                        <div class="cancel-sub-modal__feedback" id="cancelFeedbackPanel">
                                            <label class="cancel-sub-modal__feedback-label" for="cancelFeedback" id="cancelFeedbackLabel">Elige un motivo y te haremos una pregunta concreta.</label>
                                            <textarea id="cancelFeedback" maxlength="1000" placeholder="Tu comentario nos ayuda a priorizar mejoras reales."></textarea>
                                            <p class="cancel-sub-modal__feedback-note">Opcional, pero muy util. Maximo 1000 caracteres.</p>
                                        </div>
                                    </div>
                                    <div class="cancel-sub-modal__footer-note">No perderas acceso hoy: la cancelacion evita futuros cobros.</div>
                                </div>
                            `,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Cancelar suscripcion',
                            cancelButtonText: 'Mantener plan',
                            reverseButtons: true,
                            focusCancel: true,
                            preConfirm: () => {
                                const reason = document.getElementById('cancelReason')?.value || '';
                                const feedback = document.getElementById('cancelFeedback')?.value || '';

                                if (!reason) {
                                    Swal.showValidationMessage('Selecciona un motivo o elige "Prefiero no responder".');
                                    return false;
                                }

                                return { reason, feedback };
                            },
                            didOpen: () => {
                                const reasonInput = document.getElementById('cancelReason');
                                const feedbackLabel = document.getElementById('cancelFeedbackLabel');
                                const feedbackPanel = document.getElementById('cancelFeedbackPanel');
                                const feedbackTextarea = document.getElementById('cancelFeedback');
                                const cards = Array.from(document.querySelectorAll('.cancel-sub-modal__reason'));

                                cards.forEach(card => {
                                    card.addEventListener('click', () => {
                                        cards.forEach(item => item.classList.remove('is-selected'));
                                        card.classList.add('is-selected');
                                        reasonInput.value = card.dataset.reason || '';

                                        const prompt = card.dataset.prompt || '';
                                        feedbackLabel.textContent = prompt || 'Puedes continuar sin comentario.';
                                        feedbackTextarea.placeholder = prompt || 'Sin comentario adicional.';
                                        feedbackTextarea.disabled = !prompt;
                                        feedbackPanel.classList.toggle('is-muted', !prompt);

                                        if (!prompt) {
                                            feedbackTextarea.value = '';
                                        } else {
                                            feedbackTextarea.focus();
                                        }
                                    });
                                });
                            },
                            customClass: {
                                popup: 'cancel-sub-popup',
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
                                setCancellationFeedback(form, result.value?.reason, result.value?.feedback);
                                form.submit();
                            }
                        });
                    });
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initCancelForm);
            } else {
                initCancelForm();
            }
        })();
    </script>
<?php endif; ?>
