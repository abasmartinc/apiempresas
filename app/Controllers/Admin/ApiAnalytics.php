<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Database;

class ApiAnalytics extends BaseController
{
    /**
     * Dashboard analítico de Negocio para la API y Desarrolladores
     */
    public function index()
    {
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $db = Database::connect();

        // Filtro de periodo
        $period = $this->request->getGet('period') ?? 'this_month';
        $userStatusFilter = $this->request->getGet('status_filter') ?? 'all';
        $search = trim($this->request->getGet('q') ?? '');
        $sort = $this->request->getGet('sort') ?? 'usage_desc';

        // Rango de fechas según periodo
        $startOfMonth = date('Y-m-01 00:00:00');
        $endOfMonth = date('Y-m-t 23:59:59');

        switch ($period) {
            case 'last_month':
                $dateFrom = date('Y-m-01 00:00:00', strtotime('first day of last month'));
                $dateTo = date('Y-m-t 23:59:59', strtotime('last month'));
                $prevDateFrom = date('Y-m-01 00:00:00', strtotime('first day of -2 month'));
                $prevDateTo = date('Y-m-t 23:59:59', strtotime('-2 month'));
                $periodLabel = 'Mes anterior (' . ucfirst($this->spanishMonthName(strtotime('first day of last month'))) . ')';
                break;
            case 'last_30d':
                $dateFrom = date('Y-m-d 00:00:00', strtotime('-30 days'));
                $dateTo = date('Y-m-d 23:59:59');
                $prevDateFrom = date('Y-m-d 00:00:00', strtotime('-60 days'));
                $prevDateTo = date('Y-m-d 23:59:59', strtotime('-31 days'));
                $periodLabel = 'Últimos 30 días';
                break;
            case 'this_year':
                $dateFrom = date('Y-01-01 00:00:00');
                $dateTo = date('Y-12-31 23:59:59');
                $prevDateFrom = date((date('Y') - 1) . '-01-01 00:00:00');
                $prevDateTo = date((date('Y') - 1) . '-12-31 23:59:59');
                $periodLabel = 'Año ' . date('Y');
                break;
            case 'all':
                $dateFrom = null;
                $dateTo = null;
                $prevDateFrom = null;
                $prevDateTo = null;
                $periodLabel = 'Todo el histórico';
                break;
            case 'this_month':
            default:
                $period = 'this_month';
                $dateFrom = $startOfMonth;
                $dateTo = $endOfMonth;
                $prevDateFrom = date('Y-m-01 00:00:00', strtotime('first day of last month'));
                $prevDateTo = date('Y-m-t 23:59:59', strtotime('last month'));
                $periodLabel = 'Este mes (' . ucfirst($this->spanishMonthName(time())) . ')';
                break;
        }

        // Helper para cálculo de tendencia
        $calcTrend = function($current, $previous) {
            $diff = $current - $previous;
            if ($previous == 0) {
                $percent = $current > 0 ? 100 : 0;
            } else {
                $percent = round(($diff / $previous) * 100, 1);
            }
            return [
                'current' => $current,
                'previous' => $previous,
                'diff' => $diff,
                'percent' => abs($percent),
                'direction' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'neutral'),
                'formatted_percent' => ($diff > 0 ? '+' : ($diff < 0 ? '-' : '')) . abs($percent) . '%'
            ];
        };

        // -------------------------------------------------------------
        // 1. OBTENER LA COHORTE DE USUARIOS DE LA API
        // -------------------------------------------------------------
        // Usuarios con intención 'api' o que hayan usado la API / tengan plan API, no administradores
        $cohortUsersQuery = $db->query("
            SELECT DISTINCT u.id, u.name, u.email, u.company, u.created_at, u.last_login_at, u.is_active, u.unsuscribe
            FROM users u
            WHERE u.is_admin = 0
              AND (
                  u.signup_intent = 'api'
                  OR u.id IN (SELECT DISTINCT user_id FROM api_keys)
                  OR u.id IN (SELECT DISTINCT user_id FROM api_usage_daily)
                  OR u.id IN (
                      SELECT us.user_id FROM user_subscriptions us 
                      JOIN api_plans ap ON ap.id = us.plan_id 
                      WHERE ap.product_type = 'api'
                  )
              )
            ORDER BY u.created_at DESC
        ");
        $allCohortUsers = $cohortUsersQuery->getResultArray();
        $cohortUserIds = array_column($allCohortUsers, 'id');
        $totalApiUsers = count($allCohortUsers);

        // Nuevos desarrolladores registrados en el periodo vs periodo previo
        $newUsersCurrent = 0;
        $newUsersPrev = 0;
        if (!empty($cohortUserIds)) {
            $newUsersQuery = $db->table('users')->whereIn('id', $cohortUserIds);
            if ($dateFrom) $newUsersQuery->where('created_at >=', $dateFrom)->where('created_at <=', $dateTo);
            $newUsersCurrent = $newUsersQuery->countAllResults();

            $newUsersPrevQuery = $db->table('users')->whereIn('id', $cohortUserIds);
            if ($prevDateFrom) $newUsersPrevQuery->where('created_at >=', $prevDateFrom)->where('created_at <=', $prevDateTo);
            $newUsersPrev = $newUsersPrevQuery->countAllResults();
        }
        $trendNewUsers = $calcTrend($newUsersCurrent, $newUsersPrev);

        // -------------------------------------------------------------
        // 2. PETICIONES Y CONSUMO EN EL PERIODO
        // -------------------------------------------------------------
        $reqsCurrent = 0;
        $reqsPrev = 0;
        if (!empty($cohortUserIds)) {
            // Peticiones actuales (desde api_usage_daily)
            $usageQ = $db->table('api_usage_daily')->selectSum('requests_count')->whereIn('user_id', $cohortUserIds);
            if ($dateFrom) {
                $usageQ->where('date >=', substr($dateFrom, 0, 10))->where('date <=', substr($dateTo, 0, 10));
            }
            $resCurrent = $usageQ->get()->getRowArray();
            $reqsCurrent = (int)($resCurrent['requests_count'] ?? 0);

            // Peticiones periodo anterior
            if ($prevDateFrom) {
                $usagePrevQ = $db->table('api_usage_daily')->selectSum('requests_count')->whereIn('user_id', $cohortUserIds);
                $usagePrevQ->where('date >=', substr($prevDateFrom, 0, 10))->where('date <=', substr($prevDateTo, 0, 10));
                $resPrev = $usagePrevQ->get()->getRowArray();
                $reqsPrev = (int)($resPrev['requests_count'] ?? 0);
            }
        }
        $trendRequests = $calcTrend($reqsCurrent, $reqsPrev);

        // -------------------------------------------------------------
        // 3. SUSCRIPCIONES DE PAGO Y MRR
        // -------------------------------------------------------------
        $activePaidSubs = $db->table('user_subscriptions')
            ->select('user_subscriptions.user_id, user_subscriptions.plan_id, user_subscriptions.current_period_end, api_plans.name as plan_name, api_plans.slug as plan_slug, api_plans.price_monthly, api_plans.monthly_quota')
            ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id')
            ->where('user_subscriptions.status', 'active')
            ->where('api_plans.price_monthly >', 0)
            ->where('api_plans.product_type', 'api')
            ->get()->getResultArray();

        $paidUserMap = [];
        $mrrTotal = 0.0;
        foreach ($activePaidSubs as $sub) {
            $paidUserMap[(int)$sub['user_id']] = $sub;
            $mrrTotal += (float)($sub['price_monthly'] ?? 0.0);
        }
        $paidSubscribers = count($paidUserMap);
        $conversionRate = $totalApiUsers > 0 ? round(($paidSubscribers / $totalApiUsers) * 100, 1) : 0;

        // -------------------------------------------------------------
        // 4. MAPAS DE CONSUMO POR USUARIO (Mes actual e histórico)
        // -------------------------------------------------------------
        // Consumo del mes actual para control de cuota (100 peticiones en free)
        $monthUsageByUser = [];
        if (!empty($cohortUserIds)) {
            $curMonthUsage = $db->table('api_usage_daily')
                ->select('user_id, SUM(requests_count) as total_month')
                ->whereIn('user_id', $cohortUserIds)
                ->where('date >=', substr($startOfMonth, 0, 10))
                ->groupBy('user_id')
                ->get()->getResultArray();
            foreach ($curMonthUsage as $row) {
                $monthUsageByUser[(int)$row['user_id']] = (int)$row['total_month'];
            }
        }

        // Consumo histórico total por usuario
        $historyUsageByUser = [];
        if (!empty($cohortUserIds)) {
            $allUsage = $db->table('api_usage_daily')
                ->select('user_id, SUM(requests_count) as total_history')
                ->whereIn('user_id', $cohortUserIds)
                ->groupBy('user_id')
                ->get()->getResultArray();
            foreach ($allUsage as $row) {
                $historyUsageByUser[(int)$row['user_id']] = (int)$row['total_history'];
            }
        }

        // Estado de API Keys por usuario
        $apiKeysByUser = [];
        if (!empty($cohortUserIds)) {
            $keysRows = $db->table('api_keys')
                ->select('user_id, is_active, created_at, last_used_at')
                ->whereIn('user_id', $cohortUserIds)
                ->get()->getResultArray();
            foreach ($keysRows as $k) {
                $uId = (int)$k['user_id'];
                if (!isset($apiKeysByUser[$uId])) {
                    $apiKeysByUser[$uId] = [];
                }
                $apiKeysByUser[$uId][] = $k;
            }
        }

        // Estadísticas de errores y última petición desde api_requests (últimos 30 días para rendimiento)
        $userRequestsStats = [];
        $topEndpointsMap = [];
        if (!empty($cohortUserIds)) {
            $recentReqs = $db->table('api_requests')
                ->select('user_id, endpoint, status_code, created_at')
                ->whereIn('user_id', $cohortUserIds)
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-60 days')))
                ->orderBy('created_at', 'DESC')
                ->get()->getResultArray();

            foreach ($recentReqs as $r) {
                $uId = (int)$r['user_id'];
                $sc = (int)$r['status_code'];
                $ep = trim((string)$r['endpoint']);

                if (!isset($userRequestsStats[$uId])) {
                    $userRequestsStats[$uId] = [
                        'total_recent' => 0,
                        'errors_count' => 0,
                        'last_endpoint' => $ep,
                        'last_request_at' => $r['created_at'],
                        'endpoints' => []
                    ];
                }
                $userRequestsStats[$uId]['total_recent']++;
                if ($sc >= 400) {
                    $userRequestsStats[$uId]['errors_count']++;
                }
                $userRequestsStats[$uId]['endpoints'][$ep] = ($userRequestsStats[$uId]['endpoints'][$ep] ?? 0) + 1;

                // Global endpoints
                $topEndpointsMap[$ep] = ($topEndpointsMap[$ep] ?? 0) + 1;
            }
        }
        arsort($topEndpointsMap);

        // -------------------------------------------------------------
        // 5. CLASIFICACIÓN DE CADA USUARIO Y EMBUDO DE ADOPCIÓN (FUNNEL)
        // -------------------------------------------------------------
        $countLimitReached = 0;
        $countNearLimit = 0;
        $countActiveFree = 0;
        $countInactive = 0;
        $countErrors = 0;
        $countPaid = 0;

        // Pasos del embudo de adopción
        $funnel_step1_registered = $totalApiUsers;
        $funnel_step2_activated = 0;   // Generó API Key o hizo 1ª llamada
        $funnel_step3_engaged = 0;     // Hizo 5+ llamadas
        $funnel_step4_high_usage = 0;  // Alcanzó o rozó el límite (>=80 peticiones)
        $funnel_step5_paid = $paidSubscribers;

        $classifiedUsers = [];

        foreach ($allCohortUsers as $u) {
            $uId = (int)$u['id'];
            $monthReqs = $monthUsageByUser[$uId] ?? 0;
            $historyReqs = $historyUsageByUser[$uId] ?? 0;
            $isPaid = isset($paidUserMap[$uId]);
            $subData = $paidUserMap[$uId] ?? null;

            $hasApiKey = !empty($apiKeysByUser[$uId]);
            $stats = $userRequestsStats[$uId] ?? [
                'total_recent' => 0,
                'errors_count' => 0,
                'last_endpoint' => null,
                'last_request_at' => null,
                'endpoints' => []
            ];

            // Cuota del plan: si es de pago usa la del plan, si es free = 100
            $quota = $isPaid ? (int)($subData['monthly_quota'] ?? 3000) : 100;
            $usagePct = $quota > 0 ? min(100, round(($monthReqs / $quota) * 100)) : 0;

            // Embudo:
            if ($historyReqs >= 1 || $hasApiKey) {
                $funnel_step2_activated++;
            }
            if ($historyReqs >= 5) {
                $funnel_step3_engaged++;
            }
            if ($monthReqs >= 80 || $historyReqs >= 80) {
                $funnel_step4_high_usage++;
            }

            // Estado para segmentación
            $status = 'inactive';
            $statusLabel = 'Inactivo (0 reqs)';
            $statusBadge = 'neutral';

            if ($isPaid) {
                $countPaid++;
                $status = 'paid';
                $statusLabel = 'Cliente ' . ($subData['plan_name'] ?? 'Pro');
                $statusBadge = 'success';
            } elseif ($monthReqs >= $quota) {
                $countLimitReached++;
                $status = 'limit_reached';
                $statusLabel = 'Límite 100% Agotado';
                $statusBadge = 'danger';
            } elseif ($monthReqs >= 80) {
                $countNearLimit++;
                $status = 'near_limit';
                $statusLabel = 'Cerca del Límite (80%+)';
                $statusBadge = 'warning';
            } elseif ($stats['errors_count'] > 0 && ($stats['errors_count'] / max(1, $stats['total_recent'])) >= 0.5) {
                $countErrors++;
                $status = 'errors';
                $statusLabel = 'Errores 400 frecuentes';
                $statusBadge = 'purple';
            } elseif ($monthReqs > 0) {
                $countActiveFree++;
                $status = 'active_free';
                $statusLabel = 'Activo Free (' . $monthReqs . '/100)';
                $statusBadge = 'info';
            } else {
                $countInactive++;
                $status = 'inactive';
                $statusLabel = 'Sin llamadas (0)';
                $statusBadge = 'neutral';
            }

            // Endpoints ordenados de este usuario
            $userTopEndpoints = array_slice($stats['endpoints'], 0, 3, true);

            $classifiedUsers[] = [
                'user'             => $u,
                'is_paid'          => $isPaid,
                'plan_name'        => $isPaid ? ($subData['plan_name'] ?? 'Pro') : 'Free (100)',
                'plan_price'       => $isPaid ? (float)($subData['price_monthly'] ?? 19.0) : 0.0,
                'quota'            => $quota,
                'month_requests'   => $monthReqs,
                'history_requests' => $historyReqs,
                'usage_pct'        => $usagePct,
                'has_api_key'      => $hasApiKey,
                'recent_errors'    => $stats['errors_count'],
                'recent_total'     => $stats['total_recent'],
                'last_request_at'  => $stats['last_request_at'],
                'last_endpoint'    => $stats['last_endpoint'],
                'user_endpoints'   => $userTopEndpoints,
                'status'           => $status,
                'status_label'     => $statusLabel,
                'status_badge'     => $statusBadge
            ];
        }

        // -------------------------------------------------------------
        // 6. FILTRADO Y BÚSQUEDA DE LA TABLA
        // -------------------------------------------------------------
        $filteredUsers = array_filter($classifiedUsers, function($row) use ($userStatusFilter, $search) {
            // Filtro por estado
            if ($userStatusFilter !== 'all') {
                if ($userStatusFilter === 'limit_reached' && $row['status'] !== 'limit_reached') return false;
                if ($userStatusFilter === 'near_limit' && $row['status'] !== 'near_limit') return false;
                if ($userStatusFilter === 'active_free' && $row['status'] !== 'active_free') return false;
                if ($userStatusFilter === 'inactive' && $row['status'] !== 'inactive') return false;
                if ($userStatusFilter === 'errors' && $row['status'] !== 'errors') return false;
                if ($userStatusFilter === 'paid' && $row['status'] !== 'paid') return false;
            }

            // Filtro de búsqueda
            if ($search !== '') {
                $needle = mb_strtolower($search);
                $u = $row['user'];
                $matchName    = strpos(mb_strtolower($u['name'] ?? ''), $needle) !== false;
                $matchEmail   = strpos(mb_strtolower($u['email'] ?? ''), $needle) !== false;
                $matchCompany = strpos(mb_strtolower($u['company'] ?? ''), $needle) !== false;
                $matchEndp    = strpos(mb_strtolower($row['last_endpoint'] ?? ''), $needle) !== false;
                return $matchName || $matchEmail || $matchCompany || $matchEndp;
            }

            return true;
        });

        // -------------------------------------------------------------
        // 6.1 ORDENACIÓN DEL LISTADO (POR CONSUMO, FECHA, ERRORES, ETC.)
        // -------------------------------------------------------------
        usort($filteredUsers, function($a, $b) use ($sort) {
            switch ($sort) {
                case 'usage_asc':
                    if ($a['month_requests'] === $b['month_requests']) {
                        return strcmp($b['user']['created_at'] ?? '', $a['user']['created_at'] ?? '');
                    }
                    return $a['month_requests'] <=> $b['month_requests'];

                case 'history_desc':
                    if ($a['history_requests'] === $b['history_requests']) {
                        return $b['month_requests'] <=> $a['month_requests'];
                    }
                    return $b['history_requests'] <=> $a['history_requests'];

                case 'date_desc':
                    return strcmp($b['user']['created_at'] ?? '', $a['user']['created_at'] ?? '');

                case 'date_asc':
                    return strcmp($a['user']['created_at'] ?? '', $b['user']['created_at'] ?? '');

                case 'name_asc':
                    $nameA = $a['user']['name'] ?: $a['user']['email'];
                    $nameB = $b['user']['name'] ?: $b['user']['email'];
                    return strcasecmp($nameA, $nameB);

                case 'errors_desc':
                    if ($a['recent_errors'] === $b['recent_errors']) {
                        return $b['month_requests'] <=> $a['month_requests'];
                    }
                    return $b['recent_errors'] <=> $a['recent_errors'];

                case 'usage_desc':
                default:
                    if ($a['month_requests'] === $b['month_requests']) {
                        if ($a['history_requests'] === $b['history_requests']) {
                            return strcmp($b['user']['created_at'] ?? '', $a['user']['created_at'] ?? '');
                        }
                        return $b['history_requests'] <=> $a['history_requests'];
                    }
                    return $b['month_requests'] <=> $a['month_requests'];
            }
        });

        // -------------------------------------------------------------
        // 7. DIAGNÓSTICO DE CAÍDAS (DROP-OFF ANALYSIS)
        // -------------------------------------------------------------
        $dropOffNoUsage = $funnel_step1_registered > 0
            ? round((($funnel_step1_registered - $funnel_step2_activated) / $funnel_step1_registered) * 100, 1)
            : 0;

        $dropOffNoEngage = $funnel_step2_activated > 0
            ? round((($funnel_step2_activated - $funnel_step3_engaged) / $funnel_step2_activated) * 100, 1)
            : 0;

        $dropOffNoConversion = $funnel_step4_high_usage > 0
            ? round((($funnel_step4_high_usage - $funnel_step5_paid) / max(1, $funnel_step4_high_usage)) * 100, 1)
            : 0;

        $data = [
            'title'                 => 'API & Desarrolladores | Decisiones de Negocio',
            'period'                => $period,
            'period_label'          => $periodLabel,
            'user_status_filter'    => $userStatusFilter,
            'search'                => $search,
            'sort'                  => $sort,
            'total_api_users'       => $totalApiUsers,
            'trend_new_users'       => $trendNewUsers,
            'trend_requests'        => $trendRequests,
            'paid_subscribers'      => $paidSubscribers,
            'mrr_total'             => $mrrTotal,
            'conversion_rate'       => $conversionRate,
            // Embudo
            'funnel' => [
                'step1_registered' => $funnel_step1_registered,
                'step2_activated'  => $funnel_step2_activated,
                'step3_engaged'    => $funnel_step3_engaged,
                'step4_high_usage' => $funnel_step4_high_usage,
                'step5_paid'       => $funnel_step5_paid,
            ],
            // Diagnóstico
            'drop_off_no_usage'     => $dropOffNoUsage,
            'drop_off_no_engage'    => $dropOffNoEngage,
            'drop_off_no_conversion'=> $dropOffNoConversion,
            // Contadores de segmentación
            'counts' => [
                'all'           => count($classifiedUsers),
                'limit_reached' => $countLimitReached,
                'near_limit'    => $countNearLimit,
                'active_free'   => $countActiveFree,
                'inactive'      => $countInactive,
                'errors'        => $countErrors,
                'paid'          => $countPaid,
            ],
            'users'                 => $filteredUsers,
            'top_endpoints'         => array_slice($topEndpointsMap, 0, 5, true),
            'email_templates'       => $this->getEmailTemplates()
        ];

        return $this->renderView('admin/api_analytics', $data);
    }

    /**
     * Enviar correo individual a un desarrollador
     */
    public function sendSingleEmail()
    {
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $userId = (int)$this->request->getPost('user_id');
        $subject = trim((string)$this->request->getPost('subject'));
        $message = trim((string)$this->request->getPost('message'));

        if (!$userId || empty($subject) || empty($message)) {
            return redirect()->back()->withInput()->with('error', 'Todos los campos son obligatorios.');
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->back()->with('error', 'Usuario no encontrado.');
        }

        $emailService = \Config\Services::email();
        $emailLogModel = new \App\Models\EmailLogModel();
        $emailHelper = new \App\Services\EmailService();

        $trackingCode = bin2hex(random_bytes(16));

        $finalMessage = str_replace(
            ['{NOMBRE}', '{EMPRESA}', '{SITE_URL}'],
            [$user->name ?: 'desarrollador', $user->company ?: 'tu software', site_url()],
            $message
        );

        $body = view('emails/user_notification', [
            'user' => $user,
            'content' => nl2br(esc($finalMessage, 'raw')),
            'subject' => $subject,
            'tracking_code' => $trackingCode,
            'unsubscribe_url' => $emailHelper->generateUnsubscribeLink($user->email)
        ]);

        $emailService->clear();
        $emailService->setTo($user->email);
        $emailService->setSubject($subject);
        $emailService->setMessage($body);

        $logData = [
            'user_id' => $user->id,
            'subject' => $subject,
            'message' => $finalMessage,
            'tracking_code' => $trackingCode,
            'created_at' => date('Y-m-d H:i:s')
        ];

        if ($emailService->send()) {
            $logData['status'] = 'success';
            $emailLogModel->insert($logData);

            $userEventsModel = new \App\Models\UserEventsModel();
            $userEventsModel->logEvent($user->id, 'email_sent_api_direct', $subject);

            return redirect()->back()->with('message', 'Correo enviado con éxito a ' . esc($user->email));
        } else {
            $logData['status'] = 'error';
            $logData['error_message'] = $emailService->printDebugger(['headers']);
            $emailLogModel->insert($logData);
            return redirect()->back()->withInput()->with('error', 'Error al enviar el correo: ' . $emailService->printDebugger(['headers']));
        }
    }

    /**
     * Enviar correo masivo segmentado a múltiples desarrolladores
     */
    public function sendBulkEmail()
    {
        if (!session('is_admin')) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado.');
        }

        $userIdsRaw = $this->request->getPost('user_ids');
        $subject = trim((string)$this->request->getPost('subject'));
        $message = trim((string)$this->request->getPost('message'));

        if (empty($userIdsRaw) || empty($subject) || empty($message)) {
            return redirect()->back()->withInput()->with('error', 'Debes seleccionar al menos un usuario y completar el asunto y mensaje.');
        }

        $userIds = is_array($userIdsRaw) ? $userIdsRaw : explode(',', (string)$userIdsRaw);
        $userIds = array_values(array_filter(array_map('intval', $userIds)));

        if (empty($userIds)) {
            return redirect()->back()->with('error', 'No se indicaron usuarios válidos para el envío.');
        }

        $userModel = new \App\Models\UserModel();
        $users = $userModel->whereIn('id', $userIds)->findAll();

        if (empty($users)) {
            return redirect()->back()->with('error', 'No se encontraron registros de los usuarios seleccionados.');
        }

        $emailService = \Config\Services::email();
        $emailLogModel = new \App\Models\EmailLogModel();
        $emailHelper = new \App\Services\EmailService();

        $sentCount = 0;
        $errorCount = 0;
        $skippedCount = 0;

        foreach ($users as $user) {
            if ((int)($user->unsuscribe ?? 0) === 1) {
                $skippedCount++;
                continue;
            }

            $emailService->clear();
            $emailService->setTo($user->email);
            $emailService->setSubject($subject);

            $trackingCode = bin2hex(random_bytes(16));

            $finalMessage = str_replace(
                ['{NOMBRE}', '{EMPRESA}', '{SITE_URL}'],
                [$user->name ?: 'desarrollador', $user->company ?: 'su empresa', site_url()],
                $message
            );

            $body = view('emails/user_notification', [
                'user' => $user,
                'content' => nl2br(esc($finalMessage, 'raw')),
                'subject' => $subject,
                'tracking_code' => $trackingCode,
                'unsubscribe_url' => $emailHelper->generateUnsubscribeLink($user->email)
            ]);

            $emailService->setMessage($body);

            $logData = [
                'user_id' => $user->id,
                'subject' => $subject,
                'message' => $finalMessage,
                'tracking_code' => $trackingCode,
                'created_at' => date('Y-m-d H:i:s')
            ];

            if ($emailService->send()) {
                $logData['status'] = 'success';
                $sentCount++;
            } else {
                $logData['status'] = 'error';
                $logData['error_message'] = $emailService->printDebugger(['headers']);
                $errorCount++;
            }

            $emailLogModel->insert($logData);
        }

        $msg = "Campaña procesada: {$sentCount} email(s) enviados correctamente.";
        if ($skippedCount > 0) {
            $msg .= " ({$skippedCount} omitido(s) por baja voluntaria).";
        }
        if ($errorCount > 0) {
            $msg .= " {$errorCount} fallaron.";
            return redirect()->back()->with('message', $msg)->with('error', "Hubo {$errorCount} envíos con error. Revisa el log de emails.");
        }

        return redirect()->back()->with('message', $msg);
    }

    /**
     * Plantillas predeterminadas de correo para Desarrolladores de la API
     */
    private function getEmailTemplates()
    {
        return [
            [
                'id' => 'api_limit_upgrade',
                'name' => '🚨 Límite de Cuota Alcanzado (Oferta Pro 19€/mes)',
                'subject' => '¿Necesitas ampliar tu cuota de la API, {NOMBRE}?',
                'body' => "Hola {NOMBRE},\n\nHemos detectado que has alcanzado el 100% de la cuota mensual de peticiones gratuitas en APIEmpresas.\n\nPara evitar que tus peticiones sigan devolviendo error 429 (Too Many Requests) o se interrumpa tu integración, puedes activar de forma inmediata el Plan Pro (19 € / mes):\n• 3.000 peticiones mensuales incluidas\n• Acceso a todos los endpoints de empresas, administradores y BORME\n• Sin permanencia (cancela cuando quieras en 1 clic)\n\nPuedes ampliar tu plan directamente aquí:\n{SITE_URL}/billing?plan=pro\n\nSi necesitas un volumen a medida o presupuesto para empresa, responde a este correo y lo preparamos en el día.\n\nUn saludo,\nEl equipo de soporte de APIEmpresas"
            ],
            [
                'id' => 'api_tech_support_errors',
                'name' => '🛠️ Asistencia Técnica por Errores 400 en Integración',
                'subject' => '¿Te ayudamos con la integración de la API de APIEmpresas, {NOMBRE}?',
                'body' => "Hola {NOMBRE},\n\nRevisando el registro de llamadas de tu cuenta, hemos detectado varias peticiones con error 400 (Bad Request) o parámetros incorrectos en tus llamadas recientes.\n\nQueremos asegurarnos de que tu integración funcione a la primera:\n• Tienes la documentación interactiva y ejemplos en PHP, Python, Node y cURL aquí:\n{SITE_URL}/documentation\n• Colección de Postman lista para importar y probar con tu API Key.\n\nSi quieres, responde directamente a este correo indicándonos el endpoint o el mensaje de error que estás recibiendo y uno de nuestros ingenieros te echará una mano de inmediato.\n\nUn saludo,\nSoporte Técnico APIEmpresas"
            ],
            [
                'id' => 'api_inactive_nudge',
                'name' => '⚡ Recordatorio de Integración (0 peticiones)',
                'subject' => 'Configura tu integración con la API en 2 minutos (Tu API Key está lista)',
                'body' => "Hola {NOMBRE},\n\nTu cuenta de desarrollador en APIEmpresas ya está configurada con 100 peticiones gratuitas para probar en tu software o CRM.\n\nPara empezar solo necesitas copiar tu API Key y hacer una llamada de prueba:\n\ncurl -X GET \"{SITE_URL}/api/v1/companies/search?q=mercadona\" \\\n     -H \"X-API-KEY: TU_API_KEY\"\n\nPuedes ver tus credenciales y probar los endpoints en vivo en la documentación:\n{SITE_URL}/documentation\n\n¿Tienes alguna duda con la autenticación o los datos que devuelve la API? Responde a este correo y te asesoramos.\n\nUn saludo,\nEl equipo de APIEmpresas"
            ],
            [
                'id' => 'api_near_limit',
                'name' => '⚠️ Alerta: 80% de la Cuota Consumida',
                'subject' => 'Aviso: Has consumido el 80% de tu cuota de peticiones este mes',
                'body' => "Hola {NOMBRE},\n\nTe informamos de que has alcanzado el 80% de las peticiones mensuales incluidas en tu cuenta de APIEmpresas.\n\nSi estás en fase de pruebas o pasando a producción, te recomendamos revisar el Plan Pro (19 €/mes para 3.000 peticiones) para que tus servicios no se detengan al llegar al 100%:\n{SITE_URL}/billing?plan=pro\n\nQuedamos a tu disposición para lo que necesites.\n\nUn saludo,\nEl equipo de APIEmpresas"
            ]
        ];
    }

    private function spanishMonthName($timestamp)
    {
        $meses = [
            1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
            5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
            9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
        ];
        $m = (int)date('n', $timestamp);
        return $meses[$m] ?? date('F', $timestamp);
    }
}
