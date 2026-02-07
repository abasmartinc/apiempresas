<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/dashboard.css') ?>" />


</head>

<body>
<div class="bg-halo" aria-hidden="true"></div>

<div class="auth-wrapper">
    <?=view('partials/header_inner') ?>

    <main class="dash-main">
        <div class="container">
            <div class="dash-header">
                <h1>Bienvenido, <?=htmlspecialchars($user->name ?? 'Cliente') ?></h1>
                <p class="dash-sub">
                    Tu cuenta está lista. Te recomendamos completar los pasos de inicio para hacer tu primera consulta y dejar la integración funcionando en producción.
                </p>
            </div>

            <!-- Onboarding strip -->
           <!-- <section class="onb-strip">
                <div class="onb-top">
                    <div>
                        <div class="kicker">Inicio rápido</div>
                        <p class="onb-title">Configura tu primera integración en menos de 5 minutos</p>
                        <p class="onb-desc">
                            Completa estos pasos y tendrás tu primer “lookup” por CIF funcionando. Si necesitas ayuda, la documentación tiene ejemplos listos para copiar/pegar.
                        </p>
                    </div>

                    <div class="onb-progress" aria-label="Progreso de onboarding">
                        <strong>Progreso: 1/3</strong>
                        <div class="onb-bar" role="progressbar" aria-valuenow="33" aria-valuemin="0" aria-valuemax="100">
                            <div class="onb-fill"></div>
                        </div>
                    </div>
                </div>

                <ul class="checklist">
                    <li class="check done">
                        <div class="dot" aria-hidden="true"></div>
                        <div>
                            <strong>1) Copia tu API key</strong>
                            <span>Guárdala en variables de entorno (por ejemplo <code>APIEMPRESAS_KEY</code>).</span>
                        </div>
                    </li>
                    <li class="check">
                        <div class="dot" aria-hidden="true"></div>
                        <div>
                            <strong>2) Haz tu primera consulta</strong>
                            <span>Prueba con el endpoint básico y revisa la respuesta JSON. <a href="<?php /*=site_url() */?>documentation#company">Ver ejemplo</a></span>
                        </div>
                    </li>
                    <li class="check">
                        <div class="dot" aria-hidden="true"></div>
                        <div>
                            <strong>3) Activa el plan adecuado</strong>
                            <span>Cuando pases a producción, evita límites de prueba y accede a métricas avanzadas.</span>
                        </div>
                    </li>
                </ul>
            </section>-->

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
                    </section>

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
                        $get = function($src, $key, $default = null) {
                            if (is_array($src)) return $src[$key] ?? $default;
                            if (is_object($src)) return $src->$key ?? $default;
                            return $default;
                        };
                        $planNameRaw = $get($plan, 'plan_name', 'Free');
                        $currentPlanSlug = strtolower(trim($planNameRaw));
                        $planClass = '';
                        if (strpos($currentPlanSlug, 'business') !== false) $planClass = 'plan-card--business';
                        elseif (strpos($currentPlanSlug, 'pro') !== false) $planClass = 'plan-card--pro';
                    ?>
                    <!-- Debug: Plan name is "<?= esc($planNameRaw) ?>" -> slug: "<?= esc($currentPlanSlug) ?>" -->
                    <section class="plan-card <?= $planClass ?>">
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
                                <?= (strpos($currentPlanSlug, 'pro') !== false) ? 'Mejorar a Business' : 'Pasar a Pro' ?>
                            </button>
                            <span class="back_to_free">Puedes volver a Free cuando quieras.</span>
                        </div>
                        <?php endif; ?>
                    </section>

                    <!-- ACCOUNT STATUS -->
                    <section class="mini-card">
                        <h3>Estado de tu cuenta</h3>
                        <p>
                            Plan: <strong><?= esc($plan->plan_name ?? 'Free') ?></strong><br>
                            Consultas este mes: <strong><?= esc($api_request_total_month ?? 0) ?></strong><br>
                            <?php if(!empty($plan->current_period_end)): ?>
                                Renovación: <strong><?= date('d-m-Y', strtotime($plan->current_period_end)) ?></strong>
                            <?php endif; ?>
                        </p>
                    </section>

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
