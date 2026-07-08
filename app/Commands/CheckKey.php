<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckKey extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'check:key';
    protected $description = 'Check API key';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $keys = $db->table('api_keys')->where('user_id', 166)->get()->getResultArray();
        print_r($keys);
    }
}
