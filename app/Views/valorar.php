<?php
/**
 * Página de valoración desde el correo (14-10-2026). Ver Controllers/Valoraciones.php.
 * $estado: 'form', 'gracias' o 'invalido'. No se indexa.
 */
$en = ($lang ?? 'es') === 'en';
$t = $en ? [
    'title'    => 'Rate APIEmpresas',
    'h1'       => 'How would you rate the API?',
    'sub'      => 'One minute, and it helps us decide what to improve first.',
    'labels'   => [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very good', 5 => 'Excellent'],
    'pick'     => 'Pick a score from 1 to 5',
    'c_high'   => 'What do you use it for, and what works best for you? (optional)',
    'c_low'    => 'What went wrong or what is missing? Only our team will read it. (optional)',
    'ph'       => 'E.g. we use it to verify suppliers when they sign up…',
    'citar'    => 'You can show my name and company next to my review on the website.',
    'send'     => 'Send rating',
    'thanks'   => 'Thank you!',
    'thanks_h' => 'We have saved your rating. Thank you for taking the time.',
    'thanks_l' => 'We have saved your rating. We will read your comment and get back to you if we can help.',
    'back'     => 'Go to my dashboard',
    'bad'      => 'This link is not valid',
    'bad_t'    => 'It may be incomplete. Open the link from the email again or rate us from your dashboard.',
    'hello'    => 'Hi',
] : [
    'title'    => 'Valora APIEmpresas',
    'h1'       => '¿Qué nota le das a la API?',
    'sub'      => 'Es un minuto y nos ayuda a decidir qué mejorar primero.',
    'labels'   => [1 => 'Mala', 2 => 'Regular', 3 => 'Correcta', 4 => 'Muy buena', 5 => 'Excelente'],
    'pick'     => 'Elige una nota del 1 al 5',
    'c_high'   => '¿Para qué la usas y qué es lo que mejor te funciona? (opcional)',
    'c_low'    => '¿Qué ha fallado o qué echas en falta? Solo lo leerá nuestro equipo. (opcional)',
    'ph'       => 'Ej.: la usamos para verificar proveedores al darlos de alta…',
    'citar'    => 'Podéis mostrar mi nombre y mi empresa junto a mi opinión en la web.',
    'send'     => 'Enviar valoración',
    'thanks'   => '¡Gracias!',
    'thanks_h' => 'Hemos guardado tu valoración. Gracias por dedicarnos este rato.',
    'thanks_l' => 'Hemos guardado tu valoración. Leeremos tu comentario y te escribiremos si podemos ayudarte.',
    'back'     => 'Ir a mi panel',
    'bad'      => 'Este enlace no es válido',
    'bad_t'    => 'Puede que esté incompleto. Vuelve a abrirlo desde el correo o valóranos desde tu panel.',
    'hello'    => 'Hola',
];
$rating = (int) ($rating ?? 0);
?>
<!doctype html>
<html lang="<?= $en ? 'en' : 'es' ?>">

<head>
    <?= view('partials/head', [
        'title'       => $t['title'] . ' | APIEmpresas',
        'excerptText' => $t['sub'],
        'robots'      => 'noindex,nofollow',
    ]) ?>
    <style>
        .val-page { background: radial-gradient(900px 420px at 50% -10%, #e8f0ff 0%, #f8fafc 60%, #ffffff 100%); padding: 56px 16px 80px; }
        .val-card { max-width: 560px; margin: 0 auto; background: #fff; border: 1px solid #e2e8f0; border-radius: 22px; padding: 36px 32px; box-shadow: 0 30px 60px -32px rgba(15,23,42,.35); text-align: center; }
        .val-card h1 { font-size: 1.6rem; font-weight: 800; letter-spacing: -.02em; color: #0f172a; margin: 0 0 8px; }
        .val-card > p { color: #64748b; margin: 0 0 24px; }
        .val-hello { font-size: .9rem; color: #64748b; margin-bottom: 6px; }
        .val-stars { display: flex; justify-content: center; gap: 8px; margin-bottom: 6px; }
        .val-stars input { position: absolute; opacity: 0; pointer-events: none; }
        .val-stars label { cursor: pointer; width: 52px; height: 52px; border-radius: 14px; border: 1px solid #e2e8f0; background: #f8fafc; display: flex; align-items: center; justify-content: center; color: #cbd5e1; transition: all .15s; }
        .val-stars label svg { width: 28px; height: 28px; }
        .val-stars label.on { color: #f59e0b; background: #fffbeb; border-color: #fde68a; }
        .val-stars input:focus-visible + label { outline: 2px solid #2563eb; outline-offset: 2px; }
        .val-label { min-height: 22px; font-weight: 700; color: #0f172a; font-size: .95rem; margin-bottom: 20px; }
        .val-form { text-align: left; }
        .val-form label.val-q { display: block; font-weight: 700; color: #0f172a; font-size: .92rem; margin-bottom: 8px; }
        .val-form textarea { width: 100%; min-height: 110px; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px; font: inherit; font-size: .95rem; resize: vertical; box-sizing: border-box; }
        .val-form textarea:focus { outline: none; border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
        .val-cite { display: flex; gap: 10px; align-items: flex-start; margin: 14px 0 0; font-size: .88rem; color: #475569; }
        .val-cite input { margin-top: 3px; }
        .val-btn { margin-top: 22px; width: 100%; background: #2563eb; color: #fff; border: 0; border-radius: 12px; padding: 14px; font-weight: 800; font-size: 1rem; cursor: pointer; }
        .val-btn:disabled { background: #94a3b8; cursor: not-allowed; }
        .val-ok { width: 56px; height: 56px; border-radius: 50%; background: #ecfdf5; color: #059669; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
        .val-link { display: inline-block; margin-top: 8px; color: #2563eb; font-weight: 800; text-decoration: none; }
        @media (max-width: 480px) { .val-card { padding: 28px 18px; } .val-stars label { width: 46px; height: 46px; } }
    </style>
</head>

<body>
    <?= view($en ? 'partials/header_en' : 'partials/header') ?>

    <main class="val-page">
        <div class="val-card">
            <?php if ($estado === 'invalido'): ?>
                <h1><?= esc($t['bad']) ?></h1>
                <p><?= esc($t['bad_t']) ?></p>
                <a class="val-link" href="<?= site_url('dashboard') ?>"><?= esc($t['back']) ?> →</a>

            <?php elseif ($estado === 'gracias'): ?>
                <div class="val-ok"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></div>
                <h1><?= esc($t['thanks']) ?></h1>
                <p><?= esc($rating >= 4 ? $t['thanks_h'] : $t['thanks_l']) ?></p>
                <a class="val-link" href="<?= site_url('dashboard') ?>"><?= esc($t['back']) ?> →</a>

            <?php else: ?>
                <?php if (!empty($nombre)): ?><div class="val-hello"><?= esc($t['hello']) ?> <?= esc($nombre) ?>,</div><?php endif; ?>
                <h1><?= esc($t['h1']) ?></h1>
                <p><?= esc($t['sub']) ?></p>

                <form method="post" action="<?= site_url('valorar') ?>" class="val-form" id="valForm">
                    <input type="hidden" name="u" value="<?= (int) $uid ?>">
                    <input type="hidden" name="t" value="<?= esc($firma) ?>">

                    <div class="val-stars" role="radiogroup" aria-label="<?= esc($t['pick']) ?>">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <input type="radio" name="rating" id="val-r<?= $i ?>" value="<?= $i ?>" <?= $rating === $i ? 'checked' : '' ?>>
                            <label for="val-r<?= $i ?>" data-v="<?= $i ?>" title="<?= esc($t['labels'][$i]) ?>" class="<?= $i <= $rating ? 'on' : '' ?>">
                                <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                <span class="sr-only" style="position:absolute;width:1px;height:1px;overflow:hidden;clip:rect(0,0,0,0);"><?= $i ?></span>
                            </label>
                        <?php endfor; ?>
                    </div>
                    <div class="val-label" id="valLabel" style="text-align:center;"><?= esc($rating ? $t['labels'][$rating] : $t['pick']) ?></div>

                    <label class="val-q" for="valComment" id="valQ"><?= esc($rating && $rating <= 3 ? $t['c_low'] : $t['c_high']) ?></label>
                    <textarea name="comment" id="valComment" maxlength="1000" placeholder="<?= esc($t['ph']) ?>"></textarea>

                    <label class="val-cite" id="valCite" style="<?= $rating && $rating <= 3 ? 'display:none' : '' ?>">
                        <input type="checkbox" name="citar" value="1">
                        <span><?= esc($t['citar']) ?></span>
                    </label>

                    <button type="submit" class="val-btn" id="valBtn" <?= $rating ? '' : 'disabled' ?>><?= esc($t['send']) ?></button>
                </form>

                <script>
                (function () {
                    var labels = <?= json_encode($t['labels'], JSON_UNESCAPED_UNICODE) ?>;
                    var txt = { high: <?= json_encode($t['c_high'], JSON_UNESCAPED_UNICODE) ?>, low: <?= json_encode($t['c_low'], JSON_UNESCAPED_UNICODE) ?> };
                    var stars = document.querySelectorAll('.val-stars label');
                    function pintar(v) {
                        stars.forEach(function (s) { s.classList.toggle('on', +s.dataset.v <= v); });
                        document.getElementById('valLabel').textContent = labels[v];
                        document.getElementById('valQ').textContent = v <= 3 ? txt.low : txt.high;
                        document.getElementById('valCite').style.display = v <= 3 ? 'none' : '';
                        document.getElementById('valBtn').disabled = false;
                    }
                    document.querySelectorAll('.val-stars input').forEach(function (i) {
                        i.addEventListener('change', function () { pintar(+i.value); });
                    });
                })();
                </script>
            <?php endif; ?>
        </div>
    </main>

    <?= view($en ? 'partials/footer_en' : 'partials/footer', ['sinWidgets' => true]) ?>
</body>
</html>
