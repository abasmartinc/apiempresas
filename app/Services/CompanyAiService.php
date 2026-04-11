<?php

namespace App\Services;

use App\Libraries\RadarAnalyzer;
use App\Models\CompanyModel;

class CompanyAiService
{
    protected CompanyModel $companyModel;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
    }

    /**
     * Genera análisis comercial (Insights) reutilizando RadarAnalyzer
     */
    public function getInsights(string $cif): ?array
    {
        $company = $this->companyModel->getByCif($cif);
        
        if (!$company) {
            return null;
        }

        // RadarAnalyzer::analyze espera un array de empresa que ya incluya los campos de scoring
        // Aseguramos que los unimos si están disponibles
        $db = \Config\Database::connect();
        $scoring = $db->table('company_radar_scores')
                      ->where('company_id', $company['id'])
                      ->get()
                      ->getRowArray();
        
        if ($scoring) {
            $company = array_merge($company, $scoring);
        }

        $analysis = RadarAnalyzer::analyze($company);

        return [
            'profile' => $analysis['commercial_profile'],
            'summary' => $analysis['summary'],
            'needs'   => $analysis['needs'],
            'conversion_probability' => $analysis['conversion_probability']['label'],
            'estimated_ticket' => $analysis['estimated_ticket']['label'],
        ];
    }

    /**
     * Genera pitch de contacto (Contact Prep)
     */
    public function getContactPrep(string $cif): ?array
    {
        $company = $this->companyModel->getByCif($cif);
        if (!$company) return null;

        $db = \Config\Database::connect();
        $scoring = $db->table('company_radar_scores')
                      ->where('company_id', $company['id'])
                      ->get()
                      ->getRowArray();
        if ($scoring) $company = array_merge($company, $scoring);

        $analysis = RadarAnalyzer::analyze($company);

        return [
            'sales_approach' => $analysis['sales_approach'],
            'suggested_message' => $analysis['first_message'],
            'likely_objection' => $analysis['likely_objection'],
            'attack_angle' => $analysis['attack_angle'],
        ];
    }
}
