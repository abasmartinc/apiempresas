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
                ->with('error', lang('Messages.flash_24'));
        }

        $userModel = new UserModel();

        $userId = session('user_id');
        $user   = $userModel->find($userId);
        $data['user'] = $user;

        // Si el usuario tiene el plan copiloto_ventas, no tiene acceso al dashboard
        $plan = $this->UsersuscriptionsModel->getActivePlanByUserId($userId);
        if ($plan && $plan->plan_slug === 'copiloto_ventas') {
            return redirect()->to(site_url('/'));
        }



        // Determinar si hay que mostrar el wizard de onboarding
        // Con ?probar=CIF (enlace de los correos) el panel lanza la consulta real: el
        // asistente la tapaba y el usuario gastaba una consulta sin ver el resultado.
        $data['show_wizard'] = ((int)($user->wizard_completed ?? 0) === 0) && !$this->request->getGet('probar');


        // Allow admins to view client, risk or api dashboards
        $viewParam = $this->request->getGet('view');
        if (($user->is_admin ?? false) && !in_array($viewParam, ['client', 'risk', 'api'], true)) {
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

            // --- Pending Tickets Count ---
            $ticketModel = new \App\Models\TicketModel();
            $data['pending_tickets_count'] = $ticketModel->where('status', 'open')->countAllResults();
            // --------------------------

            return $this->renderView('admin/dashboard', $data);
        }

        // --- Personalization stats for non-admins ---
        $data['api_key'] = $this->ApikeysModel->where(['user_id' => $userId, 'is_active' => 1])->first();

        // Red de seguridad: cuentas creadas sin ninguna clave (altas antiguas por GitHub o
        // LinkedIn) reciben una al entrar. Si tiene una desactivada, no se toca.
        if (!$data['api_key'] && $this->ApikeysModel->where('user_id', $userId)->countAllResults() === 0) {
            $this->ApikeysModel->insert([
                'user_id'    => $userId,
                'name'       => 'Default API Key',
                'api_key'    => bin2hex(random_bytes(32)),
                'is_active'  => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
            $data['api_key'] = $this->ApikeysModel->where(['user_id' => $userId, 'is_active' => 1])->first();
        }
        
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
            if ($requestsUsedThisMonth >= $maxLimit) {
                if ($walletBalance > 0) {
                    $data['usageMessage'] = [
                        'title' => lang('Dashboard.usage_msg_limit_exceeded_title'),
                        'text'  => lang('Dashboard.usage_msg_limit_exceeded_text')
                    ];
                }
                // Free agotado sin monedero: lo cuenta el banner de uso (nivel 100 %),
                // con el botón de compra; aquí no se repite.
            } elseif ($requestsUsedThisMonth >= ($maxLimit * 0.6)) {
                $data['usageMessage'] = [
                    'title' => lang('Dashboard.usage_msg_almost_reached_title'),
                    'text'  => lang('Dashboard.usage_msg_almost_reached_text')
                ];
            } elseif ($requestsUsedThisMonth >= 5) {
                $data['usageMessage'] = [
                    'title' => lang('Dashboard.usage_msg_seeing_value_title'),
                    'text'  => lang('Dashboard.usage_msg_seeing_value_text')
                ];
            } elseif ($requestsUsedThisMonth >= 1 && $requestsUsedThisMonth <= 4) {
                $data['usageMessage'] = [
                    'title' => lang('Dashboard.usage_msg_tested_title'),
                    'text'  => lang('Dashboard.usage_msg_tested_text')
                ];
            } else {
                $data['usageMessage'] = [
                    'title' => lang('Dashboard.usage_msg_welcome_title'),
                    'text'  => lang('Dashboard.usage_msg_welcome_text')
                ];
            }
        }
        // Planes de pago: el aviso de cupo (80 %, agotado, monedero) lo pinta
        // components/paid_quota_alert en la vista, con estimación y opciones.
        $data['dashboardUsageMessage'] = $data['usageMessage']['text'] ?? '';

        // Fast query just to know whether to show onboarding strip or not
        $data['has_first_request'] = $requestsUsedThisMonth > 0;
        $data['requestsUsed'] = $requestsUsedThisMonth; // Alias for convenience in view

        // Comprobar si hay tickets respondidos por el admin
        $ticketModel = new \App\Models\TicketModel();
        $data['answeredTickets'] = $ticketModel->where('user_id', $userId)
                                               ->where('status', 'answered')
                                               ->findAll();

        // --- DASHBOARD ESPECÍFICO DE PERFIL DE RIESGO & SOLVENCIA ---
        $intent = (string)($user->signup_intent ?? '');
        // OJO: 'preferred_product' NO existe como columna en la base de datos, así
        // que esto es siempre ''. Se deja porque no molesta y porque el día que se
        // añada la columna empieza a funcionar solo, pero no cuentes con ello:
        // quien decide de verdad es 'signup_intent'.
        $prefProduct = (string)($user->preferred_product ?? '');
        $hasRiskPlan = false;
        if (!empty($data['plan'])) {
            $pSlug = strtolower(trim((string)($data['plan']->plan_slug ?? '')));
            $pType = strtolower(trim((string)($data['plan']->product_type ?? '')));
            if ($pSlug === 'risk_pro' || $pType === 'risk') {
                $hasRiskPlan = true;
            }
        }
        // Un plan de pago de la API manda sobre cómo se registró el usuario: quien entró
        // por una ficha de riesgo y después contrata Pro/Business debe ver su panel de la API.
        $hasPaidApiPlan = false;
        if (!empty($data['plan'])) {
            $pSlug = strtolower(trim((string)($data['plan']->plan_slug ?? '')));
            $pType = strtolower(trim((string)($data['plan']->product_type ?? '')));
            $hasPaidApiPlan = in_array($pType, ['api', 'bundle'], true) && $pSlug !== 'free';
        }

        $isRiskUser = $hasRiskPlan || (!$hasPaidApiPlan && ($intent === 'view_risk_profile' || $prefProduct === 'risk' || session('intended_product') === 'risk'));

        if (($isRiskUser || $viewParam === 'risk') && $viewParam !== 'api') {
            return $this->renderRiskDashboard($user, $data);
        }

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
        return $this->renderView('dashboard', $data);
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
            
            $isPaid = false;
            if ($plan) {
                $planNameRaw = $plan->plan_name ?? 'Free';
                $currentPlanSlug = strtolower(trim($planNameRaw));
                $isPaid = ($currentPlanSlug !== 'free' && !empty($currentPlanSlug));
            }

            $builder = $db->table('api_usage_daily')
                ->selectSum('requests_count', 'total')
                ->selectSum('credits_used', 'credits_total')
                ->where('user_id', $userId);
                
            if ($isPaid && $plan && $plan->plan_id) {
                $builder->where('plan_id', $plan->plan_id);
                $builder->where('date >=', date('Y-m-01'));
            } else {
                $builder->where('date >=', '2026-05-28'); // Free limit lifetime
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
             return redirect()->to(site_url('enter'))->with('error', lang('Messages.flash_25'));
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

        return redirect()->to(site_url('admin/users'))->with('message', lang('Messages.flash_26'));
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

    /**
     * Renderiza el Dashboard exclusivo para clientes de Solvencia & Perfil de Riesgo Mercantil
     */
    private function renderRiskDashboard($user, array $data)
    {
        $db = \Config\Database::connect();
        $userId = (int)($user->id ?? session('user_id'));

        // 1. Verificar si tiene suscripción activa a Solvencia Pro
        $activeRiskSub = $db->table('user_subscriptions')
            ->select('user_subscriptions.*, api_plans.name as plan_name, api_plans.slug as plan_slug, api_plans.product_type')
            ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id')
            ->where('user_subscriptions.user_id', $userId)
            ->groupStart()
                ->where('api_plans.product_type', 'risk')
                ->orWhere('api_plans.product_type', 'bundle')
                ->orWhere('api_plans.slug', 'risk_pro')
            ->groupEnd()
            ->groupStart()
                ->where('user_subscriptions.status', 'active')
                ->orGroupStart()
                    ->where('user_subscriptions.status', 'canceled')
                    ->where('user_subscriptions.current_period_end >', date('Y-m-d H:i:s'))
                ->groupEnd()
            ->groupEnd()
            ->orderBy('FIELD(user_subscriptions.status, "active", "canceled")', 'ASC', false)
            ->orderBy('user_subscriptions.current_period_end', 'DESC')
            ->get()->getRow();

        $isSubscriber = !empty($activeRiskSub);
        $planName = $isSubscriber ? ($activeRiskSub->plan_name ?? 'Solvencia Pro') : 'Plan Gratuito';

        /*
         * 2. Consultas de riesgo usadas en el mes natural actual.
         *
         * Sale del servicio, que es quien decide la regla. Aquí había una copia
         * literal de la consulta —"empresas con alguna vista este mes"—, así que al
         * corregir la regla en el servicio (cuenta la PRIMERA vista, y las empresas
         * compradas no consumen) el panel se habría quedado diciendo otro número
         * que la ficha. Ya ha pasado tres veces en este flujo con otros textos.
         */
        $viewsUsed = (new \App\Services\CompanyRiskService())->consultasDelMes($userId);
        // El 3 estaba escrito a mano aquí y en el servicio por separado.
        helper('company');
        $viewsLimitFree = (int) solvencia('consultasGratis', 3);
        /*
         * El suscriptor TAMBIÉN tiene tope: 300 al mes. Aquí seguía saliendo
         * 'unlimited' y "Sin límite práctico", que es la frase que ya quitamos de la
         * vista del panel. Hoy ninguna plantilla imprime estas dos variables, así que
         * no se veía; pero es una mina puesta para el día que alguien las use.
         */
        $viewsLimitPro  = (int) solvencia('consultasPro', 300);
        $viewsLimit     = $isSubscriber ? $viewsLimitPro : $viewsLimitFree;
        $viewsRemaining = max(0, $viewsLimit - $viewsUsed);

        // Fecha de renovación de cuota mensual
        $nextCycleDate = date('d/m/Y', strtotime('first day of next month'));

        // 3. Historial de auditorías realizadas por el usuario
        $rawHistory = $db->table('user_events')
            ->select('trigger_type as cif, MAX(created_at) as last_view_at, COUNT(id) as total_views')
            ->where('user_id', $userId)
            ->where('event_type', 'view_risk_profile')
            ->where('trigger_type IS NOT NULL')
            ->where('trigger_type !=', '')
            ->groupBy('trigger_type')
            ->orderBy('last_view_at', 'DESC')
            ->limit(30)
            ->get()->getResultArray();

        $historyCifs = array_filter(array_map('trim', array_column($rawHistory, 'cif')));
        
        $companiesMap = [];
        $riskProfilesMap = [];

        if (!empty($historyCifs)) {
            // Cargar empresas asociadas
            $compRows = $db->table('companies')
                ->select('id, cif, company_name, cnae_code, cnae_label, registro_mercantil as province')
                ->whereIn('cif', $historyCifs)
                ->get()->getResultArray();

            foreach ($compRows as $cr) {
                $companiesMap[strtoupper(trim($cr['cif']))] = $cr;
            }

            // Cargar perfiles de riesgo calculados
            $riskRows = $db->table('company_risk_profiles')
                ->select('cif, risk_score, risk_profile, updated_at')
                ->whereIn('cif', $historyCifs)
                ->get()->getResultArray();

            foreach ($riskRows as $rr) {
                $parsedData = !empty($rr['risk_profile']) ? json_decode($rr['risk_profile'], true) : [];
                $riskProfilesMap[strtoupper(trim($rr['cif']))] = [
                    'risk_score'   => (int)($rr['risk_score'] ?? 50),
                    'risk_level'   => $parsedData['risk_level'] ?? risk_level_visual((int) $rr['risk_score'])[0],
                    'summary'      => $parsedData['summary_message'] ?? 'Perfil mercantil procesado.',
                    'alerts_count' => count($parsedData['canonical_events'] ?? [])
                ];
            }
        }

        $audits = [];
        foreach ($rawHistory as $h) {
            $cleanCif = strtoupper(trim($h['cif']));
            $comp = $companiesMap[$cleanCif] ?? null;
            // Sin perfil calculado se decía "50 / MEDIO · Sin incidencias": un dato
            // inventado presentado como resultado. Ahora va a null y la vista pinta
            // "Sin calcular".
            $risk = $riskProfilesMap[$cleanCif] ?? [
                'risk_score'   => null,
                'risk_level'   => '',
                'summary'      => '',
                'alerts_count' => null
            ];

            $compId = (int)($comp['id'] ?? 0);
            $compName = $comp['company_name'] ?? ('Empresa ' . $cleanCif);
            $compSlug = url_title($compName, '-', true);

            $audits[] = [
                'cif'          => $cleanCif,
                'company_id'   => $compId,
                'company_name' => $compName,
                'slug'         => $compSlug,
                'url'          => $compId > 0 ? site_url('empresa/' . $compId . '-' . $compSlug) : site_url('perfil-de-riesgo?cif=' . urlencode($cleanCif)),
                'risk_score'   => $risk['risk_score'],
                'risk_level'   => $risk['risk_level'],
                'summary'      => $risk['summary'],
                'alerts_count' => $risk['alerts_count'],
                'cnae_label'   => $comp['cnae_label'] ?? '',
                'provincia'    => $comp['province'] ?? '',
                'last_view_at' => $h['last_view_at'],
                'total_views'  => (int)$h['total_views'],
                'unlocked'     => true
            ];
        }

        // ---------------------------------------------------------------
        // Vigilancia del BORME.
        // Sin esta pantalla la lista solo crece: se entra en vigilancia al
        // desbloquear una empresa, y un usuario que audita clientes acaba con
        // treinta avisos que no pidió. Aquí la ve y la poda.
        // ---------------------------------------------------------------
        $watches = [];
        try {
            if ($db->tableExists('user_company_watch')) {
                $watchRows = $db->table('user_company_watch w')
                    ->select('w.cif, w.company_id, w.source, w.last_notified_at, w.created_at, c.company_name')
                    ->join('companies c', 'c.id = w.company_id', 'left')
                    ->where('w.user_id', $userId)
                    ->where('w.active', 1)
                    ->orderBy('w.last_notified_at IS NULL', 'ASC', false)
                    ->orderBy('w.last_notified_at', 'DESC')
                    ->orderBy('w.id', 'DESC')
                    ->limit(100)
                    ->get()->getResultArray();

                foreach ($watchRows as $w) {
                    $cifW  = strtoupper(trim((string) $w['cif']));
                    $nameW = $w['company_name'] ?: ($companiesMap[$cifW]['company_name'] ?? ('Empresa ' . $cifW));
                    $idW   = (int) ($w['company_id'] ?? 0);
                    $slugW = url_title($nameW, '-', true);

                    $watches[] = [
                        'cif'              => $cifW,
                        'company_name'     => $nameW,
                        'url'              => $idW > 0
                            ? site_url('empresa/' . $idW . '-' . $slugW)
                            : site_url('perfil-de-riesgo?cif=' . urlencode($cifW)),
                        'source'           => (string) ($w['source'] ?? 'auto'),
                        'last_notified_at' => $w['last_notified_at'] ?? null,
                        'created_at'       => $w['created_at'] ?? null,
                    ];
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[Dashboard] No se pudo leer la vigilancia: ' . $e->getMessage());
        }

        // Tickets contestados por administración
        $ticketModel = new \App\Models\TicketModel();
        $answeredTickets = $ticketModel->where('user_id', $userId)
                                       ->where('status', 'answered')
                                       ->findAll();

        $viewData = array_merge($data, [
            'title'           => 'Panel de Solvencia & Riesgo Mercantil | APIEmpresas',
            'user'            => $user,
            'isSubscriber'    => $isSubscriber,
            'planName'        => $planName,
            'viewsUsed'       => $viewsUsed,
            'viewsLimit'      => $viewsLimit,
            'viewsRemaining'  => $viewsRemaining,
            'nextCycleDate'   => $nextCycleDate,
            'audits'          => $audits,
            'watches'         => $watches,
            'watchQuota'      => (new \App\Services\CompanyWatchService())->estadoCupo($userId),
            'alertsBorme'     => $user->alerts_borme ?? null,
            'answeredTickets' => $answeredTickets,
            'api_key'         => $data['api_key'] ?? null,
            'initialCif'      => trim((string)$this->request->getGet('cif'))
        ]);

        return $this->renderView('risk_profile/dashboard', $viewData);
    }
}


