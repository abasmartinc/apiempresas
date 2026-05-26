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
        $openapiPath = ROOTPATH . 'vendor/zircote/swagger-php/bin/openapi';
        $openapiPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $openapiPath);
        
        $outputPath = ROOTPATH . 'public/swagger.json';
        $outputPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $outputPath);
        
        $appPath = ROOTPATH . 'app';
        $appPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $appPath);
        
        $command = 'php ' . escapeshellarg($openapiPath) . ' --output ' . escapeshellarg($outputPath) . ' ' . escapeshellarg($appPath);
        $output = shell_exec($command);
        
        if ($output) {
            CLI::write($output, 'green');
        } else {
            CLI::write("Documentación de Swagger generada con éxito en public/swagger.json.", 'green');
        }
    }
}

