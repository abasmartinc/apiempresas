<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class UnblockUser extends BaseCommand
{
    protected $group       = 'Custom';
    protected $name        = 'user:unblock';
    protected $description = 'Unblock user 166';

    public function run(array $params)
    {
        $db = \Config\Database::connect('default');
        
        $db = \Config\Database::connect('default');
        
        $keys = $db->table('api_keys')->where('user_id', 187)->get()->getResultArray();
        CLI::write("--- ALL KEYS FOR 187 ---");
        print_r($keys);
        
        // Add PL to allowed_countries if not present
        foreach ($keys as $key) {
            $allowed = $key['allowed_countries'] ? explode(',', $key['allowed_countries']) : [];
            if (!in_array('PL', $allowed)) {
                $allowed[] = 'PL';
                $db->table('api_keys')->where('id', $key['id'])->update(['allowed_countries' => implode(',', $allowed)]);
                CLI::write("Added PL to allowed countries for key {$key['id']}");
            }
        }
        
        $user = $db->table('users')->where('id', 187)->get()->getRowArray();
        CLI::write("--- USER ---");
        print_r($user);
        
        if ($user && $user['is_active'] == 0) {
            $db->table('users')->where('id', 187)->update(['is_active' => 1]);
            CLI::write("User set to active.");
        }
    }
}
