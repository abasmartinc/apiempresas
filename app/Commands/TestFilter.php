<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class TestFilter extends BaseCommand
{
    protected $group       = 'Testing';
    protected $name        = 'test:filter';
    protected $description = 'Tests the ApiKeyFilter for a specific API Key.';

    public function run(array $params)
    {
        $apiKey = $params[0] ?? 'ak_32e3a5f48f3aa510d25d26fa66a3b6ac';

        $db = \Config\Database::connect('default');

        CLI::write("Testing API Key: $apiKey");

        $filter = new \App\Filters\ApiKeyFilter();
        $request = \Config\Services::request();
        // Clear headers and set X-API-KEY
        $request->setHeader('X-API-KEY', $apiKey);

        $response = $filter->before($request);

        if ($response instanceof \CodeIgniter\HTTP\ResponseInterface) {
            CLI::error("Filter BLOCKED the request!");
            CLI::write("Status Code: " . $response->getStatusCode());
            CLI::write("Body: " . $response->getBody());
        } else {
            CLI::write("Filter PASSED the request!");
        }

        CLI::write("\n=== ApiKeyFilter::\$apiMeta ===");
        foreach (\App\Filters\ApiKeyFilter::$apiMeta as $key => $val) {
            CLI::write("  $key: " . var_export($val, true));
        }
    }
}
