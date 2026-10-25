<?php
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/bootstrap.php';

// ─── Route parsing ───────────────────────────────────────────
$requestUri  = $_SERVER['REQUEST_URI'] ?? '/';
$path        = urldecode((string)parse_url($requestUri, PHP_URL_PATH));
// Strip the sub-folder from APP_URL (if any) so the app works at a domain root or in /subfolder
$urlPrefix   = rtrim((string)parse_url(APP_URL, PHP_URL_PATH), '/');
if ($urlPrefix !== '' && str_starts_with($path, $urlPrefix)) {
    $path = substr($path, strlen($urlPrefix));
}
$route       = ltrim($path, '/');

// Fallback to query-string ?route=
if ($route === '' || $route === 'index.php') {
    $route = $_GET['route'] ?? '';
}

$segments   = array_values(array_filter(explode('/', $route)));
$ctrlSlug   = $segments[0] ?? 'dashboard';
$action     = $segments[1] ?? 'index';
$id         = isset($segments[2]) ? (int)$segments[2] : null;

// ─── Controller map ──────────────────────────────────────────
$map = [
    'login'      => 'AuthController',
    'logout'     => 'AuthController',
    'dashboard'  => 'DashboardController',
    'deals'      => 'DealController',
    'incentives' => 'IncentiveController',
    'agents'     => 'AgentController',
    'payouts'    => 'PayoutController',
    'help'       => 'HelpController',
];

// Handle /login and /logout as special slugs
if ($ctrlSlug === 'login') {
    $action   = 'login';
    $ctrlSlug = 'login';
}
if ($ctrlSlug === 'logout') {
    $action   = 'logout';
    $ctrlSlug = 'logout';
}

$ctrlClass = $map[$ctrlSlug] ?? null;

if (!$ctrlClass || !class_exists($ctrlClass)) {
    http_response_code(404);
    echo '<h1>404 — Page Not Found</h1>';
    exit;
}

$controller = new $ctrlClass();

// Allowed public actions (no auth required)
$publicActions = ['login' => ['login', 'index']];
$isPublic = isset($publicActions[$ctrlSlug]) &&
            in_array($action, $publicActions[$ctrlSlug], true);

if (!$isPublic && !Auth::check()) {
    redirect('login');
}

// Dispatch
if (method_exists($controller, $action)) {
    $controller->$action($id);
} else {
    $controller->index($id);
}
