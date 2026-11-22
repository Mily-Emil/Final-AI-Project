<?php
/**
 * Controller Pemindaian Nutrisi (Scan Controller)
 * AI-Powered Nutrition Analyzer System - Universitas Klabat (UNKLAB)
 */

class scan extends Controller {

    public function index() {
        header('Location: ' . APP_PATH . '/');
        exit();
    }

    /**
     * Tampilkan rincian hasil pemindaian
     */
    public function detail($id = 0) {
        $scanId = (int)$id;
        $scanModel = $this->logic('Nutrition_scan_model');
        $scan = $scanModel ? $scanModel->getById($scanId) : null;

        if (!$scan) {
            $this->abort(404, "Hasil pemindaian nutrisi tidak ditemukan.");
            return;
        }

        $data = [
            'title' => 'Rincian Nutrisi: ' . htmlspecialchars($scan['dish_name']) . ' - ' . APP_SHORT_NAME,
            'scan' => $scan,
            'active_menu' => 'scan',
            'threshold' => defined('AI_CONFIDENCE_THRESHOLD') ? AI_CONFIDENCE_THRESHOLD : 70.0
        ];

        $this->display('scan/detail', $data);
    }

    /**
     * Endpoint MVC alternatif untuk pemrosesan scan (meneruskan ke REST API)
     */
    public function process() {
        require_once BASEPATH . '/public/api/scan.php';
        exit();
    }
}
