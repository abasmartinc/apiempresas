<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Radar B2B - APIEmpresas',
        'excerptText' => 'Herramienta de prospección profesional en tiempo real.',
    ]) ?>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-w: 280px;
            --primary: #2152ff;
            --primary-dark: #1a41cc;
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --text-main: #0f172a;
            --text-muted: #64748b;
            --border: #e2e8f0;
            --radius: 12px;
            --shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
        }

        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            margin: 0;
            overflow: hidden; /* App shell feel */
        }

        /* App Layout */
        .app-shell {
            display: grid;
            grid-template-columns: var(--sidebar-w) 1fr;
            height: 100vh;
            width: 100vw;
        }

        /* Sidebar Container */
        .sidebar {
            background: #fff;
            border-right: 1px solid var(--border);
            display: flex;
            flex-direction: column;
            z-index: 50;
        }

        .sidebar-brand {
            padding: 24px;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 24px 16px;
            overflow-y: auto;
        }

        .nav-label {
            font-size: 11px;
            font-weight: 800;
            color: var(--text-muted);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
            display: block;
            padding-left: 12px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 12px;
            border-radius: 10px;
            color: var(--slate-700);
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 4px;
            transition: all 0.2s;
        }
        .nav-link:hover { background: #f8fafc; color: var(--primary); }
        .nav-link.active { background: #eff6ff; color: var(--primary); }
        .nav-link i { margin-right: 12px; font-size: 1.1rem; }

        /* Main Area */
        .main-content {
            display: flex;
            flex-direction: column;
            height: 100vh;
            overflow: hidden;
        }

        /* Header / Top Bar */
        .header {
            height: 64px;
            background: #fff;
            border-bottom: 1px solid var(--border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 32px;
            flex-shrink: 0;
        }

        .breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 14px; font-weight: 600; color: var(--text-muted); }
        .breadcrumb span.active { color: var(--text-main); font-weight: 700; }

        /* Scrollable Content */
        .content-body {
            flex: 1;
            overflow-y: auto;
            padding: 32px;
        }

        /* Stats Section - Wide */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            margin-bottom: 32px;
        }
        .stat-card {
            background: #fff;
            padding: 24px;
            border-radius: 16px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
        }
        .stat-label { font-size: 12px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; margin-bottom: 8px; display: block; }
        .stat-value { font-size: 24px; font-weight: 900; color: var(--text-main); letter-spacing: -0.02em; }
        
        /* Filter Bar - Full Width */
        .filter-bar {
            background: #fff;
            padding: 16px 24px;
            border-radius: 16px;
            border: 1px solid var(--border);
            margin-bottom: 32px;
            display: flex;
            gap: 20px;
            align-items: flex-end;
            box-shadow: var(--shadow);
        }
        .filter-group { flex: 1; display: flex; flex-direction: column; gap: 8px; }
        .filter-label { font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; }
        .filter-select {
            width: 100%;
            padding: 10px 14px;
            border-radius: 10px;
            border: 1px solid var(--border);
            background: #f9fafb;
            font-size: 14px;
            font-weight: 600;
            outline: none;
            cursor: pointer;
        }

        /* DataTable Card */
        .data-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid var(--border);
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            overflow: hidden;
        }
        .data-header { padding: 20px 24px; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; background: #fafafb; }
        
        .table-wrap { overflow-x: auto; }
        .table { width: 100%; border-collapse: collapse; text-align: left; }
        .table th { padding: 16px 24px; font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase; border-bottom: 1px solid var(--border); }
        .table td { padding: 16px 24px; border-bottom: 1px solid #f8fafc; font-size: 14px; vertical-align: middle; }
        .table tr:hover { background: #fcfdfe; }

        .company-name { font-weight: 800; color: var(--text-main); font-size: 15px; }
        .company-cif { font-size: 12px; color: var(--text-muted); font-family: monospace; }
        
        .badge { display: inline-flex; align-items: center; padding: 4px 8px; border-radius: 6px; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .badge-sector { background: #eff6ff; color: var(--primary); border: 1px solid #dbeafe; }
        .badge-new { background: #fef2f2; color: #ef4444; border: 1px solid #fee2e2; }

        .btn-action {
            height: 36px;
            width: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            background: #f3f4f6;
            color: var(--text-muted);
            border: 1px solid var(--border);
            transition: 0.2s;
            cursor: pointer;
        }
        .btn-action:hover { background: var(--text-main); color: #fff; }

        .live-dot { height: 8px; width: 8px; background: #10b981; border-radius: 50%; display: inline-block; margin-right: 8px; animation: pulse 2s infinite; }
        @keyframes pulse { 0% { transform: scale(0.95); opacity: 0.7; } 70% { transform: scale(1.1); opacity: 1; } 100% { transform: scale(0.95); opacity: 0.7; } }

        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 8px; height: 8px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        .banner-limited {
            background: linear-gradient(90deg, #fffbeb, #fef3c7);
            border: 1px solid #fcd34d;
            padding: 12px 24px;
            border-radius: 12px;
            margin-bottom: 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #92400e;
            font-size: 14px;
            font-weight: 600;
        }
        .banner-limited i { margin-right: 8px; font-size: 1.2rem; vertical-align: middle; }
        .btn-upgrade-sm {
            background: #92400e;
            color: white;
            padding: 6px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 800;
        }

        .locked-tag {
            position: absolute;
            right: 8px;
            background: #f1f5f9;
            color: #64748b;
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 4px;
            font-weight: 800;
        }

        /* Paywall */
        .paywall-container {
            position: relative;
        }
        .paywall-blur {
            filter: blur(6px);
            pointer-events: none;
            user-select: none;
            opacity: 0.6;
        }
        .paywall-overlay {
            position: absolute;
            inset: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.4);
            z-index: 10;
        }
        .paywall-card {
            background: #fff;
            padding: 40px;
            border-radius: 24px;
            text-align: center;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            max-width: 450px;
            border: 1px solid var(--border);
        }
    </style>
</head>
<body>

    <?php
    $formatEsDate = function($dateStr, $format = 'd M Y') {
        if (empty($dateStr)) return 'Reciente';
        $timestamp = strtotime($dateStr);
        if (!$timestamp || $timestamp > strtotime('+1 year') || $timestamp < strtotime('1900-01-01')) return 'Reciente';
        $mesesEn = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $mesesEs = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        $formatted = date($format, $timestamp);
        return str_replace($mesesEn, $mesesEs, $formatted);
    };

    // Si es free, solo mostramos las primeras 5 empresas de forma real, el resto ocultas/blurry
    $displayCompanies = $isFree ? array_slice($companies, 0, 5) : $companies;
    ?>

    <div class="app-shell">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-brand">
                <img src="<?= base_url('assets/img/logo-dark.png') ?>" alt="APIEmpresas" height="32" onerror="this.src='https://via.placeholder.com/150x40?text=APIEmpresas'">
            </div>
            <div class="sidebar-nav">
                <span class="nav-label">Radar Real-Time</span>
                <a href="<?= site_url('radar') ?>" class="nav-link active">
                    <i>📊</i> Radar Principal
                </a>
                <a href="<?= site_url('precios-radar') ?>" class="nav-link <?= ($isFree) ? 'locked' : '' ?>">
                    <i>🔔</i> Alertas Diarias <?= ($isFree) ? '<span class="locked-tag">PRO</span>' : '' ?>
                </a>
                <a href="<?= site_url('precios-radar') ?>" class="nav-link <?= ($isFree) ? 'locked' : '' ?>">
                    <i>🤖</i> IA Leads <?= ($isFree) ? '<span class="locked-tag">PRO</span>' : '' ?>
                </a>
                
                <div style="margin-top: 32px;">
                    <span class="nav-label">Sectores Hot</span>
                    <?php foreach(array_slice($topSectors, 0, 8) as $s): ?>
                        <a href="<?= site_url('radar?cnae=' . $s['code']) ?>" class="nav-link <?= ($filters['cnae'] === $s['code']) ? 'active' : '' ?>">
                            <span style="opacity: 0.3; margin-right: 12px; font-weight: 900;">#</span>
                            <?= esc(substr($s['label'], 0, 22)) ?>...
                        </a>
                    <?php endforeach; ?>
                </div>

                <div style="margin-top: auto; padding-top: 32px;">
                    <span class="nav-label">Mi Cuenta</span>
                    <a href="<?= site_url('dashboard') ?>" class="nav-link"><i>🏠</i> Dashboard API</a>
                    <a href="<?= site_url('billing') ?>" class="nav-link"><i>💳</i> Billing</a>
                    <a href="<?= site_url('logout') ?>" class="nav-link"><i>🚪</i> Salir</a>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="breadcrumb">
                    <span>APIEmpresas</span>
                    <span>/</span>
                    <span class="active">Radar B2B</span>
                </div>
                <div style="display: flex; gap: 12px;">
                    <?php if (!$isFree): ?>
                    <button onclick="alert('Exportando CSV...')" style="padding: 8px 16px; border-radius: 8px; border: 1px solid var(--border); background: #fff; font-weight: 700; cursor: pointer;">CSV</button>
                    <button onclick="alert('Generando Excel...')" style="padding: 8px 16px; border-radius: 8px; border: none; background: var(--text-main); color: #fff; font-weight: 800; cursor: pointer;">Descargar Excel</button>
                    <?php else: ?>
                    <a href="<?= site_url('precios-radar') ?>" style="padding: 8px 16px; border-radius: 8px; border: none; background: var(--primary); color: #fff; font-weight: 800; text-decoration: none; font-size: 13px;">DESBLOQUEAR EXPORTACIÓN</a>
                    <?php endif; ?>
                </div>
            </header>

            <!-- Scrollable Body -->
            <div class="content-body">
                <?php if ($userPlan['isTemporary']): ?>
                <div class="banner-limited">
                    <div>
                        <i>⏳</i> <strong>Acceso Temporal:</strong> Tu acceso de descarga única caduca en <strong><?= $userPlan['hoursLeft'] ?> horas</strong>.
                    </div>
                    <a href="<?= site_url('precios-radar') ?>" class="btn-upgrade-sm">PASAR A RADAR PRO</a>
                </div>
                <?php endif; ?>

                <div style="margin-bottom: 32px;">
                    <h1 class="radar-title" style="font-size: 2.2rem; font-weight: 900; margin-bottom: 8px;">Radar de Constituciones</h1>
                    <p class="radar-subtitle" style="font-size: 1.1rem; color: var(--text-muted); font-weight: 500;">Monitoreando el mercado español para tu equipo comercial.</p>
                </div>

                <!-- Stats Grid - 4 Columns -->
                <div class="stats-row">
                    <div class="stat-card">
                        <span class="stat-label">Constituidas Hoy <span class="live-dot"></span></span>
                        <div class="stat-value"><?= number_format($stats['hoy']) ?></div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">Esta Semana</span>
                        <div class="stat-value"><?= number_format($stats['semana']) ?></div>
                    </div>
                    <div class="stat-card">
                        <span class="stat-label">Este Mes</span>
                        <div class="stat-value"><?= number_format($stats['mes']) ?></div>
                    </div>
                    <div class="stat-card" style="background: var(--primary); color: white; border: none;">
                        <span class="stat-label" style="color: rgba(255,255,255,0.7);">Sincronización</span>
                        <div class="stat-value" style="font-size: 1.1rem; color: #fff; margin-top: 5px;">BORME LIVE ACTIVADO</div>
                    </div>
                </div>

                <!-- Filter Panel - Wide -->
                <form action="<?= site_url('radar') ?>" method="GET" class="filter-bar">
                    <div class="filter-group">
                        <label class="filter-label">Provincia</label>
                        <select name="provincia" class="filter-select" onchange="this.form.submit()">
                            <option value="">Todas las provincias</option>
                            <?php foreach($provinces as $p): ?>
                                <option value="<?= url_title($p['name'], '-', true) ?>" <?= ($filters['provincia'] === url_title($p['name'], '-', true)) ? 'selected' : '' ?>>
                                    <?= esc(ucfirst(strtolower($p['name']))) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Sector Económico</label>
                        <select name="cnae" class="filter-select" onchange="this.form.submit()">
                            <option value="">Todos los CNAE</option>
                            <?php foreach($topSectors as $s): ?>
                                <option value="<?= esc($s['code']) ?>" <?= ($filters['cnae'] === $s['code']) ? 'selected' : '' ?>>
                                    <?= esc($s['label']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label class="filter-label">Ventana de Tiempo</label>
                        <select name="rango" class="filter-select" onchange="this.form.submit()">
                            <option value="7" <?= ($filters['rango'] === '7') ? 'selected' : '' ?>>7 días</option>
                            <option value="30" <?= ($filters['rango'] === '30') ? 'selected' : '' ?>>30 días</option>
                            <option value="90" <?= ($filters['rango'] === '90') ? 'selected' : '' ?>>90 días</option>
                        </select>
                    </div>
                    <div style="display: flex; gap: 8px;">
                        <button type="button" class="btn-action" style="width: auto; padding: 0 20px; font-weight: 800; font-size: 0.8rem;" onclick="<?= $isFree ? "window.location.href='".site_url('precios-radar')."'" : "alert('Búsqueda Guardada')" ?>">
                            <?= $isFree ? 'DESBLOQUEAR FILTROS' : 'GUARDAR BÚSQUEDA' ?>
                        </button>
                    </div>
                </form>

                <!-- Main Data Card -->
                <div class="data-card <?= $isFree ? 'paywall-container' : '' ?>">
                    <div class="data-header">
                        <div style="font-weight: 900; color: var(--text-main);"><?= count($companies) ?> Resultados encontrados</div>
                        <div style="font-size: 11px; font-weight: 800; color: var(--text-muted); text-transform: uppercase;">Mostrando últimas constituciones registradas</div>
                    </div>
                    
                    <div class="table-wrap <?= $isFree ? 'paywall-blur' : '' ?>">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Empresa</th>
                                    <th>Constitución</th>
                                    <th>Provincia</th>
                                    <th>Sector</th>
                                    <th style="text-align: right;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($displayCompanies as $co): 
                                    $link = company_url(['cif' => $co['cif'], 'name' => $co['company_name']]);
                                    $isNew = (strtotime($co['fecha_constitucion']) >= strtotime('-3 days'));
                                ?>
                                <tr>
                                    <td>
                                        <div class="company-name"><?= esc($co['company_name']) ?></div>
                                        <div class="company-cif"><?= esc($co['cif']) ?></div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--text-main);"><?= $formatEsDate($co['fecha_constitucion']) ?></div>
                                        <?php if($isNew): ?>
                                            <span class="badge badge-new" style="margin-top: 4px;">NUEVA</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span style="font-weight: 700; color: var(--slate-700);"><?= esc(ucfirst(strtolower($co['registro_mercantil']))) ?></span>
                                    </td>
                                    <td>
                                        <span class="badge badge-sector" title="<?= esc($co['cnae_label']) ?>">
                                            <?= esc(substr($co['cnae_label'], 0, 35)) ?><?= strlen($co['cnae_label']) > 35 ? '...' : '' ?>
                                        </span>
                                    </td>
                                    <td style="text-align: right;">
                                        <div style="display: flex; gap: 8px; justify-content: flex-end;">
                                            <button onclick="<?= $isFree ? "window.location.href='".site_url('precios-radar')."'" : "navigator.clipboard.writeText('".esc($co['company_name'])."'); alert('Copiado')" ?>" class="btn-action" title="Copiar">📋</button>
                                            <a href="<?= $isFree ? site_url('precios-radar') : $link ?>" <?= !$isFree ? 'target="_blank"' : '' ?> class="btn-action" title="Ficha">➔</a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <?php if ($isFree): ?>
                                    <!-- Filas dummy para el relleno visual del blur -->
                                    <?php for($i=0; $i<10; $i++): ?>
                                    <tr>
                                        <td><div class="company-name">EMPRESA BLOQUEADA</div><div class="company-cif">B12345678</div></td>
                                        <td><div style="font-weight: 700;">XX XXX 2024</div></td>
                                        <td><span>MADRID</span></td>
                                        <td><span class="badge badge-sector">SECTOR OCULTO</span></td>
                                        <td style="text-align: right;"><button class="btn-action">➔</button></td>
                                    </tr>
                                    <?php endfor; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($isFree): ?>
                    <div class="paywall-overlay">
                        <div class="paywall-card">
                            <h2 style="font-size: 1.8rem; font-weight: 900; color: var(--text-main); margin-bottom: 1rem;">💼 Radar Profesional</h2>
                            <p style="color: var(--text-muted); font-weight: 500; line-height: 1.6; margin-bottom: 2rem;">
                                Accede a los datos completos de las <strong><?= number_format($stats['mes']) ?></strong> empresas constituidas este mes. Exporta a Excel y recibe alertas diarias.
                            </p>
                            <div style="display: flex; flex-direction: column; gap: 12px;">
                                <a href="<?= site_url('precios-radar') ?>" style="display: block; background: var(--primary); color: white; padding: 16px; border-radius: 12px; text-decoration: none; font-weight: 800; font-size: 1.1rem; box-shadow: 0 10px 15px -3px rgba(33, 82, 255, 0.3);">
                                    DESBLOQUEAR ACCESO COMPLETO
                                </a>
                                <p style="font-size: 12px; color: var(--text-muted); font-weight: 600;">Desde solo 1€ por descarga única o suscripción mensual.</p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>

                <footer style="margin-top: 40px; padding: 24px; text-align: center; color: var(--text-muted); font-size: 13px; font-weight: 600;">
                    &copy; <?= date('Y') ?> APIEmpresas - Panel de Inteligencia Comercial B2B
                </footer>
            </div>
        </main>
    </div>

</body>
</html>
