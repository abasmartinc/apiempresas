<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyAdminModel extends Model
{
    protected $table         = 'empresia_company_details';
    protected $primaryKey    = 'id';
    protected $returnType    = 'object';
    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    protected $allowedFields = [
        'company_url_id',
        'company_name',
        'address',
        'lat',
        'long',
        'phone',
        'phone_mobile',
        'slug',
        'url',
        'cif',
        'objeto_social',
        'cnae_code',
        'cnae_label',
        'fecha_constitucion',
        'duracion_raw',
        'capital_social_raw',
        'ventas_raw',
        'registro_mercantil',
        'municipality',
        'ult_cuentas_anio',
        'estado',
        'estado_fecha',
        'processed'
    ];

    /**
     * Búsqueda optimizada para el admin
     */
    public function searchAdmin(?string $q, int $perPage = 20)
    {
        $builder = $this->builder();
        
        if ($q) {
            $q = trim($q);
            $builder->groupStart()
                ->where('cif', $q)
                ->orLike('company_name', $q, 'both')
            ->groupEnd();
        }

        return $this->orderBy('id', 'DESC')->paginate($perPage);
    }
}
