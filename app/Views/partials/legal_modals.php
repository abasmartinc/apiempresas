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
            <h3>1. Identidad del Responsable</h3>
            <p>El responsable del tratamiento de los datos personales recogidos en esta plataforma es <strong>Ariel Martinez Hernandez</strong> (en adelante, "APIEmpresas"), con NIF 54994158P y domicilio en República Dominicana 40, Bajo E, 28983, Parla (Madrid). Puedes contactar con nosotros en <strong>soporte@apiempresas.es</strong>.</p>

            <h3>2. Finalidades y Base Legitimadora</h3>
            <p>Tratamos tus datos personales con las siguientes finalidades y bases legales:</p>
            <ul>
                <li><strong>Prestación del servicio y gestión de tu cuenta:</strong> Necesario para la ejecución del contrato (Términos de Uso) al registrarte.</li>
                <li><strong>Facturación y cobros:</strong> Cumplimiento de obligaciones legales y fiscales.</li>
                <li><strong>Prevención de fraude y seguridad:</strong> Basado en nuestro interés legítimo de mantener la plataforma segura.</li>
                <li><strong>Comunicaciones comerciales y marketing:</strong> Nos basamos en nuestro <strong>interés legítimo</strong> para enviarte información sobre productos o servicios similares a los contratados. Puedes oponerte a este tratamiento marcando la casilla correspondiente durante el registro o dándote de baja en cualquier momento.</li>
            </ul>

            <h3>3. Conservación de los Datos</h3>
            <p>Los datos se conservarán mientras mantengas tu cuenta activa y sea necesario para prestar el servicio. Una vez cancelada, se conservarán debidamente bloqueados durante el plazo exigido por la legislación aplicable (generalmente hasta 5 años por motivos fiscales y legales).</p>

            <h3>4. Destinatarios y Transferencias Internacionales</h3>
            <p>No vendemos tus datos a terceros. Podemos cederlos a proveedores estrictamente necesarios (hosting, analítica, pasarela de pago como Stripe). Algunos de estos proveedores (ej. Google, GitHub) pueden transferir datos fuera del EEE. Nos aseguramos de que cumplan con el RGPD mediante cláusulas contractuales tipo o acuerdos equivalentes.</p>
            <p><strong>Cloudflare Turnstile:</strong> Utilizamos Cloudflare Turnstile para proteger nuestros formularios contra el spam y el abuso automatizado. El uso de Turnstile está sujeto a la <a href="https://www.cloudflare.com/website-terms/" target="_blank" rel="noopener noreferrer">Política de Privacidad y Términos de Cloudflare</a>.</p>

            <h3>5. Tus Derechos (ARCO-POL)</h3>
            <p>Tienes derecho a obtener confirmación sobre si estamos tratando tus datos. También tienes derecho a:</p>
            <ul>
                <li>Acceder a tus datos personales.</li>
                <li>Solicitar su rectificación si son inexactos.</li>
                <li>Solicitar su supresión cuando ya no sean necesarios.</li>
                <li>Solicitar la limitación u oponerse a su tratamiento.</li>
                <li>Solicitar la portabilidad de tus datos.</li>
            </ul>
            <p>Puedes ejercerlos enviando un email a <strong>soporte@apiempresas.es</strong>. Asimismo, si consideras que tus datos no han sido tratados adecuadamente, tienes derecho a presentar una reclamación ante la Agencia Española de Protección de Datos (AEPD).</p>

            <div class="modal-note">
                <strong>Última actualización:</strong> <?= date('d/m/Y') ?>
            </div>
        </div>

        <div class="modal-footer">
            <button class="modal-btn" type="button" data-close-modal>Cerrar</button>
            <button class="modal-btn primary" type="button" data-close-modal>Entendido</button>
        </div>
    </div>
</div>

<!-- =========================
     MODAL · AVISO LEGAL Y T�?RMINOS DE USO
     ========================= -->
<div class="modal-overlay" id="modalTerms" aria-hidden="true">
    <div class="modal modal-wow" role="dialog" aria-modal="true" aria-labelledby="termsTitle" tabindex="-1">
        <div class="modal-header">
            <div>
                <div class="modal-kicker">Legal</div>
                <h2 class="modal-title" id="termsTitle">Aviso Legal y Términos de Uso</h2>
                <p class="modal-sub">Condiciones legales para acceder y utilizar la plataforma.</p>
            </div>
            <button class="modal-close" type="button" aria-label="Cerrar" data-close-modal>✕</button>
        </div>

        <div class="modal-body modal-legal">
            <h3>1. Información General (LSSI-CE)</h3>
            <p>En cumplimiento de la Ley 34/2002, de Servicios de la Sociedad de la Información y de Comercio Electrónico (LSSI-CE), se informa que el titular de la plataforma web APIEmpresas es:</p>
            <ul>
                <li><strong>Titular:</strong> Ariel Martinez Hernandez</li>
                <li><strong>NIF:</strong> 54994158P</li>
                <li><strong>Domicilio:</strong> República Dominicana 40, Bajo E, 28983, Parla (Madrid)</li>
                <li><strong>Contacto:</strong> soporte@apiempresas.es</li>
            </ul>

            <h3>2. Aceptación</h3>
            <p>Al acceder o registrarte en el servicio, adquieres la condición de Usuario y aceptas estos términos de uso en su totalidad. Si no estás de acuerdo, no utilices la plataforma.</p>

            <h3>3. Condiciones de Uso Permitido</h3>
            <p>Te comprometes a hacer un uso adecuado y lícito del servicio. Queda terminantemente prohibido:</p>
            <ul>
                <li>Intentar acceder a sistemas o datos sin autorización.</li>
                <li>Interferir con el funcionamiento mediante el abuso de los límites de peticiones (rate limits), scraping malicioso o ataques informáticos.</li>
                <li>Utilizar el servicio para fines ilícitos o que perjudiquen los derechos de terceros.</li>
            </ul>

            <h3>4. Cuenta y Seguridad</h3>
            <p>Eres el único responsable de mantener la confidencialidad de tus credenciales (contraseña, claves API) y de toda la actividad que se realice desde tu cuenta.</p>

            <h3>5. Propiedad Intelectual</h3>
            <p>Todo el contenido de la plataforma (diseño, código, estructura y bases de datos) es propiedad exclusiva de Ariel Martinez Hernandez o cuenta con las licencias correspondientes. No se permite la reproducción, distribución o modificación sin autorización expresa.</p>

            <h3>6. Limitación de Responsabilidad</h3>
            <p>La plataforma se proporciona "tal cual". Dentro del marco legal aplicable, APIEmpresas no se hace responsable de daños o perjuicios directos o indirectos derivados del uso de la información obtenida a través de la API, ni de las posibles interrupciones del servicio por causas ajenas.</p>

            <h3>7. Jurisdicción y Ley Aplicable</h3>
            <p>Estas condiciones se rigen por la legislación española. Para la resolución de cualquier controversia derivada del uso del servicio, las partes se someten a los juzgados y tribunales de la ciudad de Madrid, renunciando a cualquier otro fuero que pudiera corresponderles.</p>

            <div class="modal-note">
                <strong>Última actualización:</strong> <?= date('d/m/Y') ?>
            </div>
        </div>

        <div class="modal-footer">
            <button class="modal-btn" type="button" data-close-modal>Cerrar</button>
            <button class="modal-btn primary" type="button" data-close-modal>Aceptar</button>
        </div>
    </div>
</div>


