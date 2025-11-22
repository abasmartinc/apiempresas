<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    // Cambia 'api' por 'default' si corresponde
    protected $DBGroup    = 'default';
    protected $table      = 'companies';
    protected $primaryKey = 'id';
    protected $returnType = 'array';

    /**
     * Devuelve una empresa por CIF con alias en los campos
     *
     * @param string $cif
     * @return array|null
     */
    public function getCompanyByCif(string $cif): ?array
    {
        $cif = trim($cif);

        $builder = $this->db->table($this->table . ' c');

        $builder->select([
            'c.company_name        AS name',
            'c.cif                 AS cif',
            'c.cnae_code           AS cnae',
            'c.cnae_label          AS cnae_label',
            'c.objeto_social       AS corporate_purpose',
            'c.fecha_constitucion  AS founded',
            'c.registro_mercantil  AS province',
            'c.estado              AS status',
        ]);

        $builder->where('c.cif', $cif);

        $row = $builder->get()->getRowArray();

        return $row ?: null;
    }
}
