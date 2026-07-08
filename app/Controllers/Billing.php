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
    protected $stripeService;
    protected $billingService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->ApikeysModel = new ApikeysModel();
        $this->UsersuscriptionsModel = new UsersuscriptionsModel();
        $this->ApiRequestsModel = new ApiRequestsModel();
        $this->stripeService = new \App\Services\StripeService();
        $this->billingService = new \App\Services\BillingService();
        helper(['form', 'url', 'pricing']); // Load pricing helper
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
        $data['all_subscriptions'] = $this->UsersuscriptionsModel
            ->select('user_subscriptions.*, api_plans.name as plan_name')
            ->join('api_plans', 'api_plans.id = user_subscriptions.plan_id')
            ->where('user_subscriptions.user_id', $userId)
            ->whereIn('user_subscriptions.status', ['active', 'canceled'])
            ->orderBy('FIELD(user_subscriptions.status, "active", "canceled")', 'ASC', false)
            ->orderBy('user_subscriptions.current_period_end', 'DESC')
            ->findAll();

        $data['api_request_total_month'] = $this->ApiRequestsModel->countRequestsForMonth(date('Y-m'), ['user_id' => $userId]);

        // Para la vista: plan actual en texto, etc.
        $data['current_plan'] = is_array($data['plan']) ? ($data['plan']['plan_name'] ?? null) : (is_object($data['plan']) ? ($data['plan']->plan_name ?? null) : null);
        $data['stripe_customer_id'] = $user->stripe_customer_id ?? null;

        return $this->renderView('billing', $data);
    }

    /**
     * POST /billing/checkout
     * Redirige a Stripe Checkout (subscription) o PayPal approve link
     */
    public function checkout()
    {
        // Enforce preview step / registration if accessed via GET (direct link)
        if ($this->request->getMethod() === 'get') {
            $params = $this->request->getGet();

            if (!session('logged_in')) {
                session()->set('pending_checkout', $params);
                return redirect()->to(site_url('register/quick'));
            }

            $queryString = $params ? '?' . http_build_query($params) : '';
            return redirect()->to(site_url('checkout/radar-export' . $queryString));
        }

        $session = session();
        $lastCheckout = $session->get('last_checkout_time');
        $currentTime = time();

        if ($lastCheckout && ($currentTime - $lastCheckout) < 10) { // 10 seconds limit
            return redirect()->back()->with('error', 'Demasiadas solicitudes. Por favor, espera unos segundos.');
        }
        $session->set('last_checkout_time', $currentTime);

        $postData = $this->request->getVar();
        $period = strtolower(trim((string) ($postData['period'] ?? 'single')));

        if (!session('logged_in')) {
            if ($period === 'single') {
                $userId = 0; // Guest User
            } else {
                // Subscription mode still requires login
                session()->set('pending_checkout', $postData);
                return redirect()->to(site_url('register/quick'));
            }
        } else {
            $userId = (int) session('user_id');
        }

        $user = $userId > 0 ? $this->userModel->find($userId) : null;

        // Check for pending context in session (after quick register) or direct params
        if (empty($postData) || !isset($postData['plan'])) {
            $postData = session('pending_checkout') ?? [];
            session()->remove('pending_checkout');
        }

        $plan = strtolower(trim((string) ($postData['plan'] ?? 'radar')));
        $period = strtolower(trim((string) ($postData['period'] ?? 'single')));
        $pm = strtolower(trim((string) ($postData['payment_method'] ?? 'stripe')));

        if (!in_array($plan, ['pro', 'business', 'radar', 'directory_single', 'subsidies_single', 'contracts_single'], true)) {
            $plan = 'radar'; // default fallback for single downloads
        }
        if (!in_array($period, ['monthly', 'annual', 'single'], true)) {
            $period = 'monthly';
        }
        if (!in_array($pm, ['stripe', 'paypal'], true)) {
            $pm = 'stripe';
        }

        // Datos opcionales de facturación (solo para pre-rellenar)
        $billEmail = trim((string) $this->request->getPost('email')) ?: (string) ($user->email ?? '');
        $billName = trim((string) $this->request->getPost('name'));



        if (env('BILLING_MODE') === 'simulator') {
            $simulator = new \App\Libraries\BillingSimulator();

            // Set context for simulator too if it's an excel download
            if ($period === 'single') {
                $downloadData = $this->billingService->getExcelDownloadContext($plan, $postData, $this->request->getGet() ?? []);
                session()->set('checkout_context', $downloadData['context']);

                // Token para permitir descarga sin login en modo simulador
                session()->set('simulator_excel_token', bin2hex(random_bytes(8)));
            }

            if ($simulator->simulatePayment($userId, $plan, $period)) {
                return redirect()->to(site_url('billing/success'))->with('message', 'Simulación de pago completada con éxito.');
            } else {
                return redirect()->back()->with('error', 'Error en la simulación del pago.');
            }
        }

        return $this->startStripeCheckout($userId, $plan, $period, $billEmail, $billName, $postData);
    }

    private function startStripeCheckout(int $userId, string $plan, string $period, ?string $email, ?string $name, array $postData = [])
    {
        // 1) Obtener Precio de la Base de Datos (ApiPlans) si no es un pago único o plan radar hardcoded
        $planModel = new \App\Models\ApiPlanModel();
        $dbPlan = null;
        if ($period !== 'single' && $plan !== 'radar') {
            $dbPlan = $planModel->where('slug', $plan)->first();
            if (!$dbPlan) {
                return redirect()->back()->with('error', 'El plan seleccionado no existe.');
            }
        }

        try {
            $successUrl = site_url('billing/success') . '?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = site_url('billing/cancel');

            // Si es una compra de radar/excel, enviamos a Stripe una URL de cancelación dinámica
            // para que el usuario vuelva a su resumen de compra en vez de a /billing/cancel (que requiere login)
            if ($plan === 'radar') {
                $exportParams = ['type' => $period === 'single' ? 'single' : 'subscription'];
                if (!empty($postData['provincia']))
                    $exportParams['provincia'] = $postData['provincia'];
                if (!empty($postData['cnae']))
                    $exportParams['cnae'] = $postData['cnae'];
                if (!empty($postData['sector']))
                    $exportParams['sector'] = $postData['sector'];
                if (!empty($postData['period_radar']))
                    $exportParams['period'] = $postData['period_radar'];

                $cancelUrl = site_url('checkout/radar-export?' . http_build_query($exportParams));
            } elseif ($plan === 'directory_single') {
                $cancelParams = [];
                if (!empty($postData['cnae'])) {
                    $cancelParams['cnae'] = $postData['cnae'];
                    $cancelParams['sector'] = $postData['sector'] ?? '';
                } else {
                    $cancelParams['provincia'] = $postData['provincia'] ?? 'España';
                }
                $cancelUrl = site_url('checkout/directory-export?' . http_build_query($cancelParams));
            }



            if ($period === 'single' || ($plan === 'radar' && $period === 'single') || $plan === 'directory_single' || $plan === 'subsidies_single' || $plan === 'contracts_single') {
                $downloadData = $this->billingService->getExcelDownloadContext($plan, $postData, $this->request->getGet() ?? []);
                session()->set('checkout_context', $downloadData['context']);

                $productName = $downloadData['product_name'];
                $productDesc = $downloadData['product_desc'];
                $amount = $downloadData['amount'];
                $metadataPlan = $downloadData['metadata_plan'];

                $lineItem = $this->billingService->buildSinglePaymentLineItem(
                    $productName,
                    $productDesc,
                    $amount,
                    $this->stripeService->getTaxRateId()
                );

                $sessionParams = [
                    'mode' => 'payment',
                    'line_items' => [$lineItem],
                    'success_url' => $successUrl,
                    'cancel_url' => $cancelUrl,
                    'customer_creation' => 'if_required',
                    'invoice_creation' => [
                        'enabled' => true,
                        'invoice_data' => [
                            'metadata' => [
                                'user_id' => (string) $userId,
                                'plan' => $metadataPlan,
                                'period' => 'single',
                                'total_count' => (string) ($downloadData['context']['total_count'] ?? 0),
                                'export_context' => json_encode($downloadData['context'] ?? []),
                            ]
                        ]
                    ],
                    'metadata' => [
                        'user_id' => (string) $userId,
                        'plan' => $metadataPlan,
                        'period' => 'single',
                        'total_count' => (string) ($downloadData['context']['total_count'] ?? 0),
                        'export_context' => json_encode($downloadData['context'] ?? []),
                    ],
                ];

                if ($userId > 0) {
                    $sessionParams['client_reference_id'] = (string) $userId;
                    if ($email) {
                        $sessionParams['customer_email'] = $email;
                    }
                }
            } else {
                // Subscription mode
                // Determinar precio según periodicidad
                $amount = 0.0;
                $planName = $dbPlan->name ?? ucfirst($plan);
                $planDesc = 'Acceso ' . ucfirst($period) . ' al plan ' . ucfirst($plan);

                if ($plan === 'radar') {
                    $amount = 79.00;
                    $planName = 'Radar B2B';
                    $planDesc = 'Acceso ilimitado al Radar de nuevas empresas.';
                } else {
                    if ($period === 'annual') {
                        if (isset($dbPlan->price_annual)) {
                            $amount = (float) $dbPlan->price_annual;
                        } else {
                            return redirect()->back()->with('error', 'El precio anual no está configurado para este plan.');
                        }
                    } else {
                        $amount = (float) $dbPlan->price_monthly;
                    }
                }

                if ($amount <= 0) {
                    return redirect()->back()->with('error', 'El precio del plan no es válido.');
                }

                $lineItem = $this->billingService->buildSubscriptionLineItem(
                    'Suscripción ' . $planName,
                    $planDesc,
                    $amount,
                    ($period === 'annual' ? 'year' : 'month'),
                    $this->stripeService->getTaxRateId()
                );

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
                    'subscription_data' => [
                        'metadata' => [
                            'user_id' => (string) $userId,
                            'plan' => $plan,
                            'period' => $period,
                        ]
                    ],
                ];
            }

            // Buscar si el usuario ya tiene un customer_id en Stripe (sólo si está logueado)
            if ($userId > 0) {
                $user = $this->userModel->find($userId);
                if (!empty($user->stripe_customer_id)) {
                    $sessionParams['customer'] = $user->stripe_customer_id;
                } else {
                    // Solo enviamos email si no hay customer asignado (Stripe fallará si envías ambos)
                    $sessionParams['customer_email'] = $email ?: null;
                }
            } else {
                // Usuario invitado (guest): solo email si se proporcionó
                if ($email) {
                    $sessionParams['customer_email'] = $email;
                }
            }

            $session = $this->stripeService->createCheckoutSession($sessionParams);

            return redirect()->to($session->url);

        } catch (\Throwable $e) {
            log_message('error', '[Billing::startStripeCheckout] ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error de Stripe: ' . $e->getMessage());
        }
    }

    /**
     * POST /billing/checkout_bonus
     * Checkout dinámico para la venta de bonos de créditos prepago.
     */
    public function checkout_bonus()
    {
        $postData = $this->request->getPost();

        if (!session('logged_in')) {
            // Guardar contexto para después del registro rápido
            session()->set('pending_checkout_bonus', $postData);
            return redirect()->to(site_url('register/quick?redirect=billing/checkout_bonus'));
        }


        if (empty($postData) || !isset($postData['credits'])) {
            $postData = session('pending_checkout_bonus') ?? [];
            session()->remove('pending_checkout_bonus');
        }

        $userId = (int) session('user_id');
        $credits = (int) ($postData['credits'] ?? 0);

        if ($credits < 10000) {
            return redirect()->back()->with('error', 'La cantidad mínima de créditos es 10.000.');
        }

        // Lógica de Precios (Igual a la de Javascript por seguridad) delegada a BillingService
        $price = $this->billingService->calculateBonusPrice($credits);

        if (env('BILLING_MODE') === 'simulator') {
            $simulator = new \App\Libraries\BillingSimulator();
            session()->set('checkout_context', [
                'type' => 'custom_bonus',
                'credits' => $credits,
                'price' => $price
            ]);
            if ($simulator->simulateBonusRecharge($userId, $credits)) {
                return redirect()->to(site_url('billing/success'))->with('message', 'Simulación de recarga completada.');
            } else {
                return redirect()->back()->with('error', 'Error en la simulación de recarga.');
            }
        }



        try {
            $successUrl = site_url('billing/success') . '?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = site_url('crear-bono-api');
            $lineItem = $this->billingService->buildSinglePaymentLineItem(
                'Bono ' . number_format($credits, 0, ',', '.') . ' Créditos API',
                'Paquete prepago de créditos universales sin caducidad.',
                $price,
                $this->stripeService->getTaxRateId()
            );

            $metadata = [
                'user_id' => (string) $userId,
                'plan' => 'custom_bonus',
                'credits' => (string) $credits,
            ];

            $sessionParams = [
                'mode' => 'payment',
                'line_items' => [$lineItem],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'customer_creation' => 'if_required',
                'invoice_creation' => [
                    'enabled' => true,
                    'invoice_data' => [
                        'metadata' => $metadata
                    ]
                ],
                'metadata' => $metadata,
                'client_reference_id' => (string) $userId,
            ];

            $user = clone $this->userModel; // Use clone or fresh model just to be safe
            $user_row = clone $this->userModel->find($userId);
            if (!empty($user_row->stripe_customer_id)) {
                $sessionParams['customer'] = $user_row->stripe_customer_id;
            } else {
                $sessionParams['customer_email'] = $user_row->email ?? null;
            }

            // Guardar contexto para la success page
            session()->set('checkout_context', [
                'type' => 'custom_bonus',
                'credits' => $credits,
                'price' => $price
            ]);

            $session = $this->stripeService->createCheckoutSession($sessionParams);

            return redirect()->to($session->url);

        } catch (\Throwable $e) {
            log_message('error', '[Billing::checkout_bonus] ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error de Stripe: ' . $e->getMessage());
        }
    }

    /**
     * GET /billing/single_checkout
     * Starts a one-time Stripe payment session directly via GET (e.g. from SEO pages)
     */
    public function single_checkout()
    {
        // Now redirects to the order summary for better conversion/trust
        $province = $this->request->getGet('provincia') ?? '';
        $sector = $this->request->getGet('sector') ?? '';
        $period = $this->request->getGet('period') ?? '';
        $cnae = $this->request->getGet('cnae') ?? '';

        $params = [];
        if ($province)
            $params['provincia'] = $province;
        if ($sector)
            $params['sector'] = $sector;
        if ($period)
            $params['period'] = $period;
        if ($cnae)
            $params['cnae'] = $cnae;

        $queryString = $params ? '?' . http_build_query($params) : '';
        return redirect()->to(site_url('checkout/radar-export' . $queryString));
    }

    /**
     * GET /billing/directory_checkout
     * Entry point for the historical directory export purchase flow.
     * Redirects to the order summary page (does NOT use the radar flow).
     */
    public function directory_checkout()
    {
        $province = $this->request->getGet('provincia') ?? 'España';
        $cnae = $this->request->getGet('cnae') ?? '';
        $cnae_text = $this->request->getGet('cnae_text') ?? '';
        $sector = $this->request->getGet('sector') ?? '';
        $estado = $this->request->getGet('estado') ?? '';
        $has_phone = $this->request->getGet('has_phone') ?? '';

        $params = [];
        if ($cnae !== '') {
            $params['cnae'] = $cnae;
            $params['sector'] = $sector;
            $params['provincia'] = $province;
        } elseif ($cnae_text !== '') {
            $params['cnae_text'] = $cnae_text;
            $params['sector'] = $sector;
            $params['provincia'] = $province;
        } else {
            $params['provincia'] = $province;
        }
        if ($estado !== '') {
            $params['estado'] = $estado;
        }
        if ($has_phone !== '') {
            $params['has_phone'] = $has_phone;
        }
        $date_min = $this->request->getGet('date_min') ?? '';
        $date_max = $this->request->getGet('date_max') ?? '';
        if ($date_min !== '')
            $params['date_min'] = $date_min;
        if ($date_max !== '')
            $params['date_max'] = $date_max;

        return redirect()->to(site_url('checkout/directory-export?' . http_build_query($params)));
    }

    /**
     * GET /checkout/directory-export
     * Pre-checkout summary for historical province directory downloads.
     * Uses the dynamic pricing: 9€ + (totalCompanies / 1000) * 0.50€
     */
    public function directory_order_summary()
    {
        $province = $this->request->getGet('provincia') ?? 'España';
        $cnae = $this->request->getGet('cnae') ?? '';
        $cnae_text = $this->request->getGet('cnae_text') ?? '';
        $sector = $this->request->getGet('sector') ?? '';
        $estado = $this->request->getGet('estado') ?? '';
        $has_phone = $this->request->getGet('has_phone') ?? '';

        $params = $this->request->getGet();
        $totalCount = $this->billingService->countDirectoryCompanies($params);

        if ($cnae !== '') {
            $displayName = $sector ? ($sector . ' en ' . $province) : ("CNAE {$cnae} en {$province}");
        } elseif ($cnae_text !== '') {
            $titleName = $sector ?: $cnae_text;
            $displayName = $titleName . ' en ' . $province;
        } else {
            $displayName = $province;
        }
        
        if (strtolower($province) === 'españa' && $cnae !== '') {
            $displayName = $sector ?: "CNAE {$cnae}";
        }

        // Dynamic pricing calculada via BillingService
        $price = $this->billingService->calculateDirectoryPrice($totalCount);
        $tax = round($price * 0.21, 2);

        return $this->renderView('billing/directory_order_summary', [
            'province' => $province,
            'display_name' => $displayName,
            'total_count' => $totalCount,
            'price' => $price,
            'tax' => $tax,
            'cnae' => $cnae,
            'cnae_text' => $cnae_text,
            'sector' => $sector,
            'estado' => $estado,
            'has_phone' => $has_phone
        ]);
    }

    public function subsidies_checkout()
    {
        $params = $this->request->getGet();
        return redirect()->to(site_url('checkout/subsidies-export?' . http_build_query($params)));
    }

    public function subsidies_order_summary()
    {
        $params = $this->request->getGet();
        $convocatoria = $params['convocatoria'] ?? '';
        $year = $params['year'] ?? '';

        $totalCount = $this->billingService->countSubsidies($params);
        $price = $this->billingService->calculatePublicFundsPrice($totalCount);
        $tax = round($price * 0.21, 2);

        $displayName = 'Subvenciones';
        if ($convocatoria) $displayName .= ' - ' . ucfirst(str_replace('-', ' ', $convocatoria));
        if ($year) $displayName .= ' (' . $year . ')';

        return $this->renderView('billing/public_funds_order_summary', [
            'display_name' => $displayName,
            'total_count' => $totalCount,
            'price' => $price,
            'tax' => $tax,
            'type' => 'subsidies',
            'convocatoria' => $convocatoria,
            'year' => $year
        ]);
    }

    public function contracts_checkout()
    {
        $params = $this->request->getGet();
        return redirect()->to(site_url('checkout/contracts-export?' . http_build_query($params)));
    }

    public function contracts_order_summary()
    {
        $params = $this->request->getGet();
        $year = $params['year'] ?? '';
        $organo = $params['organo'] ?? '';

        $totalCount = $this->billingService->countContracts($params);
        $price = $this->billingService->calculatePublicFundsPrice($totalCount);
        $tax = round($price * 0.21, 2);

        $displayName = 'Licitaciones Públicas';
        if ($organo) $displayName .= ' - ' . ucfirst(str_replace('-', ' ', $organo));
        if ($year) $displayName .= ' (' . $year . ')';

        return $this->renderView('billing/public_funds_order_summary', [
            'display_name' => $displayName,
            'total_count' => $totalCount,
            'price' => $price,
            'tax' => $tax,
            'type' => 'contracts',
            'year' => $year,
            'organo' => $organo
        ]);
    }

    /**
     * GET /checkout/radar-export
     * Show a pre-checkout summary to build trust
     */
    public function order_summary()
    {
        // NO MANDATORY LOGIN HERE - Allow user to see value first
        $province = $this->request->getGet('provincia') ?? 'España';
        $sector = $this->request->getGet('sector') ?? '';
        $period = $this->request->getGet('period') ?? '';
        $cnae = $this->request->getGet('cnae') ?? '';
        $type = $this->request->getGet('type') ?? 'single'; // 'single' or 'subscription'

        $count = 0;
        $price = 0;
        $tax = 0;

        if ($type === 'subscription') {
            $db = \Config\Database::connect();
            $planRow = $db->table('api_plans')->where('slug', 'radar')->get()->getRow();
            $price = $planRow ? (float) $planRow->price_monthly : 49.00;
            $tax = $price * 0.21;
            // No count needed for subscription intro usually, but we can show "Total Radar"
            $count = $db->table('companies')->countAllResults();
        } else {
            // Si viene de combined.php (cnae code), contar directamente sin filtro de fecha
            if ($cnae !== '') {
                $count = $this->billingService->countRadarCompanies([
                    'provincia' => $province,
                    'cnae' => $cnae
                ]);
            } else {
                // Radar mode: usar getRadarData con filtro de fecha
                $radar = new \App\Controllers\RadarController();
                $radarData = $radar->getRadarData($province, $sector, $period, 1);
                $count = $radarData['total_context_count'] ?? 0;
            }

            // Dynamic Pricing based on scale
            $pricing = calculate_radar_price($count);
            $price = $pricing['base_price'];
            $tax = $pricing['tax'];
        }

        $data = [
            'type' => $type,
            'province' => $province,
            'sector' => $sector,
            'cnae' => $cnae,
            'period' => $period,
            'price' => $price,
            'tax' => $tax,
            'total_count' => $count
        ];

        return $this->renderView('billing/order_summary', $data);
    }

    /**
     * GET /billing/success
     * (Pantalla de éxito). Estado real mejor por webhook Stripe.
     */
    public function success()
    {
        // Permitir acceso sin login si viene del simulador con contexto de excel en sesión
        $hasSimulatorContext = session('checkout_context') !== null || session('simulator_excel_token') !== null;
        if (!session('logged_in') && !$hasSimulatorContext) {
            return redirect()->to(site_url('enter'))->with('error', 'Debes iniciar sesión para continuar.');
        }

        $userId = (int) session('user_id');

        // Fetch user's active subscription
        $subscription = $this->UsersuscriptionsModel->getActivePlanByUserId($userId);

        $checkoutData = session('checkout_context') ?? [];
        $lastInfo = session('last_purchase_info') ?? [];

        // 1.5 Custom Bonus Success
        if (($checkoutData['type'] ?? '') === 'custom_bonus') {
            $data = [
                'credits' => $checkoutData['credits'] ?? 0,
                'price' => $checkoutData['price'] ?? 0,
                'order_ref' => 'BONUS-' . date('Ymd') . '-' . rand(1000, 9999),
            ];
            session()->remove('checkout_context');
            return $this->renderView('billing/success_bonus', $data);
        }

        // 1. Excel Single Purchase Flow (Check context or last info)
        $validExcelTypes = ['excel', 'directory_excel', 'subsidies_excel', 'contracts_excel'];
        if (in_array($checkoutData['type'] ?? '', $validExcelTypes) || (!empty($lastInfo) && empty($subscription))) {

            $isDir = false;
            if (($checkoutData['type'] ?? '') === 'directory_excel' || ($lastInfo['export_params']['is_historical'] ?? '0') === '1') {
                $isDir = true;
            }

            if (in_array($checkoutData['type'] ?? '', $validExcelTypes)) {
                $exportParams = [];
                if (($checkoutData['type'] ?? '') === 'subsidies_excel') {
                    $exportParams = ['convocatoria' => $checkoutData['convocatoria'] ?? '', 'year' => $checkoutData['year'] ?? ''];
                } elseif (($checkoutData['type'] ?? '') === 'contracts_excel') {
                    $exportParams = ['year' => $checkoutData['year'] ?? '', 'organo' => $checkoutData['organo'] ?? ''];
                } else {
                    $exportParams = [
                        'sector' => $checkoutData['sector'] ?? 'General',
                        'provincia' => $checkoutData['provincia'] ?? 'España',
                        'period' => $isDir ? 'general' : ($checkoutData['period'] ?? '30days'),
                        'is_historical' => $isDir ? '1' : '0'
                    ];
                }
                if (!empty($checkoutData['cnae_text'])) {
                    $exportParams['cnae_text'] = $checkoutData['cnae_text'];
                }
                if (!empty($checkoutData['estado'])) {
                    $exportParams['estado'] = $checkoutData['estado'];
                }
                $totalCount = $checkoutData['total_count'] ?? 0;

                // Guardamos info de la última compra para persistencia en refresh
                $lastInfo = [
                    'total_count' => $totalCount,
                    'export_params' => $exportParams,
                    'cnae' => $checkoutData['cnae'] ?? '',
                    'type' => $checkoutData['type'] ?? ''
                ];
                session()->set('last_purchase_info', $lastInfo);
                session()->set('just_bought_excel', true);

                // Limpiamos el contexto tras la compra (pero mantenemos last_purchase_info para refresh)
                session()->remove('checkout_context');
            }

            $exportParams = $lastInfo['export_params'] ?? [];
            if (!empty($lastInfo['cnae'])) {
                $exportParams['cnae'] = $lastInfo['cnae'];
            }

            // Retroactive fix: If we detected it's a directory download but exportParams is missing the flag
            if ($isDir && (!isset($exportParams['is_historical']) || $exportParams['is_historical'] !== '1')) {
                $exportParams['is_historical'] = '1';
                $exportParams['period'] = 'general';
                $lastInfo['export_params'] = $exportParams;
                session()->set('last_purchase_info', $lastInfo);
            }

            $downloadUrl = site_url('billing/export-excel?' . http_build_query($exportParams));
            if (($lastInfo['type'] ?? '') === 'subsidies_excel') {
                $downloadUrl = site_url('billing/export-subsidies?' . http_build_query($exportParams));
            } elseif (($lastInfo['type'] ?? '') === 'contracts_excel') {
                $downloadUrl = site_url('billing/export-contracts?' . http_build_query($exportParams));
            }

            $user = $userId > 0 ? $this->userModel->find($userId) : null;

            $data = [
                'download_url' => $downloadUrl,
                'order_ref' => 'EXC-' . date('Ymd') . '-' . rand(1000, 9999),
                'total_count' => $lastInfo['total_count'] ?? 0,
                'export_params' => $exportParams,
                'user_email' => $user->email ?? session('email') ?? '',
                'export_type' => $lastInfo['type'] ?? ''
            ];

            if ($isDir ?? false) {
                return $this->renderView('billing/success_directory', $data);
            }
            return $this->renderView('billing/success_single', $data);
        }

        // 2. Specific View for Full Radar Plan (Only if we haven't just cleared an Excel context)
        if ($subscription && ($subscription->plan_slug ?? '') === 'radar' && ($subscription->status ?? '') === 'active' && empty($checkoutData)) {
            $data = [
                'order_ref' => 'SUB-' . str_pad($subscription->id ?? '0', 6, '0', STR_PAD_LEFT),
            ];
            return $this->renderView('billing/success_radar', $data);
        }

        // 3. API Subscription Success (Pro/Business)
        if ($subscription && strtolower($subscription->plan_slug ?? '') !== 'free' && (float) ($subscription->price_monthly ?? 0) > 0) {
            $data = [
                'plan_name' => $subscription->plan_name ?? 'Pro',
                'base_price' => $subscription->price_monthly ?? '19',
                'period_name' => 'Mensual',
                'payment_method' => 'Tarjeta (Stripe)',
                'order_ref' => 'SUB-' . str_pad($subscription->id ?? '0', 6, '0', STR_PAD_LEFT),
            ];
            return $this->renderView('purchase_success', $data);
        }

        return redirect()->to(site_url('dashboard'));
    }

    public function cancel()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }
        // puedes crear una vista billing_cancel si quieres
        return redirect()->to(site_url('billing'))->with('info', 'Has cancelado el proceso de pago.');
    }



    public function purchase_success()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }
        return $this->renderView('purchase_success');
    }

    public function billing_manage()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('dashboard'));
        }
        return $this->renderView('billing_manage');
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

        return $this->renderView('billing/invoices', $data);
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

        // Debugging: Log what we are looking for
        log_message('info', "Attempting to download invoice $id for user $userId");

        $invoice = $invoiceModel->find($id);

        if (!$invoice) {
            return redirect()->back()->with('error', 'Factura no encontrada.');
        }

        // Fix: Castear a int para evitar error de "43" !== 43
        if ((int) $invoice->user_id !== $userId) {
            return redirect()->back()->with('error', 'No tienes permiso para ver esta factura.');
        }

        if (empty($invoice->pdf_path)) {
            return redirect()->back()->with('error', 'El archivo PDF no está disponible.');
        }

        // Limpiamos 'writable/' del inicio por si acaso, para usar WRITEPATH que es más seguro
        $relativePath = preg_replace('#^writable/#', '', $invoice->pdf_path);
        $fullPath = WRITEPATH . $relativePath;

        if (!file_exists($fullPath)) {
            // Intento alternativo con ROOTPATH por compatibilidad
            $altPath = ROOTPATH . $invoice->pdf_path;
            if (file_exists($altPath)) {
                $fullPath = $altPath;
            } else {
                log_message('error', "[Billing] Invoice missing. Checked: $fullPath AND $altPath");
                return redirect()->back()->with('error', 'El archivo no está en el servidor. (Err: ' . basename($fullPath) . ' not found)');
            }
        }

        return $this->response->download($fullPath, null)->setFileName($invoice->invoice_number . '.pdf');
    }

    /**
     * Rotar la API Key del usuario
     */
    public function rotate_key()
    {
        if (!session('logged_in')) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Sesión expirada. Por favor, inicia sesión.'
                ])->setStatusCode(401);
            }
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

        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'API Key regenerada con éxito.',
                'api_key' => $newKey
            ]);
        }

        return redirect()->to(site_url('dashboard'))->with('message', 'Tu API Key ha sido rotada con éxito. Recuerda actualizar tus aplicaciones.');
    }

    /**
     * Cancelar suscripción activa
     */
    public function cancel_subscription()
    {
        if (!session('logged_in')) {
            if ($this->request->isAJAX() || $this->request->getPost('ajax')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Sesión expirada. Por favor, inicia sesión.']);
            }
            return redirect()->to(site_url('enter'));
        }

        $userId = (int) session('user_id');
        $specificSubId = $this->request->getPost('sub_id');

        if ($specificSubId) {
            $plan = $this->UsersuscriptionsModel->where('user_id', $userId)->find($specificSubId);
        } else {
            $plan = $this->UsersuscriptionsModel->getActivePlanByUserId($userId);
        }

        if (!$plan) {
            if ($this->request->isAJAX() || $this->request->getPost('ajax')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No tienes ninguna suscripción activa para cancelar.']);
            }
            return redirect()->back()->with('error', 'No tienes ninguna suscripción activa para cancelar.');
        }

        // 1. Si es Stripe, cancelar en Stripe (al final del periodo)
        if (!empty($plan->stripe_subscription_id)) {
            try {
                $this->stripeService->cancelSubscription($plan->stripe_subscription_id);
                log_message('info', "[Billing::cancel_subscription] Suscripción Stripe marcada para cancelar: {$plan->stripe_subscription_id}");
            } catch (\Stripe\Exception\InvalidRequestException $e) {
                // If the subscription doesn't exist in Stripe (e.g. test environment resets),
                // we should still cancel it locally so the user isn't permanently stuck.
                $msg = $e->getMessage();
                if (strpos($msg, 'No such subscription') !== false) {
                    log_message('warning', "[Billing::cancel_subscription] Stripe no encontró la suscripción, forzando cancelación local: " . $msg);
                } else {
                    log_message('error', "[Billing::cancel_subscription] Error al cancelar en Stripe: " . $msg);
                    if ($this->request->isAJAX() || $this->request->getPost('ajax')) {
                        return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudo comunicar la cancelación a Stripe: ' . $msg]);
                    }
                    return redirect()->back()->with('error', 'No se pudo comunicar la cancelación a Stripe: ' . $msg);
                }
            } catch (\Exception $e) {
                log_message('error', "[Billing::cancel_subscription] Error al cancelar en Stripe: " . $e->getMessage());
                if ($this->request->isAJAX() || $this->request->getPost('ajax')) {
                    return $this->response->setJSON(['status' => 'error', 'message' => 'No se pudo comunicar la cancelación a Stripe: ' . $e->getMessage()]);
                }
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

        if ($this->request->isAJAX() || $this->request->getPost('ajax')) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Tu suscripción ha sido cancelada. Seguirás teniendo acceso hasta el final del periodo facturado y no se te cobrará de nuevo.'
            ]);
        }

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

        try {
            $session = $this->stripeService->createBillingPortalSession(
                $user->stripe_customer_id,
                site_url('billing')
            );

            return redirect()->to($session->url);
        } catch (\Exception $e) {
            log_message('error', '[Billing::portal] ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo abrir el portal de Stripe: ' . $e->getMessage());
        }
    }

}
