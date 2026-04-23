<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Services\EmailService;

class TestEmail extends BaseCommand
{
    protected $group       = 'Test';
    protected $name        = 'email:test';
    protected $description = 'Envía un email de prueba a papelo.amh@gmail.com';

    public function run(array $params)
    {
        $service = new EmailService();
        $target = 'papelo.amh@gmail.com';
        
        CLI::write("Enviando email de prueba a $target...", 'cyan');
        
        $userData = [
            'name'  => 'Usuario de Prueba',
            'email' => $target
        ];

        if ($service->sendQuickStartPrompt($userData)) {
            CLI::write("¡ÉXITO! El email debería estar en tu bandeja de entrada.", 'green');
        } else {
            CLI::error("FALLO. Revisa los logs en writable/logs/ para ver el error de SMTP.");
        }
    }
}
