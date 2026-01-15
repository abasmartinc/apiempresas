<!doctype html>
<html lang="es">
<head>
    <?= view('partials/head', [
        'title'       => $title,
        'excerptText' => $meta_description,
        'canonical'   => $canonical,
        'robots'      => 'index,follow',
    ]) ?>
</head>
<body>
<div class="bg-halo" aria-hidden="true"></div>

<header>
    <div class="container nav">
        <div class="brand">
            <a href="<?=site_url() ?>">
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
            <a href="<?= site_url('search_company') ?>" style="color: inherit; text-decoration: none;">Buscador</a>
            <span style="margin: 0 0.5rem;">/</span>
            <span aria-current="page"><?= esc($company['name'] ?? 'Empresa') ?></span>
        </nav>

        <div class="search-card">
            
            <div class="result">
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
                    $companyAddr = "{$companyProv}, España"; // Simplificado si no tenemos dirección completa
                    $companyAct  = $company['cnae_label'] ?? 'su actividad registrada';

                    $faqs = [
                        [
                            'q' => "¿Cuál es el CIF de {$companyName}?",
                            'a' => "El CIF de {$companyName} es **{$companyCif}**. Este identificador fiscal es único para la empresa y sirve para realizar trámites y facturación."
                        ],
                        [
                            'q' => "¿A qué se dedica {$companyName}?",
                            'a' => "Según la clasificación CNAE, la actividad principal de {$companyName} es: **{$companyAct}**. Esta clasificación permite categorizar su sector de negocio."
                        ],
                        [
                            'q' => "¿Dónde está ubicada {$companyName}?",
                            'a' => "La empresa tiene su domicilio social registrado en **{$companyProv}**. Para notificaciones oficiales o contacto, debe dirigirse a esta ubicación."
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
                                    "addressRegion" => $companyProv
                                ],
                                "foundingDate" => $company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? '',
                                "description" => $meta_description ?? ''
                            ],
                            [
                                "@type" => "BreadcrumbList",
                                "itemListElement" => [
                                    [
                                        "@type" => "ListItem",
                                        "position" => 1,
                                        "name" => "Inicio",
                                        "item" => site_url()
                                    ],
                                    [
                                        "@type" => "ListItem",
                                        "position" => 2,
                                        "name" => "Buscador",
                                        "item" => site_url('search_company')
                                    ],
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
                            <h1 class="company-card__name" style="font-size: 1.5rem; margin: 0;"><?= esc($company['name'] ?? '-') ?></h1>
                            <div class="company-card__meta">
                                <?= esc(($company['cif'] ?? $company['nif'] ?? '-') . ' · ' . ($company['province'] ?? $company['provincia'] ?? '-')) ?>
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
                                    <?php if(!empty($company['province'] ?? $company['provincia'])): ?>
                                        <a href="<?= site_url('search_company?q=' . urlencode($company['province'] ?? $company['provincia'])) ?>" style="text-decoration: underline; color: inherit;">
                                            <?= esc($company['province'] ?? $company['provincia']) ?>
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </dd>
                            </div>
                            
                            <div><dt>Fecha de constitución</dt><dd><?= esc($company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? '-') ?></dd></div>
                            <div class="company-card__purpose"><dt>Objeto social</dt><dd><?= esc($company['corporate_purpose'] ?? $company['objeto_social'] ?? '-') ?></dd></div>
                        </dl>
                    </section>


                    <div class="company-card__footer">
                        <button type="button" class="btn-json-api" id="btnToggleJson">Ver JSON de la API</button>
                    </div>

                    <pre class="company-card__json is-hidden" id="jsonBlock"><code><?= esc($jsonPretty) ?></code></pre>
                </article>

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
            </div>
            
            <div style="margin-top: 2rem; text-align: center;">
                <a href="<?= site_url('search_company') ?>" class="minor">← Volver al buscador</a>
            </div>

            <?php if (!empty($related)): ?>
            <div style="margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #eee;">
                <h3 style="font-size: 1.1rem; margin-bottom: 1rem; color: #444;">Empresas relacionadas</h3>
                <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));">
                    <?php foreach ($related as $rel): 
                        $relSlug = url_title($rel['name'] ?? '', '-', true);
                        $relUrl  = site_url(($rel['cif'] ?? '') . ($relSlug ? ('-' . $relSlug) : ''));
                    ?>
                    <a href="<?= esc($relUrl) ?>" style="text-decoration: none; color: inherit; display: block; padding: 1rem; background: #f9fafb; border-radius: 8px; border: 1px solid #e5e7eb; transition: all 0.2s;" onmouseover="this.style.borderColor='#2152FF'" onmouseout="this.style.borderColor='#e5e7eb'">
                        <div style="font-weight: 600; font-size: 0.95rem; margin-bottom: 0.25rem; color: #111; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;"><?= esc($rel['name'] ?? 'Empresa') ?></div>
                        <div style="font-size: 0.8rem; color: #666;">
                            <?= esc($rel['province'] ?? '-') ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </section>
</main>

<?= view('partials/footer') ?>

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
    });
</script>
</body>
</html>
