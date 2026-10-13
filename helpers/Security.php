<?php

class Security
{
    // ─── CSRF ────────────────────────────────────────────────

    public static function csrfToken(): string
    {
        if (!Session::has('csrf_token')) {
            Session::set('csrf_token', bin2hex(random_bytes(32)));
        }
        return Session::get('csrf_token');
    }

    public static function verifyCsrf(?string $token): bool
    {
        if (empty($token)) {
            return false;
        }
        return hash_equals(Session::get('csrf_token', ''), $token);
    }

    /** Render a hidden CSRF input field. */
    public static function csrfField(): string
    {
        return '<input type="hidden" name="csrf_token" value="' . self::csrfToken() . '">';
    }

    /** Abort with 403 if CSRF check fails. */
    public static function requireCsrf(): void
    {
        $token = $_POST['csrf_token'] ?? '';
        if (!self::verifyCsrf($token)) {
            http_response_code(403);
            die('Invalid request (CSRF check failed). Please go back and try again.');
        }
    }

    // ─── Input sanitization ──────────────────────────────────

    /** Strip tags and trim. */
    public static function sanitize(mixed $value): string
    {
        return trim(strip_tags((string)$value));
    }

    public static function int(mixed $value): int
    {
        return (int)filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    public static function float(mixed $value): float
    {
        return (float)filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    public static function email(mixed $value): string
    {
        return filter_var(trim((string)$value), FILTER_SANITIZE_EMAIL);
    }

    // ─── Validation ──────────────────────────────────────────

    public static function validateEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    public static function validateDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = DateTime::createFromFormat($format, $date);
        return $d !== false && $d->format($format) === $date;
    }

    public static function validatePositiveNumber(mixed $value): bool
    {
        return is_numeric($value) && (float)$value >= 0;
    }
}
