<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title' => 'API Registro Mercantil GRATIS: Entorno Sandbox para Pruebas | APIEmpresas',
        'excerptText' => 'Prueba nuestra API de empresas gratis sin tarjeta de crédito. Entorno Sandbox con datos reales del Registro Mercantil para validación de CIF y desarrollo.'
    ]) ?>
    <link rel="stylesheet" href="<?= base_url('public/css/home.css') ?>?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= base_url('public/css/home-mobile.css') ?>?v=<?= time() ?>" media="screen and (max-width: 768px)">
    <script type="application/ld+json">
    {
      "@context": "https://schema.org/",
      "@type": "Product",
      "name": "API Registro Mercantil - Plan Free (Sandbox)",
      "description": "Entorno Sandbox diseñado para que los desarrolladores prueben la integración y validen CIFs con datos oficiales reales sin tarjeta de crédito.",
      "brand": {
        "@type": "Brand",
        "name": "APIEmpresas"
      },
      "offers": {
        "@type": "Offer",
        "url": "<?= current_url() ?>",
        "priceCurrency": "EUR",
        "price": "0",
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
    .plan-badge-top { display: inline-flex; align-items: center; gap: 8px; background: #f1f5f9; color: #475569; padding: 6px 16px; border-radius: 100px; font-size: 0.9rem; font-weight: 700; margin-bottom: 24px; border: 1px solid #cbd5e1; }
    .plan-title { font-size: 3.5rem; font-weight: 900; color: #0f172a; letter-spacing: -0.03em; margin: 0 0 20px 0; line-height: 1.1; }
    .plan-title span { background: linear-gradient(135deg, #2563eb 0%, #10b981 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .plan-subtitle { font-size: 1.3rem; color: #475569; max-width: 700px; margin: 0 auto; line-height: 1.6; }
    
    .plan-demo-wrapper { margin: 40px auto 60px auto; max-width: 800px; background: #ffffff; border-radius: 12px; box-shadow: 0 20px 40px -10px rgba(0,0,0,0.1); border: 1px solid #e2e8f0; overflow: hidden; }
    
    /* Terminal Animation CSS */
    .terminal-window { background: #0f172a; width: 100%; font-family: 'Fira Code', monospace; text-align: left; }
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
    
    .plan-card { background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 24px; box-shadow: 0 20px 40px -15px rgba(15, 23, 42, 0.3); border: 1px solid rgba(255, 255, 255, 0.1); overflow: hidden; margin-bottom: 40px; position: relative; padding: 60px 40px; text-align: center; color: white; }
    .plan-card-bg { position: absolute; top: 0; left: 0; right: 0; bottom: 0; opacity: 0.4; background-image: radial-gradient(#334155 1px, transparent 1px); background-size: 20px 20px; pointer-events: none; }
    .plan-card h2 { font-size: 2.2rem; font-weight: 900; margin-bottom: 16px; color: #ffffff; letter-spacing: -0.02em; position: relative; z-index: 2; }
    .plan-card p { color: #94a3b8; margin-bottom: 32px; font-size: 1.15rem; max-width: 600px; margin-left: auto; margin-right: auto; line-height: 1.6; position: relative; z-index: 2; }
    
    .btn-primary-cta { display: inline-block; background: #3b82f6; color: #ffffff; font-weight: 800; font-size: 1.1rem; padding: 18px 40px; border-radius: 100px; text-decoration: none; box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.5); transition: all 0.3s; position: relative; z-index: 2; border: 1px solid #60a5fa; }
    .btn-primary-cta:hover { background: #2563eb; transform: translateY(-3px); box-shadow: 0 15px 35px -5px rgba(59, 130, 246, 0.6); }
    
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
                Plan Free (Testing)
            </div>
            <h1 class="plan-title">API Registro Mercantil <span>GRATIS</span></h1>
            <p class="plan-subtitle">Entorno Sandbox diseñado para que los desarrolladores prueben la integración y validen CIFs con datos oficiales reales sin tarjeta de crédito.</p>
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
                    <div class="term-tab active" data-tab="0">GET /companies/{cif}</div>
                    <div class="term-tab" data-tab="1">GET /companies/search</div>
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
                <div class="plan-feature-icon" style="background: #eff6ff; color: #3b82f6;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>
                </div>
                <h3>Testing de Integración</h3>
                <p>Haz pruebas con peticiones reales y asegúrate de que tu código funciona perfectamente antes de suscribirte.</p>
            </div>
            
            <div class="plan-feature-card">
                <div class="plan-feature-icon" style="background: #f0fdf4; color: #22c55e;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                </div>
                <h3>Validación Básica</h3>
                <p>Verifica si un CIF existe en el registro, obtén su Razón Social oficial y su código de actividad (CNAE).</p>
            </div>
            
            <div class="plan-feature-card">
                <div class="plan-feature-icon" style="background: #f1f5f9; color: #64748b;">
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M2 16.1A5 5 0 0 1 5.9 20M2 12.05A9 9 0 0 1 9.95 20M2 8V6a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2h-6"></path><line x1="2" y1="20" x2="2.01" y2="20"></line></svg>
                </div>
                <h3>Endpoint Principal</h3>
                <p>Acceso al endpoint GET /companies/{cif}. Simple, rápido y sin límite de tiempo (100 peticiones).</p>
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
                margin-bottom: 24px;
                text-align: center;
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
                color: #2563eb;
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
                color: #3b82f6;
                font-weight: 600;
                text-decoration: none;
                display: inline-flex;
                align-items: center;
                gap: 8px;
                font-size: 1.05rem;
                padding: 12px 24px;
                background: #eff6ff;
                border-radius: 100px;
                transition: all 0.2s;
            }
            .doc-link:hover {
                background: #dbeafe;
                color: #2563eb;
            }
        </style>

        <div class="endpoints-section">
            <h3 class="endpoints-title">Endpoints incluidos en el Plan Free</h3>
            <div class="endpoints-grid">
                <div class="endpoint-card">
                    <div class="endpoint-route">GET /companies/{cif}</div>
                    <p class="endpoint-desc">Obtén los datos oficiales básicos (Razón Social, Estado, CNAE) de una empresa indicando únicamente su CIF.</p>
                </div>
                <div class="endpoint-card">
                    <div class="endpoint-route">GET /companies/search</div>
                    <p class="endpoint-desc">Busca una empresa por su nombre comercial y localiza su CIF y estado actual de forma rápida y sencilla.</p>
                </div>
            </div>
            <div style="text-align: center; display: flex; gap: 16px; justify-content: center; flex-wrap: wrap;">
                <a href="<?= site_url('documentation') ?>" class="doc-link">
                    Ver documentación de la API
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </a>
                <a href="<?= base_url('public/docs/apiempresas_postman_free.json') ?>" class="doc-link" style="background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0;" download>
                    Descargar Postman Collection
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                </a>
            </div>
        </div>

        <div class="plan-card">
            <div class="plan-card-bg"></div>
            <h2>¿Listo para integrar?</h2>
            <p>Obtén tu API Key gratuita en menos de 1 minuto. Sin tarjeta de crédito.</p>
            <a href="<?= site_url('register?plan=free') ?>" class="btn-primary-cta">Crear cuenta gratis</a>
        </div>
        
        <div class="alternative-plans">
            <strong>¿No es exactamente lo que buscas?</strong><br>
            Si necesitas automatizar el alta de clientes, explora el <a href="<?= site_url('planes/pro') ?>">Plan Pro</a>. Para descubrir empresas gemelas usando IA, mira el <a href="<?= site_url('planes/business') ?>">Plan Business</a>.
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
                cmd: "curl -X GET 'https://api.apiempresas.es/v1/companies/B12345678' \\ \n  -H 'Authorization: Bearer test_key_123'",
                res: `{\n  <span class="json-key">"success"</span>: <span class="json-number">true</span>,\n  <span class="json-key">"data"</span>: {\n    <span class="json-key">"cif"</span>: <span class="json-string">"B12345678"</span>,\n    <span class="json-key">"name"</span>: <span class="json-string">"TECH SOLUTIONS IBERIA SL"</span>,\n    <span class="json-key">"cnae"</span>: <span class="json-string">"6201"</span>,\n    <span class="json-key">"cnae_description"</span>: <span class="json-string">"Actividades de programación informática"</span>,\n    <span class="json-key">"status"</span>: <span class="json-string">"ACTIVA"</span>\n  }\n}`
            },
            {
                cmd: "curl -X GET 'https://api.apiempresas.es/v1/companies/search?name=mercadona' \\ \n  -H 'Authorization: Bearer test_key_123'",
                res: `{\n  <span class="json-key">"success"</span>: <span class="json-number">true</span>,\n  <span class="json-key">"data"</span>: [\n    {\n      <span class="json-key">"cif"</span>: <span class="json-string">"A46103834"</span>,\n      <span class="json-key">"name"</span>: <span class="json-string">"MERCADONA SA"</span>,\n      <span class="json-key">"status"</span>: <span class="json-string">"ACTIVA"</span>\n    },\n    {\n      <span class="json-key">"cif"</span>: <span class="json-string">"B82635904"</span>,\n      <span class="json-key">"name"</span>: <span class="json-string">"MERCADONA ONLINE SL"</span>,\n      <span class="json-key">"status"</span>: <span class="json-string">"ACTIVA"</span>\n    }\n  ]\n}`
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
