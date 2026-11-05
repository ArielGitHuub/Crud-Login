<?php
class AuthController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    // La pantalla de entrada del sitio: siempre se muestra, aunque ya haya una sesión abierta
    public function login() {
        $error = null;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->userModel->login(trim($_POST['username'] ?? ''), $_POST['password'] ?? '');
            if ($user) {
                $this->iniciarSesion($user);
            }
            $error = 'Usuario o contraseña incorrectos.';
        }
        require 'app/views/login.php';
    }

    // Acceso rápido por rol para la demostración (solo para desarrollo; quitar en producción)
    public function quickSwitch() {
        $cuentas = ['admin' => 'admin', 'pro' => 'cliente_pro', 'free' => 'cliente_free'];
        $rol = $_GET['role'] ?? 'admin';

        if ($rol === 'invitado') {
            $_SESSION = [];
            session_regenerate_id(true);
            $_SESSION['rol'] = 'invitado';
            $_SESSION['plan'] = 'free';
            $_SESSION['nombre'] = 'Visitante';
            redirect('inicio');
        }
        $user = isset($cuentas[$rol]) ? $this->userModel->login($cuentas[$rol], '1234') : null;
        if (!$user) {
            redirect('login');
        }
        $this->iniciarSesion($user);
    }

    public function logout() {
        $_SESSION = [];
        session_destroy();
        redirect('login');
    }

    private function iniciarSesion(array $user): void {
        session_regenerate_id(true); // evita fijación de sesión
        $_SESSION = [
            'user_id' => $user['id'],
            'username' => $user['username'],
            'nombre' => $user['nombre'],
            'rol' => $user['rol'],
            'plan' => $user['plan'],
        ];
        redirect($user['rol'] === 'admin' ? 'dashboard' : 'inicio');
    }
}
