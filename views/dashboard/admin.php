<?php $pageTitle = 'Dashboard'; ?>

<!-- Period selector -->
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0">Overview — <?= formatPeriod($period) ?></h4>
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
    ['label'=>'Total Agents',     'value'=>$stats['total_agents'] ?? 0,    'icon'=>'bi-people',       'bg'=>'#eef0ff','ic'=>'#7c5cfc'],
    ['label'=>'Approved Deals',   'value'=>$stats['approved_deals'] ?? 0,  'icon'=>'bi-check-circle', 'bg'=>'#e8f5e9','ic'=>'#28a745'],
    ['label'=>'Pending Approval', 'value'=>$stats['pending_deals'] ?? 0,   'icon'=>'bi-clock-history','bg'=>'#fff8e1','ic'=>'#fd7e14'],
    ['label'=>'Approved Revenue', 'value'=>money($stats['total_approved_value'] ?? 0),'icon'=>'bi-currency-dollar','bg'=>'#e3f2fd','ic'=>'#0d6efd'],
    ['label'=>'Pending Payouts',  'value'=>money($stats['pending_incentives'] ?? 0),  'icon'=>'bi-wallet2',          'bg'=>'#f9e8ff','ic'=>'#d63384'],
    ['label'=>'Draft Batches',    'value'=>$stats['draft_batches'] ?? 0,   'icon'=>'bi-file-earmark-text','bg'=>'#e8f5e9','ic'=>'#20c997'],
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

<!-- Charts row -->
<div class="row g-3 mb-4">
  <!-- Revenue trend -->
  <div class="col-lg-8">
    <div class="card shadow-sm h-100">
      <div class="card-header border-0 bg-transparent pt-3 pb-0">
        <h6 class="fw-semibold mb-0">Monthly Revenue Trend</h6>
      </div>
      <div class="card-body">
        <canvas id="trendChart" height="120"></canvas>
      </div>
    </div>
  </div>
  <!-- Top agents -->
  <div class="col-lg-4">
    <div class="card shadow-sm h-100">
      <div class="card-header border-0 bg-transparent pt-3 pb-0">
        <h6 class="fw-semibold mb-0">Top Agents — <?= formatPeriod($period) ?></h6>
      </div>
      <div class="card-body">
        <?php if (empty($topAgents)): ?>
        <p class="text-muted small">No data for this period.</p>
        <?php else: ?>
        <?php
          $maxSales = max(array_column($topAgents, 'total_sales')) ?: 1;
        ?>
        <?php foreach ($topAgents as $a): ?>
        <div class="mb-3">
          <div class="d-flex justify-content-between mb-1" style="font-size:.82rem">
            <span class="fw-medium"><?= e($a['name']) ?></span>
            <span class="text-muted"><?= money($a['total_sales']) ?></span>
          </div>
          <div class="progress" style="height:6px">
            <div class="progress-bar" style="width:<?= ($a['total_sales']/$maxSales)*100 ?>%;background:#7c5cfc"></div>
          </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- Recent deals + Audit log -->
<div class="row g-3">
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-header border-0 bg-transparent pt-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-semibold mb-0">Recent Deals</h6>
        <a href="<?= url('deals') ?>" class="btn btn-sm btn-outline-primary">View All</a>
      </div>
      <div class="card-body p-0">
        <div class="table-responsive">
          <table class="table table-hover mb-0">
            <thead class="table-light"><tr>
              <th>Client</th><th>Agent</th><th>Value</th><th>Date</th><th>Status</th>
            </tr></thead>
            <tbody>
            <?php foreach ($recentDeals as $d): ?>
            <tr>
              <td class="fw-medium"><?= e($d['client_name']) ?></td>
              <td><?= e($d['agent_name']) ?></td>
              <td><?= money($d['deal_value']) ?></td>
              <td><?= e($d['closing_date']) ?></td>
              <td><span class="badge bg-<?= statusBadge($d['status']) ?>"><?= e($d['status']) ?></span></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($recentDeals)): ?>
            <tr><td colspan="5" class="text-center text-muted py-3">No deals yet.</td></tr>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4">
    <div class="card shadow-sm">
      <div class="card-header border-0 bg-transparent pt-3">
        <h6 class="fw-semibold mb-0">Recent Activity</h6>
      </div>
      <div class="card-body p-0">
        <ul class="list-group list-group-flush" style="font-size:.82rem">
        <?php foreach (array_slice($auditLog, 0, 8) as $log): ?>
        <li class="list-group-item d-flex justify-content-between">
          <div>
            <span class="badge bg-secondary me-1"><?= e($log['action']) ?></span>
            <?= e($log['user_name'] ?? 'System') ?>
          </div>
          <small class="text-muted"><?= date('d M H:i', strtotime($log['created_at'])) ?></small>
        </li>
        <?php endforeach; ?>
        <?php if (empty($auditLog)): ?>
        <li class="list-group-item text-muted small text-center py-3">No activity yet.</li>
        <?php endif; ?>
        </ul>
      </div>
    </div>
  </div>
</div>

<?php
// Build chart data from trend
$trendLabels = array_map(fn($r) => formatPeriod($r['period']), $trend);
$trendValues = array_map(fn($r) => round($r['total_value'], 2), $trend);

$extraJs = '<script>
const ctx = document.getElementById("trendChart");
if (ctx) {
  new Chart(ctx, {
    type: "bar",
    data: {
      labels: ' . json_encode($trendLabels) . ',
      datasets: [{
        label: "Approved Revenue",
        data: ' . json_encode($trendValues) . ',
        backgroundColor: "rgba(124,92,252,.7)",
        borderRadius: 6
      }]
    },
    options: {
      responsive: true,
      plugins: { legend: { display: false } },
      scales: {
        y: { ticks: { callback: v => "$" + v.toLocaleString() }, grid: { color: "#f0f0f0" } },
        x: { grid: { display: false } }
      }
    }
  });
}
</script>';
?>
