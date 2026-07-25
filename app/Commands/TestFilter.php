<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use Config\App;
use App\Filters\ApiKeyFilter;

class TestFilter extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'test:filter';
    protected $description = 'Test the ApiKeyFilter';

    public function run(array $params)
    {
        $_SERVER['HTTP_CF_IPCOUNTRY'] = 'BR';
        $_SERVER['HTTP_CF_CONNECTING_IP'] = '2.2.2.2'; // NOT IN WHITELIST
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer ak_8721c3d873a0c6d7cfc5fdfe4b309a3a'; // User 187 active key

        $config = new App();
        $uri = new URI('http://localhost/test');
        $request = new IncomingRequest($config, $uri, 'php://input', new UserAgent());
        
        $request->setHeader('Authorization', 'Bearer ak_8721c3d873a0c6d7cfc5fdfe4b309a3a');
        $request->setHeader('X-API-KEY', 'ak_8721c3d873a0c6d7cfc5fdfe4b309a3a');

        $filter = new ApiKeyFilter();
        $response = $filter->before($request);

        if ($response instanceof \CodeIgniter\HTTP\ResponseInterface) {
            CLI::write("STATUS: " . $response->getStatusCode());
            CLI::write("BODY: " . $response->getBody());
        } else {
            CLI::write("PASSED (No block)");
        }
    }
}
