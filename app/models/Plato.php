<?php
class Plato {
    private $db;

    public function __construct($db) { 
        $this->db = $db; 
    }

    public function getAll($activeOnly = false, $region = null) {
        $sql = "SELECT pl.*, pr.nombre AS provincia FROM platos pl LEFT JOIN provincias pr ON pr.id = pl.provincia_id WHERE 1=1";
        $params = [];

        if ($activeOnly) {
            $sql .= " AND pl.activo = 1";
        }

        if ($region && in_array($region, ['Costa', 'Sierra', 'Amazonía'])) {
            $sql .= " AND pl.region = ?";
            $params[] = $region;
        }

        $sql .= " ORDER BY pl.id ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM platos WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Nombre único (sin distinguir mayúsculas), ignorando el propio registro al editar
    public function nombreExiste($nombre, $exceptId = null) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM platos WHERE LOWER(nombre) = LOWER(?) AND id != ?");
        $stmt->execute([$nombre, $exceptId ?? 0]);
        return $stmt->fetchColumn() > 0;
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO platos (nombre, region, provincia_id, precio, costo, descuento_pro, activo, descripcion, ingredientes, tiempo_preparacion)
            VALUES (:nombre, :region, :provincia_id, :precio, :costo, :descuento_pro, :activo, :descripcion, :ingredientes, :tiempo_preparacion)");
        return $stmt->execute([
            ':nombre' => $data['nombre'],
            ':region' => $data['region'],
            ':provincia_id' => $data['provincia_id'],
            ':precio' => (float)$data['precio'],
            ':costo' => (float)$data['costo'],
            ':descuento_pro' => (float)($data['descuento_pro'] ?? 20.0),
            ':activo' => isset($data['activo']) ? (int)$data['activo'] : 1,
            ':descripcion' => $data['descripcion'] ?? '',
            ':ingredientes' => $data['ingredientes'] ?? '',
            ':tiempo_preparacion' => $data['tiempo_preparacion'] ?? '00:15 hrs'
        ]);
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("UPDATE platos SET 
            nombre = :nombre, 
            region = :region,
            provincia_id = :provincia_id,
            precio = :precio, 
            costo = :costo, 
            descuento_pro = :descuento_pro, 
            activo = :activo, 
            descripcion = :descripcion, 
            ingredientes = :ingredientes, 
            tiempo_preparacion = :tiempo_preparacion 
            WHERE id = :id");
        return $stmt->execute([
            ':id' => $id,
            ':nombre' => $data['nombre'],
            ':region' => $data['region'],
            ':provincia_id' => $data['provincia_id'],
            ':precio' => (float)$data['precio'],
            ':costo' => (float)$data['costo'],
            ':descuento_pro' => (float)($data['descuento_pro'] ?? 20.0),
            ':activo' => isset($data['activo']) ? (int)$data['activo'] : 1,
            ':descripcion' => $data['descripcion'] ?? '',
            ':ingredientes' => $data['ingredientes'] ?? '',
            ':tiempo_preparacion' => $data['tiempo_preparacion'] ?? '00:15 hrs'
        ]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM platos WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function countByRegion() {
        $rows = $this->db->query("SELECT region, COUNT(*) as total FROM platos WHERE activo = 1 GROUP BY region")->fetchAll(PDO::FETCH_ASSOC);
        $counts = ['Costa' => 0, 'Sierra' => 0, 'Amazonía' => 0];
        foreach ($rows as $r) {
            $counts[$r['region']] = (int)$r['total'];
        }
        return $counts;
    }
}
?>
