<?php 
require_once __DIR__ . '/../config/config.php';
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Swarnawahini Scheduler</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
    <div class="container">
        <a class="navbar-brand" href="<?php echo BASE_URL; ?>">Scheduler SW</a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <?php if (isset($_SESSION['role_id'])): ?>
                    
                    <?php if ((int)$_SESSION['role_id'] === 1): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/ratecards.php">Rate Cards</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/agencies.php">Agencies</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/episodes.php">Content</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/settings.php">Settings</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/users.php">Users</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>admin/inventory.php">Inventory</a></li>
                    <?php endif; ?>

                    <?php if ((int)$_SESSION['role_id'] === 2): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>scheduler/create.php">Create Schedule</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>scheduler/manage.php">Manage Schedules</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>scheduler/view_assets.php">View Assets</a></li>

                    <?php endif; ?>

                    <?php if ((int)$_SESSION['role_id'] === 3): ?>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>marketing/dashboard.php">Approvals</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?php echo BASE_URL; ?>marketing/upload_media.php">New shedule</a></li>
                    <?php endif; ?>

                    <li class="nav-item">
                        <a class="nav-link btn btn-outline-danger btn-sm text-white ms-lg-3" href="<?php echo BASE_URL; ?>logout.php">Logout</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
</body>
</html>