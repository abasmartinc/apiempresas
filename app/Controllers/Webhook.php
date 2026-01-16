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
            // Invalid payload
            return $this->response->setStatusCode(400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            // Invalid signature
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
        
        if (!$userId || !$planSlug) {
            log_message('error', '[Webhook::stripe] Missing userId or planSlug in session metadata.');
            return;
        }

        $planModel = new ApiPlanModel();
        $plan = $planModel->where('slug', $planSlug)->first();

        if (!$plan) {
            log_message('error', "[Webhook::stripe] Plan not found for slug: {$planSlug}");
            return;
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

        // Generar Factura
        $invoiceService = new \App\Services\InvoiceService();
        $invoiceService->createInvoiceFromPayment($userId, $plan->id, [
            'name'  => $session->customer_details->name ?? null,
            'email' => $session->customer_details->email ?? null,
        ]);

        log_message('info', "[Webhook::stripe] Subscription created and invoice generated for user {$userId}");
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

            // Generar Factura para la renovación
            $invoiceService = new \App\Services\InvoiceService();
            $invoiceService->createInvoiceFromPayment($sub->user_id, $sub->plan_id);

            log_message('info', "[Webhook::stripe] Subscription renewed and invoice generated: {$stripeSubscriptionId}");
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
