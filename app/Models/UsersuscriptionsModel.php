<?php

namespace App\Models;

use CodeIgniter\Model;

class UsersuscriptionsModel extends Model
{
    protected $DBGroup       = 'default'; // asegúrate que sea el grupo correcto
    protected $table         = 'user_subscriptions';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id',
        'plan_id',
        'status',
        'current_period_start',
        'current_period_end',
        'canceled_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;

    public function getActivePlanByUserId($userId)
    {
        return $this->select('user_subscriptions.*, api_plans.name as plan_name, api_plans.price_monthly, api_plans.monthly_quota')
                    ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id')
                    ->where('user_subscriptions.user_id', $userId)
                    ->where('user_subscriptions.status', 'active')
                    ->orderBy('user_subscriptions.current_period_end', 'DESC')
                    ->first();
    }
}
