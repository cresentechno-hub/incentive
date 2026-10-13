<?php $pageTitle = 'Batch #' . $batch['id']; ?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= url('payouts') ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0">
    Payout Batch #<?= e($batch['id']) ?>
    <span class="badge bg-<?= statusBadge($batch['status']) ?> ms-2"><?= e($batch['status']) ?></span>
  </h4>
</div>

<!-- Batch meta -->
<div class="row g-3 mb-4">
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted small mb-1">Period</div>
        <div class="fw-semibold"><?= formatPeriod($batch['payout_period']) ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted small mb-1">Total Amount</div>
        <div class="fw-semibold text-success fs-5"><?= money($batch['total_amount']) ?></div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-muted small mb-1">Agents</div>
        <div class="fw-semibold"><?= e($batch['agent_count']) ?> agent(s)</div>
      </div>
    </div>
  </div>
</div>

<!-- Agent summary -->
<div class="card shadow-sm mb-3">
  <div class="card-header border-0 bg-transparent pt-3 d-flex justify-content-between align-items-center">
    <h6 class="fw-semibold mb-0">Agent Payout Summary</h6>
    <a href="<?= url('payouts/export/' . $batch['id']) ?>" class="btn btn-sm btn-outline-secondary">
      <i class="bi bi-download me-1"></i> Export CSV
    </a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr><th>Agent</th><th>Incentives</th><th>Total Earned</th><th>Paid</th><th>Pending</th></tr>
        </thead>
        <tbody>
        <?php foreach ($summary as $s): ?>
        <tr>
          <td class="fw-medium"><?= e($s['agent_name']) ?></td>
          <td><?= e($s['incentive_count']) ?></td>
          <td class="fw-semibold text-success"><?= money($s['total_earned']) ?></td>
          <td><?= money($s['total_paid']) ?></td>
          <td><?= money($s['total_pending']) ?></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($summary)): ?>
        <tr><td colspan="5" class="text-center text-muted py-3">No incentives in this batch.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Full ledger -->
<div class="card shadow-sm">
  <div class="card-header border-0 bg-transparent pt-3">
    <h6 class="fw-semibold mb-0">Incentive Details</h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0" style="font-size:.83rem">
        <thead class="table-light">
          <tr><th>Agent</th><th>Rule</th><th>Client</th><th>Deal Value</th><th>Earned</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($ledger as $l): ?>
        <tr>
          <td><?= e($l['agent_name']) ?></td>
          <td><?= e($l['rule_name']) ?></td>
          <td><?= e($l['client_name'] ?? '—') ?></td>
          <td><?= $l['deal_value'] ? money($l['deal_value']) : '—' ?></td>
          <td class="fw-semibold text-success"><?= money($l['earned_amount']) ?></td>
          <td><span class="badge bg-<?= statusBadge($l['status']) ?>"><?= e($l['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
