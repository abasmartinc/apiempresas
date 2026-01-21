<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title, 'excerptText' => $meta_description]) ?>
    <style>
        :root {
            --dir-primary: #2152FF;
            --dir-slate-900: #0f172a;
            --dir-slate-800: #1e293b;
            --dir-slate-600: #475569;
            --dir-slate-400: #94a3b8;
            --dir-bg: #f8fafc;
        }

        /* Hero refinements consistent with index */
        .dir-hero {
            padding: 80px 0 60px;
            background: var(--dir-slate-900);
            color: #fff;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }

        .dir-hero h1 {
            font-size: 3rem;
            font-weight: 800;
            color: #fff;
            letter-spacing: -0.025em;
            margin-bottom: 1rem;
            line-height: 1.1;
        }

        .dir-hero p {
            font-size: 1.15rem;
            color: var(--dir-slate-400);
            max-width: 800px;
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .list-main {
            padding: 0px 0 120px;
            background-color: var(--dir-bg);
            min-height: 80vh;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2.5rem;
            font-size: 0.85rem;
            color: var(--dir-slate-400);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .breadcrumb a {
            color: var(--dir-primary);
            text-decoration: none;
            transition: color 0.2s;
        }

        .breadcrumb a:hover {
            color: var(--dir-slate-900);
        }

        .breadcrumb .sep {
            color: #cbd5e1;
        }

        .company-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
            gap: 2rem;
        }

        .company-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            padding: 2.5rem;
            border-radius: 28px;
            text-decoration: none;
            color: inherit;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }

        .company-card:hover {
            transform: translateY(-8px);
            border-color: var(--dir-primary);
            box-shadow: 0 30px 60px -12px rgba(33, 82, 255, 0.12);
        }

        .company-card__top {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .badge-official {
            background: #f1f5f9;
            color: var(--dir-slate-600);
            font-size: 0.65rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            padding: 6px 12px;
            border-radius: 8px;
        }

        .company-card__cif {
            font-weight: 800;
            color: var(--dir-primary);
            font-size: 0.95rem;
            letter-spacing: -0.01em;
        }

        .company-card__name {
            font-size: 1.4rem;
            font-weight: 800;
            color: var(--dir-slate-900);
            line-height: 1.25;
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            letter-spacing: -0.02em;
        }

        .company-card__footer {
            margin-top: auto;
            padding-top: 1.5rem;
            border-top: 1px solid #f1f5f9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .company-card__location {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--dir-slate-600);
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .company-card__view {
            color: var(--dir-primary);
            font-weight: 800;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            transition: gap 0.3s ease;
        }

        .company-card:hover .company-card__view {
            gap: 0.7rem;
        }

        .cta-bottom {
            margin-top: 8rem;
            padding: 6rem 2rem;
            background: #ffffff;
            border-radius: 40px;
            border: 1px solid #e2e8f0;
            text-align: center;
            box-shadow: 0 10px 30px -5px rgba(0,0,0,0.02);
        }

        @media (max-width: 768px) {
            .container { padding: 0 1.5rem; }
            .dir-hero { padding: 60px 1.5rem 40px; text-align: center; }
            .dir-hero h1 { font-size: 2.25rem; }
            .list-main { padding: 60px 0 80px; }
            .company-grid { grid-template-columns: 1fr; gap: 1.5rem; }
            .company-card { padding: 2rem; }
            .cta-bottom { padding: 3rem 1.5rem; }
        }
    </style>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>
<?= view('partials/header') ?>

<header class="dir-hero">
    <div class="container">
        <h1><?= esc($header) ?></h1>
        <p>Listado actualizado de sociedades vinculadas. Seleccione una empresa para consultar su validación de CIF, datos registrales y scoring en tiempo real.</p>
    </div>
</header>

<main class="list-main">
    <div class="container" style="padding-top: 40px; padding-bottom: 40px;">
        
        <nav class="breadcrumb">
            <a href="<?= site_url() ?>">Inicio</a>
            <span class="sep">/</span>
            <a href="<?= site_url('directorio') ?>">Directorio</a>
            <span class="sep">/</span>
            <span style="color: var(--dir-slate-900)"><?= esc($header) ?></span>
        </nav>

        <div class="company-grid">
            <?php foreach($items as $company): 
                $slug = url_title($company['name'] ?? '', '-', true);
                $url  = site_url(($company['cif'] ?? '') . ($slug ? ('-' . $slug) : ''));
            ?>
                <a href="<?= esc($url) ?>" class="company-card">
                    <div class="company-card__top">
                        <span class="badge-official">Ficha Oficial</span>
                        <span class="company-card__cif"><?= esc($company['cif'] ?? '-') ?></span>
                    </div>
                    
                    <h3 class="company-card__name" title="<?= esc($company['name']) ?>">
                        <?= esc($company['name']) ?>
                    </h3>
                    
                    <div class="company-card__footer">
                        <span class="company-card__location">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            <?= esc($company['province'] ?? 'España') ?>
                        </span>
                        <span class="company-card__view">Detalles <span>→</span></span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
        
        <div class="cta-bottom">
            <h2 style="font-size: 1.75rem; font-weight: 800; color: var(--dir-slate-900); margin-bottom: 1rem;">¿No encuentra la empresa que busca?</h2>
            <p style="color: var(--dir-slate-600); font-size: 1.1rem; margin-bottom: 2.5rem; max-width: 600px; margin-left: auto; margin-right: auto;">Nuestro buscador avanzado permite encontrar cualquier sociedad en España por nombre, CIF o ubicación.</p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <a href="<?= site_url('search_company') ?>" class="btn primary" style="padding: 1rem 2.5rem;">Buscador Avanzado</a>
                <a href="<?= site_url('directorio') ?>" class="btn secondary" style="padding: 1rem 2.5rem;">Volver al Directorio</a>
            </div>
        </div>

    </div>
</main>

<?= view('partials/footer') ?>
</body>
</html>
