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
    // --- VARIABLES DE ENTIDAD Y PERFIL ---
    $statusRaw = (string) ($company['status'] ?? '');
    $isActive = strtoupper($statusRaw) === 'ACTIVA';
    $statusClass = $isActive ? 'company-status company-status--active' : 'company-status company-status--inactive';

    // Estado registral EFECTIVO (motor + status). Manda sobre todo lo que sigue:
    // cinta, chip de estado, texto de presentación, FAQ y botones comerciales.
    // Ver company_estado_registral() en Helpers/company_helper.php.
    helper(['company', 'risk_labels']);
    $estadoReg = company_estado_registral($company, $riskProfile ?? null);

    $cnaeFull = (!empty($company['cnae']) && !empty($company['cnae_label']))
        ? ($company['cnae'] . ' · ' . $company['cnae_label'])
        : ($company['cnae_label'] ?? ($company['cnae'] ?? '-'));

    $jsonForCode = ['success' => true, 'data' => $company];
    $jsonPretty = json_encode($jsonForCode, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    $companyName = $company['name'] ?? ($company['company_name'] ?? 'Esta empresa');
    $companyCif = $company['cif'] ?? $company['nif'] ?? 'Desconocido';
    $companyProv = $company['province'] ?? $company['provincia'] ?? 'España';

    $rawAddr = $company['address'] ?? '';
    $companyAddr = $rawAddr ? "{$rawAddr}, {$companyProv}" : "{$companyProv}, España";
    $companyAct = $company['cnae_label'] ?? 'su actividad registrada';

    // Phone logic
    $phone = $company['phone'] ?? $company['phone_mobile'] ?? null;

    $adminNames = [];
    if (!empty($administrators)) {
        foreach (array_slice($administrators, 0, 3) as $adm) {
            $adminNames[] = $adm['name'];
        }
    }
    // La sección "Cargos Directivos" solo se pinta si hay administradores: sin
    // ellos, la FAQ mandaba a una sección que no existe en la página.
    if (!empty($adminNames)) {
        $adminResponse = ($estadoReg['cerrada']
                ? "Los últimos administradores y cargos que constan de **{$companyName}** son: **"
                : "Entre los administradores y cargos actuales de **{$companyName}** se encuentran: **")
            . implode(', ', $adminNames) . "**. Puede consultar el listado completo en la sección de Cargos Directivos de esta misma ficha. ";
    } else {
        $adminResponse = '';
    }

    // Respuestas que dependen del estado. Google enseña estas FAQ como respuesta
    // directa, así que no pueden describir como viva una empresa que no lo está.
    if ($estadoReg['incidencia']) {
        $faqFiable = "**{$companyName}** (CIF **{$companyCif}**) {$estadoReg['frase']}. "
            . ($estadoReg['cerrada']
                ? "Ya no desarrolla actividad mercantil con normalidad, así que no conviene contratar con ella ni venderle a crédito. "
                : "Antes de contratar con ella o venderle a crédito, conviene revisarlo con detalle. ")
            . "En esta ficha puede consultar su índice de estabilidad societaria y los actos publicados en el BORME.";
    } else {
        $faqFiable = "**{$companyName}** es una sociedad registrada en España con CIF **{$companyCif}**. Su estado actual es **{$statusRaw}**, según consta en el Registro Mercantil. Para valorar si es fiable como cliente o proveedor, consulte su índice de estabilidad societaria y los actos publicados en el BORME.";
    }

    if ($estadoReg['cerrada']) {
        $faqContacto = "El último domicilio social que consta de **{$companyName}** es **{$companyAddr}**. La sociedad {$estadoReg['frase']}, así que es probable que ya no atienda en esa dirección ni en sus antiguos teléfonos.";
    } else {
        $faqContacto = "La empresa tiene su domicilio social en **{$companyAddr}**."
            . ($phone ? " Su teléfono de contacto es **{$phone}**." : " No consta un teléfono de contacto público.");
    }

    $faqs = [
        [
            'q' => "¿Es fiable {$companyName}?",
            // Empezaba por "Sí," para TODAS las empresas, también las extinguidas o en
            // concurso: Google lo enseña como respuesta directa a "¿es fiable X?".
            'a' => $faqFiable
        ],
        [
            'q' => "¿Cómo consultar la solvencia y riesgo de impago de {$companyName}?",
            'a' => "En APIEmpresas puede obtener el **Informe de Solvencia de {$companyName}** (CIF {$companyCif}) en PDF. Incluye el índice de solvencia (Scoring IES), su estado en el Registro Mercantil, el histórico de actos publicados en el BORME y las incidencias detectadas."
        ],
        [
            'q' => "¿Cuál es el teléfono y dirección de {$companyName}?",
            'a' => $faqContacto
        ],
        [
            'q' => "¿Quiénes son los administradores de {$companyName}?",
            'a' => "{$adminResponse}En la sección de **Actos del BORME** puede revisar el histórico oficial de nombramientos, ceses y dimisiones desde su constitución."
        ]
    ];

    /*
     * Textos de IA escritos con un CNAE que luego se descartó (ver company_cnae_fiable).
     * Caso real: Camcomtur Georgia S.L., con 9900, tenía un texto de presentación que la
     * situaba "en el sector de actividades de organizaciones y organismos
     * extraterritoriales". Limpiar el CNAE no limpia lo que ya se generó a partir de él,
     * así que si el texto lo menciona, se usa la plantilla y las FAQ por defecto.
     */
    $cnaeDescartado = $cnaeDescartado ?? false;
    $iaConCnaeMalo = static function ($texto) use ($cnaeDescartado): bool {
        if (empty($cnaeDescartado) || !is_string($texto) || $texto === '') {
            return false;
        }
        return (bool) preg_match('/extraterritorial|9900/iu', $texto);
    };
    foreach (['ai_seo_text', 'ai_pitch'] as $iaCampo) {
        if ($iaConCnaeMalo($company[$iaCampo] ?? '')) {
            $company[$iaCampo] = '';
        }
    }

    // Sobrescribir con FAQs de IA si existen.
    // Salvo con un estado adverso: se generaron sin mirar el estado y hablan de la
    // empresa como si operara con normalidad.
    if (!empty($company['ai_faqs']) && !$estadoReg['incidencia'] && !$iaConCnaeMalo((string) $company['ai_faqs'])) {
        $aiFaqsDecoded = json_decode($company['ai_faqs'], true);
        if (json_last_error() === JSON_ERROR_NONE && !empty($aiFaqsDecoded) && is_array($aiFaqsDecoded)) {
            $faqs = $aiFaqsDecoded;
        }
    }

    // --- SCHEMA JSON-LD COMPLETO (@GRAPH) ---
    $organizationSchema = [
        '@type' => 'Organization',
        '@id'   => ($canonical ?? current_url()) . '#organization',
        'name'  => $companyName,
        'taxID' => $companyCif,
        'url'   => $canonical ?? current_url(),
        'description' => $meta_description ?? '',
        'logo'  => site_url('logo.png'),
        'knowsAbout' => [
            'Solvencia empresarial',
            'Scoring de solvencia',
            'Informes mercantiles',
            $company['cnae_label'] ?? 'Actividad empresarial'
        ],
        'makesOffer' => [
            [
                '@type' => 'Offer',
                'name' => 'Informe de Solvencia y Riesgo en PDF - ' . $companyName,
                'description' => 'Informe en PDF con el índice de solvencia, el estado registral, el histórico de actos del BORME y las incidencias detectadas.',
                'price' => '3.90',
                'priceCurrency' => 'EUR',
                'availability' => 'https://schema.org/InStock',
                'url' => $canonical ?? current_url()
            ],
            [
                '@type' => 'Offer',
                'name' => 'APIEmpresas Solvencia Pro - Scoring y vigilancia del BORME',
                'description' => 'Índice de solvencia, vigilancia del BORME de tu cartera y aviso por correo el día que se publique un acto nuevo.',
                'price' => '29.00',
                'priceCurrency' => 'EUR',
                'availability' => 'https://schema.org/InStock',
                'url' => $canonical ?? current_url()
            ]
        ]
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
        $organizationSchema['address'] = $addressData;
    }

    if (!empty($company['phone'])) {
        $organizationSchema['telephone'] = $company['phone'];
    }
    if (!empty($company['fecha_constitucion']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $company['fecha_constitucion'])) {
        $organizationSchema['foundingDate'] = $company['fecha_constitucion'];
    }

    if (!empty($administrators)) {
        $organizationSchema['employee'] = [];
        foreach (array_slice($administrators, 0, 10) as $adm) {
            $organizationSchema['employee'][] = [
                '@type' => 'Person',
                'name' => $adm['name'],
                'jobTitle' => $adm['position']
            ];
        }
    }

    // Sin aggregateRating: la nota venía de "¿Te ha sido útil esta información?", es
    // decir, valoraba la FICHA, y aquí se declaraba como valoración de la EMPRESA.
    // Además el widget ya no existe. Se deja el bloque desactivado por si se recupera
    // con una pregunta que sí valore a la empresa.
    if (false && !empty($ratingCount) && $ratingCount > 0) {
        $organizationSchema['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => round($ratingAvg, 1),
            'reviewCount' => (int)$ratingCount,
            'bestRating' => '5',
            'worstRating' => '1'
        ];
    }

    $breadcrumbElements = [
        [
            '@type' => 'ListItem',
            'position' => 1,
            'name' => 'Inicio',
            'item' => site_url()
        ]
    ];
    if (!empty($provinceUrl)) {
        $breadcrumbElements[] = [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Directorio',
            'item' => site_url('listado-de-empresas')
        ];
        $breadcrumbElements[] = [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => $company['province'] ?? $company['provincia'] ?? 'Provincia',
            'item' => $provinceUrl
        ];
        $breadcrumbElements[] = [
            '@type' => 'ListItem',
            'position' => 4,
            'name' => $companyName,
            'item' => $canonical ?? current_url()
        ];
    } else {
        $breadcrumbElements[] = [
            '@type' => 'ListItem',
            'position' => 2,
            'name' => 'Buscador',
            'item' => site_url('search_company')
        ];
        $breadcrumbElements[] = [
            '@type' => 'ListItem',
            'position' => 3,
            'name' => $companyName,
            'item' => $canonical ?? current_url()
        ];
    }

    $schemaGraph = [
        $organizationSchema,
        [
            '@type' => 'BreadcrumbList',
            'itemListElement' => $breadcrumbElements
        ],
        [
            '@type' => 'FAQPage',
            'mainEntity' => array_map(function ($item) {
                return [
                    '@type' => 'Question',
                    'name' => $item['q'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['a']
                    ]
                ];
            }, $faqs)
        ]
    ];

    if (!empty($company['lat']) && !empty($company['lng'])) {
        $schemaGraph[] = [
            '@type' => 'LocalBusiness',
            '@id' => ($canonical ?? current_url()) . '#localbusiness',
            'name' => $companyName,
            'address' => !empty($addressData) ? $addressData : null,
            // The map uses lat/lng as they come (lat_num / lng_num in CompanyModel) and
            // lands on the right street; the schema swapped them, which put every
            // company in the Indian Ocean. Guarded by range in case old rows are swapped:
            // Spain's latitudes are 27–44 and its longitudes -19–5.
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude'  => ((float) $company['lat'] >= 26 && (float) $company['lat'] <= 45) ? (float) $company['lat'] : (float) $company['lng'],
                'longitude' => ((float) $company['lat'] >= 26 && (float) $company['lat'] <= 45) ? (float) $company['lng'] : (float) $company['lat'],
            ],
            'url' => $canonical ?? current_url()
        ];
    }

    $schemaOrg = [
        '@context' => 'https://schema.org',
        '@graph' => array_values(array_filter($schemaGraph))
    ];
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

    <?= view('partials/header', ['force_public_header' => (!session('logged_in') && (int)(session('user_id') ?? 0) <= 0)]) ?>

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
                <div style="max-width: 1200px; margin: 0 auto; padding: 0px;">
                    <!-- HERO SECTION -->
                    <div class="b2b-header-wrapper"
                        style="padding: 0; margin-bottom: 24px;">
                        <div class="b2b-hero"
                            <?php /* Sin `overflow: hidden`. Lo llevaba para recortar la cinta de
                                     esquina, pero recortaba también el desplegable de "Descargar",
                                     que salía por debajo del borde y no se veía. Ahora el recorte
                                     lo hace una capa propia que solo envuelve a la cinta. */ ?>
                            style="position: relative; display: flex; align-items: center; gap: 32px; background: linear-gradient(135deg, #ffffff 0%, #f4f7fb 100%); padding: 40px; border-radius: 20px; box-shadow: 0 10px 40px -10px rgba(0,0,0,0.08), 0 1px 3px rgba(0,0,0,0.03); border: 1px solid rgba(226, 232, 240, 0.8);">
                            
                            <?php
                            $constValHeader = trim($company['incorporation_date'] ?? $company['founded'] ?? $company['fecha_constitucion'] ?? '');
                            $ribbonText = '';
                            $ribbonGradient = '';
                            $ribbonShadow = '';
                            
                            if ($estadoReg['incidencia']) {
                                // Con un estado adverso, la cinta dice el estado y no la edad:
                                // "Veterana (+10a)" en una extinguida se lee como solidez.
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
                            <!-- Capa de recorte SOLO para la cinta: mismo radio que la tarjeta,
                                 sin eventos de ratón para no tapar nada, y por debajo del menú. -->
                            <div style="position: absolute; inset: 0; overflow: hidden; border-radius: 20px; pointer-events: none; z-index: 1;">
                                <div style="position: absolute; top: 32px; right: -75px; width: 250px; text-align: center; background: <?= $ribbonGradient ?>; color: #fff; padding: 6px 0; font-size: 0.75rem; font-weight: 800; text-transform: uppercase; transform: rotate(45deg); box-shadow: 0 4px 12px <?= $ribbonShadow ?>; letter-spacing: 0.5px;">
                                    <?= esc($ribbonText) ?>
                                </div>
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

                                    <?php
                                    /*
                                     * RESUMEN DE RIESGO ARRIBA.
                                     *
                                     * El bloque de riesgo vive debajo de los datos generales y
                                     * mucha gente no llega a verlo. Esta etiqueta lo adelanta y
                                     * lleva hasta él. Enseña lo mismo que el teaser ya enseña
                                     * gratis a un anónimo (nivel e incidencias), nunca el detalle,
                                     * y no depende de quién mira: se puede cachear.
                                     * En modo 'opaco' no adelanta el resultado.
                                     */
                                    if (!empty($riskProfile)):
                                        helper(['company', 'risk_labels']);
                                        $rcScore = (int) ($riskProfile['risk_score'] ?? 0);
                                        $rcEventos = count($riskProfile['data']['canonical_events'] ?? []);
                                        $rcConf = isset($riskProfile['data']['confidence_score']) ? (int) $riskProfile['data']['confidence_score'] : null;
                                        $rcOpaco = solvencia('teaserModo', 'titular') === 'opaco';
                                        [$rcNivel, $rcColor, $rcFondo, $rcBorde] = risk_level_visual($rcScore);

                                        if (!$rcOpaco && $estadoReg['incidencia']) {
                                            // El estado registral va primero: "A revisar" de una
                                            // sociedad extinguida se queda muy corto.
                                            [$rcColor, $rcFondo, $rcBorde] = [$estadoReg['color'], $estadoReg['fondo'], $estadoReg['borde']];
                                            $rcTexto = esc($estadoReg['titulo'])
                                                . ($rcEventos > 0 ? ' · ' . $rcEventos . ($rcEventos === 1 ? ' incidencia' : ' incidencias') : '');
                                        } elseif ($rcOpaco || ($rcEventos === 0 && $rcConf !== null && $rcConf < 60)) {
                                            // Sin adelantar el resultado, o con un cero que no significa "limpia".
                                            [$rcColor, $rcFondo, $rcBorde] = ['#1d4ed8', '#eff6ff', '#bfdbfe'];
                                            $rcTexto = 'Perfil de riesgo disponible';
                                        } elseif ($rcEventos === 0) {
                                            $rcTexto = esc(ucfirst(mb_strtolower($rcNivel, 'UTF-8'))) . ' · sin incidencias en el BORME';
                                        } else {
                                            $rcTexto = esc(ucfirst(mb_strtolower($rcNivel, 'UTF-8'))) . ' · ' . $rcEventos
                                                . ($rcEventos === 1 ? ' incidencia registrada' : ' incidencias registradas');
                                        }
                                    ?>
                                        <a href="#perfil-de-riesgo" data-track-click="ficha_chip_riesgo"
                                           onclick="var d=document.getElementById('perfil-de-riesgo'); if(d&&d.scrollIntoView){event.preventDefault(); d.scrollIntoView({behavior:'smooth', block:'start'});}"
                                           style="display: inline-flex; align-items: center; gap: 6px; padding: 4px 12px; border-radius: 999px; background: <?= $rcFondo ?>; border: 1px solid <?= $rcBorde ?>; color: <?= $rcColor ?>; font-size: 0.75rem; font-weight: 800; line-height: 1.3; text-decoration: none;">
                                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                                            <span><?= $rcTexto ?></span>
                                            <span style="font-weight: 700; opacity: 0.85; text-decoration: underline;"><?= $rcEventos > 0 && !$rcOpaco ? 'ver por qué' : 'ver el análisis' ?> ↓</span>
                                        </a>
                                    <?php endif; ?>
                                    
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
                                    
                                    <?php if ($estadoReg['incidencia']): ?>
                                    <!-- Estado efectivo, no el literal de la tabla: "Disolución" en una
                                         sociedad que ya consta extinguida decía menos de lo que hay. -->
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

                                    <style>
                                    /* Menú de descargas de la cabecera. Un solo CTA primario:
                                       antes competían cuatro botones de peso visual idéntico. */
                                    /* z-index alto y contexto propio: la tarjeta tiene capas
                                       (cinta, avatar, degradado) y el menú tiene que ir sobre todas. */
                                    .cta-descargas { position: relative; z-index: 40; }
                                    .cta-descargas[open] { z-index: 60; }
                                    .cta-descargas > summary {
                                        display: flex; align-items: center; gap: 8px; padding: 8px 16px;
                                        background: #2563eb; color: #ffffff; font-size: 0.9rem; font-weight: 700;
                                        border-radius: 10px; border: 1px solid #2563eb; cursor: pointer;
                                        list-style: none; transition: all 0.2s; box-shadow: 0 4px 6px rgba(37, 99, 235, 0.25);
                                    }
                                    .cta-descargas > summary::-webkit-details-marker { display: none; }
                                    .cta-descargas > summary:hover { background: #1d4ed8; border-color: #1d4ed8; transform: translateY(-2px); }
                                    /* "Más": mismo menú, en secundario. IA y CRM se usan poco y
                                       competían en la cabecera con la única acción principal. */
                                    .cta-descargas.cta-mas > summary { background: #ffffff; color: #334155; border-color: #cbd5e1; box-shadow: none; }
                                    .cta-descargas.cta-mas > summary:hover { background: #f8fafc; border-color: #94a3b8; transform: none; }
                                    .cta-descargas.cta-mas .cta-descargas__menu { width: 260px; }
                                    .cta-descargas__menu {
                                        position: absolute; right: 0; top: calc(100% + 8px); z-index: 60;
                                        width: 320px; max-width: 80vw; background: #ffffff; border: 1px solid #e2e8f0;
                                        border-radius: 12px; box-shadow: 0 16px 36px -10px rgba(15, 23, 42, 0.24);
                                        padding: 6px; text-align: left;
                                    }
                                    .cta-descargas__menu > a,
                                    .cta-descargas__menu > button {
                                        display: block; width: 100%; box-sizing: border-box; text-align: left;
                                        background: none; border: none; cursor: pointer; padding: 10px 12px;
                                        border-radius: 9px; text-decoration: none; font-family: inherit;
                                    }
                                    .cta-descargas__menu > a:hover,
                                    .cta-descargas__menu > button:hover { background: #f1f5f9; }
                                    .cta-descargas__titulo { display: block; font-size: 0.9rem; font-weight: 800; color: #0f172a; }
                                    .cta-descargas__sub { display: block; font-size: 0.76rem; color: #64748b; line-height: 1.4; margin-top: 2px; }
                                    @media (max-width: 640px) {
                                        .cta-descargas__menu { right: auto; left: 0; }
                                    }
                                    </style>
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
                                        <?php if (!$estadoReg['cerrada']): ?>
                                        <?php /* Fuera en una empresa que ya no opera: preparar una llamada
                                                 comercial o mandarla al CRM no tiene sentido. */ ?>
                                        <details class="cta-descargas cta-mas">
                                            <summary aria-label="Más acciones">
                                                Más
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            </summary>
                                            <div class="cta-descargas__menu">
                                                <button type="button" onclick="this.closest('details').removeAttribute('open'); openCopilotModal('<?= esc($companyCif) ?>');"
                                                        data-track-click="company_more" data-track-element="copilot">
                                                    <span class="cta-descargas__titulo">✨ Preparar llamada con IA</span>
                                                    <span class="cta-descargas__sub">Guion y puntos clave antes de contactar</span>
                                                </button>
                                                <button type="button" onclick="this.closest('details').removeAttribute('open'); document.getElementById('crm-modal').style.display='flex';"
                                                        data-track-click="company_more" data-track-element="crm">
                                                    <span class="cta-descargas__titulo">Enviar a CRM</span>
                                                    <span class="cta-descargas__sub">Añadir esta empresa a tu CRM</span>
                                                </button>
                                            </div>
                                        </details>
                                        <?php endif; ?>
                                        <!-- Descargas.
                                             Aquí había dos botones, "Descargar informe" y "PDF Premium",
                                             que ni por el nombre ni por el icono decían en qué se
                                             diferencian: uno es la ficha gratis y el otro un informe de
                                             pago. Ahora es un solo menú donde cada opción dice qué es y
                                             cuánto cuesta. Se usa <details> a propósito: no necesita JS,
                                             así que funciona igual en la página cacheada. -->
                                        <details class="cta-descargas">
                                            <summary aria-label="Descargas de <?= esc($companyName) ?>">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><use href="#icon-b92ca97e"></use></svg>
                                                Descargar
                                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" aria-hidden="true" style="margin-left: 2px;"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            </summary>
                                            <div class="cta-descargas__menu">
                                                <a href="<?= site_url('empresa/export/' . $company['id']) ?>"
                                                   rel="nofollow"
                                                   onclick="window.dataLayer = window.dataLayer || []; window.dataLayer.push({'event': 'cta_pdf_click'});"
                                                   data-track-click="company_download" data-track-element="ficha_gratis">
                                                    <span class="cta-descargas__titulo">Ficha de la empresa</span>
                                                    <span class="cta-descargas__sub">Datos identificativos y de contacto &bull; PDF gratis</span>
                                                </a>
                                                <?php
                                                /*
                                                 * Dos variantes del mismo hueco, porque esta página va cacheada y no
                                                 * puede saber quién la está mirando. Nace la de pago —el visitante
                                                 * anónimo es la mayoría— y la hidratación enseña la otra cuando el
                                                 * usuario ya tiene derecho al informe (suscriptor, empresa ya
                                                 * consultada o comprada). Ninguna de las dos lleva dato de sesión,
                                                 * así que las dos son cacheables.
                                                 *
                                                 * El texto de pago ya no describe el contenido del informe: eso es
                                                 * justo lo que entrega el gratuito, y prometerlo aquí hacía que los
                                                 * 3,90 € parecieran cobrar por algo que ya se daba. Lo que compran
                                                 * es ver la empresa entera.
                                                 */
                                                ?>
                                                <a href="<?= site_url('empresa/export-risk/' . ($company['id'] ?? '')) ?>"
                                                   rel="nofollow" hx-boost="false"
                                                   data-descarga-informe="incluido" style="display: none;"
                                                   data-track-click="company_download" data-track-element="dictamen_incluido">
                                                    <span class="cta-descargas__titulo">Informe de riesgo y solvencia</span>
                                                    <span class="cta-descargas__sub">Ya incluido para ti &bull; descargar en PDF</span>
                                                </a>
                                                <button type="button"
                                                        data-descarga-informe="pago"
                                                        onclick="if (window.openRiskPdfModal) { openRiskPdfModal(<?= (int) $company['id'] ?>, '<?= esc($companyCif) ?>'); } else { window.location.href = '<?= site_url('perfil-de-riesgo') ?>?cif=<?= urlencode($companyCif) ?>'; }"
                                                        data-track-click="company_download" data-track-element="dictamen_riesgo">
                                                    <span class="cta-descargas__titulo">Ver esta empresa entera</span>
                                                    <span class="cta-descargas__sub">Dictamen completo en pantalla y en PDF &bull; <?= solvencia('precios.pdf', '3,90 €') ?> + IVA</span>
                                                </button>
                                                <button type="button"
                                                        onclick="document.getElementById('whitelabel-modal').style.display='flex'; if(window.trackEvent) trackEvent('premium_pdf_modal_opened');"
                                                        data-track-click="company_download" data-track-element="marca_blanca">
                                                    <?php /* El precio, como en las otras dos opciones. Era la única
                                                             sin cifra —"PDF gratis", "3,90 € + IVA" y esta en blanco—,
                                                             y una opción sin precio al lado de dos que sí lo tienen se
                                                             lee como la cara que se esconde. Sale de la MISMA clave
                                                             que usa su modal, así que no pueden divergir. */ ?>
                                                    <span class="cta-descargas__titulo">Informe de marca blanca</span>
                                                    <span class="cta-descargas__sub">El informe completo con tu logo, para enviar a un cliente &bull; <?= solvencia('precios.dossier', '5,90 €') ?> + IVA</span>
                                                </button>
                                            </div>
                                        </details>
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
    <div style="padding:18px 24px 0 24px;">
        <h2 style="font-size:1.05rem; font-weight:800; color:#0f172a; margin:0; line-height:1.3;">
            Información General y de Contacto de <?= esc($companyName) ?>
        </h2>
    </div>

    <!-- Texto SEO -->
    <div style="padding:8px 24px 18px 24px; line-height:1.65; color:#334155; font-size:0.98rem;">
        <?php if ($estadoReg['incidencia']): ?>
            <?php
            /*
             * Con un estado adverso, ni el texto de IA ni las diez plantillas de abajo
             * sirven: todas hablan de "sólida implantación", "operando activamente" o
             * "obligaciones al día". Aquí va un texto sobrio con lo que consta.
             */
            $epProv = !empty($provinceUrl) ? '<a href="' . esc($provinceUrl) . '" style="color:inherit;font-weight:700;">' . esc($companyProv) . '</a>' : '<strong>' . esc($companyProv) . '</strong>';
            $epCif  = (!empty($companyCif) && $companyCif !== 'Desconocido' && $companyCif !== '-') ? ' (CIF <strong>' . esc($companyCif) . '</strong>)' : '';
            $epAnio = '';
            $epFund = trim((string) ($company['founded'] ?? $company['incorporation_date'] ?? ''));
            if ($epFund !== '' && $epFund !== '0000-00-00' && $epFund !== '-' && preg_match('/^\d{4}/', $epFund)) {
                $epAnio = substr($epFund, 0, 4);
            }
            ?>
            <p>
                <strong><?= esc($companyName) ?></strong><?= $epCif ?> es una sociedad
                <?= $epAnio ? 'constituida en ' . esc($epAnio) . ' ' : '' ?>con domicilio social registrado en <?= $epProv ?>.
                Según los datos del Registro Mercantil, <strong><?= esc($estadoReg['frase']) ?></strong><?= $estadoReg['cerrada'] ? ', por lo que ya no desarrolla actividad mercantil con normalidad' : '' ?>.
                <?php if ($estadoReg['cerrada']): ?>
                    En esta ficha puede consultar su histórico de actos publicados en el BORME, sus últimos administradores conocidos y su índice de estabilidad societaria.
                <?php else: ?>
                    Antes de contratar con ella o venderle a crédito, conviene revisar su histórico de actos publicados en el BORME y su índice de estabilidad societaria.
                <?php endif; ?>
            </p>
        <?php elseif (!empty($company['ai_seo_text'])): ?>
            <?= nl2br(strip_tags($company['ai_seo_text'], '<strong><em><b><i><br><a><ul><li><ol><p>')) ?>
        <?php else: ?>
            <div id="fallback-seo-text">
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
                } elseif (!empty($sectorName) && !in_array(strtolower(trim($sectorName)), ['este sector', 'todos los sectores'], true)) {
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
        <?php endif; ?>
    </div>

    <?php /* Aquí iba un pie con "Datos Verificados" / "Fuente Oficial" y dos botones de
             compartir. Repetía la etiqueta "Datos oficiales Reg. Mercantil" y los tres
             botones de compartir de la cabecera, y "Verificados" es mucho decir de datos
             que a veces traen un CNAE por defecto. Eran unos 60 px sin información nueva. */ ?>
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
                                <li><a href="#api-dev-section">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                            stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><use href="#icon-7cb36ec4"></use></svg>
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
                                <?php // Sin el "CNAE 9900 - ..." copiado como objeto social: eso es el CNAE otra vez. ?>
                                <?php $objVal = company_sentence_case(company_objeto_social_real($company)); ?>
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
                            <?= view('partials/company_data_check', ['companyId' => (int) ($company['id'] ?? 0), 'lang' => 'es']) ?>
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

                    <?php
                    /*
                     * FRANJA DE LA API (24-09-2026)
                     * La API es el producto principal. Estaba a media página, en un bloque
                     * grande con botón verde, después del BORME. Ahora va aquí: justo
                     * después de la tabla de datos, que es cuando quien la mira piensa
                     * "esto lo quiero en mi software". Compacta para no empujar el bloque
                     * de riesgo, que es lo que busca la mayoría de quien llega de Google.
                     * Conserva id="api-dev-section": lo usan la pestaña "API", el enlace
                     * "(Consultar vía API)" del CIF y el aviso que salta al copiar el CIF.
                     */
                    $apiCif = (!empty($companyCif) && $companyCif !== 'Desconocido' && $companyCif !== '-') ? $companyCif : 'B12345678';
                    $apiJson = [
                        'cif'      => $apiCif,
                        'name'     => $companyName,
                        'province' => $companyProv,
                    ];
                    ?>
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
                        .api-strip__code .c-url{color:#e2e8f0}
                        .api-strip__code .c-dim{color:#64748b}
                        .api-strip__code pre{margin:6px 0 0;padding:0;background:transparent;border:0;color:#cbd5e1;white-space:pre;font:inherit}
                        @media (max-width:820px){.api-strip{grid-template-columns:1fr;padding:18px}}
                    </style>
                    <section id="api-dev-section" class="api-strip" aria-labelledby="api-strip-title">
                        <div>
                            <span class="api-strip__eyebrow">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><use href="#icon-7cb36ec4"></use></svg>
                                API REST
                            </span>
                            <h2 id="api-strip-title" class="api-strip__title">Estos datos, en tu software</h2>
                            <p class="api-strip__text">
                                Consulta <strong><?= esc($companyName) ?></strong> y cualquier empresa española por CIF con una sola llamada:
                                datos registrales, actos del BORME y perfil de riesgo, en JSON.
                            </p>
                            <div class="api-strip__actions">
                                <a class="api-strip__btn" href="<?= site_url('register') ?>" data-track-click="company_api" data-track-element="api_key">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><use href="#icon-b6af18a8"></use></svg>
                                    Obtener API key gratis
                                </a>
                                <a class="api-strip__link" href="<?= site_url('documentation') ?>" data-track-click="company_api" data-track-element="docs">Ver documentación →</a>
                            </div>
                        </div>
                        <div class="api-strip__code" aria-label="Ejemplo de llamada a la API">
                            <div><span class="c-verb">GET</span> <span class="c-url">/api/v1/companies?cif=<?= esc($apiCif) ?></span></div>
                            <div class="c-dim">Authorization: Bearer TU_API_KEY</div>
<pre><?= esc(json_encode($apiJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre>
                        </div>
                    </section>

                    <!-- RISK PROFILE SECTION -->
                    <?php if (!empty($riskProfile)): ?>
                        <div id="perfil-de-riesgo" class="b2b-card" style="margin-bottom:24px; padding: 0; overflow: hidden; position: relative; scroll-margin-top: 90px;">
                            
                            <!-- HEADER -->
                            <div style="padding: 24px; border-bottom: 1px solid #f1f5f9; display: flex; flex-wrap: wrap; gap: 12px; justify-content: space-between; align-items: flex-start; background: #fff;">
                                <div>
                                    <h2 class="no-after-line" style="font-size: 1.15rem; font-weight: 800; color: #0f172a; margin: 0 0 4px 0; display: flex; align-items: center; gap: 10px; text-transform: uppercase;">
                                        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="#3b82f6" stroke-width="2.5"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"></polyline></svg>
                                        Índice de Estabilidad Societaria
                                    </h2>
                                    <p style="margin: 0 0 12px 34px; font-size: 0.9rem; color: #64748b;">Calculado sobre los actos publicados en el BORME y el estado registral de la empresa.</p>
                                    <div style="margin-left: 34px; width: 60px; height: 3px; background: linear-gradient(90deg, #3b82f6 0%, #10b981 100%); border-radius: 2px;"></div>
                                </div>

                                <!-- VIGILAR EMPRESA
                                     Nace oculto y neutro a propósito. Esta cabecera forma parte
                                     del HTML que Cloudflare cachea, así que NO puede saber si
                                     este usuario vigila la empresa: si se renderizara el estado
                                     aquí, todos verían el del primero que pidió la página.
                                     Lo enciende la hidratación (`is_watching` del AJAX), que es
                                     lo único que ve la sesión real. -->
                                <!-- Botón + nota en una sola columna, para que la promesa
                                     cuelgue del botón y no del final de la cabecera.
                                     La nota no es un párrafo fijo: cambia con el estado.
                                     Apagado explica QUÉ GANAS si pulsas (que es lo que hace
                                     que se pulse); encendido confirma A DÓNDE llega el aviso
                                     (lo que evita el "¿se habrá guardado?" y el segundo clic
                                     que lo deshace). El `title` del botón no valía: en móvil
                                     no existe y en escritorio no lo lee nadie. -->
                                <div style="flex-shrink: 0; display: flex; flex-direction: column; align-items: flex-end; gap: 6px; max-width: 100%;">
                                    <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; justify-content: flex-end;">
                                        <!-- Estado de TU cuenta (plan o consultas restantes). Igual que el
                                             chip de vigilancia: nace oculto, porque la ficha va cacheada y
                                             esto depende de quién mira. Lo rellena la hidratación. -->
                                        <span id="risk-quota-pill" style="display: none; align-items: center; gap: 4px; padding: 4px 11px; border-radius: 999px; font-size: 0.72rem; font-weight: 800; white-space: nowrap;"></span>
                                    <button type="button"
                                            id="risk-watch-header"
                                            data-risk-watch
                                            data-cif="<?= esc($companyCif, 'attr') ?>"
                                            data-watching="0"
                                            style="display: none; align-items: center; gap: 7px; cursor: pointer; border-radius: 999px; padding: 7px 15px; font-size: 0.78rem; font-weight: 800; transition: all 0.15s; background: #ffffff; border: 1px solid #cbd5e1; color: #475569;">
                                        <span data-watch-icon>🔕</span>
                                        <span data-watch-label>Vigilar empresa</span>
                                    </button>
                                    </div>

                                    <div id="risk-watch-note"
                                         style="display: none; max-width: 280px; text-align: right; font-size: 0.75rem; line-height: 1.4; color: #64748b;"></div>
                                </div>
                            </div>

                            <div id="risk-profile-container" style="padding: 24px; position: relative; background: #fff; min-height: 260px;">
                                <?php if (session('logged_in') || (int)(session('user_id') ?? 0) > 0): ?>
                                    <?php if (!empty($riskQuota['allowed'])): ?>
                                        <?= view('partials/company_risk_profile', [
                                            'riskProfile' => $riskProfile,
                                            'company'     => $company,
                                            'contracts'   => $contracts ?? [],
                                            'subsidies'   => $subsidies ?? [],
                                            'riskQuota'   => $riskQuota ?? []
                                        ]) ?>
                                    <?php elseif (!empty($riskQuota['can_unlock'])): ?>
                                        <?= view('partials/company_risk_locked', [
                                            'riskProfile' => $riskProfile,
                                            'company'     => $company,
                                            'riskQuota'   => $riskQuota
                                        ]) ?>
                                    <?php else: ?>
                                        <?= view('partials/company_risk_paywall', [
                                            'company'     => $company,
                                            'riskQuota'   => $riskQuota,
                                            'riskProfile' => $riskProfile,
                                        ]) ?>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <?= view('partials/company_risk_teaser', [
                                        'riskProfile' => $riskProfile,
                                        'company'     => $company,
                                        'companyName' => $companyName ?? ($company['name'] ?? 'Empresa')
                                    ]) ?>
                                <?php endif; ?>
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
                    <?= view('partials/risk_pdf_modal', ['company' => $company]) ?>
                    <!-- END RISK PROFILE SECTION -->



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
                                    // Sin estas cuatro, una disolución o una extinción caían en
                                    // "Otros Actos", que se llevaba el 60 % de la barra justo en las
                                    // empresas donde más importa saber qué pasó.
                                    elseif (strpos($t, 'extinci') !== false) $t = 'Extinción';
                                    elseif (strpos($t, 'disoluci') !== false) $t = 'Disolución';
                                    elseif (strpos($t, 'liquidac') !== false) $t = 'Liquidación';
                                    elseif (strpos($t, 'concurs') !== false) $t = 'Concurso';
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

                            /*
                             * CUÁNDO MERECE LA PENA UN GRÁFICO.
                             * Con dos publicaciones, "Evolución de actividad" eran dos barras de
                             * altura 1 separadas por años, y la lista de debajo ya lo decía mejor.
                             * La evolución necesita al menos 6 publicaciones en 3 meses distintos;
                             * la distribución, al menos 4 actos de 2 tipos. Por debajo, solo la
                             * lista, que es donde está la información.
                             */
                            $verEvolucion    = count($bormePosts) >= 6 && count($bormeTimeline) >= 3;
                            $verDistribucion = $totalActs >= 4 && count($actCounts) >= 2;
                            $verResumenIa    = !empty($company['ai_borme_summary']);
                            ?>

                            <?php if ($verResumenIa || $verEvolucion || $verDistribucion): ?>
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
                                <?php if ($verResumenIa): ?>
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

                                <?php if ($verEvolucion): ?>
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

                                <?php if ($verDistribucion): ?>
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
                            <?php endif; ?>

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
                                                    <?php
                                                    // El BORME llega en MAYÚSCULAS. El titular del acto es
                                                    // vocabulario fijo y sin nombres propios, así que pasa a
                                                    // caja de frase sin riesgo.
                                                    $actoTitulo = trim((string)($post['act_types'] ?? ''));
                                                    echo esc(company_sentence_case($actoTitulo !== '' ? $actoTitulo : 'Acto registral'));
                                                    ?>
                                                </h3>
                                                <div>
                                                    <?php
                                                    // El cuerpo del asiento NO se pasa a minúsculas a propósito:
                                                    // lleva nombres de personas ("JUAN PEREZ GARCIA") y cualquier
                                                    // conversión automática los dejaría peor de lo que están.
                                                    // Sí se escapa antes de resaltar las etiquetas: venía del
                                                    // importador directo al HTML.
                                                    $desc = esc((string)($post['description'] ?? ''));
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



                    <?php /* Aquí iban las estrellas de "¿Te ha sido útil esta información?" y la
                             caja oscura de "Subir mis clientes". Las estrellas se quitaron: en un
                             registro mercantil, "Sé el primero en valorar" solo dice que la ficha
                             está vacía. "Clientes gemelos" pasa a la franja "Más herramientas". */ ?>

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
                    /*
                     * MÁS HERRAMIENTAS (24-09-2026)
                     * Sustituye a tres bloques grandes, cada uno con su estilo y su color:
                     * el CSV a todo ancho, el banner oscuro de Radar PRO y la caja de
                     * "Subir mis clientes". Se usan poco desde la ficha, así que van juntos,
                     * iguales y en segundo plano. La tarjeta del CSV conserva
                     * id="descargar-excel": el script de sesión la oculta a los registrados.
                     */
                    $ctaSinSector = in_array(trim((string) ($sectorName ?? '')), ['', 'todos los sectores', 'este sector'], true);
                    $ctaProv = !empty($targetProv) ? $targetProv : ($company['province'] ?? $company['registro_mercantil'] ?? 'España');
                    ?>
                    <style>
                        .tools-strip{margin:0 0 3rem}
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
                        <h3 id="tools-strip-title" class="tools-strip__title">Más herramientas</h3>
                        <div class="tools-strip__grid">
                            <a id="descargar-excel" class="tool-card" href="<?= $radarCheckoutUrl ?>" rel="nofollow"
                               onclick="window.dataLayer = window.dataLayer || []; window.dataLayer.push({'event': 'cta_excel_click'});"
                               data-track-click="company_tools" data-track-element="csv">
                                <span class="tool-card__head">
                                    <span class="tool-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><use href="#icon-6b651f08"></use></svg></span>
                                    <span class="tool-card__name">Listado en CSV</span>
                                </span>
                                <span class="tool-card__text">
                                    <?= $countFormatted ?> empresas<?= $ctaSinSector ? '' : ' de ' . esc(trim(explode('INFORME', $sectorName)[0])) ?> en <?= esc($ctaProv) ?>, con datos de contacto.
                                </span>
                                <span class="tool-card__cta">Descargar por <?= $priceStr ?> € + IVA →</span>
                            </a>
                            <a class="tool-card" href="<?= site_url('radar') ?>" data-track-click="company_tools" data-track-element="radar">
                                <span class="tool-card__head">
                                    <span class="tool-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><use href="#icon-1ba97933"></use></svg></span>
                                    <span class="tool-card__name">Radar de empresas nuevas</span>
                                </span>
                                <span class="tool-card__text">Las sociedades que se constituyen en tu sector y provincia, el día que salen en el BORME.</span>
                                <span class="tool-card__cta">Ver Radar →</span>
                            </a>
                            <a class="tool-card" href="<?= site_url('encontrar-empresas-similares') ?>"
                               onclick="if(window.trackEvent) trackEvent('click_lookalike_banner', { source: 'company_tools' });"
                               data-track-click="company_tools" data-track-element="lookalike">
                                <span class="tool-card__head">
                                    <span class="tool-card__icon"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true"><circle cx="9" cy="8" r="3.2"/><circle cx="16" cy="9.5" r="2.6"/><path d="M3.5 19c.6-3 3-5 5.5-5s4.9 2 5.5 5"/><path d="M14.5 14.6c.5-.2 1-.3 1.5-.3 2 0 3.7 1.5 4.2 3.8"/></svg></span>
                                    <span class="tool-card__name">Clientes gemelos</span>
                                </span>
                                <span class="tool-card__text">Sube tus mejores clientes y encuentra empresas parecidas por toda España.</span>
                                <span class="tool-card__cta">Probar →</span>
                            </a>
                        </div>
                    </section>

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
                            <strong style="color: #ffffff; display: block; margin-bottom: 4px; font-size: 1rem;">Índice de Estabilidad Societaria</strong>
                            <span style="color: #94a3b8; font-size: 0.9rem; line-height: 1.4; display: block;">Gravedad de lo que consta en el Registro Mercantil: concursos, disoluciones, cierres de hoja y cuentas sin depositar.</span>
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
                <p style="color: #475569; margin-bottom: 25px; line-height: 1.5;">Configura la Marca Blanca. Precio: <strong><?= solvencia('precios.dossier', '5,90 €') ?> + IVA</strong></p>

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
                             <div style="margin-bottom: 25px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #1e293b;">Enviar copia al correo electrónico</label>
                    <input type="email" name="email" placeholder="tu@email.com" style="width: 100%; padding: 12px; border: 1px solid #cbd5e1; border-radius: 8px; font-size: 1rem;">
                </div>

                <button type="submit" id="btn-whitelabel-submit" style="width: 100%; padding: 14px; background: #10b981; color: white; border: none; border-radius: 8px; font-weight: 700; font-size: 1.1rem; cursor: pointer; transition: background 0.2s;" onmouseover="this.style.background='#059669'" onmouseout="this.style.background='#10b981'">
                    Pagar <?= solvencia('precios.dossier', '5,90 €') ?> + IVA y Descargar
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
                formData.append('report_type', 'dossier');
                
                fetch('<?= site_url("empresa/checkout-premium-pdf") ?>', {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
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
                        statusArea.innerHTML = `<div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 14px; border-radius: 8px; margin-bottom: 15px; font-weight: bold; font-size: 0.88rem; text-align: center;">⚠️ ${data.message || 'Error'}</div>`;
                        btnSubmit.disabled = false;
                        btnSubmit.innerHTML = 'Pagar <?= solvencia('precios.dossier', '5,90 €') ?> + IVA y Descargar';
                    }
                })
                .catch(error => {
                    console.error(error);
                    statusArea.innerHTML = `<div style="background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; padding: 12px 14px; border-radius: 8px; margin-bottom: 15px; font-weight: bold; font-size: 0.88rem; text-align: center;">⚠️ Ocurrió un error en la conexión.</div>`;
                    btnSubmit.disabled = false;
                    btnSubmit.innerHTML = 'Pagar <?= solvencia('precios.dossier', '5,90 €') ?> + IVA y Descargar';
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
            
            // Desactivado (24-09-2026): un modal que salta a los 12 s para "Clientes
            // gemelos", un producto que apenas se usa, interrumpía justo a quien estaba
            // leyendo el bloque de riesgo o la API. Clientes gemelos sigue en la franja
            // "Más herramientas". Para reactivarlo, quitar `false &&`.
            if (false && shouldShow) {
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
    
    // Se usa el ORIGEN del navegador (same-origin, para que viajen las cookies de
    // sesión) + la RUTA BASE de la instalación.
    //
    // Aquí había un ternario que siempre devolvía '' y la URL quedaba colgando de
    // la raíz del dominio. En producción coincide y funciona; en una instalación
    // en subcarpeta (localhost/apiempresas) daba 404, así que la hidratación no
    // llegaba a ejecutarse NUNCA en local: ni el estado de vigilancia, ni la cuota
    // real, ni el bloque de riesgo del usuario. Se veía solo lo del HTML cacheado.
    const basePath = '<?= rtrim((string) (parse_url(site_url('/'), PHP_URL_PATH) ?: '/'), '/') ?>';
    const requestUrl = window.location.origin + basePath + '/api/empresa/private-data/' + encodeURIComponent(cif) + '?_ts=' + new Date().getTime();
    
    fetch(requestUrl, {
        method: 'GET',
        credentials: 'same-origin',
        cache: 'no-store',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Cache-Control': 'no-cache, no-store, must-revalidate',
            'Pragma': 'no-cache'
        }
    })
    .then(response => {
        if (!response.ok) throw new Error('Network response was not ok: ' + response.status);
        return response.json();
    })
    .then(data => {
        if (data.logged_in) {
            // Hide public CTAs
            const navCsv = document.getElementById('nav-descargar-csv');
            if (navCsv) navCsv.style.display = 'none';
            
            const ctaBanner = document.getElementById('descargar-excel');
            if (ctaBanner) ctaBanner.style.display = 'none';
            
            // Update Risk Profile container with private data (profile / locked / paywall)
            // Botón de vigilancia de la cabecera: el HTML cacheado lo sirve oculto
            // y en neutro; aquí es donde por fin sabemos el estado de ESTE usuario.
            const btnWatch = document.getElementById('risk-watch-header');
            if (btnWatch && data.risk_state && data.risk_state !== 'none') {
                // Se muestra con display, NO quitando un atributo `hidden`: el botón
                // trae `display` en su style inline y un estilo inline le gana al
                // `[hidden] { display: none }` del navegador. Con `hidden` el botón
                // se veía igualmente desde el primer pintado, con un estado que no
                // era el de nadie — y el primer clic partía de ahí, de modo que
                // hacían falta dos para dejarlo en "Vigilando".
                btnWatch.style.display = 'inline-flex';
                // Datos que solo conoce el servidor y que la nota necesita.
                btnWatch.dataset.alertsOn = data.watch_alerts_on ? '1' : '0';
                btnWatch.dataset.email    = data.watch_email || '';
                // Lista de vigilancia llena: lo sabe el servidor, así que se marca
                // aquí para que la caja de petición avise ANTES del clic.
                btnWatch.dataset.watchFull = data.watch_full ? '1' : '0';
                if (data.watch_quota && !data.watch_quota.ilimitado) {
                    btnWatch.dataset.watchTope = data.watch_quota.tope;
                }
                if (window.riskPintarVigilancia) {
                    window.riskPintarVigilancia(btnWatch, !!data.is_watching);
                }

                // Vuelta del registro desde "Avísame si cambia" (?vigilar=1): la
                // intención era explícita, así que se pone en vigilancia sin pedir
                // otro clic. Se hace con el MISMO botón y el mismo endpoint que el
                // clic manual, para que valgan las mismas reglas (tope de 5, aviso
                // de lista llena, avisos desactivados). Si ya la vigilaba no se
                // toca: el endpoint alterna, y pulsar aquí la quitaría.
                try {
                    const qsVig = new URLSearchParams(window.location.search);
                    if (qsVig.get('vigilar') === '1') {
                        qsVig.delete('vigilar');
                        const restoVig = qsVig.toString();
                        history.replaceState(null, '', window.location.pathname + (restoVig ? '?' + restoVig : '') + window.location.hash);

                        if (!data.is_watching) {
                            btnWatch.dataset.origen = 'teaser';   // para medir esta entrada
                            btnWatch.click();
                        }
                        btnWatch.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        if (window.trackEvent) window.trackEvent('risk_watch_from_teaser', { cif: btnWatch.dataset.cif || '' }, 'ficha');
                    }
                } catch (errVig) { /* sin URLSearchParams: se queda para el clic manual */ }

                // Plan o consultas restantes, al lado del chip de vigilancia.
                // El pintado vive en head.php porque el desbloqueo TAMBIÉN tiene que
                // repintarla: antes solo lo hacía la hidratación, así que después de
                // consultar una empresa la píldora se quedaba con el número de antes
                // y contradecía al upsell ("0 de 3" arriba, "te quedan 2" abajo).
                if (window.riskPintarCuota) window.riskPintarCuota(data.risk_quota);
            }

            // El menú "Descargar" se sirve cacheado ofreciendo la compra del informe.
            // Si este usuario ya tiene derecho a él, aquí es donde se entera.
            if (window.riskMenuDescargas) window.riskMenuDescargas(!!data.informe_gratis);

            const riskContainer = document.getElementById('risk-profile-container');
            if (riskContainer && data.risk_profile_html) {
                riskContainer.innerHTML = data.risk_profile_html;

                // La caja de "¿quieres vigilarla?" viaja DENTRO de este HTML, así que
                // hay que sincronizarla aquí: antes de esta línea todavía no existe
                // en el DOM y la llamada no encontraba nada que encender.
                if (window.riskSincronizarPeticion) window.riskSincronizarPeticion();

                // Por lo mismo: el upsell de Pro viaja dentro de este HTML, y su
                // argumento depende de si el usuario YA vigila esta empresa. El chip
                // de la cabecera se pintó unas líneas más arriba, cuando el upsell
                // todavía no estaba en el DOM.
                if (window.riskPintarUpsell) window.riskPintarUpsell();

                // El bloque llega por AJAX: hay que registrar sus impresiones a mano.
                if (window.trackObserveViews) window.trackObserveViews(riskContainer);

                // El dictamen ya es accesible (suscriptor o empresa ya desbloqueada):
                // se registra la consulta en el historial solo si el bloque llega a verse.
                // No consume cuota; el gasto real ocurre con el click de desbloqueo.
                // Vuelta del registro con ?ver-riesgo=1: la intención ya era explícita
                // (pulsó el CTA del teaser de esta empresa), así que se lleva la vista
                // al bloque y, si sigue bloqueado, se abre el dictamen sin pedir un
                // segundo click. El scroll NO depende de que haya botón: aunque el
                // bloque llegue ya desbloqueado o en paywall, el usuario vino a verlo.
                if (window.riskWantsAutoOpen && window.riskWantsAutoOpen()) {
                    if (window.riskScrollToBlock) window.riskScrollToBlock();

                    const autoBtn = (data.risk_state === 'locked')
                        ? riskContainer.querySelector('[data-risk-unlock]')
                        : null;

                    if (autoBtn) {
                        autoBtn.click();   // el handler global recoloca y limpia el flag
                    } else if (window.riskClearAutoFlag) {
                        window.riskClearAutoFlag();
                    }
                }

                if (data.risk_state === 'profile' && data.risk_cif && 'IntersectionObserver' in window) {
                    const observer = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (!entry.isIntersecting) return;
                            observer.disconnect();
                            fetch('<?= site_url('api/empresa/desbloquear-riesgo') ?>', {
                                method: 'POST',
                                credentials: 'same-origin',
                                cache: 'no-store',
                                keepalive: true,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                },
                                // 'passive': solo historial. Sin esto, a los suscriptores les entraría en
                                // vigilancia toda empresa cuyo dictamen se les abriera al hacer scroll.
                                body: JSON.stringify({ cif: data.risk_cif, mode: 'passive' })
                            }).catch(function () { /* el historial es best-effort */ });
                        });
                    }, { threshold: 0.35 });
                    observer.observe(riskContainer);
                }
            }
            
            // Sync Header UI with localStorage
            localStorage.setItem('is_logged_in', '1');
            
            const publicItems = document.querySelectorAll('.header-public-item');
            publicItems.forEach(el => el.style.setProperty('display', 'none', 'important'));
            
            const privateItems = document.querySelectorAll('.header-private-item');
            privateItems.forEach(el => el.style.setProperty('display', 'inline-flex', 'important'));
        } else {
            localStorage.removeItem('is_logged_in');
            const publicItems = document.querySelectorAll('.header-public-item');
            publicItems.forEach(el => el.style.setProperty('display', 'inline-flex', 'important'));
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




