<?php
// config/db.php

$host = 'localhost';
$dbname = 'swarnawahini_scheduler_db';
$user = 'root'; // Change this if your cPanel user is different
$pass = '';     // Change this if you have a database password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    // Set error mode to exception to help you debug errors
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>