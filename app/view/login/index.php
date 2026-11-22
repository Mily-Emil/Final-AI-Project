<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?php echo htmlspecialchars($data['title'] ?? 'Masuk - Nutrition UNKLAB'); ?></title>

  <link rel="stylesheet" href="<?php echo APP_PATH; ?>/public/assets/vendor/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="<?php echo APP_PATH; ?>/public/assets/vendor/bootstrap-icons/bootstrap-icons.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

  <style>
    :root {
      --unklab-navy: #0A2240;
      --unklab-royal: #163A6C;
      --unklab-gold: #F2A900;
      --unklab-border: #E2E8F0;
    }
    body {
      background-color: #F8FAFC;
      background-image: radial-gradient(#CBD5E1 1px, transparent 1px);
      background-size: 24px 24px;
      color: #0F172A;
      font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      -webkit-font-smoothing: antialiased;
    }
    .saas-login-card {
      background: #FFFFFF;
      border: 1px solid var(--unklab-border);
      border-radius: 20px;
      box-shadow: 0 12px 32px -8px rgba(10, 34, 64, 0.08), 0 2px 6px rgba(10, 34, 64, 0.03);
      max-width: 400px;
      width: 100%;
    }
    .brand-icon {
      width: 40px;
      height: 40px;
      border-radius: 12px;
      background: var(--unklab-navy);
      color: var(--unklab-gold);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.15rem;
    }
    .form-control {
      border-color: var(--unklab-border);
      border-radius: 10px;
      padding: 0.6rem 0.85rem;
      font-size: 0.9rem;
    }
    .form-control:focus {
      border-color: var(--unklab-royal);
      box-shadow: 0 0 0 3px rgba(22, 58, 108, 0.12);
    }
    .btn-saas-primary {
      background: var(--unklab-navy);
      color: #FFFFFF;
      font-weight: 600;
      border: none;
      border-radius: 10px;
      padding: 0.65rem 1rem;
      font-size: 0.9rem;
      transition: background-color 0.15s ease;
    }
    .btn-saas-primary:hover {
      background: var(--unklab-royal);
      color: #FFFFFF;
    }
    .demo-chip {
      border: 1px solid var(--unklab-border);
      background: #F8FAFC;
      color: #334155;
      font-size: 0.78rem;
      font-weight: 600;
      border-radius: 8px;
      padding: 0.35rem 0.75rem;
      transition: all 0.15s ease;
    }
    .demo-chip:hover {
      background: #FFFFFF;
      border-color: var(--unklab-navy);
      color: var(--unklab-navy);
    }
  </style>
</head>
<body class="p-3">

<div class="saas-login-card p-4 p-md-5">

  <div class="mb-4">
    <div class="brand-icon mb-3">
      <i class="bi bi-cup-hot-fill"></i>
    </div>
    <h4 class="fw-bold mb-1" style="letter-spacing: -0.025em; color: var(--unklab-navy);">Masuk ke Nutrition UNKLAB</h4>
    <p class="text-muted small mb-0">Portal operasional Petugas Kantin &amp; Administrator HITL.</p>
  </div>

  <?php if (!empty($data['error'])): ?>
    <div class="alert alert-danger border-0 py-2 px-3 small rounded-3 mb-3" style="background:#FEF2F2;color:#991B1B;" role="alert">
      <i class="bi bi-exclamation-circle me-1"></i><?php echo htmlspecialchars($data['error']); ?>
    </div>
  <?php endif; ?>

  <form action="<?php echo APP_PATH; ?>/login/authenticate/" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token']); ?>">

    <div class="mb-3">
      <label for="username" class="form-label small fw-semibold text-dark">Username atau Email</label>
      <input type="text" class="form-control" id="username" name="username" placeholder="admin atau petugas" required autofocus>
    </div>

    <div class="mb-4">
      <label for="password" class="form-label small fw-semibold text-dark">Kata Sandi</label>
      <input type="password" class="form-control" id="password" name="password" placeholder="••••••••" required>
    </div>

    <button type="submit" class="btn btn-saas-primary w-100 mb-3">
      Masuk
    </button>
  </form>

  <div class="border-top pt-3 mt-2">
    <div class="text-muted mb-2" style="font-size: 0.74rem;">Isi cepat akun demo:</div>
    <div class="d-flex gap-2">
      <button type="button" class="demo-chip flex-fill" onclick="fillLogin('admin', 'admin123')">
        <i class="bi bi-shield me-1"></i>Admin
      </button>
      <button type="button" class="demo-chip flex-fill" onclick="fillLogin('petugas', 'petugas123')">
        <i class="bi bi-person-badge me-1"></i>Petugas
      </button>
    </div>
  </div>

  <div class="text-center mt-4">
    <a href="<?php echo APP_PATH; ?>/" class="small text-decoration-none text-muted">
      &larr; Kembali ke Beranda Publik
    </a>
  </div>

</div>

<script>
function fillLogin(u, p) {
  document.getElementById('username').value = u;
  document.getElementById('password').value = p;
}
const urlParams = new URLSearchParams(window.location.search);
const reqUser = urlParams.get('user');
if (reqUser === 'admin') fillLogin('admin', 'admin123');
else if (reqUser === 'petugas') fillLogin('petugas', 'petugas123');
</script>

</body>
</html>
