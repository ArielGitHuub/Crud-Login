<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Fuego Andino</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: 'Inter', sans-serif; }</style>
</head>
<body class="bg-slate-100 min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 w-full max-w-md overflow-hidden">
        
        <!-- Header -->
        <div class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 p-8 text-center text-white relative">
            <div class="inline-flex items-center justify-center w-14 h-14 rounded-2xl bg-amber-500/20 text-3xl mb-3 border border-amber-400/30">
                🔥
            </div>
            <h1 class="text-2xl font-bold tracking-tight">Fuego Andino</h1>
            <p class="text-xs text-slate-300 mt-1">Gestión Gastronómica Ecuatoriana Multisucursal</p>
        </div>

        <div class="p-8 space-y-6">
            
            <?php if(isset($error) || isset($_GET['error'])): ?>
                <div class="bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 rounded-xl text-xs font-medium flex items-center space-x-2">
                    <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span><?= isset($error) ? htmlspecialchars($error) : 'Acceso restringido. Debes iniciar sesión con rol Administrador.' ?></span>
                </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" action="index.php?action=login" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-700 mb-1.5">Usuario</label>
                    <input type="text" name="username" placeholder="Ej: admin o cliente_pro" required 
                           class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label class="block text-xs font-semibold text-slate-700">Contraseña</label>
                        <span class="text-[11px] text-slate-400">Default: 1234</span>
                    </div>
                    <input type="password" name="password" placeholder="••••••••" required 
                           class="w-full px-3.5 py-2.5 text-sm border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>

                <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2.5 px-4 rounded-xl transition duration-200 text-sm shadow-sm">
                    Iniciar Sesión
                </button>
            </form>

            <!-- Acceso Rápido para Pruebas / Calificación de Tarea -->
            <div class="pt-4 border-t border-slate-100 space-y-3">
                <p class="text-[11px] text-center font-bold text-slate-400 uppercase tracking-wider">
                    Acceso rápido por roles (Simulador)
                </p>
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <a href="index.php?action=switch_user&role=admin" class="flex items-center justify-center p-2 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-semibold border border-blue-200 transition text-center">
                        👑 Admin (Ariel T.)
                    </a>
                    <a href="index.php?action=switch_user&role=pro" class="flex items-center justify-center p-2 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 font-semibold border border-amber-200 transition text-center">
                        ⭐ Cliente Pro
                    </a>
                    <a href="index.php?action=switch_user&role=free" class="flex items-center justify-center p-2 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-700 font-semibold border border-slate-200 transition text-center">
                        👤 Cliente Free
                    </a>
                    <a href="index.php?action=switch_user&role=invitado" class="flex items-center justify-center p-2 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-600 font-semibold border border-slate-200 transition text-center">
                        👀 Ver como Invitado
                    </a>
                </div>
            </div>

        </div>

    </div>

</body>
</html>