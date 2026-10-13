<?php

/** Render a view wrapped in layout header/footer. */
function render(string $view, array $data = [], bool $withLayout = true): void
{
    extract($data, EXTR_SKIP);
    $viewFile = BASE_PATH . '/views/' . $view . '.php';

    if (!file_exists($viewFile)) {
        throw new RuntimeException("View not found: {$view}");
    }

    if ($withLayout) {
        require BASE_PATH . '/views/layout/header.php';
        require $viewFile;
        require BASE_PATH . '/views/layout/footer.php';
    } else {
        require $viewFile;
    }
}

/** Redirect to an app-relative path and exit. */
function redirect(string $path): never
{
    $url = APP_URL . '/' . ltrim($path, '/');
    header('Location: ' . $url);
    exit;
}

/** Redirect back (or to a fallback). */
function redirectBack(string $fallback = 'dashboard'): never
{
    $ref = $_SERVER['HTTP_REFERER'] ?? '';
    if ($ref && str_starts_with($ref, APP_URL)) {
        header('Location: ' . $ref);
    } else {
        redirect($fallback);
    }
    exit;
}

/** Generate a URL. */
function url(string $path = ''): string
{
    return APP_URL . '/' . ltrim($path, '/');
}

/** Escape for HTML output. */
function e(mixed $value): string
{
    return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Format a number as currency. */
function money(float $amount, string $symbol = '$'): string
{
    return $symbol . number_format($amount, 2);
}

/** Return a Bootstrap badge class for deal/incentive status. */
function statusBadge(string $status): string
{
    return match (strtolower($status)) {
        'approved', 'paid', 'completed' => 'success',
        'pending', 'draft', 'processing' => 'warning',
        'rejected', 'cancelled'          => 'danger',
        default                           => 'secondary',
    };
}

/** Return current year-month as YYYY-MM. */
function currentPeriod(): string
{
    return date('Y-m');
}

/** Format a YYYY-MM string as "Month YYYY". */
function formatPeriod(string $period): string
{
    return date('F Y', mktime(0, 0, 0, (int)substr($period, 5, 2), 1, (int)substr($period, 0, 4)));
}
