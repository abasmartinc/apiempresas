<?= $this->extend(($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app') ?>

<?= $this->section('content') ?>
<?php
helper('company');

$quedan = (int) ($cupo['quedan'] ?? 0);
$tope   = (int) ($cupo['tope'] ?? 0);
$esPro  = !empty($cupo['es_pro']);

$conRiesgo = 0;
$yaVigiladas = 0;
foreach ($empresas as $e) {
    if ($e['score'] !== null && $e['score'] >= (int) solvencia('umbralMedio', 30)) {
        $conRiesgo++;
    }
    if ($e['vigilando']) {
        $yaVigiladas++;
    }
}

// Se premarcan las de MÁS riesgo hasta donde llegue el cupo. Es el orden en que
// las querría cualquiera, y así el usuario no tiene que ir marcando a mano.
$premarcadas = 0;
?>
<div class="container" style="padding: 32px 0 64px 0;">

    <a href="<?= site_url('cartera') ?>" style="display: inline-flex; align-items: center; gap: 6px; color: #64748b; font-size: 0.85rem; font-weight: 700; text-decoration: none; margin-bottom: 18px;">
        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"></polyline></svg>
        Subir otro fichero
    </a>

    <h1 style="font-size: 1.9rem; font-weight: 900; color: #0f172a; margin: 0 0 8px 0; letter-spacing: -0.6px;">
        <?php if ($conRiesgo > 0): ?>
            <?= $conRiesgo ?> <?= $conRiesgo === 1 ? 'empresa de tu cartera tiene' : 'empresas de tu cartera tienen' ?> algo registrado
        <?php else: ?>
            Ninguna empresa de tu cartera tiene incidencias
        <?php endif; ?>
    </h1>
    <p style="color: #64748b; font-size: 0.98rem; line-height: 1.55; margin: 0 0 24px 0; max-width: 70ch;">
        Hemos reconocido <strong style="color: #334155;"><?= count($empresas) ?></strong>
        de los <strong style="color: #334155;"><?= (int) $leidos ?></strong> CIF del fichero.
        <?php if ($conRiesgo > 0): ?>
            Están ordenadas por puntuación, así que lo que conviene mirar está arriba.
        <?php else: ?>
            No constan actos problemáticos en el BORME. Lo que importa ahora es enterarte si eso cambia.
        <?php endif; ?>
    </p>

    <?php if (!empty($noEncontrados) || $descartados > 0 || !empty($truncado)): ?>
        <div style="background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 12px; padding: 13px 16px; font-size: 0.86rem; line-height: 1.55; margin-bottom: 20px;">
            <?php if (!empty($noEncontrados)): ?>
                <div><strong><?= count($noEncontrados) ?></strong> CIF no están en nuestra base de datos:
                    <?= esc(implode(', ', array_slice($noEncontrados, 0, 12))) ?><?= count($noEncontrados) > 12 ? ' y ' . (count($noEncontrados) - 12) . ' más' : '' ?>.
                </div>
            <?php endif; ?>
            <?php if ($descartados > 0): ?>
                <div><?= (int) $descartados ?> <?= $descartados === 1 ? 'fila no tenía' : 'filas no tenían' ?> un CIF reconocible y las hemos saltado.</div>
            <?php endif; ?>
            <?php if (!empty($truncado)): ?>
                <div>El fichero traía más filas de las que leemos de una vez; hemos cogido las primeras.</div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (empty($empresas)): ?>
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 40px 24px; text-align: center; color: #64748b;">
            Ninguno de los CIF del fichero está en nuestra base de datos todavía.
        </div>
    <?php else: ?>

    <?php
    // Todos los CIF analizados, también los que no constan: el que exporta
    // quiere la lista entera, no solo lo que hemos sabido resolver.
    $listaExport = implode(',', array_merge(array_column($empresas, 'cif'), $noEncontrados));
    ?>
    <form id="cartera-export" method="post" action="<?= site_url('cartera/exportar') ?>" style="display: none;">
        <?= csrf_field() ?>
        <input type="hidden" name="lista" value="<?= esc($listaExport, 'attr') ?>">
    </form>

    <form method="post" action="<?= site_url('cartera/vigilar') ?>">
        <?= csrf_field() ?>

        <!-- La barra de acción va ARRIBA además de abajo: con doscientas filas,
             un botón solo al final es un botón que no se ve. -->
        <div style="position: sticky; top: 0; z-index: 20; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px 18px; margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; gap: 16px; flex-wrap: wrap; box-shadow: 0 4px 12px -6px rgba(15, 23, 42, 0.12);">
            <div style="font-size: 0.88rem; color: #475569; line-height: 1.45;">
                Te caben <strong style="color: #0f172a;"><?= $quedan ?></strong>
                <?= $quedan === 1 ? 'empresa más' : 'empresas más' ?> en vigilancia
                <span style="color: #94a3b8;">(<?= (int) ($cupo['usadas'] ?? 0) ?> de <?= $tope ?> ocupadas)</span>
                <?php if (!$esPro): ?>
                    &bull; <a href="<?= site_url('billing?view=risk&plan=risk_pro') ?>" style="color: #2563eb; font-weight: 800; text-decoration: none;">Con Pro, <?= (int) solvencia('vigilanciasPro', 25) ?></a>
                <?php endif; ?>
            </div>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <button type="submit" form="cartera-export"
                        style="display: inline-flex; align-items: center; gap: 7px; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 11px; padding: 10px 16px; font-size: 0.88rem; font-weight: 800; color: #0f172a; cursor: pointer; white-space: nowrap; transition: all 0.15s;"
                        onmouseover="this.style.borderColor='#64748b'; this.style.background='#f8fafc';"
                        onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#ffffff';">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" style="flex-shrink:0;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    Descargar en CSV
                </button>
                <button type="submit" style="background: #2563eb; color: #fff; border: none; border-radius: 11px; padding: 11px 22px; font-size: 0.92rem; font-weight: 800; cursor: pointer; white-space: nowrap; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28);"
                        onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
                    Vigilar las marcadas
                </button>
            </div>
        </div>

        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; min-width: 640px;">
                    <thead>
                        <tr style="background: #f8fafc;">
                            <th style="padding: 11px 14px; width: 44px;"></th>
                            <th style="padding: 11px 14px; font-size: 0.72rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Empresa</th>
                            <th style="padding: 11px 14px; font-size: 0.72rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Riesgo</th>
                            <th style="padding: 11px 14px; font-size: 0.72rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($empresas as $e): ?>
                        <?php
                        $marcar = false;
                        if (!$e['vigilando'] && $premarcadas < $quedan) {
                            $marcar = true;
                            $premarcadas++;
                        }
                        [$nivel, $color, $fondo, $borde] = $e['score'] !== null
                            ? risk_level_visual((int) $e['score'])
                            : ['—', '#94a3b8', '#f1f5f9', '#e2e8f0'];
                        $slug = url_title($e['nombre'] ?: 'empresa', '-', true);
                        ?>
                        <tr style="border-top: 1px solid #f1f5f9;">
                            <td style="padding: 12px 14px;">
                                <?php if ($e['vigilando']): ?>
                                    <span title="Ya la vigilas" style="font-size: 1rem;">🔔</span>
                                <?php else: ?>
                                    <input type="checkbox" name="cifs[]" value="<?= esc($e['cif'], 'attr') ?>" <?= $marcar ? 'checked' : '' ?>
                                           style="width: 17px; height: 17px; cursor: pointer; accent-color: #2563eb;">
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 14px;">
                                <a href="<?= site_url('empresa/' . $e['id'] . '-' . $slug) ?>" target="_blank" rel="noopener"
                                   style="font-weight: 700; color: #0f172a; text-decoration: none; font-size: 0.92rem;">
                                    <?= esc(company_display_name($e['nombre'], 'Empresa')) ?>
                                </a>
                                <div style="font-size: 0.76rem; color: #94a3b8; margin-top: 2px;">
                                    <?= esc($e['cif']) ?><?= $e['provincia'] !== '' ? ' &bull; ' . esc($e['provincia']) : '' ?>
                                </div>
                            </td>
                            <td style="padding: 12px 14px; white-space: nowrap;">
                                <?php if ($e['score'] !== null): ?>
                                    <span style="display: inline-flex; align-items: center; gap: 7px; background: <?= $fondo ?>; border: 1px solid <?= $borde ?>; color: <?= $color ?>; padding: 4px 11px; border-radius: 999px; font-size: 0.8rem; font-weight: 900;">
                                        <?= (int) $e['score'] ?>
                                        <span style="font-weight: 700; font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.3px;"><?= esc($nivel) ?></span>
                                    </span>
                                <?php else: ?>
                                    <span style="font-size: 0.8rem; color: #94a3b8;">Sin calcular</span>
                                <?php endif; ?>
                            </td>
                            <td style="padding: 12px 14px; font-size: 0.82rem; color: <?= $e['vigilando'] ? '#047857' : '#94a3b8' ?>;">
                                <?= $e['vigilando'] ? 'Vigilando' : 'Sin vigilar' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($quedan < count($empresas) - $yaVigiladas): ?>
            <div style="margin-top: 16px; background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; border-radius: 12px; padding: 13px 16px; font-size: 0.87rem; line-height: 1.55;">
                Hemos marcado las <strong><?= $premarcadas ?></strong> de mayor riesgo, que es lo que te cabe
                <?= $esPro ? '' : 'con el plan gratuito' ?>.
                Puedes cambiar la selección a mano<?= $esPro ? '.' : ', o pasar a Pro y vigilar hasta ' . (int) solvencia('vigilanciasPro', 25) . '.' ?>
            </div>
        <?php endif; ?>

        <button type="submit" style="width: 100%; margin-top: 18px; background: #2563eb; color: #fff; border: none; border-radius: 12px; padding: 14px; font-size: 1rem; font-weight: 800; cursor: pointer; box-shadow: 0 4px 14px rgba(37, 99, 235, 0.3);"
                onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
            Vigilar las marcadas
        </button>
        <div style="margin-top: 10px; text-align: center; font-size: 0.82rem; color: #94a3b8;">
            Te escribimos por correo en cuanto alguna aparezca en el BORME. Puedes quitarlas cuando quieras.
        </div>
        <div style="margin-top: 14px; text-align: center;">
            <button type="submit" form="cartera-export" style="background: none; border: none; color: #2563eb; font-size: 0.85rem; font-weight: 700; cursor: pointer; text-decoration: underline; padding: 4px;">
                O descargar el análisis completo en CSV
            </button>
        </div>
    </form>

    <?php endif; ?>
</div>
<?= $this->endSection() ?>
