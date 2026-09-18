<?php
/**
 * partials/company_risk_dimensions.php
 * Desglose del scoring: de dónde sale el número.
 *
 * El motor (risk_profile_engine.py, v2.0.0) ya calculaba y guardaba estas seis
 * dimensiones dentro del JSON de company_risk_profiles.risk_profile, pero
 * ninguna vista las leía. Sin esto, un 80/ALTO es una cifra que el cliente tiene
 * que creerse; con esto puede comprobar que viene de un cierre de hoja registral
 * y no de un cambio de domicilio.
 *
 * Importante para entender la suma: el motor NO suma las dimensiones. Toma la
 * mayor y le añade una fracción del margen que queda (beta = 0,20) por cada una
 * de las demás. Por eso la dominante manda y hay que señalarla.
 *
 * Variables:
 * - $riskProfile (array)
 * - $score (int, opcional)
 */
helper('company');

$dimData = $riskProfile['data'] ?? [];
$dims    = risk_dimensions($dimData);
$company = $company ?? [];   // el PDF y el buscador no siempre lo pasan

if (empty($dims)) {
    return; // perfil calculado con una versión anterior del motor
}

$dimScore = (int) ($score ?? $riskProfile['risk_score'] ?? 0);

// La dominante es la que fija el suelo del score.
$dominante = null;
$maxValor  = 0.0;
foreach ($dims as $d) {
    if ($d['suma'] && $d['valor'] > $maxValor) {
        $maxValor  = $d['valor'];
        $dominante = $d['clave'];
    }
}

/*
 * EL COLOR LO DECIDE LA GRAVEDAD, NO EL RANKING.
 *
 * La barra dominante se pintaba en rojo SIEMPRE, porque "dominante" solo
 * significa "la que más pesa de las seis" — aunque pese poco. El resultado se
 * veía en crudo en una ficha con puntuación 5: círculo verde y etiqueta LEVE a
 * la izquierda, y a diez centímetros una barra ROJA con la insignia FACTOR
 * DOMINANTE por una dimensión que iba por 20 de 60. La misma pantalla decía
 * "esto está bien" y "esto es grave".
 *
 * Se ata a la banda del semáforo, que es la que ya manda en el resto de la
 * ficha (ver risk_level_visual): por debajo de A REVISAR, la dominante es la
 * principal y se pinta en ámbar; de ahí en adelante, rojo.
 */
$bandaAlta = $dimScore >= (int) solvencia('umbralMedio', 30);

$confianza = isset($dimData['confidence_score']) ? (int) $dimData['confidence_score'] : null;
$calidad   = isset($dimData['data_quality_score']) ? (int) $dimData['data_quality_score'] : null;
$conflicto = !empty($dimData['legal_evidence_conflict']);
$modelo    = trim((string) ($dimData['model_version'] ?? ''));

/*
 * Las dimensiones a cero ocupaban lo mismo que las que explican el número:
 * cinco filas con su barra gris para decir cinco veces "Sin incidencias",
 * que es una línea de información. Se detalla lo que pesa y se agrupa lo
 * limpio.
 */
$conPeso = [];
$limpias = [];
$credito = null;

foreach ($dims as $d) {
    if (!$d['suma']) {
        $credito = $d;
    } elseif (abs($d['valor']) > 0.01) {
        $conPeso[] = $d;
    } else {
        $limpias[] = $d;
    }
}

$enOrden = $conPeso;
if ($credito !== null) {
    $enOrden[] = $credito;
}

/*
 * CUANDO NO HAY NADA QUE DESGLOSAR, ESTA CAJA ESTORBA.
 *
 * En el 79 % de las fichas la puntuación es 0 y ninguna dimensión pesa. Ahí
 * esta caja se quedaba diciendo tres cosas y las tres restaban:
 *
 *   - "DE DÓNDE SALE EL 0" y debajo "Factores estabilizadores −15". La
 *     aritmética no cuadra a la vista (0 − 15 no es 0) porque el crédito se
 *     corta en el suelo y no restó nada; pintarlo con su barra verde sugiere
 *     un margen ganado que no existe.
 *   - Un párrafo explicando que "una única incidencia seria puede sostener un
 *     riesgo alto": un mecanismo que aquí no opera, y que deja al lector
 *     buscando el riesgo alto del que le hablan.
 *   - "Sin incidencias en situación registral, depósito de cuentas..." que es
 *     lo mismo que dicen, mejor, las nueve líneas del bloque de abajo.
 *
 * Lo único que sí informa en ese caso son la cobertura y la confianza. Así que
 * cuando no hay nada que desglosar la caja se reduce a eso y el bloque de
 * comprobaciones pasa a ser el contenido de la columna.
 */
$sinNada = ($dimScore <= 0 && empty($conPeso));
?>

<div style="margin-top: 28px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 22px 24px;">

    <div style="display: flex; align-items: baseline; justify-content: space-between; flex-wrap: wrap; gap: 10px; margin-bottom: 6px;">
        <h4 style="font-size: 1.05rem; font-weight: 900; color: #0f172a; margin: 0; text-transform: uppercase; letter-spacing: -0.2px;">
            <?= $sinNada ? 'Calidad del dato' : 'De dónde sale el ' . $dimScore ?>
        </h4>
        <?php if ($modelo !== ''): ?>
            <span style="font-size: 0.7rem; color: #94a3b8; font-weight: 600;">Modelo <?= esc($modelo) ?></span>
        <?php endif; ?>
    </div>

    <?php if ($sinNada): ?>
        <p style="margin: 0; font-size: 0.86rem; color: #64748b; line-height: 1.5;">
            No consta ningún hecho que puntúe. Lo que sigue dice hasta dónde llega la
            información con la que se ha comprobado.
        </p>
    <?php else: ?>
        <p style="margin: 0 0 18px 0; font-size: 0.86rem; color: #64748b; line-height: 1.5;">
            La puntuación no es la suma de las seis dimensiones: manda la más grave, y las demás
            añaden solo una parte del margen que queda. Por eso una única incidencia seria puede
            sostener un riesgo alto aunque el resto esté limpio.
        </p>
    <?php endif; ?>

    <?php if (!$sinNada): ?>
    <div style="display: flex; flex-direction: column; gap: 13px;">
        <?php foreach ($enOrden as $d): ?>
            <?php
            $esDominante = ($d['clave'] === $dominante);
            $esCredito   = !$d['suma'];
            $activa      = abs($d['valor']) > 0.01;

            if ($esCredito) {
                $barra = '#16a34a';
            } elseif ($esDominante) {
                // Rojo solo si el conjunto llega a A REVISAR. Ver la nota de arriba.
                $barra = $bandaAlta ? '#b91c1c' : '#f59e0b';
            } elseif ($activa) {
                // Si la dominante ya va en ámbar, las demás bajan un escalón para
                // que se siga viendo cuál manda.
                $barra = $bandaAlta ? '#f59e0b' : '#cbd5e1';
            } else {
                $barra = '#e2e8f0';
            }
            ?>
            <div>
                <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 12px; margin-bottom: 5px;">
                    <div style="font-size: 0.88rem; font-weight: 800; color: <?= $activa ? '#0f172a' : '#94a3b8' ?>;">
                        <?= esc($d['titulo']) ?>
                        <?php if ($esDominante): ?>
                            <?php /* "Dominante" en rojo sobre una puntuación de 5 es una
                                     alarma por algo que la propia ficha acaba de llamar
                                     menor. En la banda baja es la principal, y en ámbar. */ ?>
                            <span style="margin-left: 6px; background: <?= $bandaAlta ? '#fef2f2' : '#fffbeb' ?>; border: 1px solid <?= $bandaAlta ? '#fecaca' : '#fde68a' ?>; color: <?= $bandaAlta ? '#b91c1c' : '#b45309' ?>; font-size: 0.62rem; font-weight: 900; padding: 2px 7px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.4px; vertical-align: middle;">
                                <?= $bandaAlta ? 'Factor dominante' : 'Factor principal' ?>
                            </span>
                        <?php endif; ?>
                    </div>
                    <div style="flex-shrink: 0; font-size: 0.82rem; font-weight: 900; color: <?= $activa ? ($esCredito ? '#16a34a' : '#0f172a') : '#cbd5e1' ?>;">
                        <?php if ($esCredito): ?>
                            <?= $activa ? number_format($d['valor'], 0, ',', '.') : '0' ?>
                        <?php else: ?>
                            <?= number_format($d['valor'], 0, ',', '.') ?>
                            <span style="color: #94a3b8; font-weight: 600;">/ <?= (int) $d['tope'] ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div style="height: 7px; background: #f1f5f9; border-radius: 999px; overflow: hidden;">
                    <div style="height: 100%; width: <?= $activa ? max(3, (int) $d['pct']) : 0 ?>%; background: <?= $barra ?>; border-radius: 999px;"></div>
                </div>

                <div style="margin-top: 4px; font-size: 0.76rem; color: <?= $activa ? '#64748b' : '#a3adbb' ?>; line-height: 1.4;">
                    <?= esc($d['explica']) ?>
                    <?php if (!$activa && !$esCredito): ?>
                        <strong style="color: #16a34a;">Sin incidencias.</strong>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (!empty($limpias)): ?>
            <?php
            $nombresLimpios = array_map(
                static fn ($d) => mb_strtolower($d['titulo'], 'UTF-8'),
                $limpias
            );
            ?>
            <?php
            /*
             * UNA LÍNEA, SIN DESPLEGABLE.
             *
             * Esto era un <details> que abría las dimensiones limpias con su
             * barra a 0/tope. Tenía sentido cuando era el único sitio donde se
             * podía comprobar que lo demás estaba limpio; desde que existe el
             * bloque "Qué se ha comprobado", justo debajo, el "Ver detalle"
             * lleva a una versión peor de lo que el usuario ya tiene delante:
             * cuatro barras vacías frente a nueve líneas con su resultado.
             *
             * La frase sí se queda, porque aquí cumple otra función: explica
             * por qué la puntuación no sube más, que es de lo que va esta caja.
             */
            ?>
            <div style="border-top: 1px solid #f1f5f9; padding-top: 12px; font-size: 0.8rem; color: #475569; line-height: 1.45;">
                <strong style="color: #16a34a;">Sin incidencias</strong>
                en <?= esc(company_lista_natural($nombresLimpios)) ?>.
            </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($conflicto): ?>
        <!-- El motor marca esto cuando el estado oficial y los actos del BORME se
             contradicen. Callarlo sería lo cómodo y lo que destruye la confianza
             el día que el cliente lo descubra por su cuenta. -->
        <div style="margin-top: 18px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; border-radius: 11px; padding: 12px 15px; font-size: 0.82rem; line-height: 1.5;">
            ⚠️ <strong>Evidencia contradictoria.</strong> El estado oficial de la empresa y los actos
            publicados en el BORME no concuerdan. La puntuación se ha calculado con el criterio más
            prudente; conviene verificarlo en el Registro Mercantil antes de tomar una decisión.
        </div>
    <?php endif; ?>

    <?php if ($confianza !== null || $calidad !== null): ?>
        <?php /* Sin desglose encima no hay nada que separar, así que la línea
                 divisoria sobra y la caja queda de dos líneas. */ ?>
        <div style="margin-top: <?= $sinNada ? '14px' : '16px' ?>; <?= $sinNada ? '' : 'border-top: 1px solid #f1f5f9; padding-top: 13px;' ?> display: flex; flex-wrap: wrap; gap: 22px; font-size: 0.79rem; color: #64748b;">
            <?php if ($calidad !== null): ?>
                <div>
                    <strong style="color: #0f172a;">Cobertura del dato:</strong> <?= $calidad ?>%
                    <span style="color: #94a3b8;">— cuánta de la información necesaria consta.</span>
                </div>
            <?php endif; ?>
            <?php if ($confianza !== null): ?>
                <div>
                    <strong style="color: #0f172a;">Confianza:</strong> <?= $confianza ?>%
                    <span style="color: #94a3b8;">— cobertura ajustada por la antigüedad de la empresa.</span>
                </div>
            <?php endif; ?>
        </div>
        <?php if ($confianza !== null && $confianza < 60): ?>
            <?php
            /*
             * El aviso decía siempre "falta información registral O la empresa es
             * muy reciente". En una sociedad constituida en 2003 la segunda mitad
             * es sencillamente falsa, y el lector no tiene forma de saber cuál de
             * las dos le aplica — que es justo lo que venía a preguntar.
             *
             * La fórmula del motor es
             *     confianza = cobertura · (1 − 0,5·e^(−antigüedad/1,5))
             * así que el cociente confianza/cobertura aísla el factor de edad. Si
             * ese factor está cerca de 1, la edad no es el problema: es el dato.
             * Y si además conocemos la fecha de constitución real, se dice si el
             * motor la tiene o no, que es lo accionable.
             */
            $factorEdad = ($calidad !== null && $calidad > 0) ? ($confianza / $calidad) : null;
            $edadPesa   = ($factorEdad !== null && $factorEdad < 0.95);

            $anyosReales = null;
            $fechaConst  = trim((string) (
                $company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? ''
            ));
            if ($fechaConst !== '' && $fechaConst !== '0000-00-00') {
                $ts = strtotime($fechaConst);
                if ($ts !== false && $ts > 0) {
                    $anyosReales = (int) floor((time() - $ts) / (365.25 * 86400));
                }
            }

            if ($edadPesa && $anyosReales !== null && $anyosReales >= 5) {
                // El motor la trata como reciente pero no lo es: le falta la fecha.
                $avisoConfianza = 'Confianza baja: al motor no le consta la antigüedad registral de esta '
                    . 'empresa, así que puntúa con menos histórico del que realmente tiene. '
                    . 'Toma la puntuación como orientativa.';
            } elseif ($edadPesa && $anyosReales !== null) {
                $avisoConfianza = 'Confianza baja: la empresa es reciente (' . $anyosReales . ' '
                    . ($anyosReales === 1 ? 'año' : 'años') . '), así que hay poco histórico con el que '
                    . 'contrastar. Toma la puntuación como orientativa.';
            } elseif ($edadPesa) {
                $avisoConfianza = 'Confianza baja: hay poco histórico registral con el que contrastar. '
                    . 'Toma la puntuación como orientativa.';
            } else {
                $avisoConfianza = 'Confianza baja: falta parte de la información registral de esta empresa. '
                    . 'Toma la puntuación como orientativa.';
            }
            ?>
            <div style="margin-top: 10px; font-size: 0.79rem; color: #92400e; background: #fffbeb; border: 1px solid #fde68a; border-radius: 9px; padding: 9px 12px; line-height: 1.45;">
                <?= esc($avisoConfianza) ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
