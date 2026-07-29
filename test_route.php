<?php
$_SERVER['HTTP_HOST'] = 'spaincompanyapi.test';
$_SERVER['REQUEST_URI'] = '/documentation';
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['SERVER_PROTOCOL'] = 'HTTP/1.1';

chdir('public');
require 'index.php';
