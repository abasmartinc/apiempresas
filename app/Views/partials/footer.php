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
                <a class="minor" href="<?=site_url() ?>directorio">Directorio SEO</a><br />
                <a class="minor" href="<?=site_url() ?>search_company">Buscador</a><br />
                <a class="minor" href="<?=site_url() ?>documentation">Documentación</a><br />
            </div>
            <div>
                <h4 style="margin:0 0 8px">Legal</h4>
                <a href="#" class="minor" data-open-modal="modalPrivacy">Política de privacidad</a><br/>
                <a href="#" class="minor" data-open-modal="modalTerms">Términos de uso</a><br/>
                <a class="minor" href="<?=site_url() ?>contact">Contacto</a>
            </div>
        </div>
    </div>
</footer>
<!-- =========================
     MODAL · POLÍTICA PRIVACIDAD
     ========================= -->
<div class="modal-overlay" id="modalPrivacy" aria-hidden="true">
    <div class="modal modal-wow" role="dialog" aria-modal="true" aria-labelledby="privacyTitle" tabindex="-1">
        <div class="modal-header">
            <div>
                <div class="modal-kicker">Legal</div>
                <h2 class="modal-title" id="privacyTitle">Política de privacidad</h2>
                <p class="modal-sub">Cómo recopilamos, usamos y protegemos tu información.</p>
            </div>
            <button class="modal-close" type="button" aria-label="Cerrar" data-close-modal>✕</button>
        </div>

        <div class="modal-body modal-legal">
            <h3>1) Información que recopilamos</h3>
            <p>Podemos recopilar datos de cuenta (por ejemplo, nombre y correo), información de facturación (gestionada por nuestro proveedor de pagos) y datos de uso (por ejemplo, solicitudes realizadas y funcionalidades utilizadas) para operar y mejorar el servicio.</p>

            <h3>2) Finalidades del tratamiento</h3>
            <ul>
                <li>Prestar y mantener el servicio</li>
                <li>Prevenir fraude, abuso y accesos no autorizados</li>
                <li>Mejorar rendimiento, fiabilidad y experiencia de usuario</li>
                <li>Comunicaciones esenciales relacionadas con tu cuenta</li>
            </ul>

            <h3>3) Cesiones y proveedores</h3>
            <p>No vendemos tus datos. Podemos compartir información con proveedores necesarios para prestar el servicio (hosting, analítica, pagos), bajo acuerdos de confidencialidad y medidas de seguridad.</p>

            <h3>4) Conservación</h3>
            <p>Conservamos la información el tiempo necesario para prestar el servicio y cumplir obligaciones legales. Puedes solicitar la eliminación cuando sea aplicable.</p>

            <h3>5) Derechos</h3>
            <p>Puedes solicitar acceso, rectificación o supresión de tus datos personales cuando sea aplicable. Escríbenos a <strong>soporte@apiempresas.es</strong>.</p>

            <h3>6) Seguridad</h3>
            <p>Aplicamos medidas técnicas y organizativas para proteger la información (controles de acceso, cifrado en tránsito, monitorización). Ningún sistema es 100% infalible.</p>

            <div class="modal-note">
                <strong>Última actualización:</strong> 31/12/2025
            </div>
        </div>

        <div class="modal-footer">
            <button class="modal-btn" type="button" data-close-modal>Cerrar</button>
            <button class="modal-btn primary" type="button" data-close-modal>Entendido</button>
        </div>
    </div>
</div>

<!-- =========================
     MODAL · TÉRMINOS DE USO
     ========================= -->
<div class="modal-overlay" id="modalTerms" aria-hidden="true">
    <div class="modal modal-wow" role="dialog" aria-modal="true" aria-labelledby="termsTitle" tabindex="-1">
        <div class="modal-header">
            <div>
                <div class="modal-kicker">Legal</div>
                <h2 class="modal-title" id="termsTitle">Términos de uso</h2>
                <p class="modal-sub">Condiciones para acceder y utilizar la plataforma.</p>
            </div>
            <button class="modal-close" type="button" aria-label="Cerrar" data-close-modal>✕</button>
        </div>

        <div class="modal-body modal-legal">
            <h3>1) Aceptación</h3>
            <p>Al acceder o utilizar el servicio, aceptas estos términos. Si no estás de acuerdo, no uses la plataforma.</p>

            <h3>2) Uso permitido</h3>
            <ul>
                <li>Usar el servicio conforme a la ley y a estos términos</li>
                <li>No intentar acceder a sistemas o datos sin autorización</li>
                <li>No interferir con el funcionamiento (abuso de rate limits, scraping malicioso, etc.)</li>
            </ul>

            <h3>3) Cuenta y seguridad</h3>
            <p>Eres responsable de mantener la confidencialidad de tus credenciales y de toda actividad realizada desde tu cuenta.</p>

            <h3>4) Disponibilidad</h3>
            <p>El servicio puede experimentar interrupciones o cambios. Podremos modificar funcionalidades para mejorar seguridad, rendimiento o cumplimiento.</p>

            <h3>5) Propiedad intelectual</h3>
            <p>La plataforma, diseño, marca y contenidos están protegidos. No se permite la copia o reventa del servicio sin autorización.</p>

            <h3>6) Limitación de responsabilidad</h3>
            <p>El servicio se ofrece “tal cual”. En la medida permitida por ley, no seremos responsables por pérdidas indirectas derivadas del uso del servicio.</p>

            <h3>7) Contacto</h3>
            <p>Para consultas legales o soporte: <strong>soporte@apiempresas.es</strong>.</p>

            <div class="modal-note">
                <strong>Última actualización:</strong> 31/12/2025
            </div>
        </div>

        <div class="modal-footer">
            <button class="modal-btn" type="button" data-close-modal>Cerrar</button>
            <button class="modal-btn primary" type="button" data-close-modal>Aceptar</button>
        </div>
    </div>
</div>

<?=view('scripts') ?>
