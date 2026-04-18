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

        CLI::write('Iniciando proceso de automatización de emails...', 'cyan');

        $this->processQuickStart();
        $this->processInactivityReminder();
        $this->processQuotaWarning();

        CLI::write('Proceso finalizado.', 'green');
    }

    /**
     * Email 1: Quick Start (5 min tras registro)
     */
    protected function processQuickStart()
    {
        CLI::write('- Comprobando activaciones Flash (5 min)...');
        
        $fiveMinsAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        
        $users = $this->userModel->asArray()
            ->where('created_at <=', $fiveMinsAgo)
            ->where('is_admin', 0)
            ->findAll();

        foreach ($users as $user) {
            $userId = (int)$user['id'];
            
            // Si ya se envió, saltar
            if ($this->automationModel->wasSent($userId, 'quick_start')) continue;

            // Si ya tiene requests, no enviar este email (ya se activó solo)
            if ($this->hasRequests($userId)) continue;

            if ($this->emailService->sendQuickStartPrompt($user)) {
                $this->automationModel->markAsSent($userId, 'quick_start');
                CLI::write("  [SENT] Quick Start -> {$user['email']}", 'yellow');
            }
        }
    }

    /**
     * Email 2: Inactivity (24h sin requests)
     */
    protected function processInactivityReminder()
    {
        CLI::write('- Comprobando recordatorios de inactividad (24h)...');
        
        $oneDayAgo = date('Y-m-d H:i:s', strtotime('-24 hours'));
        
        $users = $this->userModel->asArray()
            ->where('created_at <=', $oneDayAgo)
            ->where('is_admin', 0)
            ->findAll();

        foreach ($users as $user) {
            $userId = (int)$user['id'];
            
            if ($this->automationModel->wasSent($userId, 'inactivity_reminder')) continue;
            if ($this->hasRequests($userId)) continue;

            if ($this->emailService->sendInactivityReminder($user)) {
                $this->automationModel->markAsSent($userId, 'inactivity_reminder');
                CLI::write("  [SENT] Inactivity -> {$user['email']}", 'yellow');
            }
        }
    }

    /**
     * Email 4: Quota Warning (>50%)
     */
    protected function processQuotaWarning()
    {
        CLI::write('- Comprobando límites de cuota (>50%)...');
        
        $currentMonth = date('Y-m');
        
        // Obtenemos usuarios en plan FREE (ID 1)
        // Nota: asumo que Free es ID 1 por la lógica del Register.php
        $db = \Config\Database::connect();
        $usersToCheck = $db->table('users')
            ->select('users.*, usersuscriptions.plan_id')
            ->join('usersuscriptions', 'usersuscriptions.user_id = users.id')
            ->where('usersuscriptions.status', 'active')
            ->where('usersuscriptions.plan_id', 1) 
            ->get()->getResultArray();

        foreach ($usersToCheck as $user) {
            $userId = (int)$user['id'];
            
            // El tracking de quota lo hacemos por mes
            $trackingKey = 'quota_50_' . $currentMonth;
            if ($this->automationModel->wasSent($userId, $trackingKey)) continue;

            $usage = $this->usageModel->selectSum('requests_count')
                ->where('user_id', $userId)
                ->like('date', $currentMonth, 'after')
                ->get()->getRowArray();
            
            $count = (int)($usage['requests_count'] ?? 0);
            $limit = 100; // Free limit

            if ($count >= ($limit * 0.5)) {
                if ($this->emailService->sendQuotaWarning($user, 50)) {
                    $this->automationModel->markAsSent($userId, $trackingKey);
                    CLI::write("  [SENT] Quota Warning (50%) -> {$user['email']}", 'yellow');
                }
            }
        }
    }

    protected function hasRequests(int $userId): bool
    {
        $res = $this->usageModel->where('user_id', $userId)->limit(1)->find();
        return !empty($res);
    }
}
