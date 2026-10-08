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
                header('Location: index.php?action=crud');
                exit;
            } else {
                $error = "Credenciales incorrectas";
                require 'app/views/login.php';
                return;
            }
        }
        require 'app/views/login.php';
    }

    public function logout() {
        session_destroy();
        header('Location: index.php');
        exit;
    }
}
?>