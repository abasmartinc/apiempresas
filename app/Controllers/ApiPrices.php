<?php

namespace App\Controllers;

class ApiPrices extends BaseController
{
    /**
     * Muestra la página de marketing específica para la API comercial
     */
    public function index()
    {
        $freeLimit = get_free_plan_limit();

        return view('seo/api_prices', ['freeLimit' => $freeLimit]);
    }
}
