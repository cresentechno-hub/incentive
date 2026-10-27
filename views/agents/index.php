<?php $pageTitle = 'Users & Agents'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <h4 class="fw-bold mb-0">Users & Agents</h4>
  <?php if (Auth::isAdmin()): ?>
  <a href="<?= url('agents/create') ?>" class="btn btn-primary">
    <i class="bi bi-person-plus me-1"></i> Add User
  </a>
  <?php endif; ?>
</div>

<div class="card shadow-sm">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr><th>Name</th><th>Username</th><th>Email</th><th>Role</th><th>Team</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
        <?php foreach ($agents as $a): ?>
        <tr>
          <td class="fw-medium"><?= e($a['name']) ?></td>
          <td><?= e($a['username'] ?? '—') ?></td>
          <td><?= e($a['email']) ?></td>
          <td>
            <span class="badge bg-<?= match($a['role_name']){
              'Admin'=>'danger','Manager'=>'primary','Agent'=>'secondary', default=>'secondary'
            } ?>">
              <?= e($a['role_name']) ?>
            </span>
          </td>
          <td><?= e($a['team_name'] ?? '—') ?></td>
          <td>
            <span class="badge bg-<?= $a['status'] === 'active' ? 'success' : 'secondary' ?>">
              <?= ucfirst($a['status']) ?>
            </span>
          </td>
          <td>
            <div class="d-flex gap-1">
              <?php if ($a['role_name'] === 'Agent'): ?>
              <a href="<?= url('agents/view/' . $a['id']) ?>"
                 class="btn btn-sm btn-outline-primary py-0 px-2" title="Performance">
                <i class="bi bi-bar-chart"></i>
              </a>
              <?php endif; ?>
              <?php if (Auth::isAdmin()): ?>
              <a href="<?= url('agents/edit/' . $a['id']) ?>"
                 class="btn btn-sm btn-outline-secondary py-0 px-2" title="Edit">
                <i class="bi bi-pencil"></i>
              </a>
              <?php endif; ?>
            </div>
          </td>
        </tr>
        <?php endforeach; ?>
        <?php if (empty($agents)): ?>
        <tr>
          <td colspan="6" class="text-center text-muted py-4">No users found.</td>
        </tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
  <div class="card-footer text-muted small"><?= count($agents) ?> user(s).</div>
</div>
