<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head', [
        'title'       => $title ?? 'Perfil de Riesgo y Solvencia Mercantil de Empresas | APIEmpresas',
        'excerptText' => $excerptText ?? 'Comprueba el semáforo de solvencia, scoring IES (0-100), alertas de quiebra en BORME y depósito de cuentas de cualquier empresa en España mediante su CIF.',
        'canonical'   => $canonical ?? site_url('perfil-de-riesgo'),
        'robots'      => $robots ?? 'index,follow'
    ]) ?>

    <style>
        :root {
            --rp-blue: #2563eb;
            --rp-blue-dark: #1d4ed8;
            --rp-blue-light: #eff6ff;
            --rp-dark: #0f172a;
            --rp-slate: #64748b;
            --rp-border: #e2e8f0;
            --rp-bg: #f8fafc;
        }

        .rp-hero {
            padding: 64px 0 50px;
            background: radial-gradient(ellipse 90% 80% at 50% 0%, #163674 0%, #0b1c40 50%, #081734 100%);
            border-bottom: 1px solid rgba(255, 255, 255, 0.10);
            position: relative;
            overflow: hidden;
            color: #ffffff;
        }

        .rp-hero-dots {
            position: absolute;
            inset: 0;
            background-image: 
                linear-gradient(to right, rgba(255, 255, 255, 0.06) 1px, transparent 1px),
                linear-gradient(to bottom, rgba(255, 255, 255, 0.06) 1px, transparent 1px);
            background-size: 32px 32px;
            pointer-events: none;
            mask-image: radial-gradient(ellipse 80% 60% at 50% 30%, #000 40%, transparent 90%);
            -webkit-mask-image: radial-gradient(ellipse 80% 60% at 50% 30%, #000 40%, transparent 90%);
        }

        .rp-hero-glow-1 {
            position: absolute;
            top: -80px;
            left: 20%;
            width: 500px;
            height: 500px;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.28) 0%, transparent 65%);
            pointer-events: none;
            filter: blur(40px);
        }

        .rp-hero-glow-2 {
            position: absolute;
            top: -40px;
            right: 20%;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(16, 185, 129, 0.18) 0%, transparent 65%);
            pointer-events: none;
            filter: blur(45px);
        }

        .rp-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(37, 99, 235, 0.22);
            color: #93c5fd;
            border: 1px solid rgba(96, 165, 250, 0.35);
            padding: 7px 18px;
            border-radius: 999px;
            font-size: 0.82rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            margin-bottom: 22px;
            box-shadow: 0 4px 15px rgba(11, 28, 64, 0.3);
            backdrop-filter: blur(10px);
        }

        .rp-badge-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: #10b981;
            box-shadow: 0 0 10px #10b981;
            animation: rpPulse 2s infinite;
        }

        @keyframes rpPulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.3); opacity: 0.6; }
        }

        .rp-title {
            font-size: clamp(2rem, 4.5vw, 3.1rem);
            font-weight: 900;
            color: #ffffff;
            margin: 0 0 16px 0;
            line-height: 1.15;
            letter-spacing: -0.035em;
        }

        .rp-title .rp-grad {
            background: linear-gradient(135deg, #60a5fa 0%, #34d399 55%, #38bdf8 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            display: inline-block;
        }

        .rp-subtitle {
            font-size: 1.15rem;
            color: #cbd5e1;
            max-width: 760px;
            margin: 0 auto 32px;
            line-height: 1.65;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.25);
        }

        /* Search Card */
        .rp-search-box {
            background: #ffffff;
            border: 1.5px solid rgba(255, 255, 255, 0.8);
            border-radius: 22px;
            box-shadow: 0 20px 50px -10px rgba(0, 0, 0, 0.35), 0 0 40px rgba(37, 99, 235, 0.25);
            max-width: 760px;
            margin: 0 auto;
            padding: 10px;
            position: relative;
            z-index: 30;
        }

        .rp-search-form {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @media (max-width: 640px) {
            .rp-search-form {
                flex-direction: column;
            }
        }

        .rp-input-wrap {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
            width: 100%;
        }

        .rp-input-icon {
            position: absolute;
            left: 18px;
            color: #94a3b8;
            pointer-events: none;
            display: flex;
            align-items: center;
        }

        .rp-search-input {
            width: 100%;
            padding: 16px 44px 16px 50px;
            border: 1.5px solid #e2e8f0;
            border-radius: 16px;
            font-size: 1.05rem;
            color: #0f172a;
            background: #f8fafc;
            outline: none;
            transition: all 0.2s ease;
            box-sizing: border-box;
            font-weight: 500;
        }

        .rp-search-input:focus {
            background: #ffffff;
            border-color: var(--rp-blue);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.15);
        }

        .rp-input-clear {
            position: absolute;
            right: 14px;
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 4px;
            display: none;
            border-radius: 50%;
        }
        .rp-input-clear:hover { color: var(--rp-dark); }

        .rp-btn-submit {
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #ffffff;
            border: none;
            padding: 16px 32px;
            border-radius: 16px;
            font-weight: 800;
            font-size: 1.02rem;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.2s ease;
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.35);
            white-space: nowrap;
        }

        @media (max-width: 640px) {
            .rp-btn-submit { width: 100%; }
        }

        .rp-btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(37, 99, 235, 0.45);
        }

        /* Autocomplete dropdown */
        .rp-suggestions-menu {
            position: absolute;
            top: calc(100% + 8px);
            left: 0;
            right: 0;
            background: #ffffff;
            border: 1px solid var(--rp-border);
            border-radius: 16px;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.15);
            max-height: 380px;
            overflow-y: auto;
            display: none;
            z-index: 1000;
            padding: 6px;
            text-align: left;
        }

        .rp-suggestion-item {
            padding: 12px 16px;
            border-radius: 10px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            transition: background 0.15s ease;
        }

        .rp-suggestion-item:hover,
        .rp-suggestion-item.selected {
            background: #eff6ff;
        }

        .rp-suggestion-name {
            font-weight: 700;
            color: #0f172a;
            font-size: 0.95rem;
        }

        .rp-suggestion-meta {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 2px;
        }

        .rp-suggestion-cif {
            background: #f1f5f9;
            color: #1e293b;
            padding: 3px 8px;
            border-radius: 6px;
            font-size: 0.78rem;
            font-weight: 800;
            font-family: monospace;
            white-space: nowrap;
        }

        /* Sample pills */
        .rp-sample-pills {
            margin-top: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 8px;
            font-size: 0.86rem;
            color: #cbd5e1;
        }

        .rp-pill-btn {
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.18);
            padding: 5px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
            color: #f1f5f9;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 600;
            backdrop-filter: blur(6px);
        }
        .rp-pill-btn:hover {
            border-color: #60a5fa;
            color: #ffffff;
            background: rgba(37, 99, 235, 0.35);
            box-shadow: 0 0 15px rgba(59, 130, 246, 0.4);
            transform: translateY(-1px);
        }

        /* Trust badges in hero */
        .rp-hero-trust {
            margin-top: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 16px;
            font-size: 0.84rem;
            color: #cbd5e1;
            font-weight: 500;
        }
        .rp-hero-trust-item {
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        .rp-hero-trust-sep {
            color: rgba(255, 255, 255, 0.25);
        }

        /* Results section */
        .rp-results-container {
            padding: 40px 0 60px;
        }

        .rp-company-summary-bar {
            background: #ffffff;
            border: 1px solid var(--rp-border);
            border-radius: 16px 16px 0 0;
            padding: 20px 24px;
            border-bottom: 2px solid #f1f5f9;
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
        }

        .rp-company-header-title {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 0;
            font-size: 1.35rem;
            font-weight: 900;
            color: var(--rp-dark);
        }

        .rp-company-cif-tag {
            background: #eff6ff;
            color: #1d4ed8;
            border: 1px solid #bfdbfe;
            padding: 3px 10px;
            border-radius: 6px;
            font-family: monospace;
            font-size: 0.88rem;
            font-weight: 800;
        }

        .rp-card-main {
            background: #ffffff;
            border: 1px solid var(--rp-border);
            border-top: none;
            border-radius: 0 0 16px 16px;
            overflow: hidden;
            box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
            margin-bottom: 24px;
        }

        /* Explanatory Guide Section */
        .rp-features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(260px, 1fr));
            gap: 20px;
            margin: 40px 0;
        }

        .rp-feature-card {
            background: #ffffff;
            border: 1px solid var(--rp-border);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.03);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }
        .rp-feature-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.06);
        }

        .rp-feature-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-bottom: 16px;
        }

        .rp-feature-title {
            font-size: 1.1rem;
            font-weight: 800;
            color: var(--rp-dark);
            margin: 0 0 8px 0;
        }

        .rp-feature-desc {
            font-size: 0.9rem;
            color: var(--rp-slate);
            line-height: 1.5;
            margin: 0;
        }

        /* Scale brackets */
        .rp-brackets-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-top: 20px;
        }
        @media (max-width: 768px) {
            .rp-brackets-grid { grid-template-columns: 1fr; }
        }

        .rp-bracket-card {
            border-radius: 14px;
            padding: 20px;
            border: 1.5px solid transparent;
        }

        .rp-faq-item {
            background: #ffffff;
            border: 1px solid var(--rp-border);
            border-radius: 14px;
            margin-bottom: 12px;
            overflow: hidden;
        }
        .rp-faq-question {
            padding: 18px 22px;
            font-weight: 800;
            color: var(--rp-dark);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            user-select: none;
        }
        .rp-faq-answer {
            padding: 0 22px 18px 22px;
            color: #475569;
            font-size: 0.92rem;
            line-height: 1.6;
            display: none;
        }
        .rp-faq-item.active .rp-faq-answer {
            display: block;
        }
        .rp-faq-item.active .rp-faq-arrow {
            transform: rotate(180deg);
        }
    </style>
</head>

<body>
    <?= view('partials/header') ?>

    <!-- HERO & SEARCH SECTION -->
    <section class="rp-hero">
        <!-- Background Tech Mesh & Ambient Glow Orbs -->
        <div class="rp-hero-dots"></div>
        <div class="rp-hero-glow-1"></div>
        <div class="rp-hero-glow-2"></div>

        <div class="container" style="text-align: center; position: relative; z-index: 2;">
            <div class="rp-badge">
                <span class="rp-badge-dot"></span>
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"></polyline></svg>
                <!-- "Scoring IES Oficial" decía que nuestra puntuación es oficial.
                     Oficial es la FUENTE (BORME, Registro Mercantil); el IES lo
                     calculamos nosotros. Se dice de dónde salen los datos, que es
                     lo verificable y además el argumento más fuerte. -->
                <span>Auditoría Mercantil &bull; Scoring IES sobre fuentes oficiales</span>
            </div>

            <h1 class="rp-title">
                Consulta el <span class="rp-grad">Perfil de Riesgo</span> de Cualquier Empresa en España
            </h1>

            <p class="rp-subtitle">
                Evalúa la solvencia mercantil, incidencias judiciales en el BORME, depósito de cuentas anuales y contratos públicos de cualquier cliente o proveedor introduciendo su CIF.
            </p>

            <!-- SEARCH CARD -->
            <div class="rp-search-box">
                <form class="rp-search-form" id="rpSearchForm" method="GET" action="<?= site_url('perfil-de-riesgo') ?>">
                    <div class="rp-input-wrap">
                        <span class="rp-input-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                        </span>
                        <input type="text" 
                               name="cif" 
                               id="rpSearchInput" 
                               class="rp-search-input" 
                               placeholder="Introduce CIF o razón social (ej: B87654321, Mercadona...)" 
                               value="<?= esc($cif ?? '') ?>" 
                               autocomplete="off" 
                               required>
                        <button type="button" class="rp-input-clear" id="rpInputClear" title="Limpiar">&times;</button>
                        
                        <!-- Autocomplete dropdown -->
                        <div class="rp-suggestions-menu" id="rpSuggestionsMenu"></div>
                    </div>

                    <button type="submit" class="rp-btn-submit" id="rpSubmitBtn">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"></polyline></svg>
                        <span>Analizar Riesgo</span>
                    </button>
                </form>
            </div>


            <!-- Trust micro-stats -->
            <div class="rp-hero-trust">
                <span class="rp-hero-trust-item">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Fuentes Oficiales BORME
                </span>
                <span class="rp-hero-trust-sep">&bull;</span>
                <span class="rp-hero-trust-item">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Depósito Cuentas Anuales
                </span>
                <span class="rp-hero-trust-sep">&bull;</span>
                <span class="rp-hero-trust-item">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="#34d399" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    Contratos y Subvenciones del Estado
                </span>
            </div>
        </div>
    </section>

    <!-- RESULTS OR EXPLANATORY SECTION -->
    <main class="rp-results-container">
        <div class="container" id="rpResultWrapper">
            
            <?php if (!empty($cif)): ?>
                <!-- CIF SEARCH HAS BEEN PERFORMED -->
                <?php if (empty($riskData['found'])): ?>
                    <!-- COMPANY NOT FOUND -->
                    <div style="background: #ffffff; border: 1.5px solid #fed7aa; border-radius: 20px; padding: 40px 30px; text-align: center; max-width: 680px; margin: 0 auto; box-shadow: 0 10px 25px rgba(0,0,0,0.03);">
                        <div style="width: 64px; height: 64px; border-radius: 50%; background: #ffedd5; color: #c2410c; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 16px;">
                            🔍
                        </div>
                        <h2 style="font-size: 1.45rem; font-weight: 900; color: #0f172a; margin-bottom: 8px;">No se encontró la empresa</h2>
                        <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 24px; line-height: 1.5;">
                            No hemos encontrado ninguna entidad mercantil registrada con el identificador <strong>«<?= esc($cif) ?>»</strong>. Por favor, asegúrate de que el CIF tenga el formato correcto (ej: <code>B12345678</code>) o escribe el nombre social completo.
                        </p>
                        <div style="display: flex; justify-content: center; gap: 12px; flex-wrap: wrap;">
                            <button type="button" onclick="document.getElementById('rpSearchInput').focus(); document.getElementById('rpSearchInput').select();" style="background: #2563eb; color: #fff; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 700; cursor: pointer;">
                                Buscar otro CIF
                            </button>
                            <a href="<?= site_url('search_company?q=' . urlencode($cif)) ?>" style="background: #f1f5f9; color: #334155; padding: 12px 20px; border-radius: 10px; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center;">
                                Buscar en el directorio general &rarr;
                            </a>
                        </div>
                    </div>

                <?php elseif (empty($riskData['riskProfile'])): ?>
                    <!-- COMPANY FOUND BUT NO RISK PROFILE CALCULATED -->
                    <div style="background: #ffffff; border: 1.5px solid #e2e8f0; border-radius: 20px; padding: 40px 30px; text-align: center; max-width: 700px; margin: 0 auto; box-shadow: 0 10px 25px rgba(0,0,0,0.03);">
                        <div style="width: 64px; height: 64px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 16px;">
                            📋
                        </div>
                        <h2 style="font-size: 1.45rem; font-weight: 900; color: #0f172a; margin-bottom: 8px;">
                            <?= esc($riskData['company']['name'] ?? 'Empresa') ?> (<?= esc($riskData['cleanCif']) ?>)
                        </h2>
                        <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 24px; line-height: 1.5;">
                            Esta empresa consta inscrita en el registro mercantil, pero su perfil de riesgo algorítmico aún no ha sido calculado o requiere actualización manual en nuestros servidores.
                        </p>
                        <?php 
                        $slugVal = $riskData['company']['slug'] ?? url_title($riskData['company']['name'] ?? '', '-', true);
                        $compUrl = site_url('empresa/' . ($riskData['company']['id'] ?? 0) . '-' . $slugVal);
                        ?>
                        <a href="<?= $compUrl ?>" style="background: #2563eb; color: #fff; padding: 12px 24px; border-radius: 10px; font-weight: 800; text-decoration: none; display: inline-flex; align-items: center; gap: 8px;">
                            Ver datos mercantiles y administradores &rarr;
                        </a>
                    </div>

                <?php else: ?>
                    <!-- COMPANY FOUND & HAS RISK PROFILE -->
                    <?php 
                    $company = $riskData['company'];
                    $riskProfile = $riskData['riskProfile'];
                    $riskQuota = $riskData['riskQuota'];
                    $contracts = $riskData['contracts'] ?? [];
                    $subsidies = $riskData['subsidies'] ?? [];
                    $isLoggedIn = session('logged_in') || (int)(session('user_id') ?? 0) > 0;
                    $redirectPath = 'perfil-de-riesgo?cif=' . urlencode((string)$company['cif']);
                    $slugVal = $company['slug'] ?? url_title($company['name'] ?? '', '-', true);
                    $compFullUrl = site_url('empresa/' . ($company['id'] ?? 0) . '-' . $slugVal);
                    ?>

                    <!-- Company summary bar -->
                    <div class="rp-company-summary-bar">
                        <div>
                            <h2 class="rp-company-header-title">
                                <span><?= esc($company['name']) ?></span>
                                <span class="rp-company-cif-tag"><?= esc($company['cif']) ?></span>
                            </h2>
                            <div style="font-size: 0.85rem; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                                <?php if (!empty($company['province'])): ?>
                                    <span>📍 <?= esc($company['province']) ?></span>
                                <?php endif; ?>
                                <?php if (!empty($company['cnae_label'])): ?>
                                    <span>🏢 <?= esc($company['cnae_label']) ?></span>
                                <?php endif; ?>
                                <span>🛡️ Estado: Activa</span>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 10px;">
                            <a href="<?= $compFullUrl ?>" target="_blank" style="background: #f8fafc; border: 1px solid #cbd5e1; color: #1e293b; padding: 8px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;" title="Abrir ficha completa en nueva pestaña">
                                <span>Ficha Completa</span>
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                            </a>
                            <button type="button" onclick="rpResetSearch();" style="background: none; border: 1px dashed #cbd5e1; color: #64748b; padding: 8px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer;">
                                Consultar otra empresa
                            </button>
                        </div>
                    </div>

                    <!-- RISK WIDGET CARD -->
                    <div class="rp-card-main" id="risk-profile-container" style="padding: 24px; position: relative; background: #fff; min-height: 260px;">
                        <?php if ($isLoggedIn): ?>
                            <?php if (!empty($riskQuota['allowed'])): ?>
                                <!-- PERFIL COMPLETO (DESBLOQUEADO / SUSCRIPCIÓN) -->
                                <?= view('partials/company_risk_profile', [
                                    'riskProfile' => $riskProfile,
                                    'company'     => $company,
                                    'contracts'   => $contracts,
                                    'subsidies'   => $subsidies,
                                    'riskQuota'   => $riskQuota
                                ]) ?>
                            <?php elseif (!empty($riskQuota['can_unlock'])): ?>
                                <!-- DESBLOQUEO EXPLÍCITO (queda cuota o créditos) -->
                                <?= view('partials/company_risk_locked', [
                                    'riskProfile' => $riskProfile,
                                    'company'     => $company,
                                    'riskQuota'   => $riskQuota
                                ]) ?>
                            <?php else: ?>
                                <!-- PAYWALL CUOTA MENSUAL ALCANZADA (3/3) -->
                                <?= view('partials/company_risk_paywall', [
                                    'company'     => $company,
                                    'riskQuota'   => $riskQuota,
                                    'riskProfile' => $riskProfile,
                                ]) ?>
                            <?php endif; ?>
                        <?php else: ?>
                            <!-- TEASER PARA USUARIOS PÚBLICOS (NO REGISTRADOS) -->
                            <?= view('partials/company_risk_teaser', [
                                'riskProfile'  => $riskProfile,
                                'company'      => $company,
                                'companyName'  => $company['name'],
                                'redirectPath' => $redirectPath
                            ]) ?>
                        <?php endif; ?>
                    </div>

                <?php endif; ?>

            <?php else: ?>
                <!-- NO SEARCH PERFORMED YET: EXPLANATORY GUIDE / LANDING -->

                <!-- VALUE PILLARS -->
                <div style="text-align: center; max-width: 800px; margin: 0 auto 30px;">
                    <h2 style="font-size: 1.85rem; font-weight: 900; color: #0f172a; margin-bottom: 10px;">
                        ¿Cómo funciona el Perfil de Riesgo Corporativo?
                    </h2>
                    <p style="color: #64748b; font-size: 1.05rem; line-height: 1.6;">
                        Nuestro motor algorítmico audita diariamente las publicaciones del BORME, el Registro Mercantil Central y las plataformas de contratación pública para consolidar el <strong>Índice de Estabilidad Societaria (IES)</strong>.
                    </p>
                </div>

                <div class="rp-features-grid">
                    <div class="rp-feature-card">
                        <div class="rp-feature-icon" style="background: #eff6ff; color: #2563eb;">
                            ⚡
                        </div>
                        <h3 class="rp-feature-title">Índice IES (0 a 100)</h3>
                        <p class="rp-feature-desc">
                            Sintetiza en una sola cifra la gravedad de lo que consta publicado sobre una sociedad: concursos, cierres registrales, disoluciones y retrasos en el depósito de cuentas.
                        </p>
                    </div>

                    <div class="rp-feature-card">
                        <div class="rp-feature-icon" style="background: #fee2e2; color: #ef4444;">
                            ⚠️
                        </div>
                        <h3 class="rp-feature-title">Alertas BORME y Quiebras</h3>
                        <p class="rp-feature-desc">
                            Monitoriza declaraciones de insolvencia, apertura de fase de liquidación, concursos de acreedores y revocación de administradores clave.
                        </p>
                    </div>

                    <div class="rp-feature-card">
                        <div class="rp-feature-icon" style="background: #fef3c7; color: #d97706;">
                            📑
                        </div>
                        <h3 class="rp-feature-title">Depósito de Cuentas</h3>
                        <p class="rp-feature-desc">
                            Verifica si la empresa cumple regularmente con el depósito obligatorio de sus cuentas anuales o si se expone a cierres de hoja registral por la AEAT.
                        </p>
                    </div>

                    <div class="rp-feature-card">
                        <div class="rp-feature-icon" style="background: #dcfce7; color: #16a34a;">
                            🏛️
                        </div>
                        <h3 class="rp-feature-title">Contratación Pública</h3>
                        <p class="rp-feature-desc">
                            Comprueba si la empresa es adjudicataria habitual de contratos del Estado y subvenciones públicas, un factor estabilizador clave de solvencia.
                        </p>
                    </div>
                </div>

                <!-- SCORING EXPLANATION BRACKETS -->
                <div style="background: #ffffff; border: 1px solid var(--rp-border); border-radius: 20px; padding: 32px; margin-bottom: 40px; box-shadow: 0 4px 15px rgba(15, 23, 42, 0.03);">
                    <h3 style="font-size: 1.35rem; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 8px;">
                        Interpretación del Semáforo de Riesgo
                    </h3>
                    <p style="color: #64748b; font-size: 0.92rem; margin-bottom: 20px;">
                        El IES asigna una puntuación objetiva fundamentada en el cruce de más de 40 eventos registrales oficiales:
                    </p>

                    <div class="rp-brackets-grid">
                        <div class="rp-bracket-card" style="background: #f0fdf4; border-color: #bbf7d0;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <strong style="color: #15803d; font-size: 1.1rem;">0 - <?= (int) solvencia('umbralMedio', 30) - 1 ?> &bull; SIN INCIDENCIAS O LEVES</strong>
                                <span style="background: #dcfce7; color: #16a34a; font-weight: 800; padding: 2px 8px; border-radius: 6px; font-size: 0.75rem;">Favorable</span>
                            </div>
                            <p style="font-size: 0.85rem; color: #166534; line-height: 1.45; margin: 0;">
                                Empresa con gobernanza estable, cuentas presentadas en plazo, sin incidencias judiciales ni cambios drásticos de estructura.
                            </p>
                        </div>

                        <div class="rp-bracket-card" style="background: #fffbeb; border-color: #fde68a;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <strong style="color: #b45309; font-size: 1.1rem;"><?= (int) solvencia('umbralMedio', 30) ?> - <?= (int) solvencia('umbralAlto', 60) - 1 ?> &bull; INCIDENCIAS A REVISAR</strong>
                                <span style="background: #fef3c7; color: #d97706; font-weight: 800; padding: 2px 8px; border-radius: 6px; font-size: 0.75rem;">Atención</span>
                            </div>
                            <p style="font-size: 0.85rem; color: #78350f; line-height: 1.45; margin: 0;">
                                Existen observaciones moderadas: retraso en cuentas, rotación de cargos directivos o reducciones de capital social no habituales.
                            </p>
                        </div>

                        <div class="rp-bracket-card" style="background: #fef2f2; border-color: #fecaca;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 8px;">
                                <strong style="color: #b91c1c; font-size: 1.1rem;"><?= (int) solvencia('umbralAlto', 60) ?> - 100 &bull; INCIDENCIAS GRAVES</strong>
                                <span style="background: #fee2e2; color: #ef4444; font-weight: 800; padding: 2px 8px; border-radius: 6px; font-size: 0.75rem;">Alerta Crítica</span>
                            </div>
                            <p style="font-size: 0.85rem; color: #7f1d1d; line-height: 1.45; margin: 0;">
                                Alertas graves publicadas en BORME: concursos de acreedores, disolución societaria, ejecuciones o cierre definitivo de hoja registral.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- USE CASES -->
                <div style="margin-bottom: 40px;">
                    <h3 style="font-size: 1.45rem; font-weight: 900; color: #0f172a; text-align: center; margin-bottom: 24px;">
                        ¿Para quién está diseñada esta herramienta?
                    </h3>

                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                        <div style="background: #ffffff; border: 1px solid var(--rp-border); border-radius: 16px; padding: 24px;">
                            <div style="font-size: 1.5rem; margin-bottom: 10px;">💼</div>
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0 0 8px;">Departamentos de Crédito y Cobro</h4>
                            <p style="font-size: 0.88rem; color: #64748b; line-height: 1.5; margin: 0;">
                                Valida rápidamente si conceder crédito comercial a 30, 60 o 90 días o exigir prepago a nuevos clientes según su historial registral.
                            </p>
                        </div>

                        <div style="background: #ffffff; border: 1px solid var(--rp-border); border-radius: 16px; padding: 24px;">
                            <div style="font-size: 1.5rem; margin-bottom: 10px;">⚖️</div>
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0 0 8px;">Compliance y Homologación de Proveedores</h4>
                            <p style="font-size: 0.88rem; color: #64748b; line-height: 1.5; margin: 0;">
                                Audita a proveedores estratégicos antes de firmar contratos marco para evitar interrupciones de suministro por insolvencia sobrevenida.
                            </p>
                        </div>

                        <div style="background: #ffffff; border: 1px solid var(--rp-border); border-radius: 16px; padding: 24px;">
                            <div style="font-size: 1.5rem; margin-bottom: 10px;">🚀</div>
                            <h4 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0 0 8px;">Equipos de Ventas B2B</h4>
                            <p style="font-size: 0.88rem; color: #64748b; line-height: 1.5; margin: 0;">
                                Cualifica leads en segundos antes de agendar reuniones con prospectos que puedan encontrarse en situación concursal o inactiva.
                            </p>
                        </div>
                    </div>
                </div>

                <!-- FAQS -->
                <div style="max-width: 840px; margin: 0 auto;">
                    <h3 style="font-size: 1.45rem; font-weight: 900; color: #0f172a; text-align: center; margin-bottom: 24px;">
                        Preguntas Frecuentes
                    </h3>

                    <div class="rp-faq-item">
                        <div class="rp-faq-question" onclick="this.parentElement.classList.toggle('active');">
                            <span>¿De dónde proceden los datos del perfil de riesgo?</span>
                            <svg class="rp-faq-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                        <div class="rp-faq-answer">
                            Los datos proceden de fuentes oficiales del Estado español: Boletín Oficial del Registro Mercantil (BORME), Registro Mercantil Central, Plataforma de Contratación del Sector Público y la Base de Datos Nacional de Subvenciones.
                        </div>
                    </div>

                    <div class="rp-faq-item">
                        <div class="rp-faq-question" onclick="this.parentElement.classList.toggle('active');">
                            <span>¿Cuántas consultas gratuitas puedo realizar?</span>
                            <svg class="rp-faq-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                        <div class="rp-faq-answer">
                            Todos los usuarios registrados en APIEmpresas disponen de <strong><?= (int) solvencia('consultasGratis', 3) ?> consultas gratuitas de empresas distintas cada mes natural</strong> sin necesidad de tarjeta bancaria. Si necesitas consultar un mayor volumen de empresas de forma continuada, puedes suscribirte al plan <strong>Solvencia Pro</strong> (<?= solvencia('precios.pro_mensual', '29 €') ?>/mes) o descargar informes individuales en PDF por <?= solvencia('precios.pdf', '3,90 €') ?> + IVA.
                        </div>
                    </div>

                    <div class="rp-faq-item">
                        <div class="rp-faq-question" onclick="this.parentElement.classList.toggle('active');">
                            <span>¿Puedo descargar un informe en PDF para adjuntar a mis expedientes?</span>
                            <svg class="rp-faq-arrow" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"></polyline></svg>
                        </div>
                        <div class="rp-faq-answer">
                            <!-- Decía "dictamen oficial ... con certificación de solvencia".
                                 Una certificación de solvencia la expide un registrador o la
                                 AEAT, no nosotros: prometerla es lo que convierte una queja
                                 en una reclamación. Lo que sí se entrega —y se puede
                                 sostener— es el informe fechado con los datos y su origen. -->
                            Sí. Puedes emitir y descargar de inmediato un informe en PDF, con fecha de emisión, la puntuación de solvencia, el semáforo de riesgo y el detalle de los eventos registrales publicados en el BORME, por <?= solvencia('precios.pdf', '3,90 €') ?> + IVA, o incluido en tu suscripción Solvencia Pro.
                        </div>
                    </div>
                </div>

            <?php endif; ?>

        </div>
    </main>

    <?= view('partials/footer') ?>

    <!-- PDF Checkout Modal Component -->
    <?= view('partials/risk_pdf_modal', [
        'company' => !empty($riskData['company']) ? $riskData['company'] : []
    ]) ?>

    <!-- Client-side Autocomplete & AJAX Handler -->
    <script>
    (function() {
        const input = document.getElementById('rpSearchInput');
        const clearBtn = document.getElementById('rpInputClear');
        const menu = document.getElementById('rpSuggestionsMenu');
        const form = document.getElementById('rpSearchForm');
        const submitBtn = document.getElementById('rpSubmitBtn');
        const resultWrapper = document.getElementById('rpResultWrapper');

        let debounceTimer = null;
        let selectedIndex = -1;
        let isFetching = false;

        if (!input) return;

        // Clear button visibility
        const toggleClearBtn = () => {
            if (clearBtn) {
                clearBtn.style.display = input.value.trim().length > 0 ? 'block' : 'none';
            }
        };

        input.addEventListener('input', function() {
            toggleClearBtn();
            clearTimeout(debounceTimer);
            const query = input.value.trim();

            if (query.length < 3) {
                hideSuggestions();
                return;
            }

            debounceTimer = setTimeout(() => {
                fetchSuggestions(query);
            }, 250);
        });

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                input.value = '';
                toggleClearBtn();
                hideSuggestions();
                input.focus();
            });
        }

        // Keyboard navigation in suggestions
        input.addEventListener('keydown', function(e) {
            const items = menu ? menu.querySelectorAll('.rp-suggestion-item') : [];
            if (!menu || menu.style.display !== 'block' || items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                selectedIndex = (selectedIndex + 1) % items.length;
                updateSelectedSuggestion(items);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                selectedIndex = (selectedIndex - 1 + items.length) % items.length;
                updateSelectedSuggestion(items);
            } else if (e.key === 'Enter') {
                if (selectedIndex >= 0 && items[selectedIndex]) {
                    e.preventDefault();
                    items[selectedIndex].click();
                }
            } else if (e.key === 'Escape') {
                hideSuggestions();
            }
        });

        document.addEventListener('click', function(e) {
            if (!input.contains(e.target) && (!menu || !menu.contains(e.target))) {
                hideSuggestions();
            }
        });

        function hideSuggestions() {
            if (menu) {
                menu.style.display = 'none';
                menu.innerHTML = '';
            }
            selectedIndex = -1;
        }

        function updateSelectedSuggestion(items) {
            items.forEach((item, idx) => {
                if (idx === selectedIndex) {
                    item.classList.add('selected');
                    item.scrollIntoView({ block: 'nearest' });
                } else {
                    item.classList.remove('selected');
                }
            });
        }

        function fetchSuggestions(query) {
            const url = '<?= site_url("autocompletado-cif-empresas/get") ?>?q=' + encodeURIComponent(query);
            fetch(url)
                .then(res => res.json())
                .then(res => {
                    if (!res.success || !res.data || res.data.length === 0) {
                        hideSuggestions();
                        return;
                    }

                    renderSuggestions(res.data);
                })
                .catch(() => hideSuggestions());
        }

        function renderSuggestions(data) {
            if (!menu) return;
            menu.innerHTML = '';
            selectedIndex = -1;

            data.slice(0, 8).forEach(item => {
                const el = document.createElement('div');
                el.className = 'rp-suggestion-item';
                
                const metaText = [item.cnae_label, item.address].filter(Boolean).join(' • ');

                el.innerHTML = `
                    <div style="flex: 1; min-width: 0;">
                        <div class="rp-suggestion-name">${escapeHtml(item.name)}</div>
                        <div class="rp-suggestion-meta">${escapeHtml(metaText)}</div>
                    </div>
                    <span class="rp-suggestion-cif">${escapeHtml(item.cif)}</span>
                `;

                el.addEventListener('click', function() {
                    input.value = item.cif || item.name;
                    hideSuggestions();
                    performAjaxLookup(item.cif || item.name);
                });

                menu.appendChild(el);
            });

            menu.style.display = 'block';
        }

        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text || '';
            return div.innerHTML;
        }

        // Form submission via AJAX to prevent full page reload
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const val = input.value.trim();
            if (!val) return;
            hideSuggestions();
            performAjaxLookup(val);
        });

        function performAjaxLookup(cifOrQuery) {
            if (isFetching) return;
            isFetching = true;

            const originalBtnHtml = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="fa-spin" style="animation: spin 1s linear infinite;"><path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>
                <span>Consultando...</span>
            `;

            // Display loading skeleton in results
            resultWrapper.innerHTML = `
                <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 20px; padding: 40px; text-align: center; max-width: 600px; margin: 20px auto; box-shadow: 0 10px 25px rgba(0,0,0,0.03);">
                    <div style="width: 50px; height: 50px; border: 4px solid #eff6ff; border-top-color: #2563eb; border-radius: 50%; margin: 0 auto 16px; animation: spin 1s linear infinite;"></div>
                    <h3 style="font-size: 1.25rem; font-weight: 800; color: #0f172a; margin: 0 0 6px;">Auditando ${escapeHtml(cifOrQuery)}</h3>
                    <p style="color: #64748b; font-size: 0.9rem; margin: 0;">Consultando BORME, cuentas anuales y registro mercantil...</p>
                </div>
            `;

            const lookupUrl = '<?= site_url("api/perfil-de-riesgo/lookup") ?>?cif=' + encodeURIComponent(cifOrQuery);

            fetch(lookupUrl)
                .then(res => res.json())
                .then(data => {
                    isFetching = false;
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;

                    if (!data.success) {
                        // Error message
                        resultWrapper.innerHTML = `
                            <div style="background: #ffffff; border: 1.5px solid #fed7aa; border-radius: 20px; padding: 40px 30px; text-align: center; max-width: 680px; margin: 0 auto; box-shadow: 0 10px 25px rgba(0,0,0,0.03);">
                                <div style="width: 64px; height: 64px; border-radius: 50%; background: #ffedd5; color: #c2410c; display: flex; align-items: center; justify-content: center; font-size: 2rem; margin: 0 auto 16px;">
                                    ⚠️
                                </div>
                                <h2 style="font-size: 1.45rem; font-weight: 900; color: #0f172a; margin-bottom: 8px;">Aviso de Consulta</h2>
                                <p style="color: #64748b; font-size: 0.95rem; margin-bottom: 24px; line-height: 1.5;">
                                    ${escapeHtml(data.message || 'No se ha podido procesar la consulta.')}
                                </p>
                                <button type="button" onclick="document.getElementById('rpSearchInput').focus(); document.getElementById('rpSearchInput').select();" style="background: #2563eb; color: #fff; border: none; padding: 12px 24px; border-radius: 10px; font-weight: 700; cursor: pointer;">
                                    Probar con otro CIF
                                </button>
                            </div>
                        `;
                        return;
                    }

                    // Success: render company summary bar + risk profile container
                    const comp = data.company;
                    const compFullUrl = '<?= site_url("empresa") ?>/' + comp.id;

                    // Update modal inputs if modal exists
                    const modalCompId = document.getElementById('risk-modal-company-id');
                    const modalCif = document.getElementById('risk-modal-cif');
                    if (modalCompId) modalCompId.value = comp.id;
                    if (modalCif) modalCif.value = comp.cif;

                    resultWrapper.innerHTML = `
                        <div class="rp-company-summary-bar">
                            <div>
                                <h2 class="rp-company-header-title">
                                    <span>${escapeHtml(comp.name)}</span>
                                    <span class="rp-company-cif-tag">${escapeHtml(comp.cif)}</span>
                                </h2>
                                <div style="font-size: 0.85rem; color: #64748b; margin-top: 4px; display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                                    ${comp.province ? `<span>📍 ${escapeHtml(comp.province)}</span>` : ''}
                                    ${comp.cnae_label ? `<span>🏢 ${escapeHtml(comp.cnae_label)}</span>` : ''}
                                    <span>🛡️ Estado: Activa</span>
                                </div>
                            </div>

                            <div style="display: flex; align-items: center; gap: 10px;">
                                <a href="${compFullUrl}" target="_blank" style="background: #f8fafc; border: 1px solid #cbd5e1; color: #1e293b; padding: 8px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 700; text-decoration: none; display: inline-flex; align-items: center; gap: 6px;">
                                    <span>Ficha Completa</span>
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                </a>
                                <button type="button" onclick="rpResetSearch();" style="background: none; border: 1px dashed #cbd5e1; color: #64748b; padding: 8px 14px; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer;">
                                    Consultar otra empresa
                                </button>
                            </div>
                        </div>

                        <div class="rp-card-main" id="risk-profile-container" style="padding: 24px; position: relative; background: #fff; min-height: 260px;">
                            ${data.html}
                        </div>
                    `;

                    // Update browser address bar seamlessly without reload
                    if (data.redirectUrl && window.history.pushState) {
                        window.history.pushState({ cif: comp.cif }, '', data.redirectUrl);
                    }

                    // Smooth scroll to results
                    resultWrapper.scrollIntoView({ behavior: 'smooth', block: 'start' });
                })
                .catch(err => {
                    console.error(err);
                    isFetching = false;
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                    alert('Error al consultar el perfil de riesgo. Por favor, inténtalo de nuevo.');
                });
        }

        // Global functions for quick test buttons
        window.rpSetAndSearch = function(cif, name) {
            input.value = cif;
            toggleClearBtn();
            hideSuggestions();
            performAjaxLookup(cif);
        };

        window.rpResetSearch = function() {
            input.value = '';
            toggleClearBtn();
            hideSuggestions();
            input.focus();
            if (window.history.pushState) {
                window.history.pushState({}, '', '<?= site_url("perfil-de-riesgo") ?>');
            }
            window.location.href = '<?= site_url("perfil-de-riesgo") ?>';
        };

    })();
    </script>

    <style>
    @keyframes spin {
        from { transform: rotate(0deg); }
        to { transform: rotate(360deg); }
    }
    </style>
</body>
</html>
