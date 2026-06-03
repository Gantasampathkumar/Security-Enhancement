<?php
// config/database.php
// Reusable database connection using MySQLi and XAMPP defaults

$host = "localhost";
$user = "root";
$password = "";
$database = "blog"; // Update this to your existing database name if needed

$mysqli = new mysqli($host, $user, $password, $database);

if ($mysqli->connect_errno) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Set charset for proper encoding
$mysqli->set_charset("utf8mb4");
