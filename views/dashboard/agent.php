<?php $pageTitle = 'My Performance'; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
  <div>
    <h4 class="fw-bold mb-0">My Performance</h4>
    <small class="text-muted"><?= e(Auth::user()['name'] ?? '') ?> — <?= formatPeriod($period) ?></small>
  </div>
  <form class="d-flex gap-2" method="GET" action="<?= url('dashboard') ?>">
    <input type="month" name="period" class="form-control form-control-sm"
           value="<?= e($period) ?>" style="width:160px">
    <button class="btn btn-sm btn-outline-secondary">Go</button>
  </form>
</div>

<!-- Stat cards -->
<div class="row g-3 mb-4">
  <?php
  $cards = [
    ['label'=>'Total Deals',      'value'=>$summary['total_deals'] ?? 0,   'icon'=>'bi-briefcase',       'bg'=>'#eef0ff','ic'=>'#7c5cfc'],
    ['label'=>'Approved Deals',   'value'=>$summary['approved_deals'] ?? 0,'icon'=>'bi-check-circle',    'bg'=>'#e8f5e9','ic'=>'#28a745'],
    ['label'=>'Approved Sales',   'value'=>money($summary['approved_sales'] ?? 0),'icon'=>'bi-bar-chart','bg'=>'#e3f2fd','ic'=>'#0d6efd'],
    ['label'=>'Earned This Month','value'=>money($summary['total_earned'] ?? 0),'icon'=>'bi-wallet2',     'bg'=>'#fff8e1','ic'=>'#fd7e14'],
    ['label'=>'Paid Out',         'value'=>money($summary['total_paid'] ?? 0),  'icon'=>'bi-cash-stack', 'bg'=>'#f9e8ff','ic'=>'#d63384'],
    ['label'=>'Pending Approval', 'value'=>$summary['pending_deals'] ?? 0, 'icon'=>'bi-clock-history',   'bg'=>'#e8f5e9','ic'=>'#20c997'],
  ];
  ?>
  <?php foreach ($cards as $c): ?>
  <div class="col-sm-6 col-xl-4">
    <div class="card stat-card shadow-sm">
      <div class="d-flex align-items-center gap-3">
        <div class="stat-icon" style="background:<?= $c['bg'] ?>;color:<?= $c['ic'] ?>">
          <i class="bi <?= $c['icon'] ?>"></i>
        </div>
        <div>
          <div class="stat-value"><?= $c['value'] ?></div>
          <div class="stat-label"><?= $c['label'] ?></div>
        </div>
      </div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- Sales trend chart -->
<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-header border-0 bg-transparent pt-3 pb-0">
        <h6 class="fw-semibold mb-0">My Sales Trend (Last 6 Months)</h6>
      </div>
      <div class="card-body">
        <canvas id="agentTrend" height="130"></canvas>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card shadow-sm h-100">
      <div class="card-header border-0 bg-transparent pt-3 pb-0">
        <h6 class="fw-semibold mb-0">Quick Actions</h6>
      </div>
      <div class="card-body d-flex flex-column gap-2">
        <a href="<?= url('deals/create') ?>" class="btn btn-primary">
          <i class="bi bi-plus-circle me-1"></i> Log New Deal
        </a>
        <a href="<?= url('deals') ?>" class="btn btn-outline-secondary">
          <i class="bi bi-briefcase me-1"></i> All My Deals
        </a>
        <a href="<?= url('incentives/ledger') ?>" class="btn btn-outline-secondary">
          <i class="bi bi-journal-text me-1"></i> My Incentives
        </a>
      </div>
    </div>
  </div>
</div>

<!-- Recent deals -->
<div class="card shadow-sm mb-4">
  <div class="card-header border-0 bg-transparent pt-3 d-flex justify-content-between align-items-center">
    <h6 class="fw-semibold mb-0">My Deals — <?= formatPeriod($period) ?></h6>
    <a href="<?= url('deals/create') ?>" class="btn btn-sm btn-primary">
      <i class="bi bi-plus me-1"></i> Log Deal
    </a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr><th>Client</th><th>Product/Service</th><th>Value</th><th>Closing</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach ($deals as $d): ?>
        <tr>
          <td class="fw-medium"><?= e($d['client_name']) ?></td>
          <td><?= e($d['product_service'] ?? '—') ?></td>
          <td><?= money($d['deal_value']) ?></td>
          <td><?= e($d['closing_date']) ?></td>
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

<!-- Incentive ledger snippet -->
<div class="card shadow-sm">
  <div class="card-header border-0 bg-transparent pt-3 d-flex justify-content-between align-items-center">
    <h6 class="fw-semibold mb-0">My Incentives — <?= formatPeriod($period) ?></h6>
    <a href="<?= url('incentives/ledger') ?>" class="btn btn-sm btn-outline-primary">Full Ledger</a>
  </div>
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr><th>Rule</th><th>Type</th><th>Client</th><th>Earned</th><th>Status</th></tr>
        </thead>
        <tbody>
        <?php foreach (array_slice($ledger, 0, 8) as $l): ?>
        <tr>
          <td class="fw-medium"><?= e($l['rule_name']) ?></td>
          <td><span class="badge bg-secondary"><?= e($l['rule_type']) ?></span></td>
          <td><?= e($l['client_name'] ?? '—') ?></td>
          <td class="fw-semibold text-success"><?= money($l['earned_amount']) ?></td>
          <td><span class="badge bg-<?= statusBadge($l['status']) ?>"><?= e($l['status']) ?></span></td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($ledger)): ?>
        <tr><td colspan="5" class="text-center text-muted py-3">No incentives calculated for this period.</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php
$trendLabels = array_map(fn($r) => formatPeriod($r['period']), $trend);
$trendValues = array_map(fn($r) => round($r['total_value'], 2), $trend);

$extraJs = '<script>
const ctx2 = document.getElementById("agentTrend");
if (ctx2) {
  new Chart(ctx2, {
    type: "line",
    data: {
      labels: ' . json_encode($trendLabels) . ',
      datasets: [{
        label: "Sales",
        data: ' . json_encode($trendValues) . ',
        borderColor: "#7c5cfc",
        backgroundColor: "rgba(124,92,252,.1)",
        tension: .4, fill: true, pointRadius: 4
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: { ticks: { callback: v => "$" + v.toLocaleString() } },
        x: { grid: { display: false } }
      }
    }
  });
}
</script>';
?>
