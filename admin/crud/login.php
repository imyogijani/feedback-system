<?php
session_start();
include('../config/config.php');

if (isset($_POST['submit'])) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Fetch user from database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Handle login logic
    if ($user) {
        // Check for default admin credentials
        if ($username === 'admin' && $password === 'admin@123') {
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = 'admin';
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['success_message'] = "Welcome, " . $_SESSION['username'] . "! You are logged in as an admin.";
            header('Location: ../index.php');
            exit();
        }
        
        // Check normal user credentials
        if (password_verify($password, $user['password'])) {
            // Set common session variables
            $_SESSION['user_logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'] ?? 'user';
            $_SESSION['role_id'] = $user['role_id'] ?? 3;
            
            // Set role-specific session variables
            switch ($_SESSION['role_id']) {
                case 1:
                    $_SESSION['admin_logged_in'] = $user['id'];
                    $redirect_url = '../index.php';
                    break;
                case 2:
                    $_SESSION['moderator_logged_in'] = $user['id'];
                    $redirect_url = '../moderator_dashboard.php';
                    break;
                default:
                    $redirect_url = '../user_dashboard.php';
            }
            
            $_SESSION['success_message'] = "Welcome, " . $_SESSION['username'] . "! You are logged in.";
            header('Location: ' . $redirect_url);
            exit();
        }
    }
    
    // Invalid login
    $_SESSION['alert_message'] = "❌ Invalid username or password.";
    header('Location: ../login.php');
    exit();
}
