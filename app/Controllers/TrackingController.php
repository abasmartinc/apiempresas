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
     * Verifica si la IP actual debe ser ignorada para el tracking
     */
    private function isIpIgnored(?string $ip): bool
    {
        if (!$ip) return false;
        
        $ignoredIpsStr = env('TRACKING_IGNORE_IPS', '127.0.0.1,::1');
        if (empty(trim($ignoredIpsStr))) {
            return false;
        }
        $ignoredIps = array_filter(array_map('trim', explode(',', $ignoredIpsStr)));
        return in_array($ip, $ignoredIps);
    }

    /**
     * Procesa y guarda un evento de la demo del Radar
     * POST /tracking/radar-demo-event
     */
    public function processRadarEvent()
    {
        try {
            $clientIp = $this->request->getIPAddress();
            if ($this->isIpIgnored($clientIp)) {
                return $this->response->setJSON(['success' => true, 'ignored' => true]);
            }

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
            $clientIp = $this->request->getIPAddress();
            if ($this->isIpIgnored($clientIp)) {
                return $this->response->setJSON(['success' => true, 'ignored' => true]);
            }

            // Aceptar tanto JSON como POST estándar para máxima compatibilidad
            $data_raw = $this->request->getJSON(true);
            if (!$data_raw) {
                $data_raw = $this->request->getPost();
            }
            
            // Normalizar nombres de campos (soporte para event_type y event_name)
            $eventName = $data_raw['event_name'] ?? $data_raw['event_type'] ?? null;
            
            if (!$eventName) {
                return $this->response->setJSON(['success' => false, 'error' => 'Missing event name'])->setStatusCode(400);
            }

            // Gestionar IDs de sesión/visitante si no vienen
            $sessionId = $data_raw['session_id'] ?? session_id();
            $anonymousId = $data_raw['anonymous_id'] ?? session('radar_visitor_id') ?? md5($clientIp . $this->request->getUserAgent()->getAgentString());

            $trackingModel = new TrackingEventModel();

            $data = [
                'event_name'   => substr($eventName, 0, 100),
                'page'         => substr($data_raw['page'] ?? $this->request->getServer('HTTP_REFERER') ?? '', 0, 255),
                'user_id'      => $data_raw['user_id'] ?? session('user_id'),
                'session_id'   => substr($sessionId, 0, 100),
                'anonymous_id' => substr($anonymousId, 0, 100),
                'element'      => substr($data_raw['element'] ?? $data_raw['source'] ?? '', 0, 255),
                'metadata'     => isset($data_raw['metadata']) ? (is_array($data_raw['metadata']) ? json_encode($data_raw['metadata']) : $data_raw['metadata']) : null,
                'created_at'   => date('Y-m-d H:i:s'),
            ];

            // Si no hay metadata pero hay source, lo guardamos en metadata si element está ocupado o viceversa
            if (!$data['metadata'] && isset($data_raw['source'])) {
                $data['metadata'] = json_encode(['source' => $data_raw['source']]);
            }

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
