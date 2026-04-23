<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Verificar si hay sesión iniciada
        if (!session()->get('logged_in')) {
            return redirect()->to(site_url('enter'))->with('error', 'Debes iniciar sesión para acceder.');
        }

        // 2. Verificar si el usuario tiene permisos de administrador
        if (!session()->get('is_admin')) {
            // Log de intento de acceso no autorizado para auditoría
            log_message('critical', '[Security Alert] Unauthorized Admin Access Attempt by: ' . (session()->get('user_email') ?? 'Unknown') . ' from IP: ' . $request->getIPAddress());
            
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso denegado: Área restringida a administradores.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed
    }
}
