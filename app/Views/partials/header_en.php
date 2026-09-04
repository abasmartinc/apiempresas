<header class="main-site-header">
    <div class="container nav" style="display: flex; justify-content: space-between; align-items: center; padding: 16px 0;">
        <div class="brand">
            <a href="/" style="display: flex; align-items: center; gap: 12px; text-decoration: none;">
                <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="ve-g-en" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#2152FF" />
                            <stop offset=".65" stop-color="#5C7CFF" />
                            <stop offset="1" stop-color="#12B48A" />
                        </linearGradient>
                        <filter id="ve-cardShadow-en" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20" />
                        </filter>
                        <radialGradient id="ve-gloss-en" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse" gradientTransform="translate(20 16) rotate(45) scale(28)">
                            <stop stop-color="#FFFFFF" stop-opacity=".32" />
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity="0" />
                        </radialGradient>
                    </defs>
                    <g filter="url(#ve-cardShadow-en)">
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g-en)" />
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss-en)" />
                    </g>
                    <path d="M18 33 L28 43 L46 22" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                </svg>
                <div class="brand-text" style="display: flex; flex-direction: column;">
                    <span class="brand-name" style="color: #fff; font-weight: 900; font-size: 1.25rem; letter-spacing: -0.02em; line-height: 1;">SpainCompanyAPI</span>
                    <span class="brand-tag" style="font-size: 0.75rem; color: rgba(255, 255, 255, 0.7); font-weight: 500; margin-top: 4px;">Spanish Company Data API</span>
                </div>
            </a>
        </div>

        <nav class="desktop-only" aria-label="Main" style="display:flex; align-items:center; gap: 24px;">
            <a href="/spanish-company-data-api" style="color: rgba(255,255,255,0.85); font-weight: 600; text-decoration: none; font-size: 0.95rem; transition: color 0.2s;">API</a>
            <span style="color: rgba(255,255,255,0.2);">|</span>
            <a href="/docs" style="color: rgba(255,255,255,0.85); font-weight: 600; text-decoration: none; font-size: 0.95rem; transition: color 0.2s;">Documentation</a>
        </nav>

        <div class="desktop-only auth-buttons" style="display: flex; gap: 12px;">
            <?php if (!session('logged_in')): ?>
                <a class="btn" href="/enter" style="color: #fff; text-decoration: none; font-weight: 700; font-size: 0.9rem; padding: 10px 16px; border-radius: 99px; background: rgba(255,255,255,0.1);">Log In</a>
                <a class="btn" href="/register?intent=api" style="color: #0f172a; background: #FACC15; text-decoration: none; font-weight: 800; font-size: 0.9rem; padding: 10px 20px; border-radius: 99px; box-shadow: 0 4px 14px 0 rgba(250,204,21,0.25);">Get API Key</a>
            <?php else: ?>
                <a href="/dashboard" class="btn" style="color: #fff; text-decoration: none; font-weight: 700; font-size: 0.9rem; padding: 10px 16px; border-radius: 99px; background: rgba(255,255,255,0.1);">Dashboard</a>
            <?php endif; ?>
        </div>

        <!-- Hamburger Button (Mobile) -->
        <button class="mobile-menu-toggle" aria-label="Open menu" aria-expanded="false" style="display: none;">
            <span class="hamburger-box">
                <span class="hamburger-inner"></span>
            </span>
        </button>
    </div>

    <!-- Mobile Menu Overlay -->
    <div class="mobile-menu-overlay" id="mobileMenu">
        <div class="mobile-menu-content">
            <div class="mobile-menu-header" style="align-items: flex-start;">
                <div class="brand-text" style="display: flex; flex-direction: column;">
                    <span class="brand-name" style="color: #0f172a; font-weight: 900; line-height: 1;">SpainCompanyAPI</span>
                    <span class="brand-tag" style="font-size: 0.75rem; color: #64748b; font-weight: 500; margin-top: 4px;">Spanish Company Data API</span>
                </div>
                <button class="mobile-menu-close" aria-label="Close menu">&times;</button>
            </div>
            <nav class="mobile-nav" style="display: flex; flex-direction: column; gap: 16px;">
                <a href="/spanish-company-data-api" class="mobile-nav-link" style="color: #0f172a; font-weight: 700; text-decoration: none; font-size: 1.1rem;">API</a>
                <a href="/docs" class="mobile-nav-link" style="color: #0f172a; font-weight: 700; text-decoration: none; font-size: 1.1rem;">Documentation</a>
                
                <div class="mobile-auth" style="margin-top: 24px; display: flex; flex-direction: column; gap: 12px;">
                    <?php if (!session('logged_in')): ?>
                        <a href="/enter" class="btn" style="background: #f1f5f9; color: #0f172a; text-align: center; font-weight: 700; padding: 12px; border-radius: 12px; text-decoration: none;">Log In</a>
                        <a href="/register?intent=api" class="btn" style="background: #FACC15; color: #0f172a; text-align: center; font-weight: 700; padding: 12px; border-radius: 12px; text-decoration: none;">Get API Key</a>
                    <?php else: ?>
                        <a href="/dashboard" class="btn" style="background: #2563eb; color: #fff; text-align: center; font-weight: 700; padding: 12px; border-radius: 12px; text-decoration: none;">Dashboard</a>
                        <a href="/logout" class="btn" style="background: #f1f5f9; color: #475569; text-align: center; font-weight: 600; padding: 12px; border-radius: 12px; text-decoration: none;">Log out</a>
                    <?php endif; ?>
                </div>
            </nav>
        </div>
    </div>
</header>

<style>
    /* GLOBAL LAYOUT SYNC */
    :root {
        --container-max: 1240px;
        --container-gutter: 32px;
    }

    .container {
        max-width: var(--container-max) !important;
        padding-left: var(--container-gutter) !important;
        padding-right: var(--container-gutter) !important;
        margin-inline: auto;
    }

    .main-site-header {
        position: relative;
        z-index: 10001 !important;
        background: #0f172a;
        overflow: visible !important;
        border-bottom: 1px solid rgba(255,255,255,0.05);
    }

    .brand-name {
        font-weight: 950 !important;
        letter-spacing: -0.02em !important;
        color: #ffffff !important;
        display: flex;
        align-items: center;
    }

    /* Mobile Toggle Styling */
    .mobile-menu-toggle {
        display: none;
        background: transparent;
        border: none;
        padding: 8px;
        cursor: pointer;
        z-index: 100;
    }
    .hamburger-box { width: 24px; height: 18px; display: inline-block; position: relative; }
    .hamburger-inner, .hamburger-inner::before, .hamburger-inner::after {
        width: 22px; height: 2.5px; background-color: #ffffff !important;
        position: absolute; border-radius: 4px;
    }
    .hamburger-inner { top: 50%; transform: translateY(-50%); }
    .hamburger-inner::before { content: ""; top: -7px; }
    .hamburger-inner::after { content: ""; top: 7px; }

    /* Mobile Menu Overlay */
    .mobile-menu-overlay {
        position: fixed; inset: 0; background: rgba(15, 23, 42, 0.95) !important;
        z-index: 9999; opacity: 0; visibility: hidden; transition: opacity 0.3s ease, visibility 0.3s;
    }
    .mobile-menu-overlay.active { opacity: 1 !important; visibility: visible !important; }
    .mobile-menu-content {
        position: absolute; right: -320px; top: 0; bottom: 0; width: 300px;
        background-color: #ffffff !important; opacity: 1 !important;
        box-shadow: -10px 0 30px rgba(0, 0, 0, 0.2);
        transition: right 0.4s cubic-bezier(0.16, 1, 0.3, 1);
        display: flex; flex-direction: column; padding: 24px; z-index: 10000;
        visibility: visible !important; height: 100vh; overflow-y: auto;
    }
    .mobile-menu-overlay.active .mobile-menu-content { right: 0; }
    .mobile-menu-header {
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 32px; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9;
    }
    .mobile-menu-header .brand-name { color: #0f172a !important; }
    .mobile-menu-close {
        background: #f1f5f9; border: none; width: 40px; height: 40px; border-radius: 14px;
        font-size: 28px; display: flex; align-items: center; justify-content: center;
        cursor: pointer; color: #475569;
    }
    
    @media (max-width: 1024px) {
        .desktop-only { display: none !important; }
        .mobile-menu-toggle { display: block !important; }
    }
</style>
