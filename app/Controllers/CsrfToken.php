<?php

namespace App\Controllers;

use CodeIgniter\Controller;

/**
 * GET /csrftoken (sin guion, ver Routes.php) → {"name": "...", "hash": "..."} y la cookie CSRF del visitante.
 *
 * Por qué (01-10-2026): Cloudflare guarda el HTML de /listado-de-empresas* y
 * /base-de-datos-de-empresas con el token CSRF del primer visitante. CSRF va por cookie
 * (Config\Security::$csrfProtection = 'cookie'), así que el resto recibía 403 en el
 * buscador de la cabecera (POST /search) y en el registro rápido (register/quick_store).
 * El pie de página pide aquí el token del visitante y lo pone en los formularios.
 *
 * Esta respuesta no se guarda nunca (ni navegador ni Cloudflare).
 */
class CsrfToken extends Controller
{
    public function index()
    {
        return $this->response
            ->setHeader('Cache-Control', 'no-store, private, max-age=0')
            ->setHeader('CDN-Cache-Control', 'no-store')
            ->setHeader('Cloudflare-CDN-Cache-Control', 'no-store')
            ->setJSON([
                'name' => csrf_token(),
                'hash' => csrf_hash(),
            ]);
    }
}
