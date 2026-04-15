<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => 'Leads de Empresas Nuevas en España | Captación Comercial B2B',
        'excerptText' => 'Consigue leads diarios de empresas recién constituidas en España. Accede a oportunidades comerciales antes que tu competencia con análisis de IA.',
        'canonical'   => site_url('leads-empresas-nuevas'),
        'robots'      => 'index,follow',
    ]) ?>
    <link rel="stylesheet" href="<?= base_url('public/css/precios_radar.css?v=' . (file_exists(FCPATH . 'public/css/precios_radar.css') ? filemtime(FCPATH . 'public/css/precios_radar.css') : time())) ?>" />
</head>
<body>
<?= view('partials/header') ?>

<main class="radar-page">

    <section class="radar-hero">
        <div class="container">
            <div class="radar-hero__shell">
                <div class="radar-hero__badge">
                    <span class="radar-hero__badge-dot"></span>
                    Captación Comercial Inteligente
                </div>

                <h1 class="radar-hero__title">
                    Detecta nuevas empresas
                    <span>antes que tu competencia</span>
                </h1>

                <p class="radar-hero__subtitle">
                    Accede cada día a nuevas sociedades registradas en el BORME, analízalas con nuestro <strong>Algoritmo de Scoring</strong> y conviértelas en leads cualificados con <strong>IA</strong>.
                </p>

                <div class="radar-hero__proof">
                    <div class="radar-hero__proof-item">
                        <strong>+200</strong>
                        <span>nuevas empresas diarias</span>
                    </div>
                    <div class="radar-hero__proof-item">
                        <strong>100 Puntos</strong>
                        <span>Algoritmo de cualificación</span>
                    </div>
                    <div class="radar-hero__proof-item">
                        <strong>CRM Integrado</strong>
                        <span>Gestión de embudo comercial</span>
                    </div>
                </div>

                <div class="radar-hero__actions">
                    <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" class="radar-btn radar-btn--primary">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4">
                            <circle cx="12" cy="12" r="10"></circle>
                            <polyline points="12 6 12 12 16 14"></polyline>
                        </svg>
                        Activar Radar PRO
                    </a>
                </div>

                <div class="radar-freshness-bar" style="max-width: 600px; margin: 24px auto; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 99px; padding: 10px 24px; display: flex; align-items: center; justify-content: center; gap: 16px; font-size: 0.875rem; font-weight: 700; color: #475569; position: relative; overflow: hidden;">
                   <div style="display: flex; align-items: center; gap: 8px;">
                        <span style="width: 8px; height: 8px; background: #10b981; border-radius: 50%; display: block; animation: pulse-dot 2s infinite;"></span>
                        Última actualización: <strong><?= date('H:i', strtotime('-12 minutes')) ?></strong>
                   </div>
                   <span style="color: #cbd5e1;">|</span>
                   <div>Hoy: <strong>+218</strong> empresas detectadas</div>
                </div>

                <div class="radar-hero__feature-panel">
                    <div class="radar-hero__feature-copy">
                        <h2>El Centro de Control para tu captación B2B</h2>
                        <ul style="display: grid; gap: 16px;">
                            <li><strong>Scoring de Lead:</strong> Detectamos automáticamente qué empresas tienen mayor probabilidad de compra.</li>
                            <li><strong>Embudo Kanban:</strong> Gestiona tu prospección sin salir de la plataforma.</li>
                            <li><strong>Análisis IA:</strong> Nicho comercial, cargos clave y guiones de venta personalizados.</li>
                        </ul>
                    </div>

                    <div class="radar-hero__mini-dashboard">
                        <div class="radar-hero__mini-card" style="background: #eff6ff; border-color: #dbeafe;">
                            <span class="radar-hero__mini-label" style="color: #2563eb;">Oportunidad Hoy</span>
                            <strong>95/100</strong>
                            <small>Alta probabilidad de cierre</small>
                        </div>
                        <div class="radar-hero__mini-card">
                            <span class="radar-hero__mini-label">Fase del Radar</span>
                            <strong>Constitución</strong>
                            <small>Momento óptimo de contacto</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="radar-section">
        <div class="container">
            <div class="radar-split">
                <div class="radar-split__content">
                    <div class="radar-kicker">Oportunidad comercial</div>
                    <h2 class="radar-title">Las empresas nuevas son las mejores oportunidades comerciales</h2>
                    <p class="radar-text">
                        Durante sus primeros meses de actividad, las empresas recién constituidas suelen contratar proveedores, asesoría, software, seguros, marketing y servicios especializados.
                    </p>
                    <p class="radar-text">
                        Si llegas antes que otros proveedores, tu probabilidad de cerrar una primera venta aumenta significativamente. Radar te permite detectar esas empresas justo en el momento adecuado.
                    </p>
                    <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" class="radar-btn radar-btn--primary radar-btn--inline" style="margin-bottom: 40px;">
                        Activar Radar
                    </a>
                </div>

                <div class="radar-timeline">
                    <div class="radar-timeline__item">
                        <div class="radar-timeline__num">1</div>
                        <div>
                            <h3>Nace una nueva empresa</h3>
                            <p>Radar detecta automáticamente la constitución desde fuentes oficiales mercantiles.</p>
                        </div>
                    </div>
                    <div class="radar-timeline__item">
                        <div class="radar-timeline__num">2</div>
                        <div>
                            <h3>Tú la ves antes que otros</h3>
                            <p>Filtras tu nicho por provincia, actividad o fecha y encuentras oportunidades activas.</p>
                        </div>
                    </div>
                    <div class="radar-timeline__item">
                        <div class="radar-timeline__num">3</div>
                        <div>
                            <h3>Exportas y contactas</h3>
                            <p>Descargas los leads en Excel o CSV y empiezas tu prospección comercial ese mismo día.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="radar-section radar-section--scoring" style="background: #0f172a; color: white; padding: 80px 0;">
        <div class="container">
            <div class="radar-split">
                <div class="radar-split__content">
                    <div class="radar-kicker radar-kicker--dark">Cualificación Proactiva</div>
                    <h2 class="radar-title" style="color: white;">Algoritmo de Scoring de 100 Puntos</h2>
                    <p class="radar-text" style="color: rgba(255,255,255,0.7);">
                        No todos los leads son iguales. Nuestro algoritmo analiza cada nueva empresa en tiempo real basándose en múltiples señales de mercado para que te centres en las que tienen mayor ROI potencial.
                    </p>
                    <div class="radar-scoring-grid" style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-top: 32px;">
                        <div class="radar-scoring-item" style="display: flex; align-items: flex-start; gap: 12px;">
                            <div style="font-size: 20px;">⚖️</div>
                            <div>
                                <h4 style="margin: 0; font-size: 14px; font-weight: 800;">Tipo de Acto</h4>
                                <p style="margin: 4px 0 0; font-size: 13px; color: rgba(255,255,255,0.5);">Constitución vs Ampliación de Capital.</p>
                            </div>
                        </div>
                        <div class="radar-scoring-item" style="display: flex; align-items: flex-start; gap: 12px;">
                            <div style="font-size: 20px;">💰</div>
                            <div>
                                <h4 style="margin: 0; font-size: 14px; font-weight: 800;">Fuerza Financiera</h4>
                                <p style="margin: 4px 0 0; font-size: 13px; color: rgba(255,255,255,0.5);">Puntuación basada en Capital Social inicial.</p>
                            </div>
                        </div>
                        <div class="radar-scoring-item" style="display: flex; align-items: flex-start; gap: 12px;">
                            <div style="font-size: 20px;">🏢</div>
                            <div>
                                <h4 style="margin: 0; font-size: 14px; font-weight: 800;">Sector de Valor</h4>
                                <p style="margin: 4px 0 0; font-size: 13px; color: rgba(255,255,255,0.5);">Pesaje superior para sectores B2B estrátegicos.</p>
                            </div>
                        </div>
                        <div class="radar-scoring-item" style="display: flex; align-items: flex-start; gap: 12px;">
                            <div style="font-size: 20px;">👤</div>
                            <div>
                                <h4 style="margin: 0; font-size: 14px; font-weight: 800;">Señales Admin</h4>
                                <p style="margin: 4px 0 0; font-size: 13px; color: rgba(255,255,255,0.5);">Detección de administradores clave.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="radar-scoring-visual" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.1); border-radius: 24px; padding: 32px; display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 24px;">
                    <div style="position: relative; width: 140px; height: 140px; display: flex; align-items: center; justify-content: center;">
                        <svg viewBox="0 0 36 36" style="width: 140px; height: 140px; transform: rotate(-90deg);">
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2.5" />
                            <path d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831" fill="none" stroke="#10b981" stroke-width="2.8" stroke-dasharray="92, 100" stroke-linecap="round" />
                        </svg>
                        <div style="position: absolute; text-align: center;">
                            <span style="font-size: 32px; font-weight: 900; line-height: 1;">92</span>
                            <span style="display: block; font-size: 11px; text-transform: uppercase; font-weight: 800; opacity: 0.6; margin-top: -4px;">Score</span>
                        </div>
                    </div>
                    <div style="background: #10b981; color: white; padding: 6px 16px; border-radius: 99px; font-size: 12px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px;">
                        🔥 PRIORIDAD MUY ALTA
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="radar-section radar-section--kanban">
        <div class="container">
            <div class="radar-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 60px; align-items: center;">
               <div class="radar-kanban-mockup" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 20px; padding: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.05); display: flex; gap: 12px; overflow: hidden;">
                    <!-- Col 1 -->
                    <div style="flex: 1; min-width: 140px;">
                        <div style="font-size: 11px; font-weight: 800; color: #94a3b8; text-transform: uppercase; margin-bottom: 12px;">Nuevo</div>
                        <div style="background: white; padding: 12px; border-radius: 10px; border: 1px solid #e2e8f0; margin-bottom: 8px;">
                            <div style="height: 6px; width: 40%; background: #e2e8f0; border-radius: 3px; margin-bottom: 6px;"></div>
                            <div style="height: 6px; width: 70%; background: #f1f5f9; border-radius: 3px;"></div>
                        </div>
                        <div style="background: white; padding: 12px; border-radius: 10px; border: 1px solid #e2e8f0;">
                            <div style="height: 6px; width: 30%; background: #e2e8f0; border-radius: 3px; margin-bottom: 6px;"></div>
                            <div style="height: 6px; width: 60%; background: #f1f5f9; border-radius: 3px;"></div>
                        </div>
                    </div>
                    <!-- Col 2 -->
                    <div style="flex: 1; min-width: 140px;">
                        <div style="font-size: 11px; font-weight: 800; color: #ea580c; text-transform: uppercase; margin-bottom: 12px;">Contactado</div>
                        <div style="background: white; padding: 12px; border-radius: 10px; border: 2px solid #ea580c; margin-bottom: 8px; position: relative;">
                            <div style="height: 6px; width: 50%; background: #ea580c; opacity: 0.5; border-radius: 3px; margin-bottom: 6px;"></div>
                            <div style="height: 6px; width: 80%; background: #fff7ed; border-radius: 3px;"></div>
                        </div>
                    </div>
                    <!-- Col 3 -->
                    <div style="flex: 1; min-width: 140px; opacity: 0.4;">
                        <div style="font-size: 11px; font-weight: 800; color: #2563eb; text-transform: uppercase; margin-bottom: 12px;">Ganado</div>
                    </div>
               </div>
               <div>
                    <div class="radar-kicker">Tu Pipeline B2B</div>
                    <h2 class="radar-title">CRM Integrado: Gestiona tu Embudo Kanban</h2>
                    <p class="radar-text">
                        Deja de usar hojas de cálculo estáticas. Radar incluye un gestor de ventas integrado para que puedas trackear cada lead, guardar notas privadas y organizar tu prospección diaria en columnas. 
                    </p>
                    <ul class="radar-ai-list">
                        <li><strong>Favoritos:</strong> Marca empresas para no perderles la pista.</li>
                        <li><strong>Historial:</strong> Notas privadas compartidas con tu equipo.</li>
                        <li><strong>Pipeline Visual:</strong> Arrastra y suelta para cambiar estados.</li>
                    </ul>
               </div>
            </div>
        </div>
    </section>

    <section class="radar-section radar-section--ai">
        <div class="container">
            <div class="radar-ai-grid">
                <div class="radar-ai-visual">
                    <div class="radar-ai-card" style="overflow: hidden; border-radius: 24px; border: 1px solid #e2e8f0; background: white; box-shadow: 0 20px 40px rgba(0,0,0,0.08);">
                        <div class="radar-ai-card__header" style="background: #0f172a; padding: 28px 24px 20px;">
                            <span class="radar-ai-card__tag" style="background: #2563eb; color: white; padding: 4px 10px; border-radius: 6px; font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 0.5px; display: inline-block;">Analizado por IA v4.0</span>
                            <h3 style="color: white; font-size: 20px; margin: 12px 0 0; font-weight: 900; letter-spacing: -0.02em;">DIGITAL SOLUTIONS SL</h3>
                        </div>
                        <div class="radar-ai-card__body" style="padding: 24px;">
                            <div class="radar-ai-feature">
                                <strong style="color: #2563eb; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">🎯 Nicho Detectado</strong>
                                <p style="font-size: 14px; font-weight: 600;">SaaS para automatización de almacenes (Logística 4.0)</p>
                            </div>
                            <div class="radar-ai-feature" style="margin-top: 16px;">
                                <strong style="color: #2563eb; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">👤 Persona Decisora</strong>
                                <p style="font-size: 14px; font-weight: 600;">Director de Operaciones / Supply Chain Manager</p>
                            </div>
                            <div class="radar-ai-feature" style="margin-top: 16px;">
                                <strong style="color: #2563eb; font-size: 12px; text-transform: uppercase; letter-spacing: 0.5px;">🔥 Pain Points</strong>
                                <ul style="margin: 4px 0 0; padding-left: 16px; font-size: 13px; color: #475569;">
                                    <li>Ineficiencia en picking manual inicial.</li>
                                    <li>Necesidad de sistemas de inventario RTLS.</li>
                                </ul>
                            </div>
                        </div>
                        <div class="radar-ai-card__footer" style="background: #f8fafc; border-top: 1px solid #e2e8f0; padding: 16px;">
                            <div class="radar-ai-script" style="font-style: italic; color: #64748b; font-size: 12px; line-height: 1.4;">
                                "Hola [Nombre], he visto que acabáis de fundar Digital Solutions. Al estar en fase inicial de logística, probablemente os preocupe [Pain Point]..."
                            </div>
                        </div>
                    </div>
                </div>
                <div class="radar-ai-content">
                    <div class="radar-kicker">Socio de Prospección</div>
                    <h2 class="radar-title">IA que prepara tus ventas al instante</h2>
                    <p class="radar-text">
                        Nuestra Inteligencia Artificial no solo analiza datos; te entrega información de valor para que tu primer contacto sea demoledor.
                    </p>
                    <ul class="radar-ai-list">
                        <li><strong>Detección de Nicho Deep:</strong> Entiende el valor real detrás de un CNAE genérico.</li>
                        <li><strong>Buyer Persona:</strong> Identifica al cargo con mayor probabilidad de compra.</li>
                        <li><strong>Deep Pain Points:</strong> Descubre los desafíos que enfrentan en su mes 1.</li>
                        <li><strong>Sales Scripts:</strong> Guiones personalizados para llamadas y LinkedIn.</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    <section class="radar-section radar-section--soft">
        <div class="container">
            <div class="radar-heading radar-heading--center">
                <div class="radar-kicker">Vista previa real</div>
                <h2 class="radar-title">Centro de Prospección Directa</h2>
                <p class="radar-subtitle">
                    Esta es la interfaz profesional que utilizarás para detectar y cerrar nuevas oportunidades.
                </p>
            </div>

            <div class="radar-preview">
                <div class="radar-preview__toolbar">
                    <div class="radar-preview__dots">
                        <span></span><span></span><span></span>
                    </div>
                    <div class="radar-preview__toolbar-status" style="display: flex; align-items: center; gap: 16px;">
                        <div style="background: #eff6ff; color: #1e40af; padding: 6px 14px; border-radius: 8px; font-weight: 950; font-size: 10px; text-transform: uppercase; letter-spacing: 0.5px; border: 1px solid #dbeafe; display: flex; align-items: center; gap: 8px;">
                           <div style="width: 14px; height: 14px; background: #3b82f6; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 10px; color: white;">⚡</div>
                           OPORTUNIDADES ALTA PRIORIDAD
                        </div>
                        <div style="display: flex; align-items: center; gap: 8px; font-size: 10px; font-weight: 900; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px;">
                            <div style="width: 8px; height: 8px; min-width: 8px; min-height: 8px; background: #10b981; border-radius: 50%; flex-shrink: 0;"></div>
                            SISTEMA EN VIVO
                        </div>
                    </div>
                </div>

                <div class="radar-table-scroll">
                    <table class="radar-table">
                        <thead>
                            <tr>
                                <th>Empresa</th>
                                <th>Score / Señales</th>
                                <th>Estado CRM</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td data-label="Empresa">
                                    <div style="font-weight: 850; color: #0f172a;">TECH FLOW SOLUTIONS SL</div>
                                    <div style="font-size: 11px; color: #64748b;">Barcelona · 05/03/2026</div>
                                </td>
                                <td data-label="Score">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span style="font-weight: 900; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 4px 10px; border-radius: 6px; font-size: 13px;">🟢 94/100</span>
                                        <span style="font-size: 11px; color: #64748b; font-weight: 700;">Cap. Sólido · Tech Hub</span>
                                    </div>
                                </td>
                                <td data-label="Estado">
                                    <span style="background: #f1f5f9; color: #64748b; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; border: 1px solid #e2e8f0;">NUEVO</span>
                                </td>
                                <td data-label="Acción">
                                    <div style="width: 32px; height: 32px; background: #0f172a; color: white; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 14px;">📝</div>
                                </td>
                            </tr>
                            <tr>
                                <td data-label="Empresa">
                                    <div style="font-weight: 850; color: #0f172a;">LOGISTIC AI GROUP SL</div>
                                    <div style="font-size: 11px; color: #64748b;">Madrid · 04/03/2026</div>
                                </td>
                                <td data-label="Score">
                                    <div style="display: flex; align-items: center; gap: 6px;">
                                        <span style="font-weight: 900; color: #10b981; background: rgba(16, 185, 129, 0.1); padding: 4px 10px; border-radius: 6px; font-size: 13px;">🟢 88/100</span>
                                        <span style="font-size: 11px; color: #64748b; font-weight: 700;">Constitución</span>
                                    </div>
                                </td>
                                <td data-label="Estado">
                                    <span style="background: #fff7ed; color: #ea580c; padding: 4px 10px; border-radius: 20px; font-size: 11px; font-weight: 800; border: 1px solid #ffedd5;">CONTACTADO</span>
                                </td>
                                <td data-label="Acción">
                                    <div style="width: 32px; height: 32px; background: #0f172a; color: white; display: flex; align-items: center; justify-content: center; border-radius: 8px; font-size: 14px;">📊</div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="radar-preview__cta">
                    <a href="<?= site_url('radar') ?>" class="radar-btn radar-btn--ghost">
                        Ver Dashboard Completo
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="radar-section">
        <div class="container">
            <div class="radar-heading radar-heading--center">
                <div class="radar-kicker">Proceso</div>
                <h2 class="radar-title">Cómo funciona Radar</h2>
                <p class="radar-subtitle">
                    Un flujo simple para detectar oportunidades reales, filtrarlas y convertirlas en prospección accionable.
                </p>
            </div>

            <div class="radar-steps">
                <article class="radar-step">
                    <div class="radar-step__num">1</div>
                    <h3>Detección automática</h3>
                    <p>Radar monitoriza nuevas constituciones mercantiles y las incorpora a tu entorno de trabajo.</p>
                </article>

                <article class="radar-step">
                    <div class="radar-step__num">2</div>
                    <h3>Segmentación inteligente</h3>
                    <p>Filtra por sector CNAE, provincia o fecha para centrarte solo en el tipo de empresa que te interesa.</p>
                </article>

                <article class="radar-step">
                    <div class="radar-step__num">3</div>
                    <h3>Exportación y prospección</h3>
                    <p>Descarga los leads en Excel o CSV y empieza a trabajar campañas comerciales inmediatamente.</p>
                </article>
            </div>
        </div>
    </section>

    <section class="radar-section radar-section--soft">
        <div class="container">
            <div class="radar-heading radar-heading--center">
                <div class="radar-kicker">Capacidades</div>
                <h2 class="radar-title">Todo lo que incluye Radar PRO</h2>
                <p class="radar-subtitle">
                    Una herramienta diseñada para profesionales del desarrollo de negocio y ventas B2B.
                </p>
            </div>

            <div class="radar-includes">
                <div class="radar-include">
                    <div class="radar-include__icon">🏆</div>
                    <span>Scoring de 100 puntos incluido</span>
                </div>
                <div class="radar-include">
                    <div class="radar-include__icon">🤖</div>
                    <span>Análisis IA Profundo (Sectores/Nichos)</span>
                </div>
                <div class="radar-include">
                    <div class="radar-include__icon">📋</div>
                    <span>CRM Kanban para gestión de leads</span>
                </div>
                <div class="radar-include">
                    <div class="radar-include__icon">✓</div>
                    <span>Actualización diaria (07:00 AM)</span>
                </div>
                <div class="radar-include">
                    <div class="radar-include__icon">📍</div>
                    <span>Ubicación y Registro oficial</span>
                </div>
                <div class="radar-include">
                    <div class="radar-include__icon">📂</div>
                    <span>Exportación Excel / CSV ilimitada</span>
                </div>
            </div>
        </div>
    </section>

    <section class="radar-band">
        <div class="container">
            <div class="radar-band__header">
                <div class="radar-kicker radar-kicker--dark">Datos del mercado</div>
                <h2 class="radar-band__title">La base de datos más completa <br> de nuevas empresas en España</h2>
            </div>

            <div class="radar-metrics">
                <div class="radar-metric">
                    <strong>+4,5M</strong>
                    <span>empresas analizadas</span>
                </div>
                <div class="radar-metric">
                    <strong>+200</strong>
                    <span>nuevas empresas detectadas cada día</span>
                </div>
                <div class="radar-metric">
                    <strong>100%</strong>
                    <span>datos oficiales del BORME</span>
                </div>
            </div>
        </div>
    </section>

    <section id="radar-pricing" class="radar-section">
        <div class="container">
            <div class="radar-heading radar-heading--center">
                <div class="radar-kicker">Precio</div>
                <h2 class="radar-title">Plan Radar</h2>
                <p class="radar-subtitle">
                    Acceso mensual completo al radar de nuevas empresas en España, con filtros avanzados y exportación ilimitada.
                </p>
            </div>

            <div class="radar-pricing-wrap">
                <div class="radar-pricing-card">
                    <div class="radar-pricing-card__topbar"></div>

                    <div class="radar-pricing-card__header">
                        <div class="radar-pricing-card__label">Plan Radar</div>
                        <div class="radar-pricing-card__price">79€<span>/mes</span></div>
                        <p>Acceso completo al radar de empresas nuevas en España.</p>
                    </div>

                    <div class="radar-pricing-card__body">
                        <ul class="radar-pricing-list">
                            <li>Acceso completo al Radar</li>
                            <li>Filtros por sector y provincia</li>
                            <li>Exportación ilimitada Excel / CSV</li>
                            <li>Actualización diaria de nuevas empresas</li>
                            <li>Sin permanencia</li>
                        </ul>

                        <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" class="radar-btn radar-btn--primary radar-btn--full">
                            Activar Radar
                        </a>

                        <div class="radar-pricing-card__footnote">
                            Cancela cuando quieras
                        </div>
                    </div>
                </div>

                <div class="radar-pricing-side">
                    <div class="radar-pricing-side__card">
                        <h3>Ideal para</h3>
                        <ul style="display: grid; gap: 10px;">
                            <li><strong>SaaS B2B:</strong> Encuentra empresas en fase de digitalización.</li>
                            <li><strong>Asesorías:</strong> Capta sociedades antes de que cierren su gestoría.</li>
                            <li><strong>Marketing:</strong> Ofrece servicios de marca a nuevas empresas.</li>
                            <li><strong>Seguros:</strong> Detecta necesidades de coberturas iniciales.</li>
                        </ul>
                    </div>

                    <div class="radar-pricing-side__card radar-pricing-side__card--soft" style="background: linear-gradient(135deg, #eff6ff 0%, #ffffff 100%); border: 1px solid #dbeafe;">
                        <h3 style="color: #2563eb;">Rentabilidad Estimada (ROI)</h3>
                        <p style="font-weight: 700; color: #1e3a8a; font-size: 1.1rem; margin: 12px 0;">+450% ROI</p>
                        <p style="font-size: 0.9rem;">
                            Solo un cliente conseguido gracias al radar amortiza más de <strong>5 años</strong> de suscripción. 
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="radar-section radar-section--soft">
        <div class="container">
            <div class="radar-heading radar-heading--center">
                <div class="radar-kicker">Comparativa</div>
                <h2 class="radar-title">Radar vs Excel puntual</h2>
                <p class="radar-subtitle">
                    Si necesitas captar oportunidades de forma continua, Radar es claramente la opción más rentable.
                </p>
            </div>

            <div class="radar-comparison-wrap">
                <div class="radar-comparison-scroll">
                    <table class="radar-comparison">
                        <thead>
                            <tr>
                                <th></th>
                                <th class="radar-comparison__col-radar">
                                    <div class="radar-comparison__badge">Suscripción</div>
                                    Radar mensual
                                </th>
                                <th class="radar-comparison__col-excel">Excel puntual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Actualización</td>
                                <td class="radar-comparison__col-radar">
                                    <span class="radar-comparison__val-icon">⚡</span>
                                    Diaria automática
                                </td>
                                <td class="radar-comparison__col-excel">Descarga única</td>
                            </tr>
                            <tr>
                                <td>Filtros</td>
                                <td class="radar-comparison__col-radar">
                                    <span class="radar-comparison__val-icon">🔍</span>
                                    Avanzados e ilimitados
                                </td>
                                <td class="radar-comparison__col-excel">Sin filtros dinámicos</td>
                            </tr>
                            <tr>
                                <td>Leads nuevos</td>
                                <td class="radar-comparison__col-radar">
                                    <span class="radar-comparison__val-icon">✨</span>
                                    Cada día (BORME)
                                </td>
                                <td class="radar-comparison__col-excel">Datos estáticos</td>
                            </tr>
                            <tr>
                                <td>Exportación</td>
                                <td class="radar-comparison__col-radar">
                                    <span class="radar-comparison__val-icon">📂</span>
                                    Ilimitada
                                </td>
                                <td class="radar-comparison__col-excel">Puntual / Por pago</td>
                            </tr>
                            <tr>
                                <td>IA Analysis</td>
                                <td class="radar-comparison__col-radar">
                                    <span class="radar-comparison__val-icon">🤖</span>
                                    Incluido (Nichos/Guiones)
                                </td>
                                <td class="radar-comparison__col-excel">No incluido</td>
                            </tr>
                            <tr>
                                <td>Precio</td>
                                <td class="radar-comparison__col-radar">
                                    <div class="radar-comparison__price">79€<span>/mes</span></div>
                                </td>
                                <td class="radar-comparison__col-excel">
                                    <div class="radar-comparison__price-alt">Desde 2€<span>/listado</span></div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="radar-comparison__cta">
                    <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" class="radar-btn radar-btn--primary">
                        Activar Radar
                    </a>
                    <p>
                        O <a href="<?= site_url('billing/single_checkout?period=30days') ?>">descarga un listado puntual</a>
                    </p>
                </div>
            </div>
        </div>
    </section>

    <section class="radar-section">
        <div class="container">
            <div class="radar-heading radar-heading--center">
                <div class="radar-kicker">FAQ</div>
                <h2 class="radar-title">Preguntas frecuentes</h2>
            </div>

            <div class="radar-faq">
                <div class="radar-faq__item">
                    <h3>¿De dónde salen los datos?</h3>
                    <p>Los datos se obtienen diariamente del BORME y de registros mercantiles oficiales en España.</p>
                </div>
                <div class="radar-faq__item">
                    <h3>¿Cada cuánto se actualiza el radar?</h3>
                    <p>Radar se actualiza diariamente con todas las nuevas constituciones detectadas.</p>
                </div>
                <div class="radar-faq__item">
                    <h3>¿Puedo cancelar la suscripción?</h3>
                    <p>Sí. Es una suscripción mensual sin permanencia. Puedes cancelarla cuando quieras.</p>
                </div>
                <div class="radar-faq__item">
                    <h3>¿Puedo exportar los leads?</h3>
                    <p>Sí. Puedes exportar los leads filtrados en formato Excel o CSV directamente desde Radar.</p>
                </div>
                <div class="radar-faq__item">
                    <h3>¿Hay permanencia?</h3>
                    <p>No. No existe permanencia ni compromiso de permanencia.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="radar-final">
        <div class="container">
            <div class="radar-final__shell">
                <div class="radar-kicker radar-kicker--dark">Empieza hoy</div>
                <h2>Empieza a detectar nuevas empresas hoy</h2>
                <p>
                    Accede al Radar y convierte nuevas constituciones mercantiles en oportunidades comerciales reales cada día.
                </p>
                <a href="<?= site_url('checkout/radar-export?type=subscription&plan=radar') ?>" class="radar-btn radar-btn--yellow">
                    Activar Radar
                </a>
                <small>Sin permanencia · Cancela cuando quieras</small>
            </div>
        </div>
    </section>

</main>

<?= view('partials/footer') ?>
</body>
</html>