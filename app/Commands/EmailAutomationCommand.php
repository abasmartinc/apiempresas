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
            ->select('users.*, usersuscriptions.plan_id')
            ->join('usersuscriptions', 'usersuscriptions.user_id = users.id')
            ->where('usersuscriptions.status', 'active')
            ->where('usersuscriptions.plan_id', 1) // 1 = FREE
            ->where('users.is_admin', 0)
            ->get()->getResultArray();

        CLI::write('- Usuarios Free detectados: ' . count($usersToCheck));

        foreach ($usersToCheck as $user) {
            $this->processTriggersForUser($user);
        }

        CLI::write('✅ Proceso de automatización finalizado.', 'green');
    }

    protected function processTriggersForUser(array $user)
    {
        $userId = (int)$user['id'];
        $totalRequests = $this->getTotalRequests($userId);
        $lastRequestTime = $this->getLastRequestTime($userId);
        $createdAt = $user['created_at'];

        // 1. TRIGGER: reached_12_requests
        if ($totalRequests >= 12) {
            $this->checkAndSend($user, 'reached_12_requests', 'email_sent_limit_warning');
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
    }

    protected function checkAndSend(array $user, string $triggerType, string $trackingEvent)
    {
        $userId = (int)$user['id'];

        // Usamos email_type como nombre real del campo en la tabla
        if ($this->automationModel->wasSent($userId, $triggerType)) {
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
            case 'reached_12_requests':
                $result = $this->emailService->sendReached12Requests($user);
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
}
