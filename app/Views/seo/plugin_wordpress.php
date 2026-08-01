<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title' => 'Plugin WordPress Buscador de Empresas | Integración API Registros | APIEmpresas.es',
        'excerptText' => 'Integra el buscador de empresas oficial de APIEmpresas.es en tu WordPress. Plugin no-code, buscador por CIF o Nombre y resultados en tiempo real.',
        'canonical' => site_url('plugin-wordpress-buscador-empresas'),
        'robots' => 'index,follow',
    ]) ?>
    <link rel="stylesheet" href="<?= base_url('public/css/home.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/home-mobile.css') ?>?v=<?= time() ?>" media="screen and (max-width: 768px)">
</head>
<body>
    <?= view('partials/header') ?>
    <main>
<style>
    /* Global GS-like Styles */
    .wp-page-wrapper {
        background-color: #f8fafc;
        min-height: 100vh;
        padding: 60px 20px;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .wp-container {
        max-width: 1000px;
        margin: 0 auto;
    }
    .wp-hero {
        text-align: center;
        margin-bottom: 40px;
    }
    .wp-badge-top {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #ecfdf5;
        color: #059669;
        padding: 6px 16px;
        border-radius: 100px;
        font-size: 0.9rem;
        font-weight: 700;
        margin-bottom: 24px;
        border: 1px solid #a7f3d0;
    }
    .wp-badge-top-dot {
        background: #22c55e;
        box-shadow: 0 0 10px rgba(34, 197, 94, 0.35);
        width: 8px; height: 8px; border-radius: 50%;
    }
    .wp-title {
        font-size: 3.5rem;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -0.03em;
        margin: 0 0 20px 0;
        line-height: 1.1;
    }
    .wp-title span {
        background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .wp-subtitle {
        font-size: 1.3rem;
        color: #475569;
        max-width: 700px;
        margin: 0 auto;
        line-height: 1.6;
    }
    
    /* AHA Moment / WP Demo Styles */
    .wp-demo-wrapper {
        margin: 40px auto 60px auto;
        max-width: 600px; /* narrowed for WP card */
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .wp-demo-header {
        background: #f1f5f9;
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .wp-demo-dots {
        display: flex;
        gap: 6px;
    }
    .wp-demo-dot { width: 12px; height: 12px; border-radius: 50%; background: #cbd5e1; }
    .wp-demo-dot:nth-child(1) { background: #ef4444; }
    .wp-demo-dot:nth-child(2) { background: #f59e0b; }
    .wp-demo-dot:nth-child(3) { background: #10b981; }
    .wp-demo-logo { font-size: 18px; color: #72aee6; font-weight: 900; }

    /* The old WP preview CSS adapted */
    .wp-preview-card__body {
        padding: 32px;
        background: #f0f0f1;
        text-align: left;
    }
    .wp-search-box {
        background: #fff;
        padding: 15px;
        border-radius: 8px;
        border: 1px solid #ccd0d4;
        display: flex;
        gap: 10px;
        margin-bottom: 20px;
    }
    .wp-search-input {
        flex: 1;
        border: 1px solid #8c8f94;
        padding: 8px 12px;
        border-radius: 4px;
        font-size: 13px;
        color: #1d2327;
    }
    .wp-search-btn {
        background: #2271b1;
        color: #fff;
        border: none;
        padding: 8px 16px;
        border-radius: 4px;
        font-weight: 600;
        font-size: 13px;
    }
    .wp-result-card {
        background: #fff;
        border: 1px solid #ccd0d4;
        border-left: 4px solid #2271b1;
        padding: 15px;
        border-radius: 0 4px 4px 0;
    }
    .wp-result-anim {
        opacity: 0;
        animation: fadeInResult 4s infinite;
    }
    @keyframes fadeInResult {
        0%, 20% { opacity: 0; transform: translateY(5px); }
        30%, 90% { opacity: 1; transform: translateY(0); }
        100% { opacity: 0; }
    }
    .wp-typing-anim::after {
        content: '|';
        animation: typingText 4s infinite;
    }
    @keyframes typingText {
        0%, 5% { content: '|'; }
        10% { content: 'B|'; }
        15% { content: 'B1|'; }
        20% { content: 'B123|'; }
        25% { content: 'B12345678|'; }
        90% { content: 'B12345678|'; }
        100% { content: '|'; }
    }

    /* Layout Grids */
    .wp-features {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        margin-bottom: 60px;
    }
    @media (min-width: 768px) {
        .wp-features { grid-template-columns: repeat(4, 1fr); }
    }
    .wp-feature-card {
        background: white;
        padding: 24px;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
    }
    .wp-feature-icon {
        width: 48px; height: 48px;
        border-radius: 12px;
        background: #eff6ff;
        color: #2563eb;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 16px;
        font-size: 24px;
    }
    .wp-feature-card h3 { font-size: 1.1rem; font-weight: 800; margin: 0 0 8px 0; color: #0f172a; }
    .wp-feature-card p { margin: 0; color: #475569; font-size: 0.9rem; line-height: 1.5; }

    /* Integration Options */
    .wp-card {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 40px;
        position: relative;
    }
    .wp-card-accent {
        position: absolute; top: 0; left: 0; width: 100%; height: 6px;
        background: linear-gradient(90deg, #3b82f6, #2563eb);
    }
    .wp-card-body { padding: 48px; }
    .wp-step-title {
        font-size: 1.8rem; font-weight: 900; color: #0f172a; margin: 0 0 16px 0; display: flex; align-items: center; gap: 16px;
    }
    .wp-step-number {
        display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; color: #ffffff; font-size: 1.2rem; font-weight: 800; flex-shrink: 0;
    }
    .wp-step-number.blue { background: #2563eb; }
    
    .wp-desc { color: #475569; font-size: 1.15rem; line-height: 1.6; margin-bottom: 32px; }
    
    /* Template CTA */
    .wp-template-box {
        background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 16px; padding: 32px; display: flex; flex-direction: column; gap: 24px;
    }
    @media (min-width: 768px) {
        .wp-template-box { flex-direction: row; align-items: center; justify-content: space-between; }
    }
    .wp-template-title { font-weight: 900; color: #1e3a8a; font-size: 1.4rem; margin: 0 0 8px 0; }
    .wp-template-desc { color: #1d4ed8; margin: 0; font-size: 1.1rem; line-height: 1.5; }
    .wp-btn-primary {
        display: inline-block; background: #2563eb; color: #ffffff; font-weight: 800; font-size: 1.1rem; padding: 16px 32px; border-radius: 12px; text-decoration: none; box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3); transition: all 0.2s; text-align: center; white-space: nowrap; border: none; cursor: pointer;
    }
    .wp-btn-primary:hover { background: #1d4ed8; transform: translateY(-3px); box-shadow: 0 12px 24px rgba(37, 99, 235, 0.4); }

    /* Custom Steps */
    .wp-steps-list { display: flex; flex-direction: column; gap: 48px; }
    .wp-substep { display: flex; gap: 24px; align-items: flex-start; }
    .wp-substep-letter {
        display: flex; align-items: center; justify-content: center; width: 36px; height: 36px; background: #eff6ff; color: #1d4ed8; font-weight: 900; border-radius: 10px; font-size: 1.1rem; flex-shrink: 0; margin-top: 4px;
    }
    .wp-substep-content h4 { margin: 0 0 8px 0; font-size: 1.4rem; color: #0f172a; font-weight: 800; }
    .wp-substep-content p { margin: 0 0 16px 0; color: #475569; font-size: 1.1rem; line-height: 1.6; }
</style>

<div class="wp-page-wrapper">
    <div class="wp-container">
        
        <div class="wp-hero">
            <div class="wp-badge-top">
                <span class="wp-badge-top-dot"></span>
                NUEVO PLUGIN WP DISPONIBLE
            </div>
            <h1 class="wp-title">Convierte tu WordPress en una herramienta de<br><span>datos empresariales</span></h1>
            <p class="wp-subtitle">Instala el plugin oficial, añade tu API Key y ofrece autocompletado y validación de empresas (Directorios, Formularios, WooCommerce) en tu web en solo 1 minuto.</p>
        </div>

        <!-- AHA Moment Animation -->
        <div class="wp-demo-wrapper">
            <div class="wp-demo-header">
                <div class="wp-demo-dots">
                    <div class="wp-demo-dot"></div><div class="wp-demo-dot"></div><div class="wp-demo-dot"></div>
                </div>
                <div class="wp-demo-logo">WP</div>
            </div>
            <div class="wp-preview-card__body">
                <div class="wp-search-box">
                    <div class="wp-search-input wp-typing-anim"></div>
                    <div class="wp-search-btn">Buscar</div>
                </div>

                <div class="wp-result-card wp-result-anim">
                    <div style="font-size: 12px; font-weight: 700; color: #1d2327; margin-bottom: 5px;">
                        TECH FLOW SOLUTIONS SL</div>
                    <div style="font-size: 11px; color: #64748b;">MADRID • CNAE: 6201</div>
                    <div style="margin-top: 10px; display: flex; gap: 5px;">
                        <span style="background: #dcfce7; color: #166534; font-size: 9px; padding: 2px 6px; border-radius: 4px; font-weight: 700;">ACTIVA</span>
                        <span style="background: #e0f2fe; color: #0369a1; font-size: 9px; padding: 2px 6px; border-radius: 4px; font-weight: 700;">IO: 94/100</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Use cases -->
        <div class="wp-features">
            <div class="wp-feature-card">
                <div class="wp-feature-icon">📁</div>
                <h3>Directorios</h3>
                <p>Aumenta el tiempo en página aportando información oficial del Registro Mercantil al instante.</p>
            </div>
            <div class="wp-feature-card">
                <div class="wp-feature-icon">🎯</div>
                <h3>Agencias</h3>
                <p>Genera leads cualificados. Crea buscadores sectoriales para captar tráfico pasivo muy segmentado.</p>
            </div>
            <div class="wp-feature-card">
                <div class="wp-feature-icon">⚡</div>
                <h3>SaaS B2B</h3>
                <p>Integra validación de cuentas y scoring de empresas sin necesidad de tocar el backend.</p>
            </div>
            <div class="wp-feature-card">
                <div class="wp-feature-icon">🛒</div>
                <h3>WooCommerce</h3>
                <p>Valida automáticamente el CIF y autocompleta la Razón Social en la página de Checkout.</p>
            </div>
        </div>

        <!-- Opción 1: Descarga -->
        <div class="wp-card">
            <div class="wp-card-accent"></div>
            <div class="wp-card-body">
                <h2 class="wp-step-title">
                    <span class="wp-step-number blue">1</span>
                    Descarga e Instala el Plugin
                </h2>
                <p class="wp-desc">Hemos preparado un plugin sin dependencias y optimizado al máximo (Nativo WP) para que no afecte a la velocidad de carga de tu web.</p>
                
                <div class="wp-template-box">
                    <div>
                        <h3 class="wp-template-title">Plugin B2B Suite v1.0</h3>
                        <p class="wp-template-desc">Compatible con Elementor, Gutenberg, Divi y WooCommerce.</p>
                        <div style="display: flex; gap: 12px; margin-top: 12px;">
                            <span style="color: #059669; font-size: 0.85rem; font-weight: 700;">✔ 100% RGPD</span>
                            <span style="color: #059669; font-size: 0.85rem; font-weight: 700;">✔ Gratuito</span>
                        </div>
                    </div>
                    <a href="<?= base_url('public/apiempresas-b2b-suite.zip') ?>" download class="wp-btn-primary" onclick="if(window.trackEvent) trackEvent('wp_plugin_download');">Descargar Plugin (.zip)</a>
                </div>
            </div>
        </div>

        <!-- Opción 2: Configuración -->
        <div class="wp-card">
            <div class="wp-card-body">
                <h2 class="wp-step-title">
                    <span class="wp-step-number blue">2</span>
                    Activa tu cuenta en WordPress
                </h2>
                <p class="wp-desc">Una vez subido a WordPress desde el menú <strong>Plugins &gt; Añadir nuevo</strong>, solo te queda conectar tu API Key para que empiece a servir datos reales.</p>

                <div class="wp-steps-list">
                    
                    <div class="wp-substep">
                        <div class="wp-substep-letter">A</div>
                        <div class="wp-substep-content">
                            <h4>Copia tu API Key</h4>
                            <p>Si ya tienes cuenta, ve a tu <a href="<?= site_url('dashboard') ?>" style="color: #2563eb; font-weight: 600;" onclick="if(window.trackEvent) trackEvent('wp_plugin_dashboard_click');">Dashboard de desarrollador</a> y copia tu Clave Secreta de API. Si no tienes, puedes registrarte gratis en 10 segundos.</p>
                        </div>
                    </div>

                    <div class="wp-substep">
                        <div class="wp-substep-letter">B</div>
                        <div class="wp-substep-content" style="width: 100%;">
                            <h4>Conecta el plugin en tu WordPress</h4>
                            <p>En el menú lateral de WordPress, haz clic en <strong>APIEmpresas</strong>. Pega tu API Key en la sección "Autenticación y Conexión" y haz clic en <strong>Guardar y Conectar</strong>.</p>
                        </div>
                    </div>

                    <div class="wp-substep">
                        <div class="wp-substep-letter">C</div>
                        <div class="wp-substep-content">
                            <h4>Activa los Módulos</h4>
                            <p>Baja a "Módulos Activos" y activa el Auto-Checkout para WooCommerce con un clic, o usa el shortcode <code style="background: #f1f5f9; padding: 4px 8px; border-radius: 6px; font-weight: 700; color: #1e293b;">[apiempresas_buscador]</code> para insertar el buscador en cualquier página.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    if (window.trackEvent) {
        trackEvent('wp_plugin_page_view');
    }
});
</script>

    </main>
    <?php if (service('request')->getLocale() === 'en'): ?>
        <?= view('partials/footer_en') ?>
    <?php else: ?>
        <?= view('partials/footer') ?>
    <?php endif; ?>
</body>
</html>