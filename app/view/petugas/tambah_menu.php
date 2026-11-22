<?php require_once dirname(__DIR__) . '/template/header.php'; ?>

<div class="container py-4">

  <!-- HEADER -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <span class="badge px-3 py-1 rounded-pill mb-1" style="background: var(--unklab-navy); color: var(--unklab-gold); font-weight: 700;">
        <i class="bi bi-pencil-square me-1"></i> MANUAL MENU BUILDER
      </span>
      <h2 class="fw-bold mb-1" style="color: var(--unklab-navy);">
        Input Menu Harian Kampus
      </h2>
      <p class="text-muted small mb-0">
        Formulir terstruktur untuk menginput menu dining hall / kantin dengan kalkulator gizi otomatis berbasis faktor Atwater.
      </p>
    </div>
    <a href="<?php echo APP_PATH; ?>/petugas/" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
      <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
    </a>
  </div>

  <div class="row g-4 justify-content-center">
    <div class="col-lg-8">
      
      <div class="card card-unklab p-4">
        
        <form action="<?php echo APP_PATH; ?>/petugas/simpan_menu/" method="POST" enctype="multipart/form-data">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token'] ?? ''); ?>">

          <!-- TEMPLATE REKOMENDASI DARI TKPI / MASAKAN LOKAL -->
          <div class="p-3 mb-4 rounded-3 bg-light border">
            <label class="small fw-bold text-dark d-block mb-1">
              <i class="bi bi-magic text-warning me-1"></i> Bantuan Hitung Otomatis dari Referensi Pangan (TKPI):
            </label>
            <div class="d-flex gap-2">
              <select id="tkpiSelect" class="form-select form-select-sm rounded-pill">
                <option value="">-- Pilih Referensi Makanan Kampus / Khas Sulut --</option>
                <?php foreach ($data['food_references'] as $ref): ?>
                  <option value="<?php echo $ref['id']; ?>" 
                          data-name="<?php echo htmlspecialchars($ref['food_name']); ?>"
                          data-cal="<?php echo $ref['calories']; ?>"
                          data-serving="<?php echo $ref['serving_size_g']; ?>"
                          data-carb="<?php echo $ref['carbohydrate_g']; ?>"
                          data-pro="<?php echo $ref['protein_g']; ?>"
                          data-fat="<?php echo $ref['fat_g']; ?>"
                          data-fib="<?php echo $ref['fiber_g']; ?>"
                          data-notes="<?php echo htmlspecialchars($ref['notes'] ?? ''); ?>">
                    <?php echo htmlspecialchars($ref['food_name']); ?> (<?php echo $ref['category']; ?> - <?php echo round($ref['calories']); ?> kkal)
                  </option>
                <?php endforeach; ?>
              </select>
              <button type="button" id="btnApplyTkpi" class="btn btn-sm btn-outline-primary rounded-pill px-3 text-nowrap">
                Gunakan Data
              </button>
            </div>
          </div>

          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Sesi Makan <span class="text-danger">*</span></label>
              <select name="meal_session" class="form-select rounded-3" required>
                <option value="sarapan">Sarapan Pagi (Breakfast)</option>
                <option value="makan_siang" selected>Makan Siang (Lunch)</option>
                <option value="makan_malam">Makan Malam (Dinner)</option>
              </select>
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Tanggal Menu <span class="text-danger">*</span></label>
              <input type="date" name="menu_date" class="form-control rounded-3" value="<?php echo date('Y-m-d'); ?>" required>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Nama Menu Hidangan <span class="text-danger">*</span></label>
            <input type="text" name="name" id="menuName" class="form-control rounded-3" placeholder="Contoh: Paket Nasi + Cakalang Fufu + Sayur Kangkung" required>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Deskripsi Singkat</label>
            <textarea name="description" id="menuDesc" rows="2" class="form-control rounded-3" placeholder="Deskripsi sajian hidangan..."></textarea>
          </div>

          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Komposisi Rincian Bahan Makanan</label>
            <textarea name="ingredients" id="menuIngredients" rows="2" class="form-control rounded-3" placeholder="Contoh: Beras putih, ikan cakalang asap, cabai rica, kangkung segar, bawang putih"></textarea>
          </div>

          <!-- TAKARAN GIZI & MAKRONUTRIEN (FAKTOR ATWATER) -->
          <div class="card p-3 mb-3 bg-light border">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <span class="small fw-bold text-dark">
                <i class="bi bi-calculator me-1 text-primary"></i> Rincian Nilai Gizi & Makronutrien:
              </span>
              <button type="button" id="btnAutoCalc" class="btn btn-sm btn-outline-success rounded-pill px-3">
                <i class="bi bi-arrow-repeat me-1"></i> Hitung Kalori Atwater
              </button>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-sm-6 col-md-4">
                <label class="small text-muted fw-semibold">Berat Porsi (gram)</label>
                <input type="number" step="0.1" name="portion_weight_g" id="inWeight" class="form-control form-control-sm rounded-2" value="350">
              </div>
              <div class="col-sm-6 col-md-4">
                <label class="small text-muted fw-semibold">Total Kalori (kkal)</label>
                <input type="number" step="0.1" name="calories" id="inCalories" class="form-control form-control-sm rounded-2 fw-bold text-primary" value="450" readonly>
                <small class="text-muted" style="font-size: 0.7rem;">Dihitung otomatis: C×4 + P×4 + F×9</small>
              </div>
              <div class="col-sm-6 col-md-4">
                <label class="small text-muted fw-semibold">Serat Pangan (g)</label>
                <input type="number" step="0.1" name="fiber_g" id="inFiber" class="form-control form-control-sm rounded-2" value="3.5">
              </div>
            </div>

            <div class="row g-2 text-center">
              <div class="col-4">
                <div class="p-2 bg-white rounded-2 border">
                  <label class="small text-primary fw-bold mb-1">Karbohidrat (g)</label>
                  <input type="number" step="0.1" name="carbohydrate_g" id="inCarb" class="form-control form-control-sm text-center fw-bold" value="55.0">
                  <span class="badge bg-primary-subtle text-primary mt-1" id="badgePctCarb" style="font-size: 0.72rem;">50.0%</span>
                </div>
              </div>
              <div class="col-4">
                <div class="p-2 bg-white rounded-2 border">
                  <label class="small text-success fw-bold mb-1">Protein (g)</label>
                  <input type="number" step="0.1" name="protein_g" id="inProtein" class="form-control form-control-sm text-center fw-bold" value="28.0">
                  <span class="badge bg-success-subtle text-success mt-1" id="badgePctProtein" style="font-size: 0.72rem;">25.5%</span>
                </div>
              </div>
              <div class="col-4">
                <div class="p-2 bg-white rounded-2 border">
                  <label class="small text-warning fw-bold mb-1">Lemak (g)</label>
                  <input type="number" step="0.1" name="fat_g" id="inFat" class="form-control form-control-sm text-center fw-bold" value="12.0">
                  <span class="badge bg-warning-subtle text-warning mt-1" id="badgePctFat" style="font-size: 0.72rem;">24.5%</span>
                </div>
              </div>
            </div>
          </div>

          <!-- UPLOAD FOTO & STATUS -->
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark">Foto Hidangan (Opsional)</label>
              <input type="file" name="image" class="form-control rounded-3" accept="image/jpeg,image/png,image/webp">
            </div>

            <div class="col-md-6">
              <label class="form-label small fw-bold text-dark d-block">Status Penayangan</label>
              <div class="d-flex align-items-center gap-4 mt-2">
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="is_available" value="1" id="switchAvailable" checked>
                  <label class="form-check-label small fw-bold text-success" for="switchAvailable">Tersedia</label>
                </div>
                <div class="form-check form-switch">
                  <input class="form-check-input" type="checkbox" name="is_published" value="1" id="switchPublished" checked>
                  <label class="form-check-label small fw-bold text-primary" for="switchPublished">Publikasikan</label>
                </div>
              </div>
            </div>
          </div>

          <!-- TOMBOL SUBMIT -->
          <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
            <a href="<?php echo APP_PATH; ?>/petugas/" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
            <button type="submit" class="btn btn-unklab-gold rounded-pill px-4 py-2 fw-bold">
              <i class="bi bi-check-circle-fill me-1"></i> Simpan & Publikasikan Menu
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
  const tkpiSelect = document.getElementById('tkpiSelect');
  const btnApplyTkpi = document.getElementById('btnApplyTkpi');

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
    } else {
      badgePctCarb.textContent = '0%';
      badgePctProtein.textContent = '0%';
      badgePctFat.textContent = '0%';
    }
  }

  [inCarb, inProtein, inFat].forEach(inp => {
    inp.addEventListener('input', calculateAtwater);
  });
  btnAutoCalc.addEventListener('click', calculateAtwater);

  // Terapkan data dari TKPI
  btnApplyTkpi.addEventListener('click', () => {
    const opt = tkpiSelect.options[tkpiSelect.selectedIndex];
    if (!opt || !opt.value) return;

    document.getElementById('menuName').value = opt.getAttribute('data-name') || '';
    document.getElementById('menuDesc').value = opt.getAttribute('data-notes') || '';
    document.getElementById('inWeight').value = opt.getAttribute('data-serving') || '100';
    inCarb.value = opt.getAttribute('data-carb') || '0';
    inProtein.value = opt.getAttribute('data-pro') || '0';
    inFat.value = opt.getAttribute('data-fat') || '0';
    document.getElementById('inFiber').value = opt.getAttribute('data-fib') || '0';

    calculateAtwater();
  });

  calculateAtwater();
});
</script>

<?php require_once dirname(__DIR__) . '/template/footer.php'; ?>
