<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TrackingEventModel;
use App\Services\OpenAiService;

class MetricsController extends BaseController
{
    public function index()
    {
        $model = new TrackingEventModel();

        // Capturar filtros
        $filters = $this->getFilters();

        $data = [
            'title'          => 'Métricas de Comportamiento',
            'summary'        => $model->getEventsSummary($filters),
            'timeline'       => $model->getTimelineStats($filters),
            'topPages'       => $model->getTopPages($filters),
            'totalEvents'    => $model->getTotalEvents($filters),
            'uniqueVisitors' => $model->getUniqueVisitors($filters),
            'eventNames'     => $model->select('DISTINCT(event_name)')->findAll(),
            'filters'        => $filters
        ];

        return view('admin/metrics', $data);
    }

    /**
     * Devuelve solo el fragmento de la tabla (para AJAX)
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

    private function getFilters()
    {
        return [
            'event_name' => $this->request->getGet('event_name'),
            'from_date'  => $this->request->getGet('from_date'),
            'to_date'    => $this->request->getGet('to_date'),
            'user_id'    => $this->request->getGet('user_id'),
        ];
    }

    public function getAiAnalysis()
    {
        $model = new TrackingEventModel();
        $aiService = new OpenAiService();

        $summary = $model->getEventsSummary();
        $topPages = $model->getTopPages();
        $total = $model->getTotalEvents();

        $dataContext = "Total de eventos registrados: $total\n\nResumen de Eventos:\n" . json_encode($summary, JSON_PRETTY_PRINT) . "\n\nPáginas más activas:\n" . json_encode($topPages, JSON_PRETTY_PRINT);

        $prompt = [
            ['role' => 'system', 'content' => 'Eres un consultor experto en CRO para APIEmpresas.es.'],
            ['role' => 'user', 'content' => "Analiza estos datos y dame 3 conclusiones y 3 pautas para mejorar la conversión:\n\n$dataContext"]
        ];

        return $this->response->setJSON(['analysis' => $aiService->getChatResponse($prompt, ['temperature' => 0.5])]);
    }
}
