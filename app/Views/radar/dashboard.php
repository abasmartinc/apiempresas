<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Radar B2B - Centro de Prospección Inteligente',
        'excerptText' => 'Identifica nuevas oportunidades de negocio en tiempo real con el Radar de APIEmpresas.',
    ]) ?>

    <!-- Leaflet.js for Radar Map -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

    <link rel="stylesheet" href="<?= base_url('public/css/radar.css?v=' . (file_exists(FCPATH . 'public/css/radar.css') ? filemtime(FCPATH . 'public/css/radar.css') : time())) ?>">
    
    <!-- Radar Web Tour -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.css" />
    <link rel="stylesheet" href="<?= base_url('public/css/radar-tour.css?v=' . time()) ?>" />
</head>
<body>

<?php
// isFree ya viene del controlador
$formatEsDate = function($dateStr, $format = 'd M Y') {
    if (empty($dateStr) || $dateStr === '0000-00-00') return 'Reciente';
    $timestamp = strtotime($dateStr);
    if (!$timestamp) return 'Reciente';
    if ($timestamp > time()) return 'Reciente';
    $mesesEn = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
    $mesesEs = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    return str_replace($mesesEn, $mesesEs, date($format, $timestamp));
};

$allCompanies = $companies ?? [];
$limitFree = 3; 
$visibleCompanies = $isFree ? array_slice($allCompanies, 0, $limitFree) : $allCompanies;
?>

<div class="ae-radar-page">
    <div class="ae-radar-page__shell">

        <?= view('radar/partials/sidebar') ?>

        <main class="ae-radar-page__main">
            
            <?php 
            $source = service('request')->getGet('source');
            if (session('just_bought_excel') || $source === 'excel'): 
            ?>
            <div class="radar-welcome-banner" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); padding: 24px; border-radius: 16px; margin-bottom: 32px; border: 1px solid rgba(255,255,255,0.1); position: relative; overflow: hidden;">
                <div style="position: relative; z-index: 2; display: flex; align-items: center; justify-content: space-between; gap: 24px;">
                    <div>
                        <h2 style="color: #fff; font-size: 1.4rem; font-weight: 800; margin-bottom: 8px;">¡Bienvenido al Radar PRO!</h2>
                        <p style="color: #94a3b8; font-size: 0.95rem; line-height: 1.5; margin: 0;">
                            <?php if ($source === 'excel'): ?>
                                Estas oportunidades son <strong>nuevas</strong> respecto al listado que descargaste. <br>
                                Monitoriza el mercado en tiempo real para no llegar tarde.
                            <?php else: ?>
                                Ya tienes tu listado, pero el mercado no se detiene. <br>
                                Aquí puedes ver las empresas que se han creado <strong>hoy mismo</strong> en tu sector.
                            <?php endif; ?>
                        </p>
                    </div>
                    <button onclick="this.parentElement.parentElement.style.display='none';" style="background: rgba(255,255,255,0.1); border: none; color: #fff; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 700; white-space: nowrap;"> Entendido </button>
                </div>
            </div>
            <?php session()->remove('just_bought_excel'); ?>
            <?php endif; ?>

            <header class="ae-radar-page__topbar">
                <div class="ae-radar-page__breadcrumb">
                    <span>APIEmpresas</span>
                    <span>/</span>
                    <strong>Radar B2B</strong>
                </div>

                <div class="ae-radar-page__topbar-actions">
                    <div class="ae-radar-page__freshness">
                        <span class="ae-radar-page__freshness-dot"></span>
                        Última actualización: <strong><?= $freshness['lastUpdate'] ?></strong> 
                        <span class="ae-radar-page__freshness-sep">|</span>
                        Hoy: <strong>+<?= number_format($freshness['todayCount']) ?></strong> empresas
                    </div>

                    <?php if ($isFree) { ?>
                        <div class="ae-radar-page__pill ae-radar-page__pill--free">
                            Plan Free · Vista limitada
                        </div>
                    <?php } else { ?>
                        <?php if (isset($userPlan['status']) && $userPlan['status'] === 'canceled') { ?>
                            <a href="<?= site_url('billing') ?>" class="ae-radar-page__pill ae-radar-page__pill--live" style="text-decoration:none; background:#fef2f2; border:1px solid #fee2e2; color:#ef4444;" title="Gestionar facturación">
                                <span class="ae-radar-page__pulse" style="background:#ef4444;"></span>
                                Cancelada (Acceso hasta <?= date('d/m/Y', strtotime($userPlan['period_end'])) ?>)
                            </a>
                        <?php } else { ?>
                            <a href="<?= site_url('billing') ?>" class="ae-radar-page__pill ae-radar-page__pill--live" style="text-decoration:none;" title="Gestionar facturación">
                                <span class="ae-radar-page__pulse"></span>
                                Suscripción activa
                            </a>
                            <button type="button" class="ae-radar-page__cta-top" style="background:transparent; border:1px solid #ef4444; color:#ef4444; cursor:pointer;" onclick="cancelRadarSubscription()">
                                Cancelar PRO
                            </button>
                        <?php } ?>
                    <?php } ?>
                </div>
            </header>

            <style>
                .ae-radar-page { background: #e2e8f0 !important; }
                .ae-radar-page__content { background: transparent !important; padding: 40px !important; }
                .ae-radar-page__container { max-width: 100% !important; margin: 0 auto !important; }
                
                /* ── Radar PRO Redesign ── */
                .ae-pro-crm-bar {
                    display: flex; align-items: center; gap: 24px;
                    background: #fff; border: 1px solid #e2e8f0;
                    border-radius: 14px; padding: 14px 24px;
                    margin-bottom: 20px;
                    box-shadow: 0 1px 4px rgba(0,0,0,0.04);
                }
                .ae-pro-crm-bar__stat { display: flex; align-items: center; gap: 10px; }
                .ae-pro-crm-bar__num { font-size: 20px; font-weight: 900; color: #0f172a; line-height: 1; }
                .ae-pro-crm-bar__label { font-size: 11px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.05em; margin-top: 2px; }
                .ae-pro-crm-bar__sep { width: 1px; height: 32px; background: #f1f5f9; flex-shrink: 0; }
                .ae-pro-crm-bar__right { margin-left: auto; display: flex; align-items: center; gap: 10px; }

                .ae-pro-search-hero {
                    background: #fff; border: 1px solid #e2e8f0;
                    border-radius: 20px; padding: 24px 32px;
                    margin-bottom: 12px;
                    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
                }
                .ae-pro-search-hero__label {
                    font-size: 11px; font-weight: 800; text-transform: uppercase;
                    letter-spacing: 0.08em; color: #94a3b8; margin-bottom: 14px;
                    display: flex; align-items: center; gap: 8px;
                }
                .ae-pro-search-hero__input {
                    width: 100%; height: 58px; padding: 0 60px 0 24px;
                    border-radius: 14px; border: 2px solid #e2e8f0;
                    background: #f8fafc; font-size: 15px; font-weight: 500;
                    color: #1e293b; transition: all 0.2s;
                    outline: none;
                }
                .ae-pro-search-hero__input:focus {
                    border-color: #2563eb; background: #fff;
                    box-shadow: 0 0 0 4px rgba(37,99,235,0.08);
                }
                .ae-pro-search-hero__btn {
                    height: 58px; padding: 0 28px;
                    background: #0f172a; color: #fff;
                    border-radius: 14px; border: none;
                    font-weight: 800; font-size: 14px;
                    cursor: pointer; display: flex; align-items: center; gap: 10px;
                    transition: background 0.2s; white-space: nowrap;
                }
                .ae-pro-search-hero__btn:hover { background: #1e293b; }

                .ae-pro-filters-toggle {
                    display: flex; align-items: center; gap: 8px;
                    font-size: 13px; font-weight: 700; color: #475569;
                    background: none; border: none; cursor: pointer;
                }

                .ae-pro-filters-panel {
                    background: #fff; border: 1px solid #e2e8f0;
                    border-radius: 16px; padding: 20px 24px;
                    margin-bottom: 16px;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.03);
                }

                .ae-pro-chips { display: flex; gap: 8px; flex-wrap: wrap; margin-bottom: 20px; }
                .ae-pro-chip {
                    display: inline-flex; align-items: center; gap: 6px;
                    height: 36px; padding: 0 16px;
                    font-size: 12px; font-weight: 700;
                    border-radius: 10px; border: 1px solid #e2e8f0;
                    text-decoration: none; color: #475569; background: #fff;
                    transition: all 0.15s;
                }
                .ae-pro-chip:hover { border-color: #2563eb; color: #2563eb; }
                .ae-pro-chip.is-active { background: #2563eb; color: #fff; border-color: #2563eb; }

                .ae-ai-example {
                    background: #f1f5f9; border: 1px solid #e2e8f0;
                    padding: 5px 12px; border-radius: 8px;
                    font-size: 11px; font-weight: 600; color: #475569;
                    cursor: pointer; transition: all 0.15s; border: none;
                }
                .ae-ai-example:hover { background: #e2e8f0; }
                
                .ae-status-select-chip.status-bg-nuevo { background-color: #f1f5f9; color: #64748b; }
                .ae-status-select-chip.status-bg-contactado { background-color: #fff7ed; color: #ea580c; border-color: #ffedd5; }
                .ae-status-select-chip.status-bg-seguimiento { background-color: #eff6ff; color: #2563eb; border-color: #dbeafe; }
                .ae-status-select-chip.status-bg-negociacion { background-color: #faf5ff; color: #9333ea; border-color: #f3e8ff; }
                .ae-status-select-chip.status-bg-ganado { background-color: #f0fdf4; color: #16a34a; border-color: #dcfce7; }
                
                @keyframes ae-slide-in {
                    from { opacity: 0; transform: translateY(-10px); }
                    to { opacity: 1; transform: translateY(0); }
                }
                .ae-shine-btn {
                    position: relative;
                    overflow: hidden;
                }
                .ae-shine-btn::after {
                    content: "";
                    position: absolute;
                    top: -50%;
                    left: -50%;
                    width: 200%;
                    height: 200%;
                    background: linear-gradient(
                        to bottom right,
                        rgba(255, 255, 255, 0) 0%,
                        rgba(255, 255, 255, 0) 40%,
                        rgba(255, 255, 255, 0.4) 50%,
                        rgba(255, 255, 255, 0) 60%,
                        rgba(255, 255, 255, 0) 100%
                    );
                    transform: rotate(45deg);
                    animation: ae-shine 3s infinite;
                }
                @keyframes ae-shine {
                    0% { transform: translateX(-100%) rotate(45deg); }
                    100% { transform: translateX(100%) rotate(45deg); }
                }
            </style>

            <div class="ae-radar-page__content">
                <div class="ae-radar-page__container">

                    <?php if (!$isFree): ?>

                    <!-- ① MINI CRM BAR -->
                    <div class="ae-pro-crm-bar">
                        <div class="ae-pro-crm-bar__stat">
                            <div>
                                <div class="ae-pro-crm-bar__num"><?= number_format($crmStats['contactado'] ?? 0) ?></div>
                                <div class="ae-pro-crm-bar__label">Contactadas</div>
                            </div>
                        </div>
                        <div class="ae-pro-crm-bar__sep"></div>
                        <div class="ae-pro-crm-bar__stat">
                            <div>
                                <div class="ae-pro-crm-bar__num"><?= number_format($crmStats['seguimiento'] ?? 0) ?></div>
                                <div class="ae-pro-crm-bar__label">En seguimiento</div>
                            </div>
                        </div>
                        <div class="ae-pro-crm-bar__sep"></div>
                        <div class="ae-pro-crm-bar__stat">
                            <div>
                                <div class="ae-pro-crm-bar__num" style="color:#2563eb;"><?= number_format($crmStats['nuevo'] ?? 0) ?></div>
                                <div class="ae-pro-crm-bar__label">Sin contactar</div>
                            </div>
                        </div>
                        <div class="ae-pro-crm-bar__sep"></div>
                        <div class="ae-pro-crm-bar__stat">
                            <div>
                                <div class="ae-pro-crm-bar__num" style="color:#10b981;"><?= number_format($stats['hoy']) ?></div>
                                <div class="ae-pro-crm-bar__label">Nuevas hoy</div>
                            </div>
                        </div>
                        <div class="ae-pro-crm-bar__right">
                            <span style="font-size:10px; font-weight:700; color:#94a3b8; display:flex; align-items:center; gap:5px;">
                                <span style="width:6px;height:6px;background:#22c55e;border-radius:50%;display:block;"></span>
                                En vivo · <?= $freshness['lastUpdate'] ?>
                            </span>
                            <button class="js-start-radar-tour" style="display:flex;align-items:center;gap:5px;padding:6px 14px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:8px;color:#475569;font-size:11px;font-weight:700;cursor:pointer;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" style="width:12px;height:12px;"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                                Guía
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- ② BÚSQUEDA IA (hero) -->
                    <div class="ae-pro-search-hero <?= $isFree ? 'is-locked' : '' ?>" style="<?= $isFree ? 'border-style: dashed; background: #fafafa;' : '' ?>">
                        <div class="ae-pro-search-hero__label">
                            <svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" style="width:14px;height:14px;"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                            Búsqueda con IA
                            <span style="background:<?= $isFree ? '#fff1f2' : '#dbeafe' ?>;color:<?= $isFree ? '#e11d48' : '#2563eb' ?>;font-size:9px;font-weight:900;padding:2px 7px;border-radius:999px;letter-spacing:0.05em;display:flex;align-items:center;gap:4px;">
                                <?php if ($isFree): ?>
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                <?php endif; ?>
                                <?= $isFree ? 'PRO ONLY' : 'BETA' ?>
                            </span>
                        </div>
                        <div style="display:flex;gap:12px;align-items:center;">
                            <div style="position:relative;flex-grow:1;">
                                <input type="text" id="radar-ai-query"
                                    class="ae-pro-search-hero__input"
                                    <?= $isFree ? 'style="opacity:0.7;"' : '' ?>
                                    placeholder="Ej: Empresas nuevas de construcción en Madrid con score alto..."
                                    onkeypress="if(event.key==='Enter') handleAiSearch()">
                                <div style="position:absolute;right:18px;top:50%;transform:translateY(-50%);color:#94a3b8;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                                </div>
                            </div>
                            <button onclick="handleAiSearch()" id="btn-ai-search" class="ae-pro-search-hero__btn" style="background: #1e293b; box-shadow: 0 4px 12px rgba(30, 41, 59, 0.15);">
                                <span id="btn-ai-search-text">Buscar con IA</span>
                                <div id="btn-ai-search-loader" class="ae-spinner ae-spinner--white" style="display:none;width:16px;height:16px;border-width:2px;"></div>
                            </button>
                        </div>
                        <div style="margin-top:14px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                            <span style="font-size:11px;font-weight:700;color:#94a3b8;">Ejemplos:</span>
                            <button class="ae-ai-example" onclick="fillAiQuery('Empresas nuevas en Madrid con score alto')">Empresas nuevas en Madrid con score alto</button>
                            <button class="ae-ai-example" onclick="fillAiQuery('Leads recientes de construcción en Valencia')">Leads recientes de construcción en Valencia</button>
                            <button class="ae-ai-example" onclick="fillAiQuery('Empresas que puedan necesitar software de facturación')">Empresas que puedan necesitar software de facturación</button>
                        </div>
                        <div id="ai-search-explanation" style="display:none;margin-top:16px;padding:16px 20px;background:linear-gradient(to right, #eff6ff, #f8fafc);border-radius:12px;border:1px solid #dbeafe;align-items:flex-start;gap:12px;animation:ae-slide-in 0.4s ease-out;">
                            <span style="font-size:18px;filter:drop-shadow(0 2px 4px rgba(0,0,0,0.1));">💡</span>
                            <div style="flex-grow:1;">
                                <p id="ai-explanation-text" style="margin:0;font-size:13px;color:#1e40af;font-weight:600;line-height:1.6;"></p>
                                <button onclick="clearAiSearch()" style="margin-top:8px;background:transparent;border:none;color:#3b82f6;font-size:12px;font-weight:700;padding:0;cursor:pointer;text-decoration:underline;opacity:0.8;transition:opacity 0.2s;" onmouseover="this.style.opacity=1" onmouseout="this.style.opacity=0.8">Limpiar búsqueda IA</button>
                            </div>
                        </div>
                    </div>

                    <?php if (!$isFree): ?>

                    <!-- ③ FILTROS + CHIPS -->
                    <div style="margin-bottom:20px;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                            <div class="ae-pro-chips">
                                <?php
                                $activeFilter = 'todas';
                                if (isset($_GET['intel']) && $_GET['intel'] === 'active') $activeFilter = 'mejores';
                                elseif (isset($_GET['status']) && $_GET['status'] === 'nuevo') $activeFilter = 'sin_ver';
                                elseif (($filters['rango'] ?? '') === '7') $activeFilter = 'ventana';
                                $chips = [
                                    'todas'   => ['label' => 'Todas', 'icon' => '📊', 'url' => site_url('radar')],
                                    'sin_ver' => ['label' => 'Sin contactar', 'icon' => '🆕', 'url' => site_url('radar?' . http_build_query(array_merge($filters, ['status' => 'nuevo', 'intel' => null])))],
                                    'ventana' => ['label' => 'Esta semana', 'icon' => '⏱', 'url' => site_url('radar?' . http_build_query(array_merge($filters, ['rango' => '7', 'intel' => null])))],
                                    'mejores' => ['label' => 'Score alto', 'icon' => '🔥', 'url' => site_url('radar?' . http_build_query(array_merge($filters, ['min_score' => 75, 'intel' => 'active'])))],
                                ];
                                foreach ($chips as $key => $chip):
                                    $active = ($activeFilter === $key) ? 'is-active' : '';
                                ?>
                                    <a href="<?= $chip['url'] ?>" class="ae-pro-chip <?= $active ?>">
                                        <?= $chip['icon'] ?> <?= $chip['label'] ?>
                                    </a>
                                <?php endforeach; ?>
                            </div>

                            <button type="button" class="ae-pro-filters-toggle" onclick="toggleFilters()">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="width:14px;height:14px;"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="10" y1="18" x2="14" y2="18"/></svg>
                                Filtros avanzados
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" id="filters-chevron" style="width:14px;height:14px;transition:transform 0.2s;"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                        </div>

                        <div id="ae-filters-panel" class="ae-pro-filters-panel" style="<?= (!empty($filters['q']) || !empty($filters['provincia']) || $filters['rango'] !== 'hoy') ? '' : 'display:none;' ?>">
                            <form action="<?= site_url('radar') ?>" method="GET" class="ae-radar-page__filters">
                                <div class="ae-radar-page__filters-grid" style="display:grid; grid-template-columns: 1fr 1fr 1fr auto; gap: 16px; align-items: flex-end;">
                                    <div class="ae-radar-page__field">
                                        <label style="font-size:11px;margin-bottom:6px;display:block;">Nicho / Palabras clave</label>
                                        <input type="text" name="q" class="ae-radar-page__input" style="height:38px;font-size:13px;" placeholder="Ej: energía solar..." value="<?= esc($filters['q'] ?? '') ?>">
                                    </div>
                                    <div class="ae-radar-page__field">
                                        <label style="font-size:11px;margin-bottom:6px;display:block;">Provincia</label>
                                        <select name="provincia" class="ae-radar-page__select" style="height:38px;font-size:13px;width:100%;">
                                            <option value="">Toda España</option>
                                            <?php foreach ($provinces as $p): ?>
                                                <option value="<?= url_title($p['name'], '-', true) ?>" <?= ($filters['provincia'] === url_title($p['name'], '-', true)) ? 'selected' : '' ?>>
                                                    <?= esc($p['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="ae-radar-page__field">
                                        <label style="font-size:11px;margin-bottom:6px;display:block;">Ventana temporal</label>
                                        <select name="rango" class="ae-radar-page__select" style="height:38px;font-size:13px;width:100%;">
                                            <option value="hoy" <?= ($filters['rango'] === 'hoy') ? 'selected' : '' ?>>Hoy mismo</option>
                                            <option value="7" <?= ($filters['rango'] === '7') ? 'selected' : '' ?>>Últimos 7 días</option>
                                            <option value="30" <?= ($filters['rango'] === '30') ? 'selected' : '' ?>>Últimos 30 días</option>
                                            <option value="90" <?= ($filters['rango'] === '90') ? 'selected' : '' ?>>Últimos 90 días</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="ae-radar-page__filters-cta" style="height:38px;font-size:13px;padding:0 20px;">
                                        Aplicar
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- ④ RESULTADOS -->
                    <section class="ae-radar-page__lead-wrap">
                        <div id="radar-results-container">
                            <?= view('radar/partials/results_table', array_merge($filters, [
                                'companies'  => $visibleCompanies,
                                'isFree'     => false,
                                'pagination' => $pagination ?? null,
                                'pager'      => $pager ?? null,
                                'filters'    => $filters ?? []
                            ])) ?>
                        </div>
                        <div id="radar-map-view" style="display:none;">
                            <div id="radar-leaflet-map" style="height:600px;width:100%;border-radius:20px;border:1px solid #e2e8f0;z-index:1;"></div>
                        </div>
                    </section>

                    <?php else: ?>
                    <!-- USUARIO FREE -->
                    <div class="ae-usage-counter" style="margin-bottom:24px;background:linear-gradient(to right, #fff, #f8fafc);border-radius:16px;padding:24px 32px;border:1px solid #e2e8f0;display:flex;align-items:center;justify-content:space-between;box-shadow: 0 4px 15px rgba(0,0,0,0.02);">
                        <div>
                            <div style="display:flex; align-items:center; gap:8px; margin-bottom:4px;">
                                <span style="background:#fef2f2; color:#ef4444; font-size:10px; font-weight:800; padding:2px 8px; border-radius:999px; text-transform:uppercase;">Plan Gratuito</span>
                                <h3 style="margin:0;font-size:16px;font-weight:800;color:#1e293b;">Has desbloqueado 3 de <?= number_format($pagination['total']) ?> leads hoy</h3>
                            </div>
                            <p style="margin:0;font-size:13px;color:#64748b;">Los datos están difuminados a partir del 3er resultado. Actualiza a PRO para acceso total.</p>
                        </div>
                        <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar&source=' . esc($source)) ?>" class="ae-radar-page__cta-top ae-shine-btn" style="background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); color:#fff; box-shadow: 0 4px 20px rgba(124, 58, 237, 0.4); border:none; padding: 12px 24px; border-radius: 12px; font-weight: 900; display: flex; align-items: center; gap: 8px;">
                            <span>👑</span> Desbloquear todo ahora
                        </a>
                    </div>

                    <!-- Contenedor de resultados -->
                    <div style="position:relative;background:#fff;padding:20px;border-radius:24px;border:1px solid #e1e7f0;margin-bottom:40px;box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
                        <section class="ae-radar-page__lead-wrap">
                            <div id="radar-results-container">
                                <?= view('radar/partials/results_table', array_merge($filters, [
                                    'companies'  => $allCompanies,
                                    'isFree'     => true,
                                    'pagination' => $pagination ?? null,
                                    'pager'      => $pager ?? null,
                                    'filters'    => $filters ?? []
                                ])) ?>
                            </div>
                        </section>
                    </div>

                    <?php endif; ?>

                </div>
            </div>

            <footer class="ae-radar-page__footer">
                &copy; <?= date('Y') ?> APIEmpresas · Inteligencia comercial B2B
            </footer>
        </main>
    </div>
</div>

    <!-- Modal QuickView -->
    <div id="ae-qv-modal" class="ae-qv-modal" style="display:none;">
        <div class="ae-qv-modal__backdrop" onclick="closeQuickView()"></div>
        <div class="ae-qv-modal__container">
            <div id="ae-qv-content" class="ae-qv-modal__content">
            </div>
        </div>
    </div>

    <?= view('radar/partials/ai_modal') ?>

    <!-- Modal Cancelación PRO (Retention Modal) -->
    <div id="radar-cancel-modal" class="ae-modal-v3" style="display:none;">
        <div class="ae-modal-v3__backdrop" onclick="closeCancelModal()"></div>
        <div class="ae-modal-v3__content" style="max-width: 440px;">
            <div style="text-align: center; padding: 20px 0 10px;">
                <div style="width: 64px; height: 64px; background: #fef2f2; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#ef4444" stroke-width="2.5" style="width: 32px; height: 32px;"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                </div>
                <h3 style="font-size: 20px; font-weight: 900; color: #1e293b; margin-bottom: 12px; letter-spacing: -0.02em;">¿Seguro que quieres cancelar?</h3>
                <p style="font-size: 14px; color: #64748b; line-height: 1.6; margin-bottom: 30px;">
                    Perderás el acceso a la <strong>Búsqueda IA</strong>, los filtros avanzados y la detección de leads en tiempo real. 
                    Las oportunidades no esperan.
                </p>
                <div style="display: flex; flex-direction: column; gap: 10px;">
                    <button onclick="closeCancelModal()" style="background: #0f172a; color: white; border: none; padding: 14px; border-radius: 12px; font-weight: 800; font-size: 14px; cursor: pointer; transition: transform 0.2s;">
                        Mantener mi acceso PRO
                    </button>
                    <button onclick="confirmRadarCancellation()" style="background: transparent; color: #ef4444; border: 1px solid #fee2e2; padding: 12px; border-radius: 12px; font-weight: 700; font-size: 13px; cursor: pointer;">
                        Sí, cancelar suscripción
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Upgrade PRO (Conversion Nudge) -->
    <div id="radar-upgrade-modal" class="ae-modal-v3" style="display:none;">
        <div class="ae-modal-v3__backdrop" onclick="closeUpgradeModal()"></div>
        <div class="ae-modal-v3__content" style="max-width: 500px; padding: 0; overflow: hidden; border: none;">
            <div style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); padding: 40px 32px; text-align: center; color: white; position: relative;">
                <div style="position: absolute; top: 20px; right: 20px; cursor: pointer; opacity: 0.5;" onclick="closeUpgradeModal()">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                </div>
                <div style="width: 80px; height: 80px; background: rgba(37, 99, 235, 0.2); border-radius: 20px; display: flex; align-items: center; justify-content: center; margin: 0 auto 24px; border: 1px solid rgba(255,255,255,0.1);">
                    <svg viewBox="0 0 24 24" fill="none" stroke="#60a5fa" stroke-width="2" style="width: 40px; height: 40px;"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                </div>
                <h3 id="upgrade-modal-title" style="font-size: 24px; font-weight: 900; color: white; margin-bottom: 12px; letter-spacing: -0.02em;">Acceso Limitado</h3>
                <p id="upgrade-modal-desc" style="font-size: 15px; color: #94a3b8; line-height: 1.6; margin: 0;">Activa Radar PRO para desbloquear esta función y acceder a todas las oportunidades de hoy.</p>
            </div>
            <div style="padding: 32px; background: white;">
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 32px;">
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 700; color: #475569;">
                        <span style="color: #10b981; font-size: 18px;">✔</span> Búsqueda con IA
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 700; color: #475569;">
                        <span style="color: #10b981; font-size: 18px;">✔</span> Contactos Directos
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 700; color: #475569;">
                        <span style="color: #10b981; font-size: 18px;">✔</span> Filtros Premium
                    </div>
                    <div style="display: flex; align-items: center; gap: 10px; font-size: 13px; font-weight: 700; color: #475569;">
                        <span style="color: #10b981; font-size: 18px;">✔</span> Alertas en Vivo
                    </div>
                </div>
                <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" style="display: block; background: #2563eb; color: white; text-align: center; padding: 18px; border-radius: 14px; font-weight: 900; font-size: 16px; text-decoration: none; box-shadow: 0 10px 25px rgba(37,99,235,0.3); transition: transform 0.2s;">
                    Desbloquear acceso PRO ahora
                </a>
                <p style="text-align: center; margin-top: 16px; font-size: 11px; color: #94a3b8; font-weight: 700;">Recupera tu inversión con solo 1 cliente ganado</p>
            </div>
        </div>
    </div>

    <style>
        .ae-modal-v3 {
            position: fixed; inset: 0; z-index: 9999;
            display: flex; align-items: center; justify-content: center;
            padding: 20px;
        }
        .ae-modal-v3__backdrop {
            position: absolute; inset: 0;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(4px);
        }
        .ae-modal-v3__content {
            position: relative; background: #fff;
            border-radius: 24px; padding: 32px;
            width: 100%; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
            animation: ae-modal-pop 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
        @keyframes ae-modal-pop {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }
    </style>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
    function toggleFilters() {
        $('#ae-filters-panel').slideToggle();
        $('#filters-chevron').toggleClass('is-open');
    }

    function fillAiQuery(text) {
        <?php if ($isFree): ?>
            openUpgradeModal('Búsqueda con IA', 'Analiza el mercado con lenguaje natural y encuentra oportunidades exactas. Esta función es exclusiva de Radar PRO.');
            return;
        <?php endif; ?>
        $('#radar-ai-query').val(text);
        handleAiSearch();
    }

    function handleAiSearch() {
        <?php if ($isFree): ?>
            openUpgradeModal('Búsqueda con IA', 'Analiza el mercado con lenguaje natural y encuentra oportunidades exactas. Esta función es exclusiva de Radar PRO.');
            return;
        <?php endif; ?>
        const query = $('#radar-ai-query').val().trim();
        if (!query) return;

        $('#btn-ai-search').prop('disabled', true);
        $('#btn-ai-search-loader').show();
        $('#btn-ai-search-text').hide();
        $('#radar-results-container').css('opacity', '0.5');

        $.ajax({
            url: '<?= site_url('radar/ai-search') ?>',
            method: 'POST',
            data: { query: query, '<?= csrf_token() ?>': '<?= csrf_hash() ?>' },
            success: function(response) {
                if (response.success) {
                    $('#radar-results-container').html(response.html);
                    $('#ai-explanation-text').html(response.explanation);
                    $('#ai-search-explanation').fadeIn();
                    document.getElementById('radar-results-container').scrollIntoView({ behavior: 'smooth' });
                } else {
                    // Si el servidor devuelve error (ej: expiró sesión o perdió suscripción)
                    if (response.message && response.message.includes('Radar PRO')) {
                        openUpgradeModal('Radar PRO', response.message);
                    } else {
                        alert(response.message || 'Error en la búsqueda IA');
                    }
                }
            },
            complete: function() {
                $('#btn-ai-search').prop('disabled', false);
                $('#btn-ai-search-loader').hide();
                $('#btn-ai-search-text').show();
                $('#radar-results-container').css('opacity', '1');
            }
        });
    }

    function openUpgradeModal(title, desc) {
        $('#upgrade-modal-title').text(title || 'Acceso Limitado');
        $('#upgrade-modal-desc').text(desc || 'Activa Radar PRO para desbloquear esta función.');
        $('#radar-upgrade-modal').fadeIn();
    }

    function closeUpgradeModal() {
        $('#radar-upgrade-modal').fadeOut();
    }

    function clearAiSearch() {
        $('#radar-ai-query').val('');
        $('#ai-search-explanation').hide();
        window.location.href = '<?= site_url('radar') ?>';
    }

    function openQuickView(id) {
        $('#ae-qv-content').html('<div style="padding:100px;text-align:center;">Cargando...</div>');
        $('#ae-qv-modal').fadeIn();
        fetch('<?= site_url('radar/quickview/') ?>' + id).then(r => r.text()).then(h => $('#ae-qv-content').html(h));
    }

    function closeQuickView() {
        $('#ae-qv-modal').fadeOut();
    }

    function updateLeadStatus(select, companyId) {
        const status = select.value;
        const statusText = select.options[select.selectedIndex].text;
        
        // Update UI immediately (V3 pill)
        const $pill = $(select).closest('.ae-status-pill-v3');
        if ($pill.length) {
            const colors = {
                'nuevo': '#4b5563',
                'contactado': '#2563eb',
                'seguimiento': '#ea580c',
                'negociacion': '#7c3aed',
                'ganado': '#16a34a'
            };
            const color = colors[status] || '#4b5563';
            $pill.find('.ae-status-dot').css('background', color);
            $pill.find('.ae-status-text').css('color', color).text(statusText);
        } else {
            $(select).attr('class', 'ae-status-select-chip status-bg-' + status);
        }

        $.post('<?= site_url('radar/update-favorite-status') ?>', {
            company_id: companyId,
            status: status,
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        }).done(function() {
            if (typeof Swal !== 'undefined') {
                Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 }).fire({ icon: 'success', title: 'Estado actualizado a ' + statusText });
            }
        }).fail(function() {
            if (typeof Swal !== 'undefined') {
                Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 }).fire({ icon: 'error', title: 'Error al actualizar el estado' });
            }
        });
    }

    function toggleFavorite(btn, id) {
        const isActive = $(btn).toggleClass('is-active').hasClass('is-active');
        
        // Update SVG fill manually just in case
        const $svg = $(btn).find('svg');
        if (isActive) {
            $svg.attr('fill', '#ffb800');
        } else {
            $svg.attr('fill', 'none');
        }

        $.post('<?= site_url('radar/toggle-favorite') ?>', { company_id: id, '<?= csrf_token() ?>': '<?= csrf_hash() ?>' })
        .done(function() {
            if (typeof Swal !== 'undefined') {
                Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 }).fire({ icon: 'success', title: isActive ? 'Añadido a favoritos' : 'Eliminado de favoritos' });
            }
        });
    }

    let radarMap = null;
    let mapMarkers = [];
    const provinceCoords = {
        'ALAVA': [42.8467, -2.6716], 'ALBACETE': [38.9944, -1.8585], 'ALICANTE': [38.3452, -0.4815],
        'ALMERIA': [36.8340, -2.4637], 'ASTURIAS': [43.3614, -5.8593], 'AVILA': [40.6567, -4.7002],
        'BADAJOZ': [38.8794, -6.9706], 'BALEARS, ILLES': [39.5696, 2.6502], 'BARCELONA': [41.3851, 2.1734],
        'BIZKAIA': [43.2630, -2.9350], 'BURGOS': [42.3439, -3.6969], 'CACERES': [39.4753, -6.3722],
        'CADIZ': [36.5271, -6.2886], 'CANTABRIA': [43.4623, -3.8099], 'CASTELLON': [39.9864, -0.0513],
        'CEUTA': [35.8894, -5.3198], 'CIUDAD REAL': [38.9848, -3.9273], 'CORDOBA': [37.8882, -4.7794],
        'CORUNA, A': [43.3623, -8.4115], 'CUENCA': [40.0704, -2.1374], 'GIPUZKOA': [43.3128, -1.9812],
        'GIRONA': [41.9794, 2.8214], 'GRANADA': [37.1773, -3.5986], 'GUADALAJARA': [40.6327, -3.1643],
        'HUELVA': [37.2614, -6.9447], 'HUESCA': [42.1362, -0.4087], 'JAEN': [37.7796, -3.7849],
        'LEON': [42.5987, -5.5670], 'LLEIDA': [41.6176, 0.6200], 'LUGO': [43.0121, -7.5581],
        'MADRID': [40.4168, -3.7038], 'MALAGA': [36.7213, -4.4214], 'MELILLA': [35.2923, -2.9381],
        'MURCIA': [37.9922, -1.1307], 'NAVARRA': [42.8125, -1.6458], 'OURENSE': [42.3358, -7.8639],
        'PALENCIA': [42.0095, -4.5284], 'PALMAS, LAS': [28.1235, -15.4363], 'PONTEVEDRA': [42.4336, -8.6480],
        'RIOJA, LA': [42.4627, -2.4450], 'SALAMANCA': [40.9701, -5.6635], 'SANTA CRUZ DE TENERIFE': [28.4636, -16.2518],
        'SEGOVIA': [40.9429, -4.1088], 'SEVILLA': [37.3891, -5.9845], 'SORIA': [41.7640, -2.4688],
        'TARRAGONA': [41.1189, 1.2445], 'TERUEL': [40.3457, -1.1065], 'TOLEDO': [39.8628, -4.0273],
        'VALENCIA': [39.4699, -0.3763], 'VALLADOLID': [41.6523, -4.7245], 'ZAMORA': [41.5033, -5.7462],
        'ZARAGOZA': [41.6488, -0.8891], 'GUIPUZCOA': [43.3128, -1.9812], 'VIZCAYA': [43.2630, -2.9350]
    };

    function switchView(view) {
        if (view === 'list') { $('#radar-list-view').show(); $('#radar-map-view').hide(); }
        else { $('#radar-list-view').hide(); $('#radar-map-view').show(); initRadarMap(); }
    }

    function initRadarMap() {
        if (radarMap) { radarMap.invalidateSize(); return; }
        radarMap = L.map('radar-leaflet-map').setView([40.41, -3.70], 6);
        L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png').addTo(radarMap);
        loadMapData();
    }

    function loadMapData() {
        const params = new URLSearchParams(new FormData(document.querySelector('.ae-radar-page__filters')));
        mapMarkers.forEach(m => radarMap.removeLayer(m));
        mapMarkers = [];

        fetch('<?= site_url('radar/map-data') ?>?' + params.toString())
        .then(res => res.json())
        .then(data => {
            data.forEach(item => {
                const coords = provinceCoords[item.province.toUpperCase()];
                if (coords) {
                    const m = L.circleMarker(coords, { radius: 10, fillColor: "#2563eb", color: "#fff", weight: 2, fillOpacity: 0.7 }).addTo(radarMap);
                    m.bindPopup(`<strong>${item.province}</strong>: ${item.total} empresas`);
                    mapMarkers.push(m);
                }
            });
        });
    }

    function updateResults(url) {
        $('#radar-results-container').css('opacity', '0.5');
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.text())
            .then(html => {
                $('#radar-results-container').html(html).css('opacity', '1');
            });
    }

    $(document).on('click', '.ae-radar-page__pagination a', function(e) {
        e.preventDefault();
        updateResults(this.href);
    });

    function cancelRadarSubscription() {
        $('#radar-cancel-modal').fadeIn(200);
    }

    function closeCancelModal() {
        $('#radar-cancel-modal').fadeOut(200);
    }

    function confirmRadarCancellation() {
        const btn = event.target;
        btn.disabled = true;
        btn.innerText = 'Cancelando...';
        
        $.post('<?= site_url("billing/cancel-subscription") ?>', { 
            '<?= csrf_token() ?>': '<?= csrf_hash() ?>' 
        }, function() {
            window.location.reload();
        });
    }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/driver.js@1.0.1/dist/driver.js.iife.js"></script>
    <script src="<?= base_url('public/js/radar-tour.js?v=' . time()) ?>"></script>

    <?php if ($isFree): ?>
    <script>
        window.showConversionNudge = function(title, desc) {
            if (title) $('#upgrade-modal-title').text(title);
            if (desc) $('#upgrade-modal-desc').text(desc);
            $('#radar-upgrade-modal').fadeIn(200);
        }
        window.closeUpgradeModal = function() {
            $('#radar-upgrade-modal').fadeOut(200);
        }
    </script>
    <?php endif; ?>
</body>
</html>
