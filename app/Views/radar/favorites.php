<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => $title ?? 'Mis Favoritos - Radar PRO',
        'excerptText' => 'Gestión de leads y empresas guardadas en el Radar PRO.',
    ]) ?>
    <link rel="stylesheet" href="<?= base_url('public/css/radar.css?v=' . (file_exists(FCPATH . 'public/css/radar.css') ? filemtime(FCPATH . 'public/css/radar.css') : time())) ?>">
    <!-- SweetAlert2 -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .ae-favorites-page__header { margin-bottom: 32px; }
        .ae-favorites-page__title { font-size: 32px; font-weight: 900; color: #1e293b; margin-bottom: 8px; letter-spacing: -0.02em; }
        .ae-favorites-page__subtitle { color: #64748b; font-size: 16px; }

        .ae-favorites-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
            gap: 24px;
            margin-top: 24px;
        }

        .ae-fav-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            padding: 24px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            display: flex;
            flex-direction: column;
            gap: 20px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }
        .ae-fav-card:hover {
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            border-color: #2563eb;
            transform: translateY(-4px);
        }

        .ae-fav-card__badge-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 4px;
        }

        .ae-fav-card__status {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
        .ae-fav-card__status--nuevo { background: #eff6ff; color: #2563eb; }
        .ae-fav-card__status--contactado { background: #fff7ed; color: #ea580c; }
        .ae-fav-card__status--negociacion { background: #faf5ff; color: #9333ea; }
        .ae-fav-card__status--ganado { background: #f0fdf4; color: #16a34a; }

        .ae-fav-card__company-name {
            font-size: 20px;
            font-weight: 800;
            color: #0f172a;
            text-decoration: none;
            line-height: 1.2;
            margin-bottom: 8px;
            display: block;
        }
        .ae-fav-card__company-name:hover { color: #2563eb; }

        .ae-fav-card__meta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 12px;
            margin-top: 16px;
        }
        .ae-fav-card__meta-item {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }
        .ae-fav-card__meta-label { font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; }
        .ae-fav-card__meta-value { font-size: 13px; font-weight: 600; color: #475569; }

        .ae-fav-card__activity {
            margin-top: 16px;
            font-size: 13px;
            color: #64748b;
            line-height: 1.5;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Score Badges */
        .ae-radar-page__score {
            width: 34px !important;
            height: 34px !important;
            aspect-ratio: 1 / 1;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 900;
            flex-shrink: 0;
            border: 2px solid #fff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
            padding: 0;
            line-height: 1;
        }
        .ae-radar-page__score--aplus { background: linear-gradient(135deg, #059669, #10b981); color: #fff; }
        .ae-radar-page__score--a { background: #10b981; color: #fff; }
        .ae-radar-page__score--b { background: #f59e0b; color: #fff; }
        .ae-radar-page__score--c { background: #ef4444; color: #fff; }

        /* Sidebar: Notes */
        .ae-fav-card__notes {
            background: #f8fafc;
            border-left: 1px solid #f1f5f9;
            padding: 24px;
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .ae-fav-card__notes-header { display: flex; justify-content: space-between; align-items: center; }
        .ae-fav-card__notes-label { font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; }
        
        .ae-fav-card__notes-area {
            width: 100%;
            padding: 14px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #fff;
            font-size: 13px;
            color: #334155;
            flex: 1;
            resize: none;
            line-height: 1.5;
            transition: all 0.2s;
        }
        .ae-fav-card__notes-area:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.08);
        }

        /* Paginación */
        .ae-favorites-pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 12px;
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }
        .ae-pagination-btn {
            padding: 8px 16px;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }
        .ae-pagination-btn:hover:not(:disabled) {
            border-color: #2563eb;
            color: #2563eb;
        }
        .ae-pagination-numbers {
            display: flex;
            gap: 6px;
        }
        .ae-pagination-number {
            width: 38px;
            height: 38px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #fff;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 700;
            color: #475569;
            cursor: pointer;
            transition: all 0.2s;
        }
        .ae-pagination-number:hover { border-color: #2563eb; color: #2563eb; }
        .ae-pagination-number.is-active {
            background: #2563eb;
            border-color: #2563eb;
            color: #fff;
        }

        .ae-fav-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s;
            border: 1px solid transparent;
            text-decoration: none;
        }
        .ae-fav-btn--primary { background: #1e293b; color: #fff; }
        .ae-fav-btn--primary:hover { background: #0f172a; transform: translateY(-1px); }
        
        .ae-fav-btn--ai { background: #eff6ff; color: #2563eb; border-color: #dbeafe; }
        .ae-fav-btn--ai:hover { background: #dbeafe; }
        
        .ae-fav-btn--outline { border-color: #e2e8f0; color: #64748b; background: #fff; }
        .ae-fav-btn--outline:hover { background: #f8fafc; border-color: #cbd5e1; }

        .ae-status-select {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            font-size: 13px;
            font-weight: 600;
            color: #475569;
            outline: none;
            cursor: pointer;
        }

        .ae-fav-card__remove {
            width: 36px;
            height: 36px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 10px;
            color: #94a3b8;
            border: 1px solid #f1f5f9;
            background: #fff;
            cursor: pointer;
            transition: all 0.2s;
        }
        .ae-fav-card__remove:hover { color: #ef4444; background: #fef2f2; border-color: #fee2e2; }

        .ae-fav-empty {
            text-align: center;
            padding: 100px 40px;
            background: #fff;
            border-radius: 32px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        }
        /* Estilos Barra de Filtros */
        .ae-favorites-filters {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #fff;
            padding: 16px 20px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            margin-bottom: 24px;
            gap: 20px;
        }
        .ae-favorites-filters__search {
            flex: 1;
            position: relative;
            max-width: 400px;
        }
        .ae-favorites-filters__search-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 14px;
        }
        .ae-favorites-filters__search input {
            width: 100%;
            padding: 10px 10px 10px 40px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
        }
        .ae-favorites-filters__search input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }
        .ae-favorites-filters__groups {
            display: flex;
            gap: 16px;
        }
        .ae-favorites-filters__group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .ae-favorites-filters__group label {
            font-size: 13px;
            font-weight: 600;
            color: #64748b;
        }
        .ae-favorites-filters__group select {
            padding: 8px 12px;
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            font-size: 13px;
            background: #fff;
        }

        @media (max-width: 768px) {
            .ae-favorites-filters {
                flex-direction: column;
                align-items: stretch;
            }
            .ae-favorites-filters__search {
                max-width: none;
            }
        }

        /* Estilo para Sin Resultados */
        .ae-favorites-empty {
            grid-column: 1 / -1;
            text-align: center;
            padding: 60px 20px;
            background: #f8fafc;
            border-radius: 16px;
            border: 2px dashed #e2e8f0;
        }
        .ae-favorites-empty__icon {
            font-size: 48px;
            margin-bottom: 16px;
            opacity: 0.5;
        }
        .ae-favorites-empty__title {
            font-size: 18px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .ae-favorites-empty__text {
            color: #64748b;
            font-size: 14px;
        }
    </style>
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
                    <a href="<?= site_url('radar/favoritos') ?>" class="ae-radar-page__nav-link is-active">
                        <span class="ae-radar-page__nav-icon">⭐</span>
                        Mis favoritos
                    </a>
                    
                    <a href="<?= site_url('radar/kanban') ?>" class="ae-radar-page__nav-link">
                        <span class="ae-radar-page__nav-icon">📋</span>
                        Embudo (Kanban)
                    </a>
                    
                    <a href="<?= site_url('radar/trends') ?>" class="ae-radar-page__nav-link">
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
                <a href="<?= site_url('radar') ?>" class="ae-radar-page__nav-link">
                    <span class="ae-radar-page__nav-icon">🏠</span>
                    Volver al Radar
                </a>
            </div>
        </aside>

        <main class="ae-radar-page__main">
            <header class="ae-radar-page__topbar">
                <div class="ae-radar-page__breadcrumb">
                    <span>Radar PRO</span>
                    <span>/</span>
                    <strong>Mis Favoritos</strong>
                </div>
            </header>

            <div class="ae-radar-page__content">
                <div class="ae-radar-page__container">
                    <div class="ae-favorites-page__header">
                        <h1 class="ae-favorites-page__title">Mis Empresas Favoritas</h1>
                        <p class="ae-favorites-page__subtitle">Gestiona tus leads guardados, analiza su potencial con IA y organiza el seguimiento comercial.</p>
                    </div>

                    <!-- Nueva Barra de Filtros -->
                    <div class="ae-favorites-filters">
                        <div class="ae-favorites-filters__search">
                            <span class="ae-favorites-filters__search-icon">🔍</span>
                            <input type="text" id="fav-search" placeholder="Buscar por nombre o CIF..." onkeyup="debounceSearch()">
                        </div>
                        <div class="ae-favorites-filters__groups">
                            <div class="ae-favorites-filters__group">
                                <label for="filter-status">Estado:</label>
                                <select id="filter-status" onchange="applyFilters()">
                                    <option value="all" <?= ($currentStatus == 'all') ? 'selected' : '' ?>>Todos los estados</option>
                                    <option value="nuevo" <?= ($currentStatus == 'nuevo') ? 'selected' : '' ?>>Nuevo</option>
                                    <option value="contactado" <?= ($currentStatus == 'contactado') ? 'selected' : '' ?>>Contactado</option>
                                    <option value="negociacion" <?= ($currentStatus == 'negociacion') ? 'selected' : '' ?>>Negociación</option>
                                    <option value="ganado" <?= ($currentStatus == 'ganado') ? 'selected' : '' ?>>Ganado</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div id="ae-favorites-container">
                        <?= view('radar/partials/favorites_list', [
                            'favorites' => $favorites,
                            'currentPage' => $currentPage,
                            'totalPages' => $totalPages,
                            'totalItems' => $totalItems
                        ]) ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?= view('radar/partials/ai_modal') ?>

<script>
let searchTimeout;
let currentPage = <?= $currentPage ?>;

function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        currentPage = 1; // Reset to page 1 on search
        applyFilters();
    }, 500);
}

function goToPage(page) {
    currentPage = page;
    applyFilters();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

/**
 * Aplicar filtros de búsqueda y estado vía AJAX
 */
function applyFilters() {
    const container = document.getElementById('ae-favorites-container');
    const search = document.getElementById('fav-search').value;
    const status = document.getElementById('filter-status').value;

    container.style.opacity = '0.5';
    container.style.pointerEvents = 'none';

    const url = new URL('<?= site_url('radar/favoritos') ?>', window.location.origin);
    url.searchParams.append('search', search);
    url.searchParams.append('status', status);
    url.searchParams.append('page', currentPage);

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        container.innerHTML = html;
        container.style.opacity = '1';
        container.style.pointerEvents = 'auto';
    })
    .catch(err => {
        console.error('Error fetching favorites:', err);
        container.style.opacity = '1';
        container.style.pointerEvents = 'auto';
    });
}

function updateStatus(companyId, status, select) {
    const formData = new FormData();
    formData.append('company_id', companyId);
    formData.append('status', status);

    fetch('<?= site_url('radar/update-favorite-status') ?>', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: 'Estado actualizado'
            });
            
            // Recargar filtros para que si hay uno activo desaparezca de la vista actual si cambia de estado
            applyFilters();
        }
    });
}

function saveNote(companyId, notes) {
    const $indicator = document.getElementById('save-indicator-' + companyId);
    
    const formData = new FormData();
    formData.append('company_id', companyId);
    formData.append('notes', notes);

    fetch('<?= site_url('radar/save-note') ?>', {
        method: 'POST',
        body: formData,
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 2000,
                timerProgressBar: true
            });
            Toast.fire({
                icon: 'success',
                title: 'Nota guardada'
            });
            
            if ($indicator) {
                $indicator.style.display = 'block';
                setTimeout(() => { $indicator.style.display = 'none'; }, 2000);
            }
        }
    });
}

function removeFavorite(companyId) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta empresa se eliminará de tus favoritos.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('company_id', companyId);

            fetch('<?= site_url('radar/toggle-favorite') ?>', {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.favorite_status === 'removed') {
                    Swal.fire({
                        title: 'Eliminado',
                        text: 'La empresa ha sido eliminada.',
                        icon: 'success',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    applyFilters();
                }
            });
        }
    });
}

// Re-implementación de toggleFavorite necesaria para que funcione addToListFromAI en el modal
function toggleFavorite(btn, companyId) {
    console.log('Toggle favorite in favorites page:', companyId);
}
</script>

</body>
</html>
