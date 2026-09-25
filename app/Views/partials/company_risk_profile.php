<?php
helper(['risk_labels', 'company']);

$score = (int) ($riskProfile['risk_score'] ?? 50);
// Los cortes salían de aquí a mano con 70, pero el motor etiqueta ALTO a partir
// de 60: una empresa de 65 llevaba el texto "ALTO" pintado de naranja "MEDIO".
[$label, , , ] = risk_level_visual($score);
$color = $score < (int) solvencia('umbralMedio', 30)
    ? '#22c55e'
    : ($score < (int) solvencia('umbralAlto', 60) ? '#f59e0b' : '#ef4444');
$riskLevelText = $label;  // El motor sigue emitiendo BAJO/MEDIO/ALTO y eso no se toca: hay consultas
?>
<div style="display: flex; flex-wrap: wrap; gap: 32px; align-items: stretch;">
    
    <!-- LEFT COLUMN (Score) -->
    <div style="width: 280px; background: #f8fafc; border-radius: 16px; border: 1px solid #f1f5f9; padding: 22px 20px; display: flex; flex-direction: column; align-items: center; justify-content: flex-start; flex-shrink: 0; box-sizing: border-box;">
        <div style="width: 100%; display: flex; flex-direction: column; align-items: center; gap: 6px; margin-bottom: 20px;">
            <h4 style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 1px; color: #64748b; margin: 0; font-weight: 800; text-align: center;"><?= risk_titulo_indicador() ?></h4>

            <?php
            // La píldora de consultas/plan vivía aquí, dentro de la tarjeta del
            // NIVEL DE RIESGO: es un dato de TU CUENTA metido dentro del indicador
            // de la EMPRESA. Se ha movido a la cabecera del bloque, junto al chip
            // de vigilancia, que es donde está el resto de tu estado.
            ?>

            <?php
            // Botón de vigilancia, solo donde se pide.
            // En la ficha de empresa NO se pinta aquí: esa página va cacheada y el
            // estado se resolvería al primero que la cargase. Allí vive en la
            // cabecera y lo enciende la hidratación. Aquí (buscador de riesgo, sin
            // caché) el estado del servidor sí es el del usuario.
            $watchCif = (string) ($company['cif'] ?? '');
            $watching = !empty($isWatching);
            ?>
            <?php if (!empty($mostrarVigilancia) && $watchCif !== ''): ?>
                <button type="button"
                        data-risk-watch
                        data-cif="<?= esc($watchCif, 'attr') ?>"
                        data-watching="<?= $watching ? '1' : '0' ?>"
                        title="Te avisamos por correo cuando aparezca un acto nuevo de esta empresa en el BORME"
                        style="margin-top: 4px; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; border-radius: 999px; padding: 5px 12px; font-size: 0.72rem; font-weight: 800; transition: all 0.15s;
                               background: <?= $watching ? '#ecfdf5' : '#ffffff' ?>; border: 1px solid <?= $watching ? '#a7f3d0' : '#cbd5e1' ?>; color: <?= $watching ? '#047857' : '#475569' ?>;">
                    <span data-watch-icon><?= $watching ? '🔔' : '🔕' ?></span>
                    <span data-watch-label><?= $watching ? 'Vigilando' : 'Vigilar empresa' ?></span>
                </button>
            <?php endif; ?>
        </div>
        
        <!-- Circle -->
        <div style="width: 160px; height: 160px; border-radius: 50%; border: 12px solid <?= $color ?>; display: flex; flex-direction: column; align-items: center; justify-content: center; margin-bottom: 24px; background: #fff; box-shadow: 0 10px 20px rgba(0,0,0,0.05);">
            <span style="font-size: 4rem; font-weight: 900; color: #0f172a; line-height: 1; letter-spacing: -1px;"><?= $score ?></span>
            <span style="font-size: 0.9rem; font-weight: 600; color: #94a3b8; margin-top: 2px;">de 100</span>
        </div>
        
        <!-- Debajo del rótulo de arriba ponía otra vez "Nivel de riesgo": cuatro
             elementos para decir dos cosas. -->
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="font-size: 1.5rem; font-weight: 800; color: <?= $color ?>; text-transform: uppercase; letter-spacing: 1px; line-height: 1.2;"><?= $riskLevelText ?></div>
        </div>

        <?php
        /*
         * El icono de esta caja era FIJO: una flecha verde hacia arriba sobre
         * fondo verde menta, el símbolo universal de "va bien", acompañando a
         * textos como "Advertencia: constan observaciones de riesgo moderado".
         * Ahora sigue al nivel, igual que el resto del bloque.
         */
        // Por el SCORE y no por el texto de la etiqueta: cuando las etiquetas
        // cambiaron de ALTO/MEDIO a GRAVE/A REVISAR, un strpos('ALTO') dejaba de
        // encontrar nada y todo se pintaba de verde sin que nadie se enterara.
        if ($score >= (int) solvencia('umbralAlto', 60)) {
            $notaFondo = '#fef2f2'; $notaTinta = '#dc2626';
            // Triángulo de aviso.
            $notaIcono = '<path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line>';
        } elseif ($score >= (int) solvencia('umbralMedio', 30)) {
            $notaFondo = '#fffbeb'; $notaTinta = '#d97706';
            // Círculo de información.
            $notaIcono = '<circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line>';
        } else {
            $notaFondo = '#ecfdf5'; $notaTinta = '#10b981';
            // Escudo con visto: aquí el verde sí corresponde.
            $notaIcono = '<path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><polyline points="9 12 11 14 15 10"></polyline>';
        }
        ?>
        <div style="background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 16px; display: flex; gap: 12px; align-items: center; width: 100%; box-sizing: border-box;">
            <div style="background: <?= $notaFondo ?>; color: <?= $notaTinta ?>; padding: 8px; border-radius: 8px; flex-shrink: 0; display: flex;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><?= $notaIcono ?></svg>
            </div>
            <p style="margin: 0; font-size: 0.75rem; color: #475569; line-height: 1.4; font-weight: 500;">
                <?= esc($riskProfile['data']['summary_message'] ?? 'Puntuación procesada correctamente.') ?>
            </p>
        </div>

        <?php
        /* ------------------------------------------------------------------
           EVOLUCIÓN DEL SCORE
           Un 45 estable no es lo mismo que un 45 que hace medio año era 20.
           Sale del histórico que el motor archiva en cada recálculo, así que
           no depende de quién mire: es cacheable sin problema.
           Si no hay con qué comparar, no se pinta nada.
        ------------------------------------------------------------------ */
        $trend = $riskTrend ?? null;
        ?>
        <?php if (!empty($trend) && !empty($trend['puntos'])): ?>
            <?php
            $tDelta = (int) $trend['delta'];
            // Subir el score es EMPEORAR: rojo arriba, verde abajo.
            if ($tDelta > 0)      { $tColor = '#b91c1c'; $tFlecha = '▲'; $tTexto = 'ha empeorado'; }
            elseif ($tDelta < 0)  { $tColor = '#15803d'; $tFlecha = '▼'; $tTexto = 'ha mejorado'; }
            else                  { $tColor = '#64748b'; $tFlecha = '='; $tTexto = 'sin cambios'; }

            // Mini-gráfica. Escala vertical con margen, para que una serie plana
            // no quede pegada al borde ni una variación pequeña parezca un salto.
            $pts   = array_map('intval', $trend['puntos']);
            $minY  = max(0, min($pts) - 8);
            $maxY  = min(100, max($pts) + 8);
            if ($maxY - $minY < 12) { $maxY = min(100, $minY + 12); }
            $anchoSvg = 236; $altoSvg = 34;
            $n = count($pts);
            $coords = [];
            foreach ($pts as $i => $v) {
                $x = $n > 1 ? ($i / ($n - 1)) * $anchoSvg : $anchoSvg / 2;
                $y = $altoSvg - (($v - $minY) / max(1, $maxY - $minY)) * $altoSvg;
                $coords[] = round($x, 1) . ',' . round($y, 1);
            }
            $linea = implode(' ', $coords);
            $ultimo = end($coords);
            [$ux, $uy] = array_map('floatval', explode(',', $ultimo));
            ?>
            <div style="width: 100%; margin-top: 16px; padding-top: 14px; border-top: 1px dashed #e2e8f0;">
                <div style="font-size: 0.64rem; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.6px; text-align: center; margin-bottom: 8px;">
                    Evolución
                </div>

                <svg viewBox="0 -4 <?= $anchoSvg ?> <?= $altoSvg + 8 ?>" width="100%" height="40" preserveAspectRatio="none" aria-hidden="true" style="display: block; overflow: visible;">
                    <polyline points="<?= $linea ?>" fill="none" stroke="<?= $tColor ?>" stroke-width="2" stroke-linejoin="round" stroke-linecap="round" vector-effect="non-scaling-stroke"></polyline>
                    <circle cx="<?= $ux ?>" cy="<?= $uy ?>" r="3" fill="<?= $tColor ?>"></circle>
                </svg>

                <div style="text-align: center; font-size: 0.78rem; color: #475569; line-height: 1.45; margin-top: 8px;">
                    <?= esc(ucfirst((string) $trend['periodo'])) ?> era <strong style="color: #334155;"><?= (int) $trend['antes'] ?></strong>
                </div>
                <div style="text-align: center; font-size: 0.76rem; font-weight: 800; color: <?= $tColor ?>; margin-top: 2px;">
                    <?= $tFlecha ?> <?= $tDelta === 0 ? esc($tTexto) : (abs($tDelta) . ' ' . (abs($tDelta) === 1 ? 'punto' : 'puntos') . ' — ' . esc($tTexto)) ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- LA PETICIÓN DE VIGILANCIA
             Aquí abajo, pegada al veredicto y llenando el hueco que dejaba la
             columna. Es el sitio con mejor relación intención/esfuerzo: llegas
             después de leer el score y el resumen, sin tener que bajar hasta el
             final de la tarjeta.
             Nace OCULTA: esta ficha va cacheada, así que el estado no puede
             salir del servidor. La enciende la hidratación, y solo si el usuario
             todavía NO vigila la empresa. -->
        <?php $watchCifCol = (string) ($company['cif'] ?? ''); ?>
        <?php if ($watchCifCol !== ''): ?>
            <div data-risk-prompt style="display: none; width: 100%; margin-top: 16px; padding-top: 16px; border-top: 1px dashed #e2e8f0; flex-direction: column; align-items: center; gap: 8px; text-align: center;">
                <!-- Cada frase en su línea. Antes se partía por donde caía
                     ("...¿Y si algo / cambia mañana?") y la pregunta, que es el
                     gancho, quedaba descoyuntada. El nowrap solo protege a la
                     pregunta, que cabe de sobra en los 240 px útiles. -->
                <div data-risk-prompt-nota style="font-size: 0.78rem; color: #64748b; line-height: 1.5;">
                    Este dictamen es de hoy.<br>
                    <strong style="color: #334155; font-weight: 700; white-space: nowrap;">¿Y si algo cambia mañana?</strong>
                </div>
                <button type="button"
                        data-risk-watch
                        data-cif="<?= esc($watchCifCol, 'attr') ?>"
                        data-watching="0"
                        style="width: 100%; display: inline-flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; border-radius: 10px; padding: 10px 14px; font-size: 0.83rem; font-weight: 800; transition: all 0.15s; background: #0f172a; border: 1px solid #0f172a; color: #ffffff;"
                        onmouseover="this.style.background='#1e293b';"
                        onmouseout="this.style.background='#0f172a';">
                    <span data-watch-icon>🔔</span>
                    <span data-watch-label>Vigilar empresa</span>
                </button>
            </div>
        <?php endif; ?>

        <!-- LA CARTERA, JUSTO DESPUÉS DEL PRIMER DICTAMEN
             Quien acaba de revisar un cliente suele tener más. Subir la lista que
             exporta su programa de facturación y vigilarla de golpe es lo que más
             engancha con la vigilancia, y no aparecía en la ficha. Va siempre visible:
             la ficha está cacheada y el enlace no depende del usuario. -->
        <div style="width: 100%; margin-top: 12px; text-align: center; font-size: 0.78rem; color: #64748b; line-height: 1.5;">
            ¿Tienes más clientes o proveedores?
            <a href="<?= site_url('cartera?source=ficha_dictamen') ?>"
               onclick="if (window.trackEvent) window.trackEvent('risk_cartera_cta', { from: 'dictamen' });"
               style="color: #2563eb; font-weight: 800; text-decoration: none; white-space: nowrap;">Sube tu lista y vigílalos de golpe →</a>
        </div>
    </div>

    <!-- RIGHT COLUMN (Factors) -->
    <div style="flex: 1; min-width: 300px; display: flex; flex-direction: column;">
        
        <!-- DESGLOSE DEL SCORING
             Aquí había una caja que PROMETÍA seis dimensiones y no enseñaba
             ninguna. Las seis ya venían calculadas en el JSON del motor; ahora
             se pintan con su valor, su tope y cuál es la dominante. -->
        <?= view('partials/company_risk_dimensions', [
            'riskProfile' => $riskProfile,
            'score'       => $score,
            // Para el aviso de confianza baja: sin la fecha real de constitución
            // no se puede saber si la culpa es del dato o de la juventud de la
            // empresa, y el aviso acababa diciendo las dos cosas a la vez.
            'company'     => $company ?? [],
        ]) ?>

        <?php
        /*
         * QUÉ SE HA COMPROBADO.
         *
         * Va DESPUÉS del desglose y no antes: quien tiene incidencias las lee
         * arriba, y a quien no tiene ninguna —ocho de cada diez— le cierra la
         * ficha con un trabajo hecho en vez de con un hueco.
         */
        ?>
        <?= view('partials/company_risk_comprobaciones', ['riskProfile' => $riskProfile]) ?>

        <?php
        /*
         * ACTOS QUE NO RECOGE NINGUNA COMPROBACIÓN.
         *
         * Aquí había un listado de TODOS los eventos del motor, y desde que
         * existe "Qué se ha comprobado" decía las cosas dos veces con las
         * mismas palabras: la ficha de una empresa con las cuentas sin
         * depositar repetía la misma frase —misma descripción, mismo año— en
         * dos cajas separadas por diez centímetros, y una tercera vez en el
         * desglose del scoring.
         *
         * Borrarlo entero era lo tentador y habría sido un error: las nueve
         * comprobaciones no cubren todos los códigos que emite el motor
         * (CAMBIO_OBJETO_SOCIAL, CAMBIO_ADMINISTRADOR, OTROS_INFORMATIVO...),
         * así que una empresa cuya única incidencia fuese de ésas habría
         * enseñado nueve vistos verdes mientras el titular decía "1 incidencia
         * registrada".
         *
         * Se filtra, no se borra: lo que ya cuentan las comprobaciones sale de
         * aquí, y lo que no cabía en ninguna línea sigue saliendo. Y va DESPUÉS
         * del bloque de comprobaciones, porque es el resto, no la cabecera.
         */
        $eventosSueltos = risk_eventos_sueltos($riskProfile);
        ?>

        <?php /* El contenedor va dentro del if: si no hay nada suelto no debe
                 quedar un div vacío empujando 24px al final de la columna. */ ?>
        <?php if (!empty($eventosSueltos)): ?>
            <div style="display: flex; flex-direction: column; gap: 16px; margin-top: 22px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <div style="background: #eff6ff; color: #3b82f6; padding: 6px; border-radius: 50%;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                    </div>
                    <h4 style="font-size: 1rem; font-weight: 800; color: #0f172a; margin: 0; text-transform: uppercase;">Otros actos registrales</h4>
                </div>
                <?php foreach ($eventosSueltos as $flag): ?>
                    <?php
                    // Normalizado: `critical` caia al bloque de 'low' y se pintaba en
                    // gris una sociedad extinguida.
                    $sevN = risk_event_severidad($flag);
                    $sev  = $sevN >= 3 ? 'high' : ($sevN === 2 ? 'medium' : 'low');
                    if ($sev === 'high') {
                        $iconColor = '#ef4444';
                        $iconBg = '#fee2e2';
                        $badgeColor = '#b91c1c';
                        $badgeBg = '#fef2f2';
                        $badgeBorder = '#fecaca';
                        $sevLabel = 'ALERTA';
                    } elseif ($sev === 'medium') {
                        $iconColor = '#f59e0b';
                        $iconBg = '#fef3c7';
                        $badgeColor = '#b45309';
                        $badgeBg = '#fffbeb';
                        $badgeBorder = '#fde68a';
                        $sevLabel = 'ATENCIÓN';
                    } else {
                        /*
                         * LEVE, no "POSITIVO".
                         *
                         * Lo que se recorre aquí son los eventos que el motor ha
                         * encontrado EN CONTRA de la empresa. Una incidencia de
                         * gravedad baja sigue siendo una incidencia: pintarla en
                         * verde con un tick y la palabra POSITIVO convierte un hecho
                         * desfavorable en un punto a favor.
                         *
                         * Se veía en crudo con "Cuentas anuales sin depositar desde
                         * hace 3 ejercicios ✓ POSITIVO" — y dos dedos más abajo, la
                         * misma dimensión en rojo como FACTOR DOMINANTE. La misma
                         * ficha decía las dos cosas.
                         *
                         * El caso bueno de verdad lo cuenta ahora el bloque "Qué se
                         * ha comprobado", que es donde cabe decir que no ha saltado
                         * nada. Aquí solo quedan actos, y un acto leve es leve.
                         */
                        $iconColor = '#64748b';
                        $iconBg = '#f1f5f9';
                        $badgeColor = '#475569';
                        $badgeBg = '#f8fafc';
                        $badgeBorder = '#e2e8f0';
                        $sevLabel = 'LEVE';
                    }
                    ?>
                    <div style="border: 1px solid #f1f5f9; border-radius: 12px; padding: 20px; display: flex; gap: 16px; align-items: center; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: <?= $iconBg ?>; color: <?= $iconColor ?>; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                            <?php if ($sev === 'high'): ?>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path><line x1="12" y1="9" x2="12" y2="13"></line><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            <?php elseif ($sev === 'medium'): ?>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/><path d="M3 3v5h5"/><path d="M12 7v5l4 2"/></svg>
                            <?php else: ?>
                                <?php /* Icono de información, no un tick: el tick dice "esto
                                         está bien" sobre un hecho que está en la lista de
                                         cosas que NO lo están. */ ?>
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><line x1="12" y1="11" x2="12" y2="16"></line><line x1="12" y1="7.5" x2="12.01" y2="7.5"></line></svg>
                            <?php endif; ?>
                        </div>
                        <div style="flex: 1;">
                            <p style="margin: 0 0 6px 0; font-size: 1rem; font-weight: 700; color: #0f172a;"><?= esc(risk_event_label($flag)) ?></p>
                            <p style="margin: 0; font-size: 0.9rem; color: #64748b; line-height: 1.4;"><?= esc($flag['description'] ?? 'Basado en histórico público') ?></p>
                        </div>
                        <span style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; border: 1px solid <?= $badgeBorder ?>; padding: 6px 14px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; letter-spacing: 0.5px; text-transform: uppercase; flex-shrink: 0;">
                            <?= $sevLabel ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<!-- UPSELL SOLVENCIA PRO: pegado al dictamen, que es donde está el pico de intención.
     No se renderiza para suscriptores (el propio partial hace el guard). -->
<?= view('partials/risk_pro_upsell', [
    'riskProfile' => $riskProfile,
    'company'     => $company,
    'riskQuota'   => $riskQuota ?? []
]) ?>
<?php
/* ---------------------------------------------------------------------------
   ACCIONES SECUNDARIAS
   El dictamen en PDF y la invitación a auditar otra empresa van como barras
   finas: por encima manda el CTA de Solvencia Pro. Antes el PDF era un banner
   oscuro a todo ancho que pesaba más que la suscripción — y para una empresa ya
   desbloqueada ni siquiera cuesta dinero.
   La cuota restante ya no vive aquí: se ha absorbido en la tarjeta de Pro como
   argumento de escasez, para no decirle "aún te queda gratis" justo después
   de pedirle que pague.
--------------------------------------------------------------------------- */
$compBtnId   = !empty($company['id']) ? (int)$company['id'] : 0;
$compNameStr = company_short_name(company_display_name($company['name'] ?? '', 'esta empresa'));

$isSubscriber   = !empty($riskQuota['is_subscriber']);
$viewsUsed      = (int)($riskQuota['views_used'] ?? 1);
$viewsRemaining = max(0, (int) solvencia('consultasGratis', 3) - $viewsUsed);

$canDownloadRiskPdf = $isSubscriber
                   || !empty($riskQuota['already_unlocked'])
                   || !empty($riskQuota['allowed']);
?>

<!-- BARRA: DICTAMEN EN PDF -->
<div style="margin-top: 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 14px 18px; display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: 12px;">
    <div style="display: flex; align-items: center; gap: 12px; min-width: 240px; flex: 1;">
        <div style="color: #475569; flex-shrink: 0; display: flex;">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg>
        </div>
        <div>
            <div style="font-size: 0.92rem; font-weight: 800; color: #0f172a; line-height: 1.3;">
                Dictamen de <?= esc($compNameStr) ?> en PDF
            </div>
            <div style="font-size: 0.78rem; color: #64748b; margin-top: 1px;">
                <?php if ($isSubscriber): ?>
                    Incluido en tu plan Solvencia Pro &bull; listo para tu expediente de cliente
                <?php elseif ($canDownloadRiskPdf): ?>
                    Ya incluido con esta consulta &bull; listo para tu expediente de cliente
                <?php else: ?>
                    <?php // "Certificación de solvencia" no la emitimos nosotros. ?>
                    Puntuación de solvencia, semáforo y detalle de eventos BORME
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($canDownloadRiskPdf): ?>
        <a href="<?= site_url('empresa/export-risk/' . ($compBtnId > 0 ? $compBtnId : esc($company['cif'] ?? ''))) ?>" hx-boost="false" style="flex-shrink: 0; display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; padding: 9px 16px; border-radius: 9px; font-weight: 800; font-size: 0.85rem; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.borderColor='#94a3b8'; this.style.background='#f1f5f9';" onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#ffffff';">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            Descargar PDF
        </a>
    <?php else: ?>
        <button type="button" onclick="openRiskPdfModal(<?= $compBtnId ?>, '<?= esc($company['cif'] ?? '') ?>');" style="flex-shrink: 0; display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: #0f172a; border: 1.5px solid #cbd5e1; padding: 9px 16px; border-radius: 9px; font-weight: 800; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.borderColor='#94a3b8'; this.style.background='#f1f5f9';" onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#ffffff';">
            <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
            <?php // Lo que se compra es el acceso a la empresa, no el fichero: el
                  // fichero es el mismo que se lleva gratis quien ya la consultó. ?>
            Desbloquear por <?= solvencia('precios.pdf', '3,90 €') ?> + IVA
        </button>
    <?php endif; ?>
</div>

<?php if (!$isSubscriber && $viewsRemaining > 0): ?>
    <!-- BARRA: AUDITAR OTRA EMPRESA (activación, sin competir con el CTA de Pro) -->
    <div style="margin-top: 10px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 18px; display: flex; flex-wrap: wrap; align-items: center; gap: 12px;">
        <span style="font-size: 0.85rem; font-weight: 700; color: #334155; white-space: nowrap;">
            Auditar otra empresa
        </span>
        <form onsubmit="handleActivationSearch(event, this);" style="display: flex; gap: 8px; margin: 0; flex: 1; min-width: 260px;">
            <input
                type="text"
                name="cif"
                placeholder="CIF o nombre (ej. B85402030, Mercadona...)"
                style="flex: 1; min-width: 0; padding: 9px 12px; border: 1px solid #cbd5e1; border-radius: 9px; font-size: 0.85rem; font-weight: 500; outline: none; background: #f8fafc;"
                required
            >
            <button type="submit" style="background: #ffffff; color: #1d4ed8; border: 1.5px solid #bfdbfe; padding: 9px 16px; border-radius: 9px; font-weight: 800; font-size: 0.85rem; cursor: pointer; white-space: nowrap; transition: all 0.2s;" onmouseover="this.style.background='#eff6ff';" onmouseout="this.style.background='#ffffff';">
                Consultar
            </button>
        </form>
    </div>

    <script>
    if (typeof handleActivationSearch !== 'function') {
        window.handleActivationSearch = function(e, form) {
            if (e && e.preventDefault) e.preventDefault();
            const input = form ? form.querySelector('input[name="cif"]') : null;
            const val = input ? input.value.trim() : '';
            if (!val) return false;

            if (typeof window.lookupCifInDashboard === 'function') {
                window.lookupCifInDashboard(val);
            } else {
                window.location.href = '<?= site_url("dashboard?view=risk&cif=") ?>' + encodeURIComponent(val);
            }
            return false;
        };
    }
    </script>
<?php endif; ?>
