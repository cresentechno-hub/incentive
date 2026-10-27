<?php

class AgentController
{
    public function index(mixed $id = null): void
    {
        Auth::require('Admin', 'Manager');
        $agents = User::getAllWithRoles();
        $period = Security::sanitize($_GET['period'] ?? currentPeriod());
        render('agents/index', compact('agents', 'period'));
    }

    public function view(mixed $id = null): void
    {
        Auth::require('Admin', 'Manager');
        $agent   = User::findWithRole((int)$id);
        if (!$agent) { redirect('agents'); }

        $period  = Security::sanitize($_GET['period'] ?? currentPeriod());
        $summary = User::getAgentSummary((int)$id, $period);
        $deals   = Deal::getAgentDeals((int)$id, ['period' => $period]);
        $ledger  = AgentIncentive::getLedger(['agent_id' => (int)$id, 'period' => $period]);

        render('agents/view', compact('agent', 'summary', 'deals', 'ledger', 'period'));
    }

    public function create(mixed $id = null): void
    {
        Auth::require('Admin');
        $roles = User::getRoles();
        $teams = User::getTeams();
        render('agents/form', ['agent' => null, 'roles' => $roles, 'teams' => $teams]);
    }

    public function store(mixed $id = null): void
    {
        Auth::require('Admin');
        Security::requireCsrf();

        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            render('agents/form', [
                'agent'  => $_POST,
                'roles'  => User::getRoles(),
                'teams'  => User::getTeams(),
                'errors' => $errors,
            ]);
            return;
        }

        $userId = User::insert([
            'role_id'       => Security::int($_POST['role_id']),
            'team_id'       => !empty($_POST['team_id']) ? Security::int($_POST['team_id']) : null,
            'name'          => Security::sanitize($_POST['name']),
            'email'         => Security::email($_POST['email']),
            'username'      => $this->username($_POST),
            'password_hash' => password_hash((string)$_POST['password'], PASSWORD_BCRYPT),
            'status'        => 'active',
        ]);

        AuditLog::record('USER_CREATED', 'users', $userId);
        Session::flash('success', 'User account created.');
        redirect('agents');
    }

    public function edit(mixed $id = null): void
    {
        Auth::require('Admin');
        $agent = User::findWithRole((int)$id);
        if (!$agent) { redirect('agents'); }

        $roles = User::getRoles();
        $teams = User::getTeams();
        render('agents/form', compact('agent', 'roles', 'teams'));
    }

    public function updateAgent(mixed $id = null): void
    {
        Auth::require('Admin');
        Security::requireCsrf();

        $agent = User::find((int)$id);
        if (!$agent) { redirect('agents'); }

        $errors = $this->validate($_POST, (int)$id, false);
        if (!empty($errors)) {
            render('agents/form', [
                'agent'  => array_merge($agent, $_POST),
                'roles'  => User::getRoles(),
                'teams'  => User::getTeams(),
                'errors' => $errors,
            ]);
            return;
        }

        $updates = [
            'role_id'  => Security::int($_POST['role_id']),
            'team_id'  => !empty($_POST['team_id']) ? Security::int($_POST['team_id']) : null,
            'name'     => Security::sanitize($_POST['name']),
            'email'    => Security::email($_POST['email']),
            'username' => $this->username($_POST),
            'status'   => in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active',
        ];
        if (!empty($_POST['password'])) {
            $updates['password_hash'] = password_hash((string)$_POST['password'], PASSWORD_BCRYPT);
        }

        AuditLog::record('USER_UPDATED', 'users', (int)$id, $agent, $updates);
        User::update((int)$id, $updates);
        Session::flash('success', 'User updated.');
        redirect('agents');
    }

    /** Trimmed username, or null when left blank (blank must be NULL, not '', for the UNIQUE index). */
    private function username(array $data): ?string
    {
        $u = trim((string)($data['username'] ?? ''));
        return $u === '' ? null : $u;
    }

    private function validate(array $data, ?int $ignoreId = null, bool $requirePassword = true): array
    {
        $errors = [];
        if (empty(trim($data['name'] ?? ''))) {
            $errors[] = 'Name is required.';
        }
        if (!Security::validateEmail($data['email'] ?? '')) {
            $errors[] = 'Valid email is required.';
        }

        $username = $this->username($data);
        if ($username !== null) {
            if (!preg_match('/^[A-Za-z0-9._-]{3,50}$/', $username)) {
                $errors[] = 'Username must be 3-50 characters: letters, numbers, dot, dash or underscore.';
            } elseif (User::usernameTaken($username, $ignoreId)) {
                $errors[] = 'That username is already taken.';
            }
        }

        $password = (string)($data['password'] ?? '');
        if ($password === '') {
            if ($requirePassword) {
                $errors[] = 'Password is required.';
            }
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        return $errors;
    }
}
