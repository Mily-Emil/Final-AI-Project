<?php
/**
 * RESTful API: /api/admin_review.php
 * AI-Powered Nutrition Analyzer System - Universitas Klabat (UNKLAB)
 * Memproses intervensi Human-in-the-Loop (HITL) Administrator untuk mengoreksi
 * dan mengalibrasi hasil pemindaian bernilai confidence < 70%.
 */

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

require_once dirname(__DIR__, 2) . '/app/init.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek otentikasi role admin
$role = $_SESSION['user-role'] ?? '';
$adminId = $_SESSION['user-id'] ?? null;

if ($role !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Akses ditolak. Hanya Administrator yang memiliki hak kalibrasi HITL.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Metode HTTP tidak diizinkan. Gunakan POST.']);
    exit();
}

$inputData = $_POST;
if (empty($inputData)) {
    $inputData = json_decode(file_get_contents('php://input'), true) ?: [];
}

$scanId = (int)($inputData['scan_id'] ?? 0);
if ($scanId <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID pemindaian tidak valid.']);
    exit();
}

$scanModel = new Nutrition_scan_model();
$existingScan = $scanModel->getById($scanId);
if (!$existingScan) {
    http_response_code(404);
    echo json_encode(['success' => false, 'message' => 'Data pemindaian tidak ditemukan.']);
    exit();
}

$dishName = trim($inputData['dish_name'] ?? $existingScan['dish_name']);
$totalWeight = max(0, (float)($inputData['total_weight_g'] ?? $existingScan['total_weight_g']));
$carbsG = max(0, (float)($inputData['carbohydrate_g'] ?? $existingScan['carbohydrate_g']));
$proteinG = max(0, (float)($inputData['protein_g'] ?? $existingScan['protein_g']));
$fatG = max(0, (float)($inputData['fat_g'] ?? $existingScan['fat_g']));
$fiberG = max(0, (float)($inputData['fiber_g'] ?? $existingScan['fiber_g']));
$adminNotes = trim($inputData['admin_notes'] ?? 'Dikalibrasi oleh Administrator.');

// Hitung faktor Atwater: 1g C=4kkal, 1g P=4kkal, 1g F=9kkal
$cKkal = $carbsG * 4.0;
$pKkal = $proteinG * 4.0;
$fKkal = $fatG * 9.0;
$macroTotal = $cKkal + $pKkal + $fKkal;

if ($macroTotal > 0) {
    $cPct = round(($cKkal / $macroTotal) * 100, 1);
    $pPct = round(($pKkal / $macroTotal) * 100, 1);
    $fPct = round(100.0 - ($cPct + $pPct), 1);
    $totalCalories = round($macroTotal, 1);
} else {
    $cPct = 0.0;
    $pPct = 0.0;
    $fPct = 0.0;
    $totalCalories = 0.0;
}

$foodComponents = $inputData['food_components'] ?? $existingScan['food_components_decoded'];
if (is_string($foodComponents)) {
    $foodComponents = json_decode($foodComponents, true) ?: [];
}

$updatePayload = [
    'dish_name' => $dishName,
    'food_components' => $foodComponents,
    'total_calories' => $totalCalories,
    'total_weight_g' => $totalWeight,
    'carbohydrate_g' => $carbsG,
    'protein_g' => $proteinG,
    'fat_g' => $fatG,
    'fiber_g' => $fiberG,
    'carbohydrate_pct' => $cPct,
    'protein_pct' => $pPct,
    'fat_pct' => $fPct,
    'admin_notes' => $adminNotes,
    'reviewed_by' => $adminId
];

$success = $scanModel->updateAdminCalibration($scanId, $updatePayload);

if ($success) {
    if (class_exists('Aktivitas_model')) {
        $akt = new Aktivitas_model();
        $akt->log($adminId, $_SESSION['user-name'] ?? 'Admin', 'Admin HITL Calibration', "Mengalibrasi hasil pemindaian #$scanId ($dishName)");
    }

    echo json_encode([
        'success' => true,
        'message' => 'Pemindaian berhasil dikalibrasi dan disimpan sebagai Admin Corrected.',
        'data' => array_merge($updatePayload, [
            'scan_id' => $scanId,
            'status' => 'admin_corrected',
            'status_label' => 'Dikalibrasi Admin'
        ])
    ]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Gagal memperbarui data kalibrasi di database.']);
}
exit();
