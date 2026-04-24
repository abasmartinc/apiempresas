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

        if (!in_array($plan, ['pro', 'business', 'radar'], true)) {
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

        if ($pm === 'paypal' && getenv('BILLING_MODE') !== 'simulator') {
            return $this->startPaypalSubscription($userId, $plan, $period);
        }

        if (getenv('BILLING_MODE') === 'simulator') {
            $simulator = new \App\Libraries\BillingSimulator();
            
            // Set context for simulator too if it's an excel download
            if ($period === 'single') {
                $prov = $postData['provincia'] ?? 'España';
                $sect = $postData['sector'] ?? 'General';
                $per  = $postData['period_radar'] ?? '30days';
                
                $radar = new \App\Controllers\RadarController();
                $radarData = $radar->getRadarData($prov, $sect, $per, 1);
                $count = $radarData['total_context_count'] ?? 0;

                session()->set('checkout_context', [
                    'type'        => 'excel',
                    'sector'      => $sect,
                    'cnae'        => $postData['cnae'] ?? '',
                    'provincia'   => $prov,
                    'period'      => $per,
                    'total_count' => $count
                ]);
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
        // Check for CURL extension (required by Stripe SDK)
        if (!extension_loaded('curl')) {
            return redirect()->back()->with('error', 'El servidor no tiene habilitada la extensión CURL, necesaria para procesar pagos con Stripe. Por favor, habilítala en Laragon (Menú -> PHP -> Extensions -> curl) y reinicia los servicios.');
        }

        // 1) Obtener Precio de la Base de Datos (ApiPlans) si no es un pago único o plan radar hardcoded
        $planModel = new \App\Models\ApiPlanModel();
        $dbPlan = null;
        if ($period !== 'single' && $plan !== 'radar') {
            $dbPlan = $planModel->where('slug', $plan)->first();
            if (!$dbPlan) {
                return redirect()->back()->with('error', 'El plan seleccionado no existe.');
            }
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

            // Tax Rate for IVA
            $taxRateId = getenv('STRIPE_TAX_RATE_ID');

        if ($period === 'single' || ($plan === 'radar' && $period === 'single')) {
            // Recalculate count for security to determine price
            $prov = $postData['provincia'] ?? 'España';
            $sect = $postData['sector'] ?? '';
            $cnae = $postData['cnae'] ?? '';
            $per  = $postData['period_radar'] ?? '30days';

            if ($cnae !== '') {
                $db      = \Config\Database::connect();
                $builder = $db->table('companies');
                $builder->where('cnae_code LIKE', $cnae . '%');
                if ($prov && strtolower($prov) !== 'españa') {
                    if (strtolower($prov) === 'alicante') {
                        $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
                    } else {
                        $builder->where('registro_mercantil', $prov);
                    }
                }
                $count = $builder->countAllResults();
            } else {
                $radar = new \App\Controllers\RadarController();
                $radarData = $radar->getRadarData($prov, $sect, $per, 1);
                $count = $radarData['total_context_count'] ?? 0;
            }

            // Dynamic Pricing based on scale
            $pricing = calculate_radar_price($count);
            $amount = $pricing['base_price'];

            // Guardar contexto para la página de éxito
            session()->set('checkout_context', [
                'type'        => 'excel',
                'sector'      => $sect,
                'cnae'        => $cnae,
                'provincia'   => $prov,
                'period'      => $per,
                'total_count' => $count
            ]);
            
            $lineItem = [
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int)($amount * 100),
                    'product_data' => [
                        'name' => 'Descarga Listado Radar B2B (' . $count . ' empresas)',
                        'description' => 'Listado completo de nuevas empresas constituidas.'
                    ]
                ]
            ];

            if ($taxRateId) {
                $lineItem['tax_rates'] = [$taxRateId];
            }

            $sessionParams = [
                'mode' => 'payment',
                'line_items' => [$lineItem],
                'success_url' => $successUrl,
                'cancel_url' => $cancelUrl,
                'invoice_creation' => [
                    'enabled' => true,
                    'invoice_data' => [
                        'metadata' => [
                            'user_id' => (string) $userId,
                            'plan' => 'radar_single',
                            'period' => 'single',
                        ]
                    ]
                ],
                'metadata' => [
                    'user_id' => (string) $userId,
                    'plan' => 'radar_single',
                    'period' => 'single',
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

            $lineItem = [
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int)($amount * 100),
                    'recurring' => [
                        'interval' => ($period === 'annual' ? 'year' : 'month')
                    ],
                    'product_data' => [
                        'name' => 'Suscripción ' . $planName,
                        'description' => $planDesc
                    ]
                ]
            ];

            if ($taxRateId) {
                $lineItem['tax_rates'] = [$taxRateId];
            }

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
        }

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
        if ($province) $params['provincia'] = $province;
        if ($sector) $params['sector'] = $sector;
        if ($period) $params['period'] = $period;
        if ($cnae) $params['cnae'] = $cnae;
        
        $queryString = $params ? '?' . http_build_query($params) : '';
        return redirect()->to(site_url('checkout/radar-export' . $queryString));
    }

    /**
     * GET /checkout/radar-export
     * Show a pre-checkout summary to build trust
     */
    public function order_summary()
    {
        // NO MANDATORY LOGIN HERE - Allow user to see value first
        $province = $this->request->getGet('provincia') ?? 'España';
        $sector   = $this->request->getGet('sector') ?? '';
        $period   = $this->request->getGet('period') ?? '';
        $cnae     = $this->request->getGet('cnae') ?? '';
        $type     = $this->request->getGet('type') ?? 'single'; // 'single' or 'subscription'

        $count = 0;
        $price = 0;
        $tax   = 0;

        if ($type === 'subscription') {
            $price = 79.00;
            $tax   = $price * 0.21;
            // No count needed for subscription intro usually, but we can show "Total Radar"
            $db      = \Config\Database::connect();
            $count   = $db->table('companies')->countAllResults();
        } else {
            // Si viene de combined.php (cnae code), contar directamente sin filtro de fecha
            if ($cnae !== '') {
                $db      = \Config\Database::connect();
                $builder = $db->table('companies');
                $builder->where('cnae_code LIKE', $cnae . '%');
                if ($province && strtolower($province) !== 'españa') {
                    if (strtolower($province) === 'alicante') {
                        $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant']);
                    } else {
                        $builder->where('registro_mercantil', $province);
                    }
                }
                $count = $builder->countAllResults();
            } else {
                // Radar mode: usar getRadarData con filtro de fecha
                $radar = new \App\Controllers\RadarController();
                $radarData = $radar->getRadarData($province, $sector, $period, 1);
                $count = $radarData['total_context_count'] ?? 0;
            }

            // Dynamic Pricing based on scale
            $pricing = calculate_radar_price($count);
            $price   = $pricing['base_price'];
            $tax     = $pricing['tax'];
        }

        $data = [
            'type'        => $type,
            'province'    => $province,
            'sector'      => $sector,
            'cnae'        => $cnae,
            'period'      => $period,
            'price'       => $price,
            'tax'         => $tax,
            'total_count' => $count
        ];

        return view('billing/order_summary', $data);
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

        $checkoutData = session('checkout_context') ?? [];
        $lastInfo = session('last_purchase_info') ?? [];

        // 1. Excel Single Purchase Flow (Check context or last info)
        if (($checkoutData['type'] ?? '') === 'excel' || (!empty($lastInfo) && empty($subscription))) {
            
            if (($checkoutData['type'] ?? '') === 'excel') {
                $exportParams = [
                    'sector'    => $checkoutData['sector']   ?? 'General',
                    'provincia' => $checkoutData['provincia'] ?? 'España',
                    'period'    => $checkoutData['period']    ?? '30days',
                ];
                $totalCount = $checkoutData['total_count'] ?? 0;

                // Guardamos info de la última compra para persistencia en refresh
                $lastInfo = [
                    'total_count'   => $totalCount,
                    'export_params' => $exportParams,
                    'cnae'          => $checkoutData['cnae'] ?? ''
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
            $downloadUrl = site_url('billing/export-excel?' . http_build_query($exportParams));
            
            $user = $this->userModel->find($userId);

            $data = [
                'download_url'  => $downloadUrl,
                'order_ref'     => 'EXC-' . date('Ymd') . '-' . rand(1000, 9999),
                'total_count'   => $lastInfo['total_count'] ?? 0,
                'export_params' => $exportParams,
                'user_email'    => $user->email ?? session('email') ?? ''
            ];

            return view('billing/success_single', $data);
        }

        // 2. Specific View for Full Radar Plan (Only if we haven't just cleared an Excel context)
        if ($subscription && ($subscription->plan_slug ?? '') === 'radar' && ($subscription->status ?? '') === 'active' && empty($checkoutData)) {
             $data = [
                 'order_ref' => 'SUB-' . str_pad($subscription->id ?? '0', 6, '0', STR_PAD_LEFT),
             ];
             return view('billing/success_radar', $data);
        }

        // 3. API Subscription Success (Pro/Business)
        if ($subscription) {
            $data = [
                'plan_name' => $subscription->plan_name ?? 'Pro',
                'base_price' => $subscription->price_monthly ?? '19',
                'period_name' => 'Mensual',
                'payment_method' => 'Tarjeta (Stripe)',
                'order_ref' => 'SUB-' . str_pad($subscription->id ?? '0', 6, '0', STR_PAD_LEFT),
            ];
            return view('purchase_success', $data);
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

        // Debugging: Log what we are looking for
        log_message('info', "Attempting to download invoice $id for user $userId");

        $invoice = $invoiceModel->find($id);

        if (!$invoice) {
            return redirect()->back()->with('error', 'Factura no encontrada.');
        }

        // Fix: Castear a int para evitar error de "43" !== 43
        if ((int)$invoice->user_id !== $userId) {
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
            if ($this->request->isAJAX() || $this->request->getPost('ajax')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'Sesión expirada. Por favor, inicia sesión.']);
            }
            return redirect()->to(site_url('enter'));
        }

        $userId = (int) session('user_id');
        $plan = $this->UsersuscriptionsModel->getActivePlanByUserId($userId);

        if (!$plan) {
            if ($this->request->isAJAX() || $this->request->getPost('ajax')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No tienes ninguna suscripción activa para cancelar.']);
            }
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
