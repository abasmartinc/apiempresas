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

        if (!in_array($plan, ['pro', 'business', 'radar', 'directory_single'], true)) {
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

        if ($pm === 'paypal' && env('BILLING_MODE') !== 'simulator') {
            return $this->startPaypalSubscription($userId, $plan, $period);
        }

        if (env('BILLING_MODE') === 'simulator') {
            $simulator = new \App\Libraries\BillingSimulator();
            
            // Set context for simulator too if it's an excel download
            if ($period === 'single') {
                $prov = $postData['provincia'] ?? 'España';
                
                if ($plan === 'directory_single') {
                    $count = (int) ($postData['total_count'] ?? 0);
                    $cnae = $postData['cnae'] ?? '';
                    $sect = $postData['sector'] ?? '';
                    if ($count <= 0) {
                        $db = \Config\Database::connect();
                        $builder = $db->table('companies');
                        if ($cnae !== '') {
                            $builder->where('cnae_code', $cnae);
                        } else {
                            if (strtolower($prov) !== 'españa') {
                                if (in_array(strtolower($prov), ['alicante', 'alacant', 'alicante/alacant'])) {
                                    $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant', 'ALACANT']);
                                } else {
                                    $builder->where('registro_mercantil', $prov);
                                }
                            }
                        }
                        $count = $builder->countAllResults();
                    }
                    
                    session()->set('checkout_context', [
                        'type'        => 'directory_excel',
                        'provincia'   => $prov,
                        'cnae'        => $cnae,
                        'sector'      => $sect,
                        'total_count' => $count
                    ]);
                } else {
                    $sect = $postData['sector'] ?? '';
                    $cnae = $postData['cnae'] ?? '';
                    // period_radar vacío = flujo histórico CNAE (sin filtro de fecha) -> 'general'
                    $per  = (isset($postData['period_radar']) && $postData['period_radar'] !== '')
                        ? $postData['period_radar']
                        : ($cnae !== '' ? 'general' : '30days');

                    // Contar igual que el flujo real de Stripe: por CNAE si viene, si no por getRadarData
                    if ($cnae !== '') {
                        $db      = \Config\Database::connect();
                        $builder = $db->table('companies');
                        $builder->where('cnae_code LIKE', $cnae . '%');
                        $builder->where('fecha_constitucion IS NOT NULL'); // Consistente con el export
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

                    session()->set('checkout_context', [
                        'type'        => 'excel',
                        'sector'      => $sect,
                        'cnae'        => $cnae,
                        'provincia'   => $prov,
                        'period'      => $per,
                        'total_count' => $count
                    ]);
                }
                
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

        $secretKey = env('STRIPE_SECRET_KEY');
        if (!$secretKey) {
            return redirect()->back()->with('error', 'Stripe no está configurado (STRIPE_SECRET_KEY).');
        }

        // Requiere: composer require stripe/stripe-php
        \Stripe\Stripe::setApiKey($secretKey);

        try {
            $successUrl = site_url('billing/success') . '?session_id={CHECKOUT_SESSION_ID}';
            $cancelUrl = site_url('billing/cancel');

            // Si es una compra de radar/excel, enviamos a Stripe una URL de cancelación dinámica
            // para que el usuario vuelva a su resumen de compra en vez de a /billing/cancel (que requiere login)
            if ($plan === 'radar') {
                $exportParams = ['type' => $period === 'single' ? 'single' : 'subscription'];
                if (!empty($postData['provincia'])) $exportParams['provincia'] = $postData['provincia'];
                if (!empty($postData['cnae'])) $exportParams['cnae'] = $postData['cnae'];
                if (!empty($postData['sector'])) $exportParams['sector'] = $postData['sector'];
                if (!empty($postData['period_radar'])) $exportParams['period'] = $postData['period_radar'];
                
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

            // Tax Rate for IVA
            $taxRateId = env('STRIPE_TAX_RATE_ID');

        if ($period === 'single' || ($plan === 'radar' && $period === 'single') || $plan === 'directory_single') {
            $prov = $postData['provincia'] ?? 'España';
            
            if ($plan === 'directory_single') {
                $count = (int) ($postData['total_count'] ?? 0);
                $amount = (float) ($postData['price'] ?? 0);
                $cnae = $postData['cnae'] ?? '';
                $cnae_text = $postData['cnae_text'] ?? '';
                $sect = $postData['sector'] ?? '';
                $estado = $postData['estado'] ?? '';
                
                // Fallback si no vinieran
                if ($count <= 0 || $amount <= 0) {
                    $db = \Config\Database::connect();
                    $builder = $db->table('companies');
                    if ($estado !== '') {
            $builder->where('estado', $estado);
        }
        if ($has_phone == '1') {
            $builder->groupStart()
                    ->groupStart()->where('phone IS NOT NULL', null, false)->where('phone !=', '')->groupEnd()
                    ->orGroupStart()->where('phone_mobile IS NOT NULL', null, false)->where('phone_mobile !=', '')->groupEnd()
                    ->groupEnd();
        }
        $date_min = $this->request->getGet('date_min') ?? '';
        $date_max = $this->request->getGet('date_max') ?? '';
        if ($date_min !== '') $builder->where('estado_fecha >=', $date_min);
        if ($date_max !== '') $builder->where('estado_fecha <=', $date_max);
                    if ($cnae !== '') {
                        $builder->where('cnae_code LIKE', $cnae . '%');
                        if (strtolower($prov) !== 'españa') {
                            if (in_array(strtolower($prov), ['alicante', 'alacant', 'alicante/alacant'])) {
                                $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant', 'ALACANT']);
                            } else {
                                $builder->where('registro_mercantil', $prov);
                            }
                        }
                    } elseif ($cnae_text !== '') {
                        $builder->like('cnae_label', $cnae_text, 'both');
                        if (strtolower($prov) !== 'españa') {
                            if (in_array(strtolower($prov), ['alicante', 'alacant', 'alicante/alacant'])) {
                                $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant', 'ALACANT']);
                            } else {
                                $builder->where('registro_mercantil', $prov);
                            }
                        }
                    } else {
                        if (strtolower($prov) !== 'españa') {
                            if (in_array(strtolower($prov), ['alicante', 'alacant', 'alicante/alacant'])) {
                                $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant', 'ALACANT']);
                            } else {
                                $builder->where('registro_mercantil', $prov);
                            }
                        }
                    }
                    $count = $builder->countAllResults();
                    $amount = round(19 + (($count / 1000) * 1.00), 2);
                }

                session()->set('checkout_context', [
                    'type'        => 'directory_excel',
                    'provincia'   => $prov,
                    'cnae'        => $cnae,
                    'cnae_text'   => $cnae_text,
                    'sector'      => $sect,
                    'estado'      => $estado,
            'has_phone'   => $has_phone,
                    'total_count' => $count
                ]);

                $productName = 'BBDD Histórica ' . $prov . ' (' . number_format($count, 0, ',', '.') . ' empresas)';
                $productDesc = 'Descarga en Excel del listado histórico completo.';
                $metadataPlan = 'directory_single';

            } else {
                // Recalculate count for security to determine price (Radar flow)
                $sect = $postData['sector'] ?? '';
                $cnae = $postData['cnae'] ?? '';
                // period_radar vacío = flujo histórico CNAE (sin filtro de fecha) -> 'general'
                $per  = ($postData['period_radar'] !== '' && isset($postData['period_radar']))
                    ? $postData['period_radar']
                    : ($cnae !== '' ? 'general' : '30days');

                if ($cnae !== '') {
                    $db      = \Config\Database::connect();
                    $builder = $db->table('companies');
                    $builder->where('cnae_code LIKE', $cnae . '%');
                    $builder->where('fecha_constitucion IS NOT NULL'); // Consistente con el export
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
                
                $productName = 'Descarga Listado Radar B2B (' . $count . ' empresas)';
                $productDesc = 'Listado completo de nuevas empresas constituidas.';
                $metadataPlan = 'radar_single';
            }
            
            $lineItem = [
                'quantity' => 1,
                'price_data' => [
                    'currency' => 'eur',
                    'unit_amount' => (int)($amount * 100),
                    'product_data' => [
                        'name' => $productName,
                        'description' => $productDesc
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
                'customer_creation' => 'if_required',
                'invoice_creation' => [
                    'enabled' => true,
                    'invoice_data' => [
                        'metadata' => [
                            'user_id' => (string) $userId,
                            'plan' => $metadataPlan,
                            'period' => 'single',
                        ]
                    ]
                ],
                'metadata' => [
                    'user_id' => (string) $userId,
                    'plan' => $metadataPlan,
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
        if ($date_min !== '') $params['date_min'] = $date_min;
        if ($date_max !== '') $params['date_max'] = $date_max;
        
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

        $db = \Config\Database::connect();
        $builder = $db->table('companies');

        if ($estado !== '') {
            $builder->where('estado', $estado);
        }
        if ($has_phone == '1') {
            $builder->groupStart()
                    ->groupStart()->where('phone IS NOT NULL', null, false)->where('phone !=', '')->groupEnd()
                    ->orGroupStart()->where('phone_mobile IS NOT NULL', null, false)->where('phone_mobile !=', '')->groupEnd()
                    ->groupEnd();
        }
        $date_min = $this->request->getGet('date_min') ?? '';
        $date_max = $this->request->getGet('date_max') ?? '';
        if ($date_min !== '') $builder->where('estado_fecha >=', $date_min);
        if ($date_max !== '') $builder->where('estado_fecha <=', $date_max);

        if ($cnae !== '') {
            $builder->where('cnae_code LIKE', $cnae . '%');
            if (strtolower($province) !== 'españa') {
                if (in_array(strtolower($province), ['alicante', 'alacant', 'alicante/alacant'])) {
                    $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant', 'ALACANT']);
                } else {
                    $builder->where('registro_mercantil', $province);
                }
                $displayName = $sector ? ($sector . ' en ' . $province) : ("CNAE {$cnae} en {$province}");
            } else {
                $displayName = $sector ?: "CNAE {$cnae}";
            }
            $totalCount = $builder->countAllResults();
        } elseif ($cnae_text !== '') {
            $builder->like('cnae_label', $cnae_text, 'both');
            if (strtolower($province) !== 'españa') {
                if (in_array(strtolower($province), ['alicante', 'alacant', 'alicante/alacant'])) {
                    $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant', 'ALACANT']);
                } else {
                    $builder->where('registro_mercantil', $province);
                }
            }
            $totalCount = $builder->countAllResults();
            $titleName = $sector ?: $cnae_text;
            $displayName = $titleName . ' en ' . $province; 
        } else {
            if (strtolower($province) !== 'españa') {
                if (in_array(strtolower($province), ['alicante', 'alacant', 'alicante/alacant'])) {
                    $builder->whereIn('registro_mercantil', ['Alicante', 'Alicante/Alacant', 'ALACANT']);
                } else {
                    $builder->where('registro_mercantil', $province);
                }
            }
            $totalCount = $builder->countAllResults();
            $displayName = $province;
        }

        // Dynamic pricing: 9€ base + 0.50€ per 1,000 records
        $price = round(19 + (($totalCount / 1000) * 1.00), 2);
        $tax   = round($price * 0.21, 2);

        return view('billing/directory_order_summary', [
            'province'    => $province,
            'display_name'=> $displayName,
            'total_count' => $totalCount,
            'price'       => $price,
            'tax'         => $tax,
            'cnae'        => $cnae,
            'cnae_text'   => $cnae_text,
            'sector'      => $sector,
            'estado'      => $estado,
            'has_phone'   => $has_phone
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
        $sector   = $this->request->getGet('sector') ?? '';
        $period   = $this->request->getGet('period') ?? '';
        $cnae     = $this->request->getGet('cnae') ?? '';
        $type     = $this->request->getGet('type') ?? 'single'; // 'single' or 'subscription'

        $count = 0;
        $price = 0;
        $tax   = 0;

        if ($type === 'subscription') {
            $db      = \Config\Database::connect();
            $planRow = $db->table('api_plans')->where('slug', 'radar')->get()->getRow();
            $price   = $planRow ? (float)$planRow->price_monthly : 49.00;
            $tax     = $price * 0.21;
            // No count needed for subscription intro usually, but we can show "Total Radar"
            $count   = $db->table('companies')->countAllResults();
        } else {
            // Si viene de combined.php (cnae code), contar directamente sin filtro de fecha
            if ($cnae !== '') {
                $db      = \Config\Database::connect();
                $builder = $db->table('companies');
                $builder->where('cnae_code LIKE', $cnae . '%');
                $builder->where('fecha_constitucion IS NOT NULL'); // Consistente con el export
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

        // 1. Excel Single Purchase Flow (Check context or last info)
        if (($checkoutData['type'] ?? '') === 'excel' || ($checkoutData['type'] ?? '') === 'directory_excel' || (!empty($lastInfo) && empty($subscription))) {
            
            $isDir = false;
            if (($checkoutData['type'] ?? '') === 'directory_excel' || ($lastInfo['export_params']['is_historical'] ?? '0') === '1') {
                $isDir = true;
            }

            if (($checkoutData['type'] ?? '') === 'excel' || ($checkoutData['type'] ?? '') === 'directory_excel') {
                $exportParams = [
                    'sector'    => $checkoutData['sector']   ?? 'General',
                    'provincia' => $checkoutData['provincia'] ?? 'España',
                    'period'    => $isDir ? 'general' : ($checkoutData['period'] ?? '30days'),
                    'is_historical' => $isDir ? '1' : '0'
                ];
                if (!empty($checkoutData['cnae_text'])) {
                    $exportParams['cnae_text'] = $checkoutData['cnae_text'];
                }
                if (!empty($checkoutData['estado'])) {
                    $exportParams['estado'] = $checkoutData['estado'];
                }
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
            
            // Retroactive fix: If we detected it's a directory download but exportParams is missing the flag
            if ($isDir && (!isset($exportParams['is_historical']) || $exportParams['is_historical'] !== '1')) {
                $exportParams['is_historical'] = '1';
                $exportParams['period'] = 'general';
                $lastInfo['export_params'] = $exportParams;
                session()->set('last_purchase_info', $lastInfo);
            }
            
            $downloadUrl = site_url('billing/export-excel?' . http_build_query($exportParams));
            
            $user = $userId > 0 ? $this->userModel->find($userId) : null;

            $data = [
                'download_url'  => $downloadUrl,
                'order_ref'     => 'EXC-' . date('Ymd') . '-' . rand(1000, 9999),
                'total_count'   => $lastInfo['total_count'] ?? 0,
                'export_params' => $exportParams,
                'user_email'    => $user->email ?? session('email') ?? ''
            ];

            if ($isDir ?? false) {
                return view('billing/success_directory', $data);
            }
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

        $mode = env('PAYPAL_MODE') ?: 'sandbox';
        $base = ($mode === 'live') ? 'https://api-m.paypal.com' : 'https://api-m.sandbox.paypal.com';

        $clientId = env('PAYPAL_CLIENT_ID');
        $secret = env('PAYPAL_SECRET');

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
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status'  => 'error',
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
                'status'  => 'success',
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
                $secretKey = env('STRIPE_SECRET_KEY');
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

        $secretKey = env('STRIPE_SECRET_KEY');
        if (!$secretKey) {
            return redirect()->back()->with('error', 'Stripe no está configurado (STRIPE_SECRET_KEY).');
        }

        \Stripe\Stripe::setApiKey($secretKey);

        try {
            $session = \Stripe\BillingPortal\Session::create([
                'customer'   => $user->stripe_customer_id,
                'return_url' => site_url('billing'),
            ]);

            return redirect()->to($session->url);
        } catch (\Exception $e) {
            log_message('error', '[Billing::portal] ' . $e->getMessage());
            return redirect()->back()->with('error', 'No se pudo abrir el portal de Stripe: ' . $e->getMessage());
        }
    }

}
