<?php

namespace App\Models;

use CodeIgniter\Model;

class BormePostsModel extends Model
{
    protected $table = 'borme_posts';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'borme_date',
        'company_name',
        'act_types',
        'description',
        'url_pdf',
        'company_id'
    ];
    protected $returnType = 'array';
    protected $useTimestamps = false; 

    // Eventos del modelo
    protected $afterInsert = ['invalidateBormeSummary'];

    /**
     * Invalida el resumen del BORME generado por IA cuando entra un acto nuevo
     */
    protected function invalidateBormeSummary(array $data)
    {
        if (isset($data['data']['company_id'])) {
            $companyId = (int)$data['data']['company_id'];
            $db = \Config\Database::connect();
            
            // Ponemos a NULL el resumen para que el Cron (seo:enrich-borme) lo regenere
            $db->table('company_enrichment')
               ->where('company_id', $companyId)
               ->update(['ai_borme_summary' => null]);
        }
        
        return $data;
    } 

    /**
     * Get posts by company ID
     */
    public function getByCompanyId(int $companyId): array
    {
        return $this->where('company_id', $companyId)
                    ->orderBy('borme_date', 'DESC')
                    ->findAll();
    }
}
