<?php
class Database {
    private $pdo;

    public function connect() {
        if ($this->pdo == null) {
            $this->pdo = new PDO('sqlite:' . __DIR__ . '/../data.sqlite');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->createTables();
        }
        return $this->pdo;
    }

    private function createTables() {
        // 1. Tabla Usuarios (con roles: admin, usuario, invitado y plan: free, pro)
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            nombre TEXT NOT NULL,
            email TEXT,
            password TEXT NOT NULL,
            rol TEXT DEFAULT 'usuario', -- admin, usuario, invitado
            plan TEXT DEFAULT 'free',   -- free, pro
            creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Migración: bases creadas con la versión inicial (users solo con username/password)
        $userCols = array_column($this->pdo->query("PRAGMA table_info(users)")->fetchAll(PDO::FETCH_ASSOC), 'name');
        $nuevas = [
            'nombre' => "TEXT NOT NULL DEFAULT ''", 'email' => 'TEXT',
            'rol' => "TEXT DEFAULT 'usuario'", 'plan' => "TEXT DEFAULT 'free'", 'creado_en' => 'DATETIME',
        ];
        foreach ($nuevas as $col => $def) {
            if (!in_array($col, $userCols)) {
                $this->pdo->exec("ALTER TABLE users ADD COLUMN $col $def");
            }
        }
        $this->pdo->exec("UPDATE users SET rol = 'admin', plan = 'pro', nombre = 'Ariel Tejada', email = 'admin@fuegoandino.ec' WHERE username = 'admin' AND (nombre IS NULL OR nombre = '')");

        // 2. Tabla Sucursales
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS sucursales (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            ciudad TEXT NOT NULL,
            direccion TEXT,
            activa INTEGER DEFAULT 1
        )");

        // 3. Tabla Platos (Core: menú por región, precios, costos para cálculo de ganancias)
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS platos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            region TEXT NOT NULL, -- Costa, Sierra, Amazonía
            precio REAL NOT NULL,
            costo REAL NOT NULL,
            descuento_pro REAL DEFAULT 20.0,
            activo INTEGER DEFAULT 1,
            descripcion TEXT,
            ingredientes TEXT,
            tiempo_preparacion TEXT DEFAULT '00:15 hrs'
        )");

        // 4. Tabla Pedidos (Core: registro de ventas, cálculo de total, costo y ganancia)
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS pedidos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER,
            sucursal_id INTEGER,
            subtotal REAL NOT NULL,
            descuento REAL DEFAULT 0.0,
            envio REAL DEFAULT 0.0,
            total REAL NOT NULL,
            costo_total REAL NOT NULL,
            ganancia REAL NOT NULL,
            estado TEXT DEFAULT 'Entregado', -- Entregado, Preparando, Cancelado
            fecha DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES users(id),
            FOREIGN KEY (sucursal_id) REFERENCES sucursales(id)
        )");

        // 5. Tabla Detalle Pedidos
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS detalle_pedidos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            pedido_id INTEGER NOT NULL,
            plato_id INTEGER NOT NULL,
            cantidad INTEGER NOT NULL,
            precio_unitario REAL NOT NULL,
            costo_unitario REAL NOT NULL,
            FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
            FOREIGN KEY (plato_id) REFERENCES platos(id)
        )");

        // Tabla legado items (para mantener retrocompatibilidad)
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            description TEXT
        )");

        // Catálogos relacionales: regiones -> provincias (origen geográfico de cada plato)
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS regiones (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT UNIQUE NOT NULL
        )");
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS provincias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            region_id INTEGER NOT NULL,
            nombre TEXT NOT NULL,
            UNIQUE (region_id, nombre),
            FOREIGN KEY (region_id) REFERENCES regiones(id)
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS ciudades (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            provincia_id INTEGER NOT NULL,
            nombre TEXT NOT NULL,
            UNIQUE (provincia_id, nombre),
            FOREIGN KEY (provincia_id) REFERENCES provincias(id)
        )");

        // Migración: sucursales.ciudad_id (la columna 'ciudad' se conserva sincronizada)
        $sucCols = array_column($this->pdo->query("PRAGMA table_info(sucursales)")->fetchAll(PDO::FETCH_ASSOC), 'name');
        if (!in_array('ciudad_id', $sucCols)) {
            $this->pdo->exec("ALTER TABLE sucursales ADD COLUMN ciudad_id INTEGER REFERENCES ciudades(id)");
        }

        // Migración: platos.provincia_id (la columna 'region' se conserva sincronizada para menú y dashboard)
        $cols = array_column($this->pdo->query("PRAGMA table_info(platos)")->fetchAll(PDO::FETCH_ASSOC), 'name');
        if (!in_array('imagen', $cols)) {
            $this->pdo->exec("ALTER TABLE platos ADD COLUMN imagen TEXT");
        }
        if (!in_array('provincia_id', $cols)) {
            $this->pdo->exec("ALTER TABLE platos ADD COLUMN provincia_id INTEGER REFERENCES provincias(id)");
        }

        // Población inicial de datos (Seed Data)
        $this->seedInitialData();
    }

    private function seedGeografia() {
        if ($this->pdo->query("SELECT COUNT(*) FROM regiones")->fetchColumn() == 0) {
            $geo = [
                'Costa' => ['Esmeraldas', 'Manabí', 'Santo Domingo de los Tsáchilas', 'Guayas', 'Santa Elena', 'Los Ríos', 'El Oro'],
                'Sierra' => ['Carchi', 'Imbabura', 'Pichincha', 'Cotopaxi', 'Tungurahua', 'Bolívar', 'Chimborazo', 'Cañar', 'Azuay', 'Loja'],
                'Amazonía' => ['Sucumbíos', 'Napo', 'Orellana', 'Pastaza', 'Morona Santiago', 'Zamora Chinchipe'],
            ];
            $insR = $this->pdo->prepare("INSERT INTO regiones (nombre) VALUES (?)");
            $insP = $this->pdo->prepare("INSERT INTO provincias (region_id, nombre) VALUES (?, ?)");
            foreach ($geo as $region => $provincias) {
                $insR->execute([$region]);
                $rid = $this->pdo->lastInsertId();
                foreach ($provincias as $prov) {
                    $insP->execute([$rid, $prov]);
                }
            }
        }

        if ($this->pdo->query("SELECT COUNT(*) FROM ciudades")->fetchColumn() == 0) {
            $ciudades = [
                'Pichincha' => ['Quito', 'Cayambe', 'Machachi'], 'Guayas' => ['Guayaquil', 'Daule', 'Milagro'],
                'Azuay' => ['Cuenca', 'Gualaceo'], 'Manabí' => ['Portoviejo', 'Manta'],
                'Imbabura' => ['Ibarra', 'Otavalo'], 'Cotopaxi' => ['Latacunga'],
                'Tungurahua' => ['Ambato', 'Baños'], 'Chimborazo' => ['Riobamba'], 'Loja' => ['Loja'],
                'Esmeraldas' => ['Esmeraldas', 'Atacames'], 'El Oro' => ['Machala'], 'Santa Elena' => ['Salinas'],
                'Napo' => ['Tena'], 'Pastaza' => ['Puyo'], 'Morona Santiago' => ['Macas'],
                'Orellana' => ['Francisco de Orellana'], 'Sucumbíos' => ['Nueva Loja'],
            ];
            $insC = $this->pdo->prepare("INSERT INTO ciudades (provincia_id, nombre) SELECT id, ? FROM provincias WHERE nombre = ?");
            foreach ($ciudades as $prov => $lista) {
                foreach ($lista as $c) {
                    $insC->execute([$c, $prov]);
                }
            }
        }
        // Enlaza las sucursales existentes con su ciudad
        $this->pdo->exec("UPDATE sucursales SET ciudad_id = (SELECT id FROM ciudades WHERE ciudades.nombre = sucursales.ciudad LIMIT 1) WHERE ciudad_id IS NULL");

        // Asigna provincia de origen a los platos que aún no la tienen
        $origen = [
            'Encebollado Mixto Especial' => 'Guayas', 'Seco de Chivo Tradicional' => 'Guayas',
            'Bolón de Verde con Chicharrón y Queso' => 'Manabí', 'Ceviche de Camarón Manabita' => 'Manabí',
            'Locro de Papa con Queso y Aguacate' => 'Pichincha', 'Hornado con Llapingachos' => 'Cotopaxi',
            'Fritada Imbabureña Tradicional' => 'Imbabura', 'Cevichocho con Tostado' => 'Pichincha',
            'Maito de Tilapia Amazónico' => 'Napo', 'Caldo de Carachama Tradicional' => 'Pastaza',
            'Chicha de Chonta y Yuca' => 'Morona Santiago',
        ];
        // Ilustraciones incluidas para los platos del menú base
        $imagenes = [
            'Encebollado Mixto Especial' => 'encebollado', 'Seco de Chivo Tradicional' => 'seco-de-chivo',
            'Bolón de Verde con Chicharrón y Queso' => 'bolon-de-verde', 'Ceviche de Camarón Manabita' => 'ceviche-de-camaron',
            'Locro de Papa con Queso y Aguacate' => 'locro-de-papa', 'Hornado con Llapingachos' => 'hornado-con-llapingachos',
            'Fritada Imbabureña Tradicional' => 'fritada-imbabure', 'Cevichocho con Tostado' => 'cevichocho',
            'Maito de Tilapia Amazónico' => 'maito-de-tilapia', 'Caldo de Carachama Tradicional' => 'caldo-de-carachama',
            'Chicha de Chonta y Yuca' => 'chicha-de-chonta',
        ];
        $updImg = $this->pdo->prepare("UPDATE platos SET imagen = ? WHERE nombre = ? AND imagen IS NULL");
        foreach ($imagenes as $plato => $archivo) {
            $updImg->execute(["assets/img/platos/$archivo.svg", $plato]);
        }

        $upd = $this->pdo->prepare("UPDATE platos SET provincia_id = (SELECT id FROM provincias WHERE nombre = ?) WHERE nombre = ? AND provincia_id IS NULL");
        foreach ($origen as $plato => $prov) {
            $upd->execute([$prov, $plato]);
        }
    }

    private function seedInitialData() {
        // A. Usuarios iniciales
        $pass = md5('1234');
        $demo = [
            ['admin', 'Ariel Tejada', 'admin@fuegoandino.ec', 'admin', 'pro'],
            ['cliente_pro', 'Carlos Morales', 'carlos@udla.edu.ec', 'usuario', 'pro'],
            ['cliente_free', 'María Zambrano', 'maria@gmail.com', 'usuario', 'free'],
        ];
        $insU = $this->pdo->prepare("INSERT INTO users (username, nombre, email, password, rol, plan)
            SELECT ?, ?, ?, ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = ?)");
        foreach ($demo as [$u, $n, $e, $r, $p]) {
            $insU->execute([$u, $n, $e, $pass, $r, $p, $u]);
        }

        // B. Sucursales iniciales
        $sucursalesCount = $this->pdo->query("SELECT COUNT(*) FROM sucursales")->fetchColumn();
        if ($sucursalesCount == 0) {
            $this->pdo->exec("INSERT INTO sucursales (nombre, ciudad, direccion, activa) VALUES 
                ('Sucursal Quito - La Mariscal', 'Quito', 'Av. Amazonas y Foch', 1),
                ('Sucursal Guayaquil - Malecón', 'Guayaquil', 'Malecón Simón Bolívar y 9 de Octubre', 1),
                ('Sucursal Cuenca - El Sagrario', 'Cuenca', 'Calle Larga y Benigno Malo', 1)");
        }

        // C. Platos iniciales del menú tradicional ecuatoriano
        $platosCount = $this->pdo->query("SELECT COUNT(*) FROM platos")->fetchColumn();
        if ($platosCount == 0) {
            $platos = [
                // Costa
                ['Encebollado Mixto Especial', 'Costa', 6.50, 2.90, 20.0, 1, 
                 'Caldo tradicional de albacora fresca con camarón nacional, yuca tierna cocida, cebolla colorada curtida y chifles crocantes.', 
                 'Albacora fresca, Camarón, Yuca, Cebolla colorada, Chifles, Cilantro', '00:15 hrs'],
                ['Seco de Chivo Tradicional', 'Costa', 7.50, 3.80, 20.0, 1, 
                 'Guiso clásico de chivo cocido a fuego lento en naranjilla y cerveza, servido con arroz amarillo y plátano maduro.', 
                 'Carne de chivo, Naranjilla, Cerveza, Arroz, Plátano maduro', '00:25 hrs'],
                ['Bolón de Verde con Chicharrón y Queso', 'Costa', 3.50, 1.40, 20.0, 1, 
                 'Auténtico bolón de plátano verde majado con chicharrón crujiente y queso manaba derretido.', 
                 'Plátano verde, Chicharrón, Queso manaba, Sal marina', '00:10 hrs'],
                ['Ceviche de Camarón Manabita', 'Costa', 7.00, 3.10, 20.0, 1, 
                 'Camarones frescos marinados en jugo de limón, naranja agria, tomate, cilantro fresco y chifles.', 
                 'Camarón ecuatoriano, Limón criollo, Naranja, Tomate riñón, Chifles', '00:12 hrs'],

                // Sierra
                ['Locro de Papa con Queso y Aguacate', 'Sierra', 5.50, 2.20, 20.0, 1, 
                 'Sopa cremosa tradicional de papas andinas perfumada con achiote, servida con queso de hoja y aguacate.', 
                 'Papa chola, Papa leona, Queso de hoja, Aguacate, Leche, Achiote', '00:20 hrs'],
                ['Hornado con Llapingachos', 'Sierra', 7.50, 3.40, 20.0, 1, 
                 'Cerdo horneado lentamente a la leña servido con tortillas de papa doradas (llapingachos), mote tierno y agrio casero.', 
                 'Pierna de cerdo, Papa chola, Mote cocido, Agrio tradicional, Lechuga', '00:20 hrs'],
                ['Fritada Imbabureña Tradicional', 'Sierra', 7.00, 3.20, 20.0, 1, 
                 'Trozos de cerdo cocidos en su propia grasa y dorados en paila, servidos con mote, choclo, tostado y maduro frito.', 
                 'Carne de cerdo, Mote, Choclo, Tostado, Plátano maduro', '00:20 hrs'],
                ['Cevichocho con Tostado', 'Sierra', 3.50, 1.50, 20.0, 1, 
                 'Nutritiva ensalada fría de chochos andinos marinados en jugo de tomate de árbol y limón, con cuero y tostado.', 
                 'Chochos andinos, Tomate de árbol, Limón, Tostado de maíz, Cebolla', '00:08 hrs'],

                // Amazonía
                ['Maito de Tilapia Amazónico', 'Amazonía', 8.50, 4.00, 20.0, 1, 
                 'Filete fresco de tilapia marinado con sal en grano y envuelto en auténticas hojas de bijao, asado a las brasas con yuca.', 
                 'Tilapia fresca, Hoja de bijao, Yuca amazónica, Sal marina', '00:25 hrs'],
                ['Caldo de Carachama Tradicional', 'Amazonía', 6.00, 2.80, 20.0, 1, 
                 'Reconfortante caldo amazónico de pez carachama preparado con cilantro silvestre y plátano verde.', 
                 'Pez carachama, Yuca, Cilantro silvestre, Plátano verde', '00:30 hrs'],
                ['Chicha de Chonta y Yuca', 'Amazonía', 2.50, 0.90, 20.0, 1, 
                 'Bebida tradicional de los pueblos amazónicos elaborada a base de fruto de chonta cocido y yuca.', 
                 'Fruto de chonta, Yuca cocida, Panela natural', '00:05 hrs']
            ];

            $stmt = $this->pdo->prepare("INSERT INTO platos (nombre, region, precio, costo, descuento_pro, activo, descripcion, ingredientes, tiempo_preparacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            foreach ($platos as $p) {
                $stmt->execute($p);
            }
        }

        $this->seedGeografia();

        // D. Pedidos iniciales simulados (para que el Dashboard muestre datos reales de ventas, ganancias y rankings)
        $pedidosCount = $this->pdo->query("SELECT COUNT(*) FROM pedidos")->fetchColumn();
        if ($pedidosCount == 0) {
            // Fechas en el mes actual
            $hoy = date('Y-m-d H:i:s');
            $ayer = date('Y-m-d H:i:s', strtotime('-1 day'));
            $semana = date('Y-m-d H:i:s', strtotime('-4 days'));
            $mes_pasado = date('Y-m-d H:i:s', strtotime('-15 days'));

            // Pedido 1: Encebollado (Costa) x 3 + Bolon x 2 (Sucursal 1, Usuario Pro)
            // PVP: (3*6.50 + 2*3.50) = 19.50 + 7.00 = 26.50. Descuento 20% = 5.30. Total = 21.20. Costo = 3*2.90 + 2*1.40 = 11.50. Ganancia = 9.70
            $this->crearPedidoSimulado(2, 1, 26.50, 5.30, 0.0, 21.20, 11.50, 'Entregado', $hoy, [
                ['plato_id' => 1, 'cantidad' => 3, 'precio' => 6.50, 'costo' => 2.90],
                ['plato_id' => 3, 'cantidad' => 2, 'precio' => 3.50, 'costo' => 1.40]
            ]);

            // Pedido 2: Hornado (Sierra) x 4 + Locro x 2 (Sucursal 1, Usuario Free)
            // PVP: 4*7.50 + 2*5.50 = 30.00 + 11.00 = 41.00. Envio: 2.50. Total = 43.50. Costo: 4*3.40 + 2*2.20 = 18.00. Ganancia = 25.50
            $this->crearPedidoSimulado(3, 1, 41.00, 0.0, 2.50, 43.50, 18.00, 'Entregado', $ayer, [
                ['plato_id' => 6, 'cantidad' => 4, 'precio' => 7.50, 'costo' => 3.40],
                ['plato_id' => 5, 'cantidad' => 2, 'precio' => 5.50, 'costo' => 2.20]
            ]);

            // Pedido 3: Maito (Amazonía) x 5 + Caldo x 2 (Sucursal 2, Usuario Pro)
            // PVP: 5*8.50 + 2*6.00 = 42.50 + 12.00 = 54.50. Descuento: 10.90. Total = 43.60. Costo: 5*4.00 + 2*2.80 = 25.60. Ganancia = 18.00
            $this->crearPedidoSimulado(2, 2, 54.50, 10.90, 0.0, 43.60, 25.60, 'Entregado', $semana, [
                ['plato_id' => 9, 'cantidad' => 5, 'precio' => 8.50, 'costo' => 4.00],
                ['plato_id' => 10, 'cantidad' => 2, 'precio' => 6.00, 'costo' => 2.80]
            ]);

            // Pedido 4: Encebollado x 10 (Sucursal 2, Matriz pedido masivo)
            $this->crearPedidoSimulado(3, 2, 65.00, 0.0, 3.00, 68.00, 29.00, 'Entregado', $semana, [
                ['plato_id' => 1, 'cantidad' => 10, 'precio' => 6.50, 'costo' => 2.90]
            ]);

            // Pedido 5: Cevichocho x 2 (Sucursal 3)
            $this->crearPedidoSimulado(3, 3, 7.00, 0.0, 2.00, 9.00, 3.00, 'Entregado', $mes_pasado, [
                ['plato_id' => 8, 'cantidad' => 2, 'precio' => 3.50, 'costo' => 1.50]
            ]);

            // Pedido 6: Cancelado (Para validar exclusión en Core Dashboard)
            $this->crearPedidoSimulado(3, 1, 20.00, 0.0, 2.50, 22.50, 8.00, 'Cancelado', $hoy, [
                ['plato_id' => 1, 'cantidad' => 2, 'precio' => 6.50, 'costo' => 2.90]
            ]);
        }
    }

    private function crearPedidoSimulado($userId, $sucursalId, $subtotal, $descuento, $envio, $total, $costoTotal, $estado, $fecha, $detalles) {
        $ganancia = $total - $costoTotal;
        $stmt = $this->pdo->prepare("INSERT INTO pedidos (usuario_id, sucursal_id, subtotal, descuento, envio, total, costo_total, ganancia, estado, fecha) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $sucursalId, $subtotal, $descuento, $envio, $total, $costoTotal, $ganancia, $estado, $fecha]);
        $pedidoId = $this->pdo->lastInsertId();

        $stmtDet = $this->pdo->prepare("INSERT INTO detalle_pedidos (pedido_id, plato_id, cantidad, precio_unitario, costo_unitario) 
            VALUES (?, ?, ?, ?, ?)");
        foreach ($detalles as $det) {
            $stmtDet->execute([$pedidoId, $det['plato_id'], $det['cantidad'], $det['precio'], $det['costo']]);
        }
    }
}
?>