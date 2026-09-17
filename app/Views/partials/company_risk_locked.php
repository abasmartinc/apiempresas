<?php
/**
 * partials/company_risk_locked.php
 * Estado BLOQUEADO del Perfil de Riesgo para usuarios AUTENTICADOS que aún no han
 * desbloqueado esta empresa pero disponen de cuota gratuita o de créditos comprados.
 *
 * La consulta NO se consume al renderizar este bloque: solo al pulsar el botón,
 * que llama a POST /api/empresa/desbloquear-riesgo.
 *
 * Variables:
 * - $riskProfile (array)
 * - $company (array)
 * - $riskQuota (array)  ← salida de CompanyRiskService::getQuotaStatus()
 */
// Los helpers, ANTES de usarlos: risk_event_es_grave() se llama aquí debajo y la
// carga estaba después, así que la vista reventaba con "undefined function".
helper(['company', 'risk_labels']);

$lockedEvents = $riskProfile['data']['canonical_events'] ?? [];
$lockedTotalAlerts = count($lockedEvents);
$lockedHighAlerts = 0;
foreach ($lockedEvents as $ev) {
    if (risk_event_es_grave($ev)) $lockedHighAlerts++;   // incluye `critical`
}

$lockedName    = company_short_name(company_display_name($company['name'] ?? '', 'esta empresa'));
$lockedCif     = (string)($company['cif'] ?? '');
$lockedCompId  = (int)($company['id'] ?? 0);

$lockedTrackMeta = esc(json_encode([
    'cif'         => $lockedCif,
    'alerts'      => $lockedTotalAlerts,
    'high'        => $lockedHighAlerts,
    'unlock_cost' => $riskQuota['unlock_cost'] ?? 'free',
], JSON_UNESCAPED_UNICODE), 'attr');

$unlockCost    = $riskQuota['unlock_cost'] ?? 'free';
$viewsUsed     = (int)($riskQuota['views_used'] ?? 0);
$viewsLimit    = (int)($riskQuota['views_limit'] ?? 3);
$viewsLeft     = max(0, (int)($riskQuota['views_remaining'] ?? ($viewsLimit - $viewsUsed)));
$creditsLeft   = (int)($riskQuota['risk_credits'] ?? 0);

if ($unlockCost === 'credit') {
    $costLabel = 'Usarás 1 de tus ' . $creditsLeft . ' ' . ($creditsLeft === 1 ? 'crédito' : 'créditos') . ' del pack';
} else {
    $costLabel = 'Usarás 1 de tus ' . $viewsLeft . ' ' . ($viewsLeft === 1 ? 'consulta gratuita' : 'consultas gratuitas') . ' de este mes';
}
?>
<div style="padding: 20px; position: relative; display: flex; align-items: center; justify-content: center; min-height: 420px; overflow: hidden; background: #fafafa; margin: -24px; margin-bottom: 0;">

    <!-- FONDO DESENFOCADO -->
    <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; filter: blur(7px); opacity: 0.4; pointer-events: none; display: flex; flex-wrap: nowrap; gap: 80px; align-items: center; justify-content: center; padding: 24px;">
        <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px 20px; text-align: center; width: 190px;">
            <div style="font-size: 0.8rem; font-weight: bold; color: #64748b; margin-bottom: 12px;"><?= risk_titulo_indicador() ?></div>
            <div style="width: 80px; height: 75px; background: #94a3b8; border-radius: 12px; margin: 0 auto 12px auto;"></div>
            <div style="height: 14px; width: 70%; background: #cbd5e1; border-radius: 4px; margin: 0 auto;"></div>
        </div>
        <div style="flex: 1; max-width: 480px; display: flex; flex-direction: column; gap: 12px;">
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; display: flex; gap: 12px; align-items: center;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #fee2e2;"></div>
                <div style="flex: 1;">
                    <div style="height: 14px; width: 60%; background: #0f172a; border-radius: 4px; margin-bottom: 6px;"></div>
                    <div style="height: 10px; width: 85%; background: #cbd5e1; border-radius: 3px;"></div>
                </div>
            </div>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; display: flex; gap: 12px; align-items: center;">
                <div style="width: 32px; height: 32px; border-radius: 50%; background: #dcfce7;"></div>
                <div style="flex: 1;">
                    <div style="height: 14px; width: 45%; background: #0f172a; border-radius: 4px; margin-bottom: 6px;"></div>
                    <div style="height: 10px; width: 70%; background: #cbd5e1; border-radius: 3px;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- TARJETA DE DESBLOQUEO -->
    <div data-track-view="risk_locked_view" data-track-meta="<?= $lockedTrackMeta ?>" style="position: relative; z-index: 10; display: flex; flex-direction: column; align-items: center; text-align: center; width: 100%; max-width: 560px; background: #ffffff; padding: 32px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1); border: 1px solid #e2e8f0;">

        <?php if (!empty($riskProfile)): ?>
            <!-- El MISMO titular que ve un visitante anónimo en el teaser.
                 Sin esto la escalera del producto quedaba al revés otra vez: quien
                 llega de Google ve la puntuación, el nivel y el motivo principal, y
                 el que se ha registrado —que está un paso MÁS adentro— se encontraba
                 un recuento de incidencias sin una sola cifra. Es el mismo fallo que
                 ya se corrigió en el paywall, en la única pantalla del flujo que no
                 se había revisado.
                 Lo que cuesta la consulta sigue siendo el desglose: cada acto con su
                 fecha, su gravedad y de dónde sale la puntuación. -->
            <?= view('partials/company_risk_titular', ['riskProfile' => $riskProfile, 'tiMargen' => '18px']) ?>
        <?php endif; ?>

        <!-- GANCHO DINÁMICO BORME -->
        <?php if ($lockedHighAlerts > 0): ?>
            <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 14px; padding: 14px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; text-align: left; width: 100%; box-sizing: border-box;">
                <div style="background: #fee2e2; color: #ef4444; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; font-weight: 800;">⚠️</div>
                <?php
                /*
                 * UNA SOLA LÍNEA, y solo con lo que el titular de arriba no dice.
                 *
                 * Con el titular delante ("2 incidencias registradas. La principal: ...")
                 * esta caja repetía el recuento y añadía una frase de relleno —"constan
                 * actos registrales relevantes que afectan a la estabilidad de X"— que no
                 * aportaba ningún dato. Lo único nuevo es la GRAVEDAD, así que es lo único
                 * que se queda; el color rojo ya pone el tono.
                 */
                if ($lockedHighAlerts === $lockedTotalAlerts) {
                    $lockedAviso = $lockedTotalAlerts === 1
                        ? 'La incidencia es de gravedad alta'
                        : 'Las ' . $lockedTotalAlerts . ' son de gravedad alta';
                } else {
                    $lockedAviso = $lockedHighAlerts === 1
                        ? 'Una de ellas es de gravedad alta'
                        : $lockedHighAlerts . ' de ellas son de gravedad alta';
                }
                ?>
                <div style="font-weight: 800; color: #991b1b; font-size: 0.92rem; line-height: 1.3;">
                    <?= esc($lockedAviso) ?>
                </div>
            </div>
        <?php elseif ($lockedTotalAlerts > 0): ?>
            <div style="background: #fffbeb; border: 1px solid #fde68a; border-radius: 14px; padding: 14px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px; text-align: left; width: 100%; box-sizing: border-box;">
                <div style="background: #fef3c7; color: #d97706; width: 38px; height: 38px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0; font-weight: 800;">🔍</div>
                <?php /* Igual que la roja: el titular ya ha dado el recuento y el motivo
                         principal, así que aquí solo cabe lo que añade — que ninguna llega
                         a gravedad alta, que es tranquilizador y conviene decirlo. */ ?>
                <div style="font-weight: 800; color: #92400e; font-size: 0.92rem; line-height: 1.3;">
                    Ninguna llega a gravedad alta
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
                        Evaluación algorítmica de <strong><?= esc($lockedName) ?></strong> procesada y lista para consultar.
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <h3 style="font-size: 1.4rem; font-weight: 900; color: #0f172a; margin: 0 0 8px 0; letter-spacing: -0.5px;">
            <?php /* Decía "Ver el dictamen completo" justo encima de un botón que pone
                     "Ver dictamen de riesgo": el titular gastaba su sitio repitiendo la
                     acción en vez de decir qué se llevan. */ ?>
            Ya sabes cuánto. Falta saber por qué
        </h3>
        <p style="color: #475569; margin: 0 0 20px 0; font-size: 0.92rem; line-height: 1.5; max-width: 440px;">
            El dictamen abre cada acto con su fecha y su gravedad, el peso de cada factor en la
            puntuación y el historial de contratación pública.
        </p>

        <button type="button"
                data-risk-unlock
                data-track-click="risk_unlock_click"
                data-track-element="<?= esc($unlockCost, 'attr') ?>"
                data-track-meta="<?= $lockedTrackMeta ?>"
                data-cif="<?= esc($lockedCif, 'attr') ?>"
                data-company-id="<?= $lockedCompId ?>"
                style="background: #2563eb; color: #fff; border: none; padding: 14px 28px; border-radius: 12px; font-weight: 800; font-size: 1rem; cursor: pointer; width: 100%; max-width: 380px; box-sizing: border-box; box-shadow: 0 6px 18px rgba(37,99,235,0.3); transition: all 0.2s;"
                onmouseover="this.style.background='#1d4ed8'; this.style.transform='translateY(-1px)';"
                onmouseout="this.style.background='#2563eb'; this.style.transform='translateY(0)';">
            Ver dictamen de riesgo →
        </button>

        <div style="margin-top: 12px; color: #64748b; font-size: 0.8rem; font-weight: 600;">
            <?= esc($costLabel) ?>
        </div>

        <?php if ($unlockCost === 'free' && $viewsLeft <= 1): ?>
            <div style="margin-top: 14px; padding-top: 14px; border-top: 1px solid #e2e8f0; width: 100%; color: #64748b; font-size: 0.8rem;">
                ¿Consultas varias empresas al mes?
                <a href="<?= site_url('dashboard?view=risk') ?>" style="color: #2563eb; font-weight: 700; text-decoration: underline;">Solvencia Pro por <?= solvencia('precios.pro_mensual', '29 €') ?>/mes</a>
            </div>
        <?php endif; ?>
    </div>
</div>
