<?php

namespace App\Controllers;

use App\Models\ApikeysModel;
use App\Models\ApiRequestsModel;
use App\Models\UserModel;
use App\Models\UsersuscriptionsModel;
use App\Models\InvoiceModel;

class Billing extends BaseController
{
    /** @var UserModel */
    protected $userModel;
    protected $ApikeysModel;
    protected $UsersuscriptionsModel;
    protected $ApiRequestsModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->ApikeysModel = new ApikeysModel();
        $this->UsersuscriptionsModel = new UsersuscriptionsModel();
        $this->ApiRequestsModel = new ApiRequestsModel();
    }

    public function index()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }

        $userId = (int) session('user_id');
        $user = $this->userModel->find($userId);

        $data['user'] = $user;
        $data['api_key'] = $this->ApikeysModel->where(['user_id' => $userId, 'is_active' => 1])->first();

        // Tu método ya existente
        $data['plan'] = $this->UsersuscriptionsModel->getActivePlanByUserId($userId);

        $data['api_request_total_month'] = $this->ApiRequestsModel->countRequestsForMonth(date('Y-m'), ['user_id' => $userId]);

        // Para la vista: plan actual en texto, etc.
        $data['current_plan'] = is_array($data['plan']) ? ($data['plan']['plan_name'] ?? null) : (is_object($data['plan']) ? ($data['plan']->plan_name ?? null) : null);
        $data['stripe_customer_id'] = $user->stripe_customer_id ?? null;

        return view('billing', $data);
    }

    /**
     * POST /billing/checkout
     * Redirige a Stripe Checkout (subscription) o PayPal approve link
     */
    public function checkout()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = (int) session('user_id');
        $user = $this->userModel->find($userId);

        $plan = strtolower(trim((string) $this->request->getPost('plan')));          // pro | business
        $period = strtolower(trim((string) $this->request->getPost('period')));        // monthly | annual
        $pm = strtolower(trim((string) $this->request->getPost('payment_method'))); // stripe | paypal

        if (!in_array($plan, ['pro', 'business'], true)) {
            return redirect()->back()->with('error', 'Plan inválido.');
        }
        if (!in_array($period, ['monthly', 'annual'], true)) {
            $period = 'monthly';
        }
        if (!in_array($pm, ['stripe', 'paypal'], true)) {
            $pm = 'stripe';
        }

        // Datos opcionales de facturación (solo para pre-rellenar)
        $billEmail = trim((string) $this->request->getPost('email')) ?: (string) ($user['email'] ?? '');
        $billName = trim((string) $this->request->getPost('name'));

        if ($pm === 'paypal' && getenv('BILLING_MODE') !== 'simulator') {
            return $this->startPaypalSubscription($userId, $plan, $period);
        }

        if (getenv('BILLING_MODE') === 'simulator') {
            $simulator = new \App\Libraries\BillingSimulator();
            if ($simulator->simulatePayment($userId, $plan, $period)) {
                return redirect()->to(site_url('billing/success'))->with('message', 'Simulación de pago completada con éxito.');
            } else {
                return redirect()->back()->with('error', 'Error en la simulación del pago.');
            }
        }

        return $this->startStripeCheckout($userId, $plan, $period, $billEmail, $billName);
    }

    private function startStripeCheckout(int $userId, string $plan, string $period, ?string $email, ?string $name)
    {
        // Check for CURL extension (required by Stripe SDK)
        if (!extension_loaded('curl')) {
            return redirect()->back()->with('error', 'El servidor no tiene habilitada la extensión CURL, necesaria para procesar pagos con Stripe. Por favor, habilítala en Laragon (Menú -> PHP -> Extensions -> curl) y reinicia los servicios.');
        }

        // 1) Obtener Precio de la Base de Datos (ApiPlans)
        $planModel = new \App\Models\ApiPlanModel();
        $dbPlan = $planModel->where('slug', $plan)->first();

        if (!$dbPlan) {
            return redirect()->back()->with('error', 'El plan seleccionado no existe.');
        }

        // Determinar precio según periodicidad
        $amount = 0.0;
        if ($period === 'annual') {
            // Asumimos que la columna existe. Si no, fallback o error.
            if (isset($dbPlan->price_annual)) {
                $amount = (float) $dbPlan->price_annual;
            } else {
                // Fallback temporal si no existe la columna (ej: x10) o error
                 return redirect()->back()->with('error', 'El precio anual no está configurado para este plan.');
            }
        } else {
            $amount = (float) $dbPlan->price_monthly;
        }

        // Validación de precio mínimo
        if ($amount <= 0) {
             return redirect()->back()->with('error', 'El precio del plan no es válido.');
        }
        
        $secretKey = getenv('STRIPE_SECRET_KEY');
        if (!$secretKey) {
            return redirect()->back()->with('error', 'Stripe no está configurado (STRIPE_SECRET_KEY).');
        }

        // Requiere: composer require stripe/stripe-php
        \Stripe\Stripe::setApiKey($secretKey);

        try {
            $successUrl = site_url('billing/success') . '?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = site_url('billing/cancel');

            // Usar price_data para crear el precio al vuelo basado en la DB
            $lineItem = [
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int)($amount * 100), // En céntimos
                    'recurring' => [
                        'interval' => ($period === 'annual' ? 'year' : 'month')
                    ],
                    'product_data' => [
                        'name' => 'Suscripción ' . ($dbPlan->name ?? ucfirst($plan)),
                        'description' => 'Acceso ' . ucfirst($period) . ' al plan ' . ucfirst($plan)
                    ]
                ]
            ];

            // Preparar parámetros de sesión
            $sessionParams = [
                'mode' => 'subscription',
                'line_items' => [$lineItem],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'client_reference_id' => (string) $userId,
                'metadata' => [
                    'user_id' => (string) $userId,
                    'plan' => $plan,
                    'period' => $period,
                ],
            ];

            // Buscar si el usuario ya tiene un customer_id en Stripe
            $user = $this->userModel->find($userId);
            if (!empty($user->stripe_customer_id)) {
                $sessionParams['customer'] = $user->stripe_customer_id;
            } else {
                // Solo enviamos email si no hay customer asignado (Stripe fallará si envías ambos)
                $sessionParams['customer_email'] = $email ?: null;
            }

            $session = \Stripe\Checkout\Session::create($sessionParams);

            return redirect()->to($session->url);

        } catch (\Throwable $e) {
            log_message('error', '[Billing::startStripeCheckout] ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo iniciar el pago con Stripe.');
        }
    }

    /**
     * GET /billing/success
     * (Pantalla de éxito). Estado real mejor por webhook Stripe.
     */
    public function success()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }

        $userId = (int) session('user_id');

        // Fetch user's active subscription
        $subscription = $this->UsersuscriptionsModel->getActivePlanByUserId($userId);

        if (!$subscription) {
            // If no active subscription, redirect to billing
            return redirect()->to(site_url('billing'));
        }

        // Prepare data for the view
        $data = [];

        // Plan information (using object notation)
        $data['plan_name'] = $subscription->plan_name ?? 'Pro';
        $data['base_price'] = $subscription->price_monthly ?? '19';

        // Determine period (we'll assume monthly for now, could be enhanced)
        $data['period_name'] = 'Mensual';

        // Payment method (from subscription or default)
        $data['payment_method'] = 'Tarjeta (Stripe)';

        // Order reference (use subscription ID)
        $data['order_ref'] = 'SUB-' . str_pad($subscription->id ?? '0', 6, '0', STR_PAD_LEFT);

        return view('purchase_success', $data);
    }

    public function cancel()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }
        // puedes crear una vista billing_cancel si quieres
        return redirect()->to(site_url('billing'))->with('error', 'Has cancelado el proceso de pago.');
    }

    /**
     * PayPal: crea Subscription y redirige al approve link
     * Requiere:
     * PAYPAL_MODE=sandbox|live
     * PAYPAL_CLIENT_ID=...
     * PAYPAL_SECRET=...
     */
    private function startPaypalSubscription(int $userId, string $plan, string $period)
    {
        // 1) Mapear a PLAN IDs creados en PayPal
        $paypalPlanIds = [
            'pro' => [
                'monthly' => 'P-PRO-MONTHLY-PLAN-ID',
                'annual' => 'P-PRO-ANNUAL-PLAN-ID',
            ],
            'business' => [
                'monthly' => 'P-BUSINESS-MONTHLY-PLAN-ID',
                'annual' => 'P-BUSINESS-ANNUAL-PLAN-ID',
            ],
        ];

        $ppPlanId = $paypalPlanIds[$plan][$period] ?? null;
        if (!$ppPlanId) {
            return redirect()->back()->with('error', 'PayPal no está configurado (plan_id faltante).');
        }

        $mode = getenv('PAYPAL_MODE') ?: 'sandbox';
        $base = ($mode === 'live') ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        $clientId = getenv('PAYPAL_CLIENT_ID');
        $secret = getenv('PAYPAL_SECRET');

        if (!$clientId || !$secret) {
            return redirect()->back()->with('error', 'PayPal no está configurado (PAYPAL_CLIENT_ID/SECRET).');
        }

        // ===== 1) OAuth token =====
        $ch = curl_init($base . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD => $clientId . ':' . $secret,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER => ['Accept: application/json', 'Accept-Language: en_US'],
        ]);

        $tokenRes = curl_exec($ch);
        $tokenErr = curl_error($ch);
        curl_close($ch);

        if ($tokenRes === false) {
            log_message('error', '[PayPal token] ' . $tokenErr);
            return redirect()->back()->with('error', 'No se pudo iniciar PayPal (token).');
        }

        $tokenJson = json_decode($tokenRes, true);
        $accessToken = $tokenJson['access_token'] ?? null;

        if (!$accessToken) {
            log_message('error', '[PayPal token invalid] ' . $tokenRes);
            return redirect()->back()->with('error', 'No se pudo iniciar PayPal (token inválido).');
        }

        // ===== 2) Create subscription =====
        $returnUrl = site_url('billing/paypal/return');
        $cancelUrl = site_url('billing/cancel');

        $payload = [
            'plan_id' => $ppPlanId,
            'custom_id' => (string) $userId, // para reconciliar
            'application_context' => [
                'brand_name' => 'APIEmpresas.es',
                'locale' => 'es-ES',
                'user_action' => 'SUBSCRIBE_NOW',
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ],
        ];

        $ch = curl_init($base . '/v1/billing/subscriptions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
        ]);

        $subRes = curl_exec($ch);
        $subErr = curl_error($ch);
        curl_close($ch);

        if ($subRes === false) {
            log_message('error', '[PayPal create subscription] ' . $subErr);
            return redirect()->back()->with('error', 'No se pudo crear la suscripción en PayPal.');
        }

        $subJson = json_decode($subRes, true);
        $links = $subJson['links'] ?? [];

        $approveUrl = null;
        foreach ($links as $lnk) {
            if (($lnk['rel'] ?? '') === 'approve') {
                $approveUrl = $lnk['href'] ?? null;
                break;
            }
        }

        if (!$approveUrl) {
            log_message('error', '[PayPal create subscription missing approve] ' . $subRes);
            return redirect()->back()->with('error', 'PayPal no devolvió enlace de aprobación.');
        }

        return redirect()->to($approveUrl);
    }

    /**
     * GET /billing/paypal/return
     * Aquí PayPal suele devolver subscription_id en query. Para estado real, webhook o consulta API.
     */
    public function paypalReturn()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }

        return view('purchase_success');
    }

    public function purchase_success()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }
        return view('purchase_success');
    }

    public function billing_manage()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }
        return view('billing_manage');
    }

    /**
     * Listado de facturas del usuario
     */
    public function invoices()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = (int) session('user_id');
        $invoiceModel = new InvoiceModel();

        $data = [
            'title' => 'Mis Facturas',
            'invoices' => $invoiceModel->where('user_id', $userId)->orderBy('created_at', 'DESC')->paginate(10),
            'pager' => $invoiceModel->pager,
            'user' => $this->userModel->find($userId)
        ];

        return view('billing/invoices', $data);
    }

    /**
     * Descargar factura propia
     */
    public function invoice_download($id)
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = (int) session('user_id');
        $invoiceModel = new InvoiceModel();

        // Seguridad: Solo puede descargar si le pertenece
        $invoice = $invoiceModel->where(['id' => $id, 'user_id' => $userId])->first();

        if (!$invoice || !$invoice->pdf_path) {
            return redirect()->back()->with('error', 'Factura no encontrada o no tienes permiso.');
        }

        $fullPath = ROOTPATH . $invoice->pdf_path;
        if (!file_exists($fullPath)) {
            return redirect()->back()->with('error', 'El archivo de la factura no está disponible.');
        }

        return $this->response->download($fullPath, null)->setFileName($invoice->invoice_number . '.pdf');
    }

    /**
     * Rotar la API Key del usuario
     */
    public function rotate_key()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = (int) session('user_id');

        // 1. Desactivar claves anteriores
        $this->ApikeysModel->where('user_id', $userId)->set(['is_active' => 0])->update();

        // 2. Generar nueva clave
        $newKey = 'ak_' . bin2hex(random_bytes(16));

        // 3. Insertar nueva clave
        $this->ApikeysModel->insert([
            'user_id' => $userId,
            'name' => 'Clave Principal (Rotada)',
            'api_key' => $newKey,
            'is_active' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // Log API key rotation
        log_activity('api_key_rotated');

        return redirect()->to(site_url('dashboard'))->with('message', 'Tu API Key ha sido rotada con éxito. Recuerda actualizar tus aplicaciones.');
    }

    /**
     * Cancelar suscripción activa
     */
    public function cancel_subscription()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = (int) session('user_id');
        $plan = $this->UsersuscriptionsModel->getActivePlanByUserId($userId);

        if (!$plan) {
            return redirect()->back()->with('error', 'No tienes ninguna suscripción activa para cancelar.');
        }

        // 1. Si es Stripe, cancelar en Stripe (al final del periodo)
        if (!empty($plan->stripe_subscription_id)) {
            try {
                $secretKey = getenv('STRIPE_SECRET_KEY');
                if ($secretKey) {
                    \Stripe\Stripe::setApiKey($secretKey);
                    \Stripe\Subscription::update($plan->stripe_subscription_id, [
                        'cancel_at_period_end' => true,
                    ]);
                    log_message('info', "[Billing::cancel_subscription] Suscripción Stripe marcada para cancelar: {$plan->stripe_subscription_id}");
                }
            } catch (\Exception $e) {
                log_message('error', "[Billing::cancel_subscription] Error al cancelar en Stripe: " . $e->getMessage());
                // Podríamos decidir si continuar o no. Normalmente, si falla Stripe, queremos avisar.
                return redirect()->back()->with('error', 'No se pudo comunicar la cancelación a Stripe: ' . $e->getMessage());
            }
        }

        // 2. Marcar como cancelado en nuestra DB
        $this->UsersuscriptionsModel->update($plan->id, [
            'status' => 'canceled',
            'canceled_at' => date('Y-m-d H:i:s')
        ]);

        // Log subscription cancellation
        log_activity('subscription_cancelled', ['plan' => $plan->plan_name ?? 'Unknown']);

        return redirect()->to(site_url('billing'))->with('message', 'Tu suscripción ha sido cancelada. Seguirás teniendo acceso hasta el final del periodo facturado y no se te cobrará de nuevo.');
    }

    /**
     * Redirigir al Stripe Customer Portal
     */
    public function portal()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        $userId = (int) session('user_id');
        $user = $this->userModel->find($userId);

        if (!$user || empty($user->stripe_customer_id)) {
            return redirect()->to(site_url('billing'))->with('error', 'No tienes un historial de facturación en Stripe todavía o no tienes suscripciones activas.');
        }

        $secretKey = getenv('STRIPE_SECRET_KEY');
        if (!$secretKey) {
            return redirect()->back()->with('error', 'Stripe no está configurado.');
        }

        \Stripe\Stripe::setApiKey($secretKey);

        try {
            $session = \Stripe\BillingPortal\Session::create([
                'customer' => $user->stripe_customer_id,
                'return_url' => site_url('billing'),
            ]);

            return redirect()->to($session->url);
        } catch (\Exception $e) {
            log_message('error', '[Billing::portal] ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo abrir el portal de Stripe: ' . $e->getMessage());
        }
    }
}
