<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/login.css') ?>" />
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <?= view('partials/header') ?>

    <main class="auth-main">
        <div class="container auth-center">
            <div class="auth-card">
                <h1>Recuperar contraseña</h1>
                <p>Introduce tu correo electrónico y te enviaremos un enlace para restablecer tu contraseña.</p>
                
                <?php if (session('error')): ?>
                    <div class="auth-alert-error">
                        <?= esc(session('error')) ?>
                    </div>
                <?php endif; ?>
                
                <?php if (session('message')): ?>
                    <div class="auth-alert-success">
                        <strong><?= esc(session('message')) ?></strong>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="post" action="<?=site_url('forgot-password')?>">
                    <?= csrf_field() ?>
                    <div class="">
                        <label for="email">Correo electrónico</label>
                        <input
                                id="email"
                                name="email"
                                type="email"
                                required
                                class="input"
                                placeholder="tu@empresa.com"
                                value="<?= old('email') ?>"
                        />
                    </div>

                    <div class="auth-submit-row">
                        <button type="submit" class="btn" id="forgot-submit">Enviar enlace</button>
                        <p class="auth-muted">
                            <a href="<?=site_url('enter') ?>">Volver al login</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?=view('partials/footer') ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('.auth-form');
        const btn  = document.getElementById('forgot-submit');
        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.textContent = 'Enviando…';
            });
        }
    });
</script>
</body>
</html>
