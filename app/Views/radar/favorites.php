<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => $title ?? 'Mis Favoritos - Radar PRO',
        'excerptText' => 'Gestión de leads y empresas guardadas en el Radar PRO.',
    ]) ?>
    <link rel="stylesheet" href="<?= base_url('public/css/radar.css?v=5') ?>">
    <style>
        .ae-favorites-page__header {
            margin-bottom: 32px;
        }
        .ae-favorites-page__title {
            font-size: 28px;
            font-weight: 800;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .ae-favorites-page__subtitle {
            color: #64748b;
            font-size: 15px;
        }
        .ae-fav-card {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            padding: 24px;
            margin-bottom: 24px;
            transition: all 0.3s ease;
            position: relative;
        }
        .ae-fav-card:hover {
            box-shadow: 0 10px 30px rgba(0,0,0,0.05);
            border-color: #cbd5e1;
        }
        .ae-fav-card__grid {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 32px;
        }
        .ae-fav-card__company-name {
            font-size: 18px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
            display: block;
            text-decoration: none;
        }
        .ae-fav-card__meta {
            display: flex;
            gap: 16px;
            color: #64748b;
            font-size: 13px;
            margin-bottom: 16px;
        }
        .ae-fav-card__notes-label {
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 8px;
            display: block;
        }
        .ae-fav-card__notes-area {
            width: 100%;
            padding: 12px;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            font-size: 14px;
            color: #334155;
            min-height: 80px;
            resize: vertical;
            transition: all 0.2s ease;
        }
        .ae-fav-card__notes-area:focus {
            outline: none;
            border-color: #3b82f6;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        .ae-fav-card__remove {
            position: absolute;
            top: 24px;
            right: 24px;
            color: #94a3b8;
            background: none;
            border: none;
            cursor: pointer;
            padding: 4px;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .ae-fav-card__remove:hover {
            color: #ef4444;
            background: #fef2f2;
        }
        .ae-fav-empty {
            text-align: center;
            padding: 80px 40px;
            background: #fff;
            border-radius: 24px;
            border: 2px dashed #e2e8f0;
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
                        <p class="ae-favorites-page__subtitle">Gestiona tus leads guardados y añade notas estratégicas.</p>
                    </div>

                    <?php if (empty($favorites)): ?>
                        <div class="ae-fav-empty">
                            <div style="font-size: 48px; margin-bottom: 16px;">⭐</div>
                            <h2 style="font-size: 20px; font-weight: 700; color: #1e293b; margin-bottom: 8px;">Aún no tienes favoritas</h2>
                            <p style="color: #64748b;">Explora el radar y pulsa en la estrella para guardar empresas aquí.</p>
                            <a href="<?= site_url('radar') ?>" class="ae-radar-page__export-btn" style="margin-top: 24px;">Volver al Radar</a>
                        </div>
                    <?php else: ?>
                        <?php foreach ($favorites as $f): ?>
                            <div class="ae-fav-card" id="fav-card-<?= $f['company_id'] ?>">
                                <button class="ae-fav-card__remove" onclick="removeFavorite(<?= $f['company_id'] ?>)" title="Eliminar de favoritos">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                                </button>
                                
                                <div class="ae-fav-card__grid">
                                    <div class="ae-fav-card__info">
                                        <div style="display:flex; align-items:center; gap:10px; margin-bottom:4px;">
                                            <span class="ae-radar-page__score ae-radar-page__score--<?= strtolower(str_replace('+', 'plus', $f['lead_score'])) ?>" title="Score de calidad: <?= $f['lead_score'] ?>" style="margin-bottom:0;">
                                                <?= $f['lead_score'] ?>
                                            </span>
                                            <a href="<?= company_url(['cif' => $f['cif'], 'name' => $f['company_name']]) ?>" class="ae-fav-card__company-name" style="margin-bottom:0;">
                                                <?= esc($f['company_name']) ?>
                                            </a>
                                        </div>
                                        <div class="ae-fav-card__meta">
                                            <span><strong>CIF:</strong> <?= esc($f['cif']) ?></span>
                                            <span><strong>Municipio:</strong> <?= esc($f['municipality'] ?? 'N/D') ?></span>
                                        </div>
                                        <div style="margin-top: 12px;">
                                            <a href="<?= company_url(['cif' => $f['cif'], 'name' => $f['company_name']]) ?>" class="ae-radar-page__btn-qv" style="text-decoration:none; display:inline-block;">Ver ficha completa</a>
                                        </div>
                                    </div>
                                    
                                    <div class="ae-fav-card__notes">
                                        <label class="ae-fav-card__notes-label">Notas privadas</label>
                                        <textarea 
                                            class="ae-fav-card__notes-area" 
                                            placeholder="Añade una nota sobre este lead..."
                                            onblur="saveNote(<?= $f['company_id'] ?>, this.value)"><?= esc($f['notes'] ?? '') ?></textarea>
                                        <div class="ae-save-indicator" id="save-indicator-<?= $f['company_id'] ?>" style="font-size:11px; color:#10b981; margin-top:4px; display:none;">
                                            ✓ Guardado
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</div>

<script>
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
            $indicator.style.display = 'block';
            setTimeout(() => {
                $indicator.style.display = 'none';
            }, 2000);
        }
    });
}

function removeFavorite(companyId) {
    if (!confirm('¿Seguro que quieres eliminar esta empresa de tus favoritos?')) return;

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
            const card = document.getElementById('fav-card-' + companyId);
            card.style.opacity = '0';
            card.style.transform = 'translateX(20px)';
            setTimeout(() => {
                card.remove();
                if (document.querySelectorAll('.ae-fav-card').length === 0) {
                    location.reload();
                }
            }, 300);
        }
    });
}
</script>

</body>
</html>
