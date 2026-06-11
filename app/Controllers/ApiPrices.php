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

    /**
     * Muestra el Wizard interactivo para crear un bono de créditos a medida
     */
    public function customBonusWizard()
    {
        return view('seo/api_custom_bonus');
    }
}
