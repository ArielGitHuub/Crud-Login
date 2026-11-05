<?php
class User {
    private $db;

    public function __construct($db) { 
        $this->db = $db; 
    }

    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = :username AND password = :password");
        $stmt->execute([
            'username' => $username, 
            'password' => md5($password)
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT id, username, nombre, email, rol, plan, creado_en FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getAll() {
        return $this->db->query("SELECT id, username, nombre, email, rol, plan, creado_en FROM users ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countByPlan() {
        $free = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE rol = 'usuario' AND plan = 'free'")->fetchColumn();
        $pro = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE rol = 'usuario' AND plan = 'pro'")->fetchColumn();
        $total = (int)$this->db->query("SELECT COUNT(*) FROM users WHERE rol = 'usuario'")->fetchColumn();
        return [
            'free' => $free,
            'pro' => $pro,
            'total' => $total
        ];
    }

    public function register($username, $nombre, $email, $password, $rol = 'usuario', $plan = 'free') {
        $stmt = $this->db->prepare("INSERT INTO users (username, nombre, email, password, rol, plan) VALUES (?, ?, ?, ?, ?, ?)");
        return $stmt->execute([
            $username,
            $nombre,
            $email,
            md5($password),
            $rol,
            $plan
        ]);
    }
}
?>