<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyRadarScoreModel extends Model
{
    protected $table      = 'company_radar_scores';
    protected $primaryKey = 'id';

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'company_id',
        'score_total',
        'priority_level',
        'score_reasons',
        'main_act_type',
        'last_borme_date',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;

    /**
     * Obtener score por CIF (une con tabla de empresas)
     */
    public function getByCif(string $cif)
    {
        return $this->select('company_radar_scores.*')
                    ->join('companies', 'companies.id = company_radar_scores.company_id')
                    ->where('companies.cif', $cif)
                    ->first();
    }
}
