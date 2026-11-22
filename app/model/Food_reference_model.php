<?php
/**
 * Model Referensi Pangan (Food Reference Model - TKPI)
 * AI-Powered Nutrition Analyzer System - Universitas Klabat (UNKLAB)
 * Mengelola basis data nilai gizi standar kampus mengacu pada Tabel Komposisi Pangan Indonesia (TKPI)
 * dan kuliner lokal khas Minahasa / Sulawesi Utara.
 */

class Food_reference_model {

    private $db;

    public function __construct() {
        $this->db = new Database();
    }

    public function getAll($search = null, $category = null) {
        if (!$this->db->isConnected()) return [];

        $conditions = [];
        $params = [];

        if (!empty($search)) {
            $conditions[] = "(food_name LIKE :s OR notes LIKE :s)";
            $params[':s'] = '%' . trim($search) . '%';
        }

        if (!empty($category)) {
            $conditions[] = "category = :c";
            $params[':c'] = trim($category);
        }

        $whereClause = !empty($conditions) ? "WHERE " . implode(" AND ", $conditions) : "";
        $sql = "SELECT * FROM food_references $whereClause ORDER BY category ASC, food_name ASC";

        $this->db->query($sql);
        foreach ($params as $k => $v) {
            $this->db->bind($k, $v);
        }

        return $this->db->resultSet();
    }

    public function getById($id) {
        if (!$this->db->isConnected()) return null;
        $this->db->query("SELECT * FROM food_references WHERE id = :id LIMIT 1");
        $this->db->bind(':id', (int)$id);
        return $this->db->single();
    }

    public function getCategories() {
        if (!$this->db->isConnected()) return [];
        $this->db->query("SELECT DISTINCT category FROM food_references ORDER BY category ASC");
        $rows = $this->db->resultSet();
        $cats = [];
        foreach ($rows as $r) {
            if (!empty($r['category'])) $cats[] = $r['category'];
        }
        return $cats;
    }

    public function create($data) {
        if (!$this->db->isConnected()) return false;

        $sql = "INSERT INTO food_references (
                    food_name, category, serving_size_g, calories, carbohydrate_g, protein_g, fat_g, fiber_g, notes
                ) VALUES (
                    :name, :cat, :serving, :cal, :carb, :pro, :fat, :fib, :notes
                )";

        $this->db->query($sql);
        $this->db->bind(':name', trim($data['food_name']));
        $this->db->bind(':cat', trim($data['category']));
        $this->db->bind(':serving', (float)($data['serving_size_g'] ?? 100));
        $this->db->bind(':cal', (float)($data['calories'] ?? 0));
        $this->db->bind(':carb', (float)($data['carbohydrate_g'] ?? 0));
        $this->db->bind(':pro', (float)($data['protein_g'] ?? 0));
        $this->db->bind(':fat', (float)($data['fat_g'] ?? 0));
        $this->db->bind(':fib', (float)($data['fiber_g'] ?? 0));
        $this->db->bind(':notes', !empty($data['notes']) ? trim($data['notes']) : null);

        if ($this->db->execute()) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function update($id, $data) {
        if (!$this->db->isConnected()) return false;

        $sql = "UPDATE food_references SET 
                    food_name = :name,
                    category = :cat,
                    serving_size_g = :serving,
                    calories = :cal,
                    carbohydrate_g = :carb,
                    protein_g = :pro,
                    fat_g = :fat,
                    fiber_g = :fib,
                    notes = :notes
                WHERE id = :id";

        $this->db->query($sql);
        $this->db->bind(':name', trim($data['food_name']));
        $this->db->bind(':cat', trim($data['category']));
        $this->db->bind(':serving', (float)($data['serving_size_g'] ?? 100));
        $this->db->bind(':cal', (float)($data['calories'] ?? 0));
        $this->db->bind(':carb', (float)($data['carbohydrate_g'] ?? 0));
        $this->db->bind(':pro', (float)($data['protein_g'] ?? 0));
        $this->db->bind(':fat', (float)($data['fat_g'] ?? 0));
        $this->db->bind(':fib', (float)($data['fiber_g'] ?? 0));
        $this->db->bind(':notes', !empty($data['notes']) ? trim($data['notes']) : null);
        $this->db->bind(':id', (int)$id);

        return $this->db->execute();
    }

    public function delete($id) {
        if (!$this->db->isConnected()) return false;
        $this->db->query("DELETE FROM food_references WHERE id = :id");
        $this->db->bind(':id', (int)$id);
        return $this->db->execute();
    }
}
