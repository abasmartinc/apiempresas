<?php

namespace App\Models;

use CodeIgniter\Model;

class AiCopilotLogModel extends Model
{
    protected $table            = 'ai_copilot_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'ip_address',
        'company_cif',
        'product_input',
        'ai_response_json',
        'score',
        'feedback_score',
        'created_at'
    ];

    // Dates
    protected $useTimestamps = false; // We use created_at with default current_timestamp in DB
}
