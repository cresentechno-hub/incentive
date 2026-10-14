<?php

class DealController
{
    public function index(mixed $id = null): void
    {
        Auth::require();

        $filters = [
            'status'   => Security::sanitize($_GET['status']   ?? ''),
            'period'   => Security::sanitize($_GET['period']   ?? ''),
            'search'   => Security::sanitize($_GET['search']   ?? ''),
            'agent_id' => Security::int($_GET['agent_id']       ?? 0),
        ];

        // Agents only see their own deals
        if (Auth::isAgent()) {
            $filters['agent_id'] = Auth::id();
        }

        $deals  = Deal::getAllWithAgents($filters);
        $agents = Auth::can('Admin', 'Manager') ? User::getAgents() : [];

        render('deals/index', compact('deals', 'filters', 'agents'));
    }

    public function create(mixed $id = null): void
    {
        Auth::require();
        $agents = Auth::can('Admin', 'Manager') ? User::getAgents() : [];
        render('deals/form', ['deal' => null, 'agents' => $agents]);
    }

    public function store(mixed $id = null): void
    {
        Auth::require();
        Security::requireCsrf();

        $errors = $this->validate($_POST);

        if (!empty($errors)) {
            $agents = Auth::can('Admin', 'Manager') ? User::getAgents() : [];
            render('deals/form', ['deal' => $_POST, 'agents' => $agents, 'errors' => $errors]);
            return;
        }

        $agentId = Auth::isAgent()
            ? Auth::id()
            : Security::int($_POST['agent_id'] ?? 0);

        $dealId = Deal::insert([
            'agent_id'        => $agentId,
            'client_name'     => Security::sanitize($_POST['client_name']),
            'product_service' => Security::sanitize($_POST['product_service'] ?? ''),
            'deal_value'      => Security::float($_POST['deal_value']),
            'closing_date'    => date('Y-m-d'),
            'status'          => 'Pending',
            'notes'           => Security::sanitize($_POST['notes'] ?? ''),
        ]);

        AuditLog::record('DEAL_CREATED', 'deals', $dealId, [], [
            'client_name' => Security::sanitize($_POST['client_name']),
            'deal_value'  => Security::float($_POST['deal_value']),
        ]);

        Session::flash('success', 'Deal logged successfully.');
        redirect('deals');
    }

    public function edit(mixed $id = null): void
    {
        Auth::require();
        $deal = Deal::getWithAgent((int)$id);
        if (!$deal) { redirect('deals'); }

        // Agents can only edit their own pending deals
        if (Auth::isAgent() &&
            ((int)$deal['agent_id'] !== Auth::id() || $deal['status'] !== 'Pending')) {
            http_response_code(403);
            render('errors/403');
            return;
        }

        $agents = Auth::can('Admin', 'Manager') ? User::getAgents() : [];
        render('deals/form', compact('deal', 'agents'));
    }

    public function update(mixed $id = null): void
    {
        Auth::require();
        Security::requireCsrf();

        $deal = Deal::getWithAgent((int)$id);
        if (!$deal) { redirect('deals'); }

        if (Auth::isAgent() &&
            ((int)$deal['agent_id'] !== Auth::id() || $deal['status'] !== 'Pending')) {
            http_response_code(403);
            render('errors/403');
            return;
        }

        $errors = $this->validate($_POST);
        if (!empty($errors)) {
            $agents = Auth::can('Admin', 'Manager') ? User::getAgents() : [];
            render('deals/form', ['deal' => array_merge($deal, $_POST), 'agents' => $agents, 'errors' => $errors]);
            return;
        }

        $oldValues = $deal;
        $updates   = [
            'client_name'     => Security::sanitize($_POST['client_name']),
            'product_service' => Security::sanitize($_POST['product_service'] ?? ''),
            'deal_value'      => Security::float($_POST['deal_value']),
            'notes'           => Security::sanitize($_POST['notes'] ?? ''),
        ];
        if (Auth::can('Admin', 'Manager')) {
            $updates['agent_id'] = Security::int($_POST['agent_id'] ?? $deal['agent_id']);
        }

        Deal::update((int)$id, $updates);
        AuditLog::record('DEAL_UPDATED', 'deals', (int)$id, $oldValues, $updates);

        Session::flash('success', 'Deal updated.');
        redirect('deals');
    }

    public function approve(mixed $id = null): void
    {
        Auth::require('Admin', 'Manager');
        Security::requireCsrf();

        $deal = Deal::find((int)$id);
        if ($deal && $deal['status'] === 'Pending') {
            Deal::update((int)$id, [
                'status'      => 'Approved',
                'approved_by' => Auth::id(),
                'approved_at' => date('Y-m-d H:i:s'),
            ]);
            AuditLog::record('DEAL_APPROVED', 'deals', (int)$id);
            Session::flash('success', 'Deal approved.');
        }
        redirectBack('deals');
    }

    public function reject(mixed $id = null): void
    {
        Auth::require('Admin', 'Manager');
        Security::requireCsrf();

        $deal = Deal::find((int)$id);
        if ($deal && $deal['status'] === 'Pending') {
            Deal::update((int)$id, ['status' => 'Rejected']);
            AuditLog::record('DEAL_REJECTED', 'deals', (int)$id);
            Session::flash('warning', 'Deal rejected.');
        }
        redirectBack('deals');
    }

    public function delete(mixed $id = null): void
    {
        Auth::require('Admin');
        Security::requireCsrf();

        $deal = Deal::find((int)$id);
        if ($deal) {
            AuditLog::record('DEAL_DELETED', 'deals', (int)$id, $deal);
            Deal::delete((int)$id);
            Session::flash('success', 'Deal deleted.');
        }
        redirect('deals');
    }

    // ─── Validation ──────────────────────────────────────────

    private function validate(array $data): array
    {
        $errors = [];

        if (empty(trim($data['client_name'] ?? ''))) {
            $errors[] = 'Client name is required.';
        }
        if (!Security::validatePositiveNumber($data['deal_value'] ?? '')) {
            $errors[] = 'Deal value must be a positive number.';
        }
        return $errors;
    }
}
