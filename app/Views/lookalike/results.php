<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head') ?>
    <title>Resultados Similares - APIEmpresas</title>
    <style>
        .results-hero {
            padding: 60px 0 40px;
            text-align: center;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
        }

        .results-title {
            font-size: 2.5rem;
            font-weight: 900;
            color: var(--ae-dark, #0f172a);
            margin-bottom: 16px;
        }

        .results-subtitle {
            font-size: 1.1rem;
            color: var(--ae-slate, #64748b);
            max-width: 600px;
            margin: 0 auto;
        }

        .highlight-number {
            color: var(--ae-blue, #2563eb);
            font-weight: 900;
        }

        .results-container {
            max-width: 900px;
            margin: 0 auto 80px;
            position: relative;
        }

        .results-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            border: 1px solid var(--ae-border, #e2e8f0);
        }

        .results-table th {
            background: #f8fafc;
            padding: 16px 24px;
            text-align: left;
            font-weight: 700;
            color: var(--ae-slate, #64748b);
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid var(--ae-border, #e2e8f0);
        }

        .results-table td {
            padding: 20px 24px;
            border-bottom: 1px solid var(--ae-border, #e2e8f0);
            vertical-align: middle;
        }

        .results-table tr:last-child td {
            border-bottom: none;
        }

        .company-name {
            font-weight: 800;
            color: var(--ae-dark, #0f172a);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .match-badge {
            background: #dcfce7;
            color: #166534;
            padding: 4px 8px;
            border-radius: 99px;
            font-size: 0.75rem;
            font-weight: 800;
            white-space: nowrap;
            display: inline-block;
        }

        .meta-text {
            color: var(--ae-slate, #64748b);
            font-size: 0.9rem;
        }

        /* The Paywall / Blur effect */
        .paywall-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 550px;
            background: linear-gradient(to bottom, rgba(248,250,252,0) 0%, rgba(248,250,252,0.98) 25%, rgba(248,250,252,1) 100%);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-end;
            padding-bottom: 40px;
            border-bottom-left-radius: 16px;
            border-bottom-right-radius: 16px;
        }

        .blurred-row {
            filter: blur(4px);
            opacity: 0.5;
            user-select: none;
        }

        .btn-unlock {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: #0F172A;
            padding: 16px 32px;
            border-radius: 14px;
            font-weight: 800;
            font-size: 1.1rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(245, 158, 11, 0.3);
            display: inline-flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s ease;
            text-decoration: none;
        }

        .btn-unlock:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 25px rgba(245, 158, 11, 0.4);
        }
        
        .paywall-features {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            max-width: 800px;
            margin: 0 auto 32px;
            text-align: left;
        }
        .pf-item {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            gap: 16px;
            align-items: flex-start;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .pf-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08);
            border-color: #cbd5e1;
        }
        .pf-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .pf-text strong {
            display: block;
            color: var(--ae-dark);
            font-size: 1.05rem;
            margin-bottom: 6px;
            font-weight: 800;
            letter-spacing: -0.01em;
        }
        .pf-text span {
            color: var(--ae-slate);
            font-size: 0.9rem;
            line-height: 1.5;
            display: block;
            font-weight: 500;
        }
        @media(max-width: 768px) {
            .paywall-features {
                grid-template-columns: 1fr;
                padding: 0 16px;
            }
        }
    </style>
</head>

<body>

    <?= view('partials/header') ?>

    <main>
        <section class="results-hero">
            <div class="container">
                <h1 class="results-title">¡Análisis Completado!</h1>
                <p class="results-subtitle">Basándonos en tu archivo <strong><?= esc($filename) ?></strong>, nuestro algoritmo ha identificado <span class="highlight-number"><?= esc($total_found) ?> empresas gemelas</span> a tus mejores clientes en España.</p>
            </div>
        </section>

        <section class="container">
            <div class="results-container">
                <table class="results-table">
                    <thead>
                        <tr>
                            <th>Empresa Similar</th>
                            <th>Ubicación</th>
                            <th>Sector (CNAE)</th>
                            <th>Similitud</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($results as $index => $row): ?>
                            <tr class="<?= $index >= 2 ? 'blurred-row' : '' ?>">
                                <td>
                                    <div class="company-name">
                                        <?= esc($row['name']) ?>
                                    </div>
                                    <div class="meta-text" style="font-size: 0.8rem; margin-top: 4px;">CIF: <?= esc($row['cif']) ?></div>
                                </td>
                                <td>
                                    <div class="meta-text"><?= esc($row['province']) ?></div>
                                </td>
                                <td>
                                    <div class="meta-text" style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" title="<?= esc($row['cnae']) ?>">
                                        <?= esc($row['cnae']) ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="match-badge">Match <?= esc($row['match_score']) ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        
                        <!-- Add a few extra fake blurred rows to make it look like a long list -->
                        <tr class="blurred-row">
                            <td><div class="company-name">Empresa Tecnológica SL</div><div class="meta-text">CIF: B12300000</div></td>
                            <td><div class="meta-text">Zaragoza</div></td>
                            <td><div class="meta-text">6201 - Programación</div></td>
                            <td><span class="match-badge">Match 85%</span></td>
                        </tr>
                        <tr class="blurred-row">
                            <td><div class="company-name">Sistemas Avanzados SA</div><div class="meta-text">CIF: A87654321</div></td>
                            <td><div class="meta-text">Bilbao</div></td>
                            <td><div class="meta-text">6202 - Consultoría</div></td>
                            <td><span class="match-badge">Match 84%</span></td>
                        </tr>
                        <tr class="blurred-row">
                            <td><div class="company-name">Innovación Digital SL</div><div class="meta-text">CIF: B55555555</div></td>
                            <td><div class="meta-text">Málaga</div></td>
                            <td><div class="meta-text">6201 - Programación</div></td>
                            <td><span class="match-badge">Match 82%</span></td>
                        </tr>
                        <tr class="blurred-row">
                            <td><div class="company-name">Cloud Solutions SA</div><div class="meta-text">CIF: A44444444</div></td>
                            <td><div class="meta-text">Alicante</div></td>
                            <td><div class="meta-text">6209 - Otros servicios</div></td>
                            <td><span class="match-badge">Match 80%</span></td>
                        </tr>
                        <tr class="blurred-row">
                            <td><div class="company-name">Desarrollo Web SL</div><div class="meta-text">CIF: B33333333</div></td>
                            <td><div class="meta-text">Murcia</div></td>
                            <td><div class="meta-text">6201 - Programación</div></td>
                            <td><span class="match-badge">Match 78%</span></td>
                        </tr>
                    </tbody>
                </table>

                <div class="paywall-overlay">
                    <h3 style="font-size: 1.5rem; font-weight: 800; color: var(--ae-dark); margin-bottom: 8px;">Desbloquea los <?= esc($total_found) ?> prospectos</h3>
                    <p style="color: var(--ae-slate); margin-bottom: 24px; font-weight: 500;">Al descargar el archivo Excel completo, obtendrás acceso a todos estos datos premium para cada empresa:</p>
                    
                    <div class="paywall-features">
                        <div class="pf-item">
                            <div class="pf-icon" style="background: #eff6ff; color: #2563eb;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            </div>
                            <div class="pf-text">
                                <strong>Ubicación y Contacto</strong>
                                <span>Dirección postal completa, y (según disponibilidad) email y teléfonos.</span>
                            </div>
                        </div>

                        <div class="pf-item">
                            <div class="pf-icon" style="background: #fdf4ff; color: #c026d3;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            </div>
                            <div class="pf-text">
                                <strong>Órgano Directivo</strong>
                                <span>Nombres y cargos de los administradores y directivos.</span>
                            </div>
                        </div>

                        <div class="pf-item">
                            <div class="pf-icon" style="background: #fffbeb; color: #d97706;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2v20"></path><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                            </div>
                            <div class="pf-text">
                                <strong>Historial Financiero</strong>
                                <span>Volumen de Subvenciones, Contratos Públicos e importes (€).</span>
                            </div>
                        </div>

                        <div class="pf-item">
                            <div class="pf-icon" style="background: #f0fdf4; color: #16a34a;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect><line x1="3" y1="9" x2="21" y2="9"></line><line x1="9" y1="21" x2="9" y2="9"></line></svg>
                            </div>
                            <div class="pf-text">
                                <strong>Datos Generales</strong>
                                <span>CIF, Razón Social, Sector CNAE detallado y Ventas Estimadas.</span>
                            </div>
                        </div>
                    </div>

                    <form action="<?= site_url('billing/checkout') ?>" method="POST" style="margin: 0;">
                        <?= csrf_field() ?>
                        <input type="hidden" name="plan" value="lookalike_single">
                        <input type="hidden" name="total_count" value="<?= esc($total_found) ?>">
                        <input type="hidden" name="price" value="<?= esc($price) ?>">
                        
                        <button type="submit" class="btn-unlock">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                            Desbloquear Audiencia Completa (<?= number_format($price ?? 49, 2, ',', '.') ?>€ + IVA)
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <?= view('partials/footer') ?>
</body>
</html>
