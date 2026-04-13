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
        <h1 class="auth-branding-title">La mayor base de datos empresarial <span>en tu API</span></h1>
        <p class="auth-branding-sub">Conecta tus sistemas a la información mercantil más precisa y actualizada de España en tiempo real.</p>

        <div class="auth-features">
            <div class="auth-feature-item">
                <div class="auth-feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                </div>
                <div class="auth-feature-content">
                    <h4>API REST Profesional</h4>
                    <p>Integración sencilla y potente para consultar millones de empresas en segundos.</p>
                </div>
            </div>

            <div class="auth-feature-item">
                <div class="auth-feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                </div>
                <div class="auth-feature-content">
                    <h4>Datos Enriquecidos</h4>
                    <p>Acceso a CIF, CNAE, vinculaciones financieras, KRYS y datos de administradores.</p>
                </div>
            </div>

            <div class="auth-feature-item">
                <div class="auth-feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                </div>
                <div class="auth-feature-content">
                    <h4>Vigilancia Avanzada</h4>
                    <p>Monitorización automática de cambios en el BORME, subastas y alertas legales diarias.</p>
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
