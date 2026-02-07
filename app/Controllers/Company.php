<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use App\Models\BormePostsModel;
use CodeIgniter\Exceptions\PageNotFoundException;

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
        helper('text'); // For url_title
    }

    /**
     * Muestra ficha por ID (para empresas sin CIF).
     * Ruta: /empresa/{id}-{slug}
     */
    public function showById($id, $slug = null)
    {
        $id = (int)$id;
        $company = $this->companyModel->getById($id);

        if (!$company) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        // 1. REDIRECCIÓN 301: Si tiene CIF, mandarlo a la URL canónica (/CIF-slug)
        // Esto transfiere la autoridad SEO si la empresa gana un CIF en el futuro.
        if (!empty($company['cif'])) {
            $canonicalSlug = url_title($company['name'], '-', true);
            $canonicalUrl  = site_url($company['cif'] . ($canonicalSlug ? ('-' . $canonicalSlug) : ''));
            return redirect()->to($canonicalUrl, 301);
        }

        // 2. Validación de Slug (Canonicalización ID)
        $correctSlug = url_title($company['name'], '-', true);
        if ($slug !== $correctSlug) {
            return redirect()->to(site_url("empresa/{$id}-{$correctSlug}"), 301);
        }

        // 3. Renderizar vista (reutilizamos la misma vista)
        // Ajustamos canonical para que apunte a esta URL de ID
        $data = $this->prepareViewData($company);
        $data['canonical'] = site_url("empresa/{$id}-{$correctSlug}");
        
        return view('company', $data);
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
        $prov = $company['province'] ?? $company['provincia'] ?? '';
        
        $title = "{$name}";
        if ($cif)  $title .= " - {$cif}";
        if ($prov) $title .= " - {$prov}";
        $title .= " | APIEmpresas.es";

        $desc = "Ficha detallada de {$name}";
        if ($cif)  $desc .= " (CIF {$cif})";
        if ($prov) $desc .= " en {$prov}";
        
        $act = $company['cnae_label'] ?? '';
        if ($act) $desc .= ". Actividad: " . character_limiter($act, 50);
        
        $desc .= ". Datos registrales, contacto y scoring mercantil.";

        // Related companies
        $related = $this->companyModel->getRelated(
            $company['cnae'] ?? null,
            $company['province'] ?? $company['provincia'] ?? null,
            $company['cif'] ?? 'NO_CIF_' . $company['id'] // Excluirse a sí misma
        );

        // Breadcrumb Links
        $provinceUrl = '';
        if (!empty($company['province'] ?? $company['provincia'])) {
            $provinceUrl = site_url('directorio/provincia/' . urlencode($company['province'] ?? $company['provincia']));
        }
        
        $cnaeUrl = '';
        if (!empty($company['cnae_code'] ?? $company['cnae'])) {
             // Assuming cnae_code is what we have. Sometimes it might be just 'cnae'. 
             // Logic: try cnae_code first.
             $code = $company['cnae_code'] ?? $company['cnae'];
             $cnaeUrl = site_url('directorio/cnae/' . $code);
        }

        $provinceCnaeUrl = '';
        if (!empty($company['province'] ?? $company['provincia']) && !empty($company['cnae_code'] ?? $company['cnae'])) {
            $code = $company['cnae_code'] ?? $company['cnae'];
            $provinceCnaeUrl = site_url('directorio/provincia/' . urlencode($company['province'] ?? $company['provincia']) . '/cnae/' . $code);
        }

        return [
            'company'          => $company,
            'statusRaw'        => $statusRaw,
            'statusClass'      => $isActive ? 'company-status company-status--active' : 'company-status company-status--inactive',
            'title'            => $title,
            'meta_description' => $desc,
            'robots'           => 'index, follow',
            'related'          => $related,
            'bormePosts'       => $this->bormePostsModel->getByCompanyId((int)$company['id']),
            'provinceUrl'      => $provinceUrl,
            'cnaeUrl'          => $cnaeUrl,
            'provinceCnaeUrl'  => $provinceCnaeUrl
        ];
    }

    public function show($segment)
    {
        // Determinar si es un CIF válido o un slug
        $isValidCif = preg_match('/^[A-Z][0-9]{7}[A-Z0-9]$/i', substr($segment, 0, 9));
        
        if ($isValidCif) {
            // CASO 1: URL con CIF válido (ej: B12345678-empresa-sl)
            return $this->handleCifUrl($segment);
        } else {
            // CASO 2: URL con slug (ej: serviraibe-sl o no-disponible-serviraibe-sl)
            return $this->handleSlugUrl($segment);
        }
    }
    
    /**
     * Maneja URLs con CIF válido
     */
    private function handleCifUrl($segment)
    {
        $cif  = '';
        $slug = '';

        if (preg_match('/^([A-Z][0-9]{7}[A-Z0-9])(?:-(.*))?$/i', $segment, $matches)) {
            $cif  = strtoupper($matches[1]);
            $slug = $matches[2] ?? '';
        } else {
            $cif = strtoupper(substr($segment, 0, 9));
        }

        $company = $this->companyModel->getByCif($cif);

        if (!$company) {
            throw PageNotFoundException::forPageNotFound();
        }

        // Canonical Check
        $correctSlug = url_title($company['name'], '-', true);
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
                // Éxito: Redirigir
                $correctSlug = url_title($company['company_name'], '-', true);
                return redirect()->to(site_url("empresa/{$company['id']}-{$correctSlug}"), 301);
            }
        }

        // Fallback: Si todo falla, ir al buscador con el término limpio original
        $searchTerm = str_replace('-', ' ', $cleanSlug);
        return redirect()->to(site_url('search_company?q=' . urlencode($searchTerm)))
                         ->with('message', 'La dirección que buscas ha cambiado. Te mostramos resultados relacionados.');
    }
}
