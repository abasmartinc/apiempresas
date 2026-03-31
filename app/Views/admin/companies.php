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
        </h1>
        <div class="flex-gap-10 flex-center">
            <a href="<?= site_url('admin/companies/create') ?>" class="btn">Nueva Empresa</a>
            <a href="<?= site_url('dashboard') ?>" class="btn ghost">Volver al Dashboard</a>
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

