<?php $pageTitle = $agent ? 'Edit User' : 'Add User'; ?>
<?php $isEdit   = !empty($agent['id']); ?>

<div class="row justify-content-center">
<div class="col-lg-6">

<div class="d-flex align-items-center gap-2 mb-3">
  <a href="<?= url('agents') ?>" class="btn btn-sm btn-outline-secondary">
    <i class="bi bi-arrow-left"></i>
  </a>
  <h4 class="fw-bold mb-0"><?= $pageTitle ?></h4>
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
          action="<?= $isEdit ? url('agents/updateAgent/' . $agent['id']) : url('agents/store') ?>">
      <?= Security::csrfField() ?>

      <div class="mb-3">
        <label class="form-label fw-medium">Full Name <span class="text-danger">*</span></label>
        <input type="text" name="name" class="form-control"
               value="<?= e($agent['name'] ?? '') ?>" required maxlength="100">
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium">Email <span class="text-danger">*</span></label>
        <input type="email" name="email" class="form-control"
               value="<?= e($agent['email'] ?? '') ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium">Username</label>
        <input type="text" name="username" class="form-control"
               value="<?= e($agent['username'] ?? '') ?>" maxlength="50"
               pattern="[A-Za-z0-9._\-]{3,50}" autocomplete="off"
               placeholder="Optional - lets this user sign in without an email">
        <div class="form-text">3-50 characters: letters, numbers, dot, dash, underscore. Must be unique.</div>
      </div>

      <div class="mb-3">
        <label class="form-label fw-medium">
          Password <?= $isEdit ? '' : '<span class="text-danger">*</span>' ?>
        </label>
        <input type="password" name="password" class="form-control"
               <?= $isEdit ? '' : 'required' ?> minlength="8"
               placeholder="<?= $isEdit ? 'Leave blank to keep current password' : 'Min. 8 characters' ?>">
      </div>

      <div class="row g-3 mb-3">
        <div class="col-sm-6">
          <label class="form-label fw-medium">Role <span class="text-danger">*</span></label>
          <select name="role_id" class="form-select" required>
            <option value="">Select Role…</option>
            <?php foreach ($roles as $r): ?>
            <option value="<?= $r['id'] ?>"
              <?= (string)($agent['role_id'] ?? '') === (string)$r['id'] ? 'selected' : '' ?>>
              <?= e($r['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-sm-6">
          <label class="form-label fw-medium">Team</label>
          <select name="team_id" class="form-select">
            <option value="">No Team</option>
            <?php foreach ($teams as $t): ?>
            <option value="<?= $t['id'] ?>"
              <?= (string)($agent['team_id'] ?? '') === (string)$t['id'] ? 'selected' : '' ?>>
              <?= e($t['name']) ?>
            </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <?php if ($isEdit): ?>
      <div class="mb-4">
        <label class="form-label fw-medium">Status</label>
        <select name="status" class="form-select">
          <option value="active"   <?= ($agent['status'] ?? '') === 'active'   ? 'selected' : '' ?>>Active</option>
          <option value="inactive" <?= ($agent['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
      </div>
      <?php endif; ?>

      <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary px-4">
          <i class="bi bi-floppy me-1"></i> <?= $isEdit ? 'Update' : 'Create User' ?>
        </button>
        <a href="<?= url('agents') ?>" class="btn btn-outline-secondary">Cancel</a>
      </div>
    </form>
  </div>
</div>

</div>
</div>
