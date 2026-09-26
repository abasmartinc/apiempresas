
<script>
<?php if (!empty($force_public_header)): ?>
    // En páginas con cache pública, no sobreescribir localStorage hasta que responda el AJAX
<?php elseif (!session('logged_in')): ?>
    if (typeof localStorage !== 'undefined') localStorage.removeItem('is_logged_in');
<?php else: ?>
    if (typeof localStorage !== 'undefined') localStorage.setItem('is_logged_in', '1');
<?php endif; ?>

if (typeof localStorage !== 'undefined' && localStorage.getItem('is_logged_in') === '1') {
    document.write('<style>.header-public-item { display: none !important; } .header-private-item { display: inline-flex !important; }</style>');
}
</script>
<header class="main-site-header">
    <?php if (session('impersonator_id')): ?>
        <div
            style="background: #e0f2fe; border-bottom: 1px solid #bae6fd; padding: 10px; text-align: center; color: #0369a1; font-size: 0.9rem; font-weight: 500;">
            👀 Estás viendo el sitio como <strong><?= esc(session('user_name')) ?></strong>.
            <a href="<?= site_url('stop-impersonation') ?>"
                style="margin-left: 10px; text-decoration: underline; color: #0284c7; font-weight: 700;">Volver a Admin
                &rarr;</a>
        </div>
    <?php endif; ?>

    <div class="container nav">
        <div class="brand">
            <a href="<?= site_url() ?>" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
                <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true"
                    xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#2152FF" />
                            <stop offset=".65" stop-color="#5C7CFF" />
                            <stop offset="1" stop-color="#12B48A" />
                        </linearGradient>
                        <filter id="ve-cardShadow" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20" />
                        </filter>
                        <filter id="ve-checkShadow" x="-30%" y="-30%" width="160%" height="160%">
                            <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="#0B1A36" flood-opacity=".22" />
                        </filter>
                        <radialGradient id="ve-gloss" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                            gradientTransform="translate(20 16) rotate(45) scale(28)">
                            <stop stop-color="#FFFFFF" stop-opacity=".32" />
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity="0" />
                        </radialGradient>
                        <linearGradient id="ve-rim" x1="12" y1="52" x2="52" y2="12">
                            <stop stop-color="#FFFFFF" stop-opacity=".6" />
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity=".35" />
                        </linearGradient>
                    </defs>
                    <g filter="url(#ve-cardShadow)">
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)" />
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss)" />
                        <rect x="6.5" y="6.5" width="51" height="51" rx="13.5" fill="none" stroke="url(#ve-rim)" />
                    </g>
                    <path d="M18 33 L28 43 L46 22" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round"
                        stroke-linejoin="round" fill="none" filter="url(#ve-checkShadow)" />
                </svg>
                <div class="brand-text">
                    <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                    <span class="brand-tag">La suite de inteligencia empresarial</span>
                </div>
            </a>
        </div>

        <style>
            .nav-dropdown-mega .nav-mega-col { display: flex; flex-direction: column; gap: 4px; }
            .nav-dropdown-mega .nav-mega-sub { margin: 10px 0 2px 10px; font-size: 0.7rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: #94a3b8; }
            .nav-dropdown-mega a.nav-compact { padding: 5px 10px; }
            .nav-dropdown-mega a.nav-compact strong { font-size: 13px; font-weight: 600; color: #475569; }
            .nav-dropdown-mega a.nav-compact:hover strong { color: #0f172a; }
        </style>

        <!-- Programmatic Navigation (Desktop) -->
        <nav class="desktop-only" aria-label="Principal" style="display:flex; align-items:center; gap: 20px;">
            
            <div class="nav-dropdown">
                <button class="nav-dropdown-trigger">
                    Soluciones
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </button>
                <div class="nav-dropdown-menu nav-dropdown-mega">
                    <div class="nav-mega-col">
                        <h4 style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin: 0 0 6px 10px;">Productos</h4>
                        <a href="<?= site_url() ?>">
                            <div class="nav-item-icon">🔌</div>
                            <div>
                                <strong>API de empresas</strong>
                                <span>Valida CIF y consulta datos del Registro Mercantil y el BORME.</span>
                            </div>
                        </a>
                        <a href="<?= site_url('api-empresas') ?>">
                            <div class="nav-item-icon">✅</div>
                            <div>
                                <strong>API KYB</strong>
                                <span>Verifica NIF, estado y administradores en el alta de clientes.</span>
                            </div>
                        </a>
                        <a href="<?= site_url('perfil-de-riesgo') ?>">
                            <div class="nav-item-icon">🛡️</div>
                            <div>
                                <strong style="display: flex; align-items: center; gap: 6px;">Perfil de Riesgo <b style="background: #2563eb; color: white; font-size: 8px; font-weight: 800; padding: 2px 5px; border-radius: 4px; letter-spacing: 0.5px; font-style: normal; line-height: 1;">NUEVO</b></strong>
                                <span>Consulta scoring de solvencia, alertas BORME y riesgo comercial.</span>
                            </div>
                        </a>
                        <div class="nav-mega-sub">Otras herramientas</div>
                        <a class="nav-compact" href="<?= site_url('encontrar-empresas-similares') ?>"><strong>Empresas Gemelas</strong></a>
                        <a class="nav-compact" href="<?= getRadarRedirect('header') ?>"><strong>Radar Inteligente</strong></a>
                        <a class="nav-compact" href="https://vertice.apiempresas.es" target="_blank" rel="noopener"><strong>Vértice</strong></a>
                    </div>
                    <div class="nav-mega-col">
                        <h4 style="font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.05em; color: #64748b; margin: 0 0 6px 10px;">Integraciones</h4>
                        <a href="<?= site_url('plugin-wordpress-buscador-empresas') ?>">
                            <div class="nav-item-icon">
                                <img src="<?= base_url('public/img/logos/wordpress.svg') ?>" width="18" height="18" alt="WordPress" style="display: block;">
                            </div>
                            <div>
                                <strong style="display: flex; align-items: center; gap: 6px;">Plugin WordPress <b style="background: #10b981; color: white; font-size: 8px; font-weight: 800; padding: 2px 5px; border-radius: 4px; letter-spacing: 0.5px; font-style: normal; line-height: 1;">DISPONIBLE</b></strong>
                                <span>Buscador B2B para convertir tu web en imán de leads corporativos.</span>
                            </div>
                        </a>
                        <a href="<?= site_url('integraciones/google-sheets') ?>">
                            <div class="nav-item-icon">
                                <img src="<?= base_url('public/img/logos/googlesheets.svg') ?>" width="18" height="18" alt="Google Sheets" style="display: block;">
                            </div>
                            <div>
                                <strong style="display: flex; align-items: center; gap: 6px;">Extensión Google Sheets <b style="background: #10b981; color: white; font-size: 8px; font-weight: 800; padding: 2px 5px; border-radius: 4px; letter-spacing: 0.5px; font-style: normal; line-height: 1;">DISPONIBLE</b></strong>
                                <span>Sincroniza y enriquece BBDD masivamente desde tus hojas de cálculo.</span>
                            </div>
                        </a>
                        <a href="#" class="js-track-wp-cta">
                            <div class="nav-item-icon">
                                <img src="<?= base_url('public/img/logos/zapier.svg') ?>" width="18" height="18" alt="Zapier" style="display: block;">
                            </div>
                            <div>
                                <strong style="display: flex; align-items: center; gap: 6px;">App Zapier / Make <b style="background: #f1f5f9; color: #64748b; font-size: 8px; font-weight: 800; padding: 2px 5px; border-radius: 4px; letter-spacing: 0.5px; font-style: normal; line-height: 1;">PRÓXIMAMENTE</b></strong>
                                <span>Automatiza conectando nuestra API con miles de herramientas.</span>
                            </div>
                        </a>
                        <a href="#" class="js-track-wp-cta">
                            <div class="nav-item-icon">
                                <img src="<?= base_url('public/img/logos/shopify.svg') ?>" width="18" height="18" alt="Shopify" style="display: block;">
                            </div>
                            <div>
                                <strong style="display: flex; align-items: center; gap: 6px;">Plugin Shopify B2B <b style="background: #f1f5f9; color: #64748b; font-size: 8px; font-weight: 800; padding: 2px 5px; border-radius: 4px; letter-spacing: 0.5px; font-style: normal; line-height: 1;">PRÓXIMAMENTE</b></strong>
                                <span>Valida CIF y solvencia financiera en el checkout B2B.</span>
                            </div>
                        </a>
                    </div>
                </div>
            </div>

            <div class="nav-dropdown">
                <button class="nav-dropdown-trigger">
                    Listados
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m6 9 6 6 6-6" />
                    </svg>
                </button>
                <div class="nav-dropdown-menu">
                    <a href="<?= site_url('listado-de-empresas') ?>">
                        <div class="nav-item-icon">🗂️</div>
                        <div>
                            <strong>Directorio Histórico</strong>
                            <span>Explora el directorio B2B completo.</span>
                        </div>
                    </a>
                    <a href="<?= site_url('listado-de-grupos-empresariales') ?>">
                        <div class="nav-item-icon">🕸️</div>
                        <div>
                            <strong>Grupos y Holdings</strong>
                            <span>Descubre 134.000 entramados societarios.</span>
                        </div>
                    </a>
                    <a href="<?= site_url('base-de-datos-de-empresas') ?>">
                        <div class="nav-item-icon">📊</div>
                        <div>
                            <strong>Descargas de BBDD</strong>
                            <span>Filtra y exporta listados B2B al instante.</span>
                        </div>
                    </a>
                    <a href="<?= site_url('subvenciones-empresas') ?>">
                        <div class="nav-item-icon">💰</div>
                        <div>
                            <strong>Subvenciones Públicas</strong>
                            <span>Explora subvenciones adjudicadas.</span>
                        </div>
                    </a>
                    <a href="<?= site_url('licitaciones-del-estado') ?>">
                        <div class="nav-item-icon">🏛️</div>
                        <div>
                            <strong>Licitaciones y Contratos</strong>
                            <span>Contratos públicos del Estado.</span>
                        </div>
                    </a>
                </div>
            </div>

            <a class="minor-nav-link" href="<?= site_url() ?>#precios" data-track-event="nav_pricing_click" data-track-metadata='{"source_block": "header"}'>Precios</a>
            <a class="minor-nav-link" href="<?= site_url('documentation') ?>">Docs</a>
        </nav>

        <div class="desktop-only auth-buttons" style="position: relative;">
            <?php if (!session('logged_in') || !empty($force_public_header)): ?>
                <a class="btn btn_header btn_header--ghost header-public-item" href="<?= site_url() ?>enter">Iniciar sesión</a>
                <a class="btn btn_header btn_header--primary header-public-item" href="<?= site_url('register') . (uri_string() === '' ? '?intent=api&source=home_header' : '') ?>">Crear cuenta gratis</a>
                <a class="btn btn_header btn_header--primary header-private-item" style="display: none;" href="<?= site_url('dashboard') ?>">Dashboard</a>
                <a class="btn btn_header btn_header--ghost header-private-item" style="display: none; padding: 8px 12px;" href="<?= site_url('logout') ?>" title="Cerrar sesión">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                </a>
            <?php else: ?>
                <a class="btn btn_header btn_header--primary" href="<?= site_url('dashboard') ?>">Dashboard</a>
                <div class="user-dropdown-container">
                    <button class="user-avatar-trigger" id="userMenuTrigger">
                        <?php
                        $fullName = session('user_name') ?? 'Usuario';
                        $firstName = explode(' ', trim($fullName))[0];
                        ?>
                        <div class="user-avatar-wrapper" style="display: flex; align-items: center; gap: 8px;">
                            <?php if (session('user_avatar')): ?>
                                <img src="<?= session('user_avatar') ?>" alt="<?= esc($fullName) ?>" style="margin-left: 0;">
                            <?php else: ?>
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.9;">
                                    <path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2" />
                                    <circle cx="12" cy="7" r="4" />
                                </svg>
                            <?php endif; ?>
                        </div>
                        <span class="user-nav-name"><?= esc($firstName) ?></span>
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                            stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.6; margin-left: -2px;">
                            <path d="m6 9 6 6 6-6" />
                        </svg>
                    </button>

                    <div class="user-dropdown-menu" id="userDropdownMenu">
                        <div class="dropdown-header">
                            <span class="user-name-display"><?= esc(session('user_name')) ?></span>
                            <span class="user-email-display"><?= esc(session('user_email')) ?></span>
                        </div>
                        <div class="dropdown-divider"></div>
                        <?php
                        $showFullMenu = true;
                        if (session('logged_in')) {
                            $db = \Config\Database::connect();
                            $activePlans = $db->table('user_subscriptions')
                                ->select('plan_id')
                                ->where('user_id', session('user_id'))
                                ->where('status', 'active')
                                ->get()->getResultArray();
                                
                            if (!empty($activePlans)) {
                                $hasOnlyCopilot = true;
                                foreach ($activePlans as $plan) {
                                    if ($plan['plan_id'] != 13) {
                                        $hasOnlyCopilot = false;
                                        break;
                                    }
                                }
                                if ($hasOnlyCopilot) {
                                    $showFullMenu = false; // Only copilot plan
                                }
                            }
                        }
                        if ($showFullMenu):
                        ?>
                        <a href="<?= site_url('dashboard') ?>" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="7" height="9" x="3" y="3" rx="1" />
                                <rect width="7" height="5" x="14" y="3" rx="1" />
                                <rect width="7" height="9" x="14" y="12" rx="1" />
                                <rect width="7" height="5" x="3" y="16" rx="1" />
                            </svg>
                            Mi Dashboard
                        </a>
                        <a href="<?= site_url('tickets') ?>" class="dropdown-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path
                                    d="M2 9a3 3 0 0 1 0 6v2a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-2a3 3 0 0 1 0-6V7a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2Z" />
                            </svg>
                            Mis tickets
                        </a>
                        <div class="dropdown-divider"></div>
                        <?php endif; ?>
                        <a href="<?= site_url('logout') ?>" class="dropdown-item logout logout-item">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                                <polyline points="16 17 21 12 16 7" />
                                <line x1="21" x2="9" y1="12" y2="12" />
                            </svg>
                            Cerrar sesión
                        </a>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Hamburger Button (Mobile) -->
        <button class="mobile-menu-toggle" aria-label="Abrir menú" aria-expanded="false">
            <span class="hamburger-box">
                <span class="hamburger-inner"></span>
            </span>
        </button>
    </div>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenu">
        <div class="mobile-menu-content">
            <div class="mobile-menu-header">
                <span class="brand-name">API<span class="grad">Empresas</span></span>
                <button class="mobile-menu-close" aria-label="Cerrar menú">&times;</button>
            </div>
            <nav class="mobile-nav">
                <div class="mobile-nav-group" style="margin-bottom: 16px;">
                    <a href="<?= site_url() ?>#precios" class="mobile-nav-link" data-track-event="nav_pricing_click" data-track-metadata='{"source_block": "mobile_header"}'>Precios</a>
                    <a href="<?= site_url('documentation') ?>" class="mobile-nav-link">Docs</a>
                </div>
                <div class="mobile-nav-group">
                    <div class="mobile-nav-label">Soluciones</div>
                    
                    <div style="padding: 12px 18px 4px; font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #94a3b8; letter-spacing: 0.05em;">Productos</div>
                    <a href="<?= site_url() ?>" class="mobile-nav-link">
                        <span>API de empresas</span>
                    </a>
                    <a href="<?= site_url('api-empresas') ?>" class="mobile-nav-link">
                        <span>API KYB</span>
                    </a>
                    <a href="<?= site_url('perfil-de-riesgo') ?>" class="mobile-nav-link">
                        <span>Perfil de Riesgo</span>
                    </a>

                    <div style="padding: 16px 18px 4px; font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #94a3b8; letter-spacing: 0.05em;">Otras herramientas</div>
                    <a href="<?= site_url('encontrar-empresas-similares') ?>" class="mobile-nav-link"><span>Empresas Gemelas</span></a>
                    <a href="<?= getRadarRedirect('mobile_header') ?>" class="mobile-nav-link"><span>Radar Inteligente</span></a>
                    <a href="https://vertice.apiempresas.es" target="_blank" rel="noopener" class="mobile-nav-link"><span>Vértice</span></a>

                    <div style="padding: 16px 18px 4px; font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #94a3b8; letter-spacing: 0.05em;">Integraciones</div>
                    <a href="<?= site_url('plugin-wordpress-buscador-empresas') ?>" class="mobile-nav-link"><span>Plugin WordPress</span></a>
                    <a href="<?= site_url('integraciones/google-sheets') ?>" class="mobile-nav-link"><span>Google Sheets</span></a>
                    <a href="#" class="mobile-nav-link js-track-wp-cta"><span>Zapier / Make</span></a>
                    <a href="#" class="mobile-nav-link js-track-wp-cta"><span>Shopify B2B</span></a>
                </div>

                <div class="mobile-nav-group" style="margin-top: 16px;">
                    <div class="mobile-nav-label">Listados</div>
                    <a href="<?= site_url('listado-de-empresas') ?>" class="mobile-nav-link">
                        <span>Directorio Histórico</span>
                    </a>
                    <a href="<?= site_url('listado-de-grupos-empresariales') ?>" class="mobile-nav-link">
                        <span>Grupos y Holdings</span>
                    </a>
                    <a href="<?= site_url('base-de-datos-de-empresas') ?>" class="mobile-nav-link">
                        <span>Descarga de Listados</span>
                    </a>
                    <a href="<?= site_url('subvenciones-empresas') ?>" class="mobile-nav-link">
                        <span>Subvenciones Públicas</span>
                    </a>
                    <a href="<?= site_url('licitaciones-del-estado') ?>" class="mobile-nav-link">
                        <span>Licitaciones y Contratos</span>
                    </a>
                </div>

                <div class="mobile-auth">
                    <?php if (!session('logged_in') || !empty($force_public_header)): ?>
                        <a href="<?= site_url() ?>enter" class="btn btn-full ghost header-public-item">Iniciar sesión</a>
                        <a href="<?= site_url('register') . (uri_string() === '' ? '?intent=api&source=home_header' : '') ?>" class="btn btn-full primary header-public-item">Crear cuenta gratis</a>
                        <a href="<?= site_url('dashboard') ?>" class="btn btn-full ghost header-private-item" style="display: none;">Dashboard</a>
                        <a href="<?= site_url('logout') ?>" class="btn btn-full ghost logout header-private-item" style="display: none;">Salir</a>
                    <?php else: ?>
                        <a href="<?= site_url('dashboard') ?>" class="btn btn-full ghost">Dashboard</a>
                        <a href="<?= site_url('logout') ?>" class="btn btn-full ghost logout">Salir</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </div>
</header>



<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.querySelector('.mobile-menu-toggle');
        const close = document.querySelector('.mobile-menu-close');
        const overlay = document.querySelector('.mobile-menu-overlay');
        const body = document.body;

        function openMenu() {
            overlay.classList.add('active');
            body.style.overflow = 'hidden';
            toggle.setAttribute('aria-expanded', 'true');
        }

        function closeMenu() {
            overlay.classList.remove('active');
            body.style.overflow = '';
            toggle.setAttribute('aria-expanded', 'false');
        }

        toggle.addEventListener('click', openMenu);
        close.addEventListener('click', closeMenu);
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) closeMenu();
        });
        // Enlaces a una sección de la misma página (p. ej. Precios en la home): cerrar el menú
        overlay.querySelectorAll('a[href*="#"]').forEach(function (a) {
            a.addEventListener('click', function () {
                try {
                    var u = new URL(a.href, location.href);
                    if (u.pathname === location.pathname && u.hash) closeMenu();
                } catch (e) {}
            });
        });

        // User Dropdown
        const userTrigger = document.getElementById('userMenuTrigger');
        const userMenu = document.getElementById('userDropdownMenu');

        if (userTrigger && userMenu) {
            userTrigger.addEventListener('click', function (e) {
                e.stopPropagation();
                userMenu.classList.toggle('active');
            });

            document.addEventListener('click', function (e) {
                if (!userMenu.contains(e.target) && !userTrigger.contains(e.target)) {
                    userMenu.classList.remove('active');
                }
            });
        }

        // Close on link click
        document.querySelectorAll('.mobile-nav-link').forEach(link => {
            link.addEventListener('click', closeMenu);
        });

        // Logout Confirmation Logic (Global)
        const logoutLinks = document.querySelectorAll('.logout');
        if (logoutLinks.length > 0) {
            logoutLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    e.preventDefault();
                    const targetUrl = this.getAttribute('href') || '/logout';

                    // Check if Swal is available (loaded in head or footer of page)
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            title: '¿Cerrar sesión?',
                            html: 'Se cerrará tu sesión en <strong>APIEmpresas.es</strong> y volverás a la pantalla de acceso.',
                            icon: null,
                            iconHtml: '<span class="ve-swal-icon-inner">✓</span>',
                            showCancelButton: true,
                            confirmButtonText: 'Sí, cerrar sesión',
                            cancelButtonText: 'Cancelar',
                            reverseButtons: true,
                            focusCancel: true,
                            customClass: {
                                popup: 've-swal',
                                title: 've-swal-title',
                                htmlContainer: 've-swal-text',
                                confirmButton: 'btn ve-swal-confirm',
                                cancelButton: 'btn btn_header--ghost ve-swal-cancel',
                                icon: 've-swal-icon'
                            },
                            buttonsStyling: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = targetUrl;
                            }
                        });
                    } else {
                        // Fallback if Swal not present
                        window.location.href = targetUrl;
                    }
                });
            });
        }
    });
</script>

<?= view('partials/wp_coming_soon_modal') ?>

