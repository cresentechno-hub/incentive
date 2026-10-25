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

// ─── Error handling ──────────────────────────────────────────
error_reporting(E_ALL);
ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('log_errors', '1');
ini_set('error_log', BASE_PATH . '/storage/error.log');

if (!APP_DEBUG) {
    set_exception_handler(function (Throwable $e): void {
        error_log($e);
        if (!headers_sent()) {
            http_response_code(500);
        }
        echo '<h1>500 — Something went wrong</h1><p>Please try again later.</p>';
    });
}

// ─── Session ─────────────────────────────────────────────────
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.cookie_secure', str_starts_with(APP_URL, 'https://') ? '1' : '0');
ini_set('session.use_strict_mode', '1');
ini_set('session.gc_maxlifetime', SESSION_LIFETIME);

session_name(SESSION_NAME);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ─── Global helpers ──────────────────────────────────────────
require_once BASE_PATH . '/helpers/helpers.php';
