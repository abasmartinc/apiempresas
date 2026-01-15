<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class Company extends BaseController
{
    /** @var CompanyModel */
    protected $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
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

        $desc = "Consulte la información de {$name}";
        if ($cif)  $desc .= " con CIF {$cif}";
        if ($prov) $desc .= " situada en {$prov}";
        $desc .= ". Datos registrales, contacto y actividad.";

        // Related companies
        $related = $this->companyModel->getRelated(
            $company['cnae'] ?? null,
            $company['province'] ?? $company['provincia'] ?? null,
            $company['cif'] ?? 'NO_CIF_' . $company['id'] // Excluirse a sí misma
        );

        return [
            'company'          => $company,
            'statusRaw'        => $statusRaw,
            'statusClass'      => $isActive ? 'company-status company-status--active' : 'company-status company-status--inactive',
            'title'            => $title,
            'meta_description' => $desc,
            'robots'           => 'index, follow',
            'related'          => $related
        ];
    }

    public function show($segment)
    {
        // Extracción de CIF y Slug
        $cif  = '';
        $slug = '';

        if (preg_match('/^([A-Z][0-9]{7}[A-Z0-9])(?:-(.*))?$/i', $segment, $matches)) {
            $cif  = strtoupper($matches[1]);
            $slug = $matches[2] ?? '';
        } else {
            // Fallback for segments that are just CIFs without a slug
            // Basic validation of CIF format to avoid unnecessary DB calls if obviously wrong
            if (!preg_match('/^[A-Z][0-9]{7}[A-Z0-9]$/i', $segment)) {
                throw PageNotFoundException::forPageNotFound();
            }
            $cif = strtoupper($segment);
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
}
