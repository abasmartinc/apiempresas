<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailAutomationModel extends Model
{
    protected $table      = 'user_email_automation';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    protected $allowedFields = ['user_id', 'email_type', 'message_content', 'sent_at', 'created_at'];

    /**
     * Verifica si un correo de cierto tipo ya fue enviado a un usuario.
     */
    public function wasSent(int $userId, string $emailType): bool
    {
        return $this->where('user_id', $userId)
                    ->where('email_type', $emailType)
                    ->countAllResults() > 0;
    }

    /**
     * Registra el envío de un correo automatizado.
     */
    public function markAsSent(int $userId, string $emailType, ?string $messageContent = null): bool
    {
        return $this->insert([
            'user_id'         => $userId,
            'email_type'      => $emailType,
            'message_content' => $messageContent,
            'sent_at'         => date('Y-m-d H:i:s'),
            'created_at'      => date('Y-m-d H:i:s')
        ]) !== false;
    }
}
