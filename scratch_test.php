<?php
$mysqli = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');

// Get current cache and clear it for user 9
$currentMonth = date('Y-m');
$cacheKey = "api_usage_lifetime_9";

// We can't clear CI4 cache easily from here, let's just make the DB have a huge usage
$mysqli->query("UPDATE api_usage_daily SET requests_count = 100 WHERE user_id = 9");
if ($mysqli->affected_rows == 0) {
    $mysqli->query("INSERT INTO api_usage_daily (user_id, plan_id, date, requests_count) VALUES (9, 1, '".date('Y-m-d')."', 100)");
}
echo "Forced DB quota usage to 100 for user 9.\n";
$mysqli->close();

echo "Waiting 30 seconds for cache to expire...\n";
sleep(31);

$ch = curl_init('http://localhost/apiempresas/api/v1/companies/search?q=test');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-KEY: fb8f4c010f8a5ddd75840dd733c456cb1af46d99b25443f4c41771665c81c89d']);
$response = curl_exec($ch);
curl_close($ch);
echo "API 1 Response: " . substr($response, 0, 100) . "\n";

$mysqli = new mysqli('217.61.210.127', 'apiempresas_user', 'WONwyjpsmx3h3$@2', 'reseller3537_apiempresas');
$res = $mysqli->query('SELECT balance FROM user_wallets WHERE user_id = 9');
if ($row = $res->fetch_assoc()) echo "New balance after 1-credit call: " . $row['balance'] . "\n";

$ch = curl_init('http://localhost/apiempresas/api/v1/professional/search?q=test');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['X-API-KEY: fb8f4c010f8a5ddd75840dd733c456cb1af46d99b25443f4c41771665c81c89d']);
$response = curl_exec($ch);
curl_close($ch);
echo "API 2 Response: " . substr($response, 0, 100) . "\n";

$res = $mysqli->query('SELECT balance FROM user_wallets WHERE user_id = 9');
if ($row = $res->fetch_assoc()) echo "New balance after 3-credit call: " . $row['balance'] . "\n";

$mysqli->close();
