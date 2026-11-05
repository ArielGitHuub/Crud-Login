<?php
require_once 'app/models/Dashboard.php';
require_once 'app/models/Sucursal.php';

class DashboardController {
    private $dashboardModel;
    private $sucursalModel;

    public function __construct($db) {
        $this->dashboardModel = new Dashboard($db);
        $this->sucursalModel = new Sucursal($db);
    }

    public function index() {
        // Validación de rol (Core: Solo administradores pueden ver dashboard)
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            header('Location: index.php?action=login&error=unauthorized');
            exit;
        }

        $sucursalId = $_GET['sucursal_id'] ?? 'all';
        $periodo = $_GET['periodo'] ?? 'mes';

        $indicadores = $this->dashboardModel->getIndicadores($sucursalId, $periodo);
        $platos = $this->dashboardModel->getPlatosRanking($sucursalId, $periodo);
        $sucursales = $this->sucursalModel->getAll();

        require 'app/views/dashboard.php';
    }
}
?>
