<?php

namespace App\Models;

use CodeIgniter\Model;

class ApikeysModel extends Model
{
    protected $DBGroup       = 'default'; // asegúrate que sea el grupo correcto
    protected $table         = 'api_keys';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id',
        'name',
        'api_key',
        'is_active',
        'last_used_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
}
