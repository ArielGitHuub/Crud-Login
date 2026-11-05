<?php
class Dashboard {
    private $db;

    public function __construct($db) { 
        $this->db = $db; 
    }

    public function getIndicadores($sucursalId = null, $periodo = 'mes') {
        $where = "WHERE estado != 'Cancelado'";
        $whereCancelados = "WHERE estado = 'Cancelado'";
        $params = [];

        if ($sucursalId && $sucursalId !== 'all') {
            $where .= " AND sucursal_id = :sucursal_id";
            $whereCancelados .= " AND sucursal_id = :sucursal_id";
            $params[':sucursal_id'] = $sucursalId;
        }

        if ($periodo === 'hoy') {
            $where .= " AND date(fecha) = date('now', 'localtime')";
            $whereCancelados .= " AND date(fecha) = date('now', 'localtime')";
        } elseif ($periodo === 'semana') {
            $where .= " AND fecha >= datetime('now', '-7 days', 'localtime')";
            $whereCancelados .= " AND fecha >= datetime('now', '-7 days', 'localtime')";
        } elseif ($periodo === 'mes') {
            $where .= " AND fecha >= datetime('now', '-30 days', 'localtime')";
            $whereCancelados .= " AND fecha >= datetime('now', '-30 days', 'localtime')";
        }

        // 1. Ventas Totales = Σ(Total de Pedidos No Cancelados)
        // 2. Ganancia Total = Σ(Ganancia de Pedidos No Cancelados)
        $sql = "SELECT 
                    COUNT(*) as total_pedidos,
                    COALESCE(SUM(total), 0) as ventas_totales,
                    COALESCE(SUM(costo_total), 0) as costos_totales,
                    COALESCE(SUM(ganancia), 0) as ganancia_total
                FROM pedidos $where";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $res = $stmt->fetch(PDO::FETCH_ASSOC);

        // Cancelados excluidos
        $sqlCancel = "SELECT COUNT(*) FROM pedidos $whereCancelados";
        $stmtCancel = $this->db->prepare($sqlCancel);
        $stmtCancel->execute($params);
        $cancelados = (int)$stmtCancel->fetchColumn();

        // 3. Clientes Registrados: segmentados en Free y Pro
        $sqlUsers = "SELECT 
                        COUNT(*) as total,
                        SUM(CASE WHEN plan = 'free' THEN 1 ELSE 0 END) as free,
                        SUM(CASE WHEN plan = 'pro' THEN 1 ELSE 0 END) as pro
                     FROM users WHERE rol = 'usuario'";
        $userStats = $this->db->query($sqlUsers)->fetch(PDO::FETCH_ASSOC);

        $ventas = (float)$res['ventas_totales'];
        $ganancia = (float)$res['ganancia_total'];
        $margen = $ventas > 0 ? round(($ganancia / $ventas) * 100, 1) : 0.0;

        return [
            'ventas_totales' => $ventas,
            'ganancia_total' => $ganancia,
            'margen_porcentaje' => $margen,
            'pedidos_atendidos' => (int)$res['total_pedidos'],
            'pedidos_cancelados' => $cancelados,
            'clientes_total' => (int)($userStats['total'] ?? 0),
            'clientes_free' => (int)($userStats['free'] ?? 0),
            'clientes_pro' => (int)($userStats['pro'] ?? 0),
        ];
    }

    public function getPlatosRanking($sucursalId = null, $periodo = 'mes') {
        $where = "WHERE p.estado != 'Cancelado'";
        $params = [];

        if ($sucursalId && $sucursalId !== 'all') {
            $where .= " AND p.sucursal_id = :sucursal_id";
            $params[':sucursal_id'] = $sucursalId;
        }

        if ($periodo === 'hoy') {
            $where .= " AND date(p.fecha) = date('now', 'localtime')";
        } elseif ($periodo === 'semana') {
            $where .= " AND p.fecha >= datetime('now', '-7 days', 'localtime')";
        } elseif ($periodo === 'mes') {
            $where .= " AND p.fecha >= datetime('now', '-30 days', 'localtime')";
        }

        // Suma de unidades vendidas agrupadas por plato, ordenadas desc
        $sql = "SELECT 
                    pl.id,
                    pl.nombre,
                    pl.region,
                    pl.precio,
                    pl.costo,
                    COALESCE(SUM(dp.cantidad), 0) as unidades_vendidas,
                    COALESCE(SUM(dp.cantidad * dp.precio_unitario), 0) as total_ventas,
                    COALESCE(SUM(dp.cantidad * (dp.precio_unitario - dp.costo_unitario)), 0) as ganancia_neta
                FROM platos pl
                LEFT JOIN detalle_pedidos dp ON pl.id = dp.plato_id
                LEFT JOIN pedidos p ON dp.pedido_id = p.id AND (p.estado != 'Cancelado' OR p.estado IS NULL)
                GROUP BY pl.id
                ORDER BY unidades_vendidas DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $platos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Asignar nivel de demanda según unidades
        foreach ($platos as &$pl) {
            $u = (int)$pl['unidades_vendidas'];
            if ($u >= 8) {
                $pl['demanda'] = 'Alta';
                $pl['demanda_badge'] = 'bg-emerald-50 text-emerald-700 border-emerald-200';
            } elseif ($u >= 3) {
                $pl['demanda'] = 'Media';
                $pl['demanda_badge'] = 'bg-amber-50 text-amber-700 border-amber-200';
            } else {
                $pl['demanda'] = 'Baja';
                $pl['demanda_badge'] = 'bg-rose-50 text-rose-700 border-rose-200';
            }
        }

        return $platos;
    }
}
?>
