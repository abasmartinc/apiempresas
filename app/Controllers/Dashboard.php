<?php

namespace App\Controllers;
use App\Models\ApikeysModel;
use App\Models\ApiRequestsModel;
use App\Models\UserModel;
use App\Models\UsersuscriptionsModel;
use App\Models\SubscriptionModel;
use App\Models\SearchLogModel;
use App\Models\InvoiceModel;
use App\Models\BlockedIpModel;


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
        // Log dashboard visit
        log_activity('dashboard_visit');

        if ($user->is_admin ?? false) {
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

            // --- Full KPIs for Admin (Moved from AJAX to Controller) ---
            $ym = date('Y-m');
            $today = date('Y-m-d');
            $midnight = $today . ' 00:00:00';

            $subscriptionModel = new SubscriptionModel();
            $invoiceModel      = new InvoiceModel();
            $blockedIpModel    = new BlockedIpModel();
            $searchLogModel    = new SearchLogModel();

            try {
                $data['kpi_users_total']  = number_format($this->userModel->countAllResults(), 0, ',', '.');
                $data['kpi_users_active'] = number_format($this->userModel->where('is_active', 1)->countAllResults(), 0, ',', '.');
                $data['kpi_subs_active']  = number_format($subscriptionModel->where('status', 'active')->countAllResults(), 0, ',', '.');

                $data['kpi_api_today']      = number_format($this->ApiRequestsModel->where('created_at >=', $midnight)->countAllResults(), 0, ',', '.');
                $data['kpi_api_month']      = number_format($this->ApiRequestsModel->countRequestsForMonth($ym), 0, ',', '.');
                $data['kpi_api_error_rate'] = $this->ApiRequestsModel->getErrorRate() . '%';
                $data['kpi_api_latency_avg'] = $this->ApiRequestsModel->getAverageLatency() . 'ms';

                $data['kpi_revenue_month']     = number_format($invoiceModel->getMonthlyRevenue($ym)->total ?? 0, 2, ',', '.') . ' €';
                $data['kpi_blocked_ips_count'] = number_format($blockedIpModel->countAllResults(), 0, ',', '.');
                
                // Estos campos no se usan en la vista actual pero estaban en el AJAX, los pasamos por si acaso
                $data['kpi_searches_zero_results']   = number_format($searchLogModel->countZeroResults($ym), 0, ',', '.');
                $data['kpi_searches_resolved_count'] = number_format($searchLogModel->countResolvedGaps(), 0, ',', '.');
            } catch (\Exception $e) {
                log_message('error', 'Error loading Dashboard KPIs in Controller: ' . $e->getMessage());
            }

            return view('admin/dashboard', $data);
        }

        // --- Personalization stats for non-admins ---
        $data['api_key'] = $this->ApikeysModel->where(['user_id' => $userId, 'is_active' => 1])->first();
        $data['plan'] = $this->UsersuscriptionsModel->getActivePlanByUserId($userId);
        
        // Fast query just to know whether to show onboarding strip or not
        $data['has_first_request'] = $this->ApiRequestsModel->hasFirstRequest(['user_id' => $userId]);

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

            // Si es de tipo 'api', mostramos el dashboard de la API (actual dashboard_paid)
            return view('dashboard_paid', $data);
        }

        // Si tiene acceso API habilitado manualmente (gradual access)
        if (($user->api_access ?? 0) == 1) {
            return view('dashboard', $data);
        }

        return view('dashboard_construction', $data);
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
            $kpis = [
                'api_request_total_month' => $this->ApiRequestsModel->countRequestsForMonth(date('Y-m'), ['user_id' => $userId]),
                'avg_latency' => $this->ApiRequestsModel->getAverageLatency(['user_id' => $userId]),
                'error_rate' => $this->ApiRequestsModel->getErrorRate(['user_id' => $userId])
            ];
            // Cachear 15 minutos (900 segundos) para mitigar stress
            cache()->save($cacheKey, $kpis, 900);
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
}


