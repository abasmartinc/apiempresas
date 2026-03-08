<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Radar de Nuevas Empresas en España | Detecta Leads B2B Cada Día',
        'excerptText' => 'Detecta empresas recién creadas en España y accede a oportunidades comerciales antes que tu competencia. Datos del BORME actualizados diariamente.',
        'canonical'   => site_url('precios-radar'),
        'robots'      => 'index,follow',
    ]) ?>
    <style>
        :root {
            --primary: #2152ff;
            --primary-dark: #133a82;
            --green: #10b981;
            --slate-900: #0f172a;
            --slate-700: #334155;
            --slate-500: #64748b;
            --slate-300: #cbd5e1;
            --border: #e2e8f0;
            --bg-light: #f8fafc;
        }

        * { box-sizing: border-box; }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--slate-900);
            background: #fff;
            margin: 0;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 0 24px;
        }

        /* ─── HERO ─────────────────────────────────────────── */
        .hero {
            padding: 96px 0 72px;
            text-align: center;
            background: linear-gradient(180deg, #f0f4ff 0%, #ffffff 100%);
            border-bottom: 1px solid var(--border);
        }
        .hero-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(33,82,255,0.08);
            color: var(--primary);
            border: 1px solid rgba(33,82,255,0.15);
            border-radius: 999px;
            padding: 8px 16px;
            font-size: 0.78rem;
            font-weight: 900;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 28px;
        }
        .hero-kicker::before {
            content: '';
            width: 8px; height: 8px;
            border-radius: 50%;
            background: var(--green);
            box-shadow: 0 0 8px rgba(16,185,129,0.6);
        }
        .hero-title {
            font-size: clamp(2.4rem, 5vw, 3.8rem);
            font-weight: 950;
            line-height: 1.08;
            letter-spacing: -0.03em;
            margin: 0 0 20px;
        }
        .hero-title .grad {
            background: linear-gradient(90deg, #133A82, #2152ff, #12b48a);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
        }
        .hero-subtitle {
            font-size: 1.2rem;
            color: var(--slate-500);
            max-width: 580px;
            margin: 0 auto 36px;
            line-height: 1.65;
        }
        .hero-bullets {
            display: flex;
            flex-direction: column;
            gap: 12px;
            align-items: center;
            margin-bottom: 44px;
        }
        .hero-bullet {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1rem;
            font-weight: 600;
            color: var(--slate-700);
        }
        .hero-bullet svg { color: var(--green); flex-shrink: 0; }
        .cta-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            background: linear-gradient(135deg, #2152ff, #133a82);
            color: white;
            font-size: 1.1rem;
            font-weight: 900;
            padding: 18px 44px;
            border-radius: 14px;
            text-decoration: none;
            border: none;
            cursor: pointer;
            box-shadow: 0 14px 28px -10px rgba(33,82,255,0.45);
            transition: all 0.22s ease;
            letter-spacing: -0.01em;
        }
        .cta-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 18px 32px -10px rgba(33,82,255,0.52);
            color: white;
        }
        .hero-cancel {
            margin-top: 14px;
            font-size: 0.88rem;
            color: var(--slate-500);
        }

        /* ─── SECTION BASE ─────────────────────────────────── */
        .section { padding: 88px 0; }
        .section-alt { background: var(--bg-light); }
        .section-dark { background: var(--slate-900); color: white; }
        .section-title {
            font-size: clamp(1.8rem, 3vw, 2.6rem);
            font-weight: 950;
            letter-spacing: -0.025em;
            margin: 0 0 16px;
            line-height: 1.1;
        }
        .section-sub {
            font-size: 1.1rem;
            color: var(--slate-500);
            max-width: 640px;
            line-height: 1.65;
            margin: 0 0 52px;
        }
        .section-sub.dark { color: rgba(255,255,255,0.72); }

        /* ─── PROBLEM ──────────────────────────────────────── */
        .problem-body {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 48px;
            align-items: center;
        }
        .problem-text p {
            font-size: 1.1rem;
            color: var(--slate-700);
            line-height: 1.75;
            margin: 0 0 20px;
        }
        .problem-visual {
            background: linear-gradient(135deg, #eef4ff, #e0edff);
            border: 1px solid #c7dbff;
            border-radius: 24px;
            padding: 32px;
        }
        .timeline-item {
            display: flex;
            gap: 16px;
            align-items: flex-start;
            margin-bottom: 24px;
        }
        .timeline-dot {
            width: 36px; height: 36px;
            border-radius: 50%;
            background: var(--primary);
            color: white;
            font-weight: 900;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }
        .timeline-item:last-child { margin-bottom: 0; }
        .timeline-label {
            font-weight: 700;
            color: var(--slate-900);
            font-size: 0.95rem;
            margin-bottom: 4px;
        }
        .timeline-desc {
            font-size: 0.88rem;
            color: var(--slate-500);
            line-height: 1.5;
        }

        /* ─── EXAMPLE TABLE ────────────────────────────────── */
        .leads-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 4px 24px rgba(15,23,42,0.08);
            border: 1px solid var(--border);
        }
        .leads-table thead {
            background: var(--slate-900);
            color: white;
        }
        .leads-table thead th {
            padding: 16px 20px;
            text-align: left;
            font-size: 0.78rem;
            font-weight: 900;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        .leads-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background 0.15s;
        }
        .leads-table tbody tr:last-child { border-bottom: none; }
        .leads-table tbody tr:hover { background: #f8fafc; }
        .leads-table tbody td {
            padding: 18px 20px;
            font-size: 0.95rem;
        }
        .table-company { font-weight: 800; color: var(--slate-900); }
        .table-sector {
            display: inline-block;
            background: #eff6ff;
            color: #1d4ed8;
            border-radius: 6px;
            padding: 4px 10px;
            font-size: 0.82rem;
            font-weight: 700;
        }
        .table-province { color: var(--slate-500); font-weight: 600; }
        .table-date {
            font-weight: 700;
            color: var(--green);
            white-space: nowrap;
        }
        .table-cta-row {
            margin-top: 28px;
            text-align: center;
        }
        .cta-ghost {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            color: var(--primary);
            font-weight: 800;
            font-size: 1rem;
            text-decoration: none;
            padding: 14px 28px;
            border: 2px solid rgba(33,82,255,0.2);
            border-radius: 12px;
            transition: all 0.2s;
        }
        .cta-ghost:hover {
            background: rgba(33,82,255,0.05);
            border-color: var(--primary);
        }

        /* ─── HOW IT WORKS ─────────────────────────────────── */
        .steps-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 28px;
        }
        .step-card {
            background: white;
            border: 1px solid var(--border);
            border-radius: 22px;
            padding: 32px 28px;
            position: relative;
            transition: all 0.22s;
        }
        .step-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 32px rgba(15,23,42,0.08);
        }
        .step-number {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, #2152ff, #133a82);
            color: white;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            font-weight: 950;
            margin-bottom: 20px;
        }
        .step-title {
            font-size: 1.15rem;
            font-weight: 900;
            margin: 0 0 10px;
            color: var(--slate-900);
        }
        .step-desc {
            font-size: 0.95rem;
            color: var(--slate-500);
            line-height: 1.6;
            margin: 0;
        }

        /* ─── INCLUDED ─────────────────────────────────────── */
        .included-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 18px;
        }
        .included-item {
            display: flex;
            align-items: center;
            gap: 14px;
            background: white;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 18px 20px;
            font-weight: 700;
            font-size: 0.98rem;
            color: var(--slate-700);
            transition: all 0.2s;
        }
        .included-item:hover {
            border-color: rgba(33,82,255,0.2);
            transform: translateY(-2px);
        }
        .included-icon {
            width: 38px; height: 38px;
            background: #eff6ff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--primary);
            flex-shrink: 0;
        }

        /* ─── METRICS ──────────────────────────────────────── */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2px;
            background: rgba(255,255,255,0.06);
            border-radius: 24px;
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.10);
        }
        .metric-cell {
            background: rgba(255,255,255,0.04);
            padding: 48px 32px;
            text-align: center;
        }
        .metric-value {
            font-size: clamp(2.2rem, 4vw, 3.2rem);
            font-weight: 950;
            letter-spacing: -0.03em;
            color: white;
            margin-bottom: 10px;
            line-height: 1;
        }
        .metric-label {
            font-size: 1rem;
            color: rgba(255,255,255,0.65);
            font-weight: 600;
            line-height: 1.4;
        }

        /* ─── PRICING ──────────────────────────────────────── */
        .pricing-card {
            max-width: 520px;
            margin: 0 auto;
            background: white;
            border: 2px solid var(--primary);
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 24px 48px -20px rgba(33,82,255,0.22);
        }
        .pricing-header {
            background: linear-gradient(135deg, #133a82, #2152ff);
            padding: 36px 40px 32px;
            color: white;
            text-align: center;
        }
        .pricing-name {
            font-size: 0.82rem;
            font-weight: 900;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            opacity: 0.75;
            margin-bottom: 16px;
        }
        .pricing-price {
            font-size: 4rem;
            font-weight: 950;
            letter-spacing: -0.04em;
            line-height: 1;
        }
        .pricing-price span { font-size: 1.3rem; font-weight: 600; opacity: 0.75; }
        .pricing-desc {
            margin-top: 14px;
            font-size: 1rem;
            opacity: 0.85;
            line-height: 1.55;
        }
        .pricing-body {
            padding: 36px 40px 40px;
        }
        .pricing-features {
            list-style: none;
            padding: 0;
            margin: 0 0 36px;
        }
        .pricing-feature {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid #f1f5f9;
            font-size: 1rem;
            font-weight: 600;
            color: var(--slate-700);
        }
        .pricing-feature:last-child { border-bottom: none; }
        .pricing-feature svg { color: var(--green); flex-shrink: 0; }

        /* ─── COMPARISON ───────────────────────────────────── */
        .comparison-table {
            width: 100%;
            border-collapse: collapse;
            border-radius: 18px;
            overflow: hidden;
            border: 1px solid var(--border);
            box-shadow: 0 4px 24px rgba(15,23,42,0.06);
        }
        .comparison-table thead th {
            padding: 20px 28px;
            font-size: 1rem;
            font-weight: 900;
        }
        .comparison-table thead th:first-child {
            text-align: left;
            background: #f8fafc;
            color: var(--slate-500);
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }
        .comparison-table thead th.col-radar {
            background: var(--primary);
            color: white;
            text-align: center;
        }
        .comparison-table thead th.col-excel {
            background: #f1f5f9;
            color: var(--slate-700);
            text-align: center;
        }
        .comparison-table tbody tr { border-bottom: 1px solid #f1f5f9; }
        .comparison-table tbody tr:last-child { border-bottom: none; }
        .comparison-table tbody tr:hover { background: #fafbff; }
        .comparison-table tbody td {
            padding: 18px 28px;
            font-size: 0.97rem;
        }
        .comparison-table tbody td:first-child { font-weight: 700; color: var(--slate-700); }
        .comparison-table tbody td:not(:first-child) { text-align: center; }
        .comparison-table tbody td.col-radar { background: rgba(33,82,255,0.03); font-weight: 700; color: var(--primary); }
        .comparison-check { color: var(--green); font-weight: 900; font-size: 1.1rem; }
        .comparison-cross { color: var(--slate-300); font-size: 1.1rem; }

        /* ─── FAQ ──────────────────────────────────────────── */
        .faq-list { max-width: 740px; margin: 0 auto; }
        .faq-item {
            border-bottom: 1px solid var(--border);
            padding: 24px 0;
        }
        .faq-question {
            font-size: 1.05rem;
            font-weight: 800;
            color: var(--slate-900);
            margin: 0 0 10px;
        }
        .faq-answer {
            font-size: 0.98rem;
            color: var(--slate-500);
            line-height: 1.7;
            margin: 0;
        }

        /* ─── FINAL CTA ────────────────────────────────────── */
        .final-cta {
            padding: 100px 0;
            text-align: center;
            background: linear-gradient(135deg, #0f172a 0%, #133a82 50%, #1d4ed8 100%);
        }
        .final-cta .section-title { color: white; }
        .final-cta-sub {
            font-size: 1.15rem;
            color: rgba(255,255,255,0.72);
            max-width: 500px;
            margin: 0 auto 44px;
            line-height: 1.6;
        }
        .cta-white {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: white;
            color: var(--slate-900);
            font-size: 1.1rem;
            font-weight: 900;
            padding: 18px 48px;
            border-radius: 14px;
            text-decoration: none;
            box-shadow: 0 14px 28px rgba(0,0,0,0.2);
            transition: all 0.22s;
        }
        .cta-white:hover {
            background: #f8fafc;
            transform: translateY(-2px);
            color: var(--slate-900);
        }

        /* ─── RESPONSIVE ───────────────────────────────────── */
        @media (max-width: 860px) {
            .problem-body { grid-template-columns: 1fr; }
            .steps-grid { grid-template-columns: 1fr; }
            .metrics-grid { grid-template-columns: 1fr; }
            .comparison-table thead th, .comparison-table tbody td { padding: 14px 14px; font-size: 0.9rem; }
        }
        @media (max-width: 640px) {
            .section { padding: 64px 0; }
            .hero { padding: 64px 0 52px; }
            .pricing-header { padding: 28px 24px; }
            .pricing-body { padding: 28px 24px; }
            .leads-table thead { display: none; }
            .leads-table tbody tr { display: block; padding: 16px; border-bottom: 2px solid var(--border); }
            .leads-table tbody td { display: block; padding: 4px 0; font-size: 0.9rem; }
            .leads-table tbody td::before {
                content: attr(data-label) ': ';
                font-weight: 700;
                color: var(--slate-500);
                font-size: 0.78rem;
            }
        }
    </style>
</head>
<body>
<?= view('partials/header') ?>

<main>

<!-- ══════════════════════════════════════════════════════════
     1. HERO
══════════════════════════════════════════════════════════ -->
<section class="hero">
    <div class="container">
        <div class="hero-kicker">Radar de nuevas empresas</div>

        <h1 class="hero-title">
            Detecta nuevas empresas<br>
            <span class="grad">antes que tu competencia</span>
        </h1>

        <p class="hero-subtitle">
            Detecta empresas recién creadas y accede a oportunidades comerciales antes que tu competencia.
        </p>

        <div class="hero-bullets">
            <div class="hero-bullet">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Nuevas empresas detectadas diariamente desde el BORME
            </div>
            <div class="hero-bullet">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Filtra por sector, provincia o fecha de constitución
            </div>
            <div class="hero-bullet">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Exporta leads listos para prospección
            </div>
        </div>

        <a href="<?= site_url('precios-radar') ?>" class="cta-primary">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            Activar Radar
        </a>
        <p class="hero-cancel">Cancelar en cualquier momento. Sin permanencia.</p>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     2. PROBLEMA
══════════════════════════════════════════════════════════ -->
<section class="section">
    <div class="container">
        <div class="problem-body">
            <div class="problem-text">
                <h2 class="section-title">Las empresas nuevas son las mejores oportunidades comerciales</h2>
                <p>
                    Las empresas recién creadas suelen contratar proveedores durante sus primeros meses de actividad.
                    Necesitan asesoría, software, servicios de marketing, seguros y muchos otros productos B2B.
                </p>
                <p>
                    Con Radar puedes detectar nuevas empresas cada día y contactar con ellas antes que otros proveedores del mercado.
                </p>
                <a href="<?= site_url('precios-radar') ?>" class="cta-primary" style="font-size: 1rem; padding: 15px 32px;">
                    Activar Radar →
                </a>
            </div>
            <div class="problem-visual">
                <div class="timeline-item">
                    <div class="timeline-dot">1</div>
                    <div>
                        <div class="timeline-label">Nueva empresa se constituye</div>
                        <div class="timeline-desc">La empresa aparece en el BORME y Radar la detecta automáticamente.</div>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot">2</div>
                    <div>
                        <div class="timeline-label">Tú la ves el mismo día</div>
                        <div class="timeline-desc">Antes que tu competencia, antes de que la empresa tenga proveedor.</div>
                    </div>
                </div>
                <div class="timeline-item">
                    <div class="timeline-dot">3</div>
                    <div>
                        <div class="timeline-label">Contactas y cierras la venta</div>
                        <div class="timeline-desc">Con datos completos: sector, provincia, fecha y objeto social.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     3. EJEMPLO REAL DEL RADAR
══════════════════════════════════════════════════════════ -->
<section class="section section-alt">
    <div class="container">
        <h2 class="section-title" style="text-align: center;">Ejemplo real del Radar</h2>
        <p class="section-sub" style="text-align: center; margin: 0 auto 40px;">
            Estas son empresas detectadas recientemente. Tú verías esto en tiempo real.
        </p>

        <table class="leads-table">
            <thead>
                <tr>
                    <th>Empresa</th>
                    <th>Sector</th>
                    <th>Provincia</th>
                    <th>Fecha constitución</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td data-label="Empresa"><span class="table-company">ACME TECH SL</span></td>
                    <td data-label="Sector"><span class="table-sector">Programación informática</span></td>
                    <td data-label="Provincia"><span class="table-province">Barcelona</span></td>
                    <td data-label="Fecha"><span class="table-date">05/03/2026</span></td>
                </tr>
                <tr>
                    <td data-label="Empresa"><span class="table-company">DATA CONSULTING SL</span></td>
                    <td data-label="Sector"><span class="table-sector">Consultoría</span></td>
                    <td data-label="Provincia"><span class="table-province">Madrid</span></td>
                    <td data-label="Fecha"><span class="table-date">04/03/2026</span></td>
                </tr>
                <tr>
                    <td data-label="Empresa"><span class="table-company">FOOD GROUP SL</span></td>
                    <td data-label="Sector"><span class="table-sector">Hostelería</span></td>
                    <td data-label="Provincia"><span class="table-province">Valencia</span></td>
                    <td data-label="Fecha"><span class="table-date">04/03/2026</span></td>
                </tr>
                <tr>
                    <td data-label="Empresa"><span class="table-company">DIGITAL GROWTH SL</span></td>
                    <td data-label="Sector"><span class="table-sector">Marketing digital</span></td>
                    <td data-label="Provincia"><span class="table-province">Málaga</span></td>
                    <td data-label="Fecha"><span class="table-date">03/03/2026</span></td>
                </tr>
                <tr>
                    <td data-label="Empresa"><span class="table-company">CONSTRUCCIONES NOVA SL</span></td>
                    <td data-label="Sector"><span class="table-sector">Construcción</span></td>
                    <td data-label="Provincia"><span class="table-province">Sevilla</span></td>
                    <td data-label="Fecha"><span class="table-date">03/03/2026</span></td>
                </tr>
            </tbody>
        </table>

        <div class="table-cta-row">
            <a href="<?= site_url('precios-radar') ?>" class="cta-ghost">
                Ver todos los leads en el Radar →
            </a>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     4. CÓMO FUNCIONA
══════════════════════════════════════════════════════════ -->
<section class="section">
    <div class="container">
        <h2 class="section-title" style="text-align: center;">Cómo funciona Radar</h2>
        <p class="section-sub" style="text-align: center; margin: 0 auto 52px;">
            Tres pasos para empezar a detectar nuevas oportunidades comerciales hoy mismo.
        </p>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h3 class="step-title">Detección automática</h3>
                <p class="step-desc">
                    Radar detecta nuevas empresas automáticamente cada día desde el BORME y los registros mercantiles de España.
                </p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h3 class="step-title">Filtra tu nicho</h3>
                <p class="step-desc">
                    Filtra por sector CNAE, provincia o fecha de constitución para encontrar exactamente el tipo de empresa que buscas.
                </p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h3 class="step-title">Exporta y contacta</h3>
                <p class="step-desc">
                    Exporta los leads a Excel o CSV y empieza a contactar con las nuevas empresas antes que tu competencia.
                </p>
            </div>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     5. QUÉ INCLUYE
══════════════════════════════════════════════════════════ -->
<section class="section section-alt">
    <div class="container">
        <h2 class="section-title" style="text-align: center;">Qué incluye el Radar</h2>
        <p class="section-sub" style="text-align: center; margin: 0 auto 44px;">Todo lo que necesitas para detectar y contactar con nuevas empresas.</p>

        <div class="included-grid">
            <div class="included-item">
                <div class="included-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                Empresas nuevas detectadas diariamente
            </div>
            <div class="included-item">
                <div class="included-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                </div>
                Sector CNAE de cada empresa
            </div>
            <div class="included-item">
                <div class="included-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                </div>
                Provincia y municipio
            </div>
            <div class="included-item">
                <div class="included-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
                </div>
                Fecha de constitución
            </div>
            <div class="included-item">
                <div class="included-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"></polygon></svg>
                </div>
                Filtros por actividad y provincia
            </div>
            <div class="included-item">
                <div class="included-icon">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                </div>
                Exportación a Excel / CSV
            </div>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     6. MÉTRICAS
══════════════════════════════════════════════════════════ -->
<section class="section section-dark">
    <div class="container">
        <h2 class="section-title" style="text-align: center; color: white; margin-bottom: 52px;">
            La base de datos más completa de nuevas empresas en España
        </h2>
        <div class="metrics-grid">
            <div class="metric-cell">
                <div class="metric-value">+4,5M</div>
                <div class="metric-label">empresas analizadas</div>
            </div>
            <div class="metric-cell">
                <div class="metric-value">+200</div>
                <div class="metric-label">nuevas empresas detectadas cada día</div>
            </div>
            <div class="metric-cell">
                <div class="metric-value">100%</div>
                <div class="metric-label">datos oficiales del BORME</div>
            </div>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     7. PRECIO
══════════════════════════════════════════════════════════ -->
<section class="section">
    <div class="container">
        <h2 class="section-title" style="text-align: center;">Plan Radar</h2>
        <p class="section-sub" style="text-align: center; margin: 0 auto 48px;">
            Acceso completo al radar de empresas nuevas en España. Sin permanencia.
        </p>

        <div class="pricing-card">
            <div class="pricing-header">
                <div class="pricing-name">Plan Radar</div>
                <div class="pricing-price">79€ <span>/mes</span></div>
                <p class="pricing-desc">Acceso completo al radar de empresas nuevas en España.</p>
            </div>
            <div class="pricing-body">
                <ul class="pricing-features">
                    <li class="pricing-feature">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Acceso completo al Radar
                    </li>
                    <li class="pricing-feature">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Filtros por sector y provincia
                    </li>
                    <li class="pricing-feature">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Exportación de leads a Excel / CSV
                    </li>
                    <li class="pricing-feature">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Datos actualizados diariamente
                    </li>
                    <li class="pricing-feature">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"></polyline></svg>
                        Sin permanencia — cancela cuando quieras
                    </li>
                </ul>
                <a href="<?= site_url('precios-radar') ?>" class="cta-primary" style="display: flex; width: 100%; font-size: 1.05rem; justify-content: center;">
                    Activar Radar
                </a>
                <p style="text-align: center; margin: 14px 0 0; font-size: 0.88rem; color: var(--slate-500);">
                    Cancelar en cualquier momento
                </p>
            </div>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     8. COMPARACIÓN
══════════════════════════════════════════════════════════ -->
<section class="section section-alt">
    <div class="container">
        <h2 class="section-title" style="text-align: center;">Radar vs Excel puntual</h2>
        <p class="section-sub" style="text-align: center; margin: 0 auto 44px;">
            ¿Necesitas leads de forma continua o solo puntualmente?
        </p>

        <table class="comparison-table">
            <thead>
                <tr>
                    <th></th>
                    <th class="col-radar">Radar (Mensual)</th>
                    <th class="col-excel">Excel puntual</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Actualización</td>
                    <td class="col-radar">Diaria automática</td>
                    <td>Descarga única</td>
                </tr>
                <tr>
                    <td>Filtros</td>
                    <td class="col-radar">Avanzados (sector, provincia, fecha)</td>
                    <td>Sin filtros</td>
                </tr>
                <tr>
                    <td>Leads nuevos</td>
                    <td class="col-radar"><span class="comparison-check">✓</span> Cada día</td>
                    <td>Datos estáticos</td>
                </tr>
                <tr>
                    <td>Exportación</td>
                    <td class="col-radar">Ilimitada</td>
                    <td>Solo un listado</td>
                </tr>
                <tr>
                    <td>Precio</td>
                    <td class="col-radar"><strong>79€/mes</strong></td>
                    <td>9€/vez</td>
                </tr>
            </tbody>
        </table>

        <div class="table-cta-row" style="margin-top: 36px;">
            <a href="<?= site_url('precios-radar') ?>" class="cta-primary">
                Activar Radar →
            </a>
            <p style="margin-top: 12px; color: var(--slate-500); font-size: 0.88rem;">
                O <a href="<?= site_url('billing/single_checkout?period=30days') ?>" style="color: var(--primary); font-weight: 700;">descarga un listado puntual por 9€</a>
            </p>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     9. FAQ
══════════════════════════════════════════════════════════ -->
<section class="section">
    <div class="container">
        <h2 class="section-title" style="text-align: center; margin-bottom: 48px;">Preguntas frecuentes</h2>

        <div class="faq-list">
            <div class="faq-item">
                <h3 class="faq-question">¿De dónde salen los datos?</h3>
                <p class="faq-answer">Los datos se obtienen diariamente del BORME (Boletín Oficial del Registro Mercantil) y de los registros mercantiles provinciales de España.</p>
            </div>
            <div class="faq-item">
                <h3 class="faq-question">¿Cada cuánto se actualiza el radar?</h3>
                <p class="faq-answer">El radar se actualiza diariamente con todas las nuevas empresas detectadas en los registros mercantiles.</p>
            </div>
            <div class="faq-item">
                <h3 class="faq-question">¿Puedo cancelar la suscripción?</h3>
                <p class="faq-answer">Sí, puedes cancelar en cualquier momento desde tu panel de cliente. Sin permanencia ni penalizaciones.</p>
            </div>
            <div class="faq-item">
                <h3 class="faq-question">¿Puedo exportar los leads?</h3>
                <p class="faq-answer">Sí. Puedes exportar todos los leads filtrados en formato Excel o CSV directamente desde el radar.</p>
            </div>
            <div class="faq-item">
                <h3 class="faq-question">¿Hay contrato de permanencia?</h3>
                <p class="faq-answer">No. Es una suscripción mensual sin compromiso. Cancela cuando quieras desde tu cuenta.</p>
            </div>
        </div>
    </div>
</section>


<!-- ══════════════════════════════════════════════════════════
     10. CTA FINAL
══════════════════════════════════════════════════════════ -->
<section class="final-cta">
    <div class="container">
        <h2 class="section-title" style="color: white; margin-bottom: 20px;">
            Empieza a detectar nuevas empresas hoy
        </h2>
        <p class="final-cta-sub">
            Accede al radar y descubre nuevas oportunidades comerciales cada día antes que tu competencia.
        </p>
        <a href="<?= site_url('precios-radar') ?>" class="cta-white">
            <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            Activar Radar
        </a>
        <p style="margin-top: 18px; color: rgba(255,255,255,0.5); font-size: 0.88rem;">
            Cancelar en cualquier momento · Sin permanencia
        </p>
    </div>
</section>

</main>

<?= view('partials/footer') ?>
</body>
</html>
