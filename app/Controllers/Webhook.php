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
        $endpoint_secret = env('STRIPE_WEBHOOK_SECRET');

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

        // 0. Only process as subscription if mode is 'subscription'
        // If mode is 'payment', it's a one-time purchase (Excel list or Custom Bonus)
        if (($session->mode ?? '') !== 'subscription') {
            log_message('info', "[Webhook::stripe] One-time payment completed for user {$userId}. No subscription created.");
            
            $userModel = new \App\Models\UserModel();

            // GUEST CHECKOUT: Si el userId es 0 o null, creamos el usuario usando el email de Stripe
            if (!$userId || $userId == '0') {
                $email = $session->customer_details->email ?? null;
                if ($email) {
                    $user = $userModel->where('email', $email)->first();
                    if (!$user) {
                        $password = bin2hex(random_bytes(8));
                        $userId = $userModel->insert([
                            'name' => $session->customer_details->name ?? explode('@', $email)[0],
                            'email' => $email,
                            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                            'is_active' => 1,
                            'source_app' => 'apiempresas',
                            'preferred_product' => 'excel_single',
                            'stripe_customer_id' => $stripeCustomerId,
                            'created_at' => date('Y-m-d H:i:s'),
                        ]);
                        log_message('info', "[Webhook::stripe] Created NEW user for guest checkout: {$email} (ID: {$userId})");
                    } else {
                        $userId = $user->id;
                        $userModel->update($userId, ['stripe_customer_id' => $stripeCustomerId]);
                        log_message('info', "[Webhook::stripe] Linked existing user for guest checkout: {$email} (ID: {$userId})");
                    }
                }
            } else {
                // Still save customer ID for existing users
                $user = $userModel->find($userId);
                if ($user && empty($user->stripe_customer_id) && $stripeCustomerId) {
                    $userModel->update($userId, ['stripe_customer_id' => $stripeCustomerId]);
                }
            }

            // CUSTOM BONUS WALLET RECHARGE
            if ($planSlug === 'custom_bonus') {
                $credits = (int) ($session->metadata->credits ?? 0);
                if ($credits > 0 && $userId > 0) {
                    $db = \Config\Database::connect();
                    
                    // 1. Añadir saldo al wallet
                    $db->query("INSERT INTO user_wallets (user_id, balance) VALUES (?, ?) ON DUPLICATE KEY UPDATE balance = balance + ?", [$userId, $credits, $credits]);
                    
                    // 2. Registrar transacción
                    $db->table('user_wallet_transactions')->insert([
                        'user_id' => $userId,
                        'amount' => $credits,
                        'transaction_type' => 'stripe_payment',
                        'reference_id' => $session->id,
                        'created_at' => date('Y-m-d H:i:s')
                    ]);
                    log_message('info', "[Webhook::stripe] Added {$credits} credits to wallet for user {$userId}");
                }
            }

            // EXPORT JOBS
            if (in_array($planSlug, ['directory_single', 'subsidies_single', 'contracts_single', 'radar'])) {
                $exportContext = json_decode($session->metadata->export_context ?? '{}', true);
                $totalCount = (int) ($session->metadata->total_count ?? 0);
                
                if ($totalCount >= 100000) {
                    $jobModel = new \App\Models\ExportJobModel();
                    $type = 'directory';
                    if ($planSlug === 'subsidies_single') $type = 'subsidies';
                    if ($planSlug === 'contracts_single') $type = 'contracts';
                    if ($planSlug === 'radar') $type = 'radar';
                    
                    $jobModel->insert([
                        'user_id' => $userId,
                        'type' => $type,
                        'context' => json_encode($exportContext),
                        'status' => 'pending'
                    ]);
                    log_message('info', "[Webhook::stripe] Created export_job for user {$userId}, type {$type}, count {$totalCount}");
                } else {
                    log_message('info', "[Webhook::stripe] Payment for {$planSlug} user {$userId}, count {$totalCount} < 100k, handled live on frontend.");
                }
            }

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
                    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
                    $stripe->subscriptions->cancel($oldSub->stripe_subscription_id);
                    log_message('info', "[Webhook::stripe] Cancelada suscripción anterior en Stripe: {$oldSub->stripe_subscription_id}");
                } catch (\Exception $e) {
                    log_message('error', "[Webhook::stripe] Error al cancelar suscripción anterior en Stripe: " . $e->getMessage());
                }
            }
        }

        // 3. Desactivar suscripciones anteriores en nuestra BD
        // Nota: El ENUM no permite 'inactive', usamos '' o 'canceled'. 
        // Para mantener coherencia con el resto del sistema, usaremos '' que es lo que el ENUM permite como fallback.
        $subscriptionModel->where('user_id', $userId)->set(['status' => ''])->update();

        // 4. Crear nueva suscripción (Recuperamos fechas reales de Stripe)
        $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
        $stripeSub = $stripe->subscriptions->retrieve($stripeSubscriptionId);

        $start = !empty($stripeSub->current_period_start) ? date('Y-m-d H:i:s', $stripeSub->current_period_start) : date('Y-m-d H:i:s');
        $end   = !empty($stripeSub->current_period_end) ? date('Y-m-d H:i:s', $stripeSub->current_period_end) : date('Y-m-d H:i:s', strtotime('+1 month'));
        if ($start === $end) {
            $end = date('Y-m-d H:i:s', strtotime($start . ' +1 month'));
        }

        $subscriptionModel->insert([
            'user_id'                => $userId,
            'plan_id'                => $plan->id,
            'stripe_subscription_id' => $stripeSubscriptionId,
            'status'                 => 'active',
            'current_period_start'   => $start,
            'current_period_end'     => $end,
            'created_at'             => date('Y-m-d H:i:s'),
            'updated_at'             => date('Y-m-d H:i:s'),
        ]);

        log_message('info', "[Webhook::stripe] Subscription created for user {$userId}");
    }

    private function handleInvoicePaid($invoice)
    {
        $stripeSubscriptionId = $invoice->subscription ?? null;

        if (!$stripeSubscriptionId) {
            // Pago único (ej. descarga de Excel)
            $userId = $invoice->metadata->user_id ?? $invoice->lines->data[0]->metadata->user_id ?? null;
            if ($userId) {
                // Fetch the inner invoice if invoice isn't passed fully with metadata, although invoice_data should map it
                $this->processSinglePaymentInvoice($invoice);
            } else {
                log_message('error', "[Webhook::handleInvoicePaid] Factura sin suscripción ni user_id en metadata: " . $invoice->id);
            }
            return;
        }

        $subscriptionModel = new UsersuscriptionsModel();
        $sub = $subscriptionModel->where('stripe_subscription_id', $stripeSubscriptionId)->first();

        // FALLBACK: Si no existe localmente, puede que el webhook 'invoice.paid' llegara ANTES que 'checkout.session.completed'
        // Intentamos recuperar la suscripción de Stripe para guardarla nosotros ahora mismo.
        if (!$sub) {
            log_message('info', "[Webhook::handleInvoicePaid] Suscripción no encontrada localmente ({$stripeSubscriptionId}). Intentando recuperación desde Stripe API...");
            try {
                $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
                $stripeSub = $stripe->subscriptions->retrieve($stripeSubscriptionId);
                
                if ($stripeSub && isset($stripeSub->metadata->user_id)) {
                    $userId   = (int)$stripeSub->metadata->user_id;
                    $planSlug = $stripeSub->metadata->plan;
                    
                    $planModel = new \App\Models\ApiPlanModel();
                    $plan = $planModel->where('slug', $planSlug)->first();
                    
                    if ($plan) {
                        $start = !empty($stripeSub->current_period_start) ? date('Y-m-d H:i:s', $stripeSub->current_period_start) : date('Y-m-d H:i:s');
                        $end   = !empty($stripeSub->current_period_end) ? date('Y-m-d H:i:s', $stripeSub->current_period_end) : date('Y-m-d H:i:s', strtotime('+1 month'));
                        if ($start === $end) {
                            $end = date('Y-m-d H:i:s', strtotime($start . ' +1 month'));
                        }

                        $subscriptionModel->insert([
                            'user_id'                => $userId,
                            'plan_id'                => $plan->id,
                            'stripe_subscription_id' => $stripeSubscriptionId,
                            'status'                 => 'active',
                            'current_period_start'   => $start,
                            'current_period_end'     => $end,
                            'created_at'             => date('Y-m-d H:i:s'),
                            'updated_at'             => date('Y-m-d H:i:s'),
                        ]);
                        $sub = $subscriptionModel->where('stripe_subscription_id', $stripeSubscriptionId)->first();
                        log_message('info', "[Webhook::handleInvoicePaid] Suscripción recuperada y creada 'on-the-fly' para el usuario {$userId}");
                    }
                }
            } catch (\Exception $e) {
                log_message('error', "[Webhook::handleInvoicePaid] Error crítico recuperando suscripción de Stripe: " . $e->getMessage());
            }
        }

        if ($sub) {
            // Recuperar suscripción de Stripe para saber el periodo real (Mensual vs Anual)
            $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
            $stripeSub = $stripe->subscriptions->retrieve($stripeSubscriptionId);

            $start = !empty($stripeSub->current_period_start) ? date('Y-m-d H:i:s', $stripeSub->current_period_start) : date('Y-m-d H:i:s');
            $end   = !empty($stripeSub->current_period_end) ? date('Y-m-d H:i:s', $stripeSub->current_period_end) : date('Y-m-d H:i:s', strtotime('+1 month'));
            if ($start === $end) {
                $end = date('Y-m-d H:i:s', strtotime($start . ' +1 month'));
            }

            // Actualizar fecha de fin usando los datos reales de Stripe
            $subscriptionModel->update($sub->id, [
                'current_period_start' => $start,
                'current_period_end'   => $end,
                'status'               => 'active',
                'updated_at'           => date('Y-m-d H:i:s'),
            ]);

            // Extraer datos fiscales del objeto Invoice de Stripe
            $billingAddress = '';
            if (isset($invoice->customer_address)) {
                $addr = $invoice->customer_address;
                $parts = array_filter([
                    $addr->line1 ?? '',
                    $addr->line2 ?? '',
                    $addr->postal_code ?? '',
                    $addr->city ?? '',
                    $addr->country ?? ''
                ]);
                $billingAddress = implode(', ', $parts);
            }

            // Tax ID (VAT/NIF) - A veces viene en customer_tax_ids (array)
            $billingVat = '';
            if (!empty($invoice->customer_tax_ids) && is_array($invoice->customer_tax_ids)) {
                // Tomamos el primero
                $firstTax = $invoice->customer_tax_ids[0];
                $billingVat = $firstTax->value ?? ''; 
            }
            // Fallback: buscar en metadata si se guardó ahí
            if (empty($billingVat) && isset($invoice->metadata->nif)) {
                 $billingVat = $invoice->metadata->nif;
            }

            // PRIORIDAD STRIPE: Nombre y Correo
            $userModel = new \App\Models\UserModel();
            $dbUser = $userModel->find($sub->user_id);
            
            // Usamos el nombre de Stripe si existe, si no, fallback a BD
            $billingName = $invoice->customer_name;
            if (empty($billingName) && $dbUser) {
                $billingName = $dbUser->name;
                if (!empty($dbUser->company)) {
                    $billingName .= " (" . $dbUser->company . ")";
                }
            }
            if (empty($billingName)) {
                $billingName = 'Cliente';
            }
            
            // Usamos el correo de Stripe si existe, si no, fallback a BD
            $billingEmail = $invoice->customer_email;
            if (empty($billingEmail) && $dbUser) {
                $billingEmail = $dbUser->email;
            }

            // Generar Factura
            $invoiceService = new \App\Services\InvoiceService();
            $invoice = $invoiceService->createInvoiceFromPayment(
                (int)$sub->user_id, 
                (int)$sub->plan_id, 
                [
                    'name'    => $billingName,
                    'email'   => $billingEmail,
                    'address' => $billingAddress,
                    'vat'     => $billingVat
                ],
                $invoice->id, // Stripe Invoice ID (in_...)
                (float)($invoice->amount_paid / 100) - (float)($invoice->tax / 100), // Base Amount
                (float)($invoice->tax / 100) // Tax
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
        } else {
            log_message('error', "[Webhook::handleInvoicePaid] Subscription not found for Stripe Subscription ID: {$stripeSubscriptionId}");
        }
    }

    private function processSinglePaymentInvoice($invoice)
    {
        // Stripe usually passes metadata either directly on invoice if created via session.invoice_creation, or inside lines
        $metadata = $invoice->metadata ?? ($invoice->lines->data[0]->metadata ?? null);
        $userId = $metadata->user_id ?? null;
        $planSlug = $metadata->plan ?? 'radar_single';

        $userModel = new \App\Models\UserModel();
        $dbUser = null;

        if ($userId && $userId != '0') {
            $dbUser = $userModel->find($userId);
        } else {
            // Guest checkout fallback: find by email
            $email = $invoice->customer_email ?? null;
            if ($email) {
                $dbUser = $userModel->where('email', $email)->first();
                if ($dbUser) {
                    $userId = $dbUser->id;
                }
            }
        }

        if (!$dbUser) {
            log_message('error', "[Webhook::processSinglePaymentInvoice] Could not find user for invoice: " . $invoice->id);
            return;
        }

        $planModel = new \App\Models\ApiPlanModel();
        $plan = $planModel->where('slug', $planSlug)->first();
        if (!$plan) {
            $plan = $planModel->where('slug', 'radar')->first();
        }

        $billingAddress = '';
        if (isset($invoice->customer_address)) {
            $addr = $invoice->customer_address;
            $parts = array_filter([
                $addr->line1 ?? '', $addr->line2 ?? '', $addr->postal_code ?? '',
                $addr->city ?? '', $addr->country ?? ''
            ]);
            $billingAddress = implode(', ', $parts);
        }

        $billingVat = '';
        if (!empty($invoice->customer_tax_ids) && is_array($invoice->customer_tax_ids)) {
            $billingVat = $invoice->customer_tax_ids[0]->value ?? ''; 
        }
        if (empty($billingVat) && isset($metadata->nif)) {
            $billingVat = $metadata->nif;
        }

        // PRIORIDAD STRIPE: Nombre y Correo
        $billingName = $invoice->customer_name;
        if (empty($billingName) && $dbUser) {
            $billingName = $dbUser->name;
            if (!empty($dbUser->company)) {
                $billingName .= " (" . $dbUser->company . ")";
            }
        }
        if (empty($billingName)) {
            $billingName = 'Cliente';
        }
        
        $billingEmail = $invoice->customer_email;
        if (empty($billingEmail) && $dbUser) {
            $billingEmail = $dbUser->email;
        }

        $invoiceService = new \App\Services\InvoiceService();
        $dbInvoice = $invoiceService->createInvoiceFromPayment(
            (int)$userId, 
            (int)($plan->id ?? 5),
            [
                'name'    => $billingName,
                'email'   => $billingEmail,
                'address' => $billingAddress,
                'vat'     => $billingVat
            ],
            $invoice->id
        );

        if ($dbInvoice) {
            $emailService = new \App\Services\EmailService();
            $emailService->sendPaymentNotification([
                'invoice'        => $dbInvoice,
                'customer_name'  => $dbInvoice->billing_name,
                'customer_email' => $dbInvoice->billing_email,
                'plan_name'      => $plan->name ?? 'Descarga Excel',
                'amount'         => $dbInvoice->total_amount,
                'currency'       => $dbInvoice->currency,
                'invoice_number' => $dbInvoice->invoice_number
            ]);
            $emailService->sendInvoiceToUser([
                'customer_name'  => $dbInvoice->billing_name,
                'customer_email' => $dbInvoice->billing_email,
                'plan_name'      => $plan->name ?? 'Descarga Excel',
                'amount'         => $dbInvoice->total_amount,
                'currency'       => $dbInvoice->currency,
                'invoice_number' => $dbInvoice->invoice_number,
                'pdf_path'       => $dbInvoice->pdf_path
            ]);
            log_message('info', "[Webhook::stripe] One-time payment invoice generated and email sent: {$invoice->id}");

            // If it's a massive download, add to export_jobs queue
            $totalCount = (int) ($metadata->total_count ?? 0);
            if ($totalCount > 100000 && in_array($planSlug, ['contracts_single', 'subsidies_single'])) {
                $exportType = $planSlug === 'contracts_single' ? 'contracts_excel' : 'subsidies_excel';
                $filtersJson = $metadata->export_context ?? '{}';
                
                $db = \Config\Database::connect();
                $db->table('export_jobs')->insert([
                    'user_email' => $billingEmail,
                    'export_type' => $exportType,
                    'filters' => $filtersJson,
                    'status' => 'pending',
                    'total_records' => $totalCount,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                log_message('info', "[Webhook::stripe] Enqueued massive export job for {$billingEmail} ({$exportType}, {$totalCount} records)");
            }
        }
    }

    private function handleSubscriptionDeleted($subscription)
    {
        $stripeSubscriptionId = $subscription->id;
        $subscriptionModel = new UsersuscriptionsModel();
        
        $subscriptionModel->where('stripe_subscription_id', $stripeSubscriptionId)
                         ->set(['status' => 'canceled', 'canceled_at' => date('Y-m-d H:i:s')])
                         ->update();
                         
        log_message('info', "[Webhook::stripe] Subscription canceled: {$stripeSubscriptionId}");
    }
}
