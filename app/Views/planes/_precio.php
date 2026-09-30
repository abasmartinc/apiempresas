<?php
/**
 * Precio y botón de compra arriba de /planes/pro y /planes/business, con selector
 * mensual / anual. Antes solo había un botón al final de la página, siempre en
 * mensual y sin precio a la vista; el anual no se ofrecía nunca.
 *
 * @var string $plan   pro | business
 * @var string $source origen del botón (llega al pago)
 */
$filaPlan = (new \App\Models\ApiPlanModel())->where('slug', $plan)->first();
$mensual  = (float) ($filaPlan->price_monthly ?? ($plan === 'business' ? 49 : 19));
$anual    = (float) ($filaPlan->price_annual ?? ($plan === 'business' ? 470 : 182));
$cupo     = (int) ($filaPlan->monthly_quota ?? ($plan === 'business' ? 10000 : 3000));
$eur      = static fn (float $v) => rtrim(rtrim(number_format($v, 2, ',', '.'), '0'), ',');
$ahorro   = (int) round($mensual * 12 - $anual);
$nombre   = $plan === 'business' ? 'Business' : 'Pro';
$urlMes   = site_url('register?intent=api&plan=' . $plan . '&period=monthly&source=' . $source);
$urlAno   = site_url('register?intent=api&plan=' . $plan . '&period=annual&source=' . $source);
?>
<style>
    .precio-box { max-width: 520px; margin: 32px auto 0; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 24px; box-shadow: 0 10px 25px -10px rgba(15, 23, 42, 0.15); text-align: center; }
    .precio-toggle { display: inline-flex; background: #f1f5f9; border-radius: 100px; padding: 4px; margin-bottom: 16px; }
    .precio-toggle button { border: 0; background: transparent; padding: 8px 18px; border-radius: 100px; font-weight: 700; color: #475569; cursor: pointer; font-size: 0.95rem; }
    .precio-toggle button.activo { background: #ffffff; color: #0f172a; box-shadow: 0 1px 3px rgba(0,0,0,0.12); }
    .precio-cifra { font-size: 2.6rem; font-weight: 900; color: #0f172a; line-height: 1; }
    .precio-cifra small { font-size: 1rem; font-weight: 700; color: #64748b; }
    .precio-nota { margin: 8px 0 18px; color: #475569; font-size: 0.95rem; min-height: 1.4em; }
    .precio-box .btn-primary-cta { display: block; width: 100%; box-sizing: border-box; padding: 16px 20px; font-size: 1.05rem; }
    .precio-pie { margin-top: 12px; font-size: 0.85rem; color: #64748b; }
</style>
<div class="precio-box" id="precioBox"
     data-url-mes="<?= esc($urlMes) ?>" data-url-ano="<?= esc($urlAno) ?>">
    <div class="precio-toggle" role="group" aria-label="Periodo de pago">
        <button type="button" class="activo" data-periodo="monthly">Mensual</button>
        <button type="button" data-periodo="annual">Anual (ahorras <?= $ahorro ?> €)</button>
    </div>
    <div class="precio-cifra" id="precioCifra"><?= $eur($mensual) ?> € <small>/mes + IVA</small></div>
    <div class="precio-nota" id="precioNota"><?= number_format($cupo, 0, ',', '.') ?> consultas al mes. O <?= $eur(round($anual / 12, 2)) ?> €/mes pagando anual.</div>
    <a href="<?= esc($urlMes) ?>" class="btn-primary-cta js-precio-cta">Activar <?= $nombre ?></a>
    <div class="precio-pie">Sin permanencia: cancelas cuando quieras. Factura con tu NIF.</div>
</div>
<script>
(function () {
    var box = document.getElementById('precioBox');
    if (!box) return;
    var cifra = document.getElementById('precioCifra');
    var nota = document.getElementById('precioNota');
    var textos = {
        monthly: { cifra: <?= json_encode($eur($mensual) . ' € <small>/mes + IVA</small>') ?>, nota: <?= json_encode(number_format($cupo, 0, ',', '.') . ' consultas al mes. O ' . $eur(round($anual / 12, 2)) . ' €/mes pagando anual.') ?> },
        annual:  { cifra: <?= json_encode($eur(round($anual / 12, 2)) . ' € <small>/mes + IVA</small>') ?>, nota: <?= json_encode('Facturado ' . $eur($anual) . ' € al año (ahorras ' . $ahorro . ' €). ' . number_format($cupo, 0, ',', '.') . ' consultas al mes.') ?> }
    };
    box.querySelectorAll('.precio-toggle button').forEach(function (b) {
        b.addEventListener('click', function () {
            var p = b.getAttribute('data-periodo');
            box.querySelectorAll('.precio-toggle button').forEach(function (x) { x.classList.toggle('activo', x === b); });
            cifra.innerHTML = textos[p].cifra;
            nota.textContent = textos[p].nota;
            var url = p === 'annual' ? box.getAttribute('data-url-ano') : box.getAttribute('data-url-mes');
            // El botón de arriba y el del final de la página siguen el periodo elegido
            document.querySelectorAll('.js-precio-cta').forEach(function (a) { a.href = url; });
        });
    });
})();
</script>
