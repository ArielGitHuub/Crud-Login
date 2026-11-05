<?php
require_once 'app/models/Dashboard.php';
require_once 'app/models/Sucursal.php';

class DashboardController {
    private $dashboardModel;
    private $sucursalModel;

    public function __construct($db) {
        // Core: solo administradores pueden ver el dashboard
        if (!isset($_SESSION['rol']) || $_SESSION['rol'] !== 'admin') {
            header('Location: index.php?action=login&error=unauthorized');
            exit;
        }
        $this->dashboardModel = new Dashboard($db);
        $this->sucursalModel = new Sucursal($db);
    }

    private function filtros() {
        $periodo = in_array($_GET['periodo'] ?? '', ['hoy', 'semana', 'mes'], true) ? $_GET['periodo'] : 'mes';
        $sucursalId = $_GET['sucursal_id'] ?? 'all';
        if ($sucursalId !== 'all') {
            $sucursalId = (int)$sucursalId;
        }
        return [$sucursalId, $periodo];
    }

    public function index() {
        [$sucursalId, $periodo] = $this->filtros();
        $paquete = $this->dashboardModel->getPaquete($sucursalId, $periodo);
        $sucursales = $this->sucursalModel->getAll();
        require 'app/views/dashboard.php';
    }

    // JSON para el refresco en tiempo real
    public function data() {
        [$sucursalId, $periodo] = $this->filtros();
        header('Content-Type: application/json; charset=utf-8');
        header('Cache-Control: no-store');
        echo json_encode($this->dashboardModel->getPaquete($sucursalId, $periodo), JSON_UNESCAPED_UNICODE);
    }
}
?>
