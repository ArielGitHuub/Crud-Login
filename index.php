<?php
session_start();
require_once 'config/database.php';
require_once 'app/controllers/AuthController.php';
require_once 'app/controllers/DashboardController.php';
require_once 'app/controllers/MenuController.php';
require_once 'app/controllers/PlatoController.php';
require_once 'app/controllers/SucursalController.php';

$db = (new Database())->connect();
$action = $_GET['action'] ?? 'login'; // la pantalla de entrada siempre es el login

switch ($action) {
    case 'login':
        $controller = new AuthController($db);
        $controller->login();
        break;

    case 'logout':
        $controller = new AuthController($db);
        $controller->logout();
        break;

    case 'switch_user':
        $controller = new AuthController($db);
        $controller->quickSwitch();
        break;

    case 'dashboard':
        $controller = new DashboardController($db);
        $controller->index();
        break;

    case 'menu':
        $controller = new MenuController($db);
        $controller->index();
        break;

    case 'ordenar':
        $controller = new MenuController($db);
        $controller->ordenar();
        break;

    case 'provincias':
        $controller = new PlatoController($db);
        $controller->provincias();
        break;

    case 'sucursales':
        $controller = new SucursalController($db);
        $controller->index();
        break;

    case 'ciudades':
        $controller = new SucursalController($db);
        $controller->ciudades();
        break;

    case 'platos':
    case 'crud':
        $controller = new PlatoController($db);
        $controller->index();
        break;

    case 'dashboard_data':
        $controller = new DashboardController($db);
        $controller->data();
        break;

    default:
        header('Location: index.php?action=login');
        break;
}
?>