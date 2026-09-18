<?php
helper('company');

/*
 * QUÉ SE HA COMPROBADO — versión PDF.
 *
 * Mismo contenido que `company_risk_comprobaciones.php`, otro montaje. No se
 * puede reutilizar aquel porque el web usa CSS grid y flexbox, y Dompdf no
 * implementa ninguno de los dos: saldría todo apilado a una columna sin
 * alineación. Aquí todo es <table>, que es lo único que Dompdf coloca bien.
 *
 * Lo que SÍ se comparte es la lógica: las nueve líneas salen de
 * `risk_comprobaciones()`, la misma función que la ficha. Si el PDF y la
 * pantalla dijeran cosas distintas sobre la misma empresa, el documento que el
 * cliente adjunta a un expediente contradiría la web de la que lo bajó.
 *
 * Los iconos no pueden ser caracteres: ver `risk_pdf_tick()`.
 *
 * Variables: $riskProfile
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
$fechaBorme = risk_datos_actualizados();
?>

<?php /* El respiro que se perdió al quitar el subtítulo del panel: sin él las
         tarjetas quedaban pegadas a la barra oscura de la sección. */ ?>
<div style="font-size: 7.6pt; color: #64748b; margin: 2px 0 13px 0; line-height: 1.35;">
    <?php if ($nInc === 0): ?>
        <?= $total ?> comprobaciones contra el Registro Mercantil.
        <strong style="color: #15803d;">Ninguna ha saltado.</strong>
    <?php else: ?>
        <?= $total ?> comprobaciones ·
        <strong style="color: #b91c1c;"><?= $nInc ?> con incidencia</strong><?php if ($nOk > 0): ?> · <?= $nOk ?> sin nada que señalar<?php endif; ?>
    <?php endif; ?>
</div>

<?php foreach ($comprobaciones as $c): ?>
    <?php
    /*
     * SOLO LA INCIDENCIA LLEVA INSIGNIA.
     *
     * La primera versión ponía un círculo verde sólido de 24 px en cada línea.
     * Con una o dos tarjetas eso funciona —es lo que hace el resto del informe—
     * pero aquí son nueve seguidas, y nueve manchas verdes en columna aplastan
     * el texto que tienen al lado: el ojo ve la mancha, no lo que se comprobó.
     *
     * Ahora el visto va suelto, en verde y sin fondo (ver risk_pdf_tick_verde),
     * y el círculo se reserva a lo que sí debe saltar a la vista. Eso también es
     * jerarquía: en una empresa limpia no hay ningún elemento pesado, que es
     * exactamente lo que el documento quiere decir.
     */
    if ($c['estado'] === 'incidencia') {
        $fondoFila  = '#fef2f2';
        $bordeFila  = '#fecaca';
        $tintaTit   = '#991b1b';
        $tintaTxt   = '#b91c1c';
        $fondoIcono = '#fee2e2';
        $tintaIcono = '#b91c1c';
        $glifo      = '!';
        $conCirculo = true;
    } elseif ($c['estado'] === 'sin_datos') {
        $fondoFila  = '#ffffff';
        $bordeFila  = '#eef2f7';
        $tintaTit   = '#334155';
        $tintaTxt   = '#64748b';
        $fondoIcono = 'transparent';
        $tintaIcono = '#94a3b8';
        $glifo      = '?';
        $conCirculo = false;
    } elseif ($c['estado'] === 'no_procede') {
        $fondoFila  = '#ffffff';
        $bordeFila  = '#eef2f7';
        $tintaTit   = '#475569';
        $tintaTxt   = '#94a3b8';
        $fondoIcono = 'transparent';
        $tintaIcono = '#94a3b8';
        $glifo      = '-';
        $conCirculo = false;
    } else {
        $fondoFila  = '#ffffff';
        $bordeFila  = '#eef2f7';
        $tintaTit   = '#0b1c40';
        $tintaTxt   = '#64748b';
        $fondoIcono = 'transparent';
        $tintaIcono = '#15803d';
        $glifo      = null;   // el visto va como imagen
        $conCirculo = false;
    }
    ?>
    <?php /* page-break-inside en la tabla de cada línea: sin esto Dompdf parte una
             comprobación por la mitad al cambiar de página, que es el mismo fallo
             que ya se corrigió en la actividad registral. */ ?>
    <table style="width: 100%; border-collapse: collapse; background-color: <?= $fondoFila ?>; border: 1px solid <?= $bordeFila ?>; border-radius: 5px; margin-bottom: 4px; page-break-inside: avoid;">
        <tr>
            <td style="width: 30px; padding: 5px 0 5px 7px; vertical-align: middle;">
                <?php
                /*
                 * La celda del icono es siempre de 20x20 aunque el círculo no se
                 * pinte: así los vistos, las interrogaciones y las insignias
                 * quedan alineados en la misma columna. `vertical-align` en un
                 * <td> es lo único que centra de verdad en Dompdf; el
                 * `line-height` no, y con un <img> —que es inline y se apoya en
                 * la línea base— el hueco del descendente lo sube.
                 */
                ?>
                <table style="width: 20px; height: 20px; <?= $conCirculo ? 'border-radius: 10px; background-color: ' . $fondoIcono . ';' : '' ?> color: <?= $tintaIcono ?>; border-collapse: collapse;" cellpadding="0" cellspacing="0">
                    <tr><td style="width: 20px; height: 20px; text-align: center; vertical-align: middle; padding: 0; font-size: 9.5pt; font-weight: bold;">
                        <?php if ($glifo === null): ?>
                            <img src="<?= risk_pdf_tick_verde() ?>" width="13" height="13" alt="Comprobado">
                        <?php else: ?>
                            <?= $glifo ?>
                        <?php endif; ?>
                    </td></tr>
                </table>
            </td>
            <td style="padding: 5px 9px 5px 4px; vertical-align: middle;">
                <div style="font-size: 7.9pt; font-weight: bold; color: <?= $tintaTit ?>; margin-bottom: 1px;">
                    <?= esc($c['titulo']) ?>
                </div>
                <div style="font-size: 7.1pt; color: <?= $tintaTxt ?>; line-height: 1.3;">
                    <?= esc($c['detalle']) ?>
                </div>
            </td>
        </tr>
    </table>
<?php endforeach; ?>

<div style="font-size: 6.9pt; color: #94a3b8; margin-top: 6px; line-height: 1.35;">
    <?php if ($fechaBorme !== null): ?>
        Datos del BORME actualizados a <strong style="color: #64748b;"><?= esc($fechaBorme) ?></strong>.
    <?php endif; ?>
    <?php if ($nSin > 0): ?>
        <?= $nSin ?> <?= $nSin === 1 ? 'comprobación no disponible' : 'comprobaciones no disponibles' ?> para esta empresa.
    <?php endif; ?>
    <?php if ($nNoProc > 0): ?>
        <?= $nNoProc ?> <?= $nNoProc === 1 ? 'no aplica' : 'no aplican' ?> a esta empresa.
    <?php endif; ?>
</div>
