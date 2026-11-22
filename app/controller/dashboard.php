<?php
/**
 * Controller Dashboard - AI-Powered Nutrition Analyzer System
 * Pengarah cerdas berdasarkan peran pengguna (Admin / Petugas)
 */

class dashboard extends Controller {

    public function index() {
        $this->start_default_session_app();

        if (empty($_SESSION['user-id'])) {
            header('Location: ' . APP_PATH . '/login/');
            exit();
        }

        $role = $_SESSION['user-role'] ?? '';
        if ($role === 'admin') {
            header('Location: ' . APP_PATH . '/admin/');
            exit();
        } else {
            header('Location: ' . APP_PATH . '/petugas/');
            exit();
        }
    }
}
