<?php
session_start();
require 'db.php';

$db = new Database();
$pdo = $db->getConnection();
$historyManager = new History($pdo);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

$logs = $historyManager->getAll($user_id);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>History Log</title>
    <style>
        body { font-family: monospace; padding: 20px; }
        .wrapper { max-width: 800px; margin: 0 auto; border: 1px solid #000; padding: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        th { background: #eee; }
        a { color: #000; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Activity History</h2>
        <a href="dashboard.php">Back to Dashboard</a>
        
        <table>
            <tr>
                <th>Time</th>
                <th>Action</th>
                <th>Task Title</th>
            </tr>
            <?php foreach ($logs as $log): ?>
            <tr>
                <td><?= htmlspecialchars($log['created_at']) ?></td>
                <td><?= htmlspecialchars($log['action']) ?></td>
                <td><?= htmlspecialchars($log['task_title']) ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
    </div>
</body>
</html>