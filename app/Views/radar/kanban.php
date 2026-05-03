<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => $title ?? 'Embudo de Ventas - Radar PRO',
        'excerptText' => 'Gestiona tus leads y favoritos moviéndolos por las diferentes etapas.',
    ]) ?>
    <link rel="stylesheet" href="<?= base_url('public/css/radar.css?v=' . (file_exists(FCPATH . 'public/css/radar.css') ? filemtime(FCPATH . 'public/css/radar.css') : time())) ?>">
</head>
<body>

<div class="ae-radar-page">
    <div class="ae-radar-page__shell">
        
        <?= view('radar/partials/sidebar') ?>


        <main class="ae-radar-page__main">
            <header class="ae-radar-page__topbar">
                <div class="ae-radar-page__breadcrumb">
                    <span>Radar PRO</span>
                    <span>/</span>
                    <strong>Embudo de Ventas</strong>
                </div>
            </header>

            <div class="ae-radar-page__content">
                <div class="ae-radar-page__container">
                    
                    <section class="ae-radar-page__hero ae-radar-page__hero--pro" style="background: white; border: 1px solid rgba(15, 23, 42, 0.08); box-shadow: 0 10px 30px -10px rgba(0,0,0,0.05); margin-bottom: 20px; padding: 16px 32px;">
                        <div class="ae-radar-page__hero-grid">
                            <div>
                                <div class="ae-radar-page__eyebrow" style="background: #eff6ff; border-color: #dbeafe; color: #2563eb; padding: 6px 14px; font-size: 11px;">
                                    <span class="ae-radar-page__pulse" style="background: #2563eb; width: 8px; height: 8px;"></span>
                                    Lead CRM · Gestión Visual
                                </div>
                                <h1 class="ae-radar-page__hero-title" style="margin-top: 8px; margin-bottom: 4px; font-size: 24px;">
                                    Embudo de Ventas <span class="ae-radar-page__hero-title-grad">Interactivo</span>
                                </h1>
                                <p class="ae-radar-page__hero-text" style="color: #64748b; max-width: 650px; line-height: 1.4; font-size: 13.5px; margin-top: 4px;">
                                    Gestiona tus favoritos por etapas comerciales arrastrando las tarjetas en tiempo real.
                                </p>
                            </div>
                        </div>
                    </section>

                    <div class="kanban-wrapper">
                        <div class="kanban-board" id="kanbanBoard">
                            <?php 
                            $columnConfig = [
                                'nuevo' => ['label' => 'Nuevo', 'icon' => 'fa-plus-circle', 'color' => '#3b82f6'],
                                'contactado' => ['label' => 'Contactado', 'icon' => 'fa-paper-plane', 'color' => '#8b5cf6'],
                                'negociacion' => ['label' => 'En Negociación', 'icon' => 'fa-comments', 'color' => '#f59e0b'],
                                'ganado' => ['label' => 'Ganado', 'icon' => 'fa-check-circle', 'color' => '#10b981'],
                                'seguimiento' => ['label' => 'Seguimiento (IA)', 'icon' => 'fa-robot', 'color' => '#2563eb']
                            ];
                            
                            foreach ($columnConfig as $id => $config): ?>
                                <div class="kanban-column" data-status="<?= $id ?>">
                                    <div class="kanban-column-header" style="border-top-color: <?= $config['color'] ?>">
                                        <div class="kanban-column-title">
                                            <i class="fas <?= $config['icon'] ?>" style="color: <?= $config['color'] ?>"></i>
                                            <span><?= $config['label'] ?></span>
                                            <span class="kanban-column-count"><?= count($columns[$id]) ?></span>
                                        </div>
                                    </div>
                                    <div class="kanban-cards" id="cards-<?= $id ?>" ondrop="drop(event)" ondragover="allowDrop(event)">
                                        <?php foreach ($columns[$id] as $fav): ?>
                                            <div class="kanban-card" 
                                                 id="fav-<?= $fav['id'] ?>" 
                                                 draggable="true" 
                                                 ondragstart="drag(event)" 
                                                 data-id="<?= $fav['id'] ?>"
                                                 onclick="openQuickView(<?= $fav['company_id'] ?>)">
                                                <div class="kanban-card__score" style="display: flex; justify-content: space-between; align-items: center;">
                                                    <?php 
                                                        $rawScore = $fav['lead_score'] ?? 'POTENCIAL MEDIO';
                                                        $displayLabel = $rawScore;
                                                        $scoreClass = 'medium';

                                                        // Mapeo unificado de etiquetas a clases CSS
                                                        $classMap = [
                                                            'LEAD CALIENTE'    => 'hot',
                                                            'OPORTUNIDAD ALTA' => 'high',
                                                            'CONTACTAR AHORA'  => 'now',
                                                            'POTENCIAL MEDIO'  => 'medium',
                                                            'MÍNIMO INTERÉS'   => 'low',
                                                        ];

                                                        if (isset($classMap[$rawScore])) {
                                                            $scoreClass = $classMap[$rawScore];
                                                        }
                                                    ?>
                                                    <span class="ae-radar-page__score ae-radar-page__score--<?= $scoreClass ?>">
                                                        <?= esc($displayLabel) ?>
                                                    </span>
                                                    <?php if ($fav['is_following'] ?? false): ?>
                                                        <span class="ae-status-pill ae-status-pill--following" style="background:#eff6ff; color:#2563eb; padding:2px 8px; border-radius:6px; font-size:10px; font-weight:800; text-transform:uppercase;">
                                                            🔵 IA
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                                <h4 class="kanban-card__name"><?= esc($fav['company_name']) ?></h4>
                                                <div class="kanban-card__meta">
                                                    <span><i class="fas fa-map-marker-alt"></i> <?= esc($fav['municipality']) ?></span>
                                                    <span><?= esc($fav['cif']) ?></span>
                                                </div>
                                                <?php if (!empty($fav['notes'])): ?>
                                                    <div class="kanban-card__notes">
                                                        <i class="fas fa-sticky-note"></i> <?= esc(mb_strimwidth($fav['notes'], 0, 40, '...')) ?>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                </div>
            </div>
        </main>
    </div>
</div>

<!-- Modal QuickView -->
<div id="ae-qv-modal" class="ae-qv-modal" style="display:none;">
    <div class="ae-qv-modal__backdrop" onclick="closeQuickView()"></div>
    <div class="ae-qv-modal__container">
        <div id="ae-qv-content" class="ae-qv-modal__content">
            <!-- Se cargará por AJAX -->
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
function allowDrop(ev) {
    ev.preventDefault();
    const column = ev.target.closest('.kanban-cards');
    if (column) column.style.background = 'rgba(33, 82, 255, 0.05)';
}

// Reset background on drag leave
document.addEventListener('dragleave', function(ev) {
    if (ev.target.classList.contains('kanban-cards')) {
        ev.target.style.background = '';
    }
});

function drag(ev) {
    ev.dataTransfer.setData("text", ev.target.id);
    ev.target.classList.add('kanban-card--dragging');
}

function drop(ev) {
    ev.preventDefault();
    var data = ev.dataTransfer.getData("text");
    var card = document.getElementById(data);
    card.classList.remove('kanban-card--dragging');
    
    var target = ev.target.closest('.kanban-cards');
    if (target) {
        target.style.background = '';
        target.appendChild(card);
        var newStatus = target.parentElement.getAttribute('data-status');
        var favoriteId = card.getAttribute('data-id');
        
        updateCounts();
        updateStatus(favoriteId, newStatus);
    }
}

function updateCounts() {
    document.querySelectorAll('.kanban-column').forEach(column => {
        const count = column.querySelectorAll('.kanban-card').length;
        column.querySelector('.kanban-column-count').innerText = count;
    });
}

function updateStatus(favoriteId, status) {
    const formData = new FormData();
    formData.append('favorite_id', favoriteId);
    formData.append('status', status);
    
    fetch('<?= site_url('radar/update-favorite-status') ?>', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.status !== 'success') {
            console.error('Error updating status:', data.message);
        }
    });
}

function openQuickView(id) {
    if (!id) return;
    const modal = document.getElementById('ae-qv-modal');
    const content = document.getElementById('ae-qv-content');
    
    content.innerHTML = '<div style="padding:100px; text-align:center; color:#64748b;"><div class="ae-spinner"></div><p style="margin-top:16px; font-weight:600;">Cargando información...</p></div>';
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';

    fetch('<?= site_url('radar/quickview/') ?>' + id, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.text())
    .then(html => {
        content.innerHTML = html;
    });
}

function closeQuickView() {
    document.getElementById('ae-qv-modal').style.display = 'none';
    document.body.style.overflow = '';
}
</script>

</body>
</html>
