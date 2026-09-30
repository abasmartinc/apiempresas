<?php
/**
 * "Lo que incluye tu plan": tarjeta del panel para Pro y Business.
 *
 * Cada función con un ejemplo copiable (con el último CIF que consultó) y una marca
 * si ya la ha probado. Desaparece sola cuando las ha probado todas y se puede
 * plegar (se recuerda en este navegador).
 *
 * @var array{plan_id:int, items:array, usadas:array} $planIncluye
 */
if (empty($planIncluye) || empty($planIncluye['items'])) {
    return;
}
$items   = $planIncluye['items'];
$usadas  = $planIncluye['usadas'] ?? [];
$conMarca = array_filter($items, static fn ($it) => $it['patron'] !== null);
$probadas = count(array_filter($conMarca, static fn ($it) => isset($usadas[$it['clave']])));
if ($conMarca && $probadas === count($conMarca)) {
    return; // ya lo ha probado todo
}
$nombrePlan = (int) $planIncluye['plan_id'] === 3 ? 'Business' : 'Pro';
?>
<section class="activation-main-card" id="paid-plan-includes" data-track-section="paid_plan_includes" style="margin-top: 32px;">
    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 16px; margin-bottom: 8px;">
        <div>
            <h2 style="font-size: 1.35rem; font-weight: 900; color: #0f172a; margin: 0 0 6px !important;">Lo que incluye tu plan <?= esc($nombrePlan) ?></h2>
            <p style="font-size: 0.95rem; color: #64748b; font-weight: 600; margin: 0;">
                Con la misma API Key. Has probado <?= (int) $probadas ?> de <?= count($conMarca) ?>.
            </p>
        </div>
        <button type="button" class="btn-small" id="btnTogglePlanIncludes" style="color: #64748b; border-color: #e2e8f0; font-size: 0.75rem; padding: 6px 12px; flex-shrink: 0;">Plegar</button>
    </div>

    <ul id="planIncludesList" style="list-style: none; margin: 16px 0 0; padding: 0; display: grid; gap: 12px;">
        <?php foreach ($items as $it): ?>
            <?php $hecha = isset($usadas[$it['clave']]); ?>
            <li style="border: 1px solid <?= $hecha ? '#bbf7d0' : '#e2e8f0' ?>; background: <?= $hecha ? '#f0fdf4' : '#ffffff' ?>; border-radius: 12px; padding: 14px 16px;">
                <div style="display: flex; align-items: center; gap: 8px; font-weight: 800; color: #0f172a;">
                    <?php if ($hecha): ?>
                        <span style="color: #16a34a;" aria-label="Ya lo has probado">✓</span>
                    <?php endif; ?>
                    <?= esc($it['titulo']) ?>
                    <a href="<?= site_url('documentation#' . $it['doc']) ?>" style="margin-left: auto; font-size: 0.8rem; font-weight: 700; color: #2152ff; text-decoration: none;">Documentación →</a>
                </div>
                <div style="font-size: 0.9rem; color: #475569; margin: 4px 0 8px;"><?= esc($it['detalle']) ?></div>
                <?php if (!$hecha): ?>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <code class="plan-incl-code" style="flex: 1; min-width: 0; overflow-x: auto; white-space: nowrap; background: #f1f5f9; border-radius: 8px; padding: 8px 10px; font-size: 0.8rem; color: #0f172a;"><?= esc($it['ejemplo']) ?></code>
                        <button type="button" class="btn-small plan-incl-copy" style="font-size: 0.75rem; padding: 6px 10px; flex-shrink: 0;">Copiar</button>
                    </div>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ul>
</section>
<script>
(function () {
    var list = document.getElementById('planIncludesList');
    var btn = document.getElementById('btnTogglePlanIncludes');
    if (!list || !btn) return;
    var KEY = 'ae_plan_includes_folded';
    function set(folded) {
        list.style.display = folded ? 'none' : 'grid';
        btn.textContent = folded ? 'Mostrar' : 'Plegar';
    }
    try { set(localStorage.getItem(KEY) === '1'); } catch (e) {}
    btn.addEventListener('click', function () {
        var folded = list.style.display !== 'none';
        set(folded);
        try { localStorage.setItem(KEY, folded ? '1' : '0'); } catch (e) {}
    });
    document.querySelectorAll('.plan-incl-copy').forEach(function (b) {
        b.addEventListener('click', function () {
            var code = b.parentNode.querySelector('.plan-incl-code');
            if (!code || !navigator.clipboard) return;
            navigator.clipboard.writeText(code.textContent).then(function () {
                b.textContent = 'Copiado';
                setTimeout(function () { b.textContent = 'Copiar'; }, 1500);
            });
        });
    });
})();
</script>
