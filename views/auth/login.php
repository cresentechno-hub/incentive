<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login — <?= APP_NAME ?></title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  body { font-family: 'Inter', sans-serif; background: #f0f2f5; min-height: 100vh;
         display: flex; align-items: center; justify-content: center; }
  .login-card { width: 400px; border: none; border-radius: 1rem;
                box-shadow: 0 8px 40px rgba(0,0,0,.12); }
  .brand-icon { width: 52px; height: 52px; background: #7c5cfc; border-radius: .75rem;
                display: flex; align-items: center; justify-content: center; font-size: 1.5rem; color: #fff; }
  .form-control:focus { border-color: #7c5cfc; box-shadow: 0 0 0 .2rem rgba(124,92,252,.2); }
  .btn-primary { background: #7c5cfc; border-color: #7c5cfc; }
  .btn-primary:hover { background: #6b4fe8; border-color: #6b4fe8; }
</style>
</head>
<body>
<div class="card login-card p-4">
  <div class="card-body">
    <div class="d-flex align-items-center gap-3 mb-4">
      <div class="brand-icon"><i class="bi bi-graph-up-arrow"></i></div>
      <div>
        <h5 class="mb-0 fw-bold"><?= APP_NAME ?></h5>
        <small class="text-muted">Sign in to continue</small>
      </div>
    </div>

    <?php foreach ($errors ?? [] as $err): ?>
    <div class="alert alert-danger py-2 mb-3">
      <i class="bi bi-exclamation-circle me-1"></i><?= e($err) ?>
    </div>
    <?php endforeach; ?>

    <form method="POST" action="<?= url('login') ?>" novalidate>
      <?= Security::csrfField() ?>

      <div class="mb-3">
        <label class="form-label fw-medium">Email address</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" name="email" class="form-control"
                 value="<?= e($_POST['email'] ?? '') ?>"
                 placeholder="you@company.com" required autofocus>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label fw-medium">Password</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" name="password" class="form-control"
                 placeholder="••••••••" required id="pwField">
          <button type="button" class="btn btn-outline-secondary"
                  onclick="const p=document.getElementById('pwField');p.type=p.type==='password'?'text':'password'">
            <i class="bi bi-eye"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
        <i class="bi bi-box-arrow-in-right me-1"></i> Sign In
      </button>
    </form>

    <hr class="my-4">
    <div class="text-center text-muted" style="font-size:.8rem">
      <strong>Demo accounts</strong><br>
      admin@demo.test / password123<br>
      manager@demo.test / password123<br>
      agent@demo.test / password123
    </div>
  </div>
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>
</body>
</html>
