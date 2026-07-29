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
                <h1><?= lang('Auth.forgot_title') ?></h1>
                <p><?= lang('Auth.forgot_desc') ?></p>
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
                    <label for="email"><?= lang('Auth.email_label') ?></label>
                    <input
                        id="email"
                        name="email"
                        type="email"
                        autocomplete="email"
                        required
                        class="auth-input"
                        placeholder="<?= lang('Auth.email_ph') ?>"
                        value="<?= old('email') ?>"
                    />
                </div>

                <button type="submit" class="auth-btn-primary" id="forgot-submit"><?= lang('Auth.btn_send_link') ?></button>
            </form>

            <div class="auth-form-footer">
                <?= lang('Auth.remembered_password') ?> <a href="<?=site_url('enter') ?>"><?= lang('Auth.login_link') ?></a>
            </div>
        </div>

        <div class="auth-legal-footer">
            © <?= date('Y') ?> ApiEmpresas. <a href="#" data-modal-target="modalTerms"><?= lang('Auth.legal_notice') ?></a> · <a href="#" data-modal-target="modalPrivacy"><?= lang('Auth.privacy') ?></a>
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
                btn.textContent = '<?= lang('Auth.btn_sending') ?>';
            });
        }
    });
</script>

<?= view('partials/legal_modals') ?>
<?= view('scripts') ?>

</body>
</html>
