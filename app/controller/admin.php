<?php
/**
 * Controller Administrator (Control Center & AI Calibration)
 * AI-Powered Nutrition Analyzer System - Universitas Klabat (UNKLAB)
 * 
 * Mengimplementasikan Modul 3.3:
 * 1. Executive Operations Dashboard (Metrik Scan, Rata-rata Kalori, Akurasi AI, Antrean HITL)
 * 2. Antrean Intervensi AI (< 70% Confidence Queue)
 * 3. Alat Koreksi & Kalibrasi Nutrisi (Simpan sebagai Admin Corrected)
 * 4. Master Food & Nutrition Reference (TKPI & Khas Sulut)
 * 5. Staff Account Management (Manajemen Akun Petugas & Admin)
 */

class admin extends Controller {

    public function __construct() {
        parent::__construct();
        $this->start_default_session_app();
        $role = $_SESSION['user-role'] ?? '';
        if ($role !== 'admin') {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'title' => 'Akses Dibatasi',
                'text' => 'Modul ini hanya dapat dibuka oleh Super Administrator UNKLAB.'
            ];
            header('Location: ' . APP_PATH . '/login/');
            exit();
        }
    }

    /**
     * Executive Operations Dashboard
     */
    public function index() {
        $scanModel = $this->logic('Nutrition_scan_model');
        $stats = $scanModel ? $scanModel->getStats() : [];
        $pendingReviews = $scanModel ? $scanModel->getPendingReviews(10) : [];
        $recentScans = $scanModel ? $scanModel->getRecentScans(10) : [];

        $userModel = $this->logic('User_model');
        $staffCount = $userModel ? $userModel->get_user_count() : 0;

        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        $data = [
            'title' => 'Executive Operations Dashboard - ' . APP_SHORT_NAME,
            'stats' => $stats,
            'pending_reviews' => $pendingReviews,
            'recent_scans' => $recentScans,
            'staff_count' => $staffCount,
            'active_menu' => 'admin_dashboard',
            'flash_message' => $flashMessage,
            'threshold' => defined('AI_CONFIDENCE_THRESHOLD') ? AI_CONFIDENCE_THRESHOLD : 70.0
        ];

        $this->display('admin/index', $data);
    }

    /**
     * Antrean Intervensi AI (< 70% Confidence Review Queue)
     */
    public function queue() {
        $scanModel = $this->logic('Nutrition_scan_model');
        $pendingReviews = $scanModel ? $scanModel->getPendingReviews(100) : [];

        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        $data = [
            'title' => 'Antrean Intervensi AI (< 70% Confidence) - Human-in-the-Loop',
            'pending_reviews' => $pendingReviews,
            'active_menu' => 'admin_queue',
            'flash_message' => $flashMessage,
            'threshold' => defined('AI_CONFIDENCE_THRESHOLD') ? AI_CONFIDENCE_THRESHOLD : 70.0
        ];

        $this->display('admin/queue', $data);
    }

    /**
     * Alat Koreksi & Kalibrasi Nutrisi (Halaman Kalibrasi)
     */
    public function calibrate($id = 0) {
        $scanId = (int)$id;
        $scanModel = $this->logic('Nutrition_scan_model');
        $scan = $scanModel ? $scanModel->getById($scanId) : null;

        if (!$scan) {
            $this->abort(404, "Data pemindaian tidak ditemukan.");
            return;
        }

        $foodRefModel = $this->logic('Food_reference_model');
        $foodReferences = $foodRefModel ? $foodRefModel->getAll() : [];

        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        $data = [
            'title' => 'Kalibrasi Nutrisi: ' . htmlspecialchars($scan['dish_name']) . ' - HITL',
            'scan' => $scan,
            'food_references' => $foodReferences,
            'active_menu' => 'admin_queue',
            'csrf_token' => $this->get_csrf_token(),
            'flash_message' => $flashMessage
        ];

        $this->display('admin/calibrate', $data);
    }

    /**
     * Simpan Hasil Kalibrasi Administrator
     */
    public function save_calibration() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_PATH . '/admin/queue/');
            exit();
        }

        if (!$this->verify_csrf_token()) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'title' => 'Gagal', 'text' => 'Token CSRF tidak valid.'];
            header('Location: ' . APP_PATH . '/admin/queue/');
            exit();
        }

        $scanId = (int)($_POST['scan_id'] ?? 0);
        $scanModel = $this->logic('Nutrition_scan_model');
        $existing = $scanModel ? $scanModel->getById($scanId) : null;

        if (!$existing) {
            $this->abort(404, "Data pemindaian tidak ditemukan.");
            return;
        }

        $dishName = trim($_POST['dish_name'] ?? $existing['dish_name']);
        $totalWeight = max(0, (float)($_POST['total_weight_g'] ?? 0));
        $carbsG = max(0, (float)($_POST['carbohydrate_g'] ?? 0));
        $proteinG = max(0, (float)($_POST['protein_g'] ?? 0));
        $fatG = max(0, (float)($_POST['fat_g'] ?? 0));
        $fiberG = max(0, (float)($_POST['fiber_g'] ?? 0));
        $adminNotes = trim($_POST['admin_notes'] ?? 'Dikalibrasi dan diverifikasi oleh Administrator.');

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

        // Parse komponen jika diisi
        $rawComponents = $_POST['food_components_json'] ?? '';
        $components = !empty($rawComponents) ? json_decode($rawComponents, true) : $existing['food_components_decoded'];

        $updatePayload = [
            'dish_name' => $dishName,
            'food_components' => $components ?: [],
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
            'reviewed_by' => $_SESSION['user-id'] ?? null
        ];

        $success = $scanModel->updateAdminCalibration($scanId, $updatePayload);

        if ($success) {
            if (class_exists('Aktivitas_model')) {
                $akt = new Aktivitas_model();
                $akt->log($_SESSION['user-id'] ?? null, $_SESSION['user-name'] ?? 'Admin', 'Admin Calibration', "Sukses mengalibrasi scan #$scanId menjadi Admin Corrected.");
            }

            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Kalibrasi Berhasil Disimpan',
                'text' => 'Hasil pemindaian #' . $scanId . ' telah berhasil dikoreksi dan berstatus "Admin Corrected".'
            ];
            header('Location: ' . APP_PATH . '/admin/queue/');
            exit();
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'title' => 'Gagal Kalibrasi',
                'text' => 'Terjadi kendala saat menyimpan kalibrasi ke database.'
            ];
            header('Location: ' . APP_PATH . '/admin/calibrate/' . $scanId);
            exit();
        }
    }

    /**
     * Master Data Referensi Gizi Pangan (TKPI)
     */
    public function pangan() {
        $foodRefModel = $this->logic('Food_reference_model');
        $search = $_GET['q'] ?? null;
        $category = $_GET['cat'] ?? null;
        $references = $foodRefModel ? $foodRefModel->getAll($search, $category) : [];
        $categories = $foodRefModel ? $foodRefModel->getCategories() : [];

        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        $data = [
            'title' => 'Master Data Referensi Pangan (TKPI) - ' . APP_SHORT_NAME,
            'references' => $references,
            'categories' => $categories,
            'search' => $search,
            'selected_category' => $category,
            'active_menu' => 'admin_pangan',
            'csrf_token' => $this->get_csrf_token(),
            'flash_message' => $flashMessage
        ];

        $this->display('admin/pangan', $data);
    }

    /**
     * Tambah Referensi Pangan
     */
    public function tambah_pangan() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_PATH . '/admin/pangan/');
            exit();
        }

        if (!$this->verify_csrf_token()) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'title' => 'Gagal', 'text' => 'Token CSRF tidak valid.'];
            header('Location: ' . APP_PATH . '/admin/pangan/');
            exit();
        }

        $foodData = [
            'food_name' => trim($_POST['food_name'] ?? ''),
            'category' => trim($_POST['category'] ?? 'Makanan Pokok'),
            'serving_size_g' => (float)($_POST['serving_size_g'] ?? 100),
            'calories' => (float)($_POST['calories'] ?? 0),
            'carbohydrate_g' => (float)($_POST['carbohydrate_g'] ?? 0),
            'protein_g' => (float)($_POST['protein_g'] ?? 0),
            'fat_g' => (float)($_POST['fat_g'] ?? 0),
            'fiber_g' => (float)($_POST['fiber_g'] ?? 0),
            'notes' => trim($_POST['notes'] ?? '')
        ];

        $foodRefModel = $this->logic('Food_reference_model');
        $id = $foodRefModel ? $foodRefModel->create($foodData) : false;

        if ($id) {
            $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Berhasil', 'text' => 'Data referensi pangan "' . htmlspecialchars($foodData['food_name']) . '" berhasil ditambahkan.'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'title' => 'Gagal', 'text' => 'Gagal menyimpan data pangan.'];
        }

        header('Location: ' . APP_PATH . '/admin/pangan/');
        exit();
    }

    /**
     * Hapus Referensi Pangan
     */
    public function hapus_pangan($id = 0) {
        $foodRefModel = $this->logic('Food_reference_model');
        $foodRefModel->delete((int)$id);

        $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Dihapus', 'text' => 'Item referensi pangan berhasil dihapus.'];
        header('Location: ' . APP_PATH . '/admin/pangan/');
        exit();
    }

    /**
     * Manajemen Akun Petugas & Administrator
     */
    public function staff() {
        $userModel = $this->logic('User_model');
        $staffList = $userModel ? $userModel->get_all_staff() : [];

        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        $data = [
            'title' => 'Manajemen Akun Staf & Petugas - ' . APP_SHORT_NAME,
            'staff_list' => $staffList,
            'active_menu' => 'admin_staff',
            'csrf_token' => $this->get_csrf_token(),
            'flash_message' => $flashMessage
        ];

        $this->display('admin/staff', $data);
    }

    /**
     * Tambah Akun Staf Baru
     */
    public function tambah_staff() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_PATH . '/admin/staff/');
            exit();
        }

        if (!$this->verify_csrf_token()) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'title' => 'Gagal', 'text' => 'Token CSRF tidak valid.'];
            header('Location: ' . APP_PATH . '/admin/staff/');
            exit();
        }

        $userData = [
            'username' => trim($_POST['username'] ?? ''),
            'password' => $_POST['password'] ?? 'petugas123',
            'nama_lengkap' => trim($_POST['nama_lengkap'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'role' => in_array($_POST['role'], ['admin', 'petugas']) ? $_POST['role'] : 'petugas',
            'status' => 'active'
        ];

        $userModel = $this->logic('User_model');
        $created = $userModel ? $userModel->create_user($userData) : false;

        if ($created) {
            $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Akun Dibuat', 'text' => 'Akun baru untuk ' . htmlspecialchars($userData['nama_lengkap']) . ' berhasil didaftarkan.'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'title' => 'Gagal', 'text' => 'Gagal membuat akun staf. Pastikan username dan email unik.'];
        }

        header('Location: ' . APP_PATH . '/admin/staff/');
        exit();
    }

    /**
     * Hapus Akun Staf
     */
    public function hapus_staff($id = 0) {
        $userModel = $this->logic('User_model');
        $success = $userModel ? $userModel->delete_user((int)$id) : false;

        if ($success) {
            $_SESSION['flash_message'] = ['type' => 'success', 'title' => 'Akun Dihapus', 'text' => 'Akun pengguna berhasil dihapus.'];
        } else {
            $_SESSION['flash_message'] = ['type' => 'danger', 'title' => 'Gagal', 'text' => 'Tidak dapat menghapus akun admin utama.'];
        }

        header('Location: ' . APP_PATH . '/admin/staff/');
        exit();
    }
}
