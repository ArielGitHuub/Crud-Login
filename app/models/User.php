<?php
class User {
    private $db;

    public function __construct($db) { $this->db = $db; }

    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username AND password = :password");

        $stmt->execute(['username' => $username, 'password' => md5($password)]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>