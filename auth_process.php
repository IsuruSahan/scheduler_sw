<?php
require_once 'config/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Securely query user and join with roles
    $stmt = $pdo->prepare("SELECT u.*, r.role_name 
                           FROM users u 
                           JOIN roles r ON u.role_id = r.id 
                           WHERE u.email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify hash
    if ($user && password_verify($password, $user['password'])) {
        // Successful login
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
        $_SESSION['role']    = $user['role_name']; 
        
        // Updated Redirect based on role_id
        if ($user['role_id'] == 1) {
            header("Location: admin/settings.php");
        } elseif ($user['role_id'] == 3) {
            header("Location: marketing/upload_media.php");
        } else {
            header("Location: scheduler/create.php");
        }
        exit();
    } else {
        // Invalid credentials: Redirect back to login with an error flag
        header("Location: login.php?error=invalid");
        exit();
    }
} else {
    // If someone tries to access this script directly without POST
    header("Location: login.php");
    exit();
}