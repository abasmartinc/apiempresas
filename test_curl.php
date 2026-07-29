<?php
$ch = curl_init('http://spaincompanyapi.test/documentation');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, true);
$response = curl_exec($ch);
echo $response;
curl_close($ch);
