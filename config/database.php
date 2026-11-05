<?php
require_once __DIR__ . '/../app/models/Pedido.php';

/**
 * Conexión SQLite (PDO), esquema, migraciones para bases antiguas y datos de ejemplo.
 * El archivo data.sqlite se crea y se completa solo la primera vez que se abre el sitio.
 */
class Database {
    private const VERSION = 4; // subir al cambiar el esquema o los datos de ejemplo

    private $pdo;

    public function connect() {
        if ($this->pdo === null) {
            $this->pdo = new PDO('sqlite:' . __DIR__ . '/../data.sqlite');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->exec('PRAGMA foreign_keys = ON');

            // La preparación de la base solo corre una vez por versión (se guarda en PRAGMA user_version)
            if ((int)$this->pdo->query('PRAGMA user_version')->fetchColumn() < self::VERSION) {
                $this->createSchema();
                $this->migrate();
                $this->seed();
                $this->pdo->exec('PRAGMA user_version = ' . self::VERSION);
            }
        }
        return $this->pdo;
    }

    // ---------------------------------------------------------------- Esquema

    private function createSchema() {
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            username TEXT UNIQUE NOT NULL,
            nombre TEXT NOT NULL,
            email TEXT,
            password TEXT NOT NULL,
            rol TEXT DEFAULT 'usuario',   -- admin | usuario
            plan TEXT DEFAULT 'free',     -- free | pro
            creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        // Geografía: región -> provincia -> ciudad
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS regiones (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT UNIQUE NOT NULL
        )");
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS provincias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            region_id INTEGER NOT NULL REFERENCES regiones(id),
            nombre TEXT NOT NULL,
            UNIQUE (region_id, nombre)
        )");
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS ciudades (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            provincia_id INTEGER NOT NULL REFERENCES provincias(id),
            nombre TEXT NOT NULL,
            UNIQUE (provincia_id, nombre)
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS categorias (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT UNIQUE NOT NULL
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS sucursales (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            ciudad TEXT NOT NULL,                              -- copia de ciudades.nombre para listados rápidos
            ciudad_id INTEGER REFERENCES ciudades(id),
            direccion TEXT,
            activa INTEGER DEFAULT 1
        )");

        // Core: menú por región con precio y costo (base del cálculo de ganancias)
        $this->pdo->exec("CREATE TABLE IF NOT EXISTS platos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            nombre TEXT NOT NULL,
            region TEXT NOT NULL,                              -- copia de regiones.nombre (se deriva de la provincia)
            provincia_id INTEGER REFERENCES provincias(id),
            categoria_id INTEGER REFERENCES categorias(id),
            precio REAL NOT NULL,
            costo REAL NOT NULL,
            descuento_pro REAL DEFAULT 20.0,
            activo INTEGER DEFAULT 1,
            descripcion TEXT,
            ingredientes TEXT,
            tiempo_preparacion TEXT DEFAULT '00:15 hrs',
            imagen TEXT
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS pedidos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            usuario_id INTEGER REFERENCES users(id),
            sucursal_id INTEGER REFERENCES sucursales(id),
            subtotal REAL NOT NULL,
            descuento REAL DEFAULT 0.0,
            envio REAL DEFAULT 0.0,
            total REAL NOT NULL,
            costo_total REAL NOT NULL,
            ganancia REAL NOT NULL,
            estado TEXT DEFAULT 'Entregado',                   -- Entregado | Preparando | Cancelado
            fecha DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS detalle_pedidos (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            pedido_id INTEGER NOT NULL REFERENCES pedidos(id),
            plato_id INTEGER NOT NULL REFERENCES platos(id),
            cantidad INTEGER NOT NULL,
            precio_unitario REAL NOT NULL,
            costo_unitario REAL NOT NULL
        )");
    }

    // Completa bases creadas con versiones anteriores del proyecto
    private function migrate() {
        $this->addColumn('users', 'nombre', "TEXT NOT NULL DEFAULT ''");
        $this->addColumn('users', 'email', 'TEXT');
        $this->addColumn('users', 'rol', "TEXT DEFAULT 'usuario'");
        $this->addColumn('users', 'plan', "TEXT DEFAULT 'free'");
        $this->addColumn('users', 'creado_en', 'DATETIME');
        $this->addColumn('sucursales', 'ciudad_id', 'INTEGER REFERENCES ciudades(id)');
        $this->addColumn('platos', 'provincia_id', 'INTEGER REFERENCES provincias(id)');
        $this->addColumn('platos', 'categoria_id', 'INTEGER REFERENCES categorias(id)');
        $this->addColumn('platos', 'imagen', 'TEXT');
        $this->pdo->exec("UPDATE users SET rol = 'admin', plan = 'pro', nombre = 'Ariel Tejada', email = 'admin@fuegoandino.ec'
            WHERE username = 'admin' AND (nombre IS NULL OR nombre = '')");
    }

    private function addColumn($tabla, $columna, $definicion) {
        $columnas = array_column($this->pdo->query("PRAGMA table_info($tabla)")->fetchAll(), 'name');
        if (!in_array($columna, $columnas, true)) {
            $this->pdo->exec("ALTER TABLE $tabla ADD COLUMN $columna $definicion");
        }
    }

    // ---------------------------------------------------------- Datos de ejemplo

    private function vacia($tabla) {
        return (int)$this->pdo->query("SELECT COUNT(*) FROM $tabla")->fetchColumn() === 0;
    }

    private function seed() {
        $seed = require __DIR__ . '/seed_data.php';

        $this->seedUsuarios($seed['usuarios']);
        if ($this->vacia('regiones')) {
            $this->seedGeografia($seed['geografia'], $seed['ciudades']);
        }
        if ($this->vacia('categorias')) {
            $ins = $this->pdo->prepare("INSERT INTO categorias (nombre) VALUES (?)");
            foreach ($seed['categorias'] as $c) {
                $ins->execute([$c]);
            }
        }
        if ($this->vacia('sucursales')) {
            $this->seedSucursales($seed['sucursales']);
        }
        if ($this->vacia('platos')) {
            $this->seedPlatos($seed['platos']);
        }
        $this->completarPlatosAntiguos($seed['platos']);
        if ($this->vacia('pedidos')) {
            $this->seedPedidos($seed['pedidos']);
        }
    }

    private function seedUsuarios(array $usuarios) {
        $clave = md5('1234'); // MD5 solo por requisito académico de la Tarea 3 (ver README)
        $ins = $this->pdo->prepare("INSERT INTO users (username, nombre, email, password, rol, plan)
            SELECT ?, ?, ?, ?, ?, ? WHERE NOT EXISTS (SELECT 1 FROM users WHERE username = ?)");
        foreach ($usuarios as [$user, $nombre, $email, $rol, $plan]) {
            $ins->execute([$user, $nombre, $email, $clave, $rol, $plan, $user]);
        }
    }

    private function seedGeografia(array $geografia, array $ciudades) {
        $insR = $this->pdo->prepare("INSERT INTO regiones (nombre) VALUES (?)");
        $insP = $this->pdo->prepare("INSERT INTO provincias (region_id, nombre) VALUES (?, ?)");
        foreach ($geografia as $region => $provincias) {
            $insR->execute([$region]);
            $regionId = $this->pdo->lastInsertId();
            foreach ($provincias as $provincia) {
                $insP->execute([$regionId, $provincia]);
            }
        }
        $insC = $this->pdo->prepare("INSERT INTO ciudades (provincia_id, nombre) SELECT id, ? FROM provincias WHERE nombre = ?");
        foreach ($ciudades as $provincia => $lista) {
            foreach ($lista as $ciudad) {
                $insC->execute([$ciudad, $provincia]);
            }
        }
    }

    private function seedSucursales(array $sucursales) {
        $ins = $this->pdo->prepare("INSERT INTO sucursales (nombre, ciudad, ciudad_id, direccion, activa)
            SELECT ?, c.nombre, c.id, ?, 1 FROM ciudades c WHERE c.nombre = ? LIMIT 1");
        foreach ($sucursales as [$nombre, $ciudad, $direccion]) {
            $ins->execute([$nombre, $direccion, $ciudad]);
        }
        // Sucursales de bases antiguas: enlazar con su ciudad
        $this->pdo->exec("UPDATE sucursales SET ciudad_id = (SELECT id FROM ciudades WHERE ciudades.nombre = sucursales.ciudad LIMIT 1)
            WHERE ciudad_id IS NULL");
    }

    private function seedPlatos(array $platos) {
        $ins = $this->pdo->prepare("INSERT INTO platos
            (nombre, region, provincia_id, categoria_id, precio, costo, descuento_pro, activo, descripcion, ingredientes, tiempo_preparacion, imagen)
            SELECT :nombre, r.nombre, p.id, (SELECT id FROM categorias WHERE nombre = :categoria),
                   :precio, :costo, :descuento, 1, :descripcion, :ingredientes, :tiempo, :imagen
            FROM provincias p JOIN regiones r ON r.id = p.region_id WHERE p.nombre = :provincia");
        foreach ($platos as $p) {
            $ins->execute([
                ':nombre' => $p['nombre'], ':categoria' => $p['categoria'], ':provincia' => $p['provincia'],
                ':precio' => $p['precio'], ':costo' => $p['costo'], ':descuento' => $p['descuento_pro'],
                ':descripcion' => $p['descripcion'], ':ingredientes' => $p['ingredientes'],
                ':tiempo' => $p['tiempo'], ':imagen' => 'assets/img/platos/' . $p['imagen'] . '.jpg',
            ]);
        }
    }

    // Bases creadas antes de existir categorías/provincias/fotos: completa lo que falte por nombre de plato
    private function completarPlatosAntiguos(array $platos) {
        $upd = $this->pdo->prepare("UPDATE platos SET
                provincia_id = COALESCE(provincia_id, (SELECT id FROM provincias WHERE nombre = :provincia)),
                categoria_id = COALESCE(categoria_id, (SELECT id FROM categorias WHERE nombre = :categoria)),
                imagen = COALESCE(imagen, :imagen)
            WHERE nombre = :nombre");
        foreach ($platos as $p) {
            $upd->execute([':provincia' => $p['provincia'], ':categoria' => $p['categoria'],
                ':imagen' => 'assets/img/platos/' . $p['imagen'] . '.jpg', ':nombre' => $p['nombre']]);
        }
    }

    // Pedidos de ejemplo repartidos en el tiempo; se calculan con las mismas reglas del core (Pedido::create)
    private function seedPedidos(array $config) {
        $pedidoModel = new Pedido($this->pdo);
        $clientes = $this->pdo->query("SELECT id, plan FROM users WHERE rol = 'usuario' ORDER BY id")->fetchAll();
        $sucursales = $this->pdo->query("SELECT id FROM sucursales WHERE activa = 1 ORDER BY id")->fetchAll(PDO::FETCH_COLUMN);
        $platos = $this->pdo->query("SELECT id, precio, costo, descuento_pro FROM platos WHERE activo = 1 ORDER BY id")->fetchAll();
        if (!$clientes || !$sucursales || !$platos) {
            return;
        }

        mt_srand($config['semilla']); // determinista: siempre los mismos datos de ejemplo
        foreach ($config['dias'] as $dias => $cantidad) {
            for ($i = 0; $i < $cantidad; $i++) {
                $cliente = $clientes[mt_rand(0, count($clientes) - 1)];
                $items = [];
                foreach ((array)array_rand($platos, mt_rand(1, 3)) as $k) {
                    $p = $platos[$k];
                    $items[] = ['plato_id' => $p['id'], 'cantidad' => mt_rand(1, 3), 'precio' => (float)$p['precio'],
                        'costo' => (float)$p['costo'], 'descuento_pro' => (float)$p['descuento_pro']];
                }
                $horaMax = $dias === 0 ? max(10, (int)date('G')) : 21;
                $fecha = date('Y-m-d', strtotime("-$dias days")) . sprintf(' %02d:%02d:00', mt_rand(10, $horaMax), mt_rand(0, 59));
                $estado = mt_rand(1, 10) === 1 ? 'Cancelado' : 'Entregado';
                $pedidoModel->create($cliente['id'], $sucursales[mt_rand(0, count($sucursales) - 1)], $items,
                    $cliente['plan'] === 'pro', $estado, $fecha);
            }
        }
    }
}
