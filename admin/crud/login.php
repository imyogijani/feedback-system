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

    // Check for default admin credentials (but fetch from DB for session data)
    if ($username === 'admin' && $password === 'admin@123' && $user) {
        // Set all session variables from DB row
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = 'admin';
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role_id'] = $user['role_id']; // Should be 1 for admin
        $_SESSION['success_message'] = "Welcome, " . $_SESSION['username'] . "! You are logged in as an admin.";
        echo "<script>window.location.href = '../index.php';</script>";
        exit();
    }
    // Check normal user from database
    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['role'] = $user['role'] ?? 'user'; // Default to 'user' if role not set
        $_SESSION['role_id'] = $user['role_id'] ?? 3; // Default to 3 (user) if not set

        // Set admin or moderator session if role_id matches
        if ($_SESSION['role_id'] == 1) {
            $_SESSION['admin_logged_in'] = $user['id'];
        } elseif ($_SESSION['role_id'] == 2) {
            $_SESSION['moderator_logged_in'] = $user['id'];
        }
        $_SESSION['success_message'] = "Welcome, " . $_SESSION['username'] . "! You are logged in.";

        // Redirect based on role_id
        if ($_SESSION['role_id'] == 1) {
            echo "<script>window.location.href = '../index.php';</script>";
        } elseif ($_SESSION['role_id'] == 2) {
            echo "<script>window.location.href = '../moderator_dashboard.php';</script>";
        } else {
            echo "<script>window.location.href = '../user_dashboard.php';</script>";
        }
        exit();
    } else {
        $_SESSION['alert_message'] = "❌ Invalid username or password.";
        echo "<script>window.location.href = '../login.php';</script>";
        exit();
    }
}
