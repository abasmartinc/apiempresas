<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://apiempresas.es/B85402030-agenda-plus-sl");
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_NOBODY, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$headers = curl_exec($ch);
curl_close($ch);
echo $headers;
