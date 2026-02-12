<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title]) ?>
    <style>
        .kpi-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }
        
        .kpi-card {
            position: relative;
            overflow: hidden;
            background: white;
            border-radius: 20px;
            padding: 24px;
            border: 1px solid rgba(255, 255, 255, 0.7);
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);
            transition: all 0.4s ease;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }
        
        .kpi-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px -10px rgba(0, 0, 0, 0.1);
            cursor: pointer;
            border-color: currentColor;
        }

        .kpi-card.active {
            border: 2px solid currentColor;
            background: #f8fafc;
        }
        
        .kpi-label {
            font-size: 0.75rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        .kpi-value {
            font-size: 1.8rem;
            font-weight: 900;
            color: #1e293b;
            line-height: 1.2;
        }

        .text-red { color: #ef4444; }
        .text-green { color: #10b981; }
        .text-primary { color: #2152ff; }

        /* Skeleton Loading */
        .skeleton {
            display: inline-block;
            height: 2rem;
            width: 120px;
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite linear;
            border-radius: 8px;
            vertical-align: middle;
        }

        @keyframes shimmer {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
    </style>
</head>
<body class="admin-body">
<div class="bg-halo" aria-hidden="true"></div>

<?= view('partials/header_admin') ?>

<main class="container-admin page-padding">
    <div class="page-header">
        <h1 class="title">
            Gestión de Empresas 
            <?php if(isset($pager)): ?>
                <span class="text-slate font-normal" style="font-size: 1rem;">(<?= $pager->getTotal() ?> resultados)</span>
            <?php endif; ?>
        </h1>
        <div class="flex-gap-10 flex-center">
            <div id="stats-info" class="text-08 text-slate-400 mr-4">
                Actualizado: <span id="stats-updated-at">Nunca</span>
                <button id="btn-refresh-stats" class="btn ghost btn-sm ml-2" title="Recalcular estadísticas pesadas (puede tardar)">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" style="width: 14px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0 3.181 3.183a8.25 8.25 0 0 0 13.803-3.7M4.031 9.865a8.25 8.25 0 0 1 13.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    Refrescar
                </button>
            </div>
            <a href="<?= site_url('admin/companies/create') ?>" class="btn">Nueva Empresa</a>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
        </div>
    </div>

    <!-- KPIs -->
    <div class="kpi-grid">
        <div class="kpi-card <?= empty($filters['no_cif']) && empty($filters['no_address']) && empty($filters['no_status']) && empty($filters['no_cnae']) && empty($filters['no_mercantile']) && empty($filters['today']) ? 'active' : '' ?>" style="color: #6366f1;" data-filter="all">
            <div class="kpi-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-.75 4.5H21m-3.75 3.75h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Zm0 3h.008v.008h-.008v-.008Z" />
                </svg>
                Total Empresas
            </div>
            <div class="kpi-value text-primary kpi-async-value" data-type="total"><span class="skeleton"></span></div>
        </div>

        <div class="kpi-card <?= !empty($filters['today']) ? 'active' : '' ?>" style="color: #3b82f6;" data-filter="today">
            <div class="kpi-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5m-9-6h.008v.008H12v-.008ZM12 15h.008v.008H12V15Zm0 3h.008v.008H12V18Zm-3-3h.008v.008H9V15Zm0-3h.008v.008H9v-.008ZM15 15h.008v.008H15V15Zm0-3h.008v.008H15v-.008Z" />
                </svg>
                Empresas Hoy
            </div>
            <div class="kpi-value kpi-async-value" data-type="added_today"><span class="skeleton"></span></div>
        </div>

        <div class="kpi-card <?= !empty($filters['no_cif']) ? 'active' : '' ?>" style="color: #ef4444;" data-filter="no_cif">
            <div class="kpi-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                Sin CIF
            </div>
            <div class="kpi-value kpi-async-value" data-type="sin_cif"><span class="skeleton"></span></div>
        </div>

        <div class="kpi-card <?= !empty($filters['no_address']) ? 'active' : '' ?>" style="color: #f59e0b;" data-filter="no_address">
            <div class="kpi-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                </svg>
                Sin Dirección
            </div>
            <div class="kpi-value kpi-async-value" data-type="sin_direccion"><span class="skeleton"></span></div>
        </div>

        <div class="kpi-card <?= !empty($filters['no_status']) ? 'active' : '' ?>" style="color: #10b981;" data-filter="no_status">
            <div class="kpi-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                Sin Estado
            </div>
            <div class="kpi-value kpi-async-value" data-type="sin_estado"><span class="skeleton"></span></div>
        </div>

        <div class="kpi-card <?= !empty($filters['no_cnae']) ? 'active' : '' ?>" style="color: #8b5cf6;" data-filter="no_cnae">
            <div class="kpi-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 0 1 0 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281Z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                </svg>
                Sin CNAE
            </div>
            <div class="kpi-value kpi-async-value" data-type="sin_cnae"><span class="skeleton"></span></div>
        </div>

        <div class="kpi-card <?= !empty($filters['no_mercantile']) ? 'active' : '' ?>" style="color: #ec4899;" data-filter="no_mercantile">
            <div class="kpi-label">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" style="width: 14px;">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.332A4.833 4.833 0 0 1 12 10.5c-1.393 0-2.671-.587-3.575-1.532V21m10.5 0h.75a.75.75 0 0 0 .75-.75V11.25a.75.75 0 0 0-.75-.75h-1.5a.75.75 0 0 0-.75.75V21m-10.5 0h-.75a.75.75 0 0 1-.75-.75V11.25a.75.75 0 0 1 .75-.75h1.5a.75.75 0 0 1 .75.75V21" />
                </svg>
                Sin Reg. Mercantil
            </div>
            <div class="kpi-value kpi-async-value" data-type="sin_registro_mercantil"><span class="skeleton"></span></div>
        </div>
    </div>

    <!-- Buscador -->
    <div class="card mb-8 p-5">
        <form action="<?= site_url('admin/companies') ?>" method="get" class="flex-gap-10 flex-center" style="display: flex;">
            <input type="text" name="q" class="input flex-1" placeholder="Buscar por Nombre o CIF..." value="<?= esc($q) ?>">
            
            <label class="flex-gap-5 flex-center cursor-pointer select-none text-09 text-slate-500">
                <input type="checkbox" name="no_cif" value="1" <?= isset($filters['no_cif']) && $filters['no_cif'] ? 'checked' : '' ?>>
                Sin CIF
            </label>

            <button type="submit" class="btn">Buscar</button>
            <?php if ($q || (isset($filters['no_cif']) && $filters['no_cif'])): ?>
                <a href="<?= site_url('admin/companies') ?>" class="btn ghost">Limpiar</a>
            <?php endif; ?>
        </form>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert-success">
            <?= session()->getFlashdata('message') ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert-error">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <div id="companies-list-wrapper">
        <?= view('admin/partials/companies_table', ['companies' => $companies, 'pager' => $pager]) ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const wrapper = document.getElementById('companies-list-wrapper');

            wrapper.addEventListener('click', function(e) {
                // Intercepter clicks en los enlaces de paginación
                const link = e.target.closest('.pagination a');
                if (link) {
                    e.preventDefault();
                    const url = link.href;
                    loadPage(url);
                }
            });

            // Interceptar clicks en las tarjetas de KPI
            document.querySelectorAll('.kpi-card').forEach(card => {
                card.addEventListener('click', function() {
                    const filter = this.getAttribute('data-filter');
                    let url = new URL('<?= site_url('admin/companies') ?>');
                    
                    if (filter !== 'all') {
                        url.searchParams.set(filter, '1');
                    }
                    
                    // Mantener búsqueda si existe
                    const currentQ = document.querySelector('input[name="q"]').value;
                    if (currentQ) {
                        url.searchParams.set('q', currentQ);
                    }

                    // Marcar como activa
                    document.querySelectorAll('.kpi-card').forEach(c => c.classList.remove('active'));
                    this.classList.add('active');

                    loadPage(url.toString());
                });
            });

            function loadPage(url) {
                // Añadir un indicador de carga (opcional)
                wrapper.style.opacity = '0.5';
                wrapper.style.pointerEvents = 'none';

                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    wrapper.innerHTML = html;
                    wrapper.style.opacity = '1';
                    wrapper.style.pointerEvents = 'auto';
                    
                    // Actualizar la URL del navegador sin recargar
                    window.history.pushState({path: url}, '', url);
                    
                    // Hacer scroll suave hacia arriba de la tabla si es necesario
                    wrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
                })
                .catch(error => {
                    console.error('Error loading page:', error);
                    wrapper.style.opacity = '1';
                    wrapper.style.pointerEvents = 'auto';
                    alert('Error al cargar la página. Por favor, intente de nuevo.');
                });
            }

            // Manejar el botón de atrás/adelante del navegador
            window.addEventListener('popstate', function() {
                loadPage(window.location.href);
            });

            // --- Carga de KPIs por AJAX con Skeleton ---
            function loadKpis() {
                const kpiElements = document.querySelectorAll('.kpi-async-value');
                const statsInfo = document.getElementById('stats-info');
                const statsUpdateAtEl = document.getElementById('stats-updated-at');
                
                fetch('<?= site_url('admin/kpis-all') ?>', {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    kpiElements.forEach(el => {
                        const type = el.getAttribute('data-type');
                        const valueStr = data[type];
                        
                        if (valueStr !== undefined) {
                            el.innerHTML = valueStr;
                            
                            // Aplicar color rojo si el valor es > 0 (excepto para total y added_today)
                            if (type !== 'total' && type !== 'added_today') {
                                const numericValue = parseInt(valueStr.replace(/\./g, ''));
                                if (numericValue > 0) {
                                    el.classList.add('text-red');
                                } else {
                                    el.classList.add('text-green');
                                }
                            } else if (type === 'added_today') {
                                const numericValue = parseInt(valueStr.replace(/\./g, ''));
                                if (numericValue > 0) {
                                    el.classList.add('text-green');
                                }
                            }
                        }
                    });

                    if (data.stats_updated_at) {
                        statsUpdateAtEl.innerText = data.stats_updated_at;
                    } else {
                        statsUpdateAtEl.innerText = 'Pendiente';
                    }
                    statsInfo.style.display = 'block';
                })
                .catch(err => {
                    console.error('Error loading KPIs', err);
                    kpiElements.forEach(el => {
                        el.innerHTML = '<span class="text-red">Error</span>';
                    });
                });
            }

            // Manejar refresco manual de estadísticas
            const btnRefresh = document.getElementById('btn-refresh-stats');
            if (btnRefresh) {
                btnRefresh.addEventListener('click', function() {
                    this.disabled = true;
                    this.classList.add('loading');
                    const originalHtml = this.innerHTML;
                    this.innerHTML = '⌛ Recalculando...';

                    fetch('<?= site_url('admin/kpis-refresh') ?>', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.status === 'success') {
                            loadKpis();
                        }
                    })
                    .finally(() => {
                        this.disabled = false;
                        this.classList.remove('loading');
                        this.innerHTML = originalHtml;
                    });
                });
            }

            // Iniciar carga de KPIs
            loadKpis();
        });
    </script>
</main>

<?= view('partials/footer') ?>
</body>
</html>

