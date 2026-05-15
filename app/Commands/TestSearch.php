<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\CompanyModel;

class TestSearch extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:search';
    protected $description = 'Tests the searchMany method.';

    public function run(array $params)
    {
        $model = new CompanyModel();
        $results = $model->searchMany('restaurante', 500, 1, true);
        CLI::write("Total data: " . count($results['data']));
        CLI::write("Has more: " . ($results['meta']['has_more'] ? 'true' : 'false'));
    }
}
