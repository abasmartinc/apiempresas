<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Database;

class RiskProfileAnalytics extends BaseController
{
    /**
     * Dashboard analítico de Perfil de Riesgo / Solvencia
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
        // 1. OBTENER LA COHORTE EXACTA DE USUARIOS
        // -------------------------------------------------------------
        // Usuarios con intención 'view_risk_profile', excluyendo cuentas de administración
        $allCohortUsers = $db->table('users')
            ->select('id, name, email, company, created_at, last_login_at, is_active')
            ->where('signup_intent', 'view_risk_profile')
            ->where('is_admin', 0)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();

        $cohortUserIds = array_column($allCohortUsers, 'id');
        $totalRiskUsers = count($allCohortUsers);

        // Nuevos usuarios registrados en el periodo vs periodo previo
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

        // Consultas realizadas de perfil de riesgo únicamente por usuarios de la cohorte
        $viewsCurrent = 0;
        $viewsPrev = 0;
        if (!empty($cohortUserIds)) {
            $viewsQuery = $db->table('user_events')
                ->where('event_type', 'view_risk_profile')
                ->whereIn('user_id', $cohortUserIds);
            if ($dateFrom) $viewsQuery->where('created_at >=', $dateFrom)->where('created_at <=', $dateTo);
            $viewsCurrent = $viewsQuery->countAllResults();

            $viewsPrevQuery = $db->table('user_events')
                ->where('event_type', 'view_risk_profile')
                ->whereIn('user_id', $cohortUserIds);
            if ($prevDateFrom) $viewsPrevQuery->where('created_at >=', $prevDateFrom)->where('created_at <=', $prevDateTo);
            $viewsPrev = $viewsPrevQuery->countAllResults();
        }
        $trendViews = $calcTrend($viewsCurrent, $viewsPrev);

        // Consultas este mes para control de límites de 3 consultas
        $currentMonthEvents = [];
        if (!empty($cohortUserIds)) {
            $currentMonthEvents = $db->table('user_events')
                ->select('user_id, trigger_type, created_at')
                ->where('event_type', 'view_risk_profile')
                ->whereIn('user_id', $cohortUserIds)
                ->where('created_at >=', $startOfMonth)
                ->get()->getResultArray();
        }

        $monthDistinctCifsByUser = [];
        foreach ($currentMonthEvents as $ev) {
            $uId = (int)$ev['user_id'];
            $cleanCif = strtoupper(trim($ev['trigger_type'] ?? ''));
            if ($cleanCif === '') continue;
            if (!isset($monthDistinctCifsByUser[$uId])) {
                $monthDistinctCifsByUser[$uId] = [];
            }
            $monthDistinctCifsByUser[$uId][$cleanCif] = true;
        }

        // Consultas históricas completas de la cohorte
        $allEvents = [];
        if (!empty($cohortUserIds)) {
            $allEvents = $db->table('user_events')
                ->select('user_id, trigger_type as cif, created_at')
                ->where('event_type', 'view_risk_profile')
                ->whereIn('user_id', $cohortUserIds)
                ->orderBy('created_at', 'DESC')
                ->get()->getResultArray();
        }

        $historyDistinctCifsByUser = [];
        $userHistoryMap = [];
        $lastViewMap = [];
        $allCifs = [];
        foreach ($allEvents as $ev) {
            $uId = (int)$ev['user_id'];
            $cleanCif = strtoupper(trim($ev['cif'] ?? ''));
            if ($cleanCif === '') continue;
            $allCifs[$cleanCif] = true;
            if (!isset($historyDistinctCifsByUser[$uId])) {
                $historyDistinctCifsByUser[$uId] = [];
            }
            $historyDistinctCifsByUser[$uId][$cleanCif] = true;

            if (!isset($userHistoryMap[$uId])) {
                $userHistoryMap[$uId] = [];
                $lastViewMap[$uId] = $ev;
            }
            $userHistoryMap[$uId][] = $ev;
        }

        // Nombres de empresas auditadas
        $companyMap = [];
        if (!empty($allCifs)) {
            $companies = $db->table('companies')
                ->select('cif, company_name, cnae_code, cnae_label, registro_mercantil as province')
                ->whereIn('cif', array_slice(array_keys($allCifs), 0, 150))
                ->get()->getResultArray();
            foreach ($companies as $comp) {
                $companyMap[strtoupper(trim($comp['cif']))] = $comp;
            }
        }

        // Suscriptores de pago (Solvencia Pro) dentro de la cohorte
        $paidUserIds = [];
        if (!empty($cohortUserIds)) {
            $activeSubs = $db->table('user_subscriptions')
                ->select('user_subscriptions.user_id')
                ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id')
                ->where('user_subscriptions.status', 'active')
                ->whereIn('user_subscriptions.user_id', $cohortUserIds)
                ->groupStart()
                    ->where('api_plans.slug', 'risk_pro')
                    ->orWhere('api_plans.product_type', 'risk')
                ->groupEnd()
                ->get()->getResultArray();
            $paidUserIds = array_column($activeSubs, 'user_id');
        }

        $paidSubscribers = count($paidUserIds);
        $mrrRisk = $paidSubscribers * 29.00;
        $conversionRate = $totalRiskUsers > 0 ? round(($paidSubscribers / $totalRiskUsers) * 100, 1) : 0;

        // -------------------------------------------------------------
        // 2. CLASIFICACIÓN EXACTA DE CADA USUARIO Y CÁLCULO DE EMBUDO
        // -------------------------------------------------------------
        $countLimitReached = 0;
        $countActiveFree = 0;
        $countInactive = 0;
        $countPaid = 0;

        $step2_oneOrMore = 0;
        $step3_twoOrMore = 0;
        $step4_threeOrMore = 0;

        // ---------------------------------------------------------------
        // Embudo por cohorte de alta.
        // El embudo anterior dividía pasos del MES ACTUAL entre TODOS los
        // registrados de la historia, y remataba con los suscriptores de hoy.
        // Con eso el porcentaje de conversión solo podía bajar según crecía la
        // base, midieras lo que midieras. Aquí el denominador y los numeradores
        // son las MISMAS personas: las que se registraron en el periodo elegido,
        // y de ellas se mira lo que han hecho desde entonces.
        // ---------------------------------------------------------------
        $cohorteN     = 0;   // se registraron en el periodo
        $cohorte1     = 0;   // ...y consultaron al menos 1 empresa
        $cohorte2     = 0;   // ...y al menos 2
        $cohorte3     = 0;   // ...y llegaron al límite (3)
        $cohortePago  = 0;   // ...y hoy pagan
        $diasHastaPago = [];

        $classifiedUsers = [];

        foreach ($allCohortUsers as $u) {
            $uId = (int)$u['id'];
            $monthCount = isset($monthDistinctCifsByUser[$uId]) ? count($monthDistinctCifsByUser[$uId]) : 0;
            $historyCount = isset($historyDistinctCifsByUser[$uId]) ? count($historyDistinctCifsByUser[$uId]) : 0;
            $totalEventViews = isset($userHistoryMap[$uId]) ? count($userHistoryMap[$uId]) : 0;
            $isPaid = in_array($uId, $paidUserIds);

            // Última empresa consultada
            $lastView = $lastViewMap[$uId] ?? null;
            if ($lastView) {
                $cleanLastCif = strtoupper(trim($lastView['cif'] ?? ''));
                $lastView['company_name'] = $companyMap[$cleanLastCif]['company_name'] ?? ('Empresa ' . $lastView['cif']);
            }

            // Histórico con nombres de empresa
            $userHistory = [];
            if (isset($userHistoryMap[$uId])) {
                foreach ($userHistoryMap[$uId] as $hItem) {
                    $cleanHCif = strtoupper(trim($hItem['cif'] ?? ''));
                    $hItem['company_name'] = $companyMap[$cleanHCif]['company_name'] ?? ('Empresa ' . $hItem['cif']);
                    $userHistory[] = $hItem;
                }
            }

            // Determinar estado según consumo mensual / plan
            if ($isPaid) {
                $statusType = 'paid';
                $countPaid++;
            } elseif ($monthCount >= 3) {
                $statusType = 'limit_reached'; // Hot Lead (ha llegado al límite de 3 consultas este mes)
                $countLimitReached++;
            } elseif ($historyCount > 0) {
                $statusType = 'active_free';
                $countActiveFree++;
            } else {
                $statusType = 'inactive';
                $countInactive++;
            }

            // Pasos del Embudo (Funnel) dentro de la cohorte:
            // Paso 2: Usuarios que han consultado al menos 1 empresa
            if ($monthCount >= 1 || $historyCount >= 1) {
                $step2_oneOrMore++;
            }
            // Paso 3: Usuarios con 2 o más empresas
            if ($monthCount >= 2 || $historyCount >= 2) {
                $step3_twoOrMore++;
            }
            // Paso 4: Usuarios que han alcanzado el límite (3 empresas / Paywall)
            if ($monthCount >= 3) {
                $step4_threeOrMore++;
            }

            // ¿Este usuario pertenece a la cohorte del periodo elegido?
            $altaTs = strtotime((string)($u['created_at'] ?? ''));
            $enCohorte = ($dateFrom === null)
                || ($altaTs && $altaTs >= strtotime($dateFrom) && $altaTs <= strtotime($dateTo));

            if ($enCohorte) {
                $cohorteN++;
                if ($historyCount >= 1) $cohorte1++;
                if ($historyCount >= 2) $cohorte2++;
                if ($historyCount >= 3) $cohorte3++;
                if ($isPaid) {
                    $cohortePago++;
                    if ($altaTs) {
                        $diasHastaPago[] = max(0, (int) floor((time() - $altaTs) / 86400));
                    }
                }
            }

            $u['month_views'] = $monthCount;
            $u['total_views'] = $totalEventViews;
            $u['is_paid'] = $isPaid;
            $u['status_type'] = $statusType;
            $u['last_view'] = $lastView;
            $u['history'] = array_slice($userHistory, 0, 8);

            $classifiedUsers[] = $u;
        }

        // Tasa de Activación
        $activatedUsersCount = $countLimitReached + $countActiveFree + $countPaid;
        $activationRate = $totalRiskUsers > 0 ? round(($activatedUsersCount / $totalRiskUsers) * 100, 1) : 0;
        $inactiveUsersCount = $countInactive;

        // Mediana de días hasta el pago dentro de la cohorte. La media la
        // distorsiona un solo cliente antiguo; la mediana no.
        $medianaDiasPago = null;
        if (!empty($diasHastaPago)) {
            sort($diasHastaPago);
            $m = count($diasHastaPago);
            $medianaDiasPago = $m % 2
                ? $diasHastaPago[intdiv($m, 2)]
                : (int) round(($diasHastaPago[$m / 2 - 1] + $diasHastaPago[$m / 2]) / 2);
        }

        // Funnel estructurado: denominador y numeradores son la misma cohorte.
        $step1_registered = $cohorteN;
        $step2_oneOrMore   = $cohorte1;
        $step3_twoOrMore   = $cohorte2;
        $step4_threeOrMore = $cohorte3;
        $step5_paid        = $cohortePago;

        $funnel = [
            [
                'step' => 1,
                'name' => '1. Registrados',
                'desc' => 'Altas con intención Solvencia en el periodo',
                'count' => $step1_registered,
                'pct_total' => 100,
                'color' => '#3b82f6',
            ],
            [
                'step' => 2,
                'name' => '2. Primera Consulta',
                'desc' => 'De esas altas, las que han auditado ≥ 1 empresa',
                'count' => $step2_oneOrMore,
                'pct_total' => $step1_registered > 0 ? round(($step2_oneOrMore / $step1_registered) * 100, 1) : 0,
                'color' => '#06b6d4',
            ],
            [
                'step' => 3,
                'name' => '3. Interés Recurrente',
                'desc' => 'De esas altas, las que han auditado ≥ 2 empresas',
                'count' => $step3_twoOrMore,
                'pct_total' => $step1_registered > 0 ? round(($step3_twoOrMore / $step1_registered) * 100, 1) : 0,
                'color' => '#f59e0b',
            ],
            [
                'step' => 4,
                'name' => '4. Límite Alcanzado',
                'desc' => 'De esas altas, las que llegaron al límite de 3',
                'count' => $step4_threeOrMore,
                'pct_total' => $step1_registered > 0 ? round(($step4_threeOrMore / $step1_registered) * 100, 1) : 0,
                'color' => '#f43f5e',
            ],
            [
                'step' => 5,
                'name' => '5. Clientes Solvencia Pro',
                'desc' => 'De esas altas, las que pagan Solvencia Pro hoy',
                'count' => $step5_paid,
                'pct_total' => $step1_registered > 0 ? round(($step5_paid / $step1_registered) * 100, 1) : 0,
                'color' => '#10b981',
            ],
        ];

        // -------------------------------------------------------------
        // 3. TOP EMPRESAS INVESTIGADAS (Únicamente por la cohorte)
        // -------------------------------------------------------------
        $topCompanies = [];
        if (!empty($cohortUserIds)) {
            $topCompaniesQuery = $db->table('user_events')
                ->select('trigger_type as cif, COUNT(id) as total_views, COUNT(DISTINCT user_id) as unique_users, MAX(created_at) as last_viewed_at')
                ->where('event_type', 'view_risk_profile')
                ->whereIn('user_id', $cohortUserIds)
                ->groupBy('trigger_type')
                ->orderBy('total_views', 'DESC')
                ->limit(10);

            if ($dateFrom) {
                $topCompaniesQuery->where('created_at >=', $dateFrom)->where('created_at <=', $dateTo);
            }
            $topCifsData = $topCompaniesQuery->get()->getResultArray();

            foreach ($topCifsData as $row) {
                $cleanCif = strtoupper(trim($row['cif']));
                $comp = $companyMap[$cleanCif] ?? null;
                $topCompanies[] = [
                    'cif' => $row['cif'],
                    'total_views' => (int)$row['total_views'],
                    'unique_users' => (int)$row['unique_users'],
                    'last_viewed_at' => $row['last_viewed_at'],
                    'company_name' => $comp['company_name'] ?? ('Empresa ' . $row['cif']),
                    'cnae_code' => $comp['cnae_code'] ?? null,
                    'cnae_label' => $comp['cnae_label'] ?? null,
                    'province' => $comp['province'] ?? null,
                ];
            }
        }

        // -------------------------------------------------------------
        // 4. FILTRADO PARA LA TABLA DE USUARIOS
        // -------------------------------------------------------------
        $filteredUsers = $classifiedUsers;
        if ($search !== '') {
            $searchLower = mb_strtolower($search);
            $filteredUsers = array_values(array_filter($filteredUsers, function($u) use ($searchLower) {
                return (strpos(mb_strtolower($u['name'] ?? ''), $searchLower) !== false) ||
                       (strpos(mb_strtolower($u['email'] ?? ''), $searchLower) !== false) ||
                       (strpos(mb_strtolower($u['company'] ?? ''), $searchLower) !== false);
            }));
        }

        if ($userStatusFilter !== 'all') {
            $filteredUsers = array_values(array_filter($filteredUsers, function($u) use ($userStatusFilter) {
                return $u['status_type'] === $userStatusFilter;
            }));
        }

        // -------------------------------------------------------------
        // 4.1 ORDENACIÓN DE LA TABLA DE USUARIOS
        // -------------------------------------------------------------
        usort($filteredUsers, function($a, $b) use ($sort) {
            switch ($sort) {
                case 'usage_asc':
                    if ($a['month_views'] === $b['month_views']) {
                        return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
                    }
                    return $a['month_views'] <=> $b['month_views'];

                case 'history_desc':
                    if ($a['total_views'] === $b['total_views']) {
                        return $b['month_views'] <=> $a['month_views'];
                    }
                    return $b['total_views'] <=> $a['total_views'];

                case 'date_desc':
                    return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');

                case 'date_asc':
                    return strcmp($a['created_at'] ?? '', $b['created_at'] ?? '');

                case 'name_asc':
                    $nameA = $a['name'] ?: $a['email'];
                    $nameB = $b['name'] ?: $b['email'];
                    return strcasecmp($nameA, $nameB);

                case 'usage_desc':
                default:
                    if ($a['month_views'] === $b['month_views']) {
                        if ($a['total_views'] === $b['total_views']) {
                            return strcmp($b['created_at'] ?? '', $a['created_at'] ?? '');
                        }
                        return $b['total_views'] <=> $a['total_views'];
                    }
                    return $b['month_views'] <=> $a['month_views'];
            }
        });

        // -------------------------------------------------------------
        // Eventos de interfaz (tracking_events).
        // Todo esto se venía guardando y nadie lo leía: son los únicos números
        // que dicen si el teaser, el bloqueo y el paywall convierten, y por qué
        // canal. Incluye a los visitantes anónimos, que el embudo de arriba no ve.
        // -------------------------------------------------------------
        $ui = $this->eventosInterfaz($db, $dateFrom, $dateTo);

        $data = [
            'title' => 'Analítica de Perfil de Riesgo & Solvencia',
            'ui_events' => $ui,
            'mediana_dias_pago' => $medianaDiasPago,
            'period' => $period,
            'period_label' => $periodLabel,
            'user_status_filter' => $userStatusFilter,
            'search' => $search,
            'sort' => $sort,
            'stats' => [
                'total_users' => $totalRiskUsers,
                'new_users' => $newUsersCurrent,
                'trend_new_users' => $trendNewUsers,
                'total_views' => $viewsCurrent,
                'trend_views' => $trendViews,
                'activated_users' => $activatedUsersCount,
                'activation_rate' => $activationRate,
                'inactive_users' => $inactiveUsersCount,
                'hot_leads_count' => $countLimitReached,
                'paid_subscribers' => $paidSubscribers,
                'mrr_risk' => $mrrRisk,
                'conversion_rate' => $conversionRate,
                'count_limit_reached' => $countLimitReached,
                'count_active_free' => $countActiveFree,
                'count_inactive' => $countInactive,
                'count_paid' => $countPaid,
            ],
            'funnel' => $funnel,
            'top_companies' => $topCompanies,
            'users' => $filteredUsers,
            'email_templates' => $this->getEmailTemplates(),
        ];

        return $this->renderView('admin/risk_profile_analytics', $data);
    }

    /**
     * Enviar correo a un usuario individual
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
            return redirect()->back()->withInput()->with('error', 'Por favor, completa el asunto y el cuerpo del mensaje.');
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->back()->with('error', 'El usuario no fue encontrado.');
        }

        if ((int)($user->unsuscribe ?? 0) === 1) {
            return redirect()->back()->with('error', 'El usuario ' . esc($user->email) . ' ha solicitado la baja de correos.');
        }

        $emailService = \Config\Services::email();
        $emailService->clear();
        $emailService->setTo($user->email);
        $emailService->setSubject($subject);

        $trackingCode = bin2hex(random_bytes(16));

        $finalMessage = str_replace(
            ['{NOMBRE}', '{EMPRESA}', '{SITE_URL}'],
            [$user->name ?: 'cliente', $user->company ?: 'su empresa', site_url()],
            $message
        );

        $emailHelper = new \App\Services\EmailService();
        $body = view('emails/user_notification', [
            'user' => $user,
            'content' => nl2br(esc($finalMessage, 'raw')),
            'subject' => $subject,
            'tracking_code' => $trackingCode,
            'unsubscribe_url' => $emailHelper->generateUnsubscribeLink($user->email)
        ]);

        $emailService->setMessage($body);

        $emailLogModel = new \App\Models\EmailLogModel();
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
            return redirect()->back()->with('message', 'Email enviado con éxito a ' . esc($user->email) . '.');
        } else {
            $logData['status'] = 'error';
            $logData['error_message'] = $emailService->printDebugger(['headers']);
            $emailLogModel->insert($logData);
            return redirect()->back()->withInput()->with('error', 'Error al enviar el correo: ' . $emailService->printDebugger(['headers']));
        }
    }

    /**
     * Enviar correo masivo a múltiples usuarios seleccionados
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
                [$user->name ?: 'cliente', $user->company ?: 'su empresa', site_url()],
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
     * Plantillas predeterminadas de correo para Perfil de Riesgo
     */
    /**
     * Lee tracking_events y devuelve los ratios del embudo de interfaz.
     *
     * Deliberadamente tolerante: si la tabla no existe o está vacía devuelve
     * ceros y la vista lo dice, en vez de romper el panel entero.
     */
    private function eventosInterfaz($db, ?string $dateFrom, ?string $dateTo): array
    {
        $vacio = [
            'disponible' => false,
            'total'      => 0,
            'conteos'    => [],
            'teaser'     => ['vistas' => 0, 'clicks' => 0, 'ctr' => 0.0, 'por_canal' => []],
            'locked'     => ['vistas' => 0, 'clicks' => 0, 'ctr' => 0.0],
            'paywall'    => ['vistas' => 0, 'clicks' => 0, 'ctr' => 0.0, 'por_opcion' => []],
            'upsell'     => ['vistas' => 0, 'clicks' => 0, 'ctr' => 0.0, 'por_variante' => []],
            'checkout'   => ['iniciados' => 0, 'completados' => 0, 'ratio' => 0.0, 'por_origen' => []],
        ];

        try {
            if (!$db->tableExists('tracking_events')) {
                return $vacio;
            }

            // Agrupado solo por event_name + element: agrupar también por
            // `metadata` obligaría a la base a ordenar una columna de texto
            // entera. La variante del upsell se saca aparte, más abajo.
            $q = $db->table('tracking_events')
                ->select('event_name, element, COUNT(*) AS n', false)
                ->groupBy(['event_name', 'element']);

            if ($dateFrom) {
                $q->where('created_at >=', $dateFrom)->where('created_at <=', $dateTo);
            }

            $filas = $q->get()->getResultArray();
            if (empty($filas)) {
                return array_merge($vacio, ['disponible' => true]);
            }

            $res = $vacio;
            $res['disponible'] = true;

            $porCanal    = [];
            $porOpcion   = [];
            $porVariante = [];
            $porOrigen   = [];

            foreach ($filas as $f) {
                $nombre = (string) $f['event_name'];
                $elem   = trim((string) ($f['element'] ?? '')) ?: '(sin origen)';
                $n      = (int) $f['n'];

                $res['total'] += $n;
                $res['conteos'][$nombre] = ($res['conteos'][$nombre] ?? 0) + $n;

                switch ($nombre) {
                    case 'risk_teaser_view':   $res['teaser']['vistas']  += $n; break;
                    case 'risk_teaser_cta':    $res['teaser']['clicks']  += $n;
                                               $porCanal[$elem] = ($porCanal[$elem] ?? 0) + $n; break;
                    case 'risk_locked_view':   $res['locked']['vistas']  += $n; break;
                    case 'risk_unlock_click':  $res['locked']['clicks']  += $n; break;
                    case 'risk_paywall_view':  $res['paywall']['vistas'] += $n; break;
                    case 'risk_paywall_cta':   $res['paywall']['clicks'] += $n;
                                               $porOpcion[$elem] = ($porOpcion[$elem] ?? 0) + $n; break;
                    case 'risk_pro_upsell_view': $res['upsell']['vistas'] += $n; break;
                    case 'risk_pro_upsell_cta':  $res['upsell']['clicks'] += $n; break;
                    case 'checkout_started':   $res['checkout']['iniciados'] += $n;
                                               $porOrigen[$elem] = ($porOrigen[$elem] ?? 0) + $n; break;
                    case 'checkout_completed': $res['checkout']['completados'] += $n; break;
                }
            }

            // Variante del upsell: consulta acotada a ese evento, leyendo el JSON
            // fila a fila. Son pocas filas y así no se agrupa por texto.
            $qv = $db->table('tracking_events')
                ->select('metadata')
                ->where('event_name', 'risk_pro_upsell_cta')
                ->limit(5000);
            if ($dateFrom) {
                $qv->where('created_at >=', $dateFrom)->where('created_at <=', $dateTo);
            }
            foreach ($qv->get()->getResultArray() as $fv) {
                $meta = json_decode((string) ($fv['metadata'] ?? ''), true);
                $v = is_array($meta) ? ($meta['variant'] ?? '(sin variante)') : '(sin variante)';
                $porVariante[$v] = ($porVariante[$v] ?? 0) + 1;
            }

            $ratio = static fn (int $a, int $b): float => $b > 0 ? round(($a / $b) * 100, 1) : 0.0;

            $res['teaser']['ctr']      = $ratio($res['teaser']['clicks'], $res['teaser']['vistas']);
            $res['locked']['ctr']      = $ratio($res['locked']['clicks'], $res['locked']['vistas']);
            $res['paywall']['ctr']     = $ratio($res['paywall']['clicks'], $res['paywall']['vistas']);
            $res['upsell']['ctr']      = $ratio($res['upsell']['clicks'], $res['upsell']['vistas']);
            $res['checkout']['ratio']  = $ratio($res['checkout']['completados'], $res['checkout']['iniciados']);

            arsort($porCanal); arsort($porOpcion); arsort($porVariante); arsort($porOrigen);
            arsort($res['conteos']);

            $res['teaser']['por_canal']      = $porCanal;
            $res['paywall']['por_opcion']    = $porOpcion;
            $res['upsell']['por_variante']   = $porVariante;
            $res['checkout']['por_origen']   = $porOrigen;

            return $res;
        } catch (\Throwable $e) {
            log_message('error', '[RiskAnalytics] tracking_events ilegible: ' . $e->getMessage());
            return $vacio;
        }
    }

    private function getEmailTemplates()
    {
        return [
            [
                'id' => 'hot_lead_limit',
                'name' => '🚨 Oferta Solvencia Pro (Límite 3/3 alcanzado)',
                'subject' => '¿Necesitas auditar otra empresa este mes, {NOMBRE}?',
                'body' => "Hola {NOMBRE},\n\nHemos visto que has consumido tus 3 consultas gratuitas de Perfil de Riesgo y Solvencia este mes en APIEmpresas.\n\nSi necesitas seguir auditando la solvencia financiera, balances y riesgo de impago de tus clientes o proveedores, con el plan Solvencia Pro consultas el dictamen de cualquier CIF de España y mantienes hasta 25 empresas bajo vigilancia del BORME, con aviso por correo en cuanto una se mueve.\n\nPuedes acceder directamente al buscador y consultar cualquier CIF aquí:\n{SITE_URL}/perfil-de-riesgo\n\nSi tienes cualquier consulta o necesitas revisar un caso concreto, responde a este correo y te ayudamos encantados.\n\nUn saludo,\nEl equipo de APIEmpresas"
            ],
            [
                'id' => 'inactive_activation',
                'name' => '⚡ Recordatorio de Activación (0 consultas)',
                'subject' => 'Tienes 3 consultas gratuitas de Perfil de Riesgo y Solvencia este mes',
                'body' => "Hola {NOMBRE},\n\nTe recordamos que dispones de 3 consultas gratuitas cada mes para revisar el Perfil de Riesgo y Solvencia de cualquier sociedad en España.\n\nCon esta herramienta podrás verificar al instante:\n• Scoring de solvencia y riesgo de impago\n• Balances oficiales y cuentas de pérdidas y ganancias\n• Nivel de endeudamiento y capacidad crediticia\n\nPrueba a consultar el CIF de un cliente o proveedor en el buscador:\n{SITE_URL}/perfil-de-riesgo\n\nUn saludo,\nEl equipo de APIEmpresas"
            ],
            [
                'id' => 'product_update',
                'name' => '✨ Novedades en los Informes de Solvencia',
                'subject' => 'Novedades en los informes de Riesgo y Solvencia Financiera',
                'body' => "Hola {NOMBRE},\n\nHemos actualizado nuestra herramienta de Perfil de Riesgo con los últimos balances oficiales depositados en el Registro Mercantil.\n\nAhora dispones de histórico ampliado de ventas, comparativa sectorial y evolución de liquidez para tomar decisiones comerciales con mayor seguridad.\n\nPuedes probarlo directamente aquí:\n{SITE_URL}/perfil-de-riesgo\n\nAtentamente,\nEl equipo de APIEmpresas"
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
