<?php 
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Swarnawahini Scheduler</title>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>">Scheduler SW</a>
        <div class="collapse navbar-collapse">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['role'])): ?>
                    
                    <?php if ($_SESSION['role'] == 'Admin'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/ratecards.php">Rate Cards</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/agencies.php">Agencies</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/settings.php">Settings</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/episodes.php">Episodes</a></li>


                    <?php endif; ?>

                    <?php if ($_SESSION['role'] == 'Scheduler'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>scheduler/create.php">Create Schedule</a></li>
                    <?php endif; ?>

                    <li class="nav-item"><a class="nav-link btn btn-danger btn-sm text-white" href="<?php echo BASE_URL; ?>logout.php">Logout</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>
<div class="container">