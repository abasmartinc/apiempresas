<?php
/**
 * partials/company_contribute.php
 * "Añadir teléfono / web / email" de la ficha (15-10-2026). Formulario en ventana
 * (<dialog>) que se abre desde los enlaces [data-aportar="telefono|web|correo"] del
 * bloque de contacto. Envía a POST company/aportar-dato (Company::submitContribution),
 * que lo guarda en company_contributions SIN publicarlo (ver ContributionService).
 *
 * La ficha está en la caché de Cloudflare: nada de aquí depende de la sesión, y el
 * captcha (si TURNSTILE_ENABLED) se carga solo al abrir la ventana.
 *
 * Variables:
 * - $companyId (int)
 * - $faltan    (array) campos sin dato en la ficha: telefono, web, correo
 * - $lang      ('es' | 'en', opcional)
 */
$acEn = ($lang ?? 'es') === 'en';
$acId = (int) ($companyId ?? 0);
$acFaltan = array_values(array_intersect(['telefono', 'web', 'correo'], (array) ($faltan ?? [])));
if ($acId <= 0 || !$acFaltan) {
    return;
}
$acT = static fn (string $es, string $en) => $acEn ? $en : $es;
$acTs = filter_var(env('TURNSTILE_ENABLED', false), FILTER_VALIDATE_BOOLEAN) ? (string) env('TURNSTILE_SITE_KEY') : '';

$acCampos = [
    'telefono' => [$acT('Teléfono fijo', 'Landline phone'), 'tel',   $acT('Ej.: 910 123 456', 'e.g. 910 123 456'), 'tel'],
    'web'      => [$acT('Página web', 'Website'),           'url',   $acT('Ej.: www.empresa.es', 'e.g. www.company.es'), 'url'],
    'correo'   => [$acT('Email de la empresa', 'Company email'), 'email', $acT('Ej.: info@empresa.es', 'e.g. info@company.es'), 'email'],
];
?>
<style>
    .aport-dlg{border:0;border-radius:16px;padding:0;max-width:460px;width:calc(100% - 32px);box-shadow:0 25px 50px -12px rgba(15,23,42,.35);color:#0f172a}
    .aport-dlg::backdrop{background:rgba(15,23,42,.45)}
    .aport-dlg form{padding:22px 22px 18px;display:grid;gap:14px;font-size:.88rem}
    .aport-dlg h3{margin:0;font-size:1.15rem;font-weight:800}
    .aport-dlg .aport-sub{margin:-8px 0 0;color:#64748b;font-size:.82rem;line-height:1.45}
    .aport-dlg label{display:block;font-size:.76rem;font-weight:700;color:#475569;margin-bottom:4px}
    .aport-dlg input[type=text],.aport-dlg input[type=tel],.aport-dlg input[type=url],.aport-dlg input[type=email]{width:100%;box-sizing:border-box;border:1px solid #cbd5e1;border-radius:8px;padding:9px 11px;font-size:.9rem;font-family:inherit}
    .aport-dlg input:focus{outline:2px solid #bfdbfe;border-color:#2563eb}
    .aport-dlg .aport-err{color:#b91c1c;font-size:.76rem;font-weight:600;margin-top:4px}
    .aport-dlg .aport-rel{display:flex;flex-wrap:wrap;gap:6px}
    .aport-dlg .aport-rel label{display:inline-flex;align-items:center;gap:6px;margin:0;border:1px solid #cbd5e1;border-radius:999px;padding:6px 12px;font-size:.8rem;font-weight:600;color:#334155;cursor:pointer}
    .aport-dlg .aport-rel input{margin:0}
    .aport-dlg .aport-rel label:has(input:checked){border-color:#2563eb;background:#eff6ff;color:#1e40af}
    .aport-dlg .aport-legal{display:flex;gap:8px;align-items:flex-start;font-size:.75rem;color:#64748b;line-height:1.45;font-weight:400}
    .aport-dlg .aport-legal input{margin-top:2px}
    .aport-dlg .aport-foot{display:flex;gap:10px;justify-content:flex-end;align-items:center}
    .aport-dlg .aport-send{background:#0f172a;color:#fff;border:0;border-radius:8px;padding:9px 18px;font-weight:800;font-size:.85rem;cursor:pointer;font-family:inherit}
    .aport-dlg .aport-send[disabled]{opacity:.6;cursor:default}
    .aport-dlg .aport-cancel{background:none;border:0;color:#64748b;font-weight:700;cursor:pointer;font-family:inherit;font-size:.85rem}
    .aport-dlg .aport-msg{font-weight:700;line-height:1.45}
    .aport-dlg [hidden]{display:none!important}
    .aport-dlg .aport-hp{position:absolute!important;left:-9999px!important;width:1px;height:1px;overflow:hidden}
    .aport-dlg .aport-reg{display:grid;gap:6px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:12px 14px;color:#166534;font-size:.82rem;line-height:1.45}
    .aport-dlg .aport-reg a{justify-self:start;background:#15803d;color:#fff;border-radius:8px;padding:8px 14px;font-weight:800;text-decoration:none;margin-top:2px}
    .aport-add{color:#2563eb;font-weight:600;text-decoration:underline;background:none;border:0;padding:0;cursor:pointer;font:inherit}
    .aport-add:hover{color:#1d4ed8}
</style>
<dialog class="aport-dlg" id="aport-dlg" data-company="<?= $acId ?>" data-lang="<?= $acEn ? 'en' : 'es' ?>" aria-labelledby="aport-title">
    <form method="dialog" novalidate>
        <h3 id="aport-title"><?= $acT('Añadir datos de contacto', 'Add contact details') ?></h3>
        <p class="aport-sub"><?= $acT(
            'Rellena lo que sepas. Lo comprobamos antes de publicarlo en la ficha.',
            'Fill in what you know. We check it before publishing it on this page.'
        ) ?></p>

        <?php foreach ($acFaltan as $k): [$label, $tipo, $ph, $auto] = $acCampos[$k]; ?>
            <div>
                <label for="aport-<?= $k ?>"><?= esc($label) ?></label>
                <input id="aport-<?= $k ?>" type="<?= $tipo === 'url' ? 'text' : $tipo ?>" inputmode="<?= $tipo === 'tel' ? 'tel' : ($tipo === 'email' ? 'email' : 'url') ?>"
                       maxlength="255" placeholder="<?= esc($ph, 'attr') ?>" data-aport-campo="<?= $k ?>" autocomplete="off">
                <div class="aport-err" data-aport-err="<?= $k ?>" hidden></div>
            </div>
        <?php endforeach; ?>

        <div>
            <label><?= $acT('¿Qué relación tienes con la empresa?', 'How are you related to the company?') ?></label>
            <div class="aport-rel">
                <label><input type="radio" name="relacion" value="empresa"> <?= $acT('Soy de la empresa', 'I work there') ?></label>
                <label><input type="radio" name="relacion" value="cliente"> <?= $acT('Cliente o proveedor', 'Customer or supplier') ?></label>
                <label><input type="radio" name="relacion" value="otro" checked> <?= $acT('Otra', 'Other') ?></label>
            </div>
        </div>

        <div>
            <label for="aport-email-contacto" data-aport-email-label><?= $acT('Tu email (opcional)', 'Your email (optional)') ?></label>
            <input id="aport-email-contacto" name="email_contacto" type="email" maxlength="190" autocomplete="email">
            <div class="aport-err" data-aport-err="email_contacto" hidden></div>
        </div>

        <label class="aport-legal">
            <input type="checkbox" name="acepto" value="1">
            <span><?= $acT(
                'Confirmo que son datos de contacto de la empresa (no personales) y acepto que APIEmpresas los publique en esta ficha. Mi email solo se usará para confirmar el envío.',
                'I confirm these are the company\'s contact details (not personal ones) and agree that APIEmpresas may publish them on this page. My email will only be used to confirm this submission.'
            ) ?></span>
        </label>
        <div class="aport-err" data-aport-err="acepto" hidden></div>

        <?php if ($acTs !== ''): ?><div data-aport-ts></div><?php endif; ?>

        <div class="aport-hp" aria-hidden="true">
            <label for="aport-url-extra">URL</label>
            <input id="aport-url-extra" name="url_extra" type="text" tabindex="-1" autocomplete="off">
        </div>

        <div class="aport-msg" data-aport-msg hidden role="status"></div>

        <div class="aport-reg" data-aport-reg hidden>
            <b><?= $acT('¿Quieres que te avisemos si cambia algo de tu empresa?', 'Want us to let you know if anything changes?') ?></b>
            <span><?= $acT('Crea tu cuenta gratis y te escribimos el día que el BORME publique algo de ella. Sin tarjeta.', 'Create a free account and we will email you whenever the BORME publishes something about it.') ?></span>
            <a href="#" data-aport-reg-link data-track-click="aportacion_registro" data-track-element="formulario"><?= $acT('Crear mi cuenta gratis', 'Create my free account') ?></a>
        </div>

        <div class="aport-foot">
            <button type="button" class="aport-cancel" data-aport-close><?= $acT('Cancelar', 'Cancel') ?></button>
            <button type="submit" class="aport-send" data-track-click="company_contribute" data-track-element="send"><?= $acT('Enviar', 'Send') ?></button>
        </div>
    </form>
</dialog>
<script>
(function () {
    var dlg = document.getElementById('aport-dlg');
    if (!dlg || dlg.dataset.ready) return;
    dlg.dataset.ready = '1';

    var url    = <?= json_encode(site_url('company/aportar-dato')) ?>;
    var tsKey  = <?= json_encode($acTs) ?>;
    var form   = dlg.querySelector('form');
    var msg    = dlg.querySelector('[data-aport-msg]');
    var send   = dlg.querySelector('.aport-send');
    var tsBox  = dlg.querySelector('[data-aport-ts]');
    var reg    = dlg.querySelector('[data-aport-reg]');
    var emailLabel = dlg.querySelector('[data-aport-email-label]');
    var tsId   = null;
    var T = {
        err:      <?= json_encode($acT('No se ha podido enviar. Inténtalo de nuevo.', 'Could not send it. Please try again.')) ?>,
        vacio:    <?= json_encode($acT('Escribe al menos un dato.', 'Enter at least one item.')) ?>,
        acepto:   <?= json_encode($acT('Marca la casilla para poder publicarlo.', 'Tick the box so we can publish it.')) ?>,
        emailOpt: <?= json_encode($acT('Tu email (opcional)', 'Your email (optional)')) ?>,
        emailEmp: <?= json_encode($acT('Tu email de la empresa (para confirmarlo)', 'Your company email (to confirm it)')) ?>,
        cerrar:   <?= json_encode($acT('Cerrar', 'Close')) ?>
    };

    function errores(map) {
        dlg.querySelectorAll('[data-aport-err]').forEach(function (el) {
            var m = map && map[el.dataset.aportErr];
            el.hidden = !m;
            el.textContent = m || '';
        });
    }
    function aviso(text, ok) {
        msg.hidden = !text;
        msg.textContent = text || '';
        msg.style.color = ok ? '#15803d' : '#b91c1c';
    }

    function captcha() {
        if (!tsKey || !tsBox) return;
        if (window.turnstile) {
            if (tsId === null) tsId = window.turnstile.render(tsBox, { sitekey: tsKey });
            return;
        }
        if (document.getElementById('aport-ts-js')) return;
        window.aportTsLoad = function () { captcha(); };
        var s = document.createElement('script');
        s.id = 'aport-ts-js';
        s.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=aportTsLoad';
        s.async = true;
        document.head.appendChild(s);
    }

    document.addEventListener('click', function (e) {
        var a = e.target.closest('[data-aportar]');
        if (!a) return;
        e.preventDefault();
        errores(null);
        aviso('');
        reg.hidden = true;
        form.hidden = false;
        send.hidden = false;
        if (typeof dlg.showModal === 'function') dlg.showModal(); else dlg.setAttribute('open', '');
        var input = dlg.querySelector('[data-aport-campo="' + a.dataset.aportar + '"]');
        if (input) input.focus();
        captcha();
    });

    dlg.querySelector('[data-aport-close]').addEventListener('click', function () { dlg.close(); });
    dlg.addEventListener('click', function (e) { if (e.target === dlg) dlg.close(); });

    form.addEventListener('change', function (e) {
        if (e.target.name === 'relacion') {
            emailLabel.textContent = e.target.value === 'empresa' ? T.emailEmp : T.emailOpt;
        }
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        var body = new URLSearchParams();
        var alguno = false;
        dlg.querySelectorAll('[data-aport-campo]').forEach(function (i) {
            var v = i.value.trim();
            if (v !== '') { alguno = true; body.set('datos[' + i.dataset.aportCampo + ']', v); }
        });
        if (!alguno) { errores(null); aviso(T.vacio, false); return; }
        if (!form.acepto.checked) { errores({ acepto: T.acepto }); aviso(''); return; }

        body.set('company_id', dlg.dataset.company);
        body.set('lang', dlg.dataset.lang);
        body.set('relacion', (form.querySelector('input[name="relacion"]:checked') || {}).value || 'otro');
        body.set('email_contacto', form.email_contacto.value.trim());
        body.set('acepto', '1');
        body.set('url_extra', form.url_extra.value);
        var ts = form.querySelector('[name="cf-turnstile-response"]');
        if (ts) body.set('cf-turnstile-response', ts.value);

        send.disabled = true;
        fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
            body: body
        })
            .then(function (r) { return r.json(); })
            .then(function (d) {
                send.disabled = false;
                errores(d && d.errores);
                aviso((d && d.message) || T.err, d && d.status === 'success');
                if (d && d.status === 'success') {
                    dlg.querySelectorAll('[data-aport-campo]').forEach(function (i) { i.value = ''; });
                    send.hidden = true;
                    dlg.querySelector('[data-aport-close]').textContent = T.cerrar;
                    if (d.registro) {
                        reg.querySelector('[data-aport-reg-link]').href = d.registro;
                        reg.hidden = false;
                    }
                }
                if (window.turnstile && tsId !== null) window.turnstile.reset(tsId);
            })
            .catch(function () { send.disabled = false; aviso(T.err, false); });
    });
})();
</script>
