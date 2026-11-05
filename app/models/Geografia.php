<?php
class Geografia {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getRegiones() {
        return $this->db->query("SELECT id, nombre FROM regiones ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProvinciasByRegion($regionId) {
        $stmt = $this->db->prepare("SELECT id, nombre FROM provincias WHERE region_id = ? ORDER BY nombre ASC");
        $stmt->execute([$regionId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCiudadesByProvincia($provinciaId) {
        $stmt = $this->db->prepare("SELECT id, nombre FROM ciudades WHERE provincia_id = ? ORDER BY nombre ASC");
        $stmt->execute([$provinciaId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Ciudad con su provincia y región, o false si no existe
    public function getCiudad($ciudadId) {
        $stmt = $this->db->prepare("SELECT c.id, c.nombre, c.provincia_id, p.region_id
            FROM ciudades c JOIN provincias p ON p.id = c.provincia_id WHERE c.id = ?");
        $stmt->execute([$ciudadId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Devuelve la provincia junto con el nombre de su región, o false si no existe
    public function getProvincia($provinciaId) {
        $stmt = $this->db->prepare("SELECT p.id, p.nombre, p.region_id, r.nombre AS region
            FROM provincias p JOIN regiones r ON r.id = p.region_id WHERE p.id = ?");
        $stmt->execute([$provinciaId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>
