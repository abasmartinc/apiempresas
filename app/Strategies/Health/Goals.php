<?php
namespace App\Strategies\Health;

class Goals extends RandomGenerator
{
    protected function buildFilters($alias = 't'): string
    {
        $filters = "";

        if (isset($this->params['cats_selected'])) {
            $cats = implode(', ', $this->params['cats_selected']);
            $filters .= " AND cat.id in (".$cats.") ";
        }

        return $filters;
    }

    public function getRandomRow()
    {
        $filters = $this->buildFilters();

        $query = "
            SELECT DISTINCT gct.id, gct.ng_goal_met
            FROM ng_goals_categories_texts gct
            INNER JOIN ng_categories cat ON cat.id = gct.ng_goal_categories_id
            INNER JOIN clients_goals cg ON cg.goal_id = gct.ng_goal_id
            INNER JOIN clients c ON cg.client_id = c.id AND c.Cli_Accessibility_id = gct.ng_accessibility_id
            WHERE c.id = {$this->params['client_id']} $filters
            AND gct.ng_goal_categories_id IN 
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
