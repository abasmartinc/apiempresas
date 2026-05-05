<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/login.css?v=' . time()) ?>" />
</head>

<body>

<div class="auth-split-wrapper">
    <!-- LEFT SIDE: BRANDING -->
    <?= view('auth/partials/branding_side') ?>

    <!-- RIGHT SIDE: FORM -->
    <div class="auth-form-side">
        <div class="auth-form-container" style="max-width: 520px;">
            <div class="auth-form-header">
                <div class="auth-form-icon-badge">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><line x1="20" y1="8" x2="20" y2="14"></line><line x1="23" y1="11" x2="17" y2="11"></line></svg>
                </div>
                <h1>Crea tu cuenta gratis</h1>
                <p>Sin tarjeta, sin permanencias. Solo necesitas un correo profesional.</p>
            </div>

            <!-- ALERTS / ERRORS -->
            <?php
            /** @var \CodeIgniter\Validation\Validation $validation */
            $validation = $validation ?? \Config\Services::validation();
            ?>

            <?php if (session('error')): ?>
                <div class="auth-alert-error">
                    <?= esc(session('error')) ?>
                </div>
            <?php endif; ?>

            <?php if ($validation->getErrors()): ?>
                <div class="auth-alert-error">
                    <ul style="margin:0; padding-left: 20px;">
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

                <div class="auth-row-inline" style="display: flex; gap: 20px;">
                    <div class="auth-field-group" style="flex: 1;">
                        <label for="name">Nombre y apellidos</label>
                        <input
                                class="auth-input"
                                type="text"
                                id="name"
                                name="name"
                                autocomplete="name"
                                required
                                placeholder="Ej. Ana García"
                                value="<?= esc(old('name')) ?>"
                        />
                    </div>
                    <div class="auth-field-group" style="flex: 1;">
                        <label for="company">Empresa (opcional)</label>
                        <input
                                class="auth-input"
                                type="text"
                                id="company"
                                name="company"
                                autocomplete="organization"
                                placeholder="Ej. Tech SL"
                                value="<?= esc(old('company')) ?>"
                        />
                    </div>
                </div>

                <div class="auth-field-group">
                    <label for="email">Correo electrónico profesional</label>
                    <input
                            class="auth-input"
                            type="email"
                            id="email"
                            name="email"
                            autocomplete="email"
                            required
                            placeholder="tu@empresa.com"
                            value="<?= esc(old('email')) ?>"
                    />
                </div>

                <div class="auth-field-group">
                    <label for="password">Contraseña</label>
                    <input
                            class="auth-input"
                            type="password"
                            id="password"
                            name="password"
                            autocomplete="new-password"
                            required
                            minlength="8"
                            placeholder="Mínimo 8 caracteres"
                    />
                </div>

                <div class="auth-checkbox" style="display: flex; align-items: flex-start; gap: 10px; font-size: 13px; color: #64748b; margin-bottom: 10px;">
                    <input
                            type="checkbox"
                            id="terms"
                            name="terms"
                            value="1"
                        <?= old('terms') ? 'checked' : '' ?>
                            required
                            style="margin-top: 3px;"
                    />
                    <label for="terms" style="font-weight: 400; margin: 0; line-height: 1.4;">
                        Acepto la <a href="#" data-modal-target="modalPrivacy" style="color: #2563eb; font-weight: 600; text-decoration: none;">Política de privacidad</a>
                        y los <a href="#" data-modal-target="modalTerms" style="color: #2563eb; font-weight: 600; text-decoration: none;">Términos de servicio</a>.
                    </label>
                </div>

                <button id="registerSubmit" type="submit" class="auth-btn-primary">
                    Crear cuenta gratis
                </button>

                <div class="auth-separator">O regístrate con un clic</div>

                <a href="<?= site_url('auth/google') ?>" class="auth-btn-google">
                    <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" alt="Google Logo">
                    Registrarme con Google
                </a>
            </form>

            <div class="auth-form-footer">
                ¿Ya tienes cuenta profesional? <a href="<?=site_url() ?>enter">Inicia sesión</a>
            </div>
        </div>

        <div class="auth-legal-footer">
            © <?= date('Y') ?> AlertaEmpresas. <a href="#" data-modal-target="modalTerms">Aviso Legal</a> · <a href="#" data-modal-target="modalPrivacy">Privacidad</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('registerForm');
        const btn  = document.getElementById('registerSubmit');

        if (!form || !btn) return;

        form.addEventListener('submit', () => {
            if (btn.disabled) return;
            btn.disabled = true;
            btn.innerHTML = 'Creando cuenta...';
        });
    });
</script>

<?= view('partials/legal_modals') ?>
<?= view('scripts') ?>

</body>
</html>
