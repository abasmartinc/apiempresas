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

    public function show($segment)
    {
        // Extract CIF (first 9 chars)
        // Pattern: Letter + 7 digits + Char (Letter or Digit)
        // Example: B12345678
        $cif = substr($segment, 0, 9);

        // Basic validation of CIF format to avoid unnecessary DB calls if obviously wrong
        if (!preg_match('/^[A-Z][0-9]{7}[A-Z0-9]$/i', $cif)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $company = $this->companyModel->getByCif($cif);

        if (!$company) {
            throw PageNotFoundException::forPageNotFound();
        }

        // Generate expected slug
        $name = $company['name'] ?? '';
        $slug = url_title($name, '-', true);

        // Expected URL segment: CIF + '-' + slug
        // If slug is empty (rare), just CIF
        $expectedSegment = $cif . ($slug ? ('-' . $slug) : '');

        // Check if current segment matches expected
        if ($segment !== $expectedSegment) {
            return redirect()->to(site_url($expectedSegment), 301);
        }

        // Prepare data for view
        $data = [
            'company' => $company,
            'title'   => "{$name} - CIF {$cif} | APIEmpresas.es",
            'meta_description' => "Consulte la ficha de {$name} con CIF {$cif}. Dirección, CNAE, objeto social y más datos registrales en APIEmpresas.es.",
            'canonical' => site_url($expectedSegment),
            'related'   => $this->companyModel->getRelated(
                $company['cnae'] ?? null,
                $company['province'] ?? null,
                $cif,
                5
            ),
        ];

        return view('company', $data);
    }
}
