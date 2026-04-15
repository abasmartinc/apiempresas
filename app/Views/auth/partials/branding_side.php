<div class="auth-branding">
    <div class="auth-logo-row">
        <a href="<?= site_url() ?>" class="auth-logo-link" style="display: flex; align-items: center; gap: 12px; text-decoration: none; color: inherit;">
            <svg class="ve-logo" width="34" height="34" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
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
            <span class="auth-logo-text">APIEmpresas.es</span>
        </a>
    </div>

    <div class="auth-branding-main">
        <h1 class="auth-branding-title">Inteligencia de Datos <span>para tu Negocio</span></h1>
        <p class="auth-branding-sub">Accede a la infraestructura más completa de información empresarial en España y optimiza tus procesos B2B.</p>

        <div class="auth-features">
            <div class="auth-feature-item">
                <div class="auth-feature-icon" style="background: rgba(33, 82, 255, 0.1); color: #2152ff;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                </div>
                <div class="auth-feature-content">
                    <h4>API Empresas</h4>
                    <p>Integra validación de CIF, datos oficiales y scoring en minutos con una infraestructura potente y segura.</p>
                </div>
            </div>

            <div class="auth-feature-item">
                <div class="auth-feature-icon" style="background: rgba(18, 180, 138, 0.1); color: #12b48a;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                </div>
                <div class="auth-feature-content">
                    <h4>Radar de Empresas</h4>
                    <p>Detecta nuevas empresas, prioriza oportunidades y acelera tu prospección B2B antes que tu competencia.</p>
                </div>
            </div>
        </div>
    </div>

    <div class="auth-branding-footer">
        <div class="auth-social-badge">
            <span class="auth-social-dot"></span>
            Usado por +500 asesores y empresas españolas
        </div>
    </div>
</div>
