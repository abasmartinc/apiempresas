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
