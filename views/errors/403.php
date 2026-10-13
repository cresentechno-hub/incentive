<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Access Denied — <?= APP_NAME ?></title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center min-vh-100">
<div class="text-center">
  <i class="bi bi-shield-x text-danger" style="font-size:4rem"></i>
  <h1 class="display-6 fw-bold mt-3">Access Denied</h1>
  <p class="text-muted">You don't have permission to view this page.</p>
  <a href="<?= url('dashboard') ?>" class="btn btn-primary">
    <i class="bi bi-house me-1"></i> Back to Dashboard
  </a>
</div>
</body>
</html>
