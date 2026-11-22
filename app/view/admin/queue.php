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
      <span class="badge px-3 py-1 rounded-pill mb-1" style="background: #FEF3C7; color: #92400E; font-weight: 700; border: 1px solid #FCD34D;">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> AMBANG BATAS AI < 70.0%
      </span>
      <h2 class="fw-bold mb-1" style="color: var(--unklab-navy); letter-spacing: -0.5px;">
        Antrean Intervensi AI (Human-in-the-Loop)
      </h2>
      <p class="text-muted small mb-0">
        Hasil pemindaian foto makanan yang diragukan AI (buram, gelap, tertutup kuah, atau ambigu). Administrator wajib memeriksa dan mengalibrasi data gizi sebelum disahkan.
      </p>
    </div>

    <a href="<?php echo APP_PATH; ?>/admin/" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
      <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
    </a>
  </div>

  <!-- DAFTAR KARTU ANTREAN HITL -->
  <div class="row g-4">
    <?php if (!empty($data['pending_reviews'])): ?>
      <?php foreach ($data['pending_reviews'] as $scan): ?>
        <div class="col-md-6 col-lg-4">
          <div class="card card-unklab h-100 p-3 d-flex flex-column border-top border-4 border-warning">
            
            <!-- THUMBNAIL FOTO ASLI -->
            <div class="rounded-3 overflow-hidden bg-dark position-relative mb-3" style="aspect-ratio: 16/10;">
              <?php if (!empty($scan['image_path']) && file_exists(DIR_UPLOADS_SCANS . '/' . $scan['image_path'])): ?>
                <img src="<?php echo APP_PATH; ?>/public/uploads/scans/<?php echo htmlspecialchars($scan['image_path']); ?>" alt="Foto Makanan" style="width: 100%; height: 100%; object-fit: cover;">
              <?php else: ?>
                <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white-50">
                  <i class="bi bi-image fs-1 mb-1"></i>
                  <small>Foto tidak tersedia</small>
                </div>
              <?php endif; ?>

              <span class="position-absolute top-0 end-0 m-2 badge bg-warning text-dark fw-bold shadow-sm">
                Confidence: <?php echo $scan['confidence_score']; ?>%
              </span>
            </div>

            <!-- DETAIL KONTEN -->
            <h5 class="fw-bold text-dark mb-1"><?php echo htmlspecialchars($scan['dish_name']); ?></h5>
            <div class="text-muted small mb-2" style="font-size: 0.75rem;">
              <i class="bi bi-clock me-1"></i><?php echo date('d M Y, H:i', strtotime($scan['created_at'])); ?> WITA
            </div>

            <!-- ALASAN KETIDAKPASTIAN AI -->
            <div class="alert alert-warning p-2 px-3 small rounded-3 mb-3 border-warning border-opacity-25" style="font-size: 0.78rem;">
              <strong class="d-block mb-1 text-dark"><i class="bi bi-question-circle me-1"></i>Alasan Keraguan AI:</strong>
              <span class="text-muted"><?php echo htmlspecialchars($scan['ai_uncertainty_reason'] ?? 'Objek sulit diidentifikasi secara tegas.'); ?></span>
            </div>

            <!-- ESTIMASI AWAL MAKRO -->
            <div class="p-2 rounded-2 bg-light border small text-center mb-3 mt-auto">
              <div class="d-flex align-items-center justify-content-between mb-1">
                <span class="text-muted">Estimasi Awal:</span>
                <strong class="text-dark"><?php echo round($scan['total_calories']); ?> kkal (<?php echo round($scan['total_weight_g']); ?>g)</strong>
              </div>
              <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 0.72rem;">
                <span class="text-primary fw-bold">C: <?php echo $scan['carbohydrate_pct']; ?>%</span>
                <span class="text-success fw-bold">P: <?php echo $scan['protein_pct']; ?>%</span>
                <span class="text-warning fw-bold">F: <?php echo $scan['fat_pct']; ?>%</span>
              </div>
            </div>

            <!-- TOMBOL AKSI KALIBRASI -->
            <div class="d-grid">
              <a href="<?php echo APP_PATH; ?>/admin/calibrate/<?php echo $scan['id']; ?>/" class="btn btn-unklab-gold rounded-pill py-2 fw-bold text-center">
                <i class="bi bi-sliders me-1"></i> Kalibrasi & Koreksi Nutrisi
              </a>
            </div>

          </div>
        </div>
      <?php endforeach; ?>
    <?php else: ?>
      <div class="col-12">
        <div class="card card-unklab p-5 text-center">
          <div class="rounded-circle p-3 d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px; background: #DCFCE7; color: #166534;">
            <i class="bi bi-shield-check fs-1"></i>
          </div>
          <h4 class="fw-bold text-dark mb-1">Semua Pemindaian Terverifikasi Bersih</h4>
          <p class="text-muted mx-auto mb-3" style="max-width: 500px;">
            Tidak ada tiket pemindaian dengan tingkat keyakinan di bawah 70% yang menunggu kalibrasi saat ini. Seluruh data nutrisi valid.
          </p>
          <div>
            <a href="<?php echo APP_PATH; ?>/admin/" class="btn btn-outline-dark rounded-pill px-4">Kembali ke Dashboard</a>
          </div>
        </div>
      </div>
    <?php endif; ?>
  </div>

</div>

<?php require_once dirname(__DIR__) . '/template/footer.php'; ?>
