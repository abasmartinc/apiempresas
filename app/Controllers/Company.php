<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\BormePostsModel;
use CodeIgniter\Exceptions\PageNotFoundException;
use Dompdf\Dompdf;
use Dompdf\Options;

class Company extends BaseController
{
    /** @var CompanyModel */
    protected $companyModel;
    /** @var BormePostsModel */
    protected $bormePostsModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->bormePostsModel = new BormePostsModel();
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
        
        $title = "{$name} - CIF, Teléfono, Dirección y Cargos";
        if ($prov) $title .= " | {$prov}";
        $title .= " - APIEmpresas.es";

        $desc = "Consulte el CIF, dirección, teléfono y cargos de {$name}";
        if ($prov) $desc .= " en {$prov}";
        $desc .= ". ";
        
        $act = $company['cnae_label'] ?? '';
        if ($act) {
            $desc .= "Su actividad es " . character_limiter($act, 100) . ". ";
        }
        
        $desc .= "Consulte su balance, cuentas anuales y últimos actos inscritos en el Resgistro Mercantil (BORME).";

        // Related companies
        $related = $this->companyModel->getRelated(
            $company['cnae'] ?? null,
            $prov,
            $company['cif'] ?? 'NO_CIF_' . ($company['id'] ?? 0)
        );

        // Breadcrumb Links
        $provinceUrl = '';
        if ($prov) {
            $provinceUrl = site_url('directorio/provincia/' . urlencode($prov));
        }
        
        $cnaeCode = '';
        if (!empty($company['cnae_code'])) {
            $cnaeCode = $company['cnae_code'];
        } elseif (!empty($company['cnae'])) {
             $cnaeCode = $company['cnae'];
        }

        $cnaeUrl = '';
        if ($cnaeCode) {
             $cnaeUrl = site_url('directorio/cnae/' . $cnaeCode);
        }

        $provinceCnaeUrl = '';
        if ($prov && $cnaeCode) {
            $provinceCnaeUrl = site_url('directorio/provincia/' . urlencode($prov) . '/cnae/' . $cnaeCode);
        }

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

        return [
            'company'          => $company,
            'statusRaw'        => $statusRaw,
            'statusClass'      => $isActive ? 'company-status company-status--active' : 'company-status company-status--inactive',
            'title'            => $title,
            'meta_description' => $desc,
            'robots'           => $robots,
            'related'          => $related,
            'bormePosts'       => $this->bormePostsModel->getByCompanyId((int)$company['id']),
            'provinceUrl'      => $provinceUrl,
            'cnaeUrl'          => $cnaeUrl,
            'provinceCnaeUrl'  => $provinceCnaeUrl
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

        // Usar el helper común
        $data = $this->prepareViewData($company);
        $data['canonical'] = site_url($expectedSegment);

        return view('company', $data);
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
        
        return view('company', $data);
    }
    
    /**
     * Limpia el segmento de slug removiendo partes inválidas
     */
    private function cleanSlugSegment($segment)
    {
        // Decodificar URL
        $segment = urldecode($segment);
        
        // Dividir por guiones
        $parts = explode('-', $segment);
        
        // Filtrar partes que parecen "no disponible" o similares
        $invalidPatterns = ['no', 'disponible', 'nodisponible'];
        $cleanParts = array_filter($parts, function($part) use ($invalidPatterns) {
            $part = strtolower(trim($part));
            return !in_array($part, $invalidPatterns) && strlen($part) > 0;
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
        $company = $this->companyModel->getById((int)$id);

        if (!$company) {
            throw PageNotFoundException::forPageNotFound();
        }

        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        
        $dompdf = new Dompdf($options);
        
        $html = view('reports/company_pdf', [
            'company' => $company
        ]);

        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $filename = 'informe_' . url_title($company['name'], '_', true) . '.pdf';
        
        return $this->response->setHeader('Content-Type', 'application/pdf')
                              ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                              ->setBody($dompdf->output());
    }
}
