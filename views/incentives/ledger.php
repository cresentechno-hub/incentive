<?php $pageTitle = 'Incentive Ledger'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0">Incentive Ledger</h4>
  <?php if (Auth::can('Admin','Manager')): ?>
  <a href="<?= url('incentives/calculate') ?>" class="btn btn-primary">
    <i class="bi bi-calculator me-1"></i> Run Calculation
  </a>
  <?php endif; ?>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-3">
  <div class="card-body py-2">
    <form class="row g-2 align-items-end" method="GET" action="<?= url('incentives/ledger') ?>">
      <div class="col-auto">
        <input type="month" name="period" class="form-control form-control-sm"
               value="<?= e($filters['period']) ?>">
      </div>
      <?php if (Auth::can('Admin','Manager') && !empty($agents)): ?>
      <div class="col-auto">
        <select name="agent_id" class="form-select form-select-sm" style="min-width:160px">
          <option value="">All Agents</option>
          <?php foreach ($agents as $a): ?>
          <option value="<?= $a['id'] ?>" <?= (int)$filters['agent_id'] === (int)$a['id'] ? 'selected' : '' ?>>
            <?= e($a['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>
      <div class="col-auto">
        <select name="status" class="form-select form-select-sm">
          <option value="">All Statuses</option>
          <?php foreach (['pending','approved','rejected','paid'] as $s): ?>
          <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <button class="btn btn-sm btn-secondary">Filter</button>
        <a href="<?= url('incentives/ledger') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Summary totals -->
<?php if (!empty($ledger)): ?>
<?php
$totalEarned = array_sum(array_column($ledger, 'earned_amount'));
$pending     = array_sum(array_map(fn($r) => $r['status'] === 'pending'  ? $r['earned_amount'] : 0, $ledger));
$approved    = array_sum(array_map(fn($r) => $r['status'] === 'approved' ? $r['earned_amount'] : 0, $ledger));
$paid        = array_sum(array_map(fn($r) => $r['status'] === 'paid'     ? $r['earned_amount'] : 0, $ledger));
?>
<div class="row g-2 mb-3">
  <?php foreach ([
    ['Total Earned', money($totalEarned), 'bg-primary'],
    ['Pending',      money($pending),     'bg-warning'],
    ['Approved',     money($approved),    'bg-success'],
    ['Paid',         money($paid),        'bg-info'],
  ] as [$label, $val, $cls]): ?>
  <div class="col-auto">
    <div class="badge <?= $cls ?> text-white p-2" style="font-size:.85rem">
      <?= $label ?>: <?= $val ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<!-- Ledger table -->
<div class="card shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <?php if (Auth::can('Admin','Manager')): ?><th>Agent</th><?php endif; ?>
            <th>Rule</th>
            <th>Type</th>
            <th>Client / Deal</th>
            <th>Deal Value</th>
            <th>Earned</th>
            <th>Period</th>
            <th>Status</th>
            <?php if (Auth::can('Admin','Manager')): ?><th>Actions</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($ledger as $l): ?>
        <tr>
          <?php if (Auth::can('Admin','Manager')): ?>
          <td class="fw-medium"><?= e($l['agent_name']) ?></td>
          <?php endif; ?>
          <td>
            <div class="fw-medium"><?= e($l['rule_name']) ?></div>
          </td>
          <td><span class="badge bg-secondary"><?= e($l['rule_type']) ?></span></td>
          <td><?= e($l['client_name'] ?? '—') ?></td>
          <td><?= $l['deal_value'] ? money($l['deal_value']) : '—' ?></td>
          <td class="fw-semibold text-success"><?= money($l['earned_amount']) ?></td>
          <td><?= e($l['payout_period']) ?></td>
          <td><span class="badge bg-<?= statusBadge($l['status']) ?>"><?= e($l['status']) ?></span></td>
          <?php if (Auth::can('Admin','Manager')): ?>
          <td>
            <?php if ($l['status'] === 'pending'): ?>
            <div class="d-flex gap-1">
              <form method="POST" action="<?= url('incentives/approveIncentive/' . $l['id']) ?>">
                <?= Security::csrfField() ?>
                <button class="btn btn-sm btn-success py-0 px-2" title="Approve">
                  <i class="bi bi-check"></i>
                </button>
              </form>
              <form method="POST" action="<?= url('incentives/rejectIncentive/' . $l['id']) ?>">
                <?= Security::csrfField() ?>
                <button class="btn btn-sm btn-danger py-0 px-2" title="Reject">
                  <i class="bi bi-x"></i>
                </button>
              </form>
            </div>
            <?php else: ?>
            <span class="text-muted">—</span>
            <?php endif; ?>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($ledger)): ?>
        <tr>
          <td colspan="9" class="text-center text-muted py-4">
            <i class="bi bi-journal-x fs-4 d-block mb-1"></i>No incentive records found.
          </td>
        </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer text-muted small"><?= count($ledger) ?> record(s).</div>
</div>
