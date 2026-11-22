<?php
/**
 * Model Menu Harian Kampus (Daily Menu Model)
 * AI-Powered Nutrition Analyzer System - Universitas Klabat (UNKLAB)
 * Mengelola papan menu harian kantin/dining hall UNKLAB (Sarapan, Makan Siang, Makan Malam)
 */

class Daily_menu_model {

    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Ambil menu yang dipublikasikan untuk hari ini (Papan Menu Publik)
     */
    public function getPublishedToday($targetDate = null) {
        if (!$this->db->isConnected() || !$this->db->hasTable('daily_menus')) return ['sarapan' => [], 'makan_siang' => [], 'makan_malam' => []];

        $dateSql = $targetDate ? ':dt' : 'CURDATE()';
        $sql = "SELECT m.*, u.nama_lengkap as creator_name 
                FROM daily_menus m 
                LEFT JOIN users u ON m.created_by = u.id 
                WHERE m.menu_date = $dateSql AND m.is_published = 1 
                ORDER BY FIELD(m.meal_session, 'sarapan', 'makan_siang', 'makan_malam'), m.id ASC";

        $this->db->query($sql);
        if ($targetDate) {
            $this->db->bind(':dt', $targetDate);
        }

        $rows = $this->db->resultSet();

        // Jika hari ini belum ada menu, ambil menu tanggal terbaru yang tersedia agar tidak kosong
        if (empty($rows)) {
            $this->db->query("SELECT m.*, u.nama_lengkap as creator_name 
                              FROM daily_menus m 
                              LEFT JOIN users u ON m.created_by = u.id 
                              WHERE m.is_published = 1 
                              ORDER BY m.menu_date DESC, FIELD(m.meal_session, 'sarapan', 'makan_siang', 'makan_malam'), m.id ASC 
                              LIMIT 10");
            $rows = $this->db->resultSet();
        }

        $grouped = [
            'sarapan' => [],
            'makan_siang' => [],
            'makan_malam' => []
        ];

        foreach ($rows as $item) {
            $session = strtolower($item['meal_session']);
            if (isset($grouped[$session])) {
                $grouped[$session][] = $item;
            }
        }

        return $grouped;
    }

    /**
     * Ambil semua daftar menu harian (untuk Petugas / Admin)
     */
    public function getAllMenus($limit = 100) {
        if (!$this->db->isConnected() || !$this->db->hasTable('daily_menus')) return [];
        $this->db->query("SELECT m.*, u.nama_lengkap as creator_name 
                          FROM daily_menus m 
                          LEFT JOIN users u ON m.created_by = u.id 
                          ORDER BY m.menu_date DESC, FIELD(m.meal_session, 'sarapan', 'makan_siang', 'makan_malam'), m.id DESC 
                          LIMIT :lim");
        $this->db->bind(':lim', (int)$limit);
        return $this->db->resultSet();
    }

    public function getById($id) {
        if (!$this->db->isConnected()) return null;
        $this->db->query("SELECT m.*, u.nama_lengkap as creator_name 
                          FROM daily_menus m 
                          LEFT JOIN users u ON m.created_by = u.id 
                          WHERE m.id = :id LIMIT 1");
        $this->db->bind(':id', (int)$id);
        return $this->db->single();
    }

    /**
     * Tambah menu harian baru (Manual Menu Builder)
     */
    public function createMenu($data) {
        if (!$this->db->isConnected()) return false;

        // Hitung persentase Atwater jika belum diisi atau 0
        $carbsG = max(0, (float)($data['carbohydrate_g'] ?? 0));
        $proG   = max(0, (float)($data['protein_g'] ?? 0));
        $fatG   = max(0, (float)($data['fat_g'] ?? 0));
        $fibG   = max(0, (float)($data['fiber_g'] ?? 0));

        $cKkal = $carbsG * 4.0;
        $pKkal = $proG * 4.0;
        $fKkal = $fatG * 9.0;
        $macroTotal = $cKkal + $pKkal + $fKkal;

        if ($macroTotal > 0) {
            $cPct = round(($cKkal / $macroTotal) * 100, 1);
            $pPct = round(($pKkal / $macroTotal) * 100, 1);
            $fPct = round(100.0 - ($cPct + $pPct), 1);
            $calories = round($macroTotal, 1);
        } else {
            $cPct = (float)($data['carbohydrate_pct'] ?? 0);
            $pPct = (float)($data['protein_pct'] ?? 0);
            $fPct = (float)($data['fat_pct'] ?? 0);
            $calories = (float)($data['calories'] ?? 0);
        }

        $sql = "INSERT INTO daily_menus (
                    meal_session, menu_date, name, description, ingredients, image_path,
                    calories, portion_weight_g, carbohydrate_g, protein_g, fat_g, fiber_g,
                    carbohydrate_pct, protein_pct, fat_pct, is_available, is_published, created_by
                ) VALUES (
                    :session, :mdate, :name, :desc, :ing, :img,
                    :cal, :w, :carb, :pro, :fat, :fib,
                    :cpct, :ppct, :fpct, :avail, :pub, :creator
                )";

        $this->db->query($sql);
        $this->db->bind(':session', in_array($data['meal_session'], ['sarapan', 'makan_siang', 'makan_malam']) ? $data['meal_session'] : 'makan_siang');
        $this->db->bind(':mdate', !empty($data['menu_date']) ? $data['menu_date'] : date('Y-m-d'));
        $this->db->bind(':name', trim($data['name']));
        $this->db->bind(':desc', !empty($data['description']) ? trim($data['description']) : null);
        $this->db->bind(':ing', !empty($data['ingredients']) ? trim($data['ingredients']) : null);
        $this->db->bind(':img', !empty($data['image_path']) ? $data['image_path'] : null);
        $this->db->bind(':cal', $calories);
        $this->db->bind(':w', (float)($data['portion_weight_g'] ?? 0));
        $this->db->bind(':carb', $carbsG);
        $this->db->bind(':pro', $proG);
        $this->db->bind(':fat', $fatG);
        $this->db->bind(':fib', $fibG);
        $this->db->bind(':cpct', $cPct);
        $this->db->bind(':ppct', $pPct);
        $this->db->bind(':fpct', $fPct);
        $this->db->bind(':avail', isset($data['is_available']) ? (int)$data['is_available'] : 1);
        $this->db->bind(':pub', isset($data['is_published']) ? (int)$data['is_published'] : 1);
        $this->db->bind(':creator', !empty($data['created_by']) ? (int)$data['created_by'] : null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Perbarui menu harian
     */
    public function updateMenu($id, $data) {
        if (!$this->db->isConnected()) return false;

        $carbsG = max(0, (float)($data['carbohydrate_g'] ?? 0));
        $proG   = max(0, (float)($data['protein_g'] ?? 0));
        $fatG   = max(0, (float)($data['fat_g'] ?? 0));
        $fibG   = max(0, (float)($data['fiber_g'] ?? 0));

        $cKkal = $carbsG * 4.0;
        $pKkal = $proG * 4.0;
        $fKkal = $fatG * 9.0;
        $macroTotal = $cKkal + $pKkal + $fKkal;

        if ($macroTotal > 0) {
            $cPct = round(($cKkal / $macroTotal) * 100, 1);
            $pPct = round(($pKkal / $macroTotal) * 100, 1);
            $fPct = round(100.0 - ($cPct + $pPct), 1);
            $calories = round($macroTotal, 1);
        } else {
            $cPct = (float)($data['carbohydrate_pct'] ?? 0);
            $pPct = (float)($data['protein_pct'] ?? 0);
            $fPct = (float)($data['fat_pct'] ?? 0);
            $calories = (float)($data['calories'] ?? 0);
        }

        $imgClause = !empty($data['image_path']) ? ", image_path = :img" : "";

        $sql = "UPDATE daily_menus SET 
                    meal_session = :session,
                    menu_date = :mdate,
                    name = :name,
                    description = :desc,
                    ingredients = :ing,
                    calories = :cal,
                    portion_weight_g = :w,
                    carbohydrate_g = :carb,
                    protein_g = :pro,
                    fat_g = :fat,
                    fiber_g = :fib,
                    carbohydrate_pct = :cpct,
                    protein_pct = :ppct,
                    fat_pct = :fpct,
                    is_available = :avail,
                    is_published = :pub
                    $imgClause
                WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind(':session', in_array($data['meal_session'], ['sarapan', 'makan_siang', 'makan_malam']) ? $data['meal_session'] : 'makan_siang');
        $this->db->bind(':mdate', !empty($data['menu_date']) ? $data['menu_date'] : date('Y-m-d'));
        $this->db->bind(':name', trim($data['name']));
        $this->db->bind(':desc', !empty($data['description']) ? trim($data['description']) : null);
        $this->db->bind(':ing', !empty($data['ingredients']) ? trim($data['ingredients']) : null);
        $this->db->bind(':cal', $calories);
        $this->db->bind(':w', (float)($data['portion_weight_g'] ?? 0));
        $this->db->bind(':carb', $carbsG);
        $this->db->bind(':pro', $proG);
        $this->db->bind(':fat', $fatG);
        $this->db->bind(':fib', $fibG);
        $this->db->bind(':cpct', $cPct);
        $this->db->bind(':ppct', $pPct);
        $this->db->bind(':fpct', $fPct);
        $this->db->bind(':avail', isset($data['is_available']) ? (int)$data['is_available'] : 1);
        $this->db->bind(':pub', isset($data['is_published']) ? (int)$data['is_published'] : 1);
        if (!empty($data['image_path'])) {
            $this->db->bind(':img', $data['image_path']);
        }
        $this->db->bind(':id', (int)$id);

        return $this->db->execute();
    }

    /**
     * Toggle Ketersediaan Menu (Tersedia / Habis)
     */
    public function toggleAvailability($id, $newStatus = null) {
        if (!$this->db->isConnected()) return false;
        if ($newStatus === null) {
            $sql = "UPDATE daily_menus SET is_available = IF(is_available = 1, 0, 1) WHERE id = :id";
            $this->db->query($sql);
        } else {
            $sql = "UPDATE daily_menus SET is_available = :s WHERE id = :id";
            $this->db->query($sql);
            $this->db->bind(':s', (int)$newStatus);
        }
        $this->db->bind(':id', (int)$id);
        return $this->db->execute();
    }

    /**
     * Toggle Publikasi Menu
     */
    public function togglePublished($id, $newStatus = null) {
        if (!$this->db->isConnected()) return false;
        if ($newStatus === null) {
            $sql = "UPDATE daily_menus SET is_published = IF(is_published = 1, 0, 1) WHERE id = :id";
            $this->db->query($sql);
        } else {
            $sql = "UPDATE daily_menus SET is_published = :s WHERE id = :id";
            $this->db->query($sql);
            $this->db->bind(':s', (int)$newStatus);
        }
        $this->db->bind(':id', (int)$id);
        return $this->db->execute();
    }

    public function deleteMenu($id) {
        if (!$this->db->isConnected()) return false;
        $this->db->query("DELETE FROM daily_menus WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        return $this->db->execute();
    }
}
