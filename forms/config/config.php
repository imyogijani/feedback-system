<?php
$host = "localhost"; // Database host
$username = "root"; // Database username
$password = "070803"; // Database password

$conn = new PDO("mysql:host=$host;dbname=u334564157_feedback", $username, $password); // Create connection using PDO
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set error mode to exception
// echo "Connected successfully"; // Output success message
