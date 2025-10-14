<?php
session_start();
header('Content-Type: application/json');

// Check if user is admin
if (!isset($_SESSION['role_id']) || $_SESSION['role_id'] != 1) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit;
}

require_once '../config/config.php';

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

$userId = (int)$input['user_id'];

// Prevent admin from deleting themselves
if ($userId == $_SESSION['user_id']) {
    echo json_encode(['success' => false, 'message' => 'You cannot delete your own account']);
    exit;
}

// Check if user exists
$stmt = $conn->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo json_encode(['success' => false, 'message' => 'User not found']);
    exit;
}

try {
    // Begin transaction
    $conn->beginTransaction();

    // Delete related records first (if any foreign key constraints exist)
    // You may need to add more cleanup queries based on your database schema

    // Delete user sessions (if you have a sessions table)
    // $stmt = $conn->prepare("DELETE FROM user_sessions WHERE user_id = ?");
    // $stmt->execute([$userId]);

    // Delete forms created by this user (optional - you might want to reassign instead)
    $stmt = $conn->prepare("DELETE FROM forms_combined WHERE created_by = ?");
    $stmt->execute([$userId]);

    // Delete form responses by this user (optional)
    $stmt = $conn->prepare("DELETE FROM form_responses_combined WHERE user_id = ?");
    $stmt->execute([$userId]);

    // Finally, delete the user
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $result = $stmt->execute([$userId]);

    if ($result && $stmt->rowCount() > 0) {
        $conn->commit();
        echo json_encode([
            'success' => true,
            'message' => "User '{$user['username']}' has been deleted successfully"
        ]);
    } else {
        $conn->rollback();
        echo json_encode(['success' => false, 'message' => 'Failed to delete user']);
    }
} catch (PDOException $e) {
    $conn->rollback();
    error_log("Database error in delete_user.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
