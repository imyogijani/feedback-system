<?php
session_start();

// Check if admin is logged in
if (isset($_SESSION['admin_logged_in'])) {
    $user_id = $_SESSION['admin_logged_in'];
    $user_type = 'admin';
}
// Check if moderator is logged in
elseif (isset($_SESSION['moderator_logged_in'])) {
    $user_id = $_SESSION['moderator_logged_in'];
    $user_type = 'moderator';
}
// Check if user is logged in
elseif (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $user_type = 'user';
}
// If neither is logged in, redirect to login page
else {
    header("Location: login.php");
    exit();
}

include('config/config.php');

// Fetch user data from database based on user type
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    die("User profile not found.");
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title><?php echo ucfirst($user_type); ?> Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>

    <div class="container mt-5" style="max-width: 600px;">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5><?php echo ucfirst($user_type); ?> Profile</h5>
            </div>
            <div class="card-body text-center">
                <img src="<?= isset($user['profile_image']) && $user['profile_image'] != ''
                                ? 'assets/images/' . $user['profile_image']
                                : 'assets/img/default-avatar.png' ?>"
                    class="rounded-circle mb-3"
                    style="width: 100px; height: 100px; object-fit: cover;"
                    alt="Profile Image">

                <h4><?= htmlspecialchars($user['username']) ?></h4>
                <p><?= htmlspecialchars($user['email']) ?></p>

                <a href="edit_profile.php" class="btn btn-warning mt-3">Edit Profile</a>
                <!-- <a href="forgot-password.php" class="btn btn-link mt-2">Forgot Password?</a> -->
                <?php if ($user_type === 'admin'): ?>
                    <a href="index.php" class="btn btn-info mt-3">Go to Admin Dashboard</a>
                <?php elseif ($user_type === 'moderator'): ?>
                    <a href="forgot-password.php" class="btn btn-primary mt-3">Forgot Password?</a>
                    <a href="moderator_dashboard.php" class="btn btn-info mt-3">Go to Moderator Dashboard</a>
                <?php elseif ($user_type === 'user'): ?>
                    <a href="forgot-password.php" class="btn btn-primary mt-3">Forgot Password?</a>
                    <a href="user_dashboard.php" class="btn btn-info mt-3">Go to User Dashboard</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

</body>

</html>