<?php

namespace App\Models;

use CodeIgniter\Model;

class UserEventsModel extends Model
{
    protected $table      = 'user_events';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id', 'event_type', 'trigger_type', 'created_at'
    ];

    public function logEvent(int $userId, string $eventType, ?string $triggerType = null)
    {
        $data = [
            'user_id'      => $userId,
            'event_type'   => $eventType,
            'trigger_type' => $triggerType,
            'created_at'   => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }
}
