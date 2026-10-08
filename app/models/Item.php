<?php
class Item {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getAll() {
        return $this->db->query("SELECT * FROM items")->fetchAll(PDO::FETCH_ASSOC);
    }


    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM items WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create($name, $description) {
        $stmt = $this->db->prepare("INSERT INTO items (name, description) VALUES (?, ?)");
        return $stmt->execute([$name, $description]);
    }


    public function update($id, $name, $description) {
        $stmt = $this->db->prepare("UPDATE items SET name = ?, description = ? WHERE id = ?");
        return $stmt->execute([$name, $description, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM items WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>