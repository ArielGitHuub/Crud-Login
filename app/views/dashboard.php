<?php
$pageTitle = 'Dashboard';
$active = 'dashboard';
$maxUnidades = max(1, ...array_map(fn($p) => (int)$p['unidades_vendidas'], $platos ?: [['unidades_vendidas' => 1]]));
require 'app/views/partials/admin_top.php';
?>

<div class="page-head">
    <div>
        <h1>Dashboard</h1>
        <p>Ventas, ganancias y demanda del menú, calculadas sobre pedidos no cancelados.</p>
    </div>
    <form method="GET" action="index.php" class="filters">
        <input type="hidden" name="action" value="dashboard">
        <div class="field">
            <label for="fSucursal">Sucursal</label>
            <select class="select" id="fSucursal" name="sucursal_id">
                <option value="all" <?= ($sucursalId === 'all') ? 'selected' : '' ?>>Todas (consolidado)</option>
                <?php foreach ($sucursales as $suc): ?>
                    <option value="<?= $suc['id'] ?>" <?= ($sucursalId == $suc['id']) ? 'selected' : '' ?>><?= htmlspecialchars($suc['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="fPeriodo">Período</label>
            <select class="select" id="fPeriodo" name="periodo">
                <option value="mes" <?= ($periodo === 'mes') ? 'selected' : '' ?>>Mes (últimos 30 días)</option>
                <option value="semana" <?= ($periodo === 'semana') ? 'selected' : '' ?>>Semana (últimos 7 días)</option>
                <option value="hoy" <?= ($periodo === 'hoy') ? 'selected' : '' ?>>Hoy</option>
            </select>
        </div>
        <button type="submit" class="btn primary">Aplicar</button>
    </form>
</div>

<div class="kpis">
    <div class="kpi">
        <div class="label">Ventas totales</div>
        <div class="value">$<?= number_format($indicadores['ventas_totales'], 2) ?></div>
        <div class="note">Suma de pedidos no cancelados</div>
    </div>
    <div class="kpi profit">
        <div class="label">Ganancia total</div>
        <div class="value">$<?= number_format($indicadores['ganancia_total'], 2) ?></div>
        <div class="note">Margen neto: <?= $indicadores['margen_porcentaje'] ?>%</div>
    </div>
    <div class="kpi">
        <div class="label">Clientes registrados</div>
        <div class="value"><?= $indicadores['clientes_total'] ?></div>
        <div class="note"><?= $indicadores['clientes_free'] ?> Free · <?= $indicadores['clientes_pro'] ?> Pro</div>
    </div>
    <div class="kpi">
        <div class="label">Pedidos atendidos</div>
        <div class="value"><?= $indicadores['pedidos_atendidos'] ?></div>
        <div class="note"><?= $indicadores['pedidos_cancelados'] ?> cancelados excluidos</div>
    </div>
</div>

<section class="card">
    <div class="card-head">
        <div>
            <h2>Demanda del menú</h2>
            <p>Platos ordenados por unidades vendidas, con su rentabilidad.</p>
        </div>
        <a class="btn small" href="index.php?action=platos">Gestionar catálogo</a>
    </div>
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr>
                    <th>#</th><th>Plato</th><th>Región</th><th>Unidades</th>
                    <th class="num">Precio</th><th class="num">Costo</th><th class="num">Ventas</th><th class="num">Ganancia</th><th>Demanda</th>
                </tr>
            </thead>
            <tbody>
                <?php $pos = 1; foreach ($platos as $pl): ?>
                    <tr>
                        <td class="muted"><?= $pos++ ?></td>
                        <td><b><?= htmlspecialchars($pl['nombre']) ?></b></td>
                        <td><span class="tag <?= strtolower(strtr($pl['region'], ['í' => 'i'])) ?>"><?= htmlspecialchars($pl['region']) ?></span></td>
                        <td style="min-width:140px">
                            <div style="display:flex;align-items:center;gap:10px">
                                <b style="width:26px"><?= (int)$pl['unidades_vendidas'] ?></b>
                                <div class="meter" style="flex:1"><i style="width:<?= round($pl['unidades_vendidas'] / $maxUnidades * 100) ?>%"></i></div>
                            </div>
                        </td>
                        <td class="num">$<?= number_format($pl['precio'], 2) ?></td>
                        <td class="num muted">$<?= number_format($pl['costo'], 2) ?></td>
                        <td class="num">$<?= number_format($pl['total_ventas'], 2) ?></td>
                        <td class="num"><b style="color:var(--ok)">$<?= number_format($pl['ganancia_neta'], 2) ?></b></td>
                        <td class="muted"><?= htmlspecialchars($pl['demanda']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>

<?php require 'app/views/partials/admin_bottom.php'; ?>
