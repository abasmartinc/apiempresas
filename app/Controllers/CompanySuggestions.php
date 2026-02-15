<?php

namespace App\Controllers;

use App\Models\CompanyModel;
use CodeIgniter\API\ResponseTrait;

class CompanySuggestions extends BaseController
{
    use ResponseTrait;

    protected $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
    }

    /**
     * Renders the search page.
     */
    public function index()
    {
        $data = [
            'title' => 'Autocomplete de Empresas y Sugerencias en Tiempo Real | APIEmpresas'
        ];
        return view('company_suggestions', $data);
    }

    /**
     * AJAX Endpoint for fetching suggestions.
     * GET /company-suggestions/get?q=...
     */
    public function getSuggestions()
    {
        $q = trim((string)$this->request->getGet('q'));

        if (mb_strlen($q) < 3) {
            return $this->respond([
                'success' => true,
                'data'    => []
            ]);
        }

        try {
            $results = $this->companyModel->searchMany($q, 10);
            
            // Format results to return only specific fields
            $formatted = array_map(function($item) {
                // Ensure we handle both potential column names (aliased or original)
                return [
                    'name'    => $item['name'] ?? ($item['company_name'] ?? ''),
                    'cif'     => $item['cif'] ?? '',
                    'address' => $item['address'] ?? '',
                    'score'   => isset($item['score']) ? round((float)$item['score'], 2) : 1.00
                ];
            }, $results);

            return $this->respond([
                'success' => true,
                'data'    => $formatted
            ]);
        } catch (\Throwable $e) {
            return $this->respond([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
