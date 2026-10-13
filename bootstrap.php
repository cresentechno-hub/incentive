<?php
require_once __DIR__ . '/config.php';

// ─── Autoloader ──────────────────────────────────────────────
spl_autoload_register(function (string $class): void {
    $dirs = ['core', 'models', 'helpers', 'controllers'];
    foreach ($dirs as $dir) {
        $path = BASE_PATH . '/' . $dir . '/' . $class . '.php';
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// ─── Session ─────────────────────────────────────────────────
ini_set('session.cookie_httponly', '1');
ini_set('session.use_strict_mode', '1');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

session_name(SESSION_NAME);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Error handling (dev) ─────────────────────────────────────
error_reporting(E_ALL);
ini_set('display_errors', '1');

// ─── Global helpers ──────────────────────────────────────────
require_once BASE_PATH . '/helpers/helpers.php';
