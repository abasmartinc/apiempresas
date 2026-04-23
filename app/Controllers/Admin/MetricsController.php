<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TrackingEventModel;
use App\Services\MetricsService;
use App\Services\OpenAiService;

class MetricsController extends BaseController
{
    protected $metricsService;

    public function __construct()
    {
        $this->metricsService = new MetricsService();
    }

    /**
     * Muestra las métricas originales de Monetización (Funnel, MRR, ARPU)
     */
    public function index()
    {
        $metrics = $this->metricsService->getAllMetrics();

        $data = [
            'title'   => 'Métricas de Monetización API',
            'metrics' => $metrics,
        ];

        return view('admin/metrics', $data);
    }

    /**
     * Muestra el nuevo dashboard de Tracking de Comportamiento
     */
    public function eventTracking()
    {
        $model = new TrackingEventModel();
        $filters = $this->getFilters();

        $data = [
            'title'          => 'Tracking de Comportamiento',
            'summary'        => $model->getEventsSummary($filters),
            'timeline'       => $model->getTimelineStats($filters),
            'topPages'       => $model->getTopPages($filters),
            'totalEvents'    => $model->getTotalEvents($filters),
            'uniqueVisitors' => $model->getUniqueVisitors($filters),
            'activeUsers'    => $model->getActiveUsersCount(),
            'eventNames'     => $model->select('DISTINCT(event_name)')->findAll(),
            'filters'        => $filters
        ];

        return view('admin/event_tracking', $data);
    }

    /**
     * Endpoint AJAX para la tabla de eventos
     */
    public function getTable()
    {
        $model = new TrackingEventModel();
        $filters = $this->getFilters();

        $builder = $model->orderBy('id', 'DESC');
        if ($filters['event_name']) $builder->where('event_name', $filters['event_name']);
        if ($filters['user_id'])    $builder->where('user_id', $filters['user_id']);
        if ($filters['from_date'])  $builder->where('created_at >=', $filters['from_date'] . ' 00:00:00');
        if ($filters['to_date'])    $builder->where('created_at <=', $filters['to_date'] . ' 23:59:59');

        $data = [
            'recentEvents' => $builder->paginate(50, 'events'),
            'pager'        => $model->pager,
        ];

        return view('admin/metrics_table_partial', $data);
    }

    /**
     * Análisis IA del comportamiento
     */
    public function getAiAnalysis()
    {
        $model = new TrackingEventModel();
        $aiService = new OpenAiService();

        $summary = $model->getEventsSummary();
        $topPages = $model->getTopPages();
        $total = $model->getTotalEvents();

        $dataContext = "Total de eventos: $total\n\nEventos:\n" . json_encode($summary) . "\n\nTop Páginas:\n" . json_encode($topPages);

        $prompt = [
            ['role' => 'system', 'content' => 'Eres un experto en CRO para APIEmpresas.'],
            ['role' => 'user', 'content' => "Analiza estos datos de comportamiento y dame pautas de mejora:\n\n$dataContext"]
        ];

        return $this->response->setJSON(['analysis' => $aiService->getChatResponse($prompt)]);
    }

    private function getFilters()
    {
        return [
            'event_name' => $this->request->getGet('event_name'),
            'from_date'  => $this->request->getGet('from_date'),
            'to_date'    => $this->request->getGet('to_date'),
            'user_id'    => $this->request->getGet('user_id'),
        ];
    }
}
