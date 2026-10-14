<?php $pageTitle = $deal ? 'Edit Deal' : 'Log New Deal'; ?>
<?php $isEdit   = !empty($deal['id']); ?>

<div class="row justify-content-center">
<div class="col-lg-7">

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= url('deals') ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0"><?= $pageTitle ?></h4>
</div>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger py-2">
  <ul class="mb-0 ps-3">
    <?php foreach ($errors as $e): ?>
    <li><?= e($e) ?></li>
    <?php endforeach; ?>
  </ul>
</div>
<?php endif; ?>

<div class="card shadow-sm">
  <div class="card-body">
    <form method="POST"
          action="<?= $isEdit ? url('deals/update/' . $deal['id']) : url('deals/store') ?>">
      <?= Security::csrfField() ?>

      <?php if (Auth::can('Admin','Manager') && !empty($agents)): ?>
      <div class="mb-3">
        <label class="form-label fw-medium">Agent <span class="text-danger">*</span></label>
        <select name="agent_id" class="form-select" required>
          <option value="">Select Agent…</option>
          <?php foreach ($agents as $a): ?>
          <option value="<?= $a['id'] ?>"
            <?= (string)($deal['agent_id'] ?? '') === (string)$a['id'] ? 'selected' : '' ?>>
            <?= e($a['name']) ?>
          </option>
          <?php endforeach; ?>
        </select>
      </div>
      <?php endif; ?>

      <div class="mb-3">
        <label class="form-label fw-medium">Client Name <span class="text-danger">*</span></label>
        <input type="text" name="client_name" class="form-control"
               value="<?= e($deal['client_name'] ?? '') ?>" required maxlength="150"
               placeholder="e.g. Acme Corporation">
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium">Quotation No</label>
        <input type="text" name="product_service" class="form-control"
               value="<?= e($deal['product_service'] ?? '') ?>" maxlength="100"
               placeholder="e.g. Enterprise License">
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label class="form-label fw-medium">Deal Value ($) <span class="text-danger">*</span></label>
          <div class="input-group">
            <span class="input-group-text">$</span>
            <input type="number" step="0.01" min="0" name="deal_value" class="form-control"
                   value="<?= e($deal['deal_value'] ?? '') ?>" required placeholder="0.00">
          </div>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-medium">Notes</label>
        <textarea name="notes" class="form-control" rows="3"
                  placeholder="Optional deal notes or context…"><?= e($deal['notes'] ?? '') ?></textarea>
      </div>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-<?= $isEdit ? 'floppy' : 'plus-circle' ?> me-1"></i>
          <?= $isEdit ? 'Update Deal' : 'Log Deal' ?>
        </button>
        <a href="<?= url('deals') ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

</div>
</div>
