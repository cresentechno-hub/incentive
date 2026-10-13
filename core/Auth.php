<?php

class Auth
{
    public static function check(): bool
    {
        return Session::has('user_id');
    }

    public static function user(): ?array
    {
        return Session::get('user');
    }

    public static function id(): ?int
    {
        return Session::get('user_id');
    }

    public static function role(): string
    {
        return Session::get('user_role', '');
    }

    public static function is(string $role): bool
    {
        return self::role() === $role;
    }

    public static function isAdmin(): bool   { return self::is('Admin'); }
    public static function isManager(): bool { return self::is('Manager'); }
    public static function isAgent(): bool   { return self::is('Agent'); }

    public static function can(string ...$roles): bool
    {
        return in_array(self::role(), $roles, true);
    }

    public static function login(array $user): void
    {
        Session::regenerate();
        Session::set('user_id',   (int)$user['id']);
        Session::set('user_role', $user['role_name']);
        Session::set('user',      $user);
    }

    public static function logout(): void
    {
        Session::destroy();
        session_start();
    }

    /**
     * Require authentication and optionally enforce allowed roles.
     * Exits on failure.
     */
    public static function require(string ...$roles): void
    {
        if (!self::check()) {
            redirect('login');
        }

        if (!empty($roles) && !self::can(...$roles)) {
            http_response_code(403);
            render('errors/403');
            exit;
        }
    }
}
