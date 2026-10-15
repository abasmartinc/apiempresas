<?php
/**
 * Tarjeta "¿Qué tal te está funcionando la API?" del panel (14-10-2026).
 * Sale solo si Dashboard pasa $showReviewCard (ver App\Libraries\Valoraciones::mostrarEnPanel).
 * Guarda en POST dashboard/valoracion; "Ahora no" la oculta DESCARTE_DIAS días.
 */
$en = str_starts_with((string) service('request')->getLocale(), 'en');
$t = $en ? [
    'kicker' => 'Your opinion',
    'h'      => 'How is the API working for you?',
    'sub'    => 'Give us a score. It takes a few seconds and helps us decide what to improve first.',
    'labels' => [1 => 'Poor', 2 => 'Fair', 3 => 'Good', 4 => 'Very good', 5 => 'Excellent'],
    'c_high' => 'What do you use it for, and what works best? (optional)',
    'c_low'  => 'What went wrong or what is missing? Only our team will read it. (optional)',
    'ph'     => 'E.g. we verify suppliers when they sign up…',
    'citar'  => 'You can show my name and company next to my review on the website.',
    'send'   => 'Send',
    'later'  => 'Not now',
    'ok_h'   => 'Thank you! We have saved your rating.',
    'ok_l'   => 'Thank you. We will read it and get back to you if we can help.',
    'err'    => 'It could not be sent. Please try again.',
] : [
    'kicker' => 'Tu opinión',
    'h'      => '¿Qué tal te está funcionando la API?',
    'sub'    => 'Danos una nota. Son unos segundos y nos ayuda a decidir qué mejorar primero.',
    'labels' => [1 => 'Mala', 2 => 'Regular', 3 => 'Correcta', 4 => 'Muy buena', 5 => 'Excelente'],
    'c_high' => '¿Para qué la usas y qué es lo que mejor te funciona? (opcional)',
    'c_low'  => '¿Qué ha fallado o qué echas en falta? Solo lo leerá nuestro equipo. (opcional)',
    'ph'     => 'Ej.: la usamos para verificar proveedores al darlos de alta…',
    'citar'  => 'Podéis mostrar mi nombre y mi empresa junto a mi opinión en la web.',
    'send'   => 'Enviar',
    'later'  => 'Ahora no',
    'ok_h'   => '¡Gracias! Hemos guardado tu valoración.',
    'ok_l'   => 'Gracias. La leeremos y te escribiremos si podemos ayudarte.',
    'err'    => 'No se ha podido enviar. Inténtalo de nuevo.',
];
?>
<style>
        .rv-card { position: relative; margin: 16px 0 0; background: linear-gradient(135deg, #ffffff 0%, #f5f8ff 100%); border: 1px solid #dbeafe; border-radius: 18px; padding: 20px 22px; box-shadow: 0 12px 30px -22px rgba(37,99,235,.45); }
        .rv-top { display: flex; align-items: center; gap: 18px; flex-wrap: wrap; }
        .rv-txt { flex: 1 1 280px; min-width: 0; }
        .rv-kicker { font-size: 11px; font-weight: 800; letter-spacing: .07em; text-transform: uppercase; color: #2563eb; margin-bottom: 2px; }
        .rv-txt h3 { margin: 0 0 2px; font-size: 1.05rem; font-weight: 800; color: #0f172a; }
        .rv-txt p { margin: 0; font-size: .88rem; color: #64748b; }
        .rv-stars { display: flex; gap: 6px; align-items: center; }
        .rv-stars button { width: 40px; height: 40px; border-radius: 11px; border: 1px solid #e2e8f0; background: #fff; color: #cbd5e1; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: all .15s; padding: 0; }
        .rv-stars button svg { width: 22px; height: 22px; }
        .rv-stars button.on { color: #f59e0b; background: #fffbeb; border-color: #fde68a; }
        .rv-stars button:focus-visible { outline: 2px solid #2563eb; outline-offset: 2px; }
        .rv-label { font-size: .8rem; font-weight: 700; color: #475569; min-width: 72px; }
        .rv-close { position: absolute; top: 10px; right: 12px; background: none; border: 0; color: #94a3b8; font-size: .8rem; font-weight: 700; cursor: pointer; padding: 4px 6px; }
        .rv-close:hover { color: #475569; }
        .rv-more { display: none; margin-top: 16px; padding-top: 16px; border-top: 1px solid #e2e8f0; }
        .rv-more.open { display: block; }
        .rv-more label.rv-q { display: block; font-weight: 700; color: #0f172a; font-size: .88rem; margin-bottom: 8px; }
        .rv-more textarea { width: 100%; min-height: 80px; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 12px; font: inherit; font-size: .9rem; resize: vertical; box-sizing: border-box; background: #fff; }
        .rv-more textarea:focus { outline: none; border-color: #93c5fd; box-shadow: 0 0 0 3px rgba(37,99,235,.12); }
        .rv-row { display: flex; align-items: center; justify-content: space-between; gap: 14px; flex-wrap: wrap; margin-top: 12px; }
        .rv-cite { display: flex; gap: 8px; align-items: flex-start; font-size: .84rem; color: #475569; flex: 1 1 260px; }
        .rv-cite input { margin-top: 3px; }
        .rv-send { background: #2563eb; color: #fff; border: 0; border-radius: 10px; padding: 10px 22px; font-weight: 800; font-size: .9rem; cursor: pointer; }
        .rv-send:disabled { background: #94a3b8; }
        .rv-done { display: flex; align-items: center; gap: 10px; font-weight: 700; color: #065f46; }
        .rv-done span.i { width: 30px; height: 30px; border-radius: 50%; background: #d1fae5; display: flex; align-items: center; justify-content: center; color: #059669; flex-shrink: 0; }
        @media (max-width: 560px) { .rv-card { padding: 18px 16px; } .rv-stars button { width: 38px; height: 38px; } .rv-close { top: 6px; right: 6px; } }
</style>
<div class="rv-card" id="rvCard" data-url="<?= site_url('dashboard/valoracion') ?>" data-csrf-name="<?= csrf_token() ?>" data-csrf="<?= csrf_hash() ?>">

    <button type="button" class="rv-close" data-rv="later"><?= esc($t['later']) ?></button>

    <div class="rv-top">
        <div class="rv-txt">
            <div class="rv-kicker"><?= esc($t['kicker']) ?></div>
            <h3><?= esc($t['h']) ?></h3>
            <p><?= esc($t['sub']) ?></p>
        </div>
        <div class="rv-stars" role="radiogroup" aria-label="<?= esc($t['h']) ?>">
            <?php for ($i = 1; $i <= 5; $i++): ?>
                <button type="button" role="radio" aria-checked="false" data-v="<?= $i ?>" title="<?= esc($t['labels'][$i]) ?>" aria-label="<?= $i ?> · <?= esc($t['labels'][$i]) ?>">
                    <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                </button>
            <?php endfor; ?>
            <span class="rv-label" id="rvLabel"></span>
        </div>
    </div>

    <div class="rv-more" id="rvMore">
        <label class="rv-q" for="rvComment" id="rvQ"><?= esc($t['c_high']) ?></label>
        <textarea id="rvComment" maxlength="1000" placeholder="<?= esc($t['ph']) ?>"></textarea>
        <div class="rv-row">
            <label class="rv-cite" id="rvCite"><input type="checkbox" id="rvCitar" value="1"> <span><?= esc($t['citar']) ?></span></label>
            <button type="button" class="rv-send" id="rvSend"><?= esc($t['send']) ?></button>
        </div>
    </div>

    <script>
    (function () {
        var card = document.getElementById('rvCard');
        if (!card || card.dataset.ready) return;
        card.dataset.ready = '1';

        var labels = <?= json_encode($t['labels'], JSON_UNESCAPED_UNICODE) ?>;
        var T = <?= json_encode(['high' => $t['c_high'], 'low' => $t['c_low'], 'ok_h' => $t['ok_h'], 'ok_l' => $t['ok_l'], 'err' => $t['err']], JSON_UNESCAPED_UNICODE) ?>;
        var nota = 0;
        var stars = card.querySelectorAll('.rv-stars button');

        function post(datos) {
            var fd = new FormData();
            Object.keys(datos).forEach(function (k) { fd.append(k, datos[k]); });
            fd.append(card.dataset.csrfName, card.dataset.csrf);
            return fetch(card.dataset.url, { method: 'POST', body: fd, headers: { 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
                .then(function (r) { return r.json(); });
        }
        function track(ev, meta) {
            try { if (window.trackEvent) window.trackEvent(ev, meta || {}); } catch (e) {}
        }

        stars.forEach(function (b) {
            b.addEventListener('click', function () {
                nota = +b.dataset.v;
                stars.forEach(function (s) {
                    var on = +s.dataset.v <= nota;
                    s.classList.toggle('on', on);
                    s.setAttribute('aria-checked', +s.dataset.v === nota ? 'true' : 'false');
                });
                document.getElementById('rvLabel').textContent = labels[nota];
                document.getElementById('rvQ').textContent = nota <= 3 ? T.low : T.high;
                document.getElementById('rvCite').style.visibility = nota <= 3 ? 'hidden' : 'visible';
                document.getElementById('rvMore').classList.add('open');
                track('review_card_rate', { rating: nota });
            });
        });

        document.getElementById('rvSend').addEventListener('click', function () {
            if (!nota) return;
            var btn = this;
            btn.disabled = true;
            post({
                rating: nota,
                comment: document.getElementById('rvComment').value,
                citar: document.getElementById('rvCitar').checked && nota >= 4 ? '1' : '0'
            }).then(function (j) {
                if (!j || !j.ok) throw new Error();
                track('review_card_sent', { rating: nota });
                card.innerHTML = '<div class="rv-done"><span class="i"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></span>'
                    + (nota >= 4 ? T.ok_h : T.ok_l) + '</div>';
            }).catch(function () {
                btn.disabled = false;
                alert(T.err);
            });
        });

        card.querySelector('[data-rv="later"]').addEventListener('click', function () {
            card.remove();
            track('review_card_later');
            post({ accion: 'descartar' }).catch(function () {});
        });
    })();
    </script>
</div>
