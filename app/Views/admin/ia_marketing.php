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
            --kpi-rose: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
        }
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
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
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); 
        }
        .kpi-card:hover { 
            transform: translateY(-8px); 
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1); 
        }
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 100px; height: 100px;
            background: var(--kpi-color);
            opacity: 0.05;
            border-radius: 0 0 0 100%;
            pointer-events: none;
        }
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
        .kpi-value { font-size: 2.5rem; font-weight: 900; color: #1e293b; letter-spacing: -0.02em; margin-bottom: 0.5rem; line-height: 1; }
        .kpi-sub { font-size: 0.85rem; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 6px; }
        .progress-bar-container {
            width: 100%;
            height: 6px;
            background: #f1f5f9;
            border-radius: 100px;
            margin-top: 1rem;
            overflow: hidden;
        }
        .progress-bar-fill {
            height: 100%;
            background: var(--kpi-color);
            border-radius: 100px;
            transition: width 1s ease-out;
        }
        .pill { padding: 2px 8px; border-radius: 6px; font-weight: 600; }
    </style>
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
    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-color: var(--kpi-blue);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
            </div>
            <span class="kpi-label">Leads Analizados</span>
            <span class="kpi-value"><?= number_format($stats['total_leads'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Usuarios gratuitos activos</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-rose);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2c0 1.8-1.4 3.5-3 5.3-2.1 2.4-3 4.6-3 6.7 0 4.4 3.4 8 8 8s8-3.6 8-8c0-2.1-.9-4.3-3-6.7-1.6-1.8-3-3.5-3-5.3z"></path></svg>
            </div>
            <span class="kpi-label">Hot Leads</span>
            <span class="kpi-value"><?= number_format($stats['total_hot_leads'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Alta probabilidad de conversión</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-green);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 20V10"></path><path d="M18 20V4"></path><path d="M6 20v-4"></path></svg>
            </div>
            <span class="kpi-label">Score Promedio</span>
            <span class="kpi-value"><?= number_format($stats['average_score'], 0, ',', '.') ?>%</span>
            <span class="kpi-sub">Calidad media de la audiencia</span>
            <div class="progress-bar-container">
                <div class="progress-bar-fill" style="width: <?= $stats['average_score'] ?>%;"></div>
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

    <div class="card" style="overflow-x: auto; position: relative;">
        <form id="bulk-action-form" action="<?= site_url('admin/users/email/bulk') ?>" method="post">
            <?= csrf_field() ?>
            <input type="hidden" name="return_to" value="admin/ia-marketing">
            <table style="width: 100%; border-collapse: collapse; min-width: 1100px;" id="leadsTable">
                <thead>
                    <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                        <th style="padding: 12px; width: 40px;"><input type="checkbox" id="select-all-leads"></th>
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
                    <td style="padding: 12px; text-align: center;">
                        <input type="checkbox" name="user_ids[]" value="<?= $l->id ?>" class="lead-checkbox">
                    </td>
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
        </form>
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
                
                if (!show) {
                    const cb = row.querySelector('.lead-checkbox');
                    if (cb) cb.checked = false;
                }
            });
            updateState();
        }

        liveSearchVal.addEventListener('input', filterRows);

        // Bulk Selection Logic
        const selectAll = document.getElementById('select-all-leads');
        const checkboxes = document.querySelectorAll('.lead-checkbox');
        const bulkForm = document.getElementById('bulk-action-form');
        
        // Create floating action bar (similar to users.php)
        const actionContainer = document.createElement('div');
        actionContainer.className = 'bulk-floating-bar';
        actionContainer.style.cssText = `
            position: fixed;
            bottom: 30px;
            left: 50%;
            transform: translateX(-50%);
            background: #fff;
            padding: 15px 30px;
            border-radius: 50px;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.2);
            z-index: 1000;
            display: none;
            align-items: center;
            gap: 20px;
            border: 1px solid #e2e8f0;
            animation: slideUp 0.3s ease-out;
        `;
        actionContainer.innerHTML = `
            <span style="font-weight: 700; color: #0f172a;"><span id="selected-count">0</span> seleccionados</span>
            <button type="button" class="btn" id="btn-bulk-email" style="background: #2152ff; border: none; padding: 10px 20px;">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 18px; height: 18px; margin-right: 8px; display: inline-block; vertical-align: middle;"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                Enviar Email a seleccionados
            </button>
        `;
        document.body.appendChild(actionContainer);

        function updateState() {
            const selected = document.querySelectorAll('.lead-checkbox:checked').length;
            document.getElementById('selected-count').innerText = selected;
            if (selected > 0) {
                actionContainer.style.display = 'flex';
            } else {
                actionContainer.style.display = 'none';
            }
        }

        if (selectAll) {
            selectAll.addEventListener('change', function() {
                checkboxes.forEach(cb => {
                    // Only check visible rows
                    if (cb.closest('.lead-row').style.display !== 'none') {
                        cb.checked = this.checked;
                    }
                });
                updateState();
            });
        }

        checkboxes.forEach(cb => {
            cb.addEventListener('change', updateState);
        });

        const btnBulk = document.getElementById('btn-bulk-email');
        if(btnBulk){
             btnBulk.addEventListener('click', function() {
                bulkForm.submit();
            });
        }
    });

    // Add keyframes for the floating bar
    const styleSheet = document.createElement("style");
    styleSheet.innerText = `
        @keyframes slideUp {
            from { transform: translate(-50%, 100px); opacity: 0; }
            to { transform: translate(-50%, 0); opacity: 1; }
        }
    `;
    document.head.appendChild(styleSheet);

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
