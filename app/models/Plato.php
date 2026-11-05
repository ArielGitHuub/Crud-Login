<?php
class Plato {
    public const REGIONES = ['Costa', 'Sierra', 'Amazonía'];

    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    private const SELECT = "SELECT pl.*, pr.nombre AS provincia, ca.nombre AS categoria
        FROM platos pl
        LEFT JOIN provincias pr ON pr.id = pl.provincia_id
        LEFT JOIN categorias ca ON ca.id = pl.categoria_id";

    /** Platos con filtros opcionales: solo activos, por región y/o por categoría. */
    public function getAll($soloActivos = false, $region = null, $categoriaId = null) {
        $sql = self::SELECT . " WHERE 1=1";
        $params = [];

        if ($soloActivos) {
            $sql .= " AND pl.activo = 1";
        }
        if ($region && in_array($region, self::REGIONES, true)) {
            $sql .= " AND pl.region = ?";
            $params[] = $region;
        }
        if ($categoriaId) {
            $sql .= " AND pl.categoria_id = ?";
            $params[] = (int)$categoriaId;
        }

        $stmt = $this->db->prepare($sql . " ORDER BY pl.id ASC");
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $stmt = $this->db->prepare(self::SELECT . " WHERE pl.id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Los más pedidos (para el carrusel de inicio); si aún no hay ventas, los primeros del menú. */
    public function getDestacados($limite = 4) {
        $stmt = $this->db->prepare(self::SELECT . "
            LEFT JOIN (
                SELECT dp.plato_id, SUM(dp.cantidad) AS vendidos
                FROM detalle_pedidos dp JOIN pedidos p ON p.id = dp.pedido_id
                WHERE p.estado != 'Cancelado' GROUP BY dp.plato_id
            ) v ON v.plato_id = pl.id
            WHERE pl.activo = 1 AND pl.imagen IS NOT NULL
            ORDER BY COALESCE(v.vendidos, 0) DESC, pl.id ASC LIMIT " . (int)$limite);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** Conteo y foto representativa por región y por categoría (mosaico del menú). */
    public function getResumen() {
        $resumen = ['regiones' => [], 'categorias' => []];

        // Foto de cada región: su plato más caro (el "estandarte"); de cada categoría: el más económico.
        // Así las fotos del mosaico no se repiten entre regiones y categorías.
        foreach ($this->db->query("SELECT r.nombre, COUNT(p.id) AS total,
                (SELECT imagen FROM platos x WHERE x.region = r.nombre AND x.activo = 1 AND x.imagen IS NOT NULL
                 ORDER BY x.precio DESC, x.id LIMIT 1) AS imagen
            FROM regiones r LEFT JOIN platos p ON p.region = r.nombre AND p.activo = 1
            GROUP BY r.id ORDER BY r.id") as $fila) {
            $resumen['regiones'][$fila['nombre']] = $fila;
        }
        foreach ($this->db->query("SELECT c.id, c.nombre, COUNT(p.id) AS total,
                (SELECT imagen FROM platos x WHERE x.categoria_id = c.id AND x.activo = 1 AND x.imagen IS NOT NULL
                 ORDER BY x.precio ASC, x.id LIMIT 1) AS imagen
            FROM categorias c LEFT JOIN platos p ON p.categoria_id = c.id AND p.activo = 1
            GROUP BY c.id ORDER BY c.id") as $fila) {
            $resumen['categorias'][] = $fila;
        }
        return $resumen;
    }

    // Nombre único (sin distinguir mayúsculas), ignorando el propio registro al editar
    public function nombreExiste($nombre, $exceptId = null) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM platos WHERE LOWER(nombre) = LOWER(?) AND id != ?");
        $stmt->execute([$nombre, $exceptId ?? 0]);
        return $stmt->fetchColumn() > 0;
    }

    private function parametros(array $d): array {
        return [
            ':nombre' => $d['nombre'],
            ':region' => $d['region'],
            ':provincia_id' => $d['provincia_id'],
            ':categoria_id' => $d['categoria_id'],
            ':precio' => (float)$d['precio'],
            ':costo' => (float)$d['costo'],
            ':descuento_pro' => (float)($d['descuento_pro'] ?? 20.0),
            ':activo' => isset($d['activo']) ? (int)$d['activo'] : 1,
            ':descripcion' => $d['descripcion'] ?? '',
            ':ingredientes' => $d['ingredientes'] ?? '',
            ':tiempo_preparacion' => $d['tiempo_preparacion'] ?? '00:15 hrs',
            ':imagen' => $d['imagen'] ?? null,
        ];
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO platos
            (nombre, region, provincia_id, categoria_id, precio, costo, descuento_pro, activo, descripcion, ingredientes, tiempo_preparacion, imagen)
            VALUES (:nombre, :region, :provincia_id, :categoria_id, :precio, :costo, :descuento_pro, :activo, :descripcion, :ingredientes, :tiempo_preparacion, :imagen)");
        return $stmt->execute($this->parametros($data));
    }

    public function update($id, $data) {
        // Sin foto nueva (imagen = null) se conserva la actual
        $stmt = $this->db->prepare("UPDATE platos SET
                nombre = :nombre, region = :region, provincia_id = :provincia_id, categoria_id = :categoria_id,
                precio = :precio, costo = :costo, descuento_pro = :descuento_pro, activo = :activo,
                descripcion = :descripcion, ingredientes = :ingredientes, tiempo_preparacion = :tiempo_preparacion,
                imagen = COALESCE(:imagen, imagen)
            WHERE id = :id");
        return $stmt->execute($this->parametros($data) + [':id' => $id]);
    }

    public function delete($id) {
        // Un plato con ventas no se borra: se oculta del menú para no romper el historial de pedidos
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM detalle_pedidos WHERE plato_id = ?");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            $this->db->prepare("UPDATE platos SET activo = 0 WHERE id = ?")->execute([$id]);
            return false;
        }
        return $this->db->prepare("DELETE FROM platos WHERE id = ?")->execute([$id]);
    }
}
