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
        if (!session()->get('logged_in')) {
            $currentUri = (string) $request->getUri()->getPath();
            return redirect()->to(site_url('enter?redirect=' . urlencode($currentUri)));
        }

        $userId = session()->get('user_id');
        $productType = $arguments[0] ?? 'api'; // default is api access

        $subscriptionModel = new UsersuscriptionsModel();
        
        if ($productType === 'radar') {
            // Permitimos entrar al radar a cualquier usuario logueado para que vea el "upsell"
            // La lógica de restricción de datos se manejará en el controlador/vista
            return;
        }

        if (!$subscriptionModel->hasActiveSubscriptionFor($userId, $productType)) {
            // Si no tiene acceso, redirigir a la página de precios correspondiente
            $redirectUrl = ($productType === 'radar') ? 'precios-radar' : 'prices';
            return redirect()->to(site_url($redirectUrl))->with('error', 'Necesitas una suscripción activa para acceder a esta sección.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after
    }
}
