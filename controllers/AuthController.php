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

            $email    = Security::email($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            if (!Security::validateEmail($email)) {
                $errors[] = 'Please enter a valid email address.';
            } elseif (empty($password)) {
                $errors[] = 'Password is required.';
            } else {
                $user = User::findByEmail($email);

                if ($user && password_verify($password, $user['password_hash'])) {
                    Auth::login($user);
                    AuditLog::record('LOGIN', 'users', (int)$user['id']);
                    redirect('dashboard');
                } else {
                    $errors[] = 'Invalid email or password.';
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
