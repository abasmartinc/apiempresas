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
        body { background-color: #f8fafc; font-family: 'Inter', sans-serif; }
        .holding-header { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); color: white; padding: 4rem 2rem 6rem 2rem; text-align: center; }
        .holding-title { font-size: 2.5rem; font-weight: 800; margin-bottom: 1rem; letter-spacing: -0.02em; }
        .holding-subtitle { font-size: 1.1rem; color: #94a3b8; max-width: 600px; margin: 0 auto 2rem auto; line-height: 1.6; }
        .kpi-container { display: flex; justify-content: center; gap: 2rem; margin-top: -3rem; padding: 0 2rem; flex-wrap: wrap; }
        .kpi-card { background: white; border-radius: 16px; padding: 1.5rem 2rem; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0; text-align: center; min-width: 200px; }
        .kpi-value { font-size: 2rem; font-weight: 800; color: #0f172a; margin-bottom: 0.25rem; }
        .kpi-label { font-size: 0.85rem; font-weight: 600; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; }
        .graph-section { max-width: 1200px; margin: 3rem auto; background: white; border-radius: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; overflow: hidden; }
        .graph-header { padding: 1.5rem 2rem; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center; }
        .table-section { max-width: 1200px; margin: 0 auto 4rem auto; background: white; border-radius: 24px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); border: 1px solid #e2e8f0; overflow: hidden; }
        #holding-network { width: 100%; height: 600px; background-color: #f8fafc; }
        
        .holding-table th { text-align: left; padding: 16px 24px; background: #f8fafc; color: #475569; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em; border-bottom: 1px solid #e2e8f0; }
        .holding-table td { padding: 16px 24px; color: #0f172a; font-size: 0.95rem; border-bottom: 1px solid #f1f5f9; }
        .holding-table tr:hover { background: #f8fafc; }
        .status-badge { display: inline-block; font-size: 0.75rem; padding: 4px 10px; border-radius: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em; }
        .status-activa { background: #dcfce7; color: #16a34a; }
        .status-inactiva { background: #f1f5f9; color: #64748b; }
    </style>
</head>
<body>
    <?= view('partials/header') ?>

    <main>
        <!-- Hero Header -->
        <div class="holding-header">
            <div style="display: inline-flex; align-items: center; gap: 6px; background: rgba(255,255,255,0.1); color: #e2e8f0; padding: 6px 14px; border-radius: 999px; font-size: 0.8rem; font-weight: 700; border: 1px solid rgba(255,255,255,0.2); letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 1rem;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                Directorio de Grupos
            </div>
            <h1 class="holding-title">Grupo <?= esc($holding['name']) ?></h1>
            <p class="holding-subtitle">
                Análisis consolidado y estructura corporativa del Grupo <?= esc($holding['name']) ?>. Descubre su entramado de filiales y empresas participadas.
            </p>
        </div>

        <!-- KPIs -->
        <div class="kpi-container">
            <div class="kpi-card">
                <div class="kpi-value"><?= number_format($aggregates['total_companies'], 0, ',', '.') ?></div>
                <div class="kpi-label">Empresas en el Grupo</div>
            </div>
            <div class="kpi-card">
                <div class="kpi-value"><?= number_format($aggregates['total_capital'] ?? 0, 0, ',', '.') ?> €</div>
                <div class="kpi-label">Capital Social Agregado*</div>
            </div>
            <?php if(!empty($aggregates['top_provinces'])): ?>
            <div class="kpi-card">
                <div class="kpi-value"><?= esc(ucwords(strtolower($aggregates['top_provinces'][0]['province']))) ?></div>
                <div class="kpi-label">Provincia Principal</div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Graph Section -->
        <div class="graph-section">
            <div class="graph-header" style="flex-wrap: wrap; gap: 1rem;">
                <div>
                    <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #0f172a;">Mapa de Poder Corporativo</h2>
                    <p style="margin: 4px 0 0 0; font-size: 0.9rem; color: #64748b;">Visualización interactiva de la red de filiales del grupo.</p>
                </div>
                <form action="<?= current_url() ?>" method="GET" style="display: flex; gap: 10px; flex-wrap: wrap;">
                    <input type="text" name="q" placeholder="Buscar por Nombre o CIF..." value="<?= esc($filters['q'] ?? '') ?>" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 0.9rem; width: 220px;">
                    <select name="status" style="padding: 8px 16px; border-radius: 8px; border: 1px solid #cbd5e1; outline: none; font-size: 0.9rem; background: white;">
                        <option value="">Todos los Estados</option>
                        <option value="activa" <?= (($filters['status'] ?? '') == 'activa') ? 'selected' : '' ?>>Solo Activas</option>
                        <option value="inactiva" <?= (($filters['status'] ?? '') == 'inactiva') ? 'selected' : '' ?>>Disueltas / Inactivas</option>
                    </select>
                    <button type="submit" style="background: #4F46E5; color: white; border: none; border-radius: 8px; padding: 8px 20px; font-weight: 600; cursor: pointer; transition: background 0.2s;">Filtrar</button>
                    <?php if (!empty($filters['q']) || !empty($filters['status'])): ?>
                        <a href="<?= current_url() ?>" style="display: flex; align-items: center; text-decoration: none; color: #64748b; font-size: 0.9rem; margin-left: 10px;">Limpiar</a>
                    <?php endif; ?>
                </form>
            </div>
            <div id="holding-network"></div>
        </div>

        <!-- Table Section -->
        <div class="table-section">
            <div class="graph-header">
                <div>
                    <h2 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: #0f172a;">Estructura de Filiales</h2>
                    <p style="margin: 4px 0 0 0; font-size: 0.9rem; color: #64748b;">Mostrando el listado de filiales (Top 100 por relevancia).</p>
                </div>
            </div>
            <div style="overflow-x: auto;">
                <table class="holding-table" style="width: 100%; border-collapse: collapse; min-width: 800px;">
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
                        <tr onclick="window.location='<?= site_url('informacion-empresa/' . esc($hc['cif'])) ?>';" style="cursor: pointer; transition: background-color 0.2s;" onmouseover="this.style.backgroundColor='#f8fafc';" onmouseout="this.style.backgroundColor='transparent';">
                            <td style="font-weight: 600;">
                                <a href="<?= site_url('informacion-empresa/' . esc($hc['cif'])) ?>" style="color: #0f172a; text-decoration: none;">
                                    <?= esc($hc['name']) ?>
                                </a>
                            </td>
                            <td style="font-family: monospace; color: #64748b;"><?= esc($hc['cif']) ?></td>
                            <td style="text-transform: capitalize; color: #475569;"><?= esc(strtolower($hc['province'] ?? '')) ?: '-' ?></td>
                            <td style="text-align: right;">
                                <span class="status-badge <?= $isActiva ? 'status-activa' : 'status-inactiva' ?>">
                                    <?= esc($hc['status']) ?: 'Desconocido' ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="padding: 1rem 2rem; background: #f8fafc; text-align: center; color: #64748b; font-size: 0.85rem; border-top: 1px solid #e2e8f0;">
                *El capital social agregado es una estimación basada en los datos reportados y puede no representar el valor real de mercado.
            </div>
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
</body>
</html>
