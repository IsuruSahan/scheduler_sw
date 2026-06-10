<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) session_start();

// Redirect to login if not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Function to restrict access based on role
function check_role($allowed_roles) {
    if (!in_array($_SESSION['role_id'], $allowed_roles)) {
        die("Access Denied: You do not have permission to view this page.");
    }
}
?>