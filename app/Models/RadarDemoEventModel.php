<?php

namespace App\Models;

use CodeIgniter\Model;

class RadarDemoEventModel extends Model
{
    protected $table            = 'radar_demo_events';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'visitor_id',
        'user_id',
        'event_type',
        'source',
        'page',
        'cta_label',
        'url',
        'referrer',
        'ip_address',
        'user_agent',
        'metadata_json'
    ];

    // Dates
    protected $useTimestamps = false;
}
