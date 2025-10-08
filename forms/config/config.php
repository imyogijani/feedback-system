<?php
$host = "192.168.1.109"; // Database host
$username = "feedback_user"; // Database username
$password = "feedback_user"; // Database password

$conn = new PDO("mysql:host=$host;dbname=feedback_system", $username, $password); // Create connection using PDO
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set error mode to exception
// echo "Connected successfully"; // Output success message
