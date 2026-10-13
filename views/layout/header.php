<!doctype html>
<html lang="en" data-bs-theme="light">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($pageTitle) ? e($pageTitle) . ' — ' : '' ?><?= APP_NAME ?></title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-icons/1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
<style>
  :root {
    --bs-font-sans-serif: 'Inter', sans-serif;
    --sidebar-w: 240px;
    --topbar-h: 56px;
  }
  body { font-family: var(--bs-font-sans-serif); background: #f8f9fb; }

  /* ── Sidebar ─────────────────────────── */
  #sidebar {
    width: var(--sidebar-w);
    min-height: 100vh;
    background: #1a1f2e;
    color: #adb5bd;
    position: fixed;
    top: 0; left: 0;
    display: flex;
    flex-direction: column;
    z-index: 1040;
    transition: transform .25s ease;
  }
  #sidebar .brand {
    height: var(--topbar-h);
    display: flex;
    align-items: center;
    padding: 0 1.25rem;
    color: #fff;
    font-weight: 700;
    font-size: 1rem;
    border-bottom: 1px solid rgba(255,255,255,.07);
    text-decoration: none;
  }
  #sidebar .brand i { font-size: 1.4rem; margin-right: .6rem; color: #7c5cfc; }
  #sidebar .nav-label {
    font-size: .68rem;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #6c757d;
    padding: 1rem 1.25rem .25rem;
  }
  #sidebar .nav-link {
    color: #adb5bd;
    padding: .45rem 1.25rem;
    border-radius: .375rem;
    margin: .1rem .5rem;
    font-size: .875rem;
    display: flex;
    align-items: center;
    gap: .55rem;
    transition: background .15s, color .15s;
  }
  #sidebar .nav-link:hover, #sidebar .nav-link.active {
    background: rgba(124,92,252,.18);
    color: #c5b8fd;
  }
  #sidebar .nav-link i { font-size: 1rem; width: 1.1rem; text-align: center; }
  #sidebar .sidebar-footer {
    margin-top: auto;
    padding: 1rem 1.25rem;
    border-top: 1px solid rgba(255,255,255,.07);
    font-size: .8rem;
  }

  /* ── Main content ────────────────────── */
  #main { margin-left: var(--sidebar-w); min-height: 100vh; }
  #topbar {
    height: var(--topbar-h);
    background: #fff;
    border-bottom: 1px solid #e9ecef;
    display: flex;
    align-items: center;
    padding: 0 1.5rem;
    position: sticky;
    top: 0;
    z-index: 1030;
  }
  .page-content { padding: 1.75rem 1.5rem; }

  /* ── Cards ───────────────────────────── */
  .stat-card { border: none; border-radius: .75rem; padding: 1.25rem 1.5rem; }
  .stat-card .stat-icon {
    width: 48px; height: 48px;
    border-radius: .6rem;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
  }
  .stat-card .stat-value { font-size: 1.6rem; font-weight: 700; line-height: 1.1; }
  .stat-card .stat-label { font-size: .78rem; color: #6c757d; margin-top: .15rem; }

  /* ── Tables ──────────────────────────── */
  .table { font-size: .875rem; }
  .table th { font-weight: 600; color: #495057; border-top: none; }
  .badge { font-size: .75rem; }

  /* ── Responsive sidebar ──────────────── */
  @media (max-width: 767.98px) {
    #sidebar { transform: translateX(-100%); }
    #sidebar.show { transform: translateX(0); }
    #main { margin-left: 0; }
  }
</style>
</head>
<body>

<?php if (Auth::check()): ?>

<!-- ── Sidebar ──────────────────────────────────────── -->
<nav id="sidebar">
  <a class="brand" href="<?= url('dashboard') ?>">
    <i class="bi bi-graph-up-arrow"></i>
    <?= APP_NAME ?>
  </a>

  <div class="mt-2">
    <span class="nav-label">Main</span>
    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'dashboard') || str_ends_with(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') ? 'active' : '' ?>"
       href="<?= url('dashboard') ?>">
      <i class="bi bi-speedometer2"></i> Dashboard
    </a>

    <?php if (Auth::can('Admin', 'Manager')): ?>
    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/agents') ? 'active' : '' ?>"
       href="<?= url('agents') ?>">
      <i class="bi bi-people"></i> Agents
    </a>
    <?php endif; ?>

    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/deals') ? 'active' : '' ?>"
       href="<?= url('deals') ?>">
      <i class="bi bi-briefcase"></i> Deals
    </a>

    <?php if (Auth::can('Admin', 'Manager')): ?>
    <span class="nav-label">Incentives</span>
    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'incentives/rules') ? 'active' : '' ?>"
       href="<?= url('incentives/rules') ?>">
      <i class="bi bi-sliders"></i> Rules
    </a>
    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'incentives/calculate') ? 'active' : '' ?>"
       href="<?= url('incentives/calculate') ?>">
      <i class="bi bi-calculator"></i> Calculate
    </a>
    <?php endif; ?>
    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], 'incentives/ledger') ? 'active' : '' ?>"
       href="<?= url('incentives/ledger') ?>">
      <i class="bi bi-journal-text"></i> Ledger
    </a>

    <?php if (Auth::can('Admin', 'Manager')): ?>
    <span class="nav-label">Finance</span>
    <a class="nav-link <?= str_contains($_SERVER['REQUEST_URI'], '/payouts') ? 'active' : '' ?>"
       href="<?= url('payouts') ?>">
      <i class="bi bi-cash-stack"></i> Payouts
    </a>
    <?php endif; ?>
  </div>

  <div class="sidebar-footer">
    <div class="d-flex align-items-center gap-2">
      <div class="bg-secondary rounded-circle d-flex align-items-center justify-content-center"
           style="width:30px;height:30px;font-size:.8rem;color:#fff;flex-shrink:0">
        <?= strtoupper(substr(Auth::user()['name'] ?? 'U', 0, 1)) ?>
      </div>
      <div style="line-height:1.2">
        <div class="text-white" style="font-size:.8rem;font-weight:500"><?= e(Auth::user()['name'] ?? '') ?></div>
        <div style="font-size:.7rem;color:#6c757d"><?= e(Auth::role()) ?></div>
      </div>
    </div>
    <a href="<?= url('logout') ?>" class="btn btn-sm btn-outline-secondary mt-2 w-100">
      <i class="bi bi-box-arrow-right"></i> Logout
    </a>
  </div>
</nav>

<!-- ── Main wrapper ─────────────────────────────────── -->
<div id="main">
  <header id="topbar">
    <button class="btn btn-sm btn-outline-secondary d-md-none me-2" id="sidebarToggle">
      <i class="bi bi-list"></i>
    </button>
    <span class="fw-semibold"><?= isset($pageTitle) ? e($pageTitle) : '' ?></span>
    <div class="ms-auto d-flex align-items-center gap-3 text-secondary" style="font-size:.85rem">
      <span><i class="bi bi-calendar3 me-1"></i><?= date('d M Y') ?></span>
    </div>
  </header>

  <!-- Flash messages -->
  <div class="px-4 pt-3">
  <?php foreach (['success','warning','danger','info'] as $t): ?>
    <?php foreach (Session::getFlash($t) as $msg): ?>
    <div class="alert alert-<?= $t ?> alert-dismissible fade show py-2" role="alert">
      <?= e($msg) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endforeach; ?>
  <?php endforeach; ?>
  </div>

  <div class="page-content">

<?php else: ?>
<!-- Login page — no sidebar wrapper needed; views/auth/login.php renders full page -->
<?php endif; ?>
