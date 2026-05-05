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

                <a href="<?= site_url('auth/google') ?>" class="auth-btn-google">
                    <img src="https://www.gstatic.com/images/branding/product/1x/gsa_512dp.png" alt="Google Logo">
                    Continuar con Google
                </a>
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
