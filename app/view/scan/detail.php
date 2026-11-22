<?php require_once dirname(__DIR__) . '/template/header.php'; ?>

<div class="container py-4">

  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <span class="badge px-3 py-1 rounded-pill mb-1" style="background: var(--unklab-navy); color: var(--unklab-gold); font-weight: 700;">
        <i class="bi bi-card-checklist me-1"></i> RINCIAN PEMINDAIAN GIZI
      </span>
      <h2 class="fw-bold mb-1" style="color: var(--unklab-navy);">
        <?php echo htmlspecialchars($data['scan']['dish_name']); ?>
      </h2>
      <p class="text-muted small mb-0">ID Tiket: #<?php echo $data['scan']['id']; ?> • Dipindai: <?php echo date('d F Y, H:i', strtotime($data['scan']['created_at'])); ?> WITA</p>
    </div>
    <a href="<?php echo APP_PATH; ?>/" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
      <i class="bi bi-arrow-left me-1"></i> Kembali ke Pemindai
    </a>
  </div>

  <div class="row g-4 justify-content-center">
    
    <!-- FOTO MAKANAN -->
    <div class="col-lg-5">
      <div class="card card-unklab p-3">
        <div class="rounded-3 overflow-hidden bg-dark position-relative mb-3" style="aspect-ratio: 4/3;">
          <?php if (!empty($data['scan']['image_path']) && file_exists(DIR_UPLOADS_SCANS . '/' . $data['scan']['image_path'])): ?>
            <img src="<?php echo APP_PATH; ?>/public/uploads/scans/<?php echo htmlspecialchars($data['scan']['image_path']); ?>" alt="Foto" style="width: 100%; height: 100%; object-fit: cover;">
          <?php else: ?>
            <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white-50">
              <i class="bi bi-image fs-1 mb-1"></i>
              <small>Foto tidak ditemukan</small>
            </div>
          <?php endif; ?>

          <span class="position-absolute top-0 end-0 m-2 badge bg-warning text-dark fw-bold">
            Confidence: <?php echo $data['scan']['confidence_score']; ?>%
          </span>
        </div>

        <!-- BADGE STATUS -->
        <div class="mb-3 text-center">
          <?php if ($data['scan']['status'] === 'auto_approved'): ?>
            <span class="badge badge-approved px-3 py-2 fs-6"><i class="bi bi-check-circle-fill me-1"></i> Terverifikasi AI</span>
          <?php elseif ($data['scan']['status'] === 'admin_corrected'): ?>
            <span class="badge badge-corrected px-3 py-2 fs-6"><i class="bi bi-shield-check me-1"></i> Dikalibrasi Administrator</span>
          <?php else: ?>
            <span class="badge badge-review px-3 py-2 fs-6"><i class="bi bi-clock-history me-1"></i> Menunggu Verifikasi Admin</span>
          <?php endif; ?>
        </div>

        <?php if (!empty($data['scan']['admin_notes'])): ?>
          <div class="alert alert-info small p-3 rounded-3 mb-0">
            <strong>Catatan Administrator:</strong>
            <div class="mt-1"><?php echo htmlspecialchars($data['scan']['admin_notes']); ?></div>
            <?php if (!empty($data['scan']['reviewer_name'])): ?>
              <small class="text-muted d-block mt-1">Diverifikasi oleh: <?php echo htmlspecialchars($data['scan']['reviewer_name']); ?></small>
            <?php endif; ?>
          </div>
        <?php endif; ?>
      </div>
    </div>

    <!-- DETAIL GIZI -->
    <div class="col-lg-7">
      <div class="card card-unklab p-4">
        
        <!-- RINGKASAN ENERGI -->
        <div class="row g-2 mb-4 text-center">
          <div class="col-6">
            <div class="p-3 rounded-3" style="background: var(--unklab-navy); color: #fff;">
              <small class="text-white-50 text-uppercase fw-semibold" style="font-size: 0.72rem;">Total Energi</small>
              <div class="display-6 fw-bold lh-1 my-1"><?php echo round($data['scan']['total_calories']); ?></div>
              <small class="text-warning fw-bold">kkal (Kalori)</small>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 rounded-3 border bg-light">
              <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.72rem;">Estimasi Berat Porsi</small>
              <div class="display-6 fw-bold lh-1 my-1 text-dark"><?php echo round($data['scan']['total_weight_g']); ?></div>
              <small class="text-secondary fw-semibold">gram</small>
            </div>
          </div>
        </div>

        <!-- PROGRESS BAR MAKRO -->
        <div class="border rounded-3 p-3 mb-4 bg-white">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="small fw-bold text-dark">Rasio Distribusi Makronutrien</span>
            <span class="badge bg-light text-muted border">Atwater 100%</span>
          </div>

          <div class="progress mb-3" style="height: 14px; border-radius: 7px;">
            <div class="progress-bar" style="width: <?php echo $data['scan']['carbohydrate_pct']; ?>%; background-color: var(--macro-carb);"></div>
            <div class="progress-bar" style="width: <?php echo $data['scan']['protein_pct']; ?>%; background-color: var(--macro-protein);"></div>
            <div class="progress-bar" style="width: <?php echo $data['scan']['fat_pct']; ?>%; background-color: var(--macro-fat);"></div>
          </div>

          <div class="row g-2 text-center small">
            <div class="col-4">
              <div class="p-2 rounded-2 border badge-macro-carb">
                <div class="fw-bold fs-6"><?php echo $data['scan']['carbohydrate_pct']; ?>%</div>
                <div class="fw-semibold">Karbohidrat</div>
                <div class="text-muted" style="font-size: 0.75rem;"><?php echo $data['scan']['carbohydrate_g']; ?>g</div>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 rounded-2 border badge-macro-protein">
                <div class="fw-bold fs-6"><?php echo $data['scan']['protein_pct']; ?>%</div>
                <div class="fw-semibold">Protein</div>
                <div class="text-muted" style="font-size: 0.75rem;"><?php echo $data['scan']['protein_g']; ?>g</div>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 rounded-2 border badge-macro-fat">
                <div class="fw-bold fs-6"><?php echo $data['scan']['fat_pct']; ?>%</div>
                <div class="fw-semibold">Lemak</div>
                <div class="text-muted" style="font-size: 0.75rem;"><?php echo $data['scan']['fat_g']; ?>g</div>
              </div>
            </div>
          </div>

          <div class="d-flex align-items-center justify-content-between mt-3 pt-2 border-top small text-muted">
            <span><i class="bi bi-flower1 me-1 text-purple"></i>Serat Pangan (Fiber):</span>
            <strong class="text-dark"><?php echo $data['scan']['fiber_g']; ?> gram</strong>
          </div>
        </div>

        <!-- KOMPONEN LAUK -->
        <?php if (!empty($data['scan']['food_components_decoded'])): ?>
          <div class="mb-3">
            <span class="small fw-bold text-dark d-block mb-2">Rincian Komponen Terdeteksi:</span>
            <div class="table-responsive">
              <table class="table table-sm table-bordered align-middle mb-0 small">
                <thead class="table-light">
                  <tr>
                    <th>Komponen Makanan</th>
                    <th class="text-center">Porsi</th>
                    <th class="text-center">Kalori</th>
                    <th class="text-center">K / P / L</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($data['scan']['food_components_decoded'] as $comp): ?>
                    <tr>
                      <td class="fw-semibold"><?php echo htmlspecialchars($comp['name'] ?? '-'); ?></td>
                      <td class="text-center"><?php echo $comp['portion_g'] ?? 0; ?>g</td>
                      <td class="text-center"><?php echo $comp['calories'] ?? 0; ?> kkal</td>
                      <td class="text-center text-muted"><?php echo $comp['carbs_g'] ?? 0; ?>g / <?php echo $comp['protein_g'] ?? 0; ?>g / <?php echo $comp['fat_g'] ?? 0; ?>g</td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        <?php endif; ?>

      </div>
    </div>

  </div>

</div>

<?php require_once dirname(__DIR__) . '/template/footer.php'; ?>
