<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title' => $title,
        'excerptText' => $meta_description,
        'canonical' => $canonical,
        'robots' => $robots,
    ]) ?>
    <style>
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .hero-section { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; padding: 5rem 2rem; text-align: center; }
        .hero-title { font-size: 3rem; font-weight: 800; margin-bottom: 1rem; letter-spacing: -0.02em; }
        .hero-subtitle { font-size: 1.2rem; color: #94a3b8; max-width: 700px; margin: 0 auto; line-height: 1.6; }
        
        .directory-container { max-width: 1000px; margin: -3rem auto 4rem auto; background: white; border-radius: 24px; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; overflow: hidden; }
        
        .holding-item { padding: 1.5rem 2rem; border-bottom: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: space-between; transition: background 0.2s; text-decoration: none; }
        .holding-item:hover { background: #f8fafc; }
        .holding-item:last-child { border-bottom: none; }
        
        .holding-info { display: flex; align-items: center; gap: 1rem; }
        .holding-icon { width: 48px; height: 48px; border-radius: 12px; background: #e2e8f0; display: flex; align-items: center; justify-content: center; color: #475569; font-weight: 700; font-size: 1.2rem; }
        .holding-name { font-size: 1.1rem; font-weight: 700; color: #0f172a; margin: 0 0 0.25rem 0; }
        .holding-meta { font-size: 0.85rem; color: #64748b; margin: 0; display: flex; align-items: center; gap: 8px; }
        
        .holding-action { color: #4F46E5; font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 4px; }
        
        .pagination-container { padding: 2rem; border-top: 1px solid #e2e8f0; display: flex; justify-content: center; }
        /* Custom basic pagination styles */
        .pagination { display: flex; list-style: none; gap: 8px; margin: 0; padding: 0; }
        .pagination li a, .pagination li span { padding: 8px 16px; border-radius: 8px; border: 1px solid #e2e8f0; color: #475569; text-decoration: none; font-weight: 600; font-size: 0.9rem; transition: all 0.2s; }
        .pagination li a:hover { border-color: #cbd5e1; background: #f8fafc; }
        .pagination li.active span { background: #4F46E5; color: white; border-color: #4F46E5; }
    </style>
</head>
<body>
    <?= view('partials/header') ?>

    <main>
        <div class="hero-section">
            <h1 class="hero-title">Directorio de Grupos Empresariales</h1>
            <p class="hero-subtitle">
                Explora el ecosistema corporativo de España. Descubre la estructura, filiales y el capital agregado de más de 134.000 grupos empresariales y corporaciones.
            </p>
        </div>

        <div class="directory-container">
            <div style="padding: 1.5rem 2rem; background: #f8fafc; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                <h2 style="margin: 0; font-size: 1.1rem; font-weight: 700; color: #0f172a;">Grupos Corporativos</h2>
                
                <form action="<?= site_url('listado-de-grupos-empresariales') ?>" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <input type="text" name="q" placeholder="Buscar grupo..." value="<?= esc($searchQuery ?? '') ?>" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 0.9rem;">
                    
                    <select name="min_companies" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 0.9rem; background: white;">
                        <option value="0" <?= ($minCompanies == 0) ? 'selected' : '' ?>>Cualquier tamaño</option>
                        <option value="2" <?= ($minCompanies == 2) ? 'selected' : '' ?>>+ 2 empresas</option>
                        <option value="5" <?= ($minCompanies == 5) ? 'selected' : '' ?>>+ 5 empresas</option>
                        <option value="10" <?= ($minCompanies == 10) ? 'selected' : '' ?>>+ 10 empresas</option>
                        <option value="50" <?= ($minCompanies == 50) ? 'selected' : '' ?>>+ 50 empresas</option>
                    </select>
                    
                    <button type="submit" style="background: #4F46E5; color: white; border: none; border-radius: 8px; padding: 8px 20px; font-weight: 600; cursor: pointer; transition: background 0.2s;">Filtrar</button>
                    <?php if (!empty($searchQuery) || $minCompanies > 0): ?>
                        <a href="<?= site_url('listado-de-grupos-empresariales') ?>" style="display: flex; align-items: center; text-decoration: none; color: #64748b; font-size: 0.9rem; margin-left: 10px;">Limpiar</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <?php foreach ($holdings as $holding): ?>
            <a href="<?= site_url('grupos-empresariales/' . esc($holding['slug'])) ?>" class="holding-item">
                <div class="holding-info">
                    <div>
                        <h3 class="holding-name"><?= esc($holding['name']) ?></h3>
                        <p class="holding-meta">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                            <?= number_format($holding['companies_count'], 0, ',', '.') ?> empresas conectadas
                        </p>
                    </div>
                </div>
                <div class="holding-action">
                    Ver estructura <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m9 18 6-6-6-6"/></svg>
                </div>
            </a>
            <?php endforeach; ?>
            
            <?php if (empty($holdings)): ?>
                <div style="padding: 4rem 2rem; text-align: center; color: #64748b;">
                    No se encontraron grupos empresariales.
                </div>
            <?php endif; ?>

            <div class="pagination-container">
                <?= $pager->links('default', 'seo_es') ?>
            </div>
        </div>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
