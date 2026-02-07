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

        <div class="table-container" style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow-x: auto; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
            <table style="width: 100%; min-width: 600px; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
                <thead>
                    <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                        <th style="padding: 1rem 1.5rem; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Empresa</th>
                        <th style="padding: 1rem 1.5rem; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">CIF</th>
                        <th style="padding: 1rem 1.5rem; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em;">Ubicación</th>
                        <th style="padding: 1rem 1.5rem; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.05em; text-align: right;">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                        helper('company');
                        foreach($items as $company): 
                        $url = company_url($company);
                    ?>
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                        <td style="padding: 1rem 1.5rem;">
                            <a href="<?= esc($url) ?>" style="font-weight: 600; color: #0f172a; text-decoration: none; display: block;">
                                <?= esc($company['name']) ?>
                            </a>
                        </td>
                        <td style="padding: 1rem 1.5rem; color: #475569; font-family: monospace; font-size: 0.9rem;">
                            <?= esc($company['cif'] ?? '-') ?>
                        </td>
                        <td style="padding: 1rem 1.5rem; color: #475569;">
                            <?= esc($company['province'] ?? 'España') ?>
                        </td>
                        <td style="padding: 1rem 1.5rem; text-align: right;">
                            <a href="<?= esc($url) ?>" style="display: inline-flex; align-items: center; gap: 4px; color: #2152FF; font-weight: 600; font-size: 0.85rem; text-decoration: none;">
                                Ver Ficha
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 18l6-6-6-6"/></svg>
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <?php if (!empty($pager) && ($pager['prev'] || $pager['next'])): ?>
        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #e2e8f0;">
            
            <?php if ($pager['prev']): ?>
            <a href="<?= esc($pager['prev']) ?>" class="btn secondary" style="display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                Página Anterior
            </a>
            <?php else: ?>
            <span class="btn secondary" style="opacity: 0.5; pointer-events: none; display: flex; align-items: center; gap: 8px;">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M15 18l-6-6 6-6"/></svg>
                Página Anterior
            </span>
            <?php endif; ?>

            <span style="font-weight: 600; color: var(--dir-slate-600);">
                Página <?= esc($pager['current']) ?>
            </span>

            <?php if ($pager['next']): ?>
            <a href="<?= esc($pager['next']) ?>" class="btn secondary" style="display: flex; align-items: center; gap: 8px;">
                Siguiente Página
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
            </a>
            <?php else: ?>
            <span class="btn secondary" style="opacity: 0.5; pointer-events: none; display: flex; align-items: center; gap: 8px;">
                Siguiente Página
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18l6-6-6-6"/></svg>
            </span>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if (!empty($cross_links) && !empty($cross_links['items'])): ?>
        <div style="margin-top: 5rem; padding-top: 3rem; border-top: 1px solid #e2e8f0;">
            <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--dir-slate-900); margin-bottom: 2rem;"><?= esc($cross_links['title']) ?></h3>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 1rem;">
                <?php foreach($cross_links['items'] as $cl): ?>
                    <?php 
                        if($cross_links['type'] === 'cnae') {
                            $clUrl = site_url('directorio/cnae/' . $cl['code']);
                            $clName = $cl['label'] ?: "CNAE {$cl['code']}";
                        } else {
                            $clUrl = site_url('directorio/provincia/' . urlencode($cl['name']));
                            $clName = $cl['name'];
                        }
                    ?>
                    <a href="<?= esc($clUrl) ?>" style="text-decoration: none; color: var(--dir-slate-600); font-size: 0.95rem; font-weight: 500; display: block; padding: 0.5rem 0; transition: color 0.2s;" onmouseover="this.style.color='#2152FF'" onmouseout="this.style.color='#475569'">
                        <?= esc($clName) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <?php
        // ItemList Schema for better visibility of the list in search results
        $itemListSchema = [
            "@context" => "https://schema.org",
            "@type" => "ItemList",
            "itemListElement" => []
        ];
        helper('company');
        foreach($items as $index => $company) {
            $url = company_url($company);
            $itemListSchema["itemListElement"][] = [
                "@type" => "ListItem",
                "position" => $index + 1,
                "url" => $url,
                "name" => $company['name']
            ];
        }
        ?>
        <script type="application/ld+json">
            <?= json_encode($itemListSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
        </script>
        
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
