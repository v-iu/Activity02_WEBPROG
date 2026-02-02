<?php
require 'init.php';

$db = new Database();
$userObj = new User($db->getConnection());

if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $action = $_POST['action'];

    if ($action === 'register') {
        $message = $userObj->register($username, $password);
    } elseif ($action === 'login') {
        if ($userObj->login($username, $password)) {
            header("Location: dashboard.php");
            exit;
        } else {
            $message = "Invalid credentials.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <style>body{font-family:monospace;display:flex;justify-content:center;height:100vh;align-items:center;}.box{border:1px solid #000;padding:2rem;}input{display:block;margin:10px 0;padding:10px;width:100%;box-sizing:border-box;}button{background:none;border:1px solid #000;padding:10px;cursor:pointer;width:100%;margin-top:5px;}button:hover{background:#eee;}</style>
</head>
<body>
    <div class="box">
        <h2>Task Manager</h2>
        <p style="color:red"><?= htmlspecialchars($message) ?></p>
        <form method="POST">
            <input type="text" name="username" placeholder="Username" required>
            <input type="password" name="password" placeholder="Password" required>
            <button type="submit" name="action" value="login">Login</button>
            <button type="submit" name="action" value="register">Register</button>
        </form>
    </div>
</body>
</html>