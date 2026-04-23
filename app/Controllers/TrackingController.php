<?php

namespace App\Controllers;

use App\Models\RadarDemoEventModel;
use App\Models\TrackingEventModel;
use CodeIgniter\API\ResponseTrait;

class TrackingController extends BaseController
{
    use ResponseTrait;

    protected $eventModel;

    public function __construct()
    {
        $this->eventModel = new RadarDemoEventModel();
    }

    /**
     * Procesa y guarda un evento de la demo del Radar
     * POST /tracking/radar-demo-event
     */
    public function processRadarEvent()
    {
        try {
            $json = $this->request->getJSON(true);
            
            // Log de entrada para ver qué llega exactamente
            log_message('error', '[TrackingDebug] Datos recibidos: ' . json_encode($json));
            
            if (!$json) {
                log_message('error', '[TrackingDebug] JSON nulo o inválido');
                return $this->response->setJSON(['success' => false, 'error' => 'Invalid JSON'])->setStatusCode(400);
            }

            // 1. Whitelists de Seguridad
            $allowedEvents = ['click_cta', 'view_strategy', 'page_view'];
            
            $eventType = $json['event_type'] ?? 'unknown';
            $source    = $json['source'] ?? 'unknown';

            if (!in_array($eventType, $allowedEvents)) {
                log_message('error', '[TrackingDebug] Evento no permitido: ' . $eventType);
                return $this->response->setJSON(['success' => false, 'error' => 'Event type not allowed'])->setStatusCode(400);
            }

            // 2. Gestionar Identificación (Visitor ID)
            $session = session();
            if (!$session->has('radar_visitor_id')) {
                $session->set('radar_visitor_id', bin2hex(random_bytes(16)));
            }
            $visitorId = $session->get('radar_visitor_id');

            // 3. Capturar Metadatos del Entorno
            $data = [
                'visitor_id'    => $visitorId,
                'user_id'       => $session->get('user_id'),
                'event_type'    => $eventType,
                'source'        => $source,
                'page'          => $json['page'] ?? 'radar-demo',
                'cta_label'     => substr($json['cta_label'] ?? '', 0, 150),
                'url'           => substr($json['url'] ?? '', 0, 255),
                'referrer'      => substr($this->request->getServer('HTTP_REFERER') ?? '', 0, 255),
                'ip_address'    => $this->request->getIPAddress(),
                'user_agent'    => substr($this->request->getUserAgent()->getAgentString(), 0, 500),
                'metadata_json' => isset($json['metadata']) ? json_encode($json['metadata']) : null,
            ];

            // 4. Guardar en Base de Datos
            if ($this->eventModel->insert($data)) {
                log_message('error', '[TrackingDebug] Registro guardado con éxito. ID: ' . $this->eventModel->getInsertID());
                return $this->response->setJSON(['success' => true]);
            } else {
                log_message('error', '[TrackingDebug] Error al insertar en BD: ' . json_encode($this->eventModel->errors()));
                return $this->response->setJSON(['success' => false, 'error' => 'DB insert failed'])->setStatusCode(500);
            }

        } catch (\Throwable $e) {
            log_message('error', '[TrackingDebug] EXCEPCIÓN: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            return $this->response->setJSON(['success' => false, 'error' => 'Exception occurred'])->setStatusCode(500);
        }
    }

    /**
     * Sistema de tracking global
     * POST /api/tracking/event
     */
    public function logEvent()
    {
        try {
            $json = $this->request->getJSON(true);
            
            // Debug log
            log_message('error', '[TrackingGlobal] Hit: ' . json_encode($json));
            
            if (!$json || empty($json['event_name'])) {
                return $this->response->setJSON(['success' => false, 'error' => 'Missing event_name'])->setStatusCode(400);
            }

            $trackingModel = new TrackingEventModel();

            $data = [
                'event_name'   => substr($json['event_name'], 0, 100),
                'page'         => substr($json['page'] ?? '', 0, 255),
                'user_id'      => $json['user_id'] ?? null,
                'session_id'   => substr($json['session_id'] ?? '', 0, 100),
                'anonymous_id' => substr($json['anonymous_id'] ?? '', 0, 100),
                'element'      => substr($json['element'] ?? '', 0, 255),
                'metadata'     => isset($json['metadata']) ? json_encode($json['metadata']) : null,
                'created_at'   => date('Y-m-d H:i:s'),
            ];

            if ($trackingModel->insert($data)) {
                return $this->response->setJSON(['success' => true]);
            }

            return $this->response->setJSON(['success' => false, 'error' => 'Insert failed'])->setStatusCode(500);

        } catch (\Throwable $e) {
            log_message('error', '[TrackingGlobal] Exception: ' . $e->getMessage());
            return $this->response->setJSON(['success' => false, 'error' => 'Server error'])->setStatusCode(500);
        }
    }
}
