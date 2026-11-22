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
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <span class="badge px-3 py-1 rounded-pill mb-1" style="background: var(--unklab-navy); color: var(--unklab-gold); font-weight: 700;">
        <i class="bi bi-sliders me-1"></i> HUMAN-IN-THE-LOOP CALIBRATION TOOL
      </span>
      <h2 class="fw-bold mb-1" style="color: var(--unklab-navy);">
        Kalibrasi & Koreksi Nutrisi Hidangan #<?php echo $data['scan']['id']; ?>
      </h2>
      <p class="text-muted small mb-0">
        Periksa foto asli makanan, sesuaikan gramasi komponen, dan kalibrasi persentase makronutrien hingga presisi 100%.
      </p>
    </div>
    <a href="<?php echo APP_PATH; ?>/admin/queue/" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
      <i class="bi bi-arrow-left me-1"></i> Kembali ke Antrean
    </a>
  </div>

  <div class="row g-4">
    
    <!-- KOLOM KIRI: FOTO ASLI MAKANAN & ALASAN KERAGUAN AI -->
    <div class="col-lg-5">
      <div class="card card-unklab p-3 p-md-4 sticky-top" style="top: 80px;">
        <span class="small fw-bold text-dark d-block mb-2">
          <i class="bi bi-image me-1 text-primary"></i> Foto Asli Tangkapan Kamera:
        </span>

        <!-- FOTO MAKANAN ASLI -->
        <div class="rounded-3 overflow-hidden bg-dark mb-3 position-relative" style="aspect-ratio: 4/3;">
          <?php if (!empty($data['scan']['image_path']) && file_exists(DIR_UPLOADS_SCANS . '/' . $data['scan']['image_path'])): ?>
            <img src="<?php echo APP_PATH; ?>/public/uploads/scans/<?php echo htmlspecialchars($data['scan']['image_path']); ?>" alt="Foto Makanan" style="width: 100%; height: 100%; object-fit: cover;">
          <?php else: ?>
            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white-50">
              <i class="bi bi-image fs-1 mb-2"></i>
              <small>Foto tidak ditemukan di server</small>
            </div>
          <?php endif; ?>

          <span class="position-absolute top-0 end-0 m-2 badge bg-warning text-dark fw-bold shadow-sm">
            AI Score: <?php echo $data['scan']['confidence_score']; ?>%
          </span>
        </div>

        <!-- ALASAN KETIDAKPASTIAN AI -->
        <div class="alert alert-warning p-3 small rounded-3 mb-3 border-warning border-opacity-50">
          <strong class="d-block mb-1 text-dark">
            <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Catatan Ketidakpastian AI (< 70%):
          </strong>
          <span class="text-dark">
            <?php echo htmlspecialchars($data['scan']['ai_uncertainty_reason'] ?? 'Objek diragukan oleh model AI.'); ?>
          </span>
        </div>

        <div class="small text-muted border-top pt-2">
          <div><i class="bi bi-clock me-1"></i> <strong>Waktu Scan:</strong> <?php echo date('d F Y, H:i:s', strtotime($data['scan']['created_at'])); ?> WITA</div>
          <div><i class="bi bi-tag me-1"></i> <strong>Status Saat Ini:</strong> <span class="badge badge-review"><?php echo htmlspecialchars($data['scan']['status']); ?></span></div>
        </div>

      </div>
    </div>

    <!-- KOLOM KANAN: FORMULIR KALIBRASI & KOREKSI NUTRISI -->
    <div class="col-lg-7">
      <div class="card card-unklab p-4">
        
        <form action="<?php echo APP_PATH; ?>/admin/save_calibration/" method="POST">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token'] ?? ''); ?>">
          <input type="hidden" name="scan_id" value="<?php echo $data['scan']['id']; ?>">

          <!-- TEMPLATE PRESET DARI TKPI / MASAKAN LOKAL -->
          <div class="p-3 mb-4 rounded-3 bg-light border">
            <label class="small fw-bold text-dark d-block mb-1">
              <i class="bi bi-magic text-warning me-1"></i> Bantu Kalibrasi Cepat dari Master Data TKPI:
            </label>
            <div class="d-flex gap-2">
              <select id="tkpiSelect" class="form-select form-select-sm rounded-pill">
                <option value="">-- Pilih Referensi Standar Gizi Kampus --</option>
                <?php foreach ($data['food_references'] as $ref): ?>
                  <option value="<?php echo $ref['id']; ?>" 
                          data-name="<?php echo htmlspecialchars($ref['food_name']); ?>"
                          data-serving="<?php echo $ref['serving_size_g']; ?>"
                          data-cal="<?php echo $ref['calories']; ?>"
                          data-carb="<?php echo $ref['carbohydrate_g']; ?>"
                          data-pro="<?php echo $ref['protein_g']; ?>"
                          data-fat="<?php echo $ref['fat_g']; ?>"
                          data-fib="<?php echo $ref['fiber_g']; ?>">
                    <?php echo htmlspecialchars($ref['food_name']); ?> (<?php echo $ref['category']; ?>)
                  </option>
                <?php endforeach; ?>
              </select>
              <button type="button" id="btnApplyTkpi" class="btn btn-sm btn-outline-primary rounded-pill px-3 text-nowrap">
                Terapkan Nilai
              </button>
            </div>
          </div>

          <!-- NAMA HIDANGAN KOREKSI -->
          <div class="mb-3">
            <label class="form-label small fw-bold text-dark">Nama Hidangan Koreksi <span class="text-danger">*</span></label>
            <input type="text" name="dish_name" id="dishName" class="form-control rounded-3 fw-bold" value="<?php echo htmlspecialchars($data['scan']['dish_name']); ?>" required>
            <small class="text-muted">Koreksi jika tebakan awal AI kurang tepat atau salah penamaan.</small>
          </div>

          <!-- TAKARAN GIZI & MAKRONUTRIEN -->
          <div class="card p-3 mb-3 bg-light border">
            <div class="d-flex align-items-center justify-content-between mb-3">
              <span class="small fw-bold text-dark">
                <i class="bi bi-calculator me-1 text-primary"></i> Parameter Nutrisi (Atwater Factor Recalculator):
              </span>
              <button type="button" id="btnRecalculate" class="btn btn-sm btn-outline-success rounded-pill px-3">
                <i class="bi bi-arrow-repeat me-1"></i> Hitung Ulang Makro
              </button>
            </div>

            <div class="row g-2 mb-3">
              <div class="col-sm-6 col-md-4">
                <label class="small text-muted fw-semibold">Berat Porsi (g)</label>
                <input type="number" step="0.1" name="total_weight_g" id="totalWeight" class="form-control form-control-sm rounded-2" value="<?php echo $data['scan']['total_weight_g']; ?>">
              </div>
              <div class="col-sm-6 col-md-4">
                <label class="small text-muted fw-semibold">Total Energi (kkal)</label>
                <input type="number" step="0.1" name="total_calories" id="totalCalories" class="form-control form-control-sm rounded-2 fw-bold text-primary" value="<?php echo round($data['scan']['total_calories']); ?>" readonly>
              </div>
              <div class="col-sm-6 col-md-4">
                <label class="small text-muted fw-semibold">Serat Pangan (g)</label>
                <input type="number" step="0.1" name="fiber_g" id="totalFiber" class="form-control form-control-sm rounded-2" value="<?php echo $data['scan']['fiber_g']; ?>">
              </div>
            </div>

            <!-- MAKRONUTRIEN INPUTS -->
            <div class="row g-2 text-center mb-3">
              <div class="col-4">
                <div class="p-2 bg-white rounded-2 border">
                  <label class="small text-primary fw-bold mb-1">Karbohidrat (g)</label>
                  <input type="number" step="0.1" name="carbohydrate_g" id="inCarb" class="form-control form-control-sm text-center fw-bold" value="<?php echo $data['scan']['carbohydrate_g']; ?>">
                  <span class="badge bg-primary-subtle text-primary mt-1" id="badgePctCarb" style="font-size: 0.72rem;"><?php echo $data['scan']['carbohydrate_pct']; ?>%</span>
                </div>
              </div>
              <div class="col-4">
                <div class="p-2 bg-white rounded-2 border">
                  <label class="small text-success fw-bold mb-1">Protein (g)</label>
                  <input type="number" step="0.1" name="protein_g" id="inProtein" class="form-control form-control-sm text-center fw-bold" value="<?php echo $data['scan']['protein_g']; ?>">
                  <span class="badge bg-success-subtle text-success mt-1" id="badgePctProtein" style="font-size: 0.72rem;"><?php echo $data['scan']['protein_pct']; ?>%</span>
                </div>
              </div>
              <div class="col-4">
                <div class="p-2 bg-white rounded-2 border">
                  <label class="small text-warning fw-bold mb-1">Lemak (g)</label>
                  <input type="number" step="0.1" name="fat_g" id="inFat" class="form-control form-control-sm text-center fw-bold" value="<?php echo $data['scan']['fat_g']; ?>">
                  <span class="badge bg-warning-subtle text-warning mt-1" id="badgePctFat" style="font-size: 0.72rem;"><?php echo $data['scan']['fat_pct']; ?>%</span>
                </div>
              </div>
            </div>

            <div class="small text-muted" style="font-size: 0.75rem;">
              <i class="bi bi-info-circle me-1"></i>
              Distribusi dihitung otomatis dari faktor Atwater (1g C = 4 kkal, 1g P = 4 kkal, 1g F = 9 kkal). Total persentase dijamin tepat 100.0%.
            </div>
          </div>

          <!-- CATATAN ADMINISTRATOR -->
          <div class="mb-4">
            <label class="form-label small fw-bold text-dark">Catatan Kalibrasi Administrator <span class="text-danger">*</span></label>
            <textarea name="admin_notes" rows="3" class="form-control rounded-3" placeholder="Jelaskan dasar penyesuaian porsi atau koreksi komponen makanan..." required><?php echo htmlspecialchars($data['scan']['admin_notes'] ?? 'Dikalibrasi oleh Administrator: Gramasi komponen disesuaikan dengan standar porsi kantin kampus UNKLAB.'); ?></textarea>
          </div>

          <!-- TOMBOL SIMPAN SEBAGAI ADMIN CORRECTED -->
          <div class="d-flex align-items-center justify-content-between pt-3 border-top">
            <a href="<?php echo APP_PATH; ?>/admin/queue/" class="btn btn-outline-secondary rounded-pill px-4">Batal</a>
            <button type="submit" class="btn btn-unklab-gold rounded-pill px-4 py-2 fw-bold shadow-sm">
              <i class="bi bi-check-circle-fill me-1"></i> Simpan & Verifikasi (Admin Corrected)
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
  const totalCalories = document.getElementById('totalCalories');
  const badgePctCarb = document.getElementById('badgePctCarb');
  const badgePctProtein = document.getElementById('badgePctProtein');
  const badgePctFat = document.getElementById('badgePctFat');
  const btnRecalculate = document.getElementById('btnRecalculate');
  const tkpiSelect = document.getElementById('tkpiSelect');
  const btnApplyTkpi = document.getElementById('btnApplyTkpi');

  function recalculateAtwater() {
    const c = Math.max(0, parseFloat(inCarb.value) || 0);
    const p = Math.max(0, parseFloat(inProtein.value) || 0);
    const f = Math.max(0, parseFloat(inFat.value) || 0);

    const cKkal = c * 4.0;
    const pKkal = p * 4.0;
    const fKkal = f * 9.0;
    const totalMacroKkal = cKkal + pKkal + fKkal;

    totalCalories.value = Math.round(totalMacroKkal);

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

  [inCarb, inProtein, inFat].forEach(inp => inp.addEventListener('input', recalculateAtwater));
  btnRecalculate.addEventListener('click', recalculateAtwater);

  btnApplyTkpi.addEventListener('click', () => {
    const opt = tkpiSelect.options[tkpiSelect.selectedIndex];
    if (!opt || !opt.value) return;

    document.getElementById('dishName').value = opt.getAttribute('data-name') || '';
    document.getElementById('totalWeight').value = opt.getAttribute('data-serving') || '100';
    inCarb.value = opt.getAttribute('data-carb') || '0';
    inProtein.value = opt.getAttribute('data-pro') || '0';
    inFat.value = opt.getAttribute('data-fat') || '0';
    document.getElementById('totalFiber').value = opt.getAttribute('data-fib') || '0';

    recalculateAtwater();
  });

  recalculateAtwater();
});
</script>

<?php require_once dirname(__DIR__) . '/template/footer.php'; ?>
