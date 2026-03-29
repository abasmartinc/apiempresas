<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => $title ?? 'Análisis de Tendencias - Radar PRO',
        'excerptText' => 'Visualiza la evolución de sectores y provincias en tiempo real.',
    ]) ?>
    <link rel="stylesheet" href="<?= base_url('public/css/radar.css?v=' . (file_exists(FCPATH . 'public/css/radar.css') ? filemtime(FCPATH . 'public/css/radar.css') : time())) ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="ae-radar-page">
    <div class="ae-radar-page__shell">
        
        <aside class="ae-radar-page__sidebar">
            <div class="ae-radar-page__brand">
                <a href="<?=site_url() ?>" class="ae-radar-page__brand-header">
                    <div class="brand-text">
                        <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                    </div>
                </a>
            </div>
            <div class="ae-radar-page__sidebar-body">
                <div class="ae-radar-page__nav-group">
                    <span class="ae-radar-page__nav-label">Radar</span>
                    <a href="<?= site_url('radar') ?>" class="ae-radar-page__nav-link">
                        <span class="ae-radar-page__nav-icon">📊</span>
                        Dashboard principal
                    </a>
                    <a href="<?= site_url('radar/favoritos') ?>" class="ae-radar-page__nav-link">
                        <span class="ae-radar-page__nav-icon">⭐</span>
                        Mis favoritos
                    </a>
                    <a href="<?= site_url('radar/kanban') ?>" class="ae-radar-page__nav-link">
                        <span class="ae-radar-page__nav-icon">📋</span>
                        Embudo (Kanban)
                    </a>
                    <a href="<?= site_url('radar/trends') ?>" class="ae-radar-page__nav-link is-active">
                        <span class="ae-radar-page__nav-icon">📈</span>
                        Análisis de Tendencias
                    </a>
                    
                    <a href="<?= site_url('billing/invoices') ?>" class="ae-radar-page__nav-link">
                        <span class="ae-radar-page__nav-icon">🧾</span>
                        Mis facturas
                    </a>
                </div>
            </div>
            <div class="ae-radar-page__sidebar-footer">
                <a href="<?= site_url('dashboard') ?>" class="ae-radar-page__nav-link">
                    <span class="ae-radar-page__nav-icon">🏠</span>
                    Volver al portal
                </a>
            </div>
        </aside>

        <main class="ae-radar-page__main">
            <header class="ae-radar-page__topbar">
                <div class="ae-radar-page__breadcrumb">
                    <span>Radar PRO</span>
                    <span>/</span>
                    <strong>Análisis de Tendencias</strong>
                </div>
            </header>

            <div class="ae-radar-page__content">
                <div class="ae-radar-page__container">
                    
                    <section class="ae-radar-page__hero ae-radar-page__hero--pro" style="background: white; border: 1px solid rgba(15, 23, 42, 0.08); box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); margin-bottom: 20px; padding: 16px 32px;">
                        <div class="ae-radar-page__hero-grid">
                            <div>
                                <div class="ae-radar-page__eyebrow" style="background: #eff6ff; border-color: #dbeafe; color: #2563eb; padding: 6px 14px; font-size: 11px;">
                                    <span class="ae-radar-page__pulse" style="background: #2563eb; width: 8px; height: 8px;"></span>
                                    Business Intelligence · Datos Reales
                                </div>
                                <h1 class="ae-radar-page__hero-title" style="margin-top: 8px; margin-bottom: 4px; font-size: 24px;">
                                    Tendencias de <span class="ae-radar-page__hero-title-grad">Mercado</span>
                                </h1>
                                <p class="ae-radar-page__hero-text" style="color: #64748b; max-width: 650px; line-height: 1.4; font-size: 13.5px; margin-top: 4px;">
                                    Analiza el ritmo de creación de empresas por provincia y sector para identificar nichos calientes.
                                </p>
                            </div>
                        </div>
                    </section>

                    <!-- Filtros de Tendencias -->
                    <div class="ae-radar-page__filters" style="background: white; padding: 20px; border-radius: 20px; border: 1px solid #e2e8f0; margin-bottom: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                        <div style="display: flex; gap: 20px; align-items: flex-end;">
                            <div style="flex: 1;">
                                <label style="display: block; font-size: 12px; font-weight: 800; color: #64748b; margin-bottom: 8px; text-transform: uppercase;">Provincia</label>
                                <select id="filter-province" class="ae-radar-filter" style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1px solid #e2e8f0; font-weight: 600; color: #1e293b; background: #f8fafc;">
                                    <option value="">Toda España</option>
                                    <?php foreach ($provinces as $p): ?>
                                        <option value="<?= esc(str_replace(' ', '-', strtolower($p['name']))) ?>"><?= esc($p['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div style="flex: 1;">
                                <label style="display: block; font-size: 12px; font-weight: 800; color: #64748b; margin-bottom: 8px; text-transform: uppercase;">Sector (Sección CNAE)</label>
                                <select id="filter-section" class="ae-radar-filter" style="width: 100%; padding: 10px 14px; border-radius: 12px; border: 1px solid #e2e8f0; font-weight: 600; color: #1e293b; background: #f8fafc;">
                                    <option value="">Todos los sectores</option>
                                    <?php foreach ($sections as $s): ?>
                                        <option value="<?= $s['id'] ?>"><?= esc($s['name']) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <button onclick="loadTrendData()" style="padding: 10px 24px; background: #2563eb; color: white; border: none; border-radius: 12px; font-weight: 800; cursor: pointer; transition: all 0.2s;">
                                Actualizar Análisis
                            </button>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px;">
                        <!-- Gráfica de Evolución -->
                        <div style="background: white; padding: 32px; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
                            <h3 style="margin: 0 0 24px 0; font-size: 18px; font-weight: 900; color: #0f172a; display: flex; align-items: center; gap: 10px;">
                                <span style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #eff6ff; color: #2563eb; border-radius: 8px;"><i class="fas fa-chart-line"></i></span>
                                Evolución de Constituciones
                            </h3>
                            <div style="height: 400px; position: relative;">
                                <div id="evolution-loading" style="position: absolute; inset: 0; background: rgba(255,255,255,0.8); z-index: 10; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 12px;">
                                    <div class="ae-spinner" style="width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #2563eb; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                                    <p style="margin-top: 15px; font-weight: 700; color: #1e293b; font-size: 14px;">Buscando datos...</p>
                                </div>
                                <canvas id="evolutionChart"></canvas>
                            </div>
                        </div>

                        <!-- Top Sectores -->
                        <div style="background: white; padding: 32px; border-radius: 24px; border: 1px solid #e2e8f0; box-shadow: 0 4px 20px rgba(0,0,0,0.04);">
                            <h3 style="margin: 0 0 24px 0; font-size: 18px; font-weight: 900; color: #0f172a; display: flex; align-items: center; gap: 10px;">
                                <span style="display: flex; align-items: center; justify-content: center; width: 32px; height: 32px; background: #fef3c7; color: #d97706; border-radius: 8px;"><i class="fas fa-fire"></i></span>
                                Sectores Calientes
                            </h3>
                            <div id="sectors-list" style="display: flex; flex-direction: column; gap: 16px; position: relative; min-height: 200px;">
                                <div id="sectors-loading" style="position: absolute; inset: 0; background: rgba(255,255,255,0.8); z-index: 10; display: flex; flex-direction: column; align-items: center; justify-content: center; border-radius: 12px;">
                                    <div class="ae-spinner" style="width: 30px; height: 30px; border: 3px solid #f3f3f3; border-top: 3px solid #d97706; border-radius: 50%; animation: spin 1s linear infinite;"></div>
                                </div>
                                <!-- Se cargará dinámicamente -->
                            </div>
                            <div style="margin-top: 32px; padding-top: 24px; border-top: 1px dashed #e2e8f0; position: relative;">
                                <div style="height: 300px; position: relative;">
                                    <canvas id="sectorsChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</div>

<style>
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let evolutionChart = null;
let sectorsChart = null;

$(document).ready(function() {
    loadTrendData();
});

function loadTrendData() {
    const province = $('#filter-province').val();
    const section = $('#filter-section').val();
    
    $('#evolution-loading, #sectors-loading').show();
    
    fetch('<?= site_url('radar/trend-data') ?>?' + new URLSearchParams({
        provincia: province,
        seccion: section
    }))
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok');
        return response.json();
    })
    .then(data => {
        $('#evolution-loading, #sectors-loading').hide();
        if (data.status === 'success') {
            renderEvolutionChart(data.evolution);
            renderSectorsChart(data.sectors);
            renderSectorsList(data.sectors);
        } else {
            console.error('API Error:', data.message);
        }
    })
    .catch(error => {
        $('#evolution-loading, #sectors-loading').hide();
        console.error('Fetch Error:', error);
        alert('Error al cargar los datos de tendencias. Por favor, intenta de nuevo.');
    });
}

function renderEvolutionChart(data) {
    const ctx = document.getElementById('evolutionChart').getContext('2d');
    
    const labels = data.map(item => {
        const [year, month] = item.month.split('-');
        const names = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
        return names[parseInt(month) - 1] + ' ' + year;
    });
    const values = data.map(item => item.total);

    if (evolutionChart) evolutionChart.destroy();

    evolutionChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'Empresas constituidas',
                data: values,
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                fill: true,
                tension: 0.4,
                borderWidth: 4,
                pointBackgroundColor: '#ffffff',
                pointBorderColor: '#2563eb',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.05)' },
                    ticks: { font: { weight: 'bold' } }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { weight: 'bold' } }
                }
            }
        }
    });
}

function renderSectorsChart(data) {
    const ctx = document.getElementById('sectorsChart').getContext('2d');
    const labels = data.map(item => item.label.substring(0, 15) + '...');
    const values = data.map(item => item.total);

    if (sectorsChart) sectorsChart.destroy();

    sectorsChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: labels,
            datasets: [{
                data: values,
                backgroundColor: [
                    '#2563eb', '#8b5cf6', '#f59e0b', '#10b981', 
                    '#f43f5e', '#06b6d4', '#84cc16', '#64748b'
                ],
                borderWidth: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '70%',
            plugins: {
                legend: { display: false }
            }
        }
    });
}

function renderSectorsList(data) {
    const container = $('#sectors-list');
    container.empty();
    
    data.slice(0, 5).forEach((item, index) => {
        const colors = ['#2563eb', '#8b5cf6', '#f59e0b', '#10b981', '#f43f5e'];
        const percentage = Math.round((item.total / data[0].total) * 100);
        
        container.append(`
            <div>
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                    <span style="font-size: 13px; font-weight: 700; color: #1e293b;">${item.label}</span>
                    <span style="font-size: 12px; font-weight: 800; color: #64748b;">${item.total}</span>
                </div>
                <div style="width: 100%; height: 6px; background: #f1f5f9; border-radius: 10px; overflow: hidden;">
                    <div style="width: ${percentage}%; height: 100%; background: ${colors[index % colors.length]}; border-radius: 10px;"></div>
                </div>
            </div>
        `);
    });
}
</script>

</body>
</html>
