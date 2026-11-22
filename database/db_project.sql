-- =====================================================================
-- DATABASE: db_project
-- AI-POWERED NUTRITION ANALYZER SYSTEM - UNIVERSITAS KLABAT (UNKLAB)
-- Sistem Estimasi Persentase Nutrisi Berbasis Kamera Smartphone & Kalibrasi HITL
-- =====================================================================

CREATE DATABASE IF NOT EXISTS `db_project` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `db_project`;

-- Bersihkan tabel lama jika sebelumnya sudah pernah mengimpor template awal
SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `nutrition_scans`;
DROP TABLE IF EXISTS `daily_menus`;
DROP TABLE IF EXISTS `food_references`;
DROP TABLE IF EXISTS `aktivitas_log`;
DROP TABLE IF EXISTS `pengaturan`;
DROP TABLE IF EXISTS `mahasiswa`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- 1. Tabel: users (Pengguna, Petugas Kantin & Administrator)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `username` VARCHAR(50) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `role` ENUM('admin', 'petugas') NOT NULL DEFAULT 'petugas',
  `avatar` VARCHAR(255) DEFAULT 'default-avatar.png',
  `status` ENUM('active', 'inactive') NOT NULL DEFAULT 'active',
  `last_login` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`username`),
  INDEX (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 2. Tabel: nutrition_scans (Hasil Pemindaian Kamera & Status HITL)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `nutrition_scans` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL COMMENT 'NULL untuk Public User tanpa login, terisi jika scan oleh staf',
  `image_path` VARCHAR(255) NOT NULL,
  `dish_name` VARCHAR(150) NOT NULL,
  `food_components` LONGTEXT NULL COMMENT 'JSON array rincian komponen porsi',
  `total_calories` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `total_weight_g` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `carbohydrate_g` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `protein_g` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `fat_g` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `fiber_g` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `carbohydrate_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `protein_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `fat_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `confidence_score` DECIMAL(5,2) NOT NULL DEFAULT 0.00 COMMENT '0.00 - 100.00 %',
  `status` ENUM('auto_approved', 'pending_admin_review', 'admin_corrected') NOT NULL DEFAULT 'pending_admin_review',
  `ai_uncertainty_reason` TEXT NULL COMMENT 'Alasan jika confidence < 70%',
  `admin_notes` TEXT NULL COMMENT 'Catatan kalibrasi administrator',
  `reviewed_by` INT NULL,
  `reviewed_at` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`status`),
  INDEX (`confidence_score`),
  INDEX (`created_at`),
  CONSTRAINT `fk_scans_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_scans_reviewer` FOREIGN KEY (`reviewed_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 3. Tabel: daily_menus (Papan Menu Harian UNKLAB Dining Hall / Kantin)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `daily_menus` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `meal_session` ENUM('sarapan', 'makan_siang', 'makan_malam') NOT NULL,
  `menu_date` DATE NOT NULL,
  `name` VARCHAR(150) NOT NULL,
  `description` TEXT NULL,
  `ingredients` TEXT NULL COMMENT 'Komposisi rincian bahan makanan',
  `image_path` VARCHAR(255) NULL,
  `calories` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `portion_weight_g` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `carbohydrate_g` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `protein_g` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `fat_g` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `fiber_g` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `carbohydrate_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `protein_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `fat_pct` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
  `is_available` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Tersedia, 0 = Habis',
  `is_published` TINYINT(1) NOT NULL DEFAULT 1 COMMENT '1 = Dipublikasikan, 0 = Draf',
  `created_by` INT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`meal_session`),
  INDEX (`menu_date`),
  INDEX (`is_available`),
  INDEX (`is_published`),
  CONSTRAINT `fk_menus_creator` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 4. Tabel: food_references (Master Data Pangan TKPI & Khas Sulut)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `food_references` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `food_name` VARCHAR(150) NOT NULL,
  `category` VARCHAR(100) NOT NULL COMMENT 'Makanan Pokok, Lauk Hewani, Lauk Nabati, Sayuran, Khas Sulawesi Utara',
  `serving_size_g` DECIMAL(8,2) NOT NULL DEFAULT 100.00,
  `calories` DECIMAL(8,2) NOT NULL,
  `carbohydrate_g` DECIMAL(8,2) NOT NULL,
  `protein_g` DECIMAL(8,2) NOT NULL,
  `fat_g` DECIMAL(8,2) NOT NULL,
  `fiber_g` DECIMAL(8,2) NOT NULL DEFAULT 0.00,
  `notes` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX (`food_name`),
  INDEX (`category`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 5. Tabel: pengaturan (Pengaturan Aplikasi & Konfigurasi Sistem)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `pengaturan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(50) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `deskripsi` VARCHAR(255) NULL,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ---------------------------------------------------------------------
-- 6. Tabel: aktivitas_log (Log Aktivitas Pengguna & Audit Trail)
-- ---------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `aktivitas_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NULL,
  `username` VARCHAR(50) NULL,
  `aktivitas` VARCHAR(100) NOT NULL,
  `detail` TEXT NULL,
  `ip_address` VARCHAR(45) NULL,
  `user_agent` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (`user_id`),
  INDEX (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- =====================================================================
-- DATA SEEDER AWAL (DEFAULT SEED DATA)
-- =====================================================================

-- 1. Pengguna Bawaan (Admin & Petugas Kantin)
-- Akun 1: admin   / admin123   (Administrator UNKLAB)
-- Akun 2: petugas / petugas123 (Petugas Kantin / Dining Hall UNKLAB)
INSERT INTO `users` (`id`, `username`, `password`, `nama_lengkap`, `email`, `role`, `status`) VALUES
(1, 'admin', '$2y$10$wQ1XX1yl25e2.fK3ttYn0.ilPYpI1rjG8TiWK2sAM5ARRdknFi2Em', 'Administrator Sistem UNKLAB', 'admin@unklab.ac.id', 'admin', 'active'),
(2, 'petugas', '$2y$10$vCW.RrAY.Qfifm4wsLVv7O7F1y8ackYXAdfIa5vQVyib5dPM1bsgu', 'Staf Dining Hall UNKLAB', 'dininghall@unklab.ac.id', 'petugas', 'active')
ON DUPLICATE KEY UPDATE `username` = VALUES(`username`);

-- 2. Master Data Pangan TKPI & Kuliner Khas Sulawesi Utara
INSERT INTO `food_references` (`id`, `food_name`, `category`, `serving_size_g`, `calories`, `carbohydrate_g`, `protein_g`, `fat_g`, `fiber_g`, `notes`) VALUES
(1, 'Tinutuan (Bubur Manado)', 'Khas Sulawesi Utara', 350.00, 245.00, 42.00, 6.50, 4.20, 5.80, 'Bubur beras dengan labu kuning, kangkung, bayam, kemangi, dan jagung'),
(2, 'Cakalang Fufu Suwir Rica', 'Khas Sulawesi Utara', 100.00, 185.00, 3.20, 28.50, 6.00, 0.80, 'Ikan cakalang asap khas Minahasa kaya protein berkualitas tinggi'),
(3, 'Ayam Woku Belanga', 'Khas Sulawesi Utara', 150.00, 260.00, 4.50, 31.00, 12.50, 1.20, 'Olahan ayam berbumbu rempah woku daun kemangi, kunyit, dan serai'),
(4, 'Ikan Kuah Asam Manado', 'Khas Sulawesi Utara', 180.00, 160.00, 3.00, 26.00, 4.00, 0.50, 'Sup ikan segar berkuah bening asam tomat dan daun kemangi rendah lemak'),
(5, 'Perkedel Jagung (Milu)', 'Khas Sulawesi Utara', 80.00, 165.00, 18.00, 4.20, 8.50, 2.10, 'Gorengan jagung manis khas Manado renyah'),
(6, 'Nasi Putih Pulen', 'Makanan Pokok', 150.00, 195.00, 43.00, 3.80, 0.50, 0.60, 'Sumber karbohidrat utama beras putih matang'),
(7, 'Nasi Merah Organik', 'Makanan Pokok', 150.00, 165.00, 34.50, 4.20, 1.20, 2.80, 'Beras merah kaya serat dengan indeks glikemik lebih rendah'),
(8, 'Sayur Kangkung Cah Bawang', 'Sayuran', 100.00, 72.00, 4.00, 2.80, 4.50, 2.40, 'Tumisan kangkung segar dengan sedikit minyak dan bawang putih'),
(9, 'Tahu Goreng Kuning', 'Lauk Nabati', 100.00, 115.00, 2.50, 10.50, 7.20, 1.40, 'Sumber protein nabati berbasis kedelai'),
(10, 'Tempe Orek Manis Gurih', 'Lauk Nabati', 80.00, 158.00, 12.00, 11.20, 7.80, 2.60, 'Olahan tempe fermentasi kaya prebiotik dan protein'),
(11, 'Telur Rebus Utuh', 'Lauk Hewani', 60.00, 93.00, 0.60, 7.50, 6.40, 0.00, 'Telur ayam rebus kaya kolin dan asam amino lengkap'),
(12, 'Pisang Goroho Rebus', 'Khas Sulawesi Utara', 120.00, 135.00, 32.00, 1.50, 0.30, 3.20, 'Pisang khas Sulut dengan pati resisten sehat untuk pencernaan')
ON DUPLICATE KEY UPDATE `food_name` = VALUES(`food_name`);

-- 3. Menu Harian UNKLAB (Dining Hall & Kantin Kampus)
INSERT INTO `daily_menus` (`id`, `meal_session`, `menu_date`, `name`, `description`, `ingredients`, `image_path`, `calories`, `portion_weight_g`, `carbohydrate_g`, `protein_g`, `fat_g`, `fiber_g`, `carbohydrate_pct`, `protein_pct`, `fat_pct`, `is_available`, `is_published`, `created_by`) VALUES
(1, 'sarapan', CURDATE(), 'Paket Sarapan Sehat Tinutuan Manado + Perkedel Milu', 'Sarapan kaya serat khas Minahasa disajikan hangat dengan perkedel jagung manis renyah.', 'Beras, labu kuning, jagung manis, kangkung, kemangi, sambal dabu-dabu, minyak nabati', 'tinutuan_sample.jpg', 410.00, 430.00, 60.00, 10.70, 12.70, 7.90, 60.50, 10.80, 28.70, 1, 1, 2),
(2, 'makan_siang', CURDATE(), 'Paket Lunch Cakalang Fufu + Sayur Kangkung + Nasi Putih', 'Menu makan siang tinggi protein dengan aroma khas cakalang asap dan tumis sayur hijau segar.', 'Beras putih pulen, ikan cakalang asap, cabai rica, kangkung, bawang merah, bawang putih', 'cakalang_lunch.jpg', 512.00, 380.00, 50.20, 35.10, 11.00, 3.80, 45.60, 31.90, 22.50, 1, 1, 2),
(3, 'makan_malam', CURDATE(), 'Paket Dinner Ikan Kuah Asam Segar + Nasi Merah + Tahu', 'Santap malam rendah lemak menyehatkan dengan kuah asam belimbing/tomat dan karbohidrat kompleks.', 'Beras merah, ikan tenggiri/kakap segar, tomat hijau, daun kemangi, tahu kedelai kukus', 'kuah_asam_dinner.jpg', 440.00, 430.00, 39.50, 40.70, 12.40, 4.60, 36.60, 37.70, 25.70, 1, 1, 2)
ON DUPLICATE KEY UPDATE `name` = VALUES(`name`);

-- 4. Sampel Data Pemindaian Nutrisi (Demonstrasi HITL >= 70% dan < 70%)
INSERT INTO `nutrition_scans` (`id`, `user_id`, `image_path`, `dish_name`, `food_components`, `total_calories`, `total_weight_g`, `carbohydrate_g`, `protein_g`, `fat_g`, `fiber_g`, `carbohydrate_pct`, `protein_pct`, `fat_pct`, `confidence_score`, `status`, `ai_uncertainty_reason`, `admin_notes`, `reviewed_by`, `reviewed_at`) VALUES
(1, NULL, 'sample_tinutuan.jpg', 'Tinutuan Komplit + Sambal Roa', '[{"name":"Bubur Manado (Labu & Sayur)","portion_g":300,"calories":210,"carbs_g":38,"protein_g":5,"fat_g":3},{"name":"Perkedel Jagung","portion_g":70,"calories":145,"carbs_g":16,"protein_g":3.5,"fat_g":7.5}]', 355.00, 370.00, 54.00, 8.50, 10.50, 6.20, 62.80, 9.90, 27.30, 88.50, 'auto_approved', NULL, NULL, NULL, NULL),
(2, NULL, 'sample_kuah_pekat.jpg', 'Ikan Kuah Santan Pekat + Nasi (Deteksi Ambigu)', '[{"name":"Nasi Putih","portion_g":150,"calories":195,"carbs_g":43,"protein_g":3.8,"fat_g":0.5},{"name":"Lauk Tertutup Kuah Tebal","portion_g":120,"calories":230,"carbs_g":8,"protein_g":18,"fat_g":14}]', 425.00, 270.00, 51.00, 21.80, 14.50, 1.80, 48.40, 20.70, 30.90, 54.00, 'pending_admin_review', 'Objek lauk tertutup kuah pekat dan pencahayaan redup, estimasi gramasi protein belum pasti.', NULL, NULL, NULL),
(3, NULL, 'sample_woku.jpg', 'Ayam Woku Belanga + Nasi Putih', '[{"name":"Nasi Putih Pulen","portion_g":150,"calories":195,"carbs_g":43,"protein_g":3.8,"fat_g":0.5},{"name":"Ayam Woku Belanga","portion_g":150,"calories":260,"carbs_g":4.5,"protein_g":31,"fat_g":12.5}]', 455.00, 300.00, 47.50, 34.80, 13.00, 1.80, 42.60, 31.20, 26.20, 61.50, 'admin_corrected', 'Pencahayaan foto miring dan estimasi kuah minyak awalnya diragukan AI.', 'Dikalibrasi oleh Administrator: Gramasi ayam disesuaikan dari 110g menjadi 150g sesuai porsi standar kantin.', 1, NOW())
ON DUPLICATE KEY UPDATE `dish_name` = VALUES(`dish_name`);

-- 5. Pengaturan Aplikasi & Metadata UNKLAB
INSERT INTO `pengaturan` (`setting_key`, `setting_value`, `deskripsi`) VALUES
('app_name', 'AI-Powered Nutrition Analyzer System', 'Nama resmi sistem aplikasi gizi'),
('institution', 'Universitas Klabat (UNKLAB)', 'Institusi pemilik platform'),
('location', 'Airmadidi, Minahasa Utara, Sulawesi Utara', 'Lokasi kampus'),
('confidence_threshold', '70.0', 'Ambang batas keyakinan AI untuk Human-in-the-Loop'),
('ai_model', 'gemini-2.5-flash', 'Model AI Google Gemini Vision yang digunakan'),
('fallback_mode', 'local_heuristic', 'Metode fallback jika API offline atau quota habis')
ON DUPLICATE KEY UPDATE `setting_key` = VALUES(`setting_key`);
