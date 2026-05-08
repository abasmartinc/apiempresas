<?php
/**
 * /radar/preview — Value-First Intermediate Page
 * Replicates dashboard look with restricted access to generate urgency.
 */

// Mock data for partials
$isFree = true;
$filters = [
    'provincia' => '',
    'cnae' => '',
    'rango' => 'hoy',
    'q' => '',
    'per_page' => 20
];
$pagination = [
    'total' => $opps_count,
    'start' => 1,
    'end' => count($companies)
];
?>
<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => $title ?? 'Radar B2B — Empresas detectadas hoy listas para contactar',
        'excerptText' => $excerptText ?? 'Descubre empresas recién creadas que necesitan proveedores ahora mismo. Accede antes que tu competencia.',
        'canonical'   => site_url('radar/preview'),
        'robots'      => 'noindex,follow',
    ]) ?>

    <link rel="stylesheet" href="<?= base_url('public/css/radar.css?v=' . (file_exists(FCPATH . 'public/css/radar.css') ? filemtime(FCPATH . 'public/css/radar.css') : time())) ?>">

    <style>
        /* Override radar.css for preview specific needs if any, but try to keep it minimal */
        .ae-radar-page { background: #e2e8f0 !important; }
        .ae-radar-page__main { background: #f8fafc; }
        .ae-radar-page__topbar { 
            background: #ffffff !important; 
            border-bottom: 1px solid #e2e8f0 !important;
            padding: 16px 40px !important;
        }
        .ae-radar-page__breadcrumb { color: #64748b !important; }
        .ae-radar-page__breadcrumb strong { color: #1e293b !important; }
        .ae-radar-page__freshness { color: #64748b !important; }
        
        .ae-radar-page__content { padding: 40px !important; }

        /* Preview Hero Header - Matching Dashboard Aesthetic (LIGHT VERSION) */
        .preview-header-banner {
            background: #ffffff;
            border-radius: 24px;
            padding: 64px 40px;
            margin-bottom: 32px;
            text-align: center;
            color: #1e293b;
            position: relative;
            overflow: hidden;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 40px rgba(15, 23, 42, 0.03);
        }

        .preview-header-banner__glow {
            position: absolute;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.1) 0%, transparent 70%);
            top: -150px;
            right: -150px;
            pointer-events: none;
        }

        .preview-header-banner__badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: rgba(37, 99, 235, 0.15);
            border: 1px solid rgba(37, 99, 235, 0.3);
            border-radius: 999px;
            color: #60a5fa;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 24px;
        }

        .preview-header-banner__title {
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            font-weight: 950;
            line-height: 1.1;
            margin-bottom: 16px;
            letter-spacing: -0.04em;
        }

        .preview-header-banner__subtitle {
            font-size: 1.15rem;
            color: #64748b;
            max-width: 750px;
            margin: 0 auto 32px;
            line-height: 1.5;
            font-weight: 500;
        }

        .preview-capture-box {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 24px 32px;
            max-width: 700px;
            margin: 0 auto;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
        }

        .email-capture-form {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .email-capture-input {
            flex: 1;
            min-width: 260px;
            height: 58px;
            padding: 0 24px;
            border-radius: 14px;
            border: 1px solid #cbd5e1;
            background: #ffffff;
            color: #1e293b;
            font-size: 1rem;
            font-weight: 600;
            outline: none;
            transition: all 0.2s;
        }

        .email-capture-input:focus {
            border-color: #3b82f6;
            background: rgba(255,255,255,0.1);
        }

        .email-capture-btn {
            height: 58px;
            padding: 0 32px;
            border-radius: 14px;
            background: #2563eb;
            color: white;
            font-weight: 900;
            font-size: 1rem;
            border: none;
            cursor: pointer;
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
            transition: transform 0.2s, background 0.2s;
        }

        .email-capture-btn:hover {
            transform: translateY(-2px);
            background: #1d4ed8;
        }

        /* Mock Search Hero */
        .ae-pro-search-hero {
            background: #fff; border: 1px solid #e2e8f0;
            border-radius: 20px; padding: 24px 32px;
            margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.04);
            border-style: dashed; background: #fafafa;
        }

        /* Modals & Interaction */
        #radar-loading-overlay {
            position: fixed; inset: 0;
            background: rgba(15, 23, 42, 0.98);
            z-index: 99999;
            display: none;
            flex-direction: column; align-items: center; justify-content: center;
            color: white; text-align: center;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        #radar-loading-overlay.is-active { display: flex !important; }

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

        .ae-modal.is-active {
            opacity: 1;
            visibility: visible;
        }

        .ae-modal__content {
            background: white;
            border-radius: 28px;
            width: 100%;
            max-width: 480px;
            position: relative;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            transform: scale(0.95);
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        .ae-modal.is-active .ae-modal__content {
            transform: scale(1);
        }

        .ae-modal__close {
            position: absolute;
            top: 20px;
            right: 20px;
            width: 36px;
            height: 36px;
            background: #f1f5f9;
            border: none;
            border-radius: 50%;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            color: #64748b;
            z-index: 10;
            transition: all 0.2s;
        }
        
        .ae-modal__close:hover {
            background: #e2e8f0;
            color: #1e293b;
            transform: rotate(90deg);
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.5); opacity: 0.5; }
            100% { transform: scale(1); opacity: 1; }
        }
    </style>
</head>
<body>

    <div id="radar-loading-overlay">
        <div style="width: 60px; height: 60px; border: 5px solid rgba(255,255,255,0.1); border-top-color: #3b82f6; border-radius: 50%; animation: spin 1s linear infinite; margin-bottom: 24px;"></div>
        <h3 style="font-size: 1.75rem; font-weight: 900; margin-bottom: 8px; letter-spacing: -0.02em;">Preparando tu Radar B2B...</h3>
        <p style="font-size: 1.1rem; color: #94a3b8; font-weight: 500;">Activando acceso a las oportunidades de hoy</p>
    </div>

    <div class="ae-radar-page">
        <div class="ae-radar-page__shell" style="display:flex; width: 100%;">
            
            <?= view('radar/partials/sidebar', ['stats' => ['hoy' => $opps_count]]) ?>

            <main class="ae-radar-page__main" style="flex:1; min-width:0;">
                
                <header class="ae-radar-page__topbar">
                    <div class="ae-radar-page__breadcrumb">
                        <span>APIEmpresas</span>
                        <span>/</span>
                        <strong>Radar B2B (Preview)</strong>
                    </div>

                    <div class="ae-radar-page__topbar-actions">
                        <div class="ae-radar-page__freshness">
                            <span class="ae-radar-page__freshness-dot" style="background: #10b981; width: 8px; height: 8px; border-radius: 50%; display: inline-block; margin-right: 8px; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);"></span>
                            Actualizado hace 2 min · Hoy: <strong style="color: #1e293b;">+<?= number_format($opps_count) ?></strong> empresas
                        </div>
                    </div>
                </header>

                <div class="ae-radar-page__content">
                    <div class="ae-radar-page__container">
                        
                        <!-- 1. PREVIEW BANNER -->
                        <section class="preview-header-banner">
                            <div class="preview-header-banner__glow"></div>
                            <div class="preview-header-banner__badge">
                                <span style="width: 8px; height: 8px; border-radius: 50%; background: #60a5fa; animation: pulse 2s infinite;"></span>
                                +<?= $opps_count ?> empresas detectadas hoy — acceso inmediato
                            </div>

                            <h1 class="preview-header-banner__title">
                                <span style="background: linear-gradient(90deg, #60a5fa, #34d399); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">+<?= $opps_count ?> empresas están</span><br>
                                contratando proveedor ahora mismo
                            </h1>

                            <p class="preview-header-banner__subtitle">
                                Si no contactas tú, otro proveedor lo hará en las próximas horas. <br>
                                <strong>Consigue clientes reales sin hacer prospección manual.</strong>
                            </p>

                            <div class="preview-capture-box">
                                <p style="font-size: 0.95rem; font-weight: 800; color: #64748b; margin-bottom: 16px;">Introduce tu email para desbloquear las <?= $opps_count ?> oportunidades</p>
                                <form class="email-capture-form preview-form-handler" id="email-section" style="display: flex; gap: 12px; flex-wrap: wrap; justify-content: center;">
                                    <input type="email" name="email" class="email-capture-input email-capture__input" placeholder="Introduce tu email profesional" style="flex: 1; min-width: 280px; height: 64px !important; min-height: 64px !important; line-height: 64px !important; padding: 0 !important; border-radius: 16px !important; text-align: center; border: 1px solid #cbd5e1 !important; font-size: 1.1rem; background: white !important; color: #1e293b !important; display: block !important; box-sizing: border-box !important;" required>
                                    <button type="submit" class="email-capture-btn submit-btn-handler" style="height: 64px; padding: 0 32px; border-radius: 16px; background: #2563eb; color: white; font-weight: 900; font-size: 1.1rem; border: none; cursor: pointer; box-shadow: 0 10px 20px rgba(37, 99, 235, 0.2);">
                                        Conseguir clientes ahora ⚡
                                    </button>
                                </form>
                                <div class="form-error-handler" style="color: #ef4444; font-size: 0.85rem; font-weight: 700; margin-top: 12px; display: none; text-align: center;"></div>
                                <div style="display: flex; align-items: center; justify-content: center; gap: 20px; margin-top: 20px; font-size: 0.8rem; color: #94a3b8; font-weight: 700;">
                                    <span style="display: flex; align-items: center; gap: 6px;"><span style="color: #10b981; font-size: 1.1rem;">✓</span> Sin tarjeta</span>
                                    <span style="display: flex; align-items: center; gap: 6px;"><span style="color: #10b981; font-size: 1.1rem;">✓</span> Acceso en <10 segundos</span>
                                </div>
                            </div>
                        </section>

                        <!-- 2. SEARCH IA TEASER -->
                        <div class="ae-pro-search-hero" onclick="openModal('ai_search_teaser')" style="cursor: pointer;">
                            <div class="ae-pro-search-hero__label" style="font-size: 11px; font-weight: 800; text-transform: uppercase; color: #94a3b8; margin-bottom: 14px; display: flex; align-items: center; gap: 8px;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" style="width:14px;height:14px;"><path d="M12 2L2 7l10 5 10-5-10-5z"/><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/></svg>
                                Búsqueda con IA
                                <span style="background:#fff1f2; color:#e11d48; font-size:9px; font-weight:900; padding:2px 7px; border-radius:999px; letter-spacing:0.05em; display:flex; align-items:center; gap:4px;">
                                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                    PRO ONLY
                                </span>
                            </div>
                            <div style="display:flex;gap:12px;align-items:center; opacity: 0.6; pointer-events: none;">
                                <div style="position:relative;flex-grow:1;">
                                    <input type="text" class="ae-pro-search-hero__input" placeholder="Ej: Empresas nuevas de construcción en Madrid..." style="width: 100%; height: 58px; padding: 0 24px; border-radius: 14px; border: 2px solid #e2e8f0; background: #f8fafc; font-size: 15px; outline: none; cursor: not-allowed;" readonly>
                                </div>
                                <button class="ae-pro-search-hero__btn" style="height: 58px; padding: 0 28px; background: #0f172a; color: #fff; border-radius: 14px; border: none; font-weight: 800; cursor: not-allowed;" disabled>Buscar con IA</button>
                            </div>
                        </div>

                        <!-- 3. RESULTS TABLE -->
                        <div style="background: #ffffff; border-radius: 24px; border: 1px solid #e2e8f0; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
                            <div style="margin-bottom: 24px; text-align: center;">
                                <div style="font-size: 1rem; color: #16a34a; font-weight: 800; background: #f0fdf4; padding: 12px; border-radius: 12px; border: 1px solid #dcfce7; display: inline-block;">
                                    💰 Oportunidades con tickets estimados entre 5.000€ y 12.000€
                                </div>
                            </div>

                            <?= view('radar/partials/results_table', [
                                'companies'  => $companies,
                                'isFree'     => true,
                                'pagination' => $pagination,
                                'filters'    => $filters,
                                'pager'      => null
                            ]) ?>
                        </div>

                        <!-- 4. FOOTER PAYWALL (LIGHT VERSION) -->
                        <div class="radar-paywall-main" style="background:#ffffff; color:#1e293b; padding:80px 40px; border-radius:32px; margin:40px 0; text-align:center; border: 1px solid #e2e8f0; box-shadow: 0 10px 40px rgba(0,0,0,0.03); position: relative; overflow: hidden;">
                            <!-- Light mesh gradient decoration -->
                            <div style="position: absolute; width: 400px; height: 400px; background: radial-gradient(circle, rgba(37, 99, 235, 0.03) 0%, transparent 70%); top: -150px; left: -150px; pointer-events: none;"></div>
                            
                            <h2 style="font-size: clamp(1.8rem, 4vw, 2.5rem); font-weight:950; margin-bottom:20px; letter-spacing: -0.03em; position: relative;">¿Listo para conseguir clientes <span style="background: linear-gradient(90deg, #2563eb, #10b981); -webkit-background-clip: text; background-clip: text; -webkit-text-fill-color: transparent;">reales hoy mismo</span>?</h2>
                            <p style="font-size:1.15rem; color:#64748b; margin-bottom:40px; max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.5; font-weight: 500; position: relative;">No dejes que tu competencia se adelante. Accede ahora al Radar B2B y empieza a recibir oportunidades de negocio filtradas cada mañana.</p>
                            
                            <div style="max-width: 480px; margin: 0 auto; position: relative;">
                                <form class="preview-form-handler" style="display: flex; flex-direction: column; gap: 16px;">
                                    <input type="email" name="email" class="email-capture-input email-capture__input" placeholder="Introduce tu email profesional" style="width: 100%; height: 64px !important; min-height: 64px !important; line-height: 64px !important; padding: 0 !important; text-align: center; border: 1px solid #cbd5e1 !important; background: #ffffff !important; color: #1e293b !important; border-radius: 16px !important; font-size: 1.1rem !important; display: block !important; box-sizing: border-box !important;" required>
                                    <button type="submit" class="submit-btn-handler" style="width: 100%; height: 64px; background: #2563eb; color: white; border: none; border-radius: 16px; font-weight: 900; font-size: 1.1rem; cursor: pointer; box-shadow: 0 10px 25px rgba(37,99,235,0.3);">
                                        Activar mi acceso a las <?= $opps_count ?> empresas
                                    </button>
                                </form>
                                <div class="form-error-handler" style="color: #ef4444; font-size: 0.85rem; font-weight: 700; margin-top: 12px; display: none; text-align: center;"></div>
                                <div style="display: flex; align-items: center; justify-content: center; gap: 20px; margin-top: 24px; font-size: 0.85rem; color: #64748b; font-weight: 700;">
                                    <span style="display: flex; align-items: center; gap: 6px;"><span style="color: #10b981; font-size: 1.1rem;">✓</span> Sin tarjeta</span>
                                    <span style="display: flex; align-items: center; gap: 6px;"><span style="color: #10b981; font-size: 1.1rem;">✓</span> Acceso 24/7</span>
                                    <span style="display: flex; align-items: center; gap: 6px;"><span style="color: #10b981; font-size: 1.1rem;">✓</span> Leads reales</span>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <footer class="ae-radar-page__footer" style="padding: 24px 40px; text-align: center; font-size: 13px; color: #94a3b8; font-weight: 600;">
                    &copy; <?= date('Y') ?> APIEmpresas · Inteligencia comercial B2B
                </footer>
            </main>
        </div>
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
            <form class="email-capture__form preview-form-handler" style="display: flex; flex-direction: column; gap: 16px;">
                <div style="position: relative;">
                    <input type="email" name="email" class="email-capture__input" placeholder="ejemplo@empresa.com" style="width: 100%; height: 64px; font-size: 1.1rem; text-align: center; border-radius: 16px; background: #f8fafc; border: 1px solid #e2e8f0; outline: none; transition: border-color 0.2s;" required>
                </div>
                <button type="submit" class="email-capture__btn submit-btn-handler" style="width: 100%; height: 64px; font-size: 1.1rem; font-weight: 800; border-radius: 16px; background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); color: white; border: none; box-shadow: 0 12px 24px rgba(37, 99, 235, 0.25); cursor: pointer; transition: transform 0.2s;">
                    Acceder a las <?= $opps_count ?> empresas
                </button>
                <div class="form-error-handler" style="color: #ef4444; font-size: 0.85rem; font-weight: 700; margin-top: 4px; display: none; text-align: center;"></div>
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
                form_type: $form.closest('.ae-modal').length ? 'modal' : ($form.closest('.radar-paywall-main').length ? 'bottom' : 'inline'),
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

    // Global helper for components that use showConversionNudge
    window.showConversionNudge = function(title, desc, metadata = {}) {
        openModal('conversion_nudge');
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
