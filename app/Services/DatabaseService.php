<?php
namespace App\Services;

use CodeIgniter\Database\ConnectionInterface;

class DatabaseService
{ 
    protected $db;

    public function __construct(ConnectionInterface &$db)
    {
        $this->db = $db;
    }

    /**
     * Obtiene todos los registros de una tabla
     */
    public function getAll(string $table, array $conditions = [])
    {
        $query = $this->db->table($table);

        if (!empty($conditions)) {
            $query->where($conditions);
        }

        return $query->get()->getResult();
    }

    /**
     * Obtiene un registro por ID de una tabla
     */
    public function getById(string $table, int $id)
    {
        return $this->db->table($table)
            ->where('id', $id)
            ->get()
            ->getRow();
    }

    /**
     * Inserta datos en una tabla
     */
    public function insert(string $table, array $data)
    {
        $this->db->table($table)->insert($data);
        return $this->db->insertID();
    }

    /**
     * Actualiza datos en una tabla
     */
    public function update(string $table, int $id, array $data)
    {
        return $this->db->table($table)
            ->where('id', $id)
            ->update($data);
    }

    /**
     * Elimina un registro de una tabla
     */
    public function delete(string $table, int $id)
    {
        return $this->db->table($table)
            ->where('id', $id)
            ->delete();
    }

    public function executeQuery(string $sql, array $params = [])
    {
        $query = $this->db->query($sql, $params);

        // Verificar el tipo de resultado y devolver en consecuencia
        if ($query->resultID !== false) {
            return $query->getRowArray();
        }

        return $query;
    }
}

