<?php
// D:\xampp\htdocs\feedback-system\admin\google-login.php

// Start session at the very beginning
session_start();

// Include Composer's autoloader for Firebase PHP SDK
// IMPORTANT: Adjust this path if 'vendor' is not directly in the 'admin' directory
include  'vendor/autoload.php';

// Import necessary Firebase classes
use Kreait\Firebase\Factory;
use Kreait\Firebase\Exception\Auth\IdTokenVerificationFailed;

// Set the Content-Type header to application/json for the response
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
error_log('Google Login PHP: Raw input received: ' . var_export($rawInput, true)); // Log raw input for debugging

// Decode JSON input
$data = json_decode($rawInput, true);

// Check for JSON decoding errors
if (json_last_error() !== JSON_ERROR_NONE) {
    $errorMessage = 'JSON decoding error: ' . json_last_error_msg();
    error_log('Google Login PHP: ' . $errorMessage);
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON input received: ' . $errorMessage,
        'json_error' => json_last_error_msg(),
        'raw_input' => $rawInput
    ]);
    exit;
}

error_log('Google Login PHP: Decoded data array: ' . var_export($data, true)); // Log decoded data

// Attempt to extract the idToken from the decoded data
$idToken = $data['idToken'] ?? null;
error_log('Google Login PHP: Extracted idToken: ' . var_export($idToken, true)); // Log extracted token

// Validate ID Token presence
if (empty($idToken)) {
    echo json_encode([
        'success' => false,
        'message' => 'Missing or empty ID token. Ensure "idToken" key is present and has a valid value.'
    ]);
    exit;
}

try {
    // Initialize Firebase Factory with your service account key
    // IMPORTANT: Adjust this path if 'firebase-service-account.json' is not directly in the 'admin' directory
    $factory = (new Factory)->withServiceAccount('firebase-service-account.json');
    $auth = $factory->createAuth();

    // Verify the Firebase ID Token
    $verifiedIdToken = $auth->verifyIdToken($idToken);

    // Get user UID from the token claims
    $uid = $verifiedIdToken->claims()->get('sub');

    // Fetch full user details from Firebase Auth (optional, but good for session data)
    $user = $auth->getUser($uid);

    // Connect to your database
    include 'config/config.php';

    // Check if user exists in local DB by firebase_uid
    $stmt = $conn->prepare("SELECT * FROM users WHERE firebase_uid = ?");
    $stmt->execute([$uid]);
    $localUser = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$localUser) {
        // Ensure unique username
        $baseUsername = $user->displayName ?? $user->email;
        $username = $baseUsername;
        $suffix = 1;
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        while (true) {
            $checkStmt->execute([$username]);
            if ($checkStmt->fetchColumn() == 0) {
                break;
            }
            $username = $baseUsername . '_' . substr($uid, 0, 6) . ($suffix > 1 ? $suffix : '');
            $suffix++;
        }
        // Insert new user with firebase_uid, role_id=3, created_by=1
        $insert = $conn->prepare("INSERT INTO users (firebase_uid, username, email, role_id, created_by) VALUES (?, ?, ?, 3, 1)");
        $insert->execute([
            $uid,
            $username,
            $user->email
        ]);
        // Fetch the newly inserted user
        $stmt = $conn->prepare("SELECT * FROM users WHERE firebase_uid = ?");
        $stmt->execute([$uid]);
        $localUser = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Set session variables from local DB
    $_SESSION['user_id'] = $localUser['id'];
    $_SESSION['firebase_uid'] = $localUser['firebase_uid'];
    $_SESSION['email'] = $localUser['email'];
    $_SESSION['name'] = $localUser['username'];
    $_SESSION['signed_in_at'] = time();
    $_SESSION['auth_method'] = 'google';
    $_SESSION['role_id'] = $localUser['role_id']; // Ensure role_id is set for Google login

    error_log('Google Login PHP: User ' . $uid . ' successfully logged in via Google and session created.');

    // Respond with success
    echo json_encode([
        'success' => true,
        'message' => 'Google Sign-In successful, session created.',
        'user_id' => $uid,
        'email' => $user->email,
        'name' => $user->displayName, // Send display name back to client
        'redirect' => 'user_dashboard.php'
    ]);
} catch (IdTokenVerificationFailed $e) {
    // Catch specific errors related to ID Token verification (e.g., expired, invalid)
    $errorMessage = 'Firebase ID Token verification failed: ' . $e->getMessage();
    error_log('Google Login PHP: ' . $errorMessage);
    echo json_encode([
        'success' => false,
        'message' => 'Google Sign-In failed: ' . $errorMessage . '. Please try again.',
        'error_code' => $e->getCode()
    ]);
} catch (\Throwable $e) {
    // Catch any other unexpected errors during the process
    $errorMessage = 'Authentication process failed: ' . $e->getMessage();
    error_log('Google Login PHP: ' . $errorMessage);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred during Google Sign-In: ' . $errorMessage . '. Please try again or contact support.'
    ]);
}

// Always exit after sending the JSON response to prevent any
// further output from HTML or other includes if this script
// were part of a larger PHP page.
exit;
