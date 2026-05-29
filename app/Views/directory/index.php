<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title, 'excerptText' => $meta_description]) ?>
    <style>
        :root {
            --dir-primary: #2152FF;
            --dir-primary-soft: rgba(33, 82, 255, 0.08);
            --dir-slate-900: #0f172a;
            --dir-slate-800: #1e293b;
            --dir-slate-700: #334155;
            --dir-slate-600: #475569;
            --dir-slate-400: #94a3b8;
            --dir-bg: #f8fafc;
        }

        /* 1. Hero Refinado (High Contrast + Search Integration) */
        .dir-hero {
            padding: 95px 0 75px;
            background: linear-gradient(180deg, #090d16 0%, #0f172a 100%);
            color: #fff;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }

        .dir-hero::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 40%;
            height: 80%;
            background: radial-gradient(circle, rgba(33, 82, 255, 0.12) 0%, transparent 70%);
            pointer-events: none;
        }

        .dir-hero h1 {
            font-size: 3.5rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #ffffff;
            margin-bottom: 1.25rem;
            line-height: 1.1;
        }

        .dir-hero .grad {
            background: linear-gradient(135deg, #60A5FA 0%, #34D399 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .dir-hero p {
            font-size: 1.2rem;
            color: #cbd5e1;
            max-width: 650px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Input styling */
        .dir-search-form input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        .dir-search-form input:focus {
            border-color: #60A5FA;
            box-shadow: 0 0 0 4px rgba(96, 165, 250, 0.2), 0 12px 35px rgba(0,0,0,0.3);
            background: rgba(255, 255, 255, 0.12);
        }

        /* 2. Main Layout & Paddings */
        .dir-main {
            padding: 0px 0;
            background-color: var(--dir-bg);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .dir-section {
            margin-bottom: 90px;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-bottom: 3rem;
        }

        .section-header h2 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--dir-slate-900);
            margin: 0;
            white-space: nowrap;
            letter-spacing: -0.025em;
        }

        .section-header .line {
            height: 2px;
            background: #e2e8f0;
            flex-grow: 1;
            border-radius: 99px;
        }

        /* 3. Grid & Cards */
        .dir-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
            gap: 1.5rem;
        }

        .dir-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            padding: 1.75rem;
            border-radius: 24px;
            text-decoration: none;
            color: inherit;
            transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            gap: 1rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02);
            position: relative;
            overflow: hidden;
        }

        .dir-card:hover {
            transform: translateY(-5px);
            border-color: var(--dir-primary);
            box-shadow: 0 15px 30px -10px rgba(33, 82, 255, 0.12);
        }

        .dir-card__avatar {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            color: #1e40af;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            transition: all 0.3s ease;
        }

        .dir-card__avatar--cnae {
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            color: #065f46;
        }

        .dir-card:hover .dir-card__avatar {
            background: linear-gradient(135deg, var(--dir-primary) 0%, #1e40af 100%);
            color: #ffffff;
            transform: scale(1.05);
        }

        .dir-card:hover .dir-card__avatar--cnae {
            background: linear-gradient(135deg, #10b981 0%, #047857 100%);
            color: #ffffff;
        }

        .dir-card__name {
            font-size: 1.15rem;
            font-weight: 700;
            color: var(--dir-slate-900);
            line-height: 1.35;
            letter-spacing: -0.01em;
        }

        .dir-card__footer {
            margin-top: auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 1rem;
            border-top: 1px solid #f1f5f9;
        }

        .dir-card__count {
            font-size: 0.8rem;
            font-weight: 700;
            color: var(--dir-primary);
            background: var(--dir-primary-soft);
            padding: 5px 12px;
            border-radius: 10px;
        }

        .dir-card__arrow {
            color: var(--dir-slate-400);
            transition: transform 0.3s ease, color 0.3s;
            font-size: 1.1rem;
            font-weight: 700;
        }

        .dir-card:hover .dir-card__arrow {
            transform: translateX(4px);
            color: var(--dir-primary);
        }

        .province-row--collapsed, .cnae-row--collapsed {
            display: none !important;
        }

        @media (max-width: 768px) {
            .dir-hero { padding: 60px 1.5rem 50px; }
            .dir-hero h1 { font-size: 2.5rem; }
            .dir-main { padding: 60px 0; }
            .container { padding: 0 1.5rem; }
            .section-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .section-header h2 { font-size: 1.75rem; white-space: normal; }
            .dir-grid { gap: 1.25rem; }
        }
    </style>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>
<?= view('partials/header') ?>

<header class="dir-hero">
    <div class="container">
        <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(33, 82, 255, 0.15); color: #60A5FA; padding: 6px 14px; border-radius: 99px; font-size: 0.85rem; font-weight: 700; margin-bottom: 1.5rem; border: 1px solid rgba(33, 82, 255, 0.25);">
            <span style="display: inline-block; width: 6px; height: 6px; background: #34D399; border-radius: 99px; box-shadow: 0 0 8px #34D399;"></span>
            Base de Datos Oficial en Tiempo Real
        </div>
        <h1>Directorio de <span class="grad">Empresas Españolas</span></h1>
        <p>Valide información mercantil en tiempo real con acceso a la base de datos más completa de sociedades en España.</p>
        
        <form class="dir-search-form" method="GET" action="<?= site_url('search_company') ?>" style="margin-top: 2.5rem; max-width: 600px; margin-left: auto; margin-right: auto; position: relative;">
            <input type="text" name="q" placeholder="Buscar empresa por nombre, CIF, actividad o provincia..." required style="width: 100%; padding: 1.1rem 1.5rem; padding-right: 5rem; border-radius: 99px; border: 1px solid rgba(255,255,255,0.15); background: rgba(255,255,255,0.07); color: #fff; font-size: 0.95rem; backdrop-filter: blur(12px); outline: none; transition: all 0.3s; box-shadow: 0 10px 30px rgba(0,0,0,0.15); text-align: left;">
            <button type="submit" style="position: absolute; right: 8px; top: 8px; bottom: 8px; border-radius: 99px; background: var(--dir-primary); color: #fff; border: none; padding: 0 1.5rem; font-weight: 800; cursor: pointer; transition: all 0.2s; display: inline-flex; align-items: center; gap: 6px; font-size: 0.9rem;" onmouseover="this.style.background='#1b44d3'" onmouseout="this.style.background='var(--dir-primary)'">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-top: -1px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                Buscar
            </button>
        </form>


    </div>
</header>

<main class="dir-main">
    <div class="container" style="padding-top: 40px; padding-bottom: 40px;">
        
        <section class="dir-section" id="provincias-section">
            <div class="section-header" style="margin-bottom: 2rem;">
                <h2>Empresas por Provincia</h2>
                <div class="line"></div>
            </div>
            
            <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap;">
                <div style="position: relative; flex-grow: 1; max-width: 450px;">
                    <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; display: inline-flex; align-items: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </span>
                    <input type="text" id="provinceSearch" placeholder="Filtrar provincia al instante..." style="width: 100%; padding: 12px 16px; padding-left: 48px; border-radius: 14px; border: 1px solid #e2e8f0; outline: none; font-size: 0.95rem; font-weight: 500; transition: all 0.2s; background: #fff;" onfocus="this.style.borderColor='var(--dir-primary)'; this.style.boxShadow='0 0 0 3px var(--dir-primary-soft)';" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                </div>
            </div>

            <div class="table-container" style="background: #ffffff; border-radius: 24px; border: 1px solid #e2e8f0; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.02); margin-bottom: 2rem;">
                <table style="width: 100%; min-width: 600px; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 1.1rem 1.5rem; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Provincia</th>
                            <th style="padding: 1.1rem 1.5rem; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Volumen de Empresas</th>
                            <th style="padding: 1.1rem 1.5rem; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; text-align: right;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 0;
                        foreach($provinces as $prov): 
                            $count++;
                            $collapsedClass = ($count > 12) ? 'province-row--collapsed' : '';
                            $pct = min(100, max(2, ($prov['total'] / $max_province) * 100));
                        ?>
                        <tr class="province-row <?= $collapsedClass ?>" data-name="<?= esc($prov['name']) ?>" style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 1rem 1.5rem;">
                                <a href="<?= site_url('directorio/provincia/' . urlencode($prov['name'])) ?>" style="font-weight: 700; color: #0f172a; text-decoration: none; display: inline-flex; align-items: center; gap: 10px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="var(--dir-primary)" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    <?= esc($prov['name']) ?>
                                </a>
                            </td>
                            <td style="padding: 1rem 1.5rem; color: #475569; font-weight: 500;">
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <span style="font-weight: 700; color: #0f172a; min-width: 90px;"><?= number_format($prov['total'], 0, ',', '.') ?></span>
                                    <div style="width: 120px; height: 6px; background: #f1f5f9; border-radius: 99px; overflow: hidden; display: inline-block;">
                                        <div style="width: <?= $pct ?>%; height: 100%; background: linear-gradient(90deg, var(--dir-primary) 0%, #3b82f6 100%); border-radius: 99px;"></div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 1rem 1.5rem; text-align: right; white-space: nowrap;">
                                <a href="<?= site_url('directorio/provincia/' . urlencode($prov['name'])) ?>" style="color: #2152FF; font-weight: 700; text-decoration: none; font-size: 0.85rem; border: 1px solid #dbeafe; padding: 6px 16px; border-radius: 99px; background: #fff; transition: all 0.2s; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;" onmouseover="this.style.background='var(--dir-primary)'; this.style.color='#fff'; this.style.borderColor='var(--dir-primary)';" onmouseout="this.style.background='#fff'; this.style.color='var(--dir-primary)'; this.style.borderColor='#dbeafe';">
                                    Ver provincia
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="margin-left: 2px;"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <button id="viewMoreProvinces" style="display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: var(--dir-primary); border: 1px solid #dbeafe; padding: 12px 28px; border-radius: 99px; font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);" onmouseover="this.style.background='var(--dir-primary)'; this.style.color='#ffffff'; this.style.borderColor='var(--dir-primary)';" onmouseout="this.style.background='#ffffff'; this.style.color='var(--dir-primary)'; this.style.borderColor='#dbeafe';">
                    Ver todas las provincias (+<?= count($provinces) - 12 ?>) ↓
                </button>
            </div>
        </section>

        <section class="dir-section" id="cnaes-section">
            <div class="section-header" style="margin-bottom: 2rem;">
                <h2>Sectores de Actividad (CNAE)</h2>
                <div class="line"></div>
            </div>

            <div style="display: flex; gap: 1rem; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap;">
                <div style="position: relative; flex-grow: 1; max-width: 450px;">
                    <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; display: inline-flex; align-items: center;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                    </span>
                    <input type="text" id="cnaeSearch" placeholder="Filtrar sector al instante..." style="width: 100%; padding: 12px 16px; padding-left: 48px; border-radius: 14px; border: 1px solid #e2e8f0; outline: none; font-size: 0.95rem; font-weight: 500; transition: all 0.2s; background: #fff;" onfocus="this.style.borderColor='var(--dir-primary)'; this.style.boxShadow='0 0 0 3px var(--dir-primary-soft)';" onblur="this.style.borderColor='#e2e8f0'; this.style.boxShadow='none';">
                </div>
            </div>

            <div class="table-container" style="background: #ffffff; border-radius: 24px; border: 1px solid #e2e8f0; overflow-x: auto; box-shadow: 0 1px 3px rgba(0,0,0,0.02); margin-bottom: 2rem;">
                <table style="width: 100%; min-width: 600px; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 1.1rem 1.5rem; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Sector de Actividad</th>
                            <th style="padding: 1.1rem 1.5rem; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Volumen de Empresas</th>
                            <th style="padding: 1.1rem 1.5rem; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; text-align: right;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 0;
                        foreach($cnaes as $cnae): 
                            $count++;
                            $collapsedClass = ($count > 12) ? 'cnae-row--collapsed' : '';
                            $label = $cnae['name'] ?: "CNAE {$cnae['cnae']}";
                            $pct = min(100, max(2, ($cnae['total'] / $max_cnae) * 100));
                        ?>
                        <tr class="cnae-row <?= $collapsedClass ?>" data-name="<?= esc($label) ?>" style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 1rem 1.5rem;">
                                <a href="<?= site_url('directorio/cnae/' . $cnae['cnae']) ?>" style="font-weight: 700; color: #0f172a; text-decoration: none; display: inline-flex; align-items: center; gap: 10px;" title="<?= esc($cnae['name']) ?>">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path><line x1="7" y1="7" x2="7.01" y2="7"></line></svg>
                                    <span style="display: inline-block; max-width: 450px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?= esc($label) ?>
                                    </span>
                                </a>
                            </td>
                            <td style="padding: 1rem 1.5rem; color: #475569; font-weight: 500;">
                                <div style="display: flex; align-items: center; gap: 16px;">
                                    <span style="font-weight: 700; color: #0f172a; min-width: 90px;"><?= number_format($cnae['total'], 0, ',', '.') ?></span>
                                    <div style="width: 120px; height: 6px; background: #f1f5f9; border-radius: 99px; overflow: hidden; display: inline-block;">
                                        <div style="width: <?= $pct ?>%; height: 100%; background: linear-gradient(90deg, #10b981 0%, #059669 100%); border-radius: 99px;"></div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 1rem 1.5rem; text-align: right; white-space: nowrap;">
                                <a href="<?= site_url('directorio/cnae/' . $cnae['cnae']) ?>" style="color: #10b981; font-weight: 700; text-decoration: none; font-size: 0.85rem; border: 1px solid #d1fae5; padding: 6px 16px; border-radius: 99px; background: #fff; transition: all 0.2s; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;" onmouseover="this.style.background='#10b981'; this.style.color='#fff'; this.style.borderColor='#10b981';" onmouseout="this.style.background='#fff'; this.style.color='#10b981'; this.style.borderColor='#d1fae5';">
                                    Ver sector
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="margin-left: 2px;"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <button id="viewMoreCnaes" style="display: inline-flex; align-items: center; gap: 8px; background: #ffffff; color: #10b981; border: 1px solid #d1fae5; padding: 12px 28px; border-radius: 99px; font-weight: 700; font-size: 0.95rem; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);" onmouseover="this.style.background='#10b981'; this.style.color='#ffffff'; this.style.borderColor='#10b981';" onmouseout="this.style.background='#ffffff'; this.style.color='#10b981'; this.style.borderColor='#d1fae5';">
                    Ver todos los sectores (+<?= count($cnaes) - 12 ?>) ↓
                </button>
            </div>
        </section>

        <?php if (!empty($latest)): ?>
        <section class="dir-section">
            <div class="section-header">
                <div style="display: flex; align-items: flex-end; gap: 1rem;">
                    <h2 style="margin: 0;">Últimas Empresas Registradas</h2>
                    <a href="<?= site_url('empresas-nuevas') ?>" style="color: var(--dir-primary); font-weight: 700; text-decoration: none; margin-bottom: 5px; font-size: 0.9rem;">Ver todas →</a>
                </div>
                <div class="line"></div>
            </div>
            <div class="table-container" style="background: #fff; border-radius: 20px; border: 1px solid #e2e8f0; overflow-x: auto; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                <table style="width: 100%; min-width: 600px; border-collapse: collapse; text-align: left; font-size: 0.95rem;">
                    <thead>
                        <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                            <th style="padding: 1rem 1.5rem; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Empresa</th>
                            <th style="padding: 1rem 1.5rem; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Incorporación</th>
                            <th style="padding: 1rem 1.5rem; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase;">Provincia</th>
                            <th style="padding: 1rem 1.5rem; color: #64748b; font-weight: 600; font-size: 0.85rem; text-transform: uppercase; text-align: right;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                            helper('company');
                            foreach($latest as $company): 
                            $url = company_url($company);
                        ?>
                        <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                            <td style="padding: 1rem 1.5rem; display: flex; align-items: center; gap: 1rem;">
                                <div style="width: 36px; height: 36px; border-radius: 10px; background: #eff6ff; color: #1e40af; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 0.95rem; flex-shrink: 0;">
                                    <?= esc(mb_substr($company['name'], 0, 1)) ?>
                                </div>
                                <a href="<?= esc($url) ?>" style="font-weight: 700; color: #0f172a; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#2152FF'" onmouseout="this.style.color='#0f172a'"><?= esc($company['name']) ?></a>
                            </td>
                            <td style="padding: 1rem 1.5rem; color: #475569; font-weight: 500;">
                                <?= date('d/m/Y', strtotime($company['date'])) ?>
                            </td>
                            <td style="padding: 1rem 1.5rem; color: #475569; font-weight: 500;">
                                <span style="display: inline-flex; align-items: center; gap: 6px;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: #94a3b8;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                    <?= esc($company['province'] ?? 'España') ?>
                                </span>
                            </td>
                            <td style="padding: 1rem 1.5rem; text-align: right; white-space: nowrap;">
                                <a href="<?= esc($url) ?>" style="color: #2152FF; font-weight: 700; text-decoration: none; font-size: 0.85rem; border: 1px solid #dbeafe; padding: 6px 16px; border-radius: 99px; background: #fff; transition: all 0.2s; white-space: nowrap; display: inline-flex; align-items: center; gap: 4px;" onmouseover="this.style.background='#2152FF'; this.style.color='#fff'; this.style.borderColor='#2152FF';" onmouseout="this.style.background='#fff'; this.style.color='#2152FF'; this.style.borderColor='#dbeafe';">
                                    Ficha
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" style="margin-left: 2px;"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <?php endif; ?>

    </div>
</main>

        <?php
        $breadcrumbSchema = [
            "@context" => "https://schema.org",
            "@type" => "BreadcrumbList",
            "itemListElement" => [
                [
                    "@type" => "ListItem",
                    "position" => 1,
                    "name" => "Inicio",
                    "item" => site_url()
                ],
                [
                    "@type" => "ListItem",
                    "position" => 2,
                    "name" => "Directorio",
                    "item" => site_url('directorio')
                ]
            ]
        ];
        ?>
        <script type="application/ld+json">
            <?= json_encode($breadcrumbSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
        </script>

        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // 1. Provinces Accordion & Filter
                const provSearch = document.getElementById('provinceSearch');
                const provRows = document.querySelectorAll('.province-row');
                const provBtn = document.getElementById('viewMoreProvinces');
                let provExpanded = false;

                function updateProvinces() {
                    const query = provSearch.value.toLowerCase().trim();
                    if (query.length > 0) {
                        if (provBtn) provBtn.style.display = 'none';
                        provRows.forEach(row => {
                            const name = row.dataset.name.toLowerCase();
                            row.style.setProperty('display', name.includes(query) ? 'table-row' : 'none', 'important');
                        });
                    } else {
                        if (provBtn) provBtn.style.display = 'inline-flex';
                        provRows.forEach((row, index) => {
                            row.style.setProperty('display', (index < 12 || provExpanded) ? 'table-row' : 'none', 'important');
                        });
                    }
                }

                if (provSearch) provSearch.addEventListener('input', updateProvinces);
                if (provBtn) {
                    provBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        provExpanded = !provExpanded;
                        provBtn.innerHTML = provExpanded ? 'Ver menos provincias ↑' : 'Ver todas las provincias (+<?= count($provinces) - 12 ?>) ↓';
                        if (!provExpanded) document.getElementById('provincias-section').scrollIntoView({ behavior: 'smooth' });
                        updateProvinces();
                    });
                }

                // 2. CNAEs Accordion & Filter
                const cnaeSearch = document.getElementById('cnaeSearch');
                const cnaeRows = document.querySelectorAll('.cnae-row');
                const cnaeBtn = document.getElementById('viewMoreCnaes');
                let cnaeExpanded = false;

                function updateCnaes() {
                    const query = cnaeSearch.value.toLowerCase().trim();
                    if (query.length > 0) {
                        if (cnaeBtn) cnaeBtn.style.display = 'none';
                        cnaeRows.forEach(row => {
                            const name = row.dataset.name.toLowerCase();
                            row.style.setProperty('display', name.includes(query) ? 'table-row' : 'none', 'important');
                        });
                    } else {
                        if (cnaeBtn) cnaeBtn.style.display = 'inline-flex';
                        cnaeRows.forEach((row, index) => {
                            row.style.setProperty('display', (index < 12 || cnaeExpanded) ? 'table-row' : 'none', 'important');
                        });
                    }
                }

                if (cnaeSearch) cnaeSearch.addEventListener('input', updateCnaes);
                if (cnaeBtn) {
                    cnaeBtn.addEventListener('click', function(e) {
                        e.preventDefault();
                        cnaeExpanded = !cnaeExpanded;
                        cnaeBtn.innerHTML = cnaeExpanded ? 'Ver menos sectores ↑' : 'Ver todos los sectores (+<?= count($cnaes) - 12 ?>) ↓';
                        if (!cnaeExpanded) document.getElementById('cnaes-section').scrollIntoView({ behavior: 'smooth' });
                        updateCnaes();
                    });
                }

                // Initial setup
                updateProvinces();
                updateCnaes();
            });
        </script>

<?= view('partials/footer') ?>
</body>
</html>
