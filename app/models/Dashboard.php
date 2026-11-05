<?php
class Dashboard {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Condición de fecha de un período. $offset = 1 devuelve el período inmediatamente anterior
    // (ayer, los 7 días previos o los 30 días previos) para las comparaciones históricas.
    private function rangoFecha($col, $periodo, $offset = 0) {
        switch ($periodo) {
            case 'hoy':
                return $offset ? "date($col) = date('now', 'localtime', '-1 day')" : "date($col) = date('now', 'localtime')";
            case 'semana':
                return $offset
                    ? "$col >= datetime('now', 'localtime', '-14 days') AND $col < datetime('now', 'localtime', '-7 days')"
                    : "$col >= datetime('now', 'localtime', '-7 days')";
            default: // mes
                return $offset
                    ? "$col >= datetime('now', 'localtime', '-60 days') AND $col < datetime('now', 'localtime', '-30 days')"
                    : "$col >= datetime('now', 'localtime', '-30 days')";
        }
    }

    // WHERE común: pedidos no cancelados, del período y (opcionalmente) de una sucursal
    private function filtro($sucursalId, $periodo, $offset = 0, $alias = '') {
        $a = $alias ? "$alias." : '';
        $where = "{$a}estado != 'Cancelado' AND " . $this->rangoFecha("{$a}fecha", $periodo, $offset);
        $params = [];
        if ($sucursalId && $sucursalId !== 'all') {
            $where .= " AND {$a}sucursal_id = :sucursal_id";
            $params[':sucursal_id'] = (int)$sucursalId;
        }
        return [$where, $params];
    }

    private function totales($sucursalId, $periodo, $offset) {
        [$where, $params] = $this->filtro($sucursalId, $periodo, $offset);
        $stmt = $this->db->prepare("SELECT COUNT(*) AS pedidos, COALESCE(SUM(total), 0) AS ventas, COALESCE(SUM(ganancia), 0) AS ganancia
            FROM pedidos WHERE $where");
        $stmt->execute($params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function variacion($actual, $anterior) {
        if ($anterior <= 0) {
            return $actual > 0 ? null : 0.0; // null = sin base de comparación
        }
        return round((($actual - $anterior) / $anterior) * 100, 1);
    }

    public function getIndicadores($sucursalId = null, $periodo = 'mes') {
        $act = $this->totales($sucursalId, $periodo, 0);
        $ant = $this->totales($sucursalId, $periodo, 1);

        // Pedidos cancelados del período (se muestran aparte, no suman a las ventas)
        $whereC = "estado = 'Cancelado' AND " . $this->rangoFecha('fecha', $periodo);
        $paramsC = [];
        if ($sucursalId && $sucursalId !== 'all') {
            $whereC .= " AND sucursal_id = :sucursal_id";
            $paramsC[':sucursal_id'] = (int)$sucursalId;
        }
        $stmtC = $this->db->prepare("SELECT COUNT(*) FROM pedidos WHERE $whereC");
        $stmtC->execute($paramsC);

        // Clientes registrados (rol usuario) segmentados por plan
        $u = $this->db->query("SELECT COUNT(*) AS total,
                COALESCE(SUM(plan = 'free'), 0) AS free, COALESCE(SUM(plan = 'pro'), 0) AS pro
            FROM users WHERE rol = 'usuario'")->fetch(PDO::FETCH_ASSOC);

        $ventas = (float)$act['ventas'];
        $ganancia = (float)$act['ganancia'];
        return [
            'ventas_totales' => $ventas,
            'ganancia_total' => $ganancia,
            'margen_porcentaje' => $ventas > 0 ? round($ganancia / $ventas * 100, 1) : 0.0,
            'pedidos_atendidos' => (int)$act['pedidos'],
            'pedidos_cancelados' => (int)$stmtC->fetchColumn(),
            'clientes_total' => (int)$u['total'],
            'clientes_free' => (int)$u['free'],
            'clientes_pro' => (int)$u['pro'],
            'var_ventas' => $this->variacion($ventas, (float)$ant['ventas']),
            'var_ganancia' => $this->variacion($ganancia, (float)$ant['ganancia']),
            'var_pedidos' => $this->variacion((int)$act['pedidos'], (int)$ant['pedidos']),
        ];
    }

    // Unidades vendidas por plato (solo pedidos no cancelados, del período y sucursal elegidos)
    public function getPlatosRanking($sucursalId = null, $periodo = 'mes') {
        [$where, $params] = $this->filtro($sucursalId, $periodo, 0, 'p');
        $stmt = $this->db->prepare("SELECT pl.id, pl.nombre, pl.region, pl.precio, pl.costo, pl.activo,
                COALESCE(SUM(v.cantidad), 0) AS unidades_vendidas,
                COALESCE(SUM(v.cantidad * v.precio_unitario), 0) AS total_ventas,
                COALESCE(SUM(v.cantidad * (v.precio_unitario - v.costo_unitario)), 0) AS ganancia_neta
            FROM platos pl
            LEFT JOIN (
                SELECT dp.plato_id, dp.cantidad, dp.precio_unitario, dp.costo_unitario
                FROM detalle_pedidos dp JOIN pedidos p ON p.id = dp.pedido_id
                WHERE $where
            ) v ON v.plato_id = pl.id
            GROUP BY pl.id
            ORDER BY unidades_vendidas DESC, pl.nombre ASC");
        $stmt->execute($params);
        $platos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($platos as &$pl) {
            $u = (int)$pl['unidades_vendidas'];
            $pl['demanda'] = $u >= 8 ? 'Alta' : ($u >= 3 ? 'Media' : 'Baja');
        }
        return $platos;
    }

    // Ventas y ganancia en el tiempo: por hora si el período es "hoy", por día en los demás
    public function getSerieTiempo($sucursalId = null, $periodo = 'mes') {
        [$where, $params] = $this->filtro($sucursalId, $periodo);
        $grupo = $periodo === 'hoy' ? "strftime('%H:00', fecha)" : "date(fecha)";
        $stmt = $this->db->prepare("SELECT $grupo AS etiqueta, SUM(total) AS ventas, SUM(ganancia) AS ganancia
            FROM pedidos WHERE $where GROUP BY etiqueta ORDER BY etiqueta ASC");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Comparación entre sucursales activas (ignora el filtro de sucursal a propósito)
    public function getVentasPorSucursal($periodo = 'mes') {
        [$where, $params] = $this->filtro(null, $periodo, 0, 'p');
        $stmt = $this->db->prepare("SELECT s.nombre, COALESCE(SUM(p.total), 0) AS ventas, COALESCE(SUM(p.ganancia), 0) AS ganancia
            FROM sucursales s LEFT JOIN pedidos p ON p.sucursal_id = s.id AND $where
            WHERE s.activa = 1 GROUP BY s.id ORDER BY s.id");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPedidosRecientes($sucursalId = null, $limite = 6) {
        $sql = "SELECT p.id, p.total, p.ganancia, p.estado, p.fecha, u.nombre AS cliente, u.plan, s.nombre AS sucursal
            FROM pedidos p LEFT JOIN users u ON u.id = p.usuario_id LEFT JOIN sucursales s ON s.id = p.sucursal_id";
        $params = [];
        if ($sucursalId && $sucursalId !== 'all') {
            $sql .= " WHERE p.sucursal_id = :sucursal_id";
            $params[':sucursal_id'] = (int)$sucursalId;
        }
        $stmt = $this->db->prepare($sql . " ORDER BY p.id DESC LIMIT " . (int)$limite);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Paquete completo que consume el dashboard (carga inicial y refresco en vivo)
    public function getPaquete($sucursalId, $periodo) {
        return [
            'indicadores' => $this->getIndicadores($sucursalId, $periodo),
            'platos' => $this->getPlatosRanking($sucursalId, $periodo),
            'serie' => $this->getSerieTiempo($sucursalId, $periodo),
            'sucursales' => $this->getVentasPorSucursal($periodo),
            'recientes' => $this->getPedidosRecientes($sucursalId),
            'generado' => date('H:i:s'),
        ];
    }
}
?>
