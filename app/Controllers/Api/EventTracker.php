<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\UserEventsModel;
use App\Models\UserTriggerEventsModel;
use CodeIgniter\API\ResponseTrait;

class EventTracker extends BaseController
{
    use ResponseTrait;

    protected $userEventsModel;
    protected $userTriggerEventsModel;

    public function __construct()
    {
        $this->userEventsModel = new UserEventsModel();
        $this->userTriggerEventsModel = new UserTriggerEventsModel();
    }

    public function log()
    {
        if (!session('logged_in')) {
            return $this->failUnauthorized();
        }

        $userId = (int) session('user_id');
        $eventType = $this->request->getPost('event_type');
        $triggerType = $this->request->getPost('trigger_type');

        if (!$eventType) {
            return $this->failValidationError('El tipo de evento es obligatorio.');
        }

        // Registrar en tabla de eventos (para analitica)
        $this->userEventsModel->logEvent($userId, $eventType, $triggerType);

        // Si es 'trigger_shown', marcarlo en la tabla de control de frecuencia para no volver a mostrarlo
        if ($eventType === 'trigger_shown' && $triggerType) {
            $this->userTriggerEventsModel->markAsShown($userId, $triggerType);
            
            // Lógica de Presión Progresiva: 
            // Si mostramos uno alto, marcamos los inferiores como vistos para que nunca retroceda.
            $levels = ['first_use', '20_percent', '50_percent', '80_percent'];
            $currentIndex = array_search($triggerType, $levels);
            
            if ($currentIndex !== false) {
                for ($i = 0; $i < $currentIndex; $i++) {
                    if (!$this->userTriggerEventsModel->hasBeenShown($userId, $levels[$i])) {
                        $this->userTriggerEventsModel->markAsShown($userId, $levels[$i]);
                    }
                }
            }
        }

        return $this->respond(['success' => true]);
    }
}
