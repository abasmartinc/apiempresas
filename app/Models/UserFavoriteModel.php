<?php

namespace App\Models;

use CodeIgniter\Model;

class UserFavoriteModel extends Model
{
    protected $table      = 'user_favorites';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['user_id', 'company_id', 'notes', 'status'];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Verifica si una empresa es favorita para un usuario
     */
    public function isFavorite($userId, $companyId)
    {
        return $this->where(['user_id' => $userId, 'company_id' => $companyId])->countAllResults() > 0;
    }

    /**
     * Obtiene los IDs de las empresas favoritas de un usuario
     */
    public function getFavoriteIds($userId)
    {
        return $this->where('user_id', $userId)->findColumn('company_id') ?? [];
    }

    /**
     * Obtiene el listado de empresas favoritas con datos de la empresa
     */
    public function getFavoritesWithCompanyData($userId)
    {
        return $this->select('user_favorites.*, companies.company_name, companies.cif, companies.fecha_constitucion, companies.municipality')
                    ->join('companies', 'companies.id = user_favorites.company_id')
                    ->where('user_favorites.user_id', $userId)
                    ->orderBy('user_favorites.created_at', 'DESC')
                    ->findAll();
    }
}
