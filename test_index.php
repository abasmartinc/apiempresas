<?php
// Simulate HTTP request
$_SERVER['HTTP_HOST'] = 'spaincompanyapi.test';
$_SERVER['REQUEST_URI'] = '/documentation';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

// We must trick CI4 into thinking it's NOT running from CLI
// CI4 checks PHP_SAPI === 'cli' or defined('STDIN')
// But we can't redefine PHP_SAPI.
// Let's just use curl in a PHP script. Wait, curl returned 500!
