<?php

namespace App\Controllers;

class ApiPrices extends BaseController
{
    /**
     * Muestra la página de marketing específica para la API comercial
     */
    public function index()
    {
        $apiPlanModel = new \App\Models\ApiPlanModel();
        $freePlan = $apiPlanModel->where('slug', 'free')->first();
        $freeLimit = $freePlan ? (int)$freePlan->monthly_quota : 15;

        return view('seo/api_prices', ['freeLimit' => $freeLimit]);
    }
}
