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
                Supervisa la estabilidad de clientes y proveedores, consulta actos BORME y previene impagos con datos oficiales.
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
                        Activar Solvencia Pro (29€) ⭐
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
                        Dispones de acceso ilimitado al motor de riesgo y dictámenes descargables de cualquier CIF en España.
                    <?php else: ?>
                        Dispones de <strong>3 consultas completas gratuitas</strong> al mes para analizar a cualquier empresa.
                    <?php endif; ?>
                </p>
            </div>
            <div style="margin-top: 14px; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                <?php if (!empty($isSubscriber)): ?>
                    <span style="color: #059669; font-size: 0.82rem; font-weight: 700;">✅ Cobertura Ilimitada</span>
                <?php else: ?>
                    <a href="#solvencia-pro-banner" style="color: #2563eb; font-size: 0.82rem; font-weight: 800; text-decoration: none;">Pasar a Ilimitado (29€/mes) &rarr;</a>
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
                        <span style="color: #059669;">Ilimitado</span>
                    <?php else: ?>
                        <span id="kpiViewsUsedVal"><?= (int)$viewsUsed ?></span> <span style="font-size: 1.1rem; color: #94a3b8; font-weight: 700;">/ 3 empresas</span>
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
                    $pct = min(100, round(($viewsUsed / 3) * 100)); 
                    $barColor = $pct >= 100 ? ($userCredits > 0 ? '#4f46e5' : '#ef4444') : ($pct >= 66 ? '#f59e0b' : '#3b82f6');
                    ?>
                    <div style="width: 100%; height: 8px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin-bottom: 8px;">
                        <div id="kpiViewsProgressBar" style="width: <?= $pct ?>%; height: 100%; background: <?= $barColor ?>; border-radius: 999px; transition: width 0.4s ease;"></div>
                    </div>
                    <p class="risk-kpi-desc" id="kpiViewsDesc">
                        <?php if ($viewsUsed >= 3): ?>
                            <?php if ($userCredits > 0): ?>
                                <span style="color: #4f46e5; font-weight: 700;">Cuota mensual agotada.</span> Usando tu Pack de auditorías prepagadas.
                            <?php else: ?>
                                <span style="color: #dc2626; font-weight: 700;">⚠️ Has alcanzado el límite mensual.</span> Se renovará el <?= esc($nextCycleDate) ?>.
                            <?php endif; ?>
                        <?php else: ?>
                            Te <?= (3 - $viewsUsed === 1) ? 'queda' : 'quedan' ?> <strong><?= (3 - $viewsUsed) ?> <?= (3 - $viewsUsed === 1) ? 'consulta gratis' : 'consultas gratis' ?></strong> este mes.
                        <?php endif; ?>
                    </p>
                <?php else: ?>
                    <p class="risk-kpi-desc" style="color: #059669; font-weight: 600;">
                        Sin límites mensuales. Puedes consultar cualquier CIF tantas veces como necesites.
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
                            <th>Nivel de Riesgo</th>
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
                            <th>Nivel de Riesgo</th>
                            <th>Alertas BORME</th>
                            <th>Última Consulta</th>
                            <th style="text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="dashAuditsTbody">
                        <?php foreach ($audits as $item): ?>
                            <?php 
                            $lvl = strtoupper($item['risk_level'] ?? 'MEDIO');
                            if ($lvl === 'BAJO') {
                                $badgeClass = 'risk-score-bajo';
                                $badgeIcon = '🟢';
                            } elseif ($lvl === 'ALTO') {
                                $badgeClass = 'risk-score-alto';
                                $badgeIcon = '🔴';
                            } else {
                                $badgeClass = 'risk-score-medio';
                                $badgeIcon = '🟡';
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
                                        <span><?= esc($lvl) ?> (<?= (int)$item['risk_score'] ?>/100)</span>
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
                                        <button type="button" onclick="downloadRiskPdf(<?= (int)$item['company_id'] ?>, '<?= esc($item['cif']) ?>');" style="background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 4px; transition: all 0.2s;" onmouseover="this.style.background='#f8fafc';" onmouseout="this.style.background='#ffffff';" title="Descargar Dictamen Oficial en PDF">
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
                    ⭐ Tarifa Plana Sin Límites
                </div>
                <h3 style="font-size: 1.55rem; font-weight: 900; color: #0f172a; margin: 0 0 10px 0; letter-spacing: -0.4px;">
                    ¿Necesitas auditar múltiples empresas al mes?
                </h3>
                <p style="color: #334155; margin: 0 0 16px 0; font-size: 0.95rem; line-height: 1.5;">
                    Frente a proveedores tradicionales que cobran <strong>25 € a 35 € por cada informe individual</strong>, con <strong>Solvencia Pro</strong> disfrutas de auditorías mercantiles y dictámenes ejecutivos ilimitados de toda España por solo <strong>29 € / mes</strong>.
                </p>
                <div style="display: flex; flex-wrap: wrap; gap: 16px; font-size: 0.88rem; color: #1e3a8a; font-weight: 700;">
                    <span>✓ Consultas ilimitadas de cualquier CIF</span>
                    <span>✓ Semáforo oficial e incidencias BORME</span>
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
                            9,90 € <span style="font-size: 0.75rem; font-weight: 600; color: #64748b;">+ IVA</span>
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 12px;"><strong>1,98 € / informe</strong> &bull; Sin caducidad</div>
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
                            29 € <span style="font-size: 0.75rem; font-weight: 600; color: #64748b;">+ IVA</span>
                        </div>
                        <div style="font-size: 0.75rem; color: #64748b; margin-bottom: 12px;">Consultas y PDFs ilimitados / mes</div>
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
                <button type="button" onclick="downloadRiskPdf(${parseInt(comp.id || 0)}, '${comp.cif}');" style="background: #ffffff; color: #0f172a; border: 1px solid #cbd5e1; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 0.82rem; cursor: pointer; display: inline-flex; align-items: center; gap: 4px;" title="Descargar Dictamen Oficial en PDF">
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
