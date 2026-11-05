<?php
class Sucursal {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Sucursales activas (menú público y filtros del dashboard)
    public function getAll() {
        return $this->db->query("SELECT * FROM sucursales WHERE activa = 1 ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    // Listado de administración: incluye provincia, región y cantidad de pedidos
    public function getAllAdmin() {
        return $this->db->query("SELECT s.*, p.nombre AS provincia, r.nombre AS region,
                (SELECT COUNT(*) FROM pedidos WHERE sucursal_id = s.id) AS total_pedidos
            FROM sucursales s
            LEFT JOIN ciudades c ON c.id = s.ciudad_id
            LEFT JOIN provincias p ON p.id = c.provincia_id
            LEFT JOIN regiones r ON r.id = p.region_id
            ORDER BY s.id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM sucursales WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function nombreExiste($nombre, $exceptId = null) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM sucursales WHERE LOWER(nombre) = LOWER(?) AND id != ?");
        $stmt->execute([$nombre, $exceptId ?? 0]);
        return $stmt->fetchColumn() > 0;
    }

    public function tienePedidos($id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM pedidos WHERE sucursal_id = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    public function create($d) {
        $stmt = $this->db->prepare("INSERT INTO sucursales (nombre, ciudad, ciudad_id, direccion, activa) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$d['nombre'], $d['ciudad'], $d['ciudad_id'], $d['direccion'], $d['activa']]);
    }

    public function update($id, $d) {
        $stmt = $this->db->prepare("UPDATE sucursales SET nombre = ?, ciudad = ?, ciudad_id = ?, direccion = ?, activa = ? WHERE id = ?");
        return $stmt->execute([$d['nombre'], $d['ciudad'], $d['ciudad_id'], $d['direccion'], $d['activa'], $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM sucursales WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
?>
