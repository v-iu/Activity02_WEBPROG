<?php
session_start();
require 'db.php';
require 'task.php';

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">
    <div class="max-w-4xl mx-auto p-6">
        <!-- Header -->
        <header class="flex flex-col sm:flex-row justify-between items-center mb-8 border-b border-gray-200 pb-6">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900">Task Manager</h1>
            <div class="flex items-center gap-6 mt-4 sm:mt-0 text-sm font-medium">
                <span class="text-gray-500">User: <span class="text-gray-900"><?= htmlspecialchars(ucfirst($_SESSION['username'])) ?></span></span>
                <nav class="flex gap-4">
                    <a href="users.php" class="text-gray-600 hover:text-black transition-colors">Users</a>
                    <a href="history.php" class="text-gray-600 hover:text-black transition-colors">History</a>
                    <a href="logout.php" class="text-red-600 hover:text-red-800 transition-colors">Logout</a>
                </nav>
            </div>
        </header>

        <!-- Controls -->
        <div class="bg-white p-4 rounded-lg shadow-sm border border-gray-200 mb-6">
            <form method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" placeholder="Search tasks..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>" 
                       class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black sm:text-sm border p-2 outline-none">
                <select name="filter" class="rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black sm:text-sm border p-2 bg-white outline-none">
                    <option value="">All Tasks</option>
                    <option value="todo" <?= (isset($_GET['filter']) && $_GET['filter'] === 'todo') ? 'selected' : '' ?>>To Do</option>
                    <option value="done" <?= (isset($_GET['filter']) && $_GET['filter'] === 'done') ? 'selected' : '' ?>>Done</option>
                    <option value="overdue" <?= (isset($_GET['filter']) && $_GET['filter'] === 'overdue') ? 'selected' : '' ?>>Overdue</option>
                </select>
                <button type="submit" class="bg-black text-white px-4 py-2 rounded-md text-sm font-medium hover:bg-gray-800 transition-colors">Apply</button>
                <?php if(isset($_GET['search']) || isset($_GET['filter'])): ?>
                    <a href="dashboard.php" class="text-gray-500 hover:text-black px-4 py-2 text-sm font-medium flex items-center">Clear</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Add Task -->
        <form method="POST" class="mb-8 flex flex-col sm:flex-row gap-3">
            <input type="text" name="title" placeholder="What needs to be done?" required 
                   class="flex-1 rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black border p-3 outline-none">
            <input type="date" name="due_date" 
                   class="rounded-md border-gray-300 shadow-sm focus:border-black focus:ring-black border p-3 text-gray-500 outline-none">
            <button type="submit" name="add_task" class="bg-black text-white px-6 py-3 rounded-md font-medium hover:bg-gray-800 transition-colors shadow-lg shadow-gray-200">Add Task</button>
        </form>

        <!-- List -->
        <div class="space-y-3">
            <?php foreach ($tasks as $task): ?>
                <?php
                    $status_text = 'To Do';
                    $status_color = 'bg-gray-100 text-gray-600';
                    if ($task['status'] === 'completed') {
                        $status_text = 'Done';
                        $status_color = 'bg-green-100 text-green-700';
                    } elseif (!empty($task['due_date']) && $task['due_date'] < date('Y-m-d')) {
                        $status_text = 'Overdue';
                        $status_color = 'bg-red-100 text-red-700';
                    }
                ?>
                <div class="group flex items-center justify-between p-4 bg-white rounded-lg border border-gray-200 shadow-sm hover:shadow-md transition-all">
                    <div class="flex items-center gap-4">
                        <form method="POST" class="flex items-center">
                            <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                            <input type="hidden" name="current_status" value="<?= $task['status'] ?>">
                            <button type="submit" name="toggle_task" class="w-6 h-6 rounded-full border-2 flex items-center justify-center transition-colors <?= $task['status'] === 'completed' ? 'bg-black border-black text-white' : 'border-gray-300 hover:border-black' ?>">
                                <?php if($task['status'] === 'completed'): ?>
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                                <?php endif; ?>
                            </button>
                        </form>
                        
                        <div>
                            <p class="<?= $task['status'] === 'completed' ? 'line-through text-gray-400' : 'text-gray-900' ?> font-medium text-lg">
                                <?= htmlspecialchars($task['title']) ?>
                            </p>
                            <div class="flex gap-2 text-xs mt-1 items-center">
                                <span class="px-2 py-0.5 rounded font-medium <?= $status_color ?>"><?= $status_text ?></span>
                                <?php if ($task['due_date']): ?>
                                    <span class="text-gray-400">Due: <?= htmlspecialchars($task['due_date']) ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="task_id" value="<?= $task['id'] ?>">
                        <button type="submit" name="delete_task" class="text-gray-300 hover:text-red-600 p-2 transition-colors" title="Delete Task">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            <?php endforeach; ?>
            
            <?php if (count($tasks) === 0): ?>
                <div class="text-center py-12 bg-gray-50 rounded-lg border border-dashed border-gray-300">
                    <p class="text-gray-500">No tasks found. Start by adding one!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
