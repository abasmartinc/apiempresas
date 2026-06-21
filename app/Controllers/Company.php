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
        helper(['text', 'seo_dynamic_helper']); // Cargar text para url_title y nuestro nuevo helper SEO
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
        $slug = url_title($company['name'], '-', true);
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
        
        // Generar título y descripción
        $name = $company['name'] ?? 'Empresa';
        $cif  = $company['cif'] ?? $company['nif'] ?? '';
        
        // Robust check for province
        $prov = '';
        if (!empty($company['province'])) {
            $prov = $company['province'];
        } elseif (!empty($company['provincia'])) {
            $prov = $company['provincia'];
        }
        
        $title = "{$name} - CIF {$cif}, Teléfono, Dirección y Cargos";
        if ($prov) $title .= " | {$prov}";
        $title .= " - APIEmpresas.es";

        if (!empty($company['ai_pitch'])) {
            $desc = $company['ai_pitch'] . " Consulte el CIF {$cif}, dirección, teléfono y cargos de administradores.";
        } else {
            $desc = "Consulte el CIF {$cif}, dirección, teléfono y cargos de {$name}";
            if ($prov) $desc .= " en {$prov}";
            $desc .= ". ";
            
            $act = $company['cnae_label'] ?? '';
            if ($act) {
                $desc .= "Su actividad es " . character_limiter($act, 100) . ". ";
            }
            
            $desc .= "Consulte su balance, cuentas anuales y últimos actos inscritos en el Resgistro Mercantil (BORME).";
        }

        // Related companies
        $related = $this->companyModel->getRelated(
            $company['cnae'] ?? null,
            $prov,
            $company['cif'] ?? 'NO_CIF_' . ($company['id'] ?? 0)
        );

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

        // --- DINAMIC SEO INDEXING ---
        $indexable = shouldIndexCompany($company);
        $robots    = $indexable ? 'index, follow' : 'noindex, follow';
        
        // Si no es indexable, añadir cabecera HTTP (X-Robots-Tag)
        if (!$indexable) {
            // Nota: CodeIgniter 4 maneja la respuesta mediante el servicio response
            service('response')->setHeader('X-Robots-Tag', 'noindex, follow');
        }
        
        // Añadir flag al objeto empresa para uso en sitemaps/logs
        $company['seo_indexable'] = $indexable;
        $company['seo_score']     = calculateCompanySeoScore($company);
        // --- DINAMIC SEO INDEXING ---

        // Administrators
        $adminsRaw = $this->adminModel->getByCompanyId((int)$company['id']);
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

        // Calcular datos para el CTA B2B (Movido desde la vista)
        $companyProv = !empty($company['province']) ? $company['province'] : (!empty($company['registro_mercantil']) ? $company['registro_mercantil'] : 'España');
        $cnaeCodeStr = substr($company['cnae_code'] ?? $company['cnae'] ?? '', 0, 4);
        $cnaeUrlParam = urlencode($cnaeCodeStr);
        $provUrlParam = urlencode($companyProv);
        $sectorName = $company['cnae_label'] ?? 'este sector';
        
        $db = \Config\Database::connect();
        
        // Caching the count query results to avoid Database connections exhaustion
        $cacheKey = 'count_cta_' . md5($companyProv . '_' . $cnaeCodeStr);
        $cachedData = null;
        try {
            $cachedData = cache($cacheKey);
        } catch (\Throwable $e) {
            log_message('error', 'Cache read error for ' . $cacheKey . ': ' . $e->getMessage());
        }

        if ($cachedData !== null) {
            $listCount = $cachedData['count'];
            $targetProv = $cachedData['targetProv'];
            $provUrlParam = $cachedData['provUrlParam'];
            $cnaeUrlParam = $cachedData['cnaeUrlParam'];
            $sectorName = $cachedData['sectorName'];
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
        $pricing = calculate_directory_price($listCount);
        $priceStr = number_format($pricing['base_price'], 0, ',', '.');
        $countFormatted = number_format($listCount, 0, ',', '.');

        // Optimizar la meta descripción si disponemos de texto generado por la IA
        if (!empty($company['ai_seo_text'])) {
            $cleanDesc = strip_tags(str_replace(["\r", "\n"], ' ', $company['ai_seo_text']));
            $desc = character_limiter($cleanDesc, 155, '...');
        }

        $ratingModel = new \App\Models\CompanyRatingModel();
        $ratingStats = $ratingModel->getRatingStats((int)$company['id']);

        return [
            'company'          => $company,
            'statusRaw'        => $statusRaw,
            'statusClass'      => $isActive ? 'company-status company-status--active' : 'company-status company-status--inactive',
            'companyCif'       => $cif, // Pasamos el cif limpio a la vista
            'title'            => $title,
            'meta_description' => $desc,
            'robots'           => $robots,
            'ratingAvg'        => $ratingStats['avg'],
            'ratingCount'      => $ratingStats['count'],
            'related'          => $related,
            'bormePosts'       => $this->bormePostsModel->getByCompanyId((int)$company['id']),
            'administrators'   => $filteredAdmins,
            'provinceUrl'      => $provinceUrl,
            'cnaeUrl'          => $cnaeUrl,
            'provinceCnaeUrl'  => $provinceCnaeUrl,
            'radarCheckoutUrl' => $radarCheckoutUrl,
            'countFormatted'   => $countFormatted,
            'priceStr'         => $priceStr,
            'sectorName'       => $sectorName,
            'targetProv'       => $targetProv,
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

        return $this->response->setBody(view('company', $data));
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
                             ->with('message', 'No encontramos la empresa exacta. Te mostramos resultados relacionados.');
        }
        
        // Verificar si la empresa ahora tiene un CIF válido
        if (!empty($company['cif']) && preg_match('/^[A-Z][0-9]{7}[A-Z0-9]$/i', $company['cif'])) {
            // MIGRACIÓN AUTOMÁTICA: Redirigir a URL con CIF (301)
            $correctSlug = url_title($company['name'], '-', true);
            $canonicalUrl = site_url($company['cif'] . ($correctSlug ? ('-' . $correctSlug) : ''));
            return redirect()->to($canonicalUrl, 301);
        }
        
        // La empresa no tiene CIF válido, verificar que el slug sea correcto
        $correctSlug = url_title($company['name'], '-', true);
        
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

        return $this->response->setBody(view('company', $data));
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
                $correctSlug = url_title($company['company_name'], '-', true);
                $targetUrl = !empty($company['cif']) 
                    ? site_url($company['cif'] . ($correctSlug ? ('-' . $correctSlug) : ''))
                    : site_url($correctSlug);
                
                return redirect()->to($targetUrl, 301);
            }
        }

        // Fallback: Si todo falla, ir al buscador con el término limpio original
        $searchTerm = str_replace('-', ' ', $cleanSlug);
        return redirect()->to(site_url('search_company?q=' . urlencode($searchTerm)))
                         ->with('message', 'La dirección que buscas ha cambiado. Te mostramos resultados relacionados.');
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
}
