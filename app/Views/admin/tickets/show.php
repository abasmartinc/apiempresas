<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => 'Detalle del Ticket | Admin APIEmpresas.es']) ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        .admin-body { font-family: 'Inter', sans-serif; background-color: #f8fafc; }
        .container-admin { max-width: 1200px; margin: 0 auto; padding: 40px 20px; }
        
        .btn-back { display: inline-flex; align-items: center; gap: 8px; color: #64748b; text-decoration: none; font-weight: 600; margin-bottom: 24px; transition: color 0.2s; }
        .btn-back:hover { color: #0f172a; }
        
        .layout-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 24px; align-items: start; }
        
        /* Chat Area */
        .chat-area { background: white; border-radius: 20px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .chat-header { margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; }
        .chat-header h1 { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0 0 8px 0; }
        
        .chat-container { display: flex; flex-direction: column; gap: 20px; margin-bottom: 32px; max-height: 500px; overflow-y: auto; padding-right: 10px; }
        .message-bubble { max-width: 85%; padding: 16px 20px; border-radius: 16px; text-align: left !important; }
        .message-user { align-self: flex-start; background: #f8fafc; border: 1px solid #e2e8f0; color: #0f172a; border-bottom-left-radius: 4px; }
        .message-admin { align-self: flex-end; background: #2152ff; color: white; border-bottom-right-radius: 4px; }
        .message-private { align-self: flex-end; background: #fef3c7; color: #92400e; border: 1px solid #fde68a; border-bottom-right-radius: 4px; }
        
        .message-meta { font-size: 0.75rem; font-weight: 600; margin-bottom: 8px; opacity: 0.8; display: flex; justify-content: space-between; }
        .message-content { font-size: 0.95rem; line-height: 1.6; white-space: pre-wrap; text-align: left !important; }
        
        .reply-box { border-top: 1px solid #f1f5f9; padding-top: 24px; }
        .form-control { width: 100%; padding: 14px 16px; border-radius: 12px; border: 1px solid #cbd5e1; font-family: 'Inter', sans-serif; font-size: 1rem; color: #0f172a; transition: all 0.2s; background: #f8fafc; margin-bottom: 16px; resize: vertical; min-height: 120px; }
        .form-control:focus { outline: none; border-color: #2152ff; box-shadow: 0 0 0 4px rgba(33, 82, 255, 0.1); background: white; }
        
        .btn-submit { background: #2152ff; color: white; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: all 0.2s; }
        .btn-submit:hover { background: #1e3a8a; }
        
        /* Sidebar Info */
        .info-sidebar { background: white; border-radius: 20px; padding: 24px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); border: 1px solid #e2e8f0; }
        .info-sidebar h3 { font-size: 1.1rem; font-weight: 800; color: #0f172a; margin: 0 0 20px 0; padding-bottom: 12px; border-bottom: 1px solid #f1f5f9; }
        
        .info-item { margin-bottom: 16px; }
        .info-label { font-size: 0.8rem; font-weight: 700; color: #64748b; text-transform: uppercase; margin-bottom: 4px; display: block; }
        .info-value { font-size: 1rem; font-weight: 600; color: #334155; }
        
        .form-select { width: 100%; padding: 10px; border-radius: 8px; border: 1px solid #cbd5e1; font-family: inherit; margin-bottom: 16px; }
        .btn-update { width: 100%; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; padding: 10px; border-radius: 8px; font-weight: 700; cursor: pointer; transition: all 0.2s; }
        .btn-update:hover { background: #e2e8f0; color: #0f172a; }
        
        .ticket-badge { padding: 4px 8px; border-radius: 999px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; }
        .ticket-badge-status-open { background: #e0e7ff; color: #4338ca; }
        .ticket-badge-status-in_progress { background: #fef3c7; color: #d97706; }
        .ticket-badge-status-answered { background: #dcfce7; color: #15803d; }
        .ticket-badge-status-closed { background: #f1f5f9; color: #64748b; }
        
        .ticket-badge-priority-low { background: #f1f5f9; color: #475569; }
        .ticket-badge-priority-medium { background: #e0f2fe; color: #0369a1; }
        .ticket-badge-priority-high { background: #ffedd5; color: #c2410c; }
        .ticket-badge-priority-urgent { background: #fee2e2; color: #b91c1c; }
    </style>
</head>
<body class="admin-body">
    <?= view('partials/header_admin') ?>

    <main class="container-admin">
        <a href="<?= site_url('admin/tickets') ?>" class="btn-back">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Volver al listado
        </a>
        
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

        <div class="layout-grid">
            <!-- Left Column: Chat -->
            <div class="chat-area">
                <div class="chat-header">
                    <h1>#<?= $ticket['id'] ?> - <?= esc($ticket['subject']) ?></h1>
                    <div style="color: #64748b; font-size: 0.9rem; font-weight: 500;">
                        Usuario: <strong><?= esc($ticket['user_name'] ?? 'Usuario Desconocido') ?></strong> (<?= esc($ticket['user_email'] ?? '') ?>)
                    </div>
                </div>
                
                <div class="chat-container">
                    <?php foreach($replies as $reply): ?>
                        <?php if(isset($reply['is_private']) && $reply['is_private']) continue; ?>
                        <div class="message-bubble <?= $reply['is_admin'] ? 'message-admin' : 'message-user' ?>">
                            <div class="message-meta">
                                <span><?= $reply['is_admin'] ? 'Tú (Admin)' : esc($reply['sender_name'] ?? 'Usuario') ?></span>
                                <span><?= date('d/m/Y H:i', strtotime($reply['created_at'])) ?></span>
                            </div>
                            <div class="message-content"><?= nl2br(esc($reply['message'])) ?><?php if(!empty($reply['attachment'])): ?>
                                    <div style="margin-top: 12px; padding-top: 12px; border-top: 1px dashed rgba(255,255,255,0.2); white-space: normal;">
                                        <a href="<?= base_url($reply['attachment']) ?>" target="_blank" style="display: inline-flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 700; color: inherit; text-decoration: underline;">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21.44 11.05l-9.19 9.19a6 6 0 0 1-8.49-8.49l9.19-9.19a4 4 0 0 1 5.66 5.66l-9.2 9.19a2 2 0 0 1-2.83-2.83l8.49-8.48"></path></svg>
                                            Ver archivo adjunto
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="reply-box">
                    <form action="<?= site_url('admin/tickets/'.$ticket['id'].'/reply') ?>" method="POST" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <textarea name="message" class="form-control" placeholder="Escribe tu respuesta al usuario aquí..." required></textarea>
                        
                        <div style="margin-bottom: 16px;">
                            <label style="font-size: 0.85rem; font-weight: 700; color: #475569; display: block; margin-bottom: 6px;">Adjuntar archivo (opcional)</label>
                            <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf,.txt,.json" style="width: 100%; padding: 8px; border: 1px dashed #cbd5e1; border-radius: 8px; background: #f8fafc; font-size: 0.85rem;">
                        </div>

                        <div style="display: flex; justify-content: flex-end; align-items: center;">
                            <button type="submit" class="btn-submit">Enviar Respuesta Pública</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <!-- Right Column: Sidebar Info & Controls -->
            <div class="info-sidebar">
                <h3>Detalles del Ticket</h3>
                
                <div class="info-item">
                    <span class="info-label">Tema Relacionado</span>
                    <span class="ticket-badge" style="background: #e2e8f0; color: #475569;">
                        <?= esc(ucfirst(str_replace('_', ' ', $ticket['category'] ?? 'general'))) ?>
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label">Estado Actual</span>
                    <span class="ticket-badge ticket-badge-status-<?= $ticket['status'] ?>">
                        <?php 
                            $statuses = ['open' => 'Abierto', 'in_progress' => 'En Proceso', 'answered' => 'Respondido', 'closed' => 'Cerrado'];
                            echo $statuses[$ticket['status']] ?? $ticket['status'];
                        ?>
                    </span>
                </div>

                <div class="info-item">
                    <span class="info-label">Prioridad Actual</span>
                    <span class="ticket-badge ticket-badge-priority-<?= $ticket['priority'] ?>">
                        <?php 
                            $priorities = ['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta', 'urgent' => 'Urgente'];
                            echo $priorities[$ticket['priority']] ?? $ticket['priority'];
                        ?>
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Valoración del Usuario</span>
                    <?php if(!empty($ticket['rating'])): ?>
                        <span style="color: #fbbf24; font-size: 1.2rem; font-weight: 800;">
                            <?= str_repeat('★', $ticket['rating']) ?><span style="color: #e2e8f0;"><?= str_repeat('★', 5 - $ticket['rating']) ?></span>
                        </span>
                    <?php else: ?>
                        <span style="color: #94a3b8; font-size: 0.85rem; font-style: italic;">Pendiente de valoración</span>
                    <?php endif; ?>
                </div>

                <div class="info-item">
                    <span class="info-label">Creado el</span>
                    <span class="info-value"><?= date('d/m/Y H:i', strtotime($ticket['created_at'])) ?></span>
                </div>

                <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 24px 0;">
                
                <h3 style="display: flex; justify-content: space-between; align-items: center;">
                    Notas Internas
                    <button type="button" onclick="document.getElementById('noteModal').style.display='block'" style="background: #f59e0b; color: white; border: none; padding: 4px 8px; border-radius: 6px; font-size: 0.75rem; font-weight: 800; cursor: pointer;">+ Poner nota</button>
                </h3>
                
                <div style="margin-bottom: 24px; max-height: 300px; overflow-y: auto;">
                    <?php $hasNotes = false; ?>
                    <?php foreach($replies as $reply): ?>
                        <?php if(isset($reply['is_private']) && $reply['is_private']): $hasNotes = true; ?>
                            <div style="background: #fef3c7; border: 1px solid #fde68a; padding: 12px; border-radius: 8px; margin-bottom: 12px;">
                                <div style="font-size: 0.7rem; font-weight: 800; color: #d97706; margin-bottom: 4px; display: flex; justify-content: space-between;">
                                    <span>Tú (Admin)</span>
                                    <span><?= date('d/m H:i', strtotime($reply['created_at'])) ?></span>
                                </div>
                                <div style="font-size: 0.85rem; color: #92400e; line-height: 1.5;">
                                    <?= nl2br(esc($reply['message'])) ?>
                                </div>
                                <?php if(!empty($reply['attachment'])): ?>
                                    <div style="margin-top: 8px; padding-top: 8px; border-top: 1px dashed rgba(146,64,14,0.2);">
                                        <a href="<?= base_url($reply['attachment']) ?>" target="_blank" style="font-size: 0.75rem; font-weight: 700; color: #b45309; text-decoration: underline;">Ver adjunto</a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    
                    <?php if(!$hasNotes): ?>
                        <p style="font-size: 0.85rem; color: #94a3b8; font-style: italic;">No hay notas internas.</p>
                    <?php endif; ?>
                </div>

                <hr style="border: 0; border-top: 1px solid #f1f5f9; margin: 24px 0;">
                
                <h3>Gestionar Ticket</h3>
                <form action="<?= site_url('admin/tickets/'.$ticket['id'].'/status') ?>" method="POST">
                    <?= csrf_field() ?>
                    <div style="margin-bottom: 12px;">
                        <span class="info-label">Cambiar Estado</span>
                        <select name="status" class="form-select">
                            <option value="open" <?= $ticket['status'] === 'open' ? 'selected' : '' ?>>Abierto</option>
                            <option value="in_progress" <?= $ticket['status'] === 'in_progress' ? 'selected' : '' ?>>En Proceso</option>
                            <option value="answered" <?= $ticket['status'] === 'answered' ? 'selected' : '' ?>>Respondido</option>
                            <option value="closed" <?= $ticket['status'] === 'closed' ? 'selected' : '' ?>>Cerrado</option>
                        </select>
                    </div>
                    
                    <div style="margin-bottom: 16px;">
                        <span class="info-label">Cambiar Prioridad</span>
                        <select name="priority" class="form-select">
                            <option value="low" <?= $ticket['priority'] === 'low' ? 'selected' : '' ?>>Baja</option>
                            <option value="medium" <?= $ticket['priority'] === 'medium' ? 'selected' : '' ?>>Media</option>
                            <option value="high" <?= $ticket['priority'] === 'high' ? 'selected' : '' ?>>Alta</option>
                            <option value="urgent" <?= $ticket['priority'] === 'urgent' ? 'selected' : '' ?>>Urgente</option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn-update">Guardar Cambios</button>
                </form>
            </div>
        </div>
    </main>

    <!-- Modal for Internal Note -->
    <div id="noteModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.5); z-index: 9999; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: white; width: 100%; max-width: 500px; border-radius: 16px; padding: 24px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); margin: 10vh auto;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
                <h3 style="margin: 0; font-size: 1.25rem; font-weight: 800; color: #0f172a;">Añadir Nota Interna</h3>
                <button type="button" onclick="document.getElementById('noteModal').style.display='none'" style="background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b;">&times;</button>
            </div>
            
            <form action="<?= site_url('admin/tickets/'.$ticket['id'].'/reply') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="is_private" value="1">
                
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px;">Mensaje de la nota</label>
                    <textarea name="message" class="form-control" style="min-height: 100px; margin-bottom: 0;" placeholder="Escribe tu nota aquí..." required></textarea>
                </div>
                
                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #475569; margin-bottom: 8px;">Adjuntar archivo (opcional)</label>
                    <input type="file" name="attachment" accept=".jpg,.jpeg,.png,.pdf,.txt,.json" style="width: 100%; padding: 8px; border: 1px dashed #cbd5e1; border-radius: 8px; background: #f8fafc; font-size: 0.85rem;">
                </div>
                
                <div style="display: flex; justify-content: flex-end; gap: 12px;">
                    <button type="button" onclick="document.getElementById('noteModal').style.display='none'" style="background: #f1f5f9; color: #475569; border: none; padding: 10px 16px; border-radius: 8px; font-weight: 700; cursor: pointer;">Cancelar</button>
                    <button type="submit" style="background: #f59e0b; color: white; border: none; padding: 10px 16px; border-radius: 8px; font-weight: 700; cursor: pointer;">Guardar Nota</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
