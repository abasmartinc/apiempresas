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
                    <span class="brand-tag">Verificación mercantil y Radar de empresas</span>
                </div>
            </div>
        </div>
    </header>

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
