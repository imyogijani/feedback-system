<?php
session_start();

// Clear all session variables
$_SESSION = array();

// Destroy the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy the session
session_destroy();

echo "<h1>Session Cleared</h1>";
echo "<p>All session data has been cleared.</p>";
echo '<p><a href="login.php">Go to Login Page</a></p>';
echo '<p><a href="debug_login.php">Go to Debug Login Page</a></p>';
?>
