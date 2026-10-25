<?php
// Copy to config.local.php on the cPanel server and fill in the values.
// cPanel prefixes database and user names with your account name (e.g. cpuser_incentive).

define('APP_ENV', 'production');
define('APP_URL', 'https://incentive.example.com');   // no trailing slash; add /subfolder if installed in one

define('DB_HOST', 'localhost');
define('DB_NAME', 'cpuser_incentive');
define('DB_USER', 'cpuser_incentive');
define('DB_PASS', 'CHANGE_ME');
