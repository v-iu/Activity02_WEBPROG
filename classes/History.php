<?php
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