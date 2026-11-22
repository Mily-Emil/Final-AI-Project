<?php require_once dirname(__DIR__) . '/template/header.php'; ?>

<div class="container py-4">

  <!-- BANNER FLASH MESSAGE -->
  <?php if (!empty($data['flash_message'])): ?>
    <div class="alert alert-<?php echo htmlspecialchars($data['flash_message']['type'] ?? 'info'); ?> alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-info-circle-fill fs-5"></i>
        <div>
          <strong><?php echo htmlspecialchars($data['flash_message']['title'] ?? 'Pemberitahuan'); ?>:</strong>
          <?php echo htmlspecialchars($data['flash_message']['text'] ?? ''); ?>
        </div>
      </div>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- HEADER -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
      <span class="badge px-3 py-1 rounded-pill mb-1" style="background: var(--unklab-navy); color: var(--unklab-gold); font-weight: 700;">
        <i class="bi bi-people me-1"></i> MANAJEMEN AKUN
      </span>
      <h2 class="fw-bold mb-1" style="color: var(--unklab-navy); letter-spacing: -0.5px;">
        Akun Petugas & Administrator
      </h2>
      <p class="text-muted small mb-0">
        Kelola kredensial login staf kantin / dining hall serta hak akses administrator sistem.
      </p>
    </div>

    <div class="d-flex align-items-center gap-2">
      <button type="button" class="btn btn-unklab-gold rounded-pill px-3 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahStaff">
        <i class="bi bi-person-plus me-1"></i> Tambah Akun Baru
      </button>
      <a href="<?php echo APP_PATH; ?>/admin/" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2">
        <i class="bi bi-arrow-left me-1"></i> Dashboard
      </a>
    </div>
  </div>

  <!-- TABEL STAF -->
  <div class="card card-unklab p-3 p-md-4">
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
      <h5 class="fw-bold text-dark mb-0">Daftar Pengguna Internal (RBAC)</h5>
      <span class="badge bg-light text-dark border">Total: <?php echo count($data['staff_list']); ?> Pengguna</span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
          <tr>
            <th>Nama Lengkap</th>
            <th>Username</th>
            <th>Email</th>
            <th class="text-center">Peran (Role)</th>
            <th class="text-center">Status</th>
            <th>Terakhir Login</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($data['staff_list'] as $u): ?>
            <tr>
              <td>
                <div class="fw-bold text-dark"><?php echo htmlspecialchars($u['nama_lengkap']); ?></div>
                <small class="text-muted" style="font-size: 0.7rem;">ID: #<?php echo $u['id']; ?></small>
              </td>
              <td><code><?php echo htmlspecialchars($u['username']); ?></code></td>
              <td class="text-muted"><?php echo htmlspecialchars($u['email']); ?></td>
              <td class="text-center">
                <?php if ($u['role'] === 'admin'): ?>
                  <span class="badge bg-danger-subtle text-danger border border-danger-subtle"><i class="bi bi-shield-fill me-1"></i>Administrator</span>
                <?php else: ?>
                  <span class="badge bg-primary-subtle text-primary border border-primary-subtle"><i class="bi bi-cup-hot me-1"></i>Petugas Kantin</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <span class="badge bg-success-subtle text-success">Aktif</span>
              </td>
              <td class="text-muted">
                <?php echo !empty($u['last_login']) ? date('d M Y, H:i', strtotime($u['last_login'])) : 'Belum pernah'; ?>
              </td>
              <td class="text-end">
                <?php if ($u['id'] != 1): ?>
                  <a href="<?php echo APP_PATH; ?>/admin/hapus_staff/<?php echo $u['id']; ?>/" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill" onclick="return confirm('Hapus akun pengguna ini?');">
                    <i class="bi bi-trash"></i>
                  </a>
                <?php else: ?>
                  <span class="text-muted small">Superadmin</span>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- MODAL TAMBAH STAF -->
<div class="modal fade" id="modalTambahStaff" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      
      <div class="modal-header bg-dark text-white rounded-top-4">
        <h5 class="modal-title fw-bold" style="color: var(--unklab-gold);"><i class="bi bi-person-plus me-1"></i> Tambah Akun Pengguna</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form action="<?php echo APP_PATH; ?>/admin/tambah_staff/" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token'] ?? ''); ?>">

        <div class="modal-body p-4">
          
          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Nama Lengkap <span class="text-danger">*</span></label>
            <input type="text" name="nama_lengkap" class="form-control rounded-3" placeholder="Contoh: Sarah Manoppo" required>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-6">
              <label class="form-label small fw-bold text-dark">Username <span class="text-danger">*</span></label>
              <input type="text" name="username" class="form-control rounded-3" placeholder="sarah_dining" required>
            </div>
            <div class="col-6">
              <label class="form-label small fw-bold text-dark">Peran (Role) <span class="text-danger">*</span></label>
              <select name="role" class="form-select rounded-3">
                <option value="petugas" selected>Petugas Kantin</option>
                <option value="admin">Administrator</option>
              </select>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Email Kampus <span class="text-danger">*</span></label>
            <input type="email" name="email" class="form-control rounded-3" placeholder="sarah@unklab.ac.id" required>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Password Awal <span class="text-danger">*</span></label>
            <input type="password" name="password" class="form-control rounded-3" value="petugas123" required>
            <small class="text-muted" style="font-size: 0.72rem;">Default: <code>petugas123</code> (dapat diubah nanti oleh pengguna)</small>
          </div>

        </div>

        <div class="modal-footer p-3 bg-light">
          <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-sm btn-unklab-gold rounded-pill px-4 fw-bold">Daftarkan Akun</button>
        </div>

      </form>

    </div>
  </div>
</div>

<?php require_once dirname(__DIR__) . '/template/footer.php'; ?>
