<?php
class Database {
    private $pdo;

    public function connect() {
        if ($this->pdo == null) {

            $this->pdo = new PDO('sqlite:' . __DIR__ . '/../data.sqlite');
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->createTables();
        }
        return $this->pdo;
    }

    private function createTables() {

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY,
            username TEXT UNIQUE,
            password TEXT
        )");
        

        $this->pdo->exec("CREATE TABLE IF NOT EXISTS items (
            id INTEGER PRIMARY KEY,
            name TEXT,
            description TEXT
        )");
        

        $userCount = $this->pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        if ($userCount == 0) {
            $pass_md5 = md5('1234');
            $this->pdo->exec("INSERT INTO users (username, password) VALUES ('admin', '$pass_md5')");
        }
    }
}
?>