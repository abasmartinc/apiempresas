<?php

namespace App\Models;

use CodeIgniter\Model;

class UserTriggerEventsModel extends Model
{
    protected $table      = 'user_trigger_events';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = [
        'user_id', 'trigger_type', 'shown_at'
    ];

    public function markAsShown(int $userId, string $triggerType)
    {
        $data = [
            'user_id'      => $userId,
            'trigger_type' => $triggerType,
            'shown_at'     => date('Y-m-d H:i:s')
        ];

        return $this->insert($data);
    }

    public function hasBeenShown(int $userId, string $triggerType): bool
    {
        return $this->where([
            'user_id'      => $userId,
            'trigger_type' => $triggerType
        ])->countAllResults() > 0;
    }
}
