<?php
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));

// Jika file atau folder ada, biarkan PHP built-in server yang handle
if ($uri !== '/' && file_exists(__DIR__ . $uri)) {
    return false;
}

// Routing khusus (contoh: /users → /modules/users/index.php)
if (preg_match('#^/users/?$#', $uri)) {
    require __DIR__ . '/modules/users/index.php';
    exit;
}

// Routing generic: /foo → /foo.php jika ada file .php
$phpFile = __DIR__ . $uri . '.php';
if (file_exists($phpFile)) {
    require $phpFile;
    exit;
}

// Jika tidak ada, fallback ke index.php
require __DIR__ . '/index.php';