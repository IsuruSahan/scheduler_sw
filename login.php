<?php 
require_once 'config/config.php';
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <title>Login | Scheduler SW</title>
    <style>
        .login-card { padding: 2rem; border-radius: 1rem; border: none; }
        .bg-login { background: #f8f9fa; }
    </style>
</head>
<body class="bg-login">
    <div class="container d-flex justify-content-center align-items-center vh-100">
        <div class="card login-card shadow-lg" style="width: 400px;">
            <h3 class="text-center mb-1 text-primary">Scheduler SW</h3>
            <p class="text-center text-muted mb-4">Please sign in to continue</p>
            
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger py-2 text-center">Invalid email or password.</div>
            <?php endif; ?>

            <form action="auth_process.php" method="POST">
                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary w-100">Sign In</button>
            </form>
        </div>
    </div>
</body>
</html>