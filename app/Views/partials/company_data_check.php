<?php
/**
 * partials/company_data_check.php
 * "¿Son correctos estos datos?" — aviso de datos, bajo la tabla de la ficha.
 *
 * Sustituye a las estrellas de "¿Te ha sido útil esta información?" (24-09-2026).
 * Aquellas mezclaban utilidad con exactitud y no decían qué dato fallaba. Esto
 * pregunta lo que de verdad interesa, justo al lado de los datos, y si algo falla
 * pide cuál. No se enseña ninguna puntuación ni va nada al JSON-LD.
 *
 * Guarda en company_ratings vía POST company/data-feedback (Company::submitDataFeedback).
 * Los avisos se ven en /admin/avisos-datos.
 *
 * Variables:
 * - $companyId (int)
 * - $lang ('es' | 'en', opcional)
 */
$dcEn = ($lang ?? 'es') === 'en';
$dcId = (int) ($companyId ?? 0);
if ($dcId <= 0) {
    return;
}
$dcT = static fn (string $es, string $en) => $dcEn ? $en : $es;
$dcCampos = [
    'direccion'       => $dcT('Dirección', 'Address'),
    'telefono'        => $dcT('Teléfono', 'Phone'),
    'actividad'       => $dcT('Actividad (CNAE)', 'Activity (CNAE)'),
    'estado'          => $dcT('Estado de la empresa', 'Company status'),
    'administradores' => $dcT('Administradores', 'Directors'),
    'otro'            => $dcT('Otro dato', 'Something else'),
];
?>
<style>
    .data-check{margin-top:14px;padding-top:14px;border-top:1px dashed #e2e8f0;font-size:.85rem;color:#475569}
    .data-check__row{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .data-check__q{font-weight:700;color:#334155;margin-right:4px}
    .data-check__btn{background:#fff;border:1px solid #cbd5e1;color:#334155;border-radius:999px;padding:5px 12px;font-size:.8rem;font-weight:700;cursor:pointer;font-family:inherit;transition:background .15s,border-color .15s}
    .data-check__btn:hover{background:#f8fafc;border-color:#94a3b8}
    .data-check__form{margin-top:12px;display:grid;gap:10px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px}
    .data-check__form label{display:block;font-size:.75rem;font-weight:700;color:#64748b;margin-bottom:4px}
    .data-check__form select,.data-check__form input{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:8px;padding:8px 10px;font-size:.85rem;font-family:inherit;background:#fff;color:#0f172a}
    .data-check__grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .data-check__send{justify-self:start;background:#0f172a;color:#fff;border:0;border-radius:8px;padding:8px 16px;font-weight:800;font-size:.82rem;cursor:pointer;font-family:inherit}
    .data-check__send[disabled]{opacity:.6;cursor:default}
    .data-check__msg{margin-top:10px;font-weight:700}
    /* display:grid del formulario le ganaba al atributo hidden. */
    .data-check [hidden]{display:none!important}
    .data-check__hp{position:absolute!important;left:-9999px!important;width:1px;height:1px;overflow:hidden}
    @media (max-width:560px){.data-check__grid{grid-template-columns:1fr}}
</style>
<div class="data-check" id="data-check" data-company="<?= $dcId ?>" data-lang="<?= $dcEn ? 'en' : 'es' ?>">
    <div class="data-check__row" data-dc-ask>
        <span class="data-check__q"><?= $dcT('¿Son correctos estos datos?', 'Is this data correct?') ?></span>
        <button type="button" class="data-check__btn" data-dc-yes data-track-click="company_data_check" data-track-element="yes"><?= $dcT('Sí', 'Yes') ?></button>
        <button type="button" class="data-check__btn" data-dc-no data-track-click="company_data_check" data-track-element="no"><?= $dcT('No, hay un error', 'No, something is wrong') ?></button>
    </div>

    <form class="data-check__form" data-dc-form hidden novalidate>
        <div>
            <label for="dc-campo"><?= $dcT('¿Qué dato no es correcto?', 'Which item is wrong?') ?></label>
            <select id="dc-campo" name="campo" required>
                <option value=""><?= $dcT('Elige uno…', 'Choose one…') ?></option>
                <?php foreach ($dcCampos as $k => $v): ?>
                    <option value="<?= $k ?>"><?= esc($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="data-check__grid">
            <div>
                <label for="dc-valor"><?= $dcT('¿Cuál es el correcto? (opcional)', 'What is the correct value? (optional)') ?></label>
                <input id="dc-valor" name="valor" type="text" maxlength="500" autocomplete="off">
            </div>
            <div>
                <label for="dc-email"><?= $dcT('Tu email (opcional)', 'Your email (optional)') ?></label>
                <input id="dc-email" name="email" type="email" maxlength="190" autocomplete="email" placeholder="<?= esc($dcT('Para avisarte al corregirlo', 'To tell you once fixed'), 'attr') ?>">
            </div>
        </div>
        <div class="data-check__hp" aria-hidden="true">
            <label for="dc-web">Web</label>
            <input id="dc-web" name="web" type="text" tabindex="-1" autocomplete="off">
        </div>
        <button type="submit" class="data-check__send" data-track-click="company_data_check" data-track-element="send"><?= $dcT('Enviar aviso', 'Send report') ?></button>
    </form>

    <div class="data-check__msg" data-dc-msg hidden role="status"></div>
</div>
<script>
(function () {
    var box = document.getElementById('data-check');
    if (!box || box.dataset.ready) return;
    box.dataset.ready = '1';

    var url  = <?= json_encode(site_url('company/data-feedback')) ?>;
    var ask  = box.querySelector('[data-dc-ask]');
    var form = box.querySelector('[data-dc-form]');
    var msg  = box.querySelector('[data-dc-msg]');
    var err  = <?= json_encode($dcT('No se ha podido enviar. Inténtalo de nuevo.', 'Could not send it. Please try again.')) ?>;

    function show(text, ok) {
        msg.hidden = false;
        msg.textContent = text;
        msg.style.color = ok ? '#15803d' : '#b91c1c';
    }

    function send(fields, onDone) {
        var body = new URLSearchParams(fields);
        body.set('company_id', box.dataset.company);
        body.set('lang', box.dataset.lang);
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        })
            .then(function (r) { return r.json(); })
            .then(function (d) { onDone(d && d.status === 'success', (d && d.message) || err); })
            .catch(function () { onDone(false, err); });
    }

    box.querySelector('[data-dc-yes]').addEventListener('click', function () {
        ask.hidden = true;
        form.hidden = true;
        send({ correcto: '1' }, function (ok, text) {
            show(text, ok);
            if (!ok) ask.hidden = false;
        });
    });

    box.querySelector('[data-dc-no]').addEventListener('click', function () {
        form.hidden = !form.hidden;
        msg.hidden = true;
        if (!form.hidden) form.querySelector('select').focus();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var campo = form.campo.value;
        if (!campo) {
            show(<?= json_encode($dcT('Indica qué dato no es correcto.', 'Please tell us which item is wrong.')) ?>, false);
            return;
        }
        var btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        send({
            correcto: '0',
            campo: campo,
            valor: form.valor.value,
            email: form.email.value,
            web: form.web.value
        }, function (ok, text) {
            btn.disabled = false;
            show(text, ok);
            if (ok) { form.hidden = true; ask.hidden = true; }
        });
    });
})();
</script>
