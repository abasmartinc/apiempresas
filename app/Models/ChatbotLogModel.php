<?php

namespace App\Models;

use CodeIgniter\Model;

class ChatbotLogModel extends Model
{
    protected $table      = 'chatbot_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    
    protected $allowedFields = [
        'user_id', 'session_id', 'query', 'response', 'created_at'
    ];
    
    protected $useTimestamps = false;
    
    /**
     * Log a chatbot interaction
     */
    public function logChat(?int $userId, string $sessionId, string $query, string $response): bool
    {
        $data = [
            'user_id'    => $userId,
            'session_id' => $sessionId,
            'query'      => $query,
            'response'   => $response,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($data) !== false;
    }
}
