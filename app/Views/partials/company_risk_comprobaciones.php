<?php
helper('company');

/*
 * QUÉ SE HA COMPROBADO
 *
 * El 79 % de las fichas sale con puntuación 0. Hasta ahora eso era un cero y una
 * frase: quien llegaba buscando información veía un hueco y se iba. Pero sí se
 * ha hecho un trabajo —nueve comprobaciones contra el Registro Mercantil— y para
 * quien va a dar crédito el resultado "ninguna ha saltado" ES la respuesta.
 *
 * Este bloque la enseña. No es decorativo: cada línea se resuelve contra el
 * perfil real (ver `risk_comprobaciones()`), y lo que no se ha podido mirar se
 * marca como tal en vez de fingir un visto.
 */

$comprobaciones = risk_comprobaciones($riskProfile ?? []);
if (empty($comprobaciones)) {
    return;
}

$nOk     = count(array_filter($comprobaciones, static fn ($c) => $c['estado'] === 'ok'));
$nInc    = count(array_filter($comprobaciones, static fn ($c) => $c['estado'] === 'incidencia'));
$nSin    = count(array_filter($comprobaciones, static fn ($c) => $c['estado'] === 'sin_datos'));
$nNoProc = count(array_filter($comprobaciones, static fn ($c) => $c['estado'] === 'no_procede'));
$total   = count($comprobaciones);
$actual  = risk_datos_actualizados();
?>

<div style="margin-top: 22px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;">

    <div style="padding: 18px 22px; border-bottom: 1px solid #f1f5f9; display: flex; flex-wrap: wrap; align-items: baseline; gap: 10px;">
        <h3 style="margin: 0; font-size: 1rem; font-weight: 800; color: #0f172a;">
            Qué se ha comprobado
        </h3>
        <span style="font-size: 0.82rem; color: #64748b;">
            <?php if ($nInc === 0): ?>
                <?= $total ?> comprobaciones contra el Registro Mercantil.
                <strong style="color: #15803d;">Ninguna ha saltado.</strong>
            <?php else: ?>
                <?= $total ?> comprobaciones ·
                <strong style="color: #b91c1c;"><?= $nInc ?> con incidencia</strong>
                <?php if ($nOk > 0): ?> · <?= $nOk ?> sin nada que señalar<?php endif; ?>
            <?php endif; ?>
        </span>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(290px, 1fr)); gap: 0;">
        <?php foreach ($comprobaciones as $i => $c): ?>
            <?php
            if ($c['estado'] === 'incidencia') {
                $tinta = '#b91c1c';
                $fondo = '#fef2f2';
                // Triángulo de aviso.
                $icono = '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>';
            } elseif ($c['estado'] === 'sin_datos') {
                $tinta = '#64748b';
                $fondo = '#f1f5f9';
                // Interrogación: no se ha podido mirar. No es ni bueno ni malo, y
                // fingir un visto aquí sería afirmar algo que no se ha comprobado.
                $icono = '<circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line>';
            } elseif ($c['estado'] === 'no_procede') {
                $tinta = '#94a3b8';
                $fondo = '#f8fafc';
                // Guion, no interrogación ni visto: se ha mirado, y la pregunta
                // no aplica a esta empresa. Son tres cosas distintas y el bloque
                // pierde todo su valor si las pinta iguales.
                $icono = '<line x1="5" y1="12" x2="19" y2="12"></line>';
            } else {
                $tinta = '#15803d';
                $fondo = '#ecfdf5';
                $icono = '<polyline points="20 6 9 17 4 12"></polyline>';
            }
            ?>
            <div style="display: flex; gap: 12px; align-items: flex-start; padding: 13px 22px; border-bottom: 1px solid #f8fafc;">
                <span style="flex-shrink: 0; width: 26px; height: 26px; border-radius: 8px; background: <?= $fondo ?>; color: <?= $tinta ?>; display: inline-flex; align-items: center; justify-content: center; margin-top: 1px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round"><?= $icono ?></svg>
                </span>
                <div style="min-width: 0;">
                    <div style="font-size: 0.88rem; font-weight: 700; color: #0f172a; line-height: 1.3;">
                        <?= esc($c['titulo']) ?>
                    </div>
                    <div style="font-size: 0.78rem; color: <?= $c['estado'] === 'incidencia' ? '#b91c1c' : '#64748b' ?>; line-height: 1.4; margin-top: 2px;">
                        <?= esc($c['detalle']) ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="padding: 13px 22px; background: #f8fafc; border-top: 1px solid #f1f5f9; display: flex; flex-wrap: wrap; gap: 6px 14px; align-items: center; font-size: 0.78rem; color: #64748b;">
        <?php if ($actual !== null): ?>
            <?php /* La frescura del dato responde a la duda real de quien llega:
                     ¿esto está vivo o es una base de datos de hace tres años? */ ?>
            <span>📅 Datos del BORME actualizados a <strong style="color: #334155;"><?= esc($actual) ?></strong></span>
        <?php endif; ?>
        <?php if ($nSin > 0): ?>
            <span>· <?= $nSin ?> <?= $nSin === 1 ? 'comprobación no disponible' : 'comprobaciones no disponibles' ?> para esta empresa</span>
        <?php endif; ?>
        <?php if ($nNoProc > 0): ?>
            <span>· <?= $nNoProc ?> <?= $nNoProc === 1 ? 'no aplica' : 'no aplican' ?> a esta empresa</span>
        <?php endif; ?>
        <span style="margin-left: auto;">Y te avisamos el día que alguna cambie.</span>
    </div>
</div>
