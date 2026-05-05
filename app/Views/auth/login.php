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
        <div class="auth-form-container">
            <div class="auth-form-header">
                <div class="auth-form-icon-badge">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><circle cx="12" cy="12" r="4"></circle><line x1="12" y1="12" x2="12.01" y2="12"></line></svg>
                </div>
                <h1>Acceso al panel</h1>
                <p>Introduce tus credenciales para continuar</p>
            </div>

            <!-- ALERTS -->
            <?php if (session('error')): ?>
                <div class="auth-alert-error">
                    <?= esc(session('error')) ?>
                </div>
            <?php endif; ?>
            <?php if (session('message')): ?>
                <div class="auth-alert-success">
                    <?= esc(session('message')) ?>
                </div>
            <?php endif; ?>
            <?php if (session('info')): ?>
                <div class="auth-alert-info">
                    <?= esc(session('info')) ?>
                </div>
            <?php endif; ?>

            <form class="auth-form" method="post" action="<?=site_url() ?>login">
                <?= csrf_field() ?>
                <?php if ($redirect = session('redirect') ?? request()->getGet('redirect')): ?>
                    <input type="hidden" name="redirect" value="<?= esc($redirect) ?>">
                <?php endif; ?>

                <div class="auth-field-group">
                    <label for="email">Email profesional</label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        required
                        class="auth-input"
                        placeholder="nombre@empresa.com"
                        value="<?= esc($prefill_email ?? '') ?>"
                    />
                </div>

                <div class="auth-field-group">
                    <div class="auth-label-row">
                        <label for="password">Contraseña</label>
                        <a href="<?= site_url('forgot-password') ?>" class="auth-forgot-link">¿Olvidaste la clave?</a>
                    </div>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                        class="auth-input"
                        placeholder="••••••••"
                    />
                </div>

                <button type="submit" class="auth-btn-primary" id="login-submit">Entrar en el panel</button>

                <div class="auth-separator">O</div>

                <div class="social-login-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-top: 16px;">
                    <a href="<?= site_url('auth/google') ?>" class="social-btn google" style="display: flex; align-items: center; justify-content: center; gap: 8px; background: #ffffff; color: #1e293b; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px; font-size: 14px; font-weight: 700; text-decoration: none; transition: all 0.25s ease;">
                        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" width="18">
                        <span>Google</span>
                    </a>
                    <a href="<?= site_url('auth/github') ?>" class="social-btn github" style="display: flex; align-items: center; justify-content: center; gap: 8px; border-radius: 12px; padding: 12px; font-size: 14px; font-weight: 700; text-decoration: none; transition: all 0.25s ease;">
                        <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.041-1.416-4.041-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>
                        <span>GitHub</span>
                    </a>
                </div>
            </form>

            <div class="auth-form-footer">
                ¿No tienes cuenta profesional? <a href="<?=site_url() ?>register">Empezar ahora</a>
            </div>
        </div>

        <div class="auth-legal-footer">
            © <?= date('Y') ?> AlertaEmpresas. <a href="#" data-modal-target="modalTerms">Aviso Legal</a> · <a href="#" data-modal-target="modalPrivacy">Privacidad</a>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('.auth-form');
        const btn  = document.getElementById('login-submit');

        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.textContent = 'Iniciando sesión…';
            });
        }
    });
</script>

<?= view('partials/legal_modals') ?>
<?= view('scripts') ?>

</body>
</html>
