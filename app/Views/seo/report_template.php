<?php
/**
 * REPORT TEMPLATE - BRAND ALIGNED EDITION 🏢
 * Diseño profesional, limpio y totalmente integrado con la identidad de APIEmpresas.
 */
?>
<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => $title,
        'excerptText' => $meta_description,
        'canonical'   => $canonical,
        'robots'      => $robots ?? 'index,follow',
    ]) ?>

    <style>
        :root {
            --ae-primary: #133A82;
            --ae-secondary: #12b48a;
            --ae-bg: #fbfdff;
            --ae-text: #0e1320;
            --ae-muted: #5b647a;
            --ae-border: #e8ecf5;
            --ae-radius: 14px;
            --ae-shadow: 0 6px 22px rgba(18, 26, 60, .08);
            --ae-accent: #ff6b00;
        }

        body {
            background-color: var(--ae-bg);
            color: var(--ae-text);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.6;
        }

        /* --- Refined Hero --- */
        .report-hero {
            background: var(--ae-primary);
            padding: 40px 0 60px;
            color: #fff;
            text-align: center;
            position: relative;
        }

        .report-hero__badge {
            display: inline-block;
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            padding: 4px 12px;
            border-radius: 8px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 12px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .report-hero__title {
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 900;
            color: #fff;
            margin: 0;
            line-height: 1.2;
            letter-spacing: -0.02em;
            max-width: 1000px;
            margin-inline: auto;
        }

        /* --- Main Layout --- */
        .report-grid {
            margin-top: 40px; /* Separated from hero */
            padding-bottom: 80px;
            display: grid;
            grid-template-columns: 1fr 320px; /* Slightly wider sidebar */
            gap: 40px;
            align-items: start;
        }

        /* --- Main Content --- */
        .report-content {
            background: #fff;
            border-radius: var(--ae-radius);
            padding: 40px;
            box-shadow: var(--ae-shadow);
            border: 1px solid var(--ae-border);
        }

        .report-body {
            font-size: 1.05rem;
            color: #2d3748;
        }

        .report-body h2 {
            font-size: 1.6rem;
            font-weight: 800;
            color: var(--ae-primary);
            margin: 35px 0 20px;
            letter-spacing: -0.01em;
        }

        .report-body p { margin-bottom: 20px; }

        /* --- Metrics Overlay --- */
        .report-metrics {
            margin-top: 50px;
            padding: 30px;
            background: #f8fafc;
            border-radius: 12px;
            border: 1px solid var(--ae-border);
        }

        .report-metrics__title {
            text-align: center;
            font-size: 1.2rem;
            font-weight: 800;
            margin-bottom: 25px;
            color: var(--ae-primary);
        }

        .metrics-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(130px, 1fr));
            gap: 15px;
        }

        .metric-item {
            background: #fff;
            padding: 20px;
            border-radius: 10px;
            text-align: center;
            border: 1px solid var(--ae-border);
        }

        .metric-item__label {
            font-size: 0.7rem;
            font-weight: 700;
            color: var(--ae-muted);
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .metric-item__value {
            font-size: 1.6rem;
            font-weight: 900;
            color: var(--ae-text);
        }

        .metric-item__value.color { color: var(--ae-secondary); }

        /* --- Sidebar Widgets Redesign --- */
        .sidebar-widget {
            background: #fff;
            border-radius: 24px;
            padding: 0;
            overflow: hidden;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid #f1f5f9;
            margin-bottom: 32px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .sidebar-widget:hover {
            transform: translateY(-4px);
            box-shadow: 0 25px 30px -10px rgba(0, 0, 0, 0.08);
            border-color: var(--ae-secondary);
        }

        .widget-title {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            padding: 24px 28px;
            font-size: 0.85rem;
            font-weight: 950;
            color: var(--ae-primary);
            border-bottom: 1px solid #f1f5f9;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .widget-title svg {
            color: var(--ae-secondary);
            opacity: 0.8;
        }

        .widget-list {
            list-style: none;
            padding: 12px 0;
            margin: 0;
        }

        .widget-item {
            padding: 0;
            border: none;
        }

        .widget-link {
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #64748b;
            font-weight: 700;
            font-size: 0.92rem;
            text-decoration: none;
            transition: all 0.2s ease;
        }

        .widget-link:hover {
            background: #f0f9ff;
            color: var(--ae-primary);
            padding-left: 34px;
        }

        .widget-chevron {
            opacity: 0;
            transition: all 0.3s ease;
            color: var(--ae-secondary);
            transform: translateX(-10px);
        }

        .widget-link:hover .widget-chevron {
            opacity: 1;
            transform: translateX(0);
        }

        .widget-link::before { display: none !important; }

        /* --- CTA Block --- */
        .report-cta {
            margin-top: 60px;
            background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%);
            border-radius: 32px;
            padding: 60px 40px;
            text-align: center;
            color: #fff;
            position: relative;
            overflow: hidden;
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.2);
        }

        .report-cta::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -10%;
            width: 300px;
            height: 300px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }

        .report-cta::after {
            content: "";
            position: absolute;
            bottom: -30%;
            right: -5%;
            width: 200px;
            height: 200px;
            background: radial-gradient(circle, rgba(18, 180, 138, 0.15) 0%, transparent 70%);
            border-radius: 50%;
        }

        .report-cta h2 {
            font-size: 1.8rem;
            font-weight: 800;
            margin-bottom: 15px;
        }

        .report-cta p {
            font-size: 1rem;
            opacity: 0.8;
            margin-bottom: 30px;
            max-width: 500px;
            margin-inline: auto;
        }

        .btn-ae {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            background: var(--ae-secondary);
            color: #fff;
            padding: 14px 30px;
            border-radius: 10px;
            font-weight: 700;
            text-decoration: none;
            transition: transform 0.2s;
        }

        .btn-ae:hover { transform: translateY(-2px); box-shadow: 0 10px 20px rgba(18, 180, 138, 0.3); }

        /* --- Premium Preview Section --- */
        .premium-preview {
            background: #ffffff;
            border-radius: 24px;
            padding: 40px;
            border: 1px solid var(--ae-border);
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }

        .premium-preview::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 4px;
            background: linear-gradient(90deg, var(--ae-primary), var(--ae-secondary));
        }

        .preview-header {
            margin-bottom: 30px;
        }

        .preview-header__kicker {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--ae-secondary);
            font-weight: 800;
            font-size: 0.85rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            margin-bottom: 12px;
        }

        .preview-header__title {
            font-size: 1.8rem;
            font-weight: 900;
            color: var(--ae-primary);
            margin: 0 0 10px;
            letter-spacing: -0.02em;
        }

        .preview-header__text {
            color: var(--ae-muted);
            font-size: 1rem;
            font-weight: 500;
        }

        .premium-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }

        @media (max-width: 640px) {
            .premium-grid { grid-template-columns: 1fr; }
        }

        .premium-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            border-radius: 20px;
            padding: 24px;
            position: relative;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
            display: flex;
            flex-direction: column;
            gap: 16px;
        }

        .premium-card:hover {
            transform: translateY(-4px);
            border-color: var(--ae-secondary);
            box-shadow: 0 20px 25px -5px rgba(18, 180, 138, 0.1);
        }

        .premium-card__head {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .premium-card__name {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--ae-primary);
            line-height: 1.3;
            max-width: 80%;
        }

        .premium-card__badge {
            background: #f0fdf4;
            color: #166534;
            font-size: 0.65rem;
            font-weight: 800;
            padding: 4px 10px;
            border-radius: 99px;
            text-transform: uppercase;
        }

        .premium-card__body {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .premium-card__info {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: var(--ae-muted);
            font-weight: 500;
        }

        .premium-card__info svg {
            color: #cbd5e1;
            flex-shrink: 0;
        }

        .premium-card__footer {
            margin-top: auto;
            padding-top: 16px;
            border-top: 1px solid #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .premium-card__timing {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--ae-text);
        }

        .premium-card__timing span {
            color: #94a3b8;
            font-family: monospace;
            letter-spacing: 2px;
        }

        .premium-card__lock {
            width: 24px;
            height: 24px;
            background: #f8fafc;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
        }

        .preview-footer {
            background: #f8fafc;
            border-radius: 16px;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--ae-primary);
            border: 1px dashed #e2e8f0;
        }

        .preview-footer svg {
            color: var(--ae-secondary);
        }

        .urgency-banner {
            background: #fff1f2;
            border: 1px solid #fecdd3;
            color: #9f1239;
            padding: 20px 24px;
            border-radius: 16px;
            font-weight: 800;
            display: flex;
            align-items: center;
            gap: 16px;
            margin: 40px 0;
            box-shadow: 0 4px 12px rgba(159, 18, 57, 0.05);
        }

        .urgency-banner svg {
            color: #e11d48;
            flex-shrink: 0;
            animation: pulse-red 2s infinite;
        }

        @keyframes pulse-red {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.1); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }

        .secondary-cta-block {
            text-align: center;
            margin: 60px 0;
            padding: 40px;
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-radius: 24px;
            border: 1px solid #e2e8f0;
        }

        .secondary-cta-block__text {
            font-weight: 800;
            color: var(--ae-primary);
            margin-bottom: 25px;
            font-size: 1.25rem;
            letter-spacing: -0.01em;
            line-height: 1.4;
        }

        /* --- WOW Redesign --- */
        .wow-section {
            margin: 60px 0;
        }

        .wow-title {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--ae-primary);
            margin-bottom: 30px;
            text-align: center;
        }

        .wow-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 24px;
        }

        @media (max-width: 768px) {
            .wow-grid { grid-template-columns: 1fr; }
        }

        .wow-card {
            background: #ffffff;
            border: 1px solid #f1f5f9;
            padding: 30px 24px;
            border-radius: 20px;
            text-align: center;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.02);
        }

        .wow-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.05);
            border-color: var(--ae-secondary);
        }

        .wow-card__icon {
            width: 50px;
            height: 50px;
            background: #f0fdf4;
            color: var(--ae-secondary);
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .wow-card__title {
            font-weight: 800;
            color: var(--ae-primary);
            font-size: 1.1rem;
            margin-bottom: 12px;
        }

        .wow-card__text {
            font-size: 0.9rem;
            color: var(--ae-muted);
            font-weight: 500;
            line-height: 1.5;
        }

        .wow-pill-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            justify-content: center;
        }

        .wow-pill {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 10px 20px;
            border-radius: 99px;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--ae-primary);
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.2s ease;
        }

        .wow-pill:hover {
            background: #ffffff;
            border-color: var(--ae-secondary);
            color: var(--ae-secondary);
            transform: scale(1.05);
        }

        .wow-pill svg {
            color: var(--ae-secondary);
        }

        /* --- Card Opportunity Interaction --- */
        .premium-card { cursor: pointer; }
        
        .btn-card-opportunity {
            background: var(--ae-secondary);
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-card-opportunity:hover {
            transform: scale(1.05);
            background: #0e9a75;
        }

        /* --- Modal System --- */
        .ae-modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(8px);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            padding: 20px;
        }

        .ae-modal {
            background: #fff;
            width: 100%;
            max-width: 500px;
            border-radius: 24px;
            padding: 40px;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            animation: modalFadeIn 0.3s ease-out;
        }

        @keyframes modalFadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .ae-modal__close {
            position: absolute;
            top: 20px;
            right: 20px;
            background: #f1f5f9;
            border: none;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            color: #64748b;
            transition: all 0.2s;
        }

        .ae-modal__close:hover { background: #e2e8f0; color: #1e293b; }

        .ae-modal__title {
            font-size: 1.5rem;
            font-weight: 900;
            color: var(--ae-primary);
            line-height: 1.2;
            margin-bottom: 15px;
            letter-spacing: -0.02em;
        }

        .ae-modal__text {
            font-size: 1rem;
            color: var(--ae-muted);
            line-height: 1.5;
            margin-bottom: 25px;
        }

        .ae-modal__bullets {
            list-style: none;
            padding: 0;
            margin: 0 0 30px;
        }

        .ae-modal__bullet {
            display: flex;
            align-items: center;
            gap: 12px;
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--ae-primary);
            margin-bottom: 12px;
        }

        .ae-modal__bullet svg {
            color: var(--ae-secondary);
        }

        .radar-pulse {
            width: 8px;
            height: 8px;
            background: var(--ae-secondary);
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 0 rgba(18, 180, 138, 0.7);
            animation: radar-pulse 1.5s infinite;
        }

        @keyframes radar-pulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(18, 180, 138, 0.7); }
            70% { transform: scale(1); box-shadow: 0 0 0 10px rgba(18, 180, 138, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(18, 180, 138, 0); }
        }

        @media (max-width: 1000px) {
            .report-grid { grid-template-columns: 1fr; }
            .report-hero { padding: 30px 0 50px; }
            .premium-preview { padding: 25px; }
        }
    </style>
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div>
    <?= view('partials/header', [], ['debug' => false]) ?>

    <!-- HERO HEADER -->
    <?php 
        $sectorName = $radar_data['sector_label'] ?? 'General';
        $cityName = $radar_data['province'] ?? 'España';
        $totalCompanies = $radar_data['total_context_count'] ?? 0;
        $displayTotal = ($totalCompanies > 0) ? "+$totalCompanies" : "Decenas de";
    ?>
    <header class="report-hero">
        <div class="container">
            <span class="report-hero__badge">Oportunidad Comercial</span>
            <h1 class="report-hero__title"><?= $displayTotal ?> empresas nuevas de <?= esc($sectorName) ?> en <?= esc($cityName) ?> listas para ser detectadas antes que tu competencia</h1>
            <p style="font-size: 1.1rem; opacity: 0.9; margin: 20px auto 30px; max-width: 600px; font-weight: 500;">
                Accede al listado actualizado con empresas recién creadas con potencial comercial.
            </p>
            <div style="background: rgba(255,255,255,0.2); display: block; padding: 20px; border-radius: 16px; margin: 0 auto 30px; max-width: 700px; border: 2px solid rgba(255,255,255,0.3);">
                <div style="font-size: 1.25rem; font-weight: 900; color: #ffe162; line-height: 1.3;">
                    “💰 Estas empresas pueden generarte entre 2.000€ y 12.000€ en nuevos clientes”
                </div>
            </div>
            <div style="margin-top: 10px;">
                <a href="<?= site_url('radar/preview') ?>" class="btn-ae" style="background: #fff; color: var(--ae-primary); box-shadow: 0 10px 30px rgba(0,0,0,0.2);">
                    Acceder ahora y detectar antes que otros →
                </a>
            </div>
        </div>
    </header>

    <main class="container">
        <div class="report-grid">
            
            <!-- MAIN CONTENT -->
            <article class="report-content">
                
                <!-- PREMIUM PREVIEW BLOCK -->
                <?php if (!empty($radar_data['companies'])): ?>
                    <section class="premium-preview">
                        <div class="preview-header">
                            <div class="preview-header__kicker">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                Inteligencia de Mercado en Tiempo Real
                            </div>
                            <h2 class="preview-header__title">Empresas detectadas en <?= esc($cityName) ?></h2>
                            <p class="preview-header__text">Empresas recién constituidas con alta probabilidad de requerir nuevos servicios.</p>
                        </div>

                        <div class="premium-grid">
                            <?php foreach (array_slice($radar_data['companies'], 0, 4) as $comp): ?>
                                <div class="premium-card" onclick="openOpportunityModal(event, '<?= esc($comp['name'] ?? '') ?>')">
                                    <div class="premium-card__head">
                                        <div class="premium-card__name"><?= esc($comp['name'] ?? 'Empresa Registrada') ?></div>
                                        <span class="premium-card__badge">Nueva</span>
                                    </div>
                                    
                                    <div class="premium-card__body">
                                        <div class="premium-card__info">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="18" height="18" rx="2" ry="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
                                            <?= esc(mb_strimwidth($comp['cnae_label'] ?? 'Varios sectores', 0, 45, "...")) ?>
                                        </div>
                                        <div class="premium-card__info">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                                            <?= esc($comp['registro_mercantil'] ?? $cityName) ?>
                                        </div>
                                    </div>

                                    <div class="premium-card__footer">
                                        <div class="premium-card__timing" style="color: var(--ae-secondary);">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                                            <span>Oportunidad Temprana</span>
                                        </div>
                                        <button type="button" class="btn-card-opportunity">
                                            Ver oportunidad
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="preview-footer">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>
                            Solo estás viendo una vista previa. El listado completo incluye información clave y oportunidades reales.
                        </div>
                    </section>
                <?php endif; ?>

                <!-- URGENCY BANNER -->
                <div class="urgency-banner">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    <span>Atención: Varias de estas empresas están siendo detectadas por otros competidores ahora mismo.</span>
                </div>

                <!-- SECONDARY CTA -->
                <div class="secondary-cta-block">
                    <p class="secondary-cta-block__text">¿Quieres desbloquear el acceso completo con información estratégica y ganar la carrera hoy mismo?</p>
                    <a href="<?= site_url('radar/preview') ?>" class="btn-ae" style="padding: 18px 40px; font-size: 1.1rem; box-shadow: 0 10px 25px rgba(18, 180, 138, 0.3);">
                        Ver oportunidades y empezar ahora →
                    </a>
                </div>

                <!-- WOW VALUE BLOCK -->
                <section class="wow-section">
                    <h3 class="wow-title">Estas empresas son oportunidades estratégicas porque:</h3>
                    <div class="wow-grid">
                        <div class="wow-card">
                            <div class="wow-card__icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                            </div>
                            <div class="wow-card__title">Timing Perfecto</div>
                            <div class="wow-card__text">Acaban de empezar y necesitan configurar todos sus proveedores YA.</div>
                        </div>
                        <div class="wow-card">
                            <div class="wow-card__icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </div>
                            <div class="wow-card__title">Sin Partners</div>
                            <div class="wow-card__text">No tienen compromisos previos; puedes ser su primera opción.</div>
                        </div>
                        <div class="wow-card">
                            <div class="wow-card__icon">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/><polyline points="17 6 23 6 23 12"/></svg>
                            </div>
                            <div class="wow-card__title">Alto Crecimiento</div>
                            <div class="wow-card__text">Están en fase de expansión con alto potencial de venta a largo plazo.</div>
                        </div>
                    </div>
                    <p style="margin-top: 30px; font-weight: 900; color: var(--ae-primary); font-size: 1.2rem; text-align: center; letter-spacing: -0.01em;">Llegar primero es la clave para cerrar estas oportunidades</p>
                </section>

                <!-- WOW USAGE BLOCK -->
                <section class="wow-section" style="background: #f8fafc; padding: 40px; border-radius: 24px; border: 1px dashed #cbd5e1;">
                    <h3 class="wow-title">¿Quién está utilizando este Radar?</h3>
                    <div class="wow-pill-grid">
                        <div class="wow-pill">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                            Agencias de Marketing
                        </div>
                        <div class="wow-pill">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                            Asesorías y Gestorías
                        </div>
                        <div class="wow-pill">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/></svg>
                            Software B2B
                        </div>
                        <div class="wow-pill">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="16 11 18 13 22 9"/></svg>
                            Equipos Comerciales
                        </div>
                    </div>
                </section>

                <!-- SEO CONTENT (EXISTING) -->
                <div class="report-body" style="margin-top: 60px; padding-top: 60px; border-top: 1px solid var(--ae-border);">
                    <div style="background: #f8fafc; padding: 10px 20px; border-radius: 8px; font-size: 0.8rem; color: var(--ae-muted); margin-bottom: 30px; display: inline-block;">
                        Análisis detallado y contexto histórico
                    </div>
                    <?= $wp_content ?>

                    <!-- Dynamic Data Section -->
                    <?php if (($radar_data['total_context_count'] ?? 0) > 0): ?>
                        <div class="report-metrics" style="background: #ffffff; border: 1px solid var(--ae-border); box-shadow: 0 10px 30px rgba(0,0,0,0.03);">
                            <h3 class="report-metrics__title">Indicadores de Mercado Real (<?= esc($cityName) ?>)</h3>
                            <div class="metrics-row">
                                <div class="metric-item">
                                    <div class="metric-item__label">Alertas Hoy</div>
                                    <?php if (($radar_data['count_indicators']['today'] ?? 0) > 0): ?>
                                        <div class="metric-item__value color"><?= esc($radar_data['count_indicators']['today'] ?? 0) ?></div>
                                    <?php else: ?>
                                        <div class="metric-item__value" style="font-size: 0.8rem; color: var(--ae-secondary); font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 6px; min-height: 40px;">
                                            <span class="radar-pulse"></span> SCANNING
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-item__label">Últimos 7 días</div>
                                    <?php if (($radar_data['count_indicators']['last_7_days'] ?? 0) > 0): ?>
                                        <div class="metric-item__value"><?= esc($radar_data['count_indicators']['last_7_days'] ?? 0) ?></div>
                                    <?php else: ?>
                                        <div class="metric-item__value" style="font-size: 0.75rem; color: var(--ae-muted); font-weight: 800; min-height: 40px; display: flex; align-items: center; justify-content: center;">
                                            RADAR ACTIVO
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-item__label">Últimos 30 días</div>
                                    <div class="metric-item__value" style="font-weight: 900;"><?= number_format($radar_data['total_context_count'] ?? 0, 0, ',', '.') ?></div>
                                </div>
                                <div class="metric-item">
                                    <div class="metric-item__label">Estado Sector</div>
                                    <?php if (($radar_data['growth_pct'] ?? 0) > 0): ?>
                                        <div class="metric-item__value color">+<?= esc($radar_data['growth_pct'] ?? '0') ?>%</div>
                                    <?php else: ?>
                                        <div class="metric-item__value" style="font-size: 0.75rem; color: var(--ae-primary); font-weight: 800; min-height: 40px; display: flex; align-items: center; justify-content: center;">
                                            OPORTUNIDAD
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (($radar_data['total_context_count'] ?? 0) <= 0): ?>
                    <!-- Diseño mejorado para cuando no hay datos específicos -->
                    <div class="report-metrics" style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border: 1px solid #e2e8f0; padding: 50px 30px;">
                        <h3 class="report-metrics__title" style="margin-bottom: 40px;">Potencia tu Inteligencia de Mercado</h3>
                        
                        <div class="metrics-row" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                            <!-- Card 1 -->
                            <div style="background: #fff; padding: 24px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid rgba(33, 82, 255, 0.05); text-align: left;">
                                <div style="width: 40px; height: 40px; background: rgba(33, 82, 255, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: var(--ae-primary);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                </div>
                                <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 8px; color: var(--ae-primary);">Datos 100% Oficiales</h4>
                                <p style="font-size: 0.85rem; color: #64748b; margin: 0; line-height: 1.5;">Acceso directo fuentes registrables: BORME, AEAT e INE en tiempo real.</p>
                            </div>

                            <!-- Card 2 -->
                            <div style="background: #fff; padding: 24px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid rgba(33, 82, 255, 0.05); text-align: left;">
                                <div style="width: 40px; height: 40px; background: rgba(18, 180, 138, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: var(--ae-secondary);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                                </div>
                                <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 8px; color: var(--ae-primary);">Segmentación Precisa</h4>
                                <p style="font-size: 0.85rem; color: #64748b; margin: 0; line-height: 1.5;">Filtra por códigos CNAE exactos, capital social y rangos de facturación.</p>
                            </div>

                            <!-- Card 3 -->
                            <div style="background: #fff; padding: 24px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid rgba(33, 82, 255, 0.05); text-align: left;">
                                <div style="width: 40px; height: 40px; background: rgba(33, 82, 255, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: var(--ae-primary);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                                </div>
                                <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 8px; color: var(--ae-primary);">Leads de Calidad</h4>
                                <p style="font-size: 0.85rem; color: #64748b; margin: 0; line-height: 1.5;">Descarga listados con información clave, oportunidades estratégicas y nombres de administradores.</p>
                            </div>

                            <!-- Card 4 -->
                            <div style="background: #fff; padding: 24px; border-radius: 16px; box-shadow: 0 4px 12px rgba(0,0,0,0.03); border: 1px solid rgba(33, 82, 255, 0.05); text-align: left;">
                                <div style="width: 40px; height: 40px; background: rgba(18, 180, 138, 0.1); border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; color: var(--ae-secondary);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                </div>
                                <h4 style="font-size: 0.95rem; font-weight: 800; margin-bottom: 8px; color: var(--ae-primary);">Exportación CRM</h4>
                                <p style="font-size: 0.85rem; color: #64748b; margin: 0; line-height: 1.5;">Exporta informes ilimitados en Excel y CSV compatibles con tu flujo de ventas.</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- FINAL CTA -->
                <div class="report-cta">
                    <div style="position: relative; z-index: 2;">
                        <h2 style="color: #fff; font-size: 2.2rem; font-weight: 900; letter-spacing: -0.03em; line-height: 1.1; margin-bottom: 20px;">Si no accedes ahora, estas empresas desaparecerán en horas</h2>
                        <p style="font-weight: 600; opacity: 0.9; font-size: 1.15rem; color: #fff; margin-bottom: 40px; max-width: 600px; margin-inline: auto; line-height: 1.5;">Estás a un clic de detectar oportunidades críticas y ganar la carrera comercial hoy mismo.</p>
                        <a href="<?= site_url('radar/preview') ?>" class="btn-ae" style="background: #ffffff; color: #1e3a8a; padding: 18px 45px; font-size: 1.1rem; border-radius: 14px; box-shadow: 0 10px 25px rgba(0,0,0,0.15); border: none;">
                            Acceder al Radar B2B y ganar tiempo →
                        </a>
                    </div>
                </div>
            </article>

            <!-- SIDEBAR -->
            <aside class="sidebar">
                
                <div class="sidebar-widget">
                    <h3 class="widget-title">
                        Otras Localidades
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                    </h3>
                    <ul class="widget-list">
                        <?php 
                        // Buscar una plantilla limpia que tenga {{provincia}} (excluimos suffixes de SEO verbose)
                        $bestProvMatch = null;
                        $blacklist = ['listado', 'actualizado', 'hoy', 'semana', 'analisis'];
                        
                        foreach (($sidebar['templates'] ?? []) as $st) {
                            $stTitle = html_entity_decode($st['title']['rendered'], ENT_QUOTES, 'UTF-8');
                            $hasBlacklist = false;
                            foreach ($blacklist as $word) {
                                if (stripos($stTitle, $word) !== false) { $hasBlacklist = true; break; }
                            }
                            
                            if (!$hasBlacklist && strpos($stTitle, '{{provincia}}') !== false) {
                                if (!$bestProvMatch || strlen($stTitle) < strlen($bestProvMatch)) {
                                    $bestProvMatch = $stTitle;
                                }
                            }
                        }
                        $provBase = $bestProvMatch ?? $wp_raw_title;
 
                        $currentSectorFix = $radar_data['sector_label'] ?? 'General';
                        foreach (array_slice($sidebar['provinces'] ?? [], 0, 8) as $prov): 
                            $linkTitle = str_replace(['{{provincia}}', '{{sector}}'], [$prov, $currentSectorFix], $provBase);
                            $finalLinkSlug = $seoService->slugifyWithPlaceholders($linkTitle);
                        ?>
                            <li class="widget-item">
                                <a href="<?= site_url('informes/' . $finalLinkSlug) ?>" class="widget-link">
                                    <span>Nuevas en <?= esc($prov) ?></span>
                                    <svg class="widget-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="sidebar-widget">
                    <h3 class="widget-title">
                        Otros Sectores
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 2 7 12 12 22 7 12 2"/><polyline points="2 17 12 22 22 17"/><polyline points="2 12 12 17 22 12"/></svg>
                    </h3>
                    <ul class="widget-list">
                        <?php 
                        // Buscar una plantilla limpia que tenga {{sector}} (excluimos suffixes de SEO verbose)
                        $bestSectMatch = null;
                        foreach (($sidebar['templates'] ?? []) as $st) {
                            $stTitle = html_entity_decode($st['title']['rendered'], ENT_QUOTES, 'UTF-8');
                            $hasBlacklist = false;
                            foreach ($blacklist as $word) {
                                if (stripos($stTitle, $word) !== false) { $hasBlacklist = true; break; }
                            }
                            
                            if (!$hasBlacklist && strpos($stTitle, '{{sector}}') !== false) {
                                if (!$bestSectMatch || strlen($stTitle) < strlen($bestSectMatch)) {
                                    $bestSectMatch = $stTitle;
                                }
                            }
                        }
                        $sectBase = $bestSectMatch ?? $wp_raw_title;
 
                        $currentProvFix = $radar_data['province'] ?? 'España';
                        foreach (array_slice($sidebar['sectors'] ?? [], 0, 8) as $sect): 
                            $linkTitle = str_replace(['{{provincia}}', '{{sector}}'], [$currentProvFix, $sect], $sectBase);
                            $finalLinkSlug = $seoService->slugifyWithPlaceholders($linkTitle);
                        ?>
                        <li class="widget-item">
                            <a href="<?= site_url('informes/' . $finalLinkSlug) ?>" class="widget-link">
                                <span>Sector <?= esc($sect) ?></span>
                                <svg class="widget-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="sidebar-widget">
                    <h3 class="widget-title">
                        Informes Relacionados
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/></svg>
                    </h3>
                    <ul class="widget-list">
                        <?php 
                        $tCount = 0;
                        foreach (($sidebar['templates'] ?? []) as $t): 
                            $tTitle = html_entity_decode($t['title']['rendered'], ENT_QUOTES, 'UTF-8');
                            // Filtrar los relacionados para que tampoco tengan listado-actualizado
                            $hasBlacklist = false;
                            foreach ($blacklist as $word) {
                                if (stripos($tTitle, $word) !== false) { $hasBlacklist = true; break; }
                            }
                            if ($hasBlacklist) continue;
 
                            if ($tCount >= 6) break;
                            $tLinkTitle = str_replace(['{{provincia}}', '{{sector}}'], ['España', 'General'], $tTitle);
                            $tFinalSlug = $seoService->slugifyWithPlaceholders($tLinkTitle);
                        ?>
                        <li class="widget-item">
                            <a href="<?= site_url('informes/' . $tFinalSlug) ?>" class="widget-link">
                                <span><?= esc(mb_strimwidth(str_replace(['{{provincia}}', '{{sector}}'], ['España', 'General'], $tTitle), 0, 25, "...")) ?></span>
                                <svg class="widget-chevron" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                        </li>
                        <?php 
                            $tCount++;
                        endforeach; ?>
                    </ul>
                </div>

            </aside>
        </div>
    </main>

    <?= view('partials/footer') ?>
    <!-- OPPORTUNITY MODAL -->
    <div id="opportunityModal" class="ae-modal-overlay" onclick="if(event.target === this) closeOpportunityModal()">
        <div class="ae-modal">
            <button type="button" class="ae-modal__close" onclick="closeOpportunityModal()">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <h3 class="ae-modal__title">Desbloquea esta empresa y otras similares</h3>
            <p id="modalSubtext" class="ae-modal__text">Accede para ver más empresas como <span id="modalCompanyName" style="font-weight: 800; color: var(--ae-primary);"></span> y detectar oportunidades antes que tu competencia.</p>
            <ul class="ae-modal__bullets">
                <li class="ae-modal__bullet">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Empresas recién creadas cada día
                </li>
                <li class="ae-modal__bullet">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Oportunidades antes que otros
                </li>
                <li class="ae-modal__bullet">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                    Información estratégica para vender
                </li>
            </ul>
            <a id="modalCta" href="<?= site_url('radar/preview') ?>" class="btn-ae" style="width: 100%; justify-content: center; padding: 16px; font-size: 1.1rem; box-shadow: 0 10px 20px rgba(18, 180, 138, 0.2);">
                Acceder al Radar B2B
            </a>
            <div style="text-align: center; margin-top: 15px;">
                <button type="button" onclick="closeOpportunityModal()" style="background: none; border: none; color: var(--ae-muted); font-size: 0.85rem; font-weight: 600; cursor: pointer; text-decoration: underline;">
                    Seguir viendo vista previa
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentModalCompany = '';

        function openOpportunityModal(e, companyName) {
            if (e) e.stopPropagation();
            
            currentModalCompany = companyName;
            const modal = document.getElementById('opportunityModal');
            const subtext = document.getElementById('modalSubtext');

            if (companyName) {
                subtext.innerHTML = `Accede para ver más empresas como <span style="font-weight: 800; color: var(--ae-primary);">${companyName}</span> y detectar oportunidades antes que tu competencia.`;
            } else {
                subtext.innerText = "Accede al listado completo y detecta oportunidades antes que tu competencia.";
            }

            modal.style.display = 'flex';
            document.body.style.overflow = 'hidden';

            // Tracking
            if (window.trackEvent) {
                trackEvent('seo_company_card_opportunity_click', {
                    company_name: companyName || 'unknown',
                    sector: '<?= esc($sectorName) ?>',
                    provincia: '<?= esc($cityName) ?>',
                    timestamp: new Date().toISOString()
                });
            }
        }

        function closeOpportunityModal() {
            const modal = document.getElementById('opportunityModal');
            modal.style.display = 'none';
            document.body.style.overflow = '';
        }

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closeOpportunityModal();
        });

        document.getElementById('modalCta').addEventListener('click', function() {
            if (window.trackEvent) {
                trackEvent('seo_company_modal_radar_click', {
                    company_name: currentModalCompany || 'unknown',
                    sector: '<?= esc($sectorName) ?>',
                    provincia: '<?= esc($cityName) ?>',
                    timestamp: new Date().toISOString()
                });
            }
        });
    </script>
</body>
</html>
