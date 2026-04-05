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
     * Obtiene un mapa de [company_id => status] para un usuario
     */
    public function getFavoriteMap($userId)
    {
        $favorites = $this->where('user_id', $userId)
                          ->select('company_id, status')
                          ->findAll();
        
        $map = [];
        foreach ($favorites as $f) {
            $map[$f['company_id']] = $f['status'] ?: 'nuevo';
        }
        return $map;
    }

    /**
     * Obtiene el listado de empresas favoritas con datos de la empresa, filtros y paginación
     */
    public function getFavoritesWithCompanyData($userId, $params = [])
    {
        $builder = $this->builder();
        $builder->select('user_favorites.*, companies.company_name, companies.cif, companies.fecha_constitucion, companies.municipality, companies.objeto_social')
                ->join('companies', 'companies.id = user_favorites.company_id')
                ->where('user_favorites.user_id', $userId);

        // Filtro por Estado
        if (!empty($params['status']) && $params['status'] !== 'all') {
            $builder->where('user_favorites.status', $params['status']);
        }

        // Filtro por Búsqueda (Nombre o CIF)
        if (!empty($params['search'])) {
            $search = $params['search'];
            $builder->groupStart()
                    ->like('companies.company_name', $search)
                    ->orLike('companies.cif', $search)
                    ->groupEnd();
        }

        // Ordenación
        $builder->orderBy('user_favorites.status', 'ASC')
                ->orderBy('user_favorites.created_at', 'DESC');

        // Paginación
        if (isset($params['limit']) && isset($params['offset'])) {
            return $builder->get($params['limit'], $params['offset'])->getResultArray();
        }

        return $builder->get()->getResultArray();
    }

    /**
     * Cuenta el total de favoritos filtrados
     */
    public function countFilteredFavorites($userId, $params = [])
    {
        $builder = $this->builder();
        $builder->where('user_favorites.user_id', $userId);

        if (!empty($params['status']) && $params['status'] !== 'all') {
            $builder->where('user_favorites.status', $params['status']);
        }

        if (!empty($params['search'])) {
            $search = $params['search'];
            $builder->join('companies', 'companies.id = user_favorites.company_id')
                    ->groupStart()
                    ->like('companies.company_name', $search)
                    ->orLike('companies.cif', $search)
                    ->groupEnd();
        }

        return $builder->countAllResults();
    }
}
