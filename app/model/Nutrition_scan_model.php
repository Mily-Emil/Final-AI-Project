<?php
/**
 * Model Pemindaian Nutrisi (Nutrition Scan Model)
 * AI-Powered Nutrition Analyzer System - Universitas Klabat (UNKLAB)
 * Mengelola hasil pemindaian kamera, riwayat nutrisi, dan antrean Human-in-the-Loop (HITL)
 */

class Nutrition_scan_model {

    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    /**
     * Simpan pemindaian nutrisi baru
     */
    public function createScan($data) {
        if (!$this->db->isConnected()) return false;

        $sql = "INSERT INTO nutrition_scans (
                    user_id, image_path, dish_name, food_components,
                    total_calories, total_weight_g, carbohydrate_g, protein_g, fat_g, fiber_g,
                    carbohydrate_pct, protein_pct, fat_pct,
                    confidence_score, status, ai_uncertainty_reason, created_at
                ) VALUES (
                    :uid, :img, :dish, :comps,
                    :cal, :w, :carb, :pro, :fat, :fib,
                    :cpct, :ppct, :fpct,
                    :conf, :stat, :reason, NOW()
                )";

        $this->db->query($sql);
        $this->db->bind(':uid', !empty($data['user_id']) ? (int)$data['user_id'] : null);
        $this->db->bind(':img', $data['image_path']);
        $this->db->bind(':dish', $data['dish_name']);
        $this->db->bind(':comps', is_array($data['food_components']) ? json_encode($data['food_components'], JSON_UNESCAPED_UNICODE) : ($data['food_components'] ?? '[]'));
        $this->db->bind(':cal', (float)($data['total_calories'] ?? 0));
        $this->db->bind(':w', (float)($data['total_weight_g'] ?? 0));
        $this->db->bind(':carb', (float)($data['carbohydrate_g'] ?? 0));
        $this->db->bind(':pro', (float)($data['protein_g'] ?? 0));
        $this->db->bind(':fat', (float)($data['fat_g'] ?? 0));
        $this->db->bind(':fib', (float)($data['fiber_g'] ?? 0));
        $this->db->bind(':cpct', (float)($data['carbohydrate_pct'] ?? 0));
        $this->db->bind(':ppct', (float)($data['protein_pct'] ?? 0));
        $this->db->bind(':fpct', (float)($data['fat_pct'] ?? 0));
        $this->db->bind(':conf', (float)($data['confidence_score'] ?? 0));
        $this->db->bind(':stat', in_array($data['status'], ['auto_approved', 'pending_admin_review', 'admin_corrected']) ? $data['status'] : 'pending_admin_review');
        $this->db->bind(':reason', !empty($data['ai_uncertainty_reason']) ? $data['ai_uncertainty_reason'] : null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    /**
     * Ambil data scan berdasarkan ID
     */
    public function getById($id) {
        if (!$this->db->isConnected()) return null;
        $this->db->query("SELECT s.*, u.nama_lengkap as reviewer_name 
                          FROM nutrition_scans s 
                          LEFT JOIN users u ON s.reviewed_by = u.id 
                          WHERE s.id = :id LIMIT 1");
        $this->db->bind(':id', (int)$id);
        $scan = $this->db->single();
        if ($scan) {
            $scan['food_components_decoded'] = json_decode($scan['food_components'] ?? '[]', true) ?: [];
        }
        return $scan;
    }

    /**
     * Ambil antrean verifikasi Admin (Human-in-the-Loop < 70% Confidence)
     */
    public function getPendingReviews($limit = 50) {
        if (!$this->db->isConnected() || !$this->db->hasTable('nutrition_scans')) return [];
        $this->db->query("SELECT s.*, u.nama_lengkap as scan_by_user 
                          FROM nutrition_scans s 
                          LEFT JOIN users u ON s.user_id = u.id 
                          WHERE s.status = 'pending_admin_review' OR s.confidence_score < 70.0 
                          ORDER BY s.id DESC LIMIT :lim");
        $this->db->bind(':lim', (int)$limit);
        $rows = $this->db->resultSet();
        foreach ($rows as &$r) {
            $r['food_components_decoded'] = json_decode($r['food_components'] ?? '[]', true) ?: [];
        }
        return $rows;
    }

    /**
     * Ambil semua riwayat scan
     */
    public function getRecentScans($limit = 20) {
        if (!$this->db->isConnected() || !$this->db->hasTable('nutrition_scans')) return [];
        $this->db->query("SELECT s.*, u.nama_lengkap as scan_by_user 
                          FROM nutrition_scans s 
                          LEFT JOIN users u ON s.user_id = u.id 
                          ORDER BY s.id DESC LIMIT :lim");
        $this->db->bind(':lim', (int)$limit);
        $rows = $this->db->resultSet();
        foreach ($rows as &$r) {
            $r['food_components_decoded'] = json_decode($r['food_components'] ?? '[]', true) ?: [];
        }
        return $rows;
    }

    /**
     * Kalibrasi Nutrisi oleh Administrator (Human-in-the-Loop Correction)
     */
    public function updateAdminCalibration($id, $data) {
        if (!$this->db->isConnected()) return false;

        $sql = "UPDATE nutrition_scans SET 
                    dish_name = :dish,
                    food_components = :comps,
                    total_calories = :cal,
                    total_weight_g = :w,
                    carbohydrate_g = :carb,
                    protein_g = :pro,
                    fat_g = :fat,
                    fiber_g = :fib,
                    carbohydrate_pct = :cpct,
                    protein_pct = :ppct,
                    fat_pct = :fpct,
                    status = 'admin_corrected',
                    admin_notes = :notes,
                    reviewed_by = :reviewer,
                    reviewed_at = NOW()
                WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind(':dish', trim($data['dish_name']));
        $this->db->bind(':comps', is_array($data['food_components']) ? json_encode($data['food_components'], JSON_UNESCAPED_UNICODE) : ($data['food_components'] ?? '[]'));
        $this->db->bind(':cal', (float)$data['total_calories']);
        $this->db->bind(':w', (float)$data['total_weight_g']);
        $this->db->bind(':carb', (float)$data['carbohydrate_g']);
        $this->db->bind(':pro', (float)$data['protein_g']);
        $this->db->bind(':fat', (float)$data['fat_g']);
        $this->db->bind(':fib', (float)($data['fiber_g'] ?? 0));
        $this->db->bind(':cpct', (float)$data['carbohydrate_pct']);
        $this->db->bind(':ppct', (float)$data['protein_pct']);
        $this->db->bind(':fpct', (float)$data['fat_pct']);
        $this->db->bind(':notes', !empty($data['admin_notes']) ? trim($data['admin_notes']) : 'Telah dikalibrasi oleh Administrator.');
        $this->db->bind(':reviewer', !empty($data['reviewed_by']) ? (int)$data['reviewed_by'] : null);
        $this->db->bind(':id', (int)$id);

        return $this->db->execute();
    }

    /**
     * Metrik Ringkasan untuk Executive Operations Dashboard Administrator
     */
    public function getStats() {
        $stats = [
            'total_scans' => 0,
            'scans_today' => 0,
            'avg_calories' => 0,
            'avg_confidence' => 0,
            'pending_reviews' => 0,
            'auto_approved' => 0,
            'admin_corrected' => 0
        ];

        if (!$this->db->isConnected() || !$this->db->hasTable('nutrition_scans')) return $stats;

        // Total scans
        $this->db->query("SELECT COUNT(*) as total, AVG(total_calories) as avg_cal, AVG(confidence_score) as avg_conf FROM nutrition_scans");
        $res = $this->db->single();
        if ($res) {
            $stats['total_scans'] = (int)($res['total'] ?? 0);
            $stats['avg_calories'] = round((float)($res['avg_cal'] ?? 0), 0);
            $stats['avg_confidence'] = round((float)($res['avg_conf'] ?? 0), 1);
        }

        // Scans today
        $this->db->query("SELECT COUNT(*) as total FROM nutrition_scans WHERE DATE(created_at) = CURDATE()");
        $resToday = $this->db->single();
        $stats['scans_today'] = (int)($resToday['total'] ?? 0);

        // Status counts
        $this->db->query("SELECT status, COUNT(*) as count FROM nutrition_scans GROUP BY status");
        $statusRows = $this->db->resultSet();
        foreach ($statusRows as $sr) {
            if ($sr['status'] === 'pending_admin_review') {
                $stats['pending_reviews'] = (int)$sr['count'];
            } elseif ($sr['status'] === 'auto_approved') {
                $stats['auto_approved'] = (int)$sr['count'];
            } elseif ($sr['status'] === 'admin_corrected') {
                $stats['admin_corrected'] = (int)$sr['count'];
            }
        }

        return $stats;
    }

    public function deleteScan($id) {
        if (!$this->db->isConnected()) return false;
        $this->db->query("DELETE FROM nutrition_scans WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        return $this->db->execute();
    }
}
