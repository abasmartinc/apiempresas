<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Services\MetricsService;

class MetricsController extends BaseController
{
    protected $metricsService;

    public function __construct()
    {
        $this->metricsService = new MetricsService();
    }

    public function index()
    {
        $metrics = $this->metricsService->getAllMetrics();

        $data = [
            'title'   => 'Métricas de Monetización API',
            'metrics' => $metrics,
        ];

        return view('admin/metrics', $data);
    }
}
