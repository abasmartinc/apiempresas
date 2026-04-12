<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyAdministratorModel extends Model
{
    protected $table = 'company_administrators';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'company_id',
        'name',
        'position',
        'date_appointment',
        'date_cessation',
        'raw_data'
    ];
    protected $returnType = 'array';
    protected $useTimestamps = false;

    /**
     * Get administrators by company ID
     */
    public function getByCompanyId(int $companyId): array
    {
        return $this->where('company_id', $companyId)
                    ->orderBy('id', 'ASC')
                    ->findAll();
    }
}
