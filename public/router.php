<?php
// PHP built-in dev server router: php -S localhost:8090 -t public/ public/router.php
$uri  = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = __DIR__ . $uri;

// Serve real static files directly
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false;
}

// Route everything through index.php
require __DIR__ . '/index.php';
