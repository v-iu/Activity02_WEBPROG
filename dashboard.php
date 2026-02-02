<?php
require 'init.php';

$db = new Database();
$pdo = $db->getConnection();
$taskManager = new Task($pdo);
$historyManager = new History($pdo);

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_task'])) {
        $title = trim($_POST['title']);
        $due_date = !empty($_POST['due_date']) ? $_POST['due_date'] : null;
        
        if ($taskManager->add($user_id, $title, $due_date)) {
            $historyManager->log($user_id, $title, 'Created');
        }
    } elseif (isset($_POST['delete_task'])) {
        $task_id = (int)$_POST['task_id'];
        
        $task = $taskManager->getTask($task_id, $user_id);
        
        if ($task) {
            $taskManager->delete($task_id, $user_id);
            $historyManager->log($user_id, $task['title'], 'Deleted');
        }
    } elseif (isset($_POST['toggle_task'])) {
        $task_id = (int)$_POST['task_id'];
        $current_status = $_POST['current_status'];
        
        $task = $taskManager->getTask($task_id, $user_id);
        
        if ($task) {
            $new_status = $taskManager->toggle($task_id, $user_id, $current_status);
            $historyManager->log($user_id, $task['title'], "Marked $new_status");
        }
    }
    header("Location: dashboard.php");
    exit;
}

$tasks = $taskManager->getAll($user_id, $_GET['search'] ?? '', $_GET['filter'] ?? '');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <style>
        body { font-family: monospace; padding: 20px; background: #fff; color: #000; }
        .wrapper { max-width: 800px; margin: 0 auto; border: 1px solid #000; padding: 20px; }
        .header { display: flex; justify-content: space-between; align-items: center; border-bottom: 1px solid #000; padding-bottom: 10px; margin-bottom: 20px; }
        .task-list { list-style: none; padding: 0; }
        .task-item { display: flex; justify-content: space-between; align-items: center; padding: 10px; border: 1px solid #000; margin-bottom: -1px; }
        .completed { text-decoration: line-through; opacity: 0.5; }
        .btn { padding: 5px 10px; border: 1px solid #000; background: none; cursor: pointer; margin-left: 5px; }
        .btn:hover { background: #eee; }
        .logout, .history-link { color: #000; text-decoration: underline; margin-left: 15px; }
        form.add-form { display: flex; gap: 10px; margin-bottom: 20px; border: 1px solid #000; padding: 10px; }
        form.filter-form { margin-bottom: 20px; display: flex; gap: 10px; }
        input, select { padding: 8px; border: 1px solid #000; }
        .meta { font-size: 0.8em; margin-left: 10px; }
        .status-label { font-weight: bold; margin-right: 5px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <div class="header">
            <h2>User: <?= htmlspecialchars(ucfirst($_SESSION['username'])) ?></h2>
            <div>
                <a href="history.php" class="history-link">History Log</a>
                <a href="logout.php" class="logout">Logout</a>
            </div>
        </div>

        <form method="GET" class="filter-form">
            <input type="text" name="search" placeholder="Search tasks..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
            <select name="filter">
                <option value="">All Tasks</option>
                <option value="todo" <?= (isset($_GET['filter']) && $_GET['filter'] === 'todo') ? 'selected' : '' ?>>To Do</option>
                <option value="done" <?= (isset($_GET['filter']) && $_GET['filter'] === 'done') ? 'selected' : '' ?>>Done</option>
                <option value="overdue" <?= (isset($_GET['filter']) && $_GET['filter'] === 'overdue') ? 'selected' : '' ?>>Overdue</option>
            </select>
            <button type="submit" class="btn">Apply</button>
            <?php if(isset($_GET['search']) || isset($_GET['filter'])): ?>
                <a href="dashboard.php" class="btn" style="text-decoration:none; color:black; padding-top:7px;">Clear</a>
            <?php endif; ?>
        </form>

        <form method="POST" class="add-form">
            <input type="text" name="title" placeholder="New Task..." required style="flex-grow:1;">
            <input type="date" name="due_date" title="Due Date">
            <button type="submit" name="add_task" class="btn">Add Task</button>
        </form>

        <ul class="task-list">
            <?php foreach ($tasks as $task): ?>
                <?php
                    $status_text = 'To Do';
                    if ($task['status'] === 'completed') {
                        $status_text = 'Done';
                    } elseif (!empty($task['due_date']) && $task['due_date'] < date('Y-m-d')) {
                        $status_text = 'Overdue';
                    }
                ?>
                <li class="task-item">
                    <div>
                        <span class="status-label">[<?= $status_text ?>]</span>
                        <span class="<?= $task['status'] === 'completed' ? 'completed' : '' ?>">
                            <?= htmlspecialchars($task['title']) ?>
                        </span>
                        <?php if ($task['due_date']): ?>
                            <span class="meta">[Due: <?= htmlspecialchars($task['due_date']) ?>]</span>
                        <?php endif; ?>
                    </div>
                    
                    <div style="display:flex; gap:5px;">
                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                            <input type="hidden" name="current_status" value="<?= $task['status'] ?>">
                            <button type="submit" name="toggle_task" class="btn">
                                <?= $task['status'] === 'pending' ? '✓' : 'Undo' ?>
                            </button>
                        </form>

                        <form method="POST" style="display:inline;">
                            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                            <button type="submit" name="delete_task" class="btn">X</button>
                        </form>
                    </div>
                </li>
            <?php endforeach; ?>
            
            <?php if (count($tasks) === 0): ?>
                <p style="text-align:center; color:#888;">No tasks yet.</p>
            <?php endif; ?>
        </ul>
    </div>
</body>
</html>
