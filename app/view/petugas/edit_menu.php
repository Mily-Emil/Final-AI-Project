<?php require_once dirname(__DIR__) . '/template/header.php'; ?>

<div class="container py-4">

  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <span class="badge px-3 py-1 rounded-pill mb-1" style="background: var(--unklab-navy); color: var(--unklab-gold); font-weight: 700;">
        <i class="bi bi-pencil me-1"></i> EDIT MENU HARIAN
      </span>
      <h2 class="fw-bold mb-1" style="color: var(--unklab-navy);">
        Perbarui Data Menu
      </h2>
      <p class="text-muted small mb-0">Ubah takaran gizi atau status ketersediaan menu kampus.</p>
    </div>
    <a href="<?php echo APP_PATH; ?>/petugas/" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
      <i class="bi bi-arrow-left me-1"></i> Batal & Kembali
    </a>
  </div>

  <div class="row g-4 justify-content-center">
    <div class="col-lg-8">
      
      <div class="card card-unklab p-4">
        
        <form action="<?php echo APP_PATH; ?>/petugas/update_menu/" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token'] ?? ''); ?>">
          <input type="hidden" name="id" value="<?php echo $data['menu']['id']; ?>">

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Sesi Makan <span class="text-danger">*</span></label>
              <select name="meal_session" class="form-select rounded-3" required>
                <option value="sarapan" <?php echo ($data['menu']['meal_session'] === 'sarapan') ? 'selected' : ''; ?>>Sarapan Pagi</option>
                <option value="makan_siang" <?php echo ($data['menu']['meal_session'] === 'makan_siang') ? 'selected' : ''; ?>>Makan Siang</option>
                <option value="makan_malam" <?php echo ($data['menu']['meal_session'] === 'makan_malam') ? 'selected' : ''; ?>>Makan Malam</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Tanggal Menu <span class="text-danger">*</span></label>
              <input type="date" name="menu_date" class="form-control rounded-3" value="<?php echo htmlspecialchars($data['menu']['menu_date']); ?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Nama Menu Hidangan <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control rounded-3" value="<?php echo htmlspecialchars($data['menu']['name']); ?>" required>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Deskripsi Singkat</label>
            <textarea name="description" rows="2" class="form-control rounded-3"><?php echo htmlspecialchars($data['menu']['description'] ?? ''); ?></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Komposisi Bahan</label>
            <textarea name="ingredients" rows="2" class="form-control rounded-3"><?php echo htmlspecialchars($data['menu']['ingredients'] ?? ''); ?></textarea>
          </div>

          <!-- TAKARAN GIZI & MAKRO -->
          <div class="card p-3 mb-3 bg-light border">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <span class="small fw-bold text-dark">Nilai Gizi & Distribusi Makronutrien:</span>
              <button type="button" id="btnAutoCalc" class="btn btn-sm btn-outline-success rounded-pill px-3">
                <i class="bi bi-arrow-repeat me-1"></i> Hitung Ulang Atwater
              </button>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-sm-6 col-md-4">
                <label class="small text-muted fw-semibold">Berat Porsi (g)</label>
                <input type="number" step="0.1" name="portion_weight_g" class="form-control form-control-sm rounded-2" value="<?php echo $data['menu']['portion_weight_g']; ?>">
              </div>
              <div class="col-sm-6 col-md-4">
                <label class="small text-muted fw-semibold">Total Kalori (kkal)</label>
                <input type="number" step="0.1" name="calories" id="inCalories" class="form-control form-control-sm rounded-2 fw-bold text-primary" value="<?php echo round($data['menu']['calories']); ?>" readonly>
              </div>
              <div class="col-sm-6 col-md-4">
                <label class="small text-muted fw-semibold">Serat Pangan (g)</label>
                <input type="number" step="0.1" name="fiber_g" class="form-control form-control-sm rounded-2" value="<?php echo $data['menu']['fiber_g']; ?>">
              </div>
            </div>

            <div class="row g-2 text-center">
              <div class="col-4">
                <div class="p-2 bg-white rounded-2 border">
                  <label class="small text-primary fw-bold mb-1">Karbohidrat (g)</label>
                  <input type="number" step="0.1" name="carbohydrate_g" id="inCarb" class="form-control form-control-sm text-center fw-bold" value="<?php echo $data['menu']['carbohydrate_g']; ?>">
                  <span class="badge bg-primary-subtle text-primary mt-1" id="badgePctCarb" style="font-size: 0.72rem;"><?php echo $data['menu']['carbohydrate_pct']; ?>%</span>
                </div>
              </div>
              <div class="col-4">
                <div class="p-2 bg-white rounded-2 border">
                  <label class="small text-success fw-bold mb-1">Protein (g)</label>
                  <input type="number" step="0.1" name="protein_g" id="inProtein" class="form-control form-control-sm text-center fw-bold" value="<?php echo $data['menu']['protein_g']; ?>">
                  <span class="badge bg-success-subtle text-success mt-1" id="badgePctProtein" style="font-size: 0.72rem;"><?php echo $data['menu']['protein_pct']; ?>%</span>
                </div>
              </div>
              <div class="col-4">
                <div class="p-2 bg-white rounded-2 border">
                  <label class="small text-warning fw-bold mb-1">Lemak (g)</label>
                  <input type="number" step="0.1" name="fat_g" id="inFat" class="form-control form-control-sm text-center fw-bold" value="<?php echo $data['menu']['fat_g']; ?>">
                  <span class="badge bg-warning-subtle text-warning mt-1" id="badgePctFat" style="font-size: 0.72rem;"><?php echo $data['menu']['fat_pct']; ?>%</span>
                </div>
              </div>
            </div>
          </div>

          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Ganti Foto Hidangan</label>
              <input type="file" name="image" class="form-control rounded-3" accept="image/jpeg,image/png,image/webp">
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark d-block">Status Ketersediaan & Publikasi</label>
              <div class="d-flex align-items-center gap-4 mt-2">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="is_available" value="1" id="switchAvailable" <?php echo ($data['menu']['is_available'] == 1) ? 'checked' : ''; ?>>
                  <label class="form-check-label small fw-bold text-success" for="switchAvailable">Tersedia</label>
                </div>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="is_published" value="1" id="switchPublished" <?php echo ($data['menu']['is_published'] == 1) ? 'checked' : ''; ?>>
                  <label class="form-check-label small fw-bold text-primary" for="switchPublished">Publikasikan</label>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
            <a href="<?php echo APP_PATH; ?>/petugas/" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
            <button type="submit" class="btn btn-unklab-gold rounded-pill px-4 py-2 fw-bold">
              <i class="bi bi-save me-1"></i> Simpan Perubahan
            </button>
          </div>

        </form>

      </div>

    </div>
  </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const inCarb = document.getElementById('inCarb');
  const inProtein = document.getElementById('inProtein');
  const inFat = document.getElementById('inFat');
  const inCalories = document.getElementById('inCalories');
  const badgePctCarb = document.getElementById('badgePctCarb');
  const badgePctProtein = document.getElementById('badgePctProtein');
  const badgePctFat = document.getElementById('badgePctFat');
  const btnAutoCalc = document.getElementById('btnAutoCalc');

  function calculateAtwater() {
    const c = Math.max(0, parseFloat(inCarb.value) || 0);
    const p = Math.max(0, parseFloat(inProtein.value) || 0);
    const f = Math.max(0, parseFloat(inFat.value) || 0);

    const cKkal = c * 4.0;
    const pKkal = p * 4.0;
    const fKkal = f * 9.0;
    const totalMacroKkal = cKkal + pKkal + fKkal;

    inCalories.value = Math.round(totalMacroKkal);

    if (totalMacroKkal > 0) {
      const cPct = ((cKkal / totalMacroKkal) * 100).toFixed(1);
      const pPct = ((pKkal / totalMacroKkal) * 100).toFixed(1);
      const fPct = (100.0 - (parseFloat(cPct) + parseFloat(pPct))).toFixed(1);

      badgePctCarb.textContent = cPct + '%';
      badgePctProtein.textContent = pPct + '%';
      badgePctFat.textContent = fPct + '%';
    }
  }

  [inCarb, inProtein, inFat].forEach(inp => inp.addEventListener('input', calculateAtwater));
  btnAutoCalc.addEventListener('click', calculateAtwater);
});
</script>

<?php require_once dirname(__DIR__) . '/template/footer.php'; ?>
