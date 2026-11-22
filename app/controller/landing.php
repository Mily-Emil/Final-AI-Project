<?php
/**
 * Controller Utama (Landing Page / Pemindai Publik)
 * AI-Powered Nutrition Analyzer System - Universitas Klabat (UNKLAB)
 * 
 * Mengimplementasikan Modul 3.1 & 2.1:
 * - Zero-Friction Mobile-First Camera Food Scanner (WebRTC tanpa login)
 * - Kalkulasi Persentase Makronutrien & Kalori Instan
 * - Indikator Status Verifikasi AI (Ambang Batas 70% HITL)
 * - Papan Menu Harian UNKLAB (Sarapan, Makan Siang, Makan Malam)
 */

class landing extends Controller {

    public function index() {
        // Diagnostik Database & Lingkungan
        $db = new Database();
        $dbDiagnostics = $db->getDiagnostics();

        // Ambil Menu Harian UNKLAB & Scan hanya jika skema database sudah siap
        $todayMenus = ['sarapan' => [], 'makan_siang' => [], 'makan_malam' => []];
        $recentScans = [];
        $stats = [];

        if (!empty($dbDiagnostics['is_connected']) && !empty($dbDiagnostics['is_schema_ready'])) {
            try {
                $dailyMenuModel = $this->logic('Daily_menu_model');
                $todayMenus = $dailyMenuModel ? $dailyMenuModel->getPublishedToday() : $todayMenus;

                $scanModel = $this->logic('Nutrition_scan_model');
                $recentScans = $scanModel ? $scanModel->getRecentScans(6) : [];
                $stats = $scanModel ? $scanModel->getStats() : [];
            } catch (Throwable $e) {
                error_log("Landing query error: " . $e->getMessage());
            }
        }

        // Ambil Flash Message
        $this->start_default_session_app();
        $flashMessage = $_SESSION['flash_message'] ?? null;
        unset($_SESSION['flash_message']);

        // Tentukan sesi makan aktif berdasarkan jam WITA saat ini
        $currentHour = (int)date('G');
        if ($currentHour < 10) {
            $activeMealSession = 'sarapan';
        } elseif ($currentHour < 16) {
            $activeMealSession = 'makan_siang';
        } else {
            $activeMealSession = 'makan_malam';
        }

        $data = [
            'title' => APP_NAME . ' - ' . APP_INSTITUTION,
            'app_name' => APP_NAME,
            'app_short_name' => APP_SHORT_NAME,
            'app_institution' => APP_INSTITUTION,
            'app_tagline' => APP_TAGLINE,
            'app_desc' => APP_DESC,
            'db_diagnostics' => $dbDiagnostics,
            'today_menus' => $todayMenus,
            'active_meal_session' => $activeMealSession,
            'recent_scans' => $recentScans,
            'stats' => $stats,
            'flash_message' => $flashMessage,
            'csrf_token' => $this->get_csrf_token(),
            'threshold' => defined('AI_CONFIDENCE_THRESHOLD') ? AI_CONFIDENCE_THRESHOLD : 70.0,
            'hide_default_nav' => true
        ];

        $this->display('landing/index', $data);
    }
}
