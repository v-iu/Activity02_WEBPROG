<?php
class User {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function register($username, $password) {
        if (strlen($username) < 3 || strlen($password) < 4) {
            return "Username > 2 chars, Password > 3 chars.";
        }
        
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt->execute([$username]);
        if ($stmt->rowCount() > 0) {
            return "Username taken.";
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
        if ($stmt->execute([$username, $hash])) {
            return "Registered! Please login.";
        }
        return "Registration failed.";
    }

    public function login($username, $password) {
        $stmt = $this->pdo->prepare("SELECT id, password FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $username;
            return true;
        }
        return false;
    }
}
?>