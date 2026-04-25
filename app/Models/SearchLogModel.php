<?php

namespace App\Models;

use CodeIgniter\Model;

class SearchLogModel extends Model
{
    protected $table         = 'company_search_logs';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'user_id',
        'session_id',
        'channel',
        'route',
        'method',
        'query_raw',
        'query_norm',
        'query_type',
        'result_status',
        'http_status',
        'result_count',
        'company_cif',
        'company_name',
        'ip',
        'user_agent',
        'referer',
        'accept_language',
        'utm_source',
        'utm_medium',
        'utm_campaign',
        'meta',
        'event_hash',
        'included'
    ];
    public function countLogsForDay(string $ymd, array $where = []): int
    {
        $startDate = $ymd . ' 00:00:00';
        $endDate   = date('Y-m-d H:i:s', strtotime("$startDate +1 day"));

        $builder = $this->db->table($this->table)
            ->select('COUNT(*) AS total', false)
            ->where('created_at >=', $startDate)
            ->where('created_at <', $endDate);

        foreach ($where as $k => $v) {
            $builder->where($k, $v);
        }

        $row = $builder->get()->getRowArray();
        return (int)($row['total'] ?? 0);
    }

    public function countZeroResults($ymd = null)
    {
        $builder = $this->where('result_count', 0);
        if ($ymd) {
            $builder->like('created_at', $ymd, 'after');
        }
        return $builder->countAllResults();
    }

    public function countResolvedGaps()
    {
        return $this->db->table($this->table . ' l')
            ->join('companies c', 'l.query_raw = c.cif')
            ->where('l.result_count', 0)
            ->where('l.query_type', 'cif')
            ->countAllResults();
    }
}
