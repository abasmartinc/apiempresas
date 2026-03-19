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
        'stripe_subscription_id',
        'status',
        'current_period_start',
        'current_period_end',
        'canceled_at',
        'created_at',
        'updated_at',
        'product_type',
    ];

    protected $useTimestamps = true;

    public function getActivePlanByUserId($userId)
    {
        return $this->select('user_subscriptions.*, api_plans.name as plan_name, api_plans.slug as plan_slug, api_plans.price_monthly, api_plans.monthly_quota, api_plans.max_alerts, api_plans.product_type')
                    ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id')
                    ->where('user_subscriptions.user_id', $userId)
                    ->groupStart()
                        ->where('user_subscriptions.status', 'active')
                        ->orGroupStart()
                            ->where('user_subscriptions.status', 'canceled')
                            ->where('user_subscriptions.current_period_end >', date('Y-m-d H:i:s'))
                        ->groupEnd()
                    ->groupEnd()
                    ->orderBy('user_subscriptions.current_period_end', 'DESC')
                    ->first();
    }

    /**
     * Obtiene la última suscripción del usuario (cualquier estado) con datos del plan.
     */
    public function getUserSubscriptionWithPlan($userId)
    {
        return $this->select('user_subscriptions.*, api_plans.name as plan_name, api_plans.price_monthly, api_plans.monthly_quota, api_plans.product_type')
                    ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id', 'left')
                    ->where('user_subscriptions.user_id', $userId)
                    ->orderBy('user_subscriptions.created_at', 'DESC')
                    ->first();
    }
    
    /**
     * Verifica si el usuario tiene una suscripción activa para un tipo de producto específico.
     * 
     * @param int $userId
     * @param string $productType 'api', 'radar', or 'bundle'
     * @return bool
     */
    public function hasActiveSubscriptionFor($userId, $productType)
    {
        $builder = $this->select('user_subscriptions.id')
                    ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id')
                    ->where('user_subscriptions.user_id', $userId)
                    ->groupStart()
                        ->where('user_subscriptions.status', 'active')
                        ->orGroupStart()
                            ->where('user_subscriptions.status', 'canceled')
                            ->where('user_subscriptions.current_period_end >', date('Y-m-d H:i:s'))
                        ->groupEnd()
                    ->groupEnd();

        if ($productType !== 'bundle') {
            // Un plan 'bundle' daría acceso a ambos, pero un plan específico solo a su tipo
            $builder->groupStart()
                        ->where('api_plans.product_type', $productType)
                        ->orWhere('api_plans.product_type', 'bundle')
                    ->groupEnd();
        }

        return $builder->first() !== null;
    }
}
