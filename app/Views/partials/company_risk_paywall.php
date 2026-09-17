<?php
$compBtnId = (int)($company['id'] ?? 0);
helper('company');
/*
 * $riskProfile llega SIEMPRE con contenido en los cuatro sitios que pintan este
 * parcial: RiskProfileController corta antes con NO_RISK_PROFILE, renderRiskBlock()
 * devuelve state='none', risk_profile/index.php lo desvía en una rama anterior y en
 * company.php el bloque entero va dentro de un if (!empty($riskProfile)).
 *
 * La rama de abajo que no enseña titular es, por tanto, red de seguridad por si
 * mañana aparece un quinto punto de entrada — no un estado que se pueda reproducir.
 * (El comentario anterior decía "no siempre llega (buscador de riesgo)" y mandaba a
 * quien lo leyera a intentar reproducir algo imposible.)
 */
$riskProfile = $riskProfile ?? null;
// Por AJAX el nombre llega crudo y en MAYÚSCULAS; sin normalizar, el anclaje de
// precio queda como "DENDARA SOCIEDAD LIMITADA DE SERVICIOS AMBIENTALES".
$compNameStr = company_short_name(company_display_name($company['name'] ?? '', 'Empresa'));
$compCifStr = $company['cif'] ?? '';
// Sufijo de los ids del selector de ciclo. Único por empresa, por si alguna vez
// conviven dos tarjetas de precio en la misma página.
$cicloPw = 'pw-' . substr(md5($compCifStr . 'paywall'), 0, 6);
$paywallTrackMeta = esc(json_encode([
    'cif'        => $compCifStr,
    'views_used' => (int)($riskQuota['views_used'] ?? 3),
], JSON_UNESCAPED_UNICODE), 'attr');

/*
 * CUÁNTO HAY DETRÁS DEL MURO. De esto depende todo el texto.
 *
 * El titular de arriba ya enseña la puntuación, el nivel y la incidencia MÁS GRAVE.
 * Así que lo que queda por vender no es el mismo argumento siempre:
 *
 *   0 incidencias → no hay nada que listar. Se vende poder demostrar que está limpia.
 *   1 incidencia  → ya la ha leído arriba. Prometer "cada acto con su fecha" es
 *                   prometer una lista de un elemento: quien paga por eso pide la
 *                   devolución, y con razón. Lo que de verdad añade valor aquí es
 *                   saber si va a MÁS, y eso es la vigilancia.
 *   2 o más       → arriba solo aparece una: el resto sí es contenido real y se puede
 *                   nombrar por su número exacto, que convence más que "el dictamen
 *                   completo".
 *
 * Decir siempre lo mismo es lo que hacía que una empresa con una sola incidencia
 * recibiera la misma promesa grandilocuente que una con doce.
 */
$pwEventos = $riskProfile['data']['canonical_events'] ?? [];
$pwNum     = is_array($pwEventos) ? count($pwEventos) : 0;
$pwLimpia  = !empty($riskProfile) && $pwNum === 0;
$pwUnica   = !empty($riskProfile) && $pwNum === 1;
$pwGratis  = (int) solvencia('consultasGratis', 3);

/*
 * "SIN INCIDENCIAS" Y "SIN DATOS" NO SON LO MISMO, Y SE VEÍAN IGUAL.
 *
 * Cero eventos puede significar dos cosas opuestas: una empresa con histórico
 * publicado en el que no hay nada adverso —que es una buena noticia y se puede
 * afirmar—, o una empresa de la que apenas hay nada publicado, donde el cero no
 * dice nada sobre su solvencia.
 *
 * Las dos salían como "Está limpia". Afirmar que una empresa está limpia cuando lo
 * que pasa es que no la conocemos es el peor fallo posible de este producto: el día
 * que una de esas entre en concurso, el cliente no vuelve, y con razón.
 *
 * El motor ya calcula la confianza; se usa el mismo corte del 60 % que el bloque de
 * dimensiones para no tener dos umbrales distintos diciendo cosas distintas.
 */
$pwConf   = isset($riskProfile['data']['confidence_score'])
    ? (int) $riskProfile['data']['confidence_score']
    : null;
$pwAFalta = $pwLimpia && $pwConf !== null && $pwConf < 60;

/*
 * El fondo borroso imita el contenido de pago. Llevaba un 55/MEDIO dibujado a mano,
 * que aparecía debajo del 50 real del titular: dos puntuaciones distintas de la misma
 * empresa en la misma pantalla. El número ES el producto, y un descuido así lo pone en
 * duda entero, aunque esté desenfocado.
 */
$pwVis      = risk_level_visual(!empty($riskProfile) ? (int) ($riskProfile['risk_score'] ?? 0) : 55);
$pwBgScore  = !empty($riskProfile) ? (int) ($riskProfile['risk_score'] ?? 0) : 55;
$pwBgNivel  = $pwVis[0];  // El motor sigue emitiendo BAJO/MEDIO/ALTO y eso no se toca: hay consultas
$pwBgColor  = $pwVis[1];
?>

<!-- PAYWALL CUOTA ALCANZADA (3/3) -->
<style>
    .risk-paywall-card {
        position: relative;
        z-index: 10;
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        width: 100%;
        max-width: 900px;
        background: #ffffff;
        padding: 36px 36px 30px;
        border-radius: 20px;
        box-shadow: 0 20px 45px rgba(0, 0, 0, 0.12);
        border: 1px solid #e2e8f0;
        margin: 16px auto;
    }
    .risk-paywall-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 16px;
        width: 100%;
        margin-bottom: 22px;
        text-align: left;
        /* Las tarjetas se estiraban a la altura de Pro y dejaban un agujero blanco en
           mitad de las otras dos. El estirón se justificaría si alineara los botones,
           pero no lo hace: Pro lleva la garantía DEBAJO de su botón, así que quedaban
           desalineados igual. Sin estirar, cada una mide lo que ocupa y la recomendada
           es visiblemente la más alta, que es justo donde queremos la mirada. */
        align-items: start;
    }
    @media (max-width: 768px) {
        .risk-paywall-grid {
            grid-template-columns: 1fr;
        }
        .risk-paywall-card {
            padding: 24px 16px;
        }
    }

    /* Selector mensual/anual. Solo CSS: este bloque llega por AJAX y se inserta
       con innerHTML, que no ejecuta <script> (las <style> sí se aplican). */
    .<?= $cicloPw ?>-radio { position: absolute; width: 1px; height: 1px; opacity: 0; margin: 0; }
    .<?= $cicloPw ?>-ciclo {
        display: grid; grid-template-columns: 1fr 1fr; gap: 0;
        background: #e0ebfd; border: 1px solid #cfe0fb; border-radius: 999px;
        padding: 3px; margin-bottom: 10px;
    }
    .<?= $cicloPw ?>-ciclo label {
        display: flex; align-items: center; justify-content: center; gap: 4px;
        height: 28px; cursor: pointer; user-select: none; white-space: nowrap;
        border-radius: 999px; font-size: 0.72rem; font-weight: 700;
        color: #475569; transition: color 0.15s, background 0.15s, box-shadow 0.15s;
    }
    .<?= $cicloPw ?>-ahorro {
        font-size: 0.58rem; font-weight: 800; color: #15803d;
        background: #dcfce7; border-radius: 999px; padding: 2px 5px; line-height: 1;
    }
    #<?= $cicloPw ?>-anual:checked ~ .<?= $cicloPw ?>-ciclo .<?= $cicloPw ?>-ahorro {
        background: #16a34a; color: #ffffff;
    }
    #<?= $cicloPw ?>-mes:checked ~ .<?= $cicloPw ?>-ciclo label[for="<?= $cicloPw ?>-mes"],
    #<?= $cicloPw ?>-anual:checked ~ .<?= $cicloPw ?>-ciclo label[for="<?= $cicloPw ?>-anual"] {
        background: #ffffff; color: #0f172a; font-weight: 800;
        box-shadow: 0 1px 4px rgba(15, 23, 42, 0.16);
    }
    .<?= $cicloPw ?>-radio:focus-visible ~ .<?= $cicloPw ?>-ciclo label { outline: 2px solid #2563eb; outline-offset: 1px; }
    .<?= $cicloPw ?>-solo-anual { display: none; }
    #<?= $cicloPw ?>-anual:checked ~ .<?= $cicloPw ?>-precio .<?= $cicloPw ?>-solo-anual { display: block; }
    #<?= $cicloPw ?>-anual:checked ~ .<?= $cicloPw ?>-precio .<?= $cicloPw ?>-solo-mes { display: none; }
</style>

<div style="padding: 20px; position: relative; display: flex; align-items: center; justify-content: center; min-height: 480px; overflow: hidden; background: #fafafa; margin: -24px; margin-bottom: 0; border-radius: 0 0 16px 16px;">
    
    <!-- BLURRED BACKGROUND (Scorecard & Factors Behind) -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; filter: blur(6px); opacity: 0.45; pointer-events: none; display: flex; flex-wrap: nowrap; gap: 80px; align-items: center; justify-content: center; padding: 24px;">
        <!-- Fake Score Card -->
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px 20px; text-align: center; width: 190px;">
            <div style="font-size: 0.8rem; font-weight: bold; color: #64748b; margin-bottom: 12px;"><?= risk_titulo_indicador() ?></div>
            <div style="width: 80px; height: 75px; background: <?= $pwBgColor ?>; border-radius: 12px; margin: 0 auto 12px auto; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 2rem; font-weight: 800;"><?= $pwBgScore ?></div>
            <div style="font-size: 1.1rem; font-weight: bold; color: <?= $pwBgColor ?>;"><?= esc(mb_strtoupper((string) $pwBgNivel, 'UTF-8')) ?></div>
        </div>

        <!-- Fake Factors List -->
        <div style="flex: 1; max-width: 480px; display: flex; flex-direction: column; gap: 12px;">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; display: flex; gap: 12px; align-items: center;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #fee2e2; color: #ef4444; display: flex; align-items: center; justify-content: center; font-weight: bold;">!</div>
                <div style="flex: 1;">
                    <div style="height: 14px; width: 60%; background: #0f172a; border-radius: 4px; margin-bottom: 6px;"></div>
                    <div style="height: 10px; width: 85%; background: #cbd5e1; border-radius: 3px;"></div>
                </div>
            </div>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; display: flex; gap: 12px; align-items: center;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #dcfce7; color: #16a34a; display: flex; align-items: center; justify-content: center; font-weight: bold;">✓</div>
                <div style="flex: 1;">
                    <div style="height: 14px; width: 45%; background: #0f172a; border-radius: 4px; margin-bottom: 6px;"></div>
                    <div style="height: 10px; width: 70%; background: #cbd5e1; border-radius: 3px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOREGROUND CONVERSION MODAL / CARD (ANCHO AMPLIADO Y PACKS EN PARALELO) -->
    <div class="risk-paywall-card" data-track-view="risk_paywall_view" data-track-meta="<?= $paywallTrackMeta ?>">
        
        <?php if (!empty($riskProfile)): ?>
            <!-- El titular del dictamen, igual que lo ve un anónimo en el teaser.
                 Sin esto, registrarse hacía ver MENOS: el visitante de Google veía
                 el score y el motivo, y el usuario con cuenta se encontraba un muro
                 de precios sin un solo dato. Lo que se compra es el desglose. -->
            <?= view('partials/company_risk_titular', ['riskProfile' => $riskProfile, 'tiMargen' => '18px']) ?>
        <?php endif; ?>

        <!-- Icon & Badge -->
        <div style="display: inline-flex; align-items: center; gap: 6px; background: #fffbeb; border: 1px solid #fde68a; color: #b45309; padding: 4px 12px; border-radius: 999px; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; margin-bottom: 14px;">
            ⚠️ Límite Mensual Alcanzado (<?= (int) solvencia('consultasGratis', 3) ?>/<?= (int) solvencia('consultasGratis', 3) ?>)
        </div>

        <h3 style="font-size: 1.6rem; font-weight: 900; color: #0f172a; margin: 0 0 10px 0; letter-spacing: -0.5px; text-wrap: balance;">
            <?php if ($pwAFalta): ?>
                No consta nada, que no es lo mismo que estar limpia
            <?php elseif ($pwLimpia): ?>
                Está limpia. Otra cosa es poder demostrarlo
            <?php elseif ($pwUnica): ?>
                Ya sabes qué falla. Falta saber si va a más
            <?php elseif (!empty($riskProfile)): ?>
                <?php
                // En un titular, "las 2" canta a plantilla rellenada por una máquina.
                // Del dos al nueve va en letra; a partir de diez, la cifra se lee mejor.
                $pwLetras = [2 => 'dos', 3 => 'tres', 4 => 'cuatro', 5 => 'cinco',
                             6 => 'seis', 7 => 'siete', 8 => 'ocho', 9 => 'nueve'];
                ?>
                Arriba solo está la peor de las <?= $pwLetras[$pwNum] ?? $pwNum ?>
            <?php else: ?>
                Has alcanzado tus <?= $pwGratis ?> consultas gratuitas
            <?php endif; ?>
        </h3>

        <p style="color: #475569; margin: 0 0 <?= ($pwLimpia || $pwUnica) ? '12px' : '22px' ?> 0; font-size: 0.95rem; line-height: 1.5; max-width: 660px;">
            <?php if ($pwAFalta): ?>
                De <strong style="color: #0f172a;"><?= esc($compNameStr) ?></strong> no consta ninguna incidencia, pero la
                cobertura de su histórico es del <?= $pwConf ?> %: puede ser una empresa sin nada que declarar o una de la
                que apenas hay nada publicado. El dictamen te dice cuál de las dos, con lo que se ha podido verificar y
                lo que no.
            <?php elseif ($pwLimpia): ?>
                No constan incidencias de <strong style="color: #0f172a;"><?= esc($compNameStr) ?></strong> en el BORME, y ya has
                gastado tus <?= $pwGratis ?> consultas gratuitas de este mes. El dictamen completo te da el histórico verificado y
                de dónde sale la puntuación: lo que se adjunta a un expediente de cliente.
            <?php elseif ($pwUnica): ?>
                <!-- No se repite aquí que ha agotado la cuota: ya lo dicen la píldora roja
                     de la cabecera y la amarilla de justo encima. Tres veces la misma mala
                     noticia antes de llegar a lo que ofreces no aumenta la urgencia, cansa. -->
                De <strong style="color: #0f172a;"><?= esc($compNameStr) ?></strong> consta una sola incidencia y ya la tienes
                ahí arriba: lo que queda por ver es desde cuándo, cuánto pesa en esa puntuación y qué más se ha revisado
                para llegar a ella.
            <?php elseif (!empty($riskProfile)): ?>
                De <strong style="color: #0f172a;"><?= esc($compNameStr) ?></strong> constan
                <strong style="color: #0f172a;"><?= $pwNum ?> incidencias</strong> y el titular solo nombra la más grave. El
                dictamen las lista todas con su fecha, su gravedad y el peso que tiene cada una en la puntuación.
            <?php else: ?>
                Has analizado el límite mensual de <?= $pwGratis ?> empresas gratuitas. Para consultar el dictamen de <strong style="color: #0f172a;"><?= esc($compNameStr) ?></strong> o desbloquear más empresas:
            <?php endif; ?>
        </p>

        <?php if ($pwUnica): ?>
            <!-- El argumento que un informe suelto no puede dar, y el único honesto
                 cuando solo hay una incidencia: la foto ya la tiene, lo que no tiene
                 es la alarma. Vigilar no gasta consultas, así que decirlo aquí no
                 canibaliza la venta: quita el callejón sin salida. -->
            <p style="color: #64748b; margin: 0 0 20px 0; font-size: 0.85rem; line-height: 1.5; max-width: 660px;">
                <!-- La cabecera de la ficha ya dice "te avisamos por correo si aparece un
                     acto nuevo en el BORME". Repetirlo palabra por palabra a cuarenta
                     palabras de distancia se nota; aquí se dice lo que aporta de nuevo,
                     que es que vigilar no cuesta consulta. -->
                Un informe te da esa foto una vez. Si lo que te preocupa es que la cosa empeore, el botón
                <strong style="color: #475569;">Vigilar empresa</strong> de arriba no gasta ninguna de tus consultas.
            </p>
        <?php endif; ?>

        <?php if ($pwLimpia): ?>
            <!-- Vigilar no cuesta consultas y está ahí arriba. Decirlo aquí no
                 canibaliza nada —ya es gratis— y evita que el muro parezca la
                 única salida, que es lo que hace cerrar la pestaña. -->
            <p style="color: #64748b; margin: 0 0 20px 0; font-size: 0.85rem; line-height: 1.5; max-width: 660px;">
                Y si lo que quieres es enterarte el día que eso cambie, puedes vigilarla desde el botón de arriba
                sin gastar ninguna consulta.
            </p>
        <?php endif; ?>

        <?php
        /*
         * Ancla de precio.
         *
         * Aquí ponía "Ahorras hasta un 85%", que solo sale si el cliente consulta
         * unas ocho empresas al mes — y esa condición no estaba a la vista. El
         * punto de equilibrio sí es comprobable y además es un argumento mejor,
         * porque el lector puede hacer la cuenta él mismo.
         */
        $pwMes  = (float) solvencia('preciosNum.pro_mensual', 29);
        $pwInf  = (float) solvencia('preciosNum.informe_tradicional', 20);
        $pwCorte = ($pwInf > 0) ? max(2, (int) ceil($pwMes / $pwInf)) : 2;

        $pwNombraComp = (bool) solvencia('nombrarCompetidores', false);
        $pwEtiqueta   = $pwNombraComp
            ? (string) solvencia('competidores', 'Informa D&B / Axesor')
            : (string) solvencia('referenciaTradicional', 'Un informe tradicional');
        ?>
        <!-- COMPARATIVA VS INFORME TRADICIONAL (PRICE ANCHORING) -->
        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 12px 18px; width: 100%; margin-bottom: 22px; box-sizing: border-box; text-align: left;">
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 8px; margin-bottom: 8px;">
                <!-- Decía "lo que cuesta un informe suelto de X" encima de dos columnas
                     donde la derecha NO es un informe suelto, sino una suscripción. El
                     encabezado nombra ahora la elección real, y cada columna dice de qué
                     tipo de producto habla: comparar 20–44 € con 29 €/mes sin decirlo es
                     la clase de comparación que el lector detecta y te castiga. -->
                <span style="font-size: 0.72rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                    Informe suelto o vigilancia continua
                </span>
                <span style="background: #dcfce7; color: #15803d; font-size: 0.7rem; font-weight: 800; padding: 2px 8px; border-radius: 999px;">
                    Sale a cuenta desde la <?= $pwCorte === 2 ? 'segunda' : $pwCorte . '.ª' ?> empresa del mes
                </span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; font-size: 0.78rem;">
                <!-- La alternativa tradicional -->
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 8px 12px; color: #64748b;">
                    <div style="font-weight: 700; color: #94a3b8; font-size: 0.75rem; margin-bottom: 2px;"><?= esc($pwEtiqueta) ?></div>
                    <div style="color: #ef4444; font-weight: 800; font-size: 0.95rem;"><?= solvencia('precios.informe_tradicional', '20–44 €') ?> <span style="font-size: 0.7rem; font-weight: normal; color: #94a3b8;">/ informe</span></div>
                    <div style="font-size: 0.68rem; color: #94a3b8; margin-top: 2px;">Pago por empresa. Una foto del día que lo pides.</div>
                </div>
                <!-- APIEmpresas -->
                <div style="background: #eff6ff; border: 1.5px solid #bfdbfe; border-radius: 10px; padding: 8px 12px; color: #1e40af;">
                    <div style="font-weight: 800; color: #1d4ed8; font-size: 0.75rem; margin-bottom: 2px;">APIEmpresas Solvencia</div>
                    <div style="color: #16a34a; font-weight: 900; font-size: 0.95rem;"><?= solvencia('precios.pro_mensual', '29 €') ?> <span style="font-size: 0.7rem; font-weight: normal; color: #1e40af;">/ mes</span></div>
                    <div style="font-size: 0.68rem; color: #1e40af; font-weight: 700; margin-top: 2px;">✅ Vigila hasta <?= (int) solvencia('vigilanciasPro', 25) ?> empresas y te avisa al moverse</div>
                </div>
            </div>
        </div>

        <!-- Monetization Options Grid (3 Opciones alineadas horizontalmente) -->
        <div class="risk-paywall-grid">
            
            <!-- Option 1: PDF Download (Transactional 1 empresa) -->
            <div style="border: 1.5px solid #cbd5e1; background: #ffffff; border-radius: 14px; padding: 18px 16px; display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <div style="font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">PUNTUAL</div>
                    <div style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">1 Informe PDF</div>
                    <div style="font-size: 0.78rem; color: #475569; line-height: 1.35; margin-bottom: 8px;">Dictamen completo con el desglose y las fuentes.</div>
                    <!-- Las tres tarjetas se estiran a la altura de Pro, que lleva selector de
                         ciclo y garantía. Sin contenido, las dos primeras quedaban con un
                         agujero blanco entre la descripción y el precio. Se llena con lo que
                         de verdad incluye cada una, que además deja ver la escalera. -->
                    <ul style="list-style: none; padding: 0; margin: 0 0 12px 0; font-size: 0.73rem; color: #64748b; line-height: 1.5;">
                        <li>· Puntuación y peso de cada factor</li>
                        <!-- En una empresa sin eventos, prometer "los actos con su fecha" es
                             prometer una lista vacía: ahí lo que se entrega es qué se ha
                             mirado para poder afirmar que no hay nada. -->
                        <li>· <?= $pwNum > 0 ? 'Actos del BORME con su fecha' : 'Qué fuentes se han revisado' ?></li>
                        <li>· Listo para el expediente</li>
                        <li style="color: #94a3b8;">· Sin vigilancia ni avisos</li>
                    </ul>
                </div>
                <div>
                    <div style="font-size: 1.25rem; font-weight: 900; color: #0f172a; margin-bottom: 8px;"><?= solvencia('precios.pdf', '3,90 €') ?> <span style="font-size: 0.72rem; font-weight: 600; color: #64748b;">+ IVA</span></div>
                    <button type="button" onclick="openRiskPdfModal(<?= $compBtnId ?>, '<?= esc($compCifStr) ?>');" data-track-click="risk_paywall_cta" data-track-element="pdf_single" data-track-meta="<?= $paywallTrackMeta ?>" style="width: 100%; background: #f1f5f9; color: #0f172a; border: 1px solid #cbd5e1; padding: 10px 8px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#e2e8f0';" onmouseout="this.style.background='#f1f5f9';">
                        Descargar 1 PDF 📄
                    </button>
                </div>
            </div>

            <!-- Option 2: PACK 5 AUDITORÍAS (TRIPWIRE - LIMPIO CON TOQUE TEAL/PETRÓLEO MATE) -->
            <div style="border: 1.5px solid #cbd5e1; background: #ffffff; border-radius: 14px; padding: 18px 16px; display: flex; flex-direction: column; justify-content: space-between; position: relative;">
                <div style="position: absolute; top: -11px; right: 12px; background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 0.65rem; font-weight: 800; padding: 2px 8px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.3px;">
                    PAGO ÚNICO
                </div>
                <div>
                    <div style="font-size: 0.7rem; font-weight: 800; color: #64748b; text-transform: uppercase; margin-bottom: 4px;">SIN SUSCRIPCIÓN</div>
                    <div style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin-bottom: 4px;">Pack 5 Auditorías</div>
                    <div style="font-size: 0.78rem; color: #64748b; line-height: 1.35; margin-bottom: 8px;">5 auditorías completas + PDFs. <strong><?= number_format(((int) solvencia('centimos.pack5', 990)) / 500, 2, ',', '.') ?> €/informe</strong>. Sin caducidad.</div>
                    <!-- "Sin vigilancia ni avisos" no es letra pequeña defensiva: a 1,98 € el
                         informe, este pack es más barato que Pro por empresa, y si no se dice
                         en qué se diferencia, se come la suscripción él solo. Lo que separa a
                         los dos productos es la alarma, no el número de informes. -->
                    <ul style="list-style: none; padding: 0; margin: 0 0 12px 0; font-size: 0.73rem; color: #64748b; line-height: 1.5;">
                        <li>· Las 5 empresas que tú elijas</li>
                        <li>· Los usas cuando quieras</li>
                        <li style="color: #94a3b8;">· Sin vigilancia ni avisos</li>
                    </ul>
                </div>
                <div>
                    <div style="font-size: 1.25rem; font-weight: 900; color: #0f172a; margin-bottom: 8px;"><?= solvencia('precios.pack5', '9,90 €') ?> <span style="font-size: 0.72rem; font-weight: 600; color: #64748b;">+ IVA</span></div>
                    <form method="post" action="<?= site_url('billing/checkout') ?>" style="margin: 0;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="plan" value="risk_pack_5">
                        <input type="hidden" name="period" value="single">
                        <input type="hidden" name="cif" value="<?= esc($compCifStr) ?>">
                        <button type="submit" data-loading="Abriendo el pago…" data-track-click="risk_paywall_cta" data-track-element="pack_5" data-track-meta="<?= $paywallTrackMeta ?>" style="width: 100%; border: none; cursor: pointer; text-align: center; background: #0f766e; color: #ffffff; padding: 10px 8px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; transition: background 0.2s;" onmouseover="this.style.background='#115e59';" onmouseout="this.style.background='#0f766e';">
                            Comprar Pack 5 ⚡
                        </button>
                    </form>
                </div>
            </div>

            <!-- Option 3: Monthly Pro Subscription (HERO DESTACADO INDISCUTIBLE) -->
            <form method="post" action="<?= site_url('billing/checkout') ?>" style="border: 2.5px solid #2563eb; background: linear-gradient(180deg, #eff6ff 0%, #ffffff 100%); border-radius: 14px; padding: 18px 16px; display: flex; flex-direction: column; justify-content: space-between; position: relative; box-shadow: 0 12px 28px -4px rgba(37, 99, 235, 0.28); transform: translateY(-3px);">
                <div style="position: absolute; top: -12px; right: 12px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: #ffffff; font-size: 0.68rem; font-weight: 900; padding: 3px 12px; border-radius: 999px; text-transform: uppercase; letter-spacing: 0.4px; box-shadow: 0 3px 8px rgba(37, 99, 235, 0.35);">
                    ⭐ RECOMENDADO
                </div>
                <?= csrf_field() ?>
                <input type="hidden" name="plan" value="risk_pro">
                <input type="hidden" name="source" value="risk_paywall">
                <div>
                    <div style="font-size: 0.72rem; font-weight: 900; color: #1d4ed8; text-transform: uppercase; margin-bottom: 4px; letter-spacing: 0.3px;">VIGILANCIA CONTINUA</div>
                    <div style="font-size: 1.08rem; font-weight: 900; color: #0f172a; margin-bottom: 8px;">Solvencia Pro</div>
                    <?php
                    /*
                     * En lugar de una frase suelta, la misma lista que las otras dos tarjetas:
                     * con bullets a los lados y un párrafo aquí, la opción RECOMENDADA parecía
                     * la más pobre de las tres.
                     *
                     * Y arriba del todo, las consultas. Quien está leyendo esto ha llegado por
                     * haberse quedado sin las 3 del mes, y el número que resuelve exactamente
                     * ese problema no aparecía en ninguna parte de la página.
                     */
                    ?>
                    <ul style="list-style: none; padding: 0; margin: 0 0 12px 0; font-size: 0.75rem; color: #1e40af; line-height: 1.55; font-weight: 600;">
                        <li>· <strong><?= (int) solvencia('consultasPro', 300) ?> consultas al mes</strong>, en vez de <?= $pwGratis ?></li>
                        <li>· Vigila hasta <?= (int) solvencia('vigilanciasPro', 25) ?> empresas</li>
                        <li>· Aviso por correo al publicarse el acto</li>
                        <li>· Informes PDF incluidos</li>
                    </ul>
                </div>
                <div>
                    <!-- Mismo selector de ciclo que el upsell del dictamen, en versión
                         estrecha: el anual estaba en un enlace subrayado debajo del
                         botón, que es donde se pone lo que no se espera que se pulse. -->
                    <input class="<?= $cicloPw ?>-radio" type="radio" name="period" value="monthly" id="<?= $cicloPw ?>-mes" checked
                           data-track-click="risk_paywall_ciclo" data-track-element="monthly" data-track-meta="<?= $paywallTrackMeta ?>">
                    <input class="<?= $cicloPw ?>-radio" type="radio" name="period" value="annual" id="<?= $cicloPw ?>-anual"
                           data-track-click="risk_paywall_ciclo" data-track-element="annual" data-track-meta="<?= $paywallTrackMeta ?>">

                    <div class="<?= $cicloPw ?>-ciclo">
                        <label for="<?= $cicloPw ?>-mes">Mensual</label>
                        <label for="<?= $cicloPw ?>-anual">Anual <span class="<?= $cicloPw ?>-ahorro">−<?= solvencia('precios.pro_anual_descuento', '17 %') ?></span></label>
                    </div>

                    <div class="<?= $cicloPw ?>-precio">
                        <!-- El "+ IVA" faltaba SOLO aquí. Las dos tarjetas de al lado lo
                             llevan y el upsell del dictamen anuncia este mismo plan como
                             "29 € / mes + IVA": sin esto, Pro parecía el único precio con
                             impuestos incluidos y el cargo real no cuadraba con la ficha. -->
                        <div class="<?= $cicloPw ?>-solo-mes">
                            <div style="font-size: 1.35rem; font-weight: 900; color: #1d4ed8;"><?= solvencia('precios.pro_mensual', '29 €') ?> <span style="font-size: 0.72rem; font-weight: 600; color: #64748b;">/ mes + IVA</span></div>
                            <div style="font-size: 0.68rem; color: #94a3b8; margin-bottom: 8px;">Se cobra cada mes</div>
                        </div>
                        <div class="<?= $cicloPw ?>-solo-anual">
                            <div style="font-size: 1.35rem; font-weight: 900; color: #1d4ed8;"><?= solvencia('precios.pro_anual_mes', '24,16 €') ?> <span style="font-size: 0.72rem; font-weight: 600; color: #64748b;">/ mes + IVA</span></div>
                            <div style="font-size: 0.68rem; color: #15803d; font-weight: 700; margin-bottom: 8px;"><?= solvencia('precios.pro_anual', '290 €') ?>/año &bull; ahorras <?= solvencia('precios.pro_anual_ahorro', '58 €') ?></div>
                        </div>
                    </div>

                    <button type="submit" data-loading="Abriendo el pago…" data-track-click="risk_paywall_cta" data-track-element="pro" data-track-meta="<?= $paywallTrackMeta ?>" style="width: 100%; border: none; cursor: pointer; text-align: center; background: #2563eb; color: #fff; padding: 11px 8px; border-radius: 8px; font-weight: 900; font-size: 0.88rem; transition: all 0.2s; box-shadow: 0 6px 18px rgba(37, 99, 235, 0.4);" onmouseover="this.style.background='#1d4ed8'; this.style.transform='scale(1.02)';" onmouseout="this.style.background='#2563eb'; this.style.transform='none';">
                        Activar Pro ⭐
                    </button>

                    <?php if (solvencia('garantiaActiva', true)): ?>
                        <!-- La garantía va DENTRO de la tarjeta de Pro, no en la línea de
                             confianza de abajo: quien duda lo hace con el dedo sobre el botón,
                             no leyendo el pie. -->
                        <div style="margin-top: 9px; display: flex; align-items: flex-start; gap: 6px; text-align: left; color: #047857; font-size: 0.71rem; line-height: 1.35;">
                            <span style="flex-shrink: 0;">🛡️</span>
                            <span>
                                <strong><?= (int) solvencia('garantiaDias', 30) ?> días de garantía.</strong>
                                Si no te sirve, te devolvemos el dinero.
                                <a href="<?= site_url('garantia') ?>" target="_blank" rel="noopener" style="color: #047857; text-decoration: underline; white-space: nowrap;">Cómo</a>
                            </span>
                        </div>
                    <?php endif; ?>
                </div>
            </form>

        </div>

        <div style="display: flex; align-items: center; gap: 8px; color: #64748b; font-size: 0.78rem;">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
            Sin permanencia &bull; Factura con IVA deducible &bull; Pasarela segura Stripe
        </div>

    </div>
</div>
