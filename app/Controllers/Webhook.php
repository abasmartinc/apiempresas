<?php

namespace App\Controllers;

use App\Models\UsersuscriptionsModel;
use App\Models\ApiPlanModel;
use CodeIgniter\Controller;

class Webhook extends Controller
{
    public function stripe()
    {
        $payload = @file_get_contents('php://input');
        $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'] ?? '';
        $endpoint_secret = getenv('STRIPE_WEBHOOK_SECRET');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch (\UnexpectedValueException $e) {
            log_message('error', '[Webhook::stripe] Invalid payload: ' . $e->getMessage());
            return $this->response->setStatusCode(400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            log_message('error', '[Webhook::stripe] Invalid signature: ' . $e->getMessage());
            // Para debug (cuidado con logs en producción)
            log_message('debug', '[Webhook::stripe] Recibido Header: ' . $sig_header);
            log_message('debug', '[Webhook::stripe] Usando Secret de .env: ' . $endpoint_secret);
            return $this->response->setStatusCode(400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $this->handleCheckoutSessionCompleted($session);
                break;
            case 'invoice.paid':
                $invoice = $event->data->object;
                $this->handleInvoicePaid($invoice);
                break;
            case 'customer.subscription.deleted':
                $subscription = $event->data->object;
                $this->handleSubscriptionDeleted($subscription);
                break;
            // Add other event types here if needed
            default:
                echo 'Received unknown event type ' . $event->type;
        }

        return $this->response->setStatusCode(200);
    }

    private function handleCheckoutSessionCompleted($session)
    {
        $userId = $session->client_reference_id ?? $session->metadata->user_id ?? null;
        $planSlug = $session->metadata->plan ?? null;
        $stripeSubscriptionId = $session->subscription ?? null;
        $stripeCustomerId = $session->customer ?? null;
        
        if (!$userId || !$planSlug) {
            log_message('error', '[Webhook::stripe] Missing userId or planSlug in session metadata.');
            return;
        }

        // 1. Guardar el stripe_customer_id en el usuario si aún no lo tiene
        $userModel = new \App\Models\UserModel();
        $user = $userModel->find($userId);
        if ($user && empty($user->stripe_customer_id) && $stripeCustomerId) {
            $userModel->update($userId, ['stripe_customer_id' => $stripeCustomerId]);
        }

        $planModel = new ApiPlanModel();
        $plan = $planModel->where('slug', $planSlug)->first();

        if (!$plan) {
            log_message('error', "[Webhook::stripe] Plan not found for slug: {$planSlug}");
            return;
        }

        $subscriptionModel = new UsersuscriptionsModel();
        
        // 2. Buscar si ya tenía suscripciones activas
        $oldSubscriptions = $subscriptionModel->where('user_id', $userId)
                                              ->where('status', 'active')
                                              ->findAll();

        foreach ($oldSubscriptions as $oldSub) {
            // Si es una suscripción de Stripe diferente a la actual, cancelarla en Stripe
            if (!empty($oldSub->stripe_subscription_id) && $oldSub->stripe_subscription_id !== $stripeSubscriptionId) {
                try {
                    $stripe = new \Stripe\StripeClient(getenv('STRIPE_SECRET_KEY'));
                    $stripe->subscriptions->cancel($oldSub->stripe_subscription_id);
                    log_message('info', "[Webhook::stripe] Cancelada suscripción anterior en Stripe: {$oldSub->stripe_subscription_id}");
                } catch (\Exception $e) {
                    log_message('error', "[Webhook::stripe] Error al cancelar suscripción anterior en Stripe: " . $e->getMessage());
                }
            }
        }

        // 3. Desactivar suscripciones anteriores en nuestra BD
        $subscriptionModel->where('user_id', $userId)->set(['status' => 'inactive'])->update();

        // 4. Crear nueva suscripción
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

        log_message('info', "[Webhook::stripe] Subscription created for user {$userId}");
    }

    private function handleInvoicePaid($invoice)
    {
        $stripeSubscriptionId = $invoice->subscription;
        if (!$stripeSubscriptionId) return;

        $subscriptionModel = new UsersuscriptionsModel();
        $sub = $subscriptionModel->where('stripe_subscription_id', $stripeSubscriptionId)->first();

        if ($sub) {
            // Actualizar fecha de fin
            $subscriptionModel->update($sub->id, [
                'current_period_end' => date('Y-m-d H:i:s', strtotime('+1 month')),
                'status'             => 'active',
                'updated_at'         => date('Y-m-d H:i:s'),
            ]);

            // Generar Factura
            $invoiceService = new \App\Services\InvoiceService();
            $invoice = $invoiceService->createInvoiceFromPayment(
                (int)$sub->user_id, 
                (int)$sub->plan_id, 
                [
                    'name'  => $invoice->customer_name ?? null,
                    'email' => $invoice->customer_email ?? null,
                ],
                $invoice->id // Stripe Invoice ID (in_...)
            );

            // Enviar notificación por email al admin
            if ($invoice) {
                $emailService = new \App\Services\EmailService();
                $emailService->sendPaymentNotification([
                    'invoice'        => $invoice,
                    'customer_name'  => $invoice->billing_name,
                    'customer_email' => $invoice->billing_email,
                    'plan_name'      => $sub->plan_name ?? 'Plan API',
                    'amount'         => $invoice->total_amount,
                    'currency'       => $invoice->currency,
                    'invoice_number' => $invoice->invoice_number
                ]);
                // Enviar notificación por email al usuario con su factura adjunta
                $emailService->sendInvoiceToUser([
                    'customer_name'  => $invoice->billing_name,
                    'customer_email' => $invoice->billing_email,
                    'plan_name'      => $sub->plan_name ?? 'Plan API',
                    'amount'         => $invoice->total_amount,
                    'currency'       => $invoice->currency,
                    'invoice_number' => $invoice->invoice_number,
                    'pdf_path'       => $invoice->pdf_path
                ]);
            }

            log_message('info', "[Webhook::stripe] Subscription renewed/paid, invoice generated and email sent: {$stripeSubscriptionId}");
        }
    }

    private function handleSubscriptionDeleted($subscription)
    {
        $stripeSubscriptionId = $subscription->id;
        $subscriptionModel = new UsersuscriptionsModel();
        
        $subscriptionModel->where('stripe_subscription_id', $stripeSubscriptionId)
                         ->set(['status' => 'inactive', 'canceled_at' => date('Y-m-d H:i:s')])
                         ->update();
                         
        log_message('info', "[Webhook::stripe] Subscription canceled: {$stripeSubscriptionId}");
    }
}
