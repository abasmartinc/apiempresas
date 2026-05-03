<?php
$current_url = current_url();
?>
<aside class="ae-radar-page__sidebar">
    <div class="ae-radar-page__brand">
        <a href="<?=site_url() ?>" class="ae-radar-page__brand-header">
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
                       stroke="#FFFFFF" stroke-width="6" stroke-linecap="round" stroke-linejoin="round"
                       fill="none" filter="url(#ve-checkShadow)"/>
            </svg>
            <div class="brand-text">
                <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                <span class="brand-tag">Verificación empresarial</span>
            </div>
        </a>

        <small class="ae-radar-page__brand-note">
            Inteligencia comercial en tiempo real
        </small>
    </div>

    <div class="ae-radar-page__sidebar-body">
        <div class="ae-radar-page__nav-group">
            <span class="ae-radar-page__nav-label">Radar</span>

            <a href="<?= site_url('radar') ?>" class="ae-radar-page__nav-link <?= (rtrim($current_url, '/') == rtrim(site_url('radar'), '/')) ? 'is-active' : '' ?>">
                <span class="ae-radar-page__nav-icon">📊</span>
                Dashboard principal
            </a>

            <a href="<?= site_url('radar/favoritos') ?>" class="ae-radar-page__nav-link <?= (rtrim($current_url, '/') == rtrim(site_url('radar/favoritos'), '/')) ? 'is-active' : '' ?>">
                <span class="ae-radar-page__nav-icon">⭐</span>
                Mis favoritos
            </a>


            <a href="<?= site_url('radar/kanban') ?>" class="ae-radar-page__nav-link <?= (rtrim($current_url, '/') == rtrim(site_url('radar/kanban'), '/')) ? 'is-active' : '' ?>">
                <span class="ae-radar-page__nav-icon">📋</span>
                Embudo (Kanban)
            </a>

            <a href="<?= site_url('radar/trends') ?>" class="ae-radar-page__nav-link <?= (rtrim($current_url, '/') == rtrim(site_url('radar/trends'), '/')) ? 'is-active' : '' ?>">
                <span class="ae-radar-page__nav-icon">📈</span>
                Análisis de Tendencias
            </a>
            
            <a href="<?= site_url('radar/invoices') ?>" class="ae-radar-page__nav-link <?= (rtrim($current_url, '/') == rtrim(site_url('radar/invoices'), '/')) ? 'is-active' : '' ?>">

                <span class="ae-radar-page__nav-icon">🧾</span>
                Mis facturas
            </a>
        </div>

        <div class="ae-radar-page__nav-group">
            <span class="ae-radar-page__nav-label">Alertas</span>
            <div class="ae-radar-page__nav-teaser">
                <span class="ae-radar-page__nav-icon">🔔</span>
                <span>Alertas email</span>
                <span class="ae-radar-page__mini-badge">Próximamente</span>
            </div>
        </div>

        <div class="ae-radar-page__roi-box">
            <div class="ae-radar-page__roi-title">Calculadora ROI</div>
            <div class="ae-radar-page__roi-text">La mayoría de usuarios recupera la inversión con su primer cliente. Solo 1 cierre paga <strong>5 años</strong> de Radar PRO.</div>
            <div class="ae-radar-page__roi-stat">
                <span>Rentabilidad estimada</span>
                <strong>+450%</strong>
            </div>
        </div>
    </div>

    <div class="ae-radar-page__sidebar-footer">
        <a href="<?= site_url('dashboard') ?>" class="ae-radar-page__nav-link" id="radar_to_api_cross_sell">
            <span class="ae-radar-page__nav-icon">🔌</span>
            API para desarrolladores
        </a>

        <a href="<?= site_url('logout') ?>" class="ae-radar-page__nav-link">
            <span class="ae-radar-page__nav-icon">🚪</span>
            Cerrar sesión
        </a>
    </div>
</aside>
