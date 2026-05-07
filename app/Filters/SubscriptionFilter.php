<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\UsersuscriptionsModel;

class SubscriptionFilter implements FilterInterface
{
    /**
     * @param array|null $arguments [productType]
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        $currentUri = uri_string();
        $isApiAuthenticated = !empty(\App\Filters\ApiKeyFilter::$apiMeta['user_id']); // Set by ApiKeyFilter
        $isLoggedIn = session()->get('logged_in');
        
        $productType = $arguments[0] ?? 'api';
        $isApiRoute = (strpos($currentUri, 'api/') !== false || $productType === 'api');

        @file_put_contents(WRITEPATH . 'debug_redirect.txt', date('Y-m-d H:i:s') . " | SUB_FILTER | URI: {$currentUri} | Product: {$productType} | LoggedIn: " . ($isLoggedIn?'Y':'N') . " | ApiAuth: " . ($isApiAuthenticated?'Y':'N') . " | isApiRoute: " . ($isApiRoute?'Y':'N') . "\n", FILE_APPEND);

        if (!$isLoggedIn && !$isApiAuthenticated) {
            // If it's an API request, return JSON instead of redirecting
            if ($isApiRoute) {
                return service('response')
                    ->setStatusCode(401)
                    ->setJSON([
                        'success' => false,
                        'error'   => 'UNAUTHORIZED',
                        'message' => 'Falta autenticación o sesión.',
                    ]);
            }

            return redirect()->to(site_url('enter?redirect=' . urlencode($currentUri)));
        }

        $userId = $isApiAuthenticated ? \App\Filters\ApiKeyFilter::$apiMeta['user_id'] : session()->get('user_id');
        $productType = $arguments[0] ?? 'api'; // default is api access

        $subscriptionModel = new UsersuscriptionsModel();
        
        if ($productType === 'radar') {
            // Permitimos entrar al radar a cualquier usuario logueado para que vea el "upsell"
            // La lógica de restricción de datos se manejará en el controlador/vista
            return null;
        }

        if (!$subscriptionModel->hasActiveSubscriptionFor($userId, $productType)) {
            // If it's an API request, return JSON error for missing subscription
            if ($isApiRoute) {
                return service('response')
                    ->setStatusCode(403)
                    ->setJSON([
                        'success' => false,
                        'error'   => 'NO_SUBSCRIPTION',
                        'message' => "Necesitas una suscripción activa para el producto '{$productType}'.",
                    ]);
            }

            // Si no tiene acceso vía web, redirigir a la página de precios correspondiente
            $redirectUrl = ($productType === 'radar') ? 'leads-empresas-nuevas' : 'prices';
            return redirect()->to(site_url($redirectUrl))->with('error', 'Necesitas una suscripción activa para acceder a esta sección.');
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after
        return null;
    }
}
