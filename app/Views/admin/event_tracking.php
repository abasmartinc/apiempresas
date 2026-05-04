<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head', ['title' => $title]) ?>
    <style>
        :root {
            --kpi-blue: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            --kpi-green: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --kpi-orange: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --kpi-purple: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
        }

        .page-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
        .kpi-card { 
            position: relative;
            overflow: hidden;
            background: white; 
            border-radius: 24px; 
            padding: 2rem; 
            border: 1px solid rgba(255, 255, 255, 0.7); 
            display: flex; 
            flex-direction: column; 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); 
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05); 
        }
        .kpi-card:hover { transform: translateY(-5px); box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1); }
        
        .kpi-icon-wrapper {
            width: 48px; height: 48px;
            border-radius: 14px;
            background: var(--kpi-color);
            display: flex; align-items: center; justify-content: center;
            margin-bottom: 1.5rem;
            color: white;
            box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.1);
        }
        .kpi-label { font-size: 0.85rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
        .kpi-value { font-size: 2.2rem; font-weight: 900; color: #1e293b; letter-spacing: -0.02em; margin-bottom: 0.5rem; line-height: 1; }
        .kpi-sub { font-size: 0.8rem; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 6px; }

        .progress-bar-container { width: 100%; height: 6px; background: #f1f5f9; border-radius: 100px; margin-top: 1rem; overflow: hidden; }
        .progress-bar-fill { height: 100%; background: var(--kpi-color); border-radius: 100px; transition: width 1s ease-out; }

        .admin-table-wrapper { background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px -5px rgba(0, 0, 0, 0.05); overflow: hidden; margin-bottom: 3rem; }
        
        .status-badge { padding: 4px 10px; border-radius: 8px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
        .status-critico { background: #fef2f2; color: #991b1b; border: 1px solid #fee2e2; }
        .status-alto { background: #fffbeb; color: #92400e; border: 1px solid #fef3c7; }
        .status-medio { background: #f0f9ff; color: #075985; border: 1px solid #e0f2fe; }
        .status-bajo { background: #f8fafc; color: #64748b; border: 1px solid #f1f5f9; }

        .why-tag { font-size: 0.8rem; color: #475569; font-weight: 500; display: block; margin-top: 4px; }
        .btn-action-sm { padding: 6px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; background: #6366f1; color: white; border: none; cursor: pointer; text-decoration: none; display: inline-block; }
        .btn-action-sm:hover { opacity: 0.9; transform: scale(1.02); }

        .momentum-up { color: #10b981; font-weight: 800; font-size: 0.75rem; }

        /* Modal Overlay Minimal */
        #contactModal {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            display: none;
            align-items: center;
            justify-content: center;
        }
        .modal-box {
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 500px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
    </style>
</head>

<body class="admin-body">
    <div class="bg-halo" aria-hidden="true"></div>
    <?= view('partials/header_admin') ?>

    <main class="container-admin page-padding" style="margin-top: 2rem;">
        <div class="page-header">
            <div>
                <h1 class="title" style="color: #0f172a;">Conversión & Leads API</h1>
                <p style="color: #64748b;">Seguimiento operativo de activación y monetización</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <div style="background: white; padding: 10px 20px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 10px;">
                    <span style="width: 10px; height: 10px; background: #10b981; border-radius: 50%; box-shadow: 0 0 8px rgba(16, 185, 129, 0.4);"></span>
                    <span style="font-weight: 700; font-size: 0.9rem;"><?= $activeUsers ?> usuarios activos</span>
                </div>
                <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver</a>
            </div>
        </div>

        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card" style="--kpi-color: var(--kpi-blue);">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                </div>
                <span class="kpi-label">Total Usuarios</span>
                <span class="kpi-value"><?= number_format($kpis['total_registros']) ?></span>
                <span class="kpi-sub">Usuarios registrados</span>
            </div>

            <div class="kpi-card" style="--kpi-color: var(--kpi-purple);">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 12h-4l-3 9L9 3l-3 9H2"></path></svg>
                </div>
                <span class="kpi-label">Uso API (Hoy)</span>
                <span class="kpi-value"><?= number_format($kpis['total_requests_today'] ?? 0) ?></span>
                <span class="kpi-sub">Peticiones totales hoy</span>
            </div>

            <div class="kpi-card" style="--kpi-color: var(--kpi-green);">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20M17 5H9.5a4.5 4.5 0 1 0 0 9h5a4.5 4.5 0 1 1 0 9H6"></path></svg>
                </div>
                <span class="kpi-label">Tasa Conversión</span>
                <span class="kpi-value"><?= $kpis['conversion_rate'] ?>%</span>
                <span class="kpi-sub"><?= $kpis['paid_users'] ?> usuarios de pago</span>
                <div class="progress-bar-container">
                    <div class="progress-bar-fill" style="width: <?= $kpis['conversion_rate'] ?>%;"></div>
                </div>
            </div>

            <div class="kpi-card" style="--kpi-color: var(--kpi-orange);">
                <div class="kpi-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                </div>
                <span class="kpi-label">Listos para Convertir</span>
                <span class="kpi-value"><?= $summary['ready'] ?></span>
                <span class="kpi-sub">Urgencia Crítica/Alta</span>
            </div>
        </div>

        <div class="admin-table-wrapper" id="leads-table">
            <div style="padding: 2rem; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h3 style="margin: 0; font-size: 1.25rem; color: #1e293b; font-weight: 800;">Leads Prioritarios & Tareas</h3>
                    <p style="margin: 4px 0 0; font-size: 0.85rem; color: #64748b;">Ranking dinámico por Urgencia e Intención</p>
                </div>
                <div style="display: flex; gap: 8px; align-items: center;">
                    <button id="btnBulkEmail" class="btn-action-sm" style="display:none; background: #10b981; border:none; padding: 8px 16px;" onclick="openBulkContactModal()">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 5px; vertical-align: middle;"><path d="m22 2-7 20-4-9-9-4Z"/><path d="M22 2 11 13"/></svg>
                        Enviar a <span id="selectedCount">0</span> seleccionados
                    </button>
                    <span class="status-badge status-critico" style="padding: 6px 12px;">CRÍTICO: <?= $summary['ready'] ?></span>
                </div>
            </div>
            <div class="table-responsive">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th width="40" style="text-align: center;"><input type="checkbox" id="selectAll" style="width: 18px; height: 18px; cursor: pointer;"></th>
                            <th width="60">#</th>
                            <th>Usuario</th>
                            <th>Motivo (Estratégico)</th>
                            <th style="text-align: center;">Uso API</th>
                            <th style="text-align: center;">Urgencia</th>
                            <th style="text-align: right;">Acción Recomendada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($userList as $idx => $u): 
                            $statusClass = strtolower(str_replace(' ', '-', $u['status_label']));
                            $reqToday = (int)($u['req_24h'] ?? 0);
                            $icon = '❄️';
                            if ($reqToday >= 5) $icon = '🔥';
                            elseif ($reqToday >= 3) $icon = '⚡';
                            elseif ($reqToday >= 1) $icon = '⚪';
                        ?>
                        <tr>
                            <td style="text-align: center;">
                                <input type="checkbox" class="user-checkbox" value="<?= $u['id'] ?>" data-email="<?= esc($u['email']) ?>" data-case="<?= $u['case_type'] ?>" style="width: 18px; height: 18px; cursor: pointer;">
                            </td>
                            <td style="font-weight: 900; color: #cbd5e1; font-size: 1.2rem;">
                                <?= $icon ?>
                                #<?= $idx + 1 ?>
                            </td>
                            <td>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="color: #0f172a; font-weight: 700;"><?= esc($u['email']) ?></span>
                                    <span style="font-size: 0.75rem; color: #94a3b8;"><?= $u['plan_name'] ?: 'Plan Free' ?></span>
                                </div>
                            </td>
                            <td>
                                <span class="why-tag" style="<?= $u['urgency_score'] >= 75 ? 'color: #ef4444; font-weight: 700;' : 'color: #334155; font-weight: 600;' ?>">
                                    <?= $u['why'] ?>
                                </span>
                            </td>
                            <td style="text-align: center;">
                                <div style="display: flex; flex-direction: column; align-items: center;">
                                    <span style="font-weight: 800; font-size: 1rem; color: #1e293b;">Total: <?= $u['total_requests'] ?></span>
                                    <span style="font-size: 0.75rem; font-weight: 700; color: #6366f1;">Hoy: <?= $u['req_24h'] ?></span>
                                </div>
                            </td>
                            <td style="text-align: center;">
                                <span class="status-badge status-<?= $statusClass ?>">
                                    <?= $u['status_label'] ?>
                                </span>
                            </td>
                            <td style="text-align: right;">
                                <button class="btn-action-sm" onclick="openContactModal('<?= $u['id'] ?>', '<?= esc($u['email']) ?>', '<?= esc($u['why']) ?>', '<?= $u['case_type'] ?>')">
                                    <?= $u['recommended_action'] ?>
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="admin-table-wrapper">
            <div style="padding: 1.5rem; border-bottom: 1px solid #f1f5f9;">
                <h3 style="margin: 0; font-size: 1rem; color: #1e293b; font-weight: 800;">🛠️ Alertas Técnicas / Bloqueos Onboarding</h3>
            </div>
            <table class="admin-table">
                <tbody>
                    <?php foreach($problematicUsers as $u): ?>
                    <tr>
                        <td style="text-align: center; width: 40px;">
                            <input type="checkbox" class="user-checkbox" value="<?= $u['id'] ?>" data-email="<?= esc($u['email']) ?>" data-case="onboarding" style="width: 18px; height: 18px; cursor: pointer;">
                        </td>
                        <td>
                            <div style="font-weight: 600; font-size: 0.85rem;"><?= esc($u['email']) ?></div>
                            <div style="font-size: 0.7rem; color: #94a3b8;">ID #<?= $u['id'] ?></div>
                        </td>
                        <td style="font-size: 0.8rem; color: #475569; font-weight: 600;"><?= $u['why'] ?></td>
                        <td style="text-align: right;">
                            <button onclick="openContactModal('<?= $u['id'] ?>', '<?= esc($u['email']) ?>', '<?= esc($u['why']) ?>', 'onboarding')" style="background: none; border: none; color: #6366f1; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; cursor: pointer;">Soporte</button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>

    <!-- Modal Requerido por el Usuario -->
    <div id="contactModal">
        <div class="modal-box">
            <h3 style="margin-top:0;">Contactar usuario</h3>
            <p><strong>Email:</strong> <span id="modalUserEmail"></span></p>
            <p><strong>Motivo:</strong> <span id="modalReason"></span></p>

            <input type="text" id="modalSubject" placeholder="Asunto del correo" style="width:100%; padding:10px; border-radius:10px; border:1px solid #e2e8f0; margin-top:10px;" value="Novedades sobre tu acceso a la API">
            <textarea id="modalMessage" style="width:100%; height:150px; padding:10px; border-radius:10px; border:1px solid #e2e8f0; margin-top:10px;"></textarea>

            <br><br>
            <div style="display:flex; gap:10px; justify-content:flex-end;">
                <button onclick="closeModal()" style="padding:10px 20px; border-radius:8px; border:none; background:#f1f5f9; cursor:pointer;">Cancelar</button>
                <button onclick="sendMessage()" id="btnSend" style="padding:10px 20px; border-radius:8px; border:none; background:#6366f1; color:white; cursor:pointer;">Enviar Mensaje</button>
            </div>
        </div>
    </div>

    <?= view('partials/footer') ?>
    <script>
        document.getElementById('selectAll').addEventListener('change', function() {
            const checkboxes = document.querySelectorAll('.user-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
            updateBulkButton();
        });

        document.querySelectorAll('.user-checkbox').forEach(cb => {
            cb.addEventListener('change', updateBulkButton);
        });

        function updateBulkButton() {
            const selected = document.querySelectorAll('.user-checkbox:checked');
            const btn = document.getElementById('btnBulkEmail');
            const countSpan = document.getElementById('selectedCount');
            
            if (selected.length > 0) {
                btn.style.display = 'inline-block';
                countSpan.innerText = selected.length;
            } else {
                btn.style.display = 'none';
            }
        }

        function openContactModal(userId, email, reason, caseType) {
            const modal = document.getElementById('contactModal');
            document.getElementById('modalUserEmail').innerText = email;
            document.getElementById('modalReason').innerText = reason;

            const message = generateMessage(caseType);
            document.getElementById('modalMessage').value = message;

            modal.dataset.userIds = JSON.stringify([userId]);
            modal.style.display = 'flex';
        }

        function openBulkContactModal() {
            const selected = document.querySelectorAll('.user-checkbox:checked');
            const userIds = Array.from(selected).map(cb => cb.value);
            const emails = Array.from(selected).map(cb => cb.dataset.email);
            
            const modal = document.getElementById('contactModal');
            document.getElementById('modalUserEmail').innerText = emails.length > 3 
                ? `${emails.slice(0, 3).join(', ')} y ${emails.length - 3} más`
                : emails.join(', ');
            document.getElementById('modalReason').innerText = 'Envío masivo a usuarios seleccionados';

            // Detectar el tipo de caso predominante o usar general
            const caseTypes = Array.from(selected).map(cb => cb.dataset.case);
            const mostFrequentCase = getMostFrequent(caseTypes) || 'general_followup';

            const message = generateMessage(mostFrequentCase);
            document.getElementById('modalMessage').value = message;

            modal.dataset.userIds = JSON.stringify(userIds);
            modal.style.display = 'flex';
        }

        function getMostFrequent(arr) {
            const hashmap = arr.reduce((acc, val) => {
                acc[val] = (acc[val] || 0) + 1;
                return acc;
            }, {});
            return Object.keys(hashmap).reduce((a, b) => hashmap[a] > hashmap[b] ? a : b);
        }

        function closeModal() {
            document.getElementById('contactModal').style.display = 'none';
        }

        function generateMessage(type) {
            switch(type) {
                case 'active_high':
                    return `Hola,\n\nhe visto que hoy estás usando bastante la API 👀\n¿todo bien por ahí?\n\nSi estás probando algo en producción o necesitas más volumen, dime y te ayudo.`;
                case 'pricing_missing':
                    return `Hola,\n\nveo que ya estás usando la API bastante 👍\n\nSi te interesa escalar tu integración o necesitas un plan con más volumen, dime y te ayudo a elegir el que mejor se adapte.`;
                case 'reactivation':
                    return `Hola,\n\nhace unos días estuviste usando la API bastante.\n¿sigues con ese proyecto?\n\nSi lo retomaste, te ayudo.`;
                case 'onboarding':
                    return `Hola,\n\nvi que generaste tu API key pero no hiciste llamadas.\n\nSi quieres, te paso un ejemplo listo para probar.`;
                default:
                    return "Hola, ¿en qué puedo ayudarte?";
            }
        }

        function sendMessage() {
            const modal = document.getElementById('contactModal');
            const userIds = JSON.parse(modal.dataset.userIds);
            const message = document.getElementById('modalMessage').value;
            const subject = document.getElementById('modalSubject').value;
            const btn = document.getElementById('btnSend');

            btn.disabled = true;
            btn.innerText = 'Enviando...';

            fetch('<?= site_url('admin/send-message') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    '<?= csrf_header() ?>': '<?= csrf_hash() ?>'
                },
                body: JSON.stringify({ user_ids: userIds, message: message, subject: subject })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    closeModal();
                }
                
                Swal.fire({
                    icon: data.status === 'success' ? 'success' : 'error',
                    title: data.status === 'success' ? '¡Hecho!' : 'Error',
                    text: data.message,
                    confirmButtonColor: '#6366f1'
                });

                if (data.status === 'success') {
                    setTimeout(() => location.reload(), 2000);
                }
            })
            .catch(err => {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de red',
                    text: 'No se pudo conectar con el servidor',
                    confirmButtonColor: '#6366f1'
                });
            })
            .finally(() => {
                btn.disabled = false;
                btn.innerText = 'Enviar Mensaje';
            });
        }

        // Close on Escape
        window.onkeydown = function(event) {
            if (event.key === 'Escape') closeModal();
        }
    </script>
</body>

</html>
