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
            return $this->canonicalRedirect($canonicalUrl);
        }

        // Si no tiene CIF, la URL ahora es simplemente el slug
        return $this->canonicalRedirect(site_url($slug));
    }

    /**
     * Redirección canónica (301) conservando el query string.
     *
     * Las URLs /empresa/{id}-{slug} redirigen siempre a la canónica por CIF, y esa
     * 301 se comía los parámetros. Entre ellos ?ver-riesgo=1, que es lo que lleva al
     * usuario recién registrado hasta el bloque de riesgo: el flag moría en el salto
     * y el scroll nunca llegaba a ejecutarse.
     */
    private function canonicalRedirect(string $url, int $code = 301)
    {
        $query = (string) ($this->request->getServer('QUERY_STRING') ?? '');

        if ($query !== '' && strpos($url, '?') === false) {
            $url .= '?' . $query;
        }

        return redirect()->to($url, $code);
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
        // La normalización vive en company_display_name() (Helpers/company_helper.php)
        // para que los bloques servidos por AJAX muestren el nombre igual que la ficha.
        $name = company_display_name($company['name'] ?? '', $isEn ? 'Company' : 'Empresa');
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
            $title = "{$name} - VAT {$cif}, Solvency, Phone & Directors";
            if ($prov) $title .= " | {$prov}";
            $title .= " - SpainCompanyAPI.com";

            $desc = "Commercial and solvency report for {$name}";
            if ($cif) {
                $desc .= " (VAT {$cif})";
            }
            if ($prov) {
                $desc .= " in {$prov}";
            }
            $desc .= ". Check credit risk scoring, financials, board members, and official BORME registry acts.";
        } else {
            $title = "{$name} - CIF {$cif}, Solvencia, Teléfono y BORME";
            if ($prov) $title .= " | {$prov}";
            $title .= " - APIEmpresas.es";

            $desc = "Informe mercantil y solvencia de {$name}";
            if ($cif) {
                $desc .= " (CIF {$cif})";
            }
            if ($prov) {
                $desc .= " en {$prov}";
            }
            $desc .= ". Consulta su índice de solvencia, teléfono, dirección, directivos y actos en el BORME.";
        }
        
        $desc = character_limiter($desc, 160, '');

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
        // IMPORTANTE: aquí solo se LEE el estado de cuota. Renderizar la ficha nunca
        // debe consumir una consulta: el gasto ocurre en Company::ajaxUnlockRisk(),
        // disparado por el click explícito del usuario en el bloque de riesgo.
        $riskProfile = null;
        $userId = (int)session('user_id');
        $riskQuota = $this->getRiskQuotaStatus($userId, (string)$cif);

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
            'riskQuota'        => $riskQuota,
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
            return $this->canonicalRedirect(site_url($expectedSegment));
        }

        // Forzar la URL canónica siempre al formato oficial: CIF-slug
        $data = $this->prepareViewData($company);
        $data['canonical'] = site_url($expectedSegment);

        // Si el sistema ha decidido que NO es indexable, nos aseguramos de que el Header sea explícito
        if (isset($company['seo_indexable']) && $company['seo_indexable'] === false) {
            $this->response->setHeader('X-Robots-Tag', 'noindex, follow');
        }

        // $this->cachePage(86400); // Cache temporalmente desactivada
        
        // OJO: la clave de sesión es 'logged_in'. Aquí ponía 'is_logged_in', que no
        // la escribe NADIE, así que la condición era siempre falsa y TODAS las
        // respuestas —también las de un usuario con sesión— salían marcadas como
        // cacheables por Cloudflare durante 24 h. Es decir: el dictamen que un
        // usuario acaba de desbloquear, o su paywall, se podía quedar en la caché
        // y servirse al siguiente visitante anónimo.
        if (session('logged_in') || (int) (session('user_id') ?? 0) > 0) {
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
            return $this->canonicalRedirect($canonicalUrl);
        }
        
        // La empresa no tiene CIF válido, verificar que el slug sea correcto
        $correctSlug = $this->companyModel->generateSlug($company['name']);
        
        if ($cleanSlug !== $correctSlug) {
            // Redirigir al slug correcto (301)
            return $this->canonicalRedirect(site_url($correctSlug));
        }
        
        // Renderizar vista con canonical apuntando al slug
        $data = $this->prepareViewData($company);
        $data['canonical'] = site_url($correctSlug);
        
        // Si no tiene CIF, suele ser de menor calidad SEO, reforzamos el noindex si el score es bajo
        if (isset($company['seo_indexable']) && $company['seo_indexable'] === false) {
            $this->response->setHeader('X-Robots-Tag', 'noindex, follow');
        }

        // $this->cachePage(86400); // Cache temporalmente desactivada

        // OJO: la clave de sesión es 'logged_in'. Aquí ponía 'is_logged_in', que no
        // la escribe NADIE, así que la condición era siempre falsa y TODAS las
        // respuestas —también las de un usuario con sesión— salían marcadas como
        // cacheables por Cloudflare durante 24 h. Es decir: el dictamen que un
        // usuario acaba de desbloquear, o su paywall, se podía quedar en la caché
        // y servirse al siguiente visitante anónimo.
        if (session('logged_in') || (int) (session('user_id') ?? 0) > 0) {
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
                
                return $this->canonicalRedirect($targetUrl);
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
     * Exporta el Dictamen de Riesgo y Solvencia en PDF
     */
    public function exportRiskPdf($id)
    {
        if (is_numeric($id)) {
            $company = $this->companyModel->getById((int)$id);
        } else {
            $company = $this->companyModel->getByCif((string)$id);
        }

        if (!$company && !is_numeric($id)) {
            $company = $this->companyModel->where('cif', strtoupper(trim((string)$id)))->first();
        }

        if (!$company) {
            throw PageNotFoundException::forPageNotFound();
        }

        if (empty($company['name']) && !empty($company['company_name'])) {
            $company['name'] = $company['company_name'];
        }

        $userId = (int)session('user_id');
        if ($userId <= 0) {
            return redirect()->to(site_url('enter'))->with('error', 'Debes iniciar sesión para descargar el informe.');
        }

        $targetCif = (string)($company['cif'] ?? '');
        // Comprobación de permisos: lectura pura. Descargar el PDF nunca debe
        // gastar una consulta de forma implícita (antes sí lo hacía).
        $riskQuota = $this->getRiskQuotaStatus($userId, $targetCif);

        /*
         * Quién se lleva este PDF sin pagar.
         *
         * Aquí había una consulta a `user_events` repitiendo a mano la regla de
         * "¿ha desbloqueado esta empresa?" que ya vive en CompanyRiskService. Dos
         * copias de la misma regla es como se desincronizan: la del servicio
         * cuenta también los desbloqueos por COMPRA, y esta se había quedado solo
         * con las consultas, así que quien pagaba su informe podía ver el dictamen
         * y no volver a descargarlo. Ahora manda `already_unlocked`.
         */
        $db = \Config\Database::connect();

        $canDownload = !empty($riskQuota['is_subscriber'])
                    || !empty($riskQuota['already_unlocked'])
                    || !empty($riskQuota['allowed'])
                    || (bool) session('is_admin');

        if (!$canDownload) {
            return redirect()->to(site_url('dashboard?view=risk'))->with('error', 'Para descargar este informe en PDF debes haberlo consultado previamente o disponer de Solvencia Pro.');
        }

        $contracts = [];
        $subsidies = [];
        $riskProfile = null;
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
                
            $riskRow = $db->table('company_risk_profiles')->where('cif', $company['cif'])->get()->getRowArray();
            if ($riskRow) {
                $riskProfile = $riskRow;
                if (!empty($riskProfile['risk_profile'])) {
                    $riskProfile['data'] = json_decode($riskProfile['risk_profile'], true);
                }
            }
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        // El informe prometía "el histórico registral" y entregaba solo las conclusiones
        // del motor. Los asientos ya se le pasaban al OTRO PDF de la ficha; a este no.
        // La tendencia, igual: la ficha web la enseña y el documento de pago no.
        $html = view('reports/risk_pdf_report', [
            'company'         => $company,
            'riskProfile'     => $riskProfile,
            'contracts'       => $contracts,
            'subsidies'       => $subsidies,
            'bormePosts'      => $this->bormePostsModel->getByCompanyId((int) $company['id']),
            'riskTrend'       => $this->getRiskTrend($company, $riskProfile),
            'brandName'       => 'APIEmpresas',
            'brandColor'      => '#0f172a',
            'brandFooterText' => 'Documento confidencial generado por APIEmpresas.'
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'informe_riesgo_' . url_title($company['name'], '_', true) . '.pdf';
        
        return $this->response->setHeader('Content-Type', 'application/pdf')
                              ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                              ->setHeader('X-Robots-Tag', 'noindex, nofollow')
                              ->setBody($dompdf->output());
    }

    /**
     * Previsualización online del Informe de Riesgo en PDF
     */
    public function previewRiskPdf($id)
    {
        $id = (int)$id;
        $company = $this->companyModel->getById($id);
        if (!$company) {
            throw PageNotFoundException::forPageNotFound();
        }

        $contracts = [];
        $subsidies = [];
        $riskProfile = null;
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
                
            $riskRow = $db->table('company_risk_profiles')->where('cif', $company['cif'])->get()->getRowArray();
            if ($riskRow) {
                $riskProfile = $riskRow;
                if (!empty($riskProfile['risk_profile'])) {
                    $riskProfile['data'] = json_decode($riskProfile['risk_profile'], true);
                }
            }
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        // El informe prometía "el histórico registral" y entregaba solo las conclusiones
        // del motor. Los asientos ya se le pasaban al OTRO PDF de la ficha; a este no.
        // La tendencia, igual: la ficha web la enseña y el documento de pago no.
        $html = view('reports/risk_pdf_report', [
            'company'         => $company,
            'riskProfile'     => $riskProfile,
            'contracts'       => $contracts,
            'subsidies'       => $subsidies,
            'bormePosts'      => $this->bormePostsModel->getByCompanyId((int) $company['id']),
            'riskTrend'       => $this->getRiskTrend($company, $riskProfile),
            'brandName'       => 'APIEmpresas',
            'brandColor'      => '#0f172a',
            'brandFooterText' => 'Documento confidencial generado por APIEmpresas.'
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'informe_riesgo_previsualizacion.pdf';
        
        return $this->response->setHeader('Content-Type', 'application/pdf')
                              ->setHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
                              ->setHeader('X-Robots-Tag', 'noindex, nofollow')
                              ->setBody($dompdf->output());
    }

    /**
     * Muestra el PDF de ejemplo del Dictamen de Riesgo
     */
    public function sampleRiskPdf()
    {
        $path = FCPATH . 'ejemplos/ejemplo-informe-riesgo-solvencia.pdf';
        if (!file_exists($path) || $this->request->getGet('rebuild')) {
            command('samples:generate');
        }
        if (!file_exists($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Muestra no encontrada');
        }
        return $this->response->setHeader('Content-Type', 'application/pdf')
                              ->setHeader('Content-Disposition', 'inline; filename="dictamen-riesgo-muestra.pdf"')
                              ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
                              ->setHeader('Pragma', 'no-cache')
                              ->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT')
                              ->setHeader('X-Robots-Tag', 'noindex, nofollow')
                              ->setBody(file_get_contents($path));
    }

    /**
     * Muestra el PDF de ejemplo del Dossier 360
     */
    public function sampleDossierPdf()
    {
        $path = FCPATH . 'ejemplos/ejemplo-dossier-integral-360.pdf';
        if (!file_exists($path) || $this->request->getGet('rebuild')) {
            command('samples:generate');
        }
        if (!file_exists($path)) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Muestra no encontrada');
        }
        return $this->response->setHeader('Content-Type', 'application/pdf')
                              ->setHeader('Content-Disposition', 'inline; filename="dossier-360-muestra.pdf"')
                              ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
                              ->setHeader('Pragma', 'no-cache')
                              ->setHeader('Expires', 'Thu, 01 Jan 1970 00:00:00 GMT')
                              ->setHeader('X-Robots-Tag', 'noindex, nofollow')
                              ->setBody(file_get_contents($path));
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
     * ¿Este pedido es el Dossier 360º (5,90 €) o el informe de riesgo (3,90 €)?
     *
     * La única marca que distingue los dos productos es la etiqueta
     * `[RISK_REPORT]` que `checkoutPremiumPdf()` mete en `footer_text`; no hay
     * columna de tipo en `pdf_orders`. La regla estaba escrita suelta en el
     * generador del PDF, y el aviso de cobro no la miraba siquiera: notificaba
     * 3,90 € y "Marca Blanca" también cuando lo vendido era el Dossier.
     */
    private function esPedidoDossier(array $order): bool
    {
        return strpos((string) ($order['footer_text'] ?? ''), '[RISK_REPORT]') === false;
    }

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
        $reportType = trim((string)$request->getPost('report_type')) ?: 'risk';
        $agencyName = trim((string)$request->getPost('agency_name')) ?: 'APIEmpresas';
        $brandColor = trim((string)$request->getPost('brand_color')) ?: '#0f172a';
        $footerText = trim((string)$request->getPost('footer_text'));
        $email = trim((string)$request->getPost('email'));

        if ($companyId <= 0) {
            $cifParam = trim((string)$request->getPost('cif'));
            if (!empty($cifParam)) {
                $cComp = $this->companyModel->where('cif', $cifParam)->first();
                if ($cComp) {
                    $companyId = (int)$cComp['id'];
                }
            }
        }

        if ($companyId <= 0) {
            return $this->response->setStatusCode(400)->setJSON(['status' => 'error', 'message' => 'Identificador de empresa no válido.']);
        }

        $company = $this->companyModel->getById($companyId);
        if (!$company) {
            return $this->response->setStatusCode(404)->setJSON(['status' => 'error', 'message' => 'Empresa no encontrada']);
        }

        // Tag report type in footer_text for order reconstruction
        if ($reportType === 'risk') {
            $footerText = '[RISK_REPORT] ' . ($footerText ?: 'Documento generado por ' . $agencyName);
            // El importe sale de Config\Solvencia, igual que el precio que anuncia
            // la ficha. Estaba escrito a mano aquí, que es el único sitio donde
            // equivocarse cuesta dinero: es el fallo de anunciar 3,90 € y cobrar
            // 5,90 € sobreviviendo en el punto de cobro.
            $unitAmount = (int) solvencia('centimos.pdf', 390); // + IVA
            $productTitle = 'Informe de Riesgo y Solvencia - ' . $company['name'];
            $productDesc = 'Dictamen de Estabilidad Societaria y Alertas BORME (PDF)';
        } else {
            $footerText = ($footerText ?: 'Documento generado por ' . $agencyName);
            $unitAmount = (int) solvencia('centimos.dossier', 590); // + IVA
            $productTitle = 'Dossier Completo 360º - ' . $company['name'];
            $productDesc = 'Dossier Mercantil Integral, BORME y Riesgo (PDF)';
        }

        // Una configuración en blanco o a 0 no puede convertirse en un cobro de
        // 0 €: antes de llamar a Stripe, el importe tiene que ser un número.
        if ($unitAmount <= 0) {
            log_message('error', '[checkoutPremiumPdf] Importe no válido para ' . $reportType . ': ' . $unitAmount);
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'No se ha podido calcular el importe. Inténtalo de nuevo en unos minutos.']);
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
                        'unit_amount' => $unitAmount,
                        'product_data' => [
                            'name' => $productTitle,
                            'description' => $productDesc,
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
    /**
     * Registra un hito del PDF suelto en tracking_events, para poder comparar las
     * tres opciones del paywall (PDF / pack / suscripción) con el mismo rasero.
     */
    private function logPdfCheckoutEvent(string $eventName, array $meta = []): void
    {
        try {
            (new \App\Models\TrackingEventModel())->insert([
                'event_name'   => $eventName,
                'page'         => 'empresa/pdf',
                'user_id'      => (int) session('user_id'),
                'session_id'   => substr((string) session_id(), 0, 100),
                'anonymous_id' => '',
                'element'      => 'pdf_single',
                'metadata'     => json_encode($meta),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            log_message('error', 'logPdfCheckoutEvent(' . $eventName . '): ' . $e->getMessage());
        }
    }

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

        // `pdf_orders` no guarda el CIF, solo `company_id`: leerlo de $order
        // dejaba el evento con cif=null en todas las conversiones de PDF.
        $cifPedido = null;
        if (!empty($order['company_id'])) {
            // Sin select() a propósito: el modelo se comparte en la petición y
            // un select pegado se arrastra a la siguiente consulta.
            $empresaPedido = $this->companyModel->find((int) $order['company_id']);
            if (is_array($empresaPedido)) {
                $cifPedido = $empresaPedido['cif'] ?? null;
            } elseif (is_object($empresaPedido)) {
                $cifPedido = $empresaPedido->cif ?? null;
            }
        }

        // Una recarga de la página de descarga no debe contar otra conversión
        $pdfAttrKey = 'pdf_logged_' . $uuid;
        if (!session()->get($pdfAttrKey)) {
            session()->set($pdfAttrKey, true);

            $this->logPdfCheckoutEvent('checkout_completed', [
                'plan'      => $this->esPedidoDossier($order) ? 'risk_dossier_single' : 'risk_pdf_single',
                'period'    => 'single',
                'cif'       => $cifPedido,
                'stripe_id' => $sessionId,
            ]);
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

        /*
         * Lo que se compra es el ACCESO a esa empresa, no un fichero.
         *
         * Antes, pagar entregaba el PDF y dejaba la ficha igual de bloqueada que
         * antes de pagar; y el documento era el mismo que ya se descarga gratis
         * quien haya consultado la empresa alguna vez, porque el modal de 3,90 €
         * ni siquiera pide logotipo. Con el desbloqueo, el pago compra algo que no
         * se puede tener gratis.
         *
         * Va aquí, después de validar el pago y fuera del `status !== 'paid'`: en
         * modo simulador el pedido nace ya marcado como pagado, así que dentro de
         * ese `if` no se ejecutaría nunca — justo en el modo con el que se prueba.
         * `desbloquearPorCompra()` es idempotente, de modo que recargar no duplica.
         *
         * Desbloquea para quien tenga la sesión abierta al abrir esta página. Quien
         * compra sin cuenta se lleva el PDF igual; si se registra después, la
         * empresa no le queda desbloqueada, y eso es una mejora pendiente, no un
         * fallo de esto.
         */
        $usuarioActual = (int) session('user_id');
        if ($usuarioActual > 0 && !empty($cifPedido)) {
            try {
                (new \App\Services\CompanyRiskService())->desbloquearPorCompra($usuarioActual, (string) $cifPedido);
            } catch (\Throwable $e) {
                // Que no se caiga la entrega del PDF por esto: ya ha pagado.
                log_message('error', '[successPremiumPdf] desbloquearPorCompra: ' . $e->getMessage());
            }
        }

        // Mark as paid if it wasn't
        if ($order['status'] !== 'paid') {
            $pdfOrderModel->update($order['id'], ['status' => 'paid']);
            
            // Send notification to admin
            try {
                $emailService = new \App\Services\EmailService();
                // El aviso decía 3,90 € y "Marca Blanca" para los dos productos,
                // así que cada Dossier vendido se notificaba 2 € por debajo y con
                // el nombre del otro informe. Ahora sale del pedido.
                $esDossier = $this->esPedidoDossier($order);
                $centimos  = (int) solvencia($esDossier ? 'centimos.dossier' : 'centimos.pdf', $esDossier ? 590 : 390);

                $emailService->sendPaymentNotification([
                    'invoice_number' => 'PDF-' . strtoupper(substr($order['uuid'], 0, 8)),
                    'customer_name'  => !empty($order['agency_name']) ? $order['agency_name'] : 'Cliente',
                    'customer_email' => !empty($order['email']) ? $order['email'] : 'No especificado',
                    'plan_name'      => $esDossier
                        ? 'Dossier Completo 360º (Marca Blanca)'
                        : 'Informe de Riesgo y Solvencia (PDF)',
                    'amount'         => number_format($centimos / 100, 2, '.', ''),
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

        // Contracts & Subsidies & Risk Profile
        $contracts = [];
        $subsidies = [];
        $riskProfile = null;
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
                
            $riskRow = $db->table('company_risk_profiles')->where('cif', $company['cif'])->get()->getRowArray();
            if ($riskRow) {
                $riskProfile = $riskRow;
                if (!empty($riskProfile['risk_profile'])) {
                    $riskProfile['data'] = json_decode($riskProfile['risk_profile'], true);
                }
            }
        }

        // Dompdf configuration
        $options = new \Dompdf\Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new \Dompdf\Dompdf($options);
        
        $isRiskReport = !$this->esPedidoDossier($order);
        $cleanFooterText = trim(str_replace('[RISK_REPORT]', '', $order['footer_text'] ?? ''));
        if (empty($cleanFooterText)) {
            $cleanFooterText = 'Documento generado por ' . ($order['agency_name'] ?: 'APIEmpresas');
        }

        if ($isRiskReport) {
            // Los asientos y la tendencia, igual que en los otros dos puntos de
            // generación. Esta rama es la del PEDIDO PAGADO: era justo la que no los
            // recibía, así que el informe que alguien compra era el único de los tres
            // sin el histórico registral que el texto de venta promete. El `else` de
            // aquí al lado ya se los pasaba al PDF premium.
            $html = view('reports/risk_pdf_report', [
                'company'         => $company,
                'riskProfile'     => $riskProfile,
                'contracts'       => $contracts,
                'subsidies'       => $subsidies,
                'bormePosts'      => $this->bormePostsModel->getByCompanyId($companyId),
                'riskTrend'       => $this->getRiskTrend($company, $riskProfile),
                'brandColor'      => $order['brand_color'] ?: '#0f172a',
                'brandName'       => $order['agency_name'] ?: 'APIEmpresas',
                'brandFooterText' => $cleanFooterText,
                'brandLogoBase64' => $brandLogoBase64
            ]);
            $filenamePrefix = 'informe_riesgo_';
            $emailSubject = 'Tu Informe de Riesgo y Solvencia - ' . $company['name'];
        } else {
            $html = view('reports/company_pdf_premium', [
                'company'         => $company,
                'administrators'  => $filteredAdmins,
                'bormePosts'      => $this->bormePostsModel->getByCompanyId($companyId),
                'radarScore'      => $dynamicScoreData,
                'contracts'       => $contracts,
                'subsidies'       => $subsidies,
                'riskProfile'     => $riskProfile,
                'brandColor'      => $order['brand_color'] ?: '#0f172a',
                'brandName'       => $order['agency_name'] ?: 'APIEmpresas',
                'brandFooterText' => $cleanFooterText,
                'brandLogoBase64' => $brandLogoBase64,
                'qrBase64'        => $qrBase64
            ]);
            $filenamePrefix = 'dossier_completo_';
            $emailSubject = 'Tu Dossier Completo 360º - ' . $company['name'];
        }

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
            $emailService->setSubject($emailSubject);
            $emailService->setMessage('Hola,<br><br>Adjuntamos el informe que acabas de generar para <b>' . esc($company['name']) . '</b>.<br><br>Un saludo.');
            $emailService->attach($filePath);
            $emailService->send();
            
            // Delete temp file after sending
            @unlink($filePath);
        }

        return $this->response->setHeader('Content-Type', 'application/pdf')
                              ->setHeader('Content-Disposition', 'attachment; filename="' . $filenamePrefix . ($company['cif'] ?? $company['id']) . '.pdf"')
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

    /**
     * Endpoint AJAX para cargar datos protegidos/sesión en fichas de empresa cacheadas
     */
    public function ajaxPrivateData($cif)
    {
        $userId = (int)(session('user_id') ?? 0);
        $isLoggedIn = session('logged_in') || $userId > 0;

        $response = $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0, post-check=0, pre-check=0')
            ->setHeader('Pragma', 'no-cache')
            ->setHeader('Expires', 'Sat, 01 Jan 2000 00:00:00 GMT');

        if (!$isLoggedIn) {
            return $response->setJSON(['logged_in' => false]);
        }

        $cleanCif = strtoupper(trim(explode('-', (string)$cif)[0]));
        $db = \Config\Database::connect();
        
        $company = $this->companyModel->where('cif', $cleanCif)->first();
        if (!$company && !empty($cif)) {
            $company = $this->companyModel->where('cif', $cif)->first();
        }
        if (!$company) {
            $company = ['id' => 0, 'name' => 'Empresa', 'cif' => $cleanCif];
        }

        $company['name'] = company_display_name(
            $company['name'] ?? ($company['company_name'] ?? ''),
            'Empresa'
        );

        $targetCif = (string)($company['cif'] ?? $cleanCif);

        // Hidratación de la ficha (cacheada en Cloudflare): SOLO lectura.
        // Antes se llamaba aquí a getRiskViewQuota(), que consumía una consulta en
        // cada carga de página aunque el usuario nunca bajase al bloque de riesgo.
        $riskQuota = $this->getRiskQuotaStatus($userId, $targetCif);

        $contracts = [];
        $subsidies = [];
        if (!empty($targetCif)) {
            $contracts = $db->table('company_contracts')
                ->where('company_cif', $targetCif)
                ->orderBy('fecha_adjudicacion', 'DESC')
                ->get()->getResultArray();

            $subsidies = $db->table('company_subsidies')
                ->where('company_cif', $targetCif)
                ->orderBy('fecha_concesion', 'DESC')
                ->get()->getResultArray();
        }

        $riskProfile = $this->fetchRiskProfile($targetCif);
        $block = $this->renderRiskBlock($company, $riskProfile, $riskQuota, $contracts, $subsidies);

        // Estado de vigilancia. Viaja por aquí y no en el HTML de la ficha porque
        // esa página va cacheada: el botón de la cabecera nace apagado para todo
        // el mundo y lo enciende la hidratación, que es lo único que ve la sesión.
        $watchService = new \App\Services\CompanyWatchService();
        $vigilando = $userId > 0 && $watchService->isWatching($userId, $targetCif);
        // Si el usuario tiene los avisos desactivados, vigilar no le sirve de nada:
        // la ficha tiene que decírselo en el momento, no dejarle creer que está cubierto.
        $avisosActivos = $userId > 0 && $watchService->alertasActivas($userId);

        return $response->setJSON([
            'logged_in'         => true,
            'user_name'         => session('user_name') ?? 'Usuario',
            'user_email'        => session('user_email') ?? '',
            'limit_reached'     => ($block['state'] === 'paywall'),
            'risk_state'        => $block['state'],
            'risk_cif'          => $targetCif,
            'risk_quota'        => $riskQuota,
            'is_watching'       => $vigilando,
            // Para no invitar a un clic que va a rebotar: si ya tiene la lista
            // llena y ESTA empresa no está dentro, la caja lo dice antes.
            'watch_full'        => $userId > 0 && !$vigilando && !$watchService->puedeVigilarMas($userId),
            'watch_quota'       => $userId > 0 ? $watchService->estadoCupo($userId) : null,
            'watch_alerts_on'   => $avisosActivos,
            'watch_email'       => (string) (session('user_email') ?? ''),
            /*
             * ¿Puede bajarse el informe de riesgo sin pagar?
             *
             * El menú "Descargar" de la ficha va en el HTML cacheado, así que nace
             * ofreciendo la compra a todo el mundo — incluido el suscriptor y quien
             * ya consultó esa empresa, que lo tienen gratis en la barra del dictamen
             * dos centímetros más abajo. Ofrecerle a alguien por 3,90 € lo que ya
             * tiene no es solo una venta perdida: es la clase de detalle que le hace
             * dudar de todos los demás precios de la página.
             *
             * La misma condición que usa `company_risk_profile.php` para decidir si
             * pinta "Descargar PDF" o "Descargar por 3,90 €".
             */
            'informe_gratis'    => !empty($riskQuota['is_subscriber'])
                                || !empty($riskQuota['already_unlocked'])
                                || !empty($riskQuota['allowed']),
            'risk_profile_html' => $block['html']
        ]);
    }

    /**
     * Activa o desactiva la vigilancia de una empresa.
     *
     * POST /api/empresa/vigilar  { cif }
     */
    public function ajaxToggleWatch()
    {
        $response = $this->response->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');

        $userId = (int) (session('user_id') ?? 0);
        if (!session('logged_in') && $userId <= 0) {
            return $response->setStatusCode(401)->setJSON([
                'ok'      => false,
                'message' => 'Debes iniciar sesión para vigilar empresas.',
            ]);
        }

        $payload = $this->request->getJSON(true) ?: [];
        $rawCif  = trim((string) ($payload['cif'] ?? $this->request->getPost('cif') ?? ''));

        if ($rawCif === '') {
            return $response->setStatusCode(400)->setJSON(['ok' => false, 'message' => 'Falta el CIF.']);
        }

        $servicio = new \App\Services\CompanyWatchService();

        // Se comprueba el cupo ANTES de tocar nada para poder devolver un motivo.
        // Si se dejara a toggle(), un alta rechazada por tope volvería como
        // watching:false, indistinguible de "ha dejado de vigilarla".
        if (!$servicio->isWatching($userId, $rawCif) && !$servicio->puedeVigilarMas($userId)) {
            $cupo = $servicio->estadoCupo($userId);

            return $response->setJSON([
                'ok'       => false,
                'limite'   => true,
                'watching' => false,
                'cupo'     => $cupo,
                'message'  => 'Ya vigilas ' . $cupo['tope'] . ' empresas, el máximo de la cuenta gratuita. '
                            . 'Con Pro no hay límite; si no, deja de vigilar una para hacer sitio.',
            ]);
        }

        $watching = $servicio->toggle($userId, $rawCif);

        return $response->setJSON([
            'ok'         => true,
            'watching'   => $watching,
            'alerts_on'  => $servicio->alertasActivas($userId),
            'cupo'       => $servicio->estadoCupo($userId),
        ]);
    }

    /**
     * Evolución del score de esta empresa. No depende del usuario, así que es
     * cacheable; devuelve null cuando no hay histórico con el que comparar.
     */
    private function getRiskTrend(array $company, ?array $riskProfile): ?array
    {
        if (empty($riskProfile) || empty($company['cif'])) {
            return null;
        }

        /*
         * La versión del modelo viaja hasta el servicio a propósito: la gráfica
         * solo puede comparar puntos calculados con la MISMA regla. Ver la nota
         * en CompanyRiskService::getScoreTrend().
         */
        return (new \App\Services\CompanyRiskService())->getScoreTrend(
            (string) $company['cif'],
            (int) ($riskProfile['risk_score'] ?? 0),
            24,
            25,
            (string) ($riskProfile['data']['model_version'] ?? '')
        );
    }

    /**
     * Lee (sin consumir) el estado de cuota de perfil de riesgo del usuario.
     */
    private function getRiskQuotaStatus(int $userId, string $cif): array
    {
        $service = new \App\Services\CompanyRiskService();
        return $service->getQuotaStatus($userId, $cif);
    }

    /**
     * @deprecated Alias no destructivo. Usa getRiskQuotaStatus() para comprobar
     *             y CompanyRiskService::consumeRiskView() para consumir.
     */
    private function getRiskViewQuota(int $userId, string $cif): array
    {
        return $this->getRiskQuotaStatus($userId, $cif);
    }

    /**
     * Devuelve el perfil de riesgo calculado de un CIF (o null si no existe).
     */
    private function fetchRiskProfile(string $cif): ?array
    {
        if ($cif === '') {
            return null;
        }

        $riskRow = \Config\Database::connect()
            ->table('company_risk_profiles')
            ->where('cif', $cif)
            ->get()->getRowArray();

        if (!$riskRow) {
            return null;
        }

        if (!empty($riskRow['risk_profile'])) {
            $riskRow['data'] = json_decode($riskRow['risk_profile'], true);
        }

        return $riskRow;
    }

    /**
     * Renderiza el bloque de riesgo que corresponde al estado del usuario.
     *
     * @return array{state:string, html:string}
     */
    private function renderRiskBlock(array $company, ?array $riskProfile, array $riskQuota, array $contracts = [], array $subsidies = []): array
    {
        if (empty($riskProfile)) {
            return ['state' => 'none', 'html' => ''];
        }

        if (!empty($riskQuota['allowed'])) {
            return [
                'state' => 'profile',
                'html'  => view('partials/company_risk_profile', [
                    'riskProfile' => $riskProfile,
                    'company'     => $company,
                    'contracts'   => $contracts,
                    'subsidies'   => $subsidies,
                    'riskQuota'   => $riskQuota,
                    'riskTrend'   => $this->getRiskTrend($company, $riskProfile),
                ])
            ];
        }

        if (!empty($riskQuota['can_unlock'])) {
            return [
                'state' => 'locked',
                'html'  => view('partials/company_risk_locked', [
                    'riskProfile' => $riskProfile,
                    'company'     => $company,
                    'riskQuota'   => $riskQuota
                ])
            ];
        }

        return [
            'state' => 'paywall',
            'html'  => view('partials/company_risk_paywall', [
                'company'   => $company,
                'riskQuota' => $riskQuota,
                // El paywall no recibía el perfil, así que no podía enseñar ni el
                // score: un registrado sin cuota veía MENOS que un anónimo.
                'riskProfile' => $riskProfile,
            ])
        ];
    }

    /**
     * Consume UNA consulta de perfil de riesgo y devuelve el bloque desbloqueado.
     * Único punto de la ficha de empresa donde se gasta cuota: se llama desde el
     * click explícito del usuario en "Ver dictamen de riesgo".
     *
     * POST /api/empresa/desbloquear-riesgo  { cif }
     */
    public function ajaxUnlockRisk()
    {
        $response = $this->response
            ->setHeader('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->setHeader('Pragma', 'no-cache');

        $userId = (int)(session('user_id') ?? 0);
        if (!session('logged_in') && $userId <= 0) {
            return $response->setStatusCode(401)->setJSON([
                'ok'      => false,
                'message' => 'Debes iniciar sesión para consultar el perfil de riesgo.'
            ]);
        }

        $throttler = service('throttler');
        if ($throttler->check(md5($userId . '_risk_unlock'), 30, 60) === false) {
            return $response->setStatusCode(429)->setJSON([
                'ok'      => false,
                'message' => 'Demasiadas consultas seguidas. Espera un momento.'
            ]);
        }

        $payload = $this->request->getJSON(true) ?: [];
        $rawCif  = trim((string)($payload['cif'] ?? $this->request->getPost('cif') ?? ''));

        // 'passive' = el bloque entró en pantalla (lo manda el IntersectionObserver).
        // Solo deja rastro en el historial. Cualquier otro valor es acción deliberada
        // del usuario y sí da de alta la vigilancia.
        $modo = (string) ($payload['mode'] ?? 'click');

        $service  = new \App\Services\CompanyRiskService();
        $cleanCif = $service->cleanCif($rawCif);

        $company = $cleanCif !== '' ? $this->companyModel->where('cif', $cleanCif)->first() : null;
        if (!$company) {
            return $response->setStatusCode(404)->setJSON([
                'ok'      => false,
                'message' => 'No hemos encontrado esa empresa.'
            ]);
        }

        $company['name'] = company_display_name(
            $company['name'] ?? ($company['company_name'] ?? ''),
            'Empresa'
        );

        $targetCif   = (string)($company['cif'] ?? $cleanCif);
        $riskProfile = $this->fetchRiskProfile($targetCif);

        // Nunca se cobra una consulta por una empresa sin dictamen calculado.
        if (empty($riskProfile)) {
            return $response->setStatusCode(404)->setJSON([
                'ok'      => false,
                'message' => 'Esta empresa todavía no tiene perfil de riesgo procesado.'
            ]);
        }

        $riskQuota = $service->consumeRiskView($userId, $targetCif);

        // Vigilancia: solo si el usuario pidió ver ESTA empresa. Si entrara también
        // por la impresión, a los suscriptores —que ven el dictamen sin pulsar nada—
        // se les llenaría la lista de empresas que nunca quisieron seguir.
        $watchService = new \App\Services\CompanyWatchService();
        if ($modo !== 'passive' && !empty($riskQuota['allowed'])) {
            $watchService->watch($userId, $targetCif, 'unlock');
        }

        $db = \Config\Database::connect();
        $contracts = $db->table('company_contracts')
            ->where('company_cif', $targetCif)
            ->orderBy('fecha_adjudicacion', 'DESC')
            ->get()->getResultArray();
        $subsidies = $db->table('company_subsidies')
            ->where('company_cif', $targetCif)
            ->orderBy('fecha_concesion', 'DESC')
            ->get()->getResultArray();

        $block = $this->renderRiskBlock($company, $riskProfile, $riskQuota, $contracts, $subsidies);

        // El estado de vigilancia también cambia aquí (el alta automática de
        // arriba), y el chip de la cabecera lo pintó la hidratación ANTES de que
        // esto ocurriera: se quedaba diciendo "Vigilar empresa" con la campana
        // tachada sobre una empresa que ya estaba en la lista.
        $vigilando = $userId > 0 && $watchService->isWatching($userId, $targetCif);

        return $response->setJSON([
            'ok'              => !empty($riskQuota['allowed']),
            'state'           => $block['state'],
            'html'            => $block['html'],
            'risk_quota'      => $riskQuota,
            'is_watching'     => $vigilando,
            'watch_alerts_on' => $userId > 0 && $watchService->alertasActivas($userId),
            'watch_email'     => (string) (session('user_email') ?? ''),
            'watch_full'      => $userId > 0 && !$vigilando && !$watchService->puedeVigilarMas($userId),
            'watch_quota'     => $userId > 0 ? $watchService->estadoCupo($userId) : null,
        ]);
    }

}
