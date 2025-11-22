<?php
namespace App\Strategies\Health;

class Reflections extends RandomGenerator
{
    protected function buildFilters($alias = 't'): string
    {
        $filters = "";

        if (isset($this->params['cats_selected'])) {
            $cats = implode(', ', $this->params['cats_selected']);
            $filters .= " AND cat.id in (".$cats.") ";
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

        return $filters;
    }

    public function getRandomRow()
    {
        $filters = $this->buildFilters();

        $query = "
            SELECT DISTINCT t.id, t.name
            FROM ng_reflections t
            INNER JOIN ng_categories cat ON cat.id = t.category_id
            INNER JOIN clients c ON c.Cli_Accessibility_id = t.accessibility_id
            WHERE c.id = {$this->params['client_id']} $filters
            AND t.category_id IN 
            (
                    SELECT DISTINCT co.category_id 
                    FROM clients_category_options cco
                    INNER JOIN ng_category_options co ON co.id = cco.category_option_id
                    WHERE cco.client_id = {$this->params['client_id']} 
            ) ORDER BY RAND() LIMIT 1
        ";

        return $this->db_service->executeQuery($query);
    }
}
