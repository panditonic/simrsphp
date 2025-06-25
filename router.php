<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Jika file/folder ada, load langsung
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Routing manual contoh:
if (preg_match('#^/users/?$#', $uri)) {
    require __DIR__ . '/users/index.php';
    exit;
}

// Default: index.php
require __DIR__ . '/index.php';