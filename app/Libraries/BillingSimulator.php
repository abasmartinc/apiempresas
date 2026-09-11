<?php

namespace App\Libraries;

use App\Models\UserModel;
use App\Models\ApiPlanModel;
use App\Models\UsersuscriptionsModel;
use App\Controllers\Webhook;

class BillingSimulator
{
    /**
     * Simula un pago exitoso llamando internamente a la lógica que usaría el Webhook
     */
    public function simulatePayment(int $userId, string $planSlug, string $period)
    {
        // Simulamos el objeto de sesión que enviaría Stripe
        $session = (object)[
            'client_reference_id' => (string)$userId,
            'subscription' => 'sub_sim_' . bin2hex(random_bytes(8)),
            'metadata' => (object)[
                'user_id' => (string)$userId,
                'plan'    => $planSlug,
                'period'  => $period
            ]
        ];

        if ($planSlug === 'risk_pack_5' || strpos($planSlug, 'risk_pack_') === 0) {
            if ($userId > 0) {
                $db = \Config\Database::connect();
                $db->table('users')
                    ->where('id', $userId)
                    ->set('risk_credits', 'risk_credits + 5', false)
                    ->update();

                $userEventsModel = new \App\Models\UserEventsModel();
                $userEventsModel->logEvent($userId, 'purchase_risk_pack', '5');

                $userRow = (new \App\Models\UserModel())->find($userId);
                if ($userRow) {
                    $emailService = new \App\Services\EmailService();
                    $emailService->sendRiskPackWelcome([
                        'name'    => $userRow->name,
                        'email'   => $userRow->email,
                        'user_id' => $userRow->id
                    ], 5);
                }
            }
            log_message('info', "[Simulator] Added 5 risk_credits to user {$userId}");
            return true;
        }

        if ($period === 'single') {
            log_message('info', "[Simulator] One-time purchase simulated for user {$userId}. Skipping plan activation.");
            return true;
        }

        // Llamamos a la lógica del Webhook de forma interna
        return $this->processSubscription($userId, $planSlug, $session->subscription);
    }

    private function processSubscription($userId, $planSlug, $stripeSubscriptionId)
    {
        $planModel = new ApiPlanModel();
        $plan = $planModel->where('slug', $planSlug)->first();

        if (!$plan && $planSlug === 'radar') {
            // Mock a plan object for the Radar plan if it's not in the DB
            $plan = (object)[
                'id' => 999, // A symbolic ID or handle specially if needed
                'name' => 'Radar B2B',
                'slug' => 'radar'
            ];
        }

        if (!$plan) {
            return false;
        }

        $subscriptionModel = new UsersuscriptionsModel();
        
        // Desactivar suscripciones anteriores
        $subscriptionModel->where('user_id', $userId)->set(['status' => 'inactive'])->update();

        // Crear nueva suscripción
        $subscriptionModel->insert([
            'user_id'                => $userId,
            'plan_id'                => $plan->id,
            'stripe_subscription_id' => $stripeSubscriptionId,
            'status'                 => 'active',
            'current_period_start'   => date('Y-m-d H:i:s'),
            'current_period_end'     => date('Y-m-d H:i:s', strtotime('+1 month')),
            'created_at'             => date('Y-m-d H:i:s'),
            'updated_at'             => date('Y-m-d H:i:s'),
        ]);

        // Enviar email de bienvenida a Solvencia Pro si corresponde
        if ($planSlug === 'risk_pro') {
            $userRow = (new \App\Models\UserModel())->find($userId);
            if ($userRow) {
                $emailService = new \App\Services\EmailService();
                $emailService->sendRiskProWelcome([
                    'name'    => $userRow->name,
                    'email'   => $userRow->email,
                    'user_id' => $userRow->id
                ]);
            }
        }

        // Generar Factura (Simulada)
        $invoiceService = new \App\Services\InvoiceService();
        $invoiceService->createInvoiceFromPayment($userId, $plan->id);

        return true;
    }

    /**
     * Simula la compra de un bono (wallet recharge) sin pasar por Stripe
     */
    public function simulateBonusRecharge(int $userId, int $credits)
    {
        $db = \Config\Database::connect();
        
        // 1. Añadir saldo al wallet
        $db->query("INSERT INTO user_wallets (user_id, balance) VALUES (?, ?) ON DUPLICATE KEY UPDATE balance = balance + ?", [$userId, $credits, $credits]);
        
        // 2. Registrar transacción si existe la tabla
        if ($db->tableExists('user_wallet_transactions')) {
            $db->table('user_wallet_transactions')->insert([
                'user_id' => $userId,
                'amount' => $credits,
                'transaction_type' => 'stripe_payment',
                'reference_id' => 'sim_bonus_' . bin2hex(random_bytes(8)),
                'created_at' => date('Y-m-d H:i:s')
            ]);
        }
        
        log_message('info', "[Simulator] Added {$credits} credits to wallet for user {$userId}");
        return true;
    }
}

