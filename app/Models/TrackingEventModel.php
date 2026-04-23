<?php

namespace App\Models;

use CodeIgniter\Model;

class TrackingEventModel extends Model
{
    protected $table            = 'tracking_events';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'event_name',
        'page',
        'user_id',
        'session_id',
        'anonymous_id',
        'element',
        'metadata',
        'created_at'
    ];

    // Dates
    protected $useTimestamps = false;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
}
