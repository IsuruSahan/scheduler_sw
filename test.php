<?php
require_once 'config/db.php';
$password = password_hash('admin123', PASSWORD_DEFAULT);
$stmt = $pdo->prepare("INSERT INTO users (name, email, password, role_id) VALUES (?, ?, ?, ?)");
$stmt->execute(['Admin User', 'admin@swarnawahini.lk', $password, 1]);
echo "Admin user created!";
?>