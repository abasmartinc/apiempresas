<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/login.css') ?>" />
</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <header>
        <div class="container nav">
            <div class="brand">
                <a href="<?=site_url() ?>">
                    <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64">
                        <defs>
                            <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10">
                                <stop stop-color="#2152FF"/>
                                <stop offset=".65" stop-color="#5C7CFF"/>
                                <stop offset="1" stop-color="#12B48A"/>
                            </linearGradient>
                        </defs>
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)"/>
                        <path d="M18 33 L28 43 L46 22" stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    </svg>
                </a>
                <div class="brand-text">
                    <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                    <span class="brand-tag">Verificación mercantil en segundos</span>
                </div>
            </div>
        </div>
    </header>

    <main class="auth-main">
        <div class="container auth-center">
            <div class="auth-card">
                <h1>Nueva contraseña</h1>
                <p>Establece una nueva contraseña para acceder a tu cuenta.</p>
                
                <?php if (session('error')): ?>
                    <div class="auth-alert-error">
                        <?= esc(session('error')) ?>
                    </div>
                <?php endif; ?>

                <form class="auth-form" method="post" action="<?=site_url('reset-password')?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= esc($token) ?>">

                    <div class="">
                        <label for="password">Nueva contraseña</label>
                        <input
                                id="password"
                                name="password"
                                type="password"
                                required
                                class="input"
                                placeholder="Mínimo 8 caracteres"
                        />
                    </div>

                    <div class="">
                        <label for="password_confirm">Confirmar contraseña</label>
                        <input
                                id="password_confirm"
                                name="password_confirm"
                                type="password"
                                required
                                class="input"
                                placeholder="Repite la contraseña"
                        />
                    </div>

                    <div class="auth-submit-row">
                        <button type="submit" class="btn" id="reset-submit">Cambiar contraseña</button>
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
        const btn  = document.getElementById('reset-submit');
        if (form && btn) {
            form.addEventListener('submit', function () {
                btn.disabled = true;
                btn.textContent = 'Actualizando…';
            });
        }
    });
</script>
</body>
</html>
