<?php $pageTitle = e($agent['name']) . ' — Performance'; ?>

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= url('agents') ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0"><?= e($agent['name']) ?></h4>
  <span class="badge bg-secondary"><?= e($agent['role_name']) ?></span>
</div>

<!-- Period selector -->
<form class="mb-4 d-flex gap-2" method="GET">
  <input type="hidden" name="route" value="agents/view/<?= e($agent['id']) ?>">
  <input type="month" name="period" class="form-control form-control-sm"
         value="<?= e($period) ?>" style="width:160px">
  <button class="btn btn-sm btn-outline-secondary">Go</button>
</form>

<!-- Stat cards -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['Total Deals',    $summary['total_deals'] ?? 0,        'bi-briefcase',     '#eef0ff','#7c5cfc'],
    ['Approved Sales', money($summary['approved_sales'] ?? 0),'bi-bar-chart',    '#e8f5e9','#28a745'],
    ['Total Earned',   money($summary['total_earned'] ?? 0), 'bi-wallet2',       '#fff8e1','#fd7e14'],
    ['Paid',           money($summary['total_paid'] ?? 0),   'bi-cash-stack',    '#f9e8ff','#d63384'],
  ];
  ?>
  <?php foreach ($cards as [$label, $val, $icon, $bg, $ic]): ?>
  <div class="col-sm-6 col-xl-3">
    <div class="card stat-card shadow-sm">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon" style="background:<?= $bg ?>;color:<?= $ic ?>">
          <i class="bi <?= $icon ?>"></i>
        </div>
        <div>
          <div class="stat-value"><?= $val ?></div>
          <div class="stat-label"><?= $label ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Deals table -->
<div class="card shadow-sm mb-3">
  <div class="card-header border-0 bg-transparent pt-3">
    <h6 class="fw-semibold mb-0">Deals — <?= formatPeriod($period) ?></h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr><th>Client</th><th>Product</th><th>Value</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($deals as $d): ?>
        <tr>
          <td class="fw-medium"><?= e($d['client_name']) ?></td>
          <td><?= e($d['product_service'] ?? '—') ?></td>
          <td><?= money($d['deal_value']) ?></td>
          <td><span class="badge bg-<?= statusBadge($d['status']) ?>"><?= e($d['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($deals)): ?>
        <tr><td colspan="5" class="text-center text-muted py-3">No deals this period.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Incentive ledger -->
<div class="card shadow-sm">
  <div class="card-header border-0 bg-transparent pt-3">
    <h6 class="fw-semibold mb-0">Incentives — <?= formatPeriod($period) ?></h6>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr><th>Rule</th><th>Type</th><th>Deal</th><th>Earned</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($ledger as $l): ?>
        <tr>
          <td><?= e($l['rule_name']) ?></td>
          <td><span class="badge bg-secondary"><?= e($l['rule_type']) ?></span></td>
          <td><?= e($l['client_name'] ?? '—') ?></td>
          <td class="fw-semibold text-success"><?= money($l['earned_amount']) ?></td>
          <td><span class="badge bg-<?= statusBadge($l['status']) ?>"><?= e($l['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($ledger)): ?>
        <tr><td colspan="5" class="text-center text-muted py-3">No incentives this period.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
