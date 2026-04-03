<footer>
    <div class="container">
        <!-- TOP ROW: Links in 4 Columns -->
        <div class="foot-top-grid">
            <!-- Radar by Time -->
            <div>
                <h4 class="foot-title">Radar por Tiempo</h4>
                <ul class="foot-links">
                    <li><a href="<?=site_url('empresas-nuevas-hoy') ?>">Nuevas empresas hoy</a></li>
                    <li><a href="<?=site_url('empresas-nuevas-semana') ?>">Creadas esta semana</a></li>
                    <li><a href="<?=site_url('empresas-nuevas-mes') ?>">Constituidas este mes</a></li>
                    <li><a href="<?=site_url('empresas-nuevas') ?>">Radar Nacional (Hub)</a></li>
                    <li><a href="<?=site_url('directorio/ultimas-empresas-registradas') ?>">Últimas registradas</a></li>
                </ul>
            </div>

            <!-- Radar by Province -->
            <div>
                <h4 class="foot-title">Radar por Provincia</h4>
                <ul class="foot-links">
                    <li><a href="<?=site_url('empresas-nuevas/madrid') ?>">Nuevas en Madrid</a></li>
                    <li><a href="<?=site_url('empresas-nuevas/barcelona') ?>">Nuevas en Barcelona</a></li>
                    <li><a href="<?=site_url('empresas-nuevas/valencia') ?>">Nuevas en Valencia</a></li>
                    <li><a href="<?=site_url('empresas-nuevas/sevilla') ?>">Nuevas en Sevilla</a></li>
                    <li><a href="<?=site_url('empresas-nuevas/malaga') ?>">Nuevas en Málaga</a></li>
                    <li><a href="<?=site_url('directorio') ?>">Ver todas las provincias</a></li>
                </ul>
            </div>
            <!-- Radar by Sector -->
            <div>
                <h4 class="foot-title">Radar por Sector</h4>
                <ul class="foot-links">
                    <li><a href="<?=site_url('empresas-nuevas-sector/hosteleria') ?>">Hostelería y Restauración</a></li>
                    <li><a href="<?=site_url('empresas-nuevas-sector/construccion') ?>">Construcción e Inmobiliaria</a></li>
                    <li><a href="<?=site_url('empresas-nuevas-sector/programacion-informatica') ?>">Tecnología y Software</a></li>
                    <li><a href="<?=site_url('empresas-nuevas-sector/marketing') ?>">Marketing y Publicidad</a></li>
                    <li><a href="<?=site_url('empresas-nuevas-sector/transporte') ?>">Logística y Transporte</a></li>
                </ul>
            </div>

            <!-- Products & API -->
            <div>
                <h4 class="foot-title">Directorio y API</h4>
                <ul class="foot-links">
                    <li><a href="<?=site_url('leads-empresas-nuevas') ?>">Beneficios de Radar Pro</a></li>
                    <li><a href="<?=site_url('search_company') ?>">Buscador de Empresas</a></li>
                    <li><a href="<?=site_url('autocompletado-cif-empresas') ?>">Autocompletado Pro</a></li>
                    <li><a href="<?=site_url('documentation') ?>">Documentación API</a></li>
                    <li><a href="<?=site_url('contact') ?>">Atención al Cliente</a></li>
                </ul>
            </div>
        </div>

        <!-- SECOND ROW: Informes de Mercado 4 Columns -->
        <h3 class="foot-title" style="margin: 40px 0 20px; font-size: 0.9rem; letter-spacing: 0.1em; opacity: 0.8;">INFORMES DE MERCADO</h3>
        <div class="foot-top-grid" style="padding-top: 10px; border-top: 1px solid rgba(255,255,255,0.05);">
            <?php
            $wpService = new \App\Services\WordPressService();
            $seoService = new \App\Services\SeoTemplateService();
            $templates = $wpService->getTemplatesByCategory(20);
            $blacklist = ['listado', 'actualizado', 'hoy', 'semana', 'analisis'];
            
            // Filtro inicial limpio
            $cleanTpls = array_filter($templates, function($t) use ($blacklist) {
                $title = $t['title']['rendered'] ?? '';
                foreach ($blacklist as $word) if (stripos($title, $word) !== false) return false;
                return true;
            });

            // Encontrar la plantilla más corta que tenga los dos placeholders
            $bestTpl = null;
            foreach ($cleanTpls as $t) {
                $title = html_entity_decode($t['title']['rendered'], ENT_QUOTES, 'UTF-8');
                if (strpos($title, '{{provincia}}') !== false && strpos($title, '{{sector}}') !== false) {
                    if (!$bestTpl || strlen($title) < strlen($bestTpl)) $bestTpl = $title;
                }
            }
            $bestTpl = $bestTpl ?? 'Empresas nuevas de {{sector}} en {{provincia}}';

            // Col 1: Madrid
            echo '<div><h4 class="foot-title" style="color: #5C7CFF">Madrid</h4><ul class="foot-links">';
            foreach (['Hostelería', 'Construcción', 'Tecnología', 'Comercio'] as $s) {
                $lt = str_replace(['{{provincia}}', '{{sector}}'], ['Madrid', $s], $bestTpl);
                $fs = str_replace(['{{provincia}}', '{{sector}}'], ['madrid', $seoService->slugifyWithPlaceholders($s)], $seoService->slugifyWithPlaceholders($bestTpl));
                echo '<li><a href="' . site_url('informes/' . $fs) . '">' . esc($lt) . '</a></li>';
            }
            echo '</ul></div>';

            // Col 2: Barcelona
            echo '<div><h4 class="foot-title" style="color: #5C7CFF">Barcelona</h4><ul class="foot-links">';
            foreach (['Restauración', 'Inmobiliaria', 'Software', 'Servicios'] as $s) {
                $lt = str_replace(['{{provincia}}', '{{sector}}'], ['Barcelona', $s], $bestTpl);
                $fs = str_replace(['{{provincia}}', '{{sector}}'], ['barcelona', $seoService->slugifyWithPlaceholders($s)], $seoService->slugifyWithPlaceholders($bestTpl));
                echo '<li><a href="' . site_url('informes/' . $fs) . '">' . esc($lt) . '</a></li>';
            }
            echo '</ul></div>';

            // Col 3: Nacional
            echo '<div><h4 class="foot-title" style="color: #5C7CFF">Nacional</h4><ul class="foot-links">';
            foreach (['Hostelería', 'Construcción', 'Informatica', 'Marketing'] as $s) {
                $lt = str_replace(['{{provincia}}', '{{sector}}'], ['España', $s], $bestTpl);
                $fs = str_replace(['{{provincia}}', '{{sector}}'], ['espana', $seoService->slugifyWithPlaceholders($s)], $seoService->slugifyWithPlaceholders($bestTpl));
                echo '<li><a href="' . site_url('informes/' . $fs) . '">' . esc($lt) . '</a></li>';
            }
            echo '</ul></div>';

            // Col 4: Provincias
            echo '<div><h4 class="foot-title" style="color: #5C7CFF">Otras Provincias</h4><ul class="foot-links">';
            foreach (['Valencia', 'Sevilla', 'Málaga', 'Alicante'] as $p) {
                $lt = str_replace(['{{provincia}}', '{{sector}}'], [$p, 'General'], $bestTpl);
                $fs = str_replace(['{{provincia}}', '{{sector}}'], [$seoService->slugifyWithPlaceholders($p), 'general'], $seoService->slugifyWithPlaceholders($bestTpl));
                echo '<li><a href="' . site_url('informes/' . $fs) . '">Nuevas en ' . esc($p) . '</a></li>';
            }
            echo '</ul></div>';
            ?>
        </div>

        <!-- BOTTOM ROW: Brand Info & Trust -->
        <div class="foot-bottom-brand">
            <div class="foot-brand-content">
                <div class="brand">
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
                </div>
                <p class="foot-desc">
                    Datos oficiales procedentes de BORME, AEAT, INE y VIES. Cumplimiento normativo y trazabilidad para procesos KYB/KYC y facturación B2B.
                </p>
                <div class="foot-legal-row">
                    <a href="#" class="minor" data-modal-target="modalPrivacy">Privacidad</a> · 
                    <a href="#" class="minor" data-modal-target="modalTerms">Términos</a>
                </div>
            </div>

            <!-- Trust Signals -->
            <!-- Trust Signals -->
<div class="foot-trust foot-trust--minimal">
    <div class="trust-item">
        <span class="trust-label">Pasarela segura</span>

        <div class="trust-panel">
            <div class="trust-panel__logos">
                <img src="<?= base_url('public/images/stripe.png') ?>" alt="Stripe">
                <span class="trust-panel__divider"></span>
                <img src="<?= base_url('public/images/ssl.png') ?>" alt="SSL Secure">
            </div>
        </div>
    </div>

    <div class="trust-badges">
        <div class="badge-item premium">
            <div class="badge-icon">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <span>Fuentes Oficiales (BORME)</span>
        </div>
    </div>
</div>
        </div>
    </div>
</footer>

<!-- AI CHAT BUBBLE -->
<?= view('partials/ai_chat_bubble') ?>

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
