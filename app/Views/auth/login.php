<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/login.css') ?>" />
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>


<div class="auth-wrapper">
    <!-- HEADER -->
    <?= view('partials/header') ?>

    <!-- MAIN -->
    <main class="auth-main ">
        <div class="container auth-center ">
            <div class="auth-card">
                <h1>Iniciar sesión</h1>
                <p>Accede a tu panel para ver tu API key, consumo y documentación.</p>
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

                <?php if (session('info')): ?>
                    <div style="background: #eef2ff; border: 1px solid #e0e7ff; color: var(--primary); padding: 12px; border-radius: 8px; margin-bottom: 24px; font-size: 0.9rem; font-weight: 600;">
                        <?= esc(session('info')) ?>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="post" action="<?=site_url() ?>login">
                <?php if ($redirect = session('redirect') ?? request()->getGet('redirect')): ?>
                    <input type="hidden" name="redirect" value="<?= esc($redirect) ?>">
                <?php endif; ?>
                    <?= csrf_field() ?>

                    <div class="">
                        <label for="email">Correo electrónico</label>
                        <input
                                id="email"
                                name="email"
                                type="email"
                                autocomplete="email"
                                required
                                class="input"
                                placeholder="tu@empresa.com"
                                value="<?= esc($prefill_email ?? '') ?>"
                        />
                    </div>

                    <div>
                        <label for="password">Contraseña</label>
                        <input
                                id="password"
                                name="password"
                                type="password"
                                autocomplete="current-password"
                                required
                                class="input"
                                placeholder="Tu contraseña"
                        />
                        <div style="margin-top: 8px; text-align: right;">
                            <a href="<?= site_url('forgot-password') ?>" class="auth-muted" style="font-size: 0.85rem;">¿Has olvidado tu contraseña?</a>
                        </div>
                    </div>

                    <div class="auth-submit-row">
                        <button type="submit" class="btn" id="login-submit">Entrar</button>
                        <p class="auth-muted">
                            ¿No tienes cuenta?
                            <a href="<?=site_url() ?>register">Crear cuenta gratis</a>
                        </p>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <?=view('partials/footer') ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const form = document.querySelector('.auth-form');
        const btn  = document.getElementById('login-submit');

        if (form && btn) {
            form.addEventListener('submit', function () {
                // Evitar dobles envíos
                btn.disabled = true;
                btn.textContent = 'Iniciando sesión…';
            });
        }
    });
</script>



</body>
</html>
