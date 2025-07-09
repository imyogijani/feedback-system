<?php
session_start();
include('config/config.php');

// Check access permission
if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2])) {
    header("Location: login.php");
    exit();
}

// Get user ID from URL
$user_id = $_GET['id'] ?? null;

if (!$user_id || !is_numeric($user_id)) {
    $_SESSION['alert_message'] = "Invalid user ID.";
    header("Location: manage_users.php");
    exit();
}

// Prevent deleting self
if ($_SESSION['user_id'] == $user_id) {
    $_SESSION['alert_message'] = "You cannot delete your own account.";
    header("Location: manage_users.php");
    exit();
}

// Check if user exists
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    $_SESSION['alert_message'] = "User not found.";
    header("Location: manage_users.php");
    exit();
}

// Delete user
$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
if ($stmt->execute([$user_id])) {
    $_SESSION['success_message'] = "User deleted successfully.";
} else {
    $_SESSION['alert_message'] = "Failed to delete user.";
}

header("Location: manage_users.php");
exit();
