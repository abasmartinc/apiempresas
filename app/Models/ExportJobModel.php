<?php

namespace App\Models;

use CodeIgniter\Model;

class ExportJobModel extends Model
{
    protected $table = 'export_jobs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id',
        'type',
        'context',
        'status',
        'file_path',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}
