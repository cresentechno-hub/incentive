<?php $pageTitle = 'Incentive Rules'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0">Incentive Rules</h4>
  <?php if (Auth::isAdmin()): ?>
  <div class="d-flex gap-2">
    <a href="<?= url('incentives/calculate') ?>" class="btn btn-outline-primary">
      <i class="bi bi-calculator me-1"></i> Run Calculation
    </a>
    <a href="<?= url('incentives/createRule') ?>" class="btn btn-primary">
      <i class="bi bi-plus-circle me-1"></i> New Rule
    </a>
  </div>
  <?php endif; ?>
</div>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>Name</th>
            <th>Type</th>
            <th>Rate</th>
            <th>Min Threshold</th>
            <th>Max Threshold</th>
            <th>Active Period</th>
            <th>Status</th>
            <?php if (Auth::isAdmin()): ?><th>Actions</th><?php endif; ?>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($rules as $r): ?>
        <tr>
          <td>
            <div class="fw-medium"><?= e($r['name']) ?></div>
            <?php if ($r['description']): ?>
            <small class="text-muted"><?= e($r['description']) ?></small>
            <?php endif; ?>
          </td>
          <td>
            <span class="badge bg-<?= match($r['rule_type']){
              'flat'=>'info', 'percentage'=>'primary', 'tier'=>'warning', default=>'secondary'
            } ?>">
              <?= e($r['rule_type']) ?>
            </span>
          </td>
          <td class="fw-semibold">
            <?php if ($r['rule_type'] === 'flat'): ?>
              <?= money($r['rate_value']) ?>
            <?php else: ?>
              <?= number_format($r['rate_value'], 2) ?>%
            <?php endif; ?>
          </td>
          <td><?= money($r['min_threshold']) ?></td>
          <td><?= $r['max_threshold'] !== null ? money($r['max_threshold']) : '<span class="text-muted">∞</span>' ?></td>
          <td style="font-size:.82rem">
            <?= e($r['start_date']) ?>
            <?= $r['end_date'] ? ' — ' . e($r['end_date']) : ' (ongoing)' ?>
          </td>
          <td>
            <span class="badge bg-<?= $r['is_active'] ? 'success' : 'secondary' ?>">
              <?= $r['is_active'] ? 'Active' : 'Inactive' ?>
            </span>
          </td>
          <?php if (Auth::isAdmin()): ?>
          <td>
            <div class="d-flex gap-1">
              <a href="<?= url('incentives/editRule/' . $r['id']) ?>"
                 class="btn btn-sm btn-outline-secondary py-0 px-2">
                <i class="bi bi-pencil"></i>
              </a>
              <form method="POST" action="<?= url('incentives/deleteRule/' . $r['id']) ?>">
                <?= Security::csrfField() ?>
                <button class="btn btn-sm btn-outline-danger py-0 px-2">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
            </div>
          </td>
          <?php endif; ?>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($rules)): ?>
        <tr>
          <td colspan="8" class="text-center text-muted py-4">
            <i class="bi bi-sliders fs-4 d-block mb-1"></i>No incentive rules defined yet.
          </td>
        </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer text-muted small"><?= count($rules) ?> rule(s) total.</div>
</div>
