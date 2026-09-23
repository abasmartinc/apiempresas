<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use Config\Database;

class ApiAnalytics extends BaseController
{
    /**
     * Ventana (en días) dentro de la cual se considera que un correo con el
     * MISMO asunto para el MISMO usuario es un duplicado y hay que confirmarlo.
     */
    const DUPLICATE_WINDOW_DAYS = 30;

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
        if (!in_array($userStatusFilter, ['all', 'limit_reached', 'near_limit', 'active_free', 'inactive', 'errors', 'paid'], true)) {
            $userStatusFilter = 'all';
        }
        $search = trim($this->request->getGet('q') ?? '');
        $sort = $this->request->getGet('sort') ?? 'usage_desc';

        // Filtro de contacto por correo: all | never | contacted | recent (últimos 7 días)
        $contactFilter = $this->request->getGet('contact') ?? 'all';
        if (!in_array($contactFilter, ['all', 'never', 'contacted', 'recent'], true)) {
            $contactFilter = 'all';
        }

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
        // Usuarios con signup_intent = 'api', no administradores, y excluyendo el usuario monitor interno (ID: 376)
        $allCohortUsers = $db->table('users')
            ->select('id, name, email, company, created_at, last_login_at, is_active, unsuscribe')
            ->where('signup_intent', 'api')
            ->where('is_admin', 0)
            ->where('id !=', 376)
            ->orderBy('created_at', 'DESC')
            ->get()->getResultArray();
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
        $activePaidSubs = [];
        if (!empty($cohortUserIds)) {
            $activePaidSubs = $db->table('user_subscriptions')
                ->select('user_subscriptions.user_id, user_subscriptions.plan_id, user_subscriptions.current_period_end, api_plans.name as plan_name, api_plans.slug as plan_slug, api_plans.price_monthly, api_plans.monthly_quota')
                ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id')
                ->where('user_subscriptions.status', 'active')
                ->where('api_plans.price_monthly >', 0)
                ->where('api_plans.product_type', 'api')
                ->where('user_subscriptions.user_id !=', 376)
                ->whereIn('user_subscriptions.user_id', $cohortUserIds)
                ->get()->getResultArray();
        }

        $paidUserMap = [];
        $mrrTotal = 0.0;
        foreach ($activePaidSubs as $sub) {
            $paidUserMap[(int)$sub['user_id']] = $sub;
            $mrrTotal += (float)($sub['price_monthly'] ?? 0.0);
        }
        $paidSubscribers = count($paidUserMap);

        // Planes de pago de la API para el filtro "Plan" del listado (slug => nombre).
        // Se leen de api_plans para que un plan nuevo aparezca sin tocar código.
        $apiPaidPlans = [];
        try {
            $planRows = $db->table('api_plans')
                ->select('slug, name, price_monthly')
                ->where('product_type', 'api')
                ->where('price_monthly >', 0)
                ->orderBy('price_monthly', 'ASC')
                ->get()->getResultArray();
            foreach ($planRows as $p) {
                if (!empty($p['slug']) && !isset($apiPaidPlans[$p['slug']])) {
                    $apiPaidPlans[$p['slug']] = $p['name'] ?: $p['slug'];
                }
            }
        } catch (\Throwable $e) {
            log_message('error', '[ApiAnalytics] No se pudieron leer los planes: ' . $e->getMessage());
        }
        // Por si hay suscriptores de un plan que la consulta anterior no devolvió
        foreach ($paidUserMap as $sub) {
            if (!empty($sub['plan_slug']) && !isset($apiPaidPlans[$sub['plan_slug']])) {
                $apiPaidPlans[$sub['plan_slug']] = $sub['plan_name'] ?: $sub['plan_slug'];
            }
        }

        // Filtro por plan: all | free | <slug de un plan de pago>
        $planFilter = (string)($this->request->getGet('plan') ?? 'all');
        if ($planFilter !== 'all' && $planFilter !== 'free' && !isset($apiPaidPlans[$planFilter])) {
            $planFilter = 'all';
        }
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
        // 4.5 HISTORIAL DE CORREOS ENVIADOS (email_logs)
        // -------------------------------------------------------------
        // Se lee SIEMPRE sobre todo el histórico, no sobre el periodo del panel:
        // la pregunta que responde esta columna es "¿ya le escribí?", y la respuesta
        // no debe cambiar porque el admin haya elegido "este mes" en el filtro.
        $emailStatsByUser = $this->getEmailStatsByUser($db, $cohortUserIds);

        // -------------------------------------------------------------
        // 5. CLASIFICACIÓN DE CADA USUARIO Y EMBUDO DE ADOPCIÓN (FUNNEL)
        // -------------------------------------------------------------
        $countLimitReached = 0;
        $countNearLimit = 0;
        $countActiveFree = 0;
        $countInactive = 0;
        $countErrors = 0;
        $countPaid = 0;
        $countContacted = 0;
        $countNeverContacted = 0;

        // Pasos del embudo de adopción
        $funnel_step1_registered = $totalApiUsers;
        $funnel_step2_activated = 0;   // Primera llamada real realizada (>=1 llamada histórica)
        $funnel_step3_engaged = 0;     // Hizo 5+ llamadas
        $funnel_step4_high_usage = 0;  // Alcanzó o rozó el límite (>=80 peticiones)
        $funnel_step5_paid = $paidSubscribers;

        $neverCalledCount = 0;
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

            // Embudo: activación real se mide por hacer al menos 1 petición a la API
            if ($historyReqs >= 1) {
                $funnel_step2_activated++;
            } else {
                $neverCalledCount++;
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
            } elseif ($historyReqs > 0) {
                $countInactive++;
                $status = 'inactive';
                $statusLabel = 'Sin uso este mes (' . $historyReqs . ' hist.)';
                $statusBadge = 'neutral';
            } else {
                $countInactive++;
                $status = 'inactive';
                $statusLabel = 'Sin llamadas (0 hist.)';
                $statusBadge = 'neutral';
            }

            // Endpoints ordenados de este usuario
            $userTopEndpoints = array_slice($stats['endpoints'], 0, 3, true);

            // Historial de contacto por correo
            $mail = $emailStatsByUser[$uId] ?? [
                'sent_count'     => 0,
                'failed_count'   => 0,
                'last_sent_at'   => null,
                'last_subject'   => null,
                'last_opened_at' => null,
                'opened_count'   => 0,
                'subjects'       => []
            ];
            $daysSinceEmail = $mail['last_sent_at']
                ? (int)floor((time() - strtotime($mail['last_sent_at'])) / 86400)
                : null;

            if ($mail['sent_count'] > 0) {
                $countContacted++;
            } else {
                $countNeverContacted++;
            }

            $classifiedUsers[] = [
                'user'             => $u,
                'is_paid'          => $isPaid,
                'plan_name'        => $isPaid ? ($subData['plan_name'] ?? 'Pro') : 'Free (100)',
                'plan_slug'        => $isPaid ? (string)($subData['plan_slug'] ?? '') : 'free',
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
                'status_badge'     => $statusBadge,
                // Contacto por correo
                'emails_sent'      => $mail['sent_count'],
                'emails_failed'    => $mail['failed_count'],
                'emails_opened'    => $mail['opened_count'],
                'last_email_at'    => $mail['last_sent_at'],
                'last_email_subject' => $mail['last_subject'],
                'last_email_opened_at' => $mail['last_opened_at'],
                'days_since_email' => $daysSinceEmail,
                'is_unsubscribed'  => (int)($u['unsuscribe'] ?? 0) === 1
            ];
        }

        // -------------------------------------------------------------
        // 6. FILTRADO Y BÚSQUEDA DE LA TABLA
        // -------------------------------------------------------------
        // Filtro de contacto por correo (independiente del estado de consumo:
        // la combinación útil es justamente "límite agotado" + "sin contactar")
        $matchesContact = function(array $row, string $filter): bool {
            if ($filter === 'never')     return $row['emails_sent'] === 0;
            if ($filter === 'contacted') return $row['emails_sent'] > 0;
            if ($filter === 'recent') {
                return $row['emails_sent'] > 0
                    && $row['days_since_email'] !== null
                    && $row['days_since_email'] <= 7;
            }
            return true; // 'all'
        };

        // Filtro por estado de consumo
        $matchesStatus = function(array $row, string $filter): bool {
            return $filter === 'all' || $row['status'] === $filter;
        };

        // Filtro de búsqueda
        $needle = mb_strtolower($search);
        $matchesSearch = function(array $row) use ($search, $needle): bool {
            if ($search === '') return true;
            $u = $row['user'];
            return strpos(mb_strtolower($u['name'] ?? ''), $needle) !== false
                || strpos(mb_strtolower($u['email'] ?? ''), $needle) !== false
                || strpos(mb_strtolower($u['company'] ?? ''), $needle) !== false
                || strpos(mb_strtolower($row['last_endpoint'] ?? ''), $needle) !== false;
        };

        // Quien ha pedido no recibir correos no aparece en el listado ni en los
        // chips: no se le puede escribir, así que solo estorba. Los KPIs y el
        // embudo siguen usando $classifiedUsers completo.
        $listableUsers = array_filter($classifiedUsers, fn($row) => !$row['is_unsubscribed']);

        // Filtro por plan: Free o un plan de pago concreto (Pro, Business...)
        $matchesPlan = function(array $row, string $filter): bool {
            return $filter === 'all' || $row['plan_slug'] === $filter;
        };

        $filteredUsers = array_filter($listableUsers, function($row) use ($userStatusFilter, $contactFilter, $planFilter, $matchesContact, $matchesStatus, $matchesPlan, $matchesSearch) {
            return $matchesContact($row, $contactFilter)
                && $matchesStatus($row, $userStatusFilter)
                && $matchesPlan($row, $planFilter)
                && $matchesSearch($row);
        });

        // -------------------------------------------------------------
        // 6.0 CONTADORES DE LOS CHIPS (FACETADOS)
        // -------------------------------------------------------------
        // Cada fila de chips cuenta aplicando los filtros activos de las OTRAS
        // filas (y la búsqueda), para que el número del chip coincida con lo que
        // aparece en el listado al pulsarlo. Los contadores globales de
        // $counts se mantienen para los KPIs de la cabecera.
        $pillCounts = [
            'status'  => ['all' => 0, 'limit_reached' => 0, 'near_limit' => 0, 'active_free' => 0,
                          'inactive' => 0, 'errors' => 0, 'paid' => 0],
            'contact' => ['all' => 0, 'never' => 0, 'contacted' => 0, 'recent' => 0],
            'plan'    => array_merge(['all' => 0, 'free' => 0], array_fill_keys(array_keys($apiPaidPlans), 0)),
        ];
        foreach ($listableUsers as $row) {
            if (!$matchesSearch($row)) continue;

            $okContact = $matchesContact($row, $contactFilter);
            $okStatus  = $matchesStatus($row, $userStatusFilter);
            $okPlan    = $matchesPlan($row, $planFilter);

            if ($okContact && $okPlan) {
                $pillCounts['status']['all']++;
                if (isset($pillCounts['status'][$row['status']])) {
                    $pillCounts['status'][$row['status']]++;
                }
            }

            if ($okStatus && $okPlan) {
                foreach (array_keys($pillCounts['contact']) as $cf) {
                    if ($matchesContact($row, $cf)) $pillCounts['contact'][$cf]++;
                }
            }

            if ($okContact && $okStatus) {
                $pillCounts['plan']['all']++;
                if (isset($pillCounts['plan'][$row['plan_slug']])) {
                    $pillCounts['plan'][$row['plan_slug']]++;
                }
            }
        }

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

                case 'email_asc':
                    // Los que llevan más tiempo sin recibir nada, primero;
                    // los que nunca han recibido nada van por delante de todos.
                    $aNever = $a['emails_sent'] === 0;
                    $bNever = $b['emails_sent'] === 0;
                    if ($aNever !== $bNever) return $aNever ? -1 : 1;
                    if ($aNever && $bNever) {
                        return $b['month_requests'] <=> $a['month_requests'];
                    }
                    return strcmp($a['last_email_at'] ?? '', $b['last_email_at'] ?? '');

                case 'email_desc':
                    // Contactados más recientemente primero (para revisar lo que acabas de mandar)
                    return strcmp($b['last_email_at'] ?? '', $a['last_email_at'] ?? '');

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
            'contact_filter'        => $contactFilter,
            'plan_filter'           => $planFilter,
            'api_paid_plans'        => $apiPaidPlans,
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
                'never_called'  => $neverCalledCount,
                'errors'        => $countErrors,
                'paid'          => $countPaid,
                'contacted'     => $countContacted,
                'never_contacted' => $countNeverContacted,
            ],
            // Contadores de los chips, cruzados con el filtro de la otra fila
            'pill_counts'           => $pillCounts,
            'users'                 => $filteredUsers,
            'top_endpoints'         => array_slice($topEndpointsMap, 0, 5, true),
            'email_templates'       => $this->getEmailTemplates(),
            // Mapa id => historial de contacto, para que el modal pueda avisar
            // ANTES de enviar en vez de después
            'contact_info'          => $this->buildContactInfo($classifiedUsers, $emailStatsByUser),
            'duplicate_window_days' => self::DUPLICATE_WINDOW_DAYS
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

        if ($userId === 376) {
            return redirect()->back()->with('error', 'No se pueden enviar correos al usuario monitor interno (ID: 376).');
        }

        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);

        if (!$user) {
            return redirect()->back()->with('error', 'Usuario no encontrado.');
        }

        // Baja voluntaria: parada en seco, no hay confirmación que la salte.
        // El envío masivo ya lo respetaba; el individual no, y era el mismo dato.
        if ((int)($user->unsuscribe ?? 0) === 1) {
            return redirect()->back()->with('error',
                esc($user->email) . ' se dio de baja de las comunicaciones. No se ha enviado nada.');
        }

        // Duplicado: mismo asunto, mismo usuario, dentro de la ventana.
        $allowDuplicate = (int)$this->request->getPost('allow_duplicate') === 1;
        $previousSend = $this->findRecentDuplicate((int)$user->id, $subject);
        if ($previousSend !== null && !$allowDuplicate) {
            return redirect()->back()->withInput()->with('error',
                'No se ha enviado: ' . esc($user->email) . ' ya recibió un correo con este mismo asunto el '
                . date('d/m/Y H:i', strtotime($previousSend))
                . '. Si quieres mandarlo otra vez, marca «Enviar de todas formas» en el formulario.');
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
        $userIds = array_values(array_diff(array_filter(array_map('intval', $userIds)), [376]));

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

        $allowDuplicate = (int)$this->request->getPost('allow_duplicate') === 1;

        $sentCount = 0;
        $errorCount = 0;
        $skippedCount = 0;
        $duplicateCount = 0;
        $duplicateEmails = [];

        foreach ($users as $user) {
            if ((int)($user->unsuscribe ?? 0) === 1) {
                $skippedCount++;
                continue;
            }

            // A diferencia del envío individual, aquí un duplicado no tumba la
            // campaña entera: se salta ese destinatario y se informa al final.
            if (!$allowDuplicate && $this->findRecentDuplicate((int)$user->id, $subject) !== null) {
                $duplicateCount++;
                if (count($duplicateEmails) < 10) {
                    $duplicateEmails[] = $user->email;
                }
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
        if ($duplicateCount > 0) {
            $msg .= " {$duplicateCount} omitido(s) por haber recibido ya este mismo asunto"
                  . " en los últimos " . self::DUPLICATE_WINDOW_DAYS . " días";
            if (!empty($duplicateEmails)) {
                $msg .= ' (' . esc(implode(', ', $duplicateEmails))
                      . ($duplicateCount > count($duplicateEmails) ? ', …' : '') . ')';
            }
            $msg .= '.';
        }
        if ($errorCount > 0) {
            $msg .= " {$errorCount} fallaron.";
            return redirect()->back()->with('message', $msg)->with('error', "Hubo {$errorCount} envíos con error. Revisa el log de emails.");
        }

        return redirect()->back()->with('message', $msg);
    }

    /**
     * Historial de correos por usuario, leído de `email_logs`.
     *
     * Devuelve un mapa user_id => [sent_count, failed_count, opened_count,
     * last_sent_at, last_subject, last_opened_at, subjects, history].
     * Ver App\Libraries\EmailContactHistory.
     *
     * `subjects` guarda, por asunto, la fecha del último envío correcto: es lo que
     * permite avisar de un duplicado exacto sin volver a consultar la base de datos.
     */
    private function getEmailStatsByUser($db, array $userIds): array
    {
        // Reglas de qué cuenta como contacto (fallidos, bienvenidas, aviso al
        // admin) compartidas con el panel de Solvencia.
        return (new \App\Libraries\EmailContactHistory())->forUsers($db, $userIds);
    }

    /**
     * Resumen de contacto por usuario para el JavaScript de los modales.
     */
    private function buildContactInfo(array $classifiedUsers, array $emailStatsByUser): array
    {
        $windowStart = strtotime('-' . self::DUPLICATE_WINDOW_DAYS . ' days');

        $info = [];
        foreach ($classifiedUsers as $row) {
            $uid = (int)$row['user']['id'];

            // Asuntos ya enviados DENTRO de la ventana: es la misma condición que
            // aplica el servidor, para que el aviso no prometa algo distinto.
            $recentSubjects = [];
            foreach (($emailStatsByUser[$uid]['subjects'] ?? []) as $subject => $sentAt) {
                if (strtotime($sentAt) >= $windowStart) {
                    $recentSubjects[$subject] = date('d/m/Y', strtotime($sentAt));
                }
            }

            $info[$uid] = [
                'recent_subjects' => $recentSubjects,
                'sent'         => $row['emails_sent'],
                'opened'       => $row['emails_opened'],
                'last_at'      => $row['last_email_at']
                    ? date('d/m/Y H:i', strtotime($row['last_email_at']))
                    : null,
                'last_subject' => $row['last_email_subject'],
                'days_since'   => $row['days_since_email'],
                'unsubscribed' => $row['is_unsubscribed'],
                'name'         => $row['user']['name'] ?: ('Desarrollador #' . $uid)
            ];
        }
        return $info;
    }

    /**
     * ¿Ya recibió este usuario un correo con este mismo asunto hace poco?
     *
     * Devuelve la fecha del envío previo, o null si no hay duplicado. La
     * comprobación vive aquí, en el servidor, y no solo en el aviso del modal:
     * un límite comprobado únicamente en la interfaz no es un límite.
     */
    private function findRecentDuplicate(int $userId, string $subject): ?string
    {
        $subject = trim($subject);
        if ($subject === '') {
            return null;
        }

        $since = date('Y-m-d H:i:s', strtotime('-' . self::DUPLICATE_WINDOW_DAYS . ' days'));

        $row = Database::connect()->table('email_logs')
            ->select('created_at')
            ->where('user_id', $userId)
            ->where('status', 'success')
            ->where('subject', $subject)
            ->where('created_at >=', $since)
            ->orderBy('created_at', 'DESC')
            ->get(1)->getRowArray();

        return $row['created_at'] ?? null;
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
