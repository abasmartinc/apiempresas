
    <header>
        <?php if (session('impersonator_id')): ?>
        <div style="background: #e0f2fe; border-bottom: 1px solid #bae6fd; padding: 10px; text-align: center; color: #0369a1; font-size: 0.9rem; font-weight: 500;">
            👀 Estás viendo el sitio como <strong><?= esc(session('user_name')) ?></strong>.
            <a href="<?= site_url('stop-impersonation') ?>" style="margin-left: 10px; text-decoration: underline; color: #0284c7; font-weight: 700;">Volver a Admin &rarr;</a>
        </div>
        <?php endif; ?>
        
        <div class="container nav">
            <div class="brand">
                <a href="<?=site_url() ?>" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
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
                    <div class="brand-text">
                        <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                        <span class="brand-tag">Verificación mercantil y Radar de empresas</span>
                    </div>
                </a>
            </div>

            <nav class="desktop-only" aria-label="Principal" style="display:flex; align-items:center;">
                <a class="minor-nav-link" href="<?=site_url() ?>dashboard">Dashboard</a>
                <span class="nav-sep">•</span>
                <a class="minor-nav-link" href="<?=site_url() ?>billing">Mi Plan</a>
                <span class="nav-sep">•</span>
                <a class="minor-nav-link" href="<?=site_url() ?>consumption">Consumo</a>
                <span class="nav-sep">•</span>
                <a class="minor-nav-link" href="<?=site_url() ?>documentation">Docs</a>
                <span class="nav-sep">•</span>
                <a class="minor-nav-link" href="<?=site_url() ?>search_company">Buscador</a>
            </nav>

            <div class="desktop-only auth-buttons">
                <?php if(!session('logged_in')): ?>
                    <a class="btn btn_header btn_header--ghost" href="<?=site_url() ?>enter">Iniciar sesión</a>
                <?php else: ?>
                    <div class="user-dropdown-container">
                        <button class="user-avatar-trigger" id="userMenuTrigger">
                            <?php if(session('user_avatar')): ?>
                                <img src="<?= session('user_avatar') ?>" alt="<?= esc(session('user_name')) ?>">
                            <?php else: ?>
                                <span class="user-avatar-initials"><?= strtoupper(substr(session('user_name') ?? 'U', 0, 1)) ?></span>
                            <?php endif; ?>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" style="margin-left: 4px; opacity: 0.7;"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        
                        <div class="user-dropdown-menu" id="userDropdownMenu">
                            <div class="dropdown-header">
                                <span class="user-name-display"><?= esc(session('user_name')) ?></span>
                                <span class="user-email-display"><?= esc(session('user_email')) ?></span>
                            </div>
                            <div class="dropdown-divider"></div>
                            <a href="<?= site_url('dashboard') ?>" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                                Mi Dashboard
                            </a>
                            <a href="<?= site_url('billing') ?>" class="dropdown-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10"/></svg>
                                Mi Plan
                            </a>
                            <div class="dropdown-divider"></div>
                            <a href="<?= site_url('logout') ?>" class="dropdown-item logout-item">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                                Cerrar sesión
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <button class="mobile-menu-toggle" aria-label="Abrir menú" aria-expanded="false">
                <span class="hamburger-box">
                    <span class="hamburger-inner"></span>
                </span>
            </button>
        </div>

        <div class="mobile-menu-overlay" id="mobileMenuInner">
            <div class="mobile-menu-content">
                <div class="mobile-menu-header">
                    <span class="brand-name">API<span class="grad">Empresas</span></span>
                    <button class="mobile-menu-close" aria-label="Cerrar menú">&times;</button>
                </div>
                <nav class="mobile-nav">
                    <a href="<?=site_url() ?>dashboard" class="mobile-nav-link">Dashboard</a>
                    <a href="<?=site_url() ?>billing" class="mobile-nav-link">Mi Plan</a>
                    <a href="<?=site_url() ?>consumption" class="mobile-nav-link">Consumo</a>
                    <a href="<?=site_url() ?>documentation" class="mobile-nav-link">Documentación</a>
                    <a href="<?=site_url() ?>search_company" class="mobile-nav-link">Buscador</a>
                    <div class="mobile-auth">
                        <?php if(!session('logged_in')): ?>
                            <a href="<?=site_url() ?>enter" class="btn btn-full ghost">Iniciar sesión</a>
                        <?php else: ?>
                            <div style="display: flex; align-items: center; gap: 12px; margin-bottom: 20px; padding: 0 10px;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; overflow: hidden; border: 2px solid #f1f5f9; background: #f8fafc; display: flex; align-items: center; justify-content: center;">
                                    <?php if(session('user_avatar')): ?>
                                        <img src="<?= session('user_avatar') ?>" alt="User" style="width: 100%; height: 100%; object-fit: cover;">
                                    <?php else: ?>
                                        <span style="color: #64748b; font-weight: 800; font-size: 16px;"><?= strtoupper(substr(session('user_name') ?? 'U', 0, 1)) ?></span>
                                    <?php endif; ?>
                                </div>
                                <div style="display: flex; flex-direction: column;">
                                    <span style="font-size: 14px; font-weight: 800; color: #0f172a;"><?= esc(session('user_name')) ?></span>
                                    <span style="font-size: 12px; color: #64748b; font-weight: 600;">Sesión activa</span>
                                </div>
                            </div>
                            <a href="<?=site_url() ?>logout" class="btn btn-full ghost">Cerrar sesión</a>
                        <?php endif; ?>
                    </div>
                </nav>
            </div>
        </div>
    </header>

    <style>
        .mobile-menu-toggle { display: none; background: transparent; border: none; padding: 8px; cursor: pointer; z-index: 100; }
        .hamburger-box { width: 24px; height: 18px; display: inline-block; position: relative; }
        .hamburger-inner, .hamburger-inner::before, .hamburger-inner::after { 
            width: 22px; 
            height: 2.5px; 
            background-color: #ffffff !important; /* Force white for visibility */
            position: absolute; 
            transition: all 0.2s ease-in-out; 
            border-radius: 4px; 
        }
        .hamburger-inner { top: 50%; transform: translateY(-50%); }
        .hamburger-inner::before { content: ""; top: -7px; }
        .hamburger-inner::after { content: ""; top: 7px; }

        .brand-name { 
            font-weight: 950 !important; 
            letter-spacing: -0.02em !important; 
            color: #ffffff !important; 
            display: flex;
            align-items: center;
        }
        .brand-name .grad {
            background: none !important;
            background-color: transparent !important;
            -webkit-background-clip: unset !important;
            -webkit-text-fill-color: initial !important;
            color: inherit !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .brand-tag { color: rgba(255,255,255,0.7) !important; }

        .mobile-menu-overlay { 
            position: fixed; 
            inset: 0; 
            background: rgba(15, 23, 42, 0.95) !important; 
            z-index: 9999; 
            opacity: 0; 
            visibility: hidden; 
            transition: opacity 0.3s ease, visibility 0.3s; 
        }
        .mobile-menu-overlay.active { opacity: 1 !important; visibility: visible !important; }
        .mobile-menu-content { 
            position: absolute; 
            right: -320px; 
            top: 0; 
            bottom: 0; 
            width: 300px; 
            background-color: #ffffff !important; 
            background: #ffffff !important; 
            opacity: 1 !important;
            box-shadow: -10px 0 30px rgba(0, 0, 0, 0.2); 
            transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1); 
            display: flex; 
            flex-direction: column; 
            padding: 24px; 
            z-index: 10000;
            visibility: visible !important;
            height: 100vh;
            overflow-y: auto;
        }
        .mobile-menu-overlay.active .mobile-menu-content { right: 0; }
        .mobile-menu-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9; }
        
        /* Override brand-name color for mobile menu header */
        .mobile-menu-header .brand-name {
            color: #0f172a !important;
        }
        
        .mobile-menu-header .brand-name .grad {
            color: #0f172a !important;
        }
        
        .mobile-menu-close { background: #f1f5f9; border: none; width: 40px; height: 40px; border-radius: 14px; font-size: 28px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #475569; }
        .mobile-nav { display: flex; flex-direction: column; gap: 6px; background-color: #ffffff; padding: 0; }
        .mobile-nav-link { padding: 14px 18px; border-radius: 14px; color: #334155; font-weight: 700; text-decoration: none; font-size: 16px; transition: all 0.2s; display: flex; justify-content: space-between; align-items: center; }
        .mobile-nav-link:hover { background: #f8fafc; color: var(--primary); }
        .mobile-auth { margin-top: 24px; padding-top: 24px; border-top: 1px solid #f1f5f9; display: flex; flex-direction: column; gap: 12px; }
        .btn-full { width: 100%; justify-content: center; padding: 16px !important; border-radius: 14px !important; font-weight: 900 !important; }

        .minor-nav-link { 
            color: #ffffff !important; 
            font-weight: 700; 
            font-size: 14px; 
            text-decoration: none; 
            transition: all 0.2s; 
            padding: 6px 4px; 
            opacity: 0.9;
        }
        .minor-nav-link:hover { opacity: 1; transform: translateY(-1px); }
        .nav-sep { margin: 0 10px; color: #ffffff; font-size: 10px; opacity: 0.4; }
        .auth-buttons { display: flex; align-items: center; gap: 14px; }
        .auth-buttons .btn { padding: 10px 20px !important; font-size: 14px !important; border-radius: 12px !important; }
        .auth-buttons .btn_header--ghost { color: #ffffff !important; border-color: rgba(255,255,255,0.3) !important; }
        .auth-buttons .btn_header--ghost:hover { background: rgba(255,255,255,0.1); }

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

        @media (max-width: 1024px) {
            .desktop-only { display: none !important; }
            .mobile-menu-toggle { display: block; }
            .brand-tag { display: none; }
            .brand-name { font-size: 1.15rem; }
            .nav { padding: 12px 16px; }
        }
    </style>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mobile Menu
            const toggle = document.querySelector('.mobile-menu-toggle');
            const close = document.querySelector('.mobile-menu-close');
            const overlay = document.querySelector('.mobile-menu-overlay');
            const body = document.body;

            function openMenu() { overlay.classList.add('active'); body.style.overflow = 'hidden'; }
            function closeMenu() { overlay.classList.remove('active'); body.style.overflow = ''; }

            if(toggle) toggle.addEventListener('click', openMenu);
            if(close) close.addEventListener('click', closeMenu);
            if(overlay) overlay.addEventListener('click', function(e) { if (e.target === overlay) closeMenu(); });
            document.querySelectorAll('.mobile-nav-link').forEach(link => { link.addEventListener('click', closeMenu); });

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
        });
    </script>
