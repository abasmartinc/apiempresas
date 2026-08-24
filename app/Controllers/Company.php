<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\BormePostsModel;
use App\Models\CompanyAdministratorModel;
use App\Models\CompanyRatingModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Dompdf\Dompdf;
use Dompdf\Options;

class Company extends BaseController
{
    /** @var CompanyModel */
    protected $companyModel;
    /** @var BormePostsModel */
    protected $bormePostsModel;
    /** @var CompanyAdministratorModel */
    protected $adminModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->bormePostsModel = new BormePostsModel();
        $this->adminModel = new CompanyAdministratorModel();
        helper(['text', 'seo_dynamic', 'company']); // Cargar text para url_title, helper SEO, y helper company
    }

    /**
     * Muestra ficha por ID (para empresas sin CIF).
     * Ruta: /empresa/{id}-{slug}
     */
    public function showById($id, $slug = null)
    {
        $id = (int)$id;
        $company = $this->companyModel->getById($id);

        if (!$company && !empty($slug)) {
            // FALLBACK: Si el ID no existe (ej: link antiguo indexado), intentar buscar por slug
            return $this->handleSlugUrl($slug);
        }

        if (!$company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // REDIRECCIÓN 301: Mandar siempre a la nueva URL canónica
        $slug = $this->companyModel->generateSlug($company['name']);
        if (!empty($company['cif'])) {
            $canonicalUrl = site_url($company['cif'] . ($slug ? ('-' . $slug) : ''));
            return redirect()->to($canonicalUrl, 301);
        }

        // Si no tiene CIF, la URL ahora es simplemente el slug
        return redirect()->to(site_url($slug), 301);
    }

    /**
     * Lógica común para preparar datos de la vista
     */
    private function prepareViewData(array $company): array
    {
        // Check if company has requested privacy opt-out (Right to be Forgotten)
        if (!empty($company['cif'])) {
            $db = \Config\Database::connect();
            $isOptedOut = $db->table('company_privacy_optouts')->where('cif', $company['cif'])->countAllResults() > 0;
            if ($isOptedOut) {
                throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Perfil de empresa eliminado por privacidad.');
            }
        }

        $statusRaw = (string)($company['status'] ?? '');
        $isActive  = strtoupper($statusRaw) === 'ACTIVA';
        
        $isEn = (service('request')->getLocale() === 'en');

        // Generar título y descripción
        $rawName = $company['name'] ?? ($isEn ? 'Company' : 'Empresa');
        $name = mb_convert_case(mb_strtolower($rawName, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
        // Arreglar abreviaturas comunes (SL, SA, etc)
        $name = str_replace([' S.l.', ' S.l', ' Sl', ' Sl.', ' S.L.'], ' S.L.', $name);
        $name = str_replace([' S.a.', ' S.a', ' Sa', ' Sa.', ' S.A.'], ' S.A.', $name);
        // Actualizamos en el array para que las vistas también lo usen
        $company['name'] = $name;

        $cif  = $company['cif'] ?? $company['nif'] ?? '';
        
        // Robust check for province
        $prov = '';
        if (!empty($company['province'])) {
            $prov = $company['province'];
        } elseif (!empty($company['provincia'])) {
            $prov = $company['provincia'];
        }
        
        if ($isEn) {
            $title = "{$name} - VAT {$cif}, Phone, Address and Directors";
            if ($prov) $title .= " | {$prov}";
            $title .= " - SpainCompanyAPI.com";

            $desc = "All commercial information for {$name}";
            if ($cif) {
                $desc .= " (VAT {$cif})";
            }
            if ($prov) {
                $desc .= " in {$prov}";
            }
            $desc .= ". Get the phone number, address, board members, financials, and BORME registry acts.";
        } else {
            $title = "{$name} - CIF {$cif}, Teléfono, Dirección y Cargos";
            if ($prov) $title .= " | {$prov}";
            $title .= " - APIEmpresas.es";

            $desc = "Toda la información mercantil de {$name}";
            if ($cif) {
                $desc .= " (CIF {$cif})";
            }
            if ($prov) {
                $desc .= " en {$prov}";
            }
            $desc .= ". Conoce su teléfono, dirección, cargos directivos, balances y actos en el BORME.";
        }
        
        $desc = character_limiter($desc, 155, '');

        // Related companies
        $related = $this->companyModel->getRelated(
            $company['cnae'] ?? null,
            $prov,
            $company['cif'] ?? 'NO_CIF_' . ($company['id'] ?? 0)
        );

        $db = \Config\Database::connect();

        // Fetch Contracts & Subsidies
        $contracts = [];
        $subsidies = [];
        if (!empty($cif)) {
            $contracts = $db->table('company_contracts')
                ->where('company_cif', $cif)
                ->orderBy('fecha_adjudicacion', 'DESC')
                ->get()->getResultArray();
                
            $subsidies = $db->table('company_subsidies')
                ->where('company_cif', $cif)
                ->orderBy('fecha_concesion', 'DESC')
                ->get()->getResultArray();
        }

        // Breadcrumb Links
        $provinceUrl = '';
        if ($prov) {
            $provinceUrl = site_url('listado-de-empresas/' . urlencode($prov));
        }
        
        $cnaeCode = '';
        if (!empty($company['cnae_code'])) {
            $cnaeCode = $company['cnae_code'];
        } elseif (!empty($company['cnae'])) {
             $cnaeCode = $company['cnae'];
        }

        $cnaeUrl = '';
        if ($cnaeCode) {
             helper('text');
             $cnaeSlug = url_title($company['cnae_label'] ?? "CNAE {$cnaeCode}", '-', true);
             $cnaeUrl = site_url('listado-de-empresas/sector-' . $cnaeCode . '/' . $cnaeSlug);
        }

        $provinceCnaeUrl = '';
        if ($prov && $cnaeCode) {
            $provinceCnaeUrl = site_url('listado-de-empresas/' . urlencode($prov) . '/sector-' . $cnaeCode);
        }

        // --- ASYNC AI SEO TEXT GENERATION (QUEUE) ---
        if (empty($company['ai_seo_text']) && !empty($company['id'])) {
            $agent = service('request')->getUserAgent();
            $isBot = $agent->isRobot();
            if (!$isBot) {
                $userAgentString = strtolower($agent->getAgentString());
                $botKeywords = ['googlebot', 'bingbot', 'yandex', 'baidu', 'duckduck', 'crawler', 'spider', 'archiver'];
                foreach ($botKeywords as $kw) {
                    if (strpos($userAgentString, $kw) !== false) {
                        $isBot = true;
                        break;
                    }
                }
            }

            // Apuntar en la cola en lugar de llamar a OpenAI para no bloquear la respuesta
            if ($isBot) {
                $db = \Config\Database::connect();
                // Usamos IGNORE para no duplicar si ya está en cola
                $db->query("INSERT IGNORE INTO seo_generation_queue (company_id, requested_at, status) VALUES (?, ?, 'pending')", [$company['id'], date('Y-m-d H:i:s')]);
            }
        }
        // --- ASYNC AI SEO TEXT GENERATION (QUEUE) ---

        // --- FETCH ADMINS & BORME FOR SEO SCORE ---
        // Fetch Administrators early for SEO calculation
        $adminsRaw = $this->adminModel->getByCompanyId((int)$company['id']);
        $company['num_admins'] = count($adminsRaw);
        
        // Fetch BORME early for SEO calculation
        $bormePosts = $this->bormePostsModel->getByCompanyId((int)$company['id']);
        $company['num_borme_posts'] = count($bormePosts);

        // --- DINAMIC SEO INDEXING ---
        $indexable = shouldIndexCompany($company);
        $robots    = $indexable ? 'index, follow' : 'noindex, follow';
        
        // Si no es indexable, añadir cabecera HTTP (X-Robots-Tag)
        if (!$indexable) {
            // Nota: CodeIgniter 4 maneja la respuesta mediante el servicio response
            service('response')->setHeader('X-Robots-Tag', 'noindex, follow');
        }
        
        // Aadir flag al objeto empresa para uso en sitemaps/logs
        $company['seo_indexable'] = $indexable;
        $company['seo_score']     = calculateCompanySeoScore($company);
        // --- DINAMIC SEO INDEXING ---

        // Administrators (Already fetched above for SEO score)
        $filteredAdmins = [];
        $excludeKeywords = ['CAPITAL', 'DOMICILIO', 'OBJETO SOCIAL', 'OTROS CONCEPTOS', 'COMIENZO DE OPERACIONES', 'INSCRIPCION', 'RESULTANTE', 'SUSCRITO', 'EURO', 'REMITIDO'];
        $seenAdmins = [];

        foreach ($adminsRaw as $admin) {
            $nameStr = strtoupper($admin['name'] ?? '');
            $posStr = strtoupper($admin['position'] ?? '');
            $combinedText = $nameStr . ' ' . $posStr;

            $exclude = false;
            foreach ($excludeKeywords as $kw) {
                if (strpos($combinedText, $kw) !== false) {
                    $exclude = true;
                    break;
                }
            }
            // Tambien excluir si el nombre contiene números (ej: CIFs o Importes)
            if ($exclude || preg_match('/[0-9]+/', $nameStr)) continue;

            $uniqueKey = md5(trim($nameStr) . '|' . trim($posStr));
            if (isset($seenAdmins[$uniqueKey])) continue;

            $seenAdmins[$uniqueKey] = true;
            $filteredAdmins[] = $admin;
        }

        helper('company');
        $filteredAdmins = group_administrators($filteredAdmins);

        // Calcular datos para el CTA B2B (Movido desde la vista)
        $companyProv = !empty($company['province']) ? $company['province'] : (!empty($company['registro_mercantil']) ? $company['registro_mercantil'] : 'España');
        $cnaeCodeStr = substr($company['cnae_code'] ?? $company['cnae'] ?? '', 0, 4);
        $cnaeUrlParam = urlencode($cnaeCodeStr);
        $provUrlParam = urlencode($companyProv);
        $sectorName = $company['cnae_label'] ?? 'este sector';
        
        // $db is already connected above
        
        // Caching the count query results to avoid Database connections exhaustion
        $cacheKey = 'count_cta_' . md5($companyProv . '_' . $cnaeCodeStr);
        $cachedData = null;
        try {
            $cachedData = cache($cacheKey);
        } catch (\Throwable $e) {
            log_message('error', 'Cache read error for ' . $cacheKey . ': ' . $e->getMessage());
        }

        if (is_array($cachedData)) {
            $listCount = $cachedData['count'] ?? 0;
            $targetProv = $cachedData['targetProv'] ?? $companyProv;
            $provUrlParam = $cachedData['provUrlParam'] ?? $provUrlParam;
            $cnaeUrlParam = $cachedData['cnaeUrlParam'] ?? $cnaeUrlParam;
            $sectorName = $cachedData['sectorName'] ?? $sectorName;
        } else {
            // 1. Intentar Sector + Provincia
            $builder = $db->table('companies');
            if ($cnaeCodeStr) $builder->where('cnae_code LIKE', $cnaeCodeStr . '%');
            $builder->where('fecha_constitucion IS NOT NULL'); // Consistente con el export
            if ($companyProv && strtolower($companyProv) !== 'españa') {
                if (strtolower($companyProv) === 'alicante') {
                    $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
                } else {
                    $builder->where('registro_mercantil', $companyProv);
                }
            }
            $listCount = $builder->countAllResults();
            $targetProv = $companyProv;

            // 2. Fallback: Si hay menos de 50 empresas, ampliar a TODA ESPAÑA para ese sector
            if ($listCount < 50 && $cnaeCodeStr) {
                $builder2 = $db->table('companies');
                $builder2->where('cnae_code LIKE', $cnaeCodeStr . '%');
                $builder2->where('fecha_constitucion IS NOT NULL');
                $listCount = $builder2->countAllResults();
                $targetProv = 'toda España';
                $provUrlParam = 'España';
            }

            // 3. Fallback: Si AÚN hay menos de 50 (sector rarísimo), ofrecer TODA LA PROVINCIA (sin sector)
            if ($listCount < 50 && $companyProv && strtolower($companyProv) !== 'españa') {
                $builder3 = $db->table('companies');
                $builder3->where('fecha_constitucion IS NOT NULL');
                if (strtolower($companyProv) === 'alicante') {
                    $builder3->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
                } else {
                    $builder3->where('registro_mercantil', $companyProv);
                }
                $listCount = $builder3->countAllResults();
                $targetProv = $companyProv;
                $provUrlParam = urlencode($companyProv);
                $cnaeUrlParam = ''; // Quitamos el filtro de sector
                $sectorName = 'todos los sectores';
            }

            cache()->save($cacheKey, [
                'count' => $listCount,
                'targetProv' => $targetProv,
                'provUrlParam' => $provUrlParam,
                'cnaeUrlParam' => $cnaeUrlParam,
                'sectorName' => $sectorName
            ], 86400 * 7); // Cache for 7 days
        }

        $sectorUrlParam = urlencode($sectorName);
        $radarCheckoutUrl = site_url("checkout/radar-export?type=single&provincia={$provUrlParam}&cnae={$cnaeUrlParam}&sector={$sectorUrlParam}");
        
        helper('pricing');
        
        // Fallback en caso de que el helper de CodeIgniter falle silenciosamente
        if (!function_exists('calculate_directory_price')) {
            $helperPath = APPPATH . 'Helpers/pricing_helper.php';
            if (file_exists($helperPath)) {
                require_once $helperPath;
            } else {
                log_message('error', 'pricing_helper.php no se encontró en ' . $helperPath);
            }
        }

        // Definición inline de emergencia por si el archivo físico no existe en producción
        if (!function_exists('calculate_directory_price')) {
            function calculate_directory_price(int $count, bool $isPremium = false): array {
                $basePrice = 9.00;
                if ($count > 1000) {
                    $extraCount = $count - 1000;
                    $tier2Count = min($extraCount, 9000);
                    $basePrice += ceil($tier2Count / 1000) * 5.00;
                    if ($extraCount > 9000) {
                        $basePrice += ceil(($extraCount - 9000) / 1000) * 1.00;
                    }
                }
                if ($isPremium) $basePrice = round($basePrice * 1.5, 2);
                return [
                    'base_price' => $basePrice,
                    'tax' => round($basePrice * 0.21, 2),
                    'total' => $basePrice + round($basePrice * 0.21, 2)
                ];
            }
        }

        $pricing = calculate_directory_price($listCount);
        $priceStr = number_format($pricing['base_price'], 0, ',', '.');
        $countFormatted = number_format($listCount, 0, ',', '.');

        // (Eliminada la sobreescritura de meta_description con ai_seo_text para proteger el CIF)

        $ratingModel = new \App\Models\CompanyRatingModel();
        $ratingStats = $ratingModel->getRatingStats((int)$company['id']);

        // --- HOLDINGS LOGIC ---
        $holdingData = null;
        $holdingGraphData = null;
        $holdingCompanies = [];
        
        $holdingRow = $db->table('company_holdings')
            ->select('holdings.id, holdings.name, holdings.slug')
            ->join('holdings', 'holdings.id = company_holdings.holding_id')
            ->where('company_holdings.company_id', $company['id'])
            ->get()->getRowArray();
            
        if ($holdingRow) {
            $holdingRow['name'] = preg_replace('/^grupo\s+/i', '', trim($holdingRow['name']));
            $holdingData = $holdingRow;
            $companyHoldingModel = new \App\Models\CompanyHoldingModel();
            $holdingCompanies = $companyHoldingModel->getCompaniesByHolding($holdingRow['id'], 100);
            $totalHoldingCompaniesCount = $companyHoldingModel->getTotalCompaniesByHolding($holdingRow['id']);
            
            // Build Graph Data for Vis.js
            $nodes = [];
            $edges = [];
            
            // Central Node (Holding)
            $nodes[] = [
                'id' => 'h_' . $holdingRow['id'],
                'label' => $holdingRow['name'],
                'shape' => 'box',
                'color' => [
                    'background' => '#1a202c',
                    'border' => '#0f172a'
                ],
                'font' => ['color' => '#ffffff', 'size' => 16, 'face' => 'Inter', 'bold' => true],
                'margin' => 12
            ];
            
            foreach ($holdingCompanies as $hc) {
                $isCurrent = ($hc['id'] == $company['id']);
                $capital = (float)$hc['social_capital'];
                
                // Calcular tamaño dinámico (escala logarítmica para evitar nodos gigantes)
                $nodeSize = 12; // Base
                if ($capital > 0) {
                    $nodeSize = 12 + (log10($capital) * 3);
                    if ($nodeSize > 35) $nodeSize = 35; // Cap máximo
                }
                if ($isCurrent && $nodeSize < 22) $nodeSize = 22; // Resaltar el actual

                $estado = esc($hc['status'] ?? 'Desconocido');
                $provincia = esc(ucwords(strtolower($hc['province'] ?? '')));
                $nodes[] = [
                    'id' => 'c_' . $hc['id'],
                    // Sin 'label' para evitar la bola de pelo de textos solapados
                    'shape' => 'dot',
                    'color' => $isCurrent ? '#4F46E5' : '#94a3b8', // Añil si es actual, gris azulado para hermanas
                    'title' => "{$hc['name']}\nCIF: {$hc['cif']}\nProvincia: {$provincia}\nEstado: {$estado}",
                    'size' => $nodeSize
                ];
                
                $edges[] = [
                    'from' => 'h_' . $holdingRow['id'],
                    'to' => 'c_' . $hc['id'],
                    'color' => '#cbd5e1',
                    'length' => 150
                ];
            }
            
            $holdingGraphData = [
                'nodes' => $nodes,
                'edges' => $edges
            ];
        }
        // --- END HOLDINGS LOGIC ---

        // --- RISK PROFILE LOGIC ---
        $riskProfile = null;
        if (!empty($cif)) {
            $riskRow = $db->table('company_risk_profiles')->where('cif', $cif)->get()->getRowArray();
            if ($riskRow) {
                $riskProfile = $riskRow;
                if (!empty($riskProfile['risk_profile'])) {
                    $riskProfile['data'] = json_decode($riskProfile['risk_profile'], true);
                }
            }
        }
        // --- END RISK PROFILE LOGIC ---

        return [
            'companyName'      => $name,
            'company'          => $company,
            'riskProfile'      => $riskProfile,
            'holdingData'      => $holdingData ?? null,
            'holdingCompanies' => $holdingCompanies ?? [],
            'holdingGraphData' => $holdingGraphData ?? null,
            'totalHoldingCompaniesCount' => $totalHoldingCompaniesCount ?? 0,
            'statusRaw'        => $statusRaw,
            'statusClass'      => $isActive ? 'company-status company-status--active' : 'company-status company-status--inactive',
            'companyCif'       => $cif, // Pasamos el cif limpio a la vista
            'title'            => $title,
            'meta_description' => $desc,
            'robots'           => $robots,
            'ratingAvg'        => $ratingStats['avg'],
            'ratingCount'      => $ratingStats['count'],
            'related'          => $related,
            'bormePosts'       => $bormePosts, // Already fetched above
            'administrators'   => $filteredAdmins,
            'provinceUrl'      => $provinceUrl,
            'cnaeUrl'          => $cnaeUrl,
            'provinceCnaeUrl'  => $provinceCnaeUrl,
            'radarCheckoutUrl' => $radarCheckoutUrl,
            'totalCnae'        => $listCount,
            'priceStr'         => $priceStr,
            'pricing'          => $pricing,
            'sectorName'       => $sectorName,
            'targetProv'       => $targetProv,
            'contracts'        => $contracts,
            'subsidies'        => $subsidies,
            'countFormatted'   => $countFormatted,
            'holdingData'      => $holdingData,
            'holdingCompanies' => $holdingCompanies,
            'holdingGraphData' => $holdingGraphData,
        ];
    }

    public function show($segment)
    {
        // 1. Detect format (CIF-based vs Slug-based)
        // CIFs are usually 9 chars at the beginning (A12345678)
        $potentialCif = substr($segment, 0, 9);
        $isCifFormat = preg_match('/^[A-Z][0-9]{7}[A-Z0-9]$/i', $potentialCif);

        if ($isCifFormat) {
            return $this->handleCifUrl($segment);
        }
        
        return $this->handleSlugUrl($segment);
    }
    
    /**
     * Maneja URLs con CIF válido
     */
    private function handleCifUrl($segment)
    {
        $cif  = '';
        if (preg_match('/^([A-Z][0-9]{7}[A-Z0-9])(?:-(.*))?$/i', $segment, $matches)) {
            $cif  = strtoupper($matches[1]);
        } else {
            $cif = strtoupper(substr($segment, 0, 9));
        }

        $company = $this->companyModel->getByCif($cif);

        if (!$company) {
            return $this->handleSlugUrl($segment);
        }

        // Canonical Check
        $correctSlug = $this->companyModel->generateSlug($company['name'] ?? '');
        $expectedSegment = $cif . ($correctSlug ? ('-' . $correctSlug) : '');

        if ($segment !== $expectedSegment) {
            return redirect()->to(site_url($expectedSegment), 301);
        }

        // Forzar la URL canónica siempre al formato oficial: CIF-slug
        $data = $this->prepareViewData($company);
        $data['canonical'] = site_url($expectedSegment);

        // Si el sistema ha decidido que NO es indexable, nos aseguramos de que el Header sea explícito
        if (isset($company['seo_indexable']) && $company['seo_indexable'] === false) {
            $this->response->setHeader('X-Robots-Tag', 'noindex, follow');
        }

        // $this->cachePage(86400); // Cache temporalmente desactivada
        
        if (session('is_logged_in')) {
            $this->response->setHeader('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0');
            $this->response->setHeader('Pragma', 'no-cache');
        } else {
            // Etiqueta para que Cloudflare cachee (1 día), pero max-age=0 para que el navegador siempre pregunte y no se "coma" la versión cacheada si el usuario se loguea
            $this->response->setHeader('Cache-Control', 'public, s-maxage=86400, max-age=0');
        }
        $viewName = (service('request')->getLocale() === 'en') ? 'company_en' : 'company';
        return $this->response->setBody(view($viewName, $data));
    }
    
    /**
     * Maneja URLs con slug (sin CIF válido)
     */
    private function handleSlugUrl($segment)
    {
        // Limpiar el segmento de partes inválidas como "no disponible"
        $cleanSlug = $this->cleanSlugSegment($segment);
        
        if (!$cleanSlug) {
            throw PageNotFoundException::forPageNotFound();
        }
        
        // Buscar empresa por slug
        $company = $this->companyModel->getBySlug($cleanSlug);
        
        if (!$company) {
            // Si no encontramos, intentar buscar por nombre
            $searchName = str_replace('-', ' ', $cleanSlug);
            return redirect()->to(site_url('search_company?q=' . urlencode($searchName)))
                             ->with('message', lang('Messages.flash_22'));
        }
        
        // Verificar si la empresa ahora tiene un CIF válido
        if (!empty($company['cif']) && preg_match('/^[A-Z][0-9]{7}[A-Z0-9]$/i', $company['cif'])) {
            // MIGRACIÓN AUTOMÁTICA: Redirigir a URL con CIF (301)
            $correctSlug = $this->companyModel->generateSlug($company['name']);
            $canonicalUrl = site_url($company['cif'] . ($correctSlug ? ('-' . $correctSlug) : ''));
            return redirect()->to($canonicalUrl, 301);
        }
        
        // La empresa no tiene CIF válido, verificar que el slug sea correcto
        $correctSlug = $this->companyModel->generateSlug($company['name']);
        
        if ($cleanSlug !== $correctSlug) {
            // Redirigir al slug correcto (301)
            return redirect()->to(site_url($correctSlug), 301);
        }
        
        // Renderizar vista con canonical apuntando al slug
        $data = $this->prepareViewData($company);
        $data['canonical'] = site_url($correctSlug);
        
        // Si no tiene CIF, suele ser de menor calidad SEO, reforzamos el noindex si el score es bajo
        if (isset($company['seo_indexable']) && $company['seo_indexable'] === false) {
            $this->response->setHeader('X-Robots-Tag', 'noindex, follow');
        }

        // $this->cachePage(86400); // Cache temporalmente desactivada

        if (session('is_logged_in')) {
            $this->response->setHeader('Cache-Control', 'private, no-store, no-cache, must-revalidate, max-age=0');
            $this->response->setHeader('Pragma', 'no-cache');
        } else {
            // Etiqueta para que Cloudflare cachee (1 día), pero max-age=0 para que el navegador siempre pregunte
            $this->response->setHeader('Cache-Control', 'public, s-maxage=86400, max-age=0');
        }
        $viewName = (service('request')->getLocale() === 'en') ? 'company_en' : 'company';
        return $this->response->setBody(view($viewName, $data));
    }
    
    /**
     * Limpia el segmento de slug removiendo partes inválidas
     */
    private function cleanSlugSegment($segment)
    {
        // Decodificar URL
        $segment = urldecode($segment);
        
        // Si el slug es literalmente "no-disponible" o similar, lo limpiamos
        $invalidFullSlugs = ['no-disponible', 'nodisponible', 'n-a'];
        if (in_array(strtolower(trim($segment)), $invalidFullSlugs)) {
            return null;
        }

        // Dividir por guiones para una limpieza selectiva de placeholders
        $parts = explode('-', $segment);
        $cleanParts = array_filter($parts, function($part) {
            $part = strtolower(trim($part));
            // Solo eliminamos si es un placeholder de base de datos vacío
            return $part !== '' && $part !== 'null' && $part !== 'undefined';
        });
        
        if (empty($cleanParts)) {
            return null;
        }
        
        // Reconstruir el slug limpio
        return implode('-', $cleanParts);
    }
    public function handleBrokenCif($slug = null)
    {
        $cleanSlug = ltrim($slug ?? '', '-');
        
        // Estrategia: "Reducción Iterativa con Wildcards"
        // 1. Convertir "108-padel-equipment-sl" en tokens: ["108", "padel", "equipment", "sl"]
        $tokens = explode('-', $cleanSlug);
        $tokens = array_filter($tokens, fn($t) => strlen($t) > 0);
        $tokens = array_values($tokens); // Reindex

        // Intentar buscar reduciendo tokens desde el final (max 3 intentos)
        // 1. 108%padel%equipment%sl
        // 2. 108%padel%equipment
        // 3. 108%padel
        
        $maxAttempts = min(count($tokens), 3); // No reducir hasta vacio, solo unos pocos pasos
        
        for ($i = 0; $i < $maxAttempts; $i++) {
            // Coger los tokens actuales
            $currentTokens = array_slice($tokens, 0, count($tokens) - $i);
            if (empty($currentTokens)) break;

            // Unir con comodín para tolerar espacios dobles o puntuación
            $wildcardTerm = implode('%', $currentTokens);
            
            // Log para debug
            log_message('error', '[BrokenLink] Trying wildcard: ' . $wildcardTerm);
            
            $company = $this->companyModel->like('company_name', $wildcardTerm)->first();
            
            if ($company) {
                // Éxito: Redirigir a formato canónico (CIF-slug o slug)
                $correctSlug = $this->companyModel->generateSlug($company['company_name']);
                $targetUrl = !empty($company['cif']) 
                    ? site_url($company['cif'] . ($correctSlug ? ('-' . $correctSlug) : ''))
                    : site_url($correctSlug);
                
                return redirect()->to($targetUrl, 301);
            }
        }

        // Fallback: Si todo falla, ir al buscador con el término limpio original
        $searchTerm = str_replace('-', ' ', $cleanSlug);
        return redirect()->to(site_url('search_company?q=' . urlencode($searchTerm)))
                         ->with('message', lang('Messages.flash_23'));
    }

    /**
     * Exporta los datos de la empresa a un PDF profesional
     */
    public function exportPdf($id)
    {
        $id = (int)$id;
        $company = $this->companyModel->getById($id);

        if (!$company) {
            throw PageNotFoundException::forPageNotFound();
        }

        // Administrators (with filtering logic same as prepareViewData)
        $adminsRaw = $this->adminModel->getByCompanyId($id);
        $filteredAdmins = [];
        $excludeKeywords = ['CAPITAL', 'DOMICILIO', 'OBJETO SOCIAL', 'OTROS CONCEPTOS', 'COMIENZO DE OPERACIONES', 'INSCRIPCION', 'RESULTANTE', 'SUSCRITO', 'EURO', 'REMITIDO'];
        $seenAdmins = [];

        foreach ($adminsRaw as $admin) {
            $nameStr = strtoupper($admin['name'] ?? '');
            $posStr = strtoupper($admin['position'] ?? '');
            $combinedText = $nameStr . ' ' . $posStr;

            $exclude = false;
            foreach ($excludeKeywords as $kw) {
                if (strpos($combinedText, $kw) !== false) {
                    $exclude = true;
                    break;
                }
            }
            if ($exclude || preg_match('/[0-9]+/', $nameStr)) continue;

            $uniqueKey = md5(trim($nameStr) . '|' . trim($posStr));
            if (isset($seenAdmins[$uniqueKey])) continue;

            $seenAdmins[$uniqueKey] = true;
            $filteredAdmins[] = $admin;
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        $html = view('reports/company_pdf', [
            'company'        => $company,
            'administrators' => $filteredAdmins,
            'bormePosts'     => $this->bormePostsModel->getByCompanyId($id)
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'informe_' . url_title($company['name'], '_', true) . '.pdf';
        
        return $this->response->setHeader('Content-Type', 'application/pdf')
                              ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                              ->setHeader('X-Robots-Tag', 'noindex, nofollow')
                              ->setBody($dompdf->output());
    }

    /**
     * Endpoint AJAX para guardar la valoración de una empresa
     */
    public function submitRating()
    {
        $request = service('request');
        if (!$request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Acceso denegado']);
        }

        $companyId = (int)$request->getPost('company_id');
        $rating = (int)$request->getPost('rating');
        $ipAddress = $request->getIPAddress();

        if ($companyId <= 0 || $rating < 1 || $rating > 5) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Datos inválidos']);
        }

        $ratingModel = new CompanyRatingModel();

        if ($ratingModel->hasRated($companyId, $ipAddress)) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Ya has valorado esta empresa anteriormente']);
        }

        $ratingModel->insert([
            'company_id' => $companyId,
            'rating' => $rating,
            'ip_address' => $ipAddress
        ]);

        $stats = $ratingModel->getRatingStats($companyId);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => '¡Gracias por tu valoración!',
            'new_avg' => round($stats['avg'], 1),
            'new_count' => $stats['count']
        ]);
    }

    /**
     * Endpoint AJAX para guardar el feedback de una valoración < 5
     */
    public function submitRatingFeedback()
    {
        $request = service('request');
        if (!$request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Acceso denegado']);
        }

        $companyId = (int)$request->getPost('company_id');
        $feedback = trim((string)$request->getPost('feedback'));
        $ipAddress = $request->getIPAddress();

        if ($companyId <= 0 || empty($feedback)) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Datos inválidos']);
        }

        $ratingModel = new CompanyRatingModel();

        // Buscar la valoración previa de esta IP y empresa
        $ratingRow = $ratingModel->where('company_id', $companyId)
                                 ->where('ip_address', $ipAddress)
                                 ->first();

        if (!$ratingRow) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'No se encontró la valoración previa']);
        }

        // Actualizar el feedback
        $ratingModel->update($ratingRow['id'], ['feedback' => $feedback]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => '¡Gracias por ayudarnos a mejorar!'
        ]);
    }

    /**
     * Endpoint temporal para probar el diseño del Informe Premium Marca Blanca
     */
    public function previewPremiumPdf($id)
    {
        $id = (int)$id;
        $company = $this->companyModel->getById($id);

        if (!$company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // Administrators
        $adminsRaw = $this->adminModel->getByCompanyId($id);
        $filteredAdmins = [];
        $excludeKeywords = ['CAPITAL', 'DOMICILIO', 'OBJETO SOCIAL', 'OTROS CONCEPTOS', 'COMIENZO DE OPERACIONES', 'INSCRIPCION', 'RESULTANTE', 'SUSCRITO', 'EURO', 'REMITIDO'];
        $seenAdmins = [];

        foreach ($adminsRaw as $admin) {
            $nameStr = strtoupper($admin['name'] ?? '');
            $posStr = strtoupper($admin['position'] ?? '');
            $combinedText = $nameStr . ' ' . $posStr;

            $exclude = false;
            foreach ($excludeKeywords as $kw) {
                if (strpos($combinedText, $kw) !== false) {
                    $exclude = true;
                    break;
                }
            }
            if ($exclude || preg_match('/[0-9]+/', $nameStr)) continue;

            $uniqueKey = md5(trim($nameStr) . '|' . trim($posStr));
            if (isset($seenAdmins[$uniqueKey])) continue;

            $seenAdmins[$uniqueKey] = true;
            $filteredAdmins[] = $admin;
        }

        // Radar Score
        $radarModel = new \App\Models\CompanyRadarScoreModel();
        $radarScore = $radarModel->where('company_id', $id)->first();

        // Variables de Marca Blanca simuladas (hardcoded para la prueba)
        $brandColor = '#c026d3'; // Un color fucsia corporativo de prueba
        $brandName = 'Agencia Global SEO';
        $brandFooterText = 'Documento confidencial generado por Agencia Global SEO para uso interno.';
        
        // Simular logo (usamos el de APIEmpresas como si fuera el de la agencia, o lo dejamos vacío para que use texto)
        $brandLogoBase64 = '';
        $logoPath = ROOTPATH . 'public/images/logo.png';
        if (file_exists($logoPath)) {
            $type = pathinfo($logoPath, PATHINFO_EXTENSION);
            $data = file_get_contents($logoPath);
            // Lo quitamos en la prueba para ver cómo queda con el texto, o lo dejamos. Lo dejaremos vacío para que se vea el brandName.
            // $brandLogoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        
        $html = view('reports/company_pdf_premium', [
            'company'         => $company,
            'administrators'  => $filteredAdmins,
            'bormePosts'      => $this->bormePostsModel->getByCompanyId($id),
            'radarScore'      => $radarScore,
            'brandColor'      => $brandColor,
            'brandName'       => $brandName,
            'brandFooterText' => $brandFooterText,
            'brandLogoBase64' => $brandLogoBase64
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'informe_premium_prueba.pdf';
        
        return $this->response->setHeader('Content-Type', 'application/pdf')
                              ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
                              ->setHeader('X-Robots-Tag', 'noindex, nofollow')
                              ->setBody($dompdf->output());
    }

    /**
     * AJAX POST endpoint to generate the Premium PDF and send it via email if requested.
     */
    /**
     * AJAX POST endpoint to checkout the Premium PDF via Stripe
     */
    public function checkoutPremiumPdf()
    {
        $request = service('request');
        if (!$request->isAJAX()) {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Acceso denegado']);
        }

        $companyId = (int)$request->getPost('company_id');
        $agencyName = trim((string)$request->getPost('agency_name'));
        $brandColor = trim((string)$request->getPost('brand_color'));
        $footerText = trim((string)$request->getPost('footer_text'));
        $email = trim((string)$request->getPost('email'));

        if ($companyId <= 0 || empty($agencyName)) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Datos inválidos. El nombre de la agencia es obligatorio.']);
        }

        $company = $this->companyModel->getById($companyId);
        if (!$company) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Empresa no encontrada']);
        }

        // Handle Image Upload
        $logoPath = null;
        $file = $this->request->getFile('brand_logo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $mime = $file->getMimeType();
            if (in_array($mime, ['image/png', 'image/jpeg', 'image/jpg'])) {
                if ($file->getSize() < 2097152) { // Max 2MB
                    $newName = $file->getRandomName();
                    $uploadDir = WRITEPATH . 'uploads/whitelabel/logos/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $file->move($uploadDir, $newName);
                    $logoPath = $newName;
                }
            }
        }

        // Save order in database
        $uuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $pdfOrderModel = new \App\Models\PdfOrderModel();
        $orderId = $pdfOrderModel->insert([
            'uuid' => $uuid,
            'company_id' => $companyId,
            'agency_name' => $agencyName,
            'brand_color' => $brandColor,
            'footer_text' => $footerText,
            'email' => $email,
            'logo_path' => $logoPath,
            'status' => 'pending'
        ]);

        // Start Stripe Checkout or Simulator
        if (env('BILLING_MODE') === 'simulator') {
            // Simulator Bypass
            $fakeSessionId = 'sim_' . time() . '_' . random_string('alnum', 10);
            $pdfOrderModel->update($orderId, ['stripe_session_id' => $fakeSessionId, 'status' => 'paid']);
            
            return $this->response->setJSON([
                'status' => 'success',
                'checkout_url' => site_url('empresa/success-premium-pdf?session_id=' . $fakeSessionId . '&uuid=' . $uuid)
            ]);
        }

        try {
            $stripeService = new \App\Services\StripeService();
            $sessionParams = [
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'unit_amount' => 390, // 3,90 €
                        'product_data' => [
                            'name' => 'Informe Marca Blanca - ' . $company['name'],
                            'description' => 'Dossier Ejecutivo Premium (PDF)',
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'client_reference_id' => $orderId,
                'success_url' => site_url('empresa/success-premium-pdf?session_id={CHECKOUT_SESSION_ID}&uuid=' . $uuid),
                'cancel_url' => site_url('empresa/' . $company['id']),
            ];
            
            // Check if we have tax rate
            $taxRate = $stripeService->getTaxRateId();
            if ($taxRate) {
                $sessionParams['line_items'][0]['tax_rates'] = [$taxRate];
            }

            $session = $stripeService->createCheckoutSession($sessionParams);
            
            // Update order with session_id
            $pdfOrderModel->update($orderId, ['stripe_session_id' => $session->id]);

            return $this->response->setJSON([
                'status' => 'success',
                'checkout_url' => $session->url
            ]);
        } catch (\Exception $e) {
            log_message('error', '[checkoutPremiumPdf] Stripe Error: ' . $e->getMessage());
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Error al conectar con la pasarela de pago.']);
        }
    }

    /**
     * Endpoint to update premium PDF settings via UUID without repurchasing
     */
    public function updatePremiumPdf()
    {
        $uuid = $this->request->getPost('uuid');
        if (!$uuid) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Falta identificador del pedido (UUID).']);
        }

        $pdfOrderModel = new \App\Models\PdfOrderModel();
        $order = $pdfOrderModel->where('uuid', $uuid)->first();
        if (!$order) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Pedido no encontrado.']);
        }
        if ($order['status'] !== 'paid') {
            return $this->response->setStatusCode(403)->setJSON(['status' => 'error', 'message' => 'Este pedido aún no ha sido pagado.']);
        }

        $agencyName = $this->request->getPost('agency_name');
        $brandColor = $this->request->getPost('brand_color');
        $footerText = $this->request->getPost('footer_text');
        
        $updateData = [
            'agency_name' => $agencyName,
            'brand_color' => $brandColor,
            'footer_text' => $footerText,
        ];

        $file = $this->request->getFile('logo');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $mime = $file->getMimeType();
            if (in_array($mime, ['image/png', 'image/jpeg', 'image/jpg'])) {
                if ($file->getSize() < 2097152) { // Max 2MB
                    $newName = $file->getRandomName();
                    $uploadDir = WRITEPATH . 'uploads/whitelabel/logos/';
                    if (!is_dir($uploadDir)) {
                        mkdir($uploadDir, 0755, true);
                    }
                    $file->move($uploadDir, $newName);
                    $updateData['logo_path'] = $newName;
                } else {
                    return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'El logo no puede pesar más de 2MB.']);
                }
            } else {
                return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Formato de imagen no permitido. Usa PNG o JPG.']);
            }
        }

        $pdfOrderModel->update($order['id'], $updateData);

        return redirect()->to('empresa/success-premium-pdf?session_id=' . urlencode($order['stripe_session_id']) . '&uuid=' . $uuid);
    }

    /**
     * Endpoint for successful payment return, generates PDF
     */
    public function successPremiumPdf()
    {
        $sessionId = $this->request->getGet('session_id');
        $uuid = $this->request->getGet('uuid');

        if (!$sessionId || !$uuid) {
            return redirect()->to('/')->with('error', 'Enlace de descarga inválido.');
        }

        $pdfOrderModel = new \App\Models\PdfOrderModel();
        $order = $pdfOrderModel->where('uuid', $uuid)->first();

        if (!$order) {
            return redirect()->to('/')->with('error', 'Pedido no encontrado.');
        }

        // Validate payment with Stripe API (to prevent URL sharing without payment)
        if (strpos($sessionId, 'sim_') === 0 && env('BILLING_MODE') === 'simulator') {
            // Simulator bypass: trust the local status
            if ($order['status'] !== 'paid') {
                return redirect()->to('/')->with('error', 'El pago simulado no se completó.');
            }
        } else {
            try {
                $stripeService = new \App\Services\StripeService();
                $stripeSession = \Stripe\Checkout\Session::retrieve($sessionId);
                
                if ($stripeSession->payment_status !== 'paid') {
                    return redirect()->to('/')->with('error', 'El pago no ha sido completado.');
                }
            } catch (\Exception $e) {
                return redirect()->to('/')->with('error', 'Error validando el pago.');
            }
        }

        // Mark as paid if it wasn't
        if ($order['status'] !== 'paid') {
            $pdfOrderModel->update($order['id'], ['status' => 'paid']);
            
            // Send notification to admin
            try {
                $emailService = new \App\Services\EmailService();
                $emailService->sendPaymentNotification([
                    'invoice_number' => 'PDF-' . strtoupper(substr($order['uuid'], 0, 8)),
                    'customer_name'  => !empty($order['agency_name']) ? $order['agency_name'] : 'Cliente',
                    'customer_email' => !empty($order['email']) ? $order['email'] : 'No especificado',
                    'plan_name'      => 'Informe Premium PDF (Marca Blanca)',
                    'amount'         => '3.90',
                    'currency'       => 'EUR',
                    'invoice'        => 'N/A'
                ]);
            } catch (\Exception $e) {
                log_message('error', '[successPremiumPdf] Error sending email: ' . $e->getMessage());
            }
        }

        // Mostrar pantalla de éxito
        return view('reports/premium_success', [
            'order' => $order,
            'companyId' => $order['company_id']
        ]);
    }

    public function generateAndDownloadPremiumPdf()
    {
        $uuid = $this->request->getGet('uuid');
        if (!$uuid) return redirect()->to('/')->with('error', 'Enlace inválido');

        $pdfOrderModel = new \App\Models\PdfOrderModel();
        $order = $pdfOrderModel->where('uuid', $uuid)->first();
        if (!$order || $order['status'] !== 'paid') {
            return redirect()->to('/')->with('error', 'Pedido no válido o no pagado');
        }

        return $this->generatePdfFromOrder($order);
    }

    private function generatePdfFromOrder(array $order)
    {
        $companyId = $order['company_id'];
        $company = $this->companyModel->getById($companyId);
        if (!$company) {
            return redirect()->to('/')->with('error', 'Empresa no encontrada');
        }

        // Load Logo Base64
        $brandLogoBase64 = '';
        if (!empty($order['logo_path'])) {
            $logoFullPath = WRITEPATH . 'uploads/whitelabel/logos/' . $order['logo_path'];
            if (file_exists($logoFullPath)) {
                $data = file_get_contents($logoFullPath);
                $type = pathinfo($logoFullPath, PATHINFO_EXTENSION);
                $brandLogoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
            }
        }

        // Administrators
        $adminsRaw = $this->adminModel->getByCompanyId($companyId);
        $filteredAdmins = [];
        $excludeKeywords = ['CAPITAL', 'DOMICILIO', 'OBJETO SOCIAL', 'OTROS CONCEPTOS', 'COMIENZO DE OPERACIONES', 'INSCRIPCION', 'RESULTANTE', 'SUSCRITO', 'EURO', 'REMITIDO'];
        $seenAdmins = [];

        foreach ($adminsRaw as $admin) {
            $nameStr = strtoupper($admin['name'] ?? '');
            $posStr = strtoupper($admin['position'] ?? '');
            $combinedText = $nameStr . ' ' . $posStr;

            $exclude = false;
            foreach ($excludeKeywords as $kw) {
                if (strpos($combinedText, $kw) !== false) {
                    $exclude = true;
                    break;
                }
            }
            if ($exclude || preg_match('/[0-9]+/', $nameStr)) continue;

            $uniqueKey = md5(trim($nameStr) . '|' . trim($posStr));
            if (isset($seenAdmins[$uniqueKey])) continue;

            $seenAdmins[$uniqueKey] = true;
            $filteredAdmins[] = $admin;
        }

        // Radar Score
        $radarModel = new \App\Models\CompanyRadarScoreModel();
        $radarData = $radarModel->where('company_id', $companyId)->first();
        if ($radarData) {
            $company = array_merge($company, $radarData);
        }
        $dynamicScoreData = \App\Libraries\RadarScoringSystem::calculate($company);
        
        // Remove emojis because DOMPDF Helvetica font does not support them
        $dynamicScoreData['visuals']['icon'] = '';
        
        // Generate QR code as base64 to avoid remote load issues in Dompdf
        $profileUrl = base_url('empresa/' . $company['id']);
        $qrApiUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=150x150&format=png&margin=0&data=' . urlencode($profileUrl);
        $qrBase64 = '';
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $qrApiUrl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64)');
            $qrData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode == 200 && $qrData) {
                $qrBase64 = 'data:image/png;base64,' . base64_encode($qrData);
            }
        } catch (\Exception $e) {}

        // Contracts & Subsidies
        $contracts = [];
        $subsidies = [];
        if (!empty($company['cif'])) {
            $db = \Config\Database::connect();
            $contracts = $db->table('company_contracts')
                ->where('company_cif', $company['cif'])
                ->orderBy('fecha_adjudicacion', 'DESC')
                ->get()->getResultArray();
                
            $subsidies = $db->table('company_subsidies')
                ->where('company_cif', $company['cif'])
                ->orderBy('fecha_concesion', 'DESC')
                ->get()->getResultArray();
        }

        // Dompdf configuration
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        
        $html = view('reports/company_pdf_premium', [
            'company'         => $company,
            'administrators'  => $filteredAdmins,
            'bormePosts'      => $this->bormePostsModel->getByCompanyId($companyId),
            'radarScore'      => $dynamicScoreData,
            'contracts'       => $contracts,
            'subsidies'       => $subsidies,
            'brandColor'      => $order['brand_color'] ?: '#0f172a',
            'brandName'       => $order['agency_name'],
            'brandFooterText' => $order['footer_text'],
            'brandLogoBase64' => $brandLogoBase64,
            'qrBase64'        => $qrBase64
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $pdfContent = $dompdf->output();

        // Send Email if provided
        if (!empty($order['email']) && filter_var($order['email'], FILTER_VALIDATE_EMAIL)) {
            $uploadDir = WRITEPATH . 'uploads/whitelabel/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            $filename = 'informe_' . $companyId . '_' . time() . '.pdf';
            $filePath = $uploadDir . $filename;
            file_put_contents($filePath, $pdfContent);

            $emailService = \Config\Services::email();
            $emailService->setFrom('noreply@apiempresas.es', 'APIEmpresas');
            $emailService->setTo($order['email']);
            $emailService->setSubject('Tu Informe Premium Marca Blanca - ' . $company['name']);
            $emailService->setMessage('Hola,<br><br>Adjuntamos el informe mercantil premium que acabas de generar para <b>' . esc($company['name']) . '</b>.<br><br>Un saludo.');
            $emailService->attach($filePath);
            $emailService->send();
            
            // Delete temp file after sending
            @unlink($filePath);
        }

        return $this->response->setHeader('Content-Type', 'application/pdf')
                              ->setHeader('Content-Disposition', 'attachment; filename="informe_marca_blanca_' . $company['cif'] . '.pdf"')
                              ->setHeader('X-Robots-Tag', 'noindex, nofollow')
                              ->setBody($pdfContent);
    }

    /**
     * Endpoint to download the generated Premium PDF
     */
    public function downloadPremiumPdf($filename)
    {
        $filename = basename($filename); // Prevent path traversal
        $filePath = WRITEPATH . 'uploads/whitelabel/' . $filename;

        if (!file_exists($filePath)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('El archivo ha expirado o no existe.');
        }

        return $this->response->download($filePath, null)->setFileName('Informe_Premium.pdf');
    }
}
