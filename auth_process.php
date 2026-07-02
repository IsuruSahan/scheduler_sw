<?php
require_once 'config/config.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Securely query user and join with roles to get the role name
    $stmt = $pdo->prepare("SELECT u.*, r.role_name 
                           FROM users u 
                           JOIN roles r ON u.role_id = r.id 
                           WHERE u.email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    // Verify hash
    if ($user && password_verify($password, $user['password'])) {
        // Clear old session data
        session_regenerate_id(true);

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id'];
        
        // This is the key fix: Assign the string name for your header checks
$_SESSION['role'] = $user['role_name']; 
        
        // Updated Redirect based on role_id
        if ($user['role_id'] == 1) {
            header("Location: admin/settings.php");
        } elseif ($user['role_id'] == 3) {
            // Marketing Officer (Role ID 3) redirected to their specific upload page
            header("Location: marketing/upload_media.php");
        } else {
            // All other roles (like Scheduler, ID 2) go to create schedule
            header("Location: scheduler/create.php");
        }
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}