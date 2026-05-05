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

                <div class="social-login-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px; margin-top: 16px;">
                    <a href="<?= site_url('auth/google') ?>" class="social-btn google" style="display: flex; align-items: center; justify-content: center; gap: 6px; background: #ffffff; color: #1e293b; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 5px; font-size: 13px; font-weight: 700; text-decoration: none; transition: all 0.25s ease;">
                        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" width="16">
                        <span>Google</span>
                    </a>
                    <a href="<?= site_url('auth/github') ?>" class="social-btn github" style="display: flex; align-items: center; justify-content: center; gap: 6px; border-radius: 12px; padding: 10px 5px; font-size: 13px; font-weight: 700; text-decoration: none; transition: all 0.25s ease;">
                        <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.041-1.416-4.041-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                        <span>GitHub</span>
                    </a>
                    <a href="<?= site_url('auth/linkedin') ?>" class="social-btn linkedin" style="display: flex; align-items: center; justify-content: center; gap: 6px; border-radius: 12px; padding: 10px 5px; font-size: 13px; font-weight: 700; text-decoration: none; transition: all 0.25s ease;">
                        <svg width="16" height="16" viewBox="0 0 24 24" aria-hidden="true"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                        <span>LinkedIn</span>
                    </a>
                </div>
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
