<?php

class PayoutController
{
    public function index(mixed $id = null): void
    {
        Auth::require('Admin', 'Manager');
        $batches = PayoutBatch::getWithProcessor();
        render('payouts/index', compact('batches'));
    }

    public function create(mixed $id = null): void
    {
        Auth::require('Admin', 'Manager');
        $periods = Database::fetchAll(
            "SELECT DISTINCT payout_period FROM agent_incentives
             WHERE status = 'pending'
             ORDER BY payout_period DESC"
        );
        render('payouts/create', compact('periods'));
    }

    public function store(mixed $id = null): void
    {
        Auth::require('Admin', 'Manager');
        Security::requireCsrf();

        $period = Security::sanitize($_POST['payout_period'] ?? '');
        if (!preg_match('/^\d{4}-\d{2}$/', $period)) {
            Session::flash('danger', 'Invalid period.');
            redirect('payouts/create');
        }

        Database::beginTransaction();
        try {
            $batchId = PayoutBatch::insert([
                'batch_date'    => date('Y-m-d'),
                'payout_period' => $period,
                'status'        => 'draft',
                'processed_by'  => Auth::id(),
                'notes'         => Security::sanitize($_POST['notes'] ?? ''),
            ]);

            // Approve pending incentives for the period and link to this batch
            $count = AgentIncentive::approveForBatch($batchId, $period);

            // Update batch totals
            PayoutBatch::recalcTotal($batchId);

            Database::commit();
            AuditLog::record('BATCH_CREATED', 'payout_batches', $batchId, [], ['period' => $period, 'incentives' => $count]);
            Session::flash('success', "Payout batch created for " . formatPeriod($period) . " with {$count} incentive(s).");
        } catch (Exception $e) {
            Database::rollBack();
            Session::flash('danger', 'Failed to create batch: ' . e($e->getMessage()));
        }

        redirect('payouts');
    }

    public function view(mixed $id = null): void
    {
        Auth::require('Admin', 'Manager');
        $batch  = PayoutBatch::getWithDetails((int)$id);
        if (!$batch) { redirect('payouts'); }

        $summary = AgentIncentive::getPeriodAgentSummary($batch['payout_period']);
        $ledger  = AgentIncentive::getLedger(['period' => $batch['payout_period']]);

        render('payouts/view', compact('batch', 'summary', 'ledger'));
    }

    public function complete(mixed $id = null): void
    {
        Auth::require('Admin');
        Security::requireCsrf();

        $batch = PayoutBatch::find((int)$id);
        if ($batch && $batch['status'] === 'draft') {
            Database::beginTransaction();
            try {
                PayoutBatch::update((int)$id, ['status' => 'completed']);
                AgentIncentive::markPaidForBatch((int)$id);
                // Also mark linked deals as Paid
                Database::execute(
                    "UPDATE deals SET status = 'Paid'
                     WHERE id IN (
                         SELECT DISTINCT deal_id FROM agent_incentives
                         WHERE payout_batch_id = ? AND deal_id IS NOT NULL
                     )",
                    [(int)$id]
                );
                Database::commit();
                AuditLog::record('BATCH_COMPLETED', 'payout_batches', (int)$id);
                Session::flash('success', 'Payout batch completed and marked as paid.');
            } catch (Exception $e) {
                Database::rollBack();
                Session::flash('danger', 'Error completing batch.');
            }
        }
        redirect('payouts');
    }

    public function cancel(mixed $id = null): void
    {
        Auth::require('Admin');
        Security::requireCsrf();

        $batch = PayoutBatch::find((int)$id);
        if ($batch && $batch['status'] === 'draft') {
            // Return incentives to pending
            Database::execute(
                "UPDATE agent_incentives SET status='pending', payout_batch_id=NULL
                 WHERE payout_batch_id = ?",
                [(int)$id]
            );
            PayoutBatch::update((int)$id, ['status' => 'cancelled']);
            AuditLog::record('BATCH_CANCELLED', 'payout_batches', (int)$id);
            Session::flash('warning', 'Batch cancelled and incentives returned to pending.');
        }
        redirect('payouts');
    }

    public function export(mixed $id = null): void
    {
        Auth::require('Admin', 'Manager');

        $batch = PayoutBatch::getWithDetails((int)$id);
        if (!$batch) { redirect('payouts'); }

        $rows = AgentIncentive::getLedger(['period' => $batch['payout_period']]);

        header('Content-Type: text/csv; charset=UTF-8');
        header('Content-Disposition: attachment; filename="payout_' . $batch['payout_period'] . '.csv"');
        header('Cache-Control: no-cache, no-store, must-revalidate');

        $out = fopen('php://output', 'w');
        fputcsv($out, ['Agent', 'Client', 'Deal Value', 'Rule', 'Rule Type', 'Earned', 'Status', 'Period']);

        foreach ($rows as $row) {
            fputcsv($out, [
                $row['agent_name'],
                $row['client_name'] ?? 'N/A',
                $row['deal_value']  ?? 0,
                $row['rule_name'],
                $row['rule_type'],
                $row['earned_amount'],
                $row['status'],
                $row['payout_period'],
            ]);
        }
        fclose($out);
        exit;
    }
}
