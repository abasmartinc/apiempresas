<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyRatingModel extends Model
{
    protected $table            = 'company_ratings';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    
    protected $allowedFields    = [
        'company_id',
        'rating',
        'feedback',
        'ip_address',
        'created_at'
    ];

    protected $useTimestamps = false; 

    /**
     * Check if an IP has already rated a company
     */
    public function hasRated(int $companyId, string $ipAddress): bool
    {
        $count = $this->where('company_id', $companyId)
                      ->where('ip_address', $ipAddress)
                      ->countAllResults();
        return $count > 0;
    }

    /**
     * Get rating stats for a company
     * Returns ['avg' => float, 'count' => int]
     */
    public function getRatingStats(int $companyId): array
    {
        $builder = $this->builder();
        $builder->select('AVG(rating) as avg_rating, COUNT(id) as total_ratings');
        $builder->where('company_id', $companyId);
        $result = $builder->get()->getRowArray();

        return [
            'avg' => $result ? (float)$result['avg_rating'] : 0.0,
            'count' => $result ? (int)$result['total_ratings'] : 0
        ];
    }
}
