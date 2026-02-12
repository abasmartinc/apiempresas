<?php

namespace App\Models;

use CodeIgniter\Model;

class SystemStatsModel extends Model
{
    protected $table         = 'system_stats';
    protected $primaryKey    = 'stat_key';
    protected $returnType    = 'object';
    protected $allowedFields = ['stat_key', 'stat_value', 'updated_at'];

    public function getStat($key)
    {
        return $this->find($key);
    }

    public function setStat($key, $value)
    {
        $data = [
            'stat_key'   => $key,
            'stat_value' => is_array($value) ? json_encode($value) : $value,
        ];
        
        if ($this->find($key)) {
            return $this->update($key, $data);
        } else {
            return $this->insert($data);
        }
    }
}
