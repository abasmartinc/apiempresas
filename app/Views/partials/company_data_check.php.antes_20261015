<?php
/**
 * partials/company_data_check.php
 * "¿Son correctos estos datos?" — aviso de datos, bajo la tabla de la ficha.
 *
 * Sustituye a las estrellas de "¿Te ha sido útil esta información?" (24-09-2026).
 *
 * 29-09-2026: al pulsar "No, hay un error" se abre un formulario con los datos
 * actuales de la ficha ya rellenos. El usuario cambia los que estén mal (uno o
 * varios) y envía una sola vez. Cada input lleva en data-original el valor que
 * enseñaba la página; solo viajan los campos que el usuario ha tocado, así una
 * ficha cacheada con un dato viejo nunca pisa uno más nuevo de la base de datos.
 * Lo que no se corrige solo (estado, administradores...) va en "¿Algo más?".
 *
 * Guarda en company_ratings vía POST company/data-feedback (Company::submitDataFeedback),
 * que aplica los cambios con DataCorrectionService. Los avisos se ven en /admin/avisos-datos.
 *
 * Variables:
 * - $companyId (int)
 * - $company   (array, opcional) para rellenar los datos actuales
 * - $lang ('es' | 'en', opcional)
 */
$dcEn = ($lang ?? 'es') === 'en';
$dcId = (int) ($companyId ?? 0);
if ($dcId <= 0) {
    return;
}
$dcT = static fn (string $es, string $en) => $dcEn ? $en : $es;
$dcC = is_array($company ?? null) ? $company : [];
$dcV = static fn (string $k) => trim((string) ($dcC[$k] ?? '')) === '-' ? '' : trim((string) ($dcC[$k] ?? ''));

$dcCnae = $dcV('cnae') !== ''
    ? $dcV('cnae') . ($dcV('cnae_label') !== '' ? ' - ' . $dcV('cnae_label') : '')
    : $dcV('cnae_label');

// campo => [etiqueta, valor actual, tipo]
// Sin placeholders (29-09-2026): con ejemplos en gris parecía que el dato ya estaba
// relleno. Un campo vacío es que la ficha no tiene ese dato.
// El email de la empresa no se enseña en la ficha: se deja vacío para que lo añadan.
$dcCampos = [
    'telefono'  => [$dcT('Teléfono', 'Phone'),               $dcV('phone'),            'tel'],
    'movil'     => [$dcT('Móvil', 'Mobile'),                 $dcV('phone_mobile'),     'tel'],
    'web'       => [$dcT('Página web', 'Website'),           $dcV('website_official'), 'text'],
    'correo'    => [$dcT('Email de la empresa', 'Company email'), '',                  'email'],
    'direccion' => [$dcT('Dirección', 'Address'),            $dcV('address'),          'text'],
    'actividad' => [$dcT('Actividad (código CNAE)', 'Activity (CNAE code)'), $dcCnae, 'text'],
];
?>
<style>
    .data-check{margin-top:14px;padding-top:14px;border-top:1px dashed #e2e8f0;font-size:.85rem;color:#475569}
    .data-check__row{display:flex;align-items:center;gap:8px;flex-wrap:wrap}
    .data-check__q{font-weight:700;color:#334155;margin-right:4px}
    .data-check__btn{background:#fff;border:1px solid #cbd5e1;color:#334155;border-radius:999px;padding:5px 12px;font-size:.8rem;font-weight:700;cursor:pointer;font-family:inherit;transition:background .15s,border-color .15s}
    .data-check__btn:hover{background:#f8fafc;border-color:#94a3b8}
    .data-check__form{margin-top:12px;display:grid;gap:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;padding:14px}
    .data-check__intro{margin:0;font-size:.82rem;color:#475569}
    .data-check__note{margin:-4px 0 0;font-size:.78rem;color:#1e40af;background:#eff6ff;border:1px solid #dbeafe;border-radius:8px;padding:8px 10px}
    .data-check__form label{display:block;font-size:.75rem;font-weight:700;color:#64748b;margin-bottom:4px}
    .data-check__form input,.data-check__form textarea{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:8px;padding:8px 10px;font-size:.85rem;font-family:inherit;background:#fff;color:#0f172a;transition:border-color .15s,background .15s}
    .data-check__form textarea{resize:vertical;min-height:60px}
    .data-check__form input.is-changed{border-color:#2563eb;background:#eff6ff}
    .data-check__grid{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .data-check__full{grid-column:1 / -1}
    .data-check__foot{display:flex;align-items:center;gap:12px;flex-wrap:wrap}
    .data-check__count{font-size:.78rem;color:#64748b;font-weight:600}
    .data-check__send{background:#0f172a;color:#fff;border:0;border-radius:8px;padding:8px 16px;font-weight:800;font-size:.82rem;cursor:pointer;font-family:inherit}
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
        <p class="data-check__intro"><?= $dcT(
            'Corrige los datos que estén mal (puedes cambiar varios) y deja el resto como está.',
            'Fix whatever is wrong (you can change several) and leave the rest as it is.'
        ) ?></p>
        <p class="data-check__note"><?= $dcT(
            'Los cambios no se publican al momento: nuestro equipo los revisa uno a uno antes de actualizar la ficha.',
            'Changes are not published right away: our team reviews each one before updating the page.'
        ) ?></p>

        <div class="data-check__grid">
            <?php foreach ($dcCampos as $k => [$label, $valor, $tipo]): ?>
                <div<?= $k === 'direccion' || $k === 'actividad' ? ' class="data-check__full"' : '' ?>>
                    <label for="dc-<?= $k ?>"><?= esc($label) ?></label>
                    <input id="dc-<?= $k ?>" type="<?= $tipo ?>" maxlength="500" autocomplete="off"
                           data-dc-campo="<?= $k ?>"
                           data-original="<?= esc($valor, 'attr') ?>"
                           value="<?= esc($valor, 'attr') ?>">
                </div>
            <?php endforeach; ?>

            <div class="data-check__full">
                <label for="dc-otro"><?= $dcT('¿Algo más está mal? (estado, administradores…)', 'Anything else wrong? (status, directors…)') ?></label>
                <textarea id="dc-otro" name="otro" maxlength="500"></textarea>
            </div>

            <div class="data-check__full">
                <label for="dc-email"><?= $dcT('Tu email, para avisarte cuando esté corregido (opcional)', 'Your email, to let you know once fixed (optional)') ?></label>
                <input id="dc-email" name="email" type="email" maxlength="190" autocomplete="email">
            </div>
        </div>

        <div class="data-check__hp" aria-hidden="true">
            <label for="dc-web">Web</label>
            <input id="dc-web" name="web" type="text" tabindex="-1" autocomplete="off">
        </div>

        <div class="data-check__foot">
            <button type="submit" class="data-check__send" data-track-click="company_data_check" data-track-element="send"><?= $dcT('Enviar correcciones', 'Send corrections') ?></button>
            <span class="data-check__count" data-dc-count></span>
        </div>
    </form>

    <div class="data-check__msg" data-dc-msg hidden role="status"></div>
</div>
<script>
(function () {
    var box = document.getElementById('data-check');
    if (!box || box.dataset.ready) return;
    box.dataset.ready = '1';

    var url   = <?= json_encode(site_url('company/data-feedback')) ?>;
    var ask   = box.querySelector('[data-dc-ask]');
    var form  = box.querySelector('[data-dc-form]');
    var msg   = box.querySelector('[data-dc-msg]');
    var count = box.querySelector('[data-dc-count]');
    var inputs = Array.prototype.slice.call(form.querySelectorAll('[data-dc-campo]'));
    var err   = <?= json_encode($dcT('No se ha podido enviar. Inténtalo de nuevo.', 'Could not send it. Please try again.')) ?>;
    var txtNone = <?= json_encode($dcT('Cambia algún dato o cuéntanos qué está mal.', 'Change something or tell us what is wrong.')) ?>;
    var txtOne  = <?= json_encode($dcT('1 dato cambiado', '1 item changed')) ?>;
    var txtMany = <?= json_encode($dcT('%n datos cambiados', '%n items changed')) ?>;

    function norm(v) { return (v || '').replace(/\s+/g, ' ').trim(); }

    function changed() {
        return inputs.filter(function (i) { return norm(i.value) !== norm(i.dataset.original); });
    }

    function refresh() {
        var c = changed();
        inputs.forEach(function (i) { i.classList.toggle('is-changed', c.indexOf(i) !== -1); });
        count.textContent = c.length === 0 ? '' : (c.length === 1 ? txtOne : txtMany.replace('%n', c.length));
    }
    inputs.forEach(function (i) { i.addEventListener('input', refresh); });

    function show(text, ok) {
        msg.hidden = false;
        msg.textContent = text;
        msg.style.color = ok ? '#15803d' : '#b91c1c';
    }

    function send(body, onDone) {
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
        send(new URLSearchParams({ correcto: '1' }), function (ok, text) {
            show(text, ok);
            if (!ok) ask.hidden = false;
        });
    });

    box.querySelector('[data-dc-no]').addEventListener('click', function () {
        form.hidden = !form.hidden;
        msg.hidden = true;
        if (!form.hidden) inputs[0].focus();
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var c = changed();
        var otro = norm(form.otro.value);
        if (c.length === 0 && otro === '') {
            show(txtNone, false);
            return;
        }
        var body = new URLSearchParams();
        body.set('correcto', '0');
        c.forEach(function (i) {
            body.set('cambios[' + i.dataset.dcCampo + ']', i.value);
            body.set('originales[' + i.dataset.dcCampo + ']', i.dataset.original);
        });
        if (otro !== '') body.set('otro', otro);
        body.set('email', form.email.value);
        body.set('web', form.web.value);

        var btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        send(body, function (ok, text) {
            btn.disabled = false;
            show(text, ok);
            if (ok) { form.hidden = true; ask.hidden = true; }
        });
    });
})();
</script>
