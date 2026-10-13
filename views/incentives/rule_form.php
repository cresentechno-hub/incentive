<?php $pageTitle = $rule ? 'Edit Incentive Rule' : 'New Incentive Rule'; ?>
<?php $isEdit   = !empty($rule['id']); ?>

<div class="row justify-content-center">
<div class="col-lg-7">

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= url('incentives/rules') ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0"><?= $pageTitle ?></h4>
</div>

<!-- Rule type reference card -->
<div class="alert alert-info py-2 mb-3" style="font-size:.83rem">
  <strong>Rule Types:</strong>
  <span class="badge bg-info text-dark">flat</span> — fixed $ bonus when deal_value ≥ min_threshold.
  <span class="badge bg-primary">percentage</span> — % of deal_value when deal_value is in range.
  <span class="badge bg-warning text-dark">tier</span> — % of deal_value when agent's monthly total is in range.
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger py-2">
  <ul class="mb-0 ps-3">
    <?php foreach ($errors as $er): ?><li><?= e($er) ?></li><?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="POST"
          action="<?= $isEdit ? url('incentives/updateRule/' . $rule['id']) : url('incentives/storeRule') ?>">
      <?= Security::csrfField() ?>

      <div class="row g-3 mb-3">
        <div class="col-sm-8">
          <label class="form-label fw-medium">Rule Name <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control"
                 value="<?= e($rule['name'] ?? '') ?>" required maxlength="100">
        </div>
        <div class="col-sm-4">
          <label class="form-label fw-medium">Rule Type <span class="text-danger">*</span></label>
          <select name="rule_type" class="form-select" required id="ruleTypeSelect">
            <option value="">Select…</option>
            <?php foreach (['flat','percentage','tier'] as $t): ?>
            <option value="<?= $t ?>" <?= ($rule['rule_type'] ?? '') === $t ? 'selected' : '' ?>>
              <?= ucfirst($t) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium">Description</label>
        <input type="text" name="description" class="form-control"
               value="<?= e($rule['description'] ?? '') ?>" maxlength="200"
               placeholder="Brief description of this rule's intent">
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label class="form-label fw-medium" id="rateLabel">
            Rate Value <span class="text-danger">*</span>
            <small id="rateHint" class="text-muted"></small>
          </label>
          <div class="input-group">
            <span class="input-group-text" id="ratePrefix">—</span>
            <input type="number" step="0.0001" min="0" name="rate_value" class="form-control"
                   value="<?= e($rule['rate_value'] ?? '') ?>" required>
          </div>
        </div>
        <div class="col-sm-6">
          <label class="form-label fw-medium">Target Metric</label>
          <select name="target_metric" class="form-select">
            <option value="deal_value"    <?= ($rule['target_metric'] ?? 'deal_value') === 'deal_value'    ? 'selected' : '' ?>>Deal Value</option>
            <option value="deal_count"    <?= ($rule['target_metric'] ?? '') === 'deal_count'    ? 'selected' : '' ?>>Deal Count</option>
            <option value="period_total"  <?= ($rule['target_metric'] ?? '') === 'period_total'  ? 'selected' : '' ?>>Period Total</option>
          </select>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label class="form-label fw-medium">Min Threshold ($)</label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" min="0" name="min_threshold" class="form-control"
                   value="<?= e($rule['min_threshold'] ?? '0') ?>" placeholder="0.00">
          </div>
          <div class="form-text">Leave 0 for no minimum.</div>
        </div>
        <div class="col-sm-6">
          <label class="form-label fw-medium">Max Threshold ($)</label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" min="0" name="max_threshold" class="form-control"
                   value="<?= e($rule['max_threshold'] ?? '') ?>" placeholder="Unlimited">
          </div>
          <div class="form-text">Leave blank for no upper limit.</div>
        </div>
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label class="form-label fw-medium">Start Date <span class="text-danger">*</span></label>
          <input type="date" name="start_date" class="form-control"
                 value="<?= e($rule['start_date'] ?? date('Y-m-d')) ?>" required>
        </div>
        <div class="col-sm-6">
          <label class="form-label fw-medium">End Date</label>
          <input type="date" name="end_date" class="form-control"
                 value="<?= e($rule['end_date'] ?? '') ?>">
          <div class="form-text">Leave blank for an ongoing rule.</div>
        </div>
      </div>

      <div class="form-check form-switch mb-4">
        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" value="1"
               <?= ($rule['is_active'] ?? 1) ? 'checked' : '' ?>>
        <label class="form-check-label" for="isActive">Rule is active</label>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-floppy me-1"></i> <?= $isEdit ? 'Update Rule' : 'Save Rule' ?>
        </button>
        <a href="<?= url('incentives/rules') ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

</div>
</div>

<script>
const ruleTypeSelect = document.getElementById('ruleTypeSelect');
const ratePrefix     = document.getElementById('ratePrefix');
const rateHint       = document.getElementById('rateHint');

function updateRateUI() {
  const type = ruleTypeSelect.value;
  if (type === 'flat') {
    ratePrefix.textContent = '$';
    rateHint.textContent   = '(fixed dollar amount)';
  } else if (type === 'percentage' || type === 'tier') {
    ratePrefix.textContent = '%';
    rateHint.textContent   = '(percentage of deal value)';
  } else {
    ratePrefix.textContent = '—';
    rateHint.textContent   = '';
  }
}
ruleTypeSelect.addEventListener('change', updateRateUI);
updateRateUI(); // init on load
</script>
