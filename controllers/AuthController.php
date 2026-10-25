<?php

class AuthController
{
    public function login(mixed $id = null): void
    {
        if (Auth::check()) {
            redirect('dashboard');
        }

        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            Security::requireCsrf();

            $login    = trim((string)($_POST['login'] ?? ''));
            $password = $_POST['password'] ?? '';

            if ($login === '') {
                $errors[] = 'Please enter your username or email.';
            } elseif (empty($password)) {
                $errors[] = 'Password is required.';
            } else {
                $user = User::findByLogin($login);

                if ($user && password_verify($password, $user['password_hash'])) {
                    Auth::login($user);
                    AuditLog::record('LOGIN', 'users', (int)$user['id']);
                    redirect('dashboard');
                } else {
                    $errors[] = 'Invalid username/email or password.';
                }
            }
        }

        render('auth/login', ['errors' => $errors], false);
    }

    public function logout(mixed $id = null): void
    {
        $userId = Auth::id();
        Auth::logout();
        if ($userId) {
            // Re-open session briefly to audit the logout
            session_start();
            AuditLog::record('LOGOUT', 'users', $userId);
        }
        redirect('login');
    }

    public function index(mixed $id = null): void
    {
        $this->login($id);
    }
}
