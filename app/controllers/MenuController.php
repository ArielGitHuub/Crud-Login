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
            $sucursalId = (int)($_POST['sucursal_id'] ?? 1);
            $platoId = (int)($_POST['plato_id'] ?? 1);
            $cantidad = (int)($_POST['cantidad'] ?? 1);

            $plato = $this->platoModel->getById($platoId);
            if (!$plato) {
                header('Location: index.php?action=menu');
                exit;
            }

            $userPlan = $_SESSION['plan'] ?? 'free';
            $isPro = ($userPlan === 'pro');

            // Si es invitado y quiere pedir, podemos crear usuario temporal o registrar con usuario demo
            if (!$userId) {
                $userId = 3; // Usuario Free por defecto
            }

            $items = [
                [
                    'plato_id' => $plato['id'],
                    'cantidad' => $cantidad,
                    'precio' => (float)$plato['precio'],
                    'costo' => (float)$plato['costo']
                ]
            ];

            $pedidoId = $this->pedidoModel->create($userId, $sucursalId, $items, $isPro);

            $_SESSION['mensaje_pedido'] = [
                'tipo' => 'exito',
                'texto' => "¡Pedido N°{$pedidoId} registrado exitosamente! Se procesó para la sucursal seleccionada con los beneficios de tu cuenta (" . ($isPro ? "Descuento Pro 20% y Envío Gratis" : "Cuenta Free") . ")."
            ];

            header('Location: index.php?action=menu');
            exit;
        }
        header('Location: index.php?action=menu');
        exit;
    }
}
?>
