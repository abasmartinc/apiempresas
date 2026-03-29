<?php

namespace App\Models;

use CodeIgniter\Model;

class LeadFollowupModel extends Model
{
    protected $table      = 'lead_followups';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id', 
        'company_id', 
        'status', 
        'notify_when_contact', 
        'notes', 
        'prepared_at', 
        'contacted_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Obtiene el seguimiento de un usuario para una empresa específica
     */
    public function getFollowup($userId, $companyId)
    {
        return $this->where(['user_id' => $userId, 'company_id' => $companyId])->first();
    }
}
