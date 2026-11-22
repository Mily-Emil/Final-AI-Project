<?php require_once dirname(__DIR__) . '/template/header.php'; ?>

<div class="container py-4">

  <!-- BANNER NOTIFIKASI -->
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

  <!-- HEADER PORTAL PETUGAS -->
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
      <span class="badge px-3 py-1 rounded-pill mb-1" style="background: var(--unklab-navy); color: var(--unklab-gold); font-weight: 700;">
        <i class="bi bi-person-badge me-1"></i> PORTAL OPERASIONAL STAF
      </span>
      <h2 class="fw-bold mb-1" style="color: var(--unklab-navy); letter-spacing: -0.5px;">
        Dashboard Dining Hall & Kantin UNKLAB
      </h2>
      <p class="text-muted small mb-0">
        Kelola ketersediaan menu harian, buat menu baru dengan takaran nutrisi, dan lakukan uji mutu sampel makanan.
      </p>
    </div>

    <!-- QUICK ACTIONS -->
    <div class="d-flex align-items-center gap-2">
      <a href="<?php echo APP_PATH; ?>/petugas/checker/" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold shadow-sm">
        <i class="bi bi-camera me-1"></i> Staff Nutrition Checker
      </a>
      <a href="<?php echo APP_PATH; ?>/petugas/tambah_menu/" class="btn btn-unklab-gold rounded-pill px-3 py-2 fw-bold">
        <i class="bi bi-plus-circle me-1"></i> Input Menu Manual
      </a>
    </div>
  </div>

  <!-- DAFTAR MENU HARIAN KAMPUS -->
  <div class="card card-unklab p-3 p-md-4 mb-4">
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom flex-wrap gap-2">
      <div>
        <h5 class="fw-bold text-dark mb-0">Daftar Menu Harian Kampus</h5>
        <small class="text-muted">Klik tombol status ketersediaan untuk langsung mensinkronkan status ke publik.</small>
      </div>
      <span class="badge bg-light text-dark border">Total: <?php echo count($data['menus']); ?> Menu</span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0">
        <thead class="table-light small text-uppercase" style="font-size: 0.75rem; letter-spacing: 0.5px;">
          <tr>
            <th>Menu & Tanggal</th>
            <th>Sesi Makan</th>
            <th>Kalori & Makro</th>
            <th class="text-center">Ketersediaan</th>
            <th class="text-center">Publikasi</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody class="small">
          <?php if (!empty($data['menus'])): ?>
            <?php foreach ($data['menus'] as $menu): ?>
              <tr>
                <td>
                  <div class="fw-bold text-dark"><?php echo htmlspecialchars($menu['name']); ?></div>
                  <div class="text-muted" style="font-size: 0.75rem;">
                    <i class="bi bi-calendar3 me-1"></i><?php echo date('d M Y', strtotime($menu['menu_date'])); ?>
                    <?php if (!empty($menu['creator_name'])): ?>
                      • Oleh: <?php echo htmlspecialchars($menu['creator_name']); ?>
                    <?php endif; ?>
                  </div>
                </td>
                <td>
                  <?php if ($menu['meal_session'] === 'sarapan'): ?>
                    <span class="badge bg-warning bg-opacity-25 text-dark"><i class="bi bi-cup-hot me-1"></i>Sarapan</span>
                  <?php elseif ($menu['meal_session'] === 'makan_siang'): ?>
                    <span class="badge bg-primary bg-opacity-25 text-primary"><i class="bi bi-sun me-1"></i>Makan Siang</span>
                  <?php else: ?>
                    <span class="badge bg-purple bg-opacity-25 text-purple" style="background: #F3E8FF; color: #7C3AED;"><i class="bi bi-moon me-1"></i>Makan Malam</span>
                  <?php endif; ?>
                </td>
                <td>
                  <span class="fw-bold text-dark"><?php echo round($menu['calories']); ?> kkal</span>
                  <div class="text-muted" style="font-size: 0.72rem;">
                    K: <?php echo $menu['carbohydrate_pct']; ?>% • P: <?php echo $menu['protein_pct']; ?>% • L: <?php echo $menu['fat_pct']; ?>%
                  </div>
                </td>
                <td class="text-center">
                  <a href="<?php echo APP_PATH; ?>/petugas/toggle_availability/<?php echo $menu['id']; ?>/" class="btn btn-sm py-1 px-2 rounded-pill <?php echo ($menu['is_available'] == 1) ? 'btn-success' : 'btn-outline-danger'; ?>" title="Klik untuk mengubah ketersediaan">
                    <?php if ($menu['is_available'] == 1): ?>
                      <i class="bi bi-check-circle-fill me-1"></i> Tersedia
                    <?php else: ?>
                      <i class="bi bi-x-circle me-1"></i> Habis
                    <?php endif; ?>
                  </a>
                </td>
                <td class="text-center">
                  <a href="<?php echo APP_PATH; ?>/petugas/toggle_published/<?php echo $menu['id']; ?>/" class="btn btn-sm py-1 px-2 rounded-pill <?php echo ($menu['is_published'] == 1) ? 'btn-outline-primary' : 'btn-outline-secondary'; ?>">
                    <?php echo ($menu['is_published'] == 1) ? '<i class="bi bi-eye-fill me-1"></i> Terbit' : '<i class="bi bi-eye-slash me-1"></i> Draf'; ?>
                  </a>
                </td>
                <td class="text-end">
                  <div class="btn-group btn-group-sm">
                    <a href="<?php echo APP_PATH; ?>/petugas/edit_menu/<?php echo $menu['id']; ?>/" class="btn btn-outline-secondary" title="Edit Menu">
                      <i class="bi bi-pencil"></i>
                    </a>
                    <a href="<?php echo APP_PATH; ?>/petugas/hapus_menu/<?php echo $menu['id']; ?>/" class="btn btn-outline-danger" title="Hapus Menu" onclick="return confirm('Yakin ingin menghapus menu ini?');">
                      <i class="bi bi-trash"></i>
                    </a>
                  </div>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted p-4">
                Belum ada data menu harian. Klik tombol <strong>Input Menu Manual</strong> di atas untuk menambahkan.
              </td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<?php require_once dirname(__DIR__) . '/template/footer.php'; ?>
