<?php
/**
 * partials/company_risk_teaser.php
 * Lo que ve un visitante anónimo en el bloque de Perfil de Riesgo.
 *
 * Dos modos, según Config\Solvencia::$teaserModo:
 *
 *  - 'titular' (por defecto): se enseña el score, el nivel y UNA línea con el
 *    motivo principal. El desglose de actos, las fechas y el dictamen siguen
 *    detrás del registro. Así la ficha sirve a quien llega de Google —que es el
 *    90 % del embudo— y la pregunta "¿por qué?" queda abierta en la primera
 *    visita, no en la cuarta.
 *
 *  - 'opaco': no se revela nada del resultado. Convierte mejor por visita pero
 *    deja la ficha inútil sin registro.
 *
 * Ojo con el modo 'titular': el motivo es el TIPO de incidencia, nunca la fecha
 * ni el detalle del asiento. Esa distinción es la que separa "tienes un problema"
 * (gratis, y es lo que engancha) de "este es el problema" (de pago).
 *
 * Variables:
 * - $riskProfile (array)
 * - $company (array)
 * - $companyName (string, opcional)
 * - $redirectPath (string, opcional)
 */
helper(['company', 'risk_labels']);

$teaserEvents      = $riskProfile['data']['canonical_events'] ?? [];
$teaserTotalAlerts = count($teaserEvents);
$teaserHighAlerts  = 0;
$teaserMediumAlerts = 0;
// Por risk_event_severidad y no por la cadena: el motor emite tambien `critical`,
// que comparado a pelo contra 'high' no contaba como alerta seria en ningun sitio.
foreach ($teaserEvents as $ev) {
    $sev = risk_event_severidad($ev);
    if ($sev >= 3) $teaserHighAlerts++;
    elseif ($sev === 2) $teaserMediumAlerts++;
}

$teaserScore = (int)($riskProfile['risk_score'] ?? 50);
$teaserModo  = solvencia('teaserModo', 'titular') === 'opaco' ? 'opaco' : 'titular';
$teaserGratis = (int) solvencia('consultasGratis', 3);

[$teaserNivel, $teaserColor, $teaserFondo, $teaserBorde] = risk_level_visual($teaserScore);
$teaserNivel = $riskProfile['data']['risk_level'] ?? $teaserNivel;

$companyName = company_short_name(company_display_name($companyName ?? ($company['name'] ?? ''), 'Empresa'));
$compCif = $company['cif'] ?? '';
$compId  = (int)($company['id'] ?? 0);

/**
 * Motivo principal: el evento más grave del dictamen, por su etiqueta.
 * Sin fecha y sin el texto del asiento — eso es lo que se paga.
 */
$teaserMotivo = '';
if ($teaserTotalAlerts > 0) {
    $peor = null;
    $peorPeso = -1;
    foreach ($teaserEvents as $ev) {
        $peso = risk_event_orden($ev);
        if ($peso > $peorPeso) {
            $peorPeso = $peso;
            $peor = $ev;
        }
    }
    if ($peor) {
        $teaserMotivo = risk_event_label($peor);
    }
}

$teaserTrackMeta = esc(json_encode([
    'cif'    => $compCif,
    'score'  => $teaserScore,
    'alerts' => $teaserTotalAlerts,
    'high'   => $teaserHighAlerts,
    'modo'   => $teaserModo,
], JSON_UNESCAPED_UNICODE), 'attr');

if (empty($redirectPath)) {
    $slugVal = $company['slug'] ?? url_title($company['name'] ?? '', '-', true);
    // ver-riesgo=1: al volver de registrarse, el dictamen de ESTA empresa se abre solo.
    // La intención ya es explícita (ha pulsado el CTA del teaser de esta empresa),
    // así que no se le pide un segundo click para desbloquear.
    $redirectPath = 'empresa/' . $compId . '-' . $slugVal . '?ver-riesgo=1';
}
?>
<div style="padding: 28px 20px; position: relative; display: flex; align-items: center; justify-content: center; min-height: 460px; overflow: hidden; background: #f8fafc; margin: -24px; margin-bottom: 0;">

    <!-- FONDO DESENFOCADO: el desglose que se compra con el registro -->
    <!-- `space-between` y no `center`: centrado, todo el contenido falso quedaba
         justo DEBAJO de la tarjeta blanca y no asomaba por ningún lado, así que
         el desenfoque no se leía como "hay algo detrás" sino como una mancha.
         Empujado a los extremos sí asoma a izquierda y derecha, que es lo único
         que hace que esta capa sirva para algo. -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; filter: blur(6px); opacity: 0.5; pointer-events: none; display: flex; flex-wrap: nowrap; gap: 32px; align-items: center; justify-content: space-between; padding: 28px 30px;">
        <!-- Tarjeta del score, en gris SIEMPRE.
             Aunque en modo 'titular' el score se enseñe delante, aquí va neutro:
             el desenfoque no tapa un color, y esta caja no debe ser una segunda
             fuente de verdad que se contradiga con la de delante. -->
        <div style="flex-shrink: 0; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px 20px; text-align: center; width: 190px;">
            <div style="font-size: 0.8rem; font-weight: bold; color: #64748b; margin-bottom: 12px;">NIVEL DE RIESGO</div>
            <div style="width: 80px; height: 75px; background: #94a3b8; border-radius: 12px; margin: 0 auto 12px auto;"></div>
            <div style="height: 14px; width: 70%; background: #cbd5e1; border-radius: 4px; margin: 0 auto;"></div>
        </div>

        <!-- Lista de factores -->
        <div style="flex: 1; min-width: 0; max-width: 520px; display: flex; flex-direction: column; gap: 12px;">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; display: flex; gap: 12px; align-items: center;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #e2e8f0; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-weight: bold;">!</div>
                <div style="flex: 1;">
                    <div style="height: 14px; width: 60%; background: #0f172a; border-radius: 4px; margin-bottom: 6px;"></div>
                    <div style="height: 10px; width: 85%; background: #cbd5e1; border-radius: 3px;"></div>
                </div>
            </div>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; display: flex; gap: 12px; align-items: center;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #e2e8f0; color: #94a3b8; display: flex; align-items: center; justify-content: center; font-weight: bold;">✓</div>
                <div style="flex: 1;">
                    <div style="height: 14px; width: 45%; background: #0f172a; border-radius: 4px; margin-bottom: 6px;"></div>
                    <div style="height: 10px; width: 70%; background: #cbd5e1; border-radius: 3px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Velo sobre el desenfoque: sin él, los trozos de tarjeta gris que asoman
         por los lados parecen manchas. Con el degradado se leen como "hay algo
         detrás", que es justo lo que tienen que decir. -->
    <div style="position: absolute; inset: 0; pointer-events: none; background: radial-gradient(ellipse 62% 58% at 50% 50%, rgba(248, 250, 252, 0) 0%, rgba(248, 250, 252, 0.25) 62%, rgba(248, 250, 252, 0.9) 100%);"></div>

    <!-- TARJETA DE CONVERSIÓN -->
    <div data-track-view="risk_teaser_view" data-track-meta="<?= $teaserTrackMeta ?>" style="position: relative; z-index: 10; display: flex; flex-direction: column; align-items: center; text-align: center; width: 100%; max-width: 540px; background: #ffffff; padding: 32px 26px; border-radius: 20px; box-shadow: 0 24px 48px -12px rgba(15, 23, 42, 0.16), 0 0 0 1px rgba(15, 23, 42, 0.04); border: 1px solid #e2e8f0; box-sizing: border-box;">

        <?php if ($teaserModo === 'titular'): ?>

            <!-- RESULTADO: score + nivel + motivo. Todo lo demás se registra.
                 El bloque vive en su propio parcial porque el paywall del
                 registrado sin cuota tiene que enseñar exactamente lo mismo. -->
            <?= view('partials/company_risk_titular', ['riskProfile' => $riskProfile]) ?>

            <!-- text-wrap: balance reparte las líneas en vez de dejar un colgajo
                 de tres palabras en la segunda. Donde no esté soportado, cae al
                 comportamiento de siempre. -->
            <h3 style="font-size: 1.35rem; font-weight: 900; color: #0f172a; margin: 0 0 8px 0; letter-spacing: -0.5px; line-height: 1.25; max-width: 26ch; text-wrap: balance;">
                <?php if ($teaserTotalAlerts > 0): ?>
                    Ya sabes que hay algo. Falta saber qué es y si sigue abierto
                <?php else: ?>
                    Hoy está limpia. La pregunta es qué pasa a partir de hoy
                <?php endif; ?>
            </h3>
            <p style="color: #475569; margin: 0 0 22px 0; font-size: 0.92rem; line-height: 1.55; max-width: 44ch; text-wrap: pretty;">
                <?php if ($teaserTotalAlerts > 0): ?>
                    Accede al dictamen completo de <strong><?= esc($companyName) ?></strong>:
                    cada acto con su fecha, su gravedad y qué significa para tu riesgo de cobro.
                <?php else: ?>
                    Accede al dictamen completo de <strong><?= esc($companyName) ?></strong>:
                    el histórico registral, los factores que sostienen la puntuación y el aviso si algo cambia.
                <?php endif; ?>
            </p>

        <?php else: ?>

            <!-- MODO OPACO: no se adelanta nada del resultado -->
            <?php if ($teaserHighAlerts > 0): ?>
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 14px; padding: 14px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; text-align: left; width: 100%; box-sizing: border-box;">
                    <div style="background: #fee2e2; color: #ef4444; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; font-weight: 800;">⚠️</div>
                    <div>
                        <div style="font-weight: 800; color: #991b1b; font-size: 0.92rem; line-height: 1.3;">
                            <?= $teaserTotalAlerts ?> <?= $teaserTotalAlerts === 1 ? 'incidencia societaria' : 'incidencias societarias' ?> (<?= $teaserHighAlerts ?> de gravedad alta en BORME)
                        </div>
                        <div style="color: #7f1d1d; font-size: 0.8rem; margin-top: 2px; line-height: 1.35;">
                            Constan actos registrales relevantes que afectan a la estabilidad de <strong><?= esc($companyName) ?></strong><?= company_punto($companyName) ?>
                        </div>
                    </div>
                </div>
            <?php elseif ($teaserTotalAlerts > 0): ?>
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 14px; padding: 14px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; text-align: left; width: 100%; box-sizing: border-box;">
                    <div style="background: #fef3c7; color: #d97706; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; font-weight: 800;">🔍</div>
                    <div>
                        <div style="font-weight: 800; color: #92400e; font-size: 0.92rem; line-height: 1.3;">
                            <?= $teaserTotalAlerts ?> <?= $teaserTotalAlerts === 1 ? 'observación de estabilidad societaria' : 'observaciones de estabilidad societaria' ?>
                        </div>
                        <div style="color: #78350f; font-size: 0.8rem; margin-top: 2px; line-height: 1.35;">
                            Existen indicadores clave en el registro oficial para <strong><?= esc($companyName) ?></strong><?= company_punto($companyName) ?> Regístrate gratis para ver el desglose.
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px; padding: 14px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; text-align: left; width: 100%; box-sizing: border-box;">
                    <div style="background: #dcfce7; color: #16a34a; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; font-weight: 800;">🛡️</div>
                    <div>
                        <div style="font-weight: 800; color: #166534; font-size: 0.92rem; line-height: 1.3;">
                            Dictamen de Solvencia y Scoring BORME disponible
                        </div>
                        <div style="color: #14532d; font-size: 0.8rem; margin-top: 2px; line-height: 1.35;">
                            Evaluación algorítmica procesada en tiempo real. Crea tu cuenta gratis para ver el resultado.
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <h3 style="font-size: 1.45rem; font-weight: 900; color: #0f172a; margin: 0 0 8px 0; letter-spacing: -0.5px;">
                Consulta el nivel de riesgo
            </h3>
            <p style="color: #475569; margin: 0 0 20px 0; font-size: 0.92rem; line-height: 1.5; max-width: 440px;">
                Accede al semáforo de solvencia, alertas BORME e historial de contratación pública creando tu cuenta gratis:
            </p>

        <?php endif; ?>

        <!-- CTAs de registro.
             Las etiquetas se quedaron cortas a propósito: antes decían
             "Ver el dictamen completo con email (3 consultas gratis/mes)", que
             se partía en dos líneas y repetía palabra por palabra lo que ya dice
             la línea de confianza de debajo. Un botón no tiene que explicar las
             condiciones, solo nombrar lo que pasa al pulsarlo. -->
        <div style="display: flex; flex-direction: column; gap: 10px; width: 100%;">
            <a href="<?= site_url('auth/google') ?>?intent=view_risk_profile&cif=<?= urlencode($compCif) ?>&redirect=<?= urlencode($redirectPath) ?>" data-track-click="risk_teaser_cta" data-track-element="google" data-track-meta="<?= $teaserTrackMeta ?>" style="background: #ffffff; color: #1e293b; border: 1.5px solid #cbd5e1; padding: 13px 20px; border-radius: 12px; font-weight: 700; text-decoration: none; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%; box-sizing: border-box; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.03);" onmouseover="this.style.background='#f8fafc'; this.style.borderColor='#94a3b8';" onmouseout="this.style.background='#ffffff'; this.style.borderColor='#cbd5e1';">
                <svg width="18" height="18" viewBox="0 0 24 24"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
                <span style="white-space: nowrap;">Continuar con Google</span>
                <span style="font-size: 0.72rem; font-weight: 700; color: #64748b; background: #f1f5f9; border-radius: 999px; padding: 2px 8px; white-space: nowrap;">1 clic</span>
            </a>

            <a href="<?= site_url('register/quick') ?>?intent=view_risk_profile&cif=<?= urlencode($compCif) ?>&redirect=<?= urlencode($redirectPath) ?>" data-track-click="risk_teaser_cta" data-track-element="email" data-track-meta="<?= $teaserTrackMeta ?>" style="background: #2563eb; color: #fff; padding: 13px 20px; border-radius: 12px; font-weight: 800; text-decoration: none; font-size: 0.95rem; display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; box-sizing: border-box; transition: all 0.2s; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);" onmouseover="this.style.background='#1d4ed8'; this.style.transform='translateY(-1px)';" onmouseout="this.style.background='#2563eb'; this.style.transform='translateY(0)';">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                <span style="white-space: nowrap;"><?= $teaserModo === 'titular' ? 'Ver el dictamen completo' : 'Continuar con email' ?></span>
            </a>
        </div>

        <!-- Las condiciones van aquí, pegadas al botón, y solo una vez: es donde
             se leen justo antes de decidir, y así las etiquetas caben en una línea. -->
        <!-- El candado va INLINE dentro del texto, no como hermano flex: en un
             contenedor estrecho el `flex-wrap` lo mandaba solo a su propia línea,
             con el icono flotando encima de la frase. -->
        <div style="margin-top: 12px; color: #64748b; font-size: 0.78rem; font-weight: 500; line-height: 1.5; text-wrap: pretty;">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 5px;"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>Sin tarjeta &bull; <strong style="color: #334155; font-weight: 700;"><?= $teaserGratis ?> consultas gratis al mes</strong> &bull; <span style="white-space: nowrap;">Acceso instantáneo</span>
        </div>

        <!-- EL PDF SUELTO.
             Estaba aquí abajo, detrás de un "¿prefieres no registrarte?", como
             premio de consolación. Pero el 90 % de este tráfico viene de Google,
             mira UNA empresa y no vuelve: para esa persona el registro no es el
             plan A —no va a gastar tres consultas ni va a volver el mes que
             viene—, y el PDF es literalmente el único producto que le encaja.
             Así que deja de pedir perdón por existir: caja propia, lo que se
             lleva por escrito, y el precio delante.
             Sigue siendo secundario en peso visual: fondo gris, sin azul y sin
             sombra, para no robarle el clic a quien sí iba a registrarse. -->
        <div style="display: flex; align-items: center; gap: 12px; width: 100%; margin: 22px 0 12px 0;">
            <div style="flex: 1; height: 1px; background: #e2e8f0;"></div>
            <span style="font-size: 0.7rem; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.6px; white-space: nowrap;">O sin crear cuenta</span>
            <div style="flex: 1; height: 1px; background: #e2e8f0;"></div>
        </div>

        <div style="width: 100%; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px; box-sizing: border-box; text-align: left;">
            <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 12px; flex-wrap: wrap; margin-bottom: 6px;">
                <span style="font-size: 0.95rem; font-weight: 900; color: #0f172a;">Dictamen en PDF</span>
                <span style="white-space: nowrap;">
                    <span style="font-size: 1.15rem; font-weight: 900; color: #0f172a;"><?= solvencia('precios.pdf', '3,90 €') ?></span>
                    <span style="font-size: 0.72rem; font-weight: 600; color: #94a3b8;">+ IVA</span>
                </span>
            </div>
            <p style="margin: 0 0 12px 0; font-size: 0.8rem; color: #64748b; line-height: 1.45;">
                El histórico registral de <strong style="color: #334155;"><?= esc($companyName) ?></strong> con su puntuación,
                <!-- "con fecha y sello" es de la misma familia que los "dictamen oficial"
                     que quitamos: al lado de "histórico registral", un sello se lee como
                     una certificación del Registro, que es otro producto y de pago. La
                     fecha sí es un hecho comprobable y se queda. -->
                con la fecha de emisión, listo para adjuntar a un expediente. Pago único, sin cuenta y sin suscripción.
            </p>
            <button type="button" onclick="openRiskPdfModal(<?= $compId ?>, '<?= esc($compCif) ?>');" data-track-click="risk_teaser_cta" data-track-element="pdf" data-track-meta="<?= $teaserTrackMeta ?>" style="width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 9px; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 11px; padding: 11px 18px; color: #0f172a; font-size: 0.88rem; font-weight: 800; cursor: pointer; transition: all 0.15s; box-sizing: border-box;" onmouseover="this.style.borderColor='#64748b'; this.style.background='#f1f5f9';" onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#ffffff';">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink: 0; color: #64748b;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                <span style="white-space: nowrap;">Descargar ahora</span>
            </button>
        </div>
    </div>
</div>
