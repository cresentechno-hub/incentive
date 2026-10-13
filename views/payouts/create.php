<?php $pageTitle = 'New Payout Batch'; ?>

<div class="row justify-content-center">
<div class="col-lg-6">

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= url('payouts') ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0">New Payout Batch</h4>
</div>

<div class="alert alert-info py-2 mb-3" style="font-size:.83rem">
  Creating a batch will <strong>approve all pending incentives</strong> for the chosen period
  and group them into this batch for payout processing.
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="POST" action="<?= url('payouts/store') ?>">
      <?= Security::csrfField() ?>

      <div class="mb-3">
        <label class="form-label fw-medium">Payout Period <span class="text-danger">*</span></label>
        <input type="month" name="payout_period" class="form-control"
               value="<?= e(currentPeriod()) ?>" required>
      </div>

      <?php if (!empty($periods)): ?>
      <div class="mb-3">
        <label class="form-label text-muted small">Periods with pending incentives:</label>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach ($periods as $row): ?>
          <button type="button" class="btn btn-sm btn-outline-warning period-pill"
                  data-period="<?= e($row['payout_period']) ?>">
            <?= formatPeriod($row['payout_period']) ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <div class="mb-4">
        <label class="form-label fw-medium">Notes</label>
        <textarea name="notes" class="form-control" rows="2"
                  placeholder="Optional notes for this payout run…"></textarea>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-plus-circle me-1"></i> Create Batch
        </button>
        <a href="<?= url('payouts') ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

</div>
</div>

<script>
document.querySelectorAll('.period-pill').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelector('[name="payout_period"]').value = btn.dataset.period;
  });
});
</script>
