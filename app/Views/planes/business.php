<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title' => 'Infraestructura API de Datos Mercantiles para ERP | Plan Business',
        'excerptText' => 'Tarifas y planes de nuestra API Enterprise para extraer datos masivos del Registro Mercantil, conexión ERP y Webhooks en tiempo real.'
    ]) ?>
    <link rel="stylesheet" href="<?= base_url('public/css/home.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/home-mobile.css') ?>?v=<?= time() ?>" media="screen and (max-width: 768px)">
    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Product",
      "name": "Infraestructura API Enterprise - Plan Business",
      "description": "Tarifas y planes para alto volumen. Conecta tu ERP al Registro Mercantil, extrae datos B2B masivos y recibe Webhooks de notificaciones en tiempo real sin límites.",
      "brand": {
        "@type": "Brand",
        "name": "APIEmpresas"
      },
      "offers": {
        "@type": "Offer",
        "url": "<?= current_url() ?>",
        "priceCurrency": "EUR",
        "price": "49",
        "priceValidUntil": "2028-12-31",
        "availability": "https://schema.org/InStock"
      }
    }
    </script>
</head>
<body>
    <?= view('partials/header') ?>

<style>
    .plan-page-wrapper {
        background-color: #f8fafc;
        min-height: 100vh;
        padding: 60px 20px;
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif;
    }
    .plan-container { max-width: 1000px; margin: 0 auto; }
    .plan-hero { text-align: center; margin-bottom: 40px; }
    .plan-badge-top { display: inline-flex; align-items: center; gap: 8px; background: #ecfdf5; color: #059669; padding: 6px 16px; border-radius: 100px; font-size: 0.9rem; font-weight: 700; margin-bottom: 24px; border: 1px solid #a7f3d0; }
    .plan-title { font-size: 3.5rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0 0 20px 0; line-height: 1.1; }
    .plan-title span { background: linear-gradient(135deg, #059669 0%, #10b981 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .plan-subtitle { font-size: 1.3rem; color: #475569; max-width: 700px; margin: 0 auto; line-height: 1.6; }
    
    .plan-demo-wrapper { margin: 40px auto 60px auto; max-width: 800px; background: #ffffff; border-radius: 12px; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden; padding: 40px; }
    
    /* Terminal Animation CSS */
    .terminal-window { background: #0f172a; width: 100%; font-family: 'Fira Code', monospace; text-align: left; border-radius: 12px; overflow: hidden; }
    .terminal-header { background: #1e293b; padding: 12px 16px; display: flex; gap: 8px; align-items: center; border-bottom: 1px solid #334155; }
    .terminal-dot { width: 12px; height: 12px; border-radius: 50%; }
    .terminal-dot:nth-child(1) { background: #ef4444; }
    .terminal-dot:nth-child(2) { background: #eab308; }
    .terminal-dot:nth-child(3) { background: #22c55e; }
    .terminal-title { color: #94a3b8; font-size: 0.8rem; margin-left: auto; margin-right: auto; }
    .terminal-body { padding: 24px; color: #a5b4fc; font-size: 0.9rem; line-height: 1.5; min-height: 350px; position: relative; }
    
    .prompt { color: #22c55e; font-weight: bold; }
    .command { color: #f8fafc; }
    .json-key { color: #38bdf8; }
    .json-string { color: #a3e635; }
    .json-number { color: #fb923c; }
    @keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
    
    .terminal-tabs { background: #1e293b; padding: 0 16px; display: flex; gap: 4px; border-bottom: 1px solid #334155; overflow-x: auto; scrollbar-width: none; }
    .terminal-tabs::-webkit-scrollbar { display: none; }
    .term-tab { padding: 10px 16px; color: #94a3b8; font-size: 0.85rem; font-family: 'Inter', sans-serif; cursor: pointer; border-bottom: 2px solid transparent; white-space: nowrap; transition: all 0.2s; }
    .term-tab:hover { color: #cbd5e1; }
    .term-tab.active { color: #38bdf8; border-bottom-color: #38bdf8; background: rgba(255,255,255,0.03); }
    
    .plan-features { display: grid; grid-template-columns: 1fr; gap: 24px; margin-bottom: 60px; text-align: left; }
    @media (min-width: 768px) { .plan-features { grid-template-columns: repeat(3, 1fr); } }
    .plan-feature-card { background: white; padding: 24px; border-radius: 16px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    .plan-feature-icon { width: 48px; height: 48px; border-radius: 12px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; margin-bottom: 16px; }
    .plan-feature-card h3 { font-size: 1.2rem; font-weight: 800; margin: 0 0 8px 0; color: #0f172a; }
    .plan-feature-card p { margin: 0; color: #475569; font-size: 0.95rem; line-height: 1.5; }
    
    .plan-card { background: linear-gradient(135deg, #064e3b 0%, #047857 100%); border-radius: 24px; box-shadow: 0 20px 40px -15px rgba(4, 120, 87, 0.3); border: 1px solid rgba(255, 255, 255, 0.1); overflow: hidden; margin-bottom: 40px; position: relative; padding: 60px 40px; text-align: center; color: white; }
    .plan-card-bg { position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.4; background-image: radial-gradient(#059669 1px, transparent 1px); background-size: 20px 20px; pointer-events: none; }
    .plan-card h2 { font-size: 2.2rem; font-weight: 900; margin-bottom: 16px; color: #ffffff; letter-spacing: -0.02em; position: relative; z-index: 2; }
    .plan-card p { color: #a7f3d0; margin-bottom: 32px; font-size: 1.15rem; max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.6; position: relative; z-index: 2; }
    
    .btn-primary-cta { display: inline-block; background: #f59e0b; color: #ffffff; font-weight: 800; font-size: 1.1rem; padding: 18px 40px; border-radius: 100px; text-decoration: none; box-shadow: 0 10px 25px -5px rgba(245, 158, 11, 0.5); transition: all 0.3s; position: relative; z-index: 2; border: 1px solid #fbbf24; }
    .btn-primary-cta:hover { background: #d97706; transform: translateY(-3px); box-shadow: 0 15px 35px -5px rgba(245, 158, 11, 0.6); }
    
    .alternative-plans { text-align: center; margin-top: -10px; margin-bottom: 40px; padding: 20px; color: #64748b; font-size: 0.95rem; line-height: 1.6; border-top: 1px solid #e2e8f0; }
    .alternative-plans strong { color: #475569; }
    .alternative-plans a { color: #3b82f6; text-decoration: none; font-weight: 600; }
    .alternative-plans a:hover { text-decoration: underline; color: #2563eb; }
</style>

<div class="plan-page-wrapper">
    <div class="plan-container">
        
        <div class="plan-hero">
            <div class="plan-badge-top">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                Plan Business (Escala)
            </div>
            <h1 class="plan-title">Infraestructura API <span>Enterprise</span></h1>
            <p class="plan-subtitle">Tarifas y planes para alto volumen. Conecta tu ERP al Registro Mercantil, extrae datos B2B masivos y recibe Webhooks de notificaciones en tiempo real sin límites.</p>
        </div>

        <div class="plan-demo-wrapper" style="background: #f8fafc;">
            <div class="terminal-window">
                <div class="terminal-header">
                    <div class="terminal-dot"></div>
                    <div class="terminal-dot"></div>
                    <div class="terminal-dot"></div>
                    <div class="terminal-title">bash - api-empresas - 80x24</div>
                </div>
                <div class="terminal-tabs">
                    <div class="term-tab active" data-tab="0">Match B2B</div>
                    <div class="term-tab" data-tab="1">Preparación de Contacto</div>
                    <div class="term-tab" data-tab="2">Enriquecimiento Bulk</div>
                </div>
                <div class="terminal-body">
                    <div><span class="prompt">$</span> <span class="command" id="term-cmd"></span><span id="term-cursor" style="display:inline-block; width:8px; height:15px; background: white; animation: blink 1s infinite; vertical-align: middle;"></span></div>
                    <div id="term-response" style="display: none; margin-top: 16px;">
                    </div>
                    <div id="term-prompt-2" style="display: none; margin-top: 16px;"><span class="prompt">$</span> <span id="term-cursor-2" style="display:inline-block; width:8px; height:15px; background: white; animation: blink 1s infinite; vertical-align: middle;"></span></div>
                </div>
            </div>
        </div>

        <div class="plan-features">
            <div class="plan-feature-card">
                <div class="plan-feature-icon" style="background: #ecfdf5; color: #059669;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line><line x1="11" y1="8" x2="11" y2="14"></line><line x1="8" y1="11" x2="14" y2="11"></line></svg>
                </div>
                <h3>Match B2B Inteligente</h3>
                <p>Encuentra empresas gemelas. Pasa el CIF de tu mejor cliente y nuestra IA te devolverá una lista de prospectos idénticos con alta probabilidad de cierre.</p>
            </div>
            
            <div class="plan-feature-card">
                <div class="plan-feature-icon" style="background: #eff6ff; color: #2563eb;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="2" y="3" width="20" height="14" rx="2" ry="2"></rect><line x1="8" y1="21" x2="16" y2="21"></line><line x1="12" y1="17" x2="12" y2="21"></line></svg>
                </div>
                <h3>Copiloto AI</h3>
                <p>Uso extensivo de nuestra Inteligencia Artificial para preparar reuniones, generar guiones comerciales e investigar cuentas clave.</p>
            </div>
            
            <div class="plan-feature-card">
                <div class="plan-feature-icon" style="background: #fdf2f8; color: #db2777;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                </div>
                <h3>10.000 Consultas/mes</h3>
                <p>La capacidad necesaria para equipos enteros y aplicaciones de alto tráfico. Además de acceso prioritario a soporte (Slack Connect).</p>
            </div>
        </div>

        <style>
            .endpoints-section {
                margin-bottom: 60px;
                background: white;
                padding: 40px;
                border-radius: 24px;
                border: 1px solid #e2e8f0;
                box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            }
            .endpoints-title {
                font-size: 1.8rem;
                font-weight: 800;
                color: #0f172a;
                margin-bottom: 8px;
                text-align: center;
            }
            .endpoints-subtitle {
                text-align: center;
                color: #475569;
                margin-bottom: 24px;
                font-size: 1.05rem;
            }
            .endpoints-grid {
                display: grid;
                grid-template-columns: 1fr;
                gap: 16px;
                margin-bottom: 24px;
            }
            @media (min-width: 768px) {
                .endpoints-grid {
                    grid-template-columns: repeat(2, 1fr);
                }
            }
            .endpoint-card {
                padding: 20px;
                border-radius: 12px;
                background: #f8fafc;
                border: 1px solid #e2e8f0;
                text-align: left;
            }
            .endpoint-route {
                font-family: 'Fira Code', monospace;
                font-weight: bold;
                color: #059669;
                margin-bottom: 8px;
                font-size: 1.05rem;
            }
            .endpoint-desc {
                margin: 0;
                color: #475569;
                font-size: 0.95rem;
                line-height: 1.5;
            }
            .doc-link {
                color: #059669;
                font-weight: 600;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-size: 1.05rem;
                padding: 12px 24px;
                background: #ecfdf5;
                border-radius: 100px;
                transition: all 0.2s;
            }
            .doc-link:hover {
                background: #d1fae5;
                color: #047857;
            }
        </style>

        <div class="endpoints-section">
            <h3 class="endpoints-title">Endpoints incluidos en el Plan Business</h3>
            <p class="endpoints-subtitle">Además de todos los endpoints de los planes Free y Pro, desbloqueas:</p>
            <div class="endpoints-grid">
                <div class="endpoint-card">
                    <div class="endpoint-route">GET /companies/match</div>
                    <p class="endpoint-desc">Encuentra empresas gemelas y negocios similares a tus mejores clientes usando nuestra IA para prospección comercial automatizada.</p>
                </div>
                <div class="endpoint-card">
                    <div class="endpoint-route">GET /companies/contact-prep</div>
                    <p class="endpoint-desc">Genera tácticas de ventas, enfoques y borradores de emails altamente personalizados por IA antes de contactar a un prospecto.</p>
                </div>
                <div class="endpoint-card" style="grid-column: 1 / -1; max-width: 600px; margin: 0 auto; width: 100%;">
                    <div class="endpoint-route">POST /companies/batch</div>
                    <p class="endpoint-desc">Enriquece y procesa el estado de múltiples empresas (por lotes) en una sola llamada para actualizar tu base de datos rápidamente.</p>
                </div>
            </div>
            <div style="text-align: center; margin-top: 16px; display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <a href="<?= site_url('documentation') ?>" class="doc-link">
                    Ver documentación de la API
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
                <a href="<?= base_url('public/docs/apiempresas_postman_business.json') ?>" class="doc-link" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;" download>
                    Descargar Postman Collection
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                </a>
            </div>
        </div>

        <div class="plan-card">
            <div class="plan-card-bg"></div>
            <h2>Escala tus operaciones hoy</h2>
            <p>Únete a los clientes que ya están revolucionando su inteligencia comercial.</p>
            <a href="<?= site_url('register?plan=business') ?>" class="btn-primary-cta">Empezar con Business (49€/mes + IVA)</a>
        </div>
        
        <div class="alternative-plans">
            <strong>¿No es exactamente lo que buscas?</strong><br>
            Si solo quieres hacer pruebas básicas, descubre el <a href="<?= site_url('planes/free') ?>">Plan Free</a>. Si tu volumen de peticiones es menor, el <a href="<?= site_url('planes/pro') ?>">Plan Pro</a> puede ser ideal.
        </div>

    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const cmdEl = document.getElementById('term-cmd');
        const cursor1 = document.getElementById('term-cursor');
        const responseEl = document.getElementById('term-response');
        const prompt2 = document.getElementById('term-prompt-2');
        const tabs = document.querySelectorAll('.term-tab');
        
        let typingTimeout;
        let animationTimeout;
        let currentTab = 0;
        
        const endpoints = [
            {
                cmd: "curl -X GET 'https://api.apiempresas.es/v1/companies/match?q=A08166803' \\ \n  -H 'Authorization: Bearer biz_key_999'",
                res: `{\n  <span class="json-key">"success"</span>: <span class="json-number">true</span>,\n  <span class="json-key">"data"</span>: {\n    <span class="json-key">"target"</span>: <span class="json-string">"A08166803"</span>,\n    <span class="json-key">"matches"</span>: [\n      {\n        <span class="json-key">"cif"</span>: <span class="json-string">"V46011425"</span>,\n        <span class="json-key">"name"</span>: <span class="json-string">"CONSUM S COOP"</span>,\n        <span class="json-key">"score"</span>: <span class="json-number">0.98</span>\n      },\n      {\n        <span class="json-key">"cif"</span>: <span class="json-string">"A60194776"</span>,\n        <span class="json-key">"name"</span>: <span class="json-string">"EROSKI SA"</span>,\n        <span class="json-key">"score"</span>: <span class="json-number">0.96</span>\n      }\n    ]\n  }\n}`
            },
            {
                cmd: "curl -X GET 'https://api.apiempresas.es/v1/companies/contact-prep?cif=A08166803' \\ \n  -H 'Authorization: Bearer biz_key_999'",
                res: `{\n  <span class="json-key">"success"</span>: <span class="json-number">true</span>,\n  <span class="json-key">"data"</span>: {\n    <span class="json-key">"tacticas"</span>: [\n      <span class="json-string">"Enfatizar escalabilidad técnica"</span>,\n      <span class="json-string">"Mencionar caso de éxito sectorial"</span>\n    ],\n    <span class="json-key">"guiones_email"</span>: <span class="json-string">"Hola [Nombre], noté que..."</span>,\n    <span class="json-key">"guiones_linkedin"</span>: <span class="json-string">"Hola, veo que lideráis..."</span>\n  }\n}`
            },
            {
                cmd: "curl -X POST 'https://api.apiempresas.es/v1/companies/batch' \\ \n  -H 'Authorization: Bearer biz_key_999' \\ \n  -d '{\"cifs\": [\"A08166803\", \"V46011425\"]}'",
                res: `{\n  <span class="json-key">"success"</span>: <span class="json-number">true</span>,\n  <span class="json-key">"processed"</span>: <span class="json-number">2</span>,\n  <span class="json-key">"data"</span>: [\n    { <span class="json-key">"cif"</span>: <span class="json-string">"A08166803"</span>, <span class="json-key">"status"</span>: <span class="json-string">"ACTIVA"</span> },\n    { <span class="json-key">"cif"</span>: <span class="json-string">"V46011425"</span>, <span class="json-key">"status"</span>: <span class="json-string">"ACTIVA"</span> }\n  ]\n}`
            }
        ];
        
        function stopTerminal() {
            clearTimeout(typingTimeout);
            clearTimeout(animationTimeout);
        }
        
        function runTerminal(tabIndex) {
            stopTerminal();
            const data = endpoints[tabIndex];
            
            cmdEl.innerHTML = '';
            cursor1.style.display = 'inline-block';
            responseEl.style.display = 'none';
            prompt2.style.display = 'none';
            
            responseEl.innerHTML = '<pre style="margin:0; font-family: inherit; font-size: 0.85rem;">' + data.res + '</pre>';
            
            let i = 0;
            function typeCmd() {
                if(i < data.cmd.length) {
                    if (data.cmd.charAt(i) === '\n') {
                        cmdEl.innerHTML += '<br>&gt; ';
                    } else {
                        cmdEl.innerHTML += data.cmd.charAt(i);
                    }
                    i++;
                    typingTimeout = setTimeout(typeCmd, 40);
                } else {
                    cursor1.style.display = 'none';
                    animationTimeout = setTimeout(() => {
                        responseEl.style.display = 'block';
                        animationTimeout = setTimeout(() => {
                            prompt2.style.display = 'block';
                            animationTimeout = setTimeout(() => runTerminal(currentTab), 4000);
                        }, 500);
                    }, 400);
                }
            }
            animationTimeout = setTimeout(typeCmd, 1000);
        }
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');
                currentTab = parseInt(tab.getAttribute('data-tab'));
                runTerminal(currentTab);
            });
        });
        
        runTerminal(currentTab);
    });
</script>

<?= view('partials/footer') ?>
</body>
</html>
