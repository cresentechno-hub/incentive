<?php

class IncentiveController
{
    // ─── Rules ───────────────────────────────────────────────

    public function index(mixed $id = null): void
    {
        $this->rules();
    }

    public function rules(mixed $id = null): void
    {
        Auth::require('Admin', 'Manager');
        $rules = IncentiveRule::getAllWithCreator();
        render('incentives/rules', compact('rules'));
    }

    public function createRule(mixed $id = null): void
    {
        Auth::require('Admin');
        render('incentives/rule_form', ['rule' => null]);
    }

    public function storeRule(mixed $id = null): void
    {
        Auth::require('Admin');
        Security::requireCsrf();

        $errors = $this->validateRule($_POST);
        if (!empty($errors)) {
            render('incentives/rule_form', ['rule' => $_POST, 'errors' => $errors]);
            return;
        }

        $ruleId = IncentiveRule::insert([
            'name'          => Security::sanitize($_POST['name']),
            'description'   => Security::sanitize($_POST['description'] ?? ''),
            'rule_type'     => Security::sanitize($_POST['rule_type']),
            'target_metric' => Security::sanitize($_POST['target_metric']),
            'rate_value'    => Security::float($_POST['rate_value']),
            'min_threshold' => Security::float($_POST['min_threshold'] ?? 0),
            'max_threshold' => !empty($_POST['max_threshold'])
                                   ? Security::float($_POST['max_threshold'])
                                   : null,
            'start_date'    => Security::sanitize($_POST['start_date']),
            'end_date'      => !empty($_POST['end_date'])
                                   ? Security::sanitize($_POST['end_date'])
                                   : null,
            'is_active'     => isset($_POST['is_active']) ? 1 : 0,
            'created_by'    => Auth::id(),
        ]);

        AuditLog::record('RULE_CREATED', 'incentive_rules', $ruleId);
        Session::flash('success', 'Incentive rule created.');
        redirect('incentives/rules');
    }

    public function editRule(mixed $id = null): void
    {
        Auth::require('Admin');
        $rule = IncentiveRule::find((int)$id);
        if (!$rule) { redirect('incentives/rules'); }
        render('incentives/rule_form', compact('rule'));
    }

    public function updateRule(mixed $id = null): void
    {
        Auth::require('Admin');
        Security::requireCsrf();

        $rule = IncentiveRule::find((int)$id);
        if (!$rule) { redirect('incentives/rules'); }

        $errors = $this->validateRule($_POST);
        if (!empty($errors)) {
            render('incentives/rule_form', ['rule' => array_merge($rule, $_POST), 'errors' => $errors]);
            return;
        }

        $updates = [
            'name'          => Security::sanitize($_POST['name']),
            'description'   => Security::sanitize($_POST['description'] ?? ''),
            'rule_type'     => Security::sanitize($_POST['rule_type']),
            'target_metric' => Security::sanitize($_POST['target_metric']),
            'rate_value'    => Security::float($_POST['rate_value']),
            'min_threshold' => Security::float($_POST['min_threshold'] ?? 0),
            'max_threshold' => !empty($_POST['max_threshold'])
                                   ? Security::float($_POST['max_threshold'])
                                   : null,
            'start_date'    => Security::sanitize($_POST['start_date']),
            'end_date'      => !empty($_POST['end_date'])
                                   ? Security::sanitize($_POST['end_date'])
                                   : null,
            'is_active'     => isset($_POST['is_active']) ? 1 : 0,
        ];

        AuditLog::record('RULE_UPDATED', 'incentive_rules', (int)$id, $rule, $updates);
        IncentiveRule::update((int)$id, $updates);
        Session::flash('success', 'Incentive rule updated.');
        redirect('incentives/rules');
    }

    public function deleteRule(mixed $id = null): void
    {
        Auth::require('Admin');
        Security::requireCsrf();

        $rule = IncentiveRule::find((int)$id);
        if ($rule) {
            // Check if rule has been used in any incentives
            $used = Database::fetch(
                'SELECT id FROM agent_incentives WHERE rule_id = ? LIMIT 1', [(int)$id]
            );
            if ($used) {
                // Soft-delete: just deactivate
                IncentiveRule::update((int)$id, ['is_active' => 0]);
                Session::flash('warning', 'Rule deactivated (it has linked incentive records).');
            } else {
                AuditLog::record('RULE_DELETED', 'incentive_rules', (int)$id, $rule);
                IncentiveRule::delete((int)$id);
                Session::flash('success', 'Rule deleted.');
            }
        }
        redirect('incentives/rules');
    }

    // ─── Calculation ─────────────────────────────────────────

    public function calculate(mixed $id = null): void
    {
        Auth::require('Admin', 'Manager');
        $periods = $this->getAvailablePeriods();
        render('incentives/calculate', compact('periods'));
    }

    public function process(mixed $id = null): void
    {
        Auth::require('Admin', 'Manager');
        Security::requireCsrf();

        $period = Security::sanitize($_POST['period'] ?? currentPeriod());

        if (!preg_match('/^\d{4}-\d{2}$/', $period)) {
            Session::flash('danger', 'Invalid period format.');
            redirect('incentives/calculate');
        }

        $calc   = new IncentiveCalculator($period);
        $result = $calc->calculateForPeriod($period);

        AuditLog::record('INCENTIVES_CALCULATED', 'agent_incentives', 0, [], $result);
        Session::flash('success', sprintf(
            'Calculation complete for %s. %d deal(s) processed, $%.2f total earned.',
            formatPeriod($period),
            $result['deals_processed'],
            $result['total_earned']
        ));
        redirect('incentives/ledger');
    }

    // ─── Ledger ──────────────────────────────────────────────

    public function ledger(mixed $id = null): void
    {
        Auth::require();

        $filters = [
            'period'   => Security::sanitize($_GET['period']   ?? currentPeriod()),
            'status'   => Security::sanitize($_GET['status']   ?? ''),
            'agent_id' => Auth::isAgent() ? Auth::id() : Security::int($_GET['agent_id'] ?? 0),
        ];

        $ledger  = AgentIncentive::getLedger($filters);
        $agents  = Auth::can('Admin', 'Manager') ? User::getAgents() : [];
        $periods = $this->getAvailablePeriods();

        render('incentives/ledger', compact('ledger', 'filters', 'agents', 'periods'));
    }

    public function approveIncentive(mixed $id = null): void
    {
        Auth::require('Admin', 'Manager');
        Security::requireCsrf();

        $row = AgentIncentive::find((int)$id);
        if ($row && $row['status'] === 'pending') {
            AgentIncentive::update((int)$id, ['status' => 'approved']);
            AuditLog::record('INCENTIVE_APPROVED', 'agent_incentives', (int)$id);
            Session::flash('success', 'Incentive approved.');
        }
        redirectBack('incentives/ledger');
    }

    public function rejectIncentive(mixed $id = null): void
    {
        Auth::require('Admin', 'Manager');
        Security::requireCsrf();

        $row = AgentIncentive::find((int)$id);
        if ($row && $row['status'] === 'pending') {
            AgentIncentive::update((int)$id, [
                'status'      => 'rejected',
                'override_by' => Auth::id(),
                'override_at' => date('Y-m-d H:i:s'),
            ]);
            AuditLog::record('INCENTIVE_REJECTED', 'agent_incentives', (int)$id);
            Session::flash('warning', 'Incentive rejected.');
        }
        redirectBack('incentives/ledger');
    }

    // ─── Helpers ─────────────────────────────────────────────

    private function getAvailablePeriods(): array
    {
        $rows = Database::fetchAll(
            "SELECT DISTINCT DATE_FORMAT(closing_date,'%Y-%m') AS period
             FROM deals ORDER BY period DESC LIMIT 24"
        );
        return array_column($rows, 'period');
    }

    private function validateRule(array $data): array
    {
        $errors = [];
        if (empty(trim($data['name'] ?? ''))) {
            $errors[] = 'Rule name is required.';
        }
        if (!in_array($data['rule_type'] ?? '', ['flat', 'percentage', 'tier'], true)) {
            $errors[] = 'Invalid rule type.';
        }
        if (!Security::validatePositiveNumber($data['rate_value'] ?? '')) {
            $errors[] = 'Rate value must be a positive number.';
        }
        if (!Security::validateDate($data['start_date'] ?? '')) {
            $errors[] = 'Start date is required and must be a valid date.';
        }
        return $errors;
    }
}
