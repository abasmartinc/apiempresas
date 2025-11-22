<?php


namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $DBGroup    = 'default';
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'name',
        'company',
        'email',
        'password_hash',
        'api_key',
        'is_active',
        'created_at',
        'updated_at',
        'last_login_at',
    ];

    protected $useTimestamps = false; // gestionamos created_at/updated_at manualmente
}
