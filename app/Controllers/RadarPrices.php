<?php

namespace App\Controllers;

class RadarPrices extends BaseController
{
    /**
     * Muestra la página de precios específica para el producto Radar B2B
     */
    public function index()
    {
        // Si no está logueado, lo mandamos a registro con intent de compra
        if (!session('logged_in')) {
            $redirectUrl = 'precios-radar';
            return redirect()->to(site_url('register?redirect=' . urlencode($redirectUrl)));
        }

        return view('seo/radar_prices');
    }
}
