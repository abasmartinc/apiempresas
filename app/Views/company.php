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
                    
                    // Dirección inteligente: si hay domicilio, úsalo. Si no, provincia.
                    $rawAddr     = $company['address'] ?? $company['address'] ?? '';
                    $companyAddr = $rawAddr ? "{$rawAddr}, {$companyProv}" : "{$companyProv}, España";
                    
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
                            'a' => "La empresa tiene su domicilio social registrado en **{$companyAddr}**. Para notificaciones oficiales o contacto, debe dirigirse a esta ubicación."
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


                    <div class="company-card__footer" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px dashed #eef2f7;">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                            <div>
                                <h3 style="font-size: 1rem; margin: 0 0 0.25rem; color: #1e293b;">¿Eres desarrollador?</h3>
                                <p style="margin: 0; font-size: 0.85rem; color: #64748b;">Integra los datos de <strong><?= esc($companyName) ?></strong> en tu software.</p>
                            </div>
                            <a href="<?= site_url('register') ?>" class="btn secondary" style="font-size: 0.9rem; padding: 0.6rem 1.2rem;">
                                Obtener API Key
                            </a>
                        </div>

                        <div class="code-card" style="margin-top: 1.25rem; background: #0f172a; border: 1px solid #1e293b;">
                            <div class="code-top" style="border-bottom: 1px solid #1e293b; padding: 8px 16px; display: flex; justify-content: space-between; align-items: center;">
                                <span style="font-size: 0.75rem; color: #94a3b8; font-family: monospace;">BASH</span>
                                <button type="button" id="btnToggleJson" style="background: none; border: none; color: #38bdf8; font-size: 0.75rem; cursor: pointer; text-decoration: underline;">Ver respuesta JSON</button>
                            </div>
                            <pre style="background: transparent; color: #e2e8f0; padding: 12px 16px; margin: 0; font-size: 0.8rem; overflow-x: auto;"><code>curl -X GET "https://apiempresas.es/api/v1/companies?cif=<?= esc($companyCif) ?>" \
     -H "Authorization: Bearer TU_API_KEY"</code></pre>
                        </div>
                    </div>
                    
                    <pre class="company-card__json is-hidden" id="jsonBlock" style="margin-top: 1rem; background: #0f172a; color: #e2e8f0; padding: 1rem; border-radius: 8px; overflow: auto; max-height: 400px; font-size: 0.8rem;"><code><?= esc($jsonPretty) ?></code></pre>
                </article>

                <!-- SEO Text Block -->
                <div style="margin-top: 2rem; background: #fff; border: 1px solid #e5e7eb; border-radius: 16px; padding: 24px; box-shadow: 0 4px 12px rgba(0,0,0,0.03);">
                    <h2 style="font-size: 1.2rem; margin-bottom: 1rem; color: #1e293b;">Información Comercial de <?= esc($companyName) ?></h2>
                    <p style="color: #475569; line-height: 1.7; font-size: 0.95rem; margin-bottom: 1rem;">
                        La empresa <strong><?= esc($companyName) ?></strong>, con NIF <strong><?= esc($companyCif) ?></strong>, 
                        tiene su domicilio social activo en <strong><?= esc($companyProv) ?></strong>. 
                        Esta sociedad opera en el sector de <em><?= esc($companyAct) ?></em>, 
                        clasificada bajo el código CNAE <strong><?= esc($company['cnae'] ?? '-') ?></strong>.
                    </p>
                    <p style="color: #475569; line-height: 1.7; font-size: 0.95rem; margin: 0;">
                        Su fecha de constitución data del <strong><?= esc($company['incorporation_date'] ?? $company['founded'] ?? '-') ?></strong>. 
                        Actualmente, su estado mercantil es <strong><?= esc($statusRaw) ?></strong>. 
                        Puede consultar más información sobre sus actos registrales, cuentas anuales y crédito comercial a través de nuestra API de información empresarial.
                    </p>
                    
                    <div style="margin-top: 1.5rem; display: flex; gap: 10px;">
                        <span class="badge-demo" style="display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 0.8rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            Datos Verificados
                        </span>
                        <span class="badge-demo" style="background: #f0f9ff; color: #0369a1; border-color: #e0f2fe; display: inline-flex; align-items: center; gap: 6px; padding: 6px 12px; font-size: 0.8rem;">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="2" y1="12" x2="22" y2="12"/><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/></svg>
                            Fuente Oficial
                        </span>
                        
                        <div style="margin-left: auto; display: flex; gap: 8px;">
                            <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($canonical) ?>" target="_blank" rel="noopener noreferrer" style="color: #0a66c2; background: #f1f5f9; padding: 6px; border-radius: 6px; display: flex; align-items: center;" title="Compartir en LinkedIn">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            </a>
                            <a href="https://api.whatsapp.com/send?text=<?= urlencode("Mira esta empresa: " . $canonical) ?>" target="_blank" rel="noopener noreferrer" style="color: #25D366; background: #f1f5f9; padding: 6px; border-radius: 6px; display: flex; align-items: center;" title="Compartir en WhatsApp">
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            </a>
                        </div>
                    </div>
                </div>

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
