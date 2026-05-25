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

    /**
     * Find companies linked by the same administrators
     */
    public function getLinkedCompaniesByAdminNames(array $adminNames, int $excludeCompanyId): array
    {
        if (empty($adminNames)) {
            return [];
        }

        $db = \Config\Database::connect();
        return $db->table($this->table)
            ->select('company_administrators.*, companies.cif, companies.company_name as linked_company_name, companies.status as linked_company_status')
            ->join('companies', 'companies.id = company_administrators.company_id')
            ->whereIn('company_administrators.name', $adminNames)
            ->where('company_administrators.company_id !=', $excludeCompanyId)
            ->orderBy('companies.id', 'DESC')
            ->get()
            ->getResultArray();
    }
}
