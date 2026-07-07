<?php
// config/db.php

$host = 'localhost';
$dbname = 'monara_scheduler_db'; // Ensure this matches exactly what is in cPanel
$user = 'monara_scheduler_sw';   // Ensure this matches exactly what is in cPanel
$pass = '6no5rI0cIgb-';          // Your provided password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>