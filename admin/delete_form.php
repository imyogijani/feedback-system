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
    $stmt = $conn->prepare("DELETE FROM form_responses WHERE form_id = ?");
    $stmt->execute([$form_id]);
    // Delete responses
    $stmt = $conn->prepare("DELETE FROM responses WHERE form_id = ?");
    $stmt->execute([$form_id]);

    // Get question IDs to delete related options
    $qStmt = $conn->prepare("SELECT id FROM questions WHERE form_id = ?");
    $qStmt->execute([$form_id]);
    $questionIds = $qStmt->fetchAll(PDO::FETCH_COLUMN);

    if (!empty($questionIds)) {
        $inClause = implode(',', array_fill(0, count($questionIds), '?'));
        $optStmt = $conn->prepare("DELETE FROM options WHERE question_id IN ($inClause)");
        $optStmt->execute($questionIds);
    }

    // Delete questions
    $stmt = $conn->prepare("DELETE FROM questions WHERE form_id = ?");
    $stmt->execute([$form_id]);

    // Delete the form
    $stmt = $conn->prepare("DELETE FROM forms WHERE id = ?");
    $stmt->execute([$form_id]);

    $conn->commit();

    // Redirect after deletion based on role_id
    if (isset($_SESSION['role_id'])) {
        if ($_SESSION['role_id'] == 1) {
            header("Location: index.php?deleted=1"); // Admin dashboard
        } elseif ($_SESSION['role_id'] == 2) {
            header("Location: moderator_dashboard.php?deleted=1"); // Moderator dashboard
        } elseif ($_SESSION['role_id'] == 3) {
            header("Location: user_dashboard.php?deleted=1"); // User dashboard
        } else {
            header("Location: index.php?deleted=1"); // Default fallback
        }
    } else {
        header("Location: index.php?deleted=1");
    }
    exit();
} catch (Exception $e) {
    $conn->rollBack();
    die("Failed to delete form: " . $e->getMessage());
}
