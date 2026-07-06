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

    public function createCheckoutSession(array $params)
    {
        $this->checkConfig();
        return \Stripe\Checkout\Session::create($params);
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
