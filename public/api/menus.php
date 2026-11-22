<?php
/**
 * RESTful API: /api/menus.php
 * AI-Powered Nutrition Analyzer System - Universitas Klabat (UNKLAB)
 * Mengembalikan daftar menu harian kantin/dining hall UNKLAB yang dipublikasikan.
 */

header('Content-Type: application/json; charset=UTF-8');
header('X-Content-Type-Options: nosniff');

require_once dirname(__DIR__, 2) . '/app/init.php';

$date = $_GET['date'] ?? null;
$menuModel = new Daily_menu_model();
$menus = $menuModel->getPublishedToday($date);

echo json_encode([
    'success' => true,
    'institution' => defined('APP_INSTITUTION') ? APP_INSTITUTION : 'Universitas Klabat (UNKLAB)',
    'date' => $date ?: date('Y-m-d'),
    'data' => $menus
]);
exit();
