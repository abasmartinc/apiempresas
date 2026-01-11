<?php

namespace App\Models;

use CodeIgniter\Model;

class CompanyModel extends Model
{
    // Cambia 'api' por 'default' si corresponde
    protected $DBGroup    = 'default';
    protected $table      = 'empresia_company_details';
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
        $q = trim($cif);
        if ($q === '') return null;

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

        // (c.cif = :q:) OR (c.company_name = :q:)
        $builder->groupStart()
            ->where('c.cif', $q)
            ->orWhere('c.company_name', $q)
            ->groupEnd();

        // Si hubiera duplicados por nombre, al menos devuelves 1 de forma estable.
        // Ajusta el campo a tu PK real (id, company_id, etc.)
        $builder->orderBy('c.id', 'DESC')->limit(1);

        $row = $builder->get()->getRowArray();

        return $row ?: null;
    }

}
