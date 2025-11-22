<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/register.css') ?>" />
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <header>
        <div class="container nav">
            <div class="brand">
                <!-- ICONO VERIFICAEMPRESAS (check limpio, sin triángulo) -->
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
                <a class="btn btn_header" href="<?=site_url() ?>enter">Iniciar sesión</a>
            </div>
        </div>
    </header>


    <!-- MAIN -->
    <main class="auth-main">
        <section class="container">
            <div class="auth-grid">
                <!-- COLUMNA IZQUIERDA: COPY -->
                <div class="auth-copy">
                    <span class="auth-copy-eyebrow">Crear cuenta gratuita</span>
                    <h1>Empieza a validar empresas españolas en minutos.</h1>
                    <p>
                        Crea tu cuenta, consigue tu <strong>API key</strong> y conéctate al
                        registro mercantil, AEAT, INE y VIES desde tu propio producto.
                    </p>
                    <p>
                        Pensado para <strong>developers</strong>, gestorías y
                        <strong>departamentos de riesgo/compliance</strong> que necesitan datos fiables,
                        en tiempo real y con buena documentación.
                    </p>

                    <ul class="auth-bullets">
                        <li>
                            <span class="bullet-dot"></span>
                            <span><strong>Plan Free</strong> con 2.000 consultas/mes para desarrollo y pruebas.</span>
                        </li>
                        <li>
                            <span class="bullet-dot"></span>
                            <span><strong>API REST</strong> sencilla, JSON limpio y ejemplos listos para copiar.</span>
                        </li>
                        <li>
                            <span class="bullet-dot"></span>
                            <span>Datos procedentes de <strong>BOE/BORME, AEAT, INE y VIES</strong> con enlace a fuente oficial.</span>
                        </li>
                    </ul>
                </div>

                <!-- COLUMNA DERECHA: FORMULARIO -->
                <div class="auth-card">
                    <h2>Crea tu cuenta gratis</h2>
                    <p>Sin tarjeta, sin permanencias. Solo necesitas un correo profesional.</p>
                    <?php
                    /** @var \CodeIgniter\Validation\Validation $validation */
                    $validation = $validation ?? \Config\Services::validation();
                    ?>

                    <?php if (session('error')): ?>
                        <div class="auth-error">
                            <?= esc(session('error')) ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($validation->getErrors()): ?>
                        <div class="auth-error">
                            <ul>
                                <?php foreach ($validation->getErrors() as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form class="auth-form" method="post" action="<?= site_url('signup') ?>">
                        <?= csrf_field() ?>

                        <!-- Nombre + Empresa -->
                        <div class="auth-row-inline">
                            <div style="flex:1;">
                                <label for="name">Nombre y apellidos</label>
                                <input
                                        class="input"
                                        type="text"
                                        id="name"
                                        name="name"
                                        autocomplete="name"
                                        required
                                        placeholder="Ej. Ana García López"
                                        value="<?= esc(old('name')) ?>"
                                />
                            </div>
                            <div style="flex:1;">
                                <label for="company">Empresa (opcional)</label>
                                <input
                                        class="input"
                                        type="text"
                                        id="company"
                                        name="company"
                                        autocomplete="organization"
                                        placeholder="Ej. Gestoría Centro SL"
                                        value="<?= esc(old('company')) ?>"
                                />
                            </div>
                        </div>

                        <!-- Email -->
                        <div>
                            <label for="email">Correo electrónico</label>
                            <input
                                    class="input"
                                    type="email"
                                    id="email"
                                    name="email"
                                    autocomplete="email"
                                    required
                                    placeholder="tu@empresa.com"
                                    value="<?= esc(old('email')) ?>"
                            />
                        </div>

                        <!-- Password -->
                        <div>
                            <label for="password">Contraseña</label>
                            <input
                                    class="input"
                                    type="password"
                                    id="password"
                                    name="password"
                                    autocomplete="new-password"
                                    required
                                    minlength="8"
                                    placeholder="Mínimo 8 caracteres"
                            />
                        </div>

                        <!-- Checkbox legal -->
                        <div class="auth-checkbox">
                            <input
                                    type="checkbox"
                                    id="terms"
                                    name="terms"
                                    value="1"
                                <?= old('terms') ? 'checked' : '' ?>
                                    required
                            />
                            <label for="terms" style="margin:0; font-weight:400;">
                                Acepto la <a href="/privacidad" target="_blank" rel="noopener">Política de privacidad</a>
                                y los <a href="/terminos" target="_blank" rel="noopener">Términos de servicio</a>.
                            </label>
                        </div>

                        <!-- CTA + texto pequeño -->
                        <div class="auth-submit-row">
                            <button type="submit" class="btn">
                                Crear cuenta gratis
                            </button>
                            <p class="auth-muted">
                                Al crear tu cuenta activamos automáticamente el <strong>plan Free</strong>
                                con 2.000 consultas/mes para que integres la API sin compromiso.
                            </p>
                            <p class="auth-muted">
                                ¿Ya tienes cuenta?
                                <a href="<?=site_url() ?>enter">Inicia sesión</a>.
                            </p>
                        </div>
                    </form>

                </div>
            </div>
        </section>
    </main>

    <?=view('partials/footer') ?>
</div>
</body>
</html>
