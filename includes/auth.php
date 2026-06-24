<?php
if (session_status() === PHP_SESSION_NONE) session_start();

// Get the current script name to avoid looping on the login page
$current_script = basename($_SERVER['SCRIPT_NAME']);

// Only redirect if not on login page
if (!isset($_SESSION['user_id']) && $current_script !== 'login.php') {
    header("Location: /scheduler_sw/login.php");
    exit();
}

function check_role($allowed_roles) {
    if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], $allowed_roles)) {
        die("Access Denied: You do not have permission to view this page.");
    }
}
?>