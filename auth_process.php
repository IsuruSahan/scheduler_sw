<?php
// auth_process.php
require_once 'config/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT users.*, roles.role_name FROM users 
                           JOIN roles ON users.role_id = roles.id 
                           WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify password (assuming you used password_hash during registration)
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role_name'];
        header("Location: " . BASE_URL . "index.php");
        exit();
    } else {
        echo "Invalid login credentials.";
    }
}
?>