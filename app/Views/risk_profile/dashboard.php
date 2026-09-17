<?= $this->extend(($isHtmx ?? false) ? 'layouts/htmx' : 'layouts/app') ?>

<?= $this->section('styles') ?>
<style>
    .risk-dash {
        padding: 32px 0 64px 0;
    }
    .risk-dash-hero {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        flex-wrap: wrap;
        gap: 20px;
        margin-bottom: 28px;
    }
    .risk-dash-title {
        font-size: 2rem;
        font-weight: 900;
        color: #0f172a;
        margin: 0 0 6px 0;
        letter-spacing: -0.5px;
    }
    .risk-dash-subtitle {
        font-size: 1rem;
        color: #64748b;
        margin: 0;
    }
    .risk-kpi-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 20px;
        margin-bottom: 32px;
    }
    .risk-kpi-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }
    .risk-kpi-label {
        font-size: 0.78rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 8px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .risk-kpi-val {
        font-size: 2.2rem;
        font-weight: 900;
        color: #0f172a;
        line-height: 1.1;
        margin-bottom: 6px;
    }
    .risk-kpi-desc {
        font-size: 0.85rem;
        color: #64748b;
        line-height: 1.4;
    }
    .risk-search-box {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        border-radius: 20px;
        padding: 32px;
        color: #ffffff;
        margin-bottom: 36px;
        box-shadow: 0 15px 30px rgba(15, 23, 42, 0.15);
        position: relative;
        overflow: hidden;
    }
    .risk-search-box::after {
        content: '';
        position: absolute;
        top: -60px;
        right: -60px;
        width: 220px;
        height: 220px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.25) 0%, transparent 70%);
        pointer-events: none;
    }
    .risk-search-input-group {
        display: flex;
        gap: 12px;
        max-width: 720px;
        margin-top: 20px;
    }
    .risk-search-input {
        flex: 1;
        background: #ffffff;
        border: 2px solid transparent;
        border-radius: 12px;
        padding: 14px 20px;
        font-size: 1rem;
        color: #0f172a;
        outline: none;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transition: border-color 0.2s;
    }
    .risk-search-input:focus {
        border-color: #3b82f6;
    }
    .risk-search-btn {
        background: #2563eb;
        color: #ffffff;
        border: none;
        border-radius: 12px;
        padding: 14px 28px;
        font-size: 1rem;
        font-weight: 800;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 14px rgba(37, 99, 235, 0.4);
        white-space: nowrap;
    }
    .risk-search-btn:hover {
        background: #1d4ed8;
        transform: translateY(-1px);
    }
    .risk-table-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
        margin-bottom: 36px;
    }
    .risk-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        margin-top: 16px;
    }
    .risk-table th {
        font-size: 0.75rem;
        font-weight: 800;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 14px;
        border-bottom: 1px solid #e2e8f0;
    }
    .risk-table td {
        padding: 16px 14px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: middle;
        font-size: 0.92rem;
    }
    .risk-table tr:last-child td {
        border-bottom: none;
    }
    .risk-score-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 12px;
        border-radius: 999px;
        font-weight: 800;
        font-size: 0.82rem;
        text-transform: uppercase;
    }
    .risk-score-bajo {
        background: #ecfdf5;
        color: #047857;
        border: 1px solid #a7f3d0;
    }
    .risk-score-medio {
        background: #fffbeb;
        color: #b45309;
        border: 1px solid #fde68a;
    }
    .risk-score-alto {
        background: #fef2f2;
        color: #b91c1c;
        border: 1px solid #fecaca;
    }
    .risk-upgrade-banner {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 2px solid #bfdbfe;
        border-radius: 20px;
        padding: 32px;
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: space-between;
        gap: 24px;
        margin-bottom: 36px;
    }
    .risk-api-accordion {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 16px 20px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container risk-dash">
    
    <!-- HEADER HERO -->
    <div class="risk-dash-hero">
        <div>
            <div style="display: inline-flex; align-items: center; gap: 8px; background: #eff6ff; color: #1d4ed8; padding: 4px 12px; border-radius: 999px; font-size: 0.78rem; font-weight: 800; text-transform: uppercase; margin-bottom: 12px; border: 1px solid #bfdbfe;">
                🛡️ Solvencia & Riesgo Mercantil
            </div>
            <h1 class="risk-dash-title">
                ¡Hola, <?= esc($user->name ?: 'Usuario') ?>!
            </h1>
            <p class="risk-dash-subtitle">
                Comprueba qué consta de tus clientes y proveedores, consulta sus actos del BORME y entérate el día que se publique algo nuevo.
            </p>
        </div>

        <div style="display: flex; align-items: center; gap: 10px;">
            <?php if (session('is_admin')): ?>
                <a href="<?= site_url('dashboard?view=client') ?>" style="display: inline-flex; align-items: center; gap: 6px; background: #ffffff; border: 1px solid #cbd5e1; padding: 8px 14px; border-radius: 10px; font-size: 0.82rem; font-weight: 700; color: #475569; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9';" onmouseout="this.style.background='#ffffff';">
                    Panel General (Admin)
                </a>
            <?php endif; ?>
            <?php if (!empty($isSubscriber)): ?>
                <span style="background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857; padding: 8px 16px; border-radius: 10px; font-size: 0.85rem; font-weight: 800; display: inline-flex; align-items: center; gap: 6px;">
                    ⭐ Solvencia Pro Activo
                </span>
            <?php else: ?>
                <form method="post" action="<?= site_url('billing/checkout') ?>" style="margin: 0;">
                    <?= csrf_field() ?>
                    <input type="hidden" name="plan" value="risk_pro">
                    <input type="hidden" name="period" value="monthly">
                    <button type="submit" style="background: #2563eb; color: #ffffff; border: none; padding: 9px 18px; border-radius: 10px; font-size: 0.85rem; font-weight: 800; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(37,99,235,0.25);" onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
                        Activar Solvencia Pro (<?= solvencia('precios.pro_mensual', '29 €') ?>) ⭐
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- TICKETS RESPONDIDOS SI LOS HAY -->
    <?php if (!empty($answeredTickets)): ?>
        <?php foreach ($answeredTickets as $t): ?>
            <?php 
            $tSubject = is_object($t) ? ($t->subject ?? '') : ($t['subject'] ?? '');
            $tId = is_object($t) ? ($t->id ?? '') : ($t['id'] ?? '');
            ?>
            <div style="background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 12px; padding: 14px 18px; margin-bottom: 24px; display: flex; align-items: center; justify-content: space-between; gap: 12px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 1.2rem;">💬</span>
                    <div>
                        <strong style="color: #065f46; font-size: 0.92rem;">Respuesta de soporte en: <?= esc($tSubject) ?></strong>
                        <div style="color: #047857; font-size: 0.82rem;">El equipo de APIEmpresas ha respondido a tu consulta.</div>
                    </div>
                </div>
                <a href="<?= site_url('tickets/view/' . $tId) ?>" style="background: #059669; color: #ffffff; padding: 6px 12px; border-radius: 8px; font-size: 0.82rem; font-weight: 700; text-decoration: none;">Ver respuesta</a>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- KPI GRID -->
    <div class="risk-kpi-grid">
        
        <!-- CARD 1: ESTADO DEL PLAN -->
        <div class="risk-kpi-card">
            <div>
                <div class="risk-kpi-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    Plan Activo
                </div>
                <div class="risk-kpi-val" style="font-size: 1.7rem;">
                    <?= esc($planName) ?>
                </div>
                <p class="risk-kpi-desc">
                    <?php if (!empty($isSubscriber)): ?>
                        Vigilamos en el BORME hasta <?= (int) solvencia('vigilanciasPro', 25) ?> empresas de tu cartera y te avisamos de cada movimiento. Dictámenes y PDF de cualquier CIF de España.
                    <?php else: ?>
                        Dispones de <strong><?= (int) solvencia('consultasGratis', 3) ?> consultas completas gratuitas</strong> al mes para analizar a cualquier empresa.
                    <?php endif; ?>
                </p>
            </div>
            <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                <?php if (!empty($isSubscriber)): ?>
                    <span style="color: #059669; font-size: 0.82rem; font-weight: 700;">✅ Suscripción activa</span>
                <?php else: ?>
                    <a href="#solvencia-pro-banner" style="color: #2563eb; font-size: 0.82rem; font-weight: 800; text-decoration: none;">Pasar a Pro (<?= solvencia('precios.pro_mensual', '29 €') ?>/mes) &rarr;</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- CARD 2: CUOTA MENSUAL -->
        <div class="risk-kpi-card">
            <div>
                <div class="risk-kpi-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 2v20"></path><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                    Consumo Este Mes
                </div>
                <div class="risk-kpi-val">
                    <?php if (!empty($isSubscriber)): ?>
                        <?php /* "Sin límite práctico" era el mismo "ilimitado" con otras
                                 palabras. El suscriptor tiene tope (consultasPro) y, además
                                 de no engañarle, saber cuántas lleva gastadas le interesa:
                                 es el mismo contador que ve el gratuito, con su número. */ ?>
                        <span><?= (int) $viewsUsed ?></span>
                        <span style="font-size: 1.1rem; color: #94a3b8; font-weight: 700;">/ <?= (int) solvencia('consultasPro', 300) ?> consultas</span>
                    <?php else: ?>
                        <span id="kpiViewsUsedVal"><?= (int)$viewsUsed ?></span> <span style="font-size: 1.1rem; color: #94a3b8; font-weight: 700;">/ <?= (int) solvencia('consultasGratis', 3) ?> empresas</span>
                    <?php endif; ?>
                </div>

                <?php if (empty($isSubscriber)): ?>
                    <?php $userCredits = (int)($user->risk_credits ?? $riskCredits ?? 0); ?>
                    <?php if ($userCredits > 0): ?>
                        <div style="display: inline-flex; align-items: center; gap: 6px; background: #e0e7ff; color: #3730a3; padding: 2px 9px; border-radius: 999px; font-size: 0.74rem; font-weight: 800; margin-bottom: 8px;">
                            ⚡ <?= $userCredits ?> <?= $userCredits === 1 ? 'auditoría disponible' : 'auditorías disponibles' ?> (Pack)
                        </div>
                    <?php endif; ?>

                    <!-- Progress Bar -->
                    <?php 
                    $gratisCfg = max(1, (int) solvencia('consultasGratis', 3));
                    $pct = min(100, round(($viewsUsed / $gratisCfg) * 100)); 
                    $barColor = $pct >= 100 ? ($userCredits > 0 ? '#4f46e5' : '#ef4444') : ($pct >= 66 ? '#f59e0b' : '#3b82f6');
                    ?>
                    <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin-bottom: 8px;">
                        <div id="kpiViewsProgressBar" style="width: <?= $pct ?>%; height: 100%; background: <?= $barColor ?>; border-radius: 999px; transition: width 0.4s ease;"></div>
                    </div>
                    <p class="risk-kpi-desc" id="kpiViewsDesc">
                        <?php if ($viewsUsed >= $gratisCfg): ?>
                            <?php if ($userCredits > 0): ?>
                                <span style="color: #4f46e5; font-weight: 700;">Cuota mensual agotada.</span> Usando tu Pack de auditorías prepagadas.
                            <?php else: ?>
                                <span style="color: #dc2626; font-weight: 700;">⚠️ Has alcanzado el límite mensual.</span> Se renovará el <?= esc($nextCycleDate) ?>.
                            <?php endif; ?>
                        <?php else: ?>
                            Te <?= ($gratisCfg - $viewsUsed === 1) ? 'queda' : 'quedan' ?> <strong><?= ($gratisCfg - $viewsUsed) ?> <?= ($gratisCfg - $viewsUsed === 1) ? 'consulta gratis' : 'consultas gratis' ?></strong> este mes.
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <?php
                    /*
                     * Esta rama la ve un SUSCRIPTOR, y decía "sin límites mensuales".
                     * Pero getQuotaStatus() corta a los suscriptores en consultasPro
                     * (300) con reason = 'subscriber_cap'. Prometerle a quien ya paga
                     * que no tiene tope y cortarle en la 301 es la peor versión del
                     * "ilimitado" que quitamos de todas las demás pantallas: aquí no
                     * cuesta una venta, cuesta una baja y una reclamación.
                     */
                    $proTope = (int) solvencia('consultasPro', 300);
                    ?>
                    <p class="risk-kpi-desc" style="color: #059669; font-weight: 600;">
                        De cualquier CIF de España. Se renuevan cada mes.
                    </p>
                <?php endif; ?>
            </div>
            <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid #f1f5f9; font-size: 0.8rem; color: #94a3b8;">
                Ciclo mensual actual &bull; Renueva: <?= esc($nextCycleDate) ?>
            </div>
        </div>

        <!-- CARD 3: HISTÓRICO DISPONIBLE -->
        <div class="risk-kpi-card">
            <div>
                <div class="risk-kpi-label">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 14 14"></polyline></svg>
                    Historial Desbloqueado
                </div>
                <div class="risk-kpi-val">
                    <span id="kpiAuditsCount"><?= count($audits) ?></span> <span style="font-size: 1.1rem; color: #94a3b8; font-weight: 700;">empresas</span>
                </div>
                <p class="risk-kpi-desc">
                    Puedes volver a revisar las empresas de tu historial cuantas veces quieras sin consumir consultas adicionales.
                </p>
            </div>
            <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid #f1f5f9; font-size: 0.82rem; color: #2563eb; font-weight: 700;">
                Acceso permanente a dictámenes vistos
            </div>
        </div>

    </div>

    <!-- BUSCADOR PRINCIPAL DE AUDITORÍAS -->
    <div class="risk-search-box">
        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 8px;">
            <span style="background: rgba(59, 130, 246, 0.25); color: #93c5fd; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; border: 1px solid rgba(59, 130, 246, 0.4);">
                Auditoría en Tiempo Real
            </span>
        </div>
        <h2 style="font-size: 1.6rem; font-weight: 900; margin: 0 0 8px 0; letter-spacing: -0.3px;">
            Auditar Nueva Empresa o Proveedor
        </h2>
        <p style="color: #cbd5e1; margin: 0; font-size: 0.95rem; max-width: 620px; line-height: 1.5;">
            Introduce el CIF o razón social para comprobar su semáforo de riesgo, scoring IES (0-100), alertas de concurso en BORME e historial público:
        </p>

        <form id="dashRiskSearchForm" class="risk-search-input-group" onsubmit="handleRiskSearchSubmit(event); return false;" hx-boost="false" action="javascript:void(0);">
            <input 
                type="text" 
                name="cif" 
                id="dashRiskCifInput" 
                class="risk-search-input" 
                placeholder="Ej. B85402030, Mercadona, Telefónica..." 
                autocomplete="off" 
                onkeydown="if(event.key === 'Enter') { handleRiskSearchSubmit(event); return false; }"
                required
            >
            <button type="button" onclick="handleRiskSearchSubmit(event);" id="dashRiskSearchBtn" class="risk-search-btn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <span>Analizar Solvencia</span>
            </button>
        </form>

        <!-- ALERTA DE ERROR / AVISO -->
        <div id="riskSearchAlert" style="display: none; margin-top: 16px; background: #fee2e2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 16px; border-radius: 10px; font-size: 0.9rem; font-weight: 600; max-width: 720px;">
        </div>

        <!-- LOADING INDICATOR -->
        <div id="riskSearchLoading" style="display: none; margin-top: 16px; align-items: center; gap: 10px; color: #93c5fd; font-size: 0.95rem; font-weight: 600;">
            <div style="width: 20px; height: 20px; border: 3px solid rgba(255,255,255,0.3); border-top-color: #60a5fa; border-radius: 50%; animation: spin 0.8s linear infinite;"></div>
            <span>Auditando registros mercantiles oficiales y calculando perfil de riesgo en tiempo real...</span>
        </div>
        <style>
            @keyframes spin { 100% { transform: rotate(360deg); } }
        </style>
    </div>

    <!-- CONTENEDOR EN VIVO DE RESULTADO DE AUDITORÍA (IN-DASHBOARD) -->
    <div id="dashRiskLiveContainer" style="display: none; margin-bottom: 36px; background: #ffffff; border: 2px solid #2563eb; border-radius: 20px; box-shadow: 0 15px 35px rgba(37, 99, 235, 0.12); overflow: hidden; position: relative;">
        
        <!-- HEADER RESULTADO EN VIVO -->
        <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 18px 24px; display: flex; flex-wrap: wrap; justify-content: space-between; align-items: center; gap: 14px; border-bottom: 1px solid #334155;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 42px; height: 42px; border-radius: 50%; background: #2563eb; color: #fff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; font-weight: 800; flex-shrink: 0;">
                    🛡️
                </div>
                <div>
                    <h3 id="dashLiveCompanyName" style="margin: 0; font-size: 1.25rem; font-weight: 900; color: #ffffff; line-height: 1.2;">
                        Nombre Empresa
                    </h3>
                    <div style="display: flex; align-items: center; gap: 8px; margin-top: 2px;">
                        <span id="dashLiveCompanyCif" style="font-size: 0.82rem; color: #93c5fd; font-weight: 700; letter-spacing: 0.5px;">CIF: B00000000</span>
                        <span style="color: #64748b;">&bull;</span>
                        <span style="font-size: 0.8rem; color: #cbd5e1;">Evaluación procesada en vivo</span>
                    </div>
                </div>
            </div>

            <div style="display: flex; align-items: center; gap: 10px;">
                <button type="button" id="dashLiveBtnPdf" onclick="" style="background: #10b981; color: #ffffff; border: none; padding: 9px 16px; border-radius: 8px; font-weight: 800; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3); transition: background 0.2s;" onmouseover="this.style.background='#059669';" onmouseout="this.style.background='#10b981';">
                    <span>Descargar Dictamen PDF 📄</span>
                </button>
                <button type="button" onclick="closeDashLiveReport();" style="background: rgba(255,255,255,0.12); color: #ffffff; border: 1px solid rgba(255,255,255,0.2); padding: 8px 14px; border-radius: 8px; font-weight: 700; font-size: 0.85rem; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.25)';" onmouseout="this.style.background='rgba(255,255,255,0.12)';">
                    ✕ Cerrar Dictamen
                </button>
            </div>
        </div>

        <!-- CUERPO DEL INFORME (INYECCIÓN HTML) -->
        <div id="dashLiveReportBody" style="padding: 24px;">
            <!-- Contenido dinámico inyectado por AJAX sin salir del dashboard -->
        </div>
    </div>

    <!-- VIGILANCIA DEL BORME
         La lista se llena sola al desbloquear empresas, así que sin un sitio donde
         verla y podarla solo puede crecer: quien audita clientes acaba recibiendo
         avisos de empresas que miró una vez y no le importan. El botón reutiliza el
         mismo handler delegado de la ficha (data-risk-watch en head.php). -->
    <div class="risk-table-card" style="margin-bottom: 24px;">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 4px;">
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 900; color: #0f172a; margin: 0 0 4px 0;">
                    🔔 Empresas que vigilas
                </h3>
                <p style="color: #64748b; font-size: 0.88rem; margin: 0;">
                    Te avisamos por correo el día que aparezca un acto nuevo de estas empresas en el BORME.
                </p>
            </div>
            <?php
            // El contador de la derecha dice cuántas vigila y, si es gratuito,
            // cuántas le caben. Enseñar "4 de 5" antes de que se llene es lo que
            // hace que el tope no se descubra como un muro: cuando llegue al
            // quinto ya sabía que existía.
            // El respaldo ya no es "ilimitado": ningún plan lo es, y con true aquí
            // el contador desaparecía en vez de fallar de forma visible.
            $cupoV = $watchQuota ?? [
                'usadas'    => count($watches ?? []),
                'tope'      => (int) solvencia('vigilanciasGratis', 5),
                'ilimitado' => false,
                'quedan'    => 0,
                'lleno'     => false,
            ];
            ?>
            <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                <?php
                $vLleno = !empty($cupoV['lleno']);
                $vAviso = !$vLleno && !empty($cupoV['tope']) && (int) $cupoV['usadas'] >= ((int) $cupoV['tope'] - 1);
                $vFondo = $vLleno ? '#fef2f2' : ($vAviso ? '#fffbeb' : '#f1f5f9');
                $vColor = $vLleno ? '#b91c1c' : ($vAviso ? '#b45309' : '#64748b');
                ?>
                <a href="<?= site_url('cartera') ?>" style="display: inline-flex; align-items: center; gap: 7px; background: #ffffff; border: 1.5px solid #cbd5e1; border-radius: 10px; padding: 7px 14px; font-size: 0.83rem; font-weight: 800; color: #0f172a; text-decoration: none; white-space: nowrap; transition: all 0.15s;"
                   onmouseover="this.style.borderColor='#64748b'; this.style.background='#f8fafc';"
                   onmouseout="this.style.borderColor='#cbd5e1'; this.style.background='#ffffff';">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" style="flex-shrink:0;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    Subir mi cartera
                </a>
                <span style="font-size: 0.8rem; font-weight: 700; color: <?= $vColor ?>; background: <?= $vFondo ?>; padding: 4px 10px; border-radius: 8px; white-space: nowrap;">
                    <?php if (!empty($cupoV['ilimitado'])): ?>
                        <?= count($watches) ?> <?= count($watches) === 1 ? 'empresa' : 'empresas' ?> · sin límite
                    <?php else: ?>
                        <?= (int) $cupoV['usadas'] ?> de <?= (int) $cupoV['tope'] ?> empresas
                    <?php endif; ?>
                </span>
            </div>
        </div>

        <?php
        /*
         * Estos dos avisos daban por hecho que quien los lee es gratuito y que Pro
         * es ilimitado. Desde que Pro tiene su propio tope, las dos cosas son
         * falsas para un suscriptor: le decíamos "pasa a Pro" estando en Pro, y
         * "vigila las que quieras" cuando ya no es verdad para nadie.
         */
        $vEsPro = !empty($cupoV['es_pro']);
        ?>
        <?php if (!empty($cupoV['lleno'])): ?>
            <div style="margin-top: 16px; background: #fff7ed; border: 1px solid #fed7aa; color: #9a3412; padding: 13px 16px; border-radius: 11px; font-size: 0.88rem; line-height: 1.5;">
                <strong>Has llenado tu lista de vigilancia</strong>
                (<?= (int) $cupoV['tope'] ?> empresas<?= $vEsPro ? ' con Solvencia Pro' : ' con la cuenta gratuita' ?>).
                <?php if ($vEsPro): ?>
                    Para añadir otra, deja de vigilar alguna de abajo.
                    <a href="<?= site_url('tickets/create') ?>" style="color: #9a3412; font-weight: 700;">Si necesitas más, escríbenos</a>.
                <?php else: ?>
                    Para añadir otra, deja de vigilar una de abajo, o
                    <a href="<?= site_url('planes/pro') ?>" style="color: #9a3412; font-weight: 700;">pasa a Pro y vigila hasta <?= (int) solvencia('vigilanciasPro', 25) ?></a>.
                <?php endif; ?>
            </div>
        <?php elseif (!empty($watches) && (int) $cupoV['quedan'] === 1): ?>
            <div style="margin-top: 16px; background: #f8fafc; border: 1px solid #e2e8f0; color: #475569; padding: 11px 15px; border-radius: 11px; font-size: 0.85rem; line-height: 1.5;">
                Te queda <strong>1 hueco</strong> de los <?= (int) $cupoV['tope'] ?><?= $vEsPro ? ' de tu plan' : ' de la cuenta gratuita' ?>.
                <?php if (!$vEsPro): ?>
                    <a href="<?= site_url('planes/pro') ?>" style="color: #2563eb; font-weight: 700;">Con Pro son <?= (int) solvencia('vigilanciasPro', 25) ?></a>.
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (($alertsBorme ?? null) === 0): ?>
            <div style="margin-top: 16px; background: #fffbeb; border: 1px solid #fde68a; color: #92400e; padding: 13px 16px; border-radius: 11px; font-size: 0.88rem; line-height: 1.5;">
                Tienes los avisos del BORME desactivados, así que no te llegará ninguno aunque vigiles empresas.
                <a href="<?= site_url('profile') ?>" style="color: #92400e; font-weight: 700;">Activarlos en tu perfil</a>.
            </div>
        <?php endif; ?>

        <?php if (empty($watches)): ?>
            <div style="text-align: center; padding: 36px 20px; background: #fafafa; border-radius: 12px; margin-top: 20px; border: 1px dashed #cbd5e1;">
                <div style="width: 52px; height: 52px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: inline-flex; align-items: center; justify-content: center; font-size: 1.5rem; margin-bottom: 12px;">🔕</div>
                <div style="font-weight: 800; color: #0f172a; margin-bottom: 6px;">Todavía no vigilas ninguna empresa</div>
                <div style="color: #64748b; font-size: 0.9rem; max-width: 460px; margin: 0 auto 16px auto; line-height: 1.5;">
                    En la ficha de cualquier empresa, pulsa «Vigilar empresa» junto a la puntuación.
                    Si ya tienes la lista hecha, súbela de una vez y te decimos cuáles tienen algo.
                </div>
                <!-- Aquí es donde de verdad se lee esto: quien no vigila nada es
                     justo quien tiene la cartera en un Excel sin mirar. -->
                <a href="<?= site_url('cartera') ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #2563eb; color: #ffffff; border-radius: 11px; padding: 11px 20px; font-size: 0.9rem; font-weight: 800; text-decoration: none; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.28);"
                   onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                    Subir mi cartera en CSV
                </a>
            </div>
        <?php else: ?>
            <div style="margin-top: 18px; display: flex; flex-direction: column; gap: 10px;">
                <?php foreach ($watches as $w): ?>
                    <div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 13px 16px;">
                        <div style="flex: 1; min-width: 220px;">
                            <a href="<?= esc($w['url']) ?>" style="font-weight: 800; color: #0f172a; text-decoration: none; font-size: 0.95rem;">
                                <?= esc(company_display_name($w['company_name'], 'Empresa')) ?>
                            </a>
                            <div style="color: #94a3b8; font-size: 0.78rem; margin-top: 2px;">
                                <?= esc($w['cif']) ?>
                                <?php if (!empty($w['last_notified_at'])): ?>
                                    &bull; último aviso el <?= date('d/m/Y', strtotime($w['last_notified_at'])) ?>
                                <?php else: ?>
                                    &bull; sin avisos todavía
                                <?php endif; ?>
                            </div>
                        </div>
                        <button type="button"
                                data-risk-watch
                                data-cif="<?= esc($w['cif'], 'attr') ?>"
                                data-watching="1"
                                title="Dejar de recibir avisos de esta empresa"
                                style="flex-shrink: 0; display: inline-flex; align-items: center; gap: 6px; cursor: pointer; border-radius: 999px; padding: 6px 13px; font-size: 0.75rem; font-weight: 800; transition: all 0.15s; background: #ecfdf5; border: 1px solid #a7f3d0; color: #047857;">
                            <span data-watch-icon>🔔</span>
                            <span data-watch-label>Vigilando</span>
                        </button>
                    </div>
                <?php endforeach; ?>
            </div>

            <?php if (empty($isSubscriber)): ?>
                <div style="margin-top: 16px; background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 11px; padding: 13px 16px; color: #1e3a8a; font-size: 0.85rem; line-height: 1.5;">
                    Como usuario gratuito recibes el aviso con la empresa, el tipo de acto y la fecha.
                    Con <strong>Solvencia Pro</strong> el correo incluye además el detalle de cada acto.
                    <a href="<?= site_url('billing') ?>" style="color: #1d4ed8; font-weight: 700;">Ver Pro</a>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <!-- HISTORIAL DE EMPRESAS AUDITADAS -->
    <div class="risk-table-card">
        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px; margin-bottom: 4px;">
            <div>
                <h3 style="font-size: 1.25rem; font-weight: 900; color: #0f172a; margin: 0 0 4px 0;">
                    Tus Auditorías Recientes
                </h3>
                <p style="color: #64748b; font-size: 0.88rem; margin: 0;">
                    Empresas que has analizado. Pulsa en «Ver Dictamen» para cargarlo aquí mismo o descargar el PDF:
                </p>
            </div>
            <?php if (!empty($audits)): ?>
                <span id="auditsCountBadge" style="font-size: 0.8rem; font-weight: 700; color: #64748b; background: #f1f5f9; padding: 4px 10px; border-radius: 8px;">
                    <?= count($audits) ?> <?= count($audits) === 1 ? 'empresa guardada' : 'empresas guardadas' ?>
                </span>
            <?php endif; ?>
        </div>

        <?php if (empty($audits)): ?>
            <!-- EMPTY STATE -->
            <div id="dashEmptyAudits" style="text-align: center; padding: 48px 20px; background: #fafafa; border-radius: 12px; margin-top: 20px; border: 1px dashed #cbd5e1;">
                <div style="width: 56px; height: 56px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: inline-flex; align-items: center; justify-content: center; font-size: 1.6rem; margin-bottom: 14px;">
                    🛡️
                </div>
                <h4 style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0 0 6px 0;">Aún no has auditado ninguna empresa</h4>
                <p style="color: #64748b; font-size: 0.9rem; max-width: 440px; margin: 0 auto 20px auto; line-height: 1.45;">
                    Utiliza el buscador superior para comprobar la salud crediticia y estabilidad societaria de cualquier CIF en España. ¡El dictamen aparecerá en esta misma pantalla!
                </p>
                <button type="button" onclick="document.getElementById('dashRiskCifInput').focus();" style="background: #2563eb; color: #ffffff; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 700; font-size: 0.88rem; cursor: pointer;">
                    🔍 Realizar mi primera consulta gratis
                </button>
            </div>
            <div id="dashAuditsTableContainer" style="display: none; overflow-x: auto;">
                <table class="risk-table">
                    <thead>
                        <tr>
                            <th>Empresa / CIF</th>
                            <th>Gravedad</th>
                            <th>Alertas BORME</th>
                            <th>Última Consulta</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="dashAuditsTbody"></tbody>
                </table>
            </div>
        <?php else: ?>
            <!-- AUDITS TABLE -->
            <div id="dashAuditsTableContainer" style="overflow-x: auto;">
                <table class="risk-table">
                    <thead>
                        <tr>
                            <th>Empresa / CIF</th>
                            <th>Gravedad</th>
                            <th>Alertas BORME</th>
                            <th>Última Consulta</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="dashAuditsTbody">
                        <?php foreach ($audits as $item): ?>
                            <?php 
                            /*
                             * Antes se leía `risk_level` del motor (BAJO/MEDIO/ALTO). Ahora el
                             * rótulo sale del helper, que es el único sitio donde se decide qué
                             * se le enseña al cliente; el valor del motor sigue en la base de
                             * datos intacto para las consultas y las métricas.
                             */
                            $scoreFila = (int) ($item['risk_score'] ?? 50);
                            [$etiquetaFila] = risk_level_visual($scoreFila);
                            if ($scoreFila >= (int) solvencia('umbralAlto', 60)) {
                                $badgeClass = 'risk-score-alto';
                                $badgeIcon = '🔴';
                            } elseif ($scoreFila >= (int) solvencia('umbralMedio', 30)) {
                                $badgeClass = 'risk-score-medio';
                                $badgeIcon = '🟡';
                            } else {
                                $badgeClass = 'risk-score-bajo';
                                $badgeIcon = '🟢';
                            }
                            ?>
                            <tr id="row-cif-<?= esc($item['cif']) ?>">
                                <td>
                                    <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">
                                        <a href="javascript:void(0);" onclick="lookupCifInDashboard('<?= esc($item['cif']) ?>');" style="color: inherit; text-decoration: none;" onmouseover="this.style.color='#2563eb';" onmouseout="this.style.color='#0f172a';">
                                            <?= esc($item['company_name']) ?>
                                        </a>
                                    </div>
                                    <div style="font-size: 0.8rem; color: #64748b; display: flex; align-items: center; gap: 8px; margin-top: 2px;">
                                        <span style="font-weight: 600; color: #334155;"><?= esc($item['cif']) ?></span>
                                        <?php if (!empty($item['provincia'])): ?>
                                            <span>&bull; <?= esc($item['provincia']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="risk-score-badge <?= $badgeClass ?>">
                                        <span><?= $badgeIcon ?></span>
                                        <span><?= esc($etiquetaFila) ?> (<?= $scoreFila ?>/100)</span>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($item['alerts_count'] > 0): ?>
                                        <span style="color: #b91c1c; font-weight: 700; font-size: 0.85rem; display: inline-flex; align-items: center; gap: 4px;">
                                            ⚠️ <?= $item['alerts_count'] ?> <?= $item['alerts_count'] === 1 ? 'incidencia' : 'incidencias' ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #059669; font-size: 0.82rem; font-weight: 600;">
                                            ✓ Sin incidencias
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td style="color: #64748b; font-size: 0.85rem;">
                                    <?= date('d/m/Y H:i', strtotime($item['last_view_at'])) ?>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: inline-flex; gap: 8px; align-items: center;">
                                        <button type="button" onclick="lookupCifInDashboard('<?= esc($item['cif']) ?>');" style="background: #eff6ff; color: #1d4ed8; border: none; padding: 7px 12px; border-radius: 8px; font-weight: 700; font-size: 0.82rem; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#dbeafe';" onmouseout="this.style.background='#eff6ff';">
                                            Ver Dictamen ➔
                                        </button>
                                        <button type="button" onclick="downloadRiskPdf(<?= (int)$item['company_id'] ?>, '<?= esc($item['cif']) ?>');" style="background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='#ffffff';" title="Descargar el informe en PDF">
                                            <span>PDF 📄</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- BANNER UPGRADE A SOLVENCIA PRO (Si es usuario gratuito) -->
    <?php if (empty($isSubscriber)): ?>
        <div id="solvencia-pro-banner" class="risk-upgrade-banner">
            <div style="max-width: 580px;">
                <div style="display: inline-flex; align-items: center; gap: 6px; background: #dbeafe; color: #1e40af; padding: 4px 10px; border-radius: 999px; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; margin-bottom: 12px;">
                    <?php
                    /*
                     * Decía "Tarifa Plana Sin Límites" justo encima del párrafo que
                     * anuncia 25 empresas y 300 consultas: la misma tarjeta se
                     * contradecía en dos líneas. El argumento de Pro no es que no
                     * haya tope, es que el precio no depende de cuánto consultes.
                     */
                    ?>
                    ⭐ Tarifa plana mensual
                </div>
                <h3 style="font-size: 1.55rem; font-weight: 900; color: #0f172a; margin: 0 0 10px 0; letter-spacing: -0.4px;">
                    ¿Necesitas auditar múltiples empresas al mes?
                </h3>
                <p style="color: #334155; margin: 0 0 16px 0; font-size: 0.95rem; line-height: 1.5;">
                    Un informe suelto en un proveedor tradicional cuesta <strong><?= solvencia('precios.informe_tradicional', '20–44 €') ?></strong>. Con <strong>Solvencia Pro</strong> vigilas hasta <?= (int) solvencia('vigilanciasPro', 25) ?> empresas y tienes <?= (int) solvencia('consultasPro', 300) ?> consultas al mes por <strong><?= solvencia('precios.pro_mensual', '29 €') ?> / mes</strong>.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 16px; font-size: 0.88rem; color: #1e3a8a; font-weight: 700;">
                    <span>✓ Vigilancia del BORME con aviso por correo</span>
                    <?php /* "oficial" aplicado a NUESTRO semáforo, no a la fuente. Sobrevivió a la
                             limpieza de ayer. Los datos del BORME sí son oficiales; la conclusión
                             la calculamos nosotros. */ ?>
                    <span>✓ Semáforo de riesgo e incidencias del BORME</span>
                    <span>✓ Cancela en 1 clic cuando quieras</span>
                </div>
            </div>

            <div style="display: flex; flex-wrap: wrap; gap: 14px; align-items: stretch; justify-content: center;">
                <!-- TRIPWIRE: PACK 5 AUDITORÍAS -->
                <div style="background: #ffffff; border: 2px solid #6366f1; border-radius: 16px; padding: 20px; text-align: center; width: 230px; box-shadow: 0 10px 20px rgba(99, 102, 241, 0.12); display: flex; flex-direction: column; justify-content: space-between; position: relative;">
                    <div style="position: absolute; top: -11px; left: 50%; transform: translateX(-50%); background: #4f46e5; color: #fff; font-size: 0.65rem; font-weight: 800; padding: 2px 10px; border-radius: 999px; text-transform: uppercase; white-space: nowrap;">
                        ⚡ SIN SUSCRIPCIÓN
                    </div>
                    <div>
                        <div style="font-size: 0.74rem; font-weight: 800; color: #4338ca; text-transform: uppercase; margin-top: 4px;">Pack 5 Auditorías</div>
                        <div style="font-size: 1.8rem; font-weight: 900; color: #4338ca; margin: 4px 0 2px 0;">
                            <?= solvencia('precios.pack5', '9,90 €') ?> <span style="font-size: 0.75rem; font-weight: 600; color: #64748b;">+ IVA</span>
                        </div>
                        <?php // El 9,90 y el 1,98 estaban escritos a mano al lado del precio de Config. ?>
                        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 12px;"><strong><?= number_format(((int) solvencia('centimos.pack5', 990)) / 500, 2, ',', '.') ?> € / informe</strong> &bull; Sin caducidad</div>
                    </div>
                    <form method="post" action="<?= site_url('billing/checkout') ?>" style="margin: 0;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="plan" value="risk_pack_5">
                        <input type="hidden" name="period" value="single">
                        <button type="submit" style="width: 100%; background: #4f46e5; color: #ffffff; border: none; padding: 10px 14px; border-radius: 10px; font-weight: 800; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.25);" onmouseover="this.style.background='#4338ca';" onmouseout="this.style.background='#4f46e5';">
                            Comprar Pack 5 ⚡
                        </button>
                    </form>
                </div>

                <!-- MRR: SOLVENCIA PRO -->
                <div style="background: #ffffff; border: 2px solid #2563eb; border-radius: 16px; padding: 20px; text-align: center; width: 230px; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.12); display: flex; flex-direction: column; justify-content: space-between; position: relative;">
                    <div style="position: absolute; top: -11px; left: 50%; transform: translateX(-50%); background: #2563eb; color: #fff; font-size: 0.65rem; font-weight: 800; padding: 2px 10px; border-radius: 999px; text-transform: uppercase; white-space: nowrap;">
                        ⭐ TARIFA PLANA
                    </div>
                    <div>
                        <div style="font-size: 0.74rem; font-weight: 800; color: #1d4ed8; text-transform: uppercase; margin-top: 4px;">Solvencia Pro</div>
                        <div style="font-size: 1.8rem; font-weight: 900; color: #1d4ed8; margin: 4px 0 2px 0;">
                            <?= solvencia('precios.pro_mensual', '29 €') ?> <span style="font-size: 0.75rem; font-weight: 600; color: #64748b;">+ IVA</span>
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 12px;">Vigilancia del BORME de hasta <?= (int) solvencia('vigilanciasPro', 25) ?> empresas + informes en PDF</div>
                    </div>
                    <form method="post" action="<?= site_url('billing/checkout') ?>" style="margin: 0;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="plan" value="risk_pro">
                        <input type="hidden" name="period" value="monthly">
                        <button type="submit" style="width: 100%; background: #2563eb; color: #ffffff; border: none; padding: 10px 14px; border-radius: 10px; font-weight: 800; font-size: 0.85rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);" onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
                            Activar Pro ⭐
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>


</div>

<!-- MODAL COMPRA / DESCARGA PDF INTEGRADO -->
<?= view('partials/risk_pdf_modal', ['company' => []]) ?>

<script>
let isRiskSearching = false;

window.handleRiskSearchSubmit = function(e) {
    if (e && e.preventDefault) e.preventDefault();
    if (e && e.stopPropagation) e.stopPropagation();
    const input = document.getElementById('dashRiskCifInput');
    const val = input ? input.value.trim() : '';
    if (!val) {
        showRiskAlert('Por favor, introduce un CIF o nombre de empresa para auditar.');
        if (input) input.focus();
        return false;
    }
    lookupCifInDashboard(val);
    return false;
};

window.lookupCifInDashboard = function(cifOrQuery) {
    if (isRiskSearching) return;
    isRiskSearching = true;

    const btn = document.getElementById('dashRiskSearchBtn');
    const loading = document.getElementById('riskSearchLoading');
    const alertBox = document.getElementById('riskSearchAlert');
    const liveContainer = document.getElementById('dashRiskLiveContainer');
    const liveBody = document.getElementById('dashLiveReportBody');
    const liveName = document.getElementById('dashLiveCompanyName');
    const liveCif = document.getElementById('dashLiveCompanyCif');
    const livePdfBtn = document.getElementById('dashLiveBtnPdf');

    if (alertBox) alertBox.style.display = 'none';
    if (loading) loading.style.display = 'flex';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span>Auditando... ⏳</span>';
    }

    const endpoint = '<?= site_url("api/perfil-de-riesgo/lookup") ?>?cif=' + encodeURIComponent(cifOrQuery);

    fetch(endpoint, {
        method: 'GET',
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin'
    })
    .then(res => res.json())
    .then(data => {
        isRiskSearching = false;
        if (loading) loading.style.display = 'none';
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg><span>Analizar Solvencia</span>';
        }

        if (!data.success) {
            showRiskAlert(data.message || 'No se pudo procesar la empresa introducida.');
            return;
        }

        // Render live report in dashboard
        const comp = data.company || {};
        const compName = comp.name || comp.company_name || cifOrQuery;
        const compCif = comp.cif || '';
        const compId = parseInt(comp.id || 0);

        if (liveName) liveName.textContent = compName;
        if (liveCif) liveCif.textContent = 'CIF: ' + compCif;
        if (livePdfBtn) {
            livePdfBtn.setAttribute('onclick', 'downloadRiskPdf(' + compId + ', "' + compCif + '");');
        }

        if (liveBody) {
            liveBody.innerHTML = data.html || '<div style="padding:20px; text-align:center;">Dictamen generado correctamente.</div>';
        }

        if (liveContainer) {
            liveContainer.style.display = 'block';
            liveContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        // Update local audit row in history if table exists
        updateHistoryTableWithAudited(comp, data);
        updateKpiCounters(data.riskQuota);
    })
    .catch(err => {
        console.error(err);
        isRiskSearching = false;
        if (loading) loading.style.display = 'none';
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg><span>Analizar Solvencia</span>';
        }
        showRiskAlert('Error de conexión al consultar el motor de riesgo. Inténtalo de nuevo.');
    });
};

window.downloadRiskPdf = function(companyId, cif) {
    const target = (companyId && parseInt(companyId) > 0) ? parseInt(companyId) : encodeURIComponent(cif || '');
    if (!target) {
        showRiskAlert('No se pudo identificar la empresa para la descarga del PDF.');
        return;
    }
    window.location.href = '<?= site_url("empresa/export-risk/") ?>' + target;
};

window.closeDashLiveReport = function() {
    const liveContainer = document.getElementById('dashRiskLiveContainer');
    if (liveContainer) {
        liveContainer.style.display = 'none';
    }
};

function showRiskAlert(msg) {
    const alertBox = document.getElementById('riskSearchAlert');
    if (alertBox) {
        alertBox.textContent = msg;
        alertBox.style.display = 'block';
    } else {
        alert(msg);
    }
}

function updateKpiCounters(quota) {
    if (!quota) return;
    const viewsVal = document.getElementById('kpiViewsUsedVal');
    const progressBar = document.getElementById('kpiViewsProgressBar');
    const viewsDesc = document.getElementById('kpiViewsDesc');
    const auditsCount = document.getElementById('kpiAuditsCount');
    const auditsBadge = document.getElementById('auditsCountBadge');

    if (viewsVal && typeof quota.views_used !== 'undefined') {
        viewsVal.textContent = quota.views_used;
    }
    if (progressBar && typeof quota.views_used !== 'undefined') {
        const used = parseInt(quota.views_used);
        const pct = Math.min(100, Math.round((used / 3) * 100));
        progressBar.style.width = pct + '%';
        progressBar.style.background = pct >= 100 ? '#ef4444' : (pct >= 66 ? '#f59e0b' : '#3b82f6');
    }
    if (viewsDesc && typeof quota.views_used !== 'undefined') {
        const used = parseInt(quota.views_used);
        if (used >= 3) {
            viewsDesc.innerHTML = '<span style="color: #dc2626; font-weight: 700;">⚠️ Has alcanzado el límite mensual.</span>';
        } else {
            const rem = 3 - used;
            viewsDesc.innerHTML = 'Te ' + (rem === 1 ? 'queda' : 'quedan') + ' <strong>' + rem + ' ' + (rem === 1 ? 'consulta gratis' : 'consultas gratis') + '</strong> este mes.';
        }
    }
    if (auditsCount) {
        const current = parseInt(auditsCount.textContent || '0');
        auditsCount.textContent = current + 1;
    }
    if (auditsBadge) {
        const current = parseInt(auditsBadge.textContent || '0');
        const next = current + 1;
        auditsBadge.textContent = next + (next === 1 ? ' empresa guardada' : ' empresas guardadas');
    }
}

function updateHistoryTableWithAudited(comp, data) {
    const emptyState = document.getElementById('dashEmptyAudits');
    const tableContainer = document.getElementById('dashAuditsTableContainer');
    const tbody = document.getElementById('dashAuditsTbody');

    if (emptyState) emptyState.style.display = 'none';
    if (tableContainer) tableContainer.style.display = 'block';

    if (!tbody || !comp.cif) return;

    // Check if row already exists
    const existingRow = document.getElementById('row-cif-' + comp.cif);
    if (existingRow) {
        tbody.prepend(existingRow);
        return;
    }

    const tr = document.createElement('tr');
    tr.id = 'row-cif-' + comp.cif;
    tr.innerHTML = `
        <td>
            <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;">
                <a href="javascript:void(0);" onclick="lookupCifInDashboard('${comp.cif}');" style="color: inherit; text-decoration: none;" onmouseover="this.style.color='#2563eb';" onmouseout="this.style.color='#0f172a';">
                    ${comp.name || comp.company_name || comp.cif}
                </a>
            </div>
            <div style="font-size: 0.8rem; color: #64748b; display: flex; align-items: center; gap: 8px; margin-top: 2px;">
                <span style="font-weight: 600; color: #334155;">${comp.cif}</span>
            </div>
        </td>
        <td>
            <div class="risk-score-badge risk-score-medio">
                <span>🟡</span>
                <span>AUDITADO</span>
            </div>
        </td>
        <td>
            <span style="color: #059669; font-size: 0.82rem; font-weight: 600;">✓ Consultado hoy</span>
        </td>
        <td style="color: #64748b; font-size: 0.85rem;">
            Justo ahora
        </td>
        <td style="text-align: right;">
            <div style="display: inline-flex; gap: 8px; align-items: center;">
                <button type="button" onclick="lookupCifInDashboard('${comp.cif}');" style="background: #eff6ff; color: #1d4ed8; border: none; padding: 7px 12px; border-radius: 8px; font-weight: 700; font-size: 0.82rem; cursor: pointer;">
                    Ver Dictamen ➔
                </button>
                <button type="button" onclick="downloadRiskPdf(${parseInt(comp.id || 0)}, '${comp.cif}');" style="background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" title="Descargar el informe en PDF">
                    <span>PDF 📄</span>
                </button>
            </div>
        </td>
    `;
    tbody.prepend(tr);
}

// Auto-lookup if initial CIF is present
document.addEventListener('DOMContentLoaded', function() {
    <?php if (!empty($initialCif)): ?>
        setTimeout(function() {
            lookupCifInDashboard('<?= esc($initialCif, 'js') ?>');
        }, 150);
    <?php endif; ?>
});
</script>

<?= $this->endSection() ?>
