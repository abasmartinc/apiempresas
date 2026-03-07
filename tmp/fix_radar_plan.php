<?php

// Manually bootstrap CodeIgniter to use the models
require_once __DIR__ . '/../spark';

$planModel = new \App\Models\ApiPlanModel();

$exists = $planModel->where('slug', 'radar')->first();

if (!$exists) {
    $planModel->insert([
        'slug'               => 'radar',
        'name'               => 'Radar B2B',
        'monthly_quota'      => 999999,
        'rate_limit_per_min' => 60,
        'price_monthly'      => 49.00,
        'price_annual'       => 470.00,
        'is_active'          => 1,
        'max_alerts'         => 10,
        'product_type'       => 'radar'
    ]);
    echo "Radar plan created successfully.\n";
} else {
    echo "Radar plan already exists.\n";
}
