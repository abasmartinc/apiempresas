<?php
$data = [
    'event_name' => 'test_manual_php',
    'page' => 'test_script',
    'anonymous_id' => 'test_anon',
    'session_id' => 'test_sess'
];

$ch = curl_init('http://localhost/apiempresas/api/tracking/event');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Response: $response\n";
