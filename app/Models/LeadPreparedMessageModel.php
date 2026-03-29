<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadPreparedMessageModel extends Model
{
    protected $table      = 'lead_prepared_messages';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id', 
        'company_id', 
        'message_type', 
        'message_body', 
        'source'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Obtiene el mensaje preparado de un usuario para una empresa y tipo específicos
     */
    public function getMessage($userId, $companyId, $type = 'initial_contact')
    {
        return $this->where([
            'user_id'      => $userId, 
            'company_id'   => $companyId, 
            'message_type' => $type
        ])->first();
    }
}
