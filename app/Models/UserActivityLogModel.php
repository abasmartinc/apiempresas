<?php

namespace App\Models;

use CodeIgniter\Model;

class UserActivityLogModel extends Model
{
    protected $table      = 'user_activity_logs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    
    protected $allowedFields = [
        'user_id', 'action', 'details', 'ip_address', 'user_agent', 'created_at'
    ];
    
    protected $useTimestamps = false;
    
    /**
     * Log a user activity
     */
    public function logActivity(int $userId, string $action, array $details = []): bool
    {
        $data = [
            'user_id'    => $userId,
            'action'     => $action,
            'details'    => !empty($details) ? json_encode($details) : null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        return $this->insert($data) !== false;
    }
    
    /**
     * Get activity for a specific user
     */
    public function getUserActivity(int $userId, int $limit = 50): array
    {
        return $this->where('user_id', $userId)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
    
    /**
     * Get all activities of a specific type
     */
    public function getActivityByAction(string $action, int $limit = 100): array
    {
        return $this->where('action', $action)
                    ->orderBy('created_at', 'DESC')
                    ->limit($limit)
                    ->findAll();
    }
    
    /**
     * Get recent activity across all users
     */
    public function getRecentActivity(int $limit = 100, array $filters = []): array
    {
        $builder = $this->select('user_activity_logs.*, users.name as user_name, users.email as user_email')
                        ->join('users', 'users.id = user_activity_logs.user_id', 'left')
                        ->orderBy('user_activity_logs.created_at', 'DESC');
        
        // Apply filters
        if (!empty($filters['user_id'])) {
            $builder->where('user_activity_logs.user_id', $filters['user_id']);
        }
        
        if (!empty($filters['action'])) {
            $builder->where('user_activity_logs.action', $filters['action']);
        }
        
        if (!empty($filters['from_date'])) {
            $builder->where('user_activity_logs.created_at >=', $filters['from_date']);
        }
        
        if (!empty($filters['to_date'])) {
            $builder->where('user_activity_logs.created_at <=', $filters['to_date'] . ' 23:59:59');
        }
        
        return $builder->limit($limit)->findAll();
    }
    
    /**
     * Get activity statistics for a date range
     */
    public function getActivityStats(string $from, string $to): array
    {
        $result = $this->select('action, COUNT(*) as count')
                       ->where('created_at >=', $from)
                       ->where('created_at <=', $to . ' 23:59:59')
                       ->groupBy('action')
                       ->orderBy('count', 'DESC')
                       ->findAll();
        
        return $result;
    }
    
    /**
     * Get unique actions list
     */
    public function getUniqueActions(): array
    {
        $result = $this->distinct()
                       ->select('action')
                       ->orderBy('action', 'ASC')
                       ->findAll();
        
        return array_column($result, 'action');
    }
}
