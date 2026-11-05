<?php
class Pedido {
    private $db;

    public function __construct($db) { 
        $this->db = $db; 
    }

    public function create($usuarioId, $sucursalId, $items, $isPro = false) {
        $subtotal = 0.0;
        $costoTotal = 0.0;

        foreach ($items as $item) {
            $subtotal += ($item['precio'] * $item['cantidad']);
            $costoTotal += ($item['costo'] * $item['cantidad']);
        }

        // Reglas de negocio Core:
        // Si usuario es Pro: 20% de descuento y costo de envío $0.00
        // Si usuario es Free: sin descuento y costo de envío fijo $2.50
        $descuento = $isPro ? ($subtotal * 0.20) : 0.0;
        $envio = $isPro ? 0.0 : 2.50;
        $total = ($subtotal - $descuento) + $envio;
        $ganancia = $total - $costoTotal;

        $stmt = $this->db->prepare("INSERT INTO pedidos (usuario_id, sucursal_id, subtotal, descuento, envio, total, costo_total, ganancia, estado, fecha) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'Entregado', datetime('now', 'localtime'))");
        $stmt->execute([$usuarioId, $sucursalId, $subtotal, $descuento, $envio, $total, $costoTotal, $ganancia]);
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
