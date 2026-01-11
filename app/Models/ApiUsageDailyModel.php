<?php


namespace App\Models;

use CodeIgniter\Model;

class ApiUsageDailyModel extends Model
{
    protected $table = 'api_usage_daily';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useTimestamps = false;

    protected $allowedFields = [
        'user_id',
        'plan_id',
        'date',
        'requests_count',
        'created_at',
        'updated_at',
    ];
}
