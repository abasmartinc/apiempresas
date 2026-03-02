<?php

namespace App\Models;

use CodeIgniter\Model;

class UserReviewModel extends Model
{
    protected $table            = 'user_reviews';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'user_id',
        'ip_address',
        'rating',
        'comment',
        'created_at'
    ];

    protected $useTimestamps = false; // We use created_at via database default, but can manage manually if needed
    // If we want CI to handle it:
    // protected $useTimestamps = true;
    // protected $createdField  = 'created_at';
    // protected $updatedField  = '';
}
