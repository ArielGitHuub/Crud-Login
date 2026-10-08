<?php
session_start();
require_once 'config/database.php';
require_once 'app/controllers/AuthController.php';
require_once 'app/controllers/ItemController.php';

$db = (new Database())->connect();
$action = $_GET['action'] ?? 'login';

switch ($action) {
    case 'login':
        $controller = new AuthController($db);
        $controller->login();
        break;
    case 'logout':
        $controller = new AuthController($db);
        $controller->logout();
        break;
    case 'crud':
        $controller = new ItemController($db);
        $controller->index();
        break;
    default:
        header('Location: index.php?action=login');
        break;
}
?>