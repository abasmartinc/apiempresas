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
                
                cancelForms.forEach(form => {
                    form.addEventListener('submit', function (e) {
                        e.preventDefault();

                        const planNameAttr = form.querySelector('input[name="plan_name"]')?.value || '<?= esc($planName) ?>';

                        if (typeof Swal === 'undefined') {
                            if (confirm('¿Estás seguro de que deseas cancelar tu suscripción a ' + planNameAttr + '?')) {
                                form.submit();
                            }
                            return;
                        }

                        Swal.fire({
                            title: '¿Cancelar suscripción?',
                            html: 'Lamentamos que te vayas. Seguirás teniendo acceso a las funciones de <strong>' + planNameAttr + '</strong> hasta el final de tu periodo de facturación actual.',
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, cancelar suscripción',
                            cancelButtonText: 'Mantener plan',
                            reverseButtons: true,
                            focusCancel: true,
                            customClass: {
                                confirmButton: 'btn danger',
                                cancelButton: 'btn btn_light',
                            }
                        }).then((result) => {
                            if (result.isConfirmed) {
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
