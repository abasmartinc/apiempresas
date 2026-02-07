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
