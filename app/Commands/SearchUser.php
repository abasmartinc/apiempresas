<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class SearchUser extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'user:search';
    protected $description = 'Search user in DB';

    public function run(array $params)
    {
        $db = \Config\Database::connect('default');
        $users = $db->table('users')
            ->like('email', 'lauri@immersive.inc', 'both', null, true)
            ->get()
            ->getResultArray();
        CLI::write("--- USERS FOUND ---");
        print_r($users);
    }
}
