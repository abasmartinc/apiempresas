<?php

namespace App\Controllers;

class Plugin extends BaseController
{
    /**
     * Muestra la página de marketing para el Plugin de WordPress
     */
    public function index()
    {
        $freeLimit = get_free_plan_limit();

        return view('seo/plugin_wordpress', ['freeLimit' => $freeLimit]);
    }

    /**
     * Lógica de acceso al plugin: Redirige al registro si no está logueado
     * o al dashboard si ya tiene cuenta.
     */
    public function get_plugin()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('register?redirect=obtener-plugin-wordpress'));
        }

        return redirect()->to(site_url('dashboard#plugin-wp'))
            ->with('info', 'Ya puedes descargar y configurar tu plugin de WordPress desde aquí.');
    }

    /**
     * Descarga física del plugin
     */
    public function download()
    {
        if (!session('logged_in')) {
            return redirect()->to(site_url('enter'));
        }

        // TODO: En producción, aquí se serviría el archivo .zip real
        // Para esta fase, redirigimos al dashboard con aviso o devolvemos un mensaje
        return redirect()->to(site_url('dashboard#plugin-wp'))
            ->with('info', 'El archivo del plugin se está preparando. Estará disponible en tu dashboard en breve.');
    }
}
