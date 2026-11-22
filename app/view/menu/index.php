<?php require_once dirname(__DIR__) . '/template/header.php'; ?>

<div class="container py-4">

  <!-- HEADER PAPAN MENU -->
  <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
    <div>
      <span class="badge px-3 py-1 rounded-pill mb-1" style="background: var(--unklab-gold-subtle); color: var(--unklab-navy); font-weight: 700; border: 1px solid rgba(242, 169, 0, 0.3);">
        <i class="bi bi-calendar-check me-1"></i> Informasi Resmi Dining Hall
      </span>
      <h2 class="fw-bold mb-1" style="color: var(--unklab-navy); letter-spacing: -0.5px;">
        Papan Menu Harian Kampus UNKLAB
      </h2>
      <p class="text-muted small mb-0">
        Daftar hidangan sarapan, makan siang, dan makan malam lengkap dengan takaran kalori serta komposisi bahan.
      </p>
    </div>

    <!-- FILTER TANGGAL -->
    <form action="<?php echo APP_PATH; ?>/menu/" method="GET" class="d-flex align-items-center gap-2">
      <label class="small text-muted fw-bold d-none d-sm-inline">Tanggal:</label>
      <input type="date" name="date" class="form-control form-control-sm rounded-pill px-3" value="<?php echo htmlspecialchars($data['selected_date'] ?? date('Y-m-d')); ?>" onchange="this.form.submit()">
    </form>
  </div>

  <!-- SESI 1: SARAPAN PAGI -->
  <div class="mb-5">
    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
      <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #FEF3C7; color: #D97706;">
        <i class="bi bi-cup-hot-fill fs-5"></i>
      </div>
      <div>
        <h5 class="fw-bold mb-0 text-dark">Sarapan Pagi (Breakfast)</h5>
        <small class="text-muted">Penyajian jam 06:30 - 08:30 WITA</small>
      </div>
    </div>

    <div class="row g-3">
      <?php if (!empty($data['menus']['sarapan'])): ?>
        <?php foreach ($data['menus']['sarapan'] as $m): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card card-unklab h-100 p-3">
              <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($m['name']); ?></h6>
                <?php if ($m['is_available'] == 1): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle">Tersedia</span>
                <?php else: ?>
                  <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Habis</span>
                <?php endif; ?>
              </div>
              <p class="small text-muted mb-2"><?php echo htmlspecialchars($m['description'] ?? ''); ?></p>
              
              <?php if (!empty($m['ingredients'])): ?>
                <div class="small text-secondary mb-2" style="font-size: 0.78rem;">
                  <strong>Komposisi Bahan:</strong> <?php echo htmlspecialchars($m['ingredients']); ?>
                </div>
              <?php endif; ?>

              <div class="mt-auto pt-2 border-top d-flex align-items-center justify-content-between small">
                <div>
                  <span class="fw-bold text-dark fs-6"><?php echo round($m['calories']); ?></span>
                  <small class="text-muted">kkal (<?php echo round($m['portion_weight_g']); ?>g)</small>
                </div>
                <div class="text-muted" style="font-size: 0.75rem;">
                  <span class="text-primary fw-bold">C: <?php echo $m['carbohydrate_pct']; ?>%</span> • 
                  <span class="text-success fw-bold">P: <?php echo $m['protein_pct']; ?>%</span> • 
                  <span class="text-warning fw-bold">F: <?php echo $m['fat_pct']; ?>%</span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-muted small p-3 bg-white rounded-3 border text-center">
          Tidak ada menu sarapan yang terdaftar untuk tanggal ini.
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- SESI 2: MAKAN SIANG -->
  <div class="mb-5">
    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
      <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #DBEAFE; color: #1D4ED8;">
        <i class="bi bi-sun-fill fs-5"></i>
      </div>
      <div>
        <h5 class="fw-bold mb-0 text-dark">Makan Siang (Lunch)</h5>
        <small class="text-muted">Penyajian jam 11:30 - 13:30 WITA</small>
      </div>
    </div>

    <div class="row g-3">
      <?php if (!empty($data['menus']['makan_siang'])): ?>
        <?php foreach ($data['menus']['makan_siang'] as $m): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card card-unklab h-100 p-3">
              <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($m['name']); ?></h6>
                <?php if ($m['is_available'] == 1): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle">Tersedia</span>
                <?php else: ?>
                  <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Habis</span>
                <?php endif; ?>
              </div>
              <p class="small text-muted mb-2"><?php echo htmlspecialchars($m['description'] ?? ''); ?></p>
              
              <?php if (!empty($m['ingredients'])): ?>
                <div class="small text-secondary mb-2" style="font-size: 0.78rem;">
                  <strong>Komposisi Bahan:</strong> <?php echo htmlspecialchars($m['ingredients']); ?>
                </div>
              <?php endif; ?>

              <div class="mt-auto pt-2 border-top d-flex align-items-center justify-content-between small">
                <div>
                  <span class="fw-bold text-dark fs-6"><?php echo round($m['calories']); ?></span>
                  <small class="text-muted">kkal (<?php echo round($m['portion_weight_g']); ?>g)</small>
                </div>
                <div class="text-muted" style="font-size: 0.75rem;">
                  <span class="text-primary fw-bold">C: <?php echo $m['carbohydrate_pct']; ?>%</span> • 
                  <span class="text-success fw-bold">P: <?php echo $m['protein_pct']; ?>%</span> • 
                  <span class="text-warning fw-bold">F: <?php echo $m['fat_pct']; ?>%</span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-muted small p-3 bg-white rounded-3 border text-center">
          Tidak ada menu makan siang yang terdaftar untuk tanggal ini.
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- SESI 3: MAKAN MALAM -->
  <div class="mb-5">
    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
      <div class="rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px; background: #F3E8FF; color: #7C3AED;">
        <i class="bi bi-moon-stars-fill fs-5"></i>
      </div>
      <div>
        <h5 class="fw-bold mb-0 text-dark">Makan Malam (Dinner)</h5>
        <small class="text-muted">Penyajian jam 17:30 - 19:30 WITA</small>
      </div>
    </div>

    <div class="row g-3">
      <?php if (!empty($data['menus']['makan_malam'])): ?>
        <?php foreach ($data['menus']['makan_malam'] as $m): ?>
          <div class="col-md-6 col-lg-4">
            <div class="card card-unklab h-100 p-3">
              <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($m['name']); ?></h6>
                <?php if ($m['is_available'] == 1): ?>
                  <span class="badge bg-success-subtle text-success border border-success-subtle">Tersedia</span>
                <?php else: ?>
                  <span class="badge bg-danger-subtle text-danger border border-danger-subtle">Habis</span>
                <?php endif; ?>
              </div>
              <p class="small text-muted mb-2"><?php echo htmlspecialchars($m['description'] ?? ''); ?></p>
              
              <?php if (!empty($m['ingredients'])): ?>
                <div class="small text-secondary mb-2" style="font-size: 0.78rem;">
                  <strong>Komposisi Bahan:</strong> <?php echo htmlspecialchars($m['ingredients']); ?>
                </div>
              <?php endif; ?>

              <div class="mt-auto pt-2 border-top d-flex align-items-center justify-content-between small">
                <div>
                  <span class="fw-bold text-dark fs-6"><?php echo round($m['calories']); ?></span>
                  <small class="text-muted">kkal (<?php echo round($m['portion_weight_g']); ?>g)</small>
                </div>
                <div class="text-muted" style="font-size: 0.75rem;">
                  <span class="text-primary fw-bold">C: <?php echo $m['carbohydrate_pct']; ?>%</span> • 
                  <span class="text-success fw-bold">P: <?php echo $m['protein_pct']; ?>%</span> • 
                  <span class="text-warning fw-bold">F: <?php echo $m['fat_pct']; ?>%</span>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12 text-muted small p-3 bg-white rounded-3 border text-center">
          Tidak ada menu makan malam yang terdaftar untuk tanggal ini.
        </div>
      <?php endif; ?>
    </div>
  </div>

</div>

<?php require_once dirname(__DIR__) . '/template/footer.php'; ?>
