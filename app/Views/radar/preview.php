<?php
/**
 * /radar/preview — Value-First Intermediate Page
 * Replicates dashboard look with restricted access to generate urgency.
 */
?>
<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Radar B2B — Empresas detectadas hoy listas para contactar',
        'excerptText' => 'Descubre empresas recién creadas que necesitan proveedores ahora mismo. Accede antes que tu competencia.',
        'canonical'   => site_url('radar/preview'),
        'robots'      => 'noindex,follow', // Pre-registro, no indexar por ahora o si? User said production-ready, maybe index. But usually preview is for ads.
    ]) ?>

    <!-- No external radar CSS to avoid conflicts with custom preview styles -->

    <style>
        html, body { height: 100%; margin: 0; padding: 0; }
        
        .ae-radar-page { 
            display: flex;
            min-height: 100vh;
            background: 
                radial-gradient(circle at top left, rgba(33, 82, 255, .08), transparent 28%),
                radial-gradient(circle at top right, rgba(18, 60, 133, .06), transparent 24%),
                linear-gradient(180deg, #f8fbff 0%, #f3f6fb 100%);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        /* Sidebar Styling (Matched to Radar Dashboard) */
        .ae-radar-page__sidebar {
            width: 280px;
            background: radial-gradient(circle at top left, rgba(33, 82, 255, .18), transparent 35%), linear-gradient(180deg, #0d1730 0%, #0a1224 100%);
            color: rgba(255, 255, 255, .78);
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 100;
            border-right: 1px solid rgba(255, 255, 255, .06);
            padding: 0;
        }

        .ae-radar-page__brand {
            padding: 28px 24px 22px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
            position: relative;
            z-index: 1;
        }

        .ae-radar-page__brand-header {
            display: flex;
            align-items: center;
            gap: 16px;
            text-decoration: none;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .ae-radar-page__brand-header .brand-text {
            display: flex;
            flex-direction: column;
        }

        .ae-radar-page__brand-header .brand-name {
            font-size: 20px;
            font-weight: 900;
            color: white;
            letter-spacing: -0.02em;
            line-height: 1;
        }

        .ae-radar-page__brand-header .brand-name .grad {
            background: linear-gradient(90deg, #60a5fa, #34d399);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .ae-radar-page__brand-header .brand-tag {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: rgba(255, 255, 255, 0.4);
            margin-top: 4px;
        }

        .ae-radar-page__sidebar-body {
            padding: 20px 0;
            flex: 1;
            overflow-y: auto;
        }

        .ae-radar-page__nav-group {
            margin-bottom: 24px;
        }

        .ae-radar-page__nav-label {
            display: block;
            padding: 0 24px;
            margin-bottom: 10px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: rgba(255, 255, 255, 0.45);
        }

        .ae-radar-page__nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            width: calc(100% - 24px);
            margin: 0 12px 4px;
            padding: 10px 12px;
            border-radius: 12px;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.65);
            font-size: 13px;
            font-weight: 700;
            transition: all 0.2s ease;
            cursor: default;
            opacity: 0.8;
        }

        .ae-radar-page__nav-link.is-active {
            background: #2563eb;
            color: white;
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3);
            opacity: 1;
        }

        .ae-radar-page__nav-icon {
            font-size: 16px;
        }

        .ae-radar-page__sidebar-footer {
            padding: 16px;
            border-top: 1px solid rgba(255, 255, 255, .08);
        }

        .ae-radar-page__roi-box {
            margin: 0 16px 20px;
            padding: 20px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 16px;
        }

        .ae-radar-page__roi-title {
            font-size: 12px;
            font-weight: 800;
            color: #60a5fa;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        .ae-radar-page__roi-text {
            font-size: 11px;
            color: rgba(255, 255, 255, 0.5);
            line-height: 1.5;
        }

        .ae-radar-page__main {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        .ae-radar-page__topbar {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(12px);
            padding: 16px 40px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 90;
        }

        .ae-radar-page__breadcrumb {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.6);
        }

        .ae-radar-page__breadcrumb strong {
            color: #ffffff;
        }

        .ae-radar-page__freshness {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.7);
        }

        .ae-radar-page__freshness-dot {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2);
        }

        .ae-radar-page__content {
            padding: 40px;
            flex: 1;
        }

        /* Hero Overlay for Preview */
        .preview-hero {
            background: #ffffff;
            border-radius: 24px;
            padding: 40px;
            margin-bottom: 32px;
            text-align: center;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
        }
        .preview-hero__badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: 999px;
            background: #fef2f2;
            border: 1px solid #fee2e2;
            color: #ef4444;
            font-size: 0.75rem;
            font-weight: 800;
            margin-bottom: 20px;
        }
        .preview-hero__dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #ef4444;
            animation: blink 1.5s infinite;
        }
        @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0.3; } }

        .preview-hero__title {
            font-size: clamp(2rem, 5vw, 3rem);
            font-weight: 950;
            color: #0f172a;
            margin-bottom: 16px;
            letter-spacing: -0.04em;
            line-height: 1.05;
        }
        .preview-hero__subtitle {
            font-size: 1.1rem;
            color: #64748b;
            max-width: 700px;
            margin: 0 auto 32px;
            font-weight: 500;
            line-height: 1.5;
        }

        /* Table Section */
        .radar-table-wrapper {
            background: #ffffff;
            border-radius: 20px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 4px 25px rgba(0,0,0,0.04);
            overflow: hidden;
            position: relative;
            margin-bottom: 60px;
        }
        .radar-table { width: 100%; border-collapse: collapse; table-layout: fixed; border-spacing: 0; }
        .radar-table thead { display: table-header-group !important; }
        .radar-table th { 
            background: #0f172a !important; 
            padding: 20px 24px; 
            text-align: left; 
            font-size: 0.8rem; 
            font-weight: 800; 
            text-transform: uppercase; 
            color: #ffffff !important; 
            letter-spacing: 0.05em;
            border: none;
            position: sticky;
            top: 0;
            z-index: 10;
        }
        .radar-table td { padding: 24px 24px; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; vertical-align: top; color: #334155; }
        
        /* Column widths */
        .col-company { width: 30%; }
        .col-activity { width: 15%; }
        .col-strategy { width: 20%; }
        .col-priority { width: 15%; }
        .col-action { width: 20%; }

        .table-overlay {
            position: absolute;
            top: 380px; /* Bajado para mostrar exactamente 3 filas */
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0.98) 5%, #ffffff 15%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding-bottom: 20px;
            z-index: 50;
            backdrop-filter: blur(4px);
        }

        .table-overlay__content {
            text-align: center;
            max-width: 650px;
            width: 100%;
            padding: 0 32px;
            margin-top: 10px;
        }

        .table-overlay__icon {
            width: 56px;
            height: 56px;
            background: #ffffff;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 24px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
        }

        .table-overlay__title {
            font-size: clamp(1.4rem, 2.5vw, 1.85rem);
            font-weight: 950;
            color: #0f172a;
            margin-bottom: 12px;
            letter-spacing: -0.03em;
            line-height: 1.25;
        }

        .table-overlay__sub {
            font-size: 1rem;
            color: #64748b;
            font-weight: 500;
            margin-bottom: 32px;
            line-height: 1.5;
            max-width: 500px;
            margin-left: auto;
            margin-right: auto;
        }

        .table-overlay__btn {
            background: #0f172a;
            color: white;
            border: none;
            padding: 16px 36px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 1rem;
            cursor: pointer;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.15);
            transition: transform 0.2s, background 0.2s;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }
        .table-overlay__btn:hover { transform: translateY(-2px); background: #1e293b; }

        /* Inline Capture Block (Below Table) */
        .inline-capture {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 48px;
            text-align: center;
            max-width: 800px;
            margin: 0 auto 60px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        }
        .inline-capture__title { font-size: 2rem; font-weight: 900; color: #0f172a; margin-bottom: 32px; letter-spacing: -0.02em; }
        
        .capture-form-wrap {
            max-width: 850px;
            margin: 0 auto;
        }

        /* Modal for interaction */
        .ae-modal {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.8);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
        }
        .ae-modal.is-active { opacity: 1; visibility: visible; }
        .ae-modal__content {
            background: #fff;
            border-radius: 24px;
            width: 100%;
            max-width: 480px;
            padding: 40px;
            position: relative;
            transform: translateY(20px);
            transition: all 0.3s ease;
        }
        .ae-modal.is-active .ae-modal__content { transform: translateY(0); }
        .ae-modal__close {
            position: absolute;
            top: 24px;
            right: 24px;
            background: #f1f5f9;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 8px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            z-index: 10;
        }
        .ae-modal__close:hover { background: #e2e8f0; color: #0f172a; transform: rotate(90deg); }
        .email-capture__title { font-size: 1.25rem; font-weight: 800; color: #0f172a; margin-bottom: 24px; }
        .email-capture__form { display: flex; flex-direction: column; gap: 12px; }
        .email-capture__input {
            width: 100%;
            height: 72px;
            padding: 0 32px;
            border-radius: 20px;
            border: 2px solid #cbd5e1;
            font-size: 1.4rem;
            font-weight: 600;
            transition: all 0.2s;
            outline: none;
            background: #f8fafc;
        }
        .email-capture__input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            background: #fff;
        }
        .email-capture__btn {
            background: #2563eb;
            color: #fff;
            height: 60px;
            border: none;
            border-radius: 16px;
            font-size: 1.1rem;
            font-weight: 800;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 10px 25px rgba(37, 99, 235, 0.25);
            padding: 0 32px;
        }
        .email-capture__btn:hover { background: #1d4ed8; transform: translateY(-1px); }
        .email-capture__btn:disabled { opacity: 0.7; cursor: not-allowed; }

        .email-capture__check { display: flex; align-items: center; justify-content: center; gap: 16px; margin-top: 20px; font-size: 0.8rem; color: #94a3b8; font-weight: 700; }
        .email-capture__check span { display: flex; align-items: center; gap: 4px; }
        .email-capture__check svg { color: #10b981; }

        /* Social Proof */
        .social-proof {
            margin-top: 48px;
            text-align: center;
            color: #64748b;
            font-size: 0.9rem;
            font-weight: 600;
            max-width: 600px;
            margin-left: auto;
            margin-right: auto;
        }

        /* Skeleton Loader */
        .skeleton {
            background: linear-gradient(90deg, #f1f5f9 25%, #e2e8f0 50%, #f1f5f9 75%);
            background-size: 200% 100%;
            animation: skeleton-loading 1.5s infinite;
            border-radius: 4px;
        }
        @keyframes skeleton-loading {
            0% { background-position: 200% 0; }
            100% { background-position: -200% 0; }
        }
        .skeleton-text { height: 16px; margin: 8px 0; }
        .skeleton-btn { height: 32px; width: 100px; }

        .radar-skeleton-rows { display: none; }

        .input-highlight-pulse {
            animation: highlight-pulse 2s infinite;
        }
        @keyframes highlight-pulse {
            0% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(37, 99, 235, 0); }
            100% { box-shadow: 0 0 0 0 rgba(37, 99, 235, 0); }
        }

        /* Responsive */
        /* Sticky Footer CTA */
        .sticky-radar-cta {
            position: fixed;
            bottom: -100px;
            left: 0;
            right: 0;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 16px 24px;
            box-shadow: 0 -10px 25px rgba(0,0,0,0.05);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }
        .sticky-radar-cta.is-active { bottom: 0; }
        .sticky-radar-cta__text { font-size: 1rem; font-weight: 800; color: #0f172a; }
        .sticky-radar-cta__btn { background: #2563eb; color: white; padding: 12px 24px; border-radius: 12px; font-weight: 800; text-decoration: none; font-size: 0.9rem; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); border: none; cursor: pointer; }

        @media (max-width: 640px) {
            .ae-radar-page__sidebar { display: none; }
            .preview-hero { padding: 40px 20px; }
            .radar-table th:nth-child(2), .radar-table td:nth-child(2) { display: none; }
            .radar-table th:nth-child(3), .radar-table td:nth-child(3) { display: none; }
            .sticky-radar-cta { flex-direction: column; gap: 8px; padding: 12px; }
            .sticky-radar-cta__text { font-size: 0.85rem; }
        }
    </style>
</head>
<body>

    <!-- MICRO-LOADING OVERLAY -->
    <div id="radar-loading-overlay" style="display: none; position: fixed; inset: 0; background: rgba(15, 23, 42, 0.98); z-index: 99999; flex-direction: column; align-items: center; justify-content: center; color: white; text-align: center;">
        <div class="radar-loading-spinner" style="width: 60px; height: 60px; border: 5px solid rgba(255,255,255,0.1); border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 24px;"></div>
        <h3 style="font-size: 1.75rem; font-weight: 900; margin-bottom: 8px; letter-spacing: -0.02em;">Preparando tus oportunidades...</h3>
        <p style="font-size: 1.1rem; color: #94a3b8; font-weight: 500;">Activando acceso en tiempo real al Radar B2B</p>
    </div>

    <style>
        @keyframes spin { to { transform: rotate(360deg); } }
        #radar-loading-overlay.is-active { display: flex !important; }
    </style>

    <div class="ae-radar-page">
        <aside class="ae-radar-page__sidebar">
            <div class="ae-radar-page__brand">
                <a href="<?= site_url() ?>" class="ae-radar-page__brand-header">
                    <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                        <defs>
                            <linearGradient id="ve-g-preview" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                                <stop stop-color="#2152FF"/>
                                <stop offset=".65" stop-color="#5C7CFF"/>
                                <stop offset="1" stop-color="#12B48A"/>
                            </linearGradient>
                        </defs>
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g-preview)"/>
                        <path d="M18 33 L28 43 L46 22" stroke="#FFFFFF" stroke-width="6" stroke-linecap="round" stroke-linejoin="round" fill="none"/>
                    </svg>
                    <div class="brand-text">
                        <span class="brand-name">API<span class="grad">Empresas</span></span>
                        <span class="brand-tag">Radar B2B</span>
                    </div>
                </a>
            </div>

            <div class="ae-radar-page__sidebar-body">
                <div class="ae-radar-page__nav-group">
                    <span class="ae-radar-page__nav-label">Radar</span>
                    <div class="ae-radar-page__nav-link is-active">
                        <span class="ae-radar-page__nav-icon">📊</span>
                        Dashboard principal
                    </div>
                    <div class="ae-radar-page__nav-link">
                        <span class="ae-radar-page__nav-icon">⭐</span>
                        Mis favoritos
                    </div>
                    <div class="ae-radar-page__nav-link">
                        <span class="ae-radar-page__nav-icon">📋</span>
                        Embudo (Kanban)
                    </div>
                </div>

                <div class="ae-radar-page__nav-group">
                    <span class="ae-radar-page__nav-label">Alertas</span>
                    <div class="ae-radar-page__nav-link">
                        <span class="ae-radar-page__nav-icon">🔔</span>
                        Configurar alertas
                    </div>
                </div>

                <div class="ae-radar-page__roi-box">
                    <div class="ae-radar-page__roi-title">Calculadora ROI</div>
                    <div class="ae-radar-page__roi-text">Solo 1 cierre paga 5 años de Radar PRO. Rentabilidad estimada del 450%.</div>
                </div>
            </div>

        </aside>

        <main class="ae-radar-page__main">
            <!-- TOPBAR SIMULADA -->
            <header class="ae-radar-page__topbar">
                <div class="ae-radar-page__breadcrumb">
                    <span>Radar B2B</span> / <strong>Preview</strong>
                </div>
                <div class="ae-radar-page__topbar-actions">
                    <div class="ae-radar-page__freshness">
                        <span class="ae-radar-page__freshness-dot"></span>
                        Actualizado hace 2 min · Hoy: <strong style="color: #ffffff;">+<?= $opps_count ?></strong> empresas
                    </div>
                </div>
            </header>

            <div class="ae-radar-page__content">
                
                <!-- HERO SECTION -->
                <section class="preview-hero" data-track-section="hero" style="background: #ffffff; border-radius: 32px; padding: 40px; margin-bottom: 32px; text-align: center; border: 1px solid #e2e8f0; box-shadow: 0 10px 40px rgba(0,0,0,0.03); position: relative; overflow: hidden;">
                    <!-- Subtle Light Glows -->
                    <div style="position: absolute; width: 300px; height: 300px; background: radial-gradient(circle, rgba(37, 99, 235, 0.03) 0%, transparent 70%); top: -100px; right: -100px; pointer-events: none;"></div>

                    <div class="preview-hero__badge" style="background: #fff1f2; color: #e11d48; border: 1px solid #ffe4e6; padding: 8px 16px; font-weight: 800; font-size: 0.8rem; display: inline-flex; align-items: center; gap: 8px;">
                        <span class="preview-hero__dot" style="background: #e11d48; width: 8px; height: 8px; border-radius: 50%;"></span>
                        +<?= $opps_count ?> empresas detectadas HOY — acceso inmediato
                    </div>
                    
                    <div style="font-size: 0.85rem; color: #64748b; font-weight: 600; margin: 12px 0 16px;">
                        Actualizado hace 2 min · <span style="color: #ef4444; font-weight: 800;">+12 nuevas empresas</span> en la última hora
                    </div>

                    <h1 class="preview-hero__title" style="font-size: clamp(1.8rem, 4.5vw, 2.5rem); line-height: 1.1; margin-bottom: 12px;">
                        <span style="background: linear-gradient(135deg, #1e293b 0%, #334155 100%); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">+<?= $opps_count ?> empresas están</span><br>
                        <span style="background: linear-gradient(90deg, #2563eb, #7c3aed); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">contratando proveedor</span> ahora mismo
                    </h1>
                    
                    <p class="preview-hero__subtitle" style="font-size: 1.1rem; color: #475569; max-width: 750px; margin: 0 auto 24px; line-height: 1.4; font-weight: 500;">
                        Si no contactas tú, otro proveedor lo hará en las próximas horas. <br>
                        <strong>Consigue clientes reales sin hacer prospección manual.</strong>
                    </p>

                    <div style="background: #f8fafc; border-radius: 20px; padding: 24px 32px; border: 1px solid #e2e8f0; max-width: 680px; margin: 0 auto; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                        <p style="font-size: 0.95rem; color: #1e293b; font-weight: 800; margin-bottom: 16px; letter-spacing: -0.01em;">Introduce tu email para desbloquear las <?= $opps_count ?> oportunidades</p>
                        <form class="preview-form-handler" style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <input type="email" name="email" class="email-capture__input" placeholder="Introduce tu email profesional" style="flex: 2; min-width: 240px; height: 60px; border-radius: 14px; border: 2px solid #e2e8f0; padding: 0 20px; font-size: 1.05rem; font-weight: 600;" required>
                            <button type="submit" class="email-capture__btn submit-btn-handler" style="flex: 1; min-width: 200px; height: 60px; border-radius: 14px; background: #2563eb; color: white; font-weight: 800; font-size: 1.05rem; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);">
                                Conseguir clientes ahora ⚡
                            </button>
                        </form>
                        
                        <div style="display: flex; align-items: center; justify-content: center; gap: 20px; margin-top: 20px; font-size: 0.8rem; color: #64748b; font-weight: 700;">
                            <span style="display: flex; align-items: center; gap: 6px;"><span style="color: #10b981; font-size: 1.1rem;">✓</span> Sin tarjeta</span>
                            <span style="display: flex; align-items: center; gap: 6px;"><span style="color: #10b981; font-size: 1.1rem;">✓</span> Acceso en <10 segundos</span>
                        </div>
                    </div>

                    <div style="margin-top: 20px; font-size: 0.75rem; color: #94a3b8; font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em;">
                        Usado por agencias, SaaS y consultores para generar clientes cada semana
                    </div>
                </section>

                <div class="ae-radar-page__container">
                    
                    <div style="font-size: 1rem; color: #16a34a; font-weight: 800; text-align: center; margin-bottom: 24px; background: #f0fdf4; padding: 12px; border-radius: 12px; border: 1px solid #dcfce7;">
                        💰 Oportunidades con tickets estimados entre 5.000€ y 12.000€
                    </div>

                    <!-- Contador de Oportunidades -->
                    <div style="margin-bottom: 24px; text-align: left; padding: 0 4px; display: flex; justify-content: space-between; align-items: flex-end;">
                        <div>
                            <div style="font-size: 1.25rem; font-weight: 900; color: #0f172a;">Estás viendo 3 de <?= $opps_count ?> oportunidades activas ahora mismo</div>
                            <div style="font-size: 0.95rem; color: #f97316; font-weight: 700; margin-top: 4px;">Algunas ya están siendo contactadas en este momento</div>
                        </div>
                        <div style="font-size: 0.75rem; color: #10b981; font-weight: 800; background: #f0fdf4; padding: 4px 10px; border-radius: 999px; border: 1px solid #dcfce7;">
                            💡 Ejemplo real detectado hace 12 minutos
                        </div>
                    </div>

                    <!-- ② PREVIEW DASHBOARD (TABLE) -->
                    <div class="radar-table-wrapper radar-table-loading" id="radar-table-container" data-track-section="preview_table">
                        <table class="radar-table">
                            <thead>
                                <tr>
                                    <th class="col-company">Empresa / Score</th>
                                    <th class="col-activity">Actividad</th>
                                    <th class="col-strategy">Valor Estimado</th>
                                    <th class="col-priority">Prioridad</th>
                                    <th class="col-action">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="radar-skeleton-rows">
                                <?php for($i=0; $i<5; $i++): ?>
                                <tr>
                                    <td>
                                        <div class="skeleton skeleton-text" style="width: 140px;"></div>
                                        <div class="skeleton skeleton-text" style="width: 80px; height: 12px;"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton skeleton-text" style="width: 100px;"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton skeleton-text" style="width: 90px;"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton skeleton-text" style="width: 70px;"></div>
                                    </td>
                                    <td>
                                        <div class="skeleton skeleton-btn"></div>
                                    </td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                            <tbody id="real-radar-data" style="display: none;">
                                <?php foreach ($companies as $index => $co): ?>
                                <tr class="<?= $index >= 3 ? 'locked-rows' : '' ?>">
                                    <td class="col-company">
                                        <div style="font-weight: 800; color: #1e293b;"><?= esc($co['company_name']) ?></div>
                                        <div style="font-size: 0.75rem; color: #94a3b8; margin-top: 2px;"><?= esc($co['cnae_label'] ?? 'Sector B2B') ?></div>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: #475569;"><?= esc($co['municipality'] ?? 'España') ?></div>
                                    </td>
                                    <td><span class="ticket-pill">5.000€ – 12.000€</span></td>
                                    <td>
                                        <span class="priority-pill priority-pill--<?= $co['priority_level'] ?>">
                                            <?= $co['priority_level'] === 'muy_alta' ? '🔥 Muy Alta' : '⚡ Alta' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-ae" style="padding: 8px 16px; font-size: 0.75rem; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-weight: 800; border-radius: 8px; display: block; width: 100%;" title="Requiere acceso completo">
                                            Contactar ahora ⚡
                                        </button>
                                        <div style="font-size: 0.65rem; color: #94a3b8; font-weight: 700; margin-top: 4px; text-align: center;">(Requiere acceso completo)</div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                
                                <!-- Fake rows for blur -->
                                <?php for($i=0; $i<12; $i++): ?>
                                <tr class="locked-rows">
                                    <td><div style="height: 12px; width: 140px; background: #f1f5f9; border-radius: 4px;"></div><div style="height: 8px; width: 180px; background: #f8fafc; border-radius: 4px; margin-top: 6px;"></div></td>
                                    <td><div style="height: 12px; width: 80px; background: #f1f5f9; border-radius: 4px;"></div></td>
                                    <td><div style="height: 12px; width: 100px; background: #f1f5f9; border-radius: 4px;"></div></td>
                                    <td><div style="height: 12px; width: 60px; background: #f1f5f9; border-radius: 4px;"></div></td>
                                    <td><div style="height: 12px; width: 80px; background: #f1f5f9; border-radius: 4px;"></div></td>
                                </tr>
                                <?php endfor; ?>
                            </tbody>
                        </table>

                        <!-- ③ OVERLAY (Consolidated Refined) -->
                        <div class="table-overlay">
                            <div class="table-overlay__content">
                                <div class="table-overlay__icon">🔒</div>
                                <h3 class="table-overlay__title">Empresas en proceso de contratación ahora mismo — <span style="color: #ef4444;">acceso restringido</span></h3>
                                <p class="table-overlay__sub">Nuevas oportunidades detectadas hoy. Desbloquea el listado completo para ver el CIF, contacto y detalles financieros.</p>
                                
                                <button class="table-overlay__btn" onclick="scrollToEmail()">
                                    Desbloquear acceso ahora
                                </button>
                                
                                <div style="margin-top: 20px; font-size: 0.8rem; color: #94a3b8; font-weight: 800; display: flex; align-items: center; justify-content: center; gap: 8px; text-transform: uppercase; letter-spacing: 0.05em;">
                                    <span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; box-shadow: 0 0 10px rgba(16, 185, 129, 0.4);"></span>
                                    Monitorización en vivo
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- ④ FINAL CONVERSION BLOCK (Restored) -->
                    <section class="final-cta-block" data-track-section="capture_bottom" style="position: relative; background: #0f172a; border-radius: 32px; padding: 80px 40px; text-align: center; border: 1px solid rgba(255,255,255,0.1); box-shadow: var(--ae-radar-shadow-lg); margin-top: 60px; overflow: hidden;">
                    <!-- Premium Glows -->
                    <div style="position: absolute; width: 400px; height: 400px; background: radial-gradient(circle, rgba(33, 82, 255, 0.15) 0%, transparent 70%); top: -150px; left: -150px; pointer-events: none;"></div>
                    <div style="position: absolute; width: 400px; height: 400px; background: radial-gradient(circle, rgba(16, 185, 129, 0.1) 0%, transparent 70%); bottom: -150px; right: -150px; pointer-events: none;"></div>

                    <div style="position: relative; z-index: 2;">
                        <div style="display: inline-flex; align-items: center; gap: 8px; padding: 8px 16px; border-radius: 999px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: #60a5fa; font-size: 0.8rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 24px;">
                            <span style="width: 8px; height: 8px; border-radius: 50%; background: #60a5fa; box-shadow: 0 0 12px #60a5fa;"></span>
                            Acceso inmediato hoy
                        </div>

                        <h2 style="font-size: clamp(2rem, 4vw, 2.75rem); font-weight: 950; color: #ffffff; margin-bottom: 20px; letter-spacing: -0.04em; line-height: 1.1;">
                            ¿Listo para conseguir clientes <span style="background: linear-gradient(90deg, #60a5fa, #34d399); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">reales hoy mismo</span>?
                        </h2>
                        
                        <p style="font-size: 1.15rem; color: rgba(255,255,255,0.6); margin-bottom: 40px; max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.6; font-weight: 500;">
                            No dejes que tu competencia se adelante. Accede ahora al Radar B2B y empieza a recibir oportunidades de negocio filtradas cada mañana.
                        </p>
                        
                        <div style="max-width: 520px; margin: 0 auto;">
                            <form class="preview-form-handler" style="display: flex; flex-direction: column; gap: 16px;">
                                <input type="email" name="email" class="email-capture__input" placeholder="Introduce tu email profesional" style="width: 100%; text-align: center; height: 64px; border-radius: 16px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: white; font-size: 1.1rem; font-weight: 600;" required>
                                <button type="submit" class="email-capture__btn submit-btn-handler" style="width: 100%; font-size: 1.2rem; height: 68px; border-radius: 16px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; font-weight: 800; border: none; box-shadow: 0 20px 40px rgba(37, 99, 235, 0.3); cursor: pointer; transition: transform 0.2s;">
                                    Activar mi acceso a las <?= $opps_count ?> empresas
                                </button>
                                <div class="form-error-handler" style="color: #ef4444; font-size: 0.85rem; font-weight: 700; margin-top: 8px; display: none; text-align: center;"></div>
                            </form>
                            
                            <div style="display: flex; align-items: center; justify-content: center; gap: 16px; margin-top: 32px; padding-top: 32px; border-top: 1px solid rgba(255,255,255,0.05);">
                                <div style="display: flex; align-items: center; gap: 8px; color: rgba(255,255,255,0.5); font-size: 0.85rem; font-weight: 600;">
                                    <span style="color: #10b981;">✓</span> Sin tarjeta
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; color: rgba(255,255,255,0.5); font-size: 0.85rem; font-weight: 600;">
                                    <span style="color: #10b981;">✓</span> Acceso 24/7
                                </div>
                                <div style="display: flex; align-items: center; gap: 8px; color: rgba(255,255,255,0.5); font-size: 0.85rem; font-weight: 600;">
                                    <span style="color: #10b981;">✓</span> Leads reales
                                </div>
                            </div>
                        </div>

                        <div style="font-size: 0.8rem; color: rgba(255,255,255,0.3); font-weight: 600; margin-top: 40px; text-transform: uppercase; letter-spacing: 0.1em;">
                            Agencias, SaaS y consultores ya están generando clientes cada semana
                        </div>
                    </div>
                </section>

                </div>
            </div>
            
        </main>
    </div>


<!-- ⑥ INTERACTION MODAL (Premium Redesign) -->
<div class="ae-modal" id="interaction-modal">
    <div class="ae-modal__content" style="padding: 0; overflow: hidden; max-width: 440px;">
        <button class="ae-modal__close" onclick="closeModal()">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
        </button>

        <div style="padding: 48px 40px 32px; text-align: center;">
            <div style="display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; border-radius: 999px; background: #f0fdf4; border: 1px solid #dcfce7; color: #16a34a; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 20px;">
                <span style="width: 6px; height: 6px; border-radius: 50%; background: #10b981; animation: pulse 2s infinite;"></span>
                Radar B2B Activo
            </div>

            <h3 style="font-size: 1.75rem; font-weight: 950; color: #0f172a; margin-bottom: 12px; letter-spacing: -0.04em; line-height: 1.15;">
                Acceso restringido
            </h3>
            <p style="color: #64748b; font-weight: 500; font-size: 1rem; line-height: 1.5; margin-bottom: 0;">
                Introduce tu email profesional para ver el CIF, contacto y detalles de esta empresa.
            </p>
        </div>

        <div style="padding: 0 40px 48px;">
            <form class="email-capture__form preview-form-handler">
                <div style="position: relative;">
                    <input type="email" name="email" class="email-capture__input" placeholder="ejemplo@empresa.com" style="height: 64px; font-size: 1.1rem; text-align: center; border-radius: 16px; background: #f8fafc; border: 2px solid #e2e8f0;" required>
                </div>
                <button type="submit" class="email-capture__btn submit-btn-handler" style="height: 64px; font-size: 1.1rem; font-weight: 850; border-radius: 16px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); box-shadow: 0 12px 24px rgba(37, 99, 235, 0.25);">
                    Acceder a las <?= $opps_count ?> empresas
                </button>
                <div class="form-error-handler" style="color: #ef4444; font-size: 0.85rem; font-weight: 700; margin-top: 12px; display: none; text-align: center;"></div>
            </form>

            <div style="display: flex; align-items: center; justify-content: center; gap: 16px; margin-top: 24px; padding-top: 24px; border-top: 1px solid #f1f5f9;">
                <div style="display: flex; align-items: center; gap: 6px; color: #94a3b8; font-size: 0.8rem; font-weight: 700;">
                    <span style="color: #10b981;">✓</span> Sin tarjeta
                </div>
                <div style="display: flex; align-items: center; gap: 6px; color: #94a3b8; font-size: 0.8rem; font-weight: 700;">
                    <span style="color: #10b981;">✓</span> Acceso 24/7
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ⑧ TRACKING & LOGIC -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    // Utility for tracking
    function trackPreviewEvent(type, metadata = {}) {
        const source = metadata.source || '<?= esc($source ?? "direct") ?>';
        if (window.trackEvent) {
            window.trackEvent(type, { source: source, page_type: 'radar_preview', ...metadata });
        }
    }

    $(document).ready(function() {
        // preview_view
        trackPreviewEvent('preview_view', { source: '<?= esc($source ?? "direct") ?>' });

        // Recurring user logic
        if (localStorage.getItem('preview_seen')) {
            // Mostrar sutil mensaje de retorno
            $('<div id="returning-msg" style="position:fixed; top:20px; left:50%; transform:translateX(-50%); background:#0f172a; color:white; padding:12px 24px; border-radius:50px; font-weight:700; z-index:10000; box-shadow:0 10px 25px rgba(0,0,0,0.2); font-size:0.9rem; display:none;">Vuelve a donde lo dejaste — desbloquea las oportunidades ahora</div>')
                .appendTo('body').fadeIn().delay(3000).fadeOut();
            
            // Scroll automático suave tras s
            setTimeout(() => {
                document.getElementById('email-section').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }, 1000);
        }
        localStorage.setItem('preview_seen', 'true');

        // Skeleton Loader logic
        setTimeout(() => {
            $('#radar-table-container').removeClass('radar-table-loading');
            $('#real-radar-data').fadeIn();
        }, 500);

        // Sticky CTA Toggle
        let highlightTriggered = false;
        function triggerInputHighlight() {
            if (highlightTriggered) return;
            highlightTriggered = true;
            $('.email-capture__input').first().addClass('input-highlight-pulse').focus();
            setTimeout(() => {
                $('.email-capture__input').removeClass('input-highlight-pulse');
            }, 4000);
        }

        // Time trigger
        setTimeout(triggerInputHighlight, 5000);
        $(window).scroll(function() {
            // Scroll trigger (>50%)
            const scrollPercent = ($(window).scrollTop() / ($(document).height() - $(window).height())) * 100;
            if (scrollPercent > 50) {
                triggerInputHighlight();
            }
        });

        // Trigger modal after 15s (if not converted)
        setTimeout(() => {
            if (!sessionStorage.getItem('radar_converted')) {
                openModal('timer');
            }
        }, 15000);

        // Interaction Triggers
        $(document).on('click', '.locked-rows, .btn-ae', function(e) {
            e.preventDefault();
            openModal('interaction');
        });

        // Real-time validation & Tracking
        let typingTimer;
        $(document).on('focus', '.email-capture__input', function() {
            trackPreviewEvent('preview_input_focus', { source: '<?= esc($source ?? "direct") ?>' });
        });

        $(document).on('input', '.email-capture__input', function() {
            const val = $(this).val();
            clearTimeout(typingTimer);
            typingTimer = setTimeout(() => {
                trackPreviewEvent('preview_input_typing', { chars: val.length, source: '<?= esc($source ?? "direct") ?>' });
            }, 1000);

            // Simple validation feedback
            if (val && !val.includes('@')) {
                $(this).css('border-color', '#ef4444');
            } else {
                $(this).css('border-color', '');
            }
        });

        // Unified Form Handler
        $('.preview-form-handler').on('submit', function(e) {
            e.preventDefault();
            const $form = $(this);
            const $btn = $form.find('.submit-btn-handler');
            const $error = $form.find('.form-error-handler');
            const email = $form.find('input[name="email"]').val();
            
            if (!email || !email.includes('@')) {
                $error.text('Introduce un email profesional válido').show();
                return;
            }

            $btn.prop('disabled', true).html('<span style="display:flex;align-items:center;justify-content:center;gap:8px;"><div class="radar-loading-spinner" style="width:14px;height:14px;border:2px solid rgba(255,255,255,0.2);border-top-color:#fff;border-radius:50%;animation:spin 0.6s linear infinite;"></div> Accediendo… (menos de 10s)</span>');
            $error.hide();

            // submit_email_preview
            trackPreviewEvent('preview_email_submit', { 
                email: email, 
                form_type: $form.closest('.ae-modal').length ? 'modal' : ($form.closest('.final-cta-block').length ? 'bottom' : 'inline'),
                source: '<?= esc($source ?? "direct") ?>'
            });

            $.ajax({
                url: '<?= site_url("radar/preview") ?>',
                method: 'POST',
                data: { 
                    email: email,
                    source: '<?= esc($source ?? "direct") ?>',
                    <?= csrf_token() ?>: '<?= csrf_hash() ?>'
                },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success' || res.status === 'exists') {
                        sessionStorage.setItem('radar_converted', 'true');
                        
                        // Show Micro-loading
                        $('#radar-loading-overlay').addClass('is-active');
                        
                        // redirect_to_radar
                        trackPreviewEvent('redirect_to_radar', { status: res.status });
                        
                        // Artificial delay for perception of value
                        setTimeout(() => {
                            window.location.href = res.redirect + (res.redirect.includes('?') ? '&' : '?') + 'source=<?= esc($source ?? "direct") ?>';
                        }, 1500);
                    } else {
                        $error.text(res.message).show();
                        $btn.prop('disabled', false).text('Acceder ahora');
                    }
                },
                error: function() {
                    $error.text('Ha ocurrido un error. Por favor, inténtalo de nuevo.').show();
                    $btn.prop('disabled', false).text('Acceder ahora');
                }
            });
        });
    });

    function openModal(trigger = 'manual') {
        if (sessionStorage.getItem('radar_converted')) return;
        $('#interaction-modal').addClass('is-active');
        trackPreviewEvent('open_modal_preview', { trigger: trigger });
    }

    function closeModal() {
        $('#interaction-modal').removeClass('is-active');
    }

    function scrollToEmail() {
        // click_cta_preview
        trackPreviewEvent('click_cta_preview', { source: '<?= esc($source ?? "direct") ?>' });
        document.getElementById('email-section').scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => {
            $('.inline-capture .email-capture__input').focus();
        }, 800);
    }
</script>

<!-- SVG Gradient for logo -->
<svg style="width:0;height:0;position:absolute;" aria-hidden="true">
    <defs>
        <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
            <stop stop-color="#2152FF"/>
            <stop offset=".65" stop-color="#5C7CFF"/>
            <stop offset="1" stop-color="#12B48A"/>
        </linearGradient>
    </defs>
</svg>

</body>
</html>
