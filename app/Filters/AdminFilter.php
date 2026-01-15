<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Check if logged in
        if (! session()->get('logged_in')) {
            return redirect()->to(site_url('enter'))->with('error', 'Debes iniciar sesión.');
        }

        // 2. Check if admin
        if ((int) session()->get('is_admin') !== 1) {
            return redirect()->to(site_url('dashboard'))->with('error', 'Acceso no autorizado.');
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
