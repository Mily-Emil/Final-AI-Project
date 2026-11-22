<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$currentRole = $_SESSION['user-role'] ?? 'guest';
$currentName = $_SESSION['user-name'] ?? 'Pengguna';
$currentEmail = $_SESSION['user-email'] ?? '';
$activeMenu  = $data['active_menu'] ?? 'landing';
$hideDefaultNav = !empty($data['hide_default_nav']);
$isSaaSWorkspace = (strpos($activeMenu, 'admin') === 0 || strpos($activeMenu, 'petugas') === 0) && !$hideDefaultNav;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
  <title><?php echo htmlspecialchars($data['title'] ?? APP_NAME . ' - ' . APP_INSTITUTION); ?></title>

  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23F2A900'><circle cx='12' cy='12' r='10'/></svg>">

  <!-- Bootstrap 5 & Icons (Local) -->
  <link rel="stylesheet" href="<?php echo APP_PATH; ?>/public/assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo APP_PATH; ?>/public/assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

  <style>
    :root {
      --unklab-navy: #0A2240;
      --unklab-navy-deep: #07182E;
      --unklab-royal: #163A6C;
      --unklab-gold: #F2A900;
      --unklab-gold-hover: #DC9800;
      --unklab-gold-soft: #FFFBEB;
      --unklab-surface: #F8FAFC;
      --unklab-card-bg: #FFFFFF;
      --unklab-border: #E2E8F0;
      --unklab-border-subtle: #F1F5F9;
      --unklab-text: #0F172A;
      --unklab-text-muted: #64748B;
      --macro-carb: #163A6C;
      --macro-protein: #0D9488;
      --macro-fat: #F2A900;
      --macro-fiber: #64748B;
      --saas-shadow-xs: 0 1px 2px rgba(15, 23, 42, 0.04);
      --saas-shadow-sm: 0 2px 8px -2px rgba(15, 23, 42, 0.06), 0 1px 3px rgba(15, 23, 42, 0.03);
      --saas-shadow-md: 0 12px 32px -8px rgba(10, 34, 64, 0.10), 0 2px 6px rgba(10, 34, 64, 0.04);
      --sidebar-width: 252px;
    }

    * {
      box-sizing: border-box;
    }

    body {
      background-color: var(--unklab-surface);
      color: var(--unklab-text);
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
    }

    h1, h2, h3, h4, h5, h6 {
      letter-spacing: -0.02em;
    }

    .tabular-nums {
      font-variant-numeric: tabular-nums;
    }

    /* Modern SaaS Public Navbar */
    .navbar-unklab {
      background-color: rgba(10, 34, 64, 0.96);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }
    .navbar-unklab .nav-link {
      color: rgba(255, 255, 255, 0.72);
      font-weight: 500;
      font-size: 0.875rem;
      padding: 0.45rem 0.85rem;
      border-radius: 8px;
      transition: all 0.16s ease;
    }
    .navbar-unklab .nav-link:hover,
    .navbar-unklab .nav-link.active {
      color: #FFFFFF;
      background-color: rgba(255, 255, 255, 0.09);
    }

    /* Modern SaaS Sidebar Workspace (Admin & Petugas) */
    .saas-shell {
      display: flex;
      min-height: 100vh;
      width: 100%;
    }
    .saas-sidebar {
      width: var(--sidebar-width);
      background: var(--unklab-navy);
      color: #FFFFFF;
      display: flex;
      flex-direction: column;
      position: fixed;
      top: 0;
      bottom: 0;
      left: 0;
      z-index: 1030;
      border-right: 1px solid rgba(255, 255, 255, 0.08);
    }
    .saas-sidebar-brand {
      height: 64px;
      padding: 0 1.25rem;
      display: flex;
      align-items: center;
      gap: 0.65rem;
      border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    }
    .saas-brand-mark {
      width: 30px;
      height: 30px;
      border-radius: 8px;
      background: var(--unklab-gold);
      color: var(--unklab-navy);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 0.95rem;
      font-weight: 800;
      flex-shrink: 0;
    }
    .saas-nav-group {
      font-size: 0.68rem;
      font-weight: 700;
      letter-spacing: 0.08em;
      text-transform: uppercase;
      color: rgba(255, 255, 255, 0.38);
      padding: 1.15rem 1.25rem 0.45rem;
    }
    .saas-nav-link {
      display: flex;
      align-items: center;
      gap: 0.65rem;
      margin: 0.15rem 0.75rem;
      padding: 0.55rem 0.85rem;
      border-radius: 9px;
      color: rgba(255, 255, 255, 0.72);
      text-decoration: none;
      font-size: 0.86rem;
      font-weight: 500;
      transition: all 0.15s ease;
    }
    .saas-nav-link:hover {
      background: rgba(255, 255, 255, 0.07);
      color: #FFFFFF;
    }
    .saas-nav-link.active {
      background: rgba(255, 255, 255, 0.12);
      color: #FFFFFF;
      font-weight: 600;
      box-shadow: inset 3px 0 0 var(--unklab-gold);
    }
    .saas-nav-link i {
      font-size: 1rem;
      opacity: 0.85;
    }
    .saas-sidebar-footer {
      margin-top: auto;
      padding: 0.9rem 1rem;
      border-top: 1px solid rgba(255, 255, 255, 0.07);
      background: rgba(0, 0, 0, 0.14);
    }
    .saas-main {
      flex: 1;
      margin-left: var(--sidebar-width);
      display: flex;
      flex-direction: column;
      min-width: 0;
      background: var(--unklab-surface);
    }
    .saas-topbar {
      height: 64px;
      background: rgba(255, 255, 255, 0.86);
      backdrop-filter: blur(12px);
      border-bottom: 1px solid var(--unklab-border);
      padding: 0 1.5rem;
      display: flex;
      align-items: center;
      justify-content: space-between;
      position: sticky;
      top: 0;
      z-index: 1020;
    }
    @media (max-width: 991.98px) {
      .saas-sidebar {
        display: none;
      }
      .saas-main {
        margin-left: 0;
      }
    }

    /* Buttons */
    .btn-unklab-gold {
      background-color: var(--unklab-gold);
      color: var(--unklab-navy);
      font-weight: 600;
      border: 1px solid rgba(10, 34, 64, 0.08);
      box-shadow: var(--saas-shadow-xs);
      transition: all 0.16s ease;
    }
    .btn-unklab-gold:hover,
    .btn-unklab-gold:focus {
      background-color: var(--unklab-gold-hover);
      color: var(--unklab-navy);
      transform: translateY(-1px);
    }
    .btn-unklab-navy {
      background-color: var(--unklab-navy);
      color: #FFFFFF;
      font-weight: 600;
      border: 1px solid var(--unklab-navy);
      transition: all 0.16s ease;
    }
    .btn-unklab-navy:hover {
      background-color: var(--unklab-royal);
      border-color: var(--unklab-royal);
      color: #FFFFFF;
    }
    .btn-ghost-light {
      color: rgba(255, 255, 255, 0.9);
      border: 1px solid rgba(255, 255, 255, 0.2);
      background: rgba(255, 255, 255, 0.05);
      font-weight: 500;
      font-size: 0.85rem;
      transition: all 0.16s ease;
    }
    .btn-ghost-light:hover {
      background: rgba(255, 255, 255, 0.14);
      border-color: rgba(255, 255, 255, 0.35);
      color: #FFFFFF;
    }

    /* Modern SaaS Cards & Tables */
    .card-unklab {
      background: var(--unklab-card-bg);
      border: 1px solid var(--unklab-border);
      border-radius: 16px;
      box-shadow: var(--saas-shadow-xs);
    }
    .table > :not(caption) > * > * {
      padding: 0.75rem 0.85rem;
      border-bottom-color: var(--unklab-border-subtle);
    }
    .form-control, .form-select {
      border-color: var(--unklab-border);
      font-size: 0.9rem;
    }
    .form-control:focus, .form-select:focus {
      border-color: var(--unklab-royal);
      box-shadow: 0 0 0 3px rgba(22, 58, 108, 0.12);
    }

    /* Status Badges */
    .badge-approved {
      background-color: #ECFDF5;
      color: #065F46;
      border: 1px solid #A7F3D0;
      font-weight: 600;
    }
    .badge-review {
      background-color: #FFFBEB;
      color: #92400E;
      border: 1px solid #FDE68A;
      font-weight: 600;
    }
    .badge-corrected {
      background-color: #EFF6FF;
      color: #1E40AF;
      border: 1px solid #BFDBFE;
      font-weight: 600;
    }

    /* Macro Pills */
    .badge-macro-carb {
      background-color: #F8FAFC;
      color: var(--unklab-navy);
      border: 1px solid #E2E8F0;
    }
    .badge-macro-protein {
      background-color: #F0FDFA;
      color: #0F766E;
      border: 1px solid #CCFBF1;
    }
    .badge-macro-fat {
      background-color: #FFFBEB;
      color: #B45309;
      border: 1px solid #FDE68A;
    }

    /* Minimalist Camera Viewport */
    .camera-container {
      position: relative;
      background: #0B1324;
      border-radius: 14px;
      overflow: hidden;
      border: 1px solid var(--unklab-border);
      aspect-ratio: 4/3;
      max-height: 400px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .camera-container video,
    .camera-container canvas,
    .camera-container img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
    .scan-reticle {
      position: absolute;
      inset: 14%;
      border: 1.5px solid rgba(242, 169, 0, 0.55);
      border-radius: 12px;
      pointer-events: none;
    }
    .scan-laser {
      position: absolute;
      left: 14%;
      right: 14%;
      height: 2px;
      background: linear-gradient(90deg, transparent, var(--unklab-gold), transparent);
      animation: laserScan 1.9s infinite ease-in-out;
      display: none;
    }
    @keyframes laserScan {
      0% { top: 16%; opacity: 0.35; }
      50% { top: 84%; opacity: 1; }
      100% { top: 16%; opacity: 0.35; }
    }
    @media (max-width: 576px) {
      .camera-container {
        aspect-ratio: 1/1;
      }
    }
  </style>
</head>
<body>

<?php if ($isSaaSWorkspace): ?>
<!-- ===================================================================== -->
<!-- MODERN SAAS WORKSPACE LAYOUT (ADMIN & PETUGAS)                        -->
<!-- ===================================================================== -->
<div class="saas-shell">

  <!-- DESKTOP SIDEBAR -->
  <aside class="saas-sidebar">
    <div class="saas-sidebar-brand">
      <span class="saas-brand-mark"><i class="bi bi-cup-hot-fill"></i></span>
      <div class="lh-sm">
        <div class="fw-bold text-white" style="font-size: 0.92rem;">Nutrition UNKLAB</div>
        <div class="text-white-50" style="font-size: 0.68rem;">
          <?php echo ($currentRole === 'admin') ? 'Admin Workspace' : 'Staff Portal'; ?>
        </div>
      </div>
    </div>

    <div class="flex-grow-1 overflow-auto py-2">
      <?php if ($currentRole === 'admin'): ?>
        <div class="saas-nav-group">Control Center</div>
        <a href="<?php echo APP_PATH; ?>/admin/" class="saas-nav-link <?php echo ($activeMenu === 'admin_dashboard') ? 'active' : ''; ?>">
          <i class="bi bi-grid-1x2"></i> <span>Overview</span>
        </a>
        <a href="<?php echo APP_PATH; ?>/admin/queue/" class="saas-nav-link <?php echo ($activeMenu === 'admin_queue') ? 'active' : ''; ?>">
          <i class="bi bi-sliders"></i> <span>Antrean HITL (&lt;70%)</span>
        </a>

        <div class="saas-nav-group">Data &amp; Akses</div>
        <a href="<?php echo APP_PATH; ?>/admin/pangan/" class="saas-nav-link <?php echo ($activeMenu === 'admin_pangan') ? 'active' : ''; ?>">
          <i class="bi bi-database"></i> <span>Master Pangan TKPI</span>
        </a>
        <a href="<?php echo APP_PATH; ?>/admin/staff/" class="saas-nav-link <?php echo ($activeMenu === 'admin_staff') ? 'active' : ''; ?>">
          <i class="bi bi-people"></i> <span>Akun Petugas</span>
        </a>
      <?php endif; ?>

      <div class="saas-nav-group">Operasional Kantin</div>
      <a href="<?php echo APP_PATH; ?>/petugas/" class="saas-nav-link <?php echo ($activeMenu === 'petugas_dashboard') ? 'active' : ''; ?>">
        <i class="bi bi-journal-text"></i> <span>Daftar Menu Harian</span>
      </a>
      <a href="<?php echo APP_PATH; ?>/petugas/tambah_menu/" class="saas-nav-link <?php echo ($activeMenu === 'petugas_menu') ? 'active' : ''; ?>">
        <i class="bi bi-plus-square"></i> <span>Manual Menu Builder</span>
      </a>
      <a href="<?php echo APP_PATH; ?>/petugas/checker/" class="saas-nav-link <?php echo ($activeMenu === 'petugas_checker') ? 'active' : ''; ?>">
        <i class="bi bi-camera"></i> <span>Nutrition Checker</span>
      </a>

      <div class="saas-nav-group">Publik</div>
      <a href="<?php echo APP_PATH; ?>/" class="saas-nav-link">
        <i class="bi bi-box-arrow-up-right"></i> <span>Halaman Publik</span>
      </a>
    </div>

    <div class="saas-sidebar-footer">
      <div class="d-flex align-items-center justify-content-between gap-2">
        <div class="text-truncate">
          <div class="fw-semibold text-white small text-truncate"><?php echo htmlspecialchars($currentName); ?></div>
          <div class="text-white-50 text-uppercase" style="font-size: 0.66rem; letter-spacing: 0.05em;">
            <?php echo htmlspecialchars($currentRole); ?>
          </div>
        </div>
        <a href="<?php echo APP_PATH; ?>/login/logout/" class="btn btn-sm btn-ghost-light rounded-2 px-2 py-1" title="Keluar">
          <i class="bi bi-box-arrow-right"></i>
        </a>
      </div>
    </div>
  </aside>

  <!-- MOBILE OFFCANVAS SIDEBAR -->
  <div class="offcanvas offcanvas-start text-white" tabindex="-1" id="mobileSidebar" style="background: var(--unklab-navy); width: 260px;">
    <div class="offcanvas-header border-bottom border-white border-opacity-10">
      <div class="d-flex align-items-center gap-2">
        <span class="saas-brand-mark"><i class="bi bi-cup-hot-fill"></i></span>
        <span class="fw-bold">Nutrition UNKLAB</span>
      </div>
      <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"></button>
    </div>
    <div class="offcanvas-body p-0 py-2">
      <?php if ($currentRole === 'admin'): ?>
        <div class="saas-nav-group">Control Center</div>
        <a href="<?php echo APP_PATH; ?>/admin/" class="saas-nav-link <?php echo ($activeMenu === 'admin_dashboard') ? 'active' : ''; ?>"><i class="bi bi-grid-1x2"></i> Overview</a>
        <a href="<?php echo APP_PATH; ?>/admin/queue/" class="saas-nav-link <?php echo ($activeMenu === 'admin_queue') ? 'active' : ''; ?>"><i class="bi bi-sliders"></i> Antrean HITL (&lt;70%)</a>
        <a href="<?php echo APP_PATH; ?>/admin/pangan/" class="saas-nav-link <?php echo ($activeMenu === 'admin_pangan') ? 'active' : ''; ?>"><i class="bi bi-database"></i> Master Pangan TKPI</a>
        <a href="<?php echo APP_PATH; ?>/admin/staff/" class="saas-nav-link <?php echo ($activeMenu === 'admin_staff') ? 'active' : ''; ?>"><i class="bi bi-people"></i> Akun Petugas</a>
      <?php endif; ?>
      <div class="saas-nav-group">Operasional Kantin</div>
      <a href="<?php echo APP_PATH; ?>/petugas/" class="saas-nav-link"><i class="bi bi-journal-text"></i> Daftar Menu Harian</a>
      <a href="<?php echo APP_PATH; ?>/petugas/tambah_menu/" class="saas-nav-link"><i class="bi bi-plus-square"></i> Manual Menu Builder</a>
      <a href="<?php echo APP_PATH; ?>/petugas/checker/" class="saas-nav-link"><i class="bi bi-camera"></i> Nutrition Checker</a>
      <div class="saas-nav-group">Lainnya</div>
      <a href="<?php echo APP_PATH; ?>/" class="saas-nav-link"><i class="bi bi-box-arrow-up-right"></i> Halaman Publik</a>
      <a href="<?php echo APP_PATH; ?>/login/logout/" class="saas-nav-link text-danger"><i class="bi bi-box-arrow-right"></i> Keluar</a>
    </div>
  </div>

  <!-- WORKSPACE MAIN COLUMN -->
  <div class="saas-main">
    <header class="saas-topbar">
      <div class="d-flex align-items-center gap-3">
        <button class="btn btn-sm btn-outline-secondary d-lg-none rounded-2" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileSidebar">
          <i class="bi bi-list fs-5"></i>
        </button>
        <div class="small text-muted fw-medium d-none d-sm-block">
          UNKLAB Nutrition AI <span class="mx-2 text-black-50">/</span>
          <span class="text-dark fw-semibold"><?php echo htmlspecialchars(explode(' - ', $data['title'] ?? 'Workspace')[0]); ?></span>
        </div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <a href="<?php echo APP_PATH; ?>/" class="btn btn-sm btn-light border rounded-pill px-3 text-muted" style="font-size: 0.8rem;">
          <i class="bi bi-globe me-1"></i> Lihat Publik
        </a>
      </div>
    </header>

    <main class="flex-grow-1 p-2 p-md-3">

<?php else: ?>

<?php if (!$hideDefaultNav): ?>
<!-- PUBLIC MINIMALIST TOPBAR -->
<nav class="navbar navbar-expand-lg navbar-dark navbar-unklab sticky-top py-2">
  <div class="container" style="max-width: 1000px;">
    <a class="navbar-brand d-flex align-items-center gap-2 fw-semibold text-white" style="font-size: 0.96rem;" href="<?php echo APP_PATH; ?>/">
      <span class="saas-brand-mark" style="width:26px;height:26px;font-size:0.8rem;"><i class="bi bi-cup-hot-fill"></i></span>
      <span>Nutrition UNKLAB</span>
    </a>

    <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#navApp">
      <i class="bi bi-list fs-3 text-white"></i>
    </button>

    <div class="collapse navbar-collapse" id="navApp">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0 ms-lg-4 gap-1">
        <li class="nav-item">
          <a class="nav-link <?php echo ($activeMenu === 'landing') ? 'active' : ''; ?>" href="<?php echo APP_PATH; ?>/">Beranda</a>
        </li>
        <li class="nav-item">
          <a class="nav-link <?php echo ($activeMenu === 'menu') ? 'active' : ''; ?>" href="<?php echo APP_PATH; ?>/menu/">Menu Harian</a>
        </li>
      </ul>

      <div class="d-flex align-items-center gap-2 mt-2 mt-lg-0">
        <?php if (!empty($_SESSION['user-id'])): ?>
          <a href="<?php echo APP_PATH; ?>/<?php echo ($currentRole === 'admin') ? 'admin' : 'petugas'; ?>/" class="btn btn-ghost-light btn-sm rounded-3 px-3 py-1">Dashboard</a>
          <a href="<?php echo APP_PATH; ?>/login/logout/" class="btn btn-ghost-light btn-sm rounded-3 px-3 py-1 text-white-50">Keluar</a>
        <?php else: ?>
          <a href="<?php echo APP_PATH; ?>/login/" class="btn btn-ghost-light btn-sm rounded-3 px-3 py-1">Masuk</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
<?php endif; ?>

<main class="flex-grow-1">
<?php endif; ?>
