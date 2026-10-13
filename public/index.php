<?php
define('BASE_PATH', dirname(__DIR__));
require BASE_PATH . '/bootstrap.php';

// ─── Route parsing ───────────────────────────────────────────
$requestUri  = $_SERVER['REQUEST_URI'] ?? '/';
$scriptBase  = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
$path        = urldecode(parse_url($requestUri, PHP_URL_PATH));
$route       = ltrim(str_replace($scriptBase, '', $path), '/');

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
