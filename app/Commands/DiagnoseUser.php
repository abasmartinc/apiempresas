<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class DiagnoseUser extends BaseCommand
{
    protected $group       = 'Diagnostics';
    protected $name        = 'diagnose:user';
    protected $description = 'Diagnoses user plan and subscription issues.';

    public function run(array $params)
    {
        $db = \Config\Database::connect('default');

        CLI::write("=== USER 286 ===");
        $user = $db->table('users')->where('id', 286)->get()->getRow();
        if ($user) {
            foreach ((array)$user as $key => $val) {
                CLI::write("  $key: $val");
            }
        } else {
            CLI::error("User 286 not found.");
        }

        CLI::write("\n=== API KEYS FOR USER 286 ===");
        $keys = $db->table('api_keys')->where('user_id', 286)->get()->getResult();
        foreach ($keys as $key) {
            CLI::write("  Key ID: {$key->id} | Key: {$key->api_key} | Active: {$key->is_active} | Last Used: {$key->last_used_at}");
        }

        CLI::write("\n=== API PLANS ===");
        $plans = $db->table('api_plans')->get()->getResult();
        foreach ($plans as $plan) {
            CLI::write("  ID: {$plan->id} | Name: {$plan->name} | Slug: {$plan->slug} | Quota: {$plan->monthly_quota} | Type: {$plan->product_type}");
        }

        CLI::write("\n=== USER SUBSCRIPTIONS (user_subscriptions) ===");
        if ($db->tableExists('user_subscriptions')) {
            $subs = $db->table('user_subscriptions')->where('user_id', 286)->get()->getResult();
            foreach ($subs as $sub) {
                CLI::write("  Subscription details:");
                foreach ((array)$sub as $col => $val) {
                    CLI::write("    $col: $val");
                }
            }
            
            CLI::write("\n=== GOOD SUBSCRIPTIONS (current_period_start != current_period_end) ===");
            $goodSubs = $db->table('user_subscriptions')
                ->select('user_subscriptions.*, users.email')
                ->join('users', 'users.id = user_subscriptions.user_id')
                ->where('current_period_start != current_period_end')
                ->limit(10)
                ->get()
                ->getResult();
            foreach ($goodSubs as $gs) {
                CLI::write("  ID: {$gs->id} | User: {$gs->user_id} ({$gs->email}) | Plan: {$gs->plan_id} | Status: {$gs->status} | Start: {$gs->current_period_start} | End: {$gs->current_period_end}");
            }
            
            CLI::write("\n=== SUBSCRIPTIONS WITH START = END ===");
            $badSubs = $db->table('user_subscriptions')
                ->select('user_subscriptions.*, users.email')
                ->join('users', 'users.id = user_subscriptions.user_id')
                ->where('current_period_start = current_period_end')
                ->get()
                ->getResult();
            foreach ($badSubs as $bs) {
                CLI::write("  ID: {$bs->id} | User: {$bs->user_id} ({$bs->email}) | Plan: {$bs->plan_id} | Status: {$bs->status} | Start/End: {$bs->current_period_end}");
            }
        } else {
            CLI::write("  user_subscriptions table does not exist.");
        }

        CLI::write("\n=== USER SUBSCRIPTIONS (usersuscriptions) ===");
        if ($db->tableExists('usersuscriptions')) {
            $subs = $db->table('usersuscriptions')->where('user_id', 286)->get()->getResult();
            foreach ($subs as $sub) {
                CLI::write("  ID: {$sub->id} | Plan ID: {$sub->plan_id} | Status: {$sub->status}");
            }
        } else {
            CLI::write("  usersuscriptions table does not exist.");
        }

        CLI::write("\n=== CACHED USAGE ===");
        $currentMonth = date('Y-m');
        $cacheKey = "api_usage_286_{$currentMonth}";
        $cached = cache()->get($cacheKey);
        CLI::write("  Cached usage for 286: " . var_export($cached, true));

        CLI::write("\n=== USAGE FROM DB ===");
        $usageRow = $db->table('api_usage_daily')
            ->selectSum('requests_count')
            ->where('user_id', 286)
            ->like('date', $currentMonth, 'after')
            ->get()
            ->getRow();
        CLI::write("  DB Requests count: " . ($usageRow ? $usageRow->requests_count : '0'));

        CLI::write("\n=== API KEY IN QUESTION ===");
        $targetKey = 'ak_32e3a5f48f3aa510d25d26fa66a3b6ac';
        $apiKeyRow = $db->table('api_keys')
            ->select('api_keys.id AS api_key_id, api_keys.user_id, api_keys.is_active, api_keys.last_used_at, users.is_active as user_active, users.created_at, users.migration_reset_done')
            ->join('users', 'users.id = api_keys.user_id', 'left')
            ->where('api_keys.api_key', $targetKey)
            ->get()
            ->getRow();
        if ($apiKeyRow) {
            foreach ((array)$apiKeyRow as $key => $val) {
                CLI::write("  $key: $val");
            }
        } else {
            CLI::error("API key $targetKey not found.");
        }
    }
}
