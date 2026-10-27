<?php
/**
 * One-time setup script.
 * Visit http://incentive.test/setup.php once, then DELETE this file.
 */

define('BASE_PATH', dirname(__DIR__));

// Load config only (no bootstrap — DB may not exist yet)
require BASE_PATH . '/config.php';

// Dev-only: seeds demo users with a known password, so it must never run in production.
if (APP_ENV === 'production') {
    http_response_code(404);
    exit;
}

$steps   = [];
$success = true;

// ── 1. Create database ──────────────────────────────────────
try {
    $dsn = sprintf('mysql:host=%s;port=%d;charset=%s', DB_HOST, DB_PORT, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `" . DB_NAME . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `" . DB_NAME . "`");
    $steps[] = ['ok', 'Database "' . DB_NAME . '" created / verified.'];
} catch (PDOException $e) {
    $steps[] = ['err', 'DB connection failed: ' . $e->getMessage()];
    $success = false;
    goto render;
}

// ── 2. Import schema ────────────────────────────────────────
try {
    $sql = file_get_contents(BASE_PATH . '/database/schema.sql');
    // Split on semicolons (skip empty statements)
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $stmt) {
        $pdo->exec($stmt);
    }
    $steps[] = ['ok', 'Schema imported successfully.'];
} catch (PDOException $e) {
    $steps[] = ['warn', 'Schema import warning (may already exist): ' . $e->getMessage()];
}

// ── 3. Seed demo users ──────────────────────────────────────
$users = [
    ['Admin',   'System Admin',    'admin@demo.test',   'password123'],
    ['Manager', 'Jane Manager',    'manager@demo.test', 'password123'],
    ['Agent',   'Alice Sales',     'alice@demo.test',   'password123'],
    ['Agent',   'Bob Closer',      'bob@demo.test',     'password123'],
    ['Agent',   'Carol Rep',       'carol@demo.test',   'password123'],
];

try {
    foreach ($users as [$role, $name, $email, $pass]) {
        // Get role id
        $stmt = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
        $stmt->execute([$role]);
        $roleId = $stmt->fetchColumn();
        if (!$roleId) continue;

        // Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn()) {
            $steps[] = ['warn', "User {$email} already exists — skipped."];
            continue;
        }

        $hash = password_hash($pass, PASSWORD_BCRYPT);
        $ins  = $pdo->prepare(
            "INSERT INTO users (role_id, name, email, password_hash, status) VALUES (?,?,?,?,'active')"
        );
        $ins->execute([$roleId, $name, $email, $hash]);
        $steps[] = ['ok', "Created user: {$email} (role: {$role})"];
    }
} catch (PDOException $e) {
    $steps[] = ['err', 'User seed failed: ' . $e->getMessage()];
    $success = false;
}

// ── 4. Seed demo deals ──────────────────────────────────────
try {
    $agentIds = [];
    foreach (['alice@demo.test', 'bob@demo.test', 'carol@demo.test'] as $email) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $id = $stmt->fetchColumn();
        if ($id) $agentIds[] = $id;
    }

    $checkDeals = $pdo->query("SELECT COUNT(*) FROM deals")->fetchColumn();
    if ($checkDeals == 0 && !empty($agentIds)) {
        $period = date('Y-m');
        $prevP  = date('Y-m', strtotime('-1 month'));

        $sampleDeals = [
            [$agentIds[0], 'Acme Corp',       'Enterprise License', 15000.00, date('Y-m-d', strtotime("first day of this month")),   'Approved'],
            [$agentIds[0], 'Beta Ltd',         'SaaS Subscription',  5500.00, date('Y-m-d', strtotime("+2 days first day of this month")), 'Pending'],
            [$agentIds[1], 'Gamma Inc',        'Hardware Bundle',    22000.00, date('Y-m-d', strtotime("first day of this month")),   'Approved'],
            [$agentIds[1], 'Delta Systems',    'Consulting',          8000.00, date('Y-m-d', strtotime("+3 days first day of this month")), 'Approved'],
            [$agentIds[2], 'Epsilon Partners', 'Annual Subscription',12000.00, date('Y-m-d', strtotime("first day of this month")),   'Approved'],
            [$agentIds[2], 'Zeta Group',       'One-Time Project',   30000.00, date('Y-m-d', strtotime("+5 days first day of this month")), 'Pending'],
            // Previous month deals
            [$agentIds[0], 'Eta Corp',         'Support Plan',        9000.00, date('Y-m-15', strtotime("first day of last month")),  'Approved'],
            [$agentIds[1], 'Theta Solutions',  'Training Package',   18000.00, date('Y-m-15', strtotime("first day of last month")),  'Approved'],
        ];

        $ins = $pdo->prepare(
            "INSERT INTO deals (agent_id, client_name, product_service, deal_value, closing_date, status)
             VALUES (?,?,?,?,?,?)"
        );
        foreach ($sampleDeals as $d) {
            $ins->execute($d);
        }
        $steps[] = ['ok', 'Sample deals seeded.'];
    } else {
        $steps[] = ['warn', 'Deals already exist — skipped seeding.'];
    }
} catch (PDOException $e) {
    $steps[] = ['warn', 'Deal seed warning: ' . $e->getMessage()];
}

// ── Update rule created_by to admin ─────────────────────────
try {
    $adminId = $pdo->query("SELECT id FROM users WHERE email = 'admin@demo.test' LIMIT 1")->fetchColumn();
    if ($adminId) {
        $pdo->prepare("UPDATE incentive_rules SET created_by = ? WHERE created_by IS NULL")->execute([$adminId]);
    }
} catch (Exception $e) {}

render:
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Setup — <?= DB_NAME ?></title>
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
<div class="container" style="max-width:640px">
  <h2 class="mb-4 fw-bold"><i class="bi bi-gear me-2 text-primary"></i>Incentive System — Setup</h2>

  <?php foreach ($steps as [$type, $msg]): ?>
  <div class="alert alert-<?= $type === 'ok' ? 'success' : ($type === 'err' ? 'danger' : 'warning') ?> py-2">
    <i class="bi bi-<?= $type === 'ok' ? 'check-circle' : 'exclamation-circle' ?> me-1"></i>
    <?= htmlspecialchars($msg) ?>
  </div>
  <?php endforeach; ?>

  <?php if ($success): ?>
  <div class="card border-success mt-3">
    <div class="card-body">
      <h5 class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Setup Complete!</h5>
      <p class="mb-2">Demo accounts (password: <code>password123</code>):</p>
      <ul class="mb-3">
        <li><strong>Admin:</strong> admin@demo.test</li>
        <li><strong>Manager:</strong> manager@demo.test</li>
        <li><strong>Agents:</strong> alice@demo.test, bob@demo.test, carol@demo.test</li>
      </ul>
      <div class="alert alert-warning py-2 mb-3">
        <i class="bi bi-shield-exclamation me-1"></i>
        <strong>Delete this file</strong> (<code>public/setup.php</code>) before deploying to production.
      </div>
      <a href="/" class="btn btn-primary">
        <i class="bi bi-box-arrow-in-right me-1"></i> Go to Login
      </a>
    </div>
  </div>
  <?php else: ?>
  <div class="alert alert-danger mt-3">
    Setup failed. Check your <code>config.php</code> DB credentials and try again.
  </div>
  <?php endif; ?>
</div>
</body>
</html>
