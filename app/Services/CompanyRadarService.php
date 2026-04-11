<?php

namespace App\Services;

use App\Models\CompanyModel;

class CompanyRadarService
{
    protected CompanyModel  $companyModel;
    protected PlanAccessService $planAccess;

    public function __construct()
    {
        $this->companyModel = new CompanyModel();
        $this->planAccess   = new PlanAccessService();
    }

    /**
     * Obtiene resultados de radar filtrados y limitados por plan
     */
    public function getRadarResults(array $filters, string $planSlug): array
    {
        $limit = $this->planAccess->getRadarLimit($planSlug);
        
        $builder = $this->companyModel->builder();
        $builder->select('
            companies.id, 
            companies.company_name, 
            companies.cif, 
            companies.fecha_constitucion, 
            companies.cnae_label, 
            companies.registro_mercantil, 
            companies.municipality,
            crs.score_total,
            crs.priority_level,
            crs.main_act_type
        ');
        $builder->join('company_radar_scores crs', 'crs.company_id = companies.id', 'left');
        
        // Aplicar filtros básicos (Provincia, Sector, etc)
        if (!empty($filters['province'])) {
            $builder->where('companies.registro_mercantil', strtoupper($filters['province']));
        }

        if (!empty($filters['priority'])) {
            $builder->where('crs.priority_level', $filters['priority']);
        }

        // Rango temporal (default hoy si no se especifica)
        $range = $filters['range'] ?? 'hoy';
        $today = date('Y-m-d');
        if ($range === 'hoy') {
            $builder->where('companies.fecha_constitucion >=', $today);
        } else {
            $days = (int)$range;
            $builder->where('companies.fecha_constitucion >=', date('Y-m-d', strtotime("-$days days")));
        }

        $builder->orderBy('crs.score_total', 'DESC');
        $builder->orderBy('companies.fecha_constitucion', 'DESC');
        
        $results = $builder->get($limit)->getResultArray();

        // Aplicar enmascaramiento si es Free
        if ($planSlug === 'free') {
            foreach ($results as &$res) {
                $res['company_name'] = $this->maskName($res['company_name']);
                $res['cif'] = 'B********';
            }
        }

        return $results;
    }

    private function maskName(string $name): string
    {
        $parts = explode(' ', $name);
        if (count($parts) > 1) {
            return $parts[0] . ' ' . str_repeat('*', strlen($parts[1]));
        }
        return substr($name, 0, 4) . '****';
    }
}
