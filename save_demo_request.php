<?php
// d:/xampp/htdocs/feedback-system/save_demo_request.php
header('Content-Type: application/json');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Load DB config
require_once __DIR__ . '/admin/config/config.php';

// Get POST data
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$business_type = trim($_POST['business_type'] ?? '');
$other_business_type = trim($_POST['other_business_type'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($business_type === 'Other' && $other_business_type !== '') {
    $business_type = $other_business_type;
}

if (!$name || !$email || !$business_type) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please fill all required fields.']);
    exit;
}

// Insert into demo_requests table
try {
    $stmt = $conn->prepare('INSERT INTO demo_requests (name, email, business_type, message, approved, created_at) VALUES (?, ?, ?, ?, 0, NOW())');
    $stmt->execute([$name, $email, $business_type, $message]);
    echo json_encode(['success' => true, 'message' => 'Demo request submitted successfully!']);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
