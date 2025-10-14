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

if (!$input || !isset($input['user_id']) || !isset($input['status'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid input data']);
    exit;
}

$userId = (int)$input['user_id'];
$status = (int)$input['status'];

// Validate status (0 or 1)
if ($status !== 0 && $status !== 1) {
    echo json_encode(['success' => false, 'message' => 'Invalid status value']);
    exit;
}

// Prevent admin from deactivating themselves
if ($userId == $_SESSION['user_id'] && $status == 0) {
    echo json_encode(['success' => false, 'message' => 'You cannot deactivate your own account']);
    exit;
}

try {
    // Update user status
    $stmt = $conn->prepare("UPDATE users SET is_active = ? WHERE id = ?");
    $result = $stmt->execute([$status, $userId]);

    if ($result && $stmt->rowCount() > 0) {
        $action = $status ? 'activated' : 'deactivated';
        echo json_encode([
            'success' => true,
            'message' => "User has been {$action} successfully"
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'User not found or no changes made']);
    }
} catch (PDOException $e) {
    error_log("Database error in toggle_user_status.php: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Database error occurred']);
}
?>
