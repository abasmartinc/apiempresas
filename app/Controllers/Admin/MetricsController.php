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
        $filters = [
            'event_name' => $this->request->getGet('event_name'),
            'from_date'  => $this->request->getGet('from_date'),
            'to_date'    => $this->request->getGet('to_date'),
            'user_id'    => $this->request->getGet('user_id'),
        ];

        // Construir query base para el listado reciente con filtros
        $recentBuilder = $model->orderBy('id', 'DESC');
        if ($filters['event_name']) $recentBuilder->where('event_name', $filters['event_name']);
        if ($filters['user_id'])    $recentBuilder->where('user_id', $filters['user_id']);
        if ($filters['from_date'])  $recentBuilder->where('created_at >=', $filters['from_date'] . ' 00:00:00');
        if ($filters['to_date'])    $recentBuilder->where('created_at <=', $filters['to_date'] . ' 23:59:59');

        $data = [
            'title'          => 'Métricas de Comportamiento',
            'summary'        => $model->getEventsSummary($filters),
            'timeline'       => $model->getTimelineStats($filters),
            'topPages'       => $model->getTopPages($filters),
            'recentEvents'   => $recentBuilder->limit(100)->findAll(),
            'totalEvents'    => $model->getTotalEvents($filters),
            'uniqueVisitors' => $model->getUniqueVisitors($filters),
            'eventNames'     => $model->select('DISTINCT(event_name)')->findAll(),
            'filters'        => $filters
        ];

        return view('admin/metrics', $data);
    }

    /**
     * Llama a la IA para analizar los datos de comportamiento.
     */
    public function getAiAnalysis()
    {
        $model = new TrackingEventModel();
        $aiService = new OpenAiService();

        // Recogemos datos generales para dar contexto a la IA
        $summary = $model->getEventsSummary();
        $topPages = $model->getTopPages();
        $total = $model->getTotalEvents();

        $dataContext = "Total de eventos registrados: $total\n\n";
        $dataContext .= "Resumen de Eventos:\n" . json_encode($summary, JSON_PRETTY_PRINT) . "\n\n";
        $dataContext .= "Páginas más activas:\n" . json_encode($topPages, JSON_PRETTY_PRINT) . "\n";

        $prompt = [
            [
                'role' => 'system',
                'content' => 'Eres un consultor experto en CRO (Conversion Rate Optimization) y Growth Hacking para una plataforma SaaS de datos de empresas (APIEmpresas.es). Tu objetivo es analizar datos de comportamiento de usuarios y dar consejos accionables para mejorar la conversión de usuarios GRATIS a usuarios PRO (pago).'
            ],
            [
                'role' => 'user',
                'content' => "Analiza los siguientes datos de tracking de mi web y proporcióname:\n1. 3 Conclusiones clave sobre el comportamiento actual.\n2. 3 Pautas o acciones tácticas concretas para mejorar la conversión a planes de pago.\n\nDATOS:\n$dataContext\n\nResponde en formato Markdown limpio, usando negritas y listas."
            ]
        ];

        $analysis = $aiService->getChatResponse($prompt, ['temperature' => 0.5]);

        return $this->response->setJSON(['analysis' => $analysis]);
    }
}
