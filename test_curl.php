<?php
$url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response = curl_exec($ch);
if (curl_errno($ch)) {
    echo "Curl error: " . curl_error($ch) . "\n";
} else {
    echo "Curl success!\n";
}
curl_close($ch);
