<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CRUD MVC Moderno</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen p-4 md:p-8 flex flex-col items-center">

    <div class="w-full max-w-4xl bg-white rounded-xl shadow-lg overflow-hidden mt-8">
        

        <div class="bg-blue-600 p-5 flex justify-between items-center text-white">
            <h2 class="text-xl font-bold">Gestión de Registros</h2>
            <a href="index.php?action=logout" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded-lg font-semibold shadow transition text-sm">Cerrar Sesión</a>
        </div>
        
        <div class="p-6 md:p-8">

            <div class="mb-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
                <h3 class="text-lg font-bold text-gray-800 mb-4">
                    <?= isset($itemToEdit) ? '✏️ Editar Registro' : '➕ Añadir Nuevo Registro' ?>
                </h3>
                
                <form method="POST" action="index.php?action=crud" class="flex flex-col sm:flex-row gap-4">
                    <?php if(isset($itemToEdit)): ?>
                        <input type="hidden" name="update" value="1">
                        <input type="hidden" name="id" value="<?= $itemToEdit['id'] ?>">
                    <?php else: ?>
                        <input type="hidden" name="add" value="1">
                    <?php endif; ?>
                    
                    <input type="text" name="name" placeholder="Nombre del ítem" required 
                           value="<?= isset($itemToEdit) ? htmlspecialchars($itemToEdit['name']) : '' ?>"
                           class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    
                    <input type="text" name="description" placeholder="Descripción" required 
                           value="<?= isset($itemToEdit) ? htmlspecialchars($itemToEdit['description']) : '' ?>"
                           class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    
                    
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-bold transition shadow-md">
                        <?= isset($itemToEdit) ? 'Actualizar' : 'Guardar' ?>
                    </button>
                    
                    <?php if(isset($itemToEdit)): ?>
                        <a href="index.php?action=crud" class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg text-center transition font-bold shadow-md">Cancelar</a>
                    <?php endif; ?>
                </form>
            </div>


            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700">
                            <th class="p-4 text-sm font-bold border-b">Nº</th>
                            <th class="p-4 text-sm font-bold border-b">Nombre</th>
                            <th class="p-4 text-sm font-bold border-b">Descripción</th>
                            <th class="p-4 text-sm font-bold text-center border-b">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>

                        <?php $contador = 1; ?>
                        <?php foreach ($items as $item): ?>
                        <tr class="hover:bg-blue-50 border-b border-gray-100 transition">

                            <td class="p-4 text-gray-500 font-medium"><?= $contador++ ?></td>
                            <td class="p-4 text-gray-800 font-semibold"><?= htmlspecialchars($item['name']) ?></td>
                            <td class="p-4 text-gray-600"><?= htmlspecialchars($item['description']) ?></td>
                            <td class="p-4 text-center space-x-3">
                                <a href="index.php?action=crud&edit_id=<?= $item['id'] ?>" class="text-blue-600 hover:text-blue-800 font-bold text-sm">Editar</a>
                                
                                <form method="POST" action="index.php?action=crud" class="inline-block">
                                    <input type="hidden" name="delete" value="1">
                                  
                                    <input type="hidden" name="id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-700 font-bold text-sm" onclick="return confirm('¿Seguro que deseas eliminar este registro?');">Eliminar</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>