<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head') ?>
    <style>
        .lookalike-hero {
            padding: 40px 0 40px;
            text-align: center;
            background: linear-gradient(180deg, #f8fafc 0%, #ffffff 100%);
            position: relative;
            overflow: hidden;
        }

        .lookalike-hero::before {
            content: '';
            position: absolute;
            top: -50%;
            left: 50%;
            transform: translateX(-50%);
            width: 800px;
            height: 800px;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        .lookalike-title {
            font-size: 2.8rem;
            font-weight: 900;
            color: var(--ae-dark, #0f172a);
            margin-bottom: 12px;
            line-height: 1.1;
            letter-spacing: -0.03em;
        }

        .lookalike-subtitle {
            font-size: 1.25rem;
            color: var(--ae-slate, #64748b);
            max-width: 700px;
            margin: 0 auto 24px;
            line-height: 1.6;
        }

        .upload-card {
            background: #ffffff;
            border: 1px solid var(--ae-border, #e2e8f0);
            border-radius: 24px;
            box-shadow: 0 20px 40px -15px rgba(0,0,0,0.05);
            max-width: 600px;
            margin: 0 auto;
            padding: 32px;
            position: relative;
            z-index: 10;
        }

        .drop-zone {
            border: 2px dashed #cbd5e1;
            border-radius: 16px;
            padding: 32px 24px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #f8fafc;
        }

        .drop-zone:hover, .drop-zone.dragover {
            border-color: var(--ae-blue, #2563eb);
            background: #eff6ff;
        }

        .drop-zone-icon {
            width: 64px;
            height: 64px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 16px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);
            color: var(--ae-blue, #2563eb);
        }

        .drop-zone-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: var(--ae-dark, #0f172a);
            margin-bottom: 12px;
        }

        .drop-zone-text {
            color: var(--ae-slate, #64748b);
            font-size: 0.95rem;
            margin-bottom: 24px;
        }

        .file-input {
            display: none;
        }

        .btn-upload {
            background: var(--ae-blue, #2563eb);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-upload:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 15px -3px rgba(37,99,235,0.3);
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 32px;
            max-width: 1100px;
            margin: 80px auto 0;
            text-align: left;
        }

        .feature-item {
            display: flex;
            flex-direction: column;
            gap: 20px;
            background: #ffffff;
            padding: 36px 32px;
            border-radius: 24px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.02), 0 4px 6px -2px rgba(0,0,0,0.02);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            position: relative;
            z-index: 1;
            overflow: hidden;
        }

        .feature-item::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 6px;
            background: linear-gradient(90deg, #2563eb, #60a5fa);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .feature-item:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 40px -10px rgba(37,99,235,0.1);
            border-color: #bfdbfe;
        }

        .feature-item:hover::before {
            opacity: 1;
        }

        /* Colores únicos para cada paso */
        .feature-item:nth-child(1)::before { background: linear-gradient(90deg, #3b82f6, #06b6d4); }
        .feature-item:nth-child(2)::before { background: linear-gradient(90deg, #8b5cf6, #ec4899); }
        .feature-item:nth-child(3)::before { background: linear-gradient(90deg, #f59e0b, #ef4444); }

        .feature-icon {
            width: 60px;
            height: 60px;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.3s ease;
        }

        .feature-item:nth-child(1) .feature-icon {
            background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
            border: 1px solid #bfdbfe;
            color: #2563eb;
            box-shadow: 0 8px 16px -4px rgba(37,99,235,0.15);
        }
        
        .feature-item:nth-child(2) .feature-icon {
            background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%);
            border: 1px solid #e9d5ff;
            color: #9333ea;
            box-shadow: 0 8px 16px -4px rgba(147,51,234,0.15);
        }

        .feature-item:nth-child(3) .feature-icon {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            border: 1px solid #fed7aa;
            color: #ea580c;
            box-shadow: 0 8px 16px -4px rgba(234,88,12,0.15);
        }

        .feature-item:hover .feature-icon {
            transform: scale(1.1) rotate(5deg);
        }

        .feature-title {
            font-weight: 800;
            font-size: 1.25rem;
            margin-bottom: 4px;
            color: var(--ae-dark, #0f172a);
            letter-spacing: -0.01em;
        }

        .feature-desc {
            color: var(--ae-slate, #64748b);
            font-size: 1rem;
            line-height: 1.6;
            margin: 0;
        }

        .gradient-text {
            background: linear-gradient(135deg, #2563eb, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        .loading-overlay {
            background: #ffffff;
            border-radius: 24px;
            display: none; /* flex when active */
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
            z-index: 20;
        }
        
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #e2e8f0;
            border-top-color: var(--ae-blue, #2563eb);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }
        
        @keyframes spin { 100% { transform: rotate(360deg); } }
        
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body>

    <?= view('partials/header') ?>

    <main>
        <section class="lookalike-hero">
            <div class="container">
                
                <h1 class="lookalike-title">Buscador de Empresas Similares: Encuentra a los <span class="gradient-text">gemelos de tus clientes</span></h1>
                <p class="lookalike-subtitle">Sube un Excel con el CIF de tus mejores clientes. Nuestra IA analizará su perfil (CNAE, provincia, antigüedad, facturación) y te devolverá un listado con las empresas de España que son exactamente iguales a ellos.</p>

                <?php if(session()->has('error')): ?>
                    <div style="background: #fef2f2; border: 1px solid #f87171; color: #b91c1c; padding: 16px; border-radius: 12px; max-width: 600px; margin: 0 auto 24px; font-weight: 600;">
                        <?= session('error') ?>
                    </div>
                <?php endif; ?>

                <div class="upload-card">
                    <form action="<?= site_url('encontrar-empresas-similares/process') ?>" method="POST" enctype="multipart/form-data" id="uploadForm">
                        <?= csrf_field() ?>
                        <div class="drop-zone" id="dropZone">
                            <div class="drop-zone-icon">
                                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="17 8 12 3 7 8"></polyline><line x1="12" y1="3" x2="12" y2="15"></line></svg>
                            </div>
                            <h3 class="drop-zone-title">Arrastra tu archivo aquí</h3>
                            <p class="drop-zone-text">O haz clic para seleccionar (Excel o CSV).<br>El archivo debe contener al menos una columna con los CIFs.<br><span style="font-size: 0.85rem; color: #94a3b8; margin-top: 6px; display: inline-block;">(Tranquilo, si tu Excel tiene otras columnas desordenadas, la IA encontrará los CIFs automáticamente).</span></p>
                            
                            <input type="file" name="clientes_file" id="fileInput" class="file-input" accept=".csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel">
                            
                            <button type="button" class="btn-upload" onclick="document.getElementById('fileInput').click()">
                                Seleccionar archivo
                            </button>
                            
                            <div id="file-name-display" style="margin-top: 16px; font-weight: 700; color: var(--ae-blue, #2563eb); display: none;"></div>
                        </div>

                        <!-- Alcance movido al paso 2 -->
                        
                        <div style="margin-top: 16px; display: flex; align-items: center; justify-content: center; gap: 16px; font-size: 0.85rem; color: var(--ae-slate); flex-wrap: wrap;">
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                Tus datos están 100% cifrados
                            </span>
                            <span style="display: flex; align-items: center; gap: 6px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect><line x1="1" y1="10" x2="23" y2="10"></line></svg>
                                Pago seguro solo si hay resultados (desde 9€)
                            </span>
                        </div>

                        <div id="step2-config" style="display: none; margin-top: 24px; padding-top: 24px; border-top: 1px solid #e2e8f0; text-align: left;">
                            <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--ae-dark); margin-bottom: 16px;">2. Configura tu extracción</h3>
                            
                            <label style="display: block; font-size: 0.95rem; color: var(--ae-slate); margin-bottom: 8px; font-weight: 600;">¿Cuántos prospectos similares quieres extraer? (Máximo)</label>
                            
                            <input type="range" id="maxResultsSlider" name="max_results" min="100" max="10000" step="100" value="500" style="width: 100%; margin-bottom: 16px;">
                            
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                                <span style="font-weight: 800; color: var(--ae-blue); font-size: 1.25rem;" id="sliderValueDisplay">500 prospectos</span>
                                <div style="text-align: right;">
                                    <span style="font-size: 0.85rem; color: var(--ae-slate); display: block;">Inversión base estimada:</span>
                                    <span style="font-weight: 900; color: #16a34a; font-size: 1.5rem;" id="priceDisplay">49€</span>
                                </div>
                            </div>
                            

                            <div style="margin-top: 32px; padding-top: 24px; border-top: 1px solid #e2e8f0; text-align: left; margin-bottom: 24px;">
                                <h3 style="font-size: 1.1rem; font-weight: 800; color: var(--ae-dark); margin-bottom: 16px;">3. Configura tu alcance</h3>
                                <div style="background: #f8fafc; padding: 16px; border-radius: 12px; border: 1px solid #e2e8f0; display: flex; flex-direction: column; gap: 12px;">
                                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-size: 0.9rem; color: var(--ae-slate);">
                                        <input type="radio" name="scope" value="national" checked style="margin-top: 3px; accent-color: var(--ae-blue);">
                                        <div>
                                            <strong style="color: var(--ae-dark); display: block; margin-bottom: 2px;">🌍 Toda España (Recomendado)</strong>
                                            Encuentra gemelos en cualquier provincia para maximizar la cantidad de resultados.
                                        </div>
                                    </label>
                                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; font-size: 0.9rem; color: var(--ae-slate);">
                                        <input type="radio" name="scope" value="provincial" style="margin-top: 3px; accent-color: var(--ae-blue);">
                                        <div>
                                            <strong style="color: var(--ae-dark); display: block; margin-bottom: 2px;">📍 Búsqueda Local</strong>
                                            Limitar estrictamente a las provincias donde operan los clientes subidos.
                                        </div>
                                    </label>
                                </div>
                            </div>
                            
                            <button type="button" id="btnAnalyze" class="btn-upload" style="width: 100%; justify-content: center; font-size: 1.1rem; padding: 14px;">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="5 3 19 12 5 21 5 3"></polygon></svg>
                                Analizar y Extraer Clones
                            </button>
                        </div>
                    </form>
                    
                    <div class="loading-overlay" id="loadingOverlay">
                        <div class="spinner"></div>
                        <h3 id="loaderTitle" style="margin-top: 12px; font-weight: 800; color: var(--ae-dark); text-align: center; transition: opacity 0.3s ease; font-size: 1.1rem;">🚀 Iniciando el motor de búsqueda...</h3>
                        
                        <div style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); padding: 12px 20px; border-radius: 12px; margin-top: 12px; border: 1px solid #bfdbfe; max-width: 480px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                            <p id="loaderSubtitle" style="color: #1e40af; margin: 0; font-weight: 600; text-align: center; font-size: 0.9rem; transition: opacity 0.3s ease;">Preparando entorno seguro de datos...</p>
                        </div>

                        <div style="margin-top: 12px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px; padding: 12px 20px; text-align: center; max-width: 480px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);">
                            <span style="display: flex; align-items: center; justify-content: center; gap: 6px; color: #b45309; font-weight: 800; font-size: 0.9rem; margin-bottom: 4px;">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22c5.523 0 10-4.477 10-10S17.523 2 12 2 2 6.477 2 12s4.477 10 10 10z"></path><polyline points="12 6 12 12 16 14"></polyline></svg>
                                Por favor, no cierres esta ventana
                            </span>
                            <p style="color: #92400e; font-size: 0.85rem; margin: 0; font-weight: 500; line-height: 1.4;">Este proceso avanzado puede tomar unos instantes. Al finalizar, obtendrás una base de datos de <b>alto valor</b> lista para potenciar tus ventas y escalar tu negocio.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section style="background: #f1f5f9; padding: 100px 0; border-top: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; position: relative; z-index: 5; box-shadow: inset 0 4px 6px -4px rgba(0,0,0,0.02);">
            <div class="container">
                <div class="features-grid" style="margin-top: 0;">
                    <div class="feature-item">
                        <div class="feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        </div>
                        <div>
                            <h4 class="feature-title">1. Sube tu base actual</h4>
                            <p class="feature-desc">Sube un Excel con un puñado de tus mejores clientes (ej. 10, 50, o 100 CIFs) para que el algoritmo aprenda tu "Buyer Persona" ideal.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>
                        </div>
                        <div>
                            <h4 class="feature-title">2. IA y Big Data</h4>
                            <p class="feature-desc">Cruzamos múltiples variables sociodemográficas (CNAE, tamaño, capital, edad) para encontrar patrones ocultos de rentabilidad.</p>
                        </div>
                    </div>
                    <div class="feature-item">
                        <div class="feature-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="5"></circle><path d="M3 21v-2a7 7 0 0 1 14 0v2"></path><circle cx="19" cy="8" r="5"></circle><path d="M17 21v-2a7 7 0 0 1 4-5.3"></path></svg>
                        </div>
                        <div>
                            <h4 class="feature-title">3. Descarga de Alto Valor</h4>
                            <p class="feature-desc">Obtén un Excel con tus clones, enriquecido con Administradores, Finanzas, Subvenciones y Contratos Públicos listos para tu equipo comercial.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section style="background: #ffffff; padding: 100px 0 120px; border-top: 1px solid #f1f5f9; position: relative; overflow: hidden;">
            <!-- Efectos de luz hiper-sutiles -->
            <div style="position: absolute; top: -100px; right: -100px; width: 500px; height: 500px; background: radial-gradient(circle, rgba(56, 189, 248, 0.05) 0%, transparent 70%); border-radius: 50%; filter: blur(40px);"></div>
            <div style="position: absolute; bottom: -150px; left: -100px; width: 600px; height: 600px; background: radial-gradient(circle, rgba(37, 99, 235, 0.05) 0%, transparent 70%); border-radius: 50%; filter: blur(60px);"></div>

            <div class="container" style="position: relative; z-index: 2;">
                <div style="max-width: 1200px; margin: 0 auto;">
                    <h3 style="font-size: 2rem; font-weight: 900; color: var(--ae-dark); margin-bottom: 24px; text-align: center; letter-spacing: -0.02em;">¿Qué obtendrás exactamente?</h3>
                    <p style="color: var(--ae-slate); font-size: 1.1rem; text-align: center; margin-bottom: 40px; max-width: 800px; margin-left: auto; margin-right: auto;">Nuestro motor de Inteligencia Artificial cruza los CIFs de tus clientes con la mayor base de datos empresarial de España, devolviéndote un <b>archivo Excel premium listo para tu equipo de ventas</b> con los prospectos que necesites.</p>
                    
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 32px;">
                        
                        <div style="background: linear-gradient(180deg, #f0f9ff 0%, #ffffff 100%); padding: 24px; border-radius: 16px; border: 1px solid #bae6fd; box-shadow: 0 4px 6px -1px rgba(2,132,199,0.05);">
                            <h4 style="font-size: 1.25rem; font-weight: 700; color: #0369a1; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#0ea5e9" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                                Ubicación y Contacto
                            </h4>
                            <ul style="list-style: none; padding: 0; margin: 0; color: var(--ae-slate); font-weight: 500; display: flex; flex-direction: column; gap: 10px;">
                                <li><span style="color: #0ea5e9; font-weight: 800; margin-right: 4px;">✓</span> <b>Dirección completa</b> y código postal.</li>
                                <li><span style="color: #0ea5e9; font-weight: 800; margin-right: 4px;">✓</span> <b>Municipio</b> y provincia.</li>
                                <li><span style="color: #0ea5e9; font-weight: 800; margin-right: 4px;">✓</span> <b>Teléfono y Web</b> (en registros que dispongan de ello).</li>
                                <li><span style="color: #0ea5e9; font-weight: 800; margin-right: 4px;">✓</span> <b>Email</b> (según disponibilidad pública).</li>
                            </ul>
                        </div>

                        <div style="background: linear-gradient(180deg, #faf5ff 0%, #ffffff 100%); padding: 24px; border-radius: 16px; border: 1px solid #e9d5ff; box-shadow: 0 4px 6px -1px rgba(147,51,234,0.05);">
                            <h4 style="font-size: 1.25rem; font-weight: 700; color: #7e22ce; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#a855f7" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                                Inteligencia de Negocio
                            </h4>
                            <ul style="list-style: none; padding: 0; margin: 0; color: var(--ae-slate); font-weight: 500; display: flex; flex-direction: column; gap: 10px;">
                                <li><span style="color: #a855f7; font-weight: 800; margin-right: 4px;">✓</span> <b>Administradores:</b> Cargos directivos activos.</li>
                                <li><span style="color: #a855f7; font-weight: 800; margin-right: 4px;">✓</span> <b>Subvenciones Públicas:</b> Cantidad recibida y suma total en euros.</li>
                                <li><span style="color: #a855f7; font-weight: 800; margin-right: 4px;">✓</span> <b>Contratos Públicos:</b> Licitaciones ganadas y suma total en euros.</li>
                                <li><span style="color: #a855f7; font-weight: 800; margin-right: 4px;">✓</span> <b>Finanzas:</b> Ventas estimadas y capital social.</li>
                            </ul>
                        </div>

                        <div style="background: linear-gradient(180deg, #ecfdf5 0%, #ffffff 100%); padding: 24px; border-radius: 16px; border: 1px solid #a7f3d0; box-shadow: 0 4px 6px -1px rgba(16,185,129,0.05);">
                            <h4 style="font-size: 1.25rem; font-weight: 700; color: #047857; margin-bottom: 12px; display: flex; align-items: center; gap: 8px;">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon></svg>
                                Perfilado Perfecto
                            </h4>
                            <ul style="list-style: none; padding: 0; margin: 0; color: var(--ae-slate); font-weight: 500; display: flex; flex-direction: column; gap: 10px;">
                                <li><span style="color: #10b981; font-weight: 800; margin-right: 4px;">✓</span> <b>CIF y Razón Social</b> oficial.</li>
                                <li><span style="color: #10b981; font-weight: 800; margin-right: 4px;">✓</span> <b>CNAE:</b> Código y descripción del sector.</li>
                                <li><span style="color: #10b981; font-weight: 800; margin-right: 4px;">✓</span> <b>Provincia</b> y Registro Mercantil.</li>
                                <li><span style="color: #10b981; font-weight: 800; margin-right: 4px;">✓</span> <b>Garantía:</b> Sólo empresas con Match superior al 85%.</li>
                            </ul>
                        </div>
                    </div>
                    
                    <div style="text-align: center; margin-top: 56px;">
                        <button onclick="document.getElementById('uploadForm').scrollIntoView({behavior: 'smooth'})" style="background: var(--ae-blue, #2563eb); color: white; border: none; padding: 16px 40px; font-size: 1.15rem; font-weight: 700; border-radius: 12px; cursor: pointer; transition: all 0.3s ease; box-shadow: 0 4px 6px -1px rgba(37,99,235,0.2);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 15px -3px rgba(37,99,235,0.3)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(37,99,235,0.2)'">
                            Analizar mi base de clientes
                        </button>
                    </div>

                </div>
            </div>
        </section>

        <!-- SECCIÓN FAQ SEO -->
        <section style="padding: 80px 20px; background: #ffffff;">
            <div class="container" style="max-width: 800px; margin: 0 auto;">
                <h2 style="font-size: 2rem; font-weight: 800; text-align: center; margin-bottom: 40px; color: var(--ae-dark);">Preguntas Frecuentes sobre el Buscador Lookalike</h2>
                
                <div style="margin-bottom: 24px; padding: 24px; background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; color: var(--ae-dark);">¿Cómo encuentra la IA a estas empresas similares?</h3>
                    <p style="color: var(--ae-slate); line-height: 1.6; margin: 0;">Nuestra tecnología de <strong>Inteligencia Artificial B2B</strong> cruza los CIFs que subes con nuestra base de datos de más de 3.5 millones de empresas en España. Analizamos patrones comunes (CNAE, volumen de facturación, capital social, antigüedad y región) para perfilar a tu "Buyer Persona" ideal y buscar automáticamente otras empresas que coincidan al 100% con ese mismo patrón.</p>
                </div>
                
                <div style="margin-bottom: 24px; padding: 24px; background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; color: var(--ae-dark);">¿Qué nivel de contacto obtendré en el Excel descargado?</h3>
                    <p style="color: var(--ae-slate); line-height: 1.6; margin: 0;">El listado final generado te proporcionará datos de altísimo valor comercial para tu prospección: nombres de los administradores activos, dirección postal completa, y, cuando está disponible en fuentes públicas, <strong>teléfono, email corporativo y página web oficial</strong>. Además, incluimos datos financieros y de licitaciones.</p>
                </div>
                
                <div style="margin-bottom: 0; padding: 24px; background: #f8fafc; border-radius: 16px; border: 1px solid #e2e8f0;">
                    <h3 style="font-size: 1.1rem; font-weight: 700; margin-bottom: 8px; color: var(--ae-dark);">¿Es legal para RGPD usar estas bases de datos para ventas?</h3>
                    <p style="color: var(--ae-slate); line-height: 1.6; margin: 0;">Sí. Todo nuestro <strong>buscador de empresas gemelas (lookalike)</strong> se nutre exclusivamente de fuentes públicas y oficiales (BORME, Registro Mercantil, Plataforma de Contratación del Estado). Tratar datos de contacto profesionales (B2B) bajo la premisa de "Interés Legítimo" es una práctica estándar y reconocida por la normativa vigente para acciones comerciales B2B.</p>
                </div>
            </div>
        </section>
    </main>

    <?= view('partials/footer') ?>

    <script>
        const dropZone = document.getElementById('dropZone');
        const fileInput = document.getElementById('fileInput');
        const uploadForm = document.getElementById('uploadForm');
        const fileNameDisplay = document.getElementById('file-name-display');
        const loadingOverlay = document.getElementById('loadingOverlay');

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults (e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.add('dragover'), false);
        });

        ['dragleave', 'drop'].forEach(eventName => {
            dropZone.addEventListener(eventName, () => dropZone.classList.remove('dragover'), false);
        });

        dropZone.addEventListener('drop', handleDrop, false);

        function handleDrop(e) {
            const dt = e.dataTransfer;
            const files = dt.files;
            
            if(files.length) {
                fileInput.files = files;
                handleFileSelect();
            }
        }

        fileInput.addEventListener('change', handleFileSelect);

        function handleFileSelect() {
            if(fileInput.files.length > 0) {
                const file = fileInput.files[0];
                fileNameDisplay.textContent = '✓ Archivo listo: ' + file.name;
                fileNameDisplay.style.display = 'block';
                fileNameDisplay.style.padding = '12px';
                fileNameDisplay.style.background = '#f0fdf4';
                fileNameDisplay.style.border = '1px solid #bbf7d0';
                fileNameDisplay.style.borderRadius = '8px';
                fileNameDisplay.style.color = '#166534';
                
                // Mostrar Paso 2 (Configuración)
                const step2 = document.getElementById('step2-config');
                step2.style.display = 'block';
                step2.style.animation = 'fadeInUp 0.6s ease forwards';
                
                // Hacer scroll suave hacia la configuración para que el usuario no se lo pierda
                setTimeout(() => {
                    step2.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 150);
            }
        }

        // --- Lógica de Pricing Dinámico ---
        const maxResultsSlider = document.getElementById('maxResultsSlider');
        const sliderValueDisplay = document.getElementById('sliderValueDisplay');
        const priceDisplay = document.getElementById('priceDisplay');
        
        function calculatePrice(count) {
            let basePrice = 9.00;
            if (count > 1000) {
                let extraCount = count - 1000;
                let tier2Count = Math.min(extraCount, 9000);
                let tier2Blocks = Math.ceil(tier2Count / 1000);
                basePrice += tier2Blocks * 5.00;
                
                if (extraCount > 9000) {
                    let tier3Count = extraCount - 9000;
                    let tier3Blocks = Math.ceil(tier3Count / 1000);
                    basePrice += tier3Blocks * 1.00;
                }
            }
            
            let maxCap = 149.00;
            if (basePrice > maxCap) {
                basePrice = maxCap;
            }
            return basePrice;
        }

        function updatePricing() {
            const count = parseInt(maxResultsSlider.value);
            sliderValueDisplay.textContent = count + ' prospectos';
            const price = calculatePrice(count);
            priceDisplay.innerHTML = price + '€ <span style="font-size: 1rem; color: #15803d; font-weight: 700;">+ IVA</span>';
        }

        if (maxResultsSlider) {
            maxResultsSlider.addEventListener('input', updatePricing);
            updatePricing(); // Init values
        }

        const btnAnalyze = document.getElementById('btnAnalyze');
        if (btnAnalyze) {
            btnAnalyze.addEventListener('click', () => {
                if(fileInput.files.length === 0) return;
                
                // Mostrar overlay de carga y ocultar form para que la caja se adapte al tamaño del loader
                uploadForm.style.display = 'none';
                loadingOverlay.style.display = 'flex';
                
                // Centrar el loader en la pantalla para evitar el salto brusco al ocultar el formulario
                setTimeout(() => {
                    document.querySelector('.upload-card').scrollIntoView({ behavior: 'smooth', block: 'center' });
                }, 50);
                
                // Enviar el formulario
                uploadForm.submit();
                
                const loaderTitle = document.getElementById('loaderTitle');
                const loaderSubtitle = document.getElementById('loaderSubtitle');
                
                const processMessages = [
                    "🔍 Extrayendo y validando CIFs...",
                    "🧠 IA analizando tu 'Buyer Persona'...",
                    "📊 Procesando sectores y facturaciones...",
                    "⚡ Cruzando datos en tiempo real...",
                    "🚀 Buscando clones idénticos en la BBDD...",
                    "🎯 Ordenando los mejores resultados..."
                ];
                
                const msgSuffix = "<br><span style='display:inline-block; margin-top:8px; font-size:0.85rem; font-weight:600;'><a href='{LINK}' target='_blank' style='color:#2563eb; text-decoration:underline;'>Ver producto ↗</a> <span style='color:#64748b; font-weight: 500;'>(se abrirá en nueva pestaña, no afecta al proceso actual)</span></span>";

                const marketingMessages = [
                    "💡 <b>Radar B2B:</b> Recibe alertas diarias de nuevas empresas creadas en España. ¡No te quedes atrás!" + msgSuffix.replace('{LINK}', '<?= site_url("empresas-nuevas") ?>'),
                    "💡 <b>Vértice:</b> Estudio de mercado instantáneo con IA para saber exactamente dónde abrir tu próximo local o negocio." + msgSuffix.replace('{LINK}', 'https://vertice.apiempresas.es'),
                    "💡 <b>Descarga a Medida:</b> Filtra nuestro directorio de 3.5M de empresas y descarga listas con teléfonos." + msgSuffix.replace('{LINK}', '<?= site_url("base-de-datos-de-empresas") ?>'),
                    "💡 <b>API REST:</b> Conecta tu CRM directamente a nuestra base de datos para enriquecer tus leads al instante." + msgSuffix.replace('{LINK}', '<?= site_url("api-empresas") ?>')
                ];

                let processIndex = 0;
                let marketingIndex = Math.floor(Math.random() * marketingMessages.length);

                const changeText = (element, newText) => {
                    element.style.opacity = 0;
                    setTimeout(() => {
                        element.innerHTML = newText;
                        element.style.opacity = 1;
                    }, 300);
                };

                // Bucle infinito mientras el navegador espera la respuesta del POST
                setInterval(() => {
                    changeText(loaderTitle, processMessages[processIndex]);
                    processIndex = (processIndex + 1) % processMessages.length;
                }, 2000);

                setInterval(() => {
                    changeText(loaderSubtitle, marketingMessages[marketingIndex]);
                    marketingIndex = (marketingIndex + 1) % marketingMessages.length;
                }, 4000);
            });
        }
    </script>
</body>
</html>
