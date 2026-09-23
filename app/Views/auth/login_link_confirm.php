<?php
/**
 * auth/login_link_confirm.php
 * Pantalla del enlace de acceso (GET acceso/{token}).
 *
 * El botón hace un POST: el GET de esta página NO abre sesión ni gasta el enlace,
 * porque los antivirus de correo abren los enlaces para analizarlos. Ver
 * App\Services\LoginLinkService.
 *
 * Variables:
 * - $valido (bool)
 * - $token  (string)
 * - $email  (string)
 */
?>
<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Entrar en tu cuenta | APIEmpresas',
        'excerptText' => 'Acceso a tu cuenta de APIEmpresas.',
    ]) ?>
    <meta name="robots" content="noindex, nofollow">
    <meta name="referrer" content="no-referrer">
    <style>
        .ll-card { max-width: 480px; margin: 56px auto; padding: 40px 36px; background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.08); text-align: center; }
        .ll-card h1 { font-size: 1.55rem; font-weight: 900; color: #0f172a; margin: 0 0 10px; letter-spacing: -0.02em; }
        .ll-card p { color: #475569; line-height: 1.6; font-size: 0.95rem; margin: 0 0 22px; }
        .ll-btn { display: block; width: 100%; box-sizing: border-box; padding: 15px 18px; border-radius: 12px; font-weight: 800; font-size: 1rem; border: 0; cursor: pointer; background: #2563eb; color: #fff; text-decoration: none; box-shadow: 0 8px 18px rgba(37,99,235,0.28); }
        .ll-btn:hover { background: #1d4ed8; }
        .ll-sec { display: inline-block; margin-top: 16px; color: #2563eb; font-weight: 700; font-size: 0.88rem; }
    </style>
</head>
<body>
    <?= view('partials/header') ?>

    <main class="container">
        <div class="ll-card">
            <?php if (!empty($valido)): ?>
                <h1>Entrar en tu cuenta</h1>
                <p>Vas a entrar como <strong style="color:#0f172a;"><?= esc($email) ?></strong>.</p>
                <form action="<?= site_url('acceso') ?>" method="POST">
                    <?= csrf_field() ?>
                    <input type="hidden" name="token" value="<?= esc($token, 'attr') ?>">
                    <button type="submit" class="ll-btn" data-loading="Entrando…">Entrar →</button>
                </form>
            <?php else: ?>
                <h1>Este enlace ya no vale</h1>
                <p>Ha caducado o ya se ha usado: cada enlace sirve una sola vez y durante
                    <?= (int) \App\Services\LoginLinkService::VALIDEZ_MIN ?> minutos. Pide uno nuevo o entra de otra forma.</p>
                <a class="ll-btn" href="<?= site_url('enter') ?>">Entrar</a>
                <a class="ll-sec" href="<?= site_url('forgot-password') ?>">He olvidado mi contraseña</a>
            <?php endif; ?>
        </div>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
