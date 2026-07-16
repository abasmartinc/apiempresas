<?php
namespace App\Commands;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckFCPATH extends BaseCommand
{
    protected $group = 'Debug';
    protected $name = 'debug:fcpath';
    protected $description = 'Check FCPATH.';

    public function run(array $params)
    {
        CLI::write("FCPATH is: " . FCPATH);
    }
}
