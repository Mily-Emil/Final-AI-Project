<?php require_once dirname(__DIR__) . '/template/header.php'; ?>

<div class="container py-4">

  <!-- HEADER -->
  <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
    <div>
      <span class="badge px-3 py-1 rounded-pill mb-1" style="background: var(--unklab-navy); color: var(--unklab-gold); font-weight: 700;">
        <i class="bi bi-patch-check me-1"></i> KONTROL MUTU HIDANGAN
      </span>
      <h2 class="fw-bold mb-1" style="color: var(--unklab-navy);">
        Staff Nutrition Checker
      </h2>
      <p class="text-muted small mb-0">
        Uji coba dan periksa estimasi kandungan gizi sampel hidangan sebelum disajikan di dining hall / kantin.
      </p>
    </div>
    <a href="<?php echo APP_PATH; ?>/petugas/" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
      <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
    </a>
  </div>

  <div class="row g-4 justify-content-center">
    
    <!-- KOLOM KAMERA STAF -->
    <div class="col-lg-6 col-md-8">
      <div class="card card-unklab p-3 p-md-4">
        
        <div class="d-flex align-items-center justify-content-between mb-3">
          <span class="fw-bold text-dark"><i class="bi bi-camera me-1 text-primary"></i> Kamera Pengujian Sampel</span>
          <button id="btnSwitchCamera" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            <i class="bi bi-arrow-repeat me-1"></i> Putar Kamera
          </button>
        </div>

        <div class="camera-container mb-3" id="cameraViewport">
          <video id="webcamVideo" autoplay playsinline muted></video>
          <canvas id="snapshotCanvas" class="d-none"></canvas>
          <img id="imagePreview" class="d-none" alt="Preview Makanan">
          
          <div class="scan-reticle"></div>
          <div class="scan-laser" id="scanLaser"></div>

          <div id="scanLoadingOverlay" class="position-absolute w-100 h-100 top-0 start-0 d-flex flex-column align-items-center justify-content-center bg-dark bg-opacity-75 text-white d-none" style="backdrop-filter: blur(4px); z-index: 10;">
            <div class="spinner-grow text-warning mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
            <h6 class="fw-bold mb-1">Menganalisis Sampel Hidangan...</h6>
            <small class="text-white-50">Menghitung takaran gizi & kalori Atwater</small>
          </div>
        </div>

        <div class="d-grid gap-2">
          <button id="btnCapture" class="btn btn-unklab-gold btn-lg py-3 rounded-pill d-flex align-items-center justify-content-center gap-2">
            <i class="bi bi-camera-fill fs-5"></i>
            <span class="fs-6">Ambil Foto & Uji Gizi Sampel</span>
          </button>

          <label for="fileInput" class="btn btn-sm btn-outline-dark rounded-pill py-2 text-center mt-2 cursor-pointer">
            <i class="bi bi-upload me-1"></i> Atau Unggah Foto Sampel
          </label>
          <input type="file" id="fileInput" accept="image/jpeg,image/png,image/webp" class="d-none">
        </div>

      </div>
    </div>

    <!-- HASIL UJI MUTU -->
    <div class="col-lg-6 col-md-8">
      <div id="checkerResultCard" class="card card-unklab p-3 p-md-4">
        
        <div class="border-bottom pb-3 mb-3">
          <span class="text-muted small text-uppercase fw-bold" style="font-size: 0.72rem;">Hasil Pengujian Mutu</span>
          <h4 class="fw-bold mb-1" id="resDishName" style="color: var(--unklab-navy);">Siap Menguji Sampel</h4>
          <div id="resBadgeContainer">
            <span class="badge bg-secondary px-2 py-1">Menunggu Pemindaian</span>
          </div>
        </div>

        <div class="row g-2 mb-3 text-center">
          <div class="col-6">
            <div class="p-3 rounded-3" style="background: var(--unklab-navy); color: #fff;">
              <small class="text-white-50 text-uppercase fw-semibold" style="font-size: 0.7rem;">Estimasi Energi</small>
              <div class="display-6 fw-bold lh-1 my-1" id="resCalories">0</div>
              <small class="text-warning fw-bold">kkal</small>
            </div>
          </div>
          <div class="col-6">
            <div class="p-3 rounded-3 border bg-light">
              <small class="text-muted text-uppercase fw-semibold" style="font-size: 0.7rem;">Berat Porsi</small>
              <div class="display-6 fw-bold lh-1 my-1 text-dark" id="resWeight">0</div>
              <small class="text-secondary fw-semibold">gram</small>
            </div>
          </div>
        </div>

        <div class="border rounded-3 p-3 mb-3 bg-white">
          <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="small fw-bold text-dark">Distribusi Makronutrien</span>
            <span class="badge bg-light text-muted border">Atwater 100%</span>
          </div>

          <div class="progress mb-3" style="height: 12px; border-radius: 6px;">
            <div id="barCarb" class="progress-bar" style="width: 0%; background-color: var(--macro-carb);"></div>
            <div id="barProtein" class="progress-bar" style="width: 0%; background-color: var(--macro-protein);"></div>
            <div id="barFat" class="progress-bar" style="width: 0%; background-color: var(--macro-fat);"></div>
          </div>

          <div class="row g-2 text-center small">
            <div class="col-4">
              <div class="p-2 rounded-2 border badge-macro-carb">
                <div class="fw-bold" id="pctCarb">0%</div>
                <div class="text-truncate fw-semibold">Karbohidrat</div>
                <div class="text-muted" style="font-size: 0.72rem;" id="gramCarb">0g</div>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 rounded-2 border badge-macro-protein">
                <div class="fw-bold" id="pctProtein">0%</div>
                <div class="text-truncate fw-semibold">Protein</div>
                <div class="text-muted" style="font-size: 0.72rem;" id="gramProtein">0g</div>
              </div>
            </div>
            <div class="col-4">
              <div class="p-2 rounded-2 border badge-macro-fat">
                <div class="fw-bold" id="pctFat">0%</div>
                <div class="text-truncate fw-semibold">Lemak</div>
                <div class="text-muted" style="font-size: 0.72rem;" id="gramFat">0g</div>
              </div>
            </div>
          </div>
        </div>

        <div class="p-3 bg-light rounded-3 border small text-muted">
          <i class="bi bi-info-circle me-1 text-primary"></i>
          Gunakan hasil ini sebagai panduan takaran gizi sebelum mencatatkan menu ke papan menu harian.
        </div>

      </div>
    </div>

  </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const video = document.getElementById('webcamVideo');
  const canvas = document.getElementById('snapshotCanvas');
  const imagePreview = document.getElementById('imagePreview');
  const btnCapture = document.getElementById('btnCapture');
  const btnSwitch = document.getElementById('btnSwitchCamera');
  const fileInput = document.getElementById('fileInput');
  const loadingOverlay = document.getElementById('scanLoadingOverlay');
  const scanLaser = document.getElementById('scanLaser');

  let currentStream = null;
  let facingMode = 'environment';
  const apiEndpoint = '<?php echo APP_PATH; ?>/api/scan.php';

  async function initCamera() {
    if (currentStream) {
      currentStream.getTracks().forEach(t => t.stop());
    }
    try {
      currentStream = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: { ideal: facingMode }, width: { ideal: 1280 } },
        audio: false
      });
      video.srcObject = currentStream;
      video.classList.remove('d-none');
      imagePreview.classList.add('d-none');
    } catch (e) {
      console.warn("Camera init error:", e);
    }
  }

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

    uploadAndProcess(base64Data);
  });

  fileInput.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = (ev) => {
      imagePreview.src = ev.target.result;
      imagePreview.classList.remove('d-none');
      video.classList.add('d-none');
      uploadAndProcess(ev.target.result);
    };
    reader.readAsDataURL(file);
  });

  function uploadAndProcess(base64) {
    loadingOverlay.classList.remove('d-none');
    scanLaser.style.display = 'block';

    const formData = new FormData();
    formData.append('image_base64', base64);

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
        document.getElementById('resDishName').textContent = data.dish_name;
        document.getElementById('resCalories').textContent = Math.round(data.total_calories);
        document.getElementById('resWeight').textContent = Math.round(data.total_weight_g);

        const badgeBox = document.getElementById('resBadgeContainer');
        if (data.confidence_score >= 70.0) {
          badgeBox.innerHTML = `<span class="badge badge-approved px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i> Terverifikasi AI (${data.confidence_score}%)</span>`;
        } else {
          badgeBox.innerHTML = `<span class="badge badge-review px-2 py-1"><i class="bi bi-clock-history me-1"></i> Perlu Kalibrasi Admin (${data.confidence_score}%)</span>`;
        }

        document.getElementById('barCarb').style.width = data.carbohydrate_pct + '%';
        document.getElementById('barProtein').style.width = data.protein_pct + '%';
        document.getElementById('barFat').style.width = data.fat_pct + '%';

        document.getElementById('pctCarb').textContent = data.carbohydrate_pct + '%';
        document.getElementById('pctProtein').textContent = data.protein_pct + '%';
        document.getElementById('pctFat').textContent = data.fat_pct + '%';

        document.getElementById('gramCarb').textContent = data.carbohydrate_g + 'g';
        document.getElementById('gramProtein').textContent = data.protein_g + 'g';
        document.getElementById('gramFat').textContent = data.fat_g + 'g';
      } else {
        alert('Gagal menganalisis: ' + (data.message || 'Error'));
      }
    })
    .catch(err => {
      loadingOverlay.classList.add('d-none');
      scanLaser.style.display = 'none';
      alert('Kendala jaringan saat menghubungi server gizi.');
    });
  }

  initCamera();
});
</script>

<?php require_once dirname(__DIR__) . '/template/footer.php'; ?>
