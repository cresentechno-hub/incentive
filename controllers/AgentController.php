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
            'password_hash' => password_hash(Security::sanitize($_POST['password']), PASSWORD_BCRYPT),
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

        $updates = [
            'role_id' => Security::int($_POST['role_id']),
            'team_id' => !empty($_POST['team_id']) ? Security::int($_POST['team_id']) : null,
            'name'    => Security::sanitize($_POST['name']),
            'email'   => Security::email($_POST['email']),
            'status'  => in_array($_POST['status'] ?? '', ['active', 'inactive']) ? $_POST['status'] : 'active',
        ];
        if (!empty($_POST['password'])) {
            $updates['password_hash'] = password_hash(Security::sanitize($_POST['password']), PASSWORD_BCRYPT);
        }

        AuditLog::record('USER_UPDATED', 'users', (int)$id, $agent, $updates);
        User::update((int)$id, $updates);
        Session::flash('success', 'User updated.');
        redirect('agents');
    }

    private function validate(array $data): array
    {
        $errors = [];
        if (empty(trim($data['name'] ?? ''))) {
            $errors[] = 'Name is required.';
        }
        if (!Security::validateEmail($data['email'] ?? '')) {
            $errors[] = 'Valid email is required.';
        }
        if (empty($data['password'] ?? '')) {
            $errors[] = 'Password is required.';
        } elseif (strlen($data['password']) < 8) {
            $errors[] = 'Password must be at least 8 characters.';
        }
        return $errors;
    }
}
