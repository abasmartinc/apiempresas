<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyHoldingModel extends Model
{
    protected $table            = 'company_holdings';
    protected $primaryKey       = 'company_id'; // Note: composite primary keys are tricky in CI4, but this is fine for insertion
    protected $useAutoIncrement = false;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['company_id', 'holding_id', 'created_at'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = '';

    /**
     * Get all companies in a holding
     */
    public function getCompaniesByHolding($holdingId, $limit = 100, $filters = [])
    {
        $builder = $this->db->table('company_holdings')
            ->select("companies.id, companies.cif, companies.company_name as name, companies.capital_social_raw as social_capital, companies.estado as status, companies.registro_mercantil as province, CAST(REPLACE(REPLACE(REPLACE(companies.capital_social_raw, ' €', ''), '.', ''), ',', '.') AS DECIMAL(15,2)) as capital_limpio")
            ->join('companies', 'companies.id = company_holdings.company_id')
            ->where('company_holdings.holding_id', $holdingId);

        if (!empty($filters['q'])) {
            $builder->groupStart()
                ->like('companies.company_name', $filters['q'])
                ->orLike('companies.cif', $filters['q'])
            ->groupEnd();
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] == 'activa') {
                $builder->like('companies.estado', 'Activ', 'after');
            } else if ($filters['status'] == 'inactiva') {
                $builder->notLike('companies.estado', 'Activ', 'after');
            }
        }

        return $builder->limit($limit)
            ->get()
            ->getResultArray();
    }

    /**
     * Get total number of companies in a holding (fast count)
     */
    public function getTotalCompaniesByHolding($holdingId)
    {
        return $this->db->table('company_holdings')
            ->where('holding_id', $holdingId)
            ->countAllResults();
    }

    /**
     * Get aggregated data for the holding (Total companies, estimated capital, top provinces)
     */
    public function getHoldingAggregates($holdingId)
    {
        $db = \Config\Database::connect();
        
        $query = $db->query("
            SELECT 
                COUNT(c.id) as total_companies,
                SUM(CAST(REPLACE(REPLACE(REPLACE(c.capital_social_raw, ' €', ''), '.', ''), ',', '.') AS DECIMAL(15,2))) as total_capital
            FROM company_holdings ch
            JOIN companies c ON ch.company_id = c.id
            WHERE ch.holding_id = " . (int)$holdingId
        );
        $aggregates = $query->getRowArray();
        
        $queryProv = $db->query("
            SELECT c.registro_mercantil as province, COUNT(c.id) as count
            FROM company_holdings ch
            JOIN companies c ON ch.company_id = c.id
            WHERE ch.holding_id = " . (int)$holdingId . " AND c.registro_mercantil != ''
            GROUP BY c.registro_mercantil
            ORDER BY count DESC
            LIMIT 3
        ");
        $aggregates['top_provinces'] = $queryProv->getResultArray();
        
        $queryCnae = $db->query("
            SELECT c.cnae_label as sector, COUNT(c.id) as count
            FROM company_holdings ch
            JOIN companies c ON ch.company_id = c.id
            WHERE ch.holding_id = " . (int)$holdingId . " 
              AND c.cnae_label != '' 
              AND c.cnae_label IS NOT NULL 
              AND c.cnae_label NOT LIKE '%Desconocid%'
              AND c.cnae_label NOT LIKE '00 %'
              AND c.cnae_label != '0'
            GROUP BY c.cnae_label
            ORDER BY count DESC
            LIMIT 1
        ");
        $aggregates['top_sector'] = $queryCnae->getRowArray();
        
        return $aggregates;
    }
}
