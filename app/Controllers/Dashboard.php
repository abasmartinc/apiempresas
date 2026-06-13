<?php
namespace App\Controllers;

use App\Models\ApikeysModel;
use App\Models\ApiRequestsModel;
use App\Models\UserModel;
use App\Models\UsersuscriptionsModel;


class Dashboard extends BaseController
{
    /** @var UserModel */
    protected $userModel;
    protected $ApikeysModel;
    protected $UsersuscriptionsModel;
    protected $ApiRequestsModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->ApikeysModel = new ApikeysModel();
        $this->UsersuscriptionsModel = new UsersuscriptionsModel();
        $this->ApiRequestsModel = new ApiRequestsModel();
    }
    public function index()
    {
        if (! session('logged_in')) {
            return redirect()->to(site_url('enter'))
                ->with('error', 'Debes iniciar sesión para acceder al panel.');
        }

        $userModel = new UserModel();

        $userId = session('user_id');
        $user   = $userModel->find($userId);
        $data['user'] = $user;



        // Determinar si hay que mostrar el wizard de onboarding
        $data['show_wizard'] = ((int)($user->wizard_completed ?? 0) === 0);


        // Allow admins to view the client dashboard with ?view=client
        if (($user->is_admin ?? false) && $this->request->getGet('view') !== 'client') {
            $data['title'] = 'Panel de Administración';

            // --- Online Users Logic ---
            $fiveMinutesAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
            
            $onlineUsersQuery = $userModel->where('last_active_at >=', $fiveMinutesAgo);
            
            $data['total_online'] = $onlineUsersQuery->countAllResults(false);
            $data['online_users'] = $onlineUsersQuery->limit(10)->find();
            
            $html = '';
            foreach ($data['online_users'] as $ou) {
                $name = esc($ou->name ?: $ou->email);
                $time = date('H:i', strtotime($ou->last_active_at));
                $html .= "<div style='background: white; padding: 8px 16px; border-radius: 100px; border: 1px solid #e2e8f0; display: flex; align-items: center; gap: 8px; font-size: 0.9rem; font-weight: 500; color: #1e293b;'>
                            <span style='width: 8px; height: 8px; background: #10b981; border-radius: 50%;'></span>
                            $name
                            <span style='color: #94a3b8; font-size: 0.75rem; font-weight: 400;'>$time</span>
                          </div>";
            }
            if ($data['total_online'] > count($data['online_users'])) {
                $diff = $data['total_online'] - count($data['online_users']);
                $html .= "<div style='padding: 8px 16px; color: #64748b; font-size: 0.9rem;'>y $diff más...</div>";
            }
            $data['online_users_html'] = $html;
            // --------------------------

            return view('admin/dashboard', $data);
        }

        // --- Personalization stats for non-admins ---
        $data['api_key'] = $this->ApikeysModel->where(['user_id' => $userId, 'is_active' => 1])->first();
        
        // --- API Usage & Activation Metrics ---
        $db = \Config\Database::connect();
        
        $plan = $this->UsersuscriptionsModel->getActivePlanByUserId($userId);
        $isPaid = false;
        if ($plan) {
            $planNameRaw = $plan->plan_name ?? 'Free';
            $currentPlanSlug = strtolower(trim($planNameRaw));
            $isPaid = ($currentPlanSlug !== 'free' && !empty($currentPlanSlug));
        }

        if ($isPaid && $plan) {
            $maxLimit = (int)($plan->monthly_quota ?? 3000);
            
            $usageSum = $db->table('api_usage_daily')
                ->selectSum('requests_count', 'total')
                ->selectSum('credits_used', 'credits_total')
                ->where('user_id', $userId)
                ->where('plan_id', $plan->plan_id)
                ->where('date >=', date('Y-m-01'))
                ->get()->getRow();
        } else {
            $maxLimit = get_free_plan_limit();
            
            // Plan Free: Bono 100 consultas Lifetime (desde hoy 2026-05-28)
            $usageSum = $db->table('api_usage_daily')
                ->selectSum('requests_count', 'total')
                ->selectSum('credits_used', 'credits_total')
                ->where('user_id', $userId)
                ->where('date >=', '2026-05-28')
                ->get()->getRow();
        }
        
        $requestsUsedThisMonth = (int)($usageSum->total ?? 0) + (int)($usageSum->credits_total ?? 0);
        
        $remainingRequests = max(0, $maxLimit - $requestsUsedThisMonth);
        
        $data['requestsUsedThisMonth'] = $requestsUsedThisMonth;
        $data['freeLimit'] = $maxLimit; // Alias para compatibilidad con la vista
        $data['remainingRequests'] = $remainingRequests;
        $data['plan'] = $plan;
        $data['isPaid'] = $isPaid;
        $data['maxLimit'] = $maxLimit;

        // Recuperar saldo del monedero y estadísticas
        $walletBalance = 0;
        $walletSpent = 0;
        $walletTotal = 0;
        $walletLowBalance = false;
        
        if ($db->tableExists('user_wallets')) {
            $walletRow = $db->table('user_wallets')->where('user_id', $userId)->get()->getRow();
            if ($walletRow) {
                $walletBalance = (int)$walletRow->balance;
            }
            
            $spentRow = $db->table('api_usage_daily')->selectSum('credits_used', 'total')->where('user_id', $userId)->get()->getRow();
            $walletSpent = (int)($spentRow->total ?? 0);
            
            $walletTotal = $walletBalance + $walletSpent;
            
            // Mostrar CTA de recarga si queda menos del 20% del total o menos de 100 créditos
            if ($walletTotal > 0 && ($walletBalance <= ($walletTotal * 0.2) || $walletBalance < 100)) {
                $walletLowBalance = true;
            }
        }
        $data['walletBalance'] = $walletBalance;
        $data['walletSpent'] = $walletSpent;
        $data['walletTotal'] = $walletTotal;
        $data['walletLowBalance'] = $walletLowBalance;
        $data['isBonusUser'] = ($walletTotal > 0);

        // --- Mostrar aviso de migración a Free (solo una vez) ---
        $data['showMigrationNotice'] = false;
        if ((int)$user->migration_notice_shown === 0) {
            // Solo se le muestra visualmente a los usuarios Free sin saldo en el monedero
            if (!$isPaid && $walletBalance == 0) {
                $data['showMigrationNotice'] = true;
            }
            // En cualquier caso (Pro, Bono o Free), lo marcamos como visto en BD para no evaluarlo más
            $userModel->update($userId, ['migration_notice_shown' => 1]);
        }

        // Dynamic Usage Message
        $data['usageMessage'] = null;
        if (!$isPaid) {
            if ($requestsUsedThisMonth == 0) {
                $data['usageMessage'] = [
                    'title' => '¡Bienvenido!',
                    'text'  => 'Empieza validando una empresa para ver cómo funciona la API en tiempo real.'
                ];
            } elseif ($requestsUsedThisMonth >= 1 && $requestsUsedThisMonth <= 4) {
                $data['usageMessage'] = [
                    'title' => '⚡ Ya has probado la API',
                    'text'  => 'Haz 2–3 validaciones más para comprobar la calidad real de los datos.'
                ];
            } elseif ($requestsUsedThisMonth >= 5 && $requestsUsedThisMonth <= 9) {
                $data['usageMessage'] = [
                    'title' => 'Estás viendo el valor',
                    'text'  => 'El siguiente paso es integrarlo en tu sistema para automatizar procesos.'
                ];
            } elseif ($requestsUsedThisMonth >= ($maxLimit * 0.6) && $requestsUsedThisMonth < $maxLimit) {
                $data['usageMessage'] = [
                    'title' => 'Límite casi alcanzado',
                    'text'  => 'Te quedan pocas consultas gratuitas. Activa Pro para evitar interrupciones.'
                ];
            } else {
                if ($walletBalance > 0) {
                    $data['usageMessage'] = [
                        'title' => 'Límite gratuito superado',
                        'text'  => 'Se están consumiendo créditos automáticamente de tu monedero a medida.'
                    ];
                } else {
                    $data['usageMessage'] = [
                        'title' => 'Has alcanzado el límite gratuito',
                        'text'  => 'Activa Pro para seguir validando empresas automáticamente.'
                    ];
                }
            }
        } else {
            // Plan de Pago superado con wallet
            if ($requestsUsedThisMonth >= $maxLimit && $walletBalance > 0) {
                $data['usageMessage'] = [
                    'title' => 'Límite mensual superado',
                    'text'  => 'Se están consumiendo créditos automáticamente de tu monedero a medida.'
                ];
            }
        }
        $data['dashboardUsageMessage'] = $data['usageMessage']['text'] ?? '';

        // Fast query just to know whether to show onboarding strip or not
        $data['has_first_request'] = $requestsUsedThisMonth > 0;
        $data['requestsUsed'] = $requestsUsedThisMonth; // Alias for convenience in view

        // Si tiene plan activo, va al dashboard correspondiente
        if ($data['plan']) {
            // Buscamos si alguno de sus planes activos es de tipo radar o bundle
            $activePlans = $this->UsersuscriptionsModel->select('api_plans.product_type')
                                ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id')
                                ->where('user_subscriptions.user_id', $userId)
                                ->where('user_subscriptions.status', 'active')
                                ->where('user_subscriptions.current_period_end >', date('Y-m-d H:i:s'))
                                ->findAll();
            
            $hasRadar = false;
            foreach ($activePlans as $ap) {
                if (in_array($ap->product_type, ['radar', 'bundle'])) {
                    $hasRadar = true;
                    break;
                }
            }

            if ($hasRadar) {
                return redirect()->to(site_url('radar'));
            }

            // CASO ESPECIAL: Si solo tiene el plan gratuito (o ninguno pagado), 
            // pero su intención o preferencia es el Radar
            if (session('intended_product') === 'radar' || session('preferred_product') === 'radar') {
                return redirect()->to(site_url('radar'));
            }

            // Ahora todos los usuarios de pago de la API van al dashboard unificado
            // (La redirección a dashboard_paid ha sido eliminada)
        }

        // Todos los usuarios convergen en el dashboard unificado
        return view('dashboard', $data);
    }

    /**
     * Devuelve los KPIs pesados vía AJAX para no bloquear la carga ni la sesión
     */
    public function kpis_ajax()
    {
        if (!session('logged_in')) {
            return $this->response->setJSON(['error' => 'No autorizado'])->setStatusCode(401);
        }

        $userId = session('user_id');

        // ¡CRÍTICO! Liberar el archivo de sesión de Inmediato antes de lanzar queries pesadas
        session_write_close();

        $cacheKey = 'kpis_user_' . $userId;
        $kpis = cache($cacheKey);

        if (!$kpis) {
            $db = \Config\Database::connect();
            
            $subModel = new \App\Models\UsersuscriptionsModel();
            $plan = $subModel->getActivePlanByUserId($userId);
            
            $builder = $db->table('api_usage_daily')
                ->selectSum('requests_count', 'total')
                ->selectSum('credits_used', 'credits_total')
                ->where('user_id', $userId)
                ->where('date >=', date('Y-m-01'));
                
            if ($plan && $plan->plan_id) {
                $builder->where('plan_id', $plan->plan_id);
            }
            
            $usageSum = $builder->get()->getRow();
            $requestCount = (int)($usageSum->total ?? 0) + (int)($usageSum->credits_total ?? 0);

            $kpis = [
                'api_request_total_month' => $requestCount,
                'avg_latency' => $this->ApiRequestsModel->getAverageLatency(['user_id' => $userId]),
                'error_rate' => $this->ApiRequestsModel->getErrorRate(['user_id' => $userId])
            ];
            // Deshabilitado caché temporalmente para depuración de discrepancias
            // cache()->save($cacheKey, $kpis, 30);
        }

        return $this->response->setJSON($kpis);
    }

    /**
     * Terminar impersonación y volver a admin
     */
    public function stopImpersonating()
    {
        $impersonatorId = session('impersonator_id');
        
        if (!$impersonatorId) {
            return redirect()->to(site_url('dashboard'));
        }

        $adminUser = $this->userModel->find($impersonatorId);
        
        if (!$adminUser || !$adminUser->is_admin) {
             // Fallback raro: el impersonador ya no existe o no es admin
             session()->destroy();
             return redirect()->to(site_url('enter'))->with('error', 'Sesión de administrador no válida.');
        }

        // Restaurar sesión de admin
        session()->regenerate();
        session()->set([
            'user_id'    => $adminUser->id,
            'user_email' => $adminUser->email,
            'user_name'  => $adminUser->name ?? '',
            'is_admin'   => 1,
            'logged_in'  => true,
        ]);

        return redirect()->to(site_url('admin/users'))->with('message', 'Has vuelto a tu cuenta de administrador.');
    }

    /**
     * Completa el onboarding wizard vía AJAX
     */
    public function completeWizard()
    {
        if (!session('logged_in')) {
            return $this->response->setJSON(['error' => 'No autorizado'])->setStatusCode(401);
        }

        $userId = session('user_id');
        $this->userModel->update($userId, ['wizard_completed' => 1]);

        return $this->response->setJSON(['success' => true]);
    }
}


