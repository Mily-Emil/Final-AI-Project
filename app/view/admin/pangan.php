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

  <!-- HEADER -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
    <div>
      <span class="badge px-3 py-1 rounded-pill mb-1" style="background: var(--unklab-navy); color: var(--unklab-gold); font-weight: 700;">
        <i class="bi bi-journal-bookmark me-1"></i> MASTER DATA GIZI
      </span>
      <h2 class="fw-bold mb-1" style="color: var(--unklab-navy); letter-spacing: -0.5px;">
        Tabel Komposisi Pangan Indonesia (TKPI)
      </h2>
      <p class="text-muted small mb-0">
        Basis data referensi nilai gizi standar kampus mencakup masakan nusantara dan kuliner lokal khas Sulawesi Utara.
      </p>
    </div>

    <div class="d-flex align-items-center gap-2">
      <button type="button" class="btn btn-unklab-gold rounded-pill px-3 py-2 fw-bold" data-bs-toggle="modal" data-bs-target="#modalTambahPangan">
        <i class="bi bi-plus-circle me-1"></i> Tambah Referensi Pangan
      </button>
      <a href="<?php echo APP_PATH; ?>/admin/" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-2">
        <i class="bi bi-arrow-left me-1"></i> Dashboard
      </a>
    </div>
  </div>

  <!-- FILTER & PENCARIAN -->
  <div class="card card-unklab p-3 mb-4">
    <form action="<?php echo APP_PATH; ?>/admin/pangan/" method="GET" class="row g-2 align-items-center">
      <div class="col-md-6">
        <div class="input-group input-group-sm">
          <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
          <input type="text" name="q" class="form-control border-start-0" placeholder="Cari nama makanan atau kata kunci..." value="<?php echo htmlspecialchars($data['search'] ?? ''); ?>">
        </div>
      </div>
      <div class="col-md-4">
        <select name="cat" class="form-select form-select-sm" onchange="this.form.submit()">
          <option value="">-- Semua Kategori --</option>
          <?php foreach ($data['categories'] as $cat): ?>
            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo (($data['selected_category'] ?? '') === $cat) ? 'selected' : ''; ?>>
              <?php echo htmlspecialchars($cat); ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-2 d-grid">
        <button type="submit" class="btn btn-sm btn-dark rounded-pill">Cari</button>
      </div>
    </form>
  </div>

  <!-- TABEL REFERENSI PANGAN -->
  <div class="card card-unklab p-3 p-md-4">
    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
      <h5 class="fw-bold text-dark mb-0">Daftar Komposisi Gizi Bahan & Hidangan</h5>
      <span class="badge bg-light text-dark border">Total: <?php echo count($data['references']); ?> Data</span>
    </div>

    <div class="table-responsive">
      <table class="table table-hover align-middle mb-0 small">
        <thead class="table-light text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.5px;">
          <tr>
            <th>Nama Pangan</th>
            <th>Kategori</th>
            <th class="text-center">Takaran</th>
            <th class="text-center">Kalori</th>
            <th class="text-center">Karbo</th>
            <th class="text-center">Protein</th>
            <th class="text-center">Lemak</th>
            <th class="text-center">Serat</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($data['references'])): ?>
            <?php foreach ($data['references'] as $ref): ?>
              <tr>
                <td>
                  <div class="fw-bold text-dark"><?php echo htmlspecialchars($ref['food_name']); ?></div>
                  <?php if (!empty($ref['notes'])): ?>
                    <small class="text-muted d-block" style="font-size: 0.72rem;"><?php echo htmlspecialchars($ref['notes']); ?></small>
                  <?php endif; ?>
                </td>
                <td>
                  <?php if ($ref['category'] === 'Khas Sulawesi Utara'): ?>
                    <span class="badge rounded-pill bg-warning-subtle text-dark border border-warning-subtle">
                      <i class="bi bi-star-fill text-warning me-1"></i><?php echo htmlspecialchars($ref['category']); ?>
                    </span>
                  <?php else: ?>
                    <span class="badge rounded-pill bg-light text-dark border">
                      <?php echo htmlspecialchars($ref['category']); ?>
                    </span>
                  <?php endif; ?>
                </td>
                <td class="text-center"><?php echo round($ref['serving_size_g']); ?>g</td>
                <td class="text-center fw-bold text-dark"><?php echo round($ref['calories']); ?> kkal</td>
                <td class="text-center text-primary fw-semibold"><?php echo $ref['carbohydrate_g']; ?>g</td>
                <td class="text-center text-success fw-semibold"><?php echo $ref['protein_g']; ?>g</td>
                <td class="text-center text-warning fw-semibold"><?php echo $ref['fat_g']; ?>g</td>
                <td class="text-center text-secondary"><?php echo $ref['fiber_g']; ?>g</td>
                <td class="text-end">
                  <a href="<?php echo APP_PATH; ?>/admin/hapus_pangan/<?php echo $ref['id']; ?>/" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill" onclick="return confirm('Hapus data referensi ini?');" title="Hapus">
                    <i class="bi bi-trash"></i>
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="9" class="text-center text-muted p-4">Tidak ada data referensi pangan yang sesuai dengan filter.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

</div>

<!-- MODAL TAMBAH REFERENSI PANGAN -->
<div class="modal fade" id="modalTambahPangan" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content rounded-4 border-0 shadow">
      
      <div class="modal-header bg-dark text-white rounded-top-4">
        <h5 class="modal-title fw-bold" style="color: var(--unklab-gold);"><i class="bi bi-plus-circle me-1"></i> Tambah Referensi Gizi</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>

      <form action="<?php echo APP_PATH; ?>/admin/tambah_pangan/" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token'] ?? ''); ?>">

        <div class="modal-body p-4">
          
          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Nama Pangan / Masakan <span class="text-danger">*</span></label>
            <input type="text" name="food_name" class="form-control rounded-3" placeholder="Contoh: Ayam Woku Belanga" required>
          </div>

          <div class="row g-2 mb-3">
            <div class="col-7">
              <label class="form-label small fw-bold text-dark">Kategori</label>
              <select name="category" class="form-select rounded-3">
                <option value="Makanan Pokok">Makanan Pokok</option>
                <option value="Lauk Hewani">Lauk Hewani</option>
                <option value="Lauk Nabati">Lauk Nabati</option>
                <option value="Sayuran">Sayuran</option>
                <option value="Buah">Buah</option>
                <option value="Khas Sulawesi Utara" selected>Khas Sulawesi Utara</option>
              </select>
            </div>
            <div class="col-5">
              <label class="form-label small fw-bold text-dark">Porsi Standar (g)</label>
              <input type="number" step="0.1" name="serving_size_g" class="form-control rounded-3" value="100">
            </div>
          </div>

          <div class="row g-2 mb-3 text-center">
            <div class="col-3">
              <label class="small text-muted fw-bold">Karbo (g)</label>
              <input type="number" step="0.1" name="carbohydrate_g" id="modalCarb" class="form-control form-control-sm text-center" value="10">
            </div>
            <div class="col-3">
              <label class="small text-muted fw-bold">Protein (g)</label>
              <input type="number" step="0.1" name="protein_g" id="modalPro" class="form-control form-control-sm text-center" value="20">
            </div>
            <div class="col-3">
              <label class="small text-muted fw-bold">Lemak (g)</label>
              <input type="number" step="0.1" name="fat_g" id="modalFat" class="form-control form-control-sm text-center" value="5">
            </div>
            <div class="col-3">
              <label class="small text-muted fw-bold">Serat (g)</label>
              <input type="number" step="0.1" name="fiber_g" class="form-control form-control-sm text-center" value="1.5">
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Total Kalori (kkal)</label>
            <input type="number" step="0.1" name="calories" id="modalCal" class="form-control rounded-3 fw-bold text-primary" value="165" readonly>
            <small class="text-muted" style="font-size: 0.72rem;">Dihitung otomatis Atwater: C×4 + P×4 + F×9</small>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Catatan / Deskripsi</label>
            <textarea name="notes" rows="2" class="form-control rounded-3" placeholder="Informasi gizi atau cara pengolahan..."></textarea>
          </div>

        </div>

        <div class="modal-footer p-3 bg-light">
          <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
          <button type="submit" class="btn btn-sm btn-unklab-gold rounded-pill px-4 fw-bold">Simpan Referensi</button>
        </div>

      </form>

    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const mCarb = document.getElementById('modalCarb');
  const mPro = document.getElementById('modalPro');
  const mFat = document.getElementById('modalFat');
  const mCal = document.getElementById('modalCal');

  function calcModalAtwater() {
    const c = Math.max(0, parseFloat(mCarb.value) || 0);
    const p = Math.max(0, parseFloat(mPro.value) || 0);
    const f = Math.max(0, parseFloat(mFat.value) || 0);
    mCal.value = Math.round((c * 4.0) + (p * 4.0) + (f * 9.0));
  }

  [mCarb, mPro, mFat].forEach(inp => inp.addEventListener('input', calcModalAtwater));
  calcModalAtwater();
});
</script>

<?php require_once dirname(__DIR__) . '/template/footer.php'; ?>
