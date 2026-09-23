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
        // 'company' trae solvencia(): los importes de los productos de Solvencia
        // salen de Config\Solvencia, no escritos a mano en el punto de cobro.
        helper(['form', 'url', 'pricing', 'company']);
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

        // --- VISTA ESPECÍFICA PARA PERFIL DE RIESGO & SOLVENCIA PRO ---
        $intent = (string)($user->signup_intent ?? '');
        // Siempre ''. La columna no existe en la base de datos; ver la nota de
        // Dashboard::index. El que manda es 'signup_intent'.
        $prefProduct = (string)($user->preferred_product ?? '');
        $hasRiskPlan = false;
        if (!empty($data['plan'])) {
            $pSlug = strtolower(trim((string)($data['plan']->plan_slug ?? '')));
            $pType = strtolower(trim((string)($data['plan']->product_type ?? '')));
            if ($pSlug === 'risk_pro' || $pType === 'risk') {
                $hasRiskPlan = true;
            }
        }
        $isRiskUser = ($intent === 'view_risk_profile' || $prefProduct === 'risk' || session('intended_product') === 'risk' || $hasRiskPlan);
        $viewParam = $this->request->getGet('view');
        $planParam = $this->request->getGet('plan');

        if (($isRiskUser || $viewParam === 'risk' || $planParam === 'risk_pro') && $viewParam !== 'api') {
            return $this->renderRiskBilling($user, $data);
        }

        return $this->renderView('billing', $data);
    }

    /**
     * Renderiza la vista de facturación exclusiva para Solvencia Pro (sin planes de API)
     */
    private function renderRiskBilling($user, array $data)
    {
        $planModel = new \App\Models\ApiPlanModel();
        $riskPlan = $planModel->where('slug', 'risk_pro')->first();
        $data['risk_plan'] = $riskPlan;

        $isSubscribed = false;
        if (!empty($data['plan'])) {
            $pSlug = strtolower(trim((string)($data['plan']->plan_slug ?? '')));
            $pType = strtolower(trim((string)($data['plan']->product_type ?? '')));
            if (($pSlug === 'risk_pro' || $pType === 'risk') && ($data['plan']->status ?? '') === 'active') {
                $isSubscribed = true;
            }
        }
        // $data['plan'] puede ser el plan gratuito de la API que se crea con cada alta,
        // y entonces un suscriptor de Solvencia salía como "no suscrito". La fuente de
        // verdad es la misma que usa el resto de Solvencia.
        if (!$isSubscribed && !empty($user->id)) {
            $isSubscribed = (new \App\Services\CompanyWatchService())->esSuscriptor((int) $user->id);
        }
        $data['is_risk_subscribed'] = $isSubscribed;

        return $this->renderView('risk_profile/billing', $data);
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

            /*
             * VUELTA DEL REGISTRO CON UNA COMPRA DE SOLVENCIA PENDIENTE.
             *
             * Quien pulsaba "Activar Solvencia Pro" sin cuenta se registraba y volvía
             * aquí por GET, y esta rama lo mandaba SIEMPRE a checkout/radar-export:
             * otro producto. Ahora, si hay una compra de Solvencia guardada (y es
             * reciente), se sigue con ella hasta Stripe sin que tenga que volver a
             * pulsar nada. Se guardó en su propio POST, que ya pasó el CSRF.
             */
            $pendiente = session('pending_checkout');
            $esSolvenciaPendiente = is_array($pendiente)
                && in_array($pendiente['plan'] ?? '', ['risk_pro', 'risk_pack_5'], true)
                && (time() - (int) ($pendiente['_ts'] ?? 0)) < 3600;

            if (!$esSolvenciaPendiente) {
                if (is_array($pendiente) && in_array($pendiente['plan'] ?? '', ['risk_pro', 'risk_pack_5'], true)) {
                    session()->remove('pending_checkout');   // caducada
                }
                if (empty($params)) {
                    // Sin nada que reanudar: a la facturación, que ya sabe si es de
                    // Solvencia o de la API. No a la exportación del Radar.
                    return redirect()->to(site_url('billing'));
                }
                $queryString = '?' . http_build_query($params);
                return redirect()->to(site_url('checkout/radar-export' . $queryString));
            }
            // Sigue abajo: sin 'plan' en la petición, se toma de pending_checkout.
        }

        $session = session();
        $lastCheckout = $session->get('last_checkout_time');
        $currentTime = time();
        // Reanudar tras el registro no es un doble clic: con Google el alta dura
        // menos de 10 s y el antirrebote de abajo devolvía un error al volver.
        $reanudando = $this->request->getMethod() === 'get';

        if (!$reanudando && $lastCheckout && ($currentTime - $lastCheckout) < 10) { // 10 seconds limit
            return redirect()->back()->with('error', lang('Messages.flash_4'));
        }
        $session->set('last_checkout_time', $currentTime);

        $postData = $this->request->getVar();
        $period = strtolower(trim((string) ($postData['period'] ?? 'single')));

        if (!session('logged_in')) {
            if ($period === 'single' && ($postData['plan'] ?? '') !== 'risk_pack_5') {
                $userId = 0; // Guest User
            } else {
                // Subscription mode and credit packs require login
                $postData['_ts'] = time();
                session()->set('pending_checkout', $postData);

                // Solvencia: al registro con la intención de riesgo y de vuelta aquí,
                // que reanuda la compra (ver la rama GET). Sin esto el alta se
                // clasificaba como 'api' y acababa en la exportación del Radar.
                if (in_array($postData['plan'] ?? '', ['risk_pro', 'risk_pack_5'], true)) {
                    return redirect()->to(site_url('register/quick') . '?' . http_build_query(array_filter([
                        'intent'   => 'view_risk_profile',
                        'cif'      => (string) ($postData['cif'] ?? ''),
                        'redirect' => 'billing/checkout',
                    ])));
                }

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

        if (!in_array($plan, ['pro', 'business', 'radar', 'risk_pro', 'risk_pack_5', 'copiloto_ventas', 'directory_single', 'subsidies_single', 'contracts_single', 'lookalike_single'], true)) {
            $plan = 'radar'; // default fallback for single downloads
        }
        if (!in_array($period, ['monthly', 'annual', 'single'], true)) {
            $period = 'monthly';
        }
        if (!in_array($pm, ['stripe', 'paypal'], true)) {
            $pm = 'stripe';
        }

        // Nada impedía contratar Solvencia Pro dos veces: la página de pago enseñaba
        // "tu plan está activo" y el formulario debajo, y el panel, la ficha o un correo
        // antiguo también llevan aquí. Una segunda suscripción es un cobro doble y una
        // devolución segura. Quien la tiene (también cancelada con días por delante)
        // gestiona cambios y reactivaciones desde el portal de Stripe.
        if ($plan === 'risk_pro' && $userId > 0
            && (new \App\Services\CompanyWatchService())->esSuscriptor($userId)) {
            session()->remove('pending_checkout');
            return redirect()->to(site_url('billing?view=risk'))->with(
                'error',
                'Ya tienes Solvencia Pro en tu cuenta, así que no te lo volvemos a cobrar. Para pasar a anual, reactivarlo o darte de baja, usa «Gestionar suscripción».'
            );
        }

        // Datos opcionales de facturación (solo para pre-rellenar)
        $billEmail = trim((string) $this->request->getPost('email')) ?: (string) ($user->email ?? '');
        $billName = trim((string) $this->request->getPost('name'));

        if (env('BILLING_MODE') === 'simulator') {
            $simulator = new \App\Libraries\BillingSimulator();

            if ($plan === 'risk_pack_5') {
                session()->set('checkout_context', [
                    'type'       => 'risk_pack_5',
                    'credits'    => 5,
                    'amount'     => 9.90,
                    'target_cif' => $postData['cif'] ?? ''
                ]);
            } elseif ($period === 'single') {
                $downloadData = $this->billingService->getExcelDownloadContext($plan, $postData, $this->request->getGet() ?? []);
                session()->set('checkout_context', $downloadData['context']);

                // Token para permitir descarga sin login en modo simulador
                session()->set('simulator_excel_token', bin2hex(random_bytes(8)));
            }

            if ($simulator->simulatePayment($userId, $plan, $period)) {
                return redirect()->to(site_url('billing/success'))->with('message', lang('Messages.flash_5'));
            } else {
                return redirect()->back()->with('error', lang('Messages.flash_6'));
            }
        }

        // Atribución: de qué CTA salió este checkout (lo traen los formularios del
        // paywall y del upsell en un hidden). Sin esto el tracking sabe quién pulsa
        // pero no de dónde vienen los que acaban pagando.
        $source = trim((string) ($postData['source'] ?? ''));
        session()->set('checkout_source', $source);

        $this->logCheckoutEvent('checkout_started', $source, [
            'plan'   => $plan,
            'period' => $period,
        ], $userId);

        return $this->startStripeCheckout($userId, $plan, $period, $billEmail, $billName, $postData);
    }

    /**
     * URL de la ficha de una empresa a partir de su CIF.
     *
     * La ruta pública es `empresa/{id}-{slug}`; `empresa/{CIF}` no existe y
     * devolvía 404 (el botón "Auditar {CIF}" tras comprar el pack y la vuelta
     * al cancelar el pago). Si no se encuentra, al panel de riesgo.
     *
     * @param bool $verRiesgo añade ?ver-riesgo=1 para que el dictamen se abra solo.
     */
    private function urlFichaPorCif(string $cif, bool $verRiesgo = false): string
    {
        $cif = strtoupper(trim($cif));
        if ($cif !== '') {
            $fila = \Config\Database::connect()->table('companies')
                ->select('id, company_name')
                ->where('cif', $cif)
                ->get(1)->getRow();
            if ($fila) {
                helper('url');
                $url = site_url('empresa/' . (int) $fila->id . '-' . url_title((string) ($fila->company_name ?: 'empresa'), '-', true));
                return $verRiesgo ? $url . '?ver-riesgo=1' : $url;
            }
        }

        return site_url('dashboard?view=risk' . ($cif !== '' ? '&cif=' . rawurlencode($cif) : ''));
    }

    /**
     * Empresas que el usuario ya ha consultado y todavía no vigila, las más
     * recientes primero. Es el primer paso de la página de éxito de Pro: lo que
     * acaba de comprar es la vigilancia, y ya sabemos qué empresas le importan.
     *
     * Los CIF se cruzan en PHP y no con un JOIN: `user_events.trigger_type`,
     * `companies.cif` y `user_company_watch.cif` no comparten collation.
     *
     * @return list<array{cif:string,nombre:string}>
     */
    private function empresasConsultadasSinVigilar(int $userId, int $limite = 25): array
    {
        if ($userId <= 0) {
            return [];
        }

        try {
            $db = \Config\Database::connect();

            $filas = $db->table('user_events')
                ->select('UPPER(TRIM(trigger_type)) AS cif, MAX(created_at) AS ultima', false)
                ->where('user_id', $userId)
                ->whereIn('event_type', ['view_risk_profile', 'purchase_risk_pdf'])
                ->where('trigger_type IS NOT NULL')
                ->where("trigger_type != ''")
                ->groupBy('UPPER(TRIM(trigger_type))', false)
                ->orderBy('ultima', 'DESC')
                ->limit(200)
                ->get()->getResultArray();

            $cifs = array_values(array_unique(array_filter(array_map(
                static fn ($f) => strtoupper(trim((string) $f['cif'])),
                $filas
            ))));
            if (empty($cifs)) {
                return [];
            }

            $vigiladas = [];
            foreach ($db->table('user_company_watch')->select('cif')
                         ->where('user_id', $userId)->where('active', 1)
                         ->get()->getResultArray() as $w) {
                $vigiladas[strtoupper(trim((string) $w['cif']))] = true;
            }

            $pendientes = array_values(array_filter($cifs, static fn ($c) => !isset($vigiladas[$c])));
            if (empty($pendientes)) {
                return [];
            }

            $nombres = [];
            foreach ($db->table('companies')->select('cif, company_name')
                         ->whereIn('cif', array_slice($pendientes, 0, 200))
                         ->get()->getResultArray() as $c) {
                $nombres[strtoupper(trim((string) $c['cif']))] = (string) $c['company_name'];
            }

            helper('company');
            $salida = [];
            foreach ($pendientes as $cif) {
                if (!isset($nombres[$cif])) {
                    continue;   // sin empresa no se puede vigilar
                }
                $salida[] = [
                    'cif'    => $cif,
                    'nombre' => company_display_name($nombres[$cif], $cif),
                ];
                if (count($salida) >= $limite) {
                    break;
                }
            }

            return $salida;
        } catch (\Throwable $e) {
            log_message('error', '[Billing] empresasConsultadasSinVigilar(' . $userId . '): ' . $e->getMessage());
            return [];
        }
    }

    /**
     * ¿Le van a llegar las alertas del BORME? Misma regla que BormeAlertsCommand:
     * alerts_borme = 1, o sin decidir (NULL) y sin baja del marketing.
     */
    private function avisosActivos(object $user): bool
    {
        $pref = $user->alerts_borme ?? null;
        if ($pref !== null) {
            return (int) $pref === 1;
        }

        return (int) ($user->unsuscribe ?? 0) === 0;
    }

    /**
     * Registra un hito del checkout en tracking_events.
     *
     * Vive aquí y no en el cliente JS porque el retorno de Stripe no siempre pasa
     * por una página instrumentada, y porque un evento de compra no debe depender
     * de que el navegador llegue a ejecutar nada.
     */
    private function logCheckoutEvent(string $eventName, string $source, array $meta = [], ?int $userId = null): void
    {
        try {
            (new \App\Models\TrackingEventModel())->insert([
                'event_name'   => $eventName,
                'page'         => 'billing',
                'user_id'      => $userId ?? (int) session('user_id'),
                'session_id'   => substr((string) session_id(), 0, 100),
                'anonymous_id' => '',
                'element'      => substr($source, 0, 255),
                'metadata'     => json_encode($meta),
                'created_at'   => date('Y-m-d H:i:s'),
            ]);
        } catch (\Throwable $e) {
            // La atribución nunca debe tumbar un cobro
            log_message('error', 'logCheckoutEvent(' . $eventName . '): ' . $e->getMessage());
        }
    }

    private function startStripeCheckout(int $userId, string $plan, string $period, ?string $email, ?string $name, array $postData = [])
    {
        // 1) Obtener Precio de la Base de Datos (ApiPlans) si no es un pago único o plan radar hardcoded
        $planModel = new \App\Models\ApiPlanModel();
        $dbPlan = null;
        if ($period !== 'single' && $plan !== 'radar') {
            $dbPlan = $planModel->where('slug', $plan)->first();
            if (!$dbPlan) {
                return redirect()->back()->with('error', lang('Messages.flash_7'));
            }
        }

        try {
            $successUrl = site_url('billing/success') . '?session_id={CHECKOUT_SESSION_ID}';
            
            if (($postData['source'] ?? '') === 'copilot') {
                $successUrl .= '&source=copilot';
            }
            
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
            } elseif ($plan === 'subsidies_single') {
                $cancelParams = [];
                if (!empty($postData['convocatoria'])) {
                    $cancelParams['convocatoria'] = $postData['convocatoria'];
                }
                if (!empty($postData['year'])) {
                    $cancelParams['year'] = $postData['year'];
                }
                $cancelUrl = site_url('checkout/subsidies-export' . ($cancelParams ? '?' . http_build_query($cancelParams) : ''));
            } elseif ($plan === 'contracts_single') {
                $cancelParams = [];
                if (!empty($postData['organo'])) {
                    $cancelParams['organo'] = $postData['organo'];
                }
                if (!empty($postData['year'])) {
                    $cancelParams['year'] = $postData['year'];
                }
                $cancelUrl = site_url('checkout/contracts-export' . ($cancelParams ? '?' . http_build_query($cancelParams) : ''));
            } elseif ($plan === 'lookalike_single') {
                $cancelUrl = site_url('encontrar-empresas-similares');
            } elseif ($plan === 'risk_pack_5') {
                // `empresa/{CIF}` no es una ruta (solo `empresa/{id}-{slug}`): daba 404.
                $cancelUrl = $this->urlFichaPorCif((string) ($postData['cif'] ?? ''));
            }

            if ($plan === 'risk_pack_5') {
                $productName = 'Pack 5 Auditorías de Solvencia & Riesgo';
                // Sin "oficiales": la fuente lo es, la conclusión es nuestra. Y
                // este texto acaba en el recibo de Stripe del cliente.
                $productDesc = '5 auditorías completas de riesgo mercantil, con informe en PDF descargable sin caducidad.';
                // El importe sale de Config\Solvencia, igual que el precio que se
                // anuncia. Estaba escrito a mano aquí.
                $amount = ((int) solvencia('centimos.pack5', 990)) / 100;
                $metadataPlan = 'risk_pack_5';

                session()->set('checkout_context', [
                    'type'       => 'risk_pack_5',
                    'credits'    => 5,
                    'amount'     => $amount,
                    'target_cif' => $postData['cif'] ?? '',
                ]);

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
                    'billing_address_collection' => 'required',
                    'tax_id_collection' => ['enabled' => true],
                    'invoice_creation' => [
                        'enabled' => true,
                        'invoice_data' => [
                            'metadata' => [
                                'user_id' => (string) $userId,
                                'plan' => $metadataPlan,
                                'period' => 'single',
                                'credits' => '5',
                                'target_cif' => (string) ($postData['cif'] ?? ''),
                            ]
                        ]
                    ],
                    'metadata' => [
                        'user_id' => (string) $userId,
                        'plan' => $metadataPlan,
                        'period' => 'single',
                        'credits' => '5',
                        'target_cif' => (string) ($postData['cif'] ?? ''),
                        'source' => (string) ($postData['source'] ?? ''),
                    ],
                ];

                if ($userId > 0) {
                    $sessionParams['client_reference_id'] = (string) $userId;
                    if ($email) {
                        $sessionParams['customer_email'] = $email;
                    }
                }
            } elseif ($period === 'single' || ($plan === 'radar' && $period === 'single') || $plan === 'directory_single' || $plan === 'subsidies_single' || $plan === 'contracts_single' || $plan === 'lookalike_single') {
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
                    'billing_address_collection' => 'required',
                    'tax_id_collection' => ['enabled' => true],
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
                } elseif ($plan === 'risk_pro') {
                    $planName = $dbPlan->name ?? 'Solvencia Pro';
                    // "dictámenes oficiales" se quitó de todas las pantallas: la
                    // fuente (BORME, Registro Mercantil) es oficial, la conclusión
                    // es nuestra. Aquí seguía viva, y este texto es peor que una
                    // vista: es la línea de concepto que Stripe imprime en el
                    // recibo y en la factura que el cliente se guarda.
                    $planDesc = 'Scoring de solvencia y riesgo mercantil, informes en PDF y vigilancia del BORME de tu cartera.';
                    if ($period === 'annual') {
                        $amount = isset($dbPlan->price_annual) ? (float) $dbPlan->price_annual : 290.00;
                    } else {
                        $amount = isset($dbPlan->price_monthly) ? (float) $dbPlan->price_monthly : 29.00;
                    }
                } else {
                    if ($period === 'annual') {
                        if (isset($dbPlan->price_annual)) {
                            $amount = (float) $dbPlan->price_annual;
                        } else {
                            return redirect()->back()->with('error', lang('Messages.flash_8'));
                        }
                    } else {
                        $amount = (float) $dbPlan->price_monthly;
                    }
                }

                if ($amount <= 0) {
                    return redirect()->back()->with('error', lang('Messages.flash_9'));
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
                    'billing_address_collection' => 'required',
                    'tax_id_collection' => ['enabled' => true],
                    'metadata' => [
                        'user_id' => (string) $userId,
                        'plan' => $plan,
                        'period' => $period,
                        'source' => (string) ($postData['source'] ?? ''),
                    ],
                    'subscription_data' => [
                        'metadata' => [
                            'user_id' => (string) $userId,
                            'plan' => $plan,
                            'period' => $period,
                            'source' => (string) ($postData['source'] ?? ''),
                        ]
                    ],
                ];
            }

            // Buscar si el usuario ya tiene un customer_id en Stripe (sólo si está logueado)
            if ($userId > 0) {
                $user = $this->userModel->find($userId);
                if (!empty($user->stripe_customer_id)) {
                    $sessionParams['customer'] = $user->stripe_customer_id;
                    unset($sessionParams['customer_creation']); // Evitar error 400 en pagos únicos recurrentes
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

            // Sin campo de código promocional a propósito: un campo vacío en el pago
            // hace que el comprador se vaya a buscar códigos. Si algún día hay una
            // campaña real (asesorías, recuperación), se activa aquí con
            // $sessionParams['allow_promotion_codes'] = true para los planes que toque.

            $session = $this->stripeService->createCheckoutSession($sessionParams);

            return redirect()->to($session->url);

        } catch (\Throwable $e) {
            log_message('error', '[Billing::startStripeCheckout] plan=' . $plan . ' user=' . $userId . ' · ' . $e->getMessage());
            // El texto de Stripe va al log, no a la pantalla: llega en inglés, habla de
            // parámetros internos y, en la página donde se paga, asusta más que ayuda.
            return redirect()->back()->with(
                'error',
                'No hemos podido abrir la pasarela de pago. Vuelve a intentarlo en un momento; si sigue fallando, escríbenos a soporte@apiempresas.es y lo resolvemos.'
            );
        }
    }

    /**
     * POST /billing/checkout_bonus
     * Checkout dinámico para la venta de bonos de créditos prepago.
     */
    public function checkout_bonus()
    {
        // === DIAG TEMPORAL — borrar tras confirmar fix ===
        log_message('info', '[DIAG::checkout_bonus] method=' . $this->request->getMethod()
            . ' | logged_in=' . (session('logged_in') ? 'YES' : 'NO')
            . ' | user_id=' . session('user_id')
            . ' | POST=' . json_encode($this->request->getPost())
            . ' | GET=' . json_encode($this->request->getGet())
            . ' | IP=' . $this->request->getIPAddress()
            . ' | PROTO=' . ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? 'none')
        );
        // === /DIAG ===

        $postData = $this->request->getPost() ?: $this->request->getGet();

        if (!session('logged_in')) {
            // Guardar contexto para después del registro rápido
            // Solo guardar si tiene los datos necesarios
            if (!empty($postData['credits'])) {
                session()->set('pending_checkout_bonus', $postData);
            }
            return redirect()->to(site_url('register/quick?redirect=billing/checkout_bonus'));
        }

        // Si el POST/GET no trae créditos, intentar recuperar de sesión (viene de registro rápido)
        if (empty($postData['credits'])) {
            $pending = session('pending_checkout_bonus') ?? [];
            session()->remove('pending_checkout_bonus');
            if (!empty($pending['credits'])) {
                $postData = $pending;
            }
        } else {
            // Limpiar cualquier sesión pendiente si ya llegaron datos directamente
            session()->remove('pending_checkout_bonus');
        }

        $userId = (int) session('user_id');
        $credits = (int) ($postData['credits'] ?? 0);

        // Validar mínimo de créditos - redirigir explícitamente a la página del bono (nunca al dashboard)
        if ($credits < 10000) {
            return redirect()->to(site_url('crear-bono-api'))->with('error', lang('Messages.flash_10'));
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
                return redirect()->to(site_url('billing/success'))->with('message', lang('Messages.flash_11'));
            } else {
                return redirect()->back()->with('error', lang('Messages.flash_12'));
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

            $user_row = $this->userModel->find($userId);
            if (!$user_row) {
                return redirect()->back()->with('error', 'Usuario no encontrado.');
            }

            if (!empty($user_row->stripe_customer_id)) {
                $sessionParams['customer'] = $user_row->stripe_customer_id;
                unset($sessionParams['customer_creation']); // Evita error 400 de Stripe si se envían ambos
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
        $pricing = $this->billingService->getDirectoryPricingDetails($totalCount);
        $price = $pricing['base_price'];
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
            'has_phone' => $has_phone,
            'pricing'   => $pricing
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
        $convocatoriaName = $convocatoria !== '' ? $this->billingService->resolveSubsidiesConvocatoria($convocatoria) : '';

        $totalCount = $this->billingService->countSubsidies($params);
        $pricing = $this->billingService->getPublicFundsPricingDetails($totalCount);
        $price = $pricing['base_price'];
        $tax = round($price * 0.21, 2);

        $displayName = 'Subvenciones';
        if ($convocatoriaName) $displayName .= ' - ' . $this->billingService->formatSubsidiesConvocatoriaName($convocatoriaName);
        if ($year) $displayName .= ' (' . $year . ')';

        return $this->renderView('billing/public_funds_order_summary', [
            'display_name' => $displayName,
            'total_count' => $totalCount,
            'price' => $price,
            'tax' => $tax,
            'type' => 'subsidies',
            'pricing' => $pricing,
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
        $organoName = $organo !== '' ? $this->billingService->resolveContractsOrgano($organo) : '';

        $totalCount = $this->billingService->countContracts($params);
        $pricing = $this->billingService->getPublicFundsPricingDetails($totalCount);
        $price = $pricing['base_price'];
        $tax = round($price * 0.21, 2);

        $displayName = 'Licitaciones Públicas';
        if ($organoName) $displayName .= ' - ' . $this->billingService->formatContractsOrganoName($organoName);
        if ($year) $displayName .= ' (' . $year . ')';

        return $this->renderView('billing/public_funds_order_summary', [
            'display_name' => $displayName,
            'total_count' => $totalCount,
            'price' => $price,
            'tax' => $tax,
            'type' => 'contracts',
            'pricing' => $pricing,
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
        $pricing = null;

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
            'total_count' => $count,
            'pricing' => $pricing
        ];

        return $this->renderView('billing/order_summary', $data);
    }

    /**
     * GET /billing/success
     * (Pantalla de éxito). Estado real mejor por webhook Stripe.
     */
    public function success()
    {
        // Permitir acceso sin login si viene del simulador con contexto de excel en sesión o con session_id de Stripe
        $stripeSessionId = $this->request->getGet('session_id');
        $hasStripeSession = !empty($stripeSessionId) && str_starts_with($stripeSessionId, 'cs_');
        $hasSimulatorContext = session('checkout_context') !== null || session('simulator_excel_token') !== null || session('just_bought_excel') !== null || $hasStripeSession;
        if (!session('logged_in') && !$hasSimulatorContext) {
            return redirect()->to(site_url('enter'))->with('error', lang('Messages.flash_13'));
        }

        $userId = (int) session('user_id');

        // Fetch user's active subscription
        $subscription = $this->UsersuscriptionsModel->getActivePlanByUserId($userId);

        $checkoutData = session('checkout_context') ?? [];
        $lastInfo = session('last_purchase_info') ?? [];

        // --- ATRIBUCIÓN DE LA COMPRA ---
        // La sesión PHP puede perderse en el salto a Stripe y de vuelta, por eso el
        // source viaja también en los metadatos de la sesión de Stripe.
        $attrSource = (string) (session('checkout_source') ?? '');
        if ($attrSource === '' && $hasStripeSession) {
            try {
                $attrStripe = (new \Stripe\StripeClient(env('STRIPE_SECRET_KEY')))
                    ->checkout->sessions->retrieve($stripeSessionId);
                $attrSource = (string) ($attrStripe->metadata->source ?? '');
                $attrPlan   = (string) ($attrStripe->metadata->plan ?? '');
                $attrPeriod = (string) ($attrStripe->metadata->period ?? '');
            } catch (\Throwable $e) {
                log_message('error', '[Billing::success] atribución: ' . $e->getMessage());
            }
        }

        // Una recarga de la página de éxito no debe contar una segunda conversión
        $attrKey = 'checkout_logged_' . ($stripeSessionId ?: 'sim_' . date('YmdHi'));
        if (!session()->get($attrKey)) {
            session()->set($attrKey, true);
            $this->logCheckoutEvent('checkout_completed', $attrSource, [
                'plan'       => $attrPlan ?? ($lastInfo['plan'] ?? ($checkoutData['type'] ?? '')),
                'period'     => $attrPeriod ?? ($lastInfo['period'] ?? ''),
                'stripe_id'  => $stripeSessionId ?: null,
            ], $userId);
        }
        session()->remove('checkout_source');

        // Fallback: Recuperar contexto desde la sesión de Stripe si la sesión de PHP se perdió en la redirección
        if (empty($checkoutData) && empty($lastInfo) && $hasStripeSession) {
            try {
                $stripe = new \Stripe\StripeClient(env('STRIPE_SECRET_KEY'));
                $stripeSession = $stripe->checkout->sessions->retrieve($stripeSessionId);
                if ($stripeSession && !empty($stripeSession->metadata->export_context)) {
                    $checkoutData = json_decode($stripeSession->metadata->export_context, true) ?? [];
                } elseif ($stripeSession && ($stripeSession->metadata->plan ?? '') === 'risk_pack_5') {
                    $checkoutData = [
                        'type'       => 'risk_pack_5',
                        'credits'    => (int)($stripeSession->metadata->credits ?? 5),
                        'target_cif' => (string)($stripeSession->metadata->target_cif ?? ''),
                    ];
                }
            } catch (\Exception $e) {
                log_message('error', '[Billing::success] Error recuperando sesión Stripe: ' . $e->getMessage());
            }
        }

        // 1.4 Pack de consultas de Solvencia.
        //
        // ESTA PÁGINA NO ABONA NADA POR SU CUENTA. Antes sumaba los créditos con
        // solo tener en la sesión el contexto del checkout, que se guarda ANTES de
        // ir a Stripe: cancelar el pago y abrir /billing/success daba 5 créditos
        // gratis, repetible cada hora. Y una compra real sumaba 10, porque el
        // webhook también abonaba.
        //
        // Ahora: se le pregunta a Stripe por la sesión de pago y, solo si está
        // cobrada, se llama al mismo punto que el webhook (RiskPackService), que
        // abona una única vez por sesión. Así, si el usuario vuelve antes de que
        // llegue el webhook, ya ve sus créditos, y cuando el webhook llegue no
        // suma nada.
        $packStripe = null;
        if ($hasStripeSession) {
            $packStripe = $stripeSession ?? null;
            if (!$packStripe) {
                try {
                    $packStripe = (new \Stripe\StripeClient(env('STRIPE_SECRET_KEY')))
                        ->checkout->sessions->retrieve($stripeSessionId);
                } catch (\Throwable $e) {
                    log_message('error', '[Billing::success] No se pudo consultar la sesión ' . $stripeSessionId . ': ' . $e->getMessage());
                }
            }
        }
        $packEnStripe = $packStripe && ($packStripe->metadata->plan ?? '') === 'risk_pack_5';

        $isRiskPack = ($checkoutData['type'] ?? '') === 'risk_pack_5' || $packEnStripe;

        if ($isRiskPack) {
            $pagado = false;

            if ($packEnStripe) {
                if (($packStripe->payment_status ?? '') === 'paid') {
                    $pagado = true;
                    // Al comprador de ESA sesión, no a quien tenga la sesión PHP abierta.
                    $comprador = (int) ($packStripe->client_reference_id ?? $packStripe->metadata->user_id ?? 0);
                    (new \App\Services\RiskPackService())->abonar(
                        (string) $packStripe->id,
                        $comprador,
                        (int) ($packStripe->metadata->credits ?? 5),
                        (string) ($packStripe->metadata->target_cif ?? ''),
                        isset($packStripe->amount_total) ? (int) $packStripe->amount_total : null
                    );
                }
            } elseif (env('BILLING_MODE') === 'simulator' && session('risk_pack_sim_ref')) {
                // En el simulador el abono ya lo ha hecho BillingSimulator; aquí solo
                // se enseña la confirmación, una vez.
                $pagado = true;
                session()->remove('risk_pack_sim_ref');
            }

            if (!$pagado) {
                session()->remove('checkout_context');
                return redirect()->to(site_url('dashboard?view=risk'))->with(
                    'error',
                    'No nos consta el pago del pack. Si lo has completado, las consultas aparecerán en tu cuenta en unos minutos; si lo cancelaste, no se te ha cobrado nada.'
                );
            }

            $credits = (int) ($packStripe->metadata->credits ?? ($checkoutData['credits'] ?? 5));
            if ($credits <= 0) {
                $credits = 5;
            }

            $userRow = $userId > 0 ? (new \App\Models\UserModel())->find($userId) : null;
            $totalCredits = (int)($userRow->risk_credits ?? $credits);

            $data = [
                'credits_bought' => $credits,
                'total_credits'  => $totalCredits,
                // Base imponible cobrada de verdad (la vista suma el IVA encima, así que
                // es amount_subtotal y no amount_total); si no, el precio del config.
                'price'          => isset($packStripe->amount_subtotal)
                    ? ((int) $packStripe->amount_subtotal) / 100
                    : ((int) solvencia('centimos.pack5', 990)) / 100,
                'order_ref'      => 'RISK-' . date('Ymd') . '-' . rand(1000, 9999),
                'target_cif'     => (string) ($packStripe->metadata->target_cif ?? ($checkoutData['target_cif'] ?? '')),
            ];
            $data['target_url'] = $this->urlFichaPorCif($data['target_cif'], true);
            session()->remove('checkout_context');
            return $this->renderView('billing/success_risk_pack', $data);
        }

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
        $validExcelTypes = ['excel', 'directory_excel', 'subsidies_excel', 'contracts_excel', 'lookalike_excel'];
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

            // Retroactive fix: If we detected it's a directory download or has cnae, ensure historical flag
            if (($isDir || !empty($exportParams['cnae'])) && (!isset($exportParams['is_historical']) || $exportParams['is_historical'] !== '1')) {
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
            } elseif (($lastInfo['type'] ?? '') === 'lookalike_excel') {
                // Pre-generar el archivo ahora para que la descarga sea instantánea
                $lookalike = new \App\Controllers\LookalikeController();
                $filename = $lookalike->preGenerateExcel();
                if ($filename) {
                    $downloadUrl = site_url('billing/export-lookalike?file=' . urlencode($filename));
                } else {
                    // Fallback si no hay contexto (improbable en success)
                    $downloadUrl = site_url('billing/export-lookalike');
                }
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

        // 3. Solvencia Pro.
        //
        // Dos formas de saber que acaba de contratar:
        //  - Stripe dice que ESTA sesión de pago es de risk_pro y está completa. No
        //    depende del webhook: antes, si el usuario volvía antes de que llegara, la
        //    suscripción aún no estaba en la BD y se le mandaba al panel sin
        //    confirmación, donde seguía viendo "Activar Solvencia Pro".
        //  - O la BD ya tiene la suscripción (el caso de siempre, sin sesión de Stripe).
        $proEnStripe = isset($packStripe) && $packStripe
            && ($packStripe->metadata->plan ?? '') === 'risk_pro'
            && ($packStripe->status ?? '') === 'complete';
        //    Si la sesión de Stripe es claramente de OTRO producto, no se secuestra.
        $sesionDeOtroPlan = isset($packStripe) && $packStripe
            && ($packStripe->metadata->plan ?? '') !== ''
            && ($packStripe->metadata->plan ?? '') !== 'risk_pro';
        $proEnBd = !$sesionDeOtroPlan && $subscription
            && (($subscription->plan_slug ?? '') === 'risk_pro' || ($subscription->product_type ?? '') === 'risk');

        if ($proEnStripe || $proEnBd) {
            if ($proEnStripe) {
                $isAnnual = ($packStripe->metadata->period ?? '') === 'annual';
                $basePrice = isset($packStripe->amount_subtotal)
                    ? ((int) $packStripe->amount_subtotal) / 100
                    : ($isAnnual ? 290 : 29);
                $orderRef = 'SUB-' . strtoupper(substr((string) $packStripe->id, -8));
            } else {
                $isAnnual = false;
                if (!empty($subscription->current_period_start) && !empty($subscription->current_period_end)) {
                    $days = (strtotime((string)$subscription->current_period_end) - strtotime((string)$subscription->current_period_start)) / 86400;
                    $isAnnual = $days > 40;
                }
                $basePrice = $isAnnual ? ($subscription->price_annual ?? '290') : ($subscription->price_monthly ?? '29');
                $orderRef  = 'SUB-' . str_pad((string) ($subscription->id ?? '0'), 6, '0', STR_PAD_LEFT);
            }

            $userRow = $userId > 0 ? $this->userModel->find($userId) : null;

            $data = [
                'plan_name'      => 'Solvencia Pro',
                'base_price'     => $basePrice,
                'period_name'    => $isAnnual ? 'Anual' : 'Mensual',
                'payment_method' => 'Stripe',
                'order_ref'      => $orderRef,
                // Los tres pasos de activación
                'consultadas'    => $this->empresasConsultadasSinVigilar($userId),
                'user_email'     => (string) ($userRow->email ?? ''),
                'avisos_activos' => $userRow ? $this->avisosActivos($userRow) : true,
            ];
            return $this->renderView('billing/success_risk', $data);
        }

        // 4. API Subscription Success (Pro/Business)
        if ($subscription && strtolower($subscription->plan_slug ?? '') !== 'free' && (float) ($subscription->price_monthly ?? 0) > 0) {
            $data = [
                'plan_name' => $subscription->plan_name ?? 'Pro',
                'base_price' => $subscription->price_monthly ?? '19',
                'period_name' => 'Mensual',
                'payment_method' => 'Tarjeta (Stripe)',
                'order_ref' => 'SUB-' . str_pad($subscription->id ?? '0', 6, '0', STR_PAD_LEFT),
            ];
            
            if ($this->request->getGet('source') === 'copilot' || ($subscription->plan_slug ?? '') === 'copiloto_ventas') {
                return $this->renderView('billing/success_copilot', $data);
            }

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
        return redirect()->to(site_url('billing'))->with('info', lang('Messages.flash_14'));
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
            return redirect()->back()->with('error', lang('Messages.flash_15'));
        }

        // Fix: Castear a int para evitar error de "43" !== 43
        if ((int) $invoice->user_id !== $userId) {
            return redirect()->back()->with('error', lang('Messages.flash_16'));
        }

        if (empty($invoice->pdf_path)) {
            return redirect()->back()->with('error', lang('Messages.flash_17'));
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

        return redirect()->to(site_url('dashboard'))->with('message', lang('Messages.flash_18'));
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
        $reasonOptions = [
            'too_expensive' => 'Precio',
            'missing_features' => 'Faltan funcionalidades',
            'low_usage' => 'Poco uso',
            'technical_issues' => 'Problemas tecnicos',
            'switched_solution' => 'Uso otra solucion',
            'temporary_pause' => 'Pausa temporal',
            'other' => 'Otro motivo',
            'prefer_not_to_say' => 'Prefiere no responder',
        ];
        $cancellationReason = trim((string) $this->request->getPost('cancellation_reason'));
        if (!array_key_exists($cancellationReason, $reasonOptions)) {
            $cancellationReason = 'prefer_not_to_say';
        }
        $cancellationFeedback = trim((string) $this->request->getPost('cancellation_feedback'));
        if (function_exists('mb_substr')) {
            $cancellationFeedback = mb_substr($cancellationFeedback, 0, 1000);
        } else {
            $cancellationFeedback = substr($cancellationFeedback, 0, 1000);
        }

        if ($specificSubId) {
            $plan = $this->UsersuscriptionsModel->where('user_id', $userId)->find($specificSubId);
        } else {
            $plan = $this->UsersuscriptionsModel->getActivePlanByUserId($userId);
        }

        if (!$plan) {
            if ($this->request->isAJAX() || $this->request->getPost('ajax')) {
                return $this->response->setJSON(['status' => 'error', 'message' => 'No tienes ninguna suscripción activa para cancelar.']);
            }
            return redirect()->back()->with('error', lang('Messages.flash_19'));
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
        $subscriptionUpdate = [
            'status' => 'canceled',
            'canceled_at' => date('Y-m-d H:i:s'),
        ];
        $subscriptionColumns = \Config\Database::connect()->getFieldNames('user_subscriptions');
        if (in_array('cancellation_reason', $subscriptionColumns, true)) {
            $subscriptionUpdate['cancellation_reason'] = $cancellationReason;
        }
        if (in_array('cancellation_feedback', $subscriptionColumns, true)) {
            $subscriptionUpdate['cancellation_feedback'] = $cancellationFeedback !== '' ? $cancellationFeedback : null;
        }

        $this->UsersuscriptionsModel->update($plan->id, $subscriptionUpdate);

        // Log subscription cancellation
        log_activity('subscription_cancelled', [
            'plan' => $plan->plan_name ?? 'Unknown',
            'reason' => $cancellationReason,
            'reason_label' => $reasonOptions[$cancellationReason],
            'feedback' => $cancellationFeedback,
        ]);

        $this->sendSubscriptionCancellationEmail($userId, $plan, $reasonOptions[$cancellationReason], $cancellationFeedback);

        if ($this->request->isAJAX() || $this->request->getPost('ajax')) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Tu suscripción ha sido cancelada. Seguirás teniendo acceso hasta el final del periodo facturado y no se te cobrará de nuevo.'
            ]);
        }

        return redirect()->to(site_url('billing'))->with('message', lang('Messages.flash_20'));
    }

    private function sendSubscriptionCancellationEmail(int $userId, object $plan, string $reasonLabel, string $feedback): void
    {
        try {
            $user = $this->userModel->find($userId);
            $userName = trim((string) ($user->name ?? 'Usuario sin nombre'));
            $userEmail = trim((string) ($user->email ?? 'Sin email'));
            $planName = (string) ($plan->plan_name ?? 'Plan desconocido');
            $periodEnd = !empty($plan->current_period_end)
                ? date('d/m/Y H:i', strtotime((string) $plan->current_period_end))
                : 'No disponible';
            $stripeSubscriptionId = (string) ($plan->stripe_subscription_id ?? 'No disponible');
            $feedbackText = $feedback !== '' ? nl2br(htmlspecialchars($feedback, ENT_QUOTES, 'UTF-8')) : 'Sin comentario adicional';

            $body = '
                <div style="font-family: Arial, sans-serif; color: #0f172a; line-height: 1.5; max-width: 680px;">
                    <h2 style="margin: 0 0 16px; color: #b91c1c;">Cancelacion de suscripcion</h2>
                    <p style="margin: 0 0 18px;">Un usuario acaba de cancelar su suscripcion desde billing.</p>
                    <table style="width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0;">
                        <tr><td style="padding: 10px; border-bottom: 1px solid #e2e8f0; font-weight: bold; width: 180px;">Usuario</td><td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">' . htmlspecialchars($userName, ENT_QUOTES, 'UTF-8') . ' (#' . $userId . ')</td></tr>
                        <tr><td style="padding: 10px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Email</td><td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">' . htmlspecialchars($userEmail, ENT_QUOTES, 'UTF-8') . '</td></tr>
                        <tr><td style="padding: 10px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Plan</td><td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">' . htmlspecialchars($planName, ENT_QUOTES, 'UTF-8') . '</td></tr>
                        <tr><td style="padding: 10px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Motivo</td><td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">' . htmlspecialchars($reasonLabel, ENT_QUOTES, 'UTF-8') . '</td></tr>
                        <tr><td style="padding: 10px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Comentario</td><td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">' . $feedbackText . '</td></tr>
                        <tr><td style="padding: 10px; border-bottom: 1px solid #e2e8f0; font-weight: bold;">Acceso hasta</td><td style="padding: 10px; border-bottom: 1px solid #e2e8f0;">' . htmlspecialchars($periodEnd, ENT_QUOTES, 'UTF-8') . '</td></tr>
                        <tr><td style="padding: 10px; font-weight: bold;">Stripe subscription</td><td style="padding: 10px;">' . htmlspecialchars($stripeSubscriptionId, ENT_QUOTES, 'UTF-8') . '</td></tr>
                    </table>
                    <p style="margin-top: 18px;">
                        <a href="' . site_url('admin/subscriptions?status=canceled') . '" style="color: #2563eb; font-weight: bold;">Ver suscripciones canceladas</a>
                    </p>
                </div>';

            $emailService = \Config\Services::email();
            $emailService->clear(true);
            $emailService->setFrom('soporte@apiempresas.es', 'APIEmpresas');
            $emailService->setTo('papelo.amh@gmail.com');
            $emailService->setSubject('Cancelacion de suscripcion - ' . $planName . ' - ' . $userEmail);
            $emailService->setMailType('html');
            $emailService->setMessage($body);

            if (!$emailService->send()) {
                log_message('error', '[Billing::sendSubscriptionCancellationEmail] Error enviando email: ' . $emailService->printDebugger(['headers']));
            }
        } catch (\Throwable $e) {
            log_message('error', '[Billing::sendSubscriptionCancellationEmail] ' . $e->getMessage());
        }
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
            return redirect()->to(site_url('billing'))->with('error', lang('Messages.flash_21'));
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
