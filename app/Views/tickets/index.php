<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app' ) ?>
<?= $this->section('styles') ?>
" />
    <style>
        .tkt-wrapper { background-color: #f3f6f9; min-height: calc(100vh - 80px); padding: 40px 0 80px; }
        .tkt-container { max-width: 1200px; width: 95%; margin: 0 auto; }
        
        .tkt-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; }
        .tkt-header h1 { font-size: 2.25rem; font-weight: 900; color: #0f172a; margin: 0; letter-spacing: -0.03em; }
        
        .tkt-btn-create { background: linear-gradient(135deg, #2152ff 0%, #0369a1 100%); color: white; border: none; padding: 12px 28px; border-radius: 12px; font-weight: 800; font-size: 1.05rem; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 15px rgba(33, 82, 255, 0.25); text-decoration: none; display: inline-flex; align-items: center; gap: 8px; }
        .tkt-btn-create:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(33, 82, 255, 0.35); color: white; }
        
        .tkt-list-card { background: white; border-radius: 20px; box-shadow: 0 10px 40px -10px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; overflow: hidden; }
        
        .tkt-list-item { display: flex; align-items: center; justify-content: space-between; padding: 24px 32px; border-bottom: 1px solid #f1f5f9; text-decoration: none; color: inherit; transition: background 0.2s; }
        .tkt-list-item:last-child { border-bottom: none; }
        .tkt-list-item:hover { background: #f8fafc; }
        
        .tkt-item-main { flex: 1; }
        .tkt-item-title { font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0 0 10px 0; display: flex; align-items: center; gap: 12px; }
        
        .tkt-item-meta { display: flex; gap: 16px; align-items: center; font-size: 0.9rem; color: #64748b; font-weight: 500; flex-wrap: wrap; }
        
        /* Badges (isolated) */
        .tkt-badge { padding: 4px 12px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 6px; }
        .tkt-badge-open { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .tkt-badge-in_progress { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .tkt-badge-answered { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .tkt-badge-closed { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        
        .tkt-badge-priority-low { background: #f1f5f9; color: #475569; }
        .tkt-badge-priority-medium { background: #e0f2fe; color: #0369a1; }
        .tkt-badge-priority-high { background: #ffedd5; color: #c2410c; }
        .tkt-badge-priority-urgent { background: #fee2e2; color: #b91c1c; }
        
        .tkt-empty-state { text-align: center; padding: 80px 20px; }
        .tkt-empty-state svg { width: 64px; height: 64px; color: #94a3b8; margin-bottom: 24px; }
        .tkt-empty-state h3 { font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0 0 12px; }
        .tkt-empty-state p { color: #64748b; font-size: 1.05rem; margin: 0; }
        
        .tkt-alert { background: #f0fdf4; border-left: 5px solid #10b981; padding: 20px 24px; border-radius: 12px; margin-bottom: 32px; color: #166534; font-weight: 600; font-size: 1.05rem; display: flex; align-items: center; gap: 16px; box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.2); }
    </style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="tkt-container">
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

            <div class="tkt-list-card">
                <div class="tkt-header" style="padding: 32px; margin: 0; border-bottom: 1px solid #e2e8f0;">
                    <h1>Mis Tickets</h1>
                    <a href="<?= site_url('tickets/create') ?>" class="tkt-btn-create">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 5v14M5 12h14"/></svg>
                        Abrir Ticket
                    </a>
                </div>

                <?php if(empty($tickets)): ?>
                    <div class="tkt-empty-state">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                        <h3>No tienes tickets abiertos</h3>
                        <p>Si necesitas ayuda técnica o administrativa, abre un nuevo ticket.</p>
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table style="width: 100%; border-collapse: collapse; text-align: left;">
                            <thead>
                                <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0; color: #64748b; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700;">
                                    <th style="padding: 16px 24px;">ID / Asunto</th>
                                    <th style="padding: 16px 24px;">Estado</th>
                                    <th style="padding: 16px 24px;">Prioridad</th>
                                    <th style="padding: 16px 24px;">Categoría</th>
                                    <th style="padding: 16px 24px;">Actualizado</th>
                                    <th style="padding: 16px 24px; text-align: right;">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($tickets as $ticket): ?>
                                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                        <td style="padding: 16px 24px; color: #0f172a; font-weight: 600; font-family: 'Outfit', sans-serif; font-size: 1.05rem;">
                                            <a href="<?= site_url('tickets/'.$ticket['id']) ?>" style="color: inherit; text-decoration: none;">
                                                #<?= $ticket['id'] ?> - <?= esc($ticket['subject']) ?>
                                            </a>
                                        </td>
                                        <td style="padding: 16px 24px;">
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
                                        <td style="padding: 16px 24px;">
                                            <span class="tkt-badge tkt-badge-priority-<?= $ticket['priority'] ?>">
                                                <?php 
                                                    $priorities = ['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta', 'urgent' => 'Urgente'];
                                                    echo $priorities[$ticket['priority']] ?? $ticket['priority'];
                                                ?>
                                            </span>
                                        </td>
                                        <td style="padding: 16px 24px;">
                                            <span class="tkt-badge" style="background: #f8fafc; color: #475569; border: 1px solid #e2e8f0;">
                                                <?= esc(ucfirst(str_replace('_', ' ', $ticket['category'] ?? 'general'))) ?>
                                            </span>
                                        </td>
                                        <td style="padding: 16px 24px; color: #64748b; font-size: 0.9rem; font-weight: 500;">
                                            <?= date('d M Y, H:i', strtotime($ticket['updated_at'])) ?>
                                        </td>
                                        <td style="padding: 16px 24px; text-align: right;">
                                            <a href="<?= site_url('tickets/'.$ticket['id']) ?>" style="color: #2152ff; font-weight: 600; text-decoration: none; font-size: 0.9rem; display: inline-flex; align-items: center; gap: 4px;">
                                                Ver Ticket <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"></polyline></svg>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
<?= $this->endSection() ?>
