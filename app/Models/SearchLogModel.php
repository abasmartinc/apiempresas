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
}
