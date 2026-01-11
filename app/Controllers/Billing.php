<?php

namespace App\Controllers;

use App\Models\ApikeysModel;
use App\Models\ApiRequestsModel;
use App\Models\UserModel;
use App\Models\UsersuscriptionsModel;

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
        $user   = $this->userModel->find($userId);

        $data['user'] = $user;
        $data['api_key'] = $this->ApikeysModel->where(['user_id' => $userId, 'is_active' => 1])->first();

        // Tu método ya existente
        $data['plan'] = $this->UsersuscriptionsModel->getActivePlanByUserId($userId);

        $data['api_request_total_month'] = $this->ApiRequestsModel->countRequestsForMonth(date('Y-m'), ['user_id' => $userId]);

        // Para la vista: plan actual en texto, etc.
        $data['current_plan'] = is_array($data['plan']) ? ($data['plan']['name'] ?? null) : (is_object($data['plan']) ? ($data['plan']->name ?? null) : null);

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
        $user   = $this->userModel->find($userId);

        $plan   = strtolower(trim((string) $this->request->getPost('plan')));          // pro | business
        $period = strtolower(trim((string) $this->request->getPost('period')));        // monthly | annual
        $pm     = strtolower(trim((string) $this->request->getPost('payment_method'))); // stripe | paypal

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
        $billEmail = trim((string) $this->request->getPost('email')) ?: (string)($user['email'] ?? '');
        $billName  = trim((string) $this->request->getPost('name'));

        if ($pm === 'paypal') {
            return $this->startPaypalSubscription($userId, $plan, $period);
        }

        return $this->startStripeCheckout($userId, $plan, $period, $billEmail, $billName);
    }

    private function startStripeCheckout(int $userId, string $plan, string $period, ?string $email, ?string $name)
    {
        // 1) Mapea a PRICE IDs (creados en Stripe Dashboard)
        // Sustituye estos placeholders por tus price_ reales:
        $stripePrices = [
            'pro' => [
                'monthly' => 'price_PRO_MONTHLY_ID',
                'annual'  => 'price_PRO_ANNUAL_ID',
            ],
            'business' => [
                'monthly' => 'price_BUSINESS_MONTHLY_ID',
                'annual'  => 'price_BUSINESS_ANNUAL_ID',
            ],
        ];

        $priceId = $stripePrices[$plan][$period] ?? null;

        if (!$priceId || strpos($priceId, 'price_') !== 0) {
            return redirect()->back()->with('error', 'Stripe no está configurado (price_id faltante).');
        }

        $secretKey = getenv('STRIPE_SECRET_KEY');
        if (!$secretKey) {
            return redirect()->back()->with('error', 'Stripe no está configurado (STRIPE_SECRET_KEY).');
        }

        // Requiere: composer require stripe/stripe-php
        \Stripe\Stripe::setApiKey($secretKey);

        try {
            $successUrl = site_url('billing/success') . '?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl  = site_url('billing/cancel');

            $session = \Stripe\Checkout\Session::create([
                'mode' => 'subscription',
                'line_items' => [[
                    'price'    => $priceId,
                    'quantity' => 1,
                ]],
                'success_url' => $successUrl,
                'cancel_url'  => $cancelUrl,

                // Para reconciliar en webhook:
                'client_reference_id' => (string)$userId,
                'metadata' => [
                    'user_id' => (string)$userId,
                    'plan'    => $plan,
                    'period'  => $period,
                ],

                // Pre-fill
                'customer_email' => $email ?: null,
            ]);

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
        return view('purchase_success');
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
                'annual'  => 'P-PRO-ANNUAL-PLAN-ID',
            ],
            'business' => [
                'monthly' => 'P-BUSINESS-MONTHLY-PLAN-ID',
                'annual'  => 'P-BUSINESS-ANNUAL-PLAN-ID',
            ],
        ];

        $ppPlanId = $paypalPlanIds[$plan][$period] ?? null;
        if (!$ppPlanId) {
            return redirect()->back()->with('error', 'PayPal no está configurado (plan_id faltante).');
        }

        $mode = getenv('PAYPAL_MODE') ?: 'sandbox';
        $base = ($mode === 'live') ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        $clientId = getenv('PAYPAL_CLIENT_ID');
        $secret   = getenv('PAYPAL_SECRET');

        if (!$clientId || !$secret) {
            return redirect()->back()->with('error', 'PayPal no está configurado (PAYPAL_CLIENT_ID/SECRET).');
        }

        // ===== 1) OAuth token =====
        $ch = curl_init($base . '/v1/oauth2/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_USERPWD        => $clientId . ':' . $secret,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => 'grant_type=client_credentials',
            CURLOPT_HTTPHEADER     => ['Accept: application/json', 'Accept-Language: en_US'],
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
            'plan_id'   => $ppPlanId,
            'custom_id' => (string) $userId, // para reconciliar
            'application_context' => [
                'brand_name' => 'APIEmpresas.es',
                'locale'     => 'es-ES',
                'user_action'=> 'SUBSCRIBE_NOW',
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ],
        ];

        $ch = curl_init($base . '/v1/billing/subscriptions');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/json',
                'Authorization: Bearer ' . $accessToken,
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
        ]);

        $subRes = curl_exec($ch);
        $subErr = curl_error($ch);
        curl_close($ch);

        if ($subRes === false) {
            log_message('error', '[PayPal create subscription] ' . $subErr);
            return redirect()->back()->with('error', 'No se pudo crear la suscripción en PayPal.');
        }

        $subJson = json_decode($subRes, true);
        $links   = $subJson['links'] ?? [];

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
}
