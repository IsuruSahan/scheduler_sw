<?php
require_once 'config/config.php';
session_start();

// If not logged in, go to login
if (!isset($_SESSION['user_id'])) {
    header("Location: " . BASE_URL . "login.php");
    exit();
}

// Redirect based on role
switch ($_SESSION['role']) {
    case 'Admin': header("Location: " . BASE_URL . "admin/ratecards.php"); break;
    case 'Scheduler': header("Location: " . BASE_URL . "scheduler/create.php"); break;
    case 'Marketing Officer': header("Location: " . BASE_URL . "marketing/dashboard.php"); break;
    case 'Editor': header("Location: " . BASE_URL . "editor/dashboard.php"); break;
    default: echo "Access Denied.";
}
?>