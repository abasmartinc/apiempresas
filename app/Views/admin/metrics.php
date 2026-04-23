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
        }

        body {
            background-color: var(--bg-main);
            color: var(--text-main);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            padding: 2rem 0;
        }

        .metric-card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            height: 100%;
        }

        .metric-value { font-size: 2.2rem; font-weight: 800; color: var(--text-main); }
        .metric-label { color: var(--text-muted); text-transform: uppercase; letter-spacing: 1px; font-size: 0.7rem; font-weight: 700; margin-bottom: 0.25rem; }

        .filter-bar { background: var(--card-bg); border-radius: 16px; padding: 1.25rem; margin-bottom: 2rem; border: 1px solid var(--border-color); }
        .form-control, .form-select { border-radius: 10px; border: 1px solid #e2e8f0; padding: 0.6rem 1rem; font-size: 0.9rem; }

        .btn-filter { background: var(--accent); color: white; border: none; border-radius: 10px; padding: 0.6rem 1.5rem; font-weight: 600; }
        .btn-reset { background: #f1f5f9; color: var(--text-muted); border: none; border-radius: 10px; padding: 0.6rem 1rem; font-weight: 600; }

        /* AI Section Styles */
        .ai-card {
            background: linear-gradient(135deg, #ffffff 0%, #f0f4ff 100%);
            border: 2px solid var(--accent);
            border-radius: 20px;
            padding: 2rem;
            position: relative;
            overflow: hidden;
            box-shadow: 0 10px 30px var(--ai-glow);
            margin-bottom: 2.5rem;
        }

        .ai-card::after {
            content: '\f0e7';
            font-family: 'Font Awesome 6 Free';
            font-weight: 900;
            position: absolute;
            top: -20px;
            right: -10px;
            font-size: 150px;
            color: var(--accent);
            opacity: 0.03;
            pointer-events: none;
        }

        .btn-ai-analyze {
            background: var(--accent);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 0.8rem 2rem;
            font-weight: 700;
            box-shadow: 0 4px 15px var(--ai-glow);
            transition: all 0.3s ease;
        }

        .btn-ai-analyze:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px var(--ai-glow);
        }

        .ai-response {
            font-size: 1rem;
            line-height: 1.7;
            color: #334155;
            display: none;
            margin-top: 2rem;
            padding: 1.5rem;
            background: white;
            border-radius: 14px;
            border: 1px solid rgba(33, 82, 255, 0.1);
        }

        .ai-loader {
            display: none;
            text-align: center;
            margin-top: 1rem;
        }

        .dot-flashing {
            position: relative;
            width: 10px;
            height: 10px;
            border-radius: 5px;
            background-color: var(--accent);
            color: var(--accent);
            animation: dot-flashing 1s infinite linear alternate;
            animation-delay: .5s;
            margin: 0 auto;
        }

        @keyframes dot-flashing {
            0% { background-color: var(--accent); }
            50%, 100% { background-color: rgba(33, 82, 255, 0.2); }
        }

        .chart-container { position: relative; height: 300px; width: 100%; }
        .section-title { font-weight: 800; color: var(--text-main); margin-bottom: 1.5rem; display: flex; align-items: center; gap: 10px; }
        .section-title::before { content: ''; width: 4px; height: 20px; background: var(--accent); border-radius: 4px; }
    </style>
</head>
<body>

<div class="container">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="<?= site_url('admin/dashboard') ?>" class="btn-back"><i class="fas fa-arrow-left me-2"></i> Dashboard</a>
        <span class="badge rounded-pill px-3 py-2" style="background: #ecfdf5; color: #059669; border: 1px solid #10b981;">
            FILTROS ACTIVOS: <?= count(array_filter($filters)) ?>
        </span>
    </div>

    <div class="row align-items-center mb-5">
        <div class="col-md-8">
            <h1 class="fw-bold mb-1">Event <span style="color:var(--accent)">Tracking</span></h1>
            <p class="text-muted mb-0">Análisis detallado de comportamiento y actividad de usuarios</p>
        </div>
        <div class="col-md-4 text-end">
            <button id="btn-ai-analyze" class="btn-ai-analyze">
                <i class="fas fa-brain me-2"></i> Consultor IA CRO
            </button>
        </div>
    </div>

    <!-- AI Insights Area -->
    <div id="ai-container" class="ai-card" style="display: none;">
        <div class="d-flex align-items-center gap-3 mb-3">
            <div class="bg-primary text-white p-2 rounded-circle shadow-sm" style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <i class="fas fa-magic"></i>
            </div>
            <h4 class="fw-bold mb-0">Conclusiones de la Inteligencia Artificial</h4>
        </div>
        
        <div id="ai-loader" class="ai-loader">
            <div class="dot-flashing"></div>
            <p class="mt-3 text-muted small fw-bold">Analizando patrones de comportamiento...</p>
        </div>

        <div id="ai-response" class="ai-response">
            <!-- Content from AI -->
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar">
        <form action="" method="get" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label small fw-bold text-muted">Tipo de Evento</label>
                <select name="event_name" class="form-select">
                    <option value="">Todos los eventos</option>
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
                <button type="submit" class="btn-filter flex-grow-1"><i class="fas fa-search me-2"></i>Filtrar</button>
                <a href="<?= site_url('admin/event-tracking') ?>" class="btn-reset"><i class="fas fa-redo"></i></a>
            </div>
        </form>
    </div>

    <!-- Stats and Charts... -->
    <div class="row g-4 mb-5">
        <div class="col-md-3"><div class="metric-card"><div class="metric-label">Total Eventos</div><div class="metric-value"><?= number_format($totalEvents) ?></div></div></div>
        <div class="col-md-3"><div class="metric-card"><div class="metric-label">Visitantes Únicos</div><div class="metric-value"><?= number_format($uniqueVisitors) ?></div></div></div>
        <div class="col-md-3"><div class="metric-card"><div class="metric-label">Eventos / Visitante</div><div class="metric-value"><?= $uniqueVisitors > 0 ? round($totalEvents / $uniqueVisitors, 1) : 0 ?></div></div></div>
        <div class="col-md-3"><div class="metric-card"><div class="metric-label">Filtro Activo</div><div class="metric-value" style="font-size: 1.5rem;"><?= $filters['event_name'] ?: 'General' ?></div></div></div>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-lg-8"><div class="metric-card"><h5 class="section-title">Tendencia</h5><div class="chart-container"><canvas id="timelineChart"></canvas></div></div></div>
        <div class="col-lg-4"><div class="metric-card"><h5 class="section-title">Distribución</h5><div class="chart-container"><canvas id="eventsChart"></canvas></div></div></div>
    </div>

    <!-- Recent Activity -->
    <div class="metric-card mb-5">
        <h5 class="section-title">Actividad Reciente</h5>
        <div class="table-responsive">
            <table class="table table-custom mb-0">
                <thead><tr><th>Acción</th><th>Ruta</th><th>Elemento</th><th>Usuario / Anon</th><th class="text-end">Fecha</th></tr></thead>
                <tbody>
                    <?php foreach($recentEvents as $event): ?>
                    <tr>
                        <td><span class="badge-event"><?= $event['event_name'] ?></span></td>
                        <td class="text-truncate" style="max-width: 250px;"><?= parse_url($event['page'], PHP_URL_PATH) ?></td>
                        <td class="text-muted small"><?= esc($event['element']) ?></td>
                        <td class="small"><?php if($event['user_id']): ?><span class="text-primary fw-bold">ID: <?= $event['user_id'] ?></span><?php else: ?><span class="text-muted">Anon: <?= substr($event['anonymous_id'], -8) ?></span><?php endif; ?></td>
                        <td class="text-end small text-muted"><?= date('H:i d/m', strtotime($event['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    // IA Analysis Script
    document.getElementById('btn-ai-analyze').addEventListener('click', function() {
        const container = document.getElementById('ai-container');
        const loader = document.getElementById('ai-loader');
        const responseDiv = document.getElementById('ai-response');
        
        container.style.display = 'block';
        loader.style.display = 'block';
        responseDiv.style.display = 'none';
        
        fetch('<?= site_url('admin/event-tracking/ai-analyze') ?>')
            .then(res => res.json())
            .then(data => {
                loader.style.display = 'none';
                responseDiv.style.display = 'block';
                // Use marked.js for nice markdown rendering
                responseDiv.innerHTML = marked.parse(data.analysis);
            })
            .catch(err => {
                loader.style.display = 'none';
                responseDiv.style.display = 'block';
                responseDiv.innerHTML = '<p class="text-danger">Error al conectar con la IA. Por favor, inténtalo de nuevo.</p>';
            });
    });

    Chart.defaults.color = '#64748b';
    const timelineCtx = document.getElementById('timelineChart').getContext('2d');
    new Chart(timelineCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode(array_column($timeline, 'date')) ?>,
            datasets: [{ label: 'Eventos', data: <?= json_encode(array_column($timeline, 'total')) ?>, borderColor: '#2152ff', backgroundColor: 'rgba(33, 82, 255, 0.05)', fill: true, tension: 0.4 }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
    });

    const eventsCtx = document.getElementById('eventsChart').getContext('2d');
    new Chart(eventsCtx, {
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
