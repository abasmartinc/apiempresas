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

        // Llamamos a la lógica del Webhook de forma interna
        // Nota: En una arquitectura más limpia, esto estaría en un Service
        $webhook = new Webhook();
        
        // Usamos reflexión para acceder al método privado o simplemente lo hacemos público temporalmente
        // Para este caso, vamos a replicar la lógica aquí o moverla a un Service compartido
        return $this->processSubscription($userId, $planSlug, $session->subscription);
    }

    private function processSubscription($userId, $planSlug, $stripeSubscriptionId)
    {
        $planModel = new ApiPlanModel();
        $plan = $planModel->where('slug', $planSlug)->first();

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

        // Generar Factura (Simulada)
        $invoiceService = new \App\Services\InvoiceService();
        $invoiceService->createInvoiceFromPayment($userId, $plan->id);

        return true;
    }
}
