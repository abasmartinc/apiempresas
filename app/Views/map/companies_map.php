<!doctype html>
<html lang="es">
<head>
    <?=view('partials/head') ?>

    <link rel="stylesheet" href="<?= base_url('public/css/styles.css') ?>?v=<?= time() ?>" />
    <link rel="stylesheet" href="<?= base_url('public/css/companies_map.css') ?>?v=<?= time() ?>" />

    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet.markercluster@1.5.3/dist/MarkerCluster.Default.css">
    <style>
        .dir-hero {
            padding: 30px 0 90px;
            background: linear-gradient(180deg, #090d16 0%, #0f172a 100%);
            color: #fff;
            position: relative;
            overflow: hidden;
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
        }
        .dir-hero::before {
            content: '';
            position: absolute;
            top: -20%;
            right: -10%;
            width: 40%;
            height: 80%;
            background: radial-gradient(circle, rgba(33, 82, 255, 0.12) 0%, transparent 70%);
            pointer-events: none;
        }
        .dir-hero .grad {
            background: linear-gradient(135deg, #60A5FA 0%, #34D399 100%);
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
</head>

<body>
<?=view('partials/header') ?>


<header class="dir-hero" style="text-align: center;">
    <div class="container" style="max-width: 1200px; margin: 0 auto;">
        <nav aria-label="Breadcrumb" class="breadcrumb" style="margin-bottom: 24px; font-size: 0.85rem; color: rgba(255,255,255,0.6); display: flex; justify-content: center; align-items: center;">
            <a href="<?= site_url() ?>" style="color: inherit; text-decoration: none;">Inicio</a>
            <span style="margin: 0 0.5rem;">/</span>
            <span aria-current="page" style="color: #fff;">Base de Datos de Empresas</span>
        </nav>

        <div style="display: inline-flex; align-items: center; gap: 8px; background: rgba(33, 82, 255, 0.15); color: #60A5FA; padding: 6px 14px; border-radius: 99px; font-size: 0.85rem; font-weight: 700; margin-bottom: 1.5rem; border: 1px solid rgba(33, 82, 255, 0.25);">
            <span style="display: inline-block; width: 6px; height: 6px; background: #34D399; border-radius: 99px; box-shadow: 0 0 8px #34D399;"></span>
            Base de Datos Oficial en Tiempo Real
        </div>
        
        <h1 style="font-size: clamp(2.2rem, 4vw, 3.5rem); font-weight: 800; letter-spacing: -0.03em; color: #ffffff; margin-bottom: 1.25rem; line-height: 1.1;">
            Base de Datos de <span class="grad">Empresas Españolas</span>
        </h1>
        
        <p style="font-size: 1.2rem; color: #cbd5e1; max-width: 750px; margin: 0 auto 1.5rem auto; line-height: 1.6;">
            Filtra por provincia, municipio y sector. Configura y descarga tu <strong>base de datos de empresas</strong> al instante. Listados B2B oficiales extraídos del BORME y listos para tu CRM.
        </p>

        <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 2rem;">
            <div style="display: flex; color: #fbbf24;">
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                <svg width="18" height="18" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
            </div>
            <span style="color: #94a3b8; font-size: 0.95rem;">Usado por <strong>+2.500</strong> equipos de ventas en España</span>
        </div>

        <div style="display: flex; gap: 16px; flex-wrap: wrap; justify-content: center;">
            <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(16, 185, 129, 0.15); color: #34D399; padding: 6px 14px; border-radius: 99px; font-size: 0.85rem; font-weight: 700; border: 1px solid rgba(16, 185, 129, 0.25);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                Datos Oficiales BORME
            </span>
            <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(96, 165, 250, 0.15); color: #60A5FA; padding: 6px 14px; border-radius: 99px; font-size: 0.85rem; font-weight: 700; border: 1px solid rgba(96, 165, 250, 0.25);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21.5 2v6h-6M21.34 15.57a10 10 0 1 1-.92-10.44l5.36-1.36"></path></svg>
                Actualización Diaria
            </span>
            <span style="display: inline-flex; align-items: center; gap: 6px; background: rgba(192, 132, 252, 0.15); color: #c084fc; padding: 6px 14px; border-radius: 99px; font-size: 0.85rem; font-weight: 700; border: 1px solid rgba(192, 132, 252, 0.25);">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                Descarga Segura (CSV)
            </span>
        </div>
    </div>
</header>

    <main style="padding: 0 0 100px 0; background-color: #f8fafc; min-height: 100vh;">
        <section class="container" style="max-width: 1200px; margin-top: -50px; position: relative; z-index: 10;">

            <div class="map2-layout">
                <!-- LEFT: Filters -->
                <aside class="filters">
                    <section class="b2b-card" style="display: flex; flex-direction: column; gap: 16px; padding: 16px 20px; margin-bottom: 16px; background: #ffffff; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                        <!-- TOP ROW: Filters -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; align-items: flex-end;">
                            <div class="field" style="margin: 0;">
                                <label style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; display: block;">Provincia <span style="color: #ef4444; font-size: 1rem; line-height: 0;">*</span></label>
                                <select id="f_province" class="input" style="width: 100%; border-radius: 8px; border: 1px solid #cbd5e1; padding: 8px 12px; background-color: #f8fafc;">
                                    <option value="">— Selecciona —</option>
                                </select>
                            </div>

                            <div class="field" style="margin: 0;">
                                <label style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; display: block;">Municipio</label>
                                <select id="f_municipality" class="input" style="width: 100%; border-radius: 8px; border: 1px solid #cbd5e1; padding: 8px 12px; background-color: #f8fafc;" disabled>
                                    <option value="">— Prov. antes —</option>
                                </select>
                            </div>

                            <div class="field" style="margin: 0;">
                                <label style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; display: block;">Sector</label>
                                <select id="c_sector" class="input" style="width: 100%; border-radius: 8px; border: 1px solid #cbd5e1; padding: 8px 12px; background-color: #f8fafc;">
                                    <option value="">— Todos —</option>
                                </select>
                            </div>

                            <div class="field" style="margin: 0;">
                                <label style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 6px; display: block;">Estado</label>
                                <select id="f_estado" class="input" style="width: 100%; border-radius: 8px; border: 1px solid #cbd5e1; padding: 8px 12px; background-color: #f8fafc;">
                                    <option value="">— Todos —</option>
                                    <option value="ACTIVA">Activa</option>
                                    <option value="INACTIVA">Inactiva</option>
                                    <option value="DISUELTA">Disuelta</option>
                                    <option value="EXTINGUIDA">Extinguida</option>
                                    <option value="CIERRE HOJA REGISTRAL">Cierre Hoja Registral</option>
                                </select>
                            </div>
                        </div>

                        <!-- BOTTOM ROW: Checkbox & Actions -->
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 16px; padding-top: 16px; border-top: 1px solid #f1f5f9; align-items: end;">
                            <div style="margin: 0;">
                                <label style="display: flex; align-items: center; justify-content: center; gap: 8px; cursor: pointer; user-select: none; background: #eff6ff; padding: 0 16px; border-radius: 8px; border: 1px solid #bfdbfe; transition: all 0.2s; height: 44px; margin: 0; width: 100%;">
                                    <input type="checkbox" id="f_has_phone" value="1" style="width: 16px; height: 16px; accent-color: #2563eb; cursor: pointer; margin: 0;">
                                    <span style="font-size: 0.85rem; font-weight: 700; color: #1e40af; line-height: 1;">Solo con Teléfono</span>
                                </label>
                            </div>
                            
                            <div class="date-span" style="display: flex; align-items: center; justify-content: space-between; gap: 12px; background: #f8fafc; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 16px; height: 44px;">
                                <span style="font-size: 0.75rem; font-weight: 700; color: #475569; text-transform: uppercase;">Creadas:</span>
                                <input type="date" id="f_date_min" style="flex: 1; border: none; background: transparent; font-size: 0.85rem; color: #334155; outline: none; cursor: pointer; text-align: center; width: 100%;">
                                <span style="color: #94a3b8; font-weight: bold;">-</span>
                                <input type="date" id="f_date_max" style="flex: 1; border: none; background: transparent; font-size: 0.85rem; color: #334155; outline: none; cursor: pointer; text-align: center; width: 100%;">
                            </div>
                            
                            <div style="margin: 0;">
                                <button id="btnSearch" class="b2b-btn b2b-btn--primary" type="button" style="padding: 0 24px; height: 44px; border-radius: 8px; font-weight: bold; font-size: 0.95rem; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2); transition: transform 0.1s; display: flex; align-items: center; justify-content: center; gap: 8px; margin: 0; width: 100%;">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                    <span>Buscar</span>
                                </button>
                            </div>
                        </div>

                        <!-- AI Assistant Row -->
                        <div style="display: flex; align-items: center; justify-content: space-between; background: #f5f3ff; border: 1px solid #ddd6fe; padding: 12px 16px; border-radius: 8px; margin-top: 16px; gap: 16px;">
                            <div style="font-size: 0.9rem; color: #5b21b6; font-weight: 500; flex: 1; line-height: 1.4;">
                                ¿No tienes claro qué buscar?<br>
                                <span style="font-size: 0.85rem; color: #7c3aed; font-weight: 400;">Pídele a nuestro asistente inteligente que prepare los filtros por ti (ej: "Constructoras en Valencia").</span>
                            </div>
                            <button id="btnToggleAi" class="b2b-btn" style="background: linear-gradient(135deg, #8b5cf6, #6d28d9); color: white; border: none; padding: 0 20px; height: 40px; font-weight: bold; font-size: 0.9rem; border-radius: 6px; box-shadow: 0 4px 6px -1px rgba(139, 92, 246, 0.3); transition: transform 0.2s, box-shadow 0.2s; display: flex; align-items: center; gap: 6px; white-space: nowrap; flex-shrink: 0; margin: 0;" type="button" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 6px 8px -1px rgba(139, 92, 246, 0.4)';" onmouseout="this.style.transform='none'; this.style.boxShadow='0 4px 6px -1px rgba(139, 92, 246, 0.3)';">
                                <span style="font-size: 1rem;">✨</span> Usar Inteligencia Artificial
                            </button>
                        </div>
                    </section>
                </aside>

                
                <!-- Wrapper for Chat and Map side-by-side -->
                <div id="chatMapWrapper" style="display: flex; gap: 24px; align-items: stretch; transition: all 0.3s;">
                    <style>
                        @media (min-width: 768px) {
                            .date-span { grid-column: span 2; }
                        }
                        @media (max-width: 767px) {
                            .date-span { grid-column: span 1; }
                        }
                        @keyframes spin { 100% { transform: rotate(360deg); } }
                        .icon-spin { animation: spin 1s linear infinite; }
                        #aiChatPanel {
                            flex-direction: column; 
                            flex-shrink: 0; 
                            height: 100%; 
                            padding: 0; 
                            overflow: hidden; 
                            box-shadow: 0 10px 25px -5px rgba(139, 92, 246, 0.2);
                            
                            /* Animación de apertura */
                            width: 0;
                            opacity: 0;
                            visibility: hidden;
                            margin-right: -24px;
                            transform: translateX(-20px);
                            border: 0px solid #c4b5fd;
                            transition: all 0.4s cubic-bezier(0.25, 1, 0.5, 1);
                        }
                        #aiChatPanel.is-open {
                            width: 450px;
                            min-height: 600px;
                            opacity: 1;
                            visibility: visible;
                            margin-right: 0;
                            transform: translateX(0);
                            border-width: 1px;
                        }
                    </style>
                    <!-- AI Chat Panel -->
                    <section id="aiChatPanel" class="b2b-card" style="display: flex;">
                        <div style="width: 450px; flex: 1; display: flex; flex-direction: column; height: 100%;">
                        <div style="background: linear-gradient(135deg, #1e1b4b, #4c1d95); padding: 16px; color: white; display: flex; justify-content: space-between; align-items: center;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <span style="font-size: 1.5rem;">✨</span>
                                <div>
                                    <h3 style="margin: 0; font-size: 1.1rem; font-weight: 800; letter-spacing: -0.01em;">Asistente B2B</h3>
                                    <div style="font-size: 0.75rem; opacity: 0.8;">Búsqueda conversacional</div>
                                </div>
                            </div>
                            <button id="btnCloseAi" style="background: transparent; border: none; color: white; font-size: 1.2rem; cursor: pointer; opacity: 0.7; transition: opacity 0.2s;" onmouseover="this.style.opacity='1'" onmouseout="this.style.opacity='0.7'">✕</button>
                        </div>
                        
                        <div id="aiChatMessages" style="flex: 1; overflow-y: auto; padding: 16px; display: flex; flex-direction: column; gap: 16px; background: #f8fafc;">
                            <div style="padding: 12px 16px; border-radius: 12px 12px 12px 0; align-self: flex-start; max-width: 90%; font-size: 0.95rem; line-height: 1.5; box-shadow: 0 2px 4px rgba(0,0,0,0.02); background: white; border: 1px solid #e2e8f0; color: #334155;">
                                ¡Hola! Soy tu asistente de IA. Dime qué empresas buscas (por ej: "Empresas de Madrid", "Talleres mecánicos", "Empresas activas") y prepararé el filtro por ti.
                            </div>
                        </div>
                        
                        <div style="padding: 16px; background: white; border-top: 1px solid #e2e8f0;">
                            <div style="display: flex; gap: 8px; position: relative;">
                                <input type="text" id="aiChatInput" placeholder="Ej: Constructoras en Valencia..." style="flex: 1; padding: 12px 16px; padding-right: 48px; border: 1px solid #cbd5e1; border-radius: 99px; font-size: 0.95rem; outline: none; transition: border-color 0.2s;" onfocus="this.style.borderColor='#8b5cf6'" onblur="this.style.borderColor='#cbd5e1'">
                                <button id="aiChatSend" style="position: absolute; right: 6px; top: 6px; bottom: 6px; width: 34px; border: none; border-radius: 50%; background: #8b5cf6; color: white; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#7c3aed'" onmouseout="this.style.background='#8b5cf6'">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-left: -2px;"><line x1="22" y1="2" x2="11" y2="13"></line><polygon points="22 2 15 22 11 13 2 9 22 2"></polygon></svg>
                                </button>
                            </div>
                        </div>
                        </div>
                    </section>
<section class="map-area" style="flex: 1; display: flex; flex-direction: column; min-width: 0;">
                    <div id="mapContainerWrapper" class="b2b-card" style="display: none; padding: 0; overflow: hidden; margin-bottom: 24px; flex: 1;">
                        <div style="background: #f8fafc; padding: 12px 16px; font-size: 0.95rem; font-weight: 700; color: #111827; display: flex; justify-content: space-between; border-bottom: 1px solid #e5e7eb;">
                            <span>Área de búsqueda</span>
                            <span style="font-weight: 400; color: #6b7280; font-size: 0.85rem;" id="statusText">Configura filtros y pulsa “Buscar”.</span>
                        </div>

                        <div class="map-wrap">
                            <div id="map" class="map"></div>
                            
                            <div id="emptyOverlay" class="empty-overlay">
                                <div class="eo-card">
                                    <div class="eo-pill">Base de Datos de Empresas</div>
                                    <div class="eo-title">Acota tu segmento B2B</div>
                                    <div class="eo-text">
                                        Selecciona los filtros arriba y pulsa <strong>Buscar</strong>, o haz clic en <strong>✨ IA</strong> para pedirle a nuestro asistente conversacional que configure la búsqueda por ti.
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div style="padding: 12px 16px; font-size: 0.85rem; color: #475569; display: flex; flex-direction: column; gap: 8px; background: #f8fafc; border-top: 1px solid #e5e7eb;">
                            <div style="display: flex; justify-content: space-between; color: #6b7280;">
                                <span>Se muestran hasta <strong>5.000</strong> puntos en el mapa por búsqueda.</span>
                            </div>
                            <div style="display: flex; gap: 10px; align-items: flex-start; background: #eff6ff; padding: 10px 12px; border-radius: 8px; border: 1px solid #bfdbfe;">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2" style="flex-shrink: 0; margin-top: 2px;"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                                <span style="color: #1e3a8a; line-height: 1.4;"><strong>Nota sobre el mapa:</strong> El listado de resultados y la descarga en CSV contienen siempre el 100% del censo. El mapa visual solo dibuja los pines de aquellas empresas de las que disponemos de coordenadas geográficas exactas.</span>
                            </div>
                        </div>
                    
                    </div> <!-- end mapContainerWrapper -->

<div id="resultsCardWrapper" class="b2b-card" style="display: block; margin-bottom: 24px; padding: 0; overflow: hidden; background: white; border-radius: 12px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);">
                        <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; padding: 16px 20px; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">
                            <h2 id="resultsTitle" style="font-size: 1.2rem; font-weight: 800; color: #0f172a; margin: 0;">Directorio y Buscador de Empresas</h2>
                            <button id="toggleMapBtn" style="background: white; color: #334155; border: 1px solid #cbd5e1; padding: 6px 14px; border-radius: 99px; font-weight: 700; font-size: 0.9rem; cursor: pointer; transition: all 0.2s; display: flex; align-items: center; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onclick="toggleMapVisibility()" onmouseover="this.style.background=\'#f1f5f9\'" onmouseout="this.style.background=\'white\'">
                                📍 Mostrar Mapa
                            </button>
                        </div>
                        
                        <div id="initialPlaceholder" style="padding: 60px 20px; text-align: center; color: #64748b; font-size: 1.1rem;">
                            <span style="font-size: 2rem; margin-bottom: 12px; display: block;">✨</span>
                            Pulsa el botón <strong>✨ Inteligencia Artificial</strong> de arriba o usa los filtros manuales<br>para empezar a extraer empresas aquí.
                        </div>

                        <div id="results" class="b2b-data-list results" style="display: none; flex-direction: column; padding: 16px; gap: 4px; background: white;"></div>
                    </div>

                    

                </section> <!-- end map-area -->
            </div> <!-- end chatMapWrapper -->

            <!-- SEO & FAQ Section Moved Here -->
                    <div class="b2b-card" style="padding: 40px 32px; background: #fff; border-radius: 16px; border: 1px solid #e2e8f0; margin-top: 32px; margin-bottom: 24px;">
                        <!-- Benefits -->
                        <h2 style="font-size: 1.8rem; font-weight: 900; color: #0f172a; text-align: center; margin-bottom: 40px; letter-spacing: -0.03em; line-height: 1.2;">Exporta al instante tu base de datos B2B de empresas españolas</h2>
                        
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px; margin-bottom: 48px;">
                            <div>
                                <div style="background: #eff6ff; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                                </div>
                                <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Listos para Telemarketing</h3>
                                <p style="color: #475569; font-size: 0.9rem; line-height: 1.6; margin: 0;">Los registros incluyen teléfono, dirección postal, estado de actividad y sector CNAE preciso. Formato perfecto para importar a tu CRM.</p>
                            </div>
                            <div>
                                <div style="background: #f0fdf4; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                </div>
                                <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Datos Oficiales (BORME)</h3>
                                <p style="color: #475569; font-size: 0.9rem; line-height: 1.6; margin: 0;">Toda la información es pública, extraída y consolidada del Registro Mercantil. Asegura que tus campañas B2B cumplan con la normativa.</p>
                            </div>
                            <div>
                                <div style="background: #fdf4ff; width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 12px;">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#d946ef" stroke-width="2.5"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
                                </div>
                                <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-bottom: 6px;">Pago justo por volumen</h3>
                                <p style="color: #475569; font-size: 0.9rem; line-height: 1.6; margin: 0;">A diferencia de otros directorios, aquí solo pagas por la provincia y sector exactos que necesitas. Desde muy pocos euros.</p>
                            </div>
                        </div>

                        <!-- FAQ -->
                        <div style="background: #f8fafc; border-radius: 12px; padding: 24px;">
                            <h3 style="font-size: 1.3rem; font-weight: 800; color: #0f172a; margin-bottom: 20px;">Preguntas Frecuentes</h3>
                            
                            <div style="margin-bottom: 16px;">
                                <h4 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 6px;">¿En qué formato se descarga la base de datos?</h4>
                                <p style="color: #475569; font-size: 0.9rem; line-height: 1.6; margin: 0;">El listado se exporta automáticamente en formato CSV (delimitado por comas), estructurado en columnas ordenadas y limpias, compatible de forma nativa con Excel, Google Sheets y cualquier CRM.</p>
                            </div>
                            
                            <div style="margin-bottom: 16px;">
                                <h4 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 6px;">¿Incluye emails de las empresas?</h4>
                                <p style="color: #475569; font-size: 0.9rem; line-height: 1.6; margin: 0;">Para cumplir rigurosamente con la Ley Orgánica de Protección de Datos (RGPD) en España, nuestra base de datos prioriza los datos registrales públicos: teléfonos fijos/móviles corporativos, dirección física y datos de constitución (CNAE).</p>
                            </div>

                            <div>
                                <h4 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin-bottom: 6px;">¿Cada cuánto se actualizan los datos?</h4>
                                <p style="color: #475569; font-size: 0.9rem; line-height: 1.6; margin: 0;">Procesamos de forma ininterrumpida las actas del BORME. Si una empresa se disuelve o cambia de actividad, lo verás reflejado. Por eso nuestro mapa te permite filtrar exclusivamente por empresas con Estado "ACTIVA".</p>
                            </div>
                        </div>

        </div>

        </div> <!-- end map2-layout -->
    </section> <!-- end container -->
</main>

<!-- Sticky CTA Footer -->
    <div id="stickyCtaFooter" style="display: none; position: fixed; bottom: 0; left: 0; right: 0; background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(8px); padding: 16px; box-shadow: 0 -4px 20px rgba(0,0,0,0.08); z-index: 9990; border-top: 1px solid #e2e8f0; display: flex; justify-content: center;">
        <div id="checkoutBtnContainer" style="display: flex; gap: 12px; align-items: center; justify-content: center; flex-wrap: wrap; max-width: 1200px; width: 100%;"></div>
    </div>

    <!-- Lead Magnet Modal -->
                    

                

<div id="leadModal" style="display: none; position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 99999; align-items: center; justify-content: center;">
        <div style="background: #fff; border-radius: 16px; padding: 32px; width: 100%; max-width: 450px; box-shadow: 0 20px 25px -5px rgba(0,0,0,0.1); position: relative; margin: 16px;">
            <button onclick="closeLeadModal()" style="position: absolute; top: 16px; right: 16px; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #64748b; padding: 0; line-height: 1;">&times;</button>
            <div style="width: 48px; height: 48px; background: #eff6ff; border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-bottom: 20px;">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
            </div>
            <h3 style="font-size: 1.4rem; font-weight: 800; color: #0f172a; margin: 0 0 12px 0;">Consigue una muestra gratis</h3>
            <p style="color: #475569; font-size: 0.95rem; line-height: 1.5; margin: 0 0 24px 0;">Te enviamos ahora mismo un archivo CSV con 20 empresas reales de esta búsqueda para que compruebes la calidad de los datos.</p>
            <form id="leadForm" onsubmit="submitLeadForm(event)">
                <div style="margin-bottom: 16px;">
                    <label style="display: block; font-size: 0.85rem; font-weight: 700; color: #1e293b; margin-bottom: 6px;">Tu correo electrónico</label>
                    <input type="email" id="leadEmail" required placeholder="ejemplo@empresa.com" style="width: 100%; height: 44px; border: 1px solid #cbd5e1; border-radius: 8px; padding: 0 12px; font-size: 0.95rem; outline: none; transition: border-color 0.2s; box-sizing: border-box;" onfocus="this.style.borderColor='#2563eb'">
                </div>
                <button type="submit" id="leadSubmitBtn" style="width: 100%; height: 44px; background: #2563eb; color: #fff; border: none; border-radius: 8px; font-weight: 700; font-size: 1rem; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#1d4ed8'" onmouseout="this.style.background='#2563eb'">
                    Enviar muestra a mi correo
                </button>
            </form>
            <div id="leadSuccess" style="display: none; text-align: center; color: #10b981; font-weight: 700; margin-top: 16px; background: #f0fdf4; padding: 12px; border-radius: 8px;">
                ¡Muestra enviada! Revisa tu bandeja de entrada.
            </div>
        </div>
    </div>



    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "FAQPage",
      "mainEntity": [
        {
          "@type": "Question",
          "name": "¿En qué formato se descarga la base de datos?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "El listado se exporta automáticamente en formato CSV, estructurado en columnas ordenadas y limpias, compatible de forma nativa con Excel, Google Sheets y cualquier CRM."
          }
        },
        {
          "@type": "Question",
          "name": "¿Incluye emails de las empresas?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Para cumplir rigurosamente con la Ley Orgánica de Protección de Datos (RGPD) en España, nuestra base de datos prioriza los datos registrales públicos: teléfonos fijos/móviles corporativos, dirección física y datos de constitución (CNAE)."
          }
        },
        {
          "@type": "Question",
          "name": "¿Cada cuánto se actualizan los datos?",
          "acceptedAnswer": {
            "@type": "Answer",
            "text": "Procesamos de forma ininterrumpida las actas del BORME. Si una empresa se disuelve o cambia de actividad, lo verás reflejado. Por eso nuestro mapa te permite filtrar exclusivamente por empresas con Estado ACTIVA."
          }
        }
      ]
    }
    </script>
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Dataset",
      "name": "Base de Datos de Empresas Españolas",
      "description": "Listado oficial y actualizado de empresas de España, procesado desde el BORME. Incluye razón social, CIF, CNAE, cargos directivos, teléfonos y estado actual para campañas B2B.",
      "keywords": [
        "empresas españolas",
        "base de datos B2B",
        "directorio de empresas",
        "listado de empresas",
        "CNAE",
        "BORME"
      ],
      "creator": {
        "@type": "Organization",
        "name": "APIEmpresas"
      },
      "inLanguage": "es-ES",
      "license": "https://creativecommons.org/licenses/by/4.0/",
      "distribution": [
        {
          "@type": "DataDownload",
          "encodingFormat": "text/csv"
        }
      ]
    }
    </script>
    <?=view('partials/footer') ?>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://unpkg.com/leaflet.markercluster@1.5.3/dist/leaflet.markercluster.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js" defer></script>

<style>
    /* Estilos para integrar Select2 con el diseño B2B */
    .select2-container--default .select2-selection--single {
        height: 42px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background-color: #fff;
        display: flex;
        align-items: center;
        transition: all 0.2s;
    }
    .select2-container--default .select2-selection--single:focus,
    .select2-container--default.select2-container--open .select2-selection--single {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
        outline: none;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #0f172a;
        line-height: normal;
        padding-left: 12px;
        padding-right: 30px;
        font-size: 0.95rem;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 40px;
        right: 8px;
    }
    .select2-dropdown {
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        box-shadow: 0 10px 25px -5px rgba(0,0,0,0.1);
        z-index: 9999;
    }
    .select2-search--dropdown .select2-search__field {
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        padding: 8px 12px;
    }
    .select2-search--dropdown .select2-search__field:focus {
        border-color: #2563eb;
        outline: none;
    }
    .select2-container--default .select2-results__option--highlighted.select2-results__option--selectable {
        background-color: #eff6ff;
        color: #1e3a8a;
    }
    .select2-container--default .select2-results__option {
        padding: 8px 12px;
        font-size: 0.9rem;
    }
</style>

<script>
    (function () {
        const el = (id) => document.getElementById(id);

        const fProvince = el('f_province');
        const fMunicipality = el('f_municipality');

        const cSector = el('c_sector');

        const btnSearch = el('btnSearch');

        const statusText = el('statusText');
        const emptyOverlay = el('emptyOverlay');
        const resultsEl = el('results');

        // ---------- State ----------
        let currentResults = [];
        let currentPage = 1;
        const itemsPerPage = 50;

        // ---------- Map init ----------
        const map = L.map('map', { preferCanvas: true }).setView([40.4168, -3.7038], 6);
        window.map = map; // Export to global scope for toggleMapVisibility
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);

        const cluster = L.markerClusterGroup({ chunkedLoading: true, maxClusterRadius: 55 });
        map.addLayer(cluster);

        // ---------- Helpers ----------
        function escapeHtml(s) {
            return (s ?? '').toString()
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;');
        }
        function escapeAttr(s){ return escapeHtml(s).replaceAll('"', '&quot;'); }

        function selectedDataName(selectEl) {
            return selectEl?.selectedOptions?.[0]?.dataset?.name || '';
        }

        function selectedDataCode(selectEl) {
            return selectEl?.selectedOptions?.[0]?.dataset?.code || '';
        }

        function setDisabledSelect(selectEl, placeholder) {
            selectEl.disabled = true;
            selectEl.innerHTML = `<option value="">${placeholder}</option>`;
        }

        // Extrae un prefijo numérico 2-4 dígitos de:
        //  - dataset.code (ideal)
        //  - value/slug
        //  - texto visible (si empieza por "62 ..." o "6201 - ...")
        function extractCnaePrefixFromOption(selectEl) {
            const opt = selectEl?.selectedOptions?.[0];
            if (!opt) return '';

            const c1 = (opt.dataset?.code || '').trim();
            if (/^\d{2,4}$/.test(c1)) return c1;

            const rawCandidates = [
                (opt.value || ''),
                (opt.dataset?.name || ''),
                (opt.textContent || '')
            ].map(x => (x ?? '').toString().trim());

            for (const s of rawCandidates) {
                // busca 2-4 dígitos al inicio o tras separadores
                const m = s.match(/(^|\s|-|–|—)(\d{2,4})(\s|$)/);
                if (m && m[2]) return m[2];
                // fallback: quita no-dígitos y toma 2-4 primeros si tiene sentido
                const digits = s.replace(/[^0-9]/g, '');
                if (digits.length >= 2) return digits.slice(0, Math.min(4, digits.length));
            }
            return '';
        }

        function getSelectedCnaePrefix() {
            return extractCnaePrefixFromOption(cSector);
        }

        // bbox “España” para cuando no quieras limitar por mapa.
        // Incluye Península + Baleares + Canarias (aprox).
        function getSpainBbox() {
            return {
                north: 44.95,
                south: 27.50,
                east:   5.30,
                west: -18.40
            };
        }

        function getEffectiveBbox() {
            // Si el mapa está oculto, devolvemos el área completa de España
            // para no restringir artificialmente los resultados a un punto 0x0
            const mapContainer = document.getElementById('mapContainerWrapper');
            if (!mapContainer || mapContainer.style.display === 'none') {
                return getSpainBbox();
            }

            // Si está visible, limitamos al área que está viendo el usuario en pantalla
            const b = map.getBounds();
            return {
                north: b.getNorth(),
                south: b.getSouth(),
                east:  b.getEast(),
                west:  b.getWest()
            };
        }

        // ---------- Load dropdown data ----------
        async function loadProvinces() {
            const res = await fetch('<?= site_url('api/geo/provinces') ?>', { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            const rows = json.data || [];

            fProvince.innerHTML =
                `<option value="">— Selecciona —</option>` +
                rows.map(r =>
                    `<option value="${r.id}" data-name="${escapeAttr(r.pro_name)}">${escapeHtml(r.pro_name)}</option>`
                ).join('');

            setDisabledSelect(fMunicipality, '— Selecciona provincia antes —');
            if (window.$ && $('#f_province').data('select2')) $('#f_province').trigger('change');
        }

        async function loadMunicipalities(provinceId) {
            fMunicipality.disabled = true;
            fMunicipality.innerHTML = `<option value="">Cargando…</option>`;
            if ($('#f_municipality').data('select2')) $('#f_municipality').trigger('change');

            const url = '<?= site_url('api/geo/municipalities') ?>?province_id=' + encodeURIComponent(provinceId);
            const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
            const json = await res.json();
            const rows = json.data || [];

            fMunicipality.innerHTML =
                `<option value="">— Todos —</option>` +
                rows.map(r =>
                    `<option value="${r.id}" data-name="${escapeAttr(r.mun_name)}">${escapeHtml(r.mun_name)}</option>`
                ).join('');

            fMunicipality.disabled = false;
            if ($('#f_municipality').data('select2')) $('#f_municipality').trigger('change');
        }

        async function loadSectors() {
            cSector.disabled = true;
            cSector.innerHTML = `<option value="">Cargando…</option>`;

            // api/cnae/groups ahora no requiere section_id
            const res = await fetch('<?= site_url('api/cnae/groups') ?>', {
                headers: { 'Accept': 'application/json' }
            });
            const json = await res.json();
            const rows = json.data || [];

            cSector.innerHTML =
                `<option value="">— Todos —</option>` +
                rows.map(r => `<option value="${escapeAttr(r.slug)}">${escapeHtml(r.name)}</option>`).join('');

            cSector.disabled = false;
            if ($('#c_sector').data('select2')) $('#c_sector').trigger('change');
        }



        // El evento de cambio se mueve a DOMContentLoaded para usar jQuery y Select2



        // ---------- Search / Export ----------
        async function search(page = 1) {
            const bbox = getEffectiveBbox();

            const provinceText = selectedDataName(fProvince).trim();
            const municipalityText = selectedDataName(fMunicipality).trim();

            // CNAE: usa el nivel más bajo disponible
            const cnaePrefix = getSelectedCnaePrefix();
            const sectorOptionText = cSector.options[cSector.selectedIndex]?.text;
            const cnaeTextValue = (sectorOptionText && sectorOptionText !== '— Todos —') ? sectorOptionText : '';
            const estadoText = document.getElementById('f_estado').value;
            const hasPhone = document.getElementById('f_has_phone').checked ? 1 : 0;

            const params = new URLSearchParams({
                north: bbox.north,
                south: bbox.south,
                east:  bbox.east,
                west:  bbox.west,
                limit: 5000,

                province: provinceText,
                municipality: municipalityText,

                cnae_prefix: cnaePrefix,
                cnae_text: cnaeTextValue,

                estado: estadoText,
                has_phone: hasPhone,
                only_geocoded: 1,

                use_bbox: 1,
                page: page
            });
            const fDateMin = document.getElementById('f_date_min').value;
            const fDateMax = document.getElementById('f_date_max').value;
            if (fDateMin) params.append('date_min', fDateMin);
            if (fDateMax) params.append('date_max', fDateMax);
            
            statusText.textContent = 'Buscando empresas…';
            btnSearch.disabled = true;
            btnSearch.innerHTML = `<svg class="icon-spin" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12a9 9 0 1 1-6.219-8.56"></path></svg> <span>Buscando...</span>`;

            const initialPlaceholder = document.getElementById('initialPlaceholder');
            if (initialPlaceholder) {
                initialPlaceholder.style.display = 'none';
            }

            if (resultsEl) {
                resultsEl.style.display = 'flex';
                resultsEl.style.opacity = '1';
                resultsEl.style.pointerEvents = 'none';
                resultsEl.style.transition = 'opacity 0.2s';
                
                if (resultsEl.innerHTML.trim() === '') {
                    resultsEl.innerHTML = `<div style="padding: 60px 20px; text-align: center; color: #8b5cf6;">
                        <svg style="animation: spin 1s linear infinite; width: 40px; height: 40px; margin: 0 auto 16px;" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle><path d="M12 2a10 10 0 0 1 10 10"></path></svg>
                        <div style="font-weight: 800; font-size: 1.1rem; color: #0f172a;">Analizando base de datos...</div>
                        <div style="font-size: 0.9rem; color: #64748b; margin-top: 8px;">Extrayendo empresas con tus criterios</div>
                    </div>
                    <style>@keyframes spin { 100% { transform: rotate(360deg); } }</style>`;
                } else {
                    resultsEl.style.opacity = '0.4';
                }
            }

            try {
                const res = await fetch('<?= site_url('api/map/search') ?>?' + params.toString(), {
                    headers: { 'Accept': 'application/json' }
                });

                const json = await res.json();
                if (!res.ok || json.success === false) {
                    const msg = json.message || json.error || 'no se pudo buscar';
                    statusText.textContent = 'Error: ' + msg;
                    return;
                }

                const listData = json.list_data || [];
                let mapData = json.map_data || [];
                const meta = json.meta || {};

                // Filtrar valores atípicos (outliers) geográficos si se busca por provincia.
                // Esto elimina empresas de Almería que por error de coordenadas caen en Bilbao.
                if (provinceText && provinceText !== '— Selecciona —' && provinceText !== '— Todos —' && mapData.length > 10) {
                    const lats = mapData.map(d => parseFloat(d.lat)).filter(n => !isNaN(n)).sort((a,b) => a-b);
                    const lngs = mapData.map(d => parseFloat(d.lng)).filter(n => !isNaN(n)).sort((a,b) => a-b);
                    
                    if (lats.length > 0 && lngs.length > 0) {
                        const q1Lat = lats[Math.floor(lats.length * 0.25)];
                        const q3Lat = lats[Math.floor(lats.length * 0.75)];
                        const latTolerance = Math.max(1.5 * (q3Lat - q1Lat), 0.8); // Min 0.8 degrees (~90km) tolerance
                        
                        const q1Lng = lngs[Math.floor(lngs.length * 0.25)];
                        const q3Lng = lngs[Math.floor(lngs.length * 0.75)];
                        const lngTolerance = Math.max(1.5 * (q3Lng - q1Lng), 0.8);
                        
                        mapData = mapData.filter(d => {
                            const lat = parseFloat(d.lat);
                            const lng = parseFloat(d.lng);
                            return !isNaN(lat) && !isNaN(lng) && 
                                   lat >= (q1Lat - latTolerance) && lat <= (q3Lat + latTolerance) &&
                                   lng >= (q1Lng - lngTolerance) && lng <= (q3Lng + lngTolerance);
                        });
                    }
                }

                if (page === 1) {
                    emptyOverlay.style.display = 'none';
                    const resultsCardWrapper = document.getElementById('resultsCardWrapper');
                    if (resultsCardWrapper) resultsCardWrapper.style.display = 'block';

                    cluster.clearLayers();
                    for (const r of mapData) {
                        if (!r.lat || !r.lng) continue;
                        const m = L.marker([parseFloat(r.lat), parseFloat(r.lng)]);
                        m.bindPopup(popupHtml(r));
                        cluster.addLayer(m);
                    }

                    let titleStr = `Encontradas ${new Intl.NumberFormat('es-ES').format(meta.total_count ?? listData.length)} empresas`;
                    if (provinceText && provinceText !== '— Selecciona —' && provinceText !== '— Todos —') {
                        titleStr += ` en ${municipalityText && municipalityText !== '— Todos —' ? municipalityText : provinceText}`;
                    }
                    const resultsTitleEl = document.getElementById('resultsTitle');
                    if (resultsTitleEl) {
                        resultsTitleEl.textContent = titleStr;
                    }
                }

                renderPage(meta, listData);

                const checkoutBtnContainer = document.getElementById('checkoutBtnContainer');
                if (meta.total_count > 0 && provinceText && provinceText !== '— Selecciona —' && provinceText !== '— Todos —') {
                    let checkoutUrl = `<?= site_url('billing/directory_checkout') ?>?provincia=${encodeURIComponent(provinceText)}`;
                    if (cnaePrefix) {
                        checkoutUrl += `&cnae=${encodeURIComponent(cnaePrefix)}&sector=${encodeURIComponent(cnaeTextValue || provinceText)}`;
                    } else if (cnaeTextValue) {
                        checkoutUrl += `&cnae_text=${encodeURIComponent(cnaeTextValue)}&sector=${encodeURIComponent(cnaeTextValue)}`;
                    } else if (municipalityText && municipalityText !== '— Todos —') {
                        checkoutUrl += `&municipio=${encodeURIComponent(municipalityText)}`;
                    }
                    const fEstado = document.getElementById('f_estado');
                    if (fEstado && fEstado.value) {
                        checkoutUrl += `&estado=${encodeURIComponent(fEstado.value)}`;
                    }
                    const fHasPhone = document.getElementById('f_has_phone');
                    if (fHasPhone && fHasPhone.checked) {
                        checkoutUrl += `&has_phone=1`;
                    }
                    if (fDateMin) {
                        checkoutUrl += `&date_min=${encodeURIComponent(fDateMin)}`;
                    }
                    if (fDateMax) {
                        checkoutUrl += `&date_max=${encodeURIComponent(fDateMax)}`;
                    }

                    checkoutBtnContainer.innerHTML = `
                        <div style="display: flex; flex-direction: column; align-items: center; gap: 8px;">
                            <div style="display: flex; gap: 12px; align-items: center; justify-content: center; flex-wrap: wrap;">
                                <a href="${checkoutUrl}" onclick="if(window.trackEvent) trackEvent('map_checkout_click');" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: #10b981; color: #fff; padding: 12px 28px; border-radius: 99px; font-weight: 800; font-size: 1.05rem; text-decoration: none; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); transition: all 0.2s; white-space: nowrap;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 16px rgba(16, 185, 129, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(16, 185, 129, 0.3)';">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg>
                                    Descargar CSV (${new Intl.NumberFormat('es-ES').format(meta.total_count)} <span style="font-size: 0.85rem; font-weight: 600; opacity: 0.9;">empresas</span>) · ${meta.dynamic_price}€ + IVA
                                </a>
                                <button type="button" onclick="openLeadModal()" style="display: inline-flex; align-items: center; justify-content: center; gap: 8px; background: #fff; color: #1e40af; border: 2px solid #e0e7ff; padding: 10px 24px; border-radius: 99px; font-weight: 800; font-size: 1rem; cursor: pointer; transition: all 0.2s; white-space: nowrap; box-shadow: 0 2px 4px rgba(0,0,0,0.05);" onmouseover="this.style.borderColor='#1e40af'; this.style.backgroundColor='#eff6ff'; this.style.transform='translateY(-2px)';" onmouseout="this.style.borderColor='#e0e7ff'; this.style.backgroundColor='#fff'; this.style.transform='translateY(0)';">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 12 20 22 4 22 4 12"></polyline><rect x="2" y="7" width="20" height="5"></rect><line x1="12" y1="22" x2="12" y2="7"></line><path d="M12 7H7.5a2.5 2.5 0 0 1 0-5C11 2 12 7 12 7z"></path><path d="M12 7h4.5a2.5 2.5 0 0 0 0-5C13 2 12 7 12 7z"></path></svg>
                                    Muestra Gratuita
                                </button>
                            </div>
                            <div style="font-size: 0.8rem; color: #64748b; font-weight: 500;">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3" style="margin-right: 4px; vertical-align: -2px;"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                Tu archivo CSV incluirá: <strong>Razón social, CIF, Administradores, Teléfonos, Dirección, CNAE y Estado.</strong>
                            </div>
                        </div>
                    `;
                    document.getElementById('stickyCtaFooter').style.display = 'flex';
                } else {
                    document.getElementById('stickyCtaFooter').style.display = 'none';
                }

                statusText.textContent = `Listo: ${meta.total_count || 0} resultados encontrados.`;
            } catch (e) {
                console.error("Search error:", e);
                statusText.textContent = 'Error de red al buscar.';
            } finally {
                btnSearch.disabled = false;
                btnSearch.innerHTML = `<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg> <span>Buscar</span>`;
            }
        }

        btnSearch.addEventListener('click', () => {
            const f_province = document.getElementById('f_province').value;
            if (!f_province) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Provincia requerida',
                    text: 'Por favor, selecciona una Provincia para poder buscar empresas.',
                    confirmButtonColor: '#2563eb'
                });
                return;
            }
            if(window.trackEvent) trackEvent('map_search_clicked');
            search(1);
        });

        // ---------- UI rendering ----------
        function popupHtml(r) {
            const safe = (s) => escapeHtml((s ?? '').toString());
            const phone = [r.phone, r.phone_mobile].filter(Boolean).join(' · ');
            return `
      <div class="popup">
        <div class="p-title">${safe(r.company_name || 'Empresa')}</div>
        <div class="p-row"><strong>CIF:</strong> ${safe(r.cif || '—')}</div>
        ${r.cnae_code ? `<div class="p-row"><strong>CNAE:</strong> ${safe(r.cnae_code)} ${safe(r.cnae_label || '')}</div>` : ''}
        ${r.estado ? `<div class="p-row"><strong>Estado:</strong> ${safe(r.estado)}</div>` : ''}
        ${r.address ? `<div class="p-row"><strong>Dirección:</strong> ${safe(r.address)}</div>` : ''}
        ${phone ? `<div class="p-row"><strong>Tel:</strong> ${safe(phone)}</div>` : ''}
      </div>
    `;
        }

        function renderPage(meta, listData) {
            if (resultsEl) {
                resultsEl.style.opacity = '1';
                resultsEl.style.pointerEvents = 'auto';
            }

            if (!listData || listData.length === 0) {
                resultsEl.innerHTML = `<div class="empty" style="color: #6b7280; padding: 20px; text-align: center; background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1;">No se encontraron empresas con los filtros seleccionados en esta área.</div>`;
                return;
            }

            const page = meta.page || 1;
            const totalPages = meta.total_pages || 1;

            let html = listData.map(r => {
                const name = escapeHtml(r.company_name || 'Empresa');
                const cif = escapeHtml(r.cif || '');
                const cnae = escapeHtml(r.cnae_code || '');
                const estadoRaw = escapeHtml(r.estado || '');
                const isActive = estadoRaw.toUpperCase() === 'ACTIVA';
                const statusClass = isActive ? 'b2b-status--active' : 'b2b-status--inactive';
                const addr = escapeHtml(r.address || '');
                const phone = escapeHtml(([r.phone, r.phone_mobile].filter(Boolean).join(' · ')) || '');

                return `
        <div class="b2b-data-row" style="flex-direction: column; align-items: stretch; gap: 12px; padding: 16px 20px; transition: all 0.2s; border: 1px solid #e2e8f0; border-radius: 16px; margin-bottom: 12px; background: #fff; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03);" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 10px 15px -3px rgba(0,0,0,0.1), 0 4px 6px -2px rgba(0,0,0,0.05)'; this.style.borderColor='#cbd5e1';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -1px rgba(0,0,0,0.03)'; this.style.borderColor='#e2e8f0';">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; flex-wrap: wrap;">
                <div style="display: flex; align-items: flex-start; gap: 12px;">
                    <div style="width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); display: flex; align-items: center; justify-content: center; color: #1e3a8a; font-weight: 800; font-size: 1.2rem; flex-shrink: 0; border: 1px solid #bfdbfe;">
                        ${name.charAt(0).toUpperCase()}
                    </div>
                    <div style="display: flex; flex-direction: column; gap: 4px;">
                        <a href="<?= site_url('empresa') ?>/${r.id}" target="_blank" style="font-weight: 800; color: #0f172a; font-size: 1.05rem; letter-spacing: -0.01em; text-decoration: none; line-height: 1.2;">${name}</a>
                        ${cif ? `<span style="color: #64748b; font-size: 0.8rem; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-weight: 600;">CIF: ${cif}</span>` : ''}
                    </div>
                </div>
                ${estadoRaw ? `<div class="b2b-status ${statusClass}" style="font-size: 0.7rem; padding: 4px 10px; border-radius: 99px; white-space: nowrap; font-weight: 800; border: 1px solid ${isActive ? '#86efac' : '#fca5a5'}; background: ${isActive ? '#f0fdf4' : '#fef2f2'}; color: ${isActive ? '#166534' : '#991b1b'};">${estadoRaw}</div>` : ''}
            </div>
            
            <div style="display: flex; flex-wrap: wrap; gap: 16px; font-size: 0.85rem; color: #475569; margin-top: 4px; padding-top: 12px; border-top: 1px solid #f1f5f9;">
                ${cnae ? `
                <div style="display: flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>
                    <span><strong>${cnae}</strong> &middot; ${escapeHtml(r.cnae_label || '')}</span>
                </div>` : ''}
                ${phone ? `
                <div style="display: flex; align-items: center; gap: 6px;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path></svg>
                    <span>${phone}</span>
                </div>` : ''}
                ${addr ? `
                <div style="display: flex; align-items: center; gap: 6px; color: #64748b;">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex-shrink: 0;"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                    <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 350px;" title="${addr}">${addr}</span>
                </div>` : ''}
            </div>
        </div>
      `;
            }).join('');

            // Pagination Controls
            if (totalPages > 1) {
                html += `
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 24px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
                    <button onclick="window.goToPage(${page - 1})" ${page === 1 ? 'disabled' : ''} style="background: ${page === 1 ? '#f1f5f9' : '#fff'}; color: ${page === 1 ? '#94a3b8' : '#334155'}; border: 1px solid #e2e8f0; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: ${page === 1 ? 'not-allowed' : 'pointer'}; transition: all 0.2s;">
                        &larr; Anterior
                    </button>
                    <div style="font-size: 0.85rem; color: #64748b; font-weight: 500;">
                        Página <strong style="color: #0f172a;">${page}</strong> de ${totalPages} <span style="margin: 0 8px;">|</span> ${new Intl.NumberFormat('es-ES').format(meta.total_count)} resultados
                    </div>
                    <button onclick="window.goToPage(${page + 1})" ${page === totalPages ? 'disabled' : ''} style="background: ${page === totalPages ? '#f1f5f9' : '#fff'}; color: ${page === totalPages ? '#94a3b8' : '#334155'}; border: 1px solid #e2e8f0; padding: 8px 16px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: ${page === totalPages ? 'not-allowed' : 'pointer'}; transition: all 0.2s;">
                        Siguiente &rarr;
                    </button>
                </div>
                `;
            } else if (listData.length > 0) {
                 html += `<div style="margin-top: 16px; text-align: center; font-size: 0.85rem; color: #64748b;">Mostrando todos los resultados (${listData.length})</div>`;
            }

            resultsEl.innerHTML = html;
        }

        window.goToPage = function(page) {
            search(page);
            const titleEl = document.getElementById('resultsTitle');
            if (titleEl) {
                titleEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        };

        // ---------- Tracking Logic ----------
        function trackEvent(eventName, metadata = {}) {
            if (!window.$) return;
            $.post('<?= site_url("api/tracking/event") ?>', {
                event_name: eventName,
                metadata: JSON.stringify(metadata)
            }).catch(e => console.error('Tracking error', e));
        }

        // ---------- Lead Modal Logic ----------
        window.openLeadModal = function() {
            trackEvent('map_lead_modal_opened');
            document.getElementById('leadModal').style.display = 'flex';
        };
        window.closeLeadModal = function() {
            document.getElementById('leadModal').style.display = 'none';
            document.getElementById('leadForm').style.display = 'block';
            document.getElementById('leadSuccess').style.display = 'none';
            document.getElementById('leadEmail').value = '';
        };
        window.submitLeadForm = async function(e) {
            e.preventDefault();
            const btn = document.getElementById('leadSubmitBtn');
            const originalText = btn.innerHTML;
            const email = document.getElementById('leadEmail').value;
            
            btn.innerHTML = 'Enviando...';
            btn.disabled = true;
            
            trackEvent('map_lead_submitted', { email: email });
            
            try {
                const formData = new FormData();
                formData.append('email', email);
                
                const provinceText = selectedDataName(fProvince).trim();
                const municipalityText = selectedDataName(fMunicipality).trim();
                const cnaePrefix = getSelectedCnaePrefix();
                const sectorOptionText = cSector.options[cSector.selectedIndex]?.text;
                const cnaeTextValue = (sectorOptionText && sectorOptionText !== '— Todos —') ? sectorOptionText : '';
                const estadoText = document.getElementById('f_estado') ? document.getElementById('f_estado').value : '';

                formData.append('province', provinceText !== '— Selecciona —' && provinceText !== '— Todos —' ? provinceText : '');
                formData.append('municipality', municipalityText !== '— Todos —' ? municipalityText : '');
                formData.append('cnae_prefix', cnaePrefix);
                formData.append('cnae_text', cnaeTextValue);
                formData.append('estado', estadoText);

                const targetUrl = "<?= site_url('api/map/request-sample') ?>";
                
                const response = await fetch(targetUrl, {
                    method: 'POST',
                    body: formData
                });
                
                const responseText = await response.text();
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseErr) {
                    throw new Error('La respuesta del servidor no es válida (JSON error): ' + responseText.substring(0, 50));
                }
                
                if (data.success) {
                    document.getElementById('leadForm').style.display = 'none';
                    document.getElementById('leadSuccess').style.display = 'block';
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Error del servidor: ' + (data.message || 'No se pudo enviar la muestra.'),
                        confirmButtonColor: '#2152ff'
                    });
                }
            } catch (err) {
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error de conexión o validación: ' + err.message,
                    confirmButtonColor: '#2152ff'
                });
            } finally {
                btn.innerHTML = originalText;
                btn.disabled = false;
            }
        };

        // ---------- Boot ----------
        document.addEventListener('DOMContentLoaded', () => {
            if (window.$) {
                $('#f_province').select2({ width: '100%' });
                $('#f_municipality').select2({ width: '100%' });
                $('#c_sector').select2({ width: '100%' });
                $('#f_estado').select2({ width: '100%' });

                // Evento de cambio para Provincia con Select2
                $('#f_province').on('change', async function() {
                    const provinceId = $(this).val();
                    if (!provinceId) {
                        setDisabledSelect(fMunicipality, '— Selecciona provincia antes —');
                        return;
                    }
                    try {
                        await loadMunicipalities(provinceId);
                    } catch (e) {
                        setDisabledSelect(fMunicipality, 'Error cargando municipios');
                    }
                });
            }
            loadProvinces();
            loadSectors();
        });
    
        // ---------- AI Chat Assistant Logic ----------
        let aiChatHistory = [];
        
        const btnToggleAi = document.getElementById('btnToggleAi');
        const btnCloseAi = document.getElementById('btnCloseAi');
        const aiChatPanel = document.getElementById('aiChatPanel');
        const aiChatInput = document.getElementById('aiChatInput');
        const aiChatSend = document.getElementById('aiChatSend');
        const aiChatMessages = document.getElementById('aiChatMessages');

        if(btnToggleAi) {
            btnToggleAi.addEventListener('click', () => {
                aiChatPanel.classList.toggle('is-open');
                setTimeout(() => {
                    if (typeof map !== 'undefined') {
                        map.invalidateSize();
                    }
                }, 400);
            });
        }
        if(btnCloseAi) {
            btnCloseAi.addEventListener('click', () => {
                aiChatPanel.classList.remove('is-open');
                setTimeout(() => {
                    if (typeof map !== 'undefined') {
                        map.invalidateSize();
                    }
                }, 400);
            });
        }

        function addChatMessage(role, content, isHtml = false, id = null) {
            const div = document.createElement('div');
            if (id) div.id = id;
            div.style.padding = '12px 16px';
            div.style.borderRadius = role === 'user' ? '12px 12px 0 12px' : '12px 12px 12px 0';
            div.style.alignSelf = role === 'user' ? 'flex-end' : 'flex-start';
            div.style.maxWidth = '90%';
            div.style.fontSize = '0.95rem';
            div.style.lineHeight = '1.5';
            div.style.boxShadow = '0 2px 4px rgba(0,0,0,0.02)';
            
            if (role === 'user') {
                div.style.background = '#4c1d95';
                div.style.color = 'white';
                div.textContent = content;
            } else {
                div.style.background = 'white';
                div.style.border = '1px solid #e2e8f0';
                div.style.color = '#334155';
                if (isHtml) {
                    div.innerHTML = content;
                } else {
                    div.textContent = content;
                }
            }
            aiChatMessages.appendChild(div);
            aiChatMessages.scrollTop = aiChatMessages.scrollHeight;
        }

        async function sendAiMessage() {
            const text = aiChatInput.value.trim();
            if (!text) return;

            aiChatInput.value = '';
            addChatMessage('user', text);
            aiChatHistory.push({ role: 'user', content: text });

            const loadingId = 'ai-loading-' + Date.now();
            addChatMessage('assistant', `⏳ Procesando...`, false, loadingId);

            try {
                const response = await fetch('<?= site_url("api/chat/assistant") ?>', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ messages: aiChatHistory })
                });

                const data = await response.json();
                const loadingEl = document.getElementById(loadingId);
                if (loadingEl) loadingEl.remove();

                if (data.assistant_message) {
                    const aiReply = data.assistant_message;
                    aiChatHistory.push({ role: 'assistant', content: JSON.stringify(aiReply) });
                    
                    let replyHtml = aiReply.reply_text;
                    
                    if (data.real_count !== undefined) {
                        replyHtml += `<div style="margin-top:12px; padding-top:12px; border-top:1px solid #e2e8f0; font-size:0.9rem;">
                            <strong>🎯 Encontradas:</strong> ${new Intl.NumberFormat('es-ES').format(data.real_count)} empresas<br>
                        </div>`;
                    }

                    if (aiReply.is_ready_for_checkout && data.checkout_url) {
                        replyHtml += `<div style="margin-top:12px; background: #f0fdf4; padding:12px; border-radius:8px; border:1px solid #bbf7d0;">
                            <div style="font-weight:bold; color:#166534; margin-bottom:8px;">¡Listado preparado! (${data.price}€ + IVA)</div>
                            <a href="${data.checkout_url}" style="display:block; text-align:center; background:#10b981; color:white; padding:8px; border-radius:6px; font-weight:bold; text-decoration:none;">💳 Pagar y Descargar</a>
                        </div>`;
                    }

                    addChatMessage('assistant', replyHtml, true);

                    if (aiReply.filters) {
                        await syncFiltersToMap(aiReply.filters);
                    }
                } else {
                    addChatMessage('assistant', 'Ha ocurrido un error al procesar tu solicitud.');
                }
            } catch (error) {
                console.error('AI Error:', error);
                const loadingEl = document.getElementById(loadingId);
                if (loadingEl) loadingEl.remove();
                addChatMessage('assistant', 'Error de conexión con la IA o procesando la respuesta.');
            }
        }

        async function syncFiltersToMap(filters) {
            let changed = false;
            let provinceChanged = false;
            
            if (filters.province !== undefined) {
                const provSelect = document.getElementById('f_province');
                if (filters.province === null) {
                    provSelect.selectedIndex = 0;
                    changed = true;
                    provinceChanged = true;
                } else {
                    for(let i=0; i<provSelect.options.length; i++) {
                        if(provSelect.options[i].text.toUpperCase() === filters.province.toUpperCase()) {
                            if (provSelect.selectedIndex !== i) {
                                provSelect.selectedIndex = i;
                                changed = true;
                                provinceChanged = true;
                            }
                            break;
                        }
                    }
                }
                if (provinceChanged && window.$) {
                    const provId = $(provSelect).val();
                    if(provId) {
                        await loadMunicipalities(provId);
                    } else {
                        const m = document.getElementById('f_municipality');
                        m.innerHTML = '<option value="">— Selecciona provincia antes —</option>';
                        m.disabled = true;
                    }
                    $(provSelect).trigger({ type: 'change', aiTrigger: true });
                }
            }
            
            if (filters.municipality !== undefined && filters.municipality !== null) {
                const mSelect = document.getElementById('f_municipality');
                let munFound = false;
                for (let i = 0; i < mSelect.options.length; i++) {
                    if (mSelect.options[i].text.toUpperCase() === filters.municipality.toUpperCase()) {
                        mSelect.selectedIndex = i;
                        munFound = true;
                        changed = true;
                        break;
                    }
                }
                if (munFound && window.$) {
                    $(mSelect).trigger('change');
                }
            } else if (filters.municipality === null) {
                const mSelect = document.getElementById('f_municipality');
                if (mSelect.selectedIndex !== 0) {
                    mSelect.selectedIndex = 0;
                    changed = true;
                    if (window.$) $(mSelect).trigger('change');
                }
            }
            
            if (filters.date_min !== undefined) document.getElementById('f_date_min').value = filters.date_min || '';
            if (filters.date_max !== undefined) document.getElementById('f_date_max').value = filters.date_max || '';
            
            if (filters.has_phone !== undefined) {
                const phoneCheckbox = document.getElementById('f_has_phone');
                phoneCheckbox.checked = filters.has_phone === true;
            }
            if (filters.estado !== undefined) {
                const estSelect = document.getElementById('f_estado');
                if (filters.estado === null) {
                    estSelect.selectedIndex = 0;
                } else {
                    estSelect.value = filters.estado.toUpperCase();
                }
                if (window.$) $(estSelect).trigger('change');
            }
            
            if (filters.cnae_prefix !== undefined || filters.cnae_text !== undefined) {
                const cSector = document.getElementById('c_sector');
                if (filters.cnae_prefix === null && filters.cnae_text === null) {
                    cSector.selectedIndex = 0;
                } else {
                    let found = false;
                    if (filters.cnae_prefix) {
                        for(let i=0; i<cSector.options.length; i++) {
                            const val = cSector.options[i].value || '';
                            if(val.startsWith(filters.cnae_prefix)) {
                                cSector.selectedIndex = i;
                                found = true;
                                break;
                            }
                        }
                    }
                    if (!found && filters.cnae_text) {
                        for(let i=0; i<cSector.options.length; i++) {
                            if(cSector.options[i].text.toLowerCase().includes(filters.cnae_text.toLowerCase())) {
                                cSector.selectedIndex = i;
                                found = true;
                                break;
                            }
                        }
                    }
                    
                    if (!found) {
                        const newOption = document.createElement("option");
                        newOption.value = filters.cnae_prefix || "";
                        newOption.text = filters.cnae_text || ("CNAE " + filters.cnae_prefix);
                        newOption.selected = true;
                        cSector.appendChild(newOption);
                    }
                }
                if (window.$) $(cSector).trigger("change");
            }
            
            const btn = document.getElementById('btnSearch');
            if (btn) btn.click();
        }

        if(aiChatSend) aiChatSend.addEventListener('click', sendAiMessage);
        if(aiChatInput) aiChatInput.addEventListener('keypress', (e) => { if(e.key === 'Enter') sendAiMessage(); });
        // Toggle Map Visibility
        window.toggleMapVisibility = function() {
            const mapContainer = document.getElementById('mapContainerWrapper');
            const toggleBtn = document.getElementById('toggleMapBtn');
            
            if (mapContainer.style.display === 'none') {
                mapContainer.style.display = 'flex';
                mapContainer.style.flexDirection = 'column';
                toggleBtn.innerHTML = '🙈 Ocultar Mapa';
                // Need to invalidate size so Leaflet tiles load correctly after being hidden
                if (window.map) {
                    setTimeout(() => {
                        window.map.invalidateSize();
                    }, 400);
                }
            } else {
                mapContainer.style.display = 'none';
                toggleBtn.innerHTML = '📍 Mostrar Mapa';
            }
        };
    })();
</script>


</body>
</html>
