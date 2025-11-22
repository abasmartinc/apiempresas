<?php
namespace App\Strategies\Health;

class RandomGenerator
{
    protected $db_service;
    protected $params = [];

    public function __construct($db_service)
    {
        $this->db_service = $db_service;
    }

    public function using(array $params)
    {
        $this->params = array_merge([
            'table' => null,
            'category' => null,
            'avoid' => null,
            'client_id' => null
        ], $params);

        return $this;
    }

    public function generate()
    {
        $row = $this->getRandomRow();
        if (!empty($row) && isset($row['used_count']))
        {
            $id = isset($row['ID']) ? $row['ID'] : $row['id'];
            $new_used_count = $row['used_count'];
            $new_used_count++;
            $new_datetime = date('Y-m-d H:i:s');

            $this->db_service->update($this->params['table'], $id, [
                'used_count' => $new_used_count,
                'used_last_date' => $new_datetime
            ]);
        }

        return $row ? $this->getNameFieldValue($row) : '';
    }

    protected function getNameFieldValue($obj): string
    {
        $values = array_values($obj);
        return $values[1] ?? '';
    }

    protected function getRandomRow()
    {
        $filters = $this->buildFilters();
        $filters_subquery = $this->buildFilters('sub');
        $query = "
        SELECT * FROM
        (
            SELECT * FROM 
            (
                SELECT t.* FROM 
                {$this->params['table']} t 
                WHERE 1=1 $filters 
                        AND t.used_count <= (SELECT MIN(used_count) FROM {$this->params['table']} sub WHERE 1=1 $filters_subquery) 
            ) Q ORDER BY RAND() LIMIT 2
        ) F ORDER BY used_last_date ASC LIMIT 1
        ";

        return $this->db_service->executeQuery($query);
    }    

    protected function buildFilters($alias = 't'): string
    {
        $filters = '';

        if ($this->params['category']) {
            $filters .= " AND $alias.category_id = {$this->params['category']}";
        }

        if (!empty($this->params['avoid'])) {
            $field = $this->params['avoid']['field'];
            $values = $this->params['avoid']['values'];

            if (is_array($values)) {
                $escapedValues = array_map(function($value) {
                    return "'" . addslashes($value) . "'";
                }, $values);
                $filters .= " AND $alias.{$field} NOT IN (" . implode(', ', $escapedValues) . ")";
            } else {
                $filters .= " AND $alias.{$field} <> '" . addslashes($values) . "'";
            }
        }

        if ($this->params['client_id']) {
            $filters .= " AND $alias.id IN (SELECT DISTINCT category_option_id FROM clients_category_options WHERE client_id = {$this->params['client_id']})";
        }

        return $filters;
    }
}
