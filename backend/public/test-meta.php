<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$_SERVER['HTTP_USER_AGENT'] = 'Zalobot';
$_SERVER['REQUEST_URI'] = '/';
require __DIR__ . '/social-meta.php';
