<?php
session_start();
include '../admin/config/config.php';

if (isset($_SESSION['demo_user'])) {
    $email = $_SESSION['demo_user'];
    $stmt = $conn->prepare("UPDATE demo_requests SET approved = 0 WHERE email = ?");
    $stmt->execute([$email]);
}

$_SESSION = [];
session_destroy();

// Destroy session cookie
// if (ini_get("session.use_cookies")) {
//     $params = session_get_cookie_params();
//     setcookie(
//         session_name(),
//         '',
//         time() - 42000,
//         $params["path"],
//         $params["domain"],
//         $params["secure"],
//         $params["httponly"]
//     );
// }

// Destroy the session
session_destroy();

// Prevent back button from showing cached pages
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1
header("Pragma: no-cache"); // HTTP 1.0
header("Expires: 0"); // Proxies

// Redirect to login page
header("Location: login.php");
exit;
