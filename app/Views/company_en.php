<!doctype html>
<html lang="en">

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

    <?php
    // Dynamic Schema JSON-LD for the main entity
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
    <link rel="stylesheet" href="<?= base_url('public/css/company_ficha.css') ?>?v=1.3">
</head>

<body>
    <div class="bg-halo" aria-hidden="true"></div>

    <?= view('partials/header_en') ?>

    <main style="padding:40px 0 70px;">
        <section class="container" style="max-width: 1200px;">
            <!-- Breadcrumbs HTML -->
            <nav aria-label="Breadcrumb" class="breadcrumb"
                style="margin-bottom: 1rem; font-size: 0.9rem; color: #666;">
                <a href="<?= site_url() ?>" style="color: inherit; text-decoration: none;">Home</a>
                <span style="margin: 0 0.5rem;">/</span>

                <?php if (!empty($provinceUrl)): ?>
                    <a href="<?= site_url('listado-de-empresas') ?>" style="color: inherit; text-decoration: none;">Directory</a>
                    <span style="margin: 0 0.5rem;">/</span>
                    <a href="<?= esc($provinceUrl) ?>"
                        style="color: inherit; text-decoration: none;"><?= esc($company['province'] ?? $company['provincia']) ?></a>
                <?php else: ?>
                    <a href="<?= site_url('search_company') ?>" style="color: inherit; text-decoration: none;">Search</a>
                <?php endif; ?>

                <span style="margin: 0 0.5rem;">/</span>
                <span aria-current="page"><?= esc($company['name'] ?? 'Empresa') ?></span>
            </nav>

            <div>


                <?php
                $statusRaw = (string) ($company['status'] ?? '');
                // La tabla guarda "ACTIVA": comparar con "ACTIVE" hacía que el punto verde
                // de empresa activa no saliera nunca en la versión inglesa.
                $isActive = in_array(strtoupper(trim($statusRaw)), ['ACTIVA', 'ACTIVE'], true);
                $statusClass = $isActive ? 'company-status company-status--active' : 'company-status company-status--inactive';

                /*
                 * Estado registral EFECTIVO (motor + status), como en la ficha en español.
                 * company_estado_registral() devuelve los textos en español; aquí se
                 * traducen por clave. Ver Helpers/company_helper.php.
                 */
                helper(['company', 'risk_labels']);
                $estadoReg = company_estado_registral($company, $riskProfile ?? null);
                $estadoEnMap = [
                    'extinguida'   => ['Struck off',       'Company struck off',                  'is recorded as struck off (extinguished) in the Spanish Mercantile Registry'],
                    'concurso'     => ['In insolvency',    'Insolvency proceedings in progress',  'is in ongoing insolvency proceedings (concurso de acreedores)'],
                    'liquidacion'  => ['In liquidation',   'Company in liquidation',              'is in the liquidation phase'],
                    'disuelta'     => ['Dissolved',        'Company dissolved',                   'is recorded as dissolved in the Spanish Mercantile Registry'],
                    'hoja_cerrada' => ['Registry closed',  'Registry sheet closed',               'has its registry sheet closed in the Mercantile Registry'],
                ];
                if (isset($estadoEnMap[$estadoReg['clave']])) {
                    [$estadoReg['etiqueta'], $estadoReg['titulo'], $estadoReg['frase']] = $estadoEnMap[$estadoReg['clave']];
                }

                $cnaeFull = (!empty($company['cnae']) && !empty($company['cnae_label']))
                    ? ($company['cnae'] . ' · ' . $company['cnae_label'])
                    : ($company['cnae_label'] ?? ($company['cnae'] ?? '-'));

                $jsonForCode = ['success' => true, 'data' => $company];
                $jsonPretty = json_encode($jsonForCode, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

                $companyName = $company['name'] ?? 'This company';
                $companyCif = $company['cif'] ?? $company['nif'] ?? 'Unknown';
                $companyProv = $company['province'] ?? $company['provincia'] ?? 'Spain';

                $rawAddr = $company['address'] ?? '';
                $companyAddr = $rawAddr ? "{$rawAddr}, {$companyProv}" : "{$companyProv}, Spain";

                $phone = $company['phone'] ?? $company['phone_mobile'] ?? null;

                $adminNames = [];
                if (!empty($administrators)) {
                    foreach (array_slice($administrators, 0, 3) as $adm) {
                        $adminNames[] = $adm['name'];
                    }
                }

                /*
                 * FAQ EN INGLÉS Y SEGÚN EL ESTADO (24-09-2026)
                 * Antes estaban en español en una página en inglés, la primera empezaba
                 * por "Sí, … es fiable" para TODAS las empresas (también las extinguidas),
                 * invitaba a "visitar su delegación" y no se pintaban en la página: solo
                 * iban en el JSON-LD, y Google exige que el FAQPage se vea en pantalla.
                 * Las ai_faqs no se usan aquí: están en español.
                 */
                if ($estadoReg['incidencia']) {
                    $faqReliable = "{$companyName} (CIF {$companyCif}) {$estadoReg['frase']}. "
                        . ($estadoReg['cerrada']
                            ? "It no longer trades normally, so it is not advisable to contract with it or extend it credit. "
                            : "Review this carefully before contracting with it or extending it credit. ")
                        . "This page shows its official acts published in the BORME.";
                } else {
                    $faqReliable = "{$companyName} is a company registered in Spain with CIF {$companyCif}"
                        . ($statusRaw !== '' ? ", and its registry status is " . ($isActive ? 'active' : $statusRaw) : '')
                        . ". To assess it as a customer or supplier, review the official acts published in the BORME on this page.";
                }

                if ($estadoReg['cerrada']) {
                    $faqContact = "The last registered office on record for {$companyName} is {$companyAddr}. The company {$estadoReg['frase']}, so it is unlikely to be reachable at that address or its former phone numbers.";
                } else {
                    $faqContact = "The registered office of {$companyName} is {$companyAddr}."
                        . ($phone ? " Its contact phone number is {$phone}." : " No public contact phone number is on record.");
                }

                $faqDirectors = (!empty($adminNames)
                        ? ($estadoReg['cerrada'] ? "The last directors on record for {$companyName} are: " : "Current directors and officers of {$companyName} include: ")
                          . implode(', ', $adminNames) . ". The full list is in the Directors section of this page. "
                        : '')
                    . "The BORME section of this page lists the official history of appointments, dismissals and resignations.";

                $faqs = [
                    ['q' => "Is {$companyName} a reliable company?", 'a' => $faqReliable],
                    ['q' => "What are the address and phone number of {$companyName}?", 'a' => $faqContact],
                    ['q' => "Who are the directors of {$companyName}?", 'a' => $faqDirectors],
                ];

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

                // Sin aggregateRating: la nota valoraba la FICHA ("¿Te ha sido útil?"),
                // no la empresa, y el widget ya no existe. Desactivado, no borrado.
                if (false && !empty($ratingCount) && $ratingCount > 0) {
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
                                // Not swapped any more: CompanyModel aliases lat_num/lng_num correctly
                                // (the map lands on the right street). Range guard for old rows.
                                "latitude"  => ((float) $company['lat'] >= 26 && (float) $company['lat'] <= 45) ? (float) $company['lat'] : (float) $company['lng'],
                                "longitude" => ((float) $company['lat'] >= 26 && (float) $company['lat'] <= 45) ? (float) $company['lng'] : (float) $company['lat']
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

                if (false && isset($ratingCount) && $ratingCount > 0) {
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
                            
                            if ($estadoReg['incidencia']) {
                                // With an adverse state the ribbon shows the state, not the age:
                                // "Veteran (+10y)" on a struck-off company reads as solidity.
                                $ribbonText = $estadoReg['etiqueta'];
                                if ($estadoReg['cerrada']) {
                                    $ribbonGradient = 'linear-gradient(135deg, #64748b 0%, #334155 100%)';
                                    $ribbonShadow = 'rgba(51, 65, 85, 0.35)';
                                } else {
                                    $ribbonGradient = 'linear-gradient(135deg, #ef4444 0%, #b91c1c 100%)';
                                    $ribbonShadow = 'rgba(185, 28, 28, 0.35)';
                                }
                            } elseif (!empty($constValHeader) && $timestamp = strtotime($constValHeader)) {
                                $ageInDays = (time() - $timestamp) / (60 * 60 * 24);
                                $ageInYears = $ageInDays / 365.25;
                                
                                if ($ageInDays <= 90) {
                                    $ribbonText = 'Recent Company';
                                    $ribbonGradient = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                                    $ribbonShadow = 'rgba(16, 185, 129, 0.4)';
                                } elseif ($ageInYears <= 1) {
                                    $ribbonText = 'New Company';
                                    $ribbonGradient = 'linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%)';
                                    $ribbonShadow = 'rgba(14, 165, 233, 0.4)';
                                } elseif ($ageInYears <= 5) {
                                    $ribbonText = 'Young Company';
                                    $ribbonGradient = 'linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%)';
                                    $ribbonShadow = 'rgba(139, 92, 246, 0.4)';
                                } elseif ($ageInYears <= 10) {
                                    $ribbonText = 'Established';
                                    $ribbonGradient = 'linear-gradient(135deg, #f59e0b 0%, #d97706 100%)';
                                    $ribbonShadow = 'rgba(245, 158, 11, 0.4)';
                                } else {
                                    $ribbonText = 'Veteran (+10y)';
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
                                        COMPANY PROFILE
                                    </div>

                                    <div style="display: inline-flex; align-items: center; gap: 4px; background: #ecfdf5; color: #059669; padding: 4px 10px; border-radius: 999px; font-size: 0.7rem; font-weight: 700; border: 1px solid #a7f3d0; letter-spacing: 0.5px; text-transform: uppercase;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                            <path d="M9 12l2 2 4-4"></path>
                                        </svg>
                                        Official Mercantile Reg. Data
                                    </div>
                                    
                                    <?php if (!empty($contracts)): ?>
                                    <div style="display: inline-flex; align-items: center; gap: 4px; background: #eef2ff; color: #4f46e5; padding: 4px 10px; border-radius: 999px; font-size: 0.7rem; font-weight: 700; border: 1px solid #c7d2fe; letter-spacing: 0.5px; text-transform: uppercase;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 3v18h18"/><path d="M18.7 8l-5.1 5.2-2.8-2.7L7 14.3"/>
                                        </svg>
                                        State Contractor
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($subsidies)): ?>
                                    <div style="display: inline-flex; align-items: center; gap: 4px; background: #fefce8; color: #ca8a04; padding: 4px 10px; border-radius: 999px; font-size: 0.7rem; font-weight: 700; border: 1px solid #fef08a; letter-spacing: 0.5px; text-transform: uppercase;">
                                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"/><path d="M16 8h-6a2 2 0 100 4h4a2 2 0 110 4H8"/><path d="M12 18V6"/>
                                        </svg>
                                        Subsidized Company
                                    </div>
                                    <?php endif; ?>
                                </div>

                                <h1 style="font-size: 1.6rem; font-weight: 700; color: #0f172a; margin: 0 0 16px 0; line-height: 1.25; letter-spacing: -0.01em; text-wrap: balance;">
                                    <?= esc($company['name'] ?? '-') ?><?php if (!empty($companyCif) && $companyCif !== 'Unknown' && $companyCif !== '-'): ?> - CIF <?= esc($companyCif) ?><?php endif; ?>
                                </h1>

                                <?php if (false && !empty($company['ai_pitch'])): // HIDDEN IN ENGLISH ?>
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
                                <?php if (false && !empty($aiTags)): // HIDDEN IN ENGLISH ?>
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
                                    <?php if (!empty($companyCif) && $companyCif !== 'Unknown' && $companyCif !== '-'): ?>
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
                                    
                                    <?php if ($estadoReg['incidencia']): ?>
                                    <div title="<?= esc($estadoReg['titulo'], 'attr') ?>" style="margin: 0; display: flex; align-items: center; gap: 6px; padding: 6px 12px; border-radius: 8px; background: <?= $estadoReg['fondo'] ?>; border: 1px solid <?= $estadoReg['borde'] ?>; color: <?= $estadoReg['color'] ?>; font-weight: 700;">
                                        <span style="display: inline-flex; width: 8px; height: 8px; border-radius: 50%; background: <?= $estadoReg['color'] ?>;"></span>
                                        <span><?= esc($estadoReg['etiqueta']) ?></span>
                                    </div>
                                    <?php elseif (!empty($statusRaw)): ?>
                                    <div class="<?= str_replace('company-status', 'b2b-status', esc($statusClass)) ?>" style="margin: 0; display: flex; align-items: center; gap: 6px;">
                                        <?php if ($isActive): ?>
                                            <span style="position: relative; display: flex; width: 8px; height: 8px;">
                                                <span class="status-dot-ping" style="position: absolute; display: inline-flex; height: 100%; width: 100%; border-radius: 50%; background-color: #4ade80;"></span>
                                                <span style="position: relative; display: inline-flex; border-radius: 50%; height: 8px; width: 8px; background-color: #22c55e;"></span>
                                            </span>
                                        <?php endif; ?>
                                        <?php
                                            $statusEn = $statusRaw;
                                            if (stripos($statusRaw, 'ACTIVA') !== false) $statusEn = 'ACTIVE';
                                            elseif (stripos($statusRaw, 'CERRADA') !== false || stripos($statusRaw, 'BAJA') !== false) $statusEn = 'CLOSED';
                                            elseif (stripos($statusRaw, 'EXTINGUIDA') !== false) $statusEn = 'EXTINGUISHED';
                                        ?>
                                        <span><?= esc($statusEn) ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <?php if (!empty($company['updated_at'])): ?>
                                    <div style="display: flex; align-items: center; gap: 6px; background: #f1f5f9; padding: 6px 12px; border-radius: 8px; border: 1px solid #e2e8f0; font-size: 0.85rem;">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none"
                                            stroke="#64748b" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                            <circle cx="12" cy="12" r="10"></circle>
                                            <polyline points="12 6 12 12 16 14"></polyline>
                                        </svg>
                                        <span>Last updated: <?= date('d/m/Y', strtotime($company['updated_at'])) ?></span>
                                    </div>
                                    <?php endif; ?>

                                    <div style="margin-left: auto; display: flex; gap: 12px; align-items: center;">
                                        <div style="display: flex; gap: 6px;">
                                            <a href="https://www.linkedin.com/shareArticle?mini=true&url=<?= urlencode(current_url()) ?>&title=<?= urlencode('Company profile: ' . $companyName) ?>" target="_blank" rel="noopener noreferrer" class="btn-share-icon" title="Share on LinkedIn">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 8a6 6 0 0 1 6 6v7h-4v-7a2 2 0 0 0-2-2 2 2 0 0 0-2 2v7h-4v-7a6 6 0 0 1 6-6z"></path><rect x="2" y="9" width="4" height="12"></rect><circle cx="4" cy="4" r="2"></circle></svg>
                                            </a>
                                            <a href="https://api.whatsapp.com/send?text=<?= urlencode('Check this company: ' . $companyName . ' - ' . current_url()) ?>" target="_blank" rel="noopener noreferrer" class="btn-share-icon" title="Share on WhatsApp">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"></path></svg>
                                            </a>
                                            <button onclick="navigator.clipboard.writeText('<?= current_url() ?>'); alert('Link copied to clipboard');" class="btn-share-icon" title="Copy link" style="cursor: pointer;">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path></svg>
                                            </button>
                                        </div>
                                        <?php if (!$estadoReg['cerrada']): /* no CRM for a company that no longer trades */ ?>
                                        <button type="button" onclick="document.getElementById('crm-modal').style.display='flex';"
                                            style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: #ffffff; color: #334155; font-size: 0.9rem; font-weight: 700; text-decoration: none; border-radius: 10px; border: 1px solid #cbd5e1; transition: all 0.2s; box-shadow: 0 4px 6px rgba(0,0,0,0.1); cursor: pointer;"
                                            onmouseover="this.style.background='#f8fafc';"
                                            onmouseout="this.style.background='#ffffff';">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                                                <path d="M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"></path>
                                                <polyline points="3.27 6.96 12 12.01 20.73 6.96"></polyline>
                                                <line x1="12" y1="22.08" x2="12" y2="12"></line>
                                            </svg>
                                            Send to CRM
                                        </button>
                                        <?php endif; ?>
                                        <a href="<?= site_url('empresa/export/' . $company['id']) ?>"
                                            rel="nofollow"
                                            aria-label="Download PDF report for <?= esc($companyName) ?>"
                                            onclick="window.dataLayer = window.dataLayer || []; window.dataLayer.push({'event': 'cta_pdf_click'});"
                                            style="display: flex; align-items: center; gap: 8px; padding: 8px 16px; background: #2563eb; color: #ffffff; font-size: 0.9rem; font-weight: 700; text-decoration: none; border-radius: 10px; border: 1px solid #2563eb; transition: all 0.2s; box-shadow: 0 4px 6px rgba(37, 99, 235, 0.25);"
                                            onmouseover="this.style.background='#1d4ed8'; this.style.borderColor='#1d4ed8';"
                                            onmouseout="this.style.background='#2563eb'; this.style.borderColor='#2563eb';">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                                                <polyline points="7 10 12 15 17 10"></polyline>
                                                <line x1="12" y1="15" x2="12" y2="3"></line>
                                            </svg>
                                            Download report
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
                                            VIEW RANKING
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
                                        Overview
                                    </a></li>
                                <?php if ((!empty($company['lat']) && !empty($company['lng'])) || !empty($company['address'])): ?>
                                    <li><a href="#map-area">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                                <circle cx="12" cy="10" r="3"></circle>
                                            </svg>
                                            Location
                                        </a></li>
                                <?php endif; ?>
                                <?php if (!empty($contracts) || !empty($subsidies)): ?>
                                    <li><a href="#financial-data">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none"
                                                stroke="currentColor" stroke-width="2">
                                                <rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect>
                                                <path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path>
                                            </svg>
                                            Public Finances
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
                                            Related Companies
                                        </a></li>
                                <?php endif; ?>
                                <li><a href="#api-dev-section">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <polyline points="16 18 22 12 16 6"></polyline>
                                            <polyline points="8 6 2 12 8 18"></polyline>
                                        </svg>
                                        API
                                    </a></li>
                            </ul>
                        </nav>
                        <?php
                        // --- TOC END ---
                        ?>

                    <div class="b2b-grid-2col">
                        <section id="datos-generales" class="b2b-card" style="height: 100%;">
                            <dl class="b2b-data-list">
                                <?php if (!empty($companyCif) && $companyCif !== 'Unknown' && $companyCif !== '-'): ?>
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
                                              <div style="display: flex; align-items: center; gap: 6px; padding: 6px 12px; background: #f0f9ff; color: #0284c7; border-radius: 6px; font-size: 0.85rem; font-weight: 500;">
                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                        <circle cx="12" cy="12" r="10"></circle>
                                        <line x1="2" y1="12" x2="22" y2="12"></line>
                                        <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path>
                                    </svg>
                                    Official Source
                                </div>                                          </svg>
                                        </div>
                                        Website
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
                                        Phone
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
                                        Phone Móvil
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
                                        Province
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
                                            Address
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
                                        Incorporation
                                    </dt>
                                    <dd class="b2b-data-value"><time datetime="<?= esc($constVal) ?>"><?= date('d/m/Y', strtotime($constVal)) ?></time></dd>
                                </div>
                                <?php endif; ?>
                                <?php $objVal = trim(company_objeto_social_real($company)); // not the CNAE text copied again ?>
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
                                        Corporate purpose
                                    </dt>
                                    <dd class="b2b-data-value">
                                        <?= esc($objVal) ?>
                                    </dd>
                                </div>
                                <?php endif; ?>
                            </dl>
                            <?= view('partials/company_data_check', ['companyId' => (int) ($company['id'] ?? 0), 'lang' => 'en']) ?>
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
                                    Location
                                </div>
                                <div id="company-map"></div>
                            </div>
                        <?php endif; ?>
                    </div> <!-- /b2b-grid-2col -->

                    <?php
                    /*
                     * ABOUT + API (24-09-2026)
                     * Here were: a star rating widget in Spanish ("¿Te ha sido útil…?",
                     * "Enviar sugerencia"), the Vértice banner and a "General information"
                     * card that only held two badges and share buttons (the AI text is
                     * Spanish and was hidden). Now: a short factual paragraph in English,
                     * state-aware, and the API strip — the main product — right after the
                     * data table. Vértice moves to "More tools".
                     */
                    $abFund = trim((string) ($company['founded'] ?? $company['incorporation_date'] ?? $company['fecha_constitucion'] ?? ''));
                    $abYear = preg_match('/^\d{4}/', $abFund) && $abFund !== '0000-00-00' ? substr($abFund, 0, 4) : '';
                    $abCif  = (!empty($companyCif) && $companyCif !== 'Unknown' && $companyCif !== '-') ? " (CIF <strong>" . esc($companyCif) . "</strong>)" : '';
                    $apiCif = $abCif !== '' ? $companyCif : 'B12345678';
                    $apiJson = ['cif' => $apiCif, 'name' => $companyName, 'province' => $companyProv];
                    ?>
                    <section class="b2b-card" style="margin: 0 0 24px; padding: 18px 24px;">
                        <h2 style="font-size: 1.05rem; font-weight: 800; color: #0f172a; margin: 0 0 8px;">General information about <?= esc($companyName) ?></h2>
                        <p style="margin: 0; color: #334155; line-height: 1.65; font-size: 0.98rem;">
                            <strong><?= esc($companyName) ?></strong><?= $abCif ?> is a Spanish company<?= $abYear ? ' incorporated in ' . esc($abYear) : '' ?>
                            with its registered office in <strong><?= esc($companyProv) ?></strong>.
                            <?php if ($estadoReg['incidencia']): ?>
                                According to the Mercantile Registry, it <strong><?= esc($estadoReg['frase']) ?></strong><?= $estadoReg['cerrada'] ? ' and no longer trades normally' : '' ?>.
                            <?php elseif ($isActive): ?>
                                According to the Mercantile Registry, it is an active company.
                            <?php endif; ?>
                            This page gathers its official registry data and the acts published in the BORME (Spain's Official Gazette of the Mercantile Registry).
                        </p>
                    </section>

                    <style>
                        .api-strip{margin:0 0 24px;display:grid;grid-template-columns:minmax(0,1fr) minmax(0,1.05fr);gap:24px;align-items:center;background:#fff;border:1px solid #e2e8f0;border-radius:20px;padding:22px 24px;box-shadow:0 2px 8px rgba(15,23,42,.04);scroll-margin-top:90px}
                        .api-strip__eyebrow{display:inline-flex;align-items:center;gap:6px;font-size:.7rem;font-weight:800;letter-spacing:.7px;text-transform:uppercase;color:#2563eb;background:#eff6ff;border:1px solid #bfdbfe;border-radius:999px;padding:3px 10px;margin-bottom:10px}
                        .api-strip__title{font-size:1.2rem;font-weight:800;color:#0f172a;margin:0 0 6px;letter-spacing:-.2px}
                        .api-strip__text{font-size:.9rem;color:#475569;line-height:1.5;margin:0 0 14px}
                        .api-strip__actions{display:flex;align-items:center;gap:14px;flex-wrap:wrap}
                        .api-strip__btn{display:inline-flex;align-items:center;gap:8px;background:#2563eb;color:#fff;border-radius:10px;padding:10px 18px;font-weight:800;font-size:.9rem;text-decoration:none;box-shadow:0 4px 12px rgba(37,99,235,.22);transition:background .15s}
                        .api-strip__btn:hover{background:#1d4ed8}
                        .api-strip__link{font-size:.85rem;font-weight:700;color:#334155;text-decoration:none}
                        .api-strip__link:hover{color:#2563eb;text-decoration:underline}
                        .api-strip__code{background:#0f172a;border-radius:14px;padding:14px 16px;font-family:ui-monospace,SFMono-Regular,Menlo,Consolas,monospace;font-size:.78rem;line-height:1.6;color:#e2e8f0;overflow-x:auto;min-width:0}
                        .api-strip__code .c-verb{color:#6ee7b7;font-weight:700}
                        .api-strip__code .c-dim{color:#64748b}
                        .api-strip__code pre{margin:6px 0 0;padding:0;background:transparent;border:0;color:#cbd5e1;white-space:pre;font:inherit}
                        @media (max-width:820px){.api-strip{grid-template-columns:1fr;padding:18px}}
                    </style>
                    <section id="api-dev-section" class="api-strip" aria-labelledby="api-strip-title">
                        <div>
                            <span class="api-strip__eyebrow">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg>
                                REST API
                            </span>
                            <h2 id="api-strip-title" class="api-strip__title">This data, in your software</h2>
                            <p class="api-strip__text">
                                Look up <strong><?= esc($companyName) ?></strong> and any Spanish company by CIF with a single call:
                                registry data, BORME acts and risk profile, as JSON.
                            </p>
                            <div class="api-strip__actions">
                                <a class="api-strip__btn" href="<?= site_url('register') ?>" data-track-click="company_api" data-track-element="api_key">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><circle cx="7.5" cy="15.5" r="4.5"></circle><path d="M21 2l-9.6 9.6M15.5 7.5l3 3L22 7l-3-3"></path></svg>
                                    Get a free API key
                                </a>
                                <a class="api-strip__link" href="<?= site_url('docs') ?>" data-track-click="company_api" data-track-element="docs">Read the docs →</a>
                            </div>
                        </div>
                        <div class="api-strip__code" aria-label="API request example">
                            <div><span class="c-verb">GET</span> /api/v1/companies?cif=<?= esc($apiCif) ?></div>
                            <div class="c-dim">Authorization: Bearer YOUR_API_KEY</div>
<pre><?= esc(json_encode($apiJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                        </div>
                    </section>


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
                                Administrators and Directors of <?= esc($companyName) ?>
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
                                                <?php
                                                    $adminDict = [
                                                        "Adm. Unico" => "Sole Administrator",
                                                        "Adm. Solid." => "Joint Administrator",
                                                        "Adm. Mancom." => "Joint Administrator",
                                                        "Socio unico" => "Sole Shareholder",
                                                        "Apoderado" => "Proxy",
                                                        "Liquidador" => "Liquidator",
                                                        "Auditor" => "Auditor",
                                                        "Consejero" => "Board Member",
                                                        "Presidente" => "President",
                                                        "Vicepresidente" => "Vice President",
                                                        "Secretario" => "Secretary"
                                                    ];
                                                ?>
                                                <?= esc(strtr($admin['position'], $adminDict)) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

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
                                            Tenders Públicas y Subvenciones
                                        </h2>
                                        <p style="color: #64748b; margin: 8px 0 0 0; font-size: 0.95rem;">
                                            Historial oficial de contratos adjudicados por el Status y subvenciones recibidas por <?= esc($companyName) ?>.
                                        </p>
                                    </div>
                                </div>

                                <?php if (!empty($contracts)): ?>
                                    <h3 style="font-size: 1.1rem; font-weight: 700; color: #0f172a; margin-top: 32px; margin-bottom: 16px; border-bottom: 2px solid #e2e8f0; padding-bottom: 8px;">Contratos Públicos Adjudicados</h3>
                                    <div style="overflow-x: auto; border-radius: 12px; border: 1px solid #e2e8f0;">
                                        <table style="width: 100%; border-collapse: collapse; min-width: 600px; text-align: left;">
                                            <thead>
                                                <tr style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                                    <th style="padding: 12px 16px; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase;">Date</th>
                                                    <th style="padding: 12px 16px; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase;">Órgano de Contratación</th>
                                                    <th style="padding: 12px 16px; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase;">Título del Contrato</th>
                                                    <th style="padding: 12px 16px; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase; text-align: right;">Amount</th>
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
                                                    <th style="padding: 12px 16px; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase;">Date</th>
                                                    <th style="padding: 12px 16px; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase;">Instrumento</th>
                                                    <th style="padding: 12px 16px; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase;">Convocatoria</th>
                                                    <th style="padding: 12px 16px; font-weight: 700; font-size: 0.85rem; color: #475569; text-transform: uppercase; text-align: right;">Amount</th>
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
                                            Do you need to process this data in bulk?
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
                                        stroke-width="2.5">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                        <polyline points="14 2 14 8 20 8"></polyline>
                                        <line x1="16" y1="13" x2="8" y2="13"></line>
                                        <line x1="16" y1="17" x2="8" y2="17"></line>
                                        <polyline points="10 9 9 9 8 9"></polyline>
                                    </svg>
                                </span>
                                Mercantile Registry (BORME) Acts for <?= esc($companyName) ?>
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
                                    if (strpos($t, 'nombramiento') !== false) $t = 'Appointments';
                                    elseif (strpos($t, 'cese') !== false || strpos($t, 'dimision') !== false || strpos($t, 'revocacion') !== false) $t = 'Dismissals/Resignations';
                                    elseif (strpos($t, 'capital') !== false) $t = 'Capital changes';
                                    elseif (strpos($t, 'domicilio') !== false) $t = 'Change of address';
                                    elseif (strpos($t, 'estatutos') !== false || strpos($t, 'objeto social') !== false) $t = 'Bylaws amendments';
                                    elseif (strpos($t, 'constitucion') !== false) $t = 'Incorporation';
                                    elseif (strpos($t, 'unipersonalidad') !== false) $t = 'Single-member status';
                                    elseif (strpos($t, 'cuentas') !== false) $t = 'Annual accounts';
                                    elseif (strpos($t, 'socio unico') !== false) $t = 'Sole shareholder';
                                    // Without these four, a dissolution or a striking-off fell into
                                    // "Other acts", exactly where it matters most to name them.
                                    elseif (strpos($t, 'extinci') !== false) $t = 'Striking-off';
                                    elseif (strpos($t, 'disoluci') !== false) $t = 'Dissolution';
                                    elseif (strpos($t, 'liquidac') !== false) $t = 'Liquidation';
                                    elseif (strpos($t, 'concurs') !== false) $t = 'Insolvency';
                                    else $t = 'Other acts';
                                    
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
                            
                            // English month labels (the variable keeps its old name to avoid touching the chart markup).
                            $monthsEs = ['01'=>'Jan','02'=>'Feb','03'=>'Mar','04'=>'Apr','05'=>'May','06'=>'Jun','07'=>'Jul','08'=>'Aug','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dec'];

                            // Charts only when they say something: 2 bars of height 1 years apart
                            // say less than the list below. Same thresholds as the Spanish page.
                            $verEvolucion    = count($bormePosts) >= 6 && count($bormeTimeline) >= 3;
                            $verDistribucion = $totalActs >= 4 && count($actCounts) >= 2;
                            ?>

                            <?php if ($verEvolucion || $verDistribucion): ?>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
                                <?php if (false && !empty($company['ai_borme_summary'])): ?>
                                    <div class="ai-box-glow" style="background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%); border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
                                        <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 12px; color: #0f172a; font-weight: 800; font-size: 1.05rem;">
                                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M21 12a9 9 0 1 1-6.219-8.56"></path>
                                                <polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"></polygon>
                                            </svg>
                                            BORME AI Summary
                                        </div>
                                        <p style="margin: 0; color: #475569; line-height: 1.6; font-size: 0.95rem;">
                                            <?= nl2br(esc($company['ai_borme_summary'])) ?>
                                        </p>
                                    </div>
                                <?php endif; ?>

                                <?php if ($verEvolucion): ?>
                                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
                                        <h3 style="font-size: 0.9rem; font-weight: 700; color: #64748b; margin-top: 0; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Activity Evolution</h3>
                                        
                                        <div style="overflow-x: auto; padding-bottom: 4px;">
                                            <div style="min-width: max-content;">
                                                
                                                <!-- ROW 1: Barras -->
                                                <div style="display: flex; align-items: flex-end; gap: 6px; height: 110px; border-bottom: 1px solid #e2e8f0;">
                                                    <?php foreach ($bormeTimeline as $my => $data): 
                                                        $count = $data['count'];
                                                        $heightPct = max(($count / $maxActsTimeline) * 100, 8); 
                                                        list($y, $m) = explode('-', $my);
                                                        $tooltipYear = $monthsEs[$m] . " " . $y;
                                                        
                                                        $tooltip = "{$count} act" . ($count > 1 ? 's' : '') . " in {$tooltipYear}:&#10;";
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

                                <?php if ($verDistribucion): ?>
                                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 1.5rem; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.02);">
                                        <h3 style="font-size: 0.9rem; font-weight: 700; color: #64748b; margin-top: 0; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.5px;">Acts Distribution</h3>
                                        <div style="display: flex; flex-direction: column; gap: 14px;">
                                            <?php foreach ($topActs as $type => $count): 
                                                $pct = $totalActs > 0 ? round(($count / $totalActs) * 100) : 0;
                                            ?>
                                                <div>
                                                    <div style="display: flex; justify-content: space-between; font-size: 0.8rem; margin-bottom: 6px; color: #475569; font-weight: 600;">
                                                        <span><?= esc($type) ?></span>
                                                        <span style="color: #94a3b8;"><?= $count ?> act<?= $count > 1 ? 's' : '' ?> (<?= $pct ?>%)</span>
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
                            <?php endif; ?>

                            <?php 
                            $bormeDict = [
                                "Nombramientos" => "Appointments",
                                "Ceses/Dimisiones" => "Dismissals/Resignations",
                                "Ceses" => "Dismissals",
                                "Dimisiones" => "Resignations",
                                "Revocaciones" => "Revocations",
                                "Depósito de cuentas anuales" => "Deposit of annual accounts",
                                "Cuentas" => "Accounts",
                                "Adm. Unico" => "Sole Administrator",
                                "Adm. Solid." => "Joint Administrator",
                                "Adm. Mancom." => "Joint Administrator",
                                "Socio unico" => "Sole Shareholder",
                                "Apoderado" => "Proxy",
                                "Liquidador" => "Liquidator",
                                "Auditor" => "Auditor",
                                "Datos registrales." => "Registry data.",
                                "T " => "Vol ",
                                "F " => "Fol ",
                                "S " => "Sec ",
                                "H M" => "Sheet M",
                                "H B" => "Sheet B",
                                "H V" => "Sheet V",
                                "I/A " => "Entry ",
                                "Constitución" => "Incorporation",
                                "Ampliacion de capital" => "Capital Increase",
                                "Reduccion de capital" => "Capital Reduction",
                                "Modificaciones estatutarias" => "Statutory modifications",
                                "Cambio de domicilio social" => "Change of registered office",
                                "Modificacion de objeto social" => "Change of corporate purpose",
                                "Cambio de denominacion social" => "Change of company name",
                                "Declaracion de unipersonalidad" => "Declaration of single-member status",
                                "Acto Registral" => "Registry Act",
                                "Comienzo de operaciones" => "Commencement of operations",
                                "Capital" => "Capital",
                                "Desembolsado" => "Disbursed",
                                "Suscrito" => "Subscribed",
                                "Empresario Individual" => "Sole Proprietor",
                                " y " => " and ",
                            ];
                            ?>
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
                                                    <?php 
                                                        $actTypeEs = $post['act_types'] ?: 'Acto Registral';
                                                        $actTypeEn = isset($bormeDict) ? strtr($actTypeEs, $bormeDict) : $actTypeEs;
                                                    ?>
                                                    <?= esc($actTypeEn) ?>
                                                </h3>
                                                <div>
                                                    <?php
                                                    // Format description
                                                    $desc = $post['description'];
                                                    if (isset($bormeDict)) {
                                                        $desc = strtr($desc, $bormeDict);
                                                    }
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

                    <?php
                    /*
                     * VISIBLE FAQ (24-09-2026). The FAQPage JSON-LD was emitted but the
                     * questions were never shown, and the "FAQs" tab pointed to an anchor
                     * that did not exist. Google requires FAQ structured data to match
                     * visible content.
                     */
                    ?>
                    <section id="preguntas-frecuentes" style="margin-top: 3rem; scroll-margin-top: 90px;">
                        <h3 style="display: flex; align-items: center; gap: 10px; font-size: 1.5rem; font-weight: 800; margin: 0 0 1.25rem; color: var(--b2b-text);">
                            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--b2b-primary);" aria-hidden="true"><circle cx="12" cy="12" r="10"></circle><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"></path><line x1="12" y1="17" x2="12.01" y2="17"></line></svg>
                            Frequently asked questions
                        </h3>
                        <div style="display: flex; flex-direction: column; gap: 18px;">
                            <?php foreach ($faqs as $faq): ?>
                                <div style="border-left: 3px solid #e2e8f0; padding-left: 16px;">
                                    <h4 style="margin: 0 0 6px; font-size: 1rem; font-weight: 700; color: #0f172a;"><?= esc($faq['q']) ?></h4>
                                    <p style="margin: 0; color: #475569; line-height: 1.6; font-size: 0.95rem;"><?= esc($faq['a']) ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </section>

                    <!-- Related Companies (Full Width Grid) -->
                    <div style="margin-top: 3rem; margin-bottom: 2rem;">
                        <?php if (!empty($related)): ?>
                            <div id="empresas-relacionadas">
                                <h3 style="display: flex; align-items: center; gap: 10px; font-size: 1.5rem; font-weight: 800; margin-bottom: 1.5rem; color: var(--b2b-text);">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: var(--b2b-primary);">
                                        <path d="M6 22V4a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v18Z"></path>
                                        <path d="M6 12H4a2 2 0 0 0-2 2v6a2 2 0 0 0 2 2h2"></path>
                                        <path d="M18 9h2a2 2 0 0 1 2 2v9a2 2 0 0 1-2 2h-2"></path>
                                        <path d="M10 6h4"></path>
                                        <path d="M10 10h4"></path>
                                        <path d="M10 14h4"></path>
                                        <path d="M10 18h4"></path>
                                    </svg>
                                    Related Companies
                                </h3>

                                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;">
                                    <?php 
                                    $relSlice = array_slice($related, 0, 12);
                                    foreach ($relSlice as $idx => $rel):
                                        helper('company');
                                        $relUrl = company_url($rel);
                                        $name = esc($rel['name'] ?? 'Empresa');
                                        ?>
                                        <a href="<?= esc($relUrl) ?>" 
                                           style="display: flex; align-items: center; justify-content: space-between; padding: 1rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 12px; text-decoration: none; transition: all 0.2s; box-shadow: 0 1px 3px rgba(0,0,0,0.02);" 
                                           onmouseover="this.style.borderColor='var(--b2b-primary)'; this.style.boxShadow='0 4px 6px -1px rgba(0,0,0,0.05)'; this.querySelector('.rel-text').style.color='var(--b2b-primary)';" 
                                           onmouseout="this.style.borderColor='#e2e8f0'; this.style.boxShadow='0 1px 3px rgba(0,0,0,0.02)'; this.querySelector('.rel-text').style.color='var(--b2b-text)';">
                                            
                                            <div style="display: flex; align-items: center; overflow: hidden; gap: 12px; flex: 1; min-width: 0;">
                                                <div style="color: #94a3b8; display: flex; align-items: center; flex-shrink: 0; background: #f8fafc; padding: 8px; border-radius: 8px;">
                                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                                                <span class="rel-text" style="font-weight: 600; color: var(--b2b-text); font-size: 0.95rem; white-space: nowrap; text-overflow: ellipsis; overflow: hidden; max-width: 100%; display: block; transition: color 0.2s;">
                                                    <?= $name ?>
                                                </span>
                                            </div>
                                            
                                            <div class="rel-arrow" style="color: #cbd5e1; flex-shrink: 0; margin-left: 0.5rem; display: flex; align-items: center; transition: color 0.2s;">
                                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                    <polyline points="9 18 15 12 9 6"></polyline>
                                                </svg>
                                            </div>
                                        </a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>


                    <?php
                    /*
                     * MORE TOOLS (24-09-2026). Replaces the full-width CSV banner and the
                     * Vértice banner: secondary products, same style, one row. The CSV card
                     * keeps id="descargar-excel" and stays hidden for logged-in users, as
                     * before.
                     */
                    $ctaSinSector = in_array(trim((string) ($sectorName ?? '')), ['', 'todos los sectores', 'este sector'], true);
                    $ctaProv = !empty($targetProv) ? ($targetProv === 'toda España' ? 'Spain' : $targetProv) : ($company['province'] ?? $company['registro_mercantil'] ?? 'Spain');
                    ?>
                    <style>
                        .tools-strip{margin:1rem 0 3rem}
                        .tools-strip__title{font-size:1rem;font-weight:800;color:#0f172a;margin:0 0 12px}
                        .tools-strip__grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:14px}
                        .tool-card{display:flex;flex-direction:column;gap:6px;background:#fff;border:1px solid #e2e8f0;border-radius:14px;padding:16px;text-decoration:none;transition:border-color .15s,box-shadow .15s}
                        .tool-card:hover{border-color:#bfdbfe;box-shadow:0 6px 16px -8px rgba(15,23,42,.18)}
                        .tool-card__head{display:flex;align-items:center;gap:10px}
                        .tool-card__icon{flex-shrink:0;width:30px;height:30px;border-radius:8px;background:#f1f5f9;color:#334155;display:flex;align-items:center;justify-content:center}
                        .tool-card__name{font-size:.92rem;font-weight:800;color:#0f172a}
                        .tool-card__text{font-size:.8rem;color:#64748b;line-height:1.45;margin:0;flex:1}
                        .tool-card__cta{font-size:.82rem;font-weight:800;color:#2563eb;margin-top:4px}
                    </style>
                    <section class="tools-strip" aria-labelledby="tools-strip-title">
                        <h3 id="tools-strip-title" class="tools-strip__title">More tools</h3>
                        <div class="tools-strip__grid">
                            <?php if (!session('logged_in')): ?>
                            <a id="descargar-excel" class="tool-card" href="<?= $radarCheckoutUrl ?>" rel="nofollow"
                               onclick="window.dataLayer = window.dataLayer || []; window.dataLayer.push({'event': 'cta_excel_click'});"
                               data-track-click="company_tools" data-track-element="csv">
                                <span class="tool-card__head">
                                    <span class="tool-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path><polyline points="7 10 12 15 17 10"></polyline><line x1="12" y1="15" x2="12" y2="3"></line></svg></span>
                                    <span class="tool-card__name">Company list as CSV</span>
                                </span>
                                <span class="tool-card__text">
                                    <?= $countFormatted ?> companies<?= $ctaSinSector ? '' : ' in the sector ' . esc(trim(explode('INFORME', $sectorName)[0])) ?> in <?= esc($ctaProv) ?>, with contact details.
                                </span>
                                <span class="tool-card__cta">Download for <?= $priceStr ?> € + VAT →</span>
                            </a>
                            <?php endif; ?>
                            <a class="tool-card" href="https://vertice.apiempresas.es" target="_blank" rel="noopener noreferrer"
                               onclick="if(window.trackEvent) trackEvent('click_vertice_banner', { source: 'company_tools' });"
                               data-track-click="company_tools" data-track-element="vertice">
                                <span class="tool-card__head">
                                    <span class="tool-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg></span>
                                    <span class="tool-card__name">Vértice</span>
                                </span>
                                <span class="tool-card__text">Analyse the viability of any Spanish municipality with AI before opening a store.</span>
                                <span class="tool-card__cta">Try it for free →</span>
                            </a>
                            <a class="tool-card" href="<?= site_url('spanish-company-data-api') ?>" data-track-click="company_tools" data-track-element="api_pricing">
                                <span class="tool-card__head">
                                    <span class="tool-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><polyline points="16 18 22 12 16 6"></polyline><polyline points="8 6 2 12 8 18"></polyline></svg></span>
                                    <span class="tool-card__name">API plans and pricing</span>
                                </span>
                                <span class="tool-card__text">Bulk lookups, BORME acts and risk data for every Spanish company, with a free tier.</span>
                                <span class="tool-card__cta">See plans →</span>
                            </a>
                        </div>
                    </section>

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
                                    Copying data by hand? <b>Use our API</b> and save time.
                                </div>
                                <a href="#api-dev-section" class="btn-toast">See the API</a>
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








                    <!-- RADAR PRO CTA -->
                    
                </div>
        </section>
    </main>

    <?= view('partials/footer_en') ?>

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
                        // CARTO exige API key en sus basemaps desde septiembre de 2026: sin ella
                        // cada tesela sale con una marca de agua "API KEY REQUIRED". La clave va
                        // en .env (CARTO_BASEMAPS_KEY, gratis en carto.com/basemaps/apikey). Sin
                        // clave, se usa el mapa incrustado de Google con las coordenadas, que no
                        // la necesita, en vez de enseñar un mapa roto.
                        const cartoKey = "<?= esc(trim((string) getenv('CARTO_BASEMAPS_KEY')), 'js') ?>";
                        const companyName = "<?= esc($company['name'] ?? $company['nombre'] ?? 'Empresa', 'js') ?>";
                        const rawAddress = "<?= esc($company['address'] ?? '', 'js') ?>";
                        const province = "<?= esc($company['province'] ?? $company['provincia'] ?? '', 'js') ?>";

                        if (hasCoords && cartoKey) {
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
                            const mapQuery = hasCoords
                                ? `<?= (float) ($company['lat'] ?? 0) ?>,<?= (float) ($company['lng'] ?? 0) ?>`
                                : fullAddress;
                            const iframe = document.createElement('iframe');
                            iframe.width = "100%";
                            iframe.height = "100%";
                            iframe.frameBorder = "0";
                            iframe.style.border = "0";
                            iframe.style.borderRadius = "12px";
                            iframe.loading = "lazy";
                            iframe.src = `https://maps.google.com/maps?q=${encodeURIComponent(mapQuery)}&t=&z=${hasCoords ? 16 : 15}&ie=UTF8&iwloc=&output=embed`;
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

                        L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png?key=' + encodeURIComponent("<?= esc(trim((string) getenv('CARTO_BASEMAPS_KEY')), 'js') ?>"), {
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
                Enrich your database automatically without typing anything.
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
