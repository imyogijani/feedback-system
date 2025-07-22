<?php
session_start();
// Allow users with role_id = 1 (admin), 2 (moderator), or 3 (user) to access this page
if (!isset($_SESSION['role_id']) || !in_array($_SESSION['role_id'], [1, 2, 3])) {
    header("Location: login.php");
    exit();
}

include('config/config.php');

$form_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($form_id <= 0) {
    die("Invalid form ID.");
}

// Begin transaction
$conn->beginTransaction();

try {
    $stmt = $conn->prepare("DELETE FROM form_responses_combined WHERE form_id = ?");
    $stmt->execute([$form_id]);


    // Delete the form
    $stmt = $conn->prepare("DELETE FROM forms_combined WHERE id = ?");
    $stmt->execute([$form_id]);

    $conn->commit();

    // Redirect after deletion based on role_id
    if (isset($_SESSION['role_id'])) {
        if ($_SESSION['role_id'] == 1) {
            header("Location: forms_lists.php?deleted=1"); // Admin dashboard
        } elseif ($_SESSION['role_id'] == 2) {
            header("Location: moderator_dashboard.php?deleted=1"); // Moderator dashboard
        } elseif ($_SESSION['role_id'] == 3) {
            header("Location: user_dashboard.php?deleted=1"); // User dashboard
        } else {
            header("Location: forms_lists.php?deleted=1"); // Default fallback
        }
    } else {
        header("Location: forms_lists.php?deleted=1");
    }
    exit();
} catch (Exception $e) {
    $conn->rollBack();
    die("Failed to delete form: " . $e->getMessage());
}
