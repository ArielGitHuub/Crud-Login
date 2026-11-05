<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrativo Base - Fuego Andino</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 flex justify-center">

    <div class="w-full max-w-5xl bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">
        
        <!-- Top Navigation Bar (Matching Wireframe 1) -->
        <header class="bg-white border-b border-slate-200 px-8 py-3.5 flex items-center justify-between">
            <div class="flex items-center space-x-8">
                <!-- Brand / Logo -->
                <div class="flex items-center space-x-2">
                    <span class="text-xl">🔥</span>
                    <span class="font-bold text-slate-800 text-base tracking-tight">Fuego Andino</span>
                    <span class="text-xs bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-medium border border-slate-200">Admin</span>
                </div>

                <!-- Nav links -->
                <nav class="flex items-center space-x-6 text-sm">
                    <a href="index.php?action=dashboard" class="flex items-center space-x-2 text-blue-600 font-semibold border-b-2 border-blue-600 pb-1 -mb-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="index.php?action=platos" class="flex items-center space-x-2 text-slate-500 hover:text-slate-800 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Catálogo de Platos</span>
                    </a>
                    <a href="index.php?action=menu" class="flex items-center space-x-2 text-slate-500 hover:text-slate-800 font-medium" target="_blank">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        <span>Ver Menú Público</span>
                    </a>
                </nav>
            </div>

            <!-- User Avatar & Dropdown -->
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-semibold text-xs flex items-center justify-center shadow-sm">
                    AT
                </div>
                <div class="text-left hidden sm:block">
                    <p class="text-xs font-semibold text-slate-800"><?= htmlspecialchars($_SESSION['nombre'] ?? 'Ariel Tejada') ?></p>
                    <p class="text-[10px] text-slate-500">Administrador General</p>
                </div>
                <a href="index.php?action=logout" class="text-xs text-rose-600 hover:text-rose-800 font-medium ml-2">Salir</a>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="p-8 space-y-6">
            
            <!-- Page Header & Filters -->
            <form method="GET" action="index.php" class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 pb-2 border-b border-slate-100">
                <input type="hidden" name="action" value="dashboard">
                
                <div>
                    <h1 class="text-xl font-bold text-slate-800">Dashboard Administrativo Base</h1>
                    <p class="text-xs text-slate-500 mt-0.5">Indicadores de rentabilidad y demanda del menú calculados en tiempo real</p>
                </div>

                <!-- Interactive Filters -->
                <div class="flex flex-wrap items-center gap-3">
                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Sucursal</label>
                        <select name="sucursal_id" class="text-xs border border-slate-300 rounded-lg px-3 py-1.5 bg-white text-slate-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="all" <?= ($sucursalId === 'all') ? 'selected' : '' ?>>Todas las sucursales (Consolidado)</option>
                            <?php foreach ($sucursales as $suc): ?>
                                <option value="<?= $suc['id'] ?>" <?= ($sucursalId == $suc['id']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($suc['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[11px] font-medium text-slate-500 mb-1">Período</label>
                        <select name="periodo" class="text-xs border border-slate-300 rounded-lg px-3 py-1.5 bg-white text-slate-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <option value="mes" <?= ($periodo === 'mes') ? 'selected' : '' ?>>Este Mes (Últimos 30 días)</option>
                            <option value="semana" <?= ($periodo === 'semana') ? 'selected' : '' ?>>Esta Semana (Últimos 7 días)</option>
                            <option value="hoy" <?= ($periodo === 'hoy') ? 'selected' : '' ?>>Hoy</option>
                        </select>
                    </div>

                    <div class="pt-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-medium px-4 py-1.5 rounded-lg transition shadow-sm flex items-center space-x-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                            <span>Filtrar</span>
                        </button>
                    </div>
                </div>
            </form>

            <!-- KPI Metric Cards (Calculated directly from SQLite Database) -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                
                <!-- KPI 1: Ventas Totales -->
                <div class="bg-white rounded-lg border border-slate-200 p-4 shadow-sm hover:border-slate-300 transition">
                    <div class="flex items-center justify-between text-slate-500 mb-2">
                        <span class="text-xs font-medium">Ventas Totales</span>
                        <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-slate-800">$ <?= number_format($indicadores['ventas_totales'], 2) ?></div>
                    <p class="text-[11px] text-slate-400 mt-1">Σ(Pedidos no cancelados)</p>
                </div>

                <!-- KPI 2: Ganancia Total -->
                <div class="bg-white rounded-lg border border-slate-200 p-4 shadow-sm hover:border-slate-300 transition">
                    <div class="flex items-center justify-between text-slate-500 mb-2">
                        <span class="text-xs font-medium">Ganancia Total (Core)</span>
                        <div class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-emerald-600">$ <?= number_format($indicadores['ganancia_total'], 2) ?></div>
                    <p class="text-[11px] text-slate-400 mt-1">Margen neto: <?= $indicadores['margen_porcentaje'] ?>%</p>
                </div>

                <!-- KPI 3: Clientes Registrados -->
                <div class="bg-white rounded-lg border border-slate-200 p-4 shadow-sm hover:border-slate-300 transition">
                    <div class="flex items-center justify-between text-slate-500 mb-2">
                        <span class="text-xs font-medium">Clientes Registrados</span>
                        <div class="w-7 h-7 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-slate-800"><?= $indicadores['clientes_total'] ?></div>
                    <div class="flex items-center space-x-2 mt-1 text-[11px]">
                        <span class="text-slate-500 font-medium"><?= $indicadores['clientes_free'] ?> Free</span>
                        <span class="text-slate-300">•</span>
                        <span class="text-amber-600 font-semibold"><?= $indicadores['clientes_pro'] ?> Pro</span>
                    </div>
                </div>

                <!-- KPI 4: Pedidos Atendidos -->
                <div class="bg-white rounded-lg border border-slate-200 p-4 shadow-sm hover:border-slate-300 transition">
                    <div class="flex items-center justify-between text-slate-500 mb-2">
                        <span class="text-xs font-medium">Pedidos Atendidos</span>
                        <div class="w-7 h-7 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        </div>
                    </div>
                    <div class="text-2xl font-bold text-slate-800"><?= $indicadores['pedidos_atendidos'] ?></div>
                    <p class="text-[11px] text-slate-400 mt-1"><?= $indicadores['pedidos_cancelados'] ?> cancelados excluidos</p>
                </div>
            </div>

            <!-- Tabular Breakdown: Platos Más y Menos Consumidos -->
            <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm">
                <div class="px-5 py-3.5 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Demanda del Menú: Platos Más y Menos Consumidos</h2>
                        <p class="text-[11px] text-slate-500">Unidades vendidas y rentabilidad real calculada por plato</p>
                    </div>
                    <a href="index.php?action=platos" class="text-xs text-blue-600 font-medium hover:underline">Gestionar catálogo →</a>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/75 text-slate-600 border-b border-slate-200 font-semibold">
                                <th class="py-2.5 px-4">#</th>
                                <th class="py-2.5 px-4">Plato</th>
                                <th class="py-2.5 px-4">Región</th>
                                <th class="py-2.5 px-4 text-center">Uds. Vendidas</th>
                                <th class="py-2.5 px-4 text-right">Precio Venta</th>
                                <th class="py-2.5 px-4 text-right">Costo Elab.</th>
                                <th class="py-2.5 px-4 text-right">Total Ventas</th>
                                <th class="py-2.5 px-4 text-right">Ganancia Neta</th>
                                <th class="py-2.5 px-4 text-center">Nivel de Demanda</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php $pos = 1; ?>
                            <?php foreach ($platos as $pl): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-4 text-slate-400 font-medium"><?= $pos++ ?>.</td>
                                    <td class="py-3 px-4 font-semibold text-slate-900"><?= htmlspecialchars($pl['nombre']) ?></td>
                                    <td class="py-3 px-4">
                                        <?php if($pl['region'] === 'Costa'): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-cyan-50 text-cyan-700 border border-cyan-200">Costa</span>
                                        <?php elseif($pl['region'] === 'Sierra'): ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Sierra</span>
                                        <?php else: ?>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Amazonía</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="py-3 px-4 text-center font-bold text-slate-800"><?= $pl['unidades_vendidas'] ?></td>
                                    <td class="py-3 px-4 text-right">$ <?= number_format($pl['precio'], 2) ?></td>
                                    <td class="py-3 px-4 text-right text-slate-500">$ <?= number_format($pl['costo'], 2) ?></td>
                                    <td class="py-3 px-4 text-right font-medium">$ <?= number_format($pl['total_ventas'], 2) ?></td>
                                    <td class="py-3 px-4 text-right font-bold text-emerald-600">+$ <?= number_format($pl['ganancia_neta'], 2) ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium border <?= $pl['demanda_badge'] ?>">
                                            <?= $pl['demanda'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

</body>
</html>
