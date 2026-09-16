<?php
/**
 * partials/company_risk_titular.php
 * Score + nivel + motivo principal. El titular del dictamen, sin el desglose.
 *
 * Existe como parcial propio porque lo necesitan DOS sitios que hasta ahora
 * decían cosas distintas:
 *
 *  - El teaser del visitante anónimo, que ya lo enseñaba.
 *  - El paywall del registrado sin cuota, que NO enseñaba nada del resultado.
 *
 * Eso dejaba la escalera del producto al revés: registrarte te hacía ver menos
 * que antes de registrarte. Lo que se compra es el desglose —cada acto con su
 * fecha y su gravedad—, no la existencia del número; esconder también el número
 * no añade presión, añade la sensación de que registrarse fue un error.
 *
 * Nunca enseña la fecha ni el texto del asiento: eso es lo de pago.
 *
 * Variables:
 * - $riskProfile (array)
 * - $tiMargen (string, opcional) margen inferior; por defecto 20px
 */
helper(['company', 'risk_labels']);

$tiScore = (int) ($riskProfile['risk_score'] ?? 0);
[$tiNivel, $tiColor, $tiFondo, $tiBorde] = risk_level_visual($tiScore);
$tiNivel = $riskProfile['data']['risk_level'] ?? $tiNivel;

$tiEventos = $riskProfile['data']['canonical_events'] ?? [];
$tiTotal   = count($tiEventos);

// Motivo principal: el evento más grave, por su etiqueta. Sin fecha.
//
// Se ordena por gravedad Y, a igualdad, por familia de código. El motor marca como
// `high` tanto un concurso de acreedores como un retraso largo en el depósito de
// cuentas: ordenando solo por `severity` quedaban empatados y ganaba el primero del
// array, así que una empresa en concurso podía anunciar como principal el retraso
// contable. No cambia ninguna puntuación, solo cuál de los hechos ya emitidos se
// nombra primero.
$tiMotivo = '';
if ($tiTotal > 0) {
    $tiPeor = null;
    $tiPeso = -1;
    foreach ($tiEventos as $tiEv) {
        $p = risk_event_orden($tiEv);
        if ($p > $tiPeso) {
            $tiPeso = $p;
            $tiPeor = $tiEv;
        }
    }
    if ($tiPeor) {
        $tiMotivo = risk_event_label($tiPeor);
    }
}

$tiMargen = $tiMargen ?? '20px';

/*
 * Cero incidencias con poca cobertura NO es "sin incidencias".
 *
 * Este partial lo ve también el visitante anónimo que llega de Google, así que es
 * donde más caro sale afirmar de más: una empresa de la que apenas hay histórico
 * publicado salía con el mismo "sin incidencias registrales" que una con veinte años
 * de boletín limpio. Son conclusiones opuestas a partir del mismo cero.
 *
 * Mismo corte del 60 % que el bloque de dimensiones y que el paywall.
 */
$tiConf   = isset($riskProfile['data']['confidence_score'])
    ? (int) $riskProfile['data']['confidence_score']
    : null;
$tiAFalta = $tiTotal === 0 && $tiConf !== null && $tiConf < 60;
?>
<!-- Sobre blanco con el color solo en la ficha del score y en el nivel: en
     amarillo entero parecía un banner de aviso del sitio, y esto no es un aviso
     nuestro, es el dato que hemos calculado. -->
<div style="width: 100%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px; margin-bottom: <?= esc($tiMargen, 'attr') ?>; display: flex; align-items: center; gap: 16px; text-align: left; box-sizing: border-box;">
    <div style="flex-shrink: 0; width: 72px; padding: 10px 0; border-radius: 11px; background: <?= $tiFondo ?>; border: 1px solid <?= $tiBorde ?>; text-align: center; box-sizing: border-box;">
        <div style="font-size: 2.1rem; font-weight: 900; line-height: 1; color: <?= $tiColor ?>; letter-spacing: -1.5px;"><?= $tiScore ?></div>
        <div style="font-size: 0.6rem; font-weight: 800; color: <?= $tiColor ?>; opacity: 0.7; letter-spacing: 0.5px; margin-top: 2px;">/ 100</div>
    </div>
    <div style="flex: 1; min-width: 0;">
        <div style="font-size: 1.15rem; font-weight: 900; color: <?= $tiColor ?>; line-height: 1.15;">Riesgo <?= esc(mb_strtolower($tiNivel, 'UTF-8')) ?></div>
        <div style="font-size: 0.83rem; color: #475569; line-height: 1.45; margin-top: 5px;">
            <?php if ($tiTotal > 0 && $tiMotivo !== ''): ?>
                <?= $tiTotal ?> <?= $tiTotal === 1 ? 'incidencia registrada' : 'incidencias registradas' ?>.
                La principal: <strong style="color: #1e293b;"><?= esc($tiMotivo) ?></strong>.
            <?php elseif ($tiTotal > 0): ?>
                <?= $tiTotal ?> <?= $tiTotal === 1 ? 'incidencia registrada' : 'incidencias registradas' ?> en el BORME.
            <?php elseif ($tiAFalta): ?>
                No constan incidencias, pero el histórico publicado de esta empresa es
                limitado (cobertura del <?= $tiConf ?> %).
            <?php else: ?>
                Sin incidencias registrales en el histórico del BORME.
            <?php endif; ?>
        </div>
    </div>
</div>
