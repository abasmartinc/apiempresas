<?php
require_once 'system/Test/bootstrap.php';
$config = config('Session');
echo "Driver from config: " . $config->driver . "\n";
echo "Driver from env helper: " . env('session.driver') . "\n";
echo "Driver from getenv: " . getenv('session.driver') . "\n";
echo "Driver from $_ENV: " . ($_ENV['session.driver'] ?? 'not set') . "\n";
echo "Driver from $_SERVER: " . ($_SERVER['session.driver'] ?? 'not set') . "\n";
