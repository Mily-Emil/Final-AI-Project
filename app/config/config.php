<?php
/**
 * Konfigurasi Utama Aplikasi - AI-Powered Nutrition Analyzer System
 * Universitas Klabat (UNKLAB) - Airmadidi, Sulawesi Utara
 * Framework MVC Native PHP
 */

// 1. Direktori Root Proyek (Filesystem Path)
if (!defined('BASEPATH')) {
    define('BASEPATH', dirname(__DIR__, 2));
}

// 2. Parser Berkas Lingkungan (.env) Sederhana & Aman
if (file_exists(BASEPATH . '/.env')) {
    $envLines = file(BASEPATH . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($envLines as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($key, $val) = explode('=', $line, 2);
            $key = trim($key);
            $val = trim($val, " \t\n\r\0\x0B\"'");
            if (!array_key_exists($key, $_SERVER) && !array_key_exists($key, $_ENV)) {
                putenv("$key=$val");
                $_ENV[$key] = $val;
                $_SERVER[$key] = $val;
            }
        }
    }
}

// 3. Deteksi Base URL / APP_PATH secara otomatis
if (!defined('APP_PATH')) {
    if (php_sapi_name() === 'cli' || empty($_SERVER['HTTP_HOST'])) {
        $folderName = basename(BASEPATH);
        define("APP_PATH", "http://localhost/" . $folderName);
    } else {
        $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
        $protocol = $is_https ? 'https://' : 'http://';
        $host = $_SERVER['HTTP_HOST'];
        $scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '')), '/');
        $cleanDir = preg_replace('#/public$#', '', $scriptDir);
        define("APP_PATH", $protocol . $host . $cleanDir);
    }
}

// 4. Konfigurasi Database MySQL
define("HOST_DB", getenv('DB_HOST') ?: "localhost");
define("NAME_DB", getenv('DB_NAME') ?: "db_project");  // Nama database default
define("USER_DB", getenv('DB_USER') ?: "root");
define("PASS_DB", getenv('DB_PASS') !== false ? getenv('DB_PASS') : ""); // Default XAMPP

// 5. Metadata Aplikasi & Institusi Universitas Klabat
define("APP_NAME", "AI-Powered Nutrition Analyzer System");
define("APP_SHORT_NAME", "UNKLAB Nutrition AI");
define("APP_TAGLINE", "Sistem Estimasi Persentase Nutrisi Berbasis Kamera Smartphone & Kalibrasi Human-in-the-Loop");
define("APP_INSTITUTION", "Universitas Klabat (UNKLAB)");
define("APP_CAMPUS_LOCATION", "Airmadidi, Minahasa Utara, Sulawesi Utara");
define("APP_DESC", "Platform digital estimasi kandungan nutrisi makro & kalori makanan kantin kampus secara instan dengan verifikasi AI & Kalibrasi Human-in-the-Loop");
define("APP_VERSION", "1.0.0");
define("APP_AUTHOR", "Fakultas Ilmu Komputer UNKLAB");

// 6. Ambang Batas Akurasi AI (Human-in-the-Loop Threshold)
define("AI_CONFIDENCE_THRESHOLD", 70.0); // Skor >= 70% disetujui otomatis, < 70% masuk antrean Admin HITL

// 7. Konfigurasi Google Gemini Vision API
define("GEMINI_API_KEY", getenv('GEMINI_API_KEY') ?: '');
define("GEMINI_MODEL", getenv('GEMINI_MODEL') ?: 'gemini-2.5-flash');

// 8. Identitas Warna Resmi UNKLAB
define("COLOR_NAVY", "#0A2240");   // Warna Dominan / Header Navigasi / Sidebar
define("COLOR_ROYAL", "#163A6C");  // Warna Sekunder / Sub-judul / Akses Elemen
define("COLOR_GOLD", "#F2A900");   // Aksen Utama / Tombol Scan Kamera / Indikator Antrean
define("COLOR_SURFACE", "#F8FAFC");// Latar Belakang Bersih & Terang
define("COLOR_WHITE", "#FFFFFF");  // Kartu Kontras Tinggi

// 9. Direktori Penyimpanan Unggahan Berkas
define("DIR_UPLOADS", BASEPATH . '/public/uploads');
define("DIR_UPLOADS_SCANS", BASEPATH . '/public/uploads/scans');
define("DIR_UPLOADS_MENUS", BASEPATH . '/public/uploads/menus');

// 10. Kunci Enkripsi & Keamanan Aplikasi
define('APP_ENC_KEY', hash('sha256', 'UNKLAB_NUTRITION_ANALYZER_KEY_2026', true));
define('APP_BIDX_KEY', hash('sha256', 'UNKLAB_NUTRITION_BLIND_INDEX_SALT_2026', true));
