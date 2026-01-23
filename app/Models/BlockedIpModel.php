<?php

namespace App\Models;

use CodeIgniter\Model;

class BlockedIpModel extends Model
{
    protected $table            = 'blocked_ips';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'ip_address',
        'reason',
        'blocked_at',
        'user_agent',
        'request_count',
        'last_attempt_at',
        'meta',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'ip_address' => 'required|max_length[45]',
        'reason'     => 'required|max_length[255]',
        'blocked_at' => 'required|valid_date',
    ];

    protected $validationMessages = [
        'ip_address' => [
            'required' => 'IP address is required',
        ],
        'reason' => [
            'required' => 'Blocking reason is required',
        ],
    ];

    /**
     * Check if an IP address is currently blocked
     *
     * @param string $ip
     * @return bool
     */
    public function isBlocked(string $ip): bool
    {
        $result = $this->where('ip_address', $ip)->first();
        return $result !== null;
    }

    /**
     * Block an IP address
     *
     * @param string $ip
     * @param string $reason
     * @param array $meta Additional metadata
     * @return bool
     */
    public function blockIp(string $ip, string $reason, array $meta = []): bool
    {
        // Check if already blocked
        if ($this->isBlocked($ip)) {
            return true; // Already blocked
        }

        $data = [
            'ip_address'  => $ip,
            'reason'      => $reason,
            'blocked_at'  => date('Y-m-d H:i:s'),
            'user_agent'  => $meta['user_agent'] ?? null,
            'meta'        => !empty($meta) ? json_encode($meta) : null,
        ];

        try {
            return $this->insert($data) !== false;
        } catch (\Throwable $e) {
            log_message('error', '[BlockedIpModel] Failed to block IP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Record an attempt from a blocked IP
     *
     * @param string $ip
     * @return void
     */
    public function recordAttempt(string $ip): void
    {
        try {
            $this->where('ip_address', $ip)
                 ->set('request_count', 'request_count + 1', false)
                 ->set('last_attempt_at', date('Y-m-d H:i:s'))
                 ->update();
        } catch (\Throwable $e) {
            log_message('error', '[BlockedIpModel] Failed to record attempt: ' . $e->getMessage());
        }
    }

    /**
     * Unblock an IP address (for manual intervention)
     *
     * @param string $ip
     * @return bool
     */
    public function unblockIp(string $ip): bool
    {
        try {
            return $this->where('ip_address', $ip)->delete() !== false;
        } catch (\Throwable $e) {
            log_message('error', '[BlockedIpModel] Failed to unblock IP: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get blocked IP details
     *
     * @param string $ip
     * @return array|null
     */
    public function getBlockedIpDetails(string $ip): ?array
    {
        return $this->where('ip_address', $ip)->first();
    }

    /**
     * Get all blocked IPs with pagination
     *
     * @param int $perPage
     * @return array
     */
    public function getBlockedIps(int $perPage = 50): array
    {
        return $this->orderBy('blocked_at', 'DESC')
                    ->paginate($perPage);
    }

    /**
     * Get statistics about blocked IPs
     *
     * @return array
     */
    public function getStats(): array
    {
        $db = $this->db;
        
        return [
            'total_blocked' => $this->countAll(),
            'blocked_today' => $this->where('DATE(blocked_at)', date('Y-m-d'))->countAllResults(),
            'top_reasons' => $db->query(
                "SELECT reason, COUNT(*) as count 
                 FROM {$this->table} 
                 GROUP BY reason 
                 ORDER BY count DESC 
                 LIMIT 10"
            )->getResultArray(),
        ];
    }
}
