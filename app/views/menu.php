<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú por Región - Fuego Andino</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-100 min-h-screen py-8 px-4 flex justify-center">

    <div class="w-full max-w-5xl bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">
        
        <!-- Top Navigation Bar (Matching Wireframe 2) -->
        <header class="bg-white border-b border-slate-200 px-8 py-3.5 flex items-center justify-between">
            <div class="flex items-center space-x-8">
                <!-- Brand / Logo -->
                <div class="flex items-center space-x-2">
                    <span class="text-xl">🔥</span>
                    <span class="font-bold text-slate-800 text-base tracking-tight">Fuego Andino</span>
                    <span class="text-xs text-amber-700 bg-amber-50 px-2 py-0.5 rounded font-semibold border border-amber-200">100% Ecuatoriano</span>
                </div>

                <!-- Nav links -->
                <nav class="flex items-center space-x-6 text-sm">
                    <a href="index.php?action=menu" class="flex items-center space-x-2 text-blue-600 font-semibold border-b-2 border-blue-600 pb-1 -mb-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        <span>Menú por Región</span>
                    </a>
                    <?php if(($userRol ?? '') === 'admin'): ?>
                        <a href="index.php?action=dashboard" class="flex items-center space-x-2 text-slate-500 hover:text-slate-800 font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                            <span>Dashboard Admin</span>
                        </a>
                    <?php endif; ?>
                </nav>
            </div>

            <!-- User status (Pro Member) & Avatar -->
            <div class="flex items-center space-x-4">
                <?php if($isPro): ?>
                    <div class="flex items-center space-x-1.5 bg-amber-50 text-amber-800 border border-amber-200 px-2.5 py-1 rounded-full text-xs font-semibold">
                        <span class="text-amber-500">⭐</span>
                        <span>Plan Pro Activo</span>
                    </div>
                <?php else: ?>
                    <div class="flex items-center space-x-1.5 bg-slate-100 text-slate-600 border border-slate-200 px-2.5 py-1 rounded-full text-xs font-medium">
                        <span>👤 Cuenta Estándar (Free)</span>
                    </div>
                <?php endif; ?>

                <!-- User Dropdown / Switcher -->
                <div class="flex items-center space-x-2 pl-2 border-l border-slate-200">
                    <div class="w-8 h-8 rounded-full bg-blue-600 text-white font-semibold text-xs flex items-center justify-center shadow-sm">
                        <?= strtoupper(substr($_SESSION['nombre'] ?? 'Invitado', 0, 2)) ?>
                    </div>
                    <div class="text-left hidden sm:block">
                        <span class="text-xs font-semibold text-slate-800 block"><?= htmlspecialchars($_SESSION['nombre'] ?? 'Invitado') ?></span>
                        <a href="index.php?action=login" class="text-[10px] text-blue-600 hover:underline">Cambiar rol</a>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="p-8 space-y-6">

            <!-- Mensaje flash de pedido creado -->
            <?php if($mensajePedido): ?>
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-xl text-xs font-medium flex items-center justify-between shadow-xs">
                    <div class="flex items-center space-x-2">
                        <span class="text-lg">🎉</span>
                        <span><?= htmlspecialchars($mensajePedido['texto']) ?></span>
                    </div>
                    <?php if(($userRol ?? '') === 'admin'): ?>
                        <a href="index.php?action=dashboard" class="underline font-bold text-emerald-900 ml-4">Ver en Dashboard →</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Banner Informativo de Beneficios Pro -->
            <div class="bg-gradient-to-r from-amber-50 via-amber-100/50 to-orange-50 border border-amber-200 rounded-lg p-3.5 flex items-center justify-between shadow-xs">
                <div class="flex items-center space-x-3">
                    <div class="w-8 h-8 rounded-full bg-amber-200 text-amber-800 flex items-center justify-center font-bold text-sm">
                        ⭐
                    </div>
                    <div>
                        <p class="text-xs font-bold text-amber-900">
                            <?= $isPro ? 'Beneficios de Membresía Pro Aplicados' : '¿Deseas 20% de descuento y Envíos Gratis?' ?>
                        </p>
                        <p class="text-[11px] text-amber-700">
                            <?= $isPro 
                                ? 'Disfrutas de un <span class="font-bold">20% de descuento</span> en platos seleccionados y <span class="font-bold">Envío Gratis ($0.00)</span> en todos tus pedidos.'
                                : 'Actualmente pagas precio regular y $2.50 de envío. <a href="index.php?action=switch_user&role=pro" class="underline font-bold">Activar Plan Pro simulado aquí</a>.' ?>
                        </p>
                    </div>
                </div>
                <span class="text-[11px] bg-white text-amber-800 border border-amber-300 font-semibold px-2.5 py-1 rounded shadow-xs">
                    <?= $isPro ? 'Envío: $0.00 (Gratis)' : 'Envío: $2.50' ?>
                </span>
            </div>

            <!-- Region Tabs, Branch Selector -->
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 pb-1">
                <!-- Region Selector Tabs -->
                <div class="flex items-center space-x-2">
                    <a href="index.php?action=menu" class="px-3.5 py-1.5 text-xs font-medium rounded-lg <?= empty($region) ? 'bg-slate-800 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?> transition">
                        Todas las Regiones
                    </a>
                    <a href="index.php?action=menu&region=Costa" class="px-3.5 py-1.5 text-xs font-semibold rounded-lg <?= ($region === 'Costa') ? 'bg-cyan-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?> transition flex items-center space-x-1.5">
                        <span>🌊</span>
                        <span>Costa (<?= $conteoRegiones['Costa'] ?? 0 ?>)</span>
                    </a>
                    <a href="index.php?action=menu&region=Sierra" class="px-3.5 py-1.5 text-xs font-medium rounded-lg <?= ($region === 'Sierra') ? 'bg-blue-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?> transition flex items-center space-x-1.5">
                        <span>🏔️</span>
                        <span>Sierra (<?= $conteoRegiones['Sierra'] ?? 0 ?>)</span>
                    </a>
                    <a href="index.php?action=menu&region=Amazonía" class="px-3.5 py-1.5 text-xs font-medium rounded-lg <?= ($region === 'Amazonía') ? 'bg-emerald-600 text-white shadow-sm' : 'text-slate-600 hover:bg-slate-100' ?> transition flex items-center space-x-1.5">
                        <span>🌿</span>
                        <span>Amazonía (<?= $conteoRegiones['Amazonía'] ?? 0 ?>)</span>
                    </a>
                </div>

                <!-- Sucursal Selector -->
                <div class="flex items-center space-x-3">
                    <div class="relative">
                        <select onchange="window.location.href='index.php?action=menu<?= $region ? '&region='.$region : '' ?>&sucursal_id='+this.value" class="text-xs border border-slate-300 rounded-lg pl-3 pr-7 py-1.5 bg-white text-slate-700 focus:outline-none focus:ring-1 focus:ring-blue-500">
                            <?php foreach ($sucursales as $suc): ?>
                                <option value="<?= $suc['id'] ?>" <?= ($sucursalId == $suc['id']) ? 'selected' : '' ?>>
                                    📍 <?= htmlspecialchars($suc['nombre']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Dishes Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <?php foreach ($platos as $pl): 
                    $precioRegular = (float)$pl['precio'];
                    $descuentoPorc = (float)$pl['descuento_pro'];
                    $precioPro = $precioRegular * (1 - ($descuentoPorc / 100));
                    $precioFinal = $isPro ? $precioPro : $precioRegular;

                    $colorHeader = 'from-cyan-50 to-blue-100';
                    $badgeBg = 'bg-cyan-600';
                    $icon = '🍲';
                    if ($pl['region'] === 'Sierra') {
                        $colorHeader = 'from-amber-50 to-orange-100';
                        $badgeBg = 'bg-amber-700';
                        $icon = '🥩';
                    } elseif ($pl['region'] === 'Amazonía') {
                        $colorHeader = 'from-emerald-50 to-teal-100';
                        $badgeBg = 'bg-emerald-700';
                        $icon = '🐟';
                    }
                ?>
                <div class="bg-white rounded-lg border border-slate-200 overflow-hidden shadow-sm hover:border-slate-300 transition flex flex-col justify-between">
                    <div>
                        <div class="h-28 bg-gradient-to-br <?= $colorHeader ?> p-4 relative flex items-end justify-between border-b border-slate-100">
                            <span class="text-3xl"><?= $icon ?></span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold text-white <?= $badgeBg ?> shadow-xs">
                                <?= htmlspecialchars($pl['region']) ?> Ecuatoriana
                            </span>
                        </div>

                        <div class="p-4 space-y-2">
                            <h3 class="font-bold text-slate-800 text-sm"><?= htmlspecialchars($pl['nombre']) ?></h3>
                            <p class="text-xs text-slate-500 leading-relaxed line-clamp-2">
                                <?= htmlspecialchars($pl['descripcion']) ?>
                            </p>
                            
                            <div class="flex flex-wrap gap-1 pt-1">
                                <?php 
                                $ingredientes = explode(',', $pl['ingredientes']);
                                foreach(array_slice($ingredientes, 0, 3) as $ing): ?>
                                    <span class="text-[10px] bg-slate-100 text-slate-600 px-1.5 py-0.5 rounded">
                                        <?= htmlspecialchars(trim($ing)) ?>
                                    </span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <div class="p-4 pt-0 space-y-3">
                        <div class="flex items-baseline justify-between border-t border-slate-100 pt-3">
                            <div>
                                <?php if($isPro): ?>
                                    <span class="text-[11px] text-slate-400 line-through">$ <?= number_format($precioRegular, 2) ?></span>
                                    <div class="text-base font-bold text-blue-600">
                                        $ <?= number_format($precioPro, 2) ?> 
                                        <span class="text-[10px] text-amber-600 font-semibold bg-amber-50 px-1 py-0.2 rounded border border-amber-200">-<?= (int)$descuentoPorc ?>% Pro</span>
                                    </div>
                                <?php else: ?>
                                    <div class="text-base font-bold text-slate-800">
                                        $ <?= number_format($precioRegular, 2) ?>
                                    </div>
                                    <span class="text-[10px] text-amber-700">Con Pro: $ <?= number_format($precioPro, 2) ?></span>
                                <?php endif; ?>
                            </div>

                            <span class="text-[10px] <?= $isPro ? 'text-emerald-700 bg-emerald-50 border-emerald-200' : 'text-slate-500 bg-slate-100 border-slate-200' ?> border px-2 py-0.5 rounded font-medium">
                                <?= $isPro ? 'Envío Gratis' : 'Envío $2.50' ?>
                            </span>
                        </div>

                        <!-- Real Order Form -->
                        <form method="POST" action="index.php?action=ordenar" class="flex gap-2">
                            <input type="hidden" name="plato_id" value="<?= $pl['id'] ?>">
                            <input type="hidden" name="sucursal_id" value="<?= $sucursalId ?>">
                            <input type="hidden" name="cantidad" value="1">
                            
                            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold py-2 rounded-lg transition shadow-sm flex items-center justify-center space-x-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                <span>Pedir Ahora</span>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Footer Demo helper -->
            <div class="text-center pt-4 text-xs text-slate-400 border-t border-slate-100">
                Fuego Andino MVC • Cada pedido registrado actualiza en vivo el Dashboard Administrativo Base y la base de datos SQLite.
            </div>

        </main>
    </div>

</body>
</html>
