<?php
require_once 'app/models/User.php';

class AuthController {
    private $userModel;

    public function __construct($db) {
        $this->userModel = new User($db);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = $this->userModel->login($_POST['username'], $_POST['password']);
            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['nombre'] = $user['nombre'];
                $_SESSION['rol'] = $user['rol'];
                $_SESSION['plan'] = $user['plan'];

                // Redirigir según rol
                if ($user['rol'] === 'admin') {
                    header('Location: index.php?action=dashboard');
                } else {
                    header('Location: index.php?action=menu');
                }
                exit;
            } else {
                $error = "Credenciales incorrectas. Intenta con admin / 1234 o usa los botones de acceso rápido.";
                require 'app/views/login.php';
                return;
            }
        }
        require 'app/views/login.php';
    }

    public function quickSwitch() {
        $target = $_GET['role'] ?? 'admin';
        $user = null;

        if ($target === 'admin') {
            $user = $this->userModel->login('admin', '1234');
        } elseif ($target === 'pro') {
            $user = $this->userModel->login('cliente_pro', '1234');
        } elseif ($target === 'free') {
            $user = $this->userModel->login('cliente_free', '1234');
        } elseif ($target === 'invitado') {
            session_destroy();
            session_start();
            $_SESSION['rol'] = 'invitado';
            $_SESSION['plan'] = 'free';
            $_SESSION['nombre'] = 'Visitante Invitado';
            header('Location: index.php?action=menu');
            exit;
        }

        if ($user) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['nombre'] = $user['nombre'];
            $_SESSION['rol'] = $user['rol'];
            $_SESSION['plan'] = $user['plan'];
        }

        if (($user['rol'] ?? '') === 'admin') {
            header('Location: index.php?action=dashboard');
        } else {
            header('Location: index.php?action=menu');
        }
        exit;
    }

    public function logout() {
        session_destroy();
        header('Location: index.php?action=login');
        exit;
    }
}
?>