<?php $pageTitle = 'Run Incentive Calculation'; ?>

<div class="row justify-content-center">
<div class="col-lg-6">

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= url('incentives/rules') ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0">Run Incentive Calculation</h4>
</div>

<div class="alert alert-warning py-2 mb-3">
  <i class="bi bi-info-circle me-1"></i>
  <strong>Note:</strong> This evaluates all <em>Approved</em> deals in the selected period against active
  incentive rules. Already-calculated deal+rule combinations are skipped automatically.
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="POST" action="<?= url('incentives/process') ?>">
      <?= Security::csrfField() ?>

      <div class="mb-4">
        <label class="form-label fw-medium">Select Period <span class="text-danger">*</span></label>
        <input type="month" name="period" class="form-control"
               value="<?= e(currentPeriod()) ?>" required>
        <div class="form-text">Only approved deals with a closing date in this month will be processed.</div>
      </div>

      <?php if (!empty($periods)): ?>
      <div class="mb-4">
        <label class="form-label text-muted small">Or choose a recent period with deals:</label>
        <div class="d-flex flex-wrap gap-2">
          <?php foreach (array_slice($periods, 0, 12) as $p): ?>
          <button type="button" class="btn btn-sm btn-outline-secondary period-quick"
                  data-period="<?= e($p) ?>">
            <?= formatPeriod($p) ?>
          </button>
          <?php endforeach; ?>
        </div>
      </div>
      <?php endif; ?>

      <button type="submit" class="btn btn-primary px-4">
        <i class="bi bi-calculator me-1"></i> Run Calculation
      </button>
    </form>
  </div>
</div>

</div>
</div>

<script>
document.querySelectorAll('.period-quick').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelector('[name="period"]').value = btn.dataset.period;
  });
});
</script>
