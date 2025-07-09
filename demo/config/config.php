<?php
$host = "localhost"; // Database host
$username = "root"; // Database username
$password = ""; // Database password
$dbname = "feedback_system"; // Database name
$port = 3306; // Default MySQL port

try {
    $conn = new PDO("mysql:host=$host;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Connected successfully";
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
