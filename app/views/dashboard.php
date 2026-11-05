<?php
$pageTitle = 'Dashboard';
$active = 'dashboard';
$etiquetaPeriodo = ['hoy' => 'hoy', 'semana' => 'los últimos 7 días', 'mes' => 'los últimos 30 días'][$periodo];
$comparado = ['hoy' => 'ayer', 'semana' => 'la semana anterior', 'mes' => 'el mes anterior'][$periodo];
require 'app/views/partials/admin_top.php';
?>

<div class="page-head">
    <div>
        <h1>Dashboard</h1>
        <p>Resumen de <?= $etiquetaPeriodo ?>. Se actualiza solo cada pocos segundos.</p>
    </div>
    <form method="GET" action="index.php" class="filters">
        <input type="hidden" name="action" value="dashboard">
        <div class="field">
            <label for="fSucursal">Sucursal</label>
            <select class="select" id="fSucursal" name="sucursal_id" onchange="this.form.submit()">
                <option value="all" <?= ($sucursalId === 'all') ? 'selected' : '' ?>>Todas (consolidado)</option>
                <?php foreach ($sucursales as $suc): ?>
                    <option value="<?= $suc['id'] ?>" <?= ($sucursalId === (int)$suc['id']) ? 'selected' : '' ?>><?= htmlspecialchars($suc['nombre']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field">
            <label for="fPeriodo">Período</label>
            <select class="select" id="fPeriodo" name="periodo" onchange="this.form.submit()">
                <option value="hoy" <?= ($periodo === 'hoy') ? 'selected' : '' ?>>Hoy</option>
                <option value="semana" <?= ($periodo === 'semana') ? 'selected' : '' ?>>Últimos 7 días</option>
                <option value="mes" <?= ($periodo === 'mes') ? 'selected' : '' ?>>Últimos 30 días</option>
            </select>
        </div>
    </form>
</div>

<div style="margin:-10px 0 18px"><span class="live" id="live"><i></i><span id="liveText">Conectando…</span></span></div>

<div class="kpis">
    <div class="kpi">
        <div class="label">Ventas</div>
        <div class="value" id="kVentas">—</div>
        <div class="note"><span id="dVentas"></span></div>
    </div>
    <div class="kpi profit">
        <div class="label">Ganancia</div>
        <div class="value" id="kGanancia">—</div>
        <div class="note"><span id="dGanancia"></span> · margen <span id="kMargen">—</span></div>
    </div>
    <div class="kpi">
        <div class="label">Pedidos atendidos</div>
        <div class="value" id="kPedidos">—</div>
        <div class="note"><span id="dPedidos"></span> · <span id="kCancel">0</span> cancelados</div>
    </div>
    <div class="kpi">
        <div class="label">Clientes registrados</div>
        <div class="value" id="kClientes">—</div>
        <div class="note"><span id="kFree">0</span> Free · <span id="kPro">0</span> Pro</div>
    </div>
</div>

<div class="charts">
    <div class="chart-card wide">
        <h3>¿Cuánto vendemos y cuánto ganamos?</h3>
        <p class="why">Cada grupo de barras es un <?= $periodo === 'hoy' ? 'hora' : 'día' ?>. La barra dorada es lo que queda después de restar el costo de los platos.</p>
        <div class="chart-box"><canvas id="cSerie"></canvas></div>
    </div>
    <div class="chart-card">
        <h3>Platos más pedidos</h3>
        <p class="why">Los 5 con más unidades vendidas: los que más se piden.</p>
        <div class="chart-box"><canvas id="cTop"></canvas></div>
    </div>
    <div class="chart-card">
        <h3>Platos menos pedidos</h3>
        <p class="why">Los 5 con menos unidades (sin barra = ninguna venta): candidatos a promoción.</p>
        <div class="chart-box"><canvas id="cBottom"></canvas></div>
    </div>
    <div class="chart-card">
        <h3>Clientes con y sin plan</h3>
        <p class="why">Cuántos clientes pagan el plan Pro frente a los que usan la cuenta gratuita.</p>
        <div class="chart-box"><canvas id="cClientes"></canvas></div>
    </div>
    <div class="chart-card">
        <h3>Ventas por sucursal</h3>
        <p class="why">Compara qué local vende más en el mismo período.</p>
        <div class="chart-box"><canvas id="cSucursales"></canvas></div>
    </div>
</div>

<section class="card">
    <div class="card-head">
        <div><h2>Últimos pedidos</h2><p>Aparecen aquí en cuanto un cliente pide desde el menú.</p></div>
    </div>
    <div class="card-body"><ul class="feed" id="feed"></ul></div>
</section>

<section class="card">
    <div class="card-head">
        <div><h2>Detalle por plato</h2><p>Unidades, ventas y ganancia de cada plato en el período.</p></div>
        <a class="btn small" href="index.php?action=platos">Gestionar catálogo</a>
    </div>
    <div class="table-wrap">
        <table class="data">
            <thead>
                <tr><th>#</th><th>Plato</th><th>Región</th><th class="num">Unidades</th><th class="num">Precio</th><th class="num">Costo</th><th class="num">Ventas</th><th class="num">Ganancia</th><th>Demanda</th></tr>
            </thead>
            <tbody id="tablaPlatos"></tbody>
        </table>
    </div>
</section>

<script src="assets/js/chart.umd.min.js"></script>
<script>
    const FILTROS = new URLSearchParams({ action: 'dashboard_data', sucursal_id: <?= json_encode((string)$sucursalId) ?>, periodo: <?= json_encode($periodo) ?> });
    const ES_HORA = <?= json_encode($periodo === 'hoy') ?>;
    const COMPARADO = <?= json_encode($comparado) ?>;
    let datos = <?= json_encode($paquete, JSON_UNESCAPED_UNICODE) ?>;

    const C = { marca: '#1f5a3f', oro: '#e8a92e', suave: '#5c6b62', costa: '#2a8a99', sierra: '#c4852a', amazonia: '#4f9a45', claro: '#e6e2cf' };
    Chart.defaults.font.family = "'Inter', system-ui, sans-serif";
    Chart.defaults.color = C.suave;
    Chart.defaults.maintainAspectRatio = false;

    const dinero = n => '$' + Number(n).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const colorRegion = r => r === 'Costa' ? C.costa : (r === 'Sierra' ? C.sierra : C.amazonia);
    const esc = s => String(s).replace(/[&<>"']/g, c => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    function delta(el, v) {
        if (v === null) { el.className = 'delta flat'; el.textContent = 'sin base para comparar'; return; }
        if (v === 0) { el.className = 'delta flat'; el.textContent = 'igual que ' + COMPARADO; return; }
        el.className = 'delta ' + (v > 0 ? 'up' : 'down');
        el.textContent = (v > 0 ? '▲ ' : '▼ ') + Math.abs(v) + '% vs ' + COMPARADO;
    }

    const ejeDinero = { beginAtZero: true, grid: { color: C.claro }, ticks: { callback: v => '$' + v } };
    const sinGrid = { grid: { display: false } };
    const mk = (id, cfg) => new Chart(document.getElementById(id), cfg);

    const gSerie = mk('cSerie', { type: 'bar', data: { labels: [], datasets: [
        { label: 'Ventas', data: [], backgroundColor: C.marca, borderRadius: 3 },
        { label: 'Ganancia', data: [], backgroundColor: C.oro, borderRadius: 3 } ] },
        options: { scales: { x: sinGrid, y: ejeDinero }, plugins: { legend: { position: 'bottom' }, tooltip: { callbacks: { label: c => c.dataset.label + ': ' + dinero(c.parsed.y) } } } } });

    const opcionesBarras = { indexAxis: 'y', scales: { x: { beginAtZero: true, grid: { color: C.claro }, ticks: { precision: 0 } }, y: sinGrid },
        plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => c.parsed.x + ' unidades' } } } };
    const gTop = mk('cTop', { type: 'bar', data: { labels: [], datasets: [{ data: [], backgroundColor: [], borderRadius: 3 }] }, options: opcionesBarras });
    const gBottom = mk('cBottom', { type: 'bar', data: { labels: [], datasets: [{ data: [], backgroundColor: [], borderRadius: 3 }] }, options: opcionesBarras });

    const gClientes = mk('cClientes', { type: 'doughnut', data: { labels: ['Sin plan (Free)', 'Con plan Pro'], datasets: [{ data: [0, 0], backgroundColor: ['#cfcbb0', C.marca], borderWidth: 0 }] },
        options: { cutout: '62%', plugins: { legend: { position: 'bottom' } } } });

    const gSuc = mk('cSucursales', { type: 'bar', data: { labels: [], datasets: [{ label: 'Ventas', data: [], backgroundColor: C.marca, borderRadius: 3 }] },
        options: { scales: { x: sinGrid, y: ejeDinero }, plugins: { legend: { display: false }, tooltip: { callbacks: { label: c => 'Ventas: ' + dinero(c.parsed.y) } } } } });

    let ultimoPedido = null;

    function pintar(d) {
        const k = d.indicadores;
        kVentas.textContent = dinero(k.ventas_totales);
        kGanancia.textContent = dinero(k.ganancia_total);
        kMargen.textContent = k.margen_porcentaje + '%';
        kPedidos.textContent = k.pedidos_atendidos;
        kCancel.textContent = k.pedidos_cancelados;
        kClientes.textContent = k.clientes_total;
        kFree.textContent = k.clientes_free;
        kPro.textContent = k.clientes_pro;
        delta(dVentas, k.var_ventas); delta(dGanancia, k.var_ganancia); delta(dPedidos, k.var_pedidos);

        gSerie.data.labels = d.serie.map(s => ES_HORA ? s.etiqueta : s.etiqueta.slice(8, 10) + '/' + s.etiqueta.slice(5, 7));
        gSerie.data.datasets[0].data = d.serie.map(s => +s.ventas);
        gSerie.data.datasets[1].data = d.serie.map(s => +s.ganancia);
        gSerie.update();

        const activos = d.platos.filter(p => +p.activo === 1);
        const top = activos.slice(0, 5);
        const bottom = activos.slice(-5).reverse();
        [[gTop, top], [gBottom, bottom]].forEach(([g, lista]) => {
            g.data.labels = lista.map(p => p.nombre.length > 26 ? p.nombre.slice(0, 25) + '…' : p.nombre);
            g.data.datasets[0].data = lista.map(p => +p.unidades_vendidas);
            g.data.datasets[0].backgroundColor = lista.map(p => colorRegion(p.region));
            g.update();
        });

        gClientes.data.datasets[0].data = [k.clientes_free, k.clientes_pro];
        gClientes.update();

        gSuc.data.labels = d.sucursales.map(s => s.nombre.replace('Sucursal ', ''));
        gSuc.data.datasets[0].data = d.sucursales.map(s => +s.ventas);
        gSuc.update();

        const feed = document.getElementById('feed');
        const nuevo = d.recientes.length && d.recientes[0].id !== ultimoPedido;
        feed.innerHTML = d.recientes.length ? d.recientes.map(p =>
            `<li><span><b>Pedido N.º ${p.id}</b> · ${esc(p.cliente || 'Cliente')} ${p.plan === 'pro' ? '(Pro)' : ''}<small>${esc(p.sucursal || '')} · ${esc(p.fecha)}${p.estado === 'Cancelado' ? ' · cancelado' : ''}</small></span>
             <span style="text-align:right"><b>${dinero(p.total)}</b><small>ganancia ${dinero(p.ganancia)}</small></span></li>`).join('')
            : '<li>Aún no hay pedidos.</li>';
        feed.classList.toggle('new', !!(nuevo && ultimoPedido !== null));
        ultimoPedido = d.recientes.length ? d.recientes[0].id : null;

        document.getElementById('tablaPlatos').innerHTML = d.platos.map((p, i) =>
            `<tr><td class="muted">${i + 1}</td><td><b>${esc(p.nombre)}</b></td>
             <td><span class="tag ${p.region.toLowerCase().replace('í', 'i')}">${esc(p.region)}</span></td>
             <td class="num"><b>${p.unidades_vendidas}</b></td><td class="num">${dinero(p.precio)}</td><td class="num muted">${dinero(p.costo)}</td>
             <td class="num">${dinero(p.total_ventas)}</td><td class="num"><b style="color:var(--ok)">${dinero(p.ganancia_neta)}</b></td><td class="muted">${p.demanda}</td></tr>`).join('');
    }

    const live = document.getElementById('live'), liveText = document.getElementById('liveText');
    async function refrescar() {
        if (document.hidden) return;
        try {
            const res = await fetch('index.php?' + FILTROS.toString(), { cache: 'no-store' });
            if (!res.ok) throw new Error(res.status);
            datos = await res.json();
            pintar(datos);
            live.classList.remove('off');
            liveText.textContent = 'En vivo · actualizado a las ' + datos.generado;
        } catch (e) {
            live.classList.add('off');
            liveText.textContent = 'Sin conexión con el servidor. Reintentando…';
        }
    }
    pintar(datos);
    liveText.textContent = 'En vivo · actualizado a las ' + datos.generado;
    setInterval(refrescar, 4000);
</script>

<?php require 'app/views/partials/admin_bottom.php'; ?>
