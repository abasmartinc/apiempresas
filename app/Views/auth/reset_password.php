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
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <h1><?= lang('Auth.reset_title') ?></h1>
                <p><?= lang('Auth.reset_desc') ?></p>
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

            <form class="auth-form" method="post" action="<?=site_url('reset-password')?>">
                <?= csrf_field() ?>
                <input type="hidden" name="token" value="<?= esc($token) ?>">

                <div class="auth-field-group">
                    <label for="password"><?= lang('Auth.new_password') ?></label>
                    <input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="auth-input"
                        placeholder="<?= lang('Auth.new_password_ph') ?>"
                    />
                </div>

                <div class="auth-field-group">
                    <label for="password_confirm"><?= lang('Auth.confirm_password') ?></label>
                    <input
                        id="password_confirm"
                        name="password_confirm"
                        type="password"
                        autocomplete="new-password"
                        required
                        class="auth-input"
                        placeholder="<?= lang('Auth.confirm_password_ph') ?>"
                    />
                </div>

                <button type="submit" class="auth-btn-primary" id="reset-submit"><?= lang('Auth.btn_update') ?></button>
            </form>

            <div class="auth-form-footer">
                <?= lang('Auth.cancel_reset') ?> <a href="<?=site_url('enter') ?>"><?= lang('Auth.back_to_login') ?></a>
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
        const btn  = document.getElementById('reset-submit');

        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.textContent = '<?= lang('Auth.btn_updating') ?>';
            });
        }
    });
</script>

<?= view('partials/legal_modals') ?>
<?= view('scripts') ?>

</body>
</html>
