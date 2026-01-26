<?php

namespace App\Models;

use CodeIgniter\Model;

class ApiPlanModel extends Model
{
    protected $table         = 'api_plans';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'slug',
        'name',
        'monthly_quota',
        'rate_limit_per_min',
        'price_monthly',
        'price_annual',
        'is_active'
    ];
}
