<?php
/**
 * Controller Menu Harian Publik (UNKLAB Daily Menu Board)
 * AI-Powered Nutrition Analyzer System - Universitas Klabat
 */

class menu extends Controller {

    public function index() {
        $date = $_GET['date'] ?? date('Y-m-d');
        $dailyMenuModel = $this->logic('Daily_menu_model');
        $todayMenus = $dailyMenuModel ? $dailyMenuModel->getPublishedToday($date) : ['sarapan' => [], 'makan_siang' => [], 'makan_malam' => []];

        $data = [
            'title' => 'Papan Menu Harian Dining Hall & Kantin - ' . APP_INSTITUTION,
            'selected_date' => $date,
            'menus' => $todayMenus,
            'active_menu' => 'menu'
        ];

        $this->display('menu/index', $data);
    }
}
