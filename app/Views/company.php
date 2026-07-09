<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head', [
        'title' => $title,
        'excerptText' => $meta_description,
        'canonical' => $canonical,
        'robots' => $robots,
    ]) ?>
    <link rel="preload" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    </noscript>

    <!-- Schema.org para la Empresa -->
    <script type="application/ld+json">
    {
      "@context": "https://schema.org",
      "@type": "Organization",
      "name": "<?= esc($companyName ?? $company['name'] ?? 'Empresa') ?>",
      <?php if (!empty($companyCif) && $companyCif !== 'Desconocido' && $companyCif !== '-'): ?>
      "vatID": "<?= esc($companyCif) ?>",
      "taxID": "<?= esc($companyCif) ?>",
      <?php endif; ?>
      "url": "<?= esc($canonical ?? current_url()) ?>",
      "address": {
        "@type": "PostalAddress",
        "addressRegion": "<?= esc($company['province'] ?? $company['provincia'] ?? '') ?>",
        "addressCountry": "ES"
      }
    }
    </script>
    <style>
        html { scroll-behavior: smooth; }
        
        /* API Toast */
        .api-toast {
            position: fixed;
            bottom: 24px;
            left: 50%;
            transform: translateX(-50%) translateY(100px);
            background: #0f172a;
            color: #f8fafc;
            padding: 12px 24px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            z-index: 9999;
            opacity: 0;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .api-toast.show {
            transform: translateX(-50%) translateY(0);
            opacity: 1;
        }

        .api-toast .btn-toast {
            background: #3b82f6;
            color: white;
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 700;
            font-size: 0.85rem;
            margin-left: 8px;
            transition: background 0.2s;
        }

        .api-toast .btn-toast:hover {
            background: #2563eb;
        }
        :root {
            --b2b-primary: #2563eb;
            --b2b-primary-light: #eff6ff;
            --b2b-text: #111827;
            --b2b-text-muted: #6b7280;
            --b2b-border: #e5e7eb;
            --b2b-bg: #f9fafb;
            --b2b-surface: #ffffff;
        }

        body {
            background-color: var(--b2b-bg);
            color: var(--b2b-text);
            font-family: 'Inter', system-ui, sans-serif;
        }

        /* Hero Card */
        .b2b-hero {
            background: var(--b2b-surface);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--b2b-border);
            margin-bottom: 16px;
            display: flex;
            gap: 24px;
            align-items: center;
        }

        .b2b-hero__logo {
            width: 72px;
            height: 72px;
            border-radius: 12px;
            background: var(--b2b-primary-light);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--b2b-primary);
            flex-shrink: 0;
            border: 1px solid #dbeafe;
        }

        .b2b-hero__content {
            flex: 1;
        }

        .b2b-hero__eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: var(--b2b-primary-light);
            color: var(--b2b-primary);
            padding: 4px 10px;
            border-radius: 8px;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }

        .b2b-hero__title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--b2b-text);
            margin: 0 0 4px 0;
            line-height: 1.3;
        }

        .b2b-hero__meta {
            display: flex;
            align-items: center;
            gap: 16px;
            color: var(--b2b-text-muted);
            font-size: 0.95rem;
            margin-bottom: 16px;
        }

        .b2b-hero__meta-item {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        
        /* Micro-animaciones premium */
        @keyframes custom-ping {
            0% { transform: scale(1); opacity: 0.8; }
            70%, 100% { transform: scale(2.5); opacity: 0; }
        }
        .status-dot-ping {
            animation: custom-ping 1.5s cubic-bezier(0, 0, 0.2, 1) infinite;
        }
        
        .ai-box-glow {
            position: relative;
            transition: all 0.4s ease;
            z-index: 1;
        }
        .ai-box-glow::before {
            content: '';
            position: absolute;
            top: -2px; left: -2px; right: -2px; bottom: -2px;
            background: linear-gradient(45deg, #3b82f6, #8b5cf6, #3b82f6);
            z-index: -1;
            border-radius: 18px;
            opacity: 0;
            transition: opacity 0.4s ease;
        }
        .ai-box-glow:hover::before {
            opacity: 0.25;
        }
        .ai-box-glow:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px -5px rgba(59, 130, 246, 0.1), 0 8px 10px -6px rgba(59, 130, 246, 0.1) !important;
        }

        .reveal-on-scroll {
            opacity: 1; /* SEO Fix: No ocultar contenido inicial a Googlebot */
            transform: none;
            transition: opacity 0.8s cubic-bezier(0.16, 1, 0.3, 1), transform 0.8s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }
        .reveal-on-scroll.is-visible {
            opacity: 1;
            transform: translateY(0);
        }
        
        .btn-share-icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px; height: 36px;
            border-radius: 50%;
            background: #f1f5f9;
            color: #64748b;
            transition: all 0.2s;
            border: 1px solid transparent;
            text-decoration: none;
        }
        .btn-share-icon:hover {
            background: #ffffff;
            color: #2563eb;
            border-color: #cbd5e1;
            transform: translateY(-2px);
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        .b2b-hero__actions {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
        }

        .b2b-btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.2s;
            cursor: pointer;
            border: 1px solid transparent;
        }

        .b2b-btn--primary {
            background: var(--b2b-primary);
            color: #fff;
            box-shadow: 0 2px 4px rgba(37, 99, 235, 0.2);
        }

        .b2b-btn--primary:hover {
            background: #1d4ed8;
            color: #fff;
        }

        .b2b-btn--outline-danger {
            background: #fff;
            color: #dc2626;
            border-color: #fecaca;
        }

        .b2b-btn--outline-danger:hover {
            background: #fef2f2;
        }

        .b2b-btn--outline-primary {
            background: #fff;
            color: var(--b2b-primary);
            border-color: #bfdbfe;
        }

        .b2b-btn--outline-primary:hover {
            background: var(--b2b-primary-light);
        }

        /* Tabs */
        .b2b-tabs {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            background: var(--b2b-surface);
            border-radius: 12px;
            padding: 8px 16px;
            margin-bottom: 24px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
            border: 1px solid var(--b2b-border);
            gap: 8px;
        }

        .b2b-tabs ul {
            display: flex;
            margin: 0;
            padding: 0;
            list-style: none;
            width: 100%;
            overflow-x: auto;
            overflow-y: hidden;
            scrollbar-width: none; /* Firefox */
        }
        .b2b-tabs ul::-webkit-scrollbar {
            display: none; /* Chrome, Safari */
        }

        .b2b-tabs li {
            flex: 1 1 auto;
            padding: 0 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
        }

        .b2b-tabs li:not(:last-child)::after {
            content: "";
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 1px;
            height: 16px;
            background: var(--b2b-border);
        }

        .b2b-tabs a {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--b2b-text-muted);
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            padding: 8px 4px;
            white-space: nowrap;
            transition: color 0.2s;
        }

        .b2b-tabs a:hover {
            color: var(--b2b-text);
        }

        .b2b-tabs a.active {
            color: var(--b2b-primary);
            font-weight: 600;
            border-bottom: 2px solid var(--b2b-primary);
            margin-bottom: -2px;
            /* Pull down to cover border if needed */
        }

        /* Layout */

        @media (min-width: 992px) {}


        /* B2B Layout Grid */
        .b2b-grid-2col {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 24px;
            align-items: start;
        }

        .b2b-grid-aside {
            display: grid;
            grid-template-columns: 6.5fr 3.5fr;
            gap: 32px;
            margin-bottom: 24px;
            align-items: start;
        }

        .b2b-grid-content-aside {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(0, 1fr);
            gap: 48px;
            margin-bottom: 24px;
            align-items: start;
        }

        @media (max-width: 991px) {

            .b2b-grid-2col,
            .b2b-grid-aside,
            .b2b-grid-content-aside {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            main {
                padding-top: 20px !important;
            }
            .container {
                padding-left: 15px !important;
                padding-right: 15px !important;
                width: 100% !important;
                max-width: 100% !important;
            }
            .b2b-hero {
                flex-direction: column !important;
                align-items: center !important;
                text-align: center;
                padding: 20px 16px !important;
                gap: 16px !important;
                width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                box-sizing: border-box !important;
            }
            .b2b-header-wrapper {
                width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                box-sizing: border-box !important;
            }
            .b2b-hero__avatar {
                width: 72px !important;
                height: 72px !important;
            }
            .b2b-hero__content {
                display: flex;
                flex-direction: column;
                align-items: center;
                width: 100%;
            }
            .b2b-hero__content h1 {
                font-size: 1.25rem !important;
                word-break: break-word;
                text-align: center;
                margin-bottom: 8px !important;
            }
            .b2b-hero__meta {
                justify-content: center !important;
                flex-wrap: wrap;
                gap: 8px !important;
            }
            .b2b-data-row {
                flex-direction: column !important;
                align-items: flex-start !important;
                gap: 4px !important;
            }
            .b2b-data-label {
                width: 100% !important;
            }
            .b2b-data-value {
                width: 100%;
                word-break: break-word;
            }
            .b2b-hero > div[style*="rotate(45deg)"] {
                right: -85px !important;
                top: 20px !important;
                font-size: 0.65rem !important;
                padding: 4px 0 !important;
            }
            .b2b-hero__actions {
                width: 100%;
                flex-direction: column !important;
            }
            .b2b-hero__actions a {
                width: 100%;
                justify-content: center;
            }
            .b2b-tabs ul {
                flex-wrap: nowrap;
                padding-bottom: 8px;
            }
            .b2b-card {
                padding: 16px !important;
                width: 100% !important;
                margin-left: 0 !important;
                margin-right: 0 !important;
                box-sizing: border-box !important;
            }
            .api-dev-grid {
                grid-template-columns: 1fr !important;
                padding: 1.5rem !important;
                gap: 1.5rem !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }
            .api-dev-info h3 {
                font-size: 1.35rem !important;
                flex-wrap: wrap;
            }
            .api-dev-info a {
                width: 100% !important;
                justify-content: center;
                text-align: center;
                padding: 0.875rem 1rem !important;
                white-space: normal;
                box-sizing: border-box !important;
            }
            .company-code-card {
                max-width: 100%;
                overflow-x: auto !important;
            }
            .company-code-card pre {
                white-space: pre-wrap !important;
                word-break: break-all !important;
            }
            .dash-cta-card {
                grid-template-columns: 1fr !important;
                text-align: center;
                padding: 2rem 1.5rem !important;
                gap: 1.5rem !important;
            }
            .dash-cta-card h3 {
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
                gap: 12px;
                text-align: center;
            }
            .dash-cta-card > div:last-child {
                min-width: 100% !important;
                display: flex;
                justify-content: center;
            }
        }


        /* Cards */
        .b2b-card {
            background: var(--b2b-surface);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 2px 4px -1px rgba(0, 0, 0, 0.03);
            border: 1px solid var(--b2b-border);
        }

        .b2b-card__title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--b2b-text);
            margin: 0 0 20px 0;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        /* Data Grid */
        .b2b-data-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin: 0;
            padding: 0;
        }

        .b2b-data-row {
            display: flex;
            align-items: flex-start;
            gap: 16px;
        }

        .b2b-data-label {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--b2b-text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            text-transform: uppercase;
            width: 180px;
            flex-shrink: 0;
        }

        .b2b-data-row {
            display: flex;
            align-items: center;
            gap: 16px;
            padding-bottom: 12px;
            border-bottom: 1px dashed #e2e8f0;
        }

        .b2b-data-row:last-child {
            border-bottom: none;
            padding-bottom: 0;
        }

        .b2b-data-label svg {
            color: #9ca3af;
        }

        .b2b-data-value {
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--b2b-text);
            margin: 0;
            line-height: 1.5;
        }

        /* Status Badge */
        .b2b-status {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 500;
            font-size: 0.95rem;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .b2b-status--active {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }

        .b2b-status--inactive {
            background: #f1f5f9;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        /* Full Width Promo */
        .b2b-promo-banner {
            background: var(--b2b-primary-light);
            border: 1px solid #bfdbfe;
            border-radius: 16px;
            padding: 32px 24px;
            text-align: center;
            grid-column: 1 / -1;
            margin-bottom: 24px;
        }

        /* Dark Code Card */
        .b2b-code-card {
            background: #0f172a;
            border-radius: 12px;
            padding: 20px;
            color: #e2e8f0;
            font-family: monospace;
            font-size: 0.85rem;
            position: relative;
            overflow: hidden;
        }

        .b2b-code-card::before {
            content: "•••";
            position: absolute;
            top: 12px;
            left: 16px;
            color: #475569;
            font-size: 1.2rem;
            letter-spacing: 2px;
        }

        /* Map */
        .premium-map-container {
            border: 1px solid var(--b2b-border);
            border-radius: 12px;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 300px;
        }

        .premium-map-header {
            background: #f8fafc;
            padding: 12px 16px;
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--b2b-text);

            display: flex;
            align-items: center;
            gap: 8px;
        }

        #company-map {
            flex: 1;
            min-height: 250px;
            width: 100%;
            background: #e2e8f0;
        }

        .cif-text:hover .copy-icon {
            opacity: 1 !important;
            color: var(--b2b-primary) !important;
        }

        main {
            padding-top: 0 !important;
        }

        .bg-halo {
            display: none;
        }
    </style>
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div>

    <?= view('partials/header') ?>

    <main style="padding:40px 0 70px;">
        <section class="container" style="max-width: 1200px;">
            <!-- Breadcrumbs HTML -->
            <nav aria-label="Breadcrumb" class="breadcrumb"
                style="margin-bottom: 1rem; font-size: 0.9rem; color: #666;">
                <a href="<?= site_url() ?>" style="color: inherit; text-decoration: none;">Inicio</a>
                <span style="margin: 0 0.5rem;">/</span>

                <?php if (!empty($provinceUrl)): ?>
                    <a href="<?= site_url('listado-de-empresas') ?>" style="color: inherit; text-decoration: none;">Directorio</a>
                    <span style="margin: 0 0.5rem;">/</span>
                    <a href="<?= esc($provinceUrl) ?>"
                        style="color: inherit; text-decoration: none;"><?= esc($company['province'] ?? $company['provincia']) ?></a>
                <?php else: ?>
                    <a href="<?= site_url('search_company') ?>" style="color: inherit; text-decoration: none;">Buscador</a>
                <?php endif; ?>

                <span style="margin: 0 0.5rem;">/</span>
                <span aria-current="page"><?= esc($company['name'] ?? 'Empresa') ?></span>
            </nav>

            <div>


                <?php
                $statusRaw = (string) ($company['status'] ?? '');
                $isActive = strtoupper($statusRaw) === 'ACTIVA';
                $statusClass = $isActive ? 'company-status company-status--active' : 'company-status company-status--inactive';

                $cnaeFull = (!empty($company['cnae']) && !empty($company['cnae_label']))
                    ? ($company['cnae'] . ' · ' . $company['cnae_label'])
                    : ($company['cnae_label'] ?? ($company['cnae'] ?? '-'));

                $jsonForCode = ['success' => true, 'data' => $company];
                $jsonPretty = json_encode($jsonForCode, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                // --- AUTO-GENERATED FAQS ---
                $companyName = $company['name'] ?? 'Esta empresa';
                $companyCif = $company['cif'] ?? $company['nif'] ?? 'Desconocido';
                $companyProv = $company['province'] ?? $company['provincia'] ?? 'España';

                // Dirección inteligente: si hay domicilio, úsalo. Si no, provincia.
                $rawAddr = $company['address'] ?? $company['address'] ?? '';
                $companyAddr = $rawAddr ? "{$rawAddr}, {$companyProv}" : "{$companyProv}, España";

                $companyAct = $company['cnae_label'] ?? 'su actividad registrada';

                // Phone logic
                $phone = $company['phone'] ?? $company['phone_mobile'] ?? null;
                $phoneHtml = $phone ? "**{$phone}**" : "el teléfono de {$companyName} en nuestro informe";

                $adminNames = [];
                if (!empty($administrators)) {
                    foreach (array_slice($administrators, 0, 3) as $adm) {
                        $adminNames[] = $adm['name'];
                    }
                }
                $adminResponse = !empty($adminNames)
                    ? "Entre los administradores y cargos actuales de **{$companyName}** se encuentran: **" . implode(', ', $adminNames) . "**. Puede consultar el listado completo y sus funciones en la sección de Cargos Directivos de esta misma ficha."
                    : "Para conocer a los administradores y cargos de la empresa, consulte la sección específica de **Cargos Directivos** en este perfil.";

                $faqs = [
                    [
                        'q' => "¿Es fiable {$companyName}?",
                        'a' => "Sí, **{$companyName}** es una sociedad registrada en España con CIF **{$companyCif}**. Su estado actual es **{$statusRaw}**, según consta en el Registro Mercantil. Puede consultar sus cuentas anuales, informes y actos del BORME para verificar su solvencia."
                    ],
                    [
                        'q' => "¿Cuál es el teléfono y dirección de {$companyName}?",
                        'a' => "La empresa tiene su domicilio social en **{$companyAddr}**. Para contactar, puede llamar al {$phoneHtml} o visitar su delegación más cercana en {$companyProv}."
                    ],
                    [
                        'q' => "¿Quiénes son los administradores de {$companyName}?",
                        'a' => "{$adminResponse} Adicionalmente, en la sección de **Actos del BORME** puede revisar el histórico oficial de nombramientos, ceses y dimisiones desde su constitución."
                    ]
                ];

                // Sobrescribir con FAQs de IA si existen
                if (!empty($company['ai_faqs'])) {
                    $aiFaqsDecoded = json_decode($company['ai_faqs'], true);
                    if (json_last_error() === JSON_ERROR_NONE && !empty($aiFaqsDecoded) && is_array($aiFaqsDecoded)) {
                        $faqs = $aiFaqsDecoded;
                    }
                }

                // Schema.org Data
                $organizationSchema = [
                    "@type" => "Organization",
                    "@id" => $canonical . "#organization",
                    "name" => $companyName,
                    "taxID" => $companyCif,
                    "url" => $canonical,
                    "address" => [
                        "@type" => "PostalAddress",
                        "streetAddress" => $rawAddr ?: null,
                        "addressRegion" => $companyProv,
                        "addressCountry" => "ES"
                    ],
                    "foundingDate" => $company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? '',
                    "description" => $meta_description ?? '',
                    "logo" => site_url('logo.png')
                ];

                if (!empty($administrators)) {
                    $organizationSchema['employee'] = [];
                    foreach (array_slice($administrators, 0, 10) as $adm) {
                        $organizationSchema['employee'][] = [
                            "@type" => "Person",
                            "name" => $adm['name'],
                            "jobTitle" => $adm['position']
                        ];
                    }
                }

                if (!empty($ratingCount) && $ratingCount > 0) {
                    $organizationSchema['aggregateRating'] = [
                        "@type" => "AggregateRating",
                        "ratingValue" => round($ratingAvg, 1),
                        "reviewCount" => $ratingCount,
                        "bestRating" => 5,
                        "worstRating" => 1
                    ];
                }

                $schemaOrg = [
                    "@context" => "https://schema.org",
                    "@graph" => [
                        $organizationSchema,
                        (!empty($company['lat']) && !empty($company['lng'])) ? [
                            "@type" => "LocalBusiness",
                            "@id" => $canonical . "#localbusiness",
                            "name" => $companyName,
                            "address" => [
                                "@type" => "PostalAddress",
                                "streetAddress" => $rawAddr ?: null,
                                "addressRegion" => $companyProv,
                                "addressCountry" => "ES"
                            ],
                            "geo" => [
                                "@type" => "GeoCoordinates",
                                "latitude" => $company['lng'], // Coords están invertidas en DB
                                "longitude" => $company['lat']
                            ],
                            "url" => $canonical
                        ] : null,
                        [
                            "@type" => "BreadcrumbList",
                            "itemListElement" => [
                                [
                                    "@type" => "ListItem",
                                    "position" => 1,
                                    "name" => "Inicio",
                                    "item" => site_url()
                                ],
                                    // Logic for intermediate crumb
                                (!empty($provinceUrl) ?
                                    [
                                        "@type" => "ListItem",
                                        "position" => 2,
                                        "name" => $company['province'] ?? $company['provincia'],
                                        "item" => $provinceUrl
                                    ] :
                                    [
                                        "@type" => "ListItem",
                                        "position" => 2,
                                        "name" => "Buscador",
                                        "item" => site_url('search_company')
                                    ]),
                                [
                                    "@type" => "ListItem",
                                    "position" => 3,
                                    "name" => $companyName,
                                    "item" => $canonical
                                ]
                            ]
                        ],
                        [
                            "@type" => "FAQPage",
                            "mainEntity" => array_map(function ($item) {
                                return [
                                    "@type" => "Question",
                                    "name" => $item['q'],
                                    "acceptedAnswer" => [
                                        "@type" => "Answer",
                                        "text" => $item['a'] // Google permite HTML básico aquí
                                    ]
                                ];
                            }, $faqs)
                        ]
                    ]
                ];

                if (isset($ratingCount) && $ratingCount > 0) {
                    foreach ($schemaOrg['@graph'] as &$node) {
                        if ($node && in_array($node['@type'], ['Organization', 'LocalBusiness'])) {
                            $node['aggregateRating'] = [
                                "@type" => "AggregateRating",
                                "ratingValue" => round($ratingAvg, 1),
                                "ratingCount" => $ratingCount,
                                "bestRating" => "5",
                                "worstRating" => "1"
                            ];
                        }
                    }
                    unset($node);
                }
                ?>
                <div style="max-width: 1200px; margin: 0 auto; padding: 0px;">
                    <!-- HERO SECTION -->
                    <div class="b2b-header-wrapper"
                        style="padding: 0; margin-bottom: 24px;">
                        <div class="b2b-hero"
                            style="position: relative; overflow: hidden; display: flex; align-items: center; gap: 32px; background: linear-gradient(135deg, #ffffff 0%, #f4f7fb 100%); padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.03); border: 1px solid rgba(226, 232, 240, 0.8);">
                            
                            <?php
                            $constValHeader = trim($company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? '');
                            $ribbonText = '';
                            $ribbonGradient = '';
                            $ribbonShadow = '';
                            
                            if (!empty($constValHeader) && $timestamp = strtotime($constValHeader)) {
                                $ageInDays = (time() - $timestamp) / (60 * 60 * 24);
                                $ageInYears = $ageInDays / 365.25;
                                
                                if ($ageInDays <= 90) {
                                    $ribbonText = 'Empresa Reciente';
                                    $ribbonGradient = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                                    $ribbonShadow = 'rgba(16, 185, 129, 0.4)';
                                } elseif ($ageInYears <= 1) {
                                    $ribbonText = 'Empresa Nueva';
                                    $ribbonGradient = 'linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%)';
                                    $ribbonShadow = 'rgba(14, 165, 233, 0.4)';
                                } elseif ($ageInYears <= 5) {
                                    $ribbonText = 'Empresa Joven';
                                    $ribbonGradient = 'linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%)';
                                    $ribbonShadow = 'rgba(139, 92, 246, 0.4)';
                                } elseif ($ageInYears <= 10) {
                                    $ribbonText = 'Consolidada';
                                    $ribbonGradient = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
                                    $ribbonShadow = 'rgba(245, 158, 11, 0.4)';
                                } else {
                                    $ribbonText = 'Veterana (+10a)';
                                    $ribbonGradient = 'linear-gradient(135deg, #3b82f6 0%, #1e40af 100%)';
                                    $ribbonShadow = 'rgba(59, 130, 246, 0.4)';
                                }
                            }
                            ?>
                            <?php if ($ribbonText): ?>
                            <div style="position: absolute; top: 32px; right: -75px; width: 250px; text-align: center; background: <?= $ribbonGradient ?>; color: #fff; padding: 6px 0; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; transform: rotate(45deg); box-shadow: 0 4px 12px <?= $ribbonShadow ?>; letter-spacing: 0.5px; z-index: 10;">
                                <?= esc($ribbonText) ?>
                            </div>
                            <?php endif; ?>
                            
                            <!-- Premium Avatar -->
                            <div class="b2b-hero__avatar" style="flex-shrink: 0; width: 100px; height: 100px; border-radius: 24px; background: linear-gradient(135deg, #1e40af 0%, #3b82f6 100%); display: flex; align-items: center; justify-content: center; color: white; box-shadow: 0 12px 24px -8px rgba(59, 130, 246, 0.5); position: relative; overflow: hidden;">
                                <!-- Soft glow overlay inside -->
                                <div style="position: absolute; top: -50%; left: -50%; width: 200%; height: 200%; background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 60%); transform: rotate(30deg); pointer-events: none;"></div>
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                    stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="position: relative; z-index: 1;">
                                    <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                                    <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                                    <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                                    <path d="M10 6h4"></path>
                                    <path d="M10 10h4"></path>
                                    <path d="M10 14h4"></path>
                                    <path d="M10 18h4"></path>
                                </svg>
                            </div>

                            <div class="b2b-hero__content" style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
                                    <div style="display: inline-flex; align-items: center; gap: 6px; background: #eff6ff; color: #3b82f6; padding: 4px 12px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; border: 1px solid #bfdbfe; letter-spacing: 0.5px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                            <polyline points="14 2 14 8 20 8"></polyline>
                                            <line x1="16" y1="13" x2="8" y2="13"></line>
                                            <line x1="16" y1="17" x2="8" y2="17"></line>
                                            <polyline points="10 9 9 9 8 9"></polyline>
                                        </svg>
                                        FICHA DE EMPRESA
                                    </div>

                                    <div style="display: inline-flex; align-items: center; gap: 4px; background: #ecfdf5; color: #059669; padding: 4px 10px; border-radius: 999px; font-size: 0.7rem; font-weight: 700; border: 1px solid #a7f3d0; letter-spacing: 0.5px; text-transform: uppercase;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                            <path d="M9 12l2 2 4-4"></path>
                                        </svg>
                                        Datos oficiales Reg. Mercantil
                                    </div>
                                    
                                    <?php if (!empty($contracts)): ?>
                                    <div style="display: inline-flex; align-items: center; gap: 4px; background: #eef2ff; color: #4f46e5; padding: 4px 10px; border-radius: 999px; font-size: 0.7rem; font-weight: 700; border: 1px solid #c7d2fe; letter-spacing: 0.5px; text-transform: uppercase;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/>
                                        </svg>
                                        Contratista del Estado
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($subsidies)): ?>
                                    <div style="display: inline-flex; align-items: center; gap: 4px; background: #fefce8; color: #ca8a04; padding: 4px 10px; border-radius: 999px; font-size: 0.7rem; font-weight: 700; border: 1px solid #fef08a; letter-spacing: 0.5px; text-transform: uppercase;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 100 4h4a2 2 0 110 4H8"/><path d="M12 18V6"/>
                                        </svg>
                                        Empresa Subvencionada
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <h1 style="font-size: 1.6rem; font-weight: 700; color: #0f172a; margin: 0 0 16px 0; line-height: 1.25; letter-spacing: -0.01em; text-wrap: balance;">
                                    <?= esc($company['name'] ?? '-') ?><?php if (!empty($companyCif) && $companyCif !== 'Desconocido' && $companyCif !== '-'): ?> - CIF <?= esc($companyCif) ?><?php endif; ?>
                                </h1>

                                <?php if (!empty($company['ai_pitch'])): ?>
                                <p style="font-size: 1.05rem; color: #475569; margin: 0 0 16px 0; line-height: 1.4; text-wrap: balance; font-weight: 500;">
                                    <?= esc($company['ai_pitch']) ?>
                                </p>
                                <?php endif; ?>

                                <?php 
                                $aiTags = [];
                                if (!empty($company['ai_tags'])) {
                                    $aiTagsDecoded = json_decode($company['ai_tags'], true);
                                    if (json_last_error() === JSON_ERROR_NONE && is_array($aiTagsDecoded)) {
                                        $aiTags = $aiTagsDecoded;
                                    }
                                }
                                ?>
                                <?php if (!empty($aiTags)): ?>
                                <div style="display: flex; flex-wrap: wrap; gap: 12px; margin-bottom: 20px;">
                                    <?php foreach ($aiTags as $tag): 
                                        $tagSlug = url_title($tag, '-', true);
                                    ?>
                                    <a href="<?= site_url('listado-de-empresas/etiqueta/' . esc($tagSlug)) ?>" style="color: #64748b; font-size: 0.85rem; font-weight: 600; display: inline-flex; align-items: center; gap: 4px; text-decoration: none; transition: all 0.2s ease;" onmouseover="this.style.color='#2563eb';" onmouseout="this.style.color='#64748b';">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.7;">
                                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"></path>
                                            <line x1="7" y1="7" x2="7.01" y2="7"></line>
                                        </svg>
                                        <?= esc($tag) ?>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>

                                <div class="b2b-hero__meta" style="display: flex; flex-wrap: wrap; align-items: center; gap: 16px; color: #475569; font-size: 0.95rem; font-weight: 500;">
                                    <?php if (!empty($companyCif) && $companyCif !== 'Desconocido' && $companyCif !== '-'): ?>
                                    <div style="display: flex; align-items: center; gap: 6px; background: #f1f5f9; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                        </svg>
                                        <span style="color: #0f172a; font-weight: 700;">CIF</span>
                                        <span><?= esc($companyCif) ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <?php $provinceVal = trim($company['province'] ?? $company['provincia'] ?? ''); ?>
                                    <?php if (!empty($provinceVal) && $provinceVal !== '-'): ?>
                                    <div style="display: flex; align-items: center; gap: 6px; background: #f1f5f9; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        <span><?= esc($provinceVal) ?></span>
                                    </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($statusRaw)): ?>
                                    <div class="<?= str_replace('company-status', 'b2b-status', esc($statusClass)) ?>" style="margin: 0; display: flex; align-items: center; gap: 6px;">
                                        <?php if ($isActive): ?>
                                            <span style="position: relative; display: flex; width: 8px; height: 8px;">
                                                <span class="status-dot-ping" style="position: absolute; display: inline-flex; height: 100%; width: 100%; border-radius: 50%; background-color: #4ade80;"></span>
                                                <span style="position: relative; display: inline-flex; border-radius: 50%; height: 8px; width: 8px; background-color: #22c55e;"></span>
                                            </span>
                                        <?php endif; ?>
                                        <span><?= esc($statusRaw) ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($company['updated_at'])): ?>
                                    <div style="display: flex; align-items: center; gap: 6px; background: #f1f5f9; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.85rem;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        <span>Última actualización: <?= date('d/m/Y', strtotime($company['updated_at'])) ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <div style="margin-left: auto; display: flex; gap: 12px; align-items: center;">
                                        <div style="display: flex; gap: 6px;">
                                            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode(current_url()) ?>&title=<?= urlencode('Ficha de empresa: ' . $companyName) ?>" target="_blank" rel="noopener noreferrer" class="btn-share-icon" title="Compartir en LinkedIn">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>
                                            </a>
                                            <a href="https://api.whatsapp.com/send?text=<?= urlencode('Mira esta empresa: ' . $companyName . ' - ' . current_url()) ?>" target="_blank" rel="noopener noreferrer" class="btn-share-icon" title="Compartir por WhatsApp">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                            </a>
                                            <button onclick="navigator.clipboard.writeText('<?= current_url() ?>'); alert('Enlace copiado al portapapeles');" class="btn-share-icon" title="Copiar enlace" style="cursor: pointer;">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                            </button>
                                        </div>
                                        <button type="button" onclick="document.getElementById('crm-modal').style.display='flex';"
                                            style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: #0f172a; color: #ffffff; font-size: 0.9rem; font-weight: 700; text-decoration: none; border-radius: 10px; border: 1px solid #0f172a; transition: all 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.1); cursor: pointer;"
                                            onmouseover="this.style.background='#1e293b'; this.style.transform='translateY(-2px)';"
                                            onmouseout="this.style.background='#0f172a'; this.style.transform='translateY(0)';">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                            </svg>
                                            Enviar a CRM
                                        </button>
                                        <a href="<?= site_url('empresa/export/' . $company['id']) ?>"
                                            rel="nofollow"
                                            aria-label="Descargar Informe PDF de <?= esc($companyName) ?>"
                                            onclick="window.dataLayer = window.dataLayer || []; window.dataLayer.push({'event': 'cta_pdf_click'});"
                                            style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: #ffffff; color: #2563eb; font-size: 0.9rem; font-weight: 700; text-decoration: none; border-radius: 10px; border: 1px solid #cbd5e1; transition: all 0.2s; box-shadow: 0 2px 4px rgba(0,0,0,0.02);"
                                            onmouseover="this.style.background='#eff6ff'; this.style.borderColor='#93c5fd'; this.style.transform='translateY(-2px)';"
                                            onmouseout="this.style.background='#ffffff'; this.style.borderColor='#cbd5e1'; this.style.transform='translateY(0)';">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                <polyline points="7 10 12 15 17 10"></polyline>
                                                <line x1="12" y1="15" x2="12" y2="3"></line>
                                            </svg>
                                            Descargar informe
                                        </a>
                                    </div>
                                </div>

                                <div class="b2b-hero__actions">
                                    <?php if (getenv('ENABLE_COMPANY_ALERTS') === 'true'): ?>
                                        <a href="<?= site_url('alerts/confirm/' . ($company['cif'] ?? $company['nif'] ?? '-')) ?>"
                                            class="b2b-btn b2b-btn--outline-danger">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path
                                                    d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z">
                                                </path>
                                            </svg>
                                            VER RANKING
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div> <!-- /b2b-header-wrapper -->


                        <?php
                        // --- TOC START ---
                        ?>
                        <nav class="b2b-tabs" aria-label="Índice de contenidos"
                            style="border: none; box-shadow: none; background: transparent; padding-left: 0; padding-right: 0;">
                            <ul>
                                <li><a href="#datos-generales" class="active">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                            <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                        </svg>
                                        Datos Generales
                                    </a></li>
                                <?php if ((!empty($company['lat']) && !empty($company['lng'])) || !empty($company['address'])): ?>
                                    <li><a href="#map-area">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                <circle cx="12" cy="10" r="3"></circle>
                                            </svg>
                                            Ubicación
                                        </a></li>
                                <?php endif; ?>
                                <?php if (!empty($contracts) || !empty($subsidies)): ?>
                                    <li><a href="#financial-data">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                            </svg>
                                            Finanzas Públicas
                                        </a></li>
                                <?php endif; ?>
                                <li><a href="#preguntas-frecuentes">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                        </svg>
                                        FAQs
                                    </a></li>
                                <?php if (!empty($related)): ?>
                                    <li><a href="#empresas-relacionadas">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                                                <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                                                <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                                                <path d="M10 6h4"></path>
                                                <path d="M10 10h4"></path>
                                                <path d="M10 14h4"></path>
                                                <path d="M10 18h4"></path>
                                            </svg>
                                            Empresas relacionadas
                                        </a></li>
                                <?php endif; ?>
                                <?php if (!session('logged_in')): ?>
                                    <li><a href="#descargar-excel">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                <polyline points="7 10 12 15 17 10"></polyline>
                                                <line x1="12" y1="15" x2="12" y2="3"></line>
                                            </svg>
                                            Descargar CSV
                                        </a></li>
                                <?php endif; ?>
                                <li><a href="#api-dev-section">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="16 18 22 12 16 6"></polyline>
                                            <polyline points="8 6 2 12 8 18"></polyline>
                                        </svg>
                                        Desarrolladores
                                    </a></li>
                            </ul>
                        </nav>
                        <?php
                        // --- TOC END ---
                        ?>

                    <div class="b2b-grid-2col">
                        <section id="datos-generales" class="b2b-card" style="height: 100%;">
                            <dl class="b2b-data-list">
                                <?php if (!empty($companyCif) && $companyCif !== 'Desconocido' && $companyCif !== '-'): ?>
                                <div class="b2b-data-row">
                                    <dt class="b2b-data-label">
                                        <div>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                            </svg>
                                        </div>
                                        CIF
                                    </dt>
                                    <dd class="b2b-data-value" style="display: flex; align-items: center; gap: 12px; flex-wrap: wrap;">
                                        <span class="cif-text" id="cif-val"
                                            style="cursor: pointer; position: relative; display: inline-flex; align-items: center; gap: 6px;"
                                            title="Clic para copiar">
                                            <?= esc($company['cif'] ?? $company['nif'] ?? '-') ?>
                                            <svg class="copy-icon" width="14" height="14" viewBox="0 0 24 24"
                                                fill="none" stroke="currentColor" stroke-width="2.5"
                                                stroke-linecap="round" stroke-linejoin="round"
                                                style="color: #64748b; opacity: 0.7; transition: opacity 0.2s;">
                                                <rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect>
                                                <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1">
                                                </path>
                                            </svg>
                                        </span>
                                        <a href="#api-dev-section" 
                                           style="display: inline-block; padding: 2px 8px; background: #eff6ff; color: #2563eb; font-size: 0.75rem; font-weight: 700; border-radius: 6px; text-decoration: none; border: 1px solid #dbeafe; transition: all 0.2s;"
                                           onmouseover="this.style.background='#dbeafe'; this.style.borderColor='#bfdbfe';"
                                           onmouseout="this.style.background='#eff6ff'; this.style.borderColor='#dbeafe';">
                                            (Consultar vía API)
                                        </a>
                                    </dd>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($company['website_official'])): ?>
                                <div class="b2b-data-row">
                                    <dt class="b2b-data-label">
                                        <div>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <circle cx="12" cy="12" r="10"></circle>
                                                <line x1="2" y1="12" x2="22" y2="12"></line>
                                                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                            </svg>
                                        </div>
                                        Página Web
                                    </dt>
                                    <dd class="b2b-data-value">
                                        <?php 
                                            $hrefUrl = trim($company['website_official']);
                                            if (!preg_match("~^(?:f|ht)tps?://~i", $hrefUrl)) {
                                                $hrefUrl = "https://" . ltrim($hrefUrl, '/');
                                            }
                                        ?>
                                        <a href="<?= esc($hrefUrl) ?>" target="_blank" rel="noopener nofollow" style="color: #2563eb; text-decoration: none; font-weight: 600;">
                                            <?= esc(str_replace(['http://', 'https://', 'www.'], '', $company['website_official'])) ?>
                                        </a>
                                    </dd>
                                </div>
                                <?php endif; ?>

                                <?php $phoneVal = trim($company['phone'] ?? ''); ?>
                                <?php if (!empty($phoneVal) && $phoneVal !== '-'): ?>
                                <div class="b2b-data-row">
                                    <dt class="b2b-data-label">
                                        <div>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"></path>
                                            </svg>
                                        </div>
                                        Teléfono
                                    </dt>
                                    <dd class="b2b-data-value" style="display: flex; flex-wrap: wrap; gap: 12px;">
                                        <?php 
                                        $cleanPhones = str_replace([',', ';', '-', '/'], ' ', $phoneVal);
                                        $phonesList = array_unique(array_filter(explode(' ', $cleanPhones)));
                                        foreach ($phonesList as $p): ?>
                                            <a href="tel:<?= esc($p) ?>" style="color: #0f172a; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center;">
                                                <?= esc($p) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </dd>
                                </div>
                                <?php endif; ?>

                                <?php $mobileVal = trim($company['phone_mobile'] ?? ''); ?>
                                <?php if (!empty($mobileVal) && $mobileVal !== '-'): ?>
                                <div class="b2b-data-row">
                                    <dt class="b2b-data-label">
                                        <div>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <rect x="5" y="2" width="14" height="20" rx="2" ry="2"></rect>
                                                <line x1="12" y1="18" x2="12.01" y2="18"></line>
                                            </svg>
                                        </div>
                                        Teléfono Móvil
                                    </dt>
                                    <dd class="b2b-data-value" style="display: flex; flex-wrap: wrap; gap: 12px;">
                                        <?php 
                                        $cleanMobiles = str_replace([',', ';', '-', '/'], ' ', $mobileVal);
                                        $mobilesList = array_unique(array_filter(explode(' ', $cleanMobiles)));
                                        foreach ($mobilesList as $m): ?>
                                            <a href="tel:<?= esc($m) ?>" style="color: #0f172a; text-decoration: none; font-weight: 600; display: inline-flex; align-items: center;">
                                                <?= esc($m) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    </dd>
                                </div>
                                <?php endif; ?>

                                <?php $cnaeVal = trim($cnaeFull ?? ''); ?>
                                <?php if (!empty($cnaeVal) && $cnaeVal !== '-'): ?>
                                <div class="b2b-data-row">
                                    <dt class="b2b-data-label">
                                        <div>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                                            </svg>
                                        </div>
                                        CNAE (2009)
                                    </dt>
                                    <dd class="b2b-data-value">
                                        <?= esc($cnaeFull ?: '-') ?>
                                    </dd>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($company['cnae_2025'])): ?>
                                    <div class="b2b-data-row">
                                        <dt class="b2b-data-label">
                                            <div>
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path d="M22 12h-4l-3 9L9 3l-3 9H2"></path>
                                                </svg>
                                            </div>
                                            CNAE (2025)
                                        </dt>
                                        <dd class="b2b-data-value">
                                            <?= esc($company['cnae_2025'] . ' · ' . $company['cnae_2025_label']) ?>
                                        </dd>
                                    </div>
                                <?php endif; ?>

                                <?php $provVal = trim($company['province'] ?? $company['provincia'] ?? ''); ?>
                                <?php if (!empty($provVal) && $provVal !== '-'): ?>
                                <div class="b2b-data-row">
                                    <dt class="b2b-data-label">
                                        <div>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                <circle cx="12" cy="10" r="3"></circle>
                                            </svg>
                                        </div>
                                        Provincia
                                    </dt>
                                    <dd class="b2b-data-value">
                                        <?= esc($provVal) ?>
                                    </dd>
                                </div>
                                <?php endif; ?>

                                <?php if (!empty($company['address'])): ?>
                                    <div class="b2b-data-row">
                                        <dt class="b2b-data-label">
                                            <div>
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                    stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                    stroke-linejoin="round">
                                                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                                </svg>
                                            </div>
                                            Dirección
                                        </dt>
                                        <dd class="b2b-data-value">
                                            <?= esc($company['address']) ?>
                                        </dd>
                                    </div>
                                <?php endif; ?>

                                <?php $constVal = trim($company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? ''); ?>
                                <?php if (!empty($constVal) && $constVal !== '-'): ?>
                                <div class="b2b-data-row">
                                    <dt class="b2b-data-label">
                                        <div>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                                <line x1="3" y1="10" x2="21" y2="10"></line>
                                            </svg>
                                        </div>
                                        Constitución
                                    </dt>
                                    <dd class="b2b-data-value"><time datetime="<?= esc($constVal) ?>"><?= date('d/m/Y', strtotime($constVal)) ?></time></dd>
                                </div>
                                <?php endif; ?>
                                <?php $objVal = trim($company['corporate_purpose'] ?? $company['objeto_social'] ?? ''); ?>
                                <?php if (!empty($objVal) && $objVal !== '-'): ?>
                                <div class="b2b-data-row">
                                    <dt class="b2b-data-label">
                                        <div>
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                                                stroke-linejoin="round">
                                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                                <polyline points="14 2 14 8 20 8"></polyline>
                                                <line x1="16" y1="13" x2="8" y2="13"></line>
                                                <line x1="16" y1="17" x2="8" y2="17"></line>
                                                <polyline points="10 9 9 9 8 9"></polyline>
                                            </svg>
                                        </div>
                                        Objeto social
                                    </dt>
                                    <dd class="b2b-data-value">
                                        <?= esc($objVal) ?>
                                    </dd>
                                </div>
                                <?php endif; ?>
                            </dl>
                        </section>

                        <?php if ((!empty($company['lat']) && !empty($company['lng'])) || !empty($company['address'])): ?>
                            <div id="map-area" class="premium-map-container b2b-card"
                                style="padding:0; overflow: hidden; height: 100%;">
                                <div class="premium-map-header">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5">
                                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                        <circle cx="12" cy="10" r="3"></circle>
                                    </svg>
                                    Ubicación
                                </div>
                                <div id="company-map"></div>
                            </div>
                        <?php endif; ?>
                    </div> <!-- /b2b-grid-2col -->

                    <div class="b2b-grid-aside">
                        <div style="display:flex; flex-direction:column; gap:12px; order: 2;">
                            <!-- WIDGET DE VALORACIÓN SEO -->
                            <div id="company-rating-widget"
                                style="margin: 0; background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%); border-radius: 16px; padding: 1.25rem 1rem; text-align: center; border: 1px solid rgba(255, 255, 255, 0.8);">
                                <h3 style="font-size: 1.1rem; font-weight: 800; color: #0f172a; margin-bottom: 0.25rem;">
                                    ¿Te ha sido útil esta información?</h3>
                                <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 0.75rem;">Valora la ficha de
                                    <?= esc($companyName) ?>
                                </p>

                                <div class="rating-stars"
                                    style="display: flex; justify-content: center; gap: 6px; margin-bottom: 0.75rem; cursor: pointer;">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <svg class="star-icon" data-value="<?= $i ?>" width="28" height="28"
                                            viewBox="0 0 24 24"
                                            fill="<?= ($i <= round($ratingAvg ?? 0)) ? '#fbbf24' : 'none' ?>"
                                            stroke="<?= ($i <= round($ratingAvg ?? 0)) ? '#fbbf24' : '#cbd5e1' ?>"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                            style="transition: all 0.2s;">
                                            <polygon
                                                points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2">
                                            </polygon>
                                        </svg>
                                    <?php endfor; ?>
                                </div>

                                <div id="rating-stats-display"
                                    style="font-size: 0.9rem; color: #475569; font-weight: 600;">
                                    <?php if (isset($ratingCount) && $ratingCount > 0): ?>
                                        Puntuación media: <span id="avg-rating-val"
                                            style="color: #0f172a;"><?= number_format($ratingAvg, 1) ?></span>/5 (<span
                                            id="count-rating-val"><?= $ratingCount ?></span> votos)
                                    <?php else: ?>
                                        Sé el primero en valorar esta empresa.
                                    <?php endif; ?>
                                </div>
                                <div id="rating-message"
                                    style="margin-top: 10px; font-size: 0.9rem; font-weight: 600; display: none;"></div>

                                <div id="feedback-block" style="display: none; margin-top: 15px; text-align: left; padding-top: 15px; border-top: 1px solid #cbd5e1;">
                                    <p id="feedback-prompt" style="font-size: 0.9rem; color: #475569; margin-bottom: 8px; font-weight: 600;"></p>
                                    <textarea id="feedback-text" rows="3" style="width: 100%; padding: 10px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 0.9rem; resize: vertical; box-sizing: border-box;" placeholder="Escribe aquí tus comentarios..."></textarea>
                                    <button id="submit-feedback-btn" style="margin-top: 10px; width: 100%; background: #3b82f6; color: white; border: none; padding: 10px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#2563eb'" onmouseout="this.style.background='#3b82f6'">Enviar sugerencia</button>
                                    <div id="feedback-message" style="margin-top: 10px; font-size: 0.85rem; font-weight: 600; display: none; text-align: center;"></div>
                                </div>
                            </div>

                            <script>
                                document.addEventListener('DOMContentLoaded', function () {
                                    const stars = document.querySelectorAll('.star-icon');
                                    const widget = document.getElementById('company-rating-widget');
                                    const msgDiv = document.getElementById('rating-message');
                                    const avgSpan = document.getElementById('avg-rating-val');
                                    const countSpan = document.getElementById('count-rating-val');
                                    let hasVoted = false;

                                    // Hover effects
                                    stars.forEach(star => {
                                        star.addEventListener('mouseenter', function () {
                                            if (hasVoted) return;
                                            const val = this.getAttribute('data-value');
                                            stars.forEach(s => {
                                                if (s.getAttribute('data-value') <= val) {
                                                    s.style.fill = '#fcd34d'; // hover color
                                                    s.style.stroke = '#fcd34d';
                                                } else {
                                                    s.style.fill = 'none';
                                                    s.style.stroke = '#cbd5e1';
                                                }
                                            });
                                        });

                                        star.addEventListener('mouseleave', function () {
                                            if (hasVoted) return;
                                            // Reset to initial state based on PHP data is hard without keeping it in JS, 
                                            // so we just clear hover effect unless we already voted
                                            const currentAvg = <?= round($ratingAvg ?? 0) ?>;
                                            stars.forEach(s => {
                                                if (s.getAttribute('data-value') <= currentAvg) {
                                                    s.style.fill = '#fbbf24';
                                                    s.style.stroke = '#fbbf24';
                                                } else {
                                                    s.style.fill = 'none';
                                                    s.style.stroke = '#cbd5e1';
                                                }
                                            });
                                        });

                                        star.addEventListener('click', function () {
                                            if (hasVoted) return;
                                            const val = this.getAttribute('data-value');
                                            const companyId = <?= (int) ($company['id'] ?? 0) ?>;

                                            // Lock UI
                                            hasVoted = true;
                                            stars.forEach(s => s.style.cursor = 'default');

                                            // Send AJAX
                                            fetch('<?= site_url('company/rate') ?>', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/x-www-form-urlencoded',
                                                    'X-Requested-With': 'XMLHttpRequest'
                                                },
                                                body: new URLSearchParams({
                                                    'company_id': companyId,
                                                    'rating': val,
                                                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                                                })
                                            })
                                                .then(response => response.json())
                                                .then(data => {
                                                    msgDiv.style.display = 'block';
                                                    if (data.status === 'success') {
                                                        msgDiv.style.color = '#16a34a';
                                                        msgDiv.innerText = data.message;

                                                        // Update stars visually to the given vote
                                                        stars.forEach(s => {
                                                            if (s.getAttribute('data-value') <= val) {
                                                                s.style.fill = '#fbbf24';
                                                                s.style.stroke = '#fbbf24';
                                                            } else {
                                                                s.style.fill = 'none';
                                                                s.style.stroke = '#cbd5e1';
                                                            }
                                                        });

                                                        // Update text
                                                        let statsDisplay = document.getElementById('rating-stats-display');
                                                        statsDisplay.innerHTML = `Puntuación media: <span id="avg-rating-val" style="color: #0f172a;">${data.new_avg}</span>/5 (<span id="count-rating-val">${data.new_count}</span> votos)`;

                                                        if (val < 5) {
                                                            const feedbackBlock = document.getElementById('feedback-block');
                                                            const feedbackPrompt = document.getElementById('feedback-prompt');
                                                            feedbackBlock.style.display = 'block';
                                                            
                                                            if (val == 4) {
                                                                feedbackPrompt.innerText = 'Casi perfecto. ¿Qué detalle podríamos mejorar de la ficha?';
                                                            } else if (val == 3) {
                                                                feedbackPrompt.innerText = 'Gracias por tu valoración. ¿En qué consideras que deberíamos mejorar la ficha?';
                                                            } else if (val == 2) {
                                                                feedbackPrompt.innerText = 'Lamentamos no cumplir tus expectativas. ¿Qué información echas en falta o consideras incorrecta?';
                                                            } else if (val == 1) {
                                                                feedbackPrompt.innerText = 'Sentimos mucho tu mala experiencia. Por favor, indícanos qué errores graves has encontrado en la ficha para solucionarlos de inmediato.';
                                                            }

                                                            const submitBtn = document.getElementById('submit-feedback-btn');
                                                            submitBtn.onclick = function() {
                                                                const text = document.getElementById('feedback-text').value;
                                                                const msg = document.getElementById('feedback-message');
                                                                if (!text.trim()) {
                                                                    msg.style.display = 'block';
                                                                    msg.style.color = '#dc2626';
                                                                    msg.innerText = 'Por favor, escribe un comentario.';
                                                                    return;
                                                                }

                                                                submitBtn.disabled = true;
                                                                submitBtn.innerText = 'Enviando...';
                                                                submitBtn.style.opacity = '0.7';

                                                                fetch('<?= site_url('company/rate_feedback') ?>', {
                                                                    method: 'POST',
                                                                    headers: {
                                                                        'Content-Type': 'application/x-www-form-urlencoded',
                                                                        'X-Requested-With': 'XMLHttpRequest'
                                                                    },
                                                                    body: new URLSearchParams({
                                                                        'company_id': companyId,
                                                                        'feedback': text,
                                                                        '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
                                                                    })
                                                                })
                                                                .then(res => res.json())
                                                                .then(fData => {
                                                                    msg.style.display = 'block';
                                                                    if (fData.status === 'success') {
                                                                        msg.style.color = '#16a34a';
                                                                        msg.innerText = fData.message;
                                                                        setTimeout(() => {
                                                                            feedbackBlock.style.display = 'none';
                                                                        }, 3000);
                                                                    } else {
                                                                        msg.style.color = '#dc2626';
                                                                        msg.innerText = fData.message || 'Error al enviar sugerencia';
                                                                        submitBtn.disabled = false;
                                                                        submitBtn.innerText = 'Enviar sugerencia';
                                                                        submitBtn.style.opacity = '1';
                                                                    }
                                                                })
                                                                .catch(err => {
                                                                    msg.style.display = 'block';
                                                                    msg.style.color = '#dc2626';
                                                                    msg.innerText = 'Error de conexión';
                                                                    submitBtn.disabled = false;
                                                                    submitBtn.innerText = 'Enviar sugerencia';
                                                                    submitBtn.style.opacity = '1';
                                                                });
                                                            };
                                                        }

                                                    } else {
                                                        msgDiv.style.color = '#dc2626';
                                                        msgDiv.innerText = data.message || 'Error al procesar la valoración';
                                                        // Re-enable voting if it wasn't a "already voted" error
                                                        if (data.message !== 'Ya has valorado esta empresa anteriormente') {
                                                            hasVoted = false;
                                                        }
                                                    }
                                                })
                                                .catch(err => {
                                                    msgDiv.style.display = 'block';
                                                    msgDiv.style.color = '#dc2626';
                                                    msgDiv.innerText = 'Error de conexión';
                                                    hasVoted = false;
                                                });
                                        });
                                    });
                                });
                            </script>

                            <!-- CTA VERTICE (Sidebar) -->
                            <a href="https://vertice.apiempresas.es" target="_blank" rel="noopener noreferrer" style="display: block; background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%); border-radius: 12px; padding: 1.5rem 1.25rem; text-decoration: none; position: relative; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.5); transition: transform 0.2s, box-shadow 0.2s;"
                               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 15px 30px -5px rgba(15, 23, 42, 0.6)';"
                               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px -5px rgba(15, 23, 42, 0.5)';"
                               onclick="if(window.trackEvent) trackEvent('click_vertice_banner', { source: 'company_sidebar' });">
                                
                                <!-- Decorative element -->
                                <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: rgba(249, 115, 22, 0.2); filter: blur(30px); border-radius: 50%;"></div>
                                
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 0.85rem; position: relative; z-index: 1;">
                                    <span style="background: #22c55e; color: #ffffff; font-size: 0.7rem; font-weight: 800; padding: 4px 8px; border-radius: 6px; letter-spacing: 0.5px; text-transform: uppercase; box-shadow: 0 2px 5px rgba(34, 197, 94, 0.3);">
                                        ¡NUEVO!
                                    </span>
                                </div>
                                
                                <p style="color: #f8fafc; font-size: 1rem; line-height: 1.5; margin: 0 0 1.25rem 0; font-weight: 600; position: relative; z-index: 1;">
                                    No te la juegues al abrir un local. Analiza la viabilidad de cualquier municipio con IA.
                                </p>

                                <div style="display: flex; align-items: center; justify-content: center; width: 100%; background: linear-gradient(to right, #f97316, #ea580c); color: #ffffff; font-weight: 700; font-size: 1rem; padding: 12px 0; border-radius: 8px; box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3); transition: all 0.2s; position: relative; z-index: 1;">
                                    Probar Vértice gratis 🚀
                                </div>
                            </a>


                        </div> <!-- /left col of aside -->

                        <div class="company-seo-block b2b-card" style="height: 100%; margin-top: 0; order: 1;">
                            <h2 class="company-seo-title" style="font-size: 1.3rem; margin: 0 0 1rem 0;">Información General y de Contacto de <?= esc($companyName) ?>
                            </h2>
                            <?php if (!empty($company['ai_seo_text'])): ?>
                                <div class="seo-text mb-4" style="line-height: 1.6; color: #475569;">
                                    <?= nl2br(esc($company['ai_seo_text'])) ?>
                                </div>
                            <?php else: ?>
                                <div id="fallback-seo-text" style="display: none;">
                                    <p class="seo-text mb-4">
                                        La empresa <strong><?= esc($companyName) ?></strong> <?php if (!empty($companyCif) && $companyCif !== 'Desconocido' && $companyCif !== '-'): ?>cuenta con el <strong>CIF <?= esc($companyCif) ?></strong> y <?php endif; ?>mantiene su
                                        <strong>domicilio social</strong> en
                                        <?php if (!empty($provinceUrl)): ?>
                                            <a href="<?= esc($provinceUrl) ?>"
                                                style="color: inherit; font-weight: 700;"><?= esc($companyProv) ?></a>.
                                        <?php else: ?>
                                            <strong><?= esc($companyProv) ?></strong>.
                                        <?php endif; ?>

                                        Esta sociedad desarrolla su actividad en el sector de
                                        <?php if (!empty($provinceCnaeUrl)): ?>
                                            <a href="<?= esc($provinceCnaeUrl) ?>"
                                                style="color: inherit; font-weight: 700;"><?= esc($companyAct) ?></a><?php if (!empty($company['cnae']) || !empty($company['cnae_code'])): ?>,<?php else: ?>.<?php endif; ?>
                                        <?php elseif (!empty($cnaeUrl)): ?>
                                            <a href="<?= esc($cnaeUrl) ?>"
                                                style="color: inherit; font-weight: 700;"><?= esc($companyAct) ?></a><?php if (!empty($company['cnae']) || !empty($company['cnae_code'])): ?>,<?php else: ?>.<?php endif; ?>
                                        <?php else: ?>
                                            <em><?= esc($companyAct) ?></em><?php if (!empty($company['cnae']) || !empty($company['cnae_code'])): ?>,<?php else: ?>.<?php endif; ?>
                                        <?php endif; ?>
                                        <?php if (!empty($company['cnae']) || !empty($company['cnae_code'])): ?>
                                        registrada bajo el código <strong>CNAE
                                            <?= esc($company['cnae'] ?? ($company['cnae_code'] ?? '-')) ?></strong>.
                                        <?php endif; ?>
                                    </p>

                                    <?php
                                    $objeto = $company['corporate_purpose'] ?? $company['objeto_social'] ?? '';
                                    if (!empty($objeto)):
                                        ?>
                                        <p class="seo-text mb-4">
                                            <strong>Objeto Social:</strong> <?= esc(character_limiter($objeto, 300)) ?>
                                        </p>
                                    <?php endif; ?>

                                    <p class="seo-text mb-0">
                                        <?php 
                                            $fnd = $company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? '-';
                                            if ($fnd !== '-' && !empty($fnd)) {
                                                $fndDate = date('d/m/Y', strtotime($fnd));
                                                echo "La fecha de constitución de la empresa es el <strong>" . esc($fndDate) . "</strong>";
                                                if (!empty($statusRaw)) echo " y su estado mercantil actual es <strong>" . esc($statusRaw) . "</strong>.";
                                                else echo ".";
                                            } elseif (!empty($statusRaw)) {
                                                echo "Su estado mercantil actual es <strong>" . esc($statusRaw) . "</strong>.";
                                            }
                                        ?>
                                        En esta página podrá consultar el <strong>Informe Mercantil</strong>, el historial
                                        de <strong>Actos del BORME</strong>,
                                        sus <strong>administradores</strong><?php if (!empty($companyCif) && $companyCif !== 'Desconocido' && $companyCif !== '-'): ?> y la validación de su <strong>CIF</strong> para fines comerciales y financieros<?php endif; ?>.
                                    </p>
                                </div>

                                <div id="ai-seo-container" class="seo-text mb-4"
                                    style="line-height: 1.6; color: #475569; position: relative; min-height: 120px;">
                                    <div class="ai-skeleton" style="display: flex; flex-direction: column; gap: 10px;">
                                        <div
                                            style="height: 16px; background: #e2e8f0; border-radius: 4px; width: 100%; animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                                        </div>
                                        <div
                                            style="height: 16px; background: #e2e8f0; border-radius: 4px; width: 90%; animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                                        </div>
                                        <div
                                            style="height: 16px; background: #e2e8f0; border-radius: 4px; width: 95%; animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                                        </div>
                                        <div
                                            style="height: 16px; background: #e2e8f0; border-radius: 4px; width: 80%; animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;">
                                        </div>
                                    </div>
                                    <div
                                        style="font-size: 0.8rem; color: #94a3b8; margin-top: 10px; display: flex; align-items: center; gap: 6px;">
                                        <style>
                                            @keyframes pulse {

                                                0%,
                                                100% {
                                                    opacity: 1;
                                                }

                                                50% {
                                                    opacity: .5;
                                                }
                                            }
                                            .bg-halo { display: none; }
                                        </style>
                                        <svg class="spin-icon" width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2">
                                            <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
                                        </svg>
                                        Analizando trayectoria de la empresa con IA...
                                    </div>
                                </div>
                            <?php endif; ?>

                            <div class="company-share-row">
                                <span class="badge-demo badge-verified">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                                        <polyline points="22 4 12 14.01 9 11.01" />
                                    </svg>
                                    Datos Verificados
                                </span>
                                <span class="badge-demo badge-official">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2">
                                        <circle cx="12" cy="12" r="10" />
                                        <line x1="2" y1="12" x2="22" y2="12" />
                                        <path
                                            d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z" />
                                    </svg>
                                    Fuente Oficial
                                </span>

                                <div class="share-buttons" style="display: flex; gap: 8px; align-items: center;">
                                    <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($canonical) ?>"
                                        target="_blank" rel="noopener noreferrer nofollow"
                                        class="share-btn share-linkedin" title="Compartir en LinkedIn">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"
                                            aria-hidden="true">
                                            <path
                                                d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" />
                                        </svg>
                                    </a>
                                    <a href="https://api.whatsapp.com/send?text=<?= urlencode("Mira esta empresa: " . $canonical) ?>"
                                        target="_blank" rel="noopener noreferrer nofollow"
                                        class="share-btn share-whatsapp" title="Compartir en WhatsApp">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"
                                            aria-hidden="true">
                                            <path
                                                d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z" />
                                        </svg>
                                    </a>

                                </div>
                            </div>
                        </div>
                    </div> <!-- /b2b-grid-aside -->

                    <!-- SECCIÓN DE ADMINISTRADORES Y CARGOS -->
                    <?php if (!empty($administrators)): ?>
                        <div id="administradores" class="reveal-on-scroll" style="margin-top: 4rem;">
                            <style>.no-after-line::after { content: none !important; display: none !important; }</style>
                            <h2 class="no-after-line"
                                style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-bottom: 2rem; display: flex; align-items: center; gap: 12px;">
                                <span
                                    style="background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); color: #fff; padding: 8px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.2);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5">
                                        <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="9" cy="7" r="4"></circle>
                                        <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                        <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                                    </svg>
                                </span>
                                Administradores y Cargos Directivos de <?= esc($companyName) ?>
                            </h2>

                            <div
                                style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;">
                                <?php foreach ($administrators as $admin): ?>
                                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 14px; padding: 1.25rem; display: flex; align-items: center; gap: 1rem; transition: all 0.2s;"
                                        onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,0.05)'; this.style.borderColor='var(--primary)'"
                                        onmouseout="this.style.boxShadow='none'; this.style.borderColor='#e2e8f0'">
                                        <div
                                            style="width: 40px; height: 40px; background: #f8fafc; color: #64748b; border-radius: 50%; display: flex; align-items: center; justify-content: center; border: 1px solid #e2e8f0; flex-shrink: 0;">
                                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                                stroke-width="2">
                                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                                <circle cx="12" cy="7" r="4"></circle>
                                            </svg>
                                        </div>
                                        <div>
                                            <?php
                                            helper('text');
                                            $adminSlug = url_title($admin['name'], '-', true);
                                            $adminUrl = site_url('administrador/' . $adminSlug);
                                            ?>
                                            <a href="<?= esc($adminUrl) ?>"
                                                style="font-weight: 700; color: #1e293b; font-size: 1rem; line-height: 1.2; text-decoration: none; display: block;"
                                                onmouseover="this.style.color='#2563eb'; this.style.textDecoration='underline'"
                                                onmouseout="this.style.color='#1e293b'; this.style.textDecoration='none'">
                                                <?= esc($admin['name']) ?>
                                            </a>
                                            <div
                                                style="color: #64748b; font-size: 0.85rem; margin-top: 4px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.025em;">
                                                <?= esc($admin['position']) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- SECCIÓN PARA DESARROLLADORES (API Banner Nativo) -->
                    <section id="api-dev-section" class="api-dev-section"
                        class="reveal-on-scroll" style="margin-top: 4rem; padding: 2rem; background: #ffffff; border-radius: 20px; border: 1px solid #e2e8f0; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05);">
                        <div class="api-dev-grid"
                            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 3rem; align-items: center;">

                            <!-- Columna Izquierda: Mensaje y CTA -->
                            <div class="api-dev-info">
                                <h3 style="display: flex; align-items: center; gap: 1rem; margin: 0 0 1rem; font-size: 1.5rem; font-weight: 800; color: #0f172a; letter-spacing: -0.025em;">
                                    <div style="display: flex; align-items: center; justify-content: center; width: 48px; height: 48px; background: #eff6ff; color: #3b82f6; border-radius: 12px; flex-shrink: 0;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="16 18 22 12 16 6"></polyline>
                                            <polyline points="8 6 2 12 8 18"></polyline>
                                        </svg>
                                    </div>
                                    ¿Eres desarrollador?
                                </h3>
                                <p style="margin: 0 0 2rem; color: #64748b; line-height: 1.6; font-size: 1.05rem;">
                                    Integra la información oficial de <strong><?= esc($companyName) ?></strong>
                                    directamente
                                    en tu software mediante nuestra API REST robusta y documentada.
                                </p>
                                <a href="<?= site_url('register') ?>" class="btn secondary"
                                    style="display: inline-flex; align-items: center; padding: 0.875rem 2rem; font-weight: 700; border-radius: 12px; transition: all 0.2s; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: white; border: none; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); text-decoration: none;"
                                    onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 16px rgba(16, 185, 129, 0.4)';"
                                    onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 12px rgba(16, 185, 129, 0.3)';">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 8px;">
                                        <path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path>
                                    </svg>
                                    Obtener API Key Gratuitamente
                                </a>
                            </div>

                            <!-- Columna Derecha: Consola / Code Snippet -->
                            <div class="console-wrapper ai-box-glow"
                                style="background: #0f172a; border-radius: 16px; overflow: hidden; box-shadow: 0 15px 35px -5px rgba(15, 23, 42, 0.4); border: 1px solid #1e293b;">
                                <div class="console-header"
                                    style="display: flex; align-items: center; justify-content: space-between; padding: 12px 16px; background: #1e293b; border-bottom: 1px solid #334155;">
                                    <div class="mac-buttons" style="display: flex; gap: 8px;">
                                        <div style="width: 12px; height: 12px; border-radius: 50%; background: #ef4444;"></div>
                                        <div style="width: 12px; height: 12px; border-radius: 50%; background: #f59e0b;"></div>
                                        <div style="width: 12px; height: 12px; border-radius: 50%; background: #10b981;"></div>
                                    </div>
                                    <span style="font-size: 0.75rem; color: #94a3b8; font-family: monospace; font-weight: 600;">Ver respuesta JSON</span>
                                </div>
                                <div class="console-body"
                                    style="padding: 1.5rem; font-family: 'Fira Code', 'Courier New', Courier, monospace; font-size: 0.85rem; color: #e2e8f0; line-height: 1.7; overflow-x: auto;">
                                    <div style="color: #64748b; margin-bottom: 8px;"># Petición cURL para <?= esc($companyCif) ?></div>
                                    <div style="display: flex; gap: 8px;">
                                        <span style="color: #ec4899;">curl</span>
                                        <span style="color: #a7f3d0; word-break: break-all;">"https://apiempresas.es/api/v1/companies?cif=<?= esc($companyCif) ?>"</span>
                                    </div>
                                    <div style="padding-left: 2rem; color: #fde047;">-H "Authorization: Bearer TU_API_KEY"</div>
                                </div>
                            </div>
                        </div>
                    </section>

                    <!-- CONTRATOS Y SUBVENCIONES SECTION -->
                    <?php if (!empty($contracts) || !empty($subsidies)): ?>
                        <div id="financial-data" class="reveal-on-scroll" style="margin-top: 4rem;">
                            <div class="b2b-card" style="padding: 32px; border-radius: 20px;">
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                                    <div>
                                        <h2 class="b2b-card__title" style="margin: 0; font-size: 1.4rem;">
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                            </svg>
                                            Licitaciones Públicas y Subvenciones
                                        </h2>
                                        <p style="color: #64748b; margin: 8px 0 0 0; font-size: 0.95rem;">
                                            Historial oficial de contratos adjudicados por el Estado y subvenciones recibidas por <?= esc($companyName) ?>.
                                        </p>
                                    </div>
                                </div>

                                <?php if (!empty($contracts)): ?>
                                    <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-top: 32px; margin-bottom: 16px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">Contratos Públicos Adjudicados</h3>
                                    <div style="overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0;">
                                        <table style="width: 100%; border-collapse: collapse; min-width: 600px; text-align: left;">
                                            <thead>
                                                <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                                    <th style="padding: 12px 16px; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase;">Fecha</th>
                                                    <th style="padding: 12px 16px; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase;">Órgano de Contratación</th>
                                                    <th style="padding: 12px 16px; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase;">Título del Contrato</th>
                                                    <th style="padding: 12px 16px; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase; text-align: right;">Importe</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($contracts as $contract): ?>
                                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                                    <td style="padding: 12px 16px; font-size: 0.9rem; color: #64748b; white-space: nowrap; vertical-align: top;">
                                                        <?= date('d/m/Y', strtotime($contract['fecha_adjudicacion'])) ?>
                                                    </td>
                                                    <td style="padding: 12px 16px; font-size: 0.9rem; color: #334155; font-weight: 500; vertical-align: top;">
                                                        <?= esc($contract['organo_contratacion']) ?>
                                                    </td>
                                                    <td style="padding: 12px 16px; font-size: 0.9rem; color: #475569; vertical-align: top;">
                                                        <?= esc($contract['titulo_contrato']) ?>
                                                        <?php if (!empty($contract['enlace_licitacion'])): ?>
                                                            <a href="<?= esc($contract['enlace_licitacion']) ?>" target="_blank" style="color: #2563eb; text-decoration: none; margin-left: 8px; display: inline-block;" title="Ver documento original">
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                                            </a>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td style="padding: 12px 16px; font-size: 0.95rem; font-weight: 700; color: #0f172a; text-align: right; white-space: nowrap; vertical-align: top;">
                                                        <?= number_format($contract['importe_adjudicacion'], 2, ',', '.') ?> €
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($subsidies)): ?>
                                    <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-top: 32px; margin-bottom: 16px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">Subvenciones y Ayudas Recibidas</h3>
                                    <div style="overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0;">
                                        <table style="width: 100%; border-collapse: collapse; min-width: 600px; text-align: left;">
                                            <thead>
                                                <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                                    <th style="padding: 12px 16px; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase;">Fecha</th>
                                                    <th style="padding: 12px 16px; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase;">Instrumento</th>
                                                    <th style="padding: 12px 16px; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase;">Convocatoria</th>
                                                    <th style="padding: 12px 16px; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase; text-align: right;">Importe</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($subsidies as $subsidy): ?>
                                                <tr style="border-bottom: 1px solid #e2e8f0;">
                                                    <td style="padding: 12px 16px; font-size: 0.9rem; color: #64748b; white-space: nowrap; vertical-align: top;">
                                                        <?= date('d/m/Y', strtotime($subsidy['fecha_concesion'])) ?>
                                                    </td>
                                                    <td style="padding: 12px 16px; font-size: 0.9rem; color: #334155; font-weight: 500; vertical-align: top;">
                                                        <?= esc($subsidy['instrumento']) ?>
                                                    </td>
                                                    <td style="padding: 12px 16px; font-size: 0.9rem; color: #475569; vertical-align: top;">
                                                        <?= esc($subsidy['convocatoria']) ?>
                                                    </td>
                                                    <td style="padding: 12px 16px; font-size: 0.95rem; font-weight: 700; color: #0f172a; text-align: right; white-space: nowrap; vertical-align: top;">
                                                        <?= number_format($subsidy['importe'], 2, ',', '.') ?> €
                                                    </td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>

                                <!-- API CTA Developer-First -->
                                <div style="margin-top: 32px; background: #0f172a; border-radius: 16px; padding: 24px; color: #f8fafc; display: flex; flex-direction: column; gap: 16px; overflow: hidden; position: relative;">
                                    <div style="position: absolute; top: 0; right: 0; padding: 24px; opacity: 0.05; pointer-events: none;">
                                        <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="16 18 22 12 16 6"></polyline>
                                            <polyline points="8 6 2 12 8 18"></polyline>
                                        </svg>
                                    </div>
                                    <div style="position: relative; z-index: 1;">
                                        <h4 style="margin: 0 0 8px 0; font-size: 1.15rem; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                            </svg>
                                            ¿Necesitas procesar estos datos de forma masiva?
                                        </h4>
                                        <p style="margin: 0; font-size: 0.95rem; color: #cbd5e1; line-height: 1.5; max-width: 800px;">
                                            Extrae el historial financiero y de contratos públicos de millones de empresas en milisegundos con nuestra API REST. Ideal para integrarlo en tu CRM, herramientas de scoring o automatizaciones B2B.
                                        </p>
                                    </div>
                                    
                                    <div style="background: #1e293b; border-radius: 8px; border: 1px solid #334155; padding: 16px; font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace; font-size: 0.85rem; color: #e2e8f0; overflow-x: auto; position: relative; z-index: 1;">
                                        <div style="color: #6ee7b7; margin-bottom: 8px;">GET /api/v1/companies/<?= esc($companyCif) ?>/contracts</div>
                                        <pre style="margin: 0; padding: 0; background: transparent; border: none; color: inherit; overflow: visible;">{
  "success": true,
  "data": [
    {
      "fecha": "<?= !empty($contracts) ? date('Y-m-d', strtotime($contracts[0]['fecha_adjudicacion'])) : '2023-11-15' ?>",
      "organo": "<?= !empty($contracts) ? esc($contracts[0]['organo_contratacion']) : 'Ministerio de Defensa' ?>",
      "importe": <?= !empty($contracts) ? $contracts[0]['importe_adjudicacion'] : '145000.50' ?>
    }
  ]
}</pre>
                                    </div>
                                    
                                    <div style="position: relative; z-index: 1; display: flex; justify-content: flex-start;">
                                        <a href="<?= site_url('register') ?>" style="display: inline-flex; align-items: center; gap: 8px; background: #3b82f6; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 700; font-size: 0.95rem; transition: background 0.2s;">
                                            Obtener mi API Key
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <line x1="5" y1="12" x2="19" y2="12"></line>
                                                <polyline points="12 5 19 12 12 19"></polyline>
                                            </svg>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- BORME TIMELINE SECTION -->
                    <?php if (!empty($bormePosts)): ?>
                        <div id="borme" class="reveal-on-scroll" style="margin-top: 4rem;">
                            <h2 class="no-after-line"
                                style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-bottom: 2rem; display: flex; align-items: center; gap: 12px;">
                                <span
                                    style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: #fff; padding: 8px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.2);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                </span>
                                Actos del Registro Mercantil (BORME) de <?= esc($companyName) ?>
                            </h2>

                            <?php 
                            // Process BORME data for types of acts
                            $actCounts = [];
                            $bormeTimeline = [];
                            $totalActs = 0;
                            foreach ($bormePosts as $post) {
                                // Count by month-year
                                $monthYear = date('Y-m', strtotime($post['borme_date']));
                                if (!isset($bormeTimeline[$monthYear])) $bormeTimeline[$monthYear] = ['count' => 0, 'types' => []];
                                $bormeTimeline[$monthYear]['count']++;

                                // Count by type
                                $types = array_map('trim', explode(',', strtolower($post['act_types'] ?? '')));
                                foreach ($types as $t) {
                                    if (empty($t)) continue;
                                    // Normalize some common types for better grouping
                                    if (strpos($t, 'nombramiento') !== false) $t = 'Nombramientos';
                                    elseif (strpos($t, 'cese') !== false || strpos($t, 'dimision') !== false || strpos($t, 'revocacion') !== false) $t = 'Ceses/Dimisiones';
                                    elseif (strpos($t, 'capital') !== false) $t = 'Modific. de Capital';
                                    elseif (strpos($t, 'domicilio') !== false) $t = 'Cambio de Domicilio';
                                    elseif (strpos($t, 'estatutos') !== false || strpos($t, 'objeto social') !== false) $t = 'Modific. Estatutos';
                                    elseif (strpos($t, 'constitucion') !== false) $t = 'Constitución';
                                    elseif (strpos($t, 'unipersonalidad') !== false) $t = 'Unipersonalidad';
                                    elseif (strpos($t, 'cuentas') !== false) $t = 'Cuentas Anuales';
                                    elseif (strpos($t, 'socio unico') !== false) $t = 'Socio Único';
                                    else $t = 'Otros Actos';
                                    
                                    if (!isset($actCounts[$t])) $actCounts[$t] = 0;
                                    $actCounts[$t]++;
                                    
                                    if (!isset($bormeTimeline[$monthYear]['types'][$t])) $bormeTimeline[$monthYear]['types'][$t] = 0;
                                    $bormeTimeline[$monthYear]['types'][$t]++;
                                    
                                    $totalActs++;
                                }
                            }
                            arsort($actCounts);
                            ksort($bormeTimeline);
                            // Take top 4
                            $topActs = array_slice($actCounts, 0, 4, true);
                            
                            $maxActsTimeline = 1;
                            foreach ($bormeTimeline as $data) {
                                if ($data['count'] > $maxActsTimeline) $maxActsTimeline = $data['count'];
                            }
                            
                            $monthsEs = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
                            ?>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
                                <?php if (!empty($company['ai_borme_summary'])): ?>
                                    <div class="ai-box-glow" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; color: #0f172a; font-weight: 800; font-size: 1.05rem;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                            </svg>
                                            Resumen del BORME con IA
                                        </div>
                                        <p style="margin: 0; color: #475569; line-height: 1.6; font-size: 0.95rem;">
                                            <?= nl2br(esc($company['ai_borme_summary'])) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($bormeTimeline)): ?>
                                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
                                        <h3 style="font-size: 0.9rem; font-weight: 700; color: #64748b; margin-top: 0; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Evolución de Actividad</h3>
                                        
                                        <div style="overflow-x: auto; padding-bottom: 4px;">
                                            <div style="min-width: max-content;">
                                                
                                                <!-- ROW 1: Barras -->
                                                <div style="display: flex; align-items: flex-end; gap: 6px; height: 110px; border-bottom: 1px solid #e2e8f0;">
                                                    <?php foreach ($bormeTimeline as $my => $data): 
                                                        $count = $data['count'];
                                                        $heightPct = max(($count / $maxActsTimeline) * 100, 8); 
                                                        list($y, $m) = explode('-', $my);
                                                        $tooltipYear = $monthsEs[$m] . " " . $y;
                                                        
                                                        $tooltip = "{$count} acto" . ($count > 1 ? 's' : '') . " en {$tooltipYear}:&#10;";
                                                        arsort($data['types']);
                                                        foreach($data['types'] as $t => $c) {
                                                            $tooltip .= "- {$t}: {$c}&#10;";
                                                        }
                                                    ?>
                                                        <div style="flex: 1; min-width: 40px; height: 100%; display: flex; flex-direction: column; justify-content: flex-end; align-items: center; cursor: crosshair;" title="<?= $tooltip ?>">
                                                            <div style="font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 4px;"><?= $count ?></div>
                                                            <div style="width: 100%; height: calc(100% - 22px); display: flex; align-items: flex-end; justify-content: center;">
                                                                <div style="width: 100%; max-width: 24px; background: linear-gradient(to top, #8b5cf6, #a78bfa); border-radius: 4px 4px 0 0; height: <?= $heightPct ?>%; min-height: 6px; transition: all 0.2s;" onmouseover="this.style.filter='brightness(1.1)'; this.style.transform='scaleY(1.05)';" onmouseout="this.style.filter='none'; this.style.transform='none';"></div>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>
                                                
                                                <!-- ROW 2: Textos -->
                                                <div style="display: flex; gap: 6px; padding-top: 6px;">
                                                    <?php foreach ($bormeTimeline as $my => $data): 
                                                        list($y, $m) = explode('-', $my);
                                                    ?>
                                                        <div style="flex: 1; min-width: 40px; text-align: center; font-size: 0.65rem; color: #94a3b8; font-weight: 600; line-height: 1.2;">
                                                            <?= $monthsEs[$m] ?><br><?= substr($y, 2) ?>'
                                                        </div>
                                                    <?php endforeach; ?>
                                                </div>

                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (!empty($topActs)): ?>
                                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
                                        <h3 style="font-size: 0.9rem; font-weight: 700; color: #64748b; margin-top: 0; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Distribución de Actos</h3>
                                        <div style="display: flex; flex-direction: column; gap: 14px;">
                                            <?php foreach ($topActs as $type => $count): 
                                                $pct = $totalActs > 0 ? round(($count / $totalActs) * 100) : 0;
                                            ?>
                                                <div>
                                                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 6px; color: #475569; font-weight: 600;">
                                                        <span><?= esc($type) ?></span>
                                                        <span style="color: #94a3b8;"><?= $count ?> acto<?= $count > 1 ? 's' : '' ?> (<?= $pct ?>%)</span>
                                                    </div>
                                                    <div style="width: 100%; background: #f1f5f9; border-radius: 99px; height: 6px; overflow: hidden;">
                                                        <div style="width: <?= $pct ?>%; background: linear-gradient(90deg, #3b82f6, #0ea5e9); height: 100%; border-radius: 99px;"></div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="borme-timeline">
                                <?php foreach ($bormePosts as $post):
                                    $acts = strtolower($post['act_types'] ?? '');
                                    // Defaults: File Icon
                                    $iconColor = '#64748b'; // Slate 500
                                    $iconBg = '#f1f5f9'; // Slate 100
                                    $iconSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>';

                                    if (strpos($acts, 'nombramientos') !== false) {
                                        $iconColor = '#16a34a'; // Green 600
                                        $iconBg = '#dcfce7'; // Green 100
                                        // Briefcase Icon
                                        $iconSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>';
                                    } elseif (strpos($acts, 'ceses') !== false || strpos($acts, 'dimisiones') !== false || strpos($acts, 'revocaciones') !== false) {
                                        $iconColor = '#dc2626'; // Red 600
                                        $iconBg = '#fee2e2'; // Red 100
                                        // File Minus/Remove Icon
                                        $iconSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="9" y1="15" x2="15" y2="15"></line></svg>';
                                    } elseif (strpos($acts, 'cuentas') !== false) {
                                        $iconColor = '#2563eb'; // Blue 600
                                        $iconBg = '#dbeafe'; // Blue 100
                                        $iconSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 22h14a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"></path><path d="M14 2v6h6"></path><path d="M3 15h6"></path><path d="M3 18h6"></path></svg>';
                                    }
                                    ?>
                                    <div class="borme-item">
                                        <!-- Icon Marker -->
                                        <div class="borme-icon" style="color: <?= $iconColor ?>; background: <?= $iconBg ?>;">
                                            <?= $iconSvg ?>
                                        </div>

                                        <article class="borme-card">
                                            <header class="borme-header">
                                                <div class="borme-date">
                                                    <?= esc(date('d M Y', strtotime($post['borme_date']))) ?>
                                                </div>
                                                <?php if (!empty($post['url_pdf'])): ?>
                                                    <a href="<?= esc($post['url_pdf']) ?>" target="_blank" class="borme-pdf">
                                                        <span>PDF</span>
                                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                                            stroke="currentColor" stroke-width="2">
                                                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6">
                                                            </path>
                                                            <polyline points="15 3 21 3 21 9"></polyline>
                                                            <line x1="10" y1="14" x2="21" y2="3"></line>
                                                        </svg>
                                                    </a>
                                                <?php endif; ?>
                                            </header>
                                            <div class="borme-body">
                                                <h3 class="borme-title"
                                                    style="margin-bottom: 12px; font-size: 1.1rem; line-height:1.4;">
                                                    <?= esc($post['act_types'] ?: 'Acto Registral') ?>
                                                </h3>
                                                <div>
                                                    <?php
                                                    // Format description
                                                    $desc = $post['description'];
                                                    $desc = preg_replace('/([A-ZÁÉÍÓÚÑ\s]+:)/u', '<strong>$1</strong>', $desc);
                                                    echo nl2br($desc);
                                                    ?>
                                                </div>
                                            </div>
                                        </article>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- CTA PROMOCIONAL B2B (Data Paywall / Excel Export) -->
                    <?php if (!session('logged_in')): ?>
                        <aside id="descargar-excel" class="seo-cta-banner"
                            style="margin-top: 3rem; background: linear-gradient(135deg, #ffffff 0%, #f4f7fb 100%); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 20px; padding: 3.5rem 2rem; display: flex; flex-direction: column; align-items: center; text-align: center; box-shadow: 0 10px 40px -10px rgba(37, 99, 235, 0.1), 0 1px 3px rgba(0,0,0,0.03); position: relative; overflow: hidden;">
                            
                            <!-- Decorative background elements -->
                            <div style="position: absolute; top: -50%; left: -10%; width: 50%; height: 200%; background: radial-gradient(circle, rgba(59, 130, 246, 0.05) 0%, transparent 60%); transform: rotate(30deg); pointer-events: none;"></div>
                            <div style="position: absolute; bottom: -50%; right: -10%; width: 50%; height: 200%; background: radial-gradient(circle, rgba(99, 102, 241, 0.05) 0%, transparent 60%); transform: rotate(-30deg); pointer-events: none;"></div>

                            <div
                                style="position: relative; z-index: 1; background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; padding: 6px 16px; border-radius: 99px; font-size: 0.8rem; font-weight: 800; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.1em; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 10px rgba(59, 130, 246, 0.1);">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                    <polyline points="7 10 12 15 17 10"></polyline>
                                    <line x1="12" y1="15" x2="12" y2="3"></line>
                                </svg>
                                Base de Datos B2B
                            </div>
                            
                            <div role="heading" aria-level="2" style="position: relative; z-index: 1; font-size: 1.8rem; font-weight: 800; color: #0f172a; margin-bottom: 1.2rem; line-height: 1.3; max-width: 800px; text-wrap: balance;">
                                ¿Buscas clientes similares a <?= esc($companyName) ?>?
                            </div>
                            
                            <p style="position: relative; z-index: 1; color: #475569; font-size: 1.1rem; max-width: 700px; margin: 0 auto 2rem; line-height: 1.6; font-weight: 400;">
                                Descarga ahora mismo el listado completo en CSV con información financiera y de contacto de 
                                <strong style="color: #0f172a; font-weight: 700;"><?= $countFormatted ?> empresas</strong> del sector <strong style="color: #0f172a; font-weight: 700;"><?= esc(trim(explode('INFORME', $sectorName)[0])) ?></strong> en <strong style="color: #0f172a; font-weight: 700;"><?= esc(!empty($targetProv) ? $targetProv : ($company['province'] ?? $company['registro_mercantil'] ?? 'España')) ?></strong>. 
                                Ideal para acelerar tus campañas de marketing y dotar a tu equipo de ventas de leads cualificados.
                            </p>
                            
                            <a href="<?= $radarCheckoutUrl ?>" rel="nofollow"
                                onclick="window.dataLayer = window.dataLayer || []; window.dataLayer.push({'event': 'cta_excel_click'});"
                                style="position: relative; z-index: 1; display: inline-flex; align-items: center; gap: 12px; background: #2563eb; color: #ffffff; padding: 14px 32px; border-radius: 12px; font-weight: 800; font-size: 1.15rem; text-decoration: none; box-shadow: 0 10px 25px rgba(37, 99, 235, 0.3); transition: all 0.3s; cursor: pointer;"
                                onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='0 15px 30px -5px rgba(37, 99, 235, 0.4), 0 8px 10px -6px rgba(37, 99, 235, 0.3)';"
                                onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px rgba(37, 99, 235, 0.3)';">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <path d="M8 13h2"></path>
                                    <path d="M8 17h2"></path>
                                    <path d="M14 13h2"></path>
                                    <path d="M14 17h2"></path>
                                </svg>
                                Descargar CSV completo por <?php if(isset($pricing) && $pricing['is_discounted']): ?><s style="opacity:0.7; font-size:0.9em; margin-right:6px;"><?= number_format($pricing['original_price'], 2, ',', '') ?>€</s><?php endif; ?><?= $priceStr ?>€ <span style="font-size:0.85em; opacity:0.85; font-weight:600;">+ IVA</span>
                            </a>
                            
                            <div style="position: relative; z-index: 1; margin-top: 2rem; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 20px; color: #64748b; font-size: 0.9rem; font-weight: 500;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Descarga Inmediata
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Formato CSV (Delimitado por comas)
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg> Datos Verificados
                                </div>
                            </div>
                        </aside>
                    <?php endif; ?>

                    <!-- FAQ Section HTML -->
                    <!-- FAQ Section HTML -->
                    <div class="b2b-grid-content-aside" style="margin-top: 2rem;">
                        <div id="preguntas-frecuentes" style="padding: 1rem 1rem 1rem 0;">
                            <h3 style="display: flex; align-items: center; gap: 8px; font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--b2b-text);">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--b2b-primary);">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path>
                                    <line x1="12" y1="17" x2="12.01" y2="17"></line>
                                </svg>
                                Preguntas Frecuentes
                            </h3>
                            <div id="faq-list-container" style="display: flex; flex-direction: column; gap: 2rem; padding-top: 0.85rem;">
                                <?php foreach ($faqs as $faq): ?>
                                    <div style="border-left: 3px solid var(--b2b-primary-light); padding-left: 1.25rem;">
                                        <h4 style="font-size: 1rem; font-weight: 600; margin: 0 0 0.5rem 0; color: var(--b2b-text); line-height: 1.4;">
                                            <?= esc($faq['q']) ?>
                                        </h4>
                                        <div style="font-size: 0.95rem; color: var(--b2b-text-muted); line-height: 1.6;">
                                            <?= strip_tags(str_replace('**', '', $faq['a'])) // Limpieza básica para HTML visual ?>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <?php if (!empty($related)): ?>
                            <div id="empresas-relacionadas" style="padding: 1rem 0 1rem 1rem;">
                                <h3 style="display: flex; align-items: center; gap: 8px; font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--b2b-text);">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--b2b-primary);">
                                        <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                                        <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                                        <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                                        <path d="M10 6h4"></path>
                                        <path d="M10 10h4"></path>
                                        <path d="M10 14h4"></path>
                                        <path d="M10 18h4"></path>
                                    </svg>
                                    Empresas relacionadas
                                </h3>

                                <div style="display: flex; flex-direction: column;">
                                    <?php 
                                    $relSlice = array_slice($related, 0, 10);
                                    foreach ($relSlice as $idx => $rel):
                                        helper('company');
                                        $relUrl = company_url($rel);
                                        $name = esc($rel['name'] ?? 'Empresa');
                                        ?>
                                        <a href="<?= esc($relUrl) ?>" 
                                           style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 0.5rem; text-decoration: none; transition: all 0.2s; border-bottom: <?= $idx < count($relSlice) - 1 ? '1px dashed #e2e8f0' : 'none' ?>;" 
                                           onmouseover="this.querySelector('.rel-text').style.color='var(--b2b-primary)'; this.querySelector('.rel-arrow').style.color='var(--b2b-primary)';" 
                                           onmouseout="this.querySelector('.rel-text').style.color='var(--b2b-text)'; this.querySelector('.rel-arrow').style.color='#cbd5e1';">
                                            
                                            <div style="display: flex; align-items: center; overflow: hidden; gap: 8px; flex: 1; min-width: 0;">
                                                <div style="color: #94a3b8; display: flex; align-items: center; flex-shrink: 0;">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                        <rect x="4" y="2" width="16" height="20" rx="2" ry="2"></rect>
                                                        <path d="M9 22v-4h6v4"></path>
                                                        <path d="M8 6h.01"></path>
                                                        <path d="M16 6h.01"></path>
                                                        <path d="M12 6h.01"></path>
                                                        <path d="M12 10h.01"></path>
                                                        <path d="M12 14h.01"></path>
                                                        <path d="M16 10h.01"></path>
                                                        <path d="M16 14h.01"></path>
                                                        <path d="M8 10h.01"></path>
                                                        <path d="M8 14h.01"></path>
                                                    </svg>
                                                </div>
                                                <span class="rel-text" style="font-weight: 600; color: var(--b2b-text); font-size: 0.9rem; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; max-width: 100%; transition: color 0.2s; display: block;">
                                                    <?= $name ?>
                                                </span>
                                            </div>
                                            
                                            <div class="rel-arrow" style="color: #cbd5e1; flex-shrink: 0; margin-left: 0.5rem; display: flex; align-items: center; transition: color 0.2s;">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="9 18 15 12 9 6"></polyline>
                                                </svg>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div> <!-- /b2b-grid-content-aside -->


                    <!-- Schema.org JSON-LD -->
                    <script type="application/ld+json">
                    <?= json_encode($schemaOrg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
                </script>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const cifVal = document.getElementById('cif-val');

                            // 1. Copy to clipboard on click
                            if (cifVal) {
                                cifVal.addEventListener('click', function () {
                                    const text = this.innerText.trim();
                                    navigator.clipboard.writeText(text).then(() => {
                                        showApiToast();
                                    });
                                });
                            }

                            // 2. Detect any copy event on the page (if they select and copy manually)
                            document.addEventListener('copy', (event) => {
                                const selection = document.getSelection();
                                if (selection.toString().trim() === cifVal.innerText.trim()) {
                                    // Short delay to let the default copy finish
                                    setTimeout(showApiToast, 200);
                                }
                            });

                            function showApiToast() {
                                if (document.querySelector('.api-toast')) return;

                                const toast = document.createElement('div');
                                toast.className = 'api-toast';
                                toast.innerHTML = `
                                <div style="background: #3b82f6; width: 8px; height: 8px; border-radius: 50%;"></div>
                                <div style="font-size: 0.9rem;">
                                    ¿Copiando datos manualmente? <b>Usa nuestra API</b> y ahorra tiempo.
                                </div>
                                <a href="#api-dev-section" class="btn-toast">Ver API</a>
                            `;
                                document.body.appendChild(toast);

                                // Trigger animation
                                setTimeout(() => toast.classList.add('show'), 10);

                                // Remove after 5 seconds
                                setTimeout(() => {
                                    toast.classList.remove('show');
                                    setTimeout(() => toast.remove(), 400);
                                }, 5000);
                            }
                        });
                    </script>




                    <?php
                    // --- SEO SILO INTERNAL LINKS ---
                    $seoProv = $company['province'] ?? $company['registro_mercantil'] ?? '';
                    $secoProvStr = !empty($seoProv) ? ucfirst(strtolower($seoProv)) : '';
                    $seoCnae = current(explode(' ', $company['cnae'] ?? ''));
                    $seoCnaeLabel = $company['cnae_label'] ?? '';
                    ?>
                    <div style="margin-bottom: 4rem;">
                        <h3 style="display: flex; align-items: center; gap: 10px; font-size: 1.5rem; font-weight: 800; margin-bottom: 2rem; color: var(--b2b-text);">
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--b2b-primary);">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
                            </svg>
                            Explorar más empresas
                        </h3>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem;">
                            <?php if ($secoProvStr): ?>
                                <a href="<?= site_url('empresas/' . url_title($secoProvStr, '-', true)) ?>"
                                   style="display: flex; flex-direction: column; padding: 1.5rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; text-decoration: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 2px 4px rgba(0,0,0,0.02);"
                                   onmouseover="this.style.boxShadow='0 12px 24px rgba(0,0,0,0.08)'; this.style.borderColor='var(--b2b-primary-light)'; this.style.transform='translateY(-4px)';"
                                   onmouseout="this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)'; this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)';">
                                   
                                   <div style="width: 48px; height: 48px; border-radius: 12px; background: #f0f9ff; color: #0ea5e9; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                       <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                           <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                           <circle cx="12" cy="10" r="3"></circle>
                                       </svg>
                                   </div>
                                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                        Directorio Provincial
                                    </span>
                                    <span style="font-size: 1.1rem; font-weight: 700; color: #0f172a; line-height: 1.3;">
                                        Empresas en <?= esc($secoProvStr) ?>
                                    </span>
                                </a>
                            <?php endif; ?>

                            <?php if ($seoCnae && $seoCnaeLabel): ?>
                                <a href="<?= site_url('empresas-nuevas-sector/' . url_title($seoCnaeLabel, '-', true)) ?>"
                                   style="display: flex; flex-direction: column; padding: 1.5rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; text-decoration: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 2px 4px rgba(0,0,0,0.02);"
                                   onmouseover="this.style.boxShadow='0 12px 24px rgba(0,0,0,0.08)'; this.style.borderColor='var(--b2b-primary-light)'; this.style.transform='translateY(-4px)';"
                                   onmouseout="this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)'; this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)';">
                                   
                                   <div style="width: 48px; height: 48px; border-radius: 12px; background: #f5f3ff; color: #8b5cf6; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                       <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                           <path d="M21.21 15.89A10 10 0 1 1 8 2.83"></path>
                                           <path d="M22 12A10 10 0 0 0 12 2v10z"></path>
                                       </svg>
                                   </div>
                                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                        Análisis Sectorial CNAE
                                    </span>
                                    <span style="font-size: 1.1rem; font-weight: 700; color: #0f172a; line-height: 1.3;">
                                        Más empresas de <?= esc($seoCnaeLabel) ?>
                                    </span>
                                </a>
                            <?php endif; ?>

                            <?php if ($secoProvStr && $seoCnaeLabel): ?>
                                <a href="<?= site_url('empresas-' . url_title($seoCnaeLabel, '-', true) . '-en-' . url_title($secoProvStr, '-', true)) ?>"
                                   style="display: flex; flex-direction: column; padding: 1.5rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; text-decoration: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 2px 4px rgba(0,0,0,0.02);"
                                   onmouseover="this.style.boxShadow='0 12px 24px rgba(0,0,0,0.08)'; this.style.borderColor='var(--b2b-primary-light)'; this.style.transform='translateY(-4px)';"
                                   onmouseout="this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)'; this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)';">
                                   
                                   <div style="width: 48px; height: 48px; border-radius: 12px; background: #ecfeff; color: #06b6d4; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                       <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                           <circle cx="12" cy="12" r="10"></circle>
                                           <circle cx="12" cy="12" r="6"></circle>
                                           <circle cx="12" cy="12" r="2"></circle>
                                       </svg>
                                   </div>
                                    <span style="font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                        Sector + Provincia
                                    </span>
                                    <span style="font-size: 1.1rem; font-weight: 700; color: #0f172a; line-height: 1.3;">
                                        Empresas de <?= esc($seoCnaeLabel) ?> en <?= esc($secoProvStr) ?>
                                    </span>
                                </a>
                            <?php endif; ?>

                            <?php if ($secoProvStr): ?>
                                <a href="<?= site_url('empresas-nuevas/' . url_title($secoProvStr, '-', true)) ?>"
                                   style="display: flex; flex-direction: column; padding: 1.5rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; text-decoration: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 2px 4px rgba(0,0,0,0.02);"
                                   onmouseover="this.style.boxShadow='0 12px 24px rgba(0,0,0,0.08)'; this.style.borderColor='#fbbf24'; this.style.transform='translateY(-4px)';"
                                   onmouseout="this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)'; this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)';">
                                   
                                   <div style="width: 48px; height: 48px; border-radius: 12px; background: #fffbeb; color: #d97706; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                       <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                           <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                       </svg>
                                   </div>
                                    <span style="font-size: 0.75rem; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem;">
                                        B2B Lead Generation
                                    </span>
                                    <span style="font-size: 1.1rem; font-weight: 700; color: #0f172a; line-height: 1.3;">
                                        Empresas nuevas en <?= esc($secoProvStr) ?>
                                    </span>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>



                    <!-- RADAR PRO CTA -->
                    <div class="dash-cta-card"
                        style="margin-top: 5rem; display: grid; grid-template-columns: 1fr auto; gap: 30px; align-items: center;">
                        <div>
                            <h3>
                                <div class="dash-cta-icon">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5">
                                        <circle cx="12" cy="12" r="10" />
                                        <path
                                            d="M12 2v4M12 18v4M4.93 4.93l2.83 2.83M16.24 16.24l2.83 2.83M2 12h4M18 12h4M4.93 19.07l2.83-2.83M16.24 7.76l2.83-2.83" />
                                    </svg>
                                </div>
                                ¿Te interesa recibir leads como <?= esc($company['name'] ?? 'esta') ?>?
                            </h3>
                            <p style="margin-bottom: 0;">
                                Monitorizamos el <strong>BORME</strong> en tiempo real. Configura tu Radar para recibir
                                alertas de nuevas empresas en tu sector y provincia antes que nadie.
                            </p>
                        </div>
                        <div style="min-width: 200px;">
                            <a href="<?= site_url() ?>radar" class="btn">
                                Activar Radar PRO →
                            </a>
                        </div>
                    </div>

                </div>
        </section>
    </main>

    <?= view('partials/footer') ?>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const btn = document.getElementById('btnToggleJson');
            const pre = document.getElementById('jsonBlock');

            if (btn && pre) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const nowHidden = pre.classList.toggle('is-hidden');
                    btn.textContent = nowHidden ? 'Ver JSON de la API' : 'Ocultar JSON de la API';
                });
            }

            <?php if ((!empty($company['lat']) && !empty($company['lng'])) || !empty($company['address'])): ?>
                const mapContainer = document.getElementById('company-map');
                if (mapContainer) {
                    let mapLoaded = false;
                    const loadMap = () => {
                        if (mapLoaded) return;
                        mapLoaded = true;

                        const hasCoords = <?= (!empty($company['lat']) && !empty($company['lng'])) ? 'true' : 'false' ?>;
                        const companyName = "<?= esc($company['name'] ?? $company['nombre'] ?? 'Empresa', 'js') ?>";
                        const rawAddress = "<?= esc($company['address'] ?? '', 'js') ?>";
                        const province = "<?= esc($company['province'] ?? $company['provincia'] ?? '', 'js') ?>";

                        if (hasCoords) {
                            if (typeof L === 'undefined') {
                                const script = document.createElement('script');
                                script.src = "https://unpkg.com/leaflet@1.9.4/dist/leaflet.js";
                                script.onload = () => initLeafletMap(companyName, rawAddress, province);
                                document.head.appendChild(script);
                            } else {
                                initLeafletMap(companyName, rawAddress, province);
                            }
                        } else {
                            const fullAddress = `${rawAddress}, ${province}, España`;
                            const iframe = document.createElement('iframe');
                            iframe.width = "100%";
                            iframe.height = "100%";
                            iframe.frameBorder = "0";
                            iframe.style.border = "0";
                            iframe.style.borderRadius = "12px";
                            iframe.loading = "lazy";
                            iframe.src = `https://maps.google.com/maps?q=${encodeURIComponent(fullAddress)}&t=&z=15&ie=UTF8&iwloc=&output=embed`;
                            mapContainer.innerHTML = '';
                            mapContainer.appendChild(iframe);
                        }
                    };

                    const initLeafletMap = (companyName, rawAddress, province) => {
                        const lat = <?= (float) ($company['lat'] ?? 0) ?>;
                        const lng = <?= (float) ($company['lng'] ?? 0) ?>;

                        const map = L.map('company-map', {
                            scrollWheelZoom: false,
                            zoomControl: true
                        }).setView([lat, lng], 16);

                        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                            attribution: '&copy; OpenStreetMap contributors &copy; CARTO',
                            subdomains: 'abcd',
                            maxZoom: 20
                        }).addTo(map);

                        const modernIcon = L.divIcon({
                            className: 'custom-div-icon',
                            html: `
                        <div style="background-color: #3b82f6; width: 40px; height: 40px; border-radius: 50% 50% 50% 0; transform: rotate(-45deg); display: flex; align-items: center; justify-content: center; border: 3px solid white; box-shadow: 0 4px 6px rgba(0,0,0,0.2);">
                            <div style="width: 12px; height: 12px; background-color: white; border-radius: 50%; transform: rotate(45deg);"></div>
                        </div>
                    `,
                            iconSize: [40, 40],
                            iconAnchor: [20, 40],
                            popupAnchor: [0, -35]
                        });

                        L.marker([lat, lng], { icon: modernIcon }).addTo(map)
                            .bindPopup(`<strong>${companyName}</strong><br><span style="color: #64748b; font-size: 0.85rem;">${rawAddress}${province ? ', ' + province : ''}</span>`)
                            .openPopup();
                    };

                    if ('IntersectionObserver' in window) {
                        const observer = new IntersectionObserver((entries, observer) => {
                            entries.forEach(entry => {
                                if (entry.isIntersecting) {
                                    loadMap();
                                    observer.disconnect();
                                }
                            });
                        }, { rootMargin: '300px 0px' });
                        observer.observe(mapContainer);
                    } else {
                        loadMap();
                    }
                }
            <?php endif; ?>

            <?php if (empty($company['ai_seo_text'])): ?>
                // Fetch AI SEO Text if not cached
                fetch('<?= site_url("api/internal/generate-seo-text") ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams({
                        'cif': '<?= esc($companyCif) ?>'
                    })
                })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Network error or rate limit');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const container = document.getElementById('ai-seo-container');
                        if (container && (data.status === 'generated' || data.status === 'cached')) {
                            // Replace newlines with <br> and fade in
                            const htmlText = data.text.replace(/\n/g, '<br>');
                            container.style.opacity = 0;
                            container.innerHTML = htmlText;
                            setTimeout(() => {
                                container.style.transition = 'opacity 0.5s';
                                container.style.opacity = 1;
                            }, 50);

                            // Dynamically update FAQs if generated
                            if (data.faqs && data.faqs.length > 0) {
                                const faqList = document.getElementById('faq-list-container');
                                if (faqList) {
                                    const escapeHtml = (str) => {
                                        return str
                                            .replace(/&/g, "&amp;")
                                            .replace(/</g, "&lt;")
                                            .replace(/>/g, "&gt;")
                                            .replace(/"/g, "&quot;")
                                            .replace(/'/g, "&#039;");
                                    };
                                    let html = '';
                                    data.faqs.forEach(faq => {
                                        html += `<div>
                                    <h4 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.5rem; color: #111;">
                                        ${escapeHtml(faq.q)}
                                    </h4>
                                    <div style="font-size: 0.9rem; color: #555; line-height: 1.5;">
                                        ${escapeHtml(faq.a)}
                                    </div>
                                </div>`;
                                    });
                                    faqList.innerHTML = html;
                                }
                            }
                        } else {
                            throw new Error('API generated an error');
                        }
                    })
                    .catch(error => {
                        console.error('Error generating AI text:', error);
                        const container = document.getElementById('ai-seo-container');
                        const fallback = document.getElementById('fallback-seo-text');
                        if (container && fallback) {
                            container.style.opacity = 0;
                            container.innerHTML = fallback.innerHTML;
                            setTimeout(() => {
                                container.style.transition = 'opacity 0.5s';
                                container.style.opacity = 1;
                            }, 50);
                        }
                    });
            <?php endif; ?>
        });

        // Micro-animaciones (Scroll Reveal)
        document.addEventListener('DOMContentLoaded', () => {
            const observerOptions = {
                root: null,
                rootMargin: '0px',
                threshold: 0.1
            };

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('is-visible');
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);

            document.querySelectorAll('.reveal-on-scroll').forEach(el => {
                observer.observe(el);
            });
        });
    </script>

    <!-- CRM Modal -->
    <div id="crm-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.6); backdrop-filter: blur(4px); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #ffffff; border-radius: 24px; width: 100%; max-width: 500px; padding: 32px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25); position: relative; animation: modalIn 0.3s cubic-bezier(0.16, 1, 0.3, 1);">
            <button onclick="document.getElementById('crm-modal').style.display='none';" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: #64748b; cursor: pointer; padding: 4px; border-radius: 50%; transition: all 0.2s;" onmouseover="this.style.background='#f1f5f9'; this.style.color='#0f172a';" onmouseout="this.style.background='none'; this.style.color='#64748b';">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
            </button>
            
            <div style="width: 56px; height: 56px; background: #eff6ff; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #2563eb; margin-bottom: 24px;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                    <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                    <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                    <line x1="12" y1="22.08" x2="12" y2="12"></line>
                </svg>
            </div>
            
            <h3 style="margin: 0 0 12px 0; font-size: 1.4rem; color: #0f172a; font-weight: 800; line-height: 1.3;">Automatiza tu flujo de ventas</h3>
            <p style="margin: 0 0 24px 0; font-size: 1rem; color: #475569; line-height: 1.6;">
                Conecta APIEmpresas con tu CRM favorito (HubSpot, Salesforce, Pipedrive) utilizando nuestra <strong>API REST</strong> o integraciones como <strong>Make.com</strong>.<br><br>
                Enriquece tu base de datos automáticamente sin teclear nada.
            </p>
            
            <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                <a href="<?= site_url('documentation') ?>" style="flex: 1; min-width: 150px; text-align: center; background: #2563eb; color: #ffffff; text-decoration: none; padding: 14px 20px; border-radius: 12px; font-weight: 700; font-size: 1rem; box-shadow: 0 4px 12px rgba(37,99,235,0.2); transition: background 0.2s;" onmouseover="this.style.background='#1d4ed8';" onmouseout="this.style.background='#2563eb';">
                    Descubrir la API
                </a>
                <button onclick="document.getElementById('crm-modal').style.display='none';" style="flex: 1; min-width: 150px; text-align: center; background: #f8fafc; color: #475569; text-decoration: none; padding: 14px 20px; border-radius: 12px; font-weight: 700; font-size: 1rem; border: 1px solid #cbd5e1; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#f1f5f9';" onmouseout="this.style.background='#f8fafc';">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
    <style>
    @keyframes modalIn {
        from { opacity: 0; transform: translateY(20px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    </style>
</body>

</html>