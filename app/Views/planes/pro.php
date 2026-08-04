<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', ['title' => $title ?? 'Plan Pro - API Empresas']) ?>
    <link rel="stylesheet" href="<?= base_url('public/css/home.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/home-mobile.css') ?>?v=<?= time() ?>" media="screen and (max-width: 768px)">
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
    .plan-badge-top { display: inline-flex; align-items: center; gap: 8px; background: #e0e7ff; color: #4338ca; padding: 6px 16px; border-radius: 100px; font-size: 0.9rem; font-weight: 700; margin-bottom: 24px; border: 1px solid #c7d2fe; }
    .plan-title { font-size: 3.5rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0 0 20px 0; line-height: 1.1; }
    .plan-title span { background: linear-gradient(135deg, #4338ca 0%, #3730a3 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .plan-subtitle { font-size: 1.3rem; color: #475569; max-width: 700px; margin: 0 auto; line-height: 1.6; }
    
    .plan-demo-wrapper { margin: 40px auto 60px auto; max-width: 800px; background: #ffffff; border-radius: 12px; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden; }
    
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
    
    .plan-card { background: linear-gradient(135deg, #312e81 0%, #4338ca 100%); border-radius: 24px; box-shadow: 0 20px 40px -15px rgba(49, 46, 129, 0.3); border: 1px solid rgba(255, 255, 255, 0.1); overflow: hidden; margin-bottom: 40px; position: relative; padding: 60px 40px; text-align: center; color: white; }
    .plan-card-bg { position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.4; background-image: radial-gradient(#4f46e5 1px, transparent 1px); background-size: 20px 20px; pointer-events: none; }
    .plan-card h2 { font-size: 2.2rem; font-weight: 900; margin-bottom: 16px; color: #ffffff; letter-spacing: -0.02em; position: relative; z-index: 2; }
    .plan-card p { color: #a5b4fc; margin-bottom: 32px; font-size: 1.15rem; max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.6; position: relative; z-index: 2; }
    
    .btn-primary-cta { display: inline-block; background: #10b981; color: #ffffff; font-weight: 800; font-size: 1.1rem; padding: 18px 40px; border-radius: 100px; text-decoration: none; box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.5); transition: all 0.3s; position: relative; z-index: 2; border: 1px solid #34d399; }
    .btn-primary-cta:hover { background: #059669; transform: translateY(-3px); box-shadow: 0 15px 35px -5px rgba(16, 185, 129, 0.6); }
    
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
                Plan Pro (El más elegido)
            </div>
            <h1 class="plan-title">Automatización B2B <span>Sin Fricción</span></h1>
            <p class="plan-subtitle">Elimina el trabajo manual. Autocompleta datos de clientes en tu CRM o ERP, averigua el riesgo financiero antes de vender y extrae listas de nuevas constituciones empresariales.</p>
        </div>

        <div class="plan-demo-wrapper">
            <div class="terminal-window">
                <div class="terminal-header">
                    <div class="terminal-dot"></div>
                    <div class="terminal-dot"></div>
                    <div class="terminal-dot"></div>
                    <div class="terminal-title">bash - api-empresas - 80x24</div>
                </div>
                <div class="terminal-tabs">
                    <div class="term-tab active" data-tab="0">Scoring IA</div>
                    <div class="term-tab" data-tab="1">Búsqueda Sector</div>
                    <div class="term-tab" data-tab="2">Eventos BORME</div>
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
                <div class="plan-feature-icon" style="background: #e0e7ff; color: #4338ca;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                </div>
                <h3>Auto-relleno de ERPs</h3>
                <p>Tus usuarios solo introducen el CIF. La API rellena instantáneamente la Razón Social y dirección. Cero errores humanos.</p>
            </div>
            
            <div class="plan-feature-card">
                <div class="plan-feature-icon" style="background: #fce7f3; color: #db2777;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path></svg>
                </div>
                <h3>Scoring Comercial</h3>
                <p>Antes de dar crédito o cerrar un contrato, la API te devuelve un scoring de IA basado en impagos y antigüedad.</p>
            </div>
            
            <div class="plan-feature-card">
                <div class="plan-feature-icon" style="background: #fef3c7; color: #d97706;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                </div>
                <h3>3.000 Consultas/mes</h3>
                <p>Volumen suficiente para automatizar los procesos B2B de tu SaaS o herramienta interna sin preocuparte por los límites.</p>
            </div>
        </div>

        <div class="plan-card">
            <div class="plan-card-bg"></div>
            <h2>Pásate al nivel profesional</h2>
            <p>Obtén tu API Key Pro al instante y empieza a automatizar.</p>
            <a href="<?= site_url('register?plan=pro') ?>" class="btn-primary-cta">Empezar con Pro (19€/mes + IVA)</a>
        </div>
        
        <div class="alternative-plans">
            <strong>¿No es exactamente lo que buscas?</strong><br>
            Si solo quieres hacer pruebas básicas, descubre el <a href="<?= site_url('planes/free') ?>">Plan Free</a>. Si necesitas un volumen masivo y Match IA, explora el <a href="<?= site_url('planes/business') ?>">Plan Business</a>.
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
                cmd: "curl -X GET 'https://api.apiempresas.es/v1/companies/score?cif=B86854536' \\ \n  -H 'Authorization: Bearer pro_key_555'",
                res: `{\n  <span class="json-key">"success"</span>: <span class="json-number">true</span>,\n  <span class="json-key">"data"</span>: {\n    <span class="json-key">"cif"</span>: <span class="json-string">"B86854536"</span>,\n    <span class="json-key">"score"</span>: <span class="json-number">92</span>,\n    <span class="json-key">"risk_level"</span>: <span class="json-string">"LOW"</span>,\n    <span class="json-key">"recommendation"</span>: <span class="json-string">"Aprobar crédito"</span>\n  }\n}`
            },
            {
                cmd: "curl -X GET 'https://api.apiempresas.es/v1/companies/search?cnae=6201' \\ \n  -H 'Authorization: Bearer pro_key_555'",
                res: `{\n  <span class="json-key">"success"</span>: <span class="json-number">true</span>,\n  <span class="json-key">"data"</span>: [\n    {\n      <span class="json-key">"cif"</span>: <span class="json-string">"B12345678"</span>,\n      <span class="json-key">"name"</span>: <span class="json-string">"TECH SOLUTIONS IBERIA SL"</span>\n    },\n    {\n      <span class="json-key">"cif"</span>: <span class="json-string">"B98765432"</span>,\n      <span class="json-key">"name"</span>: <span class="json-string">"DATA DRIVEN APPS SL"</span>\n    }\n  ]\n}`
            },
            {
                cmd: "curl -X GET 'https://api.apiempresas.es/v1/companies/borme?cif=B86854536' \\ \n  -H 'Authorization: Bearer pro_key_555'",
                res: `{\n  <span class="json-key">"success"</span>: <span class="json-number">true</span>,\n  <span class="json-key">"data"</span>: {\n    <span class="json-key">"cif"</span>: <span class="json-string">"B86854536"</span>,\n    <span class="json-key">"company_name"</span>: <span class="json-string">"CAMCOMTUR GEORGIA SL"</span>,\n    <span class="json-key">"events"</span>: [\n      {\n        <span class="json-key">"date"</span>: <span class="json-string">"2023-11-15"</span>,\n        <span class="json-key">"act_types"</span>: <span class="json-string">"Nombramientos"</span>,\n        <span class="json-key">"description"</span>: <span class="json-string">"Nombramiento de Administrador Único"</span>,\n        <span class="json-key">"url_pdf"</span>: <span class="json-string">"https://www.boe.es/..."</span>\n      }\n    ]\n  }\n}`
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

<style>
@keyframes blink { 0%, 100% { opacity: 1; } 50% { opacity: 0; } }
</style>

<?= view('partials/footer') ?>
</body>
</html>
