<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<!-- Navegación y Header idénticos al resto del Admin -->
<?= view('partials/header_admin') ?>

<main class="container-admin" style="padding: 40px 0;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 class="title" style="display: flex; align-items: center; gap: 10px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width:28px; color:#10b981;"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z"></path></svg>
                IA Marketing: Lead Scoring
            </h1>
            <p style="color: #64748b; margin-top: 5px; font-size: 0.95rem;">Análisis inteligente de actividad para identificar usuarios gratuitos con alta probabilidad de suscripción.</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="grid" style="grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid #3b82f6;">
            <div style="background: #eff6ff; padding: 12px; border-radius: 12px; color: #3b82f6;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 24px; height: 24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" /></svg>
            </div>
            <div>
                <p style="color: #64748b; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">Leads Analizados</p>
                <p style="font-size: 1.5rem; font-weight: 700; color: #0f172a;"><?= number_format($stats['total_leads']) ?></p>
            </div>
        </div>

        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid #f59e0b;">
            <div style="background: #fef3c7; padding: 12px; border-radius: 12px; color: #d97706;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 24px; height: 24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M15.362 5.214A8.252 8.252 0 0 1 12 21 8.25 8.25 0 0 1 6.038 7.047 8.287 8.287 0 0 0 9 9.601a8.983 8.983 0 0 1 3.361-6.866 8.21 8.21 0 0 0 3 2.48Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 18a3.75 3.75 0 0 0 .495-7.468 5.99 5.99 0 0 0-1.925 3.547 5.975 5.975 0 0 1-2.133-1.001A3.75 3.75 0 0 0 12 18Z" /></svg>
            </div>
            <div>
                <p style="color: #64748b; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">Hot Leads (Sin Subscripción)</p>
                <p style="font-size: 1.5rem; font-weight: 700; color: #0f172a;"><?= number_format($stats['total_hot_leads']) ?></p>
            </div>
        </div>

        <div class="card" style="padding: 1.5rem; display: flex; align-items: center; gap: 1rem; border-left: 4px solid #10b981;">
            <div style="background: #d1fae5; padding: 12px; border-radius: 12px; color: #059669;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 24px; height: 24px;"><path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" /></svg>
            </div>
            <div>
                <p style="color: #64748b; font-size: 0.85rem; font-weight: 600; margin-bottom: 4px;">Score Promedio</p>
                <p style="font-size: 1.5rem; font-weight: 700; color: #0f172a;"><?= number_format($stats['average_score']) ?>%</p>
            </div>
        </div>
    </div>

    <!-- Filtros de Lead Scoring -->
    <div class="card" style="margin-bottom: 2rem; padding: 1.5rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px;">
        <form action="<?= site_url('admin/ia-marketing') ?>" method="GET" style="display: flex; flex-wrap: wrap; gap: 15px; align-items: flex-end;">
            <div style="flex: 3; min-width: 250px;">
                <label for="liveSearch" style="display: block; font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 6px;">Búsqueda Instantánea</label>
                <input type="text" id="liveSearch" class="input" placeholder="Nombre o email..." style="width: 100%;">
            </div>
            
            <div style="flex: 1; min-width: 110px;">
                <label for="days_inactive" style="display: block; font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 6px;">Días Inactivo</label>
                <input type="number" name="days_inactive" id="days_inactive" class="input" value="<?= $filters['days_inactive'] ?>" placeholder="0" min="0" style="width: 100%;">
            </div>

            <div style="flex: 1; min-width: 110px;">
                <label for="min_searches" style="display: block; font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 6px;">Mín. Búsquedas</label>
                <input type="number" name="min_searches" id="min_searches" class="input" value="<?= $filters['min_searches'] ?>" placeholder="0" min="0" style="width: 100%;">
            </div>

            <div style="flex: 1; min-width: 100px;">
                <label for="min_api" style="display: block; font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 6px;">Min. API</label>
                <input type="number" name="min_api" id="min_api" class="input" value="<?= $filters['min_api'] ?>" placeholder="0" min="0" style="width: 100%;">
            </div>

            <div style="flex: 1; min-width: 130px;">
                <label for="email_status" style="display: block; font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 6px;">Email</label>
                <select name="email_status" id="email_status" class="input" style="width: 100%; height: 42px; padding: 0 10px;">
                    <option value="all" <?= $filters['email_status'] == 'all' ? 'selected' : '' ?>>Todos</option>
                    <option value="never" <?= $filters['email_status'] == 'never' ? 'selected' : '' ?>>Nunca enviado</option>
                    <option value="at_least_one" <?= $filters['email_status'] == 'at_least_one' ? 'selected' : '' ?>>Al menos uno</option>
                </select>
            </div>

            <div style="flex: 1; min-width: 150px;">
                <label for="sort_by" style="display: block; font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 6px;">Ordenar por</label>
                <select name="sort_by" id="sort_by" class="input" style="width: 100%; height: 42px; padding: 0 10px;">
                    <option value="score" <?= $filters['sort_by'] == 'score' ? 'selected' : '' ?>>Lead Score (Recomendado)</option>
                    <option value="created_at" <?= $filters['sort_by'] == 'created_at' ? 'selected' : '' ?>>Fecha Registro</option>
                    <option value="last_login_at" <?= $filters['sort_by'] == 'last_login_at' ? 'selected' : '' ?>>Último Login</option>
                    <option value="total_searches" <?= $filters['sort_by'] == 'total_searches' ? 'selected' : '' ?>>Más Búsquedas</option>
                    <option value="total_api_requests" <?= $filters['sort_by'] == 'total_api_requests' ? 'selected' : '' ?>>Más API</option>
                </select>
            </div>

            <div style="width: 100px;">
                <label for="sort_dir" style="display: block; font-size: 0.8rem; font-weight: 700; color: #64748b; margin-bottom: 6px;">Orden</label>
                <select name="sort_dir" id="sort_dir" class="input" style="width: 100%; height: 42px; padding: 0 10px;">
                    <option value="DESC" <?= $filters['sort_dir'] == 'DESC' ? 'selected' : '' ?>>DESC</option>
                    <option value="ASC" <?= $filters['sort_dir'] == 'ASC' ? 'selected' : '' ?>>ASC</option>
                </select>
            </div>

            <div style="display: flex; gap: 10px; flex-shrink: 0; padding-bottom: 2px;">
                <button type="submit" class="btn" style="background: #10b981; border: none; min-width: 100px;">Filtrar</button>
                <a href="<?= site_url('admin/ia-marketing') ?>" class="btn ghost" style="padding: 10px 15px; border: 1px solid #e2e8f0; min-width: 100px; text-align: center;">Limpiar</a>
            </div>
        </form>
    </div>

    <div class="card" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 1100px;" id="leadsTable">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem; width: 5%;">Score</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem; width: 20%;">Lead</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem; text-align: center;">Días Inactivo</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem; text-align: center;">Búsquedas Web</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem; text-align: center;">Llamadas API</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem; text-align: center; width: 18%;">Seguimiento Email</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem; text-align: right;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($leads as $l): ?>
                <?php 
                    $scoreColor = '#ef4444'; // Red for low
                    $scoreBg = '#fee2e2';
                    if ($l->score >= 80) {
                        $scoreColor = '#059669'; // Green 
                        $scoreBg = '#d1fae5';
                    } elseif ($l->score >= 50) {
                        $scoreColor = '#d97706'; // Orange
                        $scoreBg = '#fef3c7';
                    }
                    
                    $daysSinceLogin = '-';
                    if ($l->last_login_at) {
                        $daysSinceLogin = round((strtotime(date('Y-m-d')) - strtotime($l->last_login_at)) / (60 * 60 * 24));
                    }
                ?>
                <tr class="lead-row" data-name="<?= esc(strtolower($l->name)) ?>" data-email="<?= esc(strtolower($l->email)) ?>" data-status="<?= $l->sub_status ?>" data-plan="<?= $l->plan_id ?>" style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;">
                    <td style="padding: 12px;">
                        <div style="background: <?= $scoreBg ?>; color: <?= $scoreColor ?>; font-weight: 800; text-align: center; border-radius: 8px; padding: 8px; font-size: 1.1rem; border: 1px solid <?= $scoreColor ?>33;">
                            <?= $l->score ?>%
                        </div>
                    </td>
                    <td style="padding: 12px;">
                        <div style="font-weight: 600; font-size: 0.95rem; display: flex; align-items: center; gap: 8px;">
                            <?= esc($l->name) ?>
                            <?php if($l->sub_status === 'active' && $l->plan_id == 1): ?>
                                <span class="pill" style="font-size: 0.65rem; background: #f0fdf4; color: #16a34a; border: 1px solid #22c55e40;">Plan Free</span>
                            <?php elseif($l->sub_status === 'active'): ?>
                                <span class="pill" style="font-size: 0.65rem; background: #cffafe; color: #0891b2; border: 1px solid #06b6d440;">Cliente Activo</span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 0.8rem; color: #64748b;"><?= esc($l->email) ?></div>
                        <div style="font-size: 0.7rem; color: #94a3b8; margin-top: 4px;">Registrado: <?= date('d M Y', strtotime($l->created_at)) ?></div>
                    </td>
                    
                    <td style="padding: 12px; text-align: center;">
                        <span style="font-weight: 600; color: <?= $daysSinceLogin !== '-' && $daysSinceLogin < 10 ? '#10b981' : '#64748b' ?>;">
                            <?= $daysSinceLogin ?> <?= $daysSinceLogin !== '-' ? 'días' : '' ?>
                        </span>
                    </td>
                    
                    <td style="padding: 12px; text-align: center;">
                        <span style="font-weight: 600; color: <?= $l->total_searches > 20 ? '#10b981' : '#64748b' ?>;">
                            <?= number_format($l->total_searches) ?>
                        </span>
                    </td>
                    
                    <td style="padding: 12px; text-align: center;">
                        <span style="font-weight: 600; color: <?= $l->total_api_requests > 100 ? '#10b981' : '#64748b' ?>;">
                            <?= number_format($l->total_api_requests) ?>
                        </span>
                    </td>

                    <td style="padding: 12px;">
                        <?php if ($l->last_email_at): ?>
                            <div style="font-size: 0.8rem; margin-bottom: 4px;">
                                <span style="color: #64748b;">Último:</span> 
                                <span style="font-weight: 600;"><?= date('d/m/y H:i', strtotime($l->last_email_at)) ?></span>
                            </div>
                            <div style="display: flex; gap: 4px; flex-wrap: wrap;">
                                <?php if ($l->last_email_clicked): ?>
                                    <span class="pill" style="background: #ecfdf5; color: #059669; border: 1px solid #10b98140; font-size: 0.65rem; padding: 2px 6px;">Clicado</span>
                                <?php elseif ($l->last_email_opened): ?>
                                    <span class="pill" style="background: #eff6ff; color: #2563eb; border: 1px solid #3b82f640; font-size: 0.65rem; padding: 2px 6px;">Leído</span>
                                <?php elseif ($l->last_email_status === 'success'): ?>
                                    <span class="pill" style="background: #f8fafc; color: #64748b; border: 1px solid #e2e8f0; font-size: 0.65rem; padding: 2px 6px;">Enviado</span>
                                <?php else: ?>
                                    <span class="pill" style="background: #fef2f2; color: #dc2626; border: 1px solid #ef444440; font-size: 0.65rem; padding: 2px 6px;">Error</span>
                                <?php endif; ?>
                                
                                <button onclick="showEmailHistory(<?= $l->id ?>, '<?= esc($l->name) ?>')" style="background: none; border: none; color: #2152ff; font-size: 0.7rem; font-weight: 700; cursor: pointer; text-decoration: underline; padding: 0;">
                                    (Ver <?= $l->total_emails_sent ?>)
                                </button>
                            </div>
                        <?php else: ?>
                            <span style="color: #94a3b8; font-size: 0.8rem; font-style: italic;">Sin emails enviados</span>
                        <?php endif; ?>
                    </td>
                    
                    <td style="padding: 12px; text-align: right;">
                        <a href="<?= site_url('admin/users/email/' . $l->id) ?>" class="btn" style="padding: 6px 12px; font-size: 0.8rem; background: <?= ($l->sub_status === 'active' && $l->plan_id > 1) ? '#cbd5e1' : '#2152ff' ?>;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px; height: 14px; margin-right: 4px; display: inline-block; vertical-align: sub;"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                            Enviar Promo
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if(empty($leads)): ?>
                <tr>
                    <td colspan="7" style="padding: 2rem; text-align: center; color: #64748b;">
                        No hay usuarios con actividad reciente para puntuar.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</main>

<!-- Modal Historial Email -->
<div id="emailHistoryModal" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 9999; backdrop-filter: blur(4px); align-items: center; justify-content: center;">
    <div style="background: white; width: 90%; max-width: 600px; border-radius: 20px; padding: 30px; position: relative; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);">
        <button onclick="closeEmailModal()" style="position: absolute; top: 20px; right: 20px; background: #f1f5f9; border: none; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; color: #64748b;">&times;</button>
        <h2 id="modalTitle" style="margin-bottom: 20px; font-size: 1.25rem;">Historial de Emails</h2>
        <div id="modalContent" style="max-height: 400px; overflow-y: auto;">
            <p style="text-align: center; color: #64748b;">Cargando historial...</p>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const liveSearchVal = document.getElementById('liveSearch');
        const rows = document.querySelectorAll('.lead-row');

        function filterRows() {
            const query = liveSearchVal.value.toLowerCase().trim();

            rows.forEach(row => {
                const name = row.getAttribute('data-name');
                const email = row.getAttribute('data-email');

                let show = true;
                
                if (query !== '' && !name.includes(query) && !email.includes(query)) {
                    show = false;
                }

                row.style.display = show ? '' : 'none';
            });
        }

        liveSearchVal.addEventListener('input', filterRows);
    });

    function showEmailHistory(userId, userName) {
        const modal = document.getElementById('emailHistoryModal');
        const content = document.getElementById('modalContent');
        const title = document.getElementById('modalTitle');
        
        modal.style.display = 'flex';
        title.innerText = 'Historial: ' + userName;
        content.innerHTML = '<p style="text-align: center; color: #64748b; padding: 20px;">Cargando historial...</p>';

        fetch('<?= site_url('admin/email-history/') ?>' + userId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.json())
        .then(logs => {
            if (logs.length === 0) {
                content.innerHTML = '<p style="text-align: center; color: #64748b; padding: 20px;">No hay historial de correos.</p>';
                return;
            }

            let html = '<div style="display: flex; flex-direction: column; gap: 12px;">';
            logs.forEach(log => {
                let statusBadge = '<span style="font-size: 0.7rem; color: #64748b;">Enviado</span>';
                if (log.clicked_at) statusBadge = '<span style="font-size: 0.7rem; color: #059669; font-weight: 700;">Clicado</span>';
                else if (log.opened_at) statusBadge = '<span style="font-size: 0.7rem; color: #2563eb; font-weight: 700;">Leído</span>';
                
                html += `
                    <div style="padding: 15px; background: #f8fafc; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <span style="font-size: 0.75rem; color: #94a3b8;">${log.created_at}</span>
                            ${statusBadge}
                        </div>
                        <div style="font-weight: 700; font-size: 0.9rem; color: #0f172a; margin-bottom: 5px;">${log.subject}</div>
                        <div style="font-size: 0.8rem; color: #64748b; line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                            ${log.message.replace(/<[^>]*>?/gm, '')}
                        </div>
                    </div>
                `;
            });
            html += '</div>';
            content.innerHTML = html;
        })
        .catch(err => {
            content.innerHTML = '<p style="text-align: center; color: #ef4444; padding: 20px;">Error al cargar el historial.</p>';
        });
    }

    function closeEmailModal() {
        document.getElementById('emailHistoryModal').style.display = 'none';
    }
</script>

<?= view('partials/footer') ?>
</body>
</html>
