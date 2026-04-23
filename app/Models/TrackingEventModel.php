<?php

namespace App\Models;

use CodeIgniter\Model;

class TrackingEventModel extends Model
{
    protected $table            = 'tracking_events';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'event_name', 'page', 'user_id', 'session_id', 'anonymous_id', 'element', 'metadata', 'created_at'
    ];

    /**
     * Aplica filtros comunes a un builder.
     */
    private function applyFilters($builder, $filters)
    {
        if (!empty($filters['event_name'])) $builder->where('event_name', $filters['event_name']);
        if (!empty($filters['user_id']))    $builder->where('user_id', $filters['user_id']);
        if (!empty($filters['from_date']))  $builder->where('created_at >=', $filters['from_date'] . ' 00:00:00');
        if (!empty($filters['to_date']))    $builder->where('created_at <=', $filters['to_date'] . ' 23:59:59');
        return $builder;
    }

    public function getEventsSummary($filters = [])
    {
        $builder = $this->db->table($this->table);
        $builder->select('event_name, COUNT(*) as total');
        $this->applyFilters($builder, $filters);
        return $builder->groupBy('event_name')->orderBy('total', 'DESC')->get()->getResultArray();
    }

    public function getTimelineStats($filters = [])
    {
        $builder = $this->db->table($this->table);
        $builder->select("DATE(created_at) as date, COUNT(*) as total");
        $this->applyFilters($builder, $filters);
        
        if (empty($filters['from_date'])) {
            $builder->where('created_at >=', date('Y-m-d', strtotime('-15 days')));
        }

        return $builder->groupBy('date')->orderBy('date', 'ASC')->get()->getResultArray();
    }

    public function getTopPages($filters = [])
    {
        $builder = $this->db->table($this->table);
        $builder->select('page, COUNT(*) as total');
        $this->applyFilters($builder, $filters);
        return $builder->groupBy('page')->orderBy('total', 'DESC')->limit(10)->get()->getResultArray();
    }

    public function getTotalEvents($filters = [])
    {
        $builder = $this->db->table($this->table);
        $this->applyFilters($builder, $filters);
        return $builder->countAllResults();
    }

    public function getUniqueVisitors($filters = [])
    {
        $builder = $this->db->table($this->table);
        $builder->select('COUNT(DISTINCT(anonymous_id)) as total');
        $this->applyFilters($builder, $filters);
        $res = $builder->get()->getRowArray();
        return $res['total'] ?? 0;
    }

    /**
     * Cuenta usuarios con actividad en los últimos 5 minutos.
     */
    public function getActiveUsersCount()
    {
        $fiveMinsAgo = date('Y-m-d H:i:s', strtotime('-5 minutes'));
        $builder = $this->db->table($this->table);
        $builder->select('COUNT(DISTINCT(anonymous_id)) as total');
        $builder->where('created_at >=', $fiveMinsAgo);
        $res = $builder->get()->getRowArray();
        return $res['total'] ?? 0;
    }
}
