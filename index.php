<?php
/**
 * Front controller: todas las peticiones entran por aquí y se despachan a un controlador.
 */
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();

require_once __DIR__ . '/app/helpers.php';
require_once __DIR__ . '/config/database.php';

// Carga automática de controladores y modelos
spl_autoload_register(function ($clase) {
    foreach (['app/controllers', 'app/models'] as $dir) {
        $archivo = __DIR__ . "/$dir/$clase.php";
        if (is_file($archivo)) {
            require_once $archivo;
            return;
        }
    }
});

// Rutas: acción => [Controlador, método]
const RUTAS = [
    'login'          => ['AuthController', 'login'],
    'logout'         => ['AuthController', 'logout'],
    'switch_user'    => ['AuthController', 'quickSwitch'],
    'inicio'         => ['HomeController', 'inicio'],
    'nosotros'       => ['HomeController', 'nosotros'],
    'menu'           => ['MenuController', 'index'],
    'ordenar'        => ['MenuController', 'ordenar'],
    'dashboard'      => ['DashboardController', 'index'],
    'dashboard_data' => ['DashboardController', 'data'],
    'platos'         => ['PlatoController', 'index'],
    'provincias'     => ['PlatoController', 'provincias'],
    'sucursales'     => ['SucursalController', 'index'],
    'ciudades'       => ['SucursalController', 'ciudades'],
];

$action = $_GET['action'] ?? 'login'; // la pantalla de entrada siempre es el login
if (!isset(RUTAS[$action])) {
    redirect('login');
}

// Todo formulario POST debe traer el token CSRF de la sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !csrf_valid()) {
    http_response_code(419);
    exit('La sesión del formulario expiró. <a href="index.php">Volver al inicio</a>');
}

[$clase, $metodo] = RUTAS[$action];
$db = (new Database())->connect();
(new $clase($db))->$metodo();
