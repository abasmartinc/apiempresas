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
     * English version – /spanish-company-api (Used on apiempresas.es as a subfolder)
     */
    public function english()
    {
        $freeLimit = get_free_plan_limit();

        return view('seo/api_prices_en', ['freeLimit' => $freeLimit]);
    }

    /**
     * English Standalone version – loaded when hostname is spaincompanyapi.test / .com
     */
    public function englishStandalone()
    {
        $freeLimit = get_free_plan_limit();

        return view('seo/api_prices_en_standalone', ['freeLimit' => $freeLimit]);
    }

    /**
     * Muestra el Wizard interactivo para crear un bono de créditos a medida
     */
    public function customBonusWizard()
    {
        return view('seo/api_custom_bonus');
    }

    public function customBonusWizardEn()
    {
        return view('seo/api_custom_bonus_en');
    }
}
