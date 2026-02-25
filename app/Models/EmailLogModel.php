<?php

namespace App\Models;

use CodeIgniter\Model;

class EmailLogModel extends Model
{
    protected $table = 'email_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useTimestamps = false; // We handle created_at manually or via DB default

    protected $allowedFields = [
        'user_id',
        'subject',
        'message',
        'status',
        'error_message',
        'tracking_code',
        'opened_at',
        'clicked_at',
        'logged_in_at',
        'created_at'
    ];
}
