<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/contact.css') ?>" />
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <header>
        <div class="container nav">
            <div class="brand">
                <!-- ICONO VERIFICAEMPRESAS -->
                <a href="<?= site_url() ?>">
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
                </a>

                <div class="brand-text">
                    <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                    <span class="brand-tag">Verificación empresarial en segundos</span>
                </div>
            </div>

            <nav class="desktop-only" aria-label="Principal">
                <a class="minor" href="<?= site_url() ?>dashboard">Dashboard</a>
                <span style="margin:0 12px; color:#cdd6ea">•</span>
                <a class="minor" href="<?= site_url() ?>billing">Planes y facturación</a>
                <span style="margin:0 12px; color:#cdd6ea">•</span>
                <a class="minor" href="<?= site_url() ?>usage">Consumo</a>
                <span style="margin:0 12px; color:#cdd6ea">•</span>
                <a class="minor" href="<?= site_url() ?>documentation">Documentación</a>
                <span style="margin:0 12px; color:#cdd6ea">•</span>
                <a class="minor" href="<?= site_url() ?>search_company">Buscador</a>
            </nav>

            <div class="desktop-only">
                <?php if (!session('logged_in')): ?>
                    <a class="btn btn_header btn_header--ghost" href="<?= site_url() ?>enter">
                        <span>Iniciar sesión</span>
                    </a>
                <?php else: ?>
                    <a class="btn btn_header btn_header--ghost" href="<?= site_url() ?>logout">
                        <span>Salir</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </header>

    <!-- MAIN -->
    <main class="dash-main">
        <div class="container">
            <div class="dash-header">
                <h1>Contacto y soporte</h1>
                <p>Cuéntanos en qué podemos ayudarte con la API de VerificaEmpresas. Te responderemos en menos de 24&nbsp;h laborables.</p>
            </div>

            <div class="dash-grid">
                <!-- COLUMNA IZQUIERDA -->
                <div>
                    <section class="dash-card">
                        <h2>Envíanos tu consulta</h2>
                        <p>Rellena el formulario y nuestro equipo te contactará por email con los próximos pasos.</p>

                        <?php if (session()->getFlashdata('contact_success')): ?>
                            <div class="alert alert-success" style="margin-bottom:16px;">
                                <?= esc(session()->getFlashdata('contact_success')) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (session()->getFlashdata('contact_error')): ?>
                            <div class="alert alert-danger" style="margin-bottom:16px;">
                                <?= esc(session()->getFlashdata('contact_error')) ?>
                            </div>
                        <?php endif; ?>

                        <form action="<?= site_url('contact/send') ?>" method="post" class="contact-form" novalidate>
                            <?= csrf_field() ?>

                            <div class="grid-2" style="margin-bottom:14px;">
                                <div>
                                    <label for="contact_name" class="label">Nombre</label>
                                    <input
                                            id="contact_name"
                                            name="name"
                                            type="text"
                                            class="input"
                                            value="<?= esc(old('name', $user->name ?? '')) ?>"
                                            placeholder="Tu nombre completo"
                                            required
                                    >
                                </div>
                                <div>
                                    <label for="contact_email" class="label">Email de contacto</label>
                                    <input
                                            id="contact_email"
                                            name="email"
                                            type="email"
                                            class="input"
                                            value="<?= esc(old('email', $user->email ?? '')) ?>"
                                            placeholder="tucorreo@empresa.com"
                                            required
                                    >
                                </div>
                            </div>

                            <div class="grid-2" style="margin-bottom:14px;">
                                <div>
                                    <label for="contact_type" class="label">Tipo de consulta</label>
                                    <select id="contact_type" name="type" class="input" required>
                                        <option value="">Selecciona una opción…</option>
                                        <option value="integracion" <?= old('type') === 'integracion' ? 'selected' : '' ?>>
                                            Integración técnica / SDK / API
                                        </option>
                                        <option value="facturacion" <?= old('type') === 'facturacion' ? 'selected' : '' ?>>
                                            Facturación y planes
                                        </option>
                                        <option value="consumo" <?= old('type') === 'consumo' ? 'selected' : '' ?>>
                                            Consumo y límites de consultas
                                        </option>
                                        <option value="incidencia" <?= old('type') === 'incidencia' ? 'selected' : '' ?>>
                                            Incidencia o error en la API
                                        </option>
                                        <option value="otro" <?= old('type') === 'otro' ? 'selected' : '' ?>>
                                            Otro motivo
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label for="contact_company" class="label">Empresa (opcional)</label>
                                    <input
                                            id="contact_company"
                                            name="company"
                                            type="text"
                                            class="input"
                                            value="<?= esc(old('company', $user->company_name ?? '')) ?>"
                                            placeholder="Nombre legal o comercial"
                                    >
                                </div>
                            </div>

                            <div style="margin-bottom:14px;">
                                <label for="contact_subject" class="label">Asunto</label>
                                <input
                                        id="contact_subject"
                                        name="subject"
                                        type="text"
                                        class="input"
                                        value="<?= esc(old('subject')) ?>"
                                        placeholder="Ej. Error 429 al validar CIF desde producción"
                                        required
                                >
                            </div>

                            <div style="margin-bottom:16px;">
                                <label for="contact_message" class="label">Mensaje</label>
                                <textarea
                                        id="contact_message"
                                        name="message"
                                        class="input"
                                        rows="6"
                                        placeholder="Describe con detalle tu duda o incidencia. Si aplica, indica entorno (dev/prod), endpoint, parámetros y cualquier log relevante."
                                        required
                                ><?= esc(old('message')) ?></textarea>
                            </div>

                            <div class="grid-2" style="align-items:center; margin-top:10px;">
                                <div style="font-size:13px; color:#6b7280;">
                                    <strong>Tiempo medio de respuesta:</strong> &lt; 24h laborables.<br>
                                    Si se trata de una incidencia crítica, marca el asunto como <em>[URGENTE]</em>.
                                </div>
                                <div style="text-align:right;">
                                    <button type="submit" class="btn_start">
                                        Enviar mensaje
                                    </button>
                                </div>
                            </div>
                        </form>
                    </section>

                    <section class="dash-card">
                        <h2>Antes de escribirnos</h2>
                        <p>Quizás podamos ahorrarte tiempo. Revisa primero estos recursos:</p>

                        <div class="quick-grid">
                            <div class="quick-item">
                                <strong>Guía rápida de integración</strong>
                                <p style="margin:4px 0 0;">Primeros pasos, API key, autenticación y ejemplos de código.</p>
                                <a href="<?= site_url() ?>documentation#quickstart">Abrir guía →</a>
                            </div>
                            <div class="quick-item">
                                <strong>Referencia de endpoints</strong>
                                <p style="margin:4px 0 0;">Parámetros, respuestas de ejemplo y códigos de error.</p>
                                <a href="<?= site_url() ?>documentation#reference">Ver referencia →</a>
                            </div>
                            <div class="quick-item">
                                <strong>Estado del servicio</strong>
                                <p style="margin:4px 0 0;">Consulta si hay incidencias activas o tareas de mantenimiento.</p>
                                <a href="<?= site_url() ?>status">Ir al status page →</a>
                            </div>
                            <div class="quick-item">
                                <strong>Facturación y planes</strong>
                                <p style="margin:4px 0 0;">Cómo cambiar de plan, límites de consumo y facturas.</p>
                                <a href="<?= site_url() ?>billing">Ir a facturación →</a>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- COLUMNA DERECHA -->
                <aside>
                    <section class="plan-card">
                        <div class="plan-pill">
                            <span>Canales</span>
                            <span>de soporte</span>
                        </div>

                        <h2>Habla con nosotros</h2>
                        <p>Selecciona el canal que mejor encaje con tu consulta.</p>

                        <div class="plan-meta">
                            <div><strong>Email general:</strong><br>
                                <a href="mailto:soporte@verificaempresas.es">soporte@verificaempresas.es</a>
                            </div>
                            <div><strong>Consultas técnicas:</strong><br>
                                <a href="mailto:dev@verificaempresas.es">dev@verificaempresas.es</a>
                            </div>
                            <div><strong>Facturación y planes:</strong><br>
                                <a href="mailto:billing@verificaempresas.es">billing@verificaempresas.es</a>
                            </div>
                        </div>

                        <p style="margin-top:10px; color:#e5e7eb; font-size:13px;">
                            Horario de soporte: lunes a viernes de 9:00 a 18:00 (CET).<br>
                            Para emergencias fuera de horario, indica <strong>[URGENTE]</strong> en el asunto.
                        </p>
                    </section>

                    <section class="support-card">
                        <h3>Información útil</h3>
                        <p style="margin-bottom:8px;">
                            Al escribirnos sobre una incidencia técnica, incluye:
                        </p>
                        <ul style="padding-left:18px; margin:0 0 10px; font-size:13px; color:#4b5563;">
                            <li>El endpoint que estás llamando.</li>
                            <li>El CIF o parámetros usados (si procede).</li>
                            <li>El entorno (sandbox / producción) y fecha/hora aproximada.</li>
                            <li>El mensaje de error recibido o código HTTP.</li>
                        </ul>
                        <p style="color:#6b7280; font-size:13px; margin-top:8px;">
                            Cuantos más detalles tengamos, antes podremos reproducir y resolver el problema.
                        </p>
                    </section>
                </aside>
            </div>
        </div>
    </main>

    <?= view('partials/footer') ?>
</div>
</body>
</html>
