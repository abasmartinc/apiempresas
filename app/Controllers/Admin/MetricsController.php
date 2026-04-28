<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;
use App\Models\TrackingEventModel;
use App\Models\ApiRequestsModel;
use App\Models\UserSubscriptionsModel;
use App\Services\OpenAiService;

class MetricsController extends BaseController
{
    /**
     * Dashboard de métricas de negocio y conversión (Admin)
     * Transformado en Lista de Tareas Diaria (Conversión OS)
     */
    public function eventTracking()
    {
        $db = \Config\Database::connect();
        
        // --- 1. KPIs GLOBALES (Look & Feel Email Logs) ---
        $kpis = [
            'total_registros' => $db->table('users')->countAllResults(),
            'total_requests_today' => $db->table('api_requests')->where('created_at >=', date('Y-m-d 00:00:00'))->countAllResults(),
            'paid_users' => $db->table('user_subscriptions')->where('status', 'active')->where('plan_id >', 1)->countAllResults(),
        ];
        $kpis['conversion_rate'] = $kpis['total_registros'] > 0 ? round(($kpis['paid_users'] / $kpis['total_registros']) * 100, 1) : 0;

        // --- 2. PROCESAMIENTO DE USUARIOS CON PRIORIZACIÓN ---
        $rawUsers = $db->query("
            SELECT 
                u.id, u.email, u.name, u.created_at, 
                s.plan_id, p.name as plan_name,
                (SELECT COUNT(*) FROM api_requests WHERE user_id = u.id) as total_requests,
                (SELECT MAX(created_at) FROM api_requests WHERE user_id = u.id) as last_request,
                (SELECT COUNT(*) FROM api_requests WHERE user_id = u.id AND created_at >= ?) as req_24h,
                (SELECT COUNT(*) FROM api_requests WHERE user_id = u.id AND created_at >= ? AND created_at < ?) as req_3d,
                (SELECT COUNT(*) FROM api_requests WHERE user_id = u.id AND created_at >= ? AND created_at < ?) as req_prev_3d,
                (SELECT COUNT(*) FROM api_requests WHERE user_id = u.id AND created_at >= ?) as req_7d,
                (SELECT COUNT(*) FROM tracking_events WHERE user_id = u.id AND event_name = 'pricing_view') as pricing_views,
                (SELECT MAX(created_at) FROM tracking_events WHERE user_id = u.id AND event_name = 'pricing_view') as last_pricing_view,
                (SELECT COUNT(*) FROM tracking_events WHERE user_id = u.id AND event_name = 'api_key_copied') as copied_key,
                (SELECT MAX(created_at) FROM tracking_events WHERE user_id = u.id AND event_name = 'api_key_copied') as last_key_copied,
                (SELECT COUNT(*) FROM tracking_events WHERE user_id = u.id AND event_name = 'cta_upgrade_click') as cta_clicks
            FROM users u
            LEFT JOIN user_subscriptions s ON (s.user_id = u.id AND s.status = 'active')
            LEFT JOIN api_plans p ON s.plan_id = p.id
            WHERE (s.plan_id IS NULL OR s.plan_id = 1)
            ORDER BY req_24h DESC, total_requests DESC
            LIMIT 250
        ", [
            date('Y-m-d H:i:s', strtotime('-24 hours')),
            date('Y-m-d H:i:s', strtotime('-3 days')), date('Y-m-d H:i:s'),
            date('Y-m-d H:i:s', strtotime('-6 days')), date('Y-m-d H:i:s', strtotime('-3 days')),
            date('Y-m-d H:i:s', strtotime('-7 days'))
        ])->getResultArray();

        $processedUsers = [];
        $summary = ['ready' => 0, 'active_no_plan' => 0, 'losing' => 0];

        foreach ($rawUsers as $u) {
            $u['spike'] = ($u['req_3d'] >= 5 && $u['req_3d'] >= (2 * $u['req_prev_3d']));
            
            // Triple Scoring System
            $u['urgency_score'] = $this->calculateUrgencyScore($u);
            $u['intent_score'] = $this->calculateIntentScore($u);
            $u['value_score'] = $this->calculateValueScore($u);
            
            // FINAL_SCORE = (urgency*0.5) + (intent*0.3) + (value*0.2)
            $u['final_score'] = ($u['urgency_score'] * 0.5) + ($u['intent_score'] * 0.3) + ($u['value_score'] * 0.2);

            // Overrides de Prioridad Absoluta
            if ((int)($u['req_24h'] ?? 0) >= 5) $u['final_score'] = max($u['final_score'], 95);
            if ((int)($u['total_requests'] ?? 0) >= 10 && ($u['pricing_views'] ?? 0) == 0) $u['final_score'] += 10;
            
            $u['final_score'] = min(100, $u['final_score']);

            // Identificar Case Type para el Modal de Mensajería
            $u['case_type'] = $this->determineCaseType($u);

            // Generar el "WHY" (Motivo) y Acción
            $u['why'] = $this->generateWhy($u);
            $u['recommended_action'] = $this->calculateAction($u);
            $u['status_label'] = $this->getStatusLabel($u['final_score']);
            
            $isPaid = ($u['plan_id'] && $u['plan_id'] > 1);
            if (!$isPaid) {
                if ($u['final_score'] >= 80) $summary['ready']++;
                if ((int)($u['total_requests'] ?? 0) >= 5) $summary['active_no_plan']++;
                if ((int)($u['total_requests'] ?? 0) >= 10 && strtotime($u['last_request'] ?? '') < strtotime('-7 days')) $summary['losing']++;
            }

            $processedUsers[] = $u;
        }

        // --- 3. ORDENAMIENTO FINAL (CUATRO NIVELES DE PRIORIDAD) ---
        $groupA = []; // Uso Fuerte (>= 5)
        $groupB = []; // Uso Medio (3-4)
        $groupC = []; // Testing (1-2)
        $groupD = []; // No Activos

        foreach ($processedUsers as $u) {
            $req = (int)($u['req_24h'] ?? 0);
            if ($req >= 5) $groupA[] = $u;
            elseif ($req >= 3) $groupB[] = $u;
            elseif ($req >= 1) $groupC[] = $u;
            else $groupD[] = $u;
        }

        // Función de ordenamiento común para grupos activos
        $activeSorter = function($a, $b) {
            $reqA = (int)($a['req_24h'] ?? 0);
            $reqB = (int)($b['req_24h'] ?? 0);
            if ($reqB !== $reqA) return $reqB - $reqA;
            if ($b['final_score'] != $a['final_score']) return $b['final_score'] - $a['final_score'];
            return (int)($b['total_requests'] ?? 0) - (int)($a['total_requests'] ?? 0);
        };

        usort($groupA, $activeSorter);
        usort($groupB, $activeSorter);
        usort($groupC, $activeSorter);

        // Ordenar Grupo D: final_score DESC -> total_requests DESC -> last_request DESC
        usort($groupD, function($a, $b) {
            if ($b['final_score'] != $a['final_score']) return $b['final_score'] - $a['final_score'];
            return (int)($b['total_requests'] ?? 0) - (int)($a['total_requests'] ?? 0);
        });

        // Merge Final: A -> B -> C -> D
        $finalProcessed = array_merge($groupA, $groupB, $groupC, $groupD);

        // --- 4. SECCIONES OPERATIVAS ---
        $whatToDoNow = array_slice(array_filter($finalProcessed, function($u) {
            return $u['final_score'] >= 60 && (!$u['plan_id'] || $u['plan_id'] == 1);
        }), 0, 10);

        $problematicUsers = array_slice(array_filter($finalProcessed, function($u) {
            return ((int)($u['total_requests'] ?? 0) == 0 && (int)($u['copied_key'] ?? 0) > 0) || ((int)($u['total_requests'] ?? 0) == 0 && strtotime($u['created_at']) < strtotime('-2 days'));
        }), 0, 5);

        return view('admin/event_tracking', [
            'title'           => 'Leads & Conversión - Admin',
            'kpis'            => $kpis,
            'summary'         => $summary,
            'whatToDoNow'     => $whatToDoNow,
            'problematicUsers'=> $problematicUsers,
            'userList'        => array_slice($finalProcessed, 0, 50),
            'activeUsers'     => (new TrackingEventModel())->getActiveUsersCount()
        ]);
    }

    public function sendMessage()
    {
        $json = $this->request->getJSON();
        if ($json) {
            $userId = $json->user_id ?? null;
            $message = $json->message ?? null;
        } else {
            $userId = $this->request->getPost('user_id');
            $message = $this->request->getPost('message');
        }
        
        $db = \Config\Database::connect();
        $user = $db->table('users')->where('id', $userId)->get()->getRow();
        
        if (!$user) return $this->response->setJSON(['status' => 'error', 'message' => 'Usuario no encontrado']);

        // Enviar Email
        $email = \Config\Services::email();
        $email->setTo($user->email);
        $email->setFrom('info@apiempresas.com', 'API Empresas Team');
        $email->setSubject('Novedades sobre tu acceso a la API');
        $email->setMessage(nl2br($message));

        $status = 'success';
        $errorMsg = null;
        
        if (!$email->send()) {
            $status = 'error';
            $errorMsg = $email->printDebugger(['headers']);
        }

        // Logging
        $db->table('email_logs')->insert([
            'user_id' => $userId,
            'subject' => 'Contacto Manual: Dashboard Conversión',
            'message' => $message,
            'status'  => $status,
            'error_message' => $errorMsg,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        return $this->response->setJSON(['status' => $status, 'message' => ($status == 'success' ? 'Mensaje enviado correctamente' : 'Error al enviar')]);
    }

    private function determineCaseType($u)
    {
        $reqs = (int)($u['total_requests'] ?? 0);
        $req24h = (int)($u['req_24h'] ?? 0);
        $pricing = (int)($u['pricing_views'] ?? 0);
        $days = $u['last_request'] ? floor((time() - strtotime($u['last_request'])) / 86400) : 999;

        if ($req24h >= 5) return 'active_high';
        if ($reqs >= 10 && $pricing == 0) return 'pricing_missing';
        if ($days >= 7 && $reqs > 0) return 'reactivation';
        if ($reqs == 0) return 'onboarding';

        return 'general_followup';
    }

    private function calculateUrgencyScore($u)
    {
        $score = 0;
        $req24h = $u['req_24h'] ?? 0;
        $req3d = $u['req_3d'] ?? 0;
        $req7d = $u['req_7d'] ?? 0;
        $days = $u['last_request'] ? (time() - strtotime($u['last_request'])) / 86400 : 999;

        if ($req24h >= 5) $score += 50;
        elseif ($req24h >= 2) $score += 40;
        
        if ($req3d >= 5) $score += 25;
        if ($req7d >= 5) $score += 15;
        if ($u['spike'] ?? false) $score += 20;

        if ($days > 14) $score -= 50;
        elseif ($days > 7) $score -= 30;

        return max(0, min(100, $score));
    }

    private function calculateIntentScore($u)
    {
        $score = 0;
        $pricing = $u['pricing_views'] ?? 0;
        $reqs = $u['total_requests'] ?? 0;

        if ($pricing >= 1) $score += 40;
        if ($pricing > 1) $score += 20;
        if ((int)($u['cta_clicks'] ?? 0) >= 1) $score += 25;
        if ((int)($u['copied_key'] ?? 0) > 0) $score += 15;

        if ($reqs >= 10) $score += 20;
        elseif ($reqs >= 5) $score += 10;
        
        if ($reqs == 0) $score -= 20;

        return max(0, min(100, $score));
    }

    private function calculateValueScore($u)
    {
        $score = 0;
        $reqs = $u['total_requests'] ?? 0;
        $days = $u['last_request'] ? (time() - strtotime($u['last_request'])) / 86400 : 999;

        if ($reqs >= 100) $score += 50;
        elseif ($reqs >= 50) $score += 40;
        elseif ($reqs >= 20) $score += 30;
        elseif ($reqs >= 10) $score += 20;
        elseif ($reqs >= 5) $score += 10;

        if ($days <= 7) $score += 10;

        return max(0, min(100, $score));
    }

    private function generateWhy($u)
    {
        $reqs = $u['total_requests'] ?? 0;
        $req24h = $u['req_24h'] ?? 0;
        $pricing = $u['pricing_views'] ?? 0;
        $days = $u['last_request'] ? floor((time() - strtotime($u['last_request'])) / 86400) : 999;

        if ($u['copied_key'] && $reqs == 0) return "Copió API key pero no hizo requests";
        if ($reqs >= 20 && $days > 7) return "$reqs requests históricos, inactivo $days días";
        if ($reqs >= 10 && $pricing == 0) return "$reqs requests (0 hoy) + nunca vio pricing";
        
        if ($req24h >= 5) return "$req24h requests en últimas 24h";

        if ($u['spike'] ?? false) {
            $prev = $u['req_prev_3d'] ?: 1;
            $growth = round((($u['req_3d'] - $u['req_prev_3d']) / $prev) * 100);
            return "$req24h requests (↑ +$growth% vs últimos 3 días)";
        }

        return "$reqs requests totales detectados";
    }

    private function calculateAction($u)
    {
        $score = $u['final_score'] ?? 0;
        $reqs = $u['total_requests'] ?? 0;
        $days = $u['last_request'] ? floor((time() - strtotime($u['last_request'])) / 86400) : 999;

        if ($u['copied_key'] && $reqs == 0) return "Bloqueado onboarding";
        if ($reqs >= 20 && $days > 7) return "Reactivar";
        if ($reqs == 0) return "Onboarding";

        if ($score >= 80) return "Contactar hoy";
        if ($score >= 60) {
            return (($u['pricing_views'] ?? 0) > 0) ? "Enviar oferta Pro" : "Mostrar pricing";
        }
        if ($score >= 30) return "Seguir / observar";
        
        return "Ignorar";
    }

    private function getStatusLabel($score)
    {
        if ($score >= 80) return 'Crítico';
        if ($score >= 60) return 'Alto';
        if ($score >= 30) return 'Medio';
        return 'Bajo';
    }

    private function calculateTrend($current, $previous)
    {
        if ($previous == 0) return $current > 0 ? 'up' : 'stable';
        $change = (($current - $previous) / $previous) * 100;
        if ($change > 50) return 'up';
        if ($change < -50) return 'down';
        return 'stable';
    }
}
