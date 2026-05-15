<?php
$start = microtime(true);
$url = 'http://localhost/apiempresas/public/api/v1/companies/search?q=restaurante&multiple=1&limit=0';
// We don't need auth if the local server doesn't enforce it, or we pass a dummy.
$context = stream_context_create([
    'http' => [
        'method' => 'GET',
        'header' => 'Accept: application/json'
    ]
]);
$res = file_get_contents($url, false, $context);
$end = microtime(true);
echo "Time: " . ($end - $start) . "s\n";
