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
            /*
             * Aquí se escribía el STRIPE_WEBHOOK_SECRET entero en el log ("cuidado
             * con logs en producción", decía el comentario). Ese secreto firma los
             * avisos de cobro: quien lo tenga puede fabricar un `checkout.session
             * .completed` y darse de alta en Pro sin pagar. Y los logs se copian,
             * se comparten y se suben a un ticket.
             *
             * Para diagnosticar el fallo real —que casi siempre es "el secreto del
             * .env no es el del endpoint que está enviando"— basta con saber si hay
             * secreto y de qué endpoint viene, sin publicar el valor.
             */
            log_message('debug', '[Webhook::stripe] Firma rechazada. Secreto configurado: '
                . ($endpoint_secret ? 'sí (' . strlen((string) $endpoint_secret) . ' caracteres)' : 'NO')
                . ' · cabecera recibida: ' . ($sig_header !== '' ? 'sí' : 'no'));
            return $this->response->setStatusCode(400);
        }

        // Handle the event
        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $this->handleCheckoutSessionCompleted($session);
                break;
            // Pagos que no se confirman al momento (domiciliación SEPA, por ejemplo):
            // `completed` llega con payment_status 'unpaid' y el cobro real se
            // confirma aquí. Hoy solo lo usa el pack; el resto de productos siguen
            // resolviéndose en `completed` como hasta ahora.
            case 'checkout.session.async_payment_succeeded':
                $session = $event->data->object;
                if ((($session->metadata->plan ?? '') === 'risk_pack_5') && ($session->mode ?? '') === 'payment') {
                    $this->abonarPackRiesgo($session);
                }
                break;
            case 'invoice.paid':
                $invoice = $event->data->object;
                $this->handleInvoicePaid($invoice);
                break;
            // Cobro de renovación rechazado. Stripe reintenta varias veces y manda este
            // evento en cada intento fallido; antes no se escuchaba y el cliente no se
            // enteraba hasta que la suscripción se cancelaba.
            case 'invoice.payment_failed':
                $invoice = $event->data->object;
                $this->handleInvoicePaymentFailed($invoice);
                break;
            // Cambios hechos fuera de nuestra web (portal de Stripe, panel de Stripe):
            // cancelar al final del periodo, reactivar una cancelación o renovar. Sin
            // esto la BD no se enteraba y el cliente veía un estado que no era el suyo.
            case 'customer.subscription.updated':
                $subscription = $event->data->object;
                $this->handleSubscriptionUpdated($subscription, $event->data->previous_attributes ?? null);
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

    /**
     * De qué producto es un plan cuando `api_plans.product_type` viene vacío.
     *
     * Es un respaldo, no la fuente: lo correcto es que la columna esté rellena. Se
     * mantiene deliberadamente corto y explícito —solo slugs que conocemos— porque
     * lo que hay al otro lado es cancelar suscripciones, y ahí una suposición de
     * más cuesta un cliente. Lo que no reconoce devuelve cadena vacía, y quien
     * llama debe entenderlo como "no tocar nada".
     */
    private function deducirProductType(string $slug): string
    {
        $slug = strtolower(trim($slug));

        if ($slug === '') {
            return '';
        }

        $conocidos = [
            'risk_pro'         => 'risk',
            'risk_pack_5'      => 'risk',
            'radar'            => 'radar',
            'copiloto_ventas'  => 'copilot',
        ];

        if (isset($conocidos[$slug])) {
            return $conocidos[$slug];
        }

        // 'pro' y 'business' son los planes históricos de la API.
        if (in_array($slug, ['pro', 'business', 'free'], true)) {
            return 'api';
        }

        return '';
    }

    /**
     * Abona el pack de consultas si Stripe lo da por cobrado. Idempotente.
     *
     * `$userId` lo pasa handleCheckoutSessionCompleted cuando ya lo ha resuelto
     * (incluido el alta de un invitado); si no, se toma de la propia sesión.
     */
    private function abonarPackRiesgo($session, int $userId = 0): void
    {
        if (($session->payment_status ?? '') !== 'paid') {
            log_message('info', '[Webhook::stripe] Pack de riesgo ' . ($session->id ?? '?') . ' aún sin cobrar (' . ($session->payment_status ?? '?') . '); no se abona.');
            return;
        }

        if ($userId <= 0) {
            $userId = (int) ($session->client_reference_id ?? $session->metadata->user_id ?? 0);
        }

        (new \App\Services\RiskPackService())->abonar(
            (string) ($session->id ?? ''),
            $userId,
            (int) ($session->metadata->credits ?? 5),
            (string) ($session->metadata->target_cif ?? ''),
            isset($session->amount_total) ? (int) $session->amount_total : null
        );
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
                        $isRadar = (strpos((string)$planSlug, 'radar') !== false) || (!empty($session->metadata->type) && $session->metadata->type === 'radar_export');
                        $guestIntent = $isRadar ? 'radar' : 'database';
                        $prefProduct = $isRadar ? 'radar' : 'excel_single';

                        $userId = $userModel->insert([
                            'name' => $session->customer_details->name ?? explode('@', $email)[0],
                            'email' => $email,
                            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
                            'is_active' => 1,
                            'source_app' => 'apiempresas',
                            'signup_intent' => $guestIntent,
                            'preferred_product' => $prefProduct,
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

            // RISK PACK (TRIPWIRE: PACK AUDITORIAS DE SOLVENCIA)
            if ($planSlug === 'risk_pack_5' || strpos((string)$planSlug, 'risk_pack_') === 0) {
                // Solo si está cobrado, y una sola vez por sesión de pago aunque Stripe
                // reintente el aviso (ver App\Services\RiskPackService).
                $this->abonarPackRiesgo($session, (int) $userId);
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

        if (!$plan && isset($session->amount_subtotal)) {
            $basePrice = (float)($session->amount_subtotal / 100);
            $plan = $planModel->where('price_monthly', $basePrice)->first();
            if ($plan) {
                log_message('info', "[Webhook::stripe] Plan fallback found by amount ({$basePrice}) -> {$plan->slug}");
            }
        }

        if (!$plan) {
            log_message('error', "[Webhook::stripe] Plan not found for slug: {$planSlug} or amount");
            return;
        }

        $subscriptionModel = new UsersuscriptionsModel();
        
        /*
         * 2. Suscripciones activas del MISMO producto, que son las que esta alta
         *    sustituye. Las de OTRO producto no se tocan: un cliente puede pagar
         *    la API y Solvencia a la vez.
         *
         *    Aquí había `$plan->product_type ?? 'api'`, y ese respaldo es una mina.
         *    La fila `risk_pro` de `api_plans` tiene `product_type` a NULL —lo
         *    comprobamos el 16-09—, así que dar de alta Solvencia Pro se leía como
         *    un alta de API y **cancelaba en Stripe la suscripción de API** del
         *    cliente: el que paga los dos productos pierde uno por comprar el otro,
         *    y encima de forma silenciosa, porque la cancelación va en un try/catch
         *    que solo escribe en el log.
         *
         *    Ahora el tipo se deduce del slug cuando la columna no lo dice, y si
         *    aun así no se sabe, NO se cancela nada. Equivocarse dejando dos
         *    suscripciones vivas se arregla con una consulta; equivocarse
         *    cancelando la de otro producto se arregla pidiéndole perdón.
         */
        $targetProductType = strtolower(trim((string) ($plan->product_type ?? '')));

        if ($targetProductType === '') {
            $targetProductType = $this->deducirProductType((string) ($plan->slug ?? ''));

            if ($targetProductType === '') {
                log_message('error', '[Webhook::stripe] El plan ' . ($plan->slug ?? '?')
                    . ' no declara product_type y no se puede deducir del slug: no se cancela'
                    . ' ninguna suscripción anterior. Rellena api_plans.product_type.');
            } else {
                log_message('warning', '[Webhook::stripe] product_type vacío en el plan '
                    . ($plan->slug ?? '?') . '; deducido del slug como "' . $targetProductType . '".');
            }
        }

        $oldSubscriptions = [];
        if ($targetProductType !== '') {
            $oldSubscriptions = $subscriptionModel->select('user_subscriptions.*')
                                                  ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id')
                                                  ->where('user_subscriptions.user_id', $userId)
                                                  ->where('user_subscriptions.status', 'active')
                                                  ->where('api_plans.product_type', $targetProductType)
                                                  ->findAll();
        }

        foreach ($oldSubscriptions as $oldSub) {
            // Si es una suscripción de Stripe diferente a la actual, cancelarla en Stripe
            if (!empty($oldSub->stripe_subscription_id) && $oldSub->stripe_subscription_id !== $stripeSubscriptionId) {
                try {
                    $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
                    $stripe->subscriptions->cancel($oldSub->stripe_subscription_id);
                    log_message('info', "[Webhook::stripe] Cancelada suscripción anterior de {$targetProductType} en Stripe: {$oldSub->stripe_subscription_id}");
                } catch (\Exception $e) {
                    log_message('error', "[Webhook::stripe] Error al cancelar suscripción anterior en Stripe: " . $e->getMessage());
                }
            }
        }

        // 3. Desactivar SOLO suscripciones anteriores del MISMO product_type en nuestra BD
        $oldSubIds = array_column($oldSubscriptions, 'id');
        if (!empty($oldSubIds)) {
            $subscriptionModel->whereIn('id', $oldSubIds)->set(['status' => ''])->update();
        }

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

        // Enviar email de bienvenida a Solvencia Pro si corresponde
        if ($plan->slug === 'risk_pro') {
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

        // Bienvenida a los planes de pago de la API: Pro (2) y Business (3).
        // Guardado contra reenvíos del mismo evento: una por plan y día.
        if (in_array((int) $plan->id, [2, 3], true)) {
            try {
                $automation = new \App\Models\EmailAutomationModel();
                $tipo = 'api_plan_welcome_' . (int) $plan->id;
                if (!$automation->wasSentRecently($userId, $tipo, 1)) {
                    $userRow = (new \App\Models\UserModel())->find($userId);
                    if ($userRow) {
                        $res = (new \App\Services\EmailService())->sendApiPlanWelcome(
                            ['id' => $userRow->id, 'email' => $userRow->email, 'name' => $userRow->name],
                            ['id' => (int) $plan->id, 'name' => $plan->name, 'monthly_quota' => (int) $plan->monthly_quota]
                        );
                        if (!empty($res['success'])) {
                            $automation->markAsSent($userId, $tipo, $res['body'] ?? '');
                        }
                    }
                }
            } catch (\Throwable $e) {
                log_message('error', '[Webhook::stripe] Bienvenida API: ' . $e->getMessage());
            }
        }

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
        
        $customPlanName = null;
        if ($planSlug === 'custom_bonus') {
            $freePlan = $planModel->find(1); // Use free plan as a dummy base for invoice generation
            if ($freePlan) {
                $plan = $freePlan;
            }
            $credits = (int)($metadata->credits ?? 0);
            $customPlanName = "Paquete de {$credits} créditos";
        }
        
        if (!$plan && isset($invoice->subtotal)) {
            $basePrice = (float)($invoice->subtotal / 100);
            $plan = $planModel->where('price_monthly', $basePrice)->first();
        }

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

        $baseAmount = (float)($invoice->amount_paid / 100) - (float)($invoice->tax / 100);
        $taxAmount  = (float)($invoice->tax / 100);

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
            $invoice->id,
            $baseAmount,
            $taxAmount,
            $customPlanName
        );

        if ($dbInvoice) {
            $emailService = new \App\Services\EmailService();
            $emailService->sendPaymentNotification([
                'invoice'        => $dbInvoice,
                'customer_name'  => $dbInvoice->billing_name,
                'customer_email' => $dbInvoice->billing_email,
                'plan_name'      => $customPlanName ?? ($plan->name ?? 'Descarga Excel'),
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

    /**
     * Cobro de una renovación rechazado: avisar al cliente con el enlace para pagar.
     *
     * Solo facturas de suscripción (los pagos sueltos fallan dentro del checkout,
     * delante del usuario). El enlace principal es la factura alojada de Stripe
     * (`hosted_invoice_url`), donde se paga con otra tarjeta sin iniciar sesión.
     */
    private function handleInvoicePaymentFailed($invoice): void
    {
        try {
            // Según la versión de la API de Stripe, la suscripción viene en un sitio u otro
            $stripeSubscriptionId = $invoice->subscription
                ?? ($invoice->parent->subscription_details->subscription ?? null);
            if (!$stripeSubscriptionId) {
                return;
            }

            $db  = \Config\Database::connect();
            $sub = $db->table('user_subscriptions us')
                ->select('us.user_id, ap.name AS plan_name, ap.slug AS plan_slug, ap.product_type')
                ->join('api_plans ap', 'ap.id = us.plan_id', 'left')
                ->where('us.stripe_subscription_id', $stripeSubscriptionId)
                ->orderBy('us.id', 'DESC')
                ->get()->getRowArray();

            $userId = (int) ($sub['user_id'] ?? 0);
            if (!$userId && !empty($invoice->customer)) {
                $u = $db->table('users')->select('id')->where('stripe_customer_id', $invoice->customer)->get()->getRowArray();
                $userId = (int) ($u['id'] ?? 0);
            }
            if (!$userId) {
                log_message('error', "[Webhook::paymentFailed] Sin usuario para la factura {$invoice->id} ({$stripeSubscriptionId})");
                return;
            }

            // Stripe puede reenviar el mismo evento: como mucho un correo cada 2 días
            $automation = new \App\Models\EmailAutomationModel();
            if ($automation->wasSentRecently($userId, 'payment_failed', 2)) {
                return;
            }

            $user = $db->table('users')->select('id, email, name')->where('id', $userId)->get()->getRowArray();
            if (!$user) {
                return;
            }

            $res = (new \App\Services\EmailService())->sendPaymentFailed($user, [
                'plan_name'    => (string) ($sub['plan_name'] ?? ''),
                'product_type' => (string) ($sub['product_type'] ?? ''),
                'amount'       => ((int) ($invoice->amount_due ?? 0)) / 100,
                'currency'     => strtoupper((string) ($invoice->currency ?? 'eur')),
                'attempt'      => (int) ($invoice->attempt_count ?? 1),
                'next_attempt' => !empty($invoice->next_payment_attempt) ? (int) $invoice->next_payment_attempt : null,
                'pay_url'      => (string) ($invoice->hosted_invoice_url ?? ''),
            ]);

            if (!empty($res['success']) && empty($res['skipped'])) {
                $automation->markAsSent($userId, 'payment_failed', $res['body'] ?? '');
            }

            log_message('info', "[Webhook::paymentFailed] Aviso de cobro fallido a {$user['email']} (intento " . ($invoice->attempt_count ?? '?') . ')');
        } catch (\Throwable $e) {
            // Nunca devolver error a Stripe por un correo: reintentaría el evento
            log_message('error', '[Webhook::paymentFailed] ' . $e->getMessage());
        }
    }

    /**
     * Sincroniza con la BD una suscripción que ha cambiado en Stripe.
     *
     * Convención de la casa (ApiKeyFilter, getActivePlanByUserId): `canceled` con
     * current_period_end en el futuro = el cliente conserva el plan hasta esa fecha.
     *
     *  - cancel_at_period_end = true  → status 'canceled' (conserva hasta fin de periodo).
     *    Si viene de fuera (portal), se le confirma por correo; si la baja la hizo en
     *    nuestra web, Billing ya lo marcó y ya le escribió, y no se repite.
     *  - cancel_at_period_end = false y Stripe la da por viva → status 'active' (p. ej.
     *    ha reactivado la baja desde el portal).
     *  - current_period_end se actualiza siempre (renovaciones, cambios de ciclo).
     *
     * No cambia de plan: si Stripe cambia el precio, solo se deja aviso en el log.
     * El fin real lo sigue cerrando customer.subscription.deleted.
     */
    private function handleSubscriptionUpdated($subscription, $previo = null): void
    {
        try {
            $stripeSubscriptionId = (string) ($subscription->id ?? '');
            if ($stripeSubscriptionId === '') {
                return;
            }

            $db  = \Config\Database::connect();
            $sub = $db->table('user_subscriptions')
                ->where('stripe_subscription_id', $stripeSubscriptionId)
                ->orderBy('id', 'DESC')
                ->get()->getRowArray();
            if (!$sub) {
                // Aún no existe en local (llegó antes que checkout.session.completed): lo
                // creará ese evento o invoice.paid.
                return;
            }

            // Fin de periodo: en versiones nuevas de la API va en los items
            $finTs = $subscription->current_period_end
                ?? ($subscription->items->data[0]->current_period_end ?? null);

            $estadoStripe = (string) ($subscription->status ?? '');
            $cancelaAlFin = !empty($subscription->cancel_at_period_end) || !empty($subscription->cancel_at);
            $viva         = in_array($estadoStripe, ['active', 'trialing', 'past_due'], true);

            $cambios = [];
            if ($finTs) {
                $cambios['current_period_end'] = date('Y-m-d H:i:s', (int) $finTs);
            }

            $pasaACancelada = false;
            if ($viva && $cancelaAlFin && $sub['status'] !== 'canceled') {
                $cambios['status']      = 'canceled';
                $cambios['canceled_at'] = date('Y-m-d H:i:s');
                $pasaACancelada = true;
            } elseif ($viva && !$cancelaAlFin && $sub['status'] === 'canceled') {
                // Reactivada: vuelve a renovarse
                $cambios['status']      = 'active';
                $cambios['canceled_at'] = null;
                log_message('info', "[Webhook::subUpdated] Suscripción reactivada: {$stripeSubscriptionId}");
            }

            if (!empty($cambios)) {
                $cambios['updated_at'] = date('Y-m-d H:i:s');
                $db->table('user_subscriptions')->where('id', (int) $sub['id'])->update($cambios);
            }

            // Cambio de precio hecho en Stripe: no sabemos a qué plan corresponde
            $precioNuevo = $subscription->items->data[0]->price->id ?? null;
            $precioViejo = $previo->items->data[0]->price->id ?? null;
            if ($precioViejo && $precioNuevo && $precioViejo !== $precioNuevo) {
                log_message('warning', "[Webhook::subUpdated] {$stripeSubscriptionId} cambió de precio en Stripe ({$precioViejo} → {$precioNuevo}). El plan local NO se ha cambiado: revísalo a mano.");
            }

            // Baja hecha fuera de nuestra web: confirmar al cliente como en Billing
            if ($pasaACancelada) {
                $user = $db->table('users')->select('id, email, name')->where('id', (int) $sub['user_id'])->get()->getRowArray();
                $plan = $db->table('api_plans')->select('name, product_type')->where('id', (int) $sub['plan_id'])->get()->getRowArray() ?: [];
                if ($user) {
                    (new \App\Services\EmailService())->sendSubscriptionCanceled(
                        $user,
                        ['name' => $plan['name'] ?? '', 'product_type' => $plan['product_type'] ?? ''],
                        $cambios['current_period_end'] ?? ($sub['current_period_end'] ?? null),
                        ''
                    );
                }
                log_message('info', "[Webhook::subUpdated] Cancelada al final del periodo desde fuera de la web: {$stripeSubscriptionId}");
            }
        } catch (\Throwable $e) {
            // Nunca devolver error a Stripe por esto: reintentaría el evento
            log_message('error', '[Webhook::subUpdated] ' . $e->getMessage());
        }
    }

    private function handleSubscriptionDeleted($subscription)
    {
        $stripeSubscriptionId = $subscription->id;
        $subscriptionModel = new UsersuscriptionsModel();
        
        /*
         * `customer.subscription.deleted` llega cuando la suscripción YA ha terminado:
         * al final del periodo en una baja normal, al momento en una cancelación
         * inmediata, o cuando Stripe se rinde con una tarjeta que no paga.
         *
         * Antes solo se ponía status = canceled y se dejaba current_period_end como
         * estaba. Pero todo el producto trata "canceled con periodo por delante" como
         * suscriptor (la baja normal conserva el acceso hasta el final), así que en
         * los dos últimos casos el cliente seguía teniendo Solvencia Pro hasta un mes
         * sin pagar. Se cierra el periodo en el momento real de fin.
         */
        $fin = !empty($subscription->ended_at) ? (int) $subscription->ended_at : time();
        $ahora = date('Y-m-d H:i:s', min($fin, time()));

        $subscriptionModel->where('stripe_subscription_id', $stripeSubscriptionId)
                         ->set(['status' => 'canceled', 'canceled_at' => date('Y-m-d H:i:s')])
                         ->update();

        // Solo se ACORTA: si por lo que sea la fecha guardada ya es anterior, se respeta.
        \Config\Database::connect()->table('user_subscriptions')
            ->where('stripe_subscription_id', $stripeSubscriptionId)
            ->groupStart()
                ->where('current_period_end IS NULL')
                ->orWhere('current_period_end >', $ahora)
            ->groupEnd()
            ->update(['current_period_end' => $ahora]);
                         
        log_message('info', "[Webhook::stripe] Subscription canceled: {$stripeSubscriptionId}");
    }
}
