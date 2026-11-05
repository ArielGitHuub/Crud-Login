<?php
class Sucursal {
    private $db;

    public function __construct($db) { 
        $this->db = $db; 
    }

    public function getAll() {
        return $this->db->query("SELECT * FROM sucursales WHERE activa = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM sucursales WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
