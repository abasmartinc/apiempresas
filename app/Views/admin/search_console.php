<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .page-padding { padding: 40px 0 80px; }
        .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 48px; }
        .title { font-size: 2.8rem; margin-bottom: 8px; font-weight: 800; color: #1e293b; letter-spacing: -1px; line-height: 1.1; }
        .title .grad { background: linear-gradient(135deg, #2152ff 0%, #1d4ed8 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent; }
        .subtitle { color: #64748b; font-size: 1.1rem; }
        
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 24px;
            margin-bottom: 24px;
        }
        
        .kpi-card {
            position: relative;
            overflow: hidden;
            background: white;
            border-radius: 24px;
            padding: 32px;
            border: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .kpi-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 35px -10px rgba(0, 0, 0, 0.1);
        }
        
        .kpi-card::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 120px; height: 120px;
            background: currentColor;
            opacity: 0.03;
            border-radius: 0 0 0 100%;
            pointer-events: none;
        }
        
        .kpi-label {
            font-size: 0.85rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .kpi-value {
            font-size: 2.5rem;
            font-weight: 900;
            color: #1e293b;
            line-height: 1;
            margin-bottom: 16px;
            letter-spacing: -0.02em;
        }
        
        .kpi-footer {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.85rem;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
            min-height: 40px;
        }

        .skeleton {
            display: inline-block;
            height: 1em;
            width: 80px;
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite linear;
            border-radius: 4px;
            vertical-align: middle;
        }

        .kpi-value .skeleton {
            height: 2.5rem;
            width: 120px;
            margin-bottom: 4px;
        }

        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }

        .info-card {
            background: white;
            border-radius: 20px;
            padding: 24px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
            border-left: 4px solid #2152ff;
        }
        
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 16px;
            color: #991b1b;
            padding: 24px;
            margin-bottom: 30px;
        }

        .data-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }

        @media (max-width: 991px) {
            .data-grid { grid-template-columns: 1fr; }
        }

        .data-card {
            background: white;
            border-radius: 20px;
            border: 1px solid rgba(0, 0, 0, 0.05);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            margin-bottom: 24px;
        }

        .data-header {
            padding: 20px 24px;
            border-bottom: 1px solid #f1f5f9;
            background: #f8fafc;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .data-header h3 {
            font-size: 1.1rem;
            font-weight: 700;
            color: #1e293b;
            margin: 0;
        }

        .gsc-table {
            width: 100%;
            border-collapse: collapse;
        }

        .gsc-table th, .gsc-table td {
            padding: 12px 24px;
            text-align: left;
            border-bottom: 1px solid #f1f5f9;
            font-size: 0.9rem;
        }

        .gsc-table th {
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            background: #fcfcfd;
        }

        .gsc-table td { color: #334155; }
        .gsc-table tr:hover td { background: #f8fafc; }
        .gsc-table tr:last-child td { border-bottom: none; }
        
        .text-right { text-align: right !important; }
        
        .gsc-term { font-weight: 600; color: #2152ff; }
        .gsc-url { color: #64748b; font-size: 0.85rem; word-break: break-all; }
        .gsc-metric { font-weight: 700; font-family: 'JetBrains Mono', monospace; }
        
        #gscChart {
            max-height: 280px;
        }
    </style>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin page-padding">
    <div class="page-header">
        <div>
            <h1 class="title">Google <span class="grad">Search Console</span></h1>
            <p class="subtitle">Métricas SEO cargadas directamente desde tu propiedad web.</p>
        </div>
        <div class="flex-gap-10">
            <button id="refreshGscBtn" class="btn" style="background: linear-gradient(135deg, #2152ff 0%, #1d4ed8 100%); color: white; border: none;">
                <i class="fas fa-sync-alt mr-2"></i> Recargar
            </button>
            <a href="<?= site_url('admin/dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <!-- Contenedor general informacional -->
    <div class="info-card">
        <h6 style="color: #2152ff; font-weight: 700; font-size: 0.85rem; letter-spacing: 0.05em; text-transform: uppercase; margin-bottom: 8px;">Integración API Activa</h6>
        <p style="color: #64748b; font-size: 1rem; margin: 0;">Los siguientes datos corresponden a los <strong>últimos 30 días</strong> frente a los 30 días anteriores. Se cachean durante 6 horas para no agotar el límite de la API.</p>
    </div>

    <!-- Contenedor de Error -->
    <div id="gscError" class="alert-error" style="display: none;">
        <h4 style="font-weight: 700; font-size: 1.1rem; margin-bottom: 12px;"><i class="fas fa-exclamation-triangle mr-2"></i> Error de conexión</h4>
        <p id="gscErrorMessage" style="margin-bottom: 16px;">Hubo un problema al conectar con la API.</p>
        <hr style="border-color: #fecaca; margin: 16px 0;">
        <p class="mb-0 text-sm">Asegúrate de haber creado la cuenta de servicio, dado permisos en Search Console y guardado el JSON en <code>writable/credentials/google-service-account.json</code> como se instruyó.</p>
    </div>

    <!-- Contenedor Principal de Datos -->
    <div id="gscDataContainer">
        <!-- KPIs Section -->
        <div class="kpi-grid">
            <!-- Clics Totales -->
            <div class="kpi-card" style="color: #6366f1;">
                <div>
                    <div class="kpi-label">
                        <i class="fas fa-mouse-pointer" style="width: 16px;"></i>
                        Clicks (30 días)
                    </div>
                    <div class="kpi-value" id="gscClicks"><span class="skeleton"></span></div>
                </div>
                <div class="kpi-footer" id="gscClicksTrend">
                    <span style="color: #64748b;">Calculando tendencia...</span>
                </div>
            </div>

            <!-- Impresiones Totales -->
            <div class="kpi-card" style="color: #10b981;">
                <div>
                    <div class="kpi-label">
                        <i class="fas fa-eye" style="width: 16px;"></i>
                        Impresiones
                    </div>
                    <div class="kpi-value" id="gscImpressions"><span class="skeleton"></span></div>
                </div>
                <div class="kpi-footer" id="gscImpressionsTrend">
                    <span style="color: #64748b;">Calculando tendencia...</span>
                </div>
            </div>

            <!-- CTR -->
            <div class="kpi-card" style="color: #f59e0b;">
                <div>
                    <div class="kpi-label">
                        <i class="fas fa-percentage" style="width: 16px;"></i>
                        CTR Medio
                    </div>
                    <div class="kpi-value" id="gscCtr"><span class="skeleton"></span></div>
                </div>
                <div class="kpi-footer" id="gscCtrTrend">
                    <span style="color: #64748b;">Calculando tendencia...</span>
                </div>
            </div>

            <!-- Posición Media -->
            <div class="kpi-card" style="color: #8b5cf6;">
                <div>
                    <div class="kpi-label">
                        <i class="fas fa-sort-numeric-down" style="width: 16px;"></i>
                        Posición Media
                    </div>
                    <div class="kpi-value" id="gscPosition"><span class="skeleton"></span></div>
                </div>
                <div class="kpi-footer" id="gscPositionTrend">
                    <span style="color: #64748b;">Calculando tendencia...</span>
                </div>
            </div>
        </div>

        <!-- Chart Section -->
        <div class="data-card">
            <div class="data-header">
                <div style="width: 40px; height: 40px; border-radius: 10px; background: #fdf4ff; color: #a21caf; display: flex; align-items: center; justify-content: center;">
                    <i class="fas fa-chart-area"></i>
                </div>
                <h3>Evolución Diaria (Últimos 30 días)</h3>
            </div>
            <div style="padding: 24px;">
                <canvas id="gscChart"></canvas>
            </div>
        </div>

        <!-- Tables Section (Queries & Pages) -->
        <div class="data-grid">
            <!-- Top Queries -->
            <div class="data-card" style="margin-bottom: 0;">
                <div class="data-header">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #eff6ff; color: #2152ff; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>Top Consultas (Queries)</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="gsc-table">
                        <thead>
                            <tr>
                                <th>Término</th>
                                <th class="text-right">Clics</th>
                                <th class="text-right">Pos</th>
                            </tr>
                        </thead>
                        <tbody id="gscQueriesBody">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Pages -->
            <div class="data-card" style="margin-bottom: 0;">
                <div class="data-header">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #fdf2f8; color: #db2777; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-file-alt"></i>
                    </div>
                    <h3>Top Páginas</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="gsc-table">
                        <thead>
                            <tr>
                                <th>URL</th>
                                <th class="text-right">Clics</th>
                                <th class="text-right">Impr.</th>
                            </tr>
                        </thead>
                        <tbody id="gscPagesBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Tables Section (Countries & Devices) -->
        <div class="data-grid" style="margin-top: 24px;">
            <!-- Top Countries -->
            <div class="data-card" style="margin-bottom: 0;">
                <div class="data-header">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #f0fdf4; color: #16a34a; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-globe"></i>
                    </div>
                    <h3>Top Países</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="gsc-table">
                        <thead>
                            <tr>
                                <th>País</th>
                                <th class="text-right">Clics</th>
                                <th class="text-right">CTR</th>
                            </tr>
                        </thead>
                        <tbody id="gscCountriesBody">
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Top Devices -->
            <div class="data-card" style="margin-bottom: 0;">
                <div class="data-header">
                    <div style="width: 40px; height: 40px; border-radius: 10px; background: #fffbeb; color: #f59e0b; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Top Dispositivos</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="gsc-table">
                        <thead>
                            <tr>
                                <th>Dispositivo</th>
                                <th class="text-right">Clics</th>
                                <th class="text-right">Impr.</th>
                            </tr>
                        </thead>
                        <tbody id="gscDevicesBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let gscChartInstance = null;

    function formatNumber(num) {
        return new Intl.NumberFormat('es-ES').format(num);
    }
    
    function generateTrendHtml(current, previous, isReverseGood = false) {
        if (!previous || previous === 0) return '<span style="color: #64748b;">Sin datos previos</span>';
        
        let diff = current - previous;
        let pChange = (diff / previous) * 100;
        let formattedChange = Math.abs(pChange).toFixed(1) + '%';
        
        let isPositiveChange = diff > 0;
        if (isReverseGood) {
            isPositiveChange = diff < 0; // bajando es mejor en posicion seo
        }
        
        let color = isPositiveChange ? '#16a34a' : '#dc2626';
        if (Math.abs(diff) < 0.01) color = '#f59e0b';
        
        let iconClass = diff > 0 ? 'fa-arrow-up' : (diff < 0 ? 'fa-arrow-down' : 'fa-minus');
        
        return `<span style="color: ${color}; font-weight: 700;"><i class="fas ${iconClass}"></i> ${formattedChange}</span>
                <span style="color: #cbd5e1;">•</span>
                <span style="color: #64748b;">vs prev.</span>`;
    }

    function setSkeleton() {
        const skel = '<span class="skeleton"></span>';
        document.getElementById('gscClicks').innerHTML = skel;
        document.getElementById('gscImpressions').innerHTML = skel;
        document.getElementById('gscCtr').innerHTML = skel;
        document.getElementById('gscPosition').innerHTML = skel;
        
        const skelText = '<span style="color: #64748b;">Calculando tendencia...</span>';
        document.getElementById('gscClicksTrend').innerHTML = skelText;
        document.getElementById('gscImpressionsTrend').innerHTML = skelText;
        document.getElementById('gscCtrTrend').innerHTML = skelText;
        document.getElementById('gscPositionTrend').innerHTML = skelText;

        const tableSkel = `
            <tr><td colspan="3"><div class="skeleton" style="width: 100%;"></div></td></tr>
            <tr><td colspan="3"><div class="skeleton" style="width: 80%;"></div></td></tr>
            <tr><td colspan="3"><div class="skeleton" style="width: 90%;"></div></td></tr>
        `;
        document.getElementById('gscQueriesBody').innerHTML = tableSkel;
        document.getElementById('gscPagesBody').innerHTML = tableSkel;
        document.getElementById('gscCountriesBody').innerHTML = tableSkel;
        document.getElementById('gscDevicesBody').innerHTML = tableSkel;
    }

    function renderChart(dailyData) {
        if (gscChartInstance) {
            gscChartInstance.destroy();
        }

        const labels = dailyData.map(d => d.key);
        const clicksData = dailyData.map(d => d.clicks);
        const impressionsData = dailyData.map(d => d.impressions);

        const ctx = document.getElementById('gscChart').getContext('2d');
        gscChartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Clics',
                        data: clicksData,
                        borderColor: '#2152ff',
                        backgroundColor: 'rgba(33, 82, 255, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y'
                    },
                    {
                        label: 'Impresiones',
                        data: impressionsData,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.05)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y1'
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        backgroundColor: 'rgba(15, 23, 42, 0.9)',
                        titleFont: { size: 13, family: 'Inter' },
                        bodyFont: { size: 13, family: 'Inter' },
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: { type: 'linear', display: true, position: 'left',
                         grid: { borderDash: [4, 4], color: '#f1f5f9' },
                         ticks: { color: '#64748b' } },
                    y1: { type: 'linear', display: true, position: 'right',
                          grid: { drawOnChartArea: false },
                          ticks: { color: '#64748b' } },
                    x: { grid: { display: false }, ticks: { color: '#94a3b8', maxTicksLimit: 10 } }
                }
            }
        });
    }

    function loadGscData() {
        const errorContainer = document.getElementById('gscError');
        const dataContainer = document.getElementById('gscDataContainer');
        const btn = document.getElementById('refreshGscBtn');
        const icon = btn.querySelector('i');
        
        errorContainer.style.display = 'none';
        btn.disabled = true;
        btn.style.opacity = '0.7';
        icon.classList.add('fa-spin');
        
        setSkeleton();
        
        fetch('<?= site_url('admin/search-console/kpis') ?>', {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            btn.disabled = false;
            btn.style.opacity = '1';
            icon.classList.remove('fa-spin');
            
            if (data.status === 'error') {
                document.getElementById('gscErrorMessage').innerText = data.message || 'Error desconocido';
                errorContainer.style.display = 'block';
                return;
            }
            
            // Render KPIs
            const current = data.current;
            const prev = data.previous;
            
            document.getElementById('gscClicks').innerText = formatNumber(current.clicks);
            document.getElementById('gscClicksTrend').innerHTML = generateTrendHtml(current.clicks, prev.clicks);
            
            document.getElementById('gscImpressions').innerText = formatNumber(current.impressions);
            document.getElementById('gscImpressionsTrend').innerHTML = generateTrendHtml(current.impressions, prev.impressions);
            
            document.getElementById('gscCtr').innerHTML = current.ctr + '<span style="font-size: 1.5rem; color: #94a3b8; font-weight: 700; margin-left: 2px;">%</span>';
            document.getElementById('gscCtrTrend').innerHTML = generateTrendHtml(current.ctr, prev.ctr);
            
            document.getElementById('gscPosition').innerText = current.position;
            document.getElementById('gscPositionTrend').innerHTML = generateTrendHtml(current.position, prev.position, true);

            // Render Chart
            if (data.daily && data.daily.length > 0) {
                document.getElementById('gscChart').style.display = 'block';
                renderChart(data.daily);
            }

            // Render Queries Table
            let queriesHtml = '';
            if (data.queries && data.queries.length > 0) {
                data.queries.forEach(q => {
                    queriesHtml += `<tr>
                        <td><span class="gsc-term">${q.key}</span></td>
                        <td class="text-right gsc-metric">${formatNumber(q.clicks)}</td>
                        <td class="text-right gsc-metric" style="color: #16a34a;">${q.position}</td>
                    </tr>`;
                });
            } else { queriesHtml = '<tr><td colspan="3" class="text-center text-muted">Sin datos</td></tr>'; }
            document.getElementById('gscQueriesBody').innerHTML = queriesHtml;

            // Render Pages Table
            let pagesHtml = '';
            if (data.pages && data.pages.length > 0) {
                data.pages.forEach(p => {
                    let displayUrl = p.key.replace('https://apiempresas.es', '');
                    if (displayUrl === '') displayUrl = '/';
                    pagesHtml += `<tr>
                        <td><span class="gsc-url" title="${p.key}">${displayUrl}</span></td>
                        <td class="text-right gsc-metric">${formatNumber(p.clicks)}</td>
                        <td class="text-right gsc-metric" style="color: #64748b;">${formatNumber(p.impressions)}</td>
                    </tr>`;
                });
            } else { pagesHtml = '<tr><td colspan="3" class="text-center text-muted">Sin datos</td></tr>'; }
            document.getElementById('gscPagesBody').innerHTML = pagesHtml;

            // Render Countries Table
            let countriesHtml = '';
            // Función heurística básica para mapear ISO a emojis de bandera o nombre español no es nativa, 
            // pero podemos usar Intl.DisplayNames si la clave GSC es "ESP", "MEX". GSC usa ISO 3166-1 alpha-3 tipicamente (ej. esp, mex)
            if (data.countries && data.countries.length > 0) {
                data.countries.forEach(c => {
                    countriesHtml += `<tr>
                        <td><span class="gsc-term" style="text-transform: uppercase;">${c.key}</span></td>
                        <td class="text-right gsc-metric">${formatNumber(c.clicks)}</td>
                        <td class="text-right gsc-metric" style="color: #64748b;">${c.ctr}%</td>
                    </tr>`;
                });
            } else { countriesHtml = '<tr><td colspan="3" class="text-center text-muted">Sin datos</td></tr>'; }
            document.getElementById('gscCountriesBody').innerHTML = countriesHtml;

            // Render Devices Table
            let devicesHtml = '';
            const deviceIcons = { 'DESKTOP': '<i class="fas fa-desktop mr-2 text-primary"></i> Ordenador', 'MOBILE': '<i class="fas fa-mobile-alt mr-2" style="color: #10b981;"></i> Móvil', 'TABLET': '<i class="fas fa-tablet-alt mr-2" style="color: #f59e0b;"></i> Tablet' };
            if (data.devices && data.devices.length > 0) {
                data.devices.forEach(d => {
                    let devName = deviceIcons[d.key] || d.key;
                    devicesHtml += `<tr>
                        <td><span style="color: #475569; font-weight: 600;">${devName}</span></td>
                        <td class="text-right gsc-metric">${formatNumber(d.clicks)}</td>
                        <td class="text-right gsc-metric" style="color: #64748b;">${formatNumber(d.impressions)}</td>
                    </tr>`;
                });
            } else { devicesHtml = '<tr><td colspan="3" class="text-center text-muted">Sin datos</td></tr>'; }
            document.getElementById('gscDevicesBody').innerHTML = devicesHtml;
        })
        .catch(error => {
            btn.disabled = false;
            btn.style.opacity = '1';
            icon.classList.remove('fa-spin');
            document.getElementById('gscErrorMessage').innerText = error.message || 'Fallo de red al conectar con el servidor';
            errorContainer.style.display = 'block';
        });
    }

    loadGscData();
    document.getElementById('refreshGscBtn').addEventListener('click', loadGscData);
});
</script>

<?= view('partials/footer') ?>
</body>
</html>
