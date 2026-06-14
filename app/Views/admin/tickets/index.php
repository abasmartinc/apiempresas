<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => 'Gestión de Tickets | Admin APIEmpresas.es']) ?>
    <style>
        :root {
            --kpi-blue: linear-gradient(135deg, #6366f1 0%, #4338ca 100%);
            --kpi-green: linear-gradient(135deg, #10b981 0%, #059669 100%);
            --kpi-orange: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            --kpi-purple: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            --kpi-rose: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
        }
        .container-admin { max-width: 1200px; margin: 0 auto; padding: 0 20px; }
        .kpi-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem; }
        .kpi-card { 
            position: relative; overflow: hidden; background: white; border-radius: 24px; padding: 2rem; 
            border: 1px solid rgba(255, 255, 255, 0.7); display: flex; flex-direction: column; 
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05); 
        }
        .kpi-card:hover { transform: translateY(-8px); box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1); }
        .kpi-card::before {
            content: ''; position: absolute; top: 0; right: 0; width: 100px; height: 100px;
            background: var(--kpi-color); opacity: 0.05; border-radius: 0 0 0 100%; pointer-events: none;
        }
        .kpi-icon-wrapper {
            width: 48px; height: 48px; border-radius: 14px; background: var(--kpi-color);
            display: flex; align-items: center; justify-content: center; margin-bottom: 1.5rem; color: white;
            box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.1);
        }
        .kpi-label { font-size: 0.85rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 0.5rem; }
        .kpi-value { font-size: 2.5rem; font-weight: 900; color: #1e293b; letter-spacing: -0.02em; margin-bottom: 0.5rem; line-height: 1; }
        .kpi-sub { font-size: 0.85rem; color: #94a3b8; font-weight: 500; display: flex; align-items: center; gap: 6px; }

        .filter-container { background: white; border-radius: 20px; padding: 1.5rem; border: 1px solid #e2e8f0; margin-bottom: 2rem; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); }
        .filter-row { display: grid; grid-template-columns: 1fr auto; gap: 1.5rem; align-items: end; }
        .input-group { position: relative; }
        .input-group svg { position: absolute; left: 12px; top: 38px; color: #94a3b8; pointer-events: none; }
        .input-group select { padding-left: 40px !important; }

        .tkt-badge { padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 6px; }
        .tkt-badge-open { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .tkt-badge-in_progress { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .tkt-badge-answered { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .tkt-badge-closed { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        
        .tkt-badge-priority-low { background: #f1f5f9; color: #475569; }
        .tkt-badge-priority-medium { background: #e0f2fe; color: #0369a1; }
        .tkt-badge-priority-high { background: #ffedd5; color: #c2410c; }
        .tkt-badge-priority-urgent { background: #fee2e2; color: #b91c1c; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin" style="padding: 40px 0;">
    <?php if(session()->getFlashdata('success')): ?>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: '<?= esc(session()->getFlashdata('success')) ?>',
                    showConfirmButton: false,
                    timer: 3000,
                    timerProgressBar: true
                });
            });
        </script>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <h1 class="title">Gestión de Tickets</h1>
        <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card" style="--kpi-color: var(--kpi-blue);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
            </div>
            <span class="kpi-label">Total Tickets</span>
            <span class="kpi-value"><?= number_format($kpis['total'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Tickets creados históricamente</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-orange);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <span class="kpi-label">Abiertos</span>
            <span class="kpi-value"><?= number_format($kpis['open'] + $kpis['in_progress'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Pendientes de resolución</span>
        </div>

        <div class="kpi-card" style="--kpi-color: var(--kpi-green);">
            <div class="kpi-icon-wrapper">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
            </div>
            <span class="kpi-label">Resueltos</span>
            <span class="kpi-value"><?= number_format($kpis['answered'] + $kpis['closed'], 0, ',', '.') ?></span>
            <span class="kpi-sub">Tickets gestionados</span>
        </div>
    </div>

    <!-- Filtros -->
    <div class="filter-container">
        <form action="<?= site_url('admin/tickets') ?>" method="get" id="filter-form">
            <div class="filter-row">
                <div class="input-group">
                    <label style="display: block; font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.025em;">Filtrar por estado</label>
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                    <select name="status" class="input" style="width: 100%;" onchange="this.form.submit()">
                        <option value="all" <?= ($statusFilter ?? '') === 'all' ? 'selected' : '' ?>>Todos los estados</option>
                        <option value="open" <?= ($statusFilter ?? '') === 'open' ? 'selected' : '' ?>>Abierto</option>
                        <option value="in_progress" <?= ($statusFilter ?? '') === 'in_progress' ? 'selected' : '' ?>>En Proceso</option>
                        <option value="answered" <?= ($statusFilter ?? '') === 'answered' ? 'selected' : '' ?>>Respondido</option>
                        <option value="closed" <?= ($statusFilter ?? '') === 'closed' ? 'selected' : '' ?>>Cerrado</option>
                    </select>
                </div>

                <div style="display: flex; gap: 8px;">
                    <button type="submit" class="btn" style="height: 42px; padding: 0 25px;">Filtrar</button>
                    <a href="<?= site_url('admin/tickets') ?>" class="btn ghost" style="height: 42px; width: 42px; display: flex; align-items: center; justify-content: center; padding: 0;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M23 4v6h-6"></path><path d="M20.49 15a9 9 0 1 1-2.12-9.36L23 10"></path></svg>
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Tabla -->
    <div class="card" style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
            <thead>
                <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">ID / Asunto</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Usuario</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Estado</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Prioridad</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem;">Actualizado</th>
                    <th style="padding: 12px; color: #64748b; font-size: 0.85rem; text-align: right;">Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if(empty($tickets)): ?>
                    <tr>
                        <td colspan="6" style="padding: 40px; text-align: center; color: #64748b; font-weight: 500;">No se encontraron tickets con estos criterios.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($tickets as $ticket): ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;">
                        <td style="padding: 12px;">
                            <a href="<?= site_url('admin/tickets/'.$ticket['id']) ?>" style="color: #0f172a; text-decoration: none; font-weight: 700; font-size: 0.95rem;">
                                #<?= $ticket['id'] ?> - <?= esc($ticket['subject']) ?>
                            </a>
                            <div style="font-size: 0.75rem; color: #64748b; font-weight: 700; text-transform: uppercase; margin-top: 4px;">
                                <?= esc(str_replace('_', ' ', $ticket['category'] ?? 'general')) ?>
                            </div>
                        </td>
                        <td style="padding: 12px;">
                            <div style="font-weight: 600; font-size: 0.85rem;"><?= esc($ticket['user_name'] ?? 'Usuario Desconocido') ?></div>
                            <div style="font-size: 0.75rem; color: #64748b;"><?= esc($ticket['user_email'] ?? '-') ?></div>
                        </td>
                        <td style="padding: 12px;">
                            <?php 
                                $status_classes = ['open' => 'open', 'in_progress' => 'in_progress', 'answered' => 'answered', 'closed' => 'closed'];
                                $statuses = ['open' => 'Abierto', 'in_progress' => 'En Proceso', 'answered' => 'Respondido', 'closed' => 'Cerrado'];
                                $st_class = $status_classes[$ticket['status']] ?? 'open';
                                $st_label = $statuses[$ticket['status']] ?? $ticket['status'];
                            ?>
                            <span class="tkt-badge tkt-badge-<?= $st_class ?>">
                                <div style="width: 6px; height: 6px; border-radius: 50%; background: currentColor;"></div>
                                <?= $st_label ?>
                            </span>
                        </td>
                        <td style="padding: 12px;">
                            <span class="tkt-badge tkt-badge-priority-<?= $ticket['priority'] ?>">
                                <?php 
                                    $priorities = ['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta', 'urgent' => 'Urgente'];
                                    echo $priorities[$ticket['priority']] ?? $ticket['priority'];
                                ?>
                            </span>
                        </td>
                        <td style="padding: 12px; color: #64748b; font-size: 0.85rem;">
                            <?= date('d/m/Y H:i', strtotime($ticket['updated_at'])) ?>
                        </td>
                        <td style="padding: 12px; text-align: right;">
                            <a href="<?= site_url('admin/tickets/'.$ticket['id']) ?>" style="color: #2152ff; font-weight: 600; text-decoration: none; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 4px;">
                                Ver Ticket <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</main>

<?= view('partials/footer') ?>
</body>
</html>
