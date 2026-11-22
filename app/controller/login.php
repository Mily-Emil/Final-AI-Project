<?php
/**
 * Controller Autentikasi (Login / Logout)
 * AI-Powered Nutrition Analyzer System - Universitas Klabat (UNKLAB)
 * Role: admin (Administrator Sistem) & petugas (Staf Kantin/Dining Hall)
 */

class login extends Controller {

    public function index() {
        $this->start_default_session_app();

        // Jika sudah login, redirect sesuai perannya
        if (!empty($_SESSION['user-id'])) {
            $role = $_SESSION['user-role'] ?? '';
            if ($role === 'admin') {
                header('Location: ' . APP_PATH . '/admin/');
                exit();
            } else {
                header('Location: ' . APP_PATH . '/petugas/');
                exit();
            }
        }

        $error = $_SESSION['login_error'] ?? null;
        unset($_SESSION['login_error']);

        $data = [
            'title' => 'Login Petugas & Administrator - ' . APP_SHORT_NAME,
            'app_name' => APP_NAME,
            'app_institution' => APP_INSTITUTION,
            'error' => $error,
            'csrf_token' => $this->get_csrf_token()
        ];

        $this->display('login/index', $data);
    }

    public function authenticate() {
        $this->start_default_session_app();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ' . APP_PATH . '/login/');
            exit();
        }

        // Validasi CSRF Token
        if (!$this->verify_csrf_token()) {
            $_SESSION['login_error'] = 'Token sesi tidak valid atau telah kedaluwarsa. Silakan coba lagi.';
            header('Location: ' . APP_PATH . '/login/');
            exit();
        }

        $username = trim($_POST['username'] ?? '');
        $password = trim($_POST['password'] ?? '');

        if (empty($username) || empty($password)) {
            $_SESSION['login_error'] = 'Username dan password wajib diisi.';
            header('Location: ' . APP_PATH . '/login/');
            exit();
        }

        // Periksa status database
        $db = new Database();
        if (!$db->isConnected()) {
            $_SESSION['login_error'] = 'Koneksi database belum terhubung. Pastikan MySQL di XAMPP berstatus RUNNING.';
            header('Location: ' . APP_PATH . '/login/');
            exit();
        }

        $userModel = $this->logic('User_model');
        $user = $userModel ? $userModel->validate_credentials($username, $password) : false;

        if ($user) {
            // Regenerate session id untuk keamanan (anti session fixation)
            session_regenerate_id(true);

            $_SESSION['user-id'] = $user['id'];
            $_SESSION['user-name'] = $user['nama_lengkap'];
            $_SESSION['user-username'] = $user['username'];
            $_SESSION['user-role'] = $user['role'];
            $_SESSION['user-email'] = $user['email'];
            $_SESSION['user-avatar'] = $user['avatar'] ?? 'default-avatar.png';

            if ($user['role'] === 'admin') {
                header('Location: ' . APP_PATH . '/admin/');
            } else {
                header('Location: ' . APP_PATH . '/petugas/');
            }
            exit();
        } else {
            $_SESSION['login_error'] = 'Username atau password yang Anda masukkan salah.';
            header('Location: ' . APP_PATH . '/login/');
            exit();
        }
    }

    public function logout() {
        $this->start_default_session_app();

        if (!empty($_SESSION['user-id'])) {
            $aktModel = $this->logic('Aktivitas_model');
            if ($aktModel) {
                $aktModel->log($_SESSION['user-id'], $_SESSION['user-username'] ?? 'user', 'User Logout', 'Pengguna keluar dari sistem');
            }
        }

        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();

        header('Location: ' . APP_PATH . '/login/');
        exit();
    }
}
