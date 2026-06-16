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

    /**
     * Get administrator info and their associated companies by a slugified name
     */
    public function getAdminInfoAndCompanies(string $slug): ?array
    {
        if (empty($slug)) {
            return null;
        }

        $cacheKey = 'admin_profile_' . md5($slug);
        $cached = cache($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $searchName = str_replace('-', ' ', $slug);
        $db = \Config\Database::connect();

        $companies = $db->table($this->table)
            ->select('company_administrators.name as admin_name, company_administrators.position, company_administrators.action, companies.id, companies.company_name, companies.cif, companies.registro_mercantil as province, companies.estado as status, companies.cnae_label')
            ->join('companies', 'companies.id = company_administrators.company_id')
            ->like('company_administrators.name', $searchName, 'after') // Uses B-Tree index instead of full table scan
            ->where('companies.id IS NOT NULL')
            ->orderBy('companies.fecha_constitucion', 'DESC')
            ->limit(100) // Prevent massive queries
            ->get()
            ->getResultArray();

        if (empty($companies)) {
            return null;
        }

        // Get the most frequent name format for the title
        $names = array_column($companies, 'admin_name');
        $counts = array_count_values($names);
        arsort($counts);
        $bestName = array_key_first($counts);

        $result = [
            'admin_name' => $bestName,
            'companies'  => $companies
        ];

        cache()->save($cacheKey, $result, 86400 * 30); // Cache for 30 days

        return $result;
    }
}
