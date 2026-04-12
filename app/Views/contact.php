<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/contact.css') ?>" />
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <?= view('partials/header') ?>

    <!-- MAIN -->
    <main class="dash-main">
        <div class="container">
            <div class="dash-header">
                <h1>Contacto y soporte</h1>
                <p>Cuéntanos en qué podemos ayudarte con la API de APIEmpresas. Te responderemos en menos de 24&nbsp;h laborables.</p>
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
                            <!--<div class="quick-item">
                                <strong>Estado del servicio</strong>
                                <p style="margin:4px 0 0;">Consulta si hay incidencias activas o tareas de mantenimiento.</p>
                                <a href="<?php /*= site_url() */?>status">Ir al status page →</a>
                            </div>
                            <div class="quick-item">
                                <strong>Facturación y planes</strong>
                                <p style="margin:4px 0 0;">Cómo cambiar de plan, límites de consumo y facturas.</p>
                                <a href="<?php /*= site_url() */?>billing">Ir a facturación →</a>
                            </div>-->
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
                                <a href="mailto:soporte@apiempresas.es">soporte@apiempresas.es</a>
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
