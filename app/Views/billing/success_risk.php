<?= $this->extend( ($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app' ) ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="<?= base_url('public/css/billing-success.css?v=' . time()) ?>" />
<style>
    /* Los pasos de activación: la tarjeta del paso 1 lleva una lista y no debe
       saltar al pasar el ratón como las demás tarjetas decorativas. */
    .activa-card:hover { transform: none; }
    .activa-lista { max-height: 216px; overflow-y: auto; border: 1px solid #e2e8f0; border-radius: 12px; margin: 0 0 16px; }
    .activa-lista label { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-top: 1px solid #f1f5f9; cursor: pointer; font-size: 0.86rem; color: #0f172a; }
    .activa-lista label:first-child { border-top: 0; }
    .activa-lista small { color: #94a3b8; font-size: 0.74rem; }
    .activa-ok { display: flex; align-items: center; gap: 8px; background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; border-radius: 12px; padding: 12px 14px; font-size: 0.86rem; font-weight: 700; }
    .activa-aviso { background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; border-radius: 12px; padding: 12px 14px; font-size: 0.86rem; line-height: 1.5; margin-bottom: 14px; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<?php
$consultadas   = $consultadas ?? [];
$avisosActivos = $avisosActivos ?? ($avisos_activos ?? true);
$userEmail     = $user_email ?? '';
$vigilanciasPro = (int) solvencia('vigilanciasPro', 25);
?>
<div class="container" style="padding-top: 80px; padding-bottom: 60px;">

    <!-- HERO -->
    <div class="success-hero">
        <div class="success-hero__left">
            <div class="kicker">SUSCRIPCIÓN CONFIRMADA</div>

            <div class="title-row">
                <h1>Solvencia Pro activado.<br>Ya vigilamos el BORME por ti.</h1>
                <span class="status-badge">
                    <span class="status-ic" aria-hidden="true"></span>
                    Confirmado
                </span>
            </div>

            <p class="sub">
                Puedes poner <strong>hasta <?= $vigilanciasPro ?> empresas en vigilancia</strong> —te escribimos el día
                que el BORME publique algo de ellas— y consultar el dictamen de <?= (int) solvencia('consultasPro', 300) ?>
                empresas al mes. Tres pasos y queda listo:
            </p>

            <div class="hero-note">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                <span>
                    Te enviaremos la factura al email de tu cuenta. Sin permanencia: puedes cancelar cuando quieras.
                    <?php if (solvencia('garantiaActiva', true)): ?>
                        Y tienes <?= (int) solvencia('garantiaDias', 30) ?> días de garantía: si no te sirve, te devolvemos el dinero.
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <!-- TICKET DE COMPRA -->
        <aside class="purchase-card" aria-label="Resumen de la compra">
            <div class="purchase-head">
                <div class="purchase-title">Resumen de tu suscripción</div>
                <div class="purchase-sub">Referencia: <strong>#<?= esc($order_ref ?? 'SUB-0001') ?></strong></div>
            </div>

            <div class="purchase-lines">
                <div class="line"><span>Plan contratado</span><strong>Solvencia Pro</strong></div>
                <div class="line"><span>Periodicidad</span><strong><?= esc($period_name ?? 'Mensual') ?></strong></div>
                <div class="line"><span>Empresas vigiladas</span><strong>Hasta <?= $vigilanciasPro ?></strong></div>

                <div class="ticket-divider"></div>

                <?php
                    $base = (float)($base_price ?? 29.00);
                    $iva = $base * 0.21;
                    $total = $base + $iva;
                ?>
                <div class="line"><span>Precio base</span><strong><?= number_format($base, 2, ',', '.') ?> €</strong></div>
                <div class="line"><span>IVA (21%)</span><strong><?= number_format($iva, 2, ',', '.') ?> €</strong></div>
                <div class="line total"><span>Total facturado</span><strong><?= number_format($total, 2, ',', '.') ?> €</strong></div>
            </div>
        </aside>
    </div>

    <!-- LOS TRES PASOS.
         Lo que acaba de comprar es la vigilancia, y una suscripción que no vigila
         nada el primer día es una baja el día 30: nunca le llega un aviso y
         concluye que no sirve. Aquí se deja funcionando antes de que se vaya. -->
    <section class="next-steps" style="margin-top: 40px;">
        <div class="section-head">
            <h2>Déjalo funcionando en un minuto</h2>
            <p>Solvencia Pro trabaja solo, pero necesita saber qué empresas te importan.</p>
        </div>

        <div class="step-list">

            <!-- 1. VIGILAR LO QUE YA HA CONSULTADO -->
            <article class="step-card activa-card">
                <div class="step-icon-box blue">1</div>
                <div class="step-body">
                    <?php if (!empty($consultadas)): ?>
                        <h3>Vigila las que ya has consultado</h3>
                        <p style="margin-bottom: 14px;">
                            <?= count($consultadas) === 1
                                ? 'Has consultado 1 empresa que aún no vigilas.'
                                : 'Has consultado ' . count($consultadas) . ' empresas que aún no vigilas.' ?>
                            Márcalas y te avisamos si alguna se mueve.
                        </p>
                    <?php else: ?>
                        <h3>Pon tu primera empresa en vigilancia</h3>
                        <p>
                            Busca un cliente o proveedor y pulsa «Vigilar empresa» en su ficha. Te escribimos el día que
                            aparezca en el BORME.
                        </p>
                    <?php endif; ?>
                </div>
                <div class="step-actions">
                    <?php if (!empty($consultadas)): ?>
                        <!-- Mismo alta que la carga de cartera: respeta el cupo del plan y
                             avisa de las que no caben. -->
                        <form method="post" action="<?= site_url('cartera/vigilar') ?>" style="margin: 0;">
                            <?= csrf_field() ?>
                            <input type="hidden" name="origen" value="alta_pro">
                            <div class="activa-lista">
                                <?php foreach ($consultadas as $i => $e): ?>
                                    <label>
                                        <input type="checkbox" name="cifs[]" value="<?= esc($e['cif'], 'attr') ?>"
                                               <?= $i < $vigilanciasPro ? 'checked' : '' ?>
                                               style="width: 16px; height: 16px; accent-color: #2563eb; flex-shrink: 0;">
                                        <span style="min-width: 0;">
                                            <?= esc($e['nombre']) ?><br><small><?= esc($e['cif']) ?></small>
                                        </span>
                                    </label>
                                <?php endforeach; ?>
                            </div>
                            <button type="submit" class="btn btn_primary btn_full" data-loading="Poniendo en vigilancia…"
                                    data-track-click="pro_success_vigilar">
                                Vigilar las marcadas
                            </button>
                        </form>
                    <?php else: ?>
                        <a class="btn btn_primary btn_full" href="<?= site_url('dashboard?view=risk') ?>"
                           data-track-click="pro_success_buscar">Buscar una empresa</a>
                    <?php endif; ?>
                </div>
            </article>

            <!-- 2. SUBIR LA CARTERA -->
            <article class="step-card">
                <div class="step-icon-box green">2</div>
                <div class="step-body">
                    <h3>Sube tu lista de clientes</h3>
                    <p>
                        Exporta tus clientes a CSV desde Excel o tu programa de facturación y súbelo. Te los ordenamos por
                        riesgo y pones en vigilancia los que quieras, de una vez.
                    </p>
                </div>
                <div class="step-actions">
                    <a class="btn btn_light btn_full" href="<?= site_url('cartera') ?>" data-track-click="pro_success_cartera">
                        Subir mi cartera
                    </a>
                </div>
            </article>

            <!-- 3. QUE LOS AVISOS LLEGUEN -->
            <article class="step-card">
                <div class="step-icon-box violet">3</div>
                <div class="step-body">
                    <h3>Asegúrate de recibir los avisos</h3>
                    <?php if ($avisosActivos): ?>
                        <p>
                            Los avisos llegarán a <strong><?= esc($userEmail) ?></strong> desde
                            <strong><?= esc(env('email.fromEmail', 'soporte@apiempresas.es')) ?></strong>. Añádelo a tus contactos para que no acaben en spam.
                        </p>
                    <?php else: ?>
                        <div class="activa-aviso" id="avisos-off">
                            Tienes los avisos por correo <strong>desactivados</strong>: sin ellos, la vigilancia no puede
                            avisarte de nada.
                        </div>
                    <?php endif; ?>
                </div>
                <div class="step-actions">
                    <?php if ($avisosActivos): ?>
                        <div class="activa-ok">✓ Avisos activados</div>
                    <?php else: ?>
                        <button type="button" class="btn btn_primary btn_full" id="btn-activar-avisos"
                                data-track-click="pro_success_activar_avisos">
                            Activar los avisos por correo
                        </button>
                        <div class="activa-ok" id="avisos-on" style="display: none;">✓ Avisos activados</div>
                    <?php endif; ?>
                </div>
            </article>
        </div>

        <div style="margin-top: 26px; text-align: center;">
            <a href="<?= site_url('dashboard?view=risk') ?>" style="color: #2563eb; font-weight: 800; text-decoration: none; font-size: 0.95rem;">
                Ir a mi panel de Solvencia →
            </a>
        </div>
    </section>
</div>

<?php if (!$avisosActivos): ?>
<script>
(function () {
    var btn = document.getElementById('btn-activar-avisos');
    if (!btn) return;
    btn.addEventListener('click', function () {
        btn.disabled = true;
        btn.textContent = 'Activando…';
        var datos = new URLSearchParams();
        datos.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');
        fetch('<?= site_url('api/usuario/activar-avisos') ?>', {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            body: datos,
            credentials: 'same-origin'
        })
        .then(function (r) { return r.json(); })
        .then(function (j) {
            if (j && j.ok) {
                btn.style.display = 'none';
                document.getElementById('avisos-on').style.display = 'flex';
                var off = document.getElementById('avisos-off');
                if (off) off.style.display = 'none';
            } else { throw new Error(); }
        })
        .catch(function () {
            btn.disabled = false;
            btn.textContent = 'No se pudo activar: inténtalo de nuevo';
        });
    });
})();
</script>
<?php endif; ?>
<?= $this->endSection() ?>
