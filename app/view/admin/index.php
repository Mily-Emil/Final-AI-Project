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

  <!-- EXECUTIVE HEADER -->
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
      <span class="badge px-3 py-1 rounded-pill mb-1" style="background: var(--unklab-navy); color: var(--unklab-gold); font-weight: 700;">
        <i class="bi bi-shield-lock-fill me-1"></i> CONTROL CENTER ADMINISTRATOR
      </span>
      <h2 class="fw-bold mb-1" style="color: var(--unklab-navy); letter-spacing: -0.5px;">
        Executive Operations Dashboard
      </h2>
      <p class="text-muted small mb-0">
        Monitoring metrik harian pemindaian nutrisi, performa akurasi AI, dan antrean intervensi Human-in-the-Loop.
      </p>
    </div>

    <!-- ACTION BUTTONS -->
    <div class="d-flex align-items-center gap-2">
      <a href="<?php echo APP_PATH; ?>/admin/queue/" class="btn btn-warning fw-bold text-dark rounded-pill px-3 py-2 shadow-sm">
        <i class="bi bi-exclamation-triangle-fill me-1"></i> Antrean HITL (<?php echo $data['stats']['pending_reviews'] ?? 0; ?>)
      </a>
      <a href="<?php echo APP_PATH; ?>/admin/pangan/" class="btn btn-outline-dark rounded-pill px-3 py-2 fw-semibold">
        <i class="bi bi-journal-bookmark me-1"></i> Master Pangan TKPI
      </a>
    </div>
  </div>

  <!-- 4 EXECUTIVE METRIC CARDS -->
  <div class="row g-3 mb-4">
    
    <!-- CARD 1: TOTAL PEMINDAIAN -->
    <div class="col-sm-6 col-lg-3">
      <div class="card card-unklab p-3 h-100 border-start border-4 border-primary">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">Total Pemindaian</small>
          <span class="badge rounded-pill bg-primary-subtle text-primary"><i class="bi bi-camera"></i></span>
        </div>
        <div class="d-flex align-items-baseline gap-2">
          <h2 class="fw-bold text-dark mb-0"><?php echo $data['stats']['total_scans'] ?? 0; ?></h2>
          <small class="text-success fw-bold">(+<?php echo $data['stats']['scans_today'] ?? 0; ?> Hari Ini)</small>
        </div>
        <small class="text-muted" style="font-size: 0.75rem;">Kumulatif seluruh pemindaian</small>
      </div>
    </div>

    <!-- CARD 2: RATA-RATA KALORI KAMPUS -->
    <div class="col-sm-6 col-lg-3">
      <div class="card card-unklab p-3 h-100 border-start border-4" style="border-left-color: var(--unklab-navy) !important;">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">Rata-Rata Kalori</small>
          <span class="badge rounded-pill bg-dark text-white"><i class="bi bi-fire"></i></span>
        </div>
        <div class="d-flex align-items-baseline gap-2">
          <h2 class="fw-bold text-dark mb-0"><?php echo $data['stats']['avg_calories'] ?? 0; ?></h2>
          <small class="text-muted fw-semibold">kkal / porsi</small>
        </div>
        <small class="text-muted" style="font-size: 0.75rem;">Standar porsi makan kampus</small>
      </div>
    </div>

    <!-- CARD 3: TINGKAT AKURASI AI -->
    <div class="col-sm-6 col-lg-3">
      <div class="card card-unklab p-3 h-100 border-start border-4 border-success">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <small class="text-muted text-uppercase fw-bold" style="font-size: 0.72rem;">Rata-Rata Keyakinan AI</small>
          <span class="badge rounded-pill bg-success-subtle text-success"><i class="bi bi-stars"></i></span>
        </div>
        <div class="d-flex align-items-baseline gap-2">
          <h2 class="fw-bold text-dark mb-0"><?php echo $data['stats']['avg_confidence'] ?? 0; ?>%</h2>
          <small class="text-muted">Threshold: 70%</small>
        </div>
        <small class="text-muted" style="font-size: 0.75rem;">Tingkat kepastian model Vision</small>
      </div>
    </div>

    <!-- CARD 4: ANTREAN INTERVENSI HITL (< 70%) -->
    <div class="col-sm-6 col-lg-3">
      <div class="card card-unklab p-3 h-100 border-start border-4" style="border-left-color: var(--unklab-gold) !important; background: #FFFDF5;">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <small class="text-warning text-uppercase fw-bold" style="font-size: 0.72rem; color: #B45309 !important;">Antrean HITL (< 70%)</small>
          <span class="badge rounded-pill" style="background: var(--unklab-gold); color: #000;"><i class="bi bi-clock-history"></i></span>
        </div>
        <div class="d-flex align-items-baseline gap-2">
          <h2 class="fw-bold text-dark mb-0"><?php echo $data['stats']['pending_reviews'] ?? 0; ?></h2>
          <small class="text-danger fw-bold">Perlu Kalibrasi</small>
        </div>
        <small class="text-muted" style="font-size: 0.75rem;">Tiket foto ambigu/kuah pekat</small>
      </div>
    </div>

  </div>

  <div class="row g-4">
    
    <!-- KOLOM KIRI: DAFTAR PRIORITAS ANTREAN KALIBRASI ADMIN (< 70%) -->
    <div class="col-lg-6">
      <div class="card card-unklab p-3 p-md-4 h-100">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
          <div>
            <h5 class="fw-bold text-dark mb-0">
              <i class="bi bi-exclamation-triangle-fill text-warning me-1"></i> Prioritas Kalibrasi Human-in-the-Loop
            </h5>
            <small class="text-muted">Hasil scan yang diragukan AI (< 70% confidence score)</small>
          </div>
          <a href="<?php echo APP_PATH; ?>/admin/queue/" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 fw-bold">
            Lihat Semua
          </a>
        </div>

        <?php if (!empty($data['pending_reviews'])): ?>
          <div class="list-group list-group-flush">
            <?php foreach (array_slice($data['pending_reviews'], 0, 5) as $pr): ?>
              <div class="list-group-item px-0 py-3 d-flex align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                  <div class="rounded-3 overflow-hidden bg-light border d-flex align-items-center justify-content-center" style="width: 54px; height: 54px; flex-shrink: 0;">
                    <?php if (!empty($pr['image_path']) && file_exists(DIR_UPLOADS_SCANS . '/' . $pr['image_path'])): ?>
                      <img src="<?php echo APP_PATH; ?>/public/uploads/scans/<?php echo htmlspecialchars($pr['image_path']); ?>" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                      <i class="bi bi-cup-straw fs-4 text-muted"></i>
                    <?php endif; ?>
                  </div>
                  <div>
                    <div class="fw-bold text-dark mb-0"><?php echo htmlspecialchars($pr['dish_name']); ?></div>
                    <div class="text-danger small" style="font-size: 0.75rem;">
                      <i class="bi bi-exclamation-circle me-1"></i><?php echo htmlspecialchars(mb_strimwidth($pr['ai_uncertainty_reason'] ?? 'Ambigu', 0, 60, '...')); ?>
                    </div>
                    <small class="text-muted" style="font-size: 0.72rem;">
                      Skor AI: <strong class="text-warning"><?php echo $pr['confidence_score']; ?>%</strong> • <?php echo date('d M H:i', strtotime($pr['created_at'])); ?>
                    </small>
                  </div>
                </div>

                <a href="<?php echo APP_PATH; ?>/admin/calibrate/<?php echo $pr['id']; ?>/" class="btn btn-sm btn-unklab-gold rounded-pill px-3 py-1 text-nowrap fw-bold">
                  Kalibrasi <i class="bi bi-sliders ms-1"></i>
                </a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php else: ?>
          <div class="text-center text-muted p-4 bg-light rounded-3">
            <i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2"></i>
            Semua pemindaian telah terverifikasi secara akurat. Tidak ada antrean yang membutuhkan kalibrasi saat ini.
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- KOLOM KANAN: AKTIVITAS SCAN TERBARU -->
    <div class="col-lg-6">
      <div class="card card-unklab p-3 p-md-4 h-100">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
          <div>
            <h5 class="fw-bold text-dark mb-0">Riwayat Pemindaian Terkini</h5>
            <small class="text-muted">Aktivitas pemindaian makanan oleh sivitas akademika</small>
          </div>
          <span class="badge bg-light text-muted border">Terakhir Ditangkap</span>
        </div>

        <div class="table-responsive">
          <table class="table table-sm table-hover align-middle mb-0 small">
            <thead class="table-light">
              <tr>
                <th>Hidangan</th>
                <th class="text-center">Kalori</th>
                <th class="text-center">Status Verifikasi</th>
                <th class="text-end">Waktu</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($data['recent_scans'])): ?>
                <?php foreach (array_slice($data['recent_scans'], 0, 6) as $rs): ?>
                  <tr>
                    <td>
                      <div class="fw-semibold text-dark text-truncate" style="max-width: 170px;"><?php echo htmlspecialchars($rs['dish_name']); ?></div>
                      <small class="text-muted" style="font-size: 0.7rem;">Berat: <?php echo round($rs['total_weight_g']); ?>g</small>
                    </td>
                    <td class="text-center fw-bold"><?php echo round($rs['total_calories']); ?> kkal</td>
                    <td class="text-center">
                      <?php if ($rs['status'] === 'auto_approved'): ?>
                        <span class="badge badge-approved px-2 py-1" style="font-size: 0.7rem;"><i class="bi bi-check-circle me-1"></i>AI Valid (<?php echo $rs['confidence_score']; ?>%)</span>
                      <?php elseif ($rs['status'] === 'admin_corrected'): ?>
                        <span class="badge badge-corrected px-2 py-1" style="font-size: 0.7rem;"><i class="bi bi-shield-check me-1"></i>Dikalibrasi</span>
                      <?php else: ?>
                        <span class="badge badge-review px-2 py-1" style="font-size: 0.7rem;"><i class="bi bi-clock me-1"></i>Review (<?php echo $rs['confidence_score']; ?>%)</span>
                      <?php endif; ?>
                    </td>
                    <td class="text-end text-muted" style="font-size: 0.72rem;">
                      <?php echo date('H:i', strtotime($rs['created_at'])); ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php else: ?>
                <tr>
                  <td colspan="4" class="text-center text-muted p-3">Belum ada riwayat pemindaian.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>

  </div>

</div>

<?php require_once dirname(__DIR__) . '/template/footer.php'; ?>
