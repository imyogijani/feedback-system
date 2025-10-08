<?php
// Custom error handler to suppress GuzzleHttp Promises deprecation warnings
set_error_handler(function ($severity, $message, $file, $line) {
    // Suppress deprecation warnings from GuzzleHttp vendor directory
    if ($severity === E_DEPRECATED &&
        (strpos($file, 'vendor/guzzlehttp/promises') !== false ||
         strpos($file, 'vendor\\guzzlehttp\\promises') !== false) &&
        (strpos($message, 'Implicitly marking parameter') !== false &&
         strpos($message, 'as nullable is deprecated') !== false)) {
        return true; // Don't execute the internal error handler
    }

    // For all other errors, use the default error handler
    return false;
});

$host = "192.168.1.109"; // Database host
$username = "feedback_user"; // Database username
$password = "feedback_user"; // Database password

$conn = new PDO("mysql:host=$host;dbname=u334564157_feedback", $username, $password); // Create connection using PDO
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Set error mode to exception
// echo "Connected successfully"; // Output success message
