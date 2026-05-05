<header>
    <div class="container-admin nav">
        <div class="brand">
            <a href="<?=site_url() ?>">
                <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#2152FF"/>
                            <stop offset=".65" stop-color="#5C7CFF"/>
                            <stop offset="1" stop-color="#12B48A"/>
                        </linearGradient>
                        <filter id="ve-cardShadow" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20"/>
                        </filter>
                        <filter id="ve-checkShadow" x="-30%" y="-30%" width="160%" height="160%">
                            <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="#0B1A36" flood-opacity=".22"/>
                        </filter>
                        <radialGradient id="ve-gloss" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(20 16) rotate(45) scale(28)">
                            <stop stop-color="#FFFFFF" stop-opacity=".32"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity="0"/>
                        </radialGradient>
                        <linearGradient id="ve-rim" x1="12" y1="52" x2="52" y2="12">
                            <stop stop-color="#FFFFFF" stop-opacity=".6"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity=".35"/>
                        </linearGradient>
                    </defs>
                    <g filter="url(#ve-cardShadow)">
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)"/>
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss)"/>
                        <rect x="6.5" y="6.5" width="51" height="51" rx="13.5" fill="none" stroke="url(#ve-rim)"/>
                    </g>
                    <path d="M18 33 L28 43 L46 22"
                          stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"
                          fill="none" filter="url(#ve-checkShadow)"/>
                </svg>
            </a>

            <div class="brand-text">
                <span class="brand-name">API<span class="grad">Empresas</span>.es <span style="font-size: 0.7rem; background: #2152ff; color: white; padding: 2px 6px; border-radius: 4px; margin-left: 4px; vertical-align: middle; font-weight: 800;">ADMIN</span></span>
                <span class="brand-tag">Panel de Control Administrativo</span>
            </div>
        </div>

        <div class="desktop-only auth-buttons">
            <div class="user-dropdown-container">
                <button class="user-avatar-trigger" id="userMenuTrigger">
                    <?php if(session('user_avatar')): ?>
                        <img src="<?= session('user_avatar') ?>" alt="<?= esc(session('user_name')) ?>">
                    <?php else: ?>
                        <span class="user-avatar-initials"><?= strtoupper(substr(session('user_name') ?? 'A', 0, 1)) ?></span>
                    <?php endif; ?>
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 4px; opacity: 0.7;"><path d="m6 9 6 6 6-6"/></svg>
                </button>
                
                <div class="user-dropdown-menu" id="userDropdownMenu">
                    <div class="dropdown-header">
                        <span class="user-name-display"><?= esc(session('user_name')) ?></span>
                        <span class="user-email-display"><?= esc(session('user_email')) ?></span>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="<?= site_url('admin/dashboard') ?>" class="dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                        Admin Dashboard
                    </a>
                    <a href="<?= site_url('dashboard') ?>" class="dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        Ver como Usuario
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="#" id="btn-clear-cache" class="dropdown-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/><line x1="10" x2="10" y1="11" y2="17"/><line x1="14" x2="14" y1="11" y2="17"/></svg>
                        Limpiar Caché
                    </a>
                    <div class="dropdown-divider"></div>
                    <a href="<?= site_url('logout') ?>" class="dropdown-item logout-item">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                        Cerrar sesión
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<style>
    .auth-buttons { display: flex; align-items: center; gap: 14px; }
    
    /* User Dropdown Styles */
    .user-dropdown-container { position: relative; }
    .user-avatar-trigger {
        background: rgba(255, 255, 255, 0.1);
        border: 2px solid rgba(255, 255, 255, 0.2);
        padding: 4px 8px;
        border-radius: 99px;
        display: flex;
        align-items: center;
        gap: 8px;
        cursor: pointer;
        transition: all 0.3s;
        color: #ffffff;
    }
    .user-avatar-trigger:hover {
        background: rgba(255, 255, 255, 0.2);
        border-color: rgba(255, 255, 255, 0.4);
        transform: translateY(-1px);
    }
    .user-avatar-trigger img {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid rgba(255,255,255,0.2);
    }
    .user-avatar-initials {
        width: 32px;
        height: 32px;
        background: #ffffff;
        color: #2152ff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 14px;
    }

    .user-dropdown-menu {
        position: absolute;
        top: calc(100% + 12px);
        right: 0;
        width: 240px;
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border-radius: 18px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        padding: 8px;
        display: none;
        flex-direction: column;
        z-index: 1000;
        transform-origin: top right;
        animation: dropdownFade 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        border: 1px solid rgba(255, 255, 255, 0.5);
    }
    @keyframes dropdownFade {
        from { opacity: 0; transform: scale(0.95); }
        to { opacity: 1; transform: scale(1); }
    }
    .user-dropdown-menu.active { display: flex; }

    .dropdown-header { padding: 12px 16px; display: flex; flex-direction: column; }
    .user-name-display { font-size: 14px; font-weight: 800; color: #0f172a; display: block; }
    .user-email-display { font-size: 12px; color: #64748b; font-weight: 600; }
    
    .dropdown-divider { height: 1px; background: #f1f5f9; margin: 8px 0; }
    
    .dropdown-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 10px 16px;
        border-radius: 12px;
        color: #334155;
        text-decoration: none;
        font-size: 14px;
        font-weight: 700;
        transition: all 0.2s;
    }
    .dropdown-item:hover { background: #f8fafc; color: #2152ff; }
    .logout-item { color: #ef4444; }
    .logout-item:hover { background: #fef2f2; color: #ef4444; }
</style>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // User Dropdown
    const userTrigger = document.getElementById('userMenuTrigger');
    const userMenu = document.getElementById('userDropdownMenu');

    if (userTrigger && userMenu) {
        userTrigger.addEventListener('click', function(e) {
            e.stopPropagation();
            userMenu.classList.toggle('active');
        });

        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target) && !userTrigger.contains(e.target)) {
                userMenu.classList.remove('active');
            }
        });
    }

    // Clear Cache
    document.getElementById('btn-clear-cache')?.addEventListener('click', function(e) {
        e.preventDefault();
        userMenu.classList.remove('active');
        
        Swal.fire({
            title: '¿Limpiar Caché?',
            text: 'Esto regenerará los datos del Radar con los precios actuales de la base de datos.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, limpiar',
            cancelButtonText: 'Cancelar',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.showLoading();
                fetch('<?= site_url('admin/clear-cache') ?>', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.json())
                .then(data => {
                    Swal.fire({
                        title: data.status === 'success' ? '¡Hecho!' : 'Error',
                        text: data.message,
                        icon: data.status === 'success' ? 'success' : 'error'
                    });
                })
                .catch(err => {
                    Swal.fire('Error', 'No se pudo limpiar la caché', 'error');
                });
            }
        });
    });
});
</script>
