<?php

namespace App\Controllers;

class CopilotLandingController extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $plan = $db->table('api_plans')->where('slug', 'copiloto_ventas')->get()->getRow();
        
        $price = $plan ? $plan->price_monthly : 39;

        return $this->renderView('copilot_landing', [
            'title' => 'Copiloto de Ventas Pro',
            'copilotPrice' => $price
        ]);
    }
}
