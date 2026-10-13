<?php
// ─── Database ────────────────────────────────────────────────
define('DB_HOST',    'localhost');
define('DB_PORT',    3306);
define('DB_NAME',    'incentive_system');
define('DB_USER',    'root');
define('DB_PASS',    '');
define('DB_CHARSET', 'utf8mb4');

// ─── Application ─────────────────────────────────────────────
define('APP_NAME',    'Incentive Manager');
// Laragon vhost URL — matches the ServerName in auto.incentive-system.conf
define('APP_URL',     'http://localhost:8090');
define('BASE_PATH',   dirname(__FILE__));

// ─── Session ─────────────────────────────────────────────────
define('SESSION_NAME',     'incentive_sess');
define('SESSION_LIFETIME', 3600 * 8); // 8 hours
