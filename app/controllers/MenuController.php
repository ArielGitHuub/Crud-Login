<?php
/** Menú público por región/categoría y registro de pedidos de los clientes. */
class MenuController {
    private $platoModel;
    private $sucursalModel;
    private $pedidoModel;

    public function __construct($db) {
        $this->platoModel = new Plato($db);
        $this->sucursalModel = new Sucursal($db);
        $this->pedidoModel = new Pedido($db);
    }

    public function index() {
        $sucursales = $this->sucursalModel->getAll();

        $region = in_array($_GET['region'] ?? '', Plato::REGIONES, true) ? $_GET['region'] : null;
        $categoriaId = (int)($_GET['categoria'] ?? 0) ?: null;
        $sucursalId = (int)($_GET['sucursal_id'] ?? ($sucursales[0]['id'] ?? 0));

        $platos = $this->platoModel->getAll(true, $region, $categoriaId);
        $resumen = $this->platoModel->getResumen();

        // Título de la selección actual
        $titulo = 'Todo el menú';
        if ($region) {
            $titulo = $region;
        } elseif ($categoriaId) {
            foreach ($resumen['categorias'] as $c) {
                if ((int)$c['id'] === $categoriaId) {
                    $titulo = $c['nombre'];
                }
            }
        }

        $isPro = (($_SESSION['plan'] ?? 'free') === 'pro');
        $mensajePedido = flash('pedido');

        $pagina = 'menu';
        require 'app/views/menu.php';
    }

    public function ordenar() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            redirect('menu');
        }
        // Solo usuarios registrados pueden pedir (los invitados únicamente consultan el menú)
        $userId = $_SESSION['user_id'] ?? null;
        if (!$userId) {
            redirect('login');
        }

        $sucursalId = (int)($_POST['sucursal_id'] ?? 0);
        $cantidad = max(1, min(20, (int)($_POST['cantidad'] ?? 1)));

        // Precios y costos salen siempre de la base de datos, nunca del formulario
        $plato = $this->platoModel->getById((int)($_POST['plato_id'] ?? 0));
        $sucursal = $this->sucursalModel->getById($sucursalId);
        if (!$plato || !$plato['activo'] || !$sucursal || !$sucursal['activa']) {
            redirect('menu');
        }

        $isPro = (($_SESSION['plan'] ?? 'free') === 'pro');
        $pedidoId = $this->pedidoModel->create($userId, $sucursalId, [[
            'plato_id' => $plato['id'],
            'cantidad' => $cantidad,
            'precio' => (float)$plato['precio'],
            'costo' => (float)$plato['costo'],
            'descuento_pro' => (float)$plato['descuento_pro'],
        ]], $isPro);

        flash('pedido', "¡Pedido N.º $pedidoId registrado! {$cantidad} × {$plato['nombre']} en {$sucursal['nombre']} ("
            . ($isPro ? 'descuento Pro y envío gratis' : 'cuenta Free, envío $2.50') . ').');
        redirect('menu', ['sucursal_id' => $sucursalId], 'pedidos');
    }
}
