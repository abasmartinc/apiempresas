<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiRequestsModel extends Model
{
    protected $table      = 'api_requests';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id','api_key_id','subscription_id','endpoint','http_method',
        'status_code','request_id','ip_address','user_agent','duration_ms','created_at'
    ];

    /**
     * Devuelve series para gráfico: [{day: '2026-01-01', total: 12}, ...]
     */
    public function getDailyCountsForRange(string $from, string $to, array $where = []): array
    {
        // Rango inclusivo: [from 00:00:00, to 23:59:59]
        $fromDt = $from . ' 00:00:00';
        $toDt   = $to . ' 23:59:59';

        $builder = $this->db->table($this->table)
            ->select('DATE(created_at) AS day, COUNT(*) AS total', false)
            ->where('created_at >=', $fromDt)
            ->where('created_at <=', $toDt);

        foreach ($where as $k => $v) {
            $builder->where($k, $v);
        }

        return $builder
            ->groupBy('DATE(created_at)', false)
            ->orderBy('day', 'ASC')
            ->get()
            ->getResultArray();
    }

    public function countRequestsForMonth(string $ym, array $where = []): int
    {
        // ym = 'YYYY-MM'
        $builder = $this->db->table($this->table)
            ->select('COUNT(*) AS total', false)
            ->like('created_at', $ym, 'after');

        foreach ($where as $k => $v) {
            $builder->where($k, $v);
        }

        $row = $builder->get()->getRowArray();
        return (int)($row['total'] ?? 0);
    }

    public function countRequestsForDay(string $ymd, array $where = []): int
    {
        // ymd = 'YYYY-MM-DD'
        $builder = $this->db->table($this->table)
            ->select('COUNT(*) AS total', false)
            ->like('created_at', $ymd, 'after');

        foreach ($where as $k => $v) {
            $builder->where($k, $v);
        }

        $row = $builder->get()->getRowArray();
        return (int)($row['total'] ?? 0);
    }

    public function getAverageLatency(array $where = []): int
    {
        $builder = $this->db->table($this->table)
            ->selectAvg('duration_ms', 'avg_latency');

        foreach ($where as $k => $v) {
            $builder->where($k, $v);
        }

        $row = $builder->get()->getRowArray();
        return (int)($row['avg_latency'] ?? 0);
    }

    public function getErrorRate(array $where = []): float
    {
        $builder = $this->db->table($this->table);
        foreach ($where as $k => $v) {
            $builder->where($k, $v);
        }
        $total = $builder->countAllResults(false);

        if ($total === 0) return 0.0;

        $errors = $builder->where('status_code >=', 400)->countAllResults();
        
        return round(($errors / $total) * 100, 2);
    }

    /**
     * Get endpoint breakdown for a specific month
     * Returns array of endpoints with their usage counts
     */
    public function getEndpointBreakdownForMonth(string $ym, array $where = []): array
    {
        $builder = $this->db->table($this->table)
            ->select('endpoint, COUNT(*) AS total', false)
            ->like('created_at', $ym, 'after');
        
        foreach ($where as $k => $v) {
            $builder->where($k, $v);
        }
        
        return $builder
            ->groupBy('endpoint')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();
    }

    /**
     * Get count for a specific endpoint on a specific day
     */
    public function getEndpointCountForDay(string $endpoint, string $ymd, array $where = []): int
    {
        $builder = $this->db->table($this->table)
            ->select('COUNT(*) AS total', false)
            ->where('endpoint', $endpoint)
            ->like('created_at', $ymd, 'after');
        
        foreach ($where as $k => $v) {
            $builder->where($k, $v);
        }
        
        $row = $builder->get()->getRowArray();
        return (int)($row['total'] ?? 0);
    }
}
