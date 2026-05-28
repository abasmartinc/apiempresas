<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\UserModel;
use App\Models\EmailAutomationModel;
use App\Models\ApiUsageDailyModel;
use App\Services\EmailService;

class EmailAutomationCommand extends BaseCommand
{
    protected $group       = 'Automation';
    protected $name        = 'email:automation';
    protected $description = 'Procesa y envía emails automáticos basados en comportamiento.';

    protected $userModel;
    protected $automationModel;
    protected $usageModel;
    protected $emailService;

    public function run(array $params)
    {
        $this->userModel       = new UserModel();
        $this->automationModel = new EmailAutomationModel();
        $this->usageModel      = new ApiUsageDailyModel();
        $this->emailService    = new EmailService();

        CLI::write('🚀 Iniciando proceso de automatización de emails...', 'cyan');

        // Solo procesamos usuarios FREE activos
        $db = \Config\Database::connect();
        $usersToCheck = $db->table('users')
            ->select('users.*, user_subscriptions.plan_id')
            ->join('user_subscriptions', 'user_subscriptions.user_id = users.id')
            ->where('user_subscriptions.status', 'active')
            ->where('user_subscriptions.plan_id', 1) // 1 = FREE
            ->where('users.is_admin', 0)
            ->where('users.unsuscribe', 0)
            ->where('users.source_app', 'apiempresas')
            ->get()->getResultArray();

        CLI::write('- Usuarios Free detectados: ' . count($usersToCheck));

        foreach ($usersToCheck as $user) {
            $this->processTriggersForUser($user);
        }

        CLI::write('✅ Proceso de automatización finalizado.', 'green');

        // --- BLOQUE 2: USUARIOS CON ALTA TASA DE ERRORES 400 ---
        CLI::write('🔍 Detectando usuarios con errores 400 en peticiones...', 'cyan');
        $this->processBadRequestUsers();
        CLI::write('✅ Proceso de errores 400 finalizado.', 'green');
    }

    protected function processTriggersForUser(array $user)
    {
        $userId = (int)$user['id'];
        $totalRequests = $this->getTotalRequests($userId);
        $lastRequestTime = $this->getLastRequestTime($userId);
        $createdAt = $user['created_at'];

        // 0. TRIGGER: reached_100_percent_quota (Bono de 100)
        if ($totalRequests >= 100) {
            $this->checkAndSend($user, 'reached_100_percent_quota', 'email_sent_quota_max', [], true);
            return;
        }

        // 1. TRIGGER: limit_warning (Avisar a las 80 consultas)
        if ($totalRequests >= 80) {
            $this->checkAndSend($user, 'reached_80_requests', 'email_sent_limit_warning', [], true);
            return; // No enviamos más de uno en la misma ejecución
        }

        // 2. TRIGGER: reached_5_requests
        if ($totalRequests >= 5) {
            $this->checkAndSend($user, 'reached_5_requests', 'email_sent_engaged');
            return;
        }

        // 3. TRIGGER: one_request_inactive_1h
        if ($totalRequests === 1 && $lastRequestTime) {
            $diffSeconds = time() - strtotime($lastRequestTime);
            if ($diffSeconds >= 3600) { // 1 hour
                $this->checkAndSend($user, 'one_request_inactive_1h', 'email_sent_first_usage');
                return;
            }
        }

        // 4. TRIGGER: no_requests_15min
        if ($totalRequests === 0) {
            $diffSeconds = time() - strtotime($createdAt);
            if ($diffSeconds >= 900) { // 15 minutes
                $this->checkAndSend($user, 'no_requests_15min', 'email_sent_no_usage');
                return;
            }
        }

        // 5. TRIGGER: monthly_report (Recurrente cada 30 días)
        if (!$this->automationModel->wasSentRecently($userId, 'monthly_report', 30)) {
            $usage30Days = $this->getUsageLast30Days($userId);
            if ($usage30Days > 0) {
                $this->checkAndSend($user, 'monthly_report', 'email_sent_monthly_report', ['usage' => $usage30Days], true);
            }
        }
    }

    protected function checkAndSend(array $user, string $triggerType, string $trackingEvent, array $extraParams = [], bool $isRecurring = false)
    {
        $userId = (int)$user['id'];

        // Si es recurrente, comprobamos si se envió en los últimos 30 días
        // Si NO es recurrente, comprobamos si se envió alguna vez
        $alreadySent = $isRecurring 
            ? $this->automationModel->wasSentRecently($userId, $triggerType, 30)
            : $this->automationModel->wasSent($userId, $triggerType);

        if ($alreadySent) {
            return;
        }

        CLI::write("  -> Intentando enviar '{$triggerType}' a {$user['email']}...");

        $result = ['success' => false, 'body' => ''];
        switch ($triggerType) {
            case 'no_requests_15min':
                $result = $this->emailService->sendNoUsage15Min($user);
                break;
            case 'one_request_inactive_1h':
                $result = $this->emailService->sendOneUsageInactive1H($user);
                break;
            case 'reached_5_requests':
                $result = $this->emailService->sendReached5Requests($user);
                break;
            case 'reached_80_requests':
                $result = $this->emailService->sendReached80Requests($user);
                break;
            case 'reached_100_percent_quota':
                $result = $this->emailService->sendQuotaExceeded($user);
                break;
            case 'monthly_report':
                $usage = $extraParams['usage'] ?? 0;
                $result = $this->emailService->sendMonthlyUsageReport($user, $usage);
                break;
        }

        if ($result['success']) {
            $this->automationModel->markAsSent($userId, $triggerType, $result['body']);
            $this->recordTracking($userId, $trackingEvent);
            CLI::write("     [SENT] {$triggerType} OK", 'yellow');
        }
    }

    protected function getTotalRequests(int $userId): int
    {
        $res = $this->usageModel->selectSum('requests_count')
            ->where('user_id', $userId)
            ->get()->getRowArray();
        return (int)($res['requests_count'] ?? 0);
    }

    protected function getLastRequestTime(int $userId): ?string
    {
        $res = $this->usageModel->select('updated_at')
            ->where('user_id', $userId)
            ->orderBy('updated_at', 'DESC')
            ->first();
        return $res['updated_at'] ?? null;
    }

    protected function getUsageLast30Days(int $userId): int
    {
        $date = date('Y-m-d', strtotime('-30 days'));
        $res = $this->usageModel->selectSum('requests_count')
            ->where('user_id', $userId)
            ->where('date >=', $date)
            ->get()->getRowArray();
        return (int)($res['requests_count'] ?? 0);
    }

    protected function recordTracking(int $userId, string $eventName)
    {
        $db = \Config\Database::connect();
        // Solo si existe la tabla (aunque el usuario dice que existe)
        try {
            $db->table('tracking_events')->insert([
                'event_name' => $eventName,
                'user_id'    => $userId,
                'page'       => 'automation_email',
                'created_at' => date('Y-m-d H:i:s')
            ]);
        } catch (\Exception $e) {
            // Ignorar si falla el tracking
        }
    }

    /**
     * Detecta usuarios Free con alta tasa de errores 400 hoy,
     * les envía un email de ayuda técnica y restaura las consultas fallidas.
     * Condiciones: >= 20 errores 400, >= 30% del total, registrado hace <= 7 días,
     * y que nunca hayan recibido este correo antes.
     */
    protected function processBadRequestUsers()
    {
        $db = \Config\Database::connect();

        $results = $db->query("
            SELECT 
                u.id, u.email, u.name, u.created_at,
                SUM(CASE WHEN r.status_code = 400 THEN 1 ELSE 0 END) as bad_count,
                COUNT(r.id) as total_count
            FROM users u
            JOIN user_subscriptions us ON us.user_id = u.id AND us.status = 'active' AND us.plan_id = 1
            JOIN api_requests r ON r.user_id = u.id AND DATE(r.created_at) = CURDATE()
            WHERE u.is_admin = 0
              AND u.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
              AND u.id NOT IN (
                  SELECT user_id FROM user_email_automation
                  WHERE email_type = 'bad_request_help'
              )
            GROUP BY u.id, u.email, u.name, u.created_at
            HAVING bad_count >= 20
              AND (bad_count / total_count) >= 0.30
        ")->getResultArray();

        if (empty($results)) {
            CLI::write('  - Sin usuarios con alta tasa de errores 400 hoy.', 'dark_gray');
            return;
        }

        foreach ($results as $user) {
            $userId   = (int)$user['id'];
            $badCount = (int)$user['bad_count'];
            $restore  = min($badCount, 50); // Máximo 50 consultas a restaurar

            CLI::write("  -> {$user['email']}: {$badCount} errores 400. Restaurando {$restore} consultas...");

            // 1) Restaurar cuota en api_usage_daily
            $db->query("
                UPDATE api_usage_daily
                SET requests_count = GREATEST(0, requests_count - ?),
                    updated_at = NOW()
                WHERE user_id = ? AND date = CURDATE()
            ", [$restore, $userId]);

            // 2) Enviar correo de ayuda técnica
            $result = $this->emailService->sendBadRequestHelp($user, $restore);

            if ($result['success']) {
                // 3) Marcar como enviado para no repetir nunca
                $this->automationModel->markAsSent($userId, 'bad_request_help', $result['body']);
                CLI::write("     [SENT] bad_request_help OK — {$restore} consultas restauradas", 'yellow');
            } else {
                CLI::write("     [ERROR] No se pudo enviar el email a {$user['email']}", 'red');
            }
        }
    }
}
