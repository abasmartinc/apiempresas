<?php

namespace App\Controllers;

class RadarPrices extends BaseController
{
    /**
     * Muestra la página de precios específica para el producto Radar B2B
     */
    public function index()
    {
        return view('seo/radar_prices');
    }
}
