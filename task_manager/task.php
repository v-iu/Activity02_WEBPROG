<?php
class Task {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function add($userId, $title, $dueDate) {
        if (empty($title)) return false;
        $stmt = $this->pdo->prepare("INSERT INTO tasks (user_id, title, due_date) VALUES (?, ?, ?)");
        return $stmt->execute([$userId, $title, $dueDate]);
    }

    public function getTask($taskId, $userId) {
        $stmt = $this->pdo->prepare("SELECT * FROM tasks WHERE id = ? AND user_id = ?");
        $stmt->execute([$taskId, $userId]);
        return $stmt->fetch();
    }

    public function delete($taskId, $userId) {
        $stmt = $this->pdo->prepare("DELETE FROM tasks WHERE id = ? AND user_id = ?");
        return $stmt->execute([$taskId, $userId]);
    }

    public function toggle($taskId, $userId, $currentStatus) {
        $newStatus = ($currentStatus === 'pending') ? 'completed' : 'pending';
        $stmt = $this->pdo->prepare("UPDATE tasks SET status = ? WHERE id = ? AND user_id = ?");
        $stmt->execute([$newStatus, $taskId, $userId]);
        return $newStatus;
    }

    public function getAll($userId, $search = '', $filter = '') {
        $sql = "SELECT * FROM tasks WHERE user_id = :uid";
        $params = [':uid' => $userId];

        if (!empty($search)) {
            $sql .= " AND title LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        if ($filter === 'todo') {
            $sql .= " AND status = 'pending' AND (due_date >= CURDATE() OR due_date IS NULL)";
        } elseif ($filter === 'done') {
            $sql .= " AND status = 'completed'";
        } elseif ($filter === 'overdue') {
            $sql .= " AND status = 'pending' AND due_date < CURDATE() AND due_date IS NOT NULL";
        }

        $sql .= " ORDER BY created_at DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
}
?>