<?php
// Need to set up CI4 environment correctly
define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR);
require 'app/Config/Paths.php';
$paths = new Config\Paths();
require $paths->systemDirectory . '/bootstrap.php';

$db = \Config\Database::connect();

echo "--- USER 148 ---\n";
$user = $db->table('users')->where('id', 148)->get()->getRow();
print_r($user);

echo "\n--- SUBSCRIPTIONS FOR USER 148 ---\n";
$subs = $db->table('usersuscriptions')->where('user_id', 148)->get()->getResultArray();
print_r($subs);

echo "\n--- PLAN 6 DETAILS ---\n";
$plan = $db->table('api_plans')->where('id', 6)->get()->getRow();
print_r($plan);

echo "\n--- CHECKING UsersuscriptionsModel::hasActiveSubscriptionFor ---\n";
$model = new \App\Models\UsersuscriptionsModel();
$hasApi = $model->hasActiveSubscriptionFor(148, 'api');
$hasRadar = $model->hasActiveSubscriptionFor(148, 'radar');
echo "Has Active API: " . ($hasApi ? 'YES' : 'NO') . "\n";
echo "Has Active Radar: " . ($hasRadar ? 'YES' : 'NO') . "\n";
