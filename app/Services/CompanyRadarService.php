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
            crs.main_act_type,
            companies.cnae_code AS cnae
        ');
        $builder->join('company_radar_scores crs', 'crs.company_id = companies.id', 'left');
        
        // Aplicar filtros básicos (Provincia, Sector, etc)
        if (!empty($filters['province'])) {
            $builder->where('companies.registro_mercantil', strtoupper($filters['province']));
        }

        if (!empty($filters['priority'])) {
            $builder->where('crs.priority_level', $filters['priority']);
        }

        // Filtros añadidos (26-09-2026), los mismos que ya tiene el Radar de la web.
        // cnae: uno o varios prefijos separados por comas ("62" o "4711,4719").
        $cnaes = array_values(array_filter(array_map('trim', explode(',', (string) ($filters['cnae'] ?? ''))),
            fn($c) => preg_match('/^\d{1,4}$/', $c)));
        if ($cnaes) {
            $builder->groupStart();
            foreach (array_slice($cnaes, 0, 20) as $i => $c) {
                $i === 0 ? $builder->like('companies.cnae_code', $c, 'after') : $builder->orLike('companies.cnae_code', $c, 'after');
            }
            $builder->groupEnd();
        }

        if (isset($filters['min_score']) && $filters['min_score'] !== '' && is_numeric($filters['min_score'])) {
            $builder->where('crs.score_total >=', (int) $filters['min_score']);
        }

        if (!empty($filters['main_act_type'])) {
            $builder->where('crs.main_act_type', (string) $filters['main_act_type']);
        }

        if (!empty($filters['has_phone']) && filter_var($filters['has_phone'], FILTER_VALIDATE_BOOLEAN)) {
            $builder->where('companies.phone IS NOT NULL', null, false);
            $builder->where('companies.phone !=', '');
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
        
        $totalCount = $builder->countAllResults(false);
        $results = $builder->get($limit)->getResultArray();

        // Aplicar enmascaramiento si es Free
        if ($planSlug === 'free') {
            foreach ($results as &$res) {
                $res['company_name'] = $this->maskName($res['company_name']);
                $res['cif'] = 'B********';
            }
        }

        return [
            'total' => $totalCount,
            'results' => $results
        ];
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
