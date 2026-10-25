<?php
// Per-environment overrides. Copy config.local.sample.php to config.local.php
// on the server (it is git-ignored). Without it, the local dev defaults below apply.
if (is_file(__DIR__ . '/config.local.php')) {
    require __DIR__ . '/config.local.php';
}

// ─── Environment ─────────────────────────────────────────────
defined('APP_ENV')   || define('APP_ENV',   'development');   // 'production' on cPanel
defined('APP_DEBUG') || define('APP_DEBUG', APP_ENV !== 'production');

// ─── Database ────────────────────────────────────────────────
defined('DB_HOST')    || define('DB_HOST',    'localhost');
defined('DB_PORT')    || define('DB_PORT',    3306);
defined('DB_NAME')    || define('DB_NAME',    'incentive_system');
defined('DB_USER')    || define('DB_USER',    'root');
defined('DB_PASS')    || define('DB_PASS',    '');
defined('DB_CHARSET') || define('DB_CHARSET', 'utf8mb4');

// ─── Application ─────────────────────────────────────────────
defined('APP_NAME')  || define('APP_NAME',  'Incentive Manager');
// Public URL, no trailing slash. Include the sub-folder if installed in one.
defined('APP_URL')   || define('APP_URL',   'http://incentive-system');
defined('BASE_PATH') || define('BASE_PATH', dirname(__FILE__));

// ─── Session ─────────────────────────────────────────────────
defined('SESSION_NAME')     || define('SESSION_NAME',     'incentive_sess');
defined('SESSION_LIFETIME') || define('SESSION_LIFETIME', 3600 * 8); // 8 hours
