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
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3y-3z"></path></svg>
                </div>
                <h1>Recuperar contraseña</h1>
                <p>Introduce tu correo electrónico y te enviaremos un enlace para restablecer tu clave.</p>
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

            <form class="auth-form" method="post" action="<?=site_url('forgot-password')?>">
                <?= csrf_field() ?>

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
                        value="<?= old('email') ?>"
                    />
                </div>

                <button type="submit" class="auth-btn-primary" id="forgot-submit">Enviar enlace de recuperación</button>
            </form>

            <div class="auth-form-footer">
                ¿Recordaste tu contraseña? <a href="<?=site_url('enter') ?>">Inicia sesión</a>
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
        const btn  = document.getElementById('forgot-submit');

        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.textContent = 'Enviando enlace…';
            });
        }
    });
</script>

<?= view('partials/legal_modals') ?>
<?= view('scripts') ?>

</body>
</html>
