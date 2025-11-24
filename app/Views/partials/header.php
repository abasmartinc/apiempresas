<header>
    <div class="container nav">
        <div class="brand">
            <!-- ICONO APIEMPRESAS (check limpio, sin triángulo) -->
            <a href="<?=site_url() ?>">
                <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <!-- Degradado de marca -->
                        <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#2152FF"/>
                            <stop offset=".65" stop-color="#5C7CFF"/>
                            <stop offset="1" stop-color="#12B48A"/>
                        </linearGradient>
                        <!-- Halo del bloque -->
                        <filter id="ve-cardShadow" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20"/>
                        </filter>
                        <!-- Sombra suave del check (no genera triángulos) -->
                        <filter id="ve-checkShadow" x="-30%" y="-30%" width="160%" height="160%">
                            <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="#0B1A36" flood-opacity=".22"/>
                        </filter>
                        <!-- Brillo muy leve arriba-izquierda -->
                        <radialGradient id="ve-gloss" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(20 16) rotate(45) scale(28)">
                            <stop stop-color="#FFFFFF" stop-opacity=".32"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity="0"/>
                        </radialGradient>
                        <!-- Aro exterior para definir borde en fondos muy claros -->
                        <linearGradient id="ve-rim" x1="12" y1="52" x2="52" y2="12">
                            <stop stop-color="#FFFFFF" stop-opacity=".6"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity=".35"/>
                        </linearGradient>
                    </defs>

                    <!-- Tarjeta con halo + brillo sutil -->
                    <g filter="url(#ve-cardShadow)">
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)"/>
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss)"/>
                        <rect x="6.5" y="6.5" width="51" height="51" rx="13.5" fill="none" stroke="url(#ve-rim)"/>
                    </g>

                    <!-- Check principal sin trazo oscuro debajo, con sombra de filtro -->
                    <path d="M18 33 L28 43 L46 22"
                          stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"
                          fill="none" filter="url(#ve-checkShadow)"/>
                </svg>
            </a>


            <div class="brand-text">
                <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                <span class="brand-tag">Verificación empresarial en segundos</span>
            </div>
        </div>

        <nav class="desktop-only" aria-label="Principal">
            <a class="minor" href="#buscar">Buscar</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="#caracteristicas">Características</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="#precios">Precios</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="<?=site_url() ?>documentation">Docs</a>
        </nav>
        <div class="desktop-only">
            <!-- Crear cuenta gratis -->
            <a class="btn btn_header btn_header--primary" href="<?=site_url() ?>register">

                <span>Crear cuenta gratis</span>
            </a>

            <!-- Iniciar sesión -->
            <a class="btn btn_header btn_header--ghost" href="<?=site_url() ?>enter">

                <span>Iniciar sesión</span>
            </a>
        </div>



    </div>
</header>
