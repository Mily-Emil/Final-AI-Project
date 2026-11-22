<?php require_once dirname(__DIR__) . '/template/header.php'; ?>
<?php
$currentRole = $_SESSION['user-role'] ?? 'guest';
$activeSession = $data['active_meal_session'] ?? 'makan_siang';
$todayMenus = $data['today_menus'] ?? ['sarapan' => [], 'makan_siang' => [], 'makan_malam' => []];
?>

<style>
  /* Modern SaaS Minimalist Hero */
  .saas-hero {
    position: relative;
    background:
      radial-gradient(60% 80% at 85% 15%, rgba(22, 58, 108, 0.55) 0%, rgba(10, 34, 64, 0) 70%),
      radial-gradient(40% 60% at 15% 85%, rgba(242, 169, 0, 0.08) 0%, rgba(10, 34, 64, 0) 70%),
      linear-gradient(160deg, #07182E 0%, #0A2240 55%, #112E56 100%);
    color: #FFFFFF;
    padding-top: 1.4rem;
    padding-bottom: 6.5rem;
    overflow: hidden;
  }
  .saas-hero::before {
    content: "";
    position: absolute;
    inset: 0;
    background-image:
      linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
      linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
    background-size: 48px 48px;
    pointer-events: none;
  }
  .saas-container {
    position: relative;
    z-index: 1;
    max-width: 1000px;
    margin: 0 auto;
    padding: 0 1.25rem;
  }
  .saas-eyebrow {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
    font-size: 0.72rem;
    letter-spacing: 0.1em;
    text-transform: uppercase;
    color: rgba(255, 255, 255, 0.72);
    font-weight: 600;
    padding: 0.3rem 0.75rem;
    border-radius: 99px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.12);
  }
  .saas-eyebrow-dot {
    width: 6px;
    height: 6px;
    border-radius: 50%;
    background: var(--unklab-gold);
  }
  .saas-hero-title {
    font-size: clamp(2rem, 4.4vw, 3rem);
    font-weight: 700;
    line-height: 1.14;
    letter-spacing: -0.03em;
    color: #FFFFFF;
  }
  .saas-hero-sub {
    color: rgba(255, 255, 255, 0.7);
    font-size: 0.98rem;
    line-height: 1.65;
    max-width: 540px;
  }

  /* Overlapping SaaS Surface */
  .saas-surface {
    background-color: var(--unklab-surface);
    padding-bottom: 4.5rem;
  }
  .saas-elevated-card {
    background: #FFFFFF;
    border: 1px solid var(--unklab-border);
    border-radius: 20px;
    box-shadow: var(--saas-shadow-md);
    padding: 1.75rem;
  }
  .saas-overlap {
    margin-top: -3.75rem;
    position: relative;
    z-index: 5;
  }

  /* Meal Slot Cards */
  .meal-slot-card {
    background: #FFFFFF;
    border: 1px solid var(--unklab-border);
    border-radius: 14px;
    padding: 1.15rem 1.25rem;
    height: 100%;
    transition: border-color 0.18s ease, box-shadow 0.18s ease;
  }
  .meal-slot-card.is-active {
    background: #FFFDF7;
    border-color: rgba(10, 34, 64, 0.28);
    box-shadow: var(--saas-shadow-sm);
  }
  .meal-item-row {
    display: block;
    width: 100%;
    text-align: left;
    background: transparent;
    border: none;
    padding: 0.65rem 0;
    border-bottom: 1px solid var(--unklab-border-subtle);
    transition: opacity 0.15s ease;
  }
  .meal-item-row:last-child {
    border-bottom: none;
    padding-bottom: 0;
  }
  .meal-item-row:hover {
    opacity: 0.78;
  }
  .mini-macro-bar {
    height: 4px;
    border-radius: 99px;
    overflow: hidden;
    display: flex;
    background: #F1F5F9;
    margin-top: 0.45rem;
  }
  .status-dot {
    width: 7px;
    height: 7px;
    border-radius: 50%;
    display: inline-block;
  }

  /* Segmented Control */
  .saas-segmented {
    display: inline-flex;
    background: #F1F5F9;
    padding: 3px;
    border-radius: 10px;
    border: 1px solid var(--unklab-border);
  }
  .saas-segmented button {
    border: none;
    background: transparent;
    color: var(--unklab-text-muted);
    font-size: 0.8rem;
    font-weight: 600;
    padding: 0.35rem 0.85rem;
    border-radius: 8px;
    transition: all 0.15s ease;
  }
  .saas-segmented button.active {
    background: #FFFFFF;
    color: var(--unklab-navy);
    box-shadow: var(--saas-shadow-xs);
  }
</style>

<!-- ===================================================================== -->
<!-- 1. MODERN SAAS HERO                                                   -->
<!-- ===================================================================== -->
<section class="saas-hero">
  <div class="saas-container">

    <!-- Minimalist Top Navigation -->
    <header class="d-flex align-items-center justify-content-between mb-5">
      <a href="<?php echo APP_PATH; ?>/" class="d-flex align-items-center gap-2 text-decoration-none text-white fw-semibold" style="font-size: 0.96rem;">
        <span class="saas-brand-mark" style="width:28px;height:28px;font-size:0.85rem;">
          <i class="bi bi-cup-hot-fill"></i>
        </span>
        <span>Nutrition UNKLAB</span>
      </a>

      <nav class="d-none d-md-flex align-items-center gap-4">
        <a href="#menuHariIni" class="text-white-50 text-decoration-none small fw-medium">Menu Hari Ini</a>
        <a href="#scannerSection" class="text-white-50 text-decoration-none small fw-medium">Pemindai AI</a>
        <a href="<?php echo APP_PATH; ?>/menu/" class="text-white-50 text-decoration-none small fw-medium">Jadwal Lengkap</a>
      </nav>

      <div class="d-flex align-items-center gap-2">
        <?php if (!empty($_SESSION['user-id'])): ?>
          <a href="<?php echo APP_PATH; ?>/<?php echo ($currentRole === 'admin') ? 'admin' : 'petugas'; ?>/" class="btn btn-ghost-light btn-sm rounded-3 px-3 py-1">
            Dashboard
          </a>
          <a href="<?php echo APP_PATH; ?>/login/logout/" class="btn btn-ghost-light btn-sm rounded-3 px-3 py-1 text-white-50">
            Keluar
          </a>
        <?php else: ?>
          <a href="<?php echo APP_PATH; ?>/login/" class="btn btn-ghost-light btn-sm rounded-3 px-3 py-1">
            Masuk
          </a>
        <?php endif; ?>
      </div>
    </header>

    <!-- Hero Content -->
    <div class="pt-1 pb-2">
      <div class="saas-eyebrow mb-3">
        <span class="saas-eyebrow-dot"></span>
        Universitas Klabat • Airmadidi
      </div>
      <h1 class="saas-hero-title mb-3">
        Analisis nutrisi makanan<br>
        dengan kamera AI instan.
      </h1>
      <p class="saas-hero-sub mb-4">
        Pindai piring makanan langsung dari kamera smartphone tanpa login untuk mengetahui estimasi kalori serta persentase Karbohidrat, Protein, dan Lemak secara real-time.
      </p>

      <div class="d-flex flex-wrap align-items-center gap-3">
        <button type="button" id="btnHeroScan" class="btn btn-unklab-gold rounded-pill px-4 py-2 d-inline-flex align-items-center gap-2" style="font-size: 0.9rem;">
          <i class="bi bi-camera"></i>
          <span>Pindai sekarang</span>
        </button>
        <a href="<?php echo APP_PATH; ?>/menu/" class="btn btn-ghost-light rounded-pill px-4 py-2" style="font-size: 0.88rem;">
          Lihat menu lengkap <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>
    </div>

  </div>
</section>

<!-- ===================================================================== -->
<!-- 2. ELEVATED PUBLIC DASHBOARD: MENU HARIAN + AI SCANNER                -->
<!-- ===================================================================== -->
<section class="saas-surface">
  <div class="saas-container">

    <!-- Flash Message -->
    <?php if (!empty($data['flash_message'])): ?>
      <div class="alert alert-<?php echo htmlspecialchars($data['flash_message']['type'] ?? 'info'); ?> alert-dismissible fade show border-0 rounded-4 mb-3 small shadow-sm" role="alert">
        <strong><?php echo htmlspecialchars($data['flash_message']['title'] ?? 'Info'); ?>:</strong>
        <?php echo htmlspecialchars($data['flash_message']['text'] ?? ''); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endif; ?>

    <!-- Auto-Setup DB Banner jika belum siap -->
    <?php if (!($data['db_diagnostics']['is_connected'] ?? false) || !($data['db_diagnostics']['is_schema_ready'] ?? false)): ?>
      <div class="saas-elevated-card saas-overlap mb-4 border-warning" style="background: #FFFDF5;">
        <div class="d-md-flex align-items-center justify-content-between gap-3">
          <div class="mb-3 mb-md-0">
            <div class="fw-semibold text-dark mb-1">
              <i class="bi bi-database-exclamation text-warning me-2"></i>Inisialisasi Database Diperlukan
            </div>
            <p class="text-muted small mb-0">
              Database <code>db_project</code> belum siap. Klik tombol di samping untuk membuat seluruh tabel secara otomatis.
            </p>
          </div>
          <form action="<?php echo APP_PATH; ?>/setup/init/" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($data['csrf_token'] ?? ''); ?>">
            <button type="submit" class="btn btn-unklab-gold btn-sm rounded-pill px-4 py-2 text-nowrap">
              Buat Database Otomatis
            </button>
          </form>
        </div>
      </div>
    <?php endif; ?>

    <!-- DASHBOARD UTAMA: DAFTAR MAKANAN HARI INI (TANPA LOGIN) -->
    <div id="menuHariIni" class="saas-elevated-card <?php echo (($data['db_diagnostics']['is_connected'] ?? false) && ($data['db_diagnostics']['is_schema_ready'] ?? false)) ? 'saas-overlap' : ''; ?> mb-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-3">
        <div class="d-flex align-items-center gap-2">
          <span class="d-inline-flex align-items-center justify-content-center rounded-2" style="width:30px;height:30px;background:#F1F5F9;color:var(--unklab-navy);">
            <i class="bi bi-cup-hot"></i>
          </span>
          <div>
            <h5 class="fw-bold mb-0 text-dark" style="font-size: 1rem;">Daftar makanan hari ini</h5>
            <div class="text-muted" style="font-size: 0.76rem;">Dining Hall UNKLAB • <?php echo date('d F Y'); ?></div>
          </div>
        </div>
        <a href="<?php echo APP_PATH; ?>/menu/" class="btn btn-sm btn-light border rounded-pill px-3 text-muted" style="font-size: 0.78rem;">
          Semua sesi <i class="bi bi-arrow-right ms-1"></i>
        </a>
      </div>

      <div class="row g-3">
        <?php
        $sessionsMeta = [
          'sarapan'     => ['label' => 'Sarapan',     'time' => '06:30 – 08:30'],
          'makan_siang' => ['label' => 'Makan Siang', 'time' => '11:30 – 13:30'],
          'makan_malam' => ['label' => 'Makan Malam', 'time' => '17:30 – 19:30'],
        ];
        foreach ($sessionsMeta as $key => $meta):
          $items = $todayMenus[$key] ?? [];
          $isActive = ($activeSession === $key);
        ?>
        <div class="col-md-4">
          <div class="meal-slot-card <?php echo $isActive ? 'is-active' : ''; ?>">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-dark" style="font-size: 0.92rem;"><?php echo $meta['label']; ?></span>
                <?php if ($isActive): ?>
                  <span class="badge rounded-pill" style="background: var(--unklab-gold-soft); color: #92400E; border: 1px solid #FDE68A; font-size: 0.65rem;">Sesi Aktif</span>
                <?php endif; ?>
              </div>
              <span class="text-muted tabular-nums" style="font-size: 0.72rem;"><?php echo $meta['time']; ?></span>
            </div>

            <?php if (!empty($items)): ?>
              <div class="mt-2">
                <?php foreach ($items as $item): ?>
                  <button type="button"
                          class="meal-item-row"
                          onclick='openMenuDetail(<?php echo htmlspecialchars(json_encode($item), ENT_QUOTES, "UTF-8"); ?>)'>
                    <div class="d-flex align-items-start justify-content-between gap-2">
                      <span class="fw-medium text-dark" style="font-size: 0.84rem; line-height: 1.35;">
                        <?php echo htmlspecialchars($item['name']); ?>
                      </span>
                      <span class="status-dot mt-1 flex-shrink-0 <?php echo ($item['is_available'] == 1) ? 'bg-success' : 'bg-danger'; ?>"
                            title="<?php echo ($item['is_available'] == 1) ? 'Tersedia' : 'Habis'; ?>"></span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between text-muted tabular-nums mt-1" style="font-size: 0.74rem;">
                      <strong class="text-dark"><?php echo round($item['calories']); ?> kkal</strong>
                      <span>K <?php echo round($item['carbohydrate_pct']); ?>% · P <?php echo round($item['protein_pct']); ?>% · L <?php echo round($item['fat_pct']); ?>%</span>
                    </div>
                    <div class="mini-macro-bar">
                      <div style="width: <?php echo (float)$item['carbohydrate_pct']; ?>%; background: var(--macro-carb);"></div>
                      <div style="width: <?php echo (float)$item['protein_pct']; ?>%; background: var(--macro-protein);"></div>
                      <div style="width: <?php echo (float)$item['fat_pct']; ?>%; background: var(--macro-fat);"></div>
                    </div>
                  </button>
                <?php endforeach; ?>
              </div>
            <?php else: ?>
              <p class="text-muted mb-0 mt-2" style="font-size: 0.84rem;">Menu belum diatur.</p>
            <?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- =================================================================== -->
    <!-- AI VISION FOOD SCANNER (MODERN SAAS CARD)                           -->
    <!-- =================================================================== -->
    <div id="scannerSection" class="card-unklab p-4">
      <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 pb-3 border-bottom">
        <div>
          <h5 class="fw-bold mb-0 text-dark" style="font-size: 1rem;">
            Pemindai Nutrisi Kamera AI
          </h5>
          <p class="text-muted mb-0" style="font-size: 0.82rem;">
            Estimasi kalori &amp; distribusi makronutrien (Atwater 100%) dengan verifikasi ambang batas 70%.
          </p>
        </div>

        <!-- Segmented Control -->
        <div class="saas-segmented">
          <button type="button" id="segCamera" class="active"><i class="bi bi-camera me-1"></i>Kamera</button>
          <button type="button" id="segUpload"><i class="bi bi-upload me-1"></i>Unggah Foto</button>
        </div>
      </div>

      <div class="row g-4 align-items-stretch">

        <!-- Kiri: Kamera / Upload Viewport -->
        <div class="col-lg-6 d-flex flex-column">
          <div class="camera-container mb-3 flex-grow-1" id="cameraViewport">
            <video id="webcamVideo" autoplay playsinline muted class="d-none"></video>
            <canvas id="snapshotCanvas" class="d-none"></canvas>
            <img id="imagePreview" class="d-none" alt="Preview Hidangan">

            <div class="scan-reticle d-none" id="scanReticle"></div>
            <div class="scan-laser" id="scanLaser"></div>

            <!-- Clean Initial State -->
            <div id="cameraPlaceholder" class="text-center text-white p-4">
              <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3" style="width:52px;height:52px;background:rgba(255,255,255,0.08);">
                <i class="bi bi-camera fs-4 text-warning"></i>
              </div>
              <div class="small text-white-50 mb-3">Siap memindai hidangan makanan Anda</div>
              <button type="button" id="btnPlaceholderStart" class="btn btn-unklab-gold btn-sm rounded-pill px-4 py-2">
                Aktifkan Kamera
              </button>
            </div>

            <!-- Loading Overlay -->
            <div id="scanLoadingOverlay" class="position-absolute w-100 h-100 top-0 start-0 d-flex flex-column align-items-center justify-content-center bg-dark bg-opacity-75 text-white d-none" style="backdrop-filter: blur(6px); z-index: 10;">
              <div class="spinner-border text-warning mb-3" role="status"></div>
              <div class="fw-semibold small">Menganalisis komponen makanan...</div>
              <div class="text-white-50" style="font-size: 0.75rem;">Kalkulasi kalori &amp; skor keyakinan AI</div>
            </div>
          </div>

          <div class="d-flex align-items-center gap-2">
            <button id="btnCapture" type="button" class="btn btn-unklab-gold rounded-pill flex-grow-1 py-2" style="font-size: 0.88rem;">
              <i class="bi bi-camera-fill me-1"></i> Ambil Foto &amp; Analisis
            </button>
            <button id="btnSwitchCamera" type="button" class="btn btn-light border rounded-pill px-3 py-2 d-none" title="Ganti Kamera Depan/Belakang">
              <i class="bi bi-arrow-repeat"></i>
            </button>
            <label for="fileInput" class="btn btn-light border rounded-pill px-3 py-2 mb-0 text-dark" style="cursor: pointer;" title="Pilih foto dari galeri">
              <i class="bi bi-image"></i>
            </label>
            <input type="file" id="fileInput" accept="image/jpeg,image/png,image/webp" class="d-none">
          </div>
        </div>

        <!-- Kanan: Nutrition Analytics Panel -->
        <div class="col-lg-6 d-flex flex-column">
          <div id="resultEmptyState" class="flex-grow-1 d-flex flex-column justify-content-center align-items-center p-4 rounded-4 border text-center" style="background: #F8FAFC; min-height: 300px;">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle mb-3 bg-white border" style="width:48px;height:48px;">
              <i class="bi bi-bar-chart-line text-muted fs-5"></i>
            </div>
            <div class="fw-semibold text-dark mb-1" style="font-size: 0.94rem;">Hasil analisis nutrisi</div>
            <p class="text-muted small mb-0" style="max-width: 320px;">
              Menampilkan estimasi kalori, berat porsi, persentase Karbohidrat, Protein, Lemak, serat pangan, dan indikator verifikasi AI.
            </p>
          </div>

          <div id="resultContent" class="d-none flex-grow-1 d-flex flex-column">
            <div class="d-flex align-items-start justify-content-between gap-2 mb-3">
              <div>
                <h5 class="fw-bold text-dark mb-1" id="resDishName">-</h5>
                <div id="resBadgeContainer"></div>
              </div>
              <button id="btnResetScan" type="button" class="btn btn-sm btn-light border rounded-circle" style="width: 32px; height: 32px;" title="Pindai ulang">
                <i class="bi bi-arrow-clockwise"></i>
              </button>
            </div>

            <!-- Alert HITL (< 70%) -->
            <div id="resUncertaintyAlert" class="alert alert-warning border-0 small py-2 px-3 rounded-3 mb-3 d-none" style="background: #FFFBEB; color: #92400E;">
              <i class="bi bi-info-circle me-1"></i>
              <strong>Dalam Peninjauan Admin:</strong>
              <span id="resUncertaintyReason"></span>
            </div>

            <!-- KPI Tiles -->
            <div class="row g-2 mb-3 tabular-nums">
              <div class="col-6">
                <div class="p-3 rounded-3 text-white" style="background: var(--unklab-navy);">
                  <div class="text-white-50" style="font-size: 0.68rem; letter-spacing: 0.06em;">TOTAL ENERGI</div>
                  <div class="fs-3 fw-bold lh-1 mt-1"><span id="resCalories">0</span> <small class="fs-6 fw-normal text-warning">kkal</small></div>
                </div>
              </div>
              <div class="col-6">
                <div class="p-3 rounded-3 border bg-light">
                  <div class="text-muted" style="font-size: 0.68rem; letter-spacing: 0.06em;">BERAT PORSI</div>
                  <div class="fs-3 fw-bold text-dark lh-1 mt-1"><span id="resWeight">0</span> <small class="fs-6 fw-normal text-muted">gram</small></div>
                </div>
              </div>
            </div>

            <!-- Atwater Macro Bar -->
            <div class="mb-3 tabular-nums">
              <div class="d-flex justify-content-between small mb-1">
                <span class="fw-semibold text-dark">Distribusi Makronutrien</span>
                <span class="text-muted" style="font-size: 0.76rem;">Serat: <strong class="text-dark" id="gramFiber">0g</strong></span>
              </div>
              <div class="progress mb-2" style="height: 8px; border-radius: 99px; background: #F1F5F9;">
                <div id="barCarb" class="progress-bar" style="width: 0%; background: var(--macro-carb);"></div>
                <div id="barProtein" class="progress-bar" style="width: 0%; background: var(--macro-protein);"></div>
                <div id="barFat" class="progress-bar" style="width: 0%; background: var(--macro-fat);"></div>
              </div>
              <div class="row g-2 text-center small">
                <div class="col-4">
                  <div class="p-2 rounded-3 badge-macro-carb">
                    <div class="fw-bold" id="pctCarb">0%</div>
                    <div style="font-size: 0.73rem;">Karbo · <span id="gramCarb">0g</span></div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="p-2 rounded-3 badge-macro-protein">
                    <div class="fw-bold" id="pctProtein">0%</div>
                    <div style="font-size: 0.73rem;">Protein · <span id="gramProtein">0g</span></div>
                  </div>
                </div>
                <div class="col-4">
                  <div class="p-2 rounded-3 badge-macro-fat">
                    <div class="fw-bold" id="pctFat">0%</div>
                    <div style="font-size: 0.73rem;">Lemak · <span id="gramFat">0g</span></div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Component Breakdown -->
            <div class="table-responsive mb-2">
              <table class="table table-sm align-middle mb-0 tabular-nums" style="font-size: 0.8rem;">
                <thead class="text-muted">
                  <tr>
                    <th class="fw-semibold border-bottom">Komponen</th>
                    <th class="fw-semibold border-bottom text-end">Porsi</th>
                    <th class="fw-semibold border-bottom text-end">Kalori</th>
                  </tr>
                </thead>
                <tbody id="resComponentsBody"></tbody>
              </table>
            </div>

            <div class="text-muted small pt-2 border-top mt-auto" id="resNotesBox" style="font-size: 0.78rem;">
              <span id="resNotes"></span>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</section>

<!-- MODAL DETAIL MENU HARIAN -->
<div class="modal fade" id="menuDetailModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 rounded-4 shadow">
      <div class="modal-header border-bottom-0 pb-0">
        <div>
          <span class="badge rounded-pill mb-1" id="mdStatusBadge">Tersedia</span>
          <h5 class="modal-title fw-bold text-dark" id="mdName">-</h5>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body pt-2 tabular-nums">
        <p class="text-muted small mb-3" id="mdDesc"></p>

        <div class="d-flex gap-2 mb-3">
          <div class="flex-fill p-2 px-3 rounded-3 text-white" style="background: var(--unklab-navy);">
            <div class="text-white-50" style="font-size: 0.68rem;">ENERGI</div>
            <div class="fw-bold fs-5"><span id="mdCal">0</span> <small class="fw-normal text-warning" style="font-size: 0.75rem;">kkal</small></div>
          </div>
          <div class="flex-fill p-2 px-3 rounded-3 border bg-light">
            <div class="text-muted" style="font-size: 0.68rem;">PORSI</div>
            <div class="fw-bold fs-5 text-dark"><span id="mdWeight">0</span> <small class="fw-normal text-muted" style="font-size: 0.75rem;">gram</small></div>
          </div>
        </div>

        <div class="progress mb-2" style="height: 8px; border-radius: 99px;">
          <div id="mdBarC" class="progress-bar" style="background: var(--macro-carb);"></div>
          <div id="mdBarP" class="progress-bar" style="background: var(--macro-protein);"></div>
          <div id="mdBarF" class="progress-bar" style="background: var(--macro-fat);"></div>
        </div>
        <div class="d-flex justify-content-between small text-muted mb-3" style="font-size: 0.78rem;">
          <span>Karbo <strong class="text-dark" id="mdPctC">0%</strong></span>
          <span>Protein <strong class="text-dark" id="mdPctP">0%</strong></span>
          <span>Lemak <strong class="text-dark" id="mdPctF">0%</strong></span>
        </div>

        <div class="p-3 rounded-3 bg-light border small">
          <div class="fw-semibold text-dark mb-1">Komposisi Bahan</div>
          <div class="text-muted" id="mdIngredients">-</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function openMenuDetail(item) {
  document.getElementById('mdName').textContent = item.name || '-';
  document.getElementById('mdDesc').textContent = item.description || '';
  document.getElementById('mdCal').textContent = Math.round(item.calories || 0);
  document.getElementById('mdWeight').textContent = Math.round(item.portion_weight_g || 0);
  document.getElementById('mdIngredients').textContent = item.ingredients || 'Tidak ada catatan komposisi.';

  const badge = document.getElementById('mdStatusBadge');
  if (parseInt(item.is_available) === 1) {
    badge.className = 'badge rounded-pill badge-approved mb-1';
    badge.textContent = 'Tersedia';
  } else {
    badge.className = 'badge rounded-pill bg-danger-subtle text-danger mb-1';
    badge.textContent = 'Habis';
  }

  document.getElementById('mdBarC').style.width = (item.carbohydrate_pct || 0) + '%';
  document.getElementById('mdBarP').style.width = (item.protein_pct || 0) + '%';
  document.getElementById('mdBarF').style.width = (item.fat_pct || 0) + '%';
  document.getElementById('mdPctC').textContent = (item.carbohydrate_pct || 0) + '%';
  document.getElementById('mdPctP').textContent = (item.protein_pct || 0) + '%';
  document.getElementById('mdPctF').textContent = (item.fat_pct || 0) + '%';

  new bootstrap.Modal(document.getElementById('menuDetailModal')).show();
}

document.addEventListener('DOMContentLoaded', () => {
  const video = document.getElementById('webcamVideo');
  const canvas = document.getElementById('snapshotCanvas');
  const imagePreview = document.getElementById('imagePreview');
  const placeholder = document.getElementById('cameraPlaceholder');
  const reticle = document.getElementById('scanReticle');
  const scanLaser = document.getElementById('scanLaser');
  const loadingOverlay = document.getElementById('scanLoadingOverlay');

  const btnHeroScan = document.getElementById('btnHeroScan');
  const btnPlaceholderStart = document.getElementById('btnPlaceholderStart');
  const btnSwitch = document.getElementById('btnSwitchCamera');
  const btnCapture = document.getElementById('btnCapture');
  const btnReset = document.getElementById('btnResetScan');
  const segCamera = document.getElementById('segCamera');
  const segUpload = document.getElementById('segUpload');
  const fileInput = document.getElementById('fileInput');

  let currentStream = null;
  let facingMode = 'environment';
  const apiEndpoint = '<?php echo APP_PATH; ?>/api/scan.php';
  const confidenceThreshold = <?php echo (float)($data['threshold'] ?? 70.0); ?>;

  async function initCamera() {
    if (currentStream) {
      currentStream.getTracks().forEach(t => t.stop());
    }
    if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
      alert('Browser tidak mendukung kamera WebRTC. Gunakan opsi Unggah Foto.');
      return;
    }
    try {
      currentStream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: facingMode }, width: { ideal: 1280 } },
        audio: false
      });
      video.srcObject = currentStream;
      video.classList.remove('d-none');
      reticle.classList.remove('d-none');
      btnSwitch.classList.remove('d-none');
      imagePreview.classList.add('d-none');
      placeholder.classList.add('d-none');
    } catch (err) {
      alert('Izin kamera belum diberikan atau kamera tidak terdeteksi. Anda dapat mengunggah foto dari galeri.');
    }
  }

  btnHeroScan.addEventListener('click', () => {
    document.getElementById('scannerSection').scrollIntoView({ behavior: 'smooth', block: 'start' });
    initCamera();
  });

  segCamera.addEventListener('click', () => {
    segCamera.classList.add('active');
    segUpload.classList.remove('active');
    initCamera();
  });

  segUpload.addEventListener('click', () => {
    segUpload.classList.add('active');
    segCamera.classList.remove('active');
    fileInput.click();
  });

  btnPlaceholderStart.addEventListener('click', initCamera);

  btnSwitch.addEventListener('click', () => {
    facingMode = (facingMode === 'environment') ? 'user' : 'environment';
    initCamera();
  });

  btnCapture.addEventListener('click', () => {
    if (!currentStream || video.videoWidth === 0) {
      fileInput.click();
      return;
    }
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    const base64Data = canvas.toDataURL('image/jpeg', 0.85);

    imagePreview.src = base64Data;
    imagePreview.classList.remove('d-none');
    video.classList.add('d-none');
    reticle.classList.add('d-none');

    uploadAndAnalyze(base64Data);
  });

  fileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
      imagePreview.src = ev.target.result;
      imagePreview.classList.remove('d-none');
      video.classList.add('d-none');
      placeholder.classList.add('d-none');
      reticle.classList.add('d-none');
      uploadAndAnalyze(ev.target.result);
    };
    reader.readAsDataURL(file);
  });

  function uploadAndAnalyze(base64Data) {
    loadingOverlay.classList.remove('d-none');
    scanLaser.style.display = 'block';

    const formData = new FormData();
    formData.append('image_base64', base64Data);

    fetch(apiEndpoint, {
      method: 'POST',
      body: formData,
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
      loadingOverlay.classList.add('d-none');
      scanLaser.style.display = 'none';
      if (data.success) {
        renderScanResults(data);
      } else {
        alert('Gagal menganalisis: ' + (data.message || 'Terjadi kesalahan.'));
      }
    })
    .catch(() => {
      loadingOverlay.classList.add('d-none');
      scanLaser.style.display = 'none';
      alert('Kendala koneksi saat menghubungi server.');
    });
  }

  function renderScanResults(d) {
    document.getElementById('resultEmptyState').classList.add('d-none');
    document.getElementById('resultContent').classList.remove('d-none');

    document.getElementById('resDishName').textContent = d.dish_name;
    document.getElementById('resCalories').textContent = Math.round(d.total_calories);
    document.getElementById('resWeight').textContent = Math.round(d.total_weight_g);

    const badgeContainer = document.getElementById('resBadgeContainer');
    const alertBox = document.getElementById('resUncertaintyAlert');
    const reasonEl = document.getElementById('resUncertaintyReason');

    if (d.confidence_score >= confidenceThreshold && d.status === 'auto_approved') {
      badgeContainer.innerHTML = `<span class="badge rounded-pill badge-approved px-2 py-1" style="font-size:0.74rem;"><i class="bi bi-check-circle me-1"></i>Terverifikasi AI (${d.confidence_score}%)</span>`;
      alertBox.classList.add('d-none');
    } else {
      badgeContainer.innerHTML = `<span class="badge rounded-pill badge-review px-2 py-1" style="font-size:0.74rem;"><i class="bi bi-clock me-1"></i>Menunggu Verifikasi Admin (${d.confidence_score}%)</span>`;
      alertBox.classList.remove('d-none');
      reasonEl.textContent = d.ai_uncertainty_reason || 'Skor keyakinan AI di bawah 70%. Tiket diteruskan ke antrean kalibrasi Admin.';
    }

    document.getElementById('barCarb').style.width = d.carbohydrate_pct + '%';
    document.getElementById('barProtein').style.width = d.protein_pct + '%';
    document.getElementById('barFat').style.width = d.fat_pct + '%';

    document.getElementById('pctCarb').textContent = d.carbohydrate_pct + '%';
    document.getElementById('pctProtein').textContent = d.protein_pct + '%';
    document.getElementById('pctFat').textContent = d.fat_pct + '%';

    document.getElementById('gramCarb').textContent = d.carbohydrate_g + 'g';
    document.getElementById('gramProtein').textContent = d.protein_g + 'g';
    document.getElementById('gramFat').textContent = d.fat_g + 'g';
    document.getElementById('gramFiber').textContent = d.fiber_g + 'g';

    const tbody = document.getElementById('resComponentsBody');
    tbody.innerHTML = '';
    if (d.food_components && d.food_components.length > 0) {
      d.food_components.forEach(c => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
          <td class="text-dark">${c.name}</td>
          <td class="text-end text-muted">${c.portion_g || 0}g</td>
          <td class="text-end fw-semibold text-dark">${c.calories || 0} kkal</td>
        `;
        tbody.appendChild(tr);
      });
    }

    document.getElementById('resNotes').textContent = d.nutrition_notes || '';
  }

  btnReset.addEventListener('click', () => {
    imagePreview.classList.add('d-none');
    if (currentStream) {
      video.classList.remove('d-none');
      reticle.classList.remove('d-none');
    } else {
      placeholder.classList.remove('d-none');
    }
  });
});
</script>

<?php require_once dirname(__DIR__) . '/template/footer.php'; ?>
