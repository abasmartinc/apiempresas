<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app' ) ?>

<?= $this->section('styles') ?>
<style>
    .profile-container {
        max-width: 1200px;
        margin: 0 auto;
        padding: 40px 20px;
        animation: fadeIn 0.5s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .profile-header-wrap {
        display: flex;
        align-items: center;
        gap: 24px;
        margin-bottom: 48px;
    }

    .profile-avatar-large {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #8b5cf6);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 32px;
        font-weight: 900;
        box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5);
    }

    .profile-header {
        flex: 1;
    }
    .profile-title {
        font-size: 32px;
        font-weight: 900;
        color: var(--ve-text);
        margin: 0 0 4px;
        letter-spacing: -0.5px;
    }
    .profile-subtitle {
        color: var(--ve-muted);
        font-size: 16px;
        margin: 0;
    }

    .profile-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(450px, 1fr));
        gap: 32px;
    }

    .wow-card {
        background: var(--ve-card);
        border: 1px solid rgba(226, 232, 240, 0.8);
        border-radius: 24px;
        padding: 40px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
    }
    
    .wow-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05), 0 10px 10px -5px rgba(0, 0, 0, 0.02);
        border-color: #cbd5e1;
    }

    /* Subtle gradient glow behind the card content */
    .wow-card::before {
        content: '';
        position: absolute;
        top: 0; right: 0;
        width: 150px; height: 150px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.05) 0%, transparent 70%);
        border-radius: 50%;
        transform: translate(30%, -30%);
        pointer-events: none;
    }

    .card-icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 24px;
        color: #3b82f6;
    }

    .profile-card-title {
        font-size: 20px;
        font-weight: 800;
        color: var(--ve-text);
        margin: 0 0 8px;
    }
    .profile-card-desc {
        font-size: 14px;
        color: var(--ve-muted);
        margin: 0 0 32px;
        line-height: 1.5;
    }
    
    .p-form-group {
        margin-bottom: 24px;
    }
    .p-form-label {
        display: block;
        font-size: 13px;
        font-weight: 700;
        color: #475569;
        margin-bottom: 8px;
    }
    .p-form-input {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 14px;
        font-size: 15px;
        color: var(--ve-text);
        background: #f8fafc;
        transition: all 0.3s ease;
        box-sizing: border-box;
    }
    .p-form-input:focus {
        outline: none;
        border-color: #3b82f6;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
    }
    
    .btn-wow {
        width: 100%;
        padding: 16px;
        border: none;
        border-radius: 14px;
        font-size: 15px;
        font-weight: 800;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
    }
    .btn-wow-primary {
        background: linear-gradient(135deg, #2563eb, #1d4ed8);
        color: #fff;
        box-shadow: 0 4px 14px 0 rgba(37, 99, 235, 0.39);
    }
    .btn-wow-primary:hover {
        box-shadow: 0 6px 20px rgba(37, 99, 235, 0.23);
        transform: translateY(-2px);
    }
    .btn-wow-secondary {
        background: #f1f5f9;
        color: #334155;
    }
    .btn-wow-secondary:hover {
        background: #e2e8f0;
        color: #0f172a;
    }

    .p-alert {
        padding: 16px 20px;
        border-radius: 14px;
        font-size: 14px;
        font-weight: 600;
        margin-bottom: 32px;
        display: flex;
        align-items: center;
        gap: 12px;
        animation: fadeIn 0.4s ease-out;
    }
    .p-alert-success {
        background: #ecfdf5;
        border: 1px solid #a7f3d0;
        color: #065f46;
    }
    .p-alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #991b1b;
    }

    @media (max-width: 768px) {
        .profile-grid { grid-template-columns: 1fr; }
        .profile-header-wrap { flex-direction: column; text-align: center; }
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container">
    <div class="profile-container">
        
        <div class="profile-header-wrap">
            <div class="profile-avatar-large">
                <?= strtoupper(substr($user->name ?? 'U', 0, 1)) ?>
            </div>
            <div class="profile-header">
                <h1 class="profile-title">Ajustes de Perfil</h1>
                <p class="profile-subtitle">Gestiona tu identidad, datos de contacto y seguridad de la cuenta.</p>
            </div>
        </div>

        <?php if (session()->has('message')): ?>
            <div class="p-alert p-alert-success">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                <?= esc(session('message')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->has('error')): ?>
            <div class="p-alert p-alert-error">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                <?= esc(session('error')) ?>
            </div>
        <?php endif; ?>

        <div class="profile-grid">
            
            <!-- Tarjeta de Datos Personales -->
            <div class="wow-card">
                <div class="card-icon-wrapper">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                </div>
                <h2 class="profile-card-title">Datos Personales</h2>
                <p class="profile-card-desc">Actualiza tu información básica de contacto y tu perfil profesional para el ecosistema.</p>
                
                <form action="<?= site_url('profile/update') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="p-form-group">
                        <label for="name" class="p-form-label">Nombre completo</label>
                        <input type="text" id="name" name="name" value="<?= esc($user->name) ?>" class="p-form-input" required>
                    </div>

                    <div class="p-form-group">
                        <label for="company" class="p-form-label">Empresa o Proyecto</label>
                        <input type="text" id="company" name="company" value="<?= esc($user->company) ?>" class="p-form-input">
                    </div>

                    <div class="p-form-group" style="margin-bottom: 32px;">
                        <label for="email" class="p-form-label">Correo electrónico</label>
                        <input type="email" id="email" name="email" value="<?= esc($user->email) ?>" class="p-form-input" required>
                    </div>

                    <button type="submit" class="btn-wow btn-wow-primary">
                        Guardar Cambios
                    </button>
                </form>
            </div>

            <!-- Tarjeta de Seguridad (Contraseña) -->
            <div class="wow-card">
                <div class="card-icon-wrapper" style="color: #8b5cf6; background: #f5f3ff;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                </div>
                <h2 class="profile-card-title">Seguridad y Acceso</h2>
                <p class="profile-card-desc">Modifica tu contraseña regularmente para mantener tu cuenta completamente protegida contra amenazas.</p>

                <?php if (session()->has('message_password')): ?>
                    <div class="p-alert p-alert-success" style="padding: 12px 16px; margin-bottom: 24px; font-size: 13px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                        <?= esc(session('message_password')) ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('error_password')): ?>
                    <div class="p-alert p-alert-error" style="padding: 12px 16px; margin-bottom: 24px; font-size: 13px;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
                        <?= esc(session('error_password')) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= site_url('profile/password') ?>" method="post">
                    <?= csrf_field() ?>
                    
                    <div class="p-form-group">
                        <label for="current_password" class="p-form-label">Contraseña actual</label>
                        <input type="password" id="current_password" name="current_password" class="p-form-input" required placeholder="••••••••••••">
                    </div>

                    <div class="p-form-group">
                        <label for="new_password" class="p-form-label">Nueva contraseña</label>
                        <input type="password" id="new_password" name="new_password" class="p-form-input" required placeholder="Mínimo 6 caracteres">
                    </div>

                    <div class="p-form-group" style="margin-bottom: 32px;">
                        <label for="confirm_password" class="p-form-label">Confirmar nueva contraseña</label>
                        <input type="password" id="confirm_password" name="confirm_password" class="p-form-input" required placeholder="Repite tu nueva contraseña">
                    </div>

                    <button type="submit" class="btn-wow btn-wow-secondary">
                        Actualizar Contraseña
                    </button>
                </form>
            </div>

        </div>
    </div>
</div>
<?= $this->endSection() ?>
