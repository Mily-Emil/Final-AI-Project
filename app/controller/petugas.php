<?php
/**
 * Controller Portal Petugas (Staff Operations Portal)
 * AI-Powered Nutrition Analyzer System - Universitas Klabat (UNKLAB)
 * 
 * Mengimplementasikan Modul 3.2:
 * 1. Staff Nutrition Checker (Pengecekan Nutrisi Cepat Sebelum Penyajian)
 * 2. Manual Menu Builder (Formulir Terstruktur Input Menu Harian)
 * 3. Publikasi & Ketersediaan Menu (Toggle Tersedia/Habis & Terbit)
 */

class petugas extends Controller {

    public function __construct() {
        parent::__construct();
        $this->start_default_session_app();
        $role = $_SESSION['user-role'] ?? '';
        if ($role !== 'petugas' && $role !== 'admin') {
            $_SESSION['flash_message'] = [
                'type' => 'warning',
                'title' => 'Akses Terbatas',
                'text' => 'Silakan masuk dengan akun Petugas Kantin / Dining Hall untuk mengakses portal ini.'
            ];
            header('Location: ' . APP_PATH . '/login/');
            exit();
        }
    }

    /**
     * Dashboard Operasional Petugas
     */
    public function index() {
        $dailyMenuModel = $this->logic('Daily_menu_model');
        $allMenus = $dailyMenuModel ? $dailyMenuModel->getAllMenus(50) : [];

        $scanModel = $this->logic('Nutrition_scan_model');
        $recentScans = $scanModel ? $scanModel->getRecentScans(8) : [];

        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        $data = [
            'title' => 'Portal Operasional Petugas Kantin - ' . APP_SHORT_NAME,
            'menus' => $allMenus,
            'recent_scans' => $recentScans,
            'active_menu' => 'petugas_dashboard',
            'flash_message' => $flashMessage
        ];

        $this->display('petugas/index', $data);
    }

    /**
     * Staff Nutrition Checker (Pemindai Kamera Uji Coba Kualitas Makanan)
     */
    public function checker() {
        $data = [
            'title' => 'Staff Nutrition Checker - ' . APP_SHORT_NAME,
            'active_menu' => 'petugas_checker',
            'threshold' => defined('AI_CONFIDENCE_THRESHOLD') ? AI_CONFIDENCE_THRESHOLD : 70.0
        ];

        $this->display('petugas/checker', $data);
    }

    /**
     * Manual Menu Builder: Formulir Tambah Menu
     */
    public function tambah_menu() {
        $foodRefModel = $this->logic('Food_reference_model');
        $foodReferences = $foodRefModel ? $foodRefModel->getAll() : [];

        $data = [
            'title' => 'Tambah Menu Harian - Manual Menu Builder',
            'active_menu' => 'petugas_menu',
            'food_references' => $foodReferences,
            'csrf_token' => $this->get_csrf_token()
        ];

        $this->display('petugas/tambah_menu', $data);
    }

    /**
     * Simpan Menu Baru
     */
    public function simpan_menu() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_PATH . '/petugas/');
            exit();
        }

        if (!$this->verify_csrf_token()) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'title' => 'Gagal', 'text' => 'Token CSRF tidak valid.'];
            header('Location: ' . APP_PATH . '/petugas/tambah_menu/');
            exit();
        }

        $imagePath = null;
        if (!empty($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['image']['tmp_name']);
            if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
                $ext = ($mime === 'image/jpeg') ? 'jpg' : (($mime === 'image/png') ? 'png' : 'webp');
                $filename = 'menu_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $target = DIR_UPLOADS_MENUS . '/' . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $imagePath = $filename;
                }
            }
        }

        $menuData = [
            'meal_session' => $_POST['meal_session'] ?? 'makan_siang',
            'menu_date' => !empty($_POST['menu_date']) ? $_POST['menu_date'] : date('Y-m-d'),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'ingredients' => trim($_POST['ingredients'] ?? ''),
            'image_path' => $imagePath,
            'portion_weight_g' => (float)($_POST['portion_weight_g'] ?? 0),
            'carbohydrate_g' => (float)($_POST['carbohydrate_g'] ?? 0),
            'protein_g' => (float)($_POST['protein_g'] ?? 0),
            'fat_g' => (float)($_POST['fat_g'] ?? 0),
            'fiber_g' => (float)($_POST['fiber_g'] ?? 0),
            'calories' => (float)($_POST['calories'] ?? 0),
            'is_available' => isset($_POST['is_available']) ? 1 : 0,
            'is_published' => isset($_POST['is_published']) ? 1 : 0,
            'created_by' => $_SESSION['user-id'] ?? null
        ];

        $dailyMenuModel = $this->logic('Daily_menu_model');
        $id = $dailyMenuModel ? $dailyMenuModel->createMenu($menuData) : false;

        if ($id) {
            if (class_exists('Aktivitas_model')) {
                $akt = new Aktivitas_model();
                $akt->log($_SESSION['user-id'] ?? null, $_SESSION['user-name'] ?? 'Petugas', 'Tambah Menu Harian', "Menambahkan menu baru: " . $menuData['name']);
            }
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Menu Berhasil Diterbitkan',
                'text' => 'Menu "' . htmlspecialchars($menuData['name']) . '" telah berhasil disimpan dan disinkronkan ke papan informasi kampus.'
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'title' => 'Gagal Menyimpan',
                'text' => 'Terjadi kesalahan saat menyimpan menu ke database.'
            ];
        }

        header('Location: ' . APP_PATH . '/petugas/');
        exit();
    }

    /**
     * Edit Menu
     */
    public function edit_menu($id = 0) {
        $dailyMenuModel = $this->logic('Daily_menu_model');
        $menu = $dailyMenuModel ? $dailyMenuModel->getById((int)$id) : null;

        if (!$menu) {
            $this->abort(404, "Menu tidak ditemukan.");
            return;
        }

        $foodRefModel = $this->logic('Food_reference_model');
        $foodReferences = $foodRefModel ? $foodRefModel->getAll() : [];

        $data = [
            'title' => 'Edit Menu: ' . htmlspecialchars($menu['name']),
            'menu' => $menu,
            'food_references' => $foodReferences,
            'active_menu' => 'petugas_menu',
            'csrf_token' => $this->get_csrf_token()
        ];

        $this->display('petugas/edit_menu', $data);
    }

    /**
     * Perbarui Menu yang Ada
     */
    public function update_menu() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_PATH . '/petugas/');
            exit();
        }

        if (!$this->verify_csrf_token()) {
            $_SESSION['flash_message'] = ['type' => 'danger', 'title' => 'Gagal', 'text' => 'Token CSRF tidak valid.'];
            header('Location: ' . APP_PATH . '/petugas/');
            exit();
        }

        $id = (int)($_POST['id'] ?? 0);
        $dailyMenuModel = $this->logic('Daily_menu_model');
        $existing = $dailyMenuModel ? $dailyMenuModel->getById($id) : null;

        if (!$existing) {
            $this->abort(404, "Menu tidak ditemukan.");
            return;
        }

        $imagePath = null;
        if (!empty($_FILES['image']) && is_uploaded_file($_FILES['image']['tmp_name'])) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['image']['tmp_name']);
            if (in_array($mime, ['image/jpeg', 'image/png', 'image/webp'])) {
                $ext = ($mime === 'image/jpeg') ? 'jpg' : (($mime === 'image/png') ? 'png' : 'webp');
                $filename = 'menu_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $target = DIR_UPLOADS_MENUS . '/' . $filename;
                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $imagePath = $filename;
                }
            }
        }

        $menuData = [
            'meal_session' => $_POST['meal_session'] ?? 'makan_siang',
            'menu_date' => !empty($_POST['menu_date']) ? $_POST['menu_date'] : date('Y-m-d'),
            'name' => trim($_POST['name'] ?? ''),
            'description' => trim($_POST['description'] ?? ''),
            'ingredients' => trim($_POST['ingredients'] ?? ''),
            'image_path' => $imagePath,
            'portion_weight_g' => (float)($_POST['portion_weight_g'] ?? 0),
            'carbohydrate_g' => (float)($_POST['carbohydrate_g'] ?? 0),
            'protein_g' => (float)($_POST['protein_g'] ?? 0),
            'fat_g' => (float)($_POST['fat_g'] ?? 0),
            'fiber_g' => (float)($_POST['fiber_g'] ?? 0),
            'calories' => (float)($_POST['calories'] ?? 0),
            'is_available' => isset($_POST['is_available']) ? 1 : 0,
            'is_published' => isset($_POST['is_published']) ? 1 : 0
        ];

        $success = $dailyMenuModel->updateMenu($id, $menuData);

        if ($success) {
            $_SESSION['flash_message'] = [
                'type' => 'success',
                'title' => 'Menu Diperbarui',
                'text' => 'Perubahan data menu "' . htmlspecialchars($menuData['name']) . '" berhasil disimpan.'
            ];
        } else {
            $_SESSION['flash_message'] = [
                'type' => 'danger',
                'title' => 'Gagal Memperbarui',
                'text' => 'Terjadi kendala saat menyimpan perubahan menu.'
            ];
        }

        header('Location: ' . APP_PATH . '/petugas/');
        exit();
    }

    /**
     * Toggle Ketersediaan Menu (Satu Klik: Tersedia / Habis)
     */
    public function toggle_availability($id = 0) {
        $dailyMenuModel = $this->logic('Daily_menu_model');
        $success = $dailyMenuModel ? $dailyMenuModel->toggleAvailability((int)$id) : false;

        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest');
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => (bool)$success]);
            exit();
        }

        $_SESSION['flash_message'] = [
            'type' => 'info',
            'title' => 'Status Diperbarui',
            'text' => 'Status ketersediaan menu berhasil diubah dan disinkronkan ke publik.'
        ];
        header('Location: ' . APP_PATH . '/petugas/');
        exit();
    }

    /**
     * Toggle Status Publikasi Menu
     */
    public function toggle_published($id = 0) {
        $dailyMenuModel = $this->logic('Daily_menu_model');
        $success = $dailyMenuModel ? $dailyMenuModel->togglePublished((int)$id) : false;

        $_SESSION['flash_message'] = [
            'type' => 'info',
            'title' => 'Status Publikasi Diperbarui',
            'text' => 'Status penayangan menu di papan informasi publik berhasil diubah.'
        ];
        header('Location: ' . APP_PATH . '/petugas/');
        exit();
    }

    /**
     * Hapus Menu
     */
    public function hapus_menu($id = 0) {
        $dailyMenuModel = $this->logic('Daily_menu_model');
        $dailyMenuModel->deleteMenu((int)$id);

        $_SESSION['flash_message'] = [
            'type' => 'success',
            'title' => 'Menu Dihapus',
            'text' => 'Menu berhasil dihapus dari sistem.'
        ];
        header('Location: ' . APP_PATH . '/petugas/');
        exit();
    }
}
