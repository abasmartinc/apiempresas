<footer >
    <div class="container">
        <div class="foot-grid">
            <div>
                <div class="brand">
                    <!-- ICONO APIEMPRESAS (check limpio, sin triángulo) -->
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


                    <div class="brand-text">
                        <span class="brand-name">Verifica<span class="grad">Empresas</span>.es</span>
                        <span class="brand-tag">API de verificación mercantil en segundos</span>
                    </div>
                </div>
                <p>
                    Datos procedentes de fuentes públicas: BOE/BORME, AEAT, INE, VIES.
                    No somos servicio oficial del BOE. Cumplimiento RGPD (Art. 14) y
                    reutilización de información del sector público.
                </p>
            </div>
            <div>
                <h4 style="margin:0 0 8px">Producto</h4>
                <a class="minor" href="<?=site_url() ?>blog">Guías y artículos</a><br />
                <a class="minor" href="<?=site_url() ?>search_company">Buscador</a><br />
                <a class="minor" href="<?=site_url() ?>dcumentation">Documentación</a><br />
                <a class="minor" href="#prices">Precios</a><br />
                <a class="minor" href="#faqs">Preguntas frecuentes</a>
            </div>
            <div>
                <h4 style="margin:0 0 8px">Legal</h4>
                <a class="minor" href="#">Privacidad</a><br />
                <a class="minor" href="#">Términos</a><br />
                <a class="minor" href="<?=site_url() ?>contact">Contacto</a>
            </div>
        </div>
    </div>
</footer>
<?=view('scripts') ?>
