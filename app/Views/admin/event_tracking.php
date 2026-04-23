<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head', ['title' => $title]) ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 48px;
        }

        .metric-card {
            background: white;
            border-radius: 20px;
            padding: 28px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            position: relative;
        }

        .metric-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 20px -5px rgba(0, 0, 0, 0.08);
        }

        .metric-title {
            font-size: 0.85rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
        }

        .metric-value {
            font-size: 2.2rem;
            font-weight: 900;
            color: #1e293b;
            margin-bottom: 8px;
            letter-spacing: -0.02em;
        }

        .section-separator {
            margin: 60px 0 32px;
            position: relative;
        }

        .section-separator::after {
            content: '';
            position: absolute;
            left: 0;
            right: 0;
            top: 50%;
            height: 1px;
            background: #e2e8f0;
            z-index: 1;
        }

        .section-separator span {
            position: relative;
            background: #f8fafc;
            padding-right: 20px;
            font-size: 1.1rem;
            font-weight: 800;
            color: #1e293b;
            z-index: 2;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-separator span::before {
            content: '';
            width: 4px;
            height: 20px;
            background: #2152ff;
            border-radius: 4px;
        }

        .update-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 14px;
            background: #f1f5f9;
            border-radius: 100px;
            font-size: 0.8rem;
            font-weight: 600;
            color: #64748b;
            margin-bottom: 32px;
        }

        .update-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        /* Filter Styles */
        .filter-container {
            background: white;
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 40px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            display: flex;
            gap: 16px;
            align-items: flex-end;
            flex-wrap: wrap;
        }

        .form-group-filter { display: flex; flex-direction: column; gap: 8px; flex: 1; min-width: 150px; }
        .form-group-filter label { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; }
        .form-group-filter select, .form-group-filter input { 
            background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 10px 14px; font-size: 0.9rem; font-weight: 500; color: #1e293b;
        }

        /* AI Section */
        .ai-consultant-card {
            background: linear-gradient(135deg, #2152ff 0%, #6366f1 100%);
            color: white;
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .ai-consultant-card::after {
            content: '✨';
            position: absolute;
            top: -20px;
            right: -10px;
            font-size: 100px;
            opacity: 0.1;
        }

        .ai-content {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 14px;
            padding: 20px;
            margin-top: 20px;
            font-size: 0.95rem;
            line-height: 1.6;
            display: none;
        }

        .chart-container { position: relative; height: 320px; width: 100%; }

        .btn.primary-grad {
            background: linear-gradient(135deg, #2152ff 0%, #4338ca 100%);
            color: white;
            border: none;
            font-weight: 700;
        }

        .table-admin {
            width: 100%;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            border: 1px solid rgba(0, 0, 0, 0.05);
        }

        /* Estilos de la Tabla Custom */
        .table-custom {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-custom thead th {
            background: #f8fafc;
            padding: 16px 20px;
            font-size: 0.75rem;
            font-weight: 800;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #e2e8f0;
        }

        .table-custom tbody td {
            padding: 16px 20px;
            font-size: 0.9rem;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .table-custom tbody tr:last-child td {
            border-bottom: none;
        }

        .table-custom tbody tr:hover {
            background: #f8fafc;
        }

        .badge-event {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            background: rgba(33, 82, 255, 0.08);
            color: #2152ff;
            text-transform: lowercase;
        }

        .user-id-badge {
            color: #2152ff;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 4px;
        }
    </style>
</head>

<body class="admin-body">
    <div class="bg-halo" aria-hidden="true"></div>

    <?= view('partials/header_admin') ?>

    <main class="container-admin" style="padding: 40px 0 80px;">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 8px;">
            <div>
                <h1 class="title" style="font-size: 2.8rem; margin-bottom: 8px;">Tracking <span class="grad">Comportamiento</span></h1>
                <p style="color: #64748b; font-size: 1.1rem;">Análisis de actividad, clics y funnel de comportamiento real.</p>
            </div>
            <div style="display: flex; gap: 12px; margin-top: 10px;">
                <button id="btn-ai-analyze" class="btn primary-grad shadow-sm">
                    ✨ Consultoría IA
                </button>
                <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
            </div>
        </div>

        <div class="update-badge">
            <span class="update-dot"></span>
            Usuarios en Vivo: <strong style="color: #10b981; margin-left: 4px;"><?= number_format($activeUsers) ?></strong>
        </div>

        <!-- BLOQUE IA -->
        <div id="ai-container" class="ai-consultant-card" style="display: none;">
            <h3 style="margin: 0; font-weight: 800; font-size: 1.3rem;">Insights de Comportamiento</h3>
            <p style="margin: 4px 0 0; opacity: 0.9; font-size: 0.95rem;">Análisis generado automáticamente por el motor de IA.</p>
            
            <div id="ai-loader" style="display:none;" class="mt-3">
                <div class="spinner-border spinner-border-sm text-white" role="status"></div>
                <span class="ms-2">Procesando patrones de navegación...</span>
            </div>

            <div id="ai-response" class="ai-content"></div>
        </div>

        <!-- FILTROS -->
        <form id="filter-form" class="filter-container">
            <div class="form-group-filter">
                <label>Tipo de Evento</label>
                <select name="event_name">
                    <option value="">Todos los eventos</option>
                    <?php foreach($eventNames as $en): ?>
                        <option value="<?= $en['event_name'] ?>" <?= $filters['event_name'] == $en['event_name'] ? 'selected' : '' ?>><?= $en['event_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group-filter">
                <label>Desde</label>
                <input type="date" name="from_date" value="<?= $filters['from_date'] ?>">
            </div>
            <div class="form-group-filter">
                <label>Hasta</label>
                <input type="date" name="to_date" value="<?= $filters['to_date'] ?>">
            </div>
            <div class="form-group-filter">
                <label>ID Usuario</label>
                <input type="text" name="user_id" placeholder="Ej: 123" value="<?= $filters['user_id'] ?>">
            </div>
            <div style="display: flex; gap: 8px;">
                <button type="submit" class="btn primary shadow-sm" style="height: 44px; padding: 0 24px;">Filtrar</button>
                <a href="<?= site_url('admin/event-tracking') ?>" class="btn ghost" style="height: 44px; width: 44px; padding: 0; display: flex; align-items: center; justify-content: center;"><i class="fas fa-redo"></i></a>
            </div>
        </form>

        <div class="metrics-grid">
            <div class="metric-card">
                <p class="metric-title">Visitantes Únicos</p>
                <div class="metric-value"><?= number_format($uniqueVisitors) ?></div>
                <p class="metric-desc">Usuarios distintos identificados</p>
            </div>
            <div class="metric-card">
                <p class="metric-title">Eventos Totales</p>
                <div class="metric-value"><?= number_format($totalEvents) ?></div>
                <p class="metric-desc">Acciones registradas en el periodo</p>
            </div>
            <div class="metric-card">
                <p class="metric-title">Eventos / Usuario</p>
                <div class="metric-value"><?= $uniqueVisitors > 0 ? round($totalEvents / $uniqueVisitors, 1) : 0 ?></div>
                <p class="metric-desc">Promedio de interacción por visita</p>
            </div>
        </div>

        <div class="section-separator"><span>TENDENCIA Y DISTRIBUCIÓN</span></div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 48px;">
            <div class="metric-card">
                <div class="chart-container"><canvas id="timelineChart"></canvas></div>
            </div>
            <div class="metric-card">
                <div class="chart-container"><canvas id="eventsChart"></canvas></div>
            </div>
        </div>

        <div class="section-separator"><span>ACTIVIDAD DETALLADA</span></div>

        <div id="table-container">
            <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        </div>

    </main>

    <script>
        function loadTable(page = 1) {
            const container = document.getElementById('table-container');
            const formData = new FormData(document.getElementById('filter-form'));
            const params = new URLSearchParams(formData);
            params.append('page_events', page);
            fetch('<?= site_url('admin/event-tracking/table') ?>?' + params.toString()).then(res => res.text()).then(html => {
                container.innerHTML = html;
                container.querySelectorAll('.pagination a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const url = new URL(this.href); loadTable(url.searchParams.get('page_events'));
                    });
                });
            });
        }
        document.getElementById('filter-form').addEventListener('submit', function(e) { e.preventDefault(); loadTable(1); });
        document.addEventListener('DOMContentLoaded', () => loadTable(1));

        document.getElementById('btn-ai-analyze').addEventListener('click', function() {
            const container = document.getElementById('ai-container');
            const loader = document.getElementById('ai-loader');
            const responseDiv = document.getElementById('ai-response');
            container.style.display = 'block'; loader.style.display = 'block'; responseDiv.style.display = 'none';
            fetch('<?= site_url('admin/event-tracking/ai-analyze') ?>').then(res => res.json()).then(data => {
                loader.style.display = 'none'; responseDiv.style.display = 'block';
                responseDiv.innerHTML = marked.parse(data.analysis);
            });
        });

        Chart.defaults.color = '#64748b';
        Chart.defaults.font.family = "'Inter', sans-serif";
        new Chart(document.getElementById('timelineChart').getContext('2d'), { type: 'line', data: { labels: <?= json_encode(array_column($timeline, 'date')) ?>, datasets: [{ label: 'Eventos', data: <?= json_encode(array_column($timeline, 'total')) ?>, borderColor: '#2152ff', backgroundColor: 'rgba(33, 82, 255, 0.05)', fill: true, tension: 0.4, pointRadius: 4 }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, grid: { color: '#f1f5f9' } }, x: { grid: { display: false } } } } });
        new Chart(document.getElementById('eventsChart').getContext('2d'), { type: 'doughnut', data: { labels: <?= json_encode(array_column($summary, 'event_name')) ?>, datasets: [{ data: <?= json_encode(array_column($summary, 'total')) ?>, backgroundColor: ['#2152ff', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'], borderWidth: 4, borderColor: '#ffffff' }] }, options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom', labels: { padding: 20, usePointStyle: true, font: { weight: '600' } } } }, cutout: '75%' } });
    </script>

    <?= view('partials/footer') ?>
</body>

</html>
