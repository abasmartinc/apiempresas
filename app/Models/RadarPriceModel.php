<?php

namespace App\Models;

use CodeIgniter\Model;

class RadarPriceModel extends Model
{
    protected $table            = 'radar_prices';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['min_count', 'max_count', 'base_price'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Get the base price for a given count.
     */
    public function getBasePrice(int $count): float
    {
        $tier = $this->where('min_count <=', $count)
                     ->where('max_count >=', $count)
                     ->first();

        if (!$tier) {
            // Check for the open-ended max tier (where max_count is 9999999)
            $tier = $this->where('min_count <=', $count)
                         ->orderBy('min_count', 'DESC')
                         ->first();
        }

        return $tier ? (float) $tier['base_price'] : 15.00;
    }
}
