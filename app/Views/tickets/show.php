<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/dashboard.css') ?>" />
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800;900&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        .tkt-wrapper { font-family: 'Inter', sans-serif; background-color: #f3f6f9; min-height: calc(100vh - 80px); padding: 40px 0 80px; }
        
        /* Modern Wide Layout */
        .tkt-container { max-width: 1280px; width: 95%; margin: 0 auto; display: grid; grid-template-columns: 1fr 340px; gap: 40px; align-items: start; }
        
        @media (max-width: 1024px) {
            .tkt-container { grid-template-columns: 1fr; }
        }

        /* Typography & Links */
        .tkt-h1 { font-family: 'Outfit', sans-serif; font-size: 2.25rem; font-weight: 800; color: #0f172a; margin: 0 0 16px 0; letter-spacing: -0.03em; line-height: 1.2; }
        
        .tkt-back-link { display: inline-flex; align-items: center; gap: 8px; color: #64748b; text-decoration: none; font-weight: 600; margin-bottom: 32px; transition: color 0.2s; font-size: 0.95rem; }
        .tkt-back-link:hover { color: #2152ff; }
        
        /* Main Area */
        .tkt-main { display: flex; flex-direction: column; gap: 24px; }
        
        /* Message Cards */
        .tkt-msg-card { background: white; border-radius: 20px; padding: 28px; box-shadow: 0 4px 20px -5px rgba(0,0,0,0.03); border: 1px solid rgba(226, 232, 240, 0.6); display: flex; gap: 24px; transition: transform 0.2s, box-shadow 0.2s; position: relative; overflow: hidden; }
        .tkt-msg-card:hover { box-shadow: 0 8px 30px -5px rgba(0,0,0,0.06); transform: translateY(-2px); }
        
        /* Distinct look for Admin */
        .tkt-msg-admin { background: #fafcff; border-color: #dbeafe; }
        .tkt-msg-admin::before { content: ''; position: absolute; left: 0; top: 0; bottom: 0; width: 4px; background: linear-gradient(180deg, #3b82f6 0%, #2563eb 100%); border-radius: 4px 0 0 4px; }
        
        /* Avatars */
        .tkt-avatar { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-weight: 800; font-family: 'Outfit', sans-serif; font-size: 1.1rem; color: white; flex-shrink: 0; box-shadow: 0 4px 10px rgba(0,0,0,0.1); }
        .tkt-avatar-user { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); }
        .tkt-avatar-admin { background: linear-gradient(135deg, #2152ff 0%, #0ea5e9 100%); }
        
        /* Message Content */
        .tkt-msg-content { flex: 1; min-width: 0; }
        .tkt-msg-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; }
        .tkt-sender { font-family: 'Outfit', sans-serif; font-weight: 700; color: #0f172a; font-size: 1.1rem; display: flex; align-items: center; gap: 10px; }
        .tkt-admin-badge { background: #dbeafe; color: #1d4ed8; font-family: 'Inter', sans-serif; font-size: 0.7rem; padding: 3px 8px; border-radius: 6px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; }
        .tkt-time { font-size: 0.85rem; color: #94a3b8; font-weight: 500; }
        .tkt-body { font-size: 1rem; color: #334155; line-height: 1.7; white-space: pre-wrap; }
        
        /* Reply Box (Rich Editor Style) */
        .tkt-reply-card { background: white; border-radius: 20px; box-shadow: 0 10px 40px -10px rgba(33, 82, 255, 0.08); border: 1px solid #e2e8f0; overflow: hidden; margin-top: 16px; transition: all 0.3s ease; position: relative; }
        .tkt-reply-card:focus-within { border-color: #93c5fd; box-shadow: 0 15px 50px -10px rgba(33, 82, 255, 0.15); transform: translateY(-2px); }
        .tkt-reply-header { padding: 16px 24px; background: #f8fafc; border-bottom: 1px solid #f1f5f9; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 8px; font-size: 0.95rem; }
        
        .tkt-textarea { width: 100%; border: none; padding: 24px; font-family: 'Inter', sans-serif; font-size: 1.05rem; color: #0f172a; resize: vertical; min-height: 140px; outline: none; line-height: 1.6; background: transparent; }
        .tkt-textarea::placeholder { color: #cbd5e1; }
        
        .tkt-reply-footer { display: flex; justify-content: space-between; align-items: center; padding: 16px 24px; border-top: 1px solid #f1f5f9; background: white; }
        
        /* Premium Buttons */
        .tkt-btn-send { background: linear-gradient(135deg, #2152ff 0%, #0369a1 100%); color: white; border: none; padding: 14px 32px; border-radius: 12px; font-weight: 800; font-family: 'Outfit', sans-serif; font-size: 1rem; cursor: pointer; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 4px 15px rgba(33, 82, 255, 0.3); }
        .tkt-btn-send:hover { transform: translateY(-2px); box-shadow: 0 8px 25px rgba(33, 82, 255, 0.4); }
        .tkt-btn-send svg { transition: transform 0.3s; }
        .tkt-btn-send:hover svg { transform: translateX(4px) translateY(-4px); }

        /* Sidebar */
        .tkt-sidebar { position: sticky; top: 40px; display: flex; flex-direction: column; gap: 24px; }
        
        .tkt-side-card { background: white; border-radius: 20px; padding: 32px; box-shadow: 0 4px 20px -5px rgba(0,0,0,0.03); border: 1px solid rgba(226, 232, 240, 0.6); }
        .tkt-side-title { font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0 0 24px 0; display: flex; align-items: center; gap: 10px; }
        
        .tkt-info-group { margin-bottom: 24px; }
        .tkt-info-label { font-size: 0.8rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px; display: block; }
        
        /* Stylish Badges */
        .tkt-badge { display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 10px; font-weight: 700; font-size: 0.9rem; letter-spacing: 0.02em; }
        .tkt-badge-open { background: #fffbeb; color: #b45309; border: 1px solid #fde68a; }
        .tkt-badge-in_progress { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
        .tkt-badge-answered { background: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .tkt-badge-closed { background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; }
        .tkt-badge-category { background: #f8fafc; color: #334155; border: 1px solid #cbd5e1; text-transform: capitalize; }

        .tkt-btn-resolve { width: 100%; background: white; color: #10b981; border: 2px solid #34d399; padding: 14px 20px; border-radius: 12px; font-weight: 800; font-family: 'Outfit', sans-serif; font-size: 1rem; cursor: pointer; transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 10px; margin-top: 16px; }
        .tkt-btn-resolve:hover { background: #f0fdf4; box-shadow: 0 4px 15px rgba(16, 185, 129, 0.15); transform: translateY(-2px); }

        /* Rating Card Premium */
        .tkt-rating-card { background: #ffffff; border-radius: 20px; padding: 40px; text-align: center; border: 1px solid #e2e8f0; box-shadow: 0 10px 40px -10px rgba(33, 82, 255, 0.08); position: relative; overflow: hidden; margin-top: 16px; }
        .tkt-rating-card::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 6px; background: linear-gradient(90deg, #2152ff 0%, #0ea5e9 100%); }
        .tkt-rating-title { font-family: 'Outfit', sans-serif; font-size: 1.5rem; font-weight: 800; color: #0f172a; margin: 0 0 8px; }
        .tkt-rating-desc { color: #64748b; font-size: 1rem; margin: 0 0 32px; font-weight: 500;}
        
        .tkt-stars { display: flex; justify-content: center; gap: 12px; flex-direction: row-reverse; }
        .tkt-stars input { display: none; }
        .tkt-stars label { cursor: pointer; color: #cbd5e1; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .tkt-stars label svg { width: 56px; height: 56px; fill: currentColor; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.02)); }
        .tkt-stars input:checked ~ label, .tkt-stars label:hover, .tkt-stars label:hover ~ label { color: #f59e0b; transform: scale(1.15) translateY(-4px); filter: drop-shadow(0 8px 15px rgba(245, 158, 11, 0.3)); }
        
        .tkt-btn-rate { background: white; color: #0f172a; border: 2px solid #e2e8f0; padding: 14px 40px; border-radius: 100px; font-weight: 800; font-family: 'Outfit', sans-serif; font-size: 1.05rem; cursor: pointer; transition: all 0.3s ease; margin-top: 32px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03); display: inline-flex; align-items: center; gap: 8px;}
        .tkt-btn-rate:hover { background: #f8fafc; border-color: #cbd5e1; transform: translateY(-2px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.06); }

        
        .tkt-alert { background: #f0fdf4; border-left: 5px solid #10b981; padding: 20px 24px; border-radius: 12px; margin-bottom: 32px; color: #166534; font-weight: 600; font-size: 1.05rem; display: flex; align-items: center; gap: 16px; box-shadow: 0 10px 30px -10px rgba(16, 185, 129, 0.2); }
        .tkt-alert svg { width: 28px; height: 28px; background: #dcfce7; padding: 4px; border-radius: 50%; color: #10b981; }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <?=view('partials/header_inner') ?>

    <main class="tkt-wrapper">
        <div class="tkt-container">
            <!-- Columna Izquierda: Mensajes -->
            <div>
                <a href="<?= site_url('tickets') ?>" class="tkt-back-link">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg>
                    Volver a Tickets
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

                <h1 class="tkt-h1">#<?= $ticket['id'] ?> - <?= esc($ticket['subject']) ?></h1>

                <div class="tkt-main" style="margin-top: 32px;">
                    <?php foreach($replies as $reply): ?>
                        <div class="tkt-msg-card <?= $reply['is_admin'] ? 'tkt-msg-admin' : '' ?>">
                            <div class="tkt-avatar <?= $reply['is_admin'] ? 'tkt-avatar-admin' : 'tkt-avatar-user' ?>">
                                <?= $reply['is_admin'] ? 'ST' : 'TÚ' ?>
                            </div>
                            <div class="tkt-msg-content">
                                <div class="tkt-msg-header">
                                    <div class="tkt-sender">
                                        <?= $reply['is_admin'] ? 'Soporte Técnico' : 'Tú' ?>
                                        <?php if($reply['is_admin']): ?>
                                            <span class="tkt-admin-badge">
                                                <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 2px;"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                                Staff
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="tkt-time"><?= date('d M Y, H:i', strtotime($reply['created_at'])) ?></div>
                                </div>
                                <div class="tkt-body"><?= nl2br(esc($reply['message'])) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <?php if($ticket['status'] !== 'closed'): ?>
                        <form action="<?= site_url('tickets/'.$ticket['id'].'/reply') ?>" method="POST">
                            <?= csrf_field() ?>
                            <div class="tkt-reply-card">
                                <div class="tkt-reply-header">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path></svg>
                                    Añadir una respuesta
                                </div>
                                <textarea name="message" class="tkt-textarea" placeholder="Describe tu consulta o añade más detalles aquí..." required></textarea>
                                <div class="tkt-reply-footer">
                                    <span style="font-size: 0.85rem; color: #94a3b8; font-weight: 500;">Soporta texto simple. Nos llegará al instante.</span>
                                    <button type="submit" class="tkt-btn-send">
                                        Enviar Mensaje
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php elseif($ticket['status'] === 'closed' && empty($ticket['rating'])): ?>
                        <div class="tkt-rating-card">
                            <h3 class="tkt-rating-title">¿Qué te ha parecido la atención recibida?</h3>
                            <p class="tkt-rating-desc">Tu valoración nos ayuda a mejorar continuamente el soporte de APIEmpresas.</p>
                            <form action="<?= site_url('tickets/'.$ticket['id'].'/rate') ?>" method="POST">
                                <?= csrf_field() ?>
                                <div class="tkt-stars">
                                    <input type="radio" id="star5" name="rating" value="5" /><label for="star5"><svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></label>
                                    <input type="radio" id="star4" name="rating" value="4" /><label for="star4"><svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></label>
                                    <input type="radio" id="star3" name="rating" value="3" /><label for="star3"><svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></label>
                                    <input type="radio" id="star2" name="rating" value="2" /><label for="star2"><svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></label>
                                    <input type="radio" id="star1" name="rating" value="1" /><label for="star1"><svg viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg></label>
                                </div>
                                <button type="submit" class="tkt-btn-rate">Enviar Valoración ⭐</button>
                            </form>
                        </div>
                    <?php elseif(!empty($ticket['rating'])): ?>
                         <div class="tkt-rating-card" style="background: #ffffff; border-color: #e2e8f0;">
                            <h3 class="tkt-rating-title" style="color: #0f172a; margin:0; display: flex; align-items: center; justify-content: center; gap: 12px;">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="#fbbf24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                ¡Gracias por tu valoración (<?= $ticket['rating'] ?>/5)!
                            </h3>
                            <p style="margin: 12px 0 0 0; color: #64748b; font-size: 0.95rem; font-weight: 500;">Esta solicitud se encuentra cerrada.</p>
                         </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Columna Derecha: Sidebar Sticky -->
            <div class="tkt-sidebar">
                <div class="tkt-side-card">
                    <h3 class="tkt-side-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                        Detalles del Ticket
                    </h3>
                    
                    <div class="tkt-info-group">
                        <span class="tkt-info-label">Estado Actual</span>
                        <?php 
                            $status_classes = ['open' => 'open', 'in_progress' => 'in_progress', 'answered' => 'answered', 'closed' => 'closed'];
                            $statuses = ['open' => 'Abierto', 'in_progress' => 'En Proceso', 'answered' => 'Respondido', 'closed' => 'Cerrado'];
                            $st_class = $status_classes[$ticket['status']] ?? 'open';
                            $st_label = $statuses[$ticket['status']] ?? $ticket['status'];
                        ?>
                        <div class="tkt-badge tkt-badge-<?= $st_class ?>">
                            <div style="width: 8px; height: 8px; border-radius: 50%; background: currentColor;"></div>
                            <?= $st_label ?>
                        </div>
                    </div>

                    <div class="tkt-info-group">
                        <span class="tkt-info-label">Tema Relacionado</span>
                        <div class="tkt-badge tkt-badge-category">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                            <?= esc(str_replace('_', ' ', $ticket['category'] ?? 'general')) ?>
                        </div>
                    </div>

                    <div class="tkt-info-group">
                        <span class="tkt-info-label">Prioridad</span>
                        <div class="tkt-badge" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 15s1-1 4-1 5 2 8 2 4-1 4-1V3s-1 1-4 1-5-2-8-2-4 1-4 1z"></path><line x1="4" y1="22" x2="4" y2="15"></line></svg>
                            <?php 
                                $priorities = ['low' => 'Baja', 'medium' => 'Media', 'high' => 'Alta', 'urgent' => 'Urgente'];
                                echo $priorities[$ticket['priority']] ?? 'Normal';
                            ?>
                        </div>
                    </div>

                    <?php if($ticket['status'] !== 'closed'): ?>
                        <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #f1f5f9;">
                            <form action="<?= site_url('tickets/'.$ticket['id'].'/close') ?>" method="POST" data-confirm="¿Confirmas que deseas dar por solucionado este ticket?">
                                <?= csrf_field() ?>
                                <button type="submit" class="tkt-btn-resolve">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                    Marcar como Resuelto
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<!-- Scripts globales manejan data-confirm ahora -->
</body>
</html>
