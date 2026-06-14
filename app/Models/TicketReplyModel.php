<?php

namespace App\Models;

use CodeIgniter\Model;

class TicketReplyModel extends Model
{
    protected $table            = 'ticket_replies';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['ticket_id', 'user_id', 'is_admin', 'message', 'is_private', 'attachment'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get replies for a ticket with sender info
     */
    public function getRepliesWithSender($ticketId)
    {
        return $this->select('ticket_replies.*, users.name as sender_name, users.email as sender_email')
                    ->join('users', 'users.id = ticket_replies.user_id', 'left')
                    ->where('ticket_id', $ticketId)
                    ->orderBy('ticket_replies.created_at', 'ASC')
                    ->findAll();
    }
}
