<?php
/**
 * RESTful API: /api/scan.php
 * AI-Powered Nutrition Analyzer System - Universitas Klabat (UNKLAB)
 * Menerima tangkapan kamera smartphone (Base64 atau Multipart),
 * memvalidasi MIME/ukuran, menganalisis via Gemini Vision API (atau Local Fallback),
 * dan menyimpan ke tabel nutrition_scans.
 */

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

require_once dirname(__DIR__, 2) . '/app/init.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode HTTP tidak diizinkan. Gunakan POST.']);
    exit();
}

// Periksa session user jika ada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$userId = $_SESSION['user-id'] ?? null;

$uploadDir = DIR_UPLOADS_SCANS;
if (!file_exists($uploadDir)) {
    @mkdir($uploadDir, 0755, true);
}

$allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
$maxSizeBytes = 5 * 1024 * 1024; // Maksimal 5 MB
$savedFileName = '';
$savedAbsolutePath = '';
$detectedMime = '';

// 1. Cek unggahan berkas Multipart ($_FILES)
if (!empty($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
    $fileError = $_FILES['image']['error'];
    $fileSize  = $_FILES['image']['size'];
    $tmpName   = $_FILES['image']['tmp_name'];

    if ($fileError !== UPLOAD_ERR_OK) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Terjadi kesalahan saat mengunggah foto.']);
        exit();
    }

    if ($fileSize > $maxSizeBytes) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ukuran berkas melebihi batas maksimal 5 MB.']);
        exit();
    }

    // Deteksi MIME type sesungguhnya
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $detectedMime = $finfo->file($tmpName);

    if (!in_array($detectedMime, $allowedMimes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Format gambar tidak diizinkan. Hanya JPEG, PNG, dan WebP yang didukung.']);
        exit();
    }

    $extMap = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $ext = $extMap[$detectedMime] ?? 'jpg';
    $savedFileName = 'scan_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $savedAbsolutePath = $uploadDir . '/' . $savedFileName;

    if (!move_uploaded_file($tmpName, $savedAbsolutePath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan berkas gambar ke server.']);
        exit();
    }
} 
// 2. Cek unggahan Base64 dari WebRTC Camera Smartphone
else {
    $rawBase64 = $_POST['image_base64'] ?? '';
    if (empty($rawBase64)) {
        // Coba baca dari php://input (JSON body)
        $inputJson = json_decode(file_get_contents('php://input'), true);
        $rawBase64 = $inputJson['image_base64'] ?? '';
    }

    if (empty($rawBase64)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Tidak ada gambar yang dikirimkan. Harap ambil foto melalui kamera atau pilih berkas.']);
        exit();
    }

    // Parse data URI format (e.g., data:image/jpeg;base64,.....)
    $ext = 'jpg';
    $detectedMime = 'image/jpeg';
    if (preg_match('/^data:(image\/(jpeg|png|webp));base64,(.+)$/s', $rawBase64, $matches)) {
        $detectedMime = $matches[1];
        $ext = ($matches[2] === 'jpeg') ? 'jpg' : $matches[2];
        $binaryData = base64_decode($matches[3]);
    } else {
        $binaryData = base64_decode($rawBase64);
    }

    if (!$binaryData) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Data gambar base64 tidak valid.']);
        exit();
    }

    if (strlen($binaryData) > $maxSizeBytes) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Ukuran data gambar melebihi batas 5 MB.']);
        exit();
    }

    $savedFileName = 'scan_' . date('Ymd_His') . '_' . bin2hex(random_bytes(6)) . '.' . $ext;
    $savedAbsolutePath = $uploadDir . '/' . $savedFileName;

    if (file_put_contents($savedAbsolutePath, $binaryData) === false) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Gagal menyimpan tangkapan kamera ke server.']);
        exit();
    }
}

// 3. Jalankan Analisis Multimodal Vision AI & Fallback
$geminiService = new GeminiService();
$analysis = $geminiService->analyzeFoodImage($savedAbsolutePath, $detectedMime);

if (!$analysis['success']) {
    http_response_code(500);
    echo json_encode($analysis);
    exit();
}

// 4. Simpan ke Basis Data (nutrition_scans)
$scanModel = new Nutrition_scan_model();
$scanRecord = [
    'user_id' => $userId,
    'image_path' => $savedFileName,
    'dish_name' => $analysis['dish_name'],
    'food_components' => $analysis['food_components'],
    'total_calories' => $analysis['total_calories'],
    'total_weight_g' => $analysis['total_weight_g'],
    'carbohydrate_g' => $analysis['carbohydrate_g'],
    'protein_g' => $analysis['protein_g'],
    'fat_g' => $analysis['fat_g'],
    'fiber_g' => $analysis['fiber_g'],
    'carbohydrate_pct' => $analysis['carbohydrate_pct'],
    'protein_pct' => $analysis['protein_pct'],
    'fat_pct' => $analysis['fat_pct'],
    'confidence_score' => $analysis['confidence_score'],
    'status' => $analysis['status'],
    'ai_uncertainty_reason' => $analysis['ai_uncertainty_reason']
];

$scanId = $scanModel->createScan($scanRecord);

// 5. Kembalikan Respons JSON Terstandar
$imageUrl = APP_PATH . '/public/uploads/scans/' . $savedFileName;

echo json_encode([
    'success' => true,
    'scan_id' => $scanId,
    'engine' => $analysis['engine'],
    'dish_name' => $analysis['dish_name'],
    'food_components' => $analysis['food_components'],
    'total_calories' => $analysis['total_calories'],
    'total_weight_g' => $analysis['total_weight_g'],
    'carbohydrate_g' => $analysis['carbohydrate_g'],
    'protein_g' => $analysis['protein_g'],
    'fat_g' => $analysis['fat_g'],
    'fiber_g' => $analysis['fiber_g'],
    'carbohydrate_pct' => $analysis['carbohydrate_pct'],
    'protein_pct' => $analysis['protein_pct'],
    'fat_pct' => $analysis['fat_pct'],
    'confidence_score' => $analysis['confidence_score'],
    'status' => $analysis['status'],
    'ai_uncertainty_reason' => $analysis['ai_uncertainty_reason'],
    'nutrition_notes' => $analysis['nutrition_notes'],
    'image_url' => $imageUrl,
    'status_label' => ($analysis['status'] === 'auto_approved') ? 'Terverifikasi AI' : 'Menunggu Verifikasi Admin (HITL)'
]);
exit();
