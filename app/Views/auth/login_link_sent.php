<?php
/**
 * auth/login_link_sent.php
 * "Ya tienes cuenta: revisa tu correo". Lo pinta Register::quick_store cuando el
 * email introducido ya existe (antes abría sesión sin comprobar nada).
 *
 * Variables:
 * - $email    (string) el que ha escrito
 * - $estado   'enviado' | 'reciente' | 'error'
 * - $redirect (string) ruta interna a la que volver
 * - $intent, $cif (string, opcionales) para el botón de Google
 */
$estado   = $estado ?? 'enviado';
$redirect = $redirect ?? 'dashboard';

// Google con el mismo destino e intención: quien tiene la cuenta con Google entra
// en un clic sin esperar al correo.
$googleUrl = site_url('auth/google') . '?' . http_build_query(array_filter([
    'intent'   => $intent ?? '',
    'cif'      => $cif ?? '',
    'redirect' => $redirect,
]));
$passUrl = site_url('enter') . '?redirect=' . urlencode($redirect);
?>
<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Revisa tu correo | APIEmpresas',
        'excerptText' => 'Te hemos enviado un enlace para entrar en tu cuenta.',
    ]) ?>
    <meta name="robots" content="noindex, nofollow">
    <style>
        .ll-card { max-width: 520px; margin: 48px auto; padding: 40px 36px; background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.08); text-align: center; }
        .ll-icon { width: 60px; height: 60px; border-radius: 18px; background: #eff6ff; border: 1px solid #dbeafe; color: #2563eb; display: inline-flex; align-items: center; justify-content: center; margin-bottom: 20px; }
        .ll-card h1 { font-size: 1.6rem; font-weight: 900; color: #0f172a; margin: 0 0 10px; letter-spacing: -0.02em; line-height: 1.2; }
        .ll-card p { color: #475569; line-height: 1.6; font-size: 0.95rem; margin: 0 0 14px; }
        .ll-btn { display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; box-sizing: border-box; padding: 13px 18px; border-radius: 12px; font-weight: 800; font-size: 0.95rem; text-decoration: none; cursor: pointer; }
        .ll-btn-google { background: #fff; color: #1e293b; border: 1.5px solid #cbd5e1; }
        .ll-btn-google:hover { background: #f8fafc; border-color: #94a3b8; }
        .ll-sep { display: flex; align-items: center; gap: 12px; margin: 22px 0 16px; color: #94a3b8; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; }
        .ll-sep::before, .ll-sep::after { content: ""; flex: 1; height: 1px; background: #e2e8f0; }
        .ll-links { display: flex; justify-content: center; flex-wrap: wrap; gap: 6px 18px; font-size: 0.85rem; }
        .ll-links a, .ll-links button { color: #2563eb; font-weight: 700; text-decoration: underline; background: none; border: 0; padding: 0; cursor: pointer; font-size: 0.85rem; font-family: inherit; }
        .ll-note { margin-top: 18px; font-size: 0.8rem; color: #64748b; }
        .ll-aviso { background: #fef2f2; border: 1px solid #fecaca; color: #b91c1c; border-radius: 10px; padding: 10px 12px; font-size: 0.85rem; font-weight: 600; margin-bottom: 16px; }
    </style>
</head>
<body>
    <?= view('partials/header') ?>

    <main class="container">
        <div class="ll-card">
            <div class="ll-icon">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            </div>

            <?php if ($estado === 'error'): ?>
                <h1>Ya tienes cuenta</h1>
                <div class="ll-aviso">No hemos podido enviarte el enlace ahora mismo.</div>
                <p>Entra con Google o con tu contraseña. Si no la recuerdas, puedes crear una nueva en un minuto.</p>
            <?php else: ?>
                <h1>Ya tienes cuenta. Revisa tu correo</h1>
                <p>
                    Te hemos enviado un enlace a <strong style="color:#0f172a;"><?= esc($email) ?></strong>
                    para entrar sin contraseña. Al pulsarlo vuelves justo a donde estabas.
                </p>
                <p class="ll-note" style="margin-top:0;">
                    Caduca en <?= (int) \App\Services\LoginLinkService::VALIDEZ_MIN ?> minutos y solo sirve una vez.
                    <?php if ($estado === 'reciente'): ?>
                        <br>Te lo acabamos de enviar hace un momento; usa ese mismo.
                    <?php endif; ?>
                </p>
            <?php endif; ?>

            <div class="ll-sep">o entra ahora</div>

            <a class="ll-btn ll-btn-google" href="<?= esc($googleUrl, 'attr') ?>">
                <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
                Continuar con Google
            </a>

            <div class="ll-links" style="margin-top: 16px;">
                <a href="<?= esc($passUrl, 'attr') ?>">Entrar con contraseña</a>
                <a href="<?= site_url('forgot-password') ?>">He olvidado mi contraseña</a>
            </div>

            <?php if ($estado !== 'error'): ?>
                <!-- Reenviar: el mismo formulario del registro rápido. El servicio no
                     manda otro si el anterior salió hace menos de un minuto. -->
                <form action="<?= site_url('register/quick_store') ?>" method="POST" class="ll-note">
                    <?= csrf_field() ?>
                    <input type="hidden" name="email" value="<?= esc($email, 'attr') ?>">
                    <input type="hidden" name="redirect" value="<?= esc($redirect, 'attr') ?>">
                    <input type="hidden" name="intent" value="<?= esc($intent ?? '', 'attr') ?>">
                    <input type="hidden" name="cif" value="<?= esc($cif ?? '', 'attr') ?>">
                    ¿No te llega? Mira en spam o
                    <span class="ll-links" style="display:inline;"><button type="submit">envíalo de nuevo</button></span>
                </form>
            <?php endif; ?>
        </div>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
