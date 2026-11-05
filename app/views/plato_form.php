<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión y Detalle de Platos - Fuego Andino</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<?php $err = function($k) use ($errors) { return isset($errors[$k]) ? '<p class="text-[11px] text-rose-600 mt-1 font-medium">' . htmlspecialchars($errors[$k]) . '</p>' : ''; }; ?>
<body class="bg-slate-100 min-h-screen py-8 px-4 flex justify-center">

    <div class="w-full max-w-5xl bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden">

        <!-- Top Navigation Bar (Matching Wireframe 3) -->
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
                    <a href="index.php?action=dashboard" class="flex items-center space-x-2 text-slate-500 hover:text-slate-800 font-medium">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                        <span>Dashboard</span>
                    </a>
                    <a href="index.php?action=platos" class="flex items-center space-x-2 text-blue-600 font-semibold border-b-2 border-blue-600 pb-1 -mb-1">
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
        <main class="p-8 space-y-8">

            <?php if($flashMessage): ?>
                <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-xl text-xs font-medium shadow-xs">
                    <?= htmlspecialchars($flashMessage) ?>
                </div>
            <?php endif; ?>

            <?php if ($errors): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl text-xs font-medium">
                    El servidor rechazó los datos. Corrige los campos marcados e inténtalo de nuevo.
                </div>
            <?php endif; ?>

            <!-- Form Container (Matching Wireframe 3 Layout) -->
            <div class="bg-white rounded-xl border border-slate-200 p-6 shadow-sm space-y-6">

                <!-- Title Bar -->
                <div class="flex items-center justify-between pb-4 border-b border-slate-200">
                    <div class="flex items-center space-x-3">
                        <h1 class="text-lg font-bold text-slate-800">
                            <?= $platoToEdit ? 'Plato N°00' . $platoToEdit['id'] . ' - ' . htmlspecialchars($platoToEdit['nombre']) : '➕ Registrar Nuevo Plato en el Menú' ?>
                        </h1>
                        <?php if($platoToEdit): ?>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-cyan-50 text-cyan-700 border border-cyan-200">
                                <?= htmlspecialchars($platoToEdit['region']) ?>
                            </span>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-semibold <?= $platoToEdit['activo'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' ?>">
                                <?= $platoToEdit['activo'] ? 'Activo en Catálogo' : 'Inactivo' ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if($platoToEdit): ?>
                        <a href="index.php?action=platos" class="text-xs text-slate-500 hover:text-slate-800 font-medium">
                            + Añadir nuevo plato
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Form -->
                <form method="POST" action="index.php?action=platos" class="space-y-6" id="platoForm">
                    <input type="hidden" name="save_plato" value="1">
                    <?php if($editId): ?>
                        <input type="hidden" name="id" value="<?= (int)$editId ?>">
                    <?php endif; ?>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <!-- Left Column -->
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Nombre del Plato</label>
                                <input type="text" name="nombre" required
                                       value="<?= htmlspecialchars($form['nombre']) ?>"
                                       placeholder="Ej: Encebollado Mixto Especial"
                                       class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <?= $err('nombre') ?>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Región de Origen</label>
                                    <select name="region_id" id="selRegion" required class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <option value="">-- Selecciona región --</option>
                                        <?php foreach ($regiones as $r): ?>
                                            <option value="<?= $r['id'] ?>" <?= ($form['region_id'] == $r['id']) ? 'selected' : '' ?>><?= htmlspecialchars($r['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Provincia de Origen</label>
                                    <select name="provincia_id" id="selProvincia" required class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <option value=""><?= $provincias ? '-- Selecciona provincia --' : '-- Primero elige una región --' ?></option>
                                        <?php foreach ($provincias as $pv): ?>
                                            <option value="<?= $pv['id'] ?>" <?= ($form['provincia_id'] == $pv['id']) ? 'selected' : '' ?>><?= htmlspecialchars($pv['nombre']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <?= $err('provincia_id') ?>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Descripción del Plato</label>
                                <textarea name="descripcion" rows="4" required placeholder="Describe los sabores, ingredientes y presentación tradicional ecuatoriana..."
                                          class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500 leading-relaxed"><?= htmlspecialchars($form['descripcion']) ?></textarea>
                                <?= $err('descripcion') ?>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">Ingredientes Principales (Separados por coma)</label>
                                <input type="text" name="ingredientes" required
                                       value="<?= htmlspecialchars($form['ingredientes']) ?>"
                                       placeholder="Ej: Albacora, Camarón, Yuca, Cebolla colorada"
                                       class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                <?= $err('ingredientes') ?>
                            </div>
                        </div>

                        <!-- Right Column -->
                        <div class="space-y-4">
                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Estado en el Menú</label>
                                    <select name="activo" class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <option value="1" <?= ($form['activo'] == 1) ? 'selected' : '' ?>>Activo (Visible)</option>
                                        <option value="0" <?= ($form['activo'] == 0) ? 'selected' : '' ?>>Inactivo (Oculto)</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Tiempo de Preparación</label>
                                    <input type="text" name="tiempo_preparacion"
                                           value="<?= htmlspecialchars($form['tiempo_preparacion']) ?>"
                                           class="w-full text-xs border border-slate-300 rounded-lg px-3 py-2 bg-white text-slate-800 focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    <?= $err('tiempo_preparacion') ?>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Precio de Venta (PVP)</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-slate-400 text-xs">$</span>
                                        <input type="number" step="0.01" name="precio" id="inputPrecio" required
                                               value="<?= htmlspecialchars($form['precio']) ?>"
                                               class="w-full text-xs border border-slate-300 rounded-lg pl-7 pr-3 py-2 bg-white text-slate-800 font-semibold focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    </div>
                                    <?= $err('precio') ?>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Costo Real de Elaboración</label>
                                    <div class="relative">
                                        <span class="absolute left-3 top-2 text-slate-400 text-xs">$</span>
                                        <input type="number" step="0.01" name="costo" id="inputCosto" required
                                               value="<?= htmlspecialchars($form['costo']) ?>"
                                               class="w-full text-xs border border-slate-300 rounded-lg pl-7 pr-3 py-2 bg-white text-slate-800 font-semibold focus:outline-none focus:ring-1 focus:ring-blue-500">
                                    </div>
                                    <?= $err('costo') ?>
                                    <span class="text-[10px] text-slate-400">Base para calcular Ganancia Core</span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Descuento Plan Pro (%)</label>
                                    <div class="relative">
                                        <input type="number" step="1" name="descuento_pro" id="inputDescPro" required
                                               value="<?= htmlspecialchars($form['descuento_pro']) ?>"
                                               class="w-full text-xs border border-slate-300 rounded-lg pl-3 pr-7 py-2 bg-white text-slate-800 font-semibold focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <span class="absolute right-3 top-2 text-slate-400 text-xs">%</span>
                                    </div>
                                    <?= $err('descuento_pro') ?>
                                </div>

                                <div>
                                    <label class="block text-xs font-semibold text-slate-700 mb-1">Precio Final Pro</label>
                                    <input type="text" id="labelPrecioPro" disabled
                                           class="w-full text-xs border border-slate-200 bg-slate-50 text-slate-700 rounded-lg px-3 py-2 font-semibold">
                                </div>
                            </div>

                            <!-- Live Calculation Helper Banner -->
                            <div class="bg-blue-50/70 border border-blue-200 rounded-lg p-3 space-y-1">
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-blue-900 font-medium">Margen Unitario Normal:</span>
                                    <span class="font-bold text-emerald-600" id="labelMargenNormal">+$ 3.60 (+55.4%)</span>
                                </div>
                                <div class="flex items-center justify-between text-xs">
                                    <span class="text-blue-900 font-medium">Margen Unitario con Plan Pro:</span>
                                    <span class="font-bold text-emerald-600" id="labelMargenPro">+$ 2.30 (+44.2%)</span>
                                </div>
                                <p class="text-[10px] text-blue-700 pt-1 border-t border-blue-100">
                                    Fórmula aplicada: Ganancia = PVP − Costo Elaboración
                                </p>
                            </div>
                        </div>

                    </div>

                    <!-- Action Buttons -->
                    <div class="flex items-center space-x-3 pt-4 border-t border-slate-200">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold px-5 py-2.5 rounded-lg transition shadow-sm">
                            <?= $platoToEdit ? 'Actualizar Plato' : 'Guardar en Catálogo' ?>
                        </button>
                        <?php if($platoToEdit): ?>
                            <a href="index.php?action=platos" class="bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 text-xs font-medium px-5 py-2.5 rounded-lg transition">
                                Cancelar Edición
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Listado Completo de Platos (CRUD) -->
            <div class="bg-white rounded-xl border border-slate-200 overflow-hidden shadow-sm">
                <div class="px-6 py-4 bg-slate-50 border-b border-slate-200 flex items-center justify-between">
                    <div>
                        <h2 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Catálogo General de Platos</h2>
                        <p class="text-[11px] text-slate-500">Listado de platos registrados en la base de datos SQLite</p>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/75 text-slate-600 border-b border-slate-200 font-semibold">
                                <th class="py-3 px-4">#</th>
                                <th class="py-3 px-4">Nombre del Plato</th>
                                <th class="py-3 px-4">Región / Provincia</th>
                                <th class="py-3 px-4 text-right">PVP Normal</th>
                                <th class="py-3 px-4 text-right">Costo Elaboración</th>
                                <th class="py-3 px-4 text-right">PVP Pro</th>
                                <th class="py-3 px-4 text-center">Estado</th>
                                <th class="py-3 px-4 text-center">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-700">
                            <?php foreach ($platos as $pl): ?>
                                <tr class="hover:bg-slate-50 transition">
                                    <td class="py-3 px-4 text-slate-400 font-medium"><?= $pl['id'] ?></td>
                                    <td class="py-3 px-4 font-semibold text-slate-900"><?= htmlspecialchars($pl['nombre']) ?></td>
                                    <td class="py-3 px-4">
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold <?= $pl['region'] === 'Costa' ? 'bg-cyan-50 text-cyan-700 border border-cyan-200' : ($pl['region'] === 'Sierra' ? 'bg-blue-50 text-blue-700 border border-blue-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200') ?>">
                                            <?= htmlspecialchars($pl['region']) ?>
                                        </span>
                                        <span class="text-slate-500 ml-1"><?= htmlspecialchars($pl['provincia'] ?? '—') ?></span>
                                    </td>
                                    <td class="py-3 px-4 text-right font-medium">$ <?= number_format($pl['precio'], 2) ?></td>
                                    <td class="py-3 px-4 text-right text-slate-500">$ <?= number_format($pl['costo'], 2) ?></td>
                                    <td class="py-3 px-4 text-right font-bold text-blue-600">$ <?= number_format($pl['precio'] * (1 - $pl['descuento_pro'] / 100), 2) ?></td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="px-2 py-0.5 rounded text-[10px] font-medium <?= $pl['activo'] ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-500 border border-slate-200' ?>">
                                            <?= $pl['activo'] ? 'Activo' : 'Inactivo' ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4 text-center space-x-2">
                                        <a href="index.php?action=platos&edit_id=<?= $pl['id'] ?>" class="text-blue-600 hover:text-blue-800 font-semibold text-xs">
                                            Editar
                                        </a>
                                        <form method="POST" action="index.php?action=platos" class="inline-block" onsubmit="return confirm('¿Seguro que deseas eliminar este plato?');">
                                            <input type="hidden" name="delete_plato" value="1">
                                            <input type="hidden" name="id" value="<?= $pl['id'] ?>">
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 font-semibold text-xs">
                                                Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <script>
        function calcularMargenes() {
            const precio = parseFloat(document.getElementById('inputPrecio').value) || 0;
            const costo = parseFloat(document.getElementById('inputCosto').value) || 0;
            const descPro = parseFloat(document.getElementById('inputDescPro').value) || 0;

            const precioPro = precio * (1 - (descPro / 100));
            document.getElementById('labelPrecioPro').value = '$ ' + precioPro.toFixed(2) + ' (Calculado)';

            const margenNormal = precio - costo;
            const margenNormalPct = precio > 0 ? ((margenNormal / precio) * 100).toFixed(1) : 0;
            document.getElementById('labelMargenNormal').innerText = '+$ ' + margenNormal.toFixed(2) + ' (+' + margenNormalPct + '%)';

            const margenPro = precioPro - costo;
            const margenProPct = precioPro > 0 ? ((margenPro / precioPro) * 100).toFixed(1) : 0;
            document.getElementById('labelMargenPro').innerText = '+$ ' + margenPro.toFixed(2) + ' (+' + margenProPct + '%)';
        }

        document.getElementById('inputPrecio').addEventListener('input', calcularMargenes);
        document.getElementById('inputCosto').addEventListener('input', calcularMargenes);
        document.getElementById('inputDescPro').addEventListener('input', calcularMargenes);
        calcularMargenes();

        // Dropdown dependiente: al elegir región se cargan sus provincias desde el servidor
        const selRegion = document.getElementById('selRegion');
        const selProvincia = document.getElementById('selProvincia');
        selRegion.addEventListener('change', async () => {
            if (!selRegion.value) {
                selProvincia.innerHTML = '<option value="">-- Primero elige una región --</option>';
                return;
            }
            selProvincia.innerHTML = '<option value="">-- Selecciona provincia --</option>';
            const res = await fetch('index.php?action=provincias&region_id=' + encodeURIComponent(selRegion.value));
            const provincias = await res.json();
            provincias.forEach(p => selProvincia.add(new Option(p.nombre, p.id)));
        });
    </script>
</body>
</html>
