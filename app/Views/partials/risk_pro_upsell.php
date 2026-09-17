<?php
/**
 * partials/risk_pro_upsell.php
 * CTA contextual de Solvencia Pro, insertado JUSTO DEBAJO del dictamen recién
 * desbloqueado: es el momento de máxima disposición a pagar, no tres empresas después.
 *
 * No se renderiza para suscriptores. El gancho se construye con los datos reales
 * de la empresa que el usuario acaba de leer.
 *
 * Variables:
 * - $riskProfile (array)
 * - $company (array)
 * - $riskQuota (array)
 */
if (!empty($riskQuota['is_subscriber'])) {
    return;
}

// Los dos helpers, ANTES de usarlos: risk_event_es_grave() se llama unas lineas
// mas abajo y hasta ahora helper('company') se cargaba despues.
helper(['company', 'risk_labels']);

$upsellScore  = (int)($riskProfile['risk_score'] ?? 50);
$upsellEvents = $riskProfile['data']['canonical_events'] ?? [];
$upsellTotal  = count($upsellEvents);
$upsellHigh   = 0;
foreach ($upsellEvents as $ev) {
    if (risk_event_es_grave($ev)) $upsellHigh++;   // incluye `critical`
}

// Nombre normalizado y acortado: por AJAX llega el nombre crudo en MAYÚSCULAS,
// y las denominaciones largas se comen la frase.
$upsellName = company_short_name(company_display_name($company['name'] ?? '', 'esta empresa'));
// El tono lo fija el MISMO nivel que acaba de leer el usuario dos centímetros
// más arriba. Antes se decidía por "¿hay algún evento grave?", así que una
// empresa de 45/MEDIO se coronaba con un "RIESGO DETECTADO" en rojo: el bloque
// se contradecía con su propio dictamen.
[$upsellNivel] = risk_level_visual($upsellScore);
// Por el SCORE, no por el texto: al cambiar las etiquetas a GRAVE / A REVISAR,
// comparar con 'ALTO' dejaba de encontrar nada y el upsell se quedaba siempre en
// su variante más suave sin que nadie lo notara.
$upsellVariant = $upsellScore >= (int) solvencia('umbralAlto', 60)
    ? 'alto'
    : ($upsellScore >= (int) solvencia('umbralMedio', 30) ? 'medio' : 'bajo');

// Cuota restante: antes vivía en una caja propia justo debajo de este bloque, lo que
// equivalía a decir "aún te queda gratis" inmediatamente después de pedir 29 €.
// Aquí funciona al revés, como argumento de escasez.
$upsellUsed      = (int)($riskQuota['views_used'] ?? 0);
$upsellLimit     = (int)($riskQuota['views_limit'] ?? solvencia('consultasGratis', 3));
$upsellRemaining = max(0, $upsellLimit - $upsellUsed);

if ($upsellRemaining === 0) {
    $upsellQuota = 'Has agotado tus ' . $upsellLimit . ' consultas gratuitas de este mes.';
} elseif ($upsellRemaining === 1) {
    $upsellQuota = 'Te queda <strong>1 consulta gratuita</strong> este mes.';
} else {
    $upsellQuota = 'Te quedan <strong>' . $upsellRemaining . ' consultas gratuitas</strong> este mes.';
}

// El encuadre: el BADGE sigue al nivel (para no contradecir al dictamen) y el
// TEXTO sigue a los hechos (si hay incidencias, se nombran).
$upsellNombreHtml = '<strong>' . esc($upsellName) . '</strong>' . company_punto($upsellName);
$upsellGancho = $upsellTotal > 0
    ? 'Acabas de encontrar ' . ($upsellTotal === 1 ? 'una incidencia' : $upsellTotal . ' incidencias')
        . ' en ' . $upsellNombreHtml
        . ' Lo que importa ahora es qué pasa después: te avisamos por correo el día que aparezca un acto nuevo a su nombre en el BORME, sin que tengas que volver a mirarlo.'
    : '';

if ($upsellVariant === 'alto') {
    $upsellBadge   = ['bg' => '#fef2f2', 'border' => '#fecaca', 'color' => '#b91c1c', 'text' => '⚠️ Constan incidencias graves'];
    $upsellTitle   = 'Una empresa así no se queda quieta';
    $upsellCopy    = $upsellGancho ?: 'El perfil de <strong>' . esc($upsellName) . '</strong> puede cambiar con cualquier publicación del BORME. Te avisamos por correo el día que se mueva.';
} elseif ($upsellVariant === 'medio') {
    $upsellBadge   = ['bg' => '#fffbeb', 'border' => '#fde68a', 'color' => '#b45309', 'text' => '🔍 Constan incidencias · conviene revisar'];
    $upsellTitle   = $upsellHigh > 0
        ? 'Hay algo aquí que conviene no perder de vista'
        : 'Un semáforo en ámbar hoy puede ser rojo en seis meses';
    $upsellCopy    = $upsellGancho ?: 'El perfil de <strong>' . esc($upsellName) . '</strong> cambia con cada publicación del BORME. En vez de volver a consultarlo cada mes, deja que te avisemos nosotros cuando se mueva.';
} else {
    $upsellBadge   = ['bg' => '#f0fdf4', 'border' => '#bbf7d0', 'color' => '#15803d', 'text' => '🛡️ Empresa sin incidencias'];
    $upsellTitle   = 'Hoy está limpia. ¿Y dentro de seis meses?';
    $upsellCopy    = '<strong>' . esc($upsellName) . '</strong> no presenta incidencias, y eso es exactamente lo que quieres que siga siendo verdad el día que factures. Te avisamos si algo cambia en el Registro Mercantil.';
}

/*
 * EL ARGUMENTO NO PUEDE SER ALGO QUE YA TIENE.
 *
 * Todas las variantes venden "te avisamos por correo cuando se mueva". Pero un
 * usuario gratuito que ya tiene esta empresa en vigilancia YA recibe ese aviso, y
 * gratis: leerlo como si fuera lo que va a comprar es la forma más rápida de que
 * decida que no necesita Pro.
 *
 * El estado de vigilancia NO se puede renderizar aquí: este bloque viaja por AJAX
 * pero la ficha la cachea Cloudflare, así que la verdad del usuario la pinta el
 * mismo JS que ya repinta el chip de la cabecera. Se emiten las dos frases y se
 * enseña la que toca.
 */
$upsellCopyVigilando = 'Ya tienes a ' . $upsellNombreHtml
    . ' en vigilancia, así que ese aviso te va a llegar. Lo que añade Pro es escala: hasta '
    . (int) solvencia('vigilanciasPro', 25) . ' empresas vigiladas a la vez y '
    . (int) solvencia('consultasPro', 300) . ' consultas al mes en vez de ' . $upsellLimit . '.';

$upsellTrackMeta = esc(json_encode([
    'cif'       => $company['cif'] ?? '',
    'score'     => $upsellScore,
    'alerts'    => $upsellTotal,
    'variant'   => $upsellVariant,
    'remaining' => $upsellRemaining,
], JSON_UNESCAPED_UNICODE), 'attr');
?>

<!-- Misma tarjeta que el resto de la ficha: blanca, borde fino #e2e8f0 y sombra
     suave. Antes llevaba un borde azul de 2 px y un fondo degradado, y era el
     único elemento de toda la página con ese tratamiento: llamaba la atención
     por romper, no por ser importante.
     El énfasis lo dan ahora el contenido (badge, titular, botón) y una línea de
     acento arriba con el MISMO degradado que ya usa la cabecera del bloque de
     riesgo, así que suma al diseño en vez de pelearse con él. -->
<div id="risk-pro-upsell" data-track-view="risk_pro_upsell_view" data-track-meta="<?= $upsellTrackMeta ?>" style="position: relative; overflow: hidden; margin-top: 24px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 26px 28px; box-shadow: 0 4px 16px -6px rgba(15, 23, 42, 0.08);">
    <div style="position: absolute; top: 0; left: 0; right: 0; height: 3px; background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%);"></div>

    <div style="display: flex; flex-wrap: wrap; gap: 28px; align-items: center; justify-content: space-between;">

        <!-- ARGUMENTO -->
        <div style="flex: 1; min-width: 280px;">
            <div style="display: inline-flex; align-items: center; gap: 6px; background: <?= $upsellBadge['bg'] ?>; border: 1px solid <?= $upsellBadge['border'] ?>; color: <?= $upsellBadge['color'] ?>; padding: 4px 12px; border-radius: 999px; font-size: 0.74rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.3px; margin-bottom: 12px;">
                <?= $upsellBadge['text'] ?>
            </div>

            <h4 style="margin: 0 0 8px 0; font-size: 1.3rem; font-weight: 900; color: #0f172a; letter-spacing: -0.4px; line-height: 1.25;">
                <!-- "No perder de vista" se lo dices a alguien que ya la tiene vigilada:
                     el titular pedía justo lo que el párrafo de debajo reconoce que ya
                     hace. Vigilando, la pregunta que queda no es ésta empresa sino las
                     demás, que además es la venta real de Pro. -->
                <span data-upsell-copy="off"><?= esc($upsellTitle) ?></span>
                <span data-upsell-copy="on" hidden>Ya vigilas esta empresa. ¿Y el resto de tus clientes?</span>
            </h4>

            <p style="margin: 0 0 16px 0; font-size: 0.92rem; color: #334155; line-height: 1.5; max-width: 520px;">
                <span data-upsell-copy="off"><?= $upsellCopy ?></span>
                <span data-upsell-copy="on" hidden><?= $upsellCopyVigilando ?></span>
            </p>

            <div style="display: inline-flex; align-items: center; gap: 8px; background: #ffffff; border: 1px solid #bfdbfe; border-radius: 10px; padding: 8px 14px; margin-bottom: 16px; font-size: 0.84rem; color: #1e3a8a;">
                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" style="flex-shrink:0;"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                <span><?= $upsellQuota ?> Con Pro dejas de contarlas — y dejas de tener que acordarte de volver.</span>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 8px 20px; color: #1e3a8a; font-size: 0.85rem; font-weight: 600;">
                <span style="display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <!-- Misma razón que el párrafo: a quien ya vigila esta empresa, la
                         primera ventaja de la lista es algo que ya tiene. Se cambia por
                         lo único que aquí es nuevo para él. -->
                    <strong data-upsell-copy="off">Vigilancia del BORME con aviso por correo</strong>
                    <strong data-upsell-copy="on" hidden><?= (int) solvencia('consultasPro', 300) ?> consultas al mes</strong>
                </span>
                <span style="display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Hasta <?= (int) solvencia('vigilanciasPro', 25) ?> empresas vigiladas a la vez
                </span>
                <span style="display: inline-flex; align-items: center; gap: 6px;">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Sin permanencia
                </span>
            </div>
        </div>

        <!-- PRECIO Y ACCIÓN -->
        <?php
        // El plan anual vivía en un enlace subrayado debajo del botón ("o 290 € /
        // año — ..."), que es donde se pone lo que uno no espera que se pulse.
        // Ahora son dos opciones del mismo peso y el precio cambia al elegir.
        //
        // Todo con CSS (:checked + ~), sin una línea de JS: este bloque llega por
        // AJAX y se inserta con innerHTML, que NO ejecuta <script>. Las <style> sí
        // se aplican, así que este es el único camino que funciona en la ficha
        // cacheada. Los ids llevan sufijo único por si conviven dos tarjetas.
        $ciclo = 'pro-' . substr(md5(($company['cif'] ?? '') . 'upsell'), 0, 6);
        ?>
        <style>
            .<?= $ciclo ?>-radio { position: absolute; width: 1px; height: 1px; opacity: 0; margin: 0; }
            /* Conmutador de ciclo. Las dos etiquetas ocupan UNA sola línea cada una
               (el "−17 %" va en línea, no debajo): si una tiene dos líneas y la otra
               una, el control queda descompensado y parece roto. */
            .<?= $ciclo ?>-ciclo {
                display: grid; grid-template-columns: 1fr 1fr; gap: 0;
                background: #f1f5f9; border: 1px solid #e2e8f0; border-radius: 999px;
                padding: 3px; margin-bottom: 16px;
            }
            .<?= $ciclo ?>-ciclo label {
                display: flex; align-items: center; justify-content: center; gap: 5px;
                height: 32px; cursor: pointer; user-select: none; white-space: nowrap;
                border-radius: 999px; font-size: 0.79rem; font-weight: 700;
                color: #64748b; transition: color 0.15s, background 0.15s, box-shadow 0.15s;
            }
            .<?= $ciclo ?>-ciclo label:hover { color: #334155; }
            .<?= $ciclo ?>-ahorro {
                font-size: 0.62rem; font-weight: 800; color: #15803d;
                background: #dcfce7; border-radius: 999px; padding: 2px 6px; line-height: 1;
                transition: all 0.15s;
            }
            /* Opción activa */
            #<?= $ciclo ?>-mes:checked ~ .<?= $ciclo ?>-ciclo label[for="<?= $ciclo ?>-mes"],
            #<?= $ciclo ?>-anual:checked ~ .<?= $ciclo ?>-ciclo label[for="<?= $ciclo ?>-anual"] {
                background: #ffffff; color: #0f172a; font-weight: 800;
                box-shadow: 0 1px 4px rgba(15, 23, 42, 0.14);
            }
            #<?= $ciclo ?>-anual:checked ~ .<?= $ciclo ?>-ciclo .<?= $ciclo ?>-ahorro {
                background: #16a34a; color: #ffffff;
            }
            /* Foco de teclado: el radio está oculto, el anillo lo pinta la etiqueta */
            .<?= $ciclo ?>-radio:focus-visible ~ .<?= $ciclo ?>-ciclo label[for="<?= $ciclo ?>-mes"],
            .<?= $ciclo ?>-radio:focus-visible ~ .<?= $ciclo ?>-ciclo label[for="<?= $ciclo ?>-anual"] {
                outline: 2px solid #2563eb; outline-offset: 1px;
            }
            /* Qué precio se ve */
            .<?= $ciclo ?>-solo-anual { display: none; }
            #<?= $ciclo ?>-anual:checked ~ .<?= $ciclo ?>-precio .<?= $ciclo ?>-solo-anual { display: block; }
            #<?= $ciclo ?>-anual:checked ~ .<?= $ciclo ?>-precio .<?= $ciclo ?>-solo-mes { display: none; }
        </style>

        <form method="post" action="<?= site_url('billing/checkout') ?>" style="flex-shrink: 0; width: 280px; max-width: 100%; margin: 0; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 20px; box-sizing: border-box;">
            <?= csrf_field() ?>
            <input type="hidden" name="plan" value="risk_pro">
            <input type="hidden" name="source" value="risk_profile_unlocked">

            <div style="text-align: center; font-size: 0.7rem; font-weight: 800; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 10px;">
                Solvencia Pro
            </div>

            <!-- Los radios van aquí, hermanos de todo lo que cambia: el selector ~
                 de CSS solo alcanza a los hermanos posteriores. -->
            <input class="<?= $ciclo ?>-radio" type="radio" name="period" value="monthly" id="<?= $ciclo ?>-mes" checked
                   data-track-click="risk_pro_upsell_ciclo" data-track-element="monthly" data-track-meta="<?= $upsellTrackMeta ?>">
            <input class="<?= $ciclo ?>-radio" type="radio" name="period" value="annual" id="<?= $ciclo ?>-anual"
                   data-track-click="risk_pro_upsell_ciclo" data-track-element="annual" data-track-meta="<?= $upsellTrackMeta ?>">

            <div class="<?= $ciclo ?>-ciclo">
                <label for="<?= $ciclo ?>-mes">Mensual</label>
                <label for="<?= $ciclo ?>-anual">Anual <span class="<?= $ciclo ?>-ahorro">−<?= solvencia('precios.pro_anual_descuento', '17 %') ?></span></label>
            </div>

            <div class="<?= $ciclo ?>-precio" style="text-align: center; margin-bottom: 14px;">
                <div class="<?= $ciclo ?>-solo-mes">
                    <div style="font-size: 2rem; font-weight: 900; color: #0f172a; line-height: 1;">
                        <?= solvencia('precios.pro_mensual', '29 €') ?><span style="font-size: 0.8rem; font-weight: 600; color: #64748b;"> / mes + IVA</span>
                    </div>
                    <div style="margin-top: 5px; min-height: 17px; font-size: 0.72rem; color: #94a3b8;">Se cobra cada mes</div>
                </div>

                <div class="<?= $ciclo ?>-solo-anual">
                    <div style="font-size: 2rem; font-weight: 900; color: #0f172a; line-height: 1;">
                        <?= solvencia('precios.pro_anual_mes', '24,16 €') ?><span style="font-size: 0.8rem; font-weight: 600; color: #64748b;"> / mes + IVA</span>
                    </div>
                    <div style="margin-top: 5px; min-height: 17px; font-size: 0.72rem; color: #15803d; font-weight: 700;">
                        <?= solvencia('precios.pro_anual', '290 €') ?>/año &bull; ahorras <?= solvencia('precios.pro_anual_ahorro', '58 €') ?>
                    </div>
                </div>

                <!-- Antes decía "... en Informa D&B o Axesor": afirmar el precio de
                     un tercero es publicidad comparativa y hay que poder sostenerlo.
                     El ancla funciona igual sin el nombre; se puede volver a poner
                     con Config\Solvencia::$nombrarCompetidores. -->
                <div style="margin-top: 10px; font-size: 0.75rem; color: #64748b; line-height: 1.35;">
                    Un informe suelto de <strong><?= esc($upsellName) ?></strong> cuesta
                    <strong style="color: #ef4444;"><?= solvencia('precios.informe_tradicional', '20–44 €') ?></strong>
                    <?php if (solvencia('nombrarCompetidores', false)): ?>
                        en <?= esc(solvencia('competidores', 'Informa D&B / Axesor')) ?>
                    <?php else: ?>
                        en un proveedor tradicional
                    <?php endif; ?>
                </div>
            </div>

            <button type="submit" data-loading="Abriendo el pago…" data-track-click="risk_pro_upsell_cta" data-track-meta="<?= $upsellTrackMeta ?>" style="width: 100%; border: none; cursor: pointer; background: #2563eb; color: #fff; padding: 13px 10px; border-radius: 10px; font-weight: 900; font-size: 0.95rem; box-shadow: 0 6px 18px rgba(37, 99, 235, 0.35); transition: all 0.2s;" onmouseover="this.style.background='#1d4ed8'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='#2563eb'; this.style.transform='translateY(0)';">
                Activar Solvencia Pro ⭐
            </button>

            <?php if (solvencia('garantiaActiva', true)): ?>
                <!-- La garantía se lee justo debajo del botón, que es donde se duda. -->
                <div style="margin-top: 11px; background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 9px; padding: 8px 11px; color: #15803d; font-size: 0.73rem; line-height: 1.4; text-align: center;">
                    🛡️ <strong><?= (int) solvencia('garantiaDias', 30) ?> días de garantía.</strong> Si no te sirve, te devolvemos el dinero.
                    <a href="<?= site_url('garantia') ?>" target="_blank" rel="noopener" style="color: #15803d; text-decoration: underline; white-space: nowrap;">Cómo funciona</a>
                </div>
            <?php endif; ?>

            <div style="display: flex; align-items: center; justify-content: center; gap: 5px; margin-top: 10px; color: #64748b; font-size: 0.72rem;">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                Pago seguro Stripe &bull; Cancela en 1 clic
            </div>
        </form>

    </div>
</div>
