<?php
/**
 * Datos de ejemplo del sistema. Los lee Database::seed() solo cuando una tabla está vacía.
 * Las fotos de assets/img/platos son reales (Wikimedia Commons); sus autores están en assets/img/creditos.json.
 */
return [
    // [usuario, nombre, correo, rol, plan]  — contraseña de todos: 1234
    'usuarios' => [
        ['admin', 'Ariel Tejada', 'admin@fuegoandino.ec', 'admin', 'pro'],
        ['cliente_pro', 'Carlos Morales', 'carlos@udla.edu.ec', 'usuario', 'pro'],
        ['cliente_free', 'María Zambrano', 'maria@gmail.com', 'usuario', 'free'],
        ['lucia', 'Lucía Andrade', 'lucia@example.com', 'usuario', 'free'],
        ['diego', 'Diego Paredes', 'diego@example.com', 'usuario', 'pro'],
        ['sofia', 'Sofía Villacís', 'sofia@example.com', 'usuario', 'free'],
        ['mateo', 'Mateo Cevallos', 'mateo@example.com', 'usuario', 'free'],
    ],

    'geografia' => [
        'Costa' => ['Esmeraldas', 'Manabí', 'Santo Domingo de los Tsáchilas', 'Guayas', 'Santa Elena', 'Los Ríos', 'El Oro'],
        'Sierra' => ['Carchi', 'Imbabura', 'Pichincha', 'Cotopaxi', 'Tungurahua', 'Bolívar', 'Chimborazo', 'Cañar', 'Azuay', 'Loja'],
        'Amazonía' => ['Sucumbíos', 'Napo', 'Orellana', 'Pastaza', 'Morona Santiago', 'Zamora Chinchipe'],
    ],

    'ciudades' => [
        'Pichincha' => ['Quito', 'Cayambe', 'Machachi'], 'Guayas' => ['Guayaquil', 'Daule', 'Milagro'],
        'Azuay' => ['Cuenca', 'Gualaceo'], 'Manabí' => ['Portoviejo', 'Manta'],
        'Imbabura' => ['Ibarra', 'Otavalo'], 'Cotopaxi' => ['Latacunga'],
        'Tungurahua' => ['Ambato', 'Baños'], 'Chimborazo' => ['Riobamba'], 'Loja' => ['Loja'],
        'Esmeraldas' => ['Esmeraldas', 'Atacames'], 'El Oro' => ['Machala'], 'Santa Elena' => ['Salinas'],
        'Napo' => ['Tena'], 'Pastaza' => ['Puyo'], 'Morona Santiago' => ['Macas'],
        'Orellana' => ['Francisco de Orellana'], 'Sucumbíos' => ['Nueva Loja'],
    ],

    'categorias' => ['Sopas y caldos', 'Platos fuertes', 'Entradas y snacks', 'Bebidas'],

    // [nombre, ciudad, dirección]
    'sucursales' => [
        ['Sucursal Quito - La Mariscal', 'Quito', 'Av. Amazonas y Foch'],
        ['Sucursal Guayaquil - Malecón', 'Guayaquil', 'Malecón Simón Bolívar y 9 de Octubre'],
        ['Sucursal Cuenca - El Sagrario', 'Cuenca', 'Calle Larga y Benigno Malo'],
    ],

    'platos' => [
        [
            'nombre' => 'Encebollado Mixto', 'provincia' => 'Guayas', 'categoria' => 'Sopas y caldos',
            'precio' => 6.50, 'costo' => 2.90, 'descuento_pro' => 20, 'tiempo' => '00:15 hrs', 'imagen' => 'encebollado',
            'descripcion' => 'El caldo de albacora más famoso de la Costa, con camarón, yuca, cebolla colorada curtida y limón.',
            'ingredientes' => 'Albacora, Camarón, Yuca, Cebolla colorada, Limón, Cilantro',
        ],
        [
            'nombre' => 'Seco de Chivo', 'provincia' => 'Guayas', 'categoria' => 'Platos fuertes',
            'precio' => 8.50, 'costo' => 3.90, 'descuento_pro' => 20, 'tiempo' => '00:25 hrs', 'imagen' => 'seco-de-chivo',
            'descripcion' => 'Guiso de chivo cocido a fuego lento en naranjilla y cerveza, con arroz amarillo, chifles y aguacate.',
            'ingredientes' => 'Carne de chivo, Naranjilla, Cerveza, Arroz, Plátano verde, Aguacate',
        ],
        [
            'nombre' => 'Ceviche de Camarón', 'provincia' => 'Manabí', 'categoria' => 'Entradas y snacks',
            'precio' => 7.50, 'costo' => 3.30, 'descuento_pro' => 15, 'tiempo' => '00:12 hrs', 'imagen' => 'ceviche-de-camaron',
            'descripcion' => 'Camarones marinados en limón y naranja, tomate, cebolla y cilantro, con su caldito y aguacate.',
            'ingredientes' => 'Camarón, Limón, Naranja, Tomate, Cebolla colorada, Aguacate',
        ],
        [
            'nombre' => 'Locro de Papa', 'provincia' => 'Pichincha', 'categoria' => 'Sopas y caldos',
            'precio' => 5.50, 'costo' => 2.10, 'descuento_pro' => 20, 'tiempo' => '00:20 hrs', 'imagen' => 'locro-de-papa',
            'descripcion' => 'Sopa cremosa de papas andinas con achiote, servida con queso fresco y aguacate.',
            'ingredientes' => 'Papa chola, Queso, Aguacate, Leche, Achiote',
        ],
        [
            'nombre' => 'Hornado con Llapingachos', 'provincia' => 'Cotopaxi', 'categoria' => 'Platos fuertes',
            'precio' => 8.50, 'costo' => 3.80, 'descuento_pro' => 20, 'tiempo' => '00:20 hrs', 'imagen' => 'hornado',
            'descripcion' => 'Cerdo horneado lentamente, con tortillas de papa (llapingachos), ensalada, aguacate y ají.',
            'ingredientes' => 'Cerdo, Papa chola, Mote, Aguacate, Ají, Lechuga',
        ],
        [
            'nombre' => 'Guatita Quiteña', 'provincia' => 'Pichincha', 'categoria' => 'Platos fuertes',
            'precio' => 7.00, 'costo' => 3.00, 'descuento_pro' => 15, 'tiempo' => '00:20 hrs', 'imagen' => 'guatita',
            'descripcion' => 'Mondongo en salsa cremosa de maní con papas, servido con arroz blanco y ají.',
            'ingredientes' => 'Mondongo, Maní, Papa, Arroz, Ají',
        ],
        [
            'nombre' => 'Cevichocho', 'provincia' => 'Pichincha', 'categoria' => 'Entradas y snacks',
            'precio' => 4.50, 'costo' => 1.60, 'descuento_pro' => 15, 'tiempo' => '00:08 hrs', 'imagen' => 'cevichocho',
            'descripcion' => 'Chochos andinos en un adobo de tomate, limón y cebolla, con canguil (palomitas) para acompañar.',
            'ingredientes' => 'Chochos, Tomate, Limón, Cebolla colorada, Canguil',
        ],
        [
            'nombre' => 'Empanadas de Viento', 'provincia' => 'Pichincha', 'categoria' => 'Entradas y snacks',
            'precio' => 3.00, 'costo' => 0.90, 'descuento_pro' => 10, 'tiempo' => '00:10 hrs', 'imagen' => 'empanadas-de-viento',
            'descripcion' => 'Empanadas fritas y crocantes de queso, espolvoreadas con azúcar. Porción de dos.',
            'ingredientes' => 'Harina de trigo, Queso, Azúcar',
        ],
        [
            'nombre' => 'Colada Morada', 'provincia' => 'Pichincha', 'categoria' => 'Bebidas',
            'precio' => 2.50, 'costo' => 0.80, 'descuento_pro' => 10, 'tiempo' => '00:05 hrs', 'imagen' => 'colada-morada',
            'descripcion' => 'Bebida espesa de harina de maíz morado, mora, naranjilla, canela y especias.',
            'ingredientes' => 'Maíz morado, Mora, Naranjilla, Canela, Clavo de olor',
        ],
        [
            'nombre' => 'Horchata Lojana', 'provincia' => 'Loja', 'categoria' => 'Bebidas',
            'precio' => 2.25, 'costo' => 0.55, 'descuento_pro' => 10, 'tiempo' => '00:05 hrs', 'imagen' => 'horchata-lojana',
            'descripcion' => 'Infusión fría de 18 hierbas de Loja, de color rosado y sabor fresco y floral.',
            'ingredientes' => 'Hierbas aromáticas, Flor de jamaica, Panela',
        ],
        [
            'nombre' => 'Canelazo', 'provincia' => 'Pichincha', 'categoria' => 'Bebidas',
            'precio' => 2.75, 'costo' => 0.80, 'descuento_pro' => 10, 'tiempo' => '00:05 hrs', 'imagen' => 'canelazo',
            'descripcion' => 'Bebida caliente de canela, naranjilla y panela, ideal para las noches frías de la Sierra.',
            'ingredientes' => 'Canela, Naranjilla, Panela',
        ],
        [
            'nombre' => 'Maito de Tilapia', 'provincia' => 'Napo', 'categoria' => 'Platos fuertes',
            'precio' => 9.00, 'costo' => 4.20, 'descuento_pro' => 20, 'tiempo' => '00:25 hrs', 'imagen' => 'maito-de-tilapia',
            'descripcion' => 'Tilapia envuelta en hoja de bijao y asada a las brasas, con yuca y encurtido de cebolla.',
            'ingredientes' => 'Tilapia, Hoja de bijao, Yuca, Cebolla colorada, Limón',
        ],
        [
            'nombre' => 'Ayampaco', 'provincia' => 'Morona Santiago', 'categoria' => 'Platos fuertes',
            'precio' => 8.00, 'costo' => 3.60, 'descuento_pro' => 20, 'tiempo' => '00:30 hrs', 'imagen' => 'ayampaco',
            'descripcion' => 'Plato tradicional shuar: pescado envuelto en hojas y cocido al vapor, con plátano maduro.',
            'ingredientes' => 'Pescado de río, Hojas de bijao, Plátano, Ají',
        ],
    ],

    // Pedidos de ejemplo: [días atrás => cantidad de pedidos]
    'pedidos' => [
        'semilla' => 2026,
        'dias' => [0 => 4, 1 => 5, 2 => 3, 3 => 6, 4 => 4, 5 => 3, 6 => 5, 7 => 4, 8 => 3, 9 => 4, 10 => 2, 11 => 3, 12 => 2, 13 => 3,
                   33 => 3, 38 => 2, 44 => 3],
    ],
];
