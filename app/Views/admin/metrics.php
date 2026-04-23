<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $title ?> | Admin APIEmpresas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        :root {
            --bg-main: #f1f5f9;
            --card-bg: #ffffff;
            --accent: #2152ff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --border-color: rgba(0, 0, 0, 0.05);
            --ai-glow: rgba(33, 82, 255, 0.15);
            --live-color: #10b981;
        }

        body { background-color: var(--bg-main); color: var(--text-main); font-family: 'Inter', system-ui, -apple-system, sans-serif; padding: 2rem 0; }
        .metric-card { background: var(--card-bg); border-radius: 20px; padding: 1.5rem; border: 1px solid var(--border-color); box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); height: 100%; position: relative; overflow: hidden; }
        .metric-value { font-size: 2.2rem; font-weight: 800; color: var(--text-main); }
        .metric-label { color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; font-size: 0.7rem; font-weight: 700; margin-bottom: 0.25rem; }

        .live-pulse {
            width: 10px;
            height: 10px;
            background: var(--live-color);
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
            position: relative;
            box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7);
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .ai-card { background: linear-gradient(135deg, #ffffff 0%, #f0f4ff 100%); border: 2px solid var(--accent); border-radius: 20px; padding: 2rem; position: relative; overflow: hidden; box-shadow: 0 10px 30px var(--ai-glow); margin-bottom: 2.5rem; }
        .ai-response { font-size: 1rem; line-height: 1.7; color: #334155; display: none; margin-top: 1.5rem; padding: 1.5rem; background: white; border-radius: 14px; border: 1px solid rgba(33, 82, 255, 0.1); }
        
        .filter-bar { background: var(--card-bg); border-radius: 16px; padding: 1.25rem; margin-bottom: 2rem; border: 1px solid var(--border-color); }
        .form-control, .form-select { border-radius: 10px; border: 1px solid #e2e8f0; padding: 0.6rem 1rem; font-size: 0.9rem; }
        .btn-filter { background: var(--accent); color: white; border: none; border-radius: 10px; padding: 0.6rem 1.5rem; font-weight: 600; }
        .btn-reset { background: #f1f5f9; color: var(--text-muted); border: none; border-radius: 10px; padding: 0.6rem 1rem; font-weight: 600; }

        .badge-event { background: rgba(33, 82, 255, 0.1); color: var(--accent); padding: 4px 10px; border-radius: 8px; font-size: 0.75rem; font-weight: 600; }
        .section-title { font-weight: 800; color: var(--text-main); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; }
        .section-title::before { content: ''; width: 4px; height: 20px; background: var(--accent); border-radius: 4px; }

        .chart-container { position: relative; height: 300px; width: 100%; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="<?= site_url('admin/dashboard') ?>" class="text-muted text-decoration-none small fw-bold"><i class="fas fa-arrow-left me-2"></i> Dashboard</a>
        <button id="btn-ai-analyze" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm">
            <i class="fas fa-brain me-2"></i> Consultor IA
        </button>
    </div>

    <h1 class="fw-bold mb-4">Event <span style="color:var(--accent)">Tracking</span></h1>

    <div id="ai-container" class="ai-card" style="display: none;">
        <h4 class="fw-bold mb-3"><i class="fas fa-magic me-2"></i> IA Insights</h4>
        <div id="ai-loader" style="display:none;" class="text-center py-3"><div class="spinner-border text-primary"></div></div>
        <div id="ai-response" class="ai-response"></div>
    </div>

    <div class="filter-bar">
        <form id="filter-form" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Evento</label>
                <select name="event_name" class="form-select">
                    <option value="">Todos</option>
                    <?php foreach($eventNames as $en): ?>
                        <option value="<?= $en['event_name'] ?>" <?= $filters['event_name'] == $en['event_name'] ? 'selected' : '' ?>><?= $en['event_name'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Desde</label>
                <input type="date" name="from_date" class="form-control" value="<?= $filters['from_date'] ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">Hasta</label>
                <input type="date" name="to_date" class="form-control" value="<?= $filters['to_date'] ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small fw-bold text-muted">ID Usuario</label>
                <input type="text" name="user_id" class="form-control" placeholder="ID..." value="<?= $filters['user_id'] ?>">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn-filter flex-grow-1">Filtrar</button>
                <a href="<?= site_url('admin/event-tracking') ?>" class="btn-reset"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-3">
            <div class="metric-card" style="border-left: 5px solid var(--live-color);">
                <div class="metric-label"><span class="live-pulse"></span> Usuarios en Vivo</div>
                <div class="metric-value" style="color: var(--live-color);"><?= number_format($activeUsers) ?></div>
                <div class="text-muted small mt-2">Actividad en los últimos 5 min</div>
            </div>
        </div>
        <div class="col-md-3"><div class="metric-card"><div class="metric-label">Visitantes Únicos</div><div class="metric-value"><?= number_format($uniqueVisitors) ?></div></div></div>
        <div class="col-md-3"><div class="metric-card"><div class="metric-label">Total de Eventos</div><div class="metric-value"><?= number_format($totalEvents) ?></div></div></div>
        <div class="col-md-3"><div class="metric-card"><div class="metric-label">Eventos / Visitante</div><div class="metric-value"><?= $uniqueVisitors > 0 ? round($totalEvents / $uniqueVisitors, 1) : 0 ?></div></div></div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-8"><div class="metric-card"><h5 class="section-title">Tendencia de Actividad</h5><div class="chart-container"><canvas id="timelineChart"></canvas></div></div></div>
        <div class="col-lg-4"><div class="metric-card"><h5 class="section-title">Distribución por Tipo</h5><div class="chart-container"><canvas id="eventsChart"></canvas></div></div></div>
    </div>

    <div class="metric-card mb-5">
        <h5 class="section-title">Actividad Detallada</h5>
        <div id="table-container">
            <div class="text-center py-5"><div class="spinner-border text-primary"></div></div>
        </div>
    </div>
</div>

<script>
    function loadTable(page = 1) {
        const container = document.getElementById('table-container');
        const formData = new FormData(document.getElementById('filter-form'));
        const params = new URLSearchParams(formData);
        params.append('page_events', page);
        fetch('<?= site_url('admin/event-tracking/table') ?>?' + params.toString())
            .then(res => res.text())
            .then(html => {
                container.innerHTML = html;
                container.querySelectorAll('.pagination a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        const url = new URL(this.href);
                        loadTable(url.searchParams.get('page_events'));
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
        fetch('<?= site_url('admin/event-tracking/ai-analyze') ?>')
            .then(res => res.json())
            .then(data => {
                loader.style.display = 'none'; responseDiv.style.display = 'block';
                responseDiv.innerHTML = marked.parse(data.analysis);
            });
    });

    Chart.defaults.color = '#64748b';
    new Chart(document.getElementById('timelineChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($timeline, 'date')) ?>,
            datasets: [{ label: 'Eventos', data: <?= json_encode(array_column($timeline, 'total')) ?>, borderColor: '#2152ff', backgroundColor: 'rgba(33, 82, 255, 0.05)', fill: true, tension: 0.4 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    new Chart(document.getElementById('eventsChart').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($summary, 'event_name')) ?>,
            datasets: [{ data: <?= json_encode(array_column($summary, 'total')) ?>, backgroundColor: ['#2152ff', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'], borderWidth: 2 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
    });
</script>

</body>
</html>
