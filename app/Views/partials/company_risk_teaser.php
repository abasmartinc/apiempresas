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
// El motor sigue emitiendo BAJO/MEDIO/ALTO y eso no se toca: hay consultas
// y métricas que dependen de esos valores. Pero lo que se ENSEÑA sale del
// helper, que es donde vive la razón del cambio (ver risk_level_visual).

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

// "Avísame si cambia": mismo destino, pero con vigilar=1 en vez de ver-riesgo=1.
// Al volver, la ficha pone la empresa en vigilancia (no gasta consulta) en lugar
// de abrir el dictamen (que sí la gasta).
$redirectVigilar = strpos($redirectPath, 'ver-riesgo=1') !== false
    ? str_replace('ver-riesgo=1', 'vigilar=1', $redirectPath)
    : $redirectPath . (strpos($redirectPath, '?') !== false ? '&' : '?') . 'vigilar=1';
?>
<div style="padding: 28px 20px; position: relative; display: flex; align-items: center; justify-content: center; min-height: 380px; overflow: hidden; background: #f8fafc; margin: -24px; margin-bottom: 0;">

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
            <div style="font-size: 0.8rem; font-weight: bold; color: #64748b; margin-bottom: 12px;"><?= risk_titulo_indicador() ?></div>
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

    <!-- TARJETA DE CONVERSIÓN — DOS COLUMNAS.
         Antes era una columna de ~430 px dentro de un contenedor de ~940: medía
         unos 700 px de alto y, al bajar desde el chip de la cabecera, el PDF y el
         "Avísame" quedaban por debajo del pliegue en un portátil de 1366×768.
         Ahora:
           - Izquierda: el resultado y el registro (la acción principal, en azul).
           - Derecha: las dos salidas de bajo compromiso, en un panel gris para
             que no le roben peso al registro. El PDF va primero porque es el único
             producto que encaja con quien mira una empresa y no vuelve.
         Por debajo de 780 px se apila en una columna, en el mismo orden.
         Los estilos van en clases `rt-` y no en línea porque el :hover y el
         apilado responsive no se pueden hacer con atributos style. -->
    <style>
        .rt-card{position:relative;z-index:10;width:100%;max-width:900px;background:#fff;border:1px solid #e2e8f0;border-radius:20px;box-shadow:0 24px 48px -12px rgba(15,23,42,.14),0 0 0 1px rgba(15,23,42,.03);display:grid;grid-template-columns:minmax(0,1.12fr) minmax(0,1fr);overflow:hidden;box-sizing:border-box;text-align:left}
        .rt-main{padding:28px 28px 24px;display:flex;flex-direction:column;min-width:0}
        .rt-side{padding:24px;background:#f8fafc;border-left:1px solid #eef2f7;display:flex;flex-direction:column;gap:12px;min-width:0}
        .rt-h{font-size:1.3rem;font-weight:900;color:#0f172a;margin:0 0 8px;letter-spacing:-.4px;line-height:1.25;text-wrap:balance}
        .rt-p{color:#475569;margin:0 0 20px;font-size:.9rem;line-height:1.55;text-wrap:pretty}
        .rt-ctas{display:flex;flex-direction:column;gap:10px;margin-top:auto}
        .rt-btn{display:flex;align-items:center;justify-content:center;gap:10px;width:100%;box-sizing:border-box;padding:12px 18px;border-radius:12px;font-size:.93rem;text-decoration:none;cursor:pointer;transition:background .15s,border-color .15s,transform .15s,box-shadow .15s;white-space:nowrap}
        .rt-btn--google{background:#fff;color:#1e293b;border:1.5px solid #cbd5e1;font-weight:700;box-shadow:0 1px 2px rgba(15,23,42,.04)}
        .rt-btn--google:hover{background:#f8fafc;border-color:#94a3b8}
        .rt-btn--primary{background:#2563eb;color:#fff;border:1.5px solid #2563eb;font-weight:800;box-shadow:0 4px 12px rgba(37,99,235,.22)}
        .rt-btn--primary:hover{background:#1d4ed8;border-color:#1d4ed8;transform:translateY(-1px)}
        .rt-pill{font-size:.7rem;font-weight:700;color:#64748b;background:#f1f5f9;border-radius:999px;padding:2px 8px}
        .rt-trust{margin-top:12px;color:#64748b;font-size:.77rem;font-weight:500;line-height:1.5;text-align:center;text-wrap:pretty}
        .rt-eyebrow{font-size:.68rem;font-weight:800;letter-spacing:.7px;text-transform:uppercase;color:#94a3b8;margin:0 0 2px}
        .rt-opt{background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px;box-sizing:border-box}
        .rt-opt__head{display:flex;align-items:center;gap:10px;margin-bottom:6px}
        .rt-opt__icon{flex-shrink:0;width:32px;height:32px;border-radius:9px;display:flex;align-items:center;justify-content:center}
        .rt-opt__title{font-size:.93rem;font-weight:800;color:#0f172a;line-height:1.2}
        .rt-opt__meta{font-size:.72rem;font-weight:600;color:#64748b;margin-top:1px}
        .rt-opt__price{margin-left:auto;text-align:right;white-space:nowrap;line-height:1.1}
        .rt-opt__text{margin:0 0 12px;font-size:.8rem;color:#64748b;line-height:1.45}
        .rt-btn--sm{padding:10px 14px;font-size:.86rem;font-weight:800;border-radius:10px}
        .rt-btn--ghost{background:#fff;color:#1e293b;border:1.5px solid #cbd5e1}
        .rt-btn--ghost:hover{background:#f8fafc;border-color:#94a3b8}
        .rt-btn--soft-green{background:#dcfce7;color:#14532d;border:1.5px solid #86efac}
        .rt-btn--soft-green:hover{background:#bbf7d0;border-color:#4ade80}
        .rt-link{font-size:.76rem;color:#64748b;text-decoration:none;font-weight:600;display:inline-flex;align-items:center;gap:5px;margin-top:10px}
        .rt-link:hover{color:#2563eb;text-decoration:underline}
        @media (max-width:780px){
            .rt-card{grid-template-columns:1fr;max-width:540px}
            .rt-main{padding:24px 20px 20px}
            .rt-side{padding:20px;border-left:0;border-top:1px solid #eef2f7}
        }
        @media (max-width:480px){
            .rt-main{padding:20px 16px 18px}
            .rt-side{padding:16px}
            .rt-opt{padding:14px}
            .rt-btn{white-space:normal;padding:12px 14px;font-size:.9rem;gap:8px}
            .rt-pill{display:none}
        }
    </style>

    <div class="rt-card" data-track-view="risk_teaser_view" data-track-meta="<?= $teaserTrackMeta ?>">

        <!-- ============ IZQUIERDA: resultado + registro ============ -->
        <div class="rt-main">

        <?php if ($teaserModo === 'titular'): ?>

            <!-- RESULTADO: score + nivel + motivo. Todo lo demás se registra.
                 El bloque vive en su propio parcial porque el paywall del
                 registrado sin cuota tiene que enseñar exactamente lo mismo. -->
            <?= view('partials/company_risk_titular', ['riskProfile' => $riskProfile, 'tiMargen' => '18px']) ?>

            <h3 class="rt-h">
                <?php if ($teaserTotalAlerts > 0): ?>
                    Ya sabes que hay algo. Falta saber qué es y si sigue abierto
                <?php else: ?>
                    Hoy está limpia. La pregunta es qué pasa a partir de hoy
                <?php endif; ?>
            </h3>
            <p class="rt-p">
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
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 14px; padding: 14px 16px; margin-bottom: 18px; display: flex; align-items: center; gap: 12px;">
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
                <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 14px; padding: 14px 16px; margin-bottom: 18px; display: flex; align-items: center; gap: 12px;">
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
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 14px; padding: 14px 16px; margin-bottom: 18px; display: flex; align-items: center; gap: 12px;">
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

            <h3 class="rt-h">Consulta qué consta de ella</h3>
            <p class="rt-p">
                Accede al semáforo de solvencia, alertas BORME e historial de contratación pública creando tu cuenta gratis.
            </p>

        <?php endif; ?>

            <!-- CTAs de registro. Etiquetas cortas a propósito: un botón nombra lo
                 que pasa al pulsarlo; las condiciones van una sola vez, debajo. -->
            <div class="rt-ctas">
                <a class="rt-btn rt-btn--google" href="<?= site_url('auth/google') ?>?intent=view_risk_profile&cif=<?= urlencode($compCif) ?>&redirect=<?= urlencode($redirectPath) ?>" data-track-click="risk_teaser_cta" data-track-element="google" data-track-meta="<?= $teaserTrackMeta ?>">
                    <svg width="18" height="18" viewBox="0 0 24 24" aria-hidden="true"><path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/><path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/><path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.06H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.94l2.85-2.22.81-.63z"/><path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.06l3.66 2.84c.87-2.6 3.3-4.52 6.16-4.52z"/></svg>
                    <span>Continuar con Google</span>
                    <span class="rt-pill">1 clic</span>
                </a>

                <a class="rt-btn rt-btn--primary" href="<?= site_url('register/quick') ?>?intent=view_risk_profile&cif=<?= urlencode($compCif) ?>&redirect=<?= urlencode($redirectPath) ?>" data-track-click="risk_teaser_cta" data-track-element="email" data-track-meta="<?= $teaserTrackMeta ?>">
                    <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.3" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <span><?= $teaserModo === 'titular' ? 'Ver el dictamen completo' : 'Continuar con email' ?></span>
                </a>
            </div>

            <!-- Candado INLINE dentro del texto: como hermano flex, en contenedor
                 estrecho se iba solo a su propia línea. -->
            <div class="rt-trust">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;" aria-hidden="true"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>Sin tarjeta &middot; <strong style="color: #334155; font-weight: 700;"><?= $teaserGratis ?> consultas gratis al mes</strong> &middot; <span style="white-space: nowrap;">Acceso instantáneo</span>
            </div>
        </div>

        <!-- ============ DERECHA: salidas de bajo compromiso ============ -->
        <div class="rt-side">
            <div class="rt-eyebrow">Otras opciones</div>

            <!-- EL PDF SUELTO. El 90 % de este tráfico viene de Google, mira UNA
                 empresa y no vuelve: para esa persona el PDF es el único producto
                 que encaja. Precio delante y caja propia: el precio hace de ancla
                 para el "gratis" de la izquierda. Botón blanco con borde, el de
                 MENOS peso de la tarjeta: jerarquía = registrarse (azul sólido) >
                 vigilar (verde suave) > comprar suelto. Arriba por el ancla, no
                 por importancia. (Iba en negro y le robaba el foco al azul.) -->
            <div class="rt-opt">
                <div class="rt-opt__head">
                    <div class="rt-opt__icon" style="background: #f1f5f9; color: #334155;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="8" y1="13" x2="16" y2="13"/><line x1="8" y1="17" x2="13" y2="17"/></svg>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div class="rt-opt__title">Dictamen en PDF</div>
                        <div class="rt-opt__meta">Sin cuenta · pago único</div>
                    </div>
                    <div class="rt-opt__price">
                        <div style="font-size: 1.15rem; font-weight: 900; color: #0f172a;"><?= solvencia('precios.pdf', '3,90 €') ?></div>
                        <div style="font-size: 0.68rem; font-weight: 600; color: #94a3b8;">+ IVA</div>
                    </div>
                </div>
                <!-- Sin "sello": al lado de "histórico registral" se lee como una
                     certificación del Registro, que es otro producto. La fecha sí. -->
                <p class="rt-opt__text">
                    El histórico registral de <strong style="color: #334155;"><?= esc($companyName) ?></strong> con su puntuación y fecha de emisión, listo para adjuntar a un expediente.
                </p>
                <button type="button" class="rt-btn rt-btn--sm rt-btn--ghost" onclick="openRiskPdfModal(<?= $compId ?>, '<?= esc($compCif) ?>');" data-track-click="risk_teaser_cta" data-track-element="pdf" data-track-meta="<?= $teaserTrackMeta ?>">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                    <span>Descargar ahora</span>
                </button>
                <div style="text-align: center;">
                    <a class="rt-link" href="<?= site_url('ejemplo/informe-riesgo') ?>?t=<?= time() ?>" target="_blank" rel="noopener">
                        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        <span>Ver informe de ejemplo</span>
                    </a>
                </div>
            </div>

            <!-- "AVÍSAME SI CAMBIA". Lo único que trae de vuelta a quien mira una
                 empresa y se va es el correo del día que esa empresa sale en el
                 BORME. No gasta consultas. Registrarse "para que me avisen" crea
                 un vínculo que "para ver el dictamen" no crea, y lleva a Solvencia Pro.
                 Segundo en peso: verde suave, sin llegar al sólido del azul. -->
            <div class="rt-opt">
                <div class="rt-opt__head">
                    <div class="rt-opt__icon" style="background: #f0fdf4; color: #15803d;">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/></svg>
                    </div>
                    <div style="flex: 1; min-width: 0;">
                        <div class="rt-opt__title">Avísame si cambia</div>
                        <div class="rt-opt__meta">Gratis · no gasta consultas</div>
                    </div>
                </div>
                <p class="rt-opt__text">
                    Te escribimos el día que el BORME publique algo de <strong style="color: #334155;"><?= esc($companyName) ?></strong>.
                </p>
                <a class="rt-btn rt-btn--sm rt-btn--soft-green" href="<?= site_url('register/quick') ?>?intent=view_risk_profile&cif=<?= urlencode($compCif) ?>&redirect=<?= urlencode($redirectVigilar) ?>" data-track-click="risk_teaser_cta" data-track-element="watch" data-track-meta="<?= $teaserTrackMeta ?>">
                    <span>Activar aviso gratis</span>
                </a>
            </div>
        </div>
    </div>
</div>
