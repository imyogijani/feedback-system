<?php
session_start();
include '../admin/config/config.php';

header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Unknown error'];

if (isset($_SESSION['demo_user'])) {
    $email = $_SESSION['demo_user'];
    $stmt = $conn->prepare("UPDATE demo_requests SET approved = 0 WHERE email = ?");
    if ($stmt->execute([$email])) {
        if ($stmt->rowCount() > 0) {
            $response = ['success' => true, 'message' => 'Approved set to 0 for ' . $email];
        } else {
            $response = ['success' => false, 'message' => 'No rows updated. Email may not exist.'];
        }
    } else {
        $errorInfo = $stmt->errorInfo();
        $response = ['success' => false, 'message' => 'DB error: ' . implode(' | ', $errorInfo)];
    }
} else {
    $response = ['success' => false, 'message' => 'Session variable demo_user not set'];
}

$_SESSION = [];
session_destroy();

echo json_encode($response);
