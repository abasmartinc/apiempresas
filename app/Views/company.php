<!doctype html>
<html lang="es">

<head>
    <?= view('partials/head', [
        'title' => $title,
        'excerptText' => $meta_description,
        'canonical' => $canonical,
        'robots' => $robots,
    ]) ?>

    <?php
    // Generación dinámica y segura del Schema JSON-LD para la entidad principal
    $schemaOrg = [
        '@context' => 'https://schema.org',
        '@type'    => 'Organization',
        'name'     => $companyName ?? ($company['company_name'] ?? ''),
        'taxID'    => $companyCif ?? '',
        'url'      => $canonical ?? current_url(),
    ];

    $addressData = [];
    if (!empty($company['address'])) $addressData['streetAddress'] = $company['address'];
    if (!empty($company['postal_code'])) $addressData['postalCode'] = $company['postal_code'];
    
    $provinceVal = $company['province'] ?? $company['provincia'] ?? '';
    if (!empty($company['municipality'])) {
        $addressData['addressLocality'] = $company['municipality'];
    } elseif (!empty($provinceVal)) {
        $addressData['addressLocality'] = $provinceVal;
    }
    if (!empty($provinceVal)) $addressData['addressRegion'] = $provinceVal;

    if (!empty($addressData)) {
        $addressData['@type'] = 'PostalAddress';
        $addressData['addressCountry'] = 'ES';
        $schemaOrg['address'] = $addressData;
    }

    if (!empty($company['phone'])) {
        $schemaOrg['telephone'] = $company['phone'];
    }
    if (!empty($company['cnae_label'])) {
        $schemaOrg['knowsAbout'] = $company['cnae_label'];
    }
    if (!empty($company['fecha_constitucion']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $company['fecha_constitucion'])) {
        $schemaOrg['foundingDate'] = $company['fecha_constitucion'];
    }
    ?>
    <script type="application/ld+json">
    <?= json_encode($schemaOrg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
    </script>
    <link rel="preload" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" as="style"
        onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
    </noscript>

    <link rel="stylesheet" href="<?= base_url('public/css/company_ficha.css') ?>?v=1.3">
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div>

    <?= view('partials/header', ['force_public_header' => true]) ?>

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
                                    stroke-width="1.2" stroke-linecap="round" stroke-linejoin="round" style="position: relative; z-index: 1;"><use href="#icon-f15f4088"></use></svg>
                            </div>

                            <div class="b2b-hero__content" style="flex: 1;">
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; flex-wrap: wrap;">
                                    <div style="display: inline-flex; align-items: center; gap: 6px; background: #eff6ff; color: #3b82f6; padding: 4px 12px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; border: 1px solid #bfdbfe; letter-spacing: 0.5px;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-82d4f2aa"></use></svg>
                                        FICHA DE EMPRESA
                                    </div>

                                    <div style="display: inline-flex; align-items: center; gap: 4px; background: #ecfdf5; color: #059669; padding: 4px 10px; border-radius: 999px; font-size: 0.7rem; font-weight: 700; border: 1px solid #a7f3d0; letter-spacing: 0.5px; text-transform: uppercase;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-c0454e9a"></use></svg>
                                        Datos oficiales Reg. Mercantil
                                    </div>
                                    
                                    <?php if (!empty($holdingData)): ?>
                                    <a href="<?= site_url('grupos-empresariales/' . esc($holdingData['slug'])) ?>" style="text-decoration: none; display: inline-flex; align-items: center; gap: 6px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #f8fafc; padding: 4px 12px; border-radius: 999px; font-size: 0.75rem; font-weight: 700; border: 1px solid #334155; letter-spacing: 0.5px; text-transform: uppercase; transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-1px)'; this.style.boxShadow='0 4px 6px rgba(15, 23, 42, 0.2)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none';">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                        Grupo <?= esc($holdingData['name']) ?> (<?= number_format($totalHoldingCompaniesCount ?? count($holdingCompanies), 0, ',', '.') ?>)
                                    </a>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($contracts)): ?>
                                    <div style="display: inline-flex; align-items: center; gap: 4px; background: #eef2ff; color: #4f46e5; padding: 4px 10px; border-radius: 999px; font-size: 0.7rem; font-weight: 700; border: 1px solid #c7d2fe; letter-spacing: 0.5px; text-transform: uppercase;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-0e887e56"></use></svg>
                                        Contratista del Estado
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($subsidies)): ?>
                                    <div style="display: inline-flex; align-items: center; gap: 4px; background: #fefce8; color: #ca8a04; padding: 4px 10px; border-radius: 999px; font-size: 0.7rem; font-weight: 700; border: 1px solid #fef08a; letter-spacing: 0.5px; text-transform: uppercase;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-614ce003"></use></svg>
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
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="opacity: 0.7;"><use href="#icon-503da82b"></use></svg>
                                        <?= esc($tag) ?>
                                    </a>
                                    <?php endforeach; ?>
                                </div>
                                <?php endif; ?>

                                <div class="b2b-hero__meta" style="display: flex; flex-wrap: wrap; align-items: center; gap: 16px; color: #475569; font-size: 0.95rem; font-weight: 500;">
                                    <?php if (!empty($companyCif) && $companyCif !== 'Desconocido' && $companyCif !== '-'): ?>
                                    <div style="display: flex; align-items: center; gap: 6px; background: #f1f5f9; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-d557d894"></use></svg>
                                        <span style="color: #0f172a; font-weight: 700;">CIF</span>
                                        <span><?= esc($companyCif) ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <?php $provinceVal = trim($company['province'] ?? $company['provincia'] ?? ''); ?>
                                    <?php if (!empty($provinceVal) && $provinceVal !== '-'): ?>
                                    <div style="display: flex; align-items: center; gap: 6px; background: #f1f5f9; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-7dfeea20"></use></svg>
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
                                            stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-81c635a1"></use></svg>
                                        <span>Última actualización: <?= date('d/m/Y', strtotime($company['updated_at'])) ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <div style="margin-left: auto; display: flex; gap: 12px; align-items: center;">
                                        <div style="display: flex; gap: 6px;">
                                            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode(current_url()) ?>&title=<?= urlencode('Ficha de empresa: ' . $companyName) ?>" target="_blank" rel="noopener noreferrer" class="btn-share-icon" title="Compartir en LinkedIn">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-1a61bb9b"></use></svg>
                                            </a>
                                            <a href="https://api.whatsapp.com/send?text=<?= urlencode('Mira esta empresa: ' . $companyName . ' - ' . current_url()) ?>" target="_blank" rel="noopener noreferrer" class="btn-share-icon" title="Compartir por WhatsApp">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-e9115a24"></use></svg>
                                            </a>
                                            <button onclick="navigator.clipboard.writeText('<?= current_url() ?>'); alert('Enlace copiado al portapapeles');" class="btn-share-icon" title="Copiar enlace" style="cursor: pointer;">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-51786dbf"></use></svg>
                                            </button>
                                        </div>
                                        <button type="button" onclick="openCopilotModal('<?= esc($companyCif) ?>');"
                                            style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%); color: #ffffff; font-size: 0.9rem; font-weight: 700; border: none; border-radius: 10px; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 6px rgba(99, 102, 241, 0.3);"
                                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(99, 102, 241, 0.4)';"
                                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(99, 102, 241, 0.3)';">
                                            ✨ Preparar llamada con IA
                                        </button>
                                        <button type="button" onclick="document.getElementById('crm-modal').style.display='flex';"
                                            style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: #0f172a; color: #ffffff; font-size: 0.9rem; font-weight: 700; text-decoration: none; border-radius: 10px; border: 1px solid #0f172a; transition: all 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.1); cursor: pointer;"
                                            onmouseover="this.style.background='#1e293b'; this.style.transform='translateY(-2px)';"
                                            onmouseout="this.style.background='#0f172a'; this.style.transform='translateY(0)';">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-2f243988"></use></svg>
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
                                                stroke="currentColor" stroke-width="2.5" aria-hidden="true"><use href="#icon-b92ca97e"></use></svg>
                                            Descargar informe
                                        </a>
                                        <button type="button" onclick="document.getElementById('whitelabel-modal').style.display='flex'; if(window.trackEvent) trackEvent('premium_pdf_modal_opened');"
                                            style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff; font-size: 0.9rem; font-weight: 700; border: none; border-radius: 10px; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 6px rgba(16, 185, 129, 0.3);"
                                            onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 6px 12px rgba(16, 185, 129, 0.4)';"
                                            onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 4px 6px rgba(16, 185, 129, 0.3)';">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-ef5631f6"></use></svg>
                                            PDF Premium
                                        </button>
                                    </div>
                                </div>

                                <div class="b2b-hero__actions">
                                    <?php if (getenv('ENABLE_COMPANY_ALERTS') === 'true'): ?>
                                        <a href="<?= site_url('alerts/confirm/' . ($company['cif'] ?? $company['nif'] ?? '-')) ?>"
                                            class="b2b-btn b2b-btn--outline-danger">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2"><use href="#icon-2ec66f66"></use></svg>
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

<!-- RESUMEN SEO ABOVE THE FOLD -->
<div class="seo-summary-card" style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; margin-bottom:24px; box-shadow:0 2px 8px rgba(0,0,0,0.05);">

    <!-- Título -->
    <div style="padding:18px 24px 14px 24px; border-bottom:1px solid #f1f5f9;">
        <h2 style="font-size:1.1rem; font-weight:800; color:#0f172a; margin:0; line-height:1.3;">
            Información General y de Contacto de <?= esc($companyName) ?>
        </h2>
    </div>

    <!-- Texto SEO -->
    <div style="padding:18px 24px; line-height:1.7; color:#334155; font-size:1rem;">
        <?php if (!empty($company['ai_seo_text'])): ?>
            <?= nl2br(strip_tags($company['ai_seo_text'], '<strong><em><b><i><br><a><ul><li><ol><p>')) ?>
        <?php else: ?>
            <div id="fallback-seo-text" style="display:none;">
                <?php
                $companyIdForFallback = !empty($company['id']) ? (int)$company['id'] : rand(0, 9);
                $fallbackIndex = $companyIdForFallback % 10;
                $provText = !empty($provinceUrl) ? '<a href="' . esc($provinceUrl) . '" style="color:inherit;font-weight:700;">' . esc($companyProv) . '</a>' : '<strong>' . esc($companyProv) . '</strong>';
                $cifText = (!empty($companyCif) && $companyCif !== 'Desconocido' && $companyCif !== '-') ? ' (CIF <strong>' . esc($companyCif) . '</strong>)' : '';
                
                // Extraer año de constitución
                $foundedYear = '';
                if (!empty($company['founded']) && $company['founded'] !== '0000-00-00' && $company['founded'] !== '-') {
                    $foundedYear = substr(trim($company['founded']), 0, 4);
                }
                
                // Extraer CNAE
                $cnaeText = '';
                if (!empty($company['cnae_label']) && strtolower(trim($company['cnae_label'])) !== 'desconocido') {
                    $cnaeText = strtolower(trim($company['cnae_label']));
                } elseif (!empty($sectorName) && strtolower(trim($sectorName)) !== 'este sector') {
                    $cnaeText = strtolower(trim($sectorName));
                }
                
                // Estado registral
                $isActive = (!empty($statusRaw) && strtoupper(trim($statusRaw)) === 'ACTIVA');
                $statusPhrase = $isActive ? ' actualmente activa' : '';
                
                // Frases condicionales
                $cnaePhrase = $cnaeText ? " dentro del sector de <strong>" . esc($cnaeText) . "</strong>" : " en su respectivo sector";
                $yearPhrase = $foundedYear ? " desde el año " . esc($foundedYear) : "";
                
                switch ($fallbackIndex) {
                    case 0: ?>
                        <p>La empresa <strong><?= esc($companyName) ?></strong><?= $cifText ?> es una entidad destacada<?= $statusPhrase ?> con sede principal y domicilio social registrado en <?= $provText ?>. Su trayectoria mercantil<?= $yearPhrase ?><?= $cnaePhrase ?> la convierten en un agente económico relevante en su zona geográfica de operaciones, cumpliendo con todas las normativas exigidas para el desarrollo de su objeto social.</p>
                        <?php break;
                    case 1: ?>
                        <p>Con instalaciones principales ubicadas en la provincia de <?= $provText ?>, <strong><?= esc($companyName) ?></strong><?= $cifText ?> desarrolla sus operaciones comerciales y empresariales<?= $cnaePhrase ?>. La información depositada en los registros oficiales subraya la evolución de esta sociedad mercantil<?= $yearPhrase ?>, perfilando su actividad como parte integral del desarrollo económico nacional.</p>
                        <?php break;
                    case 2: ?>
                        <p>El perfil comercial de <strong><?= esc($companyName) ?></strong><?= $cifText ?> indica que la sociedad está establecida legalmente en <?= $provText ?>. A través de su estructura organizativa<?= $cnaePhrase ?>, la empresa participa dinámicamente en el mercado mercantil español<?= $yearPhrase ?>, manteniendo sus obligaciones societarias al día e impulsando su desarrollo corporativo.</p>
                        <?php break;
                    case 3: ?>
                        <p>Operando activamente<?= $yearPhrase ?> desde su sede en <?= $provText ?>, <strong><?= esc($companyName) ?></strong><?= $cifText ?> se ha consolidado como un participante recurrente<?= $cnaePhrase ?>. Las métricas de su actividad y su información corporativa reflejan a una firma comprometida con su entorno comercial, generando valor a través de los servicios inherentes a su actividad principal.</p>
                        <?php break;
                    case 4: ?>
                        <p>Registrada oficialmente en <?= $provText ?>, la organización <strong><?= esc($companyName) ?></strong><?= $cifText ?> ejerce sus funciones mercantiles<?= $statusPhrase ?> de acuerdo a sus estatutos corporativos. Su presencia continua en España<?= $yearPhrase ?><?= $cnaePhrase ?> demuestra su solidez, estableciendo relaciones comerciales sostenidas y garantizando el cumplimiento normativo.</p>
                        <?php break;
                    case 5: ?>
                        <p>La información mercantil de <strong><?= esc($companyName) ?></strong><?= $cifText ?> confirma que su sede administrativa y fiscal se encuentra en <?= $provText ?>. Al analizar su actividad comercial<?= $cnaePhrase ?>, se evidencia que la sociedad mantiene un flujo de operaciones constante<?= $yearPhrase ?>, adaptándose a las exigencias regulatorias y manteniendo su estructura plenamente operativa.</p>
                        <?php break;
                    case 6: ?>
                        <p>Como sociedad mercantil con domicilio en <?= $provText ?>, <strong><?= esc($companyName) ?></strong><?= $cifText ?> lleva a cabo diversas actividades empresariales que contribuyen al ecosistema corporativo local. Especializada<?= $cnaePhrase ?>, la empresa ha destinado sus recursos a la consecución de sus fines comerciales<?= $yearPhrase ?>, manteniendo la transparencia en sus registros oficiales.</p>
                        <?php break;
                    case 7: ?>
                        <p>Establecida en el territorio de <?= $provText ?>, la firma <strong><?= esc($companyName) ?></strong><?= $cifText ?> mantiene sus registros vigentes y participa activamente en la dinamización de la economía española. Sus operaciones<?= $cnaePhrase ?> están avaladas por su correcto desempeño societario<?= $yearPhrase ?>, lo que le permite afianzarse en su nicho estratégico de mercado.</p>
                        <?php break;
                    case 8: ?>
                        <p>Cumpliendo con los rigurosos requisitos de inscripción legal, <strong><?= esc($companyName) ?></strong><?= $cifText ?> opera desde su sede en <?= $provText ?> y fomenta su actividad corporativa a través de una sólida estructura. Sus procesos comerciales<?= $cnaePhrase ?>, desarrollados de forma continua<?= $yearPhrase ?>, la convierten en un exponente fundamental dentro de su categoría empresarial.</p>
                        <?php break;
                    case 9: ?>
                        <p>Al estudiar el impacto empresarial de <strong><?= esc($companyName) ?></strong><?= $cifText ?>, destaca su sólida implantación en la provincia de <?= $provText ?> y su especialización funcional<?= $cnaePhrase ?>. La trazabilidad de su historia mercantil<?= $yearPhrase ?> refleja una evolución acorde a las exigencias actuales del entorno de los negocios en España, operando<?= $statusPhrase ?> con alto grado de consistencia.</p>
                        <?php break;
                } ?>
            </div>
            <div id="ai-seo-container" style="position:relative; min-height:80px;">
                <div class="ai-skeleton" style="display:flex;flex-direction:column;gap:10px;">
                    <div style="height:16px;background:#e2e8f0;border-radius:4px;width:100%;animation:pulse 2s infinite;"></div>
                    <div style="height:16px;background:#e2e8f0;border-radius:4px;width:90%;animation:pulse 2s infinite;"></div>
                    <div style="height:16px;background:#e2e8f0;border-radius:4px;width:95%;animation:pulse 2s infinite;"></div>
                </div>
                <div style="display:flex;align-items:center;gap:6px;margin-top:10px;color:#94a3b8;font-size:0.85rem;">
                    <svg class="spin-icon" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-38e7a7c3"></use></svg>
                    Analizando trayectoria de la empresa con IA...
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer: badges izq · share dcha -->
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;padding:12px 24px;border-top:1px solid #f1f5f9;background:#f8fafc;border-radius:0 0 16px 16px;">
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <span class="badge-demo badge-verified">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-295390ab"></use></svg>
                Datos Verificados
            </span>
            <span class="badge-demo badge-official">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-f57c0d27"></use></svg>
                Fuente Oficial
            </span>
        </div>
        <div class="share-buttons" style="display:flex;gap:8px;align-items:center;">
            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($canonical) ?>"
                target="_blank" rel="noopener noreferrer nofollow"
                class="share-btn share-linkedin" title="Compartir en LinkedIn">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><use href="#icon-7434dd57"></use></svg>
            </a>
            <a href="https://api.whatsapp.com/send?text=<?= urlencode("Mira esta empresa: " . $canonical) ?>"
                target="_blank" rel="noopener noreferrer nofollow"
                class="share-btn share-whatsapp" title="Compartir en WhatsApp">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><use href="#icon-f0dba875"></use></svg>
            </a>
        </div>
    </div>

</div>
                        <nav class="b2b-tabs" aria-label="Índice de contenidos"
                            style="border: none; box-shadow: none; background: transparent; padding-left: 0; padding-right: 0;">
                            <ul>
                                <li><a href="#datos-generales" class="active">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2"><use href="#icon-d557d894"></use></svg>
                                        Datos Generales
                                    </a></li>
                                <?php if ((!empty($company['lat']) && !empty($company['lng'])) || !empty($company['address'])): ?>
                                    <li><a href="#map-area">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2"><use href="#icon-7dfeea20"></use></svg>
                                            Ubicación
                                        </a></li>
                                <?php endif; ?>
                                <?php if (!empty($administrators)): ?>
                                    <li><a href="#administradores">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2"><use href="#icon-6ba5abb4"></use></svg>
                                            Cargos
                                        </a></li>
                                <?php endif; ?>
                                <?php if (!empty($bormePosts)): ?>
                                    <li><a href="#borme">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2"><use href="#icon-82d4f2aa"></use></svg>
                                            BORME
                                        </a></li>
                                <?php endif; ?>
                                <?php if (!empty($contracts) || !empty($subsidies)): ?>
                                    <li><a href="#financial-data">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2"><use href="#icon-d557d894"></use></svg>
                                            Finanzas Públicas
                                        </a></li>
                                <?php endif; ?>
                                <li><a href="#preguntas-frecuentes">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                            stroke="currentColor" stroke-width="2"><use href="#icon-89746002"></use></svg>
                                        FAQs
                                    </a></li>
                                <?php if (!empty($related)): ?>
                                    <li><a href="#empresas-relacionadas">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2"><use href="#icon-f15f4088"></use></svg>
                                            Relacionadas
                                        </a></li>
                                <?php endif; ?>
                                <div id="nav-descargar-csv"><li><a href="#descargar-excel">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-b92ca97e"></use></svg>
                                            Descargar CSV
                                        </a></li></div>
                                <li><a href="#api-dev-section">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-7cb36ec4"></use></svg>
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
                                                stroke-linejoin="round"><use href="#icon-d557d894"></use></svg>
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
                                                style="color: #64748b; opacity: 0.7; transition: opacity 0.2s;"><use href="#icon-3f345ac5"></use></svg>
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
                                                stroke-linejoin="round"><use href="#icon-f673ba92"></use></svg>
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
                                                stroke-linejoin="round"><use href="#icon-4ffe048e"></use></svg>
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
                                                stroke-linejoin="round"><use href="#icon-bb290277"></use></svg>
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
                                                stroke-linejoin="round"><use href="#icon-5f77d3d2"></use></svg>
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
                                                    stroke-linejoin="round"><use href="#icon-5f77d3d2"></use></svg>
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
                                                stroke-linejoin="round"><use href="#icon-7dfeea20"></use></svg>
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
                                                    stroke-linejoin="round"><use href="#icon-b34d1501"></use></svg>
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
                                                stroke-linejoin="round"><use href="#icon-7418cdbd"></use></svg>
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
                                                stroke-linejoin="round"><use href="#icon-82d4f2aa"></use></svg>
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
                                        stroke-width="2.5"><use href="#icon-7dfeea20"></use></svg>
                                    Ubicación
                                </div>
                                <div id="company-map"></div>
                            </div>
                        <?php endif; ?>
                    </div> <!-- /b2b-grid-2col -->

                    <!-- RISK PROFILE SECTION -->
                    <?php if (!empty($riskProfile)): ?>
                        <div class="b2b-card" style="margin-bottom:24px; padding: 0; overflow: hidden; position: relative;">
                            
                            <!-- HEADER -->
                            <div style="padding: 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: flex-start; background: #fff;">
                                <div>
                                    <h2 class="no-after-line" style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0 0 4px 0; display: flex; align-items: center; gap: 10px; text-transform: uppercase;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"></polyline></svg>
                                        Índice de Estabilidad Societaria
                                    </h2>
                                    <p style="margin: 0 0 12px 34px; font-size: 0.9rem; color: #64748b;">Análisis algorítmico basado en registros del BORME, contratos públicos y subvenciones.</p>
                                    <div style="margin-left: 34px; width: 60px; height: 3px; background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%); border-radius: 2px;"></div>
                                </div>
                            </div>

                            <div id="risk-profile-container" style="padding: 24px; position: relative; background: #fff; min-height: 260px;">
                                    <!-- TEASER PARA USUARIOS PÚBLICOS -->
                                    <div style="padding: 20px; position: relative; display: flex; align-items: center; justify-content: center; min-height: 480px; overflow: hidden; background: #fafafa; margin: -24px; margin-bottom: 0;">
                                        <!-- BLURRED BACKGROUND (Absolute, behind the text) -->
                                        <div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; filter: blur(6px); opacity: 0.6; pointer-events: none; display: flex; flex-wrap: nowrap; gap: 100px; align-items: center; justify-content: center; padding: 24px;">
                                            <!-- Fake Score Circle -->
                                            <div style="display: flex; flex-direction: column; align-items: center; justify-content: center; min-width: 180px; transform: translateX(-40px) scale(1.15);">
                                                <div style="width: 150px; height: 150px; border-radius: 50%; border: 12px solid #f87171; display: flex; align-items: center; justify-content: center; margin-bottom: 24px; background: #fff; box-shadow: 0 10px 25px rgba(239, 68, 68, 0.2);">
                                                    <svg width="56" height="56" viewBox="0 0 24 24" fill="none" stroke="#334155" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                                </div>
                                                <span style="background: #f87171; color: #fff; padding: 8px 20px; border-radius: 999px; font-size: 1rem; font-weight: 800; letter-spacing: 1px;">
                                                    ACCESO RESTRINGIDO
                                                </span>
                                            </div>

                                            <!-- Fake Factors -->
                                            <div style="flex: 1; min-width: 340px; max-width: 500px; transform: translateX(40px) scale(1.1);">
                                                <div style="display: flex; flex-direction: column; gap: 20px;">
                                                    <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; display: flex; gap: 20px; align-items: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                                                        <div style="width: 56px; height: 56px; border-radius: 50%; background: #eff6ff; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                                        </div>
                                                        <div style="width: 100%; margin-top: 4px;">
                                                            <div style="width: 80%; height: 16px; background: #cbd5e1; margin-bottom: 12px; border-radius: 4px;"></div>
                                                            <div style="width: 50%; height: 10px; background: #22c55e; border-radius: 4px;"></div>
                                                        </div>
                                                    </div>
                                                    <div style="border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; display: flex; gap: 20px; align-items: center; background: #fff; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
                                                        <div style="width: 100%; margin-top: 4px;">
                                                            <div style="width: 65%; height: 16px; background: #cbd5e1; margin-bottom: 12px; border-radius: 4px;"></div>
                                                            <div style="width: 90%; height: 10px; background: #e2e8f0; border-radius: 4px;"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- FOREGROUND CONTENT (The Box) -->
                                        <div style="position: relative; z-index: 10; display: flex; flex-direction: column; align-items: center; text-align: center; width: 540px; background: #fff; padding: 48px; border-radius: 20px; box-shadow: 0 20px 40px rgba(0, 0, 0, 0.08); border: 1px solid #f1f5f9;">
                                            <div style="background: #eff6ff; color: #3b82f6; padding: 18px; border-radius: 50%; margin-bottom: 24px;">
                                                <svg width="36" height="36" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                            </div>
                                            <h3 style="font-size: 1.7rem; font-weight: 800; color: #0f172a; margin-bottom: 16px; margin-top: 0; letter-spacing: -0.5px;">Contenido Protegido</h3>
                                            <p style="color: #334155; margin-bottom: 32px; margin-top: 0; font-size: 1.05rem; line-height: 1.6; font-weight: 500; max-width: 420px;">Descubre si esta empresa tiene <strong style="color: #ef4444;">riesgos ocultos</strong> en el BORME o deudas. Crea tu cuenta para ver el análisis completo.</p>
                                            
                                            <!-- Feature Highlights -->
                                            <div style="background: #f8fafc; border: 1px solid #f1f5f9; border-radius: 12px; padding: 20px 16px; display: flex; justify-content: space-between; align-items: center; width: 100%; margin-bottom: 36px; box-sizing: border-box;">
                                                <div style="display: flex; gap: 10px; align-items: center; flex: 1; justify-content: center;">
                                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="2" style="flex-shrink: 0;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"></polyline></svg>
                                                    <span style="font-size: 0.8rem; color: #475569; text-align: left; line-height: 1.3;">Datos oficiales<br>del BORME</span>
                                                </div>
                                                <div style="width: 1px; height: 36px; background: #e2e8f0;"></div>
                                                <div style="display: flex; gap: 10px; align-items: center; flex: 1; justify-content: center;">
                                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="2" style="flex-shrink: 0;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                                                    <span style="font-size: 0.8rem; color: #475569; text-align: left; line-height: 1.3;">Análisis completo<br>y detallado</span>
                                                </div>
                                                <div style="width: 1px; height: 36px; background: #e2e8f0;"></div>
                                                <div style="display: flex; gap: 10px; align-items: center; flex: 1; justify-content: center;">
                                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#059669" stroke-width="2" style="flex-shrink: 0;"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                                                    <span style="font-size: 0.8rem; color: #475569; text-align: left; line-height: 1.3;">Actualizado<br>continuamente</span>
                                                </div>
                                            </div>

                                            <?php 
                                            $slugVal = $company['slug'] ?? url_title($company['name'] ?? '', '-', true);
                                            $redirectPath = 'empresa/' . $company['id'] . '-' . $slugVal; 
                                            ?>
                                            <a href="<?= site_url('register') ?>?intent=view_risk_profile&redirect=<?= urlencode($redirectPath) ?>" style="background: #0f172a; color: #fff; padding: 18px 24px; border-radius: 12px; font-weight: 800; text-decoration: none; font-size: 1.15rem; display: flex; align-items: center; justify-content: center; gap: 12px; width: 100%; box-sizing: border-box; transition: background 0.2s;" onmouseover="this.style.background='#1e293b';" onmouseout="this.style.background='#0f172a';">
                                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="20" x2="18" y2="10"></line><line x1="12" y1="20" x2="12" y2="4"></line><line x1="6" y1="20" x2="6" y2="14"></line></svg>
                                                Ver Análisis Gratis
                                            </a>
                                            
                                            <div style="display: flex; align-items: center; gap: 6px; margin-top: 20px; color: #64748b; font-size: 0.85rem; font-weight: 500;">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                                                Sin tarjeta de crédito &bull; Cancelación en 1 clic
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            <!-- FOOTER / DISCLAIMER -->
                            <div style="padding: 24px 32px; border-top: 1px solid #e2e8f0; background: #f8fafc; display: flex; gap: 16px; align-items: center;">
                                <div style="width: 42px; height: 42px; border-radius: 50%; background: #fff; border: 1px solid #e2e8f0; display: flex; align-items: center; justify-content: center; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.03); color: #64748b;">
                                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                      <path d="M12 3v18"></path>
                                      <path d="M3 9h18"></path>
                                      <path d="M5 9l-2 6a3 3 0 0 0 6 0l-2-6"></path>
                                      <path d="M19 9l-2 6a3 3 0 0 0 6 0l-2-6"></path>
                                      <path d="M9 3h6"></path>
                                    </svg>
                                </div>
                                <p style="margin: 0; font-size: 0.85rem; color: #64748b; line-height: 1.5; font-weight: 500;">
                                    <strong style="color: #334155; margin-right: 4px;">Aviso Legal:</strong> Este índice es una estimación automática generada a partir de información pública y no constituye asesoramiento financiero, jurídico ni una evaluación crediticia oficial.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                    <!-- END RISK PROFILE SECTION -->


                    <div style="margin-bottom:24px;">
                        <div style="display:grid; grid-template-columns:1fr 1fr; gap:24px; align-items:start;">
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

                            <!-- CTA LOOKALIKE (Sidebar) -->
                            <a href="<?= site_url('encontrar-empresas-similares') ?>" rel="noopener noreferrer" style="display: block; background: linear-gradient(135deg, #1e3a8a 0%, #0f172a 100%); border-radius: 12px; padding: 1.5rem 1.25rem; text-decoration: none; position: relative; overflow: hidden; border: 1px solid rgba(255, 255, 255, 0.1); box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.5); transition: transform 0.2s, box-shadow 0.2s;"
                               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 15px 30px -5px rgba(15, 23, 42, 0.6)';"
                               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 10px 25px -5px rgba(15, 23, 42, 0.5)';"
                               onclick="if(window.trackEvent) trackEvent('click_lookalike_banner', { source: 'company_sidebar' });">
                                
                                <!-- Decorative element -->
                                <div style="position: absolute; top: -20px; right: -20px; width: 80px; height: 80px; background: rgba(37, 99, 235, 0.2); filter: blur(30px); border-radius: 50%;"></div>
                                
                                <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 0.85rem; position: relative; z-index: 1;">
                                    <span style="background: #22c55e; color: #ffffff; font-size: 0.7rem; font-weight: 800; padding: 4px 8px; border-radius: 6px; letter-spacing: 0.5px; text-transform: uppercase; box-shadow: 0 2px 5px rgba(34, 197, 94, 0.3);">
                                        ¡NUEVO!
                                    </span>
                                </div>
                                
                                <p style="color: #f8fafc; font-size: 0.95rem; line-height: 1.5; margin: 0 0 1.25rem 0; font-weight: 600; position: relative; z-index: 1;">
                                    ¿Tienes una lista con tus mejores clientes? Súbela y nuestra IA encontrará miles de <strong>empresas gemelas</strong> por toda España.
                                </p>

                                <div style="display: flex; align-items: center; justify-content: center; width: 100%; background: linear-gradient(to right, #fde047, #f97316); color: #1e293b; font-weight: 800; font-size: 1rem; padding: 12px 0; border-radius: 8px; box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3); transition: all 0.2s; position: relative; z-index: 1;">
                                    Subir mis clientes 🧬
                                </div>
                            </a>


                        </div> <!-- /reviews-banner -->

                        </div> <!-- /reviews-banner-row -->

                    <!-- SECCIÓN DE ADMINISTRADORES Y CARGOS -->
                    <?php if (!empty($administrators)): ?>
                        <div id="administradores" class="reveal-on-scroll" style="margin-top: 4rem;">
                            
                            <?php if (!empty($holdingData) && !empty($holdingGraphData)): ?>
                                <!-- Sección del Holding (Mapa de Poder) -->
                                <div style="margin-bottom: 4rem;">
                                    
                                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 2rem;">
                                        <div>
                                            <h2 class="no-after-line" style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin: 0 0 0.5rem 0; display: flex; align-items: center; gap: 12px;">
                                                <span style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff; padding: 8px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(15, 23, 42, 0.2);">
                                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="18" cy="5" r="3"></circle><circle cx="6" cy="12" r="3"></circle><circle cx="18" cy="19" r="3"></circle><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"></line><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"></line></svg>
                                                </span>
                                                Grupo Empresarial: <?= esc($holdingData['name']) ?>
                                            </h2>
                                            <p style="color: #64748b; font-size: 0.95rem; margin: 0;">
                                                Esta empresa forma parte de un ecosistema corporativo de <strong><?= number_format($totalHoldingCompaniesCount, 0, ',', '.') ?> empresas</strong> conectadas entre sí.
                                            </p>
                                        </div>
                                        <div>
                                            <button id="btn-show-graph" onclick="toggleHoldingGraph()" style="background: #4F46E5; color: white; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#4338ca'" onmouseout="this.style.background='#4F46E5'">
                                                <span id="btn-graph-text"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: text-bottom; margin-right: 6px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>Explorar Mapa de Poder</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Tabla Premium (Top Empresas Hermanas) -->
                                    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05); overflow: hidden;">
                                        <div style="overflow-x: auto;">
                                            <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                                                <thead style="background: #f8fafc;">
                                                    <tr style="border-bottom: 1px solid #e2e8f0;">
                                                        <th style="text-align: left; padding: 14px 20px; color: #475569; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">Empresa</th>
                                                        <th style="text-align: left; padding: 14px 20px; color: #475569; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">CIF</th>
                                                        <th style="text-align: left; padding: 14px 20px; color: #475569; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">Provincia</th>
                                                        <th style="text-align: right; padding: 14px 20px; color: #475569; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.05em;">Estado</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php 
                                                    $limit = 5;
                                                    $counter = 0;
                                                    foreach ($holdingCompanies as $hc): 
                                                        $isCurrent = ($hc['id'] == $company['id']);
                                                        $counter++;
                                                        $isHidden = $counter > $limit;
                                                    ?>
                                                    <tr class="<?= $isHidden ? 'holding-hidden-row' : '' ?>" style="<?= $isHidden ? 'display: none;' : '' ?> border-bottom: 1px solid #f1f5f9; transition: background 0.2s; <?= $isCurrent ? 'background: #eff6ff;' : '' ?>" onmouseover="this.style.background='<?= $isCurrent ? '#eff6ff' : '#f8fafc' ?>'" onmouseout="this.style.background='<?= $isCurrent ? '#eff6ff' : 'transparent' ?>'">
                                                        <td style="padding: 14px 20px; color: #0f172a; font-weight: <?= $isCurrent ? '700' : '500' ?>; font-size: 0.95rem;">
                                                            <?php if(!$isCurrent): ?><a href="<?= company_url($hc) ?>" style="color: inherit; text-decoration: none;"><?php endif; ?>
                                                            <?= esc($hc['name']) ?> <?= $isCurrent ? '<span style="font-size: 0.7rem; background:#3b82f6; color:#ffffff; padding:2px 8px; border-radius:12px; margin-left:8px; font-weight:600; letter-spacing: 0.02em;">ACTUAL</span>' : '' ?>
                                                            <?php if(!$isCurrent): ?></a><?php endif; ?>
                                                        </td>
                                                        <td style="padding: 14px 20px; color: #64748b; font-family: 'Courier New', Courier, monospace; font-size: 0.9rem;"><?= esc($hc['cif']) ?></td>
                                                        <?php
                                                        $statusLower = strtolower($hc['status'] ?? '');
                                                        $isActiva = ($statusLower === 'activa' || $statusLower === 'activo');
                                                        $estadoColor = $isActiva ? '#16a34a' : '#64748b';
                                                        $estadoBg = $isActiva ? '#dcfce7' : '#f1f5f9';
                                                        ?>
                                                        <td style="padding: 14px 20px; color: #475569; font-size: 0.95rem; text-transform: capitalize;"><?= esc(strtolower($hc['province'] ?? '')) ?: '-' ?></td>
                                                        <td style="padding: 14px 20px; text-align: right;">
                                                            <span style="font-size: 0.75rem; background: <?= $estadoBg ?>; color: <?= $estadoColor ?>; padding: 4px 10px; border-radius: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.03em;">
                                                                <?= esc($hc['status']) ?: 'Desconocido' ?>
                                                            </span>
                                                        </td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                        
                                        <?php if($totalHoldingCompaniesCount > 5): ?>
                                            <div style="background: #f8fafc; padding: 12px 20px; text-align: center; border-top: 1px solid #e2e8f0;">
                                                <?php $remainingCount = $totalHoldingCompaniesCount - 5; ?>
                                                <button id="btn-toggle-table" onclick="toggleHoldingTable()" style="background: none; border: none; color: #4f46e5; font-weight: 600; font-size: 0.9rem; cursor: pointer; text-decoration: underline;">
                                                    Ver las otras <?= number_format($remainingCount, 0, ',', '.') ?> empresas hermanas más en este grupo
                                                </button>
                                                <?php if($totalHoldingCompaniesCount > 100): ?>
                                                    <div id="holding-limit-notice" style="display: none; color: #64748b; font-size: 0.85rem; margin-top: 8px;">
                                                        (Mostrando el top 100 de empresas por relevancia de capital social para optimizar el rendimiento)
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Graph Wrapper (Hidden by default) -->
                                    <div id="holding-graph-wrapper" style="display: none; margin-top: 2rem; border-top: 1px solid #e2e8f0; padding-top: 1.5rem;">
                                        <div id="holding-network" style="width: 100%; height: 500px; background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1;"></div>
                                        
                                        <script src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
                                        <script>
                                            let holdingNetwork = null;
                                            function toggleHoldingGraph() {
                                                const wrapper = document.getElementById('holding-graph-wrapper');
                                                const btnText = document.getElementById('btn-graph-text');
                                                
                                                if (wrapper.style.display === 'none' || wrapper.style.display === '') {
                                                    wrapper.style.display = 'block';
                                                    btnText.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: text-bottom; margin-right: 6px;"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>Ocultar Mapa de Poder';
                                                    
                                                    if (!holdingNetwork) {
                                                        const container = document.getElementById('holding-network');
                                                        const rawData = <?= json_encode($holdingGraphData) ?>;
                                                    
                                                    const data = {
                                                        nodes: new vis.DataSet(rawData.nodes),
                                                        edges: new vis.DataSet(rawData.edges)
                                                    };
                                                    
                                                    const options = {
                                                        nodes: {
                                                            borderWidth: 2,
                                                            borderWidthSelected: 4,
                                                        },
                                                        edges: {
                                                            width: 1,
                                                            smooth: {
                                                                type: 'continuous'
                                                            }
                                                        },
                                                        physics: {
                                                            barnesHut: {
                                                                gravitationalConstant: -4000,
                                                                centralGravity: 0.1,
                                                                springLength: 250,
                                                                damping: 0.09
                                                            },
                                                            stabilization: {
                                                                iterations: 150
                                                            }
                                                        },
                                                        interaction: {
                                                            hover: true,
                                                            tooltipDelay: 200,
                                                            zoomView: true,
                                                            dragView: true
                                                        }
                                                    };
                                                    
                                                    holdingNetwork = new vis.Network(container, data, options);
                                                    
                                                    holdingNetwork.on("selectNode", function (params) {
                                                        if (params.nodes.length == 1) {
                                                            var nodeId = params.nodes[0];
                                                            var node = data.nodes.get(nodeId);
                                                            if(node.url) {
                                                                window.location.href = node.url;
                                                            }
                                                        }
                                                    });
                                                }
                                            } else {
                                                wrapper.style.display = 'none';
                                                btnText.innerHTML = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: text-bottom; margin-right: 6px;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>Explorar Mapa de Poder';
                                            }
                                        }

                                        function toggleHoldingTable() {
                                            const rows = document.querySelectorAll('.holding-hidden-row');
                                            const btn = document.getElementById('btn-toggle-table');
                                            const notice = document.getElementById('holding-limit-notice');
                                            
                                            if (rows.length === 0) return;
                                            const isHidden = rows[0].style.display === 'none' || rows[0].style.display === '';
                                            
                                            if (isHidden) {
                                                rows.forEach(el => el.style.display = 'table-row');
                                                btn.innerHTML = 'Ocultar filiales y contraer tabla';
                                                if(notice) notice.style.display = 'block';
                                            } else {
                                                rows.forEach(el => el.style.display = 'none');
                                                btn.innerHTML = 'Ver las otras <?= number_format($remainingCount ?? 0, 0, ',', '.') ?> empresas hermanas más en este grupo';
                                                if(notice) notice.style.display = 'none';
                                            }
                                        }
                                        </script>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <h2 id="administradores" class="no-after-line"
                                style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-bottom: 2rem; display: flex; align-items: center; gap: 12px;">
                                <span
                                    style="background: linear-gradient(135deg, #6366f1 0%, #4338ca 100%); color: #fff; padding: 8px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(99, 102, 241, 0.2);">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                        stroke-width="2.5"><use href="#icon-6ba5abb4"></use></svg>
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
                                                stroke-width="2"><use href="#icon-f4d6250e"></use></svg>
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
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-7cb36ec4"></use></svg>
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
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" style="margin-right: 8px;"><use href="#icon-b6af18a8"></use></svg>
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
                                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#2563eb" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-d557d894"></use></svg>
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
                                                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-e0e8064d"></use></svg>
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

                                <?php $hideApiBulkCta = true; ?>
                                <?php if (!$hideApiBulkCta): ?>
                                <!-- API CTA Developer-First -->
                                <div style="margin-top: 32px; background: #0f172a; border-radius: 16px; padding: 24px; color: #f8fafc; display: flex; flex-direction: column; gap: 16px; overflow: hidden; position: relative;">
                                    <div style="position: absolute; top: 0; right: 0; padding: 24px; opacity: 0.05; pointer-events: none;">
                                        <svg width="120" height="120" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-7cb36ec4"></use></svg>
                                    </div>
                                    <div style="position: relative; z-index: 1;">
                                        <h4 style="margin: 0 0 8px 0; font-size: 1.15rem; font-weight: 700; color: #fff; display: flex; align-items: center; gap: 8px;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-7a52b6a3"></use></svg>
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
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-50a1a750"></use></svg>
                                        </a>
                                    </div>
                                </div>
                                <?php endif; ?>
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
                                        stroke-width="2.5"><use href="#icon-82d4f2aa"></use></svg>
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
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-ca9ebed9"></use></svg>
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
                                    $iconSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><use href="#icon-b589a974"></use></svg>';

                                    if (strpos($acts, 'nombramientos') !== false) {
                                        $iconColor = '#16a34a'; // Green 600
                                        $iconBg = '#dcfce7'; // Green 100
                                        // Briefcase Icon
                                        $iconSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><use href="#icon-b6660207"></use></svg>';
                                    } elseif (strpos($acts, 'ceses') !== false || strpos($acts, 'dimisiones') !== false || strpos($acts, 'revocaciones') !== false) {
                                        $iconColor = '#dc2626'; // Red 600
                                        $iconBg = '#fee2e2'; // Red 100
                                        // File Minus/Remove Icon
                                        $iconSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><use href="#icon-78f96eee"></use></svg>';
                                    } elseif (strpos($acts, 'cuentas') !== false) {
                                        $iconColor = '#2563eb'; // Blue 600
                                        $iconBg = '#dbeafe'; // Blue 100
                                        $iconSvg = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><use href="#icon-ca328ae4"></use></svg>';
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
                                                            stroke="currentColor" stroke-width="2"><use href="#icon-867f4a3d"></use></svg>
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
                    <aside id="descargar-excel" class="seo-cta-banner"
                            style="margin-top: 3rem; background: linear-gradient(135deg, #ffffff 0%, #f4f7fb 100%); border: 1px solid rgba(59, 130, 246, 0.2); border-radius: 20px; padding: 3.5rem 2rem; display: flex; flex-direction: column; align-items: center; text-align: center; box-shadow: 0 10px 40px -10px rgba(37, 99, 235, 0.1), 0 1px 3px rgba(0,0,0,0.03); position: relative; overflow: hidden;">
                            
                            <!-- Decorative background elements -->
                            <div style="position: absolute; top: -50%; left: -10%; width: 50%; height: 200%; background: radial-gradient(circle, rgba(59, 130, 246, 0.05) 0%, transparent 60%); transform: rotate(30deg); pointer-events: none;"></div>
                            <div style="position: absolute; bottom: -50%; right: -10%; width: 50%; height: 200%; background: radial-gradient(circle, rgba(99, 102, 241, 0.05) 0%, transparent 60%); transform: rotate(-30deg); pointer-events: none;"></div>

                            <div
                                style="position: relative; z-index: 1; background: #eff6ff; border: 1px solid #bfdbfe; color: #1d4ed8; padding: 6px 16px; border-radius: 99px; font-size: 0.8rem; font-weight: 800; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.1em; display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 10px rgba(59, 130, 246, 0.1);">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-b92ca97e"></use></svg>
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
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-6b651f08"></use></svg>
                                Descargar CSV completo por <?php if(isset($pricing) && $pricing['is_discounted']): ?><s style="opacity:0.7; font-size:0.9em; margin-right:6px;"><?= number_format($pricing['original_price'], 2, ',', '') ?>€</s><?php endif; ?><?= $priceStr ?>€ <span style="font-size:0.85em; opacity:0.85; font-weight:600;">+ IVA</span>
                            </a>
                            
                            <div style="position: relative; z-index: 1; margin-top: 2rem; display: flex; flex-wrap: wrap; justify-content: center; align-items: center; gap: 20px; color: #64748b; font-size: 0.9rem; font-weight: 500;">
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-4d8be45b"></use></svg> Descarga Inmediata
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-4d8be45b"></use></svg> Formato CSV (Delimitado por comas)
                                </div>
                                <div style="display: flex; align-items: center; gap: 6px;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#22c55e" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-4d8be45b"></use></svg> Datos Verificados
                                </div>
                            </div>
                        </aside>

                    <!-- FAQ Section HTML -->
                    <!-- FAQ Section HTML -->
                    <div class="b2b-grid-content-aside" style="margin-top: 2rem;">
                        <div id="preguntas-frecuentes" style="padding: 1rem 1rem 1rem 0;">
                            <h3 style="display: flex; align-items: center; gap: 8px; font-size: 1.25rem; font-weight: 700; margin-bottom: 1.5rem; color: var(--b2b-text);">
                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--b2b-primary);"><use href="#icon-89746002"></use></svg>
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
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--b2b-primary);"><use href="#icon-f15f4088"></use></svg>
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
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-14dff1fc"></use></svg>
                                                </div>
                                                <span class="rel-text" style="font-weight: 600; color: var(--b2b-text); font-size: 0.9rem; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; max-width: 100%; transition: color 0.2s; display: block;">
                                                    <?= $name ?>
                                                </span>
                                            </div>
                                            
                                            <div class="rel-arrow" style="color: #cbd5e1; flex-shrink: 0; margin-left: 0.5rem; display: flex; align-items: center; transition: color 0.2s;">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-4677987d"></use></svg>
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
                            <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--b2b-primary);"><use href="#icon-b7ecf518"></use></svg>
                            Explorar más empresas
                        </h3>

                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 1.5rem;">
                            <?php if ($secoProvStr): ?>
                                <a href="<?= site_url('empresas/' . url_title($secoProvStr, '-', true)) ?>"
                                   style="display: flex; flex-direction: column; padding: 1.5rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 16px; text-decoration: none; transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); box-shadow: 0 2px 4px rgba(0,0,0,0.02);"
                                   onmouseover="this.style.boxShadow='0 12px 24px rgba(0,0,0,0.08)'; this.style.borderColor='var(--b2b-primary-light)'; this.style.transform='translateY(-4px)';"
                                   onmouseout="this.style.boxShadow='0 2px 4px rgba(0,0,0,0.02)'; this.style.borderColor='#e2e8f0'; this.style.transform='translateY(0)';">
                                   
                                   <div style="width: 48px; height: 48px; border-radius: 12px; background: #f0f9ff; color: #0ea5e9; display: flex; align-items: center; justify-content: center; margin-bottom: 1rem;">
                                       <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-7dfeea20"></use></svg>
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
                                       <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-c2db4441"></use></svg>
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
                                       <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-86d3c736"></use></svg>
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
                                       <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-7a52b6a3"></use></svg>
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
                                        stroke-width="2.5"><use href="#icon-1ba97933"></use></svg>
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
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-ee4ea388"></use></svg>
            </button>
            
            <div style="width: 56px; height: 56px; background: #eff6ff; border-radius: 16px; display: flex; align-items: center; justify-content: center; color: #2563eb; margin-bottom: 24px;">
                <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><use href="#icon-2f243988"></use></svg>
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
    <!-- Whitelabel Modal -->
    <div id="whitelabel-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); z-index: 10000; align-items: center; justify-content: center; padding: 20px;">
        <div style="background: #ffffff; border-radius: 24px; width: 100%; max-width: 850px; padding: 0; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3); position: relative; overflow-y: auto; max-height: 90vh; display: flex; flex-wrap: wrap;">
            
            <button onclick="document.getElementById('whitelabel-modal').style.display='none';" style="position: absolute; top: 15px; right: 15px; background: #f1f5f9; border: none; color: #64748b; cursor: pointer; padding: 8px; border-radius: 50%; transition: all 0.2s; z-index: 10;" onmouseover="this.style.background='#e2e8f0'; this.style.color='#0f172a'">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-ee4ea388"></use></svg>
            </button>
            
            <!-- Left Side: Features & Preview Info -->
            <div style="flex: 1 1 350px; background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%); padding: 40px; border-radius: 24px 0 0 24px; border-right: 1px solid #e2e8f0;">
                <h3 style="margin: 0 0 20px 0; font-size: 1.4rem; color: #ffffff; font-weight: 800;">¿Qué incluye el informe?</h3>
                <p style="color: #cbd5e1; margin-bottom: 25px; line-height: 1.5; font-size: 0.95rem;">
                    Obtén un dossier ejecutivo en PDF de 4 páginas de <strong style="color: #ffffff;"><?= esc($companyName) ?></strong>, con un diseño premium y la siguiente inteligencia comercial:
                </p>
                <ul style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 18px;">
                    <li style="display: flex; gap: 12px; align-items: flex-start;">
                        <span style="color: #f472b6; background: rgba(236, 72, 153, 0.2); padding: 8px; border-radius: 10px; display: inline-flex;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-7902b5e6"></use></svg></span>
                        <div>
                            <strong style="color: #ffffff; display: block; margin-bottom: 4px; font-size: 1rem;">Datos Generales y Contacto</strong>
                            <span style="color: #94a3b8; font-size: 0.9rem; line-height: 1.4; display: block;">Dirección completa, teléfonos, CNAE, provincia, municipio y datos de registro.</span>
                        </div>
                    </li>
                    <li style="display: flex; gap: 12px; align-items: flex-start;">
                        <span style="color: #34d399; background: rgba(16, 185, 129, 0.2); padding: 8px; border-radius: 10px; display: inline-flex;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-4d8be45b"></use></svg></span>
                        <div>
                            <strong style="color: #ffffff; display: block; margin-bottom: 4px; font-size: 1rem;">Diseño Espectacular</strong>
                            <span style="color: #94a3b8; font-size: 0.9rem; line-height: 1.4; display: block;">Portada premium, tipografías modernas y gráficas visuales. Todo adaptado a tus colores.</span>
                        </div>
                    </li>
                    <li style="display: flex; gap: 12px; align-items: flex-start;">
                        <span style="color: #60a5fa; background: rgba(59, 130, 246, 0.2); padding: 8px; border-radius: 10px; display: inline-flex;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-f17521fc"></use></svg></span>
                        <div>
                            <strong style="color: #ffffff; display: block; margin-bottom: 4px; font-size: 1rem;">Estructura y BORME</strong>
                            <span style="color: #94a3b8; font-size: 0.9rem; line-height: 1.4; display: block;">Línea temporal de actos registrales, balances y estado mercantil de la entidad.</span>
                        </div>
                    </li>
                    <li style="display: flex; gap: 12px; align-items: flex-start;">
                        <span style="color: #a78bfa; background: rgba(139, 92, 246, 0.2); padding: 8px; border-radius: 10px; display: inline-flex;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-cf56ec56"></use></svg></span>
                        <div>
                            <strong style="color: #ffffff; display: block; margin-bottom: 4px; font-size: 1rem;">Administradores y Cargos</strong>
                            <span style="color: #94a3b8; font-size: 0.9rem; line-height: 1.4; display: block;">Listado completo de órganos de gobierno actuales y directivos clave de la empresa.</span>
                        </div>
                    </li>
                    <li style="display: flex; gap: 12px; align-items: flex-start;">
                        <span style="color: #fbbf24; background: rgba(245, 158, 11, 0.2); padding: 8px; border-radius: 10px; display: inline-flex;"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><use href="#icon-b6660207"></use></svg></span>
                        <div>
                            <strong style="color: #ffffff; display: block; margin-bottom: 4px; font-size: 1rem;">Contratos y Subvenciones</strong>
                            <span style="color: #94a3b8; font-size: 0.9rem; line-height: 1.4; display: block;">Historial de adjudicaciones públicas y ayudas económicas (si las hubiera).</span>
                        </div>
                    </li>
                </ul>
            </div>

            <!-- Right Side: Personalization Form -->
            <div style="flex: 1 1 400px; padding: 40px;">
                <h3 style="margin: 0 0 20px 0; font-size: 1.5rem; color: #0f172a; font-weight: 800;">Personalizar PDF</h3>
                <p style="color: #475569; margin-bottom: 25px; line-height: 1.5;">Configura la Marca Blanca. Precio: <strong>3,90€ + IVA</strong></p>

                <div id="whitelabel-status"></div>

                <form id="whitelabel-form" enctype="multipart/form-data">
                <input type="hidden" name="company_id" value="<?= esc($company['id']) ?>">
                <?= csrf_field() ?>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Nombre de tu Agencia/Empresa <span style="color:red">*</span></label>
                    <input type="text" name="agency_name" required placeholder="Ej: Global Consultores" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Color Corporativo Principal</label>
                    <div style="display: flex; gap: 10px; align-items: center;">
                        <input type="color" name="brand_color" value="#0f172a" style="width: 50px; height: 40px; border: none; cursor: pointer; border-radius: 8px; padding: 0;">
                        <span style="color: #64748b; font-size: 0.9rem;">Se usará en títulos y gráficos.</span>
                    </div>
                </div>
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Logotipo (PNG/JPG)</label>
                    <input type="file" name="brand_logo" accept="image/png, image/jpeg" style="width: 100%; padding: 10px; border: 1px dashed #cbd5e1; border-radius: 8px; background: #f8fafc; font-size: 0.9rem;">
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Texto de pie de página</label>
                    <input type="text" name="footer_text" placeholder="Documento confidencial generado por Global Consultores" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem;">
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Enviar copia al correo electrónico</label>
                    <input type="email" name="email" placeholder="tu@email.com" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem;">
                </div>

                <button type="submit" id="btn-whitelabel-submit" style="width: 100%; padding: 14px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 1.1rem; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                    Pagar 3,90€ + IVA y Descargar
                </button>
            </form>
        </div>
    </div>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('whitelabel-form');
        if(form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                if(window.trackEvent) trackEvent('premium_pdf_checkout_started');
                const btnSubmit = document.getElementById('btn-whitelabel-submit');
                const statusArea = document.getElementById('whitelabel-status');
                
                btnSubmit.disabled = true;
                btnSubmit.innerHTML = 'Conectando con Stripe... ⏳';
                
                const formData = new FormData(this);
                
                fetch('<?= site_url("empresa/checkout-premium-pdf") ?>', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if(data.status === 'success') {
                        statusArea.innerHTML = `
                            <div style="background: #eff6ff; color: #1e3a8a; padding: 15px; border-radius: 8px; margin-bottom: 15px; font-weight: bold; text-align: center;">
                                Redirigiendo a pasarela segura... 💳
                            </div>
                        `;
                        window.location.href = data.checkout_url;
                    } else {
                        alert('Error: ' + data.message);
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = 'Pagar 3,90€ + IVA y Descargar';
                    }
                })
                .catch(error => {
                    console.error(error);
                    alert('Ocurrió un error en la conexión.');
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = 'Pagar 3,90€ + IVA y Descargar';
                });
            });
        }
    });
    </script>
    
    <!-- Lookalike Modal WOW Effect -->
    <div id="lookalike-modal" style="display: none; position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(15, 23, 42, 0.7); backdrop-filter: blur(8px); z-index: 10000; align-items: center; justify-content: center; padding: 20px; transition: opacity 0.3s ease;">
        <div style="background: #ffffff; border-radius: 24px; width: 100%; max-width: 520px; padding: 40px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.3); position: relative; animation: modalIn 0.4s cubic-bezier(0.16, 1, 0.3, 1); overflow: hidden;">
            
            <button onclick="document.getElementById('lookalike-modal').style.opacity='0'; setTimeout(()=>document.getElementById('lookalike-modal').style.display='none', 300);" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: #94a3b8; cursor: pointer; padding: 6px; border-radius: 50%; transition: all 0.2s; z-index: 2;" onmouseover="this.style.background='#f1f5f9'; this.style.color='#0f172a';" onmouseout="this.style.background='none'; this.style.color='#94a3b8';">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-ee4ea388"></use></svg>
            </button>
            
            <div style="position: relative; z-index: 1; text-align: center;">
                <div style="width: 64px; height: 64px; background: #f3e8ff; border-radius: 20px; display: flex; align-items: center; justify-content: center; color: #9333ea; margin: 0 auto 24px auto;">
                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-6ba5abb4"></use></svg>
                </div>
                
                <span style="display: inline-block; background: #f1f5f9; color: #475569; border: 1px solid #e2e8f0; font-size: 0.75rem; font-weight: 700; padding: 4px 12px; border-radius: 100px; letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 16px;">IA B2B</span>
                
                <h3 style="margin: 0 0 16px 0; font-size: 1.6rem; color: #0f172a; font-weight: 900; line-height: 1.2; letter-spacing: -0.02em;">Multiplica tus ventas clonando a tus clientes</h3>
                <p style="margin: 0 0 32px 0; font-size: 1.05rem; color: #475569; line-height: 1.6;">
                    Sube una lista con tus mejores clientes y nuestro algoritmo cruzará miles de variables para <strong>entregarte cientos de empresas gemelas</strong> por toda España.
                </p>
                
                <div style="display: flex; flex-direction: column; gap: 12px;">
                    <a href="<?= site_url('encontrar-empresas-similares') ?>" style="display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%; text-align: center; background: linear-gradient(to right, #fde047, #f97316); color: #1e293b; text-decoration: none; padding: 16px 20px; border-radius: 12px; font-weight: 800; font-size: 1.1rem; box-shadow: 0 8px 20px rgba(249, 115, 22, 0.3); transition: transform 0.2s, box-shadow 0.2s;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 25px rgba(249, 115, 22, 0.4)';" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(249, 115, 22, 0.3)';">
                        Subir mis clientes 🧬
                    </a>
                    <button onclick="document.getElementById('lookalike-modal').style.opacity='0'; setTimeout(()=>document.getElementById('lookalike-modal').style.display='none', 300);" style="width: 100%; text-align: center; background: transparent; color: #64748b; text-decoration: none; padding: 12px 20px; border-radius: 12px; font-weight: 600; font-size: 0.95rem; border: none; cursor: pointer; transition: color 0.2s;" onmouseover="this.style.color='#0f172a';" onmouseout="this.style.color='#64748b';">
                        Quizás en otro momento
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar modal de Lookalike después de 12 segundos (con cooldown de 7 días)
            const modalId = 'lookalike-modal';
            const storageKey = 'lookalike_modal_shown_v2'; // Cambiamos la key para limpiar el anterior
            
            const lastShownStr = localStorage.getItem(storageKey);
            let shouldShow = false;
            
            if (!lastShownStr) {
                shouldShow = true;
            } else {
                const lastShown = parseInt(lastShownStr, 10);
                const daysPassed = (new Date().getTime() - lastShown) / (1000 * 60 * 60 * 24);
                if (daysPassed >= 7) { // 7 días de enfriamiento
                    shouldShow = true;
                }
            }
            
            if (shouldShow) {
                setTimeout(() => {
                    const modal = document.getElementById(modalId);
                    if (modal) {
                        modal.style.opacity = '0';
                        modal.style.display = 'flex';
                        // trigger reflow
                        void modal.offsetWidth;
                        modal.style.opacity = '1';
                        
                        // Guardamos el timestamp actual
                        localStorage.setItem(storageKey, new Date().getTime().toString());
                    }
                }, 12000); // 12 segundos (momento óptimo)
            }
        });
    </script>

<!-- AJAX Session Handler for Cloudflare Caching -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cif = '<?= esc($companyCif ?? $company['registro_mercantil'] ?? '') ?>';
    if (!cif) return;
    
    fetch('<?= site_url('api/empresa/private-data/') ?>' + encodeURIComponent(cif), {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.logged_in) {
            // Hide public CTAs
            const navCsv = document.getElementById('nav-descargar-csv');
            if (navCsv) navCsv.style.display = 'none';
            
            const ctaBanner = document.getElementById('descargar-excel');
            if (ctaBanner) ctaBanner.style.display = 'none';
            
            // Update Risk Profile
            const riskContainer = document.getElementById('risk-profile-container');
            if (riskContainer && data.risk_profile_html) {
                riskContainer.innerHTML = data.risk_profile_html;
            }
            
            // Update Header UI via localStorage sync
            localStorage.setItem('is_logged_in', '1');
            
            // Apply styles manually for this page load if they weren't applied synchronously
            const publicItems = document.querySelectorAll('.header-public-item');
            publicItems.forEach(el => el.style.setProperty('display', 'none', 'important'));
            
            const privateItems = document.querySelectorAll('.header-private-item');
            privateItems.forEach(el => el.style.setProperty('display', 'inline-flex', 'important'));
        } else {
            localStorage.removeItem('is_logged_in');
            const publicItems = document.querySelectorAll('.header-public-item');
            publicItems.forEach(el => el.style.removeProperty('display'));
            const privateItems = document.querySelectorAll('.header-private-item');
            privateItems.forEach(el => el.style.setProperty('display', 'none', 'important'));
        }
    })
    .catch(err => console.error('Error fetching session data:', err));
});
</script>
</body>


</html>

<?= view('partials/svg_sprite_company') ?>




