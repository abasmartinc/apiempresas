<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ApiUsageDailyModel;
use App\Models\UserTriggerEventsModel;
use App\Models\UsersuscriptionsModel;
use CodeIgniter\API\ResponseTrait;

class UsageStatus extends BaseController
{
    use ResponseTrait;

    protected $apiUsageDailyModel;
    protected $userTriggerEventsModel;
    protected $usersuscriptionsModel;

    public function __construct()
    {
        $this->apiUsageDailyModel = new ApiUsageDailyModel();
        $this->userTriggerEventsModel = new UserTriggerEventsModel();
        $this->usersuscriptionsModel = new UsersuscriptionsModel();
    }

    public function index()
    {
        if (!session('logged_in')) {
            return $this->failUnauthorized('No has iniciado sesión.');
        }

        $userId = (int) session('user_id');
        
        // 1. Verificar plan
        $plan = $this->usersuscriptionsModel->getActivePlanByUserId($userId);
        $planSlug = $plan ? strtolower($plan->plan_name) : 'free';

        // Solo triggers para usuarios en plan Free
        if ($planSlug !== 'free' && $planSlug !== 'none' && !empty($planSlug)) {
            return $this->respond([
                'plan'             => $planSlug,
                'requests_count'   => 0,
                'usage_percentage' => 0,
                'trigger'          => null
            ]);
        }

        // 2. Obtener uso del mes actual (SUM de api_usage_daily)
        $currentMonth = date('Y-m');
        $usage = $this->apiUsageDailyModel->selectSum('requests_count')
            ->where('user_id', $userId)
            ->like('date', $currentMonth, 'after')
            ->get()->getRowArray();
        
        $count = (int)($usage['requests_count'] ?? 0);
        
        // Obtener límite del plan free dinámicamente
        $limit = get_free_plan_limit();
        
        $percentage = ($count / $limit) * 100;

        // 3. Detectar trigger
        $triggerToShow = $this->detectTrigger($userId, $count, $percentage);

        return $this->respond([
            'plan'             => 'free',
            'requests_count'   => $count,
            'usage_percentage' => round($percentage, 2),
            'trigger'          => $triggerToShow
        ]);
    }

    protected function detectTrigger(int $userId, int $count, float $percentage): ?string
    {
        // Reglas de prioridad (de mayor a menor urgencia)
        
        // Trigger 4: 80%
        if ($percentage >= 80) {
            if (!$this->userTriggerEventsModel->hasBeenShown($userId, '80_percent')) {
                return '80_percent';
            }
        }
        
        // Trigger 3: 50%
        if ($percentage >= 50) {
            if (!$this->userTriggerEventsModel->hasBeenShown($userId, '50_percent')) {
                return '50_percent';
            }
        }
        
        // Trigger 2: 20%
        if ($percentage >= 20) {
            if (!$this->userTriggerEventsModel->hasBeenShown($userId, '20_percent')) {
                return '20_percent';
            }
        }
        
        // Trigger 1: First Use
        if ($count >= 1) {
            if (!$this->userTriggerEventsModel->hasBeenShown($userId, 'first_use')) {
                return 'first_use';
            }
        }

        return null;
    }
}
