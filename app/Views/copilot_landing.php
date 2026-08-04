<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head') ?>
    <link rel="stylesheet" href="<?= base_url('public/css/home.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/home-mobile.css') ?>?v=<?= time() ?>" media="screen and (max-width: 768px)">
</head>
<body>
    <?= view('partials/header') ?>
    
    <main>
<style>
    .cp-page-wrapper {
        background-color: #f8fafc;
        min-height: 100vh;
        padding: 60px 20px;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .cp-container {
        max-width: 1000px;
        margin: 0 auto;
    }
    .cp-hero {
        text-align: center;
        margin-bottom: 40px;
    }
    .cp-badge-top {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #eff6ff;
        color: #2563eb;
        padding: 6px 16px;
        border-radius: 100px;
        font-size: 0.9rem;
        font-weight: 700;
        margin-bottom: 24px;
        border: 1px solid #bfdbfe;
    }
    .cp-title {
        font-size: 3.5rem;
        font-weight: 900;
        color: #0f172a;
        letter-spacing: -0.03em;
        margin: 0 0 20px 0;
        line-height: 1.1;
    }
    .cp-title span {
        background: linear-gradient(135deg, #2563eb 0%, #4f46e5 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .cp-subtitle {
        font-size: 1.3rem;
        color: #475569;
        max-width: 700px;
        margin: 0 auto;
        line-height: 1.6;
    }

    /* Mockup Demo */
    .cp-demo-wrapper {
        margin: 40px auto 60px auto;
        max-width: 800px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1);
        border: 1px solid #e2e8f0;
        overflow: hidden;
    }
    .cp-demo-header {
        background: #f1f5f9;
        padding: 12px 16px;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    .cp-demo-dots {
        display: flex;
        gap: 6px;
    }
    .cp-demo-dot { width: 12px; height: 12px; border-radius: 50%; background: #cbd5e1; }
    .cp-demo-dot:nth-child(1) { background: #ef4444; }
    .cp-demo-dot:nth-child(2) { background: #f59e0b; }
    .cp-demo-dot:nth-child(3) { background: #10b981; }
    .cp-demo-title { flex-grow: 1; text-align: center; font-size: 0.85rem; font-weight: 600; color: #64748b; margin-right: 48px; }

    .cp-demo-body { position: relative; padding: 24px; min-height: 550px; display: flex; flex-direction: column; background: #f8fafc; }
    @media (max-width: 768px) { .cp-demo-body { min-height: 600px; padding: 16px; } }
    
    .cp-fake-card { background: #ffffff; border-radius: 12px; border: 1px solid #e2e8f0; padding: 24px; position: relative; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    @media (max-width: 768px) { .cp-fake-card { padding: 16px; } }
    .cp-fake-ribbon { position: absolute; top: 16px; right: -32px; background: #3b82f6; color: white; font-size: 0.7rem; font-weight: bold; padding: 4px 32px; transform: rotate(45deg); }
    .cp-fake-header-tags { display: flex; gap: 8px; margin-bottom: 12px; flex-wrap: wrap; }
    .cp-fake-tag-blue { color: #3b82f6; background: #eff6ff; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; border: 1px solid #bfdbfe; }
    .cp-fake-tag-green { color: #10b981; background: #ecfdf5; padding: 4px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: bold; border: 1px solid #a7f3d0; }
    .cp-fake-title { font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 0 0 8px 0; }
    .cp-fake-desc { font-size: 0.85rem; color: #475569; margin: 0 0 12px 0; line-height: 1.4; }
    .cp-fake-icon-box { width: 64px; height: 64px; border-radius: 16px; background: linear-gradient(135deg, #3b82f6, #6366f1); display: flex; align-items: center; justify-content: center; color: white; margin-right: 20px; flex-shrink: 0; }
    @media (max-width: 768px) { .cp-fake-icon-box { display: none; } }
    .cp-fake-content-row { display: flex; align-items: center; }
    .cp-fake-tags-row { display: flex; gap: 8px; margin-bottom: 16px; flex-wrap: wrap; }
    .cp-fake-small-tag { font-size: 0.75rem; color: #64748b; display: flex; align-items: center; gap: 4px; }
    .cp-fake-actions { display: flex; align-items: center; justify-content: flex-end; gap: 12px; margin-top: 16px; border-top: 1px solid #e2e8f0; padding-top: 16px; flex-wrap: wrap; }
    .cp-fake-btn-ai { background: linear-gradient(135deg, #3b82f6, #8b5cf6); color: white; padding: 8px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; display: flex; align-items: center; gap: 6px; transition: transform 0.1s; }
    .cp-fake-btn-ai.active { transform: scale(0.95); }
    .cp-fake-btn-crm { background: #0f172a; color: white; padding: 8px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; }
    .cp-fake-btn-outline { background: white; color: #3b82f6; border: 1px solid #bfdbfe; padding: 8px 16px; border-radius: 6px; font-size: 0.85rem; font-weight: 600; }
    
    .cp-cursor { position: absolute; width: 28px; height: 28px; z-index: 50; top: 90%; left: 90%; transition: all 1s cubic-bezier(0.4, 0, 0.2, 1); pointer-events: none; filter: drop-shadow(0 4px 6px rgba(0,0,0,0.3)); }
    
    .cp-demo-modal-overlay { position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 100; display: none; align-items: center; justify-content: center; padding: 20px; }
    .cp-demo-modal { background: white; border-radius: 12px; width: 100%; max-width: 600px; box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25); display: flex; flex-direction: column; overflow: hidden; }
    .cp-demo-modal-header { padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-weight: 600; color: #0f172a; display: flex; justify-content: space-between; align-items: center; font-size: 0.9rem; background: #f8fafc; }
    .cp-demo-modal-body { padding: 24px; display: flex; flex-direction: column; gap: 16px; max-height: 500px; overflow-y: auto; background: white; }
    
    .cp-chat-bubble-container { display: flex; align-items: flex-start; gap: 16px; }
    .cp-chat-icon { width: 40px; height: 40px; border-radius: 12px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .cp-chat-bubble { padding: 14px 18px; border-radius: 12px; line-height: 1.5; font-size: 0.95rem; width: 100%; }

    /* Typing Indicator */
    .typing-indicator { display: flex; gap: 6px; padding: 12px 16px; background: #f1f5f9; border-radius: 20px; width: fit-content; align-items: center; }
    .typing-indicator span { display: block; width: 6px; height: 6px; background: #94a3b8; border-radius: 50%; animation: blink 1.4s infinite both; }
    .typing-indicator span:nth-child(2) { animation-delay: 0.2s; }
    .typing-indicator span:nth-child(3) { animation-delay: 0.4s; }
    @keyframes blink { 0%, 100% { opacity: 0.2; transform: translateY(0); } 50% { opacity: 1; transform: translateY(-2px); } }

    /* Layout Grids (Features) */
    .cp-features {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
        margin-bottom: 60px;
    }
    @media (min-width: 768px) {
        .cp-features { grid-template-columns: repeat(3, 1fr); }
    }
    .cp-feature-card {
        padding: 32px;
        border-radius: 24px;
        border: 1px solid rgba(255,255,255,0.1);
        box-shadow: 0 15px 35px -5px rgba(0,0,0,0.1);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        color: #ffffff;
    }
    .cp-feature-card.c-blue { background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); }
    .cp-feature-card.c-purple { background: linear-gradient(135deg, #4c1d95 0%, #8b5cf6 100%); }
    .cp-feature-card.c-emerald { background: linear-gradient(135deg, #064e3b 0%, #10b981 100%); }

    .cp-feature-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 25px 50px -12px rgba(0,0,0,0.25);
        border-color: rgba(255,255,255,0.4);
    }
    .cp-feature-icon {
        width: 56px; height: 56px;
        border-radius: 16px;
        background: rgba(255, 255, 255, 0.15);
        color: #ffffff;
        display: flex; align-items: center; justify-content: center;
        margin-bottom: 24px;
        box-shadow: inset 0 2px 4px rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
    }
    .cp-feature-icon svg { width: 28px; height: 28px; }
    .cp-feature-card h3 { font-size: 1.3rem; font-weight: 800; margin: 0 0 12px 0; color: #ffffff; letter-spacing: -0.02em; }
    .cp-feature-card p { margin: 0; color: rgba(255,255,255,0.85); font-size: 1rem; line-height: 1.6; }

    /* Pricing Card */
    .cp-card {
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
        border: 1px solid #e2e8f0;
        overflow: hidden;
        margin-bottom: 40px;
        position: relative;
    }
    .cp-card-accent {
        position: absolute; top: 0; left: 0; width: 100%; height: 6px;
        background: linear-gradient(90deg, #38bdf8, #2563eb);
    }
    .cp-card-body { padding: 48px; display: flex; flex-direction: column; gap: 32px; }
    @media (min-width: 768px) {
        .cp-card-body { flex-direction: row; align-items: center; justify-content: space-between; }
    }
    .cp-card-left { flex: 1; }
    .cp-card-right { flex: 1; text-align: center; background: #f8fafc; padding: 40px; border-radius: 16px; border: 1px solid #e2e8f0; }
    
    .cp-card-title { font-size: 1.8rem; font-weight: 900; color: #0f172a; margin: 0 0 24px 0; }
    .cp-card-features { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 16px; }
    .cp-card-features li { display: flex; align-items: center; gap: 12px; font-size: 1.1rem; color: #334155; font-weight: 500; }
    .cp-card-features svg { color: #10b981; flex-shrink: 0; }
    
    .cp-price { font-size: 4rem; font-weight: 900; color: #0f172a; display: flex; justify-content: center; align-items: baseline; margin-bottom: 8px; letter-spacing: -1px; }
    .cp-price span { font-size: 1.25rem; color: #64748b; font-weight: 500; margin-left: 8px; letter-spacing: 0; }
    
    .cp-btn-primary {
        display: inline-block; width: 100%; background: #2563eb; color: #ffffff; font-weight: 800; font-size: 1.15rem; padding: 18px 32px; border-radius: 12px; text-decoration: none; box-shadow: 0 8px 20px rgba(37, 99, 235, 0.3); transition: all 0.2s; text-align: center; border: none; cursor: pointer; margin-top: 16px;
    }
    .cp-btn-primary:hover { background: #1d4ed8; transform: translateY(-3px); box-shadow: 0 12px 24px rgba(37, 99, 235, 0.4); }

    .cp-legal { display: flex; align-items: center; justify-content: center; gap: 8px; margin-top: 16px; color: #64748b; font-size: 0.85rem; font-weight: 500; }
</style>

<div class="cp-page-wrapper">
    <div class="cp-container">
        
        <!-- Hero Section -->
        <div class="cp-hero">
            <div class="cp-badge-top">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="8.5" cy="7" r="4"></circle><polyline points="17 11 19 13 23 9"></polyline></svg>
                La IA para ventas corporativas
            </div>
            <h1 class="cp-title">Account Intelligence Automática<br>directamente en el <span>Copiloto AI</span></h1>
            <p class="cp-subtitle">El Copiloto de Ventas cruza el BORME, Subvenciones y Contratos Públicos para generarte el guion perfecto en segundos. No más horas perdidas investigando en Google.</p>
        </div>

        <!-- AHA Moment Animation -->
        <div class="cp-demo-wrapper">
            <div class="cp-demo-header">
                <div class="cp-demo-dots">
                    <div class="cp-demo-dot"></div><div class="cp-demo-dot"></div><div class="cp-demo-dot"></div>
                </div>
                <div class="cp-demo-title">Generador de Inteligencia de Cuentas</div>
            </div>
            <div class="cp-demo-body" id="demo-body-area">
                
                <div id="demo-company-card" class="cp-fake-card">
                    <div class="cp-fake-ribbon">VETERANA</div>
                    <div class="cp-fake-content-row">
                        <div class="cp-fake-icon-box">
                            <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 22h14a2 2 0 0 0 2-2V7l-5-5H6a2 2 0 0 0-2 2v16z"></path><path d="M14 2v4a2 2 0 0 0 2 2h4"></path><path d="M8 18h8"></path><path d="M8 14h8"></path><path d="M8 10h8"></path></svg>
                        </div>
                        <div style="flex-grow: 1;">
                            <div class="cp-fake-header-tags">
                                <div class="cp-fake-tag-blue">FICHA DE EMPRESA</div>
                                <div class="cp-fake-tag-green">DATOS OFICIALES</div>
                            </div>
                            <h3 class="cp-fake-title">TECH SOLUTIONS IBERIA SL - CIF B12345678</h3>
                            <p class="cp-fake-desc">Expertos en turismo y comercio, ofreciendo soluciones personalizadas para administraciones y entidades.</p>
                            <div class="cp-fake-tags-row">
                                <span class="cp-fake-tag-green" style="background: white;">ACTIVA</span>
                                <span class="cp-fake-small-tag">📍 Madrid</span>
                                <span class="cp-fake-small-tag">🗓 Última act: Hoy</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="cp-fake-actions">
                        <div id="demo-ai-btn" class="cp-fake-btn-ai">✨ Preparar llamada con IA</div>
                        <div class="cp-fake-btn-crm">Enviar a CRM</div>
                        <div class="cp-fake-btn-outline">Descargar</div>
                    </div>
                </div>
                
                <!-- Sticky Bar Demo -->
                <div id="demo-sticky-bar" class="cp-fake-sticky-bar" style="position: absolute; bottom: 0; left: 0; right: 0; background: white; padding: 12px 24px; border-top: 1px solid #e2e8f0; display: none; align-items: center; justify-content: center; gap: 12px; box-shadow: 0 -4px 12px rgba(0,0,0,0.05); border-radius: 0 0 12px 12px;">
                    <div style="background: #f3e8ff; color: #9333ea; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem;">✨</div>
                    <span style="font-weight: 700; color: #0f172a; font-size: 0.95rem; display: none; @media(min-width: 768px){display: block;}">Copiloto de Ventas</span>
                    <div style="border: none; background: #f8fafc; padding: 10px 16px; border-radius: 100px; font-size: 0.9rem; color: #94a3b8; width: 220px; text-align: left;">Introduce un CIF válido...</div>
                    <div id="demo-sticky-btn" style="background: linear-gradient(135deg, #9333ea, #d946ef); color: white; border-radius: 100px; padding: 10px 24px; font-weight: 700; font-size: 0.95rem; display: flex; align-items: center; gap: 6px;">
                        Generar Guion
                    </div>
                </div>

                <svg id="demo-cursor" class="cp-cursor" viewBox="0 0 24 24" fill="#000000" stroke="#ffffff" stroke-width="2"><path d="M3 3l7 19 2-8 8-2L3 3z"/></svg>

                <div id="demo-modal-overlay" class="cp-demo-modal-overlay">
                    <div class="cp-demo-modal">
                        <div class="cp-demo-modal-header">
                            <div style="display: flex; gap: 12px; align-items: center;">
                                <div style="background: #f3e8ff; padding: 6px; border-radius: 8px; color: #8b5cf6; display: flex;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path></svg>
                                </div>
                                <div style="line-height: 1.2;">
                                    <div style="font-weight: 800; font-size: 1.05rem; color: #0f172a;">Copiloto de Ventas</div>
                                    <div style="font-size: 0.8rem; color: #64748b; font-weight: normal;">Guion ultra-personalizado con IA.</div>
                                </div>
                            </div>
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </div>

                        <!-- Form Step -->
                        <div class="cp-demo-modal-body" id="demo-modal-form" style="display: flex;">
                            <div style="background: #f0fdf4; border: 1px solid #bbf7d0; padding: 16px; border-radius: 8px; font-size: 0.85rem; color: #166534; line-height: 1.5;">
                                <strong style="color: #14532d;">Cómo funciona:</strong> Nuestra IA analiza en tiempo real el BORME, las subvenciones, licitaciones y datos del registro mercantil de esta empresa para generarte un guion de llamada y una secuencia de correos con la máxima probabilidad de conversión.
                            </div>
                            <div style="margin-top: 8px;">
                                <label style="display: block; font-weight: 700; color: #334155; margin-bottom: 8px; font-size: 0.95rem;">¿Qué producto o servicio ofreces?</label>
                                <div style="border: 1px solid #cbd5e1; border-radius: 8px; padding: 12px 16px; color: #0f172a; font-size: 0.95rem; display: flex; align-items: center; background: white;">
                                    <span id="demo-form-input-text" style="color: #94a3b8;">Ej: Software ERP, Servicios SEO...</span>
                                    <span id="demo-form-cursor-line" style="display: none; width: 2px; height: 16px; background: #2563eb; margin-left: 2px; animation: blink 1s infinite;"></span>
                                </div>
                            </div>
                            <div id="demo-form-btn" style="background: #4f46e5; color: white; padding: 12px; border-radius: 8px; text-align: center; font-weight: 700; font-size: 0.95rem; margin-top: 8px; transition: transform 0.1s; display: flex; justify-content: center; align-items: center; gap: 8px;">
                                ✨ Generar Guion de Ventas
                            </div>
                        </div>

                        <!-- Chat Step -->
                        <div class="cp-demo-modal-body" id="demo-modal-chat" style="display: none;">
                            <div id="typing-indicator" class="typing-indicator" style="display: none;">
                                <span></span><span></span><span></span>
                            </div>
                            
                            <!-- Score Block (NEW) -->
                            <div id="chat-score-block" style="background: #fff; border: 1px solid #e2e8f0; padding: 16px; border-radius: 8px; margin-bottom: 16px; display: none; align-items: center; gap: 16px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
                                <div style="text-align: center; min-width: 80px; border-right: 1px solid #e2e8f0; padding-right: 16px;">
                                    <div style="font-size: 2.5rem; font-weight: 800; color: #10b981; line-height: 1; text-shadow: 0 1px 2px rgba(0,0,0,0.1);">92</div>
                                    <div style="font-size: 0.7rem; color: #64748b; font-weight: 800; text-transform: uppercase; margin-top: 6px;">Score</div>
                                </div>
                                <div style="flex-grow: 1;">
                                    <h4 style="margin: 0 0 6px 0; color: #0f172a; font-size: 0.95rem;">💡 Análisis del CRO</h4>
                                    <div style="color: #475569; font-size: 0.9rem; line-height: 1.5; font-style: italic;">
                                        "Prospecto excelente. Acaban de levantar fondos y el rol encaja perfecto. Ve a por ellos con un enfoque de crecimiento."
                                    </div>
                                </div>
                            </div>

                            <!-- Trigger Bubble -->
                            <div id="chat-bubble-1" class="cp-chat-bubble-container" style="display: none;">
                                <div class="cp-chat-icon" style="background: #e0f2fe; color: #0284c7;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                                </div>
                                <div class="cp-chat-bubble" style="background: #f8fafc; border: 1px solid #e2e8f0; color: #334155;">
                                    <strong style="color: #0f172a; display: block; margin-bottom: 8px;">🎯 Datos cruzados detectados en tiempo real:</strong>
                                    <ul style="margin: 0; padding-left: 20px; margin-bottom: 12px; color: #475569;">
                                        <li style="margin-bottom: 4px;"><strong style="color: #0369a1;">BORME (hace 4 días):</strong> Nombramiento de nuevo 'Director de Expansión Internacional'.</li>
                                        <li><strong style="color: #15803d;">Subvenciones (hace 10 días):</strong> 120.000€ concedidos del programa 'Kit Consulting B2B'.</li>
                                    </ul>
                                    <span style="font-weight: 600; color: #1e293b;">Conclusión IA:</span> Están inyectando dinero para abrir mercados y tienen presupuesto fresco para servicios B2B.
                                </div>
                            </div>

                            <!-- Response Bubble -->
                            <div id="chat-bubble-2" class="cp-chat-bubble-container" style="display: none;">
                                <div class="cp-chat-icon" style="background: #dbeafe; color: #2563eb;">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                </div>
                                <div class="cp-chat-bubble" style="background: #eff6ff; border: 1px solid #bfdbfe; color: #1e3a8a; font-style: italic;" id="chat-text-2">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Features Grid -->
        <div class="cp-features">
            <div class="cp-feature-card c-blue">
                <div class="cp-feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path><polyline points="7.5 4.21 12 6.81 16.5 4.21"></polyline><polyline points="7.5 19.79 7.5 14.6 3 12"></polyline><polyline points="21 12 16.5 14.6 16.5 19.79"></polyline><polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline><line x1="12" y1="22.08" x2="12" y2="12"></line></svg>
                </div>
                <h3>Inteligencia Ilimitada</h3>
                <p>Genera dossiers comerciales completos para todas tus cuentas sin preocuparte por créditos.</p>
            </div>
            <div class="cp-feature-card c-purple">
                <div class="cp-feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                </div>
                <h3>Guiones Cold Call</h3>
                <p>Rompehielos hiper-personalizados para llamadas frías y mensajes de LinkedIn.</p>
            </div>
            <div class="cp-feature-card c-emerald">
                <div class="cp-feature-icon">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>
                </div>
                <h3>Riesgo Financiero</h3>
                <p>Conoce al instante si tu prospecto tiene incidencias RAI o deudas impagadas antes de perder tiempo en venderle.</p>
            </div>
        </div>

        <!-- Integration Options (Checkout) -->
        <div style="text-align: center; margin-bottom: 20px;">
            <div style="display: inline-flex; align-items: center; gap: 8px; background: #f8fafc; padding: 8px 16px; border-radius: 99px; font-size: 0.95rem; color: #334155; font-weight: 600; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                ⭐ Ya usado por +50 equipos de ventas B2B en España
            </div>
        </div>
        <div class="cp-card">
            <div class="cp-card-accent"></div>
            <div class="cp-card-body">
                <div class="cp-card-left">
                    <h2 class="cp-card-title">Suscripción Copiloto de Ventas</h2>
                    <ul class="cp-card-features">
                        <li><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Uso ilimitado del Copiloto AI</li>
                        <li><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Secuencias de 3 correos en frío</li>
                        <li><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Análisis de Score CRO Comercial</li>
                        <li><svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Plugin para Fichas de Empresa</li>
                    </ul>
                </div>
                <div class="cp-card-right">
                    <div class="cp-price"><?= esc(number_format($copilotPrice ?? 39, 0, ',', '.')) ?>€<span style="font-size: 1rem; color: #94a3b8; margin: 0 4px 0 2px;">+IVA</span><span>/mes</span></div>
                    <p style="color: #64748b; font-size: 1.05rem; margin-bottom: 0;">Cierra una sola reunión y recupera la inversión de todo el año.</p>
                    
                    <form action="<?= site_url('billing/checkout') ?>" method="POST">
                        <?= csrf_field() ?>
                        <input type="hidden" name="plan" value="copiloto_ventas">
                        <input type="hidden" name="period" value="monthly">
                        <input type="hidden" name="payment_method" value="stripe">
                        <input type="hidden" name="source" value="copilot">
                        <button type="submit" class="cp-btn-primary">Empezar a prospectar</button>
                    </form>
                    
                    <div class="cp-legal">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        100% Legal y Cumple con RGPD
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

    </main>
    <?= view('partials/footer') ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cursor = document.getElementById('demo-cursor');
            const aiBtn = document.getElementById('demo-ai-btn');
            const stickyBtn = document.getElementById('demo-sticky-btn');
            const companyCard = document.getElementById('demo-company-card');
            const stickyBar = document.getElementById('demo-sticky-bar');
            const overlay = document.getElementById('demo-modal-overlay');
            const bodyArea = document.getElementById('demo-body-area');
            
            const scoreBlock = document.getElementById('chat-score-block');
            const bubble1 = document.getElementById('chat-bubble-1');
            const bubble2 = document.getElementById('chat-bubble-2');
            const indicator = document.getElementById('typing-indicator');
            const text2 = document.getElementById('chat-text-2');
            
            const modalForm = document.getElementById('demo-modal-form');
            const modalChat = document.getElementById('demo-modal-chat');
            const formInputText = document.getElementById('demo-form-input-text');
            const formCursorLine = document.getElementById('demo-form-cursor-line');
            const formBtn = document.getElementById('demo-form-btn');
            
            const productText = "Servicios de externalización comercial B2B";
            const fullText = '"Hola Carlos, soy [Nombre] de [Tu Empresa]. Enhorabuena por tu nuevo rol como Director de Expansión. Sé que con la reciente inyección de 120k€ del Kit Consulting estáis a tope buscando escalar ventas en nuevos mercados.\\n\\n¿Os habéis planteado automatizar la captación de leads internacionales para no quemar a vuestro equipo actual? Solo pido 5 minutos el martes para enseñarte cómo lo hacen nuestros clientes."';
            
            let useSticky = false;
            
            function runAnimation() {
                // Reset states
                cursor.style.transition = 'none';
                cursor.style.top = '90%';
                cursor.style.left = '90%';
                
                // Toggle visibility based on cycle
                companyCard.style.display = useSticky ? 'none' : 'block';
                stickyBar.style.display = useSticky ? 'flex' : 'none';
                
                aiBtn.classList.remove('active');
                stickyBtn.style.transform = 'scale(1)';
                formBtn.classList.remove('active');
                overlay.style.display = 'none';
                
                modalForm.style.display = 'flex';
                modalChat.style.display = 'none';
                formInputText.innerHTML = 'Ej: Software ERP, Servicios SEO...';
                formInputText.style.color = '#94a3b8';
                formCursorLine.style.display = 'none';
                
                scoreBlock.style.display = 'none';
                bubble1.style.display = 'none';
                bubble2.style.display = 'none';
                text2.innerHTML = '';
                
                // Determine target button
                const targetBtn = useSticky ? stickyBtn : aiBtn;
                
                // 1. Move cursor to target button
                setTimeout(() => {
                    cursor.style.transition = 'all 1s cubic-bezier(0.4, 0, 0.2, 1)';
                    const bodyRect = bodyArea.getBoundingClientRect();
                    const btnRect = targetBtn.getBoundingClientRect();
                    
                    cursor.style.left = (btnRect.left - bodyRect.left + (btnRect.width / 2)) + 'px';
                    cursor.style.top = (btnRect.top - bodyRect.top + (btnRect.height / 2)) + 'px';
                    
                    // 2. Click target button
                    setTimeout(() => {
                        if (useSticky) {
                            stickyBtn.style.transform = 'scale(0.95)';
                            setTimeout(() => stickyBtn.style.transform = 'scale(1)', 150);
                        } else {
                            aiBtn.classList.add('active');
                            setTimeout(() => aiBtn.classList.remove('active'), 150);
                        }
                        
                        // 3. Open Modal with Form
                        setTimeout(() => {
                            overlay.style.display = 'flex';
                            
                            // 3.1 Move cursor to input and type
                            setTimeout(() => {
                                formInputText.innerHTML = '';
                                formInputText.style.color = '#0f172a';
                                formCursorLine.style.display = 'block';
                                
                                let p = 0;
                                function typeProduct() {
                                    if (p < productText.length) {
                                        formInputText.innerHTML += productText.charAt(p);
                                        p++;
                                        setTimeout(typeProduct, 30);
                                    } else {
                                        // 3.2 Move cursor to Generate Button
                                        setTimeout(() => {
                                            const formBtnRect = formBtn.getBoundingClientRect();
                                            
                                            // Ensure cursor moves properly over the overlay elements
                                            cursor.style.transition = 'all 0.8s cubic-bezier(0.4, 0, 0.2, 1)';
                                            cursor.style.left = (formBtnRect.left - bodyRect.left + (formBtnRect.width / 2)) + 'px';
                                            cursor.style.top = (formBtnRect.top - bodyRect.top + (formBtnRect.height / 2)) + 'px';
                                            
                                            setTimeout(() => {
                                                // 3.3 Click Generate
                                                formBtn.classList.add('active');
                                                setTimeout(() => formBtn.classList.remove('active'), 150);
                                                
                                                setTimeout(() => {
                                                    // 4. Switch to Chat Step
                                                    modalForm.style.display = 'none';
                                                    modalChat.style.display = 'flex';
                                                    
                                                    // Move cursor away
                                                    cursor.style.top = '100%';
                                                    cursor.style.left = '80%';
                                                    
                                                    // 4.1 Start Chat Sequence
                                                    indicator.style.display = 'flex';
                                                    scoreBlock.parentNode.insertBefore(indicator, scoreBlock);
                                                    
                                                    setTimeout(() => {
                                                        indicator.style.display = 'none';
                                                        scoreBlock.style.display = 'flex';
                                                        indicator.style.display = 'flex';
                                                        bubble1.parentNode.insertBefore(indicator, bubble1);
                                                        
                                                        setTimeout(() => {
                                                            bubble1.style.display = 'flex';
                                                            bubble1.parentNode.insertBefore(indicator, bubble2);
                                                            
                                                            setTimeout(() => {
                                                                indicator.style.display = 'none';
                                                                bubble2.style.display = 'flex';
                                                            
                                                                let i = 0;
                                                                function typeWriter() {
                                                                    if (i < fullText.length) {
                                                                        let char = fullText.charAt(i);
                                                                        if (char === '\\n') {
                                                                            text2.innerHTML += '<br>';
                                                                        } else {
                                                                            text2.innerHTML += char;
                                                                        }
                                                                        i++;
                                                                        setTimeout(typeWriter, 12);
                                                                    } else {
                                                                        // 5. Restart everything after 6 seconds
                                                                        useSticky = !useSticky;
                                                                        setTimeout(runAnimation, 6000);
                                                                    }
                                                                }
                                                                typeWriter();
                                                            }, 1500);
                                                        }, 1200);
                                                    }, 1000); // Wait for score block to show
                                                    
                                                }, 300); // Wait after clicking generate
                                                
                                            }, 1000); // Wait for cursor to reach button
                                        }, 600); // Wait after typing finished
                                    }
                                }
                                typeProduct();
                                
                            }, 500); // Wait after modal opens
                            
                        }, 400); // Wait after click to show modal
                        
                    }, 1000); // Wait for cursor to move
                }, 500); // Wait before starting cursor movement
            }
            
            // Start when element is in view
            let observer = new IntersectionObserver((entries) => {
                if (entries[0].isIntersecting) {
                    runAnimation();
                    observer.disconnect();
                }
            });
            observer.observe(document.querySelector('.cp-demo-wrapper'));
        });
    </script>
</body>
</html>
