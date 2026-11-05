<?php
class Pedido {
    private $db;

    public function __construct($db) { 
        $this->db = $db; 
    }

    // $estado y $fecha solo se pasan al generar datos de ejemplo; en un pedido real son 'Entregado' y ahora
    public function create($usuarioId, $sucursalId, $items, $isPro = false, $estado = 'Entregado', $fecha = null) {
        $subtotal = 0.0;
        $costoTotal = 0.0;

        $descuento = 0.0;

        foreach ($items as $item) {
            $linea = $item['precio'] * $item['cantidad'];
            $subtotal += $linea;
            $costoTotal += ($item['costo'] * $item['cantidad']);
            // Descuento Pro: porcentaje propio de cada plato (campo descuento_pro del catálogo)
            if ($isPro) {
                $descuento += $linea * (($item['descuento_pro'] ?? 0) / 100);
            }
        }
        $subtotal = round($subtotal, 2);
        $descuento = round($descuento, 2);

        // Reglas de negocio del Core:
        // Total = Σ(precio × cantidad) − descuento Pro + envío  (envío $0.00 para Pro, $2.50 para Free)
        // Ganancia = Total − Σ(costo × cantidad)
        $envio = $isPro ? 0.0 : 2.50;
        $total = round(($subtotal - $descuento) + $envio, 2);
        $costoTotal = round($costoTotal, 2);
        $ganancia = round($total - $costoTotal, 2);

        $stmt = $this->db->prepare("INSERT INTO pedidos (usuario_id, sucursal_id, subtotal, descuento, envio, total, costo_total, ganancia, estado, fecha) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, COALESCE(?, datetime('now', 'localtime')))");
        $stmt->execute([$usuarioId, $sucursalId, $subtotal, $descuento, $envio, $total, $costoTotal, $ganancia, $estado, $fecha]);
        $pedidoId = $this->db->lastInsertId();

        $stmtDet = $this->db->prepare("INSERT INTO detalle_pedidos (pedido_id, plato_id, cantidad, precio_unitario, costo_unitario) 
            VALUES (?, ?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmtDet->execute([$pedidoId, $item['plato_id'], $item['cantidad'], $item['precio'], $item['costo']]);
        }

        return $pedidoId;
    }

    public function getAll($sucursalId = null, $periodo = null) {
        $sql = "SELECT p.*, u.nombre as cliente_nombre, u.plan as cliente_plan, s.nombre as sucursal_nombre 
                FROM pedidos p 
                LEFT JOIN users u ON p.usuario_id = u.id 
                LEFT JOIN sucursales s ON p.sucursal_id = s.id 
                WHERE 1=1";
        $params = [];

        if ($sucursalId && $sucursalId !== 'all') {
            $sql .= " AND p.sucursal_id = ?";
            $params[] = $sucursalId;
        }

        if ($periodo === 'hoy') {
            $sql .= " AND date(p.fecha) = date('now', 'localtime')";
        } elseif ($periodo === 'semana') {
            $sql .= " AND p.fecha >= datetime('now', '-7 days', 'localtime')";
        } elseif ($periodo === 'mes') {
            $sql .= " AND p.fecha >= datetime('now', '-30 days', 'localtime')";
        }

        $sql .= " ORDER BY p.fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
