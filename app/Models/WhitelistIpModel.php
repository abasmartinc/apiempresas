<?php

namespace App\Models;

use CodeIgniter\Model;

class WhitelistIpModel extends Model
{
    protected $table            = 'api_whitelist_ips';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'object';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['user_id', 'ip_address', 'description'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get whitelisted IPs for a specific user
     */
    public function getIpsByUser($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }

    /**
     * Check if a specific IP is whitelisted for a user
     */
    public function isIpWhitelisted($userId, $ipAddress)
    {
        $count = $this->where('user_id', $userId)
                      ->where('ip_address', $ipAddress)
                      ->countAllResults();
        
        return $count > 0;
    }
}
