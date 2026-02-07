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
        return view('company_suggestions');
    }

    /**
     * AJAX Endpoint for fetching suggestions.
     * GET /company-suggestions/get?q=...
     */
    public function getSuggestions()
    {
        // Simple security check: enforce AJAX (or at least encourage it)
        // if (!$this->request->isAJAX()) {} 

        $q = trim((string)$this->request->getGet('q'));

        if (mb_strlen($q) < 3) {
            return $this->respond([
                'success' => true,
                'data'    => []
            ]);
        }

        try {
            $results = $this->companyModel->searchMany($q, 10);
            
            return $this->respond([
                'success' => true,
                'data'    => $results
            ]);
        } catch (\Throwable $e) {
            return $this->respond([
                'success' => false,
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
