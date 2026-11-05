<?php
require_once 'app/models/Plato.php';
require_once 'app/models/Sucursal.php';
require_once 'app/models/Pedido.php';

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
        $region = $_GET['region'] ?? null;
        $sucursalId = $_GET['sucursal_id'] ?? 1;

        // Platos filtrados por región
        $platos = $this->platoModel->getAll(true, $region);
        $sucursales = $this->sucursalModel->getAll();
        $conteoRegiones = $this->platoModel->countByRegion();

        $userPlan = $_SESSION['plan'] ?? 'free';
        $userRol = $_SESSION['rol'] ?? 'invitado';
        $isPro = ($userPlan === 'pro');

        $mensajePedido = $_SESSION['mensaje_pedido'] ?? null;
        unset($_SESSION['mensaje_pedido']);

        require 'app/views/menu.php';
    }

    public function ordenar() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $userId = $_SESSION['user_id'] ?? null;
            // Solo usuarios registrados pueden pedir (los invitados únicamente consultan el menú)
            if (!$userId) {
                header('Location: index.php?action=login');
                exit;
            }

            $sucursalId = (int)($_POST['sucursal_id'] ?? 0);
            $platoId = (int)($_POST['plato_id'] ?? 0);
            $cantidad = (int)($_POST['cantidad'] ?? 1);
            $cantidad = max(1, min(20, $cantidad));

            // Precios y costos salen siempre de la base de datos, nunca del formulario
            $plato = $this->platoModel->getById($platoId);
            $sucursal = $this->sucursalModel->getById($sucursalId);
            if (!$plato || !$plato['activo'] || !$sucursal || !$sucursal['activa']) {
                header('Location: index.php?action=menu');
                exit;
            }

            $isPro = (($_SESSION['plan'] ?? 'free') === 'pro');

            $items = [
                [
                    'plato_id' => $plato['id'],
                    'cantidad' => $cantidad,
                    'precio' => (float)$plato['precio'],
                    'costo' => (float)$plato['costo'],
                    'descuento_pro' => (float)$plato['descuento_pro']
                ]
            ];

            $pedidoId = $this->pedidoModel->create($userId, $sucursalId, $items, $isPro);

            $_SESSION['mensaje_pedido'] = [
                'tipo' => 'exito',
                'texto' => "¡Pedido N°{$pedidoId} registrado exitosamente! Se procesó para la sucursal seleccionada con los beneficios de tu cuenta (" . ($isPro ? "descuento Pro y envío gratis" : "cuenta Free, envío $2.50") . ")."
            ];

            header('Location: index.php?action=menu');
            exit;
        }
        header('Location: index.php?action=menu');
        exit;
    }
}
?>
