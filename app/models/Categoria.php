<?php
class Categoria {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll() {
        return $this->db->query("SELECT id, nombre FROM categorias ORDER BY id")->fetchAll();
    }

    public function existe($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM categorias WHERE id = ?");
        $stmt->execute([(int)$id]);
        return $stmt->fetchColumn() > 0;
    }
}
