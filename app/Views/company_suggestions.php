<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>
    <style>
        :root {
            --glass: rgba(255, 255, 255, 0.8);
            --glass-border: rgba(255, 255, 255, 0.4);
            --accent-soft: rgba(33, 82, 255, 0.05);
        }
        
        body { background-color: white; }

        /* Unified Animations */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-up { opacity: 0; animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .delay-1 { animation-delay: 0.1s; }
        .delay-2 { animation-delay: 0.2s; }
        .delay-3 { animation-delay: 0.3s; }

        /* Navigation Spacer */
        .page-header-offset { height: 100px; }

        /* Hero Styling */
        .hero-section { padding: 80px 0 100px; text-align: center; background: white; }
        
        /* Section Containers */
        .section-padding { padding: 120px 0; }
        .bg-light { background-color: #f8fafc; }
        .bg-gradient-soft { background: linear-gradient(180deg, white 0%, #f0f7ff 100%); }

        /* Search Box Wow Factor */
        .search-outer {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 10px;
            border-radius: 28px;
            box-shadow: 0 40px 100px rgba(0,0,0,0.08);
            border: 1px solid rgba(0,0,0,0.03);
            position: relative;
        }

        #company-search {
            width: 100%;
            padding: 28px 35px;
            font-size: 24px;
            font-weight: 500;
            border-radius: 22px;
            border: none;
            outline: none;
            transition: all 0.3s ease;
            color: var(--primary);
        }

        .suggestions-dropdown {
            position: absolute;
            top: 100%;
            left: 10px;
            right: 10px;
            background: white;
            border-radius: 0 0 24px 24px;
            box-shadow: 0 30px 60px rgba(0,0,0,0.12);
            z-index: 999;
            max-height: 400px;
            overflow-y: auto;
            display: none;
            border-top: 1px solid #f1f5f9;
        }

        .suggestions-dropdown.active { display: block; }
        .suggestion-item {
            padding: 20px 30px;
            cursor: pointer;
            border-bottom: 1px solid #f1f5f9;
            transition: all 0.2s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .suggestion-item:hover { background: #f8fbff; padding-left: 35px; }
        .company-name { font-weight: 700; color: var(--primary); font-size: 16px; }
        .company-addr { font-size: 12px; color: #94a3b8; margin-top: 4px; }
        .company-cif { font-family: "JetBrains Mono", monospace; background: #eef2ff; color: #3730a3; padding: 6px 12px; border-radius: 10px; font-size: 13px; font-weight: 700; }

        .loader { 
            position: absolute; 
            right: 40px; 
            top: 50%; 
            margin-top: -15px; 
            width: 30px; 
            height: 30px; 
            border: 3px solid #f3f3f3; 
            border-top: 3px solid var(--primary); 
            border-radius: 50%; 
            animation: spin 0.8s linear infinite; 
            display: none; 
        }

        /* Technical Window */
        .tech-window {
            background: #0f172a;
            border-radius: 24px;
            overflow: hidden;
            box-shadow: 0 40px 80px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.05);
        }

        /* Use Case Cards */
        .use-case-card {
            background: white;
            padding: 40px;
            border-radius: 24px;
            border: 1px solid #f1f5f9;
            transition: all 0.3s cubic-bezier(0.165, 0.84, 0.44, 1);
        }
        .use-case-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 50px rgba(19, 58, 130, 0.08);
            border-color: rgba(33, 82, 255, 0.2);
        }

        .feature-tag {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #f1f5f9;
            padding: 10px 20px;
            border-radius: 99px;
            font-weight: 700;
            font-size: 14px;
            color: #475569;
        }
        .feature-tag svg { color: #10b981; }

        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<?=view('partials/header') ?>

<main>
    <!-- HERO SECTION -->
    <section class="hero-section container">
        <div class="animate-up">
            <span class="pill top" style="margin-bottom: 30px; background: rgba(33, 82, 255, 0.05); color: var(--primary);">Integración en < 10 minutos</span>
            <h1 class="title" style="font-size: clamp(40px, 7vw, 52px); letter-spacing: -0.03em; line-height: 1.0; font-weight: 900; margin-bottom: 30px;">
                Autocompleta empresas por CIF <br>
                <span class="grad">en menos de 10 minutos</span>.
            </h1>
            <p class="subtitle" style="font-size: 24px; max-width: 850px; margin: 0 auto 50px; color: #64748b; line-height: 1.5;">
                Evita registros erróneos, facturas fallidas y bases de datos duplicadas. Conecta tu formulario o CRM y obtén datos oficiales al instante.
            </p>
            
            <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 15px; margin-bottom: 40px;">
                <div class="feature-tag"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg> Razón Social</div>
                <div class="feature-tag"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg> Dirección Fiscal</div>
                <div class="feature-tag"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg> Validación VIES</div>
                <div class="feature-tag"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg> CNAE Oficial</div>
                <div class="feature-tag"><svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path d="M5 13l4 4L19 7"/></svg> Estado Activo</div>
            </div>
        </div>
    </section>

    <!-- LIVE DEMO SECTION -->
    <section class="section-padding bg-gradient-soft" style="margin-top: -40px; position: relative; z-index: 50;">
        <div class="container">
            <div style="text-align: center; margin-bottom: 60px;" class="animate-up delay-1">
                <span class="eyebrow">Demo en vivo</span>
                <h2 style="font-size: 46px; font-weight: 900; margin-top: 10px;">Siéntelo en tus <span class="grad">propias manos</span>.</h2>
                <p class="muted" style="font-size: 20px; margin-top: 15px;">Experimenta la velocidad y precisión de nuestro motor de autocompletado.</p>
            </div>

            <div class="search-outer animate-up delay-1">
                <input type="text" id="company-search" placeholder="Escribe el nombre de una empresa o un CIF..." autocomplete="off">
                <div class="loader" id="loading-spinner"></div>
                <div class="suggestions-dropdown" id="suggestions"></div>
            </div>

            <!-- RESULT PREVIEW -->
            <div id="json-result" class="animate-up is-hidden" style="margin-top: 50px; max-width: 900px; margin-inline: auto;">
                <div class="tech-window">
                    <div style="background: #1e293b; padding: 12px 25px; border-bottom: 1px solid rgba(255,255,255,0.05); display: flex; align-items: center; gap: 10px;">
                        <span style="font-size: 12px; font-weight: 800; color: #10b981;">RESPONSE SUCCESS 200</span>
                        <span style="font-size: 12px; color: rgba(255,255,255,0.4); margin-left: auto;">application/json</span>
                    </div>
                    <pre style="padding: 30px; margin: 0; font-size: 16px; overflow-x: auto;"><code id="json-code"></code></pre>
                </div>
                <p style="text-align: center; margin-top: 20px; color: var(--primary); font-weight: 700;">✔ Todo con una sola llamada API</p>
            </div>
        </div>
    </section>

    <!-- USE CASES SECTION -->
    <section class="section-padding container">
        <div style="text-align: center; margin-bottom: 80px;" class="animate-up">
            <span class="eyebrow">Aplica la automatización</span>
            <h3 style="font-size: 46px; font-weight: 900; margin-top: 10px;">Casos reales <span class="grad">de uso</span></h3>
        </div>

        <div class="grid animate-up delay-1" style="grid-template-columns: repeat(auto-fit, minmax(350px, 1fr)); gap: 30px;">
            <div class="use-case-card">
                <span style="font-size: 36px; margin-bottom: 20px; display: block;">✅</span>
                <h3 style="font-weight: 800; margin-bottom: 12px; font-size: 22px;">Autocompletar formularios B2B</h3>
                <p class="muted" style="line-height: 1.6;">Reduce el abandono en el registro. Al introducir el CIF, rellenamos por ti la razón social, dirección y datos fiscales.</p>
            </div>
            <div class="use-case-card">
                <span style="font-size: 36px; margin-bottom: 20px; display: block;">✅</span>
                <h3 style="font-weight: 800; margin-bottom: 12px; font-size: 22px;">Evitar altas falsas</h3>
                <p class="muted" style="line-height: 1.6;">Verifica la existencia física y mercantil de cualquier entidad antes de permitir el acceso a tu plataforma.</p>
            </div>
            <div class="use-case-card">
                <span style="font-size: 36px; margin-bottom: 20px; display: block;">✅</span>
                <h3 style="font-weight: 800; margin-bottom: 12px; font-size: 22px;">Dedupe automático</h3>
                <p class="muted" style="line-height: 1.6;">El CIF es el ID único. Úsalo para evitar que la misma empresa se registre dos veces con nombres diferentes.</p>
            </div>
            <div class="use-case-card">
                <span style="font-size: 36px; margin-bottom: 20px; display: block;">✅</span>
                <h3 style="font-weight: 800; margin-bottom: 12px; font-size: 22px;">Limpieza de CRM por CSV</h3>
                <p class="muted" style="line-height: 1.6;">Actualiza tus bases de datos obsoletas subiendo tus listados de CIFs y obteniendo los datos actuales.</p>
            </div>
            <div class="use-case-card">
                <span style="font-size: 36px; margin-bottom: 20px; display: block;">✅</span>
                <h3 style="font-weight: 800; margin-bottom: 12px; font-size: 22px;">Validación KYB</h3>
                <p class="muted" style="line-height: 1.6;">Simplifica tus procesos de "Know Your Business" accediendo a trazabilidad directa de fuentes oficiales.</p>
            </div>
            <div class="use-case-card">
                <span style="font-size: 36px; margin-bottom: 20px; display: block;">✅</span>
                <h3 style="font-weight: 800; margin-bottom: 12px; font-size: 22px;">Prevención pre-factura</h3>
                <p class="muted" style="line-height: 1.6;">No emitas facturas rectificativas. Valida los datos fiscales de tus clientes antes de generar el documento legal.</p>
            </div>
        </div>
    </section>

    <!-- DEVELOPER SECTION -->
    <section class="section-padding bg-light">
        <div class="container">
            <div class="grid" style="grid-template-columns: 1fr 1.3fr; gap: 80px; align-items: center;">
                <div class="animate-up">
                    <span class="eyebrow" style="color: var(--primary);">Developer First</span>
                    <h2 style="font-size: 46px; font-weight: 900; margin: 15px 0 25px;">Pensado para <span class="grad">ingenieros</span>.</h2>
                    <p class="muted" style="font-size: 20px; line-height: 1.6; margin-bottom: 40px;">
                        Integra la búsqueda de empresas en tu producto con una sola llamada REST. Sin burocracia, sin contratos lentos.
                    </p>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                        <div style="background: white; padding: 25px; border-radius: 20px; border: 1px solid #eef2f6;">
                            <strong style="display: block; font-size: 18px; margin-bottom: 8px;">Sin contratos</strong>
                            <span class="muted" style="font-size: 15px;">Paga por uso, cancela cuando quieras.</span>
                        </div>
                        <div style="background: white; padding: 25px; border-radius: 20px; border: 1px solid #eef2f6;">
                            <strong style="display: block; font-size: 18px; margin-bottom: 8px;">Sin demos</strong>
                            <span class="muted" style="font-size: 15px;">Empieza a probarlo en 2 minutos.</span>
                        </div>
                    </div>
                </div>
                <div class="animate-up delay-2">
                    <div class="tech-window">
                        <div style="background: #1e293b; padding: 15px 25px; border-bottom: 1px solid rgba(255,255,255,0.05);">
                            <span style="font-size: 14px; color: #94a3b8; font-weight: 700;">GET /v1/company?cif=B12345678</span>
                        </div>
                        <pre style="padding: 35px; margin: 0; line-height: 1.8; font-size: 15px; color: #e2e8f0; border-radius: 0;"><code><span style="color: #64748b;">// Funciona nativamente con:</span>
<span style="color: #64748b;">// PHP, Laravel, Node, Python...</span>

{
  <span style="color: #f472b6;">"success"</span>: <span style="color: #6ee7b7;">true</span>,
  <span style="color: #f472b6;">"data"</span>: {
    <span style="color: #f472b6;">"name"</span>: <span style="color: #fbbf24;">"EMPRESA EJEMPLO SL"</span>,
    <span style="color: #f472b6;">"cif"</span>: <span style="color: #fbbf24;">"B12345678"</span>,
    <span style="color: #f472b6;">"address"</span>: <span style="color: #fbbf24;">"CALLE MAYOR 1, MADRID"</span>,
    <span style="color: #f472b6;">"cnae"</span>: <span style="color: #fbbf24;">"6201"</span>,
    <span style="color: #f472b6;">"status"</span>: <span style="color: #fbbf24;">"ACTIVA"</span>
  }
}</code></pre>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- FINAL CTA SECTION -->
    <section style="background: #f8fbff; padding: 130px 0; border-top: 1px solid #eef2f6;">
        <div class="container animate-up" style="text-align: center;">
            <h2 style="font-size: clamp(36px, 6vw, 44px); font-weight: 900; letter-spacing: -0.02em; line-height: 1.1; margin-bottom: 30px;">
                ¿Listo para <span class="grad">automatizar</span> tu negocio?
            </h2>
            <p style="color: #64748b; font-size: 24px; margin-bottom: 50px; max-width: 850px; margin-inline: auto;">
                Únete a los SaaS B2B, ERPs y Fintech que ya confían en nuestra API de autocompletado en tiempo real.
            </p>
            <div style="display: flex; flex-wrap: wrap; justify-content: center; gap: 20px;">
                <a href="<?=site_url('register') ?>" class="btn primary" style="padding: 24px 55px; font-size: 21px; font-weight: 800; border-radius: 20px; box-shadow: 0 20px 40px rgba(33, 82, 255, 0.25);">👉 Prueba gratis ahora</a>
                <a href="<?=site_url('documentation') ?>" class="btn ghost" style="padding: 24px 55px; font-size: 21px; font-weight: 800; border-radius: 20px; color: var(--primary); background: white; border: 2px solid rgba(19, 58, 130, 0.1);">👉 Ver documentación</a>
            </div>
            <div style="margin-top: 60px; display: flex; flex-wrap: wrap; justify-content: center; gap: 50px; opacity: 0.7;">
                <span style="font-weight: 700; color: #475569;">✔ Sin llamadas comerciales</span>
                <span style="font-weight: 700; color: #475569;">✔ Sin contratos</span>
                <span style="font-weight: 700; color: #475569;">✔ Sin demos</span>
            </div>
        </div>
    </section>
</main>

<?=view('partials/footer') ?>

<script>
    const input = document.getElementById('company-search');
    const dropdown = document.getElementById('suggestions');
    const spinner = document.getElementById('loading-spinner');
    const jsonResult = document.getElementById('json-result');
    const jsonCode = document.getElementById('json-code');
    let debounceTimer;
    
    // Updated route
    const baseUrl = '<?= site_url('autocompletado-cif-empresas') ?>';

    const renderItem = (company) => {
        const jsonString = encodeURIComponent(JSON.stringify(company));
        return `
            <div class="suggestion-item" data-json="${jsonString}">
                <div>
                    <span class="company-name">${company.name}</span>
                    <span class="company-addr">${company.address || 'Ubicación no facilitada'}</span>
                </div>
                <span class="company-cif">${company.cif}</span>
            </div>
        `;
    };

    const fetchSuggestions = async (query) => {
        if (!query || query.length < 3) {
            dropdown.classList.remove('active');
            dropdown.innerHTML = '';
            return;
        }

        spinner.style.display = 'block';

        try {
            const response = await fetch(`${baseUrl}/get?q=${encodeURIComponent(query)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            const result = await response.json();
            
            spinner.style.display = 'none';
            dropdown.innerHTML = '';

            if (result.success && result.data && result.data.length > 0) {
                dropdown.innerHTML = result.data.map(renderItem).join('');
                dropdown.classList.add('active');
            } else {
                dropdown.innerHTML = '<div style="padding:30px; text-align:center; color:#94a3b8; font-style:italic; font-size:15px;">No hemos encontrado empresas oficiales para esta búsqueda.</div>';
                dropdown.classList.add('active');
            }
        } catch (error) {
            spinner.style.display = 'none';
        }
    };

    input.addEventListener('input', (e) => {
        const value = e.target.value.trim();
        
        // Hide previous result when starting a new search to avoid overlap
        jsonResult.classList.add('is-hidden');
        
        clearTimeout(debounceTimer);
        if (value.length < 3) {
            dropdown.classList.remove('active');
            return;
        }
        debounceTimer = setTimeout(() => fetchSuggestions(value), 300);
    });

    document.addEventListener('click', (e) => {
        if (!input.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
        }
    });

    dropdown.addEventListener('click', (e) => {
        const item = e.target.closest('.suggestion-item');
        if (item) {
            const jsonString = decodeURIComponent(item.dataset.json);
            const data = JSON.parse(jsonString);
            input.value = data.name;
            dropdown.classList.remove('active');
            
            // Format JSON with syntax highlighting
            jsonCode.innerHTML = syntaxHighlight(data);
            jsonResult.classList.remove('is-hidden');
            jsonResult.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
    });

    function syntaxHighlight(json) {
        if (typeof json != 'string') json = JSON.stringify(json, undefined, 2);
        json = json.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
        return json.replace(/("(\\u[a-zA-Z0-9]{4}|\\[^u]|[^\\"])*"(\s*:)?|\b(true|false|null)\b|-?\d+(?:\.\d*)?(?:[eE][+-]?\d+)?)/g, function (match) {
            var color = '#6ee7b7'; // Default for numbers/booleans
            if (/^"/.test(match)) {
                if (/:$/.test(match)) {
                    color = '#f472b6'; // Key
                } else {
                    color = '#fbbf24'; // String
                }
            }
            return '<span style="color:' + color + '">' + match + '</span>';
        });
    }

    // Initialize Intersection Observer for animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-active'); // Not used, just triggering the animation class
                entry.target.style.opacity = "1";
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.animate-up').forEach(el => observer.observe(el));
</script>

</body>
</html>
