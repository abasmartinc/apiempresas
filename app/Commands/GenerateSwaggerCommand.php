<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class GenerateSwaggerCommand extends BaseCommand
{
    protected $group       = 'Swagger';
    protected $name        = 'swagger:generate';
    protected $description = 'Genera la documentación de Swagger para la API.';

    public function run(array $params)
    {
        $output = shell_exec('php vendor/bin/openapi --output public/swagger.json app');
        CLI::write($output, 'green');
    }
}

