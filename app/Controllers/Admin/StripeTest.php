<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ApiPlanModel;
use App\Models\UserModel;
use Stripe\Stripe;
use Stripe\Checkout\Session;

class StripeTest extends BaseController
{
    public function index()
    {
        if (!session('is_admin') && session('user_id') != 1) { // Adding fallback check just in case
            return redirect()->to(site_url('dashboard'));
        }

        $planModel = new ApiPlanModel();
        $testPlan = $planModel->where('slug', 'test_1euro')->first();

        // Create the test plan automatically if it does not exist
        if (!$testPlan) {
            $planModel->insert([
                'slug' => 'test_1euro',
                'name' => 'Test 1 Euro',
                'monthly_quota' => 100,
                'rate_limit_per_min' => 10,
                'price_monthly' => 1.00,
                'price_annual' => 10.00,
                'is_active' => 1,
                'max_alerts' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            $testPlan = $planModel->where('slug', 'test_1euro')->first();
        }

        $data = [
            'title' => 'Stripe 1€ Test',
            'plan' => $testPlan
        ];

        return view('admin/stripe_test', $data);
    }

    public function checkout()
    {
        if (!session('is_admin') && session('user_id') != 1) {
            return redirect()->to(site_url('dashboard'));
        }

        $userId = (int) session('user_id');
        $userModel = new UserModel();
        $user = $userModel->find($userId);

        $planModel = new ApiPlanModel();
        $dbPlan = $planModel->where('slug', 'test_1euro')->first();

        if (!$dbPlan) {
            return redirect()->back()->with('error', 'El plan de prueba no existe.');
        }

        $secretKey = getenv('STRIPE_SECRET_KEY');
        if (!$secretKey) {
            return redirect()->back()->with('error', 'Stripe no está configurado (STRIPE_SECRET_KEY).');
        }

        Stripe::setApiKey($secretKey);

        try {
            $successUrl = site_url('billing/success') . '?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = site_url('billing/cancel');

            $amount = (float) $dbPlan->price_monthly;

            $lineItem = [
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int) ($amount * 100), // En céntimos
                    'recurring' => [
                        'interval' => 'month'
                    ],
                    'product_data' => [
                        'name' => 'Suscripción Test 1 Euro',
                        'description' => 'Prueba de pago real en producción por 1 euro'
                    ]
                ]
            ];

            $sessionParams = [
                'mode' => 'subscription',
                'line_items' => [$lineItem],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'client_reference_id' => (string) $userId,
                'metadata' => [
                    'user_id' => (string) $userId,
                    'plan' => 'test_1euro',
                    'period' => 'monthly',
                ],
            ];

            if (!empty($user->stripe_customer_id)) {
                $sessionParams['customer'] = $user->stripe_customer_id;
            } else {
                $sessionParams['customer_email'] = $user->email ?? null;
            }

            $session = Session::create($sessionParams);

            return redirect()->to($session->url);

        } catch (\Throwable $e) {
            log_message('error', '[StripeTest] ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al iniciar pago en Stripe: ' . $e->getMessage());
        }
    }
}
