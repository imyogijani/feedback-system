<?php
session_start();

echo "<h1>Debug Login Page</h1>";
echo "<p>Current URL: " . $_SERVER['REQUEST_URI'] . "</p>";
echo "<p>Session variables:</p>";
print_r($_SESSION);

echo "<p>If you can see this, PHP is working and login.php is being executed.</p>";

// Check if user is already logged in
if (isset($_SESSION['role_id'])) {
    echo "<p>User is already logged in with role_id: " . $_SESSION['role_id'] . "</p>";
    if ($_SESSION['role_id'] == 1) {
        echo "<p>This would normally redirect to index.php (admin dashboard)</p>";
    } elseif ($_SESSION['role_id'] == 2) {
        echo "<p>This would normally redirect to moderator_dashboard.php</p>";
    } else {
        echo "<p>This would normally redirect to user_dashboard.php</p>";
    }
} else {
    echo "<p>No user logged in - showing login form would be appropriate</p>";
}

// Include the original login form
include('config/config.php');
?>

<!DOCTYPE html>
<html>
<head>
    <title>Debug Login</title>
</head>
<body>
    <h2>Login Form</h2>
    <form method="POST" action="">
        <div>
            <label>Username:</label>
            <input type="text" name="username" required>
        </div>
        <div>
            <label>Password:</label>
            <input type="password" name="password" required>
        </div>
        <div>
            <button type="submit" name="submit">Login</button>
        </div>
    </form>
</body>
</html>

<?php
if (isset($_POST['submit'])) {
    echo "<h3>Form was submitted!</h3>";
    echo "<p>Username: " . ($_POST['username'] ?? 'not provided') . "</p>";
    echo "<p>Password: " . (isset($_POST['password']) ? '[PROVIDED]' : '[NOT PROVIDED]') . "</p>";

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Fetch user from database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        echo "<p>User found in database!</p>";
        echo "<p>User role_id: " . ($user['role_id'] ?? 'not set') . "</p>";
    } else {
        echo "<p>User not found in database</p>";
    }
}
?>
