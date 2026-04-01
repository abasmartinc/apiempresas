<?php
require 'system/Test/bootstrap.php';
$config = new \Config\Honeypot();
$honeypot = new \CodeIgniter\Honeypot\Honeypot($config);
$request = service('request');

// Test 1: POST non-empty
$request->setGlobal('post', ['honeypot' => 'bot']);
echo "POST with content: " . ($honeypot->hasContent($request) ? 'BLOCKED' : 'OK') . "\n";

// Test 2: POST empty
$request->setGlobal('post', ['honeypot' => '']);
echo "POST empty: " . ($honeypot->hasContent($request) ? 'BLOCKED' : 'OK') . "\n";

// Test 3: GET non-empty (should be OK if hasContent only checks POST)
$request->setGlobal('post', []);
$request->setGlobal('get', ['honeypot' => 'bot']);
echo "GET with content in URL: " . ($honeypot->hasContent($request) ? 'BLOCKED' : 'OK') . "\n";
