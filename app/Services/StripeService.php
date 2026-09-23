<?php

namespace App\Services;

class StripeService
{
    protected $secretKey;
    protected $taxRateId;

    public function __construct()
    {
        $this->secretKey = env('STRIPE_SECRET_KEY');
        $this->taxRateId = env('STRIPE_TAX_RATE_ID');

        if ($this->secretKey) {
            \Stripe\Stripe::setApiKey($this->secretKey);
        }
    }

    /**
     * @throws \Exception
     */
    private function checkConfig()
    {
        if (!$this->secretKey) {
            throw new \Exception('Stripe no está configurado (STRIPE_SECRET_KEY).');
        }
        if (!extension_loaded('curl')) {
            throw new \Exception('El servidor no tiene habilitada la extensión CURL, necesaria para procesar pagos con Stripe. Por favor, habilítala en Laragon (Menú -> PHP -> Extensions -> curl) y reinicia los servicios.');
        }
    }

    public function getTaxRateId()
    {
        return $this->taxRateId;
    }

    /**
     * Crea la sesión de Checkout. Todos los pagos pasan por aquí (Billing y los PDF
     * de Company), así que las reglas comunes viven aquí y no repetidas en cada
     * controlador.
     */
    public function createCheckoutSession(array $params)
    {
        $this->checkConfig();

        $params = $this->normalizar($params);

        try {
            return \Stripe\Checkout\Session::create($params);
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            /*
             * Cliente de Stripe que ya no existe: el `stripe_customer_id` guardado es
             * de modo de pruebas o se borró en Stripe. Antes eso impedía pagar a ese
             * usuario para siempre. Se reintenta una vez sin cliente, con su email,
             * y Stripe crea uno nuevo.
             */
            if (!empty($params['customer']) && $e->getStripeParam() === 'customer') {
                log_message('error', '[StripeService] Cliente ' . $params['customer'] . ' no válido en Stripe; se reintenta sin él: ' . $e->getMessage());

                $email = $this->emailDelCliente($params['customer']);
                unset($params['customer'], $params['customer_update']);
                if ($email !== '') {
                    $params['customer_email'] = $email;
                }

                return \Stripe\Checkout\Session::create($params);
            }

            throw $e;
        }
    }

    /**
     * Reglas comunes a cualquier sesión de pago.
     */
    private function normalizar(array $params): array
    {
        // 1. Idioma. Sin `locale` Stripe usa el del navegador; en un checkout para
        //    pymes españolas, cualquier pantalla en inglés en el paso de pagar es
        //    abandono. El dominio en inglés (spaincompanyapi) sigue en inglés.
        if (empty($params['locale'])) {
            $params['locale'] = $this->idioma();
        }

        // 2. `customer` y `customer_email` son excluyentes: Stripe rechaza la sesión
        //    si llegan los dos. Pasaba con el pack de Solvencia, que ponía el email y
        //    luego el cliente, así que a un comprador que repetía no le abría el pago.
        //    Y un `customer_email` vacío tampoco se acepta.
        if (!empty($params['customer'])) {
            unset($params['customer_email'], $params['customer_creation']);

            // 3. Con un cliente existente, pedir NIF (`tax_id_collection`) o la
            //    dirección exige decirle a Stripe que puede actualizar su ficha; si no,
            //    la sesión falla. Le pasaba a quien ya había comprado un PDF y luego
            //    quería Solvencia Pro.
            $pideNif       = !empty($params['tax_id_collection']['enabled']);
            $pideDireccion = ($params['billing_address_collection'] ?? '') === 'required';
            if ($pideNif || $pideDireccion) {
                $params['customer_update'] = array_merge(
                    ['name' => 'auto', 'address' => 'auto'],
                    (array) ($params['customer_update'] ?? [])
                );
            }
        } elseif (array_key_exists('customer_email', $params) && empty($params['customer_email'])) {
            unset($params['customer_email']);
        }

        return $params;
    }

    private function idioma(): string
    {
        $host = (string) (service('request')->getServer('HTTP_HOST') ?? '');
        if (str_contains($host, 'spaincompanyapi')) {
            return 'en';
        }

        return session('lang') === 'en' ? 'en' : 'es';
    }

    private function emailDelCliente(string $customerId): string
    {
        try {
            $fila = \Config\Database::connect()->table('users')
                ->select('email')
                ->where('stripe_customer_id', $customerId)
                ->get(1)->getRow();

            return (string) ($fila->email ?? '');
        } catch (\Throwable $e) {
            return '';
        }
    }

    public function createBillingPortalSession(string $customerId, string $returnUrl)
    {
        $this->checkConfig();
        return \Stripe\BillingPortal\Session::create([
            'customer'   => $customerId,
            'return_url' => $returnUrl,
        ]);
    }

    public function cancelSubscription(string $subscriptionId)
    {
        $this->checkConfig();
        return \Stripe\Subscription::update($subscriptionId, [
            'cancel_at_period_end' => true,
        ]);
    }
}
