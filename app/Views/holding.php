<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title' => 'Grupo ' . esc($holding['name']) . ' - Estructura y Empresas del Holding',
        'excerptText' => 'Descubre las ' . esc($aggregates['total_companies']) . ' empresas que forman el Grupo ' . esc($holding['name']) . ', su capital social agregado y su red de filiales.',
        'canonical' => site_url('grupos-empresariales/' . esc($holding['slug'])),
        'robots' => 'index, follow',
    ]) ?>
    <style>
        :root {
            --dir-primary: #2152FF;
            --dir-primary-soft: rgba(33, 82, 255, 0.08);
            --dir-slate-900: #0f172a;
            --dir-slate-800: #1e293b;
            --dir-slate-700: #334155;
            --dir-slate-600: #475569;
            --dir-slate-400: #94a3b8;
            --dir-bg: #f1f5f9;
        }

        body { background-color: var(--dir-bg); font-family: 'Inter', sans-serif; margin: 0; padding: 0; }
        
        /* ── HERO ── */
        .dir-hero {
            padding: 60px 0 140px;
            background: linear-gradient(160deg, #060a14 0%, #0c1428 50%, #0f172a 100%);
            color: #fff;
            text-align: center;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(255,255,255,0.05);
        }
        .dir-hero::before {
            content: '';
            position: absolute;
            top: -20%; right: -10%;
            width: 40%; height: 80%;
            background: radial-gradient(circle, rgba(33,82,255,0.14) 0%, transparent 70%);
            pointer-events: none;
        }
        .dir-hero::after {
            content: '';
            position: absolute;
            bottom: -10%; left: -5%;
            width: 35%; height: 60%;
            background: radial-gradient(circle, rgba(52,211,153,0.07) 0%, transparent 70%);
            pointer-events: none;
        }
        .dir-hero h1 {
            font-size: clamp(2.2rem, 4vw, 3.2rem);
            font-weight: 800;
            letter-spacing: -0.03em;
            color: #ffffff;
            margin-bottom: 1rem;
            line-height: 1.15;
            position: relative;
            z-index: 2;
        }
        .dir-hero .grad {
            background: linear-gradient(135deg, #60A5FA 0%, #34D399 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .dir-hero p {
            font-size: 1.1rem;
            color: #cbd5e1;
            max-width: 700px;
            margin: 0 auto;
            line-height: 1.6;
            position: relative;
            z-index: 2;
        }

        /* ── LAYOUT ── */
        .dir-main { padding: 0; background: var(--dir-bg); }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 2rem; }
        
        .dir-main-card {
            margin-top: -90px;
            position: relative;
            z-index: 10;
            background: #ffffff;
            border-radius: 28px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.10), 0 1px 0 rgba(255,255,255,0.8) inset;
            padding: 48px;
            margin-bottom: 60px;
            border: 1px solid #e2e8f0;
        }

        /* ── STATS BAR ── */
        .dir-stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 1px;
            background: #e2e8f0;
            border-radius: 20px;
            overflow: hidden;
            margin-bottom: 48px;
            box-shadow: 0 4px 16px rgba(0,0,0,0.04);
        }
        .dir-stat {
            background: #fff;
            padding: 24px 28px;
            display: flex;
            align-items: center;
            gap: 16px;
            transition: background 0.2s;
        }
        .dir-stat:hover { background: #fafbff; }
        .dir-stat__icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .dir-stat__icon--blue  { background: linear-gradient(135deg, #eff6ff, #dbeafe); }
        .dir-stat__icon--green { background: linear-gradient(135deg, #f0fdf4, #dcfce7); }
        .dir-stat__icon--purple{ background: linear-gradient(135deg, #faf5ff, #ede9fe); }
        .dir-stat__icon--orange{ background: linear-gradient(135deg, #fff7ed, #ffedd5); }
        .dir-stat__num {
            font-size: 1.75rem;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: -0.04em;
            line-height: 1;
        }
        .dir-stat__label {
            font-size: 0.8rem;
            color: #64748b;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-top: 3px;
        }

        /* ── GRAPH SECTION ── */
        .graph-section {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #e9eef5;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            margin-bottom: 3rem;
        }
        .graph-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e9eef5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        }
        #holding-network { width: 100%; height: 600px; background-color: #f8fafc; }

        /* ── SEARCH FORM (Inline) ── */
        .inline-search-form {
            display: flex; gap: 10px; flex-wrap: wrap; align-items: center;
        }
        .inline-search-form input, .inline-search-form select {
            padding: 10px 16px;
            border-radius: 12px;
            border: 1px solid #cbd5e1;
            background: #fff;
            color: #0f172a;
            font-size: 0.9rem;
            outline: none;
            transition: all 0.2s;
        }
        .inline-search-form input:focus, .inline-search-form select:focus {
            border-color: #60A5FA;
            box-shadow: 0 0 0 3px rgba(96,165,250,0.15);
        }
        .inline-search-btn {
            background: var(--dir-primary);
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 10px 20px;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.2s;
            font-size: 0.9rem;
        }
        .inline-search-btn:hover { background: #1b44d3; }

        /* ── SECTION HEADER ── */
        .section-header {
            display: flex;
            align-items: center;
            gap: 1.25rem;
            margin-bottom: 1.5rem;
        }
        .section-header__icon {
            width: 42px; height: 42px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            flex-shrink: 0;
        }
        .section-header__icon--indigo { background: linear-gradient(135deg, #6366f1, #4f46e5); }
        .section-header h2 {
            font-size: 1.6rem;
            font-weight: 800;
            color: #0f172a;
            margin: 0;
            letter-spacing: -0.025em;
        }
        .section-header .line {
            height: 1px;
            background: linear-gradient(90deg, #e2e8f0, transparent);
            flex-grow: 1;
        }

        /* ── PREMIUM TABLE ── */
        .prem-table-wrap {
            background: #fff;
            border-radius: 20px;
            border: 1px solid #e9eef5;
            overflow: hidden;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            margin-bottom: 1.5rem;
        }
        .prem-table {
            width: 100%;
            min-width: 580px;
            border-collapse: collapse;
            font-size: 0.92rem;
        }
        .prem-table thead tr {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 2px solid #e9eef5;
        }
        .prem-table thead th {
            padding: 14px 20px;
            color: #475569;
            font-weight: 700;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            text-align: left;
        }
        .prem-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.18s ease;
            cursor: pointer;
        }
        .prem-table tbody tr:last-child { border-bottom: none; }
        .prem-table tbody tr:hover {
            background: #fafbff;
            box-shadow: inset 3px 0 0 var(--dir-primary);
        }
        .prem-table td { padding: 14px 20px; vertical-align: middle; }

        .status-badge {
            display: inline-flex; align-items: center; justify-content: center;
            font-size: 0.72rem; padding: 4px 10px; border-radius: 99px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.04em;
        }
        .status-activa { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
        .status-inactiva { background: #f1f5f9; color: #64748b; border: 1px solid #e2e8f0; }

        /* ── RESPONSIVE ── */
        @media (max-width: 768px) {
            .dir-hero { padding: 60px 1.5rem 120px; }
            .dir-hero h1 { font-size: 2rem; }
            .container { padding: 0 1.25rem; }
            .dir-main-card { padding: 24px 20px; margin-top: -70px; border-radius: 20px; }
            .dir-stats { grid-template-columns: 1fr; }
            .graph-header { flex-direction: column; align-items: flex-start; gap: 1rem; }
            .inline-search-form { width: 100%; }
            .inline-search-form input, .inline-search-form select { width: 100%; flex: 1 1 100%; }
            .prem-table-wrap { overflow-x: auto; }
        }
    </style>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>
<?= view('partials/header') ?>

<header class="dir-hero">
    <div class="container">
        <a href="<?= site_url('listado-de-grupos-empresariales') ?>" style="display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.08); color: #cbd5e1; padding: 6px 14px; border-radius: 99px; font-size: 0.8rem; font-weight: 700; border: 1px solid rgba(255,255,255,0.15); letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 1.5rem; text-decoration: none; transition: all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.12)'; this.style.color='#fff';" onmouseout="this.style.background='rgba(255,255,255,0.08)'; this.style.color='#cbd5e1';">
            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M19 12H5M12 19l-7-7 7-7"/></svg>
            Volver a Grupos
        </a>

        <nav aria-label="breadcrumb" style="margin-bottom: 1.5rem;">
            <ol style="list-style: none; padding: 0; margin: 0; display: inline-flex; flex-wrap: wrap; align-items: center; justify-content: center; gap: 0.5rem; font-size: 0.85rem; color: #94a3b8; font-weight: 500;">
                <li><a href="<?= site_url() ?>" style="color: #cbd5e1; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#cbd5e1'">Inicio</a></li>
                <li><span style="color: #475569;">/</span></li>
                <li><a href="<?= site_url('listado-de-empresas') ?>" style="color: #cbd5e1; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#cbd5e1'">Directorios</a></li>
                <li><span style="color: #475569;">/</span></li>
                <li><a href="<?= site_url('listado-de-grupos-empresariales') ?>" style="color: #cbd5e1; text-decoration: none; transition: color 0.2s;" onmouseover="this.style.color='#fff'" onmouseout="this.style.color='#cbd5e1'">Grupos Empresariales</a></li>
                <li><span style="color: #475569;">/</span></li>
                <li style="color: #fff;" aria-current="page"><?= esc($holding['name']) ?></li>
            </ol>
        </nav>
        <h1>Grupo <span class="grad"><?= esc($holding['name']) ?></span></h1>
        <p>Análisis consolidado y estructura corporativa del Grupo <?= esc($holding['name']) ?>. Descubre su entramado de filiales y empresas participadas en España.</p>
    </div>
</header>

<main class="dir-main">
    <div class="container dir-main-card">

        <!-- ── STATS BAR ── -->
        <div class="dir-stats">
            <div class="dir-stat">
                <div class="dir-stat__icon dir-stat__icon--blue">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2152FF" stroke-width="2.5"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                </div>
                <div>
                    <div class="dir-stat__num"><?= number_format($aggregates['total_companies'], 0, ',', '.') ?></div>
                    <div class="dir-stat__label">Empresas en el Grupo</div>
                </div>
            </div>
            <div class="dir-stat">
                <div class="dir-stat__icon dir-stat__icon--green">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                </div>
                <div>
                    <?php if (empty($aggregates['total_capital']) || $aggregates['total_capital'] == 0): ?>
                        <div class="dir-stat__num" style="font-size: 1.1rem; color: #94a3b8; padding-top: 6px;">No disponible</div>
                    <?php else: ?>
                        <div class="dir-stat__num"><?= number_format($aggregates['total_capital'], 0, ',', '.') ?> €</div>
                    <?php endif; ?>
                    <div class="dir-stat__label">Capital Social Agregado*</div>
                </div>
            </div>
            <div class="dir-stat">
                <div class="dir-stat__icon dir-stat__icon--purple">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#7c3aed" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                </div>
                <div>
                    <?php if(!empty($aggregates['top_provinces'])): ?>
                        <div class="dir-stat__num"><?= esc(ucwords(strtolower($aggregates['top_provinces'][0]['province']))) ?></div>
                        <div class="dir-stat__label">Provincia Principal</div>
                    <?php else: ?>
                        <div class="dir-stat__num">-</div>
                        <div class="dir-stat__label">Provincia Principal</div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="dir-stat">
                <div class="dir-stat__icon dir-stat__icon--orange">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#ea580c" stroke-width="2.5"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                </div>
                <div>
                    <?php if(!empty($aggregates['top_sector'])): ?>
                        <div class="dir-stat__num" style="font-size: 1.1rem; line-height: 1.2; padding-top: 4px;"><?= esc(ucwords(strtolower(mb_substr($aggregates['top_sector']['sector'], 0, 28)))) ?><?= mb_strlen($aggregates['top_sector']['sector']) > 28 ? '...' : '' ?></div>
                        <div class="dir-stat__label">Sector Principal</div>
                    <?php else: ?>
                        <div class="dir-stat__num">-</div>
                        <div class="dir-stat__label">Sector Principal</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div style="margin-bottom: 3rem; background: #fff; padding: 2rem; border-radius: 20px; border: 1px solid #e9eef5; box-shadow: 0 4px 20px rgba(0,0,0,0.02); color: #475569; font-size: 1.05rem; line-height: 1.6;">
            El ecosistema corporativo del <strong>Grupo <?= esc($holding['name']) ?></strong> está compuesto por un total de <strong><?= number_format($aggregates['total_companies'], 0, ',', '.') ?> empresas filiales</strong> y participadas en España. 
            <?php if(!empty($aggregates['top_sector'])): ?>
                Su actividad principal se enmarca dentro del sector de <strong><?= esc($aggregates['top_sector']['sector']) ?></strong>.
            <?php endif; ?>
            <?php if(!empty($aggregates['top_provinces'])): ?>
                Con su base de operaciones principal concentrada en la provincia de <strong><?= esc(ucwords(strtolower($aggregates['top_provinces'][0]['province']))) ?></strong>, 
            <?php endif; ?>
            <?php if(!empty($aggregates['total_capital']) && $aggregates['total_capital'] > 0): ?>
                este holding suma un capital social agregado aproximado de <strong><?= number_format($aggregates['total_capital'], 0, ',', '.') ?> €</strong>, consolidándose como un entramado societario de relevancia en el tejido empresarial nacional.
            <?php else: ?>
                consolidándose como un entramado societario de gran relevancia en el tejido empresarial nacional.
            <?php endif; ?>
        </div>

        <!-- ── GRAPH SECTION ── -->
        <section style="margin-bottom: 48px;">
            <div class="section-header">
                <div class="section-header__icon section-header__icon--indigo">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                </div>
                <h2>Mapa de Poder Corporativo</h2>
                <div class="line"></div>
            </div>
            
            <div class="graph-section">
                <div class="graph-header">
                    <div>
                        <p style="margin: 0; font-size: 0.95rem; color: #475569; font-weight: 500;">Visualización interactiva de la red de filiales del grupo.</p>
                    </div>
                    <form action="<?= current_url() ?>" method="GET" class="inline-search-form">
                        <input type="text" name="q" placeholder="Buscar por Nombre o CIF..." value="<?= esc($filters['q'] ?? '') ?>" style="width: 220px;">
                        <select name="status">
                            <option value="">Todos los Estados</option>
                            <option value="activa" <?= (($filters['status'] ?? '') == 'activa') ? 'selected' : '' ?>>Solo Activas</option>
                            <option value="inactiva" <?= (($filters['status'] ?? '') == 'inactiva') ? 'selected' : '' ?>>Disueltas / Inactivas</option>
                        </select>
                        <button type="submit" class="inline-search-btn">Filtrar</button>
                        <?php if (!empty($filters['q']) || !empty($filters['status'])): ?>
                            <a href="<?= current_url() ?>" style="display: flex; align-items: center; text-decoration: none; color: #64748b; font-size: 0.9rem; font-weight: 600; margin-left: 8px;">Limpiar</a>
                        <?php endif; ?>
                    </form>
                </div>
                <div id="holding-network"></div>
            </div>
        </section>

        <!-- ── TABLE SECTION ── -->
        <section>
            <div class="section-header">
                <div class="section-header__icon" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="2.5"><line x1="8" y1="6" x2="21" y2="6"></line><line x1="8" y1="12" x2="21" y2="12"></line><line x1="8" y1="18" x2="21" y2="18"></line><line x1="3" y1="6" x2="3.01" y2="6"></line><line x1="3" y1="12" x2="3.01" y2="12"></line><line x1="3" y1="18" x2="3.01" y2="18"></line></svg>
                </div>
                <h2>Estructura de Filiales</h2>
                <div class="line"></div>
            </div>

            <div class="prem-table-wrap">
                <table class="prem-table">
                    <thead>
                        <tr>
                            <th>Empresa Filial</th>
                            <th>CIF</th>
                            <th>Provincia</th>
                            <th style="text-align: right;">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($holdingCompanies as $hc): 
                            $statusLower = strtolower($hc['status'] ?? '');
                            $isActiva = ($statusLower === 'activa' || $statusLower === 'activo');
                        ?>
                        <tr onclick="window.location='<?= site_url('informacion-empresa/' . esc($hc['cif'])) ?>'">
                            <td style="font-weight: 700;">
                                <a href="<?= site_url('informacion-empresa/' . esc($hc['cif'])) ?>" style="color: #0f172a; text-decoration: none;">
                                    <?= esc($hc['name']) ?>
                                </a>
                            </td>
                            <td style="font-family: monospace; font-size: 0.95rem; font-weight: 600; color: #64748b;"><?= esc($hc['cif']) ?></td>
                            <td style="text-transform: capitalize; color: #475569; font-weight: 500;"><?= esc(strtolower($hc['province'] ?? '')) ?: '-' ?></td>
                            <td style="text-align: right;">
                                <span class="status-badge <?= $isActiva ? 'status-activa' : 'status-inactiva' ?>">
                                    <?= esc($hc['status']) ?: 'Desconocido' ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php if(empty($holdingCompanies)): ?>
                <div style="padding: 4rem 2rem; text-align: center; color: #64748b; font-weight: 600;">
                    No se encontraron filiales con los filtros actuales.
                </div>
                <?php endif; ?>
            </div>
            
            <div style="text-align: center; color: #94a3b8; font-size: 0.85rem; padding-top: 1rem;">
                *El capital social agregado es una estimación basada en los datos reportados en formato de red y puede no representar el valor real de mercado ni tener en cuenta filiales intermedias.
            </div>
        </section>

    </div>
</main>

<?= view('partials/footer') ?>

<!-- Vis.js -->
<script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('holding-network');
        const rawData = <?= $holdingGraphData ?>;
        
        const data = {
            nodes: new vis.DataSet(rawData.nodes),
            edges: new vis.DataSet(rawData.edges)
        };
        
        const options = {
            nodes: {
                borderWidth: 2,
                borderWidthSelected: 3
            },
            edges: {
                width: 1,
                selectionWidth: 2,
                smooth: {
                    type: 'continuous'
                }
            },
            physics: {
                barnesHut: {
                    gravitationalConstant: -3000,
                    centralGravity: 0.3,
                    springLength: 200,
                    springConstant: 0.04,
                    damping: 0.09
                },
                stabilization: {
                    iterations: 150
                }
            },
            interaction: {
                hover: true,
                tooltipDelay: 200,
                zoomView: true,
                dragView: true
            }
        };
        
        const network = new vis.Network(container, data, options);
        
        network.on("click", function (params) {
            if (params.nodes.length > 0) {
                const nodeId = params.nodes[0];
                const node = data.nodes.get(nodeId);
                if (node && node.url) {
                    window.location.href = node.url;
                }
            }
        });
    });
</script>

<?php
$schemaSubOrgs = [];
foreach(array_slice($holdingCompanies, 0, 20) as $hc) {
    $schemaSubOrgs[] = [
        '@type' => 'Organization',
        'name' => $hc['name'],
        'taxID' => $hc['cif']
    ];
}

$schemaData = [
    '@context' => 'https://schema.org',
    '@type' => 'Organization',
    'name' => 'Grupo ' . $holding['name'],
    'url' => site_url('grupos-empresariales/' . $holding['slug']),
    'subOrganization' => $schemaSubOrgs
];

$breadcrumbData = [
    '@context' => 'https://schema.org',
    '@type' => 'BreadcrumbList',
    'itemListElement' => [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Inicio',
            'item' => site_url()
        ],
        [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Directorios',
            'item' => site_url('listado-de-empresas')
        ],
        [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => 'Grupos Empresariales',
            'item' => site_url('listado-de-grupos-empresariales')
        ],
        [
            '@type' => 'ListItem',
            'position' => 4,
            'name' => 'Grupo ' . $holding['name']
        ]
    ]
];
?>
<script type="application/ld+json">
<?= json_encode($schemaData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>
<script type="application/ld+json">
<?= json_encode($breadcrumbData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
</script>
</body>
</html>
