<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/register.css') ?>" />
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <?= view('partials/header') ?>


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
                            <span><strong>Plan Free</strong> incluido para pruebas y desarrollo.</span>
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

                    <form id="registerForm" class="auth-form" method="post" action="<?= site_url('signup') ?>">
                        <?= csrf_field() ?>
                        <?php if(!empty($redirectUrl)): ?>
                            <input type="hidden" name="redirect" value="<?= esc($redirectUrl) ?>">
                        <?php endif; ?>

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
                                Acepto la <a href="#" data-open-modal="modalPrivacy">Política de privacidad</a>
                                y los <a href="#" data-open-modal="modalTerms">Términos de servicio</a>.
                            </label>
                        </div>

                        <!-- CTA + texto pequeño -->
                        <div class="auth-submit-row">
                            <button id="registerSubmit" type="submit" class="btn">
                                Crear cuenta gratis
                            </button>
                            <p class="auth-muted">
                                Puedes pasar a Pro en cualquier momento desde el panel.
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
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('registerForm');
        const btn  = document.getElementById('registerSubmit');

        if (!form || !btn) return;

        const originalHTML = btn.innerHTML;

        form.addEventListener('submit', () => {
            // evita doble submit
            if (btn.disabled) return;

            btn.disabled = true;
            btn.setAttribute('aria-disabled', 'true');

            btn.dataset.originalHtml = originalHTML;
            btn.innerHTML = '<span class="btn-spinner" aria-hidden="true"></span>Creando cuenta...';
        });
    });
</script>


</body>
</html>
