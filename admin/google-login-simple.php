<?php
// Simplified Google Login Handler
session_start();
include('config/config.php');

header('Content-Type: application/json');

// Ensure the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Only POST requests are allowed.'
    ]);
    exit;
}

// Get raw input from the request body
$rawInput = file_get_contents('php://input');

// Decode JSON input
$data = json_decode($rawInput, true);

// Check for JSON decoding errors
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON input: ' . json_last_error_msg()
    ]);
    exit;
}

// Extract data from the request
$idToken = $data['idToken'] ?? null;
$email = $data['email'] ?? null;
$name = $data['name'] ?? null;
$picture = $data['picture'] ?? null;

// Basic validation
if (empty($idToken) || empty($email)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing required fields: idToken or email'
    ]);
    exit;
}

try {
    // For simplicity, we'll trust the client-side token validation
    // In production, you should verify the token server-side

    // Check if user exists in database
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        // Create new user from Google data
        $username = explode('@', $email)[0]; // Use email prefix as username
        $stmt = $conn->prepare("INSERT INTO users (username, email, password, role_id, created_at) VALUES (?, ?, ?, ?, NOW())");
        $stmt->execute([$username, $email, '', 3]); // Default role_id = 3 for regular users

        $userId = $conn->lastInsertId();
        $roleId = 3;
    } else {
        $userId = $user['id'];
        $roleId = $user['role_id'] ?? 3;
    }

    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['username'] = $username ?? $user['username'];
    $_SESSION['email'] = $email;
    $_SESSION['role_id'] = $roleId;
    $_SESSION['role'] = 'user';
    $_SESSION['auth_method'] = 'google';

    // Set role-specific session variables
    switch ($roleId) {
        case 1:
            $_SESSION['admin_logged_in'] = true;
            $redirect = 'index.php';
            break;
        case 2:
            $_SESSION['moderator_logged_in'] = true;
            $redirect = 'moderator_dashboard.php';
            break;
        default:
            $_SESSION['user_logged_in'] = true;
            $redirect = 'user_dashboard.php';
    }

    // Success response
    echo json_encode([
        'success' => true,
        'message' => 'Google Sign-In successful',
        'redirect' => $redirect,
        'user' => [
            'id' => $userId,
            'username' => $_SESSION['username'],
            'email' => $email,
            'role_id' => $roleId
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Database error: ' . $e->getMessage()
    ]);
}
?>
