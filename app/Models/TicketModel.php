<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketModel extends Model
{
    protected $table            = 'tickets';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'subject', 'category', 'status', 'priority', 'rating'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get tickets with user information
     */
    public function getTicketsWithUsers($status = null)
    {
        $builder = $this->select('tickets.*, users.name as user_name, users.email as user_email')
                        ->join('users', 'users.id = tickets.user_id', 'left')
                        ->orderBy('tickets.updated_at', 'DESC');
        
        if ($status) {
            $builder->where('tickets.status', $status);
        }

        return $builder->findAll();
    }
}
