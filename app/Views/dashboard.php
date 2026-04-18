<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/dashboard.css?v=' . (file_exists(FCPATH . 'public/css/dashboard.css') ? filemtime(FCPATH . 'public/css/dashboard.css') : time())) ?>" />


</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <?=view('partials/header_inner') ?>

    <main class="dash-main">
        <div class="container">
            <?= view('partials/usage_trigger_banner') ?>
            <div class="dash-header">
                <?php 
                    $userName = 'Cliente';
                    if (is_object($user)) $userName = $user->name ?? 'Cliente';
                    elseif (is_array($user)) $userName = $user['name'] ?? 'Cliente';

                    // Definir helpers y slug de plan al inicio para evitar Errores de variable indefinida
                    $get = function($src, $key, $default = null) {
                        if (is_array($src)) return $src[$key] ?? $default;
                        if (is_object($src)) return $src->$key ?? $default;
                        return $default;
                    };
                    $planNameRaw = $get($plan, 'plan_name', 'Free');
                    $currentPlanSlug = strtolower(trim($planNameRaw));
                ?>
                <h1>Bienvenido, <?= htmlspecialchars($userName) ?></h1>
                
                <?php if($currentPlanSlug === 'free' || empty($currentPlanSlug)): ?>
                <!-- CTA PRINCIPAL ARRIBA -->
                <div class="dash-upgrade-banner" style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border: 1px solid #bfdbfe; border-radius: 20px; padding: 24px; margin: 12px 0 16px; display: flex; align-items: center; justify-content: space-between; gap: 24px; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.05);">
                    <div style="flex: 1;">
                        <h2 style="margin: 0 0 8px; font-size: 1.4rem; color: #1e40af; font-weight: 800;">🚀 Lleva tu integración a producción</h2>
                        <p style="margin: 0; color: #1e40af; opacity: 0.9; font-size: 1rem; line-height: 1.5;">Evita bloqueos y limitaciones antes de escalar tu aplicación.</p>
                    </div>
                    <div style="text-align: right;">
                        <a href="<?=site_url() ?>billing" class="btn" style="padding: 14px 28px; font-size: 1rem; font-weight: 700; background: #2563eb; color: #fff; border: none; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);">👉 Activar Pro para producción</a>
                        <div style="margin-top: 8px; font-size: 12px; color: #1e40af; opacity: 0.8; font-weight: 600;">Sin permanencia. Cancela cuando quieras.</div>
                    </div>
                </div>
                <div style="margin: 4px 0 16px 12px; font-size: 14px; color: #475569; font-weight: 700; display: flex; align-items: center; gap: 8px;">
                    <span>⚡ Empieza en Free, pero activa Pro antes de lanzar a producción.</span>
                </div>
                <?php endif; ?>

            </div>

            <!-- Onboarding strip -->
            <!-- Onboarding strip -->
            <?php if (empty($api_request_total_month) || $api_request_total_month == 0): ?>
            <section class="onb-strip">
                <div class="onb-top">
                    <div>
                        <div class="kicker">Guía de Inicio</div>
                        <p class="onb-title">Cómo realizar tu primera consulta</p>
                        <p class="onb-desc">
                            Sigue estos simples pasos para integrar la API en tu aplicación. Esta guía desaparecerá una vez recibamos tu primera petición.
                        </p>
                    </div>
                </div>
                <div class="onb-steps">
                    <div class="onb-step">
                        <strong>1. Obtén tu API Key</strong>
                        <p>Copia la clave que aparece en la sección "Tu API Key" situada más abajo.</p>
                    </div>
                    <div class="onb-step">
                        <strong>2. Realiza una petición</strong>
                        <p>Prueba el endpoint de empresas enviando un CIF válido en la cabecera <code>X-Authorization</code>.</p>
                    </div>
                    <div class="onb-step">
                        <strong>3. Revisa la respuesta</strong>
                        <p>Si todo es correcto, recibirás un JSON con los datos de la empresa. <a href="<?=site_url() ?>documentation#company">Ver documentación</a></p>
                    </div>
                    <div class="onb-step">
                        <strong>4. Escala a producción</strong>
                        <p>Activa el plan Pro para eliminar limitaciones y garantizar estabilidad en tu aplicación.</p>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <div class="dash-grid">
                <!-- LEFT -->
                <div>
                    <!-- API KEY -->
                    <section class="dash-card">
                        <div class="kicker">Seguridad</div>
                        <h2>Tu API Key principal</h2>
                        <p>Usa esta clave en tu backend para autenticar las peticiones. No la expongas en frontend.</p>

                        <div class="apikey-row">
                            <div class="apikey-box" id="apiKeyBox" data-api-key="<?=htmlspecialchars($api_key->api_key ?? '') ?>">
                                <div>
                                    <div class="apikey-label">API KEY</div>
                                    <div class="apikey-value" id="apiKeyMasked"><?=htmlspecialchars($api_key->api_key ?? '') ?></div>
                                </div>
                            </div>
                            <div class="apikey-actions">
                                <button type="button" class="btn-small" id="btnToggleKey">Mostrar</button>
                                <button type="button" class="btn-small primary" id="btnCopyKey">Copiar</button>
                            </div>
                        </div>

                        <p class="usage-footnote" style="margin-top:12px;">
                            Recomendación: guarda la key en variables de entorno y rota la clave cuando termines la fase de pruebas.
                        </p>

                        <!-- PRODUCCIÓN MESSAGE DEBAJO API KEY -->
                        <div class="apikey-prod-cta" style="margin-top: 24px; padding: 20px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                            <div style="font-weight: 800; color: #0f172a; margin-bottom: 6px; font-size: 1.1rem;">⚡ ¿Vas a usar la API en producción?</div>
                            <p style="font-size: 14px; color: #475569; margin-bottom: 16px; line-height: 1.5;">El plan Free puede bloquear tu integración al escalar. Activa Pro para evitar interrupciones cuando tu aplicación empiece a recibir tráfico real.</p>
                            <a href="<?=site_url() ?>billing" class="btn-small primary" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 16px;">👉 Activar Pro para producción</a>
                            <div style="margin-top: 10px; font-size: 11px; color: #64748b;">Sin permanencia. Cancela cuando quieras.</div>
                        </div>
                    </section>

                    <?= view('partials/dashboard/wordpress_plugin') ?>

                    <!-- FIRST REQUEST -->
                    <section class="dash-card">
                        <div class="kicker">Primera consulta</div>
                        <h2>Haz tu primer request (copia/pega)</h2>
                        <p>Ejemplo con cURL para validar un CIF y obtener la ficha básica.</p>

                        <div class="quick-grid">
                            <div class="quick-item">
                                <strong>cURL</strong>
                                <code>GET /api/v1/companies?cif=B12345678</code>
                                <p style="margin:10px 0 0; color:#64748b; font-size:12px;">
                                    Usa tu API key en header (Authorization / X-API-KEY según tu implementación).
                                </p>
                                <a href="<?=site_url() ?>documentation#endpoint-by-cif">Ver ejemplo completo →</a>
                            </div>
                            <div class="quick-item">
                                <strong>Buscador web</strong>
                                <p style="margin:0; color:#64748b;">
                                    Ideal para validar manualmente sin escribir código (pruebas rápidas).
                                </p>
                                <a href="<?=site_url() ?>search_company">Abrir buscador →</a>
                            </div>
                        </div>
                    </section>

                    <!-- QUICKSTART / DOCS -->
                    <section class="dash-card">
                        <div class="kicker">Recursos</div>
                        <h2>Empieza en 5 minutos</h2>
                        <p>Endpoints principales y recursos para integrar APIEmpresas en tus flujos.</p>

                        <div class="quick-grid">
                            <div class="quick-item">
                                <strong>Ejemplos PHP / Laravel</strong>
                                <p style="margin:4px 0 0; color:#64748b;">Snippets de código listos para copiar y pegar en tu proyecto.</p>
                                <a href="<?=site_url() ?>documentation#examples">Ver ejemplos PHP →</a>
                            </div>
                            <div class="quick-item">
                                <strong>Ejemplos Node / JS</strong>
                                <p style="margin:4px 0 0; color:#64748b;">Código para integrar en backend Node o frontend (fetch).</p>
                                <a href="<?=site_url() ?>documentation#examples">Ver ejemplos Node →</a>
                            </div>
                            <div class="quick-item">
                                <strong>Consumo y límites</strong>
                                <p style="margin:4px 0 0; color:#64748b;">Cómo se contabiliza una consulta y mejores prácticas de caché.</p>
                                <a href="<?=site_url() ?>consumption">Ver consumo →</a>
                            </div>
                            <div class="quick-item">
                                <strong>Buenas prácticas</strong>
                                <p style="margin:4px 0 0; color:#64748b;">Retries, timeouts, rate limits y seguridad.</p>
                                <a href="<?=site_url() ?>documentation#best-practices">Abrir sección →</a>
                            </div>
                        </div>
                    </section>
                </div>

                <!-- RIGHT -->
                <aside>
                    <!-- PLAN -->
                    <?php 
                        $planClass = '';
                        if (strpos($currentPlanSlug, 'business') !== false) $planClass = 'plan-card--business';
                        elseif (strpos($currentPlanSlug, 'pro') !== false) $planClass = 'plan-card--pro';
                    ?>
                    <!-- Debug: Plan name is "<?= esc($planNameRaw) ?>" -> slug: "<?= esc($currentPlanSlug) ?>" -->
                    <section class="plan-card <?= $planClass ?>">
                        <?php if($currentPlanSlug === 'free' || empty($currentPlanSlug)): ?>
                            <div class="plan-pill" style="background: #fef2f2; color: #991b1b; border: 1px solid #fecaca;">
                                <span>⚠️ Estás en plan Free</span>
                            </div>

                            <h2 style="font-size: 1.75rem; margin-top: 12px;">Plan Free</h2>
                            <p style="margin: 0 0 16px; color: rgba(239,246,255,0.9); font-size: 14px; line-height: 1.6;">
                                Tu uso actual tiene limitaciones importantes:
                            </p>

                            <div class="plan-meta" style="margin-bottom: 24px; background: rgba(255,255,255,0.1); padding: 16px; border-radius: 12px;">
                                <div style="margin-bottom: 8px;">• Solo 100 consultas/mes</div>
                                <div style="margin-bottom: 8px;">• Puede bloquearse al escalar</div>
                                <div style="margin-bottom: 8px;">• No apto para producción real</div>
                                <div>• Sin soporte prioritario</div>
                            </div>

                            <div style="margin-bottom: 20px; font-weight: 700; font-size: 15px; color: #ffffff;">
                                Si tu integración crece, necesitarás Pro. Muchos usuarios cambian a Pro antes de pasar a producción. Evita problemas cuando tu aplicación empiece a crecer.
                            </div>

                            <button class="btn" type="button" onclick="window.location.href='<?=site_url() ?>billing'" style="width: 100%; background: #ffffff; color: var(--primary); font-weight: 800; font-size: 1.1rem; padding: 16px;">
                                👉 Activar Pro para producción
                            </button>
                            
                            <div style="text-align: center; margin-top: 12px; font-size: 12px; color: rgba(255,255,255,0.8);">
                                Sin permanencia. Cancela cuando quieras.
                            </div>
                        <?php else: ?>
                            <div class="plan-pill">
                                <span>PLAN ACTUAL</span>
                            </div>

                            <h2><?= esc($planNameRaw) ?></h2>
                            <div class="plan-price"><?= esc($get($plan, 'price_monthly', '0')) ?> €/mes</div>
                            <p style="margin:0 0 12px; color:rgba(239,246,255,.9); font-size:13px;">
                                <?php if($currentPlanSlug === 'business'): ?>
                                    Plan máximo para empresas con volumen alto y gestión de equipos.
                                <?php elseif($currentPlanSlug === 'pro'): ?>
                                    Plan ideal para producción: límites ampliados y monitorización.
                                <?php else: ?>
                                    Plan recomendado para pruebas, desarrollo y entornos de staging.
                                <?php endif; ?>
                            </p>

                            <div class="plan-meta">
                                <?php if($currentPlanSlug === 'business'): ?>
                                    <div>• Límite alto para uso intensivo y masivo.</div>
                                    <div>• SLA avanzado y soporte dedicado.</div>
                                    <div>• Gestión de equipos y roles incluida.</div>
                                <?php elseif($currentPlanSlug === 'pro'): ?>
                                    <div>• Límite mensual superior para producción.</div>
                                    <div>• Métricas de latencia y errores incluidas.</div>
                                    <div>• Soporte prioritario para integración.</div>
                                <?php else: ?>
                                    <div>• Límite mensual bajo para validar PoC.</div>
                                    <div>• Ideal para integrar y testear endpoints.</div>
                                    <div>• Cuando pases a producción, cambia a Pro/Business.</div>
                                <?php endif; ?>
                            </div>

                            <?php if($currentPlanSlug !== 'business'): ?>
                            <div class="alert-upgrade">
                                <strong>Consejo para producción</strong>
                                <span>
                                    <?php if($currentPlanSlug === 'pro'): ?>
                                        ¿Tu volumen crece? Pasa al plan Business para SLA avanzado y gestión multicuenta.
                                    <?php else: ?>
                                        Si vas a integrar en producción, te conviene Pro para evitar bloqueos y tener visibilidad.
                                    <?php endif; ?>
                                </span>
                                <button class="btn" type="button" onclick="window.location.href='<?=site_url() ?>billing'">
                                    <?= (strpos($currentPlanSlug, 'pro') !== false) ? '👉 Activar Pro para producción' : '👉 Activar Pro para producción' ?>
                                </button>
                                <span class="back_to_free">Puedes volver a Free cuando quieras.</span>
                            </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </section>

                    <!-- RADAR CTA -->
                    <section class="dash-cta-card">
                        <h3>
                            <div class="dash-cta-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83"/></svg>
                            </div>
                            ¿Vendes a otras empresas?
                        </h3>
                        <p>
                            Monitorizamos el <strong>BORME</strong> cada día para entregarte leads cualificados antes que nadie. Descubre el potencial del Radar.
                        </p>
                        <a href="<?=site_url() ?>leads-empresas-nuevas" class="btn">
                            Descubrir Radar PRO →
                        </a>
                    </section>

                    <!-- ACCOUNT STATUS -->
                    <section class="mini-card">
                        <h3>Estado de tu cuenta</h3>
                        <div style="font-size: 14px; line-height: 1.6;">
                            Plan: <strong style="color: var(--primary)"><?= esc($get($plan, 'plan_name', 'Free')) ?></strong><br>
                            
                            <?php if($currentPlanSlug === 'free' || empty($currentPlanSlug)): ?>
                                Has usado <strong id="status-requests-text">...</strong> de 100 consultas incluidas en tu plan Free.<br>
                                <div style="margin-top: 8px; font-size: 12px; color: #64748b; font-weight: 600;">
                                    ⚠️ Cuando empieces a usar la API en producción, este límite puede bloquear tu servicio en producción.
                                </div>
                                <a href="<?=site_url() ?>billing" style="display: block; margin-top: 10px; font-weight: 800; color: var(--primary); text-decoration: none; font-size: 13px;">👉 Activar Pro para producción</a>
                            <?php else: ?>
                                Consultas este mes: <strong id="status-requests-text">...</strong> de <?= number_format($get($plan, 'monthly_quota', 0), 0, ',', '.') ?><br>
                                <a href="<?=site_url() ?>billing" style="display: block; margin-top: 10px; font-weight: 800; color: var(--primary); text-decoration: none; font-size: 13px;">👉 Activar Pro para producción</a>
                            <?php endif; ?>

                            <?php $p_end = $get($plan, 'current_period_end'); if(!empty($p_end)): ?>
                                <div style="margin-top: 8px; border-top: 1px solid #f1f5f9; padding-top: 8px;">
                                    Renovación: <strong><?= date('d-m-Y', strtotime($p_end)) ?></strong>
                                </div>
                            <?php endif; ?>
                        </div>
                    </section>

                    <!-- GLOBAL MICROCOPY -->
                    <div style="padding: 0 12px; margin-bottom: 24px; font-size: 13px; color: #64748b; font-style: italic; text-align: center; font-weight: 600;">
                        "La mayoría de integraciones activas usan Pro para evitar limitaciones en producción"
                    </div>

                    <!-- NEXT ACTIONS -->
                    <section class="mini-card">
                        <h3>Próximos pasos recomendados</h3>
                        <ul class="hint-list">
                            <li>
                                <div>
                                    <strong>Guarda la key en tu .env</strong>
                                    <span>Evita hardcodear claves en repositorios o logs.</span>
                                </div>
                            </li>
                            <li>
                                <div>
                                    <strong>Define caché local</strong>
                                    <span>Reduce coste y latencia si consultas el mismo CIF repetidamente.</span>
                                </div>
                            </li>
                            <li>
                                <div>
                                    <strong>Activa alertas de consumo</strong>
                                    <span>Te avisaremos por email al 80% y 100% del límite.</span>
                                </div>
                            </li>
                        </ul>
                    </section>

                    <!-- SUPPORT -->
                    <section class="mini-card">
                        <h3>¿Necesitas ayuda?</h3>
                        <p>Si tienes una duda sobre la integración o uso del API, escríbenos.</p>
                        <p style="margin:0;">
                            <a href="mailto:soporte@apiempresas.es">soporte@apiempresas.es</a><br />
                            <span class="mini-note">Tiempo medio de respuesta &lt; 24h laborables.</span>
                        </p>
                    </section>
                </aside>
            </div>
        </div>
    </main>

    <?=view('partials/footer') ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const monthlyQuota = <?= json_encode((int)($get($plan, 'monthly_quota', 0))) ?>;
        
        fetch('<?= site_url('dashboard/kpis') ?>', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            if(data.error) return;
            
            // Formatter
            const numFmt = new Intl.NumberFormat('es-ES');
            
            // Update Right Widget Monthly Status
            const statusReqEl = document.getElementById('status-requests-text');
            if(statusReqEl) statusReqEl.innerText = numFmt.format(data.api_request_total_month || 0);

        })
        .catch(e => console.error('Error fetching KPIs', e));
    });
</script>

<script>
    // === Lógica sencilla para mostrar/ocultar y copiar API key ===
    (function(){
        const box = document.getElementById('apiKeyBox');
        if(!box) return;

        const realKey = box.getAttribute('data-api-key') || '';
        const masked = '•'.repeat(Math.max(realKey.length - 8, 12));
        const valueEl = document.getElementById('apiKeyMasked');
        const btnToggle = document.getElementById('btnToggleKey');
        const btnCopy = document.getElementById('btnCopyKey');

        let visible = false;
        valueEl.textContent = masked;

        btnToggle.addEventListener('click', () => {
            visible = !visible;
            valueEl.textContent = visible ? realKey : masked;
            btnToggle.textContent = visible ? 'Ocultar' : 'Mostrar';
        });

        btnCopy.addEventListener('click', async () => {
            try{
                await navigator.clipboard.writeText(realKey);
                btnCopy.textContent = 'Copiado ✓';
                setTimeout(() => btnCopy.textContent = 'Copiar', 1800);
            }catch(e){
                alert('No se pudo copiar la clave. Copia el texto manualmente.');
            }
        });
    })();
</script>

</body>
</html>
