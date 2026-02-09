<?php
class Database {
    private $pdo;

    public function __construct() {
        $host = 'localhost';
        $db   = 'task_manager';
        $user = 'root';
        $pass = '';
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $this->pdo = new PDO($dsn, $user, $pass, $options);
        } catch (\PDOException $e) {
            throw new \PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    public function getConnection() {
        return $this->pdo;
    }
}

class History {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function log($userId, $taskTitle, $action) {
        $stmt = $this->pdo->prepare("INSERT INTO task_logs (user_id, task_title, action) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $taskTitle, $action]);
    }

    public function getAll($userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM task_logs WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }
}
?>
