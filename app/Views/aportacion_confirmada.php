<?php
/**
 * aportacion_confirmada.php
 * Página del enlace "Confirmar los datos" del correo de aportaciones (15-10-2026).
 * Ver Company::confirmContribution y ContributionService::confirmar.
 *
 * GET enseña el botón "Confirmar" (los antivirus del correo abren los enlaces solos);
 * el POST del botón es la confirmación.
 *
 * Variables: $ok (enlace válido), $hecho (ya confirmado), $envio, $empresa (array|null), $ficha (url),
 *            $registro (url del alta rápida o '' si ya tiene sesión)
 */
$nombre = (string) ($empresa['company_name'] ?? '');
?>
<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => ($ok ? 'Confirmar datos' : 'Enlace no válido') . ' | APIEmpresas',
        'excerptText' => 'Confirmación de datos de contacto aportados a una ficha de empresa.',
    ]) ?>
    <meta name="robots" content="noindex, nofollow">
    <style>
        .ac-card { max-width: 520px; margin: 48px auto; padding: 40px 36px; background: #fff; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.08); text-align: center; }
        .ac-card h1 { font-size: 1.5rem; font-weight: 900; color: #0f172a; margin: 0 0 10px; line-height: 1.25; }
        .ac-card p { color: #475569; line-height: 1.6; font-size: 0.95rem; margin: 0 0 14px; }
        .ac-btn { display: inline-block; margin-top: 8px; padding: 12px 20px; border-radius: 12px; background: #0f172a; color: #fff; font-weight: 800; text-decoration: none; }
        .ac-reg { margin-top: 22px; padding: 18px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 16px; }
        .ac-reg p { margin-bottom: 4px; color: #166534; }
        @media (max-width: 560px) { .ac-card { margin: 24px 16px; padding: 28px 20px; } }
    </style>
</head>
<body>
    <?= view('partials/header') ?>
    <main class="ac-card">
        <?php if ($ok && empty($hecho)): ?>
            <h1>Confirma los datos de <?= esc($nombre) ?></h1>
            <p>Pulsa el botón si has enviado tú los datos de contacto de <b><?= esc($nombre) ?></b> y eres de la empresa.</p>
            <form method="post" action="<?= esc(site_url('aportacion/confirmar/' . $envio), 'attr') ?>">
                <?= csrf_field() ?>
                <button type="submit" class="ac-btn" style="border:0;cursor:pointer;font:inherit;font-weight:800">Sí, confirmo los datos</button>
            </form>
        <?php elseif ($ok): ?>
            <h1>¡Gracias! Datos confirmados</h1>
            <p>Hemos anotado que los datos de <b><?= esc($nombre) ?></b> los envía alguien de la empresa.
               En cuanto comprobemos que la web es vuestra, los publicaremos en la ficha.</p>
            <?php if (!empty($registro)): ?>
                <div class="ac-reg">
                    <p><b>¿Quieres que te avisemos si cambia algo de <?= esc($nombre) ?>?</b><br>
                       Crea tu cuenta gratis y te escribimos el día que el BORME publique algo de tu empresa. Sin tarjeta.</p>
                    <a class="ac-btn" href="<?= esc($registro, 'attr') ?>" data-track-click="aportacion_registro" data-track-element="confirmada">Crear mi cuenta gratis</a>
                </div>
                <p style="margin-top:14px"><a href="<?= esc($ficha, 'attr') ?>" style="color:#2563eb;font-weight:700">Ver la ficha</a></p>
            <?php else: ?>
                <a class="ac-btn" href="<?= esc($ficha, 'attr') ?>">Ver la ficha</a>
            <?php endif; ?>
        <?php else: ?>
            <h1>Este enlace no es válido</h1>
            <p>Puede que haya caducado (dura 14 días) o que se haya copiado mal. Si quieres, vuelve a enviar los datos desde la ficha de la empresa.</p>
            <a class="ac-btn" href="<?= esc($ficha, 'attr') ?>">Ir a APIEmpresas</a>
        <?php endif; ?>
    </main>
    <?= view('partials/footer') ?>
</body>
</html>
