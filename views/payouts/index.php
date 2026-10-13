<?php $pageTitle = 'Payout Batches'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0">Payout Batches</h4>
  <a href="<?= url('payouts/create') ?>" class="btn btn-primary">
    <i class="bi bi-plus-circle me-1"></i> New Batch
  </a>
</div>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Period</th>
            <th>Batch Date</th>
            <th>Agents</th>
            <th>Total Amount</th>
            <th>Status</th>
            <th>Processed By</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($batches as $b): ?>
        <tr>
          <td class="text-muted"><?= e($b['id']) ?></td>
          <td class="fw-medium"><?= formatPeriod($b['payout_period']) ?></td>
          <td><?= e($b['batch_date']) ?></td>
          <td><?= e($b['agent_count']) ?></td>
          <td class="fw-semibold"><?= money($b['total_amount']) ?></td>
          <td><span class="badge bg-<?= statusBadge($b['status']) ?>"><?= e($b['status']) ?></span></td>
          <td><?= e($b['processor_name'] ?? '—') ?></td>
          <td>
            <div class="d-flex gap-1">
              <a href="<?= url('payouts/view/' . $b['id']) ?>"
                 class="btn btn-sm btn-outline-primary py-0 px-2" title="View">
                <i class="bi bi-eye"></i>
              </a>
              <a href="<?= url('payouts/export/' . $b['id']) ?>"
                 class="btn btn-sm btn-outline-secondary py-0 px-2" title="Export CSV">
                <i class="bi bi-download"></i>
              </a>
              <?php if ($b['status'] === 'draft' && Auth::isAdmin()): ?>
              <form method="POST" action="<?= url('payouts/complete/' . $b['id']) ?>">
                <?= Security::csrfField() ?>
                <button class="btn btn-sm btn-success py-0 px-2" title="Complete">
                  <i class="bi bi-check-circle"></i>
                </button>
              </form>
              <form method="POST" action="<?= url('payouts/cancel/' . $b['id']) ?>">
                <?= Security::csrfField() ?>
                <button class="btn btn-sm btn-outline-danger py-0 px-2" title="Cancel">
                  <i class="bi bi-x-circle"></i>
                </button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($batches)): ?>
        <tr>
          <td colspan="8" class="text-center text-muted py-4">
            <i class="bi bi-cash-stack fs-4 d-block mb-1"></i>No payout batches yet.
          </td>
        </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
