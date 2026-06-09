<?php
// config/config.php

// Define the Base URL
// Change 'scheduler_sw' to match your actual folder name if it's different
$base_url = "http://" . $_SERVER['HTTP_HOST'] . "/scheduler_sw/"; 

define('BASE_URL', $base_url);

// Include the database connection so all pages can use it
require_once 'db.php';
?>