<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => $title,
        'excerptText' => $meta_description,
        'canonical'   => $canonical,
        'robots'      => 'index,follow',
    ]) ?>
    <link rel="preload" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"></noscript>
    <style>
        #company-map {
            height: 350px;
            width: 100%;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
            z-index: 1;
        }
    #map-area {
        margin-top: 2rem;
        background: white;
        border-radius: 20px;
        padding: 1.5rem;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(226, 232, 240, 0.8);
    }
    .map-section-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.75rem;
        padding: 0.5rem 0;
    }
    .map-section-title svg {
        color: #3b82f6;
    }
    #company-map {
        height: 350px;
        width: 100%;
        border-radius: 12px;
        z-index: 1;
        border: 1px solid #f1f5f9;
    }
    /* Modern Popup Styling */
    .leaflet-popup-content-wrapper {
        border-radius: 12px;
        padding: 0;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    }
    .leaflet-popup-content {
        margin: 12px 16px;
        font-family: inherit;
        color: #334155;
        line-height: 1.5;
    }
    .leaflet-popup-tip {
        box-shadow: none;
    }
</style>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<header>
    <div class="container nav">
        <div class="brand">
            <a href="<?=site_url() ?>" aria-label="Volver al inicio de APIEmpresas.es">
                <svg class="ve-logo" width="32" height="32" viewBox="0 0 64 64" aria-hidden="true" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <linearGradient id="ve-g" x1="10" y1="54" x2="54" y2="10" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#2152FF"/>
                            <stop offset=".65" stop-color="#5C7CFF"/>
                            <stop offset="1" stop-color="#12B48A"/>
                        </linearGradient>
                        <filter id="ve-cardShadow" x="-20%" y="-20%" width="140%" height="140%">
                            <feDropShadow dx="0" dy="2" stdDeviation="3" flood-opacity=".20"/>
                        </filter>
                        <filter id="ve-checkShadow" x="-30%" y="-30%" width="160%" height="160%">
                            <feDropShadow dx="0" dy="1" stdDeviation="1.2" flood-color="#0B1A36" flood-opacity=".22"/>
                        </filter>
                        <radialGradient id="ve-gloss" cx="0" cy="0" r="1" gradientUnits="userSpaceOnUse"
                                        gradientTransform="translate(20 16) rotate(45) scale(28)">
                            <stop stop-color="#FFFFFF" stop-opacity=".32"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity="0"/>
                        </radialGradient>
                        <linearGradient id="ve-rim" x1="12" y1="52" x2="52" y2="12">
                            <stop stop-color="#FFFFFF" stop-opacity=".6"/>
                            <stop offset="1" stop-color="#FFFFFF" stop-opacity=".35"/>
                        </linearGradient>
                    </defs>

                    <g filter="url(#ve-cardShadow)">
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-g)"/>
                        <rect x="6" y="6" width="52" height="52" rx="14" fill="url(#ve-gloss)"/>
                        <rect x="6.5" y="6.5" width="51" height="51" rx="13.5" fill="none" stroke="url(#ve-rim)"/>
                    </g>

                    <path d="M18 33 L28 43 L46 22"
                          stroke="#FFFFFF" stroke-width="5" stroke-linecap="round" stroke-linejoin="round"
                          fill="none" filter="url(#ve-checkShadow)"/>
                </svg>
            </a>

            <div class="brand-text">
                <span class="brand-name">API<span class="grad">Empresas</span>.es</span>
                <span class="brand-tag">Verificación empresarial en segundos</span>
            </div>
        </div>

        <nav class="desktop-only" aria-label="Principal">
            <a class="minor" href="<?=site_url() ?>dashboard">Dashboard</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="<?=site_url() ?>documentation">Documentación</a>
            <span style="margin:0 12px; color:#cdd6ea">•</span>
            <a class="minor" href="<?=site_url() ?>search_company">Buscador</a>
        </nav>

        <div class="desktop-only">
            <?php if(!session('logged_in')){ ?>
                <a class="btn btn_header btn_header--ghost" href="<?=site_url() ?>enter">
                    <span>Iniciar sesión</span>
                </a>
            <?php } else { ?>
                <a class="btn btn_header btn_header--ghost logout" href="<?=site_url() ?>logout">
                    <span>Salir</span>
                </a>
            <?php } ?>
        </div>
    </div>
</header>

<main style="padding:40px 0 70px;">
    <section class="container search-section">
        <!-- Breadcrumbs HTML -->
        <nav aria-label="Breadcrumb" class="breadcrumb" style="margin-bottom: 1rem; font-size: 0.9rem; color: #666;">
            <a href="<?= site_url() ?>" style="color: inherit; text-decoration: none;">Inicio</a>
            <span style="margin: 0 0.5rem;">/</span>
            
            <?php if (!empty($provinceUrl)): ?>
                <a href="<?= site_url('directorio') ?>" style="color: inherit; text-decoration: none;">Directorio</a>
                <span style="margin: 0 0.5rem;">/</span>
                <a href="<?= esc($provinceUrl) ?>" style="color: inherit; text-decoration: none;"><?= esc($company['province'] ?? $company['provincia']) ?></a>
            <?php else: ?>
                <a href="<?= site_url('search_company') ?>" style="color: inherit; text-decoration: none;">Buscador</a>
            <?php endif; ?>

            <span style="margin: 0 0.5rem;">/</span>
            <span aria-current="page"><?= esc($company['name'] ?? 'Empresa') ?></span>
        </nav>

        <div>
            

                <?php
                    $statusRaw = (string)($company['status'] ?? '');
                    $isActive  = strtoupper($statusRaw) === 'ACTIVA';
                    $statusClass = $isActive ? 'company-status company-status--active' : 'company-status company-status--inactive';

                    $cnaeFull = (!empty($company['cnae']) && !empty($company['cnae_label']))
                        ? ($company['cnae'] . ' · ' . $company['cnae_label'])
                        : ($company['cnae_label'] ?? ($company['cnae'] ?? '-'));

                    $jsonForCode = ['success' => true, 'data' => $company];
                    $jsonPretty  = json_encode($jsonForCode, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
                    
                    // --- AUTO-GENERATED FAQS ---
                    $companyName = $company['name'] ?? 'Esta empresa';
                    $companyCif  = $company['cif'] ?? $company['nif'] ?? 'Desconocido';
                    $companyProv = $company['province'] ?? $company['provincia'] ?? 'España';
                    
                    // Dirección inteligente: si hay domicilio, úsalo. Si no, provincia.
                    $rawAddr     = $company['address'] ?? $company['address'] ?? '';
                    $companyAddr = $rawAddr ? "{$rawAddr}, {$companyProv}" : "{$companyProv}, España";
                    
                    $companyAct  = $company['cnae_label'] ?? 'su actividad registrada';
                    
                    // Phone logic
                    $phone = $company['phone'] ?? $company['phone_mobile'] ?? null;
                    $phoneHtml = $phone ? "**{$phone}**" : "el teléfono de {$companyName} en nuestro informe";

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
                            'a' => "Para conocer a los administradores, directivos y cargos de la empresa, consulte la sección de **Actos del BORME**, donde se publican los nombramientos, ceses y dimisiones oficiales."
                        ],
                        [
                            'q' => "¿A qué se dedica {$companyName}?",
                            'a' => "Su actividad principal CNAE es: **{$companyAct}**. Esta clasificación permite categorizar su sector de negocio y actividad económica."
                        ]
                    ];

                    // Schema.org Data
                    $schemaOrg = [
                        "@context" => "https://schema.org",
                        "@graph" => [
                            [
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
                            ],
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
                                "mainEntity" => array_map(function($item) {
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
                ?>
                <article class="company-card">
                    <header class="company-card__header">
                        <div>
                            <div class="company-card__eyebrow">Ficha registral</div>
                            <h1 class="company-card__name" style="font-size: 1.5rem; margin: 0;">Información de empresa: <?= esc($company['name'] ?? '-') ?></h1>
                            <div class="company-card__meta">
                                <?= esc(($company['cif'] ?? $company['nif'] ?? '-') . ' · ' . ($company['province'] ?? $company['provincia'] ?? '-')) ?>
                            </div>
                            <div style="margin-top: 12px; display: flex; gap: 10px; flex-wrap: wrap;">
                                <?php if (getenv('ENABLE_COMPANY_ALERTS') === 'true'): ?>
                                    <a href="<?= site_url('alerts/confirm/' . ($company['cif'] ?? $company['nif'] ?? '-')) ?>" class="btn" style="padding: 8px 16px; font-size: 0.9rem; background: #2563eb; color: #ffffff; border: 1px solid #1d4ed8; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: background 0.2s;">
                                        🔔 Monitorizar cambios
                                    </a>
                                <?php endif; ?>

                                <a href="<?= site_url('empresa/export/' . $company['id']) ?>" class="btn" aria-label="Descargar Informe PDF de <?= esc($companyName) ?>" style="padding: 8px 16px; font-size: 0.9rem; background: #ffffff; color: #1e293b; border: 1px solid #e2e8f0; border-radius: 6px; display: inline-flex; align-items: center; gap: 8px; text-decoration: none; font-weight: 500; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: all 0.2s;">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                                    Descargar Informe PDF
                                </a>
                            </div>
                        </div>
                        <div class="<?= esc($statusClass) ?>">
                            <span class="company-status__dot"></span>
                            <span><?= esc($statusRaw ?: '-') ?></span>
                        </div>
                    </header>

                    <section class="company-card__body">
                        <dl class="company-card__grid">
                            <div><dt>CIF</dt><dd><?= esc($company['cif'] ?? $company['nif'] ?? '-') ?></dd></div>
                            
                            <div>
                                <dt>CNAE</dt>
                                <dd>
                                    <?php if(!empty($company['cnae'])): ?>
                                        <a href="<?= site_url('search_company?q=' . urlencode($company['cnae'])) ?>" style="text-decoration: underline; color: inherit;">
                                            <?= esc($cnaeFull ?: '-') ?>
                                        </a>
                                    <?php else: ?>
                                        <?= esc($cnaeFull ?: '-') ?>
                                    <?php endif; ?>
                                </dd>
                            </div>
                            
                            <div>
                                <dt>Provincia</dt>
                                <dd>
                                    <?php if(!empty($company['province'] ?? $company['provincia'] ?? null)): ?>
                                        <a href="<?= site_url('search_company?q=' . urlencode($company['province'] ?? $company['provincia'] ?? '')) ?>" style="text-decoration: underline; color: inherit;">
                                            <?= esc($company['province'] ?? $company['provincia'] ?? '-') ?>
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </dd>
                            </div>
                            
                            <div><dt>Fecha de constitución</dt><dd><time datetime="<?= esc($company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? '') ?>"><?= esc($company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? '-') ?></time></dd></div>
                            <div class="company-card__purpose"><dt>Objeto social</dt><dd><?= esc($company['corporate_purpose'] ?? $company['objeto_social'] ?? '-') ?></dd></div>
                        </dl>
                    </section>



                    <div class="company-card__footer">
                        <div class="company-footer__row">
                            <div>
                                <h3 class="company-cta-title">¿Eres desarrollador?</h3>
                                <p class="company-cta-desc">Integra los datos de <strong><?= esc($companyName) ?></strong> en tu software.</p>
                            </div>
                            <a href="<?= site_url('register') ?>" class="btn secondary company-cta-btn">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 1 1-7.778 7.778 5.5 5.5 0 0 1 7.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"></path></svg>
                                Obtener API Key
                            </a>
                        </div>

                        <div class="company-code-card">
                            <div class="company-code-header">
                                <span class="company-code-lang">BASH</span>
                                <button type="button" id="btnToggleJson" class="company-code-toggle">Ver respuesta JSON</button>
                            </div>
                            <pre class="company-code-content"><code>curl -X GET "https://apiempresas.es/api/v1/companies?cif=<?= esc($companyCif) ?>" \
     -H "Authorization: Bearer TU_API_KEY"</code></pre>
                        </div>
                    </div>
                    
                    <pre class="company-card__json is-hidden" id="jsonBlock" style="margin-top: 1rem; background: #0f172a; color: #e2e8f0; padding: 1rem; border-radius: 8px; overflow: auto; max-height: 400px; font-size: 0.8rem;"><code><?= esc($jsonPretty) ?></code></pre>
                </article>

                <?php if (!empty($company['lat']) && !empty($company['lng'])): ?>
                    <div id="map-area" class="map-card">
                        <h2 class="map-section-title">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path><circle cx="12" cy="10" r="3"></circle></svg>
                            Ubicación y Datos de Contacto
                        </h2>
                        <div id="company-map"></div>
                    </div>
                <?php endif; ?>


                <!-- SEO Text Block -->
                <!-- SEO Text Block -->
                <div class="company-seo-block">
                    <h2 class="company-seo-title">Información General y de Contacto de <?= esc($companyName) ?></h2>
                    <p class="seo-text mb-4">
                        La empresa <strong><?= esc($companyName) ?></strong> cuenta con el <strong>CIF <?= esc($companyCif) ?></strong> y mantiene su 
                        <strong>domicilio social</strong> en 
                        <?php if(!empty($provinceUrl)): ?>
                            <a href="<?= esc($provinceUrl) ?>" style="color: inherit; font-weight: 700;"><?= esc($companyProv) ?></a>.
                        <?php else: ?>
                            <strong><?= esc($companyProv) ?></strong>.
                        <?php endif; ?>
                        
                        Esta sociedad desarrolla su actividad en el sector de 
                        <?php if(!empty($provinceCnaeUrl)): ?>
                            <a href="<?= esc($provinceCnaeUrl) ?>" style="color: inherit; font-weight: 700;"><?= esc($companyAct) ?></a>,
                        <?php elseif(!empty($cnaeUrl)): ?>
                            <a href="<?= esc($cnaeUrl) ?>" style="color: inherit; font-weight: 700;"><?= esc($companyAct) ?></a>,
                        <?php else: ?>
                            <em><?= esc($companyAct) ?></em>,
                        <?php endif; ?>
                        registrada bajo el código <strong>CNAE <?= esc($company['cnae'] ?? ($company['cnae_code'] ?? '-')) ?></strong>.
                    </p>
                    
                    <?php 
                        $objeto = $company['corporate_purpose'] ?? $company['objeto_social'] ?? '';
                        if(!empty($objeto)): 
                    ?>
                    <p class="seo-text mb-4">
                        <strong>Objeto Social:</strong> <?= esc(character_limiter($objeto, 300)) ?>
                    </p>
                    <?php endif; ?>

                    <p class="seo-text mb-0">
                        La fecha de constitución de la empresa es el <strong><?= esc($company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? '-') ?></strong> 
                        y su estado mercantil actual es <strong><?= esc($statusRaw) ?></strong>. 
                        En esta página podrá consultar el <strong>Informe Mercantil</strong>, el historial de <strong>Actos del BORME</strong>, 
                        sus <strong>administradores</strong> y la validación de su <strong>CIF</strong> para fines comerciales y financieros.
                    </p>
                    
                    <div class="company-share-row">
                        <span class="badge-demo badge-verified">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            Datos Verificados
                        </span>
                        <span class="badge-demo badge-official">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            Fuente Oficial
                        </span>
                        
                        <div class="share-buttons">
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($canonical) ?>" target="_blank" rel="noopener noreferrer nofollow" class="share-btn share-linkedin" title="Compartir en LinkedIn">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            </a>
                            <a href="https://api.whatsapp.com/send?text=<?= urlencode("Mira esta empresa: " . $canonical) ?>" target="_blank" rel="noopener noreferrer nofollow" class="share-btn share-whatsapp" title="Compartir en WhatsApp">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- BORME TIMELINE SECTION -->
                <?php if (!empty($bormePosts)): ?>
                <div style="margin-top: 4rem;">
                    <h2 style="font-size: 1.5rem; font-weight: 700; color: #0f172a; margin-bottom: 2.5rem; display: flex; align-items: center; gap: 12px;">
                        <span style="background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%); color: #fff; padding: 8px; border-radius: 10px; box-shadow: 0 4px 6px -1px rgba(14, 165, 233, 0.2);">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
                        </span>
                        Actos del Registro Mercantil (BORME)
                    </h2>
                    
                    <div class="borme-timeline">
                        <?php foreach ($bormePosts as $post): 
                            $acts = strtolower($post['act_types'] ?? '');
                            // Defaults: File Icon
                            $iconColor = '#64748b'; // Slate 500
                            $iconBg    = '#f1f5f9'; // Slate 100
                            $iconSvg   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>';

                            if (strpos($acts, 'nombramientos') !== false) {
                                $iconColor = '#16a34a'; // Green 600
                                $iconBg    = '#dcfce7'; // Green 100
                                // Briefcase Icon
                                $iconSvg   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="2" y="7" width="20" height="14" rx="2" ry="2"></rect><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"></path></svg>';
                            } elseif (strpos($acts, 'ceses') !== false || strpos($acts, 'dimisiones') !== false || strpos($acts, 'revocaciones') !== false) {
                                $iconColor = '#dc2626'; // Red 600
                                $iconBg    = '#fee2e2'; // Red 100
                                // File Minus/Remove Icon
                                $iconSvg   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="9" y1="15" x2="15" y2="15"></line></svg>';
                            } elseif (strpos($acts, 'cuentas') !== false) {
                                $iconColor = '#2563eb'; // Blue 600
                                $iconBg    = '#dbeafe'; // Blue 100
                                $iconSvg   = '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M4 22h14a2 2 0 0 0 2-2V7.5L14.5 2H6a2 2 0 0 0-2 2v4"></path><path d="M14 2v6h6"></path><path d="M3 15h6"></path><path d="M3 18h6"></path></svg>';
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
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"></path><polyline points="15 3 21 3 21 9"></polyline><line x1="10" y1="14" x2="21" y2="3"></line></svg>
                                        </a>
                                    <?php endif; ?>
                                </header>
                                <div class="borme-body">
                                    <h3 class="borme-title" style="margin-bottom: 12px; font-size: 1.1rem; line-height:1.4;">
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

                <!-- FAQ Section HTML -->
                <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #eee;">
                    <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: #444;">Preguntas Frecuentes sobre <?= esc($companyName) ?></h3>
                    <div style="display: grid; gap: 1.5rem;">
                        <?php foreach($faqs as $faq): ?>
                            <div>
                                <h4 style="font-size: 0.95rem; font-weight: 600; margin-bottom: 0.5rem; color: #111;"><?= esc($faq['q']) ?></h4>
                                <div style="font-size: 0.9rem; color: #555; line-height: 1.5;">
                                    <?= strip_tags(str_replace('**', '', $faq['a'])) // Limpieza básica para HTML visual ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                
                <!-- Schema.org JSON-LD -->
                <script type="application/ld+json">
                    <?= json_encode($schemaOrg, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?>
                </script>

            
            <div style="margin-top: 2rem; text-align: center;">
                <a href="<?= site_url('search_company') ?>" class="minor">← Volver al buscador</a>
            </div>

            <?php if (!empty($related)): ?>
            <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #eee;">
                <h3 style="font-size: 1.1rem; margin-bottom: 1.5rem; color: #444;">Empresas relacionadas en el sector y provincia</h3>
                
                <div class="table-container" style="background: #fff; border-radius: 12px; border: 1px solid #e2e8f0; overflow: hidden; box-shadow: 0 1px 2px rgba(0,0,0,0.04);">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.9rem;">
                        <thead>
                            <tr style="background: #f8fafc; border-bottom: 1px solid #e2e8f0;">
                                <th style="padding: 0.75rem 1rem; color: #64748b; font-weight: 600; font-size: 0.8rem; text-transform: uppercase;">Empresa</th>
                                <th style="padding: 0.75rem 1rem; color: #64748b; font-weight: 600; font-size: 0.8rem; text-transform: uppercase;">CIF</th>
                                <th style="padding: 0.75rem 1rem; color: #64748b; font-weight: 600; font-size: 0.8rem; text-transform: uppercase;">Ubicación</th>
                                <th style="padding: 0.75rem 1rem; color: #64748b; font-weight: 600; font-size: 0.8rem; text-transform: uppercase; text-align: right;">Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($related as $rel): 
                                $relSlug = url_title($rel['name'] ?? '', '-', true);
                                $relCif  = $rel['cif'] ?? '';
                                
                                if (preg_match('/^[A-Z][0-9]{7}[A-Z0-9]$/', $relCif)) {
                                    $relUrl = site_url($relCif . ($relSlug ? ('-' . $relSlug) : ''));
                                } else {
                                    $relUrl = site_url('empresa/' . ($rel['id'] ?? 0) . ($relSlug ? ('-' . $relSlug) : ''));
                                }
                            ?>
                            <tr style="border-bottom: 1px solid #f1f5f9; transition: background 0.15s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                                <td style="padding: 0.75rem 1rem;">
                                    <a href="<?= esc($relUrl) ?>" style="font-weight: 600; color: #0f172a; text-decoration: none;">
                                        <?= esc($rel['name'] ?? 'Empresa') ?>
                                    </a>
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #475569; font-family: monospace;">
                                    <?= esc($relCif ?: '-') ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; color: #475569;">
                                    <?= esc($rel['province'] ?? '-') ?>
                                </td>
                                <td style="padding: 0.75rem 1rem; text-align: right;">
                                    <a href="<?= esc($relUrl) ?>" title="Ver ficha de <?= esc($rel['name'] ?? 'empresa') ?>" style="color: #2152FF; font-weight: 600; text-decoration: none; font-size: 0.85rem;">
                                        Ficha completa →
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </section>
</main>

<?= view('partials/footer') ?>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" defer></script>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const btn = document.getElementById('btnToggleJson');
        const pre = document.getElementById('jsonBlock');
        
        if(btn && pre) {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const nowHidden = pre.classList.toggle('is-hidden');
                btn.textContent = nowHidden ? 'Ver JSON de la API' : 'Ocultar JSON de la API';
            });
        }

        <?php if (!empty($company['lat']) && !empty($company['lng'])): ?>
            // Coordinates appear to be swapped in the DB (Lng in Lat field)
            const lng = <?= (float)$company['lat'] ?>;
            const lat = <?= (float)$company['lng'] ?>;
            const companyName = "<?= esc($company['name'] ?? $company['nombre'] ?? 'Empresa') ?>";
            const rawAddress = "<?= esc($company['address'] ?? '') ?>";
            const province = "<?= esc($company['province'] ?? $company['provincia'] ?? '') ?>";

            // Initialize Map with a cleaner zoom
            const map = L.map('company-map', {
                scrollWheelZoom: false,
                zoomControl: true
            }).setView([lat, lng], 16);

            // Add Modern Tile Layer (CartoDB Voyager)
            L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors &copy; <a href="https://carto.com/attributions">CARTO</a>',
                subdomains: 'abcd',
                maxZoom: 20
            }).addTo(map);

            // Modern SVG Marker Icon
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

            // Add Marker
            L.marker([lat, lng], { icon: modernIcon }).addTo(map)
                .bindPopup(`<strong>${companyName}</strong><br><span style="color: #64748b; font-size: 0.85rem;">${rawAddress}${province ? ', ' + province : ''}</span>`)
                .openPopup();
        <?php endif; ?>
    });
</script>
</body>
</html>
