<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * Class BaseController
 *
 * BaseController provides a convenient place for loading components
 * and performing functions that are needed by all your controllers.
 * Extend this class in any new controllers:
 *     class Home extends BaseController
 *
 * For security be sure to declare any new methods as protected or private.
 */
abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = ['radar_helper', 'company', 'api'];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    // protected $session;

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        $host = $request->getServer('HTTP_HOST') ?? '';
        
        // Priority 1: User's session language
        // Priority 2: Domain based language
        if (session()->has('lang')) {
            $locale = session('lang');
        } else {
            $locale = (strpos((string)$host, 'spaincompanyapi') !== false) ? 'en' : 'es';
        }
        
        $request->setLocale($locale);
        \Config\Services::language()->setLocale($locale);

        // Marca "sesión iniciada" para Cloudflare (01-10-2026). La Page Rule
        // *apiempresas.es/*-* guarda un mes el HTML de todas las URL con guion (fichas,
        // listados) y la cabecera lleva nombre, email y avatar de quien tiene sesión: si
        // un usuario con sesión era el primero en pedir una página, Cloudflare podía
        // servir su cabecera a todos. La Cache Rule "Protección Privacidad" salta la caché
        // cuando la petición trae la cookie ae_auth=1. No lleva datos: solo dice "hay sesión".
        $conSesion = (bool) session('logged_in');
        $marca     = ($_COOKIE['ae_auth'] ?? '') === '1';
        if (!is_cli() && $conSesion !== $marca && !headers_sent()) {
            setcookie('ae_auth', $conSesion ? '1' : '', [
                'expires'  => $conSesion ? time() + 30 * 86400 : time() - 3600,
                'path'     => '/',
                'secure'   => $request->isSecure(),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
        }

        // Preload any models, libraries, etc, here.
        // E.g.: $this->session = \Config\Services::session();
    }

    /**
     * Devuelve true si la petición es de HTMX
     */
    protected function isHtmx(): bool
    {
        return $this->request->hasHeader('HX-Request');
    }

    /**
     * Renderiza la vista pasando automáticamente la variable $isHtmx
     */
    protected function renderView(string $view, array $data = [])
    {
        $data['isHtmx'] = $this->isHtmx();
        return view($view, $data);
    }
}
