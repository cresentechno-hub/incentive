<?php $pageTitle = 'Deals'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0">Deals</h4>
  <a href="<?= url('deals/create') ?>" class="btn btn-primary">
    <i class="bi bi-plus-circle me-1"></i> Log Deal
  </a>
</div>

<!-- Filters -->
<div class="card shadow-sm mb-3">
  <div class="card-body py-2">
    <form class="row g-2 align-items-end" method="GET" action="<?= url('deals') ?>">
      <div class="col-auto">
        <input type="text" name="search" class="form-control form-control-sm"
               placeholder="Search client / agent…" value="<?= e($filters['search']) ?>">
      </div>
      <?php if (!Auth::isAgent() && !empty($agents)): ?>
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
          <?php foreach (['Pending','Approved','Rejected','Paid'] as $s): ?>
          <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= $s ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-auto">
        <input type="month" name="period" class="form-control form-control-sm"
               value="<?= e($filters['period']) ?>">
      </div>
      <div class="col-auto">
        <button class="btn btn-sm btn-secondary">Filter</button>
        <a href="<?= url('deals') ?>" class="btn btn-sm btn-outline-secondary">Reset</a>
      </div>
    </form>
  </div>
</div>

<!-- Table -->
<div class="card shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th>#</th>
            <th>Client</th>
            <?php if (!Auth::isAgent()): ?><th>Agent</th><?php endif; ?>
            <th>Product / Service</th>
            <th>Value</th>
            <th>Closing Date</th>
            <th>Status</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($deals as $d): ?>
        <tr>
          <td class="text-muted"><?= e($d['id']) ?></td>
          <td class="fw-medium"><?= e($d['client_name']) ?></td>
          <?php if (!Auth::isAgent()): ?>
          <td><?= e($d['agent_name']) ?></td>
          <?php endif; ?>
          <td><?= e($d['product_service'] ?? '—') ?></td>
          <td><?= money($d['deal_value']) ?></td>
          <td><?= e($d['closing_date']) ?></td>
          <td><span class="badge bg-<?= statusBadge($d['status']) ?>"><?= e($d['status']) ?></span></td>
          <td>
            <div class="d-flex gap-1">
              <?php if (Auth::isAgent() && $d['status'] === 'Pending'): ?>
              <a href="<?= url('deals/edit/' . $d['id']) ?>" class="btn btn-xs btn-outline-secondary btn-sm py-0 px-2">
                <i class="bi bi-pencil"></i>
              </a>
              <?php endif; ?>

              <?php if (Auth::can('Admin','Manager') && $d['status'] === 'Pending'): ?>
              <form method="POST" action="<?= url('deals/approve/' . $d['id']) ?>" class="d-inline">
                <?= Security::csrfField() ?>
                <button class="btn btn-sm btn-success py-0 px-2">
                  <i class="bi bi-check-circle"></i>
                </button>
              </form>
              <form method="POST" action="<?= url('deals/reject/' . $d['id']) ?>" class="d-inline">
                <?= Security::csrfField() ?>
                <button class="btn btn-sm btn-danger py-0 px-2">
                  <i class="bi bi-x-circle"></i>
                </button>
              </form>
              <?php endif; ?>

              <?php if (Auth::isAdmin()): ?>
              <form method="POST" action="<?= url('deals/delete/' . $d['id']) ?>" class="d-inline">
                <?= Security::csrfField() ?>
                <button class="btn btn-sm btn-outline-danger py-0 px-2">
                  <i class="bi bi-trash"></i>
                </button>
              </form>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($deals)): ?>
        <tr>
          <td colspan="8" class="text-center text-muted py-4">
            <i class="bi bi-inbox fs-4 d-block mb-1"></i>No deals found.
          </td>
        </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer text-muted small">
    <?= count($deals) ?> deal(s) found.
  </div>
</div>
